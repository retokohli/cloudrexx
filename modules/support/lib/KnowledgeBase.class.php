<?php

/**
 * Knowledge Base
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */

/*

Database Tables Structure:

CREATE TABLE contrexx_module_support_knowledge_base (
  id                    int(11)    unsigned NOT NULL auto_increment,
  `support_category_id` int(11)    unsigned NOT NULL,
  `status`              tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY               (id),
  KEY `support_category_id` (`support_category_id`),
  KEY `status`              (`status`)
) ENGINE=MyISAM;

CREATE TABLE contrexx_module_support_knowledge_base_language (
  knowledge_base_id int(10)      unsigned NOT NULL,
  language_id       int(10)      unsigned NOT NULL,
  subject           varchar(255)          NOT NULL,
  body              mediumtext            NOT NULL,
  PRIMARY KEY (knowledge_base_id, language_id),
  INDEX subject (subject),
  INDEX body (body),
) ENGINE=MyISAM;

*/


/**
 * Knowledge Base
 *
 * Stores the best of Support Ticket replys.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */

class KnowledgeBase
{
    /**
     * Knowledge Base ID
     *
     * From table module_support_knowledge_base
     * @var integer
     */
    var $id;

    /**
     * Support Category this entry is associated with
     *
     * From table module_support_knowledge_base
     * @var integer
     */
    var $supportCategoryId;

    /**
     * Knowledge Base status
     *
     * From table module_support_knowledge_base
     * @var integer
     */
    var $status;

    /**
     * Knowledge Base entry language ID
     *
     * From table module_support_knowledge_base_language
     * @var integer
     */
    var $languageId;

    /**
     * Knowledge Base entry subject
     *
     * From table module_support_knowledge_base_language
     * @var string
     */
    var $subject;

    /**
     * Knowledge Base entry body
     *
     * From table module_support_knowledge_base_language
     * @var string
     */
    var $body;


    /**
     * Constructor (PHP4)
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     * @see         __construct()
     */
    function KnowledgeBase(
        $subject, $body, $supportCategoryId, $languageId, $status=1, $id=0
    ) {
        $this->__construct(
            $subject, $body, $supportCategoryId, $languageId, $status, $id
        );
    }

    /**
     * Constructor (PHP5)
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     */
    function __construct(
        $subject, $body, $supportCategoryId, $languageId, $status=1, $id=0
    ) {
        $this->subject           = strip_tags($subject);
        $this->body              = strip_tags($body);
        $this->supportCategoryId = intval($supportCategoryId);
        $this->languageId        = intval($languageId);
        $this->status            = intval($status);
        $this->id                = intval($id);
if (MY_DEBUG) { echo("__construct(subject=$subject, body=$body, supportCategoryId=$supportCategoryId, lang=$languageId, status=$status, id=$id): made ");var_export($this);echo("<br />"); }
    }


    /**
     * Get this Knowledge Base's ID
     * @return  integer     The Knowledge Base ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getId()
    {
        return $this->Id;
    }

    /**
     * Get this Knowledge Base's status
     * @return  integer     The Knowledge Base status
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getStatus()
    {
        return $this->status;
    }
    /**
     * Set this Knowledge Base's status
     * @param   integer     The Knowledge Base status
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setStatus($status)
    {
        $this->status = intval($status);
    }

    /**
     * Get this Knowledge Base entry subject
     * @return  string      The subject
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getSubject()
    {
        return $this->subject;
    }
    /**
     * Set this Knowledge Base entry subject
     * @param   string      The subject
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setSubject($subject)
    {
        $this->subject = strip_tags($subject);
    }

    /**
     * Get this Knowledge Base entry body
     * @return  string      The body
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getBody()
    {
        return $this->body;
    }
    /**
     * Set this Knowledge Base entry body
     * @param   string      The body
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setBody($body)
    {
        $this->body = strip_tags($body);
    }

    /**
     * Get this Knowledge Base's language ID
     * @return  integer     The Knowledge Base language ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getLanguageId()
    {
        return $this->languageId;
    }
    /**
     * Set this Knowledge Base's language ID
     * @param   integer     The Knowledge Base language ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setLanguageId($languageId)
    {
        $this->languageId = intval($languageId);
    }

    /**
     * Get this Knowledge Base entry Support Category ID
     * @return  integer     The Support Category ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getSupportCategoryId()
    {
        return $this->supportCategoryId;
    }
    /**
     * Set this Knowledge Base entry Support Category ID
     * @param   integer     The Support Category ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setSupportCategoryId($supportCategoryId)
    {
        $this->supportCategoryId = intval($supportCategoryId);
    }


    /**
     * Clone the Knowledge Base entry
     *
     * Note that this does NOT create a copy in any way, but simply clears
     * the Knowledge Base ID.  Upon storing this object, a new ID is created.
     * @return      void
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function makeClone() {
        $this->id = '';
    }


    /**
     * Delete this Knowledge Base entry from the database.
     *
     * If the optional $languageId parameter is set,
     * only the language entry with the appropriate language ID
     * is removed, and the Knowledge Base itself is left untouched.
     * Otherwise, both the Knowledge Base and all its language entries
     * are deleted.
     * @return      boolean                 True on success, false otherwise
     * @global      mixed   $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function delete($languageId=0)
    {
        global $objDatabase;
//if (MY_DEBUG) echo("Debug: KnowledgeBase::delete(): entered<br />");

        if (!$this->id) {
if (MY_DEBUG) echo("KnowledgeBase::delete($languageId): Error: This Knowledge Base is missing the ID<br />");
            return false;
        }
        $return = true;
        $query = "
            DELETE FROM ".DBPREFIX."module_support_knowledge_base_language
             WHERE knowledge_base_id=$this->id
        ";
        if ($languageId) {
            $query .= "AND language_id=$languageId";
        } else {
            $objResult = $objDatabase->Execute("
                DELETE FROM ".DBPREFIX."module_support_knowledge_base
                 WHERE id=$this->id
            ");
            if (!$objResult) {
if (MY_DEBUG) echo("KnowledgeBase::delete($languageId): Error: Failed to delete the Knowledge Base from the database<br />");
                $return = false;
            }
        }
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
if (MY_DEBUG) echo("KnowledgeBase::delete($languageId): Error: Failed to delete the Knowledge Base language entry from the database<br />");
            return false;
        }
        return $return;
    }


    /**
     * Stores this Knowledge Base in the database.
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
     * Update this Knowledge Base in the database.
     *
     * @return      boolean                     True on success, false otherwise
     * @global      mixed   $objDatabase        Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function update()
    {
        global $objDatabase;
if (MY_DEBUG) { echo("update(): ");var_export($this);echo("<br />"); }
        $query = "
            UPDATE ".DBPREFIX."module_support_knowledge_base
               SET support_category_id=$this->supportCategoryId,
                   `status`=$this->status,
             WHERE id=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        if (!$this->updateLanguage()) {
            return false;
        }
//if (MY_DEBUG) echo("KnowledgeBase::update(): done<br />");
        return true;
    }


    /**
     * Update this Knowledge Bases' language entry in the database.
     *
     * @return      boolean                 True on success, false otherwise
     * @global      mixed   $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function updateLanguage()
    {
        global $objDatabase;

        $query = "
            UPDATE ".DBPREFIX."module_support_knowledge_base_language
               SET `subject`='".contrexx_addslashes($this->subject)."',
                   `body`='".contrexx_addslashes($this->body)."'
             WHERE knowledge_base_id=$this->id
               AND language_id=$this->languageId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            // Maybe a new language has just been added.
            // Try inserting the entry as well.
            return $this->insertLanguage();
        }
//if (MY_DEBUG) echo("KnowledgeBase::update(): done<br />");
        return true;
    }


    /**
     * Insert this Knowledge Base into the database.
     *
     * @return      boolean                 True on success, false otherwise
     * @global      mixed   $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function insert()
    {
        global $objDatabase;
if (MY_DEBUG) { echo("insert(): ");var_export($this);echo("<br />"); }

        $query = "
            INSERT INTO ".DBPREFIX."module_support_knowledge_base (
                   support_category_id,
                   `status`
            ) VALUES (
                   $this->supportCategoryId,
                   $this->status
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $this->id = $objDatabase->Insert_ID();
        // If the Knowledge Base didn't exist, both the language
        // and relations didn't either.
        if (!$this->insertLanguage()) {
            return false;
        }
if (MY_DEBUG) echo("KnowledgeBase::insert(): done<br />");
        return true;
    }


    /**
     * Insert this Knowledge Bases' language entry into the database.
     *
     * @return      boolean                     True on success, false otherwise
     * @global      mixed   $objDatabase        Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function insertLanguage()
    {
        global $objDatabase;

        $query = "
            INSERT INTO ".DBPREFIX."module_support_knowledge_base_language (
                   knowledge_base_id,
                   language_id,
                   `subject`,
                   `body`
            ) VALUES (
                   $this->id,
                   $this->languageId,
                   '".contrexx_addslashes($this->subject)."',
                   '".contrexx_addslashes($this->body)."'
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
//if (MY_DEBUG) echo("KnowledgeBase::update(): done<br />");
        return true;
    }


    /**
     * Select a Knowledge Base by ID from the database.
     *
     * The $languageId parameter determines the language that is picked
     * from the database.
     * @static
     * @param       integer     $id             The Knowledge Base ID
     * @param       integer     $languageId     The language ID
     * @return      Knowledge Base              The Knowledge Base object
     *                                          on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getById($id, $languageId)
    {
        global $objDatabase;

        $query = "
            SELECT *
              FROM ".DBPREFIX."module_support_knowledge_base
        INNER JOIN ".DBPREFIX."module_support_knowledge_base_language
                ON id=knowledge_base_id
             WHERE id=$id
               AND language_id=$languageId
        ";
//if (MY_DEBUG) echo("KnowledgeBase::getById($id, $languageId): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
//if (MY_DEBUG) echo("KnowledgeBase::getById($id, $languageId): objResult: '$objResult'<br />");
        if (!$objResult) {
if (MY_DEBUG) echo("KnowledgeBase::getById($id, $languageId): query failed, objResult: '$objResult', count: ".$objResult->RecordCount()."<br />");
            return false;
        }
        if ($objResult->RecordCount() == 0) {
if (MY_DEBUG) echo("KnowledgeBase::getById($id, $languageId): no result: ".$objResult->RecordCount()."<br />");
            return false;
        }
//if (MY_DEBUG) echo("KnowledgeBase::getById($id, $languageId): ID is ".$objResult->fields['id']."<br />");
        $objKnowledgeBase = new KnowledgeBase(
            contrexx_stripslashes($objResult->fields['subject']),
            contrexx_stripslashes($objResult->fields['body']),
            $objResult->fields['support_category_id'],
            $objResult->fields['language_id'],
            $objResult->fields['status'],
            $objResult->fields['id']
        );
//if (MY_DEBUG) echo("KnowledgeBase::getById($id, $languageId): my ID is ".$objKnowledgeBase->getId()."<br />");
        return $objKnowledgeBase;
    }


    /**
     * Select a Knowledge Base subject by ID from the database.
     *
     * Only the language corresponding to the $languageId parameter is read
     * from the database.
     * @static
     * @param       integer     $id             The Knowledge Base ID
     * @param       integer     $languageId     The language ID
     * @return      string                      The Knowledge Base subject
     *                                          on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getSubjectById($id, $languageId)
    {
        global $objDatabase;

        $query = "
            SELECT subject
              FROM ".DBPREFIX."module_support_knowledge_base
        INNER JOIN ".DBPREFIX."module_support_knowledge_base_language
                ON id=knowledge_base_id
             WHERE id=$id
               AND language_id=$languageId
        ";
//if (MY_DEBUG) echo("KnowledgeBase::getSubjectById($id, $languageId): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
//if (MY_DEBUG) echo("KnowledgeBase::getSubjectById($id, $languageId): objResult: '$objResult'<br />");
        if (!$objResult) {
if (MY_DEBUG) echo("KnowledgeBase::getSubjectById($id, $languageId): query failed, objResult: '$objResult', count: ".$objResult->RecordCount()."<br />");
            return false;
        }
        if ($objResult->RecordCount() == 0) {
if (MY_DEBUG) echo("KnowledgeBase::getSubjectById($id, $languageId): no result: ".$objResult->RecordCount()."<br />");
            return false;
        }
//if (MY_DEBUG) echo("KnowledgeBase::getSubjectById($id, $languageId): ID is ".$objResult->fields['id']."<br />");
        return contrexx_stripslashes($objResult->fields['subject']);
    }


    /**
     * Select Knowledge Base entries by search term from the database.
     *
     * The $languageId parameter determines the language that is picked
     * from the database.
     * @static
     * @param       integer     $id             The Knowledge Base ID
     * @param       integer     $languageId     The language ID
     * @param       integer     $supportCategoryId  The optional Support
     *                                          Category ID limiting the search
     * @return      array                       An array of Knowledge Base IDs
     *                                          on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getByWildcard($term='', $languageId=0, $supportCategoryId=0)
    {
        global $objDatabase;

        $query = "
            SELECT id
              FROM ".DBPREFIX."module_support_knowledge_base
        INNER JOIN ".DBPREFIX."module_support_knowledge_base_language
                ON id=knowledge_base_id
             WHERE 1
               ".($languageId        ? "AND language_id=$languageId" : '')."
               ".($supportCategoryId ? "AND support_category_id=$supportCategoryId" : '')."
               ".($term              ? "AND (subject LIKE '%$term%' OR body LIKE '%$term%')" : '');
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
if (MY_DEBUG) echo("KnowledgeBase::getByWildcard(term=$term, languageId=$languageId, supportCategoryId=$supportCategoryId): query failed: $query<br />");
            return false;
        }
        $arrKnowledgeBaseId = array();
        if ($objResult->RecordCount() == 0) {
if (MY_DEBUG) echo("KnowledgeBase::getByWildcard(term=$term, languageId=$languageId, supportCategoryId=$supportCategoryId): INFO: No results.<br />");
            return $arrKnowledgeBaseId;
        }
        while (!$objResult->EOF) {
            $arrKnowledgeBaseId[] = $objResult->Fields['id'];
            $objResult->MoveNext();
        }
        return $arrKnowledgeBaseId;
    }

}

?>
