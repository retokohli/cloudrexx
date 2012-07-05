<?php

define('MY_DEBUG', 0);

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
 * Information Field
 */
require_once ASCMS_MODULE_PATH.'/support/lib/InfoField.class.php';
/**
 * Information Fields
 */
require_once ASCMS_MODULE_PATH.'/support/lib/InfoFields.class.php';
/**
 * Knowledge Base
 */
require_once ASCMS_MODULE_PATH.'/support/lib/KnowledgeBase.class.php';
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
     * Do not confuse this with Support Category objects!
     * @var     SupportCategories
     */
    var $objSupportCategories;

    /**
     * The Info Fields object
     *
     * Do not confuse this with Info Field objects!
     * @var     SupportCategories
     */
    var $objInfoFields;

    /**
     * The currently selected Ticket language ID (also filter value)
     * @var     integer
     */
    var $supportLanguageId;

    /**
     * Show all available languages instead of the selected one only
     * @var     boolean
     */
    var $supportLanguageShowAll;

    /**
     * The currently selected Ticket ID (also filter value)
     * @var     integer
     */
    var $supportTicketId;

    /**
     * The currently selected owner ID (filter value)
     * @var     integer
     */
    var $supportTicketOwnerId;

    /**
     * The currently selected e-mail address (filter value)
     * @var     string
     */
    var $supportTicketEmail;

    /**
     * The currently selected Support Category ID (filter value)
     * @var     integer
     */
    var $supportCategoryId;

    /**
     * The currently selected Support Category language ID
     * @var     integer
     */
    var $supportCategoryLanguageId;

    /**
     * The currently selected Info Field ID
     * @var     integer
     */
    var $supportInfoFieldId;

    /**
     * The currently selected Info Field language ID
     * @var     integer
     */
    var $supportInfoFieldLanguageId;

    /**
     * The offset for the Info Field list
     *
     * Note: Not implemented yet!
     * @var     integer
     */
    var $supportInfoFieldOffset;

    /**
     * The currently selected Ticket status (filter value)
     * @var     integer
     */
    var $supportTicketStatus;

    /**
     * The currently selected Ticket source (filter value)
     * @var     integer
     */
    var $supportTicketSource;

    /**
     * The flag indicating whether to close the Ticket
     * @var     integer
     */
    var $supportTicketClose;

    /**
     * The currently selected Ticket search term (filter value)
     * @var     string
     */
    var $supportTicketSearchTerm;

    /**
     * The currently selected value of the "Show closed Tickets" option
     * (filter value)
     * @var     boolean
     */
    var $supportTicketShowClosed;

    /**
     * The value of the Ticket table record offset
     * @var     integer
     */
    var $supportTicketOffset;

    /**
     * The value of the Ticket table record limit
     * @var     integer
     */
    var $supportTicketLimit;

    /**
     * The Ticket table sorting order (SQL-ish)
     * @var     integer
     */
    var $supportTicketOrder;

    /**
     * The currently selected Message ID
     * @var     integer
     */
    var $supportMessageId;

    /**
     * The body of the current Message
     * @var     string
     */
    var $supportMessageBody;

    /**
     * The e-mail address of the current Message
     * @var     string
     */
    var $supportMessageFrom;

    /**
     * The subject of the current Message
     * @var     string
     */
    var $supportMessageSubject;

    /**
     * The value of the Message table record offset
     * @var     integer
     */
    var $supportMessageOffset;

    /**
     * The value of the Message table record limit
     * @var     integer
     */
    var $supportMessageLimit;

    /**
     * The Message table sorting order (SQL-ish)
     * @var     integer
     */
    var $supportMessageOrder;


    /**
     * Constructor (PHP4)
     * @param   string  $strTemplate
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @see     __construct()
     */
    function Support()
    {
        $this->__construct();
    }

    private $act = '';
    
    /**
     * Constructor (PHP5)
     * @global  Template    $objTemplate    PEAR Sigma Template
     * @global  array       $_ARRAYLANG     Language array
     * @global  mixed       $objInit        Init object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function __construct()
    {
        global $objTemplate, $_ARRAYLANG, $objInit;

        if (MY_DEBUG && 1) {
            error_reporting(E_ALL); ini_set('display_errors', 1);
        } else {
            error_reporting(0); ini_set('display_errors', 0);
        }
        if (MY_DEBUG && 2) {
            global $objDatabase; $objDatabase->debug = 1;
        }

        $this->objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/support/template');
        CSRF::add_placeholder($this->objTemplate);
        $this->objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $this->objTemplate->loadTemplateFile('module_support_main.html');        

        $this->supportLanguageId = (!empty($_REQUEST['supportLanguageId'])
            ?   $_REQUEST['supportLanguageId']
            :   BACKEND_LANG_ID
        );
        $this->supportLanguageShowAll = (!empty($_REQUEST['supportLanguageShowAll'])
            ?   1
            :   0
        );
        $this->supportTicketId = (!empty($_REQUEST['supportTicketId'])
            ?   $_REQUEST['supportTicketId']
            :   0
        );
        $this->supportTicketOwnerId = (!empty($_REQUEST['supportTicketOwnerId'])
            ?   $_REQUEST['supportTicketOwnerId']
            :   0
        );
        $this->supportTicketEmail = (!empty($_REQUEST['supportTicketEmail'])
            ?   $_REQUEST['supportTicketEmail']
            :   ''
        );
        $this->supportCategoryId = (!empty($_REQUEST['supportCategoryId'])
            ?   $_REQUEST['supportCategoryId']
            :   0
        );
        $this->supportCategoryLanguageId = (!empty($_REQUEST['supportCategoryLanguageId'])
            ?   $_REQUEST['supportCategoryLanguageId']
            :   0
        );
        $this->supportInfoFieldId = (!empty($_REQUEST['supportInfoFieldId'])
            ?   $_REQUEST['supportInfoFieldId']
            :   0
        );
        $this->supportInfoFieldLanguageId = (!empty($_REQUEST['supportInfoFieldLanguageId'])
            ?   $_REQUEST['supportInfoFieldLanguageId']
            :   0
        );
        $this->supportInfoFieldOffset = (!empty($_REQUEST['supportInfoFieldOffset'])
            ?   $_REQUEST['supportInfoFieldOffset']
            :   0
        );
        $this->supportTicketStatus = (!empty($_REQUEST['supportTicketStatus'])
            ?   $_REQUEST['supportTicketStatus']
            :   -1  // Default to all
        );
        $this->supportTicketSource = (!empty($_REQUEST['supportTicketSource'])
            ?   $_REQUEST['supportTicketSource']
            :   -1  // Default to all
        );
        $this->supportTicketClose = (!empty($_REQUEST['supportTicketClose'])
            ?   $_REQUEST['supportTicketClose']
            :   0  // Don't close by default
        );
        $this->supportTicketSearchTerm = (!empty($_REQUEST['supportTicketSearchTerm'])
            ?   $_REQUEST['supportTicketSearchTerm']
            :   ''
        );
        $this->supportTicketShowClosed = (!empty($_REQUEST['supportTicketShowClosed'])
            ?   $_REQUEST['supportTicketShowClosed']
            :   0
        );
        $this->supportTicketOffset = (!empty($_REQUEST['supportTicketOffset'])
            ?   $_REQUEST['supportTicketOffset']
            :   0
        );
        $this->supportTicketLimit = (!empty($_REQUEST['supportTicketLimit'])
            ?   $_REQUEST['supportTicketLimit']
            :   0
        );
        $this->supportTicketOrder = (!empty($_REQUEST['supportTicketOrder'])
            ?   $_REQUEST['supportTicketOrder']
            :   0
        );
        $this->supportMessageId = (!empty($_REQUEST['supportMessageId'])
            ?   $_REQUEST['supportMessageId']
            :   0
        );
        $this->supportMessageBody = (!empty($_REQUEST['supportMessageBody'])
            ?   $_REQUEST['supportMessageBody']
            :   0
        );
        $this->supportMessageFrom = (!empty($_REQUEST['supportMessageFrom'])
            ?   $_REQUEST['supportMessageFrom']
            :   0
        );
        $this->supportMessageSubject = (!empty($_REQUEST['supportMessageSubject'])
            ?   $_REQUEST['supportMessageSubject']
            :   0
        );
        $this->supportMessageOffset = (!empty($_REQUEST['supportMessageOffset'])
            ?   $_REQUEST['supportMessageOffset']
            :   0
        );
        $this->supportMessageLimit = (!empty($_REQUEST['supportMessageLimit'])
            ?   $_REQUEST['supportMessageLimit']
            :   0
        );
        $this->supportMessageOrder = (!empty($_REQUEST['supportMessageOrder'])
            ?   $_REQUEST['supportMessageOrder']
            :   0
        );

        // Support Categories object
        $this->objSupportCategories =
            new SupportCategories($this->supportLanguageId);
        // Info Fields object
        $this->objInfoFields =
            new InfoFields($this->supportLanguageId);
    }
    private function setNavigation()
    {
        global $objTemplate, $_ARRAYLANG;

        $objTemplate->setVariable(
            'CONTENT_NAVIGATION',
                '<a href="index.php?cmd=support&amp;act=ticketTable" class="'.($this->act == 'ticketTable' ? 'active' : '').'">'.
                $_ARRAYLANG['TXT_SUPPORT_TICKETS'].'</a>'.
                '<a href="index.php?cmd=support&amp;act=categoriesEdit" class="'.($this->act == 'categoriesEdit' ? 'active' : '').'">'.
                $_ARRAYLANG['TXT_SUPPORT_CATEGORIES'].'</a>'.
                '<a href="index.php?cmd=support&amp;act=infoFieldsEdit" class="'.($this->act == 'infoFieldsEdit' ? 'active' : '').'">'.
                $_ARRAYLANG['TXT_SUPPORT_INFO_FIELDS'].'</a>'.
                '<a href="index.php?cmd=support&amp;act=messageEdit" class="'.($this->act == 'messageEdit' ? 'active' : '').'">'.
                $_ARRAYLANG['TXT_SUPPORT_TICKET_NEW'].'</a>'.
                '<a href="index.php?cmd=support&amp;act=settings" class="'.($this->act == 'settings' ? 'active' : '').'">'.
                $_ARRAYLANG['TXT_SETTINGS'].'</a>'
        );
    }


    /**
     * Call the appropriate method to set up the requested page.
     * @access  public
     * @global  Template    $objTemplate    Template
     * @global  array       $_ARRAYLANG     Language array
     * @return  string      The created content
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getPage()
    {
        global $objTemplate, $_ARRAYLANG;

        $action = (!empty($_GET['act']) ? $_GET['act'] : '');
        switch ($action) {
          case 'categoriesEdit':
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
          case 'infoFieldsEdit':
            $this->objTemplate->setVariable('SUPPORT_CENTER', $this->infoFieldsEdit());
            break;
          case 'infoFieldStore':
            $this->infoFieldStore();
            $this->objTemplate->setVariable('SUPPORT_CENTER', $this->infoFieldsEdit());
            break;
          case 'infoFieldsStore':
            $this->infoFieldsStore();
            $this->objTemplate->setVariable('SUPPORT_CENTER', $this->infoFieldsEdit());
            break;
          case 'infoFieldDelete':
            $this->infoFieldDelete();
            $this->objTemplate->setVariable('SUPPORT_CENTER', $this->infoFieldsEdit());
            break;
          case 'infoFieldsDelete':
            $this->infoFieldsDelete();
            $this->objTemplate->setVariable('SUPPORT_CENTER', $this->infoFieldsEdit());
            break;
          case 'ticketData':
            $this->objTemplate->setVariable('SUPPORT_TOP', $this->ticketData());
            $this->objTemplate->setVariable('SUPPORT_CENTER', $this->messageTable());
            $this->objTemplate->setVariable('SUPPORT_BOTTOM', $this->messageData());
            break;
          case 'ticketReply':
            $this->ticketReply();
            $this->objTemplate->setVariable('SUPPORT_CENTER', $this->ticketTable());
            break;
          case 'ticketChange':
            $this->ticketChange();
            $this->objTemplate->setVariable('SUPPORT_TOP', $this->ticketData());
            $this->objTemplate->setVariable('SUPPORT_CENTER', $this->messageTable());
            $this->objTemplate->setVariable('SUPPORT_BOTTOM', $this->messageData());
            break;
          case 'ticketClose':
            $this->ticketClose();
            $this->objTemplate->setVariable('SUPPORT_CENTER', $this->ticketTable());
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

        $this->act = $_REQUEST['act'];
        $this->setNavigation();
    }


    function getFilterValuesUri() {
        return
            "&amp;supportLanguageId=$this->supportLanguageId".
            "&amp;supportCategoryId=$this->supportCategoryId".
            "&amp;supportTicketOwnerId=$this->supportTicketOwnerId".
            "&amp;supportTicketEmail=".urlencode($this->supportTicketEmail).
            "&amp;supportTicketStatus=$this->supportTicketStatus".
            "&amp;supportTicketSource=$this->supportTicketSource".
            "&amp;supportTicketShowClosed=$this->supportTicketShowClosed";
    }

    /**
     * Commit the posted Message.
     *
     * Causes all related data to be updated.
     * @return  boolean             True on success, false otherwise.
     * @global  Init    $objInit    Init object
     * @global  array   $_ARRAYLANG Language array
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function messageCommit()
    {
        global $objInit, $_ARRAYLANG;

        $objTicket  = false;
        $supportMessageBody = $this->supportMessageBody;
        if (!$supportMessageBody) {
if (MY_DEBUG) echo("messageCommit(): No Message Body!<br />");
            return false;
        }
        $supportMessageFrom = $this->supportMessageFrom;
        if (!$supportMessageFrom) {
if (MY_DEBUG) echo("messageCommit(): No e-mail address!<br />");
            return false;
        }
        $supportMessageSubject = $this->supportMessageSubject;
        if (!$supportMessageSubject) {
if (MY_DEBUG) echo("messageCommit(): No Message subject!<br />");
            return false;
        }

        $supportTicketId = $this->supportTicketId;
        if ($supportTicketId > 0) {
            // There is a reference to an existing Ticket.
            // Create a reply.
            $objTicket = Ticket::getById($supportTicketId);
        }
        if ($objTicket) {
            // A valid Ticket ID is present, which means that the
            // Message is intended to be a reply to an existing Ticket.
            // Adding a reply to the Ticket will create a REPLY TicketEvent.
            if (!$objTicket->addReply(
                $supportMessageFrom,
                $supportMessageSubject,
                $supportMessageBody
            )) {
                $this->addError($_ARRAYLANG['TXT_SUPPORT_REPLY_FAILED']);
                return false;
            }
            $this->addMessage($_ARRAYLANG['TXT_SUPPORT_REPLY_SENT']);
//if (MY_DEBUG) echo("messageCommit(): INFO: Ticket close is '$this->supportTicketClose'<br />");
            if ($this->supportTicketClose) {
                return $this->ticketClose();
            }
            return true;
        }
        // No or an invalid Ticket ID is present, which means
        // that a new Ticket must be created.
        // Use the Support Category ID from the request.
        if ($this->supportCategoryId <= 0) {
if (MY_DEBUG) echo("messageCommit(): ERROR: No or invalid Support Category ID ($this->supportCategoryId)!<br />");
            return false;
        }
        // Pick the language parameter from the request, too
        if ($this->supportLanguageId <= 0) {
if (MY_DEBUG) echo("messageCommit(): ERROR: No or invalid language ID ($this->supportLanguageId)!<br />");
            return false;
        }
        // create a new Ticket from the edited Message.
        $objTicket = new Ticket(
            $supportMessageFrom,
            SUPPORT_TICKET_SOURCE_SYSTEM,
            $this->supportCategoryId,
            $this->supportLanguageId
        );
        // Need to store it, so it gets an ID.
        $objTicket->store();
if (MY_DEBUG) { echo("messageCommit(): INFO: Stored new Ticket: ");var_export($objTicket);echo("<br />"); }
        // Adding a Message to the Ticket will create a TicketEvent.
        $messageId = $objTicket->addMessage(
            $supportMessageFrom,
            $supportMessageSubject,
            $supportMessageBody
        );
        return ($messageId ? true : false);
    }


    /**
     * Delete the chosen Ticket
     * @return  boolean             True on success, false otherwise
     * @global  array       $_ARRAYLANG     Language array
     * @global  mixed       $objInit        Init object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function ticketDelete()
    {
        global $_ARRAYLANG, $objInit;

if (MY_DEBUG) { echo("ticketDelete(): \$_GET: ");var_export($_GET);echo("<br />"); }
        $return = true;

        // The ID of the Ticket currently being edited
        $supportTicketId = $this->supportTicketId;
        if ($supportTicketId <= 0) {
if (MY_DEBUG) echo("ticketDelete(): ERROR: No Ticket ID!<br />");
            $return = false;
        } else {
            $objTicket = Ticket::getById($supportTicketId);
            if (!$objTicket) {
                $return = false;
            } else {
                if (!$objTicket->delete()) {
                    $return = false;
                }
            }
        }
        if ($return) {
            $this->addMessage($_ARRAYLANG['TXT_SUPPORT_UPDATE_SUCCESSFUL']);
        } else {
            $this->addError($_ARRAYLANG['TXT_SUPPORT_UPDATE_FAILED']);
        }
        return $return;
    }


    /**
     * Delete the marked Tickets
     * @return  boolean             True on success, false otherwise
     * @global  array       $_ARRAYLANG     Language array
     * @global  mixed       $objInit        Init object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function ticketsDelete()
    {
        global $_ARRAYLANG, $objInit;

if (MY_DEBUG) { echo("ticketsDelete(): \$_POST: ");var_export($_POST);echo("<br />"); }
        foreach ($_POST['selectedTicketId'] as $supportTicketId) {
            $objTicket =
                Ticket::getById($supportTicketId);
            if (!$objTicket) {
if (MY_DEBUG) echo("ticketsDelete(): ERROR: Ticket with ID $supportTicketId could not be retrieved!<br />");
                return false;
            }
            if (!$objTicket->delete()) {
                $this->addError($_ARRAYLANG['TXT_SUPPORT_UPDATE_FAILED']);
if (MY_DEBUG) echo("ticketsDelete(): ERROR: Ticket with ID $supportTicketId could not be deleted!<br />");
                return false;
            }
        }
        $this->addMessage($_ARRAYLANG['TXT_SUPPORT_UPDATE_SUCCESSFUL']);
        return true;
    }


    /**
     * Save changes to the Ticket
     * @return  boolean             True on success, false otherwise
     * @global  array   $_ARRAYLANG Language array
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function ticketChange()
    {
        global $_ARRAYLANG;

        $supportTicketId = $this->supportTicketId;
        if ($supportTicketId <= 0) {
if (MY_DEBUG) echo("ticketChange(): ERROR: missing the Ticket ID!<br />");
            return false;
        }
        $objTicket = Ticket::getById($supportTicketId);
        if (!$objTicket) {
if (MY_DEBUG) echo("ticketChange(): ERROR: could not retrieve the Ticket with ID $supportTicketId!<br />");
            return false;
        }
        // As long as nothing has been changed, there's no need to update
        // the Ticket.
        $flagSuccessCategory = true;
        $flagChangedCategory = false;
        $flagSuccessOwner    = true;
        $flagChangedOwner    = false;
        $supportCategoryId = $this->supportCategoryId;
        if ($supportCategoryId <= 0) {
if (MY_DEBUG) echo("ticketChange(): WARNING: illegal Support Category ID $supportCategoryId!<br />");
        } else {
            $flagSuccessCategory =
                $objTicket->updateSupportCategoryId($supportCategoryId);
            if ($flagSuccessCategory) {
                $flagChangedCategory = true;
            }
        }
        if ($this->supportTicketOwnerId <= 0) {
if (MY_DEBUG) echo("ticketChange(): WARNING: illegal Ticket owner ID $this->supportTicketOwnerId!<br />");
        } else {
            $flagSuccessOwner    =
                $objTicket->updateOwnerId($this->supportTicketOwnerId);
            if ($flagSuccessOwner) {
                $flagChangedOwner = true;
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
        // If just one fails, consider it a failure.
        return $flagSuccessCategory && $flagSuccessOwner;
    }


    /**
     * Closes a Ticket
     * @return  boolean             True on success, false otherwise
     * @global  array   $_ARRAYLANG Language array
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function ticketClose()
    {
        global $_ARRAYLANG;

        $supportMessageId = $this->supportMessageId;
        if ($supportMessageId <= 0) {
if (MY_DEBUG) echo("ticketClose(): ERROR: missing the Message ID!<br />");
            return false;
        }
        $objMessage = Message::getById($supportMessageId);
        if (!$objMessage) {
if (MY_DEBUG) echo("ticketClose(): ERROR: could not retrieve the Message with ID $supportMessageId!<br />");
            return false;
        }
        $supportTicketId = $objMessage->getTicketId();
        $objTicket = Ticket::getById($supportTicketId);
        if (!$objTicket) {
if (MY_DEBUG) echo("ticketClose(): ERROR: could not retrieve the Ticket with ID $supportTicketId!<br />");
            return false;
        }
        if ($objTicket->close()) {
            $this->addMessage($_ARRAYLANG['TXT_SUPPORT_TICKET_CLOSED']);
            return true;
        }
        $this->addError($_ARRAYLANG['TXT_SUPPORT_TICKET_CLOSE_FAILED']);
        return false;
    }


    /**
     * Delete the Support Category
     *
     * Deletes the currently selected language only!
     * @return  boolean             True on success, false otherwise
     * @global  array       $_ARRAYLANG     Language array
     * @global  mixed       $objInit        Init object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function categoryDelete()
    {
        global $_ARRAYLANG, $objInit;

if (MY_DEBUG) { echo("categoryDelete(): \$_GET: ");var_export($_GET);echo("<br />"); }
        $return = true;

        // The ID of the Support Category currently being edited
        if ($this->supportCategoryId <= 0) {
            $return = false;
        } else {
            $objSupportCategory =
                SupportCategory::getById(
                    $this->supportCategoryId, $this->supportCategoryLanguageId);
            if (!$objSupportCategory) {
                $return = false;
            } else {
                if (!$objSupportCategory->delete(
                        $this->supportCategoryLanguageId)
                ) {
                    $return = false;
                }
            }
        }
        if ($return) {
            // *MUST* invalidate the tree array after
            // changing the data!
            $this->objSupportCategories->invalidateSupportCategoryTreeArray();
            $this->addMessage($_ARRAYLANG['TXT_SUPPORT_UPDATE_SUCCESSFUL']);
        } else {
            $this->addError($_ARRAYLANG['TXT_SUPPORT_UPDATE_FAILED']);
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
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function categoriesDelete()
    {
        global $_ARRAYLANG, $objInit;

if (MY_DEBUG) { echo("categoriesDelete(): \$_POST: ");var_export($_POST);echo("<br />"); }
        foreach ($_POST['selectedCategoryArrayId'] as $id) {
            $arrLanguageId = $_POST['selectedCategoryArrayLanguageId'];
            $objSupportCategory =
                SupportCategory::getById($id, $arrLanguageId[$id]);
            if (!$objSupportCategory) {
if (MY_DEBUG) { echo("Support::categoriesDelete(): ERROR: Failed to get Support Category with ID $id, lang {$arrLanguageId[$id]}!<br />"); }
                return false;
            }
            if (!$objSupportCategory->delete($arrLanguageId[$id])) {
                $this->addError($_ARRAYLANG['TXT_SUPPORT_UPDATE_FAILED']);
                return false;
            }
        }
        // *MUST* invalidate the tree array after
        // changing the data!
        $this->objSupportCategories->invalidateSupportCategoryTreeArray();
        $this->addMessage($_ARRAYLANG['TXT_SUPPORT_UPDATE_SUCCESSFUL']);
        return true;
    }


    /**
     * Set up the viewing and editing of Support Categories.
     * @global  array   $_ARRAYLANG Language array
     * @global  Init    $objInit    Init object
     * @return  string              The HTML content
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function categoriesEdit()
    {
        global $_ARRAYLANG, $objInit;

        $this->pageTitle = $_ARRAYLANG['TXT_SUPPORT_CATEGORIES_EDIT'];
        $objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/support/template');
        CSRF::add_placeholder($objTemplate);
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $objTemplate->loadTemplateFile('module_support_category_edit.html', true, true);

        $objTemplate->setVariable(array(
            'TXT_SUPPORT_STORE'                     => $_ARRAYLANG['TXT_SUPPORT_STORE'],
            'TXT_SUPPORT_ACTION'                    => $_ARRAYLANG['TXT_SUPPORT_ACTION'],
            'TXT_SUPPORT_ACTION_IS_IRREVERSIBLE'    => $_ARRAYLANG['TXT_SUPPORT_ACTION_IS_IRREVERSIBLE'],
            'TXT_SUPPORT_ACTIVE'                    => $_ARRAYLANG['TXT_SUPPORT_ACTIVE'],
            'TXT_SUPPORT_CATEGORIES'                => $_ARRAYLANG['TXT_SUPPORT_CATEGORIES'],
            'TXT_SUPPORT_CATEGORIES_COUNT'          => $_ARRAYLANG['TXT_SUPPORT_CATEGORIES_COUNT'],
            'TXT_SUPPORT_CATEGORIES_COUNT_TOTAL'    => $_ARRAYLANG['TXT_SUPPORT_CATEGORIES_COUNT_TOTAL'],
            'TXT_SUPPORT_CATEGORY_ID'               => $_ARRAYLANG['TXT_SUPPORT_ID'],
            'TXT_SUPPORT_CATEGORY_LANGUAGE'         => $_ARRAYLANG['TXT_SUPPORT_LANGUAGE'],
            'TXT_SUPPORT_CATEGORY_NAME'             => $_ARRAYLANG['TXT_SUPPORT_NAME'],
            'TXT_SUPPORT_CATEGORY_STATUS'           => $_ARRAYLANG['TXT_SUPPORT_STATUS'],
            'TXT_SUPPORT_CATEGORY_ORDER'            => $_ARRAYLANG['TXT_CORE_SORTING_ORDER'],
            'TXT_SUPPORT_CATEGORY_PARENT'           => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_PARENT'],
            'TXT_SUPPORT_CATEGORIES_DELETE_CONFIRM' => $_ARRAYLANG['TXT_SUPPORT_CATEGORIES_DELETE_CONFIRM'],
            'TXT_SUPPORT_CATEGORY_DELETE_CONFIRM'   => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_DELETE_CONFIRM'],
            'TXT_SUPPORT_CATEGORIES_DELETE'         => $_ARRAYLANG['TXT_SUPPORT_CATEGORIES_DELETE'],
//            'TXT_SUPPORT_CATEGORY_DELETE'           => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_DELETE'],
            'TXT_SUPPORT_DELETE_MARKED'             => $_ARRAYLANG['TXT_SUPPORT_DELETE_MARKED'],
            'TXT_SUPPORT_MAKE_SELECTION'            => $_ARRAYLANG['TXT_SUPPORT_MAKE_SELECTION'],
            'TXT_SUPPORT_CATEGORIES_MARKED'         => $_ARRAYLANG['TXT_SUPPORT_CATEGORIES_MARKED'],
            'TXT_SUPPORT_CATEGORY_ROOT'             => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_ROOT'],
            'TXT_SUPPORT_LANGUAGE_SHOW_ALL'         => $_ARRAYLANG['TXT_SUPPORT_LANGUAGE_SHOW_ALL'],
            'TXT_SUPPORT_SELECT_ACTION'             => $_ARRAYLANG['TXT_SUPPORT_SELECT_ACTION'],
            'TXT_SUPPORT_SELECT_ALL'                => $_ARRAYLANG['TXT_SUPPORT_SELECT_ALL'],
            'TXT_SUPPORT_SELECT_NONE'               => $_ARRAYLANG['TXT_SUPPORT_SELECT_NONE'],
            'TXT_SUPPORT_STORE'                     => $_ARRAYLANG['TXT_SUPPORT_STORE'],
            'TXT_SUPPORT_CATEGORY_EDIT'  =>
                ($this->supportCategoryId > 0
                    ?   $_ARRAYLANG['TXT_SUPPORT_CATEGORY_EDIT']
                    :   $_ARRAYLANG['TXT_SUPPORT_CATEGORY_EDIT_NEW']
                ),
            'SUPPORT_EDIT_LANGUAGE_MENU' =>
                $this->objLanguage->getMenu(
                    $this->supportLanguageId,
                    'supportLanguageId',
                    "window.location.replace('index.php?cmd=support&".
                    CSRF::param() .
                    "&amp;act=categoriesEdit".
                    "&amp;supportCategoryId=$this->supportCategoryId".
                    "&amp;supportCategoryLanguageId=$this->supportCategoryLanguageId".
                    "&amp;supportLanguageId='+this.value+'".
                    "&amp;supportLanguageShowAll=$this->supportLanguageShowAll')"
//                    "&amp;supportCategoryOffset=$supportCategoryOffset".
                ),
        ));

        $objTemplate->setGlobalVariable(array(
            'SUPPORT_LANGUAGE_SHOW_ALL'         =>
                ($this->supportLanguageShowAll ? '1' : ''),
            'SUPPORT_LANGUAGE_SHOW_ALL_CHECKED' =>
                ($this->supportLanguageShowAll ? ' checked="checked"' : ''),
            'SUPPORT_EDIT_LANGUAGE_ID' => $this->supportLanguageId,
            'TXT_SUPPORT_LANGUAGE'     => $_ARRAYLANG['TXT_SUPPORT_LANGUAGE'],
            'TXT_SUPPORT_NAME'         => $_ARRAYLANG['TXT_SUPPORT_NAME'],
            'TXT_SUPPORT_STATUS'       => $_ARRAYLANG['TXT_SUPPORT_STATUS'],
            'TXT_SUPPORT_DELETE'       => $_ARRAYLANG['TXT_SUPPORT_INFO_FIELD_DELETE'],
            'TXT_SUPPORT_EDIT'         => $_ARRAYLANG['TXT_SUPPORT_INFO_FIELD_EDIT'],
//            'SUPPORT_INFO_FIELD_OFFSET' => $this->supportInfoFieldOffset,
        ));

        // List Support Categories by language
        $arrSupportCategoryTree =
            $this->objSupportCategories->getSupportCategoryTreeArray(
                $this->supportLanguageId, false
            );
//if (MY_DEBUG) { echo("Support::categoriesEdit(): INFO: Got Support Category tree:<br />");var_export($arrSupportCategoryTree);echo("<br />"); }
        if ($arrSupportCategoryTree === false) {
if (MY_DEBUG) echo("failed to get Support Category tree<br />");
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
        if ($this->supportCategoryId > 0) {
            $languageId = $this->supportCategoryLanguageId;
            if ($languageId <= 0) {
                $languageId = $this->supportLanguageId;
            }
            // Select one by ID
//if (MY_DEBUG) echo("categoriesEdit(): id is $this->supportCategoryId<br />");
            // Find the array index corresponding to the ID
            $index = 0;
            while (   $index < count($arrSupportCategoryTree)
                   && $arrSupportCategoryTree[$index]['id'] != $this->supportCategoryId) {
                ++$index;
            }
            // Found the matching index
            if ($index < count($arrSupportCategoryTree)) {
                // Edit the existing Support Category
                $objTemplate->setVariable(array(
                    'SUPPORT_CATEGORY_ID'             =>
                        $this->supportCategoryId,
                    'SUPPORT_CATEGORY_PARENTID'       =>
                        $this->objSupportCategories->getAdminMenu(
                            $languageId,
                            $arrSupportCategoryTree[$index]['parentId']
                        ),
                    'SUPPORT_CATEGORY_STATUS_CHECKED' =>
                        ($arrSupportCategoryTree[$index]['status']
                            ? ' checked="checked"'
                            : ''
                        ),
                    'SUPPORT_CATEGORY_ORDER'          =>
                        $arrSupportCategoryTree[$index]['order'],
                    'SUPPORT_CATEGORY_LANGUAGE_MENU'  =>
                        $this->objLanguage->getMenu(
                            $languageId,
                            'supportCategoryLanguageId'
                    ),
                    'SUPPORT_CATEGORY_NAME'           =>
                        $arrSupportCategoryTree[$index]['arrName'][$languageId],
                ));
            }
        } else {
            // Default values
            $objTemplate->setVariable(array(
                'SUPPORT_CATEGORY_ID'             => 0,
                    'SUPPORT_CATEGORY_PARENTID'   =>
                        $this->objSupportCategories->getAdminMenu(
                            $this->supportLanguageId
                        ),
                'SUPPORT_CATEGORY_STATUS_CHECKED' => ' checked="checked"',
                'SUPPORT_CATEGORY_ORDER'          => 0,
                'SUPPORT_CATEGORY_LANGUAGE_MENU'  =>
                    $this->objLanguage->getMenu(
                        $this->supportLanguageId, 'supportCategoryLanguageId'),
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
     * @global  array   $_ARRAYLANG             Language array
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function categoryRow($arrSupportCategory)
    {
        global $moduloRow, $_ARRAYLANG;

        $objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/support/template');
        CSRF::add_placeholder($objTemplate);
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $objTemplate->loadTemplateFile('module_support_category_row.html', true, true);

        $objTemplate->setCurrentBlock('supportCategoryRow');
        $objTemplate->setVariable(array(
            'TXT_SUPPORT_CATEGORY_HAS_TICKETS' => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_HAS_TICKETS'],
            'TXT_SUPPORT_CATEGORY_EDIT'        => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_EDIT'],
            'TXT_SUPPORT_CATEGORY_DELETE'      => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_DELETE'],
            'SUPPORT_ROW_CLASS'                => (++$moduloRow % 2 ? 'row2' : 'row1'),
            'SUPPORT_CATEGORY_ID'              => $arrSupportCategory['id'],
            'SUPPORT_CATEGORY_LANGUAGE_ID'     => $arrSupportCategory['languageId'],
            'SUPPORT_CATEGORY_LANGUAGE'        =>
                $this->objLanguage->getLanguageParameter(
                    $arrSupportCategory['languageId'], 'name'
                ),
            'SUPPORT_EDIT_LANGUAGE_ID'         => $this->supportLanguageId,
            'SUPPORT_CATEGORY_STATUS_CHECKED'  =>
                ($arrSupportCategory['status'] ? ' checked="checked"' : '' ),
            'SUPPORT_CATEGORY_ORDER'           => $arrSupportCategory['order'],
            'SUPPORT_INDENT'   =>
                ($arrSupportCategory['level'] >= 1
                    ?   str_repeat('&nbsp;', ($arrSupportCategory['level']-1)*6).
                        '&nbsp;+&nbsp;'
                    : ''
                ),
            'SUPPORT_CATEGORY_NAME'            => $arrSupportCategory['name'],
            'SUPPORT_LANGUAGE_SHOW_ALL'         =>
                ($this->supportLanguageShowAll ? '1' : ''),
        ));
        $objTemplate->parseCurrentBlock();
        $arrName = $arrSupportCategory['arrName'];
        if ($this->supportLanguageShowAll) {
            if (is_array($arrName)) {
                $objTemplate->setCurrentBlock('supportCategoryLanguageRow');
                foreach ($arrName as $languageId => $name) {
                    // Skip selected language ID, this is displayed above already.
                    if ($languageId == $arrSupportCategory['languageId']) {
                        continue;
                    }
                    $objTemplate->setVariable(array(
                        'TXT_SUPPORT_CATEGORY_HAS_TICKETS' => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_HAS_TICKETS'],
                        'TXT_SUPPORT_CATEGORY_EDIT'        => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_EDIT'],
                        'TXT_SUPPORT_CATEGORY_DELETE'      => $_ARRAYLANG['TXT_SUPPORT_CATEGORY_DELETE'],
                        'SUPPORT_ROW_CLASS'         => ($moduloRow % 2 ? 'row2' : 'row1'),
                        'SUPPORT_INDENT'            =>
                            ($arrSupportCategory['level'] >= 1
                                ?   str_repeat('&nbsp;', ($arrSupportCategory['level']-1)*6).
                                    '&nbsp;+&nbsp;'
                                : ''
                            ),
                        'SUPPORT_CATEGORY_ID'       => $arrSupportCategory['id'],
                        'SUPPORT_CATEGORY_LANGUAGE_ID' => $languageId,
                        'SUPPORT_CATEGORY_LANGUAGE' =>
                            $this->objLanguage->getLanguageParameter(
                                $languageId, 'name'
                            ),
                        'SUPPORT_EDIT_LANGUAGE_ID'  => $this->supportLanguageId,
                        'SUPPORT_CATEGORY_NAME'     => $name,
                        'SUPPORT_LANGUAGE_SHOW_ALL'         =>
                            ($this->supportLanguageShowAll ? '1' : ''),
                    ));
                    $objTemplate->parseCurrentBlock();
                }
            } else {
if (MY_DEBUG) echo("Support::categoryRow(...): ERROR: Missing Name array!<br />");
            }
        }
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
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function categoryStore()
    {
        global $_ARRAYLANG;

        if (empty($_POST['supportCategoryName'])) {
            $this->addError($_ARRAYLANG['TXT_SUPPORT_CATEGORY_FILL_IN_NAME']);
            return false;
        }
//if (MY_DEBUG) { echo("POST: ");var_export($_POST);echo("<br />"); }
        $supportCategoryName = $_POST['supportCategoryName'];
        $supportCategoryParentId = $_POST['supportCategoryParentId'];
        $supportCategoryStatus =
            (!empty($_POST['supportCategoryStatus'])
                ? true : false
            );
        $supportCategoryLanguageId =
            (!empty($_POST['supportCategoryLanguageId'])
                ? $_POST['supportCategoryLanguageId'] : 0
            );
        $supportCategoryOrder =
            (!empty($_POST['supportCategoryOrder'])
                ? $_POST['supportCategoryOrder'] : 0
            );
        $objSupportCategory = new SupportCategory(
            $supportCategoryName,
            $supportCategoryLanguageId,
            $supportCategoryParentId,
            $supportCategoryStatus,
            $supportCategoryOrder,
            $this->supportCategoryId
        );
        if (!$objSupportCategory) {
if (MY_DEBUG) echo("categoryStore(): ERROR: Failed to create SupportCategory object!<br />");
            return false;
        }
//if (MY_DEBUG) { echo("categoryStore(): ");var_export($objSupportCategory);echo("<br />"); }
        if ($objSupportCategory->store()) {
            $this->addMessage($_ARRAYLANG['TXT_SUPPORT_UPDATE_SUCCESSFUL']);
            // Clear the ID of the Support Category, so the User
            // can create a new one after that.
            $this->supportCategoryId = 0;
            // *MUST* invalidate the tree array after
            // changing the data!
            $this->objSupportCategories->invalidateSupportCategoryTreeArray();
            return true;
        } else {
            $this->addError($_ARRAYLANG['TXT_SUPPORT_UPDATE_FAILED']);
        }
        return false;
    }


    /**
     * Store all changes made to the Support Categories submitted.
     * @return  boolean                     True on success, false otherwise
     * @global  array       $_ARRAYLANG     Language array
     * @global  mixed       $objInit        Init object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function categoriesStore()
    {
        global $_ARRAYLANG, $objInit;
//echo("Support::categoriesStore(): INFO: Entered.<br />");

        // If no array with IDs has been posted, then why are we here?
        if (!is_array($_POST['supportCategoryArrayId'])) {
            return false;
        }

        $arrSupportCategoryTree =
            $this->objSupportCategories->getSupportCategoryTreeArray(
                $this->supportLanguageId, false
            );
        $return = true;

//if (MY_DEBUG) { echo("Support::categoriesStore(): INFO: lang=$this->supportLanguageId, tree=");var_export($arrSupportCategoryTree);echo(".<br />"); }
        foreach ($arrSupportCategoryTree as $arrSupportCategory) {
            $id     = $arrSupportCategory['id'];
            $postOrder  =
                (!empty($_POST['supportCategoryArrayOrder'][$id])
                    ? $_POST['supportCategoryArrayOrder'][$id]
                    : 0
                );
            $postStatus =
                (!empty($_POST['supportCategoryArrayStatus'][$id])
                    ? $_POST['supportCategoryArrayStatus'][$id]
                    : 0
                );
//if (MY_DEBUG) echo("Support::categoriesStore(): INFO: id=$id, order=$order, postOrder=$postOrder, status=$status, postStatus=$postStatus.<br />");
            if (   !empty($_POST['supportCategoryArrayId'][$id])
                && (   $postOrder  != $arrSupportCategory['order']
                    || $postStatus != $arrSupportCategory['status'])
            ) {
//echo("Support::categoriesStore(): INFO: Updating id=$id.<br />");
                $objSupportCategory = SupportCategory::getById($id);
                if (!$objSupportCategory) {
if (MY_DEBUG) echo("Support::categoriesStore(): ERROR: Failed to get Support Category id=$id!<br />");
                    $this->addError($_ARRAYLANG['TXT_SUPPORT_UPDATE_FAILED']);
                    $return = false;
                } else {
                    $objSupportCategory->setOrder($postOrder);
                    $objSupportCategory->setStatus($postStatus);
                    if (!$objSupportCategory->store()) {
                        $this->addError($_ARRAYLANG['TXT_SUPPORT_UPDATE_FAILED'].
                            ", ID $id -- ".$objSupportCategory->getName());
                        $return = false;
                    } else {
                        // *MUST* invalidate the tree array after
                        // changing the data!
                        $this->objSupportCategories->invalidateSupportCategoryTreeArray();
                    }
                }
            }
        }
        if ($return) {
            $this->addMessage($_ARRAYLANG['TXT_SUPPORT_UPDATE_SUCCESSFUL']);
        } else {
            $this->addError($_ARRAYLANG['TXT_SUPPORT_UPDATE_FAILED']);
        }
        return $return;
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
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function ticketData($supportTicketId=0, $flagTicketChange=false)
    {
        global $_ARRAYLANG, $objInit;

        $this->pageTitle = $_ARRAYLANG['TXT_SUPPORT_TICKET'];
        $objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/support/template');
        CSRF::add_placeholder($objTemplate);
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $objTemplate->loadTemplateFile('module_support_ticket_data.html', true, true);

        if ($supportTicketId <= 0) {
            $supportTicketId = $this->supportTicketId;
        }
        $supportCategoryId = $this->supportCategoryId;
        $supportLanguageId = $this->supportLanguageId;

        // Other Support Ticket defaults
        $ownerId             = 0;
        $ownerName           = $_ARRAYLANG['TXT_SUPPORT_OWNER_NONE'];

        $objFWUser = FWUser::getFWUserObject();
        $ticketEmail         = $objFWUser->objUser->getEmail();
        $ticketTimestamp     = $_ARRAYLANG['TXT_SUPPORT_DATE_NONE'];
        $ticketStatusString  = $_ARRAYLANG['TXT_SUPPORT_TICKET_STATUS_NEW'];
        $ticketSourceString  = $_ARRAYLANG['TXT_SUPPORT_TICKET_SOURCE_SYSTEM'];

        if ($supportTicketId <= 0) {
if (MY_DEBUG) echo("Support::ticketData(): INFO: No or invalid Ticket ID -- creating new Ticket.<br />");
        }
        if ($supportTicketId > 0) {
            // get the Ticket
            $objTicket = Ticket::getById($supportTicketId);
            if (!$objTicket) {
if (MY_DEBUG) echo("Support::ticketData(): ERROR: Could not get the Ticket with ID $supportTicketId!<br />");
                return false;
            }
            // The Support Ticket details override the defaults
            $supportLanguageId  = $objTicket->getLanguageId();
            $supportCategoryId  = $objTicket->getSupportCategoryId();
            $ownerId            = TicketEvent::getTicketOwnerId($supportTicketId);
            $ownerName          = ($objOwner = $objFWUser->objUser->getUser($ownerId)) ? $objOwner->getProfileAttribute('firstname').' '.$objOwner->getProfileAttribute('lastname') : false;
            if ($ownerName == false) {
                $ownerName = $_ARRAYLANG['TXT_SUPPORT_OWNER_UNKNOWN'];
            }
            $ticketEmail        = $objTicket->getEmail();
            $ticketTimestamp    = $objTicket->getTimestamp();
            if ($ticketTimestamp == false) {
                $ticketTimestamp = $_ARRAYLANG['TXT_SUPPORT_DATE_NONE'];
            }
            $ticketStatusString  = $objTicket->getStatusString();
            $ticketSourceString  = $objTicket->getSourceString();
        }
        if ($ticketEmail == false) {
            $ticketEmail = $_ARRAYLANG['TXT_SUPPORT_EMAIL_UNKNOWN'];
        }
        $languageName    =
            $this->objLanguage->getLanguageParameter($supportLanguageId, 'name');
        if ($languageName == false) {
            $languageName = $_ARRAYLANG['TXT_SUPPORT_LANGUAGE_UNKNOWN'];
        }
        $supportCategoryName =
            SupportCategory::getNameById($supportCategoryId, $supportLanguageId);
        if ($supportCategoryName == false) {
            $supportCategoryName = $_ARRAYLANG['TXT_SUPPORT_CATEGORY_UNKNOWN'];
        }

        if ($flagTicketChange) {
            $objTemplate->setVariable(array(
                'SUPPORT_TICKET_OWNER_MENU'  =>
                    $objTicket->getOwnerMenu($ownerId, 'supportTicketOwnerId'),
                'SUPPORT_TICKET_CATEGORY_MENU' =>
                    $this->objSupportCategories->getAdminMenu(
                        $supportLanguageId,
                        $supportCategoryId,
                        'supportCategoryId'
                    ),
                'TXT_SUPPORT_CHANGE_TO'      => $_ARRAYLANG['TXT_SUPPORT_CHANGE_TO'],
                'TXT_SUPPORT_STORE' => $_ARRAYLANG['TXT_SUPPORT_STORE'],
            ));
            $objTemplate->touchBlock('messageButton');
        }

        $objTemplate->setVariable(array(
            'SUPPORT_TICKET_ID'          => $supportTicketId,
            'SUPPORT_TICKET_EMAIL'       => $ticketEmail,
            'SUPPORT_TICKET_OWNER_ID'    => $ownerId,
            'SUPPORT_TICKET_OWNER'       => htmlentities($ownerName, ENT_QUOTES, CONTREXX_CHARSET),
            'SUPPORT_TICKET_DATE'        => $ticketTimestamp,
            'SUPPORT_TICKET_LANGUAGE_ID' => $supportLanguageId,
            'SUPPORT_TICKET_LANGUAGE'    => $languageName,
            'SUPPORT_TICKET_STATUS'      => $ticketStatusString,
            'SUPPORT_TICKET_SOURCE'      => $ticketSourceString,
            'SUPPORT_TICKET_CATEGORY_ID' => $supportCategoryId,
            'SUPPORT_TICKET_CATEGORY'    => $supportCategoryName,
            'TXT_SUPPORT_TICKET_ID'              => $_ARRAYLANG['TXT_SUPPORT_TICKET_ID'],
            'TXT_SUPPORT_TICKET_CLOSE'           => $_ARRAYLANG['TXT_SUPPORT_TICKET_CLOSE'],
            'TXT_SUPPORT_TICKET_DETAIL'          => $_ARRAYLANG['TXT_SUPPORT_TICKET_DETAIL'],
            'TXT_SUPPORT_TICKET_INFO'            => $_ARRAYLANG['TXT_SUPPORT_TICKET_INFO'],
//            'TXT_SUPPORT_TICKET_CHANGE_CONFIRM'  => $_ARRAYLANG['TXT_SUPPORT_TICKET_CHANGE_CONFIRM'],
            'TXT_SUPPORT_TICKET_DELETE_CONFIRM'  => $_ARRAYLANG['TXT_SUPPORT_TICKET_DELETE_CONFIRM'],
            'TXT_SUPPORT_TICKET_CLOSE_CONFIRM'   => $_ARRAYLANG['TXT_SUPPORT_TICKET_CLOSE_CONFIRM'],
            'TXT_SUPPORT_FROM'                   => $_ARRAYLANG['TXT_SUPPORT_FROM'],
            'TXT_SUPPORT_DATE'                   => $_ARRAYLANG['TXT_SUPPORT_DATE'],
            'TXT_SUPPORT_LANGUAGE'               => $_ARRAYLANG['TXT_SUPPORT_LANGUAGE'],
            'TXT_SUPPORT_STATUS'                 => $_ARRAYLANG['TXT_SUPPORT_STATUS'],
            'TXT_SUPPORT_CATEGORY'               => $_ARRAYLANG['TXT_SUPPORT_CATEGORY'],
            'TXT_SUPPORT_OWNER'                  => $_ARRAYLANG['TXT_SUPPORT_OWNER'],
            'TXT_SUPPORT_SOURCE'                 => $_ARRAYLANG['TXT_SUPPORT_TICKET_SOURCE'],
            'TXT_SUPPORT_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_SUPPORT_ACTION_IS_IRREVERSIBLE'],
            'TXT_SUPPORT_DELETE'                 => $_ARRAYLANG['TXT_SUPPORT_DELETE'],
            'TXT_SUPPORT_EDIT'                   => $_ARRAYLANG['TXT_SUPPORT_EDIT'],
            'TXT_SUPPORT_DELETE'                 => $_ARRAYLANG['TXT_SUPPORT_DELETE'],
        ));
        return $objTemplate->get();
    }


    /**
     * Set up the tickets table view
     *
     * @return  string              The HTML content on success, the empty
     *                              string otherwise.
     * @global  array   $_ARRAYLANG Language array
     * @global  Init    $objInit    Init object
     * @global  array   $_CONFIG    Global cofiguration array
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function ticketTable()
    {
        global $_ARRAYLANG, $objInit;

        $baseUri =
            '?cmd=support&amp;act=ticketTable'.
            $this->getFilterValuesUri();

        $this->pageTitle = $_ARRAYLANG['TXT_SUPPORT_TICKET_OVERVIEW'];
        $objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/support/template');
        CSRF::add_placeholder($objTemplate);
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $objTemplate->loadTemplateFile('module_support_ticket_table.html', true, true);

        // Ticket filtering parameters
// TODO: remove
/*
        $supportTicketOwnerId = $this->supportTicketOwnerId;
        // A value of -1 here stands for DON'T CARE
        $supportTicketStatus = $this->supportTicketStatus; // -1;
        // A value of -1 here stands for DON'T CARE
        $supportTicketSource = $this->supportTicketSource; // -1;
        $supportTicketEmail = $this->supportTicketEmail;
        $supportTicketSearchTerm = $this->supportTicketSearchTerm;
        $supportTicketShowClosed = $this->supportTicketShowClosed;
*/

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
        // Get total Ticket count -- Not very performant!
        $ticketCount = count(Ticket::getTicketIdArray(
            $this->supportCategoryId,
            $this->supportLanguageId,
            $this->supportTicketOwnerId,
            $this->supportTicketStatus,
            $this->supportTicketSource,
            $this->supportTicketEmail,
// TODO: implement
//            $supportTicketSearchTerm
            '',
            0,
            999999999 // limit; make sure the count isn't limited
        ));
if (MY_DEBUG) echo("ticketTable(): sorting order: ".$objSorting->getOrder()."<br />");
        // get range of Tickets IDs, default to latest first
        $arrTicketId = Ticket::getTicketIdArray(
            $this->supportCategoryId,
            $this->supportLanguageId,
            $this->supportTicketOwnerId,
            $this->supportTicketStatus,
            $this->supportTicketSource,
            $this->supportTicketEmail,
// TODO: implement
//            $supportTicketSearchTerm,
            $objSorting->getOrder(),
            $this->supportTicketOffset
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
            'TXT_SUPPORT_NO_OWNER'              => $_ARRAYLANG['TXT_SUPPORT_NO_OWNER'],
            'TXT_SUPPORT_OWNER'                 => $_ARRAYLANG['TXT_SUPPORT_OWNER'],
            'TXT_SUPPORT_SOURCE'                => $_ARRAYLANG['TXT_SUPPORT_TICKET_SOURCE'],
            'TXT_SUPPORT_VIEW_DETAILS'          => $_ARRAYLANG['TXT_SUPPORT_VIEW_DETAILS'],
            'TXT_SUPPORT_TICKET_SHOW_CLOSED'    => $_ARRAYLANG['TXT_SUPPORT_TICKET_SHOW_CLOSED'],
            'TXT_SUPPORT_TICKET_MARKED'         => $_ARRAYLANG['TXT_SUPPORT_TICKET_MARKED'],
            'TXT_SUPPORT_SELECT_ALL'            => $_ARRAYLANG['TXT_SUPPORT_SELECT_ALL'],
            'TXT_SUPPORT_SELECT_NONE'           => $_ARRAYLANG['TXT_SUPPORT_SELECT_NONE'],
            'TXT_SUPPORT_SELECT_ACTION'         => $_ARRAYLANG['TXT_SUPPORT_SELECT_ACTION'],
            'TXT_SUPPORT_TICKET_DELETE_CONFIRM' => $_ARRAYLANG['TXT_SUPPORT_TICKET_DELETE_CONFIRM'],
            'TXT_SUPPORT_TICKETS_DELETE_CONFIRM' => $_ARRAYLANG['TXT_SUPPORT_TICKETS_DELETE_CONFIRM'],
            'TXT_SUPPORT_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_SUPPORT_ACTION_IS_IRREVERSIBLE'],
            'TXT_SUPPORT_MAKE_SELECTION'        => $_ARRAYLANG['TXT_SUPPORT_MAKE_SELECTION'],
            'SUPPORT_TICKET_SEARCH_TERM'        => htmlspecialchars($this->supportTicketSearchTerm),
            'TXT_SUPPORT_TICKET_SEARCH_TERM'    => $_ARRAYLANG['TXT_SUPPORT_SEARCH_TERM'],
            'SUPPORT_TICKET_SHOW_CLOSED_CHECK'  =>
                ($this->supportTicketShowClosed  ? 'checked="checked"' : ''),
            'SUPPORT_PAGING'                    =>
                getPaging(
                    $ticketCount, $this->supportTicketOffset,
                    $baseUri.'&amp'.$objSorting->getOrderUriEncoded(), '', true
                ),
        ));

        $objTemplate->setVariable(array(
            'SUPPORT_TICKET_LANGUAGE_MENU'  =>
                $this->objLanguage->getMenu($this->supportLanguageId),
            'SUPPORT_TICKET_OWNER_MENU'     =>
                Ticket::getOwnerMenu($this->supportTicketOwnerId),
            'SUPPORT_TICKET_CATEGORY_MENU'  =>
                $this->objSupportCategories->getAdminMenu(
                    $this->supportLanguageId,
                    $this->supportCategoryId
                ),
            'SUPPORT_TICKET_STATUS_MENU'    =>
                Ticket::getStatusMenu($this->supportTicketStatus),
            'SUPPORT_TICKET_SOURCE_MENU'    =>
                Ticket::getSourceMenu($this->supportTicketSource),
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
     * Set up the Ticket row view.
     *
     * If the $supportTicketId argument is omitted, or zero, the method tries
     * to pick the ID from the ticketId parameter in the $_REQUEST array.
     * @param   integer $supportTicketId    The optional Ticket ID
     * @return  string                      The HTML content
     * @global  array   $_ARRAYLANG         Language array
     * @global  Init    $objInit            Init object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function ticketRow($supportTicketId=0)
    {
        global $_ARRAYLANG, $objInit;

        $objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/support/template');
        CSRF::add_placeholder($objTemplate);
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $objTemplate->loadTemplateFile('module_support_ticket_row.html', true, true);

        if (!$supportTicketId) {
            $supportTicketId = $this->supportTicketId;
            if (!$supportTicketId) {
if (MY_DEBUG) echo("Support::ticketRow(): ERROR: Missing the Ticket ID!<br />");
                return false;
            }
        }
        // get the Ticket
        $objTicket = Ticket::getById($supportTicketId);
        if (!$objTicket) {
if (MY_DEBUG) echo("Support::ticketRow(): ERROR: Could not get the Ticket with ID $supportTicketId!<br />");
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
        $ownerId            = TicketEvent::getTicketOwnerId($supportTicketId);
        $objFWUser = FWUser::getFWUserObject();
        $ownerName          = ($objOwner = $objFWUser->objUser->getUser($ownerId)) ? $objOwner->getProfileAttribute('firstname').' '.$objOwner->getProfileAttribute('lastname') : false;
        if (!$ownerId) {
            $ownerName = $_ARRAYLANG['TXT_SUPPORT_OWNER_NONE'];
        }
//if (MY_DEBUG) echo("owner: $ownerName<br />");
        $messageCount = Message::getRecordCount(
            $supportTicketId, 0, '', '', ''
        );
        $objTemplate->setVariable(array(
            'SUPPORT_TICKET_ID'             => $supportTicketId,
            'SUPPORT_TICKET_EMAIL'          => $objTicket->getEmail(),
            'SUPPORT_TICKET_OWNER_ID'       => $ownerId,
            'SUPPORT_TICKET_OWNER'          => htmlentities($ownerNamem, ENT_QUOTES, CONTREXX_CHARSET),
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
if (MY_DEBUG) echo("REACHED<br />");exit;
        if ($objTemplate->placeholderExists('SUPPORT_TICKET_CATEGORY_MENU', 'ticketRow')) {
            $objTemplate->setVariable(
                'SUPPORT_TICKET_CATEGORY_MENU',
                    $this->objSupportCategories->getAdminMenu(
                        ($objTicket
                            ?   $objTicket->getLanguageId()
                            :   BACKEND_LANG_ID
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
     *                                      If true, the selection column
     *                                      with a radio button will be shown.
     * @param   integer     $selectedMessageId  The optional selected Message ID
     * @param   integer     $status         The optional status filter value
     * @param   string      $from           The optional from filter value
     * @param   string      $subject        The optional subject filter value
     * @param   string      $date           The optional date filter value
     * @return  string                      The HTML content
     * @global  array       $_ARRAYLANG     Language array
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function messageTable(
        $supportTicketId=0, $flagShowSelectionColumn=false,
        $status=0, $from='', $subject='', $date=''
    ) {
        global $_ARRAYLANG;

        $objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/support/template');
        CSRF::add_placeholder($objTemplate);
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $objTemplate->loadTemplateFile('module_support_message_table.html', true, true);

        if ($supportTicketId <= 0) {
            $supportTicketId = $this->supportTicketId;
            if ($supportTicketId <= 0) {
if (MY_DEBUG) echo("Support::messageTable(ticketId=$supportTicketId, flagShowSelectionColumn=$flagShowSelectionColumn, status=$status, from=$from, subject=$subject, date=$date): ERROR: No or invalid Ticket ID '$supportTicketId'!<br />");
                return false;
            }
        }

        $baseUri =
            '?cmd=support&amp;act=ticketData'.
            "&amp;supportTicketId=$supportTicketId".
            $this->getFilterValuesUri();

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
            $this->supportMessageOffset, $this->supportMessageLimit
        );
        if (!is_array($arrMessageId)) {
if (MY_DEBUG) echo("Support::messageTable(ticketId=$supportTicketId, flagShowSelectionColumn=$flagShowSelectionColumn, status=$status, from=$from, subject=$subject, date=$date): ERROR: got no Message array!<br />");
            return false;
        }

        $objTemplate->setCurrentBlock('messageRow');
        foreach ($arrMessageId as $supportMessageId) {
            $objTemplate->setVariable(
                'MESSAGE_ROW', $this->messageRow(
                    $supportMessageId,
                    ($supportMessageId == $this->supportMessageId)
                )
            );
            $objTemplate->parseCurrentBlock();
        }

        $objTemplate->setVariable(array(
            'TXT_SUPPORT_MESSAGES'           => $_ARRAYLANG['TXT_SUPPORT_MESSAGES'],
            'TXT_SUPPORT_SELECTED'           => $_ARRAYLANG['TXT_SUPPORT_SELECTED'],
            'HEADER_SUPPORT_MESSAGE_DATE'    => $objSorting->getHeaderForField('date'),
            'HEADER_SUPPORT_MESSAGE_ID'      => $objSorting->getHeaderForField('id'),
            'HEADER_SUPPORT_MESSAGE_STATUS'  => $objSorting->getHeaderForField('status'),
            'HEADER_SUPPORT_MESSAGE_FROM'    => $objSorting->getHeaderForField('from'),
            'HEADER_SUPPORT_MESSAGE_SUBJECT' => $objSorting->getHeaderForField('subject'),
            'SUPPORT_MESSAGE_OFFSET'         => $this->supportMessageOffset,
            'SUPPORT_MESSAGE_LIMIT'          => $this->supportMessageLimit,
            'SUPPORT_MESSAGE_TABLE_ACTION'   => "ticketData&amp;supportTicketId=$supportTicketId",
        ));

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
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function messageData($supportMessageId=0)
    {
        global $_ARRAYLANG;

        $objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/support/template');
        CSRF::add_placeholder($objTemplate);
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $objTemplate->loadTemplateFile('module_support_message_data.html', true, true);

        // Any Message selected?
        if ($supportMessageId <= 0) {
            $supportMessageId = $this->supportMessageId;
        }
        if ($supportMessageId <= 0) {
if (MY_DEBUG) echo("Support::messageData(): ERROR: No or invalid Message ID present!<br />");
            return false;
        }

        $objMessage = Message::getById($supportMessageId);
        if (!$objMessage) {
if (MY_DEBUG) echo("Support::messageData(): ERROR: Could not get Message with ID $supportMessageId!<br />");
            return false;
        }

        $supportTicketId = $objMessage->getTicketId();
        if (!$supportTicketId > 0) {
if (MY_DEBUG) echo("Support::messageData(supportMessageId=$supportMessageId): ERROR: Message object contains invalid Ticket ID ($supportTicketId)!<br />");
            return false;
        }
        $objTicket = Ticket::getById($objMessage->getTicketId());
        if (!$supportTicketId > 0) {
if (MY_DEBUG) echo("Support::messageData(supportMessageId=$supportMessageId): ERROR: Could not get Ticket with ID ($supportTicketId)!<br />");
            return false;
        }

        $supportMessageBody = $this->supportMessageBody;
        if (empty($supportMessageBody)) {
            $supportMessageBody = $objMessage->getBody();
        }
        if (empty($supportMessageBody)) {
            $supportMessageBody = $_ARRAYLANG['TXT_SUPPORT_MESSAGE_BODY_EMPTY'];
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
            'TXT_SUPPORT_TICKET_CLOSE'  => $_ARRAYLANG['TXT_SUPPORT_TICKET_CLOSE'],
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
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function messageRow($supportMessageId=0, $selected=false)
    {
        global $_ARRAYLANG;

        $objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/support/template');
        CSRF::add_placeholder($objTemplate);
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $objTemplate->loadTemplateFile('module_support_message_row.html', true, true);

        // Any Message selected?
        if ($supportMessageId <= 0) {
            $supportMessageId = $this->supportMessageId;
        }
        if ($supportMessageId <= 0) {
if (MY_DEBUG) echo("Support::messageRow(): ERROR: No or invalid Message ID present!<br />");
            return false;
        }

        $objMessage = Message::getById($supportMessageId);
        if (!$objMessage) {
if (MY_DEBUG) echo("Support::messageRow(): ERROR: Could not get Message with ID $supportMessageId!<br />");
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
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function messageEdit()
    {
        global $_ARRAYLANG, $objInit;

        $this->pageTitle = $_ARRAYLANG['TXT_SUPPORT_MESSAGE_EDIT'];
        $objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/support/template');
        CSRF::add_placeholder($objTemplate);
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $objTemplate->loadTemplateFile('module_support_message_edit.html', true, true);

        $supportTicketId  = $this->supportTicketId;
        $supportMessageId = $this->supportMessageId;
        // Both Ticket and Message ID may still be invalid here.

        $objMessage = false;
        if ($supportMessageId > 0) {
            // A Message has been selected
if (MY_DEBUG) echo("Support::messageEdit(): INFO: Got Message ID $supportMessageId<br />");
            $objMessage = Message::getById($supportMessageId);
            if (!$objMessage) {
if (MY_DEBUG) echo("Support::messageEdit(): ERROR: No Message found for Message ID $supportMessageId<br />");
                return false;
            }
            $supportTicketId = $objMessage->getTicketId();
if (MY_DEBUG) echo("Support::messageEdit(): INFO: Got Ticket ID $supportTicketId from Message ID $supportMessageId<br />");
        }
        // Both Ticket and Message ID may still be invalid here.
        // If the Message ID is valid, we also have a
        // Message object and a Ticket ID now.
        $objTicket = false;
        if ($supportTicketId > 0) {
            $objTicket = Ticket::getById($supportTicketId);
            if (!$objTicket) {
if (MY_DEBUG) echo("Support::messageEdit(): ERROR: No Ticket found for Ticket ID $supportTicketId<br />");
                return false;
            }
if (MY_DEBUG) echo("Support::messageEdit(): INFO: Got Ticket object for ID $supportTicketId<br />");
        }
        // Both Ticket and Message ID may still be invalid here.
        // If the Message ID is valid, we have both
        // Message and Ticket objects now.
        // If only the Ticket ID is valid, we have a Ticket object now.
        if (!$objMessage && $objTicket) {
            // Pick the ID of the latest Message
            $supportMessageId = Message::getLatestByTicketId($supportTicketId);
            if (!$supportMessageId) {
if (MY_DEBUG) echo("Support::messageEdit(): ERROR: No latest Message ID found for Ticket ID $supportTicketId<br />");
                return false;
            }
            $objMessage = Message::getById($supportMessageId);
            if (!$objMessage) {
if (MY_DEBUG) echo("Support::messageEdit(): ERROR: No Message found for Message ID $supportMessageId<br />");
                return false;
            }
if (MY_DEBUG) echo("Support::messageEdit(): INFO: Got Message object for ID $supportMessageId from Ticket ID $supportTicketId<br />");
        }

        $supportMessageSubject = '';
        $supportMessageBody    = '';
        // Both Ticket and Message ID may still be invalid here.
        // If either the Message or Ticket ID is valid, we have both
        // Message and Ticket objects now.
        if ($objMessage && $objTicket) {
            $supportMessageSubject = $objMessage->getSubject();
            $supportCategoryId = $objTicket->getSupportCategoryId();
            $supportCategoryName =
                SupportCategory::getNameById(
                    $supportCategoryId,
                    $objTicket->getLanguageId()
                );
            if ($supportCategoryName === false) {
                $supportCategoryName = $_ARRAYLANG['TXT_SUPPORT_CATEGORY_UNKNOWN'];
            }
if (MY_DEBUG) echo("Support::messageEdit(): INFO: supportCategoryId is $supportCategoryId, supportCategoryName is $supportCategoryName.<br />");
            $supportMessageBody =
                $_ARRAYLANG['TXT_SUPPORT_TICKET_ID'].': '.
                    $objTicket->getId()."\n".
                $_ARRAYLANG['TXT_SUPPORT_CATEGORY'].': '.
                    $supportCategoryName."\n\n".
                $_ARRAYLANG['TXT_SUPPORT_SUBJECT'].': '.
                    $objMessage->getSubject()."\n".
                $_ARRAYLANG['TXT_SUPPORT_MESSAGE_QUOTE_ON']."\n".
                    $objMessage->getBody()."\n".
                $_ARRAYLANG['TXT_SUPPORT_MESSAGE_QUOTE_OFF']."\n\n";
if (MY_DEBUG) echo("Support::messageEdit(): INFO: quoting body ($supportMessageBody).<br />");
/*
// Added:
            $objTemplate->setVariable(array(
                'SUPPORT_CATEGORY_ID'       => $objTicket->getSupportCategoryId(),
            ));
*/
        } else {
            // Otherwise, let's compose a new Message for a new Ticket.
            // Set up both the Support Category and language menus.
            $objTemplate->setVariable(array(
                'TXT_SUPPORT_LANGUAGE'  => $_ARRAYLANG['TXT_SUPPORT_LANGUAGE'],
                'SUPPORT_LANGUAGE_MENU' =>
                    $this->objLanguage->getMenu(
                        $this->supportLanguageId,
                        'supportLanguageId',
                        'document.forms.formSupportMessageEdit.submit();'
                    ),
                'TXT_SUPPORT_CATEGORY'  => $_ARRAYLANG['TXT_SUPPORT_CATEGORY'],
                'SUPPORT_CATEGORY_MENU' =>
                    $this->objSupportCategories->getAdminMenu(
                        $this->supportLanguageId,
                        $this->supportCategoryId,
                        'supportCategoryId'
                    ),
            ));
if (MY_DEBUG) echo("Support::messageEdit(): INFO: Editing Message for new Ticket<br />");
        }

        $objFWUser = FWUser::getFWUserObject();
        $objTemplate->setVariable(array(
            'TICKET_DATA'   => $this->ticketData($supportTicketId),
            'MESSAGE_TABLE' =>
                $this->messageTable($supportTicketId, true, $supportMessageId),
            'SUPPORT_TICKET_ID'         => $supportTicketId,
            'SUPPORT_MESSAGE_FROM'      => $objFWUser->objUser->getEmail(),
            'SUPPORT_MESSAGE_SUBJECT'   => $supportMessageSubject,
            'SUPPORT_MESSAGE_BODY'      => $supportMessageBody,
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
            'TXT_SUPPORT_TICKET_CLOSE'  => $_ARRAYLANG['TXT_SUPPORT_TICKET_CLOSE'],
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
     * @author  Reto Kohli <reto.kohli@comvation.com>
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
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function addMessage($strOkMessage)
    {
        $this->strOkMessage .=
            ($this->strOkMessage ? '<br />' : '').
            $strOkMessage;
    }


    /**
     * Delete the Info Field
     *
     * Deletes the currently selected language only!
     * @return  boolean                     True on success, false otherwise
     * @global  array       $_ARRAYLANG     Language array
     * @global  mixed       $objInit        Init object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function infoFieldDelete()
    {
        global $_ARRAYLANG, $objInit;

if (MY_DEBUG) { echo("infoFieldDelete(): \$_GET: ");var_export($_GET);echo("<br />"); }
        $return = true;

        // The ID of the Info Field currently being edited
        if ($this->supportInfoFieldId <= 0) {
            $return = false;
        } else {
            $objInfoField = InfoField::getById(
                $this->supportInfoFieldId, $this->supportInfoFieldLanguageId);
            if (!$objInfoField) {
                $return = false;
            } else {
                if (!$objInfoField->delete($this->supportInfoFieldLanguageId)) {
                    $return = false;
                } else {
                    // *MUST* invalidate the InfoField Array
                    // after changing the data!
                    $this->objInfoFields->invalidateInfoFieldArray();
                }
            }
        }
        if ($return) {
            $this->addMessage($_ARRAYLANG['TXT_SUPPORT_UPDATE_SUCCESSFUL']);
        } else {
            $this->addError($_ARRAYLANG['TXT_SUPPORT_UPDATE_FAILED']);
        }
        return $return;
    }


    /**
     * Delete the marked Info Fields
     *
     * Deletes the selected language only!
     * @return  boolean                     True on success, false otherwise
     * @global  array       $_ARRAYLANG     Language array
     * @global  mixed       $objInit        Init object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function infoFieldsDelete()
    {
        global $_ARRAYLANG, $objInit;

if (MY_DEBUG) { echo("infoFields(): \$_POST: ");var_export($_POST);echo("<br />"); }
        foreach ($_POST['selectedInfoFieldArrayId'] as $id) {
            $arrLanguageId = $_POST['supportInfoFieldArrayLanguageId'];
            $objInfoField =
                InfoField::getById($id, $arrLanguageId[$id]);
            if (!$objInfoField) {
if (MY_DEBUG) { echo("infoFields(): ERROR: Failed to get InfoField with ID $id!<br />"); }
                return false;
            }
            if (!$objInfoField->delete($arrLanguageId[$id])) {
                $this->addError($_ARRAYLANG['TXT_SUPPORT_UPDATE_FAILED']);
                return false;
            }
            // *MUST* invalidate the InfoField Array after changing the data!
            $this->objInfoFields->invalidateInfoFieldArray();
        }
        $this->addMessage($_ARRAYLANG['TXT_SUPPORT_UPDATE_SUCCESSFUL']);
        return true;
    }


    /**
     * Set up the viewing and editing of Info Fields.
     * @global  array   $_ARRAYLANG Language array
     * @global  Init    $objInit    Init object
     * @return  string              The HTML content
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function infoFieldsEdit()
    {
        global $_ARRAYLANG, $objInit;

        $this->pageTitle = $_ARRAYLANG['TXT_SUPPORT_INFO_FIELDS_EDIT'];
        $objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/support/template');
        CSRF::add_placeholder($objTemplate);
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $objTemplate->loadTemplateFile('module_support_info_field_edit.html', true, true);

        $objTemplate->setVariable(array(
            'TXT_SUPPORT_STORE'             => $_ARRAYLANG['TXT_SUPPORT_STORE'],
            'TXT_SUPPORT_ACTION'                     => $_ARRAYLANG['TXT_SUPPORT_ACTION'],
            'TXT_SUPPORT_ACTION_IS_IRREVERSIBLE'     => $_ARRAYLANG['TXT_SUPPORT_ACTION_IS_IRREVERSIBLE'],
            'TXT_SUPPORT_ACTIVE'                     => $_ARRAYLANG['TXT_SUPPORT_ACTIVE'],
            'TXT_SUPPORT_INFO_FIELDS'                => $_ARRAYLANG['TXT_SUPPORT_INFO_FIELDS'],
            'TXT_SUPPORT_INFO_FIELDS_COUNT'          => $_ARRAYLANG['TXT_SUPPORT_INFO_FIELDS_COUNT'],
            'TXT_SUPPORT_INFO_FIELDS_COUNT_TOTAL'    => $_ARRAYLANG['TXT_SUPPORT_INFO_FIELDS_COUNT_TOTAL'],
            'TXT_SUPPORT_INFO_FIELD_ID'              => $_ARRAYLANG['TXT_SUPPORT_ID'],
            'TXT_SUPPORT_INFO_FIELD_ORDER'           => $_ARRAYLANG['TXT_CORE_SORTING_ORDER'],
            'TXT_SUPPORT_INFO_FIELDS_DELETE_CONFIRM' => $_ARRAYLANG['TXT_SUPPORT_INFO_FIELDS_DELETE_CONFIRM'],
            'TXT_SUPPORT_INFO_FIELD_DELETE_CONFIRM'  => $_ARRAYLANG['TXT_SUPPORT_INFO_FIELD_DELETE_CONFIRM'],
            'TXT_SUPPORT_INFO_FIELDS_DELETE'         => $_ARRAYLANG['TXT_SUPPORT_INFO_FIELDS_DELETE'],
            'TXT_SUPPORT_INFO_FIELD_DELETE'          => $_ARRAYLANG['TXT_SUPPORT_INFO_FIELD_DELETE'],
            'TXT_SUPPORT_DELETE_MARKED'              => $_ARRAYLANG['TXT_SUPPORT_DELETE_MARKED'],
            'TXT_SUPPORT_LANGUAGE_SHOW_ALL'          => $_ARRAYLANG['TXT_SUPPORT_LANGUAGE_SHOW_ALL'],
            'TXT_SUPPORT_MAKE_SELECTION'             => $_ARRAYLANG['TXT_SUPPORT_MAKE_SELECTION'],
            'TXT_SUPPORT_INFO_FIELDS_MARKED'         => $_ARRAYLANG['TXT_SUPPORT_INFO_FIELDS_MARKED'],
            'TXT_SUPPORT_SELECT_ACTION'              => $_ARRAYLANG['TXT_SUPPORT_SELECT_ACTION'],
            'TXT_SUPPORT_SELECT_ALL'                 => $_ARRAYLANG['TXT_SUPPORT_SELECT_ALL'],
            'TXT_SUPPORT_SELECT_NONE'                => $_ARRAYLANG['TXT_SUPPORT_SELECT_NONE'],
            'TXT_SUPPORT_STORE'                      => $_ARRAYLANG['TXT_SUPPORT_STORE'],
            'TXT_CORE_SORTING_ORDER'                 => $_ARRAYLANG['TXT_CORE_SORTING_ORDER'],
            'SUPPORT_EDIT_LANGUAGE_MENU'             =>
                $this->objLanguage->getMenu(
                    $this->supportLanguageId,
                    'supportLanguageId',
                    "window.location.replace('index.php?cmd=support&".
                    CSRF::param() .
                    "&amp;act=infoFieldsEdit".
                    "&amp;supportInfoFieldId=$this->supportInfoFieldId".
                    "&amp;supportInfoFieldLanguageId=$this->supportInfoFieldLanguageId".
                    "&amp;supportLanguageId='+this.value+'".
                    "&amp;supportLanguageShowAll=$this->supportLanguageShowAll')"
//                    "&amp;supportInfoFieldOffset=$this->supportInfoFieldOffset".
                ),
        ));

        $objTemplate->setGlobalVariable(array(
            'SUPPORT_LANGUAGE_SHOW_ALL'         =>
                ($this->supportLanguageShowAll ? '1' : ''),
            'SUPPORT_LANGUAGE_SHOW_ALL_CHECKED' =>
                ($this->supportLanguageShowAll ? ' checked="checked"' : ''),
            'SUPPORT_EDIT_LANGUAGE_ID' => $this->supportLanguageId,
            'TXT_SUPPORT_LANGUAGE'     => $_ARRAYLANG['TXT_SUPPORT_LANGUAGE'],
            'TXT_SUPPORT_NAME'         => $_ARRAYLANG['TXT_SUPPORT_NAME'],
            'TXT_SUPPORT_STATUS'       => $_ARRAYLANG['TXT_SUPPORT_STATUS'],
            'TXT_SUPPORT_DELETE'       => $_ARRAYLANG['TXT_SUPPORT_INFO_FIELD_DELETE'],
            'TXT_SUPPORT_EDIT'         => $_ARRAYLANG['TXT_SUPPORT_INFO_FIELD_EDIT'],
            'TXT_SUPPORT_MANDATORY'    => $_ARRAYLANG['TXT_SUPPORT_MANDATORY'],
            'TXT_SUPPORT_MULTIPLE'     => $_ARRAYLANG['TXT_SUPPORT_MULTIPLE'],
            'TXT_SUPPORT_TYPE'         => $_ARRAYLANG['TXT_SUPPORT_TYPE'],
//            'SUPPORT_INFO_FIELD_OFFSET' => $this->supportInfoFieldOffset,
        ));

        // List Info Fields by language
        $arrInfoFields =
            $this->objInfoFields->getInfoFieldArray(
                $this->supportLanguageId, false
            );
//if (MY_DEBUG) { echo("got Info Field tree:<br />");var_export($arrInfoField);echo("<br />"); }
        if ($arrInfoFields === false) {
if (MY_DEBUG) echo("failed to get Info Field tree<br />");
        }

        $objTemplate->setCurrentBlock('infoFieldRow');
        foreach ($arrInfoFields as $arrInfoField) {
            $objTemplate->setVariable(
                'INFO_FIELD_ROW',
                $this->infoFieldRow($arrInfoField)
            );
            $objTemplate->parseCurrentBlock();
        }

        $objTemplate->setCurrentBlock();
        // Edit Info Field
        if ($this->supportInfoFieldId > 0) {
            $languageId = $this->supportInfoFieldLanguageId;
            if ($languageId <= 0) {
                $languageId = $this->supportLanguageId;
            }
            // Some InfoField is selected by ID
if (MY_DEBUG) { echo("infoFieldsEdit(): id is ");var_export($this->supportInfoFieldId);echo("<br />"); }
            $arrInfoField =
                $this->objInfoFields->getArrayById($this->supportInfoFieldId);
            // Edit the existing Info Field
            $objTemplate->setVariable(array(
                'SUPPORT_INFO_FIELD_ID'                =>
                    $this->supportInfoFieldId,
                'SUPPORT_INFO_FIELD_STATUS_CHECKED'    =>
                    ($arrInfoField['status']    ? ' checked="checked"' : ''),
                'SUPPORT_INFO_FIELD_MANDATORY_CHECKED' =>
                    ($arrInfoField['mandatory'] ? ' checked="checked"' : ''),
                'SUPPORT_INFO_FIELD_MULTIPLE_CHECKED'  =>
                    ($arrInfoField['multiple']  ? ' checked="checked"' : ''),
                'SUPPORT_INFO_FIELD_TYPE_MENU'         =>
                    InfoFields::getTypeMenu(
                        $arrInfoField['type'],
                        'supportInfoFieldType'),
                'SUPPORT_INFO_FIELD_ORDER'             =>
                    $arrInfoField['order'],
                'SUPPORT_INFO_FIELD_LANGUAGE_MENU'     =>
                    $this->objLanguage->getMenu(
                        $languageId,
                        'supportInfoFieldLanguageId'
                    ),
                'SUPPORT_INFO_FIELD_NAME'              =>
                    $arrInfoField['arrName'][$languageId],
            ));
        } else {
            // Default values
            $objTemplate->setVariable(array(
                'SUPPORT_INFO_FIELD_ID'             => 0,
                'SUPPORT_INFO_FIELD_STATUS_CHECKED' => ' checked="checked"',
                    'SUPPORT_INFO_FIELD_TYPE_MENU'  =>
                        InfoFields::getTypeMenu(0, 'supportInfoFieldType' ),
                'SUPPORT_INFO_FIELD_ORDER'          => 0,
                'SUPPORT_INFO_FIELD_LANGUAGE_MENU'  =>
                    $this->objLanguage->getMenu(
                        $this->supportLanguageId, 'supportInfoFieldLanguageId'),
//                'SUPPORT_INFO_FIELD_MANDATORY_CHECKED' => '',
//                'SUPPORT_INFO_FIELD_MULTIPLE_CHECKED'  => '',
            ));
        }
        return $objTemplate->get();
    }


    /**
     * Set up a single Info Field row.
     *
     * Takes an array of a Info Field as provided by an element of
     * the array returned by
     * {@link InfoFields::getInfoFieldArray()}.
     * @param   array   $arrInfoField     The array with the Support
     *                                          Category data
     * @return  string                          The HTML content
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function infoFieldRow($arrInfoField)
    {
        global $moduloRow;

        $objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/support/template');
        CSRF::add_placeholder($objTemplate);
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $objTemplate->loadTemplateFile('module_support_info_field_row.html', true, true);

        $objTemplate->setCurrentBlock('supportInfoFieldRow');
        $objTemplate->setVariable(array(
            'SUPPORT_ROW_CLASS'                 =>
                (++$moduloRow % 2 ? 'row2' : 'row1'),
            'SUPPORT_INFO_FIELD_ID'             => $arrInfoField['id'],
            'SUPPORT_INFO_FIELD_LANGUAGE_ID'    => $arrInfoField['languageId'],
            'SUPPORT_INFO_FIELD_STATUS_CHECKED' =>
                ($arrInfoField['status']
                    ? ' checked="checked"'
                    : ''
                ),
            'SUPPORT_INFO_FIELD_MANDATORY_CHECKED' =>
                ($arrInfoField['mandatory']
                    ? ' checked="checked"' : ''
                ),
            'SUPPORT_INFO_FIELD_MULTIPLE_CHECKED' =>
                ($arrInfoField['multiple']
                    ? ' checked="checked"' : ''
                ),
            'SUPPORT_INFO_FIELD_ORDER'    => $arrInfoField['order'],
            'SUPPORT_INFO_FIELD_LANGUAGE' =>
                $this->objLanguage->getLanguageParameter(
                    $arrInfoField['languageId'], 'name'
                ),
            'SUPPORT_EDIT_LANGUAGE_ID'    => $this->supportLanguageId,
            'SUPPORT_INFO_FIELD_NAME'     => $arrInfoField['name'],
            'SUPPORT_INFO_FIELD_TYPE'     =>
                InfoField::getTypeString($arrInfoField['type']),
            'SUPPORT_INFO_FIELD_OFFSET'   => $this->supportInfoFieldOffset,
            'SUPPORT_LANGUAGE_SHOW_ALL'         =>
                ($this->supportLanguageShowAll ? '1' : ''),
//            'SUPPORT_EDIT_LANGUAGE_ID'    => $this->supportLanguageId,
        ));
        $objTemplate->parseCurrentBlock();
        $arrName = $arrInfoField['arrName'];
        if ($this->supportLanguageShowAll) {
            if (is_array($arrName)) {
                $objTemplate->setCurrentBlock('supportInfoFieldLanguageRow');
                foreach ($arrName as $languageId => $name) {
                    // Skip selected language ID, this is displayed above already.
                    if ($languageId == $arrInfoField['languageId']) {
                        continue;
                    }
                    $objTemplate->setVariable(array(
                        'SUPPORT_ROW_CLASS'                 =>
                            ($moduloRow % 2 ? 'row2' : 'row1'),
                        'SUPPORT_INFO_FIELD_ID'       => $arrInfoField['id'],
                        'SUPPORT_INFO_FIELD_LANGUAGE_ID' => $languageId,
                        'SUPPORT_INFO_FIELD_LANGUAGE' =>
                            $this->objLanguage->getLanguageParameter(
                                $languageId, 'name'
                            ),
                        'SUPPORT_EDIT_LANGUAGE_ID'    => $this->supportLanguageId,
                        'SUPPORT_INFO_FIELD_NAME'     => $name,
                        'SUPPORT_INFO_FIELD_OFFSET'   => $this->supportInfoFieldOffset,
                        'SUPPORT_LANGUAGE_SHOW_ALL'         =>
                            ($this->supportLanguageShowAll ? '1' : ''),
                    ));
                    $objTemplate->parseCurrentBlock();
                }
            } else {
if (MY_DEBUG) echo("Support::infoFieldRow(...): ERROR: Missing Name array!<br />");
            }
        }
        return $objTemplate->get();
    }


    /**
     * Store the Info Field currently being edited.
     *
     * Note that the Info Field tree array in the InfoFields
     * object will be outdated after inserting a new InfoField.  Don't
     * forget to reinitialize it, or you won't see the new entry!
     * @return  boolean             True on success, false otherwise
     * @global  array   $_ARRAYLANG Language array
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function infoFieldStore()
    {
        global $_ARRAYLANG;

        if (empty($_POST['supportInfoFieldName'])) {
            $this->addError($_ARRAYLANG['TXT_SUPPORT_INFO_FIELD_FILL_IN_NAME']);
            return false;
        }
        if (empty($_POST['supportInfoFieldType'])) {
            $this->addError($_ARRAYLANG['TXT_SUPPORT_INFO_FIELD_SELECT_TYPE']);
            return false;
        }
//if (MY_DEBUG) { echo("POST: ");var_export($_POST);echo("<br />"); }
        $supportInfoFieldId = $this->supportInfoFieldId;
        $supportInfoFieldName = $_POST['supportInfoFieldName'];
        $supportInfoFieldType = $_POST['supportInfoFieldType'];
        $supportInfoFieldMandatory =
            (!empty($_POST['supportInfoFieldMandatory'])
                ? true : false
            );
        $supportInfoFieldMultiple =
            (!empty($_POST['supportInfoFieldMultiple'])
                ? true : false
            );
        $supportInfoFieldStatus =
            (!empty($_POST['supportInfoFieldStatus'])
                ? true : false
            );
        $supportInfoFieldLanguageId =
            (!empty($_POST['supportInfoFieldLanguageId'])
                ? $_POST['supportInfoFieldLanguageId'] : 0
            );
        $supportInfoFieldOrder =
            (!empty($_POST['supportInfoFieldOrder'])
                ? $_POST['supportInfoFieldOrder'] : 0
            );
        $objInfoField = new InfoField(
            $supportInfoFieldName,
            $supportInfoFieldType,
            $supportInfoFieldLanguageId,
            $supportInfoFieldMandatory,
            $supportInfoFieldMultiple,
            $supportInfoFieldStatus,
            $supportInfoFieldOrder,
            $supportInfoFieldId
        );
        if (!$objInfoField) {
if (MY_DEBUG) echo("infoFieldStore(): ERROR: Failed to create InfoField object!<br />");
            return false;
        }
if (MY_DEBUG) { echo("infoFieldStore(): ");var_export($objInfoField);echo("<br />"); }
        if ($objInfoField->store()) {
            $this->addMessage($_ARRAYLANG['TXT_SUPPORT_UPDATE_SUCCESSFUL']);
            // Clear the ID of the Info Field, so the User
            // can create a new one after that.
            $this->supportInfoFieldId = 0;
            // *MUST* invalidate the tree array after
            // changing the data!
            $this->objInfoFields->invalidateInfoFieldArray();
            return true;
        }
        $this->addError($_ARRAYLANG['TXT_SUPPORT_UPDATE_FAILED']);
        return false;
    }


    /**
     * Store all changes made to the Info Fields shown.
     * @return  boolean             True on success, false otherwise
     * @global  array       $_ARRAYLANG     Language array
     * @global  mixed       $objInit        Init object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function infoFieldsStore()
    {
        global $_ARRAYLANG, $objInit;

if (MY_DEBUG) { echo("infoFieldStore(): INFO: Entered.<br />"); }

        $return = true;
        $flagChanged = false;

        foreach ($_POST['supportInfoFieldArrayId'] as $id) {
            $postOrder = $_POST['supportInfoFieldArrayOrder'][$id];
            $postMandatory =
                (!empty($_POST['supportInfoFieldArrayMandatory'][$id])
                    ? true : false
                );
            $postMultiple =
                (!empty($_POST['supportInfoFieldArrayMultiple'][$id])
                    ? true : false
                );
            $postStatus =
                (!empty($_POST['supportInfoFieldArrayStatus'][$id])
                    ? true : false
                );
            $arrInfoField = $this->objInfoFields->getArrayById($id);
            $order  = $arrInfoField['order'];
            $mandatory = $arrInfoField['mandatory'];
            $multiple = $arrInfoField['multiple'];
            $status = $arrInfoField['status'];
if (MY_DEBUG) { echo("infoFieldStore(): INFO: id=$id, order=$order, mandatory=$mandatory, multiple=$multiple, status=$status<br />"); }
if (MY_DEBUG) { echo("infoFieldStore(): INFO: id=$id, postOrder=$postOrder, postMandatory=$postMandatory, postMultiple=$postMultiple, postStatus=$postStatus<br />"); }
            if (   !empty($_POST['supportInfoFieldArrayId'][$id])
                && (   $order  != $postOrder
                    || $mandatory != $postMandatory
                    || $multiple != $postMultiple
                    || $status != $postStatus)
            ) {
if (MY_DEBUG) { echo("infoFieldStore(): INFO: updating id=$id<br />"); }
                $objInfoField = InfoField::getById($id);
                if (!$objInfoField) {
if (MY_DEBUG) { echo("infoFieldStore(): ERROR: Failed to get InfoField with id=$id!<br />"); }
                    $this->addError($_ARRAYLANG['TXT_SUPPORT_UPDATE_FAILED']);
                    $return = false;
                } else {
                    $objInfoField->setOrder($postOrder);
                    $objInfoField->setMandatory($postMandatory);
                    $objInfoField->setMultiple($postMultiple);
                    $objInfoField->setStatus($postStatus);
                    if (!$objInfoField->store()) {
if (MY_DEBUG) { echo("infoFieldStore(): ERROR: Failed to update InfoField with id=$id!<br />"); }
                        $this->addError($_ARRAYLANG['TXT_SUPPORT_UPDATE_FAILED'].
                            ", ID $id -- ".$objInfoField->getName());
                        $return = false;
                    } else {
                        $flagChanged = true;
                    }
                }
            }
        }
        if ($flagChanged) {
            // *MUST* invalidate the InfoField array
            // after changing the data!
            $this->objInfoFields->invalidateInfoFieldArray();
        }
        if ($return) {
            $this->addMessage($_ARRAYLANG['TXT_SUPPORT_UPDATE_SUCCESSFUL']);
        } else {
            $this->addError($_ARRAYLANG['TXT_SUPPORT_UPDATE_FAILED']);
        }
        return $return;
    }

}

?>
