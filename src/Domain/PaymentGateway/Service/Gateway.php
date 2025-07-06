<?php

namespace Domain\PaymentGateway\Service;

use Domain\PaymentGateway\Support\RequestPreparer;
use Domain\PaymentGateway\Support\ResponseVerifier;
use Domain\PaymentGateway\Support\SignatureHelper;
use Domain\PaymentGateway\Support\HtmlHelper;
use Domain\PaymentGateway\Support\Debugger;
use RuntimeException;
use InvalidArgumentException;

/**
 * Class Gateway
 *
 * Handles integration with the payment gateway for both direct and hosted payment requests.
 * Provides methods to prepare, sign, send, and verify payment requests and responses.
 *
 * @package Domain\PaymentGateway\Service
 *
 * @property static string $hostedUrl   URL for hosted payment form.
 * @property static string $directUrl   URL for direct payment API.
 * @property static ?string $merchantID Merchant identifier.
 * @property static ?string $merchantPwd Merchant password.
 * @property static ?string $merchantSecret Merchant secret key for signing requests.
 * @property static ?string $proxyUrl   Optional proxy URL for outgoing requests.
 * @property static bool $debug         Enable or disable debug logging.
 *
 * @const int RC_SUCCESS                      Response code for successful transaction.
 * @const int RC_DO_NOT_HONOR                 Response code for "Do Not Honor".
 * @const int RC_NO_REASON_TO_DECLINE         Response code for "No Reason To Decline".
 * @const int RC_3DS_AUTHENTICATION_REQUIRED  Response code for 3DS authentication required.
 *
 * @method static array directRequest(array $request, ?array $options = null)
 *         Sends a direct API request to the payment gateway.
 *         Prepares and signs the request, sends it via cURL or HTTP stream, and verifies the response.
 *         Throws RuntimeException on communication or verification failure.
 *         Returns the parsed response array.
 *
 * @method static string hostedRequest(array $request, ?array $options = null)
 *         Prepares a hosted payment form for the gateway.
 *         Signs the request and generates an HTML form for user redirection.
 *         Returns the HTML form as a string.
 */

class Gateway
{
    public static string $hostedUrl = 'https://gw1.tponlinepayments.com/paymentform/';
    public static string $directUrl = 'https://gw1.tponlinepayments.com/direct/';
    public static ?string $merchantID;
    public static ?string $merchantPwd = null;
    public static ?string $merchantSecret;
    public static ?string $proxyUrl = null;
    public static bool $debug = true;

    const RC_SUCCESS = 0;
    const RC_DO_NOT_HONOR = 5;
    const RC_NO_REASON_TO_DECLINE = 85;
    const RC_3DS_AUTHENTICATION_REQUIRED = 0x1010A;

static public function directRequest(array $request, ?array $options = null)
    {
        Debugger::debug(__METHOD__ . '() - args=', func_get_args());

        RequestPreparer::prepare($request, $options, $secret, $directUrl, $hostedUrl);

        if ($secret) {
            $request['signature'] = SignatureHelper::sign($request, $secret);
        }

        if (function_exists('curl_init')) {
            $opts = [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($request, '', '&'),
                CURLOPT_HEADER => false,
                CURLOPT_FAILONERROR => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
                CURLOPT_PROXY => static::$proxyUrl,
            ];

            $ch = curl_init($directUrl);
            if ($ch === false) {
                throw new RuntimeException('Failed to initialise communications with Payment Gateway');
            }

            if (curl_setopt_array($ch, $opts) === false || ($data = curl_exec($ch)) === false) {
                $err = curl_error($ch);
                curl_close($ch);
                throw new RuntimeException('Failed to communicate with Payment Gateway: ' . $err);
            }

        } elseif (ini_get('allow_url_fopen')) {
            $opts = [
                'http' => [
                    'method' => 'POST',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                    'proxy' => static::$proxyUrl,
                    'header' => 'Content-Type: application/x-www-form-urlencoded',
                    'content' => http_build_query($request, '', '&'),
                    'timeout' => 5,
                ]
            ];

            $context = stream_context_create($opts);
            if (($data = file_get_contents($directUrl, false, $context)) === false) {
                throw new RuntimeException('Failed to send request to Payment Gateway');
            }

        } else {
            throw new RuntimeException('No means of communicate with Payment Gateway, please enable CURL or HTTP Stream Wrappers');
        }

        if (!$data) {
            throw new RuntimeException('No response from Payment Gateway');
        }

        $response = null;
        parse_str($data, $response);

        ResponseVerifier::verify($response, $secret);

        Debugger::debug(__METHOD__ . '() - ret=', $response);
        return $response;
    }

static public function hostedRequest(array $request, ?array $options = null)
{
    Debugger::debug(__METHOD__ . '() - args=', func_get_args());

    RequestPreparer::prepare($request, $options, $secret, $directUrl, $hostedUrl);

    if (!isset($request['redirectURL'])) {
        $request['redirectURL'] = (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    if ($secret) {
        $request['signature'] = SignatureHelper::sign($request, $secret, true);
    }

    // Start form with Bootstrap styling
    $ret = '<form method="post" class="p-4 border rounded" ' .
        (isset($options['formAttrs']) ? $options['formAttrs'] : '') .
        ' action="' . htmlentities($hostedUrl, ENT_COMPAT, 'UTF-8') . "\">\n";

    foreach ($request as $name => $value) {
        if ($name === 'signature') {
            $ret .= '<input type="hidden" name="' . htmlentities($name, ENT_QUOTES, 'UTF-8') . '" value="' . htmlentities($value, ENT_QUOTES, 'UTF-8') . "\">\n";
        } else {
            $ret .= '<div class="mb-3">
                        <label class="form-label">' . htmlentities($name, ENT_QUOTES, 'UTF-8') . '</label>
                        <input type="text" class="form-control" name="' . htmlentities($name, ENT_QUOTES, 'UTF-8') . '" value="' . htmlentities($value, ENT_QUOTES, 'UTF-8') . '">
                    </div>' . "\n";
        }
    }

    if (isset($options['submitImage'])) {
        $ret .= '<input ' . (isset($options['submitAttrs']) ? $options['submitAttrs'] : '') .
            ' type="image" src="' . htmlentities($options['submitImage'], ENT_COMPAT, 'UTF-8') . "\">\n";
    } elseif (isset($options['submitHtml'])) {
        $ret .= '<button type="submit" class="btn btn-primary w-100" ' . (isset($options['submitAttrs']) ? $options['submitAttrs'] : '') .
            ">{$options['submitHtml']}</button>\n";
    } else {
        $ret .= '<button type="submit" class="btn btn-primary w-100" ' . (isset($options['submitAttrs']) ? $options['submitAttrs'] : '') .
            '>' . (isset($options['submitText']) ? htmlentities($options['submitText'], ENT_COMPAT, 'UTF-8') : 'Pay Now') . "</button>\n";
    }

    $ret .= "</form>\n";

    //Debugger::debug(__METHOD__ . '() - ret=', $ret);

    return $ret;
}

}
