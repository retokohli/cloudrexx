<?php

/**
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */

/**
 * @ignore
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Settings.class.php';

/**
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @access      public
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 */
class Vat
{
    /**
     * entries look like
     *  VAT ID => array(
     *    'id' => VAT ID,
     *    'rate' => VAT rate (in percent, double),
     *    'class' => VAT class name,
     *    'text_class_id' => VAT class Text ID,
     *  )
     * @var     array   $arrVat         The Vat rate and class array
     * @static
     * @access  private
     */
    private static $arrVat = false;

    /**
     * @var     array   $arrVatEnabled
     *                    Indicates whether VAT is enabled for
     *                    customers or resellers, home or foreign countries
     * Indexed as follows:
     *  $arrVatEnabled[is_home_country ? 1 : 0][is_reseller ? 1 : 0] = is_enabled
     * @static
     * @access  private
     */
    private static $arrVatEnabled = false;

    /**
     * @var     boolean $arrVatIncluded
     *                    Indicates whether VAT is included for
     *                    customers or resellers, home or foreign countries.
     * Indexed as follows:
     *  $arrVatIncluded[is_home_country ? 1 : 0][is_reseller ? 1 : 0] = is_included
     * @static
     * @access  private
     */
    private static $arrVatIncluded = false;

    /**
     * @var     double  $vatDefaultId   The default VAT ID
     * @static
     * @access  private
     */
    private static $vatDefaultId = false;

    /**
     * @var     double  $vatDefaultRate The default VAT rate, determined by the tax_default_id entry
     *                                  in the shop_config table.  See {@see init()},
     *                                  {@see  calculateDefaultTax()}.
     * @static
     * @access  private
     */
    private static $vatDefaultRate;

    /**
     * @var     double  $vatOtherId     The other VAT ID
     *                                  for fees and post & package
     * @static
     * @access  private
     */
    private static $vatOtherId = false;

    /**
     * The current order goes to the shop country if true.
     * Defaults to true.
     * @var     boolean
     */
    private static $is_home_country = true;

    /**
     * The current user is a reseller if true
     * Defaults to false.
     * @var     boolean
     */
    private static $is_reseller = false;


    /**
     * Set the home country flag
     * @param   boolean     True if the shop home country and the
     *                      ship-to country are identical
     */
    static function setIsHomeCountry($is_home_country)
    {
        self::$is_home_country = $is_home_country;
    }
    /**
     * Get the home country flag
     * @return  boolean     True if the shop home country and the
     *                      ship-to country are identical
     */
    static function getIsHomeCountry()
    {
        return self::$is_home_country;
    }

    /**
     * Set the reseller flag
     * @param   boolean     True if the current customer has the
     *                      reseller flag set
     */
    static function isReseller($is_reseller)
    {
        self::$is_reseller = $is_reseller;
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
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @return  void
     * @static
     */
    static function init()
    {
        global $objDatabase;

//        $arrSqlClass = Text::getSqlSnippets('`vat`.`text_class_id`', FRONTEND_LANG_ID);
//        $query = "
//            SELECT `vat`.`id`, `percent`".$arrSqlClass['field']."
//              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_vat as `vat`
//             ".$arrSqlClass['join'];
//        $objResult = $objDatabase->Execute($query);
//        if (!$objResult) return false;
        $query = "SELECT id, percent, class ".
                 "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_vat";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        self::$arrVat = array();
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
//            $text_class_id = $objResult->fields[$arrSqlClass['name']];
//            $strClass = $objResult->fields[$arrSqlClass['text']];
//            // Replace Text in a missing language by another, if available
//            if ($text_class_id && $strClass === null) {
//                $objText = Text::getById($text_class_id, 0);
//                if ($objText)
//                    $objText->markDifferentLanguage(FRONTEND_LANG_ID);
//                    $strClass = $objText->getText();
//            }
            self::$arrVat[$id] = array(
                'id'    => $id,
                'rate'  => $objResult->fields['percent'],
                'class' => $objResult->fields['class'], //$strClass,
//                'text_class_id' => $text_class_id,
            );
            $objResult->MoveNext();
        }
        self::$arrVatEnabled = array(
            // Foreign countries
            0 => array(
                // Customer
                0 => Settings::getValueByName('vat_enabled_foreign_customer'),
                // Reseller
                1 => Settings::getValueByName('vat_enabled_foreign_reseller'),
            ),
            // Home country
            1 => array(
                // Customer
                0 => Settings::getValueByName('vat_enabled_home_customer'),
                // Reseller
                1 => Settings::getValueByName('vat_enabled_home_reseller'),
            ),
        );
        self::$arrVatIncluded = array(
            // Foreign country
            0 => array(
                // Customer
                0 => Settings::getValueByName('vat_included_foreign_customer'),
                // Reseller
                1 => Settings::getValueByName('vat_included_foreign_reseller'),
            ),
            // Home country
            1 => array(
                // Customer
                0 => Settings::getValueByName('vat_included_home_customer'),
                // Reseller
                1 => Settings::getValueByName('vat_included_home_reseller'),
            ),
        );
        self::$vatDefaultId = Settings::getValueByName('vat_default_id');
        self::$vatDefaultRate = self::getRate(self::$vatDefaultId);
        self::$vatOtherId = Settings::getValueByName('vat_other_id');

// NOTE: Temporary fix for VAT rate change on 2011-01-01 in switzerland
        if (   isset(self::$arrVat[10])
            && self::$arrVat[10]['rate'] == 7.6) {
            $date = date('Y-m-d');
            if ($date >= '2011-01-01') {
                self::updateVat(
                    array(10 => self::$arrVat[10]['class']),
                    array(10 => '8.00'));
                self::$arrVat[10]['rate'] = '8.00';
            }
        }

        return true;
    }


    /**
     * Returns an array with all VAT record data for the given VAT ID
     *
     * The array returned contains the following elements:
     *  array(
     *    'id'    => VAT ID,
     *    'class' => VAT class name
     *    'text_class_id' => VAT class Text ID (for updating the record)
     *    'rate'  => VAT rate in percent
     *  )
     * @param   integer   $vatId        The VAT ID
     * @return  array                   The VAT data array on success,
     *                                  false otherwise
     */
    static function getArrayById($vatId)
    {
        if (!is_array(self::$arrVat)) self::init();
        return self::$arrVat[$vatId];
    }


    /**
     * Returns the default VAT rate
     * @return  float   The default VAT rate
     * @static
     */
    static function getDefaultRate()
    {
        if (!is_array(self::$arrVat)) self::init();
        return self::$arrVat[self::$vatDefaultId]['rate'];
    }


    /**
     * Returns the default VAT ID
     * @return  integer The default VAT ID
     * @static
     */
    static function getDefaultId()
    {
        if (!is_array(self::$arrVat)) self::init();
        return self::$vatDefaultId;
    }


    /**
     * Returns the other VAT rate
     * @return  float   The other VAT rate
     * @static
     */
    static function getOtherRate()
    {
        if (!is_array(self::$arrVat)) self::init();
        return self::$arrVat[self::$vatOtherId]['rate'];
    }


    /**
     * Returns the other VAT ID
     * @return  integer The other VAT ID
     * @static
     */
    static function getOtherId()
    {
        if (!is_array(self::$arrVat)) self::init();
        return self::$vatOtherId;
    }


    /**
     * Returns true if VAT is enabled, false otherwise
     * @return  boolean     True if VAT is enabled, false otherwise.
     * @static
     */
    static function isEnabled()
    {
        if (!is_array(self::$arrVat)) self::init();
        return
            (self::$arrVatEnabled[self::$is_home_country ? 1 : 0][self::$is_reseller ? 1 : 0]
                ? true : false
            );
    }


    /**
     * Returns true if VAT is included, false otherwise
     * @return  boolean     True if VAT is included, false otherwise.
     * @static
     */
    static function isIncluded()
    {
        if (!is_array(self::$arrVat)) self::init();
        return
            (self::$arrVatIncluded[self::$is_home_country ? 1 : 0][self::$is_reseller ? 1 : 0]
                ? true : false
            );
    }


    /**
     * Return the array of IDs, rates, and class names
     *
     * The ID keys correspond to the IDs used in the database.
     * Use these to get the respective VAT class.
     * @access  public
     * @return  array           The VAT array
     * @static
     */
    static function getArray()
    {
        if (!is_array(self::$arrVat)) self::init();
        return self::$arrVat;
    }


    /**
     * Returns a HTML dropdown menu with IDs as values and
     * VAT rates as text.
     *
     * The <select>/</select> tags are only added if you also specify a name
     * for the menu as second argument. Otherwise you'll have to add them later.
     * The $attributes are added to the <select> tag if there is one.
     * @access  public
     * @param   integer $selected   The optional preselected VAT ID
     * @param   string  $menuname   The name attribute value for the <select> tag
     * @param   string  $attributes Optional attributes for the <select> tag
     * @return  string              The dropdown menu (with or without <select>...</select>)
     * @static
     */
    static function getShortMenuString($selected='', $menuname='', $attributes='')
    {
        $string = self::getMenuoptions($selected, false);
        if ($menuname) {
            $string =
                '<select name="'.$menuname.'"'.
                ($attributes ? ' '.$attributes : '').
                '>'.$string.'</select>';
        }
        return $string;
    }


    /**
     * Returns a HTML dropdown menu with IDs as values and
     * VAT classes plus rates as text.
     *
     * The <select>/</select> tags are only added if you also specify a name
     * for the menu as second argument. Otherwise you'll have to add them later.
     * The $selectAttributes are added to the <select> tag if there is one.
     * @access  public
     * @param   integer $selected   The optional preselected VAT ID
     * @param   string  $menuname   The name attribute value for the <select> tag
     * @param   string  $attributes Optional attributes for the <select> tag
     * @return  string              The dropdown menu (with or without <select>...</select>)
     * @static
     */
    static function getLongMenuString($selected='', $menuname='', $attributes='')
    {
        $string = self::getMenuoptions($selected, true);
        if ($menuname) {
            $string =
                '<select name="'.$menuname.'"'.
                ($attributes ? ' '.$attributes : '').
                '>'.$string.'</select>';
        }
        return $string;
    }


    /**
     * Return the HTML dropdown menu options code with IDs as values and
     * VAT classes plus rates as text.
     * @access  public
     * @param   integer $selected   The optional preselected VAT ID
     * @param   boolean $flagLong   Include the VAT class name if true
     * @return  string              The dropdown menu options HTML code
     * @static
     */
    static function getMenuoptions($selected='', $flagLong=false)
    {
        if (!is_array(self::$arrVat)) self::init();
        $strMenuoptions = '';
        foreach (self::$arrVat as $id => $arrVat) {
            $strMenuoptions .=
                '<option value="'.$id.'"'.
                ($selected == $id ? ' selected="selected"' : '').'>'.
                ($flagLong ? $arrVat['class'].' ' : '').
                self::format($arrVat['rate']).'</option>';
        }
        return $strMenuoptions;
    }


    /**
     * Return the vat rate for the given VAT ID, if available,
     * or '0.0' if the entry could not be found.
     * @access  public
     * @param   integer $vatId  The VAT ID
     * @return  double          The VAT rate, or '0.0'
     * @static
     */
    static function getRate($vatId)
    {
        if (!is_array(self::$arrVat)) self::init();
        if (isset(self::$arrVat[$vatId]))
            return self::$arrVat[$vatId]['rate'];
        // No entry found.  But some sensible value is required by the Shop.
        return '0.0';
    }


    /**
     * Return the vat class for the given VAT ID, if available,
     * or a warning message if the entry could not be found.
     * @access  public
     * @param   integer $vatId  The VAT ID
     * @global  array
     * @return  string          The VAT class, or a warning
     * @static
     */
    static function getClass($vatId)
    {
        global $_ARRAYLANG;

        if (!is_array(self::$arrVat)) self::init();
        if (isset(self::$arrVat[$vatId]))
            return self::$arrVat[$vatId]['class'];
        // no entry found
        return $_ARRAYLANG['TXT_SHOP_VAT_NOT_SET'];
    }


    /**
     * Return the vat rate with a trailing percent sign
     * for the given percentage.
     * @static
     * @access  public
     * @param   float   $rate   The Vat rate in percent
     * @return  string          The resulting string
     * @static
     */
    static function format($rate)
    {
        return "$rate%";
    }


    /**
     * Return the short vat rate with a trailing percent sign for the given
     * Vat ID, if available, or '0.0%' if the entry could not be found.
     * @access  public
     * @param   integer $vatId  The Vat ID
     * @global  array
     * @return  string          The resulting string
     * @static
     */
    static function getShort($vatId)
    {
        return self::format(self::getRate($vatId));
    }


    /**
     * Return the long VAT rate, including the class, rate and a trailing
     * percent sign for the given Vat ID, if available, or a warning message
     * if the entry could not be found.
     * @access  public
     * @param   integer $vatId  The Vat ID
     * @global  array
     * @return  string          The resulting string
     * @static
     */
    static function getLong($vatId)
    {
        if (!is_array(self::$arrVat)) self::init();
        return
            self::$arrVat[$vatId]['class'].'&nbsp;'.self::getShort($vatId);
    }


    /**
     * Update the VAT entries found in the array arguments
     * in the database.
     *
     * Check if the rates are non-negative decimal numbers, and only
     * updates records that have been changed.
     * Remember to re-init() the Vat class after changing the database table.
     * @access  public
     * @param   array   $vatClasses VAT classes (ID => (string) class)
     * @param   array   $vatRates   VAT rates in percent (ID => rate)
     * @global  ADONewConnection
     * @return  boolean         True if *all* the values were accepted and
     *                          successfully updated in the database,
     *                          false otherwise.
     * @static
     */
    static function updateVat($vatClasses, $vatRates)
    {
        global $objDatabase;

        if (!is_array(self::$arrVat)) self::init();
        foreach ($vatClasses as $id => $class) {
            $rate = $vatRates[$id];
            if (   self::$arrVat[$id]['class'] != $class
                || self::$arrVat[$id]['rate']  != $rate) {
// Note: Text::replace() now returns the ID, not the object!
//                $objText = Text::replace(
//                    $text_class_id, LANG_ID, $class,
//                    MODULE_ID, TEXT_SHOP_VAT_CLASS
//                );
//                if (!$objText) return false;
                $query = "
                    UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_vat
                       SET `percent`=$rate,
                           `class`='".addslashes($class)."'
                     WHERE `id`=$id
                ";
                $objResult = $objDatabase->Execute($query);
                if (!$objResult) return false;
            }
        }
        return true;
    }


    /**
     * Add the VAT class and rate to the database.
     *
     * Checks if the rate is a non-negative decimal number,
     * the class string may be empty.
     * Note that VAT class names are only visible in the backend.  Thus,
     * the backend language is used to display and store those Texts.
     * Remember to re-init() the Vat class after changing the database table.
     * @static
     * @access  public
     * @param   string          Name of the VAT class
     * @param   double          Rate of the VAT in percent
     * @global  ADONewConnection
     * @return  boolean         True if the values were accepted and
     *                          successfully inserted into the database,
     *                          false otherwise.
     */
    static function addVat($vatClass, $vatRate)
    {
        global $objDatabase;

        $vatRate = doubleval($vatRate);
        if ($vatRate >= 0) {
// Note: Text::replace() now returns the ID, not the object!
//            $objText = Text::replace(
//                0, BACKEND_LANG_ID, $vatClass, MODULE_ID, TEXT_SHOP_VAT_CLASS
//            );
//            if (!$objText) return false;
//            $query = "
//                INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_vat (
//                    text_class_id, percent
//                ) VALUES (
//                    ".$objText->getId().", $vatRate
//                )
//            ";
            $query = "
                INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_vat (
                    class, percent
                ) VALUES (
                    '".addslashes($vatClass)."', $vatRate
                )
            ";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) return true;
//            // Rollback and delete the Text
//            Text::deleteById($objText->getId(), BACKEND_LANG_ID);
        }
        return false;
    }


    /**
     * Remove the VAT with the given ID from the database
     *
     * Note that VAT class names are only visible in the backend.  Thus,
     * the backend language is used to display and store those Texts.
     * Remember to re-init() the Vat class after changing the database table.
     * @static
     * @access  public
     * @param   integer         The VAT ID
     * @global  ADONewConnection
     * @return  boolean         True if the values were accepted and
     *                          successfully inserted into the database,
     *                          false otherwise.
     */
    static function deleteVat($vatId)
    {
        global $objDatabase;

        if (!is_array(self::$arrVat)) self::init();
        $vatId = intval($vatId);
        if ($vatId > 0) {
//            if (!Text::deleteById(self::$arrVat[$vatId]['text_class_id']))
//                return false;
            $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_vat WHERE id=$vatId";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) return true;
        }
        return false;
    }


    /**
     * post-2.1
     * Returns the Text ID stored in the VAT record for the given VAT ID
     * @param   integer   $vat_id       The VAT ID
     * @return  integer                 The Text ID on success, false otherwise
     */
    static function getTextClassIdById($vat_id)
    {
        global $objDatabase;

        $query = "
            SELECT `text_class_id`
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_vat
             WHERE `id`=$vat_id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        return $objResult->fields['text_class_id'];
    }


    /**
     * Calculate the VAT amount using the given rate (percentage) and price.
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
    static function amount($rate, $price)
    {
        if (!is_array(self::$arrVat)) self::init();
        // Is the vat enabled at all?
        if (self::isEnabled()) {
            if (self::isIncluded()) {
                // Gross price; calculate the included VAT amount, like
                // $amount = $price - 100 * $price / (100 + $rate)
                return $price - 100*$price / (100+$rate);
            }
            // Net price; $rate percent of $price
            return $price * $rate * 0.01;
        }
        // VAT disabled.  Amount is zero
        return '0.00';
    }


    /**
     * Return the VAT rate associated with the product.
     *
     * If the product is associated with a VAT rate, the rate is returned.
     * Otherwise, returns -1.
     * Note: This function returns the VAT rate no matter whether it is
     * enabled in the shop or not.  Check this yourself!
     * @param   double  $productId  The product ID
     * @global  ADONewConnection
     * @return  double              The (positive) associated vat rate
     *                              in percent, or -1 if the record could
     *                              not be found.
     * @static
     */
    static function getAssociatedTaxRate($productId)
    {
        global $objDatabase;

        $query = "
            SELECT percent FROM ".DBPREFIX."module_shop".MODULE_INDEX."_vat vat
             INNER JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_products products
                ON vat.id=products.vat_id
             WHERE products.id=$productId
        ";
        $objResult = $objDatabase->Execute($query);
        // There must be exactly one match
        if ($objResult && $objResult->RecordCount() == 1)
            return $objResult->fields['percent'];
        // No or more than one record found
        return -1;
    }


    /**
     * Returns the VAT amount using the default rate for the given price.
     *
     * Note that the amount returned is not formatted as a currency.
     * @param   double  $price  The price
     * @return  double          The VAT amount
     * @static
     */
    static function calculateDefaultTax($price)
    {
        return self::amount(self::$vatDefaultRate, $price);
// Old and incorrect:
//        $amount = $price * self::$vatDefaultRate / 100;
//        return $amount;
    }


    /**
     * Returns the VAT amount using the other rate for the given price.
     *
     * Note that the amount returned is not formatted as a currency.
     * @param   double  $price  The price
     * @return  double          The VAT amount
     * @static
     */
    static function calculateOtherTax($price)
    {
        $otherRate = self::getRate(self::$vatOtherId);
        return self::amount($otherRate, $price);
    }

}

?>
