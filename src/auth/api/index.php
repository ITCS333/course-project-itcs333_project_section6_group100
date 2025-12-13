<?php
session_start();

header("Content-Type: application/json");

try {

    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            "success" => false,
            "message" => "Invalid request method"
        ]);
        exit;
    }

    // Read raw POST data
    $rawInput = file_get_contents("php://input");
    $data = json_decode($rawInput, true);

    if (!isset($data['email']) || !isset($data['password'])) {
        echo json_encode([
            "success" => false,
            "message" => "Missing credentials"
        ]);
        exit;
    }

    $email = trim($data['email']);
    $password = $data['password'];

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid email"
        ]);
        exit;
    }

    // DB connection
    $pdo = new PDO(
        "mysql:host=localhost;dbname=test",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Prepared statement
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify password
    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode([
            "success" => false,
            "message" => "Login failed"
        ]);
        exit;
    }

    // Store session
    $_SESSION['user'] = $user['email'];

    echo json_encode([
        "success" => true,
        "message" => "Login successful"
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Server error"
    ]);
}
