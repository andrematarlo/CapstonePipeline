<?php
session_start();

$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$otp = rand(100000, 999999); // Generate a random OTP
$_SESSION['otp'] = $otp; // Store OTP in session

$to = $email;
$from = "no-reply@trashsurebin.onlinewebshop.net";
$fromName = "TrashsureBin";
$subject = "OTP Authentication";
$message = "Your OTP is: $otp.\n\nPlease enter this OTP to complete your registration.";
$headers = "From: $fromName <$from>\r\n";
$headers .= "Reply-To: $from\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

if (mail($to, $subject, $message, $headers)) {
    echo "OTP sent successfully to your email!";
} else {
    echo "Failed to send OTP. Please try again.";
}
?>

<form method="POST" action="submitotp.php">
    Enter OTP
    <div class="input-field">
        <input type="number" name="checkotp" placeholder="Enter OTP" required>
    </div>
    <button type="submit">Verify OTP</button>
</form>
