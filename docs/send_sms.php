<?php
// Infobip credentials
$apiKey = 'd65794dea570191ea69a1c556c66aaf6-4ff6d939-5680-40f2-838c-0e81ebb65406';
$baseUrl = 'http://d9mpyv.api.infobip.com/sms/2/text/advanced';

// Function to send SMS via Infobip
function sendSMS($contactNumber, $message) {
    global $apiKey, $baseUrl;

    $data = [
        "messages" => [
            [
                "from" => "TrashsureBin",
                "destinations" => [
                    ["to" => $contactNumber]
                ],
                "text" => $message
            ]
        ]
    ];

    // Initialize cURL session
    $ch = curl_init($baseUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: App $apiKey",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // Disable SSL verification (temporary)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // Execute the cURL request
    $response = curl_exec($ch);

    if ($response === false) {
        return ['error' => 'Error sending SMS: ' . curl_error($ch)];
    }

    // Get the HTTP response code
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check for a successful response
    if ($httpCode == 200) {
        return ['success' => 'SMS sent successfully'];
    } else {
        return ['error' => 'Failed to send SMS. HTTP Code: ' . $httpCode];
    }
}

// Handling the POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactNumber = $_POST['contact_number'];
    $message = $_POST['message'];

    if (empty($contactNumber) || empty($message)) {
        echo json_encode(['error' => 'Contact number and message are required']);
        exit;
    }

    $result = sendSMS($contactNumber, $message);
    echo json_encode($result);
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
