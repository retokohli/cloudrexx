<?php

define('_HOTELCARD_DEBUG', 0);

/* TEST */
/*
$_SESSION['hotelcard'] = array (

'lang_id' => '2',
'hotel_name' => 'Heartbreak Hotel',
'group_id' => 'Chain #5',
'accomodation_type_id' => '5',
'hotel_address' => 'Penny Lane 1',
'hotel_zip' => '54321',
'hotel_location' => 'Memphis, TN',
'hotel_region_id' => '14',
'hotel_country_id' => '17',
'contact_name' => 'Peter Kloppel',
'contact_gender' => 'm',
'contact_position' => 'Head of Heads',
'contact_department' => 'HIP',
'contact_phone' => '+12 345 67 89',
'contact_fax' => '+12 345 67 90',
'contact_email' => 'pk@heartbreak.com',
'contact_email_retype' => 'pk@heartbreak.com',
'submit' => 'Continue',
'numof_rooms' => '132',
'description_text_id' => 'Nice place to spend the weekend',
'hotel_facility_id' =>
array (
264 => 'All Public and Private spaces non-smoking',
265 => 'Allergy-Free Room Available',
266 => 'Bar',
267 => 'Breakfast Buffet',
269 => 'Continental Breakfast',
270 => 'Design Hotel',
280 => 'Newspapers',
281 => 'Non-Smoking Rooms',
282 => 'Parking',
283 => 'Pets Allowed',
290 => 'Terrace',
291 => 'Valet Parking',
292 => 'BQ Facilities',
293 => 'Billiards',
305 => 'Hammam',
306 => 'Hiking',
307 => 'Horse Riding',
308 => 'Jacuzzi',
309 => 'Karaoke',
327 => 'ATM/Cash Machine on site',
328 => 'Babysitting/Child Services',
329 => 'Bicycle Rental',
330 => 'Breakfast in the Room',
331 => 'Bridal Suite',
349 => 'VIP Room Facilities',
350 => 'Wi-Fi/Wireless LAN',
),
'hotel_uri' => 'http://www.elvis.com/epheartbreakhotel',
'hotel_image' =>
array (
'name' => 'hh_img_regroom2.jpg',
'type' => 'image/jpeg',
'tmp_name' => 'C:\\Programme\\xampp\\tmp\\php2C.tmp',
'error' => 0,
'size' => 10544,
),
'hotel_rating' => '3',
'checkin_from' => '05:00',
'checkin_to' => '22:00',
'checkout_from' => '05:00',
'checkout_to' => '12:00',
'reservation_name' => 'Federal Reserve',
'reservation_gender' => 'f',
'reservation_phone' => '+88 777 66 55',
'reservation_fax' => '+88 777 66 44',
'reservation_email' => 'reservation@heartbreak.com',
'reservation_email_retype' => 'reservation@heartbreak.com',
'found_how' => 'TV show',
'room_type' =>
array (
1 => 'Guest Room',
2 => 'Elvis Themed Suite',
3 => '',
4 => '',
),
'room_available' =>
array (
1 => '128',
2 => '4',
3 => '',
4 => '',
),
'room_price' =>
array (
1 => '112',
2 => '549',
3 => '',
4 => '',
),
'room_facility_id' =>
array (
1 =>
array (
5 => 'Balkon',
6 => 'Badewanne',
7 => 'Minibar',
8 => 'Radio',
9 => 'Etagenbad',
),
2 =>
array (
5 => 'Balkon',
6 => 'Badewanne',
8 => 'Radio',
9 => 'Etagenbad',
10 => 'Dusche',
11 => 'Telefon',
),
),
//'step' => '4',
//'confirm_terms' => '1',
//'registration_id' => '8',
//'registration_date' => '24.08.2009',
);
*/

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
require_once ASCMS_CORE_PATH.'/Country.class.php'; // Also contains the region class
require_once ASCMS_CORE_PATH.'/Creditcard.class.php';
require_once ASCMS_CORE_PATH.'/Filetype.class.php';
require_once ASCMS_CORE_PATH.'/Html.class.php';
require_once ASCMS_CORE_PATH.'/Imagetype.class.php';
require_once ASCMS_CORE_PATH.'/Image.class.php';
require_once ASCMS_CORE_PATH.'/SettingDb.class.php';
require_once ASCMS_CORE_PATH.'/Sorting.class.php';
require_once ASCMS_CORE_PATH.'/Text.class.php';
require_once ASCMS_FRAMEWORK_PATH.'/Language.class.php';
require_once 'lib/Hotel.class.php';
require_once 'lib/HotelAccomodationType.class.php';
require_once 'lib/HotelCheckInOut.class.php';
require_once 'lib/HotelFacility.class.php';
require_once 'lib/HotelcardLibrary.class.php';
require_once 'lib/HotelRating.class.php';
require_once 'lib/HotelRoom.class.php';
require_once 'lib/RelHotelCreditcard.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Download.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Product.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Property.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Reference.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/RelProductCategory.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/RelProductProperty.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/RelProductReference.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/RelUserContact.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/constants.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/sorting.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Material.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Manufacturer.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Line.class.php';
//lib/FRAMEWORK/File.class.php

define('HOTELCARD_REFERENCE_VIEW_COUNT', 2);
define('TEXT_CORE_REGION_NAME', 'CORE_REGION_NAME');

/**
 * Class Hotelcard
 *
 * Frontend for the Hotelcard module.
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
     * Page template
     * @var HTML_Template_Sigma
     */
    private static $objTemplate = false;
    /**
     * Page Title
     *
     * Only used by index.php if its not the empty string
     * @var   string
     */
    private static $page_title = '';
    /**
     * The page content
     *
     * Handed over when {@see getPage()} is called
     * @var   string
     */
    private static $page_content = '';
    /**
     * Status / error message
     * @var string
     */
    private static $message = '';


    /**
     * Determine the page to be shown and call appropriate methods.
     * @return  string            The finished HTML page content
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getPage($page_content)
    {

if (_HOTELCARD_DEBUG & 1) DBG::enable_error_reporting();
if (_HOTELCARD_DEBUG & 2) DBG::enable_adodb_debug();

        self::$page_content = $page_content;
        // PEAR Sigma template
        self::$objTemplate = new HTML_Template_Sigma('.');
        self::$objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        self::$objTemplate->setTemplate(self::$page_content, true, true);

        if (isset($_GET['cmd'])) $_GET['act'] = $_GET['cmd'];
        if (empty($_GET['act'])) $_GET['act'] = '';

        // Flag for error handling
        $result = true;
        switch($_GET['act']) {
            case 'add_hotel':
                // Add a new hotel using the wizard
                $result &= self::addHotel();
                break;
            case 'reset':
                // Reset the add hotel wizard form data and
                // go back to the hotelcard start page
                unset($_SESSION['hotelcard']);
            case 'edit_hotel':
//echo("Edit Hotel<br />");
                // Edit the hotel/s associated with the logged in user
                $result &= self::editHotel();
                break;
            case 'overview':
            default:
                $result &= self::overview();
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
        global $_ARRAYLANG;

        // Gobble up all posted data whatsoever
        foreach ($_POST as $key => $value) {
            $_SESSION['hotelcard'][$key] = $value;
        }
//echo("Added POST<br />".nl2br(var_export($_POST, true))."<hr />");
        foreach ($_FILES as $key => $value) {
            $_SESSION['hotelcard'][$key] = $value;
        }
//echo("Added FILES<br />".nl2br(var_export($_FILES, true))."<hr />".(isset($_SESSION['hotelcard']) ? "Collected<br />".nl2br(var_export($_SESSION['hotelcard'], true))."<hr />" : ''));
        // Number of the current step
        $step_posted = 1;
        if (empty($_SESSION['hotelcard']['step'])) {
            $_SESSION['hotelcard']['step'] = 0;
        } else {
            $step_posted = $_SESSION['hotelcard']['step'];
        }
/* echo("Posted step empty, resetting wizard<br />");
            if (empty($step)) $step = 1;
            // Reset the wizard step counter
            $_SESSION['hotelcard_step'] = 0;
            // Also drop the whole hotel registration array,
            // start from scratch
            unset($_SESSION['hotelcard']); */
        // Start verifying the data from the step posted
        $step = $step_posted;
        $result_step = true;
        while ($step <= self::HOTEL_REGISTRATION_STEPS) {
//echo("Trying step $step<br />");
            // Returns false if it misses some data,
            // continue with the next step if true is returned.
            // After each step has been shown once, the single steps
            // must verify their data
            $result_step = call_user_func(array('self', 'addHotelStep'.$step));
            if (!$result_step) {
//echo("Step $step incomplete.  Session step ".$_SESSION['hotelcard']['step']."<br />");
                break;
            } else {
//echo("Step $step successfully completed.  Session step ".$_SESSION['hotelcard']['step']."<br />");
                ++$step;
                if ($step > self::HOTEL_REGISTRATION_STEPS) {
                    // After the last step, get back to the hotelcard start page
                    header('Location: index.php?section=hotelcard');
                    exit;
                }
            }
        }
        // So the result was false, and the current step is shown
//echo("Trying step $step FAILED<br />");
        self::$objTemplate->setVariable(array(
            'HOTELCARD_STEP' => $step,
            'HOTELCARD_FORM_ACTION' => htmlentities(
                $_SERVER['REQUEST_URI'], ENT_QUOTES, CONTREXX_CHARSET),
//            'HOTELCARD_FORM_SUBMIT_NAME' => 'submit',
            'HOTELCARD_FORM_SUBMIT_VALUE' =>
                ($step < self::HOTEL_REGISTRATION_STEPS
                  ? $_ARRAYLANG['TXT_HOTELCARD_FORM_SUBMIT_CONTINUE']
                  : $_ARRAYLANG['TXT_HOTELCARD_FORM_SUBMIT_FINISH']
                ),
            'HOTELCARD_PAGE_NUMBER' => sprintf(
                $_ARRAYLANG['TXT_HOTELCARD_PAGE_NUMBER_X_OF_Y'],
                $step, self::HOTEL_REGISTRATION_STEPS),
            'TXT_HOTELCARD_NOTE' => sprintf(
                $_ARRAYLANG['TXT_HOTELCARD_HOTEL_ADD_STEP'.$step.'_NOTE'],
                // Replace format parameters with
                // 1 - Customer name
                // 2 - Registration date
                // 3 - hotelcard.ch online ID
                (isset($_SESSION['hotelcard']['contact_name'])
                    ? $_SESSION['hotelcard']['contact_name'] : ''),
                (isset($_SESSION['hotelcard']['registration_date'])
                    ? date(ASCMS_DATE_FORMAT,
                        $_SESSION['hotelcard']['registration_date'])
                    : ''),
                (isset($_SESSION['hotelcard']['registration_id'])
                    ? $_SESSION['hotelcard']['registration_id'] : '')
            ),
        ));
//echo("Steps done<br />");
        return $result_step;
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

        $mandatoryFields = array(
            'group_id', // Mind that this goes into a lookup table!
            'accomodation_type_id',
            'lang_id',
            'hotel_name',
            'hotel_address',
            'hotel_zip',
            'hotel_location',
            'hotel_region_id',
            'contact_name',
            'contact_gender',
            'contact_position',
            'contact_department',
            'contact_phone',
            'contact_email',
            'contact_email_retype',
        );
        // Verify the data already present if it's the current step
        if ($_SESSION['hotelcard']['step'] == 1) {
            $flagComplete = true;
            foreach ($mandatoryFields as $name) {
                if (empty($_SESSION['hotelcard'][$name])) {
//echo("Step 1: missing mandatory field value for $name<br />");
                    $flagComplete = false;
                    self::addMessage($_ARRAYLANG['TXT_HOTELCARD_MISSING_'.strtoupper($name)]);
                }
            }
            // Only verify the e-mail addresses after the fields have been
            // filled out
            if ($flagComplete) {
                if ($_SESSION['hotelcard']['contact_email'] !=
                      $_SESSION['hotelcard']['contact_email_retype']) {
                    $flagComplete = false;
                    self::addMessage($_ARRAYLANG['TXT_HOTELCARD_EMAILS_DO_NOT_MATCH']);
                } elseif (!FWValidator::isEmail($_SESSION['hotelcard']['contact_email'])) {
                    $flagComplete = false;
                    self::addMessage($_ARRAYLANG['TXT_HOTELCARD_EMAIL_IS_INVALID']);
                }
            }
            if ($flagComplete) {
//echo("Step 1 complete<br />");
                return true;
            }
        }
//echo("Showing step 1<br />");

        $arrData = array(
            'TXT_HOTELCARD_LANG_ID' =>
                FWLanguage::getMenuActiveOnly(
                    (isset($_SESSION['hotelcard']['lang_id'])
                        ? $_SESSION['hotelcard']['lang_id'] : FRONTEND_LANG_ID),
                    'lang_id', 'document.forms.form_hotelcard.submit();'),
            'TXT_HOTELCARD_HOTEL_NAME' => Html::getInputText('hotel_name',
                (isset($_SESSION['hotelcard']['hotel_name'])
                    ? $_SESSION['hotelcard']['hotel_name'] : '')),
            'TXT_HOTELCARD_GROUP_ID' => Html::getInputText('group_id',
                (isset($_SESSION['hotelcard']['group_id'])
                    ? $_SESSION['hotelcard']['group_id'] : '')),
            'TXT_HOTELCARD_ACCOMODATION_TYPE_ID' => Html::getSelect(
                'accomodation_type_id',
                HotelAccomodationType::getNameArray(),
                (isset($_SESSION['hotelcard']['accomodation_type_id'])
                    ? $_SESSION['hotelcard']['accomodation_type_id'] : 0)),
            'TXT_HOTELCARD_HOTEL_ADDRESS' => Html::getInputText('hotel_address',
                (isset($_SESSION['hotelcard']['hotel_address'])
                    ? $_SESSION['hotelcard']['hotel_address'] : '')),
            'TXT_HOTELCARD_HOTEL_ZIP' => Html::getInputText('hotel_zip',
                (isset($_SESSION['hotelcard']['hotel_zip'])
                    ? $_SESSION['hotelcard']['hotel_zip'] : '')),
            'TXT_HOTELCARD_HOTEL_LOCATION' => Html::getInputText('hotel_location',
                (isset($_SESSION['hotelcard']['hotel_location'])
                    ? $_SESSION['hotelcard']['hotel_location'] : '')),
            'TXT_HOTELCARD_HOTEL_REGION_ID' => Html::getSelect(
                'hotel_region_id', Region::getNameArray(),
                (isset($_SESSION['hotelcard']['hotel_region_id'])
                    ? $_SESSION['hotelcard']['hotel_region_id'] : '')),
            'TXT_HOTELCARD_HOTEL_COUNTRY_ID' => Html::getSelect(
                'hotel_country_id', Country::getNameArray(),
                (isset($_SESSION['hotelcard']['hotel_country_id'])
                    ? $_SESSION['hotelcard']['hotel_country_id'] : '')),
            'TXT_HOTELCARD_CONTACT_DATA' => '',
            'TXT_HOTELCARD_CONTACT_NAME' => Html::getInputText('contact_name',
                (isset($_SESSION['hotelcard']['contact_name'])
                    ? $_SESSION['hotelcard']['contact_name'] : '')),
            'TXT_HOTELCARD_CONTACT_GENDER' => Html::getRadioGroup(
                'contact_gender',
                array(
                    'm' => $_ARRAYLANG['TXT_HOTELCARD_GENDER_MALE'],
                    'f' => $_ARRAYLANG['TXT_HOTELCARD_GENDER_FEMALE'],
                ),
                (isset($_SESSION['hotelcard']['contact_gender'])
                    ? $_SESSION['hotelcard']['contact_gender'] : '')
                ),
            'TXT_HOTELCARD_CONTACT_POSITION' => Html::getInputText('contact_position',
                (isset($_SESSION['hotelcard']['contact_position'])
                    ? $_SESSION['hotelcard']['contact_position'] : '')),
            'TXT_HOTELCARD_CONTACT_DEPARTMENT' =>
                Html::getInputText('contact_department',
                    (isset($_SESSION['hotelcard']['contact_department'])
                        ? $_SESSION['hotelcard']['contact_department'] : '')),
            'TXT_HOTELCARD_CONTACT_PHONE' => Html::getInputText('contact_phone',
                (isset($_SESSION['hotelcard']['contact_phone'])
                    ? $_SESSION['hotelcard']['contact_phone'] : '')),
            'TXT_HOTELCARD_CONTACT_FAX' => Html::getInputText('contact_fax',
                (isset($_SESSION['hotelcard']['contact_fax'])
                    ? $_SESSION['hotelcard']['contact_fax'] : '')),
            'TXT_HOTELCARD_CONTACT_EMAIL' => Html::getInputText('contact_email',
                (isset($_SESSION['hotelcard']['contact_email'])
                    ? $_SESSION['hotelcard']['contact_email'] : '')),
            'TXT_HOTELCARD_EMAIL_RETYPE' =>
                Html::getInputText('contact_email_retype', ''),
        );
        foreach ($arrData as $placeholder => $element) {
//echo("Language variable $placeholder => ".$_ARRAYLANG[$placeholder]."<br />");
            self::$objTemplate->setVariable(array(
                'HOTELCARD_DATA_LABEL' => $_ARRAYLANG[$placeholder],
                'HOTELCARD_DATA_INPUT' => $element,
            ));
            self::$objTemplate->parse('hotelcard_data');
        }
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

        $mandatoryFields = array(
            'numof_rooms',
            'description_text_id',
            'hotel_facility_id',
            'hotel_uri',
            'hotel_image',
            'hotel_rating',
            'checkin_from',
            'checkin_to',
            'checkout_from',
            'checkout_to',
            'reservation_name',
            'reservation_gender',
            'reservation_phone',
            'reservation_fax',
            'reservation_email',
            'reservation_email_retype',
            'found_how',
        );
        // Verify the data already present
        if ($_SESSION['hotelcard']['step'] == 2) {
            $flagComplete = true;
            foreach ($mandatoryFields as $name) {
                if (empty($_SESSION['hotelcard'][$name])) {
//echo("Step 2: missing mandatory field value for $name<br />");
                    $flagComplete = false;
                    self::addMessage($_ARRAYLANG['TXT_HOTELCARD_MISSING_'.strtoupper($name)]);
                }
            }
            // Only verify the e-mail addresses after the fields have been
            // filled out
            if ($flagComplete) {
                if ($_SESSION['hotelcard']['reservation_email'] !=
                      $_SESSION['hotelcard']['reservation_email_retype']) {
                    $flagComplete = false;
                    self::addMessage($_ARRAYLANG['TXT_HOTELCARD_EMAILS_DO_NOT_MATCH']);
                } elseif (!FWValidator::isEmail($_SESSION['hotelcard']['reservation_email'])) {
                    $flagComplete = false;
                    self::addMessage($_ARRAYLANG['TXT_HOTELCARD_EMAIL_IS_INVALID']);
                }
            }
            if (   $_SESSION['hotelcard']['checkin_to']
                && $_SESSION['hotelcard']['checkin_from'] >
                      $_SESSION['hotelcard']['checkin_to']) {
                $flagComplete = false;
                self::addMessage($_ARRAYLANG['TXT_HOTELCARD_CHECKIN_FROM_AFTER_TO']);
            } elseif (
                   $_SESSION['hotelcard']['checkout_to']
                && $_SESSION['hotelcard']['checkout_from'] >
                      $_SESSION['hotelcard']['checkout_to']) {
                $flagComplete = false;
                self::addMessage($_ARRAYLANG['TXT_HOTELCARD_CHECKOUT_FROM_AFTER_TO']);
            }
            if ($flagComplete) {
//echo("Step 2 complete<br />");
                return true;
            }
        }
//echo("Showing step 2<br />");

        $arrData = array(
//            'TXT_HOTELCARD_ADDITIONAL_DATA' => '',
            'TXT_HOTELCARD_NUMOF_ROOMS' =>
                Html::getInputText('numof_rooms',
                    (isset($_SESSION['hotelcard']['numof_rooms'])
                        ? $_SESSION['hotelcard']['numof_rooms'] : '')),
            'TXT_HOTELCARD_DESCRIPTION_TEXT_ID' =>
                Html::getTextarea('description_text_id',
                (isset($_SESSION['hotelcard']['description_text_id'])
                    ? $_SESSION['hotelcard']['description_text_id'] : '')),
            'TXT_HOTELCARD_HOTEL_FACILITY_ID' => '', // See below
        );
        foreach ($arrData as $placeholder => $element) {
//echo("Language variable $placeholder => ".$_ARRAYLANG[$placeholder]."<br />");
            self::$objTemplate->setVariable(array(
                'HOTELCARD_DATA_LABEL' => $_ARRAYLANG[$placeholder],
                'HOTELCARD_DATA_INPUT' => $element,
            ));
            self::$objTemplate->parse('hotelcard_data');
        }
        foreach (HotelFacility::getGroupNameArray() as $group_id => $group_name) {
            $arrFacilities = HotelFacility::getFacilityNameArray($group_id);
// TODO: Limit the number of facilities shown here.
            self::$objTemplate->setVariable(array(
                'HOTELCARD_DATA_LABEL' => $group_name,
                'HOTELCARD_DATA_INPUT' => Html::getCheckboxGroup(
                    'hotel_facility_id',
                    $arrFacilities, $arrFacilities,
                    (   isset($_SESSION['hotelcard']['hotel_facility_id'])
                      ? array_keys($_SESSION['hotelcard']['hotel_facility_id'])
                      : '')
                ),
            ));
            self::$objTemplate->parse('hotelcard_data');
        }
        $arrData = array(
            'TXT_HOTELCARD_HOTEL_URI' =>
                Html::getInputText('hotel_uri',
                    (isset($_SESSION['hotelcard']['hotel_uri'])
                        ? $_SESSION['hotelcard']['hotel_uri'] : '')),
            'TXT_HOTELCARD_HOTEL_IMAGE' =>
                Html::getInputFileupload('hotel_image'),
            'TXT_HOTELCARD_HOTEL_RATING' =>
                HotelRating::getMenu('hotel_rating',
                    (isset($_SESSION['hotelcard']['hotel_rating'])
                        ? $_SESSION['hotelcard']['hotel_rating'] : '')),
            'TXT_HOTELCARD_CHECKIN_FROM_TO' =>
                HotelCheckInOut::getMenuCheckin('checkin_from',
                    (isset($_SESSION['hotelcard']['checkin_from'])
                        ? $_SESSION['hotelcard']['checkin_from'] : '')).
                '&nbsp;-&nbsp;'.
                HotelCheckInOut::getMenuCheckin('checkin_to',
                    (isset($_SESSION['hotelcard']['checkin_to'])
                        ? $_SESSION['hotelcard']['checkin_to'] : '')),
            'TXT_HOTELCARD_CHECKOUT_FROM_TO' =>
                HotelCheckInOut::getMenuCheckout('checkout_from',
                    (isset($_SESSION['hotelcard']['checkout_from'])
                        ? $_SESSION['hotelcard']['checkout_from'] : '')).
                '&nbsp;-&nbsp;'.
                HotelCheckInOut::getMenuCheckout('checkout_to',
                    (isset($_SESSION['hotelcard']['checkout_to'])
                        ? $_SESSION['hotelcard']['checkout_to'] : '')),
            'TXT_HOTELCARD_RESERVATION_DATA' => '',
            'TXT_HOTELCARD_RESERVATION_NAME' => Html::getInputText('reservation_name',
                (isset($_SESSION['hotelcard']['reservation_name'])
                    ? $_SESSION['hotelcard']['reservation_name'] : '')),
            'TXT_HOTELCARD_RESERVATION_GENDER' => Html::getRadioGroup(
                'reservation_gender',
                array(
                    'm' => $_ARRAYLANG['TXT_HOTELCARD_GENDER_MALE'],
                    'f' => $_ARRAYLANG['TXT_HOTELCARD_GENDER_FEMALE'],
                ),
                (isset($_SESSION['hotelcard']['reservation_gender'])
                    ? $_SESSION['hotelcard']['reservation_gender'] : '')
                ),
            'TXT_HOTELCARD_RESERVATION_PHONE' => Html::getInputText('reservation_phone',
                (isset($_SESSION['hotelcard']['reservation_phone'])
                    ? $_SESSION['hotelcard']['reservation_phone'] : '')),
            'TXT_HOTELCARD_RESERVATION_FAX' => Html::getInputText('reservation_fax',
                (isset($_SESSION['hotelcard']['reservation_fax'])
                    ? $_SESSION['hotelcard']['reservation_fax'] : '')),
            'TXT_HOTELCARD_RESERVATION_EMAIL' => Html::getInputText('reservation_email',
                (isset($_SESSION['hotelcard']['reservation_email'])
                    ? $_SESSION['hotelcard']['reservation_email'] : '')),
            'TXT_HOTELCARD_EMAIL_RETYPE' =>
                Html::getInputText('reservation_email_retype', ''),
            'TXT_HOTELCARD_FOUND_HOW' => Html::getTextarea('found_how',
                (isset($_SESSION['hotelcard']['found_how'])
                    ? $_SESSION['hotelcard']['found_how'] : '')),
        );
        foreach ($arrData as $placeholder => $element) {
//echo("Language variable $placeholder => ".$_ARRAYLANG[$placeholder]."<br />");
            self::$objTemplate->setVariable(array(
                'HOTELCARD_DATA_LABEL' => $_ARRAYLANG[$placeholder],
                'HOTELCARD_DATA_INPUT' => $element,
            ));
            self::$objTemplate->parse('hotelcard_data');
        }
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

        $mandatoryFields = array(
            // Note: These are checkbox groups and are thus posted as
            // arrays, like 'room_type[0]'
            'room_type',
            'room_available',
            'room_price',
// Not mandatory:
//            'room_facilities',
        );
        // Verify the data already present
        if ($_SESSION['hotelcard']['step'] == 3) {
            $flagComplete = true;
            foreach ($mandatoryFields as $name) {
//echo("Step 3: checking mandatory field $name: ".(empty($_SESSION['hotelcard'][$name][1]) ?  '-- empty --' : $_SESSION['hotelcard'][$name][1])."<br />");
//                $name_stripped = preg_replace('/\[.*$/', '', $name);
                // Verify the first index, only one is mandatory
                if (empty($_SESSION['hotelcard'][$name][1])) {
//echo("Step 3: missing mandatory field value for $name<br />");
                    $flagComplete = false;
                    self::addMessage($_ARRAYLANG['TXT_HOTELCARD_MISSING_'.strtoupper($name)]);
                }
            }
            if ($flagComplete) {
//echo("Step 3 complete<br />");
                return true;
            }
        }
//echo("Showing step 3<br />");

        // Show room type form content four times
        for ($i = 1; $i <= 4; ++$i) {
            self::$objTemplate->setVariable(array(
                'HOTELCARD_DATA_LABEL' =>
                    sprintf($_ARRAYLANG['TXT_HOTELCARD_ROOM_TYPE_NUMBER'], $i),
                'HOTELCARD_DATA_INPUT' => '',
            ));
            self::$objTemplate->parse('hotelcard_data');
            $arrData = array(
                'TXT_HOTELCARD_ROOM_TYPE' =>
                    Html::getInputText('room_type['.$i.']',
                    (isset($_SESSION['hotelcard']['room_type'][$i])
                        ? $_SESSION['hotelcard']['room_type'][$i] : '')),
                'TXT_HOTELCARD_ROOM_AVAILABLE' =>
                    Html::getInputText('room_available['.$i.']',
                    (isset($_SESSION['hotelcard']['room_available'][$i])
                        ? $_SESSION['hotelcard']['room_available'][$i] : '')),
                'TXT_HOTELCARD_ROOM_PRICE' =>
                    Html::getInputText('room_price['.$i.']',
                    (isset($_SESSION['hotelcard']['room_price'][$i])
                        ? $_SESSION['hotelcard']['room_price'][$i] : '')),
                'TXT_HOTELCARD_ROOM_FACILITY_ID' =>
                    Html::getCheckboxGroup('room_facility_id['.$i.']',
                    HotelRoom::getFacilityNameArray(),
                    HotelRoom::getFacilityNameArray(),
                    (isset($_SESSION['hotelcard']['room_facility_id'][$i])
                        ? array_keys($_SESSION['hotelcard']['room_facility_id'][$i]) : '')),
            );
            foreach ($arrData as $placeholder => $element) {
//echo("Language variable $placeholder => ".$_ARRAYLANG[$placeholder]."<br />");
                self::$objTemplate->setVariable(array(
                    'HOTELCARD_DATA_LABEL' => $_ARRAYLANG[$placeholder],
                    'HOTELCARD_DATA_INPUT' => $element,
                ));
                self::$objTemplate->parse('hotelcard_data');
            }
        }
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
        global $_ARRAYLANG;

        $mandatoryFields = array(
            'confirm_terms',
        );
        // Verify the data already present
        if ($_SESSION['hotelcard']['step'] == 4) {
            $flagComplete = true;
            foreach ($mandatoryFields as $name) {
                if (empty($_SESSION['hotelcard'][$name])) {
//echo("Step 4: missing mandatory field value for $name<br />");
                    $flagComplete = false;
                    self::addMessage($_ARRAYLANG['TXT_HOTELCARD_MISSING_'.strtoupper($name)]);
                }
            }
            if ($flagComplete) {
//echo("Step 4 complete<br />");
                return true;
            }
        }
//echo("Showing step 4<br />");

//echo("Creditcards: ".var_export($_SESSION['hotelcard']['creditcard_id'], true)."<hr />");
//echo("Room facilities: ".var_export($_SESSION['hotelcard']['room_facility_id'], true)."<hr />");
        foreach ($_SESSION['hotelcard'] as $name => $value) {
            // Skip fields that are irrelevant or handled separately.
            // room_available, room_price, room_facility_id are all arrays
            // and handled together with room_type in the loop below.
            if (preg_match(
                '/(?:confirm_terms|room_available|room_price|room_facility_id)$/',
                $name)) continue;

            // Fix values that are IDs, special, or arrays of anything
            if (preg_match('/country_id$/', $name))
                $value = Country::getNameById($value);
            elseif (preg_match('/region_id$/', $name))
                $value = Region::getNameById($value);
            elseif (preg_match('/accomodation_type_id$/', $name))
                $value = HotelAccomodationType::getNameById($value);
            elseif (preg_match('/creditcard_id$/', $name))
// array ( 3 => 'Argencard', 6 => 'Bankcard', 9 => 'Carte Blanche', 13 => 'Discover', 18 => 'JCB', 22 => 'PIN', 28 => 'Switch', )
                $value = join(', ', $value);
            elseif (preg_match('/lang_id$/', $name))
                $value = FWLanguage::getLanguageParameter($value, 'name');
            elseif (preg_match('/gender$/', $name))
                $value = ($value == 'm'
                  ? $_ARRAYLANG['TXT_HOTELCARD_GENDER_MALE']
                  : $_ARRAYLANG['TXT_HOTELCARD_GENDER_FEMALE']);
            elseif (preg_match('/rating$/', $name))
                $value = HotelRating::getString($value);
            elseif (preg_match('/hotel_facility_id$/', $name))
                $value = join(', ', $value);
            elseif (preg_match('/room_type$/', $name)) {
                // Collect all hotel room data
                $room_data = '';
                foreach ($value as $index => $room_type) {
//echo("index $index, room_type $room_type<br />");
                    if (empty($room_type)) continue;
                    $room_data .=
                        sprintf(
                            $_ARRAYLANG['TXT_HOTELCARD_ROOM_TYPE_NUMBER'],
                            $index).'<br />'.
                        $_ARRAYLANG['TXT_HOTELCARD_ROOM_TYPE'].' '.
                        $room_type.'<br />'.
                        $_ARRAYLANG['TXT_HOTELCARD_ROOM_AVAILABLE'].' '.
                        $_SESSION['hotelcard']['room_available'][$index].'<br />'.
                        $_ARRAYLANG['TXT_HOTELCARD_ROOM_PRICE'].' '.
                        $_SESSION['hotelcard']['room_price'][$index].'<br />'.
                        $_ARRAYLANG['TXT_HOTELCARD_ROOM_FACILITY_ID'].' '.
                        (isset($_SESSION['hotelcard']['room_facility_id'][$index])
                          ? join(', ', $_SESSION['hotelcard']['room_facility_id'][$index])
                          : '').'<br /><br />';
                }
                $name = 'room_types';
                $value = $room_data;
            }
/*
WARNING: Missing language entry for TXT_HOTELCARD_CONTACT_EMAIL_RETYPE
WARNING: Missing language entry for TXT_HOTELCARD_CONTINUE
WARNING: Missing language entry for TXT_HOTELCARD_DESCRIPTION_TEXT_ID
*/
            $name = 'TXT_HOTELCARD_'.strtoupper($name);
            if (empty($_ARRAYLANG[$name])) {
echo("Note: No language entry for $name, skipped<br />");
                continue;
            }
//echo("Language variable $name => ".$_ARRAYLANG[$name]."<br />");
            self::$objTemplate->setVariable(array(
                'HOTELCARD_DATA_LABEL' => $_ARRAYLANG[$name],
                'HOTELCARD_DATA_INPUT' => $value,
            ));
            self::$objTemplate->parse('hotelcard_data');
        }
        $arrData = array(
            'TXT_HOTELCARD_CONFIRM_TERMS' => Html::getCheckbox('confirm_terms'),
            'TXT_HOTELCARD_REGISTER_DATE' => date('d.m.Y'),
        );
        foreach ($arrData as $placeholder => $element) {
//echo("Language variable $placeholder => ".$_ARRAYLANG[$placeholder]."<br />");
            self::$objTemplate->setVariable(array(
                'HOTELCARD_DATA_LABEL' => $_ARRAYLANG[$placeholder],
                'HOTELCARD_DATA_INPUT' => $element,
            ));
            self::$objTemplate->parse('hotelcard_data');
        }
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

        $mandatoryFields = array(
            'submit',
        );

        $result = true;
        if (empty($_SESSION['hotelcard']['registration_id'])) {
            // Store the hotel if it hasn't been yet
            $result = Hotel::insertFromSession();
        }

        // See whether the finish button has been clicked
        if ($_SESSION['hotelcard']['step'] == 5) {
            $flagComplete = true;
            foreach ($mandatoryFields as $name) {
                if (empty($_SESSION['hotelcard'][$name])) {
//echo("Step 5: missing mandatory field value for $name<br />");
                    $flagComplete = false;
                    self::addMessage($_ARRAYLANG['TXT_HOTELCARD_MISSING_'.strtoupper($name)]);
                }
            }
            if ($flagComplete) {
//echo("Step 5 complete<br />");
                return true;
            }
        }
//echo("Showing step 5<br />");
        if (!$result)
            self::addMessage($_ARRAYLANG['TXT_HOTELCARD_ERROR_STORING_HOTEL']);
        return false; // Still running
    }


    /**
     * Edit the hotel data
     *
     * Determines the hotel ID from the User field selected in the settings
     * @return  boolean             True on success, false otherwise
     */
    static function editHotel()
    {
        $objFWUser = FWUser::getFWUserObject();
        /**
         * @var User
         */
        $objUser = $objFWUser->objUser;
        if (!$objUser) {
            header('Location: index.php?section=hotelcard');
            exit;
        }
        $user_id = $objUser->getId();
        SettingDb::init(MODULE_ID);
        $attribute_id = SettingDb::getValue('user_attribute_hotel_id');
        $hotel_id = User_Profile_Attribute::getAttributeValue($attribute_id, $user_id);
//die("attribute ID $attribute_id, got hotel ID $hotel_id<br />");

$hotel_id = 6;

        $view = (isset($_REQUEST['view']) ? $_REQUEST['view'] : '');
        switch ($view) {
          case '':
          default:
            // Overview
            self::editHotelOverview($hotel_id);
        }
        return true;
    }


    /**
     * Shows the overview for the hotel ID specified
     *
     * @todo    This might have to be adapted if any single user may be managing
     * more than one hotel at a time.  In that case, a list of hotels can be
     * shown instead.
     * @param   integer   $hotel_id     The selected hotel ID
     * @return  boolean                 True on success, false otherwise
     */
    static function editHotelOverview($hotel_id)
    {
        global $_ARRAYLANG, $_CORELANG;

        // Requires DatePickerControl.js
        JS::activate('datepicker');

//echo("Hotelcard::editHotelOverview($hotel_id): Entered<br />");
DBG::enable_adodb_debug();
DBG::enable_error_reporting();

        $objhotel = Hotel::getById($hotel_id);
// TODO:  Add error message, maybe a redirect
        if (empty($objhotel)) {
            self::addMessage($_ARRAYLANG['TXT_HOTELCARD_ERROR_HOTEL_ID_NOT_FOUND'].' '.$hotel_id);
            return false;
        }

        // Range for the dates to be shown
        $_SESSION['hotelcard']['date_from'] = (isset($_REQUEST['date_from'])
            ? $_REQUEST['date_from']
            : (isset($_SESSION['hotelcard']['date_from'])
                ? $_SESSION['hotelcard']['date_from']
                : strtotime('tomorrow')));
        $_SESSION['hotelcard']['date_to'] = (isset($_REQUEST['date_to'])
            ? $_REQUEST['date_to']
            : (isset($_SESSION['hotelcard']['date_to'])
                ? $_SESSION['hotelcard']['date_to']
                : strtotime('+2 months')));
        $time_from = strtotime($_SESSION['hotelcard']['date_from']);
        $time_to = strtotime($_SESSION['hotelcard']['date_to']);

        // Spray language variables all over
        self::$objTemplate->setGlobalVariable($_ARRAYLANG);
        self::$objTemplate->setVariable(array(
//            'HOTELCARD_FORM_SUBMIT_NAME'  => 'submit',
            'HOTELCARD_FORM_SUBMIT_VALUE' => $_ARRAYLANG['TXT_HOTELCARD_FORM_SUBMIT_STORE'],
            'HOTELCARD_HOTEL_ID' => $hotel_id,
            'HOTELCARD_DATE_FROM' => Html::getSelectDate(
                'date_from', $_SESSION['hotelcard']['date_from']),
            'HOTELCARD_DATE_TO' => Html::getSelectDate(
                'date_to', $_SESSION['hotelcard']['date_to']),
            // Datepicker language and settings
            'HOTELCARD_DPC_TODAY_TEXT' => $_CORELANG['TXT_CORE_TODAY'],
            'HOTELCARD_DPC_BUTTON_TITLE' => $_ARRAYLANG['TXT_HOTELCARD_OPEN_CALENDAR'],
            'HOTELCARD_DPC_MONTH_NAMES' =>
                "'".join("','", explode(',', $_CORELANG['TXT_MONTH_ARRAY']))."'",
            // Reformat from "Su,Mo,Tu,We,Th,Fr,Sa"
            // to "'Su','Mo','Tu','We','Th','Fr','Sa'"
            'HOTELCARD_DPC_DAY_NAMES' =>
                "'".join("','", explode(',', $_CORELANG['TXT_CORE_DAY_ABBREV2_ARRAY']))."'",


        ));
/*        array(
            'TXT_HOTELCARD_ROOM_TYPE_ID' => $_ARRAYLANG[],
            'TXT_HOTELCARD_DATE'
            'TXT_HOTELCARD_TOTAL'
            'TXT_HOTELCARD_BOOKED'
            'TXT_HOTELCARD_CANCELLED'
            'TXT_HOTELCARD_PRICE'
            'TXT_HOTELCARD_FACILITY_ID'
            'TXT_HOTELCARD_FACILITY_NAME'
            'TXT_HOTELCARD_FACILITY_SELECTED'
            'TXT_HOTELCARD_ROOM_TYPE'
            'TXT_HOTELCARD_NUMBER_DEFAULT'
            'TXT_HOTELCARD_PRICE_DEFAULT'
            'TXT_HOTELCARD_DATE_FROM'
            'TXT_HOTELCARD_DATE_TO'
        )); */

        // Show the room types and availabilities
        $arrRoomTypes = HotelRoom::getTypeArray(
            $hotel_id, 0,
            $_SESSION['hotelcard']['date_from'],
            $_SESSION['hotelcard']['date_to']
        );
        // Complete list of all facilites for reference
        $arrAllFacilities = HotelRoom::getFacilityNameArray();
        foreach ($arrRoomTypes as $type_id => $arrRoomType) {
            $name              = $arrRoomType['name'];
            $number_default    = $arrRoomType['number_default'];
            $price_default     = $arrRoomType['price_default'];
            // We just need the keys to see which are provided
            $arrFacility_id    = array_keys($arrRoomType['facilities']);
            $arrAvailabilities = $arrRoomType['availabilities'];
            for ($time = $time_from; $time <= $time_to; $time += 86400) {
                $date = date('Y-m-d', $time);
            //$arrAvailabilities as $date => $arrAvailability) {
                $number_total     = $arrAvailabilities[$date]['number_total'];
                $number_booked    = $arrAvailabilities[$date]['number_booked'];
                $number_cancelled = $arrAvailabilities[$date]['number_cancelled'];
                $price            = $arrAvailabilities[$date]['price'];
//echo("Room type $type_id: $name: number_total $number_total, number_booked $number_booked, number_cancelled$number_cancelled, price $price<br />");
                self::$objTemplate->setVariable(array(
                    'HOTELCARD_ROOM_TYPE_ID' => $type_id,
                    'HOTELCARD_DATE'         => date(ASCMS_DATE_SHORT_FORMAT, $time),
                    'HOTELCARD_TOTAL'        => $number_total,
                    'HOTELCARD_BOOKED'       => $number_booked,
                    'HOTELCARD_CANCELLED'    => $number_cancelled,
                    'HOTELCARD_PRICE'        => $price,
                ));
                self::$objTemplate->parse('hotelcard_day');
            }
            foreach ($arrAllFacilities as $facility_id => $facility_name) {
//echo("Room type $type_id: facility_id $facility_id, facility_name $facility_name, ".(in_array($facility_id, $arrFacility_id) ? 'selected' : '')."<br />");
                self::$objTemplate->setVariable(array(
                    'HOTELCARD_FACILITY_ID'       => $facility_id,
                    'HOTELCARD_FACILITY_NAME'     => $facility_name,
                    'HOTELCARD_FACILITY_SELECTED' =>
                        (in_array($facility_id, $arrFacility_id)
                          ? ' selected="selected"' : ''),
                ));
                self::$objTemplate->parse('hotelcard_facility');
            }
            self::$objTemplate->setVariable(array(
                'HOTELCARD_ROOM_TYPE_ID'   => $type_id,
                'HOTELCARD_ROOM_TYPE'      => $name,
                'HOTELCARD_NUMBER_DEFAULT' => $number_default,
                'HOTELCARD_PRICE_DEFAULT'  => $price_default,
            ));
            self::$objTemplate->parse('hotelcard_roomtype');
        }
        return true;
    }


    /**
     * Set up the overview page
     *
     * @todo    Contents have yet to be defined
     * @return  boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function overview()
    {
        return true;
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


    static function getJavascript()
    {
        return '
function openWindow(theURL, winName, features)
{
  window.open(theURL, winName, features);
}

fieldID = false;
function openBrowser(url, id, attrs)
{
  fieldID = id;
  try {
    if (!browserPopup.closed) {
      return browserPopup.focus();
    }
  } catch(e) {}
  if (!window.focus) return true;
  browserPopup = window.open(url, "", attrs);
  browserPopup.focus();
  return false;
}

function SetUrl(url, width, height, alt)
{
  var fact = 80 / height;
  if (width > height) fact = 80 / width;
  var element_img = document.getElementById(fieldID).
  element_img.setAttribute("src", url);
  element_img.style.width = parseInt(width*fact)+"px";
  element_img.style.height = parseInt(height*fact)+"px";
  document.getElementById(fieldID+"_src").value = url;
  document.getElementById(fieldID+"_width").value = width;
  document.getElementById(fieldID+"_height").value = height;
}

function clearImage(id, index)
{
  document.getElementById(id).src = "'.Image::NO_IMAGE_SRC.'";
  document.getElementById(id+"_src").value = "";
  document.getElementById(id+"_width").value = "";
  document.getElementById(id+"_height").value = "";
}
';
    }

}

?>
