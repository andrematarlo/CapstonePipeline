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
    $item_name = htmlspecialchars($_POST['itemName']);
    $quantity = intval($_POST['quantity']);
    $price = floatval($_POST['price']);

    // Validate input - Basic validation, you can add more as needed
    if (empty($item_name) || $quantity <= 0 || $price <= 0) {
        echo json_encode(array("error" => "Please fill all fields correctly."));
        exit;
    }

    // Establish database connection
    $servername = "fdb1029.awardspace.net";
$username = "4528675_accounts";
$password = "matarlo13";
$database = "4528675_accounts";

    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        die(json_encode(array("error" => "Connection failed: " . $conn->connect_error)));
    }

    // Prepare and execute SQL statement to insert data into inventory table
    // Include 'datetimestamp' in the INSERT statement
    $sql = "INSERT INTO inventory (item_name, quantity, price, datetimestamp) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sid", $item_name, $quantity, $price);

    if ($stmt->execute()) {
        echo json_encode(array("success" => "New record created successfully"));
    } else {
        echo json_encode(array("error" => "Error: " . $sql . "<br>" . $conn->error));
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(array("error" => "Method not allowed"));
}
?>
