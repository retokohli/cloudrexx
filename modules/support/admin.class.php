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
 * Provides clickable table headers for sorting
 */
require_once ASCMS_CORE_PATH.'/Sorting.class.php';

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
     * @global  Template    $objTemplate    PEAR Sigma Template
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
                '<a href="index.php?cmd=support&amp;act=enterMessage">'.
                $_ARRAYLANG['TXT_SUPPORT_CREATE_TICKET'].'</a>'.
                '<a href="index.php?cmd=support&amp;act=enterMessage">'.
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
          case 'enterMessage':
            $this->enterMessage();
            break;
          case 'deleteTickets':
            $this->deleteTickets();
            $this->ticketOverview();
            break;
          case 'ticket':
            $this->ticket();
            break;
          case 'changeTicket':
            $this->changeTicket();
            $this->ticket();
            break;
          case 'replyTicket':
            $this->replyTicket();
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
     * Enter a new Message into the system
     *
     * If there is no associated Ticket ID, as a consequence, a new
     * Ticket will be created as well.  Otherwise, the Message can be
     * used as a reply to an open Ticket.
     * @global  array   $_ARRAYLANG Language array
     * @global  Init    $objInit    Init object
     */
    function enterMessage()
    {
        global $_ARRAYLANG, $objInit;

        $this->pageTitle = $_ARRAYLANG['TXT_SUPPORT_ENTER_MESSAGE'];
        $this->objTemplate->loadTemplateFile('module_support_enter_message.html', true, true);

        // Any Ticket around?
        $ticketId = 0;
        if (!empty($_REQUEST['ticketId'])) {
            $ticketId = intval($_REQUEST['ticketId']);
            $this->showTicket($ticketId);
            $this->showMessages($ticketId);
        }

        // The Message
        $supportMessageStatusString = '';
        $supportMessageFrom         = '';
        $supportMessageSubject      = '';
        $supportMessageBody         = '';
        $supportMessageDate         = '';

        // Any Message selected?
        $messageId = 0;
        if (!empty($_REQUEST['messageId'])) {
            $messageId  = intval($_REQUEST['messageId']);
            $objMessage = Message::getById($messageId);
            if (!$objMessage) {
echo("Support::enterMessage(): ERROR: No Message found for Message ID $messageId<br />");
                return false;
            }
            $supportMessageStatusString = $objMessage->getStatusString();
            $supportMessageFrom         = $objMessage->getFrom();
            $supportMessageSubject      = $objMessage->getSubject();
            $supportMessageBody         = $objMessage->getBody();
            $supportMessageDate         = $objMessage->getDate();
        } else {
            if (!empty($_REQUEST['supportMessageFrom'])) {
                $supportMessageFrom = intval($_REQUEST['supportMessageFrom']);
            }
            if (!empty($_REQUEST['supportMessageSubject'])) {
                $supportMessageSubject = $_REQUEST['supportMessageSubject'];
            }
            if (!empty($_REQUEST['supportMessageBody'])) {
                $supportMessageBody = $_REQUEST['supportMessageBody'];
            }
            if (!empty($_REQUEST['supportMessageDate'])) {
                $supportMessageDate =  $_REQUEST['supportMessageDate'];
            }
        }

        $this->objTemplate->setVariable(array(
            'SUPPORT_MESSAGE_STATUS'    => $supportMessageStatusString,
            'SUPPORT_MESSAGE_FROM'      => $supportMessageFrom,
            'SUPPORT_MESSAGE_SUBJECT'   => $supportMessageSubject,
            'SUPPORT_MESSAGE_BODY'      => $supportMessageBody,
            'SUPPORT_MESSAGE_DATE'      => $supportMessageDate,
        ));

        $this->objTemplate->setVariable(array(
            'TXT_SUPPORT_CATEGORY'              => $_ARRAYLANG['TXT_SUPPORT_CATEGORY'],
            'TXT_SUPPORT_CONFIRM_CHANGE_STATUS' => $_ARRAYLANG['TXT_SUPPORT_CONFIRM_CHANGE_STATUS'],
            'TXT_SUPPORT_CONFIRM_DELETE_TICKET' => $_ARRAYLANG['TXT_SUPPORT_CONFIRM_DELETE_TICKET'],
            'TXT_SUPPORT_DELETE'                => $_ARRAYLANG['TXT_SUPPORT_DELETE'],
            'TXT_SUPPORT_EDIT'                  => $_ARRAYLANG['TXT_SUPPORT_EDIT'],
            'TXT_SUPPORT_EMAIL'                 => $_ARRAYLANG['TXT_SUPPORT_EMAIL'],
            'TXT_SUPPORT_TICKET_MARKED'        => $_ARRAYLANG['TXT_SUPPORT_TICKET_MARKED'],
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
        return true;
    }


    /**
     * Delete the chosen Ticket
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
     * @global  array   $_CONFIG    Global cofiguration array
     */
    function ticketOverview()
    {
        global $_ARRAYLANG, $objInit;

        $baseUri = '?cmd=support&amp;act=ticketOverview';

        $this->pageTitle = $_ARRAYLANG['TXT_SUPPORT_TICKET_OVERVIEW'];
        $this->objTemplate->loadTemplateFile('module_support_ticket_overview.html', true, true);

        $offset = 0;
        if (!empty($_REQUEST['offset'])) {
            $offset = intval($_REQUEST['offset']);
        }

        // Ticket filtering parameters
        $supportTicketCategoryId = 0;
        if (!empty($_REQUEST['supportTicketCategoryId'])) {
            $supportTicketCategoryId = intval($_REQUEST['supportTicketCategoryId']);
        }
        $supportTicketLanguageId = 0;
        if (!empty($_REQUEST['supportTicketLanguageId'])) {
            $supportTicketLanguageId = intval($_REQUEST['supportTicketLanguageId']);
        }
        $supportTicketOwnerId = 0;
        if (!empty($_REQUEST['supportTicketOwnerId'])) {
            $supportTicketOwnerId = intval($_REQUEST['supportTicketOwnerId']);
        }
        $supportTicketStatus = -1;
        if (!empty($_REQUEST['supportTicketStatus'])) {
            $supportTicketStatus = intval($_REQUEST['supportTicketStatus']);
        }
        $supportTicketSource = -1;
        if (!empty($_REQUEST['supportTicketSource'])) {
            $supportTicketSource = intval($_REQUEST['supportTicketSource']);
        }
        $supportTicketEmail = '';
        if (!empty($_REQUEST['supportTicketEmail'])) {
            $supportTicketEmail = $_REQUEST['supportTicketEmail'];
        }
        $supportTicketSearchTerm = '';
        if (!empty($_REQUEST['supportTicketSearchTerm'])) {
            $supportTicketSearchTerm =
                contrexx_stripslashes($_REQUEST['supportTicketSearchTerm']);
        }
/*        $supportTicketShowOwnOnly = 1;
        if (empty($_REQUEST['supportTicketShowOwnOnly'])) {
            $supportTicketShowOwnOnly = 0;
        }*/
        $supportTicketShowClosed = 0;
        if (!empty($_REQUEST['supportTicketShowClosed'])) {
            $supportTicketShowClosed = 1;
        }

        $objSorting = new Sorting(
            $baseUri,
            array(
                'timestamp', 'id', 'status', 'email',
                'support_category_id', 'language_id', 'owner_id'
            ),
            array(
                $_ARRAYLANG['TXT_SUPPORT_TICKET_DATE'],
                $_ARRAYLANG['TXT_SUPPORT_TICKET_ID'],
                $_ARRAYLANG['TXT_SUPPORT_TICKET_STATUS'],
                $_ARRAYLANG['TXT_SUPPORT_TICKET_EMAIL'],
                $_ARRAYLANG['TXT_SUPPORT_TICKET_CATEGORY'],
                $_ARRAYLANG['TXT_SUPPORT_TICKET_LANGUAGE'],
                $_ARRAYLANG['TXT_SUPPORT_TICKET_OWNER'],
            ),
            false
        );

        // get all Support Categories' IDs and names
        $arrSupportCategoryName =
            $this->objSupportCategories->getSupportCategoryNameArray(
                0, true, false
            );

        // Get total Ticket count
        $ticketCount = Ticket::getRecordCount(
            $supportTicketCategoryId,
            $supportTicketLanguageId,
            $supportTicketOwnerId,
            $supportTicketStatus,
            $supportTicketSource,
            $supportTicketEmail,
            $supportTicketSearchTerm
        );
        // get range of Tickets, default to latest first
        $arrTicket = Ticket::getTicketArray(
            $supportTicketCategoryId,
            $supportTicketLanguageId,
            $supportTicketOwnerId,
            $supportTicketStatus,
            $supportTicketSource,
            $supportTicketEmail,
            $supportTicketSearchTerm,
            $objSorting->getOrder(),
            $offset
        );

        $this->objTemplate->setVariable(array(
            'HEADER_SUPPORT_TICKET_ID'          => $objSorting->getHeaderForField('id'),
            'HEADER_SUPPORT_TICKET_DATE'        => $objSorting->getHeaderForField('timestamp'),
            'HEADER_SUPPORT_TICKET_STATUS'      => $objSorting->getHeaderForField('status'),
            'HEADER_SUPPORT_TICKET_EMAIL'       => $objSorting->getHeaderForField('email'),
            'HEADER_SUPPORT_TICKET_CATEGORY'    => $objSorting->getHeaderForField('support_category_id'),
            'HEADER_SUPPORT_TICKET_LANGUAGE'    => $objSorting->getHeaderForField('language_id'),
            'HEADER_SUPPORT_TICKET_OWNER'       => $objSorting->getHeaderForField('owner_id'),
            'TXT_SUPPORT_TICKETS_FILTER'        => $_ARRAYLANG['TXT_SUPPORT_TICKETS_FILTER'],
            'TXT_SUPPORT_TICKET_CATEGORY'       => $_ARRAYLANG['TXT_SUPPORT_TICKET_CATEGORY'],
            'TXT_SUPPORT_TICKET_CONFIRM_DELETE' => $_ARRAYLANG['TXT_SUPPORT_TICKET_CONFIRM_DELETE'],
            'TXT_SUPPORT_DELETE'                => $_ARRAYLANG['TXT_SUPPORT_DELETE'],
            'TXT_SUPPORT_ALL'                   => $_ARRAYLANG['TXT_SUPPORT_ALL'],
            'TXT_SUPPORT_TICKET_EMAIL'          => $_ARRAYLANG['TXT_SUPPORT_TICKET_EMAIL'],
            'TXT_SUPPORT_TICKET_MARKED'         => $_ARRAYLANG['TXT_SUPPORT_TICKET_MARKED'],
            'TXT_SUPPORT_TICKET_UPDATE'         => $_ARRAYLANG['TXT_SUPPORT_TICKET_UPDATE'],
//            'TXT_SUPPORT_TICKET_SEARCH_TERM'    => $_ARRAYLANG['TXT_SUPPORT_TICKET_SEARCH_TERM'],
            'TXT_SUPPORT_TICKET_STATUS'         => $_ARRAYLANG['TXT_SUPPORT_TICKET_STATUS'],
            'TXT_SUPPORT_TICKET_LANGUAGE'       => $_ARRAYLANG['TXT_SUPPORT_TICKET_LANGUAGE'],
            'TXT_SUPPORT_TICKET_OWNER'          => $_ARRAYLANG['TXT_SUPPORT_TICKET_OWNER'],
            'TXT_SUPPORT_TICKET_SOURCE'         => $_ARRAYLANG['TXT_SUPPORT_TICKET_SOURCE'],
            'TXT_SUPPORT_VIEW_DETAILS'          => $_ARRAYLANG['TXT_SUPPORT_VIEW_DETAILS'],
//            'TXT_SUPPORT_TICKET_SHOW_OWN_ONLY'  => $_ARRAYLANG['TXT_SUPPORT_TICKET_SHOW_OWN_ONLY'],
            'TXT_SUPPORT_TICKET_SHOW_CLOSED'    => $_ARRAYLANG['TXT_SUPPORT_TICKET_SHOW_CLOSED'],
            'TXT_SUPPORT_TICKET_MARKED'         => $_ARRAYLANG['TXT_SUPPORT_TICKET_MARKED'],
            'TXT_SUPPORT_SELECT_ALL'            => $_ARRAYLANG['TXT_SUPPORT_SELECT_ALL'],
            'TXT_SUPPORT_SELECT_NONE'           => $_ARRAYLANG['TXT_SUPPORT_SELECT_NONE'],
            'TXT_SUPPORT_SELECT_ACTION'         => $_ARRAYLANG['TXT_SUPPORT_SELECT_ACTION'],
            'TXT_SUPPORT_TICKET_CONFIRM_DELETE' => $_ARRAYLANG['TXT_SUPPORT_TICKET_CONFIRM_DELETE'],
            'TXT_SUPPORT_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_SUPPORT_ACTION_IS_IRREVERSIBLE'],
            'TXT_SUPPORT_MAKE_SELECTION'        => $_ARRAYLANG['TXT_SUPPORT_MAKE_SELECTION'],
            'SUPPORT_TICKET_SEARCH_TERM'        => htmlspecialchars($supportTicketSearchTerm),
/*            'SUPPORT_TICKET_SHOW_OWN_ONLY_CHECK'     =>
                ($supportTicketShowOwnOnly ? 'checked="checked"' : ''),*/
            'SUPPORT_TICKET_SHOW_CLOSED_CHECK'  =>
                ($supportTicketShowClosed  ? 'checked="checked"' : ''),
            'SUPPORT_TICKET_CATEGORY'           =>
                $this->objSupportCategories->getAdminMenu(
                    $supportTicketLanguageId,
                    $supportTicketCategoryId
                ),
            'SUPPORT_TICKET_LANGUAGE'           =>
                $this->objLanguage->getMenu($supportTicketLanguageId),
            'SUPPORT_TICKET_STATUS'             =>
                Ticket::getStatusMenu($supportTicketStatus),
            'SUPPORT_TICKET_SOURCE'             =>
                Ticket::getSourceMenu($supportTicketSource),
            'SUPPORT_PAGING'                    =>
                getPaging(
                    $ticketCount, $offset,
                    $baseUri.$objSorting->getOrderUriEncoded(), ''
                ),
        ));

        if (is_array($arrTicket) && count($arrTicket)) {
            $this->objTemplate->setCurrentBlock('ticketRow');
            foreach ($arrTicket as $objTicket) {
                $supportCategoryId = $objTicket->getSupportCategoryId();
                $this->objTemplate->setVariable(array(
                    'SUPPORT_TICKET_ID'       => $objTicket->getId(),
                    'SUPPORT_TICKET_EMAIL'    => $objTicket->getEmail(),
                    'SUPPORT_TICKET_DATE'     => $objTicket->getTimestamp(),
                    'SUPPORT_TICKET_STATUS'   => $objTicket->getStatus(),
                    'SUPPORT_TICKET_CATEGORY' =>
                        $arrSupportCategoryName[$supportCategoryId],
                    'SUPPORT_TICKET_OWNER'    =>
                        Auth::getFullName($objTicket->getOwnerId),
                    'SUPPORT_TICKET_LANGUAGE' =>
                        FWLanguage::getLanguageParameter(
                            $objTicket->getLanguageId(), 'name'
                        ),
                ));
                $this->objTemplate->parseCurrentBlock();
            }
        } else {
            $this->objTemplate->hideBlock('ticketRow');
        }
    }


    /**
     * Show the Ticket detail page.
     * @return  void
     * @global  array   $_ARRAYLANG Language array
     * @global  Init    $objInit    Init object
     */
    function ticketDetail()
    {
        global $_ARRAYLANG, $objInit;

        $this->pageTitle = $_ARRAYLANG['TXT_SUPPORT_TICKET'];
        $this->objTemplate->loadTemplateFile('module_support_ticket_detail.html', true, true);
        $this->showTicket();
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
                $this->editLanguageId, false
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
                    $this->objLanguage->getMenu(
                        $this->editLanguageId,
                        'languageId'
                    ),
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
                $this->editLanguageId, false
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
/*  ignore
                    $this->strErrMessage .=
                        ($this->strErrMessage ? '<br />' : '').
                        $_ARRAYLANG['TXT_SUPPORT_UPDATING_DATA_FAILED'].
                        ', ???';
                    $return = false;
*/
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


    /**
     * Set up the Ticket detail view.
     *
     * This method may be called on any page that includes the
     * appropriate block and placeholders.
     * @global  array   $_ARRAYLANG Language array
     * @global  Init    $objInit    Init object
     */
    function showTicket()
    {
        global $_ARRAYLANG, $objInit;

        $ticketId = 0;
        if (!empty($_REQUEST['ticketId'])) {
            $ticketId = intval($_REQUEST['ticketId']);
        }
        if (!$ticketId) {
echo("Support::showTicket(): ERROR: Missing the Ticket ID!<br />");
            return false;
        }
        // get the Ticket
        $objTicket = Ticket::getById($ticketId);
        if (!$objTicket) {
echo("Support::showTicket(): ERROR: Could not get the Ticket with ID $ticketId!<br />");
            return false;
        }

        // get all Support Categories' IDs and names
        $arrSupportCategoryName =
            $this->objSupportCategories->getSupportCategoryNameArray(
                0, true, false
            );

        // The Support Ticket details
        $ticketEmail            = $objTicket->getEmail();
        $ticketDate             = $objTicket->getDate();
        $ticketLanguageId       = $objTicket->getLanguageId();
        $ticketLanguageString   =
            $this->objLanguage->getLanguageParameter(
                $ticketLanguageId, 'name'
            );
        $ticketStatusString     = $objTicket->getStatusString();
        $ticketCategoryId       = $objTicket->getSupportCategoryId();
        $ticketCategoryString   = $arrSupportCategoryName[$ticketCategoryId];

        $this->objTemplate->setVariable(array(
            'SUPPORT_TICKET_ID'       => $ticketId,
            'SUPPORT_TICKET_EMAIL'    => $ticketEmail,
            'SUPPORT_TICKET_DATE'     => $ticketDate,
            'SUPPORT_TICKET_LANGUAGE' => $ticketLanguageString,
            'SUPPORT_TICKET_STATUS'   => $ticketStatusString,
            'SUPPORT_TICKET_CATEGORY' => $ticketCategoryString,
        ));

        $this->objTemplate->setVariable(array(
            'TXT_SUPPORT_CATEGORY'              => $_ARRAYLANG['TXT_SUPPORT_CATEGORY'],
            'TXT_SUPPORT_CONFIRM_DELETE_TICKET' => $_ARRAYLANG['TXT_SUPPORT_CONFIRM_DELETE_TICKET'],
            'TXT_SUPPORT_DELETE'                => $_ARRAYLANG['TXT_SUPPORT_DELETE'],
            'TXT_SUPPORT_EDIT'                  => $_ARRAYLANG['TXT_SUPPORT_EDIT'],
            'TXT_SUPPORT_EMAIL'                 => $_ARRAYLANG['TXT_SUPPORT_EMAIL'],
            'TXT_SUPPORT_TICKET_CATEGORY'       => $_ARRAYLANG['TXT_SUPPORT_TICKET_CATEGORY'],
            'TXT_SUPPORT_TICKET_DATE'           => $_ARRAYLANG['TXT_SUPPORT_TICKET_DATE'],
            'TXT_SUPPORT_TICKET_EMAIL'          => $_ARRAYLANG['TXT_SUPPORT_TICKET_EMAIL'],
            'TXT_SUPPORT_TICKET_ID'             => $_ARRAYLANG['TXT_SUPPORT_TICKET_ID'],
            'TXT_SUPPORT_TICKET_SOURCE'         => $_ARRAYLANG['TXT_SUPPORT_TICKET_SOURCE'],
            'TXT_SUPPORT_TICKET_STATUS'         => $_ARRAYLANG['TXT_SUPPORT_TICKET_STATUS'],
            'TXT_SUPPORT_WEB'                   => $_ARRAYLANG['TXT_SUPPORT_WEB'],
        ));
        return true;
    }


    /**
     * Sets up the Messages list view on any page that includes the
     * appropriate block and placeholders.
     *
     * A range of optional parameters lets you choose the Messages to display.
     * Also picks a few standard options from the $_REQUEST array.
     * See {@link Messages::getMessageArray()} for a detailed explanation.
     * @param   integer     $ticketId       The Ticket ID
     * @param   integer     $status         The optional status filter value
     * @param   string      $from           The optional from filter value
     * @param   string      $subject        The optional subject filter value
     * @param   string      $date           The optional date filter value
     * @return  boolean         True on success, false otherwise.
     * @global  array       $_ARRAYLANG     Language array
     */
    function showMessages($ticketId, $status=0, $from='', $subject='', $date='')
    {
        global $_ARRAYLANG;

        $offset = 0;
        if (!empty($_REQUEST['offset'])) {
            $offset = intval($_REQUEST['offset']);
        }
        $limit = 0;
        if (!empty($_REQUEST['limit'])) {
            $limit = intval($_REQUEST['limit']);
        }
        $order = '';
        if (!empty($_GET['order'])) {
            $order = html_entity_decode(stripslashes($_GET['order']));
echo("Support::showMessages(ticketId=$ticketId, status=$status, from=$from, subject=$subject, date=$date): order: GET $order '".htmlentities($order)."'<br />");
        } elseif (!empty($_POST['order'])) {
            $order = stripslashes($_GET['order']);
echo("Support::showMessages(ticketId=$ticketId, status=$status, from=$from, subject=$subject, date=$date): order: POST $order '".htmlentities($order)."'<br />");
        }

        $arrMessages = Message::getMessageArray(
            $ticketId(),
            $status, $from, $subject, $date,
            $order, $offset, $limit
        );
        if (!$arrMessages) {
echo("Support::showMessages(ticketId=$ticketId, status=$status, from=$from, subject=$subject, date=$date): ERROR: got no Message array!<br />");
            return false;
        }
        $this->objTemplate->setCurrentBlock('messageRow');
        foreach ($arrMessages as $objMessage) {
            $this->objTemplate->setVariable(array(
                'SUPPORT_MESSAGE_ID'        => $objMessage->getId(),
                'SUPPORT_MESSAGE_STATUS'    => $objMessage->getStatus(),
                'SUPPORT_MESSAGE_FROM'      => $objMessage->getFrom(),
                'SUPPORT_MESSAGE_SUBJECT'   => $objMessage->getSubject(),
                'SUPPORT_MESSAGE_DATE'      => $objMessage->getDate(),
            ));
        	$this->objTemplate->parseCurrentBlock();
        }

        $this->objTemplate->setVariable(array(
            'TXT_SUPPORT_MESSAGES'          => $_ARRAYLANG['TXT_SUPPORT_MESSAGES'],
            'TXT_SUPPORT_MESSAGE_STATUS'    => $_ARRAYLANG['TXT_SUPPORT_MESSAGE_STATUS'],
            'TXT_SUPPORT_MESSAGE_FROM'      => $_ARRAYLANG['TXT_SUPPORT_MESSAGE_FROM'],
            'TXT_SUPPORT_MESSAGE_SUBJECT'   => $_ARRAYLANG['TXT_SUPPORT_MESSAGE_SUBJECT'],
            'TXT_SUPPORT_MESSAGE_DATE'      => $_ARRAYLANG['TXT_SUPPORT_MESSAGE_DATE'],
            'SUPPORT_MESSAGE_OFFSET'        => $offset,
            'SUPPORT_MESSAGE_LIMIT'         => $limit,
            'SUPPORT_MESSAGE_ORDER'         => $order,
        ));

        return true;
    }


    /**
     * Sets up the single Message view on any page that includes the
     * appropriate block and placeholders.
     *
     * Returns true if the Message is displayed successfully, false if
     * the Message ID is missing or if there is some other problem.
     * @param   integer     $messageId      The Message ID
     * @return  boolean                     True on success, false otherwise.
     * @global  array       $_ARRAYLANG     Language array
     */
    function showMessage()
    {
        // Any Message selected?
        $messageId = 0;
        if (!empty($_REQUEST['messageId'])) {
            $messageId = intval($_REQUEST['messageId']);
        } else {
echo("Support::showMessage(): ERROR: No message ID present!<br />");
            return false;
        }
        $objMessage = Message::getById($messageId);
        if (!$objMessage) {
echo("Support::showMessage(): ERROR: Could not get Message with ID $messageId!<br />");
            return false;
        }

        // If the person owning the associated Ticket reads a Message with
        // status NEW, the status must be toggled to READ.
        if ($objMessage->getStatus() == SUPPORT_MESSAGE_STATUS_NEW)
        // Selected message details
        $supportMessageStatus   = $objMessage->getStatus();
        $supportMessageFrom     = $objMessage->getFrom();
        $supportMessageSubject  = $objMessage->getSubject();
        $supportMessageBody     = $objMessage->getBody();
        $supportMessageDate     = $objMessage->getDate();

        $this->objTemplate->setVariable(array(
            'SUPPORT_MESSAGE_STATUS'    => $supportMessageStatus,
            'SUPPORT_MESSAGE_FROM'      => $supportMessageFrom,
            'SUPPORT_MESSAGE_SUBJECT'   => $supportMessageSubject,
            'SUPPORT_MESSAGE_BODY'      => $supportMessageBody,
            'SUPPORT_MESSAGE_DATE'      => $supportMessageDate,
        ));
        return true;
    }
}

?>
