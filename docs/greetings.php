<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['studentid']) || !isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Database credentials
$servername = "fdb1029.awardspace.net";
$username_db = "4528675_accounts";
$password_db = "matarlo13";
$database = "4528675_accounts";

// Create connection
$conn = new mysqli($servername, $username_db, $password_db, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the student's ID and username from the session
$studentid = $_SESSION['studentid'];
$username = $_SESSION['username'];

// Query to fetch the course for the logged-in student
$sql = "SELECT course FROM tb_users WHERE studentid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $studentid);
$stmt->execute();
$result = $stmt->get_result();

// Check if course data is found
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $course = $row['course'];
    $_SESSION['course'] = $course; // Store the course in the session
} else {
    $course = 'Unknown'; // Default if course not found
}

// Close connection
$stmt->close();
$conn->close();

// Set the timezone to Philippines (Asia/Manila)
date_default_timezone_set('Asia/Manila');

// Greeting logic
$hour = date('H');
if ($hour >= 0 && $hour < 12) {
    $greeting = "Good Morning";
} elseif ($hour >= 12 && $hour < 18) {
    $greeting = "Good Afternoon";
} else {
    $greeting = "Good Evening";
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <meta http-equiv="refresh" content="5;url=index2.php">
    <style>
        /* Global styles */
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to bottom right, #e0ffe0, #b3ffb3);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-image: url('greetbg4.png'); /* You can use any image */
background-size: cover;
background-position: center;
background-repeat: no-repeat; /* Prevents tiling */

        }

        /* Centered card style */
        .card {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            padding: 40px;
            text-align: center;
            width: 400px;
            animation: fadeIn 1.5s ease-out;
            backdrop-filter: blur(10px);
        }

        /* Smooth color transition for the heading */
        h1 {
            color: #2e7d32;
            font-size: 32px;
            margin-bottom: 20px;
            animation: colorPulse 4s infinite;
            font-family: 'Georgia', serif;
        }

        /* Simple paragraph styling */
        p {
            color: #444;
            font-size: 18px;
            margin-bottom: 20px;
        }

        /* Animation for fading in */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Color pulse animation for heading */
        @keyframes colorPulse {
            0% { color: #2e7d32; }
            50% { color: #388e3c; }
            100% { color: #2e7d32; }
        }

        /* Subtle footer note */
        .footer {
            font-size: 14px;
            color: #666;
            margin-top: 20px;
        }

        /* Add some plant icons for visual vibe */
        .plant-icon {
            margin: 15px 0;
            font-size: 48px;
            color: #66bb6a;
            animation: bounce 2s infinite;
        }

        /* Bounce animation for icons */
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="plant-icon">🌿</div> <!-- Nature Icon -->
        <h1><?php echo $greeting . ", " . htmlspecialchars($username); ?></h1>
        <p>Course: <?php echo htmlspecialchars($course); ?></p> <!-- Display course here -->
        <h2>Welcome to TrashSure Bin!</h2>
        <p>You will be redirected in 5 seconds...</p>
    </div>
</body>
</html>
