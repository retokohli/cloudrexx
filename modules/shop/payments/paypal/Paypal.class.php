<?php

/**
 * Interface for the PayPal form
 *
 * It requires a html form to send the date to
 * PayPal. This class generates it.
 *
 * @link https://www.paypal.com/ch/cgi-bin/webscr?cmd=_pdn_howto_checkout_outside
 * @link https://www.paypal.com/ipn
 * @author Stefan Heinemannn <stefan.heinemann@comvation.com>
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
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
 * Interface for the PayPal form
 *
 * It requires a html form to send the date to
 * PayPal. This class generates it.
 *
 * @link https://www.paypal.com/ch/cgi-bin/webscr?cmd=_pdn_howto_checkout_outside
 * @link https://www.paypal.com/ipn
 * @author Stefan Heinemannn <stefan.heinemann@comvation.com>
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */
class PayPal
{
    private static $arrAcceptedCurrencyCode = array(
        'AUD', // Australian Dollar
        'CAD', // Canadian Dollar
        'CHF', // Swiss Franc
        'CZK', // Czech Koruna
        'DKK', // Danish Krone
        'EUR', // Euro
        'GBP', // Pound Sterling
        'HKD', // Hong Kong Dollar
        'HUF', // Hungarian Forint
        'JPY', // Japanese Yen
        'NOK', // Norwegian Krone
        'NZD', // New Zealand Dollar
        'PLN', // Polish Zloty
        'SEK', // Swedish Krona
        'SGD', // Singapore Dollar
        'THB', // Thai Baht
        'USD', // U.S. Dollar
    );

    /**
     * PHP 5 constructor
     */
    function __construct()
    {
    }


    /**
     * Returns the form for PayPal accessing
     *
     * @return string HTML-Code for the PayPal form
     */
    static function getForm()
    {
        global $_ARRAYLANG;

        require_once ASCMS_MODULE_PATH.'/shop/lib/Currency.class.php';
        $orderid = $_SESSION['shop']['orderid'];
        $business = self::getBusiness();
        $item_name = $_ARRAYLANG['TXT_SHOP_PAYPAL_ITEM_NAME'];
        $currency_code = Currency::getCodeById($_SESSION['shop']['currencyId']);
        $amount = $_SESSION['shop']['grand_total_price'];

        $host = ASCMS_PROTOCOL.'://'.$_SERVER['HTTP_HOST'].ASCMS_PATH_OFFSET;
        $return = $host.'/index.php?section=shop'.MODULE_INDEX.'&amp;cmd=success&amp;handler=paypal&amp;result=1&amp;orderid='.$orderid;
        $cancel_return = $host.'/index.php?section=shop'.MODULE_INDEX.'&amp;cmd=success&amp;handler=paypal&amp;result=2&amp;orderid='.$orderid;
        $notify_url = $host.'/index.php?section=shop'.MODULE_INDEX.'&amp;act=paypalIpnCheck';

        $retval = "<script language='JavaScript' type='text/javascript'>
            // <![CDATA[
                function go()
                {
                    document.paypal.submit();
                }
                ".(_PAYPAL_DEBUG > 0 ? '' : "window.setTimeout('go()',3000);
            ")."// ]]>
            </script>";

        $retval .=
            (_PAYPAL_DEBUG == 0
              ? "\n<form name='paypal' action='https://www.paypal.com/ch/cgi-bin/webscr' method='post'>\n"
              : (_PAYPAL_DEBUG == 1
                  ? "\n<form name='paypal' action='https://www.sandbox.paypal.com/ch/cgi-bin/webscr' method='post'>\n"
                  // _PAYPAL_DEBUG == 2 or higher
                  : "\n<form name='paypal' action='$host/index.php?section=shop".MODULE_INDEX."&amp;act=testIpn' method='post'>\n"
            ));
        $retval .= self::getInput('cmd', '_xclick');
        $retval .= self::getInput('business', $business);
        $retval .= self::getInput('item_name', $item_name);
        $retval .= self::getInput('currency_code', $currency_code);
        $retval .= self::getInput('amount', $amount);
        $retval .= self::getInput('custom', $orderid);
        $retval .= self::getInput('notify_url', $notify_url);
        $retval .= self::getInput('return', $return);
        $retval .= self::getInput('cancel_return', $cancel_return);
        $retval .= "{$_ARRAYLANG['TXT_PAYPAL_SUBMIT']}<br /><br />";
        $retval .= "<input type='submit' name='submitbutton' value=\"{$_ARRAYLANG['TXT_PAYPAL_SUBMIT_BUTTON']}\" />\n";
        $retval .= "</form>\n";
        return $retval;
    }

    /**
     * Generates a hidden input field
     * @param $field Array containing the name and the value of the field
     */
    static function getInput($name, $value)
    {
        return
            '<input type="hidden" name="'.$name.'" value="'.$value.'" />'.
            "\n";
    }


    /**
     * Reads the paypal email address from the database
     */
    static function getBusiness()
    {
        return SettingDb::getValue('paypal_account_email');
    }


    /**
     * This method is called whenever the IPN from PayPal is received
     *
     * The data from the IPN is verified and answered.  After that,
     * PayPal must reply again with either the "VERIFIED" or "INVALID"
     * keyword.  According to that reply, the status of the order
     * concerned is updated.  See {@see Shop::updateOrderStatus}.
     */
    static function ipnCheck()
    {
        global $objDatabase;

if (_PAYPAL_DEBUG) $log = @fopen(ASCMS_DOCUMENT_ROOT.'/ipnCheckLog.txt', 'w');

        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';
        foreach ($_POST as $key => $value) {
            $value = urlencode($value);
            $req .= "&$key=$value";
        }
//if (_PAYPAL_DEBUG) @fwrite($log, "Made parameters: $req\r\n");

        $errno = '';
        $errstr = '';
        $fp =
            (_PAYPAL_DEBUG == 0
                ? fsockopen('www.paypal.com', 80, $errno, $errstr, 30)
                : (_PAYPAL_DEBUG == 1
                    ? fsockopen('www.sandbox.paypal.com', 80, $errno, $errstr, 30)
                    // _PAYPAL_DEBUG == 2 or higher
                    : fsockopen('localhost', 80, $errno, $errstr, 30)
        ));
        if (!$fp) {
if (_PAYPAL_DEBUG) @fwrite($log, "no fp, errno $errno, error $errstr\r\nexiting\r\n");@fclose($log);
            exit;
        }

        // post back to PayPal system to validate
        $header  =
            (_PAYPAL_DEBUG < 2
                ? "POST /cgi-bin/webscr HTTP/1.0\r\n"
                : "POST ".ASCMS_PATH_OFFSET."/index.php?section=shop".MODULE_INDEX."&act=testIpnValidate HTTP/1.0\r\n"
            ).
            "Content-Type: application/x-www-form-urlencoded\r\n".
            "Content-Length: ".strlen($req)."\r\n\r\n";
        fwrite($fp, $header.$req);
if (_PAYPAL_DEBUG) @fwrite($log, "sent\r\n$header$req\r\n\r\n");

        // assign posted variables to local variables
// The following are unused
//        $item_name = $_POST['item_name'];
//        $item_number = $_POST['item_number'];
//        $payment_status = $_POST['payment_status'];
//        $txn_id = $_POST['txn_id'];
//        $payer_email = $_POST['payer_email'];
        $payment_amount = $_POST['mc_gross'];
        $payment_currency = $_POST['mc_currency'];
        $receiver_email = $_POST['receiver_email'];
        $orderid = $_POST['custom'];

        $query = "SELECT `value` FROM ".DBPREFIX."module_shop".MODULE_INDEX."_config
                  WHERE `name`='paypal_account_email'";
        $objResult = $objDatabase->Execute($query);
if (_PAYPAL_DEBUG) @fwrite($log, "query $query\r\nresult ".($objResult ? 'true' : 'false')."\r\n"); if (!$objResult) { @fwrite($log, "Query failed:\r\n$query\r\n"); }
        $paypalAccountEmail = $objResult->fields['value'];

        $query = "SELECT currency_order_sum, selected_currency_id FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders WHERE orderid=$orderid";
        $objResult = $objDatabase->Execute($query);
if (_PAYPAL_DEBUG) @fwrite($log, "query $query\r\nresult ".($objResult ? 'true' : 'false')."\r\n"); if (!$objResult) { @fwrite($log, "Query failed:\r\n$query\r\n"); }
        $currencyId = $objResult->fields['selected_currency_id'];
        $amount = $objResult->fields['currency_order_sum'];

        $query = "SELECT code FROM ".DBPREFIX."module_shop".MODULE_INDEX."_currencies WHERE id=$currencyId";
        $objResult = $objDatabase->Execute($query);
if (_PAYPAL_DEBUG) @fwrite($log, "query $query\r\nresult ".($objResult ? 'true' : 'false')."\r\n"); if (!$objResult) { @fwrite($log, "Query failed:\r\n$query\r\n"); }
        $currencyCode = $objResult->fields['code'];

        $newOrderStatus = SHOP_ORDER_STATUS_CANCELLED;
        while (!feof($fp)) {
            $res = fgets($fp, 1024);
//$res = 'VERIFIED';
//if (_PAYPAL_DEBUG) @fwrite($log, "got $res\r\n");
            if (preg_match('/^VERIFIED/', $res)) {
                if (   $paypalAccountEmail == $receiver_email
                    && $payment_amount == $amount
                    && $payment_currency == $currencyCode) {
                    // Update the order status to a value determined
                    // automatically.
                    $newOrderStatus = SHOP_ORDER_STATUS_PENDING;
if (_PAYPAL_DEBUG) @fwrite($log, "VERIFIED\r\nquery $query\r\nresult ".($objResult ? 'true' : 'false')."\r\n");
                    break;
                }
            }
            if (preg_match('/^INVALID/', $res)) {
                // The payment failed.
                $newOrderStatus = SHOP_ORDER_STATUS_CANCELLED;
if (_PAYPAL_DEBUG) @fwrite($log, "INVALID\r\nquery $query\r\nresult ".($objResult ? 'true' : 'false')."\r\n");
                break;
            }
        }
        fclose ($fp);

if (_PAYPAL_DEBUG) {
  echo "The response from IPN was: <b>".$res."</b><br><br>";
  $query = "SELECT order_status FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders WHERE orderid=$orderid";
  $objResult = $objDatabase->Execute($query);
  @fwrite($log, "query $query\r\nresult ".($objResult ? 'true' : 'false')."\r\n"); if (!$objResult) { @fwrite($log, "Query failed:\r\n$query\r\n"); }
  $orderStatus = $objResult->fields['order_status'];
  @fwrite($log, "order status: $orderStatus\r\n");
  @fwrite($log, "finished, leaving\r\n");
  @fclose($log);
}
        // This method is now called from within here.
        // The IPN may be received after the customer has left both
        // the PayPal site and the Shop!
        Shop::updateOrderStatus($orderid, $newOrderStatus, 'PaypalIPN');
    }


    /**
     * Test the IPN processing
     *
     * Creates a dummy order and sends the IPN to the Shop.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function testIpn()
    {
        global $objDatabase;

        $query = "SELECT `value` FROM ".DBPREFIX."module_shop".MODULE_INDEX."_config
                  WHERE `name`='paypal_account_email'";
        $objResult = $objDatabase->Execute($query);
        $paypalAccountEmail = $objResult->fields['value'];

/*
$log = @fopen(ASCMS_DOCUMENT_ROOT.'/testIpn.txt', 'w');

        $currencyId = 1; // CHF
        $amount = '99.00';
        $query = "SELECT code FROM ".DBPREFIX."module_shop".MODULE_INDEX."_currencies WHERE id=$currencyId";
        $objResult = $objDatabase->Execute($query);
        $currencyCode = $objResult->fields['code'];
        // Create order entry
        $query = "
            INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_orders (
            orderid, customerid, selected_currency_id, order_sum, currency_order_sum,
            order_date, order_status,
            ship_prefix, ship_company, ship_firstname, ship_lastname,
            ship_address, ship_city, ship_zip, ship_country_id, ship_phone,
            tax_price, currency_ship_price, shipping_id, payment_id,
            currency_payment_price,
            customer_ip, customer_host, customer_lang, customer_browser, customer_note,
            last_modified, modified_by
            )
            VALUES (
            NULL, '0', $currencyId, '0.00', '$amount',
            '0000-00-00 00:00:00', '0',
            '', '', '', '',
            '', '', NULL, NULL, '',
            '0.00', '0.00', NULL, NULL,
            '0.00',
            '', '', '', '', '',
            '0000-00-00 00:00:00', ''
        )";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
die ("Error: Failed to insert order<br />$query<br />");
        }
        $orderid = $objDatabase->insert_id();

        // Create request for the Shop
        $arrRequest = array(
            'mc_gross' => $amount,
            'mc_currency' => $currencyCode,
            'receiver_email' => $paypalAccountEmail,
            'custom' => $orderid,
        );
        $req = '';
        foreach ($arrRequest as $key => $value) {
            $value = urlencode($value);
            $req .= ($req ? '&' : '')."$key=$value";
        }
@fwrite($log, "Made parameters: $req\r\n");

        $errno = '';
        $errstr = '';
        $fp = fsockopen('localhost', 80, $errno, $errstr, 30);
        if (!$fp) {
@fwrite($log, "no fp, errno $errno, error $errstr\r\nexiting\r\n");@fclose($log);
            exit;
        }
        // Post IPN to shop
        $header  =
            "POST ".ASCMS_PATH_OFFSET."/index.php?section=shop".MODULE_INDEX."&act=paypalIpnCheck HTTP/1.0\r\n".
            "Content-Type: application/x-www-form-urlencoded\r\n".
            "Content-Length: ".strlen($req)."\r\n\r\n";
        fwrite($fp, "$header$req");
@fwrite($log, "sent\r\n$header$req\r\n\r\n");
@fclose($log);
*/

        $currencyCode = $_POST['currency_code'];
        $amount = $_POST['amount'];
        $orderid = $_POST['custom'];
        $host = ASCMS_PROTOCOL.'://'.$_SERVER['HTTP_HOST'].ASCMS_PATH_OFFSET;

die('
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head><title>IPN Test</title></head><body>
<form method="post" action="'.ASCMS_PATH_OFFSET.'/index.php?section=shop'.MODULE_INDEX.'&act=paypalIpnCheck">
<input type="hidden" name="mc_gross" value="'.$amount.'" />
<input type="hidden" name="mc_currency" value="'.$currencyCode.'" />
<input type="hidden" name="receiver_email" value="'.$paypalAccountEmail.'" />
<input type="hidden" name="custom" value="'.$orderid.'" />
<input type="submit" name="ipn" value="IPN senden" />
</form>
<a href="'.$host.'/index.php?section=shop'.MODULE_INDEX.'&amp;cmd=success&amp;handler=paypal&amp;result=1&amp;orderid='.$orderid.'">
  Erfolgreiche Zahlung, zur&uuml;ck zum Shop
</a><br />
<a href="'.$host.'/index.php?section=shop'.MODULE_INDEX.'&amp;cmd=success&amp;handler=paypal&amp;result=2&amp;orderid='.$orderid.'">
  Zahlung annulieren, zur&uuml;ck zum Shop
</a><br />
<a href="'.$host.'/index.php?section=shop'.MODULE_INDEX.'&amp;cmd=success&amp;handler=paypal&amp;result=0&amp;orderid='.$orderid.'">
  Zahlung abbrechen, zur&uuml;ck zum Shop
</a><br />
</body></html>
');
    }


    /**
     * Validate the reply to the IPN message from the Shop
     * and send back the VALID or INVALID message.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function testIpnValidate()
    {
        global $objDatabase;

$log = @fopen(ASCMS_DOCUMENT_ROOT.'/ipnValidateLog.txt', 'w');

        $orderid = $_POST['custom'];

        $query = "SELECT `value` FROM ".DBPREFIX."module_shop".MODULE_INDEX."_config
                  WHERE `name`='paypal_account_email'";
        $objResult = $objDatabase->Execute($query);
@fwrite($log, "query $query\r\nresult ".($objResult ? 'true' : 'false')."\r\n"); if (!$objResult) { @fwrite($log, "Query failed:\r\n$query\r\n"); }
        $paypalAccountEmail = $objResult->fields['value'];

        $query = "SELECT currency_order_sum, selected_currency_id FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders WHERE orderid=$orderid";
        $objResult = $objDatabase->Execute($query);
@fwrite($log, "query $query\r\nresult ".($objResult ? 'true' : 'false')."\r\n"); if (!$objResult) { @fwrite($log, "Query failed:\r\n$query\r\n"); }
        $currencyId = $objResult->fields['selected_currency_id'];
        $amount = $objResult->fields['currency_order_sum'];

        $query = "SELECT code FROM ".DBPREFIX."module_shop".MODULE_INDEX."_currencies WHERE id=$currencyId";
        $objResult = $objDatabase->Execute($query);
@fwrite($log, "query $query\r\nresult ".($objResult ? 'true' : 'false')."\r\n"); if (!$objResult) { @fwrite($log, "Query failed:\r\n$query\r\n"); }
        $currencyCode = $objResult->fields['code'];

        // read and verify the post from the Shop
        $cmd = ($_POST['cmd'] == '_notify-validate' ? 'OK' : 'FAILED');
        $mc_gross = ($_POST['mc_gross'] == $amount ? 'OK' : 'FAILED');
        $mc_currency = ($_POST['mc_currency'] == $currencyCode ? 'OK' : 'FAILED');
        $receiver_email = (urldecode($_POST['receiver_email']) == $paypalAccountEmail ? 'OK' : 'FAILED');
@fwrite($log,
  "cmd $cmd\r\n".
  "mc_gross $mc_gross\r\n".
  "mc_currency $mc_currency\r\n".
  "receiver_email $receiver_email\r\n".
  "order ID $orderid\r\n"
);
        $reply = (   $cmd == 'OK'
                  && $mc_gross == 'OK'
                  && $mc_currency == 'OK'
                  && $receiver_email == 'OK'
            ? "VERIFIED\r\n"
            : "INVALID\r\n"
        );

        // Send reply back to Shop
@fwrite($log, "sending reply $reply");
@fclose($log);
        die($reply);
    }


    static function getAcceptedCurrencyCodeArray()
    {
        return self::$arrAcceptedCurrencyCode;
    }


    static function getAcceptedCurrencyCodeMenuoptions($selected=0)
    {
        $strMenuoptions = '';
        foreach (self::$arrAcceptedCurrencyCode as $code) {
            $strMenuoptions .=
                '<option value="'.$code.'"'.
                ($selected == $code ? ' selected="selected"' : '').">".
                $code.
                "</option>\n";
        }
        return $strMenuoptions;
    }

}

?>
