<?php
/**
 * Interface for the PayPal form
 * @link        https://www.paypal.com/ch/cgi-bin/webscr?cmd=_pdn_howto_checkout_outside
 * @link        https://www.paypal.com/ipn
 * @author      Stefan Heinemannn <janik.tschanz@comvation.com>
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  module_auction
 * @todo        Edit PHP DocBlocks!
 * @todo        The description 'interface' is ambiguous. Find a better way.
 * @todo        Who has actually written this?
 */

/**
 * Interface for the PayPal form
 *
 * It requires a html form to send the date to
 * PayPal. This class generates it.
 * @author      Stefan Heinemannn <janik.tschanz@comvation.com>
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  module_auction
 */
class PayPal
{
    /**
     * e-mail address for paypal paying
     * @var string
     * @see getForm()
     */
    var $PayPalAcc;
    
    
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
    function getForm($orderId)
    {
        global $_ARRAYLANG;
        
        $business = $this->getBusiness();
        $currency_code = "EUR";
        $amount = $this->getPrice($orderId);

        $sum = md5("contrexx".$_SERVER['HTTP_HOST'].intval($amount).$orderid);
        $host = ASCMS_PROTOCOL."://".$_SERVER['HTTP_HOST'].ASCMS_PATH_OFFSET;
        $return = $host. "/index.php?section=auction&cmd=paypal_successfull&id=$orderId";
        $cancel_return = $host."/index.php?section=auction&amp;paypal_error&amp;id=$orderId";
        $notify_url = $host."/index.php?section=auction&amp;act=paypalIpnCheck";
        $item_name = "Insarat";

        
        $retval .= "\n<form name=\"paypal\" action=\"https://www.sandbox.paypal.com/ch/cgi-bin/webscr\" method=\"post\">\n";        
        //$retval .= "\n<form name=\"paypal\" action=\"https://www.paypal.com/ch/cgi-bin/webscr\" method=\"post\">\n";        
        $retval .= $this->getInput("cmd", "_xclick");
        $retval .= $this->getInput("business", $business);
        $retval .= $this->getInput("item_name", $item_name);
        $retval .= $this->getInput("currency_code", $currency_code);
        $retval .= $this->getInput("amount", $amount);
        $retval .= $this->getInput("custom", $orderId);
        $retval .= $this->getInput("notify_url", $notify_url);
        $retval .= $this->getInput("return", $return);
        $retval .= $this->getInput("cancel_return", $cancel_return);
        $retval .= "{$_ARRAYLANG['TXT_PAYPAL_SUBMIT']}Text<br /><br />";
        $retval .= "<input id=\"submit\" type=\"submit\" name=\"submit\" value=\"Button{$_ARRAYLANG['TXT_PAYPAL_SUBMIT_BUTTON']}\">\n";
        $retval .= "</form>\n";
        
        return $retval;
    }
    
    /**
     * Generates an hidden input field 
     *
     * @param $field Array containing the name and the value of the field
     */
    function getInput($name, $value)
    {
        return "<input type=\"hidden\" name=\"$name\" value=\"$value\">\n";
    }
    
    
    /**
     * reads the paypal email address out of the database
     */
    function getBusiness()
    {
        global $objDatabase;
        //get paypal
        $objReslut = $objDatabase->Execute("SELECT profile FROM ".DBPREFIX."module_auction_paypal WHERE id = '1'");
          if($objReslut !== false){
            while(!$objReslut->EOF){
                $paypalProfile         = $objReslut->fields['profile'];
                $objReslut->MoveNext();
            }
          }
          
          return $paypalProfile;
    }
    
    
    /**
     * reads the price out of the database
     */
    function getPrice($orderId)
    {
        global $objDatabase;
        
        $objReslut = $objDatabase->Execute("SELECT premium FROM ".DBPREFIX."module_auction WHERE id = '".$orderId."'");
          if($objReslut !== false){
            while(!$objReslut->EOF){
                $premium        = $objReslut->fields['premium'];
                $objReslut->MoveNext();
            }
          }
        
        $objReslut = $objDatabase->Execute("SELECT price, price_premium FROM ".DBPREFIX."module_auction_paypal WHERE id = '1'");
          if($objReslut !== false){
            while(!$objReslut->EOF){
                $paypalPrice         = $objReslut->fields['price'];
                $paypalPremium        = $objReslut->fields['price_premium'];
                $objReslut->MoveNext();
            }
          }
          
          if($premium == '1'){
              $paypalTotal = $paypalPrice+$paypalPremium;
          }else{
              $paypalTotal = $paypalPrice;
          }
          
          return $paypalTotal;
    }
    
    
    /**
     * confirms the payment
     */
    function payConfirm()
    {
        global $objDatabase;
        
        if (!empty($_GET['orderid'])) {
                $orderid = intval($_GET['orderid']);
        }
        $query = "SELECT order_status FROM ".DBPREFIX."module_shop_orders WHERE orderid = $orderid";
        if (!$objResult = $objDatabase->Execute($query)) {
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
        $fp = fsockopen ('www.sandbox.paypal.com', 80, $errno, $errstr, 30);
//        $fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);
        
        // assign posted variables to local variables
        $item_name = $_POST['item_name'];
        $item_number = $_POST['item_number'];
        $payment_status = $_POST['payment_status'];
        $payment_amount = $_POST['mc_gross'];
        $payment_currency = $_POST['mc_currency'];
        $txn_id = $_POST['txn_id'];
        $receiver_email = $_POST['receiver_email'];
        $payer_email = $_POST['payer_email'];
        $orderId = $_POST['custom'];
        
        if (!$fp) {
            exit;
        } else {
            fwrite ($fp, $header . $req);
            while (!feof($fp)) {
                $res = fgets ($fp, 1024);
                if (strcmp ($res, "VERIFIED") == 0) {
                    //wenn bezahlung ok, mach...
                    
                    //$amount = $this->getPrice($orderId);
                    
                    //if ($payment_amount == $amount && $payment_currency == "EUR") {
                        $query = "UPDATE ".DBPREFIX."module_auction SET paypal='1' WHERE id ='175' ";
                        $objResult = $objDatabase->Execute($query);
                    //}
                }
            }
            fclose ($fp);
        }
    }
}

?>
