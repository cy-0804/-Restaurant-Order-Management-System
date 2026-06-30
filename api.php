<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/order_mailer.php';
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
        
        if ($status === 'cancelled') {
            $stmtOrder = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
            $stmtOrder->execute([$id]);
            $order = $stmtOrder->fetch();
            
            if ($order && !empty($order['customer_email'])) {
                $subject = 'Sup Tulang ZZ Order Cancelled #' . $order['id'];
                $body = "Dear " . $order['customer_name'] . ",\r\n\r\n";
                $body .= "We regret to inform you that your Order #" . $order['id'] . " has been rejected and cancelled by our staff.\r\n";
                $body .= "This may be due to unavailable items, payment issues, or our inability to fulfill the order at this time.\r\n";
                $body .= "If you have already made a payment, please contact us with your receipt to process a refund.\r\n\r\n";
                $body .= "We apologize for the inconvenience.\r\n\r\n";
                $body .= "Sup Tulang ZZ";
                
                $config = load_mail_config();
                if ($config) {
                    try {
                        send_smtp_email($config, $order['customer_email'], $order['customer_name'], $subject, $body);
                    } catch (Exception $e) {
                        log_order_confirmation_email($order['customer_email'], $subject, $body);
                    }
                } else {
                    log_order_confirmation_email($order['customer_email'], $subject, $body);
                }
            }
        }
        
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

elseif ($action === 'unverify_payment') {
    if (!is_logged_in() || !in_array($_SESSION['user_role'], ['admin', 'staff'])) {
        returnError("Unauthorized payment verification action.");
    }
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if (!$db_connected) {
        returnError("Database not connected.");
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = ?");
        $stmt->execute([$id]);
        
        $stmtOrder = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmtOrder->execute([$id]);
        $order = $stmtOrder->fetch();
        
        if ($order && !empty($order['customer_email'])) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $base_path = rtrim(dirname($_SERVER['PHP_SELF'] ?? ''), '/\\');
            $tracking_url = $protocol . '://' . $host . $base_path . '/track.php?id=' . urlencode($order['id']);
            
            $subject = 'Sup Tulang ZZ Payment Failed for Order #' . $order['id'];
            $body = "Dear " . $order['customer_name'] . ",\r\n\r\n";
            $body .= "We reviewed your payment receipt for Order #" . $order['id'] . ", but it appears to be invalid or incomplete.\r\n";
            $body .= "Your payment status has been marked as Failed. Your order is pending resolution.\r\n";
            $body .= "Please re-upload a valid payment receipt using this link: " . $tracking_url . "\r\n\r\n";
            $body .= "Please contact us to resolve this issue.\r\n\r\n";
            $body .= "Sup Tulang ZZ";
            
            $config = load_mail_config();
            if ($config) {
                try {
                    send_smtp_email($config, $order['customer_email'], $order['customer_name'], $subject, $body);
                } catch (Exception $e) {
                    log_order_confirmation_email($order['customer_email'], $subject, $body);
                }
            } else {
                log_order_confirmation_email($order['customer_email'], $subject, $body);
            }
        }
        
        echo json_encode(['success' => true]);
        exit;
    } catch (Exception $e) {
        returnError($e->getMessage());
    }
}

elseif ($action === 'reupload_receipt') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    if (!$db_connected) {
        returnError("Database not connected.");
    }
    
    try {
        if (!isset($_FILES['payment_receipt']) || $_FILES['payment_receipt']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Please upload a valid receipt file.");
        }
        
        if ($_FILES['payment_receipt']['size'] > 5 * 1024 * 1024) {
            throw new Exception("Payment receipt must be 5MB or smaller.");
        }
        
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($_FILES['payment_receipt']['tmp_name']);
        $allowed_mimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf'
        ];
        if (!isset($allowed_mimes[$mime_type])) {
            throw new Exception("Payment receipt must be a JPG, PNG, WEBP, or PDF file.");
        }
        
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $filename = uniqid('receipt_re_', true) . '.' . $allowed_mimes[$mime_type];
        $destination = $upload_dir . $filename;
        
        if (!move_uploaded_file($_FILES['payment_receipt']['tmp_name'], $destination)) {
            throw new Exception("Could not save payment receipt.");
        }
        
        $receipt_path = 'uploads/' . $filename;
        
        $stmt = $pdo->prepare("UPDATE orders SET payment_receipt = ?, payment_status = 'pending' WHERE id = ?");
        $stmt->execute([$receipt_path, $id]);
        
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
        $order_type = $_POST['order_type'] ?? '';
        $allowed_order_types = ['walk-in', 'online'];
        if (!in_array($order_type, $allowed_order_types, true)) {
            throw new Exception("Invalid order type.");
        }

        $table_number = isset($_POST['table_number']) && $_POST['table_number'] !== '' ? intval($_POST['table_number']) : null;
        if ($order_type === 'walk-in' && (!$table_number || $table_number < 1 || $table_number > 99)) {
            throw new Exception("A valid table number is required for walk-in orders.");
        }

        $customer_name = trim($_POST['customer_name'] ?? '');
        $customer_email = trim($_POST['customer_email'] ?? '');
        $delivery_address = trim($_POST['delivery_address'] ?? '');
        $payment_method = $_POST['payment_method'] ?? '';

        if ($customer_name === '' || strlen($customer_name) > 100) {
            throw new Exception("Please enter a valid customer name.");
        }
        if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL) || strlen($customer_email) > 100) {
            throw new Exception("Please enter a valid email address.");
        }

        $cart_data = json_decode($_POST['cart_data'] ?? '', true);
        if (!is_array($cart_data) || count($cart_data) === 0) {
            throw new Exception("Your cart is empty or invalid.");
        }

        $requested_items = [];
        foreach ($cart_data as $item) {
            $menu_item_id = isset($item['id']) ? intval($item['id']) : 0;
            $quantity = isset($item['quantity']) ? intval($item['quantity']) : 0;
            if ($menu_item_id <= 0 || $quantity <= 0 || $quantity > 50) {
                throw new Exception("Invalid cart item quantity.");
            }
            if (!isset($requested_items[$menu_item_id])) {
                $requested_items[$menu_item_id] = 0;
            }
            $requested_items[$menu_item_id] += $quantity;
        }

        $placeholders = implode(',', array_fill(0, count($requested_items), '?'));
        $stmtMenu = $pdo->prepare("
            SELECT id, name, price
            FROM menu_items
            WHERE is_available = 1 AND id IN ($placeholders)
        ");
        $stmtMenu->execute(array_keys($requested_items));
        $menu_rows = $stmtMenu->fetchAll();

        if (count($menu_rows) !== count($requested_items)) {
            throw new Exception("One or more cart items are unavailable.");
        }

        $validated_items = [];
        $subtotal = 0.0;
        foreach ($menu_rows as $row) {
            $quantity = $requested_items[(int)$row['id']];
            $price = (float)$row['price'];
            $subtotal += $price * $quantity;
            $validated_items[] = [
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'quantity' => $quantity,
                'price' => $price
            ];
        }
        $total_amount = round($subtotal * 1.06, 2);
        
        $receipt_path = null;
        if (isset($_FILES['payment_receipt']) && $_FILES['payment_receipt']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['payment_receipt']['size'] > 5 * 1024 * 1024) {
                throw new Exception("Payment receipt must be 5MB or smaller.");
            }

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->file($_FILES['payment_receipt']['tmp_name']);
            $allowed_mimes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'application/pdf' => 'pdf'
            ];
            if (!isset($allowed_mimes[$mime_type])) {
                throw new Exception("Payment receipt must be a JPG, PNG, WEBP, or PDF file.");
            }

            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $filename = uniqid('receipt_', true) . '.' . $allowed_mimes[$mime_type];
            $destination = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['payment_receipt']['tmp_name'], $destination)) {
                $receipt_path = 'uploads/' . $filename;
            } else {
                throw new Exception("Could not save payment receipt.");
            }
        }

        if ($payment_method === 'manual_transfer' && empty($receipt_path)) {
            throw new Exception("Please upload a payment receipt for manual bank transfer.");
        }
        
        $pdo->beginTransaction();
        
        $is_gateway_paid = in_array($payment_method, ['ewallet', 'card'], true)
            && isset($_POST['payment_gateway_paid'])
            && $_POST['payment_gateway_paid'] === '1';
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
        
        $stmtItem = $pdo->prepare("
            INSERT INTO order_items (order_id, menu_item_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($validated_items as $item) {
            $stmtItem->execute([
                $order_id,
                $item['id'],
                $item['quantity'],
                $item['price']
            ]);
        }
        
        $pdo->commit();

        $email_status = send_order_confirmation_email([
            'id' => $order_id,
            'order_type' => $order_type,
            'table_number' => $table_number,
            'customer_name' => $customer_name,
            'customer_email' => $customer_email,
            'delivery_address' => $delivery_address,
            'total_amount' => $total_amount,
            'payment_status' => $payment_status,
            'order_status' => 'pending'
        ], $validated_items);
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        header("Location: order_confirmation.php?id=" . $order_id . "&email_status=" . urlencode($email_status));
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
