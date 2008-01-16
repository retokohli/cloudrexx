<?php

/* TODO: move somewhere else
 ($this->ticketHasNewMessages() > 1) {
if (MY_DEBUG) echo("TicketEvent::actionMessageView(): INFO: Ticket HAS new Messages.<br />");
            return false;
        }
if (MY_DEBUG) echo("TicketEvent::actionMessageView(): INFO: Ticket has NO new Messages.<br />");
*/

/**
 * Ticket Event
 *
 * This class provides a State-Event Engine which keeps track of any
 * changes, and also controls the owner and status of Tickets.
 * All changes are logged to the event database table, which allows
 * the recreation of the whole history of each Ticket.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */

/*

Database Table Structure:

DROP TABLE contrexx_module_support_ticket_event;
CREATE TABLE contrexx_module_support_ticket_event (
  id              int(10)     unsigned NOT NULL auto_increment,
  ticket_id       int(10)     unsigned     NULL default NULL,
  `event`         tinyint(2)  unsigned     NULL default NULL,
  `value`         int(10)     unsigned     NULL default NULL,
  user_id         int(10)     unsigned     NULL default NULL,
  `status`        tinyint(2)  unsigned     NULL default NULL,
  `timestamp`     timestamp            NOT NULL default current_timestamp,
  PRIMARY KEY     (id),
  KEY ticket_id   (ticket_id),
  KEY `event`     (`event`)
) ENGINE=MyISAM;

*/

/**
 * Ticket Event
 *
 * This class provides a State-Event Engine which keeps track of any
 * changes, and also controls the status of Tickets.
 * All changes are logged to the event database table, which enables
 * the recreation of the whole history of each Ticket.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */
class TicketEvent
{
    /**
     * The TicketEvent ID
     * @var integer
     */
    var $id;

    /**
     * The Ticket object affected by the Event
     * @var Ticket
     */
    var $objTicket;

    /**
     * The Event code
     * @var integer
     */
    var $event;

    /**
     * The target value
     * @var integer
     */
    var $value;

    /**
     * The ID of the User which caused the TicketEvent.
     *
     * Note that this is not necessarily identical to the owner ID,
     * which may be found in the value field of MOVE events instead.
     * @var integer
     */
    var $userId;

    /**
     * The status of the Ticket before processing the Event.
     *
     * This is taken from the previous TicketEvent record for this Ticket,
     * and is used to determine the (new) status for a TicketEvent.
     * @var integer
     */
    var $oldStatus;

    /**
     * The status of the Ticket after successfully processing the Event.
     *
     * This is the status that will be stored in the TicketEvent record,
     * if it is processed successfully.
     * @var integer
     */
    var $newStatus;

    /**
     * The timestamp of the event being processed
     *
     * Note that according to the definition of the timestamp field in the
     * database table, this value *MUST* not be updated (or changed).
     * It is set to the current date and time only once, when the record
     * is INSERTed into the table.
     * From table modules_support_action
     * @var string
     */
    var $timestamp;

    /**
     * The ID of the current Owner of the referenced Ticket.
     *
     * Note that this is not necessarily identical to the User ID
     * stored in the user_id field.  Instead, this is found in the
     * value field of the most recent CHANGE_OWNER TicketEvent record
     * associated with the Ticket.
     * This ID is used in many methods throughout the class, so it is
     * set in the constructor right away for convenience.
     * @var integer
     */
    var $ownerId;


    /**
     * The State-Event-Action Matrix
     *
     * The state-event diagram for the Ticket:
     * (The default Status of any new Tickets is, of course, NEW.)
     *
     * \EVENT  |UNKN.| VIEW |CATEG| PERS. |OTHER|REPLY|M.NEW|M.DEL|REFER|CLOSE|
     * STATUS\ |     |      |     |       |     |     |     |     |     |     |
     * UNKNOWN | -/U | V/O  | -/U | -/U   | -/U | -/U | N/U | -/U | L/U | -/U |
     * NEW     | -/U | V/O* | -/U | T/N   | -/U | -/U | N/N | -/U | L/N | -/U |
     * OPEN    | -/U | V/O  | S/O | T/M***| X/O | R/W | N/N | E/O | L/O | C/C |
     * WAIT    | -/U | V/W  | -/U | -/U   | -/U | R/W | N/N | -/U | L/W | C/C |
     * MOVED   | -/U | V/O**| -/U | T/M***| -/U | -/U | N/N | -/U | L/M | -/U |
     * CLOSED  | -/U | V/C  | -/U | -/U   | -/U | -/U | N/C | -/U | L/C | -/C |
     *           \____________________ ACTION/NEW_STATUS ___________________/
     *
     * Actions are:
     * -    No action is performed, and no Event record created.
     * C    Close a Ticket.  Update everything related.
     * E    Erase: Mark one of the Tickets' Messages as deleted.
     *      Applies to the current owner of the Ticket only.
     * L    Link: Create a reference from a new Ticket to a closed one.
     *      This event is only created by a new Message referring to
     *      an already closed Ticket.  Neither Ticket status will be
     *      affected by this, see event N, new Ticket.
     * N    New Message: Add the message to the Ticket.
     *      If the Ticket has already been CLOSED, a new Ticket and a
     *      reference (see event L) to the closed one will be created.
     *      The status will be set to NEW, except when it was UNKNOWN before.
     *      In that case, it won't be changed. (messageId)
     * R    Reply to the Ticket.  Store and send the reply, update the KB and all
     *      affected tables. (messageId)
     * S    Modify the Ticket Support Category.  This is only possible for the
     *      current owner of the Ticket. (supportCategoryId)
     * T    Transfer a Ticket.  Anyone can take over Tickets in MOVED state,
     *      which they will keep until viewed by the new owner.
     *      This is intended to enable others to process your Tickets when
     *      you are unable to.
     *      Alternatively assigns ownership of the Ticket to the
     *      person chosen (personId).  This is only possible for the owner
     *      of a Ticket in OPEN state.
     * V    View a Message.  Adds a VIEW TicketEvent record referencing
     *      the Message.
     *      Assigns ownership of the Ticket to the reader if the
     *      status is NEW (no current owner).
     * X    Modify any property of the Ticket other than the Support Category,
     *      or the owner.  [TODO: Implement, explain which!]
     *
     * Notes:
     * *  : New Tickets are not assigned to any person.  Anybody reading
     *      one of the associated Messages first will take ownership of it.
     * ** : The ownership will be accepted, and the Ticket status set to OPEN
     *      only if read by the person it has been assigned to.
     * ***: OPEN and MOVED Tickets can always be taken over by anyone willing
     *      to process them further.  The status will change to MOVED.
     *      Once they own it, the status will be set to OPEN upon viewing.
     *      Tickets can only be delegated to another person by the owner.
     *
     * Note that this variable *SHOULD* of course be static.
     *
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @var     array
     */
    var $arrSea = array(
        SUPPORT_TICKET_STATUS_UNKNOWN => array(
            SUPPORT_TICKET_EVENT_UNKNOWN         => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_MESSAGE_VIEW    => array(
                'action' => '$this->actionMessageView();',
                'status' => SUPPORT_TICKET_STATUS_OPEN),
            SUPPORT_TICKET_EVENT_CHANGE_CATEGORY => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_CHANGE_OWNER    => array(
                'action' => '$this->actionTransfer();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_CHANGE_OTHER    => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_REPLY           => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_MESSAGE_NEW     => array(
                'action' => '$this->actionMessageNew();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_MESSAGE_DELETE  => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_REFERENCE       => array(
                'action' => '$this->actionReference();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_CLOSE           => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
        ),
        SUPPORT_TICKET_STATUS_NEW     => array(
            SUPPORT_TICKET_EVENT_UNKNOWN         => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_MESSAGE_VIEW    => array(
                'action' => '$this->actionMessageView();',
                'status' => SUPPORT_TICKET_STATUS_OPEN),
            SUPPORT_TICKET_EVENT_CHANGE_CATEGORY => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_CHANGE_OWNER    => array(
                'action' => '$this->actionTransfer();',
                'status' => SUPPORT_TICKET_STATUS_MOVED),
            SUPPORT_TICKET_EVENT_CHANGE_OTHER    => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_REPLY           => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_MESSAGE_NEW     => array(
                'action' => '$this->actionMessageNew();',
                'status' => SUPPORT_TICKET_STATUS_NEW),
            SUPPORT_TICKET_EVENT_MESSAGE_DELETE  => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_REFERENCE       => array(
                'action' => '$this->actionReference();',
                'status' => SUPPORT_TICKET_STATUS_NEW),
            SUPPORT_TICKET_EVENT_CLOSE           => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
        ),
        SUPPORT_TICKET_STATUS_OPEN    => array(
            SUPPORT_TICKET_EVENT_UNKNOWN         => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_MESSAGE_VIEW    => array(
                'action' => '$this->actionMessageView();',
                'status' => SUPPORT_TICKET_STATUS_OPEN),
            SUPPORT_TICKET_EVENT_CHANGE_CATEGORY => array(
                'action' => '$this->actionSupportCategoryChange();',
                'status' => SUPPORT_TICKET_STATUS_OPEN),
            SUPPORT_TICKET_EVENT_CHANGE_OWNER    => array(
                'action' => '$this->actionTransfer();',
                'status' => SUPPORT_TICKET_STATUS_MOVED),
            SUPPORT_TICKET_EVENT_CHANGE_OTHER    => array(
                'action' => '$this->actionOtherChange();',
                'status' => SUPPORT_TICKET_STATUS_OPEN),
            SUPPORT_TICKET_EVENT_REPLY           => array(
                'action' => '$this->actionReply();',
                'status' => SUPPORT_TICKET_STATUS_WAIT),
            SUPPORT_TICKET_EVENT_MESSAGE_NEW     => array(
                'action' => '$this->actionMessageNew();',
                'status' => SUPPORT_TICKET_STATUS_NEW),
            SUPPORT_TICKET_EVENT_MESSAGE_DELETE  => array(
                'action' => '$this->actionMessageErase();',
                'status' => SUPPORT_TICKET_STATUS_OPEN),
            SUPPORT_TICKET_EVENT_REFERENCE       => array(
                'action' => '$this->actionReference();',
                'status' => SUPPORT_TICKET_STATUS_OPEN),
            SUPPORT_TICKET_EVENT_CLOSE           => array(
                'action' => '$this->actionClose();',
                'status' => SUPPORT_TICKET_STATUS_CLOSED),
        ),
        SUPPORT_TICKET_STATUS_WAIT    => array(
            SUPPORT_TICKET_EVENT_UNKNOWN         => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_MESSAGE_VIEW    => array(
                'action' => '$this->actionMessageView();',
                'status' => SUPPORT_TICKET_STATUS_WAIT),
            SUPPORT_TICKET_EVENT_CHANGE_CATEGORY => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_CHANGE_OWNER    => array(
                'action' => '$this->actionTransfer();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_CHANGE_OTHER    => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_REPLY           => array(
                'action' => '$this->actionReply();',
                'status' => SUPPORT_TICKET_STATUS_WAIT),
            SUPPORT_TICKET_EVENT_MESSAGE_NEW     => array(
                'action' => '$this->actionMessageNew();',
                'status' => SUPPORT_TICKET_STATUS_NEW),
            SUPPORT_TICKET_EVENT_MESSAGE_DELETE  => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_REFERENCE       => array(
                'action' => '$this->actionReference();',
                'status' => SUPPORT_TICKET_STATUS_WAIT),
            SUPPORT_TICKET_EVENT_CLOSE           => array(
                'action' => '$this->actionClose();',
                'status' => SUPPORT_TICKET_STATUS_CLOSED),
        ),
        SUPPORT_TICKET_STATUS_MOVED   => array(
            SUPPORT_TICKET_EVENT_UNKNOWN         => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_MESSAGE_VIEW    => array(
                'action' => '$this->actionMessageView();',
                'status' => SUPPORT_TICKET_STATUS_OPEN),
            SUPPORT_TICKET_EVENT_CHANGE_CATEGORY => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_CHANGE_OWNER    => array(
                'action' => '$this->actionTransfer();',
                'status' => SUPPORT_TICKET_STATUS_MOVED),
            SUPPORT_TICKET_EVENT_CHANGE_OTHER    => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_REPLY           => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_MESSAGE_NEW     => array(
                'action' => '$this->actionMessageNew();',
                'status' => SUPPORT_TICKET_STATUS_NEW),
            SUPPORT_TICKET_EVENT_MESSAGE_DELETE  => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_REFERENCE       => array(
                'action' => '$this->actionReference();',
                'status' => SUPPORT_TICKET_STATUS_MOVED),
            SUPPORT_TICKET_EVENT_CLOSE           => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
        ),
        SUPPORT_TICKET_STATUS_CLOSED  => array(
            SUPPORT_TICKET_EVENT_UNKNOWN         => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_MESSAGE_VIEW    => array(
                'action' => '$this->actionMessageView();',
                'status' => SUPPORT_TICKET_STATUS_CLOSED),
            SUPPORT_TICKET_EVENT_CHANGE_CATEGORY => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_CHANGE_OWNER    => array(
                'action' => '$this->actionTransfer();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_CHANGE_OTHER    => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_REPLY           => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_MESSAGE_NEW     => array(
                'action' => '$this->actionMessageNew();',
                'status' => SUPPORT_TICKET_STATUS_CLOSED),
            SUPPORT_TICKET_EVENT_MESSAGE_DELETE  => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_REFERENCE       => array(
                'action' => '$this->actionReference();',
                'status' => SUPPORT_TICKET_STATUS_CLOSED),
            SUPPORT_TICKET_EVENT_CLOSE           => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_CLOSED),
        ),
    );


    /**
     * Constructor (PHP4)
     *
     * @param   mixed       $objTicket  The affected Ticket object
     * @param   integer     $event      The event code
     * @param   integer     $value      The target value
     * @param   integer     $userId     The optional ID of the User causing
     *                                  the TicketEvent,
     *                                  used only if the TicketEvent
     *                                  originates from the database.
     * @param   integer     $oldStatus  The optional current status of the
     *                                  Ticket, used only if the TicketEvent
     *                                  originates from the database.
     * @param   string      $timestamp  The optional timestamp,
     *                                  used only if the TicketEvent
     *                                  originates from the database.
     * @param   integer     $id         The optional TicketEvent ID,
     *                                  used only if the TicketEvent
     *                                  originates from the database.
     * @return  TicketEvent             The TicketEvent object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function TicketEvent(
        $objTicket, $event, $value,
        $userId=false, $oldStatus=false, $timestamp='', $id=0
    ) {
        $this->__construct(
            $objTicket, $event, $value, $userId, $oldStatus, $timestamp, $id
        );
    }

    /**
     * Constructor (PHP5)
     *
     * The optional arguments $userId, $oldStatus, $timestamp, and $id *MUST*
     * only be used when creating an object from a database record.
     * See {@link getById()}.
     * If the call to the constructor does not include arguments for user ID
     * and/or status, the former is provided by the {@link Auth} class,
     * the latter is the result of processing the event and evaluating
     * the State-Event matrix.  Both are then stored in the database table
     * for both consistency and performance reasons.
     * @param   mixed       $objTicket  The affected Ticket object
     * @param   integer     $event      The event code
     * @param   integer     $value      The target value
     * @param   integer     $userId     The optional ID of the User causing
     *                                  the TicketEvent,
     *                                  used only if the TicketEvent
     *                                  originates from the database.
     * @param   integer     $oldStatus  The optional current status of the
     *                                  Ticket, used only if the TicketEvent
     *                                  originates from the database.
     * @param   string      $timestamp  The optional timestamp,
     *                                  used only if the TicketEvent
     *                                  originates from the database.
     * @param   integer     $id         The optional TicketEvent ID,
     *                                  used only if the TicketEvent
     *                                  originates from the database.
     * @return  TicketEvent             The TicketEvent object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function __construct(
        $objTicket, $event, $value,
        $userId=0, $oldStatus=false, $timestamp='', $id=0
    ) {
        $this->objTicket = $objTicket;
        $this->event     = $event;
        $this->value     = $value;
        $this->timestamp = $timestamp;
        $this->id        = $id;

        // Pick the current owner ID from the last CHANGE_OWNER
        // TicketEvent record.
        $this->ownerId = $this->getTicketOwnerId();

// TODO: Add some plausibility checks!
        $this->userId    = $userId;
        $this->oldStatus = $oldStatus;
        if ($this->id <= 0) {
// TODO: This only works in the Backend
//            $this->userId    = Auth::getUserId();
            $this->oldStatus =
                $this->getTicketStatus($this->objTicket->getId());
        }
        // The (new) status the Ticket will have, if processing
        // the TicketEvent succeeds.
        // This *MAY* be changed by the action*() methods, if necessary.
        $this->newStatus = $this->getNewTicketStatus();

//if (MY_DEBUG) { echo("TicketEvent::__construct(objTicket=[...], event=$event, value=$value, timestamp=$timestamp', id=$id): INFO: Made TicketEvent: ");var_export($this);echo("<br />"); }
        // This *SHOULD* never be visible!
        // -- Which means that every state-event combination causing it
        // has to be avoided!
        if ($this->newStatus == SUPPORT_TICKET_EVENT_UNKNOWN) {
if (MY_DEBUG) { echo("TicketEvent::__construct(objTicket=");var_export($objTicket);echo(", event=$event, value=$value, timestamp=$timestamp', id=$id): WARNING: Event is causing an UNKNOWN status!<br />"); }
        }
    }


    /**
     * Get this TicketEvents' ID
     * @return  integer     The TicketEvent ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getId()
    {
        return $this->Id;
    }

    /**
     * Get the event code from this TicketEvent
     * @return  integer     The event code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getEvent()
    {
        return $this->event;
    }

    /**
     * Get the value changed by this TicketEvent
     * @return  string      The changed value
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getValue()
    {
        return $this->value;
    }

    /**
     * Get the ID of the User that caused the TicketEvent
     * @return  integer     The User ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getUserId()
    {
        return $this->userId;
    }

    /**
     * Get the old Ticket status.
     * @return  integer     The old Ticket status
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getOldStatus()
    {
        return $this->oldStatus;
    }

    /**
     * Get the new Ticket status that results from this TicketEvent.
     * @return  integer     The new Ticket status
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getNewStatus()
    {
        return $this->newStatus;
    }

    /**
     * Get this TicketEvents' timestamp
     * @return  string      The TicketEvent timestamp
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getTimestamp()
    {
        return $this->timestamp;
    }


    /**
     * Get the current owner ID for the Ticket with the given ID.
     *
     * Note that a person is already considered to be the owner of a
     * Ticket even before he has viewed (and thus OPENed) it after it
     * has been MOVEd.
     * Also note that the owner is not necessarily identical to the
     * User that caused the TicketEvent.
     * This method may both be called as an object method or statically.
     * @param   integer     $ticketId       The Ticket ID, mandatory if
     *                                      the method is called statically.
     * @return  mixed                       The Ticket owner ID on success,
     *                                      false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getTicketOwnerId($ticketId=0)
    {
        global $objDatabase;
//if (MY_DEBUG) echo("TicketEvent::getCurrentOwnerName(ticketId=$ticketId): entered<br />");

        if ($ticketId <= 0) {
            $ticketId = $this->objTicket->getId();
            if ($ticketId <= 0) {
if (MY_DEBUG) echo("TicketEvent::getTicketOwnerId(ticketId=$ticketId): ERROR: missing or invalid ticket ID '$ticketId'!<br />");
                return false;
            }
        }
        $query = "
            SELECT value
              FROM ".DBPREFIX."module_support_ticket_event
             WHERE ticket_id=$ticketId
               AND event=".SUPPORT_TICKET_EVENT_CHANGE_OWNER."
          ORDER BY id DESC
        ";
        $objResult = $objDatabase->SelectLimit($query, 1);
        if (!$objResult) {
if (MY_DEBUG) echo("TicketEvent::getTicketOwnerId(ticketId=$ticketId): ERROR: query failed:<br />$query<br />");
            return false;
        }
        if ($objResult->EOF) {
            // No record present -- so there is no owner yet.
            return 0;
        }
        return $objResult->fields['value'];
    }


    /**
     * Get the current Ticket status for the Ticket with the given ID.
     *
     * This is the status that resulted by the last TicketEvent processed.
     * This method may both be called as an object method or statically.
     * @param   integer     $ticketId       The Ticket ID, mandatory if
     *                                      the method is called statically.
     * @return  mixed                       The current Ticket Status
     *                                      on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getTicketStatus($ticketId=0)
    {
//if (MY_DEBUG) echo("TicketEvent::getTicketStatus(ticketId=$ticketId): entered<br />");
        global $objDatabase;

        if ($ticketId <= 0) {
            $ticketId = $this->objTicket->getId();
            if ($ticketId <= 0) {
if (MY_DEBUG) echo("TicketEvent::getTicketStatus(ticketId=$ticketId): ERROR: missing or invalid ticket ID '$ticketId'!<br />");
                return false;
            }
        }
        $query = "
            SELECT `status`
              FROM ".DBPREFIX."module_support_ticket_event
             WHERE ticket_id=$ticketId
          ORDER BY id DESC
        ";
        $objResult = $objDatabase->SelectLimit($query, 1);
        if (!$objResult) {
if (MY_DEBUG) echo("TicketEvent::getTicketStatus(ticketId=$ticketId): ERROR: query failed:<br />$query<br />");
            return false;
        }
        // If there are no records, this Ticket is brand NEW.
        // It could as well be an error, however...
        if ($objResult->EOF) {
            return SUPPORT_TICKET_STATUS_NEW;
        }
        return $objResult->fields['status'];
    }


    /**
     * Get the current Message status for the Ticket and Message
     * with the given IDs and the current User.
     *
     * This is handled in a way different from the Ticket status itself.
     * While the Ticket status is determined by the status field in the
     * last TicketEvent record for each Ticket, the Message status is
     * handled individually for each User.  As a result, even if the
     * Ticket status is OPEN, Messages associated with this Ticket are
     * still shown as NEW to Users that haven't viewed them.
     * This method may both be called as an object method or statically.
     * @param   integer     $messageId      The Message ID
     * @param   integer     $ticketId       The Ticket ID, mandatory if
     *                                      this method is called statically.
     * @return  mixed                       The current Message Status
     *                                      on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getMessageStatus($messageId, $ticketId=0)
    {
if (MY_DEBUG) echo("TicketEvent::getMessageStatus(messageId=$messageId, ticketId=$ticketId): entered<br />");
        global $objDatabase;

        if ($messageId <= 0) {
if (MY_DEBUG) echo("TicketEvent::getMessageStatus(messageId=$messageId, ticketId=$ticketId): ERROR: missing or invalid message ID '$messageId'!<br />");
            return false;
        }
        if ($ticketId <= 0) {
            $ticketId = $this->objTicket->getId();
            if ($ticketId <= 0) {
if (MY_DEBUG) echo("TicketEvent::getMessageStatus(messageId=$messageId, ticketId=$ticketId): ERROR: missing or invalid ticket ID '$ticketId'!<br />");
                return false;
            }
        }

        $query = "
            SELECT event, value
              FROM ".DBPREFIX."module_support_ticket_event
             WHERE ticket_id=$ticketId
               AND event IN
                   (".SUPPORT_TICKET_EVENT_CHANGE_OWNER.",
                    ".SUPPORT_TICKET_EVENT_MESSAGE_NEW.",
                    ".SUPPORT_TICKET_EVENT_MESSAGE_VIEW.",
                    ".SUPPORT_TICKET_EVENT_REPLY.",
                    ".SUPPORT_TICKET_EVENT_MESSAGE_DELETE.")
          ORDER BY id ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
if (MY_DEBUG) echo("TicketEvent::getMessageStatus(messageId=$messageId, ticketId=$ticketId): ERROR: query failed:<br />$query<br />");
            return false;
        }

        // Look up the status for the current User
        $userId = Auth::getUserId();
        if ($userId <= 0) {
if (MY_DEBUG) echo("TicketEvent::getMessageStatus(messageId=$messageId, ticketId=$ticketId): ERROR: missing or invalid user ID '$userId'!<br />");
            return false;
        }
        // Cautiously assume that there may only be bogus entries
        // that don't define the Messages' status properly.
        $messageStatus = SUPPORT_MESSAGE_STATUS_UNKNOWN;
        $isOwner = false;

        while (!$objResult->EOF) {
            $event = $objResult->fields['event'];
            $value = $objResult->fields['value'];
            switch ($event) {
              case SUPPORT_TICKET_EVENT_MESSAGE_NEW:
                // If the Message IDs match, the Message was inserted
                // into the system.
                if ($messageId == $value) {
                    $messageStatus = SUPPORT_MESSAGE_STATUS_NEW;
                }
                break;
              case SUPPORT_TICKET_EVENT_REPLY:
                // If the Message IDs match, and the User was the owner
                // at the time, she wrote it herself.
                if ($isOwner && $messageId == $value) {
                    $messageStatus = SUPPORT_MESSAGE_STATUS_READ;
                }
                break;
              case SUPPORT_TICKET_EVENT_CHANGE_OWNER:
                // True iff the owner was changed to the current User
                $isOwner = ($value == $userId);
                break;
              case SUPPORT_TICKET_EVENT_MESSAGE_VIEW:
                // The current User took a look at that Message iff
                // she was the owner at the time of reading!
                if ($isOwner && $messageId == $value) {
                    $messageStatus = SUPPORT_MESSAGE_STATUS_READ;
                }
                break;
              case SUPPORT_TICKET_EVENT_MESSAGE_DELETE:
                // The Message has been deleted.
                // Note that this applies to all Users!
                // To undelete a Message, the TicketEvent record has
                // to be permanently removed.
                if ($messageId == $value) {
                    return SUPPORT_MESSAGE_STATUS_DELETED;
                }
                break;
              default:
if (MY_DEBUG) echo("TicketEvent::getMessageStatus(messageId=$messageId, ticketId=$ticketId): ERROR CODE 8342A-CRX (Engine temperature is above the measurement range)<br />");
                return false;
            }
//if (MY_DEBUG) echo("TicketEvent::getMessageStatus(messageId=$messageId, ticketId=$ticketId): Loop: event=$event, value=$value, messageStatus=$messageStatus<br />");
            $objResult->MoveNext();
        }
if (MY_DEBUG) echo("TicketEvent::getMessageStatus(messageId=$messageId, ticketId=$ticketId): INFO: Message status is $messageStatus.  Exiting<br />");
        return $messageStatus;
    }


    /**
     * Returns true iff the Ticket has new Messages associated with it.
     *
     * This method may both be called as an object method or statically.
     * @param   integer     $ticketId       The Ticket ID, mandatory if
     *                                      this method is called statically.
     * @return  mixed                       The unread Message count on success,
     *                                      false otherwise
     * @global  mixed       $objDatabase    Database object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function ticketHasNewMessages($ticketId=0)
    {
        global $objDatabase;
//if (MY_DEBUG) echo("TicketEvent::ticketHasNewMessages(ticketId=$ticketId): entered<br />");

        if ($ticketId <= 0) {
            $ticketId = $this->objTicket->getId();
            if ($ticketId <= 0) {
if (MY_DEBUG) echo("TicketEvent::ticketHasNewMessages(ticketId=$ticketId): ERROR: missing or invalid ticket ID '$ticketId'!<br />");
                return false;
            }
        }

        // The value field contains:
        // - SUPPORT_TICKET_EVENT_CHANGE_OWNER:     The new owner ID
        // - SUPPORT_TICKET_EVENT_MESSAGE_NEW,
        //   SUPPORT_TICKET_EVENT_MESSAGE_VIEW,
        //   SUPPORT_TICKET_EVENT_MESSAGE_DELETE:   The (incoming) Message ID
        // - SUPPORT_TICKET_EVENT_REPLY:            The (reply) Message ID
        $query = "
            SELECT event, value
              FROM ".DBPREFIX."module_support_ticket_event
             WHERE ticket_id=$ticketId
               AND (   event=".SUPPORT_TICKET_EVENT_MESSAGE_NEW."
                    OR event=".SUPPORT_TICKET_EVENT_MESSAGE_VIEW."
                    OR event=".SUPPORT_TICKET_EVENT_MESSAGE_DELETE."
                    OR event=".SUPPORT_TICKET_EVENT_CHANGE_OWNER."
                    OR event=".SUPPORT_TICKET_EVENT_REPLY.")
          ORDER BY id ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
if (MY_DEBUG) echo("TicketEvent::ticketHasNewMessages(ticketId=$ticketId): ERROR: query failed:<br />$query<br />");
            return false;
        }

        // The current Users' ID
        $userId = Auth::getUserId();
        // Array for collecting the Messages viewed while owned
        $arrViews = array();
        // Flag indicating whether the current User was the owner
        // of the Ticket at the time she read one of the Messages
        $isOwner = false;

        while (!$objResult->EOF) {
            $event = $objResult->fields['event'];
            $value = $objResult->fields['value'];
            switch ($event) {
              case SUPPORT_TICKET_EVENT_CHANGE_OWNER:
                // True iff the owner was changed to the current User.
                $isOwner = ($value == $userId);
                break;
              case SUPPORT_TICKET_EVENT_MESSAGE_NEW:
                // Tag new Message IDs in the array.
                $arrViews[$value] = true;
                break;
              case SUPPORT_TICKET_EVENT_MESSAGE_VIEW:
                // The current User took a look at that Message iff
                // she was the owner at the time of reading!
                // Remove the Message ID from the array.
                if ($isOwner) unset($arrViews[$value]);
                break;
              case SUPPORT_TICKET_EVENT_MESSAGE_DELETE:
                // The Message was deleted, applies to all Users.
                unset($arrViews[$value]);
                break;
              case SUPPORT_TICKET_EVENT_REPLY:
                // The Message was written by herself
                // as a reply to a Ticket.
                if ($isOwner) unset($arrViews[$value]);
                break;
              default:
if (MY_DEBUG) echo("TicketEvent::ticketHasNewMessages(): ERROR CODE 72-84268-GFX (DirectX V12.8258.001 failed to load)<br />");
                return false;
            }
            $objResult->MoveNext();
        }
        // Only unread Message IDs left
        return count($arrViews);
    }


    /**
     * Delete this TicketEvent from the database.
     *
     * Note that this *MUST* only be called when rolling back a failed
     * Ticket or Message update, or to remove a MESSAGE_DELETE event in order
     * to undelete a Message.  Updates to Tickets or Messages *MUST NOT*
     * be or have been made for this TicketEvent!
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function delete()
    {
        global $objDatabase;
//if (MY_DEBUG) echo("Debug: TicketEvent::delete(): entered<br />");

        if (!$this->id > 0) {
if (MY_DEBUG) echo("TicketEvent::delete(): ERROR: missing or illegal ID ($this->id)!<br />");
            return false;
        }
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_support_ticket_event
             WHERE id=$this->id
        ");
        if (!$objResult) {
if (MY_DEBUG) echo("TicketEvent::delete(): Error: Failed to delete the TicketEvent with ID $this->id from the database!<br />");
            return false;
        }
        return true;
    }


    /**
     * Delete the TicketEvents referring to a certain Ticket ID
     * from the database.
     *
     * Note that this *MUST* only be called when the associated
     * Ticket is deleted as well.
     * This method may both be called as an object method or statically.
     * @param       integer     $ticketId       The Ticket ID, mandatory if
     *                                          this method is called statically.
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function deleteByTicketId($ticketId=0)
    {
        global $objDatabase;
//if (MY_DEBUG) echo("Debug: TicketEvent::delete(): entered<br />");

        if ($ticketId <= 0) {
            $ticketId = $this->objTicket->getId();
            if ($ticketId <= 0) {
if (MY_DEBUG) echo("TicketEvent::deleteByTicketId(ticketId=$ticketId): ERROR: missing or illegal Ticket ID!<br />");
                return false;
            }
        }

        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_support_ticket_event
             WHERE ticket_id=$ticketId
        ");
        if (!$objResult) {
if (MY_DEBUG) echo("TicketEvent::deleteByTicketId(ticketId=$ticketId): Error: Failed to delete the TicketEvent records from the database<br />");
            return false;
        }
        return true;
    }


    /**
     * Insert this TicketEvent into the database.
     *
     * Note that the timestamp field will be set to the current date and time
     * on INSERTing the record, by definition of the database table.
     * Note that this method *MUST* call refreshTimestamp() after a successful
     * INSERT.
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function insert()
    {
        global $objDatabase;

        $ticketId = $this->objTicket->getId();
        $query = "
            INSERT INTO ".DBPREFIX."module_support_ticket_event (
                   ticket_id,
                   `event`,
                   `value`,
                   user_id,
                   `status`
            ) VALUES (
                   $ticketId,
                   $this->event,
                   $this->value,
                   $this->userId,
                   $this->newStatus
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $this->id = $objDatabase->Insert_ID();
        // Update the object with the actual timestamp
//if (MY_DEBUG) echo("TicketEvent::insert(): done<br />");
        return $this->refreshTimestamp();
    }


    /**
     * Updates the TicketEvent object with the timestamp value stored in the
     * database.
     *
     * This *MUST* be called by insert() after INSERTing any new
     * event record!
     * @return  boolean         True on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function refreshTimestamp()
    {
        global $objDatabase;

        $query = "
            SELECT `timestamp`
              FROM ".DBPREFIX."module_support_ticket_event
             WHERE id=$this->id
        ";
if (MY_DEBUG) echo("TicketEvent::refreshTimestamp(): INFO: query: $query<br />");
        $objResult = $objDatabase->Execute($query);
if (MY_DEBUG) echo("TicketEvent::refreshTimestamp(): INFO: objResult: '$objResult'<br />");
        if (!$objResult) {
if (MY_DEBUG) echo("TicketEvent::refreshTimestamp(): ERROR: query failed, objResult: '$objResult', count: ".$objResult->RecordCount()."<br />");
            return false;
        }
        if ($objResult->RecordCount() == 0) {
if (MY_DEBUG) echo("TicketEvent::refreshTimestamp(): ERROR: no result: ".$objResult->RecordCount()."<br />");
            return false;
        }
        $this->timestamp = contrexx_stripslashes($objResult->fields['timestamp']);
if (MY_DEBUG) echo("TicketEvent::refreshTimestamp(): INFO: timestamp is '$this->timestamp'<br />");
        return true;
    }


    /**
     * Select a TicketEvent by ID from the database.
     * @static
     * @param   integer     $id             The TicketEvent ID
     * @return  TicketEvent                 The TicketEvent object on success,
     *                                      false otherwise
     * @global  mixed       $objDatabase    Database object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getById($id)
    {
        global $objDatabase;

        $query = "
            SELECT *
              FROM ".DBPREFIX."module_support_ticket_event
             WHERE id=$id
        ";
if (MY_DEBUG) echo("TicketEvent::getById($id): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
if (MY_DEBUG) echo("TicketEvent::getById($id): objResult: '$objResult'<br />");
        if (!$objResult) {
if (MY_DEBUG) echo("TicketEvent::getById($id): query failed, objResult: '$objResult', count: ".$objResult->RecordCount()."<br />");
            return false;
        }
        if ($objResult->RecordCount() == 0) {
if (MY_DEBUG) echo("TicketEvent::getById($id): no result: ".$objResult->RecordCount()."<br />");
            return false;
        }
// TODO: add some checks here for NULL and zero values!
        $objTicketEvent = new TicketEvent(
            Ticket::getById($objResult->fields['ticket_id']),
            $objResult->fields['event'],
            $objResult->fields['value'],
            $objResult->fields['user_id'],
            $objResult->fields['status'],
            contrexx_stripslashes($objResult->fields['timestamp']),
            $objResult->fields['id']
        );
        return $objTicketEvent;
    }


    /**
     * Returns an array of TicketEvent objects related to a certain
     * Ticket ID and/or having certain other properties from the database.
     *
     * Any of the mandatory arguments may contain values evaluating to
     * the boolean false value, in which case they are not considered
     * in the WHERE clause.  Any such arguments that evaluate to true,
     * however, limit the result set to records having identical values.
     * The optional parameter $order determines the sorting order
     * in SQL syntax, it defaults to ordered by date descending, or
     * latest first.
     * The optional parameter $offset determines the offset of the
     * first TicketEvent to be read from the database, and defaults to 0 (zero).
     * The optional parameter $limit limits the number of results.
     * It defaults to the value of the global $_CONFIG['corePagingLimit']
     * setting if unset or zero.
     * @static
     * @param       integer     $ticketId       The desired ticket ID, or zero
     * @param       integer     $event          The desired event code, or zero
     * @param       integer     $value          The desired value, or zero
     * @param       integer     $userId         The desired User ID, or zero
     * @param       string      $order          The optional sorting order
     * @param       integer     $offset         The optional offset
     * @param       integer     $limit          The optional limit
     * @return      array                       The array of TicketEvent objects
     *                                          on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @global      array       $_CONFIG        Global configuration array
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getTicketEventArray(
        $ticketId, $event, $value, $userId,
        $order="'timestamp' DESC", $offset=0, $limit=0
    ) {
        global $objDatabase, $_CONFIG;

        $limit = ($limit ? $limit : $_CONFIG['corePagingLimit']);
        $query = "
            SELECT id
              FROM ".DBPREFIX."module_support_ticket_event
             WHERE 1
              ".($ticketId ? "AND ticket_id=$ticketId" : '')."
              ".($event    ? "AND event=$event"        : '')."
              ".($value    ? "AND value='$value'"      : '')."
              ".($userId   ? "AND user_id='$userId'"   : '')."
          ORDER BY $order
        ";
        $objResult = $objDatabase->SelectLimit($query, $limit, $offset);
        if (!$objResult) {
            return false;
        }
        // return array
        $arrTicketEvent = array();
        while (!$objResult->EOF) {
            $arrTicketEvent[] = TicketEvent::getById($objResult->fields['id']);
            $objResult->MoveNext();
        }
        return $arrTicketEvent;
    }


    /**
     * Processes any TicketEvent.
     *
     * Finds out what action to take, calls appropriate action*() methods,
     * and returns the new Ticket status, which in fact may be identical
     * to the old one.
     * @return  mixed               The new Ticket status on success,
     *                              false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function process()
    {
        // Verify that this TicketEvent hasn't been insert()ed yet.
        if ($this->id) {
if (MY_DEBUG) echo("TicketEvent::process(): ERROR: TicketEvent (ID $this->id) *MUST NOT* be processed!<br />");
            return false;
        }
        // Get the appropriate action method name for the status and event
        $action = $this->getAction();
        // These methods *MUST* return a boolean true upon success,
        // or false otherwise.  This indicates whether the Ticket status
        // is to be updated or not.
        // They must also create the appropriate event record.
        // If this fails, they *MUST* return false, and the status
        // *MUST* remain untouched by the caller!
        // They *MAY* change the value of $newStatus if necessary
        // (although this is a violation of the State-Event model, it
        // greatly simplifies cases where the status depends on external
        // properties as well as the original status and the event).
        $actionResult = eval("return $action");
if (MY_DEBUG) echo("TicketEvent::process(): INFO: Action '$action' returned '$actionResult'<br />");
        if ($actionResult) {
if (MY_DEBUG) echo("TicketEvent::process(): INFO: Inserting new TicketEvent record.<br />");
            $this->insert();
if (MY_DEBUG) echo("TicketEvent::process(): INFO: Returning new status $this->newStatus<br />");
            // Return the new Ticket status
            return $this->newStatus;
        }
if (MY_DEBUG) echo("TicketEvent::process(): INFO: returning false<br />");
        return false;
    }


    /**
     * Returns the appropriate Action for the current Ticket status
     * and event code.
     *
     * The event code must be one of the codes define()d in the
     * constants in {@link lib/SupportCommon.class.php} and used in
     * {@link seaTicketMatrix.inc.php} to initialize the matrix.
     * The returned string must correspond to a action*() method.
     * @return  string          The name of the action to take.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getAction()
    {
        return $this->arrSea[
            $this->getTicketStatus($this->objTicket->getId())
        ][$this->event]['action'];
    }


    /**
     * Returns the prospective status the Ticket will be set to
     * after successfully processing the current event.
     *
     * The returned integer corresponds to a status as define()d
     * in the constants in {@link lib/SupportCommon.class.php}.
     * @return  string          The prospective status code.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getNewTicketStatus()
    {
//if (MY_DEBUG) { echo("TicketEvent::getNewTicketStatus(): State-Event matrix: ");var_export($this->arrSea);echo("<br />"); }
//if (MY_DEBUG) echo("TicketEvent::getNewTicketStatus(): Ticket status: ".$this->objTicket->getStatus().", event: $this->event<br />");
        return $this->arrSea[
            $this->getTicketStatus($this->objTicket->getId())
        ][$this->event]['status'];
    }


    /**
     * Take no Action on this Ticket.
     *
     * This is all about not doing anything.
     * Note that this, as any of the action*() methods, *MUST NOT* be called
     * for TicketEvents that have already been insert()ed into the database!
     * @return  boolean         True.  Always.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function actionNone()
    {
        return true;
    }


    /**
     * Close a Ticket.
     *
     * This method is to be called for Tickets with status OPEN,
     * whenever a CLOSE event occurs.
     * The event is only allowed if the current user
     * is the owner of the Ticket.
     * Note that this, as any of the action*() methods, *MUST NOT* be called
     * for TicketEvents that have already been insert()ed into the database!
     * @return  boolean     True on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function actionClose()
    {
        if ($this->ownerId <= 0) {
if (MY_DEBUG) echo("TicketEvent::actionClose(): ERROR: No or invalid current owner ID ($this->ownerId)!<br />");
        }
        if ($this->userId != $this->ownerId) {
if (MY_DEBUG) echo("TicketEvent::actionClose(): ERROR: The current User isn't the owner of the Ticket ($this->ownerId)!<br />");
            return false;
        }
        return true;
    }


    /**
     * Modify the Ticket Support Category (S).
     *
     * This is only possible for the current owner of the Ticket.
     * The $value variable carries the new Support Category ID.
     * Note that this, as any of the action*() methods, *MUST NOT* be called
     * for TicketEvents that have already been insert()ed into the database!
     * @return  boolean     True on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function actionSupportCategoryChange()
    {
        if ($this->value <= 0) {
if (MY_DEBUG) echo("TicketEvent::actionSupportCategoryChange(): ERROR: No or invalid target Support Category ID $this->value!<br />");
            return false;
        }
        if ($this->ownerId <= 0) {
if (MY_DEBUG) echo("TicketEvent::actionSupportCategoryChange(): ERROR: No or invalid owner ID ($this->ownerId)!<br />");
            return false;
        }
        if ($this->userId != $this->ownerId) {
if (MY_DEBUG) echo("TicketEvent::actionSupportCategoryChange(): ERROR: The current User isn't the owner of the Ticket ($this->ownerId)!<br />");
            return false;
        }
        return true;
    }


    /**
     * Reply to the Ticket (R).
     *
     * Store and send the reply, update the KB and all affected tables.
     * The $value variable contains the Message ID of the reply.
     * This is only possible for the current owner of the Ticket, and if
     * the Ticket is in either OPEN or WAIT state.
     * Note that this, as any of the action*() methods, *MUST NOT* be called
     * for TicketEvents that have already been insert()ed into the database!
     * @return  boolean     True on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function actionReply()
    {
        if ($this->value <= 0) {
if (MY_DEBUG) echo("TicketEvent::actionReply(): ERROR: No or invalid Message ID $this->value!<br />");
            return false;
        }
        if ($this->ownerId <= 0) {
if (MY_DEBUG) echo("TicketEvent::actionReply(): ERROR: No or invalid owner ID ($this->ownerId)!<br />");
            return false;
        }
        if ($this->userId != $this->ownerId) {
if (MY_DEBUG) echo("TicketEvent::actionReply(): ERROR: The current User isn't the owner of the Ticket ($this->ownerId)!<br />");
            return false;
        }
        return true;
    }


    /**
     * Transfer this Ticket to a User (T).
     *
     * This method is to be called for Tickets with status OPEN or MOVED,
     * whenever a CHANGE_PERSON event occurs.  The $value variable
     * contains the target User ID.
     * Two cases are handled:
     * - Take over a Ticket.  Anyone can take over Tickets in MOVED state,
     *   which they will keep until viewed by the new owner.
     *   This is intended to enable others to process your Tickets when
     *   you are unable to.  (userId)
     * - Delegate a Ticket to someone else.  This is only allowed, however,
     *   for the owner of the Ticket.
     * Note that this, as any of the action*() methods, *MUST NOT* be called
     * for TicketEvents that have already been insert()ed into the database!
     * @return  boolean     True on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function actionTransfer()
    {
        if ($this->value <= 0) {
if (MY_DEBUG) echo("TicketEvent::actionTransfer(): ERROR: No or invalid target owner ID $this->value!<br />");
            return false;
        }
        if ($this->userId <= 0) {
if (MY_DEBUG) echo("TicketEvent::actionTransfer(): ERROR: No or invalid user ID ($this->userId)!<br />");
            return false;
        }

        switch ($this->oldStatus) {
          case SUPPORT_TICKET_STATUS_NEW:
            // NEW state.  The Ticket is being assigned to the current User if
            // two conditions are met:
            // - The Ticket *MUST NOT* have a valid owner ID already, and...
            if ($this->ownerId > 0) {
if (MY_DEBUG) echo("TicketEvent::actionTransfer(): ERROR: NEW Ticket already has an owner (ID $this->ownerId)!<br />");
                return false;
            }
            // - ...the target owner ID *MUST* be identical to
            //   the current User ID.
            if ($this->value != $this->userId) {
if (MY_DEBUG) echo("TicketEvent::actionTransfer(): ERROR: Target owner (ID $this->value) is different from current User (ID $this->userId)!<br />");
                return false;
            }
            return true;
          case SUPPORT_TICKET_STATUS_OPEN:
            // OPEN state.
            // The owner may delegate the Ticket to anyone except herself; so:
            // - The Ticket must have a valid owner ID already, ...
            if ($this->ownerId <= 0) {
if (MY_DEBUG) echo("TicketEvent::actionTransfer(): ERROR: No or invalid owner (ID $this->value)!<br />");
                return false;
            }
            // - the current owner ID *MUST* be equal to the
            //   current User ID, and...
            if ($this->ownerId != $this->userId) {
if (MY_DEBUG) echo("TicketEvent::actionTransfer(): ERROR: The current User (ID $this->userId) may not transfer the Ticket to User ID $this->value, as he is not the owner (ID $this->ownerId).<br />");
                return false;
            }
            // - ...the target owner ID *MUST* be different
            //   from the current User ID.
            if ($this->value == $this->userId) {
if (MY_DEBUG) echo("TicketEvent::actionTransfer(): INFO: Target owner ID ($this->value) is equal to current User ID ($this->userId) already.<br />");
                return false;
            }
            return true;
          case SUPPORT_TICKET_STATUS_MOVED:
            // MOVED state.  Anyone can take over the Ticket.
            // The target owner ID in $value *MUST* be equal to the current
            // User ID.
            if ($this->value != $this->userId) {
if (MY_DEBUG) echo("TicketEvent::actionTransfer(): ERROR: The current User (ID $this->userId) can only transfer the Ticket to himself (not ID $this->value)!<br />");
                return false;
            }
            // It doesn't make sense to transfer the Ticket
            // if the current User is the current owner already.
            // However, we have to return true so that the
            // Ticket status is updated.
            if ($this->ownerId == $this->userId) {
if (MY_DEBUG) echo("TicketEvent::actionTransfer(): INFO: The current owner (ID $this->ownerId) is equal to current User ID ($this->userId) already.<br />");
                return true;
            }
            return true;
          default:
if (MY_DEBUG) echo("TicketEvent::actionTransfer(): ERROR: The Ticket is in an illegal state ($this->oldStatus)!<br />");
            return false;
        }
if (MY_DEBUG) echo("TicketEvent::actionTransfer(): ERROR: Passed the switch block!<br />");
        return false;
    }


    /**
     * A Message associated with the Ticket is being viewed.
     *
     * If the Ticket has no more new Messages (unread by the current User),
     * this method returns true, and {@link process()} will return
     * the new status, so that the Ticket can be updated.
     * Note that this, as any of the action*() methods, *MUST NOT* be called
     * for TicketEvents that have already been insert()ed into the database!
     * @return  boolean             True if the Ticket has no new Messages,
     *                              false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function actionMessageView()
    {
//if (MY_DEBUG) { echo("TicketEvent::actionMessageView(): INFO: entered.  TicketEvent: ");var_export($this);echo("<br />"); }

        if ($this->userId <= 0) {
if (MY_DEBUG) echo("TicketEvent::actionMessageView(): ERROR: No or illegal User ID ($this->userId)!<br />");
            return false;
        }
        if ($this->oldStatus == SUPPORT_TICKET_STATUS_NEW) {
            // NEW state.  The Ticket is being assigned to the current User.
            // The Ticket *MUST NOT* have a valid owner ID already!
            if ($this->ownerId > 0) {
if (MY_DEBUG) echo("TicketEvent::actionTransfer(): ERROR: NEW Ticket already has an owner (ID $this->ownerId)!<br />");
                return false;
            }
            // Create the TicketEvent to change ownership.
            // This *MUST* be made before the VIEW event can be logged!
            $objTicketEvent = new TicketEvent(
                $this->objTicket,
                SUPPORT_TICKET_EVENT_CHANGE_OWNER,
                $this->userId,
                Auth::getUserId()
            );
            // If the owner has been set successfully, the VIEW may also
            // be processed.
            return $objTicketEvent->process();
        }
        // Only accept VIEWs for Tickets having an owner already.
        // This implies that NEW Tickets are transferred to the current
        // User before this action is performed!
        if ($this->ownerId <= 0) {
if (MY_DEBUG) echo("TicketEvent::actionMessageView(): ERROR: No or illegal owner ID ($this->ownerId)!<br />");
            return false;
        }
        // The owner is someone else?
        if ($this->userId != $this->ownerId) {
if (MY_DEBUG) echo("TicketEvent::actionMessageView(): INFO: current User (ID $this->userId) is NOT the owner (ID $this->ownerId) of the Ticket (ID ".$this->objTicket->getId().")!<br />");
            // Reject
            return false;
        }
        // One case is that the Ticket may be in UNKNOWN state.
        // By viewing any one of its Messages, the current owner
        // can bring it back to OPEN.
        if ($this->oldStatus == SUPPORT_TICKET_STATUS_UNKNOWN) {
            return true;
        }
        $messageStatus =
            $this->getMessageStatus($this->value, $this->objTicket->getId());
        // Add the record only if this Message is unREAD by this User.
        // Note that any views while the Message is marked as DELETED
        // are not logged either!
        if ($messageStatus == SUPPORT_MESSAGE_STATUS_NEW) {
if (MY_DEBUG) echo("TicketEvent::actionMessageView(): INFO: marking NEW Message (ID $this->value) as READ by the current User (ID $this->userId).<br />");
            // All conditions are met; mark this Message as READ by
            // the current User (which happens to be the owner, too).
            return true;
        }
        // Otherwise, fail.
        return false;
    }


    /**
     * Modify any property of the Ticket other than the Support Category,
     * or the owner (X).  [TODO: Explain which!]
     *
     * Note that this, as any of the action*() methods, *MUST NOT* be called
     * for TicketEvents that have already been insert()ed into the database!
     * @return  boolean     True on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function actionOtherChange()
    {
        if ($this->value <= 0) {
if (MY_DEBUG) echo("TicketEvent::actionOtherChange(): ERROR: No or invalid target ID $this->value!<br />");
            return false;
        }
        if ($this->ownerId <= 0) {
if (MY_DEBUG) echo("TicketEvent::actionOtherChange(): ERROR: No or invalid owner ID ($this->ownerId)!<br />");
            return false;
        }
        if ($this->userId != $this->ownerId) {
if (MY_DEBUG) echo("TicketEvent::actionOtherChange(): ERROR: The current User isn't the owner of the Ticket ($this->ownerId)!<br />");
            return false;
        }
        return true;
    }


    /**
     * Add a new Message to an existing Ticket (N).
     *
     * The $value variable contains the Message ID.
     * Note that this, as any of the action*() methods, *MUST NOT* be called
     * for TicketEvents that have already been insert()ed into the database!
     * @return  boolean     True on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function actionMessageNew()
    {
        if ($this->value <= 0) {
if (MY_DEBUG) echo("TicketEvent::actionMessageNew(): ERROR: No or invalid message ID $this->value!<br />");
            return false;
        }
        return true;
    }


    /**
     * Mark a Message as deleted (E).
     *
     * The $value variable contains the Message ID.
     * Note that this, as any of the action*() methods, *MUST NOT* be called
     * for TicketEvents that have already been insert()ed into the database!
     * @return  boolean     True on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function actionMessageErase()
    {
        if ($this->value <= 0) {
if (MY_DEBUG) echo("TicketEvent::actionMessageNew(): ERROR: No or invalid message ID $this->value!<br />");
            return false;
        }
        return true;
    }


    /**
     * Create a reference from a new Ticket to another (L).
     *
     * The $value variable contains the old Ticket ID.
     * Note that this, as any of the action*() methods, *MUST NOT* be called
     * for TicketEvents that have already been insert()ed into the database!
     * @return  boolean     True on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function actionReference()
    {
        if ($this->value <= 0) {
if (MY_DEBUG) echo("TicketEvent::actionMessageNew(): ERROR: No or invalid old Ticket ID $this->value!<br />");
            return false;
        }
        return true;
    }
}

?>
