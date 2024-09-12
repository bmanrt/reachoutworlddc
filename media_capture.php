<?php
include('db_config.php'); // Include your database configuration

// Get the user_id from the POST request
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$user_country = isset($_POST['user_country']) && !empty($_POST['user_country']) ? $_POST['user_country'] : 'N/A';
$user_state = isset($_POST['user_state']) && !empty($_POST['user_state']) ? $_POST['user_state'] : 'N/A';
$user_region = isset($_POST['user_region']) && !empty($_POST['user_region']) ? $_POST['user_region'] : 'N/A';

// Check if user_id is valid
if ($user_id == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid user ID']);
    exit();
}

$target_dir = "uploads/";
$unique_name = uniqid('', true) . "_" . basename($_FILES["media"]["name"]);
$target_file = $target_dir . $unique_name;
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Logging the paths and variables for debugging
echo json_encode([
    'status' => 'debug',
    'message' => 'Starting upload process',
    'target_file' => $target_file,
    'temp_file' => $_FILES['media']['tmp_name']
]);

// Check if the file is an image or video
$check = getimagesize($_FILES["media"]["tmp_name"]);
if ($check !== false) {
    $uploadOk = 1;
    echo json_encode(['status' => 'debug', 'message' => 'File is a valid image']);
} else {
    $file_type = mime_content_type($_FILES["media"]["tmp_name"]);
    if (strstr($file_type, "video/")) {
        $uploadOk = 1;
        echo json_encode(['status' => 'debug', 'message' => 'File is a valid video']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'File is not an image or video']);
        $uploadOk = 0;
        exit();
    }
}

// Check file size (limit to 50MB)
if ($_FILES["media"]["size"] > 50000000) {
    echo json_encode(['status' => 'error', 'message' => 'File size exceeds the 50MB limit']);
    $uploadOk = 0;
    exit();
}

// Allow certain file formats
$allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi', 'mov'];
if (!in_array($imageFileType, $allowed_types)) {
    echo json_encode(['status' => 'error', 'message' => 'Only JPG, JPEG, PNG, GIF, MP4, AVI & MOV files are allowed']);
    $uploadOk = 0;
    exit();
}

// If any validation has failed
if ($uploadOk == 0) {
    echo json_encode(['status' => 'error', 'message' => 'File did not pass validation']);
    exit();
}

// Attempt to move the uploaded file
if (move_uploaded_file($_FILES["media"]["tmp_name"], $target_file)) {
    $media_type = (strstr(mime_content_type($target_file), "video/")) ? 'video' : 'image';

    // Updated SQL to insert user location data (country, state, region)
    $stmt = $conn->prepare("INSERT INTO user_media (user_id, file_path, media_type, user_country, user_state, user_region) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $user_id, $target_file, $media_type, $user_country, $user_state, $user_region);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'File uploaded successfully', 'file_path' => $target_file]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to save file in the database',
            'error' => $stmt->error
        ]);
    }
    $stmt->close();
} else {
    // Check why the file upload failed
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to upload the file. Error moving the file.',
        'tmp_name' => $_FILES['media']['tmp_name'],
        'target_file' => $target_file,
        'upload_error_code' => $_FILES['media']['error'], // File upload error code
    ]);
}

$conn->close();
?>
