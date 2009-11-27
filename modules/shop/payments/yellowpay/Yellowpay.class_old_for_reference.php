<?php

/**
 * Class Yellowpay
 *
 * Interface for the payment mask yellowpay
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Currency: Conversion, formatting.
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Currency.class.php';

/**
 * Yellowpay plugin for online payment
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 * @todo    Yellowpay must be configured and this code rewritten to return and
 * handle the follwing requests:
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
    private $form;

    /**
     * Enable test mode if true
     * @access  private
     * @var     boolean
     * @see     getForm()
     */
    private $is_test = true;

    /**
     * Information that was handed over to the class
     * @access  private
     * @var     array
     * @see     Yellowpay(), __construct(), addPaymentTypeKeys(),
     *          addOtherKeys(), checkKey(), addToForm()
     */
    private $arrShopOrder = array();

    /**
     * Error messages
     * @access  public
     * @var     array
     * @see     getForm(), checkKey()
     */
    public $arrError = array();

    /**
     * Warning messages
     * @access  public
     * @var     array
     * @see     addPaymentTypeKeys(), checkKey()
     */
    public $arrWarning = array();

    /**
     * Language codes
     * @access  private
     * @var     array
     * @see     checkKey()
     */
    private $arrLangVersion = array(
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
    private $arrArtCurrency = array(
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
        'immediate',
        'deferred',
    );

    /**
     * Current authorization type, defaults to 'immediate'
     * @access  private
     * @var     string
     */
    private $strAuthorization = 'immediate';

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
    private $arrAcceptedPaymentMethod = array(
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


    /**
     * Constructor
     *
     * The optional $strAcceptedPaymentMethods argument lets you restrict
     * the payment methods to be available.  If the string is empty, any
     * known methods will be accepted, however.
     * @param   string  $strAcceptedPaymentMethods  The comma separated list
     *                                              of payment methods
     * @return  Yellowpay                           The Yellowpay object
     */
    function __construct($strAcceptedPaymentMethods='', $strAuthorization='')
    {
        // There needs to be at least one accepted payment method,
        // if there is none, accept all.
        if (!empty($strAcceptedPaymentMethods)) {
            foreach (Yellowpay::$arrKnownPaymentMethod as $strPaymentMethod) {
                // Remove payment methods not mentioned
                if (!preg_match("/$strPaymentMethod/", $strAcceptedPaymentMethods)) {
                    unset($this->arrAcceptedPaymentMethod[$strPaymentMethod]);
                }
            }
//        } else {
        }
        if (in_array($strAuthorization, Yellowpay::$arrKnownAuthorization)) {
            $this->strAuthorization = $strAuthorization;
        }
    }


    /**
     * Creates and returns the HTML-Form for requesting the yellowpay-service.
     *
     * @access  public
     * @return  string    The HTML-Form
     * @see     addRequiredKeys(), addPaymentTypeKeys(), addOtherKeys()
     */
    function getForm(
        $arrShopOrder, $submitValue='send', $autopost=false, $isTest=false
    ) {
        $this->arrShopOrder = $arrShopOrder;
        $this->form =
            // The real yellowpay server or the test server
            '<form name="yellowpay" method="post" '.
            'action="https://yellowpay'.
            ($isTest ? 'test' : '').
            '.postfinance.ch/checkout/Yellowpay.aspx?userctrl=Invisible"'.
            ">\n";
/*
            // Yellowpay dummy
            '<form name="yellowpay" method="post" '.
            'action="http://localhost/c_trunk/modules/shop/payments/yellowpay/YellowpayDummy.class.php"'.
            ">\n";
*/
        $this->addRequiredKeys();
        $this->addPaymentTypeKeys();
        $this->addOtherKeys();
        if ($autopost) {
            $this->form .=
                '<script type="text/javascript">/* <![CDATA[ */ '.
                'document.yellowpay.submit(); '.
                '/* ]]> */</script>';
        } else {
            $this->form .=
                '<input type="submit" name="go" value="'.$submitValue."\" />\n";
        }
        $this->form .= "</form>";
//$this->arrError[] = "Test for error handling";
        return $this->form;
    }


    /**
     * Checks if all head keys were set correctly.
     *
     * @access  private
     * @see     checkKey()
     */
    function addRequiredKeys()
    {
        $this->addHash();
        $this->addToForm('txtShopId');
        $this->addToForm('txtLangVersion');
        $this->addToForm('txtOrderTotal');
        $this->addToForm('txtArtCurrency');
    }


    /**
     * Check payment keys
     *
     * Checks if all keys for the payment type were set correctly.
     *
     * @access  private
     * @see     addToForm(), checkKey()
     */
    function addPaymentTypeKeys()
    {
        // Skip this if no payment methods are specified.
        if (!isset($this->arrShopOrder['acceptedPaymentMethods'])) {
            $this->arrError[] = "Missing accepted payment methods";
            return;
        }
        $arrAcceptedPM = explode(',', $this->arrShopOrder['acceptedPaymentMethods']);
        // Remove list of accepted payment methods
        unset($this->arrShopOrder['acceptedPaymentMethods']);
        if (empty($arrAcceptedPM)) {
            $this->arrError[] = "Failed to decode accepted payment methods";
            return;
        }

        foreach ($arrAcceptedPM as $strPM) {
            if (array_key_exists($strPM, $this->arrAcceptedPaymentMethod)) {
                $this->arrShopOrder["txtPM_{$strPM}_Status"] = 'true';
                $this->addToForm("txtPM_{$strPM}_Status");
            } else {
                $this->arrError[] = "Payment type '$strPM' is disabled or unknown.";
                return;
            }
        }
        // Enable dynamic payment method selection
        $this->arrShopOrder['txtUseDynPM'] = 'true';
        $this->addToForm('txtUseDynPM');
    }


    /**
     * Returns the array with all currently accepted payment methods.
     *
     * Note: This is still under development.
     * The contents of this array directly depend on the list of
     * accepted payment methods specified when calling the constructor.
     * @return  array         The payment type name strings
     */
    function getAcceptedPaymentMethods()
    {
        return array_keys($this->arrAcceptedPaymentMethod);
    }


    /**
     * Check optional keys
     *
     * Checks if all other (optional) keys were set correctly.
     *
     * @access  private
     * @see     checkKey()
     */
    function addOtherKeys()
    {
        unset($this->arrShopOrder['txtShopId']);
        unset($this->arrShopOrder['Hash_seed']);
        foreach (array_keys($this->arrShopOrder) as $key) {
            $this->addToForm($key);
        }
    }


    /**
     * Generates the txtHash hash key and adds it to the HTML form
     *
     * @return  void
     */
    function addHash()
    {
        if (empty($this->arrShopOrder['txtShopId'])) {
            $this->arrError[] = "Missing the txtShopId parameter";
        }
        if ($this->arrShopOrder['Hash_seed'] == '') {
            $this->arrError[] = "Missing the Hash_seed parameter";
        }
        $this->arrShopOrder['txtHash'] = md5(
            $this->arrShopOrder['txtShopId'].
            $this->arrShopOrder['txtArtCurrency'].
            $this->arrShopOrder['txtOrderTotal'].
            $this->arrShopOrder['Hash_seed']
        );
        $this->addToForm('txtHash');
    }


    /**
     * Adds a key to the HTML form and removes it from the array arrShopOrder.
     *
     * Verifies any key/value pair and only adds valid parameters.
     * @param   string    $key    Key to be added to the HTML form
     * @return  boolean           True on success, false otherwise
     */
    function addToForm($key)
    {
        if ($this->checkKey($key)) {
            $this->form .= "<input type='hidden' name='$key' value='{$this->arrShopOrder[$key]}' />\n";
            unset($this->arrShopOrder[$key]);
            return true;
        }
        return false;
    }


    /**
     * Verifies a key/value pair.
     *
     * @access  private
     * @param   string    $key    Key to check
     * @return  boolean           True if both key and value are valid,
     *                            false otherwise
     * @see     addToForm()
     */
    function checkKey($key)
    {
        if (!array_key_exists($key, $this->arrShopOrder)) {
            $this->arrError[] = "Missing mandatory key '$key'!";
            return false;
        }
        switch ($key) {
            case 'txtShopId':
                if (empty($this->arrShopOrder[$key])) {
                    $this->arrError[] = "$key isn't valid.";
                    return false;
                }
                if (strlen($this->arrShopOrder[$key]) > 30) {
                    $this->arrShopOrder[$key] = substr($this->arrShopOrder[$key], 0, 30);
                    $this->arrWarning[] = "$key was cut to 30 characters.";
                }
                break;
            case 'txtLangVersion':
                if (array_key_exists(strtoupper($this->arrShopOrder[$key]), $this->arrLangVersion)) {
                    $this->arrShopOrder[$key] = $this->arrLangVersion[strtoupper($this->arrShopOrder[$key])];
                } else {
                    $this->arrShopOrder[$key] = $this->arrLangVersion['US'];
                    $this->arrWarning[] = "$key was set to US";
                }
                break;
            case 'txtOrderTotal':
                if (   empty($this->arrShopOrder[$key])
                    || $this->arrShopOrder[$key] <= 0) {
                    $this->arrError[] = "$key is missing or invalid.";
                    return false;
                }
                if (!ereg('^[0-9]+\.[0-9][0-9]$', $this->arrShopOrder[$key])) {
                    $this->arrShopOrder[$key] = Currency::formatPrice($this->arrShopOrder[$key]);
                    $this->arrWarning[] = "$key was reformatted to ".$this->arrShopOrder[$key];
                }
                break;
            case 'txtArtCurrency':
                if (empty($this->arrShopOrder[$key])) {
                    $this->arrError[] = "$key is missing.";
                    return false;
                }
                $this->arrShopOrder[$key] = strtoupper($this->arrShopOrder[$key]);
                if (!in_array($this->arrShopOrder[$key], $this->arrArtCurrency)) {
                    $this->arrShopOrder[$key] = $this->arrArtCurrency[0];
                    $this->arrWarning[] = "$key was set to ".$this->arrArtCurrency[0];
                }
                break;
            case 'txtOrderIDShop':
                if (strlen($this->arrShopOrder[$key]) > 18) {
                    $this->arrShopOrder[$key] = substr($this->arrShopOrder[$key], 0, 18);
                    $this->arrWarning[] = "$key was cut to 18 characters.";
                }
                break;
            case 'deliveryPaymentType':
                if (!in_array($this->arrShopOrder[$key], Yellowpay::$arrKnownAuthorization)) {
                    $this->arrShopOrder[$key] = Yellowpay::$arrKnownAuthorization['1'];
                    $this->arrWarning[] = "$key was set to '".Yellowpay::$arrKnownAuthorization['1']."'.";
                }
                break;
// Note:  Mandatory for the yellowbill payment method
            case 'txtESR_Member':
                if (!ereg('^[0-9]{1,2}-[0-9]{1,6}-[0-9]$', $this->arrShopOrder[$key])) {
                    $this->arrError[] = "$key isn't valid.";
                    return false;
                }
                break;
// Note:  Mandatory for the yellowbill payment method
            case 'txtESR_Ref':
                if (!strlen($this->arrShopOrder[$key]) == 16 or !strlen($this->arrShopOrder[$key]) == 27) {
                    $this->arrWarning[] = "$key isn't valid.";
                }
                break;
            case 'txtShopPara':
                if (strlen($this->arrShopOrder[$key]) > 255) {
                    $this->arrShopOrder[$key] = substr($this->arrShopOrder[$key], 0, 255);
                    $this->arrWarning[] = "$key was cut to 255 characters.";
                }
                break;
            case 'txtBTitle':
                if (strlen($this->arrShopOrder[$key]) > 30) {
                    $this->arrShopOrder[$key] = substr($this->arrShopOrder[$key], 0, 30);
                    $this->arrWarning[] = "$key was cut to 30 characters.";
                }
                break;
            case 'txtBLastName':
                if (strlen($this->arrShopOrder[$key]) > 40) {
                    $this->arrShopOrder[$key] = substr($this->arrShopOrder[$key], 0, 40);
                    $this->arrWarning[] = "$key was cut to 40 characters.";
                }
                break;
            case 'txtBFirstName':
                if (strlen($this->arrShopOrder[$key]) > 40) {
                    $this->arrShopOrder[$key] = substr($this->arrShopOrder[$key], 0, 40);
                    $this->arrWarning[] = "$key was cut to 40 characters.";
                }
                break;
            case 'txtBAddr1':
                if (strlen($this->arrShopOrder[$key]) > 40) {
                    $this->arrShopOrder[$key] = substr($this->arrShopOrder[$key], 0, 40);
                    $this->arrWarning[] = "$key was cut to 40 characters.";
                }
                break;
            case 'txtBZipCode':
                if (strlen($this->arrShopOrder[$key]) > 10) {
                    $this->arrShopOrder[$key] = substr($this->arrShopOrder[$key], 0, 10);
                    $this->arrWarning[] = $key.' was cut to 10 characters.';
                }
                break;
            case 'txtBCity':
                if (strlen($this->arrShopOrder[$key]) > 40) {
                    $this->arrShopOrder[$key] = substr($this->arrShopOrder[$key], 0, 40);
                    $this->arrWarning[] = "$key was cut to 40 characters.";
                }
                break;
            case 'txtBCountry':
                if (strlen($this->arrShopOrder[$key]) != 2) {
// TODO: Only valid ISO-3166 country codes are accepted by Yellowpay!
// This should be verified.
                    $this->arrError[] = "$key isn't a valid 2 character ISO country code.";
                    return false;
                }
                $this->arrShopOrder[$key] = strtoupper($this->arrShopOrder[$key]);
                break;
            case 'txtBTel':
                if (strlen($this->arrShopOrder[$key]) > 40) {
                    $this->arrShopOrder[$key] = substr($this->arrShopOrder[$key], 0, 40);
                    $this->arrWarning[] = "$key was cut to 40 characters.";
                }
                break;
            case 'txtBFax':
                if (strlen($this->arrShopOrder[$key]) > 40) {
                    $this->arrShopOrder[$key] = substr($this->arrShopOrder[$key], 0, 40);
                    $this->arrWarning[] = "$key was cut to 40 characters.";
                }
                break;
            case 'txtBEmail':
                if (strlen($this->arrShopOrder[$key]) > 40) {
                    $this->arrShopOrder[$key] = substr($this->arrShopOrder[$key], 0, 40);
                    $this->arrWarning[] = "$key was cut to 40 characters.";
                }
                break;

            // Added 20080325, Reto Kohli:
            case 'txtHash':
                if (   empty($this->arrShopOrder[$key])
                    || !preg_match('/^[0-9a-f]{32}$/', $this->arrShopOrder[$key])) {
                    $this->arrError[] = "$key is invalid.";
                    return false;
                }
                break;
            case 'txtUsePopup':
                if (   $this->arrShopOrder[$key] != 'true'
                    && $this->arrShopOrder[$key] != 'false') {
                    $this->arrShopOrder[$key] = 'true';
                    $this->arrWarning[] = "$key was set to 'true'.";
                }
                break;
            case 'txtUseWindow':
                if (   $this->arrShopOrder[$key] != 'true'
                    && $this->arrShopOrder[$key] != 'false') {
                    $this->arrShopOrder[$key] = 'false';
                    $this->arrWarning[] = "$key was set to 'false'.";
                }
                break;
            case 'txtDestination':
                if (   $this->arrShopOrder[$key] != 'pfpopup'
                    && $this->arrShopOrder[$key] != 'pfwindow') {
                    $this->arrShopOrder[$key] = 'pfpopup';
                    $this->arrWarning[] = "$key was set to 'pfpopup'.";
                }
                break;
            case 'txtHistoryBack':
                if (   $this->arrShopOrder[$key] != 'true'
                    && $this->arrShopOrder[$key] != 'false') {
                    $this->arrShopOrder[$key] = 'false';
                    $this->arrWarning[] = "$key was set to 'false'.";
                }
                break;
            // Added 20080410, Reto Kohli
            // Dynamic payment methods selection.
            // All default to false
            case 'txtPM_PostFinanceCard_Status':
            case 'txtPM_yellownet_Status':
            case 'txtPM_Master_Status':
            case 'txtPM_Visa_Status':
            case 'txtPM_Amex_Status':
            case 'txtPM_Diners_Status':
            case 'txtPM_yellowbill_Status':
            case 'txtUseDynPM':
                if (   $this->arrShopOrder[$key] != 'true'
                    && $this->arrShopOrder[$key] != 'false') {
                    $this->arrShopOrder[$key] = 'false';
                    $this->arrWarning[] = "$key was set to 'false'.";
                }
                break;
            // All unknown keys like 'acceptedPaymentMethods' should have been
            // unset() already.
            default:
                $this->arrError[] = "$key is an unknown key!";
                return false;
        }
        return true;
    }


    /**
     * Returns the HTML menu options for selecting from the currently accepted
     * payment methods.
     * @param   string    $strSelected    The optional preselected payment
     *                                    method name
     * @return  string                    The HTML menu options
     */
    function getAcceptedPaymentMethodMenuOptions($strSelected='')
    {
        global $_ARRAYLANG;

        $strOptions = '';
        foreach (array_keys($this->arrAcceptedPaymentMethod)
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
    function getKnownPaymentMethodCheckboxes()
    {
        global $_ARRAYLANG;

        $strOptions = '';
        foreach (Yellowpay::$arrKnownPaymentMethod as $index => $strPaymentMethod) {
            $strOptions .=
                '<input name="yellowpay_accepted_payment_methods[]" '.
                'id="yellowpay_pm_'.$index.'" type="checkbox" '.
                (in_array($strPaymentMethod, array_keys($this->arrAcceptedPaymentMethod))
                    ? 'checked="checked" ' : ''
                ).
                'value="'.$strPaymentMethod.'" />'.
                '<label for="yellowpay_pm_'.$index.'">&nbsp;'.
                $_ARRAYLANG['TXT_SHOP_YELLOWPAY_'.strtoupper($strPaymentMethod)].
                '</label><br />';
        }
        return $strOptions;
    }


    function getAuthorizationMenuoptions()
    {
        global $_ARRAYLANG;

        return
            '<option value="immediate" '.
            ($this->strAuthorization == 'immediate' ? 'selected="selected"' : '').'>'.
            $_ARRAYLANG['TXT_SHOP_YELLOWPAY_IMMEDIATE'].
            '</option>'.
            '<option value="deferred" '.
            ($this->strAuthorization == 'deferred' ? 'selected="selected"' : '').'>'.
            $_ARRAYLANG['TXT_SHOP_YELLOWPAY_DEFERRED'].
            '</option>';
    }
}

?>
