<?php

/**
 * Interface for the PayPal payment service provider
 * @link https://www.paypal.com/ch/cgi-bin/webscr?cmd=_pdn_howto_checkout_outside
 * @link https://www.paypal.com/ipn
 * @author Stefan Heinemannn <stefan.heinemann@comvation.com>
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  module_shop
 */

/**
 * Debug mode
 * @internal
    Debug modes:
    0   No debugging, normal operation
    1   Use PayPal Sandbox, create log files
    2   Use test suite, create log files
*/
define('_PAYPAL_DEBUG', 0);

/**
 * Interface for the PayPal payment service provider
 * @link https://www.paypal.com/ch/cgi-bin/webscr?cmd=_pdn_howto_checkout_outside
 * @link https://www.paypal.com/ipn
 * @author Stefan Heinemannn <stefan.heinemann@comvation.com>
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  module_shop
 */
class PayPal
{
    /**
     * Currency codes accepted by PayPal
     *
     * Mind that both key and value are required by the methods below.
     * @var     array
     */
    private static $arrAcceptedCurrencyCode = array(
        'AUD' => 'AUD', // Australian Dollar
        'CAD' => 'CAD', // Canadian Dollar
        'CHF' => 'CHF', // Swiss Franc
        'CZK' => 'CZK', // Czech Koruna
        'DKK' => 'DKK', // Danish Krone
        'EUR' => 'EUR', // Euro
        'GBP' => 'GBP', // British Pound
        'HKD' => 'HKD', // Hong Kong Dollar
        'HUF' => 'HUF', // Hungarian Forint
        'JPY' => 'JPY', // Japanese Yen
        'NOK' => 'NOK', // Norwegian Krone
        'NZD' => 'NZD', // New Zealand Dollar
        'PLN' => 'PLN', // Polish Zloty
        'SEK' => 'SEK', // Swedish Krona
        'SGD' => 'SGD', // Singapore Dollar
        'THB' => 'THB', // Thai Baht
        'USD' => 'USD', // U.S. Dollar
// 20120601 New supported currencies:
        'ILS' => 'ILS', // Israeli New Shekel
        'MXN' => 'MXN', // Mexican Peso
        'PHP' => 'PHP', // Philippine Peso
        'TWD' => 'TWD', // New Taiwan Dollar
// Note that the following are only supported by accounts
// in the respective countries and must be enabled here:
//        'BRL' => 'BRL', // Brazilian Real (only for Brazilian members)
//        'MYR' => 'MYR', // Malaysian Ringgit (only for Malaysian members)
//        'TRY' => 'TRY', // Turkish Lira (only for Turkish members)
    );


    /**
     * Returns the PayPal form for initializing the payment process
     * @param   string  $account_email  The PayPal account e-mail address
     * @param   string  $order_id       The Order ID
     * @param   string  $currency_code  The Currency code
     * @param   string  $amount         The amount
     * @param   string  $item_name      The description used for the payment
     * @return  string                  The HTML code for the PayPal form
     */
    static function getForm($account_email, $order_id, $currency_code,
        $amount, $item_name)
    {
        global $_ARRAYLANG;

//        require_once ASCMS_MODULE_PATH.'/shop/lib/Currency.class.php';
        $host = ASCMS_PROTOCOL.'://'.$_SERVER['HTTP_HOST'].ASCMS_PATH_OFFSET;
        $return = $host.'/index.php?section=shop'.MODULE_INDEX.
            '&amp;cmd=success&amp;handler=paypal&amp;result=1&amp;order_id='.
            $order_id;
        $cancel_return = $host.'/index.php?section=shop'.MODULE_INDEX.
            '&amp;cmd=success&amp;handler=paypal&amp;result=2&amp;order_id='.
            $order_id;
        $notify_url = $host.'/index.php?section=shop'.MODULE_INDEX.
            '&amp;cmd=success&amp;handler=paypal&amp;result=-1&amp;order_id='.
            $order_id;
        $retval = (_PAYPAL_DEBUG == 0
            ? '<script type="text/javascript">
// <![CDATA[
function go() { document.paypal.submit(); }
window.setTimeout("go()", 3000);
// ]]>
</script>
<form name="paypal" method="post"
      action="https://www.paypal.com/ch/cgi-bin/webscr">
' :
'<form name="paypal" method="post"
      action="https://www.sandbox.paypal.com/ch/cgi-bin/webscr">
').
            Html::getHidden('cmd', '_xclick').
            Html::getHidden('business', $account_email).
            Html::getHidden('item_name', $item_name).
            Html::getHidden('currency_code', $currency_code).
            Html::getHidden('amount', $amount).
            Html::getHidden('custom', $order_id).
            Html::getHidden('notify_url', $notify_url).
            Html::getHidden('return', $return).
            Html::getHidden('cancel_return', $cancel_return).
            $_ARRAYLANG['TXT_PAYPAL_SUBMIT'].'<br /><br />'.
            '<input type="submit" name="submitbutton" value="'.
            $_ARRAYLANG['TXT_PAYPAL_SUBMIT_BUTTON'].
            "\" />\n</form>\n";
        return $retval;
    }


    /**
     * Returns the Order ID taken from either the "custom" or "order_id"
     * parameter value, in that order
     *
     * If none of these parameters is present in the $_REQUEST array,
     * returns false.
     * @return  mixed               The Order ID on success, false otherwise
     */
    static function getOrderId()
    {
        return (isset($_REQUEST['custom'])
          ? intval($_REQUEST['custom'])
          : (isset($_REQUEST['order_id'])
              ? intval($_REQUEST['order_id'])
              : false));
    }


    /**
     * This method is called whenever the IPN from PayPal is received
     *
     * The data from the IPN is verified and answered.  After that,
     * PayPal must reply again with either the "VERIFIED" or "INVALID"
     * keyword.
     * All parameter values are optional.  Any that are non-empty are
     * compared to their respective counterparts received in the post
     * from PayPal.  The verification fails if any comparison fails.
     * You should consider the payment as failed whenever an empty
     * (false or NULL) value is returned.  The latter is intended for
     * diagnostic purposes only, but will never be returned on success.
     * @param   string  $amount         The optional amount
     * @param   string  $currency       The optional currency code
     * @param   string  $order_id       The optional  order ID
     * @param   string  $customer_email The optional customer e-mail address
     * @param   string  $account_email  The optional PayPal account e-mail
     * @return  boolean                 True on successful verification,
     *                                  false on failure, or NULL when
     *                                  an arbitrary result is received.
     */
    static function ipnCheck($amount=NULL, $currency=NULL, $order_id=NULL,
        $customer_email=NULL, $account_email=NULL)
    {
        global $objDatabase;

        self::log("Paypal::ipnCheck(): Entered");
        if ($amount && isset($_POST['mc_gross'])) {
            if ($amount != $_POST['mc_gross'])
                self::log("Paypal::ipnCheck(): Invalid mc_gross {$_POST['mc_gross']}, expected $amount");
            return false;
        }
        if ($currency && isset($_POST['mc_currency'])) {
            if ($currency != $_POST['mc_currency'])
                self::log("Paypal::ipnCheck(): Invalid mc_currency {$_POST['mc_currency']}, expected $currency");
            return false;
        }
        if ($order_id && isset($_POST['custom'])) {
            if ($order_id != $_POST['custom'])
                self::log("Paypal::ipnCheck(): Invalid custom {$_POST['custom']}, expected $order_id");
            return false;
        }
        if ($customer_email && isset ($_POST['payer_email'])) {
            if ($customer_email != $_POST['payer_email'])
                self::log("Paypal::ipnCheck(): Invalid payer_email {$_POST['payer_email']}, expected $customer_email");
            return false;
        }
        if ($account_email && isset($_POST['business'])) {
            if ($account_email != $_POST['business'])
                self::log("Paypal::ipnCheck(): Invalid business {$_POST['business']}, expected $account_email");
            return false;
        }
        if (   empty ($_POST['mc_gross'])
            || empty ($_POST['mc_currency'])
            || empty ($_POST['custom'])
            || empty ($_POST['payer_email'])
            || empty ($_POST['business'])) {
            self::log("Paypal::ipnCheck(): Incomplete IPN parameter values:");
            self::log(var_export($_POST, true));
            return false;
        }
        // Copy the post from PayPal and prepend 'cmd'
        $encoded = 'cmd=_notify-validate';
        foreach($_POST as $name => $value) {
            $encoded .= '&'.urlencode($name).'='.urlencode($value);
        }
        self::log("Paypal::ipnCheck(): Made parameters: $encoded");
// 20120530 cURL version
        $host = (_PAYPAL_DEBUG == 0
            ? 'www.paypal.com'
            : 'www.sandbox.paypal.com');
        $uri = 'https://'.$host.'/cgi-bin/webscr';
        $res = $ch = '';
        if (function_exists('curl_init')) {
            $ch = curl_init();
        }
        if ($ch) {
            curl_setopt($ch, CURLOPT_URL, $uri);
            // Return the received data as a string
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
            $res = curl_exec($ch);
            if (curl_errno($ch)) {
                self::log("Paypal::ipnCheck(): ERROR: cURL: ".curl_errno($ch)." - ".curl_error($ch));
                return false;
            }
            curl_close($ch);
        } else {
            self::log("Paypal::ipnCheck(): WARNING: failed to init cURL, falling back to file");
            $res = file_get_contents("$uri?$encoded");
            if (!$res) {
                self::log("Paypal::ipnCheck(): WARNING: failed to fget(), falling back to socket");
                $res = Socket::getHttp10Response("$uri?$encoded");
            }
            if (!$res) {
                self::log("Paypal::ipnCheck(): ERROR: failed to connect to PayPal");
                return false;
            }
        }
        self::log("Paypal::ipnCheck(): PayPal response: $res");
        if (preg_match('/^VERIFIED/', $res)) {
            self::log("Paypal::ipnCheck(): PayPal IPN verification successful (VERIFIED)");
            return true;
        }
        if (preg_match('/^INVALID/', $res)) {
            // The payment failed.
            self::log("Paypal::ipnCheck(): PayPal IPN verification failed (INVALID)");
            return false;
        }
        self::log("Paypal::ipnCheck(): WARNING: PayPal IPN verification unclear (none of the expected results)");
        return NULL;
    }


    /**
     * Returns the array of currency codes accepted by PayPal
     *
     * Note that both keys and values of the returned array contain the
     * same strings.
     * @return  array           The array of currency codes
     */
    static function getAcceptedCurrencyCodeArray()
    {
        return self::$arrAcceptedCurrencyCode;
    }


    /**
     * Returns true if the given string equals one of the currency codes
     * accepted by PayPal
     * @return  boolean         True if the currency code is accepted,
     *                          false otherwise
     */
    static function isAcceptedCurrencyCode($currency_code)
    {
        return isset (self::$arrAcceptedCurrencyCode[$currency_code]);
    }


    /**
     * Returns HTML code representing select options for choosing one of
     * the currency codes accepted by PayPal
     * @param   string  $selected   The optional preselected currency code
     * @return  string              The HTML select options
     */
    static function getAcceptedCurrencyCodeMenuoptions($selected='')
    {
        return Html::getOptions(self::$arrAcceptedCurrencyCode, $selected);
    }


    /**
     * Logs the message
     *
     * Returns immediately if logging is not enabled in here
     * @param   string  $message    The message to be logged
     */
    static function log($message)
    {
        if (!_PAYPAL_IPN_LOG) return;
        DBG::log($message);
    }

}
