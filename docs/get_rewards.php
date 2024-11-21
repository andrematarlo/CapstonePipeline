<?php
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

// Fetch rewards from the database
$sql = "SELECT id, name, pointsRequired, image, stocks FROM rewards_table"; // Include the 'id' column
$result = $conn->query($sql);

$rewards = array();
if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        $rewards[] = array(
            "id" => $row["id"], // Include 'id' information
            "name" => $row["name"],
            "pointsRequired" => $row["pointsRequired"],
            "image" => $row["image"],
            "stocks" => $row["stocks"]
        );
    }
    echo json_encode($rewards);
} else {
    echo json_encode(array("error" => "No rewards found"));
}

$conn->close();
?>
