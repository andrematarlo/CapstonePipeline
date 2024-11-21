<?php
session_start();

// Establish database connection
$servername = "fdb1029.awardspace.net";
$username_db = "4528675_accounts";
$password_db = "matarlo13";
$database = "4528675_accounts";

$conn = new mysqli($servername, $username_db, $password_db, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$registration_message = "";
$registration_success = false; // Track if registration is successful

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verified']) && $_POST['verified'] === 'true') {
    // User has verified their email and submitted the form
    $studentid = filter_var($_POST['studentid'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $username = filter_var($_POST['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $course = filter_var($_POST['course'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $contact_number = filter_var($_POST['contact_number'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Check if passwords match
    if ($password != $confirm_password) {
        $registration_message = "Passwords do not match.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registration_message = "Invalid email format.";
    } else if (empty($course)) {
        $registration_message = "Please select a course.";
    } else if (!preg_match('/^[0-9]{10,15}$/', $contact_number)) {
        $registration_message = "Invalid contact number. Must start at 09";
    } else {
        // Check if student ID already exists
        $check_query = "SELECT * FROM tb_users WHERE studentid=?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("s", $studentid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $registration_message = "Student ID already exists.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user into the database, including course and contact number
            $insert_query = "INSERT INTO tb_users (studentid, username, password, email, course, contact_number) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("ssssss", $studentid, $username, $hashed_password, $email, $course, $contact_number);

            if ($stmt->execute()) {
                $registration_message = "Registration successful!";
                $registration_success = true; // Set success flag
            } else {
                $registration_message = "Error: " . $stmt->error;
            }
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,500,600" rel="stylesheet">
    <style>
      body {
            font-family: 'Poppins', sans-serif;
            background-color: gray;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            display: flex;
            width: 50%; /* Reduced overall width */
            height: auto; /* Adjust height to be dynamic */
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }

       .container .image-section {
    flex: 2.5;
    background: url('regpic11.png') no-repeat center center/cover;
    padding-right:30px;
}

       .container .form-section {
    flex: 2.5;
    background-color: #111;
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: center; /* Centers the form vertically */
    align-items: center; /* Centers the form horizontally */
    text-align: center; /* Ensures the text is centered */
    width: 100%;
}

.form-section h2 {
    color: #ffffff;
    margin-bottom: 20px;
    font-size: 22px;
    text-align: center; /* Center the header */
}

        .input-field {
    width: 100%;
    margin-bottom: 10px;
    display: flex;
    justify-content: center; /* Centers the input fields horizontally */
}

.input-field input, .input-field select {
    width: 100%; /* Input fields will be smaller and centered */
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
    margin-bottom: 15px;
    font-size: 14px;
    text-align: left;
}

button {
    width: 80%;
    padding: 10px;
    background-color: #27ae60;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    font-weight: 600;
    color: #ffffff;
    cursor: pointer;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #2ecc71;
}
        .error-message {
            color: #ff6b6b;
            text-align: center;
            margin-bottom: 10px;
        }

        p {
            text-align: center;
            color: #ffffff;
        }

        a {
            color: #27ae60;
            text-decoration: none;
            transition: color 0.3s;
        }

        a:hover {
            color: #2ecc71;
        }
    </style>
         <!-- Firebase App (the core Firebase SDK) -->
    <script src="https://www.gstatic.com/firebasejs/7.20.0/firebase-app.js"></script>
    <!-- Firebase Authentication -->
    <script src="https://www.gstatic.com/firebasejs/7.20.0/firebase-auth.js"></script>
</head>
<body>
    <div class="container">
        <div class="image-section">
            <!-- Left side with image -->
        </div>

        <div class="form-section">
            <h2>Registration Info</h2>

            <?php if (!empty($registration_message)) : ?>
                <p class="error-message"><?php echo htmlspecialchars($registration_message); ?></p>
            <?php endif; ?>

            <form id="register-form" method="POST" action="register.php">
                <div class="input-field">
                    <input type="text" name="studentid" id="studentid" placeholder="Student ID" required>
                </div>
                <div class="input-field">
                    <input type="text" name="username" id="username" placeholder="Full Name" required>
                </div>
                <div class="input-field">
                    <input type="password" name="password" id="password" placeholder="Password" required>
                </div>
                <div class="input-field">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                </div>
                <div class="input-field">
                    <select name="course" id="course" required>
                        <option value="">--Select Course--</option>
                        <option value="BEED-CE">BEED-CE</option>
                        <option value="BTLEd">BTLEd</option>
                        <option value="BSEd Math">BSEd Math</option>
                        <option value="AB-ELS">AB-ELS</option>
                        <option value="ABLit">ABLit</option>
                        <option value="BIT-CompTech">BIT-CompTech</option>
                        <option value="BIT-Electronics">BIT-Electronics</option>
                        <option value="BIT-Drafting">BIT-Drafting</option>
                        <option value="BIT-Garments">BIT-Garments</option>
                        <option value="BSIE">BSIE</option>
                        <option value="BSIT">BSIT</option>
                        <option value="BSHM">BSHM</option>
                        <option value="BSF">BSF</option>
                        <option value="BSA">BSA</option>
                    </select>
                </div>
                <div class="input-field">
                    <input type="text" name="contact_number" id="contact_number" placeholder="Contact Number" required>
                </div>
                <div class="input-field">
                    <input type="email" name="email" id="email" placeholder="Email Address" required>
                </div>
                <input type="hidden" name="verified" id="verified" value="false">
                <button type="submit" id="register-button">Submit</button>
            </form>

            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>

   <script>
    // Check for successful registration
    <?php if ($registration_success): ?>
        alert("Registration successful! You can now log in.");
        window.location.href = "login.php"; // Redirect to login after successful registration
    <?php endif; ?>

    // Firebase configuration
    const firebaseConfig = {
        apiKey: "AIzaSyBpVXVp-N_cXPKyJiGEIjm9H9wQlyIBeX8",
        authDomain: "trashsurebin.firebaseapp.com",
        projectId: "trashsurebin",
        storageBucket: "trashsurebin.appspot.com",
        messagingSenderId: "1031191124393",
        appId: "1:1031191124393:web:4e4f59754191b8b1f08cb8",
        measurementId: "G-WC9KRNKSYB"
    };

    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);

    document.getElementById('register-form').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent form submission
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        // Check if passwords match before proceeding
        const confirmPassword = document.getElementById('confirm_password').value;
        if (password !== confirmPassword) {
            alert('Passwords do not match. Please re-enter them.');
            document.getElementById('password').value = '';
            document.getElementById('confirm_password').value = '';
            return;
        }

        // Create the user in Firebase and send verification email
        firebase.auth().createUserWithEmailAndPassword(email, password)
            .then(function(userCredential) {
                const user = userCredential.user;

                // Send verification email
                user.sendEmailVerification().then(function() {
                    alert('Verification email sent. Please verify your email before continuing.');

                    // Check every 3 seconds if the user has verified the email
                    const checkVerification = setInterval(function() {
                        user.reload().then(() => {
                            if (user.emailVerified) {
                                clearInterval(checkVerification);
                                document.getElementById('verified').value = "true";
                                document.getElementById('register-form').submit(); // Submit the form after verification
                            }
                        });
                    }, 3000);
                }).catch(function(error) {
                    alert('Error sending verification email: ' + error.message);
                });
            })
            .catch(function(error) {
                // Log detailed error if user creation fails
                alert('Error creating user: ' + error.message);
            });
    });
</script>

</body>
</html>

