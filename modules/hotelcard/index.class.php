<?php

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
require_once ASCMS_CORE_PATH.'/Country.class.php'; // Also contains the region and location classes
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
     * Mail template key for the hotel registration confirmation
     */
    const MAILTEMPLATE_HOTEL_REGISTRATION_CONFIRMATION =
        'hotelcard_hotel_registration_confirmation';

    /**
     * The default "no image" URI
     */
    const PATH_NO_IMAGE = 'images/modules/hotelcard/no_picture.gif';


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
     * Determine the page to be shown and call appropriate methods.
     * @return  string            The finished HTML page content
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getPage($page_content)
    {
        global $_ARRAYLANG;

DBG::activate(DBG_PHP|DBG_ADODB_ERROR|DBG_LOG_FIREPHP);
//DBG::activate(DBG_PHP|DBG_ADODB|DBG_LOG_FIREPHP);

//SettingDb::init();
//SettingDb::add('hotel_minimum_rooms_per_day', 1, 201, 'text', '', 'admin');
//SettingDb::delete('hotel_minimum_rooms_days', 1, 201, 'text', '', 'admin');

        // PEAR Sigma template
        self::$objTemplate = new HTML_Template_Sigma('.');
        CSRF::add_placeholder(self::$objTemplate);
        self::$objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        self::$objTemplate->setTemplate($page_content, true, true);
        self::$objTemplate->setGlobalVariable(
            'HOTELCARD_FORM_SUBMIT_VALUE',
            $_ARRAYLANG['TXT_HOTELCARD_FORM_SUBMIT_STORE']
        );

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
            case 'edit_hotel_roomtypes':
            case 'edit_hotel_contact':
            case 'edit_hotel_images':
            case 'edit_hotel_details':
            case 'edit_hotel_facilities':
//echo("Edit Hotel<br />");
//DBG::activate(DBG_PHP|DBG_ADODB|DBG_LOG_FIREPHP);
                // We have different templates and contents here, but the
                // User and Hotel ID must be verified in any of these cases.
                $result &= HotelcardLibrary::editHotel(self::$objTemplate);
                break;

            case 'terms':
                $result &= self::terms();
                break;

            // Ajax
            case 'get_locations':
                die(
                    Location::getMenuoptions(
                        (isset($_GET['state']) ? $_GET['state'] : ''),
                        (isset($_SESSION['hotelcard']['hotel_location'])
                          ? $_SESSION['hotelcard']['hotel_location'] : 0),
                        '%1$s (%2$s)', true)
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
                )) ? "Mail sent successfully" : "Failed to send mail");
                break;


            case 'details':
                $result &= self::detail();
                break;

            case 'overview':
            default:
                $result &= self::overview();
        }
        $message = HotelcardLibrary::getMessage();
        if ($message)
            self::$objTemplate->setVariable(array(
                'HOTELCARD_MESSAGE' => $message,
                'HOTELCARD_MESSAGE_TYPE' => HotelcardLibrary::getMessagetype(),
            ));
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

//DBG::activate(DBG_ADODB|DBG_PHP|DBG_LOG_FIREPHP);

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
// echo FOR TESTING ONLY
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
//        HotelcardLibrary::processPostFiles();
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
            if (  $_SESSION['hotelcard']['step_complete']
                < $_SESSION['hotelcard']['step_current'])
                $_SESSION['hotelcard']['step_complete'] =
                    $_SESSION['hotelcard']['step_current'];
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
            'HOTELCARD_FORM_ACTION' => Html::getRelativeUri_entities(),
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
            // Unset the contact name to mark this session obsolete.
            // *DO NOT* unset the whole session or the hotelcard branch,
            // otherwise the step number will be shown as zero in the last step!
// Comment for TESTING ONLY, so you can reload the last page
            unset($_SESSION['hotelcard']['contact_name']);
        }
//echo("Steps done<br />");
        return $result_step;
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
     * Shows the first step of the "Add Hotel" wizard
     *
     * If it still misses information, shows itself, and returns false.
     * Returns true if the information is complete.
     */
    static function addHotelStep1()
    {
        global $_ARRAYLANG;


        // If the step is entered at the beginnig of a new registration,
        // make sure that the Hotel ID is reset.
        // Otherwise, existing Hotels may be overwritten if the User is
        // logged in and has previously edited another Hotel!
        if (empty($_SESSION['hotelcard']['contact_email_retype'])) {
            $_SESSION['hotelcard']['hotel_id'] = 0;
        }

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
//                'mandatory' => true,
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
            ),
            'hotel_location' => array(
                'mandatory' => true,
                'label' => 'TXT_HOTELCARD_HOTEL_LOCATION',
                'input' => Html::getSelect('hotel_location',
                    (isset($_SESSION['hotelcard']['hotel_region'])
                      ? Location::getArrayByState($_SESSION['hotelcard']['hotel_region'], '%1$s (%2$s)')
                      : array($_ARRAYLANG['TXT_HOTELCARD_HOTEL_LOCATION_PLEASE_CHOOSE_REGION'])),
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
                'input' => Html::getInputText('contact_email_retype',
                    (isset($_SESSION['hotelcard']['contact_email_retype'])
                        ? $_SESSION['hotelcard']['contact_email_retype'] : ''), false,
                    'style="text-align: left;"'),
            ),
        );
        // Verify the data already present if it's the current step
        $complete = HotelcardLibrary::verifyAndStoreHotel($arrFields);
        // Only verify the e-mail addresses after the fields have been
        // filled out
        if ($complete) {
            if ($_SESSION['hotelcard']['contact_email'] !=
                  $_SESSION['hotelcard']['contact_email_retype']) {
                $complete = false;
                $arrFields['contact_email']['error'] = HotelcardLibrary::INCOMPLETE_CLASS;
                $arrFields['contact_email_retype']['error'] = HotelcardLibrary::INCOMPLETE_CLASS;
                HotelcardLibrary::addMessage($_ARRAYLANG['TXT_HOTELCARD_EMAILS_DO_NOT_MATCH']);
            } elseif (!FWValidator::isEmail($_SESSION['hotelcard']['contact_email'])) {
                $complete = false;
                $arrFields['contact_email']['error'] = HotelcardLibrary::INCOMPLETE_CLASS;
                HotelcardLibrary::addMessage($_ARRAYLANG['TXT_HOTELCARD_EMAIL_IS_INVALID']);
            }
        }
        if ($complete && isset($_POST['bsubmit'])) return true;
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

        $hotel_id = $_SESSION['hotelcard']['hotel_id'];

        $arrFields = array();
        if (SettingDb::getValue('terms_and_conditions_file_'.FRONTEND_LANG_ID)) {
            $arrFields = array(
                // The visible terms are inserted in the step heading.
                // Terms for downloading
                'download_terms' => array(
                    'mandatory' => false,
                    'label' => 'TXT_HOTELCARD_TERMS_DOWNLOAD',
                    'input' =>
                        '<a target="agb" href="'.
                          SettingDb::getValue(
                              'terms_and_conditions_file_'.FRONTEND_LANG_ID).
                        '">'.$_ARRAYLANG['TXT_HOTELCARD_TERMS_DOWNLOAD_CLICK'].'</a>'
            ));
        }
        $arrFields += array(
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
        $complete = HotelcardLibrary::verifyAndStoreHotel($arrFields);
        if ($complete && isset($_POST['bsubmit'])) {
            if (empty($_SESSION['hotelcard']['username'])) {
//DBG::log("No username in session");
                $email = $_SESSION['hotelcard']['contact_email'];
                $objFWUser = FWUser::getFWUserObject();
                $objUser = $objFWUser->objUser;
                // Not logged in,
                if (   $objUser->EOF
                // or a User is logged in, but the e-mail address differs
                    || $email != $objUser->getEmail()) {
                    // create a new User
//DBG::log("User is not logged in, or different e-mail address");
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
//DBG::log("first $firstname, last $lastname");
                        $objUser->setProfile(array(
                            'firstname' => array(0 => $firstname),
                            'lastname'  => array(0 => $lastname),
                            SettingDb::getValue('user_profile_attribute_hotel_id') =>
                                array(0 => $_SESSION['hotelcard']['hotel_id']),
                        ));
                        $username = User::makeUsername($firstname, $lastname);
                        if (!$username) {
                            HotelcardLibrary::addMessage(sprintf(
                                $_ARRAYLANG['TXT_HOTELCARD_REGISTRATION_CREATING_USERNAME_FAILED'],
                                $_SESSION['hotelcard']['contact_email']));
                            $complete = false;
                        } else {
                            $password = User::makePassword();
//DBG::log("Setting username $username, pass $password<br />");
                            $objUser->setUsername($username);
                            $objUser->setPassword($password);
                            $objUser->setEmail($_SESSION['hotelcard']['contact_email']);
                            $objUser->setGroups(array(SettingDb::getValue('hotel_usergroup')));
                            $objUser->setActiveStatus(1);
                        }
                    }
                } else {
                    // The user is logged in and provides the same e-mail address
                    $user_profile_attribute_hotel_id =
                        SettingDb::getValue('user_profile_attribute_hotel_id');
                    // Append the new Hotel ID to the list
                    $hotel_id_list = $objUser->getProfileAttribute(
                        $user_profile_attribute_hotel_id).
                        ','.$_SESSION['hotelcard']['hotel_id'];
                    // Append the additional Hotel ID, does not clear
                    // the first and last name
                    $objUser->setProfile(array(
                        $user_profile_attribute_hotel_id =>
                            array(0 => $hotel_id_list),
                    ));
                }
//DBG::log(("Storing User"));
                if ($objUser->store()) {
                    $_SESSION['hotelcard']['lastname'] = $lastname;
                    $_SESSION['hotelcard']['username'] = $username;
                    $_SESSION['hotelcard']['password'] = $password;
//echo("user stored, session: ".$_SESSION['hotelcard']['username']." / ".$_SESSION['hotelcard']['password']."<br />");
                    Hotel::updateStatus($hotel_id, Hotel::STATUS_ACCOUNT);
                } else {
//echo("ERROR: Failed to store user<br />");
                    HotelcardLibrary::addMessage(sprintf(
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
            if (empty($_SESSION['hotelcard']['mail_sent'])) {
//echo("Mail not sent yet<br />");
                // If it hasn't happened before, send a confirmation by e-mail.
                // Note that this may fail and will be retried on submitting
                // this step if it did.
                if (self::sendRegistrationConfirmationMail()) {
//echo("Mail sent<br />");
                    $_SESSION['hotelcard']['mail_sent'] = 1;
                    Hotel::updateStatus($hotel_id, Hotel::STATUS_CONFIRMED);
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
        HotelcardLibrary::processPostFiles();
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
                    Image::getFromSessionByName('image'),
                    'image', HotelcardLibrary::IMAGETYPE_TITLE,
                    HotelcardLibrary::IMAGE_PATH_HOTEL_DEFAULT),
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
            && HotelcardLibrary::verifyAndStoreHotel($arrFields)) {
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
            $numof_beds =
                (isset($_SESSION['hotelcard']['numof_beds_'.$i])
                  ? $_SESSION['hotelcard']['numof_beds_'.$i] : '');
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
                'numof_beds_'.$i => array(
                    'mandatory' => ($i == 1),
                    'label' => 'TXT_HOTELCARD_NUMOF_BEDS',
                    'input' => Html::getSelect('numof_beds_'.$i,
                        HotelRoom::getNumofBedsArray($numof_beds),
                        $numof_beds, false,
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
            && HotelcardLibrary::verifyAndStoreHotel($arrFields)) {
//echo("After:<br />".htmlentities(var_export($arrFields, true), ENT_QUOTES, CONTREXX_CHARSET)."<hr />");
            // The last-but-one step is complete, so update the Hotel status
            if (!Hotel::updateStatus(
                $_SESSION['hotelcard']['hotel_id'], Hotel::STATUS_COMPLETED)
            ) {
                HotelcardLibrary::addMessage(
                    $_ARRAYLANG['TXT_HOTELCARD_ERROR_UPDATING_STATUS'],
                    HotelcardLibrary::MSG_ERROR);
                return false;
            }
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


    /**
     * Sends the mail confirming the successful registration of a new Hotel
     * @return  boolean               True on success, false otherwise
     */
    static function sendRegistrationConfirmationMail()
    {
        global $_CORELANG, $_ARRAYLANG;

        if (empty($_SESSION['hotelcard']['username'])) return false;
        SettingDb::init('admin');
        // Day of the week, number and string abbreviation
        $arrDow = explode(',', $_CORELANG['TXT_CORE_DAY_ARRAY']);

        $search = array(
            '[admin_email]',
            '[contact_email]',
            '[contact_salutation]',
            '[contact_name]',
            '[hotel_id]',
            '[hotel_name]',
            '[registration_time]',
            '[username]',
            '[password]',
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
            HotelcardLibrary::addMessage(sprintf(
                $_ARRAYLANG['TXT_HOTELCARD_REGISTRATION_MAIL_SENT_SUCCESSFULLY'],
                $_SESSION['hotelcard']['contact_email']), HotelcardLibrary::MSG_OK);
            return true;
        }
        HotelcardLibrary::addMessage(sprintf(
            $_ARRAYLANG['TXT_HOTELCARD_REGISTRATION_MAIL_SENDING_FAILED'],
            $_SESSION['hotelcard']['contact_email']));
        return false;
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
             WHERE name='hotelcard'";
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
                )";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                return false;
            }
        }

        // Verify that the backend area is present
        $query = "
            SELECT 1
              FROM ".DBPREFIX."backend_areas
             WHERE uri='index.php?cmd=hotelcard'";
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
                );";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                return false;
            }
        }

        return false;
    }


    /**
     * Returns the current value of the static $page_title variable
     * @return  string                    The current page title
     */
    function getPageTitle()
    {
        return self::$page_title;
    }


    /**
     * Returns a string representing the current add Hotel wizard step number,
     * like " - Step X", to be included in the HTML title tag
     * @return  string                    The current add Hotel wizard step
     */
    static function getStepString()
    {
        global $_ARRAYLANG;

        return ' - '.sprintf(
            $_ARRAYLANG['TXT_HOTELCARD_STEP_NUMBER'],
            $_SESSION['hotelcard']['step_current']);
    }


    /**
     * Redirect to the target URI
     *
     * Replaces parameters in the current page address taken from
     * {@see Html::getRelativeUri()} with those present in the $targetUri
     * parameter, then jumps to the result.
     * Neither protocol, host, path, nor script name will be modified.
     * Note:  The parameter is expected to contain simple ampersands (&)
     * between parameters.  Entities will cause additional parameters to
     * be added!
     * @param   string    $targetUri    The target page address or parameters
     */
    static function redirect($targetUri)
    {
        $uri = Html::getRelativeUri();
        // Strip proto, host, path, and script name from the URI,
        // leave the parameters only
        $targetUri = preg_replace('/^.+\?/', '', $targetUri);
        // Replace any parameters left in the target URI.
        // Split the string at everything that is not part of any single
        // argument
        foreach (preg_split('/[^\w\d\=\%\+]+/', $targetUri, null, PREG_SPLIT_NO_EMPTY) as $parameter) {
            Html::replaceUriParameter($uri, $parameter);
        }
        header('Location: '.$uri);
        exit();
    }


    /**
     * Set up the Hotelcard overview page
     *
     * This will be the starting page for visitors.
     * @todo    Determine the contens and layout of this page
     * @return  boolean               True on success, false otherwise
     */
// TODO
    static function overview()
    {
        global $_ARRAYLANG;

//DBG::activate(DBG_PHP|DBG_ADODB_ERROR|DBG_LOG_FIREPHP);

        // Requires DatePickerControl.js
        JS::activate('datepicker');

        $objSorting = new Sorting(
            Html::getRelativeUri_entities(),
            array(
                'hotel_name'     => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_NAME'],
                'hotel_location' => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_LOCATION'],
                'room_price'     => $_ARRAYLANG['TXT_HOTELCARD_ROOM_PRICE'],
                'rating'         => $_ARRAYLANG['TXT_HOTELCARD_RATING'],
            ),
            false
        );

//        $arrResults = false;
        if ($_REQUEST) {
            // Gobble up all posted data whatsoever
            foreach ($_REQUEST as $key => $value) {
                $_SESSION['hotelcard'][$key] = (is_array($_REQUEST[$key])
                    ? $value : contrexx_stripslashes($value));
            }
        }
        HotelcardLibrary::setSessionDateRangeFixed();
        SettingDb::init('frontend');

        $uri_base = Html::getRelativeUri_entities();
//        Html::replaceUriParameter($uri_base, 'hotel_region='.$_SESSION['hotelcard']['hotel_region']);
//        Html::replaceUriParameter($uri_base, 'hotel_location='.$_SESSION['hotelcard']['hotel_location']);
//        Html::replaceUriParameter($uri_base, 'date_from='.$_SESSION['hotelcard']['date_from']);
//        Html::replaceUriParameter($uri_base, 'date_to='.$_SESSION['hotelcard']['date_to']);
        $objSorting->setUri($uri_base);
        self::showSearchForm();
        $count = 0;
        $limit = SettingDb::getValue('hotel_per_page_frontend');
        $arrResults = Hotel::getIdArray(
            $count,
            $objSorting->getOrder(),
            array(
                'hotel_region'   =>
                    (empty($_SESSION['hotelcard']['hotel_region'])
                      ? '' : $_SESSION['hotelcard']['hotel_region']),
                'hotel_location' =>
                    (empty($_SESSION['hotelcard']['hotel_location'])
                      ? 0 : $_SESSION['hotelcard']['hotel_location']),
                'room_price_min' =>
                    (empty($_SESSION['hotelcard']['room_price_min'])
                      ? 0 : $_SESSION['hotelcard']['room_price_min']),
                'room_price_max' =>
                    (empty($_SESSION['hotelcard']['room_price_max'])
                      ? null : $_SESSION['hotelcard']['room_price_max']),
            ),
            Paging::getPosition(),
            $limit,
            true
        );
        if ($arrResults) {
            $objImage_thumb_default = new Image();
            $objImage_thumb_default->setImagetypeKey('hotelcard_hotel_title');
            $objImage_thumb_default->setPath(self::PATH_NO_IMAGE);
//DBG::log("Replacement thumb: ".var_export($objImage_thumb_default, true));
            foreach ($arrResults as $hotel_id) {
                self::showHotel($hotel_id);
                self::$objTemplate->parse('hotelcard_hotel');
            }
        }
        require_once ASCMS_CORE_PATH.'/Currency.class.php';
        self::$objTemplate->setGlobalVariable(
            $_ARRAYLANG
          + array(
            'HOTELCARD_PRICE_UNIT'          => Currency::getActiveCurrencyCode(),
            'HEAD_HOTELCARD_HOTEL_NAME'     => $objSorting->getHeaderForField('hotel_name'),
            'HEAD_HOTELCARD_HOTEL_REGION'   => $objSorting->getHeaderForField('hotel_region'),
            'HEAD_HOTELCARD_HOTEL_LOCATION' => $objSorting->getHeaderForField('hotel_location'),
            'HEAD_HOTELCARD_ROOM_PRICE'     => $objSorting->getHeaderForField('room_price'),
            'HEAD_HOTELCARD_RATING'         => $objSorting->getHeaderForField('rating'),
            'HOTELCARD_PRICE_UNIT'          => Currency::getActiveCurrencyCode(),
        ));
        JS::activate('shadowbox');
        return true;
    }


// TODO
    static function detail()
    {
        global $_ARRAYLANG;

        if (empty($_REQUEST['hotel_id'])) {
//die("detail(): No Hotel ID");
            self::redirect('cmd=overview');
        }
        $hotel_id = $_REQUEST['hotel_id'];
        HotelcardLibrary::setSessionDateRangeFixed();
        SettingDb::init('frontend');
        self::showSearchForm();
        self::showHotel($hotel_id);
        return self::showAvailability($hotel_id);
    }


    /**
     * Set up the Hotel search form in the current template
     * @return  boolean             True
     */
    static function showSearchForm()
    {
        global $_ARRAYLANG, $_CORELANG;

        // Abbreviations for day of the week, indices 0 .. 6
        $arrDow = explode(',', $_CORELANG['TXT_CORE_DAY_ABBREV2_ARRAY']);
        // Months of the year, indices 1 .. 12
        $arrMoy = explode(',', $_CORELANG['TXT_CORE_MONTH_ARRAY']);
        unset($arrMoy[0]);
        self::$objTemplate->setGlobalVariable(
            // Spray language variables all over
            $_ARRAYLANG
          + array(
            // Datepicker language and settings
            'HOTELCARD_DPC_DEFAULT_FORMAT' => 'DD.MM.YYYY',
            'HOTELCARD_DPC_TODAY_TEXT'     => $_CORELANG['TXT_CORE_TODAY'],
            'HOTELCARD_DPC_BUTTON_TITLE'   => $_ARRAYLANG['TXT_HOTELCARD_OPEN_CALENDAR'],
            'HOTELCARD_DPC_MONTH_NAMES'    => "'".join("','", $arrMoy)."'",
            'HOTELCARD_DPC_DAY_NAMES'      => "'".join("','", $arrDow)."'",
            'HOTELCARD_FORM_SUBMIT_VALUE'  => $_ARRAYLANG['TXT_HOTELCARD_FORM_SUBMIT_UPDATE'],
        ));

        $arrFields = array(
            'hotel_region' => array(
                'label' => 'TXT_HOTELCARD_HOTEL_REGION',
                'input' => Html::getSelect(
                    'hotel_region',
                    array('' => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_REGION_ANY'])
                      + State::getArray(true),
                    (isset($_SESSION['hotelcard']['hotel_region'])
                      ? $_SESSION['hotelcard']['hotel_region']
                      : (isset($_SESSION['hotelcard']['hotel_location'])
                          ? State::getByLocation($_SESSION['hotelcard']['hotel_location'])
                          : '')), 'hotel_region',
                    'new Ajax.Updater(\'hotel_location\', \'index.php?section=hotelcard&amp;act=get_locations&amp;state=\'+document.getElementById(\'hotel_region\').value, { method: \'get\' });'),
            ),
            'hotel_location' => array(
                'label' => 'TXT_HOTELCARD_HOTEL_LOCATION',
                'input' => Html::getSelect('hotel_location',
                    (empty($_SESSION['hotelcard']['hotel_region'])
                      ? array($_ARRAYLANG['TXT_HOTELCARD_HOTEL_LOCATION_PLEASE_CHOOSE_REGION'])
                      : array($_ARRAYLANG['TXT_HOTELCARD_HOTEL_LOCATION_ANY'])
                         + Location::getArrayByState(
                            $_SESSION['hotelcard']['hotel_region'], '%1$s (%2$s)')),
                    (isset($_SESSION['hotelcard']['hotel_location'])
                        ? $_SESSION['hotelcard']['hotel_location'] : ''), 'hotel_location',
                    '', 'style="text-align: left;"'),
            ),
            'date_from' => array(
                'label' => 'TXT_HOTELCARD_DATE_FROM',
                'input' => Html::getSelectDate(
                    'date_from',
                    date(ASCMS_DATE_SHORT_FORMAT, $_SESSION['hotelcard']['date_from'])
                ),
            ),
            'date_to' => array(
                'label' => 'TXT_HOTELCARD_DATE_TO',
                'input' => Html::getSelectDate(
                    'date_to',
                    date(ASCMS_DATE_SHORT_FORMAT, $_SESSION['hotelcard']['date_to'])
                ),
            ),
        );
        return HotelcardLibrary::parseDataTable(self::$objTemplate, $arrFields);
    }


    static function showHotel($hotel_id)
    {
        global $_ARRAYLANG;

        $objHotel = Hotel::getById($hotel_id, true);
        if (!$objHotel) {
//die("showHotel($hotel_id): No Hotel");
            self::redirect('cmd=overview');
        }
        HotelcardLibrary::setSessionDateRangeFixed();
        $uri_base = Html::getRelativeUri_entities();
//        Html::replaceUriParameter($uri_base, 'hotel_region='.$_SESSION['hotelcard']['hotel_region']);
//        Html::replaceUriParameter($uri_base, 'hotel_location='.$_SESSION['hotelcard']['hotel_location']);
//        Html::replaceUriParameter($uri_base, 'date_from='.$_SESSION['hotelcard']['date_from']);
//        Html::replaceUriParameter($uri_base, 'date_to='.$_SESSION['hotelcard']['date_to']);

        $default_image = new Image();
        $default_image->setImagetypeKey('hotelcard_hotel_title');
        $default_image->setPath(self::PATH_NO_IMAGE);
//DBG::log("Replacement thumb: ".var_export($objImage_thumb_default, true));
//DBG::log("Hotel description: ".$objHotel->getFieldvalue('description_text'));
        // Ord 0 Images are all of the same type, hotelcard_hotel_title
        $objImage = Image::getById(
            $objHotel->getFieldvalue('image_id'), 0);
        $hotel_image_path = '';
        if ($objImage && File::exists($objImage->getPath())) {
DBG::log("Image OK: ".$objImage->getPath());
            $hotel_image = Html::getImage($objImage);
            $hotel_thumb = Html::getThumbnail($objImage);
            $hotel_image_path =
                FWValidator::getEscapedSource($objImage->getPath());
        } else {
DBG::log("Image not found: ".($objImage ? $objImage->getPath() : 'ID '.$objHotel->getFieldvalue('image_id')));
            $hotel_image = Html::getImage($default_image);
            $hotel_thumb = Html::getThumbnail($default_image);
        }
        $uri_detail = $uri_base;
        Html::replaceUriParameter($uri_detail, 'cmd=detail');
        Html::replaceUriParameter($uri_detail, 'hotel_id='.$hotel_id);
//DBG::log("Image: $hotel_image, Thumb: $hotel_thumb");
        self::$objTemplate->setVariable(array(
            'HOTELCARD_HOTEL_ID'         => $hotel_id,
            'HOTELCARD_HOTEL_IMAGE_PATH' => $hotel_image_path,
            'HOTELCARD_HOTEL_IMAGE'      => $hotel_image,
            'HOTELCARD_HOTEL_THUMB'      => $hotel_thumb,
            'HOTELCARD_HOTEL_NAME'       =>
                htmlentities(
                    $objHotel->getFieldvalue('hotel_name'),
                    ENT_QUOTES, CONTREXX_CHARSET),
            'HOTELCARD_HOTEL_DESCRIPTION' =>
                htmlentities($objHotel->getFieldvalue('description_text'),
                    ENT_QUOTES, CONTREXX_CHARSET),
            'HOTELCARD_HOTEL_DESCRIPTION_MORE' =>
                Html::shortenText(
                    $objHotel->getFieldvalue('description_text'),
                    250, $uri_detail),
            'HOTELCARD_HOTEL_REGION'     =>
                State::getByZip($objHotel->getFieldvalue('hotel_region')),
            'HOTELCARD_HOTEL_LOCATION'   =>
                $objHotel->getFieldvalue('hotel_location').'&nbsp;'.
                Location::getCityByZip($objHotel->getFieldvalue('hotel_location'), 204),
            'HOTELCARD_ROOM_PRICE'       => $objHotel->getFieldvalue('room_price'),
            'HOTELCARD_RATING'           => HotelRating::getString($objHotel->getFieldvalue('rating')),
        ));
        if (empty($hotel_image_path)) {
            self::$objTemplate->hideBlock('hotel_overview_link_image');
        } else {
            if (self::$objTemplate->blockExists('hotel_overview_link_image_end'))
                self::$objTemplate->touchBlock('hotel_overview_link_image_end');
        }
        return true;
    }


    static function showAvailability($hotel_id)
    {
        global $_CORELANG, $_ARRAYLANG;

//DBG::activate(DBG_ADODB_ERROR|DBG_PHP|DBG_LOG_FIREPHP);

        $arrRoomtypes = HotelRoom::getTypeArray(
            $hotel_id,
            $_SESSION['hotelcard']['date_from'],
            $_SESSION['hotelcard']['date_to']
        );
        $arrDow = explode(',', $_CORELANG['TXT_CORE_DAY_ABBREV2_ARRAY']);
        // Months of the year, indices 1 .. 12
        $arrMoy = explode(',', $_CORELANG['TXT_CORE_MONTH_ARRAY']);
        unset($arrMoy[0]);
        $first = true;

// TODO: Determine the dates in the range and their number in the range!
// Some availabilities may be empty.

        $time_start = $_SESSION['hotelcard']['date_from'];
        $time_end   = $_SESSION['hotelcard']['date_to'];
        foreach ($arrRoomtypes as $roomtype_id => $arrRoomtype) {
            $minimum_available = 1e9;
//DBG::log("Roomtype ID $roomtype_id:<br />".var_export($arrRoomtype, true)."<br />");
            for ($time = $time_start;
                $time <= $time_end;
                $time += 86400
            ) {
                $date = date(ASCMS_DATE_FORMAT_DATE, $time);
                $arrAvailability =
                    (isset($arrRoomtype['availabilities'][$date])
                      ? $arrRoomtype['availabilities'][$date]
                      : array (
                          'number_total' => '0',
                          'number_booked' => '0',
                          'number_cancelled' => '0',
                          'price' => '0.00',
                        )
                    );
                $numof_available =
                      $arrAvailability['number_total']
                    - $arrAvailability['number_booked']
                    + $arrAvailability['number_cancelled'];
                if ($numof_available < $minimum_available)
                    $minimum_available = $numof_available;
                self::$objTemplate->setVariable(array(
                    'HOTELCARD_ROOMTYPE_PRICE' =>
                        ($numof_available > 0
                          ? $arrAvailability['price']
                          : $_ARRAYLANG['TXT_HOTELCARD_PRICE_NONE']
                        ),
                    'HOTELCARD_ROOMS_AVAILABLE_X' =>
                        sprintf(
                            $_ARRAYLANG['TXT_HOTELCARD_ROOMS_AVAILABLE_X'],
                            $numof_available),
                ));
                self::$objTemplate->parse('hotelcard_roomtypes_row_weekday');
                if ($first) {
                    $time = strtotime($date);
//                    $day = date('j', $time);
//                    $month = $arrMoy[date('n', $time)];
//                    $year = date('Y', $time);
//                    $numof_days_month = date('t', $time);
                    $dow = date('w', $time);
                    self::$objTemplate->setVariable(
                        'HOTELCARD_WEEKDAY', $arrDow[$dow]);
                    self::$objTemplate->parse('hotelcard_roomtypes_header_weekday');
                }
            }
            $first = false;
            self::$objTemplate->setVariable(array(
                'HOTELCARD_ROOMTYPE_NAME' => $arrRoomtype['name'],
                'HOTELCARD_ROOMTYPE_AVAILABILITY' =>
                    ($minimum_available > 0
                      ? Html::getSelect(
                            'book['.$roomtype_id.']',
                            range(0, $minimum_available, 1),
                            0, false, '',
                            'style="width: 50px;"')
                      : '')
            ));
            self::$objTemplate->parse('hotelcard_roomtype_row');
        }
//die("EOF");

/*
Roomtype ID 574:
array (
  'id' => '574', 'name' => 'Doppelzimmer', 'name_text_id' => '7843', 'number_default' => '2', 'price_default' => '290.00', 'breakfast_included' => '1', 'numof_beds' => '2',
  'facilities' => array (
    1 => 'Badewanne', 2 => 'Dusche', 5 => 'Kühlschrank', 6 => 'Minibar',
    7 => 'Radio', 8 => 'Telefon', 9 => 'TV', 10 => 'WC', 11 => 'Weckradio', 13 => 'Haartrockner', ),
  'availabilities' => array (
    '2010-02-20' => array ( 'number_total' => '2', 'number_booked' => '0', 'number_cancelled' => '0', 'price' => '240.00', ),
    '2010-02-21' => array ( 'number_total' => '2', 'number_booked' => '0', 'number_cancelled' => '0', 'price' => '240.00', ),
  ),
)

<div class="hotel_detail_roomtypes">
<!-- BEGIN hotelcard_roomtypes_header -->
  <div class="hotel_detail_roomtype_header_name">[[TXT_HOTELCARD_ROOMTYPES]]</div>
  <div class="hotel_detail_roomtype_header_weekday">[[TXT_HOTELCARD_WEEKDAY]]</div>
  <div class="hotel_detail_roomtype_header_availability">[[TXT_HOTELCARD_AVAILABILITY]]</div>
<!-- END hotelcard_roomtypes_header -->
*/


    }
}

?>
