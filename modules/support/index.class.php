<?php

define('MY_DEBUG', 1);

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
 * Info Fields
 */
require_once ASCMS_MODULE_PATH.'/support/lib/InfoFields.class.php';
/**
 * Info Field
 */
require_once ASCMS_MODULE_PATH.'/support/lib/InfoField.class.php';
/**
 * Support Ticket
 */
require_once ASCMS_MODULE_PATH.'/support/lib/Ticket.class.php';
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
 * The Info Field array is present or unused.
 */
define('SUPPORT_REQUEST_STATUS_INFOFIELD',  1<<3);
/**
 * The User name is present.
 */
define('SUPPORT_REQUEST_STATUS_NAME',       1<<4);
/**
 * The User e-mail is present.
 */
define('SUPPORT_REQUEST_STATUS_EMAIL',      1<<5);
/**
 * The Ticket is created and presented to the User.
 */
define('SUPPORT_REQUEST_STATUS_TICKET',     1<<6);
/**
 * When all required fields have been filled,
 * we're ready to request the Ticket.
 */
define('SUPPORT_REQUEST_STATUS_READY',
      SUPPORT_REQUEST_STATUS_CATEGORY
    | SUPPORT_REQUEST_STATUS_SUBJECT
    | SUPPORT_REQUEST_STATUS_MESSAGE
    | SUPPORT_REQUEST_STATUS_INFOFIELD
    | SUPPORT_REQUEST_STATUS_NAME
    | SUPPORT_REQUEST_STATUS_EMAIL
);
/**
 * All bits are present.  This completes the request cycle.
 * Keep this up to date!
 */
define('SUPPORT_REQUEST_STATUS_COMPLETE',
      SUPPORT_REQUEST_STATUS_CATEGORY
    | SUPPORT_REQUEST_STATUS_SUBJECT
    | SUPPORT_REQUEST_STATUS_MESSAGE
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
     * Do not confuse this with the Support Category class!
     * @var     SupportCategories
     */
    var $objSupportCategories;

    /**
     * The Info Fields object
     *
     * Do not confuse this with the Info Field class!
     * @var     InfoFields
     */
    var $objInfoFields;

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
    var $arrSupportInfoField = array();

    /**
     * The name of the User
     * @var     string
     */
    var $supportName;

    /**
     * The e-mail address of the User
     * @var     string
     */
    var $supportEmail;

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

        if (MY_DEBUG & 1) {
            error_reporting(E_ALL); ini_set('display_errors', 1);
        } else {
            error_reporting(0); ini_set('display_errors', 0);
        }
        if (MY_DEBUG & 2) {
            global $objDatabase; $objDatabase->debug = 1;
        }

        $this->objTemplate = new HTML_Template_Sigma('.');
        CSRF::add_placeholder($this->objTemplate);
        $this->objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $this->objTemplate->setTemplate($strTemplate);

        $this->objSupportCategories =
            new SupportCategories(FRONTEND_LANG_ID);
        $this->objInfoFields =
            new InfoFields(FRONTEND_LANG_ID);
if (MY_DEBUG) { echo("Support::__construct(): POST: ");var_export($_POST);echo("<br />"); }
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
//echo("Template 1: ".htmlentities($this->objTemplate->get())."<br />");
            $strTemplate = join('', file(ASCMS_DOCUMENT_ROOT.'/modules/support/template/frontend_module_support_request.html'));
            $strTemplate = preg_replace('/\[\[([^\[\]]+)\]\]/', '{$1}', $strTemplate);
            $this->objTemplate->setTemplate($strTemplate);
//echo("Template 2: ".htmlentities($this->objTemplate->get())."<br />");
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
     * @global      $objInit        Init object
     */
    function supportRequest()
    {
        global $_ARRAYLANG, $objInit;

        // Needs to be initialized for InfoFields::isComplete()
        $this->objInfoFields->getInfoFieldArray(FRONTEND_LANG_ID);

        // The status is at its default, START, before this.
        if (!empty($_REQUEST['supportCategoryId'])) {
if (MY_DEBUG) echo("Support::supportRequest(): got category id, ");
            $this->supportCategoryId = intval($_REQUEST['supportCategoryId']);
            if ($this->supportCategoryId > 0) {
                $this->supportStatus |= SUPPORT_REQUEST_STATUS_CATEGORY;
if (MY_DEBUG) echo("Support::supportRequest():status is now $this->supportStatus, ");
            }
        }
        if (!empty($_REQUEST['supportSubject'])) {
if (MY_DEBUG) echo("Support::supportRequest():got subject, ");
            $this->supportSubject = $_REQUEST['supportSubject'];
            $this->supportStatus |= SUPPORT_REQUEST_STATUS_SUBJECT;
if (MY_DEBUG) echo("Support::supportRequest():status is now $this->supportStatus, ");
        }
        if (!empty($_REQUEST['supportBody'])) {
if (MY_DEBUG) echo("Support::supportRequest():got body, ");
            $this->supportBody = $_REQUEST['supportBody'];
            $this->supportStatus |= SUPPORT_REQUEST_STATUS_MESSAGE;
if (MY_DEBUG) echo("Support::supportRequest():status is now $this->supportStatus, ");
        }
        if (isset($_REQUEST['arrSupportInfoField'])) {
if (MY_DEBUG) echo("Support::supportRequest():got infofield/s, ");
            $this->arrSupportInfoField = $_REQUEST['arrSupportInfoField'];
if (MY_DEBUG) { echo("Support::supportRequest():arrIF: ");var_export($this->arrSupportInfoField);echo("<br />"); }
            if ($this->objInfoFields->isComplete($this->arrSupportInfoField)) {
                $this->supportStatus |= SUPPORT_REQUEST_STATUS_INFOFIELD;
            }
if (MY_DEBUG) echo("Support::supportRequest():status is now $this->supportStatus, ");
        }
        if (!empty($_REQUEST['supportName'])) {
if (MY_DEBUG) echo("Support::supportRequest():got name, ");
            $this->supportName = $_REQUEST['supportName'];
            $this->supportStatus |= SUPPORT_REQUEST_STATUS_NAME;
if (MY_DEBUG) echo("Support::supportRequest():status is now $this->supportStatus, ");
        }
        if (!empty($_REQUEST['supportEmail'])) {
if (MY_DEBUG) echo("Support::supportRequest():got email, ");
            $this->supportEmail = $_REQUEST['supportEmail'];
            $this->supportStatus |= SUPPORT_REQUEST_STATUS_EMAIL;
if (MY_DEBUG) echo("Support::supportRequest():status is now $this->supportStatus, ");
        }
        if (!empty($_REQUEST['supportTicketId'])) {
if (MY_DEBUG) echo("Support::supportRequest():got ticket id, ");
            $this->supportTicketId = $_REQUEST['supportTicketId'];
            $this->supportStatus |= SUPPORT_REQUEST_STATUS_TICKET;
if (MY_DEBUG) echo("Support::supportRequest():status is now $this->supportStatus, ");
        }
if (MY_DEBUG) echo("<br />");
if (MY_DEBUG) echo("Support::supportRequest(): INFO: Support status is $this->supportStatus.<br />");

        $ticketId = 0;
if (MY_DEBUG) echo("Support::supportRequest(): status is $this->supportStatus.<br />");
        if ($this->supportStatus == SUPPORT_REQUEST_STATUS_READY) {
if (MY_DEBUG) echo("Support::supportRequest(): Requesting Ticket.<br />");
            // Try to obtain a Ticket with the parameters posted.
            $ticketId = $this->requestTicket();
if (MY_DEBUG) echo("Support::supportRequest(): Got Ticket ID $ticketId.<br />");
        }
//$this->dumpTemplate($this->objTemplate);

        $this->objTemplate->setVariable(array(
            'TXT_SUPPORT_REQUEST_CONTINUE'      =>
                ($ticketId
                    ? $_ARRAYLANG['TXT_SUPPORT_REQUEST_FINISH']
                    : $_ARRAYLANG['TXT_SUPPORT_REQUEST_CONTINUE']
                ),
            'SUPPORT_REQUEST_CONTINUE_FUNCTION' =>
                ($ticketId
                    ? "JavaScript:window.location.href='index.php?".CSRF::param()."';"
                    : "JavaScript:supportContinue();"
                ),
            'SUPPORT_REQUEST_TICKET_ID'     => $ticketId,
            'SUPPORT_REQUEST_STATUS'        => $this->supportStatus,
        ));

        if ($ticketId > 0) {
            $this->objTemplate->hideBlock('requestIncomplete');
            $this->objTemplate->hideBlock('requestData');
            $this->objTemplate->setVariable(array(
                'TXT_SUPPORT_REQUEST_COMPLETE'          => $_ARRAYLANG['TXT_SUPPORT_REQUEST_COMPLETE'],
                'TXT_SUPPORT_REQUEST_YOUR_TICKET'       => sprintf($_ARRAYLANG['TXT_SUPPORT_REQUEST_YOUR_TICKET'], $ticketId),
                'TXT_SUPPORT_REQUEST_THANK_YOU'         => $_ARRAYLANG['TXT_SUPPORT_REQUEST_THANK_YOU'],
            ));
        } else {
            $this->objTemplate->setVariable(array(
                // __global__ and text constants
                'TXT_SUPPORT_REQUEST_STATUS_0'          => $_ARRAYLANG['TXT_SUPPORT_REQUEST_STATUS_CATEGORY'],
                'TXT_SUPPORT_REQUEST_WELCOME'           => $_ARRAYLANG['TXT_SUPPORT_REQUEST_WELCOME'],
                'TXT_SUPPORT_REQUEST_CHOOSE_CATEGORY'   => $_ARRAYLANG['TXT_SUPPORT_REQUEST_CHOOSE_CATEGORY'],
                'TXT_SUPPORT_REQUEST_STATUS_1'          => $_ARRAYLANG['TXT_SUPPORT_REQUEST_STATUS_SUBJECT'],
                'TXT_SUPPORT_REQUEST_ENTER_SUBJECT'     => $_ARRAYLANG['TXT_SUPPORT_REQUEST_ENTER_SUBJECT'],
                'TXT_SUPPORT_REQUEST_STATUS_2'          => $_ARRAYLANG['TXT_SUPPORT_REQUEST_STATUS_MESSAGE'],
                'TXT_SUPPORT_REQUEST_ENTER_MESSAGE'     => $_ARRAYLANG['TXT_SUPPORT_REQUEST_ENTER_MESSAGE'],
                'TXT_SUPPORT_REQUEST_STATUS_3'          => $_ARRAYLANG['TXT_SUPPORT_REQUEST_STATUS_INFOFIELD'],
                'TXT_SUPPORT_REQUEST_PROVIDE_INFO'      => $_ARRAYLANG['TXT_SUPPORT_REQUEST_PROVIDE_INFO'],
                'TXT_SUPPORT_REQUEST_STATUS_4'          => $_ARRAYLANG['TXT_SUPPORT_REQUEST_STATUS_NAME'],
                'TXT_SUPPORT_REQUEST_PROVIDE_NAME'      => $_ARRAYLANG['TXT_SUPPORT_REQUEST_PROVIDE_NAME'],
                'TXT_SUPPORT_REQUEST_STATUS_5'          => $_ARRAYLANG['TXT_SUPPORT_REQUEST_STATUS_EMAIL'],
                'TXT_SUPPORT_REQUEST_PROVIDE_EMAIL'     => $_ARRAYLANG['TXT_SUPPORT_REQUEST_PROVIDE_EMAIL'],
                'TXT_SUPPORT_REQUEST_STATUS_6'          => $_ARRAYLANG['TXT_SUPPORT_REQUEST_STATUS_TICKET'],
                'TXT_SUPPORT_REQUEST_INCOMPLETE'        => $_ARRAYLANG['TXT_SUPPORT_REQUEST_INCOMPLETE'],
                'TXT_SUPPORT_REQUEST_COMPLETE_DATA'     => $_ARRAYLANG['TXT_SUPPORT_REQUEST_COMPLETE_DATA'],
                'SUPPORT_REQUEST_CATEGORIES_MENU'       => $this->objSupportCategories->getMenu(0, $this->supportCategoryId),
                'SUPPORT_REQUEST_SUBJECT'       => $this->supportSubject,
                'SUPPORT_REQUEST_MESSAGE'       => $this->supportBody,
                'SUPPORT_REQUEST_USER_NAME'     => $this->supportName,
                'SUPPORT_REQUEST_USER_EMAIL'    => $this->supportEmail,
            ));
            $this->objTemplate->hideBlock('requestComplete');
        }

        // Index inside of infofieldRow
        $infoFieldIndex = 0;
        // The array of all available InfoFields
        $arrInfoFields = $this->objInfoFields->getInfoFieldArray(FRONTEND_LANG_ID);
        foreach ($arrInfoFields as $count => $arrInfoField) {
            // Is it the last InfoField?
            $flagIsLast = ($count == count($arrInfoFields));
//echo("Support::supportRequest(): flagIsLast '$flagIsLast', count $count of ".(count($arrInfoFields))."<br />");
            // The current InfoField ID
            $infoFieldId = $arrInfoField['id'];
            // Re-index all InfoFields
            // The InfoField Index starts at 1 (one), and is only incremented
            // for those with the 'multiple' flag set.  Single instance fields
            // do get an index of 0 (zero), indicating that it is not used.
            $arrInfoField['index'] =
                ($arrInfoField['multiple']
                    ? ++$infoFieldIndex
                    : 0
                );
            // Has this been posted back?
            if (isset($this->arrSupportInfoField[$infoFieldId])) {
                // Yes, so keep the non-empty values
                foreach ($this->arrSupportInfoField[$infoFieldId] as $value) {
                    // Only resend non-empty fields
//echo("Support::supportRequest(): Have InfoField ID $infoFieldId, value $value<br />");
                    if (!empty($value)) {
                        $arrInfoField['value'] = $value;
                        $this->objTemplate->setVariable(array(
                            // Only in the last of the InfoFields the
                            // continue function shall be mentioned in the
                            // onchange attribute
                            'SUPPORT_REQUEST_INFOFIELD' =>
                                InfoFields::getHtml($arrInfoField, $flagIsLast),
                            'SUPPORT_REQUEST_INFOFIELD_ID' => $infoFieldId,
                            'SUPPORT_REQUEST_INFOFIELD_INDEX' => $infoFieldIndex,
                        ));
                        $this->objTemplate->parse('infofieldIndex');
                    }
                }
            }
            // If there are no non-empty values at all,
            // include a new empty InfoField of that kind.
            if (!isset($arrInfoField['value'])) {
                $arrInfoField['value'] = '';
                $this->objTemplate->setVariable(array(
                    'SUPPORT_REQUEST_INFOFIELD' =>
                        InfoFields::getHtml($arrInfoField, $flagIsLast),
                    'SUPPORT_REQUEST_INFOFIELD_ID' => $infoFieldId,
                    'SUPPORT_REQUEST_INFOFIELD_INDEX' => $infoFieldIndex,
                ));
                $this->objTemplate->parse('infofieldIndex');
            }
            // The InfoFields of this kind are complete now,
            // proceed with the next ID
                $this->objTemplate->setVariable(array(
                    'SUPPORT_REQUEST_INFOFIELD_ID' => $infoFieldId,
                ));
            $this->objTemplate->parse('infofieldId');
        }
        // Include the next index for the InfoFields.
        // This is needed for indexing new clones.
        $this->objTemplate->setVariable(array(
            'SUPPORT_REQUEST_INFOFIELD_INDEX' => ++$infoFieldIndex,
        ));
        // requestComplete
        $this->objTemplate->setVariable(array(
            'SUPPORT_REQUEST_TICKET_ID' => '',
        ));
        // requestIncomplete -- text only, no values

        // Show / hide steps based on current status
        $hideTheRest = false;
        for ($i = SUPPORT_REQUEST_STATUS_START;
             $i < SUPPORT_REQUEST_STATUS_TICKET;
             ++$i) {
            if (!$hideTheRest && ($this->supportStatus & 1<<$i) == 0) {
                // Show the first of the pending steps as being active
                $this->objTemplate->setVariable(array(
                    'SUPPORT_REQUEST_CLASS_'.$i => 'supportStepActive',
                    'SUPPORT_REQUEST_STYLE_'.$i => 'inline',
                ));
                $hideTheRest = true;
            } else {
                // Hide all following the currently active step
                if ($this->supportStatus & 1<<$i) {
                    // This step has been completed
                    $this->objTemplate->setVariable(array(
                        'SUPPORT_REQUEST_CLASS_'.$i => 'supportStepOk',
                        'SUPPORT_REQUEST_STYLE_'.$i => 'none',
                    ));
                } else {
                    // This is still pending
                    $this->objTemplate->setVariable(array(
                        'SUPPORT_REQUEST_CLASS_'.$i => 'supportStepPending',
                        'SUPPORT_REQUEST_STYLE_'.$i => 'none',
                    ));
                }
            }
        }
        $this->objTemplate->parse();
        return $this->objTemplate->get();
    }


    /**
     * Verify that all necessary data for the Ticket is present,
     * create the Ticket, and return its ID.
     * @return  integer             The Ticket ID on success,
     *                              0 (zero) otherwise.
     */
    function requestTicket()
    {
        global $objInit, $_ARRAYLANG;

        if ($this->supportStatus == SUPPORT_REQUEST_STATUS_READY) {
            // A new Ticket must be created.
            // create a new Ticket from the edited Message.
            $objTicket = new Ticket(
                $this->supportEmail,
                SUPPORT_TICKET_SOURCE_WEB,
                $this->supportCategoryId,
                FRONTEND_LANG_ID
            );
            // Need to store it, so it gets an ID.
            if (!$objTicket->store()) {
                return 0;
            }
//if (MY_DEBUG) { echo("messageCommit(): INFO: Stored new Ticket: ");var_export($objTicket);echo("<br />"); }
            // Adding a Message to the Ticket will create a TicketEvent.
            $messageId = $objTicket->addMessage(
                $this->supportEmail,
                $this->supportSubject,
                $this->supportBody.
                $this->objInfoFields->arrayToText(
                    $this->arrSupportInfoField,
                    FRONTEND_LANG_ID
                )
            );
            if ($messageId == 0) {
                return 0;
            }
            foreach ($_FILES as $arrFile) {
                $error = $arrFile['error'];
                if ($error == UPLOAD_ERR_OK) {
                    $tmpName = $arrFile["tmp_name"];
                    $name    = $arrFile["name"];
                    $type    = $arrFile["type"];
                    $content = file_get_contents($tmpName);
                    $objAttachment = new Attachment(
                        $messageId,
                        $name,
                        $type,
                        $content
                    );
                    $objAttachment->store();
                }
            }
            return $objTicket->getId();
        }
        return 0;
    }


    /**
     * Dump a HTML_Template_Sigma object
     *
     * This is a test.
     * @param   HTML_Template_Sigma     $objTemplate    The template
     * @param   string                  $block          The optional block name
     * @return  boolean                                 Always true
     */
    function dumpTemplate($objTemplate, $block='__global__')
    {
//echo("Support::supportRequest(): INFO: List of blocks and placeholders:<br />");
        $arrBlockList = $this->objTemplate->getBlockList($block, true);
        if (count($arrBlockList) == 0) {
//echo("Support::supportRequest(): INFO: List of blocks is empty.<br />");
            $arrBlockList[] = '__global__';
        }
        foreach ($arrBlockList as $index => $block) {
if (MY_DEBUG) echo("== $block<br />");
            if (is_array($block)) {
                foreach ($block as $index => $blockName) {
                    foreach ($blockName as $index => $block) {
                        $this->dumpTemplate($objTemplate, $blockName[$index]);
                    }
                }
            } else {
                $arrPlaceholder = $this->objTemplate->getPlaceholderList($block);
                foreach ($arrPlaceholder as $index => $placeholderName) {
if (MY_DEBUG) echo("$placeholderName<br />");
                }
            }
        }
//echo("Support::supportRequest(): INFO: End of list.<br />");
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
    function addMessage($strMessage)
    {
        $this->statusMessage .=
            ($this->statusMessage ? '<br />' : '').
            $strMessage;
    }



}


/*
if (MY_DEBUG) echo("
CATEGORY   ".SUPPORT_REQUEST_STATUS_CATEGORY   ."<br />
SUBJECT    ".SUPPORT_REQUEST_STATUS_SUBJECT    ."<br />
MESSAGE    ".SUPPORT_REQUEST_STATUS_MESSAGE    ."<br />
INFOFIELD  ".SUPPORT_REQUEST_STATUS_INFOFIELD  ."<br />
NAME       ".SUPPORT_REQUEST_STATUS_NAME       ."<br />
EMAIL      ".SUPPORT_REQUEST_STATUS_EMAIL      ."<br />
TICKET     ".SUPPORT_REQUEST_STATUS_TICKET     ."<br />
COMPLETE   ".SUPPORT_REQUEST_STATUS_COMPLETE   ."<br />
");
*/

?>
