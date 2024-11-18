<?php
require_once __DIR__ . '/vendor/autoload.php';

use ClickSend\Api\SMSApi;
use ClickSend\Configuration;
use ClickSend\Model\SmsMessage;
use ClickSend\Model\SmsMessageCollection;
use Dotenv\Dotenv;

// Load .env file for environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// ClickSend credentials
$clicksend_username = $_ENV['CLICKSEND_USERNAME'];
$clicksend_api_key = $_ENV['CLICKSEND_API_KEY'];

// Function to send SMS
function sendSMS($recipient, $messageBody)
{
    global $clicksend_username, $clicksend_api_key;

    // Configure ClickSend API
    $config = Configuration::getDefaultConfiguration()
        ->setUsername($clicksend_username)
        ->setPassword($clicksend_api_key);
    $smsApi = new SMSApi(null, $config);

    // Create SMS message
    $smsMessage = new SmsMessage([
        'to' => $recipient,
        'body' => $messageBody
    ]);
    $smsCollection = new SmsMessageCollection([
        'messages' => [$smsMessage]
    ]);

    try {
        // Send SMS
        $result = $smsApi->smsSendPost($smsCollection);

        // Return success response
        return [
            'status' => 'Success',
            'response' => $result
        ];
    } catch (Exception $e) {
        // Return error response
        return [
            'status' => 'Failed',
            'error' => $e->getMessage()
        ];
    }
}

// Handle API request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode the raw POST data
    $data = json_decode(file_get_contents('php://input'), true);

    // Retrieve and validate the phone number and message from decoded data
    $recipient = $data['phone_number'] ?? '';
    $messageBody = $data['message'] ?? '';

    if (empty($recipient) || empty($messageBody)) {
        http_response_code(400);
        echo json_encode(['error' => 'Phone number and message are required.']);
        exit;
    }

    // Send SMS and get response
    $response = sendSMS($recipient, $messageBody);

    // Return response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
