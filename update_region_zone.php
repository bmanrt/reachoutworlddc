<?php
header('Content-Type: application/json');

// Include database configuration
include('db_config.php');

// Check the request method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle POST requests (e.g., updating region and zone)
    
    // Get the POST parameters (email, region, zone)
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $region = isset($_POST['region']) ? trim($_POST['region']) : null;
    $zone = isset($_POST['zone']) ? trim($_POST['zone']) : null;

    // Validate that the email is provided
    if (!$email) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Email is required.'
        ]);
        exit();
    }

    // Prepare the SQL statement to check if the email exists in the users table
    $sql_check_email = "SELECT id FROM users WHERE email = ?";
    $stmt_check_email = $conn->prepare($sql_check_email);
    $stmt_check_email->bind_param("s", $email);
    $stmt_check_email->execute();
    $result = $stmt_check_email->get_result();

    if ($result->num_rows > 0) {
        // If email is found, update the region and/or zone if provided
        $user = $result->fetch_assoc();
        $user_id = $user['id'];

        // Prepare dynamic SQL based on the provided fields
        $update_fields = [];
        $update_values = [];
        $update_types = '';

        if (!empty($region)) {
            $update_fields[] = "region = ?";
            $update_values[] = $region;
            $update_types .= 's';
        }

        if (!empty($zone)) {
            $update_fields[] = "zone = ?";
            $update_values[] = $zone;
            $update_types .= 's';
        }

        // Check if any fields are provided for update
        if (!empty($update_fields)) {
            $update_values[] = $user_id;
            $update_types .= 'i';

            $sql_update = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param($update_types, ...$update_values);

            if ($stmt_update->execute()) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Region and/or zone updated successfully.'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to update region and/or zone.'
                ]);
            }
            $stmt_update->close();
        } else {
            // If no fields provided for update
            echo json_encode([
                'status' => 'error',
                'message' => 'No region or zone provided to update.'
            ]);
        }
    } else {
        // Email not found in the users table
        echo json_encode([
            'status' => 'error',
            'message' => 'Email not found.'
        ]);
    }

    $stmt_check_email->close();
    $conn->close();

} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Handle GET requests (e.g., fetching region and zone)

    // Get the user_id from the GET parameters
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

    // Validate the user_id
    if ($user_id > 0) {
        // Prepare the SQL statement to retrieve the region and zone
        $sql_get_user_data = "SELECT region, zone FROM users WHERE id = ?";
        $stmt_get_user_data = $conn->prepare($sql_get_user_data);
        $stmt_get_user_data->bind_param("i", $user_id);
        $stmt_get_user_data->execute();
        $result = $stmt_get_user_data->get_result();

        if ($result->num_rows > 0) {
            // Fetch the user data
            $user_data = $result->fetch_assoc();

            // Return the region and zone (even if they are null)
            echo json_encode([
                'status' => 'success',
                'region' => $user_data['region'],
                'zone' => $user_data['zone']
            ]);
        } else {
            // User not found
            echo json_encode([
                'status' => 'error',
                'message' => 'User not found.'
            ]);
        }

        $stmt_get_user_data->close();
    } else {
        // Invalid user_id
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid user_id provided.'
        ]);
    }

    $conn->close();

} else {
    // Handle invalid request methods
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method. Only POST and GET are allowed.'
    ]);
}
?>
