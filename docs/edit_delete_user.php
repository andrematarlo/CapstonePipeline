<?php
// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection details
    $dsn = "mysql:host=fdb1029.awardspace.net;dbname=4528675_accounts";
    $username = "4528675_accounts";
    $password = "matarlo13";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    try {
        // Establish a connection to the database
        $pdo = new PDO($dsn, $username, $password, $options);

        // Check if it's an edit request
        if (isset($_POST["action"]) && $_POST["action"] == "edit") {
            if (isset($_POST["id"]) && isset($_POST["new_username"])) {
                $userId = $_POST["id"];
                $newUsername = $_POST["new_username"];

                // Check if the new username already exists
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_users WHERE username = :username AND studentid != :id");
                $stmt->bindParam(":username", $newUsername);
                $stmt->bindParam(":id", $userId);
                $stmt->execute();
                $count = $stmt->fetchColumn();

                if ($count > 0) {
                    // Username already exists, return error response
                    echo json_encode(["error" => "Username already exists"]);
                } else {
                    // Update the user's information
                    $stmt = $pdo->prepare("UPDATE tb_users SET username = :username WHERE studentid = :id");
                    $stmt->bindParam(":id", $userId);
                    $stmt->bindParam(":username", $newUsername);
                    $stmt->execute();

                    // Return a JSON response indicating success
                    echo json_encode(["success" => true]);
                }
            } else {
                echo json_encode(["error" => "Student ID or new username not provided"]);
            }
        }
        // Check if it's an edit points request
        elseif (isset($_POST["action"]) && $_POST["action"] == "edit_points") {
            if (isset($_POST["id"]) && isset($_POST["points"])) {
                $userId = $_POST["id"];
                $newPoints = $_POST["points"];

                // Update the user's points
                $stmt = $pdo->prepare("UPDATE tb_users SET points = :points WHERE studentid = :id");
                $stmt->bindParam(":id", $userId);
                $stmt->bindParam(":points", $newPoints);
                $stmt->execute();

                // Return a JSON response indicating success
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["error" => "Student ID or points not provided"]);
            }
        }
        // Check if it's a delete request
        elseif (isset($_POST["action"]) && $_POST["action"] == "delete") {
            if (isset($_POST["id"])) {
                $userId = $_POST["id"];

                // Delete the user
                $stmt = $pdo->prepare("DELETE FROM tb_users WHERE studentid = :id");
                $stmt->bindParam(":id", $userId);
                $stmt->execute();

                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["error" => "Student ID not provided"]);
            }
        } else {
            echo json_encode(["error" => "Invalid action"]);
        }
    } catch (PDOException $e) {
        // Handle database connection error
        echo json_encode(["error" => $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>
