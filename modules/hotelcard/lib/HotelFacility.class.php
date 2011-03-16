<?php

/**
 * Hotel facility class
 * @version     2.2.0
 * @since       2.2.0
 * @package     contrexx
 * @subpackage  module_hotelcard
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */

/**
 * Multilanguage text
 * @ignore
 */
require_once ASCMS_CORE_PATH.'/Text.class.php';

/**
 * Hotel facility class
 * @version     2.2.0
 * @since       2.2.0
 * @package     contrexx
 * @subpackage  module_hotelcard
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */
class HotelFacility
{
    const TEXT_HOTELCARD_FACILITY = 'hotelcard_facility';
    const TEXT_HOTELCARD_FACILITY_GROUP = 'hotelcard_facility_group';

    /**
     * Array of hotel facilities
     *
     * The array is of the form
     *  array(
     *    facility ID => array(
     *      'id'   => facility ID,
     *      'name' => facility name,
     *      'ord'  => ordinal value,
     *    ),
     *    ... more ...
     *  )
     * The facilities are sorted by their ordinal values.
     * @see     __construct
     * @var     array
     * @access  private
     * @static
     */
    private static $arrFacilities = false;

    /**
     * Array of hotel facility groups
     *
     * The array is of the form
     *  array(
     *    group ID => array(
     *      'id'   => group ID,
     *      'name' => group name,
     *      'ord'  => ordinal value,
     *    ),
     *    ... more ...
     *  )
     * The groups are sorted by their ordinal values.
     * @see     __construct
     * @var     array
     * @access  private
     * @static
     */
    private static $arrGroups = false;

    /**
     * Array of hotel to facility relations
     *
     * The array is of the form
     *  array(
     *    hotel ID => array(
     *      facility ID,
     *      ... more ...
     *    ),
     *    ... more ...
     *  )
     * The facilities are sorted by their ordinal values.
     * Usually, this array will only be initialized by
     * {@see getRelationArray()} once for a single hotel.
     * @var     array
     * @access  private
     * @static
     */
    private static $arrRelations = false;


    /**
     * Initializes the facilities and groups data from the database
     *
     * Note that this does not read relations.  See {@see getRelationArray()}
     * for that.
     * @global  ADONewConnection  $objDatabase
     */
    static function init()
    {
        global $objDatabase;

        // Facilities
        $arrSqlName = Text::getSqlSnippets(
            '`facility`.`name_text_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_HOTELCARD_FACILITY
        );
        $query = "
            SELECT `facility`.`id`,  `facility`.`facility_group_id`,
                   `facility`.`ord` ".$arrSqlName['field']."
              FROM `".DBPREFIX."module_hotelcard_hotel_facility` AS `facility`".
                   $arrSqlName['join']."
             ORDER BY `facility`.`ord` ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        self::$arrFacilities = array();
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $text_id = $objResult->fields[$arrSqlName['id']];
            $strName = $objResult->fields[$arrSqlName['text']];
            if ($strName === null) {
                $objText = Text::getById($text_id, 0);
                if ($objText) $strName = $objText->getText();
            }
            self::$arrFacilities[$id] = array(
                'id'       => $id,
                'text_id'  => $text_id,
                'group_id' => $objResult->fields['facility_group_id'],
                'name'     => $strName,
                'ord'      => $objResult->fields['ord'],
            );
            $objResult->MoveNext();
        }

        // Facility groups
        $arrSqlName = Text::getSqlSnippets(
            '`group`.`name_text_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_HOTELCARD_FACILITY_GROUP
        );
        $query = "
            SELECT `group`.`id`, `group`.`ord` ".$arrSqlName['field']."
              FROM `".DBPREFIX."module_hotelcard_hotel_facility_group` AS `group`".
                   $arrSqlName['join']."
             ORDER BY `group`.`ord` ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        self::$arrGroups = array();
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $text_id = $objResult->fields['name_text_id'];
            $strName = $objResult->fields[$arrSqlName['text']];
            if ($strName === null) {
                $objText = Text::getById($text_id, 0);
                if ($objText) $strName = $objText->getText();
            }
            self::$arrGroups[$id] = array(
                'id'      => $id,
                'text_id' => $text_id,
                'name'    => $strName,
                'ord'     => $objResult->fields['ord'],
            );
            $objResult->MoveNext();
        }
        return true;
    }


    /**
     * Returns the array of all facilities
     *
     * The optional $group_id parameter limits the result to that group.
     * @param   integer   $group_id   The optional group ID
     * @return  array                 The facilities array on success,
     *                                false otherwise
     */
    function getFacilityArray($group_id='')
    {
        if (empty(self::$arrFacilities)) self::init();
        if (empty($group_id)) return self::$arrFacilities;
        $arrFacilities = array();
        foreach (self::$arrFacilities as $facility_id => $arrFacility) {
            if ($arrFacility['group_id'] != $group_id)
                continue;
            $arrFacilities[$facility_id] = $arrFacility;
        }
        return $arrFacilities;
    }


    /**
     * Returns an array of facility names.
     *
     * The optional $group_id parameter limits the result to that group.
     * @param   integer   $group_id   The optional group ID
     * @param   boolean   $short      If true, only a short list of the
     *                                available facilities is returned,
     *                                the full otherwise.
     * @return  array                 The facilities array on success,
     *                                false otherwise
     */
    function getFacilityNameArray($group_id=0, $short=false)
    {
        static $arrFacilityName = false;

        if (empty(self::$arrFacilities)) self::init();
        if (empty($group_id)) {
            // There is no group ID.
            // Return the buffered array, or set it up first.
            if (empty($arrFacilityName)) {
                foreach (self::$arrFacilities as $id => $arrFacility) {
                    if ($short && $arrFacility['ord'] >= 1000) continue;
                    $arrFacilityName[$id] = $arrFacility['name'];
                }
            }
            return $arrFacilityName;
        }
        // This subset is not buffered.  Do not confuse it with the static one!
        $arrFacilityNameTemp = array();
        foreach (self::$arrFacilities as $facility_id => $arrFacility) {
            if (   ($short && $arrFacility['ord'] >= 1000)
                || $arrFacility['group_id'] != $group_id)
                continue;
            $arrFacilityNameTemp[$facility_id] = $arrFacility['name'];
        }
        return $arrFacilityNameTemp;
    }


    static function getFacilityNameById($facility_id)
    {
//echo("HotelFacility::getFacilityNameById($facility_id): Entered<br />");
        if (empty(self::$arrFacilities)) self::init();
        return self::$arrFacilities[$facility_id]['name'];
    }


    /**
     * Returns the array of all facility groups
     * @return  array               The facility groups array on success,
     *                              false otherwise
     */
    static function getGroupArray()
    {
        if (empty(self::$arrGroups)) self::init();
        return self::$arrGroups;
    }


    /**
     * Returns an array of facility group names.
     * @return  array                 The facility group names array
     *                                on success, false otherwise
     */
    static function getGroupNameArray()
    {
        static $arrGroupName = false;

        if (empty(self::$arrGroups)) self::init();
        // Return the buffered array, or set it up first.
        if (empty($arrGroupName)) {
            foreach (self::$arrGroups as $id => $arrGroups) {
                $arrGroupName[$id] = $arrGroups['name'];
            }
        }
        return $arrGroupName;
    }


    /**
     * Returns true if the facility record with the given ID exists,
     * false otherwise
     * @param   integer   $id     The facility ID
     * @return  boolean           True on success, false otherwise
     * @static
     */
    static function recordFacilityExists($id)
    {
        global $objDatabase;

        $query = "
            SELECT 1
              FROM `".DBPREFIX."module_hotelcard_hotel_facility`
             WHERE `id`=$id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        return (!(bool)$objResult->EOF);
    }


    /**
     * Returns true if the facility group record with the given ID exists,
     * false otherwise
     * @param   integer   $id     The facility group ID
     * @return  boolean           True on success, false otherwise
     * @static
     */
    static function recordFacilityGroupExists($id)
    {
        global $objDatabase;

        $query = "
            SELECT 1
              FROM `".DBPREFIX."module_hotelcard_hotel_facility_group`
             WHERE `id`=$id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        return (!(bool)$objResult->EOF);
    }


    /**
     * Validates the array of facilities given by reference
     *
     * If found, invalid IDs are removed from the array, and false is
     * returned.  If the array contains nothing but positive integers as keys,
     * returns true.
     * Also returns false if the argument is no array.
     * Note that calling this function with an empty array will return
     * true.  Calling it twice with the same array will always return true
     * after the second run.
     * The $arrFacility array looks like
     *  array(
     *    facility ID => facility name,
     *    ... more ...
     *  )
     * @param   array     $arrFacility      The array of facilities
     * @return  boolean                     True if all IDs are valid,
     *                                      false otherwise
     */
    static function validateFacilityIdArray(&$arrFacility)
    {
        if (!is_array($arrFacility)) return false;
        $result = true;
        foreach (array_keys($arrFacility) as $facility_id) {
            if (is_integer($facility_id) && $facility_id > 0) {
                continue;
            }
//echo("HotelFacility::validateFacilityIdArray(".var_export($arrFacility, true).": Invalid entry at index $facility_id<br />");
            $result = false;
            unset($arrFacility[$facility_id]);
        }
        return $result;
    }


    /**
     * Stores a facility
     *
     * Updates the facility if it exists, otherwise inserts it.
     * This method fails if the name given is empty.
     * Affects the current frontend language as specified by FRONTEND_LANG_ID.
     * @param   string    $name       The facility name
     * @param   integer   $group_id   The facility group ID
     * @param   integer   $id         The facility ID
     * @param   integer   $ord        The ordinal number, defaults to zero
     * @return  boolean               True on success, false otherwise
     * @static
     * @global  ADONewConnection  $objDatabase
     */
    static function storeFacility($name, $group_id, $id=0, $ord=0)
    {
        global $objDatabase;

        if (empty($name)) return false;

        $text_id = 0;
        if ($id) {
            if (empty(self::$arrFacilities)) self::init();
            $text_id = self::$arrFacilities[$id]['text_id'];
            if ($text_id) $objText = Text::getById($text_id, FRONTEND_LANG_ID);
        }
        if (!$objText)
            $objText = new Text(
                $name, FRONTEND_LANG_ID, MODULE_ID,
                self::TEXT_HOTELCARD_FACILITY, $text_id);
        $objText->setText($name);
        $objText->setLanguageId(FRONTEND_LANG_ID);
        if (!$objText->store()) return false;
        $text_id = $objText->getId();
        if (self::recordFacilityExists($id)) {
            return self::updateFacility($text_id, $group_id, $id, $ord);
        }
        return self::insertFacility($text_id, $group_id, $ord);
    }


    /**
     * Updates a facility
     *
     * Use {@see storeFacility()}.  Mind that the related Text record is not
     * affected here.
     * @param   integer   $text_id    The name Text ID
     * @param   integer   $group_id   The facility group ID
     * @param   integer   $id         The facility ID
     * @param   integer   $ord        The optional ordinal number, defaults
     *                                to zero
     * @return  boolean               True on success, false otherwise
     * @static
     * @global  ADONewConnection  $objDatabase
     */
    private static function updateFacility($text_id, $group_id, $id, $ord=0)
    {
        global $objDatabase;

        $query = "
            UPDATE `".DBPREFIX."module_hotelcard_hotel_facility`
               SET `name_text_id`=$text_id,
                   `facility_group_id`=$group_id,
                   `ord`=$ord
             WHERE `id`=$id";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) return true;
        return false;
    }


    /**
     * Inserts a facility
     *
     * Use {@see storeFacility()}.  Mind that the related Text record is
     * inserted already and is not affected here.
     * @param   integer   $text_id    The name Text ID
     * @param   integer   $group_id   The facility group ID
     * @param   integer   $ord        The optional ordinal number, defaults
     *                                to zero
     * @return  boolean               True on success, false otherwise
     * @static
     * @global  ADONewConnection  $objDatabase
     */
    static function insertFacility($text_id, $group_id, $ord=0)
    {
        global $objDatabase;

        $query = "
            INSERT INTO `".DBPREFIX."module_hotelcard_hotel_facility` (
                `name_text_id`, `facility_group_id`, `ord`
            ) VALUES (
                $text_id, $group_id, $ord
            )";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) return true;
        return false;
    }


    /**
     * Deletes any facility relations for the given Hotel ID
     * @param   integer   $hotel_id     The Hotel ID
     * @return  boolean                 True on success, false otherwise
     */
    static function deleteByHotelId($hotel_id)
    {
        global $objDatabase;

        $query = "
            DELETE FROM `".DBPREFIX."module_hotelcard_hotel_has_facility`
             WHERE `hotel_id`=$hotel_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        return true;
    }


    /**
     * Stores a facility group
     *
     * Updates the facility group if it exists, otherwise inserts it.
     * This method fails if the name given is empty.
     * Affects the current frontend language as specified by FRONTEND_LANG_ID.
     * @param   string    $name       The facility group name
     * @param   integer   $id         The facility group ID
     * @param   integer   $ord        The ordinal number, defaults to zero
     * @return  boolean               True on success, false otherwise
     * @static
     * @global  ADONewConnection  $objDatabase
     */
    static function storeFacilityGroup($name, $id=0, $ord=0)
    {
        global $objDatabase;

        if (empty($name)) return false;

        $text_id = 0;
        if ($id) {
            if (empty(self::$arrGroups)) self::init();
            $text_id = self::$arrGroups[$id]['text_id'];
            if ($text_id) $objText = Text::getById($text_id, FRONTEND_LANG_ID);
        }
        if (!$objText)
            $objText = new Text(
                $name, FRONTEND_LANG_ID, MODULE_ID,
                self::TEXT_HOTELCARD_FACILITY_GROUP, $text_id);
        $objText->setText($name);
        $objText->setLanguageId(FRONTEND_LANG_ID);
        if (!$objText->store()) return false;
        $text_id = $objText->getId();
        if (self::recordFacilityGroupExists($id)) {
            return self::updateFacilityGroup($text_id, $id, $ord);
        }
        return self::insertFacilityGroup($text_id, $ord);
    }


    /**
     * Updates a facility group
     *
     * Use {@see storeFacilityGroup()} instead.
     * Mind that the related Text record is inserted in {@see storeFacility()}
     * and is not affected here.
     * @param   integer   $text_id    The name Text ID
     * @param   integer   $id         The facility group ID
     * @param   integer   $ord        The optional ordinal number, defaults
     *                                to zero
     * @return  boolean               True on success, false otherwise
     * @static
     * @global  ADONewConnection  $objDatabase
     */
    private static function updateFacilityGroup($text_id, $id, $ord=0)
    {
        global $objDatabase;

        $query = "
            UPDATE `".DBPREFIX."module_hotelcard_hotel_facility_group`
               SET `name_text_id`=$text_id,
                   `ord`=$ord
             WHERE `id`=$id";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) return true;
        return false;
    }


    /**
     * Inserts a facility
     *
     * Use {@see storeFacilityGroup()} instead.
     * Mind that the related Text record is inserted in {@see storeFacility()}
     * and is not affected here.
     * @param   integer   $text_id    The name Text ID
     * @param   integer   $ord        The optional ordinal number, defaults
     *                                to zero
     * @return  boolean               True on success, false otherwise
     * @static
     * @global  ADONewConnection  $objDatabase
     */
    private static function insertFacilityGroup($text_id, $ord=0)
    {
        global $objDatabase;

        $query = "
            INSERT INTO `".DBPREFIX."module_hotelcard_hotel_facility` (
                `name_text_id`, `ord`
            ) VALUES (
                $text_id, $ord
            )";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) return true;
        return false;
    }


    /**
     * Initializes the hotel to facility relations
     *
     * Reads the relations from the database for the hotel ID given, if any,
     * or the complete table otherwise.  Mind your step!
     * The facility IDs are sorted by the ordinal value from the facility
     * table.
     * The array returned looks like
     *  array(
     *    hotel ID => array(
     *      facility ID => facility ID
     *    ),
     *    ... more ...
     *  );
     * @todo    The array value should be replaced by the facility name
     *          in the current frontend language
     * @param   integer   $hotel_id     The optional hotel ID
     * @return  array                   The relation array on success,
     *                                  false otherwise
     * @global  ADONewConnection  $objDatabase
     * @static
     */
    static function getRelationArray($hotel_id=0)
    {
        global $objDatabase;

        if (   ($hotel_id && empty(self::$arrRelations[$hotel_id]))
            || empty(self::$arrRelations)) {
            $query = "
                SELECT `relation`.`hotel_id`, `relation`.`facility_id`
                  FROM `".DBPREFIX."module_hotelcard_hotel_has_facility` AS `relation`
                 INNER JOIN `".DBPREFIX."module_hotelcard_hotel_facility` AS `facility`
                    ON `relation`.`facility_id`=`facility`.`id`".
                ($hotel_id ? " WHERE `relation`.`hotel_id`=$hotel_id" : '')."
                 ORDER BY `facility`.`ord` ASC";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return self::errorHandler();
            if (!is_array(self::$arrRelations)) self::$arrRelations = array();
            while (!$objResult->EOF) {
                $hotel_id = $objResult->fields['hotel_id'];
                $facility_id = $objResult->fields['facility_id'];
                self::$arrRelations[$hotel_id][$facility_id] = $facility_id;
                $objResult->MoveNext();
            }
        }
        return self::$arrRelations;
    }


    /**
     * Returns true if the relation record with the given IDs exists,
     * false otherwise
     *
     * Note that this does not verify the existence of either the hotel
     * nor the facility, but only the relation itself.
     * @param   integer   $hotelid        The hotel ID
     * @param   integer   $facility_id    The facility ID
     * @return  boolean                   True if the record exists,
     *                                    false otherwise
     * @static
     */
    static function recordRelationExists($hotel_id, $facility_id)
    {
        global $objDatabase;

        $query = "
            SELECT 1
              FROM `".DBPREFIX."module_hotelcard_hotel_has_facility`
             WHERE `hotel_id`=$hotel_id
               AND `facility_id`=$facility_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        return (!(bool)$objResult->EOF);
    }


    /**
     * Adds a relation for the given hotel and facility IDs
     *
     * Inserts a new relation if it's not present in the database yet.
     * There's no need for an update for this table.
     * If a record exists already, true is returned.
     * @param   integer   $hotelid        The hotel ID
     * @param   integer   $facility_id    The facility ID
     * @return  boolean                   True on success, false otherwise
     * @static
     */
    static function addRelation($hotel_id, $facility_id)
    {
        global $objDatabase;

        if (self::recordRelationExists($hotel_id, $facility_id))
            return true;
        $query = "
            INSERT INTO `".DBPREFIX."module_hotelcard_hotel_has_facility` (
                `hotel_id`, `facility_id`
            ) VALUES (
                $hotel_id, $facility_id
            )";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) return true;
        return false;
    }


    /**
     * Removes one or several relations for the given hotel
     *
     * Deletes matching relations present in the database.
     * If no such record exists, true is returned anyway.
     * If the $facility_id argument is empty, all records for the
     * given $hotel_id are removed.
     * @param   integer   $hotelid        The hotel ID
     * @param   integer   $facility_id    The optional facility ID
     * @return  boolean                   True on success, false otherwise
     * @static
     */
    static function removeRelation($hotel_id, $facility_id=0)
    {
        global $objDatabase;

        $query = "
            DELETE FROM `".DBPREFIX."module_hotelcard_hotel_has_facility`
                WHERE `hotel_id`=$hotel_id".
            ($facility_id ? " AND `facility_id`=$facility_id" : '');
        $objResult = $objDatabase->Execute($query);
        if ($objResult) return true;
        return false;
    }


    /**
     * Tries to fix or recreate the database table(s) for the class
     *
     * Should be called whenever there's a problem with the database table.
     * @return  boolean             False.  Always.
     */
    static function errorHandler()
    {
        global $objDatabase;

die("HotelFacility::errorHandler(): Disabled!<br />");

        $arrTables = $objDatabase->MetaTables('TABLES');
        if (in_array(DBPREFIX."module_hotelcard_hotel_facility_group", $arrTables)) {
            $query = "DROP TABLE `".DBPREFIX."module_hotelcard_hotel_facility_group`";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
//echo("HotelFacility::errorHandler(): Dropped table ".DBPREFIX."module_hotelcard_hotel_facility_group<br />");
        }
        $query = "
            CREATE TABLE `".DBPREFIX."module_hotelcard_hotel_facility_group` (
              `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `name_text_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
              `ord` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Ordinal value, used for sorting the groups.',
              PRIMARY KEY (`id`)
            ) ENGINE=MYISAM";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
//echo("HotelFacility::errorHandler(): Created table ".DBPREFIX."module_hotelcard_hotel_facility_group<br />");

        if (in_array(DBPREFIX."module_hotelcard_hotel_facility", $arrTables)) {
            $query = "DROP TABLE `".DBPREFIX."module_hotelcard_hotel_facility`";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
//echo("HotelFacility::errorHandler(): Dropped table ".DBPREFIX."module_hotelcard_hotel_facility<br />");
        }
        $query = "
            CREATE TABLE `".DBPREFIX."module_hotelcard_hotel_facility` (
              `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `name_text_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
              `facility_group_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
              `ord` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Ordinal value, used for sorting the services within each group.',
              PRIMARY KEY (`id`)
            ) ENGINE=MYISAM";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
//echo("HotelFacility::errorHandler(): Created table ".DBPREFIX."module_hotelcard_hotel_facility<br />");

        // Add data
        // Groups
        $arrFacilityGroup = array(
// TODO: Translate the group names to French and Italian
            'general' => array(
                1 => 'Allgemein', // Deutsch
                2 => 'General',   // English
                3 => 'Géneral',   // Français
                4 => 'Generali',  // Italiano
            ),
            'activities' => array(
                1 => 'Aktivitäten', // Deutsch
                2 => 'Activities',  // English
                3 => 'Activités',   // Français
                4 => 'Attivitati',  // Italiano
            ),
            'services' => array(
                1 => 'Dienstleistungen', // Deutsch
                2 => 'Services',         // English
                3 => 'Services',         // Français
                4 => 'Servizi',          // Italiano
            ),
        );

        $arrFacilities = array(
            // General
            'general' => array(
                // Wizard
                array(
                    1 => 'Air Conditioning', // de
                    2 => 'Air Conditioning', // en
                    3 => 'Air Conditioning', // fr
                    4 => 'Air Conditioning', // it
                ),
// TODO: Translate all the entries in English, French, and Italian
                array(
                    1 => 'Fahrstuhl',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Frühstück',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Restaurant',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Parkplätze',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),

                // Edit only
                1000 =>
                array(
                    1 => '24 Stunden Reception',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Nichtraucherbetrieb',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Allergiefreie Räume',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Bar',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Kapelle/Gebetsraum',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Designerhotel',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Abgetrennte Raucherzone',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Frühstücksbuffet',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Express Check-In/Check-Out',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Familienzimmer',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Garten',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Zentralheizung',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Gepäckaufbewahrung',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Gratis Parking',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Zeitungen',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Nichtraucherzimmer',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Haustiere erlaubt',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Behindertengerechte Infrastruktur',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Hotelsafe',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Einkaufsmöglichkeiten',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Ski Aufbewahrung',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Schallgedämmte Zimmer',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Terrasse',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Parkierdienst',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
            ),
            // Activities
            'activities' => array(
                // Wizard
                array(
                    1 => 'Fitnessraum',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Golfplatz (im Umkreis von 3 km)',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Kinderspielplatz',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Spa & Wellness',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Tennisplatz',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),

                // Edit only
                1000 =>
                array(
                    1 => 'Grillplatz',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Billiard',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Bowling/Kegeln',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Kanufahrten',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Casino',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Radwege',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Dart',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Tauchen',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Fischen',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Spielzimmer',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Wanderwege',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Pferdereiten',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Jacuzzi',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Karaoke',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Bibliothek',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Massage',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Minigolf',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Sauna',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Ski Schule',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Skipisten',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Schnorcheln',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Solarium',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Squash',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Swimming Pool innen',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Swimming Pool aussen',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Tischtennis',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Dampfbad',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Windsurfen',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
            ),

            // Services
            'services' => array(
                // Wizard
                array(
                    1 => 'Geldautomat im Gebäude',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Geldwechsel',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Internet Zugang',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Konferenzraum',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Zimmerservice',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),

                // Edit only
                1000 =>
                array(
                    1 => 'Airport Shuttle',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Kinderkrippe/Hütdienst',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Fahrradvermietung',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Frühstück im Zimmer',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Hochzeitssuite',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Autovermietung',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Chemische Reinigung',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Fax/Kopiergerät',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Kostenloser WiFi Internet Zugang inbegriffen',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Coiffeur/Schönheitssalon',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Bügelservice',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Wäscheservice',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Bankettsaal',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Schuhreinigung',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Souvenirs/Geschenk Shop',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Ticket Verkauf',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'Tour Desk',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'VIP Räume',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
                array(
                    1 => 'WiFi/Drahtloses Internet',
                    2 => '', // en
                    3 => '', // fr
                    4 => '', // it
                ),
            ),
        );

        $ord_group = 0;
        $arrGroupId = array();
        foreach ($arrFacilityGroup as $group => $arrLang) {
            $text_id = 0;
            foreach ($arrLang as $lang_id => $name) {
                $objText = new Text(
                    $name, $lang_id,
                    MODULE_ID, self::TEXT_HOTELCARD_FACILITY_GROUP, $text_id);
                if (!$objText->store()) {
//die("failed to add Text for facility group $group: $name");
//                    return false;
                }
                $text_id = $objText->getId();
            }
            $objResult = $objDatabase->Execute("
                INSERT INTO `".DBPREFIX."module_hotelcard_hotel_facility_group` (
                  `name_text_id`, `ord`
                ) VALUES (
                  $text_id, ".++$ord_group."
                )");
            if (!$objResult) {
//echo("HotelFacility::errorHandler(): Failed to insert group $name<br />");
                continue;
            }
            $arrGroupId[$group] = $objDatabase->Insert_ID();
        }

        foreach ($arrFacilities as $group => $arrFacility) {
            $group_id = $arrGroupId[$group];
            foreach ($arrFacility as $ord_facility => $arrLang) {
                $text_id = 0;
                foreach ($arrLang as $lang_id => $name) {
                    $objText = new Text(
                        $name, $lang_id,
                        MODULE_ID, self::TEXT_HOTELCARD_FACILITY, $text_id);
                    if (!$objText->store()) {
//die("HotelFacility::errorHandler(): Failed to store facility text $name<br />");
//                        continue;
                    }
                    $text_id = $objText->getId();
                }
                $objResult = $objDatabase->Execute("
                    INSERT INTO `".DBPREFIX."module_hotelcard_hotel_facility` (
                      `name_text_id`, `facility_group_id`, `ord`
                    ) VALUES (
                      $text_id, $group_id, $ord_facility
                    )");
                if (!$objResult) {
//die("HotelFacility::errorHandler(): Failed to insert facility $name<br />");
//                    continue;
                }
            }
        }

        if (in_array(DBPREFIX."module_hotelcard_hotel_has_facility", $arrTables)) {
            $query = "DROP TABLE `".DBPREFIX."module_hotelcard_hotel_has_facility`";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
//echo("HotelFacility::errorHandler(): Dropped table ".DBPREFIX."module_hotelcard_hotel_has_facility<br />");
        }
        $query = "
            CREATE TABLE `".DBPREFIX."module_hotelcard_hotel_has_facility` (
              `hotel_id` INT(10) UNSIGNED NOT NULL,
              `facility_id` INT(10) UNSIGNED NOT NULL,
              PRIMARY KEY (`hotel_id`, `facility_id`)
            ) ENGINE=MYISAM";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
//echo("HotelFacility::errorHandler(): Created table ".DBPREFIX."module_hotelcard_hotel_has_facility<br />");
//            return false;
        }

        // More to come...

        // Always!
        return false;
    }

}

?>
