<?php

namespace Domain\PaymentGateway\Support;

use RuntimeException;
use Domain\PaymentGateway\Service\Gateway;

class ResponseVerifier
{
    public static function verify(array &$response, $secret = null): bool
    {
        if (!$response || !isset($response['responseCode'])) {
            throw new RuntimeException('Invalid response from Payment Gateway');
        }

        $secret = $secret ?? Gateway::$merchantSecret;

        $fields = null;
        $signature = null;

        if (isset($response['signature'])) {
            $signature = $response['signature'];
            unset($response['signature']);
            if ($secret && $signature && strpos($signature, '|') !== false) {
                list($signature, $fields) = explode('|', $signature);
            }
        }

        if (!$secret && $signature) {
            throw new RuntimeException('Incorrectly signed response from Payment Gateway (1)');
        } elseif ($secret && !$signature) {
            throw new RuntimeException('Incorrectly signed response from Payment Gateway (2)');
        } elseif ($secret && SignatureHelper::sign($response, $secret, $fields) !== $signature) {
            throw new RuntimeException('Incorrectly signed response from Payment Gateway');
        }

        settype($response['responseCode'], 'integer');
        return true;
    }
}
