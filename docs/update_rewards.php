<?php
// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the required parameters are set
    if (isset($_POST['id']) && isset($_POST['name']) && isset($_POST['pointsRequired']) && isset($_POST['image'])) {
        // Get parameters from the POST request
        $id = $_POST['id'];
        $name = $_POST['name'];
        $pointsRequired = $_POST['pointsRequired'];
        $image = $_POST['image'];

        // Connect to your MySQL database
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

        // Prepare and bind SQL statement to prevent SQL injection
        $stmt = $conn->prepare("UPDATE rewards_table SET name = ?, pointsRequired = ?, image = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $pointsRequired, $image, $id);
        
        if ($stmt->execute()) {
            echo "Reward updated successfully!";
        } else {
            echo "Error updating reward: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    } else {
        echo "Missing parameters!";
    }
} else {
    echo "Invalid request method!";
}
