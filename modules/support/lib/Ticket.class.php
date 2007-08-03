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
  owner_id            int(10)      unsigned NOT NULL default 0,
  `status`            tinyint(2)   unsigned NOT NULL default 1,
  source              tinyint(2)   unsigned NOT NULL default 0,
  email               varchar(255)          NOT NULL,
  `timestamp`         timestamp             NOT NULL default current_timestamp,
  PRIMARY KEY (id),
  KEY support_category_id (support_category_id),
  KEY language_id         (language_id),
  KEY owner_id            (owner_id),
  KEY `status`            (`status`),
  KEY email               (email)
) ENGINE=MyISAM;

*/


/**
 * Ticket State text array
 * @todo    *SHOULD* be static instead of global
 */
$arrTicketStatusString = array(
    SUPPORT_TICKET_STATUS_UNKNOWN => $_ARRAYLANG['TXT_SUPPORT_TICKET_STATUS_UNKNOWN'],
    SUPPORT_TICKET_STATUS_NEW     => $_ARRAYLANG['TXT_SUPPORT_TICKET_STATUS_NEW'],
    SUPPORT_TICKET_STATUS_OPEN    => $_ARRAYLANG['TXT_SUPPORT_TICKET_STATUS_OPEN'],
    SUPPORT_TICKET_STATUS_WAIT    => $_ARRAYLANG['TXT_SUPPORT_TICKET_STATUS_WAIT'],
    SUPPORT_TICKET_STATUS_MOVED   => $_ARRAYLANG['TXT_SUPPORT_TICKET_STATUS_MOVED'],
    SUPPORT_TICKET_STATUS_CLOSED  => $_ARRAYLANG['TXT_SUPPORT_TICKET_STATUS_CLOSED'],
);

/**
 * Ticket Event text array
 * @todo    *SHOULD* be static instead of global
 */
$arrTicketEventString = array(
    SUPPORT_TICKET_EVENT_UNKNOWN         => $_ARRAYLANG['TXT_SUPPORT_TICKET_EVENT_UNKNOWN'],
    SUPPORT_TICKET_EVENT_READ            => $_ARRAYLANG['TXT_SUPPORT_TICKET_EVENT_READ'],
    SUPPORT_TICKET_EVENT_CHANGE_CATEGORY => $_ARRAYLANG['TXT_SUPPORT_TICKET_EVENT_CHANGE_CATEGORY'],
    SUPPORT_TICKET_EVENT_CHANGE_PERSON   => $_ARRAYLANG['TXT_SUPPORT_TICKET_EVENT_CHANGE_PERSON'],
    SUPPORT_TICKET_EVENT_CHANGE_OTHER    => $_ARRAYLANG['TXT_SUPPORT_TICKET_EVENT_CHANGE_OTHER'],
    SUPPORT_TICKET_EVENT_REPLY           => $_ARRAYLANG['TXT_SUPPORT_TICKET_EVENT_REPLY'],
    SUPPORT_TICKET_EVENT_MESSAGE         => $_ARRAYLANG['TXT_SUPPORT_TICKET_EVENT_MESSAGE'],
    SUPPORT_TICKET_EVENT_CLOSE           => $_ARRAYLANG['TXT_SUPPORT_TICKET_EVENT_CLOSE'],
);

/**
 * Ticket Source text array
 * @todo    *SHOULD* be static instead of global
 */
$arrTicketSourceString = array(
    SUPPORT_TICKET_SOURCE_UNKNOWN => $_ARRAYLANG['TXT_SUPPORT_TICKET_SOURCE_UNKNOWN'],
    SUPPORT_TICKET_SOURCE_EMAIL   => $_ARRAYLANG['TXT_SUPPORT_TICKET_SOURCE_EMAIL'],
    SUPPORT_TICKET_SOURCE_WEB     => $_ARRAYLANG['TXT_SUPPORT_TICKET_SOURCE_WEB'],
);


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
 * @todo        PHP5: Make $this->arrStatusString and
 *                         $this->arrActionString static!
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
     * Source
     *
     * Shows where the Ticket came from
     * From table modules_support_ticket
     * @var integer
     */
    var $source;

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
     * The ID of the current owner of this Ticket
     *
     * From table modules_support_ticket
     * @var integer
     */
    var $ownerId;

    /**
     * Timestamp
     *
     * From table modules_support_ticket
     * @var string
     */
    var $timestamp;

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
        $email, $status, $source, $supportCategoryId,
        $languageId, $ownerId, $timestamp='', $id=0
    ) {
        $this->__construct(
            $email, $status, $source, $supportCategoryId,
            $languageId, $ownerId, $timestamp='', $id
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
        $email, $status, $source, $supportCategoryId,
        $languageId, $ownerId, $timestamp='', $id=0
    ) {
        $this->email             = $email;
        $this->status            = $status;
        $this->source            = $source;
        $this->supportCategoryId = $supportCategoryId;
        $this->languageId        = $languageId;
        $this->ownerId           = $ownerId;
        $this->timestamp         = $timestamp;
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
//        $this->statusChanged = true;
    }
     */

    /**
     * Get this Tickets' source
     * @return  integer     The Ticket source
     */
    function getSource()
    {
        return $this->source;
    }
    /**
     * Set this Tickets' source
     * @param   string      The Ticket source
    function setSource($source)
    {
        $this->source = intval($source);
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
     */
    function setSupportCategoryId($supportCategoryId)
    {
        $this->supportCategoryId = intval($supportCategoryId);
//        $this->supportCategoryIdChanged = true;
    }

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
     * Get this Tickets' owner ID
     * @return  integer     The Ticket owner ID
     */
    function getOwnerId()
    {
        return $this->ownerId;
    }
    /**
     * Set this Tickets' owner ID
     * @param   integer     The Ticket owner ID
     */
    function setOwnerId($ownerId)
    {
        $this->ownerId = intval($ownerId);
    }

    /**
     * Get this Tickets' timestamp
     * @return  string      The Ticket timestamp
     */
    function getTimestamp()
    {
        return $this->timestamp;
    }
    /**
     * Set this Tickets' timestamp
     * @param   string      The Ticket timestamp
    function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }
     */


    /**
     * Get this Tickets' status as a string
     * @return  string                              The Ticket status string
     * @global  array       $arrTicketStatusString  Ticket status strings
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getStatusString($status='')
    {
        global $arrTicketStatusString;

        if ($status === '') {
            return $arrTicketStatusString[$this->status];
        } else {
        	return $arrTicketStatusString[$status];
        }
    }


    /**
     * Get this Tickets' source as a string
     * @return  string                              The Ticket source string
     * @global  array       $arrTicketSourceString  Ticket source strings
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getSourceString($source='')
    {
        global $arrTicketSourceString;

        if ($source === '') {
            return $arrTicketSourceString[$this->source];
        } else {
        	return $arrTicketSourceString[$source];
        }
    }


    /**
     * Get the event string for the code
     * @static
     * @param   integer     The event code
     * @return  string      The respective event string
     * @global  array       $arrTicketEventString   Ticket event strings
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getEventString($code)
    {
        global $arrTicketEventString;

        return $arrTicketEventString[$code];
    }


    /**
     * Delete this Ticket from the database.
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
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
     *
     * Note that only the status, source, support_category_id,
     * language_id, and owner_id fields may be changed.
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function update()
    {
        global $objDatabase;

        $query = "
            UPDATE ".DBPREFIX."module_support_ticket
               SET 'status'=$this->status,
                   source=$this->source,
                   support_category_id=$this->supportCategoryId,
                   language_id=$this->languageId
                   owner_id=$this->ownerId
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
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function insert()
    {
        global $objDatabase;

        $query = "
            INSERT INTO ".DBPREFIX."module_support_ticket (
                   email,
                   'status',
                   source,
                   support_category_id,
                   language_id,
                   owner_id
            ) VALUES (
                   $this->email,
                   $this->status,
                   $this->source,
                   $this->supportCategoryId,
                   $this->languageId,
                   $this->ownerId
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $this->id = $objDatabase->Insert_ID();
//echo("Ticket::insert(): done<br />");
        return $this->refreshTimestamp();
    }


    /**
     * Updates the Ticket object with the timestamp value stored in the
     * database.
     *
     * This *MUST* be called by insert() after INSERTing any new record!
     * @return  boolean         True on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function refreshTimestamp()
    {
        global $objDatabase;

        $query = "
            SELECT 'timestamp'
              FROM ".DBPREFIX."module_support_ticket
             WHERE id=$this->id
        ";
echo("Ticket::refreshTimestamp(): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
echo("Ticket::refreshTimestamp(): objResult: '$objResult'<br />");
        if (!$objResult) {
echo("Ticket::refreshTimestamp(): query failed, objResult: '$objResult', count: ".$objResult->RecordCount()."<br />");
            return false;
        }
        if ($objResult->RecordCount() == 0) {
echo("Ticket::refreshTimestamp(): no result: ".$objResult->RecordCount()."<br />");
            return false;
        }
        $this->timestamp = $objResult->fields('timestamp');
        return true;
    }


    /**
     * Select a Ticket by ID from the database.
     * @static
     * @param       integer     $id             The Ticket ID
     * @return      Ticket                      The Ticket object
     *                                          on success, false otherwise
     * @global      mixed       $objDatabase    Database object
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
            $objResult->fields('email'),
            $objResult->fields('status'),
            $objResult->fields('source'),
            $objResult->fields('support_category_id'),
            $objResult->fields('language_id'),
            $objResult->fields('owner_id'),
            $objResult->fields('timestamp'),
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
     * first Ticket to be read from the database, and $limit overrides the
     * global paging size limit setting.
     * @static
     * @param       integer     $supportCategoryId  The Support Category ID,
     *                                              or zero
     * @param       integer     $languageId     The language ID, or zero
     * @param       integer     $ownerId        The owner ID, or zero
     * @param       integer     $status         The Ticket status, or
     *                                          a negative number
     * @param       integer     $source         The Ticket source, or
     *                                          a negative number
     * @param       string      $email          The e-mail address, or the
     *                                          empty string
     * @param       string      $order          The sorting order
     * @param       integer     $offset         The offset
     * @return      array                       The array of Ticket objects
     *                                          on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @global      array       $_CONFIG        Global configuration array
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getTicketArray(
        $supportCategoryId, $languageId, $ownerId, $status, $source, $email,
        $order="'timestamp' DESC", $offset=0, $limit=0
    ) {
        global $objDatabase, $_CONFIG;

        if (!$limit) {
            $limit = $_CONFIG['corePagingLimit'];
        }
        $query = "
            SELECT id
              FROM ".DBPREFIX."module_support_ticket
             WHERE 1
              ".($supportCategoryId ? " AND support_category_id=$supportCategoryId" : '')."
              ".($languageId        ? " AND language_id=$languageId" : '')."
              ".($ownerId           ? " AND owner_id=$ownerId"       : '')."
              ".(!$status < 0       ? " AND `status`=$status"        : '')."
              ".(!$source < 0       ? " AND source=$source"          : '')."
              ".($email             ? " AND email=$email"            : '')."
          ORDER BY $order
        ";
        $objResult = $objDatabase->SelectLimit(
            $query, $limit, $offset
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
     * Returns the number of records for the given criteria
     *
     * The method uses the same mandatory arguments as
     * {@link getTicketArray()}, but returns the number of records
     * found (without limiting the size).
     * @static
     * @param       integer     $supportCategoryId  The Support Category ID,
     *                                              or zero
     * @param       integer     $languageId     The language ID, or zero
     * @param       integer     $ownerId        The owner ID, or zero
     * @param       integer     $status         The Ticket status, or
     *                                          a negative number
     * @param       integer     $source         The Ticket source, or
     *                                          a negative number
     * @param       string      $email          The e-mail address, or the
     *                                          empty string
     * @return      integer                     The number of Ticket records
     *                                          on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getRecordCount(
        $supportCategoryId, $languageId, $ownerId, $status, $source, $email
    ) {
        global $objDatabase, $_CONFIG;

        $query = "
            SELECT COUNT(*) as numof
              FROM ".DBPREFIX."module_support_ticket
             WHERE 1
              ".($supportCategoryId ? " AND support_category_id=$supportCategoryId" : '')."
              ".($languageId        ? " AND language_id=$languageId" : '')."
              ".($ownerId           ? " AND owner_id=$ownerId"       : '')."
              ".(!$status < 0       ? " AND `status`=$status"        : '')."
              ".(!$source < 0       ? " AND source=$source"          : '')."
              ".($email             ? " AND email=$email"            : '');
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        // return count
        if (!$objResult->EOF) {
            return $objResult->fields['numof'];
        }
        return false;
    }


    /**
     * Processes any event for this Ticket.
     *
     * Finds out what action to take, calls appropriate Ticket methods,
     * and updates the Ticket status, if necessary.
     * @param   integer $event      Any valid (or invalid) Ticket event.
     * @param   integer $foreignId  The foreign ID
     * @param   integer $value      The new value
     * @return  boolean             True on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function processEvent($event, $foreignId, $value)
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
        $action = $this->getAction($event, $foreignId, $value);
        // These methods *MUST* return a boolean true upon success,
        // or false otherwise.
        // They must also call the appropriate Ticket method in order
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
     * @author  Reto Kohli <reto.kohli@comvation.com>
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
     * @author  Reto Kohli <reto.kohli@comvation.com>
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
     * @author  Reto Kohli <reto.kohli@comvation.com>
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
     * @author  Reto Kohli <reto.kohli@comvation.com>
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
     * @author  Reto Kohli <reto.kohli@comvation.com>
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


    /**
     * Returns HTML code for the Ticket status dropdown menu.
     *
     * Does only contain the <select> tag pair if the optional $menuName
     * is specified and evaluates to a true value.
     * @param   integer $selectedId The optional preselected status
     * @param   string  $menuName   The optional menu name, defaults to the
     *                              empty string.  Unless specified, no <select>
     *                              tag pair will be added.
     * @return  string              The dropdown menu HTML code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getStatusMenu($selectedId=0, $menuName='')
    {
        $menu = '';
        for ($index = 0; $index < SUPPORT_TICKET_STATUS_COUNT; ++$index) {
            $menu .=
                "<option value='$index'".
                ($selectedId == $index ? ' selected="selected"' : '').
                '>'.Ticket::getStatusString($index)."</option>\n";
        }
        if ($menuName) {
            $menu = "<select id='$menuName' name='$menuName'>\n$menu\n</select>\n";
        }
//echo("getStatusMenu(selected=$selectedId, name=$menuName): made menu: ".htmlentities($menu)."<br />");
        return $menu;

    }


    /**
     * Returns HTML code for the Ticket source dropdown menu.
     *
     * Does only contain the <select> tag pair if the optional $menuName
     * is specified and evaluates to a true value.
     * @param   integer $selectedId The optional preselected source
     * @param   string  $menuName   The optional menu name, defaults to the
     *                              empty string.  Unless specified, no <select>
     *                              tag pair will be added.
     * @return  string              The dropdown menu HTML code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getSourceMenu($selectedId=0, $menuName='')
    {
        $menu = '';
        for ($index = 0; $index < SUPPORT_TICKET_SOURCE_COUNT; ++$index) {
            $menu .=
                "<option value='$index'".
                ($selectedId == $index ? ' selected="selected"' : '').
                '>'.Ticket::getSourceString($index)."</option>\n";
        }
        if ($menuName) {
            $menu = "<select id='$menuName' name='$menuName'>\n$menu\n</select>\n";
        }
//echo("getSourceMenu(selected=$selectedId, name=$menuName): made menu: ".htmlentities($menu)."<br />");
        return $menu;

    }


    /**
     * Returns HTML code for the Ticket owner dropdown menu.
     *
     * Does only contain the <select> tag pair if the optional $menuName
     * is specified and evaluates to a true value.
     * @param   integer $selectedId The optional preselected owner ID
     * @param   string  $menuName   The optional menu name, defaults to the
     *                              empty string.  Unless specified, no <select>
     *                              tag pair will be added.
     * @return  string              The dropdown menu HTML code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getOwnerMenu($selectedId=0, $menuName='')
    {
        // The argument in this function call is fake!
        // It will return all user IDs.
        $arrUserId = Auth::getUserIdArray('support');
        $menu = '';
        foreach ($arrUserId as $userId) {
            $menu .=
                "<option value='$userId'".
                ($selectedId == $userId ? ' selected="selected"' : '').
                '>'.Auth::getFullName($userId)."</option>\n";
        }
        if ($menuName) {
            $menu = "<select id='$menuName' name='$menuName'>\n$menu\n</select>\n";
        }
echo("getOwnerMenu(selected=$selectedId, name=$menuName): made menu: ".htmlentities($menu)."<br />");
        return $menu;

    }


}

?>
