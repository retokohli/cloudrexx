<?php

/**
 * Support System Common
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */

// Ticket status constant values
// UNKNOWN: The Ticket was found in a state other than those listed
// here, and has been reset to unknown state.  Someone needs to
// adjust its state manually now.
define('SUPPORT_TICKET_STATUS_UNKNOWN', 0);
// NEW: The Ticket has been opened, but noone has seen it yet,
// or a followup message was received while it was not in UNKNOWN
// (or CLOSED) state.
define('SUPPORT_TICKET_STATUS_NEW',     1);
// OPEN: The Ticket is open, someone has already taken a look at it.
define('SUPPORT_TICKET_STATUS_OPEN',    2);
// WAIT: The Ticket has been answered.  It's now waiting for a
// followup message from the originator.
define('SUPPORT_TICKET_STATUS_WAIT',    3);
// MOVED: The Ticket has been moved from one person to another,
// It's now waiting for the other person to review it.
// Then, it must be shown along with the preceding movements.
// This is very similar to the NEW status.
define('SUPPORT_TICKET_STATUS_MOVED',   4);
// CLOSED: The Ticket has been replied and is considered to be
// satisfactorily answered.
define('SUPPORT_TICKET_STATUS_CLOSED',  5);
// More to come...
//define('SUPPORT_TICKET_STATUS_', 0);
// Total number.  Keep this up to date!
define('SUPPORT_TICKET_STATUS_COUNT',   6);

// Ticket Event constant values
// UNKNOWN: Some irregular Ticket event has occurred,
// and the Ticket status should be set to UNKNOWN now, too.
// Someone needs to take action now.
define('SUPPORT_TICKET_EVENT_UNKNOWN',         0);
// MESSAGE_VIEW: Someone took a look at a Message associated
// with this Ticket.
// The Ticket status is set to OPEN after that if no unread
// Messages are associated with the Ticket anymore.
define('SUPPORT_TICKET_EVENT_MESSAGE_VIEW',    1);
// CHANGE_CATEGORY: Someone changed the Support Category.
// Upon changing the Support Category of a new or open Ticket,
// its status must be set to OPEN.
// Tickets with status WAIT or CLOSED should not be changed!
define('SUPPORT_TICKET_EVENT_CHANGE_CATEGORY', 2);
// CHANGE_PERSON: Someone moved this Ticket to another person.
// Upon changing the Person responsible for a new or open Ticket,
// its status must be set to WAIT, and the other person must
// be notified!
// The Ticket status is WAIT until the other person views it.
// Tickets with status WAIT or CLOSED should not be changed!
define('SUPPORT_TICKET_EVENT_CHANGE_OWNER',    3);
// CHANGE_OTHER: Any other changes that don't affect the Ticket's state.
define('SUPPORT_TICKET_EVENT_CHANGE_OTHER',    4);
// REPLY: Someone has sent a reply to the Ticket.
// Tickets that have been replied to must be set to status WAIT!
define('SUPPORT_TICKET_EVENT_REPLY',           5);
// MESSAGE_NEW: The Customer has sent another message regarding his
// Ticket.  This will usually lead to the Ticket state being reset
// to new, except when it has already been closed, or if it's in
// UNKNOWN state.  In the former case, a new Ticket will be created.
// In the latter case, the status will be left UNKNOWN.
define('SUPPORT_TICKET_EVENT_MESSAGE_NEW',     6);
// MESSAGE_DELETE: The User has marked the Message as deleted.
// This will not change the Ticket status, but the Message
// status field *MUST* be updated.
// Note that Message status are restored whenever a Ticket has been MOVEd
// to and accepted by another owner.  If the new owner never owned the
// Ticket before, all MESSAGE status will be reset to NEW.  If the Ticket
// was owned by her before, all Messages she has viewed
// before will be marked accordingly.  Messages marked as DELETED, however,
// will remain in that state until they are restored.
define('SUPPORT_TICKET_EVENT_MESSAGE_DELETE',  7);
// REFERENCE: This Ticket references some other, probably older and closed,
// Ticket.  This Event doesn't require any changes to both Tickets,
// it simply creates a TicketEvent record.
define('SUPPORT_TICKET_EVENT_REFERENCE',       8);
// CLOSE: Someone has declared this Ticket closed.
// The Ticket state must now be CLOSED, and the Ticket should
// not be MOVEd or CHANGEd anymore!
define('SUPPORT_TICKET_EVENT_CLOSE',           9);
// Total number.  Keep this up to date!
define('SUPPORT_TICKET_EVENT_COUNT',          10);

// Ticket source constants
// UNKNOWN: It is not known how this Ticket got here.
define('SUPPORT_TICKET_SOURCE_UNKNOWN',        0);
// EMAIL: The Ticket has been sent by e-mail.
define('SUPPORT_TICKET_SOURCE_EMAIL',          1);
// WEB: The Ticket has been posted on the web site.
define('SUPPORT_TICKET_SOURCE_WEB',            2);
// SYSTEM: The Ticket has been posted from the administration page.
define('SUPPORT_TICKET_SOURCE_SYSTEM',         3);
// Total number.  Keep this up to date!
define('SUPPORT_TICKET_SOURCE_COUNT',          4);

// Message status constants
// UNKNOWN: The Message needs to be read again to return to READ status.
define('SUPPORT_MESSAGE_STATUS_UNKNOWN', 0);
// NEW: The Message has not been viewed by the owner.  It needs to be
// read to get to READ status.
define('SUPPORT_MESSAGE_STATUS_NEW',     1);
// READ: The Message has been read by the owner.
define('SUPPORT_MESSAGE_STATUS_READ',    2);
// DELETED: The Message has been marked as deleted.  It won't be removed
// from the system until the associated Ticket is deleted, however.
define('SUPPORT_MESSAGE_STATUS_DELETED', 3);
// Total number.  Keep this up to date!
define('SUPPORT_MESSAGE_STATUS_COUNT',   4);


/**
 * Common functions and methods used by both front- and backend.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */
class SupportCommon
{
    /**
     * Constructor (PHP4)
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     * @see         __construct()
     */
    function SupportCommon()
    {
        $this->__construct();
    }

    /**
     * Constructor (PHP5)
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     */
    function __construct()
    {
    }
}

?>
