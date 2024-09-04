<?php
// Set the header to indicate the content type as JSON
header('Content-Type: application/json');

// Start the session to get the user_id (if needed)
session_start();

// Include database configuration
include('db_config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON input from the Flutter app
    $input = json_decode(file_get_contents("php://input"), true);

    // Validate the input
    if (!isset($input['user_id']) || !isset($input['name']) || !isset($input['email']) || !isset($input['phone']) || !isset($input['country'])) {
        http_response_code(400); // Bad Request
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    } else {
        // Sanitize and assign the input values
        $user_id = $conn->real_escape_string($input['user_id']);
        $name = $conn->real_escape_string($input['name']);
        $email = $conn->real_escape_string($input['email']);
        $phone = $conn->real_escape_string($input['phone']);
        $country = $conn->real_escape_string($input['country']);

        // Check if the exact same entry already exists in the database
        $checkSql = "SELECT * FROM captured_data WHERE user_id = ? AND name = ? AND email = ? AND phone = ? AND country = ?";
        $stmt = $conn->prepare($checkSql);

        if ($stmt) {
            $stmt->bind_param("issss", $user_id, $name, $email, $phone, $country);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // If an identical record is found, return a message indicating that
                http_response_code(409); // Conflict
                echo json_encode(["status" => "error", "message" => "Duplicate entry: This data has already been captured"]);
            } else {
                // Insert the data into the captured_data table if no duplicate is found
                $insertSql = "INSERT INTO captured_data (user_id, name, email, phone, country) VALUES (?, ?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertSql);

                if ($insertStmt) {
                    $insertStmt->bind_param("issss", $user_id, $name, $email, $phone, $country);

                    if ($insertStmt->execute()) {
                        // Send success response
                        echo json_encode(["status" => "success", "message" => "Data captured successfully"]);
                    } else {
                        // If there was a problem executing the query
                        http_response_code(500); // Internal Server Error
                        echo json_encode(["status" => "error", "message" => "Failed to capture data"]);
                    }

                    $insertStmt->close();
                } else {
                    // If there was a problem preparing the INSERT SQL statement
                    http_response_code(500); // Internal Server Error
                    echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
                }
            }

            $stmt->close();
        } else {
            // If there was a problem preparing the SELECT SQL statement
            http_response_code(500); // Internal Server Error
            echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
        }
    }
}

// Close the database connection
$conn->close();
?>
