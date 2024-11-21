<?php
session_start();

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['adminid'])) {
    echo json_encode(['error' => 'Access Denied']);
    exit;
}

// Establish database connection
$servername = "fdb1029.awardspace.net";
$username = "4528675_accounts";
$password = "matarlo13";
$database = "4528675_accounts";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission to add inventory
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['reward_id']) && isset($_POST['datepurchased']) && isset($_POST['stocks_added']) && isset($_POST['price'])) {
        $reward_id = $_POST['reward_id'];
        $stocks_added = $_POST['stocks_added'];
        $price = $_POST['price'];
        $datepurchased = $_POST['datepurchased'];

        // Fetch the current total stocks for the selected reward from rewards_table
        $stmt = $conn->prepare("SELECT stocks, name FROM rewards_table WHERE id = ?");
        $stmt->bind_param("i", $reward_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $current_total_stocks = $row['stocks'];
        $item_name = $row['name'];
        $stmt->close();

        // Calculate new total stocks
        $new_total_stocks = $current_total_stocks + $stocks_added;

        // Begin transaction
        $conn->begin_transaction();
        try {
            // Insert into inventory_table
            $stmt = $conn->prepare("INSERT INTO inventory_table (reward_id, item_name, datepurchased, stocks_added, total_stocks, price) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issidi", $reward_id, $item_name, $datepurchased, $stocks_added, $new_total_stocks, $price);
            $stmt->execute();
            $stmt->close();

            // Update the total stocks in rewards_table
            $stmt = $conn->prepare("UPDATE rewards_table SET stocks = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_total_stocks, $reward_id);
            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $conn->commit();
            echo "<p style='color:green;'>Inventory updated successfully!</p>";
        } catch (Exception $e) {
            // Rollback transaction if there is an error
            $conn->rollback();
            echo "<p style='color:red;'>Error updating inventory: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color:red;'>Invalid form submission.</p>";
    }
}

// Fetch rewards from database
$rewards = [];
$sql = "SELECT id, name, stocks FROM rewards_table";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $rewards[] = $row;
    }
}

$conn->close();
?>
