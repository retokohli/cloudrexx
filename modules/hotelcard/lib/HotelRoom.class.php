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
    const TEXT_HOTELCARD_ROOMTYPE = 'hotelcard_room_type';
    const TEXT_HOTELCARD_ROOM_FACILITY = 'hotelcard_room_facility';

    /**
     * Array of hotel room types
     *
     * The array is of the form
     *  array(
     *    room type ID => array(
     *      'id'                 => room type ID,
     *      'name'               => type name,
     *      'number_default'     => default number of rooms available per day,
     *      'price_default'      => default price per day,
     *      'breakfast_included' => type name,
     *      'facilities'         => array (
     *        facility ID => facility name,
     *        ... more ...
     *      'availabilities'     => array(
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
     * The optional $time_from and $time_to may be left out, in which case
     * *NO* availabilities will be included.
     * @param   integer   $hotel_id       The hotel ID
     * @param   integer   $room_type_id   The optional room type ID
     * @param   integer   $time_from      The optinal start time for the
     *                                    availabilities
     * @param   integer   $time_to        The optinal end time for the
     *                                    availabilities
     * @return  boolean                   True on success, false otherwise
     * @global  ADONewConnection  $objDatabase
     */
    static function init(
        $hotel_id, $room_type_id=0, $time_from='', $time_to='')
    {
        global $objDatabase;

//echo("HotelRoom::init(): Entered<br />");

        if (empty($hotel_id)) return false;
        if (empty(self::$arrFacilities)) self::initFacilities();

        // Flush previous types that belong to a different hotel
        if (   empty(self::$arrRoomtypes)
            || $hotel_id != self::$hotel_id)
            self::$arrRoomtypes = array();

        // Room type
        $arrSqlName = Text::getSqlSnippets(
            '`type`.`type_text_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_HOTELCARD_ROOMTYPE
        );
        $query = "
            SELECT `type`.`id`,
                   `type`.`number_default`, `type`.`price_default`,
                   `type`.`breakfast_included`
                   ".$arrSqlName['field']."
              FROM `".DBPREFIX."module_hotelcard_room_type` AS `type`".
                   $arrSqlName['join']."
             WHERE `type`.`hotel_id`=$hotel_id".
            ($room_type_id ? " AND `type`.`id`=$room_type_id" : '')."
             ORDER BY `type`.`type_text_id` ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        while (!$objResult->EOF) {
            $room_type_id = $objResult->fields['id'];
            $type_text_id = $objResult->fields[$arrSqlName['id']];
            $strName = $objResult->fields[$arrSqlName['text']];
            if ($strName === null) {
                $objText = Text::getById($type_text_id, 0);
                if ($objText) $strName = $objText->getText();
            }
            self::$arrRoomtypes[$room_type_id] = array(
                'id'                 => $room_type_id,
                'name'               => $strName,
                'name_text_id'       => $type_text_id,
                'number_default'     => $objResult->fields['number_default'],
                'price_default'      => $objResult->fields['price_default'],
                'breakfast_included' => $objResult->fields['breakfast_included'],
                'facilities'         => array(),
                'availabilities'     => array(),
            );
            $objResult->MoveNext();
        }

        foreach (self::$arrRoomtypes as $room_type_id => &$arrRoomtype) {
            // Facility for each room type
            $query = "
                SELECT `room_facility_id`
                  FROM `".DBPREFIX."module_hotelcard_room_facility` AS `facility`
                 INNER JOIN `".DBPREFIX."module_hotelcard_room_type_has_room_facility` AS `relation`
                    ON `facility`.`id`=`relation`.`room_facility_id`
                 WHERE `relation`.`room_type_id`=$room_type_id
                 ORDER BY `facility`.`ord` ASC";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return self::errorHandler();
            while (!$objResult->EOF) {
                $facility_id = $objResult->fields['room_facility_id'];
                $strName = self::$arrFacilities[$facility_id]['name'];
                $arrRoomtype['facilities'][$facility_id] = $strName;
//echo("HotelRoom::init(): added facility ID $facility_id: $strName<br />");
                $objResult->MoveNext();
            }

            if ($time_from !== '' && $time_to !== '') {
                // Availability for each room type
                $query = "
                    SELECT `availability`.`date`,
                           `availability`.`number_total`,
                           `availability`.`number_booked`,
                           `availability`.`number_cancelled`,
                           `availability`.`price`
                      FROM `".DBPREFIX."module_hotelcard_room_available` AS `availability`
                     WHERE `availability`.`room_type_id`=$room_type_id".
                      ($time_from === ''
                        ? ''
                        : " AND `availability`.`date`>='".date('Y-m-d', $time_from)."'").
                      ($time_to === ''
                        ? ''
                        : " AND `availability`.`date`<='".date('Y-m-d', $time_to  )."'")."
                     ORDER BY `availability`.`date` ASC";
                $objResult = $objDatabase->Execute($query);
                if (!$objResult) return self::errorHandler();
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
        }
        // Remember the previous Hotel ID
        self::$hotel_id = $hotel_id;
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
            SELECT `facility`.`id`, `facility`.`ord` ".$arrSqlName['field']."
              FROM `".DBPREFIX."module_hotelcard_room_facility` AS `facility`".
                   $arrSqlName['join']."
             ORDER BY `facility`.`ord` ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        self::$arrFacilities = array();
        while (!$objResult->EOF) {
            $facility_id = $objResult->fields['id'];
            $name_text_id = $objResult->fields[$arrSqlName['id']];
            $strName = $objResult->fields[$arrSqlName['text']];
            if ($strName === null) {
                $objText = Text::getById($name_text_id, 0);
                if ($objText) $strName = $objText->getText();
            }
            self::$arrFacilities[$facility_id] = array(
                'id'           => $facility_id,
                'name'         => $strName,
                'name_text_id' => $name_text_id,
                'ord'          => $objResult->fields['ord'],
            );
            $objResult->MoveNext();
        }
//echo("HotelRoom::initFacilities(): Made ".var_export(self::$arrFacilities, true)."<hr />");
        return true;
    }


    /**
     * Clear the data stored in the class
     *
     * Forces the class to re-init() the data on the next access
     * @static
     */
    static function reset()
    {
        self::$arrFacilities = false;
        self::$arrRoomtypes = false;
        self::$hotel_id = false;
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
     * @param   integer   $time_from      The optinal start date for the
     *                                    availabilities
     * @param   integer   $time_to        The optinal end date for the
     *                                    availabilities
     * @return  array                     The room types array on success,
     *                                    false otherwise
     * @static
     */
    static function getTypeArray(
        $hotel_id=0, $room_type_id=0, $time_from='', $time_to='')
    {
        if (empty($hotel_id)) $hotel_id = self::$hotel_id;
        if (   ($room_type_id && empty(self::$arrRoomtypes[$room_type_id]))
            || empty(self::$arrRoomtypes))
            self::init($hotel_id, $room_type_id, $time_from, $time_to);
        return self::$arrRoomtypes;
    }


    /**
     * Returns an array with options available for the "breakfast included"
     * dropdown menu
     *
     * If $selected is NULL or the empty string, the "Please select" option
     * will be included as well.
     * @param     mixed   $selected   The optional selected option index
     * @return    array               The breakfast included options array
     */
    static function getBreakfastIncludedArray($selected='')
    {
        global $_ARRAYLANG;

        $breakfast_included_array = array(
             1 => $_ARRAYLANG['TXT_HOTELCARD_BREAKFAST_INCLUDED_YES'],
             0 => $_ARRAYLANG['TXT_HOTELCARD_BREAKFAST_INCLUDED_NO'],
        );
        if ($selected === '' || $selected === NULL) $breakfast_included_array = array(
            '' => $_ARRAYLANG['TXT_HOTELCARD_BREAKFAST_INCLUDED_PLEASE_CHOOSE'],
        ) + $breakfast_included_array;
        return $breakfast_included_array;
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
             WHERE `id`=$room_type_id";
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
        if ($objResult) self::reset();
        return (bool)$objResult;
    }


    /**
     * Store the name of a room type
     *
     * Do this only after store()ing it.
     * @param   integer   $room_type_id   The room type ID
     * @param   string    $name           The room type name
     * @return  boolean                   True on success, false otherwise
     */
    static function renameType($room_type_id, $name)
    {
        global $objDatabase;

        if (!$room_type_id || !self::recordTypeExists($room_type_id))
            return false;
        if (!self::validateRoomtypeName($name))
            return false;
        $query = "
            SELECT `type_text_id`
              FROM `".DBPREFIX."module_hotelcard_room_type`
             WHERE `id`=$room_type_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        $type_text_id = $objResult->fields['type_text_id'];
        $type_text_id = Text::replace(
            $type_text_id, FRONTEND_LANG_ID, $name,
            MODULE_ID, self::TEXT_HOTELCARD_ROOMTYPE);
        if (!$type_text_id) {
//echo("HotelRoom::storeType(): Failed to replace Text ID $type_text_id for name $name<br />");
            return false;
        }
        $query = "
            UPDATE `".DBPREFIX."module_hotelcard_room_type`
               SET `type_text_id`=$type_text_id
             WHERE `id`=$room_type_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        self::reset();
        return true;
    }


    /**
     * Stores a room type
     *
     * Updates the room type if it exists, otherwise inserts it.
     * This method fails if the name or hotel ID given is empty.
     * Affects the current frontend language as specified by FRONTEND_LANG_ID.
     * @param   integer   $hotel_id         The hotel ID
     * @param   integer   $number_default   The default number of rooms per day
     * @param   integer   $price_default    The default price per day
     * @param   integer   $breakfast_included   Breakfast is included if true
     * @param   integer   $room_type_id     The optional room type ID
     * @return  integer                     The ID of the record inserted or
     *                                      updated on success, zero otherwise
     * @static
     * @global  ADONewConnection  $objDatabase
     */
    static function storeType(
        $hotel_id, $number_default, $price_default,
        $breakfast_included, $room_type_id=0
    ) {
        global $objDatabase;

        if (empty($hotel_id)) {
//echo("HotelRoom::storeType(hotel_id $hotel_id, name $name, number_default $number_default, price_default $price_default, room_type_id $room_type_id): Empty Hotel ID<br />");
            return false;
        }
        if (   !self::validateRoomtypeNumber($number_default)
            || !self::validateRoomtypePrice($price_default)) {
//echo("HotelRoom::storeType(hotel_id $hotel_id, name $name, number_default $number_default, price_default $price_default, room_type_id $room_type_id): Invalid arguments<br />");
            return false;
        }
//echo("HotelRoom::storeType(hotel_id $hotel_id, name $name, number_default $number_default, price_default $price_default, room_type_id $room_type_id): Entered<br />");

        if ($room_type_id && self::recordTypeExists($room_type_id)) {
            return self::updateType(
                $room_type_id, $number_default,
                $price_default, $breakfast_included);
        }
//echo("Insertting<br />");
        return self::insertType(
            $hotel_id, $number_default, $price_default, $breakfast_included);
    }


    /**
     * Updates a room type
     *
     * Mind that the related Text record is stored in {@see renameType()}
     * and is not affected here.
     * @param   integer   $room_type_id     The room type ID
     * @param   integer   $number_default   The default number of rooms per day
     * @param   integer   $price_default    The default price per day
     * @param   integer   $breakfast_included   Breakfast is included in the
     *                                      price if true
     * @return  integer                     The ID of the record inserted or
     *                                      updated on success, zero otherwise
     * @static
     * @global  ADONewConnection  $objDatabase
     */
    static function updateType(
        $room_type_id, $number_default, $price_default, $breakfast_included
    ) {
        global $objDatabase;

        if (empty($room_type_id)) return false;
        $query = "
            UPDATE `".DBPREFIX."module_hotelcard_room_type`
               SET `number_default`=$number_default,
                   `price_default`=$price_default,
                   `breakfast_included`=$breakfast_included
             WHERE `type_text_id`=$room_type_id";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) self::reset();
        return ($objResult ? $room_type_id : 0);
    }


    /**
     * Inserts a room type
     *
     * Mind that the related Text record is stored in {@see renameType()}
     * and is not affected here.
     * @param   integer   $hotel_id         The hotel ID
     * @param   integer   $number_default   The default number of rooms per day
     * @param   integer   $price_default    The default price per day
     * @param   integer   $breakfast_included   Breakfast is included in the
     *                                      price if true
     * @return  integer                     The ID of the record inserted or
     *                                      updated on success, zero otherwise
     * @static
     * @global  ADONewConnection  $objDatabase
     */
    static function insertType(
        $hotel_id, $number_default, $price_default, $breakfast_included
    ) {
        global $objDatabase;

        if (empty($hotel_id)) return false;
        $query = "
            INSERT INTO `".DBPREFIX."module_hotelcard_room_type` (
                `hotel_id`,
                `number_default`, `price_default`,
                `breakfast_included`
            ) VALUES (
                $hotel_id,
                $number_default, $price_default,
                ".($breakfast_included ? 1 : 0)."
            )";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return 0;
        self::reset();
        return $objDatabase->Insert_ID();
    }


    /**
     * Validate the room type name
     *
     * If the name argument given by reference contains any HTML-like tags
     * they are removed, as are leading and trailing whitespace.
     * False is returned if the result is empty.
     * @param   string    $name             The room type name, by reference
     * @return  boolean                     True if the name is valid,
     *                                      false otherwise
     */
    static function validateRoomtypeName(&$name)
    {
        $name = trim(strip_tags($name));
//echo("HotelRoom::validateRoomtypeName(): Fixed name to $name<br />");
        if (empty($name)) {
//            $name = '';
            return false;
        }
        return true;
    }


    /**
     * Validate the number of available rooms
     *
     * Fixes the value given by reference to an integer.
     * Returns false if a number smaller than one (1) results.
     * @param   integer   $number           The number of rooms available,
     *                                      by reference.
     *                                      Must be one or greater.
     * @return  boolean                     True if the number is valid,
     *                                      false otherwise
     */
    static function validateRoomtypeNumber(&$number)
    {
        if (intval($number) < 1) {
            return false;
        }
        return true;
    }


    /**
     * Validate the room price
     *
     * Formats the price given by reference as a double with two digits after
     * the decimal point.
     * Returns false if the resulting price is invalid.
     * @param   double    $price            The price, by reference.
     *                                      Must be greater than zero.
     * @return  boolean                     True if the price is valid,
     *                                      false otherwise
     */
    static function validateRoomtypePrice(&$price) {
        $price = number_format($price, 2, '.', '');
//echo("HotelRoom::validateRoomtypePrice(): Fixed price to $price<br />");
        if ($price <= 0) {
            return false;
        }
//echo("HotelRoom::validateRoomtypePrice(): Price $price is OK<br />");
        return true;
    }


    static function deleteByHotelId($hotel_id)
    {
        global $objDatabase;

        self::init($hotel_id);
        foreach (self::$arrRoomtypes as $arrRoomtype) {
//echo("HotelRoom::deleteByHotelId($hotel_id): Hotelroom type array:<br />".var_export($arrRoomtype, true)."<hr />");
            $room_type_id = $arrRoomtype['id'];
            $query = "
                DELETE FROM `".DBPREFIX."module_hotelcard_room_available`
                 WHERE `room_type_id`=$room_type_id";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return self::errorHandler();
            self::reset();
            if (!Text::deleteById($arrRoomtype['name_text_id'])) {
//echo("HotelRoom::deleteByHotelId($hotel_id): Failed to delete Text ID ".$arrRoomtype['name_text_id']."<br />");
                return self::errorHandler();
            }
        }
        $query = "
            DELETE FROM `".DBPREFIX."module_hotelcard_room_type`
             WHERE `hotel_id`=$hotel_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        return true;
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
        self::reset();
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
        if ($objResult) self::reset();
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
        if ($objResult) self::reset();
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

die("HotelRoom::errorHandler(): Disabled!<br />");

        $arrTables = $objDatabase->MetaTables('TABLES');
        if (in_array(DBPREFIX."module_hotelcard_room_type", $arrTables)) {
            $query = "DROP TABLE `".DBPREFIX."module_hotelcard_room_type`";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
//echo("HotelRoom::errorHandler(): Dropped table ".DBPREFIX."module_hotelcard_room_type<br />");
        }
        $query = "
            CREATE TABLE `".DBPREFIX."module_hotelcard_room_type` (
              `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `type_text_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
              `hotel_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
              `number_default` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Default number of rooms available for this type',
              `price_default` DECIMAL(7,2) UNSIGNED NOT NULL DEFAULT 100.00,
              `breakfast_included` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              INDEX `room_type_text_id` (`type_text_id` ASC)
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
              `room_type_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
              `date` DATE NOT NULL DEFAULT '0000-00-00',
              `number_total` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total number of rooms available for the given date.',
              `number_booked` INT(10) UNSIGNED NOT NULL DEFAULT 0,
              `number_cancelled` INT(10) NOT NULL DEFAULT 0,
              `price` DECIMAL(7,2) UNSIGNED NOT NULL DEFAULT 100.00,
              PRIMARY KEY (`room_type_id`, `date`)
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
              `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `name_text_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
              `ord` INT(10) UNSIGNED NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`)
            ) ENGINE=MYISAM";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
//echo("HotelRoom::errorHandler(): Created table ".DBPREFIX."module_hotelcard_room_facility<br />");

        Text::deleteByKey(self::TEXT_HOTELCARD_ROOM_FACILITY);

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
        foreach ($arrFacility as $ord => $facility) {
            $objText = new Text(
                $facility, 1, // German *ONLY*
                MODULE_ID, self::TEXT_HOTELCARD_ROOM_FACILITY
            );
            if (!$objText->store()) {
die("HotelRoom::errorHandler(): Failed to store Text for room facility $facility<br />");
                return false;
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
              `room_type_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
              `room_facility_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
              PRIMARY KEY (`room_type_id`, `room_facility_id`)
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
