<?php

if (isset($_REQUEST['test']))
$_SESSION['hotelcard'] = array (
//'step_complete' => 3,
'hotel_name' => 'hotelname',
'group' => 'hotelkette',
'accomodation_type_id' => 2,
'hotel_address' => 'hotelstrasse',
'hotel_location' => '3600',
'hotel_region' => 'BE',
'contact_name' => 'Kontak T. Person',
'contact_gender' => 'M',
'contact_position' => 'kontaktposition',
'contact_department' => 'kontaktabteilung',
'contact_phone' => '033 226 6000',
'contact_fax' => '033 226 6001',
'contact_email' => 'rk@comvation.com',
'contact_email_retype' => 'rk@comvation.com',
//'bsubmit' => 'Weiter',
'numof_rooms' => 101,
'description_text' => 'hotelbeschreibung hotelbeschreibung hotelbeschreibung hotelbeschreibung hotelbeschreibung hotelbeschreibung hotelbeschreibung',
'hotel_facility_id' =>
array (
2 => 'Fahrstuhl',
30 => 'Fitnessraum',
33 => 'Spa & Wellness',
65 => 'Internet Zugang',
),
'hotel_uri' => 'http://hotelwebsite.com',
'rating' => '4',
'found_how' => 'hotelfoundhow',
'room_type_1' => 'roomtype_1',
'room_available_1' => '12',
'room_price_1' => '13.00',
'room_facility_id_1' =>
array (
7085 => 'Kühlschrank',
7086 => 'Minibar',
),
'room_type_2' => 'roomtype_2',
'room_available_2' => '22',
'room_price_2' => '23.00',
'room_facility_id_2' =>
array (
7089 => 'TV',
7090 => 'WC',
),
'breakfast_included_1' => '0',
'breakfast_included_2' => '1',
'confirm_terms' => '0',
'step_current' => 0,
'image_type' => 'hotelcard_hotel_title',
);

// Test stuff
// Remove the current hotecard data from the session
if (isset($_REQUEST['reset'])) {
    // Reset the add hotel wizard form data and
    // go back to the hotelcard start page
    unset($_SESSION['hotelcard']);
    unset($_SESSION['image']);
    header('Location: index.php?section=hotelcard&cmd=add_hotel');
    exit;
}

/**
 * Class Hotelcard
 *
 * Frontend for the Hotelcard module
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.1.1
 * @package     contrexx
 * @subpackage  module_hotelcard
 * @uses        modules/hotelcard/lib/Config.class.php
 * @todo        Update the @uses
 */

/** @ignore */
require_once ASCMS_CORE_PATH.'/Country.class.php'; // Also contains the region classes
require_once ASCMS_CORE_PATH.'/Creditcard.class.php';
require_once ASCMS_CORE_PATH.'/Filetype.class.php';
require_once ASCMS_CORE_PATH.'/Html.class.php';
require_once ASCMS_CORE_PATH.'/Imagetype.class.php';
require_once ASCMS_CORE_PATH.'/Image.class.php';
require_once ASCMS_CORE_PATH.'/MailTemplate.class.php';
require_once ASCMS_CORE_PATH.'/SettingDb.class.php';
require_once ASCMS_CORE_PATH.'/Sorting.class.php';
require_once ASCMS_CORE_PATH.'/Text.class.php';
require_once ASCMS_FRAMEWORK_PATH.'/Language.class.php';
require_once 'lib/HotelcardLibrary.class.php';
require_once 'lib/Hotel.class.php';
require_once 'lib/HotelAccomodationType.class.php';
require_once 'lib/HotelCheckInOut.class.php';
require_once 'lib/HotelFacility.class.php';
require_once 'lib/HotelcardLibrary.class.php';
require_once 'lib/HotelRating.class.php';
require_once 'lib/HotelRoom.class.php';
require_once 'lib/RelHotelCreditcard.class.php';

//die (nl2br(htmlentities(var_export($_SERVER, true))));
//die(Location::getMenuoptions(isset($_GET['state']) ? $_GET['state'] : ''));

/**
 * Class Hotelcard
 *
 * Frontend for the Hotelcard module
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.1.1
 * @package     contrexx
 * @subpackage  module_hotelcard
 */
class Hotelcard
{
    /**
     * Number of steps to complete the wizard for adding a new hotel
     */
    const HOTEL_REGISTRATION_STEPS = 5;

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
    const INCOMPLETE_CLASS = ' class="error"';

    /**
     * Mail template key for the hotel registration confirmation
     */
    const MAILTEMPLATE_HOTEL_REGISTRATION_CONFIRMATION =
        'hotelcard_hotel_registration_confirmation';

    /**
     * Page template
     * @var HTML_Template_Sigma
     * @static
     */
    private static $objTemplate = false;

    /**
     * Page Title
     *
     * Only used by index.php if its not the empty string
     * @var   string
     * @static
     */
    private static $page_title = '';

    /**
     * Status / error message
     * @var   string
     * @static
     */
    private static $message = '';


    /**
     * Determine the page to be shown and call appropriate methods.
     * @return  string            The finished HTML page content
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getPage($page_content)
    {
        // PEAR Sigma template
        self::$objTemplate = new HTML_Template_Sigma('.');
        CSRF::add_placeholder(self::$objTemplate);
        self::$objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        self::$objTemplate->setTemplate($page_content, true, true);

        // Load settings
// TODO: Move this where it is needed
        SettingDb::init();

        if (isset($_GET['cmd'])) $_GET['act'] = $_GET['cmd'];
        if (empty($_GET['act'])) $_GET['act'] = '';

        // Flag for error handling
        $result = true;
        switch($_GET['act']) {
            case 'add_hotel':
                // Add a new hotel using the wizard
                $result &= self::addHotel();
                break;
            case 'edit_hotel':
            case 'edit_hotel_availability':
            case 'edit_hotel_details':
            case 'edit_hotel_roomtypes':
//echo("Edit Hotel<br />");
                // We have different templates and contents here, but the
                // User and Hotel ID must be verified in any of these cases.
                $result &= self::editHotel();
                break;
            case 'terms':
                $result &= self::terms();
                break;

            // Ajax
            case 'get_locations':
                die(
                    Location::getMenuoptions(
                        (isset($_GET['state']) ? $_GET['state'] : ''),
                        (isset($_SESSION['hotelcard']['hotel-location'])
                          ? $_SESSION['hotelcard']['hotel-location'] : 0),
                        '%1$s (%2$s)')
                );

            // Send a test e-mail
            case 'testmail':
                // Reset the add hotel wizard form data and
                // go back to the hotelcard start page
                die(
                MailTemplate::send(array(
                    'key' => self::MAILTEMPLATE_HOTEL_REGISTRATION_CONFIRMATION,
                    'to' => 'reto.kohli@comvation.com',
                    'from' => 'test@hotelcard.ch',
//                    '' => '',
                )) ? "Mail sent successfully" : "Failed to send mail");
                break;

            case 'overview':
            default:
//                $result &= self::overview();
        }
//        $result &= (empty(self::$message));
//        if (!$result) {
//            self::errorHandler();
//            global $_ARRAYLANG;
//            self::addMessage($_ARRAYLANG['TXT_HOTELCARD_ERROR_TRY_RELOADING']);
//        }
//echo("Messages:<br />".self::$message."<hr />");
        if (self::$message)
            self::$objTemplate->setVariable('HOTELCARD_STATUS', self::$message);
        return self::$objTemplate->get();

    }


    /**
     * Add a new hotel to the database
     *
     * Step through the wizard step by step.
     * Returns true after the last step has been completed
     * and the Hotel has been added successfully.
     * @return  boolean             False while the wizard is running,
     *                              ture upon completion
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function addHotel()
    {
        global $_ARRAYLANG, $_CORELANG;

        JS::activate('wz_tooltip');
//echo("addHotel(): Entered<hr />");

        // Day of the week, number and string abbreviation
        $arrDow = explode(',', $_CORELANG['TXT_CORE_DAY_ARRAY']);

        // Gobble up all posted data whatsoever
        foreach ($_POST as $key => $value) {
            $_SESSION['hotelcard'][$key] = (is_array($_POST[$key])
                ? $value : contrexx_stripslashes($value));
        }
//echo("Added POST<br />".nl2br(var_export($_POST, true))."<hr />");

        // If the step is greater than 1, but the contact name is missing,
        // the page has been reloaded after the wizard was completed.
        // Redirect to the home page.
        if (   empty($_SESSION['hotelcard']['contact_name'])
            && isset($_SESSION['hotelcard']['step'])
            && $_SESSION['hotelcard']['step'] > 1) {
// TODO:  TEST ONLY
//echo("Detected EOW -- Redirecting...<br />");
            unset($_SESSION['hotelcard']);
            unset($_SESSION['image']);
            CSRF::header('Location: index.php');
            exit;
        }

//         Look for uploaded image files and try storing them.
//         The filename and resulting image ID are stored in the session array.
//echo("addHotel(): Going to process Files...<hr />");
//
//        self::processPostFiles();
//echo("Session:<br />".nl2br(var_export($_SESSION['hotelcard'], true))."<hr />");

        // If the form has been posted, automatically move to the next step
        // if the data is complete, or stay there if not.
        // Otherwise, switch to the step selected by the GET request.
        $_SESSION['hotelcard']['step_posted'] =
            (isset($_POST['step']) ? intval($_POST['step']) : 0);

        // Highest number of any steps completed
        if (empty($_SESSION['hotelcard']['step_complete'])) {
            $_SESSION['hotelcard']['step_complete'] = 0;
        }
        $_SESSION['hotelcard']['step_current'] =
            ($_SESSION['hotelcard']['step_posted']
              ? $_SESSION['hotelcard']['step_posted']
              : (   isset($_GET['step'])
                 && $_GET['step'] > 0
                 && $_GET['step'] <= 1 + $_SESSION['hotelcard']['step_complete']
                  ? intval($_GET['step'])
                  : 1 + $_SESSION['hotelcard']['step_complete']));

        // Verify the data from the step posted
        $result_step = true;
//echo("Trying step $_SESSION['hotelcard']['step_current'].  Session step ".$_SESSION['hotelcard']['step_complete']."<br />");
        // Returns false if it misses some data,
        // continue with the next step if true is returned.
        // After each step has been shown once, the single steps
        // must verify their data
        $result_step = call_user_func(array(
            'self', 'addHotelStep'.$_SESSION['hotelcard']['step_current']
        ));

        if ($result_step && $_SESSION['hotelcard']['step_posted']) {
//echo("Step $_SESSION['hotelcard']['step_current'] successfully completed.  completed step ".$_SESSION['hotelcard']['step_complete']."<br />");
            // Update the completed step if it increases
            if ($_SESSION['hotelcard']['step_complete'] < $_SESSION['hotelcard']['step_current'])
                $_SESSION['hotelcard']['step_complete'] = $_SESSION['hotelcard']['step_current'];
            // Move to the next and show it
            ++$_SESSION['hotelcard']['step_current'];
            $result_step = call_user_func(array(
                'self', 'addHotelStep'.$_SESSION['hotelcard']['step_current']
            ));
        } else {
//echo("Step $_SESSION['hotelcard']['step_current'] incomplete.  completed step ".$_SESSION['hotelcard']['step_complete']."<br />");
        }

//echo("Trying step $_SESSION['hotelcard']['step_current'] FAILED<br />");

        // Set up the step bar
        for ($i = 1; $i <= self::HOTEL_REGISTRATION_STEPS; ++$i) {
            self::$objTemplate->setVariable(array(
                'HOTELCARD_STEP_NUMBER' =>
                    sprintf($_ARRAYLANG['TXT_HOTELCARD_STEP_NUMBER'], $i),
                'HOTELCARD_STEP_CLASS' =>
                    ($i == $_SESSION['hotelcard']['step_current']
                      ? 'active'
                      : ($i > 1 + $_SESSION['hotelcard']['step_complete']
                          ? 'disabled'
                          : ($i == self::HOTEL_REGISTRATION_STEPS
                              ? 'last_step'
                              : ''))),
            ));
            if (   $i <= 1 + $_SESSION['hotelcard']['step_complete']
                && $_SESSION['hotelcard']['step_complete'] < self::HOTEL_REGISTRATION_STEPS - 1) {
                self::$objTemplate->setVariable(
                    'HOTELCARD_STEP_HREF',
                    'index.php?section=hotelcard&amp;cmd=add_hotel&amp;step='.$i
                );
                self::$objTemplate->touchBlock('hotelcard_step_link2');
            }
            self::$objTemplate->parse('hotelcard_step');
        }
        self::$objTemplate->setVariable(array(
            'HOTELCARD_STEP' => $_SESSION['hotelcard']['step_current'],
            'HOTELCARD_FORM_ACTION' => htmlentities(
                $_SERVER['REQUEST_URI'], ENT_QUOTES, CONTREXX_CHARSET),
//            'HOTELCARD_FORM_SUBMIT_NAME' => 'bsubmit',

            'TXT_HOTELCARD_NOTE_TITLE' => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_ADD_TITLE_STEP'.$_SESSION['hotelcard']['step_current']],
            'TXT_HOTELCARD_NOTE_TEXT'  => sprintf(
                $_ARRAYLANG['TXT_HOTELCARD_HOTEL_ADD_TEXT_STEP'.$_SESSION['hotelcard']['step_current']],
                // Replace format parameters with
                // 1 - Customer name
                // 2 - Registration date
                // 3 - hotelcard.ch online ID
                // 4 - terms and conditions
                (isset($_SESSION['hotelcard']['lastname'])
                  ? ($_SESSION['hotelcard']['contact_gender'] == 'M'
                      ? $_ARRAYLANG['TXT_HOTELCARD_SALUTATION_MALE']
                      : $_ARRAYLANG['TXT_HOTELCARD_SALUTATION_FEMALE']).' '.
                    $_SESSION['hotelcard']['lastname']
                  : ''),
//                (isset($_SESSION['hotelcard']['contact_name'])
//                    ? $_SESSION['hotelcard']['contact_name'] : ''),

                (isset($_SESSION['hotelcard']['registration_time'])
                    ? $arrDow[date('w', $_SESSION['hotelcard']['registration_time'])].
                      ', '.
                      date(ASCMS_DATE_SHORT_FORMAT,
                          $_SESSION['hotelcard']['registration_time'])
                    : ''),

                (isset($_SESSION['hotelcard']['hotel_id'])
                    ? $_SESSION['hotelcard']['hotel_id'] : ''),

                SettingDb::getValue('terms_and_conditions_'.FRONTEND_LANG_ID)),
        ));
        // Show the submit button in all but the last step
        if ($_SESSION['hotelcard']['step_current'] < self::HOTEL_REGISTRATION_STEPS) {
            self::$objTemplate->setVariable(
                'HOTELCARD_FORM_SUBMIT_VALUE',
                    // The last step but one says "Finish", the others "Continue"
                    ($_SESSION['hotelcard']['step_current'] < self::HOTEL_REGISTRATION_STEPS-1
                      ? $_ARRAYLANG['TXT_HOTELCARD_FORM_SUBMIT_CONTINUE']
                      : $_ARRAYLANG['TXT_HOTELCARD_FORM_SUBMIT_FINISH']
                    )
            );
        } else {
            // That was the last step, then.
            // Unset the contact name to mark this session obsolete
// Comment for TESTING ONLY, so you can reload the last page
            unset($_SESSION['hotelcard']['contact_name']);
        }
//echo("Steps done<br />");
        return $result_step;
    }


    /**
     * Stores any uploaded image files
     *
     * Each file is moved to the Hotelcard image folder with a uniquid()
     * inserted in the filename.  An Image object is created and stored.
     * The original file name and Image ID are stored in the session.
     * @return  void
     * @todo    Make this work properly with multiple files being uploaded.
     *          Currently, successive files will overwrite their predecessors!
     * @todo    Handle deletion of previous instances.
     */
    static function processPostFiles()
    {
//echo("processPostFiles(): Entered<br />");

        if (empty($_SESSION['hotelcard']['hotel_id'])) {
//echo("processPostFiles(): No Hotel ID<br />");
            return false;
        }
//echo("processPostFiles(): Calling Image::processPostFiles()<br />");
        $result = Image::processPostFiles(
            ASCMS_HOTELCARD_IMAGES_FOLDER.'/'.
            $_SESSION['hotelcard']['hotel_id']
        );
        if ($result === '') {
//echo("No change to the image<br />");
            return true;
        }
        if ($result > 0) {
            $_SESSION['hotelcard']['image_id'] = $result;
//echo("Set image_id to $result<br />");
            return true;
        }
//echo("Error handling image<br />");
        return false;
    }


    /**
     * Set the note placeholders for the current step of the add hotel wizard
     * @param   HTML_Template_Sigma   $objTemplate    The template
     */
    static function setStepNote($objTemplate)
    {
        global $_ARRAYLANG, $_CORELANG;

//echo("Hotelcard::setStepNote():"." current ".$_SESSION['hotelcard']['step_current'].", completed ".$_SESSION['hotelcard']['step_complete'].", GET['step'] ".(isset($_GET['step']) ? $_GET['step'] : '-').", POST['step'] ".(isset($_POST['step']) ? $_POST['step'] : '-').", bsubmit ".(isset($_POST['bsubmit']) ? $_POST['bsubmit'] : '-')."<br />");

        // Day of the week, number and string abbreviation
        $arrDow = explode(',', $_CORELANG['TXT_CORE_DAY_ARRAY']);
        $objTemplate->setVariable(array(
            'TXT_HOTELCARD_NOTE_TITLE' => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_ADD_TITLE_STEP'.$_SESSION['hotelcard']['step_current']],
            'TXT_HOTELCARD_NOTE_TEXT'  => sprintf(
                $_ARRAYLANG['TXT_HOTELCARD_HOTEL_ADD_TEXT_STEP'.$_SESSION['hotelcard']['step_current']],
                // Replace format parameters with
                // 1 - Customer name
                // 2 - Registration date
                // 3 - hotelcard.ch online ID
                (isset($_SESSION['hotelcard']['contact_name'])
                    ? $_SESSION['hotelcard']['contact_name'] : ''),
                (isset($_SESSION['hotelcard']['registration_time'])
                    ? $arrDow[date('w', $_SESSION['hotelcard']['registration_time'])].
                      ', '.
                      date(ASCMS_DATE_SHORT_FORMAT,
                          $_SESSION['hotelcard']['registration_time'])
                    : ''),
                (isset($_SESSION['hotelcard']['hotel_id'])
                    ? $_SESSION['hotelcard']['hotel_id'] : '')),
        ));
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
        if ($_SESSION['hotelcard']['step_posted'] != $_SESSION['hotelcard']['step_current'])
            return false;

        // This variable is reported as being "never used" two times(!)
        // by the code analyzer.  Ignore that sucker...
        $complete = true;

        $hotel_id =
            (isset($_SESSION['hotelcard']['hotel_id'])
                ? $_SESSION['hotelcard']['hotel_id'] : 0);
//echo("Got Hotel ID from Session: $hotel_id<br />");
        $objHotel = Hotel::getById($hotel_id);
        if (!$objHotel) {
//echo("Got NO Hotel, making new<br />");
            $objHotel = new Hotel();
        } else {
//echo("Got Hotel with ID $hotel_id<br />");
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
//echo("Hotelcard::verifyAndStoreHotel(): $name => ".var_export($value, true)."<br />");

            // Test if the value is valid whether it's mandatory or not.
            // First, try to set the parameter in the hotel object.
            // If false is returned, it's a Hotel field, but the value
            // is rejected.
            if ($objHotel->setFieldvalue($name, $value)) {
//echo("Hotelcard::verifyAndStoreHotel(): Set $name to $value<br />");
                if ($objHotel->getFieldvalue($name) !== null) {
                    // Value has been accepted by the Hotel class;
                    // update the session with the actual value
                    $_SESSION['hotelcard'][$name] =
                        $objHotel->getFieldvalue($name);
//echo("Hotelcard::verifyAndStoreHotel(): Accepted $name to be ".$_SESSION['hotelcard'][$name]."<br />");
                    continue;
                }
                // else... The value was accepted but did not change the
                // default null value, so the Hotel simply wasn't interested.
                // The non-hotel cases are handled below.
            } else {
                // The value has been rejected
                // If the field is empty, but not mandatory, just ignore it
                if (empty($row_data['mandatory']) && empty($value))
                    continue;
//echo("Hotelcard::verifyAndStoreHotel(): Rejected mandatory field $name value '$value'<br />");
                $complete = false;
                $arrFields[$name]['class'] = self::INCOMPLETE_CLASS;
                continue;
            }

//echo("Hotelcard::verifyAndStoreHotel(): Checking field $name: ".var_export($value, true)."<br />");

            $arrMatch = array();
            $index = 0;
            if (preg_match('/_(\d)$/', $name, $arrMatch)) {
                $index = $arrMatch[1];
            }
            $result = false;
            // All the remaining special cases
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
//echo("room type name set to $value<br />");
                break;
              case 'room_available_1':
              case 'room_available_2':
              case 'room_available_3':
              case 'room_available_4':
                $result = HotelRoom::validateRoomtypeNumber($value);
                if (empty($_SESSION['hotelcard']['room_type_'.$index])) {
//echo("Ignoring invalid room number $value for unused room type ".$index."<br />");
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
//echo("Field $name => number ".$arrMatch[1].", price $value => room type ".$_SESSION['hotelcard']['room_type_'.$arrMatch[1]]."<br />");
                if (empty($_SESSION['hotelcard']['room_type_'.$index])) {
//echo("Ignoring invalid price $value for unused room type ".$index."<br />");
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
//echo("*** breakfast selection is ".var_export($value, true)." for room type ".$index."<br />");
                  $result = ($value !== '');
                if (empty($_SESSION['hotelcard']['room_type_'.$index])) {
//echo("Ignoring invalid breakfast selection $value for unused room type ".$index."<br />");
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
            self::addMessage($_ARRAYLANG['TXT_HOTELCARD_MISSING_MANDATORY_DATA']);
            return false;
        }

        // Store the Hotel
        $hotel_id = $objHotel->store();
        if (!$hotel_id) {
            self::addMessage($_ARRAYLANG['TXT_HOTELCARD_ERROR_STORING_HOTEL']);
            return false;
        }

        $_SESSION['hotelcard']['hotel_id'] = $hotel_id;
        $_SESSION['hotelcard']['registration_time'] =
            $objHotel->getFieldvalue('registration_time');
//echo("Stored Hotel, ID in session: ".$_SESSION['hotelcard']['hotel_id']."<br />");

        // Store the hotel facilities, if present
        if (isset($_SESSION['hotelcard']['hotel_facility_id'])) {
            // Clear all relations, then add the current ones
            if (!HotelFacility::deleteByHotelId($hotel_id)) {
//echo("ERROR: Failed to delete Hotel Facilities for Hotel ID $hotel_id<br />");
                return false;
            }
            foreach (array_keys($_SESSION['hotelcard']['hotel_facility_id']) as $hotel_facility_id) {
                if (HotelFacility::addRelation(
                    $hotel_id, $hotel_facility_id)) continue;
//echo("ERROR: Failed to store Hotel Facilities for Hotel ID $hotel_id<br />");
                Hotelcard::addMessage(sprintf(
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
                      ? false : true)
                );
                if (!$room_type_id) {
                    Hotelcard::addMessage(sprintf(
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
                    Hotelcard::addMessage(sprintf(
                        $_ARRAYLANG['TXT_HOTELCARD_ERROR_FAILED_TO_ADD_ROOM_FACILITY'],
                        $room_facility_name));
                    return false;
                }
            }
//echo("Stored Room Types and Facilites for Hotel ID $hotel_id<br />");
        }
        return $complete;
    }


    /**
     * Shows the first step of the "Add Hotel" wizard
     *
     * If it still misses information, shows itself, and returns false.
     * Returns true if the information is complete.
     */
    static function addHotelStep1()
    {
        global $_ARRAYLANG;

          $arrFields = array(
//            'lang_id' => array(
//                'mandatory' => false,
//                'label' => 'TXT_HOTELCARD_LANG_ID',
//                'input' => FWLanguage::getMenuActiveOnly(
//                    (isset($_SESSION['hotelcard']['lang_id'])
//                        ? $_SESSION['hotelcard']['lang_id'] : FRONTEND_LANG_ID),
//                    'lang_id', 'document.forms.form_hotelcard.submit();'),
//            ),
            'hotel_name' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_HOTEL_NAME',
                'input' => Html::getInputText('hotel_name',
                    (isset($_SESSION['hotelcard']['hotel_name'])
                        ? $_SESSION['hotelcard']['hotel_name'] : ''), false,
                    'style="text-align: left;"'),
            ),
            'group' => array( // Mind that this goes into a lookup table!
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_GROUP',
                'input' => Html::getInputText('group',
                    (isset($_SESSION['hotelcard']['group'])
                        ? $_SESSION['hotelcard']['group'] : ''), false,
                    'style="text-align: left;"'),
            ),
            'accomodation_type_id' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_ACCOMODATION_TYPE_ID',
                'input' => Html::getSelect(
                    'accomodation_type_id',
                    HotelAccomodationType::getNameArray(),
                    (isset($_SESSION['hotelcard']['accomodation_type_id'])
                        ? $_SESSION['hotelcard']['accomodation_type_id'] : 0)),
            ),
            'hotel_address' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_HOTEL_ADDRESS',
                'input' => Html::getInputText('hotel_address',
                    (isset($_SESSION['hotelcard']['hotel_address'])
                        ? $_SESSION['hotelcard']['hotel_address'] : ''), false,
                    'style="text-align: left;"'),
            ),
//            'hotel_zip' => array(
//                'mandatory' => true,
//                'label' => 'TXT_HOTELCARD_HOTEL_ZIP',
//                'input' => Html::getInputText('hotel_zip',
//                    (isset($_SESSION['hotelcard']['hotel_zip'])
//                        ? $_SESSION['hotelcard']['hotel_zip'] : ''), false,
//                        'style="text-align: left;"'),
//            ),
            'hotel_region' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_HOTEL_REGION',
                'input' => Html::getSelect(
                'hotel_region',
                    (isset($_SESSION['hotelcard']['hotel_region'])
                      ? array()
                      :   array('' => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_REGION_PLEASE_CHOOSE']))
                        + State::getArray(true),
                    (isset($_SESSION['hotelcard']['hotel_region'])
                      ? $_SESSION['hotelcard']['hotel_region']
                      : (isset($_SESSION['hotelcard']['hotel_location'])
                          ? State::getByLocation($_SESSION['hotelcard']['hotel_location'])
                          : '')), 'hotel_region',
                    'new Ajax.Updater(\'hotel_location\', \'index.php?section=hotelcard&amp;act=get_locations&amp;state=\'+document.getElementById(\'hotel_region\').value, { method: \'get\' });'),
            ),
            'hotel_location' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_HOTEL_LOCATION',
                'input' => Html::getSelect('hotel_location',
                    (isset($_SESSION['hotelcard']['hotel_region'])
                      ? Location::getArrayByState($_SESSION['hotelcard']['hotel_region'], '%1$s (%2$s)')
                      : array($_ARRAYLANG['TXT_HOTELCARD_PLEASE_CHOOSE_REGION'])),
                    (isset($_SESSION['hotelcard']['hotel_location'])
                        ? $_SESSION['hotelcard']['hotel_location'] : ''), 'hotel_location',
                    '', 'style="text-align: left;"'),
            ),
//            'hotel_country_id' => array(
//                'mandatory' => false,
//                'label' => 'TXT_HOTELCARD_HOTEL_COUNTRY_ID',
//                'input' => Html::getSelect(
//                    'hotel_country_id', Country::getNameArray(),
//                    (isset($_SESSION['hotelcard']['hotel_country_id'])
//                        ? $_SESSION['hotelcard']['hotel_country_id'] : '')), false,
//            ),
            'contact_data' => array(
                'mandatory' => false,
                'label' => $_ARRAYLANG['TXT_HOTELCARD_CONTACT_DATA'],
                'input' => '',
            ),
            'contact_name' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_CONTACT_NAME',
                'input' => Html::getInputText('contact_name',
                    (isset($_SESSION['hotelcard']['contact_name'])
                        ? $_SESSION['hotelcard']['contact_name'] : ''), false,
                    'style="text-align: left;"'),
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
                        (isset($_SESSION['hotelcard']['contact_gender'])
                            ? $_SESSION['hotelcard']['contact_gender'] : '')).
                    "</span>\n",
            ),
            'contact_position' => array(
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_CONTACT_POSITION',
                'input' => Html::getInputText('contact_position',
                    (isset($_SESSION['hotelcard']['contact_position'])
                        ? $_SESSION['hotelcard']['contact_position'] : ''), false,
                    'style="text-align: left;"'),
            ),
            'contact_department' => array(
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_CONTACT_DEPARTMENT',
                'input' => Html::getInputText('contact_department',
                    (isset($_SESSION['hotelcard']['contact_department'])
                        ? $_SESSION['hotelcard']['contact_department'] : ''), false,
                    'style="text-align: left;"'),
            ),
            'contact_phone' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_CONTACT_PHONE',
                'input' => Html::getInputText('contact_phone',
                    (isset($_SESSION['hotelcard']['contact_phone'])
                        ? $_SESSION['hotelcard']['contact_phone'] : ''), false,
                        'style="text-align: left;"'),
            ),
            'contact_fax' => array(
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_CONTACT_FAX',
                'input' => Html::getInputText('contact_fax',
                    (isset($_SESSION['hotelcard']['contact_fax'])
                        ? $_SESSION['hotelcard']['contact_fax'] : ''), false,
                        'style="text-align: left;"'),
            ),
            'contact_email' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_CONTACT_EMAIL',
                'input' => Html::getInputText('contact_email',
                    (isset($_SESSION['hotelcard']['contact_email'])
                        ? $_SESSION['hotelcard']['contact_email'] : ''), false,
                    'style="text-align: left;"'),
            ),
            'contact_email_retype' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_CONTACT_EMAIL_RETYPE',
                'input' => Html::getInputText('contact_email_retype',
                    (isset($_SESSION['hotelcard']['contact_email_retype'])
                        ? $_SESSION['hotelcard']['contact_email_retype'] : ''), false,
                    'style="text-align: left;"'),
            ),
        );
        // Verify the data already present if it's the current step
        $complete = self::verifyAndStoreHotel($arrFields);
        // Only verify the e-mail addresses after the fields have been
        // filled out
        if ($complete) {
            if ($_SESSION['hotelcard']['contact_email'] !=
                  $_SESSION['hotelcard']['contact_email_retype']) {
                $complete = false;
                $arrFields['contact_email']['error'] = self::INCOMPLETE_CLASS;
                $arrFields['contact_email_retype']['error'] = self::INCOMPLETE_CLASS;
                self::addMessage($_ARRAYLANG['TXT_HOTELCARD_EMAILS_DO_NOT_MATCH']);
            } elseif (!FWValidator::isEmail($_SESSION['hotelcard']['contact_email'])) {
                $complete = false;
                $arrFields['contact_email']['error'] = self::INCOMPLETE_CLASS;
                self::addMessage($_ARRAYLANG['TXT_HOTELCARD_EMAIL_IS_INVALID']);
            }
        }
        if ($complete && isset($_POST['bsubmit'])) return true;
//echo("Showing step 1<br />");
        HotelcardLibrary::parseDataTable(self::$objTemplate, $arrFields);
        return false; // Still running
    }


    /**
     * Shows the second step of the "Add Hotel" wizard
     *
     * If it still misses information, shows itself, and returns false.
     * Returns true if the information is complete.
     */
    static function addHotelStep2()
    {
        global $_ARRAYLANG;

//        SettingDb::init();
//$terms = SettingDb::getValue('terms_and_conditions_'.FRONTEND_LANG_ID);
//echo("Hotelcard::addHotelStep2(): terms:<br />$terms<hr />");

        $arrFields = array(
// The terms are inserted in the step heading
//            'terms_header' => array(
//                'mandatory' => false,
//                'label' => 'TXT_HOTELCARD_TERMS_AND_CONDITIONS',
//            ),
//            'terms' => array(
//                'mandatory' => false,
//                'input' => SettingDb::getValue('terms_and_conditions_'.FRONTEND_LANG_ID),
//            ),
            'confirm_terms' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_CONFIRM_TERMS',
                'input' => Html::getCheckbox('confirm_terms', 1, false,
                    isset($_SESSION['hotelcard']['confirm_terms'])
                ),
            ),
            'register_date' => array(
                'label' => 'TXT_HOTELCARD_REGISTER_DATE',
                'input' => date(ASCMS_DATE_SHORT_FORMAT),
            ),
        );
        // Verify the data already present
        $complete = self::verifyAndStoreHotel($arrFields);
        if ($complete && isset($_POST['bsubmit'])) {
            if (empty($_SESSION['hotelcard']['username'])) {
//echo("No username in session<br />");
                $objUser = new User();
                $objUser->setFrontendLanguage(FRONTEND_LANG_ID);
                $objUser->setEmail($_SESSION['hotelcard']['contact_email']);
                $arrMatch = array();
                if (preg_match(
                    '/^([\S\s]*)\s+([\S\s]+)$/',
                    $_SESSION['hotelcard']['contact_name'], $arrMatch
                )) {
                    $firstname = $arrMatch[1];
                    $lastname = $arrMatch[2];
//echo("first $firstname, last $lastname<br />");
                    $objUser->setProfile(array(
                        'firstname' => array(0 => $firstname),
                        'lastname'  => array(0 => $lastname),
                        SettingDb::getValue('user_profile_attribute_hotel_id') =>
                            array(0 => $_SESSION['hotelcard']['hotel_id']),
                    ));
                    $username = User::makeUsername($firstname, $lastname);
                    if (!$username) {
                        self::addMessage(sprintf(
                            $_ARRAYLANG['TXT_HOTELCARD_REGISTRATION_CREATING_USERNAME_FAILED'],
                            $_SESSION['hotelcard']['contact_email']));
                        $complete = false;
                    } else {
                        $password = User::makePassword();
//echo("user: $username / $password<br />");
                        $objUser->setUsername($username);
                        $objUser->setPassword($password);
                        $objUser->setEmail($_SESSION['hotelcard']['contact_email']);
                        $objUser->setGroups(array(SettingDb::getValue('hotel_usergroup')));
                        $objUser->setActiveStatus(1);
                        if ($objUser->store()) {
                            $_SESSION['hotelcard']['lastname'] = $lastname;
                            $_SESSION['hotelcard']['username'] = $username;
                            $_SESSION['hotelcard']['password'] = $password;
//echo("user stored, session: ".$_SESSION['hotelcard']['username']." / ".$_SESSION['hotelcard']['password']."<br />");
                              $hotel_id = $_SESSION['hotelcard']['hotel_id'];
                              $objHotel = Hotel::getById($hotel_id);
                              if ($objHotel) {
                                  $objHotel->setFieldvalue('status', Hotel::STATUS_ACCOUNT);
                                  $objHotel->store();
                              } else {
DBG::log("Failed to update status to ACCOUNT");
                              }
                        } else {
//echo("ERROR: Failed to store user<br />");
                            self::addMessage(sprintf(
                                $_ARRAYLANG['TXT_HOTELCARD_REGISTRATION_CREATING_USER_FAILED'],
                                $_SESSION['hotelcard']['contact_email']));
                            // Clear the contact e-mail
                            $_SESSION['hotelcard']['contact_email'] = '';
                            $_SESSION['hotelcard']['contact_email_retype'] = '';
                            // Re-init the e-mail fields.
                            // Have to reset the retype field, too, because of the tabindex.
                            $arrFields['contact_email'] = array(
                                'mandatory' => true,
                                'label' => 'TXT_HOTELCARD_CONTACT_EMAIL',
                                'input' => Html::getInputText('contact_email', '', false,
                                'style="text-align: left;"'),
                            );
                            $arrFields['contact_email_retype'] = array(
                                'mandatory' => true,
                                'label' => 'TXT_HOTELCARD_CONTACT_EMAIL_RETYPE',
                                'input' => Html::getInputText('contact_email_retype', '', false,
                                    'style="text-align: left;"'),
                            );
                            $complete = false;
                        }
                    }
                }
            }
            if (empty($_SESSION['hotelcard']['mail_sent'])) {
//echo("Mail not sent yet<br />");
                // If it hasn't happened before, send a confirmation by e-mail.
                // Note that this may fail and will be retried on submitting
                // this step if it did.
                if (self::sendRegistrationConfirmationMail()) {
//echo("Mail sent<br />");
                    $_SESSION['hotelcard']['mail_sent'] = 1;
                    $hotel_id = $_SESSION['hotelcard']['hotel_id'];
                    $objHotel = Hotel::getById($hotel_id);
                    if ($objHotel) {
                        $objHotel->setFieldvalue('status', Hotel::STATUS_CONFIRMED);
                        $objHotel->store();
                    } else {
DBG::log("Failed to update status to CONFIRMED");
                    }
                }
            }
        }
        if ($complete) return true;
//echo("Showing step 2<br />");
        HotelcardLibrary::parseDataTable(self::$objTemplate, $arrFields);
        return false; // Still running
    }


    /**
     * Shows the third step of the "Add Hotel" wizard
     *
     * If it still misses information, shows itself, and returns false.
     * Returns true if the information is complete.
     */
    static function addHotelStep3()
    {
        global $_ARRAYLANG;

        // Look for uploaded image files and try storing them.
        // The filename and resulting image ID are stored in the session array.
        // Note that images *CAN NOT* be processed in the first step,
        // while the Hotel hasn't been stored yet.  It requires the hotel_id
        // index in the session array to be set and valid.
//echo("addHotel(): Going to process Files...<hr />");
        self::processPostFiles();
//echo("Session:<br />".nl2br(var_export($_SESSION['hotelcard'], true))."<hr />");


//echo("Rating: ".$_SESSION['hotelcard']['rating']."<hr />");
        $arrFields = array(
//            'additional_data' => array(
//                'mandatory' => false,
//                'label' => $_ARRAYLANG['TXT_HOTELCARD_ADDITIONAL_DATA'],
//                'input' => '',
//            ),
            'numof_rooms' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_NUMOF_ROOMS',
                'input' => Html::getInputText('numof_rooms',
                    (isset($_SESSION['hotelcard']['numof_rooms'])
                        ? $_SESSION['hotelcard']['numof_rooms'] : ''), false,
                    'style="text-align: left;"'),
            ),
            'description_text' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_DESCRIPTION_TEXT',
                'input' => Html::getTextarea('description_text',
                    (isset($_SESSION['hotelcard']['description_text'])
                        ? $_SESSION['hotelcard']['description_text'] : ''),
                    '', '',
                    'onkeyup="lengthLimit(this, this.form.count_min, this.form.count_max, 100, 500);"').
                '<br />'.
                sprintf($_ARRAYLANG['TXT_HOTELCARD_TEXT_LENGTH_MINIMUM_MAXIMUM'],
                    html::getInputText('count_min', 100, 'count_min',
                        'disabled="disabled" style="width: 30px;"'),
                    html::getInputText('count_max', 500, 'count_max',
                        'disabled="disabled" style="width: 30px;"')),
            ),
            'rating' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_RATING',
                'input' => Html::getSelect('rating',
                    (isset($_SESSION['hotelcard']['rating'])
                      ? array()
                      : array('' =>
                          $_ARRAYLANG['TXT_HOTELCARD_RATING_PLEASE_CHOOSE']))
                    + HotelRating::getArray(),
                    (isset($_SESSION['hotelcard']['rating'])
                        ? $_SESSION['hotelcard']['rating'] : '')),
            ),
            'hotel_facilities' => array(
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_HOTEL_FACILITY_ID',
                'input' => '',
            ),
        );
        foreach (HotelFacility::getGroupNameArray() as $group_id => $group_name) {
//echo("Setting up group ID $group_id, name $group_name<br />");
            $arrFacilities = HotelFacility::getFacilityNameArray($group_id, true);
//echo("Setting up Facilities: ".var_export($arrFacilities, true)."<br />");
//echo("Hotel Facilities: ".var_export($_SESSION['hotelcard']['hotel_facility_id'], true)."<br />");
//echo("POST: ".var_export($_POST, true)."<br />");
            $arrFields['hotel_facility_id'][$group_id] = array(
                'mandatory' => false,
                //'label' => '<b>'.$group_name.'</b>',
                'label' => $group_name,
                'input' => '<span class="inputgroup">'.
                    Html::getCheckboxGroup(
                        'hotel_facility_id',
                        $arrFacilities, $arrFacilities,
                        (   isset($_SESSION['hotelcard']['hotel_facility_id'])
                         && is_array($_SESSION['hotelcard']['hotel_facility_id'])
                          ? array_keys($_SESSION['hotelcard']['hotel_facility_id'])
                          : '')).
                    "</span>\n",
            );
        }
        $arrFields += array(
            'hotel_uri' => array(
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_HOTEL_URI',
                'input' => Html::getInputText('hotel_uri',
                    (empty($_SESSION['hotelcard']['hotel_uri'])
                        ? ''
                        : (FWValidator::hasProto($_SESSION['hotelcard']['hotel_uri'])
                            ? '' : 'http://').
                          $_SESSION['hotelcard']['hotel_uri']), false,
                    'style="text-align: left;"'),
            ),
            'image_id' => array(
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_IMAGE',
                'input' => Html::getImageChooserUpload(
                    Image::getFromSessionByKey(
                        'image', self::IMAGE_PATH_HOTEL_DEFAULT),
                    'image', self::IMAGETYPE_TITLE,
                    self::IMAGE_PATH_HOTEL_DEFAULT),
            ),
            'found_how' => array(
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_FOUND_HOW',
                'input' => Html::getTextarea('found_how',
                (isset($_SESSION['hotelcard']['found_how'])
                    ? $_SESSION['hotelcard']['found_how'] : '')),
            ),
        );
        // Verify the data already present
//echo("Before:<br />".htmlentities(var_export($arrFields, true), ENT_QUOTES, CONTREXX_CHARSET)."<hr />");
        if (   isset($_POST['bsubmit'])
            && self::verifyAndStoreHotel($arrFields)) {
//echo("After:<br />".htmlentities(var_export($arrFields, true), ENT_QUOTES, CONTREXX_CHARSET)."<hr />");
            return true;
        }
//echo("Showing step 3<br />");
        HotelcardLibrary::parseDataTable(self::$objTemplate, $arrFields);
        JS::registerCode(Html::getJavascript_Text());
        return false; // Still running
    }


    /**
     * Shows the fourth step of the "Add Hotel" wizard
     *
     * If it still misses information, shows itself, and returns false.
     * Returns true if the information is complete.
     */
    static function addHotelStep4()
    {
        global $_ARRAYLANG, $_CORELANG;

        // The actual labels and values are set in the loop below
        // First, show the tabs to select the room types
        $arrFields = array(
            'dummy_ul_open' => array(
                'special' => '<ul class="roomtype_ul">',
            ),
        );
        // The list of tabs
        for ($i = 1; $i <= 4; ++$i) {
//echo("Room type $i: ".(isset($_SESSION['hotelcard']['room_type_'.$i]) ? $_SESSION['hotelcard']['room_type_'.$i] : '')."<br />");
            $arrFields['dummy_li_'.$i] = array(
                'special' =>
                    '<li class="roomtype_li'.($i == 1 ? '_active' : '').'"'.
                    ' id="roomtype_li-'.$i.'"'.
                    // showTab(tab_base, div_base, active_suffix, min_suffix, max_suffix)
                    ' onclick="showTab(\'roomtype_li-\', \'roomtype-\', '.$i.', 1, 4)">'.
//                    ($i > 1 && empty($_SESSION['hotelcard']['room_type_'.$i])
//                      ? $_CORELANG['TXT_CORE_HTML_TOGGLE_OPEN']
//                      : $_CORELANG['TXT_CORE_HTML_TOGGLE_CLOSE']).'>'.
                    sprintf($_ARRAYLANG['TXT_HOTELCARD_ROOMTYPE_NUMBER'], $i).
                    '</li>'
//                    '<b class="toggleRoomtype" id="toggleRoomtype-'.$i.'"'.
//                    '</b>'
            );
        }
        $arrFields['dummy_ul_close'] = array(
            'special' => '</ul>',
        );

        // Show room type form content four times:
        for ($i = 1; $i <= 4; ++$i) {
//echo("Room type $i: ".(isset($_SESSION['hotelcard']['room_type_'.$i]) ? $_SESSION['hotelcard']['room_type_'.$i] : '')."<br />");

            $breakfast_included =
                (isset($_SESSION['hotelcard']['breakfast_included_'.$i])
                  ? $_SESSION['hotelcard']['breakfast_included_'.$i] : '');
            // Only the first type is mandatory
            $arrFields += array(
                'dummy_div_open_'.$i => array(
                    'special' =>
                        '<div id="roomtype-'.$i.'" style="display: '.
                        ($i == 1 ? 'block' : 'none').'">'
                ),
                'room_type_'.$i => array(
                    'mandatory' => ($i == 1),
                    'label' => 'TXT_HOTELCARD_ROOMTYPE',
                    'input' => Html::getInputText('room_type_'.$i,
                        (isset($_SESSION['hotelcard']['room_type_'.$i])
                            ? $_SESSION['hotelcard']['room_type_'.$i] : ''), false,
                        'style="text-align: left;"'),
                ),
                'room_available_'.$i => array(
                    'mandatory' => ($i == 1),
                    'label' => 'TXT_HOTELCARD_ROOM_AVAILABLE',
                    'input' => Html::getInputText('room_available_'.$i,
                        (isset($_SESSION['hotelcard']['room_available_'.$i])
                            ? $_SESSION['hotelcard']['room_available_'.$i] : ''), false,
                        'style="text-align: left;"'),
                ),
                'room_price_'.$i => array(
                    'mandatory' => ($i == 1),
                    'label' => 'TXT_HOTELCARD_ROOM_PRICE',
                    'input' => Html::getInputText('room_price_'.$i,
                        (isset($_SESSION['hotelcard']['room_price_'.$i])
                            ? $_SESSION['hotelcard']['room_price_'.$i] : ''), false,
                        'style="text-align: left;"'),
                ),
                'breakfast_included_'.$i => array(
                    'mandatory' => ($i == 1),
                    'label' => 'TXT_HOTELCARD_BREAKFAST_INCLUDED',
                    'input' => Html::getSelect('breakfast_included_'.$i,
                        HotelRoom::getBreakfastIncludedArray($breakfast_included),
                        $breakfast_included, false,
                        '', 'style="text-align: left;"'),
                ),
                // Note: These are checkbox groups and are thus posted as
                // arrays, like 'room_facility_id_1[]'
                'room_facility_id_'.$i => array(
                    'mandatory' => false,
                    'label' => 'TXT_HOTELCARD_ROOM_FACILITY_ID',
                    'input' => '<span class="inputgroup">'.
                        Html::getCheckboxGroup('room_facility_id_'.$i,
                            HotelRoom::getFacilityNameArray(true),
                            HotelRoom::getFacilityNameArray(true),
                            (   isset($_SESSION['hotelcard']['room_facility_id_'.$i])
                             && is_array($_SESSION['hotelcard']['room_facility_id_'.$i])
                              ? array_keys($_SESSION['hotelcard']['room_facility_id_'.$i]) : '')).
                        "</span>\n",
                ),
                'dummy_div_close_'.$i => array(
                    // This closes the div id=roomtype opened above
                    'special' => '</div>',
                ),
            );
        }
        // Add JS for the toggle block
        JS::registerCode(Html::getJavascript_Element());

        if (   isset($_POST['bsubmit'])
            && self::verifyAndStoreHotel($arrFields)) {
//echo("After:<br />".htmlentities(var_export($arrFields, true), ENT_QUOTES, CONTREXX_CHARSET)."<hr />");
            return true;
        }
//echo("Still Room type 1: ".(isset($_SESSION['hotelcard']['room_type_1']) ? $_SESSION['hotelcard']['room_type_1'] : '')."<br />");
        HotelcardLibrary::parseDataTable(self::$objTemplate, $arrFields);
//echo("Showing step 4<br />");
        return false; // Still running
    }


    /**
     * Shows the fifth step of the "Add Hotel" wizard
     *
     * If it still misses information, shows itself, and returns false.
     * Returns true if the information is complete.
     */
    static function addHotelStep5()
    {
        global $_ARRAYLANG;

//echo("Creditcards: ".var_export($_SESSION['hotelcard']['creditcard_id'], true)."<hr />");
//echo("Room facilities: ".var_export($_SESSION['hotelcard']['room_facility_id'], true)."<hr />");

if (!defined('_DEBUG')) return false;

        $hotel_id =
            (isset($_SESSION['hotelcard']['hotel_id'])
                ? $_SESSION['hotelcard']['hotel_id'] : 0);
        HotelcardLibrary::hotel_view(self::$objTemplate, $hotel_id);

//echo("Got Hotel ID from Session: $hotel_id<br />");
        return false; // This last step is never successful
    }


    static function sendRegistrationConfirmationMail()
    {
        global $_CORELANG, $_ARRAYLANG;

        if (empty($_SESSION['hotelcard']['username'])) return false;

        SettingDb::init('admin');
        // Day of the week, number and string abbreviation
        $arrDow = explode(',', $_CORELANG['TXT_CORE_DAY_ARRAY']);

        $search = array(
            '<admin_email>',
            '<contact_email>',
            '<contact_salutation>',
            '<contact_name>',
            '<hotel_id>',
            '<hotel_name>',
            '<registration_time>',
            '<username>',
            '<password>',
        );
        $replace = array(
            SettingDb::getValue('admin_email'),
            $_SESSION['hotelcard']['contact_email'],
            ($_SESSION['hotelcard']['contact_gender'] == 'M'
              ? $_ARRAYLANG['TXT_HOTELCARD_SALUTATION_MALE']
              : $_ARRAYLANG['TXT_HOTELCARD_SALUTATION_FEMALE']),
            $_SESSION['hotelcard']['lastname'],
            $_SESSION['hotelcard']['hotel_id'],
            $_SESSION['hotelcard']['hotel_name'],
            $arrDow[date('w', $_SESSION['hotelcard']['registration_time'])].
            ', '.
            date(ASCMS_DATE_SHORT_FORMAT, $_SESSION['hotelcard']['registration_time']),
            $_SESSION['hotelcard']['username'],
            $_SESSION['hotelcard']['password'],
        );
        if (MailTemplate::send(array(
            'key'     => Hotelcard::MAILTEMPLATE_HOTEL_REGISTRATION_CONFIRMATION,
            'search'  => $search,
            'replace' => $replace,
        ))) {
            self::addMessage(sprintf(
                $_ARRAYLANG['TXT_HOTELCARD_REGISTRATION_MAIL_SENT_SUCCESSFULLY'],
                $_SESSION['hotelcard']['contact_email']));
            return true;
        }
        self::addMessage(sprintf(
            $_ARRAYLANG['TXT_HOTELCARD_REGISTRATION_MAIL_SENDING_FAILED'],
            $_SESSION['hotelcard']['contact_email']));
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
    static function editHotel()
    {
        $objFWUser = FWUser::getFWUserObject();
        /** @var User */
        $objUser = $objFWUser->objUser;
        if (!$objUser) {
            CSRF::header('Location: index.php?section=hotelcard');
            exit;
        }
        $user_id = $objUser->getId();
// TODO: Perhaps the init() can be limited to just the 'admin' key?
        SettingDb::init('admin');
        $attribute_id = SettingDb::getValue('user_profile_attribute_hotel_id');
        $hotel_id = $objUser->getProfileAttribute($attribute_id);
        if (empty($hotel_id)) {
            CSRF::header('Location: index.php?section=hotelcard');
            exit();
        }
//echo("attribute ID $attribute_id, got hotel ID $hotel_id<br />");
//$hotel_id = 6;

        self::$objTemplate->setVariable(
            'HOTELCARD_EDIT_HOTEL_MENU',
            self::getEditHotelMenu());

        switch ($_GET['act']) {
          case 'edit_hotel_details':
            return self::editHotelDetails($hotel_id);
          case 'edit_hotel_roomtypes':
            return self::editHotelRoomtypes($hotel_id);
        }
        return self::editHotelAvailability($hotel_id);
    }


    /**
     * Shows the availablility of the different roomtypes for the hotel ID
     * specified
     * @param   integer   $hotel_id     The selected hotel ID
     * @return  boolean                 True on success, false otherwise
     */
    static function editHotelAvailability($hotel_id)
    {
        global $_ARRAYLANG, $_CORELANG;

        // Requires DatePickerControl.js
        JS::activate('datepicker');

//echo("Hotelcard::editHotelOverview($hotel_id): Entered<br />");
//DBG::enable_adodb_debug();
//DBG::enable_error_reporting();

        $objhotel = Hotel::getById($hotel_id);
// TODO:  Add error message, maybe a redirect
        if (empty($objhotel)) {
            self::addMessage($_ARRAYLANG['TXT_HOTELCARD_ERROR_HOTEL_ID_NOT_FOUND'].' '.$hotel_id);
            return false;
        }

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
        $_SESSION['hotelcard']['date_to'] = (isset($_REQUEST['date_to'])
            ? strtotime($_REQUEST['date_to']) + 7200
            : (isset($_SESSION['hotelcard']['date_to'])
                ? $_SESSION['hotelcard']['date_to']
                : strtotime('+1 month') + 7200));
//echo("Date from ".$_SESSION['hotelcard']['date_from'].", to ".$_SESSION['hotelcard']['date_to']."<br />");

        // Store changes, if any.
        // The type IDs and more information is taken from the post parameters.
        if (isset($_POST['bsubmit'])) self::updateHotel($hotel_id);


        // Abbreviations for day of the week
        $arrDow = explode(',', $_CORELANG['TXT_CORE_DAY_ABBREV2_ARRAY']);
        $arrMoy = explode(',', $_CORELANG['TXT_CORE_MONTH_ARRAY']);

        // Fetch the room types and availabilities
        $arrRoomTypes = HotelRoom::getTypeArray(
            $hotel_id, 0,
            $_SESSION['hotelcard']['date_from'],
            $_SESSION['hotelcard']['date_to']
        );

        // Spray language variables all over
        self::$objTemplate->setGlobalVariable(
            $_ARRAYLANG
          + array(
            'HOTELCARD_HOTEL_ID'          => $hotel_id,
            'HOTELCARD_DATE_FROM'         => Html::getSelectDate(
                'date_from',
                date('d.m.Y', $_SESSION['hotelcard']['date_from'])),
            'HOTELCARD_DATE_TO'           => Html::getSelectDate(
                'date_to',
                date(ASCMS_DATE_SHORT_FORMAT, $_SESSION['hotelcard']['date_to'])),
            // Datepicker language and settings
            'HOTELCARD_DPC_TODAY_TEXT'    => $_CORELANG['TXT_CORE_TODAY'],
            'HOTELCARD_DPC_BUTTON_TITLE'  => $_ARRAYLANG['TXT_HOTELCARD_OPEN_CALENDAR'],
            'HOTELCARD_DPC_MONTH_NAMES'   =>
                "'".join("','", explode(',', $_CORELANG['TXT_MONTH_ARRAY']))."'",
            // Reformat from "Su,Mo,Tu,We,Th,Fr,Sa"
            // to "'Su','Mo','Tu','We','Th','Fr','Sa'"
            'HOTELCARD_DPC_DAY_NAMES'     => "'".join("','", $arrDow)."'",
            'HOTELCARD_FORM_SUBMIT_BUTTON_1' =>
                Html::getInputButton('bsubmit',
                    $_ARRAYLANG['TXT_HOTELCARD_FORM_SUBMIT_STORE'],
                    'submit', false),
            'HOTELCARD_ROOMTYPE_INDEX_MAX' => count($arrRoomTypes),
        ));

        $index_li = 0;
        foreach ($arrRoomTypes as $room_type_id => $arrRoomType) {
            $room_type_name     = $arrRoomType['name'];
            $number_default     = $arrRoomType['number_default'];
            $price_default      = $arrRoomType['price_default'];
            // We just need the keys to see which are provided
//            $arrFacility_id    = array_keys($arrRoomType['facilities']);
            $arrAvailabilities = $arrRoomType['availabilities'];
//echo("roomtype $room_type_name, default number $number_default, price $price_default, bf ".var_export($breakfast_included, true)."<br />");
            $first_date = true;
            for ($time = $_SESSION['hotelcard']['date_from'];
                $time <= $_SESSION['hotelcard']['date_to'];
                $time += 86400
            ) {
                $date = date('Y-m-d', $time);
                $day = date('j', $time);
                $month = $arrMoy[date('m', $time)];
                $year = date('Y', $time);
                if ($first_date == true || $day == 1) {
                    self::$objTemplate->setVariable(array(
                        'HOTELCARD_ROWCLASS'  => 'row3',
                        'HOTELCARD_DATE'      => "$month $year",
                    ));
                    self::$objTemplate->parse('hotelcard_day');
                    $first_date = false;
                }
                //echo("time $time -> date $date<br />");
                $arrAvailability = (empty($arrAvailabilities[$date])
                  ? array(
                      'number_total'     => $number_default,
                      'number_booked'    => 0,
                      'number_cancelled' => 0,
                      'price'            => $price_default, )
                  : $arrAvailabilities[$date]);
                $number_total     = $arrAvailability['number_total'];
                $number_booked    = $arrAvailability['number_booked'];
                $number_cancelled = $arrAvailability['number_cancelled'];
                $price            = $arrAvailability['price'];
                // Day of the week, number and string abbreviation
                $intDow           = date('w', $time);
                $strDow           = $arrDow[$intDow];
//echo("Room type $room_type_id: $room_type_name: number_total $number_total, number_booked $number_booked, number_cancelled$number_cancelled, price $price<br />");
                self::$objTemplate->setVariable(array(
                    'HOTELCARD_ROWCLASS'  => ($intDow % 6 ? 'row1' : 'row2'),
                    'HOTELCARD_DATE'      => "$strDow, $day.",
                    'HOTELCARD_TOTAL'     => html::getInputText(
                        'availability['.$room_type_id.']['.$date.'][number_total]',
                        $number_total, false, 'style="width: 40px; text-align: right;"'),
                    'HOTELCARD_BOOKED'    => html::getInputText(
                        'availability['.$room_type_id.']['.$date.'][number_booked]',
                        $number_booked, false, 'style="width: 40px; text-align: right;"'),
                    'HOTELCARD_CANCELLED' => html::getInputText(
                        'availability['.$room_type_id.']['.$date.'][number_cancelled]',
                        $number_cancelled, false, 'style="width: 40px; text-align: right;"'),
                    'HOTELCARD_PRICE'     => html::getInputText(
                        'availability['.$room_type_id.']['.$date.'][price]',
                        $price, false, 'style="width: 80px; text-align: right;"'),
                ));
                self::$objTemplate->parse('hotelcard_day');
            }

            // <li> switch
            self::$objTemplate->setGlobalVariable(array(
                'HOTELCARD_ROOMTYPE_INDEX' => ++$index_li,
                'HOTELCARD_ROOMTYPE_DISPLAY' => ($index_li == 1 ? 'block' : 'none'),
                'HOTELCARD_ROOMTYPE_LI_CLASS' => ($index_li == 1 ? '_active' : ''),
                'HOTELCARD_ROOMTYPE_NAME' =>
                    ($room_type_name
                      ? $room_type_name
                      : $_ARRAYLANG['TXT_HOTELCARD_NEW_ROOMTYPE']),
            ));
            self::$objTemplate->touchBlock('hotelcard_roomtype_li');
            self::$objTemplate->parse('hotelcard_roomtype_li');
            self::$objTemplate->touchBlock('hotelcard_roomtype');
            self::$objTemplate->parse('hotelcard_roomtype');
        }
        self::$objTemplate->setVariable('HOTELCARD_FORM_SUBMIT_BUTTON_2',
            Html::getInputButton('bsubmit',
            $_ARRAYLANG['TXT_HOTELCARD_FORM_SUBMIT_STORE'],
            'submit', false));
        JS::registerCode(Html::getJavascript_Element());
        //self::$objTemplate->touchBlock('hotelcard_form_date');
        return true;
    }


    /**
     * Shows the page for editing or adding room types
     *
     * @param   integer   $hotel_id     The selected hotel ID
     * @return  boolean                 True on success, false otherwise
     */
    static function editHotelRoomtypes($hotel_id)
    {
        global $_ARRAYLANG, $_CORELANG;

        $objhotel = Hotel::getById($hotel_id);
// TODO:  Add error message, maybe a redirect
        if (empty($objhotel)) {
            self::addMessage($_ARRAYLANG['TXT_HOTELCARD_ERROR_HOTEL_ID_NOT_FOUND'].' '.$hotel_id);
            return false;
        }

        // Store changes, if any.
        // The type IDs and more information is taken from the post parameters.
        if (isset($_POST['bsubmit'])) self::updateHotel(
            $hotel_id,
            $_SESSION['hotelcard']['date_from'],
            $_SESSION['hotelcard']['date_to']
        );

        // Fetch the room types and availabilities
        $arrRoomTypes = HotelRoom::getTypeArray($hotel_id);
        $arrRoomTypes[-1] = array(
            'name' => '',
            'number_default' => '',
            'price_default' => '',
            'breakfast_included' => '',
            'facilities' => array(),
            'availabilities' => array(),
        );

        // Spray language variables all over
        self::$objTemplate->setGlobalVariable(
            $_ARRAYLANG
          + array(
            'HOTELCARD_FORM_SUBMIT_VALUE' => $_ARRAYLANG['TXT_HOTELCARD_FORM_SUBMIT_STORE'],
            'HOTELCARD_HOTEL_ID'          => $hotel_id,
            'HOTELCARD_ROOMTYPE_INDEX_MAX' => count($arrRoomTypes),
        ));

        // Complete list of all facilites for reference
        $arrAllFacilities = HotelRoom::getFacilityNameArray();
        $index_li = 0;
        foreach ($arrRoomTypes as $room_type_id => $arrRoomType) {
//            self::$objTemplate->setGlobalVariable(array(
//                'HOTELCARD_ROOMTYPE_ID' => $room_type_id,
//            ));

            $room_type_name     = $arrRoomType['name'];
            $number_default     = $arrRoomType['number_default'];
            $price_default      = $arrRoomType['price_default'];
            $breakfast_included = $arrRoomType['breakfast_included'];

            // <li> switch
            self::$objTemplate->setGlobalVariable(array(
                'HOTELCARD_ROOMTYPE_INDEX' => ++$index_li,
                'HOTELCARD_ROOMTYPE_DISPLAY' => ($index_li == 1 ? 'block' : 'none'),
                'HOTELCARD_ROOMTYPE_LI_CLASS' => ($index_li == 1 ? '_active' : ''),
                'HOTELCARD_ROOMTYPE_NAME' =>
                    ($room_type_name
                      ? $room_type_name
                      : $_ARRAYLANG['TXT_HOTELCARD_NEW_ROOMTYPE']),
            ));
            self::$objTemplate->touchBlock('hotelcard_roomtype_li');
            self::$objTemplate->parse('hotelcard_roomtype_li');

            $arrFields = array(
//                'dummy_div_open_'.$room_type_id => array(
//                    'special' =>
//                        '<div id="roomtype-'.$room_type_id.'" style="display: '.
//                        ($room_type_id == 1 ? 'block' : 'none').'">'
//                ),
                'room_type_'.$room_type_id => array(
                    'mandatory' => ($index_li == 1),
                    'label' => 'TXT_HOTELCARD_ROOMTYPE',
                    'input' => Html::getInputText(
                        'roomtype['.$room_type_id.'][room_type]',
                        $room_type_name, false, 'style="width: 200px;"'),
                ),
                'room_available_'.$room_type_id => array(
                    'mandatory' => ($index_li == 1),
                    'label' => 'TXT_HOTELCARD_ROOM_AVAILABLE',
                    'input' => Html::getInputText(
                        'roomtype['.$room_type_id.'][number_default]',
                        $number_default, false, 'style="width: 200px; text-align: right;"'),
                ),
                'room_price_'.$room_type_id => array(
                    'mandatory' => ($index_li == 1),
                    'label' => 'TXT_HOTELCARD_ROOM_PRICE',
                    'input' => Html::getInputText(
                        'roomtype['.$room_type_id.'][price_default]',
                        $price_default, false, 'style="width: 200px; text-align: right;"'),
                ),
                'breakfast_included_'.$room_type_id => array(
                    'mandatory' => ($index_li == 1),
                    'label' => 'TXT_HOTELCARD_BREAKFAST_INCLUDED',
                    'input' => Html::getSelect(
                        'roomtype['.$room_type_id.'][breakfast_included]',
                        HotelRoom::getBreakfastIncludedArray($breakfast_included),
                        $breakfast_included, false, '',
                        'style="width: 200px;"'),
                ),
                // Note: These are checkbox groups and are thus posted as
                // arrays, like 'room_facility_id_1[]'
                'room_facility_id_'.$room_type_id => array(
                    'mandatory' => false,
                    'label' => 'TXT_HOTELCARD_ROOM_FACILITY_ID',
                    'input' => '<span class="inputgroup">'.
                        Html::getCheckboxGroup(
                            'facilities['.$room_type_id.']',
                            $arrAllFacilities, $arrAllFacilities,
                            array_keys($arrRoomType['facilities']),
                            'facilities-'.$room_type_id).
                        "</span>\n",
                ),
//                'dummy_div_close_'.$room_type_id => array(
//                    // This closes the div id=roomtype opened above
//                    'special' => '</div>',
//                ),
            );
            HotelcardLibrary::parseDataTable(self::$objTemplate, $arrFields);
            self::$objTemplate->parse('hotelcard_roomtype');
        }
        JS::registerCode(Html::getJavascript_Element());
        return true;
    }


    /**
     * Show the page for editing the Hotel details, like the contact address
     * or the images
     * @todo    Write me!
     * @param   integer     $hotel_id     The ID of the Hotel to be edited
     * @return  boolean                   True on success, false otherwise
     */
    static function editHotelDetails($hotel_id)
    {
        global $_ARRAYLANG;

self::addMessage("Ich bin noch nicht geschrieben!");
return false;
die("Hotelcard::editHotelDetails($hotel_id):  Write me first!");

        $arrFields = array(
            'lang_id' => array(
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_LANG_ID',
                'input' => FWLanguage::getMenuActiveOnly(
                    (isset($_SESSION['hotelcard']['lang_id'])
                        ? $_SESSION['hotelcard']['lang_id'] : FRONTEND_LANG_ID),
                    'lang_id', 'document.forms.form_hotelcard.submit();'),
            ),
            'hotel_name' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_HOTEL_NAME',
                'input' => Html::getInputText('hotel_name',
                    (isset($_SESSION['hotelcard']['hotel_name'])
                        ? $_SESSION['hotelcard']['hotel_name'] : ''), false,
                    'style="text-align: left;"'),
            ),
            'group' => array( // Mind that this goes into a lookup table!
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_GROUP',
                'input' => Html::getInputText('group',
                    (isset($_SESSION['hotelcard']['group'])
                        ? $_SESSION['hotelcard']['group'] : ''), false,
                    'style="text-align: left;"'),
            ),
            'accomodation_type_id' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_ACCOMODATION_TYPE_ID',
                'input' => Html::getSelect(
                    'accomodation_type_id',
                    HotelAccomodationType::getNameArray(),
                    (isset($_SESSION['hotelcard']['accomodation_type_id'])
                        ? $_SESSION['hotelcard']['accomodation_type_id'] : 0)),
            ),
            'hotel_address' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_HOTEL_ADDRESS',
                'input' => Html::getInputText('hotel_address',
                    (isset($_SESSION['hotelcard']['hotel_address'])
                        ? $_SESSION['hotelcard']['hotel_address'] : ''), false,
                    'style="text-align: left;"'),
            ),
            'hotel_region' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_HOTEL_REGION',
                'input' => Html::getSelect(
                'hotel_region',
                    (isset($_SESSION['hotelcard']['hotel_region'])
                      ? array()
                      :   array('' => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_REGION_PLEASE_CHOOSE']))
                        + State::getArray(true),
                    (isset($_SESSION['hotelcard']['hotel_region'])
                      ? $_SESSION['hotelcard']['hotel_region']
                      : (isset($_SESSION['hotelcard']['hotel_location'])
                          ? State::getByLocation($_SESSION['hotelcard']['hotel_location'])
                          : '')), 'hotel_region',
                    'new Ajax.Updater(\'hotel_location\', \'index.php?section=hotelcard&amp;act=get_locations&amp;state=\'+document.getElementById(\'hotel_region\').value, { method: \'get\' });'),
                    //document.forms.form_hotelcard.submit();'),
            ),
            'hotel_location' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_HOTEL_LOCATION',
                'input' => Html::getSelect('hotel_location',
                    (isset($_SESSION['hotelcard']['hotel_region'])
                      ? Location::getArrayByState($_SESSION['hotelcard']['hotel_region'], '%1$s (%2$s)')
                      : array($_ARRAYLANG['TXT_HOTELCARD_PLEASE_CHOOSE_REGION'])),
                    (isset($_SESSION['hotelcard']['hotel_location'])
                        ? $_SESSION['hotelcard']['hotel_location'] : ''), 'hotel_location',
                    '', 'style="text-align: left;"'),
            ),
            'contact_data' => array(
                'mandatory' => false,
                'label' => $_ARRAYLANG['TXT_HOTELCARD_CONTACT_DATA'],
                'input' => '',
            ),
            'contact_name' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_CONTACT_NAME',
                'input' => Html::getInputText('contact_name',
                    (isset($_SESSION['hotelcard']['contact_name'])
                        ? $_SESSION['hotelcard']['contact_name'] : ''), false,
                    'style="text-align: left;"'),
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
                    (isset($_SESSION['hotelcard']['contact_gender'])
                        ? $_SESSION['hotelcard']['contact_gender'] : '')).
                    "</span>\n",
            ),
            'contact_position' => array(
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_CONTACT_POSITION',
                'input' => Html::getInputText('contact_position',
                    (isset($_SESSION['hotelcard']['contact_position'])
                        ? $_SESSION['hotelcard']['contact_position'] : ''), false,
                    'style="text-align: left;"'),
            ),
            'contact_department' => array(
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_CONTACT_DEPARTMENT',
                'input' => Html::getInputText('contact_department',
                    (isset($_SESSION['hotelcard']['contact_department'])
                        ? $_SESSION['hotelcard']['contact_department'] : ''), false,
                    'style="text-align: left;"'),
            ),
            'contact_phone' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_CONTACT_PHONE',
                'input' => Html::getInputText('contact_phone',
                    (isset($_SESSION['hotelcard']['contact_phone'])
                        ? $_SESSION['hotelcard']['contact_phone'] : ''), false,
                        'style="text-align: left;"'),
            ),
            'contact_fax' => array(
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_CONTACT_FAX',
                'input' => Html::getInputText('contact_fax',
                    (isset($_SESSION['hotelcard']['contact_fax'])
                        ? $_SESSION['hotelcard']['contact_fax'] : ''), false,
                        'style="text-align: left;"'),
            ),
            'contact_email' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_CONTACT_EMAIL',
                'input' => Html::getInputText('contact_email',
                    (isset($_SESSION['hotelcard']['contact_email'])
                        ? $_SESSION['hotelcard']['contact_email'] : ''), false,
                    'style="text-align: left;"'),
            ),
            'contact_email_retype' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_CONTACT_EMAIL_RETYPE',
                'input' => Html::getInputText('contact_email_retype', '', false,
                    'style="text-align: left;"'),
            ),
            'numof_rooms' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_NUMOF_ROOMS',
                'input' => Html::getInputText('numof_rooms',
                    (isset($_SESSION['hotelcard']['numof_rooms'])
                        ? $_SESSION['hotelcard']['numof_rooms'] : ''), false,
                    'style="text-align: left;"'),
            ),
            'description_text' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_DESCRIPTION_TEXT',
                'input' => Html::getTextarea('description_text',
                    (isset($_SESSION['hotelcard']['description_text'])
                        ? $_SESSION['hotelcard']['description_text'] : ''),
                    '', '',
                    'onkeyup="lengthLimit(this, this.form.count_min, this.form.count_max, 100, 500);"').
                '<br />'.
                sprintf($_ARRAYLANG['TXT_HOTELCARD_TEXT_LENGTH_MINIMUM_MAXIMUM'],
                    html::getInputText('count_min', 100, 'count_min',
                        'disabled="disabled" style="width: 30px;"'),
                    html::getInputText('count_max', 500, 'count_max',
                        'disabled="disabled" style="width: 30px;"')),
            ),
            'rating' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_RATING',
                'input' => Html::getSelect('rating',
                    HotelRating::getArray(),
                    (isset($_SESSION['hotelcard']['rating'])
                        ? $_SESSION['hotelcard']['rating'] : '')),
            ),
            'hotel_facilities' => array(
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_HOTEL_FACILITY_ID',
                'input' => '',
            ),
        );
        foreach (HotelFacility::getGroupNameArray() as $group_id => $group_name) {
//echo("Setting up group ID $group_id, name $group_name<br />");
            $arrFacilities = HotelFacility::getFacilityNameArray($group_id, true);
//echo("Setting up Facilities: ".var_export($arrFacilities, true)."<br />");
            $arrFields['hotel_facility_id'][$group_id] = array(
                'mandatory' => false,
                'label' => $group_name,
                'input' => '<span class="inputgroup">'.
                    Html::getCheckboxGroup(
                        'hotel_facility_id',
                        $arrFacilities, $arrFacilities,
                        (   isset($_SESSION['hotelcard']['hotel_facility_id'])
                          ? array_keys($_SESSION['hotelcard']['hotel_facility_id'])
                          : '')).
                    "</span>\n",
            );
        }
        $arrFields += array(
            'hotel_uri' => array(
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_HOTEL_URI',
                'input' => Html::getInputText('hotel_uri',
                    (empty($_SESSION['hotelcard']['hotel_uri'])
                        ? '' // 'http://'
                        : $_SESSION['hotelcard']['hotel_uri']), false,
                    'style="text-align: left;"'),
            ),
            'image_id' => array(
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_IMAGE',
                'input' => Html::getImageChooserUpload(
                    Image::getFromSessionByKey('image', self::IMAGE_PATH_HOTEL_DEFAULT),
                    'image', self::IMAGETYPE_TITLE),
            ),
        );
        // Verify the data already present
        if (   isset($_POST['bsubmit'])
            && self::verifyAndStoreHotel($arrFields)) {
//echo("After:<br />".htmlentities(var_export($arrFields, true), ENT_QUOTES, CONTREXX_CHARSET)."<hr />");
            self::addMessage($_ARRAYLANG['TXT_HOTELCARD_HOTEL_UPDATED_SUCCESSFULLY']);
        }
//echo("Showing step 5<br />");
        HotelcardLibrary::parseDataTable(self::$objTemplate, $arrFields);
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

//echo("Hotelcard::updateHotel($hotel_id):  Entered");

        if (empty($_POST)) {
//echo("No POST<br />");
            return '';
        }
//echo("Hotelcard::updateHotel($hotel_id):  POST:<br />".nl2br(var_export($_POST, true))."<hr />");

//DBG::enable_adodb_debug();
//DBG::enable_error_reporting();
        $result = '';

        if (isset($_POST['roomtype'])) {
            foreach ($_POST['roomtype'] as $room_type_id => $arrRoomtype) {
    //echo("Roomtype<br />");
                // Array indexed by room type IDs,
                // containing room type parameters:
                //  roomtype[room_type_id] =>
                //    array(number_total, number_booked, number_cancelled, price)

                $room_type = $arrRoomtype['room_type'];

                // Ignore room types with empty names
                if (empty($room_type)) {
    //echo("Skipping Room type ID $room_type_id<br />");
                    continue;
                }
    //echo("Roomtype ID $room_type_id<br />");

                $number_default = $arrRoomtype['number_default'];
                $price_default = $arrRoomtype['price_default'];
                $breakfast_included = $arrRoomtype['breakfast_included'];

                if (!HotelRoom::storeType(
                    $hotel_id,
                    $number_default, $price_default,
                    $breakfast_included,
                    ($room_type_id > 0 ? $room_type_id : 0))
                ) {
                    self::addMessage(sprintf(
                        $_ARRAYLANG['TXT_HOTELCARD_ERROR_STORING_ROOMTYPE'],
                        $room_type)
                    );
                    return false;
                }
                // Rename the room type
                if (!HotelRoom::renameType(
                    $room_type_id, $room_type)) {
    //echo("ERROR: Failed to rename Roomtype ID $room_type_id to $room_type<br />");
                    return false;
                }
                if ($result === '') $result = true;
            }
        }

//echo("Room types:<br />".var_export($arrRoomtype, true)."<br />");
        if (isset($_POST['availability'])) {
            foreach ($_POST['availability'] as $room_type_id => $arrAvailability) {
                // Array indexed by room type IDs,
                // containing date => availability pairs:
                //  availability[room_type_id][date] =>
                //    array(number_total, number_booked, number_cancelled, price)

    //echo("Availability<br />");
                if (!HotelRoom::storeAvailabilityArray(
                    $room_type_id, $arrAvailability)
                ) {
                    self::addMessage(sprintf(
                        $_ARRAYLANG['TXT_HOTELCARD_ERROR_STORING_AVAILABILITY'],
                        $room_type)
                    );
                    return false;
                }
                if ($result === '') $result = true;
            }
        }
        if ($result === true) {
            self::addMessage(
                $_ARRAYLANG['TXT_HOTELCARD_HOTEL_UPDATED_SUCCESSFULLY']);
        }
        return true;
    }


    static function getEditHotelMenu()
    {
        global $_ARRAYLANG;

        return '
<a href="index.php?section=hotelcard&amp;cmd=edit_hotel" title="">
  '.$_ARRAYLANG['TXT_HOTELCARD_EDIT_HOTEL_AVAILABILITY'].'
</a>
<a href="index.php?section=hotelcard&amp;cmd=edit_hotel_roomtypes" title="">
  '.$_ARRAYLANG['TXT_HOTELCARD_EDIT_HOTEL_ROOMTYPES'].'
</a>
<a href="index.php?section=hotelcard&amp;cmd=edit_hotel_details" title="">
  '.$_ARRAYLANG['TXT_HOTELCARD_EDIT_HOTEL_DETAILS'].'
</a>
<br /><br />
';
    }


    /**
     * Shows the terms and conditions
     *
     * Picks the terms from the settings table in the current frontend language
     * @return  boolean           True on success, false otherwise
     */
    static function terms()
    {
        return self::$objTemplate->setVariable(
            'TXT_HOTELCARD_NOTE_TEXT',
            SettingDb::getValue('terms_and_conditions_'.FRONTEND_LANG_ID)
        );
    }


    /**
     * Adds the string to the status messages.
     *
     * If necessary, inserts a line break tag (<br />) between
     * messages.
     * @param   string  $text         The text to add
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function addMessage($text)
    {
        self::$message .=
            (self::$message ? '<br />' : '').
            $text;
    }


    /**
     * Handle any error occurring in this class.
     *
     * Tries to fix known problems with the database.
     * @global  mixed     $objDatabase    Database object
     * @return  boolean                   False.  Always.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function errorHandler()
    {
        global $objDatabase;

die("Hotelcard::errorHandler(): disabled!<br />");

        // Verify that the module is installed
        $query = "
            SELECT 1
              FROM ".DBPREFIX."modules
             WHERE name='hotelcard'
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        if ($objResult->RecordCount() == 0) {
            $query = "
                INSERT INTO ".DBPREFIX."modules (
                  `id`, `name`, `description_variable`,
                  `status`, `is_required`, `is_core`
                ) VALUES (
                  '101', 'hotelcard', 'TXT_HOTELCARD_MODULE_DESCRIPTION',
                  'y', '0', '0'
                )
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                return false;
            }
        }

        // Verify that the backend area is present
        $query = "
            SELECT 1
              FROM ".DBPREFIX."backend_areas
             WHERE uri='index.php?cmd=hotelcard'
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        if ($objResult->RecordCount() == 0) {
            $query = "
                INSERT INTO ".DBPREFIX."backend_areas (
                  `area_id`, `parent_area_id`, `type`, `area_name`,
                  `is_active`, `uri`, `target`, `module_id`, `order_id`, `access_id`
                ) VALUES (
                  126, '2', 'navigation', 'TXT_HOTELCARD',
                  '1', 'index.php?cmd=hotelcard', '_self', '101', '0', '126'
                );
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                return false;
            }
        }

        return false;
    }


    function getPageTitle()
    {
        return self::$page_title;
    }


    static function getStepString()
    {
        global $_ARRAYLANG;

        return ' - '.sprintf(
            $_ARRAYLANG['TXT_HOTELCARD_STEP_NUMBER'],
            $_SESSION['hotelcard']['step_current']);
    }

}

?>
