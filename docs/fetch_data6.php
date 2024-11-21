<?php
session_start(); // Start the session to access user login information

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

// Fetch points to add from config table
$configResult = $conn->query("SELECT points_to_add FROM config LIMIT 1");
$pointsToAdd = 10; // Default value

if ($configResult && $row = $configResult->fetch_assoc()) {
    $pointsToAdd = (int)$row['points_to_add']; // Get the configurable points to add
}

// Check if the user is logged in
if (!isset($_SESSION['studentid'])) {
    echo json_encode(['error' => 'User not logged in.']);
    exit();
}

// Retrieve the logged-in user's studentid
$studentid = $_SESSION['studentid'];

// Fetch the latest sensor_value and id
$query = "SELECT id, sensor_value, datetime FROM sensor_readings ORDER BY datetime DESC LIMIT 1";
$result = $conn->query($query);

if ($result) {
    $row = $result->fetch_assoc();
    $sensorValue = $row['sensor_value'];
    $lastUpdateTime = $row['datetime'];
    $sensorId = $row['id']; // Retrieve the id

    // Check if sensor_value is 1 and if points have not been updated recently
    $currentTime = new DateTime();
    $lastUpdateTime = new DateTime($lastUpdateTime);
    $interval = $currentTime->diff($lastUpdateTime);

    // Check if 5 seconds have passed since last update
    if ($sensorValue == 1 && $interval->s >= 5) {
        // Fetch the last processed sensor ID for the user
        $stmt = $conn->prepare("SELECT last_sensor_id, points FROM tb_users WHERE studentid = ?");
        $stmt->bind_param("s", $studentid);
        $stmt->execute();
        $stmt->bind_result($lastProcessedId, $currentPoints); // Get the current points
        $stmt->fetch();
        $stmt->close();

        // Check if the current sensor ID is greater than the last processed ID
        if ($sensorId > $lastProcessedId) {
            // Check if the sensor ID is already assigned to another user
            $stmt = $conn->prepare("SELECT studentid FROM tb_users WHERE last_sensor_id = ?");
            $stmt->bind_param("i", $sensorId);
            $stmt->execute();
            $stmt->store_result();

            // If another user is using this sensor ID, don't update points
            if ($stmt->num_rows > 0) {
                $stmt->close();
                echo json_encode(['message' => 'Sensor ID is already assigned to another user.']);
            } else {
                $stmt->close();

                // Add points to the logged-in user
                $newPoints = $currentPoints + $pointsToAdd; // Update to add points from config
                $stmt = $conn->prepare("UPDATE tb_users SET points = ?, last_sensor_id = ? WHERE studentid = ?");
                $stmt->bind_param("iis", $newPoints, $sensorId, $studentid);

                if ($stmt->execute()) {
                    // Send a response that the points were added
                    echo json_encode(['pointsAdded' => $pointsToAdd, 'newPoints' => $newPoints, 'sensorId' => $sensorId, 'message' => "Congratulations, you earned $pointsToAdd points!"]);
                } else {
                    echo json_encode(['error' => 'Error updating points: ' . $stmt->error]);
                }

                $stmt->close();
            }
        } else {
            echo json_encode(['message' => 'Sensor value is not new or points already updated recently.']);
        }
    } else {
        echo json_encode(['message' => 'Sensor value is not 1 or points already updated recently.']);
    }
} else {
    echo json_encode(['error' => 'Error fetching sensor value: ' . $conn->error]);
}

// Close the connection
$conn->close();
?>
