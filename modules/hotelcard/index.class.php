<?php

define('_HOTELCARD_DEBUG', 0);

if (isset($_REQUEST['test']))
$_SESSION['hotelcard'] = array (
//  'step_posted' => 3,
  'step_complete' => 3,
//  'step_current' => 4,
//  'step' => '4',
  'hotel_name' => 'hotelname',
  'group' => 'hotelkette',
  'accomodation_type_id' => 2,
  'hotel_address' => 'hotelstrasse',
  'hotel_zip' => 'hotelplz',
  'hotel_location' => 'hotelort',
  'hotel_region' => 'BE',
  'contact_name' => 'kontaktperson',
  'contact_gender' => 'M',
  'contact_position' => 'kontaktposition',
  'contact_department' => 'kontaktabteilung',
  'contact_phone' => '+123456789',
  'contact_fax' => '+123456790',
  'contact_email' => 'kontakt@email.com',
  'contact_email_retype' => 'kontakt@email.com',
  'bsubmit' => 'Fertig stellen',
  'numof_rooms' => 101,
  'description_text' => 'hotelbeschreibung',
  'hotel_facility_id' =>
  array (
    3127 => 'Air Conditioning',
    3157 => 'Golfplatz (im Umkreis von 3 km)',
    3191 => 'Internet Zugang',
  ),
  'hotel_uri' => 'http://hotelwebsite.com',
  'rating' => 4,
  'checkin_from' => '05:00',
  'checkin_to' => '06:00',
  'checkout_from' => '07:00',
  'checkout_to' => '08:00',
  'found_how' => 'hotelfoundhow',
  'room_type_1' => 'roomtype_1',
  'room_available_1' => 12,
  'room_price_1' => '13.00',
  'room_facility_id_1' =>
  array ( 3217 => 'Badewanne', ),
  'room_type_2' => 'roomtype_2',
  'room_available_2' => 22,
  'room_price_2' => '23.00',
  'room_facility_id_2' =>
  array ( 3218 => 'Dusche', ),
  'room_type_3' => '',
  'room_available_3' => '',
  'room_price_3' => '',
  'room_type_4' => '',
  'room_available_4' => '',
  'room_price_4' => '',
);

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
    const IMAGETYPE_TITLE       = 'hotelcard_hotel_title';
    const IMAGETYPE_ROOM        = 'hotelcard_hotel_room';
    const IMAGETYPE_VICINITY    = 'hotelcard_hotel_vicinity';
    const IMAGETYPE_LOBBY       = 'hotelcard_hotel_lobby';
    /**
     * Style for marking incomplete mandatory form input.
     */
    const INCOMPLETE_CLASS = ' class="error"';

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
     * The page content
     *
     * Handed over when {@see getPage()} is called
     * @var   string
     * @static
     */
    private static $page_content = '';
    /**
     * Status / error message
     * @var string
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

if (_HOTELCARD_DEBUG & 1) DBG::enable_error_reporting();
if (_HOTELCARD_DEBUG & 2) DBG::enable_adodb_debug();

        self::$page_content = $page_content;
        // PEAR Sigma template
        self::$objTemplate = new HTML_Template_Sigma('.');
        CSRF::add_placeholder(self::$objTemplate);
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

            // Ajax
            case 'get_locations':
                die(
                    Location::getMenuoptions(
                        isset($_GET['state']) ? $_GET['state'] : '',
                        isset($_SESSION['hotelcard']['hotel-location'])
                          ? $_SESSION['hotelcard']['hotel-location'] : 0)
                );

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
        // Look for uploaded image files and try storing them.
        // The filename and resulting image ID are stored in the session array.
        self::processPostFiles();
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

        // After the last step has been completed, go back to the hotelcard
        // start page.  Also unset the session data.
        if ($_SESSION['hotelcard']['step_current'] > self::HOTEL_REGISTRATION_STEPS) {
            unset($_SESSION['hotelcard']);
            CSRF::header('Location: index.php?section=hotelcard');
            exit;
        }

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
            if ($i <= 1 + $_SESSION['hotelcard']['step_complete']) {
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
            'HOTELCARD_FORM_SUBMIT_VALUE' =>
                ($_SESSION['hotelcard']['step_current'] < self::HOTEL_REGISTRATION_STEPS-1
                  ? $_ARRAYLANG['TXT_HOTELCARD_FORM_SUBMIT_CONTINUE']
                  : $_ARRAYLANG['TXT_HOTELCARD_FORM_SUBMIT_FINISH']
                ),
        ));
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
        return Image::processPostFiles(ASCMS_HOTELCARD_IMAGES_FOLDER);
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
                (isset($_SESSION['hotelcard']['registration_date'])
                    ? $arrDow[date('w', $_SESSION['hotelcard']['registration_date'])].
                      ', '.
                      date(ASCMS_DATE_SHORT_FORMAT,
                          $_SESSION['hotelcard']['registration_date'])
                    : ''),
                (isset($_SESSION['hotelcard']['registration_id'])
                    ? $_SESSION['hotelcard']['registration_id'] : '')),
        ));
    }


    static function parseDataTable($arrFields)
    {
        global $_ARRAYLANG;

        foreach ($arrFields as $row_data) {
//echo("Hotelcard::parseDataTable(): row_data $index => ".nl2br(htmlentities(var_export($row_data, true), ENT_QUOTES, CONTREXX_CHARSET))."<hr />");

            // Some "rows" actually contain arrays of rows (i.e.,
            // hotel facilities).  Recurse into them
            if (empty($row_data['label']) && empty($row_data['input'])) {
//echo("Hotelcard::parseDataTable(): &gt;&gt;&gt;<br />");
                self::parseDataTable($row_data);
//echo("Hotelcard::parseDataTable(): &lt;&lt;&lt;<br />");
                continue;
            }

//echo("Language variable $label => ".$_ARRAYLANG[$label]."<br />");

            $mandatory = $row_data['mandatory'];
            $label = $row_data['label'];
            $input = $row_data['input'];
            $class = (isset($row_data['class']) ? $row_data['class'] : '');

//echo("Hotelcard::parseDataTable(): class: $class<br />");
            if (empty($input)) {
                // Parse header
                self::$objTemplate->setVariable(
                    'HOTELCARD_DATA_HEADER',
                        (preg_match('/^TXT_/', $label)
                            ? $_ARRAYLANG[$label] : $label)
                );
            } else {
                self::$objTemplate->setVariable(array(
                    'HOTELCARD_DATA_LABEL' =>
                        (preg_match('/^TXT_/', $label)
                            ? $_ARRAYLANG[$label] : $label).
                        ($mandatory
                            ? '&nbsp;*' : ''),
                    'HOTELCARD_DATA_LABEL_CLASS' => $class,
                    'HOTELCARD_DATA_INPUT' => $input,
                ));
            }
            self::$objTemplate->parse('hotelcard_data');
        }
    }


    /**
     * Verifies that the fields marked as mandatory contain some proper values
     *
     * Sets the values in the array given by reference to some attribute
     * string which should be added as the attribute parameter when
     * creating the HTML elements later
     * @param   array   $arrFields    The array with mandatory field
     *                                      names as keys
     * @return  void
     */
    static function verifyMandatoryFields(&$arrFields)
    {
        global $_ARRAYLANG;

        // Do not validate if the current step is shown for the first time,
        // but return false immediately
        if ($_SESSION['hotelcard']['step_posted'] != $_SESSION['hotelcard']['step_current'])
            return false;

        $flagComplete = true;
        $objTestHotel = new Hotel();
        foreach ($arrFields as $name => $row_data) {

            $value = (isset($_SESSION['hotelcard'][$name])
                ? $_SESSION['hotelcard'][$name] : '');
//echo("Hotelcard::verifyMandatoryFields(): $name => ".var_export($value, true)."<br />");

            // Test if the value is valid whether it's mandatory or not
            // First, try to set the parameter in the hotel object
            if ($objTestHotel->setFieldvalue($name, $value)) {
//echo("Hotelcard::verifyMandatoryFields(): Set $name to $value<br />");
                if ($objTestHotel->getFieldvalue($name) !== null) {
                    // Value has been accepted by the Hotel class,
                    // update the session
                    $_SESSION['hotelcard'][$name] =
                        $objTestHotel->getFieldvalue($name);
//echo("Hotelcard::verifyMandatoryFields(): Accepted $name to be ".$_SESSION['hotelcard'][$name]."<br />");
                    continue;
                }
                // else... The value was accepted but did not change the
                // default null value, so the Hotel simply wasn't interested.
                // The non-hotel cases are handled below.
            } else {
                // The value has been rejected
//echo("Hotelcard::verifyMandatoryFields(): Rejected mandatory field $name value '$value'<br />");
                $flagComplete = false;
                $arrFields[$name]['class'] = self::INCOMPLETE_CLASS;
                continue;
            }

            // Check the fields that did not fit into the hotel object
            if (empty($value)) {
                // Don't bother if it's empty and not mandatory
                if (empty($row_data['mandatory'])) {
//echo("Hotelcard::verifyMandatoryFields(): Ignored empty non-mandatory field $name value '$value'<br />");
                    continue;
                }
                // Mandatory fields must not be empty
                $flagComplete = false;
                $arrFields[$name]['class'] = self::INCOMPLETE_CLASS;
//echo("Hotelcard::verifyMandatoryFields(): Rejected empty mandatory field $name value '$value'<br />");
                continue;
            }

//echo("Hotelcard::verifyMandatoryFields(): Checking field $name: ".var_export($value, true)."<br />");

            $result = false;
            // All the remaining special cases
            switch ($name) {
              case 'hotel_image_id':
              case 'bsubmit':
                // Workaround for the image and submit buttons.
                // Always let them pass.
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
                break;
              case 'room_price_1':
              case 'room_price_2':
              case 'room_price_3':
              case 'room_price_4':
                $result = HotelRoom::validateRoomtypePrice($value);
                break;
              case 'room_facility_id_1':
              case 'room_facility_id_2':
              case 'room_facility_id_3':
              case 'room_facility_id_4':
                $result = HotelFacility::validateFacilityIdArray($value);
                break;
              case 'confirm_terms':
                $result = (!empty($value));
                break;
              default:
//echo("Hotelcard::verifyMandatoryFields(): WARNING: Missed name $name: ".var_export($value, true)."<br />");
            }
            // The value may have been fixed by the verification method
            $_SESSION['hotelcard'][$name] = $value;
            if ($result) continue;
//echo("Hotelcard::verifyMandatoryFields(): ***** name $name value ".var_export($value, true)." is invalid<br />");
            $flagComplete = false;
            $arrFields[$name]['class'] = self::INCOMPLETE_CLASS;
        }
        if (!$flagComplete)
            self::addMessage($_ARRAYLANG['TXT_HOTELCARD_MISSING_MANDATORY_DATA']);
        return $flagComplete;
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
                        ? $_SESSION['hotelcard']['hotel_name'] : ''),
                    'style="text-align: left;"'),
            ),
            'group' => array( // Mind that this goes into a lookup table!
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_GROUP',
                'input' => Html::getInputText('group',
                    (isset($_SESSION['hotelcard']['group'])
                        ? $_SESSION['hotelcard']['group'] : ''),
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
                        ? $_SESSION['hotelcard']['hotel_address'] : ''),
                    'style="text-align: left;"'),
            ),
            'hotel_zip' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_HOTEL_ZIP',
                'input' => Html::getInputText('hotel_zip',
                    (isset($_SESSION['hotelcard']['hotel_zip'])
                        ? $_SESSION['hotelcard']['hotel_zip'] : ''),
                        'style="text-align: left;"'),
            ),
            'hotel_region' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_HOTEL_REGION',
                'input' => Html::getSelect(
                'hotel_region', State::getArray(),
                    (isset($_SESSION['hotelcard']['hotel_location'])
                      ? State::getByLocation($_SESSION['hotelcard']['hotel_location'])
                      : (isset($_SESSION['hotelcard']['hotel_region'])
                          ? $_SESSION['hotelcard']['hotel_region'] : '')),
                    'new Ajax.Updater(\'hotel_location\', \'index.php?section=hotelcard&act=get_locations&state=\'+document.getElementById(\'hotel_region\').value, { method: \'get\' });'),
                    //document.forms.form_hotelcard.submit();'),
            ),
            'hotel_location' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_HOTEL_LOCATION',
                'input' => Html::getSelect('hotel_location',
                    (isset($_SESSION['hotelcard']['hotel_region'])
                      ? Location::getArrayByState($_SESSION['hotelcard']['hotel_region'])
                      : array($_ARRAYLANG['TXT_HOTELCARD_PLEASE_CHOOSE_REGION'])),
                    (isset($_SESSION['hotelcard']['hotel_location'])
                        ? $_SESSION['hotelcard']['hotel_location'] : ''),
                    '', 'style="text-align: left;"'),
            ),
//            'hotel_country_id' => array(
//                'mandatory' => false,
//                'label' => 'TXT_HOTELCARD_HOTEL_COUNTRY_ID',
//                'input' => Html::getSelect(
//                    'hotel_country_id', Country::getNameArray(),
//                    (isset($_SESSION['hotelcard']['hotel_country_id'])
//                        ? $_SESSION['hotelcard']['hotel_country_id'] : '')),
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
                        ? $_SESSION['hotelcard']['contact_name'] : ''),
                    'style="text-align: left;"'),
            ),
            'contact_gender' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_CONTACT_GENDER',
                'input' => Html::getRadioGroup(
                    'contact_gender',
                    array(
                        'M' => $_ARRAYLANG['TXT_HOTELCARD_GENDER_MALE'],
                        'F' => $_ARRAYLANG['TXT_HOTELCARD_GENDER_FEMALE'],
                    ),
                    (isset($_SESSION['hotelcard']['contact_gender'])
                        ? $_SESSION['hotelcard']['contact_gender'] : '')),
            ),
            'contact_position' => array(
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_CONTACT_POSITION',
                'input' => Html::getInputText('contact_position',
                    (isset($_SESSION['hotelcard']['contact_position'])
                        ? $_SESSION['hotelcard']['contact_position'] : ''),
                    'style="text-align: left;"'),
            ),
            'contact_department' => array(
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_CONTACT_DEPARTMENT',
                'input' => Html::getInputText('contact_department',
                    (isset($_SESSION['hotelcard']['contact_department'])
                        ? $_SESSION['hotelcard']['contact_department'] : ''),
                    'style="text-align: left;"'),
            ),
            'contact_phone' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_CONTACT_PHONE',
                'input' => Html::getInputText('contact_phone',
                    (isset($_SESSION['hotelcard']['contact_phone'])
                        ? $_SESSION['hotelcard']['contact_phone'] : ''),
                        'style="text-align: left;"'),
            ),
            'contact_fax' => array(
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_CONTACT_FAX',
                'input' => Html::getInputText('contact_fax',
                    (isset($_SESSION['hotelcard']['contact_fax'])
                        ? $_SESSION['hotelcard']['contact_fax'] : ''),
                        'style="text-align: left;"'),
            ),
            'contact_email' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_CONTACT_EMAIL',
                'input' => Html::getInputText('contact_email',
                    (isset($_SESSION['hotelcard']['contact_email'])
                        ? $_SESSION['hotelcard']['contact_email'] : ''),
                    'style="text-align: left;"'),
            ),
            'contact_email_retype' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_CONTACT_EMAIL_RETYPE',
                'input' => Html::getInputText('contact_email_retype', '',
                    'style="text-align: left;"'),
            ),
        );
        // Verify the data already present if it's the current step
        $flagComplete = self::verifyMandatoryFields($arrFields);
        // Only verify the e-mail addresses after the fields have been
        // filled out
        if ($flagComplete) {
            if ($_SESSION['hotelcard']['contact_email'] !=
                  $_SESSION['hotelcard']['contact_email_retype']) {
                $flagComplete = false;
                $arrFields['contact_email']['error'] = self::INCOMPLETE_CLASS;
                $arrFields['contact_email_retype']['error'] = self::INCOMPLETE_CLASS;
                self::addMessage($_ARRAYLANG['TXT_HOTELCARD_EMAILS_DO_NOT_MATCH']);
            } elseif (!FWValidator::isEmail($_SESSION['hotelcard']['contact_email'])) {
                $flagComplete = false;
                $arrFields['contact_email']['error'] = self::INCOMPLETE_CLASS;
                self::addMessage($_ARRAYLANG['TXT_HOTELCARD_EMAIL_IS_INVALID']);
            }
        }
        if ($flagComplete && isset($_POST['bsubmit'])) {
//echo("Step 1 complete<br />");
            return true;
        }
//echo("Showing step 1<br />");
        self::parseDataTable($arrFields);
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

//echo("Rating: ".$_SESSION['hotelcard']['rating']."<hr />");
        $arrFields = array(
            'additional_data' => array(
                'mandatory' => false,
                'label' => $_ARRAYLANG['TXT_HOTELCARD_ADDITIONAL_DATA'],
                'input' => '',
            ),
            'numof_rooms' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_NUMOF_ROOMS',
                'input' => Html::getInputText('numof_rooms',
                    (isset($_SESSION['hotelcard']['numof_rooms'])
                        ? $_SESSION['hotelcard']['numof_rooms'] : ''),
                    'style="text-align: left;"'),
            ),
            'description_text' => array(
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_DESCRIPTION_TEXT',
                'input' => Html::getTextarea('description_text',
                    (isset($_SESSION['hotelcard']['description_text'])
                        ? $_SESSION['hotelcard']['description_text'] : ''),
                    'style="text-align: left;"'),
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
                'input' => Html::getCheckboxGroup(
                    'hotel_facility_id',
                    $arrFacilities, $arrFacilities,
                    (   isset($_SESSION['hotelcard']['hotel_facility_id'])
                      ? array_keys($_SESSION['hotelcard']['hotel_facility_id'])
                      : '')),
            );
        }
        $arrFields += array(
            'hotel_uri' => array(
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_HOTEL_URI',
                'input' => Html::getInputText('hotel_uri',
                    (isset($_SESSION['hotelcard']['hotel_uri'])
                        ? $_SESSION['hotelcard']['hotel_uri'] : ''),
                    'style="text-align: left;"'),
            ),
            'hotel_image_id' => array(
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_IMAGE',
                'input' => Html::getImageChooserUpload(
                    Image::getSession('hotel_image'), 'hotel_image',
                    self::IMAGETYPE_TITLE),
            ),
            'rating' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_RATING',
                'input' => HotelRating::getMenu('rating',
                    (isset($_SESSION['hotelcard']['rating'])
                        ? $_SESSION['hotelcard']['rating'] : '')),
            ),
            'checkin_from' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_CHECKIN_FROM',
                'input' => HotelCheckInOut::getMenuCheckin('checkin_from',
                    (isset($_SESSION['hotelcard']['checkin_from'])
                        ? $_SESSION['hotelcard']['checkin_from'] : '')),
            ),
            'checkin_to' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_CHECKIN_TO',
                'input' => HotelCheckInOut::getMenuCheckin('checkin_to',
                    (isset($_SESSION['hotelcard']['checkin_to'])
                        ? $_SESSION['hotelcard']['checkin_to'] : '')),
            ),
            'checkout_from' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_CHECKOUT_FROM',
                'input' => HotelCheckInOut::getMenuCheckout('checkout_from',
                    (isset($_SESSION['hotelcard']['checkout_from'])
                        ? $_SESSION['hotelcard']['checkout_from'] : '')),
            ),
            'checkout_to' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_CHECKOUT_TO',
                'input' => HotelCheckInOut::getMenuCheckout('checkout_to',
                    (isset($_SESSION['hotelcard']['checkout_to'])
                        ? $_SESSION['hotelcard']['checkout_to'] : '')),
            ),
//            'reservation_data' => array(
//                'mandatory' => false,
//                'label' => $_ARRAYLANG['TXT_HOTELCARD_RESERVATION_DATA'],
//                'input' => '',
//            ),
//            'reservation_name' => array(
//                'mandatory' => false,
//                'label' => 'TXT_HOTELCARD_RESERVATION_NAME',
//                'input' => Html::getInputText('reservation_name',
//                (isset($_SESSION['hotelcard']['reservation_name'])
//                    ? $_SESSION['hotelcard']['reservation_name'] : '')),
//            ),
//            'reservation_gender' => array(
//                'mandatory' => false,
//                'label' => 'TXT_HOTELCARD_RESERVATION_GENDER',
//                'input' => Html::getRadioGroup(
//                'reservation_gender',
//                array(
//                    'M' => $_ARRAYLANG['TXT_HOTELCARD_GENDER_MALE'],
//                    'F' => $_ARRAYLANG['TXT_HOTELCARD_GENDER_FEMALE'],
//                ),
//                (isset($_SESSION['hotelcard']['reservation_gender'])
//                    ? $_SESSION['hotelcard']['reservation_gender'] : '')
//                ),
//            ),
//            'reservation_phone' => array(
//                'mandatory' => false,
//                'label' => 'TXT_HOTELCARD_RESERVATION_PHONE',
//                'input' => Html::getInputText('reservation_phone',
//                (isset($_SESSION['hotelcard']['reservation_phone'])
//                    ? $_SESSION['hotelcard']['reservation_phone'] : '')),
//            ),
//            'reservation_fax' => array(
//                'mandatory' => false,
//                'label' => 'TXT_HOTELCARD_RESERVATION_FAX',
//                'input' => Html::getInputText('reservation_fax',
//                (isset($_SESSION['hotelcard']['reservation_fax'])
//                    ? $_SESSION['hotelcard']['reservation_fax'] : '')),
//            ),
//            'reservation_email' => array(
//                'mandatory' => false,
//                'label' => 'TXT_HOTELCARD_RESERVATION_EMAIL',
//                'input' => Html::getInputText('reservation_email',
//                (isset($_SESSION['hotelcard']['reservation_email'])
//                    ? $_SESSION['hotelcard']['reservation_email'] : '')),
//            ),
//            'reservation_email_retype' => array(
//                'mandatory' => false,
//                'label' => 'TXT_HOTELCARD_EMAIL_RETYPE',
//                'input' => Html::getInputText('reservation_email_retype', ''),
//            ),
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
        $flagComplete = self::verifyMandatoryFields($arrFields);
//echo("After:<br />".htmlentities(var_export($arrFields, true), ENT_QUOTES, CONTREXX_CHARSET)."<hr />");
        // Only verify the e-mail addresses after the fields have been
        // filled out
//            if ($flagComplete && isset($_POST['bsubmit'])) {
//                if ($_SESSION['hotelcard']['reservation_email'] !=
//                      $_SESSION['hotelcard']['reservation_email_retype']) {
//                    $flagComplete = false;
//                    $arrFields['reservation_email'] = self::INCOMPLETE_CLASS;
//                    $arrFields['reservation_email_retype'] = self::INCOMPLETE_CLASS;
//                    self::addMessage($_ARRAYLANG['TXT_HOTELCARD_EMAILS_DO_NOT_MATCH']);
//                } elseif (!FWValidator::isEmail($_SESSION['hotelcard']['reservation_email'])) {
//                    $flagComplete = false;
//                    $arrFields['reservation_email'] = self::INCOMPLETE_CLASS;
//                    self::addMessage($_ARRAYLANG['TXT_HOTELCARD_EMAIL_IS_INVALID']);
//                }
//            }
        if (   $_SESSION['hotelcard']['checkin_to']
            && $_SESSION['hotelcard']['checkin_from']
                >= $_SESSION['hotelcard']['checkin_to']) {
            $flagComplete = false;
            $arrFields['checkin_from']['class'] = self::INCOMPLETE_CLASS;
            $arrFields['checkin_to']['class'] = self::INCOMPLETE_CLASS;
            self::addMessage($_ARRAYLANG['TXT_HOTELCARD_CHECKIN_FROM_AFTER_TO']);
        }
        if (   $_SESSION['hotelcard']['checkout_to']
            && $_SESSION['hotelcard']['checkout_from']
                >= $_SESSION['hotelcard']['checkout_to']) {
            $flagComplete = false;
            $arrFields['checkout_from']['class'] = self::INCOMPLETE_CLASS;
            $arrFields['checkout_to']['class'] = self::INCOMPLETE_CLASS;
            self::addMessage($_ARRAYLANG['TXT_HOTELCARD_CHECKOUT_FROM_AFTER_TO']);
        }
        if ($flagComplete && isset($_POST['bsubmit'])) {
//echo("Step 2 complete<br />");
            return true;
        }
//echo("Showing step 2<br />");
        self::parseDataTable($arrFields);
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

        $flagComplete = true;
        // The actual labels and values are set in the loop below
        // Show room type form content four times
        $arrFields = array();
        for ($i = 1; $i <= 4; ++$i) {
//echo("Room type $i: ".(isset($_SESSION['hotelcard']['room_type_'.$i]) ? $_SESSION['hotelcard']['room_type_'.$i] : '')."<br />");

            // Only the first type is mandatory
            $arrFields += array(
                'room_type_data_'.$i => array(
                  'mandatory' => false,
                  'label' => sprintf($_ARRAYLANG['TXT_HOTELCARD_ROOM_TYPE_NUMBER'], $i),
                  'input' => '',
                ),
                'room_type_'.$i => array(
                  'mandatory' => ($i == 1),
                  'label' => 'TXT_HOTELCARD_ROOM_TYPE',
                  'input' => Html::getInputText('room_type_'.$i,
                      (isset($_SESSION['hotelcard']['room_type_'.$i])
                          ? $_SESSION['hotelcard']['room_type_'.$i] : ''),
                      'style="text-align: left;"'),
                ),
                'room_available_'.$i => array(
                    'mandatory' => ($i == 1),
                    'label' => 'TXT_HOTELCARD_ROOM_AVAILABLE',
                    'input' => Html::getInputText('room_available_'.$i,
                        (isset($_SESSION['hotelcard']['room_available_'.$i])
                            ? $_SESSION['hotelcard']['room_available_'.$i] : ''),
                        'style="text-align: left;"'),
                ),
                'room_price_'.$i => array(
                    'mandatory' => ($i == 1),
                    'label' => 'TXT_HOTELCARD_ROOM_PRICE',
                    'input' => Html::getInputText('room_price_'.$i,
                        (isset($_SESSION['hotelcard']['room_price_'.$i])
                            ? $_SESSION['hotelcard']['room_price_'.$i] : ''),
                        'style="text-align: left;"'),
                ),
                // Note: These are checkbox groups and are thus posted as
                // arrays, like 'room_facility_id[1][]'
                'room_facility_id_'.$i => array(
                    'mandatory' => false,
                    'label' => 'TXT_HOTELCARD_ROOM_FACILITY_ID',
                    'input' => Html::getCheckboxGroup('room_facility_id_'.$i,
                        HotelRoom::getFacilityNameArray(true),
                        HotelRoom::getFacilityNameArray(true),
                        (isset($_SESSION['hotelcard']['room_facility_id_'.$i])
                            ? array_keys($_SESSION['hotelcard']['room_facility_id_'.$i]) : '')),
                ),
            );
        }
        $flagComplete = self::verifyMandatoryFields($arrFields);
        if ($flagComplete && isset($_POST['bsubmit'])) {
//echo("Step 3 complete<br />");
            return true;
        }
//echo("Still Room type 1: ".(isset($_SESSION['hotelcard']['room_type_1']) ? $_SESSION['hotelcard']['room_type_1'] : '')."<br />");

        self::parseDataTable($arrFields);
//echo("Showing step 3<br />");
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

//echo("Creditcards: ".var_export($_SESSION['hotelcard']['creditcard_id'], true)."<hr />");
//echo("Room facilities: ".var_export($_SESSION['hotelcard']['room_facility_id'], true)."<hr />");
        $arrFields = array();
        foreach ($_SESSION['hotelcard'] as $name => $value) {
            // Skip fields that are irrelevant or handled separately.
            // room_available, room_price, room_facility_id are all arrays
            // and handled together with room_type in the loop below.
            if (preg_match(
                '/(?:step|bsubmit'.
                '|group_id|contact_email_retype'.
                '|image_src|image_width|image_height'.
                '|image_file'.
                // These are part of the room type handling below
                '|room_available|room_price|room_facility_id'.
                ')$/',
                $name)) {
//echo("Skipped field name $name, value ".var_export($value, true)."<br />");
                continue;
            }

            // Fix values that are IDs, special, or arrays of anything
            if (preg_match('/country_id$/', $name)) {
                $value = Country::getNameById($value);
//            } elseif (preg_match('/region$/', $name)) {
//                $value = Region::getNameById($value);
            } elseif (preg_match('/^accomodation_type_id$/', $name)) {
                $value = HotelAccomodationType::getNameById($value);
            } elseif (preg_match('/^creditcard_id$/', $name)) {
                $value = join(', ', $value);
            } elseif (preg_match('/^lang_id$/', $name)) {
                $value = FWLanguage::getLanguageParameter($value, 'name');
            } elseif (preg_match('/gender$/', $name)) {
                $value = (preg_match('/[wf]/i', $value)
                  ? $_ARRAYLANG['TXT_HOTELCARD_GENDER_FEMALE']
                  : $_ARRAYLANG['TXT_HOTELCARD_GENDER_MALE']);
            } elseif (preg_match('/^rating$/', $name)) {
                $value = HotelRating::getString($value);
            } elseif (preg_match('/^hotel_facility_id$/', $name)) {
                $value = join(', ', $value);
            } elseif (preg_match('/^hotel_image_id$/', $name)) {
                $ord = (isset($_SESSION['hotelcard']['hotel_image_ord'])
                    ? $_SESSION['hotelcard']['hotel_image_ord'] : 0);
                $objImage = Image::getById($value, $ord);
                if (!$objImage) continue;
                $value = preg_replace('/^[0-9a-f]+_/', '', $objImage->getPath());
            } elseif (preg_match('/^room_type_1$/', $name)) {
                // Catch room type 1 only, as this is the only one mandatory.
                // Collect all hotel room data available now, any other
                // parameter will be skipped.
                $room_data = '';
                for ($i = 1; $i <= 4; ++$i) {
//echo("index $index, room_type $room_type<br />");
                    if (empty($_SESSION['hotelcard']['room_type_'.$i])) continue;
                    $room_data .=
                        sprintf($_ARRAYLANG['TXT_HOTELCARD_ROOM_TYPE_NUMBER'], $i).
                        '<br />'.
                        $_ARRAYLANG['TXT_HOTELCARD_ROOM_TYPE'].' '.
                        $_SESSION['hotelcard']['room_type_'.$i].'<br />'.
                        $_ARRAYLANG['TXT_HOTELCARD_ROOM_AVAILABLE'].' '.
                        $_SESSION['hotelcard']['room_available_'.$i].'<br />'.
                        $_ARRAYLANG['TXT_HOTELCARD_ROOM_PRICE'].' '.
                        $_SESSION['hotelcard']['room_price_'.$i].'<br />'.
                        $_ARRAYLANG['TXT_HOTELCARD_ROOM_FACILITY_ID'].' '.
                        (isset($_SESSION['hotelcard']['room_facility_id_'.$i])
                          ? join(', ', $_SESSION['hotelcard']['room_facility_id_'.$i])
                          : '').'<br /><br />';
                }
                $name = 'room_types';
                $value = $room_data;
            } elseif (preg_match('/^room_.+$/', $name)) {
                // Skip any other room_* index
//echo("Skipping room_* stuff $name, value ".var_export($value, true)."<br />");
                continue;
            } else {
//echo("Unhandled field name $name, value ".var_export($value, true)."<br />");
            }
            $name = 'TXT_HOTELCARD_'.strtoupper($name);
            if (empty($value) || empty($_ARRAYLANG[$name])) {
//echo("Note: Empty value or no language entry for $name => $value, skipped<br />");
                continue;
            }
//echo("Language variable $name => ".$_ARRAYLANG[$name]."<br />");
            $arrFields[$name] = array(
                'mandatory' => false,
                'label' => $_ARRAYLANG[$name],
                'input' => $value,
            );
        }
        $arrFields += array(
            'confirm_terms' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_CONFIRM_TERMS',
                'input' => Html::getCheckbox('confirm_terms'),
            ),
            'register_date' => array(
                'mandatory' => false,
                'label' => 'TXT_HOTELCARD_REGISTER_DATE',
                'input' => date(ASCMS_DATE_SHORT_FORMAT),
            ),
        );
        // Verify the data already present
        $flagComplete = self::verifyMandatoryFields($arrFields);
        if ($flagComplete && isset($_POST['bsubmit'])) {
//echo("Step 4 complete<br />");
            return true;
        }
//echo("Showing step 4<br />");
        self::parseDataTable($arrFields);
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
        global $_ARRAYLANG, $_CORELANG;

        $arrFields = array(
            // Dummy field for the submit button.
            // This is not shown, only checked.
            'bsubmit' => array(
                'mandatory' => false,
                'label' => '',
                'input' => '',
            ),

        );

        $result = true;
        if (empty($_SESSION['hotelcard']['registration_id'])) {
            // Store the hotel if it hasn't been yet
            $result = Hotel::insertFromSession();
            if (!$result) {
                self::addMessage($_ARRAYLANG['TXT_HOTELCARD_ERROR_STORING_HOTEL']);
            } else {
                self::addMessage($_ARRAYLANG['TXT_HOTELCARD_HOTEL_STORED_SUCCESSFULLY']);
//echo("Successfully stored Hotel from session<br />");
// TODO: Use proper Mail templates
                require_once(ASCMS_CORE_PATH.'/Mail.class.php');

                // Day of the week, number and string abbreviation
                $arrDow = explode(',', $_CORELANG['TXT_CORE_DAY_ARRAY']);

                if (Mail::send(
                    $_SESSION['hotelcard']['contact_email'],
                    'info@hotelcard.ch',
                    'hotelcard.ch',
                    'Danke für Ihre Registrierung',
                    $_SESSION['hotelcard']['contact_name'].",\n\n\n".
                    "Sie haben das Hotel ".$_SESSION['hotelcard']['hotel_name']." am ".

                    $arrDow[date('w', $_SESSION['hotelcard']['registration_date'])].
                    ', '.
                    date(ASCMS_DATE_SHORT_FORMAT,
                        $_SESSION['hotelcard']['registration_date']).
// date(ASCMS_DATE_FORMAT_DOW_D_M_Y, $_SESSION['hotelcard']['registration_date']).

                    " registriert.\n\n".
                    "Bitte bewahren Sie diese Nachricht auf und geben Sie Ihre Registrations ID ".
                    $_SESSION['hotelcard']['registration_id']." an, wenn Sie mit uns Kontakt ".
                    "aufnehmen.\n\n\n".
                    "Mit freundlichen Grüssen,\n\n".
                    "Das hotelcard.ch Team"
                )) {
                    self::addMessage(sprintf(
                        $_ARRAYLANG['TXT_HOTELCARD_REGISTRATION_MAIL_SENT_SUCCESSFULLY'],
                        $_SESSION['hotelcard']['contact_email']));
                } else {
                    self::addMessage(sprintf(
                        $_ARRAYLANG['TXT_HOTELCARD_REGISTRATION_MAIL_SENDING_FAILED'],
                        $_SESSION['hotelcard']['contact_email']));

                }
            }
        }

        // See whether the finish button has been clicked
        $flagComplete = self::verifyMandatoryFields($arrFields);
        if ($flagComplete) {
//echo("Step 5 complete<br />");
            return true;
        }
//echo("Showing step 5<br />");
        return false; // Still running
    }


    /**
     * Edit the hotel data
     *
     * Determines the hotel ID from the User field selected in the settings.
     * There is no way to fake this ID with the request, so no user may
     * change other than her own hotel data.
     * @todo    Maybe there should be a way to manage more than one hotel
     * for a single user.  If so, some kind of selection needs to be added.
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
        SettingDb::init(MODULE_ID);
        $attribute_id = SettingDb::getValue('user_attribute_hotel_id');
        $hotel_id = User_Profile_Attribute::getAttributeValue($attribute_id, $user_id);
//die("attribute ID $attribute_id, got hotel ID $hotel_id<br />");
//$hotel_id = 6;

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
//DBG::enable_adodb_debug();
//DBG::enable_error_reporting();

        $objhotel = Hotel::getById($hotel_id);
// TODO:  Add error message, maybe a redirect
        if (empty($objhotel)) {
            self::addMessage($_ARRAYLANG['TXT_HOTELCARD_ERROR_HOTEL_ID_NOT_FOUND'].' '.$hotel_id);
            return false;
        }

        // Store changes, if any.
        // The type IDs and more information is taken from the post parameters.
        if (isset($_POST['bsubmit'])) self::updateHotel($hotel_id);

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

        // Abbreviations for day of the week
        $arrDow = explode(',', $_CORELANG['TXT_CORE_DAY_ABBREV2_ARRAY']);

        // Spray language variables all over
        self::$objTemplate->setGlobalVariable($_ARRAYLANG);
        self::$objTemplate->setGlobalVariable(array(
            'HOTELCARD_FORM_SUBMIT_VALUE' => $_ARRAYLANG['TXT_HOTELCARD_FORM_SUBMIT_STORE'],
            'HOTELCARD_HOTEL_ID'          => $hotel_id,
            'HOTELCARD_DATE_FROM'         => Html::getSelectDate(
                'date_from', $_SESSION['hotelcard']['date_from']),
            'HOTELCARD_DATE_TO'           => Html::getSelectDate(
                'date_to', $_SESSION['hotelcard']['date_to']),
            // Datepicker language and settings
            'HOTELCARD_DPC_TODAY_TEXT'    => $_CORELANG['TXT_CORE_TODAY'],
            'HOTELCARD_DPC_BUTTON_TITLE'  => $_ARRAYLANG['TXT_HOTELCARD_OPEN_CALENDAR'],
            'HOTELCARD_DPC_MONTH_NAMES'   =>
                "'".join("','", explode(',', $_CORELANG['TXT_MONTH_ARRAY']))."'",
            // Reformat from "Su,Mo,Tu,We,Th,Fr,Sa"
            // to "'Su','Mo','Tu','We','Th','Fr','Sa'"
            'HOTELCARD_DPC_DAY_NAMES'     => "'".join("','", $arrDow)."'",
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

        // Fetch the room types and availabilities
        $arrRoomTypes = HotelRoom::getTypeArray(
            $hotel_id, 0,
            date('Y-m-d', $time_from),
            date('Y-m-d', $time_to)
        );
        // Fill up the room type array if there are less than four.
        // The negative indices will be ignored after they are posted back
        // and are stored.
        $not_id = 0;
        while (count($arrRoomTypes) < 4) {
            $arrRoomTypes[--$not_id] = array(
                'name' => '',
                'number_default' => '',
                'price_default' => '',
                'facilities' => array(),
                'availabilities' => array(),
            );
        };

        // Complete list of all facilites for reference
        $arrAllFacilities = HotelRoom::getFacilityNameArray();

        foreach ($arrRoomTypes as $type_id => $arrRoomType) {
            self::$objTemplate->setGlobalVariable(array(
                'HOTELCARD_ROOM_TYPE_ID' => $type_id,
            ));

            $name              = $arrRoomType['name'];
            $number_default    = $arrRoomType['number_default'];
            $price_default     = $arrRoomType['price_default'];
            // We just need the keys to see which are provided
            $arrFacility_id    = array_keys($arrRoomType['facilities']);
            $arrAvailabilities = $arrRoomType['availabilities'];
            for ($time = $time_from; $time <= $time_to; $time += 86400) {
                $date = date('Y-m-d', $time);
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
//echo("Room type $type_id: $name: number_total $number_total, number_booked $number_booked, number_cancelled$number_cancelled, price $price<br />");
                self::$objTemplate->setVariable(array(
                    'HOTELCARD_ROWCLASS'     => ($intDow % 6 ? 'row1' : 'row2'),
                    'HOTELCARD_DATE'         => $strDow.', '.date(ASCMS_DATE_SHORT_FORMAT, $time),
                    'HOTELCARD_TOTAL'        => html::getInputText('availability['.$type_id.']['.$date.'][number_total]', $number_total, 'style="width: 40px; text-align: right;"'),
                    'HOTELCARD_BOOKED'       => html::getInputText('availability['.$type_id.']['.$date.'][number_booked]', $number_booked, 'style="width: 40px; text-align: right;"'),
                    'HOTELCARD_CANCELLED'    => html::getInputText('availability['.$type_id.']['.$date.'][number_cancelled]', $number_cancelled, 'style="width: 40px; text-align: right;"'),
                    'HOTELCARD_PRICE'        => html::getInputText('availability['.$type_id.']['.$date.'][price]', $price, 'style="width: 80px; text-align: right;"'),
                ));
                self::$objTemplate->parse('hotelcard_day');
            }
            foreach ($arrAllFacilities as $facility_id => $facility_name) {
//echo("Room type $type_id: facility_id $facility_id, facility_name $facility_name, ".(in_array($facility_id, $arrFacility_id) ? 'selected' : '')."<br />");
                self::$objTemplate->setVariable(array(
//                    'HOTELCARD_FACILITY_ID'   => $facility_id,
                    'HOTELCARD_FACILITY_NAME' => $facility_name,
                    'HOTELCARD_FACILITY'      => Html::getCheckbox(
                        $type_id.'[facility_id]', $facility_id, '',
                        in_array($facility_id, $arrFacility_id)),
                ));
                self::$objTemplate->parse('hotelcard_facility');
            }
            self::$objTemplate->setVariable(array(
                'HOTELCARD_ROOM_TYPE'      => Html::getInputText('roomtype['.$type_id.'][room_type]', $name, 'style="width: 200px;"'),
                'HOTELCARD_NUMBER_DEFAULT' => Html::getInputText('roomtype['.$type_id.'][number_default]', $number_default, 'style="width: 200px; text-align: right;"'),
                'HOTELCARD_PRICE_DEFAULT'  => Html::getInputText('roomtype['.$type_id.'][price_default]', $price_default, 'style="width: 200px; text-align: right;"'),
            ));
            self::$objTemplate->parse('hotelcard_roomtype');
        }
        self::$objTemplate->touchBlock('hotelcard_form_date');
        return true;
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
        if (empty($_POST)) return '';
//echo("Hotelcard::updateHotel($hotel_id):  POST:<br />".nl2br(var_export($_POST, true))."<hr />");

//DBG::enable_adodb_debug();
//DBG::enable_error_reporting();

        foreach ($_POST as $name => $value) {
            switch ($name) {
              case 'availability':
                  // Array indexed by room type IDs,
                  // containing date => availability pairs:
                  //  availability[room_type_id][date] =>
                  //    array(number_total, number_booked, number_cancelled, price)
                  foreach ($value as $room_type_id => $arrAvailability) {
                      if (HotelRoom::storeAvailabilityArray(
                          $room_type_id, $arrAvailability) === false)
// TODO: Add error message
                          return false;
                  }
                  break;
              case 'roomtype':
                  // Array indexed by room type IDs,
                  // containing room type parameters:
                  //  roomtype[room_type_id] =>
                  //    array(number_total, number_booked, number_cancelled, price)
                  foreach ($value as $room_type_id => $arrRoomtype) {
                      $room_type = $arrRoomtype['room_type'];
                      $number_default = $arrRoomtype['number_default'];
                      $price_default = $arrRoomtype['price_default'];
                      if (HotelRoom::storeType(
                          $hotel_id, $room_type,
                          $number_default, $price_default,
                          ($room_type_id > 0 ? $room_type_id : 0)) === false)
// TODO: Add error message
                          return false;
                  }
                  break;
              default:
// TODO: Add error message
                  return false;
            }
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

}

?>
