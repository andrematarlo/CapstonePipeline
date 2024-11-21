<?php
// Start session and check if the admin is logged in
session_start();
if (!isset($_SESSION['adminid'])) {
    echo "error: not logged in";
    exit;
}

// Check if the reward ID was sent via POST
if (isset($_POST['id'])) {
    // Get the reward ID from the POST request
    $rewardId = $_POST['id'];

    // Database connection details
    $servername = "fdb1029.awardspace.net";
    $username = "4528675_accounts";
    $password = "matarlo13";
    $database = "4528675_accounts";

    // Create a new connection to the database
    $conn = new mysqli($servername, $username, $password, $database);

    // Check if the connection was successful
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and execute the SQL statement to delete the reward
    $stmt = $conn->prepare("DELETE FROM rewards_table WHERE id = ?");
    $stmt->bind_param("i", $rewardId);

    if ($stmt->execute()) {
        // Successfully deleted the reward
        echo "success";
    } else {
        // Error occurred during deletion
        echo "error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    // No reward ID was provided
    echo "error: missing reward ID";
}
?>
