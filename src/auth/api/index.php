<?php
// Start PHP session
session_start();

// Set JSON response header
header('Content-Type: application/json');

try {
    // Check request method
    if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request method'
        ]);
        exit;
    }

    // Read raw JSON input
    $rawInput = file_get_contents('php://input');

    // Decode JSON data into associative array
    $data = json_decode($rawInput, true);

    // Extract email and password
    $email = isset($data['email']) ? trim($data['email']) : '';
    $password = isset($data['password']) ? (string)$data['password'] : '';

    // Validate email format
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email'
        ]);
        exit;
    }

    // Validate password existence
    if (!$password) {
        echo json_encode([
            'success' => false,
            'message' => 'Password is required'
        ]);
        exit;
    }

    // Include database connection (PDO)
    require_once __DIR__ . '/../../Config/database.php';

    // Ensure PDO connection exists
    if (!isset($pdo)) {
        throw new PDOException('PDO connection not found');
    }

    // Prepare SQL query
    $stmt = $pdo->prepare(
        "SELECT id, name, email, password, role 
         FROM users 
         WHERE email = :email 
         LIMIT 1"
    );

    // Execute prepared statement
    $stmt->execute([':email' => $email]);

    // Fetch user data
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user exists
    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid credentials'
        ]);
        exit;
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid credentials'
        ]);
        exit;
    }

    // Store user data in session
    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role']
    ];

    // Successful login response
    echo json_encode([
        'success' => true,
        'message' => 'Login successful'
    ]);
    exit;

} catch (PDOException $e) {
    // Handle database errors
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
    exit;

} catch (Exception $e) {
    // Handle general server errors
    echo json_encode([
        'success' => false,
        'message' => 'Server error'
    ]);
    exit;
}
