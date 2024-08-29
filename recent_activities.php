<?php
// Set the header to indicate the content type as JSON
header('Content-Type: application/json');

// Include database configuration
include('db_config.php');

// Get the JSON input from the Flutter app (or query parameters in a GET request)
$input = json_decode(file_get_contents("php://input"), true);

// Check if the request method is GET and user_id is provided
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);

    // Fetch latest entries for the provided user_id
    $sql = "SELECT name, email, phone, country, created_at FROM captured_data WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize an array to hold the activities
    $activities = [];

    // If there are results, populate the activities array
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
    }

    // Close the database connection
    $stmt->close();
    $conn->close();

    // Return the results in JSON format
    if (count($activities) > 0) {
        echo json_encode(["status" => "success", "data" => $activities]);
    } else {
        echo json_encode(["status" => "success", "message" => "No activities found", "data" => []]);
    }
} else {
    // Respond with a JSON error if the request method is not GET or user_id is missing
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Invalid request method or missing user_id"]);
}
?>
