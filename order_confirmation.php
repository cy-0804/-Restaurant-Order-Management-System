<?php
include 'config/db.php';

$order_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$mock_id = isset($_GET['mock_id']) ? htmlspecialchars($_GET['mock_id']) : null;
$email_status = isset($_GET['email_status']) ? $_GET['email_status'] : '';

$db_order = null;
$db_order_items = [];

if ($db_connected && $order_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $db_order = $stmt->fetch();
        
        if ($db_order) {
            $stmtItems = $pdo->prepare("
                SELECT oi.*, mi.name 
                FROM order_items oi 
                JOIN menu_items mi ON oi.menu_item_id = mi.id 
                WHERE oi.order_id = ?
            ");
            $stmtItems->execute([$order_id]);
            $db_order_items = $stmtItems->fetchAll();
        }
    } catch (Exception $e) {
    }
}

include 'includes/header.php';
?>

<div class="confirmation-card glass-card">
    <div class="success-icon"><i class="fa-solid fa-circle-check"></i></div>
    
    <h2>Order Placed Successfully!</h2>
    <p style="color:var(--text-muted); margin-bottom: 2rem;">
        Thank you for ordering with Sup Tulang ZZ. Your order has been sent to the kitchen.
    </p>

    <?php
        $email_title = 'Confirmation Email Queued';
        $email_message = 'A receipt and tracking details have been prepared for ';
        $email_icon = 'fa-paper-plane';
        $email_color = 'var(--success)';

        if ($email_status === 'sent') {
            $email_title = 'Confirmation Email Sent!';
            $email_message = 'A receipt and tracking details have been sent to ';
        } elseif ($email_status === 'logged') {
            $email_title = 'Confirmation Email Logged';
            $email_message = 'Mail is not configured, so a copy was saved in logs/email_confirmations.log for ';
            $email_icon = 'fa-file-lines';
        } elseif ($email_status === 'skipped') {
            $email_title = 'Email Not Sent';
            $email_message = 'No valid email address was available for ';
            $email_icon = 'fa-circle-info';
            $email_color = 'var(--info)';
        }
    ?>
    <div style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: var(--radius-sm); padding: 1rem; text-align: left; margin-bottom: 2rem; display: flex; gap: 0.75rem; align-items: flex-start;">
        <i class="fa-solid <?php echo $email_icon; ?>" style="color: <?php echo $email_color; ?>; font-size: 1.25rem; margin-top: 0.15rem;"></i>
        <div>
            <strong style="color: <?php echo $email_color; ?>; font-size: 0.95rem;"><?php echo htmlspecialchars($email_title); ?></strong>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem; line-height: 1.4;">
                <?php echo htmlspecialchars($email_message); ?><strong id="sent-email-span" style="color:var(--text-main)">your email</strong>.
            </p>
        </div>
    </div>

    <div class="order-info-grid">
        <div>
            <span style="color:var(--text-muted); font-size:0.85rem;">Order Number</span>
            <p style="font-weight:600; font-size:1.1rem;" id="info-order-id">
                <?php echo $order_id ? '#'.$order_id : '#'.$mock_id; ?>
            </p>
        </div>
        <div>
            <span style="color:var(--text-muted); font-size:0.85rem;">Order Mode</span>
            <p style="font-weight:600; font-size:1.1rem; text-transform:capitalize;" id="info-order-type">
                <?php echo $db_order ? htmlspecialchars($db_order['order_type']) : 'Loading...'; ?>
            </p>
        </div>
        <div style="grid-column: span 2; margin-top: 0.5rem;">
            <span style="color:var(--text-muted); font-size:0.85rem;">Customer Name</span>
            <p style="font-weight:600; font-size:1.1rem;" id="info-customer-name">
                <?php echo $db_order ? htmlspecialchars($db_order['customer_name']) : 'Loading...'; ?>
            </p>
        </div>
    </div>

    <div style="text-align: left; margin-bottom: 2rem;">
        <h4 style="font-size: 1.05rem; margin-bottom: 1rem; color: var(--text-main);">Order Items</h4>
        <div id="items-receipt-list" style="display:flex; flex-direction:column; gap:0.75rem; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:1rem; margin-bottom:1rem;">
            <?php if ($db_order): ?>
                <?php foreach ($db_order_items as $item): ?>
                    <div style="display:flex; justify-content:space-between; font-size:0.95rem;">
                        <span style="color:var(--text-muted);"><?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['name']); ?></span>
                        <span>RM <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div id="info-subtotal-tax" style="border-bottom: 1px dashed var(--border-color); padding-bottom: 1rem; margin-bottom: 1rem;">
            <?php if ($db_order): 
                $subtotal = 0;
                foreach ($db_order_items as $item) {
                    $subtotal += ($item['price'] * $item['quantity']);
                }
                $tax = $db_order['total_amount'] - $subtotal;
            ?>
                <div style="display:flex; justify-content:space-between; font-size:0.9rem; margin-bottom:0.5rem; color:var(--text-muted);">
                    <span>Subtotal</span>
                    <span>RM <?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:0.9rem; color:var(--text-muted);">
                    <span>Service Tax (6%)</span>
                    <span>RM <?php echo number_format($tax, 2); ?></span>
                </div>
            <?php else: ?>
                <div style="display:flex; justify-content:space-between; font-size:0.9rem; margin-bottom:0.5rem; color:var(--text-muted);">
                    <span>Subtotal</span>
                    <span id="info-subtotal-val">RM 0.00</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:0.9rem; color:var(--text-muted);">
                    <span>Service Tax (6%)</span>
                    <span id="info-tax-val">RM 0.00</span>
                </div>
            <?php endif; ?>
        </div>
        
        <div style="display:flex; justify-content:space-between; font-weight:700; font-size:1.15rem; color:var(--primary);">
            <span>Total Paid</span>
            <span id="info-total-amount">
                <?php echo $db_order ? 'RM ' . number_format($db_order['total_amount'], 2) : 'RM 0.00'; ?>
            </span>
        </div>
    </div>

    <div style="display: flex; gap: 1rem;">
        <a id="track-order-link" href="track.php" class="btn btn-primary" style="flex:1">
            Track Your Order
        </a>
        <a href="menu.php" class="btn btn-secondary">
            Order Again
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const mockId = "<?php echo $mock_id; ?>";
    const orderId = "<?php echo $order_id; ?>";
    const dbConnected = <?php echo $db_connected ? 'true' : 'false'; ?>;
    
    let trackUrl = 'track.php';
    
    if (dbConnected && orderId) {
        trackUrl = `track.php?id=${orderId}`;
        const email = "<?php echo $db_order ? htmlspecialchars($db_order['customer_email']) : ''; ?>";
        document.getElementById('sent-email-span').textContent = email;
    } else if (mockId) {
        const orders = getMockOrders();
        const order = orders.find(o => o.id == mockId);
        
        if (order) {
            trackUrl = `track.php?mock_id=${mockId}`;
            
            document.getElementById('info-order-type').textContent = order.table_number ? `Walk-In (Table ${order.table_number})` : 'Online Delivery';
            document.getElementById('info-customer-name').textContent = order.customer_name;
            document.getElementById('info-total-amount').textContent = `RM ${parseFloat(order.total_amount).toFixed(2)}`;
            document.getElementById('sent-email-span').textContent = order.customer_email;
            
            const container = document.getElementById('items-receipt-list');
            container.innerHTML = '';
            let subtotal = 0;
            order.items.forEach(item => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;
                const row = `
                    <div style="display:flex; justify-content:space-between; font-size:0.95rem;">
                        <span style="color:var(--text-muted);">${item.quantity}x ${item.name}</span>
                        <span>RM ${itemTotal.toFixed(2)}</span>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', row);
            });
            
            const tax = order.total_amount - subtotal;
            const subtotalEl = document.getElementById('info-subtotal-val');
            const taxEl = document.getElementById('info-tax-val');
            if (subtotalEl) subtotalEl.textContent = `RM ${subtotal.toFixed(2)}`;
            if (taxEl) taxEl.textContent = `RM ${tax.toFixed(2)}`;
        }
    }
    
    document.getElementById('track-order-link').href = trackUrl;
});
</script>

<?php
include 'includes/footer.php';
?>
