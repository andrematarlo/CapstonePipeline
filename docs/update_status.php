<?php
// update_status.php

// Database connection
$servername = "fdb1029.awardspace.net";
$username = "4528675_accounts";
$password = "matarlo13";
$database = "4528675_accounts";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    // Log and return a JSON error response if connection fails
    error_log("Database connection failed: " . $conn->connect_error);
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve POST data
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $new_status = isset($_POST['status']) ? $_POST['status'] : null;

    // Ensure required data is present
    if (!$id || !$new_status) {
        echo json_encode(['success' => false, 'error' => 'Missing required parameters.']);
        exit();
    }

    // Prepare the SQL statement
    $sql = "UPDATE redemption_history SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("si", $new_status, $id);

        // Execute the statement
        if ($stmt->execute()) {
            // Return success response
            echo json_encode(['success' => true]);
        } else {
            // Return error if execution fails
            error_log("Statement execution failed: " . $stmt->error);
            echo json_encode(['success' => false, 'error' => 'Failed to execute statement: ' . $stmt->error]);
        }

        // Close statement
        $stmt->close();
    } else {
        // Return error if preparation fails
        error_log("SQL preparation failed: " . $conn->error);
        echo json_encode(['success' => false, 'error' => 'SQL preparation failed: ' . $conn->error]);
    }

    // Close connection
    $conn->close();
} else {
    // Return error if request method is not POST
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
?>
