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
$sql = "INSERT INTO captured_data (user_id, name, email, phone, country) VALUES ('$user_id', '$name', '$email', '$phone', '$country')";

if ($conn->query($sql) === TRUE) {
    // Show success page
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Success</title>
        <link rel='stylesheet' href='styles.css'>
    </head>
    <body>
        <div class='container'>
            <h1>Success!</h1>
            <p>Data captured successfully.</p>
            <button onclick=\"window.location.href='dashboard.html'\">Go to Dashboard</button>
        </div>
    </body>
    </html>
    ";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
