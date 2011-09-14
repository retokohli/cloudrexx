<?php

/**
 * Payment service manager
 * @package     contrexx
 * @copyright   CONTREXX CMS - COMVATION AG
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 * @version     2.1.0
 */

require_once ASCMS_MODULE_PATH.'/shop/lib/PaymentProcessing.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Zones.class.php';

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
    const TEXT_NAME = 'payment_name';

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

        $arrSqlName = Text::getSqlSnippets('`payment`.`id`', FRONTEND_LANG_ID,
            'shop', array('name' => self::TEXT_NAME));
        $query = "
            SELECT `payment`.`id`, `payment`.`processor_id`,
                   `payment`.`fee`, `payment`.`free_from`,
                   `payment`.`ord`, `payment`.`active`, ".
            $arrSqlName['field']."
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_payment` AS `payment`".
            $arrSqlName['join']."
             ORDER BY id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) { return self::errorHandler(); }
        self::$arrPayments = array();
        if ($objResult->EOF) return true;
        while ($objResult && !$objResult->EOF) {
            $id = $objResult->fields['id'];
            $strName = $objResult->fields['name'];
            if ($strName === null) {
                $objText = Text::getById($id, 'shop', self::TEXT_NAME);
                if ($objText) $strName = $objText->content();
            }
            self::$arrPayments[$id] = array(
                'id'           => $id,
                'processor_id' => $objResult->fields['processor_id'],
                'name'         => $strName,
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
     * If PayPal is the selected payment method, any Currencies not supported
     * will be removed from the Currency array.
     * Returns the Payment IDs allowed for the given Country ID.
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @param    integer $countryId         The country ID
     * @param    array   $arrCurrencies     The currencies array, by reference
     * @return   array                      Array of payment IDs, like:
     *                                      array( index => paymentId )
     */
    static function getCountriesRelatedPaymentIdArray($countryId, &$arrCurrencies)
    {
        global $objDatabase;

        if (is_null(self::$arrPayments)) self::init();
// TODO: Rewrite to only test this when it's selected,
// and to include any currency otherwise.
        if (   isset($_SESSION['shop']['paymentId'])) {
            $payment_id = $_SESSION['shop']['paymentId'];
            $processor_id = self::getPaymentProcessorId($payment_id);
            if ($processor_id == 2) {
                require_once ASCMS_MODULE_PATH.'/shop/payments/paypal/Paypal.class.php';
                $arrPaypalAcceptedCurrencyCodes =
                   PayPal::getAcceptedCurrencyCodeArray();
                foreach ($arrCurrencies as $index => $arrCurrency) {
                    if (!in_array($arrCurrency['code'],
                           $arrPaypalAcceptedCurrencyCodes)) {
                        unset($arrCurrencies[$index]);
                    }
                }
            }
        }
        $arrPaymentId = array();
        $query = "
            SELECT DISTINCT `payment`.`id`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries` AS `country`
             INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_zones` AS `zone`
                ON `country`.`zone_id`=`zone`.`id`
             INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment` AS `rel_payment`
                ON `zone`.`id`=`rel_payment`.`zone_id`
             INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_payment` AS `payment`
                ON `payment`.`id`=`rel_payment`.`payment_id`
             WHERE `country`.`country_id`=".intval($countryId)."
               AND `payment`.`active`=1
               AND `zone`.`active`=1";
        $objResult = $objDatabase->Execute($query);
        while ($objResult && !$objResult->EOF) {
            if (   isset(self::$arrPayments[$objResult->fields['id']])
                && (   self::$arrPayments[$objResult->fields['id']]['processor_id'] != 2
                    || count($arrCurrencies))
            ) {
                $arrPaymentId[] = $objResult->fields['id'];
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
        $arrPaymentId = ($countryId
            ? self::getCountriesRelatedPaymentIdArray(
                  $countryId, Currency::getCurrencyArray())
            : array_keys(self::$arrPayments));
        $arrOption = (empty($selectedId)
              ? array(0 => $_ARRAYLANG['TXT_SHOP_PLEASE_SELECT'])
              : array());
        foreach ($arrPaymentId as $id) {
            $arrOption[$id] = self::$arrPayments[$id]['name'];
        }
        return Html::getOptions($arrOption, $selectedId);
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

        if (empty($_POST['payment_add']) || empty($_POST['name_new']))
            return null;
        $query = "
            INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_payment (
                `processor_id`, `fee`, `free_from`, `ord`, `active`
            ) VALUES (
                ".intval($_POST['processor_id_new']).",
                ".floatval($_POST['fee_new']).",
                ".floatval($_POST['free_from_new']).",
                0,
                ".(empty($_POST['active_new']) ? 0 : 1)."
            )";
        if (!$objDatabase->Execute($query)) return false;
        $payment_id = $objDatabase->Insert_ID();
        if (!Text::replace($payment_id, FRONTEND_LANG_ID, 'shop',
            self::TEXT_NAME,
            trim(strip_tags(contrexx_input2raw($_POST['name_new']))))) {
            return false;
        }
        return (boolean)$objDatabase->Execute("
            INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment (
                zone_id, payment_id
            ) VALUES (
                ".intval($_POST['zone_id_new']).",
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

        if (empty($_POST['bpayment'])) return null;
        if (is_null(self::$arrPayments)) self::init();
        $result = true;
        $changed = false;
        foreach ($_POST['name'] as $payment_id => $name) {
            $payment_id = intval($payment_id);
            $name = contrexx_input2raw($name);
            $fee = floatval($_POST['fee'][$payment_id]);
            $free_from = floatval($_POST['free_from'][$payment_id]);
            $processor_id = intval($_POST['processor_id'][$payment_id]);
// NTH: The ordinal is implemented, but unused yet
//            $ord = intval($_POST['ord'][$payment_id]);
            $active = (empty($_POST['active'][$payment_id]) ? 0 : 1);
            $zone_id = intval($_POST['zone_id'][$payment_id]);
            $zone_id_old = Zones::getZoneIdByPaymentId($payment_id);
            if (   $name == self::$arrPayments[$payment_id]['name']
                && $fee == self::$arrPayments[$payment_id]['fee']
                && $free_from == self::$arrPayments[$payment_id]['free_from']
                && $processor_id == self::$arrPayments[$payment_id]['processor_id']
//                && $ord == self::$arrPayments[$payment_id]['ord']
                && $active == self::$arrPayments[$payment_id]['active']
                && $zone_id == $zone_id_old) {
                continue;
            }
            $changed = true;
            if (!Text::replace($payment_id, FRONTEND_LANG_ID, 'shop',
                self::TEXT_NAME, trim(strip_tags(contrexx_input2raw($name))))) {
                $result = false;
            }
            $query = "
                UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_payment
                   SET processor_id=$processor_id,
                       fee=$fee,
                       free_from=$free_from,
                       active=$active
                 WHERE id=$payment_id";
            if (!$objDatabase->Execute($query)) {
                $result = false;
            }
            if (!$objDatabase->Execute("
                UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment
                   SET `zone_id`=$zone_id
                 WHERE `payment_id`=$payment_id")) {
                $result = false;
            }
        }
        if ($changed) return $result;
        return null;
    }


    /**
     * Clear the Payments stored in the class
     *
     * Call this after updating the database.  The Payments will be
     * reinitialized on demand.
     */
    static function reset()
    {
        self::$arrPayments = null;
    }


    /**
     * Sets up the Payment settings view
     * @param   HTML_Template_Sigma $objTemplate    The optional Template,
     *                                              by reference
     * @return  boolean                             True on success,
     *                                              false otherwise
     */
    static function view_settings(&$objTemplate=null)
    {
        if (!$objTemplate) {
            $objTemplate = new HTML_Template_Sigma();
            $objTemplate->loadTemplateFile('module_shop_settings_payment.html');
        } else {
            $objTemplate->addBlockfile('SHOP_SETTINGS_FILE',
                'settings_block', 'module_shop_settings_payment.html');
        }
        $i = 0;
        foreach (Payment::getArray() as $payment_id => $arrPayment) {
            $zone_id = Zones::getZoneIdByPaymentId($payment_id);
            $objTemplate->setVariable(array(
                'SHOP_PAYMENT_STYLE' => 'row'.(++$i % 2 + 1),
                'SHOP_PAYMENT_ID' => $arrPayment['id'],
                'SHOP_PAYMENT_NAME' => $arrPayment['name'],
                'SHOP_PAYMENT_HANDLER_MENUOPTIONS' =>
                    PaymentProcessing::getMenuoptions($arrPayment['processor_id']),
                'SHOP_PAYMENT_COST' => $arrPayment['fee'],
                'SHOP_PAYMENT_COST_FREE_SUM' => $arrPayment['free_from'],
                'SHOP_ZONE_SELECTION' => Zones::getMenu(
                    $zone_id, "zone_id[$payment_id]"),
                'SHOP_PAYMENT_STATUS' => (intval($arrPayment['active'])
                    ? HTML_ATTRIBUTE_CHECKED : ''),
            ));
            $objTemplate->parse('shopPayment');
        }
        $objTemplate->setVariable(array(
            'SHOP_PAYMENT_HANDLER_MENUOPTIONS_NEW' =>
                // Selected PSP ID is -1 to disable the "please select" option
                PaymentProcessing::getMenuoptions(-1),
            'SHOP_ZONE_SELECTION_NEW' => Zones::getMenu(0, 'zone_id_new'),
        ));
        return true;
    }


    /**
     * Handles any kind of database errors
     *
     * Includes updating the payments table (I guess from version 1.2.0(?),
     * note that this is unconfirmed) to the current structure
     * @return  boolean               False.  Always.
     */
    static function errorHandler()
    {
        require_once(ASCMS_DOCUMENT_ROOT.'/update/UpdateUtil.php');

//DBG::activate(DBG_DB_FIREPHP);

        // Fix the Text and Zones tables first
        Text::errorHandler();
        Zones::errorHandler();

        $table_name = DBPREFIX.'module_shop'.MODULE_INDEX.'_payment';
        $table_structure = array(
            'id' => array('type' => 'INT(10)', 'unsigned' => true, 'auto_increment' => true, 'primary' => true),
            'processor_id' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0'),
            'fee' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'default' => '0', 'renamefrom' => 'costs'),
            'free_from' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'default' => '0', 'renamefrom' => 'costs_free_sum'),
            'ord' => array('type' => 'INT(5)', 'unsigned' => true, 'default' => '0', 'renamefrom' => 'sort_order'),
            'active' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'default' => '1', 'renamefrom' => 'status'),
        );
        $table_index = array();
        if (UpdateUtil::table_exist($table_name)) {
            if (UpdateUtil::column_exist($table_name, 'name')) {
                // Migrate all Payment names to the Text table first
                Text::deleteByKey('shop', self::TEXT_NAME);
                $query = "
                    SELECT `id`, `name`
                      FROM `$table_name";
                $objResult = UpdateUtil::sql($query);
                if (!$objResult) {
                    throw new Update_DatabaseException(
                        "Failed to query Payment names", $query);
                }
                while (!$objResult->EOF) {
                    $id = $objResult->fields['id'];
                    $name = $objResult->fields['name'];
                    if (!Text::replace($id, FRONTEND_LANG_ID,
                        'shop', self::TEXT_NAME, $name)) {
                        throw new Update_DatabaseException(
                            "Failed to migrate Payment name '$name'");
                    }
                    $objResult->MoveNext();
                }
            }
        }
        UpdateUtil::table($table_name, $table_structure, $table_index);

        $table_name = DBPREFIX.'module_shop'.MODULE_INDEX.'_rel_payment';
        $table_structure = array(
            'payment_id' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0', 'primary' => true),
            'zone_id' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0', 'primary' => true, 'renamefrom' => 'zones_id'),
        );
        $table_index = array(
// TODO: Verify primary key!
//            'primary' => array('zone_id', 'payment_id'),
        );
        UpdateUtil::table($table_name, $table_structure, $table_index);

        // Always
        return false;
    }

}
