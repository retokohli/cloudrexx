<?php
/**
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */

/**
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_shop
 */
class Vat
{
    /**
     * @var     array   $arrVatClass    The Vat class array, entries look like
     *                                  (ID => "Class") (string)
     * @access  private
     */
    var $arrVatClass = array();

    /**
     * @var     array   $arrVatRate     The Vat rate array, entries look
     *                                  like (ID => rate) (in percent, double)
     * @access  private
     */
    var $arrVatRate  = array();

    /**
     * @var     boolean $vatEnabled     Indicates whether VAT is enabled (true), or not (false).
     *                                  Determined by the tax_enabled entry in the shop_config table.
     * @access  private
     */
    var $vatEnabled;

    /**
     * @var     boolean $vatIncluded    Indicates whether VAT is included in the shop price (true),
     *                                  or not (false).  Determined by the tax_included entry
     *                                  in the shop_config table.
     * @access  private
     */
    var $vatIncluded;

    /**
     * @var     double  $vatDefaultId   The default VAT ID, determined by the tax_default_id entry
     *                                  in the shop_config table.  See {@see init()},
     *                                  {@see  calculateDefaultTax()}.
     * @access  private
     */
    var $vatDefaultId;
    /**
     * @var     double  $vatDefaultRate The default VAT rate, determined by the tax_default_id entry
     *                                  in the shop_config table.  See {@see init()},
     *                                  {@see  calculateDefaultTax()}.
     * @access  private
     */
    var $vatDefaultRate;



    /**
     * Set up an initialized Vat object including ready-to-use
     * arrays taken from the database. (PHP4)
     *
     * See {@link init()}.
     * @access      public
     */
    function Vat()
    {
        $this->init();
    }
    /**
     * Set up an initialized Vat object including ready-to-use
     * arrays taken from the database. (PHP5)
     *
     * See {@link init()}.
     * @access      public
     */
    function __construct()
    {
        $this->init();
    }


    /**
     * Initialize the Vat object with current values from the database.
     *
     * Set up two class array variables, one called $arrVatClass, like
     *  (ID => "class", ...)
     * and the other called $arrVatRate, like
     *  (ID => rate)
     * Plus initializes the various object variables.
     * May die() with a message if it fails to access its settings.
     * @global  mixed   $objDatabase    Database object
     * @return  void
     */
    function init()
    {
        global $objDatabase;

        $query = "SELECT id, percent, class ".
                 "FROM ".DBPREFIX."module_shop_vat";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
            while (!$objResult->EOF) {
                $id = $objResult->fields['id'];
                $this->arrVatClass[$id] = $objResult->fields['class'];
                $this->arrVatRate[$id]  = $objResult->fields['percent'];
                $objResult->MoveNext();
            }
        } else {
            // no record found
            die ("Failed to init VAT arrays<br />");
        }

        $query = "SELECT * FROM ".DBPREFIX."module_shop_config WHERE name='tax_enabled'";
        $objResult = $objDatabase->Execute($query);
        if ($objResult && !$objResult->EOF) {
            $this->vatEnabled = $objResult->Fields('value');
        } else { die ("Failed to get VAT enabled flag<br />"); }

        $query = "SELECT * FROM ".DBPREFIX."module_shop_config WHERE name='tax_included'";
        $objResult = $objDatabase->Execute($query);
        if ($objResult && !$objResult->EOF) {
            $this->vatIncluded = $objResult->Fields('value');
        } else { die ("Failed to get VAT included flag<br />"); }

        $query = "SELECT * FROM ".DBPREFIX."module_shop_config WHERE name='tax_default_id'";
        $objResult = $objDatabase->Execute($query);
        if ($objResult && !$objResult->EOF) {
            $this->vatDefaultId = $objResult->Fields('value');
        } else { die ("Failed to get default VAT ID<br />"); }

        $this->vatDefaultRate = $this->getRate($this->vatDefaultId);
    }


    /**
     * Returns the default VAT rate
     *
     * @return  float   The default VAT rate
     */
    function getDefaultRate() {
        return $this->vatDefaultRate;
    }


    /**
     * Returns the default VAT ID
     *
     * @return  integer The default VAT ID
     */
    function getDefaultId() {
        return $this->vatDefaultId;
    }


    /**
     * Returns the value of the $vatEnabled variable.
     *
     * Returns true if the VAT is enabled, false otherwise.
     * @return  boolean     True if VAT is enabled, false otherwise.
     */
    function isEnabled()
    {
        return $this->vatEnabled;
    }


    /**
     * Returns the value of the $vatIncluded variable.
     *
     * Returns true if the VAT is included in the products' prices, false otherwise.
     * @return  boolean     True if VAT is included, false otherwise.
     */
    function isIncluded()
    {
        return $this->vatIncluded;
    }


    /**
     * Return the array of IDs and rates
     *
     * The ID keys correspond to the IDs used in the database.
     * Use these to get the respective VAT class.
     * @access  public
     * @return  array           The ID array
     */
    function getRateArray()
    {
        return $this->arrVatRate;
    }


    /**
     * Return dropdown menu options with IDs as names and VAT rates as values.
     *
     * The default for the $selected parameter is '' (the empty string).
     * The <select>/</select> tags are only added if you also specify a name
     * for the menu as second argument. Otherwise you'll have to add them later.
     * The $selectAttributes are added to the <select> tag if there is one.
     * The ID keys used correspond to the IDs used in the database.
     * @access  public
     * @param   integer $selected   The default VAT ID to be preselected in the menu
     * @param   string  $menuname   The name to use in the <select> tag
     * @return  string              The dropdown menu (with or without <select>...</select>)
     */
    function getShortMenuString($selected='', $menuname='', $selectAttributes='')
    {
        global $_ARRAYLANG;

        $string = '';
        foreach ($this->arrVatRate as $id => $rate) {
            $string .= "<option value='$id'";
            if ($selected == $id) {
                $string .= " selected='selected'";
            }
            $string .= ">$rate %</option>";
        }
        if ($menuname) {
            $string = "<select name='$menuname'".
                ($selectAttributes=='' ? '' : " $selectAttributes").
                ">$string</select>";
        }
        return $string;
    }


    /**
     * Return dropdown menu options with IDs as names and VAT classes plus rates
     * as values.
     *
     * The default for the $selected parameter is '' (the empty string).
     * The <select>/</select> tags are only added if you also specify a name
     * for the menu as second argument. Otherwise you'll have to add them later.
     * The $selectAttributes are added to the <select> tag if there is one.
     * The ID keys used correspond to the IDs used in the database.
     * @access  public
     * @param   integer $selected   The default VAT ID to be preselected in the menu
     * @param   string  $menuname   The name to use in the <select> tag
     * @return  string              The dropdown menu (with or without <select>...</select>)
     */
    function getLongMenuString($selected='', $menuname='', $selectAttributes='')
    {
        global $_ARRAYLANG;

        $string = '';
        foreach ($this->arrVatRate as $id => $rate) {
            $string .= "<option value='$id'";
            if ($selected == $id) {
                $string .= " selected='selected'";
            }
            $string .= ">".$this->arrVatClass[$id]." $rate %</option>";
        }
        if ($menuname) {
            $string = "<select name='$menuname'".
                ($selectAttributes == '' ? '' : " $selectAttributes").
                ">$string</select>";
        }
        return $string;
    }


    /**
     * Return the tax rate for the given VAT ID, if available,
     * or '' (the empty string) if the entry could not be found.
     * @access  public
     * @param   integer $vatId  The VAT ID
     * @return  double          The VAT rate, or ''
     */
    function getRate($vatId)
    {
        if (isset($this->arrVatRate[$vatId])) {
            return $this->arrVatRate[$vatId];
        }
        // no entry found
        return '';
    }


    /**
     * Return the tax class for the given VAT ID, if available,
     * or a warning message if the entry could not be found.
     *
     * @access  public
     * @param   integer $vatId  The VAT ID
     * @global  array           Language array
     * @return  string          The VAT class, or a warning
     */
    function getClass($vatId)
    {
        global $_ARRAYLANG;

        if (isset($this->arrVatClass[$vatId])) {
            return $this->arrVatClass[$vatId];
        }
        // no entry found
        return $_ARRAYLANG['TXT_TAX_NOT_SET'];
    }


    /**
     * Return the tax rate with a trailing percent sign
     * for the given percentage.
     *
     * @static
     * @access  public
     * @param   float   $rate   The Vat rate in percent
     * @return  string          The resulting string
     */
    //static
    function format($rate)
    {
        return "$rate%";
    }


    /**
     * Return the short tax rate with a trailing percent sign for the given
     * Vat ID, if available, or a warning message if the entry could not be
     * found.
     *
     * @access  public
     * @param   integer $vatId  The Vat ID
     * @global  array           Language array
     * @return  string          The resulting string
     */
    function getShort($vatId)
    {
        global $_ARRAYLANG;

        $rate = 0;
        if (isset($this->arrVatRate[$vatId])) {
            $rate = $this->arrVatRate[$vatId];
        }
        return Vat::format($rate);
    }


    /**
     * Return the long tax rate, including the class, rate and a trailing
     * percent sign for the given Vat ID, if available, or a warning message
     * if the entry could not be found.
     *
     * @access  public
     * @param   integer $vatId  The Vat ID
     * @global  array           Language array
     * @return  string          The resulting string
     */
    function getLong($vatId)
    {
        global $_ARRAYLANG;

        if (isset($this->arrVatClass[$vatId]) &&
            isset($this->arrVatRate[$vatId])) {
            return $this->arrVatClass[$vatId] . '&nbsp;' .
                   Vat::format($this->arrVatRate[$vatId]);
        }
        // no entry found
        return $_ARRAYLANG['TXT_TAX_NOT_SET'];
    }


    /**
     * Update the VAT entries found in the array arguments
     * in the database.
     *
     * Check if the rates are non-negative decimal numbers, and only
     * updates records that have been changed.
     * @access  public
     * @param   array   $vatIds     VAT IDs (index => ID)
     * @param   array   $vatClasses VAT classes (ID => 'class')
     * @param   array   $vatRates   VAT rates in percent (ID => rate)
     * @global  mixed               Database
     * @return  boolean         True if *all* the values were accepted and
     *                          successfully inserted into the database,
     *                          false otherwise.
     */
    function updateVat($vatIds, $vatClasses, $vatRates)
    {
        global $objDatabase;
        $alright = true;
        foreach ($vatIds as $index => $id) {
            if (intval($id) && $id >= 0) {
                $class  = $vatClasses[$index];
                $rate   = $vatRates[$index];
                if (isset($this->arrVatClass[$id]) &&
                    isset($this->arrVatRate[$id]))
                {
                    if ($this->arrVatClass[$id] != $class ||
                        $this->arrVatRate[$id]  != $rate  )
                    {
                        $query = "UPDATE ".DBPREFIX."module_shop_vat " .
                            "SET class='$class', percent=$rate " .
                            "WHERE id=$id";
                        $objResult = $objDatabase->Execute($query);
                        if (!$objResult) $alright = false;
                    } else {
                    }
                } else {
                    $alright = false;
                }
            } else {
                    $alright = false;
            }
        }
        return $alright;
    }


    /**
     * Add the VAT class and rate to the database.
     *
     * Check if the rate is a non-negative decimal number,
     * the class string may be empty.
     * @static
     * @access  public
     * @param   string          Name of the VAT class
     * @param   double          Rate of the VAT in percent
     * @global  mixed           Database
     * @return  boolean         True if the values were accepted and
     *                          successfully inserted into the database,
     *                          false otherwise.
     */
    //static
    function addVat($vatClass, $vatRate)
    {
        global $objDatabase;
        $vatRate = doubleval($vatRate);
        if ($vatRate >= 0) {
            $query = "INSERT INTO ".DBPREFIX."module_shop_vat " .
                "(class, percent) VALUES ('$vatClass', $vatRate)";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) return true;
        }
        return false;
    }


    /**
     * Remove the VAT with the given ID from the database
     *
     * @static
     * @access  public
     * @param   integer         The VAT ID
     * @global  mixed           Database
     * @return  boolean         True if the values were accepted and
     *                          successfully inserted into the database,
     *                          false otherwise.
     */
    //static
    function deleteVat($vatId)
    {
        global $objDatabase;
        $vatId = intval($vatId);
        if ($vatId > 0) {
            $query = "DELETE FROM ".DBPREFIX."module_shop_vat WHERE id=$vatId";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) return true;
        }
        return false;
    }


    /**
     * Calculate the tax amount using the given rate (percentage) and price.
     *
     * Note: This function returns the correct amount depending on whether VAT is
     * enabled in the shop, and whether it's included or not.  It will not
     * behave as a "standard" interest function!
     * Also note that the value returned will neither be rounded nor
     * number_format()ted in any way, so prepare it for displaying yourself.
     * See {@link Currency::formatPrice()} for a way to do this.
     * @static
     * @param   double  $rate       The rate in percent (%)
     * @param   double  $price      The (product) price
     * @return  double              Tax amount
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function amount($rate, $price)
    {
        // is the tax enabled at all?
        if ($this->isEnabled()) {
            if ($this->isIncluded()) {
                // gross price; calculate the included VAT amount, like
                // $amount = $price - 100 * $price / (100 + $rate)
                return $price - 100*$price / (100+$rate);
            } else {
                // net price; $rate percent of $price
                return $price * $rate * 0.01;
            }
        } else {
            // tax disabled. leave everything at '' (zero)
            return '0.00';
        }
    }


    /**
     * Return the tax rate associated with the product.
     *
     * If the product is associated with a tax rate, the rate is returned.
     * Otherwise, returns -1.
     * Note: This function returns the VAT rate no matter whether it is
     * enabled in the shop or not.  Check this yourself!
     * @param   double  $productId  The product ID
     * @global  mixed               Database
     * @return  double              The (positive) associated tax rate
     *                              in percent, or -1 if the record could
     *                              not be found.
     */
    function getAssociatedTaxRate($productId)
    {
        global $objDatabase;
        $query = "SELECT percent FROM ".DBPREFIX."module_shop_vat vat ".
                 "INNER JOIN ".DBPREFIX."module_shop_products products ".
                 "ON vat.id = products.vat_id ".
                 "WHERE products.id = ".$productId;
        $objResult = $objDatabase->Execute($query);
        // there must be exactly one match
        if ($objResult && $objResult->RecordCount() == 1) {
            return $objResult->fields['percent'];
        }
        // no or more than one record found
        return -1;
    }


    /**
     * Returns the VAT amount using the default rate for the given net price.
     *
     * Note that the amount returned is not formatted as a currency!
     * See {@link Currency::formatPrice()} for a way to do this.
     * @param   double  $price  The net price
     * @return  double          The VAT amount to add to the net price
     */
    function calculateDefaultTax($price)
    {
        global $objDatabase;
        $amount = $price * $this->vatDefaultRate / 100;
        return $amount;
    }
}

?>
