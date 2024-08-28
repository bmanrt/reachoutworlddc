<?php
// Set headers for JSON response and to accept JSON request
header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Check if user_id is provided
if (!isset($input['user_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing user_id"]);
    exit();
}

include 'db_config.php';

// Sanitize and assign the user_id
$user_id = $conn->real_escape_string($input['user_id']);

// Prepare the query to fetch user data
$query = $conn->prepare("SELECT name, email, country, profile_picture FROM users WHERE id = ?");
if (!$query) {
    http_response_code(500);
    echo json_encode(["error" => "Database query preparation failed: " . $conn->error]);
    exit();
}

$query->bind_param('i', $user_id);
$query->execute();
$result = $query->get_result();

// Check if the user was found
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode($user); // Return user details as JSON
} else {
    http_response_code(404);
    echo json_encode(["error" => "User not found"]);
}

$query->close();
$conn->close();
?>
