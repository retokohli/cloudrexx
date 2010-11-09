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

        SettingDb::init();
        self::storeGeneral();
        self::storeCurrencies();
        self::storePayments();
        self::storeCountries();
        Zones::store();
        Mailtemplate::storeTemplateFromPost();
        self::storeShipping();
        self::storeVat();
        if (SettingDb::updateAll() === false) return false;
        if (self::$changed) return self::$success;
        return null;
    }


    /**
     * Store general settings
     *
     * @return  boolean     true on success, false otherwise.
     */
    function storeGeneral()
    {
        if (empty($_POST['general'])) return;

        SettingDb::set('email', $_POST['email']);
        SettingDb::set('email_confirmation', $_POST['email_confirmation']);
        // added: shop company name and address
        SettingDb::set('company', $_POST['company']);
        SettingDb::set('address', $_POST['address']);
        SettingDb::set('telephone', $_POST['telephone']);
        SettingDb::set('fax', $_POST['fax']);
        SettingDb::set('country_id', $_POST['country_id']);

        // Postfinance (FKA yellowpay)
        $strYellowpayAcceptedPM = (isset($_POST['postfinance_accepted_payment_methods'])
            ? addslashes(join(',', $_POST['postfinance_accepted_payment_methods']))
            : ''
        );
        SettingDb::set('postfinance_shop_id', $_POST['postfinance_shop_id']);
        SettingDb::set('postfinance_active', empty($_POST['postfinance_active']) ? 0 : 1);
        //SettingDb::set('postfinance_hash_seed', $_POST['postfinance_hash_seed']);
        // Replaced by
        SettingDb::set('postfinance_hash_signature_in', $_POST['postfinance_hash_signature_in']);
        SettingDb::set('postfinance_hash_signature_out', $_POST['postfinance_hash_signature_out']);
        SettingDb::set('postfinance_authorization_type', $_POST['postfinance_authorization_type']);
        SettingDb::set('postfinance_accepted_payment_methods', $strYellowpayAcceptedPM);
        SettingDb::set('postfinance_use_testserver', $_POST['postfinance_use_testserver']);

        // Postfinance Mobile
        SettingDb::set('postfinance_mobile_webuser', $_POST['postfinance_mobile_webuser']);
        SettingDb::set('postfinance_mobile_sign', $_POST['postfinance_mobile_sign']);
        SettingDb::set('postfinance_mobile_ijustwanttotest', isset($_POST['postfinance_mobile_ijustwanttotest']));
        SettingDb::set('postfinance_mobile_status', isset($_POST['postfinance_mobile_status']));

        // Saferpay
        SettingDb::set('saferpay_id', $_POST['saferpay_id']);
        SettingDb::set('saferpay_active', empty($_POST['saferpay_active']) ? 0 : 1);
        SettingDb::set('saferpay_finalize_payment', empty($_POST['saferpay_finalize_payment']) ? 0 : 1);
        SettingDb::set('saferpay_use_test_account', empty($_POST['saferpay_use_test_account']) ? 0 : 1);
        SettingDb::set('saferpay_window_option', $_POST['saferpay_window_option']);

        // Paypal
        SettingDb::set('paypal_account_email', $_POST['paypal_account_email']);
        SettingDb::set('paypal_active', empty($_POST['paypal_active']) ? 0 : 1);
        SettingDb::set('paypal_default_currency', $_POST['paypal_default_currency']);

        // Datatrans
        SettingDb::set('datatrans_merchant_id', trim(contrexx_strip_tags($_POST['datatrans_merchant_id'])));
        SettingDb::set('datatrans_active', empty($_POST['datatrans_active']) ? 0 : 1);
        SettingDb::set('datatrans_request_type', $_POST['datatrans_request_type']);
        SettingDb::set('datatrans_use_testserver', empty($_POST['datatrans_use_testserver']) ? 0 : 1);

        // LSV
        SettingDb::set('payment_lsv_active', empty($_POST['payment_lsv_active']) ? 0 : 1);

        // Thumbnail settings
        SettingDb::set('thumbnail_max_width', $_POST['thumbnail_max_width']);
        SettingDb::set('thumbnail_max_height', $_POST['thumbnail_max_height']);
        SettingDb::set('thumbnail_quality', $_POST['thumbnail_quality']);

        // Various settings
        SettingDb::set('weight_enable', empty($_POST['weight_enable']) ? 0 : 1);
        SettingDb::set('show_products_default',
            empty($_POST['show_products_default'])
              ? 0 : $_POST['show_products_default']);
        // Mind that this defaults to 1, zero is not a valid value
        SettingDb::set('product_sorting',
            empty($_POST['product_sorting'])
              ? 1 : $_POST['product_sorting']);
        // Order amount upper limit (applicable when using Saferpay)
        SettingDb::set('orderitems_amount_max',
            empty($_POST['orderitems_amount_max'])
                ? 0 : $_POST['orderitems_amount_max']);
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
        Payment::reset();
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
        Shipment::reset();
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
        if (empty($_POST['shipperNameNew'])) return true;
        self::$changed = true;
        $shipper_id = Shipment::addShipper(
            $_POST['shipperNameNew'],
            (isset($_POST['shipperActiveNew']) ? 1 : 0),
            intval($_POST['shipmentZoneNew'])
        );
        if (!$shipper_id) return false;
        return Zones::storeShipmentRelation(
            $_POST['shipmentZoneNew'], $shipper_id);
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
            foreach ($_POST['shipmentMaxWeightNew'] as $shipper_id => $value) {
                if (   (isset($value) && $value > 0)
                    || (   isset($_POST['shipmentCostNew'][$shipper_id])
                        && $_POST['shipmentCostNew'][$shipper_id] > 0)
                    || (   isset($_POST['shipmentPriceFreeNew'][$shipper_id])
                        && $_POST['shipmentPriceFreeNew'][$shipper_id] > 0)
                ) {
                    self::$changed = true;
                    $success &= Shipment::addShipment(
                        $shipper_id,
                        floatval($_POST['shipmentCostNew'][$shipper_id]),
                        floatval($_POST['shipmentPriceFreeNew'][$shipper_id]),
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
        $success = true;
        if (isset($_POST['shipment']) && !empty($_POST['shipment'])) {
            self::$changed = true;
            // Update all shipment conditions
            if (!empty($_POST['shipmentMaxWeight'])) {
                foreach ($_POST['shipmentMaxWeight'] as $shipment_id => $cvalue) {
                    $shipper_id = $_POST['sid'][$shipment_id];
                    $success &= Shipment::updateShipment(
                        $shipment_id,
                        $shipper_id,
                        $_POST['shipmentCost'][$shipment_id],
                        $_POST['shipmentPriceFree'][$shipment_id],
                        Weight::getWeight($cvalue)
                    );
                }
            }

            foreach ($_POST['shipper_name'] as $shipper_id => $shipper_name) {
                // update the status field in the Shipper
                $shipperActive =
                    (empty($_POST['shipperActive'][$shipper_id]) ? false : true);
                $success &= Shipment::updateShipper(
                    $shipper_id,intval($shipperActive));
                $success &= Shipment::renameShipper(
                    $shipper_id, $shipper_name);

                // lastly, update the zones
                if ($_POST['old_shipmentZone'][$shipper_id] != $_POST['shipmentZone'][$shipper_id]) {
                    // zone has been changed.
                    // also use the (possibly changed) svalue where necessary.
                    // note that shipment_id here actually refers to a shipper!
                    if (!Zones::storeShipmentRelation(
                        $_POST['shipmentZone'][$shipper_id], $shipper_id)
                    ) {
                        $success = false;
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
        self::$success &= SettingDb::set('vat_number', $_POST['vat_number']);
        self::$success &= SettingDb::set('vat_default_id', $_POST['vat_default_id']);
        self::$success &= SettingDb::set('vat_other_id', $_POST['vat_other_id']);
        $vat_enabled_home_customer = (empty($_POST['vat_enabled_home_customer']) ? 0 : 1);
        self::$success &= SettingDb::set('vat_enabled_home_customer', $vat_enabled_home_customer);
        if ($vat_enabled_home_customer)
            self::$success &= SettingDb::set('vat_included_home_customer',
                (empty($_POST['vat_included_home_customer']) ? 0 : 1));
        $vat_enabled_home_reseller = (empty($_POST['vat_enabled_home_reseller']) ? 0 : 1);
        self::$success &= SettingDb::set('vat_enabled_home_reseller', $vat_enabled_home_reseller);
        if ($vat_enabled_home_reseller)
            self::$success &= SettingDb::set('vat_included_home_reseller',
                (empty($_POST['vat_included_home_reseller']) ? 0 : 1));
        $vat_enabled_foreign_customer = (empty($_POST['vat_enabled_foreign_customer']) ? 0 : 1);
        self::$success &= SettingDb::set('vat_enabled_foreign_customer', $vat_enabled_foreign_customer);
        if ($vat_enabled_foreign_customer)
            self::$success &= SettingDb::set('vat_included_foreign_customer',
                (empty($_POST['vat_included_foreign_customer']) ? 0 : 1));
        $vat_enabled_foreign_reseller = (empty($_POST['vat_enabled_foreign_reseller']) ? 0 : 1);
        self::$success &= SettingDb::set('vat_enabled_foreign_reseller', $vat_enabled_foreign_reseller);
        if ($vat_enabled_foreign_reseller)
            self::$success &= SettingDb::set('vat_included_foreign_reseller',
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
                    break;
                  case 'shop_address':
                  case 'shop_company':
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
                  case 'yellowpay_use_testserver':
                    $name = preg_replace('/^yellowpay(.*)$/', 'postfinance$1', $name);
                    break;
                  case 'yellowpay_id':
                    // Obsolete
                    $name = null;
                    break;

                  // VALUE & STATUS
                  case 'paypal_account_email':
                    $name_status = 'paypal_active';
                    break;
                  case 'saferpay_id':
                    $name_status = 'saferpay_active';
                    break;
                  case 'yellowpay_shop_id':
                    $name = 'postfinance_shop_id';
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
DBG::log("ShopSettings::errorHandler(): Warning: failed to add SettingDb entry for value $name, assuming it already exists");
                }
                if ($name_status && !SettingDb::add($name_status, $status, ++$i)) {
DBG::log("ShopSettings::errorHandler(): Warning: failed to add SettingDb entry for status $name_status, assuming it already exists");
                }
                $objResult->MoveNext();
            }
        }

        // Add new/missing settings, e.g.
//        SettingDb::add('product_sorting', 1, ++$i);
        // more?

        if (!DbTool::drop_table($table_name)) {
die("ShopSettings::errorHandler(): Error: failed to drop table $table_name, code aw47ane");
        }

        // Always
        return false;
    }

}

?>
