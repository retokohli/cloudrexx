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
class ShopSettings
{
    /**
     * This flag is set to true as soon as any changed setting is
     * detected and stored.  Only used by new methods that support it.
     * @var     boolean
     * @access  private
     */
    private static $changed = null;

    /**
     * This flag is set to false as soon as storing any setting fails.
     * Only used by new methods that support it.
     * @var     boolean
     * @access  private
     */
    private static $success = null;


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
        self::$success = true;
        self::$changed = false;

        self::storeGeneral();
        self::storeCurrencies();
        self::storePayments();
        self::storeCountries();
        Zones::store();
        Mailtemplate::storeTemplateFromPost();
        self::storeShipping();
        self::storeVat();

        if (self::$changed) return self::$success;
        return null;
    }


    /**
     * OBSOLETE
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
     */


    /**
     * Store general settings
     *
     * @return  boolean     true on success, false otherwise.
     */
    function storeGeneral()
    {
        if (empty($_POST['general'])) return;

        self::storeSetting('email', $_POST['email']);
        self::storeSetting('email_confirmation', $_POST['email_confirmation']);
        // added: shop company name and address
        self::storeSetting('shop_company', $_POST['shop_company']);
        self::storeSetting('shop_address', $_POST['shop_address']);
        self::storeSetting('telephone', $_POST['telephone']);
        self::storeSetting('fax', $_POST['fax']);
        self::storeSetting('country_id', $_POST['country_id']);

        // Postfinance (FKA yellowpay)
        $strYellowpayAcceptedPM = (isset($_POST['postfinance_accepted_payment_methods'])
            ? addslashes(join(',', $_POST['postfinance_accepted_payment_methods']))
            : ''
        );
        self::storeSetting('postfinance_shop_id', $_POST['postfinance_shop_id'], (!empty($_POST['postfinance_active']) ? 1 : 0));
        //self::storeSetting('postfinance_hash_seed', $_POST['postfinance_hash_seed']);
        // Replaced by
        self::storeSetting('postfinance_hash_signature_in', $_POST['postfinance_hash_signature_in']);
        self::storeSetting('postfinance_hash_signature_out', $_POST['postfinance_hash_signature_out']);
        self::storeSetting('postfinance_authorization_type', $_POST['postfinance_authorization_type']);
        self::storeSetting('postfinance_accepted_payment_methods', $strYellowpayAcceptedPM);
        self::storeSetting('postfinance_use_testserver', $_POST['postfinance_use_testserver']);

        // Postfinance Mobile
        self::storeSetting('postfinance_mobile_webuser', $_POST['postfinance_mobile_webuser']);
        self::storeSetting('postfinance_mobile_sign', $_POST['postfinance_mobile_sign']);
        self::storeSetting('postfinance_mobile_ijustwanttotest', isset($_POST['postfinance_mobile_ijustwanttotest']));
        self::storeSetting('postfinance_mobile_status', isset($_POST['postfinance_mobile_status']));

        // Saferpay
        self::storeSetting('saferpay_id', $_POST['saferpay_id'], (!empty($_POST['saferpay_active']) ? 1 : 0));
        self::storeSetting('saferpay_finalize_payment', (!empty($_POST['saferpay_finalize_payment']) ? 1 : 0));
        self::storeSetting('saferpay_use_test_account', 0, (!empty($_POST['saferpay_use_test_account']) ? 1 : 0));
        self::storeSetting('saferpay_window_option', $_POST['saferpay_window_option']);

        // Paypal
        self::storeSetting('paypal_account_email', $_POST['paypal_account_email'], (!empty($_POST['paypal_active']) ? 1 : 0));
        self::storeSetting('paypal_default_currency', $_POST['paypal_default_currency']);

        // Datatrans
        self::storeSetting('datatrans_merchant_id', trim(contrexx_strip_tags($_POST['datatrans_merchant_id'])));
        self::storeSetting('datatrans_active',
            (empty($_POST['datatrans_active']) ? 0 : 1));
        self::storeSetting('datatrans_request_type', $_POST['datatrans_request_type']);
        self::storeSetting('datatrans_use_testserver',
            (empty($_POST['datatrans_use_testserver']) ? 0 : 1));

        // LSV
        self::storeSetting('payment_lsv_active', '',
            (empty($_POST['payment_lsv_active']) ? 0 : 1));

        // Thumbnail settings
        self::storeSetting('thumbnail_max_width', $_POST['thumbnail_max_width']);
        self::storeSetting('thumbnail_max_height', $_POST['thumbnail_max_height']);
        self::storeSetting('thumbnail_quality', $_POST['thumbnail_quality']);

        // Various settings
        self::storeSetting('weight_enable',
            (empty($_POST['weight_enable']) ? 0 : 1));
        self::storeSetting('show_products_default',
            (empty($_POST['show_products_default'])
              ? 0 : $_POST['show_products_default']));
        // Mind that this defaults to 1, zero is not a valid value
        self::storeSetting('product_sorting',
            (empty($_POST['product_sorting'])
              ? 1 : $_POST['product_sorting']));
        // Order amount upper limit (applicable when using Saferpay)
        self::storeSetting('orderitems_amount_max',
            (empty($_POST['orderitems_amount_max'])
                ? 0 : $_POST['orderitems_amount_max']));
    }


    /**
     * Stores the Currencies as present in the POST request
     *
     * See {@see Currency::delete()},
     * {@see Currency::add()}, and
     * {@see Currency::update()}.
     */
    static function storeCurrencies()
    {
        $result = Currency::delete();
        if (isset($result)) {
            self::$changed = true;
            self::$success &= $result;
        }
        $result = Currency::add();
        if (isset($result)) {
            self::$changed = true;
            self::$success &= $result;
        }
        $result = Currency::update();
        if (isset($result)) {
            self::$changed = true;
            self::$success &= $result;
        }
        if (self::$changed) {
            // Remember to reinit the Currencies, or the User
            // won't see changes instantly
            Currency::reset();
        }
    }


    /**
     * Stores the Payments as present in the POST request
     *
     * See {@see Payment::delete()},
     * {@see Payment::add()}, and
     * {@see Payment::update()}.
     */
    static function storePayments()
    {
        $result = Payment::delete();
        if (isset($result)) {
            self::$changed = true;
            self::$success &= $result;
        }
        $result = Payment::add();
        if (isset($result)) {
            self::$changed = true;
            self::$success &= $result;
        }
        $result = Payment::update();
        if (isset($result)) {
            self::$changed = true;
            self::$success &= $result;
        }
    }


    /**
     * OBSOLETE
     * Delete currency
    function _deleteCurrency()
    {
        global $objDatabase;

        if (isset($_GET['currencyId']) && !empty($_GET['currencyId'])) {
            $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_currencies WHERE id=".intval($_GET['currencyId'])." AND is_default=0");
            $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_currencies");
        }
    }
     */


    static function storeShipping()
    {
        // new methods - these set $flagChanged accordingly.
        self::_deleteShipper();
        self::_deleteShipment();
        self::_storeNewShipper();
        self::_storeNewShipments();
        self::_updateShipment();
    }


    /**
     * Delete the Shipper with its ID present in $_GET['shipperId'], if any
     */
    function _deleteShipper()
    {
        if (empty($_GET['shipperId'])) return;
        self::$changed = true;
        self::$success &= Shipment::deleteShipper(intval($_GET['shipperId']));
    }


    /**
     * Delete the Shipment with its ID present in $_GET['shipmentId'], if any
     */
    function _deleteShipment()
    {
        if (empty($_GET['shipmentId'])) return;
        self::$changed = true;
        self::$success &= Shipment::deleteShipment(intval($_GET['shipmentId']));
    }


    /**
     * Add new shipper
     * @return  boolean                     True on success, false otherwise.
     * @todo    Move to Shipment class
     */
    function _storeNewShipper()
    {
        global $objDatabase; // remove this if you move the INSERT below!
        if (empty($_POST['shipperNameNew'])) return true;
        self::$changed = true;
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
     * @todo    Move to Shipment class
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
                    self::$changed = true;
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
     * @todo    Move to Shipment class
     */
    function _updateShipment()
    {
        global $objDatabase;
        $success = true;
        if (isset($_POST['shipment']) && !empty($_POST['shipment'])) {
            self::$changed = true;
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
     *
     * Returns null if nothing is changed.
     * @return    boolean         True on success, false on failure, or null.
     */
    private static function storeCountries()
    {
        // Skip if not submitted or if the list is empty.
        // At least one Country needs to be active.
        // "list1" contains the active Country IDs
        if (   empty($_POST['countries'])
            || empty($_POST['list1'])) return null;
        $strCountryIdActive = join(',', $_POST['list1']);
        return Country::activate($strCountryIdActive);
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
            if ($objDatabase->Affected_Rows()) { self::$changed = true; }
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


    function storeVat()
    {
        if (empty($_POST['vat'])) {
            self::deleteVat();
            self::setProductsVat();
            return;
        }

        self::$changed = true;
        self::$success &= self::storeSetting('vat_number', $_POST['vat_number']);
        self::$success &= self::storeSetting('vat_default_id', $_POST['vat_default_id']);
        self::$success &= self::storeSetting('vat_other_id', $_POST['vat_other_id']);
        $vat_enabled_home_customer = (empty($_POST['vat_enabled_home_customer']) ? 0 : 1);
        self::$success &= self::storeSetting('vat_enabled_home_customer', $vat_enabled_home_customer);
        if ($vat_enabled_home_customer)
            self::$success &= self::storeSetting('vat_included_home_customer',
                (empty($_POST['vat_included_home_customer']) ? 0 : 1));
        $vat_enabled_home_reseller = (empty($_POST['vat_enabled_home_reseller']) ? 0 : 1);
        self::$success &= self::storeSetting('vat_enabled_home_reseller', $vat_enabled_home_reseller);
        if ($vat_enabled_home_reseller)
            self::$success &= self::storeSetting('vat_included_home_reseller',
                (empty($_POST['vat_included_home_reseller']) ? 0 : 1));
        $vat_enabled_foreign_customer = (empty($_POST['vat_enabled_foreign_customer']) ? 0 : 1);
        self::$success &= self::storeSetting('vat_enabled_foreign_customer', $vat_enabled_foreign_customer);
        if ($vat_enabled_foreign_customer)
            self::$success &= self::storeSetting('vat_included_foreign_customer',
                (empty($_POST['vat_included_foreign_customer']) ? 0 : 1));
        $vat_enabled_foreign_reseller = (empty($_POST['vat_enabled_foreign_reseller']) ? 0 : 1);
        self::$success &= self::storeSetting('vat_enabled_foreign_reseller', $vat_enabled_foreign_reseller);
        if ($vat_enabled_foreign_reseller)
            self::$success &= self::storeSetting('vat_included_foreign_reseller',
                (empty($_POST['vat_included_foreign_reseller']) ? 0 : 1));
        self::$success &= $this->_updateVat();
    }


    /**
     * Delete VAT entry
     *
     * Takes the ID of the record to be deleted from $_GET['vatid']
     * and passes it on the {@link Vat::deleteVat()} static method.
     */
    function deleteVat()
    {
        if (empty($_GET['vatid'])) return;
        self::$changed = true;
        self::$success &= Vat::deleteVat($_GET['vatid']);
    }


    /**
     * Add and/or update VAT entries
     *
     * Takes the class and rate of the VAT to be added from the $_POST array
     * variable and passes them on to {@link addVat()}.
     * Takes the IDs, classes and rates of the records to be updated from the
     * $_POST array variable and passes them on to {@link updateVat()}.
     */
    function _updateVat()
    {
        if (!empty($_POST['vatratenew'])) {
            self::$changed = true;
            self::$success &= Vat::addVat($_POST['vatclassnew'], $_POST['vatratenew']);
        }
        if (isset($_POST['vatclass'])) {
            self::$changed = true;
            self::$success &= Vat::updateVat($_POST['vatclass'], $_POST['vatrate']);
        }
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
     * @global  ADONewConnection
     */
    function setProductsVat()
    {
        global $objDatabase;

        $vatId = 0;
        $query_where = '';
        if (isset($_GET['setVatAll'])) {
            $vatId = intval($_GET['setVatAll']);
        }
        if (isset($_GET['setVatUnset'])) {
            $vatId = intval($_GET['setVatUnset']);
            $query_where = ' WHERE vat_id IS NULL OR vat_id=0';
        }
        if ($vatId) {
            self::$changed = true;
            self::$success &= $objDatabase->Execute("
                UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_products
                   SET vat_id=$vatId".$query_where
            );
        }
    }


    static function errorHandler()
    {
        require_once(ASCMS_CORE_PATH.'/DbTool.class.php');

DBG::activate(DBG_DB_FIREPHP);

        $table_name = DBPREFIX.'module_shop'.MODULE_INDEX.'_config';

        if (DbTool::table_exists($table_name)) {
            // Migrate all entries using the SettingDb class
            $objResult = DbTool::sql("
                SELECT `name`, `value`, `status`
                  FROM ".DBPREFIX."module_shop".MODULE_INDEX."_config
                 ORDER BY `id` ASC");
            if (!$objResult) {
die("ShopSettings::errorHandler(): Error: failed to query config, code rstjaer57z");
            }
            SettingDb::init('config');
            $i = 0;
            while (!$objResult->EOF) {
                $name = $objResult->fields['name'];
                $value = $objResult->fields['value'];
                $status = $objResult->fields['status'];
                $name_status = null;
                switch ($name) {
                  // OBSOLETE
                  case 'tax_default_id':
                  case 'tax_enabled':
                  case 'tax_included':
                  case 'tax_number':
                    // Ignore, do not migrate!
                    $name = null;
                    break;

                  // VALUE ONLY (RE: arrConfig\[.*?\]\[.value.\])
                  case 'confirmation_emails':
                    $name = 'email_confirmation';
                    break;
                  case 'country_id':
                  case 'datatrans_merchant_id':
                  case 'datatrans_request_type':
                    break;
                  case 'datatrans_status':
                    $name = 'datatrans_active';
                    break;
                  case 'datatrans_use_testserver':
                  case 'email':
                  case 'fax':
                  case 'orderitems_amount_max':
                  case 'paypal_default_currency':
                  case 'postfinance_mobile_ijustwanttotest':
                  case 'postfinance_mobile_sign':
                  case 'postfinance_mobile_status':
                  case 'postfinance_mobile_webuser':
                  case 'product_sorting':
                  case 'saferpay_finalize_payment':
                  case 'saferpay_window_option':
                  case 'shop_address':
                  case 'shop_company':
                    break;
                  case 'shop_show_products_default':
                  case 'shop_thumbnail_max_height':
                  case 'shop_thumbnail_max_width':
                  case 'shop_thumbnail_quality':
                  case 'shop_weight_enable':
                    $name = preg_replace('/^shop_/', '', $name);
                    break;
                  case 'telephone':
                  case 'vat_default_id':
                  case 'vat_enabled_foreign_customer':
                  case 'vat_enabled_foreign_reseller':
                  case 'vat_enabled_home_customer':
                  case 'vat_enabled_home_reseller':
                  case 'vat_included_foreign_customer':
                  case 'vat_included_foreign_reseller':
                  case 'vat_included_home_customer':
                  case 'vat_included_home_reseller':
                  case 'vat_number':
                  case 'vat_other_id':
                    break;
                  case 'yellowpay_accepted_payment_methods':
                  case 'yellowpay_authorization_type':
                  case 'yellowpay_hash_seed':
                  case 'yellowpay_hash_signature_in':
                  case 'yellowpay_hash_signature_out':
                  case 'yellowpay_id':
                  case 'yellowpay_use_testserver':
                    $name = preg_replace('/^yellowpay(.*)$/', 'postfinance$1', $name);
                    break;

                  // VALUE & STATUS
                  case 'paypal_account_email':
                    $name_status = 'paypal_active';
                    break;
                  case 'saferpay_id':
                    $name_status = 'saferpay_active';
                    break;
                  case 'yellowpay_shop_id':
                    $name_status = 'postfinance_active';
                    break;

                  // STATUS ONLY (RE: arrConfig\[.*?\]\[.status.\])
                  case 'payment_lsv_status':
                    $name_status = 'payment_lsv_active';
                    $name = null;
                    break;
                  case 'saferpay_use_test_account':
                    $name_status = $name;
                    $name = null;
                    break;
                }
                if ($name && !SettingDb::add($name, $value, ++$i)) {
die("ShopSettings::errorHandler(): Error: failed to add SettingDb entry for $name, code adgmezwea442wy");
                }
                if ($name_status && !SettingDb::add($name, $status, ++$i)) {
die("ShopSettings::errorHandler(): Error: failed to add SettingDb entry for $name, code srs37sus");
                }
                $objResult->MoveNext();
            }
        }

        if (!DbTool::drop_table($table_name)) {
die("ShopSettings::errorHandler(): Error: failed to drop table $table_name, code aw47ane");
        }

        // Always
        return false;
    }

}

?>
