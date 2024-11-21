<?php
// Database connection details
$servername = "fdb1029.awardspace.net";
$username = "4528675_accounts";
$password = "matarlo13";
$database = "4528675_accounts";

// Create connection to the database
$conn = new mysqli($servername, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the raw POST data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Extract label and confidence from the JSON data
    $label = $data['label'];
    $confidence = $data['confidence'];

    // Prepare an SQL statement to insert data into the table
    $stmt = $conn->prepare("INSERT INTO classifications (label, confidence) VALUES (?, ?)");
    $stmt->bind_param("sd", $label, $confidence);

    // Execute the statement and check if it was successful
    if ($stmt->execute()) {
        // Success response
        echo json_encode(["message" => "Data inserted successfully"]);
    } else {
        // Error response
        echo json_encode(["message" => "Error inserting data: " . $stmt->error]);
    }

    // Close the statement
    $stmt->close();
} else {
    // Invalid request method
    echo json_encode(["message" => "Invalid request method"]);
}

// Close the connection
$conn->close();
?>
