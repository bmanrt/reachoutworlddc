<?php
include('db_config.php');

// Set headers for JSON response
header("Content-Type: application/json");

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON input data
    $input = json_decode(file_get_contents("php://input"), true);

    // Check if all necessary data is provided
    if (isset($input['name']) && isset($input['email']) && isset($input['country']) && isset($input['password'])) {
        // Sanitize and assign the input data
        $name = $conn->real_escape_string($input['name']);
        $email = $conn->real_escape_string($input['email']);
        $country = $conn->real_escape_string($input['country']);
        $password = password_hash($conn->real_escape_string($input['password']), PASSWORD_DEFAULT);

        // Insert data into database
        $sql = "INSERT INTO users (name, email, country, password) VALUES ('$name', '$email', '$country', '$password')";

        if ($conn->query($sql) === TRUE) {
            // Success response
            echo json_encode(["status" => "success", "message" => "Registration successful!"]);
        } else {
            // Error response
            echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
        }
    } else {
        // Invalid input response
        echo json_encode(["status" => "error", "message" => "Invalid input data"]);
    }
} else {
    // Invalid request method response
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
