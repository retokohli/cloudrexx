<?php

/**
 * Payment service manager
 * @package     contrexx
 * @copyright   CONTREXX CMS - COMVATION AG
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 * @version     2.1.0
 */

/**
 * Payment service manager
 * @package     contrexx
 * @copyright   CONTREXX CMS - COMVATION AG
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 * @version     2.1.0
 */
class Payment
{
    /**
     * Text keys
     */
    const TEXT_NAME = "shop_payment_name";

    /**
     * Array of available payment service data
     * @var     array
     * @access  private
     * @static
     */
    private static $arrPayments = null;


    /**
     * Set up the payment array
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @since   2.1.0
     */
    static function init()
    {
        global $objDatabase;

        $arrSqlName = Text::getSqlSnippets(
            '`payment`.`text_name_id`', FRONTEND_LANG_ID
        );
        $query = "
            SELECT `payment`.`id`, `payment`.`processor_id`,
                   `payment`.`fee`, `payment`.`free_from`,
                   `payment`.`ord`, `payment`.`active`".
                   $arrSqlName['field']."
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_payment` AS `payment`".
                   $arrSqlName['join']."
             ORDER BY id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) { return self::errorHandler(); }
        self::$arrPayments = array();
        if ($objResult->EOF) return true;
        while ($objResult && !$objResult->EOF) {
            $text_name_id = $objResult->fields[$arrSqlName['name']];
            $strName = $objResult->fields[$arrSqlName['text']];
            if ($strName === null) {
                $objText = Text::getById($text_name_id, 0);
                $objText->markDifferentLanguage(FRONTEND_LANG_ID);
                $strName = $objText->getText();
            }
            self::$arrPayments[$objResult->fields['id']] = array(
                'id'           => $objResult->fields['id'],
                'processor_id' => $objResult->fields['processor_id'],
                'name'         => $strName,
                'text_name_id' => $text_name_id,
                'fee'          => $objResult->fields['fee'],
                'free_from'    => $objResult->fields['free_from'],
                'ord'          => $objResult->fields['ord'],
                'active'       => $objResult->fields['active'],
            );
            $objResult->MoveNext();
        }
        return true;
    }


    /**
     * Returns the array of available Payment service data
     * @see     Payment::init()
     * @return  array
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @since   2.1.0
     */
    static function getArray()
    {
        if (empty(self::$arrPayments)) self::init();
        return self::$arrPayments;
    }


    /**
     * Returns the array of available Payment names
     *
     * The array is indexed by the Payment IDs.
     * @see     Payment::init()
     * @return  array           The array of Payment names
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @since   3.0.0
     */
    static function getNameArray()
    {
        if (is_null(self::$arrPayments)) self::init();
        $arrPaymentName = array();
        foreach (self::$arrPayments as $payment_id => $arrPayment) {
            $arrPaymentName[$payment_id] = $arrPayment['name'];
        }
        return $arrPaymentName;
    }


    /**
     * Returns the named property for the given Payment service
     * @param   integer   $payment_id       The Payment service ID
     * @param   string    $property_name    The property name
     * @return  string                      The property value
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @since   2.1.0
     */
    static function getProperty($payment_id, $property_name)
    {
        if (is_null(self::$arrPayments)) self::init();
        return
            (   isset(self::$arrPayments[$payment_id])
             && isset(self::$arrPayments[$payment_id][$property_name])
              ? self::$arrPayments[$payment_id][$property_name]
              : false
            );
    }


    /**
     * Returns the countries related payment ID array.
     *
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @param    integer $countryId         The country ID
     * @param    array   $arrCurrencies     The currencies array
     * @return   array                      Array of payment IDs, like:
     *                                      array( index => paymentId )
     */
    static function getCountriesRelatedPaymentIdArray($countryId, $arrCurrencies)
    {
        global $objDatabase;

        if (is_null(self::$arrPayments)) self::init();
        require_once ASCMS_MODULE_PATH.'/shop/payments/paypal/Paypal.class.php';
        $arrAcceptedCurrencyCodes = array();
        $arrPaypalAcceptedCurrencyCodes = PayPal::getAcceptedCurrencyCodeArray();
        foreach ($arrCurrencies as $arrCurrency) {
            if (   $arrCurrency['status']
                && in_array($arrCurrency['code'],
                            $arrPaypalAcceptedCurrencyCodes)
            ) {
                array_push($arrAcceptedCurrencyCodes, $arrCurrency['code']);
            }
        }

        $arrPaymentId = array();
        $query = "
            SELECT DISTINCT `p`.`payment_id`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries` AS `c`
             INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_zones` AS `z`
                ON `c`.`zone_id`=`z`.`id`
             INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment` AS `p`
                ON `z`.`id`=`p`.`zone_id`
             WHERE `c`.`country_id`=".intval($countryId)."
               AND `z`.`active`=1";
        $objResult = $objDatabase->Execute($query);
        while ($objResult && !$objResult->EOF) {
            if (   isset(self::$arrPayments[$objResult->fields['payment_id']])
                && self::$arrPayments[$objResult->fields['payment_id']]['active']
                && (   self::$arrPayments[$objResult->fields['payment_id']]['processor_id'] != 2
                    || count($arrAcceptedCurrencyCodes) > 0)
            ) {
                $arrPaymentId[] = $objResult->fields['payment_id'];
            }
            $objResult->MoveNext();
        }
        return $arrPaymentId;
    }


    /**
     * Return HTML code for the payment dropdown menu
     * @param   string  $selectedId     Optional preselected payment ID
     * @param   string  $onchange       Optional onchange function
     * @param   integer $countryId      Country ID
     * @return  string                  HTML code for the dropdown menu
     * @global  array   $_ARRAYLANG     Language array
     */
    static function getPaymentMenu($selectedId=0, $onchange='', $countryId=0)
    {
        global $_ARRAYLANG;

        $menu =
            '<select name="paymentId"'.
            ($onchange ? ' onchange="'.$onchange.'"' : '').'>'.
            (intval($selectedId) == 0 && $onchange
                ? '<option value="0" selected="selected">'.
                  $_ARRAYLANG['TXT_SHOP_PAYMENT_PLEASE_SELECT'].
                  "</option>\n"
                : ''
            ).
            self::getPaymentMenuoptions($selectedId, $countryId).
            "</select>\n";
        return $menu;
    }


    /**
     * Return HTML code for the payment dropdown menu options
     * @param   string  $selectedId     Optional preselected payment ID
     * @param   integer $countryId      Country ID
     * @return  string                  HTML code for the dropdown menu options
     * @global  array   $_ARRAYLANG     Language array
     */
    static function getPaymentMenuoptions($selectedId=0, $countryId=0)
    {
        global $_ARRAYLANG;

        if (is_null(self::$arrPayments)) self::init();
        // Get Payment IDs available in the selected country, if any, or all.
        $arrPaymentId =
            ($countryId
                ? self::getCountriesRelatedPaymentIdArray(
                    $countryId, Currency::getCurrencyArray())
                : array_keys(self::$arrPayments)
            );
        $strMenuoptions =
            (empty($selectedId)
              ? '<option value="" selected="selected">'.
                $_ARRAYLANG['TXT_SHOP_PLEASE_SELECT'].
                "</option>\n"
              : ''
            );
// TODO: Use Html::getOptions()
        foreach ($arrPaymentId as $id) {
            $strMenuoptions .=
                '<option value="'.$id.'"'.
                ($id == $selectedId ? HTML_ATTRIBUTE_SELECTED : '').'>'.
                self::$arrPayments[$id]['name'].
                "</option>\n";
        }
        return $strMenuoptions;
    }


    /**
     * Get the payment name for the ID given
     * @static
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @param   integer   $paymentId      The payment ID
     * @return  mixed                     The payment name on success,
     *                                    false otherwise
     * @since   1.2.1
     */
    static function getNameById($paymentId)
    {
        if (is_null(self::$arrPayments)) self::init();
        return self::$arrPayments[$paymentId]['name'];
    }


    /**
     * Returns the ID of the payment processor for the given payment ID
     * @static
     * @param   integer   $paymentId    The payment ID
     * @return  integer                 The payment processor ID on success,
     *                                  false otherwise
     * @global  ADONewConnection  $objDatabase    Database connection object
     */
    static function getPaymentProcessorId($paymentId)
    {
        global $objDatabase;

        $query = "
            SELECT `processor_id`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_payment`
             WHERE `id`=$paymentId";
        $objResult = $objDatabase->Execute($query);
        if ($objResult && !$objResult->EOF)
            return $objResult->fields['processor_id'];
        return false;
    }


    /**
     * Deletes the Payment method with its ID present in $_GET['paymentId'],
     * if any.
     *
     * Returns null if no Payment ID is present.
     * @return    boolean           True on success, false on failure, or null
     */
    static function delete()
    {
        global $objDatabase;

        if (empty($_GET['paymentId'])) return null;
        $objResult = $objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."module_shop".MODULE_INDEX."_payment", 2, 0);
        if ($objResult->RecordCount() < 2) return false;
        if (!$objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment
             WHERE payment_id=".intval($_GET['paymentId']))) return false;
        if (!$objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_payment
             WHERE id=".intval($_GET['paymentId']))) return false;
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_payment");
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment");
        return true;
    }


    /**
     * Adds a new Payment method with its data present in the $_POST array,
     * if any
     *
     * Returns null if no new Payment is present.
     * @return    boolean           True on success, false on failure, or null
     */
    function add()
    {
        global $objDatabase;

        if (empty($_POST['payment_add']) || empty($_POST['paymentName_new']))
            return null;
        $text_name_id = Text::replace(null, FRONTEND_LANG_ID,
            contrexx_stripslashes($_POST['paymentName_new']),
            MODULE_ID, self::TEXT_NAME);
        if (!$text_name_id) return false;
        $query = "
            INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_payment (
                `processor_id`, `text_name_id`,
                `fee`, `free_from`, `ord`, `active`
            ) VALUES (
                ".intval($_POST['paymentHandler_new']).",
                $text_name_id,
                ".floatval($_POST['paymentFee_new']).",
                ".floatval($_POST['paymentFreeFrom_new']).",
                0,
                ".(empty($_POST['paymentActive_new']) ? 0 : 1)."
            )";
        if (!$objDatabase->Execute($query)) return false;
        $payment_id = $objDatabase->Insert_ID();
        return (boolean)$objDatabase->Execute("
            INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment (
                zone_id, payment_id
            ) VALUES (
                ".intval($_POST['paymentZone_new']).",
                ".intval($payment_id)."
            )");
    }


    /**
     * Updates existing Payments with its data present in the $_POST array,
     * if any
     *
     * Returns null if no Payment data is present.
     * @return    boolean           True on success, false on failure, or null
     */
    function update()
    {
        global $objDatabase;

        if (empty($_POST['payment'])) return null;
        if (is_null(self::$arrPayments)) self::init();
        $result = true;
        foreach ($_POST['paymentName'] as $payment_id => $value) {
            $text_name_id = self::$arrPayments[$payment_id]['text_name_id'];
            $text_name_id = Text::replace($text_name_id, FRONTEND_LANG_ID,
                contrexx_addslashes($value), MODULE_ID, self::TEXT_NAME);
            if (empty($text_name_id)) {
                $result = false;
                continue;
            }
            $query = "
                UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_payment
                   SET text_name_id=$text_name_id,
                       processor_id=".intval($_POST['paymentHandler'][$payment_id]).",
                       fee=".floatval($_POST['paymentFee'][$payment_id]).",
                       free_from=".floatval($_POST['paymentFreeFrom'][$payment_id]).",
                       active=".(empty($_POST['paymentActive'][$payment_id]) ? 0 : 1)."
                 WHERE id=".intval($payment_id);
            if (!$objDatabase->Execute($query)) {
                $result = false;
                continue;
            }
            if (!$objDatabase->Execute("
                REPLACE INTO ".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment (
                    zone_id, payment_id
                ) VALUES (
                    ".intval($_POST['paymentZone'][$payment_id]).",
                    ".intval($payment_id)."
                )")
            ) {
                $result = false;
            }
        }
        return $result;
    }


    static function flush()
    {
        self::$arrPayments = null;
    }


    static function errorHandler()
    {
        require_once(ASCMS_CORE_PATH.'/DbTool.class.php');

DBG::activate(DBG_DB_FIREPHP);

        // Fix the Text table first
        Text::errorHandler();

        $table_name = DBPREFIX.'module_shop'.MODULE_INDEX.'_payment';
        $table_structure = array(
            'id' => array('type' => 'INT(10)', 'unsigned' => true, 'auto_increment' => true, 'primary' => true),
            'processor_id' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0'),
            'text_name_id' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0', 'renamefrom' => 'name'),
            'fee' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'default' => '0', 'renamefrom' => 'costs'),
            'free_from' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'default' => '0', 'renamefrom' => 'costs_free_sum'),
            'ord' => array('type' => 'INT(5)', 'unsigned' => true, 'default' => '0', 'renamefrom' => 'sort_order'),
            'active' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'default' => '1', 'renamefrom' => 'status'),
        );
        $table_index =  array();

        if (DbTool::table_exists($table_name)) {
            if (DbTool::column_exists($table_name, 'name')) {
                // Migrate all Payment names to the Text table first
                Text::deleteByKey(self::TEXT_NAME);
                $objResult = DbTool::sql("
                    SELECT `id`, `name`
                      FROM `$table_name");
                if (!$objResult) {
die("Payment::errorHandler(): Error: failed to query names, code atdauu43rjhj");
                }
                while (!$objResult->EOF) {
                    $id = $objResult->fields['id'];
                    $name = $objResult->fields['name'];
                    $text_name_id = Text::replace(
                        null, FRONTEND_LANG_ID,
                        $name, MODULE_ID, self::TEXT_NAME);
                    if (!$text_name_id) {
die("Payment::errorHandler(): Error: failed to migrate name '$name', code sitktjsrn47bcs");
                    }
                    $objResult2 = DbTool::sql("
                        UPDATE `$table_name`
                           SET `name`='$text_name_id'
                         WHERE `id`=$id");
                    if (!$objResult2) {
die("Payment::errorHandler(): Error: failed to update Payment ID $id, code djru344hasds");
                    }
                    $objResult->MoveNext();
                }
            }
        }

        if (!DbTool::table($table_name, $table_structure, $table_index)) {
die("Payment::errorHandler(): Error: failed to migrate Payment table, code snbrujase42hds");
        }

        // Always
        return false;
    }

}

?>
