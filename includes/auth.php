<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

function check_role($required_role) {
    if (!is_logged_in()) {
        $current_url = basename($_SERVER['PHP_SELF']);
        if (!empty($_SERVER['QUERY_STRING'])) {
            $current_url .= '?' . $_SERVER['QUERY_STRING'];
        }
        header("Location: login.php?redirect=" . urlencode($current_url));
        exit;
    }
    
    $user_role = $_SESSION['user_role'];
    $allowed_employee_roles = ['admin', 'staff'];
    
    if (!in_array($user_role, $allowed_employee_roles)) {
        header("Location: login.php?unauthorized=1");
        exit;
    }
}
?>
