<?php

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
  source              tinyint(2)   unsigned NOT NULL default 0,
  email               varchar(255)          NOT NULL,
  `timestamp`         timestamp             NOT NULL default current_timestamp,
  PRIMARY KEY (id),
  KEY support_category_id (support_category_id),
  KEY language_id         (language_id),
  KEY source              (source),
  KEY email               (email),
  KEY `timestamp`         (`timestamp`)
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
     * Timestamp
     *
     * From table modules_support_ticket
     * @var string
     */
    var $timestamp;


    /**
     * Constructor (PHP4)
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
if (MY_DEBUG) { echo("Ticket::__construct(email=$email, source=$source, supportCategoryId=$supportCategoryId, languageId=$languageId, timestamp=$timestamp, id=$id): INFO: Made Ticket: ");var_export($this);echo("<br />"); }
    }


    /**
     * Get this Tickets' ID
     * @return  integer     The Ticket ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getId()
    {
        return $this->id;
    }

    /**
     * Get this Tickets' e-mail address
     * @return  string      The Ticket e-mail address
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getEmail()
    {
        return $this->email;
    }

    /**
     * Get this Tickets' source
     * @return  integer     The Ticket source
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getSource()
    {
        return $this->source;
    }

    /**
     * Get this Tickets' SupportCategory ID
     * @return  integer     The Ticket SupportCategory ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getSupportCategoryId()
    {
        return $this->supportCategoryId;
    }

    /**
     * Get this Tickets' language ID
     * @return  integer     The Ticket language ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getLanguageId()
    {
        return $this->languageId;
    }

    /**
     * Get this Tickets' timestamp
     * @return  string      The Ticket timestamp
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getTimestamp()
    {
        return $this->timestamp;
    }


    /**
     * Get this Tickets' status as a string
     * @return  string                              The Ticket status string
     * @global  array       $arrTicketStatusString  Ticket status strings
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getStatusString()
    {
        global $arrTicketStatusString;

        $status = TicketEvent::getTicketStatus($this->id);
if (MY_DEBUG) echo("getStatusString(): INFO: status of Ticket (ID $this->id) is '$status'.<br />");
        return $arrTicketStatusString[$status];
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
//if (MY_DEBUG) echo("Debug: Ticket::delete(): entered<br />");

        if (!$this->id) {
if (MY_DEBUG) echo("Ticket::delete(): Error: This Ticket is missing the Ticket ID<br />");
            return false;
        }
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_support_ticket
             WHERE id=$this->id
        ");
        if (!$objResult) {
if (MY_DEBUG) echo("Ticket::delete(): ERROR: Failed to delete the Ticket with ID $this->id from the database!<br />");
            return false;
        }
        // delete associated records in Messages and TicketEvents tables
        if (!Message::deleteByTicketId($this->id)) {
if (MY_DEBUG) echo("Ticket::delete(): Error: Failed to delete Messages associated with Ticket ID $this->id from the database!<br />");
            return false;
        }
        if (!TicketEvent::deleteByTicketId($this->id)) {
if (MY_DEBUG) echo("Ticket::delete(): Error: Failed to delete TicketEvents associated with Ticket ID $this->id from the database!<br />");
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
                   source=$this->source,
                   language_id=$this->languageId,
*/
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
//if (MY_DEBUG) echo("Ticket::update(): done<br />");
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
                   '".contrexx_addslashes($this->email)."',
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

//if (MY_DEBUG) echo("Ticket::insert(): done<br />");
        return $this->refreshTimestamp();
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
if (MY_DEBUG) echo("Ticket::refreshTimestamp(): ID: $this->id, query: $query<br />");
// TODO: Here, ADODB shoots its foot:
        $objResult = $objDatabase->Execute($query);
if (MY_DEBUG) echo("Ticket::refreshTimestamp(): objResult: '$objResult'<br />");
        if (!$objResult) {
if (MY_DEBUG) echo("Ticket::refreshTimestamp(): query failed, objResult: '$objResult', count: ".$objResult->RecordCount()."<br />");
            return false;
        }
        if ($objResult->RecordCount() == 0) {
if (MY_DEBUG) echo("Ticket::refreshTimestamp(): no result: ".$objResult->RecordCount()."<br />");
            return false;
        }
        $this->timestamp = contrexx_stripslashes($objResult->fields['timestamp']);
if (MY_DEBUG) echo("Ticket::refreshTimestamp(): done!<br />");
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
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function updateSupportCategoryId($supportCategoryId)
    {
        if ($this->supportCategoryId != $supportCategoryId) {
            $objFWUser = FWUser::getFWUserObject();

            // Create the appropriate TicketEvent
            $objEvent = new TicketEvent(
                $this,
                SUPPORT_TICKET_EVENT_CHANGE_CATEGORY,
                $supportCategoryId,
                $objFWUser->objUser->getId()
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
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function updateOwnerId($ownerId)
    {
        if ($this->ownerId != $ownerId) {
            $objFWUser = FWUser::getFWUserObject();

            // Create the appropriate TicketEvent
            $objEvent = new TicketEvent(
                $this,
                SUPPORT_TICKET_EVENT_CHANGE_OWNER,
                $ownerId,
                $objFWUser->objUser->getId()
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
     * @author  Reto Kohli <reto.kohli@comvation.com>
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
     * The optional $supportMessageDate argument is set to the current
     * date and time if empty.
     * @param   string  $supportMessageFrom     The Messages' e-mail field
     * @param   string  $supportMessageSubject  The Message subject
     * @param   string  $supportMessageBody     The Message text
     * @return  integer                         The ID of the new Message
     *                                          on success, 0 (zero) otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function addMessage(
        $supportMessageFrom, $supportMessageSubject,
        $supportMessageBody, $supportMessageDate=''
    ) {
if (MY_DEBUG) echo("Ticket::addMessage(supportMessageFrom=$supportMessageFrom, supportMessageSubject=$supportMessageSubject, supportMessageBody=$supportMessageBody, supportMessageDate=$supportMessageDate): INFO: entered<br />");
        if (empty($supportMessageDate)) {
            $supportMessageDate = date('Y-m-d H:i:s');
        }
        $objTicket = $this;
        $ticketStatus = TicketEvent::getTicketStatus($this->id);
        if ($ticketStatus == SUPPORT_TICKET_STATUS_CLOSED) {
            // The Ticket has already been closed.
            // Create the new Ticket.
            $objTicket = new Ticket(
                $$this->email,
                $this->source,
                $this->supportCategoryId,
                $this->languageId
            );
            if (!$objTicket->insert()) {
                return 0;
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
if (MY_DEBUG) echo("Ticket::addMessage(): ERROR: Failed to insert() the new Message, ticketId ".$objTicket->getId()."<br />");
            return 0;
        }
        // Create the TicketEvent
        $objEvent = new TicketEvent(
            $objTicket,
            SUPPORT_TICKET_EVENT_MESSAGE_NEW,
            $objMessage->getId()
        );
        if (!$objEvent) {
if (MY_DEBUG) echo("Ticket::addMessage(): ERROR: Failed to create MESSAGE TicketEvent, ticketId ".$objTicket->getId().", messageId ".$objMessage->getId()."<br />");
            $objMessage->delete();
            return 0;
        }
        // Process the MESSAGE TicketEvent.  Returns the new Ticket status.
        $newStatus = $objEvent->process();
        if ($newStatus == SUPPORT_TICKET_STATUS_UNKNOWN) {
if (MY_DEBUG) echo("Ticket::addMessage(): ERROR: Adding Message results in UNKNOWN state - rolling back!  ticketId ".$objTicket->getId().", messageId ".$objMessage->getId()."<br />");
            // On failure, try to roll back
            $objMessage->delete();
            return 0;
        }
        // If a new Ticket was created above, add a REFERENCE to the old one.
        if ($this != $objTicket) {
            $objFWUser = FWUser::getFWUserObject();

            $objEvent = new TicketEvent(
                $objTicket,                     // New Ticket object
                SUPPORT_TICKET_EVENT_REFERENCE,
                $this->id,                       // Old Ticket ID
                $objFWUser->objUser->getId()
            );
            if (!$objEvent) {
if (MY_DEBUG) echo("Ticket::addMessage(): ERROR: Failed to create REFERENCE TicketEvent, ticketId ".$objTicket->getId().", reference ticketId $this->id<br />");
                return 0;
            }
            // Process the REFERENCE TicketEvent.
            // Note that this will not change either Tickets' status.
            $newStatus = $objEvent->process();
            if ($newStatus == SUPPORT_TICKET_STATUS_UNKNOWN) {
if (MY_DEBUG) echo("Ticket::addMessage(): ERROR: Adding REFERENCE TicketEvent results in UNKNOWN state!  ticketId ".$objTicket->getId().", ref. Ticket ID $this->id<br />");
                // Nothing to roll back upon failure
                return 0;
            }
        }
        return $objMessage->getId();
    }


    /**
     * Adds a reply to this Ticket.
     *
     * This method creates a TicketEvent in order to
     * create a MESSAGE_WAIT entry and to update the Ticket status.
     * If the Ticket has already been closed, this will fail.
     * The optional $supportMessageDate argument will be set to the current
     * date and time if empty.
     * @param   string  $supportMessageFrom     The Messages' e-mail field
     * @param   string  $supportMessageSubject  The Message subject
     * @param   string  $supportMessageBody     The Message text
     * @return  boolean                         True on success,
     *                                          false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function addReply(
        $supportMessageFrom, $supportMessageSubject,
        $supportMessageBody, $supportMessageDate=0
    ) {
if (MY_DEBUG) echo("Ticket::addReply(
        supportMessageFrom=$supportMessageFrom,
        supportMessageSubject=$supportMessageSubject,
        supportMessageBody=$supportMessageBody,
        supportMessageDate=$supportMessageDate
): INFO: entered<br />");
        if ($supportMessageDate == 0) {
            $supportMessageDate = date('Y-m-d H:i:s');
        }
        // Create the new Message object
        $objMessage = new Message(
            $this->getId(),
            $supportMessageFrom,
            $supportMessageSubject,
            $supportMessageBody,
            $supportMessageDate
        );
        // The Message *MUST* be insert()ed prior to creating the TicketEvent
        // (Otherwise, we wouldn't have a valid Message ID).
        if (!$objMessage->insert()) {
if (MY_DEBUG) echo("Ticket::addReply(): ERROR: Failed to insert() the new Message, ticketId ".$this->getId()."<br />");
            return false;
        }

        $objFWUser = FWUser::getFWUserObject();

        // Create the TicketEvent
        $objEvent = new TicketEvent(
            $this,
            SUPPORT_TICKET_EVENT_REPLY,
            $objMessage->getId(),
            $objFWUser->objUser->getId()
        );
        if (!$objEvent) {
if (MY_DEBUG) echo("Ticket::addReply(): ERROR: Failed to create MESSAGE_REPLY TicketEvent (ticketId ".$this->getId().", messageId ".$objMessage->getId().")!<br />");
            return false;
        }
        // Process the MESSAGE TicketEvent.  Returns the new Ticket status.
        $newStatus = $objEvent->process();
        if ($newStatus == SUPPORT_TICKET_STATUS_UNKNOWN) {
if (MY_DEBUG) echo("Ticket::addReply(): ERROR: Adding reply results in UNKNOWN state - rolling back!  ticketId ".$this->getId().", messageId ".$objMessage->getId()."<br />");
            // On failure, try to roll back
            if (!$objMessage->delete()) {
if (MY_DEBUG) echo("Ticket::addReply(): ERROR: Failed to roll back Message insert (ticketId ".$this->getId().", messageId ".$objMessage->getId().")!<br />");
            }
            // Adding the reply failed.
            return false;
        }
        // Got a valid status, so all is well.
        return true;
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
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function deleteMessage($messageId)
    {
        $objFWUser = FWUser::getFWUserObject();
        $objEvent = new TicketEvent(
            $this,
            SUPPORT_TICKET_EVENT_MESSAGE_DELETE,
            $messageId,
            $objFWUser->objUser->getId()
        );
        $newStatus = $objEvent->process();
        if ($newStatus == SUPPORT_TICKET_STATUS_UNKNOWN) {
if (MY_DEBUG) echo("Ticket::deleteMessage(messageId=$messageId): INFO: process() returned SUPPORT_TICKET_STATUS_UNKNOWN (0).<br />");
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
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function updateView($messageId)
    {
        $objFWUser = FWUser::getFWUserObject();
        $objEvent = new TicketEvent(
            $this,
            SUPPORT_TICKET_EVENT_MESSAGE_VIEW,
            $messageId,
            $objFWUser->objUser->getId()
        );
        $newStatus = $objEvent->process();
        if ($newStatus == SUPPORT_TICKET_STATUS_UNKNOWN) {
if (MY_DEBUG) echo("Ticket::updateView(messageId=$messageId): INFO: process() returned SUPPORT_TICKET_STATUS_UNKNOWN (0).<br />");
            // Nothing to roll back upon failure
            return false;
        }
        return true;
    }


    /**
     * Close this Ticket.
     *
     * An appropriate TicketEvent record is added.
     * @return  boolean                     True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function close() {
        $objFWUser = FWUser::getFWUserObject();
        $objEvent = new TicketEvent(
            $this,
            SUPPORT_TICKET_EVENT_CLOSE,
            0,
            $objFWUser->objUser->getId()
        );
        $newStatus = $objEvent->process();
        if ($newStatus == SUPPORT_TICKET_STATUS_UNKNOWN) {
            // Nothing to roll back upon failure
            return false;
        }
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
//if (MY_DEBUG) echo("Ticket::getById($id): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
//if (MY_DEBUG) echo("Ticket::getById($id): objResult: '$objResult'<br />");
        if (!$objResult) {
if (MY_DEBUG) echo("Ticket::getById($id): query failed, objResult: '$objResult', count: ".$objResult->RecordCount()."<br />query: $query<br />");
            return false;
        }
        if ($objResult->RecordCount() == 0) {
if (MY_DEBUG) echo("Ticket::getById($id): no result: ".$objResult->RecordCount()."<br />");
            return false;
        }
        $objTicket = new Ticket(
            contrexx_stripslashes($objResult->fields['email']),
            $objResult->fields['source'],
            $objResult->fields['support_category_id'],
            $objResult->fields['language_id'],
            contrexx_stripslashes($objResult->fields['timestamp']),
            $objResult->fields['id']
        );
        return $objTicket;
    }


    /**
     * Returns an array of Ticket IDs from the database.
     *
     * Set all the arguments to the values you are looking for, or to their
     * DON'T CARE value if they should be ignored.  See the details for
     * each parameter.
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
     *                                              or false
     * @param       integer     $languageId     The language ID, or false
     * @param       integer     $ownerId        The owner ID, or a
     *                                          negative number.
     * @param       integer     $status         The Ticket status, or a
     *                                          negative number.
     * @param       integer     $source         The Ticket source, or a
     *                                          negative number.
     * @param       string      $email          The e-mail address, or false
     * @param       string      $order          The sorting order, or the
     *                                          empty string
     * @param       integer     $offset         The offset, or zero
     * @return      array                       The array of Ticket IDs
     *                                          on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @global      array       $_CONFIG        Global configuration array
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getTicketIdArray(
        $supportCategoryId, $languageId, $ownerId, $status, $source, $email,
        $order='', $offset=0, $limit=0
    ) {
        global $objDatabase, $_CONFIG;

        if (!$limit) {
            $limit = $_CONFIG['corePagingLimit'];
        }
/*
        if (!$order) {
            $order = '`timestamp` DESC';
        }
*/
        $query = "
            SELECT id
              FROM ".DBPREFIX."module_support_ticket
             WHERE 1
               ".($supportCategoryId == false ? '' : "AND support_category_id=$supportCategoryId")."
               ".($languageId        == false ? '' : "AND language_id=$languageId")."
               ".($source            <  0     ? '' : "AND source=$source")."
               ".($email             == false ? '' : "AND email='".contrexx_addslashes($email)."'")."
          ".($order ? "ORDER BY $order" : '');
        $objResult = $objDatabase->SelectLimit(
            $query, $limit, $offset
        );
        if (!$objResult) {
if (MY_DEBUG) echo("getTicketIdArray(supportCategoryId=$supportCategoryId, languageId=$languageId, ownerId=$ownerId, status=$status, source=$source, email=$email, order=$order, offset=$offset, limit=$limit): ERROR: query failed: $query<br />");
            return false;
        }
        // return array
        $arrTicketId = array();
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            // Compare status and owner ID if desired -- This is a bit costly!
            if (
                    ($status < 0
                        ?   true
                        :   TicketEvent::getTicketStatus($id) == $status
                    )
                &&
                    ($ownerId <= 0
                        ?   true
                        :   TicketEvent::getTicketOwnerId($id) == $ownerId
                    )
            ) {
                $arrTicketId[] = $id;
            }
            $objResult->MoveNext();
        }

if (MY_DEBUG) { echo("getTicketIdArray(supportCategoryId=$supportCategoryId, languageId=$languageId, ownerId=$ownerId, status=$status, source=$source, email=$email, order=$order, offset=$offset, limit=$limit): INFO: returning array: ");var_export($arrTicketId);echo("<br />"); }
        return $arrTicketId;
    }


    /**
     * Returns HTML code for the Ticket status dropdown menu.
     *
     * Does only contain the <select> tag pair if the optional $menuName
     * is specified and evaluates to a true value.
     * @static
     * @param   integer $selectedId The optional preselected status
     * @param   string  $menuName   The optional menu name, defaults to the
     *                              empty string.  Unless specified, no <select>
     *                              tag pair will be added.
     * @return  string              The dropdown menu HTML code
     * @global  array   $arrTicketStatusString  Ticket status strings
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getStatusMenu($selectedId=0, $menuName='')
    {
        global $arrTicketStatusString;

        $menu = '';
        for ($status = 0; $status < SUPPORT_TICKET_STATUS_COUNT; ++$status) {
            $menu .=
                "<option value='$status'".
                ($selectedId == $status ? ' selected="selected"' : '').
                '>'.$arrTicketStatusString[$status]."</option>\n";
        }
        if ($menuName) {
            $menu = "<select id='$menuName' name='$menuName'>\n$menu\n</select>\n";
        }
//if (MY_DEBUG) echo("getStatusMenu(selected=$selectedId, name=$menuName): made menu: ".htmlentities($menu)."<br />");
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
//if (MY_DEBUG) echo("getSourceMenu(selected=$selectedId, name=$menuName): made menu: ".htmlentities($menu)."<br />");
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
        $objFWUser = FWUser::getFWUserObject();
        if (($objUsers = $objFWUser->objUser->getUsers()) === false) {
if (MY_DEBUG) echo("getOwnerMenu(selected=$selectedId, name=$menuName, onchange=$onchange): ERROR: got no user IDs!<br />");
            return false;
        }
//if (MY_DEBUG) { echo("getOwnerMenu(selected=$selectedId, name=$menuName, onchange=$onchange): got user IDs: ");var_export($arrUserId);echo("<br />"); }
        $menu = '';
        while (!$objUsers->EOF) {
            $fullName = trim($objUsers->getProfileAttribute('firstanme').' '.$objUsers->getProfileAttribute('lastname'));
            if ($fullName == '') {
                continue;
            }
            $menu .=
                "<option value='{$objUsers->getId()}'".
                ($selectedId == $objUsers->getId() ? ' selected="selected"' : '').
                '>'.htmlentities($fullName, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
            $objUsers->next();
        }

        if ($menuName) {
            $menu = "<select id='$menuName' name='$menuName'".
            ($onchange ? ' onchange="'.$onchange.'"' : '').
            ">\n$menu\n</select>\n";
        }
//if (MY_DEBUG) echo("getOwnerMenu(selected=$selectedId, name=$menuName, onchange=$onchange): made menu: ".htmlentities($menu)."<br />");
        return $menu;

    }


}

?>
