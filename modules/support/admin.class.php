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
    var $strOkMessage = '';

    /**
     * @access  private
     * @var     string
     */
    var $strErrMessage = '';

    /**
     * The title of the current page
     * @var string
     */
    var $pageTitle = 'NO TITLE SET!';

    /**
     * The modulo counter used to alternatingly colour the table rows
     * @var integer
     */
    var $moduloRow = 0;

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

        $this->objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/support/template');
        $this->objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $this->objTemplate->loadTemplateFile('module_support_main.html');
        $objTemplate->setVariable(
            'CONTENT_NAVIGATION',
                '<a href="index.php?cmd=support&amp;act=ticketTable">'.
                $_ARRAYLANG['TXT_SUPPORT_TICKETS'].'</a>'.
                '<a href="index.php?cmd=support&amp;act=editCategories">'.
                $_ARRAYLANG['TXT_SUPPORT_CATEGORIES'].'</a>'.
                '<a href="index.php?cmd=support&amp;act=messageEdit">'.
                $_ARRAYLANG['TXT_SUPPORT_TICKET_NEW'].'</a>'.
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
          case 'editCategories':
            $this->objTemplate->setVariable('SUPPORT_CENTER', $this->categoriesEdit());
            break;
          case 'categoryStore':
            $this->categoryStore();
            $this->objTemplate->setVariable('SUPPORT_CENTER', $this->categoriesEdit());
            break;
          case 'categoriesStore':
            $this->categoriesStore();
            $this->objTemplate->setVariable('SUPPORT_CENTER', $this->categoriesEdit());
            break;
          case 'categoryDelete':
            $this->categoryDelete();
            $this->objTemplate->setVariable('SUPPORT_CENTER', $this->categoriesEdit());
            break;
          case 'categoriesDelete':
            $this->categoriesDelete();
            $this->objTemplate->setVariable('SUPPORT_CENTER', $this->categoriesEdit());
            break;
          case 'ticketData':
            $this->objTemplate->setVariable('SUPPORT_TOPCENTER', $this->ticketData());
            $this->objTemplate->setVariable('SUPPORT_CENTER', $this->messageTable());
            break;
          case 'ticketReply':
            $this->ticketReply();
            $this->objTemplate->setVariable('SUPPORT_CENTER', $this->ticketTable());
            break;
          case 'ticketChange':
            $this->ticketChange();
            $this->objTemplate->setVariable('SUPPORT_TOPCENTER', $this->ticketData());
            $this->objTemplate->setVariable('SUPPORT_CENTER', $this->messageTable());
            break;
          case 'ticketDelete':
            $this->ticketDelete();
            $this->objTemplate->setVariable('SUPPORT_CENTER', $this->ticketTable());
            break;
          case 'ticketsDelete':
            $this->ticketsDelete();
            $this->objTemplate->setVariable('SUPPORT_CENTER', $this->ticketTable());
            break;
          case 'messageData':
            $this->objTemplate->setVariable('SUPPORT_CENTER', $this->messageData());
            break;
          case 'messageEdit':
            $this->objTemplate->setVariable('SUPPORT_CENTER', $this->messageEdit());
            break;
          case 'messageCommit':
            $this->messageCommit();
            $this->objTemplate->setVariable('SUPPORT_CENTER', $this->ticketTable());
            break;
          default:
            $this->objTemplate->setVariable('SUPPORT_CENTER', $this->ticketTable());
            break;
        }
        $objTemplate->setVariable(array(
            'CONTENT_TITLE'          => $this->pageTitle,
            'CONTENT_OK_MESSAGE'     => $this->strOkMessage,
            'CONTENT_STATUS_MESSAGE' => $this->strErrMessage,
            'ADMIN_CONTENT'          => $this->objTemplate->get()
        ));
    }


    /**
     * Commit the posted Message.
     *
     * Causes all related data to be updated.
     * @return  boolean             True on success, false otherwise.
     * @global  Init    $objInit    Init object
     */
    function messageCommit()
    {
        global $objInit;

        $supportTicketId   = 0;
        $objTicket  = false;
        $supportCategoryId =  0;
        if (   empty($_REQUEST['supportCategoryId'])
            || $_REQUEST['supportCategoryId'] <= 0) {
echo("messageCommit(): No or invalid Support Category ID ($supportCategoryId)!<br />");
        } else {
            $supportCategoryId = intval($_REQUEST['supportCategoryId']);
        }
        $supportMessageBody     = '';
        if (!empty($_REQUEST['supportMessageBody'])) {
            $supportMessageBody     = $_REQUEST['supportMessageBody'];
        }
        $supportMessageFrom     = '';
        if (!empty($_REQUEST['supportMessageFrom'])) {
            $supportMessageFrom     = $_REQUEST['supportMessageFrom'];
        }
        $supportMessageSubject  = '';
        if (!empty($_REQUEST['supportMessageSubject'])) {
            $supportMessageSubject  = $_REQUEST['supportMessageSubject'];
        }

        // Any Ticket selected?
        if (!empty($_REQUEST['supportTicketId'])) {
            // Create a reply
            $supportTicketId  = intval($_REQUEST['supportTicketId']);
            $objTicket = Ticket::getById($supportTicketId);
        }
        if (!$objTicket) {
            // No or an invalid Ticket ID is present.
            // Pick the language parameter from the request, too
            $editLanguageId = $objInit->getBackendLangId();
            if (!empty($_REQUEST['editLanguageId'])) {
                $editLanguageId  = $_REQUEST['editLanguageId'];
            }
            // create a new Ticket from the edited Message.
            $objTicket = new Ticket(
                $supportMessageFrom,
                0,
                SUPPORT_TICKET_SOURCE_SYSTEM,
                $supportCategoryId,
                $editLanguageId,
                0
            );
            // Need to store it, so it gets an ID.
            $objTicket->store();
echo("messageCommit(): Stored Ticket: ");var_export($objTicket);echo("<br />");
        }
        // Adding a Message to the Ticket will create a TicketEvent.
        return $objTicket->addMessage(
            $supportMessageFrom,
            $supportMessageSubject,
            $supportMessageBody,
            $supportCategoryId
        );
    }


    /**
     * Delete the chosen Ticket
     * @return  boolean             True on success, false otherwise
     * @global  array       $_ARRAYLANG     Language array
     * @global  mixed       $objInit        Init object
     */
    function ticketDelete()
    {
        global $_ARRAYLANG, $objInit;

echo("ticketDelete(): \$_GET: ");var_export($_GET);echo("<br />");
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
            $this->strOkMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATE_SUCCESSFUL'];
        } else {
            $this->strErrMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATE_FAILED'];
        }
        return $return;
    }


    /**
     * Delete the marked Tickets
     * @return  boolean             True on success, false otherwise
     * @global  array       $_ARRAYLANG     Language array
     * @global  mixed       $objInit        Init object
     */
    function ticketsDelete()
    {
        global $_ARRAYLANG, $objInit;

echo("ticketsDelete(): \$_POST: ");var_export($_POST);echo("<br />");
        foreach ($_POST['selectedTicketId'] as $id) {
            $objTicket =
                Ticket::getById($id);
            if (!$objTicket) {
echo("ticketsDelete(): ERROR: Ticket with ID $id could not be retrieved!<br />");
                return false;
            }
            if (!$objTicket->delete()) {
                $this->strErrMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATE_FAILED'];
echo("ticketsDelete(): ERROR: Ticket with ID $id could not be deleted!<br />");
                return false;
            }
        }
        $this->strOkMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATE_SUCCESSFUL'];
        return true;
    }


    /**
     * Save changes to the Ticket
     * @return  boolean             True on success, false otherwise
     * @global  array   $_ARRAYLANG Language array
     */
    function ticketChange()
    {
        global $_ARRAYLANG;

        // The Ticket ID
        if (empty($_REQUEST['supportTicketId'])) {
echo("ticketChange(): ERROR: missing the Ticket ID!<br />");
            return false;
        }
        $supportTicketId = $_REQUEST['supportTicketId'];
        $objTicket = Ticket::getById($supportTicketId);
        if (!$objTicket) {
echo("ticketChange(): ERROR: could not retrieve the Ticket with ID $supportTicketId!<br />");
            return false;
        }
        // As long as nothing has been changed, there's no need to update
        // the Ticket.
        $flagSuccessCategory = true;
        $flagChangedCategory = false;
        $flagSuccessOwner    = true;
        $flagChangedOwner    = false;
        if (!empty($_REQUEST['supportCategoryId'])) {
            $supportCategoryId = intval($_REQUEST['supportCategoryId']);
            if (!$supportCategoryId > 0) {
echo("ticketChange(): WARNING: illegal Support Category ID $supportCategoryId!<br />");
            } else {
                $flagSuccessCategory =
                    $objTicket->updateSupportCategoryId($supportCategoryId);
                if ($flagSuccessCategory) {
                    $flagChangedCategory = true;
                }
            }
        }
        if (!empty($_REQUEST['supportTicketOwnerId'])) {
            $supportTicketOwnerId = intval($_REQUEST['supportTicketOwnerId']);
            if (!$supportTicketOwnerId > 0) {
echo("ticketChange(): WARNING: illegal Ticket owner ID $supportTicketOwnerId!<br />");
            } else {
                $flagSuccessOwner    =
                    $objTicket->updateOwnerId($supportTicketOwnerId);
                if ($flagSuccessOwner) {
                    $flagChangedOwner = true;
                }
            }
        }
        if (!$flagSuccessCategory) {
            $this->addError($_ARRAYLANG['TXT_SUPPORT_CATEGORY_UPDATE_FAILED']);
        }
        if (!$flagSuccessOwner) {
            $this->addError($_ARRAYLANG['TXT_SUPPORT_OWNER_UPDATE_FAILED']);
        }
        if (!$flagChangedCategory) {
            $this->addMessage($_ARRAYLANG['TXT_SUPPORT_CATEGORY_UPDATE_SUCCESSFUL']);
        }
        if (!$flagChangedOwner) {
            $this->addMessage($_ARRAYLANG['TXT_SUPPORT_OWNER_UPDATE_SUCCESSFUL']);
        }
        return true;
    }


    /**
     * Delete the Support Category
     *
     * Deletes the currently selected language only!
     * @return  boolean             True on success, false otherwise
     * @global  array       $_ARRAYLANG     Language array
     * @global  mixed       $objInit        Init object
     */
    function categoryDelete()
    {
        global $_ARRAYLANG, $objInit;

echo("categoryDelete(): \$_GET: ");var_export($_GET);echo("<br />");
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
            $this->strOkMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATE_SUCCESSFUL'];
        } else {
            $this->strErrMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATE_FAILED'];
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
    function categoriesDelete()
    {
        global $_ARRAYLANG, $objInit;

echo("categoriesDelete(): \$_POST: ");var_export($_POST);echo("<br />");
        foreach ($_POST['selectedCategoryId'] as $id) {
            $objSupportCategory =
                SupportCategory::getById($id, $this->editLanguageId);
            if (!$objSupportCategory) {
                return false;
            }
            if (!$objSupportCategory->delete($objSupportCategory->getLanguageId())) {
                $this->strErrMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATE_FAILED'];
                return false;
            }
        }
        $this->strOkMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATE_SUCCESSFUL'];
        return true;
    }


    /**
     * Set up the viewing and editing of Support Categories.
     * @global  array   $_ARRAYLANG Language array
     * @global  Init    $objInit    Init object
     * @return  string              The HTML content
     */
    function categoriesEdit()
    {
        global $_ARRAYLANG, $objInit;

        $this->pageTitle = $_ARRAYLANG['TXT_SUPPORT_CATEGORIES_EDIT'];
        $objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/support/template');
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $objTemplate->loadTemplateFile('module_support_categories_edit.html', true, true);

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

        $objTemplate->setVariable(array(
            'TXT_SUPPORT_ACCEPT_CHANGES'            => $_ARRAYLANG['TXT_SUPPORT_ACCEPT_CHANGES'],
            'TXT_SUPPORT_ACTION'                    => $_ARRAYLANG['TXT_SUPPORT_ACTION'],
            'TXT_SUPPORT_ACTION_IS_IRREVERSIBLE'    => $_ARRAYLANG['TXT_SUPPORT_ACTION_IS_IRREVERSIBLE'],
            'TXT_SUPPORT_ACTIVE'                    => $_ARRAYLANG['TXT_SUPPORT_ACTIVE'],
            'TXT_SUPPORT_CATEGORIES'                => $_ARRAYLANG['TXT_SUPPORT_CATEGORIES'],
            'TXT_SUPPORT_CATEGORIES_COUNT'          => $_ARRAYLANG['TXT_SUPPORT_CATEGORIES_COUNT'],
            'TXT_SUPPORT_CATEGORIES_COUNT_TOTAL'    => $_ARRAYLANG['TXT_SUPPORT_CATEGORIES_COUNT_TOTAL'],
            'TXT_SUPPORT_CATEGORY_ID'               => $_ARRAYLANG['TXT_SUPPORT_ID'],
            'TXT_SUPPORT_CATEGORY_ORDER'            => $_ARRAYLANG['TXT_CORE_SORTING_ORDER'],
            'TXT_SUPPORT_CATEGORY_PARENT'           => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_PARENT'],
            'TXT_SUPPORT_CATEGORIES_DELETE_CONFIRM' => $_ARRAYLANG['TXT_SUPPORT_CATEGORIES_DELETE_CONFIRM'],
            'TXT_SUPPORT_CATEGORY_DELETE_CONFIRM'   => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_DELETE_CONFIRM'],
            'TXT_SUPPORT_CATEGORIES_DELETE'         => $_ARRAYLANG['TXT_SUPPORT_CATEGORIES_DELETE'],
            'TXT_SUPPORT_CATEGORY_DELETE'           => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_DELETE'],
            'TXT_SUPPORT_DELETE_MARKED'             => $_ARRAYLANG['TXT_SUPPORT_DELETE_MARKED'],
            'TXT_SUPPORT_MAKE_SELECTION'            => $_ARRAYLANG['TXT_SUPPORT_MAKE_SELECTION'],
            'TXT_SUPPORT_CATEGORIES_MARKED'         => $_ARRAYLANG['TXT_SUPPORT_CATEGORIES_MARKED'],
            'TXT_SUPPORT_CATEGORY_ROOT'              => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_ROOT'],
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
        $objTemplate->setGlobalVariable(array(
            'SUPPORT_CATEGORY_EDIT_LANGUAGE_ID'     => $this->editLanguageId,
//            'SUPPORT_CATEGORY_OFFSET'               => $offset,
            'TXT_SUPPORT_CATEGORY_LANGUAGE'         => $_ARRAYLANG['TXT_SUPPORT_LANGUAGE'],
            'TXT_SUPPORT_CATEGORY_NAME'             => $_ARRAYLANG['TXT_SUPPORT_NAME'],
            'TXT_SUPPORT_CATEGORY_STATUS'           => $_ARRAYLANG['TXT_SUPPORT_STATUS'],
            'TXT_SUPPORT_CATEGORY_DELETE'           => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_DELETE'],
            'TXT_SUPPORT_CATEGORY_EDIT'             => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_EDIT'],
            'TXT_SUPPORT_CATEGORY_HAS_TICKETS'       => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_HAS_TICKETS'],
        ));

        // List Support Categories by language
        $arrSupportCategoryTree =
            $this->objSupportCategories->getSupportCategoryTreeArray(
                $this->editLanguageId, false
            );
echo("got Support Category tree:<br />");var_export($arrSupportCategoryTree);echo("<br />");
        if ($arrSupportCategoryTree === false) {
echo("failed to get Support Category tree<br />");
        }

        $objTemplate->setCurrentBlock('categoryRow');
        foreach ($arrSupportCategoryTree as $arrSupportCategory) {
            $objTemplate->setVariable(
                'CATEGORY_ROW',
                $this->categoryRow($arrSupportCategory)
            );

            $objTemplate->parseCurrentBlock();
        }

        $objTemplate->setCurrentBlock();
        // Edit Support Category
        if ($id) {
            // Select one by ID
echo("editCategories(): id is $id<br />");
            $objCategory = SupportCategory::getById($id, $this->editLanguageId);
            if ($objCategory) {
                // Edit the existing Support Category
                $objTemplate->setVariable(array(
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
            $objTemplate->setVariable(array(
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

        return $objTemplate->get();
    }


    /**
     * Set up a single Support Category row.
     *
     * Takes an array of a Support Category as provided by an element of
     * the array returned by
     * {@link SupportCategories::getSupportCategoryTreeArray()}.
     * @param   array   $arrSupportCategory     The array with the Support
     *                                          Category data
     * @return  string                          The HTML content
     */
    function categoryRow($arrSupportCategory)
    {
        global $moduloRow;

        $objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/support/template');
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $objTemplate->loadTemplateFile('module_support_categories_row.html', true, true);

        $objTemplate->setVariable(array(
            'SUPPORT_ROW_CLASS'         => (++$moduloRow % 2 ? 'row2' : 'row1'),
            'SUPPORT_CATEGORY_ID'       => $arrSupportCategory['id'],
            'SUPPORT_CATEGORY_STATUS_CHECKED' =>
                ($arrSupportCategory['status']
                    ? ' checked="checked"'
                    : ''
                ),
            'SUPPORT_CATEGORY_ORDER'    => $arrSupportCategory['order'],
            'SUPPORT_CATEGORY_LANGUAGE' =>
                $this->objLanguage->getLanguageParameter(
                    $arrSupportCategory['languageId'], 'name'
                ),
            'SUPPORT_CATEGORY_INDENT'   => str_repeat('|----', $arrSupportCategory['level']),
            'SUPPORT_CATEGORY_NAME'     => $arrSupportCategory['name'],
        ));

        return $objTemplate->get();
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
    function categoryStore()
    {
        global $_ARRAYLANG;

        if (empty($_POST['name'])) {
            $this->strErrMessage = $_ARRAYLANG['TXT_SUPPORT_CATEGORY_FILL_IN_NAME'];
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
echo("categoryStore(): ");var_export($objSupportCategory);echo("<br />");
        if ($objSupportCategory->store()) {
            $this->strOkMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATE_SUCCESSFUL'];
            return true;
        } else {
            $this->strErrMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATE_FAILED'];
        }
        return false;
    }


    /**
     * Store all changes made to the Support Categories shown.
     * @return  boolean             True on success, false otherwise
     * @global  array       $_ARRAYLANG     Language array
     * @global  mixed       $objInit        Init object
     */
    function categoriesStore()
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
                        $_ARRAYLANG['TXT_SUPPORT_UPDATE_FAILED'].
                        ', ???';
                    $return = false;
*/
                } else {
                    $objSupportCategory->setOrder($postOrder);
                    $objSupportCategory->setStatus($postStatus);
                    if (!$objSupportCategory->store()) {
                        $this->strErrMessage .=
                            ($this->strErrMessage ? '<br />' : '').
                            $_ARRAYLANG['TXT_SUPPORT_UPDATE_FAILED'].
                            ', '.$objSupportCategory->getName();
                        $return = false;
                    }
                }
            }
        }
        if ($return) {
            $this->strOkMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATE_SUCCESSFUL'];
        }
        return $return;
    }


    /**
     * Set up the tickets table view
     *
     * @return  string              The HTML content on success, the empty
     *                              string otherwise.
     * @global  array   $_ARRAYLANG Language array
     * @global  Init    $objInit    Init object
     * @global  array   $_CONFIG    Global cofiguration array
     */
    function ticketTable()
    {
        global $_ARRAYLANG, $objInit;

        $baseUri = '?cmd=support&amp;act=ticketTable';

        $this->pageTitle = $_ARRAYLANG['TXT_SUPPORT_TICKET_OVERVIEW'];
        $objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/support/template');
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $objTemplate->loadTemplateFile('module_support_ticket_table.html', true, true);

        $offset = 0;
        if (!empty($_REQUEST['offset'])) {
            $offset = intval($_REQUEST['offset']);
        }

        // Ticket filtering parameters
        $supportCategoryId = 0;
        if (!empty($_REQUEST['supportCategoryId'])) {
            $supportCategoryId = intval($_REQUEST['supportCategoryId']);
        }
        // Default to the users' backend language setting
        $supportTicketLanguageId = $objInit->getBackendLangId();
        if (isset($_REQUEST['supportTicketLanguageId'])) {
            $supportTicketLanguageId = intval($_REQUEST['supportTicketLanguageId']);
echo("ticketTable(): Setting language ID to requested $supportTicketLanguageId<br />");
        } else {
echo("ticketTable(): Setting language ID to default $supportTicketLanguageId<br />");
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
                $_ARRAYLANG['TXT_SUPPORT_DATE'],
                $_ARRAYLANG['TXT_SUPPORT_ID'],
                $_ARRAYLANG['TXT_SUPPORT_STATUS'],
                $_ARRAYLANG['TXT_SUPPORT_EMAIL'],
                $_ARRAYLANG['TXT_SUPPORT_CATEGORY'],
                $_ARRAYLANG['TXT_SUPPORT_LANGUAGE'],
                $_ARRAYLANG['TXT_SUPPORT_OWNER'],
            ),
            false
        );

/*
            // get all Support Categories' IDs and names
            $arrSupportCategoryName =
                $this->objSupportCategories->getSupportCategoryNameArray(
                    0, true, false
                );
*/
        // Get total Ticket count -- Not very performant!
        $ticketCount = count(Ticket::getTicketIdArray(
            $supportCategoryId,
            $supportTicketLanguageId,
            $supportTicketOwnerId,
            $supportTicketStatus,
            $supportTicketSource,
            $supportTicketEmail
// TODO:      $supportTicketSearchTerm
        ));
echo("ticketTable(): sorting order: ".$objSorting->getOrder()."<br />");
        // get range of Tickets IDs, default to latest first
        $arrTicketId = Ticket::getTicketIdArray(
            $supportCategoryId,
            $supportTicketLanguageId,
            $supportTicketOwnerId,
            $supportTicketStatus,
            $supportTicketSource,
            $supportTicketEmail,
// TODO:      $supportTicketSearchTerm,
            $objSorting->getOrder(),
            $offset
        );

        $objTemplate->setVariable(array(
            'HEADER_SUPPORT_TICKET_ID'          => $objSorting->getHeaderForField('id'),
            'HEADER_SUPPORT_TICKET_DATE'        => $objSorting->getHeaderForField('timestamp'),
            'HEADER_SUPPORT_TICKET_STATUS'      => $objSorting->getHeaderForField('status'),
            'HEADER_SUPPORT_TICKET_EMAIL'       => $objSorting->getHeaderForField('email'),
            'HEADER_SUPPORT_TICKET_CATEGORY'    => $objSorting->getHeaderForField('support_category_id'),
            'HEADER_SUPPORT_TICKET_LANGUAGE'    => $objSorting->getHeaderForField('language_id'),
            'HEADER_SUPPORT_TICKET_OWNER'       => $objSorting->getHeaderForField('owner_id'),
            'HEADER_SUPPORT_TICKET_MESSAGE_COUNT' => $_ARRAYLANG['TXT_SUPPORT_MESSAGE_COUNT'],
            'TXT_SUPPORT_TICKETS_FILTER'        => $_ARRAYLANG['TXT_SUPPORT_TICKETS_FILTER'],
            'TXT_SUPPORT_CATEGORY'              => $_ARRAYLANG['TXT_SUPPORT_CATEGORY'],
            'TXT_SUPPORT_TICKET_DELETE_CONFIRM' => $_ARRAYLANG['TXT_SUPPORT_TICKET_DELETE_CONFIRM'],
            'TXT_SUPPORT_DELETE'                => $_ARRAYLANG['TXT_SUPPORT_DELETE'],
            'TXT_SUPPORT_ALL'                   => $_ARRAYLANG['TXT_SUPPORT_ALL'],
//            'TXT_SUPPORT_TICKET_EMAIL'          => $_ARRAYLANG['TXT_SUPPORT_EMAIL'],
            'TXT_SUPPORT_TICKET_MARKED'         => $_ARRAYLANG['TXT_SUPPORT_TICKET_MARKED'],
            'TXT_SUPPORT_TICKET_UPDATE'         => $_ARRAYLANG['TXT_SUPPORT_UPDATE'],
            'TXT_SUPPORT_STATUS'                => $_ARRAYLANG['TXT_SUPPORT_STATUS'],
            'TXT_SUPPORT_LANGUAGE'              => $_ARRAYLANG['TXT_SUPPORT_LANGUAGE'],
            'TXT_SUPPORT_OWNER'                 => $_ARRAYLANG['TXT_SUPPORT_OWNER'],
            'TXT_SUPPORT_SOURCE'                => $_ARRAYLANG['TXT_SUPPORT_TICKET_SOURCE'],
            'TXT_SUPPORT_VIEW_DETAILS'          => $_ARRAYLANG['TXT_SUPPORT_VIEW_DETAILS'],
            'TXT_SUPPORT_TICKET_SHOW_CLOSED'    => $_ARRAYLANG['TXT_SUPPORT_TICKET_SHOW_CLOSED'],
            'TXT_SUPPORT_TICKET_MARKED'         => $_ARRAYLANG['TXT_SUPPORT_TICKET_MARKED'],
            'TXT_SUPPORT_SELECT_ALL'            => $_ARRAYLANG['TXT_SUPPORT_SELECT_ALL'],
            'TXT_SUPPORT_SELECT_NONE'           => $_ARRAYLANG['TXT_SUPPORT_SELECT_NONE'],
            'TXT_SUPPORT_SELECT_ACTION'         => $_ARRAYLANG['TXT_SUPPORT_SELECT_ACTION'],
            'TXT_SUPPORT_TICKET_DELETE_CONFIRM' => $_ARRAYLANG['TXT_SUPPORT_TICKET_DELETE_CONFIRM'],
            'TXT_SUPPORT_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_SUPPORT_ACTION_IS_IRREVERSIBLE'],
            'TXT_SUPPORT_MAKE_SELECTION'        => $_ARRAYLANG['TXT_SUPPORT_MAKE_SELECTION'],
            'SUPPORT_TICKET_SEARCH_TERM'        => htmlspecialchars($supportTicketSearchTerm),
            'TXT_SUPPORT_TICKET_SEARCH_TERM'           => $_ARRAYLANG['TXT_SUPPORT_SEARCH_TERM'],
            'SUPPORT_TICKET_SHOW_CLOSED_CHECK'  =>
                ($supportTicketShowClosed  ? 'checked="checked"' : ''),
            'SUPPORT_PAGING'                    =>
                getPaging(
                    $ticketCount, $offset,
                    $baseUri.$objSorting->getOrderUriEncoded(), '', true
                ),
        ));

        $objTemplate->setVariable(array(
            'SUPPORT_TICKET_LANGUAGE_MENU'  =>
                $this->objLanguage->getMenu($supportTicketLanguageId),
            'SUPPORT_TICKET_OWNER_MENU'     =>
                Ticket::getOwnerMenu($supportTicketOwnerId),
            'SUPPORT_TICKET_CATEGORY_MENU'  =>
                $this->objSupportCategories->getAdminMenu(
                    $supportTicketLanguageId,
                    $supportCategoryId
                ),
            'SUPPORT_TICKET_STATUS_MENU'    =>
                Ticket::getStatusMenu($supportTicketStatus),
            'SUPPORT_TICKET_SOURCE_MENU'    =>
                Ticket::getSourceMenu($supportTicketSource),
        ));

        if (is_array($arrTicketId) && count($arrTicketId)) {
            $objTemplate->setCurrentBlock('ticketRow');
            foreach ($arrTicketId as $supportTicketId) {
                $objTemplate->setVariable(
                    'TICKET_ROW',
                    $this->ticketRow($supportTicketId)
                );
                $objTemplate->parseCurrentBlock();
            }
        } else {
            $objTemplate->hideBlock('ticketRow');
        }
        return $objTemplate->get();
    }


    /**
     * Set up the Ticket detail view.
     *
     * If the $supportTicketId argument is omitted, or zero, the method tries
     * to pick the ID from the ticketId parameter in the $_REQUEST array.
     * If the optional $flagTicketChange parameter is set to true, two
     * additional menus to change the owner and Support Category respectively,
     * are displayed.
     * @param   integer $supportTicketId    The optional Ticket ID
     * @param   boolean $flagTicketChange   Show additional menus if true
     * @return  string                      The HTML content
     * @global  array   $_ARRAYLANG         Language array
     * @global  Init    $objInit            Init object
     */
    function ticketData($supportTicketId=0, $flagTicketChange=false)
    {
        global $_ARRAYLANG, $objInit;

        $this->pageTitle = $_ARRAYLANG['TXT_SUPPORT_TICKET'];
        $objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/support/template');
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $objTemplate->loadTemplateFile('module_support_ticket_data.html', true, true);

        if (!empty($_REQUEST['supportTicketId'])) {
            $supportTicketId = intval($_REQUEST['supportTicketId']);
        }
        if ($supportTicketId <= 0) {
echo("Support::ticketData(): ERROR: No or invalid Ticket ID!<br />");
            return false;
        }
        // get the Ticket
        $objTicket = Ticket::getById($supportTicketId);
        if (!$objTicket) {
echo("Support::ticketData(): ERROR: Could not get the Ticket with ID $supportTicketId!<br />");
            return false;
        }

        // The Support Ticket details
        $languageId         = $objTicket->getLanguageId();
        $supportCategoryId  = $objTicket->getSupportCategoryId();
        $supportCategoryName =
            SupportCategory::getNameById($supportCategoryId, $languageId);
        if (!$supportCategoryId) {
            $supportCategoryName = $_ARRAYLANG['TXT_SUPPORT_CATEGORY_NONE'];
        }
        $ownerId            = $objTicket->getOwnerId();
        $ownerName          = Auth::getFullName($ownerId);
        if (!$ownerId) {
            $ownerName = $_ARRAYLANG['TXT_SUPPORT_OWNER_NONE'];
        }
//echo("owner: $ownerName<br />");
        $objTemplate->setVariable(array(
            'SUPPORT_TICKET_ID'             => $supportTicketId,
            'SUPPORT_TICKET_EMAIL'          => $objTicket->getEmail(),
            'SUPPORT_TICKET_OWNER_ID'       => $ownerId,
            'SUPPORT_TICKET_OWNER'          => $ownerName,
            'SUPPORT_TICKET_DATE'           => $objTicket->getTimestamp(),
            'SUPPORT_TICKET_LANGUAGE_ID'    => $languageId,
            'SUPPORT_TICKET_LANGUAGE'       =>
                $this->objLanguage->getLanguageParameter(
                    $objTicket->getLanguageId(), 'name'
                ),
            'SUPPORT_TICKET_STATUS'         => $objTicket->getStatusString(),
            'SUPPORT_TICKET_SOURCE'         => $objTicket->getSource(),
            'SUPPORT_TICKET_CATEGORY_ID'    => $supportCategoryId,
            'SUPPORT_TICKET_CATEGORY'       => $supportCategoryName,
        ));
        if ($flagTicketChange) {
            $objTemplate->setVariable(array(
                'SUPPORT_TICKET_OWNER_MENU'  =>
                    $objTicket->getOwnerMenu($ownerId, 'supportTicketOwnerId'),
                'SUPPORT_TICKET_CATEGORY_MENU' =>
                    $this->objSupportCategories->getAdminMenu(
                        ($objTicket
                            ?   $objTicket->getLanguageId()
                            :   $objInit->getBackendLangId()
                        ),
                        ($supportCategoryId ? $supportCategoryId : 0),
                        'supportCategoryId'
                    ),
                'TXT_SUPPORT_CHANGE_TO'      => $_ARRAYLANG['TXT_SUPPORT_CHANGE_TO'],
                'TXT_SUPPORT_ACCEPT_CHANGES' => $_ARRAYLANG['TXT_SUPPORT_ACCEPT_CHANGES'],
            ));
            $objTemplate->touchBlock('messageButton');
        }

        $objTemplate->setVariable(array(
            'TXT_SUPPORT_TICKET_ID'                 => $_ARRAYLANG['TXT_SUPPORT_TICKET_ID'],
            'TXT_SUPPORT_FROM'                      => $_ARRAYLANG['TXT_SUPPORT_FROM'],
            'TXT_SUPPORT_DATE'                      => $_ARRAYLANG['TXT_SUPPORT_DATE'],
            'TXT_SUPPORT_LANGUAGE'                  => $_ARRAYLANG['TXT_SUPPORT_LANGUAGE'],
            'TXT_SUPPORT_STATUS'                    => $_ARRAYLANG['TXT_SUPPORT_STATUS'],
            'TXT_SUPPORT_CATEGORY'                  => $_ARRAYLANG['TXT_SUPPORT_CATEGORY'],
            'TXT_SUPPORT_OWNER'                     => $_ARRAYLANG['TXT_SUPPORT_OWNER'],
            'TXT_SUPPORT_SOURCE'                    => $_ARRAYLANG['TXT_SUPPORT_TICKET_SOURCE'],
            'TXT_SUPPORT_TICKET_DETAIL'             => $_ARRAYLANG['TXT_SUPPORT_TICKET_DETAIL'],
            'TXT_SUPPORT_TICKET_INFO'               => $_ARRAYLANG['TXT_SUPPORT_TICKET_INFO'],
            'TXT_SUPPORT_TICKET_DELETE_CONFIRM'     => $_ARRAYLANG['TXT_SUPPORT_TICKET_DELETE_CONFIRM'],
            'TXT_SUPPORT_ACTION_IS_IRREVERSIBLE'    => $_ARRAYLANG['TXT_SUPPORT_ACTION_IS_IRREVERSIBLE'],
            'TXT_SUPPORT_DELETE'                    => $_ARRAYLANG['TXT_SUPPORT_DELETE'],
            'TXT_SUPPORT_EDIT'                      => $_ARRAYLANG['TXT_SUPPORT_EDIT'],
            'TXT_SUPPORT_TICKET_CHANGE_CONFIRM'     => $_ARRAYLANG['TXT_SUPPORT_TICKET_CHANGE_CONFIRM'],
        ));
        return $objTemplate->get();
    }


    /**
     * Set up the Ticket row view.
     *
     * If the $supportTicketId argument is omitted, or zero, the method tries
     * to pick the ID from the ticketId parameter in the $_REQUEST array.
     * @param   integer $supportTicketId    The optional Ticket ID
     * @return  string                      The HTML content
     * @global  array   $_ARRAYLANG         Language array
     * @global  Init    $objInit            Init object
     */
    function ticketRow($supportTicketId)
    {
        global $_ARRAYLANG, $objInit;

        $objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/support/template');
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $objTemplate->loadTemplateFile('module_support_ticket_row.html', true, true);

        if (!empty($_REQUEST['supportTicketId'])) {
            $supportTicketId = intval($_REQUEST['supportTicketId']);
        }
        if (!$supportTicketId) {
echo("Support::ticketRow(): ERROR: Missing the Ticket ID!<br />");
            return false;
        }
        // get the Ticket
        $objTicket = Ticket::getById($supportTicketId);
        if (!$objTicket) {
echo("Support::ticketRow(): ERROR: Could not get the Ticket with ID $supportTicketId!<br />");
            return false;
        }

        // The Support Ticket details
        $languageId         = $objTicket->getLanguageId();
        $supportCategoryId  = $objTicket->getSupportCategoryId();
        $supportCategoryName =
            SupportCategory::getNameById($supportCategoryId, $languageId);
        if (!$supportCategoryId) {
            $supportCategoryName = $_ARRAYLANG['TXT_SUPPORT_CATEGORY_NONE'];
        }
        $ownerId            = $objTicket->getOwnerId();
        $ownerName          = Auth::getFullName($ownerId);
        if (!$ownerId) {
            $ownerName = $_ARRAYLANG['TXT_SUPPORT_OWNER_NONE'];
        }
//echo("owner: $ownerName<br />");
        $messageCount = Message::getRecordCount(
            $supportTicketId, 0, '', '', ''
        );
        $objTemplate->setVariable(array(
            'SUPPORT_TICKET_ID'             => $supportTicketId,
            'SUPPORT_TICKET_EMAIL'          => $objTicket->getEmail(),
            'SUPPORT_TICKET_OWNER_ID'       => $ownerId,
            'SUPPORT_TICKET_OWNER'          => $ownerName,
            'SUPPORT_TICKET_DATE'           => $objTicket->getTimestamp(),
            'SUPPORT_TICKET_LANGUAGE_ID'    => $languageId,
            'SUPPORT_TICKET_LANGUAGE'       =>
                $this->objLanguage->getLanguageParameter(
                    $objTicket->getLanguageId(), 'name'
                ),
            'SUPPORT_TICKET_STATUS'         => $objTicket->getStatusString(),
            'SUPPORT_TICKET_SOURCE'         => $objTicket->getSource(),
            'SUPPORT_TICKET_CATEGORY_ID'    => $supportCategoryId,
            'SUPPORT_TICKET_CATEGORY'       => $supportCategoryName,
            'SUPPORT_TICKET_MESSAGE_COUNT'  => $messageCount,
        ));
/*
        if ($objTemplate->placeholderExists('SUPPORT_TICKET_OWNER_MENU', 'ticketRow')) {
            $objTemplate->setVariable(
                'SUPPORT_TICKET_OWNER_MENU',
                    $objTicket->getOwnerMenu($ownerId, 'supportTicketOwnerId')
            );
        }
echo("REACHED<br />");exit;
        if ($objTemplate->placeholderExists('SUPPORT_TICKET_CATEGORY_MENU', 'ticketRow')) {
            $objTemplate->setVariable(
                'SUPPORT_TICKET_CATEGORY_MENU',
                    $this->objSupportCategories->getAdminMenu(
                        ($objTicket
                            ?   $objTicket->getLanguageId()
                            :   $objInit->getBackendLangId()
                        ),
                        ($supportCategoryId
                            ?   $supportCategoryId
                            :   0
                        ),
                        'supportCategoryId'
                    )
            );
        }
*/
        return $objTemplate->get();
    }


    /**
     * Sets up the Messages table view.
     *
     * The $supportTicketId determines which Tickets the Messages are
     * taken from.
     * If $flagShowSelectionColumn is true, an additional column with
     * radiobuttons is inserted, letting the User select a Message,
     * e.g. for quoting it.  $selectedMessageId carries the ID of the
     * previously selected Message.
     * A range of optional parameters lets you choose the Messages to display.
     * Also picks a few standard options from the $_REQUEST array.
     * See {@link Messages::getMessageArray()} for a detailed explanation.
     * @param   integer     $supportTicketId    The Ticket ID, optionally
     *                                          empty -- it is then taken from
     *                                          the request array.
     * @param   boolean     $flagShowSelectionColumn
     *
     * @param   integer     $selectedMessageId  The optional selected Message ID
     * @param   integer     $status         The optional status filter value
     * @param   string      $from           The optional from filter value
     * @param   string      $subject        The optional subject filter value
     * @param   string      $date           The optional date filter value
     * @return  string                      The HTML content
     * @global  array       $_ARRAYLANG     Language array
     */
    function messageTable(
        $supportTicketId=0, $flagShowSelectionColumn=false,
        $selectedMessageId=0,
        $status=0, $from='', $subject='', $date=''
    ) {
        global $_ARRAYLANG;

        $objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/support/template');
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $objTemplate->loadTemplateFile('module_support_message_table.html', true, true);

        if (   $supportTicketId <= 0
            && !empty($_REQUEST['supportTicketId'])) {
            $supportTicketId = intval($_REQUEST['supportTicketId']);
        }
        if ($supportTicketId <= 0) {
echo("Support::messageTable(ticketId=$supportTicketId, status=$status, from=$from, subject=$subject, date=$date): ERROR: No or invalid Ticket ID '$supportTicketId'!<br />");
            return false;
        }

        $offset = 0;
        if (!empty($_REQUEST['supportMessageOffset'])) {
            $offset = intval($_REQUEST['supportMessageOffset']);
        }
        $limit = 0;
        if (!empty($_REQUEST['supportMessageLimit'])) {
            $limit = intval($_REQUEST['supportMessageLimit']);
        }

        $baseUri =
            "?cmd=support&amp;act=ticketData&amp;supportTicketId=$supportTicketId";
        $objSorting = new Sorting(
            $baseUri,
            array(
                'date', 'id', 'status', 'from', 'subject',
            ),
            array(
                $_ARRAYLANG['TXT_SUPPORT_DATE'],
                $_ARRAYLANG['TXT_SUPPORT_ID'],
                $_ARRAYLANG['TXT_SUPPORT_STATUS'],
                $_ARRAYLANG['TXT_SUPPORT_EMAIL'],
                $_ARRAYLANG['TXT_SUPPORT_SUBJECT'],
                $_ARRAYLANG['TXT_SUPPORT_TICKET_ID'],
            ),
            false
        );

        $arrMessageId = Message::getMessageIdArray(
            $supportTicketId,
            $status, $from, $subject, $date,
            $objSorting->getOrder(),
            $offset, $limit
        );
        if (!is_array($arrMessageId)) {
echo("Support::messageTable(ticketId=$supportTicketId, status=$status, from=$from, subject=$subject, date=$date): ERROR: got no Message array!<br />");
            return false;
        }

        $objTemplate->setVariable(array(
            'TXT_SUPPORT_MESSAGES'           => $_ARRAYLANG['TXT_SUPPORT_MESSAGES'],
            'TXT_SUPPORT_SELECTED'           => $_ARRAYLANG['TXT_SUPPORT_SELECTED'],
            'HEADER_SUPPORT_MESSAGE_DATE'    => $objSorting->getHeaderForField('date'),
            'HEADER_SUPPORT_MESSAGE_ID'      => $objSorting->getHeaderForField('id'),
            'HEADER_SUPPORT_MESSAGE_STATUS'  => $objSorting->getHeaderForField('status'),
            'HEADER_SUPPORT_MESSAGE_FROM'    => $objSorting->getHeaderForField('from'),
            'HEADER_SUPPORT_MESSAGE_SUBJECT' => $objSorting->getHeaderForField('subject'),
            'SUPPORT_MESSAGE_OFFSET'         => $offset,
            'SUPPORT_MESSAGE_LIMIT'          => $limit,
        ));

        $objTemplate->setCurrentBlock('messageRow');
        foreach ($arrMessageId as $supportMessageId) {
            $objTemplate->setVariable(
                'MESSAGE_ROW', $this->messageRow(
                    $supportMessageId,
                    ($supportMessageId == $selectedMessageId)
                )
            );
            $objTemplate->parseCurrentBlock();
        }

        return $objTemplate->get();
    }


    /**
     * Sets up the single Message view.
     *
     * Returns the Message if set up successfully, the empty string if
     * the Message ID is missing or if there is some other problem.
     * If the Message ID isn't provided as an argument, the parameter
     * is looked for in the request.
     * This method also causes a VIEW TicketEvent.
     * @param   integer     $supportMessageId   The optional Message ID
     * @return  string                          The HTML content on success,
     *                                          the empty string otherwise.
     * @global  array       $_ARRAYLANG         Language array
     */
    function messageData($supportMessageId=0, $flagQuoteBody=0)
    {
        global $_ARRAYLANG;

        $objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/support/template');
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $objTemplate->loadTemplateFile('module_support_message_data.html', true, true);

        // Any Message selected?
        if ($supportMessageId <= 0) {
            if (   !empty($_REQUEST['supportMessageId'])
                && $_REQUEST['supportMessageId'] > 0) {
                $supportMessageId = intval($_REQUEST['supportMessageId']);
            } else {
echo("Support::messageData(): ERROR: No or invalid Message ID present!<br />");
                return false;
            }
        }

        $objMessage = Message::getById($supportMessageId);
        if (!$objMessage) {
echo("Support::messageData(): ERROR: Could not get Message with ID $supportMessageId!<br />");
            return false;
        }

        $supportTicketId = $objMessage->getTicketId();
        if (!$supportTicketId > 0) {
echo("Support::messageData(supportMessageId=$supportMessageId, flagQuoteBody=$flagQuoteBody): ERROR: Message object contains invalid Ticket ID ($supportTicketId)!<br />");
            return false;
        }
        $objTicket = Ticket::getById($objMessage->getTicketId());
        if (!$supportTicketId > 0) {
echo("Support::messageData(supportMessageId=$supportMessageId, flagQuoteBody=$flagQuoteBody): ERROR: Could not get Ticket with ID ($supportTicketId)!<br />");
            return false;
        }

        $supportMessageBody = $objMessage->getBody();
        if (empty($supportMessageBody)) {
            $supportMessageBody = $_ARRAYLANG['TXT_SUPPORT_MESSAGE_BODY_EMPTY'];
        }
        if ($flagQuoteBody) {
            // If flagQuoteBody is true, the Message body has to be quoted.
            $supportMessageBody =
                $_ARRAYLANG['TXT_SUPPORT_TICKET_ID'].': '.
                    $objTicket->getId()."\n".
                $_ARRAYLANG['TXT_SUPPORT_CATEGORY'].': '.
                    SupportCategory::getNameById($objTicket->getSupportCategoryId()).
                    "\n\n".
                $_ARRAYLANG['TXT_SUPPORT_SUBJECT'].': '.
                    $objMessage->getSubject()."\n".
                $_ARRAYLANG['TXT_SUPPORT_MESSAGE_QUOTE_ON']."\n".
                    $supportMessageBody.
                $_ARRAYLANG['TXT_SUPPORT_MESSAGE_QUOTE_OFF']."\n\n";
echo("Support::messageData(supportMessageId=$supportMessageId, flagQuoteBody=$flagQuoteBody): INFO: quoting body<br />");
        }

        // Other message details
        $supportMessageStatus   = $objMessage->getStatusString();
        $supportMessageFrom     = $objMessage->getFrom();
        $supportMessageSubject  = $objMessage->getSubject();
        $supportMessageDate     = $objMessage->getDate();

        $objTemplate->setVariable(array(
            'TXT_SUPPORT_MESSAGE'       => $_ARRAYLANG['TXT_SUPPORT_MESSAGE'],
            'TXT_SUPPORT_ID'            => $_ARRAYLANG['TXT_SUPPORT_ID'],
            'TXT_SUPPORT_STATUS'        => $_ARRAYLANG['TXT_SUPPORT_STATUS'],
            'TXT_SUPPORT_DATE'          => $_ARRAYLANG['TXT_SUPPORT_DATE'],
            'TXT_SUPPORT_FROM'          => $_ARRAYLANG['TXT_SUPPORT_FROM'],
            'TXT_SUPPORT_SUBJECT'       => $_ARRAYLANG['TXT_SUPPORT_SUBJECT'],
            'TXT_SUPPORT_MESSAGE_QUOTE' => $_ARRAYLANG['TXT_SUPPORT_MESSAGE_QUOTE'],
            'TXT_SUPPORT_MESSAGE_BODY'  => $_ARRAYLANG['TXT_SUPPORT_MESSAGE_BODY'],
            'SUPPORT_MESSAGE_ID'        => $supportMessageId,
            'SUPPORT_MESSAGE_STATUS'    => $supportMessageStatus,
            'SUPPORT_MESSAGE_FROM'      => $supportMessageFrom,
            'SUPPORT_MESSAGE_SUBJECT'   => $supportMessageSubject,
            'SUPPORT_MESSAGE_BODY'      => $supportMessageBody,
            'SUPPORT_MESSAGE_DATE'      => $supportMessageDate,
        ));

        // Have the Ticket create a TicketEvent
        $objTicket->updateView($supportMessageId);

        return $objTemplate->get();
    }


    /**
     * Sets up the single Message row.
     *
     * If the Message ID isn't provided as an argument, the parameter
     * is looked for in the request.
     * @param   integer     $supportMessageId   The optional Message ID
     * @param   boolean     $selected           If true, the Message will
     *                                          be marked as selected.
     * @return  string                          The HTML content on success,
     *                                          the empty string otherwise.
     * @global  array       $_ARRAYLANG         Language array
     */
    function messageRow($supportMessageId=0, $selected=false)
    {
        global $_ARRAYLANG;

        $objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/support/template');
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $objTemplate->loadTemplateFile('module_support_message_row.html', true, true);

        // Any Message selected?
        if ($supportMessageId <= 0) {
            if (   !empty($_REQUEST['supportMessageId'])
                && $_REQUEST['supportMessageId'] > 0) {
                $supportMessageId = intval($_REQUEST['supportMessageId']);
            } else {
echo("Support::messageRow(): ERROR: No or invalid Message ID present!<br />");
                return false;
            }
        }

        $objMessage = Message::getById($supportMessageId);
        if (!$objMessage) {
echo("Support::messageRow(): ERROR: Could not get Message with ID $supportMessageId!<br />");
            return false;
        }

        // Message details - except for the body
        $objTemplate->setVariable(array(
            'SUPPORT_MESSAGE_SELECTED'  =>
                ($selected ? ' checked="checked"' : ''),
            'SUPPORT_MESSAGE_ID'        => $supportMessageId,
            'SUPPORT_MESSAGE_STATUS'    => $objMessage->getStatusString(),
            'SUPPORT_MESSAGE_FROM'      => $objMessage->getFrom(),
            'SUPPORT_MESSAGE_SUBJECT'   => $objMessage->getSubject(),
            'SUPPORT_MESSAGE_DATE'      => $objMessage->getDate(),
        ));
        return $objTemplate->get();
    }


    /**
     * Set up the Message edit view.
     *
     * Edit a new Message to either reply to an existing Ticket,
     * or in order to create a new Ticket.
     * If there is no associated Ticket ID, as a consequence, a new
     * Ticket will be created as well.  Otherwise, the Message is prepared
     * with a quote of the preceding, or any chosen, Message, and can be
     * used as a reply to an open Ticket.
     *
     * The following cases are covered:
     * - Ticket ID, Message ID present:
     *      The Ticket is selected, all associated Messages are listed.
     *      The selected Message is quoted in the new Message Body.
     * - Ticket ID present, no Message ID:
     *      The Ticket is selected, all associated Messages are listed.
     *      The latest Message is quoted in the new Message Body.
     * - Message ID present, no Ticket ID:
     *      There has to be an associated Ticket!  If not, false is returned.
     *      The Ticket is selected, all associated Messages are listed.
     *      The selected Message is quoted in the new Message Body.
     * - No Ticket ID, no Message ID:
     *      A new Message is created, along with a new Ticket.
     *      The User may choose the Support Category from a dropdown menu.
     * @global  array   $_ARRAYLANG Language array
     * @global  Init    $objInit    Init object
     * @return  string              The HTML content on success,
     *                              or the empty string on failure
     */
    function messageEdit()
    {
        global $_ARRAYLANG, $objInit;

        $this->pageTitle = $_ARRAYLANG['TXT_SUPPORT_MESSAGE_EDIT'];
        $objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/support/template');
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $objTemplate->loadTemplateFile('module_support_message_edit.html', true, true);

        $supportTicketId  = 0;
        $supportMessageId =  0;

        $objMessage = false;
        if (!empty($_REQUEST['supportMessageId'])) {
            // A Message has been selected
            $supportMessageId = intval($_REQUEST['supportMessageId']);
            if ($supportMessageId > 0) {
echo("Support::messageEdit(): INFO: Got Message ID $supportMessageId<br />");
                $objMessage = Message::getById($supportMessageId);
                if (!$objMessage) {
echo("Support::messageEdit(): ERROR: No Message found for Message ID $supportMessageId<br />");
                    return false;
                }
                $supportTicketId = $objMessage->getTicketId();
echo("Support::messageEdit(): INFO: Got Ticket ID $supportTicketId from Message ID $supportMessageId<br />");
            }
        }
        if (!empty($_REQUEST['supportTicketId'])) {
            // A Ticket has been selected
            if ($supportTicketId > 0) {
                if ($supportTicketId != intval($_REQUEST['supportTicketId'])) {
echo("Support::messageEdit(): ERROR: Selected Message and Ticket are not associated!<br />");
                    return false;
                }
            } else {
                $supportTicketId = intval($_REQUEST['supportTicketId']);
echo("Support::messageEdit(): INFO: Got Ticket ID $supportTicketId from request<br />");
            }
        }
        $objTicket = false;
        if ($supportTicketId > 0) {
            $objTicket = Ticket::getById($supportTicketId);
            if (!$objTicket) {
echo("Support::messageEdit(): ERROR: No Ticket found for Ticket ID $supportTicketId<br />");
                return false;
            }
echo("Support::messageEdit(): INFO: Got Ticket object for ID $supportTicketId<br />");
        }
        if (!$objMessage && $objTicket) {
            // Pick the ID of the latest Message
            $supportMessageId = Message::getLatestByTicketId($supportTicketId);
            $objMessage = Message::getById($supportMessageId);
            if (!$objMessage) {
echo("Support::messageEdit(): ERROR: No Message found for Message ID $supportMessageId<br />");
                return false;
            }
echo("Support::messageEdit(): INFO: Got Message object for ID $supportMessageId from Ticket ID $supportTicketId<br />");
        }
        if (!($objMessage || $objTicket)) {
            // new Ticket from Message
            $objTemplate->setVariable(array(
                'TXT_SUPPORT_LANGUAGE'  => $_ARRAYLANG['TXT_SUPPORT_LANGUAGE'],
                'SUPPORT_LANGUAGE_MENU' =>
                    $this->objLanguage->getMenu(
                        (!empty($_REQUEST['editLanguageId'])
                            ?   $_REQUEST['editLanguageId']
                            :   $objInit->getBackendLangId()
                        ),
                        'editLanguageId',
                        'document.forms.formSupportMessageEdit.submit();'
                    ),
                'TXT_SUPPORT_CATEGORY'  => $_ARRAYLANG['TXT_SUPPORT_CATEGORY'],
                'SUPPORT_CATEGORY_MENU' =>
                    $this->objSupportCategories->getAdminMenu(
                        (!empty($_REQUEST['editLanguageId'])
                            ?   $_REQUEST['editLanguageId']
                            :   $objInit->getBackendLangId()
                        ),
                        (!empty($_REQUEST['supportCategoryId'])
                            ?   $_REQUEST['supportCategoryId']
                            :   0
                        ),
                        'supportCategoryId'
                    ),
                'SUPPORT_MESSAGE_FROM'  => Auth::getEmail(Auth::getUserId()),
            ));
echo("Support::messageEdit(): INFO: Editing Message for new Ticket<br />");
        }

        $objTemplate->setVariable(array(
            'TICKET_DATA'   => $this->ticketData($supportTicketId),
            'MESSAGE_TABLE' =>
                $this->messageTable($supportTicketId, true, $supportMessageId),
            'MESSAGE_DATA'  => $this->messageData($supportMessageId, true),
            'TXT_SUPPORT_MESSAGE'       => $_ARRAYLANG['TXT_SUPPORT_MESSAGE'],
            'TXT_SUPPORT_ID'            => $_ARRAYLANG['TXT_SUPPORT_ID'],
            'TXT_SUPPORT_STATUS'        => $_ARRAYLANG['TXT_SUPPORT_STATUS'],
            'TXT_SUPPORT_DATE'          => $_ARRAYLANG['TXT_SUPPORT_DATE'],
            'TXT_SUPPORT_FROM'          => $_ARRAYLANG['TXT_SUPPORT_FROM'],
            'TXT_SUPPORT_SUBJECT'       => $_ARRAYLANG['TXT_SUPPORT_SUBJECT'],
            'TXT_SUPPORT_MESSAGE_BODY'  => $_ARRAYLANG['TXT_SUPPORT_MESSAGE_BODY'],
            'TXT_SUPPORT_MESSAGE_QUOTE' => $_ARRAYLANG['TXT_SUPPORT_MESSAGE_QUOTE'],
            'TXT_SUPPORT_CHANGE_TO'     => $_ARRAYLANG['TXT_SUPPORT_CHANGE_TO'],
            'TXT_SUPPORT_CLEAR'         => $_ARRAYLANG['TXT_SUPPORT_CLEAR'],
            'TXT_SUPPORT_COMMIT'        => $_ARRAYLANG['TXT_SUPPORT_COMMIT'],
            'TXT_SUPPORT_REVERT'        => $_ARRAYLANG['TXT_SUPPORT_REVERT'],
            'TXT_SUPPORT_MESSAGE_REALLY_CLEAR'  =>
                $_ARRAYLANG['TXT_SUPPORT_MESSAGE_REALLY_CLEAR'],
            'TXT_SUPPORT_MESSAGE_REALLY_COMMIT' =>
                $_ARRAYLANG['TXT_SUPPORT_MESSAGE_REALLY_COMMIT'],
            'TXT_SUPPORT_MESSAGE_REALLY_REVERT' =>
                $_ARRAYLANG['TXT_SUPPORT_MESSAGE_REALLY_REVERT'],
/*
            'SUPPORT_TIP_ID' => ,
            'SUPPORT_TIP_NOTE' => ,
*/
        ));
        return $objTemplate->get();
    }


    /**
     * Adds the string $strErrorMessage to the error messages.
     *
     * If necessary, inserts a line break tag (<br />) between
     * error messages.
     * @param   string  $strErrorMessage    The error message to add
     */
    function addError($strErrorMessage)
    {
        $this->strErrMessage .=
            ($this->strErrMessage ? '<br />' : '').
            $strErrorMessage;
    }


    /**
     * Adds the string $strOkMessage to the success messages.
     *
     * If necessary, inserts a line break tag (<br />) between
     * messages.
     * @param   string  $strOkMessage       The message to add
     */
    function addMessage($strOkMessage)
    {
        $this->strOkMessage .=
            ($this->strOkMessage ? '<br />' : '').
            $strOkMessage;
    }
}

?>
