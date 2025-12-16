<?php
session_start();
$_SESSION['user'] = $_SESSION['user'] ?? 'guest';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

function getAllWeeks($db) {
    // TODO: Initialize variables for search, sort, and order from query parameters
    $search = isset($_GET['search']) ? $_GET['search'] : null;
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'start_date';
    $order = isset($_GET['order']) ? strtolower($_GET['order']) : 'asc';

    // TODO: Start building the SQL query
    // Base query: SELECT week_id, title, start_date, description, links, created_at FROM weeks
    $query = "SELECT week_id, title, start_date, description, links, created_at FROM weeks";

    // TODO: Check if search parameter exists
    // If yes, add WHERE clause using LIKE for title and description
    if ($search) {
        $query .= " WHERE title LIKE :search OR description LIKE :search";
    }

    // TODO: Check if sort parameter exists
    // Validate sort field to prevent SQL injection (only allow: title, start_date, created_at)
    $allowedSortFields = ['title', 'start_date', 'created_at'];
    if (!in_array($sort, $allowedSortFields)) {
        $sort = 'start_date';
    }

    // TODO: Check if order parameter exists
    // Validate order to prevent SQL injection (only allow: asc, desc)
    $allowedOrders = ['asc', 'desc'];
    if (!in_array($order, $allowedOrders)) {
        $order = 'asc';
    }

    // TODO: Add ORDER BY clause to the query
    $query .= " ORDER BY $sort $order";

    // TODO: Prepare the SQL query using PDO
    $stmt = $db->prepare($query);

    // TODO: Bind parameters if using search
    if ($search) {
        $searchTerm = "%{$search}%";
        $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
    }

    // TODO: Execute the query
    $stmt->execute();

    // TODO: Fetch all results as an associative array
    $weeks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // TODO: Process each week's links field
    // Decode the JSON string back to an array using json_decode()
    foreach ($weeks as &$week) {
        $week['links'] = json_decode($week['links'], true);
    }

    // TODO: Return JSON response with success status and data
    // Use sendResponse() helper function
    sendResponse(true, $weeks);
}


function getWeekById($db, $weekId) {
    // TODO: Validate that week_id is provided
    // If not, return error response with 400 status
    if (empty($weekId)) {
        sendResponse(false, null, "week_id is required", 400);
        return;
    }

    // TODO: Prepare SQL query to select week by week_id
    // SELECT week_id, title, start_date, description, links, created_at FROM weeks WHERE week_id = ?
    $query = "SELECT week_id, title, start_date, description, links, created_at FROM weeks WHERE week_id = ?";

    // TODO: Bind the week_id parameter
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $weekId, PDO::PARAM_STR);

    // TODO: Execute the query
    $stmt->execute();

    // TODO: Fetch the result
    $week = $stmt->fetch(PDO::FETCH_ASSOC);

    // TODO: Check if week exists
    // If yes, decode the links JSON and return success response with week data
    // If no, return error response with 404 status
    if ($week) {
        $week['links'] = json_decode($week['links'], true);
        sendResponse(true, $week);
    } else {
        sendResponse(false, null, "Week not found", 404);
    }
}


function createWeek($db, $data) {
    // TODO: Validate required fields
    // Check if week_id, title, start_date, and description are provided
    // If any field is missing, return error response with 400 status
    if (empty($data['week_id']) || empty($data['title']) || empty($data['start_date']) || empty($data['description'])) {
        sendResponse(false, null, "Missing required fields", 400);
        return;
    }

    // TODO: Sanitize input data
    // Trim whitespace from title, description, and week_id
    $weekId = trim($data['week_id']);
    $title = trim($data['title']);
    $description = trim($data['description']);
    $startDate = trim($data['start_date']);

    // TODO: Validate start_date format
    // Use a regex or DateTime::createFromFormat() to verify YYYY-MM-DD format
    // If invalid, return error response with 400 status
    $d = DateTime::createFromFormat('Y-m-d', $startDate);
    if (!$d || $d->format('Y-m-d') !== $startDate) {
        sendResponse(false, null, "Invalid start_date format", 400);
        return;
    }

    // TODO: Check if week_id already exists
    // Prepare and execute a SELECT query to check for duplicates
    // If duplicate found, return error response with 409 status (Conflict)
    $checkQuery = "SELECT week_id FROM weeks WHERE week_id = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(1, $weekId, PDO::PARAM_STR);
    $checkStmt->execute();
    if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
        sendResponse(false, null, "week_id already exists", 409);
        return;
    }

    // TODO: Handle links array
    // If links is provided and is an array, encode it to JSON using json_encode()
    // If links is not provided, use an empty array []
    $links = isset($data['links']) && is_array($data['links']) ? json_encode($data['links']) : json_encode([]);

    // TODO: Prepare INSERT query
    // INSERT INTO weeks (week_id, title, start_date, description, links) VALUES (?, ?, ?, ?, ?)
    $insertQuery = "INSERT INTO weeks (week_id, title, start_date, description, links) VALUES (?, ?, ?, ?, ?)";

    // TODO: Bind parameters
    $stmt = $db->prepare($insertQuery);
    $stmt->bindParam(1, $weekId);
    $stmt->bindParam(2, $title);
    $stmt->bindParam(3, $startDate);
    $stmt->bindParam(4, $description);
    $stmt->bindParam(5, $links);

    // TODO: Execute the query
    $success = $stmt->execute();

    // TODO: Check if insert was successful
    // If yes, return success response with 201 status (Created) and the new week data
    // If no, return error response with 500 status
    if ($success) {
        $newWeek = [
            'week_id' => $weekId,
            'title' => $title,
            'start_date' => $startDate,
            'description' => $description,
            'links' => json_decode($links, true)
        ];
        sendResponse(true, $newWeek, null, 201);
    } else {
        sendResponse(false, null, "Failed to create week", 500);
    }
}


function updateWeek($db, $data) {
    // TODO: Validate that week_id is provided
    // If not, return error response with 400 status
    if (empty($data['week_id'])) {
        sendResponse(false, null, "week_id is required", 400);
        return;
    }
    $weekId = trim($data['week_id']);

    // TODO: Check if week exists
    // Prepare and execute a SELECT query to find the week
    // If not found, return error response with 404 status
    $checkQuery = "SELECT week_id FROM weeks WHERE week_id = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(1, $weekId, PDO::PARAM_STR);
    $checkStmt->execute();
    if (!$checkStmt->fetch(PDO::FETCH_ASSOC)) {
        sendResponse(false, null, "Week not found", 404);
        return;
    }

    // TODO: Build UPDATE query dynamically based on provided fields
    // Initialize an array to hold SET clauses
    // Initialize an array to hold values for binding
    $setClauses = [];
    $values = [];

    // TODO: Check which fields are provided and add to SET clauses
    // If title is provided, add "title = ?"
    if (!empty($data['title'])) {
        $setClauses[] = "title = ?";
        $values[] = trim($data['title']);
    }

    // If start_date is provided, validate format and add "start_date = ?"
    if (!empty($data['start_date'])) {
        $d = DateTime::createFromFormat('Y-m-d', $data['start_date']);
        if (!$d || $d->format('Y-m-d') !== $data['start_date']) {
            sendResponse(false, null, "Invalid start_date format", 400);
            return;
        }
        $setClauses[] = "start_date = ?";
        $values[] = trim($data['start_date']);
    }

    // If description is provided, add "description = ?"
    if (!empty($data['description'])) {
        $setClauses[] = "description = ?";
        $values[] = trim($data['description']);
    }

    // If links is provided, encode to JSON and add "links = ?"
    if (isset($data['links'])) {
        $setClauses[] = "links = ?";
        $values[] = json_encode($data['links']);
    }

    // TODO: If no fields to update, return error response with 400 status
    if (empty($setClauses)) {
        sendResponse(false, null, "No fields to update", 400);
        return;
    }

    // TODO: Add updated_at timestamp to SET clauses
    // Add "updated_at = CURRENT_TIMESTAMP"
    $setClauses[] = "updated_at = CURRENT_TIMESTAMP";

    // TODO: Build the complete UPDATE query
    // UPDATE weeks SET [clauses] WHERE week_id = ?
    $updateQuery = "UPDATE weeks SET " . implode(", ", $setClauses) . " WHERE week_id = ?";

    // TODO: Prepare the query
    $stmt = $db->prepare($updateQuery);

    // TODO: Bind parameters dynamically
    // Bind values array and then bind week_id at the end
    foreach ($values as $i => $val) {
        $stmt->bindValue($i + 1, $val);
    }
    $stmt->bindValue(count($values) + 1, $weekId);

    // TODO: Execute the query
    $success = $stmt->execute();

    // TODO: Check if update was successful
    // If yes, return success response with updated week data
    // If no, return error response with 500 status
    if ($success) {
        getWeekById($db, $weekId);
    } else {
        sendResponse(false, null, "Failed to update week", 500);
    }
}


function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

function sendError($message, $statusCode = 400) {
    $error = ['success' => false, 'error' => $message];
    sendResponse($error, $statusCode);
}

function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = strip_tags($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function isValidSortField($field, $allowedFields) {
    return in_array($field, $allowedFields);
}
?>
