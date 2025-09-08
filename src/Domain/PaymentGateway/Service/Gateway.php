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

static public function hostedRequest(array $request, ?array $options = null)
{
    // Prepare the request and signature
    RequestPreparer::prepare($request, $options, $secret, $directUrl, $hostedUrl);
    
    // Set the redirect URL if not provided
    $request['redirectURL'] = $request['redirectURL'] ?? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
    // Sign the request if a secret exists
    if ($secret) {
        $request['signature'] = SignatureHelper::sign($request, $secret, true);
        echo 'Signature: ' . $request['signature'] . "\n";
    }
    
    // Start building the form
    $formAttributes = $options['formAttrs'] ?? '';
    $formAction = htmlentities($hostedUrl, ENT_COMPAT, 'UTF-8');
    $form = "<form method=\"post\" class=\"p-4 border rounded\" $formAttributes action=\"$formAction\">\n";
    
    // Add the form fields (hidden inputs for signature, and text inputs for others)
    foreach ($request as $name => $value) {
        $value = htmlentities($value, ENT_QUOTES, 'UTF-8');
        if ($name === 'signature') {
            $form .= "<input type=\"hidden\" name=\"$name\" value=\"$value\">\n";
        } else {
            $form .= "<div class=\"mb-3\">
                        <label class=\"form-label\">$name</label>
                        <input type=\"text\" class=\"form-control\" name=\"$name\" value=\"$value\">
                      </div>\n";
        }
    }
    
    // Add submit button or image
    $submitButton = self::generateSubmitButton($options);

    // Complete the form
    $form .= $submitButton . "</form>\n";

    return $form;
}

private static function generateSubmitButton(?array $options)
{
    // Check for a custom submit image
    if (isset($options['submitImage'])) {
        $submitAttrs = $options['submitAttrs'] ?? '';
        $submitImage = htmlentities($options['submitImage'], ENT_COMPAT, 'UTF-8');
        return "<input $submitAttrs type=\"image\" src=\"$submitImage\">\n";
    }

    // Check for custom submit HTML
    if (isset($options['submitHtml'])) {
        $submitAttrs = $options['submitAttrs'] ?? '';
        return "<button type=\"submit\" class=\"btn btn-primary w-100\" $submitAttrs>{$options['submitHtml']}</button>\n";
    }

    // Default submit button
    $submitText = $options['submitText'] ?? 'Pay Now';
    return "<button type=\"submit\" class=\"btn btn-primary w-100\">$submitText</button>\n";
}


}
