<?php

/**
 * Message
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */

/*

Database Tables Structure:

DROP TABLE contrexx_module_support_message;
CREATE TABLE contrexx_module_support_message (
  id            int(10)      unsigned NOT NULL auto_increment,
  ticket_id     int(10)      unsigned NOT NULL,
--  `status`      tinyint(2)   unsigned NOT NULL default 1,
  `from`        varchar(255)          NOT NULL,
  subject       varchar(255)          NOT NULL,
  body          mediumtext            NOT NULL,
  `date`        datetime              NOT NULL,
  `timestamp`   timestamp             NOT NULL default current_timestamp,
  PRIMARY KEY     (id),
  KEY ticket_id   (ticket_id),
--  KEY status      (status),
  KEY `date`      (`date`),
  KEY `timestamp` (`timestamp`)
) ENGINE=MyISAM;

*/

/**
 * Message
 *
 * Every Support Message is associated with one Support Ticket.
 * Every Support Message is associated with zero or more Attachments.
 * Every Message has the following fields:
 *  id            The Message ID
 *  ticket_id     The associated Ticket ID
 *  // `status`      The Message status
 *  from          (Usually) the originating e-mail address
 *  subject       The Message subject line
 *  body          The Message body
 *  `date`        The original Message date and time
 *  `timestamp`   The timestamp of when the Message was inserted
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */

class Message
{
    /**
     * Message ID
     *
     * From table modules_support_message
     * @var integer
     */
    var $id;

    /**
     * Ticket ID
     *
     * From table modules_support_message
     * @var integer
     */
    var $ticketId;

    /**
     * The Message source
     *
     * Usually taken from the original e-mail messages' 'From:' field
     * From table modules_support_message
     * @var string
     */
    var $from;

    /**
     * The Message subject line
     *
     * Usually taken from the original e-mail messages' 'Subject:' field
     * From table modules_support_message
     * @var string
     */
    var $subject;

    /**
     * The Message body
     *
     * Usually taken from the original e-mail messages' text body
     * From table modules_support_message
     * @var string
     */
    var $body;

    /**
     * The original Message date
     *
     * Usually taken from the original e-mail messages' 'Date:' field
     * From table modules_support_message
     * @var string
     */
    var $date;

    /**
     * Timestamp
     *
     * Set to the date and time when the record is created in the
     * database table.
     * From table modules_support_message
     * @var string
     */
    var $timestamp;

    /**
     * Message status
     *
     * This is a run-time calculated value not stored with the
     * Message record.
     * From table modules_support_ticket_event, see
     * {@link TicketEvent::getMessageStatus()}
     * @var integer
     */
    var $status = false;

    /**
     * The status text array
     *
     * @todo    *SHOULD* be static.
     * @var     array
     */
    var $arrStatusString;


    /**
     * Constructor (PHP4)
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     * @see         __construct()
     */
    function Message(
        $ticketId, $from, $subject, $body, $date, $timestamp='', $id=0
    ) {
        $this->__construct(
            $ticketId, $from, $subject, $body, $date, $timestamp, $id
        );
    }

    /**
     * Constructor (PHP5)
     * @global      array   $_ARRAYLANG     Language array
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     * @todo        PHP5: Make $this->arrStatusString static!
     * @global  array       $_ARRAYLANG     Language array
     */
    function __construct(
        $ticketId, $from, $subject, $body, $date, $timestamp='', $id=0
    ) {
        global $_ARRAYLANG;

        $this->ticketId  = $ticketId;
//        $this->status    = $status;
        $this->from      = $from;
        $this->subject   = $subject;
        $this->body      = $body;
        $this->date      = $date;
        $this->timestamp = $timestamp;
        $this->id        = $id;
/*
        $this->statusChanged = false;
*/
        /**
         * Message State text array
         * *SHOULD* be static
         */
        $this->arrStatusString = array(
            SUPPORT_MESSAGE_STATUS_UNKNOWN => $_ARRAYLANG['TXT_SUPPORT_MESSAGE_STATUS_UNKNOWN'],
            SUPPORT_MESSAGE_STATUS_NEW     => $_ARRAYLANG['TXT_SUPPORT_MESSAGE_STATUS_NEW'],
            SUPPORT_MESSAGE_STATUS_READ    => $_ARRAYLANG['TXT_SUPPORT_MESSAGE_STATUS_READ'],
            SUPPORT_MESSAGE_STATUS_DELETED => $_ARRAYLANG['TXT_SUPPORT_MESSAGE_STATUS_DELETED'],
        );
//if (MY_DEBUG) { echo("Message::__construct(ticketId=$ticketId, from=$from, subject=$subject, body=$body, date=$date, timestamp=$timestamp, id=$id): INFO: Made Message: ");var_export($this);echo("<br />"); }
    }


    /**
     * Get this Messages' ID
     * @return  integer     The Message ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getId()
    {
        return $this->id;
    }

    /**
     * Get this Messages' Ticket ID
     * @return  integer     The Ticket ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getTicketId()
    {
if (MY_DEBUG) echo("Message::getTicketId(): returning $this->ticketId<br />");
        return $this->ticketId;
    }

    /**
     * Get this Messages' source (e-mail address)
     * @return  string      The Message source
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getFrom()
    {
        return $this->from;
    }

    /**
     * Get this Messages' subject line
     * @return  string      The Message subject line
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getSubject()
    {
        return $this->subject;
    }

    /**
     * Get this Messages' body
     * @return  string      The Message body
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getBody()
    {
        return $this->body;
    }

    /**
     * Get this Messages' date
     * @return  string      The Message date
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getDate()
    {
        return $this->date;
    }

    /**
     * Get this Messages' timestamp
     * @return  string      The Message timestamp
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getTimestamp()
    {
        return $this->timestamp;
    }


    /**
     * Get this Messages' status
     * @return  integer     The Message status
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getStatus()
    {
        if ($this->status === false) {
            $this->status =
                TicketEvent::getMessageStatus(
                    $this->id,
                    $this->ticketId
                );
        }
        return $this->status;
    }

    /**
     * Get this Messages' status as a string
     * @return  string      The Message status string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getStatusString()
    {
        return $this->arrStatusString[
            TicketEvent::getMessageStatus($this->id, $this->ticketId)
        ];
    }


    /**
     * Delete this Message from the database.
     *
     * Note that this *SHOULD* only be done when the associated Ticket
     * is deleted as well!
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function delete()
    {
        global $objDatabase;
//if (MY_DEBUG) echo("Debug: Message::delete(): entered<br />");

        if (!$this->id) {
if (MY_DEBUG) echo("Message::delete(): Error: This Message is missing its ID!<br />");
            return false;
        }
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_support_message
             WHERE id=$this->id
        ");
        if (!$objResult) {
if (MY_DEBUG) echo("Message::delete(): Error: Failed to delete the Message from the database!<br />");
            return false;
        }
        return true;
    }


    /**
     * Stores this Message in the database.
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
     * Update this Message in the database.
     *
     * Note that currently, only the status field may be updated.
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function update()
    {
        global $objDatabase;

        $query = "
            UPDATE ".DBPREFIX."module_support_message
               SET `status`=$this->status,
             WHERE id=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
//if (MY_DEBUG) echo("Message::update(): done<br />");
        return true;
    }


    /**
     * Insert this Message into the database.
     *
     * Note that the status field *MUST* be set to the correct default
     * value of 1 (NEW), as specified in the table definition.
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function insert()
    {
        global $objDatabase;

        $query = "
            INSERT INTO ".DBPREFIX."module_support_message (
                   ticket_id,
                   `from`,
                   subject,
                   body,
                   `date`
            ) VALUES (
                   $this->ticketId,
                   '".contrexx_addslashes($this->from)."',
                   '".contrexx_addslashes($this->subject)."',
                   '".contrexx_addslashes($this->body)."',
                   '".contrexx_addslashes($this->date)."'
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $this->id = $objDatabase->Insert_ID();
        // Update the object with the actual timestamp
//if (MY_DEBUG) echo("Message::insert(): done<br />");
        return $this->refreshTimestamp();
    }


    /**
     * Updates the Message object with the timestamp value stored in the
     * database.
     *
     * This *MUST* be called by insert() after INSERTing any new
     * Message object!
     * @return  boolean         True on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function refreshTimestamp()
    {
        global $objDatabase;

        $query = "
            SELECT `timestamp`
              FROM ".DBPREFIX."module_support_message
             WHERE id=$this->id
        ";
if (MY_DEBUG) echo("Message::refreshTimestamp(): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
if (MY_DEBUG) echo("Message::refreshTimestamp(): objResult: '$objResult'<br />");
        if (!$objResult) {
if (MY_DEBUG) echo("Message::refreshTimestamp(): query failed, objResult: '$objResult', count: ".$objResult->RecordCount()."<br />");
            return false;
        }
        if ($objResult->RecordCount() == 0) {
if (MY_DEBUG) echo("Message::refreshTimestamp(): no result: ".$objResult->RecordCount()."<br />");
            return false;
        }
        $this->timestamp = contrexx_stripslashes($objResult->fields['timestamp']);
        return true;
    }


    /**
     * Delete the Messages referring to a certain Ticket ID
     * from the database.
     *
     * Note that this *MUST* only be called when the associated
     * Ticket is deleted as well.
     * @static
     * @param       integer     $ticketId       The Ticket ID
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function deleteByTicketId($ticketId)
    {
        global $objDatabase;
//if (MY_DEBUG) echo("Debug: Message::deleteByTicketId(ticketId=$ticketId): entered<br />");

        if (!$ticketId > 0) {
if (MY_DEBUG) echo("Message::deleteByTicketId(ticketId=$ticketId): ERROR: missing or illegal Ticket ID!<br />");
            return false;
        }
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_support_message
             WHERE ticket_id=$ticketId
        ");
        if (!$objResult) {
if (MY_DEBUG) echo("Message::deleteByTicketId(ticketId=$ticketId): ERROR: Failed to delete the Message records from the database<br />");
            return false;
        }
        return true;
    }


    /**
     * Select a Message by ID from the database.
     * @static
     * @param       integer     $id             The Message ID
     * @return      Message                      The Message object
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
              FROM ".DBPREFIX."module_support_message
             WHERE id=$id
        ";
//if (MY_DEBUG) echo("Message::getById($id): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
//if (MY_DEBUG) echo("Message::getById($id): objResult: '$objResult'<br />");
        if (!$objResult) {
if (MY_DEBUG) echo("Message::getById($id): query failed, objResult: '$objResult', count: ".$objResult->RecordCount()."<br />");
            return false;
        }
        if ($objResult->RecordCount() == 0) {
if (MY_DEBUG) echo("Message::getById($id): no result: ".$objResult->RecordCount()."<br />");
            return false;
        }
        $objMessage = new Message(
            $objResult->fields['ticket_id'],
            contrexx_stripslashes($objResult->fields['from']),
            contrexx_stripslashes($objResult->fields['subject']),
            contrexx_stripslashes($objResult->fields['body']),
            contrexx_stripslashes($objResult->fields['date']),
            contrexx_stripslashes($objResult->fields['timestamp']),
            $objResult->fields['id']
        );
if (MY_DEBUG) { echo("Message::getById($id): made Message: ");var_export($objMessage);echo("<br />"); }
        return $objMessage;
    }


    /**
     * Returns an array of Message IDs related to a certain
     * ticket ID, or with a given status, from, subject, or date
     * from the database.
     *
     * Any of the mandatory arguments may contain values evaluating to
     * the boolean false value, in which case they are not considered
     * in the WHERE clause.  Any such arguments that evaluate to true,
     * however, limit the result set to records having identical values.
     * The optional parameter $order determines the sorting order
     * in SQL syntax, it defaults to ordered by date descending, or
     * latest first.
     * The optional parameter $offset determines the offset of the
     * first Message to be read from the database, and defaults to 0 (zero).
     * The optional parameter $limit limits the number of results.
     * It defaults to the value of the global $_CONFIG['corePagingLimit']
     * setting if unset or zero.
     * @static
     * @param       integer     $ticketId       The Ticket ID
     * @param       integer     $status         The desired Message status,
     *                                          or zero
     * @param       string      $from           The desired sender e-mail,
     *                                          or the empty string
     * @param       string      $subject        The desired subject line,
     *                                          or the empty string
     * @param       string      $date           The desired message date,
     *                                          or the empty string
     * @param       string      $order          The sorting order
     * @param       integer     $offset         The offset
     * @param       integer     $limit          The limit for the number of
     *                                          IDs returned
     * @return      array                       The array of Message IDs
     *                                          on success, false otherwise.
     * @global      mixed       $objDatabase    Database object
     * @global      array       $_CONFIG        Global configuration array
     *                                          on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getMessageIdArray(
        $ticketId, $status, $from, $subject, $date,
        $order="`timestamp` DESC", $offset=0, $limit=0
    ) {
        global $objDatabase, $_CONFIG;

        $limit = ($limit ? $limit : $_CONFIG['corePagingLimit']);
        $query = "
            SELECT id
              FROM ".DBPREFIX."module_support_message
             WHERE 1
               ".($ticketId ? "AND ticket_id=$ticketId" : '')."
               ".($status   ? "AND status=$status" : '')."
               ".($from     ? "AND from='".contrexx_addslashes($from)."'" : '')."
               ".($subject  ? "AND subject='".contrexx_addslashes($subject)."'" : '')."
               ".($date     ? "AND date='".contrexx_addslashes($date)."'" : '')."
          ORDER BY $order
        ";
        $objResult = $objDatabase->SelectLimit($query, $limit, $offset);
        if (!$objResult) {
            return false;
        }
        // return array
        $arrMessageId = array();
        while (!$objResult->EOF) {
            $arrMessageId[] = $objResult->fields['id'];
            $objResult->MoveNext();
        }
        return $arrMessageId;
    }


    /**
     * Returns an array of Message objects related to a certain
     * ticket ID, or with a given status, from, subject, or date
     * from the database.
     *
     * Any of the mandatory arguments may contain values evaluating to
     * the boolean false value, in which case they are not considered
     * in the WHERE clause.  Any such arguments that evaluate to true,
     * however, limit the result set to records having identical values.
     * The optional parameter $order determines the sorting order
     * in SQL syntax, it defaults to ordered by date descending, or
     * latest first.
     * The optional parameter $offset determines the offset of the
     * first Message to be read from the database, and defaults to 0 (zero).
     * The optional parameter $limit limits the number of results.
     * It defaults to the value of the global $_CONFIG['corePagingLimit']
     * setting if unset or zero.
     * Note that this calls {@link getMessageIdArray()} with the same
     * parameters in order to obtain the array of IDs of the Messages.
     * @static
     * @param       integer     $ticketId       The Ticket ID
     * @param       integer     $status         The desired Message status,
     *                                          or zero
     * @param       string      $from           The desired sender e-mail,
     *                                          or the empty string
     * @param       string      $subject        The desired subject line,
     *                                          or the empty string
     * @param       string      $date           The desired message date,
     *                                          or the empty string
     * @param       string      $order          The sorting order
     * @param       integer     $offset         The offset
     * @param       integer     $limit          The limit for the number of
     *                                          IDs returned
     * @return      array                       The array of Message objects
     *                                          on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getMessageArray(
        $ticketId, $status, $from, $subject, $date,
        $order="`timestamp` DESC", $offset=0, $limit=0
    ) {
        // Go get the IDs
        $arrMessageId = getMessageIdArray(
            $ticketId, $status, $from, $subject, $date,
            $order, $offset, $limit
        );
        if (!is_array($arrMessageId)) {
if (MY_DEBUG) echo("Message::getMessageArray(array=$arrMessageId): ERROR: got no array of IDs!");
            return false;
        }
        // return array of objects
        $arrMessage = array();
        foreach ($arrMessageId as $messageId) {
// TODO: Verify that the objects are in fact, er... objects.
             $arrMessage[] = Message::getById($messageId);
        }
        return $arrMessage;
    }


    /**
     * Returns the number of records for the given criteria
     *
     * The method uses the same mandatory arguments as
     * {@link getMessageArray()}, but returns the number of records
     * found (without limiting the size).
     * @static
     * @param       integer     $ticketId       The Ticket ID
     * @param       integer     $status         The desired Message status,
     *                                          or zero
     * @param       string      $from           The desired sender e-mail,
     *                                          or the empty string
     * @param       string      $subject        The desired subject line,
     *                                          or the empty string
     * @param       string      $date           The desired message date,
     *                                          or the empty string
     * @return      integer                     The number of Message records
     *                                          on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getRecordCount(
        $ticketId, $status, $from, $subject, $date
    ) {
        global $objDatabase, $_CONFIG;

        $query = "
            SELECT COUNT(*) as numof
              FROM ".DBPREFIX."module_support_message
             WHERE 1
               ".($ticketId ? "AND ticket_id=$ticketId" : '')."
               ".($status   ? "AND status=$status" : '')."
               ".($from     ? "AND from='".contrexx_addslashes($from)."'" : '')."
               ".($subject  ? "AND subject='".contrexx_addslashes($subject)."'" : '')."
               ".($date     ? "AND date='".contrexx_addslashes($date)."'" : '');
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
     * Returns the ID of the latest Message available for the given
     * Ticket ID.
     * @param   integer $ticketId       The Ticket ID
     * @return  mixed                   The latest Message ID on success,
     *                                  false otherwise
     * @global  mixed   $objDatabase    Database object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getLatestByTicketId($ticketId)
    {
        global $objDatabase;

        $query = "
            SELECT id
              FROM ".DBPREFIX."module_support_message
             WHERE ticket_id=$ticketId
          ORDER BY `date` DESC
        ";
        $objResult = $objDatabase->SelectLimit($query, 1);
        if (!$objResult) {
            return false;
        }
        if (!$objResult->EOF) {
            // Return the ID
            return $objResult->fields['id'];
        }
        return false;
    }



}

?>
