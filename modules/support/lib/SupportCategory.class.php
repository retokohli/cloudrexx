<?php

/**
 * Support Category
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */

/*

Database Tables Structure:

CREATE TABLE contrexx_module_support_category (
  id int(11) unsigned NOT NULL auto_increment,
  parent_id int(11) unsigned NOT NULL default '0',
  `status` tinyint(1) unsigned NOT NULL default '1',
  `order` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY (id),
  KEY parent_id (parent_id),
  KEY `status` (`status`)
) ENGINE=MyISAM;

CREATE TABLE contrexx_module_support_category_language (
  support_category_id int(10) unsigned NOT NULL,
  language_id int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (support_category_id, language_id)
) ENGINE=MyISAM;

*/


/**
 * Support Category
 *
 * Every Support Ticket is associated with one of the Support Categories.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */

class SupportCategory
{
    /**
     * Support Category ID
     *
     * From table modules_support_category
     * @var integer
     */
    var $id;

    /**
     * Support Category parent ID
     *
     * From table modules_support_category
     * @var integer
     */
    var $parentId;

    /**
     * Support Category status
     *
     * From table modules_support_category
     * @var boolean
     */
    var $status;

    /**
     * Support Category sorting order
     *
     * From table modules_support_category
     * @var integer
     */
    var $order;

    /**
     * Support Category language ID
     *
     * From table modules_support_category_language
     * @var integer
     */
    var $languageId;

    /**
     * Support Category name
     *
     * From table modules_support_category_language
     * @var string
     */
    var $name;

    /**
     * Support Category names array
     *
     * It has the form
     *  array(language ID => "Support Category name")
     * and is only used by some backend methods.
     * @var array
     */
    var $arrName;


    /**
     * Constructor (PHP4)
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     * @see         __construct()
     */
    function SupportCategory($name, $languageId, $parentId, $status=true,
        $order=0, $id=0)
    {
        $this->__construct($name, $languageId, $parentId, $status,
            $order, $id);
    }

    /**
     * Constructor (PHP5)
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     */
    function __construct($name, $languageId, $parentId, $status=true,
        $order=0, $id=0)
    {
        // No Support Category may be its own parent
        if ($id && $id == $parentId) {
            $id = 0;
        }
        $this->name       = strip_tags($name);
        $this->languageId = intval($languageId);
        $this->parentId   = intval($parentId);
        $this->status     = ($status ? true : false);
        $this->order      = intval($order);
        $this->id         = intval($id);
        $this->arrName    = false;
//if (MY_DEBUG) { echo("SupportCategory::__construct(name=$name, lang=$languageId, parent=$parentId, status=$status, order=$order, id=$id): made ");var_export($this);echo("<br />"); }
    }


    /**
     * Get this Support Category's ID
     * @return  integer     The Support Category ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getId()
    {
        return $this->id;
    }

    /**
     * Get this Support Category's parent ID
     * @return  integer     The Support Category parent ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getParentId()
    {
        return $this->parentId;
    }
    /**
     * Set this Support Category's parent ID
     * @param   integer     The Support Category parent ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setParentId($parentId)
    {
        $this->parentId = intval($parentId);
    }

    /**
     * Get this Support Category's status
     * @return  integer     The Support Category status
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getStatus()
    {
        return $this->status;
    }
    /**
     * Set this Support Category's status
     * @param   integer     The Support Category status
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setStatus($status)
    {
        $this->status = ($status ? true : false);
    }

    /**
     * Get this Support Category's sorting order
     * @return  integer     The Support Category sorting order
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getOrder()
    {
        return $this->order;
    }
    /**
     * Set this Support Category's sorting order
     * @param   integer     The Support Category sorting order
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setOrder($order)
    {
        $this->order = intval($order);
    }

    /**
     * Get this Support Category's name
     * @return  string      The Support Category name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getName()
    {
        return $this->name;
    }
    /**
     * Set this Support Category's name
     * @param   string      The Support Category name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setName($name)
    {
        $this->name = strip_tags($name);
    }

    /**
     * Get the array of names
     *
     * The array looks like this: array( ID => Name, ... ).
     * Note that it is only used if there actually is more than
     * just one entry for a single language.  Otherwise, it
     * defaults to false.
     * @return  mixed       The Support Category name array, if available,
     *                      false otherwise (single language entry)
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getNameArray()
    {
        return $this->arrName;
    }
    /**
     * Set the array of names
     *
     * The array must look like this: array( ID => Name, ... ).
     * If the argument is not an array, or boolean false,
     * the method does nothing.
     * Use the false value to invalidate the array.
     * @param   mixed       The Support Category name array, or false
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setNameArray($arrName)
    {
        if (is_array($arrName)) {
            $this->arrName = array();
            foreach ($arrName as $id => $name) {
                $this->arrName[$id] = strip_tags($name);
            }
        } elseif ($arrName === false) {
            $this->arrName = false;
        }
    }

    /**
     * Get this Support Category's language ID
     * @return  integer     The Support Category language ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getLanguageId()
    {
        return $this->languageId;
    }
    /**
     * Set this Support Category's language ID
     * @param   integer     The Support Category language ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setLanguageId($languageId)
    {
        $this->languageId = intval($languageId);
    }


    /**
     * Clone the Support Category
     *
     * Note that this does NOT create a copy in any way, but simply clears
     * the Support Category ID.  Upon storing this object, a new ID is created.
     * @return      void
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function makeClone() {
        $this->id = '';
    }


    /**
     * Delete this Support Category from the database.
     *
     * If the optional $languageId parameter is set,
     * only the language entry with the appropriate language ID
     * is removed, and the Support Category itself is left untouched.
     * Otherwise, both the Support Category and all its language entries
     * are deleted.
     * Note that all child categories are deleted as well!
     * @return      boolean                 True on success, false otherwise
     * @global      mixed   $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function delete($languageId=0)
    {
        global $objDatabase;
//echo("Debug: Support Category::delete(): entered<br />");

        if (!$this->id) {
if (MY_DEBUG) echo("Support Category::delete($languageId): Error: This Support Category is missing the ID<br />");
            return false;
        }
        // delete child categories first
        $arrChildSupportCategories = $this->getChildren();
        if ($arrChildSupportCategories === false) {
            return false;
        }
        foreach ($arrChildSupportCategories as $objChild) {
            if (!$objChild->delete($languageId)) {
                return false;
            }
        }
        $query = "
            DELETE FROM ".DBPREFIX."module_support_category_language
             WHERE support_category_id=$this->id
               ".($languageId ? "AND language_id=$languageId" : '');
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
if (MY_DEBUG) echo("Support Category::delete($languageId): Error: Failed to delete the Support Category language entry/-ies from the database for ID $this->id!<br />");
            return false;
        }
        // If this record cannot be retrieved again from the database,
        // that means that the last of its language entries has been
        // deleted above.  In that case, remove the main entry as well.
        if (!SupportCategory::getById($this->id, 0, true)) {
            $objResult = $objDatabase->Execute("
                DELETE FROM ".DBPREFIX."module_support_category
                 WHERE id=$this->id
            ");
            if (!$objResult) {
if (MY_DEBUG) echo("Support Category::delete($languageId): Error: Failed to delete the Support Category from the database<br />");
                return false;
            }
        }
        return true;
    }


    /**
     * Returns an array of all child objects
     *
     * @return      array                       All child objects on success,
     *                                          false on failure.
     * @global      mixed   $objDatabase        Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getChildren()
    {
        global $objDatabase;

        if (!$this->id) {
if (MY_DEBUG) echo("getChildren(): Error: This Support Category is missing the ID<br />");
            return false;
        }
        $query = "
            SELECT id
              FROM ".DBPREFIX."module_support_category
        INNER JOIN ".DBPREFIX."module_support_category_language
                ON id=support_category_id
             WHERE parent_id=$this->id
               AND language_id=$this->languageId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $arrChildren = array();
        while (!$objResult->EOF) {
            $arrChildren[] = SupportCategory::getById(
                $objResult->fields['id'],
                $this->languageId
            );
if (MY_DEBUG) echo("getChildren(): parent: $this->id, child: ".$objResult->fields['id']."<br />");
            $objResult->MoveNext();
        }
        return $arrChildren;
    }


    /**
     * Stores this Support Category in the database.
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
     * Update this Support Category in the database.
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
            UPDATE ".DBPREFIX."module_support_category
               SET parent_id=$this->parentId,
                   `status`=".($this->status ? 1 : 0).",
                   `order`=$this->order
             WHERE id=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
//echo("Support Category::update(): done<br />");
        // update the language entry as well
        return $this->updateLanguage();
    }


    /**
     * Update this Support Categories' language entry in the database.
     *
     * @return      boolean                     True on success, false otherwise
     * @global      mixed   $objDatabase        Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function updateLanguage()
    {
        global $objDatabase;

        // Firstly, check whether the record already exists
        $query = "
            SELECT 1
              FROM ".DBPREFIX."module_support_category_language
             WHERE support_category_id=$this->id
               AND language_id=$this->languageId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        // If it doesn't exist, INSERT it.
        if ($objResult->RecordCount() == 0) {
            return $this->insertLanguage();
        }

        // Otherwise, proceed with the UPDATE.
        $query = "
            UPDATE ".DBPREFIX."module_support_category_language
               SET `name`='".contrexx_addslashes($this->name)."'
             WHERE support_category_id=$this->id
               AND language_id=$this->languageId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
//echo("Support Category::update(): done<br />");
        return true;
    }


    /**
     * Insert this Support Category into the database.
     *
     * @return      boolean                     True on success, false otherwise
     * @global      mixed   $objDatabase        Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function insert()
    {
        global $objDatabase;
if (MY_DEBUG) { echo("insert(): ");var_export($this);echo("<br />"); }

        $query = "
            INSERT INTO ".DBPREFIX."module_support_category (
                   parent_id,
                   `status`,
                   `order`
            ) VALUES (
                   $this->parentId,
                   ".($this->status ? 1 : 0).",
                   $this->order
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $this->id = $objDatabase->Insert_ID();
//echo("Support Category::insert(): done<br />");
        // If the support category didn't exist, the language didn't either.
        // Insert the language entry as well.
        return $this->insertLanguage();
    }


    /**
     * Insert this Support Categories' language entry into the database.
     *
     * @return      boolean                     True on success, false otherwise
     * @global      mixed   $objDatabase        Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function insertLanguage()
    {
        global $objDatabase;

        $query = "
            INSERT INTO ".DBPREFIX."module_support_category_language (
                   support_category_id,
                   language_id,
                   `name`
            ) VALUES (
                   $this->id,
                   $this->languageId,
                   '".contrexx_addslashes($this->name)."'
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
//echo("Support Category::update(): done<br />");
        return true;
    }


    /**
     * Store this Support Category's names in all languages in the database.
     *
     * Note that this will only work in conjunction with backend methods
     * that actually set the $arrName array.
     * Also note that this method does not store the Support Category itself!
     * @return      boolean         True on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function storeAllLanguages()
    {
        global $objDatabase;

        $originalLanguageId = $this->languageId;
        $return             = true;
        foreach ($this->arrName as $languageId => $name) {
            $this->languageId = $languageId;
            $this->name = $name;
            // updateLanguage both tries updating, then inserting
            // the language entries.
            if (!$this->updateLanguage()) {
                $return = false;
                break;
            }
        }
        $this->languageId = $originalLanguageId;
        $this->name       = $this->arrName[$originalLanguageId];
        return $return;
    }


    /**
     * Select a Support Category by ID from the database.
     *
     * If the optional $languageId parameter is set and not zero,
     * only the corresponding language is picked along with the
     * Support Category from the database.  Otherwise, all languages
     * are loaded and stored in the $arrName array, and the $name variable
     * is set to the language with the lowest ID.
     * @static
     * @param       integer     $id             The Support Category ID
     * @param       integer     $languageId     The optional language ID
     * @param       boolean     $flagAllLanguages   If true, all available
     *                                          languages are returned.
     *                                          Defaults to false.
     * @return      SupportCategory             The Support Category object
     *                                          on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getById($id, $languageId=0, $flagAllLanguages=false)
    {
        global $objDatabase;

        $query = "
            SELECT *
              FROM ".DBPREFIX."module_support_category
        INNER JOIN ".DBPREFIX."module_support_category_language
                ON id=support_category_id
             WHERE id=$id
               ".($languageId && !$flagAllLanguages
                    ? "AND language_id=$languageId"
                    : 'ORDER BY language_id ASC'
                 );
//echo("SupportCategory::getById($id, $languageId): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
//echo("SupportCategory::getById($id, $languageId): objResult: '$objResult'<br />");
        if (!$objResult) {
if (MY_DEBUG) echo("SupportCategory::getById($id, $languageId): query failed, objResult: '$objResult', count: ".$objResult->RecordCount()."<br />");
            return false;
        }
        if ($objResult->RecordCount() == 0) {
if (MY_DEBUG) echo("SupportCategory::getById($id, $languageId): no result: ".$objResult->RecordCount()."<br />");
            return false;
        }
//echo("SupportCategory::getById($id, $languageId): ID is ".$objResult->fields['id']."<br />");
        $arrName = array();
        $objSupportCategory = false;
        while (!$objResult->EOF) {
            if (   $languageId == $objResult->fields['language_id']
                || ($languageId <= 0 && !$objSupportCategory)) {
                $objSupportCategory = new SupportCategory(
                    contrexx_stripslashes($objResult->fields['name']),
                    $objResult->fields['language_id'],
                    $objResult->fields['parent_id'],
                    $objResult->fields['status'],
                    $objResult->fields['order'],
                    $objResult->fields['id']
                );
            }
            $arrName[$objResult->fields['language_id']] =
                contrexx_stripslashes($objResult->fields['name']);
            $objResult->MoveNext();
        }
        if (count($arrName)) {
            $objSupportCategory->arrName = $arrName;
        }
//echo("SupportCategory::getById($id, $languageId): my ID is ".$objSupportCategory->getId()."<br />");
        return $objSupportCategory;
    }


    /**
     * Select a Support Category name by ID from the database.
     *
     * Only the language corresponding to the $languageId parameter is read
     * from the database.
     * @static
     * @param       integer     $id             The Support Category ID
     * @param       integer     $languageId     The language ID
     * @return      string                      The Support Category name
     *                                          on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getNameById($id, $languageId)
    {
        global $objDatabase;

        $query = "
            SELECT name
              FROM ".DBPREFIX."module_support_category
        INNER JOIN ".DBPREFIX."module_support_category_language
                ON id=support_category_id
             WHERE id=$id
               AND language_id=$languageId
        ";
//echo("SupportCategory::getNameById($id, $languageId): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
//echo("SupportCategory::getNameById($id, $languageId): objResult: '$objResult'<br />");
        if (!$objResult) {
if (MY_DEBUG) echo("SupportCategory::getNameById($id, $languageId): query failed, objResult: '$objResult', count: ".$objResult->RecordCount()."<br />");
            return false;
        }
        if ($objResult->RecordCount() == 0) {
if (MY_DEBUG) echo("SupportCategory::getNameById($id, $languageId): no result: ".$objResult->RecordCount()."<br />");
            return false;
        }
//echo("SupportCategory::getNameById($id, $languageId): ID is ".$objResult->fields['id']."<br />");
        return contrexx_stripslashes($objResult->fields['name']);
    }

}

?>
