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
     * Array of available payment service data
     * @var     array
     * @access  private
     * @static
     */
    private static $arrPayment = array();


    /**
     * Set up the payment array
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @since   2.1.0
     */
    static function init()
    {
        global $objDatabase;

        $arrSqlName = Text::getSqlSnippets('`payment`.`text_name_id`', FRONTEND_LANG_ID);
        $query = "
            SELECT `payment`.`id`, `payment`.`processor_id`,
                   `payment`.`costs`, `payment`.`costs_free_sum`,
                   `payment`.`sort_order`, `payment`.`status`".
                   $arrSqlName['field']."
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_payment` AS `payment`".
                   $arrSqlName['join']."
             ORDER BY `payment`.`sort_order` ASC, `payment`.`id` ASC
        ";
        $objResult = $objDatabase->Execute($query);
        while ($objResult && !$objResult->EOF) {
            $text_name_id = $objResult->fields[$arrSqlName['name']];
            $strName = $objResult->fields[$arrSqlName['text']];
            // Replace Text in a missing language by another, if available
            if ($strName === null) {
                $objText = Text::getById($text_name_id, 0);
                if ($objText)
                    $objText->markDifferentLanguage(FRONTEND_LANG_ID);
                    $strName = $objText->getText();
            }
            self::$arrPayment[$objResult->fields['id']] = array(
                'id'             => $objResult->fields['id'],
                'name'           => $strName,
                'text_name_id'   => $text_name_id,
                'processor_id'   => $objResult->fields['processor_id'],
                'costs'          => $objResult->fields['costs'],
                'costs_free_sum' => $objResult->fields['costs_free_sum'],
                'sort_order'     => $objResult->fields['sort_order'],
                'status'         => $objResult->fields['status'],
            );
            $objResult->MoveNext();
        }
    }


    /**
     * Returns the array of available Payment service data
     * @see     Payment::init()
     * @return  array
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @since   2.1.0
     */
    static function getPaymentArray()
    {
        if (empty(self::$arrPayment)) self::init();
        return self::$arrPayment;
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
        if (empty(self::$arrPayment)) self::init();
        return
            (   isset(self::$arrPayment[$payment_id])
             && isset(self::$arrPayment[$payment_id][$property_name])
              ? self::$arrPayment[$payment_id][$property_name]
              : false
            );
    }


    /**
     * Returns the countries related payment ID array.
     *
     * @global   ADONewConnection
     * @param    integer $countryId         The country ID
     * @param    array   $arrCurrencies     The currencies array
     * @return   array   $arrPaymentId      Array of payment IDs, like:
     *                                      array( index => paymentId )
     */
    static function getCountriesRelatedPaymentIdArray($countryId, $arrCurrencies)
    {
        global $objDatabase;

        require_once ASCMS_MODULE_PATH.'/shop/payments/paypal/Paypal.class.php';
        $objPayPal = new PayPal();
        $arrAcceptedCurrencyCodes = array();
        foreach ($arrCurrencies as $arrCurrency) {
            if (   $arrCurrency['status']
                && in_array($arrCurrency['code'],
                            $objPayPal->arrAcceptedCurrencyCodes)
            ) {
                array_push($arrAcceptedCurrencyCodes, $arrCurrency['code']);
            }
        }

        $arrPaymentId = array();
        $query = "
            SELECT `p`.`payment_id`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries` AS `c`
             INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_zones` AS `z`
                ON `c`.`zone_id`=`z`.`id`
             INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment` AS `p`
                ON `z`.`id`=`p`.`zone_id`
             WHERE `c`.`country_id`=".intval($countryId)."
               AND `z`.`status`=1
        ";
        $objResult = $objDatabase->Execute($query);
        while ($objResult && !$objResult->EOF) {
            if (   isset(self::$arrPayment[$objResult->fields['payment_id']])
                && self::$arrPayment[$objResult->fields['payment_id']]['status'] == 1
                && (   self::$arrPayment[$objResult->fields['payment_id']]['processor_id'] != 2
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
     * @param   string  $menuname       Optional menu name
     * @param   string  $onchange       Optional onchange function
     * @param   integer $countryId      Country ID
     * @return  string                  HTML code for the dropdown menu
     * @global  array   $_ARRAYLANG     Language array
     */
    static function getPaymentMenu($selectedId=0, $onchange='', $countryId=0)
    {
        $menu =
            '<select name="paymentId"'.
            ($onchange ? ' onchange="'.$onchange.'"' : '').'>'.
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

        // Initialize if necessary
        if (empty(self::$arrPayment)) self::init();
        $arrPaymentId =
            ($countryId
                ? self::getCountriesRelatedPaymentIdArray(
                    $countryId, Currency::getCurrencyArray()
                  )
                : array_keys(self::$arrPayment)
            );
        $strMenuoptions =
            (empty($selectedId)
              ? '<option value="" selected="selected">'.
                $_ARRAYLANG['TXT_SHOP_PLEASE_SELECT'].
                "</option>\n"
              : ''
            );
        foreach($arrPaymentId as $id) {
            $strMenuoptions .=
                '<option value="'.$id.'"'.
                ($id == $selectedId ? ' selected="selected"' : '').'>'.
                self::$arrPayment[$id]['name'].
                "</option>\n";
        }
        return $strMenuoptions;
    }


    /**
     * Get the payment name for the ID given
     * @static
     * @global  ADONewConnection  $objDatabase
     * @param   integer   $paymentId      The payment ID
     * @return  mixed                     The payment name on success,
     *                                    false otherwise
     * @since   1.2.1
     */
    static function getNameById($paymentId)
    {
        // Initialize if necessary
        if (empty(self::$arrPayment)) self::init();
        return self::$arrPayment[$paymentId]['name'];
    }


    /**
     * Returns the ID of the payment processor for the given payment ID
     * @static
     * @param   integer   $paymentId    The payment ID
     * @return  integer                 The payment processor ID on success,
     *                                  false otherwise
     * @global  ADONewConnection  $objDatabase
     */
    static function getPaymentProcessorId($paymentId)
    {
        global $objDatabase;

        $query = "
            SELECT `processor_id`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_payment`
             WHERE `id`=$paymentId
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult && !$objResult->EOF)
            return $objResult->fields['processor_id'];
        return false;
    }

}

?>
