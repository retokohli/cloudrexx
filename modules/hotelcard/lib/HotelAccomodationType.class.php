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
    const TEXT_ACCOMODATION_TYPE = 'HOTELCARD_ACCOMODATION_TYPE';

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
            SELECT `type`.`ord` ".$arrSqlName['field']."
              FROM `".DBPREFIX."module_hotelcard_hotel_accomodation_type` AS `type`".
                   $arrSqlName['join']."
             ORDER BY `type`.`ord` ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        while (!$objResult->EOF) {
            $id = $objResult->fields['name_text_id'];
            $strName = $objResult->fields[$arrSqlName['text']];
            if ($strName === null) {
                $objText = Text::getById($id, 0);
                if ($objText) $strName = $objText->getText();
            }
//echo("HotelAccomodationType::init(): Name $strName<br />");

            self::$arrAccomodationTypes[$id] = array(
                'id' => $id,
                'name' => $strName,
                'ord' => $objResult->fields['ord'],
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
            foreach (self::$arrAccomodationTypes as $id => $arrAccomodationType) {
                $arrName[$id] = $arrAccomodationType['name'];
            }
        }
        return $arrName;
    }


    /**
     * Returns the accomodation type name for the given ID
     * @return  string              The accomodation type name on success,
     *                              false otherwise
     */
    static function getNameById($id)
    {
//echo("HotelAccomodationType::getNameById($id):  Entered<br />");
        if (empty(self::$arrAccomodationTypes)) self::init();
        return self::$arrAccomodationTypes[$id]['name'];
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

echo("HotelAccomodationType::errorHandler(): Entered<br />");

        $arrTables = $objDatabase->MetaTables('TABLES');
        if (in_array(DBPREFIX."module_hotelcard_hotel_accomodation_type", $arrTables)) {
            // Drop it
            $query = "
                DROP TABLE `".DBPREFIX."module_hotelcard_hotel_accomodation_type`";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
echo("HotelAccomodationType::errorHandler(): Dropped table ".DBPREFIX."module_hotelcard_hotel_accepts_HotelAccomodationType<br />");
        }
        $query = "
            CREATE TABLE `".DBPREFIX."module_hotelcard_hotel_accomodation_type` (
              `name_text_id` INT UNSIGNED NOT NULL DEFAULT 0,
              `ord` INT UNSIGNED NOT NULL DEFAULT 0,
              PRIMARY KEY (`name_text_id`),
              INDEX `accomodation_text_id` (`name_text_id` ASC)
            ) ENGINE=MYISAM";
/*            , CONSTRAINT `accomodation_text_id`
                FOREIGN KEY (`name_text_id` )
                REFERENCES `hotelcard`.`".DBPREFIX."core_text` (`id` )
                ON DELETE NO ACTION
                ON UPDATE NO ACTION*/
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
echo("HotelAccomodationType::errorHandler(): Created table ".DBPREFIX."module_hotelcard_hotel_accepts_HotelAccomodationType<br />");

// TODO:  Try to DROP old records

        // Add types
        $arrTypes = array(
            // ord, name
            0 => '-- select hotel type --',
            1 => 'Apartment',
            2 => 'Guest accommodation',
            3 => 'Hostel',
            4 => 'Hotel',
            5 => 'Motel',
            6 => 'Residence',
            7 => 'Resort',
        );
        $arrText = Text::getArrayById(
            MODULE_ID, self::TEXT_ACCOMODATION_TYPE, FRONTEND_LANG_ID
        );

        foreach ($arrTypes as $ord => $type) {
            $objTextFound = false;
            foreach ($arrText as $objText) {
                // Do not insert text that is already there
                if ($type == $objText->getText()) {
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
                    $type, FRONTEND_LANG_ID,
                    MODULE_ID, self::TEXT_ACCOMODATION_TYPE
                );
                if (!$objText->store()) {
// TODO:  Add error message
                    return false;
                }
            }
            $query = "
                INSERT INTO `".DBPREFIX."module_hotelcard_hotel_accomodation_type` (
                  `name_text_id`, `ord`
                ) VALUES (
                  ".$objText->getId().", $ord
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
