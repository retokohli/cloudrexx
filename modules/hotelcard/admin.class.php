<?php

define('_HOTELCARD_DEBUG', 0);

//echo(nl2br(htmlentities(var_export($_SERVER, true)))."<br />");

/**
 * Class Hotelcard
 *
 * Administration of the Hotelcard module
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.2.0
 * @package     contrexx
 * @subpackage  module_hotelcard
 */

/** @ignore */
require_once ASCMS_CORE_PATH.'/Filetype.class.php';
require_once ASCMS_CORE_PATH.'/Imagetype.class.php';
require_once ASCMS_CORE_PATH.'/Image.class.php';
require_once ASCMS_CORE_PATH.'/MailTemplate.class.php';
require_once ASCMS_CORE_PATH.'/SettingDb.class.php';
require_once ASCMS_CORE_PATH.'/Sorting.class.php';
require_once ASCMS_CORE_PATH.'/Text.class.php';
require_once ASCMS_FRAMEWORK_PATH.'/Language.class.php';
require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Hotel.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Download.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Product.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Property.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Reference.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Image.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Text.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/RelProductCategory.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/RelProductProperty.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/RelProductReference.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/RelUserContact.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/constants.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/sorting.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Material.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Manufacturer.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Line.class.php';

/**
 * Class Hotelcard
 *
 * Administration of the Hotelcard module
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.2.0
 * @package     contrexx
 * @subpackage  module_hotelcard
 */
class Hotelcard
{
    /**
     * Mail template key for the hotel registration confirmation
     */
    const MAILTEMPLATE_HOTEL_REGISTRATION_CONFIRMATION =
        'hotelcard_hotel_registration_confirmation';

    /**
     * Page title
     * @var     string
     * @static
     * @access  private
     */
    private static $pageTitle = '';
    /**
     * Success notice
     * @var     string
     * @static
     * @access  private
     */
    private static $strOkMessage = '';
    /**
     * Failure notice
     * @var     string
     * @static
     * @access  private
     */
    private static $strErrMessage = '';
    /**
     * Sigma Template
     * @var     HTML_Template_Sigma
     * @static
     * @access  private
     */
    private static $objTemplate = null;
    /**
     * Base page URI with cmd, act, and id parameters
     * @var     string
     * @static
     * @access  private
     */
    private static $uriBase;


    /**
     * Initialize
     * @access  public
     */
    function init()
    {
    }


    /**
     * Set up the selected administration page
     */
    function getPage()
    {
        global $objTemplate, $_ARRAYLANG;

        // Sigma template
        self::$objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/hotelcard/template');
        CSRF::add_placeholder(self::$objTemplate);
        self::$objTemplate->setErrorHandling(PEAR_ERROR_DIE);

if (_HOTELCARD_DEBUG) {
    DBG::enable_firephp();
    if (_HOTELCARD_DEBUG & 1) DBG::enable_error_reporting();
    if (_HOTELCARD_DEBUG & 2) DBG::enable_adodb_debug();;
DBG::log('debug enabled');
}

        $cmd = (isset($_GET['cmd']) ? $_GET['cmd'] : '');
        $act = (isset($_GET['act']) ? $_GET['act'] : '');
//        $tpl = (isset($_GET['tpl']) ? $_GET['tpl'] : '');

        // Used for setting up the sorting headers and others
        self::$uriBase =
            CONTREXX_DIRECTORY_INDEX.'?cmd='.$cmd.
            (empty($act) ? '' : '&amp;act='.$act);
//echo("URI base is ".self::$uriBase."<br />");
        self::$objTemplate->setGlobalVariable('MODULE_URI_BASE', self::$uriBase);

        $result = true;
        $subnavigation = '';
        switch ($act) {
            case 'settings':
                $result &= self::settings();
                break;
            case 'mailtemplate_overview':
                $result &= self::mailtemplate_overview();
                break;
            case 'mailtemplate_edit':
                $result &= self::mailtemplate_edit();
                break;
            case 'hotels':
                $subnavigation =
                    '<a href="index.php?cmd=hotelcard&amp;act=hotels&amp;tpl=new">'.
                    $_ARRAYLANG['TXT_HOTELCARD_HOTEL_ADD'].'</a>';
                $result &= self::hotels();
                break;
            case 'add_hotel':
            case 'overview':
            default:
                $result &= self::overview();
                break;
        }

        $result &= (self::$strErrMessage == '');
        if (!$result) {
            self::errorHandler();
            self::addError('An error has occurred.  Please try reloading the page.');
//die("ERROR building page, code dfsgljbew<br />");
        }

//        $objTemplate->setGlobalVariable(array(
//            'MODULE_URI_BASE' => self::$uriBase,
//        ));
        $objTemplate->setVariable(array(
            'CONTENT_TITLE'          => self::$pageTitle,
            'CONTENT_OK_MESSAGE'     => self::$strOkMessage,
            'CONTENT_STATUS_MESSAGE' => self::$strErrMessage,
            'ADMIN_CONTENT'          => self::$objTemplate->get(),
            'CONTENT_NAVIGATION'     =>
                '<a href="index.php?cmd=hotelcard">'.$_ARRAYLANG['TXT_HOTELCARD_OVERVIEW'].'</a>'.
                '<a href="index.php?cmd=hotelcard&amp;act=hotels">'.$_ARRAYLANG['TXT_HOTELCARD_HOTELS'].'</a>'.
//                '<a href="index.php?cmd=hotelcard&amp;act=reservations">'.$_ARRAYLANG['TXT_HOTELCARD_RESERVATIONS'].'</a>'.
                '<a href="index.php?cmd=hotelcard&amp;act=settings">'.$_ARRAYLANG['TXT_HOTELCARD_SETTINGS'].'</a>'.
                '<a href="index.php?cmd=hotelcard&amp;act=mailtemplate_overview">'.$_ARRAYLANG['TXT_HOTELCARD_MAILTEMPLATE'].'</a>',
            'CONTENT_SUBNAVIGATION' => $subnavigation,
        ));
        return true;
    }


    /**
     * Set up the page with a list of all Settings
     *
     * Stores the settings if requested to.
     * @return  boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function settings()
    {
        global $_ARRAYLANG;

        if (isset($_POST['store'])) self::storeSettings();

        self::$pageTitle = $_ARRAYLANG['TXT_HOTELCARD_SETTINGS'];
        if (!self::$objTemplate->loadTemplateFile('settings.html', true, true))
            die("Failed to load template settings.html");
        self::$objTemplate->setGlobalVariable('MODULE_URI_BASE', self::$uriBase);

        // *MUST* reinitialise after storing!
        SettingDb::init('admin');
        $result = true && SettingDb::show(
            self::$objTemplate,
            $_ARRAYLANG['TXT_HOTELCARD_SETTING_SECTION_ADMIN'],
            'TXT_HOTELCARD_SETTING_'
        );
        SettingDb::init('frontend');
        $result &= SettingDb::show(
            self::$objTemplate,
            $_ARRAYLANG['TXT_HOTELCARD_SETTING_SECTION_FRONTEND'],
            'TXT_HOTELCARD_SETTING_'
        );
        SettingDb::init('backend');
        $result &= SettingDb::show(
            self::$objTemplate,
            $_ARRAYLANG['TXT_HOTELCARD_SETTING_SECTION_BACKEND'],
            'TXT_HOTELCARD_SETTING_'
        );
        return $result;
    }


    /**
     * Store changes to the list of Settings in the database.
     *
     * Returns the empty string if there is nothing to be stored
     * (no or an empty array has been posted).
     * If no setting has changed, true is returned.
     * @return  mixed       True on success, false on failure,
     *                      the empty string otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function storeSettings()
    {
        global $_ARRAYLANG;

//        if (empty($_POST['hotelcard']) || !is_array($_POST['hotelcard'])) return '';

        // Compare POST with current settings.
        // Only store what was changed.
        SettingDb::init();
        unset($_POST['store']);
        foreach ($_POST as $name => $value) {
//echo("Updating $name to $value (");
            $value = contrexx_stripslashes($value);
            SettingDb::set($name, $value);
        }
        $result = SettingDb::updateAll();
        // No changes detected
        if ($result === '') return true;
        if ($result) {
            self::addMessage($_ARRAYLANG['TXT_HOTELCARD_SETTING_STORED_SUCCESSFULLY']);
            return true;
        }
        self::addError($_ARRAYLANG['TXT_HOTELCARD_ERROR_SETTING_NOT_STORED']);
        return false;
    }


    /**
     * Set up the page with a list of hotels
     *
     * The hotels visible here are determined by various filter parameters
     * and (the rights of) the current user.
     * @todo    Define what content is to be shown here
     * @return  boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function hotels()
    {
        global $_ARRAYLANG;

        self::$pageTitle = $_ARRAYLANG['TXT_HOTELCARD_HOTELS'];
        if (!self::$objTemplate->loadTemplateFile('hotels.html', true, true))
            die("Failed to load template settings.html");
        self::$objTemplate->setGlobalVariable('MODULE_URI_BASE', self::$uriBase);

// TODO

        return true;
    }


    /**
     * Set up the overview page
     * @todo    Define what content is to be shown here
     * @return  boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function overview()
    {
        global $_ARRAYLANG;

        self::$pageTitle = $_ARRAYLANG['TXT_HOTELCARD_OVERVIEW'];
        if (!self::$objTemplate->loadTemplateFile('overview.html', true, true))
            die("Failed to load template settings.html");
        self::$objTemplate->setGlobalVariable('MODULE_URI_BASE', self::$uriBase);

        $objSorting = new Sorting(
            self::$uriBase,
            array(
                'id', 'hotel_name',
            ),
            array(
                $_ARRAYLANG['TXT_HOTELCARD_HOTEL_ID'],
                $_ARRAYLANG['TXT_HOTELCARD_HOTEL_NAME'],
            ),
            false
        );
        // Number of matching hotels total, passed by reference
        $count = 0;
        $order = $objSorting->getOrder();
        $filter = array(
            'term' => (isset($_POST['term']) ? $_POST['term'] : ''),
            'id' => (isset($_REQUEST['hotel_id']) ? $_REQUEST['hotel_id'] : ''),
            'accomodation_type_id' => (isset($_REQUEST['accomodation_type_id']) ? $_REQUEST['accomodation_type_id'] : ''),
            'lang_id' => (isset($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : ''),
            'rating' => (isset($_REQUEST['rating']) ? $_REQUEST['rating'] : ''),
            'recommended' => (isset($_REQUEST['recommended']) ? $_REQUEST['recommended'] : ''),
            'hotel_location' => (isset($_REQUEST['hotel_location']) ? $_REQUEST['hotel_location'] : ''),
            'hotel_region' => (isset($_REQUEST['hotel_region']) ? $_REQUEST['hotel_region'] : ''),
// TODO:  More to come...  maybe
        );
        $offset = Paging::getPosition();
        $limit = SettingDb::getValue('hotel_per_page_backend');
        $arrHotels = Hotel::getArray($count, $order, $filter, $offset, $limit);
        $last_registration_time = Hotel::getLastRegistrationDate();

        self::$objTemplate->setGlobalVariable(array(
            'TXT_HOTELCARD_NAME' => $_ARRAYLANG['TXT_HOTELCARD_NAME'],
            'TXT_HOTELCARD_VALUE' => $_ARRAYLANG['TXT_HOTELCARD_VALUE'],
            'TXT_HOTELCARD_HOTEL_ID' => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_ID'],
            'TXT_HOTELCARD_HOTEL_NAME' => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_NAME'],
            'TXT_HOTELCARD_HOTEL_INFO' => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_INFO'],
            'TXT_HOTELCARD_FUNCTIONS' => $_ARRAYLANG['TXT_HOTELCARD_FUNCTIONS'],
        ));
        $arrOverview = array(
            'HOTEL_NUMOF' => $count,
            'HOTEL_LAST_REGISTRATION' => $last_registration_time
        );
        $i = 0;
        foreach ($arrOverview as $name => $value) {
/*
'hotelcard_section'
            'HOTELCARD_SECTION'
*/
            self::$objTemplate->setVariable(array(
                'HOTELCARD_ROWCLASS' => (++$i % 2) + 1,
                'HOTELCARD_NAME'     => $_ARRAYLANG['TXT_HOTELCARD_'.$name],
                'HOTELCARD_VALUE'    => $value,
            ));
            self::$objTemplate->parse('hotelcard_row');
        }
        foreach ($arrHotels as $objHotel) {
            self::$objTemplate->setVariable(array(
                'HOTELCARD_ROWCLASS'   => (++$i % 2) + 1,
                'HOTELCARD_HOTEL_ID'   => $objHotel->getFieldvalue('id'),
                'HOTELCARD_HOTEL_NAME' => $objHotel->getFieldvalue('hotel_name'),
// TODO: Compose some useful abstract
                'HOTELCARD_HOTEL_INFO' => $objHotel->getFieldvalue('hotel_uri'),
// TODO
                'HOTELCARD_FUNCTIONS'  => '',
            ));
            self::$objTemplate->parse('hotelcard_hotel_row');
        }
        return true;
    }


    /**
     * Set up the Mailtemplate overview page
     * @return  boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function mailtemplate_overview()
    {
        global $_ARRAYLANG;

        self::$pageTitle = $_ARRAYLANG['TXT_HOTELCARD_MAILTEMPLATE_OVERVIEW'];
        SettingDb::init();
        self::$objTemplate = MailTemplate::overview(
            SettingDb::getValue('mailtemplate_per_page_backend')
        );
        return (bool)self::$objTemplate;
    }


    /**
     * Set up the Mailtemplate edit page
     * @return  boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function mailtemplate_edit()
    {
        global $_ARRAYLANG;

        self::$pageTitle = $_ARRAYLANG['TXT_HOTELCARD_MAILTEMPLATE_EDIT'];
        self::$objTemplate = MailTemplate::edit();
        return (bool)self::$objTemplate;
    }


    /**
     * Adds the string $strErrorMessage to the error messages.
     *
     * If necessary, inserts a line break tag (<br />) between
     * error messages.
     * @param   string  $strErrorMessage    The error message to add
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function addError($strErrorMessage)
    {
        self::$strErrMessage .=
            (self::$strErrMessage != '' && $strErrorMessage != ''
                ? '<br />' : ''
            ).$strErrorMessage;
    }


    /**
     * Adds the string $strOkMessage to the success messages.
     *
     * If necessary, inserts a line break tag (<br />) between
     * messages.
     * @param   string  $strOkMessage       The message to add
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function addMessage($strOkMessage)
    {
        self::$strOkMessage .=
            (self::$strOkMessage != '' && $strOkMessage != ''
                ? '<br />' : ''
            ).$strOkMessage;
    }


    /**
     * Handle any error occurring in this class.
     *
     * Tries to fix known problems with the database.
     * @global  mixed     $objDatabase    Database object
     * @return  boolean                   False.  Always.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function errorHandler()
    {
        global $objDatabase;

        // Verify that the module is installed
        $query = "
            SELECT 1
              FROM ".DBPREFIX."modules
             WHERE name='hotelcard'
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
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
            if (!$objResult) return false;
        }

        // Verify that the backend area is present
        $query = "
            SELECT 1
              FROM ".DBPREFIX."backend_areas
             WHERE uri='index.php?cmd=hotelcard'
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
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
            if (!$objResult) return false;
        }

DBG::log("Hotelcard::errorHandler(): Settings init()<br />");
        // Add missing settings
        SettingDb::init();

DBG::log("Hotelcard::errorHandler(): Settings delete, module ID ".MODULE_ID."<br />");
// To reset the default settings, enable
//        SettingDb::deleteModule();

DBG::log("Hotelcard::errorHandler(): Settings add<br />");
        SettingDb::add('admin_email', 'info@hotelcard.ch', 101, 'email', '', 'admin');
        SettingDb::add('hotel_minimum_rooms_days', 180, 201, 'text', '', 'admin');
        SettingDb::add('user_profile_attribute_hotel_id', '', 301, 'dropdown_user_custom_attribute', '', 'admin');
        SettingDb::add('hotel_usergroup', '', 401, 'dropdown_usergroup', '', 'admin');

        SettingDb::add('hotel_per_page_frontend', '10', 1, 'text', '', 'frontend');
        SettingDb::add('hotel_default_order_frontend', 'price DESC', 2, 'text', '', 'frontend');
        SettingDb::add('hotel_max_pictures', '3', 3, 'text', '', 'frontend');
        SettingDb::add('terms_and_conditions_1', '[AGB hier]', 6, 'wysiwyg', '', 'frontend');
        SettingDb::add('terms_and_conditions_2', '[Terms and Conditions here]', 7, 'wysiwyg', '', 'frontend');
        SettingDb::add('terms_and_conditions_3', '[AGB ici]', 8, 'wysiwyg', '', 'frontend');
        SettingDb::add('terms_and_conditions_4', '[AGB qui]', 9, 'wysiwyg', '', 'frontend');

        SettingDb::add('hotel_per_page_backend', '10', 1, 'text', '', 'backend');
        SettingDb::add('mailtemplate_per_page_backend', '10', 2, 'text', '', 'backend');
        SettingDb::add('hotel_default_order_backend', 'name ASC', 3, 'text', '', 'backend');

        // Add mail templates.  Uses the current FRONTEND_LANG_ID
        /*
<admin_email>
<contact_email>
<contact_name>
<hotel_name>
<registration_time>
<username>
<password>
        */
DBG::log("Hotelcard::errorHandler(): Mail templates delete, key ".self::MAILTEMPLATE_HOTEL_REGISTRATION_CONFIRMATION."<br />");
        MailTemplate::deleteTemplateByKey(self::MAILTEMPLATE_HOTEL_REGISTRATION_CONFIRMATION);
DBG::log("Hotelcard::errorHandler(): Mail templates add<br />");
        MailTemplate::storeTemplate(array(
            'key'     => Hotelcard::MAILTEMPLATE_HOTEL_REGISTRATION_CONFIRMATION,
            'name'    => 'Bestätigung über die erfolgreiche Registration des Hotels',
            'from'    => 'info@hotelcard.ch',
            'to'      => '<contact_email>',
            'bcc'     => '<admin_email>',
            'sender'  => 'Hotelcard Registration',
            'subject' => 'Danke für Ihre Registrierung auf hotelcard.ch',
            'message' =>
                "<contact_salutation> <contact_name>,\n\n\n".
                "Sie haben das Hotel <hotel_name> am <registration_time> registriert.\n\n".
                "Bitte bewahren Sie diese Nachricht auf und geben Sie Ihre Registrations ID <hotel_id> an, wenn Sie mit uns Kontakt aufnehmen.\n\n\n".
                "Bearbeiten Sie Ihre Hotel- und Zimmerdaten unter http://www.hotelcard.ch/index.php?section=hotelcard&cmd=edit_hotel\n\n".
                "Die Zugangsdaten für Ihr hotelcard.ch Benutzerkonto:\n\n".
                "Benutzername: <username>\n".
                "Passwort: <password>\n\n".
                "Diese Bestätigung wurde gesendet an <contact_email>\n\n\n".
                "Freundliche Grüsse\n\n".
                "Das hotelcard.ch Team\n".
                "http://www.hotelcard.ch/",

        ));

        // Always!
        return false;
    }

}

Hotelcard::init();

?>
