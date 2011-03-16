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
     * Array of active shippers
     * @static
     * @var     array
     * @access  private
     */
    private static $arrShippers  = array();

    /**
     * Array of active shipment conditions
     * @static
     * @var     array
     * @access  private
     */
    private static $arrShipments = array();


    /**
     * OBSOLETE -- All static now.
     * See {@link init()}.
     *
     * Construct a Shipment object
    function __construct($ignoreStatus=1)
    {
    }
     */

    /**
     * Initialize shippers and shipment conditions
     *
     * Read the shipping options from the shipper (s) and shipment_cost (c)
     * tables.  For each shipper, creates array entries like:
     * arrShippers[s.id] = array (
     *      name       => s.name,
     *      status     => s.status
     * )
     * arrShipments[s.id][c.id] = array (
     *      max_weight => c.max_weight,
     *      price_free => c.price_free,
     *      cost       => c.cost
     * )
     * Note that the table module_shop_shipment has been replaced by
     * module_shop_shipper (id, name, status) and
     * module_shop_shipment_cost (id, shipper_id, max_weight, cost, price_free)
     * as of version 1.1.
     * @global  ADONewConnection
     * @param   boolean   $ignoreStatus   If false, only records with status==1
     *                                    are returned, all records otherwise.
     *                                    Use $ignoreStatus=true for the
     *                                    backend settings.
     * @return  void
     * @since   1.1
     * @version 2.1.0
     */
    static function init()
    {
        global $objDatabase;

//        $arrSqlName = Text::getSqlSnippets(
//            '`shipper`.`text_name_id`', FRONTEND_LANG_ID,
//            MODULE_ID, TEXT_SHOP_SHIPPER_NAME
//        );
//        $objResult = $objDatabase->Execute("
//            SELECT `shipper`.`id`, `shipper`.`status`".$arrSqlName['field']."
//              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_shipper` as `shipper`".
//                   $arrSqlName['join']."
//             ORDER BY `shipper`.`id` ASC
//        ");
        $objResult = $objDatabase->Execute("
            SELECT `shipper`.`id`, `shipper`.`status`, `shipper`.`name`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_shipper` as `shipper`
             ORDER BY `shipper`.`id` ASC
        ");
        if (!$objResult) return false;
        while (!$objResult->EOF) {
            $sid = $objResult->fields['id'];
//            $text_name_id = $objResult->fields[$arrSqlName['name']];
//            $strName = $objResult->fields[$arrSqlName['text']];
//            // Replace Text in a missing language by another, if available
//            if ($text_name_id && $strName === null) {
//                $objText = Text::getById($text_name_id, 0);
//                if ($objText)
//                    $objText->markDifferentLanguage(FRONTEND_LANG_ID);
//                    $strName = $objText->getText();
//            }
            self::$arrShippers[$sid] = array(
                'id' => $objResult->fields['id'],
                'name' => $objResult->fields['name'], //$strName,
//                'text_name_id' => $text_name_id,
                'status' => $objResult->fields['status'],
            );
            $objResult->MoveNext();
        }
        // Now get the associated shipment conditions from shipment_cost
        $objResult = $objDatabase->Execute("
            SELECT `c`.`id`, `c`.`shipper_id`,
                   `c`.`max_weight`, `c`.`cost`, `c`.`price_free`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_shipment_cost` AS `c`
             INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_shipper` AS `s`
                ON `s`.`id`=`shipper_id`
        ");
        if (!$objResult) return false;
        while (!$objResult->EOF) {
            $sid = $objResult->fields['shipper_id'];
            $cid = $objResult->fields['id'];
            self::$arrShipments[$sid][$cid] =
                array(
                    'max_weight' => Weight::getWeightString($objResult->fields['max_weight']),
                    'price_free' => $objResult->fields['price_free'],
                    'cost'       => $objResult->fields['cost'],
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
        if (empty(self::$arrShippers)) self::init(true);
        if (empty(self::$arrShippers[$shipperId])) return '';
        return self::$arrShippers[$shipperId]['name'];
    }


    /**
     * Access method.  Returns the arrShippers array.
     * See {@link init()}.
     * @return                The array of shippers
     * @static
     */
    static function getShippersArray()
    {
        if (empty(self::$arrShippers)) self::init();
        return self::$arrShippers;
    }

    /**
     * Access method.  Returns the arrShipments array.
     * See {@link Shipment()}.
     * @return  array         The array of shipments
     * @static
     */
    static function getShipmentsArray()
    {
        if (empty(self::$arrShippments)) self::init();
        return self::$arrShipments;
    }


    /**
     * Returns the shipment arrays (shippers and shipment costs) in JavaScript
     * syntax.
     * @static
     * @return  string      The Shipment arrays definition in JavaScript
     */
    static function getJSArrays()
    {
        if (empty(self::$arrShippers)) self::init();
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
                        Currency::getCurrencyPrice($arrShipment['price_free'])."', '".
                        Currency::getCurrencyPrice($arrShipment['cost'])."');\n";
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
            SELECT `r`.`shipment_id` AS `shipper_id`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries` AS `c`
             INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_zones` AS `z`
                ON `c`.`zones_id`=`z`.`zones_id`
             INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_rel_shipment` AS `r`
                ON `z`.`zones_id`=`r`.`zones_id`
             INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_shipper` AS `s`
                ON `r`.`shipment_id`=`s`.`id`
             WHERE `z`.`activation_status`=1
               AND `s`.`status`=1".
              ($countryId ? " AND `c`.`countries_id`=$countryId" : '');
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

        if (count($arrId) == 1) {
            $arrShipper = self::$arrShippers[current($arrId)];
            return $arrShipper['name'];
        }

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
            // only show suitable shipments in the menu if the user is on the payment page,
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
        $objResult = $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_rel_shipment WHERE shipment_id=".$sid);
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
             WHERE `id`=$cid
        ");
        return ($objResult ? true : false);
    }


    /**
     * Add a Shipper to the database
     * @param   string  $name       The Shipper name
     * @param   boolean $isActive   Marking the Shipper as active -- or not
     * @param   integer $zone       The zone the Shipper is in
     * @return  boolean             True on success, false otherwise
     * @static
     */
    function addShipper($name, $isActive)
    {
        global $objDatabase;

//        $objText = new Text($name, FRONTEND_LANG_ID, MODULE_ID, TEXT_SHOP_SHIPPER_NAME);
//        if (!$objText->store()) return false;
//        $objResult = $objDatabase->Execute("
//            INSERT INTO `".DBPREFIX."module_shop".MODULE_INDEX."_shipper` (
//                `text_name_id`, `status`
//            ) VALUES (
//                ".$objText->getId().", ".($isActive ? 1 : 0)."
//            )
//        ");
        $objResult = $objDatabase->Execute("
            INSERT INTO `".DBPREFIX."module_shop".MODULE_INDEX."_shipper` (
                `name`, `status`
            ) VALUES (
                '".addslashes($name)."', ".($isActive ? 1 : 0)."
            )
        ");
        return ($objResult ? true : false);
    }


    /**
     * Add a Shipment entry to the database
     * @param   integer $sid            The associated Shipper ID
     * @param   double  $cost           The cost of delivery
     * @param   double  $price_free     The minimum order value to get a free delivery
     * @param   integer $max_weight     The maximum weight of the delivery
     * @return  boolean                 True on success, false otherwise
     * @static
     */
    static function addShipment($sid, $cost, $price_free, $max_weight)
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            INSERT INTO `".DBPREFIX."module_shop".MODULE_INDEX."_shipment_cost` (
                `shipper_id`, `cost`, `price_free`, `max_weight`
            ) VALUES (
                $sid, $cost, $price_free, $max_weight
            )
        ");
        return ($objResult ? true : false);
    }


    /**
     * Update a Shipment entry
     * @param   integer $cid            The Shipment ID
     * @param   integer $sid            The associated Shipper ID
     * @param   double  $cost           The cost of delivery
     * @param   double  $price_free     The minimum order value to get a free delivery
     * @param   integer $max_weight     The maximum weight of the delivery
     * @return  boolean                 True on success, false otherwise
     * @static
     */
    static function updateShipment($cid, $sid, $cost, $price_free, $max_weight)
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            UPDATE `".DBPREFIX."module_shop".MODULE_INDEX."_shipment_cost`
               SET `shipper_id`=$sid,
                   `cost`=$cost,
                   `price_free`=$price_free,
                   `max_weight`=$max_weight
             WHERE `id`=$cid
        ");
        return ($objResult ? true : false);
    }


    /**
     * Update the Shipper
     *
     * Note that the name cannot be changed in the settings.
     * Create a new shipper, change the association from the old shipper
     * to the new one, and delete the old.
     * @param   integer $svalue     The ID of the Shipper
     * @param   boolean $isActive   Marking the Shipper as active -- or not
     * @return  boolean             True on success, false otherwise
     * @static
     */
    static function updateShipper($sid, $isActive)
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            UPDATE `".DBPREFIX."module_shop".MODULE_INDEX."_shipper`
               SET `status`=$isActive
             WHERE `id`=$sid
        ");
        return ($objResult ? true : false);
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
        $cost = 0;
        // Found flag is set to the index of a suitable shipment, if encountered below.
        // If the flag stays at -1, there is no way to deliver it!
        $found = -1;
        // Try all the available shipments
        // (see Shipment.class.php::getJSArrays())
        foreach ($arrShipment as $cid => $conditions) {
            $price_free = $conditions['price_free'];
            $max_weight = Weight::getWeight($conditions['max_weight']);
            // Get the shipment conditions that are closest to our order:
            // We have to make sure the maximum weight is big enough for the order,
            // or that it's unspecified (don't care)
            if (($max_weight > 0 && $weight <= $max_weight) || $max_weight == 0) {
                // If price_free is set, the order amount has to be higher than that
                // in order to get the shipping for free.
                if ($price_free > 0 && $price >= $price_free) {
                    // We're well within the weight limit, and the order is also expensive
                    // enough to get a free shipping.
                    $cost = '0.00';
                } else {
                    // Either the order amount is too low, or price_free is unset, or zero,
                    // so the shipping has to be paid for in any case.
                    $cost = $conditions['cost'];
                }
                // We found a kind of shipment that can handle the order, but maybe
                // it's too expensive. - keep the cheapest way to deliver it
                if ($cost < $lowest_cost) {
                    // Found a cheaper one. keep the index.
                    $found = $cid;
                    $lowest_cost = $cost;
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
        // and the conditions look like: array(max_weight, cost_free, cost)

        // Return this
        $arrResult = array();
        foreach (self::$arrShippers as $sid => $shipper) {
            // get countries covered by this shipper
            $query ="
                SELECT DISTINCT `c`.`countries_name`
                  FROM `".DBPREFIX."module_shop".MODULE_INDEX."_countries` AS `c`
                 INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries` AS `rc`
                    ON `rc`.`country_id`=`c`.`id`
                 INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_zones` AS `z`
                    ON `z`.`id`=`rc`.`zone_id`
                 INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_rel_shipment` AS `rs`
                    ON `rs`.`zone_id`=`z`.`id`
                 INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_shipper` AS `s`
                    ON `s`.`id`=`rs`.`shipment_id`
                 WHERE `s`.`shipment_id`=$sid
                   AND `z`.`status`=1
                   AND `s`.`status`=1
                 ORDER BY `countries_name` ASC
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
            $arrCountries = array();
            while (!$objResult->EOF) {
                $arrCountries[] = $objResult->fields['countries_name'];
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
                    'price_free' => ($arrCond['price_free'] > 0
                        ? $arrCond['price_free']
                        : '-'
                    ),
                    'cost'       => ($arrCond['cost'] > 0
                        ? $arrCond['cost']
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

}

?>
