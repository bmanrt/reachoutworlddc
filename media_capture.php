<?php
include('db_config.php'); // Include your database configuration

// Get the user_id from the POST request
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

if ($user_id == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid user ID']);
    exit();
}

$target_dir = "uploads/";
$unique_name = uniqid('', true) . "_" . basename($_FILES["media"]["name"]);
$target_file = $target_dir . $unique_name;
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Check if image or video file is an actual image or video
$check = getimagesize($_FILES["media"]["tmp_name"]);
if($check !== false) {
    $uploadOk = 1;
} else {
    $file_type = mime_content_type($_FILES["media"]["tmp_name"]);
    if(strstr($file_type, "video/")) {
        $uploadOk = 1;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'File is not an image or video']);
        $uploadOk = 0;
        exit();
    }
}

// Check file size (limit to 50MB)
if ($_FILES["media"]["size"] > 50000000) {
    echo json_encode(['status' => 'error', 'message' => 'Sorry, your file is too large.']);
    $uploadOk = 0;
    exit();
}

// Allow certain file formats
$allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi', 'mov'];
if(!in_array($imageFileType, $allowed_types)) {
    echo json_encode(['status' => 'error', 'message' => 'Sorry, only JPG, JPEG, PNG, GIF, MP4, AVI & MOV files are allowed.']);
    $uploadOk = 0;
    exit();
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Sorry, your file was not uploaded.']);
} else {
    if (move_uploaded_file($_FILES["media"]["tmp_name"], $target_file)) {
        $media_type = (strstr(mime_content_type($target_file), "video/")) ? 'video' : 'image';

        $stmt = $conn->prepare("INSERT INTO user_media (user_id, file_path, media_type) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $target_file, $media_type);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'File uploaded successfully', 'file_path' => $target_file]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save file in the database.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Sorry, there was an error uploading your file.']);
    }
}

$conn->close();
?>
