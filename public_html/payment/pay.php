<?php
session_start();

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
$dynamicCallback = $protocol . $domainName . '/payment/confirm.php';

$post = [
    'type' => "deposit",
    'api' => $_ENV['PAYMENT_API_KEY'] ?? '5d6af34cd11f453aa837766355d07b25',
    'merchant' => $_ENV['PAYMENT_MERCHANT_ID'] ?? '888100868',
    'order' => 'MONUxx'.$_GET['user'].'xx'.time(),
    'callback' => $dynamicCallback,
    'pay_type' => '102',
    'amount' => $_GET['am'],
];

// If MOCK_PAYMENT is set to 'true' or no API key is specified, redirect to Sandbox payment simulator
if (!isset($_ENV['PAYMENT_API_KEY']) || ($_ENV['MOCK_PAYMENT'] ?? 'true') === 'true') {
    $user = $_GET['user'] ?? '';
    $am = $_GET['am'] ?? '0';
    $order = $post['order'];
    header("Location: mock_pay.php?user=" . urlencode($user) . "&am=" . urlencode($am) . "&order=" . urlencode($order));
    exit;
}

function post($url, $data)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);

    if ($response === false) {
        echo 'Curl error: ' . curl_error($curl);
        // Handle the error, possibly log it
        die();
    }

    curl_close($curl);

    return $response;
}

$response = post("https://primewin.live/wowpay.php", $post);

// Debugging statements
// echo "Raw Response: <pre>" . htmlspecialchars($response) . "</pre>";

try {
    $jsonResponse = json_decode($response, true);

    // Check if the response is valid JSON
    if ($jsonResponse === null && json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Unable to decode the API response as JSON.");
    }

    // Extract information from the response
    $status = isset($jsonResponse['status']) ? $jsonResponse['status'] : '';
    $payUrl = isset($jsonResponse['payUrl']) ? $jsonResponse['payUrl'] : '';

    // Process the response
    if ($status == 'SUCCESS' && !empty($payUrl)) {
        // Redirect only if payUrl is available
        header("Location: $payUrl");
        exit;
    } else {
        echo "Error: $status - $payUrl";
        if ($status == 'FAIL' && isset($jsonResponse['error'])) {
            echo "<br>Error Message: " . $jsonResponse['error'];
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
