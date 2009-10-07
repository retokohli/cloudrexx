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
     * Otherwise, nothing happens, and false is returned
     * @see init(), updateAll()
     * @param   string    $name       The settings name
     * @param   string    $value      The settings value
     * @return  boolean               True if the value has been changed,
     *                                false otherwise
     */
    function set($name, $value)
    {
        if (   isset(self::$arrSettings[$name])
            && self::$arrSettings[$name]['value'] != $value) {
//echo("SettingDb::set($name, $value): Added/updated<br />");
            self::$flagChanged = true;
            self::$arrSettings[$name]['value'] = $value;
            return true;
        }
//echo("SettingDb::set($name, $value): No change, leaving<br />");
        return false;
    }


    /**
     * Stores all settings entries present in the $arrSettings object
     * array variable
     *
     * Returns boolean true if all records were stored successfully,
     * false otherwise.
     * Upon success, also resets the $flagChanged class variable to false.
     * The class *MUST* have been initialized before calling this
     * method using {@see init()}, and the new values been {@see set()}.
     * Note that this method does not work for adding new settings.
     * See {@see add()} on how to do this.
     * @return  boolean                   True on success, false otherwise
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
        return false;
    }


    /**
     * Updates the value for the given name in the settings table
     *
     * The class *MUST* have been initialized before calling this
     * method using {@see init()}, and the new value been {@see set()}.
     * Sets $flagChanged to true and returns true if the value has been
     * updated successfully.
     * Note that this method does not work for adding new settings.
     * See {@see add()} on how to do this.
     * @param   string    $name   The settings name
     * @return  boolean           True on successful update or if
     *                            unchanged, false on failure
     * @static
     * @global  mixed     $objDatabase    Database connection object
     */
    static function update($name)
    {
        global $objDatabase;

        // Fail if the name is invalid
        // or the setting does not exist already
// TODO: Add error messages for individual errors
        if (empty($name)) return false;
        if (!isset(self::$arrSettings[$name])) return false;

        $objResult = $objDatabase->Execute("
            UPDATE `".DBPREFIX."core_setting`
               SET `value`='".addslashes(self::$arrSettings[$name]['value'])."'
             WHERE `name`='".addslashes($name)."'
               AND `module_id`=".MODULE_ID.
             (self::$key !== false ? " AND `key`='".addslashes(self::$key)."'" : ''));
        if (!$objResult) return self::errorHandler();
        self::$flagChanged = true;
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
        if (empty($name)) return false;

        // This can only be done with a non-empty key!
        // Use the current key, if present, otherwise fail
        if ($key === false) {
            if (self::$key === false) return false;
            $key = self::$key;
        }
        // Initialize if necessard
        if (self::$arrSettings === false || self::$key != $key)
            self::init($key);

        // Such an entry exists already, fail
        if (self::getValue($name)) return false;

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
     *    SETTINGDB_NAME        The content of $_ARRAYLANG['TXT_SHOP_DUMMY']
     *    SETTINGDB_VALUE       The HTML element for the setting type with
     *                          a name attribute of 'shop_dummy'
     *
     * Placeholders:
     * The settings' name is to SETTINGDB_NAME, and the input element to
     * SETTINGDB_VALUE.
     * Set the default block to parse after each array entry if it
     * differs from the default 'core_setting_db'.
     * Make sure to define all the language variables that are expected
     * to be defined here!
     * In addition, some entries from $_CORELANG are set up. These are both
     * used as placeholder name and language array index:
     *  - TXT_CORE_SETTINGDB_STORE
     *  - TXT_CORE_SETTINGDB_NAME
     *  - TXT_CORE_SETTINGDB_VALUE
     *
     * The template object is given by reference, and if the block
     * 'core_settingdb_row' is not present, is replaced by the default backend
     * template.
     * $uriBase *SHOULD* be the URI for the current module start page, without
     * any 'act' parameter, i.e. 'index.php?section=mymodule'.
     * If you want your settings to be stored, you *MUST* handle the parameter
     * 'act=settings' in your modules' getPage() method, check for the 'bsubmit'
     * index in the $_POST array, and call {@see SettingDb::store()}.
     * @param   HTML_Template_Sigma $objTemplateLocal   Template object
     * @param   string              $uriBase      The base URI for the module.
     * @param   string              $section      The section header text to add
     * @param   string              $tabName      The tab name to add
     * @param   string              $prefix       The prefix for language variables,
     *                                            defaults to 'TXT_'
     * @return  boolean                           True on success, false otherwise
     * @todo    Add functionality to handle arrays within arrays
     * @todo    Add functionality to handle special form elements
     * @todo    Verify special values like e-mail addresses in methods
     *          that store them, like add(), update(), and updateAll()
     */
    static function show(
        &$objTemplateLocal, $uriBase, $section='', $tabName='', $prefix='TXT_'
    ) {
        global $objTemplate, $_CORELANG, $_ARRAYLANG;
        static $tab_index = 0;

//$objTemplate->setCurrentBlock();
//echo(nl2br(htmlentities(var_export($objTemplate->getPlaceholderList()))));

        if (!$objTemplateLocal->blockExists('core_settingdb_row')) {
            $objTemplateLocal = new HTML_Template_Sigma(ASCMS_ADMIN_TEMPLATE_PATH);
            if (!$objTemplateLocal->loadTemplateFile('settingDb.html'))
                die("Failed to load template settingDb.html");
        }
        if (!preg_match('/[&;]act\=/', $uriBase))
            $uriBase .= '&amp;act=settings';
        $objTemplateLocal->setGlobalVariable('URI_BASE', $uriBase);

        // Default headings and elements
        $objTemplateLocal->setGlobalVariable(array(
            'TXT_CORE_SETTINGDB'       => $_CORELANG['TXT_CORE_SETTINGDB'],
            'TXT_CORE_SETTINGDB_STORE' => $_CORELANG['TXT_CORE_SETTINGDB_STORE'],
            'TXT_CORE_SETTINGDB_NAME'  => $_CORELANG['TXT_CORE_SETTINGDB_NAME'],
            'TXT_CORE_SETTINGDB_VALUE' => $_CORELANG['TXT_CORE_SETTINGDB_VALUE'],
        ));

        if ($objTemplateLocal->blockExists('core_settingdb_row'))
            $objTemplateLocal->setCurrentBlock('core_settingdb_row');
//echo("SettingDb::show(objTemplateLocal, $prefix): got Array: ".var_export(self::$arrSettings, true)."<br />");
        if (!is_array(self::$arrSettings)) {
            $objTemplate->setVariable(
                'CONTENT_STATUS_MESSAGE',
                $_CORELANG['TXT_CORE_SETTINGDB_ERROR_RETRIEVING']
            );
            return false;
        }
        if (empty(self::$arrSettings)) {
            $objTemplate->setVariable(
                'CONTENT_STATUS_MESSAGE',
                sprintf(
                    $_CORELANG['TXT_CORE_SETTINGDB_WARNING_NONE_FOUND_FOR_TAB_AND_SECTION'],
                    $tabName, $section
                )
            );
            return true;
        }

        $i = 0;
        foreach (self::$arrSettings as $name => $arrSetting) {
            // Determine HTML element for type and apply values and selected
            $element = '';
            $value = $arrSetting['value'];

            if (empty($value)) {
                $objTemplate->setVariable(
                    'CONTENT_STATUS_MESSAGE',
                    sprintf($_CORELANG['TXT_CORE_SETTINGDB_WARNING_EMPTY'],
                        $_ARRAYLANG[$prefix.strtoupper($name)],
                        $name)
                );
            }
            if (empty($_ARRAYLANG[$prefix.strtoupper($name)])) {
                $objTemplate->setVariable(
                    'CONTENT_STATUS_MESSAGE',
                    sprintf($_CORELANG['TXT_CORE_SETTINGDB_WARNING_MISSING_LANGUAGE'],
                        $prefix.strtoupper($name),
                        $name)
                );
            }
            $value_align = (is_numeric($value) ? 'text-align: right;' : '');
            switch ($arrSetting['type']) {
              // Dropdown menu
              case 'dropdown':
                $element = Html::getSelect(
                    $name, self::splitValues($arrSetting['values']), $value,
                    '', '',
                    'style="width: 220px;'.$value_align.'"');
                break;
              case 'dropdown_user_custom_attribute':
                $objFWUser = FWUser::getFWUserObject();
                $element = Html::getSelect(
                    $name,
                    $objFWUser->objUser->objAttribute->getCustomAttributeNameArray(),
                    $arrSetting['value'], '', '', 'style="width: 220px;"'
                );
                break;
              case 'dropdown_usergroup':
                $element = Html::getSelect(
                    $name,
                    UserGroup::getNameArray(),
                    $arrSetting['value'],
                    '', '', 'style="width: 220px;"'
                );
                break;
              case 'wysiwyg':
                // These must be treated differently, as wysiwyg editors
                // claim the full width
                $element = get_wysiwyg_editor($name, $value);
                $objTemplateLocal->setVariable(array(
                    'CORE_SETTINGDB_ROW'       => $_ARRAYLANG[$prefix.strtoupper($name)],
                    'CORE_SETTINGDB_ROWCLASS1' => (++$i % 2 ? '1' : '2'),
                ));
                $objTemplateLocal->parseCurrentBlock();
                $objTemplateLocal->setVariable(array(
                    'CORE_SETTINGDB_ROW'       => $element.'<br /><br />',
                    'CORE_SETTINGDB_ROWCLASS1' => (++$i % 2 ? '1' : '2'),
                ));
                $objTemplateLocal->parseCurrentBlock();
                continue 2;

// More...
//              case '':
//                break;

              // Default to text input fields
              case 'text':
              case 'email':
              default:
                $element =
                    Html::getInputText(
                        $name, $value, '',
                        'style="width: 220px;'.$value_align.'"');
            }

            $objTemplateLocal->setVariable(array(
                'CORE_SETTINGDB_NAME'        => $_ARRAYLANG[$prefix.strtoupper($name)],
                'CORE_SETTINGDB_VALUE'       => $element,
                'CORE_SETTINGDB_ROWCLASS2'    => (++$i % 2 ? '1' : '2'),
            ));
            $objTemplateLocal->parseCurrentBlock();
//echo("SettingDb::show(objTemplateLocal, $prefix): shown $name => $value<br />");
        }

        if (   !empty($section)
            && $objTemplateLocal->blockExists('core_settingdb_section')) {
//echo("SettingDb::show(objTemplateLocal, $section, $prefix): creating section $section<br />");
            $objTemplateLocal->setVariable(array(
                'CORE_SETTINGDB_SECTION' => $section,
            ));
            $objTemplateLocal->parse('core_settingdb_section');
        }

        // Set up tab, if any
        if (!empty($tabName)) {
            $objTemplateLocal->setGlobalVariable(array(
                'CORE_SETTINGDB_TAB_NAME'  => $tabName,
                'CORE_SETTINGDB_TAB_INDEX' => ++$tab_index,
                'CORE_SETTINGDB_TAB_CLASS' => ($tab_index == 1 ? 'active' : ''),
                'CORE_SETTINGDB_TAB_DISPLAY' => ($tab_index == 1 ? 'block' : 'none'),
            ));
            $objTemplateLocal->touchBlock('core_settingdb_tab_row');
            $objTemplateLocal->parse('core_settingdb_tab_row');
            $objTemplateLocal->touchBlock('core_settingdb_tab_div');
            $objTemplateLocal->parse('core_settingdb_tab_div');
        }
        return true;
    }


    /**
     * Update and store all settings found in the $_POST array
     * @return  boolean                 True on success,
     *                                  the empty string if none was changed,
     *                                  or false on failure
     */
    static function storeFromPost()
    {
//echo("SettingDb::storeFromPost(): Entered<br />");
        // Compare POST with current settings.
        // Only store what was changed.
        self::init(false);
        unset($_POST['store']);
//        unset($_POST['csrf']);
        foreach ($_POST as $name => $value) {
            $value = contrexx_stripslashes($value);
            SettingDb::set($name, $value);
        }
        return self::updateAll();
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

die("SettingDb::errorHandler(): Disabled!<br />");

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
