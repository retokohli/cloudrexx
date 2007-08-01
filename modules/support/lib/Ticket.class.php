<?

/**
 * Ticket
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */

/*

Database Tables Structure:

DROP TABLE contrexx_module_support_ticket;
CREATE TABLE contrexx_module_support_ticket (
  id                  int(10)      unsigned NOT NULL auto_increment,
  support_category_id int(10)      unsigned NOT NULL,
  language_id         int(10)      unsigned NOT NULL,
  `status`            tinyint(2)   unsigned NOT NULL default 1,
  email               varchar(255)          NOT NULL,
  `timestamp`         timestamp             NOT NULL default current_timestamp,
  PRIMARY KEY (id),
  KEY support_category_id (support_category_id),
  KEY language_id         (language_id),
  KEY `status`            (`status`),
  KEY email               (email)
) ENGINE=MyISAM;

*/

/**
 * Actions taken on a Ticket
 */
require_once ASCMS_MODULE_PATH.'/support/lib/Action.class.php';
/**
 * Messages related to a Ticket
 */
require_once ASCMS_MODULE_PATH.'/support/lib/Message.class.php';

/**
 * Ticket
 *
 * Every Support Ticket consists of one or more messages
 * The Ticket class is associated with one of the Support Categories.
 * Every Ticket has the following fields:
 *  id
 *  email       Either from the e-mail message, or the web form.
 *  date
 *  status      See {@link seaTicket.inc.php} for details.
 *  support_category_id
 * One or more Messages should be linked to the Ticket.
 * Attachments may be linked to Messages.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */

class Ticket
{
    /**
     * Ticket ID
     *
     * From table modules_support_ticket
     * @var integer
     */
    var $id;

    /**
     * Date
     *
     * From table modules_support_ticket
     * @var string
     */
    var $date;

    /**
     * E-Mail
     *
     * From table modules_support_ticket
     * @var string
     */
    var $email;

    /**
     * Status
     *
     * From table modules_support_ticket
     * @var integer
     */
    var $status;

    /**
     * Support Category associated with this Ticket
     *
     * From table modules_support_ticket
     * @var integer
     */
    var $supportCategoryId;

    /**
     * Language associated with this Ticket
     *
     * From table modules_support_ticket
     * @var integer
     */
    var $languageId;

    /**
     * ID of the person responsible for the Ticket
     *
     * From table modules_support_action
     * @var integer
    var $personId;
     */

    /**
     * Set to true whenever the status of this Ticket changes.
     *
     * Defaults to false.
     * @var boolean
    var $statusChanged = false;
     */

    /**
     * Set to true whenever the Support Category ID of this Ticket changes.
     *
     * Defaults to false.
     * @var boolean
    var $supportCategoryIdChanged = false;
     */


    /**
     * The State-Event-Action Matrix
     *
     * *SHOULD* be static
     * @var     array
     */
    var $arrSea;

    /**
     * The status text array
     *
     * *SHOULD* be static
     * @var     array
     */
    var $arrStatusString;

    /**
     * The event text array
     *
     * *SHOULD* be static
     * @var     array
     */
    var $arrEventString;


    /**
     * Constructor (PHP4)
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     * @see         __construct()
     */
    function Ticket(
        $date, $email, $status, $supportCategoryId, $languageId, $id=0
    ) {
        $this->__construct(
            $date, $email, $status, $supportCategoryId, $languageId, $id
        );
    }

    /**
     * Constructor (PHP5)
     * @global      array   $_ARRAYLANG     Language array
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     * @todo        PHP5: Make $this->arrStatusString and
     *                         $this->arrActionString static!
     */
    function __construct(
        $date, $email, $status, $supportCategoryId, $languageId, $id=0)
    {
        $this->date              = $date;
        $this->email             = $email;
        $this->status            = $status;
        $this->supportCategoryId = $supportCategoryId;
        $this->languageId        = $languageId;
        $this->id                = $id;
/*
        $this->statusChanged = false;
        $this->supportCategoryIdChanged = false;
*/
        /**
         * Ticket State-Event-Action matrix (would-be static)
         */
        require_once ASCMS_MODULE_PATH.'/support/lib/seaTicketMatrix.php';
    }


    /**
     * Get this Tickets' ID
     * @return  integer     The Ticket ID
     */
    function getId()
    {
        return $this->Id;
    }

    /**
     * Get this Tickets' date
     * @return  string      The Ticket date
     */
    function getDate()
    {
        return $this->date;
    }
    /**
     * Set this Tickets' date
     * @param   string      The Ticket date
    function setDate($date)
    {
        $this->date = $date;
    }
     */

    /**
     * Get this Tickets' e-mail address
     * @return  string      The Ticket e-mail address
     */
    function getEmail()
    {
        return $this->email;
    }
    /**
     * Set this Tickets' e-mail address
     * @param   string      The Ticket e-mail address
    function setEmail($email)
    {
        $this->email = strip_tags($email);
    }
     */

    /**
     * Get this Tickets' status
     * @return  integer     The Ticket status
     */
    function getStatus()
    {
        return $this->status;
    }
    /**
     * Set this Tickets' status
     * @param   string      The Ticket status
    function setStatus($status)
    {
        $this->status = intval($status);
        $this->statusChanged = true;
    }
     */

    /**
     * Get this Tickets' SupportCategory ID
     * @return  integer     The Ticket SupportCategory ID
     */
    function getSupportCategoryId()
    {
        return $this->supportCategoryId;
    }
    /**
     * Set this Tickets' SupportCategory ID
     * @param   integer     The Ticket SupportCategory ID
    function setSupportCategoryId($supportCategoryId)
    {
        $this->supportCategoryId = intval($supportCategoryId);
        $this->supportCategoryIdChanged = true;
    }
     */

    /**
     * Get this Tickets' language ID
     * @return  integer     The Ticket language ID
     */
    function getLanguageId()
    {
        return $this->languageId;
    }
    /**
     * Set this Tickets' language ID
     * @param   integer     The Ticket language ID
     */
    function setLanguageId($languageId)
    {
        $this->languageId = intval($languageId);
    }


    /**
     * Get this Tickets' status as a string
     * @return  string      The Ticket status string
     */
    function getStatusString()
    {
        return $this->arrStatusString[$this->status];
    }


    /**
     * Get the event string for the code
     * @param   integer     The event code
     * @return  string      The respective event string
     * @todo    As soon as $this->arrEventString is static, so is this method.
     * @(static)
     */
    //static
    function getEventString($code)
    {
        return $this->arrEventString[$code];
    }


    /**
     * Delete this Ticket from the database.
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function delete()
    {
        global $objDatabase;
//echo("Debug: Ticket::delete(): entered<br />");

        if (!$this->id) {
echo("Ticket::delete(): Error: This Ticket is missing the Ticket ID<br />");
            return false;
        }
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_support_ticket
             WHERE id=$this->id
        ");
        if (!$objResult) {
echo("Ticket::delete(): Error: Failed to delete the Ticket from the database<br />");
            return false;
        }
        return true;
    }


    /**
     * Stores this Ticket in the database.
     *
     * Either updates (id > 0) or inserts (id == 0) the object.
     * @return      boolean     True on success, false otherwise
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function store()
    {
        if ($this->id > 0) {
            return $this->update();
        }
        return $this->insert();
    }


    /**
     * Update this Ticket in the database.
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function update()
    {
        global $objDatabase;

        $query = "
            UPDATE ".DBPREFIX."module_support_ticket
               SET 'date'='".contrexx_addslashes($this->date)."',
                   email=$this->email,
                   'status'=$this->status,
                   support_category_id=$this->supportCategoryId,
                   language_id=$this->languageId,
             WHERE id=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
//echo("Ticket::update(): done<br />");
        return true;
    }


    /**
     * Insert this Ticket into the database.
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function insert()
    {
        global $objDatabase;

        $query = "
            INSERT INTO ".DBPREFIX."module_support_ticket (
                   'date',
                   email,
                   'status',
                   support_category_id,
                   language_id
            ) VALUES (
                   $this->date,
                   $this->email,
                   $this->status,
                   $this->supportCategoryId,
                   $this->languageId
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $this->id = $objDatabase->Insert_ID();
//echo("Ticket::insert(): done<br />");
        return true;
    }


    /**
     * Select a Ticket by ID from the database.
     * @static
     * @param       integer     $id             The Ticket ID
     * @return      Ticket                      The Ticket object
     *                                          on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getById($id)
    {
        global $objDatabase;

        $query = "
            SELECT *
              FROM ".DBPREFIX."module_support_ticket
             WHERE id=$id
        ";
echo("Ticket::getById($id): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
echo("Ticket::getById($id): objResult: '$objResult'<br />");
        if (!$objResult) {
echo("Ticket::getById($id): query failed, objResult: '$objResult', count: ".$objResult->RecordCount()."<br />");
            return false;
        }
        if ($objResult->RecordCount() == 0) {
echo("Ticket::getById($id): no result: ".$objResult->RecordCount()."<br />");
            return false;
        }
        $objTicket = new Ticket(
            $objResult->fields('date'),
            $objResult->fields('email'),
            $objResult->fields('status'),
            $objResult->fields('support_category_id'),
            $objResult->fields('language_id'),
            $objResult->fields('id')
        );
        return $objTicket;
    }


    /**
     * Returns an array of Ticket objects from the database.
     *
     * The array size is limited by the global paging size limit setting.
     * The optional parameter $order determines the sorting order
     * in SQL syntax, it defaults to ordered by date, latest first.
     * The optional parameter $offset determines the offset of the
     * first Ticket to be read from the database.
     * @static
     * @param       string      $order          The sorting order
     * @param       integer     $offset         The offset
     * @return      array                       The array of Ticket objects
     * @global      mixed       $objDatabase    Database object
     * @global      array       $_CONFIG        Global configuration array
     *                                          on success, false otherwise
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getTicketArray($order="'date' DESC", $offset=0)
    {
        global $objDatabase, $_CONFIG;

        $query = "
            SELECT id
              FROM ".DBPREFIX."module_support_ticket
          ORDER BY $order
        ";
        $objResult = $objDatabase->SelectLimit(
            $query, $_CONFIG['corePagingLimit'], $offset
        );
        if (!$objResult) {
            return false;
        }
        // return array
        $arrTicket = array();
        while (!$objResult->EOF) {
            $arrTicket[] = Ticket::getById($objResult->fields['id']);
            $objResult->MoveNext();
        }
        return $arrTicket;
    }


    /**
     * Processes any event for this Ticket.
     *
     * Updates affected objects (and tables) accordingly:
     * Ticket: status
     * @param   integer $event      Any valid (or invalid) Ticket event.
     * @param   integer $personId   The person ID (from the Ticket form)
     * @param   integer $messageId  The message ID (from the Ticket form)
     * @param   integer $supportCategoryId
     *                          The SupportCategory ID (from the Ticket form)
     * @return  boolean             True on success, false otherwise.
     */
    function processEvent($event, $personId, $messageId, $supportCategoryId)
    {
        // Get the would-be status after processing the event
        $newStatus = $this->getNewStatus($event);
        // This *SHOULD* never be visible!
        // -- Which means that every state-event combination causing it
        // has to be avoided.
        if ($newStatus == SUPPORT_TICKET_EVENT_UNKNOWN) {
echo("WARNING!  Event is causing an UNKNOWN status<br />");
        }

        // Get the appropriate action method name for the event
        $action = $this->getAction($event);
        // These methods *MUST* return a boolean true upon success,
        // or false otherwise.
        // They must also call the appropriate Ticket methods in order
        // to create the Action object and entry.
        // When they fail, the status must remain untouched!
        if (!eval('$this->$action;')) {
            return false;
        }
        // Only store if it's necessary
        if ($this->status != $newStatus) {
            // Update $this->status and store it.
            $this->status = $newStatus;
            return $this->store();
        }
        // Same status
        return true;
    }


    /**
     * Returns the appropriate Action for the current status
     * and the event code.
     *
     * The event code must be one of the codes define()d in the
     * constants in {@link lib/SupportCommon.class.php} and used in
     * {@link seaTicketMatrix.inc.php} to initialize the matrix.
     * The returned string must correspond to a Ticket method with
     * the appropriate variable names as arguments.
     * @param   integer $event  The event code
     * @return  string          The name of the action to take.
     */
    function getAction($event)
    {
        return $this->arrSea[$this->status][$event]['action'];
    }


    /**
     * Returns the prospective status the Ticket will be set to
     * after taking successful action for the current status and
     * the event code.
     *
     * The event code must be one of the codes define()d in the
     * constants in {@link lib/SupportCommon.class.php} and used in
     * {@link seaTicketMatrix.inc.php} to initialize the matrix.
     * The returned integer must correspond to a status as define()d
     * in the constants in {@link lib/SupportCommon.class.php} as well.
     * @param   integer $event  The event code
     * @return  string          The prospective status code.
     */
    function getNewStatus($event)
    {
        return $this->arrSea[$this->status][$event]['status'];
    }


    /**
     * Take no Action on this Ticket.
     *
     * This is all about not changing the Ticket status.
     * @return unknown
     */
    function actionNone()
    {
        return true;
    }


    /**
     * Try to assign this Ticket to the person reading it.
     *
     * This method is to be called for Tickets with status NEW or MOVED,
     * whenever a READ event occurs.
     * The Ticket is assigned to the person reading it only if
     * - The Ticket hasn't been assigned before (its status is NEW), or if
     * - The Ticket has been assigned (moved) to the person reading it now,
     *   that is, the person currently logged in. See
     *   {@link core/auth.class.php}.
     * @return  boolean     True on success, false otherwise.
     */
    function actionAssignToReader()
    {
        $ownerId  = $this->getOwnerId();
        $personId = intval($_SESSION['auth']['userid']);
        if ($personId == 0) {
echo("ERROR: No User ID found in Session!<br />");
            return false;
        }
        if ($ownerId != 0) {
            if ($ownerId == $personId) {
                return true;
            }
echo("NOTE: Ticket is assigned to someone else!<br />");
            return false;
        }
        // Creates an Action entry
        return $this->setOwnerId($personId);
    }


    /**
     * Try to assign this Ticket to the person with the given ID.
     *
     * This method is to be called for Tickets with status OPEN or MOVED,
     * whenever a CHANGE_PERSON event occurs.
     * @return  boolean     True on success, false otherwise.
     */
    function actionAssign($personId)
    {
        if ($personId == 0) {
echo("actionAssign($personId): ERROR: No User ID specified!<br />");
            return false;
        }
        $ownerId = $this->getOwnerId();
        if ($ownerId == 0) {
echo("actionAssign($personId): WARNING: Something's wrong -- this Ticket isn't owned!<br />");
            return false;
        }
        if ($ownerId == $personId) {
            return true;
        }
        // Creates an Action entry
        return $this->setOwnerId($personId);
    }

}

?>
