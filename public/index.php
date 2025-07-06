<?php

require_once __DIR__ . '/../vendor/autoload.php';


use Domain\PaymentGateway\Service\Gateway;

$CSGW = new Gateway;
$key = 'S89o46guh1221';

$tran = [
    'merchantID' => '118478',
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

echo $CSGW->hostedRequest($tran);
