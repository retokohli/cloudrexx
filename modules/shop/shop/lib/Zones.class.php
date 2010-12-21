<?php

class Zones
{
    private static $arrZone = false;
    private static $arrRelation = false;

    function init()
    {
        global $objDatabase;

//        $arrSqlName = Text::getSqlSnippets(
//            '`zone`.`text_name_id`', FRONTEND_LANG_ID,
//            MODULE_ID, TEXT_SHOP_ZONES_NAME
//        );
        $query = "
            SELECT `zone`.`zones_id`, `zone`.`activation_status`, `zone`.`zones_name`".
        //$arrSqlName['field']."
            "
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_zones` AS `zone`".
        //$arrSqlName['join']."
            "
             ORDER BY `zone`.`zones_name` ASC";
        //".$arrSqlName['text']."
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        self::$arrZone = array();
        while (!$objResult->EOF) {
            $id = $objResult->fields['zones_id']; // id
//            $text_name_id = $objResult->fields[$arrSqlName['name']];
//            $strName = $objResult->fields[$arrSqlName['text']];
//            if ($strName === null) {
//                $objText = Text::getById($text_name_id, 0);
//                if ($objText) $strName = $objText->getText();
//            }
            self::$arrZone[$id] = array(
                'id' => $id,
                'name' => $objResult->fields['zones_name'], // REPLACE:
//                'text_name_id' => $text_name_id,
//                'name' => $strName,
                'status' => $objResult->fields['activation_status'], // status
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
        if (empty(self::$arrZone)) self::init();
        return self::$arrZone;
    }


    static function getCountryRelationArray()
    {
        global $objDatabase;

        if (empty(self::$arrRelation)) {
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
        self::$arrZone = false;
        return $total_result;
    }


    /**
     * Delete Zone
     */
    function deleteZone()
    {
        global $objDatabase;

        if (empty($_GET['zonesId'])) return '';
        if (empty(self::$arrZone)) self::init();
        $zone_id = $_GET['zonesId'];
        if (empty(self::$arrZone[$zone_id])) return '';
        // Delete zone with Text
// 3.0
//        $text_id = self::$arrZone[$zone_id]['text_name_id'];
//        if (!Text::deleteById($text_id)) return false;
//        $objResult = $objDatabase->Execute("
//            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_zones
//             WHERE id=$zone_id");
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_zones
             WHERE zones_id=$zone_id"); // 3.0: zone_id
        if (!$objResult) return false;
        // Delete country relations
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries
             WHERE zones_id=$zone_id"); // 3.0: zone_id
        if (!$objResult) return false;
        // Update relations:  Apply zone "All" to those still associated
        // with the deleted one
        $objResult = $objDatabase->Execute("
            UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment
               SET zones_id=1
             WHERE zones_id=$zone_id"); // 3.0: zone_id
        if (!$objResult) return false;
        $objResult = $objDatabase->Execute("
            UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_shipment
               SET zones_id=1
             WHERE zones_id=$zone_id"); // 3.0: zone_id
        if (!$objResult) return false;
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_zones");
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries");
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment");
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_shipment");
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
// Note: Text::replace() now returns the ID, not the object!
//        $objText = Text::replace(
//            0, FRONTEND_LANG_ID, $strName,
//            MODULE_ID, TEXT_SHOP_ZONES_NAME
//        );
//        if (!$objText) return false;
//        $objResult = $objDatabase->Execute("
//            INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_zones (
//                text_name_id, status
//            ) VALUES (
//                ".$objText->getId().",
//                ".(isset($_POST['zone_active_new']) ? 1 : 0)."
//            )");
        $objResult = $objDatabase->Execute("
            INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_zones (
                zones_name, activation_status
            ) VALUES (
                '".addslashes($strName)."',
                ".(empty($_POST['zone_active_new']) ? 0 : 1)."
            )");
        if (!$objResult) return false;
        $zone_id = $objDatabase->Insert_ID();
        if (isset($_POST['selected_countries'])) {
            foreach ($_POST['selected_countries'] as $country_id) {
//                $objResult = $objDatabase->Execute("
//                    INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries (
//                        zone_id, country_id
//                    ) VALUES (
//                        $zone_id, $country_id
//                    )");
                $objResult = $objDatabase->Execute("
                    INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries (
                        zones_id, countries_id
                    ) VALUES (
                        $zone_id, $country_id
                    )");
                if (!$objResult) return false;
            }
            $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries");
        }
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_zones");
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
        if (empty(self::$arrZone)) self::init();
        foreach ($_POST['zone_list'] as $zone_id) {
            $strName = $_POST['zone_name'][$zone_id];
// Note: Text::replace() now returns the ID, not the object!
//            $text_name_id = self::$arrZone[$zone_id]['text_name_id'];
//            $objText = Text::replace(
//                $text_name_id, FRONTEND_LANG_ID, $strName,
//                MODULE_ID, TEXT_SHOP_ZONES_NAME
//            );
//            if (!$objText) return false;
//            $objResult = $objDatabase->Execute("
//                UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_zones
//                   SET text_name_id=".$objText->getId().",
//                       status=".(isset($_POST['zone_active'][$zone_id]) ? 1 : 0)."
//                 WHERE id=$zone_id");
            $objResult = $objDatabase->Execute("
                UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_zones
                   SET zones_name='".addslashes($strName)."',
                       activation_status=".(empty($_POST['zone_active'][$zone_id]) ? 0 : 1)."
                 WHERE zones_id=$zone_id");
            if (!$objResult) return false;

//            $objResult = $objDatabase->Execute("
//                DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries
//                 WHERE zone_id=$zone_id");
            $objResult = $objDatabase->Execute("
                DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries
                 WHERE zones_id=$zone_id");
            if (!$objResult) return false;
            if (!empty($_POST['selected_countries'][$zone_id])) {
                foreach ($_POST['selected_countries'][$zone_id] as $country_id) {
//                    $objResult = $objDatabase->Execute("
//                        INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries (
//                            zone_id, country_id
//                        ) VALUES (
//                            $zone_id, $country_id
//                        )");
                    $objResult = $objDatabase->Execute("
                        INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries (
                            zones_id, countries_id
                        ) VALUES (
                            $zone_id, $country_id
                        )");
                    if (!$objResult) return false;
                }
            }
        }
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_zones");
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries");
        return true;
    }


    function getMenu($selectedId='', $menuName='zone_id', $onchange='')
    {
        if (empty(self::$arrZone)) self::init();
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

}

?>
