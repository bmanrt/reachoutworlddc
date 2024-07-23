<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login if not logged in
    exit();
}

// Database connection
include('db_config.php');

$user_id = $_SESSION['user_id'];

// Fetch latest entries for the logged-in user
$sql = "SELECT name, email, phone, country, created_at FROM captured_data WHERE user_id = '$user_id' ORDER BY created_at DESC";
$result = $conn->query($sql);

$activities = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($activities);
?>
