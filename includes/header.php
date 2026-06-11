<?php
require_once __DIR__ . '/auth.php';

if (isset($_GET['table'])) {
    $_SESSION['table_number'] = intval($_GET['table']);
    $_SESSION['order_type'] = 'walk-in';
}

$table_number = isset($_SESSION['table_number']) ? $_SESSION['table_number'] : null;
$order_type = isset($_SESSION['order_type']) ? $_SESSION['order_type'] : 'online';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sup Tulang ZZ - Restaurant Order Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <script>
        localStorage.setItem('order_type', '<?php echo $order_type; ?>');
        <?php if ($table_number): ?>
            localStorage.setItem('table_number', '<?php echo $table_number; ?>');
        <?php else: ?>
            localStorage.removeItem('table_number');
        <?php endif; ?>
    </script>
</head>
<body>
    <header>
        <div class="nav-container">
            <a href="index.php" class="logo-link">
                <div class="logo-icon"><i class="fa-solid fa-bowl-hot"></i></div>
                <div class="logo-text">Sup Tulang <span>ZZ</span></div>
            </a>
            
            <ul class="nav-menu">
                <li>
                    <a href="menu.php" class="nav-link <?php echo ($current_page === 'menu.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-utensils"></i> Menu
                    </a>
                </li>
                <li>
                    <a href="cart.php" class="nav-link <?php echo ($current_page === 'cart.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-cart-shopping"></i> Cart
                        <span class="badge cart-badge">0</span>
                    </a>
                </li>
                <li>
                    <a href="track.php" class="nav-link <?php echo ($current_page === 'track.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-map-location-dot"></i> Track
                    </a>
                </li>
                <?php if (is_logged_in()): ?>
                    <li>
                        <a href="staff.php" class="nav-link <?php echo ($current_page === 'staff.php') ? 'active' : ''; ?>">
                            <i class="fa-solid fa-chart-line"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="login.php?action=logout" class="nav-link" style="color: var(--danger);">
                            <i class="fa-solid fa-right-from-bracket"></i> Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="login.php" class="nav-link <?php echo ($current_page === 'login.php') ? 'active' : ''; ?>">
                            <i class="fa-solid fa-right-to-bracket"></i> Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </header>
    
    <?php if ($order_type === 'walk-in' && $table_number): ?>
    <div style="background: rgba(245, 158, 11, 0.1); border-bottom: 1px solid var(--border-color); text-align: center; padding: 0.5rem; font-size: 0.9rem;">
        <span style="color: var(--primary); font-weight: 600;">🛎️ Walk-In Mode:</span> You are ordering for <strong>Table <?php echo htmlspecialchars($table_number); ?></strong>. 
        <a href="index.php?reset_session=1" style="color: var(--text-main); margin-left: 10px; text-decoration: underline;">Switch to Online</a>
    </div>
    <?php endif; ?>
    
    <main>
