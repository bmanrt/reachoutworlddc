<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login if not logged in
    exit();
}

// Database connection
include('db_config.php');

// Get form data
$user_id = $_SESSION['user_id'];
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$country = $_POST['country'];

// Insert data into captured_data table
$sql = "INSERT INTO captured_data (user_id, name, email, phone, country) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("issss", $user_id, $name, $email, $phone, $country);
    if ($stmt->execute()) {
        echo "Data captured successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
