<?php

/**
 * Hotelcard Library
 *
 * Common front- and backend functionality
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.2.0
 * @package     contrexx
 * @subpackage  module_hotelcard
 */

/**
 * Hotelcard Library
 *
 * Common front- and backend functionality
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.2.0
 * @package     contrexx
 * @subpackage  module_hotelcard
 */
class HotelcardLibrary
{
    /**
     * Page template
     * @var HTML_Template_Sigma
     * @static
     */
    private static $objTemplate = false;

    /**
     * Image types for the module, see {@see Imagetype}
     */
    const IMAGETYPE_TITLE    = 'hotelcard_hotel_title';
    const IMAGETYPE_ROOM     = 'hotelcard_hotel_room';
    const IMAGETYPE_VICINITY = 'hotelcard_hotel_vicinity';
    const IMAGETYPE_LOBBY    = 'hotelcard_hotel_lobby';

    /**
     * The path of the default hotel image
     *
     * Used as long as there is none associated with the hotel
     */
    const IMAGE_PATH_HOTEL_DEFAULT = 'images/modules/hotelcard/no_image.jpg';

    /**
     * Style for marking invalid or incomplete form input
     */
    const INCOMPLETE_CLASS = 'error';

    /**
     * Message types
     */
    const MSG_OK      = 'ok';
    const MSG_INFO    = 'information';
    const MSG_WARNING = 'warning';
    const MSG_ERROR   = 'error';

    /**
     * Status message
     *
     * Frontend and backend.
     * @var   string
     * @static
     */
    private static $message = '';

    /**
     * Status message type
     *
     * Frontend only.
     * @var   string
     * @static
     */
    private static $message_type = '';

    /**
     * Error message
     *
     * Backend only.
     * @var     string
     * @static
     * @access  private
     */
    private static $error_message = '';


    /**
     * Format with HTML code for mandatory field marks
     */
    const MANDATORY_FIELD_HTML = '<span style="color: red;">&nbsp;*</span>';

    /**
     * Colors for available/unavailable rooms in the "red/green" view
     */
    const CLASS_ROOM_AVAILABLE_ON  = 'room_on';
    const CLASS_ROOM_AVAILABLE_OFF = 'room_off';

    /**
     * Set up the view of any single hotel
     * @param   HTML_Template_Sigma   $objTemplate    The Template object,
     *                                                by reference
     * @param   integer   $hotel_id       The Hotel ID
     * @return  boolean                   True on success, false otherwise
     */
    static function hotel_view(&$objTemplate, $hotel_id)
    {
        global $_ARRAYLANG;

        $objHotel = Hotel::getById($hotel_id);
        if (empty($objHotel)) {
            Hotelcard::addError(sprintf(
                $_ARRAYLANG['TXT_HOTELCARD_HOTEL_ID_NOT_FOUND'], $hotel_id
            ));
            return Hotelcard::hotel_overview();
        }
        $arrFields = array();
        $arrFieldnames = Hotel::getFieldnames();
        foreach ($arrFieldnames as $name) {
            $value = $objHotel->getFieldvalue($name);

            if (preg_match('/(?:^recommended$'.
                '|^reservation_|^accountant_|^billing_'.
                '|^checkin_|^checkout_'.
                // currently unused
                '|^policy_text_id$|^comment$'.
                ')/', $name)) {
                continue; // $value = Country::getNameById($value);
            }
            // Fix values that are IDs, special, or arrays of anything
            if (preg_match('/(?:group_id|description_text_id)$/', $name)) {
                $value = htmlentities(Text::getById(
                    $value, FRONTEND_LANG_ID)->getText(),
                    ENT_QUOTES, CONTREXX_CHARSET);
            } elseif (preg_match('/region$/', $name)) {
                $value = htmlentities(State::getFullname($value),
                    ENT_QUOTES, CONTREXX_CHARSET);
            } elseif (preg_match('/location$/', $name)) {
                // 204 is the country ID of switzerland
                $value = htmlentities(
                    $value.' '.Location::getCityByZip($value, 204),
                    ENT_QUOTES, CONTREXX_CHARSET);
            } elseif (preg_match('/^accomodation_type_id$/', $name)) {
                $value = htmlentities(
                    HotelAccomodationType::getNameById($value),
                    ENT_QUOTES, CONTREXX_CHARSET);
            } elseif (preg_match('/^lang_id$/', $name)) {
                $value = htmlentities(
                    FWLanguage::getLanguageParameter($value, 'name'),
                    ENT_QUOTES, CONTREXX_CHARSET);
            } elseif (preg_match('/contact_gender$/', $name)) {
                $value = (preg_match('/[wf]/i', $value)
                  ? $_ARRAYLANG['TXT_HOTELCARD_GENDER_FEMALE']
                  : $_ARRAYLANG['TXT_HOTELCARD_GENDER_MALE']);
            } elseif (preg_match('/^rating$/', $name)) {
                $value = HotelRating::getString_edit($value);
            } elseif (preg_match('/^image_id$/', $name)) {
// We can probably safely assume that all available pictures may be shown
//                $ord = (isset($_SESSION['hotelcard']['image_ord'])
//                    ? $_SESSION['hotelcard']['image_ord'] : 0);
////echo("Image ID $value, ord $ord<br />");
//                $objImage = Image::getById($value, $ord);
                $arrImage = Image::getArrayById($value, true);
                if (!is_array($arrImage)) {
//echo("Image array is invalid<br />");
                    continue;
                }
                $value = '';
                foreach ($arrImage as $objImage) {
                    if (!File::exists($objImage->getPath())) {
                        $objImage->delete();
                        continue;
                    }
//echo("Image path: ".$objImage->getPath());
//echo(" - fixed: ".$objImage->getPath()."<br />");
                    $value .= Html::getImageByPath(
                        (defined('BACKEND_LANG_ID') ? '../' : '').
                        $objImage->getPath()
                    ).'&nbsp;';
                }
//echo("Image HTML: ".htmlentities($value)."<br />");
                //preg_replace('/^[0-9a-f]+_/', '', $objImage->getPath());
            } elseif (preg_match('/^registration_time$/', $name)) {
                $value = date(ASCMS_DATE_FILE_FORMAT, $value);
            } elseif (preg_match('/^status$/', $name)) {
                $value =
                    Html::getLed(Hotel::getStatusColor($value),
                    Hotel::getStatusText($value));
            } elseif (preg_match('/(?:^numof_rooms$'.
                '|^hotel_name$|^hotel_address$|^hotel_uri$'.
                '|^contact_|^found_how$|^comment$|^country_id$)/', $name)) {
                $value = htmlentities($value, ENT_QUOTES, CONTREXX_CHARSET);
            } else {
//echo("Unhandled field name $name, value ".var_export($value, true)."<br />");
            }
            $name = 'TXT_HOTELCARD_'.strtoupper($name);
            if (empty($value)) {
//echo("Note: Empty value for $name<br />");
                $value = $_ARRAYLANG['TXT_HOTELCARD_NO_VALUE'];
            }
            if (empty($_ARRAYLANG[$name])) {
//echo("Note: No language entry for $name (value '$value')<br />");
                continue;
            }
//echo("Language variable $name => ".$_ARRAYLANG[$name]."<br />");
            $arrFields[$name] = array(
                'label' => $_ARRAYLANG[$name],
                'input' => $value,
            );
        }

        $arrHotelFacilities = HotelFacility::getFacilityNameArray();
        $arrHotelFacilitiesRelation = HotelFacility::getRelationArray($hotel_id); // self::$arrRelations[$hotel_id][$facility_id] = $facility_id;
        $strHotelFacilities = '';
        foreach ($arrHotelFacilitiesRelation[$hotel_id] as $facility_id) {
            $strHotelFacilities .=
                (empty($strHotelFacilities) ? '' : ', ').
                $arrHotelFacilities[$facility_id];
        }
        $arrFields['hotel_facility_id'] = array(
            'label' => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_FACILITY_ID'],
            'input' => htmlentities($strHotelFacilities,
                ENT_QUOTES, CONTREXX_CHARSET),
        );

        $arrRoomtypes = HotelRoom::getTypeArray($hotel_id, 0, 0);
//echo("room types: ".var_export($arrRoomtypes, true)."<br />");
        $strRoomtypes = '';
        $i = 0;
        foreach ($arrRoomtypes as $arrRoomtype) {
//echo("room type index $i + 1<br />");
            $strRoomFacilities = '';
            foreach ($arrRoomtype['facilities'] as $facility_id => $strFacilityName) {
//echo("room facility $strFacilityName<br />");
                $strRoomFacilities .=
                    (empty($strRoomFacilities) ? '' : ', ').
                    $strFacilityName;
            }
            $strRoomtypes .=
                ($strRoomtypes ? '<br />' : '').
                '<b>'.
                sprintf($_ARRAYLANG['TXT_HOTELCARD_ROOMTYPE_NUMBER_COLON'], ++$i).
                '</b><br />'.
                $_ARRAYLANG['TXT_HOTELCARD_ROOMTYPE'].': '.
                htmlentities($arrRoomtype['name'],
                    ENT_QUOTES, CONTREXX_CHARSET).'<br />'.
                $_ARRAYLANG['TXT_HOTELCARD_ROOM_AVAILABLE'].': '.
                $arrRoomtype['number_default'].'<br />'.
                $_ARRAYLANG['TXT_HOTELCARD_ROOM_PRICE'].': '.
                $arrRoomtype['price_default'].'<br />'.
                ($arrRoomtype['breakfast_included']
                  ? $_ARRAYLANG['TXT_HOTELCARD_BREAKFAST_INCLUDED']
                  : $_ARRAYLANG['TXT_HOTELCARD_BREAKFAST_NOT_INCLUDED']).'<br />'.
                $_ARRAYLANG['TXT_HOTELCARD_NUMOF_BEDS'].': '.
                $arrRoomtype['numof_beds'].'<br />'.
                $_ARRAYLANG['TXT_HOTELCARD_ROOM_FACILITY_ID'].': '.
                htmlentities($strRoomFacilities,
                    ENT_QUOTES, CONTREXX_CHARSET).'<br />';
        }
        $arrFields['room_types'] = array(
            'label' => $_ARRAYLANG['TXT_HOTELCARD_ROOMTYPES'],
            'input' => $strRoomtypes,
        );
// Not implemented
//            } elseif (preg_match('/^creditcard_id$/', $name)) {
//                $value = join(', ', $value);
        return self::parseDataTable($objTemplate, $arrFields);
    }


    /**
     * Parse and display the form data as provided
     *
     * The $arrFields argument must be an array of rows, with single rows
     * containing fields like this:
     *  array(
     *    'label' => 'Label text',
     *    'input' => 'Input form element or text',
     *    'mandatory' => boolean,
     *    'class' => 'optional_class_name',
     *    'error' => 'Optional error message to be added on top of the input',
     *    'special' => Custom line, shown as-is.  If this index is present,
     *                  any of the other are ignored.
     *  )
     * All fields are optional.
     * If both label and input are missing, the array is recursively scanned
     * for valid subrows with at least a label or an input.
     * If only the input is empty, the label is displayes as a header (<h2>).
     * If the mandatory flag evaluates to true, the label is marked with a
     * trailing non-breakable space, and an asterisk.
     * If the class value is not empty, it is inserted into the otherwise
     * empty class attribute of the label tag.
     * If the error value is not empty, it is added on top of the input
     * element or text.
     * @param   HTML_Template_Sigma   $objTemplate    The Template object,
     *                                                by reference
     * @param   array   $arrFields    The array of rows to be displayed
     * @return  boolean               True on success, false otherwise
     */
    static function parseDataTable(&$objTemplate, $arrFields)
    {
        global $_ARRAYLANG;
//echo("Hotelcard::parseDataTable(): Template:<br />".nl2br(htmlentities(var_export($objTemplate, true), ENT_QUOTES, CONTREXX_CHARSET))."<hr />");
        static $i = 0;

        foreach ($arrFields as $row_data) {
//echo("Hotelcard::parseDataTable(): row_data is ".(htmlentities(var_export($row_data, true), ENT_QUOTES, CONTREXX_CHARSET))."<br />");
            // This is not good and should never happen
            if (!is_array($row_data)) return false;
            // Some rows contain the 'special' index, which contains
            // HTML content that is inserted as-is
            if (isset($row_data['special'])) {
                $objTemplate->setVariable(
                    'HOTELCARD_DATA_SPECIAL', $row_data['special']);
                $objTemplate->parse('hotelcard_data');
                continue;
            }
            // Some other "rows" contain arrays of rows (i.e.,
            // hotel facilities).  Recurse into them
            if (   empty($row_data['label'])
                && empty($row_data['input'])) {
//echo("Hotelcard::parseDataTable(): &gt;&gt;&gt;<br />");
                self::parseDataTable($objTemplate, $row_data);
//echo("Hotelcard::parseDataTable(): &lt;&lt;&lt;<br />");
                continue;
            }
//echo("Language variable $label => ".$_ARRAYLANG[$label]."<br />");
            $mandatory = (empty($row_data['mandatory']) ? false : true);
            $label = (isset($row_data['label']) ? $row_data['label'] : '');
            $input = (isset($row_data['input']) ? $row_data['input'] : '');
            $class = (isset($row_data['class']) ? $row_data['class'] : '');
            $error = (isset($row_data['error']) ? $row_data['error'].'<br />' : '');
//echo("Hotelcard::parseDataTable(): class: $class<br />");
            if (empty($input)) {
//echo("Header: $label<hr />");
                // Parse header
                $objTemplate->setGlobalVariable(array(
                    'HOTELCARD_ROWCLASS' => '3',
                    'HOTELCARD_DATA_HEADER' =>
                        (preg_match('/^TXT_/', $label)
                            ? $_ARRAYLANG[$label] : $label)
                ));
                $objTemplate->touchBlock('hotelcard_header');
            } else {
//echo("Label: $label, input: $input<hr />");
                $objTemplate->setGlobalVariable(array(
                    'HOTELCARD_ROWCLASS' => ++$i % 2 + 1,
                    'HOTELCARD_DATA_LABEL' =>
                        (preg_match('/^TXT_/', $label)
                            ? (empty($_ARRAYLANG[$label.'_TOOLTIP'])
                                ? $_ARRAYLANG[$label]
                                : '<span id="tooltip-'.$i.'"'.
                                  ' onmouseover=\'Tip("'.
                                  $_ARRAYLANG[$label.'_TOOLTIP'].'",'.
//                                  'FADEIN,50,'.
//                                  'FADEOUT,1000,'.
                                  'LEFT,true,'.
                                  'WIDTH,200,'.
                                  'FIX,["tooltip-'.$i.'",-230,-21],'.
                                  'STICKY,true,'.
                                  'BGCOLOR,"#f0f8ff",'.
                                  'BORDERCOLOR,"#d0e0f0",'.
                                  'FONTCOLOR,"#000000");\''.
                                  ' onmouseout="UnTip();">'.
                                  Html::getImageByPath(
                                      'images/modules/hotelcard/question_mark.png',
                                      'alt=""').
                                  '&nbsp;'.
                                  $_ARRAYLANG[$label].'</span>')
//."Adding tip ".htmlentities($_ARRAYLANG[$label.'_TOOLTIP'])."<br />"
                            : $label).
                        ($mandatory
                            ? self::MANDATORY_FIELD_HTML : ''),
                    'HOTELCARD_DATA_LABEL_CLASS' =>
                        ($class ? ' class="'.$class.'"' : ''),
                    'HOTELCARD_DATA_INPUT' => $error.$input,
                ));
                $objTemplate->touchBlock('hotelcard_input');
            }
            $objTemplate->parse('hotelcard_data');
        }
//echo("Hotelcard::parseDataTable(): Template:<br />".nl2br(htmlentities(var_export($objTemplate, true), ENT_QUOTES, CONTREXX_CHARSET))."<hr />");
      return true;
    }


    /**
     * Stores any uploaded image files
     *
     * Each file is moved to the Hotel's image folder.
     * An Image object is created and stored.
     * The original file name and Image ID are stored in the session.
     * @return  mixed         The empty string if nothing was changed,
     *                        true on success, or false otherwise
     * @todo    Make this work properly with multiple files being uploaded.
     *          Currently, successive files will overwrite their predecessors!
     */
    static function processPostFiles()
    {
        global $_ARRAYLANG;
//echo("processPostFiles(): Entered<br />");

        if (empty($_SESSION['hotelcard']['hotel_id'])) {
//echo("processPostFiles(): No Hotel ID<br />");
            return false;
        }
//echo("processPostFiles(): Calling Image::processPostFiles().  Hotel ID ".$_SESSION['hotelcard']['hotel_id'].", Image ID ".$_SESSION['hotelcard']['image_id']."<br />");
        $result = Image::processPostFiles(
            ASCMS_HOTELCARD_IMAGES_FOLDER.'/'.
            $_SESSION['hotelcard']['hotel_id']
        );
        if ($result === '') {
//echo("No change to the image<br />");
            return '';
        }
        if ($result > 0) {
            // The Image ID is needed by the add_hotel wizard
            $_SESSION['hotelcard']['image_id'] = $result;
//echo("processPostFiles(): Stored Image.  Hotel ID ".$_SESSION['hotelcard']['hotel_id'].", Image ID ".$_SESSION['hotelcard']['image_id']."<br />");
            $objHotel = Hotel::getById($_SESSION['hotelcard']['hotel_id']);
            $objHotel->setFieldvalue('image_id', $result);
            if ($objHotel->store()) {
                self::addMessage($_ARRAYLANG['TXT_HOTELCARD_IMAGE_DATA_STORED_SUCCESSFULLY'], self::MSG_OK);
                return true;
            }
        }
//echo("processPostFiles(): Failed to store Image.  Hotel ID ".$_SESSION['hotelcard']['hotel_id'].", Image ID ".$_SESSION['hotelcard']['image_id']."<br />");
        self::addError($_ARRAYLANG['TXT_HOTELCARD_ERROR_STORING_IMAGE_DATA']);
        return false;
    }


    /**
     * Edit the hotel data
     *
     * Determines the hotel ID from the User field selected in the settings.
     * There is no way to fake this ID with the request, so no user may
     * change other than her own hotel data.
     * @todo    This might have to be adapted if any single user may be managing
     * more than one hotel at a time.  In that case, a list of hotels can be
     * shown instead.
     * @return  boolean             True on success, false otherwise
     */
    static function editHotel($objTemplate)
    {
        global $_ARRAYLANG;

        self::$objTemplate = $objTemplate;
        $objFWUser = FWUser::getFWUserObject();
        /** @var User */
        $objUser = $objFWUser->objUser;
        if (!$objUser) {
            CSRF::header('Location: index.php?section=hotelcard');
            exit;
        }
        $is_admin = $objUser->getAdminStatus();
        // The admin may choose any hotel she likes.
        // This is stored in the session anyway, so next time,
        // the ID is restored.
        $hotel_id = (isset($_REQUEST['hotel_id'])
            ? $_REQUEST['hotel_id']
            : (isset($_SESSION['hotelcard']['hotel_id'])
                ? $_SESSION['hotelcard']['hotel_id']
                : 0
        ));
        $hotel_ids = '';
        $arrHotelId = array();
        if (!$is_admin) {
            // The user is restricted to her hotel IDs
            SettingDb::init('admin');
            $attribute_id = SettingDb::getValue('user_profile_attribute_hotel_id');
            $hotel_ids = $objUser->getProfileAttribute($attribute_id);
            $arrHotelId = preg_split('/\s*,\s*/', $hotel_ids);
            if (empty($hotel_id)) $hotel_id = current($arrHotelId);
//echo("Hotel IDs: $hotel_ids, ID $hotel_id<br />");
            // No Hotel IDs in the profile, or the selected ID is not in the list
            if (   empty($hotel_ids)
                ||    $hotel_id
                   && !in_array($hotel_id, $arrHotelId)
            ) {
                CSRF::header('Location: index.php?section=hotelcard');
                exit();
            }
        }

        // This is read by some methods, like verifyAndStoreHotel();
        $_SESSION['hotelcard']['hotel_id'] = $hotel_id;
//echo("attribute ID $attribute_id, got hotel ID $hotel_id<br />");

        self::$objTemplate->setGlobalVariable(
            $_ARRAYLANG + array(
            'HOTELCARD_EDIT_HOTEL_MENU' =>
                // The subpage selection
                self::getEditHotelMenu().
                // The admin may choose a hotel from the menu in the frontend
//                ($is_admin && !defined('BACKEND_LANG_ID')
                // When there are multiple Hotels, the user may choose
                // one from the menu in the frontend only
                (   !defined('BACKEND_LANG_ID')
                 && ($is_admin || count($arrHotelId) > 1)
                  ? self::getHotelNameMenu($hotel_ids, $hotel_id, true)
                  : ''),
        ));

        if ($hotel_id) {
            switch ($_GET['act']) {
              case 'edit_hotel_roomtypes':
                $result = self::editHotelRoomtypes($hotel_id);
                break;
              case 'edit_hotel_contact':
                $result = self::editHotelContact($hotel_id);
                break;
              case 'edit_hotel_images':
                $result = self::editHotelImages($hotel_id);
                break;
              case 'edit_hotel_details':
                $result = self::editHotelDetails($hotel_id);
                break;
              case 'edit_hotel_facilities':
                $result = self::editHotelFacilities($hotel_id);
                break;
              default:
                $result = self::editHotelAvailability($hotel_id);
            }
            return $result;
        }
        return '';
    }


    /**
     * Shows the availablility of the different roomtypes for the hotel ID
     * specified and lets you edit them
     * @param   integer   $hotel_id     The selected hotel ID
     * @return  boolean                 True on success, false otherwise
     */
    static function editHotelAvailability($hotel_id)
    {
        global $_ARRAYLANG, $_CORELANG;

        if (isset($_REQUEST['click'])) {
            $id = $_REQUEST['click'];
            $status = (empty($_REQUEST['status']) ? 0 : 1);
            $number = (empty($_REQUEST['number']) ? 0 : $_REQUEST['number']);
            $match = array();
            if (preg_match('/_(\d+)_(\d+)_(\d+)_(\d+)$/i', $id, $match)) {
                die(HotelRoom::ajaxToggleAvailability(
                    // Hotel ID, room type ID, date (YYYY-MM-DD)
                    $hotel_id, $match[1],
                    $match[2].'-'.$match[3].'-'.$match[4],
                    $status, $number));
            } else {
                exit(0);
            }
        }
        if (isset($_REQUEST['change'])) {
            $id = $_REQUEST['change'];
            if (!preg_match('/_(\d+)_(\d+)_(\d+)_(\d+)$/i', $id, $match)) {
                exit(0);
            }
            $room_type_id = $match[1];
            $date = $match[2].'-'.$match[3].'-'.$match[4];
            $number = (isset($_REQUEST['number']) ? $_REQUEST['number'] : 0);
            $price = (isset($_REQUEST['price']) ? $_REQUEST['price'] : 0);
            $match = array();
                /* Hotel ID, room type ID, date (YYYY-MM-DD), value */
                die(HotelRoom::ajaxChangeAvailability(
                    $hotel_id, $room_type_id,
                    $date, $number, $price));
            } else {
        }

        $objHotel = Hotel::getById($hotel_id);
// NTH:  Maybe add a redirect
        if (empty($objHotel)) {
//DBG::log("editHotelAvailability($hotel_id): Hotel not found");
            self::addError($_ARRAYLANG['TXT_HOTELCARD_ERROR_HOTEL_ID_NOT_FOUND'].' '.$hotel_id);
            return false;
        }

        // Store changes, if any.
        // The type IDs and more information is taken from the post parameters.
        if (isset($_POST['bsubmit'])) self::updateHotel($hotel_id);
        // Verify the room numbers
        self::verifyAvailability(
            $hotel_id,
            (isset($_SESSION['hotelcard']['date_from'])
              ? $_SESSION['hotelcard']['date_from'] : 0),
            (isset($_SESSION['hotelcard']['date_to'])
              ? $_SESSION['hotelcard']['date_to'] : 0)
        );

        self::setSessionDateRangeFixed();
        // Abbreviations for day of the week
        //$arrDow = explode(',', $_CORELANG['TXT_CORE_DAY_ABBREV2_ARRAY']);
        $arrDow = explode(',', $_CORELANG['TXT_CORE_DAY_ARRAY']);
        $arrDow2 = explode(',', $_CORELANG['TXT_CORE_DAY_ABBREV2_ARRAY']);
        // Months of the year
        $arrMoy = explode(',', $_CORELANG['TXT_CORE_MONTH_ARRAY']);
        unset($arrMoy[0]);

        // Fetch the room types and availabilities
        $arrRoomTypes = HotelRoom::getTypeArray(
            $hotel_id,
            $_SESSION['hotelcard']['date_from'],
            $_SESSION['hotelcard']['date_to']
        );

        self::$objTemplate->setGlobalVariable(
            // Spray language variables all over
            $_ARRAYLANG
          + array(
            'HOTELCARD_HOTEL_ID' => $hotel_id,
            'HOTELCARD_DATE_FROM' => Html::getSelectDate(
                'date_from',
                date(ASCMS_DATE_SHORT_FORMAT,
                    $_SESSION['hotelcard']['date_from']),
                'style="width: 100px;"'),
            'HOTELCARD_DATE_TO' => Html::getSelectDate(
                'date_to',
                date(ASCMS_DATE_SHORT_FORMAT,
                    $_SESSION['hotelcard']['date_to']),
                'style="width: 100px;"'),
            // Datepicker language and settings
            'HOTELCARD_DPC_DEFAULT_FORMAT' => 'DD.MM.YYYY',
            'HOTELCARD_DPC_TODAY_TEXT'     => $_CORELANG['TXT_CORE_TODAY'],
            'HOTELCARD_DPC_BUTTON_TITLE'   => $_ARRAYLANG['TXT_HOTELCARD_OPEN_CALENDAR'],
            'HOTELCARD_DPC_MONTH_NAMES'    => "'".join("','", $arrMoy)."'",
            // Reformat from "Su,Mo,Tu,We,Th,Fr,Sa"
            // to "'Su','Mo','Tu','We','Th','Fr','Sa'"
            'HOTELCARD_DPC_DAY_NAMES'      => "'".join("','", $arrDow2)."'",
            'HOTELCARD_ROOMTYPE_INDEX_MAX' => count($arrRoomTypes),
            'HOTELCARD_FORM_SUBMIT_BUTTON_HALF'  =>
                Html::getInputButton('half',
                  $_ARRAYLANG['TXT_HOTELCARD_FORM_SUBMIT_HALF_YEAR_TIMERANGE']),
            'HOTELCARD_FORM_SUBMIT_BUTTON_FULL'  =>
                Html::getInputButton('full',
                  $_ARRAYLANG['TXT_HOTELCARD_FORM_SUBMIT_FULL_YEAR_TIMERANGE']),
            'HOTELCARD_FORM_SUBMIT_BUTTON_RANGE' =>
                Html::getInputButton('bsubmit',
                  $_ARRAYLANG['TXT_HOTELCARD_FORM_SUBMIT_SELECT_TIMERANGE']),
        ));

        // You can only edit dates that are after today
        $date_min = date('Y-m-d', time() + 86400);

        $time_start = $_SESSION['hotelcard']['date_from'];
        $time_end   = $_SESSION['hotelcard']['date_to'];

        // Data for all room types
        $arrRoomTypeAll = array(
            'name' => $_ARRAYLANG['TXT_HOTELCARD_ROOMTYPE_ALL'],
            'number_default' => 0,
            'price_default' => 0,
            'availabilities' => array(),
        );

        $index_li = 0;
        // Set up the lower list view first and sum up the available rooms
        // for the special "all rooms" type
        foreach ($arrRoomTypes as $room_type_id => $arrRoomType) {
            $room_type_name    = $arrRoomType['name'];
            $room_type_name_short = (strlen($room_type_name) > 20
                ? substr($room_type_name, 0, 18).'...' : $room_type_name);
            $number_default    = $arrRoomType['number_default'];
            $price_default     = $arrRoomType['price_default'];
            $arrAvailabilities = $arrRoomType['availabilities'];
//echo("roomtype $room_type_name, default number $number_default, price $price_default<br />");
            // Add the default to the "all rooms" type
            $arrRoomTypeAll['number_default'] += $number_default;

            // <li> switch and tab selection
            self::$objTemplate->setGlobalVariable(array(
                'HOTELCARD_ROOMTYPE_NAME_SHORT' =>
                    htmlentities($room_type_name_short, ENT_QUOTES, CONTREXX_CHARSET),
                'HOTELCARD_ROOMTYPE_NAME' =>
                    htmlentities($room_type_name, ENT_QUOTES, CONTREXX_CHARSET),
                'HOTELCARD_ROOMTYPE_INDEX' => ++$index_li,
                'HOTELCARD_ROOMTYPE_DISPLAY' => ($index_li == 1 ? 'block' : 'none'),
                'HOTELCARD_ROOMTYPE_LI_CLASS' => ($index_li == 1 ? '_active' : ''),
            ));
            self::$objTemplate->touchBlock('hotelcard_roomtype_li');
            self::$objTemplate->parse('hotelcard_roomtype_li');

            // Lower table room type availability views
            $first_date = true;
            for ($time = $time_start; //$_SESSION['hotelcard']['date_from'];
                $time <= $time_end;   //$_SESSION['hotelcard']['date_to'];
                $time += 86400
            ) {
                $date = date('Y-m-d', $time);
                // Special format date for JS properties
                $_date = date('Y_m_d', $time);

                // Heading for the red/green view: months and day numbers
                $day = date('j', $time);
                $month = $arrMoy[date('n', $time)];
                $year = date('Y', $time);
                $numof_days_month = date('t', $time);
//echo("time $time, date $date, day $day, month $month, year $year, numof_days_month $numof_days_month<br />");

                // Set up the month
                if ($first_date || $day == 1) {
                    self::$objTemplate->setGlobalVariable(array(
                        'HOTELCARD_CLASS'       => '3',
                        'HOTELCARD_DATE'        => "<b>$month $year</b>",
                        'HOTELCARD_DATE_DAY_OF_THE_WEEK' => '',
                        'HOTELCARD_ROOMS_TOTAL' => '',
                        'HOTELCARD_ROOM_PRICE'  => '',
                    ));
                    self::$objTemplate->touchBlock('hotelcard_day');
                    self::$objTemplate->parse('hotelcard_day');
                    $first_date = false;
                } //echo("time $time -> date $date<br />");

                // Current values for this room type on that date:
                // If there's no record, use the room defaults
                $number_total     = (empty($arrAvailabilities[$date])
                  ? $number_default : $arrAvailabilities[$date]['number_total']);
                $price            = (empty($arrAvailabilities[$date])
                  ? $price_default  : $arrAvailabilities[$date]['price']);
                // Add the availability the "all rooms" type
                if (!isset($arrRoomTypeAll['availabilities'][$date]['number_total'])) {
                    $arrRoomTypeAll['availabilities'][$date]['number_total'] = 0;
                    $arrRoomTypeAll['availabilities'][$date]['price'] = 0;
                }
                $arrRoomTypeAll['availabilities'][$date]['number_total'] += $number_total;
                // Day of the week, number and string abbreviation
                $intDow           = date('w', $time);
                $strDow           = $arrDow[$intDow];
                self::$objTemplate->setGlobalVariable(array(
                    'HOTELCARD_CLASS'     => ($intDow % 6 ? '1' : '2'),
                    'HOTELCARD_DATE'      => "$day.",
                    'HOTELCARD_DATE_DAY_OF_THE_WEEK' => $strDow,
                    'HOTELCARD_DATE_DAY_NUMBER'      => "$day.",
                    'HOTELCARD_ROOMS_TOTAL' =>
                        ($date >= $date_min
                          ? html::getInputText(
                              'availability['.$room_type_id.']['.$date.'][number_total]',
                              $number_total,
                              "availability_${room_type_id}_$_date",
                              'style="width: 40px; text-align: right;"'.
                              ' onchange="Toggler.change(\''.
                              "availability_${room_type_id}_$_date".'\')"')
                          : $number_total),
                    'HOTELCARD_ROOM_PRICE' =>
                        ($date >= $date_min
                          ? html::getInputText(
                              'availability['.$room_type_id.']['.$date.'][price]',
                              $price,
                              "price_${room_type_id}_$_date",
                              'style="width: 80px; text-align: right;"'.
                              ' onchange="Toggler.change(\''.
                              "price_${room_type_id}_$_date".'\')"')
                          : $price),
                ));
                self::$objTemplate->touchBlock('hotelcard_day');
                self::$objTemplate->parse('hotelcard_day');
            }
            // End of the lower day-by-day view of this room type
//            self::$objTemplate->touchBlock('hotelcard_roomtype');
            self::$objTemplate->parse('hotelcard_roomtype');
//echo("Current:<br />".nl2br(htmlentities(self::$objTemplate->get('hotelcard_roomtype_row')))."<hr />");
        }
//return true;

        // red/green view from here
        // Prepend the "all rooms" type to the room type array
        $arrRoomTypes = array(0 => $arrRoomTypeAll) + $arrRoomTypes;
//echo("Joint availabilities:<br />".nl2br(htmlentities(var_export($arrRoomTypes, true)))."<hr />");

        // Initial date values
        // These are incremented for each day or row
//        $day_start = date('j', $time_start);
//        $day_end   = date('j', $time_end);
//        $month_start = $arrMoy[date('n', $time_start)];
//        $month_end   = $arrMoy[date('n', $time_end)];
//        $year_start = date('Y', $time_start);
//        $year_end   = date('Y', $time_end);

        // Default values for the red/green view
        $arrValues = array(
            'key_off'   => 0,
            'key_on'    => 1,
            'title_off' => $_ARRAYLANG['TXT_HOTELCARD_ROOMS_AVAILABLE_NONE'],
            'title_on'  => $_ARRAYLANG['TXT_HOTELCARD_ROOMS_AVAILABLE_X'],
            'class_off' => self::CLASS_ROOM_AVAILABLE_OFF,
            'class_on'  => self::CLASS_ROOM_AVAILABLE_ON,
        );

        // Loop through four week periods
        $period = 28;
        // The last month of the last year must be recognized, so the rows
        // may be filled up
        $last_month = date('Y-n', $time_end);

        // and show all availabilities within that period
        for ($time_row = $time_start; //$_SESSION['hotelcard']['date_from'];
             $time_row <= $time_end;   //$_SESSION['hotelcard']['date_to'];
             $time_row += $period * 86400
        ) {
            // Number of days left on the current row.
            // Add one because it's decremented before anything is parsed below.
            $period_left = $period;
//            $day_first_of_row = date('j', $time_row);
//            $month_first_of_row = date('n', $time_row);

            // The row index starts at zero for the "all rooms" type
            $index_row = -1;
            // Set up basic room type information
            // and a row of the red/green view for each room type
            foreach ($arrRoomTypes as $room_type_id => $arrRoomType) {
                ++$index_row;
                $room_type_name    = $arrRoomType['name'];
                $room_type_name_short = (strlen($room_type_name) > 20
                    ? substr($room_type_name, 0, 18).'...' : $room_type_name);
                $number_default    = $arrRoomType['number_default'];
                $price_default     = $arrRoomType['price_default'];
                $arrAvailabilities = $arrRoomType['availabilities'];
                $arrValues['key_on'] = $number_default;
//echo("roomtype $room_type_name, default number $number_default, price $price_default<br />");
                self::$objTemplate->setGlobalVariable(array(
                    'HOTELCARD_ROOMTYPE_NAME_SHORT' =>
                        htmlentities($room_type_name_short, ENT_QUOTES, CONTREXX_CHARSET),
                    'HOTELCARD_ROOMTYPE_NAME' =>
                        htmlentities($room_type_name, ENT_QUOTES, CONTREXX_CHARSET),
                ));

                $colspan = 1;
                $first_date = true;
                // Set up a row for the period
                for ($time = $time_row;
                       $time < $time_row + ($period * 86400)
                    && $time <= $time_end;
                    $time += 86400
                ) {
                    // Day of the week, number and string abbreviation
                    $intDow = date('w', $time);
                    $date = date('Y-m-d', $time);
                    $_date = date('Y_m_d', $time);
                    // If this is the first row (happens to be the "all rooms"
                    // type), set up the month, year and date
                    if ($room_type_id == 0) {
                        --$period_left;
                        // Day of the week, number and string abbreviation
                        $strDow = $arrDow[$intDow];
                        $day = date('j', $time);

//echo("Date: $date, time row $time_row, Timediff ".($time_end - $time).", colspan $colspan<br />");
                        self::$objTemplate->setGlobalVariable(array(
                            'HOTELCARD_COLSPAN_DATE' => 1,
                            'HOTELCARD_CLASS' => ($intDow % 6 ? '1' : '2'),
                            'HOTELCARD_DATE'  => "$day.",
                            'HOTELCARD_DATE_DAY_OF_THE_WEEK' => $strDow,
                            'HOTELCARD_DATE_DAY_NUMBER' => "$day.",
                        ));
                        self::$objTemplate->touchBlock('hotelcard_day_date');
                        self::$objTemplate->parse('hotelcard_day_date');

                        // Set up the month only for the first day of the month
                        // or on a new line
                        if ($first_date == true || $day == 1) {
                            $month = date('n', $time);
                            $year = date('Y', $time);
                            $numof_days_month = date('t', $time);
                            $numof_days_month_left = ($numof_days_month-$day+1);
                            // At least four cells are needed to show
                            // the month name!
                            $colspan_month =
                                (   $numof_days_month_left > $period_left
                                 || "$year-$month" == $last_month
                              ? $period_left+1 : $numof_days_month_left);
                            self::$objTemplate->setGlobalVariable(array(
                                'HOTELCARD_COLSPAN_MONTH' => $colspan_month,
                                'HOTELCARD_DATE_MONTH'  => ($colspan_month > 4 ? $arrMoy[$month].'&nbsp;'.$year : '&nbsp;'),
                                'HOTELCARD_ROOMS_TOTAL' => '',
                                'HOTELCARD_ROOM_PRICE'  => '',
                            ));
                            self::$objTemplate->touchBlock('hotelcard_day_month');
                            self::$objTemplate->parse('hotelcard_day_month');
                            $first_date = false;
//echo("Set up month: time $time, date $date, day $day, month $month, year $year<br />numof_days_month $numof_days_month, numof_days_month_left $numof_days_month_left<br />last_month $last_month, colspan_month $colspan_month<hr />");
                        } //echo("time $time -> date $date<br />");
                    }

                    $number_total = (empty($arrAvailabilities[$date])
                        ? $number_default : $arrAvailabilities[$date]['number_total']);
                    $price = (empty($arrAvailabilities[$date])
                        ? $price_default  : $arrAvailabilities[$date]['price']);
//echo("roomtype $room_type_name, number total $number_total, price $price (default number $number_default, default price $price_default)<br />");
                    $arrValues["_${room_type_id}_$_date"] = array(
                        'key_on' =>
                            ($number_total ? $number_total : $number_default), );
//echo("Room type $room_type_id: $room_type_name: number_total $number_total, number_default $number_default -> key_on: ".$arrValues["_${room_type_id}_$_date"]['key_on']."<br />");
                    self::$objTemplate->setGlobalVariable(array(
                        'HOTELCARD_COLSPAN_ROOMTYPE' => $colspan,
                        'HOTELCARD_CLASS_COL_ROOMTYPE' => ($intDow % 6 ? '1' : '2'),
                        'HOTELCARD_ROOMS_AVAILABLE' =>
                            ($date >= $date_min
                              ? html::getToggle(
                                  'availability['.$room_type_id.']['.$date.'][available]',
                                  array(
                                      0 =>
                                          self::CLASS_ROOM_AVAILABLE_OFF,
                                      ($number_total ? $number_total : 1) =>
                                          self::CLASS_ROOM_AVAILABLE_ON,
                                  ),
                                  "available_${room_type_id}_$_date",
                                  "availability_${room_type_id}_$_date",
                                  $number_total,
                                  array(
                                      0 =>
                                          $_ARRAYLANG['TXT_HOTELCARD_ROOMS_AVAILABLE_NONE'],
                                      ($number_total ? $number_total : 1) =>
                                          sprintf(
                                              $_ARRAYLANG['TXT_HOTELCARD_ROOMS_AVAILABLE_X'],
                                              ($number_total ? $number_total : $number_default)
                                          ),
                                ))
                              : $number_total
                            ),
                    ));
                    self::$objTemplate->touchBlock('hotelcard_day_roomtype');
                    self::$objTemplate->parse('hotelcard_day_roomtype');
                }

                // Fill up the rows for the date and all room types
                if ($period_left > 0) {
//echo("Row finish: $period_left<hr />");
                    self::$objTemplate->setGlobalVariable(array(
                        'HOTELCARD_COLSPAN_DATE' => $period_left,
                        'HOTELCARD_CLASS' => 1,
                        'HOTELCARD_DATE'  => '&nbsp;',
                        'HOTELCARD_DATE_DAY_OF_THE_WEEK' => '',
                        'HOTELCARD_DATE_DAY_NUMBER' => '&nbsp;',

                        'HOTELCARD_COLSPAN_ROOMTYPE' => $period_left,
                        'HOTELCARD_CLASS_COL_ROOMTYPE' => 1,
                        'HOTELCARD_ROOMS_AVAILABLE' => '&nbsp;',
                    ));
                    if (!$room_type_id) {
                        self::$objTemplate->touchBlock('hotelcard_day_date');
                        self::$objTemplate->parse('hotelcard_day_date');
                    }
                    self::$objTemplate->touchBlock('hotelcard_day_roomtype');
                    self::$objTemplate->parse('hotelcard_day_roomtype');
                }

                self::$objTemplate->setGlobalVariable(array(
                    'HOTELCARD_CLASS_ROW_ROOMTYPE' =>
                        ($index_row > 0 ? (($index_row+1) % 2) + 1 : 3),
                    'HOTELCARD_ROOMTYPE_NAME' =>
                        htmlentities($room_type_name, ENT_QUOTES, CONTREXX_CHARSET),
                ));
                // End of the current row
                self::$objTemplate->touchBlock('hotelcard_row_roomtype');
                self::$objTemplate->parse('hotelcard_row_roomtype');
                self::$objTemplate->touchBlock('hotelcard_row');
                self::$objTemplate->parse('hotelcard_row');
//echo("Current:<br />".nl2br(htmlentities(self::$objTemplate->get('hotelcard_roomtype_row')))."<hr />");
            }
        }
//        self::$objTemplate->touchBlock('hotelcard_row');
//        self::$objTemplate->parse('hotelcard_row');
        // Requires DatePickerControl.js and .css
        JS::activate('datepicker');
        JS::registerCode(Html::getJavascript_Element());
        // The "All roomtypes" type (pseudo ID 0) is not needed below.
        unset($arrRoomTypes[0]);
        JS::registerCode(Html::getJavascript_Toggle(
            'available', 'availability', $arrValues, array_keys($arrRoomTypes)));
        return true;
    }


    /**
     * Shows the page for adding, editing, or deleting room types
     * @param   integer   $hotel_id     The selected hotel ID
     * @return  boolean                 True on success, false otherwise
     */
    static function editHotelRoomtypes($hotel_id)
    {
        global $_ARRAYLANG, $_CORELANG;

        // Store changes, if any.
        $result = '';
        if (isset($_POST['bsubmit'])) {
            // The type IDs and more information is taken from the post array
            $result = self::updateHotel($hotel_id);
        }
        if (isset($_REQUEST['delete_roomtype_id'])) {
            $room_type_id = $_REQUEST['delete_roomtype_id'];
            if (HotelRoom::deleteByHotelId($hotel_id, $room_type_id)) {
                self::addMessage($_ARRAYLANG['TXT_HOTELCARD_ROOMTYPE_DELETED_SUCCESSFULLY'], self::MSG_OK);
            } else {
                self::addError($_ARRAYLANG['TXT_HOTELCARD_ROOMTYPE_ERROR_DELETING']);
            }
        }
        // Do not reuse the posted values again after they have been stored!
        // Now they will be taken from the database.
        if ($result === true) unset($_POST);

        // Verify the room numbers
        self::verifyAvailability($hotel_id);

        // Fetch the room types and availabilities
        $arrRoomTypes = HotelRoom::getTypeArray($hotel_id);
        if (   //$result !== true &&
            count($arrRoomTypes) < 4) {
            $arrRoomTypes[-1] = array(
                'name' => (isset($_POST['roomtype'][-1]['room_type'])
                    ? $_POST['roomtype'][-1]['room_type'] : ''),
                'number_default' => (isset($_POST['roomtype'][-1]['number_default'])
                    ? $_POST['roomtype'][-1]['number_default'] : ''),
                'price_default' => (isset($_POST['roomtype'][-1]['price_default'])
                    ? $_POST['roomtype'][-1]['price_default'] : ''),
                'breakfast_included' => (isset($_POST['roomtype'][-1]['breakfast_included'])
                    ? $_POST['roomtype'][-1]['breakfast_included'] : ''),
                'numof_beds' => (isset($_POST['roomtype'][-1]['numof_beds'])
                    ? $_POST['roomtype'][-1]['numof_beds'] : 0),
                'facilities' => (isset($_POST['facilities'][-1])
                    ? $_POST['facilities'][-1] : array()),
            );
        }

        self::$objTemplate->setGlobalVariable(
            // Spray language variables all over
//            $_ARRAYLANG
//          +
            array(
            'HOTELCARD_FORM_SUBMIT_VALUE' => $_ARRAYLANG['TXT_HOTELCARD_FORM_SUBMIT_STORE'],
            'HOTELCARD_HOTEL_ID'          => $hotel_id,
            'HOTELCARD_ROOMTYPE_INDEX_MAX' => count($arrRoomTypes),
        ));

//        $arrFields = array(
//            'header' => array(
//                'label' => 'TXT_HOTELCARD_EDIT_HOTEL_ROOMTYPES',
//        ));
        // Complete list of all facilites for reference
        $arrAllFacilities = HotelRoom::getFacilityNameArray();
        $index_li = 0;
        foreach ($arrRoomTypes as $room_type_id => $arrRoomType) {
            $room_type_name     = $arrRoomType['name'];
            $room_type_name_short = (strlen($room_type_name) > 20
                ? substr($room_type_name, 0, 18).'...' : $room_type_name);
            $number_default     = $arrRoomType['number_default'];
            $price_default      = $arrRoomType['price_default'];
            $breakfast_included = $arrRoomType['breakfast_included'];
            $numof_beds         = $arrRoomType['numof_beds'];
            // <li> switch
            self::$objTemplate->setGlobalVariable(array(
                'HOTELCARD_ROOMTYPE_INDEX' => ++$index_li,
                'HOTELCARD_ROOMTYPE_DISPLAY' => ($index_li == 1 ? 'block' : 'none'),
                'HOTELCARD_ROOMTYPE_LI_CLASS' => ($index_li == 1 ? '_active' : ''),
                'HOTELCARD_ROOMTYPE_NAME_SHORT' =>
                    ($room_type_name_short
                      ? htmlentities($room_type_name_short, ENT_QUOTES, CONTREXX_CHARSET)
                      : $_ARRAYLANG['TXT_HOTELCARD_NEW_ROOMTYPE']),
                'HOTELCARD_ROOMTYPE_NAME' =>
                    ($room_type_name
                      ? htmlentities($room_type_name, ENT_QUOTES, CONTREXX_CHARSET)
                      : $_ARRAYLANG['TXT_HOTELCARD_NEW_ROOMTYPE']),
            ));
            self::$objTemplate->touchBlock('hotelcard_roomtype_li');
            self::$objTemplate->parse('hotelcard_roomtype_li');

            $arrFields['dummy_div_open_'.$index_li] = array(
                'special' =>
                    '<div id="roomtype-'.$index_li.'" style="display: '.
                    ($index_li == 1 ? 'block' : 'none').'">'
            );
            if (defined('BACKEND_LANG_ID')) {
                $arrFields['header'.$index_li] = array(
                    'label' =>
                        sprintf(
                          $_ARRAYLANG['TXT_HOTELCARD_ROOMTYPE_EDIT'],
                            ($room_type_name
                              ? $room_type_name
                              : $_ARRAYLANG['TXT_HOTELCARD_NEW_ROOMTYPE'])),
                );
            }
            if ($room_type_id > 0 && count($arrRoomTypes) > 2) {
                $arrFields['room_type_delete_'.$index_li] = array(
                    'label' => 'TXT_HOTELCARD_ROOMTYPE_DELETE',
                    'input' =>
//                        '<div style="width: 160px; text-align: left;">'.
                        Html::getBackendFunctions(
                            array(
                                'delete' => 'delete_roomtype_id='.$room_type_id,
                            ),
                            array(
                                'delete' =>
                                    $_ARRAYLANG['TXT_HOTELCARD_ROOMTYPE_DELETE_CONFIRM'].
                                    $_ARRAYLANG['TXT_HOTELCARD_ACTION_IS_IRREVERSIBLE'],
                            ), false)
//                            .'</div>'

                );
            }
            $arrFields += array(
                'room_type_'.$index_li => array(
                    'mandatory' => ($index_li == 1),
                    'label' => 'TXT_HOTELCARD_ROOMTYPE',
                    'input' => Html::getInputText(
                        'roomtype['.$room_type_id.'][room_type]',
                        $room_type_name, false,
                        (defined('BACKEND_LANG_ID')
                          ? 'style="width: '.DEFAULT_INPUT_WIDTH_BACKEND.'px;"' : '')),
                    'class' =>
                        (   $index_li > 1
                         || HotelRoom::validateRoomtypeName($room_type_name)
                          ? '' : self::INCOMPLETE_CLASS),
                ),
                'room_available_'.$index_li => array(
                    'mandatory' => ($index_li == 1),
                    'label' => 'TXT_HOTELCARD_ROOM_AVAILABLE',
                    'input' => Html::getInputText(
                        'roomtype['.$room_type_id.'][number_default]',
                        $number_default, false,
                        'style="text-align: right;'.
                        (defined('BACKEND_LANG_ID')
                          ? ' width: '.DEFAULT_INPUT_WIDTH_BACKEND.'px;'
                          : '').
                        '"'),
                    'class' =>
                        (   $index_li > 1 && empty($room_type_name)
                         || HotelRoom::validateRoomtypeNumber($number_default)
                          ? '' : self::INCOMPLETE_CLASS),
                ),
                'room_price_'.$index_li => array(
                    'mandatory' => ($index_li == 1),
                    'label' => 'TXT_HOTELCARD_ROOM_PRICE',
                    'input' => Html::getInputText(
                        'roomtype['.$room_type_id.'][price_default]',
                        $price_default, false,
                        'style="text-align: right;'.
                        (defined('BACKEND_LANG_ID')
                          ? ' width: '.DEFAULT_INPUT_WIDTH_BACKEND.'px;'
                          : '').
                        '"'),
                    'class' =>
                        (   $index_li > 1 && empty($room_type_name)
                         || HotelRoom::validateRoomtypePrice($price_default)
                          ? '' : self::INCOMPLETE_CLASS),
                ),
                'breakfast_included_'.$index_li => array(
                    'mandatory' => ($index_li == 1),
                    'label' => 'TXT_HOTELCARD_BREAKFAST_INCLUDED',
                    'input' => Html::getSelect(
                        'roomtype['.$room_type_id.'][breakfast_included]',
                        HotelRoom::getBreakfastIncludedArray($breakfast_included),
                        $breakfast_included, false, '',
                        (defined('BACKEND_LANG_ID')
                          ? 'style="width: '.DEFAULT_INPUT_WIDTH_BACKEND.'px;"' : '')),
                    'class' =>
                        (   $index_li > 1 && empty($room_type_name)
                         || $breakfast_included !== ''
                          ? '' : self::INCOMPLETE_CLASS),
                ),
                'numof_beds_'.$index_li => array(
                    'mandatory' => ($index_li == 1),
                    'label' => 'TXT_HOTELCARD_NUMOF_BEDS',
                    'input' => Html::getSelect(
                        'roomtype['.$room_type_id.'][numof_beds]',
                        HotelRoom::getNumofBedsArray($numof_beds),
                        $numof_beds, false, '',
                        (defined('BACKEND_LANG_ID')
                          ? 'style="width: '.DEFAULT_INPUT_WIDTH_BACKEND.'px;"' : '')),
                    'class' =>
                        (   $index_li > 1 && empty($room_type_name)
                         || $numof_beds != 0
                          ? '' : self::INCOMPLETE_CLASS),
                ),
                // Note: These are checkbox groups and are thus posted as
                // arrays, like 'room_facility_id_1[]'
                'room_facility_id_'.$index_li => array(
                    'label' => 'TXT_HOTELCARD_ROOM_FACILITY_ID',
                    'input' => '<span class="inputgroup">'.
                        Html::getCheckboxGroup(
                            'facilities['.$room_type_id.']',
                            $arrAllFacilities, $arrAllFacilities,
                            array_keys($arrRoomType['facilities']),
                            'facilities-'.$room_type_id,
                            '', (defined('BACKEND_LANG_ID') ? '<br />' : '')).
                        "</span>\n",
                ),
                'dummy_div_close_'.$index_li => array(
                    // This closes the div id=roomtype opened above
                    'special' => '</div>',
                ),
            );
        }
        self::parseDataTable(self::$objTemplate, $arrFields);
        JS::registerCode(Html::getJavascript_Element());
        return true;
    }


    /**
     * Shows the contact data for the Hotel for editing
     * @todo    Write me!
     * @param   integer   $hotel_id     The selected hotel ID
     * @return  boolean                 True on success, false otherwise
     */
    static function editHotelContact($hotel_id)
    {
        global $_ARRAYLANG;

        $arrFields = array(
            'contact_name' => array('mandatory' => true, ),
            'contact_gender' => array('mandatory' => true, ),
            'contact_position' => array(),
            'contact_department' => array(),
            'contact_phone' => array('mandatory' => true, ),
            'contact_fax' => array(),
            'contact_email' => array('mandatory' => true, ),
        );
        // Verify the data already present
        if (isset($_POST['bsubmit'])) {
            // Gobble up all posted data whatsoever
            foreach ($_POST as $key => $value) {
                $_SESSION['hotelcard'][$key] = (is_array($_POST[$key])
                    ? $value : contrexx_stripslashes($value));
            }
            if (self::verifyAndStoreHotel($arrFields)) {
//echo("After:<br />".htmlentities(var_export($arrFields, true), ENT_QUOTES, CONTREXX_CHARSET)."<hr />");
                self::addMessage($_ARRAYLANG['TXT_HOTELCARD_HOTEL_UPDATED_SUCCESSFULLY'], self::MSG_OK);
            }
        }

        $objHotel = Hotel::getById($hotel_id);
        if (empty($objHotel)) {
            self::addError($_ARRAYLANG['TXT_HOTELCARD_ERROR_HOTEL_ID_NOT_FOUND'].' '.$hotel_id);
            return false;
        }
        $arrFields = array(
            'header' => array(
                'label' => $_ARRAYLANG['TXT_HOTELCARD_EDIT_HOTEL_CONTACT'],
            ),
            'contact_name' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_CONTACT_NAME',
                'input' => Html::getInputText('contact_name',
                      ($objHotel->getFieldvalue('contact_name')
                        ? $objHotel->getFieldvalue('contact_name') : ''), false,
                        'style="text-align: left;'.
                        (defined('BACKEND_LANG_ID')
                          ? ' width: '.DEFAULT_INPUT_WIDTH_BACKEND.'px;'
                          : '').
                        '"'),
            ),
            'contact_gender' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_CONTACT_GENDER',
                'input' => '<span class="inputgroup">'.
                    Html::getRadioGroup(
                    'contact_gender',
                    array(
                        'M' => $_ARRAYLANG['TXT_HOTELCARD_GENDER_MALE'],
                        'F' => $_ARRAYLANG['TXT_HOTELCARD_GENDER_FEMALE'],
                    ),
                    ($objHotel->getFieldvalue('contact_gender')
                        ? $objHotel->getFieldvalue('contact_gender') : '')).
                    "</span>\n",
            ),
            'contact_position' => array(
                'label' => 'TXT_HOTELCARD_CONTACT_POSITION',
                'input' => Html::getInputText('contact_position',
                      ($objHotel->getFieldvalue('contact_position')
                        ? $objHotel->getFieldvalue('contact_position') : ''), false,
                        'style="text-align: left;'.
                        (defined('BACKEND_LANG_ID')
                          ? ' width: '.DEFAULT_INPUT_WIDTH_BACKEND.'px;'
                          : '').
                        '"'),
            ),
            'contact_department' => array(
                'label' => 'TXT_HOTELCARD_CONTACT_DEPARTMENT',
                'input' => Html::getInputText('contact_department',
                    ($objHotel->getFieldvalue('contact_department')
                        ? $objHotel->getFieldvalue('contact_department') : ''), false,
                    'style="text-align: left;'.
                    (defined('BACKEND_LANG_ID')
                      ? ' width: '.DEFAULT_INPUT_WIDTH_BACKEND.'px;'
                      : '').
                    '"'),
            ),
            'contact_phone' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_CONTACT_PHONE',
                'input' => Html::getInputText('contact_phone',
                    ($objHotel->getFieldvalue('contact_phone')
                        ? $objHotel->getFieldvalue('contact_phone') : ''), false,
                    'style="text-align: left;'.
                    (defined('BACKEND_LANG_ID')
                      ? ' width: '.DEFAULT_INPUT_WIDTH_BACKEND.'px;'
                      : '').
                    '"'),
            ),
            'contact_fax' => array(
                'label' => 'TXT_HOTELCARD_CONTACT_FAX',
                'input' => Html::getInputText('contact_fax',
                    ($objHotel->getFieldvalue('contact_fax')
                        ? $objHotel->getFieldvalue('contact_fax') : ''), false,
                    'style="text-align: left;'.
                    (defined('BACKEND_LANG_ID')
                      ? ' width: '.DEFAULT_INPUT_WIDTH_BACKEND.'px;'
                      : '').
                    '"'),
            ),
            'contact_email' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_CONTACT_EMAIL',
                'input' => Html::getInputText('contact_email',
                    ($objHotel->getFieldvalue('contact_email')
                        ? $objHotel->getFieldvalue('contact_email') : ''), false,
                    'style="text-align: left;'.
                    (defined('BACKEND_LANG_ID')
                      ? ' width: '.DEFAULT_INPUT_WIDTH_BACKEND.'px;'
                      : '').
                    '"'),
            ),
        );
        self::parseDataTable(self::$objTemplate, $arrFields);
        return false; // Still running
    }


    /**
     * Shows the page for uploading and deleting Hotel images
     *
     * The first picture is of the type "title", while all others' types
     * may be chosen from all available types except "title".
     * @param   integer   $hotel_id     The selected hotel ID
     * @return  boolean                 True on success, false otherwise
     */
    static function editHotelImages($hotel_id)
    {
        global $_ARRAYLANG;

        SettingDb::init();
        self::processPostFiles();

        $objHotel = Hotel::getById($hotel_id);
        if (empty($objHotel)) {
            self::addError($_ARRAYLANG['TXT_HOTELCARD_ERROR_HOTEL_ID_NOT_FOUND'].' '.$hotel_id);
            return false;
        }
        $arrFields = array(
            'header' => array(
                'label' => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_CHOOSE_IMAGES'],
        ));
        self::parseDataTable(self::$objTemplate, $arrFields);
        $image_id = $objHotel->getFieldvalue('image_id');
        $arrImages = Image::getArrayById($image_id);
        $arrImagetypeNames = Imagetype::getNameArray();
        unset ($arrImagetypeNames[self::IMAGETYPE_TITLE]);
        // Show all images; use the default image for empty ones
        for ($ord = 0; $ord < SettingDb::getValue('hotel_max_pictures'); ++$ord) {
//echo("self::editHotelImages($hotel_id): ord $ord of ".SettingDb::getValue('hotel_max_pictures')."<br />");
            $objImage = (empty($arrImages[$ord])
                ? new Image($ord, $image_id) : $arrImages[$ord]);
            $arrFields = array(
                'image_label_'.$ord => array(
                    'label' => '<b>'.sprintf(
                        $_ARRAYLANG['TXT_HOTELCARD_IMAGE_NUMBER'], $ord + 1).
                        '</b>',
//                    'input' => '&nbsp',
                ),
                'image_type_'.$ord => array(
                    'label' => 'TXT_HOTELCARD_IMAGE_TYPE',
                    'input' => Imagetype::getMenu(
                        'image-'.$ord, $objImage->getImagetypeKey(),
                        (empty($ord)
                          // The first one must be a "title"
                          ? self::IMAGETYPE_TITLE : $arrImagetypeNames))
                ),
                'hotel_image_id_'.$ord => array(
                    'mandatory' => empty($ord),
                    'label' => 'TXT_HOTELCARD_HOTEL_IMAGE_ID',
                    'input' => Html::getImageChooserUpload(
                        $objImage, 'image-'.$ord,
                        // NO imagetype menu
                        false,
                        self::IMAGE_PATH_HOTEL_DEFAULT, empty($ord)).
                        '<br /><br />'
                ),

            );
//echo("self::editHotelImages($hotel_id): Fields:<br />".nl2br(htmlentities(var_export($arrFields, true)))."<br />");
//echo("self::editHotelImages($hotel_id): now ord $ord<br />");
            self::parseDataTable(self::$objTemplate, $arrFields);
        }
        JS::registerCode(Html::getJavascript_Image(self::IMAGE_PATH_HOTEL_DEFAULT));
        return false; // Still running
    }


    /**
     * Shows the page for editing the Hotel details
     * @todo    Write me!
     * @param   integer   $hotel_id     The selected hotel ID
     * @return  boolean                 True on success, false otherwise
     */
    static function editHotelDetails($hotel_id)
    {
        global $_ARRAYLANG;

        $arrFields = array(
            'header' => array(
                'label' => $_ARRAYLANG['TXT_HOTELCARD_EDIT_HOTEL_DETAILS'],
            ),
            'hotel_name' => array('mandatory' => true, ),
            'group' => array(),
            'accomodation_type_id' => array('mandatory' => true, ),
            'hotel_address' => array(),
            'hotel_region' => array('mandatory' => true, ),
            'hotel_location' => array('mandatory' => true, ),
            'numof_rooms' => array('mandatory' => true, )
        );
        $arrLanguages = FWLanguage::getLanguageArray();
        foreach ($arrLanguages as $lang_id => &$arrLanguage) {
            // Languages active in the frontend only
            if (empty($arrLanguage['frontend'])) continue;
            $arrFields += array(
                'description_text_'.$lang_id => array('mandatory' => true, )
            );
        }
        $arrFields += array(
            'rating' => array('mandatory' => true, ),
            'hotel_uri' => array(),
        );
        // Verify the data already present
        if (isset($_POST['bsubmit'])) {
            // Gobble up all posted data whatsoever
            foreach ($_POST as $key => $value) {
                $_SESSION['hotelcard'][$key] = (is_array($_POST[$key])
                    ? $value : contrexx_stripslashes($value));
            }
            if (self::verifyAndStoreHotel($arrFields)) {
//echo("After:<br />".htmlentities(var_export($arrFields, true), ENT_QUOTES, CONTREXX_CHARSET)."<hr />");
                self::addMessage($_ARRAYLANG['TXT_HOTELCARD_HOTEL_UPDATED_SUCCESSFULLY'], self::MSG_OK);
            }
        }

        $objHotel = Hotel::getById($hotel_id);
// NTH:  Maybe add a redirect
        if (empty($objHotel)) {
//DBG::log("Error: Hotel ID $hotel_id not found")            ;
            self::addError($_ARRAYLANG['TXT_HOTELCARD_ERROR_HOTEL_ID_NOT_FOUND'].' '.$hotel_id);
            return false;
        }

//echo("BACKEND is ".(defined('BACKEND_LANG_ID') ? '' : 'NOT')." defined<br />");
//if (defined('BACKEND_LANG_ID')) { echo '<br />'; }
//echo BACKEND;
        $arrFields = array_merge_recursive($arrFields, array(
            'hotel_name' => array(
                'label' => 'TXT_HOTELCARD_HOTEL_NAME',
                'input' => Html::getInputText('hotel_name',
                    ($objHotel->getFieldvalue('hotel_name')
                        ? $objHotel->getFieldvalue('hotel_name') : ''), false,
                    'style="'.
                    (defined('BACKEND_LANG_ID')
                        ? 'width: '.DEFAULT_INPUT_WIDTH_BACKEND.'px; ' : '').
                    'text-align: left;"'),
            ),
            'group' => array( // Mind that this goes into a lookup table!
                'label' => 'TXT_HOTELCARD_GROUP',
                'input' => Html::getInputText('group',
                    ($objHotel->getFieldvalue('group')
                        ? $objHotel->getFieldvalue('group') : ''), false,
                    'style="'.
                    (defined('BACKEND_LANG_ID')
                        ? 'width: '.DEFAULT_INPUT_WIDTH_BACKEND.'px; ' : '').
                    'text-align: left;"'),
            ),
            'accomodation_type_id' => array(
                'label' => 'TXT_HOTELCARD_ACCOMODATION_TYPE_ID',
                'input' => Html::getSelect(
                    'accomodation_type_id',
                    HotelAccomodationType::getNameArray(),
                    ($objHotel->getFieldvalue('accomodation_type_id')
                        ? $objHotel->getFieldvalue('accomodation_type_id') : 0),
                    false, '',
                    (defined('BACKEND_LANG_ID')
                        ? 'style="width: '.DEFAULT_INPUT_WIDTH_BACKEND.'px;"' : '')),
            ),
            'hotel_address' => array(
                'label' => 'TXT_HOTELCARD_HOTEL_ADDRESS',
                'input' => Html::getInputText('hotel_address',
                    ($objHotel->getFieldvalue('hotel_address')
                        ? $objHotel->getFieldvalue('hotel_address') : ''), false,
                    'style="'.
                    (defined('BACKEND_LANG_ID')
                        ? 'width: '.DEFAULT_INPUT_WIDTH_BACKEND.'px; ' : '').
                    'text-align: left;"'),
            ),
            'hotel_region' => array(
                'label' => 'TXT_HOTELCARD_HOTEL_REGION',
                'input' => Html::getSelect(
                'hotel_region',
                    (isset($_SESSION['hotelcard']['hotel_region'])
                      ? array()
                      :   array('' => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_REGION_PLEASE_CHOOSE']))
                        + State::getArray(true),
                    ($objHotel->getFieldvalue('hotel_region')
                      ? $objHotel->getFieldvalue('hotel_region')
                      : ($objHotel->getFieldvalue('hotel_location')
                          ? State::getByLocation($objHotel->getFieldvalue('hotel_location'))
                          : '')), 'hotel_region',
                    'new Ajax.Updater(\'hotel_location\','.
                    ' \'index.php?section=hotelcard&amp;act=get_locations&amp;state=\''.
                    '+document.getElementById(\'hotel_region\').value, { method: \'get\' });',
                    (defined('BACKEND_LANG_ID')
                        ? 'style="width: '.DEFAULT_INPUT_WIDTH_BACKEND.'px;"' : '')),
            ),
            'hotel_location' => array(
                'label' => 'TXT_HOTELCARD_HOTEL_LOCATION',
                'input' => Html::getSelect('hotel_location',
                    ($objHotel->getFieldvalue('hotel_region')
                      ? Location::getArrayByState($objHotel->getFieldvalue('hotel_region'), '%1$s (%2$s)')
                      : array($_ARRAYLANG['TXT_HOTELCARD_HOTEL_LOCATION_PLEASE_CHOOSE_REGION'])),
                    ($objHotel->getFieldvalue('hotel_location')
                        ? $objHotel->getFieldvalue('hotel_location') : ''), 'hotel_location',
                    '', 'style="'.
                    (defined('BACKEND_LANG_ID')
                        ? 'width: '.DEFAULT_INPUT_WIDTH_BACKEND.'px; ' : '').
                    'text-align: left;"'),
            ),
            'numof_rooms' => array(
                'label' => 'TXT_HOTELCARD_NUMOF_ROOMS',
                'input' => Html::getInputText('numof_rooms',
                    ($objHotel->getFieldvalue('numof_rooms')
                        ? $objHotel->getFieldvalue('numof_rooms') : ''), false,
                    'style="'.
                    (defined('BACKEND_LANG_ID')
                        ? 'width: '.DEFAULT_INPUT_WIDTH_BACKEND.'px; ' : '').
                    'text-align: left;"'),
            ),
//            'description_text' => array(
//                'label' => 'TXT_HOTELCARD_DESCRIPTION_TEXT',
//                'input' => Html::getTextarea('description_text',
//                    ($objHotel->getFieldvalue('description_text')
//                        ? $objHotel->getFieldvalue('description_text') : ''),
//                    '', '',
//                    (defined('BACKEND_LANG_ID')
//                        ? 'style="width: '.DEFAULT_INPUT_WIDTH_BACKEND.'px;"' : '').
//                    ' onkeyup="lengthLimit(this, this.form.count_min, this.form.count_max, 100, 500);"').
//                '<br />'.
//                sprintf($_ARRAYLANG['TXT_HOTELCARD_TEXT_LENGTH_MINIMUM_MAXIMUM'],
//                    html::getInputText('count_min', 100, 'count_min',
//                        'disabled="disabled" style="width: 30px;"'),
//                    html::getInputText('count_max', 500, 'count_max',
//                        'disabled="disabled" style="width: 30px;"')),
//            ),
            'rating' => array(
                'label' => 'TXT_HOTELCARD_RATING',
                'input' => Html::getSelect('rating',
                    HotelRating::getArray(),
                    ($objHotel->getFieldvalue('rating')
                        ? $objHotel->getFieldvalue('rating') : ''),
                    false, '',
                    (defined('BACKEND_LANG_ID')
                        ? 'style="width: '.DEFAULT_INPUT_WIDTH_BACKEND.'px;"' : '')),
            ),
            'hotel_uri' => array(
                'label' => 'TXT_HOTELCARD_HOTEL_URI',
                'input' => Html::getInputText('hotel_uri',
                    ($objHotel->getFieldvalue('hotel_uri')
                        ? $objHotel->getFieldvalue('hotel_uri')
                        : ''), false,
                    'style="'.
                    (defined('BACKEND_LANG_ID')
                        ? 'width: '.DEFAULT_INPUT_WIDTH_BACKEND.'px; ' : '').
                    'text-align: left;"'),
            ),
        ));
        $arrLanguages = FWLanguage::getLanguageArray();
        foreach ($arrLanguages as $lang_id => &$arrLanguage) {
            // Languages active in the frontend only
            if (empty($arrLanguage['frontend'])) continue;
            $name = 'description_text_'.$lang_id;
            $arrFields = array_merge_recursive($arrFields, array(
                $name => array(
                    'label' => 'TXT_HOTELCARD_DESCRIPTION_TEXT_'.$lang_id,
                    'input' => Html::getTextarea($name,
                        $objHotel->getDescriptionByLanguageId($lang_id),
//                        ($objHotel->getFieldvalue('description_text')
//                            ? $objHotel->getFieldvalue('description_text') : ''),
                        '', '',
                        (defined('BACKEND_LANG_ID')
                            ? 'style="width: '.DEFAULT_INPUT_WIDTH_BACKEND.'px;"' : '').
                        ' onkeyup="lengthLimit(this, this.form.count_min_'.
                          $lang_id.', this.form.count_max_'.$lang_id.', 100, 500);"').
                    '<br />'.
                    sprintf($_ARRAYLANG['TXT_HOTELCARD_TEXT_LENGTH_MINIMUM_MAXIMUM'],
                        html::getInputText(
                            'count_min_'.$lang_id, 100, 'count_min_'.$lang_id,
                            'disabled="disabled" style="width: 30px;"'),
                        html::getInputText(
                            'count_max_'.$lang_id, 500, 'count_max_'.$lang_id,
                            'disabled="disabled" style="width: 30px;"')),
                ),
            ));
        }
//echo("Fields:<br />".nl2br(htmlentities(var_export($arrFields, true), ENT_QUOTES, CONTREXX_CHARSET))."<br />");
        self::parseDataTable(self::$objTemplate, $arrFields);
        JS::registerCode(Html::getJavascript_Text());
        return false; // Still running
    }


    /**
     * Shows the page for editing the Hotel details
     * @param   integer   $hotel_id     The selected hotel ID
     * @return  boolean                 True on success, false otherwise
     */
    static function editHotelFacilities($hotel_id)
    {
        global $_ARRAYLANG;

        // Store the data, if requested
        if (   isset($_POST['bsubmit'])) {
            if (HotelFacility::storeRelations(
                  $hotel_id, array_keys($_POST['hotel_facility_id']))) {
//echo("After:<br />".htmlentities(var_export($arrFields, true), ENT_QUOTES, CONTREXX_CHARSET)."<hr />");
                self::addMessage($_ARRAYLANG['TXT_HOTELCARD_HOTEL_UPDATED_SUCCESSFULLY'], self::MSG_OK);
            } else {
                self::addError($_ARRAYLANG['TXT_HOTELCARD_HOTEL_ERROR_UPDATING']);
            }
        }

//        self::$objTemplate->setGlobalVariable(
//            // Spray language variables all over
//            $_ARRAYLANG
//        );
        $arrFields = array(
            'header' => array(
                'label' => $_ARRAYLANG['TXT_HOTELCARD_EDIT_HOTEL_FACILITIES'],
        ));
        if (defined('BACKEND_LANG_ID')) {
            $arrFields['header'] = array(
                'label' => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_FACILITES_EDIT'],
            );
        }
        foreach (HotelFacility::getGroupNameArray() as $group_id => $group_name) {
//echo("Setting up group ID $group_id, name $group_name<br />");
            $arrFacilities = HotelFacility::getFacilityNameArray($group_id);
            // Skip empty groups
            if (empty($arrFacilities)) continue;
            $arrHotelFacilities = HotelFacility::getRelationArray($hotel_id);
            // In case that there are none, avoid the warning
            if (empty($arrHotelFacilities[$hotel_id]))
                $arrHotelFacilities[$hotel_id] = array();
//echo("Setting up Facilities: ".var_export($arrFacilities, true)."<br />");
            $arrFields['hotel_facility_id'][$group_id] = array(
                'label' => $group_name,
                'input' => '<span class="inputgroup">'.
                    Html::getCheckboxGroup(
                        'hotel_facility_id',
                        $arrFacilities, $arrFacilities,
                        array_keys($arrHotelFacilities[$hotel_id]),
                        '', '', (defined('BACKEND_LANG_ID') ? '<br />' : '')).
                    "</span>\n",
            );
        }
        self::parseDataTable(self::$objTemplate, $arrFields);
        return false; // Still running
    }


    /**
     * Store any changes made to a hotel in the database
     *
     * Picks any data available from the $_POST array and calls class methods
     * accordingly in order to add or update the database
     * @param   integer   $hotel_id     The hotel ID
     */
    static function updateHotel($hotel_id)
    {
        global $_ARRAYLANG;

        if (empty($_POST)) {
            return '';
        }

        $result = '';

        if (isset($_POST['roomtype'])) {
            foreach ($_POST['roomtype'] as $room_type_id => $arrRoomtype) {
                // Array indexed by room type IDs,
                // containing room type parameters:
                //  roomtype[room_type_id] =>
                //    array(number_total, number_booked, number_cancelled, price)
                $room_type = contrexx_stripslashes($arrRoomtype['room_type']);
                // Ignore room types with empty names
                if (empty($room_type)) {
                    continue;
                }

                $number_default = $arrRoomtype['number_default'];
                $price_default = $arrRoomtype['price_default'];
                $breakfast_included = $arrRoomtype['breakfast_included'];
                $numof_beds = $arrRoomtype['numof_beds'];

                // The ID will change if the room type is inserted, but we
                // still need the posted one below
                $room_type_id_new = HotelRoom::storeType(
                    $hotel_id,
                    $number_default, $price_default,
                    $breakfast_included, $numof_beds,
                    ($room_type_id > 0 ? $room_type_id : 0)
                );
                if (   !$room_type_id_new
                    || !HotelRoom::renameType($room_type_id_new, $room_type)) {
                    self::addError(sprintf(
                        $_ARRAYLANG['TXT_HOTELCARD_ERROR_STORING_ROOMTYPE'],
                        $room_type)
                    );
                    return false;
                }
                if ($result === '') $result = true;

                // Store facilities
                // Note that the posted room type ID is negative for new
                // room types.
                if (empty($_POST['facilities'][$room_type_id])) continue;
                if (!HotelRoom::storeFacilities(
                    $room_type_id_new, $_POST['facilities'][$room_type_id])) {
                    self::addError(sprintf(
                        $_ARRAYLANG['TXT_HOTELCARD_ERROR_STORING_ROOMTYPE_FACILITIES'],
                        $room_type)
                    );
                    return false;
                }
                if ($result === '') $result = true;
            }
        }

        if (isset($_POST['availability'])) {
            foreach ($_POST['availability'] as $room_type_id => $arrAvailability) {
                // Array indexed by room type IDs,
                // containing date => availability pairs:
                //  availability[room_type_id][date] =>
                //    array(number_total, number_booked, number_cancelled, price)

                if (!HotelRoom::storeAvailabilityArray(
                    $room_type_id, $arrAvailability)
                ) {
                    $arrRoomtype = HotelRoom::getTypeArray($hotel_id);
                    self::addError(sprintf(
                        $_ARRAYLANG['TXT_HOTELCARD_ERROR_STORING_AVAILABILITY'],
                        $$arrRoomtype[$room_type_id]['name'])
                    );
                    return false;
                }
                if ($result === '') $result = true;
            }
        }
        if ($result === true) {
            self::addMessage(
                $_ARRAYLANG['TXT_HOTELCARD_HOTEL_UPDATED_SUCCESSFULLY'], self::MSG_OK);
        }
        return true;
    }


    /**
     * Verifies that the fields contain some proper values
     *
     * For any missing or invalid values, adds the 'class' index to the
     * subarrays in the array given by reference  to some attribute string
     * which should be added as the attribute parameter when creating the
     * HTML elements later.
     * @param   array   $arrFields    The array with field names as keys
     * @return  void
     */
    static function verifyAndStoreHotel(&$arrFields)
    {
        global $_ARRAYLANG;

        // Do not validate if the current step is shown for the first time,
        // but return false immediately
        if (   isset($_REQUEST['cmd'])
            && $_REQUEST['cmd'] == 'add_hotel'
            && $_SESSION['hotelcard']['step_posted'] != $_SESSION['hotelcard']['step_current'])
            return false;

        // This variable is reported as being "never used" two times(!)
        // by the code analyzer.  Ignore that sucker...
        $complete = true;

        $hotel_id =
            (isset($_SESSION['hotelcard']['hotel_id'])
                ? $_SESSION['hotelcard']['hotel_id'] : 0);
        $objHotel = Hotel::getById($hotel_id);
        if (!$objHotel) {
            $objHotel = new Hotel();
        }

        foreach ($arrFields as $name => $row_data) {
            // Ignore "dummy" values.
            // Note that it's not mandatory to filter those here; they would
            // just pass through.  However, this eases debugging.
            if (preg_match(
                // Headings, no input
                '/^(?:contact_data|hotel_facilities'.
                '|room_type_data_\d'.
                // Dummies, no input
                '|dummy_'.
                // register_date is a generated field, not editable
                '|register_date)/',
                $name)
            ) continue;

            $value = (isset($_SESSION['hotelcard'][$name])
                ? $_SESSION['hotelcard'][$name] : '');

            // Test if the value is valid whether it's mandatory or not.
            // First, try to set the parameter in the hotel object.
            // If false is returned, it's a Hotel field, but the value
            // is rejected.
            if ($objHotel->setFieldvalue($name, $value)) {
                if ($objHotel->getFieldvalue($name) !== null) {
                    // Value has been accepted by the Hotel class;
                    // update the session with the actual value
                    $_SESSION['hotelcard'][$name] =
                        $objHotel->getFieldvalue($name);
                    continue;
                }
                // else... The value was accepted but did not change the
                // default null value, so the Hotel simply wasn't interested.
                // The non-Hotel cases are handled below.
            } else {
//DBG::log("Hotel rejected $name value $value");
                // The value has been rejected
                // If the field is empty, but not mandatory, just ignore it
                if (empty($row_data['mandatory']) && empty($value))
                    continue;
                $complete = false;
                $arrFields[$name]['class'] = self::INCOMPLETE_CLASS;
                continue;
            }

            $arrMatch = array();
            $result = false;

            // Store the Hotel description in foreign languages, if present.
            // Note that the Hotel class only store()s the current frontend
            // language present in the object, so we use custom methods for that.
            // Also note that this won't work until the Hotel is already
            // present in the database, so the text *MUST NOT* be posted
            // from the add_hotel/step 1 page.
            if (preg_match(
                '/^description_text_(\d+)$/', $name, $arrMatch)
            ) {
                // If the value is not mandatory and empty, it has already
                // been skipped above.  This means, however, that optional
                // empty texts would never be updated at all!
                $result = $objHotel->updateDescriptionByLanguageId(
                    $arrMatch[1], $value);
//DBG::log("Updating Description, language ".$arrMatch[1].", Result: ".($result ? "OK" : "FAILED"));
            }

            // For all cases below, if a trailing index is present in the name,
            // it has nothing to do with languages, but indicates instances
            // of various room types.  Their number is fixed, other than the
            // number of languages!
            $index = 0;
            if (preg_match('/_(\d)$/', $name, $arrMatch)) {
                $index = $arrMatch[1];
            }
            // All the remaining special cases:
            switch ($name) {
              case 'image_id':
              case 'bsubmit':
                // Image and submit buttons: always let them pass.
                $result = true;
                break;
              case 'hotel_facility_id':
                $result = HotelFacility::validateFacilityIdArray($value);
                break;
              case 'room_type_1':
              case 'room_type_2':
              case 'room_type_3':
              case 'room_type_4':
                $result = HotelRoom::validateRoomtypeName($value);
                break;
              case 'room_available_1':
              case 'room_available_2':
              case 'room_available_3':
              case 'room_available_4':
                $result = HotelRoom::validateRoomtypeNumber($value);
                if (empty($_SESSION['hotelcard']['room_type_'.$index])) {
                    continue 2;
                }
                // Set $value to anything non-empty and invalid if it's needed,
                // so the illegal value is recognised below.
                if (!$result) $value = 'invalid';
                break;
              case 'room_price_1':
              case 'room_price_2':
              case 'room_price_3':
              case 'room_price_4':
                // Prices for i > 1 must be considered invalid if zero
                // only if they are in use
                if (empty($_SESSION['hotelcard']['room_type_'.$index])) {
                    continue 2;
                }
                $result = HotelRoom::validateRoomtypePrice($value);
                break;
              case 'room_facility_id_1':
              case 'room_facility_id_2':
              case 'room_facility_id_3':
              case 'room_facility_id_4':
                $result = HotelFacility::validateFacilityIdArray($value);
                break;
              case 'breakfast_included_1':
              case 'breakfast_included_2':
              case 'breakfast_included_3':
              case 'breakfast_included_4':
                  $result = ($value !== '');
                if (empty($_SESSION['hotelcard']['room_type_'.$index])) {
                    continue;
                }
                // Set $value to anything non-empty and invalid if it's needed,
                // so the illegal value is recognised below.
                if (!$result) $value = 'invalid';
                break;
              case 'numof_beds_1':
              case 'numof_beds_2':
              case 'numof_beds_3':
              case 'numof_beds_4':
                  $result = ($value > 0);
                if (empty($_SESSION['hotelcard']['room_type_'.$index])) {
                    continue;
                }
                // Set $value to anything non-empty and invalid if it's needed,
                // so the illegal value is recognised below.
                if (!$result) $value = 'invalid';
                break;
              case 'confirm_terms':
                $result = (!empty($value));
                break;
              default:
//echo("Hotelcard::verifyAndStoreHotel(): WARNING: Missed name $name: ".var_export($value, true)."<br />");
            }
            // The value may have been fixed by the verification method
            $_SESSION['hotelcard'][$name] = $value;
            if ($result) continue;

            // Don't bother if it's empty and not mandatory
            if (empty($row_data['mandatory']) && empty($value)) {
//echo("Hotelcard::verifyAndStoreHotel(): Ignored empty non-mandatory field $name value '$value'<br />");
                continue;
            }
            // Mandatory fields must not be empty
//echo("Hotelcard::verifyAndStoreHotel(): Rejected empty mandatory field $name value '$value'<br />");
            $complete = false;
            $arrFields[$name]['class'] = self::INCOMPLETE_CLASS;
        }
        if (!$complete) {
            self::addError($_ARRAYLANG['TXT_HOTELCARD_MISSING_MANDATORY_DATA']);
            return false;
        }

        // Store the Hotel
        $hotel_id = $objHotel->store();
        if (!$hotel_id) {
            self::addError($_ARRAYLANG['TXT_HOTELCARD_ERROR_STORING_HOTEL']);
            return false;
        }

        $_SESSION['hotelcard']['hotel_id'] = $hotel_id;
        $_SESSION['hotelcard']['registration_time'] =
            $objHotel->getFieldvalue('registration_time');
//echo("Stored Hotel, ID in session: ".$_SESSION['hotelcard']['hotel_id']."<br />");

        // Store the hotel facilities, if present
        if (isset($_SESSION['hotelcard']['hotel_facility_id'])
            && is_array($_SESSION['hotelcard']['hotel_facility_id'])) {
            // Clear all relations, then add the current ones
            if (!HotelFacility::deleteByHotelId($hotel_id)) {
//echo("ERROR: Failed to delete Hotel Facilities for Hotel ID $hotel_id<br />");
                return false;
            }
            foreach (array_keys($_SESSION['hotelcard']['hotel_facility_id']) as $hotel_facility_id) {
                if (HotelFacility::addRelation(
                    $hotel_id, $hotel_facility_id)) continue;
//echo("ERROR: Failed to store Hotel Facilities for Hotel ID $hotel_id<br />");
                Hotelcard::addError(sprintf(
                    $_ARRAYLANG['TXT_HOTELCARD_ERROR_FAILED_TO_ADD_HOTEL_FACILITY'],
                    HotelFacility::getFacilityNameById($hotel_facility_id)));
                return false;
            }
//echo("Stored Hotel Facilities for Hotel ID $hotel_id<br />");
        }

        // Store the room types, if present
        if (isset($_SESSION['hotelcard']['room_type_1'])) {
            // Clear all room type and related room facility data,
            // then add the current
            if (!HotelRoom::deleteByHotelId($hotel_id)) {
//echo("ERROR: Failed to delete Roomtypes for Hotel ID $hotel_id<br />");
                return false;
            }
            for ($i = 1; $i <= 4; ++$i) {
//echo("Adding Room Type $i:<br />");
                // Skip types without a name
                if (   empty($_SESSION['hotelcard']['room_type_'.$i])
                    || empty($_SESSION['hotelcard']['room_available_'.$i])
                    || empty($_SESSION['hotelcard']['room_price_'.$i]))
                    continue;
//echo("Room Type ".$_SESSION['hotelcard']['room_type_'.$i].", number ".$_SESSION['hotelcard']['room_available_'.$i].", price ".$_SESSION['hotelcard']['room_price_'.$i]."<br />");
                $room_type_id = HotelRoom::storeType(
                    $hotel_id,
                    $_SESSION['hotelcard']['room_available_'.$i],
                    $_SESSION['hotelcard']['room_price_'.$i],
                    (empty($_SESSION['hotelcard']['breakfast_included_'.$i])
                      ? false : true),
                    $_SESSION['hotelcard']['numof_beds_'.$i]
                );
                if (!$room_type_id) {
                    Hotelcard::addError(sprintf(
                        $_ARRAYLANG['TXT_HOTELCARD_ERROR_FAILED_TO_ADD_ROOMTYPE'],
                        $_SESSION['hotelcard']['room_type_'.$i]));
//echo("ERROR: Failed to add Roomtypes for Hotel ID $hotel_id<br />");
                    return false;
                }
                // Rename the room type
                if (!HotelRoom::renameType(
                    $room_type_id, $_SESSION['hotelcard']['room_type_'.$i])) {
//echo("ERROR: Failed to rename Roomtype ID $room_type_id to ".$_SESSION['hotelcard']['room_type_'.$i]."<br />");
                    return false;
                }

                // Store the room facilities
                foreach ($_SESSION['hotelcard']['room_facility_id_'.$i]
                        as $room_facility_id => $room_facility_name) {
//echo("Adding Room Facility $room_facility_name (ID $room_facility_id)<br />");
                    if (HotelRoom::addFacility(
                        $room_type_id, $room_facility_id)) continue;
//echo("ERROR: Failed to add Room Facilites for Hotel ID $hotel_id<br />");
                    Hotelcard::addError(sprintf(
                        $_ARRAYLANG['TXT_HOTELCARD_ERROR_FAILED_TO_ADD_ROOM_FACILITY'],
                        $room_facility_name));
                    return false;
                }
            }
            // Verify the room numbers
            if (!self::verifyAvailability($hotel_id))
                $complete = false;
//echo("Stored Room Types and Facilites for Hotel ID $hotel_id<br />");
        }
        return $complete;
    }


    /**
     * Verifies whether the number of available rooms is great enough
     *
     * Displays a message if not.
     * The Hotel status is updated according to the result, see
     * {@see HotelRoom::hasMinimumRoomsAvailable()}.
     * @param   integer   $hotel_id         The Hotel ID
     * @param   integer   $time_from        The lower bound of the time range
     * @param   integer   $time_to          The upper bound of the time range
     * @return  boolean                     True if the available rooms
     *                                      are okay, false otherwise
     */
    static function verifyAvailability($hotel_id, $time_from=0, $time_to=0)
    {
        global $_ARRAYLANG;

        $result = HotelRoom::hasMinimumRoomsAvailable(
            $hotel_id,
            $time_from, $time_to
        );
        if ($result === true) return true;
        if ($result === false) {
            self::addError(
                $_ARRAYLANG['TXT_HOTELCARD_ROOM_AVAILABLE_TOO_LOW'],
                self::MSG_ERROR);
        }
        if (is_array($result)) {
            $dates = '';
            foreach (array_keys($result) as $date) {
                $dates .=
                    ($dates ? ', ' : '').
                    date(ASCMS_DATE_SHORT_FORMAT, strtotime($date));
            }
            self::addError(
                sprintf(
                    $_ARRAYLANG['TXT_HOTELCARD_ROOM_AVAILABLE_TOO_LOW_DATES_COLON'],
                    $dates),
                self::MSG_ERROR);
        }
        return false;
    }


    /**
     * Picks the date range from the request if set, and fix invalid values
     *
     * The current values are stored in the session array:
     * $_SESSION['hotelcard']['date_from']
     * $_SESSION['hotelcard']['date_to']
     * @return  void
     */
    static function setSessionDateRangeFixed()
    {
//echo("setSessionDateRangeFixed(): post: ".var_export($_POST, true)."<br />");
        // Range for the dates to be shown.
        // Problems may occur if we work with times too close to midnight here,
        // probably because of DST switching.  Thus, two hours are added to
        // the times stored in the session.  This won't affect the dates
        // being shown and stored.
        $_SESSION['hotelcard']['date_from'] = (isset($_REQUEST['date_from'])
            ? strtotime($_REQUEST['date_from']) + 7200
            : (isset($_SESSION['hotelcard']['date_from'])
                ? $_SESSION['hotelcard']['date_from']
                : strtotime('tomorrow') + 7200));

        // Verify and fix the time range:
        // Dates must not be before 01.01.2010 02:00:00,
        // which is a time() of 1262307600
        if (  $_SESSION['hotelcard']['date_from'] < 1262307600)
            $_SESSION['hotelcard']['date_from'] = 1262307600 + 7200;
        // Limit the start date to dates after today, but within a year
        if (  $_SESSION['hotelcard']['date_from'] < time() + 86400)
            $_SESSION['hotelcard']['date_from'] = time() + 86400 + 7200;
        if (  $_SESSION['hotelcard']['date_from'] > time() + 86400*366)
            $_SESSION['hotelcard']['date_from'] = time() + 86400*366 + 7200;

        $_SESSION['hotelcard']['date_to'] = (isset($_REQUEST['date_to'])
            ? strtotime($_REQUEST['date_to']) + 7200
            : (isset($_SESSION['hotelcard']['date_to'])
                ? $_SESSION['hotelcard']['date_to']
                : $_SESSION['hotelcard']['date_from'] + 86400*83));

        // Special buttons for half and full year ranges
        if (isset($_POST['half']))
            $_SESSION['hotelcard']['date_to'] =
                $_SESSION['hotelcard']['date_from'] + 86400*183;
        if (isset($_POST['full']))
            $_SESSION['hotelcard']['date_to'] =
                $_SESSION['hotelcard']['date_from'] + 86400*365;

        // Set the end to 366 days later if the range is more than that
        if (  $_SESSION['hotelcard']['date_from'] + 86400*366
            < $_SESSION['hotelcard']['date_to'])
            $_SESSION['hotelcard']['date_to'] =
                $_SESSION['hotelcard']['date_from'] + 86400*366;
        // Set the end to 56 days later if the range is negative
        if (  $_SESSION['hotelcard']['date_from']
            > $_SESSION['hotelcard']['date_to'])
            $_SESSION['hotelcard']['date_to'] =
                $_SESSION['hotelcard']['date_from'] + 86400*56;
//echo("Date from ".$_SESSION['hotelcard']['date_from'].", to ".$_SESSION['hotelcard']['date_to']."<br />");
    }


    /**
     * Returns HTML code for a Hotel selection dropdown menu
     *
     * When changed, the page is automatically reloaded with the same
     * URI plus the selected Hotel ID
     * @param   string    $hotel_ids    The optional comma seaparated list of
     *                                  Hotel IDs available to the user.
     *                                  Defaults to the empty string
     * @param   integer   $hotel_id     The optional preselected Hotel ID
     * @param   boolean   $include_id   If true, the Hotel ID is appended
     *                                  to the name, in parentheses.
     *                                  Defaults to false
     * @return  string                  The HTML code for the dropdown menu
     */
    static function getHotelNameMenu(
        $hotel_ids='', $hotel_id='', $include_id=false
    ) {
        $uri = Html::getRelativeUri_entities();
//echo("Hotelcard::getHotelNameMenu(): URI 1: ".htmlentities($uri)."<br />");
        Html::stripUriParam($uri, 'hotel_id');
//echo("Hotelcard::getHotelNameMenu(): URI 2: ".htmlentities($uri)."<br />");
        return Html::getForm(
            'formHotelMenu', $uri,
            Html::getSelect(
                'hotel_id',
                Hotel::getNameArray($hotel_ids, $include_id),
                $hotel_id, false,
                'document.formHotelMenu.submit();'
            )
        );
    }


    /**
     * Returns HTML code for the menu that lets the user choose what
     * part of the Hotel data she wants to edit
     *
     * Expects the Hotel ID to be present and valid in
     * $_SESSION['hotelcard']['hotel_id'].  Fails otherwise and returns
     * the emtpy string.
     * @return  string              The Hotel edit menu on success,
     *                              the empty string otherwise
     */
    static function getEditHotelMenu()
    {
        global $_ARRAYLANG;

        if (empty($_SESSION['hotelcard']['hotel_id'])) return '';
        $hotel_id = $_SESSION['hotelcard']['hotel_id'];

        if (defined('BACKEND_LANG_ID')) {
            $cmd = (isset($_REQUEST['act'])     ? $_REQUEST['act']     : '');
            $uri_param = 'cmd=hotelcard&amp;act';
        } else {
            $cmd = (isset($_REQUEST['cmd'])     ? $_REQUEST['cmd']     : '');
            $uri_param = 'section=hotelcard&amp;cmd';
        }
        return '
'.(defined('BACKEND_LANG_ID') ? '<div id="subnavbar_level2">' : '').'
<ul class="edit_hotel_menu">
  <li class="'.($cmd == 'edit_hotel_availability' ? 'active' : '').'">
    <a href="index.php?'.$uri_param.'=edit_hotel_availability&amp;hotel_id='.$hotel_id.'" title="">
      '.$_ARRAYLANG['TXT_HOTELCARD_EDIT_HOTEL_AVAILABILITY'].'
    </a>
  </li>
  <li class="'.($cmd == 'edit_hotel_roomtypes' ? 'active' : '').'">
    <a href="index.php?'.$uri_param.'=edit_hotel_roomtypes&amp;hotel_id='.$hotel_id.'" title="">
      '.$_ARRAYLANG['TXT_HOTELCARD_EDIT_HOTEL_ROOMTYPES'].'
    </a>
  </li>
  <li class="'.($cmd == 'edit_hotel_contact' ? 'active' : '').'">
    <a href="index.php?'.$uri_param.'=edit_hotel_contact&amp;hotel_id='.$hotel_id.'" title="">
      '.$_ARRAYLANG['TXT_HOTELCARD_EDIT_HOTEL_CONTACT'].'
    </a>
  </li>
  <li class="'.($cmd == 'edit_hotel_images' ? 'active' : '').'">
    <a href="index.php?'.$uri_param.'=edit_hotel_images&amp;hotel_id='.$hotel_id.'" title="">
      '.$_ARRAYLANG['TXT_HOTELCARD_EDIT_HOTEL_IMAGES'].'
    </a>
  </li>
  <li class="'.($cmd == 'edit_hotel_details' ? 'active' : '').'">
    <a href="index.php?'.$uri_param.'=edit_hotel_details&amp;hotel_id='.$hotel_id.'" title="">
      '.$_ARRAYLANG['TXT_HOTELCARD_EDIT_HOTEL_DETAILS'].'
    </a>
  </li>
  <li class="'.($cmd == 'edit_hotel_facilities' ? 'active' : '').'">
    <a href="index.php?'.$uri_param.'=edit_hotel_facilities&amp;hotel_id='.$hotel_id.'" title="">
      '.$_ARRAYLANG['TXT_HOTELCARD_EDIT_HOTEL_FACILITIES'].'
    </a>
  </li>
</ul>
'.(defined('BACKEND_LANG_ID') ? '</div>' : '').'
<br />
';
    }


    /**
     * Updates the Hotel status
     *
     * See {@see Hotel::updateStatus()} for an explanation on how the
     * status is handled.
     * Sets up an error message on failure.
     * @param   integer   $hotel_id     The Hotel ID
     * @param   integer   $status       The status bits
     * @return  boolean                 True on success, false otherwise
     */
    static function updateHotelStatus($hotel_id, $status)
    {
        global $_ARRAYLANG;

        if (empty($hotel_id)) {
            self::addError(
                $_ARRAYLANG['TXT_HOTELCARD_ERROR_MISSING_HOTEL_ID'],
                self::MSG_ERROR);
            return false;
        }
        if (Hotel::updateStatus($hotel_id, $status)) return true;
        self::addError(
            $_ARRAYLANG['TXT_HOTELCARD_ERROR_UPDATING_STATUS'],
            self::MSG_ERROR);
        return false;
    }


    /**
     * Adds the string to the status messages.
     *
     * If necessary, inserts a line break tag (<br />) between
     * messages.
     * Used by both the front- and backend.
     * @param   string  $message      The message text to add
     * @param   string  $class        The message class.
     *                                Defaults to MSG_ERROR
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function addMessage($message, $class=self::MSG_ERROR)
    {
        if (self::$message_type != $class) self::$message = '';
        self::$message .=
            (self::$message ? '<br />' : '').
            $message;
        self::$message_type = $class;
    }


    /**
     * Adds the string $strErrorMessage to the error messages.
     *
     * If necessary, inserts a line break tag (<br />) between
     * error messages.
     * Empty strings are ignored.
     * Used by the backend only.
     * @param   string  $strErrorMessage    The error message to add
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function addError($message)
    {
        if (empty($message)) return;
        if (defined('BACKEND_LANG_ID')) {
            self::$error_message .=
                (self::$error_message ? '<br />' : '').
                $message;
        } else {
            self::addMessage($message, self::MSG_ERROR);
        }
    }


    /**
     * Returns the current status message
     *
     * Clears the $message class variable.
     * @return  string              The message
     */
    static function getMessage()
    {
        $message = self::$message;
        self::$message = '';
        return $message;
    }


    /**
     * Returns the current status message type
     *
     * Clears the $message_type class variable.
     * @return  string              The message type
     */
    static function getMessagetype()
    {
        $message_type = self::$message_type;
        self::$message_type = 0;
        return $message_type;
    }


    /**
     * Returns the current error message
     *
     * Clears the $error_message class variable.
     * @return  string              The error message
     */
    static function getErrorMessage()
    {
        $error_message = self::$error_message;
        self::$error_message = '';
        return $error_message;
    }

}

?>
