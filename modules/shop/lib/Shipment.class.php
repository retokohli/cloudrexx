<?PHP
/**
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Useful methods to handle shipping related stuff
 *
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @package     contrexx
 * @subpackage  module_shop
 */

class Shipment {
    /**
     * Array of all available shipment methods
     * -- UNUSED
     * @var array
     * @access public
     * @ignore
     */
    //var $arrAllShipmentMethods = array();


    /**
     * Array of active shippers and shipment conditions
     * @var     array
     * @access  private
     */
    var $arrShippers  = array();
    var $arrShipments = array();


    /**
     * Construct a Shipment object
     *
     * Initialize the shipping options from the shipper (s) and shipment_cost (c)
     * tables. For each shipper, create array entries like:
     * arrShippers[s.id]        = array (
     *      name       => s.name,
     *      status     => s.status
     * )
     * arrShipments[s.id][c.id] = array (
     *      max_weight => c.max_weight,
     *      price_free => c.price_free,
     *      cost       => c.cost
     * )
     *
     * Note that the table module_shop_shipment has been replaced by
     * module_shop_shipper (id, name, status) and
     * module_shop_shipment_cost (id, shipper_id, max_weight, cost, price_free)
     * as of version 1.1.
     *
     * @global  mixed           $objDatabase    Database object
     * @param   boolean         $ignoreStatus   If false, only records with status==1 are
     *                                          returned, all records otherwise.
     *                                          Use $ignoreStatus=1 for the settings (backend).
     * @return  void
     * @since   v1.1
     */
    function Shipment($ignoreStatus=1)
    {
        global $objDatabase;
        // get the shippers first
        $objResult = $objDatabase->Execute(
            "SELECT id, name, status ".
            "FROM ".DBPREFIX."module_shop_shipper ".
            ($ignoreStatus ? '' : 'WHERE status=1 ').
            "ORDER BY id ASC");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $sid = $objResult->fields['id'];
                $this->arrShippers[$sid] = array(
                    'name'     => $objResult->fields['name'],
                    'status'   => $objResult->fields['status'],
                );
                $objResult->MoveNext();
            }

            // now get the associated shipment conditions from shipment_cost
            $objResult = $objDatabase->Execute(
                "SELECT c.id, c.shipper_id, c.max_weight, c.cost, c.price_free ".
                "FROM ".DBPREFIX."module_shop_shipment_cost c ".
                "INNER JOIN ".DBPREFIX."module_shop_shipper s ".
                "ON s.id=shipper_id ".
                ($ignoreStatus ? '' : 'WHERE status=1'));
            if ($objResult !== false) {
                while (!$objResult->EOF) {
                    $sid = $objResult->fields['shipper_id'];
                    $cid = $objResult->fields['id'];
                    $this->arrShipments[$sid][$cid] =
                        array(
                            'max_weight' => Weight::getWeightString($objResult->fields['max_weight']),
                            'price_free' => $objResult->fields['price_free'],
                            'cost'       => $objResult->fields['cost'],
                        );
                    $objResult->MoveNext();
                } // end while
            }
        }
    }


    /**
     * Returns the name of the shipper with the given ID
     *
     * @param   integer $shipperId  The shippers' ID
     * @return  string              The shippers' name
     */
    function getShipperName($shipperId)
    {
        return $this->arrShippers[$shipperId]['name'];
    }


    /**
     * Access method.  Returns the arrShippers array.
     * See {@link Shipment()}.
     */
    function getShippersArray()
    {
        return $this->arrShippers;
    }

    /**
     * Access method.  Returns the arrShipments array.
     * See {@link Shipment()}.
     */
    function getShipmentsArray()
    {
        return $this->arrShipments;
    }


    /**
     * Returns the shipment arrays (shippers and shipment costs) in JavaScript
     * syntax.
     *
     * @param   mixed   $objCurrency    Currency object, see {@link Currency.class.php}
     */
    function getJSArrays($objCurrency)
    {
        // set shippers and shipment cost javascript arrays
        // Shippers are not used for calculating the shipment costs
        //$strJsArrays = "arrShippers = new Array();\narrShipments = new Array();\n";
        $strJsArrays = "arrShipments = new Array();\n";
        // insert shippers by id
        foreach ($this->arrShippers as $sid => $arrShipper) {
            //$strJsArrays .= "arrShippers[$sid] = new Array('".
                //$this->arrShippers[$sid]['name']."', ".$this->arrShippers[$sid]['status'].");\n";
            // insert shipments by shipper id
            $strJsArrays .= "arrShipments[$sid] = new Array();\n";
            $i = 0;
            foreach ($this->arrShipments[$sid] as $cid => $arrShipment) {
                $strJsArrays .=
                    "arrShipments[$sid][".$i++."] = new Array('$cid', '".
                    $arrShipment['max_weight']."', '".   // string
                    $objCurrency->getCurrencyPrice($arrShipment['price_free'])."', '".
                    $objCurrency->getCurrencyPrice($arrShipment['cost'])."');\n";
            }
        }
        return $strJsArrays;
    }


    /**
     * Returns an array of shipper ids relevant for the country specified by
     * the argument $countryId.
     *
     * @internal Note that s.shipment_id below now associates with shipper.id (TODO)
     * @param   string  $countryId      Country ID
     * @return  array                   Array of shipment IDs
     * @todo    Rename shipment_id to shipper_id in all affected relations
     */
    function getCountriesRelatedShippingIdArray($countryId)
    {
        global $objDatabase;

        $arrShipperId = array();
        // mind that s.shipper_id actually points to a shipper, not a shipment!
        $query ="SELECT s.shipment_id as shipper_id ".
                         "FROM ".DBPREFIX."module_shop_rel_countries AS c, ".
                                 DBPREFIX."module_shop_zones AS z, ".
                                 DBPREFIX."module_shop_rel_shipment AS s ".
                        "WHERE c.countries_id=".intval($countryId).
                          " AND z.activation_status=1 ".
                          "AND z.zones_id=c.zones_id ".
                          "AND z.zones_id=s.zones_id";
        $objResult = $objDatabase->Execute($query);
        while ($objResult && !$objResult->EOF) {
            $shipper = $objResult->fields['shipper_id'];
            if(isset($this->arrShippers[$shipper])){
                $arrShipperId[]=$shipper;
            }
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
     * @global  array   $_ARRAYLANG     Language array
     */
    function getShipperMenu($countryId=0, $selectedId=0, $onchange="")
    {
        global $_ARRAYLANG;

        $menu = (intval($selectedId) == 0
            ? "<option value='0' selected='selected'>".$_ARRAYLANG['TXT_SHOP_PLEASE_SELECT']."</option>\n"
            : ''
        );
        $arrId = $this->getCountriesRelatedShippingIdArray($countryId);
        $haveShipper = false;
        foreach ($this->arrShippers as $sid => $arrShipper){
            // only show suitable shipments in the menu if the user is on the payment page,
            // check the availability of the shipment in her country,
            // and verify that the shipper will be able to handle the freight.
            if (!($_REQUEST['cmd'] == 'payment') ||
                ((!$countryId || in_array($sid, $arrId)) &&
                $this->calculateShipmentPrice(
                    $sid,
                    $_SESSION['shop']['cart']['total_price'],
                    $_SESSION['shop']['cart']['total_weight']) != -1
            )) {
                $selected = ($sid==intval($selectedId) ? 'selected="selected"' : '');
                $menu .= '<option value="'.$sid.'" '.$selected.'>'.$this->arrShippers[$sid]['name']."</option>\n";
                $haveShipper = true;
            }
        }
        if (!$haveShipper) {
            return $_ARRAYLANG['SHOP_SHIPMENT_TOO_HEAVY'];
        }
        if ($onchange) {
            $menu = "\n<select name=\"shipperId\" onchange=\"$onchange\">\n$menu\n</select>\n";
        }
        return $menu;
    }


    /**
     * Delete a Shipper from the database
     *
     * @param   integer     $sid    The Shipper ID
     * @return  boolean             True on success, false otherwise.
     */
    function deleteShipper($sid)
    {
        global $objDatabase;
        $objResult = $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_shop_shipper WHERE id=".$sid);
        if ($objResult) {
            $objResult = $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_shop_shipment_cost WHERE shipper_id=".$sid);
            if ($objResult) {
                $objResult = $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_shop_rel_shipment WHERE shipment_id=".$sid);
                if ($objResult) {
                    $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop_shipper");
                    $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop_shipment_cost");
                    $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop_rel_shipment");
                    return true;
                } else {
                }
            } else {
            }
        } else {
        }
        return false;
    }


    /**
     * Delete a Shipment entry from the database
     *
     * @param   integer     $cid    The Shipment ID
     */
    function deleteShipment($cid)
    {
        global $objDatabase;
        $objResult = $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_shop_shipment_cost WHERE id=$cid");
        return $objResult;
    }


    /**
     * Add a Shipper to the database
     *
     * @param   string  $name       The Shipper name
     * @param   boolean $isActive   Marking the Shipper as active -- or not
     * @param   integer $zone       The zone the Shipper is in
     * @return  boolean             The result of DB->Execute()
     */
    function addShipper($name, $isActive, $zone)
    {
        global $objDatabase;
        $objResult = $objDatabase->Execute(
            "INSERT INTO ".DBPREFIX."module_shop_shipper (name, status) ".
            "VALUES ('".addslashes($name)."', $isActive)"
        );
        return $objResult;
    }


    /**
     * Add a Shipment entry to the database
     *
     * @param   integer $sid            The associated Shipper ID
     * @param   double  $cost           The cost of delivery
     * @param   double  $price_free     The minimum order value to get a free delivery
     * @param   integer $max_weight     The maximum weight of the delivery
     */
    function addShipment($sid, $cost, $price_free, $max_weight)
    {
        global $objDatabase;
        $objResult = $objDatabase->Execute(
            "INSERT INTO ".DBPREFIX."module_shop_shipment_cost (shipper_id, cost, price_free, max_weight) ".
            "VALUES ($sid, $cost, $price_free, $max_weight)"
        );
        return $objResult;
    }


    /**
     * Update the Shipment entry
     *
     * @param   integer $cid            The Shipment ID
     * @param   integer $sid            The associated Shipper ID
     * @param   double  $cost           The cost of delivery
     * @param   double  $price_free     The minimum order value to get a free delivery
     * @param   integer $max_weight     The maximum weight of the delivery
     */
    function updateShipment($cid, $sid, $cost, $price_free, $max_weight)
    {
        global $objDatabase;
        $objResult = $objDatabase->Execute(
            "UPDATE ".DBPREFIX."module_shop_shipment_cost SET ".
                "shipper_id=$sid, ".
                "cost=$cost, ".
                "price_free=$price_free, ".
                "max_weight=$max_weight ".
            "WHERE id = $cid"
        );
        return $objResult;
    }


    /**
     * Update the Shipper
     *
     * Note that the name cannot be changed in the settings.
     * Create a new shipper, change the association from the old shipper
     * to the new one, and delete the old.
     * @param   integer $svalue     The ID of the Shipper
     * @param   boolean $isActive   Marking the Shipper as active -- or not
     */
    function updateShipper($sid, $isActive)
    {
        global $objDatabase;
        $objResult = $objDatabase->Execute(
            "UPDATE ".DBPREFIX."module_shop_shipper SET status=$isActive WHERE id = $sid"
        );
        return $objResult;
    }


    /**
     * Calculate the shipment price for the given Shipper ID, order price and
     * total weight.
     *
     * Returns the shipment price in default currency, or -1 if there is any kind
     * of problem with the shipment conditions.
     * The weight is converted from string using {@link Weight::getWeight()
     * to make sure that grams are used.
     * Note: You have to convert the returned value to the customers' currency
     * using {@link Currency::getCurrencyPrice()}!
     * @param   integer $shipperId  The Shipper ID
     * @param   double  $price      The total order price
     * @param   integer $weight     The total order weight in grams.
     * @return  double              The cost for shipping in default currency, or -1.
     */
    function calculateShipmentPrice($shipperId, $price, $weight)
    {
        $shipmentPrice = 0;

        // are there conditions available from this shipper?
        // otherwise, don't even try to find one. return
        if (!isset($this->arrShipments[$shipperId])) return -1;
        // check shipments available by this shipper

        $arrShipment = $this->arrShipments[$shipperId];
        // find the best match for the current order weight and shipment cost.
        // arbitrary upper limit - we *SHOULD* be able to find one that's lower!
        // we'll just try to find the cheapest way to handle the delivery.
        $lowest_cost = 1e100;
        // temporary shipment cost
        $cost = 0;
        // found flag is set to the index of a suitable shipment, if encountered below.
        // if the flag stays at -1, there is no way to deliver it!
        $found = -1;
        // try all the available shipments;
        // (see Shipment.class.php::getJSArrays())
        foreach ($arrShipment as $cid => $conditions) {
            $price_free = $conditions['price_free'];
            $max_weight = Weight::getWeight($conditions['max_weight']);
            // get the shipment conditions that are closest to our order:
            // we have to make sure the maximum weight is big enough for the order,
            // or that it's unspecified (don't care)
            if (($max_weight > 0 && $weight <= $max_weight) || $max_weight == 0) {
                // if price_free is set, the order amount has to be higher than that
                // in order to get the shipping for free.
                if ($price_free > 0 && $price >= $price_free) {
                    // we're well within the weight limit, and the order is also expensive
                    // enough to get a free shipping.
                    $cost = '0.00';
                } else {
                    // either the order amount is too low, or price_free is unset, or zero,
                    // so the shipping has to be paid for in any case.
                    $cost = $conditions['cost'];
                }
                // we found a kind of shipment that can handle the order, but maybe
                // it's too expensive. - keep the cheapest way to deliver it
                if ($cost < $lowest_cost) {
                    // found a cheaper one. keep the index.
                    $found = $cid;
                    $lowest_cost = $cost;
                }
            }
        }
        if ($found > 0) {
            // after checking all the shipments, we found the lowest cost for the
            // given weight and order price. - update the shipping cost
            return $lowest_cost;
        } else {
            // cannot find suitable shipment conditions for the selected shipper.
            return -1;
        }
    }


    /**
     * Returns an array containing all the active shipment conditions.
     *
     * @global  mixed   $objDatabase    Database object
     * @global  array   $_ARRAYLANG     Language array
     * @return  array           Countries and conditions array
     */
    function getShipmentConditions()
    {
        global $objDatabase;
        global $_ARRAYLANG;

        // get shippers and associated countries (via zones)
        // make an array(shipper_name => array( array(country, ...), array(conditions) )
        // where the countries are listed as strings of their names,
        // and the conditions look like: array(max_weight, cost_free, cost)

        // return this
        $arrResult;
        foreach ($this->arrShippers as $sid => $shipper) {
            // get countries covered by this shipper
            $query ="SELECT DISTINCT c.countries_name FROM ".
                DBPREFIX."module_shop_countries AS c, ".
                DBPREFIX."module_shop_rel_countries AS rc, ".
                DBPREFIX."module_shop_zones AS z, ".
                DBPREFIX."module_shop_rel_shipment AS rs, ".
                DBPREFIX."module_shop_shipper AS s ".
                "WHERE rc.countries_id=c.countries_id ".
                "AND z.zones_id=rc.zones_id ".
                "AND rs.zones_id=z.zones_id ".
                "AND z.activation_status=1 ".
                "AND s.status=1 ".
                "AND s.id=$sid ".
                "ORDER BY countries_name ASC";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
                // store in array
                $arrCountries = array();
                while (!$objResult->EOF) {
                    $arrCountries[] = $objResult->fields['countries_name'];
                    $objResult->MoveNext();
                } // end while
                // now add the conditions, ordered by weight
                $arrConditions = array();
                foreach ($this->arrShipments[$sid] as $cid => $arrCond) {
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
            } else {
            // no countries!?
            } // if objresult
        } // foreach shipper
        return $arrResult;
    }
}

?>
