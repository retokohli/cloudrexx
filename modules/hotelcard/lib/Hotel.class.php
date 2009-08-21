<?php

/**
 * Hotel class
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
 * Validator
 * @ignore
 */
require_once ASCMS_FRAMEWORK_PATH.'/Validator.class.php';

require_once 'HotelFacility.class.php';
require_once 'HotelRoom.class.php';

/**
 * Hotel class
 * @version     2.2.0
 * @since       2.2.0
 * @package     contrexx
 * @subpackage  module_hotelcard
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */
class Hotel
{
    const TEXT_HOTEL_GROUP = 'HOTELCARD_HOTEL_GROUP';

    /**
     * A complete list of all Hotel field names
     * @var     array
     * @access  private
     * @static
     */
    private static $arrFieldnames = array(
        'id',
        'group_id',
        'accomodation_type_id',
        'lang_id',
        'image_id',
        'rating',
        'recommended',
        'numof_rooms',
        'hotel_name',
        'hotel_address',
        'hotel_zip',
        'hotel_location',
        'hotel_region_id',
        'description_text_id',
        'policy_text_id',
        'hotel_uri',
        'contact_name',
        'contact_gender',
        'contact_position',
        'contact_department',
        'contact_phone',
        'contact_fax',
        'contact_email',
        'reservation_name',
        'reservation_phone',
        'reservation_gender',
        'reservation_fax',
        'reservation_email',
        'accountant_name',
        'accountant_gender',
        'accountant_phone',
        'accountant_fax',
        'accountant_email',
        'billing_name',
        'billing_gender',
        'billing_address',
        'billing_company',
        'billing_zip',
        'billing_location',
        'billing_country_id',
        'billing_tax_id',
        'checkin_from',
        'checkin_to',
        'checkout_from',
        'checkout_to',
        'comment',
        'found_how',
    );

    /**
     * Stores the Hotel data in the object
     * @var     array
     * @access  private
     */
    private $arrFieldvalues = array();

    /**
     * If this is false, the object has not been modified.  Otherwise,
     * changes will be lost unless it is {@see store()}d.
     *
     * Set to true whenever {@see setFieldvalue()} applies a change,
     * and cleared when it's {@see store()}d.
     * @var   boolean
     */
    private $flagChanged = false;

    /**
     * Construct the Hotel
     * @param   integer           $hotel_id       The Hotel ID
     * @global  ADONewConnection  $objDatabase
     */
    function __construct()
    {
    }


    /**
     * Initializes all the data from the database if the Hotel ID is
     * given and refers to an existing record
     *
     * If the ID is invalid or no record is found for it, returns false.
     * @param   integer   $hotel_id   The Hotel ID
     * @return  Hotel                 The object
     */
    static function getById($hotel_id)
    {
        global $objDatabase;

        $query = "
            SELECT `".join('`,`', array_keys($this->arrFieldvalues))."`
              FROM `".DBPREFIX."module_hotelcard_hotel`
             WHERE `id`=$hotel_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->EOF) return false;
        $objHotel = new Hotel();
        foreach (array_keys($this->arrFieldvalues) as $name) {
            $objHotel->arrFieldvalues[$name] = $objResult->fields[$name];
        }
        return $objHotel;
    }


    /**
     * Returns true if any changes have been applied to the object.
     * @return  boolean             True if the object has been changed,
     *                              false otherwise
     */
    function isChanged()
    {
        return $this->flagChanged;
    }


    /**
     * Returns an array with all available field names
     * @return  array         The field name array
     */
    static function getFieldnames()
    {
        return self::$arrFieldnames;
    }


    /**
     * Set the value for the given field name to the given value
     *
     * $name must be one of the field names present in the objects'
     * $arrFieldvalues array.  If it's not, false is returned, and no changes
     * are made to the object.
     * All values are validated.  If validation fails, no changes are made.
     * If the $name is valid, and its value changes, the objects' $flagChange
     * variable is set to true, and true is returned.
     * @param   string    $name     The field name
     * @param   string    $value
     * @return  boolean             True if the value has been applied,
     *                              false otherwise.
     */
    function setFieldvalue($name, $value)
    {
        switch ($name) {
          // INT(10) NULL, IDs -> Verify that it's a non-negative integer
          case 'group_id':
          case 'image_id':
          case 'policy_text_id':
            if (!is_integer($value) || $value < 0) return false;
            break;

          // INT(10) NOT NULL, IDs -> Verify that it's a positive integer
          case 'accomodation_type_id':
          case 'lang_id':
          case 'description_text_id':
          case 'hotel_region_id':
          case 'billing_country_id':
            if (!is_integer($value) || $value <= 0) return false;
            break;

          // TINYINT(1), flags -> chop down to boolean
          case 'rating':
          case 'recommended':
            $value = !empty($value);
            break;

          // INT, non-negative -> Verify that it's not a negative number
          case 'numof_rooms':
            if (!is_integer($value) || $value < 0) return false;
            break;

          // ENUM('M','F'), gender -> If she's not female, he's male
          case 'contact_gender':
          case 'reservation_gender':
          case 'accountant_gender':
          case 'billing_gender':
            $value = (preg_match('/[wf]/', $value) ? 'F' : 'M');
            break;

          // TINYTEXT, names and address stuff -> Strip tags and possibly other crap
          case 'hotel_name':
          case 'hotel_address':
          case 'hotel_zip':
          case 'hotel_location':
          case 'contact_name':
          case 'contact_position':
          case 'contact_department':
          case 'reservation_name':
          case 'accountant_name':
          case 'billing_name':
          case 'billing_address':
          case 'billing_company':
          case 'billing_zip':
          case 'billing_location':
          case 'billing_tax_id':
            $value = trim(strip_tags($value));
            break;

          // TINYTEXT, phone or fax number -> Strip crap and format as such
          case 'contact_phone':
          case 'reservation_phone':
          case 'reservation_fax':
          case 'accountant_phone':
          case 'accountant_fax':
            $value = preg_replace('/\D/', '', $value);
            break;

          // TINYTEXT, e-mail address -> Verify
          case 'contact_email':
          case 'reservation_email':
          case 'accountant_email':
            if (!FWValidator::isEmail($value)) return false;
            break;

          // TINYTEXT, URI -> Verify
          case 'hotel_uri':
            if (!FWValidator::isUri($value)) return false;
            break;

          // TIME -> Verify (
          //    Note: checkin_from, checkout_to are mandatory.
          //    If provided, the matching pairs must be in timely order.
          //    This needs to be verified in another place, however.
          case 'checkin_from':
          case 'checkout_to':
            if (empty($value)) return false;
          case 'checkin_to':
          case 'checkout_from':
            // Remove non-digits and insert a colon
            $value = preg_replace('/\D/', '', $value);
            $value = preg_replace('/^(\d?\d)(\d\d)/', '\1:\2', $value);
            break;

          // TEXT -> Strip tags and possibly other crap
          case 'comment':
          case 'found_how':
            $value = trim(strip_tags($value));
            break;

          default:
            // case 'id' -> *MUST NOT* be changed
            // Skip any unknown/unwnanted fields
            return false;
        }
        // Accept valid values
        $this->arrFieldvalues[$name] = $value;
        $this->flagChanged = true;
        return true;
    }


    /**
     * Returns the value for the given field name
     *
     * If the name is invalid, or if the value is not set, null is returned.
     * @param   string    $name   The field name
     * @return  string            The field value, or null
     */
    function getFieldvalue($name)
    {
        return (isset($this->arrFieldvalues[$name])
            ? $this->arrFieldvalues[$name] : null);
    }


    /**
     * Stores the object in the database
     *
     * Inserts or updates the record depending on the result of
     * {@see recordExists()}.
     * @return  boolean                     True on success, false otherwise
     * @global  ADONewConnection  $objDatabase
     */
    function store()
    {
        if ($this->recordExists()) return $this->update();
        return $this->insert();
    }


    /**
     * Insert the object into the database
     * @return  boolean                     True on success, false otherwise
     */
    function insert()
    {
        global $objDatabase;

// TODO: store Text

        $query = "
            INSERT INTO `".DBPREFIX."module_hotelcard_hotel` (
              `".join('`,`', array_keys($this->arrFieldvalues))."`
            ) VALUES (
              '".join("','", $this->arrFieldvalues)."'
            )";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) return true;
        return false;
    }


    /**
     * Updates the object in the database
     * @return  boolean                     True on success, false otherwise
     */
    function update()
    {
        global $objDatabase;

// TODO: store Text

        $strFields = '';
        foreach ($this->arrFieldvalues as $name => $value) {
        	 $strFields .=
        	     (empty($strFields) ? ',' : '').
        	     "`$name`='$value'";
        }
        $query = "
            UPDATE `".DBPREFIX."module_hotelcard_hotel`
               SET $strFields";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) return true;
        return false;
    }


    static function storeFromSession()
    {
        global $_ARRAYLANG;

        $objHotel = new Hotel();
        foreach (Hotel::getFieldnames() as $name) {
            if (!isset($_SESSION['hotelcard'][$name])) continue;
            $value = $_SESSION['hotelcard'][$name];
            switch ($name) {
              case 'id':
echo("Skipping name $name<br />");
                continue;
echo("Storing name $name, value $value<br />");
              case 'group_id':
echo("Adding group name $name<br />");
                // See whether that hotel group exists already in that language
                $arrId = Text::getIdArrayBySearch($value, MODULE_ID, self::TEXT_HOTEL_GROUP, FRONTEND_LANG_ID);
                if (empty($arrId)) {
                    // In another language maybe?
                    $arrId = Text::getIdArrayBySearch($value, MODULE_ID, self::TEXT_HOTEL_GROUP);
                }
                if (empty($arrId)) {
                    // No way... Create a new Text
                    $objText = new Text($value, FRONTEND_LANG_ID, MODULE_ID, self::TEXT_HOTEL_GROUP);
                    if (!$objText->store()) {
                // TODO:  Error message
                        return false;
                    }
                } else {
                    // Use the existing Text
                    $id = key($arrId);
                    $objText = Text::getById($id, FRONTEND_LANG_ID);
                }
                $_SESSION['hotelcard']['group_id'] = $objText->getId();
                break;
              default:
            }
            if ($objHotel->setFieldvalue($name, $value)) {
echo("Success name $name, value $value<br />");
                continue;
            }
            Hotelcard::addMessage(sprintf(
                $_ARRAYLANG['TXT_HOTELCARD_ERROR_ILLEGAL_VALUE'],
                $_ARRAYLANG['TXT_HOTELCARD_'.strtoupper($name)]));
// Test only!
//                return false;
        }
        $hotel_id =  $objHotel->getFieldvalue('id');

        // Store the hotel facilities
        if (empty($_SESSION['hotelcard']['hotel_facility_id'])) return false;
        foreach ($_SESSION['hotelcard']['hotel_facility_id'] as $hotel_facility_id) {
            if (HotelFacility::addRelation(
                $hotel_id, $hotel_facility_id)) continue;
            Hotelcard::addMessage(sprintf(
                $_ARRAYLANG['TXT_HOTELCARD_ERROR_FAILED_TO_ADD_HOTEL_FACILITY'],
                HotelFacility::getFacilityNameById($hotel_facility_id)));
            return false;
        }

        // Store the room types
        if (empty($_SESSION['hotelcard']['room_type'])) return false;
        foreach ($_SESSION['hotelcard']['room_type'] as $room_type) {
            $room_type_id = HotelRoom::storeType(
                $room_type,
                $hotel_id,
                $_SESSION['hotelcard']['room_available'],
                $_SESSION['hotelcard']['room_price']);
            if ($room_type_id) {
                // Store the room facilities
                foreach ($_SESSION['hotelcard']['room_facility_id'] as $room_facility_id) {
                    if (HotelRoom::addFacility(
                        $room_type_id, $room_facility_id)) continue;
                    Hotelcard::addMessage(sprintf(
                        $_ARRAYLANG['TXT_HOTELCARD_ERROR_FAILED_TO_ADD_ROOM_FACILITY'],
                        HotelRoom::getFacilityNameById($room_facility_id)));
                    return false;
                }
                continue;
            }
            Hotelcard::addMessage(sprintf(
                $_ARRAYLANG['TXT_HOTELCARD_ERROR_FAILED_TO_ADD_ROOM_TYPE'],
                HotelFacility::getFacilityNameById($room_type)));
            return false;
        }

// TODO
        $_SESSION['hotelcard']['registration_id'] = 0;
        $_SESSION['hotelcard']['registration_date'] = 0;
        return true;
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

echo("Hotel::errorHandler(): Entered<br />");

        $arrTables = $objDatabase->MetaTables('TABLES');
        if (in_array(DBPREFIX."module_hotelcard_hotel", $arrTables)) {
            $query = "DROP TABLE `".DBPREFIX."module_hotelcard_hotel`";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
echo("Hotel::errorHandler(): Dropped table ".DBPREFIX."module_hotelcard_hotel<br />");
        }
        $query = "
            CREATE TABLE `".DBPREFIX."module_hotelcard_hotel` (
              `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `group_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Group or chain that the hotel belongs to.\nIf NULL, it is independent.',
              `accomodation_type_id` INT(10) UNSIGNED NULL DEFAULT NULL,
              `lang_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Preferred language.\nIs NULL if none is selected.',
              `image_id` INT(10) UNSIGNED NULL DEFAULT NULL,
              `rating` TINYINT(1) UNSIGNED NULL DEFAULT NULL,
              `recommended` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Defaults to zero.\nRecommended if non-zero. In that case, the value *SHOULD* be 1.',
              `numof_rooms` INT(5) UNSIGNED NULL DEFAULT NULL,
              `hotel_name` TINYTEXT NOT NULL DEFAULT '',
              `hotel_address` TINYTEXT NULL DEFAULT NULL,
              `hotel_zip` TINYTEXT NULL DEFAULT NULL,
              `hotel_location` TINYTEXT NULL DEFAULT NULL,
              `hotel_region_id` INT(10) UNSIGNED NULL,
              `description_text_id` INT(10) UNSIGNED NULL DEFAULT NULL,
              `policy_text_id` INT(10) UNSIGNED NULL DEFAULT NULL,
              `uri` TINYTEXT NULL DEFAULT NULL,
              `contact_name` TINYTEXT NULL DEFAULT NULL,
              `contact_gender` ENUM('M','F') NULL DEFAULT NULL,
              `contact_position` TINYTEXT NULL DEFAULT NULL,
              `contact_department` TINYTEXT NULL DEFAULT NULL,
              `contact_phone` TINYTEXT NULL DEFAULT NULL,
              `contact_fax` TINYTEXT NULL DEFAULT NULL,
              `contact_email` TINYTEXT NULL DEFAULT NULL,
              `reservation_name` TINYTEXT NULL DEFAULT NULL,
              `reservation_gender` ENUM('M','F') NULL DEFAULT NULL,
              `reservation_phone` TINYTEXT NULL DEFAULT NULL,
              `reservation_fax` TINYTEXT NULL DEFAULT NULL,
              `reservation_email` TINYTEXT NULL DEFAULT NULL,
              `accountant_name` TINYTEXT NULL DEFAULT NULL,
              `accountant_gender` ENUM('M','F') NULL DEFAULT NULL,
              `accountant_phone` TINYTEXT NULL DEFAULT NULL,
              `accountant_fax` TINYTEXT NULL DEFAULT NULL,
              `accountant_email` TINYTEXT NULL DEFAULT NULL,
              `billing_name` TINYTEXT NULL DEFAULT NULL,
              `billing_gender` ENUM('M','F') NULL DEFAULT NULL,
              `billing_address` TINYTEXT NULL DEFAULT NULL,
              `billing_company` TINYTEXT NULL DEFAULT NULL,
              `billing_zip` TINYTEXT NULL DEFAULT NULL,
              `billing_location` TINYTEXT NULL DEFAULT NULL,
              `billing_country_id` INT(10) NULL DEFAULT NULL,
              `billing_tax_id` TINYTEXT NULL DEFAULT NULL,
              `checkin_from` TIME NULL DEFAULT NULL,
              `checkin_to` TIME NULL DEFAULT NULL,
              `checkout_from` TIME NULL DEFAULT NULL,
              `checkout_to` TIME NULL DEFAULT NULL,
              `comment` TEXT NULL DEFAULT NULL,
              `found_how` TEXT NULL DEFAULT NULL,
              PRIMARY KEY (`id`, `description_text_id`),
              INDEX `hotel_type_id` (`accomodation_type_id` ASC),
              INDEX `hotel_image_id` (`image_id` ASC),
              INDEX `hotel_group_id` (`group_id` ASC),
              INDEX `hotel_description_text_id` (`description_text_id` ASC),
              INDEX `hotel_region_id` (`hotel_region_id` ASC),
              CONSTRAINT `hotel_type_id`
                FOREIGN KEY (`accomodation_type_id` )
                REFERENCES `".DBPREFIX."module_hotelcard_hotel_accomodation_type` (`id` )
                ON DELETE NO ACTION
                ON UPDATE NO ACTION,
              CONSTRAINT `hotel_image_id`
                FOREIGN KEY (`image_id` )
                REFERENCES `".DBPREFIX."core_image` (`id` )
                ON DELETE NO ACTION
                ON UPDATE NO ACTION,
              CONSTRAINT `hotel_group_id`
                FOREIGN KEY (`group_id` )
                REFERENCES `".DBPREFIX."module_hotelcard_hotel_group` (`id` )
                ON DELETE NO ACTION
                ON UPDATE NO ACTION,
              CONSTRAINT `hotel_description_text_id`
                FOREIGN KEY (`description_text_id` )
                REFERENCES `".DBPREFIX."core_text` (`id` )
                ON DELETE NO ACTION
                ON UPDATE NO ACTION,
              CONSTRAINT `hotel_region_id`
                FOREIGN KEY (`hotel_region_id` )
                REFERENCES `".DBPREFIX."core_region` (`id` )
                ON DELETE NO ACTION
                ON UPDATE NO ACTION
            ) ENGINE=MYISAM";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
echo("Hotel::errorHandler(): Created table ".DBPREFIX."module_hotelcard_hotel<br />");

        // More to come...

        // Always!
        return false;
    }

}

?>
