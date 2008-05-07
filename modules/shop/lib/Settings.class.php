<?php

/*
INSERT INTO `contrexx`.`contrexx_module_shop_config` (
  `id`, `name`, `value`, `status`
) VALUES (
  NULL, 'yellowpay_accepted_payment_methods', '', '0'
);
*/

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
 * @todo        Edit PHP DocBlocks!
 */

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
 * @todo        Edit PHP DocBlocks!
 */
class Settings
{
    /**
     * Array of all countries
     *
     * @var array $arrCountries Array of all countries
     * @access public
     * @see _initCountries()
     */
    var $arrCountries = array();

    /**
     * This flag is set to true as soon as any changed setting is
     * detected and stored. Only used by new methods that support it.
     *
     * @var     boolean     $flagChanged
     * @access  private
     */
    var $flagChanged = false;

    /**
     * PHP4 Constructor
     */
    function Settings()
    {
        $this->__construct();
    }


    /**
     * PHP5 Constructor
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
        $success &= $this->_storeGeneral();

        $this->_deleteCurrency();
        $this->_storeNewCurrency();
        $this->_storeCurrencies();

        $this->_deletePayment();
        $this->_storeNewPayments();
        $this->_storePayments();

        $this->_storeCountries();

        $this->_deleteZone();
        $this->_storeNewZone();
        $this->_storeZones();

        $this->_delMailTpl();
        $this->_addMail();
        $this->_storeMails();

        // new methods - these set $flagChanged accordingly.
        $success &= $this->_deleteShipper();
        $success &= $this->_deleteShipment();
        $success &= $this->_storeNewShipper();
        $success &= $this->_storeNewShipments();
        $success &= $this->_updateShipment();

        $success &= $this->_deleteVat();
        $success &= $this->_updateVat();
        $success &= $this->_setProductsVat();

        if ($this->flagChanged === true) {
            return $success;
        }
        return '';
    }


    function _initCountries()
    {
        global $objDatabase;
        $query = "
            SELECT countries_id, countries_name,
                   countries_iso_code_2, countries_iso_code_3,
                   activation_status
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_countries
             ORDER BY countries_id
        ";
        $objResult = $objDatabase->Execute($query);
        while(!$objResult->EOF) {
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
    function _storeGeneral()
    {
        global $objDatabase;

        if (isset($_POST['general'])) {
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET value='".addslashes($_POST['email'])."'
                                    WHERE name='email'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET value='".addslashes($_POST['confirmation_emails'])."'
                                    WHERE name='confirmation_emails'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            // added: shop company name and address
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET value='".addslashes($_POST['shop_company'])."'
                                    WHERE name='shop_company'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET value='".addslashes($_POST['shop_address'])."'
                                    WHERE name='shop_address'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET value='".addslashes($_POST['telephone'])."'
                                    WHERE name='telephone'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET value='".addslashes($_POST['fax'])."'
                                    WHERE name='fax'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET value='".addslashes($_POST['yellowpay_shop_id'])."',
                                        status=".(!empty($_POST['yellowpay_status']) ? 1 : 0)."
                                    WHERE name='yellowpay_shop_id'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET value='".addslashes($_POST['yellowpay_hash_seed'])."'
                                    WHERE name='yellowpay_hash_seed'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET value='".addslashes($_POST['yellowpay_authorization_type'])."'
                                    WHERE name='yellowpay_authorization_type'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $strAcceptedPM = (isset($_POST['yellowpay_accepted_payment_methods'])
                ? addslashes(join(',', $_POST['yellowpay_accepted_payment_methods']))
                : ''
            );
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET value='".addslashes($_POST['yellowpay_use_testserver'])."'
                                    WHERE name='yellowpay_use_testserver'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET value='$strAcceptedPM'
                                    WHERE name='yellowpay_accepted_payment_methods'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET value='".addslashes($_POST['saferpay_id'])."',
                                        status=".(!empty($_POST['saferpay_status']) ? 1 : 0)."
                                    WHERE name='saferpay_id'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET value=".intval($_POST['saferpay_finalize_payment'])."
                                    WHERE name='saferpay_finalize_payment'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                     SET status='".intval($_POST['saferpay_use_test_account'])."'
                                   WHERE name='saferpay_use_test_account'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                     SET value='".intval($_POST['saferpay_window_option'])."'
                                   WHERE name='saferpay_window_option'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET value='".addslashes($_POST['paypal_account_email'])."',
                                        status=".(!empty($_POST['paypal_status']) ? 1 : 0)."
                                    WHERE name='paypal_account_email'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET value='".addslashes($_POST['tax_number'])."'
                                    WHERE name='tax_number'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET value='".intval($_POST['tax_enabled'])."'
                                    WHERE name='tax_enabled'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            // default vat rate
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET value='".intval($_POST['tax_default_id'])."'
                                    WHERE name='tax_default_id'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET value='".intval($_POST['tax_included'])."'
                                    WHERE name='tax_included'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET value='".intval($_POST['country_id'])."'
                                    WHERE name='country_id'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET value='".contrexx_addslashes($_POST['paypal_default_currency'])."'
                                    WHERE name='paypal_default_currency'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET status=".(!empty($_POST['payment_lsv_status']) ? 1 : 0)."
                                    WHERE name='payment_lsv_status'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET value=".intval($_POST['shop_thumbnail_max_width'])."
                                    WHERE name='shop_thumbnail_max_width'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET value=".intval($_POST['shop_thumbnail_max_height'])."
                                    WHERE name='shop_thumbnail_max_height'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_config
                                    SET value=".intval($_POST['shop_thumbnail_quality'])."
                                    WHERE name='shop_thumbnail_quality'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }

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
             foreach ($_POST['currencyCode'] as $cId => $value){
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
        if (isset($_POST['shipperNameNew']) && !empty($_POST['shipperNameNew'])) {
            $this->flagChanged = true;
            if (Shipment::addShipper(
                $_POST['shipperNameNew'],
                (isset($_POST['shipperActiveNew']) ? 1 : 0),
                intval($_POST['shipmentZoneNew'])
            )) {
// This block belongs to some method in the Zones or Shipment class
// -- not decided yet.
                $sid = intval($objDatabase->Insert_ID());
                $objResult = $objDatabase->Execute(
                    "INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_rel_shipment (zones_id, shipment_id) ".
                    "VALUES (".intval($_POST['shipmentZoneNew']).", $sid)");
                return $objResult;
            }
            return false;
        }
        return true;
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
            // update all shipment conditions.
            // note: $cid is the shipment ID.
            foreach ($_POST['shipmentMaxWeight'] as $cid => $cvalue) {
                // note: we must use the (possibly changed) shipper id from $svalue as ID here!
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
            $updateList = count($_POST['list1']) < count($_POST['list2']) ? 'list1' : 'list2';
            if (!isset($_POST[$updateList])) {
                $_POST[$updateList] = array();
            }
            // Set new list
            foreach ($this->arrCountries as $cValues) {
                if ($cValues['activation_status'] == ($updateList == 'list1' ? 1 : 0)) {
                    if (!in_array($cValues['countries_id'],$_POST[$updateList])) {
                        $query = "UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_countries
                                                    Set activation_status=".($updateList == 'list1' ? 0 : 1)."
                                                    WHERE countries_id=".intval($cValues['countries_id']);
                        $objDatabase->Execute($query);
                    } else {
                        unset($_POST[$updateList][array_search($cValues['countries_id'],$_POST[$updateList])]);
                    }
                }
            }
            foreach ($_POST[$updateList] as $cId) {
                $query = "UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_countries
                                            Set activation_status=".($updateList == 'list1' ? 1 : 0)."
                                            WHERE countries_id=".intval($cId);
                $objDatabase->Execute($query);
            }
            $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_countries");
        }
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
     * Delete template
     */
    function _delMailTpl()
    {
        global $objDatabase;
        if (isset($_GET['delTplId']) && !empty($_GET['delTplId'])){
            $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_mail WHERE id=".intval($_GET['delTplId'])." AND protected!=1");
            if ($objDatabase->affected_rows() == 1){
                $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_mail_content WHERE tpl_id=".intval($_GET['delTplId']));
            }
        }
        if (isset($_GET['delMailId']) && !empty($_GET['delMailId'])){
            $objLanguage = new FWLanguage();
            $arrLanguage = $objLanguage->arrLanguage;
            foreach ($arrLanguage as $langValues){
                if ($langValues['is_default']){
                    $defaultLang = $langValues['id'];
                    break;
                }
            }
            $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_mail_content WHERE id=".intval($_GET['delMailId'])." AND lang_id!=".$defaultLang);
        }
        if ((isset($_GET['delTplId']) && !empty($_GET['delTplId'])) || (isset($_GET['delMailId']) && !empty($_GET['delMailId']))){
            $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_mail");
            $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_mail_content");
        }
    }


    /**
     * Add new template
     */
    function _addMail()
    {
        global $objDatabase;
        if (isset($_POST['mails']) && !empty($_POST['mails'])){
            if (   $_POST['tplId'] == 0
                || (   isset($_POST['shopMailSaveNew']))) {
                $query = "
                    INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_mail (
                        tplname, protected
                    ) VALUES (
                        '".contrexx_addslashes($_POST['shopMailTemplate'])."', 0)
                ";
                $objDatabase->Execute($query);
                $tlpId = $objDatabase->Insert_ID();

                $objLanguage = new FWLanguage();
                $arrLanguage = $objLanguage->arrLanguage;
                foreach ($arrLanguage as $langValues){
                    if ($langValues['is_default']){
                        $defaultLang = $langValues['id'];
                        break;
                    }
                }

                $query = "
                    INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_mail_content (
                        tpl_id, lang_id, from_mail, xsender, subject, message
                    ) VALUES (
                        ".intval($tlpId).", $defaultLang,
                        '".contrexx_addslashes($_POST['shopMailFromAddress'])."',
                        '".contrexx_addslashes($_POST['shopMailFromName'])."',
                        '".contrexx_addslashes($_POST['shopMailSubject'])."',
                        '".contrexx_addslashes($_POST['shopMailBody'])."'
                    )";
                $objDatabase->Execute($query);
                $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_mail");
                $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_mail_content");
            }
        }
    }


    /**
     * Store mail template
     */
    function _storeMails()
    {
        global $objDatabase;

        if (isset($_POST['mails'])) {
            if (empty($_POST['shopMailSaveNew'])
                && !empty($_POST['shopMailFromName'])
                && !empty($_POST['shopMailSubject'])
                && !empty($_POST['shopMailBody']))
            {
                if (!empty($_POST['mailId'])) {
                    $query = "
                        UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_mail_content
                           SET from_mail='".contrexx_addslashes($_POST['shopMailFromAddress'])."',
                               xsender='".contrexx_addslashes($_POST['shopMailFromName'])."',
                               subject='".contrexx_addslashes($_POST['shopMailSubject'])."',
                               message='".contrexx_addslashes($_POST['shopMailBody'])."'
                         WHERE id=".intval($_POST['mailId']);
                    $objDatabase->Execute($query);
                } else {
                    $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_mail_content ".
                    "(tpl_id, lang_id, from_mail, xsender, subject, message) VALUES (".
                    intval($_POST['tplId']).", ".intval($_POST['langId']).", '".
                    contrexx_addslashes($_POST['shopMailFromAddress'])."', '".
                    contrexx_addslashes($_POST['shopMailFromName'])."', '".
                    contrexx_addslashes($_POST['shopMailSubject'])."', '".
                    contrexx_addslashes($_POST['shopMailBody'])."')");
                }
                $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_mail_content");
                $objResult = $objDatabase->Execute("
                    SELECT protected
                      FROM ".DBPREFIX."module_shop".MODULE_INDEX."_mail
                     WHERE id=".intval($_POST['tplId']));
                if (!$objResult->EOF) {
                    if (!$objResult->fields['protected']) {
                        $objDatabase->Execute("
                            UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_mail
                               SET tplname='".contrexx_addslashes($_POST['shopMailTemplate'])."'
                             WHERE id=".intval($_POST['tplId']));
                        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_mail");
                    }
                }
            }
        }
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
     * variable and passes them on to the {@link Vat::addVat()} static method.
     * Takes the IDs, classes and rates of the records to be updated from the
     * $_POST array variable and passes them on to the {@link Vat::updateVat()}
     * object method.
     * @return  boolean                     True on success, false otherwise.
     */
    function _updateVat()
    {
        $success = true;
        // currently, *both* 'vatratenew' and 'vatids' fields *SHOULD* be set at the same time
        if (isset($_POST['vatratenew']) && !empty($_POST['vatratenew'])) {
            $this->flagChanged = true;
            $success &= Vat::addVat($_POST['vatclassnew'], $_POST['vatratenew']);
        }
        if (isset($_POST['vatids'])) {
            $this->flagChanged = true;
            // arrConfig isn't available here!  luckily, Vat::updateVat() only
            // dumps the arguments to the database and doesn't use any of the settings.
            // thus, we fake this with an empty array.
            $objVat = new Vat();
            $success &= $objVat->updateVat($_POST['vatids'], $_POST['vatclasses'], $_POST['vatrates']);
        }
        return $success;
    }


    /**
     * If the $_GET field 'setVatAll is present, sets the VAT ID to the ID found
     * therein for all the products.
     *
     * @todo    Add possibility to choose some products to change,
     *          and add a parameter for this list of IDs
     * @return  boolean                     True on success, false otherwise.
     * @global  mixed       $objDatabase    Database object
     */
    function _setProductsVat()
    {
        global $objDatabase;
        $vatId = '';
        if (isset($_GET['setVatAll'])) {
            $vatId = $_GET['setVatAll'];
            $query = "UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_products SET vat_id=$vatId";
        }
        if (isset($_GET['setVatUnset'])) {
            $vatId = $_GET['setVatUnset'];
            $query = "UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_products SET vat_id=$vatId ".
                "WHERE vat_id IS NULL";
        }
        if ($vatId) {
            $this->flagChanged = true;
            // add array of product IDs here
            //if ($arrProdId) {
            //    $keys = implode(',', $arrProdId);
            //    $query .= " WHERE id IN ($keys)";
            //}
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }
}

?>
