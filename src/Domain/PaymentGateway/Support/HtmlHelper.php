<?php

namespace Domain\PaymentGateway\Support;

class HtmlHelper
{
    public static function fieldToHtml($name, $value): string
    {
        $ret = '';
        if (is_array($value)) {
            foreach ($value as $n => $v) {
                $ret .= self::fieldToHtml("{$name}[{$n}]", $v);
            }
        } elseif ($value !== '') {
            $value = preg_replace_callback('/[\x00-\x1f]/', fn($m) => '&#' . ord($m[0]) . ';', htmlentities($value, ENT_COMPAT, 'UTF-8', true));
            $ret = "<input type=\"hidden\" name=\"{$name}\" value=\"{$value}\" />\n";
        }
        return $ret;
    }
}
