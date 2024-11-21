<?php
session_start();

// Database credentials
$servername = "fdb1029.awardspace.net";
$username_db = "4528675_accounts";
$password_db = "matarlo13";
$database = "4528675_accounts";

// Initialize variables
$email = "";
$reset_request_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_request'])) {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $reset_request_message = "Email address is required.";
    } else {
        $conn = new mysqli($servername, $username_db, $password_db, $database);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $query = "SELECT * FROM tb_users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $token = bin2hex(random_bytes(16));
            $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

            // Update reset token and expiry in the database
            $update_query = "UPDATE tb_users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("sss", $token, $expiry, $email);
            $update_stmt->execute();

            // Send the email
            $reset_link = "http://trashsurebin.onlinewebshop.net/reset_password.php?token=" . urlencode($token);
            $subject = "Password Reset Request";
            $message = "To reset your password, please click the following link: <a href=\"$reset_link\">Reset Password</a>";
            $headers = "Content-Type: text/html; charset=UTF-8";

            if (mail($email, $subject, $message, $headers)) {
                $reset_request_message = "A password reset link has been sent to your email address.";
            } else {
                $reset_request_message = "Failed to send password reset email. Please try again later.";
            }
        } else {
            $reset_request_message = "No account found with that email address.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>
  <div class="wrapper">
    <h2>Forgot Password</h2>
    <form id="resetForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
      <input type="hidden" name="reset_request" value="1">
      <div class="input-field">
        <input type="email" name="email" required>
        <label>Enter your email address</label>
      </div>
      <button type="submit">Send Reset Link</button>
      <div class="message">
        <p><?php echo $reset_request_message; ?></p>
      </div>
    </form>
  </div>
</body>
</html>
