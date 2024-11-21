<?php
session_start(); // Start session to access session variables

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the OTP entered by the user
    $checkotp = filter_var($_POST['checkotp'], FILTER_SANITIZE_NUMBER_INT);
    
    // Retrieve the OTP stored in the session
    if (isset($_SESSION['otp'])) {
        $otp = $_SESSION['otp'];

        // Check if the entered OTP matches the one stored in the session
        if ($checkotp == $otp) {
            echo "OTP Verified and Signup completed";
            
            // Clear the OTP from the session after successful verification
            unset($_SESSION['otp']);
        } else {
            echo "Incorrect OTP. Please try again.";
        }
    } else {
        echo "Session expired or OTP not set. Please try again.";
    }
} else {
    echo "Invalid request.";
}
?>
