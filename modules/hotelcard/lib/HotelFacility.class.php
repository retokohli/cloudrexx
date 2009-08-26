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
    const TEXT_HOTELCARD_FACILITY = 'HOTELCARD_FACILITY';
    const TEXT_HOTELCARD_FACILITY_GROUP = 'HOTELCARD_FACILITY_GROUP';

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
            SELECT `facility`.`facility_group_id`, `facility`.`ord`
                   ".$arrSqlName['field']."
              FROM `".DBPREFIX."module_hotelcard_hotel_facility` AS `facility`".
                   $arrSqlName['join']."
             ORDER BY `facility`.`ord` ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        self::$arrFacilities = array();
        while (!$objResult->EOF) {
            $id = $objResult->fields['name_text_id'];
            $strName = $objResult->fields[$arrSqlName['text']];
            if ($strName === null) {
                $objText = Text::getById($id, 0);
                if ($objText) $strName = $objText->getText();
            }
            self::$arrFacilities[$id] = array(
                'id'       => $id,
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
            SELECT `name_text_id`, `ord` ".$arrSqlName['field']."
              FROM `".DBPREFIX."module_hotelcard_hotel_facility_group` AS `group`".
                   $arrSqlName['join']."
             ORDER BY `group`.`ord` ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        self::$arrGroups = array();
        while (!$objResult->EOF) {
            $id = $objResult->fields['name_text_id'];
            $strName = $objResult->fields[$arrSqlName['text']];
            if ($strName === null) {
                $objText = Text::getById($id, 0);
                if ($objText) $strName = $objText->getText();
            }
            self::$arrGroups[$id] = array(
                'id'   => $id,
                'name' => $strName,
                'ord'  => $objResult->fields['ord'],
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
     * @return  array                 The facilities array on success,
     *                                false otherwise
     */
    function getFacilityNameArray($group_id)
    {
        static $arrFacilityName = false;

        if (empty(self::$arrFacilities)) self::init();
        if (empty($group_id)) {
            // There is no group ID.
            // Return the buffered array, or set it up first.
            if (empty($arrFacilityName)) {
                foreach (self::$arrFacilities as $id => $arrFacility) {
                    $arrFacilityName[$id] = $arrFacility['name'];
                }
            }
            return $arrFacilityName;
        }
        // This subset is not buffered.  Do not confuse it with the static one!
        $arrFacilityNameTemp = array();
        foreach (self::$arrFacilities as $facility_id => $arrFacility) {
            if ($arrFacility['group_id'] != $group_id)
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
             WHERE `name_text_id`=$id";
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
             WHERE `name_text_id`=$id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        return (!(bool)$objResult->EOF);
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

        if ($id) $objText = Text::getById($id, FRONTEND_LANG_ID);
        if (!$objText)
            $objText = new Text(
                $name, FRONTEND_LANG_ID, MODULE_ID,
                self::TEXT_HOTELCARD_FACILITY, $id);
        if (!$objText->store()) return false;
        $id = $objText->getId();
        if (self::recordFacilityExists($id)) {
            return self::updateFacility($name, $group_id, $id, $ord);
        }
        return self::insertFacility($name, $group_id, $id, $ord);
    }


    /**
     * Updates a facility
     *
     * Mind that the related Text record is inserted in {@see storeFacility()}
     * and is not affected here.
     * @param   integer   $id         The facility ID
     * @param   integer   $group_id   The facility group ID
     * @param   integer   $ord        The ordinal number, defaults to zero
     * @return  boolean               True on success, false otherwise
     * @static
     * @global  ADONewConnection  $objDatabase
     */
    static function updateFacility($id, $group_id, $ord=0)
    {
        global $objDatabase;

        $query = "
            UPDATE `".DBPREFIX."module_hotelcard_hotel_facility`
               SET `facility_group_id`=$group_id,
                   `ord`=$ord
             WHERE `name_text_id`=$id";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) return true;
        return false;
    }


    /**
     * Inserts a facility
     *
     * Mind that the related Text record is inserted in {@see storeFacility()}
     * and is not affected here, so the facility ID is known already.
     * @param   integer   $id         The facility ID
     * @param   integer   $group_id   The facility group ID
     * @param   integer   $ord        The ordinal number, defaults to zero
     * @return  boolean               True on success, false otherwise
     * @static
     * @global  ADONewConnection  $objDatabase
     */
    static function insertFacility($id, $group_id, $ord=0)
    {
        global $objDatabase;

        $query = "
            INSERT INTO `".DBPREFIX."module_hotelcard_hotel_facility` (
                `name_text_id`, `facility_group_id`, `ord`
            ) VALUES (
                $id, $group_id, $ord
            )";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) return true;
        return false;
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

        if ($id) $objText = Text::getById($id, FRONTEND_LANG_ID);
        if (!$objText)
            $objText = new Text(
                $name, FRONTEND_LANG_ID, MODULE_ID,
                self::TEXT_HOTELCARD_FACILITY_GROUP, $id);
        if (!$objText->store()) return false;
        $id = $objText->getId();
        if (self::recordFacilityGroupExists($id)) {
            return self::updateFacilityGroup($name, $id, $ord);
        }
        return self::insertFacilityGroup($name, $id, $ord);
    }


    /**
     * Updates a facility group
     *
     * Mind that the related Text record is inserted in {@see storeFacility()}
     * and is not affected here.
     * @param   integer   $id         The facility group ID
     * @param   integer   $ord        The ordinal number, defaults to zero
     * @return  boolean               True on success, false otherwise
     * @static
     * @global  ADONewConnection  $objDatabase
     */
    static function updateFacilityGroup($id, $ord=0)
    {
        global $objDatabase;

        $query = "
            UPDATE `".DBPREFIX."module_hotelcard_hotel_facility_group`
               SET `ord`=$ord
             WHERE `name_text_id`=$id";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) return true;
        return false;
    }


    /**
     * Inserts a facility
     *
     * Mind that the related Text record is inserted in {@see storeFacility()}
     * and is not affected here, so the facility ID is known already.
     * @param   integer   $id         The facility group ID
     * @param   integer   $ord        The ordinal number, defaults to zero
     * @return  boolean               True on success, false otherwise
     * @static
     * @global  ADONewConnection  $objDatabase
     */
    static function insertFacilityGroup($id, $ord=0)
    {
        global $objDatabase;

        $query = "
            INSERT INTO `".DBPREFIX."module_hotelcard_hotel_facility` (
                `name_text_id`, `ord`
            ) VALUES (
                $id, $ord
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
     * for that.
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
                    ON `relation`.`facility_id`=`facility`.`name_text_id`".
                ($hotel_id ? " WHERE `relation`.`hotel_id`=$hotel_id" : '')."
                 ORDER BY `facility`.`ord` ASC";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return self::errorHandler();
            if (!is_array(self::$arrRelations)) self::$arrRelations = array();
            while (!$objResult->EOF) {
                $hotel_id = $objResult->fields['hotel_id'];
                $facility_id = $objResult->fields['facility_id'];
                self::$arrRelations[$hotel_id] = $facility_id;
                $objResult->MoveNext();
            }
        }
        return self::$arrRelations;
    }


    /**
     * Returns true if the relation record with the given IDs exists,
     * false otherwise
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

echo("HotelFacility::errorHandler(): Entered<br />");

        $arrTables = $objDatabase->MetaTables('TABLES');
        if (in_array(DBPREFIX."module_hotelcard_hotel_facility_group", $arrTables)) {
            $query = "DROP TABLE `".DBPREFIX."module_hotelcard_hotel_facility_group`";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
echo("HotelFacility::errorHandler(): Dropped table ".DBPREFIX."module_hotelcard_hotel_facility_group<br />");
        }
        $query = "
            CREATE TABLE `".DBPREFIX."module_hotelcard_hotel_facility_group` (
              `name_text_id` INT UNSIGNED NOT NULL DEFAULT 0,
              `ord` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Ordinal value, used for sorting the groups.',
              PRIMARY KEY (`name_text_id`),
              INDEX `facility_name_text_id` (`name_text_id` ASC),
              CONSTRAINT `facility_name_text_id`
                FOREIGN KEY (`name_text_id`)
                REFERENCES `".DBPREFIX."core_text` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION
            ) ENGINE=MYISAM";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
echo("HotelFacility::errorHandler(): Created table ".DBPREFIX."module_hotelcard_hotel_facility_group<br />");

        // Add data
        $arrFacilityGroup = array(
            // name
            'General',
            'Activities',
            'Services',
        );
        $ord = 0;
        $arrGroupId = array();
        foreach ($arrFacilityGroup as $name) {
            $objText = new Text(
                $name, 2, MODULE_ID, self::TEXT_HOTELCARD_FACILITY_GROUP);
            if (!$objText->store()) {
echo("HotelFacility::errorHandler(): Failed to store group text $name<br />");
                continue;
            }
            $objResult = $objDatabase->Execute("
                INSERT INTO `".DBPREFIX."module_hotelcard_hotel_facility_group` (
                  `name_text_id`, `ord`
                ) VALUES (
                  ".$objText->getId().", ".++$ord."
                )");
            if (!$objResult) {
echo("HotelFacility::errorHandler(): Failed to insert group $name<br />");
                continue;
            }
            $arrGroupId[$name] = $objText->getId();
        }

        if (in_array(DBPREFIX."module_hotelcard_hotel_facility", $arrTables)) {
            $query = "DROP TABLE `".DBPREFIX."module_hotelcard_hotel_facility`";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
echo("HotelFacility::errorHandler(): Dropped table ".DBPREFIX."module_hotelcard_hotel_facility<br />");
        }
        $query = "
            CREATE TABLE `".DBPREFIX."module_hotelcard_hotel_facility` (
              `name_text_id` INT UNSIGNED NOT NULL DEFAULT 0,
              `facility_group_id` INT UNSIGNED NOT NULL DEFAULT 0,
              `ord` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Ordinal value, used for sorting the services within each group.',
              PRIMARY KEY (`name_text_id`, `facility_group_id`),
              INDEX `facility_group_id` (`facility_group_id` ASC),
              CONSTRAINT `facility_group_id`
                FOREIGN KEY (`facility_group_id`)
                REFERENCES `".DBPREFIX."module_hotelcard_hotel_facility_group` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION
            ) ENGINE=MYISAM";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
echo("HotelFacility::errorHandler(): Created table ".DBPREFIX."module_hotelcard_hotel_facility<br />");

        // Add data
        $arrFacility = array(
            // name => group name
            '24-Hour Front Desk' => 'General',
            'Air Conditioning' => 'General',
            'All Public and Private spaces non-smoking' => 'General',
            'Allergy-Free Room Available' => 'General',
            'Bar' => 'General',
            'Breakfast Buffet' => 'General',
            'Chapel/Shrine' => 'General',
            'Continental Breakfast' => 'General',
            'Design Hotel' => 'General',
            'Designated Smoking Area' => 'General',
            'Elevator' => 'General',
            'Express Check-In/Check-Out' => 'General',
            'Family Rooms' => 'General',
            'Free Parking' => 'General',
            'Garden' => 'General',
            'Gay Friendly' => 'General',
            'Heating' => 'General',
            'Luggage Storage' => 'General',
            'Newspapers' => 'General',
            'Non-Smoking Rooms' => 'General',
            'Parking' => 'General',
            'Pets Allowed' => 'General',
            'Restaurant' => 'General',
            'Rooms/Facilities for Disabled Guests' => 'General',
            'Safety Deposit Box' => 'General',
            'Shops in Hotel' => 'General',
            'Ski Storage' => 'General',
            'Soundproofed Rooms' => 'General',
            'Terrace' => 'General',
            'Valet Parking' => 'General',
            'BQ Facilities' => 'Activities',
            'Billiards' => 'Activities',
            'Bowling' => 'Activities',
            'Canoeing' => 'Activities',
            'Casino' => 'Activities',
            'Children\'s Playground' => 'Activities',
            'Cycling' => 'Activities',
            'Darts' => 'Activities',
            'Diving' => 'Activities',
            'Fishing' => 'Activities',
            'Fitness Centre' => 'Activities',
            'Games Room' => 'Activities',
            'Golf Course (within 3 km)' => 'Activities',
            'Hammam' => 'Activities',
            'Hiking' => 'Activities',
            'Horse Riding' => 'Activities',
            'Jacuzzi' => 'Activities',
            'Karaoke' => 'Activities',
            'Library' => 'Activities',
            'Massage' => 'Activities',
            'Mini Golf' => 'Activities',
            'Sauna' => 'Activities',
            'Ski School' => 'Activities',
            'Skiing' => 'Activities',
            'Snorkelling' => 'Activities',
            'Solarium' => 'Activities',
            'Spa & Wellness Centre' => 'Activities',
            'Squash' => 'Activities',
            'Indoor Swimming Pool' => 'Activities',
            'Outdoor Swimming Pool' => 'Activities',
            'Table Tennis' => 'Activities',
            'Tennis Court' => 'Activities',
            'Turkish/Steam Bath' => 'Activities',
            'Windsurfing' => 'Activities',
            'Airport Shuttle' => 'Services',
            'ATM/Cash Machine on site' => 'Services',
            'Babysitting/Child Services' => 'Services',
            'Bicycle Rental' => 'Services',
            'Breakfast in the Room' => 'Services',
            'Bridal Suite' => 'Services',
            'Business Centre' => 'Services',
            'Car Rental' => 'Services',
            'Currency Exchange' => 'Services',
            'Dry Cleaning' => 'Services',
            'Fax/Photocopying' => 'Services',
            'Free Wi-Fi Internet Access Included' => 'Services',
            'Barber/Beauty Shop' => 'Services',
            'Internet Services' => 'Services',
            'Ironing Service' => 'Services',
            'Laundry' => 'Services',
            'Meeting/Banquet Facilities' => 'Services',
            'Packed Lunches' => 'Services',
            'Room Service' => 'Services',
            'Shoe Shine' => 'Services',
            'Souvenirs/Gift Shop' => 'Services',
            'Ticket Service' => 'Services',
            'Tour Desk' => 'Services',
            'VIP Room Facilities' => 'Services',
            'Wi-Fi/Wireless LAN' => 'Services',
        );

        $ord = 0;
        foreach ($arrFacility as $name => $group) {
            $objText = new Text(
                $name, 2, MODULE_ID, self::TEXT_HOTELCARD_FACILITY);
            if (!$objText->store()) {
echo("HotelFacility::errorHandler(): Failed to store facility text $name<br />");
                continue;
            }
            $objResult = $objDatabase->Execute("
                INSERT INTO `".DBPREFIX."module_hotelcard_hotel_facility` (
                  `name_text_id`, `facility_group_id`, `ord`
                ) VALUES (
                  ".$objText->getId().", ".$arrGroupId[$group].", ".++$ord."
                )");
            if (!$objResult) {
echo("HotelFacility::errorHandler(): Failed to insert facility $name<br />");
                continue;
            }
        }

        if (in_array(DBPREFIX."module_hotelcard_hotel_has_facility", $arrTables)) {
            $query = "DROP TABLE `".DBPREFIX."module_hotelcard_hotel_has_facility`";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
echo("HotelFacility::errorHandler(): Dropped table ".DBPREFIX."module_hotelcard_hotel_has_facility<br />");
        }
        $query = "
            CREATE TABLE `".DBPREFIX."module_hotelcard_hotel_has_facility` (
              `hotel_id` INT UNSIGNED NOT NULL,
              `facility_id` INT UNSIGNED NOT NULL,
              INDEX `facility_hotel_id` (`hotel_id` ASC),
              INDEX `facility_id` (`facility_id` ASC),
              PRIMARY KEY (`hotel_id`, `facility_id`),
              CONSTRAINT `facility_hotel_id`
                FOREIGN KEY (`hotel_id`)
                REFERENCES `".DBPREFIX."module_hotelcard_hotel` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION,
              CONSTRAINT `facility_id`
                FOREIGN KEY (`facility_id`)
                REFERENCES `".DBPREFIX."module_hotelcard_hotel_facility` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION
            ) ENGINE=MYISAM";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
echo("HotelFacility::errorHandler(): Created table ".DBPREFIX."module_hotelcard_hotel_has_facility<br />");
// TODO: Add data

        // More to come...

        // Always!
        return false;
    }

}

?>
