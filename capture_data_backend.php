<?php
// header("Access-Control-Allow-Origin: *");
// header("Content-Type: application/json; charset=UTF-8");

// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname = "user_data";

// // Create connection
// $conn = new mysqli($servername, $username, $password, $dbname);

// // Check connection
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }

// if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//     $name = $_POST['name'];
//     $email = $_POST['email'];
//     $phone = $_POST['phone'];
//     $country = $_POST['country'];

//     $sql = "INSERT INTO users (name, email, phone, country) VALUES ('$name', '$email', '$phone', '$country')";

//     if ($conn->query($sql) === TRUE) {
//         echo json_encode(array("status" => "success", "message" => "Data captured successfully"));
//     } else {
//         echo json_encode(array("status" => "error", "message" => "Error: " . $sql . "<br>" . $conn->error));
//     }
//     $conn->close();
// } else {
//     echo json_encode(array("status" => "error", "message" => "Invalid request method"));
// }
?>
