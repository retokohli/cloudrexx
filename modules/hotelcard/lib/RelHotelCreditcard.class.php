<?php

/**
 * Hotelcard to Creditcard relation class
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
 * Hotelcard to Creditcard relation class
 * @version     2.2.0
 * @since       2.2.0
 * @package     contrexx
 * @subpackage  module_hotelcard
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */
class RelHotelCreditcard
{
    /**
     * Array of hotel to creditcard relations
     *
     * The array is of the form
     *  array(
     *    hotel ID => array(
     *      creditcard ID,
     *      ... more ...
     *    ),
     *    ... more ...
     *  )
     * The creditcard IDs are sorted by their ordinal values.
     * @see     __construct
     * @var     array
     * @access  private
     */
    private $arrRelations = false;


    /**
     * Construct the hotel to creditcard relation
     *
     * Initializes all the data from the database
     * @param   string  $arrHotel_id        Optional comma separated list of
     *                                      Hotel IDs, or an empty value
     * @param   string  $arrCreditcard_id   Optional comma separated list of
     *                                      Creditcard IDs, or an empty value
     * @global  ADONewConnection  $objDatabase
     */
    function __construct($arrHotel_id=false, $arrCreditcard_id=false)
    {
        global $objDatabase;

        $query = "
            SELECT `hotel_id`, `creditcard_id`
              FROM `".DBPREFIX."module_hotelcard_hotel_accepts_creditcard`
             INNER JOIN `".DBPREFIX."core_creditcard`
                ON `creditcard_id`=`id`
             WHERE 1".
              (empty($arrHotel_id)
                ? ''
                : ' AND `hotel_id` IN ('.join(',', $arrHotel_id).')').
              (empty($arrCreditcard_id)
                ? ''
                : ' AND `creditcard_id` IN ('.join(',', $arrHotel_id).')')."
             ORDER BY `ord` ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return;
        $this->arrRelations = array();
        while (!$objResult->EOF) {
            $hotel_id      = $objResult->fields['hotel_id'];
            $creditcard_id = $objResult->fields['creditcard_id'];
            if (empty($this->arrRelations[$hotel_id]))
                $this->arrRelations[$hotel_id] = array();
            $this->arrRelations[$hotel_id][] = $creditcard_id;
            $objResult->MoveNext();
        }
    }


    /**
     * Stores the list of creditcard IDs for the given Hotel ID
     *
     * Removes any relations that are not present in the list.
     * If the creditcard ID array is empty, all relations are deleted!
     * If the Hotel ID is empty, this method will fail and not change anything.
     * @param   integer $hotel_id           The Hotel ID
     * @param   array   $arrCreditcard_id   Array of creditcard IDs
     * @return  boolean                     True on success, false otherwise
     * @static
     * @global  ADONewConnection  $objDatabase
     */
    static function store($hotel_id, $arrCreditcard_id)
    {
        global $objDatabase;

        if (empty($hotel_id)) return false;

        // Delete any existing relations for that Hotel ID first
        $query = "
            DELETE FROM `".DBPREFIX."module_hotelcard_hotel_accepts_creditcard`
             WHERE `hotel_id`=$hotel_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;

        // Add any creditcard IDs from the array
        $result = true;
        foreach ($arrCreditcard_id as $creditcard_id) {
            $query = "
                INSERT INTO `".DBPREFIX."module_hotelcard_hotel_accepts_creditcard` (
                  `hotel_id`, `creditcard_id`
                ) VALUES (
                  $hotel_id, $creditcard_id
                )";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) $result = false;
        }
        return $result;
    }


    /**
     * Returns the array of all relations
     * @return  array               The relations array on success,
     *                              false otherwise
     */
    function getArray()
    {
        return $this->arrRelations;
    }


    /**
     * Returns all the creditcard IDs present in the object for the
     * given hotel ID
     *
     * If a creditcard with the given ID is not there,
     * returns the empty array.
     * Mind that the result depends on the parameters that you used when
     * you created that object!
     * @param   integer   $hotel      The hotel ID
     * @return  array                 The array of creditcard IDs,
     *                                or the empty array
     */
    function getCreditcardIdsByHotelId($hotel_id)
    {
        return (isset($this->arrRelations[$hotel_id])
            ? $this->arrRelations[$hotel_id]
            : array()
        );
    }


    /**
     * Tries to fix or recreate the database table(s) for the class
     *
     * Should be called whenever there's a problem with the database table.
     * @return  boolean             False.  Always.
     */
    function errorHandler()
    {
        global $objDatabase;

die("RelHotelCreditcard::errorHandler(): Disabled!<br />");

        $arrTables = $objDatabase->MetaTables('TABLES');
        if (!in_array(DBPREFIX."module_hotelcard_hotel_accepts_creditcard", $arrTables)) {
            $query = "
                CREATE TABLE `".DBPREFIX."module_hotelcard_hotel_accepts_creditcard` (
                  `hotel_id` INT UNSIGNED NOT NULL DEFAULT 0,
                  `creditcard_id` INT UNSIGNED NOT NULL DEFAULT 0,
                  PRIMARY KEY (`hotel_id`, `creditcard_id`),
                  INDEX `hotel_id` (`hotel_id` ASC),
                  INDEX `creditcard_id` (`creditcard_id` ASC),
                  CONSTRAINT `hotel_id`
                    FOREIGN KEY (`hotel_id` )
                    REFERENCES `".DBPREFIX."module_hotelcard_hotel` (`id`)
                    ON DELETE NO ACTION
                    ON UPDATE NO ACTION,
                  CONSTRAINT `creditcard_id`
                    FOREIGN KEY (`creditcard_id`)
                    REFERENCES `".DBPREFIX."core_creditcard` (`id`)
                    ON DELETE NO ACTION
                    ON UPDATE NO ACTION
                ) ENGINE=MYISAM";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
//echo("RelHotelCreditcard::errorHandler(): Created table ".DBPREFIX."module_hotelcard_hotel_accepts_creditcard<br />");
        }

        // More to come...

        // Always!
        return false;
    }

}

?>
