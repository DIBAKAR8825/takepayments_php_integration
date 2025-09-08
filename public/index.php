<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Domain\PaymentGateway\Service\Gateway;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Set Gateway credentials from .env
Gateway::$merchantID = $_ENV['MERCHANT_ID'];
Gateway::$merchantSecret = $_ENV['MERCHANT_SECRET'];
Gateway::$debug = filter_var($_ENV['DEBUG'], FILTER_VALIDATE_BOOLEAN);

// Load payment data from JSON file
$responseFile = __DIR__ . '/response.json';
if (!file_exists($responseFile)) {
    die('Error: response.json file not found');
}

$responseData = json_decode(file_get_contents($responseFile), true);
if (!isset($responseData['data'])) {
    die('Error: Invalid response.json structure');
}

$paymentData = $responseData['data'];

// Prepare transaction using data from JSON
$tran = [
    'merchantID' => Gateway::$merchantID,
    'merchantSecret' => Gateway::$merchantSecret,
    'action' => 'SALE',
    'type' => 1,
    'countryCode' => $paymentData['countryCode'],
    'currencyCode' => $paymentData['currencyCode'],
    'amount' => $paymentData['amount'],
    'customerEmail' => $paymentData['customerEmail'],
    'customerAddress' => $paymentData['customerAddress'],
    'customerPostCode' => $paymentData['customerPostCode'],
    'customerPhone' => $paymentData['customerPhone'],
    'orderRef' => $paymentData['orderRef'],
    'formResponsive' => 'Y',
    'transactionUnique' => $paymentData['transactionUnique'],
    'redirectURL' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
];

// Output the payment form
echo Gateway::hostedRequest($tran);
