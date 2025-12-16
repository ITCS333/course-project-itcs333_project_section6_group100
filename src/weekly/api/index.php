<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$_SESSION['user'] = $_SESSION['user'] ?? null;

try {
    require_once '../db/connection.php'; // or your actual DB connection

  
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $json = file_get_contents('php://input');

       
        $data = json_decode($json, true);

        
        if (!isset($data['email']) || !isset($data['password'])) {
            echo json_encode(['success' => false, 'error' => 'Missing fields']);
            exit;
        }

        
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Invalid email']);
            exit;
        }

       
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

       
        if ($user && password_verify($data['password'], $user['password'])) {
            $_SESSION['user'] = $user['id'];
            echo json_encode(['success' => true, 'message' => 'Login successful']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
