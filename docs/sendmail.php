<?php
// Turn on output buffering to capture any unwanted output
ob_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer classes
require '/srv/disk9/4528675/www/trashsurebin.onlinewebshop.net/PHPMailer-master/PHPMailer-master/src/Exception.php';
require '/srv/disk9/4528675/www/trashsurebin.onlinewebshop.net/PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
require '/srv/disk9/4528675/www/trashsurebin.onlinewebshop.net/PHPMailer-master/PHPMailer-master/src/SMTP.php';

// Database connection
$servername = "fdb1029.awardspace.net";
$username = "4528675_accounts";
$password = "matarlo13";
$database = "4528675_accounts";

$conn = new mysqli($servername, $username, $password, $database);

// Check the database connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die(json_encode(array('error' => 'Database connection failed.')));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['studentid']) && isset($_POST['subject']) && isset($_POST['message'])) {
        $studentid = $_POST['studentid'];
        $subject = $_POST['subject'];
        $message = $_POST['message'];

        // Fetch the user's email from the tb_users table based on studentid
        $stmt = $conn->prepare("SELECT email FROM tb_users WHERE studentid = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            echo json_encode(array('error' => 'Query preparation failed.'));
            exit();
        }
        $stmt->bind_param("s", $studentid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $to = $row['email'];  // Get the user's email

            // Validate the email address
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(array('error' => 'Invalid email format.'));
                exit();
            }

            // Instantiate PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Set sender and recipient details
                $mail->setFrom('noreply@trashsurebin.onlinewebshop.net', 'TrashsureBin');
                $mail->addAddress($to);  // Add recipient (user's email from tb_users)

                // Email content
                $mail->isHTML(false);  // Set email format to plain text
                $mail->Subject = $subject;
                $mail->Body    = $message;

                // Send email using PHP mail() function
                if ($mail->send()) {
                    echo json_encode(array('success' => 'Email sent successfully to ' . $to));
                } else {
                    echo json_encode(array('error' => 'Email could not be sent using PHP mail() function.'));
                }
            } catch (Exception $e) {
                error_log('Email error: ' . $mail->ErrorInfo);
                echo json_encode(array('error' => 'Email could not be sent. Error: ' . $mail->ErrorInfo));
            }
        } else {
            echo json_encode(array('error' => 'Student ID not found.'));
        }

        $stmt->close();
    } else {
        echo json_encode(array('error' => 'Missing email parameters.'));
    }
} else {
    echo json_encode(array('error' => 'Invalid request method. Only POST requests are allowed.'));
}

// Close the database connection
$conn->close();

// Clear any unwanted output
ob_end_flush();
?>
