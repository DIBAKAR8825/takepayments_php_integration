<?php

require_once __DIR__ . '/../src/Domain/PaymentGateway/Service/Gateway.php';
spl_autoload_register(function ($class) {
    $base = __DIR__ . '/../src/';
    $classPath = $base . str_replace('\\', '/', $class) . '.php';
    if (file_exists($classPath)) {
        require $classPath;
    }
});

use Domain\PaymentGateway\Service\Gateway;

Gateway::$merchantID = '119836';
Gateway::$merchantSecret = '9GXwHNVC87VqsqNM';
Gateway::$debug = true;

$request = [
    'merchantID' => Gateway::$merchantID,
    'merchantSecret' => Gateway::$merchantSecret,
    'action' => 'SALE',
    'type' => 1,
    'countryCode' => 826,
    'currencyCode' => 826,
    'amount' => 1001,
    'orderRef' => 'Test Order',
    'formResponsive' => 'Y',
    'transactionUnique' => uniqid(),
    'redirectURL' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
];

echo Gateway::hostedRequest($request);
