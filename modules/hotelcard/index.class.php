<?php

define('_HOTELCARD_DEBUG', 0);

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
require_once 'lib/HotelAccomodationType.class.php';
require_once 'lib/HotelCheckInOut.class.php';
require_once 'lib/HotelFacility.class.php';
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
    const HOTEL_REGISTRATION_STEPS = 9;

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
    private static $statusMessage = '';


    /**
     * Determine the page to be shown and call appropriate methods.
     * @return  string            The finished HTML page content
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getPage($page_content)
    {
        self::$page_content = $page_content;
        // PEAR Sigma template
        self::$objTemplate = new HTML_Template_Sigma('.');
        self::$objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        self::$objTemplate->setTemplate(self::$page_content, true, true);

        if (isset($_GET['cmd'])) {
            $_GET['act'] = $_GET['cmd'];
        }
        if (!isset($_GET['act'])) {
            $_GET['act'] = '';
        }

        // Flag for error handling
        $result = true;

        switch($_GET['act']) {
            case 'add_hotel':
                $result &= self::addHotel();
                break;
            case 'overview':
            default:
                $result &= self::overview();
        }
//        $result &= (empty(self::$statusMessage));
//        if (!$result) {
//            self::errorHandler();
//            global $_ARRAYLANG;
//            self::addMessage($_ARRAYLANG['TXT_HOTELCARD_ERROR_TRY_RELOADING']);
//        }
echo("Messages:<br />".self::$statusMessage."<hr />");
        self::$objTemplate->setVariable(
            'HOTELCARD_STATUS', self::$statusMessage);
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

DBG::enable_adodb(0);
DBG::enable_error_reporting();

        // Gobble up all posted data whatsoever
        foreach ($_POST as $key => $value) {
            $_SESSION['hotelcard'][$key] = $value;
        }
echo("Added POST<br />".nl2br(var_export($_POST, true))."<hr />Collected<br />".nl2br(var_export($_SESSION['hotelcard'], true))."<hr />");
        // Number of the current step
        $step_posted = 1;
        if (empty($_SESSION['hotelcard']['step'])) {
            $_SESSION['hotelcard']['step'] = 0;
        } else {
            $step_posted = $_SESSION['hotelcard']['step'];
        }
//echo("Posted step empty, resetting wizard<br />");
//            if (empty($step)) $step = 1;
//            // Reset the wizard step counter
//            $_SESSION['hotelcard_step'] = 0;
//            // Also drop the whole hotel registration array,
//            // start from scratch
//            unset($_SESSION['hotelcard']);
        // Start verifying the data from the step posted
        $step = $step_posted;
        while ($step < self::HOTEL_REGISTRATION_STEPS) {
echo("Trying step $step<br />");
            // Returns false if it misses some data,
            // continue with the next step if true is returned.
            // After each step has been shown once, the single steps
            // must verify their data
            $result_step = call_user_func(array('self', 'addHotelStep'.$step));
            if ($result_step) {
echo("Step $step successfully completed.  Session step ".$_SESSION['hotelcard']['step']."<br />");
                // This step has been completed
                // Show next step, without verification
                ++$step;
                continue;
            }
            // So the result was false, and the current step is shown
echo("Trying step $step FAILED<br />");
            self::$objTemplate->setVariable(array(
                'HOTELCARD_STEP'              => $step,
                'HOTELCARD_FORM_ACTION'       => $_SERVER['REQUEST_URI'],
                'HOTELCARD_FORM_SUBMIT_NAME'  => 'continue',
                'HOTELCARD_FORM_SUBMIT_VALUE' =>
                    $_ARRAYLANG['TXT_HOTELCARD_FORM_SUBMIT_CONTINUE'],
                'HOTELCARD_PAGE_NUMBER'       => sprintf(
                    $_ARRAYLANG['TXT_HOTELCARD_PAGE_NUMBER_X_OF_Y'],
                    $step, self::HOTEL_REGISTRATION_STEPS),
            ));
            return false;
        }
echo("Steps done<br />");
        return true;
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

        // Verify the data already present if it's the current step
        if ($_SESSION['hotelcard']['step'] == 1) {
            $flagComplete = true;
            $mandatoryFields = array(
                'hotel_group', // Mind that this goes into a lookup table!
                'accomodation_type_id',
                'lang_id',
                'hotel_name',
                'hotel_address',
                'hotel_zip',
                'hotel_location',
                'hotel_region_id',
                'contact_name',
                'contact_email',
                'contact_email_retype',
                'contact_phone',
                'contact_fax',
            );
            foreach ($mandatoryFields as $name) {
                if (empty($_SESSION['hotelcard'][$name])) {
echo("Step 1: missing mandatory field value for $name<br />");
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
echo("Step 1 complete<br />");
                return true;
            }
        }
echo("Showing step 1<br />");

        $arrData = array(
            'TXT_HOTELCARD_LANG_ID' =>
                FWLanguage::getMenuActiveOnly(
                    (isset($_SESSION['hotelcard']['lang_id'])
                        ? $_SESSION['hotelcard']['lang_id'] : FRONTEND_LANG_ID),
                    'lang_id', 'document.forms.form_hotelcard.submit();'),
            'TXT_HOTELCARD_HOTEL_NAME' => Html::getInputText('hotel_name',
                (isset($_SESSION['hotelcard']['hotel_name'])
                    ? $_SESSION['hotelcard']['hotel_name'] : '')),
            'TXT_HOTELCARD_HOTEL_GROUP' => Html::getInputText('hotel_group',
                (isset($_SESSION['hotelcard']['hotel_group'])
                    ? $_SESSION['hotelcard']['hotel_group'] : '')),
            'TXT_HOTELCARD_HOTEL_ACCOMODATION_TYPE_ID' => Html::getSelect(
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
            'TXT_HOTELCARD_CONTACT_PHONE' => Html::getInputText('contact_phone',
                (isset($_SESSION['hotelcard']['contact_phone'])
                    ? $_SESSION['hotelcard']['contact_phone'] : '')),
            'TXT_HOTELCARD_CONTACT_FAX' => Html::getInputText('contact_fax',
                (isset($_SESSION['hotelcard']['contact_fax'])
                    ? $_SESSION['hotelcard']['contact_fax'] : '')),
            'TXT_HOTELCARD_CONTACT_NAME' => Html::getInputText('contact_name',
                (isset($_SESSION['hotelcard']['contact_name'])
                    ? $_SESSION['hotelcard']['contact_name'] : '')),
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
        self::$objTemplate->setVariable(array(
            'TXT_HOTELCARD_NOTE' => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_ADD_STEP1_NOTE'],
        ));
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

        // Verify the data already present
        if ($_SESSION['hotelcard']['step'] == 2) {
            $flagComplete = true;
            $mandatoryFields = array(
                'confirm_terms',
                'hotel_name',
                'contact_name',
                'contact_position',
            );
            foreach ($mandatoryFields as $name) {
                if (empty($_SESSION['hotelcard'][$name])) {
echo("Step 2: missing mandatory field value for $name<br />");
                    $flagComplete = false;
                    self::addMessage($_ARRAYLANG['TXT_HOTELCARD_MISSING_'.strtoupper($name)]);
                }
            }
            if ($flagComplete) {
echo("Step 2 complete<br />");
                return true;
            }
        }
echo("Showing step 2<br />");
        $arrData = array(
            'TXT_HOTELCARD_CONFIRM_TERMS' => Html::getCheckbox('confirm_terms'),
            'TXT_HOTELCARD_REGISTER_DATE' => date('d.m.Y'),
            'TXT_HOTELCARD_HOTEL_NAME' => Html::getInputText('hotel_name',
                (isset($_SESSION['hotelcard']['hotel_name'])
                    ? $_SESSION['hotelcard']['hotel_name'] : '')),
            'TXT_HOTELCARD_CONTACT_NAME' => Html::getInputText('contact_name',
                (isset($_SESSION['hotelcard']['contact_name'])
                    ? $_SESSION['hotelcard']['contact_name'] : '')),
            'TXT_HOTELCARD_CONTACT_POSITION' => Html::getInputText('contact_position',
                (isset($_SESSION['hotelcard']['contact_position'])
                    ? $_SESSION['hotelcard']['contact_position'] : '')),
        );
        foreach ($arrData as $placeholder => $element) {
//echo("Language variable $placeholder => ".$_ARRAYLANG[$placeholder]."<br />");
            self::$objTemplate->setVariable(array(
                'HOTELCARD_DATA_LABEL' => $_ARRAYLANG[$placeholder],
                'HOTELCARD_DATA_INPUT' => $element,
            ));
            self::$objTemplate->parse('hotelcard_data');
        }
        self::$objTemplate->setVariable(array(
            'TXT_HOTELCARD_NOTE' => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_ADD_STEP2_NOTE'],
        ));
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

        // Verify the data already present
        if ($_SESSION['hotelcard']['step'] == 3) {
            $flagComplete = true;
            $mandatoryFields = array(
                'contact_name',
                'contact_gender',
                'contact_position',
                'contact_department',
                'contact_phone',
                'contact_email',
                'reservation_name',
                'reservation_gender',
                'reservation_phone',
                'reservation_fax',
                'reservation_email',
                'hotel_uri',
                'hotel_image',
                'hotel_rating',
                'numof_rooms',
                'checkin_from',
                'checkin_to',
                'checkout_from',
                'checkout_to',
                'found_how',
            );
            foreach ($mandatoryFields as $name) {
                if (empty($_SESSION['hotelcard'][$name])) {
echo("Step 3: missing mandatory field value for $name<br />");
                    $flagComplete = false;
                    self::addMessage($_ARRAYLANG['TXT_HOTELCARD_MISSING_'.strtoupper($name)]);
                }
            }
            if ($flagComplete) {
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
            }
            if ($flagComplete) {
echo("Step 3 complete<br />");
                return true;
            }
        }
echo("Showing step 3<br />");
        $arrData = array(
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
            'TXT_HOTELCARD_CONTACT_POSITION' =>
                Html::getInputText('contact_position',
                    (isset($_SESSION['hotelcard']['contact_position'])
                        ? $_SESSION['hotelcard']['contact_position'] : '')),
            'TXT_HOTELCARD_CONTACT_DEPARTMENT' =>
                Html::getInputText('contact_department',
                    (isset($_SESSION['hotelcard']['contact_department'])
                        ? $_SESSION['hotelcard']['contact_department'] : '')),
            'TXT_HOTELCARD_CONTACT_PHONE' =>
                Html::getInputText('contact_phone',
                    (isset($_SESSION['hotelcard']['contact_phone'])
                        ? $_SESSION['hotelcard']['contact_phone'] : '')),
            'TXT_HOTELCARD_CONTACT_EMAIL' =>
                Html::getInputText('contact_email',
                    (isset($_SESSION['hotelcard']['contact_email'])
                        ? $_SESSION['hotelcard']['contact_email'] : '')),
            'TXT_HOTELCARD_RESERVATION_DATA' => '',
            'TXT_HOTELCARD_RESERVATION_NAME' =>
                Html::getInputText('reservation_name',
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
            'TXT_HOTELCARD_RESERVATION_PHONE' =>
                Html::getInputText('reservation_phone',
                    (isset($_SESSION['hotelcard']['reservation_phone'])
                        ? $_SESSION['hotelcard']['reservation_phone'] : '')),
            'TXT_HOTELCARD_RESERVATION_FAX' =>
                Html::getInputText('reservation_fax',
                    (isset($_SESSION['hotelcard']['reservation_fax'])
                        ? $_SESSION['hotelcard']['reservation_fax'] : '')),
            'TXT_HOTELCARD_RESERVATION_EMAIL' =>
                Html::getInputText('reservation_email',
                    (isset($_SESSION['hotelcard']['reservation_email'])
                        ? $_SESSION['hotelcard']['reservation_email'] : '')),
            'TXT_HOTELCARD_ADDITIONAL_DATA' => '',
            'TXT_HOTELCARD_HOTEL_URI' =>
                Html::getInputText('hotel_uri',
                    (isset($_SESSION['hotelcard']['hotel_uri'])
                        ? $_SESSION['hotelcard']['hotel_uri'] : '')),
            'TXT_HOTELCARD_HOTEL_IMAGE' =>
                Html::getInputFileupload('hotel_image'),
            'TXT_HOTELCARD_HOTEL_RATING' =>
                HotelRating::getMenu(
                    'hotel_rating',
                    (isset($_SESSION['hotelcard']['hotel_rating'])
                        ? $_SESSION['hotelcard']['hotel_rating'] : '')),
            'TXT_HOTELCARD_NUMOF_ROOMS' =>
                Html::getInputText('numof_rooms',
                    (isset($_SESSION['hotelcard']['numof_rooms'])
                        ? $_SESSION['hotelcard']['numof_rooms'] : '')),
            'TXT_HOTELCARD_CHECKIN_FROM_TO' =>
                HotelCheckInOut::getMenuCheckin(
                    'checkin_from',
                    (isset($_SESSION['hotelcard']['checkin_from'])
                        ? $_SESSION['hotelcard']['checkin_from'] : '')).
                '&nbsp;-&nbsp;'.
                HotelCheckInOut::getMenuCheckin(
                    'checkin_to',
                    (isset($_SESSION['hotelcard']['checkin_to'])
                        ? $_SESSION['hotelcard']['checkin_to'] : '')),
            'TXT_HOTELCARD_CHECKOUT_FROM_TO' =>
                HotelCheckInOut::getMenuCheckout(
                    'checkout_from',
                    (isset($_SESSION['hotelcard']['checkout_from'])
                        ? $_SESSION['hotelcard']['checkout_from'] : '')).
                '&nbsp;-&nbsp;'.
                HotelCheckInOut::getMenuCheckout(
                    'checkout_to',
                    (isset($_SESSION['hotelcard']['checkout_to'])
                        ? $_SESSION['hotelcard']['checkout_to'] : '')),
            'TXT_HOTELCARD_FOUND_HOW' => Html::getTextarea('found_how',
                (isset($_SESSION['hotelcard']['found_how'])
                    ? $_SESSION['hotelcard']['found_how'] : '')),
        );
        foreach ($arrData as $placeholder => $element) {
            self::$objTemplate->setVariable(array(
                'HOTELCARD_DATA_LABEL' => $_ARRAYLANG[$placeholder],
                'HOTELCARD_DATA_INPUT' => $element,
            ));
            self::$objTemplate->parse('hotelcard_data');
        }
        self::$objTemplate->setVariable(array(
            'TXT_HOTELCARD_NOTE' => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_ADD_STEP3_NOTE'],
        ));
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

        // Verify the data already present
        if ($_SESSION['hotelcard']['step'] == 4) {
            $flagComplete = true;
            $mandatoryFields = array(
                'creditcard_id',
            );
            foreach ($mandatoryFields as $name) {
                if (empty($_SESSION['hotelcard'][$name])) {
echo("Step 4: missing mandatory field value for $name<br />");
                    $flagComplete = false;
                    self::addMessage($_ARRAYLANG['TXT_HOTELCARD_MISSING_'.strtoupper($name)]);
                }
            }
            if ($flagComplete) {
echo("Step 4 complete<br />");
                return true;
            }
        }
echo("Showing step 4<br />");
        $arrData = array(
            'TXT_HOTELCARD_CREDITCARD_ID' => Html::getCheckboxGroup(
                'creditcard_id',
                Creditcard::getNameArray(), Creditcard::getNameArray(),
                (isset($_SESSION['hotelcard']['creditcard_id'])
                    ? array_keys($_SESSION['hotelcard']['creditcard_id']) : '')),
        );
        foreach ($arrData as $placeholder => $element) {
            self::$objTemplate->setVariable(array(
                'HOTELCARD_DATA_LABEL' => $_ARRAYLANG[$placeholder],
                'HOTELCARD_DATA_INPUT' => $element,
            ));
            self::$objTemplate->parse('hotelcard_data');
        }
        self::$objTemplate->setVariable(array(
            'TXT_HOTELCARD_NOTE' => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_ADD_STEP4_NOTE'],
        ));
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

        // Verify the data already present
        if ($_SESSION['hotelcard']['step'] == 5) {
            $flagComplete = true;
            $mandatoryFields = array(
                'accountant_name',
                'accountant_gender',
                'accountant_phone',
                'accountant_fax',
                'accountant_email',
                'billing_company',
                'billing_address',
                'billing_zip',
                'billing_country_id',
                'billing_name',
                'billing_gender',
                'billing_tax_id',
            );
            foreach ($mandatoryFields as $name) {
                if (empty($_SESSION['hotelcard'][$name])) {
echo("Step 5: missing mandatory field value for $name<br />");
                    $flagComplete = false;
                    self::addMessage($_ARRAYLANG['TXT_HOTELCARD_MISSING_'.strtoupper($name)]);
                }
            }
            if ($flagComplete) {
echo("Step 5 complete<br />");
                return true;
            }
        }
echo("Showing step 5<br />");

        $arrData = array(
            'TXT_HOTELCARD_ACCOUNTANT_DETAILS' => '',
            'TXT_HOTELCARD_ACCOUNTANT_NAME' =>
                Html::getInputText('accountant_name',
                (isset($_SESSION['hotelcard']['accountant_name'])
                    ? $_SESSION['hotelcard']['accountant_name'] : '')),
            'TXT_HOTELCARD_ACCOUNTANT_GENDER' => Html::getRadioGroup(
                'accountant_gender',
                array(
                    'm' => $_ARRAYLANG['TXT_HOTELCARD_GENDER_MALE'],
                    'f' => $_ARRAYLANG['TXT_HOTELCARD_GENDER_FEMALE'],
                ),
                (isset($_SESSION['hotelcard']['accountant_gender'])
                    ? $_SESSION['hotelcard']['accountant_gender'] : '')
                ),
//Direkte Telefonnummer der Buchhaltung 	+
            'TXT_HOTELCARD_ACCOUNTANT_PHONE' =>
                Html::getInputText('accountant_phone',
                (isset($_SESSION['hotelcard']['accountant_phone'])
                    ? $_SESSION['hotelcard']['accountant_phone'] : '')),
//Direkte Faxnummer der Buchhaltung 	+
            'TXT_HOTELCARD_ACCOUNTANT_FAX' =>
                Html::getInputText('accountant_fax',
                (isset($_SESSION['hotelcard']['accountant_fax'])
                    ? $_SESSION['hotelcard']['accountant_fax'] : '')),
//Direkte E-Mail der Buchhaltung
            'TXT_HOTELCARD_ACCOUNTANT_EMAIL' =>
                Html::getInputText('accountant_email',
                (isset($_SESSION['hotelcard']['accountant_email'])
                    ? $_SESSION['hotelcard']['accountant_email'] : '')),
//*** Rechnungsdetails ***
            'TXT_HOTELCARD_BILLING_DETAILS' => '',
//Firmenname fÃ¼r die Rechnung
            'TXT_HOTELCARD_BILLING_COMPANY' =>
                Html::getInputText('billing_company',
                (isset($_SESSION['hotelcard']['billing_company'])
                    ? $_SESSION['hotelcard']['billing_company'] : '')),
//Rechnungsadresse
            'TXT_HOTELCARD_BILLING_ADDRESS' =>
                Html::getInputText('billing_address',
                (isset($_SESSION['hotelcard']['billing_address'])
                    ? $_SESSION['hotelcard']['billing_address'] : '')),
//Postleitzahl
            'TXT_HOTELCARD_BILLING_ZIP' =>
                Html::getInputText('billing_zip',
                (isset($_SESSION['hotelcard']['billing_zip'])
                    ? $_SESSION['hotelcard']['billing_zip'] : '')),
//Land
            'TXT_HOTELCARD_BILLING_COUNTRY_ID' => Html::getSelect(
                'billing_country_id', Country::getNameArray(),
                (isset($_SESSION['hotelcard']['billing_country_id'])
                    ? $_SESSION['hotelcard']['billing_country_id'] : '')),
//Kontaktperson fÃ¼r Rechnungen
            'TXT_HOTELCARD_BILLING_NAME' =>
                Html::getInputText('billing_name',
                (isset($_SESSION['hotelcard']['billing_name'])
                    ? $_SESSION['hotelcard']['billing_name'] : '')),
//Geschlecht : 		Herr
//Frau

            'TXT_HOTELCARD_BILLING_GENDER' => Html::getRadioGroup(
                'billing_gender',
                array(
                    'm' => $_ARRAYLANG['TXT_HOTELCARD_GENDER_MALE'],
                    'f' => $_ARRAYLANG['TXT_HOTELCARD_GENDER_FEMALE'],
                ),
                (isset($_SESSION['hotelcard']['billing_gender'])
                    ? $_SESSION['hotelcard']['billing_gender'] : '')
                ),
//Ust-ID Nummer der Firma (obligatorisch fÃ¼r EU LÃ¤nder)
            // sales tax identification number
            'TXT_HOTELCARD_BILLING_TAX_ID' =>
                Html::getInputText('billing_tax_id',
                (isset($_SESSION['hotelcard']['billing_tax_id'])
                    ? $_SESSION['hotelcard']['billing_tax_id'] : '')),

        );
        foreach ($arrData as $placeholder => $element) {
//echo("Language variable $placeholder => ".$_ARRAYLANG[$placeholder]."<br />");
            self::$objTemplate->setVariable(array(
                'HOTELCARD_DATA_LABEL' => $_ARRAYLANG[$placeholder],
                'HOTELCARD_DATA_INPUT' => $element,
            ));
            self::$objTemplate->parse('hotelcard_data');
        }
        self::$objTemplate->setVariable(array(
            'TXT_HOTELCARD_NOTE' => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_ADD_STEP5_NOTE'],
        ));
        return false; // Still running
    }


    /**
     * Shows the sixth step of the "Add Hotel" wizard
     *
     * If it still misses information, shows itself, and returns false.
     * Returns true if the information is complete.
     */
    static function addHotelStep6()
    {
        global $_ARRAYLANG;

        // Verify the data already present
        if ($_SESSION['hotelcard']['step'] == 6) {
            $flagComplete = true;
            $mandatoryFields = array(
                'hotel_description_de',
                'hotel_description_en',
                'hotel_facility_id',
            );
            foreach ($mandatoryFields as $name) {
                if (empty($_SESSION['hotelcard'][$name])) {
echo("Step 6: missing mandatory field value for $name<br />");
                    $flagComplete = false;
                    self::addMessage($_ARRAYLANG['TXT_HOTELCARD_MISSING_'.strtoupper($name)]);
                }
            }
            if ($flagComplete) {
echo("Step 6 complete<br />");
                return true;
            }
        }
echo("Showing step 6<br />");

        $arrData = array(
            'TXT_HOTELCARD_HOTEL_DESCRIPTION_DE' =>
                Html::getTextarea('hotel_description_de',
                (isset($_SESSION['hotelcard']['hotel_description_de'])
                    ? $_SESSION['hotelcard']['hotel_description_de'] : '')),
            'TXT_HOTELCARD_HOTEL_DESCRIPTION_EN' =>
                Html::getTextarea('hotel_description_en',
                (isset($_SESSION['hotelcard']['hotel_description_en'])
                    ? $_SESSION['hotelcard']['hotel_description_en'] : '')),
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

// TODO: write getGroupNameArray()
        $arrFacilityGroups = HotelFacility::getGroupArray();
        foreach ($arrFacilityGroups as $group_id => $arrFacilityGroup) {
            $arrFacilities = HotelFacility::getFacilityNameArray($group_id);
//            foreach ($arrFacilities as $facility_id => $facility_name) {
// TODO: Limit the number of facilities shown here.
            self::$objTemplate->setVariable(array(
                'HOTELCARD_DATA_LABEL' => $arrFacilityGroup['name'],
                'HOTELCARD_DATA_INPUT' => Html::getCheckboxGroup(
                    'hotel_facility_id',
                    $arrFacilities, $arrFacilities,
                    (   isset($_SESSION['hotelcard']['hotel_facility_id'])
                      ? array_keys($_SESSION['hotelcard']['hotel_facility_id'])
                      : '')
                ),
            ));

            self::$objTemplate->parse('hotelcard_data');
//            }
        }

        self::$objTemplate->setVariable(array(
            'TXT_HOTELCARD_NOTE' => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_ADD_STEP6_NOTE'],
        ));
        return false; // Still running
    }


    /**
     * Shows the seventh step of the "Add Hotel" wizard
     *
     * If it still misses information, shows itself, and returns false.
     * Returns true if the information is complete.
     */
    static function addHotelStep7()
    {
        global $_ARRAYLANG;

        // Verify the data already present
        if ($_SESSION['hotelcard']['step'] == 7) {
            $flagComplete = true;
            $mandatoryFields = array(
                // Note: These are checkbox groups and are thus posted as
                // arrays, like 'room_type[0]'
                'room_type',
                'room_available',
                'room_price',
// Not mandatory:
//                'room_facilities',
            );
            foreach ($mandatoryFields as $name) {
//echo("Step 7: checking mandatory field $name: ".(empty($_SESSION['hotelcard'][$name][1]) ?  '-- empty --' : $_SESSION['hotelcard'][$name][1])."<br />");
//                $name_stripped = preg_replace('/\[.*$/', '', $name);
                // Verify the first index, only one is mandatory
                if (empty($_SESSION['hotelcard'][$name][1])) {
echo("Step 7: missing mandatory field value for $name<br />");
                    $flagComplete = false;
                    self::addMessage($_ARRAYLANG['TXT_HOTELCARD_MISSING_'.strtoupper($name)]);
                }
            }
            if ($flagComplete) {
echo("Step 7 complete<br />");
                return true;
            }
        }
echo("Showing step 7<br />");

        // Show room type form content four times
        for ($i = 1; $i <= 4; ++$i) {
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
                    HotelRoom::getFixtureNameArray(),
                    HotelRoom::getFixtureNameArray(),
                    (isset($_SESSION['hotelcard']['room_facility_id'][$i])
                        ? $_SESSION['hotelcard']['room_facility_id'][$i] : '')),
            );
            self::$objTemplate->setVariable(array(
                'HOTELCARD_DATA_LABEL' =>
                    sprintf($_ARRAYLANG['TXT_HOTELCARD_ROOM_TYPE_NUMBER'], $i),
                'HOTELCARD_DATA_INPUT' => '',
            ));
            self::$objTemplate->parse('hotelcard_data');
            foreach ($arrData as $placeholder => $element) {
//echo("Language variable $placeholder => ".$_ARRAYLANG[$placeholder]."<br />");
                self::$objTemplate->setVariable(array(
                    'HOTELCARD_DATA_LABEL' => $_ARRAYLANG[$placeholder],
                    'HOTELCARD_DATA_INPUT' => $element,
                ));
                self::$objTemplate->parse('hotelcard_data');
            }
        }
        self::$objTemplate->setVariable(array(
            'TXT_HOTELCARD_NOTE' => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_ADD_STEP7_NOTE'],
        ));
        return false; // Still running
    }


    /**
     * Shows the eighth step of the "Add Hotel" wizard
     *
     * If it still misses information, shows itself, and returns false.
     * Returns true if the information is complete.
     */
    static function addHotelStep8()
    {
        global $_ARRAYLANG;

        // Verify the data already present
        if ($_SESSION['hotelcard']['step'] == 8) {
            $flagComplete = true;
            $mandatoryFields = array(
                'room_type',
            );
            foreach ($mandatoryFields as $name) {
                if (empty($_SESSION['hotelcard'][$name])) {
echo("Step 8: missing mandatory field value for $name<br />");
                    $flagComplete = false;
                    self::addMessage($_ARRAYLANG['TXT_HOTELCARD_MISSING_'.strtoupper($name)]);
                }
            }
            if ($flagComplete) {
echo("Step 8 complete<br />");
                return true;
            }
        }
echo("Showing step 8<br />");

        $arrData = array();
        foreach ($_SESSION['hotelcard'] as $name => $value) {
            // Skip fields that are irrelevant
            if (preg_match('/confirm_terms$/', $name)) continue;

            // Fix values that are IDs, special, or arrays of anything
            if (preg_match('/country_id$/', $name))
                $value = Country::getNameById($value);
            elseif (preg_match('/region_id$/', $name))
                $value = Region::getNameById($value);
            elseif (preg_match('/accomodation_type_id$/', $name))
                $value = HotelAccomodationType::getNameById($value);
            elseif (preg_match('/creditcard_id$/', $name))
                $value = join(', ', array_map(array('Creditcard', 'getNameById'), $value));
            elseif (preg_match('/lang_id$/', $name))
                $value = FWLanguage::getLanguageParameter($value, 'name');
            elseif (preg_match('/gender$/', $name))
                $value = ($value == 'm'
                  ? $_ARRAYLANG['TXT_HOTELCARD_GENDER_MALE']
                  : $_ARRAYLANG['TXT_HOTELCARD_GENDER_FEMALE']);
            elseif (preg_match('/rating$/', $name))
                $value = HotelRating::getString($value);

            $name = 'TXT_HOTELCARD_'.strtoupper($name);
            if (empty($_ARRAYLANG[$name])) {
echo("WARNING: Missing language entry for $name<br />");
                continue;
            }

            $arrData[$name] = $value;
        }
        foreach ($arrData as $placeholder => $element) {
//echo("Language variable $placeholder => ".$_ARRAYLANG[$placeholder]."<br />");
            self::$objTemplate->setVariable(array(
                'HOTELCARD_DATA_LABEL' => $_ARRAYLANG[$placeholder],
                'HOTELCARD_DATA_INPUT' => $element,
            ));
            self::$objTemplate->parse('hotelcard_data');
        }

        self::$objTemplate->setVariable(array(
            'TXT_HOTELCARD_NOTE' => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_ADD_STEP8_NOTE'],
        ));
        return false; // Still running
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
     * @param   string  $strMessage       The message to add
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function addMessage($strMessage)
    {
        self::$statusMessage .=
            (self::$statusMessage ? '<br />' : '').
            $strMessage;
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
