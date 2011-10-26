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
 * Multilanguage text
 * @ignore
 */
require_once ASCMS_CORE_PATH.'/Text.class.php';

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
    /**
     * Text key
     */
    const TEXT_NAME = 'currency_name';

    /**
     * class suffixes for active/inactive currencies
     */
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
    private static $arrCurrency = null;

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

        $arrSqlName = Text::getSqlSnippets(
            '`currency`.`id`', FRONTEND_LANG_ID, 'shop',
            array('name' => self::TEXT_NAME));
        $query = "
            SELECT `currency`.`id`, `currency`.`code`, `currency`.`symbol`,
                   `currency`.`rate`, `currency`.`increment`,
                   `currency`.`ord`,
                   `currency`.`active`, `currency`.`default`, ".
                   $arrSqlName['field']."
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_currencies` AS `currency`".
                   $arrSqlName['join']."
             ORDER BY `currency`.`id` ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $strName = $objResult->fields['name'];
            if ($strName === null) {
                $strName = Text::getById($id, 'shop', self::TEXT_NAME)->content();
            }
            self::$arrCurrency[$objResult->fields['id']] = array(
                'id' => $objResult->fields['id'],
                'code' => $objResult->fields['code'],
                'symbol' => $objResult->fields['symbol'],
                'name' => $strName,
                'rate' => $objResult->fields['rate'],
                'increment' => $objResult->fields['increment'],
                'ord' => $objResult->fields['ord'],
                'active' => $objResult->fields['active'],
                'default' => $objResult->fields['default'],
            );
            if ($objResult->fields['default'])
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
        return true;
    }


    /**
     * Resets the $arrCurrency class array to null to enforce
     * reinitialisation
     *
     * Call this after changing the database table
     */
    static function reset()
    {
        self::$arrCurrency = null;
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
        $increment = self::$arrCurrency[self::$activeCurrencyId]['increment'];
        return self::formatPrice(round($price*$rate/$increment)*$increment);
        ;
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
            return self::formatPrice($price);
        }
        $rate = self::$arrCurrency[self::$activeCurrencyId]['rate'];
        $defaultRate = self::$arrCurrency[self::$defaultCurrencyId]['rate'];
        $defaultIncrement = self::$arrCurrency[self::$defaultCurrencyId]['increment'];
        return self::formatPrice(round(
            $price*$defaultRate/$rate/$defaultIncrement)*$defaultIncrement);
    }


    /**
     * Returns the formatted amount in a non-localized notation
     * rounded to two decimal places,
     * using no thousands, and '.' as decimal separator.
     *
     * The optional $length is inserted into the sprintf()
     * format string and determines the maximum length of the number.
     * If present, the optional $padding character is inserted into the
     * sprintf() format string.
     * @todo    Localize!  Create language and country dependant
     *          settings in the database, and make this behave accordingly.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @static
     * @param   double  $price    The amount
     * @param   string  $length   The optional number length
     * @param   string  $padding  The optional padding
     * @return  double            The formatted amount
     */
    static function formatPrice($price, $length='', $padding='')
    {
        return sprintf('%'.$padding.$length.'.2f', $price);
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
        $uri = $_SERVER['REQUEST_URI'];
        Html::stripUriParam($uri, 'currency');
        foreach (self::$arrCurrency as $id => $arrCurrency) {
            if (!$arrCurrency['active']) continue;
            $strCurNavbar .=
                '<a class="'.($id == self::$activeCurrencyId
                    ? self::STYLE_NAME_ACTIVE : self::STYLE_NAME_INACTIVE
                ).
                '" href="'.htmlspecialchars(
                    $uri, ENT_QUOTES, CONTREXX_CHARSET
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
     *
     * This method will fail if you try to delete the default Currency.
     * @return  boolean             Null if nothing was deleted,
     *                              boolean true upon deleting the currency
     *                              successfully, or false otherwise
     */
    static function delete()
    {
        global $objDatabase;

        if (empty($_GET['currencyId'])) return null;
        self::init();
        $currency_id = $_GET['currencyId'];
        if ($currency_id == self::$defaultCurrencyId) return false;
        if (!Text::deleteById($currency_id, 'shop', self::TEXT_NAME))
            return false;
        $objResult = $objDatabase->Execute("
            DELETE FROM `".DBPREFIX."module_shop".MODULE_INDEX."_currencies`
             WHERE `id`=$currency_id");
        if (!$objResult) return false;
        unset(self::$arrCurrency[$currency_id]);
        $objDatabase->Execute("OPTIMIZE TABLE `".DBPREFIX."module_shop".MODULE_INDEX."_currencies`");
        return true;
    }


    /**
     * Add a new currency
     * @return  boolean             Null if nothing was added,
     *                              boolean true upon adding the currency
     *                              successfully, or false otherwise
     * @static
     */
    static function add()
    {
        global $objDatabase, $_ARRAYLANG;

        if (empty($_POST['currencyNameNew'])
         || empty($_POST['currencyCodeNew'])
         || empty($_POST['currencySymbolNew'])
         || empty($_POST['currencyRateNew'])
         || empty($_POST['currencyIncrementNew'])) {
            Message::error($_ARRAYLANG['TXT_SHOP_CURRENCY_INCOMPLETE']);
            return null;
        }
        $code = contrexx_input2raw($_POST['currencyCodeNew']);
        foreach (self::$arrCurrency as $id => $currency) {
            if ($code == $currency['code']) {
                Message::error(sprintf(
                    $_ARRAYLANG['TXT_SHOP_CURRENCY_EXISTS'],
                    $code));
                return null;
            }
        }
        $active = (empty($_POST['currencyActiveNew']) ? 0 : 1);
        $default = (empty($_POST['currencyDefaultNew']) ? 0 : 1);
        $query = "
            INSERT INTO `".DBPREFIX."module_shop".MODULE_INDEX."_currencies` (
                `code`, `symbol`, `rate`, `increment`, `active`
            ) VALUES (
                '".contrexx_raw2db($code)."',
                '".contrexx_input2db($_POST['currencySymbolNew'])."',
                ".floatval($_POST['currencyRateNew']).",
                ".floatval($_POST['currencyIncrementNew']).",
                $active
            )";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $currency_id = $objDatabase->Insert_Id();
        if (!Text::replace($currency_id, FRONTEND_LANG_ID, 'shop',
            self::TEXT_NAME, contrexx_input2raw($_POST['currencyNameNew']))) {
            return false;
        }
        if ($default) {
            return self::setDefault($currency_id);
        }
        return true;
    }


    /**
     * Update currencies
     * @return  boolean             Null if nothing was changed,
     *                              boolean true upon storing everything
     *                              successfully, or false otherwise
     * @static
     */
    static function update()
    {
        global $objDatabase;

        if (empty($_POST['currency'])) return null;
        self::init();
        $default_id = (isset($_POST['currencyDefault'])
            ? intval($_POST['currencyDefault']) : self::$defaultCurrencyId);
        $changed = false;
        foreach ($_POST['currencyCode'] as $currency_id => $code) {
            $code = contrexx_input2raw($code);
            $name = contrexx_input2raw($_POST['currencyName'][$currency_id]);
            $symbol = contrexx_input2raw($_POST['currencySymbol'][$currency_id]);
            $rate = floatval($_POST['currencyRate'][$currency_id]);
            $increment = floatval($_POST['currencyIncrement'][$currency_id]);
            $default = ($default_id == $currency_id ? 1 : 0);
            $active = (empty ($_POST['currencyActive'][$currency_id]) ? 0 : 1);
            // The default currency must be activated
            $active = ($default ? 1 : $active);
            if (   $code == self::$arrCurrency[$currency_id]['code']
                && $name == self::$arrCurrency[$currency_id]['name']
                && $symbol == self::$arrCurrency[$currency_id]['symbol']
                && $rate == self::$arrCurrency[$currency_id]['rate']
                && $increment == self::$arrCurrency[$currency_id]['increment']
// NOTE: The ordinal is implemented, but not used yet
//                && $ord == self::$arrCurrency[$currency_id]['ord']
                && $active == self::$arrCurrency[$currency_id]['active']
                && $default == self::$arrCurrency[$currency_id]['default']) {
                continue;
            }
            $query = "
                UPDATE `".DBPREFIX."module_shop".MODULE_INDEX."_currencies`
                   SET `code`='".contrexx_raw2db($code)."',
                       `symbol`='".contrexx_raw2db($symbol)."',
                       `rate`=$rate,
                       `increment`=$increment,
                       `active`=$active
                 WHERE `id`=$currency_id";
            if (!$objDatabase->Execute($query)) return false;
            $changed = true;
            if (!Text::replace($currency_id, FRONTEND_LANG_ID,
                'shop', self::TEXT_NAME,
                contrexx_input2raw($_POST['currencyName'][$currency_id]))) {
                return false;
            }
        } // end foreach
        if ($changed) {
            return self::setDefault($default_id);
        }
        return null;
    }


    static function setDefault($currency_id)
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            UPDATE `".DBPREFIX."module_shop".MODULE_INDEX."_currencies`
               SET `default`=0
             WHERE `id`!=$currency_id");
        if (!$objResult) return false;
        $objResult = $objDatabase->Execute("
            UPDATE `".DBPREFIX."module_shop".MODULE_INDEX."_currencies`
               SET `default`=1
             WHERE `id`=$currency_id");
        if (!$objResult) return false;
        return true;
    }


    /**
     * Handles database errors
     *
     * Also migrates old Currency names to the Text class,
     * and inserts default Currencyes if necessary
     * @return  boolean     false       Always!
     * @throws  Update_DatabaseException
     */
    static function errorHandler()
    {
        global $objDatabase;
        require_once(ASCMS_DOCUMENT_ROOT.'/update/UpdateUtil.php');

//DBG::activate(DBG_DB_FIREPHP);
//DBG::log("Currency::errorHandler(): Entered");

        Text::errorHandler();

        $table_name = DBPREFIX.'module_shop'.MODULE_INDEX.'_currencies';
        $table_structure = array(
            'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
            'code' => array('type' => 'CHAR(3)', 'notnull' => true, 'default' => ''),
            'symbol' => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => ''),
            'rate' => array('type' => 'DECIMAL(10,6)', 'unsigned' => true, 'notnull' => true, 'default' => '1.000000'),
            'increment' => array('type' => 'DECIMAL(3,2)', 'unsigned' => true, 'notnull' => true, 'default' => '0.01'),
            'ord' => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'renamefrom' => 'sort_order'),
            'active' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'renamefrom' => 'status'),
            'default' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'renamefrom' => 'is_default'),
        );
        $table_index = array();

        if (UpdateUtil::table_exist($table_name)) {
            if (UpdateUtil::column_exist($table_name, 'name')) {
                // Migrate all Currency names to the Text table first
                Text::deleteByKey('shop', self::TEXT_NAME);
                $query = "
                    SELECT `id`, `code`, `name`
                      FROM `$table_name`";
                $objResult = UpdateUtil::sql($query);
                if (!$objResult) {
                    throw new Update_DatabaseException(
                       "Failed to query Currency names", $query);
                }
                while (!$objResult->EOF) {
                    $id = $objResult->fields['id'];
                    $name = $objResult->fields['name'];
                    if (!Text::replace($id, FRONTEND_LANG_ID,
                        'shop', self::TEXT_NAME, $name)) {
                        throw new Update_DatabaseException(
                           "Failed to migrate Currency name '$name'");
                    }
                    $objResult->MoveNext();
                }
            }
            UpdateUtil::table($table_name, $table_structure, $table_index);
            return false;
        }

        // If the table did not exist, insert defaults
        $arrCurrencies = array(
            'Schweizer Franken' => array('CHF', 'sFr.', 1.000000, 1, 1, 1),
// TODO: I dunno if I'm just lucky, or if this will work with any charsets
// configured for PHP and mySQL?
// Anyway, neither entering the Euro-E literally nor various hacks involving
// utf8_decode()/utf8_encode() did the trick...
            'Euro' => array('EUR', html_entity_decode("&euro;"), 1.180000, 2, 1, 0),
            'United States Dollars' => array('USD', '$', 0.880000, 3, 1, 0),
        );

        // There is no previous version, so don't use DbTools::table()
        if (!UpdateUtil::create_table($table_name, $table_structure)) {
            throw new Update_DatabaseException(
                "Failed to create Currency table");
        }
        // And there aren't even records to migrate, so
        foreach ($arrCurrencies as $name => $arrCurrency) {
            $query = "
                INSERT INTO `contrexx_module_shop_currencies` (
                    `code`, `symbol`, `rate`, `increment`,
                    `ord`, `active`, `default`
                ) VALUES (
                    '".join("','", $arrCurrency)."'
                )";
            $objResult = UpdateUtil::sql($query);
            if (!$objResult) {
                throw new Update_DatabaseException(
                    "Failed to insert default Currencies");
            }
            $id = $objDatabase->Insert_ID();
            if (!Text::replace($id, FRONTEND_LANG_ID, 'shop',
                self::TEXT_NAME, $name)) {
                throw new Update_DatabaseException(
                    "Failed to add Text for default Currency name '$name'");
            }
        }

        // Always
        return false;
    }

}

?>
