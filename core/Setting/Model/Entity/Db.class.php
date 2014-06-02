<?php
/**
 * Specific Setting for this Component. Use this to interact with the Setting.class.php
 *
 * @copyright   Comvation AG
 * @author      Reto Kohli <reto.kohli@comvation.com> (parts)
 * @package     contrexx
 * @subpackage  core_setting
 * @todo        Edit PHP DocBlocks!
 */
 
namespace Cx\Core\Setting\Model\Entity;

class Db implements Engine{
    
    /**
     * The array of currently loaded settings, like
     *  array(
     *    'name' => array(
     *      'section' => section,
     *      'group' => group,
     *      'value' => current value,
     *      'type' => element type (text, dropdown, ... [more to come]),
     *      'values' => predefined values (for dropdown),
     *      'ord' => ordinal number (for sorting),
     *    ),
     *    ... more ...
     *  );
     * @var     array
     * @static
     * @access  private
     */
    private static $arrSettings = null;
    
    /**
     * The group last used to {@see init()} the settings.
     * Defaults to null (ignored).
     * @var     string
     * @static
     * @access  private
     */
    private static $group = null;

    /**
     * The section last used to {@see init()} the settings.
     * Defaults to null (which will cause an error in most methods).
     * @var     string
     * @static
     * @access  private
     */
    private static $section = null;
    
     /**
     * Changed flag
     *
     * This flag is set to true as soon as any change to the settings is detected.
     * It is cleared whenever {@see updateAll()} is called.
     * @var     boolean
     * @static
     * @access  private
     */
    private static $changed = false;
    
    /**
     * Returns the current value of the changed flag.
     *
     * If it returns true, you probably want to call {@see updateAll()}.
     * @return  boolean           True if values have been changed in memory,
     *                            false otherwise
     */
    static function changed()
    {
        return self::$changed;
    }

    /**
     * Tab counter for the {@see show()} and {@see show_external()}
     * @var     integer
     * @access  private
     */
    public static $tab_index = 1;


    /**
     * Optionally sets and returns the value of the tab index
     * @param   integer             The optional new tab index
     * @return  integer             The current tab index
     */
    static function tab_index($tab_index=null)
    {
        if (isset($tab_index)) {
            self::$tab_index = intval($tab_index);
        }
        return self::$tab_index;
    }
    
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
            die("\Cx\Core\Setting\Model\Entity\Db::init($section, $group): ERROR: Missing \$section parameter!");
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
     * Flush the stored settings
     *
     * Resets the class to its initial state.
     * Does *NOT* clear the section, however.
     * @return  void
     */
    static function flush()
    {
        self::$arrSettings = null;
        self::$section = null;
        self::$group = null;
        self::$changed = null;
    }
    
    /**
     * Returns the settings array for the given section and group
     *
     * See {@see init()} on how the arguments are used.
     * If the method is called successively using the same $group argument,
     * the current settings are returned without calling {@see init()}.
     * Thus, changes made by calling {@see set()} will be preserved.
     * @param   string    $section    The section
     * @param   string    $group        The optional group
     * @return  array                 The settings array on success,
     *                                false otherwise
     */
    static function getArray($section, $group=null)
    {
        if (self::$section !== $section
         || self::$group !== $group) {
            if (!self::init($section, $group)) return false;
        }
        return self::$arrSettings;
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
     * Returns the settings value stored in the object for the name given.
     *
     * If the settings have not been initialized (see {@see init()}), or
     * if no setting of that name is present in the current set, null
     * is returned.
     * @param   string    $name       The settings name
     * @return  mixed                 The settings value, if present,
     *                                null otherwise
     */
    static function getValue($name)
    {
        if (is_null(self::$arrSettings)) {
        \DBG::log("\Cx\Core\Setting\Model\Entity\Db::getValue($name): ERROR: no settings loaded");
            return null;
        }
        //echo("self::getValue($name): Value is ".(isset(self::$arrSettings[$name]['value']) ? self::$arrSettings[$name]['value'] : 'NOT FOUND')."<br />");
        if (isset(self::$arrSettings[$name]['value'])) {
            return self::$arrSettings[$name]['value'];
        };
        // \DBG::log("\Cx\Core\Setting\Model\Entity\Db::getValue($name): ERROR: unknown setting '$name' (current group ".var_export(self::$group, true).")");
        return null;
    }

     /**
     * Updates a setting
     *
     * If the setting name exists and the new value is not equal to
     * the old one, it is updated, and $changed set to true.
     * Otherwise, nothing happens, and false is returned
     * @see init(), updateAll()
     * @param   string    $name       The settings name
     * @param   string    $value      The settings value
     * @return  boolean               True if the value has been changed,
     *                                false otherwise, null on noop
     */
    static function set($name, $value)
    {
        if (!isset(self::$arrSettings[$name])) {
            // \DBG::log("\Cx\Core\Setting\Model\Entity\Db::set($name, $value): Unknown, changed: ".self::$changed);
            return false;
        }
        if (self::$arrSettings[$name]['value'] == $value) {
            // \DBG::log("\Cx\Core\Setting\Model\Entity\Db::set($name, $value): Identical, changed: ".self::$changed);
            return null;
        }
        self::$changed = true;
        self::$arrSettings[$name]['value'] = $value;
            // \DBG::log("\Cx\Core\Setting\Model\Entity\Db::set($name, $value): Added/updated, changed: ".self::$changed);
            return true;
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
            // Message::information($_CORELANG['TXT_CORE_SETTINGDB_INFORMATION_NO_CHANGE']);
            return null;
        }
        $success = true;
        foreach (self::$arrSettings as $name => $arrSetting) {
            $success &= self::update($name, $arrSetting['value']);
        }
        if ($success) {
            self::$changed = false;
            //return Message::ok($_CORELANG['TXT_CORE_SETTINGDB_STORED_SUCCESSFULLY']);
            return true;
        }
        //return Message::error($_CORELANG['TXT_CORE_SETTINGDB_ERROR_STORING']);
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
            \DBG::log("\Cx\Core\Setting\Model\Entity\Db::update(): ERROR: Empty section!");
            return false;
        }
        // Fail if the name is invalid
        // or the setting does not exist
        if (empty($name)) {
            \DBG::log("\Cx\Core\Setting\Model\Entity\Db::update(): ERROR: Empty name!");
            return false;
        }
        if (!isset(self::$arrSettings[$name])) {
            \DBG::log("\Cx\Core\Setting\Model\Entity\Db::update(): ERROR: Unknown setting name '$name'!");
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
     */
    static function add( $name, $value, $ord=false, $type='text', $values='', $group=null)
    {
        global $objDatabase;

        if (!isset(self::$section)) {
            // TODO: Error message
            \DBG::log("\Cx\Core\Setting\Model\Entity\Db::add(): ERROR: Empty section!");
            return false;
        }
        // Fail if the name is invalid
        if (empty($name)) {
            \DBG::log("\Cx\Core\Setting\Model\Entity\Db::add(): ERROR: Empty name!");
            return false;
        }
        // This can only be done with a non-empty group!
        // Use the current group, if present, otherwise fail
        if (!$group) {
            if (!self::$group) {
                \DBG::log("\Cx\Core\Setting\Model\Entity\Db::add(): ERROR: Empty group!");
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
            // \DBG::log("\Cx\Core\Setting\Model\Entity\Db::add(): ERROR: Setting '$name' already exists and is non-empty ($old_value)");
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
            \DBG::log("\Cx\Core\Setting\Model\Entity\Db::add(): ERROR: Query failed: $query");
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
     * Splits the string value at commas and returns an array of strings
     *
     * Commas escaped by a backslash (\) are ignored and replaced by a
     * single comma.
     * The values themselves may be composed of pairs of key and value,
     * separated by a colon.  Colons escaped by a backslash (\) are ignored
     * and replaced by a single colon.
     * Leading and trailing whitespace is removed from both keys and values.
     * Note that keys *MUST NOT* contain commas or colons!
     * @param   string    $strValues    The string to be split
     * @return  array                   The array of strings
     */
    static function splitValues($strValues)
    {
        /*
        Example:
        postfinance:Postfinance Card,postfinanceecom:Postfinance E-Commerce,mastercard:Mastercard,visa:Visa,americanexpress:American Express,paypal:Paypal,invoice:Invoice,voucher:Voucher
        */
        $arrValues = array();
        $match = array();
        foreach (
            preg_split(
                '/\s*(?<!\\\\),\s*/', $strValues,
                null, PREG_SPLIT_NO_EMPTY) as $value
        ) {
            $key = null;
            if (preg_match('/^(.+?)\s*(?<!\\\\):\s*(.+$)/', $value, $match)) {
                $key = $match[1];
                $value = $match[2];
            // \DBG::log("Split $key and $value");
            }
            str_replace(array('\\,', '\\:'), array(',', ':'), $value);
            if (isset($key)) {
                $arrValues[$key] = $value;
            } else {
                $arrValues[] = $value;
            }
            // \DBG::log("Split $key and $value");
        }
            // \DBG::log("Array: ".var_export($arrValues, true));
        return $arrValues;
    }


    /**
     * Joins the strings in the array with commas into a single values string
     *
     * Commas within the strings are escaped by a backslash (\).
     * The array keys are prepended to the values, separated by a colon.
     * Colons within the strings are escaped by a backslash (\).
     * Note that keys *MUST NOT* contain either commas or colons!
     * @param   array     $arrValues    The array of strings
     * @return  string                  The concatenated values string
     * @todo    Untested!  May or may not work as described.
     */
    static function joinValues($arrValues)
    {
        $strValues = '';
        foreach ($arrValues as $key => $value) {
            $value = str_replace(
                array(',', ':'), array('\\,', '\\:'), $value);
            $strValues .=
                ($strValues ? ',' : '').
                "$key:$value";
        }
        return $strValues;
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


    /**
     * Returns the settings from the old settings table for the given module ID,
     * if available
     *
     * If the module ID is missing or invalid, or if the settings cannot be
     * read for some other reason, returns null.
     * Don't drop the table after migrating your settings, other modules
     * might still need it!  Instead, try this method only after you failed
     * to get your settings from SettingDb.
     * @param   integer   $module_id      The module ID
     * @return  array                     The settings array on success,
     *                                    null otherwise
     * @static
     */
    static function __getOldSettings($module_id)
    {
        global $objDatabase;

        $module_id = intval($module_id);
        if ($module_id <= 0) return null;
        $objResult = $objDatabase->Execute('
            SELECT `setname`, `setvalue`
              FROM `'.DBPREFIX.'settings`
             WHERE `setmodule`='.$module_id);
        if (!$objResult) {
            return null;
        }
        $arrConfig = array();
        while (!$objResult->EOF) {
            $arrConfig[$objResult->fields['setname']] =
                $objResult->fields['setvalue'];
            $objResult->MoveNext();
        }
        return $arrConfig;
    }
    
   

}
