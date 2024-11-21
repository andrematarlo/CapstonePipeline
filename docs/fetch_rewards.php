<?php
header('Content-Type: application/json');

// Database connection details
$servername = "fdb1029.awardspace.net";
$username = "4528675_accounts";
$password_db = "matarlo13";
$database = "4528675_accounts";

// Create a new connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to fetch inventory data
$sql = "SELECT id, reward_id, item_name, datepurchased, stocks_added, total_stocks, price FROM inventory_table";
$result = $conn->query($sql);

// Initialize an array to hold the inventory data
$inventory = array();

// Check if there are results
if ($result->num_rows > 0) {
    // Fetch data row by row
    while($row = $result->fetch_assoc()) {
        $inventory[] = $row;
    }
}

// Encode the inventory array as JSON and output it
echo json_encode($inventory);

// Close the connection
$conn->close();
?>
