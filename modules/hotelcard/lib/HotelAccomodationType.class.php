<?php

/**
 * Hotel Accomodation Type
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
 * Hotel Accomodation Type
 * @version     2.2.0
 * @since       2.2.0
 * @package     contrexx
 * @subpackage  module_hotelcard
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */
class HotelAccomodationType
{
    const TEXT_ACCOMODATION_TYPE = 'hotelcard_accomodation_type';

    /**
     * Array of hotel to HotelAccomodationType relations
     *
     * The array is of the form
     *  array(
     *    type ID => type name,
     *    ... more ...
     *  )
     * The HotelAccomodationType IDs are sorted by their ordinal values.
     * @see     __construct
     * @var     array
     * @access  private
     * @static
     */
    private static $arrAccomodationTypes = false;


    /**
     * Initialize the accomodation types array
     * @global  ADONewConnection  $objDatabase
     */
    static function init()
    {
        global $objDatabase;

//echo("HotelAccomodationType::init(): Entered<br />");
        $arrSqlName = Text::getSqlSnippets(
            '`type`.`name_text_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_ACCOMODATION_TYPE
        );
        $query = "
            SELECT `type`.`id`, `type`.`ord` ".$arrSqlName['field']."
              FROM `".DBPREFIX."module_hotelcard_hotel_accomodation_type` AS `type`".
                   $arrSqlName['join']."
             ORDER BY `type`.`ord` ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        if ($objResult->EOF) return self::errorHandler();
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $text_id = $objResult->fields['name_text_id'];
            $strName = $objResult->fields[$arrSqlName['text']];
            if ($strName === null) {
                $objText = Text::getById($text_id, 0);
                if ($objText) $strName = $objText->getText();
            }
//echo("HotelAccomodationType::init(): Name $strName<br />");
            self::$arrAccomodationTypes[$id] = array(
                'id'      => $id,
                'text_id' => $text_id,
                'name'    => $strName,
                'ord'     => $objResult->fields['ord'],
            );
            $objResult->MoveNext();
        }
//echo("HotelAccomodationType::init(): Made<br />".var_export(self::$arrAccomodationTypes, true)."<br />");
        return true;
    }


    /**
     * Returns the array of all accomodation types
     * @return  array               The accomodation types array on success,
     *                              false otherwise
     */
    static function getArray()
    {
        if (empty(self::$arrAccomodationTypes)) self::init();
        return self::$arrAccomodationTypes;
    }


    /**
     * Returns the array of all accomodation type names, indexed by their ID
     * @return  array               The accomodation type names array
     *                              on success, false otherwise
     */
    static function getNameArray()
    {
        static $arrName = false;

        if (empty(self::$arrAccomodationTypes)) {
            $arrName = false;
            self::init();
        }
        if (empty($arrName)) {
            foreach (self::$arrAccomodationTypes as $arrAccomodationType) {
                $id = $arrAccomodationType['id'];
                $arrName[$id] = $arrAccomodationType['name'];
            }
        }

        return $arrName;
    }


    /**
     * Returns the accomodation type name for the given ID
     *
     * If the ID is empty (or zero), the entry with ID 1 is returned.
     * This is the "please choose" option.
     * @return  string              The accomodation type name on success,
     *                              false otherwise
     */
    static function getNameById($id)
    {
//echo("HotelAccomodationType::getNameById($id):  Entered<br />");
        if (empty(self::$arrAccomodationTypes)) self::init();
        if (empty($id)) $id = 1;
        return (isset(self::$arrAccomodationTypes[$id])
            ? self::$arrAccomodationTypes[$id]['name'] : false);
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

die("HotelAccomodationType::errorHandler(): Disabled!<br />");

        $arrTables = $objDatabase->MetaTables('TABLES');
        if (in_array(DBPREFIX."module_hotelcard_hotel_accomodation_type", $arrTables)) {
            // Drop it
            $query = "
                DROP TABLE `".DBPREFIX."module_hotelcard_hotel_accomodation_type`";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
//echo("HotelAccomodationType::errorHandler(): Dropped table ".DBPREFIX."module_hotelcard_hotel_accepts_HotelAccomodationType<br />");
        }
        $query = "
            CREATE TABLE `".DBPREFIX."module_hotelcard_hotel_accomodation_type` (
              `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `name_text_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
              `ord` INT(10) UNSIGNED NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`)
            ) ENGINE=MYISAM";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
//echo("HotelAccomodationType::errorHandler(): Created table ".DBPREFIX."module_hotelcard_hotel_accepts_HotelAccomodationType<br />");

        // Add types
        $arrTypes = array(
            // ord => language arrays
//            0 => array(
//                // language ID => name
//                1 => '-- Hoteltyp wählen --',
//                2 => '-- Select hotel type --',
//                3 => '-- Select hotel type --',
//                4 => '-- Select hotel type --',
//            ),
            1 => array(
                1 => 'Hotel',
                2 => 'Hotel',
                3 => 'Hotel',
                4 => 'Hotel',
            ),
            array(
                1 => 'Motel',
                2 => 'Motel',
                3 => 'Motel',
                4 => 'Motel',
            ),
            array(
                1 => 'Resort',
                2 => 'Resort',
                3 => 'Resort',
                4 => 'Resort',
            ),
            array(
                1 => 'Apartment',
                2 => 'Apartment',
                3 => 'Apartment',
                4 => 'Apartment',
            ),
            array(
                1 => 'Herberge',
                2 => 'Hostel',
                3 => 'Hostel',
                4 => 'Hostel',
            ),
            array(
                1 => 'Residenz',
                2 => 'Residence',
                3 => 'Residence',
                4 => 'Residence',
            ),
/*
            array(
                1 => 'Guest accommodation',
                2 => 'Guest accommodation',
                3 => 'Guest accommodation',
                4 => 'Guest accommodation',
            ),
*/
        );

        Text::deleteByKey(self::TEXT_ACCOMODATION_TYPE);

        // The first option ("please choose") *MUST* have the ordinal 0 (zero)
        // in order for the selection dropdown to work properly.
        foreach ($arrTypes as $ord => $arrLang) {
            $text_id = 0;
            foreach ($arrLang as $lang_id => $name) {
                $objText = new Text(
                    $name, $lang_id,
                    MODULE_ID, self::TEXT_ACCOMODATION_TYPE, $text_id
                );
                if (!$objText->store()) {
                    Text::errorHandler();
                    return self::errorHandler();
//die("Failed to store Text for accomodation type $name");
//                    return false;
                }
                $text_id = $objText->getId();
            }
            $query = "
                INSERT INTO `".DBPREFIX."module_hotelcard_hotel_accomodation_type` (
                  `name_text_id`, `ord`
                ) VALUES (
                  $text_id, $ord
                )";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
        }

        // More to come...

        // Always!
        return false;
    }

}

?>
