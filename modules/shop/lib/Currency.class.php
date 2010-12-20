<?php

/**
 * Currency class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  module_shop
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.1.0
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Currency related static methods
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  module_shop
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.1.0
 */
class Currency
{
    const STYLE_NAME_INACTIVE = 'inactive';
    const STYLE_NAME_ACTIVE   = 'active';

    /**
     * Array of available currencies (default null).
     *
     * Use {@link getCurrencyArray()} to access it from outside this class.
     * @access  private
     * @static
     * @var     array
     */
    private static $arrCurrency = false;

    /**
     * Active currency object id (default null).
     *
     * Use {@link getActiveCurrencyId()} to access it from outside this class.
     * @access  private
     * @static
     * @var     integer
     */
    private static $activeCurrencyId = false;

    /**
     * Default currency object id (defaults to null).
     *
     * Use {@link getDefaultCurrencyId()} to access it from outside this class.
     * @access  private
     * @static
     * @var     integer
     */
    private static $defaultCurrencyId = false;


    /**
     * Initialize currencies
     *
     * Sets up the Currency array, and picks the selected Currency from the
     * 'currency' request parameter, if available.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @static
     */
    static function init($active_currency_id=0)
    {
        global $objDatabase;

//        $arrSqlName = Text::getSqlSnippets(
//            '`currency`.`text_name_id`', FRONTEND_LANG_ID,
//            MODULE_ID, TEXT_SHOP_CURRENCIES_NAME
//        );
        $query = "
            SELECT `currency`.`id`, `currency`.`code`, `currency`.`symbol`,
                   `currency`.`rate`, `currency`.`sort_order`,
                   `currency`.`status`, `currency`.`is_default`,
                   `currency`.`name`".
//                   $arrSqlName['field']."
            "
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_currencies` AS `currency`".
//                   $arrSqlName['join']."
            "
             ORDER BY `currency`.`id` ASC
        ";
        $objResult = $objDatabase->Execute($query);
        while (!$objResult->EOF) {
//            $text_name_id = $objResult->fields[$arrSqlName['name']];
//            $strName = $objResult->fields[$arrSqlName['text']];
//            if ($strName === null) {
//                $objText = Text::getById($text_name_id, 0);
//                $objText->markDifferentLanguage(FRONTEND_LANG_ID);
//                $strName = $objText->getText();
//            }
            self::$arrCurrency[$objResult->fields['id']] = array(
                'id' => $objResult->fields['id'],
                'code' => $objResult->fields['code'],
                'symbol' => $objResult->fields['symbol'],
                'name' => $objResult->fields['name'], //$strName,
//                'text_name_id' => $text_name_id,
                'rate' => $objResult->fields['rate'],
                'sort_order' => $objResult->fields['sort_order'],
                'status' => $objResult->fields['status'],
                'is_default' => $objResult->fields['is_default']
            );
            if ($objResult->fields['is_default'])
                self::$defaultCurrencyId = $objResult->fields['id'];
            $objResult->MoveNext();
        }

        if (isset($_REQUEST['currency'])) {
            $currency_id = intval($_REQUEST['currency']);
            $_SESSION['shop']['currencyId'] =
                (isset(self::$arrCurrency[$currency_id])
                    ? $currency_id : self::$defaultCurrencyId
                );
        }
        if (!empty($active_currency_id)) {
            $_SESSION['shop']['currencyId'] =
                (isset(self::$arrCurrency[$active_currency_id])
                    ? $active_currency_id : self::$defaultCurrencyId
                );

        }
        if (!isset($_SESSION['shop']['currencyId'])) {
            $_SESSION['shop']['currencyId'] = self::$defaultCurrencyId;
        }
        self::$activeCurrencyId = intval($_SESSION['shop']['currencyId']);
    }


    /**
     * Returns the currency array
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @access  public
     * @static
     * @return  array   The currency array
     */
    static function getCurrencyArray()
    {
        if (!is_array(self::$arrCurrency)) self::init();
        return self::$arrCurrency;
    }


    /**
     * Returns the default currency ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @access  public
     * @static
     * @return  integer     The ID of the default currency
     */
    static function getDefaultCurrencyId()
    {
        if (!is_array(self::$arrCurrency)) self::init();
        return self::$defaultCurrencyId;
    }


    /**
     * Returns the default currency symbol
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @access  public
     * @static
     * @return  string      The string representing the default currency
     */
    static function getDefaultCurrencySymbol()
    {
        if (!is_array(self::$arrCurrency)) self::init();
        return self::$arrCurrency[self::$defaultCurrencyId]['symbol'];
    }


    /**
     * Returns the default currency code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @access  public
     * @static
     * @return  string      The string representing the default currency code
     */
    static function getDefaultCurrencyCode()
    {
        if (!is_array(self::$arrCurrency)) self::init();
        return self::$arrCurrency[self::$defaultCurrencyId]['code'];
    }


    /**
     * Returns the active currency ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @access  public
     * @static
     * @return  integer     The ID of the active currency
     */
    static function getActiveCurrencyId()
    {
        if (!is_array(self::$arrCurrency)) self::init();
        return self::$activeCurrencyId;
    }


    /**
     * Set the active currency ID
     * @param   integer     $currency_id    The active Currency ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @access  public
     * @static
     */
    static function setActiveCurrencyId($currency_id)
    {
        if (!is_array(self::$arrCurrency)) self::init($currency_id);
        self::$activeCurrencyId = $currency_id;
    }


    /**
     * Returns the active currency symbol
     *
     * This is a custom Currency name that does not correspond to any
     * ISO standard, like "sFr.", or "Euro".
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @access  public
     * @static
     * @return  string      The string representing the active currency
     */
    static function getActiveCurrencySymbol()
    {
        if (!is_array(self::$arrCurrency)) self::init();
        return self::$arrCurrency[self::$activeCurrencyId]['symbol'];
    }


    /**
     * Returns the active currency code
     *
     * This usually corresponds to the ISO 4217 code for the Currency,
     * like CHF, or USD.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @access  public
     * @static
     * @return  string      The string representing the active currency code
     */
    static function getActiveCurrencyCode()
    {
        if (!is_array(self::$arrCurrency)) self::init();
        return self::$arrCurrency[self::$activeCurrencyId]['code'];
    }


    /**
     * Returns the currency symbol for the given ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @access  public
     * @static
     * @return  string      The string representing the active currency
     */
    static function getCurrencySymbolById($currency_id)
    {
        if (!is_array(self::$arrCurrency)) self::init();
        return self::$arrCurrency[$currency_id]['symbol'];
    }


    /**
     * Returns the currency code for the given ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @access  public
     * @static
     * @return  string      The string representing the active currency code
     */
    static function getCurrencyCodeById($currency_id)
    {
        if (!is_array(self::$arrCurrency)) self::init();
        return self::$arrCurrency[$currency_id]['code'];
    }


    /**
     * Returns the amount converted from the default to the active currency
     *
     * Note that the amount is rounded to five cents before formatting.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @access  public
     * @static
     * @param   double  $price  The amount in default currency
     * @return  string          Formatted amount in the active currency
     * @todo    In case that the {@link formatPrice()} function is localized,
     *          the returned value *MUST NOT* be treated as a number anymore!
     */
    static function getCurrencyPrice($price)
    {
        if (!is_array(self::$arrCurrency)) self::init();
        $rate = self::$arrCurrency[self::$activeCurrencyId]['rate'];
        // getting 0.05 increments
        return Currency::formatPrice(round(20*$price*$rate)/20);
    }


    /**
     * Returns the amount converted from the active to the default currency
     *
     * Note that the amount is rounded to five cents before formatting.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @access  public
     * @static
     * @param   double  $price  The amount in active currency
     * @return  string          Formated amount in default currency
     * @todo    In case that the {@link formatPrice()} function is localized,
     *          the returned value *MUST NOT* be treated as a number anymore!
     */
    static function getDefaultCurrencyPrice($price)
    {
        if (!is_array(self::$arrCurrency)) self::init();
        if (self::$activeCurrencyId == self::$defaultCurrencyId) {
            return Currency::formatPrice($price);
        } else {
            $rate = self::$arrCurrency[self::$activeCurrencyId]['rate'];
            $defaultRate = self::$arrCurrency[self::$defaultCurrencyId]['rate'];
            // getting 0.05 increments
            return Currency::formatPrice(round(20*$price*$defaultRate/$rate)/20);
        }
    }


    /**
     * Returns the formatted amount in a non-localized notation
     * rounded to two decimal places,
     * using no thousands, and '.' as decimal separator.
     *
     * The optional $padding character is inserted into the sprintf()
     * format string.
     * @todo    Localize!  Create language and country dependant
     *          settings in the database, and make this behave accordingly.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @static
     * @param   double  $price    The amount
     * @param   string  $padding  The optional padding
     * @return  double            The formatted amount
     */
    static function formatPrice($price, $padding='')
    {
        return sprintf('%'.$padding.'9.2f', $price);
//        return number_format($price, 2, '.', '');
    }


    /**
     * Returns the amount in a non-localized notation in cents,
     * rounded to one cent.
     *
     * Note that the amount argument is supposed to be in decimal format
     * with decimal separator and the appropriate number of decimal places,
     * as returned by {@link formatPrice()}, but it also works for integer
     * values like the ones returned by itself.
     * Removes underscores (_) as well decimal (.) and thousands (') separators,
     * and replaces dashes (-) by zeroes (0).
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @static
     * @param   string    $amount   The amount in decimal format
     * @return  integer             The amount in cents, rounded to one cent
     * @todo    Test!
     * @since   2.1.0
     * @version 2.1.0
     */
    static function formatCents($amount)
    {
        $amount = preg_replace('/[_\\.\']/', '', $amount);
        $amount = preg_replace('/-/', '0', $amount);
        return intval($amount);
    }


    /**
     * Set up the Currency navbar
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @return  string            The HTML code for the Currency navbar
     * @access  public
     * @static
     */
    static function getCurrencyNavbar()
    {
        if (!is_array(self::$arrCurrency)) self::init();
        $strCurNavbar = '';
        foreach (self::$arrCurrency as $id => $arrCurrency) {
            if (!$arrCurrency['status']) continue;
            $strCurNavbar .=
                '<a class="'.($id == self::$activeCurrencyId
                    ? self::STYLE_NAME_ACTIVE : self::STYLE_NAME_INACTIVE
                ).
                '" href="'.htmlspecialchars(
                    $_SERVER['REQUEST_URI'], ENT_QUOTES, CONTREXX_CHARSET
                ).
                '&amp;currency='.$id.'" title="'.$arrCurrency['code'].'">'.
                $arrCurrency['code'].
                '</a>';
        }
        return $strCurNavbar;
    }


    /**
     * Return the currency code for the ID given
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @static
     * @param   integer   $currencyId   The currency ID
     * @return  mixed                   The currency code on success,
     *                                  false otherwise
     * @global  ADONewConnection
     */
    static function getCodeById($currencyId)
    {
        if (!is_array(self::$arrCurrency)) self::init();
        if (isset(self::$arrCurrency[$currencyId]['code']))
            return self::$arrCurrency[$currencyId]['code'];
        return false;
    }


    /**
     * Return the currency symbol for the ID given
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @static
     * @param   integer   $currencyId   The currency ID
     * @return  mixed                   The currency symbol on success,
     *                                  false otherwise
     * @global  ADONewConnection
     */
    static function getSymbolById($currencyId)
    {
        if (!is_array(self::$arrCurrency)) self::init();
        if (isset(self::$arrCurrency[$currencyId]['symbol']))
            return self::$arrCurrency[$currencyId]['symbol'];
        return false;
    }



    /**
     * Store the currencies as present in the post request
     *
     * See {@link deleteCurrency()}, {@link addCurrency()}, and
     * {@link updateCurrencies()}.
     * @return  boolean             The empty string if nothing was changed,
     *                              boolean true upon storing everything
     *                              successfully, or false otherwise
     */
    static function store()
    {
        if (empty(self::$arrCurrency)) self::init();
        $total_result = true;
        $result = self::deleteCurrency();
        if ($result !== '') $total_result &= $result;
        $result = self::addCurrency();
        if ($result !== '') $total_result &= $result;
        $result = self::updateCurrencies();
        if ($result !== '') $total_result &= $result;
        // Reinit after storing, or the user won't see any changes at first
        self::init();
        return $total_result;
    }


    /**
     * Deletes a currency
     * @return  boolean             The empty string if nothing was changed,
     *                              boolean true upon deleting the currency
     *                              successfully, or false otherwise
     */
    static function deleteCurrency()
    {
        global $objDatabase;

        if (empty($_GET['currencyId'])) return '';
        $currency_id = $_GET['currencyId'];
        if ($currency_id == self::$defaultCurrencyId) return false;
//        $text_id = self::$arrCurrency[$currency_id]['text_name_id'];
//        if (!Text::deleteById($text_id)) return false;
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_currencies
             WHERE id=$currency_id");
        if (!$objResult) return false;
        unset(self::$arrCurrency[$currency_id]);
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_currencies");
        return true;
    }


    /**
     * Add a new currency
     * @return  boolean             The empty string if nothing was added,
     *                              boolean true upon adding the currency
     *                              successfully, or false otherwise
     */
    function addCurrency()
    {
        global $objDatabase;

        if (empty($_POST['currencyNameNew'])) return '';

        $_POST['currencyActiveNew']  =
            (empty($_POST['currencyActiveNew'])  ? 0 : 1);
        $_POST['currencyDefaultNew'] =
            (empty($_POST['currencyDefaultNew']) ? 0 : 1);

//        $objText = new Text(
//            $_POST['currencyNameNew'], FRONTEND_LANG_ID,
//            MODULE_ID, TEXT_SHOP_CURRENCIES_NAME
//        );
//        if (!$objText->store()) return false;
//        $query = "
//            INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_currencies (
//                code, symbol, text_name_id, rate, status, is_default
//            ) VALUES (
//                '".addslashes($_POST['currencyCodeNew'])."',
//                '".addslashes($_POST['currencySymbolNew'])."',
//                ".$objText->getId().",
//                '".addslashes($_POST['currencyRateNew'])."',
//                ".intval($_POST['currencyActiveNew']).",
//                ".intval($_POST['currencyDefaultNew'])."
//            )
//        ";
        $query = "
            INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_currencies (
                code, symbol, name, rate, status, is_default
            ) VALUES (
                '".addslashes($_POST['currencyCodeNew'])."',
                '".addslashes($_POST['currencySymbolNew'])."',
                '".addslashes($_POST['currencyNameNew'])."',
                '".addslashes($_POST['currencyRateNew'])."',
                ".$_POST['currencyActiveNew'].",
                ".$_POST['currencyDefaultNew']."
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $currency_id = $objDatabase->Insert_Id();
        if ($_POST['currencyDefaultNew']) {
            $objResult = $objDatabase->Execute("
                UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_currencies
                   SET is_default=0
                 WHERE id!=$currency_id
            ");
            if (!$objResult) return false;
        }
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_currencies");
        return true;
    }


    /**
     * Update currencies
     * @return  boolean             The empty string if nothing was changed,
     *                              boolean true upon storing everything
     *                              successfully, or false otherwise
     */
    function updateCurrencies()
    {
        global $objDatabase;

        if (empty($_POST['currency'])) return '';
        $default_id =
            (isset($_POST['currencyDefault'])
                ? $_POST['currencyDefault']
                : self::$defaultCurrencyId
            );
        foreach ($_POST['currencyCode'] as $currency_id => $code) {
            $is_default = ($default_id == $currency_id ? 1 : 0);
            $is_active = (isset($_POST['currencyActive'][$currency_id]) ? 1 : 0);
            // The default currency must be activated
            $is_active = ($is_default ? 1 : $is_active);
// Note: Text::replace() now returns the ID, not the object!
//            $objText = Text::replace(
//                self::$arrCurrency[$currency_id]['text_name_id'],
//                FRONTEND_LANG_ID,
//                $_POST['currencyName'][$currency_id],
//                MODULE_ID, TEXT_SHOP_CURRENCIES_NAME
//            );
//            if (!$objText) return false;
            $query = "
                UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_currencies
                   SET code='".addslashes($code)."',
                       symbol='".addslashes($_POST['currencySymbol'][$currency_id])."',
                       name='".addslashes($_POST['currencyName'][$currency_id])."', ".
//                       text_name_id=".$objText->getId().",
                "
                       rate='".addslashes($_POST['currencyRate'][$currency_id])."',
                       status=$is_active,
                       is_default=$is_default
                 WHERE id=$currency_id
            ";
            if (!$objDatabase->Execute($query)) return false;
        } // end foreach
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_currencies");
        return true;
    }

}

?>
