<?php

/**
 * Interface to Saferpay
 * @author Comvation Development Team <info@comvation.com>
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  module_shop
 * @version     3.0.0
 */

/**
 * Socket connections to payment services
 */
require_once ASCMS_CORE_PATH.'/Socket.class.php';

/**
 * Interface to Saferpay
 * @author Comvation Development Team <info@comvation.com>
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  module_shop
 * @version     3.0.0
 */
class Saferpay
{
    /**
     * 'Is test' flag
     * @access  public
     * @var     boolean
     */
    public $isTest = false;

    /**
     * Temporary data
     * @access  private
     * @var     array
     */
    private $arrTemp = array();

    /**
     * Attributes
     * @access  private
     * @var     string
     * @see     checkOut()
     */
    private $attributes;

    /**
     * Attributes
     * @access  public
     * @var     string
     */
    private $testAccountId = '99867-94913159';

    /**
     * The hosting gateways
     * @access  private
     * @var     array
     * @see     checkOut(), success()
     */
    private $gateway = array(
        'payInit'     => 'https://www.saferpay.com/hosting/CreatePayInit.asp',
        'payConfirm'  => 'https://www.saferpay.com/hosting/VerifyPayConfirm.asp',
        'payComplete' => 'https://www.saferpay.com/hosting/PayComplete.asp',
    );

    /**
     * Currency codes
     * @access  private
     * @var     array
     */
    private $arrCurrency = array('CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'PLN', 'SEK', 'USD',);

    /**
     * Language codes
     * @access  private
     * @var     array
     */
    private $arrLangId = array('en', 'de', 'fr', 'it',);

    /**
     * Keys needed for the respective operations
     * @access  private
     * @var     array
     */
    private $arrKeys = array(
        'payInit'     => array(
            'AMOUNT',
            'CURRENCY',
            'ACCOUNTID',
            'SUCCESSLINK',
            'DESCRIPTION',
        ),
        'payConfirm'  => array(
            'DATA',
            'SIGNATURE',
        ),
        'payComplete' => array(
            'ACCOUNTID',
            'ID',
// Not used
//            'TOKEN',
        )
    );

    /**
     * Payment Information
     * @access  private
     * @var     array
     * @see     checkOut()
     */
    private $arrShopOrder = array();

    /**
     * Error messages
     * @access  public
     * @var     array
     * @see
     */
    private $arrError = array();

    /**
     * Error messages
     * @access  public
     * @var     array
     * @see
     */
    private $arrWarning = array();

    /**
     * Payment providers
     * @access  public
     * @var     array
     */
    private $arrProviders = array(
        'Airplus Corporate Card'         => 486,
        'American Express'               => 1,
        'AMEX DE'                        => 77,
        'AMEX DE CHF'                    => 333,
        'AMEX DE GBP'                    => 303,
        'AMEX DE USD'                    => 156,
        'AMEX EUR'                       => 112,
        'AMEX USD'                       => 57,
        'Bonus Card'                     => 15,
        'Bonus Card NSP'                 => 454,
        'Diners Citicorp'                => 81,
        'Diners Citicorp GBP'            => 334,
        'Diners Citicorp Int. CHF'       => 199,
        'Diners Citicorp Int. EUR'       => 179,
        'Diners Citicorp Int. GBP'       => 197,
        'Diners Citicorp Int. USD'       => 195,
        'Diners Citicorp USD'            => 245,
        'Diners Club'                    => 5,
        'Diners Club EUR'                => 205,
        'Diners Club USD'                => 181,
        'eScore Adress Verification'     => 167,
        'eScore Adress Verification'     => 155,
        'Geschenkkarte EP2'              => 415,
        'Homebanking DirectPay'          => 345,
        'Homebanking ELBA'               => 344,
        'Homebanking netpay'             => 343,
        'Homebanking netpay (T)'         => 357,
        'Homebanking POP'                => 341,
        'InterCard LSV'                  => 132,
        'IQA Adress Verification'        => 159,
        'JCB B+S'                        => 106,
        'JCB B+S CHF'                    => 277,
        'JCB B+S ep2 (TKC)'              => 478,
        'JCB CAR'                        => 12,
        'Lastschrift B+S'                => 352,
        'Maestro CH Multipay ep2'        => 332,
        'Maestro CH. B+S ep2 (TKC)'      => 480,
        'Maestro Intl. B+S ep2 (TKC)'    => 474,
        'Maestro Intl. Multipay ep2'     => 361,
        'Maestro Intl. Streamline ep2'   => 427,
        'MasterCard Airplus'             => 141,
        'MasterCard Airplus USD'         => 432,
        'MasterCard B+S'                 => 104,
        'MasterCard B+S CHF'             => 273,
        'Mastercard B+S ep2 (Datatrans)' => 379,
        'Mastercard B+S ep2 (TKC)'       => 472,
        'MasterCard B+S GBP'             => 227,
        'MasterCard B+S USD'             => 223,
        'MasterCard Citicorp'            => 79,
        'MasterCard Citicorp CHF'        => 255,
        'MasterCard Citicorp DKK'        => 257,
        'MasterCard Citicorp GBP'        => 259,
        'MasterCard Citicorp Int. CHF'   => 193,
        'MasterCard Citicorp Int. DKK'   => 207,
        'MasterCard Citicorp Int. EUR'   => 177,
        'MasterCard Citicorp Int. GBP'   => 189,
        'MasterCard Citicorp Int. SEK'   => 209,
        'MasterCard Citicorp Int. USD'   => 187,
        'MasterCard Citicorp SEK'        => 261,
        'MasterCard Citicorp USD'        => 219,
        'Mastercard Concardis ep2'       => 463,
        'Mastercard Corner CDS'          => 100,
        'Mastercard Corner CDS EUR'      => 110,
        'Mastercard Corner CDS GBP'      => 327,
        'Mastercard Corner CDS USD'      => 108,
        'Mastercard Corner ep2'          => 328,
        'MasterCard GZS'                 => 116,
        'MasterCard GZS ATS'             => 120,
        'MasterCard GZS USD'             => 148,
        'Mastercard Multipay CAR'        => 2,
        'Mastercard Multipay ep2'        => 330,
        'MasterCard Multipay NSP'        => 324,
        'MasterCard OmniPay Postbank'    => 358,
        'MasterCard SET B+S'             => 163,
        'MasterCard SET CITICORP'        => 166,
        'MasterCard SET GZS'             => 153,
        'MasterCard SET Multipay'        => 96,
        'MasterCard Streamline ep2'      => 423,
        'MC ConCardis CHF'               => 124,
        'Mediamarkt EP2'                 => 413,
        'Multipay CAR'                   => 400,
        'myOne Card EP2'                 => 411,
        'myOne NSP'                      => 444,
        'Paybox'                         => 147,
        'Paybox Test'                    => 164,
        'Post Finance Yellownet'         => 384,
        'PostCard DebitDirect'           => 322,
        'POSTCARD SET'                   => 173,
        'POSTCARD SET - OLD'             => 88,
        'Rechnung'                       => 114,
        'Telekurs American Express'      => 239,
        'Telekurs Bonus Card'            => 452,
        'Telekurs Diners'                => 235,
        'Telekurs ex MasterCard'         => 251,
        'Telekurs Geschenkkarte'         => 402,
        'Telekurs JCB'                   => 253,
        'Telekurs Maestro CH'            => 241,
        'Telekurs Maestro Intl.'         => 249,
        'Telekurs MasterCard'            => 237,
        'Telekurs Mastercard B+S'        => 482,
        'Telekurs Mediamarkt'            => 393,
        'Telekurs myOne Card'            => 391,
        'Telekurs PowerCard'             => 459,
        'Telekurs VISA'                  => 231,
        'Telekurs Visa B+S'              => 484,
        'Telekurs VISA Corner'           => 389,
        'Telekurs VISA Epsys'            => 233,
        'VISA Airplus'                   => 139,
        'VISA Airplus USD'               => 430,
        'VISA B+S'                       => 102,
        'VISA B+S CHF'                   => 275,
        'Visa B+S ep2 (Datatrans)'       => 381,
        'Visa B+S ep2 (TKC)'             => 476,
        'VISA B+S GBP'                   => 229,
        'VISA B+S USD'                   => 225,
        'VISA Citicorp'                  => 69,
        'VISA Citicorp CHF'              => 263,
        'VISA Citicorp DKK'              => 265,
        'VISA Citicorp GBP'              => 269,
        'VISA Citicorp Int. CHF'         => 191,
        'VISA Citicorp Int. DKK'         => 211,
        'VISA Citicorp Int. EUR'         => 175,
        'VISA Citicorp Int. GBP'         => 185,
        'VISA Citicorp Int. SEK'         => 213,
        'VISA Citicorp Int. USD'         => 183,
        'VISA Citicorp SEK'              => 267,
        'VISA Citicorp USD'              => 221,
        'Visa ConCardis CHF'             => 126,
        'Visa Concardis ep2'             => 461,
        'VISA Corner CHF'                => 4,
        'VISA Corner DEM'                => 135,
        'Visa Corner ep2'                => 365,
        'VISA Corner EURO'               => 65,
        'VISA Corner GBP'                => 133,
        'VISA Corner ITL'                => 143,
        'VISA Corner USD'                => 55,
        'VISA GZS'                       => 118,
        'VISA GZS ATS'                   => 122,
        'VISA GZS USD'                   => 150,
        'Visa Multipay CAR'              => 339,
        'Visa Multipay ep2'              => 363,
        'VISA Multipay NSP'              => 337,
        'Visa OmniPay Postbank'          => 359,
        'VISA SET B+S'                   => 162,
        'VISA SET CITICORP'              => 165,
        'VISA SET GZS'                   => 152,
        'VISA SET Multipay'              => 94,
        'VISA Streamline ep2'            => 425,
        'VISA UBS CHF'                   => 3,
        'VISA UBS DEM'                   => 137,
        'VISA UBS EUR'                   => 51,
        'VISA UBS GBP'                   => 310,
        'VISA UBS Purchasing'            => 63,
        'VISA UBS USD'                   => 13,
    );

    /**
     * Window options constants
     * @access  public
     */
    // Iframes are disabled due to handling problems.
    // After the payment is complete, the shop itself is displayed in the
    // frame instead of its parent!
    //const saferpay_windowoption_id_iframe = 0;
    const saferpay_windowoption_id_popup  = 1;
    const saferpay_windowoption_id_window = 2;
    // keep this up to date!
    // Note that the class method getWindowMenuoptions() has been
    // adapted to skip the disabled option ID 0!
    const saferpay_windowoption_id_count  = 3;


    /**
     * Constructor
     */
    function __construct()
    {
    }


    /**
     * Generates a list of all attributes
     * @param   string  Step of the payment
     * @access  private
     * @return  string  Attributelist on success, empty string on failure
     */
    function getAttributeList($step)
    {
        $this->attributes = null;
        foreach ($this->arrKeys[$step] as $attribute) {
            if ($this->ifExist($attribute)) {
                if ($this->checkAttribute($attribute)) {
                    $this->addAttribute($attribute);
                }
            }
        }
        foreach (array_keys($this->arrShopOrder) as $attribute) {
            if ($this->checkAttribute($attribute)) {
                $this->addAttribute($attribute);
            }
        }
        if (count($this->arrError) == 0) {
            return $this->attributes;
        }
        return '';
    }


    /**
     * Initializes the payment
     *
     * Generates the link for requesting the VT at Saferpay
     * @param   array   Attributes
     * @access  public
     * @return  string  Link for payment initialisation
     */
    function payInit($arrShopOrder)
    {
        $this->arrShopOrder = $arrShopOrder;
        $this->attributes = $this->getAttributeList('payInit');

        // This won't work without allow_url_fopen
        $this->arrTemp['result'] =
            file_get_contents($this->gateway['payInit'].'?'.$this->attributes);
        if ($this->arrTemp['result']) return $this->arrTemp['result'];
        // Try socket connection as well
        $this->arrTemp['result'] =
            Socket::getHttp10Response(
                $this->gateway['payInit'].'?'.$this->attributes
            );
        return $this->arrTemp['result'];
    }


    /**
     * Confirms the payment transaction
     * @access  public
     * @return  boolean     True on success, false otherwise
     */
    function payConfirm()
    {
        // Predefine the variables parsed by parse_str() to avoid
        // code analyzer warnings
        $DATA = '';
        $SIGNATURE = '';
        parse_str($_SERVER['QUERY_STRING']);
        // Note: parse_str()'s results comply with the magic quotes setting!
        $this->arrShopOrder['DATA']      = urlencode(contrexx_stripslashes($DATA));
        $this->arrShopOrder['SIGNATURE'] = urlencode(contrexx_stripslashes($SIGNATURE));
        $this->attributes = $this->getAttributeList('payConfirm');

        // This won't work without allow_url_fopen
        $this->arrTemp['result'] =
            file_get_contents($this->gateway['payConfirm'].'?'.$this->attributes);
        if (!$this->arrTemp['result']) {
            // Try socket connection as well
            $this->arrTemp['result'] =
                Socket::getHttp10Response(
                    $this->gateway['payConfirm'].'?'.$this->attributes
                );
        }

        if (substr($this->arrTemp['result'], 0, 2) == 'OK') {
            $ID = '';
            $TOKEN = '';
            parse_str(substr($this->arrTemp['result'], 3));
            $this->arrTemp['id'] = $ID;
            $this->arrTemp['token'] = $TOKEN;
            return true;
        }
        $this->arrError[] = $this->arrTemp['result'];
        return false;
    }


    /**
     * Completes the payment transaction
     * @param   array       Attributes
     * @access  public
     * @return  boolean     True on success, false otherwise
     */
    function payComplete($arrShopOrder)
    {
        $this->arrShopOrder = $arrShopOrder;
        $this->arrShopOrder['ID'] = $this->arrTemp['id'];
// Not used
//        $this->arrShopOrder['TOKEN'] = $this->arrTemp['token'];
        $this->attributes =
            $this->getAttributeList('payComplete').
            // Business account *ONLY*, like the test account
            // There is no password setting (yet), so this is for
            // future testing porposes *ONLY*
            (SettingDb::getValue('saferpay_use_test_account')
              ? '&spPassword=XAjc3Kna'
              : '');
        // This won't work without allow_url_fopen
        $this->arrTemp['result'] =
            file_get_contents($this->gateway['payComplete'].'?'.$this->attributes);
        if (!$this->arrTemp['result']) {
            // Try socket connection as well
            $this->arrTemp['result'] =
                Socket::getHttp10Response(
                    $this->gateway['payComplete'].'?'.$this->attributes
                );
        }
        if (substr($this->arrTemp['result'], 0, 2) == 'OK') {
            return true;
        }
        $this->arrError[] = $this->arrTemp['result'];
        return false;
    }


    /**
     * Returns the order ID of the transaction
     * @access  public
     * @return  integer The order ID
     * @static
     */
    static function getOrderId()
    {
        $arrMatches = array();
        $strParams = urldecode(stripslashes($_GET['DATA']));
        if (!preg_match('/\sORDERID\=\"(\d+)\"/', $strParams, $arrMatches))
            return false;
        $orderId = $arrMatches[1];
        return $orderId;
    }


    /**
     * Checks whether the given attribute exists in the array arrShopOrder.
     * @access  private
     * @param   string      Attribute to check for its existence
     * @return  boolean     True on success, false otherwise
     */
    function ifExist($attribute)
    {
        if (array_key_exists($attribute,$this->arrShopOrder)) {
            return true;
        }
        $this->arrError[] = $attribute." isn't set!";
        return false;
    }


    /**
     * Verifies the value of an attribute for correctness.
     * @access  private
     * @param   string      Attribute to check for correctness
     * @return  boolean     True on success, false otherwise
     */
    function checkAttribute($attribute)
    {
        switch ($attribute) {
            case 'AMOUNT':
                $this->arrShopOrder[$attribute] = intval($this->arrShopOrder[$attribute]);
                if ($this->arrShopOrder[$attribute] <= 0) {
                    $this->arrError[] = $attribute." isn't valid.";
                    return false;
                }
                return true;
            case 'CURRENCY':
                if (in_array(strtoupper($this->arrShopOrder[$attribute]),$this->arrCurrency)) {
                    $this->arrShopOrder[$attribute] = strtoupper($this->arrShopOrder[$attribute]);
                    return true;
                }
                $this->arrError[] = $attribute." isn't valid.";
                return false;
            case 'ACCOUNTID':
                if ($this->isTest) {
                    $this->arrShopOrder[$attribute] = $this->testAccountId;
                }
                if ($this->arrShopOrder[$attribute] == '') {
                    $this->arrError[] = $attribute." isn't set";
                    return false;
                }
                return true;
            case 'ORDERID':
                if (strlen($this->arrShopOrder[$attribute]) > 80) {
                    $this->arrShopOrder[$attribute] = substr($this->arrShopOrder[$attribute],0,80);
                    $this->arrWarning[] = $attribute.' was cut to 80 characters.';
                }
                return true;
            case 'SUCCESSLINK':
                if ($this->arrShopOrder[$attribute] == null) {
                    $this->arrError[] = $attribute." isn't set";
                    return false;
                }
                return true;
            case 'FAILLINK':
                if ($this->arrShopOrder[$attribute] == null) {
                    $this->arrWarning[] = $attribute." isn't set";
                    return false;
                }
                return true;
            case 'BACKLINK':
                if ($this->arrShopOrder[$attribute] == null) {
                    $this->arrWarning[] = $attribute." isn't set";
                    return false;
                }
                return true;
            case 'DESCRIPTION':
                return true;
            case 'ALLOWCOLLECT':
                if ($this->arrShopOrder[$attribute] != 'yes') {
                    $this->arrShopOrder[$attribute] = 'yes';
                    $this->arrWarning[] = $attribute.' was set to "yes"';
                }
                return true;
            case 'DELIVERY':
                if ($this->arrShopOrder[$attribute] != 'yes') {
                    $this->arrShopOrder[$attribute] = 'yes';
                    $this->arrWarning[] = $attribute.' was set to "yes"';
                }
                return true;
            case 'NOTIFYADDRESS':
                return true;
            case 'TOLERANCE':
                return true;
            case 'LANGID':
                if (in_array(strtolower($this->arrShopOrder[$attribute]),$this->arrLangId)) {
                    $this->arrShopOrder[$attribute] = strtolower($this->arrShopOrder[$attribute]);
                } else {
                    $this->arrShopOrder[$attribute] = $this->arrLangId['0'];
                    $this->arrWarning[] = $attribute.' was set to default value "'.$this->arrLangId['0'].'".';
                }
                return true;
            case 'DURATION':
                if (strlen($this->arrShopOrder[$attribute]) != 14) {
                    $this->arrWarning[] = $attribute." isn't valid.";
                    return false;
                }
                return true;
            case 'DATA':
                return true;
            case 'SIGNATURE':
                return true;
            case 'ID':
                return true;
            case 'TOKEN':
                return true;
            case 'EXPIRATION':
                return true;
            case 'PROVIDERID':
                return true;
            case 'PROVIDERNAME':
                return true;
            case 'PROVIDERSET':
                // see http://www.saferpay.com/help/ProviderTable.asp
                if (is_array($this->arrShopOrder[$attribute])) {
                    foreach ($this->arrShopOrder[$attribute] as $provider) {
                        if (isset($this->arrProviders[$provider])) {
                            $arrProviders[] = $this->arrProviders[$provider];
                            //$arrProviders[] = $provider;
                        } else {
                            $this->arrWarning[] = 'Unknown provider "'.$provider.'"';
                        }
                    }
                }
// fixed: $arrProviders may be undefined!
                if (isset($arrProviders) && is_array($arrProviders)) {
                    $this->arrShopOrder[$attribute] = urlencode(implode(',', $arrProviders));
                } else {
                    $this->arrShopOrder[$attribute] = '';
                }
                return true;
            case 'PAYMENTAPPLICATION':
                return true;
            case 'ACTION':
                return true;
            default:
                return true;
        }
    }


    /**
     * Adds an attribute to the attributes list class variable.
     *
     * Also deletes the attribute from the attribute array arrShopOrder.
     * @access  private
     * @param   string      Attribute to add
     * @return  boolean     True on success, false otherwise
     */
    function addAttribute($attribute)
    {
        $this->attributes .=
            ($this->attributes != '' ? '&' : '').
            $attribute.'='.$this->arrShopOrder[$attribute];
        unset($this->arrShopOrder[$attribute]);
    }


    /**
     * Returns code for HTML menu options for choosing the window display
     * option
     * @param   integer     $selected       The selected option ID
     * @return  string                      The HTML menu options
     */
    static function getWindowMenuoptions($selected=0)
    {
        global $_ARRAYLANG;

        $strMenuoptions = '';
        // Set $id to start at 0 (zero) to enable iframes!
        for ($id = 1; $id < self::saferpay_windowoption_id_count; ++$id) {
            $strMenuoptions .=
                '<option value="'.$id.'"'.
                ($id == $selected ? ' selected="selected"' : '').'>'.
                $_ARRAYLANG['TXT_SHOP_SAFERPAY_WINDOWOPTION_'.$id].
                "</option>\n";
        }
        return $strMenuoptions;
    }

}

?>
