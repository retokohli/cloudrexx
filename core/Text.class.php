<?php

/**
 * Text (core version)
 * @version     2.2.0
 * @package     contrexx
 * @subpackage  core
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 */

/**
 * Changes in version 2.2.0:
 * - Module ID *MUST NOT* be NULL, but zero if unused
 * - Changed integer 'key_id' to string 'key' (TINYTEXT)
 */

/**
 * Text
 *
 * Includes access methods and data layer.
 * Do not, I repeat, do not access private fields, or even try
 * to access the database directly!
 * @version     2.2.0
 * @package     contrexx
 * @subpackage  core
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 */
class Text
{
    /**
     * @var     integer         $id                 The object ID
     * @access  private
     */
    private $id = 0;

    /**
     * @var     integer         $lang_id            The language ID
     * @access  private
     */
    private $lang_id = 0;

    /**
     * @var     integer         $module_id          The optional module ID
     * @access  private
     */
    private $module_id = null;

    /**
     * @var     string          $key                The optional key
     * @access  private
     */
    private $key = null;

    /*
     * @var     string          $reference          The optional reference name
     * @access  private
    private $reference = null;
     */

    /**
     * @var     string          $text               The content
     * @access  private
     */
    private $text = '';


    /**
     * Create a Text object
     *
     * @access  public
     * @param   string      $text             The content
     * @param   integer     $lang_id          The language ID
     * @param   integer     $module_id        The module ID
     * @param   string      $key              The key
     * @param   integer     $text_id          The optional Text ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function __construct($text, $lang_id, $module_id, $key, $text_id=0)
    {
        $this->text = $text;
        $this->lang_id = $lang_id;
        $this->module_id = $module_id;
        $this->key = $key;
        $this->id = $text_id;
    }


    /**
     * Get the ID
     * @return  integer                             The Text ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getId()
    {
        return $this->id;
    }
    /**
     * Set the ID -- NOT ALLOWED
     * See {@link makeClone()}
     */

    /**
     * Get the Language ID
     * @return  integer                             The Language ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getLanguageId()
    {
        return $this->lang_id;
    }
    /**
     * Set the Language ID
     * @param   integer         $lang_id             The Language ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setLanguageId($lang_id)
    {
        $this->lang_id = intval($lang_id);
    }

    /**
     * Get the Module ID
     * @return  integer                             The Module ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getModuleId()
    {
        return $this->module_id;
    }
    /*
     * Set the Module ID
     * NOT ALLOWED!  Set this upon creation.
     * @param   integer         $module_id             The Module ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
    function setModuleId($module_id)
    {
        $this->module_id = intval($module_id);
    }
     */

    /**
     * Get the key
     * @return  string                              The key
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getKey()
    {
        return $this->key;
    }
    /*
     * Set the key
     * NOT ALLOWED!  Set this upon creation.
     * @param   string          $key                The key
     * @author      Reto Kohli <reto.kohli@comvation.com>
    function setKey($key)
    {
        $this->key = $key;
    }
     */

    /**
     * Get the content text
     * @return  string                              The content text
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getText()
    {
        return $this->text;
    }
    /**
     * Set the content text
     *
     * The string value is used as-is.
     * Nothing is checked, trimmed nor stripped.  Mind your step!
     * @param   string          $text               The content text
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setText($text)
    {
        $this->text = $text;
    }


    /**
     * Clone the object
     *
     * Note that this does NOT create a copy in any way, but simply clears
     * the Text ID.  Upon storing this Text, a new ID is created.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function makeClone()
    {
        $this->id = 0;
    }


    /**
     * Delete this object from the database.
     *
     * Deletes the Text record in the selected language, if specified.
     * If the optional $lang_id parameter is missing or zero, all
     * languages are removed.
     * Note that you *SHOULD NOT* call this from outside the module
     * classes as all of these *SHOULD* take care of cleaning up by
     * themselves.
     * Remark:  There is no point really in deleting single Text records in
     * one particular language.  See {@link deleteLanguage()} for details
     * on nuking entire populace.
     * @static
     * @global      mixed       $objDatabase    Database object
     * @param       integer     $text_id        The object ID
     * @param       integer     $lang_id        The optional language ID
     * @return      boolean                     True on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function deleteById($text_id, $lang_id=0)
    {
        global $objDatabase;

//echo("Text::deleteById($text_id, $lang_id): Entered<br />");
        if (!$text_id) {
//echo("Text::deleteById($text_id, $lang_id): Empty ID<br />");
            return true;
        }
        $query = "
            DELETE FROM `".DBPREFIX."core_text`
             WHERE `id`=$text_id
               ".($lang_id == 0 ? '' : "AND `lang_id`=$lang_id");
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
//die("Text::deleteById($text_id, $lang_id): Query failed<br />$query");
            return self::errorHandler();
        }
//echo("Text::deleteById($text_id, $lang_id): Deleted<br />");
        return true;
    }


    /**
     * Delete all Text with the given language ID from the database.
     *
     * This is dangerous stuff -- mind your step!
     * @static
     * @global      mixed       $objDatabase    Database object
     * @param       integer     $lang_id        The language ID
     * @return      boolean                     True on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function deleteLanguage($lang_id)
    {
        global $objDatabase;

        if (!$lang_id) return false;
        $query = "DELETE FROM `".DBPREFIX."core_text` WHERE `lang_id`=$lang_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        return true;
    }


    /**
     * Test whether a record with these Text and language IDs is already present
     * in the database.
     * @return  boolean                     True if the record exists,
     *                                      false otherwise
     * @global  mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function recordExists()
    {
        global $objDatabase;

        if ($this->id == 0) return false;
        $query = "
            SELECT 1
              FROM `".DBPREFIX."core_text`
             WHERE `id`=$this->id
               AND `lang_id`=$this->lang_id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        if ($objResult->RecordCount() == 1) return true;
        return false;
    }


    /**
     * Stores the object in the database.
     *
     * Either updates or inserts the object, depending on the outcome
     * of the call to {@link recordExists()}.
     * Calling {@link recordExists()} is necessary, as there may be
     * no record in the current language, although the Text ID is valid.
     * @return      boolean     True on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function store()
    {
        if ($this->id && $this->recordExists()) return $this->update();
        return $this->insert();
    }


    /**
     * Update this object in the database.
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function update()
    {
        global $objDatabase;

//echo("Text::update(): ".var_export($this, true)."<br />");

        $query = "
            UPDATE `".DBPREFIX."core_text`
            SET `module_id`=".($this->module_id ? $this->module_id : 'NULL').",
                `key`='".addslashes($this->key)."',
                `text`='".addslashes($this->text)."'
          WHERE `id`=$this->id
            AND `lang_id`=$this->lang_id
        ";
// Removed: `reference`='".(empty($this->reference) ? 'NULL' : addslashes($this->reference))."',
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
//DBG::log("Text::update(): Failed to update ".var_export($this, true));
            return self::errorHandler();
        }
        return true;
    }


    /**
     * Insert this object into the database.
     * @return      mixed                       The ID of the inserted record
     *                                          on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function insert()
    {
        global $objDatabase;

        if (!$this->lang_id) {
//DBG::log("Text::insert(): Invalid language ID ".var_export($this, true));
            return false;
        }
        if (empty($this->id)) $this->id = self::nextId();
        if (empty($this->id)) {
//DBG::log("Text::insert(): Invalid next ID ".var_export($this, true));
            return false;
        }
        $query = "
            INSERT INTO `".DBPREFIX."core_text` (
                ".($this->id ? '`id`, ' : '')."
                `lang_id`, `module_id`, `key`,
                `text`
            ) VALUES (
                ".($this->id ? "$this->id, " : '')."
                $this->lang_id,
                ".($this->module_id ? $this->module_id : '0').",
                '".addslashes($this->key)."',
                '".addslashes($this->text)."'
            )
        ";
// Removed:
// `reference`,
// ".(empty($this->reference) ? 'NULL' : "'".addslashes($this->reference)."'")."
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
//DBG::log("Text::insert(): Failed to update ".var_export($this, true));
            return self::errorHandler();
        }
        if ($this->id == 0) $this->id = $objDatabase->Insert_ID();
        return $this->id;
    }


    /**
     * Add a new Text object directly to the database table
     *
     * Mind that this method uses the  MODULE_ID global constant.
     * This is but a handy shortcut for those in the know.
     * @param   string    $text       The text string
     * @param   string    $key        The key
     * @param   string    $text       The optional language ID,
     *                                defaults to FRONTEND_LANG_ID
     * @return  integer               The object ID on success, false otherwise
     */
    static function add($text, $key, $lang_id=0)
    {
        if (empty($lang_id)) $lang_id = FRONTEND_LANG_ID;
        $objText = new Text($text, $lang_id, MODULE_ID, $key);
        if ($objText->store()) return $objText->getId();
        return false;
    }


    /**
     * Returns the next available ID
     *
     * Called by {@link insert()}.
     * @return  integer               The next ID on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function nextId()
    {
        global $objDatabase;

        $query = "SELECT MAX(`id`) AS `id` FROM `".DBPREFIX."core_text`";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->EOF) return self::errorHandler();
        // This will also work for an empty database table,
        // when mySQL merrily returns NULL in the ID field.
        return 1+$objResult->fields['id'];
    }


    /**
     * Select an object by ID from the database.
     *
     * Note that if the $lang_id parameter is zero, this method picks the
     * first language of the Text that it encounters.  This is useful
     * for displaying records in languages which haven't been edited yet.
     * If the Text cannot be found for the language ID given, the first
     * language encountered is returned.
     * If no record is found for the given ID, creates a new object
     * with a warning message and returns it.
     * Note that in the last case, neither the module nor the key
     * are set and remain at their default (null) value.  You *MUST*
     * set them to the desired values before storing the object,
     * or you may never find that record again!
     * @static
     * @param       integer     $id             The object ID
     * @param       integer     $lang_id        The language ID
     * @return      Text                        The object on success,
     *                                          false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function getById($text_id, $lang_id)
    {
        global $objDatabase; //, $_CORELANG;

        $objText = false;
        if (intval($text_id)) {
            $query = "
                SELECT `id`, `lang_id`, `text`, `module_id`, `key`
                  FROM `".DBPREFIX."core_text`
                 WHERE `id`=$text_id
                   ".($lang_id ? "AND `lang_id`=$lang_id" : '');
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return self::errorHandler();
            if ($objResult->RecordCount()) {
                $objText = new Text(
                    $objResult->fields['text'],
                    $objResult->fields['lang_id'],
                    $objResult->fields['module_id'],
                    $objResult->fields['key'],
                    $objResult->fields['id']
                );
                // Optionally mark replacement Texts
//                if (!$lang_id) {
//                    $objText->markDifferentLanguage();
//                }
            } else {
                if ($lang_id) {
                    $objText = self::getById($text_id, 0);
                    if ($objText) {
                        $objText->lang_id = $lang_id;
//                        return $objText;
                    }
                }
            }
        }
        if (!$objText) {
//echo("Text::getById($text_id, $lang_id): Missing text ID $text_id, returning new<br />");
            $objText = new Text(
                '', //$_CORELANG['TXT_CORE_TEXT_MISSING'],
                $lang_id, null, null, $text_id
            );
        }
        // Mark Text not present in the selected language
        //$objText->markDifferentLanguage($lang_id);
        return $objText;
    }


    /**
     * Select an object by its key from the database
     *
     * This method is intended to provide a means to store arbitrary
     * texts in various languages that don't need to be referred to by
     * an ID, but some distinct key.  If the key is not unique, however,
     * you will not be able to retrieve any particular of those records,
     * but only the first one that is encountered.
     * Note that if the $lang_id parameter is zero, this method picks the
     * first language of the Text that it encounters.  This is useful
     * for displaying records in languages which haven't been edited yet.
     * If the Text cannot be found for the language ID given, the first
     * language encountered is returned.
     * If no record is found for the given key, creates a new object
     * with a warning message and returns it.
     * Note that in the last case, neither the module nor the text ID
     * are set and remain at their default (null) value.  You should
     * set them to the desired values before storing the object.
     * @static
     * @param       integer     $key            The key, must not be empty
     * @param       integer     $lang_id        The language ID
     * @return      Text                        The object on success,
     *                                          false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function getByKey($key, $lang_id)
    {
        global $objDatabase, $_CORELANG;

        if (empty($key)) return false;
        $query = "
            SELECT `id`, `lang_id`, `text`, `module_id`, `key`
              FROM `".DBPREFIX."core_text`
             WHERE `key`='".addslashes($key)."'
               ".($lang_id ? "AND `lang_id`=$lang_id" : '');
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        if ($objResult->RecordCount() == 0) {
            if ($lang_id) {
                $objText = self::getByKey($key, 0);
                if ($objText) {
                    $objText->lang_id = $lang_id;
                    return $objText;
                }
            }
            // Return "* Text missing *".
            // In order to avoid this being shown, check either
            // the id of the object returned, which is non-empty
            // for any valid Text.
            return new Text(
                $_CORELANG['TXT_CORE_TEXT_MISSING'],
                $lang_id, null, $key, null
            );
        }
        $objText = new Text(
            $objResult->fields['text'],
            $objResult->fields['lang_id'],
            $objResult->fields['module_id'],
            $objResult->fields['key'],
            $objResult->fields['id']
        );
        // Mark Text not present in the selected language
        //$objText->markDifferentLanguage($lang_id);
        return $objText;
    }


    /**
     * Replace or insert the Text record
     *
     * If the Text ID is specified, looks for the same record in the
     * given language, or any other language if that is not found.
     * If the Text ID is false, but the key is valid, looks for a record
     * with the same key.
     * Set $text_id to null or 0 (zero) to force the record to be inserted,
     * *NOT* false!
     * If no record is found this way, a new object is created.
     * The parameters are applied, and the Text is then stored.
     * The optional arguments $module_id and $key are ignored if empty.
     * @param   integer     $text_id        The Text ID, or false
     * @param   integer     $lang_id        The language ID
     * @param   string      $strText        The text
     * @param   integer     $module_id      The optional module ID
     * @param   string      $key            The optional key
     * @return  integer                     The Text ID on success,
     *                                      false otherwise
     */
    static function replace(
        $text_id=false, $lang_id, $strText, $module_id=null, $key=null
    ) {
        if ($text_id === false) {
            $objText = self::getByKey($key, $lang_id);
//echo("replace($text_id, $lang_id, $strText, $module_id, $key): got by key: ".$objText->getText()."<br />");
        } else {
            $objText = Text::getById($text_id, $lang_id);
//echo("replace($text_id, $lang_id, $strText, $module_id, $key): got by ID: ".$objText->getText()."<br />");
        }
        if (   !$objText || !$objText->getLanguageId()
// TODO:  This is not well defined yet
//            || !$objText->getModuleId() || !$objText->getKey())
        ) $objText = new Text('', 0, 0, '', $text_id);
        $objText->setText($strText);
        $objText->setLanguageId($lang_id);
        if (isset($module_id)) $objText->module_id = intval($module_id);
        if (isset($key)) $objText->key = $key;
//echo("Storing Text: ".var_export($objText, true)."<br />");
        if (!$objText->store()) {
die("Text::replace($text_id, $lang_id, $strText, $module_id, $key): Error: failed to store Text<br />");
            return false;
        }
        return $objText->id;
    }


    /**
     * Deletes the Text record from the database
     *
     * If the optional $all_languages parameter evaluates to boolean true,
     * all records with the same ID are deleted.  Otherwise, only the
     * language of this object is affected.
     * @param   boolean   $all_languages    Delete all languages if true
     * @return  boolean                     True on success, false otherwise
     */
    function delete($all_languages=false)
    {
        global $objDatabase;

        if (empty($this->id) || empty($this->lang_id)) return false;
        $query = "
            DELETE FROM `".DBPREFIX."core_text`
             WHERE `id`=$this->id".
             ($all_languages ? '' : " AND `lang_id`=$this->lang_id");
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        return true;
    }


    /**
     * Deletes the Text records with the given key from the database
     *
     * Use with due care!
     * @param   string    $key              The key to match
     * @return  boolean                     True on success, false otherwise
     */
    static function deleteByKey($key)
    {
        global $objDatabase;

        if (empty($key)) return false;
        $query = "
            DELETE FROM `".DBPREFIX."core_text`
             WHERE `key`='".addslashes($key)."'";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        return true;
    }


    /**
     * Returns an array of SQL snippets to include the selected Text records
     * in the query.
     *
     * The array returned looks as follows:
     *  array(
     *    'id'    => Text ID field alias, like "text_#_id"
     *    'text'  => Text field alias, autocreated like "text_#_text",
     *               or the alias that you provided in the $alias parameter.
     *               Use the alias if you need to sort the result by that
     *               text content.
     *    'name'  => Text ID foreign key field name as specified in the
     *               $field_id_name parameter, usually like "text_*_id"
     *    'alias' => Automatically created table alias for the text table,
     *               like "text_#"; # is a unique integer index
     *    'field' => Field snippet to be included in the SQL SELECT, uses
     *               aliased field names for the id ("text_#_id") and text
     *               ("text_#_text") fields.
     *               Note that a leading comma is already included!
     *    'join'  => SQL JOIN snippet, the LEFT JOIN with the core_text table
     *               and conditions
     *  )
     * The '#' is replaced by a unique integer number.
     * The '*' may be any descriptive part of the name that disambiguates
     * multiple foreign keys in a single table, like 'name', or 'value'.
     * Note that the $lang_id parameter is mandatory and *MUST NOT* be
     * emtpy.  Any of $module_id, $key, $alias, or $text_ids may be false,
     * in which case they are ignored.
     * IMPORTANT NOTE: For the time being, the $module_id is ignored entirely
     * and thus not present in the resulting SQL at all!
     * This allows the use of content from different modules to be shown
     * on the same page, as this value is usually determined by the global
     * MODULE_ID constant.  A proper solution is desired, though.
     * @static
     * @param       string      $field_id_name  The name of the text ID
     *                                          foreign key field
     * @param       integer     $lang_id        The language ID
     * @param       integer     $module_id      The optional module ID, or false
     * @param       string      $key            The optional key, or false
     * @param       string      $alias          The optional text field alias,
     *                                          or false
     * @param       integer     $text_ids       The optional comma separated
     *                                          list of Text IDs, or false
     * @return      array                       The array with SQL code parts
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function getSqlSnippets(
        $field_id_name, $lang_id,
        $module_id=false, $key=false, $alias=false, $text_ids=false
    ) {
        static $table_alias_index = 0;

        if (empty($field_id_name) || empty($lang_id)) return false;
        $table_alias = 'text_'.++$table_alias_index;
        $field_id = $table_alias.'_id';
        $field_text = ($alias ? $alias : $table_alias.'_text');
        $query_field =
            ', '.$field_id_name.
            ', `'.$table_alias.'`.`id`   AS `'.$field_id.'`'.
            ', `'.$table_alias.'`.`text` AS `'.$field_text.'`';
        $query_join =
            ' LEFT JOIN `'.DBPREFIX.'core_text` as `'.$table_alias.'`'.
            ' ON `'.$table_alias.'`.`id`='.$field_id_name.
            ' AND `'.$table_alias.'`.`lang_id`='.$lang_id.
//            ($module_id !== false ? " AND `$table_alias`.`module_id`=$module_id"       : '').
//            ($module_id ? " AND `$table_alias`.`module_id`=$module_id"       : '').
            ($key       !== false ? " AND `$table_alias`.`key`='".addslashes($key)."'" : '').
            ($text_ids  !== false ? " AND `$table_alias`.`id` IN ($text_ids)"          : '');
//echo("Text::getSqlSnippets(): got name /$field_id_name/, made ");
            // Remove table name, dot and backticks, if any
            $field_id_name = preg_replace('/(?:`\w*`\.`)?(\w+)`?/', '$1', $field_id_name);
//echo("/$field_id_name/<br />");
        return array(
            'id'    => $field_id,
            'text'  => $field_text,
            'name'  => $field_id_name,
            'alias' => $table_alias,
            'field' => $query_field,
            'join'  => $query_join,
        );
    }


    /**
     * Returns an array of objects selected by module, key and language ID,
     * plus optional text IDs from the database.
     *
     * You may multiply the $lang_id parameter with -1 to get a negative value,
     * in which case this method behaves very much like {@link getById()} and
     * returns other languages or a warning if the language with the same
     * positive ID is unavailable.  This is intended for backend use only.
     * The array returned looks like this:
     *  array(
     *    text_id => obj_text,
     *    ... more ...
     *  )
     * @static
     * @param       integer     $module_id      The module ID
     * @param       string      $key            The key
     * @param       integer     $lang_id        The language ID
     * @param       integer     $text_ids       The optional comma separated
     *                                          list of Text IDs
     * @return      array                       The array of Text objects
     *                                          on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function getArrayById($module_id, $key, $lang_id, $text_ids='')
    {
        global $objDatabase;

        if (empty($module_id) || empty($key) || empty($lang_id)) return false;
        $query = "
            SELECT `id`
              FROM `".DBPREFIX."core_text`
             WHERE `module_id`=$module_id
               AND `key`='".addslashes($key)."'".
               ($lang_id > 0 ? ' AND `lang_id`='.$lang_id          : '').
               ($text_ids    ? ' AND `text_id` IN ('.$text_ids.')' : '');
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        $arrText = array();
        $lang_id = abs($lang_id);
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $objText = self::getById($id, $lang_id);
            if ($objText) $arrText[$id] = $objText;
            $objResult->MoveNext();
        }
        return $arrText;
    }


    /**
     * Returns an array of Text and language IDs of records matching
     * the search pattern and optional module, key, language, and text IDs
     *
     * Note that you have to add "%" signs to the pattern if you want the
     * match to be open ended.
     * The array returned looks like this:
     *  array(
     *    id => array(
     *      0 => Language ID,
     *      ... 1 => more language IDs ...
     *    ),
     *    ... more ids ...
     *  )
     * @static
     * @param       string      $pattern        The search pattern
     * @param       integer     $module_id      The optional module ID, or false
     * @param       string      $key            The optional key, or false
     * @param       integer     $lang_id        The optional language ID, or false
     * @param       integer     $text_ids       The optional comma separated
     *                                          list of Text IDs, or false
     * @return      array                       The array of Text and language
     *                                          IDs on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function getIdArrayBySearch(
        $pattern, $module_id=false, $key=false,
        $lang_id=false, $text_ids=false
    ) {
        global $objDatabase;

        $query = "
            SELECT `id`, `lang_id`
              FROM `".DBPREFIX."core_text`
             WHERE `text` LIKE '".addslashes($pattern)."'".
            ($lang_id   !== false ? " AND `lang_id`=$lang_id"           : '').
            ($module_id !== false ? " AND `module_id`=$module_id"       : '').
            ($key       !== false ? " AND `key`='".addslashes($key)."'" : '').
            ($text_ids  !== false ? " AND `id` IN ($text_ids)"     : '');
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        $arrId = array();
        while (!$objResult->EOF) {
            $arrId[$objResult->fields['id']][$objResult->fields['lang_id']] = true;
            $objResult->MoveNext();
        }
        return $arrId;
    }


    /**
     * If the language ID given is different from the language of this
     * Text object, the content may be marked here.
     *
     * Customize as desired.
     * @param   integer   $lang_id         The desired language ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function markDifferentLanguage() //$lang_id
    {
// Different formatting -- up to you.
// None
        return;
//        if ($lang_id != $this->lang_id) {
// or
//            $this->text = '['.$this->text.']';
// or
//            $this->text = $this->text.' *';
// or (not suitable for form element contents)
//            $this->text = '<font color="red">'.$this->text.'</font>';
//        }
    }


    /**
     * Handle any error occurring in this class.
     *
     * Tries to fix known problems with the database table.
     * @global  mixed     $objDatabase    Database object
     * @return  boolean                   False.  Always.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function errorHandler()
    {
        global $objDatabase;

//die("Text::errorHandler(): Disabled!<br />");

        $arrTables = $objDatabase->MetaTables('TABLES');
        if (!in_array(DBPREFIX."core_text", $arrTables)) {
            $query = "
                CREATE TABLE `".DBPREFIX."core_text` (
                  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `lang_id` INT(10) UNSIGNED NOT NULL DEFAULT 1,
                  `module_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
                  `key` TINYTEXT NOT NULL DEFAULT '',
                  `text` TEXT NOT NULL DEFAULT '',
                  PRIMARY KEY `id` (`id`, `lang_id`),
                  INDEX `module_id` (`module_id` ASC),
                  INDEX `key` (`key`(32) ASC),
                  FULLTEXT `text` (`text`)
                ) ENGINE=MyISAM
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
        }

        // More to come...

        return false;
    }

}

?>
