<?php

/**
 * Payment Service Provider class
 * @package     contrexx
 * @subpackage  module_shop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @version     2.1.0
 * @author      Reto Kohli <reto.kohli@comvation.com> (parts)
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Debug mode
 */
define('_PAYMENT_DEBUG', 0);

/**
 * Payment logo folder (e.g. /modules/shop/images/payments/)
 */
define('SHOP_PAYMENT_LOGO_PATH',
       ASCMS_PATH_OFFSET.'/modules/shop/images/payments/');

/**
 * Settings
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Settings.class.php';
/**
 * Saferpay payment handling
 */
require_once ASCMS_MODULE_PATH.'/shop/payments/saferpay/Saferpay.class.php';
/**
 * Yellowpay payment handling
 */
require_once ASCMS_MODULE_PATH.'/shop/payments/yellowpay/Yellowpay.class.php';
/**
 * PayPal payment handling
 */
require_once ASCMS_MODULE_PATH.'/shop/payments/paypal/Paypal.class.php';
/**
 * Dummy payment handling -- for testing purposes only.
 */
require_once ASCMS_MODULE_PATH.'/shop/payments/dummy/Dummy.class.php';

/**
 * Payment Service Provider manager
 *
 * These are the requirements of the current specification
 * for any external payment service provider class:
 * - Any payment method *MUST* be implemented in its own class, with its
 *   constructor and/or methods being called from PaymentProcessing.class.php
 *   using only the two methods checkIn() and checkOut().
 * - Any data needed by the payment service class *MUST* be provided
 *   as arguments to the constructor and/or methods from within the
 *   PaymentProcessing class.
 * - Any code in checkIn() *MUST* return either a valid payment form *OR*
 *   redirect to a payment page of that provider, supplying all necessary
 *   data for a successful payment.
 * - Any code in checkOut() *MUST* return the original order ID of the order
 *   being processed on success, false otherwise (both in the case of failure
 *   and upon cancelling the payment).
 * - A payment provider class *MUST NOT* access the database itself, in
 *   particular it is strictly forbidden to read or change the order status
 *   of any order.
 * - A payment provider class *MUST NOT* store any data in the global session
 *   array.  Instead, it is to rely on the protocol of the payment service
 *   provider to transmit and retrieve all necessary data.
 * - Any payment method providing different return values for different
 *   outcomes of the payment in the consecutive HTTP requests *SHOULD* use
 *   the follwing arguments and values:
 *      Successful payments:            result=1
 *      Successful payments, silent *:  result=-1
 *      Failed payments:                result=0
 *      Aborted payments:               result=2
 *   * Some payment services do not only redirect the customer after a
 *     successful payment has completed, but already after the payment
 *     has been authorized.  Yellowpay, as an example, expects an empty
 *     page as a reply to such a request.
 *     Other PSP send the notification even for failed or cancelled
 *     transactions, e.g. Datatrans.  Consult your local PSP for further
 *     information.
 * @package     contrexx
 * @subpackage  module_shop
 * @author      Reto Kohli <reto.kohli@comvation.com> (parts)
 * @copyright   CONTREXX CMS - COMVATION AG
 * @version     2.1.0
 */
class PaymentProcessing
{
    /**
     * Array of all available payment processors
     * @access  private
     * @static
     * @var     array
     */
    private static $arrPaymentProcessor = false;

    /**
     * The selected processor ID
     * @access  private
     * @static
     * @var     integer
     */
    private static $processorId = false;


    /**
     * Initialize known payment service providers
     */
    static function init()
    {
        global $objDatabase;

        $query = '
            SELECT id, type, name, description,
                   company_url, status, picture, text
              FROM '.DBPREFIX.'module_shop_payment_processors
          ORDER BY id
        ';
        $objResult = $objDatabase->Execute($query);
        while (!$objResult->EOF) {
            self::$arrPaymentProcessor[$objResult->fields['id']] = array(
                'id'          => $objResult->fields['id'],
                'type'        => $objResult->fields['type'],
                'name'        => $objResult->fields['name'],
                'description' => $objResult->fields['description'],
                'company_url' => $objResult->fields['company_url'],
                'status'      => $objResult->fields['status'],
                'picture'     => $objResult->fields['picture'],
                'text'        => $objResult->fields['text']
            );
            $objResult->MoveNext();
        }
    }


    /**
     * Set the active processor ID
     * @return  void
     * @param   integer $processorId    The PSP ID to use
     * @static
     */
    static function initProcessor($processorId)
    {
        if (!is_array(self::$arrPaymentProcessor)) self::init();
        self::$processorId = $processorId;
    }


    /**
     * Returns an array with all the payment processor names indexed
     * by their ID.
     * @return  array             The payment processor name array
     *                            on success, the empty array on failure.
     * @static
     */
    static function getPaymentProcessorNameArray()
    {
        if (empty(self::$arrPaymentProcessor)) self::init();
        $arrName = array();
        foreach (self::$arrPaymentProcessor as $id => $arrProcessor) {
            $arrName[$id] = $arrProcessor['name'];
        }
        return $arrName;
    }


    /**
     * Returns the name associated with a payment processor ID.
     *
     * If the optional argument is not set and greater than zero, the value
     * processorId stored in this object is used.  If this is invalid as
     * well, returns the empty string.
     * @param   integer     $processorId    The payment processor ID
     * @return  string                      The payment processors' name,
     *                                      or the empty string on failure.
     * @global  ADONewConnection
     * @static
     */
    static function getPaymentProcessorName($processorId=0)
    {
        // Either the argument or the class variable must be initialized
        if (!$processorId) $processorId = self::$processorId;
        if (!$processorId) return '';
        if (empty(self::$arrPaymentProcessor)) self::init();
        return self::$arrPaymentProcessor[$processorId]['name'];
    }


    /**
     * Returns the processor type associated with a payment processor ID.
     *
     * If the optional argument is not set and greater than zero, the value
     * processorId stored in this object is used.  If this is invalid as
     * well, returns the empty string.
     * Note: Currently supported types are 'internal' and 'external'.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @param   integer     $processorId    The payment processor ID
     * @return  string                      The payment processor type,
     *                                      or the empty string on failure.
     * @global  ADONewConnection
     * @static
     */
    static function getCurrentPaymentProcessorType($processorId=0)
    {
        // Either the argument or the object may not be initialized
        if (!$processorId) $processorId = self::$processorId;
        if (!$processorId) return '';
        if (empty(self::$arrPaymentProcessor)) self::init();
        return self::$arrPaymentProcessor[$processorId]['type'];
    }


    /**
     * Returns the picture file name associated with a payment processor ID.
     *
     * If the optional argument is not set and greater than zero, the value
     * processorId stored in this object is used.  If this is invalid as
     * well, returns the empty string.
     * @param   integer     $processorId    The payment processor ID
     * @return  string                      The payment processors' picture
     *                                      file name, or the empty string
     *                                      on failure.
     * @global  ADONewConnection
     * @static
     */
    static function getPaymentProcessorPicture($processorId=0)
    {
        // Either the argument or the object may not be initialized
        if (!$processorId) $processorId = self::$processorId;
        if (!$processorId) return '';
        if (empty(self::$arrPaymentProcessor)) self::init();
        return self::$arrPaymentProcessor[$processorId]['picture'];
    }


    /**
     * Check out the payment processor associated with the payment processor
     * selected by {@link initProcessor()}.
     *
     * If the page is redirected, or has already been handled, returns the empty
     * string.
     * In the other cases, returns HTML code for the payment form and to insert
     * a picture representing the payment method.
     * @return  string      Empty string, or HTML code
     * @static
     */
    static function checkOut()
    {
        if (!is_array(self::$arrPaymentProcessor)) self::init();
        $return = '';
        switch (self::getPaymentProcessorName()) {
            case 'Internal':
                /* Redirect browser */
                CSRF::header('location: index.php?section=shop'.MODULE_INDEX.'&cmd=success&result=1&handler=Internal');
                exit;
                break;
            case 'Internal_LSV':
                /* Redirect browser */
                CSRF::header('location: index.php?section=shop'.MODULE_INDEX.'&cmd=success&result=1&handler=Internal');
                exit;
                break;
            case 'Internal_CreditCard':
                $return = self::_Internal_CreditCardProcessor();
                break;
            case 'Internal_Debit':
                $return = self::_Internal_DebitProcessor();
                break;
            case 'Saferpay':
            case 'Saferpay_All_Cards':
                $return = self::_SaferpayProcessor();
                break;
            case 'Saferpay_Mastercard_Multipay_CAR':
                $return = self::_SaferpayProcessor(array('Mastercard Multipay CAR'));
                break;
            case 'Saferpay_Visa_Multipay_CAR':
                $return = self::_SaferpayProcessor(array('Visa Multipay CAR'));
                break;
            case 'yellowpay': // was: 'PostFinance_DebitDirect'
                $return = self::_YellowpayProcessor();
                break;
            // Added 20081117 -- Reto Kohli
            case 'Datatrans':
                $return = self::getDatatransForm(Currency::getActiveCurrencyCode());
                break;
            case 'Paypal':
                $return = PayPal::getForm();
                break;
            case 'Dummy':
                $return = Dummy::getForm();
                break;
        }
        // shows the payment picture
        $return .= self::_getPictureCode();
        return $return;
    }


    /**
     * Returns HTML code for showing the logo associated with this
     * payment processor, if any, or an empty string otherwise.
     * @return  string      HTML code, or the empty string
     * @static
     */
    static function _getPictureCode()
    {
        if (!is_array(self::$arrPaymentProcessor)) self::init();
        $imageName = self::getPaymentProcessorPicture();
        if (empty($imageName)) return '';
        return
            '<br /><br /><img src="'.
            SHOP_PAYMENT_LOGO_PATH.$imageName.
            '" alt="" title="" /><br /><br />';
    }


    /**
     * Returns the HTML code for the Saferpay payment method.
     * @return  string  HTML code
     * @static
     */
    static function _SaferpayProcessor($arrCards=array())
    {
        global $_ARRAYLANG;

        $objSaferpay = new Saferpay();
        if (Settings::getStatusByName('saferpay_use_test_account'))
            $objSaferpay->isTest = true;

        $serverBase =
            $_SERVER['SERVER_NAME'].
            (ASCMS_PATH_OFFSET != '' ? '' : '/').
            ASCMS_PATH_OFFSET.
            (ASCMS_PATH_OFFSET != '' ? '/' : '');
        $arrShopOrder = array(
            'AMOUNT'      => str_replace('.', '', $_SESSION['shop']['grand_total_price']),
            'CURRENCY'    => Currency::getActiveCurrencyCode(),
            'ORDERID'     => $_SESSION['shop']['orderid'],
            'ACCOUNTID'   => Settings::getValueByName('saferpay_id'),
            'SUCCESSLINK' => urlencode('http://'.$serverBase.'index.php?section=shop'.MODULE_INDEX.'&cmd=success&result=1&handler=saferpay'),
            'FAILLINK'    => urlencode('http://'.$serverBase.'index.php?section=shop'.MODULE_INDEX.'&cmd=success&result=0&handler=saferpay'),
            'BACKLINK'    => urlencode('http://'.$serverBase.'index.php?section=shop'.MODULE_INDEX.'&cmd=success&result=2&handler=saferpay'),
            'DESCRIPTION' => urlencode('"'.$_ARRAYLANG['TXT_ORDER_NR'].' '.$_SESSION['shop']['orderid'].'"'),
            'LANGID'      => FWLanguage::getLanguageCodeById(FRONTEND_LANG_ID),
            'PROVIDERSET' => $arrCards,
        );

        $payInitUrl = $objSaferpay->payInit($arrShopOrder);
        // Fixed: Added check for empty return string,
        // i.e. on connection problems
        if (   !$payInitUrl
            || strtoupper(substr($payInitUrl, 0, 5)) == 'ERROR') {
            return
                "<font color='red'><b>".
                $_ARRAYLANG['TXT_SHOP_PSP_FAILED_TO_INITIALISE_SAFERPAY'].
                "<br />$payInitUrl</b></font>";
        }
        $return = "<script src='http://www.saferpay.com/OpenSaferpayScript.js'></script>\n";
        switch (Settings::getValueByName('saferpay_window_option')) {
            case 0: // iframe
                return
                    $return.
                    $_ARRAYLANG['TXT_ORDER_PREPARED']."<br/><br/>\n".
                    "<iframe src='$payInitUrl' width='580' height='400' scrolling='no' marginheight='0' marginwidth='0' frameborder='0' name='saferpay'></iframe>\n";
            case 1: // popup
                return
                    $return.
                    $_ARRAYLANG['TXT_ORDER_LINK_PREPARED']."<br/><br/>\n".
                    "<script language='javascript' type='text/javascript'>
                     function openSaferpay()
                     {
                         strUrl = '$payInitUrl';
                         if (strUrl.indexOf(\"WINDOWMODE=Standalone\") == -1) {
                             strUrl += \"&WINDOWMODE=Standalone\";
                         }
                         oWin = window.open(strUrl, 'SaferpayTerminal',
                                       'scrollbars=1,resizable=0,toolbar=0,location=0,directories=0,status=1,menubar=0,width=580,height=400'
                         );
                         if (oWin==null || typeof(oWin)==\"undefined\") {
                             alert(\"The payment couldn't be initialized, because it seems that you are using a popup blocker!\");
                         }
                     }
                     </script>\n".
                    "<input type='button' name='order_now' value='".
                    $_ARRAYLANG['TXT_ORDER_NOW'].
                    "' onclick='openSaferpay()' />\n";
            default:  //case 2: // new window
        }
        return
            $return.
            $_ARRAYLANG['TXT_ORDER_LINK_PREPARED']."<br/><br/>\n".
            "<form method='post' action='$payInitUrl'>\n<input type='Submit' value='".
            $_ARRAYLANG['TXT_ORDER_NOW'].
            "' />\n</form>\n";
    }


    /**
     * Returns the HTML code for the Yellowpay payment method.
     * @return  string  HTML code
     */
    function _YellowpayProcessor()
    {
        global $_ARRAYLANG;

        $language = FWLanguage::getLanguageCodeById(FRONTEND_LANG_ID);
        $language = strtolower($language).'_'.strtoupper($language);
        // Temporary workaround until SettingDb is fully in charge.
        // After that, just do a simple
        //$settings = SettingDb::getArray();
        $settings = array(
            'postfinance_shop_id' => array('value' =>
                Settings::getValueByName('yellowpay_shop_id')),
            'postfinance_hash_signature_in' => array('value' =>
                Settings::getValueByName('yellowpay_hash_signature_in')),
            'postfinance_hash_signature_out' => array('value' =>
                Settings::getValueByName('yellowpay_hash_signature_out')),
            'postfinance_authorization_type' => array('value' =>
                Settings::getValueByName('yellowpay_authorization_type')),
            'postfinance_accepted_payment_methods' => array('value' =>
                Settings::getValueByName('yellowpay_accepted_payment_methods')),
            'postfinance_use_testserver' => array('value' =>
                Settings::getValueByName('yellowpay_use_testserver')),
        );
        $arrShopOrder = array(
            'PSPID'    => $settings['postfinance_shop_id']['value'],
            'ORDERID'  => $_SESSION['shop']['orderid'],
            'AMOUNT'   => intval($_SESSION['shop']['grand_total_price']*100),
            'LANGUAGE' => $language,
            'CURRENCY' => Currency::getActiveCurrencyCode(),
// TODO: Fill these in when available
//            'COM' => '',
//            'CN' => '',
//            'OWNERADDRESS' => '',
//            'OWNERZIP' => '',
//            'OWNERTOWN' => '',
//            'OWNERCTY' => '',
//            'OWNERTELNO' => '',
//            'EMAIL' => '',
        );
        $yellowpayForm = Yellowpay::getForm(
            $arrShopOrder, $_ARRAYLANG['TXT_ORDER_NOW'], false, $settings);
        if (Yellowpay::$arrError) {
            foreach (Yellowpay::$arrError as $error) {
                DBG::log("Yellowpay Error: $error");
            }
            return
                '<font color="red"><b>'.
                $_ARRAYLANG['TXT_SHOP_PSP_FAILED_TO_INITIALISE_YELLOWPAY'].
                '<br /></b></font>';
        }
        return $yellowpayForm;
    }


    /**
     * Returns the complete HTML code for the Datatrans payment form
     *
     * Includes form, input and submit button tags
     * @return  string                        The HTML form code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @version 0.9
     * @since   2.1.0
     */
    static function getDatatransForm()
    {
        global $_ARRAYLANG;

        require_once(ASCMS_MODULE_PATH.'/shop/payments/datatrans/Datatrans.class.php');
        Datatrans::initialize(
            Settings::getValueByName('datatrans_merchant_id'),
            $_SESSION['shop']['orderid'],
            $_SESSION['shop']['grand_total_price'],
            Currency::getActiveCurrencyCode()
        );
        return
            $_ARRAYLANG['TXT_ORDER_LINK_PREPARED'].'<br/><br/>'."\n".
            '<form name="yellowpay" method="post" '.
            'action="'.Datatrans::getGatewayUri().'">'."\n".
            Datatrans::getHtml()."\n".
            '<input type="submit" name="go" value="'.
            $_ARRAYLANG['TXT_ORDER_NOW'].'" />'."\n".
            '</form>'."\n";
    }


    /**
     * Check in the payment processor after the payment is complete.
     * @return  mixed   For external payment methods:
     *                  The integer order ID, if known, upon success
     *                  For internal payment methods:
     *                  Boolean true, in order to make these skip the order
     *                  status update, as this has already been done.
     *                  If the order ID is unknown or upon failure:
     *                  Boolean false
     */
    function checkIn()
    {
        if (   isset($_GET['result'])
            && $_GET['result'] == 0 || $_GET['result'] == 2
        ) return false;
        if (empty($_GET['handler'])) return false;
        switch ($_GET['handler']) {
            case 'saferpay':
                $objSaferpay = new Saferpay();
                $arrShopOrder = array();
// Not used
//                if (Settings::getStatusByName('saferpay_use_test_account')) {
//                    $objSaferpay->isTest = true;
//                } else {
                    $arrShopOrder['ACCOUNTID'] = Settings::getValueByName('saferpay_id');
//                }
                $transaction = $objSaferpay->payConfirm();
                if (Settings::getValueByName('saferpay_finalize_payment')) {
// payComplete() has been fixed to work
//                    if ($objSaferpay->isTest == true) {
//                        $transaction = true;
//                    } else {
                        $transaction = $objSaferpay->payComplete($arrShopOrder);
//                    }
                }
                return $transaction;
            case 'paypal':
                // The order ID must be returned when the payment is done.
                // is this guaranteed to be a GET request?
                if (isset($_REQUEST['orderid']))
                    return intval($_REQUEST['orderid']);
                break;
            case 'yellowpay':
                return Yellowpay::checkin(
                    Settings::getValueByName('yellowpay_hash_signature_out'));
//                    if (Yellowpay::$arrError || Yellowpay::$arrWarning) {
//                        global $_ARRAYLANG;
//                        echo('<font color="red"><b>'.
//                        $_ARRAYLANG['TXT_SHOP_PSP_FAILED_TO_INITIALISE_YELLOWPAY'].
//                        '</b><br />'.
//                        'Errors:<br />'.
//                        join('<br />', Yellowpay::$arrError).
//                        'Warnings:<br />'.
//                        join('<br />', Yellowpay::$arrWarning).
//                        '</font>');
//                    }
            // Added 20081117 -- Reto Kohli
            case 'datatrans':
                require_once(ASCMS_MODULE_PATH.'/shop/payments/datatrans/Datatrans.class.php');
                return Datatrans::validateReturn()
                    && Datatrans::getPaymentResult() == 1;

            // For the remaining types, there's no need to check in, so we
            // return true and jump over the validation of the order ID
            // directly to success!
            // Note: A backup of the order ID is kept in the session
            // for payment methods that do not return it. This is used
            // to cancel orders in all cases where false is returned.
            case 'Internal':
            case 'Internal_CreditCard':
            case 'Internal_Debit':
            case 'Internal_LSV':
                return true;
            // Dummy payment.
            case 'dummy':
                $result = '';
                if (isset($_REQUEST['result']))
                    $result = $_REQUEST['result'];
                // Returns the order ID on success, false otherwise
                return Dummy::commit($result);
            default:
                break;
        }
        // Anything else is wrong.
        return false;
    }


    static function getOrderId()
    {
        if (empty($_GET['handler'])) return false;
        switch ($_GET['handler']) {
            case 'saferpay':
                return Saferpay::getOrderId();

            case 'paypal':
                return (isset($_REQUEST['orderid'])
                    ? intval($_REQUEST['orderid'])
                    : false);

            case 'yellowpay':
                return Yellowpay::getOrderId();

            // Added 20100222 -- Reto Kohli
            case 'mobilesolutions':
                require_once(ASCMS_MODULE_PATH.'/shop/payments/yellowpay/PostfinanceMobile.class.php');
                $order_id = PostfinanceMobile::getOrderId();
                return $order_id;

            // Added 20081117 -- Reto Kohli
            case 'datatrans':
                require_once(ASCMS_MODULE_PATH.'/shop/payments/datatrans/Datatrans.class.php');
                return Datatrans::getOrderId();

            // For the remaining types, there's no need to check in, so we
            // return true and jump over the validation of the order ID
            // directly to success!
            // Note: A backup of the order ID is kept in the session
            // for payment methods that do not return it. This is used
            // to cancel orders in all cases where false is returned.
            case 'Internal':
            case 'Internal_CreditCard':
            case 'Internal_Debit':
            case 'Internal_LSV':
            case 'dummy':
                return (isset($_SESSION['shop']['orderid_checkin'])
                    ? $_SESSION['shop']['orderid_checkin']
                    : false);
        }
        // Anything else is wrong.
        return false;
    }


    static function getMenuoptions($selected_id=0)
    {
        global $_ARRAYLANG;

        if (!is_array(self::$arrPaymentProcessor)) self::init();
        $strMenuoptions =
            (empty($selected_id)
              ? '<option value="" selected="selected">'.
                $_ARRAYLANG['TXT_SHOP_PLEASE_SELECT'].
                "</option>\n"
              : ''
            );
        foreach (array_keys(self::$arrPaymentProcessor) as $id) {
            $strMenuoptions .=
                '<option value="'.$id.'"'.
                ($id == $selected_id ? ' selected="selected"' : '').'>'.
                self::$arrPaymentProcessor[$id]['name'].
                "</option>\n";
        }
        return $strMenuoptions;
    }

}

?>
