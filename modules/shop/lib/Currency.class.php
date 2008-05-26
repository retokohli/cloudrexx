<?php

/**
 * Currencies manager.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Currencies manager.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  module_shop
 */
class Currency
{
    var $inactiveStyleName = "inactive";
    var $activeStyleName = "active";

    /**
     * Array of available currencies (default null).
     *
     * Use {@link getCurrencyArray()} to access it from outside this class.
     * @access  private
     * @var     array
     */
    var $arrCurrency = array();

    /**
     * Active currency object id (default null).
     *
     * Use {@link getActiveCurrencyId()} to access it from outside this class.
     * @access  private
     * @var     integer
     */
    var $activeCurrencyId = null;

    /**
     * Default currency object id (defaults to null).
     *
     * Use {@link getDefaultCurrencyId()} to access it from outside this class.
     * @access  private
     * @var     integer
     */
    var $defaultCurrencyId = null;

    /**
     * Default currency symbol (defaults to '').
     * Use {@link getDefaultCurrencySymbol()} to access it from outside this class.
     * @access  private
     * @var     string
     */
    var $defaultCurrencySymbol = '';


    /**
     * Constructor
     *
     * Initializes the currencies array
     */
    function __construct()
    {
        global $objDatabase;

        $query =
            "SELECT id, code, symbol, name, rate, sort_order, status, is_default ".
            "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_currencies ".
            "ORDER BY id";

        $objResult = $objDatabase->Execute($query);

        while(!$objResult->EOF) {
            $this->arrCurrency[$objResult->fields['id']] = array(
                'id' => $objResult->fields['id'],
                'code' => $objResult->fields['code'],
                'symbol' => $objResult->fields['symbol'],
                'name' => $objResult->fields['name'],
                'rate' => $objResult->fields['rate'],
                'sort_order' => $objResult->fields['sort_order'],
                'status' => $objResult->fields['status'],
                'is_default' => $objResult->fields['is_default']
            );

            if ($objResult->fields['is_default'] == 1) {
                $this->defaultCurrencyId = $objResult->fields['id'];
                $this->defaultCurrencySymbol = $objResult->fields['symbol'];
            }
            $objResult->MoveNext();
        }
        $this->_init();
    }


    /**
     * Initiates currencies with Request URI parameters if there are any.
     */
    function _init()
    {
        if (isset($_REQUEST['currency'])) {
            $sId = intval($_REQUEST['currency']);
            $_SESSION['shop']['currencyId'] = isset($this->arrCurrency[$sId]) ? $sId : $this->defaultCurrencyId;
        } else {
            if (!isset($_SESSION['shop']['currencyId'])) {
                $_SESSION['shop']['currencyId'] = $this->defaultCurrencyId;
            }
        }
        $this->activeCurrencyId     = intval($_SESSION['shop']['currencyId']);
        $this->activeCurrencySymbol = $this->arrCurrency[$this->activeCurrencyId]['symbol'];
        $this->activeCurrencyCode   = $this->arrCurrency[$this->activeCurrencyId]['code'];
    }


    /**
     * Returns the currency array
     *
     * @access  public
     * @return  array   The currency array
     */
    function getCurrencyArray()
    {
        return $this->arrCurrency;
    }


    /**
     * Returns the default currency ID
     *
     * @author  Reto Kohli
     * @access  public
     * @return  integer     The ID of the default currency
     */
    function getDefaultCurrencyId()
    {
        return $this->defaultCurrencyId;
    }


    /**
     * Returns the default currency symbol
     *
     * @author  Reto Kohli
     * @access  public
     * @return  string      The string representing the default currency
     */
    function getDefaultCurrencySymbol()
    {
        return $this->defaultCurrencySymbol;
    }


    /**
     * Returns the active currency ID
     *
     * @author  Reto Kohli
     * @access  public
     * @return  integer     The ID of the active currency
     */
    function getActiveCurrencyId()
    {
        return $this->activeCurrencyId;
    }


    /**
     * Returns the active currency symbol
     *
     * @author  Reto Kohli
     * @access  public
     * @return  string      The string representing the active currency
     */
    function getActiveCurrencySymbol()
    {
        return $this->activeCurrencySymbol;
    }


    /**
     * Returns the active currency code
     *
     * @author  Reto Kohli
     * @access  public
     * @return  string      The string representing the active currency code
     */
    function getActiveCurrencyCode()
    {
        return $this->activeCurrencyCode;
    }


    /**
     * Returns the amount converted from the default to the active currency
     *
     * Note that the amount is rounded to five cents before formatting.
     * @access  public
     * @param   double  $price  The amount in default currency
     * @return  string          Formatted amount in the active currency
     * @todo    In case that the {@link formatPrice()} function is localized,
     *          the returned value *MUST NOT* be treated as a number anymore!
     */
    function getCurrencyPrice($price)
    {
        $rate = $this->arrCurrency[$this->activeCurrencyId]['rate'];
        // getting 0.05 increments
        return Currency::formatPrice(round(20*$price*$rate)/20);
    }


    /**
     * Returns the amount converted from the active to the default currency
     *
     * Note that the amount is rounded to five cents before formatting.
     * @access  public
     * @param   double  $price  The amount in active currency
     * @return  string          Formated amount in default currency
     * @todo    In case that the {@link formatPrice()} function is localized,
     *          the returned value *MUST NOT* be treated as a number anymore!
     */
    function getDefaultCurrencyPrice($price)
    {
        if ($this->activeCurrencyId == $this->defaultCurrencyId) {
            return Currency::formatPrice($price);
        } else {
            $rate = $this->arrCurrency[$this->activeCurrencyId]['rate'];
            $defaultRate = $this->arrCurrency[$this->defaultCurrencyId]['rate'];
            // getting 0.05 increments
            return Currency::formatPrice(round(20*$price*$defaultRate/$rate)/20);
        }
    }


    /**
     * Returns the formatted amount in a non-localized notation
     * rounded to two decimal places,
     * using no thousands, and '.' as decimal separator.
     * @todo    Localize!  Create language and country dependant
     *          settings in the database, and make this behave accordingly.
     * @static
     * @param   double  $price  The amount
     * @return  double          The formatted amount
     */
    //static
    function formatPrice($price)
    {
        return number_format($price, 2, '.', '');
    }


    /**
     * makes the currency navbar
     *
     * @return string $curNavbar
     * @access public
     */
    function getCurrencyNavbar()
    {
        $arrCurNavbar = array();
        foreach ($this->arrCurrency as $id => $arrCurrency) {
            if ($arrCurrency['status'] == 1) {
                $style = ($id == $this->activeCurrencyId
                    ? $this->activeStyleName
                    : $this->inactiveStyleName
                );
                $arrCurNavbar[] =
                    '<a class="'.$style.'" href="'.
                    htmlspecialchars(
                        $_SERVER['REQUEST_URI'], ENT_QUOTES, CONTREXX_CHARSET
                    ).
                    '&amp;currency='.$id.'" title="'.$arrCurrency['code'].'">'.
                    $arrCurrency['code'].
                    '</a>';
            }
        }
        return join("&nbsp;|&nbsp;\n", $arrCurNavbar);
    }


    /**
     * Return the currency code for the ID given
     * @static
     * @param   integer   $currencyId   The currency ID
     * @return  mixed                   The currency code on success,
     *                                  false otherwise
     * @global  mixed     $objDatabase  Database object
     */
    //static
    function getCodeById($currencyId)
    {
        global $objDatabase;

        $query = "
            SELECT code
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_currencies
            WHERE id=$currencyId
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult && !$objResult->EOF) {
            return $objResult->fields['code'];
        }
        return false;
    }



}

?>
