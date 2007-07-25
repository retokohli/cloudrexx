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
     */
    function __construct()
    {
        global $objTemplate, $_ARRAYLANG;

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
//        $this->initialize();
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
          case 'deleteCategories':
            $this->deleteCategories();
          case 'editCategories':
            $this->pageTitle = $_ARRAYLANG['TXT_SUPPORT_EDIT_CATEGORIES'];
            $this->objTemplate->loadTemplateFile('module_support_edit_categories.html', true, true);
            $this->editCategories();
            break;
/*
            case '':
            $this->_();
            break;
*/
          default:
            $this->pageTitle = $_ARRAYLANG['TXT_SUPPORT_SETTINGS'];
            $this->objTemplate->loadTemplateFile('module_support_ticket_overview.html', true, true);
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
     * Set up the overview of the tickets in the system.
     * @global  array   $_ARRAYLANG Language array
     * @global  Init    $objInit    Init object
     */
    function ticketOverview()
    {
        global $_ARRAYLANG, $objInit;

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
        // get all Support Categories' names
        $arrSupportCategoryName =
            SupportCategory::getSupportCategoryNameArray(
                $objInit->frontendLangId, $supportCategoryId
            );
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
     * Delete the marked Support Categories
     *
     * After deleting, returns to the editCategories page.
     * @return  boolean             True on success, false otherwise
     */
    function deleteCategories()
    {
        global $_ARRAYLANG, $objInit;

        $editLanguageId = 0;
        if (!empty($_REQUEST['editLanguageId'])) {
            $editLanguageId = $_REQUEST['editLanguageId'];
        }
echo("deleteCategories(): \$_POST: ");var_export($_POST);echo("<br />");
        foreach ($_POST['selectedId'] as $id) {
            $objSupportCategory = SupportCategory::getById($id, $editLanguageId);
            if (!$objSupportCategory) {
                return false;
            }
            if (!$objSupportCategory->delete($objSupportCategory->getLanguageId())) {
                $this->strErrMessage = $_ARRAYLANG['TXT_SUPPORT_CATEGORIES_UPDATING_DATA_FAILED'];
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

        $success = '';
        if (!empty($_POST['storeSupportCategory'])) {
            $success = $this->storeSupportCategory();
        }
        if (!empty($_POST['storeSupportCategories'])) {
            $success = $this->storeSupportCategories();
        }
        if ($success === true) {
            $this->strOkMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATED_DATA_SUCCESSFULLY'];
        } elseif ($success === false) {
            $this->strErrMessage = $_ARRAYLANG['TXT_SUPPORT_UPDATING_DATA_FAILED'];
        }

        // The ID of the Support Category currently being edited
        $id = 0;
        if (!empty($_GET['id'])) {
            $id = intval($_GET['id']);
        }
        // The offset of the Support Category list being displayed
        // THIS IS NOT SUPPORTED FOR THE TIME BEING!
        $offset = 0;
        if (!empty($_REQUEST['offset'])) {
            $offset = $_REQUEST['offset'];
        }
        // The language ID of the Support Categories being shown and edited
        $editLanguageId = $objInit->defaultFrontendLangId;
        if (!empty($_REQUEST['editLanguageId'])) {
            $editLanguageId = $_REQUEST['editLanguageId'];
        }
        $objLanguage = new FWLanguage();

        $this->objTemplate->setVariable(array(
            'TXT_SUPPORT_ACCEPT_CHANGES'            => $_ARRAYLANG['TXT_SUPPORT_ACCEPT_CHANGES'],
            'TXT_SUPPORT_ACTION'                    => $_ARRAYLANG['TXT_SUPPORT_ACTION'],
            'TXT_SUPPORT_ACTION_IS_IRREVERSIBLE'    => $_ARRAYLANG['TXT_SUPPORT_ACTION_IS_IRREVERSIBLE'],
            'TXT_SUPPORT_ACTIVE'                    => $_ARRAYLANG['TXT_SUPPORT_ACTIVE'],
            'TXT_SUPPORT_CATEGORIES'                => $_ARRAYLANG['TXT_SUPPORT_CATEGORIES'],
            'TXT_SUPPORT_CATEGORIES_COUNT'          => $_ARRAYLANG['TXT_SUPPORT_CATEGORIES_COUNT'],
            'TXT_SUPPORT_CATEGORIES_COUNT_TOTAL'    => $_ARRAYLANG['TXT_SUPPORT_CATEGORIES_COUNT_TOTAL'],
            'TXT_SUPPORT_CATEGORY_ID'               => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_ID'],
//            'TXT_SUPPORT_CATEGORY_ORDER'            => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_ORDER'],
            'TXT_SUPPORT_CATEGORY_PARENT'           => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_PARENT'],
            'TXT_SUPPORT_CONFIRM_DELETE_CATEGORIES' => $_ARRAYLANG['TXT_SUPPORT_CONFIRM_DELETE_CATEGORIES'],
            'TXT_SUPPORT_DELETE_MARKED'             => $_ARRAYLANG['TXT_SUPPORT_DELETE_MARKED'],
            'TXT_SUPPORT_MAKE_SELECTION'            => $_ARRAYLANG['TXT_SUPPORT_MAKE_SELECTION'],
            'TXT_SUPPORT_MARKED_CATEGORIES'         => $_ARRAYLANG['TXT_SUPPORT_MARKED_CATEGORIES'],
            'TXT_SUPPORT_NEW_CATEGORY'              => $_ARRAYLANG['TXT_SUPPORT_NEW_CATEGORY'],
            'TXT_SUPPORT_SELECT_ACTION'             => $_ARRAYLANG['TXT_SUPPORT_SELECT_ACTION'],
            'TXT_SUPPORT_SELECT_ALL'                => $_ARRAYLANG['TXT_SUPPORT_SELECT_ALL'],
            'TXT_SUPPORT_SELECT_NONE'               => $_ARRAYLANG['TXT_SUPPORT_SELECT_NONE'],
            'TXT_SUPPORT_STORE'                     => $_ARRAYLANG['TXT_SUPPORT_STORE'],
            'SUPPORT_CATEGORY_EDIT_LANGUAGE_MENU'   =>
                $objLanguage->getMenu(
                    $editLanguageId,
                    'editLanguageId',
                    "window.location.replace('index.php?cmd=support".
                    "&amp;offset=$offset&amp;act=editCategories&amp;id=$id".
                    "&amp;editLanguageId='+document.getElementById('editLanguageId').value);"
                ),
        ));
        $this->objTemplate->setGlobalVariable(array(
            'SUPPORT_CATEGORY_EDIT_LANGUAGE_ID'     => $editLanguageId,
            'SUPPORT_CATEGORY_OFFSET'               => $offset,
            'TXT_SUPPORT_CATEGORY_LANGUAGE'         => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_LANGUAGE'],
            'TXT_SUPPORT_CATEGORY_NAME'             => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_NAME'],
            'TXT_SUPPORT_CATEGORY_STATUS'           => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_STATUS'],
            'TXT_SUPPORT_DELETE_CATEGORY'           => $_ARRAYLANG['TXT_SUPPORT_DELETE_CATEGORY'],
            'TXT_SUPPORT_EDIT_CATEGORY'             => $_ARRAYLANG['TXT_SUPPORT_EDIT_CATEGORY'],
            'TXT_SUPPORT_TICKETS_IN_CATEGORY'       => $_ARRAYLANG['TXT_SUPPORT_TICKETS_IN_CATEGORY'],
        ));

        // List Support Categories by language
        $arrCategories = SupportCategory::getSupportCategoryNameTreeArray(
            $editLanguageId, 0, 0
        );
        if ($arrCategories === false) {
echo("failed to get any Support Categories<br />");
        }
        $this->objTemplate->setCurrentBlock('supportCategoryRow');
        $i = 0;
        foreach ($arrCategories as $arrCategory) {
            $this->objTemplate->setVariable(array(
                'SUPPORT_ROW_CLASS'         => (++$i % 2 ? 'row2' : 'row1'),
                'SUPPORT_CATEGORY_ID'       => $arrCategory['id'],
//                'SUPPORT_CATEGORY_PARENTID' => $arrCategory['parentId'],
                'SUPPORT_CATEGORY_STATUS_CHECKED' =>
                    ($arrCategory['status']
                        ? ' checked="checked"'
                        : ''
                    ),
                'SUPPORT_CATEGORY_ORDER'    =>
                    ($arrCategory['order']
                        ? $arrCategory['order']
                        : 0
                    ),
                'SUPPORT_CATEGORY_LANGUAGE' =>
                    $objLanguage->getLanguageParameter(
                        $arrCategory['languageId'], 'name'
                    ),
                'SUPPORT_CATEGORY_INDENT'   => str_repeat('|----', $arrCategory['level']),
                'SUPPORT_CATEGORY_NAME'     => $arrCategory['name'],
            ));
            $this->objTemplate->parseCurrentBlock();
        }

        // Edit Support Category
        $this->objTemplate->setCurrentBlock('editSupportCategory');
        if ($id) {
            // Select one by ID
echo("editCategories(): id is $id<br />");
            $objCategory = SupportCategory::getById($id, $editLanguageId);
            if ($objCategory) {
                // New/edit Support Category
                $this->objTemplate->setVariable(array(
                    'SUPPORT_CATEGORY_ID'             => $id,
                    'SUPPORT_CATEGORY_PARENTID'       =>
                        SupportCategory::getMenu(
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
                        $objLanguage->getMenu(
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
                        SupportCategory::getMenu($editLanguageId),
                'SUPPORT_CATEGORY_STATUS_CHECKED' => ' checked="checked"',
                'SUPPORT_CATEGORY_ORDER'          => 0,
                'SUPPORT_CATEGORY_LANGUAGE_MENU'  =>
                    $objLanguage->getMenu($editLanguageId),
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
        return $objSupportCategory->store();
    }


    /**
     * Store all changes made to the Support Categories shown.
     * @return  boolean             True on success, false otherwise
     */
    function storeSupportCategories()
    {
        global $_ARRAYLANG;

        foreach ($_POST['id'] as $id) {
            $objSupportCategory = SupportCategory::getById($id);
            $objSupportCategory->setOrder($_POST['order'][$id]);
            $objSupportCategory->setStatus($_POST['status'][$id]);
            if (!$objSupportCategory->store()) {
                return false;
            }
        }
        return true;
    }

}

?>
