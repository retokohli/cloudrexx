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
 * IPN log mode
 * @internal
    Log modes:
    0   No logging
    1   Logging to file (/dbg.log)
*/
define('_PAYPAL_IPN_LOG', 0);

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

        $retval =
(_PAYPAL_DEBUG == 0
? "<script language='JavaScript' type='text/javascript'>
// <![CDATA[
function go() { document.paypal.submit(); }
window.setTimeout('go()', 3000);
// ]]>
</script>
"
: '');

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

if (_PAYPAL_IPN_LOG) {
    DBG::activate(DBG_PHP|DBG_ADODB_ERROR|DBG_LOG_FILE);
    DBG::log("-------------------------------------------------------");
    DBG::log("Paypal::ipnCheck(): Entered on ".date("Y-m-d H:i:s"));
}

        // assign posted variables to local variables
// The following are unused
//        $item_name = $_POST['item_name'];
//        $item_number = $_POST['item_number'];
//        $payment_status = $_POST['payment_status'];
//        $txn_id = $_POST['txn_id'];
//        $payer_email = $_POST['payer_email'];
        $payment_amount = (isset($_POST['mc_gross'])
            ? $_POST['mc_gross'] : null);
        $payment_currency = (isset($_POST['mc_currency'])
            ? $_POST['mc_currency'] : null);
        $receiver_email = (isset($_POST['business'])
            ? urldecode($_POST['business']) : null);
        $orderid = (isset($_POST['custom'])
            ? $_POST['custom'] : null);
        if (!(   $payment_amount && $payment_currency
              && $receiver_email && $orderid)) {
if (_PAYPAL_IPN_LOG) {
    DBG::log("Invalid IPN parameter values: amount $payment_amount, currency $payment_currency, e-mail $receiver_email, order ID $orderid");
    DBG::log("Paypal::ipnCheck(): Aborted on ".date("Y-m-d H:i:s"));
    DBG::log("-------------------------------------------------------");
    DBG::deactivate();
}
            exit;
        }

// TODO: Update to use SettingDb!
        $query = "
            SELECT `value`
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_config
             WHERE `name`='paypal_account_email'";
        $objResult = $objDatabase->Execute($query);
        $paypalAccountEmail = $objResult->fields['value'];

        $query = "
            SELECT currency_order_sum, selected_currency_id
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders
             WHERE orderid=$orderid";
        $objResult = $objDatabase->Execute($query);
        $currencyId = null;
        $amount = null;
        if ($objResult && !$objResult->EOF) {
            $currencyId = $objResult->fields['selected_currency_id'];
            $amount = $objResult->fields['currency_order_sum'];
        }
        if (empty($currencyId) || empty($amount)) {
if (_PAYPAL_IPN_LOG) {
    DBG::log("Error querying amount ($amount) or currency ID ($currencyId) for Order ID $orderid, ignoring");
    DBG::log("Paypal::ipnCheck(): Finished on ".date("Y-m-d H:i:s"));
    DBG::log("-------------------------------------------------------");
    DBG::deactivate();
}
            exit();
        }

        $query = "
            SELECT code
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_currencies
             WHERE id=$currencyId";
        $objResult = $objDatabase->Execute($query);
        $currencyCode = null;
        if ($objResult && !$objResult->EOF) {
            $currencyCode = $objResult->fields['code'];
        }
        if (empty($currencyCode)) {
if (_PAYPAL_IPN_LOG) {
    DBG::log("Failed to query currency code for currency ID $currencyId (Order ID $orderid), ignoring");
    DBG::log("Paypal::ipnCheck(): Finished on ".date("Y-m-d H:i:s"));
    DBG::log("-------------------------------------------------------");
    DBG::deactivate();
}
            exit();
        }

        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';
        foreach ($_POST as $key => $value) {
            $value = urlencode($value);
            $req .= "&$key=$value";
        }
//if (_PAYPAL_IPN_LOG) @fwrite($log, "Made parameters: $req\r\n");

        $errno = '';
        $errstr = '';
        $uri =
            (_PAYPAL_DEBUG == 0
              ? 'www.paypal.com'
              : (_PAYPAL_DEBUG == 1
                ? 'www.sandbox.paypal.com'
                // _PAYPAL_IPN_LOG == 2 or higher
                : 'localhost'));
if (_PAYPAL_IPN_LOG) DBG::log("Sending IPN validation request to $uri");
        $fp = fsockopen($uri, 80, $errno, $errstr, 30);
        if (!$fp) {
if (_PAYPAL_IPN_LOG) DBG::log("Failed to connect Socket with $uri, errno $errno, error $errstr - exiting");
            exit();
        }

        // post back to PayPal system to validate
        $header  =
            (_PAYPAL_DEBUG < 2
                ? "POST /cgi-bin/webscr HTTP/1.0\r\n"
                : "POST ".ASCMS_PATH_OFFSET."/index.php?section=shop".
                    MODULE_INDEX."&act=testIpnValidate HTTP/1.0\r\n").
            "Content-Type: application/x-www-form-urlencoded\r\n".
            "Content-Length: ".strlen($req)."\r\n\r\n";
        fwrite($fp, $header.$req);
if (_PAYPAL_IPN_LOG) DBG::log("Sent header and request: $header$req");

        $newOrderStatus = SHOP_ORDER_STATUS_CANCELLED;
        while (!feof($fp)) {
            $res = fgets($fp, 1024);
if (_PAYPAL_IPN_LOG) DBG::log("PayPal response (part): ".trim($res));
            if (preg_match('/^VERIFIED/', $res)) {
if (_PAYPAL_IPN_LOG) {
DBG::log("PayPal IPN successfully VERIFIED");
                if (   $receiver_email == $paypalAccountEmail
                    && $payment_amount == $amount
                    && $payment_currency == $currencyCode) {
DBG::log("INFO: Data identical");
                } else {
DBG::log("NOTE: Differing data:");
DBG::log("Account:  Expected /$paypalAccountEmail/, got /$receiver_email/");
DBG::log("Amount:  Expected /$amount/, got /$payment_amount/");
DBG::log("Currency:  Expected /$currencyCode/, got /$payment_currency/");
                }
}
                // Update the order status to a value determined
                // automatically.
                $newOrderStatus = SHOP_ORDER_STATUS_PENDING;
                break;
            }
            if (preg_match('/^INVALID/', $res)) {
                // The payment failed.
                $newOrderStatus = SHOP_ORDER_STATUS_CANCELLED;
if (_PAYPAL_IPN_LOG) {
    DBG::log("PayPal IPN is INVALID, new Order status CANCELLED (POST values: amount /$payment_amount/, currency /$payment_currency/, e-mail /$receiver_email/, order ID /$orderid/)");
}
                break;
            }
//if (_PAYPAL_IPN_LOG) DBG::log("PayPal's response: $res");
        }
        fclose ($fp);

        // This method is now called from within here.
        // The IPN may be received after the customer has left both
        // the PayPal site and the Shop!
if (_PAYPAL_IPN_LOG) DBG::log("Updating Order ID $orderid status, new value $newOrderStatus");
        $order_status = Shop::updateOrderStatus(
            $orderid, $newOrderStatus, 'PaypalIPN');
if (_PAYPAL_IPN_LOG) {
    DBG::log("Updated Order status to $order_status");
    DBG::log("Paypal::ipnCheck(): Finished on ".date("Y-m-d H:i:s"));
    DBG::log("-------------------------------------------------------");
    DBG::deactivate();
}
        exit();
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
