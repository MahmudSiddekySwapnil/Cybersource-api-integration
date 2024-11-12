
<?php

// Required information from the CyberSource Business Center
$merchant_id = 'merchant-id';
$key_id = 'key-id';
$secret_key = 'sec-key';
$host = 'apitest.cybersource.com';  // Use api.cybersource.com for production
$request_target = 'post /risk/v1/authentication-setups';

// The request body (JSON-encoded)
$message_body = json_encode([
    "paymentInformation" => [
        "card" => [
            "expirationYear" => "2025",
            "number" => "4456530000001096",
            "securityCode" => "123",
            "expirationMonth" => "11"
        ]
    ]
]);

// Generate the date in GMT format
$date = gmdate('D, d M Y H:i:s T');

// Generate the SHA-256 digest for the message body
$digest = 'SHA-256=' . base64_encode(hash('sha256', $message_body, true));

// Create the signature string
$signature_string = "host: $host\ndate: $date\n(request-target): $request_target\ndigest: $digest\nv-c-merchant-id: $merchant_id";

// Generate the signature using HMAC-SHA256
$decoded_secret_key = base64_decode($secret_key);
$signature = base64_encode(hash_hmac('sha256', $signature_string, $decoded_secret_key, true));


// Build the authorization header
$auth_header = sprintf(
    'keyid="%s", algorithm="HmacSHA256", headers="host date (request-target) digest v-c-merchant-id", signature="%s"',
    $key_id,
    $signature
);

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://$host/risk/v1/authentication-setups");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "v-c-merchant-id: $merchant_id",
    "Date: $date",
    "Host: $host",
    "Digest: $digest",
    "Signature: $auth_header"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $message_body);

// Execute the request and get the response
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Output the response for debugging
echo "HTTP Status Code: $http_code\n";
echo "Response: $response\n";
?>
