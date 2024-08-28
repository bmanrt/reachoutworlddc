<?php
header('Content-Type: application/json'); 


session_start();
if (!isset($_SESSION['user_id'])) {
    // If the user is not logged in, return an error response
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit();
}

include('db_config.php');
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];

    $profile_picture = $_FILES['profile_picture'];
    $profile_picture_path = '';
    $uploadOk = 1;

    // Fetch the existing profile picture path from the database
    $sql = "SELECT profile_picture FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $existing_profile_picture = $user['profile_picture'];
    $stmt->close();

    // Check if a new profile picture is uploaded
    if ($profile_picture['size'] > 0) {
        // Validate the file
        $imageFileType = strtolower(pathinfo($profile_picture['name'], PATHINFO_EXTENSION));
        $target_dir = "uploads/";
        $target_file = $target_dir . uniqid() . '.' . $imageFileType; // Unique filename

        // Check if the file is an actual image
        $check = getimagesize($profile_picture['tmp_name']);
        if ($check === false) {
            $uploadOk = 0;
            echo json_encode(["status" => "error", "message" => "File is not an image"]);
            exit();
        }

        // Check file size (limit: 5MB)
        if ($profile_picture['size'] > 5000000) { // 5MB limit
            $uploadOk = 0;
            echo json_encode(["status" => "error", "message" => "File is too large"]);
            exit();
        }

        // Allow certain file formats
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $uploadOk = 0;
            echo json_encode(["status" => "error", "message" => "Only JPG, JPEG, PNG & GIF files are allowed"]);
            exit();
        }

        // If everything is ok, try to upload the file
        if ($uploadOk == 1) {
            if (move_uploaded_file($profile_picture['tmp_name'], $target_file)) {
                $profile_picture_path = $target_file;

                // Optionally, delete the old profile picture if it exists and is not the default
                if (!empty($existing_profile_picture) && file_exists($existing_profile_picture)) {
                    unlink($existing_profile_picture); // Delete old image
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to upload the file"]);
                exit();
            }
        }
    } else {
        // Retain the existing profile picture if no new picture is uploaded
        $profile_picture_path = $existing_profile_picture;
    }

    // Update user info in the database
    $sql = "UPDATE users SET name = ?, email = ?, profile_picture = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssi', $name, $email, $profile_picture_path, $user_id);

    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Profile updated successfully",
            "profile_picture_url" => $profile_picture_path // Send back the updated image URL
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update profile: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} elseif ($_GET['action'] == 'get') {
    // Fetch user details for profile display
    $sql = "SELECT name, email, profile_picture FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Return user data as JSON
    header('Content-Type: application/json');
    echo json_encode($user);

    $stmt->close();
    $conn->close();
}
?>
