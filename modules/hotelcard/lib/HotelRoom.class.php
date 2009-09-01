<?php

/**
 * Hotel room class
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
 * Hotel room class
 * @version     2.2.0
 * @since       2.2.0
 * @package     contrexx
 * @subpackage  module_hotelcard
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */
class HotelRoom
{
    const TEXT_HOTELCARD_ROOM_TYPE = 'hotelcard_room_type';
    const TEXT_HOTELCARD_ROOM_FACILITY = 'hotelcard_room_facility';

    /**
     * Array of hotel room types
     *
     * The array is of the form
     *  array(
     *    room type ID => array(
     *      'id'             => room type ID,
     *      'name'           => type name,
     *      'number_default' => default number of rooms available per day,
     *      'price_default'  => default price per day,
     *      'facilities'  => array (
     *        facility ID => facility name,
     *        ... more ...
     *      'availabilities'   => array(
     *        date => array(
     *          'number_total'     => number of rooms available that day,
     *          'number_booked'    => number of rooms booked that day,
     *          'number_cancelled' => number of rooms cancelled that day,
     *          'price'            => price for that day,
     *        ),
     *        ... more ...
     *      ),
     *    ),
     *    ... more ...
     *  )
     * The room types are ordered by their room type ID, which is usually the
     * same order that they have been entered in.
     * @var     array
     * @access  private
     * @static
     */
    private static $arrRoomtypes = false;

    /**
     * Array of all known room facilities
     *
     * The array is of the form
     *  array(
     *    facility ID => array(
     *      'name' => facility name,
     *      'ord'  => ordinal value,
     *    ),
     *    ... more ...
     *  )
     * The facilities are sorted by their ordinal values.
     * @var     array
     * @access  private
     * @static
     */
    private static $arrFacilities = false;

    /**
     * The hotel ID used when calling {@see init()}
     * @var   integer
     */
    private static $hotel_id = false;


    /**
     * Initializes the facilities and types data from the database
     *
     * Reads records for the given $hotel_id only.  The optional $room_type_id
     * may further restrict the result to any single type of room.
     * The optional $da
     * @param   integer   $hotel_id       The hotel ID
     * @param   integer   $room_type_id   The optional room type ID
     * @param   string    $date_from      The optinal start date for the
     *                                    availabilities
     * @param   string    $date_to        The optinal end date for the
     *                                    availabilities
     * @return  boolean                   True on success, false otherwise
     * @global  ADONewConnection  $objDatabase
     */
    static function init(
        $hotel_id, $room_type_id=0, $date_from='', $date_to='')
    {
        global $objDatabase;

        if (empty($hotel_id)) return false;
        if (empty(self::$arrFacilities)) self::initFacilities();

        // Room type
        $arrSqlName = Text::getSqlSnippets(
            '`type`.`type_text_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_HOTELCARD_ROOM_TYPE
        );
        $query = "
            SELECT `type`.`number_default`, `type`.`price_default`
                   ".$arrSqlName['field']."
              FROM `".DBPREFIX."module_hotelcard_room_type` AS `type`".
                   $arrSqlName['join']."
             WHERE `type`.`hotel_id`=$hotel_id".
            ($room_type_id ? " AND `type`.`type_text_id`=$room_type_id" : '')."
             ORDER BY `type`.`type_text_id` ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
die;//return self::errorHandler();
        // Flush previous types that belong to a different hotel
        if (   empty(self::$arrRoomtypes)
            || $hotel_id != self::$hotel_id)
            self::$arrRoomtypes = array();
        while (!$objResult->EOF) {
            $room_type_id = $objResult->fields['type_text_id'];
            $strName = $objResult->fields[$arrSqlName['text']];
            if ($strName === null) {
                $objText = Text::getById($room_type_id, 0);
                if ($objText) $strName = $objText->getText();
            }
            self::$arrRoomtypes[$room_type_id] = array(
                'id'             => $room_type_id,
                'name'           => $strName,
                'number_default' => $objResult->fields['number_default'],
                'price_default'  => $objResult->fields['price_default'],
                'facilities'     => array(),
                'availabilities' => array(),
            );
            $objResult->MoveNext();
        }

        foreach (self::$arrRoomtypes as $room_type_id => &$arrRoomtype) {
            // Facility for each room type
            $query = "
                SELECT `room_facility_id`
                  FROM `".DBPREFIX."module_hotelcard_room_facility` AS `facility`
                 INNER JOIN `".DBPREFIX."module_hotelcard_room_type_has_room_facility` AS `relation`
                    ON `facility`.`name_text_id`=`relation`.`room_facility_id`
                 WHERE `relation`.`room_type_id`=$room_type_id
                 ORDER BY `facility`.`ord` ASC";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult)
die;//return self::errorHandler();
            while (!$objResult->EOF) {
                $facility_id = $objResult->fields['room_facility_id'];
                $strName = self::$arrFacilities[$facility_id]['name'];
                $arrRoomtype['facilities'][$facility_id] = $strName;
                $objResult->MoveNext();
            }

            // Availability for each room type
            $query = "
                SELECT `availability`.`date`,
                       `availability`.`number_total`,
                       `availability`.`number_booked`,
                       `availability`.`number_cancelled`,
                       `availability`.`price`
                  FROM `".DBPREFIX."module_hotelcard_room_available` AS `availability`
                 WHERE `availability`.`room_type_id`=$room_type_id".
                  ($date_from ? " AND `availability`.`date`>='$date_from'" : '').
                  ($date_to   ? " AND `availability`.`date`<='$date_to'"   : '')."
                 ORDER BY `availability`.`date` ASC";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult)
die;//return self::errorHandler();
            while (!$objResult->EOF) {
                $date = $objResult->fields['date'];
                $arrRoomtype['availabilities'][$date] = array(
                    'number_total'     => $objResult->fields['number_total'],
                    'number_booked'    => $objResult->fields['number_booked'],
                    'number_cancelled' => $objResult->fields['number_cancelled'],
                    'price'            => $objResult->fields['price'],
                );
                $objResult->MoveNext();
            }
        }
//echo("HotelRoom::init($hotel_id, $room_type_id): made<br />".var_export(self::$arrRoomtypes, true)."<hr />");
        return true;
    }


    /**
     * Initialize the static array of all facilities available
     *
     * Usually only called once by {@see init()}, or directly by
     * {@see getFacilityArray()} when needed.
     * @return    boolean               True on success, false otherwise
     */
    static function initFacilities()
    {
        global $objDatabase;

        // All facilities available
        $arrSqlName = Text::getSqlSnippets(
            '`facility`.`name_text_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_HOTELCARD_ROOM_FACILITY
        );
        $query = "
            SELECT `facility`.`ord` ".$arrSqlName['field']."
              FROM `".DBPREFIX."module_hotelcard_room_facility` AS `facility`".
                   $arrSqlName['join']."
             ORDER BY `facility`.`ord` ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        self::$arrFacilities = array();
        while (!$objResult->EOF) {
            $facility_id = $objResult->fields['name_text_id'];
            $strName = $objResult->fields[$arrSqlName['text']];
            if ($strName === null) {
                $objText = Text::getById($facility_id, 0);
                if ($objText) $strName = $objText->getText();
            }
            self::$arrFacilities[$facility_id] = array(
                'id'       => $facility_id,
                'name'     => $strName,
                'ord'      => $objResult->fields['ord'],
            );
            $objResult->MoveNext();
        }
//echo("HotelRoom::initFacilities(): Made ".var_export(self::$arrFacilities, true)."<hr />");
        return true;
    }


    /**
     * Returns the array of all facilities
     * @return  array               The facilities array on success,
     *                              false otherwise
     * @static
     */
    static function getFacilityArray()
    {
        if (empty(self::$arrFacilities)) self::initFacilities();
        return self::$arrfacilities;
    }


    /**
     * Returns the array of all facility names
     * @param   boolean   $short    If true, only a short list of facilites
     *                              are returned with the array
     * @return  array               The facility name array on success,
     *                              false otherwise
     * @static
     */
    static function getFacilityNameArray($short=false)
    {
        static $arrFacilityName = false;

        if (empty($arrFacilityName)) {
            if (empty(self::$arrFacilities)) self::initFacilities();
            foreach (self::$arrFacilities as $facility_id => $arrFacility) {
//echo("HotelRoom::getFacilityNameArray($short): Facility: ".var_export($arrFacility, true)."<hr />");
//echo("HotelRoom::getFacilityNameArray($short): ord ".$arrFacility['ord']."<hr />");
                if ($short && $arrFacility['ord'] >= 1000) {
//echo("HotelRoom::getFacilityNameArray($short): Skipping ord ".$arrFacility['ord']."<hr />");
                    continue;
                }
                $arrFacilityName[$facility_id] = $arrFacility['name'];
            }
        }
//echo("HotelRoom::getFacilityNameArray($short): Returning ".var_export($arrFacilityName, true)."<hr />");
        return $arrFacilityName;
    }


    /**
     * Returns the array of room types
     *
     * Uses the previous hotel ID stored in the class, if available.
     * Otherwise, the $hotel_id *MUST* be specified.
     * Apart from that, it works like {@see init()}, but returns
     * the type array instead of a boolean.
     * @param   integer   $hotel_id       The optional hotel ID
     * @param   integer   $room_type_id   The optional room type ID
     * @param   string    $date_from      The optinal start date for the
     *                                    availabilities
     * @param   string    $date_to        The optinal end date for the
     *                                    availabilities
     * @return  array                     The room types array on success,
     *                                    false otherwise
     * @static
     */
    static function getTypeArray(
        $hotel_id=0, $room_type_id=0, $date_from='', $date_to='')
    {
        if (empty($hotel_id)) $hotel_id = self::$hotel_id;
        if (   ($room_type_id && empty(self::$arrRoomtypes[$room_type_id]))
            || empty(self::$arrRoomtypes))
            self::init($hotel_id, $room_type_id, $date_from, $date_to);
        return self::$arrRoomtypes;
    }


    /**
     * Returns true if the facility record with the given ID exists,
     * false otherwise
     * @param   integer   $facility_id    The facility ID
     * @return  boolean                   True on success, false otherwise
     * @static
     */
    static function recordFacilityExists($facility_id)
    {
        global $objDatabase;

        $query = "
            SELECT 1
              FROM `".DBPREFIX."module_hotelcard_room_facility`
             WHERE `facility`.`name_text_id`=$facility_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        return (!(bool)$objResult->EOF);
    }


    /**
     * Returns true if the room type record with the given ID exists,
     * false otherwise
     * @param   integer   $room_type_id   The room type ID
     * @return  boolean                   True on success, false otherwise
     * @static
     */
    static function recordTypeExists($room_type_id)
    {
        global $objDatabase;

        $query = "
            SELECT 1
              FROM `".DBPREFIX."module_hotelcard_room_type`
             WHERE `type_text_id`=$room_type_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        return (!(bool)$objResult->EOF);
    }


    /**
     * Returns true if the room availability record with the given IDs
     * and date exists, false otherwise
     * @param   integer   $room_type_id   The room type ID
     * @param   string    $date           The date
     * @return  boolean                   True on success, false otherwise
     * @static
     */
    static function recordAvailabilityExists($room_type_id, $date)
    {
        global $objDatabase;

        $query = "
            SELECT 1
              FROM `".DBPREFIX."module_hotelcard_room_available`
             WHERE `room_type_id`=$room_type_id
               AND `date`='$date'";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        return (!(bool)$objResult->EOF);
    }


    /**
     * Adds the facility to the room type
     * @param   integer   $room_type_id   The room type ID
     * @param   integer   $facility_id    The facility ID
     * @return  boolean                   True on success, false otherwise
     * @return
     */
    static function addFacility($room_type_id, $facility_id)
    {
        global $objDatabase;

        $query = "
            INSERT INTO `".DBPREFIX."module_hotelcard_room_type_has_room_facility` (
                `room_type_id`, `room_facility_id`
            ) VALUES (
                $room_type_id, $facility_id
            )";
        $objResult = $objDatabase->Execute($query);
        return (bool)$objResult;

    }


    /**
     * Stores a room type
     *
     * Updates the room type if it exists, otherwise inserts it.
     * This method fails if the name or hotel ID given is empty.
     * Affects the current frontend language as specified by FRONTEND_LANG_ID.
     * @param   integer   $hotel_id         The hotel ID
     * @param   string    $name             The room type name
     * @param   integer   $number_default   The default number of rooms per day
     * @param   integer   $price_default    The default price per day
     * @param   integer   $room_type_id     The optional room type ID
     * @return  integer                     The ID of the record inserted or
     *                                      updated on success, zero otherwise
     * @static
     * @global  ADONewConnection  $objDatabase
     */
    static function storeType(
        $hotel_id, $name, $number_default=1, $price_default=100, $room_type_id=0
    ) {
        global $objDatabase;

        if (empty($hotel_id) || empty($name)) return false;
//echo("HotelRoom::storeType(hotel_id $hotel_id, name $name, number_default $number_default, price_default $price_default, room_type_id $room_type_id): Entered<br />");
        $objText = false;
        $room_type_id = intval($room_type_id);
        if ($room_type_id)
            $objText = Text::getById($room_type_id, FRONTEND_LANG_ID);
        if ($objText) {
            $objText->setText($name);
        } else {
            $objText = new Text(
                $name, FRONTEND_LANG_ID, MODULE_ID,
                self::TEXT_HOTELCARD_ROOM_TYPE, $room_type_id);
        }
        if (!$objText->store()) return false;
        $room_type_id = $objText->getId();
        $number_default = intval($number_default);
        $price_default = intval($price_default);
        if (self::recordTypeExists($room_type_id))
            return self::updateType($room_type_id, $number_default, $price_default);
        return self::insertType(
            $room_type_id, $hotel_id, $number_default, $price_default);
    }


    /**
     * Updates a room type
     *
     * Mind that the related Text record is inserted in {@see storeType()}
     * and is not affected here.
     * @param   integer   $room_type_id     The room type ID
     * @param   integer   $number_default   The default number of rooms per day
     * @param   integer   $price_default    The default price per day
     * @return  integer                     The ID of the record inserted or
     *                                      updated on success, zero otherwise
     * @static
     * @global  ADONewConnection  $objDatabase
     */
    static function updateType($room_type_id, $number_default, $price_default)
    {
        global $objDatabase;

        if (empty($room_type_id)) return false;
        $query = "
            UPDATE `".DBPREFIX."module_hotelcard_room_type`
               SET `number_default`=$number_default,
                   `price_default`=$price_default
             WHERE `type_text_id`=$room_type_id";
        $objResult = $objDatabase->Execute($query);
        return ($objResult ? $room_type_id : 0);
    }


    /**
     * Inserts a room type
     *
     * Mind that the related Text record is inserted in {@see storeType()}
     * and is not affected here.
     * @param   integer   $room_type_id     The room type ID
     * @param   integer   $hotel_id         The hotel ID
     * @param   integer   $number_default   The default number of rooms per day
     * @param   integer   $price_default    The default price per day
     * @return  integer                     The ID of the record inserted or
     *                                      updated on success, zero otherwise
     * @static
     * @global  ADONewConnection  $objDatabase
     */
    static function insertType(
        $room_type_id, $hotel_id, $number_default, $price_default
    ) {
        global $objDatabase;

        if (empty($room_type_id) || empty($hotel_id)) return false;
        $query = "
            INSERT INTO `".DBPREFIX."module_hotelcard_room_type` (
                `type_text_id`, `hotel_id`, `number_default`, `price_default`
            ) VALUES (
                $room_type_id, $hotel_id, $number_default, $price_default
            )";
        $objResult = $objDatabase->Execute($query);
        return ($objResult ? $room_type_id : 0);
    }


    /**
     * Stores the availability for a room type
     *
     * Updates single availability records that exist, inserts missing ones.
     * Dates that are not present in the availability array will not be
     * affected at all.
     * If the $room_type_id is empty, or if the $arrAvailability argument
     * is empty or not an array, false is returned.
     * The array has the same structure as the 'availabilities' branch in
     * {@see $arrRoomtypes}, and the data posted by the edit_hotel page in
     * the frontend:
     *  array(
     *    date => array(
     *      'number_total'     => number of rooms available that day,
     *      'number_booked'    => number of rooms booked that day,
     *      'number_cancelled' => number of rooms cancelled that day,
     *      'price'            => price for that day,
     *    ),
     *    ... more ...
     *  )
     * @param   string    $room_type_id     The room type ID
     * @param   array     $arrAvailability  The availability array
     * @return  boolean                     True on success, false on failure,
     *                                      or the empty string
     * @static
     * @global  ADONewConnection  $objDatabase
     */
    static function storeAvailabilityArray($room_type_id, $arrAvailability)
    {
        global $objDatabase;

//echo("HotelRoom::storeAvailabilityArray($room_type_id, ".var_export($arrAvailability, true).": Entered<br />");

        if (   empty($room_type_id)
            || empty($arrAvailability) || !is_array($arrAvailability))
            return false;

        $result = true;
        foreach ($arrAvailability as $date => $arrAvailable) {
//echo("HotelRoom::storeAvailabilityArray(): date $date, available ".var_export($arrAvailable, true)."<br />");

            // If the number_total index is not present, jump out
            if (   !is_array($arrAvailable)
                || !isset($arrAvailable['number_total']))
                return '';
            if (self::recordAvailabilityExists($room_type_id, $date)) {
                $result &= self::updateAvailable(
                    $room_type_id, $date,
                    $arrAvailable['number_total'],
                    $arrAvailable['number_booked'],
                    $arrAvailable['number_cancelled'],
                    $arrAvailable['price']);
            } else {
                $result &= self::insertAvailable(
                    $room_type_id, $date,
                    $arrAvailable['number_total'],
                    $arrAvailable['number_booked'],
                    $arrAvailable['number_cancelled'],
                    $arrAvailable['price']);
            }
        }
        return $result;
    }


    /**
     * Updates the available rooms for the given type and date
     * @param   integer   $room_type_id       The room type ID
     * @param   integer   $date               The date
     * @param   integer   $number_total       The total number of rooms
     *                                        available
     * @param   integer   $number_booked      The number of rooms booked
     * @param   integer   $number_cancelled   The number of rooms cancelled
     * @param   integer   $price              The price
     * @return  boolean                       True on success, false otherwise
     * @static
     * @global  ADONewConnection  $objDatabase
     */
    static function updateAvailable(
        $room_type_id, $date, $number_total,
        $number_booked, $number_cancelled, $price
    ) {
        global $objDatabase;

        $query = "
            UPDATE `".DBPREFIX."module_hotelcard_room_available`
               SET `number_total`=$number_total,
                   `number_booked`=$number_booked,
                   `number_cancelled`=$number_cancelled,
                   `price`=$price
             WHERE `room_type_id`=$room_type_id
               AND `date`='$date'";
        $objResult = $objDatabase->Execute($query);
        return (bool)$objResult;
    }


    /**
     * Inserts the available rooms for the given type and date
     * @param   integer   $room_type_id       The room type ID
     * @param   integer   $date               The date
     * @param   integer   $number_total       The total number of rooms
     *                                        available
     * @param   integer   $number_booked      The number of rooms booked
     * @param   integer   $number_cancelled   The number of rooms cancelled
     * @param   integer   $price              The price
     * @return  boolean                       True on success, false otherwise
     * @static
     * @global  ADONewConnection  $objDatabase
     */
    static function insertAvailable(
        $room_type_id, $date, $number_total,
        $number_booked, $number_cancelled, $price
    ) {
        global $objDatabase;

        $query = "
            INSERT INTO `".DBPREFIX."module_hotelcard_room_available` (
              `room_type_id`, `date`,
              `number_total`, `number_booked`, `number_cancelled`,
              `price`
            ) VALUES (
              $room_type_id, '$date',
              $number_total, $number_booked, $number_cancelled,
              $price
            )";
        $objResult = $objDatabase->Execute($query);
        return (bool)$objResult;
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

//echo("HotelRoom::errorHandler(): Entered<br />");

        $arrTables = $objDatabase->MetaTables('TABLES');
        if (in_array(DBPREFIX."module_hotelcard_room_type", $arrTables)) {
            $query = "DROP TABLE `".DBPREFIX."module_hotelcard_room_type`";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
//echo("HotelRoom::errorHandler(): Dropped table ".DBPREFIX."module_hotelcard_room_type<br />");
        }
        $query = "
            CREATE TABLE `".DBPREFIX."module_hotelcard_room_type` (
              `type_text_id` INT UNSIGNED NOT NULL DEFAULT 0,
              `hotel_id` INT UNSIGNED NOT NULL DEFAULT '0',
              `number_default` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Default number of rooms available for this type',
              `price_default` DECIMAL(7,2) UNSIGNED NOT NULL DEFAULT 100.00,
              PRIMARY KEY (`type_text_id`),
              INDEX `room_type_text_id` (`type_text_id` ASC),
              CONSTRAINT `room_type_text_id`
                FOREIGN KEY (`type_text_id`)
                REFERENCES `".DBPREFIX."core_text` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION
            ) ENGINE=MYISAM";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
//echo("HotelRoom::errorHandler(): Created table ".DBPREFIX."module_hotelcard_room_type<br />");


        if (in_array(DBPREFIX."module_hotelcard_room_available", $arrTables)) {
            $query = "DROP TABLE `".DBPREFIX."module_hotelcard_room_available`";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
//echo("HotelRoom::errorHandler(): Dropped table ".DBPREFIX."module_hotelcard_room_available<br />");
        }
        $query = "
            CREATE TABLE `".DBPREFIX."module_hotelcard_room_available` (
              `room_type_id` INT UNSIGNED NOT NULL DEFAULT 0,
              `date` DATE NOT NULL DEFAULT '0000-00-00',
              `number_total` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total number of rooms available for the given date.',
              `number_booked` INT UNSIGNED NOT NULL DEFAULT 0,
              `number_cancelled` INT NOT NULL DEFAULT 0,
              `price` DECIMAL(7,2) UNSIGNED NOT NULL DEFAULT 100.00,
              PRIMARY KEY (`room_type_id`, `date`),
              INDEX `room_available_room_type_id` (`room_type_id` ASC),
              CONSTRAINT `room_available_room_type_id`
                FOREIGN KEY (`room_type_id`)
                REFERENCES `".DBPREFIX."module_hotelcard_room_type` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION
            ) ENGINE=MYISAM";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
//echo("HotelRoom::errorHandler(): Created table ".DBPREFIX."module_hotelcard_room_available<br />");


        if (in_array(DBPREFIX."module_hotelcard_room_facility", $arrTables)) {
            $query = "DROP TABLE `".DBPREFIX."module_hotelcard_room_facility`";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
//echo("HotelRoom::errorHandler(): Dropped table ".DBPREFIX."module_hotelcard_room_facility<br />");
        }
        $query = "
            CREATE TABLE `".DBPREFIX."module_hotelcard_room_facility` (
              `name_text_id` INT UNSIGNED NOT NULL DEFAULT 0,
              `ord` INT UNSIGNED NOT NULL DEFAULT 0,
              PRIMARY KEY (`name_text_id`)
            ) ENGINE=MYISAM";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
//echo("HotelRoom::errorHandler(): Created table ".DBPREFIX."module_hotelcard_room_facility<br />");
        $arrFacility = array(
            'Badewanne',
            'Dusche',
            'Balkon',
            'Klimaanlage',
            'Kühlschrank',
            'Minibar',
            'Radio',
            'Telefon',
            'TV',
            'WC',
            'Weckradio',
            1000 => // The following are not visible in the wizard
            'Etagenbad',
            'Haartrockner',
            'Kaffeemaschine',
            'Küche',
            'Mikrowelle',
        );
        $arrText = Text::getArrayById(
            MODULE_ID, self::TEXT_HOTELCARD_ROOM_FACILITY, FRONTEND_LANG_ID
        );
        foreach ($arrFacility as $ord => $facility) {
            $objTextFound = false;
            foreach ($arrText as $objText) {
                // Do not insert text that is already there
                if ($facility == $objText->getText()) {
                    $objTextFound = $objText;
                    break;
                }
            }
            if ($objTextFound) {
                // Reuse existing text
                $objText = $objTextFound;
            } else {
                // Add missing text
                $objText = new Text(
                    $facility, 1, // German *ONLY*
                    MODULE_ID, self::TEXT_HOTELCARD_ROOM_FACILITY
                );
                if (!$objText->store()) {
// TODO:  Add error message
                    return false;
                }
            }
            $query = "
                INSERT INTO `".DBPREFIX."module_hotelcard_room_facility` (
                  `name_text_id`, `ord`
                ) VALUES (
                  ".$objText->getId().", $ord
                )";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
        }

        if (in_array(DBPREFIX."module_hotelcard_room_type_has_room_facility", $arrTables)) {
            $query = "DROP TABLE `".DBPREFIX."module_hotelcard_room_type_has_room_facility`";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
//echo("HotelRoom::errorHandler(): Dropped table ".DBPREFIX."module_hotelcard_room_type_has_room_facility<br />");
        }
        $query = "
            CREATE TABLE `".DBPREFIX."module_hotelcard_room_type_has_room_facility` (
              `room_type_id` INT UNSIGNED NOT NULL DEFAULT 0,
              `room_facility_id` INT UNSIGNED NOT NULL DEFAULT 0,
              PRIMARY KEY (`room_type_id`, `room_facility_id`),
              INDEX `room_facility_id` (`room_facility_id` ASC),
              INDEX `room_facility_room_type_id` (`room_type_id` ASC),
              CONSTRAINT `room_facility_id`
                FOREIGN KEY (`room_facility_id`)
                REFERENCES `".DBPREFIX."module_hotelcard_room_facility` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION,
              CONSTRAINT `room_facility_room_type_id`
                FOREIGN KEY (`room_type_id`)
                REFERENCES `".DBPREFIX."module_hotelcard_room_type` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION
            ) ENGINE=MYISAM";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
//echo("HotelRoom::errorHandler(): Created table ".DBPREFIX."module_hotelcard_room_type_has_room_facility<br />");

        // More to come...

        // Always!
        return false;
    }

}

?>
