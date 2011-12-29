<?php

/**
 * PostFinance online payment
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  module_shop
 */

/**
 * Currency: Conversion, formatting.
 */
//require_once ASCMS_MODULE_PATH.'/shop/lib/Currency.class.php';
//require_once ASCMS_MODULE_PATH.'/shop/lib/Settings.class.php';
require_once ASCMS_CORE_PATH.'/SettingDb.class.php';
require_once ASCMS_FRAMEWORK_PATH.'/Validator.class.php';

/**
 * PostFinance online payment
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @author      Reto Kohli <reto.kohli@comvation.com>
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
     * @see     getForm()
     */
    private static $form;

    /**
     * Field name/value pairs with Order and payment information
     * @access  private
     * @var     array
     * @see     Yellowpay(), __construct(), addPaymentTypeKeys(),
     *          addOtherKeys(), verifyParameter()
     */
    private static $arrField = array();

    /**
     * Error messages
     * @access  public
     * @var     array
     * @see     getForm(), verifyParameter()
     */
    public static $arrError = array();

    /**
     * Warning messages
     * @access  public
     * @var     array
     * @see     addPaymentTypeKeys(), verifyParameter()
     */
    public static $arrWarning = array();

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
        'ORDERID',
        'AMOUNT',
        'CURRENCY',
        // Not mandatory, but needed for SHA-1 anyway
        'OPERATION',
        // The following  parameters are not mandatory, but we're being nice to customers
        'LANGUAGE',
        // post payment redirection: see chapter 8.2
        'ACCEPTURL',
        'DECLINEURL',
        'EXCEPTIONURL',
        'CANCELURL',
    );


    /**
     * Creates and returns the HTML Form for requesting the payment service.
     * @access  public
     * @return  string                The HTML Form code
     * @see     addRequiredKeys(), addPaymentTypeKeys(), addOtherKeys()
     */
    static function getForm(
        $arrField, $submitValue='send', $autopost=false
    ) {
        global $_ARRAYLANG;

        $strAcceptedPaymentMethods =
            SettingDb::getValue('postfinance_accepted_payment_methods');
        if (empty ($arrField['OPERATION'])) {
        self::$strAuthorization =
            SettingDb::getValue('postfinance_authorization_type');
            $arrField['OPERATION'] = self::$strAuthorization;
        }
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
        if (empty(self::$arrField['ACCEPTURL'])) {
            $arrField['ACCEPTURL'] = $base_uri.'1';
        }
        if (empty(self::$arrField['DECLINEURL'])) {
            $arrField['DECLINEURL'] = $base_uri.'2';
        }
        if (empty(self::$arrField['EXCEPTIONURL'])) {
            $arrField['EXCEPTIONURL'] = $base_uri.'2';
        }
        if (empty(self::$arrField['CANCELURL'])) {
            $arrField['CANCELURL'] = $base_uri.'0';
        }
        self::$form =
            $_ARRAYLANG['TXT_ORDER_LINK_PREPARED']."<br/><br/>\n".
            // The real yellowpay server or the test server
            '<form name="yellowpay" method="post" '.
            // Yellowpay dummy, for testing only
            //'action="modules/shop/payments/yellowpay/YellowpayDummy.class.php"'.
            // Current Postfinance E-Commerce URI, as of 2011
            'action="https://e-payment.postfinance.ch/ncol/'.
            (SettingDb::getValue('postfinance_use_testserver') ? 'test' : 'prod').
            '/orderstandard.asp"'.
            ">\n";
        if (!self::setFields($arrField)) {
            self::$arrError[] = 'ERROR: Failed to verify keys';
            return false;
        }
        self::$arrField['SHASIGN'] = self::signature(self::$arrField);
        foreach (self::$arrField as $name => $value) {
            self::$form .= Html::getHidden($name, $value)."\n";
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


    /**
     * Sets the parameters with name/value pairs from the given array
     *
     * If $arrField is missing mandatory fields, or contains invalid values,
     * fails.
     * @param   array     $arrField     The data array
     * @return  boolean                 True on success, false otherwise
     */
    static function setFields($arrField=null)
    {
        self::$arrField = array();
        if (empty($arrField)) {
//DBG::log("Yellowpay::setFields(): Empty field array");
            return false;
        }
//die("Field array: ".var_export($arrField, true));
        foreach (self::$arrFieldMandatory as $name) {
            if (empty($arrField[$name])) {
//DBG::log("Yellowpay::setFields(): Missing mandatory name '$name'");
                self::$arrError[] = "Missing mandatory name '$name'";
                return false;
            }
        }
        foreach ($arrField as $name => $value) {
            $name = strtoupper($name);
            if (!self::addField($name, $value)) {
//DBG::log("Yellowpay::setFields(): Failed to add '$name' (value '$value')");
                return false;
            }
        }
        return true;
    }


    /**
     * Verifies a name/value pair and adds valid ones.
     *
     * Fails on any invalid parameter.
     * @return  boolean           True if both the field name and value are
     *                            valid, false otherwise
     */
    static function addField($name, $value)
    {
        $value = self::verifyParameter($name, $value);
        if ($value === null) {
            self::$arrError[] = "Invalid value '$value' for name '$name'";
            return false;
        }
        self::$arrField[$name] = $value;
        return true;
    }


    /**
     * Verifies a name/value pair
     *
     * May change the value before returning it.
     * Use the value returned when adding to the form in any case.
     * @access  private
     * @param   string    $name     The name of the parameter
     * @param   string    $value    The value of the parameter
     * @return  boolean             The verified value on success,
     *                              null otherwise
     */
    static function verifyParameter($name, $value)
    {
        switch ($name) {
            // Mandatory
            case 'ORDERID':
                if (intval($value)) return intval($value);
                break;
            case 'AMOUNT':
                // Fix cents, like "1.23" to "123"
                if (preg_match('/\./', $value)) {
                    $value = intval($value * 100);
                }
                if ($value === intval($value)) return $value;
                break;
            case 'CURRENCY':
                if (preg_match('/^\w{3}$/', $value)) return $value;
                break;
            case 'PSPID':
                if (preg_match('/.+/', $value)) return $value;
                break;
            // The above four are needed to form the hash:
            case 'SHASIGN':
                // 40 digit hexadecimal string, like
                // 4d0a445beac3561528dc26023e9ecb2d38fadc61
                if (preg_match('/^[0-9a-z]{40}$/i', $value)) return $value;
            case 'LANGUAGE':
                if (preg_match('/^\w{2}(?:_\w{2})?$/', $value)) return $value;
                break;
            case 'OPERATION':
                if ($value == 'RES' || $value == 'SAL') return $value;
                break;
            case 'ACCEPTURL':
            case 'DECLINEURL':
            case 'EXCEPTIONURL':
            case 'CANCELURL':
//                if (FWValidator::isUri($value)) return $value;
// *SHOULD* verify the URIs, but the expression is not fit
                if ($value) return $value;
                break;
            // Optional
            // optional customer details, highly recommended for fraud prevention: see chapter 5.2
            case 'CN':
            case 'OWNERADDRESS':
            case 'OWNERCTY':
            case 'OWNERZIP':
            case 'OWNERTOWN':
            case 'OWNERTELNO':
            case 'COM':
                if (preg_match('/.*/', $value)) return $value;
                break;
            case 'EMAIL':
                if (FWValidator::isEmail($value)) return $value;
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

            // Contrexx does not yet supply nor support the following:
            // payment methods/page specifics: see chapter 9.1
            case 'PM':
            case 'BRAND':
            case 'PMLISTTYPE':
            // link to your website: see chapter 8.1
            case 'HOMEURL':
            case 'CATALOGURL':
            // optional extra login field: see chapter 9.3
            case 'USERID':
            // Alias details: see Alias Management documentation
            case 'ALIAS':
            case 'ALIASUSAGE':
            case 'ALIASOPERATION':
                break;
        }
        self::$arrError[] = "Invalid field '$name', value '$value'";
        return null;
    }


    /**
     * Returns the current SHA signature
     *
     * Concatenates the values of all fields, separating them with the secret
     * passphrase (in or out).
     * @param   array     $fields   The parameter array
     * @param   boolean   $out  Use the 'out' passphrase if true.
     *                          Defaults to false (for 'in').
     * @return  string              The signature hash on success,
     *                              null otherwise
     */
    static function signature($fields, $out=false)
    {
        $hash_string = self::concatenateFields($fields, $out);
        return strtoupper(sha1($hash_string));
    }


    /**
     * Returns a string formed by concatenating all fields
     *
     * Name/value pairs are separated by an equals sign, and individual pairs
     * separated by the passphrase (in or out).
     * Mind that according to the new specification, all field names must be
     * all uppercase, thus the array is reindexed using uppercase only keys
     * before it is sorted and concatenated.
     * @todo    Currently, all fields present in the $fields array are added
     *          to the string with SHASIGN being the only exception that is
     *          skipped.  It will probably be necessary to exclude further
     *          keys that are not used for computing the hash.
     * @param   array     $fields   The parameter array
     * @param   boolean   $out  Use the 'out' passphrase if true.
     *                          Defaults to false (for 'in').
     * @return  string              The signature string on success,
     *                              null otherwise
     */
    static function concatenateFields($fields, $out=false)
    {
        $passphrase = SettingDb::getValue('postfinance_hash_signature_'.
            ($out ? 'out' : 'in'));
        $hash_string = '';
        foreach ($fields as $name => $value) {
            unset($fields[$name]);
            $name = strtoupper($name);
            $fields[$name] = $value;
        }
        ksort($fields);
        foreach ($fields as $name => $value) {
            if ($name == 'SHASIGN') {
//DBG::log("Skipping SHASIGN");
                continue;
            }
            if ($value === '') {
                continue;
//DBG::log("Skipping empty string value");
            }
//DBG::log("Adding $name={$fields[$name]}");
            $hash_string .=
                $name.
                '='.
                $fields[$name].
                $passphrase;
        }
        return $hash_string;
    }


    /**
     * Verifies the parameters posted back by e-commerce
     * @return  boolean           True on success, false otherwise
     */
    static function checkIn()
    {
//DBG::activate(DBG_LOG_FILE);
//DBG::log("Yellowpay::checkIn(): POST: ".var_export($_POST, true));
//DBG::log("Yellowpay::checkIn(): GET: ".var_export($_GET, true));
        if (empty($_POST['SHASIGN'])) {
            self::$arrError[] = 'No SHASIGN value in request';
            return false;
        }
        self::$arrField = contrexx_input2raw($_POST);
        $shasign_request = self::$arrField['SHASIGN'];
        // If the hash is correct, so is the Order (and ID)
        $shasign_computed = self::signature(self::$arrField, true);
//DBG::log("Yellowpay::checkIn(): SHA Request $shasign_request <> $shasign_computed ?");
        return ($shasign_request == $shasign_computed);
    }


    /**
     * Returns the Order ID from the POST request, if present
     * @return  integer           The order ID, or false
     */
    static function getOrderId()
    {
        if (isset($_POST['orderID'])) return $_POST['orderID'];
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
