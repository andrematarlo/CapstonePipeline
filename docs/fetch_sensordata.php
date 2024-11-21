<?php
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

// Receive data from ESP8266
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve POST data
    $sensorValue = isset($_POST['sensorValue']) ? floatval($_POST['sensorValue']) : null;
    $distance = isset($_POST['distance']) ? floatval($_POST['distance']) : null;
    $status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : null;

    // Validate received data
    if ($sensorValue !== null && $distance !== null && $status !== null) {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO sensor_readings (sensor_value, distance, status, datetime) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("dds", $sensorValue, $distance, $status);

        // Execute the statement
        if ($stmt->execute()) {
            echo "Data received and saved to database.";
        } else {
            echo "Error inserting data: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Invalid data received.";
    }
} else {
    // Method not allowed
    http_response_code(405);
}

// Close the connection
$conn->close();
?>
