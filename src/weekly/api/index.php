
<?php
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
    sendResponse(true, $weeks)


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




function deleteWeek($db, $weekId) {
    // TODO: Validate that week_id is provided
    // If not, return error response with 400 status
    if (empty($weekId)) {
        sendResponse(false, null, "week_id is required", 400);
        return;
    }

    // TODO: Check if week exists
    // Prepare and execute a SELECT query
    // If not found, return error response with 404 status
    $checkQuery = "SELECT week_id FROM weeks WHERE week_id = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(1, $weekId, PDO::PARAM_STR);
    $checkStmt->execute();
    if (!$checkStmt->fetch(PDO::FETCH_ASSOC)) {
        sendResponse(false, null, "Week not found", 404);
        return;
    }

    // TODO: Delete associated comments first (to maintain referential integrity)
    // Prepare DELETE query for comments table
    // DELETE FROM comments WHERE week_id = ?
    $deleteCommentsQuery = "DELETE FROM comments WHERE week_id = ?";
    $deleteCommentsStmt = $db->prepare($deleteCommentsQuery);

    // TODO: Execute comment deletion query
    $deleteCommentsStmt->bindParam(1, $weekId, PDO::PARAM_STR);
    $deleteCommentsStmt->execute();

    // TODO: Prepare DELETE query for week
    // DELETE FROM weeks WHERE week_id = ?
    $deleteWeekQuery = "DELETE FROM weeks WHERE week_id = ?";

    // TODO: Bind the week_id parameter
    $deleteWeekStmt = $db->prepare($deleteWeekQuery);
    $deleteWeekStmt->bindParam(1, $weekId, PDO::PARAM_STR);

    // TODO: Execute the query
    $success = $deleteWeekStmt->execute();

    // TODO: Check if delete was successful
    // If yes, return success response with message indicating week and comments deleted
    // If no, return error response with 500 status
    if ($success) {
        sendResponse(true, null, "Week and associated comments deleted successfully");
    } else {
        sendResponse(false, null, "Failed to delete week", 500);
    }
}


function getCommentsByWeek($db, $weekId) {
    // TODO: Validate that week_id is provided
    // If not, return error response with 400 status
    if (empty($weekId)) {
        sendResponse(false, null, "week_id is required", 400);
        return;
    }

    // TODO: Prepare SQL query to select comments for the week
    // SELECT id, week_id, author, text, created_at FROM comments WHERE week_id = ? ORDER BY created_at ASC
    $query = "SELECT id, week_id, author, text, created_at FROM comments WHERE week_id = ? ORDER BY created_at ASC";

    // TODO: Bind the week_id parameter
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $weekId, PDO::PARAM_STR);

    // TODO: Execute the query
    $stmt->execute();

    // TODO: Fetch all results as an associative array
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // TODO: Return JSON response with success status and data
    // Even if no comments exist, return an empty array
    sendResponse(true, $comments);
}


function createComment($db, $data) {
    // TODO: Validate required fields
    // Check if week_id, author, and text are provided
    // If any field is missing, return error response with 400 status
    if (empty($data['week_id']) || empty($data['author']) || empty($data['text'])) {
        sendResponse(false, null, "Missing required fields", 400);
        return;
    }

    // TODO: Sanitize input data
    // Trim whitespace from all fields
    $weekId = trim($data['week_id']);
    $author = trim($data['author']);
    $text = trim($data['text']);

    // TODO: Validate that text is not empty after trimming
    // If empty, return error response with 400 status
    if (empty($text)) {
        sendResponse(false, null, "Comment text cannot be empty", 400);
        return;
    }

    // TODO: Check if the week exists
    // Prepare and execute a SELECT query on weeks table
    // If week not found, return error response with 404 status
    $checkQuery = "SELECT week_id FROM weeks WHERE week_id = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(1, $weekId, PDO::PARAM_STR);
    $checkStmt->execute();
    if (!$checkStmt->fetch(PDO::FETCH_ASSOC)) {
        sendResponse(false, null, "Week not found", 404);
        return;
    }

    // TODO: Prepare INSERT query
    // INSERT INTO comments (week_id, author, text) VALUES (?, ?, ?)
    $insertQuery = "INSERT INTO comments (week_id, author, text) VALUES (?, ?, ?)";

    // TODO: Bind parameters
    $stmt = $db->prepare($insertQuery);
    $stmt->bindParam(1, $weekId);
    $stmt->bindParam(2, $author);
    $stmt->bindParam(3, $text);

    // TODO: Execute the query
    $success = $stmt->execute();

    // TODO: Check if insert was successful
    // If yes, get the last insert ID and return success response with 201 status
    // Include the new comment data in the response
    // If no, return error response with 500 status
    if ($success) {
        $newComment = [
            'id' => $db->lastInsertId(),
            'week_id' => $weekId,
            'author' => $author,
            'text' => $text,
            'created_at' => date('Y-m-d H:i:s')
        ];
        sendResponse(true, $newComment, null, 201);
    } else {
        sendResponse(false, null, "Failed to create comment", 500);
    }
}


function deleteComment($db, $commentId) {
    // TODO: Validate that id is provided
    // If not, return error response with 400 status
    if (empty($commentId)) {
        sendResponse(false, null, "commentId is required", 400);
        return;
    }

    // TODO: Check if comment exists
    // Prepare and execute a SELECT query
    // If not found, return error response with 404 status
    $checkQuery = "SELECT id FROM comments WHERE id = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(1, $commentId, PDO::PARAM_INT);
    $checkStmt->execute();
    if (!$checkStmt->fetch(PDO::FETCH_ASSOC)) {
        sendResponse(false, null, "Comment not found", 404);
        return;
    }

    $deleteQuery = "DELETE FROM comments WHERE id = ?";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->bindParam(1, $commentId, PDO::PARAM_INT);
    $success = $deleteStmt->execute();

    if ($success) {
        sendResponse(true, null, "Comment deleted successfully");
    } else {
        sendResponse(false, null, "Failed to delete comment", 500);
    }
}

 // TODO: Prepare DELETE query
    // DELETE FROM comments WHERE id = ?
    $deleteQuery = "DELETE FROM comments WHERE id = ?";
    $deleteStmt = $db->prepare($deleteQuery);

    // TODO: Bind the id parameter
    $deleteStmt->bindParam(1, $commentId, PDO::PARAM_INT);

    // TODO: Execute the query
    $success = $deleteStmt->execute();

    // TODO: Check if delete was successful
    // If yes, return success response
    // If no, return error response with 500 status
    if ($success) {
        sendResponse(['success' => true, 'message' => 'Comment deleted successfully']);
    } else {
        sendError("Failed to delete comment", 500);
    }
}


// ============================================================================
// MAIN REQUEST ROUTER
// ============================================================================

try {
    // TODO: Determine the resource type from query parameters
    // Get 'resource' parameter (?resource=weeks or ?resource=comments)
    // If not provided, default to 'weeks'
    $resource = isset($_GET['resource']) ? $_GET['resource'] : 'weeks';
    $method = $_SERVER['REQUEST_METHOD'];
    $body = json_decode(file_get_contents('php://input'), true);

    // Route based on resource type and HTTP method

    // ========== WEEKS ROUTES ==========
    if ($resource === 'weeks') {

        if ($method === 'GET') {
            // TODO: Check if week_id is provided in query parameters
            // If yes, call getWeekById()
            // If no, call getAllWeeks() to get all weeks (with optional search/sort)
            if (isset($_GET['week_id'])) {
                getWeekById($db, $_GET['week_id']);
            } else {
                getAllWeeks($db);
            }

        } elseif ($method === 'POST') {
            // TODO: Call createWeek() with the decoded request body
            createWeek($db, $body);

        } elseif ($method === 'PUT') {
            // TODO: Call updateWeek() with the decoded request body
            updateWeek($db, $body);

        } elseif ($method === 'DELETE') {
            // TODO: Get week_id from query parameter or request body
            // Call deleteWeek()
            $weekId = isset($_GET['week_id']) ? $_GET['week_id'] : ($body['week_id'] ?? null);
            deleteWeek($db, $weekId);

        } else {
            // TODO: Return error for unsupported methods
            // Set HTTP status to 405 (Method Not Allowed)
            sendError("Method Not Allowed", 405);
        }
    }

    // ========== COMMENTS ROUTES ==========
    elseif ($resource === 'comments') {

        if ($method === 'GET') {
            // TODO: Get week_id from query parameters
            // Call getCommentsByWeek()
            if (isset($_GET['week_id'])) {
                getCommentsByWeek($db, $_GET['week_id']);
            } else {
                sendError("week_id is required", 400);
            }

        } elseif ($method === 'POST') {
            // TODO: Call createComment() with the decoded request body
            createComment($db, $body);

        } elseif ($method === 'DELETE') {
            // TODO: Get comment id from query parameter or request body
            // Call deleteComment()
            $commentId = isset($_GET['id']) ? $_GET['id'] : ($body['id'] ?? null);
            deleteComment($db, $commentId);

        } else {
            // TODO: Return error for unsupported methods
            // Set HTTP status to 405 (Method Not Allowed)
            sendError("Method Not Allowed", 405);
        }
    }

    // ========== INVALID RESOURCE ==========
    else {
        // TODO: Return error for invalid resource
        // Set HTTP status to 400 (Bad Request)
        // Return JSON error message: "Invalid resource. Use 'weeks' or 'comments'"
        sendError("Invalid resource. Use 'weeks' or 'comments'", 400);
    }

} catch (PDOException $e) {
    // TODO: Handle database errors
    // Log the error message (optional, for debugging)
    // error_log($e->getMessage());

    // TODO: Return generic error response with 500 status
    // Do NOT expose database error details to the client
    // Return message: "Database error occurred"
    sendError("Database error occurred", 500);

} catch (Exception $e) {
    // TODO: Handle general errors
    // Log the error message (optional)
    // Return error response with 500 status
    sendError("An error occurred", 500);
}


// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Helper function to send JSON response
 * 
 * @param mixed $data - Data to send (will be JSON encoded)
 * @param int $statusCode - HTTP status code (default: 200)
 */
function sendResponse($data, $statusCode = 200) {
    // TODO: Set HTTP response code
    // Use http_response_code($statusCode)
    http_response_code($statusCode);

    // TODO: Echo JSON encoded data
    // Use json_encode($data)
    echo json_encode($data);

    // TODO: Exit to prevent further execution
    exit;
}


/**
 * Helper function to send error response
 * 
 * @param string $message - Error message
 * @param int $statusCode - HTTP status code
 */
function sendError($message, $statusCode = 400) {
    // TODO: Create error response array
    // Structure: ['success' => false, 'error' => $message]
    $error = ['success' => false, 'error' => $message];

    // TODO: Call sendResponse() with the error array and status code
    sendResponse($error, $statusCode);
}


/**
 * Helper function to validate date format (YYYY-MM-DD)
 * 
 * @param string $date - Date string to validate
 * @return bool - True if valid, false otherwise
 */
function validateDate($date) {
    // TODO: Use DateTime::createFromFormat() to validate
    // Format: 'Y-m-d'
    // Check that the created date matches the input string
    // Return true if valid, false otherwise
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}


/**
 * Helper function to sanitize input
 * 
 * @param string $data - Data to sanitize
 * @return string - Sanitized data
 */
function sanitizeInput($data) {
    // TODO: Trim whitespace
    $data = trim($data);

    // TODO: Strip HTML tags using strip_tags()
    $data = strip_tags($data);

    // TODO: Convert special characters using htmlspecialchars()
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

    // TODO: Return sanitized data
    return $data;
}


/**
 * Helper function to validate allowed sort fields
 * 
 * @param string $field - Field name to validate
 * @param array $allowedFields - Array of allowed field names
 * @return bool - True if valid, false otherwise
 */
function isValidSortField($field, $allowedFields) {
    // TODO: Check if $field exists in $allowedFields array
    // Use in_array()
    // Return true if valid, false otherwise
    return in_array($field, $allowedFields);
}

?> 




    
   
