<?php

/**
 * Shipment class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  module_shop
 */

/**
 * Useful methods to handle everything related to shipments
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  module_shop
 */
class Shipment
{
    /**
     * Text keys
     */
    const TEXT_NAME = 'shop_shipper_name';

    /**
     * Array of active shippers
     * @static
     * @var     array
     * @access  private
     */
    private static $arrShippers  = null;

    /**
     * Array of active shipment conditions
     * @static
     * @var     array
     * @access  private
     */
    private static $arrShipments = null;


    /**
     * Initialize shippers and shipment conditions
     *
     * Use $all=true for the backend settings.
     * Reads the shipping options from the shipper (s) and shipment_cost (c)
     * tables.  For each shipper, creates array entries like:
     * arrShippers[s.id] = array (
     *      name       => s.name,
     *      status     => s.status
     * )
     * arrShipments[s.id][c.id] = array (
     *      max_weight => c.max_weight,
     *      free_from => c.free_from,
     *      fee       => c.fee
     * )
     * Note that the table module_shop_shipment has been replaced by
     * module_shop_shipper (id, name, status) and
     * module_shop_shipment_cost (id, shipper_id, max_weight, fee, free_from)
     * as of version 1.1.
     * @global  ADONewConnection
     * @param   boolean   $all        If true, includes inactive records.
     *                                Defaults to false.
     * @return  void
     * @since   1.1
     * @version 2.1.0
     */
    static function init($all=false)
    {
        global $objDatabase;

        $arrSqlName = Text::getSqlSnippets(
            '`shipper`.`text_name_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_NAME);
        $objResult = $objDatabase->Execute("
            SELECT `shipper`.`id`, `shipper`.`active`".$arrSqlName['field']."
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_shipper` as `shipper`".
                   $arrSqlName['join'].
             ($all ? '' : ' WHERE `shipper`.`active`=1')."
             ORDER BY `shipper`.`id` ASC");
        if (!$objResult) return self::errorHandler();
        while (!$objResult->EOF) {
            $sid = $objResult->fields['id'];
            $text_name_id = $objResult->fields[$arrSqlName['name']];
            $strName = $objResult->fields[$arrSqlName['text']];
            // Replace Text in a missing language by another, if available
            if ($text_name_id && $strName === null) {
                $objText = Text::getById($text_name_id, 0);
                if ($objText)
                    $objText->markDifferentLanguage(FRONTEND_LANG_ID);
                    $strName = $objText->getText();
            }
            self::$arrShippers[$sid] = array(
                'id' => $objResult->fields['id'],
                'name' => $strName,
                'text_name_id' => $text_name_id,
                'active' => $objResult->fields['active'],
            );
            $objResult->MoveNext();
        }
        // Now get the associated shipment conditions from shipment_cost
        $objResult = $objDatabase->Execute("
            SELECT `c`.`id`, `c`.`shipper_id`,
                   `c`.`max_weight`, `c`.`fee`, `c`.`free_from`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_shipment_cost` AS `c`
             INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_shipper` AS `s`
                ON `s`.`id`=`shipper_id`");
        if (!$objResult) return false;
        while (!$objResult->EOF) {
            $sid = $objResult->fields['shipper_id'];
            $cid = $objResult->fields['id'];
            self::$arrShipments[$sid][$cid] =
                array(
                    'max_weight' => Weight::getWeightString($objResult->fields['max_weight']),
                    'free_from'  => $objResult->fields['free_from'],
                    'fee'        => $objResult->fields['fee'],
                );
            $objResult->MoveNext();
        }
        return true;
    }


    /**
     * Returns the name of the shipper with the given ID
     * @static
     * @param   integer   $shipperId  The shipper ID
     * @return  string                The shipper name
     */
    static function getShipperName($shipperId)
    {
        if (empty($shipperId)) return '';
        if (is_null(self::$arrShippers)) self::init(true);
        if (empty(self::$arrShippers[$shipperId])) return '';
        return self::$arrShippers[$shipperId]['name'];
    }


    /**
     * Access method.  Returns the arrShippers array.
     *
     * See {@link init()}.
     * @param   boolean   $all      Include inactive Shippers if true.
     *                              Defaults to false.
     * @return  array               The array of shippers
     * @static
     */
    static function getShippersArray($all=false)
    {
        if (is_null(self::$arrShippers)) self::init($all);
        return self::$arrShippers;
    }

    /**
     * Access method.  Returns the arrShipments array.
     *
     * See {@link Shipment()}.
     * @return  array               The array of shipments
     * @static
     */
    static function getShipmentsArray()
    {
        if (is_null(self::$arrShipments)) self::init(true);
        return self::$arrShipments;
    }


    /**
     * Returns the shipment arrays (shippers and shipment costs) in JavaScript
     * syntax.
     *
     * Backend use only.
     * @static
     * @return  string              The Shipment arrays definition
     */
    static function getJSArrays()
    {
        if (is_null(self::$arrShippments)) self::init(true);
        // Set up shipment cost javascript arrays
        // Shippers are not needed for calculating the shipment costs
        //$strJsArrays = "arrShippers = new Array();\narrShipments = new Array();\n";
        $strJsArrays = "arrShipments = new Array();\n";
        // Insert shippers by id
        foreach (array_keys(self::$arrShippers) as $sid) {
            //$strJsArrays .= "arrShippers[$sid] = new Array('".
            //    self::$arrShippers[$sid]['name']."', ".
            //    self::$arrShippers[$sid]['status'].");\n";
            // Insert shipments by shipper id
            $strJsArrays .= "arrShipments[$sid] = new Array();\n";
            $i = 0;
            if (isset(self::$arrShipments[$sid])) {
                foreach (self::$arrShipments[$sid] as $cid => $arrShipment) {
                    $strJsArrays .=
                        "arrShipments[$sid][".$i++."] = new Array('$cid', '".
                        $arrShipment['max_weight']."', '".   // string
                        Currency::getCurrencyPrice($arrShipment['free_from'])."', '".
                        Currency::getCurrencyPrice($arrShipment['fee'])."');\n";
                }
            }
        }
        return $strJsArrays;
    }


    /**
     * Returns an array of shipper ids relevant for the country specified by
     * the argument $countryId.
     * @internal Note that s.shipment_id below now associates with shipper.id
     * @param   integer $countryId      The optional country ID
     * @return  array                   Array of shipment IDs on success,
     *                                  false otherwise
     * @static
     * @todo    Rename shipment_id to shipper_id in all affected relations
     */
    static function getCountriesRelatedShippingIdArray($countryId=0)
    {
        global $objDatabase;

        if (empty(self::$arrShippers)) self::init();
        // Mind that s.shipment_id actually points to a shipper, not a shipment!
        $query = "
            SELECT `r`.`shipper_id`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries` AS `c`
             INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_zones` AS `z`
                ON `c`.`zone_id`=`z`.`id`
             INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_rel_shipment` AS `r`
                ON `z`.`id`=`r`.`zone_id`
             INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_shipper` AS `s`
                ON `r`.`shipper_id`=`s`.`id`
             WHERE `z`.`active`=1
               AND `s`.`active`=1".
              ($countryId ? " AND `c`.`country_id`=$countryId" : '');
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $arrShipperId = array();
        while ($objResult && !$objResult->EOF) {
            $shipper = $objResult->fields['shipper_id'];
            if (isset(self::$arrShippers[$shipper]))
                $arrShipperId[] = $shipper;
            $objResult->MoveNext();
        }
        return $arrShipperId;
    }


    /**
     * Returns the shipper dropdown menu string.
     *
     * For the admin zone (order edit page), you *MUST* specify $onchange,
     * so that both the onchange call and the <select> tag are added.
     *? For use in the user zone (shop, frontend), you *MUST NOT* specify the
     *? $onchange call to update the
     *?
     * The entry with ID $selectedId will have the selected attribute added,
     * if found.  If the $onchange string is specified and non-null, it will be
     * inserted into the <select> string as the onchange attribute value.
     * @param   string  $selectedId     Optional preselected shipment ID
     * @param   string  $onchange       Optional onchange javascript callback
     * @return  string                  Dropdown menu string
     * @global  array
     * @static
     */
    static function getShipperMenu($countryId=0, $selectedId=0, $onchange="")
    {
        global $_ARRAYLANG;

        if (empty(self::$arrShippers)) self::init();
        $arrId = self::getCountriesRelatedShippingIdArray($countryId);
        $menu =
            (   intval($selectedId) == 0
             && count($arrId) > 1
                ? '<option value="0" selected="selected">'.
                  $_ARRAYLANG['TXT_SHOP_SHIPMENT_PLEASE_SELECT'].
                  "</option>\n"
                : ''
            );
        $haveShipper = false;
        foreach (array_keys(self::$arrShippers) as $sid) {
            // Only show suitable shipments in the menu if the user is on the payment page,
            // check the availability of the shipment in her country,
            // and verify that the shipper will be able to handle the freight.
            if (!($_REQUEST['cmd'] == 'payment') ||
                ((!$countryId || in_array($sid, $arrId)) &&
                self::calculateShipmentPrice(
                    $sid,
                    $_SESSION['shop']['cart']['total_price'],
                    $_SESSION['shop']['cart']['total_weight']) != -1
            )) {
                $menu .=
                    '<option value="'.$sid.'"'.
                    ($sid==intval($selectedId) ? ' selected="selected"' : '').
                    '>'.self::$arrShippers[$sid]['name']."</option>\n";
                $haveShipper = true;
            }
        }
        if (!$haveShipper)
            return $_ARRAYLANG['TXT_SHOP_SHIPMENT_TOO_HEAVY'];
        if ($onchange)
            $menu =
                '<select name="shipperId" onchange="'.$onchange.'">'.$menu.'</select>';
        return $menu;
    }


    /**
     * Delete a Shipper from the database
     *
     * Deletes related Text, shipment cost, and zone relation records as well.
     * @param   integer     $sid    The Shipper ID
     * @return  boolean             True on success, false otherwise.
     * @static
     */
    static function deleteShipper($sid)
    {
        global $objDatabase;

        if (empty(self::$arrShippers)) self::init();
        if (empty(self::$arrShippers[$sid])) return false;
//        if (!Text::deleteById(self::$arrShippers[$sid]['text_name_id'])) return false;
        $objResult = $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_shipper WHERE id=".$sid);
        if (!$objResult) return false;
        $objResult = $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_shipment_cost WHERE shipper_id=".$sid);
        if (!$objResult) return false;
        $objResult = $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_rel_shipment WHERE shipper=".$sid);
        if (!$objResult) return false;
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_shipper");
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_shipment_cost");
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_shipment");
        return true;
    }


    /**
     * Delete a Shipment entry from the database
     * @param   integer     $cid    The Shipment ID
     * @return  boolean             True on success, false otherwise
     * @static
     */
    static function deleteShipment($cid)
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            DELETE FROM `".DBPREFIX."module_shop".MODULE_INDEX."_shipment_cost`
             WHERE `id`=$cid");
        return (boolean)$objResult;
    }


    /**
     * Add a Shipper to the database
     * @param   string  $name       The Shipper name
     * @param   boolean $active     If true, the Shipper is made active.
     *                              Defaults to false
     * @return  boolean             True on success, false otherwise
     * @static
     */
    function addShipper($name, $active=false)
    {
        global $objDatabase;

        $text_name_id = Text::replace(
            null, FRONTEND_LANG_ID, $name,
            MODULE_ID, self::TEXT_NAME);
        if (!$text_name_id) return false;
        $objResult = $objDatabase->Execute("
            INSERT INTO `".DBPREFIX."module_shop".MODULE_INDEX."_shipper` (
                `text_name_id`, `active`
            ) VALUES (
                $text_name_id, ".($active ? 1 : 0)."
            )");
        return (boolean)$objResult;
    }


    /**
     * Add a Shipment entry to the database
     * @param   integer $sid            The associated Shipper ID
     * @param   double  $fee            The fee for delivery
     * @param   double  $free_from      The minimum order value to get a free delivery
     * @param   integer $max_weight     The maximum weight of the delivery
     * @return  boolean                 True on success, false otherwise
     * @static
     */
    static function addShipment($sid, $fee, $free_from, $max_weight)
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            INSERT INTO `".DBPREFIX."module_shop".MODULE_INDEX."_shipment_cost` (
                `shipper_id`, `fee`, `free_from`, `max_weight`
            ) VALUES (
                $sid, $fee, $free_from, $max_weight
            )");
        return (boolean)$objResult;
    }


    /**
     * Update a Shipment entry
     * @param   integer $cid            The Shipment ID
     * @param   integer $sid            The associated Shipper ID
     * @param   double  $fee            The fee for delivery
     * @param   double  $free_from      The minimum order value to get a free delivery
     * @param   integer $max_weight     The maximum weight of the delivery
     * @return  boolean                 True on success, false otherwise
     * @static
     */
    static function updateShipment($cid, $sid, $fee, $free_from, $max_weight)
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            UPDATE `".DBPREFIX."module_shop".MODULE_INDEX."_shipment_cost`
               SET `shipper_id`=$sid,
                   `fee`=$fee,
                   `free_from`=$free_from,
                   `max_weight`=$max_weight
             WHERE `id`=$cid");
        return (boolean)$objResult;
    }


    /**
     * Update the Shipper
     *
     * Note that the name cannot be changed in the settings.
     * Create a new shipper, change the association from the old shipper
     * to the new one, and delete the old.
     * @param   integer $svalue     The ID of the Shipper
     * @param   boolean $active     If true, the Shipper is made active.
     *                              Defaults to false
     * @return  boolean             True on success, false otherwise
     * @static
     */
    static function updateShipper($sid, $active)
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            UPDATE `".DBPREFIX."module_shop".MODULE_INDEX."_shipper`
               SET `active`=".($active ? 1 : 0)."
             WHERE `id`=$sid");
        return (boolean)$objResult;
    }


    /**
     * Calculate the shipment price for the given Shipper ID, order price and
     * total weight.
     *
     * Returns the shipment price in default currency, or -1 if there is any kind
     * of problem with the shipment conditions.
     * The weight is converted from string using {@link Weight::getWeight()}
     * to make sure that grams are used.
     * Note: You have to convert the returned value to the customers' currency
     * using {@link Currency::getCurrencyPrice()}!
     * @param   integer $shipperId  The Shipper ID
     * @param   double  $price      The total order price
     * @param   integer $weight     The total order weight in grams.
     * @return  double              The cost for shipping in the default
     *                              currency, or -1.
     * @static
     */
    static function calculateShipmentPrice($shipperId, $price, $weight)
    {
        if (empty(self::$arrShippers)) self::init();
        // Are there conditions available from this shipper?
        // Otherwise, don't even try to find one. return
        if (!isset(self::$arrShipments[$shipperId])) return -1;
        // check shipments available by this shipper
        $arrShipment = self::$arrShipments[$shipperId];
        // Find the best match for the current order weight and shipment cost.
        // Arbitrary upper limit - we *SHOULD* be able to find one that's lower!
        // We'll just try to find the cheapest way to handle the delivery.
        $lowest_cost = 1e100;
        // Temporary shipment cost
        $fee = 0;
        // Found flag is set to the index of a suitable shipment, if encountered below.
        // If the flag stays at -1, there is no way to deliver it!
        $found = -1;
        // Try all the available shipments
        // (see Shipment.class.php::getJSArrays())
        foreach ($arrShipment as $cid => $conditions) {
            $free_from = $conditions['free_from'];
            $max_weight = Weight::getWeight($conditions['max_weight']);
            // Get the shipment conditions that are closest to our order:
            // We have to make sure the maximum weight is big enough for the order,
            // or that it's unspecified (don't care)
            if (($max_weight > 0 && $weight <= $max_weight) || $max_weight == 0) {
                // If free_from is set, the order amount has to be higher than that
                // in order to get the shipping for free.
                if ($free_from > 0 && $price >= $free_from) {
                    // We're well within the weight limit, and the order is also expensive
                    // enough to get a free shipping.
                    $fee = '0.00';
                } else {
                    // Either the order amount is too low, or free_from is unset, or zero,
                    // so the shipping has to be paid for in any case.
                    $fee = $conditions['fee'];
                }
                // We found a kind of shipment that can handle the order, but maybe
                // it's too expensive. - keep the cheapest way to deliver it
                if ($fee < $lowest_cost) {
                    // Found a cheaper one. keep the index.
                    $found = $cid;
                    $lowest_cost = $fee;
                }
            }
        }
        if ($found > 0) {
            // After checking all the shipments, we found the lowest cost for the
            // given weight and order price. - update the shipping cost
            return $lowest_cost;
        }
        // Cannot find suitable shipment conditions for the selected shipper.
        return -1;
    }


    /**
     * Returns an array containing all the active shipment conditions.
     * @global  ADONewConnection  $objDatabase
     * @global  array   $_ARRAYLANG
     * @return  array             Countries and conditions array on success,
     *                            false otherwise
     * @static
     */
    static function getShipmentConditions()
    {
        global $objDatabase, $_ARRAYLANG;

        if (empty(self::$arrShippers)) self::init();

        // Get shippers and associated countries (via zones).
        // Make an array(shipper_name => array( array(country, ...), array(conditions) )
        // where the countries are listed as strings of their names,
        // and the conditions look like: array(max_weight, free_from, fee)

        // Return this
        $arrResult = array();
        foreach (self::$arrShippers as $sid => $shipper) {
            // Get countries covered by this shipper
            $arrSqlName = Country::getSqlSnippets();
            $query ="
                SELECT DISTINCT `country`.`id`".
                       $arrSqlName['field']."
                  FROM `".DBPREFIX."module_shop".MODULE_INDEX."_shipper` AS `s`
                 INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_rel_shipment` AS `rs`
                    ON `s`.`id`=`rs`.`shipment_id`
                 INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_zones` AS `z`
                    ON `rs`.`zone_id`=`z`.`id`
                 INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries` AS `rc`
                    ON `z`.`id`=`rc`.`zone_id`
                 INNER JOIN `".DBPREFIX."core_country` AS `country`
                    ON `rc`.`country_id`=`country`.`id`".
                       $arrSqlName['join']."
                 WHERE `s`.`shipment_id`=$sid
                   AND `z`.`active`=1
                   AND `s`.`active`=1
                 ORDER BY ".$arrSqlName['text']." ASC";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return self::errorHandler();
            $arrCountries = array();
            while (!$objResult->EOF) {
                $country_id = $objResult->fields['id'];
                $arrCountries[$country_id] =
                    $objResult->fields[$arrSqlName['text']];
                $objResult->MoveNext();
            }
            // Now add the conditions, and order them by weight
            $arrConditions = array();
            foreach (self::$arrShipments[$sid] as $arrCond) {
                $arrConditions[$arrCond['max_weight']] = array(
                    'max_weight' => ($arrCond['max_weight'] > 0
                        ? $arrCond['max_weight']
                        : $_ARRAYLANG['TXT_SHOP_WEIGHT_UNLIMITED']
                    ),
                    'free_from' => ($arrCond['free_from'] > 0
                        ? $arrCond['free_from']
                        : '-'
                    ),
                    'fee' => ($arrCond['fee'] > 0
                        ? $arrCond['fee']
                        : $_ARRAYLANG['TXT_SHOP_COST_FREE']
                    ),
                );
            }
            krsort($arrConditions);
            $arrResult[$shipper['name']] = array(
                'countries'  => $arrCountries,
                'conditions' => $arrConditions,
            );
        }
        return $arrResult;
    }


    /**
     * Get the shipper name for the ID given
     * @static
     * @global  ADONewConnection
     * @param   integer   $shipperId      The shipper ID
     * @return  mixed                     The shipper name on success,
     *                                    false otherwise
     * @since   1.2.1
     */
    static function getNameById($shipperId)
    {
        if (empty(self::$arrShippers)) self::init();
        return self::$arrShippers[$shipperId]['name'];
    }


    function errorHandler()
    {
        require_once(ASCMS_CORE_PATH.'/DbTool.class.php');
        static $break = false;

        if ($break) {
            die("
                Shipment::errorHandler(): Recursion detected while handling an error.<br /><br />
                This should not happen.  We are very sorry for the inconvenience.<br />
                Please contact customer support: support@comvation.com");
        }
        $break = true;

//die("Shipment::errorHandler(): Disabled!<br />");

        $table_name = DBPREFIX.'module_shop_shipper';
        $table_structure = array(
            'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
            'text_name_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'renamefrom' => 'name'),
            'ord' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
            'active' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'renamefrom' => 'status'),
        );
        $table_index = array();

        if (DbTool::table_exists($table_name)) {
            if (DbTool::column_exists($table_name, 'name')) {
                Text::deleteByKey(self::TEXT_NAME);
                $objResult = DbTool::sql("
                    SELECT `id`, `name`
                      FROM `$table_name`");
                if (!$objResult) {
die("Shipment::errorHandler(): Error: failed to query names, code jrstujrths43w");
                }
                while (!$objResult->EOF) {
                    $id = $objResult->fields['id'];
                    $name = $objResult->fields['name'];
                    $text_name_id = Text::replace(
                        null, FRONTEND_LANG_ID,
                        $name, MODULE_ID, self::TEXT_NAME);
                    if (!$text_name_id) {
die("Shipment::errorHandler(): Error: failed to migrate name '$name', code gfs4wuhtj");
                    }
                    $objResult2 = DbTool::sql("
                        UPDATE `$table_name`
                           SET `name`='$text_name_id'
                         WHERE `id`=$id");
                    if (!$objResult2) {
die("Shipment::errorHandler(): Error: failed to update Shipper ID $id, code ejrsr5t348ujf");
                    }
                    $objResult->MoveNext();
                }
            }
        }
        if (!DbTool::table($table_name, $table_structure, $table_index)) {
die("Shipment::errorHandler(): Error: failed to migrate Shipper table, code eja47ujed");
        }

        $table_name = DBPREFIX.'module_shop_shipment_cost';
        $table_structure = array(
            'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
            'shipper_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
            'max_weight' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null),
            'fee' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'notnull' => false, 'default' => null, 'renamefrom' => 'cost'),
            'free_from' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'notnull' => false, 'default' => null, 'renamefrom' => 'price_free'),
        );
        $table_index = array();
        if (!DbTool::table($table_name, $table_structure, $table_index)) {
die("Shipment::errorHandler(): Error: failed to migrate Shipment cost table, code mserjew43erj");
        }

        // More to come...

        // Always!
        return false;
    }

}

?>
