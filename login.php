<?php
include 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error_msg = '';
$info_msg = '';

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header("Location: login.php?logged_out=1");
    exit;
}

if (isset($_GET['logged_out'])) {
    $info_msg = "Logged out successfully.";
}

if (isset($_GET['unauthorized'])) {
    $error_msg = "Access denied. Please log in with an authorized role for that dashboard.";
}

$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';

function safe_redirect_target($redirect) {
    if (empty($redirect)) {
        return 'staff.php';
    }

    $path = parse_url($redirect, PHP_URL_PATH);
    $query = parse_url($redirect, PHP_URL_QUERY);
    $target = basename($path);
    $allowed_targets = ['staff.php', 'qr_generator.php'];

    if (!in_array($target, $allowed_targets, true)) {
        return 'staff.php';
    }

    return $query ? $target . '?' . $query : $target;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error_msg = "Please fill in all fields.";
    } else {
        if ($db_connected) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
                $stmt->execute([$username]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    header("Location: " . safe_redirect_target($redirect));
                    exit;
                } else {
                    $error_msg = "Invalid username or password.";
                }
            } catch (Exception $e) {
                $error_msg = "Login error: " . $e->getMessage();
            }
        } else {
            $mock_users = [
                ['username' => 'staff', 'password' => 'password', 'role' => 'staff', 'id' => 992],
                ['username' => 'staff1', 'password' => 'password123', 'role' => 'staff', 'id' => 992],
                ['username' => 'staff2', 'password' => 'password123', 'role' => 'staff', 'id' => 992],
                ['username' => 'admin', 'password' => 'password', 'role' => 'admin', 'id' => 993],
                ['username' => 'admin1', 'password' => 'password123', 'role' => 'admin', 'id' => 993]
            ];
            
            $found_user = null;
            foreach ($mock_users as $mu) {
                if ($mu['username'] === strtolower($username) && $mu['password'] === $password) {
                    $found_user = $mu;
                    break;
                }
            }
            
            if ($found_user) {
                $_SESSION['user_id'] = $found_user['id'];
                $_SESSION['username'] = $found_user['username'];
                $_SESSION['user_role'] = $found_user['role'];
                
                header("Location: " . safe_redirect_target($redirect));
                exit;
            } else {
                $error_msg = "Invalid mock credentials. (Use: staff1 / password123)";
            }
        }
    }
}

include 'includes/header.php';
?>

<div style="max-width: 450px; margin: 4rem auto 0 auto;">
    <div class="glass-card" style="padding: 2.5rem 2rem;">
        <div style="text-align:center; margin-bottom:2rem;">
            <i class="fa-solid fa-lock" style="font-size: 3rem; color: var(--primary); filter: drop-shadow(0 0 8px var(--primary-glow)); margin-bottom: 1rem;"></i>
            <h3>Portal Login</h3>
            <p style="color:var(--text-muted); font-size:0.9rem;">Sign in to access your role-based dashboard</p>
        </div>
        
        <?php if (!empty($error_msg)): ?>
            <div style="background:rgba(239, 68, 68, 0.1); border:1px solid rgba(239, 68, 68, 0.3); color:var(--danger); border-radius:var(--radius-sm); padding:0.75rem 1rem; margin-bottom:1.5rem; font-size:0.9rem;">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($info_msg)): ?>
            <div style="background:rgba(16, 185, 129, 0.1); border:1px solid rgba(16, 185, 129, 0.3); color:var(--success); border-radius:var(--radius-sm); padding:0.75rem 1rem; margin-bottom:1.5rem; font-size:0.9rem;">
                <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($info_msg); ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" placeholder="Enter username" required autocomplete="username">
            </div>
            
            <div class="form-group" style="margin-bottom:2rem;">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required autocomplete="current-password">
            </div>
            
            <button type="submit" class="btn btn-primary btn-full">
                Log In <i class="fa-solid fa-right-to-bracket"></i>
            </button>
        </form>
    </div>
    

</div>

<?php
include 'includes/footer.php';
?>
