<?php
session_start();
// Database credentials
$servername = "fdb1029.awardspace.net";
$username_db = "4528675_accounts";
$password_db = "matarlo13";
$database = "4528675_accounts";

// Create connection
$conn = new mysqli($servername, $username_db, $username_db, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$verification_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify_otp'])) {
    $user_otp = $_POST['otp'];

    // Check if the user-entered OTP matches the one in the session
    if ($user_otp == $_SESSION['otp']) {
        // OTP matches, now insert the data into the database
        $studentid = $_SESSION['studentid'];
        $username = $_SESSION['username'];
        $hashed_password = $_SESSION['password'];
        $contact_number = $_SESSION['contact_number'];

        // Insert the user data into the database
        $insert_query = "INSERT INTO tb_users (studentid, username, password, contact_number) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssss", $studentid, $username, $hashed_password, $contact_number);

        if ($stmt->execute()) {
            // Clear the session data to prevent reuse
            session_unset();
            session_destroy();

            // Success message
            $verification_message = "OTP verified successfully! Registration complete.";
        } else {
            $verification_message = "Error inserting data: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $verification_message = "Invalid OTP. Please try again.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OTP Verification</title>
    <link rel="stylesheet" href="verify.css">
</head>
<body>
    <div class="wrapper">
        <h2>OTP Verification</h2>

        <?php if (!empty($verification_message)) : ?>
            <p class="error-message"><?php echo htmlspecialchars($verification_message); ?></p>
        <?php endif; ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="input-field">
                <input type="text" name="otp" id="otp" placeholder="Enter OTP" required>
            </div>
            <button type="submit" name="verify_otp">Verify OTP</button>
        </form>
    </div>
</body>
</html>
