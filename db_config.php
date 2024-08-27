<?php
$servername = "10.11.0.106";
$username = "root";
$password = "lolamarsh";
$dbname = "user_capture";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
