<?php

namespace Domain\PaymentGateway\Support;

use InvalidArgumentException;

class RequestPreparer
{
    public static function prepare(array &$request, ?array $options, &$secret, &$directUrl, &$hostedUrl): void
    {
        if (!$request) {
            throw new InvalidArgumentException('Request must be provided.');
        }

        if (!isset($request['action'])) {
            throw new InvalidArgumentException('Request must contain an \'action\'.');
        }

        if (!isset($request['merchantID']) && \Domain\PaymentGateway\Service\Gateway::$merchantID) {
            $request['merchantID'] = \Domain\PaymentGateway\Service\Gateway::$merchantID;
        }

        if (!isset($request['merchantPwd']) && \Domain\PaymentGateway\Service\Gateway::$merchantPwd) {
            $request['merchantPwd'] = \Domain\PaymentGateway\Service\Gateway::$merchantPwd;
        }

        if (empty($request['merchantID'])) {
            throw new InvalidArgumentException('Merchant ID or Alias must be provided.');
        }

        $secret = $request['merchantSecret'] ?? \Domain\PaymentGateway\Service\Gateway::$merchantSecret;
        unset($request['merchantSecret']);

        $hostedUrl = $request['hostedUrl'] ?? \Domain\PaymentGateway\Service\Gateway::$hostedUrl;
        unset($request['hostedUrl']);

        $directUrl = $request['directUrl'] ?? \Domain\PaymentGateway\Service\Gateway::$directUrl;
        unset($request['directUrl']);

        $request = array_diff_key($request, array_flip([
            'responseCode', 'responseMessage', 'responseStatus',
            'state', 'signature', 'merchantAlias', 'merchantID2'
        ]));
    }
}
