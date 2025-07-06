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

// Prepare transaction
$key = Gateway::$merchantSecret;

$tran = [
    'merchantID' => Gateway::$merchantID,
    'merchantSecret' => $key,
    'action' => 'SALE',
    'type' => 1,
    'countryCode' => 826,
    'currencyCode' => 826,
    'amount' => 1001,
    'orderRef' => 'Test purchase',
    'formResponsive' => 'Y',
    'transactionUnique' => uniqid(),
    'redirectURL' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
];

echo Gateway::hostedRequest($tran);
