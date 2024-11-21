// Fetch analytics data and update the boxes
function fetchAnalyticsData() {
    $.ajax({
        url: "fetch_data2.php", // Update with your server-side script
        type: "GET",
        dataType: "json",
        success: function (data) {
            if (data.error) {
                alert("Error: " + data.error);
            } else {
                updateBoxes(data); // Update the boxes with the fetched data
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            alert("Error fetching analytics data.");
            console.log("Error: " + textStatus, errorThrown);
        }
    });
}

// Function to update the analytics boxes with data
function updateBoxes(data) {
    if (data.labels.length > 0 && data.data.length > 0) {
        for (let i = 0; i < data.labels.length; i++) {
            document.getElementById(`box${i + 1}`).innerHTML = `<h3>${data.labels[i]}</h3><p>${data.data[i]}</p>`;
        }
    } else {
        alert("Not enough data to display.");
    }
}

// Function to fetch and display sensor data at intervals
function showSensorReading() {
    const mainContent = document.getElementById("mainContent");
    mainContent.innerHTML = `
        <div id="sensor-data">
            <!-- Sensor data will be inserted here -->
        </div>
    `;

    function fetchSensorData() {
        $.ajax({
            url: "fetch_data2.php",
            type: "GET",
            dataType: "json",
            success: function (data) {
                if (data.error) {
                    showError("Error: " + data.error);
                } else {
                    displaySensorData(data);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showError("Error fetching sensor data.");
                console.log("Error: " + textStatus, errorThrown);
            }
        });
    }

    fetchSensorData(); // Initial fetch
    setInterval(fetchSensorData, 5000); // Fetch data every 5 seconds
}

// Show analytics boxes with loading placeholders
function showAnalytics() {
    const content = `
        <div class="analytics-container">
            ${[...Array(9)].map((_, i) => `
                <div id="box${i + 1}" class="analytics-box">
                    <h3>Loading...</h3>
                    <p>0</p>
                </div>
            `).join('')}
        </div>
    `;
    document.getElementById('mainContent').innerHTML = content;
    fetchAnalyticsData(); // Fetch and display the analytics data
}

// Show user edit form and fetch users
function showEditForm() {
    const container = document.getElementById("mainContent");

    container.innerHTML = `
        <div class='table-container' style='display: flex; justify-content: center;'>
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
                        <th>Edit Username</th>
                        <th>Edit Points</th>
                        <th>Delete</th>
                    </tr>
                </table>
            </div>
        </div>
    `;

    $.ajax({
        url: "fetch_data3.php",
        type: "GET",
        dataType: "json",
        success: function (data) {
            let tableBody = '';
            data.forEach((user) => {
                const studentId = user.studentid || "N/A";
                tableBody += `
                    <tr>
                        <td>${studentId}</td>
                        <td id='username_${studentId}'>${user.username}</td>
                        <td>${user.email || "N/A"}</td>
                        <td>${user.course || "N/A"}</td>
                        <td id='points_${studentId}'>${user.points}</td>
                        <td>${user.contact_number || "N/A"}</td>
                        <td><button onclick='showEditUsernameForm(${studentId})'>Edit</button></td>
                        <td><button onclick='showEditPointsForm(${studentId})'>Edit</button></td>
                        <td><button onclick='deleteUser(${studentId})'>Delete</button></td>
                    </tr>
                `;
            });
            document.querySelector("#userTable").innerHTML += tableBody;
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error fetching user records:", textStatus, errorThrown);
            container.innerHTML = "<p>Error fetching user records.</p>";
        }
    });
}

// Show the form to edit the username
function showEditUsernameForm(studentId) {
    const currentUsername = document.getElementById("username_" + studentId).innerHTML;
    const newUsername = prompt("Enter new username:", currentUsername);

    if (newUsername) {
        editUser(studentId, newUsername);
    }
}

// Update user points
function showEditPointsForm(userId) {
    const pointsCell = document.getElementById("points_" + userId);
    const currentPoints = pointsCell.innerHTML;
    const newPoints = prompt("Enter new points", currentPoints);

    if (newPoints && newPoints !== currentPoints) {
        updatePoints(userId, newPoints, pointsCell, currentPoints);
    }
}

// Generic AJAX call to edit a user
function editUser(userId, newUsername) {
    $.ajax({
        url: "edit_delete_user.php",
        type: "POST",
        data: { action: "edit", id: userId, new_username: newUsername },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                alert("User edited successfully");
                showEditForm();
            } else {
                alert("Error editing user: " + response.error);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error editing user:", textStatus, errorThrown);
            alert("Error editing user");
        }
    });
}

// Update user points with error handling
function updatePoints(userId, newPoints, pointsCell, currentPoints) {
    $.ajax({
        url: "edit_delete_user.php",
        type: "POST",
        data: { action: "edit_points", id: userId, points: newPoints },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                pointsCell.innerHTML = newPoints;
            } else {
                alert("Error updating points: " + response.error);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log("Error: " + textStatus, errorThrown);
            alert("Error updating points.");
            pointsCell.innerHTML = currentPoints; // Revert to old value
        }
    });
}

// Delete a user
function deleteUser(userId) {
    if (confirm("Are you sure you want to delete this user?")) {
        $.ajax({
            url: "edit_delete_user.php",
            type: "POST",
            data: { action: "delete", id: userId },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    alert("User deleted successfully");
                    showEditForm();
                } else {
                    alert("Error deleting user: " + response.error);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error("Error deleting user:", textStatus, errorThrown);
                alert("Error deleting user");
            }
        });
    } else {
        alert("Deletion canceled");
    }
}

// Call `showEditForm` when the document is ready
$(document).ready(function () {
    showEditForm();
});
