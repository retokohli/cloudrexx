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
    const TYPE_EMAIL = 'email';
    const TYPE_BUTTON = 'button';
// Not implemented
//    const TYPE_SUBMIT = 'submit';


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
     * Tab counter for the {@see show()} and {@see show_external()}
     * @var     integer
     * @access  private
     */
    private static $tab_index = 1;

    /**
     * Internal error message
     *
     * See {@see getErrorString()}.
     * @var   string
     */
    private static $error_message = '';


    /**
     * Returns the current error message
     *
     * The message is cleared when read, so you *SHOULD* read it once.
     * @return  string                  The error message, if any,
     *                                  or the empty string
     */
    static function getErrorString()
    {
        $error_message = self::$error_message;
        self::$error_message = '';
        return $error_message;
    }


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
     *                                null otherwise
     */
    function getValue($name)
    {
//echo("SettingDb::getValue($name): Value is ".(isset(self::$arrSettings[$name]['value']) ? self::$arrSettings[$name]['value'] : 'NOT FOUND')."<br />");
        return (isset(self::$arrSettings[$name]['value'])
            ? self::$arrSettings[$name]['value'] : null
        );
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
     * Delete a record from the settings table
     *
     * For maintenance/update purposes only.
     * @param   string    $name     The setting name
     * @param   string    $key      The optional key
     * @return  boolean             True on success, false otherwise
     */
    static function delete($name, $key=null)
    {
        global $objDatabase;

        // Fail if the name is invalid
        if (empty($name)) return false;
        $objResult = $objDatabase->Execute("
            DELETE FROM `".DBPREFIX."core_setting`
             WHERE `name`='".addslashes($name)."'".
            (isset($key) ? " AND `key`='".addslashes($key)."'" : ''));
        if (!$objResult) return self::errorHandler();
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
        &$objTemplateLocal, $uriBase,
        $section='', $tab_name='', $prefix='TXT_'
    ) {
        global $objTemplate, $_CORELANG, $_ARRAYLANG;

//$objTemplate->setCurrentBlock();
//echo(nl2br(htmlentities(var_export($objTemplate->getPlaceholderList()))));

        if (   !is_a($objTemplateLocal, 'HTML_Template_Sigma')
            || !$objTemplateLocal->blockExists('core_settingdb_row')) {
            $objTemplateLocal = new HTML_Template_Sigma(ASCMS_ADMIN_TEMPLATE_PATH);
            if (!$objTemplateLocal->loadTemplateFile('settingDb.html'))
                die("Failed to load template settingDb.html");
        }
        Html::replaceUriParameter($uriBase, 'active_tab='.self::$tab_index);
        // Default headings and elements
        $objTemplateLocal->setGlobalVariable(
            $_CORELANG
          + array(
            'URI_BASE' => $uriBase,
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
                    $tab_name, $section
                )
            );
            return true;
        }

        // This is set to multipart if necessary
        $enctype = '';
        $i = 0;
        foreach (self::$arrSettings as $name => $arrSetting) {
            // Determine HTML element for type and apply values and selected
            $element = '';
            $value = $arrSetting['value'];
            $type = $arrSetting['type'];
            // Not implemented yet:
            // Warn if some mandatory value is empty
            if (empty($value) && preg_match('/_mandatory$/', $type)) {
                $objTemplate->setVariable(
                    'CONTENT_STATUS_MESSAGE',
                    sprintf($_CORELANG['TXT_CORE_SETTINGDB_WARNING_EMPTY'],
                        $_ARRAYLANG[$prefix.strtoupper($name)],
                        $name)
                );
            }
            // Warn if some language variable is not defined
            if (empty($_ARRAYLANG[$prefix.strtoupper($name)])) {
                $objTemplate->setVariable(
                    'CONTENT_STATUS_MESSAGE',
                    sprintf($_CORELANG['TXT_CORE_SETTINGDB_WARNING_MISSING_LANGUAGE'],
                        $prefix.strtoupper($name),
                        $name)
                );
            }
            $value_align = (is_numeric($value) ? 'text-align: right;' : '');
//DBG::log("Value: $value -> align $value_align");
            switch ($type) {
              // Dropdown menu
              case self::TYPE_DROPDOWN:
                $arrValues = self::splitValues($arrSetting['values']);
//DBG::log("Values: ".var_export($arrValues, true));
                $element = Html::getSelect(
                    $name, $arrValues, $value,
                    '', '',
                    'style="width: 220px;'.$value_align.'"');
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
//echo("Setting up upload for $name, $value<br />");
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
//DBG::log("SettingDb::show(): Event: $event");
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
//DBG::log("SettingDb::show(): Element: $element");
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
                        'style="width: 220px;'.$value_align.'"');
            }

            $objTemplateLocal->setVariable(array(
                'CORE_SETTINGDB_NAME'      => $_ARRAYLANG[$prefix.strtoupper($name)],
                'CORE_SETTINGDB_VALUE'     => $element,
                'CORE_SETTINGDB_ROWCLASS2' => (++$i % 2 ? '1' : '2'),
            ));
            $objTemplateLocal->parseCurrentBlock();
//echo("SettingDb::show(objTemplateLocal, $prefix): shown $name => $value<br />");
        }

        // Set form encoding to multipart if necessary
        if (!empty($enctype))
            $objTemplateLocal->setVariable('CORE_SETTINGDB_ENCTYPE', $enctype);

        if (   !empty($section)
            && $objTemplateLocal->blockExists('core_settingdb_section')) {
//echo("SettingDb::show(objTemplateLocal, $section, $prefix): creating section $section<br />");
            $objTemplateLocal->setVariable(array(
                'CORE_SETTINGDB_SECTION' => $section,
            ));
            $objTemplateLocal->parse('core_settingdb_section');
        }

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
        global $objTemplate, $_CORELANG, $_ARRAYLANG;

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
     * Update and store all settings found in the $_POST array
     *
     * Sets up an error message in the $error_message class variable
     * on failure.  See {@see getErrorString()}.
     * @return  boolean                 True on success,
     *                                  the empty string if none was changed,
     *                                  or false on failure
     */
    static function storeFromPost()
    {
        global $_CORELANG;

//echo("SettingDb::storeFromPost(): POST:<br />".nl2br(htmlentities(var_export($_POST, true)))."<hr />");
//echo("SettingDb::storeFromPost(): FILES:<br />".nl2br(htmlentities(var_export($_FILES, true)))."<hr />");
        // Compare POST with current settings.
        // Only store what was changed.
        self::init(false);
        unset($_POST['bsubmit']);
        $result = true;
        foreach ($_POST as $name => $value) {
//            if (preg_match('/^'.preg_quote(CSRF::key(), '/').'$/', $name))
//                continue;
            if (empty(self::$arrSettings[$name])) {
// Silently ignore unknown settings for the time being
//                self::$error_message = sprintf(
//                    $_CORELANG['TXT_CORE_SETTINGDB_ERROR_STORING_UNKNOWN_SETTING'],
//                    $name);
//                $result = false;
                continue;
            }
            if (self::$arrSettings[$name]['type'] == 'fileupload') {
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
                            self::$error_message = File::getErrorString();
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
                        self::$error_message = File::getErrorString();
                        $result = false;
                    }
                }
            } else {
                // Regular value of any other type
                $value = contrexx_stripslashes($value);
            }
            SettingDb::set($name, $value);
        }
//echo("SettingDb::storeFromPost(): So far, the result is ".($result ? 'okay' : 'no good')."<br />");
        $result_update = self::updateAll();
        if ($result_update === false)
            self::$error_message = $_CORELANG['TXT_CORE_SETTINGDB_ERROR_STORING'];
        // If nothig bad happened above, return the result of updateAll(),
        // which may be true, false, or the empty string
        if ($result === true) return $result_update;
        // There has been an error anyway
        return false;
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
     * Splits the string value at commas and returns an array of strings
     *
     * Commas escaped by a backslash (\) are ignored and replaced by a
     * single comma.
     * The values themselves may be composed of pairs of key and value,
     * separated by a colon.  Colons escaped by a backslash (\) are ignored
     * and replaced by a single colon.
     * Leading and trailing whitespace is removed from both keys and values.
     * Note that keys *MUST NOT* contain either commas or colons!
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
    function errorHandler()
    {
        global $objDatabase;

//die("SettingDb::errorHandler(): Disabled!<br />");

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
        // new settings.  Example:
//        SettingDb::init('country');
//        SettingDb::add('core_country_per_page_backend', 30, 1, SettingDb::TYPE_TEXT);

        // More to come...

        // Always!
        return false;
    }

}

?>
