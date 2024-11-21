<?php
session_start();

// Database credentials
$servername = "fdb1029.awardspace.net";
$username_db = "4528675_accounts";
$password_db = "matarlo13";
$database = "4528675_accounts";

// Initialize variables
$studentid = "";
$adminid = "";
$student_login_error = "";
$admin_login_error = "";

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'student') {
        $studentid = trim($_POST['studentid']);

        // Check if student ID is provided
        if (empty($studentid)) {
            $student_login_error = "Student ID is required.";
        } else {
            // Create connection
            $conn = new mysqli($servername, $username_db, $password_db, $database);

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Query to check if student ID exists in tb_users table
            $query = "SELECT * FROM tb_users WHERE studentid = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $studentid);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                
                // Store the student's username in session
                $_SESSION['studentid'] = $studentid;
                $_SESSION['username'] = $row['username'];  // Assuming you have a 'username' field in your table

                // Redirect to greeting page
                header("Location: greetings.php");
                exit();
            } else {
                $student_login_error = "Invalid student ID.";
            }

            $stmt->close();
            $conn->close();
        }
    
    } elseif (isset($_POST['form_type']) && $_POST['form_type'] === 'admin') {
        $adminid = trim($_POST['adminid']);
        $password = $_POST['password'];

        // Create connection
        $conn = new mysqli($servername, $username_db, $password_db, $database);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Query to check if admin ID exists in admin_table
        $query = "SELECT * FROM admin_table WHERE adminid = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $adminid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            // Verify the password
            if ($password === $row['password']) {
                $_SESSION['adminid'] = $adminid;
                header("Location: index.php");
                exit();
            } else {
                $admin_login_error = "Invalid password.";
            }
        } else {
            $admin_login_error = "Admin ID not found.";
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
  <title>Login</title>
  <link rel="stylesheet" href="login.css">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <style>
    /* Additional CSS to style the buttons at the top-right */
    .top-right-buttons {
      position: absolute;
      top: 10px;
      right: 10px;
      display: flex;
      gap: 10px;
    }

    .top-right-buttons button,
    .top-right-buttons a {
      background-color: #4CAF50;
      color: white;
      padding: 10px 20px;
      border: none;
      cursor: pointer;
      text-decoration: none;
      font-size: 16px;
    }

    .top-right-buttons button:hover,
    .top-right-buttons a:hover {
      background-color: #45a049;
    }

    /* Initially hide the student form */
    #studentForm {
      display: none;
    }

    /* Styling for the back button as an icon next to Admin Login */
    .back-button {
      margin-right: 10px;
      background-color: transparent;
      border: none;
      font-size: 24px;
      color: #333;
      cursor: pointer;
    }

    .back-button:hover {
      color: gray;
    }

    .admin-header {
      display: flex;
      align-items: center;
      justify-content: flex-start;
    }
  </style>
  <script>
    function toggleForm(formType) {
    const studentForm = document.getElementById('studentForm');
    const adminForm = document.getElementById('adminForm');
    const adminLoginButton = document.querySelector('.top-right-buttons button'); // Select the Admin Login button

    if (formType === 'student') {
        studentForm.style.display = 'block';
        adminForm.style.display = 'none';
        adminLoginButton.style.display = 'block'; // Show the Admin Login button when in the student form
    } else if (formType === 'admin') {
        studentForm.style.display = 'none';
        adminForm.style.display = 'block';
        adminLoginButton.style.display = 'none'; // Hide the Admin Login button when in the admin form
    }
}


    // Function to handle PHP-generated alerts
    function handleAlerts() {
      <?php
      if (!empty($student_login_error)) {
        echo "setTimeout(function() { alert('".$student_login_error."'); }, 100);";
      }
      if (!empty($admin_login_error)) {
        echo "setTimeout(function() { alert('".$admin_login_error."'); }, 100);";
      }
      ?>
    }

    document.addEventListener('DOMContentLoaded', function() {
        const studentForm = document.getElementById('studentForm');
        const studentIdInput = document.querySelector('[name="studentid"]');
        let typingTimer;  // Timer for detecting the end of barcode scan
        const doneTypingInterval = 3000; // Time in ms (0.5 seconds) after barcode input completes

        // Automatically submit the form when input stops for 500ms
        studentIdInput.addEventListener('input', function() {
            clearTimeout(typingTimer); // Reset the timer on every input
            typingTimer = setTimeout(function() {
                studentForm.submit();  // Automatically submit the form after input stops
            }, doneTypingInterval);
        });

        // Show the student form when the barcode scanner starts input
        studentIdInput.addEventListener('focus', function() {
            studentForm.style.display = 'block';
        });

        // Automatically focus on the input field
        studentIdInput.focus();

        // Call handleAlerts on page load
        window.onload = function() {
            toggleForm('student'); // Initial form display
            handleAlerts(); // Handle any PHP-generated alerts
        };
    });
  </script>
</head>
<body>
  <!-- Top-right buttons for Admin Login and Register -->
  <div class="top-right-buttons">
    <button onclick="toggleForm('admin')">Admin Login</button>
  </div>

  <div class="wrapper">
    <!-- Student Login Form -->
    <form id="studentForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
      <h2>Student Login</h2>
     
      <input type="hidden" name="form_type" value="student">
      <div class="input-field">
        <input type="text" name="studentid" value="<?php echo htmlspecialchars($studentid); ?>" required autofocus autocomplete="off">
        <label>Scan your student ID barcode</label>
      </div>
    </form>

    <!-- Admin Login Form -->
    <form id="adminForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" style="display: none;">
      <div class="admin-header">
        <!-- Add Back button as an icon to switch back to student login form -->
        <button type="button" class="back-button" onclick="toggleForm('student')">
          <i class="fas fa-arrow-left"></i> <!-- Back Icon -->
        </button>
        <h2>Admin Login</h2>
      </div>
      
      <input type="hidden" name="form_type" value="admin">
      <div class="input-field">
        <input type="text" name="adminid" value="<?php echo htmlspecialchars($adminid); ?>" required>
        <label>Enter your admin ID</label>
      </div>
      <div class="input-field">
        <input type="password" name="password" required>
        <label>Enter your password</label>
      </div>
      <!-- Add Login button for admin form -->
      <button type="submit">Log In</button>
    </form>
  </div>
</body>
</html>