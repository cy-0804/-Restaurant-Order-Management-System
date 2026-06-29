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

function load_mail_config() {
    $config_path = __DIR__ . '/../config/mail.php';
    if (!file_exists($config_path)) {
        return null;
    }

    $config = require $config_path;
    if (!is_array($config) || empty($config['enabled'])) {
        return null;
    }

    $required_keys = ['host', 'port', 'username', 'password', 'from_email', 'from_name'];
    foreach ($required_keys as $key) {
        if (empty($config[$key]) || strpos((string)$config[$key], 'your-') === 0) {
            return null;
        }
    }

    return $config;
}

function smtp_read_response($socket) {
    $response = '';
    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (strlen($line) >= 4 && $line[3] === ' ') {
            break;
        }
    }
    return $response;
}

function smtp_command($socket, $command, $expected_codes) {
    fwrite($socket, $command . "\r\n");
    $response = smtp_read_response($socket);
    $code = (int)substr($response, 0, 3);
    if (!in_array($code, $expected_codes, true)) {
        throw new Exception("SMTP command failed: " . trim($response));
    }
    return $response;
}

function smtp_escape_body($body) {
    $body = str_replace(["\r\n", "\r"], "\n", $body);
    $lines = explode("\n", $body);
    foreach ($lines as &$line) {
        if (isset($line[0]) && $line[0] === '.') {
            $line = '.' . $line;
        }
    }
    return implode("\r\n", $lines);
}

function send_smtp_email($config, $recipient_email, $recipient_name, $subject, $body) {
    $socket = fsockopen($config['host'], (int)$config['port'], $errno, $errstr, 20);
    if (!$socket) {
        throw new Exception("Could not connect to SMTP server: $errstr ($errno)");
    }

    stream_set_timeout($socket, 20);

    try {
        $response = smtp_read_response($socket);
        if ((int)substr($response, 0, 3) !== 220) {
            throw new Exception("SMTP greeting failed: " . trim($response));
        }

        smtp_command($socket, 'EHLO localhost', [250]);
        smtp_command($socket, 'STARTTLS', [220]);

        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new Exception("Could not enable SMTP TLS encryption.");
        }

        smtp_command($socket, 'EHLO localhost', [250]);
        smtp_command($socket, 'AUTH LOGIN', [334]);
        smtp_command($socket, base64_encode($config['username']), [334]);
        smtp_command($socket, base64_encode($config['password']), [235]);
        smtp_command($socket, 'MAIL FROM:<' . $config['from_email'] . '>', [250]);
        smtp_command($socket, 'RCPT TO:<' . $recipient_email . '>', [250, 251]);
        smtp_command($socket, 'DATA', [354]);

        $encoded_subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $headers = [
            'From: ' . $config['from_name'] . ' <' . $config['from_email'] . '>',
            'To: ' . $recipient_name . ' <' . $recipient_email . '>',
            'Subject: ' . $encoded_subject,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ];

        fwrite($socket, implode("\r\n", $headers) . "\r\n\r\n" . smtp_escape_body($body) . "\r\n.\r\n");
        $response = smtp_read_response($socket);
        $code = (int)substr($response, 0, 3);
        if (!in_array($code, [250], true)) {
            throw new Exception("SMTP message send failed: " . trim($response));
        }

        smtp_command($socket, 'QUIT', [221]);
    } finally {
        fclose($socket);
    }
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
    $config = load_mail_config();

    if ($config) {
        try {
            send_smtp_email($config, $order['customer_email'], $order['customer_name'], $subject, $body);
            return 'sent';
        } catch (Exception $e) {
            log_order_confirmation_email(
                $order['customer_email'],
                $subject,
                $body . "\r\n\r\nSMTP delivery failed: " . $e->getMessage()
            );
            return 'logged';
        }
    }

    $headers = [
        'From: Sup Tulang ZZ <no-reply@suptulangzz.local>',
        'Reply-To: no-reply@suptulangzz.local',
        'Content-Type: text/plain; charset=UTF-8',
    ];

    $sent = @mail($order['customer_email'], $subject, $body, implode("\r\n", $headers));
    if ($sent) {
        log_order_confirmation_email($order['customer_email'], $subject, $body);
        return 'queued';
    }

    log_order_confirmation_email($order['customer_email'], $subject, $body);
    return 'logged';
}
?>
