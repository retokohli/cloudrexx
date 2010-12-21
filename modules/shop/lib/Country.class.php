<?php

/**
 * Shop Country class
 * @version     2.1.0
 * @since       2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 * @todo        To be unified with the core Country class
 */

/**
 * Country helper methods
 * @version     2.1.0
 * @since       2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 * @todo        To be unified with the core Country class
 */
class Country
{
    /**
     * Array of all countries
     * @var     array
     * @access  private
     * @see     initCountries()
     */
    private static $arrCountries = false;

    /**
     * Array of all country-zone relations
     * @var     array
     * @access  private
     * @see     initCountryRelations()
     */
    private static $arrCountryRelations = false;


    /**
     * Initialise the static array with all countries from the database
     *
     * Note that the Countries are always shown in the selected
     * frontend language.
     * @global  ADONewConnection  $objDatabase
     * @return  boolean                     True on success, false otherwise
     */
    function initCountries()
    {
        global $objDatabase;

// post-2.1
//        $arrSqlName = Text::getSqlSnippets(
//            '`country`.`text_name_id`', FRONTEND_LANG_ID,
//            MODULE_ID, TEXT_SHOP_COUNTRY_NAME
//        );
//        $query = "
//            SELECT `country`.`id`, `country`.`status`,
//                   `country`.`iso_code_2`, `country`.`iso_code_3`".
//                   $arrSqlName['field']."
//              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_countries AS `country`".
//                   $arrSqlName['join']."
//             ORDER BY `country`.`id` ASC
//        ";
        $query = "
            SELECT `country`.`countries_id`, `country`.`activation_status`,
                   `country`.`countries_iso_code_2`, `country`.`countries_iso_code_3`,
                   `country`.`countries_name`".
//                   $arrSqlName['field']."
            "
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_countries AS `country`".
//                   $arrSqlName['join']."
            "
             ORDER BY `country`.`countries_name` ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        while (!$objResult->EOF) {
            $id = $objResult->fields['countries_id'];
//            $id = $objResult->fields['id'];
//            $text_name_id = $objResult->fields[$arrSqlName['name']];
//            $strName = $objResult->fields[$arrSqlName['text']];
//            if ($strName === null) {
//                $objText = Text::getById($text_name_id, 0);
//                if ($objText) $strName = $objText->getText();
//            }
            self::$arrCountries[$id] = array(
                'id' => $id,
                'name' => $objResult->fields['countries_name'], //$strName,
//                'text_name_id' => $text_name_id,
                'iso_code_2' => $objResult->fields['countries_iso_code_2'],
                'iso_code_3' => $objResult->fields['countries_iso_code_3'],
                'status' => $objResult->fields['activation_status']
            );
            $objResult->MoveNext();
        }
        return true;
    }


    /**
     * Initialise the static array with all country relations from the database
     * @global  ADONewConnection  $objDatabase
     * @return  boolean                 True on success, false otherwise
     */
    function initCountryRelations()
    {
        global $objDatabase;

        $query = "
            SELECT zone_id, country_id
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries
             ORDER BY id ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        while (!$objResult->EOF) {
            self::$arrCountryRelations[] = array(
                'zone_id'    => $objResult->fields['zone_id'],
                'country_id' => $objResult->fields['country_id']);
            $objResult->MoveNext();
        }
        return true;
    }


    /**
     * Returns array of all countries
     * @return  array               The country array
     * @static
     */
    static function getArray()
    {
        if (empty(self::$arrCountries)) self::initCountries();
        return self::$arrCountries;
    }


    /**
     * Returns the name of the country selected by its ID
     *
     * If a country with the given ID does not exist, returns the empty string.
     * @param   integer   $country_id     The country ID
     * @return  string                    The country name, or the empty string
     * @static
     */
    static function getNameById($country_id)
    {
        if (empty($country_id)) return '';
        if (empty(self::$arrCountries)) self::initCountries();
        return self::$arrCountries[$country_id]['name'];
    }


    /**
     * Returns the HTML dropdown menu code for the active countries.
     * @param   string  $menuName   Optional name of the menu,
     *                              defaults to "countryId"
     * @param   string  $selectedId Optional preselected country ID
     * @param   string  $onchange   Optional onchange callback function
     * @return  string              The HTML dropdown menu code
     * @static
     */
    static function getMenu($menuName='countryId', $selectedId='', $onchange='')
    {
        $strMenu =
            '<select name="'.$menuName.'" '.
            ($onchange ? ' onchange="'.$onchange.'"' : '').">\n".
            self::getCountryMenuoptions($selectedId).
            "</select>\n";
        return $strMenu;
    }


    /**
     * Returns the HTML code for the countries dropdown menu options
     * @param   string  $selectedId   Optional preselected country ID
     * @param   boolean $flagActiveonly   If true, only active countries
     *                                are added to the options, all otherwise.
     * @return  string                The HTML dropdown menu options code
     * @static
     */
    static function getMenuoptions($selected_id=0, $flagActiveonly=true)
    {
        static $strMenuoptions = '';
        static $last_selected_id = 0;

        if (empty(self::$arrCountries)) self::initCountries();
        if ($strMenuoptions && $last_selected_id == $selected_id)
            return $strMenuoptions;
        if (empty(self::$arrCountries)) self::initCountries();
        foreach (self::$arrCountries as $id => $arrCountry) {
            if (   $flagActiveonly
                && empty($arrCountry['status'])) continue;
            $strMenuoptions .=
                '<option value="'.$id.'"'.
                ($selected_id == $id ? ' selected="selected"' : '').'>'.
                $arrCountry['name']."</option>\n";
        }
        $last_selected_id = $selected_id;
        return $strMenuoptions;
    }


    /**
     * Returns an array of two arrays; one with countries in the given zone,
     * the other with the remaining countries.
     *
     * The array looks like this:
     *  array(
     *    'in' => array(    // Countries in the zone
     *      country ID => array(
     *        'id' => country ID,
     *        'name' => country name,
     *        'text_name_id' => country name Text ID,
     *      ),
     *      ... more ...
     *    ),
     *    'out' => array(   // Countries not in the zone
     *      country ID => array(
     *        'id' => country ID,
     *        'name' => country name,
     *        'text_name_id' => country name Text ID,
     *      ),
     *      ... more ...
     *    ),
     *  );
     * @param   integer     $zone_id        The zone ID
     * @return  array                       Countries array, as described above
     */
    static function getArraysByZoneId($zone_id)
    {
        global $objDatabase;

        if (empty(self::$arrCountries)) self::initCountries();

        // Query relations between zones and countries:
        // Get all country IDs and names
        // associated with that zone ID
//        $arrSqlName = Text::getSqlSnippets(
//            '`country`.`text_name_id`', FRONTEND_LANG_ID,
//            MODULE_ID, TEXT_SHOP_COUNTRY_NAME
//        );
// TEST!
//        $query = "
//            SELECT `country`.`id`, `relation`.`country_id`".
//                   $arrSqlName['field']."
//              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_countries` AS `country`".
//                   $arrSqlName['join']."
//              LEFT JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries` AS `relation`
//                ON `country`.`id`=`relation`.`country_id`
//             WHERE `country`.`status`=1
//               AND `relation`.`zone_id`=$zone_id
//             ORDER BY ".$arrSqlName['text']." ASC";
        $query = "
            SELECT `relation`.`countries_id`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries` AS `relation`
              JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_countries` AS `country`
                ON `country`.`countries_id`=`relation`.`countries_id`
             WHERE `relation`.`zones_id`=$zone_id
             ORDER BY `country`.`countries_name`";
//             WHERE `country`.`activation_status`=1
//             ORDER BY ".//$arrSqlName['text']."`country`.`countries_name` ASC
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        // Initialize the array to avoid notices when one or the other is empty
        $arrZoneCountries = array('in' => array(), 'out' => array());
        while (!$objResult->EOF) {
            $id = $objResult->fields['countries_id'];
            $objResult->MoveNext();
            // Country may only be in the Zone if it exists and is active
            if (   empty(self::$arrCountries[$id])
                || empty(self::$arrCountries[$id]['status']))
                continue;
            $arrZoneCountries['in'][$id] = array(
                'id' => $id,
                'name' => self::$arrCountries[$id]['name'],
// Probably not needed:
//                'text_name_id' => $text_name_id,
            );
        }
        foreach (self::$arrCountries as $id => $arrCountry) {
            // Country may only be available for the Zone if it is active
            if (empty($arrZoneCountries['in'][$id])
                && $arrCountry['status'])
                $arrZoneCountries['out'][$id] = array(
                    'id' => $id,
                    'name' => $arrCountry['name'],
                );

        }
        return $arrZoneCountries;
    }

}
