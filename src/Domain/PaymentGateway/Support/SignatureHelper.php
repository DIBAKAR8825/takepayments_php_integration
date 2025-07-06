<?php

namespace Domain\PaymentGateway\Support;

class SignatureHelper
{
    public static function sign(array $data, string $secret, $partial = false): string
    {
        if ($partial) {
            if (is_string($partial)) {
                $partial = explode(',', $partial);
            }
            if (is_array($partial)) {
                $data = array_intersect_key($data, array_flip($partial));
            }
            $partial = join(',', array_keys($data));
        }

        ksort($data);
        $ret = http_build_query($data, '', '&');
        $ret = preg_replace('/%0D%0A|%0A%0D|%0D/i', '%0A', $ret);
        $ret = hash('SHA512', $ret . $secret);

        if ($partial) {
            $ret .= '|' . $partial;
        }

        return $ret;
    }
}
