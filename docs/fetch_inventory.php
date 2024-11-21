<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['adminid'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Access Denied']);
    exit;
}

// Establish database connection
$servername = "fdb1029.awardspace.net";
$username = "4528675_accounts";
$password_db = "matarlo13";
$database = "4528675_accounts";

$conn = new mysqli($servername, $username, $password_db, $database);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Prepare SQL query with optional filter
$reward_id = isset($_GET['reward_id']) ? intval($_GET['reward_id']) : null;

$sql = "
    SELECT i.id, r.name AS item_name, i.reward_id, i.datepurchased, i.stocks_added, i.total_stocks, i.price
    FROM inventory_table i
    JOIN rewards_table r ON i.reward_id = r.id
";

if ($reward_id !== null) {
    $sql .= " WHERE i.reward_id = ?";
}

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'SQL prepare failed: ' . $conn->error]);
    exit;
}

if ($reward_id !== null) {
    $stmt->bind_param("i", $reward_id);
}

$execute_result = $stmt->execute();

if ($execute_result === false) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'SQL execute failed: ' . $stmt->error]);
    exit;
}

$result = $stmt->get_result();
$inventory = [];

// Fetch data
while ($row = $result->fetch_assoc()) {
    $inventory[] = $row;
}

// Encode the result as JSON
header('Content-Type: application/json');
echo json_encode($inventory);

// Close the connection
$stmt->close();
$conn->close();
?>
