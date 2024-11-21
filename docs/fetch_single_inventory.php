<?php
session_start();

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['adminid'])) {
    header("Location: login.php");
    exit;
}

// Check if data is submitted via GET
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    // Sanitize and validate input data
    $itemId = intval($_GET['id']);

   $servername = "fdb1029.awardspace.net";
$username = "4528675_accounts";
$password = "matarlo13";
$database = "4528675_accounts";

    // Establish database connection
    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        die(json_encode(array("error" => "Connection failed: " . $conn->connect_error)));
    }

    // Prepare and execute SQL statement to fetch data from inventory table
    $sql = "SELECT id, item_name, quantity, price FROM inventory WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo json_encode(array("error" => "Error preparing statement: " . $conn->error));
        $conn->close();
        exit;
    }
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
        echo json_encode($item);
    } else {
        echo json_encode(array("error" => "Item not found"));
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(array("error" => "Invalid request"));
}
?>
