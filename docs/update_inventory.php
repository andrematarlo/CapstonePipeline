<?php
session_start();

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['adminid'])) {
    header("Location: login.php");
    exit;
}

// Check if data is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input data
    $itemId = intval($_POST['editItemId']);
    $item_name = htmlspecialchars($_POST['editItemName']);
    $quantity = intval($_POST['editQuantity']);
    $price = floatval($_POST['editPrice']);

    // Validate input - Basic validation, you can add more as needed
    if (empty($item_name) || $quantity <= 0 || $price <= 0) {
        echo json_encode(array("error" => "Please fill all fields correctly."));
        exit;
    }

    // Database connection settings
$servername = "fdb1029.awardspace.net";
$username = "4528675_accounts";
$password = "matarlo13";
$database = "4528675_accounts";

    // Establish database connection
    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        die(json_encode(array("error" => "Connection failed: " . $conn->connect_error)));
    }

    // Prepare and execute SQL statement to update data in inventory table
    $sql = "UPDATE inventory SET item_name = ?, quantity = ?, price = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo json_encode(array("error" => "Error preparing statement: " . $conn->error));
        $conn->close();
        exit;
    }
    $stmt->bind_param("sidi", $item_name, $quantity, $price, $itemId);

    if ($stmt->execute()) {
        echo json_encode(array("success" => "Item updated successfully"));
    } else {
        echo json_encode(array("error" => "Error: " . $stmt->error));
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(array("error" => "Method not allowed"));
}
?>
