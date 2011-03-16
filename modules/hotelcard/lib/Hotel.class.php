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
require_once ASCMS_CORE_PATH.'/Country.class.php';

/**
 * Validator
 * @ignore
 */
require_once ASCMS_FRAMEWORK_PATH.'/Validator.class.php';

require_once 'HotelFacility.class.php';
require_once 'HotelRoom.class.php';
require_once 'HotelAccomodationType.class.php';

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
// Generic, not used
//    const TEXT_HOTEL       = 'hotelcard_hotel';
    const TEXT_GROUP        = 'hotelcard_hotel_group';
    const TEXT_DESCRIPTION  = 'hotelcard_hotel_description';
    const TEXT_POLICY       = 'hotelcard_hotel_policy';
    //const TEXT_LOCATION     = 'hotelcard_hotel_location';

    /**
     * Various Hotel status, pretty much self-descriptive
     */
// TODO:  Apply these!
    const STATUS_UNKNOWN    = 0; // Error or whatever
    const STATUS_ACCOUNT    = 1; // User account created
    const STATUS_CONFIRMED  = 2; // Confirmation e-mail sent
    const STATUS_VERIFIED   = 3; // Hotel data internally verified
    const STATUS_LOCKED     = 4; // User account locked
    const STATUS_DISABLED   = 5; // Hotel not visible in the frontend
    const STATUS_TERMINATED = 6; // Cooperation terminated,
                                 // like LOCKED and DISABLED.
    const STATUS_DELETED    = 7; // Account and hotel deleted.
                                 // This will never occur, for obvious reasons.
    const STATUS_COUNT      = 8; // Keep this up to date!


   /**
     * A complete list of all Hotel database table field names
     *
     * Mind that there are other (temporary) fields not listed here,
     * that correspond to some of the IDs and contain the plain text
     * in the current language
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
//        'hotel_zip',
        'hotel_location',
        'hotel_region',
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
//        'billing_zip',
        'billing_location',
        'billing_country_id',
        'billing_tax_id',
        'checkin_from',
        'checkin_to',
        'checkout_from',
        'checkout_to',
        'comment',
        'found_how',
        'registration_time',
        'status',
    );


   /**
     * A complete list of all fields by which lists of Hotels may be sorted
     *
     * Some of the field names are aliases for joined Text records and are
     * not part of the Hotel record itself.  Mind that you have to set
     * these aliases manually if you want to use them in your own queries!
     * @var     array
     * @access  public
     * @static
     */
    public static $arrSortfields = array(
        'id',
        'group',
        'accomodation_type',
        'lang',
        'rating',
        'numof_rooms',
        'hotel_name',
        'hotel_location',
        'hotel_location_name',
        'hotel_region',
        'description_text',
        'policy_text',
        'contact_name',
        'registration_time',
        'status',
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

// TODO: Test
        $arrSqlGroup = Text::getSqlSnippets(
            '`hotel`.`group_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_GROUP
        );
        $arrSqlDescription = Text::getSqlSnippets(
            '`hotel`.`description_text_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_DESCRIPTION
        );
        $arrSqlPolicy = Text::getSqlSnippets(
            '`hotel`.`policy_text_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_POLICY
        );

        $query = "
            SELECT `hotel`.`".join('`,`hotel`.`', self::$arrFieldnames)."`".
                   $arrSqlGroup['field'].
                   $arrSqlDescription['field'].
                   $arrSqlPolicy['field']."
              FROM `".DBPREFIX."module_hotelcard_hotel` AS `hotel`".
                   $arrSqlGroup['join'].
                   $arrSqlDescription['join'].
                   $arrSqlPolicy['join']."
             WHERE `hotel`.`id`=$hotel_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        if ($objResult->EOF) return false;
        $objHotel = new Hotel();
        foreach (self::$arrFieldnames as $name) {
            $objHotel->arrFieldvalues[$name] = $objResult->fields[$name];
        }
        // Text
        $objHotel->arrFieldvalues['group'] = $objResult->fields[$arrSqlGroup['text']];
        $objHotel->arrFieldvalues['description_text'] = $objResult->fields[$arrSqlDescription['text']];
        $objHotel->arrFieldvalues['policy_text'] = $objResult->fields[$arrSqlPolicy['text']];
        $objHotel->arrFieldvalues['accomodation_type'] =
            HotelAccomodationType::getNameById(
                $objHotel->arrFieldvalues['accomodation_type_id']);
        // Note:  The Hotel region is only stored as a ZIP in the object.
//        $objHotel->arrFieldvalues['hotel_region'] =
//            Text::getById($objHotel->getFieldvalue('hotel_region'), FRONTEND_LANG_ID);
        // Image
        $objHotel->arrFieldvalues['images'] =
            Image::getArrayById($objHotel->getFieldvalue('image_id'));
        return $objHotel;
    }


    /**
     * Returns an array of Hotels (also arrays) for the given parameters
     *
     * See {@see getIdArray()} and {@see getById()} for details.
     * @param   integer   $count      The actual number of hotels returned,
     *                                by reference
     * @param   string    $order      The sorting order, SQL syntax
     * @param   array     $filter     The array of filter values
     * @param   integer   $offset     The zero based offset for the list
     *                                of Hotels returned
     * @param   integer   $limit      The maximum number of Hotels to be
     *                                returned
     * @return  array                 The array of Hotel arrays on success,
     *                                false otherwise
     */
    static function getArray(&$count, $order, $filter, $offset, $limit)
    {
        $arrId = self::getIdArray($count, $order, $filter, $offset, $limit);
        $arrHotels = array();
        foreach ($arrId as $hotel_id) {
            $objHotel = self::getById($hotel_id);
            if (!$objHotel) {
                --$count;
                continue;
            }
            $arrHotels[$hotel_id] = $objHotel;
        }
        return $arrHotels;
    }


    /**
     * Returns an array of Hotel IDs for the given parameters
     *
     * The $filter array may include zero or more of the following field
     * names as indices, plus some value that will be tested for equality:
     * id, accomodation_type_id, lang_id, rating, recommended,
     * hotel_location, hotel_region, status.
     * In addition, the 'term' index may hold a search term against which
     * most of the hotel fields plus some are matched.
     * The $order parameter value may be one of the field names defined
     * in {@see self::$arrSortfields}.  Backticks are added to the field
     * name, so you *MUST NOT* add them yourself.
     * If $limit is empty, it is set to -1 in order to include all results.
     * @param   integer   $count      The actual number of hotels returned,
     *                                by reference
     * @param   string    $order      The sorting order field, SQL syntax;
     *                                defaults to 'register_date DESC'
     * @param   array     $filter     The array of filter values
     * @param   integer   $offset     The zero based offset for the list
     *                                of Hotels returned
     * @param   integer   $limit      The maximum number of Hotels to be
     *                                returned
     * @return  array                 The array of Hotel arrays on success,
     *                                false otherwise
     */
    static function getIdArray(
        &$count, $order=false, $filter=false, $offset=0, $limit=0
    ) {
        global $objDatabase;

        $arrSqlGroup = Text::getSqlSnippets(
            '`hotel`.`group_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_GROUP //, 'group'
        );
//        $arrSqlAccomodation = Text::getSqlSnippets(
//            '`hotel`.`accomodation_type_id`', FRONTEND_LANG_ID,
//            MODULE_ID, HotelAccomodationType::TEXT_ACCOMODATION_TYPE,
//            'accomodation_type'
//        );
        $arrSqlDescription = Text::getSqlSnippets(
            '`hotel`.`description_text_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_DESCRIPTION, 'description_text'
        );
        $arrSqlPolicy = Text::getSqlSnippets(
            '`hotel`.`policy_text_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_POLICY, 'policy_text'
        );
        // 'hotel_location' is a ZIP, looked up in the zip (aka location) table
        $arrSqlLocation = Location::getSqlSnippets(
            '`hotel`.`hotel_location`', 'hotel_location_name'
        );
        if (empty($order)) $order = 'register_date DESC';
        // The order *SHOULD* contain the direction.
        // Add backticks if they are not present.
        $order = preg_match_replace('/^(\w)(\s\w)?$/', '`$1` $2', $order);
        $term = (empty($filter['term']) ? '' : addslashes($filter['term']));
        $query_id = "SELECT `hotel`.`id`";
        $query_count = "SELECT COUNT(*) AS `numof_hotels`";
        $query_order = " ORDER BY `hotel`.$order";
//echo("Status: ${filter['status']}: ".(is_numeric($filter['status']) ? "num" : " not num").(is_integer($filter['status']) ? "int" : " not int")."<br />");
        $query_body = "
              FROM `".DBPREFIX."module_hotelcard_hotel` AS `hotel`".
                   $arrSqlGroup['join'].
//                   $arrSqlAccomodation['join'].
                   $arrSqlDescription['join'].
                   $arrSqlPolicy['join'].
                   $arrSqlLocation['join']."
             WHERE 1".
        // Hotel identity matches
              (empty($filter['id']) ? '' : " AND `hotel`.`id`=".$filter['id']).
              (empty($filter['accomodation_type_id'])
                  ? ''
                  : " AND `hotel`.`accomodation_type_id`=".
                    $filter['accomodation_type_id']).
              (empty($filter['lang_id'])
                  ? '' : " AND `hotel`.`lang_id`=".$filter['lang_id']).
              (empty($filter['rating'])
                  ? '' : " AND `hotel`.`rating`='".$filter['rating']."'").
              (empty($filter['recommended'])
                  ? '' : " AND `hotel`.`recommended`=".$filter['recommended']).
              (empty($filter['hotel_location'])
                  ? ''
                  : " AND (   `hotel`.`hotel_location`='".
                    $filter['hotel_location']."'".
                    " OR `".$arrSqlLocation['alias']."`.`city`='".
                    $filter['hotel_location']."')").
              (empty($filter['hotel_region'])
                  ? ''
                  : " AND `hotel`.`hotel_region`='".
                    $filter['hotel_region']."'").
              (isset($filter['status']) && is_numeric($filter['status'])
                  ? " AND `hotel`.`status`=".$filter['status'] : '').
        // Search term
              (empty($term)
                ? ''
                : " AND (
                     `".$arrSqlGroup['alias']."`.`text` LIKE '%$term%'
                  OR `".$arrSqlDescription['alias']."`.`text` LIKE '%$term%'
                  OR `".$arrSqlPolicy['alias']."`.`text` LIKE '%$term%'
                  OR `hotel`.`hotel_name` LIKE '%$term%'
                  OR `hotel`.`hotel_address` LIKE '%$term%'
                  OR `hotel`.`hotel_location` LIKE '%$term%'
                  OR `hotel`.`hotel_region` LIKE '%$term%'
                  OR `hotel`.`description_text_id` LIKE '%$term%'
                  OR `hotel`.`policy_text_id` LIKE '%$term%'
                  OR `hotel`.`hotel_uri` LIKE '%$term%'
                  OR `hotel`.`contact_name` LIKE '%$term%'
                  OR `hotel`.`contact_position` LIKE '%$term%'
                  OR `hotel`.`contact_department` LIKE '%$term%'
                  OR `hotel`.`contact_phone` LIKE '%$term%'
                  OR `hotel`.`contact_fax` LIKE '%$term%'
                  OR `hotel`.`contact_email` LIKE '%$term%' ".
// Unused -- for the time being
//                  OR `hotel`.`reservation_name` LIKE '%$term%'
//                  OR `hotel`.`reservation_phone` LIKE '%$term%'
//                  OR `hotel`.`reservation_fax` LIKE '%$term%'
//                  OR `hotel`.`reservation_email` LIKE '%$term%'
//                  OR `hotel`.`accountant_name` LIKE '%$term%'
//                  OR `hotel`.`accountant_gender` LIKE '%$term%'
//                  OR `hotel`.`accountant_phone` LIKE '%$term%'
//                  OR `hotel`.`accountant_fax` LIKE '%$term%'
//                  OR `hotel`.`accountant_email` LIKE '%$term%'
//                  OR `hotel`.`billing_name` LIKE '%$term%'
//                  OR `hotel`.`billing_address` LIKE '%$term%'
//                  OR `hotel`.`billing_company` LIKE '%$term%'
//                  OR `hotel`.`billing_location` LIKE '%$term%'
//                  OR `hotel`.`billing_tax_id` LIKE '%$term%'
                  "OR `hotel`.`comment` LIKE '%$term%'
                  OR `hotel`.`found_how` LIKE '%$term%')");

        // Get the total count of matching hotels, set $count
        $objResult = $objDatabase->Execute($query_count.$query_body);
        if (!$objResult) return self::errorHandler();
        $count = $objResult->fields['numof_hotels'];

        // Get the IDs of the hotels according to the offset and limit
        if (empty($limit)) $limit = -1;
        $objResult = $objDatabase->SelectLimit(
            $query_id.$query_body.$query_order, $limit, $offset);
        if (!$objResult) return self::errorHandler();
        $arrId = array();
        while (!$objResult->EOF) {
            $arrId[] = $objResult->fields['id'];
            $objResult->MoveNext();
        }
        // Return the array of IDs
        return $arrId;
    }


    /**
     * Returns the total count of all hotel records present in the database
     * @return  integer     The count of Hotels on success, false otherwise
     */
    static function getCount()
    {
        global $objDatabase;

        $query = "
            SELECT COUNT(*) AS `numof_hotels`
              FROM `".DBPREFIX."module_hotelcard_hotel`";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        return $objResult->fields['numof_hotels'];
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
//echo("Hotel::setFieldvalue($name, $value): Entered<br />");
        switch ($name) {
          // INT(10) NULL, IDs -> Verify that it's a non-negative integer
          case 'group_id':
          case 'image_id':
          case 'policy_text_id':
            $value = intval($value);
            if ($value < 0) return false;
            break;

          // INT(10) NOT NULL, positive numbers, IDs
          // -> Verify that it's a positive integer
          case 'accomodation_type_id':
          case 'lang_id':
          case 'description_text_id': // See below
          case 'billing_country_id':
          case 'numof_rooms':
            $value = intval($value);
            if ($value <= 0) return false;
            break;

          // TINYINT(1), flags -> chop down to boolean
          case 'recommended':
            if ($value === '') return false;
            $value = !empty($value);
            break;

          // TINYINT(1), small number, greater than zero
          case 'status':
            $value = intval($value);
            if ($value == 0) return false;
            break;

          // ENUM('M','F'), gender -> If she's not female, he's male
          case 'contact_gender':
          case 'reservation_gender':
          case 'accountant_gender':
          case 'billing_gender':
            // Match any of 'w', 'W', 'f', 'F', 'female', 'weiblich', and so on.
            // Anything else is male.
            $value = (preg_match('/[wf]/i', $value) ? 'F' : 'M');
            break;

          // VARCHAR(3), usually small numbers, or '-' for no rating.
          // The empty string means that none has been selected.
          // If it's too long, the database will chop off the tail.
          case 'rating':
            $value = trim(strip_tags($value));
            if ($value === '') return false;
            break;

          // TINYTEXT, names and address stuff -> Strip tags and possibly other crap
          case 'hotel_name':
          case 'hotel_address':
//          case 'hotel_zip':
          case 'hotel_location':
          case 'hotel_region':
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
            if (empty($value)) return false;
            break;

          // TINYTEXT, phone or fax number -> Strip crap and format as such
          case 'contact_phone':
          case 'contact_fax':
          case 'reservation_phone':
          case 'reservation_fax':
          case 'accountant_phone':
          case 'accountant_fax':
//echo("1 Phone $name is $value...<br />");
            $value = trim(strip_tags($value));
//echo("2 Phone $name is $value...<br />");
            // Fix "+41" to "0041"
            $value = preg_replace('/^\\+/', '00', $value);
//echo("3 Phone $name is $value...<br />");
            // Strip any other non-numbers
            $value = preg_replace('/\\D/', '', $value);
//echo("4 Phone $name is $value...<br />");

            // Accept toll-free numbers
            // I guess these are beginning with
            // 0800, 0840, 0842, 0844, or 0848
            if (preg_match('/^08(?:00|40|42|44|48)\d{4,}/', $value)) break;

            // The number must be 10 or 13 digits, with a leading zero
//echo("5 Phone $name is $value, length ".strlen($value)."<br />");
            if (   (   strlen($value) != 10 && strlen($value) != 13)
                || !preg_match('/^0/', $value)) return false;
            if (strlen($value) == 10)
                // Replace "033" by "004133"
                $value = preg_replace('/^0/', '0041', $value);
//echo("fixed to $value<br />");
            break;

          // TINYTEXT, e-mail address -> Verify
          case 'contact_email':
          case 'contact_email_retype':
          case 'reservation_email':
          case 'reservation_email_retype':
          case 'accountant_email':
          case 'accountant_email_retype':
            if (!FWValidator::isEmail($value)) return false;
            break;

          // TINYTEXT, URI -> Verify
          case 'hotel_uri':
//            if (!preg_match('/^http\:\/\//', $value)) $value = 'http://'.$value;
            if (!FWValidator::hasProto($value)) $value = 'http://'.$value;
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
            if (empty($value)) return false;
            break;

          // TEXT -> Strip tags and possibly other crap
          case 'description_text': // See above
            // Need
            $value = trim(strip_tags($value));
//echo("Trimmed text to $value<br />");
            if (strlen($value) < 100 || strlen($value) > 500) return false;
            break;
          case 'comment':
          case 'found_how':
            // These are temporary string versions of text fields that
            // will be stored externally in multiple languages.
            // They replace their ID counterparts upon inserting or updating.
          case 'group':            // See above
          case 'policy_text':      // See above
            $value = trim(strip_tags($value));
            if (empty($value)) return false;
            break;

          default:
            // case 'id' -> *MUST NOT* be changed
            // Skip any unknown/unwnanted fields, but return true anyway
//echo("Hotel::setFieldvalue($name, $value):  Illegal name skipped<br />");
            return true;
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
     * Looks up this objects' ID and returns true if that record exists.
     * If the ID is invalid or no record is found for it, returns false.
     * @return  booelan           True if the reocrd exists, false otherwise
     */
    function recordExists()
    {
        global $objDatabase;

        if (empty($this->arrFieldvalues['id'])) return false;
        $id = $this->arrFieldvalues['id'];
        if (intval($id) <= 0) return false;
        $query = "
            SELECT 1
              FROM `".DBPREFIX."module_hotelcard_hotel`
             WHERE `id`=$id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        if ($objResult->EOF) return false;
        return true;
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
        // Pick any strings and add or replace corresponding Text records
        // in the current frontend language
        $arrTextfield = array(
            'description_text' => self::TEXT_DESCRIPTION,
            'policy_text' => self::TEXT_POLICY,
            'group' => self::TEXT_GROUP,
        );
        foreach ($arrTextfield as $textfield => $key) {
            if (!isset($this->arrFieldvalues[$textfield])) continue;
            $text = $this->arrFieldvalues[$textfield];
            $text_id = (empty($this->arrFieldvalues[$textfield.'_id'])
                ? 0 : $this->arrFieldvalues[$textfield.'_id']);
//echo("Hotel::store(): $textfield value $text, ID $text_id<br />");

            // Delete Text if it has been cleared, skip it if it's still empty
            if (empty($text)) {
                if (!$text_id) continue;
                //if (!
                Text::deleteById($text_id, FRONTEND_LANG_ID); //) {
//die("Hotel::store(): FAILED to delete empty text!<br />");
                //}
                $this->arrFieldvalues[$textfield.'_id'] = 0;
            }

            $text_id = Text::replace(
                $text_id, FRONTEND_LANG_ID, $text, MODULE_ID, $key);
            if (!$text_id) {
//die("Hotel::store(): FAILED to replace text!<br />");
                return false;
            }
            $this->arrFieldvalues[$textfield.'_id'] = $text_id;
//echo("Hotel::store(): replaced $textfield value $text, ID ".$text_id."<br />");
            unset($this->arrFieldvalues[$textfield]);
//echo("Hotel::store(): text $textfield value $text cleared<br />");
        }

        if (empty($this->arrFieldvalues['lang_id']))
            $this->arrFieldvalues['lang_id'] = FRONTEND_LANG_ID;
        if (empty($this->arrFieldvalues['registration_time']))
            $this->arrFieldvalues['registration_time'] = time();
        if (empty($this->arrFieldvalues['status']))
            $this->arrFieldvalues['status'] = self::STATUS_UNKNOWN;

        if ($this->recordExists()) {
//echo("Exists<br />");
            return $this->update();
        }
//echo("Does not exist<br />");
        return $this->insert();
    }


    /**
     * Insert the object into the database
     * @return  integer               The Hotel ID on success, false otherwise
     */
    function insert()
    {
        global $objDatabase;

        unset($this->arrFieldvalues['id']);
        if (empty($this->arrFieldvalues)) {
//die("no fields to insert");
            return false;
        }

        $strFieldnames = '';
        $strFieldvalues = '';
        foreach (self::$arrFieldnames as $name) {
            if ($name == 'id') continue;
            if (!isset($this->arrFieldvalues[$name])) {
//echo("no value for $name: ".$this->arrFieldvalues[$name]."<br />");
                continue;
            }
            $value = addslashes($this->arrFieldvalues[$name]);
//echo("adding $name => $value<br />");
            $strFieldnames .=
                 (empty($strFieldnames) ? '' : ',')."`$name`";
            $strFieldvalues .=
                 (empty($strFieldvalues) ? '' : ',')."'$value'";
        }
        if (empty($strFieldnames)) {
//die("no fields to update");
            return false;
        }

        $query = "
            INSERT INTO `".DBPREFIX."module_hotelcard_hotel` (
              $strFieldnames
            ) VALUES (
              $strFieldvalues
            )";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        $this->arrFieldvalues['id'] = $objDatabase->Insert_ID();
        return $this->arrFieldvalues['id'];
    }


    /**
     * Updates the object in the database
     * @return  integer               The Hotel ID on success, false otherwise
     */
    function update()
    {
        global $objDatabase;

//echo("Updating hotel<br />".var_export($this, true)."<hr />");
        $strFields = '';
        foreach (self::$arrFieldnames as $name) {
            if ($name == 'id') continue;
            if (!isset($this->arrFieldvalues[$name])) {
//echo("no value for $name: ".$this->arrFieldvalues[$name]."<br />");
                continue;
            }
            $value = addslashes($this->arrFieldvalues[$name]);
//echo("adding $name => $value<br />");
            $strFields .=
                 (empty($strFields) ? '' : ',').
                 "`$name`='$value'";
        }
        if (empty($strFields)) {
//die("no fields to update");
            return false;
        }
        $query = "
            UPDATE `".DBPREFIX."module_hotelcard_hotel`
               SET $strFields
             WHERE `id`=".$this->arrFieldvalues['id'];
        $objResult = $objDatabase->Execute($query);
        if ($objResult) return $this->arrFieldvalues['id'];
        return self::errorHandler();
    }


    /**
     * Returns the time of the last hotel registration that took place
     *
     * If there is a problem with the query, or no hotel registered,
     * returns false
     * @return  integer                   The time of the last registration
     *                                    on success, false otherwise
     */
    static function getLastRegistrationDate()
    {
        global $objDatabase;

        $query = "
            SELECT MAX(`registration_time`) AS `registration_time`
              FROM `".DBPREFIX."module_hotelcard_hotel`";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        if ($objResult->EOF || empty($objResult->fields['registration_time']))
            return false;
        return $objResult->fields['registration_time'];
    }


    /**
     * Looks up the ID for the hotel group name specified
     *
     * If it is not found, a new group is created.
     * @param   string    $group_name     The hotel group name
     * @return  intger                    The group ID on success,
     *                                    false otherwise
     */
    static function getGroupId($group_name)
    {
        $arrId = Text::getIdArrayBySearch(
            $group_name, MODULE_ID, self::TEXT_GROUP, FRONTEND_LANG_ID);
        if (empty($arrId)) {
            // None found.  Create a new Text
            $objText = new Text($group_name, FRONTEND_LANG_ID, MODULE_ID, self::TEXT_GROUP);
            if (!$objText->store()) {
                return false;
            }
        } else {
            // Use the existing Text
            $id = key($arrId);
            $objText = Text::getById($id, FRONTEND_LANG_ID);
        }
        return $objText->getId();
    }


    /**
     * Returns an array of all available Hotel status
     * @return  array             The Hotel status array
     */
    static function getStatusArray()
    {
        global $_ARRAYLANG;

        $arrStatus = array();
        for ($i = self::STATUS_UNKNOWN; $i < self::STATUS_COUNT; ++$i) {
            $arrStatus[$i] = $_ARRAYLANG['TXT_HOTELCARD_HOTEL_STATUS_'.$i];
        }
        return $arrStatus;
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

die("Hotel::errorHandler(): Disabled!<br />");

        $arrTables = $objDatabase->MetaTables('TABLES');
        if (in_array(DBPREFIX."module_hotelcard_hotel", $arrTables)) {
            $query = "DROP TABLE `".DBPREFIX."module_hotelcard_hotel`";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
//echo("Hotel::errorHandler(): Dropped table ".DBPREFIX."module_hotelcard_hotel<br />");
        }
        $query = "
            CREATE TABLE `".DBPREFIX."module_hotelcard_hotel` (
              `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `group_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Group or chain that the hotel belongs to.\nIf NULL, it is independent.',
              `accomodation_type_id` INT(10) UNSIGNED NULL DEFAULT NULL,
              `lang_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Preferred language.\nIs NULL if none is selected.',
              `image_id` INT(10) UNSIGNED NULL DEFAULT NULL,
              `rating` VARCHAR(3) NOT NULL DEFAULT '-',
              `recommended` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Defaults to zero.\nRecommended if non-zero. In that case, the value *SHOULD* be 1.',
              `numof_rooms` INT(5) UNSIGNED NULL DEFAULT NULL,
              `hotel_name` TINYTEXT NOT NULL DEFAULT '',
              `hotel_address` TINYTEXT NULL DEFAULT NULL,
              `hotel_location` TINYTEXT NULL DEFAULT NULL,
              `hotel_region` TINYTEXT NULL DEFAULT NULL,
              `description_text_id` INT(10) UNSIGNED NULL DEFAULT NULL,
              `policy_text_id` INT(10) UNSIGNED NULL DEFAULT NULL,
              `hotel_uri` TINYTEXT NULL DEFAULT NULL,
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
              `billing_location` TINYTEXT NULL DEFAULT NULL,
              `billing_country_id` INT(10) NULL DEFAULT NULL,
              `billing_tax_id` TINYTEXT NULL DEFAULT NULL,
              `checkin_from` TIME NULL DEFAULT '05:00',
              `checkin_to` TIME NULL DEFAULT '23:30',
              `checkout_from` TIME NULL DEFAULT '05:00',
              `checkout_to` TIME NULL DEFAULT '12:00',
              `comment` TEXT NULL DEFAULT NULL,
              `found_how` TEXT NULL DEFAULT NULL,
              `registration_time` INT(10) UNSIGNED NOT NULL DEFAULT 0,
              `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`),
              INDEX `hotel_type_id` (`accomodation_type_id` ASC),
              INDEX `hotel_group_id` (`group_id` ASC),
              INDEX `hotel_region` (`hotel_region`(8) ASC),
              INDEX `registration_time` (`registration_time` ASC)
            ) ENGINE=MYISAM";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
//echo("Hotel::errorHandler(): Created table ".DBPREFIX."module_hotelcard_hotel<br />");

        Text::deleteByKey(self::TEXT_GROUP);
        Text::deleteByKey(self::TEXT_DESCRIPTION);
        Text::deleteByKey(self::TEXT_POLICY);

        // More to come...

        // Always!
        return false;
    }

}

?>
