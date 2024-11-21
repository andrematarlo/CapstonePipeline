<?php
$ch = curl_init('https://d9mpyv.api.infobip.com');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification temporarily
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    echo "Failed to connect: " . curl_error($ch);
} else {
    echo "Connection successful. HTTP code: $http_code";
}
?>
