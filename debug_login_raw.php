<?php

$host = 'localhost';
$db   = 'rasidev_hr';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    echo "Connecting to DB...\n";
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Connected.\n";
    
    $email = 'admin@rasidev.com';
    $password = '12345678';
    
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "User NOT FOUND.\n";
        $stmt = $pdo->query('SELECT COUNT(*) FROM users');
        echo "Total users: " . $stmt->fetchColumn() . "\n";
    } else {
        echo "User FOUND.\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Stored Hash: " . $user['password'] . "\n";
        
        if (password_verify($password, $user['password'])) {
            echo "Password verification PASSED.\n";
        } else {
            echo "Password verification FAILED.\n";
            echo "Expected logic used: password_verify('12345678', '" . substr($user['password'], 0, 10) . "...')\n";
        }
    }

} catch (\PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
