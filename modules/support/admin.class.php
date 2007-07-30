<?php

/**
 * Support system including Tickets, Knowledge Base and Mail support.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */

/**
 * Common functions and methods used by both front- and backend
 */
require_once ASCMS_MODULE_PATH.'/support/lib/SupportCommon.class.php';
/**
 * Support Category
 */
require_once ASCMS_MODULE_PATH.'/support/lib/SupportCategory.class.php';
/**
 * Support Categories
 */
require_once ASCMS_MODULE_PATH.'/support/lib/SupportCategories.class.php';
/**
 * Support Ticket
 */
require_once ASCMS_MODULE_PATH.'/support/lib/Ticket.class.php';

/**
 * Support system backend
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */
class Support
{
    /**
     * @access  private
     * @var     HTML_Template_Sigma
     */
    var $objTemplate;

    /**
     * @access  private
     * @var     string
     */
    var $strOkMessage;

    /**
     * @access  private
     * @var     string
     */
    var $strErrMessage;

    /**
     * The Support Categories object
     *
     * Do not confuse this with the Support Category object!
     * @var     SupportCategories
     */
    var $objSupportCategories;

    /**
     * The language object
     * @var     FWLanguage
     */
    var $objLanguage;

    /**
     * The currently selected edit language ID
     * @var     integer
     */
    var $editLanguageId;


    /**
     * Constructor (PHP4)
     * @param   string  $strTemplate
     * @see     __construct()
     */
    function Support()
    {
        $this->__construct();
    }

    /**
     * Constructor (PHP5)
     * @global  array       $_ARRAYLANG     Language array
     * @global  mixed       $objInit        Init object
     */
    function __construct()
    {
        global $objTemplate, $_ARRAYLANG, $objInit;

        if (1) {
            global $objDatabase; $objDatabase->debug = 1;
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
        }

        $this->objTemplate = &new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/support/template');
        $this->objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $objTemplate->setVariable(
            'CONTENT_NAVIGATION',
                '<a href="index.php?cmd=support&amp;act=ticketOverview">'.
                $_ARRAYLANG['TXT_SUPPORT_TICKETS'].'</a>'.
                '<a href="index.php?cmd=support&amp;act=editCategories">'.
                $_ARRAYLANG['TXT_SUPPORT_CATEGORIES'].'</a>'.
                '<a href="index.php?cmd=support&amp;act=settings">'.
                $_ARRAYLANG['TXT_SETTINGS'].'</a>'
        );

        $this->editLanguageId = $objInit->defaultFrontendLangId;
        if (!empty($_REQUEST['editLanguageId'])) {
            $this->editLanguageId = $_REQUEST['editLanguageId'];
        }

        // Language object
        $this->objLanguage = new FWLanguage();

        // Support Categories object
        $this->objSupportCategories =
            new SupportCategories($this->editLanguageId);
    }


    /**
     * Call the appropriate method to set up the requested page.
     * @access  public
     * @global  Template    $objTemplate    Template
     * @global  array       $_ARRAYLANG     Language array
     * @return  string      The created content
     */
    function getPage()
    {
        global $objTemplate, $_ARRAYLANG;

        if (!isset($_GET['act'])) {
            $_GET['act'] = '';
        }
        switch ($_GET['act']) {
          case 'storeCategory':
            $this->storeSupportCategory();
            $this->editCategories();
            break;
          case 'storeCategories':
            $this->storeSupportCategories();
            $this->editCategories();
            break;
          case 'deleteCategory':
            $this->deleteCategory();
            $this->editCategories();
            break;
          case 'deleteCategories':
            $this->deleteCategories();
            $this->editCategories();
            break;
          case 'editCategories':
            $this->editCategories();
            break;
          case 'deleteTicket':
            $this->deleteTicket();
            $this->ticketOverview();
            break;
          case 'deleteTickets':
            $this->deleteTickets();
            $this->ticketOverview();
            break;
/*
            case '':
            $this->_();
            break;
*/
          default:
            $this->ticketOverview();
            break;
        }
        $objTemplate->setVariable(array(
//            'CONTENT_TITLE'          => $this->_pageTitle,
            'CONTENT_OK_MESSAGE'     => $this->strOkMessage,
            'CONTENT_STATUS_MESSAGE' => $this->strErrMessage,
            'ADMIN_CONTENT'          => $this->objTemplate->get()
        ));
    }


    /**
     * Set the Ticket status
     * @return  boolean             True on success, false otherwise
     * @global  array       $_ARRAYLANG     Language array
     * @global  mixed       $objInit        Init object
     */
    function setTicketStatus()
    {
        global $_ARRAYLANG, $objInit;

echo("deleteTicket(): \$_GET: ");var_export($_GET);echo("<br />");
        $return = true;

        // The ID of the Ticket currently being edited
        if (empty($_GET['id']) || empty($_GET['status'])) {
            $return = false;
        } else {
            $id     = intval($_GET['id']);
            $status = intval($_GET['status']);
            $objTicket = Ticket::getById($id);
            if (!$objTicket) {
                $return = false;
            } else {
                if ($status != $objTicket->getStatus()) {
                    $objTicket->setStatus($status);
                    if (!$objTicket->store()) {
                        $return = false;
                    }
                }
            }
        }
        if ($return) {
            $this->strOkMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATED_DATA_SUCCESSFULLY'];
        } else {
            $this->strErrMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATING_DATA_FAILED'];
        }
        return $return;
    }


    /**
     * Delete the Ticket
     * @return  boolean             True on success, false otherwise
     * @global  array       $_ARRAYLANG     Language array
     * @global  mixed       $objInit        Init object
     */
    function deleteTicket()
    {
        global $_ARRAYLANG, $objInit;

echo("deleteTicket(): \$_GET: ");var_export($_GET);echo("<br />");
        $return = true;

        // The ID of the Ticket currently being edited
        if (empty($_GET['id'])) {
            $return = false;
        } else {
            $id = intval($_GET['id']);
            $objTicket = Ticket::getById($id);
            if (!$objTicket) {
                $return = false;
            } else {
                if (!$objTicket->delete()) {
                    $return = false;
                }
            }
        }
        if ($return) {
            $this->strOkMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATED_DATA_SUCCESSFULLY'];
        } else {
            $this->strErrMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATING_DATA_FAILED'];
        }
        return $return;
    }


    /**
     * Delete the marked Tickets
     * @return  boolean             True on success, false otherwise
     * @global  array       $_ARRAYLANG     Language array
     * @global  mixed       $objInit        Init object
     */
    function deleteTickets()
    {
        global $_ARRAYLANG, $objInit;

echo("deleteTickets(): \$_POST: ");var_export($_POST);echo("<br />");
        foreach ($_POST['selectedId'] as $id) {
            $objTicket =
                Ticket::getById($id);
            if (!$objTicket) {
                return false;
            }
            if (!$objTicket->delete()) {
                $this->strErrMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATING_DATA_FAILED'];
                return false;
            }
        }
        $this->strOkMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATED_DATA_SUCCESSFULLY'];
        return true;
    }


    /**
     * Set up the overview of the tickets in the system.
     * @global  array   $_ARRAYLANG Language array
     * @global  Init    $objInit    Init object
     */
    function ticketOverview()
    {
        global $_ARRAYLANG, $objInit;

        $this->pageTitle = $_ARRAYLANG['TXT_SUPPORT_SETTINGS'];
        $this->objTemplate->loadTemplateFile('module_support_ticket_overview.html', true, true);

        $supportCategoryId = 0;
        if (isset($_REQUEST['supportcategoryid'])) {
            $supportCategoryId = $_REQUEST['supportcategoryid'];
        }
        $order = 'date DESC';
        if (isset($_REQUEST['order'])) {
            $order = $_REQUEST['order'];
        }
        $offset = 0;
        if (isset($_REQUEST['offset'])) {
            $offset = $_REQUEST['offset'];
        }

        // get range of Tickets, default to latest first
        $arrTicket = Ticket::getTicketArray($order, $offset);
        // get all Support Categories' IDs and names
        $arrSupportCategoryName =
            $this->objSupportCategories->getSupportCategoryNameArray(0, true);
        foreach ($arrTicket as $objTicket) {
            $supportCategoryId = $objTicket->getSupportCategoryId();
            $this->objTemplate->setVariable(array(
                'SUPPORT_TICKET_ID'       => $objTicket->getId(),
                'SUPPORT_TICKET_EMAIL'    => $objTicket->getEmail(),
                'SUPPORT_TICKET_DATE'     => $objTicket->getDate(),
                // status: new, open, reopened, pending, closed, ...
                'SUPPORT_TICKET_STATUS'   => $objTicket->getStatus(),
                'SUPPORT_TICKET_CATEGORY' =>
                    $arrSupportCategoryName[$supportCategoryId],
            ));
        }
        $this->objTemplate->setVariable(array(
            'TXT_SUPPORT_CATEGORY'              => $_ARRAYLANG['TXT_SUPPORT_CATEGORY'],
            'TXT_SUPPORT_CONFIRM_CHANGE_STATUS' => $_ARRAYLANG['TXT_SUPPORT_CONFIRM_CHANGE_STATUS'],
            'TXT_SUPPORT_CONFIRM_DELETE_TICKET' => $_ARRAYLANG['TXT_SUPPORT_CONFIRM_DELETE_TICKET'],
            'TXT_SUPPORT_DELETE'                => $_ARRAYLANG['TXT_SUPPORT_DELETE'],
            'TXT_SUPPORT_EDIT'                  => $_ARRAYLANG['TXT_SUPPORT_EDIT'],
            'TXT_SUPPORT_EMAIL'                 => $_ARRAYLANG['TXT_SUPPORT_EMAIL'],
            'TXT_SUPPORT_MARKED_TICKETS'        => $_ARRAYLANG['TXT_SUPPORT_MARKED_TICKETS'],
            'TXT_SUPPORT_SEARCH_TICKETS'        => $_ARRAYLANG['TXT_SUPPORT_SEARCH_TICKETS'],
            'TXT_SUPPORT_SEND_MAIL_TO_CUSTOMER' => $_ARRAYLANG['TXT_SUPPORT_SEND_MAIL_TO_CUSTOMER'],
            'TXT_SUPPORT_SHOW_CLOSED_TICKETS'   => $_ARRAYLANG['TXT_SUPPORT_SHOW_CLOSED_TICKETS'],
            'TXT_SUPPORT_SORT_TICKET'           => $_ARRAYLANG['TXT_SUPPORT_SORT_TICKET'],
            'TXT_SUPPORT_TICKET_CATEGORY'       => $_ARRAYLANG['TXT_SUPPORT_TICKET_CATEGORY'],
            'TXT_SUPPORT_TICKET_DATE'           => $_ARRAYLANG['TXT_SUPPORT_TICKET_DATE'],
            'TXT_SUPPORT_TICKET_EMAIL'          => $_ARRAYLANG['TXT_SUPPORT_TICKET_EMAIL'],
            'TXT_SUPPORT_TICKET_ID'             => $_ARRAYLANG['TXT_SUPPORT_TICKET_ID'],
            'TXT_SUPPORT_TICKET_SOURCE'         => $_ARRAYLANG['TXT_SUPPORT_TICKET_SOURCE'],
            'TXT_SUPPORT_TICKET_STATUS'         => $_ARRAYLANG['TXT_SUPPORT_TICKET_STATUS'],
            'TXT_SUPPORT_VIEW_DETAILS'          => $_ARRAYLANG['TXT_SUPPORT_VIEW_DETAILS'],
            'TXT_SUPPORT_WEB'                   => $_ARRAYLANG['TXT_SUPPORT_WEB'],
        ));
    }


    /**
     * Set up the Ticket detail view.
     * @global  array   $_ARRAYLANG Language array
     * @global  Init    $objInit    Init object
     */
    function ticket()
    {
        global $_ARRAYLANG, $objInit;

        $this->pageTitle = $_ARRAYLANG['TXT_SUPPORT_TICKET'];
        $this->objTemplate->loadTemplateFile('module_support_ticket.html', true, true);

        $id = 0;
        if (isset($_REQUEST['id'])) {
            $id = $_REQUEST['id'];
        }

        // get the Ticket
        $objTicket = Ticket::getById($id);
        // The language ID of the language the Customer chose
        $languageId = $objTicket->getLanguageId();

// TODO FROM HERE

        // get all Support Categories' IDs and names
        $arrSupportCategoryName =
            $this->objSupportCategories->getSupportCategoryNameArray(0, true);
        foreach ($arrTicket as $objTicket) {
            $supportCategoryId = $objTicket->getSupportCategoryId();
            $this->objTemplate->setVariable(array(
                'SUPPORT_TICKET_ID'       => $objTicket->getId(),
                'SUPPORT_TICKET_EMAIL'    => $objTicket->getEmail(),
                'SUPPORT_TICKET_DATE'     => $objTicket->getDate(),
                'SUPPORT_TICKET_LANGUAGE' =>
                    $this->objLanguage->getLanguageParameter(
                        $objTicket->getLanguageId(), 'name'
                    ),
                // status: new, open, reopened, pending, closed, ...
                'SUPPORT_TICKET_STATUS'   => $objTicket->getStatusAsString(),
                'SUPPORT_TICKET_CATEGORY' =>
                    $arrSupportCategoryName[$supportCategoryId],
            ));
        }
        $this->objTemplate->setVariable(array(
            'TXT_SUPPORT_CATEGORY'              => $_ARRAYLANG['TXT_SUPPORT_CATEGORY'],
            'TXT_SUPPORT_CONFIRM_CHANGE_STATUS' => $_ARRAYLANG['TXT_SUPPORT_CONFIRM_CHANGE_STATUS'],
            'TXT_SUPPORT_CONFIRM_DELETE_TICKET' => $_ARRAYLANG['TXT_SUPPORT_CONFIRM_DELETE_TICKET'],
            'TXT_SUPPORT_DELETE'                => $_ARRAYLANG['TXT_SUPPORT_DELETE'],
            'TXT_SUPPORT_EDIT'                  => $_ARRAYLANG['TXT_SUPPORT_EDIT'],
            'TXT_SUPPORT_EMAIL'                 => $_ARRAYLANG['TXT_SUPPORT_EMAIL'],
            'TXT_SUPPORT_MARKED_TICKETS'        => $_ARRAYLANG['TXT_SUPPORT_MARKED_TICKETS'],
            'TXT_SUPPORT_SEARCH_TICKETS'        => $_ARRAYLANG['TXT_SUPPORT_SEARCH_TICKETS'],
            'TXT_SUPPORT_SEND_MAIL_TO_CUSTOMER' => $_ARRAYLANG['TXT_SUPPORT_SEND_MAIL_TO_CUSTOMER'],
            'TXT_SUPPORT_SHOW_CLOSED_TICKETS'   => $_ARRAYLANG['TXT_SUPPORT_SHOW_CLOSED_TICKETS'],
            'TXT_SUPPORT_SORT_TICKET'           => $_ARRAYLANG['TXT_SUPPORT_SORT_TICKET'],
            'TXT_SUPPORT_TICKET_CATEGORY'       => $_ARRAYLANG['TXT_SUPPORT_TICKET_CATEGORY'],
            'TXT_SUPPORT_TICKET_DATE'           => $_ARRAYLANG['TXT_SUPPORT_TICKET_DATE'],
            'TXT_SUPPORT_TICKET_EMAIL'          => $_ARRAYLANG['TXT_SUPPORT_TICKET_EMAIL'],
            'TXT_SUPPORT_TICKET_ID'             => $_ARRAYLANG['TXT_SUPPORT_TICKET_ID'],
            'TXT_SUPPORT_TICKET_SOURCE'         => $_ARRAYLANG['TXT_SUPPORT_TICKET_SOURCE'],
            'TXT_SUPPORT_TICKET_STATUS'         => $_ARRAYLANG['TXT_SUPPORT_TICKET_STATUS'],
            'TXT_SUPPORT_VIEW_DETAILS'          => $_ARRAYLANG['TXT_SUPPORT_VIEW_DETAILS'],
            'TXT_SUPPORT_WEB'                   => $_ARRAYLANG['TXT_SUPPORT_WEB'],
        ));
    }


    /**
     * Delete the Support Category
     *
     * Deletes the currently selected language only!
     * @return  boolean             True on success, false otherwise
     * @global  array       $_ARRAYLANG     Language array
     * @global  mixed       $objInit        Init object
     */
    function deleteCategory()
    {
        global $_ARRAYLANG, $objInit;

echo("deleteCategory(): \$_GET: ");var_export($_GET);echo("<br />");
        $return = true;

        // The ID of the Support Category currently being edited
        if (empty($_GET['id'])) {
            $return = false;
        } else {
            $id = intval($_GET['id']);
            $objSupportCategory =
                SupportCategory::getById($id, $this->editLanguageId);
            if (!$objSupportCategory) {
                $return = false;
            } else {
                if (!$objSupportCategory->delete(
                    $objSupportCategory->getLanguageId())
                ) {
                    $return = false;
                }
            }
        }
        if ($return) {
            $this->strOkMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATED_DATA_SUCCESSFULLY'];
        } else {
            $this->strErrMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATING_DATA_FAILED'];
        }
        return $return;
    }


    /**
     * Delete the marked Support Categories
     *
     * Deletes the currently selected language only!
     * @return  boolean             True on success, false otherwise
     * @global  array       $_ARRAYLANG     Language array
     * @global  mixed       $objInit        Init object
     */
    function deleteCategories()
    {
        global $_ARRAYLANG, $objInit;

echo("deleteCategories(): \$_POST: ");var_export($_POST);echo("<br />");
        foreach ($_POST['selectedId'] as $id) {
            $objSupportCategory =
                SupportCategory::getById($id, $this->editLanguageId);
            if (!$objSupportCategory) {
                return false;
            }
            if (!$objSupportCategory->delete($objSupportCategory->getLanguageId())) {
                $this->strErrMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATING_DATA_FAILED'];
                return false;
            }
        }
        $this->strOkMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATED_DATA_SUCCESSFULLY'];
        return true;
    }


    /**
     * Set up the page for viewing and editing the Support Categories.
     * @global  array   $_ARRAYLANG Language array
     * @global  Init    $objInit    Init object
     */
    function editCategories()
    {
        global $_ARRAYLANG, $objInit;

        $this->pageTitle = $_ARRAYLANG['TXT_SUPPORT_EDIT_CATEGORIES'];
        $this->objTemplate->loadTemplateFile('module_support_edit_categories.html', true, true);

        // The ID of the Support Category currently being edited
        $id = 0;
        if (!empty($_GET['id'])) {
            $id = intval($_GET['id']);
        }
        // The offset of the Support Category list being displayed
        // THIS IS NOT SUPPORTED FOR THE TIME BEING!
/*
        $offset = 0;
        if (!empty($_REQUEST['offset'])) {
            $offset = $_REQUEST['offset'];
        }
*/

        $this->objTemplate->setVariable(array(
            'TXT_SUPPORT_ACCEPT_CHANGES'            => $_ARRAYLANG['TXT_SUPPORT_ACCEPT_CHANGES'],
            'TXT_SUPPORT_ACTION'                    => $_ARRAYLANG['TXT_SUPPORT_ACTION'],
            'TXT_SUPPORT_ACTION_IS_IRREVERSIBLE'    => $_ARRAYLANG['TXT_SUPPORT_ACTION_IS_IRREVERSIBLE'],
            'TXT_SUPPORT_ACTIVE'                    => $_ARRAYLANG['TXT_SUPPORT_ACTIVE'],
            'TXT_SUPPORT_CATEGORIES'                => $_ARRAYLANG['TXT_SUPPORT_CATEGORIES'],
            'TXT_SUPPORT_CATEGORIES_COUNT'          => $_ARRAYLANG['TXT_SUPPORT_CATEGORIES_COUNT'],
            'TXT_SUPPORT_CATEGORIES_COUNT_TOTAL'    => $_ARRAYLANG['TXT_SUPPORT_CATEGORIES_COUNT_TOTAL'],
            'TXT_SUPPORT_CATEGORY_ID'               => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_ID'],
            'TXT_SUPPORT_CATEGORY_ORDER'            => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_ORDER'],
            'TXT_SUPPORT_CATEGORY_PARENT'           => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_PARENT'],
            'TXT_SUPPORT_CONFIRM_DELETE_CATEGORIES' => $_ARRAYLANG['TXT_SUPPORT_CONFIRM_DELETE_CATEGORIES'],
            'TXT_SUPPORT_CONFIRM_DELETE_CATEGORY'   => $_ARRAYLANG['TXT_SUPPORT_CONFIRM_DELETE_CATEGORY'],
            'TXT_SUPPORT_DELETE_CATEGORIES'         => $_ARRAYLANG['TXT_SUPPORT_DELETE_CATEGORIES'],
            'TXT_SUPPORT_DELETE_CATEGORY'           => $_ARRAYLANG['TXT_SUPPORT_DELETE_CATEGORY'],
            'TXT_SUPPORT_DELETE_MARKED'             => $_ARRAYLANG['TXT_SUPPORT_DELETE_MARKED'],
            'TXT_SUPPORT_MAKE_SELECTION'            => $_ARRAYLANG['TXT_SUPPORT_MAKE_SELECTION'],
            'TXT_SUPPORT_MARKED_CATEGORIES'         => $_ARRAYLANG['TXT_SUPPORT_MARKED_CATEGORIES'],
            'TXT_SUPPORT_NEW_CATEGORY'              => $_ARRAYLANG['TXT_SUPPORT_NEW_CATEGORY'],
            'TXT_SUPPORT_SELECT_ACTION'             => $_ARRAYLANG['TXT_SUPPORT_SELECT_ACTION'],
            'TXT_SUPPORT_SELECT_ALL'                => $_ARRAYLANG['TXT_SUPPORT_SELECT_ALL'],
            'TXT_SUPPORT_SELECT_NONE'               => $_ARRAYLANG['TXT_SUPPORT_SELECT_NONE'],
            'TXT_SUPPORT_STORE'                     => $_ARRAYLANG['TXT_SUPPORT_STORE'],
            'SUPPORT_CATEGORY_EDIT_LANGUAGE_MENU'   =>
                $this->objLanguage->getMenu(
                    $this->editLanguageId,
                    'editLanguageId',
                    "window.location.replace('index.php?cmd=support".
//                    "&amp;offset=$offset".
                    "&amp;act=editCategories&amp;id=$id".
                    "&amp;editLanguageId='+document.getElementById('editLanguageId').value);"
                ),
        ));
        $this->objTemplate->setGlobalVariable(array(
            'SUPPORT_CATEGORY_EDIT_LANGUAGE_ID'     => $this->editLanguageId,
//            'SUPPORT_CATEGORY_OFFSET'               => $offset,
            'TXT_SUPPORT_CATEGORY_LANGUAGE'         => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_LANGUAGE'],
            'TXT_SUPPORT_CATEGORY_NAME'             => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_NAME'],
            'TXT_SUPPORT_CATEGORY_STATUS'           => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_STATUS'],
            'TXT_SUPPORT_DELETE_CATEGORY'           => $_ARRAYLANG['TXT_SUPPORT_DELETE_CATEGORY'],
            'TXT_SUPPORT_EDIT_CATEGORY'             => $_ARRAYLANG['TXT_SUPPORT_EDIT_CATEGORY'],
            'TXT_SUPPORT_TICKETS_IN_CATEGORY'       => $_ARRAYLANG['TXT_SUPPORT_TICKETS_IN_CATEGORY'],
        ));

        // List Support Categories by language
        $arrSupportCategoryTree =
            $this->objSupportCategories->getSupportCategoryTreeArray(
                $this->editLanguageId
            );
        if ($arrSupportCategoryTree === false) {
echo("failed to get Support Category tree<br />");
        }
        $this->objTemplate->setCurrentBlock('supportCategoryRow');
        $i = 0;
        foreach ($arrSupportCategoryTree as $arrSupportCategory) {
            $this->objTemplate->setVariable(array(
                'SUPPORT_ROW_CLASS'         => (++$i % 2 ? 'row2' : 'row1'),
                'SUPPORT_CATEGORY_ID'       => $arrSupportCategory['id'],
                'SUPPORT_CATEGORY_STATUS_CHECKED' =>
                    ($arrSupportCategory['status']
                        ? ' checked="checked"'
                        : ''
                    ),
                'SUPPORT_CATEGORY_ORDER'    =>
                    ($arrSupportCategory['order']
                        ? $arrSupportCategory['order']
                        : 0
                    ),
                'SUPPORT_CATEGORY_LANGUAGE' =>
                    $this->objLanguage->getLanguageParameter(
                        $arrSupportCategory['languageId'], 'name'
                    ),
                'SUPPORT_CATEGORY_INDENT'   => str_repeat('|----', $arrSupportCategory['level']),
                'SUPPORT_CATEGORY_NAME'     => $arrSupportCategory['name'],
            ));
            $this->objTemplate->parseCurrentBlock();
        }

        // Edit Support Category
        $this->objTemplate->setCurrentBlock('editSupportCategory');
        if ($id) {
            // Select one by ID
echo("editCategories(): id is $id<br />");
            $objCategory = SupportCategory::getById($id, $this->editLanguageId);
            if ($objCategory) {
                // New/edit Support Category
                $this->objTemplate->setVariable(array(
                    'SUPPORT_CATEGORY_ID'             => $id,
                    'SUPPORT_CATEGORY_PARENTID'       =>
                        $this->objSupportCategories->getAdminMenu(
                            $objCategory->getLanguageId(),
                            $objCategory->getParentId()
                        ),
                    'SUPPORT_CATEGORY_STATUS_CHECKED' =>
                        ($objCategory->getStatus()
                            ? ' checked="checked"'
                            : ''
                        ),
                    'SUPPORT_CATEGORY_ORDER'          => $objCategory->getOrder(),
                    'SUPPORT_CATEGORY_LANGUAGE_MENU'  =>
                        $this->objLanguage->getMenu(
                            $objCategory->getLanguageId(),
                            'languageId',
                            "document.forms.supportEditCategoryForm.submit()"
                    ),
                    'SUPPORT_CATEGORY_NAME'           => $objCategory->getName(),
                ));
            }
        } else {
            // Default values
            $this->objTemplate->setVariable(array(
                'SUPPORT_CATEGORY_ID'             => 0,
                    'SUPPORT_CATEGORY_PARENTID'   =>
                        $this->objSupportCategories->getAdminMenu(
                            $this->editLanguageId
                        ),
                'SUPPORT_CATEGORY_STATUS_CHECKED' => ' checked="checked"',
                'SUPPORT_CATEGORY_ORDER'          => 0,
                'SUPPORT_CATEGORY_LANGUAGE_MENU'  =>
                    $this->objLanguage->getMenu($this->editLanguageId),
                'SUPPORT_CATEGORY_NAME'           => '',
            ));
        }
        $this->objTemplate->parseCurrentBlock();

//        $this->objTemplate->parse();
        // success!
        return true;
    }


    /**
     * Store the Support Category currently being edited.
     *
     * Note that the Support Category tree array in the SupportCategories
     * object will be outdated after inserting a new SupportCategory.  Don't
     * forget to reinitialize it, or you won't see the new entry!
     * @return  boolean             True on success, false otherwise
     * @global  array   $_ARRAYLANG Language array
     */
    function storeSupportCategory()
    {
        global $_ARRAYLANG;

        if (empty($_POST['name'])) {
            $this->strErrMessage = $_ARRAYLANG['TXT_SUPPORT_FILL_IN_CATEGORY_NAME'];
            return false;
        }
//echo("POST: ");var_export($_POST);echo("<br />");
        $supportCategoryName = $_POST['name'];
        $supportCategoryParentId = $_POST['parentId'];
        $supportCategoryStatus =
            (!empty($_POST['status'])
                ? $_POST['status']
                : 0
            );
        $supportCategoryId =
            (!empty($_POST['id'])
                ? $_POST['id']
                : 0
            );
        $supportCategoryLanguageId =
            (!empty($_POST['languageId'])
                ? $_POST['languageId']
                : 0
            );
        $supportCategoryOrder =
            (!empty($_POST['order'])
                ? $_POST['order']
                : 0
            );
        $objSupportCategory = new SupportCategory(
            $supportCategoryName,
            $supportCategoryLanguageId,
            $supportCategoryParentId,
            $supportCategoryStatus,
            $supportCategoryOrder,
            $supportCategoryId
        );
        if (!$objSupportCategory) {
            return false;
        }
echo("storeSupportCategory(): ");var_export($objSupportCategory);echo("<br />");
        if ($objSupportCategory->store()) {
            $this->strOkMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATED_DATA_SUCCESSFULLY'];
            return true;
        } else {
            $this->strErrMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATING_DATA_FAILED'];
        }
        return false;
    }


    /**
     * Store all changes made to the Support Categories shown.
     * @return  boolean             True on success, false otherwise
     * @global  array       $_ARRAYLANG     Language array
     * @global  mixed       $objInit        Init object
     */
    function storeSupportCategories()
    {
        global $_ARRAYLANG, $objInit;

        $arrSupportCategoryTree =
            $this->objSupportCategories->getSupportCategoryTreeArray(
                $this->editLanguageId
            );
        $return = true;

        foreach ($arrSupportCategoryTree as $arrSupportCategory) {
            $id     = $arrSupportCategory['id'];
            $order  = $arrSupportCategory['order'];
            $status = $arrSupportCategory['status'];
            $postOrder  = $_POST['order'][$id];
            $postStatus =
                (!empty($_POST['status'][$id])
                    ? $_POST['status'][$id]
                    : 0
                );
            if (   !empty($_POST['id'][$id])
                && (   $order  != $postOrder
                    || $status != $postStatus)
            ) {
                $objSupportCategory = SupportCategory::getById($id);
                if (!$objSupportCategory) {
                    $this->strErrMessage .=
                        ($this->strErrMessage ? '<br />' : '').
                        $_ARRAYLANG['TXT_SUPPORT_UPDATING_DATA_FAILED'].
                        ', ???';
                    $return = false;
                } else {
                    $objSupportCategory->setOrder($postOrder);
                    $objSupportCategory->setStatus($postStatus);
                    if (!$objSupportCategory->store()) {
                        $this->strErrMessage .=
                            ($this->strErrMessage ? '<br />' : '').
                            $_ARRAYLANG['TXT_SUPPORT_UPDATING_DATA_FAILED'].
                            ', '.$objSupportCategory->getName();
                        $return = false;
                    }
                }
            }
        }
        if ($return) {
            $this->strOkMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATED_DATA_SUCCESSFULLY'];
        }
        return $return;
    }
}

?>
