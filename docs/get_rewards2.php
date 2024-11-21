<?php
// PHP script to handle updating existing rewards in the rewards table

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $name = $_POST['name'];
    $pointsRequired = $_POST['pointsRequired'];
    // For image, you may need additional handling such as uploading to a server and storing the path in the database.
    // For simplicity, let's assume image is a URL
    $image = $_POST['image'];

    // Connect to your database
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

    // Prepare SQL statement to update existing record in rewards table
    $sql = "UPDATE rewards_table SET pointsRequired='$pointsRequired', image='$image' WHERE name='$name'";

    if ($conn->query($sql) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close connection
    $conn->close();
}
?>
