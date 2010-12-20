<?php

/**
 * Settings
 *
 * Stores Shop settings
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Ivan Schmid <ivan.schmid@comvation.com>
 * @author      Reto Kohli <reto.kohli@comvation.com> (parts)
 * @package     contrexx
 * @subpackage  module_shop
 * @version     3.0.0
 * @todo        Edit PHP DocBlocks!
 */

require_once ASCMS_CORE_PATH.'/MailTemplate.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Currency.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Zones.class.php';

/**
 * Settings
 *
 * Stores Shop settings
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Ivan Schmid <ivan.schmid@comvation.com>
 * @author      Reto Kohli <reto.kohli@comvation.com> (parts)
 * @package     contrexx
 * @subpackage  module_shop
 * @version     3.0.0
 * @todo        Edit PHP DocBlocks!
 */
class Settings
{
    /**
     * Array of all countries
     * @var   array   $arrCountries   Array of all countries
     * @see   _initCountries()
     */
    private $arrCountries = array();

    /**
     * This flag is set to true as soon as any changed setting is
     * detected and stored.  Only used by new methods that support it.
     * @var     boolean     $flagChanged
     * @access  private
     */
    private $flagChanged = false;

    /**
     * Constructor
     */
    function __construct()
    {
    }


    /**
     * Runs all the methods to store the various settings from the shop admin zone.
     *
     * Note that not all of the methods report their success or failure back here (yet),
     * so you should not rely on the result of this method.
     * @return  mixed               True on success, false on failure,
     *                              the empty string if no change is detected.
     */
    function storeSettings()
    {
        $success = true;

        // sets $flagChanged accordingly.
        $success &= $this->storeGeneral();

        $result = Currency::store();
        if ($result !== '') $success &= $result;

        $this->_deletePayment();
        $this->_storeNewPayments();
        $this->_storePayments();

        $this->_storeCountries();

        $result = Zones::store();
        if ($result !== '') $success &= $result;

        $result = Mailtemplate::storeTemplateFromPost();
        if ($result !== '') $success &= $result;

        // new methods - these set $flagChanged accordingly.
        $success &= $this->_deleteShipper();
        $success &= $this->_deleteShipment();
        $success &= $this->_storeNewShipper();
        $success &= $this->_storeNewShipments();
        $success &= $this->_updateShipment();
        $success &= $this->storeVat();

        if ($this->flagChanged === true) {
            return $success;
        }
        return '';
    }


    function _initCountries()
    {
        global $objDatabase;
// post-2.1//        $arrSqlName = Text::getSqlSnippets(
//            '`country`.`text_name_id`', FRONTEND_LANG_ID,
//            MODULE_ID, TEXT_SHOP_COUNTRY_NAME
//        );
//        $query = "
//            SELECT `country`.`id`, `country`.`status`,
//                   `country`.`iso_code_2`, .`country`.`iso_code_3`".
//                   $arrSqlName['field']."
//              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_countries as `country`
//             ORDER BY `country`.`id`
//        ";
//        $objResult = $objDatabase->Execute($query);
//        while (!$objResult->EOF) {
//            $this->arrCountries[$objResult->fields['id']] = array(
//                'id' => $objResult->fields['id'],
//                'name' => $objResult->fields[$arrSqlName['name']],
//                'iso_code_2' => $objResult->fields['iso_code_2'],
//                'iso_code_3' => $objResult->fields['iso_code_3'],
//                'status' => $objResult->fields['status']
//            );
//            $objResult->MoveNext();
//        }
        $query = "
            SELECT countries_id, countries_name,
                   countries_iso_code_2, countries_iso_code_3,
                   activation_status
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_countries
             ORDER BY countries_id
        ";
        $objResult = $objDatabase->Execute($query);
        while (!$objResult->EOF) {
            $this->arrCountries[$objResult->fields['countries_id']] = array(
                'countries_id' => $objResult->fields['countries_id'],
                'countries_name' => $objResult->fields['countries_name'],
                'countries_iso_code_2' => $objResult->fields['countries_iso_code_2'],
                'countries_iso_code_3' => $objResult->fields['countries_iso_code_3'],
                'activation_status' => $objResult->fields['activation_status']
            );
            $objResult->MoveNext();
        }
    }


    /**
     * Store general settings
     *
     * @return  boolean     true on success, false otherwise.
     */
    function storeGeneral()
    {
        global $objDatabase;

        if (isset($_POST['general'])) {
            $strYellowpayAcceptedPM = (isset($_POST['yellowpay_accepted_payment_methods'])
                ? addslashes(join(',', $_POST['yellowpay_accepted_payment_methods']))
                : ''
            );

            Settings::storeSetting('email', $_POST['email']);
            Settings::storeSetting('confirmation_emails', $_POST['confirmation_emails']);
            // added: shop company name and address
            Settings::storeSetting('shop_company', $_POST['shop_company']);
            Settings::storeSetting('shop_address', $_POST['shop_address']);
            Settings::storeSetting('telephone', $_POST['telephone']);
            Settings::storeSetting('fax', $_POST['fax']);
            Settings::storeSetting('yellowpay_shop_id', $_POST['yellowpay_shop_id'], (!empty($_POST['yellowpay_status']) ? 1 : 0));
//            Settings::storeSetting('yellowpay_hash_seed', $_POST['yellowpay_hash_seed']);
// Replaced by
            Settings::storeSetting('yellowpay_hash_signature_in', $_POST['yellowpay_hash_signature_in']);
            Settings::storeSetting('yellowpay_hash_signature_out', $_POST['yellowpay_hash_signature_out']);

            Settings::storeSetting('yellowpay_authorization_type', $_POST['yellowpay_authorization_type']);
            Settings::storeSetting('yellowpay_accepted_payment_methods', $strYellowpayAcceptedPM);
            Settings::storeSetting('yellowpay_use_testserver', $_POST['yellowpay_use_testserver']);
            Settings::storeSetting('saferpay_id', $_POST['saferpay_id'], (!empty($_POST['saferpay_status']) ? 1 : 0));
            Settings::storeSetting('saferpay_finalize_payment', (!empty($_POST['saferpay_finalize_payment']) ? 1 : 0));
            Settings::storeSetting('saferpay_use_test_account', 0, (!empty($_POST['saferpay_use_test_account']) ? 1 : 0));
            Settings::storeSetting('saferpay_window_option', $_POST['saferpay_window_option']);
            Settings::storeSetting('paypal_account_email', $_POST['paypal_account_email'], (!empty($_POST['paypal_status']) ? 1 : 0));

            // Datatrans
            Settings::storeSetting('datatrans_merchant_id', trim(contrexx_strip_tags($_POST['datatrans_merchant_id'])));
            Settings::storeSetting('datatrans_status', (isset($_POST['datatrans_status']) ? 1 : 0));
            Settings::storeSetting('datatrans_request_type', $_POST['datatrans_request_type']);
            Settings::storeSetting('datatrans_use_testserver', ($_POST['datatrans_use_testserver'] ? 1 : 0));

            Settings::storeSetting('country_id', $_POST['country_id']);
            Settings::storeSetting('paypal_default_currency', $_POST['paypal_default_currency']);
            Settings::storeSetting('payment_lsv_status', '', (!empty($_POST['payment_lsv_status']) ? 1 : 0));
            Settings::storeSetting('shop_thumbnail_max_width', $_POST['shop_thumbnail_max_width']);
            Settings::storeSetting('shop_thumbnail_max_height', $_POST['shop_thumbnail_max_height']);
            Settings::storeSetting('shop_thumbnail_quality', $_POST['shop_thumbnail_quality']);
            Settings::storeSetting('shop_weight_enable', (!empty($_POST['shop_weight_enable']) ? 1 : 0));
            Settings::storeSetting(
                'shop_show_products_default',
                    (!empty($_POST['shop_show_products_default'])
                        ? $_POST['shop_show_products_default'] : 0)
            );
            // Mind that this defaults to 1.
            Settings::storeSetting(
                'product_sorting',
                    (!empty($_POST['product_sorting']) ? $_POST['product_sorting'] : 1)
            );
            // Order amount upper limit
            Settings::storeSetting(
                'orderitems_amount_max',
                    (!empty($_POST['orderitems_amount_max']) ? $_POST['orderitems_amount_max'] : 0)
            );

            $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_config");
            return true;
        }
        return false;
    }


    /**
     * Delete currency
     */
    function _deleteCurrency()
    {
        global $objDatabase;

        if (isset($_GET['currencyId']) && !empty($_GET['currencyId'])) {
            $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_currencies WHERE id=".intval($_GET['currencyId'])." AND is_default=0");
            $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_currencies");
        }
    }


    /**
     * Store new currency
     */
    function _storeNewCurrency()
    {
        global $objDatabase;
        if (isset($_POST['currency_add']) && !empty($_POST['currency_add'])) {
            $_POST['currencyActiveNew'] = isset($_POST['currencyActiveNew']) ? 1 : 0;
            $_POST['currencyDefaultNew'] = isset($_POST['currencyDefaultNew']) ? 1 : 0;

            $query = "INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_currencies
                                        (code, symbol, name, rate, status, is_default)
                                 VALUES ('".addslashes($_POST['currencyCodeNew'])."',
                                         '".addslashes($_POST['currencySymbolNew'])."',
                                         '".addslashes($_POST['currencyNameNew'])."',
                                         '".addslashes($_POST['currencyRateNew'])."',
                                         ".intval($_POST['currencyActiveNew']).",
                                         ".intval($_POST['currencyDefaultNew']).")";

            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                return false;
            }
            $cId = $objDatabase->Insert_Id();
            if ($_POST['currencyDefaultNew']) {
                $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_currencies Set is_default=0 WHERE id!=".intval($cId));
            }
            $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_currencies");
            return true;
        }
        return false;
    }


    /**
     * Store currency settings
     */
    function _storeCurrencies()
    {
        global $objDatabase;
        if (isset($_POST['currency']) && !empty($_POST['currency'])) {
             foreach ($_POST['currencyCode'] as $cId => $value) {
                 $is_default=($_POST['currencyDefault']==$cId)?1:0;
                 $status = isset($_POST['currencyActive'][$cId])?1:0;

                 // default currency must be activated
                 $is_active = ($is_default==1 && $status==0)?1:$status;

                $query = "UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_currencies
                                       SET code='".addslashes($value)."',
                                           symbol='".addslashes($_POST['currencySymbol'][$cId])."',
                                           name='".addslashes($_POST['currencyName'][$cId])."',
                                           rate='".addslashes($_POST['currencyRate'][$cId])."',
                                           status=".intval($is_active).",
                                           is_default=".intval($is_default)."
                                 WHERE id =".intval($cId);

                $objDatabase->Execute($query);
            } // end foreach

            $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_currencies");
        }
    }


    /**
     * Delete payment method
     */
    function _deletePayment()
    {
        global $objDatabase;
        if (isset($_GET['paymentId']) && !empty($_GET['paymentId'])) {
            $objResult = $objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."module_shop".MODULE_INDEX."_payment", 2, 0);
            if ($objResult->RecordCount() == 2) {
                $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_payment WHERE id=".intval($_GET['paymentId']));
                $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment WHERE payment_id=".intval($_GET['paymentId']));

                $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_payment");
                $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment");
            }
        }
    }


    /**
     * Store new payment method
     */
    function _storeNewPayments()
    {
        global $objDatabase;
        if (isset($_POST['payment_add']) && !empty($_POST['payment_add'])) {
            $_POST['paymentActive_new'] = isset($_POST['paymentActive_new']) ? 1 : 0;

            $query = "INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_payment (".
                "name, processor_id, costs, costs_free_sum, status) VALUES ('".
                addslashes($_POST['paymentName_new'])."', '".
                intval($_POST['paymentHandler_new'])."', '".
                addslashes($_POST['paymentCosts_new'])."', '".
                addslashes($_POST['paymentCostsFreeSumNew'])."', ".
                intval($_POST['paymentActive_new']).")";
            $objDatabase->Execute($query);
            $pId = $objDatabase->Insert_ID();
            $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment (zones_id, payment_id) VALUES (".intval($_POST['paymentZone_new']).",".intval($pId).")");
            $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_payment");
            $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment");
        }
    }


    /**
     * Store payment settings
     */
    function _storePayments()
    {
        global $objDatabase;
        if (isset($_POST['payment']) && !empty($_POST['payment'])) {
            foreach ($_POST['paymentName'] as $pId => $value) {
                $_POST['paymentActive'][$pId] = isset($_POST['paymentActive'][$pId]) ? 1 : 0;

                $query = "UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_payment ".
                    "SET name='".addslashes($value).
                    "', processor_id='".intval($_POST['paymentHandler'][$pId]).
                    "', costs='".addslashes($_POST['paymentCosts'][$pId]).
                    "', costs_free_sum='".addslashes($_POST['paymentCostsFreeSum'][$pId]).
                    "', status='".intval($_POST['paymentActive'][$pId]).
                    "' WHERE id=".intval($pId);
                $objDatabase->Execute($query);
                if ($_POST['old_paymentZone'][$pId] != $_POST['paymentZone'][$pId]) {
                    $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment Set zones_id='".intval($_POST['paymentZone'][$pId])."' WHERE payment_id=".intval($pId));
                    if (!$objDatabase->Affected_Rows()) {
                        $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment
                                              (zones_id, payment_id) VALUES (".intval($_POST['paymentZone'][$pId]).", ".intval($pId).")");
                    }
                }
            }
            $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_payment");
            $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment");
        }
    }


    /**
     * Delete shipper
     * @return  boolean                     True on success, false otherwise.
     */
    function _deleteShipper()
    {
        if (isset($_GET['shipperId']) && !empty($_GET['shipperId'])) {
            $this->flagChanged = true;
            return Shipment::deleteShipper(intval($_GET['shipperId']));
        }
        return true;
    }


    /**
     * Delete shipment
     * @return  boolean                     True on success, false otherwise.
     */
    function _deleteShipment()
    {
        if (isset($_GET['shipmentId']) && !empty($_GET['shipmentId'])) {
            $this->flagChanged = true;
            return Shipment::deleteShipment(intval($_GET['shipmentId']));
        }
        return true;
    }


    /**
     * Add new shipper
     * @return  boolean                     True on success, false otherwise.
     */
    function _storeNewShipper()
    {
        global $objDatabase; // remove this if you move the INSERT below!
        if (empty($_POST['shipperNameNew'])) return true;
        $this->flagChanged = true;
        if (!Shipment::addShipper(
            $_POST['shipperNameNew'],
            (isset($_POST['shipperActiveNew']) ? 1 : 0),
            intval($_POST['shipmentZoneNew'])
        )) return false;

        // This may belong both to the Zones or Shipment class
        $sid = intval($objDatabase->Insert_ID());
        $objResult = $objDatabase->Execute(
            "INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_rel_shipment (zones_id, shipment_id) ".
            "VALUES (".intval($_POST['shipmentZoneNew']).", $sid)");
        return ($objResult ? true : false);
    }


    /**
     * Add new shipment conditions
     * @return  boolean                     True on success, false otherwise.
     */
    function _storeNewShipments()
    {
        $success = true;
        if (isset($_POST['shipment']) && !empty($_POST['shipment'])) {
            // check whether form fields contain valid new values
            // at least one of them must be non-zero!
            foreach ($_POST['shipmentMaxWeightNew'] as $id => $value) {
                if ( (isset($value) && $value > 0) ||
                     (isset($_POST['shipmentCostNew'][$id]) && $_POST['shipmentCostNew'][$id] > 0) ||
                     (isset($_POST['shipmentPriceFreeNew'][$id]) && $_POST['shipmentPriceFreeNew'][$id] > 0)
                ) {
                    $this->flagChanged = true;
                    // note: the old shipper id which belonged to this row may have been
                    // changed using the dropdown menu.
                    // that's why we *MUST* use the current value from the menu's value
                    // as foreign key!
                    $sid = intval($_POST['shipperId'][$id]);
                    $success &= Shipment::addShipment(
                        $sid,
                        floatval($_POST['shipmentCostNew'][$id]),
                        floatval($_POST['shipmentPriceFreeNew'][$id]),
                        Weight::getWeight($value)
                    );
                }
            }
        }
        return $success;
    }


    /**
     * Update shippers and shipments that possibly have been changed in the form
     * @return  boolean                     True on success, false otherwise.
     */
    function _updateShipment()
    {
        global $objDatabase;
        $success = true;
        if (isset($_POST['shipment']) && !empty($_POST['shipment'])) {
            $this->flagChanged = true;
            // Update all shipment conditions
            if (!empty($_POST['shipmentMaxWeight'])) {
                // Note: $cid is the shipment ID.
                foreach ($_POST['shipmentMaxWeight'] as $cid => $cvalue) {
                    // Note: we must use the (possibly changed) shipper id from $svalue as ID here!
                    // the old value is stored in the sid[$cid] field, use that to find the current
                    // $svalue from the shipperId array.
                    $svalue = $_POST['shipperId'][$_POST['sid'][$cid]];
                    $success &= Shipment::updateShipment(
                        $cid,
                        $svalue,
                        $_POST['shipmentCost'][$cid],
                        $_POST['shipmentPriceFree'][$cid],
                        Weight::getWeight($cvalue)
                    );
                }
            }

            // may be that $sid == $svalue, but may also have changed
            // if the user assigned a whole bunch of shipment conditions
            // to another shipper.
            // in the latter case, $sid is the original shipper ID, and
            // $svalue is the changed one.
            foreach ($_POST['shipperId'] as $sid => $svalue) {
                // update the status field in the Shipper
                $shipperActive =
                    (isset($_POST['shipperActive'][$sid]) ? true : false);
                // note: we must use the (possibly changed) shipper id from $svalue as ID here!
                $success &= Shipment::updateShipper(
                    $svalue,
                    intval($shipperActive)
                );

                // lastly, update the zones
                if ($_POST['old_shipmentZone'][$sid] != $_POST['shipmentZone'][$sid]) {
                    // zone has been changed.
                    // also use the (possibly changed) svalue where necessary.
                    // note that shipment_id here actually refers to a shipper!
                    $query =
                        "UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_shipment ".
                        "SET zones_id=".intval($_POST['shipmentZone'][$sid]).
                        " WHERE shipment_id=$svalue";
                        $objResult = $objDatabase->Execute($query);
                    // no such record yet? insert a new one
                    if (!$objDatabase->Affected_Rows()) {
                        $query =
                            "INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_rel_shipment (zones_id, shipment_id) ".
                            "VALUES (".intval($_POST['shipmentZone'][$sid]).", $svalue)";   //
                        $objResult = $objDatabase->Execute($query);
                        if (!$objResult) {
                            $success = false;
                        }
                    }
                }
            }
        }
        return $success;
    }


    /**
     * Store countries settings
     */
    function _storeCountries()
    {
        global $objDatabase;

        if (isset($_POST['countries']) && !empty($_POST['countries'])) {
            $this->_initCountries();
            // "list1" contains active countries
            $strCountryIdActive = join(',', $_POST['list1']);
            $strCountryIdInactive = join(',', $_POST['list2']);
            if ($strCountryIdActive) {
                $query = "
                    UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_countries
                       SET activation_status=1
                     WHERE countries_id IN ($strCountryIdActive)
                ";
                $objDatabase->Execute($query);
            }
            if ($strCountryIdInactive) {
                $query = "
                    UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_countries
                       SET activation_status=0
                     WHERE countries_id IN ($strCountryIdInactive)
                ";
                $objDatabase->Execute($query);
            }
            $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_countries");
        }
    }


    /**
     * Store any single shop setting in the database
     *
     * Inserts any setting whose name cannot be found, updates present ones.
     * @param   string  $name     The name of the setting
     * @param   string  $value    The value of the setting
     * @param   string  $status   The status of the setting
     * @return  boolean           True on success, false otherwise
     * @global  ADONewConnection
     */
    function storeSetting($name, $value, $status=0)
    {
        global $objDatabase;

        // Does the setting exist already?
        $objResult = $objDatabase->Execute("
            SELECT 1
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_config
             WHERE name='".addslashes($name)."'");
        if (!$objResult) return false;
        if ($objResult->RecordCount() > 0) {
            // Exists, update it
            $objDatabase->Execute("
                UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                   SET value='".addslashes($value)."',
                       status='".addslashes($status)."'
                 WHERE name='".addslashes($name)."'");
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
        } else {
            // Not present, insert it
            $objResult = $objDatabase->Execute("
                INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_config (
                    `name`, `value`, `status`
                ) VALUES (
                    '".addslashes($name)."',
                    '".addslashes($value)."',
                    '".addslashes($status)."'
                )");
            if (!$objResult) return false;
        }
        return true;
    }


    /**
     * Delete Zone
     */
    function _deleteZone()
    {
        global $objDatabase;
        if (isset($_GET['zonesId']) && !empty($_GET['zonesId'])) {
            // Delete zone
            $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_zones WHERE zones_id=".intval($_GET['zonesId']));
            $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_zones");

            // Delete country relations
            $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries WHERE zones_id=".intval($_GET['zonesId']));
            $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries");

            // Update relations
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment Set zones_id=1 WHERE zones_id=".intval($_GET['zonesId']));
            $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment");
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_shipment Set zones_id=1 WHERE zones_id=".intval($_GET['zonesId']));
            $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_shipment");
        }
    }


    /**
     * Store new zone
     */
    function _storeNewZone()
    {
        global $objDatabase;
        if (isset($_POST['zones_new']) && !empty($_POST['zones_new'])) {
            $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_zones (zones_name, activation_status) VALUES ('".addslashes($_POST['zone_name_new'])."',".(isset($_POST['zone_active_new']) ? 1 : 0).")");
            $zId = $objDatabase->Insert_ID();

            if (isset($_POST['selected_countries'])) {
                foreach ($_POST['selected_countries'] as $cId) {
                    $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries (zones_id, countries_id) VALUES (".intval($zId).",".intval($cId).")");
                }
                $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries");
            }
            $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_zones");
        }
    }


    /**
     * Store zone settings
     */
    function _storeZones()
    {
        global $objDatabase;
        if (isset($_POST['zones']) && !empty($_POST['zones'])) {
            $query= "SELECT zones_id, countries_id FROM ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries";
            $objResult = $objDatabase->Execute($query);
            if ($objResult !== false) {
                $arrRelCountries = array();
                while (!$objResult->EOF) {
                    $zonesId   = $objResult->fields['zones_id'];
                    $countryId = $objResult->fields['countries_id'];
                    if (!is_array($arrRelCountries[$zonesId])) {
                        $arrRelCountries[$zonesId] = array();
                    }
                    $arrRelCountries[$zonesId][] = $countryId;
                    $objResult->MoveNext();
                }

                if (isset($_POST['zone_list']) && !empty($_POST['zone_list'])) {
                    foreach ($_POST['zone_list'] as $zId) {
                        $query = "UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_zones SET ".
                            "zones_name='".addslashes($_POST['zone_name'][$zId])."', ".
                            "activation_status=".(isset($_POST['zone_active'][$zId]) ? 1 : 0).
                            "WHERE zones_id=".intval($zId);
                        $objDatabase->Execute($query);

                        if (isset($arrRelCountries[$zId])) {
                            foreach ($arrRelCountries[$zId] as $cId) {
                                if (!in_array($cId, $_POST['selected_countries'][$zId])) {
                                    $objDatabase->Execute(
                                        "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries ".
                                        "WHERE zones_id=$zId AND countries_id=$cId");
                                } else {
                                    unset($_POST['selected_countries'][$zId][array_search($cId, $_POST['selected_countries'][$zId])]);
                                }
                            }
                        }
                        if (isset($_POST['selected_countries'][$zId])) {
                            foreach ($_POST['selected_countries'][$zId] as $cId) {
                                $objDatabase->Execute(
                                    "INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries ".
                                    "(zones_id, countries_id) VALUES ".
                                    "($zId, $cId)");
                            }
                        }
                    }
                    $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_zones");
                    $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries");
                }
            }
        }
    }


    /**
     * Return any single Shop Setting value from the database
     * @param   string  $name     The name of the setting
     * @return  string            The Setting value on success, false otherwise
     * @global  ADONewConnection  $objDatabase
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @since   2.1.0
     * @version 0.9
     * @todo    Test!
     */
    static function getValueByName($name)
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            SELECT `value`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_config`
             WHERE `name`='$name'
        ");
        if (!$objResult || $objResult->EOF) return false;
        return $objResult->fields['value'];
    }

    /**
     * Return any single Shop Setting status from the database
     * @param   string  $name     The name of the setting
     * @return  string            The Setting status on success, false otherwise
     * @global  ADONewConnection  $objDatabase
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @since   2.1.0
     * @version 0.9
     * @todo    Test!
     */
    static function getStatusByName($name)
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            SELECT `status`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_config`
             WHERE `name`='$name'
        ");
        if (!$objResult || $objResult->EOF) return false;
        return $objResult->fields['status'];
    }


    function storeVat()
    {
        $success = true;
        if (isset($_POST['vat'])) {
            $success &= Settings::storeSetting('vat_number', $_POST['vat_number']);
            $success &= Settings::storeSetting('vat_default_id', $_POST['vat_default_id']);
            $success &= Settings::storeSetting('vat_other_id', $_POST['vat_other_id']);

            $vat_enabled_home_customer = (empty($_POST['vat_enabled_home_customer']) ? 0 : 1);
            $success &= Settings::storeSetting('vat_enabled_home_customer', $vat_enabled_home_customer);
            if ($vat_enabled_home_customer)
                $success &= Settings::storeSetting('vat_included_home_customer',
                    (empty($_POST['vat_included_home_customer']) ? 0 : 1));

            $vat_enabled_home_reseller = (empty($_POST['vat_enabled_home_reseller']) ? 0 : 1);
            $success &= Settings::storeSetting('vat_enabled_home_reseller', $vat_enabled_home_reseller);
            if ($vat_enabled_home_reseller)
                $success &= Settings::storeSetting('vat_included_home_reseller',
                    (empty($_POST['vat_included_home_reseller']) ? 0 : 1));

            $vat_enabled_foreign_customer = (empty($_POST['vat_enabled_foreign_customer']) ? 0 : 1);
            $success &= Settings::storeSetting('vat_enabled_foreign_customer', $vat_enabled_foreign_customer);
            if ($vat_enabled_foreign_customer)
                $success &= Settings::storeSetting('vat_included_foreign_customer',
                    (empty($_POST['vat_included_foreign_customer']) ? 0 : 1));

            $vat_enabled_foreign_reseller = (empty($_POST['vat_enabled_foreign_reseller']) ? 0 : 1);
            $success &= Settings::storeSetting('vat_enabled_foreign_reseller', $vat_enabled_foreign_reseller);
            if ($vat_enabled_foreign_reseller)
                $success &= Settings::storeSetting('vat_included_foreign_reseller',
                    (empty($_POST['vat_included_foreign_reseller']) ? 0 : 1));

            $success &= $this->_updateVat();
        } else {
            $success &= $this->_deleteVat();
            $success &= $this->_setProductsVat();
        }
        return $success;
    }


    /**
     * delete VAT entry
     *
     * Takes the ID of the record to be deleted from $_GET['vatId']
     * and passes it on the {@link Vat::deleteVat()} static method.
     * @return  boolean                     True on success, false otherwise.
     */
    function _deleteVat()
    {
        if (isset($_GET['vatid'])) {
            $this->flagChanged = true;
            return Vat::deleteVat($_GET['vatid']);
        }
        return true;
    }


    /**
     * Add and/or update VAT entries
     *
     * Takes the class and rate of the VAT to be added from the $_POST array
     * variable and passes them on to {@link addVat()}.
     * Takes the IDs, classes and rates of the records to be updated from the
     * $_POST array variable and passes them on to {@link updateVat()}.
     * @return  boolean                     True on success, false otherwise.
     */
    function _updateVat()
    {
        $success = true;
        if (!empty($_POST['vatratenew'])) {
            $this->flagChanged = true;
            $success &= Vat::addVat($_POST['vatclassnew'], $_POST['vatratenew']);
        }
        if (isset($_POST['vatclass'])) {
            $this->flagChanged = true;
            $success &= Vat::updateVat($_POST['vatclass'], $_POST['vatrate']);
        }
        return $success;
    }


    /**
     * Apply default VAT rate
     *
     * If the get request array field "setVatAll" is present, sets the VAT ID
     * to the ID found therein for all the products.
     * If the get request array field "setVatUnset" is present, sets the VAT ID
     * to the ID found therein for all products having a zero or NULL VAT ID.
     * @todo    Add possibility to choose some products to change,
     *          and add a parameter for this list of IDs
     * @return  boolean                     True on success, false otherwise.
     * @global  ADONewConnection
     */
    function _setProductsVat()
    {
        global $objDatabase;

        $vatId = '';
        $query = '';
        if (isset($_GET['setVatAll'])) {
            $vatId = intval($_GET['setVatAll']);
        }
        if (isset($_GET['setVatUnset'])) {
            $vatId = intval($_GET['setVatUnset']);
            $query = ' WHERE vat_id IS NULL OR vat_id=0';
        }
        if ($vatId !== '') {
            $this->flagChanged = true;
            $objResult = $objDatabase->Execute("
                UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_products
                   SET vat_id=$vatId".$query
            );
            if ($objResult) return true;
            return false;
        }
        return true;
    }

}

?>
