<?php
// Database connection credentials
$servername = "fdb1029.awardspace.net";
$username = "4528675_accounts";
$password = "matarlo13";
$database = "4528675_accounts";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    // Return error response in JSON
    header('Content-Type: application/json');
    echo json_encode(array("success" => false, "error" => "Database connection failed: " . $conn->connect_error));
    exit();
}

// Fetch redemption history from the database, including id and status
$sql = "SELECT id, studentid, item_redeemed, points_required, datetimestamp, status FROM redemption_history";
$result = $conn->query($sql);

$history = array();

if ($result) {
    if ($result->num_rows > 0) {
        // Output data of each row
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
    }

    // Free result set
    $result->free();
} else {
    // Return error if the query fails
    header('Content-Type: application/json');
    echo json_encode(array("success" => false, "error" => "Failed to fetch redemption history: " . $conn->error));
    $conn->close();
    exit();
}

// Close connection
$conn->close();

// Return redemption history in JSON format
header('Content-Type: application/json');
echo json_encode(array("success" => true, "history" => $history));
?>
