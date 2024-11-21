<?php
// Database connection details
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

// Collect form data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $pointsRequired = $_POST['pointsRequired'];
    $imageUrl = $_POST['imageUrl'];  // Make sure the name here matches the input name in HTML

    // Validate form data
    if (empty($name) || empty($pointsRequired) || empty($imageUrl)) {
        die("Please fill all the fields");
    }

    // Prepare SQL statement to insert data with default stocks set to 0
    $sql = "INSERT INTO rewards_table (name, pointsRequired, image, stocks) VALUES (?, ?, ?, ?)";

    // Set the default stock to 0
    $defaultStock = 0;

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sisi", $name, $pointsRequired, $imageUrl, $defaultStock);

    // Execute the query and check if successful
    if ($stmt->execute()) {
        echo "Reward added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid Request";
}
?>
