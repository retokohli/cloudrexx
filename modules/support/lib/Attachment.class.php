<?php

/**
 * Attachment
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */

/*

Database Tables Structure:

DROP TABLE contrexx_module_support_attachment;
CREATE TABLE contrexx_module_support_attachment (
  id                  int(11)      unsigned NOT NULL auto_increment,
  message_id          int(11)      unsigned NOT NULL,
  `name`              varchar(255)              NULL default NULL,
  type                varchar(255)              NULL default NULL,
  content             mediumtext            NOT NULL,
  PRIMARY KEY    (id),
  KEY message_id (message_id)
) ENGINE=MyISAM;

*/

/**
 * Attachment
 *
 * Every Message may have zero or more Attachment associated with it.
 * Each Attachment has the following fields:
 *  id          The Attachment ID
 *  message_id  The associated Message ID
 *  name        The Attachment file name, if any.
 *  type        The content MIME type, if known.
 *  content     The content of the Attachment
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */

class Attachment
{
    /**
     * Attachment ID
     *
     * From table modules_support_attachment
     * @var integer
     */
    var $id;

    /**
     * Message associated with this Attachment
     *
     * From table modules_support_attachment
     * @var integer
     */
    var $messageId;

    /**
     * Attachment file name
     *
     * From table modules_support_attachment
     * @var string
     */
    var $name;

    /**
     * Attachment MIME type
     *
     * From table modules_support_attachment
     * @var string
     */
    var $type;

    /**
     * The Attachment content
     *
     * From table modules_support_attachment
     * @var string
     */
    var $content;


    /**
     * Constructor (PHP4)
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     * @see         __construct()
     */
    function Attachment($messageId, $name, $type, $content, $id=0) {
        $this->__construct($messageId, $name, $type, $content, $id);
    }

    /**
     * Constructor (PHP5)
     * @global      array   $_ARRAYLANG     Language array
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     * @todo        PHP5: Make $this->arrStatusString and
     *                         $this->arrActionString static!
     */
    function __construct($messageId, $name, $type, $content, $id=0) {
        $this->messageId = intval($messageId);
        $this->name      = strip_tags($name);
        $this->type      = strip_tags($type);
        $this->content   = $content;
        $this->id        = intval($id);
if (MY_DEBUG) { echo("Attachment::__construct(messageId=$messageId, name=$name, type=$type, content=$content, id=$id): INFO: Made Attachment: ");var_export($this);echo("<br />"); }
    }


    /**
     * Get this Attachments' ID
     * @return  integer     The Attachment ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getId()
    {
        return $this->id;
    }

    /**
     * Get the Message ID associated with this Attachment
     * @return  integer     The Message ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * Get this Attachments' file name
     * @return  string      The Attachment file name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getName()
    {
        return $this->name;
    }

    /**
     * Get this Attachments' MIME type
     * @return  integer     The Attachment MIME type
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getType()
    {
        return $this->type;
    }

    /**
     * Get this Attachments' content
     * @return  integer     The Attachment content
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getContent()
    {
        return $this->content;
    }


    /**
     * Delete this Attachment from the database.
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function delete()
    {
        global $objDatabase;
//if (MY_DEBUG) echo("Debug: Attachment::delete(): entered<br />");

        if (!$this->id) {
if (MY_DEBUG) echo("Attachment::delete(): ERROR: This Attachment has no ID!<br />");
            return false;
        }
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_support_attachment
             WHERE id=$this->id
        ");
        if (!$objResult) {
if (MY_DEBUG) echo("Attachment::delete(): ERROR: Failed to delete the Attachment with ID $this->id from the database!<br />");
            return false;
        }
        return true;
    }


    /**
     * Stores this Attachment in the database.
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
     * Update this Attachment in the database.
     *
     * Note that this is currently unused.
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function update()
    {
        global $objDatabase;

        $query = "
            UPDATE ".DBPREFIX."module_support_attachment
               SET message_id=$this->supportCategoryId,
                   name='".contrexx_addslashes($this->name)."',
                   type='".contrexx_addslashes($this->type)."',
                   content='".contrexx_addslashes($this->content)."'
             WHERE id=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
//if (MY_DEBUG) echo("Attachment::update(): done<br />");
        return true;
    }


    /**
     * Insert this new Attachment into the database.
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function insert()
    {
        global $objDatabase;

        $query = "
            INSERT INTO ".DBPREFIX."module_support_attachment (
                   message_id,
                   name,
                   type,
                   content
            ) VALUES (
                   $this->messageId,
                   '".contrexx_addslashes($this->name)."',
                   '".contrexx_addslashes($this->type)."',
                   '".contrexx_addslashes($this->content)."'
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $this->id = $objDatabase->Insert_ID();
//if (MY_DEBUG) echo("Attachment::insert(): done<br />");
        return $this->refreshTimestamp();
    }


    /**
     * Select an Attachment by ID from the database.
     * @static
     * @param       integer     $id             The Attachment ID
     * @return      Attachment                  The Attachment object
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
              FROM ".DBPREFIX."module_support_attachment
             WHERE id=$id
        ";
//if (MY_DEBUG) echo("Attachment::getById($id): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
//if (MY_DEBUG) echo("Attachment::getById($id): objResult: '$objResult'<br />");
        if (!$objResult) {
if (MY_DEBUG) echo("Attachment::getById($id): query failed, objResult: '$objResult', count: ".$objResult->RecordCount()."<br />query: $query<br />");
            return false;
        }
        if ($objResult->RecordCount() == 0) {
if (MY_DEBUG) echo("Attachment::getById($id): no result: ".$objResult->RecordCount()."<br />");
            return false;
        }
        $objAttachment = new Attachment(
            $objResult->fields['message_id'],
            contrexx_stripslashes($objResult->fields['name']),
            contrexx_stripslashes($objResult->fields['type']),
            contrexx_stripslashes($objResult->fields['content']),
            $objResult->fields['id']
        );
        return $objAttachment;
    }


    /**
     * Returns an array of Attachment IDs for the given Message ID.
     * @static
     * @param       integer     $messageId      The Message ID
     * @return      array                       The array of Attachment IDs
     *                                          on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @global      array       $_CONFIG        Global configuration array
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getAttachmentIdArray($messageId) {
        global $objDatabase;

        $query = "
            SELECT id
              FROM ".DBPREFIX."module_support_attachment
             WHERE message_id=$messageId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
if (MY_DEBUG) echo("getAttachmentIdArray(messageId=$messageId): ERROR: query failed: $query<br />");
            return false;
        }
        // return array
        $arrAttachmentId = array();
        while (!$objResult->EOF) {
            $arrAttachmentId[] = $objResult->fields['id'];
            $objResult->MoveNext();
        }
if (MY_DEBUG) { echo("getAttachmentIdArray(messageId=$messageId): INFO: returning array: ");var_export($arrAttachmentId);echo("<br />"); }
        return $arrAttachmentId;
    }

}

?>
