<?php

//define('_DEBUG', DBG_LOG_FIREPHP | DBG_PHP | DBG_ADODB);
//DBG::__internal__setup();//DBG::log("Debugging");
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
require_once 'lib/HotelcardLibrary.class.php';
require_once 'lib/Hotel.class.php';
require_once 'lib/HotelRating.class.php';

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

//if (_HOTELCARD_DEBUG) {
//    DBG::enable_firephp();
//    if (_HOTELCARD_DEBUG & 1) DBG::enable_error_reporting();
//    if (_HOTELCARD_DEBUG & 2) DBG::enable_adodb_debug();;
//    DBG::log('debug enabled');
//}

        $cmd = (isset($_GET['cmd']) ? $_GET['cmd'] : '');
        $act = (isset($_GET['act']) ? $_GET['act'] : '');
//        $tpl = (isset($_GET['tpl']) ? $_GET['tpl'] : '');

        // Used for setting up the sorting headers and others
        self::$uriBase =
            CONTREXX_DIRECTORY_INDEX.'?cmd='.$cmd; //.(empty($act) ? '' : '&amp;act='.$act);

        $result = true;
        $subnavigation = '';
        switch ($act) {
            case 'settings':
                $result &= self::settings();
                break;

            case 'mailtemplate_delete':
                if (!empty($_REQUEST['key'])) {
                    $result &= MailTemplate::deleteTemplate($_REQUEST['key']);
                }
                // No break on purpose!
            case 'mailtemplate_overview':
                $result &= self::mailtemplate_overview();
                break;
            case 'mailtemplate_edit':
                $result &= self::mailtemplate_edit();
                break;

            case 'imagetypes_edit':
                $result &= self::imagetypes_edit();
                break;

            case 'hotel_overview':
// Neither implemented nor needed
//                $subnavigation =
//                    '<a href="index.php?cmd=hotelcard&amp;act=hotels&amp;tpl=new">'.
//                    $_ARRAYLANG['TXT_HOTELCARD_HOTEL_ADD'].'</a>';
                $result &= self::hotel_overview();
                break;
            case 'hotel_view':
                $result &= self::hotel_view();
                break;

            // Ajax stuff
            case 'get_matching_location':
                Location::getMatching(
                    (isset($_REQUEST['location']) ? $_REQUEST['location'] : ''),
                    (isset($_REQUEST['state'])    ? $_REQUEST['state']    : ''));

            case 'overview':
            default:
                $result &= self::overview();
                break;
        }

//echo("URI base is ".self::$uriBase."<br />");
        self::$objTemplate->setGlobalVariable('MODULE_URI_BASE', self::$uriBase);

        $result &= (self::$strErrMessage == '');
        if (!$result) {
//            self::errorHandler();
            self::addError('An error has occurred.  Please try reloading the page.');
//die("ERROR building page, code dfsgljbew<br />");
        }

//        $objTemplate->setGlobalVariable(array(
//            'MODULE_URI_BASE' => self::$uriBase,
//        ));
        $objTemplate->setVariable(array(
            'CONTENT_TITLE'          => self::$pageTitle,
            'ADMIN_CONTENT'          => self::$objTemplate->get(),
            'CONTENT_NAVIGATION'     =>
                '<a href="index.php?cmd=hotelcard">'.$_ARRAYLANG['TXT_HOTELCARD_OVERVIEW'].'</a>'.
// TODO: Not yet implemented
//                '<a href="index.php?cmd=hotelcard&amp;act=hotels">'.$_ARRAYLANG['TXT_HOTELCARD_HOTELS'].'</a>'.
//                '<a href="index.php?cmd=hotelcard&amp;act=reservations">'.$_ARRAYLANG['TXT_HOTELCARD_RESERVATIONS'].'</a>'.
                '<a href="index.php?cmd=hotelcard&amp;act=mailtemplate_overview">'.$_ARRAYLANG['TXT_HOTELCARD_MAILTEMPLATES'].'</a>'.
                '<a href="index.php?cmd=hotelcard&amp;act=imagetypes_edit">'.$_ARRAYLANG['TXT_HOTELCARD_IMAGETYPES'].'</a>'.
                '<a href="index.php?cmd=hotelcard&amp;act=settings">'.$_ARRAYLANG['TXT_HOTELCARD_SETTINGS'].'</a>',
            'CONTENT_SUBNAVIGATION' => $subnavigation,
        ));
        if (self::$strOkMessage)
            $objTemplate->setVariable(
                'CONTENT_OK_MESSAGE', self::$strOkMessage);
        if (self::$strErrMessage)
            $objTemplate->setVariable(
                'CONTENT_STATUS_MESSAGE', self::$strErrMessage);
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

        if (isset($_POST['bsubmit'])) self::storeSettings();

        self::$pageTitle = $_ARRAYLANG['TXT_HOTELCARD_SETTINGS'];
        // *MUST* reinitialise after storing!
        SettingDb::init('frontend');
        $result = true;
        $result &= SettingDb::show(
            self::$objTemplate,
            self::$uriBase,
            $_ARRAYLANG['TXT_HOTELCARD_SETTING_SECTION_FRONTEND'],
            $_ARRAYLANG['TXT_HOTELCARD_SETTING_SECTION_FRONTEND'],
            'TXT_HOTELCARD_SETTING_'
        );
        SettingDb::init('backend');
        $result &= SettingDb::show(
            self::$objTemplate,
            self::$uriBase,
            $_ARRAYLANG['TXT_HOTELCARD_SETTING_SECTION_BACKEND'],
            $_ARRAYLANG['TXT_HOTELCARD_SETTING_SECTION_BACKEND'],
            'TXT_HOTELCARD_SETTING_'
        );
        SettingDb::init('admin');
        $result = true && SettingDb::show(
            self::$objTemplate,
            self::$uriBase,
            $_ARRAYLANG['TXT_HOTELCARD_SETTING_SECTION_ADMIN'],
            $_ARRAYLANG['TXT_HOTELCARD_SETTING_SECTION_ADMIN'],
            'TXT_HOTELCARD_SETTING_'
        );
        SettingDb::init('terms');
        $result &= SettingDb::show(
            self::$objTemplate,
            self::$uriBase,
            $_ARRAYLANG['TXT_HOTELCARD_SETTING_SECTION_TERMS'],
            $_ARRAYLANG['TXT_HOTELCARD_SETTING_SECTION_TERMS'],
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

        $result = SettingDb::storeFromPost();
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
     * Set up the overview page
     * @todo    Define what content is to be shown here
     * @return  boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function overview()
    {
        global $_ARRAYLANG;

        self::hotel_overview(true);
        $count = Hotel::getCount();
        $last_registration_time = Hotel::getLastRegistrationDate();
DBG::log("Last registration time: $last_registration_time");
        $arrOverview = array(
            'HOTEL_NUMOF' => $count,
            'HOTEL_LAST_REGISTRATION' => ($last_registration_time
                ? date(ASCMS_DATE_FILE_FORMAT, $last_registration_time) : '-'),
        );
        $i = 0;
        foreach ($arrOverview as $name => $value) {
            self::$objTemplate->setVariable(array(
                'HOTELCARD_ROWCLASS' => (++$i % 2) + 1,
                'HOTELCARD_NAME'     => $_ARRAYLANG['TXT_HOTELCARD_'.$name],
                'HOTELCARD_VALUE'    => $value,
            ));
            self::$objTemplate->parse('hotelcard_row');
        }
        return true;
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
    function hotel_overview($last_registered=false)
    {
        global $_ARRAYLANG;

        self::$pageTitle = $_ARRAYLANG['TXT_HOTELCARD_HOTELS'];
        if (!self::$objTemplate->loadTemplateFile('hotel_overview.html', true, true))
            die("Failed to load template hotel_overview.html");

        // Set up filters and search
        $filter = self::setupFilterAndSearch();
        if ($last_registered) {
            $filter = false;
        }

        // List matching Hotels
        SettingDb::init();
        $arrSorting = array();
        foreach (Hotel::$arrSortfields as $field_name) {
            $arrSorting[$field_name] =
                $_ARRAYLANG['TXT_HOTELCARD_'.strtoupper($field_name)];
        }
        $uri = self::$uriBase.'&amp;act=hotel_overview';
//echo("Local URI: ".htmlentities($uri)."<br />");
        $objSorting = new Sorting(
            $uri,
            array_keys($arrSorting), $arrSorting,
            false, 'hotel_order'
        );
        // Number of matching hotels total, passed by reference
        $count = 0;
        $order = ($last_registered
            ? '`registration_time` DESC' : $objSorting->getOrder());
        $offset = Paging::getPosition();
        $limit = SettingDb::getValue('hotel_per_page_backend');
        $arrHotels = Hotel::getArray($count, $order, $filter, $offset, $limit);
        // If the paging offset is too large, there may be no results.
        if (empty($arrHotels)) {
            Paging::reset();
            $arrHotels = Hotel::getArray($count, $order, $filter, 0, $limit);
        }

        if (empty($arrHotels)) {
            self::$objTemplate->setVariable(array(
                'HOTELCARD_ROWCLASS'   => 1,
                'HOTELCARD_HOTEL_NAME' =>
                    $_ARRAYLANG['TXT_HOTELCARD_NO_RESULTS'],
            ));
        } else {
            $i = 0;
            foreach ($arrHotels as $objHotel) {
                $hotel_id = $objHotel->getFieldvalue('id');
                $status = $objHotel->getFieldvalue('status');
                $status_color =
                    (   $status == Hotel::STATUS_VERIFIED
                      ? 'green'
                      :
                    (   $status == Hotel::STATUS_ACCOUNT
                     || $status == Hotel::STATUS_CONFIRMED
                      ? 'yellow'
                      : 'red'
                    ));
                self::$objTemplate->setVariable(array(
                    'HOTELCARD_ROWCLASS'       => (++$i % 2) + 1,
                    'HOTELCARD_HOTEL_ID'       => $hotel_id,
                    'HOTELCARD_HOTEL_NAME'     =>
                        $objHotel->getFieldvalue('hotel_name'),
                    'HOTELCARD_CONTACT_NAME'   =>
                        $objHotel->getFieldvalue('contact_name'),
                    'HOTELCARD_HOTEL_LOCATION' =>
                        $objHotel->getFieldvalue('hotel_location').' '.
                        Location::getCityByZip(
                            $objHotel->getFieldvalue('hotel_location'), 204
                        ),
                    'HOTELCARD_HOTEL_REGION'   =>
                        $objHotel->getFieldvalue('hotel_region'),
                    'HOTELCARD_HOTEL_STATUS'   =>
                        Html::getLed($status_color,
                        $_ARRAYLANG['TXT_HOTELCARD_HOTEL_STATUS_'.$status]),
                    'HOTELCARD_FUNCTIONS'      => Html::getBackendFunctions(
                        array(
                            'view' => 'act=hotel_view&amp;hotel_id='.$hotel_id,
                        )),
                ));
                self::$objTemplate->parse('hotelcard_hotel_row');
            }
        }

//echo("Query string: ".var_export($_SERVER['QUERY_STRING'], true)."<br />");
        self::$objTemplate->setGlobalVariable(
            $_ARRAYLANG
          + array(
            'MODULE_URI_BASE' => self::$uriBase,
            'TXT_HOTELCARD_MATCHING_HOTELS_COUNT' => ($last_registered
                ? $_ARRAYLANG['TXT_HOTELCARD_HOTELS_RECENT']
                : sprintf($_ARRAYLANG['TXT_HOTELCARD_MATCHING_HOTELS'], $count)),
            'HOTELCARD_HEADING_ID' => $objSorting->getHeaderForField('id'),
            'HOTELCARD_HEADING_HOTEL_NAME' =>
                $objSorting->getHeaderForField('hotel_name'),
            'HOTELCARD_HEADING_CONTACT_NAME' =>
                $objSorting->getHeaderForField('contact_name'),
            'HOTELCARD_HEADING_HOTEL_LOCATION' =>
                $objSorting->getHeaderForField('hotel_location'),
            'HOTELCARD_HEADING_HOTEL_REGION' =>
                $objSorting->getHeaderForField('hotel_region'),
            'HOTELCARD_HEADING_STATUS' =>
                $objSorting->getHeaderForField('status'),
            'CORE_PAGING' => Paging::getPaging(
                $count, $_SERVER['QUERY_STRING'],
                $_ARRAYLANG['TXT_HOTELCARD_HOTELS'], false,
                SettingDb::getValue('hotel_per_page_backend')
            )
        ));

        return true;
    }


    /**
     * Set up the page with the view of a single hotel
     * @todo    Define what content is to be shown here, and how
     * @return  boolean             True on success, false otherwise
     */
    function hotel_view()
    {
        global $_ARRAYLANG;

        $hotel_id = (isset($_REQUEST['hotel_id'])
            ? intval($_REQUEST['hotel_id']) : 0);
        if (empty($hotel_id)) {
            return self::hotel_overview();
        }

        self::$pageTitle = $_ARRAYLANG['TXT_HOTELCARD_HOTEL_VIEW'];
        if (!self::$objTemplate->loadTemplateFile('hotel_view.html', true, true))
            die("Failed to load template hotel_view.html");
//        $uri = self::$uriBase.'&amp;act=hotel_view';
//echo("Local URI: ".htmlentities($uri)."<br />");
        self::$objTemplate->setGlobalVariable(
            $_ARRAYLANG
          + array(
            'MODULE_URI_BASE' => self::$uriBase,
        ));
        return HotelcardLibrary::hotel_view(self::$objTemplate, $hotel_id);
    }


    /**
     * Set up the filter and search section of the Hotel list view
     * @return  array           The array of current filter names and values
     */
    static function setupFilterAndSearch()
    {
        global $_ARRAYLANG;

        self::$objTemplate->addBlockfile(
            'HOTELCARD_HOTEL_FILTER_AND_SEARCH',
            'hotelcard_hotel_filter_and_search',
            'hotel_filter_and_search.html');

        if (empty($_SESSION['hotelcard']))
            $_SESSION['hotelcard'] = array();
        if (empty($_SESSION['hotelcard']['filter']))
            $_SESSION['hotelcard']['filter'] = array(
            'term' => '',
            'status' => '',
            'hotel_location' => '',
            'hotel_region' => '',
            'accomodation_type_id' => '',
            'rating' => '',
            'lang_id' => '',
        );
        $filter = &$_SESSION['hotelcard']['filter'];

        if (isset($_REQUEST['term'])) $filter['term'] =
            trim(contrexx_stripslashes($_REQUEST['term']));
        self::$objTemplate->setVariable(array(
            'HOTELCARD_FILTER_NAME' =>
                $_ARRAYLANG['TXT_HOTELCARD_TERM'],
            'HOTELCARD_FILTER_VALUE' => Html::getInputText(
                'term', $filter['term']),
        ));
        self::$objTemplate->parse('hotelcard_filter_name');
        self::$objTemplate->parse('hotelcard_filter_value');
        if (isset($_REQUEST['status'])) $filter['status'] = $_REQUEST['status'];
        self::$objTemplate->setVariable(array(
            'HOTELCARD_FILTER_NAME' =>
                $_ARRAYLANG['TXT_HOTELCARD_HOTEL_STATUS'],
            'HOTELCARD_FILTER_VALUE' => Html::getSelect(
                'status',
                array('' => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_STATUS_ANY'])
                  + Hotel::getStatusArray(), $filter['status']),
        ));
        self::$objTemplate->parse('hotelcard_filter_name');
        self::$objTemplate->parse('hotelcard_filter_value');
        if (isset($_REQUEST['hotel_location']))
            $filter['hotel_location'] =
                contrexx_stripslashes($_REQUEST['hotel_location']);
        self::$objTemplate->setVariable(array(
            'HOTELCARD_FILTER_NAME' =>
                $_ARRAYLANG['TXT_HOTELCARD_HOTEL_LOCATION'],
            'HOTELCARD_FILTER_VALUE' => Html::getInputText(
                'hotel_location', $filter['hotel_location']
// TODO:  Update the location with possible names
/*                , 'hotel_location',
                'onchange="new Ajax.Updater(\'hotel_location\','.
                ' \'index.php?cmd=hotelcard&amp;act=get_matching_location'.
                '&amp;location=\'+document.getElementById(\'hotel_location\').value+\''.
                '&amp;state=\'+document.getElementById(\'hotel_region\').value'.
                ', {method:\'get\'});"'
*/
                ),
        ));
        self::$objTemplate->parse('hotelcard_filter_name');
        self::$objTemplate->parse('hotelcard_filter_value');
        if (isset($_REQUEST['hotel_region']))
            $filter['hotel_region'] = $_REQUEST['hotel_region'];
        self::$objTemplate->setVariable(array(
            'HOTELCARD_FILTER_NAME' =>
                $_ARRAYLANG['TXT_HOTELCARD_HOTEL_REGION'],
            'HOTELCARD_FILTER_VALUE' => Html::getSelect(
                'hotel_region',
                array('' =>
                    $_ARRAYLANG['TXT_HOTELCARD_HOTEL_REGION_ANY'])
                  + State::getArray(true),
                $filter['hotel_region'], 'hotel_region'),
        ));
        self::$objTemplate->parse('hotelcard_filter_name');
        self::$objTemplate->parse('hotelcard_filter_value');
        if (isset($_REQUEST['accomodation_type_id']))
            $filter['accomodation_type_id'] = $_REQUEST['accomodation_type_id'];
        self::$objTemplate->setVariable(array(
            'HOTELCARD_FILTER_NAME' =>
                $_ARRAYLANG['TXT_HOTELCARD_ACCOMODATION_TYPE_ID'],
            'HOTELCARD_FILTER_VALUE' => Html::getSelect(
                'accomodation_type_id',
                array('' =>
                    $_ARRAYLANG['TXT_HOTELCARD_ACCOMODATION_TYPE_ID_ANY'])
                  + HotelAccomodationType::getNameArray(),
                $filter['accomodation_type_id']),
        ));
        self::$objTemplate->parse('hotelcard_filter_name');
        self::$objTemplate->parse('hotelcard_filter_value');
        if (isset($_REQUEST['rating'])) $filter['rating'] = $_REQUEST['rating'];
        self::$objTemplate->setVariable(array(
            'HOTELCARD_FILTER_NAME' =>
                $_ARRAYLANG['TXT_HOTELCARD_RATING'],
            'HOTELCARD_FILTER_VALUE' => Html::getSelect(
                'rating',
                array('' => $_ARRAYLANG['TXT_HOTELCARD_RATING_ANY'])
                  + HotelRating::getArray(),
                $filter['rating']),
        ));
        self::$objTemplate->parse('hotelcard_filter_name');
        self::$objTemplate->parse('hotelcard_filter_value');
        if (isset($_REQUEST['lang_id'])) $filter['lang_id'] = $_REQUEST['lang_id'];
        self::$objTemplate->setVariable(array(
            'HOTELCARD_FILTER_NAME' =>
                $_ARRAYLANG['TXT_HOTELCARD_LANG_ID'],
            'HOTELCARD_FILTER_VALUE' => Html::getSelect(
                'lang_id',
                array('' => $_ARRAYLANG['TXT_HOTELCARD_LANG_ID_ANY'])
                  + FWLanguage::getNameArray(),
                $filter['lang_id']),
        ));
        self::$objTemplate->parse('hotelcard_filter_name');
        self::$objTemplate->parse('hotelcard_filter_value');
        // Submit
        self::$objTemplate->setVariable(array(
            'HOTELCARD_FILTER_NAME' =>
                '', //$_ARRAYLANG['TXT_HOTELCARD_SUBMIT_FILTER_AND_SEARCH'],
            'HOTELCARD_FILTER_VALUE' =>
                Html::getInputButton('bsubmit',
                    $_ARRAYLANG['TXT_HOTELCARD_SUBMIT_FILTER_AND_SEARCH']),
        ));
        self::$objTemplate->parse('hotelcard_filter_name');
        self::$objTemplate->parse('hotelcard_filter_value');
        self::$objTemplate->setVariable(array(
            'HOTELCARD_FILTER_AND_SEARCH_NUMOF_COLUMNS' => 8,
            'TXT_HOTELCARD_FILTER_AND_SEARCH' =>
                $_ARRAYLANG['TXT_HOTELCARD_FILTER_AND_SEARCH']
        ));
//echo("Session filter:<br />".var_export($_SESSION['hotelcard']['filter'], true)."<br />"."current filter:<br />".var_export($filter, true)."<hr />");
//        $_SESSION['hotelcard']['filter'] = $filter;
        return $filter;
    }


    /**
     * Set up the Mailtemplate overview page
     * @return  boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function mailtemplate_overview()
    {
        global $_CORELANG;

        self::$pageTitle = $_CORELANG['TXT_CORE_MAILTEMPLATE_OVERVIEW'];
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
        global $_CORELANG;

        self::$pageTitle = $_CORELANG['TXT_CORE_MAILTEMPLATE_EDIT'];
        self::$objTemplate = MailTemplate::edit();
        return (bool)self::$objTemplate;
    }


    static function imagetypes_edit()
    {
        global $_CORELANG;

        self::$pageTitle = $_CORELANG['TXT_CORE_IMAGETYPES_EDIT'];
        self::$objTemplate = Imagetype::edit();
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

die("Hotelcard::errorHandler(): Disabled!");

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
//DBG::log("Hotelcard::errorHandler(): Mail templates delete, key ".self::MAILTEMPLATE_HOTEL_REGISTRATION_CONFIRMATION."<br />");
//        MailTemplate::deleteTemplate(self::MAILTEMPLATE_HOTEL_REGISTRATION_CONFIRMATION);
        If (!MailTemplate::getTemplate(Hotelcard::MAILTEMPLATE_HOTEL_REGISTRATION_CONFIRMATION))
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
                'protected' => 1,
            ));

        // Always!
        return false;
    }

}

Hotelcard::init();

?>
