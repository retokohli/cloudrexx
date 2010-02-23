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
    /**
     * Text keys
     */
    const TEXT_HOTELCARD_ROOMTYPE      = 'hotelcard_room_type';
    const TEXT_HOTELCARD_ROOM_FACILITY = 'hotelcard_room_facility';

    /**
     * Lower and upper limits for the selectable number of beds
     */
    const NUMOF_BEDS_MINIMUM = 1;
    const NUMOF_BEDS_MAXIMUM = 6;

    /**
     * Array of hotel room types
     *
     * The array is of the form
     *  array(
     *    room type ID => array(
     *      'id'                 => Room type ID,
     *      'name'               => Type name,
     *      'number_default'     => Default number of rooms available per day,
     *      'price_default'      => Default price per day,
     *      'breakfast_included' => Type name,
     *      'numof_beds'         => Number of beds,
     *      'facilities'         => array (
     *        facility ID => facility name,
     *        ... more ...
     *      'availabilities'     => array(
     *        date => array(
     *          'number_total'     => Number of rooms available that day,
     *          'number_booked'    => Number of rooms booked that day,
     *          'number_cancelled' => Number of rooms cancelled that day,
     *          'price'            => Price for that day,
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
     * Reads records for the given $hotel_id only.
     * The optional $time_from and $time_to may be left out, in which case
     * *NO* availabilities will be included.
     * @param   integer   $hotel_id       The hotel ID
     * @param   integer   $time_from      The optinal start time for the
     *                                    availabilities
     * @param   integer   $time_to        The optinal end time for the
     *                                    availabilities
     * @return  boolean                   True on success, false otherwise
     * @global  ADONewConnection  $objDatabase
     */
    static function init(
        $hotel_id, $time_from=0, $time_to=0)
    {
        global $objDatabase;

//echo("HotelRoom::init(): Entered<br />");

        if (empty($hotel_id)) return false;
        if (empty(self::$arrFacilities)) self::initFacilities();
        self::$arrRoomtypes = array();

        // Room type
        $arrSqlName = Text::getSqlSnippets(
            '`type`.`type_text_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_HOTELCARD_ROOMTYPE
        );
        $query = "
            SELECT `type`.`id`,
                   `type`.`number_default`, `type`.`price_default`,
                   `type`.`breakfast_included`, `type`.`numof_beds`
                   ".$arrSqlName['field']."
              FROM `".DBPREFIX."module_hotelcard_room_type` AS `type`".
                   $arrSqlName['join']."
             WHERE `type`.`hotel_id`=$hotel_id".
//            ($room_type_id ? " AND `type`.`id`=$room_type_id" : '')."
             " ORDER BY `type`.`type_text_id` ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        while (!$objResult->EOF) {
            $room_type_id = $objResult->fields['id'];
            $type_text_id = $objResult->fields['type_text_id'];
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
                'numof_beds'         => $objResult->fields['numof_beds'],
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

            if ($time_from > 0 && $time_to > 0) {
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
        return true;
    }


    /**
     * Initialize the static array of all facilities available
     *
     * Usually only called once by {@see init()}, or directly by
     * {@see getFacilityArray()} when needed.
     * The facilities are sorted by name, ascending.
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
             ORDER BY ".$arrSqlName['text']." ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        self::$arrFacilities = array();
        while (!$objResult->EOF) {
            $facility_id = $objResult->fields['id'];
            $name_text_id = $objResult->fields['name_text_id'];
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
        return self::$arrFacilities;
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
     * It works like {@see init()}, but returns the type array
     * instead of a boolean.
     * @param   integer   $hotel_id       The hotel ID
     * @param   integer   $time_from      The optinal start date for the
     *                                    availabilities
     * @param   integer   $time_to        The optinal end date for the
     *                                    availabilities
     * @return  array                     The room types array on success,
     *                                    false otherwise
     * @static
     */
    static function getTypeArray($hotel_id, $time_from=0, $time_to=0)
    {
        if (empty($hotel_id)) return false;
        self::init($hotel_id, $time_from, $time_to);
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
     * Returns an array with the selectable numbers of beds for the
     * "number of beds" dropdown menu
     *
     * If $selected is zero (or otherwise empty), the "Please select" option
     * will be included as well.
     * @param     mixed   $selected   The optional selected option index
     * @return    array               The number of beds options array
     */
    static function getNumofBedsArray($selected=0)
    {
        global $_ARRAYLANG;

        // The same values as keys and values
        $numof_beds_array = array_combine(
            range(self::NUMOF_BEDS_MINIMUM, self::NUMOF_BEDS_MAXIMUM, 1),
            range(self::NUMOF_BEDS_MINIMUM, self::NUMOF_BEDS_MAXIMUM, 1)
        );
        if ($selected == 0) $numof_beds_array = array(
            0 => $_ARRAYLANG['TXT_HOTELCARD_NUMOF_BEDS_PLEASE_CHOOSE'],
        ) + $numof_beds_array;
        return $numof_beds_array;
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
     * Delete the room facilities for the room type with the given ID
     *
     * If there is no room type for that ID, or if it doesn't have any
     * facilities associated, true is returned anyway.
     * @param   integer   $room_type_id     The room type ID
     * @return  boolean                     True on success, false otherwise
     */
    static function deleteFacilities($room_type_id)
    {
        global $objDatabase;

        $query = "
            DELETE FROM `".DBPREFIX."module_hotelcard_room_type_has_room_facility`
             WHERE `room_type_id`=$room_type_id";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) self::reset();
        return (bool)$objResult;
    }


    /**
     * Deletes the room facility with the given ID
     *
     * Also removes the present relations and Text records.
     * @param   integer     $facility_id    The room facility ID
     * @return  boolean                     True on success, false otherwise
     */
    static function deleteFacilityById($facility_id)
    {
        global $objDatabase;

        // Firstly, remove any relations to existing room types
        if (!self::deleteRelationsByRoomFacilityId($facility_id))
            return false;
        if (empty(self::$arrFacilities)) self::initFacilities();
        if (empty(self::$arrFacilities[$facility_id])) return false;
        // Then, delete the Text and the record itself
        $text_id = self::$arrFacilities[$facility_id]['name_text_id'];
        if (!Text::deleteById($text_id, 0)) return false;
        $query = "
            DELETE FROM `".DBPREFIX."module_hotelcard_room_facility`
             WHERE `id`=$facility_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        return true;
    }


    /**
     * Store the room facilities for the given room type ID
     *
     * The array must contain the facility IDs as keys, and may contain the
     * facility names as values.  The latter are ignored, though.
     * The former facilities are deleted first, then the current ones are
     * added.
     * @param   integer   $room_type_id     The room type ID
     * @param   array     $arrFacility      The room facility array
     * @return  boolean                     True on success, false otherwise
     */
    static function storeFacilities($room_type_id, $arrFacility)
    {
        if (!self::deleteFacilities($room_type_id)) return false;
        foreach (array_keys($arrFacility) as $facility_id) {
            if (!self::addFacility($room_type_id, $facility_id)) return false;
        }
        return true;
    }


    /**
     * Stores a new room facility
     *
     * Adds or replaces the related Text entry, then calls either
     * {@see updateFacility()} or {@see insertFacility()}.
     * Remember to call {@see reset()} when all storing is done, so the
     * static facility data is refreshed on the next access.
     * @param   string    $name           The room facility name
     * @param   integer   $facility_id    The room facility ID, or zero
     * @param   integer   $ord            The optional ordinal value.
     *                                    Defaults to zero
     * @return  boolean                   True on success, false otherwise
     */
    static function storeFacility($name, $facility_id=0, $ord=0)
    {
        if (empty($name)) return false;

        $text_id = 0;
        if ($facility_id) {
            if (empty(self::$arrFacilities)) self::initFacilities();
            if (empty(self::$arrFacilities[$facility_id])) {
                $facility_id = 0;
            } else {
                $text_id = self::$arrFacilities[$facility_id]['name_text_id'];
            }
        }
        $text_id = Text::replace(
            $text_id, FRONTEND_LANG_ID, $name,
                MODULE_ID, self::TEXT_HOTELCARD_ROOM_FACILITY);
        if (!$text_id) return false;
        $ord = intval(empty($ord) || !is_numeric($ord) ? 100 : $ord);
        if ($facility_id)
            return self::updateFacility($text_id, $facility_id, $ord);
        return self::insertFacility($text_id, $ord);
    }


    /**
     * Updates an existing room facility record
     *
     * This does not change the Text record, so you'd better use
     * {@see storeFacility().
     * @param   integer   $text_id        The room facility name Text ID
     * @param   integer   $facility_id    The room facility ID
     * @param   integer   $ord            The ordinal value
     * @return  boolean                   True on success, false otherwise
     */
    static function updateFacility($text_id, $facility_id, $ord)
    {
        global $objDatabase;

        $query = "
            UPDATE `".DBPREFIX."module_hotelcard_room_facility`
               SET `name_text_id`=$text_id,
                   `ord`=$ord
             WHERE `id`=$facility_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        return true;
    }


    /**
     * Inserts a new room facility record
     *
     * This does not add the Text record, so you'd better use
     * {@see storeFacility().
     * @param   integer   $text_id        The room facility name Text ID
     * @param   integer   $ord            The ordinal value
     * @return  boolean                   True on success, false otherwise
     */
    static function insertFacility($text_id, $ord)
    {
        global $objDatabase;

        $query = "
            INSERT INTO `".DBPREFIX."module_hotelcard_room_facility` (
              `name_text_id`, `ord`
            ) VALUES (
              $text_id, $ord
            )";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        return true;
    }


    /**
     * Adds the facility to the room type
     *
     * Mind that this does not add a new facility, but only the relation
     * to the given room type.
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
     * Deletes any relations to existing room types for the given room
     * facility ID.
     *
     * This is called every time a room facility is about to be deleted.
     * @param   integer   $facility_id      The room facility ID
     * @return  boolean                     True on success, false otherwise
     */
    static function deleteRelationsByRoomFacilityId($facility_id)
    {
        global $objDatabase;

        if (empty($facility_id) || !is_numeric($facility_id))
            return false;
        $query = "
            DELETE FROM `".DBPREFIX."module_hotelcard_room_type_has_room_facility`
             WHERE `room_facility_id`=$facility_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        return true;
    }


    /**
     * Update the name of a room type
     *
     * Do this only after calling {@see storeType()}.
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
     * @param   integer   $numof_beds       Number of beds
     * @param   integer   $room_type_id     The optional room type ID
     * @return  integer                     The ID of the record inserted or
     *                                      updated on success, zero otherwise
     * @static
     * @global  ADONewConnection  $objDatabase
     */
    static function storeType(
        $hotel_id, $number_default, $price_default,
        $breakfast_included, $numof_beds, $room_type_id=0
    ) {
        global $objDatabase;

        if (empty($hotel_id)) {
//echo("HotelRoom::storeType(hotel_id $hotel_id, name $name, number_default $number_default, price_default $price_default, room_type_id $room_type_id): Empty Hotel ID<br />");
            return false;
        }
        if (   !self::validateRoomtypeNumber($number_default)
            || !self::validateRoomtypePrice($price_default)
            || $breakfast_included === ''
            || $numof_beds == 0
        ) {
//echo("HotelRoom::storeType(hotel_id $hotel_id, name $name, number_default $number_default, price_default $price_default, room_type_id $room_type_id): Invalid arguments<br />");
            return false;
        }
//echo("HotelRoom::storeType(hotel_id $hotel_id, name $name, number_default $number_default, price_default $price_default, room_type_id $room_type_id): Entered<br />");

        if ($room_type_id && self::recordTypeExists($room_type_id)) {
            return self::updateType(
                $room_type_id, $number_default, $price_default,
                $breakfast_included, $numof_beds);
        }
//echo("Inserting<br />");
        return self::insertType(
            $hotel_id, $number_default, $price_default,
            $breakfast_included, $numof_beds);
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
     * @param   integer   $numof_beds       Number of beds
     * @return  integer                     The ID of the record inserted or
     *                                      updated on success, zero otherwise
     * @static
     * @global  ADONewConnection  $objDatabase
     */
    static function updateType(
        $room_type_id, $number_default, $price_default,
        $breakfast_included, $numof_beds
    ) {
        global $objDatabase;

        if (empty($room_type_id)) return false;
        $query = "
            UPDATE `".DBPREFIX."module_hotelcard_room_type`
               SET `number_default`=$number_default,
                   `price_default`=$price_default,
                   `breakfast_included`=$breakfast_included,
                   `numof_beds`=$numof_beds
             WHERE `id`=$room_type_id";
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
     * @param   integer   $numof_beds       Number of beds
     * @return  integer                     The ID of the record inserted or
     *                                      updated on success, zero otherwise
     * @static
     * @global  ADONewConnection  $objDatabase
     */
    static function insertType(
        $hotel_id, $number_default, $price_default,
        $breakfast_included, $numof_beds
    ) {
        global $objDatabase;

        if (empty($hotel_id)) return false;
        $query = "
            INSERT INTO `".DBPREFIX."module_hotelcard_room_type` (
                `hotel_id`,
                `number_default`, `price_default`,
                `breakfast_included`, `numof_beds`
            ) VALUES (
                $hotel_id,
                $number_default, $price_default,
                ".($breakfast_included ? 1 : 0).",
                $numof_beds
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
        $number = intval($number);
        if ($number < 0) return false;
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
        if ($price <= 0) return false;
//echo("HotelRoom::validateRoomtypePrice(): Price $price is OK<br />");
        return true;
    }


    /**
     * Deletes room types for the given Hotel ID
     *
     * If the optional $room_type_id is specified and not empty, only that
     * room type is deleted.
     * @param   integer   $hotel_id       The Hotel ID
     * @param   integer   $room_type_id   The optional room type ID
     * @return  boolean                   True on success, false otherwise
     */
    static function deleteByHotelId($hotel_id, $room_type_id=0)
    {
        global $objDatabase;

        self::init($hotel_id);
        foreach (self::$arrRoomtypes as $arrRoomtype) {
//echo("HotelRoom::deleteByHotelId($hotel_id): Hotelroom type array:<br />".var_export($arrRoomtype, true)."<hr />");
            $room_type_id_current = $arrRoomtype['id'];
            if ($room_type_id > 0 && $room_type_id != $room_type_id_current)
                continue;
            $query = "
                DELETE FROM `".DBPREFIX."module_hotelcard_room_available`
                 WHERE `room_type_id`=$room_type_id_current";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return self::errorHandler();
            if (!Text::deleteById($arrRoomtype['name_text_id'])) {
//echo("HotelRoom::deleteByHotelId($hotel_id): Failed to delete Text ID ".$arrRoomtype['name_text_id']."<br />");
                return self::errorHandler();
            }
            $query = "
                DELETE FROM `".DBPREFIX."module_hotelcard_room_type`
                 WHERE `id`=$room_type_id_current
                   AND `hotel_id`=$hotel_id";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return self::errorHandler();
        }
        self::reset();
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
            // Set unset values
            if (!isset($arrAvailable['number_total']))
                $arrAvailable['number_total'] = 0;
            if (!isset($arrAvailable['number_booked']))
                $arrAvailable['number_booked'] = 0;
            if (!isset($arrAvailable['number_cancelled']))
                $arrAvailable['number_cancelled'] = 0;
            // Skip invalid values
            if (   intval($arrAvailable['number_total']) < 0
                || intval($arrAvailable['number_booked']) < 0
                || intval($arrAvailable['number_cancelled']) < 0
                || !isset($arrAvailable['price'])
                || $arrAvailable['price'] <= 0)
                continue;
            if (self::recordAvailabilityExists($room_type_id, $date)) {
                $result &= self::updateAvailable(
                    $room_type_id, $date,
                    intval($arrAvailable['number_total']),
                    intval($arrAvailable['number_booked']),
                    intval($arrAvailable['number_cancelled']),
                    $arrAvailable['price']);
            } else {
                $result &= self::insertAvailable(
                    $room_type_id, $date,
                    intval($arrAvailable['number_total']),
                    intval($arrAvailable['number_booked']),
                    intval($arrAvailable['number_cancelled']),
                    $arrAvailable['price']);
            }
        }
        self::reset();
        return $result;
    }


    /**
     * Inserts or updates the available rooms for the given type and date
     *
     * Note that this does an {@see init()} for the given date and thus
     * is not efficient.  Call {@see insertAvailable()} or
     * {@see updateAvailable()} after checking the static data yourself.
     * This is meant to be called by some AJAX driven methods only.
     * @param   integer   $hotel_id           The Hotel ID
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
    static function storeAvailable(
        $hotel_id, $room_type_id, $date,
        $number_total, $number_booked, $number_cancelled, $price
    ) {
        $time = strtotime($date);
        self::init($hotel_id, $time, $time);
        if (empty(self::$arrRoomtypes[$room_type_id]['availabilities'][$date])) {
            return self::insertAvailable($room_type_id, $date,
                $number_total, $number_booked, $number_cancelled, $price);
        }
        return self::updateAvailable($room_type_id, $date,
            $number_total, $number_booked, $number_cancelled, $price);
    }


    /**
     * Updates the available rooms for the given type and date, if necessary
     *
     * If the number of rooms and the price are identical to the defaults
     * for that room type, the existing record is deleted instead,
     * and true is returned.
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

        if (   $number_total == self::$arrRoomtypes[$room_type_id]['number_default']
            && $price == self::$arrRoomtypes[$room_type_id]['price_default']) {
            return self::deleteAvailability($room_type_id, $date);
        }
        $query = "
            UPDATE `".DBPREFIX."module_hotelcard_room_available`
               SET `number_total`=$number_total,
                   `number_booked`=$number_booked,
                   `number_cancelled`=$number_cancelled,
                   `price`=$price
             WHERE `room_type_id`=$room_type_id
               AND `date`='$date'";
        $objResult = $objDatabase->Execute($query);
//        if ($objResult) self::reset();
        return (bool)$objResult;
    }


    /**
     * Inserts the available rooms for the given type and date, if necessary
     *
     * If the number of rooms and the price are identical to the defaults
     * for that room type, nothing is inserted, and true is returned.
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

        if (   $number_total == self::$arrRoomtypes[$room_type_id]['number_default']
            && $price == self::$arrRoomtypes[$room_type_id]['price_default']) {
            return true;
        }
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
//        if ($objResult) self::reset();
        return (bool)$objResult;
    }


    /**
     * Deletes an availability record for the given room type ID and date
     * @param   integer   $room_type_id   The room type ID
     * @param   string    $date           The date
     * @return  boolean                   True on success, false otherwise
     */
    static function deleteAvailability($room_type_id, $date)
    {
        global $objDatabase;

        $query = "
            DELETE FROM `".DBPREFIX."module_hotelcard_room_available`
             WHERE `room_type_id`=$room_type_id
               AND `date`='$date'";
        $objResult = $objDatabase->Execute($query);
        return (bool)$objResult;
    }


    /**
     * DYSFUNCT -- ALWAYS RETURNS TRUE
     *
     * We couldn't find the proper way to implement the requirements so far.
     * TODO:  See if there are enough rooms available
     * Returns true if all availability records in the time range are okay,
     * and the minimum number is reached with the default room numbers for
     * dates missing.
     * Returns false if the default room numbers do not sum up to the minimum.
     * Returns an array of the numbers that are too low, indexed by the
     * respective dates for all availablility records that are too low.
     * This updates the Hotel status according to the result.
     * @param   integer   $hotel_id         The Hotel ID
     * @param   integer   $time_from        The lower bound of the time range
     * @param   integer   $time_to          The upper bound of the time range
     * @return  mixed                       True if everything is okay,
     *                                      false if the defaults are too low,
     *                                      or an array of dates and numbers
     *                                      otherwise
     * @todo    This does not work as required for the time being,
     *          and thus returns true always.
     */
    static function hasMinimumRoomsAvailable(
        $hotel_id, $time_from=0, $time_to=0
    ) {
        global $objDatabase;

        return true;

// TODO FROM HERE

        if (empty($hotel_id)) return false;
        SettingDb::init('admin');
        $number_minimum = SettingDb::getValue('hotel_minimum_rooms_per_day');
        // If the setting is missing or empty, fix it to one
        if (empty($number_minimum)) $number_minimum = 1;
        if (empty(self::$arrRoomtypes)) self::init($hotel_id);
//echo("HotelRoom::hasMinimumRoomsAvailable(): Room types:<br />".var_export(self::$arrRoomtypes, true)."<br />");

        // Get the room types for the defaults, without the availabilities
        self::init($hotel_id, $time_from, $time_to);
        $arrRoomtype_id = array_keys(self::$arrRoomtypes);

        // Ignore the availability records if the time frame is not defined
        if ($time_from > 0 && $time_to > 0) {
            $roomtype_ids = join(',', array_keys(self::$arrRoomtypes));
            // Find all availability records for all room types of this hotel
            // where there are not enough rooms in single room types
            $query = "
                SELECT `room_type_id`, `date`, `number_total`
                  FROM `".DBPREFIX."module_hotelcard_room_available` AS `availability`
                 WHERE `room_type_id` IN ($roomtype_ids)
                   AND `date`>='".date('Y-m-d', $time_from)."'
                   AND `date`<='".date('Y-m-d', $time_to  )."'
                HAVING `number_total`<$number_minimum
                 ORDER BY `date` ASC";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return self::errorHandler();
            if (!$objResult->EOF) {
                Hotel::updateStatus($hotel_id, -Hotel::STATUS_AVAILABLE);
                $arrDates = array();
                while (!$objResult->EOF) {
                    $room_type_id = $objResult->fields['room_type_id'];
                    $date = $objResult->fields['date'];
                    $number_total = $objResult->fields['number_total'];
//echo("Too low: id $room_type_id, date $date => number_total $number_total<br />");
                    $arrDates[$date][$room_type_id] = $number_total;
                    $objResult->MoveNext();
                }
//echo("HotelRoom::hasMinimumRoomsAvailable($hotel_id, $time_from, $time_to): Room numbers too low for dates: ".var_export($arrDates, true)."<br />");
                foreach ($arrDates as $date => $arrNumbers) {
                    $number_total = 0;
                    foreach ($arrRoomtype_id as $room_type_id) {
                        $number_default =
                            self::$arrRoomtypes[$room_type_id]['number_default'];
                  	     if (!isset($arrNumbers[$room_type_id])) {
                  	         if ($number_default >= $number_minimum) {
//echo("HotelRoom::hasMinimumRoomsAvailable(): Date $date, room type id $room_type_id covers $number_default &gt;= $number_minimum rooms<br />");
                  	             unset($arrDates[$date]);
                  	             continue 2;
                  	         }
                  	         $arrNumbers[$room_type_id] = $number_default;
              	         }
          	             $number_total += $arrNumbers[$room_type_id];
                    }
                    if ($number_total >= $number_minimum) {
//echo("HotelRoom::hasMinimumRoomsAvailable(): Date $date, total covers $number_total &gt;= $number_minimum rooms<br />");
          	            unset($arrDates[$date]);
          	            continue;
                    }
//echo("HotelRoom::hasMinimumRoomsAvailable(): Date $date, total of $number_total does not cover $number_minimum rooms<br />");
                }
                if ($arrDates) return $arrDates;
            }
        }

        // See if the default numbers cover the days for which
        // there are no records
        $number = 0;
        foreach (self::$arrRoomtypes as $arrRoomtype) {
//echo("HotelRoom::hasMinimumRoomsAvailable(): Room type ID $room_type_id: default number ".$arrRoomtype['number_default']."<br />");
            $number += $arrRoomtype['number_default'];
        }
        if ($number < $number_minimum) {
//echo("HotelRoom::hasMinimumRoomsAvailable($hotel_id, $time_from, $time_to): Room number defaults (total $number) too low (at least $number_minimum)<br />");
            Hotel::updateStatus($hotel_id, -Hotel::STATUS_AVAILABLE);
            return false;
        }
        Hotel::updateStatus($hotel_id, Hotel::STATUS_AVAILABLE);
        return true;
    }


    /**
     * Process the AJAX request fired when a toggle element is clicked
     *
     * Replies with a JSON object.  See {@see getAvailabilityJson()}
     * for details.
     * @param   integer   $hotel_id       The Hotel ID
     * @param   integer   $room_type_id   The room type ID
     * @param   string    $date           The affected date
     * @param   string    $status         The current status of the element
     * @param   string    $status         The optional new room number
     * @return  string                    The JSON response
     */
    static function ajaxToggleAvailability(
        $hotel_id, $room_type_id, $date, $status, $number=0
    ) {

//DBG::log("ajaxToggleAvailability($hotel_id, $room_type_id, $date, $status, $number)");

        $time = strtotime($date);
        self::init($hotel_id, $time, $time);

        $arrRoomtype_ids = false;
        if ($room_type_id) {
            $arrRoomtype_ids = array($room_type_id);
        } else {
            $arrRoomtype_ids = array_keys(self::$arrRoomtypes);
        }
        foreach ($arrRoomtype_ids as $id) {
            $numof_rooms = ($status
              // If some rooms have been available for that day, turn them off
              ? 0
              // Otherwise, make some rooms available.
              // Use either the parameter value, the value already in the
              // database, or the default.
              : ($room_type_id && $number
                  ? $number
                  : (empty(self::$arrRoomtypes[$id]['availabilities'][$date]['number_total'])
                      ? self::$arrRoomtypes[$id]['number_default']
                      : self::$arrRoomtypes[$id]['availabilities'][$date]['number_total']
                    )
                )
            );

            if (empty(self::$arrRoomtypes[$id]['availabilities'][$date])) {
                self::insertAvailable($id, $date,
                    $numof_rooms, 0, 0,
                    self::$arrRoomtypes[$id]['price_default']);
            } else {
                self::updateAvailable($id, $date,
                    $numof_rooms, 0, 0,
                    self::$arrRoomtypes[$id]['availabilities'][$date]['price']);
            }
        }
        return self::getAvailabilityJson($hotel_id, $date); //, $room_type_id);
    }


    /**
     * Process the AJAX request fired when an input element is changed
     *
     * Replies with a JSON object.  See {@see getAvailabilityJson()}
     * for details.
     * @param   integer   $hotel_id       The Hotel ID
     * @param   integer   $room_type_id   The room type ID
     * @param   string    $date           The affected date
     * @param   string    $number_total   The new number of rooms
     * @param   string    $price          The new price
     * @return  string                    The JSON response
     */
    static function ajaxChangeAvailability(
        $hotel_id, $room_type_id, $date, $number_total, $price
    ) {
        self::storeAvailable($hotel_id, $room_type_id, $date,
            $number_total, 0, 0, $price);
        return self::getAvailabilityJson($hotel_id, $date); //, $room_type_id);
    }


    /**
     * Returns the JSON encoded array for the availability of
     * one or all room types for the given Hotel ID
     *
     * Note that in the JSON string returned, all dashes (-) in the date
     * are replaced by underscores already, so they can be used as
     * part of the id attribute.
     * The JSON object has the form
     *  array(
     *    index => array(
     *      'date' => the date (with dashes (-) replaced by underscores),
     *      'room_type_id' => the room type ID,
     *      'number_total' => the total number of available rooms,
     *      'price' => the room price,
     *    );
     *    ... more ...
     *  )
     * @param   integer   $hotel_id       The Hotel ID
     * @param   integer   $time           The affected time
     * //@param   integer   $room_type_id   The Room type ID, or zero for all
     * @return  string                    The JSON response
     */
    static function getAvailabilityJson($hotel_id, $date) //, $room_type_id)
    {
        $time = strtotime($date);
        self::init($hotel_id, $time, $time);
        $_date = preg_replace('/[-]/', '_', $date);
        $response = array();
        foreach (self::$arrRoomtypes as $id => $arrRoomtype) {
            // If a single room type is given, exclude all other ones
            //if ($room_type_id && $room_type_id != $id) continue;
            $response[] = array(
                'date' => $_date,
                'room_type_id' => $id,
                'number_total' =>
                    (isset($arrRoomtype['availabilities'][$date])
                      ? $arrRoomtype['availabilities'][$date]['number_total']
                      : $arrRoomtype['number_default']),
                'price' =>
                    (isset($arrRoomtype['availabilities'][$date])
                      ? $arrRoomtype['availabilities'][$date]['price']
                      : $arrRoomtype['price_default']),
            );
        }
        $response = json_encode(
            $response
            //, JSON_FORCE_OBJECT
        );
        return $response;
    }



// TODO
    /**
     * Returns an array of SQL snippets to include in the Hotel or Room
     * queries in order to find a certain price range
     *
     * Includes the dates equal to or greater than $date_from, but smaller
     * than $date_to (the check out date).
     * The array returned looks as follows:
     *  array(
     *    'room_price' => Price field alias, like "room_#_price"
     *    'field'      => Field snippet to be included in the SQL SELECT, uses
     *                    an aliased name for the price field
     *                    Note that a leading comma is already included!
     *    'where'      => SQL WHERE snippet, starting with " AND", then some
     *                    conditions
     *  )
     * The '#' is replaced by a unique integer number.
     * Any of $price_min, $price_max, and $alias may be left out.
     * Missing prices will be ignored, the alias will be set automatically
     * if absent.
     * @static
     * @param   string    $field_id_name  The name of the text ID
     *                                    foreign key field
     * @param   string    $date_from      Start date, in mySQL DATE format
     * @param   string    $date_to        End date, in mySQL DATE format
     * @param   float     $price_min      The optional minimum room price
     * @param   float     $price_max      The optional maximum room price
     * @param   string    $alias          The optional text field alias
     * @return  array                     The array with SQL code parts
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getSqlSnippets_price(
        $field_hotel_id_name, $date_from, $date_to,
        $price_min=null, $price_max=null, $alias=''
    ) {
        static $table_alias_index = 0;

        if (empty($field_hotel_id_name)) return false;
        $table_alias_room = 'room_'.++$table_alias_index;
        $table_alias_availability = 'availability_'.++$table_alias_index;
        $field_price = ($alias ? $alias : $table_alias_room.'_price');

        $query_field = "
            MIN(IFNULL(`$table_alias_availability`.`price`,
                `$table_alias_room`.`price_default`)) AS `$field_price`";
        $query_join = "
              LEFT JOIN `".DBPREFIX."module_hotelcard_room_type` AS `$table_alias_room`
                ON `$table_alias_room`.`hotel_id`=$field_hotel_id_name
              LEFT JOIN `".DBPREFIX."module_hotelcard_room_available` AS `$table_alias_availability`
                ON `$table_alias_availability`.`room_type_id`=`$table_alias_room`.`id`";
//        $query_where = "
//               AND (   (    (   `$table_alias_availability`.`price` IS NULL
//                             OR `$table_alias_availability`.`price`=0)
//                        AND `$table_alias_room`.`price_default`>0)
//                    OR (    (   `$table_alias_room`.`price_default` IS NULL
//                             OR `$table_alias_room`.`price_default`=0)
//                        AND `$table_alias_availability`.`price`>0
//                       )
//                   )
//               AND (    `$table_alias_availability`.`date` IS NULL
//                    OR  `$table_alias_availability`.`date`>='$date_from' AND `date`<'$date_to')".
        $query_where = "
               AND (   `$table_alias_room`.`price_default`>0
                    OR `$table_alias_availability`.`price`>0)
               AND (   (    `$table_alias_availability`.`date`>='$date_from'
                        AND `$table_alias_availability`.`date`<'$date_to'
                        AND `$table_alias_availability`.`number_total`>0)
                    OR (    `$table_alias_availability`.`date` IS NULL
                        AND `$table_alias_room`.`number_default`>0)
               )
            ".
            ($price_min === null ? '' : " AND `$field_price`>=$price_min").
            ($price_max === null ? '' : " AND `$field_price`<=$price_max");

        return array(
            'room_price' => $field_price,
            'field'      => ", $query_field",
            'join'       => $query_join,
            'where'      => $query_where,
        );
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
              `numof_beds` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
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
