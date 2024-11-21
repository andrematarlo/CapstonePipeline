<?php
$servername = "fdb1029.awardspace.net";
$username = "4528675_accounts";
$password = "matarlo13";
$database = "4528675_accounts";

// Create a connection
$conn = new mysqli($servername, $username, $password, $database);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect POST data from the request
    $studentid = $_POST['studentid'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $course = $_POST['course'];
    $contact_number = $_POST['contact_number'];

    // Validate input data
    if (empty($studentid) || empty($username) || empty($email) || empty($course) || empty($contact_number)) {
        echo 'All fields are required.';
        exit;
    }

    // Check if the student ID already exists
    $checkSql = "SELECT * FROM tb_users WHERE studentid = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $studentid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo 'Student ID already exists.';
        $stmt->close();
        $conn->close();
        exit;
    }

    // Prepare and execute SQL query to insert the new student
    $sql = "INSERT INTO tb_users (studentid, username, email, course, contact_number) VALUES (?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssss", $studentid, $username, $email, $course, $contact_number);

        if ($stmt->execute()) {
            echo 'Student added successfully!';
        } else {
            echo 'Error adding student: ' . $stmt->error;
        }

        $stmt->close();
    } else {
        echo 'Database error: ' . $conn->error;
    }

    $conn->close();
}
?>
