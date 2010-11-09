<?php

class Zones
{
    const TEXT_NAME = 'shop_zone_name';

    private static $arrZone = null;
    private static $arrRelation = null;


    function init()
    {
        global $objDatabase;

        $arrSqlName = Text::getSqlSnippets(
            '`zone`.`text_name_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_NAME);
        $query = "
            SELECT `zone`.`id`, `zone`.`active`".
                   $arrSqlName['field']."
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_zones` AS `zone`".
                   $arrSqlName['join']."
             ORDER BY ".$arrSqlName['text']." ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        self::$arrZone = array();
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $text_name_id = $objResult->fields[$arrSqlName['name']];
            $strName = $objResult->fields[$arrSqlName['text']];
            if ($strName === null) {
                $objText = Text::getById($text_name_id, 0);
                if ($objText) $strName = $objText->getText();
            }
            self::$arrZone[$id] = array(
                'id' => $id,
                'text_name_id' => $text_name_id,
                'name' => $strName,
                'active' => $objResult->fields['active'],
            );
            $objResult->MoveNext();
        }
        return true;
    }


    /**
     * Returns an array of the available zones
     * @return  array                           The zones array
     */
    function getZoneArray()
    {
        if (is_null(self::$arrZone)) self::init();
        return self::$arrZone;
    }


    static function getCountryRelationArray()
    {
        global $objDatabase;

        if (is_null(self::$arrRelation)) {
            $query = "
                SELECT zone_id, country_id
                  FROM ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
            $arrRelCountries = array();
            while (!$objResult->EOF) {
                $zonesId   = $objResult->fields['zone_id'];
                $countryId = $objResult->fields['country_id'];
                if (empty($arrRelCountries[$zonesId])) {
                    $arrRelCountries[$zonesId] = array();
                }
                $arrRelCountries[$zonesId][] = $countryId;
                $objResult->MoveNext();
            }
        }
        return self::$arrRelation;
    }



    /**
     * Returns the Zone ID associated with the given Payment ID, if any
     *
     * If the Payment isn't associated with any Zone, returns null.
     * @param   integer     $payment_id     The Payment ID
     * @return  integer                     The Zone ID, if any, or null
     */
    static function getZoneIdByPaymentId($payment_id)
    {
    	global $objDatabase;

        $query = "
            SELECT r.zone_id
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment AS r
              JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_zones AS z
                ON z.id=r.zone_id
             WHERE z.active=1
               AND r.payment_id=".$arrPayment['id'];
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return null;
        if ($objResult->EOF) return null;
        return $objResult->fields['zone_id'];
    }


    static function store()
    {
        $total_result = true;
        $result = self::deleteZone();
        if ($result !== '') $total_result &= $result;
        $result = self::addZone();
        if ($result !== '') $total_result &= $result;
        $result = self::updateZones();
        if ($result !== '') $total_result &= $result;
        // Reinit after storing, or the user won't see any changes at first
        self::$arrZone = null;
        return $total_result;
    }


    /**
     * Delete Zone
     */
    function deleteZone()
    {
        global $objDatabase;

        if (empty($_GET['zonesId'])) return '';
        $zone_id = $_GET['zonesId'];
        if (is_null(self::$arrZone)) self::init();
        if (empty(self::$arrZone[$zone_id])) return '';
        // Delete country relations
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries
             WHERE zone_id=$zone_id");
        if (!$objResult) return false;
        // Delete zone with Text
        $text_id = self::$arrZone[$zone_id]['text_name_id'];
        if (!Text::deleteById($text_id)) return false;
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_zones
             WHERE id=$zone_id");
        if (!$objResult) return false;
        // Update relations:  Apply zone "All" to those still associated
        // with the deleted one
        $objResult = $objDatabase->Execute("
            UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment
               SET zone_id=1
             WHERE zone_id=$zone_id");
        if (!$objResult) return false;
        $objResult = $objDatabase->Execute("
            UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_shipment
               SET zone_id=1
             WHERE zone_id=$zone_id");
        if (!$objResult) return false;
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_zones");
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries");
        return true;
    }


    /**
     * Add a new zone
     */
    function addZone()
    {
        global $objDatabase;

        if (empty($_POST['zone_name_new'])) return '';
        $strName = $_POST['zone_name_new'];
        $objText = Text::replace(
            null, FRONTEND_LANG_ID, $strName, MODULE_ID, self::TEXT_NAME);
        if (!$objText) return false;
        $objResult = $objDatabase->Execute("
            INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_zones (
                text_name_id, active
            ) VALUES (
                ".$objText->getId().",
                ".(isset($_POST['zone_active_new']) ? 1 : 0)."
            )");
        if (!$objResult) return false;
        $zone_id = $objDatabase->Insert_ID();
        if (isset($_POST['selected_countries'])) {
            foreach ($_POST['selected_countries'] as $country_id) {
                $objResult = $objDatabase->Execute("
                    INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries (
                        zone_id, country_id
                    ) VALUES (
                        $zone_id, $country_id
                    )");
                if (!$objResult) return false;
            }
        }
        return true;
    }


    /**
     * Update zones
     */
    function updateZones()
    {
        global $objDatabase;

        if (   empty($_POST['zones'])
            || empty($_POST['zone_list'])) return '';
        if (is_null(self::$arrZone)) self::init();
        foreach ($_POST['zone_list'] as $zone_id) {
            $strName = $_POST['zone_name'][$zone_id];
            $text_name_id = self::$arrZone[$zone_id]['text_name_id'];
            $objText = Text::replace(
                $text_name_id, FRONTEND_LANG_ID, $strName,
                MODULE_ID, self::TEXT_NAME);
            if (!$objText) return false;
            $objResult = $objDatabase->Execute("
                UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_zones
                   SET text_name_id=$text_name_id,
                       active=".(empty($_POST['zone_active'][$zone_id]) ? 0 : 1)."
                 WHERE id=$zone_id");
            if (!$objResult) return false;
            $objResult = $objDatabase->Execute("
                DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries
                 WHERE zone_id=$zone_id");
            if (!$objResult) return false;
            if (!empty($_POST['selected_countries'][$zone_id])) {
                foreach ($_POST['selected_countries'][$zone_id] as $country_id) {
                    $objResult = $objDatabase->Execute("
                        INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries (
                            zone_id, country_id
                        ) VALUES (
                            $zone_id, $country_id
                        )");
                    if (!$objResult) return false;
                }
            }
        }
        return true;
    }


    /**
     * Adds a relation entry for the given Shipper and Zone ID
     * @param   integer   $zone_id      The Zone ID
     * @param   integer   $shipper_id   The Shipper ID
     * @return  boolean                 True on success, false otherwise
     */
    static function storeShipmentRelation($zone_id, $shipper_id)
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            REPLACE INTO ".DBPREFIX."module_shop".MODULE_INDEX."_rel_shipment (
                zone_id, shipper_id
            ) VALUES (
                $zone_id, $shipper_id
            )");
        return (boolean)$objResult;
    }


    function getMenu($selectedId='', $menuName='zone_id', $onchange='')
    {
        if (is_null(self::$arrZone)) self::init();
        $menu = '';
        foreach (self::$arrZone as $zone_id => $arrZone) {
            $menu .=
                '<option value="'.$zone_id.'"'.
                ($selectedId == $zone_id ? ' selected="selected"' : '').
                '>'.$arrZone['name'].'</option>'."\n";
        }
        // Add select tag and hidden input if the menu name is non-empty
        if ($menuName)
            $menu =
                '<input type="hidden" name="old_'.$menuName.'" '.
                'value="'.$selectedId.'" />'."\n".
                '<select name="'.$menuName.'"'.
                (empty($onchange) ? '' : ' onchange="'.$onchange.'"').">\n".
                $menu.
                "</select>\n";
        return $menu;
    }


    static function errorHandler()
    {
        require_once(ASCMS_CORE_PATH.'/DbTool.class.php');

DBG::activate(DBG_DB_FIREPHP);

        // Fix the Zone-Payment relation table
        $table_name = DBPREFIX.'module_shop'.MODULE_INDEX.'_rel_payment';
        $table_structure = array(
            'zone_id' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0', 'primary' => true, 'renamefrom' => 'zones_id'),
            'payment_id' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0', 'primary' => true),
        );
        $table_index =  array();
        if (!DbTool::table($table_name, $table_structure, $table_index)) {
die("Zones::errorHandler(): Error: failed to migrate Zones-Payment relation table, code hserz362zhrh");
        }

        // Fix the Text table
        Text::errorHandler();

        $table_name = DBPREFIX.'module_shop'.MODULE_INDEX.'_zones';
        $table_structure = array(
            'id' => array('type' => 'INT(10)', 'unsigned' => true, 'auto_increment' => true, 'primary' => true, 'renamefrom' => 'zones_id'),
            'text_name_id' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0', 'renamefrom' => 'zones_name'),
            'active' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'default' => '1', 'renamefrom' => 'activation_status'),
        );
        $table_index =  array();

        if (DbTool::table_exists($table_name)) {
            if (DbTool::column_exists($table_name, 'zones_name')) {
                // Migrate all Zone names to the Text table first
                Text::deleteByKey(self::TEXT_NAME);
                $objResult = DbTool::sql("
                    SELECT `zones_id`, `zones_name`
                      FROM `$table_name");
                if (!$objResult) {
die("Zones::errorHandler(): Error: failed to query names, code herh45uhaa4");
                }
                while (!$objResult->EOF) {
                    $id = $objResult->fields['zones_id'];
                    $name = $objResult->fields['zones_name'];
                    $text_name_id = Text::replace(
                        null, FRONTEND_LANG_ID,
                        $name, MODULE_ID, self::TEXT_NAME);
                    if (!$text_name_id) {
die("Zones::errorHandler(): Error: failed to migrate name '$name', code dsn42w4zdffh");
                    }
                    $objResult2 = DbTool::sql("
                        UPDATE `$table_name`
                           SET `zones_name`='$text_name_id'
                         WHERE `zones_id`=$id");
                    if (!$objResult2) {
die("Zones::errorHandler(): Error: failed to update Zone ID $id, code sj5ie53ujjg");
                    }
DBG::log("Migrated Zone ID $id, name $name ($text_name_id)");
                    $objResult->MoveNext();
                }
            }
        }
        if (!DbTool::table($table_name, $table_structure, $table_index)) {
die("Zones::errorHandler(): Error: failed to migrate Zones table, code js5riksr58j");
        }

        $table_name = DBPREFIX.'module_shop_rel_shipment';
        $table_structure = array(
            'shipper_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'renamefrom' => 'shipment_id'),
            'zone_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'renamefrom' => 'zones_id'),
        );
        $table_index = array();
        if (!DbTool::table($table_name, $table_structure, $table_index)) {
die("Shipment::errorHandler(): Error: failed to migrate Shipper relation table, code sdjszh34zhese");
        }

        $table_name = DBPREFIX.'module_shop_rel_countries';
        $table_structure = array(
            'country_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'renamefrom' => 'countries_id'),
            'zone_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'renamefrom' => 'zones_id'),
        );
        $table_index = array();
        if (!DbTool::table($table_name, $table_structure, $table_index)) {
die("Shipment::errorHandler(): Error: failed to migrate Shipper relation table, code sje485ts4");
        }


        // Always
        return false;
    }

}

?>
