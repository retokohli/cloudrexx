<?php
/**
 * Manages settings stored in the database or file system    
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com> (parts)
 * @author      Manish Thakur <manishthakur@cdnsol.com>
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  core_setting
 * @todo        Edit PHP DocBlocks!
 */
 
namespace Cx\Core\Setting\Controller;

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
class SettingException extends \Exception {}


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
class Setting{
     
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
    // 20120508
    const TYPE_RADIO = 'radio';
    // Not implemented
    //const TYPE_SUBMIT = 'submit';

    /**
     * Default width for input fields   
     *
     * Note that textareas often use twice that value.
     */
    const DEFAULT_INPUT_WIDTH = 300;

    const TYPE_PASSWORD = 'password';
    
    /**
     * Default \Cx\Core\Setting\Model\Entity\DbEngine    
     *
     */
    private static $engineType = '\Cx\Core\Setting\Model\Entity\DbEngine'; 
    
    
    /**
     * Returns the current value of the changed flag.    
     *
     * If it returns true, you probably want to call {@see updateAll()}.
     * @return  boolean           True if values have been changed in memory,
     *                            false otherwise
     */
    static function changed()
    {
        $engineType=self::getEngineType();
        return $engineType::changed();  
    }
    /**
     * Optionally sets and returns the value of the tab index    
     * @param   integer  $tab_index The optional new tab index
     * @return  integer             The current tab index
     */
    static function tab_index($tab_index=null)
    {
        $engineType=self::getEngineType();
        return $engineType::tab_index($tab_index);
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
     * @param   string    $engine     The Engine type Database or File system
     *                                Default to set Database 
     * @param   string    $fileSystemConfigRepository     An optional path
     *                                to the storage location of config files (/config) which shall be used for the engine 'File System'.
     *                                Default to set Database 
     * @return  boolean               True on success, false otherwise
     * @global  ADOConnection   $objDatabase
     */
    static function init($section, $group=null,$engine = 'Database', $fileSystemConfigRepository = null)
    {
        
        if($engine=="Database" || empty($engine)){ //default
            \Cx\Core\Setting\Model\Entity\DbEngine::init($section, $group);
            self::setEngineType('\Cx\Core\Setting\Model\Entity\DbEngine');
        }elseif($engine=="FileSystem"){ //optional
            \Cx\Core\Setting\Model\Entity\FileSystem::init($section, $group, $fileSystemConfigRepository);
            self::setEngineType('\Cx\Core\Setting\Model\Entity\FileSystem');
        }elseif($engine=="Yaml"){ //optional
            \Cx\Core\Setting\Model\Entity\YamlEngine::init($section, $group, $fileSystemConfigRepository);
            self::setEngineType('\Cx\Core\Setting\Model\Entity\YamlEngine');
        }else{
            throw new SettingException('Invalid arguments supplied');
            return false;
        }
        return true;
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
        $engineType=self::getEngineType();
        return $engineType::flush();
    }
    /**
     * Returns the settings array for the given section and group
     *
     * See {@see init()} on how the arguments are used.
     * If the method is called successively using the same $group argument,
     * the current settings are returned without calling {@see init()}.
     * Thus, changes made by calling {@see set()} will be preserved.
     * @param   string    $section    The section
     * @param   string    $group      The optional group
     * @return  array                 The settings array on success,
     *                                false otherwise
     */
     static function getArray($section, $group=null)
    {
        $engineType=self::getEngineType();
        return $engineType::getArray($section,$group);
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
        $engineType=self::getEngineType();
        return $engineType::getValue($name);  
    }
    /**
     * Returns the true or false, if settings name is exist or not .
     *
     * If the settings have not been initialized (see {@see init()}), or
     * if no setting of that name is present in the current set, false
     * is returned.
     * @param   string    $name       The settings name
     * @return  boolean               The settings name, if present,
     *                                true otherwise false
     */
    static function isDefined($name)
    {
        $engineType=self::getEngineType();
        return $engineType::isDefined($name);  
        
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
        $engineType=self::getEngineType();
        return $engineType::set($name, $value);  
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
        $engineType=self::getEngineType();
        return $engineType::updateAll();  
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
        $engineType=self::getEngineType();
        return $engineType::update($name);  
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
        $engineType=self::getEngineType();
        return $engineType::add( $name, $value, $ord, $type, $values, $group);  
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
        $engineType=self::getEngineType();
        return $engineType::delete($name, $group);
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
     *    SETTING_NAME      The content of $_ARRAYLANG['TXT_SHOP_DUMMY']
     *    SETTING_VALUE     The HTML element for the setting type with
     *                        a name attribute of 'shop_dummy'
     *
     * Placeholders:
     * The settings' name is to SETTING_NAME, and the input element to
     * SETTING_VALUE.
     * Set the default block to parse after each array entry if it
     * differs from the default 'core_setting'.
     * Make sure to define all the language variables that are expected
     * to be defined here!
     * In addition, some entries from $_CORELANG are set up. These are both
     * used as placeholder name and language array index:
     *  - TXT_CORE_SETTING_STORE
     *  - TXT_CORE_SETTING_NAME
     *  - TXT_CORE_SETTING_VALUE
     *
     * The template object is given by reference, and if the block
     * 'core_setting_row' is not present, is replaced by the default backend
     * template.
     * $uriBase *SHOULD* be the URI for the current module page.
     * If you want your settings to be stored, you *MUST* handle the post
     * request, check for the 'bsubmit' index in the $_POST array, and call
     * {@see \Cx\Core\Setting\Controller\Setting::store()}.
     * @param   \Cx\Core\Html\Sigma $objTemplateLocal   Template object
     * @param   string              $uriBase      The base URI for the module.
     * @param   string              $section      The optional section header
     *                                            text to add
     * @param   string              $tab_name     The optional tab name to add
     * @param   string              $prefix       The optional prefix for
     *                                            language variables.
     *                                            Defaults to 'TXT_'
     * @param   boolean             $readOnly     Optional argument to make the generated form read-only. Defaults to false.
     * @return  boolean                           True on success, false otherwise
     * @todo    Add functionality to handle arrays within arrays
     * @todo    Add functionality to handle special form elements
     * @todo    Verify special values like e-mail addresses in methods
     *          that store them, like add(), update(), and updateAll()
     * @adding tooltip   A tooltip for a configuration option can be defined by simply
     *                   adding a language variable in the corresponding language
     *                   file, that uses the same key as used for 
     *                   the configuration variable itself, but with the suffix _TOOLTIP 
     *                   I.e. if the language variable for an option is TXT_CORE_MODULE_MULTISITE_INSTANCESPATH, 
     *                   then the option's tooltip language variable key would be TXT_CORE_MODULE_MULTISITE_INSTANCESPATH_TOOLTIP
     */
    static function show(&$objTemplateLocal, $uriBase, $section='', $tab_name='', $prefix='TXT_', $readOnly = false) 
    {
        global $_CORELANG;
        $engineType=self::getEngineType();
        $arrSettings=$engineType::getArraySetting();
        self::verify_template($objTemplateLocal);
        \Html::replaceUriParameter($uriBase, 'active_tab='.$engineType::$tab_index);
        // Default headings and elements
        $objTemplateLocal->setGlobalVariable(
            $_CORELANG
          + array(
            'URI_BASE' => $uriBase,
        ));

        if ($objTemplateLocal->blockExists('core_setting_row')){
                $objTemplateLocal->setCurrentBlock('core_setting_row');
        }        
        if (!is_array($arrSettings)) {
        //die("No Settings array");
            return \Message::error($_CORELANG['TXT_CORE_SETTING_ERROR_RETRIEVING']);
        }
        if (empty($arrSettings)) {
            //die("No Settings found");
            \Message::warning(
                sprintf(
                    $_CORELANG['TXT_CORE_SETTING_WARNING_NONE_FOUND_FOR_TAB_AND_SECTION'],
                    $tab_name, $section));
            return false;
        }
        self::show_section($objTemplateLocal, $section, $prefix, $readOnly);
        // The tabindex must be set in the form name in any case
        $objTemplateLocal->setGlobalVariable(
            'CORE_SETTING_TAB_INDEX', $engineType::$tab_index);
        // Set up tab, if any
        if (!empty($tab_name)) {
            $active_tab = (isset($_REQUEST['active_tab']) ? $_REQUEST['active_tab'] : 1);
            $objTemplateLocal->setGlobalVariable(array(
                'CORE_SETTING_TAB_NAME' => $tab_name,
            //  'CORE_SETTING_TAB_INDEX' => self::$tab_index,
                'CORE_SETTING_TAB_CLASS' => ($engineType::$tab_index == $active_tab ? 'active' : ''),
                'CORE_SETTING_TAB_DISPLAY' => ($engineType::$tab_index++ == $active_tab ? 'block' : 'none'),
                'CORE_SETTING_CURRENT_TAB'=>'tab-'.$active_tab
            ));
            $objTemplateLocal->touchBlock('core_setting_header');
            $objTemplateLocal->touchBlock('core_setting_tab_row');
            $objTemplateLocal->parse('core_setting_tab_row');

            // parse submit button (or hide if $readOnly is set)
            if ($objTemplateLocal->blockExists('core_setting_submit')) {
                if ($readOnly) {
                    $objTemplateLocal->hideBlock('core_setting_submit');
                } else {
                    $objTemplateLocal->touchBlock('core_setting_submit');
                    $objTemplateLocal->parse('core_setting_submit');
                }
            }
            $objTemplateLocal->touchBlock('core_setting_tab_div');
            $objTemplateLocal->parse('core_setting_tab_div');
        }

// NOK
//die(nl2br(contrexx_raw2xhtml(var_export($objTemplateLocal, true))));

        return true;
    }
    /**
     * Display a section of settings present in the $arrSettings class array
     *
     * See the description of {@see show()} for details.
     * @param   \Cx\Core\Html\Sigma $objTemplateLocal   The Template object,
     *                                                  by reference
     * @param   string              $section      The optional section header
     *                                            text to add
     * @param   string              $prefix       The optional prefix for
     *                                            language variables.
     *                                            Defaults to 'TXT_'
     * @return  boolean                           True on success, false otherwise
     */
    static function show_section(&$objTemplateLocal, $section='', $prefix='TXT_', $readOnly = false)
    {
        global $_ARRAYLANG, $_CORELANG; $engineType=self::getEngineType();
        $arrSettings=$engineType::getArraySetting();
        self::verify_template($objTemplateLocal);
        // This is set to multipart if necessary
        $enctype = '';
        $i = 0;
        if ($objTemplateLocal->blockExists('core_setting_row'))
            $objTemplateLocal->setCurrentBlock('core_setting_row');
        foreach ($arrSettings as $name => $arrSetting) {
            // Determine HTML element for type and apply values and selected
            $element = '';
            $value = $arrSetting['value'];
            $values = self::splitValues($arrSetting['values']);
            $type = $arrSetting['type'];
            // Not implemented yet:
            // Warn if some mandatory value is empty
            if (empty($value) && preg_match('/_mandatory$/', $type)) {
                \Message::warning(
                    sprintf($_CORELANG['TXT_CORE_SETTING_WARNING_EMPTY'],
                        $_ARRAYLANG[$prefix.strtoupper($name)],
                        $name));
            }
            // Warn if some language variable is not defined
            if (empty($_ARRAYLANG[$prefix.strtoupper($name)])) {
                \Message::warning(
                    sprintf($_CORELANG['TXT_CORE_SETTING_WARNING_MISSING_LANGUAGE'],
                        $prefix.strtoupper($name),
                        $name));
            }

//DBG::log("Value: $value -> align $value_align");
            switch ($type) {
              // Dropdown menu
              case self::TYPE_DROPDOWN:
                if (preg_match('/^\{src:([a-z0-9_\\\:]+)\(\)\}$/i', $arrSetting['values'], $matches)) {
                    $arrValues = self::splitValues(call_user_func($matches[1]));
                } else {
                    $arrValues = self::splitValues($arrSetting['values']);
                }
//DBG::log("Values: ".var_export($arrValues, true));
                $element = \Html::getSelect(
                    $name, $arrValues, $value,
                    '', '',
                    'style="width: '.self::DEFAULT_INPUT_WIDTH.'px;'.
                    (   isset ($arrValues[$value])
                     && is_numeric($arrValues[$value])
                        ? 'text-align: right;' : '').
                    '"'.
                    ($readOnly ? \Html::ATTRIBUTE_DISABLED : ''));
                break;
              case self::TYPE_DROPDOWN_USER_CUSTOM_ATTRIBUTE:
                $element = \Html::getSelect(
                    $name,
                    User_Profile_Attribute::getCustomAttributeNameArray(),
                    $arrSetting['value'], '', '',
                    'style="width: '.self::DEFAULT_INPUT_WIDTH.'px;"'.($readOnly ? \Html::ATTRIBUTE_DISABLED : '')
                );
                break;
              case self::TYPE_DROPDOWN_USERGROUP:
                $element = \Html::getSelect(
                    $name,
                    UserGroup::getNameArray(),
                    $arrSetting['value'],
                    '', '', 'style="width: '.self::DEFAULT_INPUT_WIDTH.'px;"'.($readOnly ? \Html::ATTRIBUTE_DISABLED : '')
                );
                break;
              case self::TYPE_WYSIWYG:
                // These must be treated differently, as wysiwyg editors
                // claim the full width
                if ($readOnly) {
// TODO: this might be dangerous! should be rewritten probably
                    $element = $value;
                } else {
                    $element = new \Cx\Core\Wysiwyg\Wysiwyg($name, $value);
                }
                $objTemplateLocal->setVariable(array(
                    'CORE_SETTING_ROW' => $_ARRAYLANG[$prefix.strtoupper($name)],
                    'CORE_SETTING_ROWCLASS1' => (++$i % 2 ? '1' : '2'),
                ));
                $objTemplateLocal->parseCurrentBlock();
                $objTemplateLocal->setVariable(array(
                    'CORE_SETTING_ROW' => $element.'<br /><br />',
                    'CORE_SETTING_ROWCLASS1' => (++$i % 2 ? '1' : '2'),
                ));
                $objTemplateLocal->parseCurrentBlock();
                // Skip the part below, all is done already
                continue 2;

              case self::TYPE_FILEUPLOAD:
//echo("\Cx\Core\Setting\Controller\Setting::show_section(): Setting up upload for $name, $value<br />");
                $element =
                    \Html::getInputFileupload(
                        // Set the ID only if the $value is non-empty.
                        // This toggles the file name and delete icon on or off
                        $name, ($value ? $name : false),
                        Filetype::MAXIMUM_UPLOAD_FILE_SIZE,
                        // "values" defines the MIME types allowed
                        $arrSetting['values'],
                        'style="width: '.self::DEFAULT_INPUT_WIDTH.'px;"'.($readOnly ? \Html::ATTRIBUTE_DISABLED : ''), true,
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
                        'document.formSettings_'.$engineType::$tab_index.'.submit();'.
                      '}\'';
//DBG::log("\Cx\Core\Setting\Controller\Setting::show_section(): Event: $event");
                $element =
                    \Html::getInputButton(
                        // The button itself gets a dummy name attribute value
                        '__'.$name,
                        $_ARRAYLANG[strtoupper($prefix.$name).'_LABEL'],
                        'button', false,
                        $event.($readOnly ? \Html::ATTRIBUTE_DISABLED : '')
                    ).
                    // The posted value is set to 1 when confirmed,
                    // before the form is posted
                    \Html::getHidden($name, 0, '');
//DBG::log("\Cx\Core\Setting\Controller\Setting::show_section(): Element: $element");
                break;

              case self::TYPE_TEXTAREA:
                $element =
                    \Html::getTextarea($name, $value, 80, 8, ($readOnly ? \Html::ATTRIBUTE_DISABLED : ''));
//                        'style="width: '.self::DEFAULT_INPUT_WIDTH.'px;'.$value_align.'"');
                break;

              case self::TYPE_CHECKBOX:
                $arrValues = self::splitValues($arrSetting['values']);
                $value_true = current($arrValues);
                $element =
                    \Html::getCheckbox($name, $value_true, false,
                        in_array($value, $arrValues), '', ($readOnly ? \Html::ATTRIBUTE_DISABLED : ''));
                break;
              case self::TYPE_CHECKBOXGROUP:
                $checked = self::splitValues($value);
                $element =
                    \Html::getCheckboxGroup($name, $values, $values, $checked,
                        '', '', '<br />', ($readOnly ? \Html::ATTRIBUTE_DISABLED : ''), '');
                break;
// 20120508 UNTESTED!
              case self::TYPE_RADIO:
                $checked = $value;
                $element =
                    \Html::getRadioGroup($name, $values, $checked, '', ($readOnly ? \Html::ATTRIBUTE_DISABLED : ''));
                break;

// More...
//              case self::TYPE_:
//                break;
                case self::TYPE_PASSWORD:
                $element =
                    \Html::getInputPassword($name, $value, 'style="width: '.self::DEFAULT_INPUT_WIDTH.'px;"'.($readOnly ? \Html::ATTRIBUTE_DISABLED : ''));
                break;
                
              // Default to text input fields
              case self::TYPE_TEXT:
              case self::TYPE_EMAIL:
              default:
                $element =
                    \Html::getInputText(
                        $name, $value, false,
                        'style="width: '.self::DEFAULT_INPUT_WIDTH.'px;'.
                        (is_numeric($value) ? 'text-align: right;' : '').
                        '"'.
                        ($readOnly ? \Html::ATTRIBUTE_DISABLED : ''));
            }
            
            //add Tooltip
            $toolTips='';
            $toolTipsHelp ='';
            if (isset($_ARRAYLANG[$prefix.strtoupper($name).'_TOOLTIP'])) {
                // generate tooltip for configuration option
                $toolTips='  <span class="icon-info tooltip-trigger"></span><span class="tooltip-message">'.$_ARRAYLANG[$prefix.strtoupper($name).'_TOOLTIP'].'</span>';
            }
            if (isset($_ARRAYLANG[$prefix.strtoupper($name).'_TOOLTIP_HELP'])) {
                // generate tooltip for configuration option
                $toolTipsHelp ='  <span class="icon-info tooltip-trigger"></span><span class="tooltip-message">'.$_ARRAYLANG[$prefix.strtoupper($name).'_TOOLTIP_HELP'].'</span>';
            }
            $objTemplateLocal->setVariable(array(
                'CORE_SETTING_NAME' => (isset($_ARRAYLANG[$prefix.strtoupper($name)]) ? $_ARRAYLANG[$prefix.strtoupper($name)] : $name).$toolTips,
                'CORE_SETTING_VALUE' => $element.$toolTipsHelp,
                'CORE_SETTING_ROWCLASS2' => (++$i % 2 ? '1' : '2'),
            ));
            $objTemplateLocal->parseCurrentBlock();
//echo("\Cx\Core\Setting\Controller\Setting::show(objTemplateLocal, $prefix): shown $name => $value<br />");
        }

        // Set form encoding to multipart if necessary
        if (!empty($enctype))
            $objTemplateLocal->setVariable('CORE_SETTING_ENCTYPE', $enctype);

        if (   !empty($section)
            && $objTemplateLocal->blockExists('core_setting_section')) {
//echo("\Cx\Core\Setting\Controller\Setting::show(objTemplateLocal, $header, $prefix): creating section $header<br />");
            $objTemplateLocal->setVariable(array(
                'CORE_SETTING_SECTION' => $section,
            ));
            //$objTemplateLocal->parse('core_setting_section');
        }
        return true;
    }
    /**
     * Adds an external settings view to the current template
     *
     * The content must contain the full view, including the surrounding form
     * tags and submit button.
     * Note that these are always appended on the right end of the tab list.
     * @param   \Cx\Core\Html\Sigma $objTemplateLocal   Template object
     * @param   string              $tab_name           The tab name to add
     * @param   string              $content            The external content
     * @return  boolean                                 True on success
     */
    static function show_external( &$objTemplateLocal, $tab_name, $content)
    {
        $engineType=self::getEngineType();
        
        if (empty($objTemplateLocal)|| !$objTemplateLocal->blockExists('core_setting_row')) 
        {
            $objTemplateLocal = new \Cx\Core\Html\Sigma(\Env::get('cx')->getCodeBaseDocumentRootPath() . '/core/Setting/View/Template/Generic');
            if (!$objTemplateLocal->loadTemplateFile('Form.html'))
                die("Failed to load template Form.html");
        }

        $active_tab = (isset($_REQUEST['active_tab']) ? $_REQUEST['active_tab'] : 1);
        // The tabindex must be set in the form name in any case
        $objTemplateLocal->setGlobalVariable(array(
                                                    'CORE_SETTING_TAB_INDEX' => $engineType::$tab_index,
                                                    'CORE_SETTING_EXTERNAL' => $content,
                                                ));
        // Set up the tab, if any
        if (!empty($tab_name)) 
        {
            $objTemplateLocal->setGlobalVariable(array(
                                                        'CORE_SETTING_TAB_NAME' => $tab_name,
                                        //                'CORE_SETTING_TAB_INDEX' => self::$tab_index,
                                                        'CORE_SETTING_TAB_CLASS' => ($engineType::$tab_index == $active_tab ? 'active' : ''),
                                                        'CORE_SETTING_TAB_DISPLAY' => ($engineType::$tab_index++ == $active_tab ? 'block' : 'none'),
                                                ));
            $objTemplateLocal->touchBlock('core_setting_tab_row');
            $objTemplateLocal->parse('core_setting_tab_row');
            $objTemplateLocal->touchBlock('core_setting_tab_div_external');
            $objTemplateLocal->parse('core_setting_tab_div_external');
        }
        return true;
    }
    /**
     * Ensures that a valid template is available
     *
     * Die()s if the template given is invalid, and Form.html cannot be
     * loaded to replace it.
     * @param   \Cx\Core\Html\Sigma $objTemplateLocal   The template,
     *                                                  by reference
     */
    static function verify_template(&$objTemplateLocal)
    {
        //"instanceof" considers subclasses of Sigma to be a Sigma, too!
        if (!($objTemplateLocal instanceof \Cx\Core\Html\Sigma)) {
            $objTemplateLocal = new \Cx\Core\Html\Sigma(\Env::get('cx')->getCodeBaseDocumentRootPath() . '/core/Setting/View/Template/Generic');
        }
        if (!$objTemplateLocal->blockExists('core_setting_row')) {
            $objTemplateLocal->setRoot(\Env::get('cx')->getCodeBaseDocumentRootPath() . '/core/Setting/View/Template/Generic');
        //$objTemplateLocal->setCacheRoot('.');
            if (!$objTemplateLocal->loadTemplateFile('Form.html')){
                die("Failed to load template Form.html");
            }
            //die(nl2br(contrexx_raw2xhtml(var_export($objTemplateLocal, true))));
        }
    }
    /**
     * Update and store all settings found in the $_POST array
     *
     * Note that you *MUST* call {@see init()} beforehand, or your settings
     * will be unknown and thus not be stored.
     * Sets up an error message on failure.
     * @return  boolean                 True on success, null on noop,
     *                                  or false on failure
     */
    static function storeFromPost()
    {
        global $_CORELANG;

        //echo("self::storeFromPost(): POST:<br />".nl2br(htmlentities(var_export($_POST, true)))."<hr />");
        //echo("self::storeFromPost(): FILES:<br />".nl2br(htmlentities(var_export($_FILES, true)))."<hr />");
        
        // There may be several tabs for different groups being edited, so
        // load the full set of settings for the module.
        // Note that this is why setting names should be unique.
        // TODO: You *MUST* call this yourself *before* in order to
        // properly initialize the section!
        // self::init();
        
        $engineType=self::getEngineType();
        $arrSettings=$engineType::getArraySetting();
        unset($_POST['bsubmit']);
        $result = true;
        // Compare POST with current settings and only store what was changed.
        foreach (array_keys($arrSettings) as $name) {
            if (isset ($_POST[$name])) {
                $value=contrexx_input2raw($_POST[$name]);
                //if (preg_match('/^'.preg_quote(CSRF::key(), '/').'$/', $name))
                //continue;
                switch ($arrSettings[$name]['type']) {
                  case self::TYPE_FILEUPLOAD:
                    // An empty folder path has been posted, indicating that the
                    // current file should be removed
                    if (empty($value)) {
                        //echo("Empty value, deleting file...<br />");
                        if ($arrSettings[$name]['value']) {
                            if (\File::delete_file($arrSettings[$name]['value'])) {
                        //echo("File deleted<br />");
                                $value = '';
                            } else {
                        //echo("Failed to delete file<br />");
                                \Message::error(\File::getErrorString());
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
                        $result_upload = \File::upload_file_http(
                            $name, $target_path,
                            \Filetype::MAXIMUM_UPLOAD_FILE_SIZE,
                            // The allowed file types
                            $arrSettings[$name]['values']
                        );
                        // If no file has been uploaded at all, ignore the no-change
                        // TODO: Noop is not implemented in File::upload_file_http()
                        // if ($result_upload === '') continue;
                        if ($result_upload === true) {
                            $value = $target_path;
                        } else {
                        //echo("self::storeFromPost(): Error uploading file for setting $name to $target_path<br />");
                        // TODO: Add error message
                            \Message::error(\File::getErrorString());
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
                        // 20120508
                  case self::TYPE_RADIO:
                      break;
                  default:
                        // Regular value of any other type
                    break;
                }
                self::set($name, $value);
            }    
        }
        //echo("self::storeFromPost(): So far, the result is ".($result ? 'okay' : 'no good')."<br />");
        $result_update = self::updateAll();
        if ($result_update === false) {
            \Message::error($_CORELANG['TXT_CORE_SETTING_ERROR_STORING']);
        } elseif ($result_update === true) {
            \Message::ok($_CORELANG['TXT_CORE_SETTING_STORED_SUCCESSFULLY']);
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
     * Deletes all entries for the current section    
     *
     * This is for testing purposes only.  Use with care!
     * The static $section determines the module affected.
     * @return    boolean               True on success, false otherwise
     */
    static function deleteModule()
    {
        $engineType=self::getEngineType();
        return $engineType::deleteModule();  
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
        $engineType=self::getEngineType();
        return $engineType::errorHandler();  
    }
    /**
     * Returns the settings from the old settings table for the given module ID,    
     * if available
     *
     * If the module ID is missing or invalid, or if the settings cannot be
     * read for some other reason, returns null.
     * Don't drop the table after migrating your settings, other modules
     * might still need it!  Instead, try this method only after you failed
     * to get your settings from Setting.
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
    /**
     * Get engineType    
     *
     * @return string $engineType
     */ 
    static function getEngineType(){
        return self::$engineType;
    }
    /**
     * Set engineType    
     *
     * @param string $engineType
     */
    static function setEngineType($engineType){
        self::$engineType=$engineType;
    }   
}
