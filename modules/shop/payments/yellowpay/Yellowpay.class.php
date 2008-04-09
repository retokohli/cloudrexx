<?php

/**
 * Class Yellowpay
 *
 * Interface for the payment mask yellowpay
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @version     $Id:  Exp $
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Yellowpay plugin for online payment
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @version     $Id:  Exp $
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
        'US' => 2057,
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
     * Delivery payment types
     * @access  private
     * @var     array
     * @see     checkKey()
     */
    private $arrDeliveryPaymentType = array(
        'immediate',
        'deferred',
    );

    /**
     * Payment types
     * @access  private
     * @var     array
     * @see     addPaymentTypeKeys()
     */
    private $arrPaymentType = array(
        'PostFinanceCard' => array(),
        'yellownet' => array(),
        'Master' => array(),
        'Visa' => array(),
        'Amex' => array(),
        'Diners' => array(),
        'yellowbill' => array(
            'txtESR_Member',
            'txtBLastName',
            'txtBAddr1',
            'txtBZipCode',
            'txtBCity',
        ),
    );

    /**
     * Accepted payment methods; comma separated list, no whitespace!
     * @var   string
     */
    private $strAcceptedPaymentMethods;


    /**
     * Constructor
     */
    function Yellowpay()
    {
    }


    /**
     * Creates and returns the HTML-Form for requesting the yellowpay-service.
     *
     * @access  public
     * @return  string    The HTML-Form
     * @see     addRequiredKeys(), addPaymentTypeKeys(), addOtherKeys()
     */
    function getForm($arrShopOrder, $submitValue='send')
    {
        $this->arrShopOrder = $arrShopOrder;

        if ($this->is_test) {
            // Test mode
            $this->form ="<form action='https://yellowpaytest.postfinance.ch/checkout/Yellowpay.aspx?userctrl=Invisible' method='post'>\n";
        } else {
            // Live mode
            $this->form ="<form action='https://yellowpay.postfinance.ch/checkout/Yellowpay.aspx?userctrl=Invisible' method='post'>\n";
        }
        $this->addRequiredKeys();
        $this->addPaymentTypeKeys();
        $this->addOtherKeys();
        $this->form .= "<input type='submit' name='submit' value='$submitValue' />\n";
        $this->form .= '</form>';
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
            return;
        }
        $arrAcceptedPM = explode(',', $this->arrShopOrder['acceptedPaymentMethods']);
        if (empty($arrAcceptedPM)) {
            return;
        }

        foreach ($arrAcceptedPM as $strPM) {
            if (array_key_exists($strPM, $this->arrPaymentType)) {
                $this->arrShopOrder["txtPM_{$strPM}_Status"] = 'true';
                $this->addToForm("txtPM_{$strPM}_Status");
            } else {
                $this->arrError[] = "Payment type '$strPM' is unknown.";
                return;
            }
        }
        // Enable dynamic payment method selection
        $this->arrShopOrder['txtUseDynPM'] = 'true';
        $this->addToForm('txtUseDynPM');
    }


    /**
     * Returns an array with all supported payment types.
     *
     * Note: This is still under development.
     * @return  array         The payment type name strings
     */
    function getActivePaymentTypes()
    {
        //return array_keys($this->arrPaymentType);
        return array(
            'PostFinanceCard',
            'yellownet',
            'Master',
            'Visa',
            'Amex',
            'Diners',
            'yellowbill',
        );
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
        unset($this->arrShopOrder['ShopId']);
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
        if (empty($this->arrShopOrder['ShopId'])) {
            $this->arrError[] = "Missing the ShopId parameter";
        }
        if ($this->arrShopOrder['Hash_seed'] == '') {
            $this->arrError[] = "Missing the Hash_seed parameter";
        }
        $this->arrShopOrder['txtHash'] = md5(
            $this->arrShopOrder['ShopId'].
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
                if (!ereg('^[0-9]+\.[0-9]{1,2}$', $this->arrShopOrder[$key])) {
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
                if (!in_array($this->arrShopOrder[$key], $this->arrDeliveryPaymentType)) {
                    $this->arrShopOrder[$key] = $this->arrDeliveryPaymentType['1'];
                    $this->arrWarning[] = "$key was set to '{$this->arrDeliveryPaymentType['1']}'.";
                }
                break;
            case 'txtESR_Member':
                if (!ereg('^[0-9]{1,2}-[0-9]{1,6}-[0-9]$', $this->arrShopOrder[$key])) {
                    $this->arrError[] = "$key isn't valid.";
                    return false;
                }
                break;
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

            default:
                $this->arrError[] = "$key is an unknown key!";
                return false;
        }
        return true;
    }

}

?>
