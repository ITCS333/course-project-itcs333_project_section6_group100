<?php
require_once __DIR__ . '/db.php';

// Create table if it doesn't exist
$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    student_id VARCHAR(50) NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','student') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// Check if an admin already exists
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM users WHERE role = 'admin'");
$count = (int)$stmt->fetch()['cnt'];

if ($count > 0) {
    echo "Admin already exists.";
    exit;
}

$name  = 'Admin User';
$email = 'admin@example.com';
$role  = 'admin';
$pwd   = 'admin123';

$hash = password_hash($pwd, PASSWORD_BCRYPT);

$insert = $pdo->prepare("
    INSERT INTO users (name, student_id, email, password_hash, role)
    VALUES (?, NULL, ?, ?, ?)
");
$insert->execute([$name, $email, $hash, $role]);

echo "Admin user created.<br>";
echo "Email: {$email}<br>";
echo "Password: {$pwd}<br>";
