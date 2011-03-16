<?php

/**
 * Info Field
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */

/*

Database Tables Structure:

CREATE TABLE contrexx_module_support_info_field (
  id        int(11)          unsigned NOT NULL auto_increment,
  `status`  tinyint(1)       unsigned NOT NULL default '1',
  `order`   int(11)          unsigned NOT NULL default '0',
  `type`    tinyint(2)       unsigned NOT NULL default '1',
  mandatory tinyint(1)       unsigned NOT NULL default '0',
  multiple  tinyint(1)       unsigned NOT NULL default '0',
  PRIMARY KEY (id),
  KEY `status` (`status`)
) ENGINE=MyISAM;

CREATE TABLE contrexx_module_support_info_field_language (
  info_field_id int(10)      unsigned NOT NULL,
  language_id   int(10)      unsigned NOT NULL,
  `name`        varchar(255)          NOT NULL,
  PRIMARY KEY (info_field_id, language_id)
) ENGINE=MyISAM;

CREATE TABLE contrexx_module_support_info_field_rel_support_category (
  info_field_id       int(10) unsigned NOT NULL,
  support_category_id int(10) unsigned NOT NULL,
  PRIMARY KEY (info_field_id, support_category_id)
) ENGINE=MyISAM;

*/

/**
 * Constants defining the various Info Field types.
 * 0: Unknown type.  Used to identify false entries.
 */
define('SUPPORT_INFO_FIELD_TYPE_UNKNOWN', 0);
/**
 * 1: String type.  Is stripped of all tags, characters with HTML
 *    entity equivalents are substituted by these.
 */
define('SUPPORT_INFO_FIELD_TYPE_STRING', 1);
/**
 * 2: String type.  May contain HTML tags and entitites.
 *    No substitutions are made.
 */
define('SUPPORT_INFO_FIELD_TYPE_STRING_HTML', 2);
/**
 * 3: Integer type.  May only contain a leading '+' or '-' and digits.
 */
define('SUPPORT_INFO_FIELD_TYPE_INTEGER', 3);
/**
 * 4: Decimal type.  May only contain a leading '+' or '-', digits,
 *    one decimal point and an integer exponent.
 */
define('SUPPORT_INFO_FIELD_TYPE_DECIMAL', 4);
/**
 * 5: File type.  Represented by an upload button, used to upload files.
 */
define('SUPPORT_INFO_FIELD_TYPE_FILE', 5);
/**
 * Type count.  Keep this up to date!
 */
define('SUPPORT_INFO_FIELD_TYPE_COUNT', 6);
/*
define('SUPPORT_INFO_FIELD_TYPE_', );
*/

/**
 * The names of the Info Field types
 */
global $_ARRAYLANG;
$arrInfoFieldNames = array(
    $_ARRAYLANG['TXT_SUPPORT_INFO_FIELD_TYPE_UNKNOWN'],
    $_ARRAYLANG['TXT_SUPPORT_INFO_FIELD_TYPE_STRING'],
    $_ARRAYLANG['TXT_SUPPORT_INFO_FIELD_TYPE_STRING_HTML'],
    $_ARRAYLANG['TXT_SUPPORT_INFO_FIELD_TYPE_INTEGER'],
    $_ARRAYLANG['TXT_SUPPORT_INFO_FIELD_TYPE_DECIMAL'],
    $_ARRAYLANG['TXT_SUPPORT_INFO_FIELD_TYPE_FILE'],
);


/**
 * InfoField
 *
 * Every Info Field may be associated with any of the Info Fields.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */

class InfoField
{
    /**
     * InfoField ID
     *
     * From table module_support_info_field
     * @var integer
     */
    var $id;

    /**
     * InfoField status
     *
     * From table module_support_info_field
     * @var integer
     */
    var $status;

    /**
     * InfoField sorting order
     *
     * From table module_support_info_field
     * @var integer
     */
    var $order;

    /**
     * InfoField type
     *
     * From table module_support_info_field
     * @var string
     */
    var $type;

    /**
     * InfoField mandatory flag
     *
     * From table module_support_info_field
     * @var integer
     */
    var $mandatory;

    /**
     * InfoField multiple flag
     *
     * From table module_support_info_field
     * @var integer
     */
    var $multiple;

    /**
     * InfoField language ID
     *
     * From table module_support_info_field_language
     * @var integer
     */
    var $languageId;

    /**
     * InfoField name
     *
     * From table module_support_info_field_language
     * @var string
     */
    var $name;

    /**
     * InfoField names array
     *
     * It has the form
     *  array(language ID => "InfoField name")
     * and is only used by some backend methods.
     * @var array
     */
    var $arrName;

    /**
     * Array of Info Fields this InfoField is associated with
     *
     * It has the form
     *  array(index => supportCategoryId)
     * @var array
     */
    var $arrSupportCategoryId;


    /**
     * Constructor (PHP4)
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     * @see         __construct()
     */
    function InfoField(
        $name, $type, $languageId,
        $mandatory=0, $multiple=0, $status=1, $order=0, $id=0
    ) {
        $this->__construct(
            $name, $type, $languageId,
            $mandatory, $multiple, $status, $order, $id
        );
    }

    /**
     * Constructor (PHP5)
     *
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     */
    function __construct(
        $name, $type, $languageId,
        $mandatory=0, $multiple=0, $status=1, $order=0, $id=0
    ) {
        $this->name       = strip_tags($name);
        $this->type       = intval($type);
        $this->languageId = intval($languageId);
        $this->mandatory  = ($mandatory ? true : false);
        $this->multiple   = ($multiple ? true : false);
        $this->status     = ($status ? true : false);
        $this->order      = intval($order);
        $this->id         = intval($id);
        $this->arrName    = false;
        // No Support Categories are associated with this yet.
        $this->arrSupportCategoryId = array();
//if (MY_DEBUG) { echo("__construct(name=$name, type=$type, lang=$languageId, mandatory=$mandatory, multiple=$multiple, status=$status, order=$order, id=$id): made ");var_export($this);echo("<br />"); }
    }


    /**
     * Get this InfoField's ID
     * @return  integer     The InfoField ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getId()
    {
        return $this->Id;
    }

    /**
     * Get this InfoField's status
     * @return  integer     The InfoField status
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getStatus()
    {
        return $this->status;
    }
    /**
     * Set this InfoField's status
     * @param   integer     The InfoField status
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setStatus($status)
    {
        $this->status = ($status ? true : false);
    }

    /**
     * Get this InfoField's sorting order
     * @return  integer     The InfoField sorting order
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getOrder()
    {
        return $this->order;
    }
    /**
     * Set this InfoField's sorting order
     * @param   integer     The InfoField sorting order
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setOrder($order)
    {
        $this->order = intval($order);
    }

    /**
     * Get the mandatory flag
     * @return  integer     The mandatory flag
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getMandatory()
    {
        return $this->mandatory;
    }
    /**
     * Set the mandatory flag
     * @param   mixed     $mandatory    Mandatory flag, evaluated as boolean
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setMandatory($mandatory)
    {
        $this->mandatory = ($mandatory ? true : false);
    }

    /**
     * Get the multiple flag
     * @return  integer     The multiple flag
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getMultiple()
    {
        return $this->multiple;
    }
    /**
     * Set the multiple flag
     * @param   mixed     $multiple     Multiple flag, evaluated as boolean
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setMultiple($multiple)
    {
        $this->multiple = ($multiple ? true : false);
    }


    /**
     * Get this InfoField's name
     * @return  string      The InfoField name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getName()
    {
        return $this->name;
    }
    /**
     * Set this InfoField's name
     * @param   string      The InfoField name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setName($name)
    {
        $this->name = strip_tags($name);
    }

    /**
     * Get this InfoField's type
     * @return  integer     The InfoField type
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getType()
    {
        return $this->type;
    }
    /**
     * Set this InfoField's type
     * @param   integer     The InfoField type
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setType($type)
    {
        $this->type = intval($type);
    }

    /**
     * Get this InfoField's language ID
     * @return  integer     The InfoField language ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getLanguageId()
    {
        return $this->languageId;
    }
    /**
     * Set this InfoField's language ID
     * @param   integer     The InfoField language ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setLanguageId($languageId)
    {
        $this->languageId = intval($languageId);
    }


    /**
     * Returns the array with the names in all available languages .
     *
     * The array looks like:  array( languageId => 'name', ... )
     * @return  array       The array of names
     */
    function getNameArray()
    {
        return $this->arrName;
    }


    /**
     * Returns the name of the InfoField type given as a number
     *
     * @param   integer     $type       The InfoField type
     * @return  string                  The InfoField type name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getTypeString($type)
    {
        global $arrInfoFieldNames;

        return $arrInfoFieldNames[$type];
    }


    /**
     * Clone the InfoField
     *
     * Note that this does NOT create a copy in any way, but simply clears
     * the InfoField ID.  Upon storing this object, a new ID is created.
     * @return      void
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function makeClone() {
        $this->id = '';
    }


    /**
     * Delete this InfoField from the database.
     *
     * If the optional $languageId parameter is set,
     * only the language entry with the appropriate language ID
     * is removed, and the InfoField itself is left untouched.
     * Otherwise, both the InfoField and all its language entries
     * are deleted.
     * @return      boolean                 True on success, false otherwise
     * @global      mixed   $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function delete($languageId=0)
    {
        global $objDatabase;
//if (MY_DEBUG) echo("Debug: InfoField::delete(): entered<br />");

        if (!$this->id) {
if (MY_DEBUG) echo("InfoField::delete($languageId): Error: This InfoField is missing the ID<br />");
            return false;
        }
        $query = "
            DELETE FROM ".DBPREFIX."module_support_info_field_language
             WHERE info_field_id=$this->id
               ".($languageId ? "AND language_id=$languageId" : '');
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
if (MY_DEBUG) echo("InfoField::delete($languageId): Error: Failed to delete the InfoField language entry from the database<br />");
            return false;
        }
        if (!InfoField::getById($this->id, 0, true)) {
            $objResult = $objDatabase->Execute("
                DELETE FROM ".DBPREFIX."module_support_info_field_rel_support_category
                 WHERE info_field_id=$this->id
            ");
            if (!$objResult) {
if (MY_DEBUG) echo("InfoField::delete($languageId): Error: Failed to delete the InfoField-SupportCategory relations from the database<br />");
                return false;
            }
            $objResult = $objDatabase->Execute("
                DELETE FROM ".DBPREFIX."module_support_info_field
                 WHERE id=$this->id
            ");
            if (!$objResult) {
if (MY_DEBUG) echo("InfoField::delete($languageId): Error: Failed to delete the InfoField from the database<br />");
                return false;
            }
        }
        return true;
    }


    /**
     * Stores this InfoField in the database.
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
     * Update this InfoField in the database.
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
            UPDATE ".DBPREFIX."module_support_info_field
               SET `status`=".($this->status ? 1 : 0).",
                   `order`=$this->order,
                   `type`=$this->type,
                   mandatory=".($this->mandatory ? 1 : 0).",
                   multiple=".($this->multiple ? 1 : 0)."
             WHERE id=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        if (!$this->updateLanguage()) {
            return false;
        }
        if (!$this->updateRelations()) {
            return false;
        }
//if (MY_DEBUG) echo("InfoField::update(): done<br />");
        return true;
    }


    /**
     * Update this Info Fields' language entry in the database.
     *
     * @return      boolean                 True on success, false otherwise
     * @global      mixed   $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function updateLanguage()
    {
        global $objDatabase;

        // Firstly, check whether the record already exists
        $query = "
            SELECT 1
              FROM ".DBPREFIX."module_support_info_field_language
             WHERE info_field_id=$this->id
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
            UPDATE ".DBPREFIX."module_support_info_field_language
               SET `name`='".contrexx_addslashes($this->name)."'
             WHERE info_field_id=$this->id
               AND language_id=$this->languageId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
//if (MY_DEBUG) echo("InfoField::update(): done<br />");
        return true;
    }


    /**
     * Update this Info Fields' relations entries in the database.
     *
     * @return      boolean                 True on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function updateRelations()
    {
        // Get the IDs of all Support Categories
        // this object is associated with from the database.
        $arrSupportCategoryId = $this->getSupportCategoryIdArray();

        // Compare them to the ones present in this object
        sort($arrSupportCategoryId);
        sort($this->arrSupportCategoryId);

        $supportCategoryIdOld = current($arrSupportCategoryId);
        $supportCategoryIdNew = current($this->arrSupportCategoryId);
        // While there are elements in one or both arrays
        while (   $supportCategoryIdNew !== false
               && $supportCategoryIdNew !== false) {
            if ($supportCategoryIdNew == $supportCategoryIdNew) {
                // The IDs are identical, go on to the next pair
                $supportCategoryIdOld = next($arrSupportCategoryId);
                $supportCategoryIdNew = next($this->arrSupportCategoryId);
            }
            if (   $supportCategoryIdNew === false
                || $supportCategoryIdOld < $supportCategoryIdNew) {
                // The old ID is missing in the current object -- delete it.
                if (!$this->deleteRelation($supportCategoryIdOld)) {
                    return false;
                }
                $supportCategoryIdOld = next($arrSupportCategoryId);
                continue;
            }
            if (   $supportCategoryIdOld === false
                || $supportCategoryIdOld > $supportCategoryIdNew) {
                // The new ID is missing in the database -- add it.
                if (!$this->addRelation($supportCategoryIdNew)) {
                    return false;
                }
                $supportCategoryIdNew = next($this->arrSupportCategoryId);
                continue;
            }
        }
if (MY_DEBUG) echo("InfoField::updateRelations(): done<br />");
        return true;
    }


    /**
     * Delete an Info Field - Support Category relation from the database.
     *
     * @param   integer $supportCategoryId  The Support Category ID
     * @return  boolean                     True on success, false otherwise
     * @global      mixed   $objDatabase    Database object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function deleteRelation($supportCategoryId)
    {
        global $objDatabase;

        $query = "
            DELETE FROM ".DBPREFIX."module_support_info_field_rel_support_category
             WHERE info_field_id=$this->id
               AND support_category_id=$supportCategoryId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        return true;
    }


    /**
     * Add an Info Field - Support Category relation to the database.
     *
     * @param   integer $supportCategoryId  The Support Category ID
     * @return  boolean                     True on success, false otherwise
     * @global  mixed   $objDatabase        Database object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function addRelation($supportCategoryId)
    {
        global $objDatabase;

        $query = "
            INSERT INTO ".DBPREFIX."module_support_info_field_rel_support_category (
                   info_field_id,
                   support_category_id
            ) VALUES (
                    $this->id,
                    $supportCategoryId
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        return true;
    }


    /**
     * Returns an array with all Support Category IDs this Info Field
     * is associated with.
     * @return  array               Array of Support Category IDs on success,
     *                              false otherwise.
     * @global      mixed   $objDatabase    Database object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getSupportCategoryIdArray()
    {
        global $objDatabase;

        // Get all relations from the database
        $query = "
            SELECT support_category_id
              FROM ".DBPREFIX."module_support_info_field_rel_support_category
             WHERE info_field_id=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $arrSupportCategoryId = array();
        while (!$objResult->EOF) {
            $arrSupportCategoryId[] = $objResult->Fields['support_category_id'];
            $objResult->MoveNext();
        }
        return $arrSupportCategoryId;
    }


    /**
     * Insert this InfoField into the database.
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
            INSERT INTO ".DBPREFIX."module_support_info_field (
                   `status`,
                   `order`,
                   `type`,
                   mandatory,
                   multiple
            ) VALUES (
                   ".($this->status ? 1 : 0).",
                   $this->order,
                   $this->type,
                   ".($this->mandatory ? 1 : 0).",
                   ".($this->multiple ? 1 : 0)."
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $this->id = $objDatabase->Insert_ID();
        // If the Info Field didn't exist, both the language
        // and relations didn't either.
        if (!$this->insertLanguage()) {
            return false;
        }
        // Note that updateRelations() works for both INSERTs and UPDATEs!
        if (!$this->updateRelations()) {
            return false;
        }
if (MY_DEBUG) echo("InfoField::insert(): done<br />");
        return true;
    }


    /**
     * Insert this Info Fields' language entry into the database.
     *
     * @return      boolean                     True on success, false otherwise
     * @global      mixed   $objDatabase        Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function insertLanguage()
    {
        global $objDatabase;

        $query = "
            INSERT INTO ".DBPREFIX."module_support_info_field_language (
                   info_field_id,
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
//if (MY_DEBUG) echo("InfoField::update(): done<br />");
        return true;
    }


    /**
     * Store this InfoField's names in all languages in the database.
     *
     * Note that this will only work in conjunction with backend methods
     * that actually set the $arrName array.
     * Also note that this method does not store the InfoField itself!
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
            // missing language entries.
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
     * Select a InfoField by ID from the database.
     *
     * If the optional $languageId parameter is set and not zero,
     * only the corresponding language is picked along with the
     * InfoField from the database.  Otherwise, all languages
     * are loaded and stored in the $arrName array, and the $name variable
     * is set to the language with the lowest ID.
     * Also initializes the array of associated Support Category IDs.
     * @static
     * @param       integer     $id             The InfoField ID
     * @param       integer     $languageId     The optional language ID
     * @return      InfoField                   The InfoField object
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
              FROM ".DBPREFIX."module_support_info_field
        INNER JOIN ".DBPREFIX."module_support_info_field_language
                ON id=info_field_id
             WHERE id=$id
        ".($languageId && !$flagAllLanguages
            ? "AND language_id=$languageId"
            : 'ORDER BY language_id ASC'
        );
//if (MY_DEBUG) echo("InfoField::getById($id, $languageId): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
//if (MY_DEBUG) echo("InfoField::getById($id, $languageId): objResult: '$objResult'<br />");
        if (!$objResult) {
if (MY_DEBUG) echo("InfoField::getById($id, $languageId): query failed, objResult: '$objResult', count: ".$objResult->RecordCount()."<br />");
            return false;
        }
        if ($objResult->RecordCount() == 0) {
if (MY_DEBUG) echo("InfoField::getById($id, $languageId): no result: ".$objResult->RecordCount()."<br />");
            return false;
        }
//if (MY_DEBUG) echo("InfoField::getById($id, $languageId): ID is ".$objResult->fields['id']."<br />");
        $arrName = array();
        $objInfoField = false;
        while (!$objResult->EOF) {
            if (   $languageId == $objResult->fields['language_id']
                || ($languageId <= 0 && !$objInfoField)) {
                $objInfoField = new InfoField(
                    contrexx_stripslashes($objResult->fields['name']),
                    $objResult->fields['type'],
                    $objResult->fields['language_id'],
                    $objResult->fields['mandatory'],
                    $objResult->fields['multiple'],
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
            $objInfoField->arrName = $arrName;
        }
        // Get the array of associated Support Category IDs
        $objInfoField->arrSupportCategoryId =
            $objInfoField->getSupportCategoryIdArray();
        if (!$objInfoField->arrSupportCategoryId) {
if (MY_DEBUG) { echo("InfoField::getById($id): WARNING: Failed to get related Support Categories!<br />"); }
            // Set the array variable to false to indicate that
            // it must be ignored.  The InfoField is used in all
            // Support Categories.
            $objInfoField->arrSupportCategoryId = false;
        }
//if (MY_DEBUG) echo("InfoField::getById($id, $languageId): my ID is ".$objInfoField->getId()."<br />");
        return $objInfoField;
    }


    /**
     * Verify that the given value fits the field type
     *
     * @param   mixed   $fieldValue     The value to test
     * @return  boolean                 True if the value fits, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @todo    Improve the checks.
     */
    function verify($fieldValue)
    {
        switch ($this->type) {
          case 'SUPPORT_INFO_FIELD_TYPE_STRING':
          case 'SUPPORT_INFO_FIELD_TYPE_STRING_HTML':
          case 'SUPPORT_INFO_FIELD_TYPE_FILE':
            return true;
          case 'SUPPORT_INFO_FIELD_TYPE_INTEGER':
            if ($fieldValue == intval($fieldValue)) {
                return true;
            }
          case 'SUPPORT_INFO_FIELD_TYPE_DECIMAL':
            if ($fieldValue == doubleval($fieldValue)) {
                return true;
            }
          case 'SUPPORT_INFO_FIELD_TYPE_UNKNOWN':
          default:
        }
        return false;
    }
}

?>
