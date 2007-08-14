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
--  owner_id            int(10)      unsigned NOT NULL default 0,
--  `status`            tinyint(2)   unsigned NOT NULL default 1,
  source              tinyint(2)   unsigned NOT NULL default 0,
  email               varchar(255)          NOT NULL,
  `timestamp`         timestamp             NOT NULL default current_timestamp,
  PRIMARY KEY (id),
  KEY support_category_id (support_category_id),
  KEY language_id         (language_id),
  KEY owner_id            (owner_id),
--  KEY `status`            (`status`),
  KEY email               (email)
) ENGINE=MyISAM;

*/


global $_ARRAYLANG;

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
    SUPPORT_TICKET_EVENT_MESSAGE_VIEW    => $_ARRAYLANG['TXT_SUPPORT_TICKET_EVENT_MESSAGE_VIEW'],
    SUPPORT_TICKET_EVENT_CHANGE_CATEGORY => $_ARRAYLANG['TXT_SUPPORT_TICKET_EVENT_CHANGE_CATEGORY'],
    SUPPORT_TICKET_EVENT_CHANGE_OWNER    => $_ARRAYLANG['TXT_SUPPORT_TICKET_EVENT_CHANGE_OWNER'],
    SUPPORT_TICKET_EVENT_CHANGE_OTHER    => $_ARRAYLANG['TXT_SUPPORT_TICKET_EVENT_CHANGE_OTHER'],
    SUPPORT_TICKET_EVENT_REPLY           => $_ARRAYLANG['TXT_SUPPORT_TICKET_EVENT_REPLY'],
    SUPPORT_TICKET_EVENT_MESSAGE_NEW     => $_ARRAYLANG['TXT_SUPPORT_TICKET_EVENT_MESSAGE_NEW'],
    SUPPORT_TICKET_EVENT_REFERENCE       => $_ARRAYLANG['TXT_SUPPORT_TICKET_EVENT_REFERENCE'],
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
    SUPPORT_TICKET_SOURCE_SYSTEM  => $_ARRAYLANG['TXT_SUPPORT_TICKET_SOURCE_SYSTEM'],
);


/**
 * Handles events on a Ticket
 */
require_once ASCMS_MODULE_PATH.'/support/lib/TicketEvent.class.php';
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
 * // status      See {@link seaTicket.inc.php} for details.
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
    var $ownerId;
     */

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
     * Status
     *
     * This is a run-time calculated value not stored with the
     * Ticket record.
     * From table modules_support_ticket_event, see
     * {@link TicketEvent::getTicketStatus()}
     * @var integer
     */
    var $status;

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
     * Constructor (PHP4)
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     * @see         __construct()
     */
    function Ticket(
        $email, $source, $supportCategoryId,
        $languageId, $timestamp='', $id=0
    ) {
        $this->__construct(
            $email, $source, $supportCategoryId,
            $languageId, $timestamp, $id
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
        $email, $source, $supportCategoryId,
        $languageId, $timestamp='', $id=0
    ) {
        $this->email             = $email;
//        $this->status            = $status;
        $this->source            = $source;
        $this->supportCategoryId = $supportCategoryId;
        $this->languageId        = $languageId;
//        $this->ownerId           = $ownerId;
        $this->timestamp         = $timestamp;
        $this->id                = $id;
/*
        $this->statusChanged = false;
        $this->supportCategoryIdChanged = false;
*/
    }


    /**
     * Get this Tickets' ID
     * @return  integer     The Ticket ID
     */
    function getId()
    {
        return $this->id;
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
        if ($this->status === false) {
            $this->status =
                TicketEvent::getTicketStatus(
                    $this->ticketId,
                    $this->id
                );
        }
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
     * Set this Tickets' SupportCategory ID -- OBSOLETE
     * @param   integer     The Ticket SupportCategory ID
    function setSupportCategoryId($supportCategoryId)
    {
        $this->supportCategoryId = intval($supportCategoryId);
//        $this->supportCategoryIdChanged = true;
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
     * Set this Tickets' language ID -- OBSOLETE
     * @param   integer     The Ticket language ID
    function setLanguageId($languageId)
    {
        $this->languageId = intval($languageId);
    }
     */

    /**
     * Get this Tickets' owner ID
     * @return  integer     The Ticket owner ID
    function getOwnerId()
    {
        return $this->ownerId;
    }
     */
    /**
     * Set this Tickets' owner ID -- OBSOLETE
     * @param   integer     The Ticket owner ID
    function setOwnerId($ownerId)
    {
        $this->ownerId = intval($ownerId);
    }
     */

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
     *
     * This method may be called as a static method, if the optional
     * $status parameter is set.
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
     * Get this Tickets' source as a string.
     *
     * This method may be called as a static method, if the optional
     * $source parameter is set.
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
echo("Ticket::delete(): ERROR: Failed to delete the Ticket with ID $this->id from the database!<br />");
            return false;
        }
        // delete associated records in Messages and TicketEvents tables
        if (!Message::deleteByTicketId($this->id)) {
echo("Ticket::delete(): Error: Failed to delete Messages associated with Ticket ID $this->id from the database!<br />");
            return false;
        }
        if (!TicketEvent::deleteByTicketId($this->id)) {
echo("Ticket::delete(): Error: Failed to delete TicketEvents associated with Ticket ID $this->id from the database!<br />");
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
     * Note that only the support_category_id field may be changed.
     * (not source, language_id!).
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function update()
    {
        global $objDatabase;

        $query = "
            UPDATE ".DBPREFIX."module_support_ticket
               SET support_category_id=$this->supportCategoryId
             WHERE id=$this->id
        ";
/*
                   `status`=$this->status,
                   owner_id=$this->ownerId
                   source=$this->source,
                   language_id=$this->languageId,
*/
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
//echo("Ticket::update(): done<br />");
        return true;
    }


    /**
     * Insert this new Ticket into the database.
     *
     * Note that some fields have their default values defined
     * by the table definition and are not set explicitly here.
     * These include: timestamp (current time).
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
                   source,
                   support_category_id,
                   language_id
            ) VALUES (
                   '$this->email',
                   $this->source,
                   $this->supportCategoryId,
                   $this->languageId
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $this->id = $objDatabase->Insert_ID();

        // Do not create a TicketEvent for this new Ticket, but for
        // the Message that caused it!  See addMessage().

//echo("Ticket::insert(): done<br />");
        return $this->refresh();
    }


    /**
     * Updates the Ticket object with the timestamp values
     * stored in the database.
     *
     * This *MUST* be called by insert() after INSERTing any new record!
     * @return  boolean         True on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function refreshTimestamp()
    {
        global $objDatabase;

        $query = "
            SELECT `timestamp`
              FROM ".DBPREFIX."module_support_ticket
             WHERE id=$this->id
        ";
echo("Ticket::refresh(): ID: $this->id, query: $query<br />");
// TODO: Here, ADODB shoots its foot:
        $objResult = $objDatabase->Execute($query);
echo("Ticket::refresh(): objResult: '$objResult'<br />");
        if (!$objResult) {
echo("Ticket::refresh(): query failed, objResult: '$objResult', count: ".$objResult->RecordCount()."<br />");
            return false;
        }
        if ($objResult->RecordCount() == 0) {
echo("Ticket::refresh(): no result: ".$objResult->RecordCount()."<br />");
            return false;
        }
//        $this->status    = $objResult->fields('status');
        $this->timestamp = $objResult->fields('timestamp');
echo("Ticket::refresh(): done!<br />");
        return true;
    }


    /**
     * Update the Support Category of this Ticket
     *
     * Only updates the Ticket record if the Support Category ID differs
     * from the current value in the object.
     * Returns true if the update was successful, or if no changes
     * were made.  Returns false on errors only.
     * @param   integer $supportCategoryId  The new Support Category ID
     * @return  boolean                     True on success, false otherwise
     */
    function updateSupportCategoryId($supportCategoryId)
    {
        if ($this->supportCategoryId != $supportCategoryId) {
            // Create the appropriate TicketEvent
            $objEvent = new TicketEvent(
                $this,
                SUPPORT_TICKET_EVENT_CHANGE_CATEGORY,
                $supportCategoryId
            );
            // Process the TicketEvent, returns the new Ticket status
            $newStatus = $objEvent->process();
            // If the status would be UNKNOWN, abort the whole process
            if ($newStatus == SUPPORT_TICKET_STATUS_UNKNOWN) {
                return false;
            }
            $this->supportCategoryId = $supportCategoryId;
//            $this->status = $newStatus;
            // Update the Ticket
            if (!$this->update()) {
                // Roll back if the update failed
                return $objEvent->delete();
            }
        }
        // Nothing has been changed.  All is well.
        return true;
    }


    /**
     * Update the owner ID of this Ticket
     *
     * Only updates the Ticket record if the owner ID differs
     * from the current value in the object.
     * Returns true if the update was successful, or if no changes
     * were made.  Returns false on errors only.
     * @param   integer $ownerId            The new owner ID
     * @return  boolean                     True on success, false otherwise
     */
    function updateOwnerId($ownerId)
    {
        if ($this->ownerId != $ownerId) {
            // Create the appropriate TicketEvent
            $objEvent = new TicketEvent(
                $this,
                SUPPORT_TICKET_EVENT_CHANGE_OWNER,
                $ownerId
            );
            // Process the TicketEvent, returns the new Ticket status
            $newStatus = $objEvent->process();
            // If the status would be UNKNOWN, abort the whole process
            if ($newStatus == SUPPORT_TICKET_STATUS_UNKNOWN) {
                return false;
            }
/*
            $this->ownerId = $ownerId;
            $this->status = $newStatus;
            // Update the Ticket
            if (!$this->update()) {
                // Roll back if the update failed
                return $objEvent->delete();
            }
            return false;
*/
        }
        // Nothing has been changed.  All is well.
        return true;



    }


    /**
     * Change a Ticket property not covered by either updateSupportCategoryId()
     * or updateOwnerId().
     *
     * Note: This is currently unimplemented, and thus always fails.
     * @return  boolean                     Always false.
     */
    function updateOther() {
        return false;
    }


    /**
     * Adds a new Message to this Ticket.
     *
     * This method creates a TicketEvent in order to
     * create a MESSAGE_NEW entry and to update the Ticket status.
     * If the Ticket has already been closed, this creates a new Ticket
     * and a REFERENCE to the old one.
     * The optional $supportCategoryId, $supportTicketLanguageId,
     * and $supportTicketSource arguments are only considered in case
     * a new Ticket is created.  If they are missing, the values from
     * the old Ticket are copied.  Otherwise, they are ignored, and the
     * respective values of the existing Ticket are left untouched.
     * The optional $supportMessageDate argument will be set to the current
     * date and time if empty.
     * @param   string  $supportMessageFrom     The Messages' e-mail field
     * @param   string  $supportMessageSubject  The Message subject
     * @param   string  $supportMessageBody     The Message text
     * @return  boolean                         True on success,
     *                                          false otherwise.
     */
    function addMessage(
        $supportMessageFrom, $supportMessageSubject, $supportMessageBody,
        $supportCategoryId=0, $supportTicketLanguageId=0,
        $supportTicketSource=0, $supportMessageDate=0
    ) {
echo("Ticket::addMessage(
        supportMessageFrom=$supportMessageFrom,
        supportMessageSubject=$supportMessageSubject,
        supportMessageBody=$supportMessageBody,
        supportCategoryId=$supportCategoryId,
        supportTicketLanguageId=$supportTicketLanguageId,
        supportTicketSource=$supportTicketSource,
        supportMessageDate=$supportMessageDate,
): INFO: entered<br />");
        if ($supportMessageDate == 0) {
            $supportMessageDate = date('Y-m-d H:i:s');
        }
        $objTicket = $this;
        $ticketStatus = TicketEvent::getTicketStatus($this->getId());
        if ($ticketStatus == SUPPORT_TICKET_STATUS_CLOSED) {
            // The Ticket has already been closed.
            // Copy old Ticket values, if no new ones are available.
            if ($supportCategoryId == 0) {
                $supportCategoryId = $this->supportCategoryId;
            }
            if ($supportTicketLanguageId == 0) {
                $supportTicketLanguageId = $this->languageId;
            }
            if ($supportTicketSource == 0) {
                $supportTicketSource = $this->source;
            }
            // Create the new Ticket.
            $objTicket = new Ticket(
                $supportMessageFrom,
                0,
                $supportTicketSource,
                $supportCategoryId,
                $supportTicketLanguageId,
                0
            );
            if (!$objTicket->insert()) {
                return false;
            }
        }
        // Create the new Message object
        $objMessage = new Message(
            $objTicket->getId(),
            $supportMessageFrom,
            $supportMessageSubject,
            $supportMessageBody,
            $supportMessageDate
        );
        // The Message *MUST* be insert()ed prior to creating the TicketEvent
        // (Otherwise, we wouldn't have a valid Message ID).
        if (!$objMessage->insert()) {
echo("Ticket::addMessage(): ERROR: Failed to insert() the new Message, ticketId ".$objTicket->getId()."<br />");
            return false;
        }
        // Create the TicketEvent
        $objEvent = new TicketEvent(
            $objTicket,
            SUPPORT_TICKET_EVENT_MESSAGE_NEW,
            $objMessage->getId()
        );
        if (!$objEvent) {
echo("Ticket::addMessage(): ERROR: Failed to create MESSAGE TicketEvent, ticketId ".$objTicket->getId().", messageId ".$objMessage->getId()."<br />");
            return false;
        }
        // Process the MESSAGE TicketEvent.  Returns the new status.
        $newStatus = $objEvent->process();
        if ($newStatus == SUPPORT_TICKET_STATUS_UNKNOWN) {
echo("Ticket::addMessage(): ERROR: Adding Message results in UNKNOWN state - rolling back!  ticketId ".$objTicket->getId().", messageId ".$objMessage->getId()."<br />");
            // On failure, try to roll back
            return $objMessage->delete();
        }
        // If a new Ticket was created above, add a REFERENCE to the old one.
        if ($this != $objTicket) {
            $objEvent = new TicketEvent(
                $objTicket,                     // New Ticket object
                SUPPORT_TICKET_EVENT_REFERENCE,
                $this->id                       // Old Ticket ID
            );
            if (!$objEvent) {
echo("Ticket::addMessage(): ERROR: Failed to create REFERENCE TicketEvent, ticketId ".$objTicket->getId().", reference ticketId $this->id<br />");
                return false;
            }
            // Process the REFERENCE TicketEvent.
            // Note that this will not change either Tickets' status.
            $newStatus = $objEvent->process();
            if ($newStatus == SUPPORT_TICKET_STATUS_UNKNOWN) {
echo("Ticket::addMessage(): ERROR: Adding REFERENCE TicketEvent results in UNKNOWN state!  ticketId ".$objTicket->getId().", ref. Ticket ID $this->id<br />");
                // Nothing to roll back upon failure
                return false;
            }
        }
        $objTicket->status = $newStatus;
        return $objTicket->update();
    }


    /**
     * Mark the Message as deleted
     *
     * Only works for Messages associated with this Ticket.
     * This will not physically erase the Message from the
     * Database, but a TicketEvent record will be added indicating that
     * the current User doesn't consider it necessary to further process
     * the Ticket.
     * Note that this decision applies to the current owner, as well as all
     * future owners of the Ticket.  The Message can be restored, however.
     * @param   integer     $messageId      The Message ID
     * @return  boolean                     True on success, false otherwise
     */
    function deleteMessage($messageId)
    {
        $objEvent = new TicketEvent(
            $this,
            SUPPORT_TICKET_EVENT_MESSAGE_DELETE,
            $messageId
        );
        $newStatus = $objEvent->process();
        if ($newStatus == SUPPORT_TICKET_STATUS_UNKNOWN) {
            // Nothing to roll back upon failure
            return false;
        }
/*
        $this->status = $newStatus;
        return $this->update();
*/
        return true;
    }


    /**
     * Mark the Message as viewed, if applicable
     *
     * Only works for Messages associated with this Ticket.
     * A TicketEvent record will be added indicating that the User
     * has seen it, if and only if he is the current owner of the Ticket.
     * @param   integer     $messageId      The Message ID
     * @return  boolean                     True on success, false otherwise
     */
    function updateView($messageId)
    {
        $objEvent = new TicketEvent(
            $this,
            SUPPORT_TICKET_EVENT_MESSAGE_VIEW,
            $messageId
        );
        $newStatus = $objEvent->process();
        if ($newStatus == SUPPORT_TICKET_STATUS_UNKNOWN) {
            // Nothing to roll back upon failure
            return false;
        }
/*
        // All Messages have been seen by the current User,
        // update the Ticket status to OPEN
        $this->status = $newStatus;
        return $this->update();
*/
        return true;
    }


    /**
     * Mark the Ticket as replied.
     *
     * Only works for Messages associated with this Ticket.
     * An appropriate TicketEvent record will be added.
     * @param   integer     $messageId      The Message ID
     * @return  boolean                     True on success, false otherwise
     */
    function reply($messageId) {
        $objEvent = new TicketEvent(
            $this,
            SUPPORT_TICKET_EVENT_REPLY,
            $messageId
        );
        $newStatus = $objEvent->process();
        if ($newStatus == SUPPORT_TICKET_STATUS_UNKNOWN) {
            // Nothing to roll back upon failure
            return false;
        }
/*
        $this->status = $newStatus;
        return $this->update();
*/
        return true;
    }


    /**
     * Add a reference from this Ticket to another.
     *
     * An appropriate TicketEvent record will be added.
     * @param   integer     $ticketId       The referenced Ticket ID
     * @return  boolean                     True on success, false otherwise
    function reference($ticketId) {
        $objEvent = new TicketEvent(
            $this,
            SUPPORT_TICKET_EVENT_REFERENCE,
            $ticketId
        );
        $newStatus = $objEvent->process();
        if ($newStatus == SUPPORT_TICKET_STATUS_UNKNOWN) {
            // Nothing to roll back upon failure
            return false;
        }
        return true;
    }
     */


    /**
     * Close this Ticket.
     *
     * An appropriate TicketEvent record will be added.
     * @param   integer     $ticketId       The referenced Ticket ID
     * @return  boolean                     True on success, false otherwise
     */
    function close() {
        $objEvent = new TicketEvent(
            $this,
            SUPPORT_TICKET_EVENT_CLOSE,
            0
        );
        $newStatus = $objEvent->process();
        if ($newStatus == SUPPORT_TICKET_STATUS_UNKNOWN) {
            // Nothing to roll back upon failure
            return false;
        }
/*
        $this->status = $newStatus;
        return $this->update();
*/
        return true;
    }


    /**
     * Select a Ticket by ID from the database.
     * @static
     * @param       integer     $id             The Ticket ID
     * @return      Ticket                      The Ticket object on success,
     *                                          false otherwise
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
//echo("Ticket::getById($id): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
//echo("Ticket::getById($id): objResult: '$objResult'<br />");
        if (!$objResult) {
echo("Ticket::getById($id): query failed, objResult: '$objResult', count: ".$objResult->RecordCount()."<br />query: $query<br />");
            return false;
        }
        if ($objResult->RecordCount() == 0) {
echo("Ticket::getById($id): no result: ".$objResult->RecordCount()."<br />");
            return false;
        }
        $objTicket = new Ticket(
            $objResult->fields('email'),
            $objResult->fields('source'),
            $objResult->fields('support_category_id'),
            $objResult->fields('language_id'),
//            $objResult->fields('owner_id'),
            $objResult->fields('timestamp'),
            $objResult->fields('id')
        );
        return $objTicket;
    }


    /**
     * Returns an array of Ticket IDs from the database.
     *
     * This is the same as {@link getTicketArray()}, except that it
     * returns an array of IDs rather than complete objects.
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
     * @return      array                       The array of Ticket IDs
     *                                          on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @global      array       $_CONFIG        Global configuration array
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getTicketIdArray(
        $supportCategoryId, $languageId, $ownerId, $status, $source, $email,
        $order="`timestamp` DESC", $offset=0, $limit=0
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
        $arrTicketId = array();
        while (!$objResult->EOF) {
            // Compare status if desired -- This is a bit costly!
            if (
                    ($status >= SUPPORT_TICKET_STATUS_UNKNOWN
                        ?   TicketEvent::getTicketStatus($this->id) == $status
                        :   true
                    )
                &&
                    ($ownerId >= 0
                        ?   TicketEvent::getTicketOwnerId($this->id) == $ownerId
                        :   true
                    )
            ) {
                $arrTicketId[] = $objResult->fields['id'];
            }
            $objResult->MoveNext();
        }
        return $arrTicketId;
    }


    /**
     * Returns an array of Ticket objects from the database.
     *
     * This is the same as {@link getTicketIdArray()}, except that it
     * returns an array of complete objects rather than just IDs.
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
        $order="`timestamp` DESC", $offset=0, $limit=0
    ) {
        global $objDatabase, $_CONFIG;

        if (!$limit) {
            $limit = $_CONFIG['corePagingLimit'];
        }

        // Get Ticket ID array
        $arrTicketId = $this->getTicketIdArray(
            $supportCategoryId, $languageId, $ownerId, $status, $source, $email,
            $order, $offset, $limit
        );
        // Was it successful?
        if ($arrTicketId === false) {
            return false;
        }

        // return array
        $arrTicket = array();
        foreach ($arrTicketId as $ticketId) {
            $arrTicket[] = Ticket::getById($ticketId);
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
     */


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
     * The $onchange argument will only be inserted if the <select> tag
     * is added, according to the above rule.
     * Note that Users with neither first nor last names present in the
     * database table will silently be ignored!
     * @static
     * @param   integer $selectedId The optional preselected owner ID
     * @param   string  $menuName   The optional menu name, defaults to the
     *                              empty string.  Unless specified, no <select>
     *                              tag pair will be added.
     * @param   string  $onchange   The optional onchange code.
     * @return  mixed               The dropdown menu HTML code on success,
     *                              false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    // static
    function getOwnerMenu($selectedId=0, $menuName='', $onchange='')
    {
        // The argument in this function call is fake!
        // It will return all user IDs.
        $arrUserId = Auth::getUserIdArray('support');
        if (!$arrUserId) {
echo("getOwnerMenu(selected=$selectedId, name=$menuName, onchange=$onchange): ERROR: got no user IDs!<br />");
            return false;
        }
//echo("getOwnerMenu(selected=$selectedId, name=$menuName, onchange=$onchange): got user IDs: ");var_export($arrUserId);echo("<br />");
        $menu = '';
        foreach ($arrUserId as $userId) {
            $fullName = trim(Auth::getFullName($userId));
            if ($fullName == '') {
                continue;
            }
            $menu .=
                "<option value='$userId'".
                ($selectedId == $userId ? ' selected="selected"' : '').
                '>'.$fullName."</option>\n";
        }
        if ($menuName) {
            $menu = "<select id='$menuName' name='$menuName'".
            ($onchange ? ' onchange="'.$onchange.'"' : '').
            ">\n$menu\n</select>\n";
        }
//echo("getOwnerMenu(selected=$selectedId, name=$menuName, onchange=$onchange): made menu: ".htmlentities($menu)."<br />");
        return $menu;

    }


}

?>
