<?php
$host = 'localhost';
$db   = 'restaurant_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = null;
$db_error = null;
$db_connected = false;

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db`");
    
    $tableExists = false;
    try {
        $result = $pdo->query("SELECT 1 FROM users LIMIT 1");
        $tableExists = true;
    } catch (Exception $e) {
        $tableExists = false;
    }

    if (!$tableExists) {
        $schemaPath = __DIR__ . '/../database/schema.sql';
        if (file_exists($schemaPath)) {
            $sql = file_get_contents($schemaPath);
            $queries = explode(';', $sql);
            foreach ($queries as $query) {
                $query = trim($query);
                if (!empty($query)) {
                    $pdo->exec($query);
                }
            }
        }
    }
    
    $db_connected = true;
} catch (\PDOException $e) {
    $db_error = $e->getMessage();
    $db_connected = false;
}

if (isset($_GET['check_db'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'connected' => $db_connected,
        'error' => $db_error
    ]);
    exit;
}
?>
