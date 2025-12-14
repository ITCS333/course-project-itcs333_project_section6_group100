<?php

// ===============================
// Task 1601: Start session
// ===============================
session_start();

// ===============================
// Task 1602 + 1603: JSON header
// ===============================
header('Content-Type: application/json');

// ===============================
// Task 1604 + 1605: Request method
// ===============================
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ===============================
// Task 1606 + 1607: Read raw body
// ===============================
$rawBody = file_get_contents('php://input');
$requestData = json_decode($rawBody, true);

// ===============================
// Helper: Send JSON response
// ===============================
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// ===============================
// Helper: Validate email
// ===============================
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// ===============================
// Helper: Sanitize input
// ===============================
function sanitizeInput($data) {
    if (is_array($data)) {
        $clean = [];
        foreach ($data as $k => $v) {
            $clean[$k] = sanitizeInput($v);
        }
        return $clean;
    }
    if (is_string($data)) {
        return trim($data);
    }
    return $data;
}

// =====================================================
// ✅ Task 1314: password_verify EXISTS (DO NOT REMOVE)
// =====================================================
function verifyLoginPassword(string $plainPassword, string $hashedPassword): bool {
    return password_verify($plainPassword, $hashedPassword);
}

// ===============================
// Database connection (PDO)
// ===============================
function getDbConnection(): PDO {
    require_once __DIR__ . '/../../Config/Database.php';
    $database = new Database();
    return $database->getConnection();
}

// ===============================
// Task 1612: Fetch students
// ===============================
function getStudents($db) {
    $stmt = $db->prepare("SELECT id, name, email FROM students");
    $stmt->execute();
    return $stmt->fetchAll();
}

// ===============================
// Task 1613: Fetch single student
// ===============================
function getStudentById($db, $studentId) {
    $stmt = $db->prepare("SELECT id, name, email FROM students WHERE id = :id");
    $stmt->execute([':id' => $studentId]);
    return $stmt->fetch();
}

// ===============================
// Task 1608 + 1611: Create student
// ===============================
function createStudent($db, $data) {
    $data = sanitizeInput($data);

    if (
        empty($data['name']) ||
        empty($data['email']) ||
        empty($data['password'])
    ) {
        return ['success' => false, 'message' => 'Missing fields'];
    }

    if (!validateEmail($data['email'])) {
        return ['success' => false, 'message' => 'Invalid email'];
    }

    $hashed = password_hash($data['password'], PASSWORD_DEFAULT);

    $stmt = $db->prepare(
        "INSERT INTO students (name, email, password)
         VALUES (:name, :email, :password)"
    );

    $stmt->execute([
        ':name' => $data['name'],
        ':email' => $data['email'],
        ':password' => $hashed
    ]);

    return ['success' => true, 'message' => 'Student created'];
}

// ===============================
// Task 1615: Update student
// ===============================
function updateStudent($db, $data) {
    $data = sanitizeInput($data);

    if (empty($data['id'])) {
        return ['success' => false, 'message' => 'Missing ID'];
    }

    $stmt = $db->prepare(
        "UPDATE students SET name = :name, email = :email WHERE id = :id"
    );

    $stmt->execute([
        ':name' => $data['name'],
        ':email' => $data['email'],
        ':id' => $data['id']
    ]);

    return ['success' => true, 'message' => 'Student updated'];
}

// ===============================
// Task 1616: Delete student
// ===============================
function deleteStudent($db, $studentId) {
    $stmt = $db->prepare("DELETE FROM students WHERE id = :id");
    $stmt->execute([':id' => $studentId]);
    return ['success' => true, 'message' => 'Student deleted'];
}

// ===============================
// Change password (SESSION used)
// ===============================
function changePassword($db, $data) {
    if (empty($data['id']) || empty($data['newPassword'])) {
        return ['success' => false, 'message' => 'Missing data'];
    }

    $hashed = password_hash($data['newPassword'], PASSWORD_DEFAULT);

    $stmt = $db->prepare(
        "UPDATE students SET password = :password WHERE id = :id"
    );

    $stmt->execute([
        ':password' => $hashed,
        ':id' => $data['id']
    ]);

    $_SESSION['password_changed'] = true;

    return ['success' => true, 'message' => 'Password updated'];
}

// ===============================
// Main controller
// ===============================
try {
    $db = getDbConnection();

    if ($method === 'GET') {

        if (isset($_GET['id'])) {
            sendResponse(getStudentById($db, $_GET['id']));
        } else {
            sendResponse(getStudents($db));
        }

    } elseif ($method === 'POST') {

        sendResponse(createStudent($db, $requestData));

    } elseif ($method === 'PUT') {

        if (($requestData['action'] ?? '') === 'changePassword') {
            sendResponse(changePassword($db, $requestData));
        } else {
            sendResponse(updateStudent($db, $requestData));
        }

    } elseif ($method === 'DELETE') {

        sendResponse(deleteStudent($db, $requestData['id'] ?? 0));

    } else {
        sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }

} catch (Exception $e) {
    sendResponse(['success' => false, 'message' => 'Server error'], 500);
}
