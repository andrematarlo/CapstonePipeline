<?php
// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// For preflight requests (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Respond with OK status code (200)
    http_response_code(200);
    exit();
}

// Start session to access user information
session_start();

// Check if the user is logged in
if(!isset($_SESSION['ID'])){
    // If not logged in, return error
    $error = array("error" => "User is not logged in");
    echo json_encode($error);
    exit();
}

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

// Fetch latest sensor data from the database
$sql = "SELECT ID, status FROM sensor_readings ORDER BY datetime DESC LIMIT 1";
$result = $conn->query($sql);

if ($result === false) {
    // Query execution failed
    $error = array("error" => "Error executing the query: " . $conn->error);
    echo json_encode($error);
} else {
    if ($result->num_rows > 0) {
        // Fetch result as an associative array
        $row = $result->fetch_assoc();
        
        // Check if status is 'full', then add 10 points for the logged-in user
        if ($row['status'] === 'full') {
            // Get the user ID from the session
            $user_id = $_SESSION['ID'];
            
            // Update points only for the logged-in user
            $updateSql = "UPDATE tb_users SET points = points + 10 WHERE ID = $user_id";
            $updateResult = $conn->query($updateSql);

            if ($updateResult === false) {
                // Error updating points
                $error = array("error" => "Error updating points: " . $conn->error);
                echo json_encode($error);
            } else {
                // Output JSON data
                echo json_encode($row);
            }
        } else {
            // Output JSON data
            echo json_encode($row);
        }
    } else {
        // No data available
        $error = array("error" => "No data available");
        echo json_encode($error);
    }
}

// Close connection
$conn->close();
?>
