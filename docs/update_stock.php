<?php
// Set header to return JSON response
header('Content-Type: application/json');

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the required parameters are set
    if (isset($_POST['id']) && isset($_POST['stocks'])) {
        // Sanitize and validate input data
        $id = intval($_POST['id']); // Ensure ID is an integer
        $additionalStocks = intval($_POST['stocks']); // Ensure stocks is an integer

        // Validate input - Basic validation
        if ($additionalStocks < 0) {
            echo json_encode(['error' => 'Stocks cannot be negative.']);
            exit;
        }

        // Database connection settings
$servername = "fdb1029.awardspace.net";
$username = "4528675_accounts";
$password = "matarlo13";
$database = "4528675_accounts";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $database);

        // Check connection
        if ($conn->connect_error) {
            echo json_encode(['error' => 'Connection failed: ' . $conn->connect_error]);
            exit;
        }

        // Prepare and bind SQL statement to add stocks
        $stmt = $conn->prepare("UPDATE rewards_table SET stocks = stocks + ? WHERE id = ?");
        $stmt->bind_param("ii", $additionalStocks, $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => 'Stock updated successfully.']);
        } else {
            echo json_encode(['error' => 'Error updating stock: ' . $stmt->error]);
        }

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(['error' => 'Missing parameters.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
?>
