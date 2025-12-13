<?php
require_once __DIR__ . '/config.php';

$host = 'localhost';
$db   = 'itcs333_project'; // MUST match DB name in phpMyAdmin
$user = 'root';            // default in XAMPP
$pass = '';                // empty if you didn't set a password

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
