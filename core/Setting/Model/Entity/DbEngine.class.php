<?php
/**
 * Specific Setting for this Component. Use this to interact with the Setting.class.php
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com> (parts)
 * @author      Manish Thakur <manishthakur@cdnsol.com>
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  core_setting
 * @todo        Edit PHP DocBlocks!
 */

namespace Cx\Core\Setting\Model\Entity;

/**
 * Manages settings stored in the database or file system
 *
 * Before trying to access a modules' settings, *DON'T* forget to call
 * {@see Setting::init()} before calling getValue() for the first time!
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com> (parts)
 * @author      Manish Thakur <manishthakur@cdnsol.com>
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  core_setting
 * @todo        Edit PHP DocBlocks!
 */
class DbEngine extends Engine{
    /**
     * Initialize the settings entries from the database with key/value pairs
     * for the current section and the given group
     *
     * An empty $group value is ignored.  All records with the section are
     * included in this case.
     * Note that all setting names *SHOULD* be unambiguous for the entire
     * section.  If there are two settings with the same name but different
     * $group values, the second one may overwrite the first!
     * @internal  The records are ordered by
     *            `group` ASC, `ord` ASC, `name` ASC
     * @param   string    $section    The section
     * @param   string    $group      The optional group.
     *                                Defaults to null
     * @return  boolean               True on success, false otherwise
     * @global  ADOConnection   $objDatabase
     */
    static function init($section, $group=null) {
        global $objDatabase;

        if (empty($section)) {
            die("\Cx\Core\Setting\Model\Entity\DbEngine::init($section, $group): ERROR: Missing \$section parameter!");
            //return false;
        }
        self::flush();
        //echo("self::init($section, $group): Entered<br />");
        $objResult = $objDatabase->Execute("
            SELECT `name`, `group`, `value`,
                   `type`, `values`, `ord`
              FROM ".DBPREFIX."core_setting
             WHERE `section`='".addslashes($section)."'".
             ($group ? " AND `group`='".addslashes($group)."'" : '')."
             ORDER BY `group` ASC, `ord` ASC, `name` ASC");
        if (!$objResult) return self::errorHandler();
        // Set the current group to the empty string if empty
        self::$section = $section;
        self::$group = $group;
        self::$arrSettings = array();
        while (!$objResult->EOF) {
            self::$arrSettings[$objResult->fields['name']] = array(
                'section' => $section,
                'group' => $objResult->fields['group'],
                'value' => $objResult->fields['value'],
                'type' => $objResult->fields['type'],
                'values' => $objResult->fields['values'],
                'ord' => $objResult->fields['ord'],
            );
        //echo("Setting ".$objResult->fields['name']." = ".$objResult->fields['value']."<br />");
            $objResult->MoveNext();
        }
    }

    /**
     * Returns the settings array for the given section and group
     * @return  array
     */
    static function getArraySetting()
    {
       return self::$arrSettings;
    }

    /**
     * Returns true or false for the given setting name, if exist means true,
     * otherwise false
     * 
     * @return  boolean
     */
    static function isDefined($name)
    { 
        if (isset(self::$arrSettings[$name]['name'])) {
            return true;   
        }
        return false;
    }
    
    
    /**
     * Stores all settings entries present in the $arrSettings object
     * array variable
     *
     * Returns boolean true if all records were stored successfully,
     * null if nothing changed (noop), false otherwise.
     * Upon success, also resets the $changed class variable to false.
     * The class *MUST* have been initialized before calling this
     * method using {@see init()}, and the new values been {@see set()}.
     * Note that this method does not work for adding new settings.
     * See {@see add()} on how to do this.
     * @return  boolean                   True on success, null on noop,
     *                                    false otherwise
     */
    static function updateAll()
    {
        //        global $_CORELANG;

        if (!self::$changed) {
            // TODO: These messages are inapropriate when settings are stored by another piece of code, too.
            // Find a way around this.
            // Message::information($_CORELANG['TXT_CORE_SETTING_INFORMATION_NO_CHANGE']);
            return null;
        }
        $success = true;
        foreach (self::$arrSettings as $name => $arrSetting) {
            $success &= self::update($name);
        }
        if ($success) {
            self::$changed = false;
            //return Message::ok($_CORELANG['TXT_CORE_SETTING_STORED_SUCCESSFULLY']);
            return true;
        }
        //return Message::error($_CORELANG['TXT_CORE_SETTING_ERROR_STORING']);
        return false;
    }

    /**
     * Updates the value for the given name in the settings table
     *
     * The class *MUST* have been initialized before calling this
     * method using {@see init()}, and the new value been {@see set()}.
     * Sets $changed to true and returns true if the value has been
     * updated successfully.
     * Note that this method does not work for adding new settings.
     * See {@see add()} on how to do this.
     * Also note that the loaded setting is not updated, only the database!
     * @param   string    $name   The settings name
     * @return  boolean           True on successful update or if
     *                            unchanged, false on failure
     * @static
     * @global  mixed     $objDatabase    Database connection object
     */
    static function update($name)
    {   
        global $objDatabase;
        // TODO: Add error messages for individual errors
        if (empty(self::$section)) {
            \DBG::log("\Cx\Core\Setting\Model\Entity\DbEngine::update(): ERROR: Empty section!");
            return false;
        }
        // Fail if the name is invalid
        // or the setting does not exist
        if (empty($name)) {
            \DBG::log("\Cx\Core\Setting\Model\Entity\DbEngine::update(): ERROR: Empty name!");
            return false;
        }
        if (!isset(self::$arrSettings[$name])) {
            \DBG::log("\Cx\Core\Setting\Model\Entity\DbEngine::update(): ERROR: Unknown setting name '$name'!");
            return false;
        }
        $objResult = $objDatabase->Execute("
            UPDATE `".DBPREFIX."core_setting`
               SET `value`='".addslashes(self::$arrSettings[$name]['value'])."'
             WHERE `name`='".addslashes($name)."'
               AND `section`='".addslashes(self::$section)."'".
            (self::$group
                ? " AND `group`='".addslashes(self::$group)."'" : ''));
        if (!$objResult) return self::errorHandler();
        self::$changed = true;
        return true;
    }

    /**
     * Add a new record to the settings
     *
     * The class *MUST* have been initialized by calling {@see init()}
     * or {@see getArray()} before this method is called.
     * The present $group stored in the class is used as a default.
     * If the current class $group is empty, it *MUST* be specified in the call.
     * @param   string    $name     The setting name
     * @param   string    $value    The value
     * @param   integer   $ord      The ordinal value for sorting,
     *                              defaults to 0
     * @param   string    $type     The element type for displaying,
     *                              defaults to 'text'
     * @param   string    $values   The values for type 'dropdown',
     *                              defaults to the empty string
     * @param   string    $group    The optional group
     * @return  boolean             True on success, false otherwise
     */ 
    static function add( $name, $value, $ord=false, $type='text', $values='', $group=null)
    {
        global $objDatabase;
        if (!isset(self::$section)) {
            // TODO: Error message
            \DBG::log("\Cx\Core\Setting\Model\Entity\DbEngine::add(): ERROR: Empty section!");
            return false;
        }
        // Fail if the name is invalid
        if (empty($name)) {
            \DBG::log("\Cx\Core\Setting\Model\Entity\DbEngine::add(): ERROR: Empty name!");
            return false;
        }
        // This can only be done with a non-empty group!
        // Use the current group, if present, otherwise fail
        if (!$group) {
            if (!self::$group) {
                \DBG::log("\Cx\Core\Setting\Model\Entity\DbEngine::add(): ERROR: Empty group!");
                return false;
            }
            $group = self::$group;
        }
        // Initialize if necessary
        if (is_null(self::$arrSettings) || self::$group != $group){
            self::init(self::$section, $group);
        }
        // Such an entry exists already, fail.
        // Note that getValue() returns null if the entry is not present
        $old_value = self::getValue($name);
        if (isset($old_value)) {
            // \DBG::log("\Cx\Core\Setting\Model\Entity\DbEngine::add(): ERROR: Setting '$name' already exists and is non-empty ($old_value)");
            return false;
        }

        // Not present, insert it
        $query = "
            INSERT INTO `".DBPREFIX."core_setting` (
                `section`, `group`, `name`, `value`,
                `type`, `values`, `ord`
            ) VALUES (
                '".addslashes(self::$section)."',
                '".addslashes($group)."',
                '".addslashes($name)."',
                '".addslashes($value)."',
                '".addslashes($type)."',
                '".addslashes($values)."',
                ".intval($ord)."
            )";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            \DBG::log("\Cx\Core\Setting\Model\Entity\DbEngine::add(): ERROR: Query failed: $query");
            return false;
        }
        return true;
    }

    /**
     * Delete one or more records from the database table
     *
     * For maintenance/update purposes only.
     * At least one of the parameter values must be non-empty.
     * It will fail if both are empty.  Mind that in this case,
     * no records will be deleted.
     * Does {@see flush()} the currently loaded settings on success.
     * @param   string    $name     The optional setting name.
     *                              Defaults to null
     * @param   string    $group      The optional group.
     *                              Defaults to null
     * @return  boolean             True on success, false otherwise
     */
    static function delete($name=null, $group=null)
    {
        global $objDatabase;

        // Fail if both parameter values are empty
        if (empty($name) && empty($group)) return false;
        $objResult = $objDatabase->Execute("
            DELETE FROM `".DBPREFIX."core_setting`
             WHERE 1".
            ($name ? " AND `name`='".addslashes($name)."'" : '').
            ($group  ? " AND `group`='".addslashes($group)."'"   : ''));
        if (!$objResult) return self::errorHandler();
        self::flush();
        return true;
    }

    /**
     * Deletes all entries for the current section
     *
     * This is for testing purposes only.  Use with care!
     * The static $section determines the module affected.
     * @return    boolean               True on success, false otherwise
     */
    static function deleteModule()
    {
        global $objDatabase;

        if (empty(self::$section)) {
            // TODO: Error message
            return false;
        }
        $objResult = $objDatabase->Execute("
            DELETE FROM `".DBPREFIX."core_setting`
             WHERE `section`='".self::$section."'");
        if (!$objResult) return self::errorHandler();
        return true;
    }

    /**
     * Should be called whenever there's a problem with the settings table
     *
     * Tries to fix or recreate the settings table.
     * @return  boolean             False, always.
     * @static
     */
    static function errorHandler()
    {
        $table_name = DBPREFIX.'core_setting';
        $table_structure = array(
            'section' => array('type' => 'VARCHAR(32)', 'default' => '', 'primary' => true),
            'name' => array('type' => 'VARCHAR(255)', 'default' => '', 'primary' => true),
            'group' => array('type' => 'VARCHAR(32)', 'default' => '', 'primary' => true),
            'type' => array('type' => 'VARCHAR(32)', 'default' => 'text'),
            'value' => array('type' => 'TEXT', 'default' => ''),
            'values' => array('type' => 'TEXT', 'notnull' => true, 'default' => null),
            'ord' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0'),
        );
        // TODO: The index array structure is wrong here!
        $table_index =  array();
        \Cx\Lib\UpdateUtil::table($table_name, $table_structure, $table_index);
        //echo("self::errorHandler(): Created table ".DBPREFIX."core_setting<br />");

        //Use self::add(); in your module code to add settings; example:
        //self::init('core', 'country');
        //self::add('numof_countries_per_page_backend', 30, 1, self::TYPE_TEXT);

        //More to come...

        //Always!
        return false;
    }
}
