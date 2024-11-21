<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Database connection parameters
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

// Fetch redemption history for the logged-in user
$username = $_SESSION['username'];
$sql = "SELECT * FROM redemption_history WHERE username = '$username'";
$result = $conn->query($sql);

// Initialize an empty array to store the redemption history
$redemptionHistory = array();

// Check if there are any redemption history records
if ($result->num_rows > 0) {
    // Fetch each row from the result set and add it to the redemption history array
    while ($row = $result->fetch_assoc()) {
        $redemptionHistory[] = $row;
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Include necessary CSS and JavaScript libraries -->
</head>
<body>
    <!-- Header -->
    <header>
        <!-- Your header content goes here -->
    </header>

    <!-- Main content -->
    <div>
        <!-- Display redemption history -->
        <h2>Redemption History</h2>
        <table>
            <thead>
                <tr>
                    <th>Item Redeemed</th>
                    <th>Points Required</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($redemptionHistory as $entry) : ?>
                    <tr>
                        <td><?php echo $entry['item_redeemed']; ?></td>
                        <td><?php echo $entry['points_required']; ?></td>
                        <td><?php echo $entry['timestamp']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <footer>
        <!-- Your footer content goes here -->
    </footer>
</body>
</html>
