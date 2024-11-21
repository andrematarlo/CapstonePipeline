<?php
// Start session (optional, remove if not needed)
// session_start();

// For preflight requests (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration
$servername = "fdb1029.awardspace.net";
$username = "4528675_accounts";
$password = "matarlo13";
$database = "4528675_accounts";

// Create connection (using mysqli_connect for simplicity)
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// SQL query to count redemptions by month and year and also get item names
$sql = "
    SELECT item_redeemed, DATE_FORMAT(datetimestamp, '%Y-%m') AS month, COUNT(*) AS count
    FROM redemption_history
    GROUP BY item_redeemed, month
    ORDER BY month
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    $error = array("error" => "Error preparing statement");
    echo json_encode($error);
    mysqli_close($conn);
    exit();
}

// Execute the statement
if (!mysqli_stmt_execute($stmt)) {
    $error = array("error" => "Error executing query");
    echo json_encode($error);
    mysqli_close($conn);
    exit();
}

// Get results
$result = mysqli_stmt_get_result($stmt);
$data = [];

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'item' => $row['item_redeemed'],
            'month' => $row['month'],
            'count' => $row['count'],
        ];
    }
}

// Close resources
mysqli_free_result($result);
mysqli_stmt_close($stmt);
mysqli_close($conn);

// Prepare JSON output with data suitable for plotting
$output = [
    'analytics' => $data,
    'chart_labels' => array_map(function ($d) { return $d['month']; }, $data),  // Month labels for the chart
    'chart_data' => array_map(function ($d) { return $d['count']; }, $data),      // Count of redemptions for the chart
];

echo json_encode($output);
?>
