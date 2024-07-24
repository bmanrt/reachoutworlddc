<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With");

include('db_config.php');

// Get raw POST data
$data = json_decode(file_get_contents("php://input"));

$name = isset($data->name) ? $data->name : '';
$email = isset($data->email) ? $data->email : '';
$country = isset($data->country) ? $data->country : '';
$password = isset($data->password) ? $data->password : '';

// Validate input
if (empty($name) || empty($email) || empty($country) || empty($password)) {
    echo json_encode(array("status" => "error", "message" => "All fields are required."));
    exit();
}

// Sanitize input
$name = $conn->real_escape_string($name);
$email = $conn->real_escape_string($email);
$country = $conn->real_escape_string($country);
$password = password_hash($conn->real_escape_string($password), PASSWORD_DEFAULT);

// Insert data into database
$sql = "INSERT INTO users (name, email, country, password) VALUES ('$name', '$email', '$country', '$password')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(array("status" => "success", "message" => "Registration successful!"));
} else {
    echo json_encode(array("status" => "error", "message" => "Error: " . $conn->error));
}

$conn->close();
?>


