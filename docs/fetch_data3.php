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

// Check if a studentid was provided in the query string
$studentId = isset($_GET['studentid']) ? $_GET['studentid'] : '';

// Prepare SQL query based on whether studentid is provided
if (!empty($studentId)) {
    // If a student ID is provided, fetch only that specific student's record
    $sql = "SELECT studentid, username, email, contact_number, course, points FROM tb_users WHERE studentid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $studentId);
} else {
    // If no student ID is provided, fetch all records
    $sql = "SELECT studentid, username, email, contact_number, course, points FROM tb_users";
    $stmt = $conn->prepare($sql);
}

// Execute the query
$stmt->execute();
$result = $stmt->get_result();

// Check if there are any results
if ($result->num_rows > 0) {
    // Fetch result as an associative array
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    // Output JSON data
    echo json_encode($data);
} else {
    // No data available
    $error = array("error" => "No data available");
    echo json_encode($error);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
