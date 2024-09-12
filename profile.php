<?php
header('Content-Type: application/json');

// Initialize response
$response = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include('db_config.php'); // Ensure DB connection
    $user_id = $conn->real_escape_string($_POST['user_id'] ?? '');

    // Check if user_id is provided
    if (!empty($user_id)) {
        $response['status'] = "success";
        $response['message'] = "user_id received";
        $response['user_id'] = $user_id;

        // Profile picture upload handling
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['size'] > 0) {
            $profile_picture = $_FILES['profile_picture'];
            $imageFileType = strtolower(pathinfo($profile_picture['name'], PATHINFO_EXTENSION));
            $target_dir = "uploads/";
            $target_file = $target_dir . uniqid('', true) . '.' . $imageFileType;

            // Validate the file type and size
            $check = getimagesize($profile_picture['tmp_name']);
            if ($check === false) {
                $response['status'] = "error";
                $response['message'] = "File is not a valid image";
                echo json_encode($response);
                exit();
            }

            if ($profile_picture['size'] > 5000000) { // 5MB size limit
                $response['status'] = "error";
                $response['message'] = "File size exceeds 5MB limit";
                echo json_encode($response);
                exit();
            }

            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($imageFileType, $allowed_types)) {
                $response['status'] = "error";
                $response['message'] = "Only JPG, JPEG, PNG & GIF formats are allowed";
                echo json_encode($response);
                exit();
            }

            // Try to upload the profile picture
            if (move_uploaded_file($profile_picture['tmp_name'], $target_file)) {
                // Update user's profile picture in the database
                $sql = "UPDATE users SET profile_picture = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('si', $target_file, $user_id);
                
                if ($stmt->execute()) {
                    $response['status'] = "success";
                    $response['message'] = "Profile picture updated successfully";
                    $response['profile_picture_url'] = $target_file;
                } else {
                    $response['status'] = "error";
                    $response['message'] = "Failed to update profile picture in the database";
                }
                $stmt->close();
            } else {
                // Check why the file upload failed
                $response['status'] = "error";
                $response['message'] = "Failed to upload the profile picture";
                $response['error'] = "File could not be moved. Check server permissions or file path.";
                
                // Log file upload error details for debugging
                $response['tmp_name'] = $profile_picture['tmp_name'];
                $response['target_file'] = $target_file;
                $response['upload_error'] = $_FILES['profile_picture']['error']; // Error code
            }
        } else {
            $response['status'] = "error";
            $response['message'] = "No profile picture uploaded";
        }
    } else {
        $response['status'] = "error";
        $response['message'] = "Missing user_id";
    }
    echo json_encode($response);
}

elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!isset($_GET['user_id'])) {
        echo json_encode(["status" => "error", "message" => "Missing user_id"]);
        exit();
    }

    include('db_config.php');
    $user_id = $conn->real_escape_string($_GET['user_id']);

    // Return the user's current profile picture URL
    $sql = "SELECT profile_picture FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        echo json_encode([
            "status" => "success",
            "profile_picture_url" => $user['profile_picture']
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "User not found"]);
    }
}

$conn->close();
