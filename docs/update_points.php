<?php
session_start(); // Start the session

// Database configuration
$servername = "fdb1029.awardspace.net";
$username = "4528675_accounts";
$password = "matarlo13";
$database = "4528675_accounts";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the points to add from the AJAX request
    $pointsToAdd = intval($_POST['pointsToAdd']);

    // Update the points in the config table
    $stmt = $conn->prepare("UPDATE config SET points_to_add = ? WHERE id = 1");
    $stmt->bind_param("i", $pointsToAdd);

    if ($stmt->execute()) {
        echo "Points updated successfully to $pointsToAdd.";
    } else {
        echo "Error updating points: " . $stmt->error;
    }

    $stmt->close();
}

// Close the connection
$conn->close();
?>
