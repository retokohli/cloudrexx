<?php

/* TODO: move somewhere else
 ($this->ticketHasNewMessages() > 1) {
echo("TicketEvent::actionView(): INFO: Ticket HAS new Messages.<br />");
            return false;
        }
echo("TicketEvent::actionView(): INFO: Ticket has NO new Messages.<br />");
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

/*

Database Table Structure:

DROP TABLE contrexx_module_support_ticket_event;
CREATE TABLE contrexx_module_support_ticket_event (
  id            int(10)     unsigned NOT NULL auto_increment,
  ticket_id     int(10)     unsigned     NULL default NULL,
  `event`       tinyint(2)  unsigned     NULL default NULL,
  `value`       int(10)     unsigned     NULL default NULL,
  `timestamp`   timestamp            NOT NULL default current_timestamp,
  PRIMARY KEY     (id),
  KEY ticket_id   (ticket_id),
  KEY `timestamp` (`timestamp`)
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
     * The would-be Ticket status after processing the Event successfully.
     *
     * Note that is a run-time variable only, it is not stored in the
     * database table.
     * @var integer
     */
    var $newStatus;


    /**
     * The State-Event-Action Matrix
     *
     * The state-event diagram for the Ticket:
     * (The default Status of any new Tickets is, of course, NEW.)
     *
     * \EVENT  |UNKN.| VIEW |CATEG| PERS. |OTHER|REPLY|M.NEW|M.DEL|REFER|CLOSE|
     * STATUS\ |     |      |     |       |     |     |     |     |     |     |
     * UNKNOWN | -/U | V/O  | -/U | -/U   | -/U | -/U | F/U | -/U | L/U | -/U |
     * NEW     | -/U | V/O* | -/U | -/U   | -/U | -/U | F/N | -/U | L/N | -/U |
     * OPEN    | -/U | V/O  | S/O | T/M***| X/O | R/W | F/N | E/O | L/O | C/C |
     * WAIT    | -/U | V/W  | -/U | -/U   | -/U | R/W | F/N | -/U | L/W | -/U |
     * MOVED   | -/U | V/O**| -/U | T/M***| -/U | -/U | F/N | -/U | L/M | -/U |
     * CLOSED  | -/U | V/C  | -/U | -/U   | -/U | -/U | N/C | -/U | L/C | -/C |
     *           \____________________ ACTION/NEW_STATUS ___________________/
     *
     * Actions are:
     * -    No action is performed, and no Event record created.
     * C    Close a Ticket.  Update everything related.
     * E    Erase: Mark one of the Tickets' Messages as deleted.
     *      Applies to the current owner of the Ticket only.
     * F    Add the followup message to the Ticket (in the Message table)
     *      and update all affected data.  The Ticket status will be reset
     *      to NEW, except when it was UNKNOWN before.  In that case, it
     *      won't be changed. (messageId)
     * L    Link: Create a reference from a new Ticket to a closed one.
     *      This event is only created by a new Message referring to
     *      an already closed Ticket.  Neither Ticket status will be
     *      affected by this, see event N, new Ticket.
     * N    New Ticket (and reference).  A new Ticket will automagically be created,
     *      with a reference to the previous one.  Caused by a MESSAGE to an
     *      already closed Ticket. (messageId)
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
     * V    View the Ticket.  Adds a VIEW Event record referencing the Message.
     *      Assigns ownership of the Ticket to the reader if either the
     *      status is NEW (no current owner), or if the status is MOVED and
     *      the target owner is the person reading it.
     * X    Modify any property of the Ticket other than the Support Category,
     *      or the owner.  [TODO: Explain which!]
     *
     * Notes:
     * *  : New Tickets are not assigned to any person.  Anybody reading it first
     *      will take ownership for it.
     * ** : The ownership will be accepted, and the Ticket status set to OPEN
     *      only if read by the person it has been assigned to.
     * ***: OPEN and MOVED Tickets can always be taken over by anyone willing
     *      to process them further.  The status will change to MOVED.
     *      Once they own it, the status will be set to OPEN upon viewing.
     *      Tickets can only be delegated to another person by the owner.
     *
     * Note that this variable *SHOULD* of course be static.
     */
    var $arrSea = array(
        SUPPORT_TICKET_STATUS_UNKNOWN => array(
            SUPPORT_TICKET_EVENT_UNKNOWN         => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_MESSAGE_VIEW    => array(
                'action' => '$this->actionView();',
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
                'action' => '$this->actionFollowup();',
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
                'action' => '$this->actionView();',
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
                'action' => '$this->actionFollowup();',
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
                'action' => '$this->actionView();',
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
                'action' => '$this->actionFollowup();',
                'status' => SUPPORT_TICKET_STATUS_NEW),
            SUPPORT_TICKET_EVENT_MESSAGE_DELETE  => array(
                'action' => '$this->actionMessageDelete();',
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
                'action' => '$this->actionView();',
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
                'action' => '$this->actionFollowup();',
                'status' => SUPPORT_TICKET_STATUS_NEW),
            SUPPORT_TICKET_EVENT_MESSAGE_DELETE  => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_REFERENCE       => array(
                'action' => '$this->actionReference();',
                'status' => SUPPORT_TICKET_STATUS_WAIT),
            SUPPORT_TICKET_EVENT_CLOSE           => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
        ),
        SUPPORT_TICKET_STATUS_MOVED   => array(
            SUPPORT_TICKET_EVENT_UNKNOWN         => array(
                'action' => '$this->actionNone();',
                'status' => SUPPORT_TICKET_STATUS_UNKNOWN),
            SUPPORT_TICKET_EVENT_MESSAGE_VIEW    => array(
                'action' => '$this->actionView();',
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
                'action' => '$this->actionFollowup();',
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
                'action' => '$this->actionView();',
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
                'action' => '$this->actionNew();',
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
     * @param   string      $timestamp  The optional timestamp
     * @param   integer     $id         The optional TicketEvent ID
     * @return  TicketEvent             The TicketEvent object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function TicketEvent(
        $objTicket, $event, $value, $timestamp='', $id=0
    ) {
        $this->__construct(
            $objTicket, $event, $value, $timestamp, $id
        );
    }

    /**
     * Constructor (PHP5)
     *
     * The optional arguments $timestamp and $id *MUST* only be used
     * when creating an object from a database record.
     * See {@link getById()}.
     * @param   mixed       $objTicket  The affected Ticket object
     * @param   integer     $event      The event code
     * @param   integer     $value      The target value
     * @param   string      $timestamp  The optional timestamp
     * @param   integer     $id         The optional TicketEvent ID
     * @return  TicketEvent             The TicketEvent object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function __construct(
        $objTicket, $event, $value, $timestamp='', $id=0
    ) {
        $this->objTicket = $objTicket;
        $this->event     = $event;
        $this->value     = $value;
        $this->timestamp = $timestamp;
        $this->id        = $id;

        // Get the would-be status after processing the event.
        // This may be changed by the action*() methods, if necessary.
        $this->newStatus = $this->getNewTicketStatus();
        // This *SHOULD* never be visible!
        // -- Which means that every state-event combination causing it
        // has to be avoided.
        if ($this->newStatus == SUPPORT_TICKET_EVENT_UNKNOWN) {
echo("TicketEvent::__construct(ticketId=$ticketId, event=$event, value=$value, status=$status, timestamp=$timestamp', id=$id): WARNING: Event is causing an UNKNOWN status!<br />");
        }
    }


    /**
     * Get this TicketEvents' ID
     * @return  integer     The TicketEvent ID
     */
    function getId()
    {
        return $this->Id;
    }

    /**
     * Get the event code from this TicketEvent
     * @return  integer     The event code
     */
    function getEvent()
    {
        return $this->event;
    }
    /**
     * Set the event code for this TicketEvent
     * @param   integer     The event code
    function setEvent($event)
    {
        $this->event = intval($event);
    }
     */

    /**
     * Get the value changed by this TicketEvent
     * @return  string      The changed value
     */
    function getValue()
    {
        return $this->value;
    }
    /**
     * Set the value changed by this TicketEvent
     * @param   string      The changed value
    function setValue($value)
    {
        $this->value = $value;
    }
     */

    /**
     * Get this TicketEvents' timestamp
     * @return  string      The TicketEvent timestamp
     */
    function getTimestamp()
    {
        return $this->timestamp;
    }
    /**
     * Set this TicketEvents' timestamp
     * @param   string      The TicketEvent timestamp
    function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }
     */


    /**
     * Get the current owner ID for the Ticket with the given ID.
     *
     * Note that a person is already considered to be the owner of a
     * Ticket even before he has viewed (and thus OPENed) it after it
     * has been MOVEd.
     * This method may both be called as an object method or statically.
     * @param   integer     $ticketId       The Ticket ID
     * @return  mixed                       The Ticket owner ID on success,
     *                                      false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getCurrentOwnerId($ticketId=0)
    {
        global $objDatabase;
//echo("TicketEvent::getCurrentOwnerName(ticketId=$ticketId): entered<br />");

        if ($ticketId <= 0) {
            $ticketId = $this->objTicket->getId();
            if ($ticketId <= 0) {
echo("TicketEvent::getCurrentOwnerName(ticketId=$ticketId): ERROR: missing or invalid ticket ID '$ticketId'!<br />");
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
echo("TicketEvent::getCurrentOwnerName(ticketId=$ticketId): ERROR: query failed:<br />$query<br />");
            return false;
        }
        if ($objResult->EOF) {
            return false;
        }
        return $objResult->fields['value'];
    }


    /**
     * Get the current Ticket status for the Ticket with the given ID.
     *
     * @static
     * @param   integer     $ticketId       The Ticket ID
     * @return  mixed                       The current Ticket Status
     *                                      on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getTicketStatus($ticketId)
    {

/*

    HEAVY TODO HERE

*/

//echo("TicketEvent::getTicketStatus(ticketId=$ticketId): entered<br />");
        global $objDatabase;

        if ($ticketId <= 0) {
echo("TicketEvent::getTicketStatus(ticketId=$ticketId): ERROR: missing or invalid ticket ID '$ticketId'!<br />");
            return false;
        }

        $query = "
            SELECT event, value
              FROM ".DBPREFIX."module_support_ticket_event
             WHERE ticket_id=$ticketId
               AND event IN
                   (".SUPPORT_TICKET_EVENT_UNKNOWN.",
                    ".SUPPORT_TICKET_EVENT_MESSAGE_VIEW.",
                    ".SUPPORT_TICKET_EVENT_CHANGE_OWNER.",
                    ".SUPPORT_TICKET_EVENT_REPLY.",
                    ".SUPPORT_TICKET_EVENT_MESSAGE_NEW.",
                    ".SUPPORT_TICKET_EVENT_MESSAGE_DELETE.",
                    ".SUPPORT_TICKET_EVENT_CLOSE.")
          ORDER BY id DESC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
echo("TicketEvent::getTicketStatus(ticketId=$ticketId): ERROR: query failed:<br />$query<br />");
            return false;
        }

        // Look up the status for the current User
        $userId = Auth::getUserId();
        if ($userId <= 0) {
echo("TicketEvent::getTicketStatus(ticketId=$ticketId): ERROR: missing or invalid user ID '$userId'!<br />");
            return false;
        }
        $ticketStatus = SUPPORT_TICKET_STATUS_NEW;
        $isOwner = false;
        $arrViews = array();

        while (!$objResult->EOF) {
            $event = $objResult->fields['event'];
            $value = $objResult->fields['value'];
            switch ($event) {
              case SUPPORT_TICKET_EVENT_CHANGE_OWNER:
                // True iff the owner was changed to the current User
                $isOwner = ($value == $ownerId);
                $ticketStatus = SUPPORT_TICKET_STATUS_MOVED;
                break;
              case SUPPORT_TICKET_EVENT_MESSAGE_NEW:
                // A new Message was inserted into the system.
                $arrViews[$value] = true;
                $ticketStatus = SUPPORT_TICKET_STATUS_NEW;
                break;
              case SUPPORT_TICKET_EVENT_MESSAGE_VIEW:
                // The current User took a look at that Message iff
                // she was the owner of the Ticket at that time.
                if ($isOwner) {
                    unset ($arrViews[$value]);
                    if (count($arrViews) == 0 &&
                        (   $ticketStatus == SUPPORT_TICKET_STATUS_UNKNOWN
                         || $ticketStatus == SUPPORT_TICKET_STATUS_NEW)
                    ) {
                        $ticketStatus = SUPPORT_TICKET_STATUS_OPEN;
                    }
                }
                break;
              case SUPPORT_TICKET_EVENT_MESSAGE_DELETE:
                // The Ticket has been deleted.
                // Note that this applies to all Users!
                unset ($arrViews[$value]);
                if (count($arrViews) == 0) {
                    $ticketStatus = SUPPORT_TICKET_STATUS_OPEN;
                }
                break;
              case SUPPORT_TICKET_EVENT_UNKNOWN:
                $ticketStatus = SUPPORT_TICKET_STATUS_UNKNOWN;
                break;
              case SUPPORT_TICKET_EVENT_REPLY:
                $ticketStatus = SUPPORT_TICKET_STATUS_WAIT;
                break;
              case SUPPORT_TICKET_EVENT_CLOSE:
                $ticketStatus = SUPPORT_TICKET_STATUS_CLOSED;
                break;
              default:
echo("TicketEvent::getTicketStatus(ticketId=$ticketId): ERROR CODE 0-NMI (Multiplication by zero)<br />");
                return false;
            }
            $objResult->MoveNext();
        }
        return $ticketStatus;
    }


    /**
     * Get the current Message status for the Ticket and Message
     * with the given IDs.
     *
     * @static
     * @param   integer     $ticketId       The Ticket ID
     * @param   integer     $messageId      The Message ID
     * @return  mixed                       The current Message Status
     *                                      on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getMessageStatus($ticketId, $messageId)
    {
//echo("TicketEvent::getMessageStatus(ticketId=$ticketId, messageId=$messageId): entered<br />");
        global $objDatabase;

        if ($ticketId <= 0) {
echo("TicketEvent::getMessageStatus(ticketId=$ticketId, messageId=$messageId): ERROR: missing or invalid ticket ID '$ticketId'!<br />");
            return false;
        }
        if ($messageId <= 0) {
echo("TicketEvent::getMessageStatus(ticketId=$ticketId, messageId=$messageId): ERROR: missing or invalid message ID '$messageId'!<br />");
            return false;
        }

        $query = "
            SELECT event, value
              FROM ".DBPREFIX."module_support_ticket_event
             WHERE ticket_id=$ticketId
               AND event IN
                   (".SUPPORT_TICKET_EVENT_CHANGE_OWNER.",
                    ".SUPPORT_TICKET_EVENT_MESSAGE_NEW.",
                    ".SUPPORT_TICKET_EVENT_MESSAGE_VIEW.",
                    ".SUPPORT_TICKET_EVENT_MESSAGE_DELETE.")
          ORDER BY id ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
echo("TicketEvent::getMessageStatus(ticketId=$ticketId, messageId=$messageId): ERROR: query failed:<br />$query<br />");
            return false;
        }

        // Look up the status for the current User
        $userId = Auth::getUserId();
        if ($userId <= 0) {
echo("TicketEvent::getMessageStatus(ticketId=$ticketId, messageId=$messageId): ERROR: missing or invalid user ID '$userId'!<br />");
            return false;
        }
        $messageStatus = SUPPORT_MESSAGE_STATUS_UNKNOWN;
        $isOwner = false;

        while (!$objResult->EOF) {
            $event = $objResult->fields['event'];
            $value = $objResult->fields['value'];
            switch ($event) {
              case SUPPORT_TICKET_EVENT_CHANGE_OWNER:
                // True iff the owner was changed to the current User
                $isOwner = ($value == $ownerId);
                break;
              case SUPPORT_TICKET_EVENT_MESSAGE_NEW:
                // If the Message IDs match, the Message was inserted
                // into the system.
                if ($messageId == $value) {
                    $messageStatus = SUPPORT_MESSAGE_STATUS_NEW;
                }
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
                if ($messageId == $value) {
                    $messageStatus = SUPPORT_MESSAGE_STATUS_DELETED;
                }
                break;
              default:
echo("TicketEvent::getMessageStatus(ticketId=$ticketId, messageId=$messageId): ERROR CODE 8342A-CRX (Engine temperature is above the measurement range)<br />");
                return false;
            }
            $objResult->MoveNext();
        }
        return $messageStatus;
    }


    /**
     * Returns true iff the Ticket has new Messages associated with it.
     *
     * This method may both be called as an object method or statically.
     * @param   integer     $ticketId       The Ticket ID
     * @return  mixed                       The unread Message count on success,
     *                                      false otherwise
     * @global  mixed       $objDatabase    Database object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function ticketHasNewMessages($ticketId=0)
    {
        global $objDatabase;
//echo("TicketEvent::ticketHasNewMessages(ticketId=$ticketId): entered<br />");

        if ($ticketId <= 0) {
            $ticketId = $this->objTicket->getId();
            if ($ticketId <= 0) {
echo("TicketEvent::ticketHasNewMessages(ticketId=$ticketId): ERROR: missing or invalid ticket ID '$ticketId'!<br />");
                return false;
            }
        }
        // The value field contains:
        // - SUPPORT_TICKET_EVENT_CHANGE_OWNER: The new owner ID
        // - SUPPORT_TICKET_EVENT_MESSAGE_VIEW: The Message ID
        $query = "
            SELECT event, value
              FROM ".DBPREFIX."module_support_ticket_event
             WHERE ticket_id=$ticketId
               AND (   event=".SUPPORT_TICKET_EVENT_MESSAGE_NEW."
                    OR event=".SUPPORT_TICKET_EVENT_MESSAGE_VIEW."
                    OR event=".SUPPORT_TICKET_EVENT_CHANGE_OWNER."
                    OR event=".SUPPORT_TICKET_EVENT_REPLY.")
          ORDER BY id ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
echo("TicketEvent::ticketHasNewMessages(ticketId=$ticketId): ERROR: query failed:<br />$query<br />");
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
                // True iff the owner was changed to the current User
                $isOwner = ($value == $userId);
                break;
              case SUPPORT_TICKET_EVENT_MESSAGE_NEW:
                // Tag new Message IDs in the array
                $arrViews[$value] == true;
                break;
              case SUPPORT_TICKET_EVENT_MESSAGE_VIEW:
                // The current User took a look at that Message iff
                // she was the owner at the time of reading!
                // Remove the Message ID from the array.
                if ($isOwner) unset($arrViews[$value]);
                break;
              case SUPPORT_TICKET_EVENT_REPLY:
                // The Message was written by herself
                // as a reply to a Ticket
                if ($isOwner) unset($arrViews[$value]);
                break;
              default:
echo("TicketEvent::ticketHasNewMessages(): ERROR CODE 72-84268-GFX (DirectX V12.8258.001 failed to load)<br />");
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
     * Ticket or Message update.  Updates to Tickets or Messages *MUST NOT*
     * have been successfully made for this TicketEvent!
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function delete()
    {
        global $objDatabase;
//echo("Debug: TicketEvent::delete(): entered<br />");

        if (!$this->id > 0) {
echo("TicketEvent::delete(): ERROR: missing or illegal ID ($this->id)!<br />");
            return false;
        }
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_support_ticket_event
             WHERE id=$this->id
        ");
        if (!$objResult) {
echo("TicketEvent::delete(): Error: Failed to delete the TicketEvent with ID $this->id from the database!<br />");
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
     * @static
     * @param       integer     $ticketId       The Ticket ID
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function deleteByTicketId($ticketId)
    {
        global $objDatabase;
//echo("Debug: TicketEvent::delete(): entered<br />");

        if (!$ticketId > 0) {
echo("TicketEvent::deleteByTicketId(ticketId=$ticketId): ERROR: missing or illegal Ticket ID!<br />");
            return false;
        }
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_support_ticket_event
             WHERE ticket_id=$ticketId
        ");
        if (!$objResult) {
echo("TicketEvent::deleteByTicketId(ticketId=$ticketId): Error: Failed to delete the TicketEvent records from the database<br />");
            return false;
        }
        return true;
    }


    /**
     * Stores this TicketEvent in the database.
     *
     * Either updates (id > 0) or inserts (id == 0) the object.
     * This method is disabled on purpose.  Do not enable it!
     * Use insert() instead.
     * @return      boolean     True on success, false otherwise
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
    function store()
    {
        if ($this->id > 0) {
echo("TicketEvent::store(): WARNING: someone is trying to UPDATE an TicketEvent record! -- Bailing out<br />");
            return false;
        }
        return $this->insert();
    }
     */


    /**
     * Update this TicketEvent in the database. -- NOT IMPLEMENTED
     *
     * This method is disabled on purpose.  Do not enable it!
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
    function update()
    {
        global $objDatabase;

        $query = "
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
//echo("TicketEvent::update(): done<br />");
        return true;
    }
     */


    /**
     * Insert this TicketEvent into the database.
     *
     * Note that the timestamp field will be set to the current date and time
     * on INSERTing the record, by definition of the database table.
     * This method *MUST* call refreshTimestamp() after a successful INSERT.
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function insert()
    {
        global $objDatabase;

        $ticketId = $this->objTicket->getId();
        $query = "
            INSERT INTO ".DBPREFIX."module_support_ticket_event (
                   ticket_id, `event`, `value`
            ) VALUES (
                   $ticketId,
                   $this->event,
                   $this->value
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $this->id = $objDatabase->Insert_ID();
        // Update the object with the actual timestamp
//echo("TicketEvent::insert(): done<br />");
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
echo("TicketEvent::refreshTimestamp(): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
echo("TicketEvent::refreshTimestamp(): objResult: '$objResult'<br />");
        if (!$objResult) {
echo("TicketEvent::refreshTimestamp(): query failed, objResult: '$objResult', count: ".$objResult->RecordCount()."<br />");
            return false;
        }
        if ($objResult->RecordCount() == 0) {
echo("TicketEvent::refreshTimestamp(): no result: ".$objResult->RecordCount()."<br />");
            return false;
        }
        $this->timestamp = $objResult->fields('timestamp');
        return true;
    }


    /**
     * Select an TicketEvent by ID from the database.
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
echo("TicketEvent::getById($id): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
echo("TicketEvent::getById($id): objResult: '$objResult'<br />");
        if (!$objResult) {
echo("TicketEvent::getById($id): query failed, objResult: '$objResult', count: ".$objResult->RecordCount()."<br />");
            return false;
        }
        if ($objResult->RecordCount() == 0) {
echo("TicketEvent::getById($id): no result: ".$objResult->RecordCount()."<br />");
            return false;
        }
        $objTicketEvent = new TicketEvent(
            Ticket::getById($objResult->fields('ticket_id')),
            $objResult->fields('event'),
            $objResult->fields('value'),
            $objResult->fields('timestamp'),
            $objResult->fields('id')
        );
        return $objTicketEvent;
    }


    /**
     * Returns an array of TicketEvent objects related to a certain
     * Ticket ID from the database.
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
     * @param       string      $value          The desired field value, or zero
     * @param       string      $order          The optional sorting order
     * @param       integer     $offset         The optional offset
     * @param       integer     $limit          The optional limit
     * @return      array                       The array of TicketEvent objects
     *                                          on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @global      array       $_CONFIG        Global configuration array
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getTicketEventArray(
        $ticketId, $event, $value,
        $order="'timestamp' DESC", $offset=0, $limit=0
    ) {
        global $objDatabase, $_CONFIG;

        $limit = ($limit ? $limit : $_CONFIG['corePagingLimit']);
        $query = "
            SELECT id
              FROM ".DBPREFIX."module_support_ticket_event
             WHERE 1
              ".($ticketId  ? " AND ticket_id=$ticketId" : '')."
              ".($event     ? " AND event=$event"        : '')."
              ".($value     ? " AND value='$value'"      : '')."
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
     * Finds out what action to take, calls appropriate action*() methods
     * if necessary,
     * and returns the new Ticket status, which in fact may be identical
     * to the old one.
     * @return  mixed               The new Ticket status on success,
     *                              false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function process()
    {
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
echo("TicketEvent::process(): INFO: Action returned '$actionResult'<br />");
        if ($actionResult) {
echo("TicketEvent::process(): INFO: Returning new status $this->newStatus<br />");
            // Return the new Ticket status
            return $this->newStatus;
        }
echo("TicketEvent::process(): INFO: returning false<br />");
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
        return $this->arrSea[$this->objTicket->getStatus()][$this->event]['action'];
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
//echo("TicketEvent::getNewTicketStatus(): State-Event matrix: ");var_export($this->arrSea);echo("<br />");
//echo("TicketEvent::getNewTicketStatus(): Ticket status: ".$this->objTicket->getStatus().", event: $this->event<br />");
        return $this->arrSea[$this->objTicket->getStatus()][$this->event]['status'];
    }


    /**
     * Take no Action on this Ticket.
     *
     * This is all about not doing anything.
     * @return  boolean         True.  Always.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function actionNone()
    {
        return true;
    }


    /**
     * Assign this Ticket to the person reading it.
     *
     * This method is to be called for Tickets with status NEW or MOVED,
     * whenever a READ event occurs.
     * The Ticket is assigned to the person reading it only if
     * - The Ticket hasn't been assigned before (its status is NEW), or if
     * - The Ticket has been assigned (MOVEd) to the person reading it now,
     *   that is, the person currently logged in. See
     *   {@link core/auth.class.php}.
     * @return  boolean     True on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function actionAssign()
    {
        // Make sure that there is a valid owner to be set
        if (!$this->value > 0) {
echo("TicketEvent::actionAssign(): ERROR: No or invalid target owner ID ($this->value)!<br />");
            return false;
        }
        $ticketStatus = $this->objTicket->getStatus();
        // If the status is NEW, just insert it and go.
        if ($ticketStatus == SUPPORT_TICKET_STATUS_NEW) {
            return $this->insert();
        }
        // If the status is MOVED, we have to verify that the current user
        // and the prospective owner are identical.
        if ($ticketStatus == SUPPORT_TICKET_STATUS_MOVED) {
            $ownerId = $this->getCurrentOwnerId();
            if (!$ownerId > 0) {
echo("TicketEvent::actionAssign(): ERROR: No or invalid current owner ID ($ownerId)!<br />");
            }
            if (Auth::isCurrentUserId($ownerId)) {
                return $this->insert();
            }
echo("TicketEvent::actionAssign(): ERROR: Current User is not the owner of the Ticket!<br />");
        }
        // In any other case, the assignment isn't allowed.
echo("TicketEvent::actionAssign(): ERROR: The Ticket is not in MOVED state ($ticketStatus)!<br />");
        return false;
    }


    /**
     * Close a Ticket.
     *
     * This method is to be called for Tickets with status OPEN,
     * CLOSE event occurs.
     * The event is only allowed if the current user
     * is the owner of the Ticket.
     * @return  boolean     True on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function actionClose()
    {
        $ticketStatus = $this->objTicket->getStatus();
        if ($ticketStatus != SUPPORT_TICKET_STATUS_OPEN) {
echo("TicketEvent::actionClose(): ERROR: The Ticket isn't in state OPEN ($ticketStatus)!<br />");
            return false;
        }
        $ownerId = $this->getCurrentOwnerId();
        if (!$ownerId > 0) {
echo("TicketEvent::actionClose(): ERROR: No or invalid owner ID ($ownerId)!<br />");
        }
        if (Auth::isCurrentUserId($ownerId)) {
            return $this->insert();
        }
        // Everything else is wrong.
echo("TicketEvent::actionClose(): ERROR: The current User isn't the owner of the Ticket ($ownerId)!<br />");
        return false;
    }


    /**
     * S    Modify the Ticket Support Category.  This is only possible for the
     *      current owner of the Ticket. (supportCategoryId)
     */
    function actionSupportCategoryChange()
    {
        if (!$this->value > 0) {
echo("TicketEvent::actionSupportCategoryChange(): ERROR: No or invalid target Support Category ID $this->value!<br />");
            return false;
        }
        $ticketStatus = $this->objTicket->getStatus();
        if ($ticketStatus != SUPPORT_TICKET_STATUS_OPEN) {
echo("TicketEvent::actionSupportCategoryChange(): ERROR: The Ticket isn't in state OPEN ($ticketStatus)!<br />");
            return false;
        }
        $ownerId = $this->getCurrentOwnerId();
        if (!$ownerId > 0) {
echo("TicketEvent::actionSupportCategoryChange(): ERROR: No or invalid owner ID ($ownerId)!<br />");
        }
        if (Auth::isCurrentUserId($ownerId)) {
            return $this->insert();
        }
        // Everything else is wrong.
echo("TicketEvent::actionSupportCategoryChange(): ERROR: The current User isn't the owner of the Ticket ($ownerId)!<br />");
        return false;
    }


    /**
     * R    Reply to the Ticket.  Store and send the reply, update the KB and all
     *      affected tables. (messageId)
     * This is only possible for the
     *      current owner of the Ticket, and if the Ticket is in either
     *  OPEN or WAIT state.
     */
    function actionReply()
    {
        if (!$this->value > 0) {
echo("TicketEvent::actionReply(): ERROR: No or invalid Message ID $this->value!<br />");
            return false;
        }
        $ticketStatus = $this->objTicket->getStatus();
        if (   $ticketStatus != SUPPORT_TICKET_STATUS_OPEN
            && $ticketStatus != SUPPORT_TICKET_STATUS_WAIT
        ) {
echo("TicketEvent::actionReply(): ERROR: The Ticket isn't in state OPEN or WAIT ($ticketStatus  )!<br />");
            return false;
        }
        $ownerId = $this->getCurrentOwnerId();
        if ($ownerId <= 0) {
echo("TicketEvent::actionReply(): ERROR: No or invalid owner ID ($ownerId)!<br />");
        }
        if (Auth::isCurrentUserId($ownerId)) {
            return $this->insert();
        }
        // Everything else is wrong.
echo("TicketEvent::actionReply(): ERROR: The current User isn't the owner of the Ticket ($ownerId)!<br />");
        return false;
    }


    /**
     * Transfer this Ticket to a User
     *
     * This method is to be called for Tickets with status OPEN or MOVED,
     * whenever a CHANGE_PERSON event occurs.  The $value property of the
     * TicketEvent object contains the target User ID.
     * Two cases are handled:
     * - Take over a Ticket.  Anyone can take over Tickets in MOVED state,
     *   which they will keep until viewed by the new owner.
     *   This is intended to enable others to process your Tickets when
     *   you are unable to.  (userId)
     * - Delegate a Ticket to someone else.  This is only allowed, however,
     *   for the owner of the Ticket.
     * @return  boolean     True on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function actionTransfer()
    {
        if ($this->value <= 0) {
echo("TicketEvent::actionTransfer(): ERROR: No or invalid target owner ID $this->value!<br />");
            return false;
        }
        $userId  = Auth::getUserId();
        if ($userId <= 0) {
echo("TicketEvent::actionTransfer(): ERROR: No or invalid user ID ($userId)!<br />");
            return false;
        }

        // Get the current Ticket status
        $ticketStatus = $this->objTicket->getStatus();

        if ($ticketStatus == SUPPORT_TICKET_STATUS_NEW) {
echo("TicketEvent::actionTransfer(): INFO: Committing ownership of new Ticket.<br />");
            // NEW state.  The current User becomes the owner of the Ticket.
            return $this->insert();
        }

        // Other cases imply that there is an owner already.
        $ownerId = $this->objTicket->getOwnerId();
        if ($ownerId <= 0) {
echo("TicketEvent::actionTransfer(): ERROR: No or invalid owner ID ($ownerId)!<br />");
            return false;
        }

        if ($ticketStatus == SUPPORT_TICKET_STATUS_OPEN) {
            // OPEN state.  The owner may delegate the Ticket to anyone
            // except herself; so:
            // - The current owner ID *MUST* be equal to the
            //   current User ID, and...
            if ($ownerId != $userId) {
echo("TicketEvent::actionTransfer(): ERROR: The current User (ID$userId) may not transfer the Ticket to User ID $this->value, as he is not the owner (ID $ownerId).<br />");
                return false;
            }
            // - ...the target owner ID *MUST* be different
            //   from the current User ID.
            if ($this->value == $userId) {
echo("TicketEvent::actionTransfer(): INFO: Target owner ID ($this->value) is equal to current User ID ($userId) already.<br />");
                return false;
            }
            $this->insert();
            return true;
        }
        if ($ticketStatus == SUPPORT_TICKET_STATUS_MOVED) {
            // MOVED state.  Anyone can take over the Ticket.
            // The target owner ID in $value *MUST* be equal to the current
            // User ID.
            if ($this->value != $userId) {
echo("TicketEvent::actionTransfer(): ERROR: The current User (ID $userId) must not transfer the Ticket to someone other than himself ($this->value)!<br />");
                return false;
            }
            // It doesn't make sense to transfer the Ticket
            // if the current User is the current owner already, however.
            if ($ownerId == $userId) {
echo("TicketEvent::actionTransfer(): WARNING: The current owner (ID $ownerId) is equal to current User ID ($userId) already.<br />");
                // return true anyway, so that the Ticket status is updated.
                return true;
            }
            $this->insert();
            return true;
        }
echo("TicketEvent::actionTransfer(): ERROR: The Ticket state isn't neither MOVED nor OPEN ($ticketStatus)!<br />");
        return false;
    }


    /**
     * A Message associated with the Ticket is being viewed.
     *
     * If the Ticket has no more new Messages (unread by the current User),
     * this method returns true, and {@link process()} will return
     * the new status, so that the Ticket can be updated.
     * Also see {@link ticketHasNewMessages()}, which does the work, but
     * returns the negated result.
     * @return  boolean             True if the Ticket has no new Messages,
     *                              false otherwise.
     */
    function actionView()
    {
echo("TicketEvent::actionView(): INFO: entered.  TicketEvent: ");var_export($this);echo("<br />");

echo("TicketEvent::actionView(): INFO: trying to transfer Ticket.<br />");
        $transferResult = $this->actionTransfer();
echo("TicketEvent::actionView(): INFO: transfer attempt result: $transferResult<br />");

        // Who's the current owner?
        $ticketOwnerId = $this->objTicket->getOwnerId();
        // The transfer attempt above may have failed, despite the Ticket
        // being NEW
        if ($ticketOwnerId <= 0) {
echo("TicketEvent::actionView(): ERROR: Transfer of NEW Ticket obviously failed!<br />");
            return false;
        }
        $userId = Auth::getUserId();
        if ($userId <= 0) {
echo("TicketEvent::actionView(): ERROR: No or illegal User ID ($userId)!<br />");
            return false;
        }
        // The owner is someone else?
        if ($userId != $ticketOwnerId) {
echo("TicketEvent::actionView(): INFO: current User is NOT owner of the Ticket.<br />");
            // Reject
            return false;
        }

        $messageStatus =
            $this->getMessageStatus($this->objTicket->getId(), $this->value);
        // Add the record only if this Message is unREAD by this User.
        // Note that any views while the Message is marked as DELETED
        // are not logged either!
        if ($messageStatus == SUPPORT_MESSAGE_STATUS_NEW) {
echo("TicketEvent::actionView(): INFO: marking Message (ID $this->value) as READ by the current User (ID $ is NOT owner of the Ticket.<br />");
            // All conditions are met; mark this Message as READ by
            // the current User (which happens to be the owner, too).
            return $this->insert();
        }
        // Otherwise, fail.
        return false;
    }


    /**
     * X    Modify any property of the Ticket other than the Support Category,
     *      or the owner.  [TODO: Explain which!]
     */
    function actionOtherChange()
    {
        if (!$this->value > 0) {
echo("TicketEvent::actionOtherChange(): ERROR: No or invalid target ID $this->value!<br />");
            return false;
        }
        $ticketStatus = $this->objTicket->getStatus();
        if ($ticketStatus != SUPPORT_TICKET_STATUS_OPEN) {
echo("TicketEvent::actionOtherChange(): ERROR: The Ticket isn't in state OPEN ($ticketStatus)!<br />");
            return false;
        }
        $ownerId = $this->getCurrentOwnerId();
        if (!$ownerId > 0) {
echo("TicketEvent::actionOtherChange(): ERROR: No or invalid owner ID ($ownerId)!<br />");
        }
        if (Auth::isCurrentUserId($ownerId)) {
            return $this->insert();
        }
        // Everything else is wrong.
echo("TicketEvent::actionOtherChange(): ERROR: The current User isn't the owner of the Ticket ($ownerId)!<br />");
        return false;
    }


    /**
     * F    Add a followup Message to an existing Ticket (messageId)
     */
    function actionFollowup() {
        if (!$this->value > 0) {
echo("TicketEvent::actionFollowup(): ERROR: No or invalid message ID $this->value!<br />");
            return false;
        }
        $ticketStatus = $this->objTicket->getStatus();
        if ($ticketStatus == SUPPORT_TICKET_STATUS_CLOSED) {
echo("TicketEvent::actionFollowup(): ERROR: The Ticket is already CLOSED ($ticketStatus)!<br />");
            return false;
        }
        return $this->insert();
    }


    /**
     * Open a new Ticket with a reference to the old one.
     *
     * The new Ticket must already be present in the system. (old TicketId)
     */
    function actionNew() {
        if (!$this->value > 0) {
echo("TicketEvent::actionOtherChange(): ERROR: No or invalid old Ticket ID $this->value!<br />");
            return false;
        }
        $ticketStatus = $this->objTicket->getStatus();
        if ($ticketStatus != SUPPORT_TICKET_STATUS_CLOSED) {
echo("TicketEvent::actionOtherChange(): ERROR: The Ticket isn't in state CLOSED ($ticketStatus)!<br />");
            return false;
        }
        return $this->insert();
    }


    /**
     * Verfify that the current User is the owner of the Ticket
     *
     * UNUSED!
     *
     * @return  boolean             True if the IDs are equal, false otherwise
    function currentUserIsOwner() {
        if (!$_SESSION['auth']['userid'] > 0) {
echo("TicketEvent::currentUserIsOwner(): ERROR: No or invalid current User ID $this->value!<br />");
            return false;
        }
        $ownerId = $this->getOwnerId();
        if (!$ownerId > 0) {
echo("TicketEvent::currentUserIsOwner(): ERROR: No or invalid owner ID $ownerId found -- this Ticket cannot be delegated!<br />");
            return false;
        }
        if ($ownerId == $_SESSION['auth']['userid']) {
            return true;
        }
        return false;
    }
     */
}
