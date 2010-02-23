<?php

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
     * Set up the selected administration page
     */
    static function getPage()
    {
        global $objTemplate, $_ARRAYLANG;

//DBG::activate(DBG_PHP|DBG_ADODB|DBG_LOG_FIREPHP);

        // Sigma template
        self::$objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/hotelcard/template');
        CSRF::add_placeholder(self::$objTemplate);
        self::$objTemplate->setErrorHandling(PEAR_ERROR_DIE);

        $cmd = (isset($_GET['cmd']) ? $_GET['cmd'] : '');
        $act = (isset($_GET['act']) ? $_GET['act'] : '');

        // Used for setting up the sorting headers and others
        self::$uriBase =
            CONTREXX_DIRECTORY_INDEX.'?cmd='.$cmd; //.(empty($act) ? '' : '&amp;act='.$act);

        $result = true;
//        $subnavigation = '';
        switch ($act) {
            case 'settings':
            case 'mailtemplate_overview':
            case 'mailtemplate_edit':
            case 'imagetypes_edit':
                $result &= self::settings();
                break;
            case 'edit_facility_groups':
            case 'edit_facilities_hotel':
            case 'edit_facilities_room':
            case 'edit_accomodation_types':
                $result &= self::settings(1);
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
            case 'hotel_edit':
            case 'edit_hotel':
                $act = 'edit_hotel_availability';
            case 'edit_hotel_availability':
            case 'edit_hotel_roomtypes':
            case 'edit_hotel_contact':
            case 'edit_hotel_images':
            case 'edit_hotel_details':
            case 'edit_hotel_facilities':
                self::$pageTitle = $_ARRAYLANG['TXT_HOTELCARD_'.strtoupper($act)];
                if (!self::$objTemplate->loadTemplateFile($act.'.html', true, true))
                    die("Failed to load template $act.html");
                $result &= HotelcardLibrary::editHotel(self::$objTemplate);
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
        $objTemplate->setVariable(array(
            'CONTENT_TITLE'          => self::$pageTitle,
            'ADMIN_CONTENT'          => self::$objTemplate->get(),
            'CONTENT_NAVIGATION'     =>
                '<a href="index.php?cmd=hotelcard">'.$_ARRAYLANG['TXT_HOTELCARD_OVERVIEW'].'</a>'.
                '<a href="index.php?cmd=hotelcard&amp;act=settings">'.$_ARRAYLANG['TXT_HOTELCARD_SETTINGS'].'</a>',
// Not implemented
//                '<a href="index.php?cmd=hotelcard&amp;act=hotels">'.$_ARRAYLANG['TXT_HOTELCARD_HOTELS'].'</a>'.
//                '<a href="index.php?cmd=hotelcard&amp;act=reservations">'.$_ARRAYLANG['TXT_HOTELCARD_RESERVATIONS'].'</a>'.
// Part of the settings view
//                '<a href="index.php?cmd=hotelcard&amp;act=mailtemplate_overview">'.$_ARRAYLANG['TXT_HOTELCARD_MAILTEMPLATES'].'</a>'.
//                '<a href="index.php?cmd=hotelcard&amp;act=imagetypes_edit">'.$_ARRAYLANG['TXT_HOTELCARD_IMAGETYPES'].'</a>'.
        ));
        $message = HotelcardLibrary::getMessage();
        if ($message)
            $objTemplate->setVariable(
                'CONTENT_OK_MESSAGE', $message);
        $error_message = HotelcardLibrary::getErrorMessage();
        if ($error_message)
            $objTemplate->setVariable(
                'CONTENT_STATUS_MESSAGE', $error_message);
        return true;
    }


    /**
     * Set up the page with a list of all Settings
     *
     * Stores the settings if requested to.
     * @return  boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function settings($layer=0)
    {
        global $_ARRAYLANG, $_CORELANG;

        self::$pageTitle = $_ARRAYLANG['TXT_HOTELCARD_SETTINGS'];

        if ($layer == 0) {
            if (isset($_POST['bsubmit'])) {
                if (isset($_REQUEST['act'])) {
                    switch ($_REQUEST['act']) {
                      case 'settings':
                        self::storeSettings();
                    }
                }
            }

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
            $result &= SettingDb::show(
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
            $result &= SettingDb::show_external(
                self::$objTemplate,
                $_CORELANG['TXT_CORE_IMAGETYPES_EDIT'],
                Imagetype::edit()->get()
            );
            if (   isset($_REQUEST['act'])
                && $_REQUEST['act'] == 'mailtemplate_edit') {
                $result &= SettingDb::show_external(
                    self::$objTemplate,
                    $_CORELANG['TXT_CORE_MAILTEMPLATE_EDIT'],
                    MailTemplate::edit()->get()
                );
            } else {
                SettingDb::init('backend');
                $result &= SettingDb::show_external(
                    self::$objTemplate,
                    $_CORELANG['TXT_CORE_MAILTEMPLATES'],
                    MailTemplate::overview(
                        SettingDb::getValue('mailtemplate_per_page_backend')
                    )->get()
                );
            }
        } else {
            $result &= SettingDb::show_external(
                self::$objTemplate,
                $_ARRAYLANG['TXT_HOTELCARD_FACILITY_GROUPS'],
                self::editFacilityGroups()->get()
            );
            $result &= SettingDb::show_external(
                self::$objTemplate,
                $_ARRAYLANG['TXT_HOTELCARD_FACILITIES_HOTEL'],
                self::editFacilitiesHotel()->get()
            );
            $result &= SettingDb::show_external(
                self::$objTemplate,
                $_ARRAYLANG['TXT_HOTELCARD_FACILITIES_ROOM'],
                self::editFacilitiesRoom()->get()
            );
            $result &= SettingDb::show_external(
                self::$objTemplate,
                $_ARRAYLANG['TXT_HOTELCARD_ACCOMODATION_TYPES'],
                self::editAccomodationTypes()->get()
            );
        }
        self::$objTemplate->setVariable(
            'CONTENT_SUBNAVIGATION',
                '<li><a href="index.php?cmd=hotelcard&amp;act=settings">'.
                $_ARRAYLANG['TXT_HOTELCARD_SETTINGS_BASIC'].'</a></li>'.
                '<li><a href="index.php?cmd=hotelcard&amp;act=edit_facility_groups">'.
                $_ARRAYLANG['TXT_HOTELCARD_SETTINGS_LANGUAGE'].'</a></li>'
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
    static function storeSettings()
    {
        global $_ARRAYLANG;

        $result = SettingDb::storeFromPost();
        // No changes detected
        if ($result === '') return true;
        if ($result) {
            HotelcardLibrary::addMessage($_ARRAYLANG['TXT_HOTELCARD_SETTING_STORED_SUCCESSFULLY']);
            return true;
        }
        HotelcardLibrary::addError($_ARRAYLANG['TXT_HOTELCARD_ERROR_SETTING_NOT_STORED']);
        // Probably the SettingDb class has something to say, too
        HotelcardLibrary::addError(SettingDb::getErrorString());
        return false;
    }


    /**
     * Set up the overview page
     * @todo    Define what content is to be shown here
     * @return  boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function overview()
    {
        global $_ARRAYLANG;

        self::hotel_overview(true);
        $count = Hotel::getCount();
        $last_registration_time = Hotel::getLastRegistrationDate();
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

        // Any of these requests will pass here
        self::hotel_delete();
        self::hotel_status();

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
        $uri = self::$uriBase;
        Html::stripUriParam($uri, 'hotel_id');
        Html::stripUriParam($uri, 'hotel_delete_id');
        Html::stripUriParam($uri, 'hotel_status');
        html::replaceUriParameter($uri, 'act=hotel_overview');
//echo("Local URI: ".htmlentities($uri)."<br />");
        $objSorting = new Sorting(
            $uri,
//            array_keys($arrSorting),
            $arrSorting,
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
            self::$objTemplate->hideBlock('hotelcard_row');
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
                self::$objTemplate->setVariable(array(
                    'HOTELCARD_ROWCLASS'       => (++$i % 2) + 1,
                    'HOTELCARD_HOTEL_ID'       => $hotel_id,
                    'HOTELCARD_HOTEL_NAME'     =>
                        htmlentities($objHotel->getFieldvalue('hotel_name'),
                            ENT_QUOTES, CONTREXX_CHARSET),
                    'HOTELCARD_CONTACT_NAME'   =>
                        $objHotel->getFieldvalue('contact_name'),
                    'HOTELCARD_HOTEL_LOCATION' =>
                        $objHotel->getFieldvalue('hotel_location').' '.
                        Location::getCityByZip(
                            $objHotel->getFieldvalue('hotel_location'), 204
                        ),
                    'HOTELCARD_HOTEL_REGION'   =>
                        $objHotel->getFieldvalue('hotel_region'),
                    'HOTELCARD_HOTEL_RATING'   =>
                        HotelRating::getString(
                            $objHotel->getFieldvalue('rating')),
                    'HOTELCARD_HOTEL_REGISTER_DATE'   =>
                        date(ASCMS_DATE_SHORT_FORMAT,
                            $objHotel->getFieldvalue('registration_time')),
                    'HOTELCARD_HOTEL_STATUS'   =>
                        Html::getLed(Hotel::getStatusColor($status),
                            Hotel::getStatusText($status)),
                    'HOTELCARD_FUNCTIONS'      => Html::getBackendFunctions(
                        array(
                            'view'   => 'act=hotel_view&amp;hotel_id='.$hotel_id,
                            'edit'   => 'act=hotel_edit&amp;hotel_id='.$hotel_id,
                            'delete' => 'hotel_delete_id='.$hotel_id,
                        )
                      + ($status & Hotel::STATUS_DELETED
                          ? array(
                              'mark_undeleted' =>
                                'status_bits=-'.Hotel::STATUS_DELETED.
                                '&amp;hotel_id='.$hotel_id, )
                          : array(
                              'mark_deleted' =>
                                'status_bits='.Hotel::STATUS_DELETED.
                                '&amp;hotel_id='.$hotel_id, )
                        ),
                        array(
                            'delete' =>
                                $_ARRAYLANG['TXT_HOTELCARD_HOTEL_DELETE_CONFIRM'].
                                $_ARRAYLANG['TXT_HOTELCARD_ACTION_IS_IRREVERSIBLE'],
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
            'TXT_HOTELCARD_MATCHING_HOTELS_COUNT' =>
                ($last_registered
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
            'HOTELCARD_HEADING_HOTEL_RATING' =>
                $objSorting->getHeaderForField('rating'),
            'HOTELCARD_HEADING_HOTEL_REGISTER_DATE' =>
                $objSorting->getHeaderForField('registration_time'),
            'HOTELCARD_HEADING_STATUS' =>
                $objSorting->getHeaderForField('status'),
            'CORE_PAGING' => Paging::getPaging(
                $count, $uri,
                $_ARRAYLANG['TXT_HOTELCARD_HOTELS'], false,
                SettingDb::getValue('hotel_per_page_backend')
            )
        ));
        return true;
    }


    /**
     * Deletes the Hotel with the given Hotel ID and all associated data
     *
     * Requires the Hotel ID to be present in $_REQUEST['hotel_delete_id'].
     * Also returns true if the required parameter is empty.
     * Adds some message according to the result of the operation.
     * @return  boolean               True on success, false otherwise
     */
    static function hotel_delete()
    {
        global $_ARRAYLANG;

        if (empty($_REQUEST['hotel_delete_id'])) {
            // NOOP
            return true;
        }
        $hotel_id = $_REQUEST['hotel_delete_id'];
        if (Hotel::deleteById($hotel_id)) {
            HotelcardLibrary::addMessage(
                $_ARRAYLANG['TXT_HOTELCARD_HOTEL_DELETED_SUCCESSFULLY']
            );
            return true;
        }
        HotelcardLibrary::addError(
            $_ARRAYLANG['TXT_HOTELCARD_HOTEL_DELETING_FAILED']
        );
        return false;
    }


    /**
     * OBSOLETE -- See {@see hotel_status()}.
     * Marks the Hotel with the given Hotel ID as deleted
     *
     * Requires the Hotel ID to be present in
     * $_REQUEST['hotel_mark_deleted_id'].
     * Also returns true if the required parameter is empty.
     * Adds some message according to the result of the operation.
     * @return  boolean               True on success, false otherwise
     */
    static function hotel_mark_deleted()
    {
        global $_ARRAYLANG;

        if (empty($_REQUEST['hotel_mark_deleted_id'])) {
            // NOOP
            return true;
        }
        $hotel_id = $_REQUEST['hotel_mark_deleted_id'];
        if (Hotel::updateStatus($hotel_id, Hotel::STATUS_DELETED)) {
            HotelcardLibrary::addMessage(
                $_ARRAYLANG['TXT_HOTELCARD_HOTEL_STATUS_UPDATED_SUCCESSFULLY']
            );
            return true;
        }
        HotelcardLibrary::addError(
            $_ARRAYLANG['TXT_HOTELCARD_ERROR_UPDATING_STATUS']
        );
        return false;
    }


    /**
     * Updates the status for the given Hotel ID
     *
     * Requires the Hotel ID to be present in $_REQUEST['hotel_id'], and
     * the new status in $_REQUEST['status_bits'].
     * Also returns true if one or more required parameters are empty.
     * Adds some message according to the result of the operation.
     * @return  boolean               True on success, false otherwise
     * @todo    Implement the backend functionality for this
     */
    static function hotel_status()
    {
        global $_ARRAYLANG;

        if (   empty($_REQUEST['status_bits'])
            || empty($_REQUEST['hotel_id'])) {
            // NOOP
            return true;
        }
        $hotel_id = $_REQUEST['hotel_id'];
        $status = $_REQUEST['status_bits'];
        if (Hotel::updateStatus($hotel_id, $status)) {
            HotelcardLibrary::addMessage(
                $_ARRAYLANG['TXT_HOTELCARD_HOTEL_STATUS_CHANGED_SUCCESSFULLY']
            );
            return true;
        }
        HotelcardLibrary::addError(
            $_ARRAYLANG['TXT_HOTELCARD_HOTEL_CHANGING_STATUS_FAILED']
        );
        return false;
    }


    /**
     * Set up the page with the view of a single hotel
     * @todo    Define what content is to be shown here, and how
     * @return  boolean             True on success, false otherwise
     */
    static function hotel_view()
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
        $columns = 7;
        self::$objTemplate->setGlobalVariable(array(
            'MODULE_URI_BASE' => self::$uriBase,
            'HOTELCARD_FILTER_AND_SEARCH_NUMOF_COLUMNS' => $columns,
            'TXT_HOTELCARD_FILTER_AND_SEARCH' =>
                $_ARRAYLANG['TXT_HOTELCARD_FILTER_AND_SEARCH'],
        ));

        // Init to defaults when called for the first time
        if (empty($_SESSION['hotelcard']['filter']['mode']))
            $_SESSION['hotelcard']['filter'] = array(
                'mode' => $_ARRAYLANG['TXT_HOTELCARD_FILTER_AND_SEARCH_SIMPLE'],
                'term' => '',
                // Default to entries that are not in the trashcan
                'status' => array(Hotel::STATUS_DELETED => -Hotel::STATUS_DELETED),
                'hotel_location' => '',
                'hotel_region' => '',
                'accomodation_type_id' => '',
                'rating' => '',
                'lang_id' => '',
            );
        $filter = &$_SESSION['hotelcard']['filter'];
//echo("Filter: ".var_export($filter, true)."<br />");
        // First row:  Search term, submit button, and simple/advanced switch
        if (isset($_REQUEST['term'])) $filter['term'] =
            trim(contrexx_stripslashes($_REQUEST['term']));
        self::$objTemplate->setVariable(array(
            'HOTELCARD_FILTER_NAME' =>
                $_ARRAYLANG['TXT_HOTELCARD_TERM'],
            'HOTELCARD_FILTER_VALUE' => Html::getInputText(
                'term', $filter['term'], false, 'style="width: 150px;"'),
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
        // Switch from simple to advanced and back
        if (isset($_REQUEST['mode'])) $filter['mode'] = $_REQUEST['mode'];
        self::$objTemplate->setVariable(array(
            'HOTELCARD_FILTER_NAME' =>
                $_ARRAYLANG['TXT_HOTELCARD_FILTER_AND_SEARCH_MODE'],
            'HOTELCARD_FILTER_VALUE' =>
                Html::getInputButton(
                    'mode',
                    ($filter['mode'] == html_entity_decode(
                        $_ARRAYLANG['TXT_HOTELCARD_FILTER_AND_SEARCH_SIMPLE'],
                        ENT_QUOTES, CONTREXX_CHARSET)
                      ? $_ARRAYLANG['TXT_HOTELCARD_FILTER_AND_SEARCH_ADVANCED']
                      : $_ARRAYLANG['TXT_HOTELCARD_FILTER_AND_SEARCH_SIMPLE'])
                ),
        ));
        // Fill up the table row
        for ($i = 0; $i < $columns-2; ++$i) {
            self::$objTemplate->touchBlock('hotelcard_filter_name');
            self::$objTemplate->touchBlock('hotelcard_filter_value');
            self::$objTemplate->parse('hotelcard_filter_name');
            self::$objTemplate->parse('hotelcard_filter_value');
        }
        self::$objTemplate->parse('hotelcard_filter_row');

        // In simple mode, only the search term parameter is considered
        if ($filter['mode'] == html_entity_decode(
            $_ARRAYLANG['TXT_HOTELCARD_FILTER_AND_SEARCH_SIMPLE'],
            ENT_QUOTES, CONTREXX_CHARSET))
            return array('term' => $filter['term'], );

        // Advanced options start here
        if (isset($_REQUEST['status'])) $filter['status'] = $_REQUEST['status'];
//echo("Got status array: ".var_export($filter['status'], true)."<br />");
        self::$objTemplate->setVariable(array(
            'HOTELCARD_FILTER_NAME' =>
                $_ARRAYLANG['TXT_HOTELCARD_HOTEL_STATUS'],
            'HOTELCARD_FILTER_VALUE' =>
                Hotel::getStatusSelection($filter['status']),
            // This one needs more room
            'HOTELCARD_FILTER_NAME_COLSPAN'  => ' colspan="2"',
            'HOTELCARD_FILTER_VALUE_COLSPAN' => ' colspan="2"',
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
                'hotel_location', $filter['hotel_location'],
                false, 'style="width: 150px;"'
// NTH:  Update the location with possible names
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
                $filter['hotel_region'], 'hotel_region',
                '', 'style="width: 150px;"'),
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
                $filter['accomodation_type_id'],
                false, '', 'style="width: 150px;"'),
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
                $filter['rating'], false, '', 'style="width: 150px;"'),
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
                $filter['lang_id'], false, '', 'style="width: 150px;"'),
        ));
        self::$objTemplate->parse('hotelcard_filter_name');
        self::$objTemplate->parse('hotelcard_filter_value');
//echo("Filter settings:<br />".var_export($filter, true)."<hr />");
//echo("Session filter:<br />".var_export($_SESSION['hotelcard']['filter'], true)."<br />"."current filter:<br />".var_export($filter, true)."<hr />");
        return $filter;
    }


    /**
     * Sets up the view for editing Hotel facility groups
     * @return  HTML_Template_Sigma       The template object
     */
    static function editFacilityGroups()
    {
        global $_ARRAYLANG;

        self::$pageTitle = $_ARRAYLANG['TXT_HOTELCARD_EDIT_FACILITY_GROUPS'];
        $objTemplateLocal = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/hotelcard/template');
        CSRF::add_placeholder($objTemplateLocal);
        $objTemplateLocal->setErrorHandling(PEAR_ERROR_DIE);
        if (!$objTemplateLocal->loadTemplateFile('edit_facility_groups.html', true, true))
            die("Failed to load template edit_facility_groups.html");

        self::deleteFacilityGroup();
        self::storeFacilityGroups();

        $uri = Html::getRelativeUri_entities();
        Html::replaceUriParameter($uri, 'act=edit_facility_groups');
        Html::replaceUriParameter($uri, 'active_tab='.SettingDb::getTabIndex());
        Html::stripUriParam($uri, 'delete_accomodation_type_id');
        Html::stripUriParam($uri, 'delete_facility_room_id');
        Html::stripUriParam($uri, 'delete_facility_id');
        Html::stripUriParam($uri, 'delete_group_id');
//echo("Hotelcard::editFacilityGroups(): URI $uri<br />");
        $objTemplateLocal->setGlobalVariable(
            $_ARRAYLANG
          + array(
            'URI_BASE' => $uri,
            'FACILITIES_TAB_INDEX' => SettingDb::getTabIndex(),
        ));

        $arrGroups = HotelFacility::getGroupArray();
        $row = 0;
        foreach ($arrGroups as $group_id => $arrGroup) {
            $objTemplateLocal->setVariable(array(
                'FACILITIES_ROWCLASS' => (++$row % 2) + 1,
                'FACILITIES_ID' => $group_id,
                'FACILITIES_ORD' => Html::getInputText(
                    'facility_group['.$group_id.'][ord]', $arrGroup['ord'], false,
                    'style="text-align: right; width: 100px;"'
                ),
                'FACILITIES_NAME' => Html::getInputText(
                    'facility_group['.$group_id.'][name]', $arrGroup['name'], false,
                    'style="text-align: left; width:'.DEFAULT_INPUT_WIDTH_BACKEND.'px;"'
                ),
                'FACILITIES_GROUP' => '-',
                'FACILITIES_FUNCTIONS' => Html::getBackendFunctions(
                    array(
                        'delete' => $uri.'&amp;delete_group_id='.$group_id,
                    ),
                    array(
                        'delete' => $_ARRAYLANG['TXT_HOTELCARD_FACILITY_GROUP_DELETE_CONFIRM'],
                    )
                ),
            ));
            $objTemplateLocal->parse('facilities_row');
        }
        $objTemplateLocal->setGlobalVariable(array(
            'FACILITIES_SECTION'     => $_ARRAYLANG['TXT_HOTELCARD_EDIT_FACILITY_GROUPS'],
        ));
        $objTemplateLocal->touchBlock('facilities_section');
        $objTemplateLocal->parse('facilities_section');

        $objTemplateLocal->setVariable(array(
            'FACILITIES_ROWCLASS' => 1,
            'FACILITIES_ID' => 0,
            'FACILITIES_ORD' => Html::getInputText(
                'facility_group[0][ord]', '', false,
                'style="text-align: right; width: 100px;"'
            ),
            'FACILITIES_NAME' => Html::getInputText(
                'facility_group[0][name]', '', false,
                'style="text-align: left; width:'.DEFAULT_INPUT_WIDTH_BACKEND.'px;"'
            ),
            'FACILITIES_GROUP' => '-',
            'FACILITIES_FUNCTIONS' => '',
        ));
        $objTemplateLocal->parse('facilities_row');
        $objTemplateLocal->setGlobalVariable(array(
            'FACILITIES_SECTION' => $_ARRAYLANG['TXT_HOTELCARD_ADD_FACILITY_GROUP'],
        ));
        $objTemplateLocal->touchBlock('facilities_section');
        $objTemplateLocal->parse('facilities_section');
        return $objTemplateLocal;
    }


    /**
     * Stores edited and new Hotel facility groups
     * @return  boolean                   True on success, false otherwise
     */
    static function storeFacilityGroups()
    {
        global $_ARRAYLANG;

        if (   empty($_POST['bsubmit'])
            || empty($_POST['facility_group'])) return;
        $result = '';
        HotelFacility::init();
        $arrGroups = HotelFacility::getGroupArray();
        foreach ($_POST['facility_group'] as $group_id => $arrGroup) {
            if (empty($group_id) && empty($arrGroup['name'])) continue;
            if (   isset($arrGroups[$group_id])
                && $arrGroups[$group_id]['ord'] == $arrGroup['ord']
                && $arrGroups[$group_id]['name'] == $arrGroup['name']
            ) continue;
            if ($result === '') $result = true;
            if (!HotelFacility::storeFacilityGroup(
                $arrGroup['name'],
                $group_id,
                $arrGroup['ord']
            )) $result = false;
        }
        // No changes
        if ($result === '') return;
        HotelFacility::reset();
        if ($result === true) {
            HotelcardLibrary::addMessage($_ARRAYLANG['TXT_HOTELCARD_FACILITY_GROUPS_STORED_SUCCESSFULLY']);
            return;
        }
        HotelcardLibrary::addMessage($_ARRAYLANG['TXT_HOTELCARD_FACILITY_GROUPS_STORING_FAILED']);
    }


    /**
     * Deletes the Hotel facility group with its ID present in the
     * delete_group_id request parameter
     * @return  boolean                   True on success, false otherwise
     */
    static function deleteFacilityGroup()
    {
        global $_ARRAYLANG;

        if (empty($_REQUEST['delete_group_id'])) return;
        $group_id = $_REQUEST['delete_group_id'];
        if (HotelFacility::deleteGroupById($group_id)) {
            HotelcardLibrary::addMessage($_ARRAYLANG['TXT_HOTELCARD_FACILITY_GROUP_DELETED_SUCCESSFULLY']);
            HotelFacility::reset();
            return;
        }
        HotelcardLibrary::addMessage($_ARRAYLANG['TXT_HOTELCARD_FACILITY_GROUP_DELETING_FAILED']);
    }


    /**
     * Sets up the view for editing Hotel facilities
     * @return  HTML_Template_Sigma       The template object
     */
    static function editFacilitiesHotel()
    {
        global $_ARRAYLANG;

        self::$pageTitle = $_ARRAYLANG['TXT_HOTELCARD_EDIT_FACILITIES_HOTEL'];
        $objTemplateLocal = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/hotelcard/template');
        CSRF::add_placeholder($objTemplateLocal);
        $objTemplateLocal->setErrorHandling(PEAR_ERROR_DIE);
        if (!$objTemplateLocal->loadTemplateFile('edit_facilities_hotel.html', true, true))
            die("Failed to load template edit_facilities_hotel.html");

        self::deleteFacilityHotel();
        self::storeFacilitiesHotel();

        $uri = Html::getRelativeUri_entities();
        Html::replaceUriParameter($uri, 'act=edit_facilities_hotel');
        Html::replaceUriParameter($uri, 'active_tab='.SettingDb::getTabIndex());
        Html::stripUriParam($uri, 'delete_accomodation_type_id');
        Html::stripUriParam($uri, 'delete_facility_room_id');
        Html::stripUriParam($uri, 'delete_facility_id');
        Html::stripUriParam($uri, 'delete_group_id');
        $objTemplateLocal->setGlobalVariable(
            $_ARRAYLANG
          + array(
            'URI_BASE' => $uri,
            'FACILITIES_TAB_INDEX' => SettingDb::getTabIndex(),
        ));

        $arrGroupName = HotelFacility::getGroupNameArray();
        $arrFacilities = HotelFacility::getFacilityArray();
        $row = 0;
        foreach ($arrFacilities as $facility_id => $arrFacility) {
            $objTemplateLocal->setVariable(array(
                'FACILITIES_ROWCLASS' => (++$row % 2) + 1,
                'FACILITIES_ID' => $facility_id,
                'FACILITIES_VISIBILITY' => Html::getSelect(
                    'facility_hotel['.$facility_id.'][ord]',
                    array(
                        $row =>
                            $_ARRAYLANG['TXT_HOTELCARD_FACILITY_VISIBLE_REGISTER'],
                        1000 + $row =>
                            $_ARRAYLANG['TXT_HOTELCARD_FACILITY_VISIBLE_EDIT'],
                    ),
                    ($arrFacility['ord'] < 1000
                        ? $row : 1000 + $row),
                    false, '',
                    'style="text-align: left; width: 100px;"'
                ),
                'FACILITIES_NAME' => Html::getInputText(
                    'facility_hotel['.$facility_id.'][name]', $arrFacility['name'], false,
                    'style="text-align: left; width:'.DEFAULT_INPUT_WIDTH_BACKEND.'px;"'
                ),
                'FACILITIES_GROUP' => Html::getSelect(
                    'facility_hotel['.$facility_id.'][group_id]', $arrGroupName,
                    $arrFacility['group_id'], false, '',
                    'style="text-align: right; width:'.DEFAULT_INPUT_WIDTH_BACKEND.'px;"'
                ),
                'FACILITIES_FUNCTIONS' => Html::getBackendFunctions(
                    array(
                        'delete' => $uri.'&amp;delete_facility_id='.$facility_id,
                    ),
                    array(
                        'delete' => $_ARRAYLANG['TXT_HOTELCARD_FACILITY_DELETE_CONFIRM'],
                    )
                ),
            ));
            $objTemplateLocal->parse('facilities_row');
        }
        $objTemplateLocal->setGlobalVariable(array(
            'FACILITIES_SECTION'     => $_ARRAYLANG['TXT_HOTELCARD_EDIT_FACILITIES_HOTEL'],
        ));
        $objTemplateLocal->touchBlock('facilities_section');
        $objTemplateLocal->parse('facilities_section');

        ++$row;
        $objTemplateLocal->setVariable(array(
            'FACILITIES_ROWCLASS' => 1,
            'FACILITIES_ID' => 0,
            'FACILITIES_VISIBILITY' => Html::getSelect(
                'facility_hotel[0][ord]',
                array(
                    $row =>
                        $_ARRAYLANG['TXT_HOTELCARD_FACILITY_VISIBLE_REGISTER'],
                    1000 + $row =>
                        $_ARRAYLANG['TXT_HOTELCARD_FACILITY_VISIBLE_EDIT'],
                ),
                1000 + $row, false, '',
                'style="text-align: left; width: 100px;"'
            ),
            'FACILITIES_NAME' => Html::getInputText(
                'facility_hotel[0][name]', '', false,
                'style="text-align: left; width:'.DEFAULT_INPUT_WIDTH_BACKEND.'px;"'
            ),
            'FACILITIES_GROUP' => Html::getSelect(
                'facility_hotel[0][group_id]', $arrGroupName, 0, false, '',
                'style="text-align: right; width:'.DEFAULT_INPUT_WIDTH_BACKEND.'px;"'
            ),
            'FACILITIES_FUNCTIONS' => '',
        ));
        $objTemplateLocal->parse('facilities_row');
        $objTemplateLocal->setGlobalVariable(array(
            'FACILITIES_SECTION' => $_ARRAYLANG['TXT_HOTELCARD_ADD_FACILITY_HOTEL'],
        ));
        $objTemplateLocal->touchBlock('facilities_section');
        $objTemplateLocal->parse('facilities_section');
//die($objTemplateLocal->get());
        return $objTemplateLocal;
    }


    /**
     * Stores edited and new Hotel facilities
     *
     * Note that the entries' ordinal value are reindexed when they are
     * displayed for editing, and thus may get updated on storing
     * even if no changes were made.
     * @return  boolean                   True on success, false otherwise
     */
    static function storeFacilitiesHotel()
    {
        global $_ARRAYLANG;

        if (   empty($_POST['bsubmit'])
            || empty($_POST['facility_hotel'])) return;
        $result = '';
        HotelFacility::init();
        $arrFacilities = HotelFacility::getFacilityArray();
        foreach ($_POST['facility_hotel'] as $facility_id => $arrFacility) {
            if (empty($facility_id) && empty($arrFacility['name'])) continue;
            if (   isset($arrFacilities[$facility_id])
                && $arrFacilities[$facility_id]['ord'] == $arrFacility['ord']
                && $arrFacilities[$facility_id]['name'] == $arrFacility['name']
                && $arrFacilities[$facility_id]['group_id'] == $arrFacility['group_id']
            ) continue;
//echo("Hotelcard::storeFacilitiesHotel(): Storing facility ID $facility_id: ord ".$arrFacility['ord'].", name ".$arrFacility['name'].", group ID ".$arrFacility['group_id']."<br />");
            if ($result === '') $result = true;
            if (!HotelFacility::storeFacility(
                $arrFacility['name'],
                $arrFacility['group_id'],
                $facility_id,
                $arrFacility['ord']
            )) $result = false;
        }
        // No changes
        if ($result === '') return;
        HotelFacility::reset();
        if ($result === true) {
            HotelcardLibrary::addMessage($_ARRAYLANG['TXT_HOTELCARD_FACILITIES_STORED_SUCCESSFULLY']);
            return;
        }
        HotelcardLibrary::addMessage($_ARRAYLANG['TXT_HOTELCARD_FACILITIES_STORING_FAILED']);
    }


    /**
     * Deletes the Hotel facility with its ID present in the
     * delete_facility_id request parameter
     * @return  boolean                   True on success, false otherwise
     */
    static function deleteFacilityHotel()
    {
        global $_ARRAYLANG;

        if (empty($_REQUEST['delete_facility_id'])) return;
        $facility_id = $_REQUEST['delete_facility_id'];
        if (HotelFacility::deleteFacilityById($facility_id)) {
            HotelcardLibrary::addMessage($_ARRAYLANG['TXT_HOTELCARD_FACILITY_DELETED_SUCCESSFULLY']);
            HotelFacility::reset();
            return;
        }
        HotelcardLibrary::addMessage($_ARRAYLANG['TXT_HOTELCARD_FACILITIES_DELETING_FAILED']);
    }


    /**
     * Sets up the view for editing room facilities
     * @return  HTML_Template_Sigma       The template object
     */
    static function editFacilitiesRoom()
    {
        global $_ARRAYLANG;

        self::$pageTitle = $_ARRAYLANG['TXT_HOTELCARD_EDIT_FACILITIES_ROOM'];
        $objTemplateLocal = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/hotelcard/template');
        CSRF::add_placeholder($objTemplateLocal);
        $objTemplateLocal->setErrorHandling(PEAR_ERROR_DIE);
        if (!$objTemplateLocal->loadTemplateFile('edit_facilities_room.html', true, true))
            die("Failed to load template edit_facilities_room.html");

        self::deleteFacilityRoom();
        self::storeFacilitiesRoom();

        $uri = Html::getRelativeUri_entities();
        Html::replaceUriParameter($uri, 'act=edit_facilities_room');
        Html::replaceUriParameter($uri, 'active_tab='.SettingDb::getTabIndex());
        Html::stripUriParam($uri, 'delete_accomodation_type_id');
        Html::stripUriParam($uri, 'delete_facility_room_id');
        Html::stripUriParam($uri, 'delete_facility_id');
        Html::stripUriParam($uri, 'delete_group_id');
        $objTemplateLocal->setGlobalVariable(
            $_ARRAYLANG
          + array(
            'URI_BASE' => $uri,
            'FACILITIES_TAB_INDEX' => SettingDb::getTabIndex(),
        ));

        $arrFacilities = HotelRoom::getFacilityArray();
        $row = 0;
        foreach ($arrFacilities as $facility_id => $arrFacility) {
            $objTemplateLocal->setVariable(array(
                'FACILITIES_ROWCLASS' => (++$row % 2) + 1,
                'FACILITIES_ID' => $facility_id,
                'FACILITIES_VISIBILITY' => Html::getSelect(
                    'facility_room['.$facility_id.'][ord]',
                    array(
                        $row =>
                            $_ARRAYLANG['TXT_HOTELCARD_FACILITY_VISIBLE_REGISTER'],
                        1000 + $row =>
                            $_ARRAYLANG['TXT_HOTELCARD_FACILITY_VISIBLE_EDIT'],
                    ),
                    ($arrFacility['ord'] < 1000
                        ? $row : 1000 + $row),
                    false, '',
                    'style="text-align: left; width: 100px;"'
                ),
                'FACILITIES_NAME' => Html::getInputText(
                    'facility_room['.$facility_id.'][name]', $arrFacility['name'], false,
                    'style="text-align: left; width:'.DEFAULT_INPUT_WIDTH_BACKEND.'px;"'
                ),
                'FACILITIES_FUNCTIONS' => Html::getBackendFunctions(
                    array(
                        'delete' => $uri.'&amp;delete_facility_room_id='.$facility_id,
                    ),
                    array(
                        'delete' => $_ARRAYLANG['TXT_HOTELCARD_FACILITY_DELETE_CONFIRM'],
                    )
                ),
            ));
            $objTemplateLocal->parse('facilities_row');
        }
        $objTemplateLocal->setGlobalVariable(array(
            'FACILITIES_SECTION'     => $_ARRAYLANG['TXT_HOTELCARD_EDIT_FACILITIES_HOTEL'],
        ));
        $objTemplateLocal->touchBlock('facilities_section');
        $objTemplateLocal->parse('facilities_section');

        ++$row;
        $objTemplateLocal->setVariable(array(
            'FACILITIES_ROWCLASS' => 1,
            'FACILITIES_ID' => 0,
            'FACILITIES_VISIBILITY' => Html::getSelect(
                'facility_room[0][ord]',
                array(
                    $row =>
                        $_ARRAYLANG['TXT_HOTELCARD_FACILITY_VISIBLE_REGISTER'],
                    1000 + $row =>
                        $_ARRAYLANG['TXT_HOTELCARD_FACILITY_VISIBLE_EDIT'],
                ),
                1000 + $row, false, '',
                'style="text-align: left; width: 100px;"'
            ),
            'FACILITIES_NAME' => Html::getInputText(
                'facility_room[0][name]', '', false,
                'style="text-align: left; width:'.DEFAULT_INPUT_WIDTH_BACKEND.'px;"'
            ),
            'FACILITIES_FUNCTIONS' => '',
        ));
        $objTemplateLocal->parse('facilities_row');
        $objTemplateLocal->setGlobalVariable(array(
            'FACILITIES_SECTION'     => $_ARRAYLANG['TXT_HOTELCARD_ADD_FACILITY_HOTEL'],

        ));
        $objTemplateLocal->touchBlock('facilities_section');
        $objTemplateLocal->parse('facilities_section');
//die($objTemplateLocal->get());
        return $objTemplateLocal;
    }


    /**
     * Stores edited and new room facilities
     *
     * Note that the entries' ordinal value are reindexed when they are
     * displayed for editing, and thus may get updated on storing
     * even if no changes were made.
     * @return  boolean                   True on success, false otherwise
     */
    static function storeFacilitiesRoom()
    {
        global $_ARRAYLANG;

        if (   empty($_POST['bsubmit'])
            || empty($_POST['facility_room'])) return;
        $result = '';
        $arrFacilities = HotelRoom::getFacilityArray();
        foreach ($_POST['facility_room'] as $facility_id => $arrFacility) {
            if (empty($facility_id) && empty($arrFacility['name'])) continue;
            if (   isset($arrFacilities[$facility_id])
                && $arrFacilities[$facility_id]['ord'] == $arrFacility['ord']
                && $arrFacilities[$facility_id]['name'] == $arrFacility['name']
            ) continue;
//echo("Hotelcard::storeFacilitiesRoom(): Storing facility ID $facility_id: ord ".$arrFacility['ord'].", name ".$arrFacility['name']."<br />");
            if ($result === '') $result = true;
            if (!HotelRoom::storeFacility(
                $arrFacility['name'],
                $facility_id,
                $arrFacility['ord']
            )) $result = false;
        }
        // No changes
        if ($result === '') return;
        HotelRoom::reset();
        if ($result === true) {
            HotelcardLibrary::addMessage($_ARRAYLANG['TXT_HOTELCARD_FACILITIES_STORED_SUCCESSFULLY']);
            return;
        }
        HotelcardLibrary::addMessage($_ARRAYLANG['TXT_HOTELCARD_FACILITIES_STORING_FAILED']);
    }


    /**
     * Deletes the room facility with its ID present in the
     * delete_facility_room_id request parameter
     * @return  boolean                   True on success, false otherwise
     */
    static function deleteFacilityRoom()
    {
        global $_ARRAYLANG;

        if (empty($_REQUEST['delete_facility_room_id'])) return;
        $facility_id = $_REQUEST['delete_facility_room_id'];
        if (HotelRoom::deleteFacilityById($facility_id)) {
            HotelcardLibrary::addMessage($_ARRAYLANG['TXT_HOTELCARD_FACILITY_DELETED_SUCCESSFULLY']);
            HotelRoom::reset();
            return;
        }
        HotelcardLibrary::addMessage($_ARRAYLANG['TXT_HOTELCARD_FACILITIES_DELETING_FAILED']);
    }


    /**
     * Sets up the view for editing accomodation types
     * @return  HTML_Template_Sigma       The template object
     */
    static function editAccomodationTypes()
    {
        global $_ARRAYLANG;

        self::$pageTitle = $_ARRAYLANG['TXT_HOTELCARD_EDIT_ACCOMODATION_TYPES'];
        $objTemplateLocal = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/hotelcard/template');
        CSRF::add_placeholder($objTemplateLocal);
        $objTemplateLocal->setErrorHandling(PEAR_ERROR_DIE);
        if (!$objTemplateLocal->loadTemplateFile('edit_accomodation_types.html', true, true))
            die("Failed to load template edit_accomodation_types.html");

        self::deleteAccomodationType();
        self::storeAccomodationTypes();

        $uri = Html::getRelativeUri_entities();
        Html::replaceUriParameter($uri, 'act=edit_accomodation_types');
        Html::replaceUriParameter($uri, 'active_tab='.SettingDb::getTabIndex());
        Html::stripUriParam($uri, 'delete_accomodation_type_id');
        Html::stripUriParam($uri, 'delete_facility_room_id');
        Html::stripUriParam($uri, 'delete_facility_id');
        Html::stripUriParam($uri, 'delete_group_id');
        $objTemplateLocal->setGlobalVariable(
            $_ARRAYLANG
          + array(
            'URI_BASE' => $uri,
            'ACCOMODATION_TYPE_TAB_INDEX'  => SettingDb::getTabIndex(),
        ));

        $arrTypes = HotelAccomodationType::getArray();
        $row = 0;
        foreach ($arrTypes as $type_id => $arrType) {
            $objTemplateLocal->setVariable(array(
                'ACCOMODATION_TYPE_ROWCLASS' => (++$row % 2) + 1,
                'ACCOMODATION_TYPE_ID' => $type_id,
                'ACCOMODATION_TYPE_ORD' => Html::getInputText(
                    'accomodation_type['.$type_id.'][ord]',
                    $arrType['ord'], false,
                    'style="text-align: right; width: 100px;"'
                ),
                'ACCOMODATION_TYPE_NAME' => Html::getInputText(
                    'accomodation_type['.$type_id.'][name]', $arrType['name'], false,
                    'style="text-align: left; width:'.DEFAULT_INPUT_WIDTH_BACKEND.'px;"'
                ),
                'ACCOMODATION_TYPE_FUNCTIONS' => Html::getBackendFunctions(
                    array(
                        'delete' => $uri.'&amp;delete_accomodation_type_id='.$type_id,
                    ),
                    array(
                        'delete' => $_ARRAYLANG['TXT_HOTELCARD_ACCOMODATION_TYPE_DELETE_CONFIRM'],
                    )
                ),
            ));
            $objTemplateLocal->parse('accomodation_type_row');
        }
        $objTemplateLocal->setGlobalVariable(array(
            'ACCOMODATION_TYPE_SECTION'     => $_ARRAYLANG['TXT_HOTELCARD_EDIT_ACCOMODATION_TYPES'],
        ));
        $objTemplateLocal->touchBlock('accomodation_type_section');
        $objTemplateLocal->parse('accomodation_type_section');

        ++$row;
        $objTemplateLocal->setVariable(array(
            'ACCOMODATION_TYPE_ROWCLASS' => 1,
            'ACCOMODATION_TYPE_ID' => 0,
            'ACCOMODATION_TYPE_ORD' => Html::getInputText(
                'accomodation_type[0][ord]', '', false,
                'style="text-align: right; width: 100px;"'
            ),
            'ACCOMODATION_TYPE_NAME' => Html::getInputText(
                'accomodation_type[0][name]', '', false,
                'style="text-align: left; width:'.DEFAULT_INPUT_WIDTH_BACKEND.'px;"'
            ),
            'ACCOMODATION_TYPE_FUNCTIONS' => '',
        ));
        $objTemplateLocal->parse('accomodation_type_row');
        $objTemplateLocal->setGlobalVariable(array(
            'ACCOMODATION_TYPE_SECTION'     => $_ARRAYLANG['TXT_HOTELCARD_ADD_ACCOMODATION_TYPE_HOTEL'],

        ));
        $objTemplateLocal->touchBlock('accomodation_type_section');
        $objTemplateLocal->parse('accomodation_type_section');
//die($objTemplateLocal->get());
        return $objTemplateLocal;
    }


    /**
     * Stores edited and new accomodation types
     *
     * Note that the entries' ordinal value are reindexed when they are
     * displayed for editing, and thus may get updated on storing
     * even if no changes were made.
     * @return  boolean                   True on success, false otherwise
     */
    static function storeAccomodationTypes()
    {
        global $_ARRAYLANG;

        if (   empty($_POST['bsubmit'])
            || empty($_POST['accomodation_type'])) return;
        $result = '';
        $arrTypes = HotelAccomodationType::getArray();
        foreach ($_POST['accomodation_type'] as $type_id => $arrType) {
            if (empty($type_id) && empty($arrType['name'])) continue;
            if (   isset($arrTypes[$type_id])
                && $arrTypes[$type_id]['ord'] == $arrType['ord']
                && $arrTypes[$type_id]['name'] == $arrType['name']
            ) continue;
//echo("Hotelcard::storeFacilitiesRoom(): Storing facility ID $facility_id: ord ".$arrFacility['ord'].", name ".$arrFacility['name']."<br />");
            if ($result === '') $result = true;
            if (!HotelAccomodationType::store(
                $arrType['name'],
                $type_id,
                $arrType['ord']
            )) $result = false;
        }
        // No changes
        if ($result === '') return;
        HotelAccomodationType::reset();
        if ($result === true) {
            HotelcardLibrary::addMessage($_ARRAYLANG['TXT_HOTELCARD_ACCOMODATION_TYPES_STORED_SUCCESSFULLY']);
            return;
        }
        HotelcardLibrary::addMessage($_ARRAYLANG['TXT_HOTELCARD_ACCOMODATION_TYPES_STORING_FAILED']);
    }


    /**
     * Deletes the accomodation type with its ID present in the
     * delete_accomodation_type_id request parameter
     * @return  boolean                   True on success, false otherwise
     */
    static function deleteAccomodationType()
    {
        global $_ARRAYLANG;

        if (empty($_REQUEST['delete_accomodation_type_id'])) return;
        $type_id = $_REQUEST['delete_accomodation_type_id'];
        if (HotelAccomodationType::deleteById($type_id)) {
            HotelcardLibrary::addMessage($_ARRAYLANG['TXT_HOTELCARD_ACCOMODATION_TYPE_DELETED_SUCCESSFULLY']);
            HotelAccomodationType::reset();
            return;
        }
        HotelcardLibrary::addMessage($_ARRAYLANG['TXT_HOTELCARD_ACCOMODATION_TYPE_DELETING_FAILED']);
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

//DBG::log("Hotelcard::errorHandler(): Settings init()<br />");
        // Add missing settings
        SettingDb::init();

//DBG::log("Hotelcard::errorHandler(): Settings delete, module ID ".MODULE_ID."<br />");
// To reset the default settings, enable
//        SettingDb::deleteModule();

//DBG::log("Hotelcard::errorHandler(): Settings add<br />");
        SettingDb::add('admin_email', 'info@hotelcard.ch', 101, 'email', '', 'admin');
// Obsolete
//        SettingDb::add('hotel_minimum_rooms_days', 180, 201, 'text', '', 'admin');
// Replaced by
        SettingDb::add('hotel_minimum_rooms_per_day', 1, 201, 'text', '', 'admin');
        SettingDb::add('user_profile_attribute_hotel_id', '', 301, 'dropdown_user_custom_attribute', '', 'admin');
        SettingDb::add('hotel_usergroup', '', 401, 'dropdown_usergroup', '', 'admin');

        SettingDb::add('hotel_per_page_frontend', '10', 1, 'text', '', 'frontend');
        SettingDb::add('hotel_default_order_frontend', 'price DESC', 2, 'text', '', 'frontend');
        SettingDb::add('hotel_max_pictures', '3', 3, 'text', '', 'frontend');
        SettingDb::add('terms_and_conditions_1', '[AGB hier]', 11, 'wysiwyg', '', 'frontend');
        SettingDb::add('terms_and_conditions_file_1', '', 12, 'fileupload', 'application/pdf', 'terms');
        SettingDb::add('terms_and_conditions_2', '[Terms and Conditions here]', 13, 'wysiwyg', '', 'frontend');
        SettingDb::add('terms_and_conditions_file_2', '', 14, 'fileupload', 'application/pdf', 'terms');
        SettingDb::add('terms_and_conditions_3', '[AGB ici]', 15, 'wysiwyg', '', 'frontend');
        SettingDb::add('terms_and_conditions_file_3', '', 16, 'fileupload', 'application/pdf', 'terms');
        SettingDb::add('terms_and_conditions_4', '[AGB qui]', 17, 'wysiwyg', '', 'frontend');
        SettingDb::add('terms_and_conditions_file_4', '', 18, 'fileupload', 'application/pdf', 'terms');

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
//DBG::log("Hotelcard::errorHandler(): Mail templates add<br />");
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

?>
