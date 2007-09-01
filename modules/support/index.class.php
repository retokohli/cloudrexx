<?php

define('MY_DEBUG', 3);

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
require_once ASCMS_MODULE_PATH.'/support/lib/Ticket.class.php';
 */
/**
 * Provides clickable table headers for sorting
require_once ASCMS_CORE_PATH.'/Sorting.class.php';
 */


/**
 * The initial support request state.
 * No User information is present yet, and the process is about to begin.
 */
define('SUPPORT_REQUEST_STATUS_START',         0);
/**
 * The Support Category is present.
 */
define('SUPPORT_REQUEST_STATUS_CATEGORY',   1<<0);
/**
 * The subject is present.
 */
define('SUPPORT_REQUEST_STATUS_SUBJECT',    1<<1);
/**
 * The message text is present.
 */
define('SUPPORT_REQUEST_STATUS_MESSAGE',    1<<2);
/**
 * The Attachment array is present or unused.
 */
define('SUPPORT_REQUEST_STATUS_ATTACHMENT', 1<<3);
/**
 * The Info Field array is present or unused.
 */
define('SUPPORT_REQUEST_STATUS_INFOFIELD',  1<<4);
/**
 * The User name is present.
 */
define('SUPPORT_REQUEST_STATUS_NAME',       1<<5);
/**
 * The User e-mail is present.
 */
define('SUPPORT_REQUEST_STATUS_EMAIL',      1<<6);
/**
 * The Ticket ID is present.
 */
define('SUPPORT_REQUEST_STATUS_TICKET',     1<<7);
/**
 * All bits are present.  This completes the request cycle.
 * Keep this up to date!
 */
define('SUPPORT_REQUEST_STATUS_COMPLETE',
      SUPPORT_REQUEST_STATUS_START
    | SUPPORT_REQUEST_STATUS_CATEGORY
    | SUPPORT_REQUEST_STATUS_SUBJECT
    | SUPPORT_REQUEST_STATUS_MESSAGE
    | SUPPORT_REQUEST_STATUS_ATTACHMENT
    | SUPPORT_REQUEST_STATUS_INFOFIELD
    | SUPPORT_REQUEST_STATUS_NAME
    | SUPPORT_REQUEST_STATUS_EMAIL
    | SUPPORT_REQUEST_STATUS_TICKET
);


/**
 * Support system frontend
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
     * Status message
     * @access  private
     * @var     string
     */
    var $statusMessage = '';

    /**
     * The Support Categories object
     *
     * Do not confuse this with the Support Category object!
     * @var     SupportCategories
     */
    var $objSupportCategories;

    /**
     * The current status of the support request.
     * Defaults to START.
     * @var     integer
     */
    var $supportStatus = SUPPORT_REQUEST_STATUS_START;

    /**
     * The Support Category chosen by the User
     * @var     integer
     */
    var $supportCategoryId;

    /**
     * The User's e-mail address
     * @var     string
     */
    var $supportEmail;

    /**
     * The Ticket subject
     * @var     string
     */
    var $supportSubject;

    /**
     * The Ticket body text
     * @var     string
     */
    var $supportBody;

    /**
     * The Info Field array
     * @var     array
     */
    var $arrSupportInfoField;

    /**
     * The Attachment array
     * @var     array
     */
    var $arrSupportAttachment;

    /**
     * The Ticket ID
     * @var     integer
     */
    var $supportTicketId;


    /**
     * Constructor (PHP4)
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     * @param       string  $strTemplate
     * @see     __construct()
     */
    function Support($strTemplate)
    {
        $this->__construct($strTemplate);
    }

    /**
     * Constructor (PHP5)
     * @param       string      $strTemplate    Template name
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     */
    function __construct($strTemplate)
    {
        global $objInit;

        if (MY_DEBUG && 1) {
            error_reporting(E_ALL); ini_set('display_errors', 1);
        } else {
            error_reporting(0); ini_set('display_errors', 0);
        }
        if (MY_DEBUG && 2) {
            global $objDatabase; $objDatabase->debug = 1;
        }

        $this->objTemplate = new HTML_Template_Sigma('.');
        $this->objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $this->objTemplate->setTemplate($strTemplate);

        $this->objSupportCategories =
            new SupportCategories($objInit->getFrontendLangId());

        if (isset($_REQUEST['supportCategoryId'])) {
            $this->supportCategoryId == $_REQUEST['supportCategoryId'];
            // The status is at its default, START, before this.
            $this->supportStatus = SUPPORT_REQUEST_STATUS_CATEGORY;
        }
        if (isset($_REQUEST['supportEmail'])) {
            $this->supportEmail = $_REQUEST['supportEmail'];
            if ($this->supportStatus == SUPPORT_REQUEST_STATUS_CATEGORY) {
                $this->supportStatus = SUPPORT_REQUEST_STATUS_EMAIL;
            }
        }
        if (isset($_REQUEST['supportSubject'])) {
            $this->supportSubject = $_REQUEST['supportSubject'];
            if ($this->supportStatus == SUPPORT_REQUEST_STATUS_EMAIL) {
                $this->supportStatus = SUPPORT_REQUEST_STATUS_SUBJECT;
            }
        }
        if (isset($_REQUEST['supportBody'])) {
            $this->supportBody = $_REQUEST['supportBody'];
            if ($this->supportStatus == SUPPORT_REQUEST_STATUS_SUBJECT) {
                $this->supportStatus = SUPPORT_REQUEST_STATUS_MESSAGE;
            }
        }
        if (isset($_REQUEST['arrSupportInfoField'])) {
            $this->arrSupportInfoField = $_REQUEST['arrSupportInfoField'];
            if ($this->supportStatus == SUPPORT_REQUEST_STATUS_MESSAGE) {
                $this->supportStatus = SUPPORT_REQUEST_STATUS_ATTACHMENT;
            }
        }
        if (isset($_REQUEST['arrSupportAttachment'])) {
            $this->arrSupportAttachment = $_REQUEST['arrSupportAttachment'];
            if ($this->supportStatus == SUPPORT_REQUEST_STATUS_ATTACHMENT) {
                $this->supportStatus = SUPPORT_REQUEST_STATUS_INFOFIELD;
            }
        }
        if (isset($_REQUEST['supportTicketId'])) {
            $this->supportTicketId = $_REQUEST['supportTicketId'];
            if ($this->supportStatus == SUPPORT_REQUEST_STATUS_INFOFIELD) {
                $this->supportStatus = SUPPORT_REQUEST_STATUS_TICKET;
            }
        }
        //SUPPORT_REQUEST_STATUS_COUNT
    }


    /**
     * Call the appropriate method to set up the requested page.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     * @access  public
     * @return  string      The created content
     */
    function getPage()
    {
        if (!isset($_GET['cmd'])) {
            $_GET['cmd'] = '';
        }
        switch ($_GET['cmd']) {
          case 'ticket':
            $this->browseTickets();
            break;
          case 'knowledgebase':
            $this->browseKnowledgebase();
            break;
/*
          case '':
            $this->_();
            break;
*/
          default:
            $this->supportRequest();
            break;
        }
        return $this->objTemplate->get();
    }


    /**
     * Set up and return the Support Ticket welcome page HTML.
     *
     * Welcome the User and let her choose a Support Category for the Ticket.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     * @return      string          The support ticket welcome page
     * @global      $_ARRAYLANG     Language array
     */
    function supportRequest()
    {
        global $_ARRAYLANG;

//$this->dumpTemplate($this->objTemplate);

        $this->objTemplate->setVariable(array(
// __global__ and text constants
'TXT_SUPPORT_REQUEST_STATUS_1'          => $_ARRAYLANG['TXT_SUPPORT_REQUEST_STATUS_CATEGORY'],
'TXT_SUPPORT_REQUEST_WELCOME'           => $_ARRAYLANG['TXT_SUPPORT_REQUEST_WELCOME'],
'TXT_SUPPORT_REQUEST_CHOOSE_CATEGORY'   => $_ARRAYLANG['TXT_SUPPORT_REQUEST_CHOOSE_CATEGORY'],
'TXT_SUPPORT_REQUEST_STATUS_2'          => $_ARRAYLANG['TXT_SUPPORT_REQUEST_STATUS_SUBJECT'],
'TXT_SUPPORT_REQUEST_ENTER_SUBJECT'     => $_ARRAYLANG['TXT_SUPPORT_REQUEST_ENTER_SUBJECT'],
'TXT_SUPPORT_REQUEST_STATUS_3'          => $_ARRAYLANG['TXT_SUPPORT_REQUEST_STATUS_MESSAGE'],
'TXT_SUPPORT_REQUEST_ENTER_MESSAGE'     => $_ARRAYLANG['TXT_SUPPORT_REQUEST_ENTER_MESSAGE'],
'TXT_SUPPORT_REQUEST_STATUS_4'          => $_ARRAYLANG['TXT_SUPPORT_REQUEST_STATUS_ATTACHMENT'],
'TXT_SUPPORT_REQUEST_PRESENT_ATTACHMENTS' => $_ARRAYLANG['TXT_SUPPORT_REQUEST_PRESENT_ATTACHMENTS'],
'TXT_SUPPORT_REQUEST_SPECIFY_ATTACHMENT' => $_ARRAYLANG['TXT_SUPPORT_REQUEST_SPECIFY_ATTACHMENT'],
'TXT_SUPPORT_REQUEST_STATUS_5'          => $_ARRAYLANG['TXT_SUPPORT_REQUEST_STATUS_INFOFIELD'],
'TXT_SUPPORT_REQUEST_PROVIDE_INFO'      => $_ARRAYLANG['TXT_SUPPORT_REQUEST_PROVIDE_INFO'],
'TXT_SUPPORT_REQUEST_STATUS_6'          => $_ARRAYLANG['TXT_SUPPORT_REQUEST_STATUS_NAME'],
'TXT_SUPPORT_REQUEST_PROVIDE_NAME'      => $_ARRAYLANG['TXT_SUPPORT_REQUEST_PROVIDE_NAME'],
'TXT_SUPPORT_REQUEST_STATUS_7'          => $_ARRAYLANG['TXT_SUPPORT_REQUEST_STATUS_EMAIL'],
'TXT_SUPPORT_REQUEST_PROVIDE_EMAIL'     => $_ARRAYLANG['TXT_SUPPORT_REQUEST_PROVIDE_EMAIL'],
'TXT_SUPPORT_REQUEST_STATUS_8'          => $_ARRAYLANG['TXT_SUPPORT_REQUEST_STATUS_TICKET'],
'TXT_SUPPORT_REQUEST_CONTINUE'          => $_ARRAYLANG['TXT_SUPPORT_REQUEST_CONTINUE'],
'TXT_SUPPORT_REQUEST_INCOMPLETE'        => $_ARRAYLANG['TXT_SUPPORT_REQUEST_INCOMPLETE'],
'TXT_SUPPORT_REQUEST_COMPLETE_DATA'     => $_ARRAYLANG['TXT_SUPPORT_REQUEST_COMPLETE_DATA'],
'TXT_SUPPORT_REQUEST_COMPLETE'          => $_ARRAYLANG['TXT_SUPPORT_REQUEST_COMPLETE'],
'TXT_SUPPORT_REQUEST_YOUR_TICKET'       => $_ARRAYLANG['TXT_SUPPORT_REQUEST_YOUR_TICKET'],
'TXT_SUPPORT_REQUEST_THANK_YOU'         => $_ARRAYLANG['TXT_SUPPORT_REQUEST_THANK_YOU'],
'SUPPORT_REQUEST_CATEGORIES_MENU'       => $this->objSupportCategories->getMenu($this->supportCategoryId),
'SUPPORT_REQUEST_SUBJECT'   => '',
'SUPPORT_REQUEST_MESSAGE'   => '',
'SUPPORT_REQUEST_USER_NAME' => '',
'SUPPORT_REQUEST_USER_EMAIL' => '',
'SUPPORT_REQUEST_STYLE_1'   => '',
'SUPPORT_REQUEST_STYLE_2'   => '',
'SUPPORT_REQUEST_STYLE_3'   => '',
'SUPPORT_REQUEST_STYLE_4'   => '',
'SUPPORT_REQUEST_STYLE_5'   => '',
'SUPPORT_REQUEST_STYLE_6'   => '',
'SUPPORT_REQUEST_STYLE_7'   => '',
'SUPPORT_REQUEST_STYLE_8'   => '',
// attachmentRow
'SUPPORT_REQUEST_ATTACHMENT_NAME'   => '',
'SUPPORT_REQUEST_ATTACHMENT_VALUE'  => '',
'SUPPORT_REQUEST_ATTACHMENT_DELETE' => '',
// infofieldRow
'SUPPORT_REQUEST_INFOFIELD_ID'      => '',
'SUPPORT_REQUEST_INFOFIELD_TYPE'    => '',
'SUPPORT_REQUEST_INFOFIELD_NAME'    => '',
'SUPPORT_REQUEST_INFOFIELD_VALUE'   => '',
'SUPPORT_REQUEST_INFOFIELD_MANDATORY' => '',
'SUPPORT_REQUEST_INFOFIELD_MULTIPLE' => '',
// requestComplete
'SUPPORT_REQUEST_TICKET_ID' => '',
// requestIncomplete -- text only, no values
        ));
        $this->objTemplate->parse();
        return $this->objTemplate->get();
    }






    /** TEST */
    function dumpTemplate($objTemplate, $block='__global__')
    {
//echo("Support::supportRequest(): INFO: List of blocks and placeholders:<br />");
        $arrBlockList = $this->objTemplate->getBlockList($block, true);
        if (count($arrBlockList) == 0) {
//echo("Support::supportRequest(): INFO: List of blocks is empty.<br />");
            $arrBlockList[] = '__global__';
        }
        foreach ($arrBlockList as $index => $block) {
echo("== $block<br />");
            $arrBlock = false;
            if (is_array($block)) {
                foreach ($block as $index => $blockName) {
                    foreach ($blockName as $index => $block) {
                    	$this->dumpTemplate($objTemplate, $blockName[$index]);
                    }
                }
            } else {
                $arrPlaceholder = $this->objTemplate->getPlaceholderList($block);
                foreach ($arrPlaceholder as $index => $placeholderName) {
echo("$placeholderName<br />");
                }
            }
        }
//echo("Support::supportRequest(): INFO: End of list.<br />");
        return true;
    }

}

?>
