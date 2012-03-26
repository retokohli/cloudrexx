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

// From version 3.0.0 only
//require_once ASCMS_CORE_PATH.'/Message.class.php';
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
     * Upload path for documents
     * Used externally only, see hotelcard module for an example.
     */
    const FILEUPLOAD_FOLDER_PATH = 'media';

    /**
     * Setting types
     * See {@see show()} for examples on how to extend these.
     */
    const TYPE_DROPDOWN = 'dropdown';
    const TYPE_DROPDOWN_USER_CUSTOM_ATTRIBUTE = 'dropdown_user_custom_attribute';
    const TYPE_DROPDOWN_USERGROUP = 'dropdown_usergroup';
    const TYPE_WYSIWYG = 'wysiwyg';
    const TYPE_FILEUPLOAD = 'fileupload';
    const TYPE_TEXT = 'text';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_EMAIL = 'email';
    const TYPE_BUTTON = 'button';
// 20110224
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_CHECKBOXGROUP = 'checkboxgroup';
// Not implemented
//    const TYPE_SUBMIT = 'submit';

    /**
     * Default width for input fields
     *
     * Note that textareas often use twice that value.
     */
    const DEFAULT_INPUT_WIDTH = 220;

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
    private static $arrSettings = null;

    /**
     * The key last used to {@see init()} the settings.
     * Defaults to null (ignored).
     * @var     string
     * @static
     * @access  private
     */
    private static $key = null;

    /**
     * The module ID last used to {@see init()} the settings.
     * Defaults to null (which will cause an error in most methods).
     * @var     integer
     * @static
     * @access  private
     */
    private static $module_id = null;

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
     * Tab counter for the {@see show()} and {@see show_external()}
     * @var     integer
     * @access  private
     */
    private static $tab_index = 1;


    /**
     * Returns the current value of the tab index
     * @return  integer             The current tab index
     */
    static function getTabIndex()
    {
        return self::$tab_index;
    }


    /**
     * Initialize the settings entries from the database with key/value pairs
     * for the current module ID and the given key
     *
     * An empty $key value is ignored.  All records with the module ID
     * (taken from the global MODULE_ID constant if missing) are included in
     * this case.
     * Note that the setting name *SHOULD* be unambiguous whether $key is
     * empty or not.  If there are two settings with the same name but different
     * $key values, the second one will overwrite the first!
     * @internal  The records are ordered by
     *            `key` ASC, `ord` ASC, `name` ASC
     * @param   string    $key        The key, or an empty value.
     *                                Defaults to the empty string
     * @param   integer   $module_id  The optional module ID.  *MUST* be set
     *                                unless the global MODULE_ID is defined.
     *                                Defaults to null.
     * @return  boolean               True on success, false otherwise
     */
    static function init($key='', $module_id=null)
    {
        global $objDatabase;

        if (empty($module_id)) {
            if (!defined('MODULE_ID')) {
                return false;
            }
            $module_id = MODULE_ID;
        }
        self::$module_id = $module_id;
        self::flush();
//echo("SettingDb::init($key): Entered<br />");
        $objResult = $objDatabase->Execute("
            SELECT `name`, `key`, `value`,
                   `type`, `values`, `ord`
              FROM ".DBPREFIX."core_setting
             WHERE `module_id`=".$module_id.
             ($key ? " AND `key`='".addslashes($key)."'" : '')."
             ORDER BY `key` ASC, `ord` ASC, `name` ASC");
        if (!$objResult) return self::errorHandler();
        // Set the current key to the empty string if empty
        self::$key = ($key ? $key : '');
        self::$arrSettings = array();
        while (!$objResult->EOF) {
            self::$arrSettings[$objResult->fields['name']] = array(
                'module_id' => $module_id,
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
     * Resets the class to its initial state.
     * Does *NOT* clear the module ID, however.
     * @return  void
     */
    static function flush()
    {
        self::$arrSettings = null;
        self::$key         = null;
        self::$flagChanged = null;
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
     * @param   string    $key        The optional key
     * @return  array                 The settings array on success,
     *                                false otherwise
     */
    static function getArray($key='')
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
     * if no setting of that name is present in the current set, null
     * is returned.
     * @param   string    $name       The settings name
     * @return  mixed                 The settings value, if present,
     *                                null otherwise
     */
    static function getValue($name)
    {
        if (is_null(self::$arrSettings)) {
DBG::log("SettingDb::getValue($name): ERROR: no settings loaded");
            return null;
        }
//echo("SettingDb::getValue($name): Value is ".(isset(self::$arrSettings[$name]['value']) ? self::$arrSettings[$name]['value'] : 'NOT FOUND')."<br />");
        if (isset(self::$arrSettings[$name]['value'])) {
            return self::$arrSettings[$name]['value'];
        };
DBG::log("SettingDb::getValue($name): ERROR: unknown setting '$name' (current key ".var_export(self::$key, true).")");
        return null;
    }


    /**
     * Updates a setting
     *
     * If the setting name exists and the new value is not equal to
     * the old one, it is updated, and $flagChanged set to true.
     * Otherwise, nothing happens, and false is returned
     * @see init(), updateAll()
     * @param   string    $name       The settings name
     * @param   string    $value      The settings value
     * @return  boolean               True if the value has been changed,
     *                                false otherwise
     */
    static function set($name, $value)
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
    static function updateAll()
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

        if (empty(self::$module_id)) {
// TODO: Error message
            return false;
        }
        // Fail if the name is invalid
        // or the setting does not exist already
// TODO: Add error messages for individual errors
        if (empty($name)) {
//echo("Empty setting name: $name<br />");
            return false;
        }
        if (!isset(self::$arrSettings[$name])) {
//echo("Unknown setting: $name<br />");
            return false;
        }
        $objResult = $objDatabase->Execute("
            UPDATE `".DBPREFIX."core_setting`
               SET `value`='".addslashes(self::$arrSettings[$name]['value'])."'
             WHERE `name`='".addslashes($name)."'
               AND `module_id`=".self::$module_id.
             (self::$key ? " AND `key`='".addslashes(self::$key)."'" : ''));
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
    static function add(
        $name, $value, $ord=false, $type='text', $values='', $key=null)
    {
        global $objDatabase;

        if (empty(self::$module_id)) {
// TODO: Error message
            return false;
        }
        // Fail if the name is invalid
        if (empty($name)) return false;

        // This can only be done with a non-empty key!
        // Use the current key, if present, otherwise fail
        if (!$key) {
            if (!self::$key) return false;
            $key = self::$key;
        }
        // Initialize if necessary
        if (is_null(self::$arrSettings) || self::$key != $key)
            self::init($key);

        // Such an entry exists already, fail.
        // Note that getValue() returns null if the entry is not present
        $old_value = self::getValue($name);
        if (isset($old_value)) return false;

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
                ".intval($ord)."
            )");
        if (!$objResult) return self::errorHandler();
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
     * @param   string    $key      The optional key.
     *                              Defaults to null
     * @return  boolean             True on success, false otherwise
     */
    static function delete($name=null, $key=null)
    {
        global $objDatabase;

        // Fail if both parameter values are empty
        if (empty($name) && empty($key)) return false;
        $objResult = $objDatabase->Execute("
            DELETE FROM `".DBPREFIX."core_setting`
             WHERE 1".
            ($name ? " AND `name`='".addslashes($name)."'" : '').
            ($key  ? " AND `key`='".addslashes($key)."'"   : ''));
        if (!$objResult) return self::errorHandler();
        self::flush();
        return true;
    }


    /**
     * Display the settings present in the $arrSettings class array
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
     *    SETTINGDB_NAME      The content of $_ARRAYLANG['TXT_SHOP_DUMMY']
     *    SETTINGDB_VALUE     The HTML element for the setting type with
     *                        a name attribute of 'shop_dummy'
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
     * $uriBase *SHOULD* be the URI for the current module page.
     * If you want your settings to be stored, you *MUST* handle the post
     * request, check for the 'bsubmit' index in the $_POST array, and call
     * {@see SettingDb::store()}.
     * @param   HTML_Template_Sigma $objTemplateLocal   Template object
     * @param   string              $uriBase      The base URI for the module.
     * @param   string              $section      The optional section header
     *                                            text to add
     * @param   string              $tab_name     The optional tab name to add
     * @param   string              $prefix       The optional prefix for
     *                                            language variables.
     *                                            Defaults to 'TXT_'
     * @return  boolean                           True on success, false otherwise
     * @todo    Add functionality to handle arrays within arrays
     * @todo    Add functionality to handle special form elements
     * @todo    Verify special values like e-mail addresses in methods
     *          that store them, like add(), update(), and updateAll()
     */
    static function show(
        &$objTemplateLocal, $uriBase, $section='', $tab_name='', $prefix='TXT_'
    ) {
        global $_CORELANG, $_ARRAYLANG;

//$objTemplate->setCurrentBlock();
//echo(nl2br(htmlentities(var_export($objTemplate->getPlaceholderList()))));

        self::verify_template($objTemplateLocal);
// TODO: Test if everything works without this line
//        Html::replaceUriParameter($uriBase, 'act=settings');
        Html::replaceUriParameter($uriBase, 'active_tab='.self::$tab_index);
        // Default headings and elements
        $objTemplateLocal->setGlobalVariable(
            $_CORELANG
          + array(
            'URI_BASE' => $uriBase,
        ));

//echo("SettingDb::show(objTemplateLocal, $prefix): got Array: ".var_export(self::$arrSettings, true)."<br />");
        if (!is_array(self::$arrSettings)) {
// TODO: Error message
//            Message::add($_CORELANG['TXT_CORE_SETTINGDB_ERROR_RETRIEVING'],
//                Message::MSG_CLASS_ERROR);
//die("No Settings array");
            return false;
        }
        if (empty(self::$arrSettings)) {
// TODO: Error message
//            Message::add(
//                sprintf(
//                    $_CORELANG['TXT_CORE_SETTINGDB_WARNING_NONE_FOUND_FOR_TAB_AND_SECTION'],
//                    $tab_name, $section),
//                Message::MSG_CLASS_WARN);
//die("No Settings found");
            return false;
        }
        self::show_section($objTemplateLocal, $section, $prefix);
        // The tabindex must be set in the form name in any case
        $objTemplateLocal->setGlobalVariable(
            'CORE_SETTINGDB_TAB_INDEX', self::$tab_index);
        // Set up tab, if any
        if (!empty($tab_name)) {
            $active_tab = (isset($_REQUEST['active_tab']) ? $_REQUEST['active_tab'] : 1);
            $objTemplateLocal->setGlobalVariable(array(
                'CORE_SETTINGDB_TAB_NAME'    => $tab_name,
//                'CORE_SETTINGDB_TAB_INDEX'   => self::$tab_index,
                'CORE_SETTINGDB_TAB_CLASS'   => (self::$tab_index == $active_tab ? 'active' : ''),
                'CORE_SETTINGDB_TAB_DISPLAY' => (self::$tab_index++ == $active_tab ? 'block' : 'none'),
            ));
            $objTemplateLocal->touchBlock('core_settingdb_tab_row');
            $objTemplateLocal->parse('core_settingdb_tab_row');
            $objTemplateLocal->touchBlock('core_settingdb_tab_div');
            $objTemplateLocal->parse('core_settingdb_tab_div');
        }

// NOK
//die(nl2br(contrexx_raw2xhtml(var_export($objTemplateLocal, true))));

        return true;
    }



    /**
     * Display a section of settings present in the $arrSettings class array
     *
     * See the description of {@see show()} for details.
     * @param   HTML_Template_Sigma $objTemplateLocal   The Template object,
     *                                                  by reference
     * @param   string              $section      The optional section header
     *                                            text to add
     * @param   string              $prefix       The optional prefix for
     *                                            language variables.
     *                                            Defaults to 'TXT_'
     * @return  boolean                           True on success, false otherwise
     */
    static function show_section(&$objTemplateLocal, $section='', $prefix='TXT_')
    {
        global $_ARRAYLANG;

        self::verify_template(&$objTemplateLocal);
        // This is set to multipart if necessary
        $enctype = '';
        $i = 0;
        if ($objTemplateLocal->blockExists('core_settingdb_row'))
            $objTemplateLocal->setCurrentBlock('core_settingdb_row');
        foreach (self::$arrSettings as $name => $arrSetting) {
            // Determine HTML element for type and apply values and selected
            $element = '';
            $value = $arrSetting['value'];
            $values = self::splitValues($arrSetting['values']);
            $type = $arrSetting['type'];
            // Not implemented yet:
            // Warn if some mandatory value is empty
            if (empty($value) && preg_match('/_mandatory$/', $type)) {
// TODO: Error message
//                Message::add(
//                    sprintf($_CORELANG['TXT_CORE_SETTINGDB_WARNING_EMPTY'],
//                        $_ARRAYLANG[$prefix.strtoupper($name)],
//                        $name),
//                    Message::MSG_CLASS_WARN);
            }
            // Warn if some language variable is not defined
            if (empty($_ARRAYLANG[$prefix.strtoupper($name)])) {
// TODO: Error message
//                Message::add(
//                    sprintf($_CORELANG['TXT_CORE_SETTINGDB_WARNING_MISSING_LANGUAGE'],
//                        $prefix.strtoupper($name),
//                        $name),
//                    Message::MSG_CLASS_WARN);
            }
//DBG::log("Value: $value -> align $value_align");
            switch ($type) {
              // Dropdown menu
              case self::TYPE_DROPDOWN:
                $arrValues = self::splitValues($arrSetting['values']);
//DBG::log("Values: ".var_export($arrValues, true));
                $element = Html::getSelect(
                    $name, $arrValues, $value,
                    '', '',
                    'style="width: 220px;'.
                    (   isset ($arrValues[$value])
                     && is_numeric($arrValues[$value])
                        ? 'text-align: right;' : '').
                    '"');
                break;
              case self::TYPE_DROPDOWN_USER_CUSTOM_ATTRIBUTE:
                $element = Html::getSelect(
                    $name,
                    User_Profile_Attribute::getCustomAttributeNameArray(),
                    $arrSetting['value'], '', '', 'style="width: 220px;"'
                );
                break;
              case self::TYPE_DROPDOWN_USERGROUP:
                $element = Html::getSelect(
                    $name,
                    UserGroup::getNameArray(),
                    $arrSetting['value'],
                    '', '', 'style="width: 220px;"'
                );
                break;
              case self::TYPE_WYSIWYG:
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
                // Skip the part below, all is done already
                continue 2;

              case self::TYPE_FILEUPLOAD:
//echo("SettingDb::show_section(): Setting up upload for $name, $value<br />");
                $element =
                    Html::getInputFileupload(
                        // Set the ID only if the $value is non-empty.
                        // This toggles the file name and delete icon on or off
                        $name, ($value ? $name : false),
                        Filetype::MAXIMUM_UPLOAD_FILE_SIZE,
                        // "values" defines the MIME types allowed
                        $arrSetting['values'],
                        'style="width: 220px;"', true,
                        ($value
                          ? $value
                          : 'media/'.
                            (isset($_REQUEST['cmd'])
                                ? $_REQUEST['cmd'] : 'other'))
                    );
                // File uploads must be multipart encoded
                $enctype = 'enctype="multipart/form-data"';
                break;

              case self::TYPE_BUTTON:
                // The button is only available to trigger some event.
                $event =
                    'onclick=\''.
                      'if (confirm("'.$_ARRAYLANG[$prefix.strtoupper($name).'_CONFIRM'].'")) {'.
                        'document.getElementById("'.$name.'").value=1;'.
                        'document.formSettings_'.self::$tab_index.'.submit();'.
                      '}\'';
//DBG::log("SettingDb::show_section(): Event: $event");
                $element =
                    Html::getInputButton(
                        // The button itself gets a dummy name attribute value
                        '__'.$name,
                        $_ARRAYLANG[strtoupper($prefix.$name).'_LABEL'],
                        'button', false,
                        $event
                    ).
                    // The posted value is set to 1 when confirmed,
                    // before the form is posted
                    Html::getHidden($name, 0, '');
//DBG::log("SettingDb::show_section(): Element: $element");
                break;

              case self::TYPE_TEXTAREA:
                $element =
                    Html::getTextarea($name, $value, 80, 8, '');
//                        'style="width: 220px;'.$value_align.'"');
                break;

              case self::TYPE_CHECKBOX:
                $arrValues = self::splitValues($arrSetting['values']);
                $value_true = current($arrValues);
                $element =
                    Html::getCheckbox($name, $value_true, false,
                        in_array($value, $arrValues));
                break;
              case self::TYPE_CHECKBOXGROUP:
                $checked = self::splitValues($value);
//DBG::log("Values: ".var_export($values, true).", checked: ".var_export($checked, true)."<br />");
                $element =
                    Html::getCheckboxGroup($name, $values, $values, $checked,
                        '', '', '<br />', '', '');
                break;

// More...
//              case self::TYPE_:
//                break;

              // Default to text input fields
              case self::TYPE_TEXT:
              case self::TYPE_EMAIL:
              default:
                $element =
                    Html::getInputText(
                        $name, $value, false,
                        'style="width: 220px;'.
                        (is_numeric($value) ? 'text-align: right;' : '').
                        '"');
            }

            $objTemplateLocal->setVariable(array(
                'CORE_SETTINGDB_NAME'      => $_ARRAYLANG[$prefix.strtoupper($name)],
                'CORE_SETTINGDB_VALUE'     => $element,
                'CORE_SETTINGDB_ROWCLASS2' => (++$i % 2 ? '1' : '2'),
            ));
            $objTemplateLocal->parseCurrentBlock();
//echo("SettingDb::show_section(objTemplateLocal, $prefix): shown $name => $value<br />");
        }

        // Set form encoding to multipart if necessary
        if (!empty($enctype))
            $objTemplateLocal->setVariable('CORE_SETTINGDB_ENCTYPE', $enctype);

        if (   !empty($section)
            && $objTemplateLocal->blockExists('core_settingdb_section')) {
//echo("SettingDb::show_section(objTemplateLocal, $section, $prefix): creating section $section<br />");
            $objTemplateLocal->setVariable(array(
                'CORE_SETTINGDB_SECTION' => $section,
            ));
            $objTemplateLocal->parse('core_settingdb_section');
        }
//DBG::log("SettingDb::show_section(): Made sections:<hr />".$objTemplateLocal->get('core_settingdb_sections')."<hr />");
        return true;
    }


    /**
     * Adds an external settings view to the current template
     *
     * The content must contain the full view, including the surrounding form
     * tags and submit button.
     * Note that these are always appended on the right end of the tab list.
     * @param   HTML_Template_Sigma $objTemplateLocal   Template object
     * @param   string              $tab_name           The tab name to add
     * @param   string              $content            The external content
     */
    static function show_external(
        &$objTemplateLocal, $tab_name, $content
    ) {
        global $_CORELANG, $_ARRAYLANG;

//$objTemplate->setCurrentBlock();
//echo(nl2br(htmlentities(var_export($objTemplate->getPlaceholderList()))));

        if (   empty($objTemplateLocal)
            || !$objTemplateLocal->blockExists('core_settingdb_row')) {
            $objTemplateLocal = new HTML_Template_Sigma(ASCMS_ADMIN_TEMPLATE_PATH);
            if (!$objTemplateLocal->loadTemplateFile('settingDb.html'))
                die("Failed to load template settingDb.html");
        }

        $active_tab = (isset($_REQUEST['active_tab']) ? $_REQUEST['active_tab'] : 1);
        // The tabindex must be set in the form name in any case
        $objTemplateLocal->setGlobalVariable(array(
            'CORE_SETTINGDB_TAB_INDEX' => self::$tab_index,
            'CORE_SETTINGDB_EXTERNAL' => $content,
        ));
        // Set up the tab, if any
        if (!empty($tab_name)) {
            $objTemplateLocal->setGlobalVariable(array(
                'CORE_SETTINGDB_TAB_NAME'    => $tab_name,
//                'CORE_SETTINGDB_TAB_INDEX'   => self::$tab_index,
                'CORE_SETTINGDB_TAB_CLASS'   => (self::$tab_index == $active_tab ? 'active' : ''),
                'CORE_SETTINGDB_TAB_DISPLAY' => (self::$tab_index++ == $active_tab ? 'block' : 'none'),
            ));
            $objTemplateLocal->touchBlock('core_settingdb_tab_row');
            $objTemplateLocal->parse('core_settingdb_tab_row');
            $objTemplateLocal->touchBlock('core_settingdb_tab_div_external');
            $objTemplateLocal->parse('core_settingdb_tab_div_external');
        }
        return true;
    }


    /**
     * Ensures that a valid template is available
     *
     * Die()s if the template given is invalid, and settingDb.html cannot be
     * loaded to replace it.
     * @param   HTML_Template_Sigma $objTemplateLocal   The template,
     *                                                  by reference
     */
    static function verify_template(&$objTemplateLocal)
    {
        // "instanceof" considers subclasses of Sigma to be a Sigma, too!
        if (!($objTemplateLocal instanceof HTML_Template_Sigma)) {
            $objTemplateLocal = new HTML_Template_Sigma(ASCMS_ADMIN_TEMPLATE_PATH);
        }
        if (!$objTemplateLocal->blockExists('core_settingdb_row')) {
            $objTemplateLocal->setRoot(ASCMS_ADMIN_TEMPLATE_PATH);
//            $objTemplateLocal->setCacheRoot('.');
            if (!$objTemplateLocal->loadTemplateFile('settingDb.html'))
                die("Failed to load template settingDb.html");
//die(nl2br(contrexx_raw2xhtml(var_export($objTemplateLocal, true))));
        }
    }


    /**
     * Update and store all settings found in the $_POST array
     *
     * Note that you *MUST* call {@see init()} beforehand, or your settings
     * will be unknown and thus not be stored.
     * @todo    Set up error messages on various problems and on failure.
     * @return  boolean                         True on success, the empty
     *                                          string if none was changed,
     *                                          or false on failure
     */
    static function storeFromPost()
    {
        global $_CORELANG;

//echo("SettingDb::storeFromPost(): POST:<br />".nl2br(htmlentities(var_export($_POST, true)))."<hr />");
//echo("SettingDb::storeFromPost(): FILES:<br />".nl2br(htmlentities(var_export($_FILES, true)))."<hr />");
        // There may be several tabs for different keys being edited, so
        // load the full set of settings for the module.
        // Note that this is why setting names should be unique.
// TODO: You *MUST* call this yourself *before* in order to
// properly initialize the module ID!
//        self::init();
        unset($_POST['bsubmit']);
        $result = true;
        // Compare POST with current settings and only store what was changed.
        foreach (array_keys(self::$arrSettings) as $name) {
            $value = (isset ($_POST[$name])
                ? contrexx_input2raw($_POST[$name])
                : null);
//            if (preg_match('/^'.preg_quote(CSRF::key(), '/').'$/', $name))
//                continue;
            if (empty(self::$arrSettings[$name])) {
                if (!$ignore_unknown) {
// TODO: Error message
//                Message::add(sprintf(
//                    $_CORELANG['TXT_CORE_SETTINGDB_ERROR_STORING_UNKNOWN_SETTING'],
//                    $name), Message::MSG_CLASS_WARN);
// Ignore unknown settings for the time being
//                $result = false;
                }
                continue;
            }
            switch (self::$arrSettings[$name]['type']) {
              case self::TYPE_FILEUPLOAD:
                // An empty folder path has been posted, indicating that the
                // current file should be removed
                if (empty($value)) {
//echo("Empty value, deleting file...<br />");
                    if (self::$arrSettings[$name]['value']) {
                        if (File::delete_file(self::$arrSettings[$name]['value'])) {
//echo("File deleted<br />");
                            $value = '';
                        } else {
//echo("Failed to delete file<br />");
// TODO: Error message
//                            Message::add(File::getErrorString(),
//                                Message::MSG_CLASS_ERROR);
                            $result = false;
                        }
                    }
                } else {
                    // No file uploaded.  Skip.
                    if (empty($_FILES[$name]['name'])) continue;
                    // $value is the target folder path
                    $target_path = $value.'/'.$_FILES[$name]['name'];
// TODO: Test if this works in all browsers:
                    // The path input field name is the same as the
                    // file upload input field name!
                    $result_upload = File::upload_file_http(
                        $name, $target_path,
                        Filetype::MAXIMUM_UPLOAD_FILE_SIZE,
                        // The allowed file types
                        self::$arrSettings[$name]['values']
                    );
                    // If no file has been uploaded at all, ignore the no-change
                    if ($result_upload === '') continue;
                    if ($result_upload === true) {
                        $value = $target_path;
                    } else {
//echo("SettingDb::storeFromPost(): Error uploading file for setting $name to $target_path<br />");
// TODO: Add error message
//                        Message::add(File::getErrorString(), Message::MSG_CLASS_ERROR);
                        $result = false;
                    }
                }
                break;
              case self::TYPE_CHECKBOX:
                  break;
              case self::TYPE_CHECKBOXGROUP:
                $value = (is_array($value)
                    ? join(',', array_keys($value))
                    : $value);
              default:
                // Regular value of any other type
                break;
            }
            SettingDb::set($name, $value);
        }
//echo("SettingDb::storeFromPost(): So far, the result is ".($result ? 'okay' : 'no good')."<br />");
        $result_update = self::updateAll();
        if ($result_update === false) {
// TODO: Error message
//            Message::add($_CORELANG['TXT_CORE_SETTINGDB_ERROR_STORING'],
//                Message::MSG_CLASS_ERROR);
        } elseif ($result_update === true) {
// TODO: Error message
//            Message::add($_CORELANG['TXT_CORE_SETTINGDB_STORED_SUCCESSFULLY']);
        }
        // If nothing bad happened above, return the result of updateAll(),
        // which may be true, false, or the empty string
        if ($result === true) {
            return $result_update;
        }
        // There has been an error anyway
        return false;
    }


    /**
     * Deletes all entries for the current module
     *
     * This is for testing purposes only.  Use with care!
     * The static $module_id determines the current module ID.
     * @return    boolean               True on success, false otherwise
     */
    static function deleteModule()
    {
        global $objDatabase;

        if (empty(self::$module_id)) {
// TODO: Error message
            return false;
        }
        $objResult = $objDatabase->Execute("
            DELETE FROM `".DBPREFIX."core_setting`
             WHERE `module_id`=".self::$module_id);
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
//DBG::log("Split $key and $value");
            }
            str_replace(array('\\,', '\\:'), array(',', ':'), $value);
            if (isset($key)) {
                $arrValues[$key] = $value;
            } else {
                $arrValues[] = $value;
            }
//DBG::log("Split $key and $value");
        }
//DBG::log("Array: ".var_export($arrValues, true));
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
     */
    static function errorHandler()
    {
        global $objDatabase;

//die("SettingDb::errorHandler(): Disabled!<br />");

        $table_name = DBPREFIX.'core_setting';
        $arrTables = $objDatabase->MetaTables('TABLES');
        if (!in_array($table_name, $arrTables)) {
            $query = "
                CREATE TABLE `$table_name` (
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
            if (!$objResult) {
DBG::log("SettingDb::errorHandler(): ERROR: Failed to create table $table_name<br />");
                return false;
            }
DBG::log("SettingDb::errorHandler(): Successfully created table $table_name<br />");
        }

        // Use SettingDb::add(); in your module code to add missing and
        // new settings.
//        SettingDb::init('country');
//        SettingDb::add('core_country_per_page_backend', 30, 1, SettingDb::TYPE_TEXT);

        // More to come...

        // Always!
        return false;
    }

}
