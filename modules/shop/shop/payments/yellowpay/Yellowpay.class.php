<?php

/**
 * Class Yellowpay
 *
 * Interface for the payment mask yellowpay
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Currency: Conversion, formatting.
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Currency.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Settings.class.php';
require_once ASCMS_FRAMEWORK_PATH.'/Validator.class.php';

/**
 * Yellowpay plugin for online payment
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  module_shop
 * @internal    Yellowpay must be configured to return with the follwing requests:
 * POST after payment was made:
 *      http://<my>.com/index.php?section=shop&cmd=success&handler=yellowpay&result=-1
 * GET after payment has completed successfully:
 *      http://<my>.com/index.php?section=shop&cmd=success&handler=yellowpay&result=1
 * GET after payment has failed:
 *      http://<my>.com/index.php?section=shop&cmd=success&handler=yellowpay&result=0
 * GET after payment has been cancelled:
 *      http://<my>.com/index.php?section=shop&cmd=success&handler=yellowpay&result=2
 */
class Yellowpay
{
    /**
     * Return string of the function getForm()
     * @access  private
     * @var     string
     * @see     getForm(), addToForm()
     */
    private static $form;

    /**
     * Information that was handed over to the class
     * @access  private
     * @var     array
     * @see     Yellowpay(), __construct(), addPaymentTypeKeys(),
     *          addOtherKeys(), checkKey(), addToForm()
     */
    private static $arrShopOrder = array();

    /**
     * Error messages
     * @access  public
     * @var     array
     * @see     getForm(), checkKey()
     */
    public static $arrError = array();

    /**
     * Warning messages
     * @access  public
     * @var     array
     * @see     addPaymentTypeKeys(), checkKey()
     */
    public static $arrWarning = array();

    /**
     * Language codes
     * @access  private
     * @var     array
     * @see     checkKey()
     */
    private static $arrLangVersion = array(
        'DE' => 2055,
        'US' => 2057, 'EN' => 2057,
        'IT' => 2064,
        'FR' => 4108,
    );

    /**
     * Currency codes
     * @access  private
     * @var     array
     * @see     checkKey()
     */
    private static $arrArtCurrency = array(
        'CHF',
        'USD',
        'EUR',
    );

    /**
     * Known authorization types
     * @access  private
     * @var     array
     * @see     checkKey()
     */
    private static $arrKnownAuthorization = array(
        'RES',
        'SAL',
    );

    /**
     * Current authorization type, defaults to 'immediate'
     * @access  private
     * @var     string
     */
    private static $strAuthorization = false;

    /**
     * Known payment method names
     *
     * Note that these correspond to the names used for
     * dynamically choosing the payment methods using the
     * txtPM_*_Status" parameters and must thus
     * be spelt *exactly* as specified.
     * @var     array
     * @static
     */
    private static $arrKnownPaymentMethod = array(
        'PostFinanceCard',
        'yellownet',
        'Master',
        'Visa',
        'Amex',
        'Diners',
//        'yellowbill',
    );


    /**
     * Accepted payment methods
     *
     * Those not allowed will be unset in the constructor.
     * @access  private
     * @var     array
     * @see     addPaymentTypeKeys()
     */
    private static $arrAcceptedPaymentMethod = array(
        'PostFinanceCard' => array(),
        'yellownet' => array(),
        'Master' => array(),
        'Visa' => array(),
        'Amex' => array(),
        'Diners' => array(),
// Not supported by the shop yet (missing some mandatory fields).
//        'yellowbill' => array(
//            'txtESR_Member',
//            'txtBLastName',
//            'txtBAddr1',
//            'txtBZipCode',
//            'txtBCity',
//        ),
    );


    private static $arrFieldMandatory = array(
        'PSPID',
        'orderID',
        'amount',
        'currency',
        'language',
        // check before the payment: see chapter 6.2
        'SHASign',
        // post payment redirection: see chapter 8.2
        'accepturl',
        'declineurl',
        'exceptionurl',
        'cancelurl',
    );

    private static $arrFieldOptional = array(
        // optional customer details, highly recommended for fraud prevention: see chapter 5.2
        'CN',
        'EMAIL',
        'ownerZIP',
        'owneraddress',
        'ownercty',
        'ownertown',
        'ownertelno',
        'COM',
        // payment methods/page specifics: see chapter 9.1
        'PM',
        'BRAND',
        'WIN3DS',
        'PM list type',
        'PMListType',
        // link to your website: see chapter 8.1
        'homeurl',
        'catalogurl',
        // post payment parameters: see chapter 8.2
        'COMPLUS',
        'PARAMPLUS',
        // post payment parameters: see chapter 8.3
        'PARAMVAR',
        // optional operation field: see chapter 9.2
        'operation',
        // optional extra login field: see chapter 9.3
        'USERID',
        // Alias details: see Alias Management documentation
        'Alias',
        'AliasUsage',
        'AliasOperation',
        'PMLIST',
        'WIN3DS',
        // layout information: see chapter 7.1
        'TITLE',
        'BGCOLOR',
        'TXTCOLOR',
        'TBLBGCOLOR',
        'TBLTXTCOLOR',
        'BUTTONBGCOLOR',
        'BUTTONTXTCOLOR',
        'LOGO',
        'FONTTYPE',
        // dynamic template page: see chapter 7.2
        'TP',
    );

    /**
     * Mandatory fields required to confirm the SHA-1-OUT hash validity
     * @var   array
     */
    private static $arrFieldShaOut = array(
        'orderID',
        'currency',
        'amount',
        'PM',
        'ACCEPTANCE',
        'STATUS',
        'CARDNO',
        'PAYID',
        'NCERROR',
        'BRAND',
    );


    /**
     * Initializes class
     *
     * Sets up the accepted payment methods according to the settings
     */
    static function init()
    {
        $strAcceptedPaymentMethods =
            Settings::getValueByName('yellowpay_accepted_payment_methods');
        // There needs to be at least one accepted payment method,
        // if there is none, accept all.
        if (!empty($strAcceptedPaymentMethods)) {
            foreach (Yellowpay::$arrKnownPaymentMethod as $strPaymentMethod) {
                // Remove payment methods not mentioned
                if (!preg_match("/$strPaymentMethod/", $strAcceptedPaymentMethods)) {
                    unset(self::$arrAcceptedPaymentMethod[$strPaymentMethod]);
                }
            }
        }
    }


    /**
     * Creates and returns the HTML-Form for requesting the yellowpay-service.
     *
     * @access  public
     * @return  string    The HTML-Form
     * @see     addRequiredKeys(), addPaymentTypeKeys(), addOtherKeys()
     */
    static function getForm(
        $arrShopOrder, $submitValue='send', $autopost=false
    ) {
        global $_ARRAYLANG;

        self::init();
        self::$arrShopOrder = $arrShopOrder;
        // Build the base URI from the referrer, which also includes the
        // protocol (http:// or https://)
        $base_uri = $_SERVER['HTTP_REFERER'];
        $match = array();
        if (preg_match('/^(.+section=shop)/', $base_uri, $match)) {
            $base_uri = $match[1];
        } else {
            self::$arrError[] = 'Failed to determine base URI: '.$base_uri;
            return '';
        }
        $base_uri = $base_uri.'&cmd=success&handler=yellowpay&result=';
        if (empty(self::$arrShopOrder['accepturl'])) {
            self::$arrShopOrder['accepturl'] = $base_uri.'1';
        }
        if (empty(self::$arrShopOrder['declineurl'])) {
            self::$arrShopOrder['declineurl'] = $base_uri.'2';
        }
        if (empty(self::$arrShopOrder['exceptionurl'])) {
            self::$arrShopOrder['exceptionurl'] = $base_uri.'2';
        }
        if (empty(self::$arrShopOrder['cancelurl'])) {
            self::$arrShopOrder['cancelurl'] = $base_uri.'0';
        }
        self::$form =
            $_ARRAYLANG['TXT_ORDER_LINK_PREPARED']."<br/><br/>\n".
            // The real yellowpay server or the test server
            '<form name="yellowpay" method="post" '.
// OLD yellowpay URI
//            'action="https://yellowpay'.($isTest ? 'test' : '').
//            '.postfinance.ch/checkout/Yellowpay.aspx?userctrl=Invisible"'.
// CURRENT Postfinance E-Commerce URI
            'action="https://e-payment.postfinance.ch/ncol/'.
            (Settings::getValueByName('yellowpay_use_testserver') ? 'test' : 'prod').
            '/orderstandard.asp"'.
            ">\n";
/*
            // Yellowpay dummy
            '<form name="yellowpay" method="post" '.
            'action="http://localhost/c_trunk/modules/shop/payments/yellowpay/YellowpayDummy.class.php"'.
            ">\n";
*/
        if (!self::addHash()) {
            self::$arrError[] = 'ERROR: Failed to compute hash';
            return false;
        }
        if (!self::verifyKeys()) {
            self::$arrError[] = 'ERROR: Failed to verify keys';
            return false;
        }
        if (!self::addKeys()) {
            self::$arrError[] = 'ERROR: Failed to add keys';
            return false;
        }
        if ($autopost) {
            self::$form .=
                '<script type="text/javascript">/* <![CDATA[ */ '.
                'document.yellowpay.submit(); '.
                '/* ]]> */</script>';
        } else {
            self::$form .=
                '<input type="submit" name="go" value="'.$submitValue."\" />\n";
        }
        self::$form .= "</form>";
//self::$arrError[] = "Test for error handling";
        return self::$form;
    }


    private static function verifyKeys()
    {
        foreach (self::$arrFieldMandatory as $key) {
            if (empty(self::$arrShopOrder[$key])) {
                self::$arrError[] = "Missing mandatory key '$key'";
            }
        }
        return empty(self::$arrError);
    }


    /**
     * Enter description here...
     *
     * Concatenates the values of the fields
     *  orderID, amount, currency, PSPID
     * plus the secret taken from the 'yellowpay_hash_seed' setting
     * and computes the SHA1 hash.
     * Fails if one or more of the needed values are empty.
     * @return  boolean         True on success, false otherwise
     */
    private static function addHash()
    {
        $seed = Settings::getValueByName('yellowpay_hash_signature_in');
        if (   empty(self::$arrShopOrder['orderID'])
            || empty(self::$arrShopOrder['amount'])
            || empty(self::$arrShopOrder['currency'])
            || empty(self::$arrShopOrder['PSPID'])
            || empty($seed)) {
            self::$arrError[] = 'Missing mandatory parameter for computing the hash';
            return false;
        }
        $hash_string =
            self::$arrShopOrder['orderID'].
            self::$arrShopOrder['amount'].
            self::$arrShopOrder['currency'].
            self::$arrShopOrder['PSPID'].
            $seed;
        self::$arrShopOrder['SHASign'] = sha1($hash_string);
        return true;
    }


    /**
     * Sets all accepted fields from the order array
     * @access  private
     * @see     checkKey()
     */
    static function addKeys()
    {
        foreach (array_keys(self::$arrShopOrder) as $key) {
            if (!self::addToForm($key)) return false;
        }
        return true;
    }


    /**
     * Adds a key to the HTML form and removes it from the array arrShopOrder.
     *
     * Verifies any key/value pair and only adds valid parameters.
     * @param   string    $key    Key to be added to the HTML form
     * @return  boolean           True on success, false otherwise
     */
    static function addToForm($key)
    {
        $value = self::checkKey($key);
        if ($value === false) return false;
        self::$form .=
            "<input type='hidden' name='$key' value='".
            htmlspecialchars($value)."' />\n";
        return true;
    }


    /**
     * Verifies a key/value pair
     * @access  private
     * @param   string    $key    Key to check
     * @return  boolean           True if both key and value are valid,
     *                            false otherwise
     * @see     addToForm()
     */
    static function checkKey($key)
    {
        if (!array_key_exists($key, self::$arrShopOrder)) {
            self::$arrError[] = "Missing key '$key'!";
            return false;
        }
        $value = self::$arrShopOrder[$key];
        // This one *MUST NOT* be used a second time
        unset(self::$arrShopOrder[$key]);
        switch ($key) {
            // Mandatory
            case 'orderID':
                if (intval($value)) return intval($value);
                break;
            case 'amount':
                if ($value === intval($value)) return $value;
                break;
            case 'currency':
                if (preg_match('/^\w{3}$/', $value)) return $value;
                break;
            case 'PSPID':
                if (preg_match('/.+/', $value)) return $value;
                break;
            // The above four are needed to form the hash:
            case 'SHASign':
                // 40 digit hexadecimal string, like
                // 4d0a445beac3561528dc26023e9ecb2d38fadc61
                if (preg_match('/^[0-9a-z]{40}$/i', $value)) return $value;
            case 'language':
                if (preg_match('/^\w{2}(?:_\w{2})?$/', $value)) return $value;
                break;
            case 'accepturl':
            case 'declineurl':
            case 'exceptionurl':
            case 'cancelurl':
//                if (FWValidator::isUri($value)) return $value;
// *SHOULD* verify the URIs, but the expression is not fit
                if ($value) return $value;
                break;
            // Optional
            // optional customer details, highly recommended for fraud prevention: see chapter 5.2
            case 'CN':
            case 'owneraddress':
            case 'ownercty':
            case 'ownerZIP':
            case 'ownertown':
            case 'ownertelno':
            case 'COM':
                if (preg_match('/.*/', $value)) return $value;
                break;
            case 'EMAIL':
                if (isEmail($value)) return $value;
                break;
            case 'PMLIST':
                if (preg_match('/.*/', $value)) return $value;
                break;
            case 'WIN3DS':
                if ($value == 'MAINW' || $value = 'POPUP') return $value;
                break;
            // post payment parameters: see chapter 8.2
            case 'COMPLUS':
                if (preg_match('/.*/', $value)) return $value;
                break;
            case 'PARAMPLUS':
                if (preg_match('/.*/', $value)) return $value;
                break;
            // post payment parameters: see chapter 8.3
            case 'PARAMVAR':
                if (preg_match('/.*/', $value)) return $value;
                break;
            // optional operation field: see chapter 9.2
            case 'operation':
                if ($value == 'RES' || $value == 'SAL') return $value;
                break;
            // layout information: see chapter 7.1
            case 'TITLE':
            case 'BGCOLOR':
            case 'TXTCOLOR':
            case 'TBLBGCOLOR':
            case 'TBLTXTCOLOR':
            case 'BUTTONBGCOLOR':
            case 'BUTTONTXTCOLOR':
            case 'LOGO':
            case 'FONTTYPE':
            // dynamic template page: see chapter 7.2
            case 'TP':
                if (preg_match('/.+/', $value)) return $value;
                break;

            // Contrexx does neither supply nor support the following:
            //
            // payment methods/page specifics: see chapter 9.1
            case 'PM':
            case 'BRAND':
            case 'PM list type':
            case 'PMListType':
            // link to your website: see chapter 8.1
            case 'homeurl':
            case 'catalogurl':
            // optional extra login field: see chapter 9.3
            case 'USERID':
            // Alias details: see Alias Management documentation
            case 'Alias':
            case 'AliasUsage':
            case 'AliasOperation':
                break;
        }
        self::$arrError[] = "Invalid field '$key', value '$value'";
        return false;
    }


    /**
     * Verifies the parameters posted back by e-commerce
     * @return  boolean           True on success, false otherwise
     */
    static function checkIn()
    {
        // If the hash is correct, so is the order ID
        return self::checkHash();
    }


    /**
     * Returns the Order ID from the GET request, if present
     * @return  integer           The order ID, or false
     */
    static function getOrderId()
    {
        if (isset($_POST['txtOrderIDShop']))
            return $_POST['txtOrderIDShop'];
        if (isset($_GET['orderID']))
            return $_GET['orderID'];
        return false;
    }


    /**
     * Validates the hash returned by the PSP
     *
     * Returns true both if it is correct and if it's missing, as this is not
     * a mandatory feature.
     * If the hash is present and wrong, however, false is returned.
     * The fields used for the SHA-OUT value are -- in this order:
     * orderID, currency, amount, PM, ACCEPTANCE, STATUS,
     * CARDNO, PAYID, NCERROR, BRAND
     * @return  boolean         True on success, false otherwise
     */
    static function checkHash()
    {
        if (empty($_GET['SHASIGN'])) {
            self::$arrWarning[] = 'No SHASIGN value in request';
            return true;
        }
        $hash_string = '';
        foreach (self::$arrFieldShaOut as $key) {
            // This means failure!
            if (!isset($_GET[$key])) {
                continue;
            }
            $hash_string .= $_GET[$key];
        }
        $hash_string .= Settings::getValueByName('yellowpay_hash_signature_out');
        $hash = strtoupper(sha1($hash_string));
        if ($_GET['SHASIGN'] == $hash) {
            return true;
        }
        self::$arrError[] = 'Invalid SHASIGN value in request';
        return false;
    }


    /**
     * Returns the array with all currently accepted payment methods.
     *
     * Note: This is still under development.
     * The contents of this array directly depend on the list of
     * accepted payment methods specified when calling the constructor.
     * @return  array         The payment type name strings
     */
    static function getAcceptedPaymentMethods()
    {
        self::init();
        return array_keys(self::$arrAcceptedPaymentMethod);
    }


    /**
     * Returns the HTML menu options for selecting from the currently accepted
     * payment methods.
     * @param   string    $strSelected    The optional preselected payment
     *                                    method name
     * @return  string                    The HTML menu options
     */
    static function getAcceptedPaymentMethodMenuOptions($strSelected='')
    {
        global $_ARRAYLANG;

        self::init();
        $strOptions = '';
        foreach (array_keys(self::$arrAcceptedPaymentMethod)
                  as $strPaymentMethod) {
            $strOptions .=
                '<option value="'.$strPaymentMethod.'"'.
                ($strPaymentMethod == $strSelected
                    ? ' selected="selected"' : ''
                ).'>'.
                $_ARRAYLANG['TXT_SHOP_YELLOWPAY_'.strtoupper($strPaymentMethod)].
                '</option>';
        }
        return $strOptions;
    }


    /**
     * Returns the HTML checkboxes for selecting zero or more from the known
     * payment methods.
     * @return  string        The HTML checkboxes
     */
    static function getKnownPaymentMethodCheckboxes()
    {
        global $_ARRAYLANG;

        self::init();
        $strOptions = '';
        foreach (Yellowpay::$arrKnownPaymentMethod as $index => $strPaymentMethod) {
            $strOptions .=
                '<input name="yellowpay_accepted_payment_methods[]" '.
                'id="yellowpay_pm_'.$index.'" type="checkbox" '.
                (in_array($strPaymentMethod, array_keys(self::$arrAcceptedPaymentMethod))
                    ? 'checked="checked" ' : ''
                ).
                'value="'.$strPaymentMethod.'" />'.
                '<label for="yellowpay_pm_'.$index.'">&nbsp;'.
                $_ARRAYLANG['TXT_SHOP_YELLOWPAY_'.strtoupper($strPaymentMethod)].
                '</label><br />';
        }
        return $strOptions;
    }


    static function getAuthorizationMenuoptions()
    {
        global $_ARRAYLANG;

        self::$strAuthorization =
            Settings::getValueByName('yellowpay_authorization_type');
        return
            '<option value="SAL"'.
            (self::$strAuthorization == 'SAL' ? ' selected="selected"' : '').'>'.
            $_ARRAYLANG['TXT_SHOP_YELLOWPAY_REQUEST_FOR_SALE'].
            '</option>'.
            '<option value="RES"'.
            (self::$strAuthorization == 'RES' ? ' selected="selected"' : '').'>'.
            $_ARRAYLANG['TXT_SHOP_YELLOWPAY_REQUEST_FOR_AUTHORIZATION'].
            '</option>';
    }

}

?>
