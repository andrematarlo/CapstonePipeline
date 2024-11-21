<?php
session_start();

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['adminid'])) {
    header("Location: login.php");
    exit;
}

// Establish database connection
$servername = "fdb1029.awardspace.net";
$username = "4528675_accounts";
$password = "matarlo13";
$database = "4528675_accounts";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission to add inventory
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['adminid'])) {
        // Login code
        $adminid = $_POST['adminid'];
        $stmt = $conn->prepare("SELECT * FROM admin_table WHERE username = ?");
        $stmt->bind_param("s", $adminid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $_SESSION['adminid'] = $row['username']; // Set session variable
            $stmt->close();
            $conn->close();
            header("Location: index.php"); // Redirect to index or wherever after successful login
            exit;
        } else {
            echo "<p style='color:red;'>Admin ID not found.</p>";
        }
        $stmt->close();
    } else {
        // Inventory form handling
        $reward_id = $_POST['reward_id'];
        $stocks_added = $_POST['stocks_added'];
        $price = $_POST['price'];
        $datepurchased = $_POST['datepurchased'];

        // Fetch the current total stocks for the selected reward from rewards_table
        $stmt = $conn->prepare("SELECT stocks FROM rewards_table WHERE id = ?");
        $stmt->bind_param("i", $reward_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $current_total_stocks = $row['stocks'];
            $stmt->close();

            // Calculate new total stocks
            $new_total_stocks = $current_total_stocks + $stocks_added;

            // Begin transaction
            $conn->begin_transaction();
            try {
                // Insert into inventory_table
                $stmt = $conn->prepare("INSERT INTO inventory_table (reward_id, datepurchased, stocks_added, total_stocks, price) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("isidi", $reward_id, $datepurchased, $stocks_added, $new_total_stocks, $price);
                $stmt->execute();
                $stmt->close();

                // Update the total stocks in rewards_table
                $stmt = $conn->prepare("UPDATE rewards_table SET stocks = ? WHERE id = ?");
                $stmt->bind_param("ii", $new_total_stocks, $reward_id);
                $stmt->execute();
                $stmt->close();

                // Commit transaction
                $conn->commit();
                echo "<p style='color:green;'>Inventory updated successfully!</p>";
            } catch (Exception $e) {
                // Rollback transaction if there is an error
                $conn->rollback();
                echo "<p style='color:red;'>Error updating inventory: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        } else {
            echo "<p style='color:red;'>Reward ID not found.</p>";
        }
    }
}

// Fetch rewards from database
$rewards = [];
$sql = "SELECT id, name, stocks FROM rewards_table";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rewards[] = $row;
    }
}

$conn->close();
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   <link rel="stylesheet" href="indexstyle.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .analytics-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
        }

        .analytics-box {
            width: 250px;  /* Wider to accommodate larger text */
            height: 150px;  /* Taller for more space */
            background-color: #f0f8ff; /* Light blue background */
            border: 1px solid #ccc;
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .analytics-box h3 {
            margin: 0;
            font-size: 22px;
            color: #333;
        }

        .analytics-box p {
            margin: 10px 0 0;
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
        }
        
        .footer {
            width: 200px;
            color: white;
            text-align: center;
            padding: 1px 0;
            position: fixed;
            bottom: 0;
            left: 0; /* Align footer to the start of the sidebar */
        }
            
.logout-link {
    position: absolute;
    top: 20px;
    right: 20px;
    text-decoration: none;
    background-color: red;
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    z-index: 1000; /* Ensures it stays on top */
}


        /* Ensuring responsiveness */
        @media screen and (max-width: 768px) {
            .analytics-container {
                flex-direction: column;
                align-items: center;
            }
        }
        

.content {
    background-color: white;
    padding: 20px;
    min-height: 100vh; /* Full viewport height */
    position: relative; /* Necessary for the absolute positioning of logout */
}

            .grid-container {
    margin-top: 50px; /* Prevent overlap */
}

             /* Bounce animation */
    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% {
            transform: translateY(0); /* Start and end at original position */
        }
        40% {
            transform: translateY(-10px); /* Move up */
        }
        60% {
            transform: translateY(-5px); /* Move slightly down */
        }
    }

    /* Hover effect to trigger bounce */
    .logout-link:hover {
        animation: bounce 1s ease; /* Apply the bounce animation */
        background-color: red; /* Optional: Change background color on hover */
        color: white; /* Optional: Change text color on hover */
    }
            
    </style>
</head>
<body>
    <div class="sidebar">
        <div>
            <img src="ctulogo.png" alt="Logo" class="logo" style="width: 150px; height: 150px; border-radius: 50%; display: block; margin: 0 auto 20px auto;">
            <p class="welcome-message">Welcome, Admin!</p>
<ul class="dashboard-menu" style="list-style: none; padding: 0;">
    <li style="margin-bottom: 15px; position: relative;">
        <a href="#" onclick="toggleSubMenu(event)" style="text-decoration: none; color: #000; padding: 12px 20px; display: block; background-color: white; border-radius: 5px; text-align: center; margin-bottom: 5px;">Rewards</a>
        <ul class="sub-menu" style="list-style: none; padding-left: 0px; display: none; position: relative; margin-left: 20px;">
            <li style="margin-bottom: 8px;">
                <a href="#" onclick="showAddRewardForm()" style="padding: 6px 15px; display: block; background-color: transparent; border-radius: 5px; text-align: center; width: 150px; color: white; border: 1px solid white; transition: background-color 0.3s;" 
                   onmouseover="this.style.backgroundColor='lightgreen'" onmouseout="this.style.backgroundColor='transparent'">
                    Add Reward
                </a>
            </li>
            <li style="margin-bottom: 8px;">
                <a href="#" onclick="showRedemptionHistory()" style="padding: 6px 15px; display: block; background-color: transparent; border-radius: 5px; text-align: center; width: 150px; color: white; border: 1px solid white; transition: background-color 0.3s;" 
                   onmouseover="this.style.backgroundColor='lightgreen'" onmouseout="this.style.backgroundColor='transparent'">
                    Reward Transaction
                </a>
            </li>
            <li style="margin-bottom: 8px;">
                <a href="#" onclick="showEditRewards()" style="padding: 6px 15px; display: block; background-color: transparent; border-radius: 5px; text-align: center; width: 150px; color: white; border: 1px solid white; transition: background-color 0.3s;" 
                   onmouseover="this.style.backgroundColor='lightgreen'" onmouseout="this.style.backgroundColor='transparent'">
                    Reward Lists
                </a>
            </li>
            <li>
                <a href="#" onclick="showAnalytics()" style="padding: 6px 15px; display: block; background-color: transparent; border-radius: 5px; text-align: center; width: 150px; color: white; border: 1px solid white; transition: background-color 0.3s;" 
                   onmouseover="this.style.backgroundColor='lightgreen'" onmouseout="this.style.backgroundColor='transparent'">
                    Reward Analytics
                </a>
            </li>
        </ul>
    </li>

    <li style="margin-bottom: 15px;">
        <a href="#" onclick="showEditForm()" style="text-decoration: none; color: #000; padding: 12px 20px; display: block; background-color: white; border-radius: 5px; text-align: center;">Users</a>
    </li>
    <li style="margin-bottom: 15px;">
        <a href="#" onclick="adminFunction()" style="text-decoration: none; color: #000; padding: 12px 20px; display: block; background-color: white; border-radius: 5px; text-align: center;">Points Recalibration</a>
    </li>
    <li style="margin-bottom: 15px;">
        <a href="#" onclick="showInventory()" style="text-decoration: none; color: #000; padding: 12px 20px; display: block; background-color: white; border-radius: 5px; text-align: center;">Inventory</a>
    </li>
</ul>




        </div>
    </div>
    
    <div class="content">
        <a href="logout.php" class="logout-link" onclick="confirmLogout(event)">Logout</a> <!-- Updated to call confirmLogout -->
        <div class="grid-container" id="mainContent">
        </div>
        <div class="footer">
            <p>&copy; 2024 TheJuans.</p>
                <p>All Rights Reserved.</p>
        </div>
    </div>
</body>
</html>



    
    
 
    <script>
             function confirmLogout(event) {
        event.preventDefault(); // Prevent the default action of the link
        if (confirm("Are you sure you want to log out?")) {
            window.location.href = "logout.php"; // Redirect to logout page if confirmed
        }
    }
            
     // Function to toggle sub-menu visibility
    function toggleSubMenu(event) {
        event.preventDefault(); // Prevent default link behavior

        // Find the sub-menu within the clicked <li> element
        var parentLi = event.target.parentElement;
        var subMenu = parentLi.querySelector('.sub-menu');

        // Toggle sub-menu visibility
        if (subMenu.style.display === 'block') {
            subMenu.style.display = 'none';
        } else {
            subMenu.style.display = 'block';
        }

        // Close other open sub-menus if needed
        var allMenus = document.querySelectorAll('.sub-menu');
        allMenus.forEach(function(menu) {
            if (menu !== subMenu) {
                menu.style.display = 'none';
            }
        });
    }
            
  function adminFunction() {
    // Display a modal or perform an action for admin settings
    const modalContent = `
 <div class="modal" id="adminModal">
    <div class="modal-content" style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 30px; width: 400px; border-radius: 15px;">
        <h2>Admin Settings</h2>
        <p>Update or recalibrate points for each plastic bottle.</p>
        <form id="adminUpdatePointsForm" style="width: 100%;">
            <label for="pointsToAdd">Points:</label>
            <input type="number" id="pointsToAdd" name="pointsToAdd" min="1" required style="width: 200px; padding: 10px; margin-top: 10px; border-radius: 5px; border: 1px solid #ccc;"> <!-- Shorter width -->
            <div class="button-container" style="display: flex; justify-content: center; gap: 10px; margin-top: 20px;">
                <button type="submit" style="padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 20px; cursor: pointer; transition: background-color 0.3s;" onmouseover="this.style.backgroundColor='#218838'" onmouseout="this.style.backgroundColor='#28a745'">Update</button> 
                <button type="button" onclick="closeAdminModal()" style="padding: 10px 20px; background-color: #dc3545; color: white; border: none; border-radius: 20px; cursor: pointer; transition: background-color 0.3s;" onmouseover="this.style.backgroundColor='#c82333'" onmouseout="this.style.backgroundColor='#dc3545'">Close</button>
            </div>
        </form>
    </div>
</div>


    `;

    // Append modal content to the body
    $('body').append(modalContent);

    // Show the modal with appropriate styles
    $('#adminModal').css({
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
        position: 'fixed',
        top: 0,
        left: 0,
        width: '100%',
        height: '100%',
        backgroundColor: 'rgba(0, 0, 0, 0.7)' // Semi-transparent black background
    });

    // Style modal content
    $('.modal-content').css({
        backgroundColor: 'white', // Background for modal content
        padding: '20px',
        borderRadius: '5px',
        boxShadow: '0 0 10px rgba(0, 0, 0, 0.5)' // Add shadow for better visibility
    });

    // Handle form submission
    $('#adminUpdatePointsForm').off('submit').on('submit', function(event) { // Use .off() to avoid duplicate handlers
        event.preventDefault(); // Prevent the default form submission
        const pointsToAdd = $('#pointsToAdd').val();

        // Send an AJAX request to update points
        $.ajax({
            type: 'POST',
            url: 'update_points.php', // The PHP script to handle the update
            data: { pointsToAdd: pointsToAdd },
            success: function(response) {
                alert(response); // Display the response message
                $('#pointsToAdd').val(''); // Clear the input field
                closeAdminModal(); // Optionally close the modal after successful update
            },
            error: function() {
                alert('An error occurred while updating points.');
            }
        });
    });
}

// Function to close the admin modal
function closeAdminModal() {
    $('#adminModal').remove(); // Remove modal from the DOM
}


            
            
function fetchAnalyticsData() {
    $.ajax({
        url: "fetch_data2.php", // Update with your server-side script
        type: "GET",
        dataType: "json",
        success: function(data) {
            if (data.error) {
                alert("Error: " + data.error);
            } else {
                updateBoxes(data.analytics); // Update the boxes with item names and counts
                createChart(data.chart_labels, data.chart_data); // Create the chart with fetched data
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert("Error fetching analytics data.");
            console.log("Error: " + textStatus, errorThrown);
        }
    });
}

// Update the boxes with item names and counts
function updateBoxes(analyticsData) {
    // Assuming you have boxes for displaying items
    for (let i = 0; i < analyticsData.length; i++) {
        // Ensure you have enough boxes (e.g., 9 boxes)
        if (i < 9) {
            document.getElementById(`box${i + 1}`).innerHTML = `
                <h3>${analyticsData[i].item}</h3>
                <p>${analyticsData[i].count}</p>
            `;
        }
    }
}

// Create the chart
function createChart(labels, data) {
    const ctx = document.getElementById('analyticsChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar', // or 'line', 'pie', etc.
        data: {
            labels: labels,
            datasets: [{
                label: 'Analytics Data',
                data: data,
                backgroundColor: 'rgba(75, 192, 192, 0.5)', // Adjusted for visibility
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Initial fetch
fetchAnalyticsData();

function showSensorReading() {
    var mainContent = document.getElementById("mainContent");
    mainContent.innerHTML = `
        <div id="sensor-data">
            <!-- Sensor data will be inserted here -->
        </div>
    `;

    function fetchSensorData() {
        $.ajax({
            url: "fetch_data2.php", // Ensure this URL is correct for sensor data
            type: "GET",
            dataType: "json",
            success: function(data) {
                if (data.error) {
                    showError("Error: " + data.error);
                } else {
                    displaySensorData(data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                showError("Error fetching sensor data.");
                console.log("Error: " + textStatus, errorThrown);
            }
        });
    }

    function showError(message) {
        $("#sensor-data").html("<p>" + message + "</p>");
    }

    // Initial fetch
    fetchSensorData();

    // Fetch data every 5 seconds
    setInterval(fetchSensorData, 5000);
}

function showAnalytics() {
    var content = `
        <div class="analytics-container">
            <div id="box1" class="analytics-box">
                <h3>Loading...</h3>
                <p>0</p>
            </div>
            <div id="box2" class="analytics-box">
                <h3>Loading...</h3>
                <p>0</p>
            </div>
            <div id="box3" class="analytics-box">
                <h3>Loading...</h3>
                <p>0</p>
            </div>
            <div id="box4" class="analytics-box">
                <h3>Loading...</h3>
                <p>0</p>
            </div>
            <div id="box5" class="analytics-box">
                <h3>Loading...</h3>
                <p>0</p>
            </div>
            <div id="box6" class="analytics-box">
                <h3>Loading...</h3>
                <p>0</p>
            </div>
            <div id="box7" class="analytics-box">
                <h3>Loading...</h3>
                <p>0</p>
            </div>
            <div id="box8" class="analytics-box">
                <h3>Loading...</h3>
                <p>0</p>
            </div>
            <div id="box9" class="analytics-box">
                <h3>Loading...</h3>
                <p>0</p>
            </div>
            <canvas id="analyticsChart" style="width: 100%; height: 400px;"></canvas>
        </div>
    `;
    document.getElementById('mainContent').innerHTML = content;

    // Fetch and display the analytics data
    fetchAnalyticsData();
}

     
let currentPage = 1;
let rowsPerPage = 5; // Number of records per page
let studentData = []; // Store all student data

function showEditForm() {
    var container = document.getElementById("mainContent");

    // Construct the HTML for the table with a search bar and an Add Student button
    var html = `
        <div class='table-container' style='display: flex; flex-direction: column; align-items: center;'>
            <div style='margin-bottom: 10px;'>
                <input type='text' id='searchInput' placeholder='Student ID' style='padding: 8px;' border: 2px solid black; />
               <button 
    onclick='searchStudent()' 
    style='padding: 8px 12px; margin-left: 10px; border: 2px solid darkgreen; border-radius: 20px; background-color: white; color: darkgreen; cursor: pointer;' 
    onmouseover="this.style.backgroundColor='lightgreen';" 
    onmouseout="this.style.backgroundColor='white';"
>
    Search
</button>

                <button onclick='showAddStudentForm()' style='padding: 8px 12px; margin-left: 10px; color:white; background-color: darkgreen;'>+ Add Student</button>
            </div>
            <div>
                <h1 style='text-align: center;'>Registered Accounts</h1>
                <table id='userTable'>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Points</th>
                        <th>Contact Number</th>
                        <th>Delete</th>
                    </tr>
                </table>                
                <div id='pagination' style='margin-top: 10px;'></div>
            </div>
        </div>
    `;

    // Display the HTML content in the main container
    container.innerHTML = html;

    // Fetch and display all records initially
    fetchAllStudents(); // This line is crucial for loading data on initial page load
}


            
function showAddStudentForm() {
    var container = document.getElementById("mainContent");

    var html = `
        <div style='text-align: center; margin-top: 20px;'>
            <h2>Add New Student</h2>
            <form id='addStudentForm' style='display: inline-block; text-align: left; border: 2px solid #888; padding: 40px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); width: 400px;'>
                <div style='margin-bottom: 15px;'>
                    <input type='text' id='newStudentID' placeholder='Student ID' required style='width: 100%; padding: 10px; border: 2px solid #888; border-radius: 4px;'>
                </div>
                <div style='margin-bottom: 15px;'>
                    <input type='text' id='newUsername' placeholder='Full Name' required style='width: 100%; padding: 10px; border: 2px solid #888; border-radius: 4px;'>
                </div>
                <div style='margin-bottom: 15px;'>
                    <input type='email' id='newEmail' placeholder='Email' required style='width: 100%; padding: 10px; border: 2px solid #888; border-radius: 4px;'>
                </div>
                <div style='margin-bottom: 15px;'>
                    <select id='newCourse' required style='width: 100%; padding: 10px; border: 2px solid #888; border-radius: 4px;'>
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
                <div style='margin-bottom: 15px;'>
                    <input type='text' id='newContactNumber' placeholder='Contact Number' required style='width: 100%; padding: 10px; border: 2px solid #888; border-radius: 4px;'>
                </div>
                <button type='button' onclick='addStudent()' style='padding: 10px 20px; background-color: green; color: white; border: none; border-radius: 4px; cursor: pointer; display: block; margin: 0 auto;'>Add Student</button>
            </form>
        </div>
    `;

    container.innerHTML = html;
}



function addStudent() {
    // Collect values from the form fields
    var studentID = document.getElementById('newStudentID').value.trim();
    var username = document.getElementById('newUsername').value.trim();
    var email = document.getElementById('newEmail').value.trim();
    var course = document.getElementById('newCourse').value.trim();
    var contactNumber = document.getElementById('newContactNumber').value.trim();

    // Validation
    if (studentID === "") {
        alert("Please enter a Student ID.");
        return;
    }
    if (username === "") {
        alert("Please enter a Full Name.");
        return;
    }
    if (email === "") {
        alert("Please enter an Email.");
        return;
    }
    if (!validateEmail(email)) {
        alert("Please enter a valid Email address.");
        return;
    }
    if (course === "") {
        alert("Please select a Course.");
        return;
    }
    if (contactNumber === "") {
        alert("Please enter a Contact Number.");
        return;
    }

    // If all validations pass, send the data to the PHP script
    $.ajax({
        url: "add_student.php",
        type: "POST",
        data: {
            studentid: studentID,
            username: username,
            email: email,
            course: course,
            contact_number: contactNumber
        },
        success: function(response) {
            if (response.includes('Student ID already exists')) {
                alert("The Student ID already exists. Please use a different ID.");
            } else if (response.includes('Student added successfully!')) {
                alert("Student added successfully!");
                // Clear input fields after successful addition
                document.getElementById('newStudentID').value = '';
                document.getElementById('newUsername').value = '';
                document.getElementById('newEmail').value = '';
                document.getElementById('newCourse').value = '';
                document.getElementById('newContactNumber').value = '';
                fetchAllStudents();  // Reload the student table after adding
            } else {
                alert(response);  // Alert any other response from the PHP script
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert("Error adding student. Please try again.");
            console.log("Error: " + textStatus, errorThrown);
        }
    });
}

// Function to validate email format
function validateEmail(email) {
    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}





function fetchAllStudents() {
    // Make AJAX request to fetch all user records
    $.ajax({
        url: "fetch_data3.php",
        type: "GET",
        dataType: "json",
        success: function(data) {
            studentData = data; // Store the fetched data
            paginateData(); // Call the paginate function
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error fetching user records:", textStatus, errorThrown);
            document.getElementById("mainContent").innerHTML = "<p>Error fetching user records.</p>";
        }
    });
}

function paginateData() {
    // Calculate the total pages
    const totalPages = Math.ceil(studentData.length / rowsPerPage);
    
    // Display the current page data
    displayPage(currentPage);
    
    // Create pagination controls
    const paginationElement = document.getElementById('pagination');
    paginationElement.innerHTML = '';

    // Previous button
    if (currentPage > 1) {
        paginationElement.innerHTML += `<button onclick='changePage(${currentPage - 1})'>Previous</button>`;
    }

    // Next button
    if (currentPage < totalPages) {
        paginationElement.innerHTML += `<button onclick='changePage(${currentPage + 1})'>Next</button>`;
    }
}

function displayPage(page) {
    const startIndex = (page - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;
    const paginatedData = studentData.slice(startIndex, endIndex);
    populateTable(paginatedData);
}

function changePage(page) {
    currentPage = page;
    paginateData(); // Call paginateData to update the table and pagination controls
}



function searchStudent() {
    var studentId = document.getElementById('searchInput').value.trim();

    if (studentId === '') {
        alert('Please enter a Student ID to search.');
        return;
    }

    // Make AJAX request to fetch a specific user by student ID
    $.ajax({
        url: "fetch_data3.php",
        type: "GET",
        data: { studentid: studentId },
        dataType: "json",
        success: function(data) {
            if (data.error) {
                alert(data.error);
            } else {
                populateTable(data);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error fetching user records:", textStatus, errorThrown);
            document.getElementById("mainContent").innerHTML = "<p>Error fetching user records.</p>";
        }
    });
}


function populateTable(data) {
    var tableBody = '';
    
    // Check if there is data to display
    if (data.length === 0) {
        tableBody += "<tr><td colspan='7'>No records found.</td></tr>";
    } else {
        for (var i = 0; i < data.length; i++) {
            tableBody += "<tr>";
            // Display student ID
            var studentId = data[i].studentid !== undefined ? data[i].studentid : "N/A";
            tableBody += "<td class='studentid'>" + studentId + "</td>";
            // Display username
            tableBody += "<td id='username_" + studentId + "'>" + data[i].username + "</td>";
            // Display email
            var email = data[i].email !== undefined ? data[i].email : "N/A";
            tableBody += "<td id='email_" + studentId + "'>" + email + "</td>";
            // Display course
            var course = data[i].course !== undefined ? data[i].course : "N/A";
            tableBody += "<td id='course_" + studentId + "'>" + course + "</td>";
            // Display points
            tableBody += "<td id='points_" + studentId + "'>" + data[i].points + "</td>";
            // Display contact number
            var contactNumber = data[i].contact_number !== undefined ? data[i].contact_number : "N/A";
            tableBody += "<td id='contact_" + studentId + "'>" + contactNumber + "</td>";
            tableBody += "<td><button style='background-color: red; color: white; border: none; border-radius: 20px; padding: 10px 20px; cursor: pointer;' onclick='deleteUser(" + studentId + ")'>Delete</button></td>";

            tableBody += "</tr>";
        }
    }

    // Append the rows to the table
    document.querySelector("#userTable").innerHTML = `
        <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Course</th>
            <th>Points</th>
            <th>Contact Number</th>
            <th>Delete</th>
        </tr>
    ` + tableBody;
}





function showEditUsernameForm(studentId) {
    // Get the current username from the table cell
    var currentUsername = document.getElementById("username_" + studentId).innerHTML;
    
    // Prompt user to enter new username
    var newUsername = prompt("Enter new username:", currentUsername);
    
    // If user cancels or enters empty username, return
    if (!newUsername) return;
    
    // Call editUser function to update the username
    editUser(studentId, newUsername);
}



function showEditPointsForm(userId) {
    var pointsCell = document.getElementById("points_" + userId);
    var currentPoints = pointsCell.innerHTML;

    var newPoints = prompt("Enter new points", currentPoints);
    if (newPoints !== null && newPoints !== currentPoints) {
        // Make AJAX request to update points in the database
        $.ajax({
            url: "edit_delete_user.php",
            type: "POST",
            data: { action: "edit_points", id: userId, points: newPoints },
            dataType: "json",
            success: function(response) {
                // Handle success
                if (response.success) {
                    // Update points in the UI if database update successful
                    pointsCell.innerHTML = newPoints;
                } else {
                    alert("Error updating points: " + response.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // Handle error
                console.log("Error: " + textStatus, errorThrown);
                alert("Error updating points.");
            }
        });
    }
}

function updatePoints(userId, newPoints, currentPoints, pointsCell) {
    // Make AJAX request to update points in the database
    $.ajax({
        url: "edit_delete_user.php",
        type: "POST",
        data: { id: userId, points: newPoints },
        success: function(response) {
            // Handle success
            console.log("Points updated successfully.");
        },
        error: function(jqXHR, textStatus, errorThrown) {
            // Handle error
            console.error("Error updating points:", textStatus, errorThrown);
            alert("Error updating points.");
            // Revert the points in the UI
            pointsCell.innerHTML = currentPoints;
        }
    });
}

function editUser(userId, newUsername) {
    // Make AJAX request to edit user
    $.ajax({
        url: "edit_delete_user.php",
        type: "POST",
        data: { action: "edit", id: userId, new_username: newUsername },
        dataType: "json",
        success: function(response) {
            // Handle success response
            if (response.success) {
                alert("User edited successfully");
                // Reload the user records after editing
                showEditForm();
            } else {
                alert("Error editing user: " + response.error);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            // Handle error
            console.error("Error editing user:", textStatus, errorThrown);
            alert("Error editing user");
        }
    });
}

function deleteUser(userId) {
    if (confirm("Are you sure you want to delete this user?")) {
        // Make AJAX request to delete user
        $.ajax({
            url: "edit_delete_user.php",
            type: "POST",
            data: { action: "delete", id: userId },
            dataType: "json",
            success: function(response) {
                // Handle success response
                if (response.success) {
                    alert("User deleted successfully");
                    // Reload the user records after deletion
                    showEditForm();
                } else {
                    alert("Error deleting user: " + response.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // Handle error
                console.error("Error deleting user:", textStatus, errorThrown);
                alert("Error deleting user");
            }
        });
    } else {
        alert("Deletion canceled");
    }
}

// Call showEditForm() to display the user records when the page loads
$(document).ready(function() {
    showEditForm();
});

function fetchRewards() {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                var rewards = JSON.parse(xhr.responseText);
                showRewardsList(rewards);
                checkStocksAndNotify(rewards); // Check stocks after fetching
            } else {
                console.error('Failed to fetch rewards: ' + xhr.status);
            }
        }
    };
    xhr.open('GET', 'get_rewards.php', true);
    xhr.send();
}
            
            function showEditRewards() {
    // Fetch rewards
    fetchRewards();
}

function updateStocks(item) {
    var newStocks = document.getElementById("stocks_" + item).value;
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "update_stocks.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                alert(xhr.responseText);
                // Refresh rewards after updating stocks
                fetchRewards();
            } else {
                alert('Error: ' + xhr.status);
            }
        }
    };
    xhr.send("item=" + encodeURIComponent(item) + "&newStocks=" + encodeURIComponent(newStocks));
}



// Function to update a reward
function updateReward(id) {
    var name = document.getElementById("name_" + id).value;
    var pointsRequired = document.getElementById("points_" + id).value;
    var image = document.getElementById("image_" + id).value;
    var stocks = document.getElementById("stocks_" + id).value;

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "update_rewards.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                alert(xhr.responseText);
                fetchRewards(); // Refresh rewards after updating
            } else {
                alert('Error: ' + xhr.status);
            }
        }
    };
    var params = "id=" + id + "&name=" + encodeURIComponent(name) + "&pointsRequired=" + encodeURIComponent(pointsRequired) + "&image=" + encodeURIComponent(image) + "&stocks=" + encodeURIComponent(stocks);
    xhr.send(params);
}



function showLowStockNotification(itemName, stockLevel, notificationId) {
    // Check if a notification for this item already exists
    var existingNotification = document.getElementById(notificationId);
    if (existingNotification) {
        // Update the existing notification's text if the stock level changes
        existingNotification.textContent = `Low stock alert: ${itemName} has only ${stockLevel} left!`;
        return;
    }

    // Create a notification div
    var notification = document.createElement('div');
    notification.id = notificationId;
    notification.style.position = 'fixed';
    notification.style.bottom = '20px';
    notification.style.right = '20px';
    notification.style.backgroundColor = 'red';
    notification.style.color = 'white';
    notification.style.padding = '10px';
    notification.style.borderRadius = '5px';
    notification.style.boxShadow = '0 0 10px rgba(0, 0, 0, 0.1)';
    notification.style.zIndex = '1000';
    notification.style.marginTop = '10px';
    notification.textContent = `Low stock alert: ${itemName} has only ${stockLevel} left!`;

    // Append the notification to the body
    document.body.appendChild(notification);
}

// Function to check stocks and show notifications
function checkStocksAndNotify(rewards) {
    // Loop through the rewards and check the stock levels
    rewards.forEach(function(reward) {
        var notificationId = `notification-${reward.id}`;

        if (reward.stocks < 2) {
            // Show the notification if stock is below 2
            showLowStockNotification(reward.name, reward.stocks, notificationId);
        } else {
            // Remove the notification if the stock is now above 2
            var existingNotification = document.getElementById(notificationId);
            if (existingNotification) {
                existingNotification.remove();
            }
        }
    });
}

// Fetch rewards data from the server and notify on low stock
document.addEventListener('DOMContentLoaded', function() {
    // Fetch rewards from the server immediately when the page loads
    fetchRewards();

    // Optionally, check every 5 seconds and fetch the updated rewards
    setInterval(function() {
        fetch('get_rewards.php')
            .then(response => response.json()) // Parse JSON response
            .then(data => {
                // Call the checkStocksAndNotify function with the fetched rewards data
                checkStocksAndNotify(data);
            })
            .catch(error => {
                console.error('Error fetching rewards:', error);
            });
    }, 5000); // This will fetch updated stock every 5 seconds
});


// Function to refill stocks
function refillStocks(id) {
    var stocksInput = prompt("Enter new stocks for item ID " + id + ":");
    if (stocksInput !== null) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "update_stock.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        // Update stocks in UI
                        document.getElementById("stocks_" + id).value = stocksInput;
                        removeNotification(id); // Remove notification after stocks are refilled
                        alert("Stock updated successfully.");
                    } else {
                        alert("Error: " + response.error);
                    }
                } else {
                    alert('Error: ' + xhr.status);
                }
            }
        };

        var params = "id=" + id + "&stocks=" + encodeURIComponent(stocksInput);
        xhr.send(params);
    }
}




function showRewardsList(rewards) {
    var mainContent = document.getElementById("mainContent");

    // Generate HTML for rewards list
    var html = "<div class='rewards-container' style='display: flex; flex-direction: row; justify-content: center; align-items: center; flex-wrap: wrap; width: 100%; position: relative;'>";

    // Add the "Add Reward" button with a Font Awesome "+" icon at the top left corner
  


    // Add heading for the rewards list
    html += "<h1 class='rewards-heading' style='width: 100%; text-align: center;'>Rewards List</h1>"; // Heading above the rewards list

    rewards.forEach(function(reward) {
        // Card-like layout for each reward
        html += "<div class='reward-item' style='display: flex; flex-direction: column; align-items: center; padding: 20px; border: 1px solid #ddd; width: 200px; border-radius: 10px; margin: 10px;'>";

        // Only show the image if it exists
        if (reward.image) {
            html += "<img src='" + reward.image + "' alt='" + reward.name + "' class='reward-image' style='width: 150px; height: 100px; object-fit: cover; margin-bottom: 10px; border-radius: 8px;'>";
        }

        html += "<div class='reward-details' style='text-align: center;'>";

        // Display reward name
        html += "<p>Name: <input type='text' id='name_" + reward.id + "' value='" + reward.name + "' style='width: 100%; text-align: center;'></p>";

        // Display points required
        html += "<p>Points Required: <input type='number' id='points_" + reward.id + "' value='" + reward.pointsRequired + "' min='0' style='width: 80%;'></p>";

        // Display the image URL input field
        html += "<p>Image URL: <input type='text' id='image_" + reward.id + "' value='" + reward.image + "' style='width: 100%;'></p>";

        // Display stocks
        html += "<p>Stocks: <span id='stocks_" + reward.id + "'>" + reward.stocks + "</span></p>";

        // Add buttons for update and delete
        html += "<button onclick='updateReward(" + reward.id + ")' style='padding: 5px 10px; margin-top: 10px; border: none; background-color: green; color: white; border-radius: 20px; cursor: pointer;'>Update Reward</button>";
		html += "<button onclick='deleteReward(" + reward.id + ")' style='padding: 5px 10px; margin-top: 10px; margin-left: 10px; background-color: red; color: white; border: none; border-radius: 20px; cursor: pointer;'>Delete</button>";



        html += "</div>"; // Close reward-details div
        html += "</div>"; // Close reward-item div
    });

    html += "</div>"; // Close rewards-container div

    // Update the content area
    mainContent.innerHTML = html;

    // Style adjustments
    mainContent.style.display = 'flex';
    mainContent.style.justifyContent = 'center';
    mainContent.style.alignItems = 'center';
    mainContent.style.width = '100%'; // Adjust as necessary
}


  function deleteReward(rewardId) {
    if (confirm("Are you sure you want to delete this reward?")) {
        // Make an AJAX request to delete the reward
        fetch('delete_reward.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id=${rewardId}`
        })
        .then(response => response.text())
        .then(result => {
            if (result === 'success') {
                alert('Reward deleted successfully!');
                fetchRewards(); // Refresh the rewards list after deletion
            } else {
                alert('Error deleting reward: ' + result);
            }
        })
        .catch(error => {
            console.error('Error deleting reward:', error);
        });
    }
}



function showAddRewardForm() {
    var mainContent = document.getElementById("mainContent");

    // Create a form to add a reward (with an image URL input)
    var formHtml = `
       <div class="add-reward-form" style="padding: 20px; background-color: #f9f9f9; border: 2px solid darkgreen; border-radius: 10px; max-width: 500px; margin: 20px auto;">
    <h3 style="text-align: center;">Add New Reward</h3>
    <form id="addRewardForm">
        <div style="text-align: center; margin-bottom: 10px;">
            <label for="newRewardName" style="display: block;">Name:</label>
            <input type="text" id="newRewardName" name="name" style="width: 300px; padding: 8px;" required>
        </div>

        <div style="text-align: center; margin-bottom: 10px;">
            <label for="newRewardPoints" style="display: block;">Points Required:</label>
            <input type="number" id="newRewardPoints" name="pointsRequired" style="width: 300px; padding: 8px;" min="0" required>
        </div>

        <div style="text-align: center; margin-bottom: 10px;">
            <label for="newRewardImageUrl" style="display: block;">Image URL:</label>
            <input type="url" id="newRewardImageUrl" name="imageUrl" style="width: 300px; padding: 8px;" required>
        </div>

        <div style="display: flex; justify-content: center; gap: 10px;">
            <button type="submit" style="padding: 10px 20px; background-color: darkgreen; color: white; border: none; border-radius: 5px;">Add Reward</button>
            <button type="button" onclick="fetchRewards()" style="padding: 10px 20px; background-color: darkred; color: white; border: none; border-radius: 5px;">Cancel</button>
        </div>
    </form>
</div>



    `;

    // Display the form in the main content area
    mainContent.innerHTML = formHtml;

    // Handle form submission
    document.getElementById("addRewardForm").addEventListener("submit", function (event) {
        event.preventDefault(); // Prevent the default form submission behavior

        // Collect form data
        var formData = new FormData(event.target);

        // Send form data to the server using fetch
        fetch('add_reward.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(result => {
            alert('Reward added successfully!');
            fetchRewards(); // Refresh the rewards list after successful addition
        })
        .catch(error => {
            console.error('Error adding reward:', error);
        });
    });
}







       
function showInventory() {
    var container = document.getElementById('mainContent');

    if (!container) {
        console.error('mainContent element not found!');
        return;
    }

    // Clear existing content
    container.innerHTML = '';

    // Create a container to center the content
    var contentWrapper = document.createElement('div');
    contentWrapper.style.display = 'flex';
    contentWrapper.style.flexDirection = 'column';
    contentWrapper.style.alignItems = 'center'; // Center content horizontally
    contentWrapper.style.width = '100%';

    // Create a container for top buttons
    var topButtonContainer = document.createElement('div');
    topButtonContainer.style.textAlign = 'center'; // Center the buttons
    topButtonContainer.style.marginBottom = '10px';

    // Create the "Add Inventory" button
    var addButton = document.createElement('button');
addButton.textContent = '+ Add Record';
addButton.classList.add('btn', 'btn-primary');
addButton.style.padding = '5px 10px'; // Smaller padding
addButton.style.fontSize = '12px'; // Smaller font
addButton.style.marginRight = '10px'; // Add space between buttons
addButton.style.backgroundColor = 'green'; // Blue color for Add button
addButton.style.color = 'white'; // White text color
addButton.style.border = 'none'; // Remove default border
addButton.style.borderRadius = '5px'; // Rounded corners
addButton.style.cursor = 'pointer'; // Pointer cursor on hover
addButton.addEventListener('click', showAddInventoryModal);

// Create the "Search Reward ID" button
var searchButton = document.createElement('button');
searchButton.textContent = 'Search Reward ID';
searchButton.classList.add('btn', 'btn-secondary');
searchButton.style.padding = '5px 10px'; // Smaller padding
searchButton.style.fontSize = '12px'; // Smaller font
searchButton.style.backgroundColor = 'transparent'; // No background color
searchButton.style.color = 'darkgreen'; // Dark green text color
searchButton.style.border = '2px solid darkgreen'; // Dark green border
searchButton.style.borderRadius = '5px'; // Rounded corners
searchButton.style.cursor = 'pointer'; // Pointer cursor on hover

searchButton.addEventListener('click', function () {
    var rewardId = prompt('Enter Reward ID to search:');
    if (rewardId) {
        currentPage = 1;
        fetchInventory(rewardId);
    }
});


    // Append buttons to the top button container
    topButtonContainer.appendChild(addButton);
    topButtonContainer.appendChild(searchButton);

    // Append the button container to the contentWrapper
    contentWrapper.appendChild(topButtonContainer);

    // Create a table to display the inventory data
    var table = document.createElement('table');
    table.setAttribute('id', 'inventoryTable');
    table.style.width = '80%'; // Ensure the table width is set properly
    table.style.borderCollapse = 'collapse';
    table.style.marginTop = '20px';
    table.style.textAlign = 'center'; // Center-align text in the table

    // Create table header row
    var thead = document.createElement('thead');
    var headerRow = document.createElement('tr');
    var headers = ['ID', 'Item Name', 'Reward ID', 'Date Purchased', 'Stocks Added', 'Total Stocks', 'Price'];
    headers.forEach(header => {
        var th = document.createElement('th');
        th.style.border = '1px solid #ddd';
        th.style.padding = '8px';
        th.style.textAlign = 'left';
        th.style.backgroundColor = '#f2f2f2';
        th.style.fontWeight = 'bold';
        th.textContent = header;
        headerRow.appendChild(th);
    });
    thead.appendChild(headerRow);
    table.appendChild(thead);

    // Create table body
    var tbody = document.createElement('tbody');
    table.appendChild(tbody);

    // Append table to the contentWrapper
    contentWrapper.appendChild(table);

    // Create pagination controls and place them below the Add/Search buttons
    var paginationContainer = document.createElement('div');
    paginationContainer.style.textAlign = 'center';
    paginationContainer.style.marginTop = '10px';

    var prevButton = document.createElement('button');
    prevButton.textContent = 'Previous';
    prevButton.classList.add('btn', 'btn-secondary');
    prevButton.style.padding = '5px 10px'; // Smaller padding
    prevButton.style.fontSize = '12px'; // Smaller font
    prevButton.style.marginRight = '10px';
    prevButton.style.backgroundColor = 'green';
    prevButton.style.color = 'white';
    prevButton.disabled = true; // Start with Previous button disabled
    prevButton.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            fetchInventory(currentSearchRewardId);
        }
    });

    var nextButton = document.createElement('button');
    nextButton.textContent = 'Next';
    nextButton.classList.add('btn', 'btn-secondary');
    nextButton.style.padding = '5px 10px'; // Smaller padding
    nextButton.style.fontSize = '12px'; // Smaller font
    nextButton.style.backgroundColor = 'green';
    nextButton.style.color = 'white';
    nextButton.addEventListener('click', () => {
        currentPage++;
        fetchInventory(currentSearchRewardId);
    });

    // Append buttons to pagination container
    paginationContainer.appendChild(prevButton);
    paginationContainer.appendChild(nextButton);

    // Append pagination controls below the buttons (before the table)
    contentWrapper.appendChild(paginationContainer);

    // Append the contentWrapper to the main container
    container.appendChild(contentWrapper);

    // Variables to handle pagination
    var currentPage = 1;
    var rowsPerPage = 10;
    var currentSearchRewardId = null;

    // Function to fetch and display inventory data in the table
    function fetchInventory(searchRewardId) {
        currentSearchRewardId = searchRewardId;
        var url = 'fetch_inventory.php' + (searchRewardId ? '?reward_id=' + encodeURIComponent(searchRewardId) : '');

        fetch(url)
            .then(response => response.json())
            .then(data => {
                // Sort the data by ID in ascending order
                data.sort((a, b) => a.id - b.id);

                var startIndex = (currentPage - 1) * rowsPerPage;
                var endIndex = Math.min(startIndex + rowsPerPage, data.length);

                // Clear previous table rows
                tbody.innerHTML = '';

                // Display each inventory item as a table row
                data.slice(startIndex, endIndex).forEach(item => {
                    var row = document.createElement('tr');
                    var fields = [item.id, item.item_name || 'N/A', item.reward_id, item.datepurchased || 'N/A', item.stocks_added || 'N/A', item.total_stocks, item.price || 'N/A'];
                    fields.forEach(field => {
                        var td = document.createElement('td');
                        td.style.border = '1px solid #ddd';
                        td.style.padding = '8px';
                        td.textContent = field;
                        row.appendChild(td);
                    });
                    tbody.appendChild(row);
                });

                // Update pagination buttons
                updatePaginationButtons(currentPage, data.length, rowsPerPage);
            })
            .catch(error => console.error('Error fetching inventory data:', error));
    }

    // Function to update pagination button states
    function updatePaginationButtons(current, total, perPage) {
        prevButton.disabled = current === 1;
        nextButton.disabled = (current * perPage) >= total;
    }

    // Initial fetch and display
    fetchInventory();
}








function showAddInventoryModal() {
    // Create modal backdrop
    var modalBackdrop = document.createElement('div');
    modalBackdrop.style.position = 'fixed';
    modalBackdrop.style.top = '0';
    modalBackdrop.style.left = '0';
    modalBackdrop.style.width = '100%';
    modalBackdrop.style.height = '100%';
    modalBackdrop.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
    modalBackdrop.style.zIndex = '1000';

    // Create modal window
    var modalWindow = document.createElement('div');
    modalWindow.style.position = 'fixed';
    modalWindow.style.top = '50%';
    modalWindow.style.left = '50%';
    modalWindow.style.transform = 'translate(-50%, -50%)';
    modalWindow.style.backgroundColor = '#fff';
    modalWindow.style.padding = '40px'; // Increased padding for larger modal
    modalWindow.style.borderRadius = '10px'; // Rounded corners
    modalWindow.style.zIndex = '1001';
    modalWindow.style.boxShadow = '0 0 10px rgba(0, 0, 0, 0.3)'; // Add shadow for depth

    // Create the form inside the modal
    var form = document.createElement('form');
    form.setAttribute('action', 'add_inventory.php'); // Endpoint to handle adding inventory
    form.setAttribute('method', 'POST');
    form.style.textAlign = 'left'; // Align content to the left

    // Add the form elements for adding inventory
    var labelReward = document.createElement('label');
    labelReward.textContent = 'Select Reward:';
    form.appendChild(labelReward);

    var selectReward = document.createElement('select');
    selectReward.setAttribute('id', 'reward_id');
    selectReward.setAttribute('name', 'reward_id');
    selectReward.required = true;
    form.appendChild(selectReward);

    // Fetch rewards dynamically from the server
    fetch('get_rewards.php')
        .then(response => response.json())
        .then(rewards => {
            rewards.forEach(reward => {
                var option = document.createElement('option');
                option.value = reward.id;
                option.textContent = reward.name;
                selectReward.appendChild(option);
            });
        })
        .catch(error => console.error('Error fetching rewards:', error));

    form.appendChild(document.createElement('br'));
    form.appendChild(document.createElement('br')); // Extra spacing

    // Add date purchased input
    var labelDate = document.createElement('label');
    labelDate.textContent = 'Date Purchased:';
    form.appendChild(labelDate);

    var inputDate = document.createElement('input');
    inputDate.type = 'date';
    inputDate.name = 'datepurchased';
    inputDate.required = true;
    form.appendChild(inputDate);

    form.appendChild(document.createElement('br'));
    form.appendChild(document.createElement('br')); // Extra spacing

    // Add stocks input
    var labelStocks = document.createElement('label');
    labelStocks.textContent = 'Stocks Added:';
    form.appendChild(labelStocks);

    var inputStocks = document.createElement('input');
    inputStocks.type = 'number';
    inputStocks.name = 'stocks_added';
    inputStocks.required = true;
    form.appendChild(inputStocks);

    form.appendChild(document.createElement('br'));
    form.appendChild(document.createElement('br')); // Extra spacing

    // Add price input
    var labelPrice = document.createElement('label');
    labelPrice.textContent = 'Price:';
    form.appendChild(labelPrice);

    var inputPrice = document.createElement('input');
    inputPrice.type = 'number';
    inputPrice.name = 'price';
    inputPrice.step = '0.01'; // To allow decimal values
    inputPrice.required = true;
    form.appendChild(inputPrice);

    form.appendChild(document.createElement('br'));
    form.appendChild(document.createElement('br')); // Extra spacing

    // Create a div to center-align the buttons
    var buttonContainer = document.createElement('div');
    buttonContainer.style.textAlign = 'center'; // Center-align buttons

    // Add submit button
    var submitButton = document.createElement('button');
    submitButton.type = 'submit';
    submitButton.textContent = 'Add to Inventory';
    submitButton.style.borderRadius = '20px'; // Oval shape
    submitButton.style.backgroundColor = 'green'; // Green background
    submitButton.style.color = 'white'; // White text
    submitButton.style.border = 'none'; // No border
    submitButton.style.padding = '10px 20px'; // Padding
    submitButton.style.cursor = 'pointer'; // Pointer cursor
    buttonContainer.appendChild(submitButton); // Append submit button to container

    // Add close button
    var closeButton = document.createElement('button');
    closeButton.type = 'button'; // Button type to prevent form submission
    closeButton.textContent = 'Close';
    closeButton.style.borderRadius = '20px'; // Oval shape
    closeButton.style.backgroundColor = 'red'; // Red background
    closeButton.style.color = 'white'; // White text
    closeButton.style.border = 'none'; // No border
    closeButton.style.padding = '10px 20px'; // Padding
    closeButton.style.cursor = 'pointer'; // Pointer cursor
    closeButton.style.marginLeft = '10px'; // Space between buttons
    closeButton.onclick = function() {
        modalBackdrop.remove();
        modalWindow.remove();
    };

    buttonContainer.appendChild(closeButton); // Append close button to container
    form.appendChild(buttonContainer); // Append button container to the form

    // Handle form submission
    form.addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent form from submitting the traditional way

        var formData = new FormData(form); // Collect form data

        fetch('add_inventory.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(result => {
            console.log(result);

            // Remove modal elements
            modalBackdrop.remove();
            modalWindow.remove();

            // Show success alert and refresh inventory
            alert('Inventory successfully added!');
            showInventory(); // Refresh inventory table
        })
        .catch(error => {
            console.error('Error adding inventory:', error);
        });
    });

    // Append form to modal window
    modalWindow.appendChild(form);

    // Append modal window and backdrop to the document
    document.body.appendChild(modalBackdrop);
    document.body.appendChild(modalWindow);

    // Close the modal when clicking outside the modal window
    modalBackdrop.addEventListener('click', function() {
        modalBackdrop.remove();
        modalWindow.remove();
    });
}




function showRedemptionHistory() {
    $.ajax({
        url: "redemption.php",
        type: "GET",
        dataType: "json",
        success: function(data) {
            if (data.success === false) {
                showError("Error: " + (data.error || 'Unknown error occurred while fetching history.'));
            } else {
                // Store the data in a global variable for later use
                window.historyData = data.history || [];
                displayRedemptionHistory(window.historyData, 1); // Start on page 1 with full data
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            showError("Error fetching redemption history.");
            console.log("Error: " + textStatus, errorThrown);
        }
    });
}

function displayRedemptionHistory(history, currentPage) {
    const rowsPerPage = 12; // Display 12 rows per page
    const totalPages = Math.ceil(history.length / rowsPerPage);
    const start = (currentPage - 1) * rowsPerPage;
    const end = Math.min(start + rowsPerPage, history.length);

    var mainContent = document.getElementById("mainContent");

    // Ensure the element exists before modifying it
    if (!mainContent) {
        console.error("Error: mainContent element not found.");
        showError("Error displaying redemption history.");
        return;
    }

    // Generate HTML for the table content
    var html = "<div class='history-container'>";
    html += "<h2>Redeem Reward Transactions</h2>";
    html += "<button onclick='promptForStudentId()' style='background-color: darkgreen; padding: 10px 20px; font-size: 16px; border: none; border-radius: 5px; color: white; cursor: pointer;'>Search</button>";


    if (history.length === 0) {
        html += "<p>No redemption history available.</p>";
    } else {
        // Add a new column for the ID
        html += "<table><tr><th>ID</th><th>Student ID</th><th>Item Redeemed</th><th>Points Required</th><th>Timestamp</th><th>Status</th><th>Action</th></tr>";

        // Display rows for the current page
        for (let i = start; i < end; i++) {
            const entry = history[i];
            const statusText = entry.status === "claimed" ? "Claimed" : "Not Claimed";
            const buttonText = entry.status === "claimed" ? "Unclaim" : "Claim";

            html += "<tr>";
            html += "<td>" + entry.id + "</td>"; // Display the id in the first column
            html += "<td>" + entry.studentid + "</td>";
            html += "<td>" + entry.item_redeemed + "</td>";
            html += "<td>" + entry.points_required + "</td>";
            html += "<td>" + entry.datetimestamp + "</td>";
            html += "<td>" + statusText + "</td>";
            html += "<td><button onclick='updateStatus(" + entry.id + ", \"" + entry.status + "\")'>" + buttonText + "</button></td>";
            html += "</tr>";
        }

        html += "</table>";
    }

    // Add pagination controls
    if (totalPages > 1) {
        html += "<div class='pagination-controls'>";
        if (currentPage > 1) {
            html += "<button onclick='displayRedemptionHistory(" + JSON.stringify(history) + ", " + (currentPage - 1) + ")'>Previous</button>";
        }
        html += " Page " + currentPage + " of " + totalPages + " ";
        if (currentPage < totalPages) {
            html += "<button onclick='displayRedemptionHistory(" + JSON.stringify(history) + ", " + (currentPage + 1) + ")'>Next</button>";
        }
        html += "</div>";
    }

    html += "</div>";

    // Set the entire HTML to mainContent
    mainContent.innerHTML = html;
}

function promptForStudentId() {
    // Show an alert box to enter the student ID
    const studentId = prompt("Enter Student ID:");

    // If user cancels or doesn't enter anything, don't proceed
    if (!studentId) {
        alert("No Student ID entered.");
        return;
    }

    // Filter and display history based on the entered Student ID
    filterByStudentId(studentId);
}

function filterByStudentId(studentId) {
    // Ensure that window.historyData is available before filtering
    if (!window.historyData) {
        showError("No history data available for filtering.");
        return;
    }

    // Filter the history data stored in the global variable
    const filteredHistory = window.historyData.filter(entry => {
        // Check if studentid exists and perform the search
        return entry.studentid == studentId;
    });

    // Display filtered data
    displayRedemptionHistory(filteredHistory, 1);
}

function updateStatus(id, currentStatus) {
    const newStatus = currentStatus === "claimed" ? "not claimed" : "claimed";

    $.ajax({
        url: "update_status.php",
        type: "POST",
        dataType: "json",  // Expecting a JSON response
        data: {
            id: id,
            status: newStatus
        },
        success: function(response) {
            console.log("Server response:", response); // Log full response for debugging

            if (response.success) {
                showRedemptionHistory(); // Reload the history after update
            } else {
                showError("Error updating status: " + (response.error || 'Unknown error'));
             }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            // Log the AJAX error
            console.log("AJAX error:", textStatus, errorThrown);
            showError("Error updating status. Please try again.");
        }
    });
}

function showError(message) {
    // Ensure there is an error container to display the message
    var errorContainer = document.getElementById("errorContainer");

    if (!errorContainer) {
        // Create the error container if it doesn't exist
        errorContainer = document.createElement("div");
        errorContainer.id = "errorContainer";
        errorContainer.style.color = "red";
        document.body.appendChild(errorContainer);
    }

    // Display the error message
    errorContainer.innerHTML = message;
    errorContainer.scrollIntoView(); // Optional: Scroll to the error message
}






    </script>




</body>
</html>
