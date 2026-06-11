<?php
require_once __DIR__ . '/includes/auth.php';
include 'config/db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

header('Content-Type: application/json');

function returnError($message) {
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

if ($action === 'get_all_orders') {
    if (!is_logged_in()) {
        returnError("Unauthorized access. Please log in.");
    }
    if (!$db_connected) {
        returnError("Database not connected. Mock mode should read from local storage.");
    }
    
    try {
        $stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
        $orders = $stmt->fetchAll();
        
        foreach ($orders as &$order) {
            $stmtItems = $pdo->prepare("
                SELECT oi.*, mi.name 
                FROM order_items oi 
                JOIN menu_items mi ON oi.menu_item_id = mi.id 
                WHERE oi.order_id = ?
            ");
            $stmtItems->execute([$order['id']]);
            $order['items'] = $stmtItems->fetchAll();
        }
        
        echo json_encode($orders);
        exit;
    } catch (Exception $e) {
        returnError($e->getMessage());
    }
}

elseif ($action === 'get_order_status') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if (!$db_connected) {
        returnError("Database not connected. Mock mode should read from local storage.");
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        
        if (!$order) {
            returnError("Order not found");
        }
        
        $stmtItems = $pdo->prepare("
            SELECT oi.*, mi.name 
            FROM order_items oi 
            JOIN menu_items mi ON oi.menu_item_id = mi.id 
            WHERE oi.order_id = ?
        ");
        $stmtItems->execute([$id]);
        $order['items'] = $stmtItems->fetchAll();
        
        echo json_encode($order);
        exit;
    } catch (Exception $e) {
        returnError($e->getMessage());
    }
}

elseif ($action === 'update_status') {
    if (!is_logged_in() || !in_array($_SESSION['user_role'], ['admin', 'staff'])) {
        returnError("Unauthorized status update action.");
    }
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    
    $allowed_statuses = ['pending', 'preparing', 'ready', 'completed', 'cancelled'];
    if (!in_array($status, $allowed_statuses)) {
        returnError("Invalid status value");
    }
    
    if (!$db_connected) {
        returnError("Database not connected.");
    }
    
    try {
        $payment_status_sql = "";
        if ($status === 'ready' || $status === 'completed') {
            $payment_status_sql = ", payment_status = 'verified'";
        }
        
        $stmt = $pdo->prepare("UPDATE orders SET order_status = ? $payment_status_sql WHERE id = ?");
        $stmt->execute([$status, $id]);
        
        echo json_encode(['success' => true]);
        exit;
    } catch (Exception $e) {
        returnError($e->getMessage());
    }
}

elseif ($action === 'verify_payment') {
    if (!is_logged_in() || !in_array($_SESSION['user_role'], ['admin', 'staff'])) {
        returnError("Unauthorized payment verification action.");
    }
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if (!$db_connected) {
        returnError("Database not connected.");
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'verified' WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true]);
        exit;
    } catch (Exception $e) {
        returnError($e->getMessage());
    }
}

elseif ($action === 'create_order') {
    
    if (!$db_connected) {
        header('Content-Type: text/html');
        echo "<h2>Database Error</h2><p>Cannot save order because MySQL is offline. Enable MySQL to proceed.</p>";
        echo "<a href='checkout.php'>Go back and use client mock mode instead.</a>";
        exit;
    }
    
    try {
        $order_type = $_POST['order_type'];
        $table_number = isset($_POST['table_number']) && !empty($_POST['table_number']) ? intval($_POST['table_number']) : null;
        $customer_name = $_POST['customer_name'];
        $customer_email = $_POST['customer_email'];
        $delivery_address = isset($_POST['delivery_address']) ? $_POST['delivery_address'] : null;
        $total_amount = floatval($_POST['total_amount']);
        
        $receipt_path = null;
        if (isset($_FILES['payment_receipt']) && $_FILES['payment_receipt']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_ext = strtolower(pathinfo($_FILES['payment_receipt']['name'], PATHINFO_EXTENSION));
            $filename = uniqid('receipt_', true) . '.' . $file_ext;
            $destination = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['payment_receipt']['tmp_name'], $destination)) {
                $receipt_path = 'uploads/' . $filename;
            }
        }
        
        $pdo->beginTransaction();
        
        $is_gateway_paid = isset($_POST['payment_gateway_paid']) && $_POST['payment_gateway_paid'] === '1';
        $payment_status = ($order_type === 'walk-in' || $is_gateway_paid) ? 'verified' : 'pending';
        
        if ($is_gateway_paid && empty($receipt_path)) {
            $receipt_path = 'SUP-PAY Gateway FPX';
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO orders (order_type, table_number, customer_name, customer_email, delivery_address, total_amount, payment_status, payment_receipt, order_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([
            $order_type,
            $table_number,
            $customer_name,
            $customer_email,
            $delivery_address,
            $total_amount,
            $payment_status,
            $receipt_path
        ]);
        
        $order_id = $pdo->lastInsertId();
        
        $cart_data = json_decode($_POST['cart_data'], true);
        if (is_array($cart_data)) {
            $stmtItem = $pdo->prepare("
                INSERT INTO order_items (order_id, menu_item_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($cart_data as $item) {
                $stmtItem->execute([
                    $order_id,
                    $item['id'],
                    $item['quantity'],
                    $item['price']
                ]);
            }
        }
        
        $pdo->commit();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        header("Location: order_confirmation.php?id=" . $order_id);
        exit;
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header('Content-Type: text/html');
        echo "<h2>Transaction Failed</h2><p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<a href='checkout.php'>Go Back</a>";
        exit;
    }
}

else {
    echo json_encode(['error' => 'Action not defined']);
    exit;
}
?>
