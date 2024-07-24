<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login if not logged in
    exit();
}

include('db_config.php');

$user_id = $_SESSION['user_id'];
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$country = $_POST['country'];

// Insert data into captured_data table
$sql = "INSERT INTO captured_data (user_id, name, email, phone, country) VALUES ('$user_id', '$name', '$email', '$phone', '$country')";

if ($conn->query($sql) === TRUE) {
    echo "Data captured successfully!";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
