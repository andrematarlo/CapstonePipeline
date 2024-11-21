<?php
session_start();

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['studentid'])) {
    header("Location: login.php");
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentid = trim($_POST['studentid']);
    $password = $_POST['username'];

    // Database credentials moved to a separate configuration file
    $servername = "fdb1029.awardspace.net";
    $username = "4528675_accounts";
    $db_password = "matarlo13"; // Renamed for clarity
    $database = "4528675_accounts";

    // Establish database connection
    $conn = new mysqli($servername, $username, $db_password, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch user from database using prepared statements
    $stmt = $conn->prepare("SELECT * FROM tb_users WHERE studentid = ?");
    $stmt->bind_param("s", $studentid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        // Use password_verify to compare hashed password with plain text
        if (password_verify($password, $row['password'])) {
            $_SESSION['studentid'] = $row['studentid']; // Set session variable
            $stmt->close();
            $conn->close();
            header("Location: index2.php"); // Redirect after successful login
            exit;
        } else {
            echo "<p style='color:red;'>Invalid password.</p>";
        }
    } else {
        echo "<p style='color:red;'>Student ID not found.</p>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="indexstyle2.css"> 
    <style>
        /* Your existing styles here */
        
        /* Modal styles */
        #receiptModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            justify-content: center;
            align-items: center;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
        }

        .modal-content {
            position: relative;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            text-align: center;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Receipt details */
        .receipt-details p {
            margin: 10px 0;
            font-size: 16px;
            color: #333;
        }

        .modal-content p {
            margin: 5px 0; /* Reduced margin for vertical stacking */
        }

        .modal-content h3 {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .modal-content button {
            margin-top: 10px;
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
        }

        .modal-content button:hover {
            background-color: #0056b3;
        }

        /* Admin settings styles */
        .admin-settings {
            margin: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #f9f9f9;
        }

        .admin-settings input[type="number"] {
            width: 60px;
            margin-left: 10px;
        }

        .admin-settings button {
            margin-top: 10px;
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .admin-settings button:hover {
            background-color: #0056b3;
        }

        .update-message {
            margin-top: 10px;
            color: green;
        }

        .welcome-message {
            text-align: center; /* Centers the text */
            font-weight: bold;
            font-size: 24px;
            margin: 20px 0;
            color: white; /* You can keep this or change it based on your design */
            background-color: transparent; /* Removed background color */
            display: block;
            width: 100%; /* Ensures the message is centered within its container */
        }
            .submenu {
    list-style-type: none; /* Remove bullet points */
    padding-left: 20px; /* Indent the submenu */
    margin: 0; /* Remove margin */
}

.submenu li a {
    text-decoration: none; /* Remove underline */
    color: #007bff; /* Link color */
    display: block; /* Make the link area bigger */
    padding: 5px 0; /* Add some padding */
}

.submenu li a:hover {
    text-decoration: underline; /* Underline on hover */
    color: #0056b3; /* Darker color on hover */
}
    </style>
</head>
<body>
    <div class="sidebar">
        <img src="ctulogo.png" alt="Logo" class="logo" style="width: 150px; height: 150px; border-radius: 50%; display: block; margin: 0 auto 20px auto;">    

            
<ul class="dashboard-menu">
    <p class="welcome-message">Welcome, <?php echo $_SESSION['username']; ?>!</p>
    <li><a href="#" onclick="viewPoints()">My Points</a></li>
    <li>
        <a href="#" onclick="toggleSubMenu(event)">Rewards</a>
        <ul class="submenu" style="display: none;">
            <li style="margin-top: 10px;"> <!-- Adjust the margin value as needed -->
                <a href="#" onclick="showRewards()" style="padding: 12px 15px; display: block; background-color: transparent; border-radius: 5px; text-align: center; width: 150px; color: white; border: 1px solid white; transition: background-color 0.3s;" 
                   onmouseover="this.style.backgroundColor='lightgreen'" onmouseout="this.style.backgroundColor='transparent'">
                    Redeem Reward
                </a>
            </li>
            <li>
                <a href="#" onclick="showRedemptionHistory()" style="padding: 12px 15px; display: block; background-color: transparent; border-radius: 5px; text-align: center; width: 150px; color: white; border: 1px solid white; transition: background-color 0.3s;" 
                   onmouseover="this.style.backgroundColor='lightgreen'" onmouseout="this.style.backgroundColor='transparent'">
                    Reward History
                </a>
            </li>
        </ul>
    </li>
    <li><a href="logout.php" class="logout-link" onclick="confirmLogout(event)">Logout</a></li>
</ul>

        <div class="footer">
            <p style="text-align: center;">&copy; 2024 TheJuans.<br> All Rights Reserved.</p>
        </div>
    </div>
    <div class="content-container">
        <div class="content" id="mainContent">
            <img src="gifbin.gif" alt="Garbage Insertion GIF" style="position: absolute; top: 35%; left: 50%; transform: translate(-50%, -50%);">    
            <h1 style="font-size: 48px; color: black; font-weight: bold; text-align: center; position: absolute; top: 75%; left: 50%; transform: translate(-40%, -50%); padding-left: 20px;">
            PLEASE DROP YOUR GARBAGE...
            </h1>
          
        </div>

  
        
    <!-- Receipt Modal -->
    <div id="receiptModal">
        <div class="modal-content">
            <img src="logo.png" alt="Watermark" class="watermark-image">
            <div class="receipt-details">
                <p id="receiptTransactionId">Transaction ID:</p>    
                <p id="receiptReward">Reward:</p>
                <p id="receiptDate">Date:</p>
                <p id="receiptPointsRequired">Points Required:</p>
                <p id="receiptRemainingPoints">Remaining Points:</p>
                <button onclick="printReceipt()">Print Receipt</button>
                <button onclick="closeModal()">Close</button>    
            </div>
        </div>
    </div>

  <script>
          
          function toggleSubMenu(event) {
    event.preventDefault(); // Prevent the default anchor click behavior
    const submenu = event.target.nextElementSibling; // Get the submenu next to the clicked link
    if (submenu.style.display === "none" || submenu.style.display === "") {
        submenu.style.display = "block"; // Show the submenu
    } else {
        submenu.style.display = "none"; // Hide the submenu
    }
}
          
          function confirmLogout(event) {
        // Prevent the default action (navigation) until the user confirms
        event.preventDefault();
        
        // Show a confirmation dialog
        const userConfirmed = confirm("Are you sure you want to log out?");
        
        // If the user confirms, proceed with the logout
        if (userConfirmed) {
            window.location.href = event.target.href; // Redirect to the logout page
        }
    }
          
          
          
      let pointsAdded = false;
    let logoutTimer;

    function resetLogoutTimer() {
        clearTimeout(logoutTimer); // Clear any previous timers
        logoutTimer = setTimeout(function() {
            if (!pointsAdded) {
                // Show alert when no points are added within 1 minute
                alert("No activity detected for 2 minutes. You will be logged out.");
                window.location.href = "logout.php"; // Redirect to logout after alert is acknowledged
            }
        }, 1200000000);
    }

function fetchSensorData() {
    // Default points value to use if input is empty
    const defaultPoints = 10; 
    let pointsToAdd = defaultPoints;

    // Check if there is a points input field and get its value
    const pointsInput = document.getElementById('points');
    if (pointsInput && pointsInput.value) {
        pointsToAdd = parseInt(pointsInput.value, 10) || defaultPoints; // Fallback to default if NaN
    }

    $.ajax({
        url: "fetch_data6.php?points=" + pointsToAdd, // Include points in the URL
        type: "GET",
        dataType: "json",
        success: function(data) {
            if (data.error) {
                showError("Error: " + data.error);
            } else {
                // Check if points were added
                if (data.pointsAdded) {
                    pointsAdded = true; // Points were added
                    resetLogoutTimer();  // Reset the logout timer
                    alert("Congratulations you earned " + data.pointsAdded + " Points.");
                    // Optionally update the points on the screen
                    if (data.newPoints !== undefined) {
                        showUserPoints(data.newPoints); // Display updated points
                    }
                } else {
                    pointsAdded = false;
                }
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            showError("Fetching Sensor data...");
            console.log("Error: " + textStatus, errorThrown);
        }
    });
}

// Set the interval to check the sensor data every 3 seconds
setInterval(fetchSensorData, 3000);




    // Start the logout timer as soon as the page loads
    resetLogoutTimer();


        function updatePoints(message) {
            if (message.includes('User points updated')) {
                // Fetch updated user points
                $.ajax({
                    url: "redeem_reward.php",
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        if (data.error) {
                            showError("Error: " + data.error);
                        } else {
                            showUserPoints(data.points);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        showError("Error fetching user points.");
                        console.log("Error: " + textStatus, errorThrown);
                    }
                });
            }
        }

        function showError(message) {
            var sensorDataDiv = document.getElementById("sensor-data");
            sensorDataDiv.innerHTML = `<p>${message}</p>`;
        }

       

        function viewPoints() {
            $.ajax({
                url: "redeem_reward.php",
                type: "GET",
                dataType: "json",
                success: function(data) {
                    if (data.error) {
                        showError("Error: " + data.error);
                    } else {
                        showUserPoints(data.points);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    showError("Error fetching user points.");
                    console.log("Error: " + textStatus, errorThrown);
                }
            });
        }

 function showUserPoints(points) {
    var mainContent = document.getElementById("mainContent");

    // Clear any existing content and center the new content with additional bottom padding
    mainContent.innerHTML = `
        <div style='display: flex; justify-content: center; align-items: center; height: calc(100vh - 150px); padding-bottom: 50px;'>
            <div style='text-align: center;'>
                <h2 style='font-size: 36px; margin-bottom: 20px;'>Your Points:</h2>
                <p style='font-size: 48px; color: #28a745;'>You have ${points} points.</p>
            </div>
        </div>
    `;
}



        function showRewards() {
            // Fetch rewards
            fetchRewards();
        }
        
function redeemReward(item, pointsRequired) {
    // Fetch the current student ID from the session
    var studentid = "<?php echo htmlspecialchars($_SESSION['studentid']); ?>";

    // Create a new XMLHttpRequest object for redeeming the reward
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "redeem_reward.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    // Handle the response from the server
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        // Show success alert
                        alert(response.success);

                        // Show the receipt modal and pass the transaction ID
                        showReceiptModal(item, pointsRequired, response.remainingPoints, response.transactionId);

                        // Refresh rewards after redemption
                        fetchRewards();

                        // Pass contactNumber from the response to sendSMSNotification
                        var contactNumber = response.contactNumber;
                        sendSMSNotification(contactNumber, item);

                    } else {
                        alert(response.error);  // Show error if redemption fails
                    }
                } catch (e) {
                    alert('Error: Invalid response from server.');
                }
            } else {
                alert('Error: ' + xhr.status);
            }
        }
    };

    // Send the POST request with student ID, item, and points required
    xhr.send("studentid=" + encodeURIComponent(studentid) + "&item=" + encodeURIComponent(item) + "&pointsRequired=" + encodeURIComponent(pointsRequired));
}
          
function sendSMSNotification(contactNumber, item) {
    var message = "You have successfully redeemed the reward: " + item + " from TrashsureBin!";

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "send_sms.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        console.log("SMS sent successfully: " + response.success);
                    } else {
                        console.error("Error sending SMS: " + response.error);
                    }
                } catch (e) {
                    console.error("Error parsing SMS response: " + xhr.responseText);
                }
            } else {
                console.error("Failed to send SMS. HTTP Status: " + xhr.status);
            }
        }
    };

    // Send the POST request with contact number and message
    xhr.send("contact_number=" + encodeURIComponent(contactNumber) + "&message=" + encodeURIComponent(message));
}





      function showReceiptModal(item, pointsRequired, remainingPoints, transactionId) {
    // Get the current date
    var currentDate = new Date();

    // Display the transaction ID, reward details, and date in the receipt modal
    document.getElementById('receiptReward').innerText = "Reward: " + item;
    document.getElementById('receiptDate').innerText = "Date: " + currentDate.toLocaleString();
    document.getElementById('receiptPointsRequired').innerText = "Points Required: " + pointsRequired;
    document.getElementById('receiptRemainingPoints').innerText = "Remaining Points: " + remainingPoints;

    // Add the transaction ID to the receipt modal
    var transactionIdElement = document.getElementById('receiptTransactionId');
    if (!transactionIdElement) {
        // If the element doesn't exist, create it
        transactionIdElement = document.createElement('p');
        transactionIdElement.id = 'receiptTransactionId';
        var receiptDetailsDiv = document.querySelector('.receipt-details');
        receiptDetailsDiv.insertBefore(transactionIdElement, document.getElementById('receiptReward'));
    }
    transactionIdElement.innerText = "Transaction ID: " + transactionId;

    // Display the modal
    document.getElementById('receiptModal').style.display = 'flex';
}
          function printReceipt() {
    // Store original page content
    var originalContent = document.body.innerHTML;
    
    // Get the receipt modal content
    var printContent = document.querySelector('.modal-content').outerHTML;

    // Replace the body's content with the receipt for printing
    document.body.innerHTML = printContent;
    
    // Trigger the print dialog
    window.print();
    
    // Restore the original content after printing
    document.body.innerHTML = originalContent;

    // After printing, ensure the modal is still visible
    closeModal(); // Optionally close the modal after print, or remove this line if you want to keep it open
}

function closeModal() {
    // Hide the modal by changing its display style
    document.getElementById('receiptModal').style.display = 'none';
}





        function fetchRewards() {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        var rewards = JSON.parse(xhr.responseText);
                        showRewardsList(rewards);
                    } else {
                        console.error('Failed to fetch rewards: ' + xhr.status);
                    }
                }
            };
            xhr.open('GET', 'get_rewards.php', true);
            xhr.send();
        }

          function showRewardsList(rewards) {
    var mainContent = document.getElementById("mainContent");

    // Set the mainContent to relative positioning so the button can be positioned within it
    mainContent.style.position = "relative";

    // Generate HTML for rewards list
    var html = "<h2 style='text-align: center;'>Rewards</h2>"; // Center the title

   

    html += "<div id='rewards-list' style='display: flex; justify-content: center; align-items: center; flex-wrap: wrap; margin-top: 20px;'>"; // Center the rewards list

    rewards.forEach(function(reward) {
        html += "<div class='reward-item' style='display: flex; flex-direction: column; align-items: center; margin: 20px; text-align: center;'>"; // Center each reward item
        html += "<img src='" + reward.image + "' alt='" + reward.name + "' style='width: 150px; height: 150px; margin-bottom: 10px; border: 2px solid #ccc; border-radius: 5px;'>"; // Increase image size
        html += "<p>" + reward.name + " - " + reward.pointsRequired + " Points</p>";
        html += "<p>Stocks: " + reward.stocks + "</p>";
        html += "<button style='padding: 10px 20px; font-size: 16px; background-color: darkgreen; color: white; border: none; border-radius: 5px; cursor: pointer;' onclick='redeemReward(\"" + reward.name + "\", " + reward.pointsRequired + ")'>Redeem</button>"; // Increase button size and style
        html += "</div>";
    });

    html += "</div>";
    mainContent.innerHTML = html;
}





        function showRedemptionHistory() {
            $.ajax({
                url: "redeem_reward.php", // Assuming this file returns redemption history
                type: "GET",
                dataType: "json",
                success: function(data) {
                    if (data.error) {
                        showError("Error: " + data.error);
                    } else {
                        displayRedemptionHistory(data.history);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    showError("Error fetching redemption history.");
                    console.log("Error: " + textStatus, errorThrown);
                }
            });
        }

function displayRedemptionHistory(history) {
    var mainContent = document.getElementById("mainContent");
    var currentPage = 1;
    var rowsPerPage = 10;

    // Function to render a specific page of the table
    function renderTablePage() {
        mainContent.innerHTML = "<h2 style='text-align: center;'>Redeemed Rewards History</h2>";

        if (history.length === 0) {
            mainContent.innerHTML += "<p style='text-align: center;'>No redemption history available.</p>";
            return;
        }

        // Calculate the start and end index for the current page
        var startIndex = (currentPage - 1) * rowsPerPage;
        var endIndex = Math.min(startIndex + rowsPerPage, history.length);

        // Table structure
        var table = `
        <div style='display: flex; justify-content: center;'>
            <table style='border-collapse: collapse; width: 80%; text-align: center; border: 1px solid #ddd;'>
                <thead style='background-color: #f2f2f2;'>
                    <tr>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Item Redeemed</th>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Points Required</th>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Timestamp</th>
                    </tr>
                </thead>
                <tbody>`;

        // Populate table rows with data
        for (var i = startIndex; i < endIndex; i++) {
            var entry = history[i];
            table += `
            <tr>
                <td style='border: 1px solid #ddd; padding: 8px;'>${entry.item_redeemed}</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>${entry.points_required}</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>${entry.datetimestamp}</td>
            </tr>`;
        }

        table += "</tbody></table></div>";

        // Add the table to the main content
        mainContent.innerHTML += table;

        // Add pagination buttons
        var pagination = "<div style='text-align: center; margin-top: 20px;'>";
        if (currentPage > 1) {
            pagination += "<button onclick='prevPage()' style='padding: 8px 15px; font-size: 14px; margin-right: 10px;'>Previous</button>";
        }
        if (endIndex < history.length) {
            pagination += "<button onclick='nextPage()' style='padding: 8px 15px; font-size: 14px;'>Next</button>";
        }
        pagination += "</div>";

        mainContent.innerHTML += pagination;
    }

    // Define the global functions to go to the next and previous pages
    window.nextPage = function() {
        if (currentPage * rowsPerPage < history.length) {
            currentPage++;
            renderTablePage();
        }
    }

    window.prevPage = function() {
        if (currentPage > 1) {
            currentPage--;
            renderTablePage();
        }
    }

    // Render the first page when the function is called
    renderTablePage();
}


    </script>
</body>
</html>
