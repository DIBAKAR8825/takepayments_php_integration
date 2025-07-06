<?php

namespace Domain\PaymentGateway\Support;

use Domain\PaymentGateway\Service\Gateway;

class Debugger
{
    public static function debug(...$args): void
    {
        if (Gateway::$debug) {
            $msg = '';
            foreach ($args as $arg) {
                $msg .= (is_string($arg) ? $arg : var_export($arg, true)) . ' ';
            }
            error_log($msg);
        }
    }
}
