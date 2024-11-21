<?php
session_start();

if (!isset($_SESSION['studentid'])) {
    exit('Unauthorized');
}

// Database connection parameters
$servername = "fdb1029.awardspace.net";
$username = "4528675_accounts";
$password = "matarlo13";
$database = "4528675_accounts";

// Create a new MySQLi connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle POST request for redeeming a reward
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['studentid'], $_POST['item'], $_POST['pointsRequired'])) {
        $studentid = $_POST['studentid'];
        $item = $_POST['item'];
        $pointsRequired = intval($_POST['pointsRequired']);

        // Fetch user's points, email, and contact number from the database
        $stmt = $conn->prepare("SELECT points, email, contact_number FROM tb_users WHERE studentid = ?");
        $stmt->bind_param("s", $studentid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $points = $row['points'];
            $email = $row['email'];  // User's email from the database
            $contactNumber = $row['contact_number'];  // User's contact number for SMS

            // Check if the user has enough points
            if ($points >= $pointsRequired) {
                // Check if the item is in stock
                $stmt = $conn->prepare("SELECT stocks FROM rewards_table WHERE name = ?");
                $stmt->bind_param("s", $item);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $stocks = $row['stocks'];

                    if ($stocks > 0) {
                        $newPoints = $points - $pointsRequired;

                        // Start a transaction
                        $conn->begin_transaction();

                        try {
                            // Update user points
                            $stmt = $conn->prepare("UPDATE tb_users SET points = ? WHERE studentid = ?");
                            $stmt->bind_param("is", $newPoints, $studentid);
                            $stmt->execute();

                            // Update the stock of the redeemed item
                            $stmt = $conn->prepare("UPDATE rewards_table SET stocks = stocks - 1 WHERE name = ?");
                            $stmt->bind_param("s", $item);
                            $stmt->execute();

                            // Insert redemption history and fetch the transaction ID
                            $stmt = $conn->prepare("INSERT INTO redemption_history (studentid, item_redeemed, points_required, datetimestamp) VALUES (?, ?, ?, NOW())");
                            $stmt->bind_param("ssi", $studentid, $item, $pointsRequired);
                            $stmt->execute();

                            // Fetch the transaction ID (last inserted ID)
                            $transactionId = $conn->insert_id;

                            // Commit the transaction
                            $conn->commit();

                            // Redemption was successful, return the transaction ID and other details
                            echo json_encode([
                                'success' => "Redeemed $item successfully!",
                                'remainingPoints' => $newPoints,
                                'transactionId' => $transactionId,  // Include the transaction ID
                                'email' => $email,
                                'contactNumber' => $contactNumber  // Send contact number for SMS
                            ]);

                        } catch (Exception $e) {
                            $conn->rollback();
                            echo json_encode(['error' => "An error occurred during redemption."]);
                        }
                    } else {
                        echo json_encode(['error' => "$item is out of stock."]);
                    }
                } else {
                    echo json_encode(['error' => "Item not found."]);
                }
            } else {
                echo json_encode(['error' => "Insufficient points to redeem $item."]);
            }
        } else {
            echo json_encode(['error' => "Student not found."]);
        }
    }
} else {
    // Handle GET requests to fetch user points and redemption history
    $studentid = $_SESSION['studentid'];
    $stmt = $conn->prepare("SELECT points FROM tb_users WHERE studentid = ?");
    $stmt->bind_param("s", $studentid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $userPoints = $row['points'];

        // Fetch redemption history
        $stmt = $conn->prepare("SELECT * FROM redemption_history WHERE studentid = ?");
        $stmt->bind_param("s", $studentid);
        $stmt->execute();
        $historyResult = $stmt->get_result();

        $history = [];
        while ($row = $historyResult->fetch_assoc()) {
            $history[] = $row;
        }

        // Respond with points and history data
        echo json_encode(array('points' => $userPoints, 'history' => $history));
    } else {
        // Log user points not found error
        error_log('User points not found');
        echo json_encode(array('error' => 'User points not found.'));
    }
}

// Close the database connection
$conn->close();
?>
