<?php

/**
 * Interface for the PayPal form
 *
 * It requires a html form to send the date to
 * PayPal. This class generates it.
 *
 * @link https://www.paypal.com/ch/cgi-bin/webscr?cmd=_pdn_howto_checkout_outside
 * @link https://www.paypal.com/ipn
 * @author Stefan Heinemannn <stefan.heinemann@astalavista.ch>
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */


class PayPal
{
    /**
     * e-mail address for paypal paying
     * @var string
     * @see getForm()
     */
    var $PayPalAcc;

    var $arrAcceptedCurrencyCodes = array(
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
     *
     * Gets the main information for paypal
     */
    function __construct()
    {

    }

    /**
     * PHP 4.3 constructor
     *
     * calls the __construct() function
     */
    function PayPal()
    {
        $this->__construct();
    }

    /**
     * Returns the form for PayPal accessing
     *
     * @return string HTML-Code for the PayPal form
     */
    function getForm()
    {
        global $_ARRAYLANG;

        $orderid = $_SESSION['shop']['orderid'];
        $business = $this->getBusiness();
        $item_name = $_ARRAYLANG['TXT_SHOP_PAYPAL_ITEM_NAME'];
        $currency_code = $this->getCurencyCode($_SESSION['shop']['currencyId']);
        $amount = $_SESSION['shop']['grand_total_price'];

        $sum = md5('contrexx'.$_SERVER['HTTP_HOST'].intval($amount).$orderid);
        $host = ASCMS_PROTOCOL.'://'.$_SERVER['HTTP_HOST'].ASCMS_PATH_OFFSET;
        $return = $host. '/index.php?section=shop&amp;cmd=success&amp;handler=paypal&amp;orderid=$orderid';
        $cancel_return = $host.'/index.php?section=shop&amp;cmd=cancel";//&amp;orderid=$orderid';
        $notify_url = $host.'/index.php?section=shop&amp;act=paypalIpnCheck';

        $retval = "<script language='JavaScript' type='text/javascript'>
            // <![CDATA[
                function go()
                {
                    document.paypal.submit();
                }
                window.setTimeout('go()',3000);
            // ]]>
            </script>";

//        $retval .= "\n<form name=\"paypal\" action=\"https://www.sandbox.paypal.com/ch/cgi-bin/webscr\" method=\"post\">\n";
        $retval .= "\n<form name='paypal' action='https://www.paypal.com/ch/cgi-bin/webscr' method='post'>\n";
        $retval .= $this->getInput('cmd', '_xclick');
        $retval .= $this->getInput('business', $business);
        $retval .= $this->getInput('item_name', $item_name);
        $retval .= $this->getInput('currency_code', $currency_code);
        $retval .= $this->getInput('amount', $amount);
        $retval .= $this->getInput('custom', $orderid);
        $retval .= $this->getInput('notify_url', $notify_url);
        $retval .= $this->getInput('return', $return);
        $retval .= $this->getInput('cancel_return', $cancel_return);
        $retval .= "{$_ARRAYLANG['TXT_PAYPAL_SUBMIT']}<br /><br />";
        $retval .= "<input type='submit' name='submitbutton' value=\"{$_ARRAYLANG['TXT_PAYPAL_SUBMIT_BUTTON']}\" />\n";
        $retval .= "</form>\n";

        return $retval;
    }

    /**
     * Generates a hidden input field
     * @param $field Array containing the name and the value of the field
     */
    function getInput($name, $value)
    {
        return "<input type='hidden' name=\"$name\" value=\"$value\" />\n";
    }


    /**
     * Reads the paypal email address from the database
     */
    function getBusiness()
    {
        global $objDatabase;
        $query = "
            SELECT value FROM ".DBPREFIX."module_shop_config
             WHERE name = 'paypal_account_email'
        ";
        $objResult = $objDatabase->Execute($query);
        //FIXME
        // was ist wenn das feld leer ist?
        return $objResult->fields['value'];
    }

    /**
     * Read the currency code from the database
     * @param   integer     $id         The currency code ID
     * @return  string                  The currency code
     */
    function getCurencyCode($id)
    {
        global $objDatabase;
        $query = "
            SELECT code FROM ".DBPREFIX."module_shop_currencies
             WHERE id = '$id'
        ";
        $objResult = $objDatabase->Execute($query);
        return $objResult->fields['code'];
    }


    /**
     * Try to determine whether the payment was successful.
     * @return  mixed       True on success, false on failure, or
     *                      NULL if the order status isn't set to 'confirmed'.
     */
    function payConfirm()
    {
        global $objDatabase;

        if (isset($_GET['orderid']) && !empty($_GET['orderid'])) {
            $orderid = intval($_GET['orderid']);
        }
        $query = "
            SELECT order_status
              FROM ".DBPREFIX."module_shop_orders
             WHERE orderid=$orderid
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        if ($objResult->fields['order_status'] == 1) {
            return $orderid;
        } else {
            return NULL;
        }
    }


    /**
     * Communicates with paypal
     */
    function ipnCheck()
    {
        global $objDatabase;

        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';
        foreach ($_POST as $key => $value) {
            $value = urlencode(stripslashes($value));
            $req .= "&$key=$value";
        }

        // post back to PayPal system to validate
        $header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
//        $fp = fsockopen ('www.sandbox.paypal.com', 80, $errno, $errstr, 30);
        $fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);

        // assign posted variables to local variables
        $item_name = $_POST['item_name'];
        $item_number = $_POST['item_number'];
        $payment_status = $_POST['payment_status'];
        $payment_amount = $_POST['mc_gross'];
        $payment_currency = $_POST['mc_currency'];
        $txn_id = $_POST['txn_id'];
        $receiver_email = $_POST['receiver_email'];
        $payer_email = $_POST['payer_email'];
        $orderid = $_POST['custom'];

        if (!$fp) {
            exit;
        } else {
            fwrite ($fp, $header . $req);
            while (!feof($fp)) {
                $res = fgets ($fp, 1024);
                if (strcmp ($res, 'VERIFIED') == 0) {
                    $query = "SELECT value FROM ".DBPREFIX."module_shop_config
                              WHERE name = 'paypal_account_email'";
                    $objResult = $objDatabase->Execute($query);

                    if ($objResult->fields['value'] == $receiver_email) {
                        $query = "SELECT currency_order_sum, selected_currency_id FROM ".DBPREFIX."module_shop_orders WHERE orderid = $orderid";
                        $objResult = $objDatabase->Execute($query);
                        $currency = $objResult->fields['selected_currency_id'];
                        $amount = $objResult->fields['currency_order_sum'];

                        $query = "SELECT code FROM ".DBPREFIX."module_shop_currencies WHERE id = $currency";
                        $objResult = $objDatabase->Execute($query);

                        if ($payment_amount == $amount && $payment_currency == $objResult->fields['code']) {
                            $query = "UPDATE ".DBPREFIX."module_shop_orders
                                           SET order_status='1'
                                       WHERE orderid =".intval($orderid);
                            $objResult = $objDatabase->Execute($query);
                        }
                    }
                }
            }
            fclose ($fp);
        }
    }
}

?>
