<?php
function build_order_confirmation_body($order, $items, $tracking_url) {
    $lines = [];
    $lines[] = "Thank you for ordering with Sup Tulang ZZ.";
    $lines[] = "";
    $lines[] = "Order #" . $order['id'];
    $lines[] = "Status: " . ucfirst($order['order_status']);
    $lines[] = "Type: " . ucfirst($order['order_type']);
    if (!empty($order['table_number'])) {
        $lines[] = "Table: " . $order['table_number'];
    }
    if (!empty($order['delivery_address'])) {
        $lines[] = "Delivery Address: " . $order['delivery_address'];
    }
    $lines[] = "";
    $lines[] = "Items:";
    foreach ($items as $item) {
        $line_total = number_format($item['price'] * $item['quantity'], 2);
        $lines[] = "- " . $item['quantity'] . "x " . $item['name'] . " (RM " . $line_total . ")";
    }
    $lines[] = "";
    $lines[] = "Total: RM " . number_format($order['total_amount'], 2);
    $lines[] = "Payment Status: " . ucfirst($order['payment_status']);
    $lines[] = "Track your order: " . $tracking_url;
    $lines[] = "";
    $lines[] = "Sup Tulang ZZ";

    return implode("\r\n", $lines);
}

function log_order_confirmation_email($recipient, $subject, $body) {
    $log_dir = __DIR__ . '/../logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $entry = "==== " . date('Y-m-d H:i:s') . " ====\r\n";
    $entry .= "To: " . $recipient . "\r\n";
    $entry .= "Subject: " . $subject . "\r\n\r\n";
    $entry .= $body . "\r\n\r\n";

    file_put_contents($log_dir . '/email_confirmations.log', $entry, FILE_APPEND);
}

function send_order_confirmation_email($order, $items) {
    if (empty($order['customer_email']) || !filter_var($order['customer_email'], FILTER_VALIDATE_EMAIL)) {
        return 'skipped';
    }

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base_path = rtrim(dirname($_SERVER['PHP_SELF'] ?? ''), '/\\');
    $tracking_url = $protocol . '://' . $host . $base_path . '/track.php?id=' . urlencode($order['id']);

    $subject = 'Sup Tulang ZZ Order Confirmation #' . $order['id'];
    $body = build_order_confirmation_body($order, $items, $tracking_url);
    $headers = [
        'From: Sup Tulang ZZ <no-reply@suptulangzz.local>',
        'Reply-To: no-reply@suptulangzz.local',
        'Content-Type: text/plain; charset=UTF-8',
    ];

    $sent = @mail($order['customer_email'], $subject, $body, implode("\r\n", $headers));
    if ($sent) {
        return 'sent';
    }

    log_order_confirmation_email($order['customer_email'], $subject, $body);
    return 'logged';
}
?>
