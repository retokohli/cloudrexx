<?php

/**
 * Manages settings stored in the database
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com> (parts)
 * @version     2.2.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

require_once ASCMS_CORE_PATH.'/Html.class.php';

/**
 * Manages settings stored in the database
 *
 * Before trying to access a modules' settings, *DON'T* forget to call
 * {@see SettingDb::init()} before calling getValue() for the first time!
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com> (parts)
 * @version     2.2.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */
class SettingDb
{
    /**
     * The array of currently loaded settings settings, like
     *  array(
     *    'name' => array(
     *      'module_id' => module ID,
     *      'key'       => key,
     *      'value'     => current value,
     *      'type'      => element type (text, dropdown, ... [more to come]),
     *      'values'    => predefined values (for dropdown),
     *      'ord'       => ordinal number (for sorting),
     *    ),
     *    ... more ...
     *  );
     * @var     array
     * @static
     * @access  private
     */
    private static $arrSettings = false;

    /**
     * The key last used to {@see init()} the settings.
     * Defaults to false (ignored).
     * @var     string
     * @static
     * @access  private
     */
    private static $key = false;

    /**
     * Changes flag
     *
     * This flag is set to true as soon as any change to the settings is detected.
     * It is cleared whenever {@see updateAll()} is called.
     * @var     boolean
     * @static
     * @access  private
     */
    private static $flagChanged = false;


    /**
     * Initialize the settings entries from the database with key/value pairs
     * for the current module ID and the given key
     *
     * A $key value of false is ignored.  All records with the current module ID
     * taken from the global MODULE_ID constant are included in this case.
     * Note that the setting name *SHOULD* be unambiguous whether $key is
     * empty or not.  If there are two settings with the same name but different
     * $key values, the second one will overwrite the first!
     * @internal  The records are ordered by
     *            `key` ASC, `ord` ASC, `name` ASC
     * @param   string    $key        The key, or false.  Defaults to false
     * @return  boolean               True on success, false otherwise
     */
    function init($key=false)
    {
        global $objDatabase;

        self::flush();
//echo("SettingDb::init($key): Entered<br />");
        $objResult = $objDatabase->Execute("
            SELECT `name`, `key`, `value`,
                   `type`, `values`, `ord`
              FROM ".DBPREFIX."core_setting
             WHERE `module_id`=".MODULE_ID.
             ($key === false ? '' : " AND `key`='".addslashes($key)."'")."
             ORDER BY `key` ASC, `ord` ASC, `name` ASC");
        if (!$objResult) return self::errorHandler();
        self::$key = $key;
        self::$arrSettings = array();
        while (!$objResult->EOF) {
            self::$arrSettings[$objResult->fields['name']] = array(
                'module_id' => MODULE_ID,
                'key'       => $objResult->fields['key'],
                'value'     => $objResult->fields['value'],
                'type'      => $objResult->fields['type'],
                'values'    => $objResult->fields['values'],
                'ord'       => $objResult->fields['ord'],
            );
//echo("Setting ".$objResult->fields['name']." = ".$objResult->fields['value']."<br />");
            $objResult->MoveNext();
        }
        return true;
    }


    /**
     * Flush the stored settings
     *
     * Resets the class to its initial state
     * @return  void
     */
    static function flush()
    {
        self::$arrSettings = false;
        self::$key         = false;
        self::$flagChanged = false;
    }


    /**
     * Returns true if changes have been made to the current settings
     * @return  boolean         True if changes have been made,
     *                          false otherwise
     */
    static function hasChanged()
    {
        return self::$flagChanged;
    }


    /**
     * Returns the settings array for the current module ID and the
     * given key
     *
     * See {@see init()} on how the arguments are used.
     * If the method is called successively using the same $key argument,
     * the current settings are returned without calling {@see init()}.
     * Thus, changes made by calling {@see set()} will be preserved.
     * @param   string    $key        The key, or false
     * @return  array                 The settings array on success,
     *                                false otherwise
     */
    function getArray($key=false)
    {
        if (self::$key !== $key) {
            if (!self::init($key)) return false;
        }
        return self::$arrSettings;
    }


    /**
     * Returns the settings value stored in the object for the name given.
     *
     * If the settings have not been initialized (see {@see init()}), or
     * if no setting of that name is present in the current set, false
     * is returned.
     * @param   string    $name       The settings name
     * @return  mixed                 The settings value, if present,
     *                                false otherwise
     */
    function getValue($name)
    {
//echo("SettingDb::getValue($name): Value is ".(isset(self::$arrSettings[$name]['value']) ? self::$arrSettings[$name]['value'] : 'NOT FOUND')."<br />");
        return (isset(self::$arrSettings[$name]['value'])
            ? self::$arrSettings[$name]['value'] : false
        );
    }


    /**
     * Updates or adds a setting
     *
     * If the name does not exist yet, it is added, and $flagChanged
     * is set to true.
     * If the new value is not equal to the old one, it is updated,
     * and $flagChanged set to true.
     * Otherwise, nothing happens.
     * @see init(), updateAll()
     * @param   string    $name       The settings name
     * @param   string    $value      The settings value
     * @return  boolean               True if the value has been changed,
     *                                false otherwise
     */
    function set($name, $value)
    {
        if (   !isset(self::$arrSettings[$name])
            || (   isset(self::$arrSettings[$name])
                && self::$arrSettings[$name]['value'] != $value)) {
//echo("SettingDb::set($name, $value): Added/updated<br />");
            self::$flagChanged = true;
            self::$arrSettings[$name]['value'] = $value;
        }
//echo("SettingDb::set($name, $value): Leaving<br />");
        return self::$flagChanged;
    }


    /**
     * Stores all settings entries present in the $arrSettings object array variable
     *
     * Returns boolean true if all records were stored successfully, false if any one
     * failed, or the empty string if no change to the settings has been detected
     * and thus nothing has been stored at all.
     * Upon success, also resets the $flagChanged object variable to false.
     * @return  mixed               True on success, false on failure,
     *                              the empty string if no change is detected.
     */
    function updateAll()
    {
        if (!self::$flagChanged) return '';

        $success = true;
        foreach (self::$arrSettings as $name => $arrSetting) {
            $success &= self::update($name, $arrSetting['value']);
        }
        if ($success) {
            self::$flagChanged = false;
            return true;
        }
        return self::errorHandler();
    }


    /**
     * Changes the value for the given name in the settings
     * and updates the database as necessary.
     *
     * Returns true both if the value has been updated successfully
     * and if it hasn't been changed.
     * Note that this method does not work for adding new settings.
     * See {@see add()} on how to do this.
     * @param   string    $name   The settings name
     * @param   string    $value  The settings value
     * @param   integer   $ord    The optional ordinal number, ignored
     *                            if false. Defaults to false.
     * @return  boolean           True on successful update or if
     *                            unchanged, false on failure
     * @static
     * @global  mixed     $objDatabase    Database connection object
     */
    static function update($name, $value, $ord=false)
    {
        global $objDatabase;

        // Fail if the name is invalid
        // or the setting does not exist already
// TODO: Add error messages for individual errors
        if (   empty($name)
            || !isset(self::$arrSettings[$name]))
            return false;

        // Return the empty string if nothing changes
        if (!self::set($name, $value)) return true;

        // Exists, update it
        $objResult = $objDatabase->Execute("
            UPDATE `".DBPREFIX."core_setting`
               SET `value`='".addslashes($value)."'".
                   ($ord !== false ? ", `ord`=$ord" : '')."
             WHERE `name`='".addslashes($name)."'
               AND `module_id`=".MODULE_ID.
             (self::$key !== false ? " AND `key`='".addslashes(self::$key)."'" : ''));
        if (!$objResult) return self::errorHandler();
        return true;
    }


    /**
     * Add a new record to the settings
     *
     * The class *MUST* have been initialized by calling {@see init()}
     * or {@see getArray()} before this method is called.
     * The present $key stored in the class is used as a default.
     * If the current class $key is empty, it *MUST* be specified in the call.
     * @param   string    $name     The setting name
     * @param   string    $value    The value
     * @param   integer   $ord      The ordinal value for sorting,
     *                              defaults to 0
     * @param   string    $type     The element type for displaying,
     *                              defaults to 'text'
     * @param   string    $values   The values for type 'dropdown',
     *                              defaults to the empty string
     * @param   string    $key      The optional key
     * @return  boolean             True on success, false otherwise
     */
    static function add($name, $value, $ord=false, $type='text', $values='', $key=false)
    {
        global $objDatabase;

        // Fail if the name is invalid
        if (   self::$arrSettings === false
            || empty($name))
            return false;

        // This can only be done with a non-empty key!
        // Use the current key, if present, otherwise fail
        if ($key === false) {
            if (self::$key === false) return false;
            $key = self::$key;
        }

        // Does the setting exist already?
        $objResult = $objDatabase->Execute("
            SELECT 1
              FROM `".DBPREFIX."core_setting`
             WHERE `name`='".addslashes($name)."'
               AND `module_id`=".MODULE_ID.
             (self::$key === false ? '' : " AND `key`='".addslashes(self::$key)."'"));
        if (!$objResult) return self::errorHandler();
        if (!$objResult->EOF) {
            // Such an entry exists already, fail
            return false;
            // update it
            //return self::update($name, $value, $ord);
        }

        // Not present, insert it
        $objResult = $objDatabase->Execute("
            INSERT INTO `".DBPREFIX."core_setting` (
                `name`,
                `module_id`,
                `key`,
                `value`,
                `type`,
                `values`,
                `ord`
            ) VALUES (
                '".addslashes($name)."',
                ".MODULE_ID.",
                '".addslashes($key)."',
                '".addslashes($value)."',
                '".addslashes($type)."',
                '".addslashes($values)."',
                ".intval($ord)."
            )");
        if (!$objResult) return self::errorHandler();
        return true;
    }


    /**
     * Display the settings stored in the given array
     *
     * Uses the indices as the names for any parameter, the values
     * as themselves, and adds language variables for the settings' name
     * with the given prefix (i.e. 'TXT_', or 'TXT_MYMODULE_') plus the
     * upper case indices.
     * Example:
     *    Settings: array('shop_dummy' => 1)
     *    Prefix:   'TXT_'
     *  Results in placeholders to be set as follows:
     *    Placeholder         Value
     *    SETTING_NAME        The content of $_ARRAYLANG['TXT_SHOP_DUMMY']
     *    SETTING_VALUE       '1'
     *    SETTING_PARAMETER   'shop_dummy'
     *
     * Placeholders:  The parameter name is written to SETTING_PARAMETER,
     * The settings' name is to SETTING_NAME, and the value to SETTING_VALUE.
     * Set the default block to parse after each array entry if it
     * differs from the default 'core_setting_db'.
     * Make sure to define all the language variables that are expected
     * to be defined here!
     * In addition, some entries from $_CORELANG are set up. These are both
     * used as placeholder name and language array index:
     *  - TXT_CORE_SETTING_STORE
     *  - TXT_CORE_SETTING_NAME
     *  - TXT_CORE_SETTING_VALUE
     * @param   HTML_Template_Sigma $objTemplateLocal  Template object
     * @param   string              $section      The section header text to add
     *                                            if not empty
     * @param   string              $prefix       The prefix for language variables,
     *                                            defaults to 'TXT_'
     * @return  boolean                           True on success, false otherwise
     * @todo    Add functionality to handle arrays within arrays
     * @todo    Add functionality to handle special form elements
     * @todo    Verify special values like e-mail addresses in methods
     *          that store them, like add(), update(), and updateAll()
     */
    static function show($objTemplateLocal, $section='', $prefix='TXT_')
    {
        global $objTemplate, $_CORELANG, $_ARRAYLANG;

        // Default headings and elements
        $objTemplateLocal->setGlobalVariable(array(
            'TXT_SETTINGS'      => $_CORELANG['TXT_CORE_SETTINGS'],
            'TXT_SETTING_STORE' => $_CORELANG['TXT_CORE_SETTING_STORE'],
            'TXT_SETTING_NAME'  => $_CORELANG['TXT_CORE_SETTING_NAME'],
            'TXT_SETTING_VALUE' => $_CORELANG['TXT_CORE_SETTING_VALUE'],
        ));

        if ($objTemplateLocal->blockExists('core_setting_db_row'))
            $objTemplateLocal->setCurrentBlock('core_setting_db_row');
//echo("SettingDb::show(objTemplate, $prefix): got Array: ".var_export(self::$arrSettings, true)."<br />");
        if (!is_array(self::$arrSettings)) {
            $objTemplate->setVariable(
                'CONTENT_STATUS_MESSAGE',
                $_CORELANG['TXT_CORE_SETTINGS_ERROR_RETRIEVING']
            );
            return false;
        }
        if (empty(self::$arrSettings)) {
            $objTemplate->setVariable(
                'CONTENT_STATUS_MESSAGE',
                $_CORELANG['TXT_CORE_SETTINGS_NONE_FOUND']
            );
            return true;
        }

        $i = 0;
        foreach (self::$arrSettings as $name => $arrSetting) {
            // Determine HTML element for type and apply values and selected
            $element = '';
            $value = $arrSetting['value'];
            $value_align = (is_numeric($value) ? 'text-align: right;' : '');
            switch ($arrSetting['type']) {
              // Dropdown menu
              case 'dropdown':
// TODO:  Use the Html class to create the dropdown
                $element = Html::getSelect(
                    $name, self::splitValues($arrSetting['values']), $value,
                    '',
                    'style="width: 220px;'.$value_align.'"');
                break;
              case 'dropdown_user_custom_attribute':
                $element = Html::getSelect(
                    $name,
                    User_Profile_Attribute::getCustomAttributeNameArray(),
                    $arrSetting['value'],
                    '', 'style="width: 220px;"'
                );
                break;
              case 'dropdown_usergroup':
                $element = Html::getSelect(
                    $name,
                    UserGroup::getNameArray(),
                    $arrSetting['value'],
                    '', 'style="width: 220px;"'
                );
                break;
              case 'wysiwyg':
                $element = get_wysiwyg_editor($name, $value);
                break;

// More...
//              case '':
//                break;

              // Default to text input fields
              case 'text':
              case 'email':
              default:
                $element =
                    '<input type="text" style="width: 220px;'.$value_align.'" '.
                    'name="'.$name.'" value="'.$value.'" />';
            }

            $objTemplateLocal->setVariable(array(
                'SETTING_NAME'        => $_ARRAYLANG[$prefix.strtoupper($name)],
                'SETTING_VALUE'       => $element,
                'SETTING_VALUE_ALIGN' => $value_align,
                'SETTING_PARAMETER'   => $name,
                'SETTING_ROWCLASS'    => (++$i % 2 ? '1' : '2'),
            ));
            $objTemplateLocal->parseCurrentBlock();
//echo("SettingDb::show(objTemplate, $prefix): shown $name => $value<br />");
        }

        if (   !empty($section)
            && $objTemplateLocal->blockExists('core_setting_db_section')) {
//echo("SettingDb::show(objTemplate, $section, $prefix): creating section $section<br />");
            $objTemplateLocal->setVariable(array(
                'TXT_SETTING_SECTION' => $section,
            ));
            $objTemplateLocal->parse('core_setting_db_section');
        }
        return true;
    }


    /**
     * Deletes all entries for the current module
     *
     * This is for testing purposes only.  Use with care!
     * The global MODULE_ID determines the current module ID.
     * @return    boolean               True on success, false otherwise
     */
    static function deleteModule()
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            DELETE FROM `".DBPREFIX."core_setting`
             WHERE `module_id`=".MODULE_ID);
        if (!$objResult) return self::errorHandler();
        return true;
    }


    /**
     * Should be called whenever there's a problem with the settings table
     *
     * Tries to fix or recreate the settings table.
     * @return  boolean             False, always.
     */
    function errorHandler()
    {
        global $objDatabase;

echo("SettingDb::errorHandler(): Entered<br />");

        $arrTables = $objDatabase->MetaTables('TABLES');
        if (!in_array(DBPREFIX."core_setting", $arrTables)) {
            $query = "
                CREATE TABLE `".DBPREFIX."core_setting` (
                  `name` TINYTEXT NOT NULL,
                  `module_id` INT(10) NOT NULL DEFAULT 0,
                  `key` TINYTEXT NOT NULL DEFAULT '',
                  `value` TEXT NOT NULL DEFAULT '',
                  `type` VARCHAR(32) NOT NULL DEFAULT 'text',
                  `values` TEXT NULL DEFAULT NULL,
                  `ord` INT(10) UNSIGNED NOT NULL DEFAULT 0,
                  PRIMARY KEY (`name`(32), `module_id`, `key`(32))
                ) ENGINE=MYISAM";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
echo("SettingDb::errorHandler(): Created table ".DBPREFIX."core_setting<br />");
        }

        // Use SettingDb::add(); in your module code to add missing and
        // new settings.

        // More to come...

        // Always!
        return false;
    }

}

?>
