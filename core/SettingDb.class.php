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

/**
 * Manages settings stored in the database
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
     * The module ID last used to {@see init()} the settings.
     * Defaults to zero.
     * @var     integer
     * @static
     * @access  private
     */
    private static $module_id = 0;

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
     * It is cleared whenever {@see storeAll()} is called.
     * @var     boolean
     * @static
     * @access  private
     */
    private static $flagChanged = false;


    /**
     * Initialize the settings entries from the database with key/value pairs
     * for the given module ID and key
     *
     * A $key value of false is ignored; any records with the given $module_id
     * are included in this case.
     * Note that the setting name *SHOULD* be unambiguous whether $key is
     * empty or not.  If there are two settings with the same name but different
     * $key values, the second one will overwrite the first!
     * @internal  The records are ordered by
     *            `module_id` ASC, `key` ASC, `ord` ASC, `name` ASC
     * @param   integer   $module_id  The module ID, or zero for core,
     *                                defaults to zero
     * @param   string    $key        The key, or false
     * @return  boolean               True on success, false otherwise
     */
    function init($module_id=0, $key=false)
    {
        global $objDatabase;

        self::flush();
//echo("SettingDb::init($module_id, $key): Entered<br />");
        $objResult = $objDatabase->Execute("
            SELECT `name`, `module_id`, `key`,
                   `value`,
                   `type`, `values`, `ord`
              FROM ".DBPREFIX."core_setting
             WHERE `module_id`=$module_id".
             ($key === false ? '' : " AND `key`='".addslashes($key)."'")."
             ORDER BY `module_id` ASC, `key` ASC, `ord` ASC, `name` ASC
        ");
        if (!$objResult) return self::errorHandler();
        self::$module_id = $module_id;
        self::$key = $key;
        self::$arrSettings = array();
        while (!$objResult->EOF) {
            self::$arrSettings[$objResult->fields['name']] = array(
                'module_id' => $objResult->fields['module_id'],
                'key' => $objResult->fields['key'],
                'value' => $objResult->fields['value'],
                'type' => $objResult->fields['type'],
                'values' => $objResult->fields['values'],
                'ord' => $objResult->fields['ord'],
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
        self::$module_id   = 0;
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
     * Returns the settings array with key/value pairs for the given
     * module ID and key
     *
     * See {@see init()} on how the arguments are used.
     * If the method is called successively using the same arguments,
     * the current settings are returned without calling {@see init()}.
     * Thus, changes made by calling {@see set()} will be preserved.
     * @param   integer   $module_id  The module ID, or zero for core,
     *                                defaults to zero
     * @param   string    $key        The key, or false
     * @return  array                 The settings array on success,
     *                                false otherwise
     */
    function getArray($module_id=0, $key=false)
    {
        if (   self::$module_id !== $module_id
            || self::$key !== $key) {
            if (!self::init($module_id, $key)) return false;
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
     * @see init(), storeAll()
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
//echo("SettingsDb::set($name, $value): Added/updated<br />");
            self::$flagChanged = true;
            self::$arrSettings[$name]['value'] = $value;
        }
//echo("SettingsDb::set($name, $value): Leaving<br />");
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
    function storeAll()
    {
        if (!self::$flagChanged) return '';

        $success = true;
        foreach (self::$arrSettings as $name => $arrSetting) {
            $success &= self::store($name, $arrSetting['value']);
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
    static function store($name, $value, $ord=false)
    {
        global $objDatabase;

        // Fail if the module ID is false, the name is invalid
        // or the setting does not exist already
// TODO: Add error messages for individual errors
        if (   self::$module_id === false
            || empty($name)
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
               AND `module_id`=".self::$module_id.
             (self::$key !== false ? " AND `key`='".addslashes(self::$key)."'" : ''));
//echo("Result is a ".get_class($objResult)."<br />");
//        if (is_a($objResult, 'ADORecordSet_empty') || $objResult->AffectedRows() == 0) {
//        if ($objResult->AffectedRows() == 0) {
//echo("Warning:  SettingDb::store($name, $value, $ord):  Updating the setting affected zero rows!<br />");
//            return false;
//        }
//    }
        if (!$objResult) return self::errorHandler();
        return true;
    }


    /**
     * Add a new record to the currently loaded settings
     *
     * The class *MUST* have been initialized by calling {@see init()}
     * or {@see getArray()} before this method is called.
     * Fails if the current $module_id is false or no settings are loaded.
     * The present $module_id and $key stored in the class are used
     * as defaults.
     * If the current $key is false, it *MUST* be specified.
     * @param   string    $name     The setting name
     * @param   string    $value    The value
     * @param   integer   $ord      The ordinal value for sorting,
     *                              defaults to 0
     * @param   string    $type     The element type for displaying,
     *                              defaults to 'text'
     * @param   string    $values   The values for type 'dropdown',
     *                              defaults to the empty string
     * @param   string    $key      The key, defaults to the key stored
     *                              in the class
     * @return  boolean             True on success, false otherwise
     */
    static function add($name, $value, $ord=0, $type='text', $values='', $key=false)
    {
        global $objDatabase;

        // Fail if the module ID is false or the name is invalid
        if (   self::$module_id === false
            || self::$arrSettings === false
            || empty($name))
            return false;

        // This can only be done with a non-empty key!
        if ($key === false) {
            if (self::$key === false) return false;
            // Use the current key, if present
            $key = self::$key;
        }

        // Does the setting exist already?
        $objResult = $objDatabase->Execute("
            SELECT 1
              FROM `".DBPREFIX."core_setting`
             WHERE `name`='".addslashes($name)."'
               AND `module_id`=".self::$module_id.
             (self::$key === false ? '' : " AND `key`='".addslashes(self::$key)."'"));
        if (!$objResult) return self::errorHandler();
        if (!$objResult->EOF) {
            // Entry exists already!
// TODO:  Add error message
            return false;
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
                ".self::$module_id.",
                '".addslashes($key)."',
                '".addslashes($value)."',
                '".addslashes($type)."',
                '".addslashes($values)."',
                $ord
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
            $value_align = (is_numeric($value) ? ' text-align: right;' : '');
            switch ($arrSetting['type']) {
              // Dropdown menu
              case 'dropdown':
                $element =
                    '<select name="'.$name.
                    '" style="width: 220px;'.$value_align.'" >';
                foreach (self::splitValues($arrSetting['values']) as $some_value) {
                    $element .=
                        '<option value="'.$some_value.'"'.
                        ($value == $some_value ? ' selected="selected"' : '').
                        '>'.$some_value.'</option>';
                }
                $element .= '</select>';
                break;
// More...
//              case '':
//                break;
              // Default to text input fields
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
        if (in_array(DBPREFIX."core_setting", $arrTables)) {
            // TODO:  Fix it!
        } else {
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

        // Mind:  For funny values, apply addslashes()!
        $arrRecord = array(
            'hotel_minimum_rooms_days' => array(10013, 'admin', '180', 'text', '180', 0),
            'hotel_default_description_en' => array(10013, 'admin', 'Description', 'text', 'Description', 2),
            'hotel_default_description_de' => array(10013, 'admin', 'Beschreibung', 'text', 'Beschreibung', 1),
            'hotel_per_page_frontend' => array(10013, 'frontend', '10', 'text', '10', 1),
            'hotel_default_order_frontend' => array(10013, 'frontend', 'price DESC', 'text', 'price DESC', 2),
            'hotel_max_pictures' => array(10013, 'frontend', '3', 'text', '3', 2),
            'hotel_per_page_backend' => array(10013, 'backend', '10', 'text', '10', 1),
            'hotel_default_order_backend' => array(10013, 'backend', 'name ASC', 'text', 'name ASC', 2),
        );
        foreach ($arrRecord as $name => $arrSetting) {
            $objResult = $objDatabase->Execute("
                SELECT 1
                  FROM `".DBPREFIX."core_setting`
                 WHERE `name`='".addslashes($name)."'");
            if (!$objResult) return false;
            if ($objResult->EOF) {
                $objResult = $objDatabase->Execute("
                    INSERT INTO `".DBPREFIX."core_setting` (
                        `name`, `module_id`, `key`, `value`, `type`, `values`, `ord`
                    ) VALUES (
                        '".addslashes($name)."', '".join("', '", $arrSetting)."'
                    )");
                if (!$objResult) return false;
echo("SettingDb::errorHandler(): Added record ('".join("', '", $arrSetting)."')<br />");
            }
        }

        // More to come...

        // Always!
        return false;
    }

}

?>
