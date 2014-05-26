<?php
/**
 * Specific Setting for this Component. Use this to interact with the classes "Db" and "FileSystem"
 *
 * @copyright   Comvation AG
 * @author      Manish Thakur <manishthakur@cdnsol.com>
 * @package     contrexx
 * @subpackage  Setting
 */

namespace Cx\Core\Setting\Controller;

class SettingException extends \Exception {}

/**
 * Specific Setting for this Component. Use this to easily using "Db" and "FileSystem"
 *
 * @copyright   Comvation AG
 * @author      Manish Thakur <manishthakur@cdnsol.com>
 * @package     contrexx
 * @subpackage  Setting
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
//    const TYPE_SUBMIT = 'submit';

    /**
     * Default width for input fields
     *
     * Note that textareas often use twice that value.
     */
    const DEFAULT_INPUT_WIDTH = 300;

    
   
    private static $engineType = null;
    
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
    static function init($section, $group=null,$engine = 'FileSystem')
    {
        
        if($engine=="Database"){
            
            \Cx\Core\Setting\Model\Entity\Db::init($section, $group);
            self::$engineType='\Cx\Core\Setting\Model\Entity\Db';
             
        }elseif($engine=="FileSystem"){
            
            \Cx\Core\Setting\Model\Entity\FileSystem::init($section, $group);
            self::$engineType='\Cx\Core\Setting\Model\Entity\FileSystem';
        }else{
            throw new SettingException('Invalid arguments supplied');
            return false;
        }
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
        $engineType=self::$engineType;
        $engineType::errorHandler();  
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
     * @param   \Cx\Core\Html\Sigma $objTemplateLocal   Template object
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
    static function show(&$objTemplateLocal, $uriBase, $section='', $tab_name='', $prefix='TXT_') 
    {
        global $_CORELANG;
        
        $engineType=self::$engineType;

//$objTemplate->setCurrentBlock();
//echo(nl2br(htmlentities(var_export($objTemplate->getPlaceholderList()))));

        $engineType::verify_template($objTemplateLocal);
// TODO: Test if everything works without this line
//        Html::replaceUriParameter($uriBase, 'act=settings');
        \Html::replaceUriParameter($uriBase, 'active_tab='.$engineType::$tab_index);
        // Default headings and elements
        $objTemplateLocal->setGlobalVariable(
            $_CORELANG
          + array(
            'URI_BASE' => $uriBase,
        ));

        if ($objTemplateLocal->blockExists('core_settingdb_row'))
            $objTemplateLocal->setCurrentBlock('core_settingdb_row');
//echo("SettingDb::show(objTemplateLocal, $prefix): got Array: ".var_export(self::$arrSettings, true)."<br />");
        if (!is_array($engineType::$arrSettings)) {
//die("No Settings array");
            return \Message::error($_CORELANG['TXT_CORE_SETTINGDB_ERROR_RETRIEVING']);
        }
        if (empty($engineType::$arrSettings)) {
//die("No Settings found");
            \Message::warning(
                sprintf(
                    $_CORELANG['TXT_CORE_SETTINGDB_WARNING_NONE_FOUND_FOR_TAB_AND_SECTION'],
                    $tab_name, $section));
            return false;
        }
        self::show_section($objTemplateLocal, $section, $prefix);
        // The tabindex must be set in the form name in any case
        $objTemplateLocal->setGlobalVariable(
            'CORE_SETTINGDB_TAB_INDEX', $engineType::$tab_index);
        // Set up tab, if any
        if (!empty($tab_name)) {
            $active_tab = (isset($_REQUEST['active_tab']) ? $_REQUEST['active_tab'] : 1);
            $objTemplateLocal->setGlobalVariable(array(
                'CORE_SETTINGDB_TAB_NAME' => $tab_name,
//                'CORE_SETTINGDB_TAB_INDEX' => self::$tab_index,
                'CORE_SETTINGDB_TAB_CLASS' => ($engineType::$tab_index == $active_tab ? 'active' : ''),
                'CORE_SETTINGDB_TAB_DISPLAY' => ($engineType::$tab_index++ == $active_tab ? 'block' : 'none'),
            ));
            $objTemplateLocal->touchBlock('core_settingdb_header');
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
     * @param   \Cx\Core\Html\Sigma $objTemplateLocal   The Template object,
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
        global $_ARRAYLANG, $_CORELANG; $engineType=self::$engineType;

        $engineType::verify_template($objTemplateLocal);
        // This is set to multipart if necessary
        $enctype = '';
        $i = 0;
        if ($objTemplateLocal->blockExists('core_settingdb_row'))
            $objTemplateLocal->setCurrentBlock('core_settingdb_row');
        foreach ($engineType::$arrSettings as $name => $arrSetting) {
            // Determine HTML element for type and apply values and selected
            $element = '';
            $value = $arrSetting['value'];
            $values = $engineType::splitValues($arrSetting['values']);
            $type = $arrSetting['type'];
            // Not implemented yet:
            // Warn if some mandatory value is empty
            if (empty($value) && preg_match('/_mandatory$/', $type)) {
                \Message::warning(
                    sprintf($_CORELANG['TXT_CORE_SETTINGDB_WARNING_EMPTY'],
                        $_ARRAYLANG[$prefix.strtoupper($name)],
                        $name));
            }
            // Warn if some language variable is not defined
            if (empty($_ARRAYLANG[$prefix.strtoupper($name)])) {
                \Message::warning(
                    sprintf($_CORELANG['TXT_CORE_SETTINGDB_WARNING_MISSING_LANGUAGE'],
                        $prefix.strtoupper($name),
                        $name));
            }

//DBG::log("Value: $value -> align $value_align");
            switch ($type) {
              // Dropdown menu
              case self::TYPE_DROPDOWN:
                $arrValues = $engineType::splitValues($arrSetting['values']);
//DBG::log("Values: ".var_export($arrValues, true));
                $element = \Html::getSelect(
                    $name, $arrValues, $value,
                    '', '',
                    'style="width: '.self::DEFAULT_INPUT_WIDTH.'px;'.
                    (   isset ($arrValues[$value])
                     && is_numeric($arrValues[$value])
                        ? 'text-align: right;' : '').
                    '"');
                break;
              case self::TYPE_DROPDOWN_USER_CUSTOM_ATTRIBUTE:
                $element = \Html::getSelect(
                    $name,
                    User_Profile_Attribute::getCustomAttributeNameArray(),
                    $arrSetting['value'], '', '',
                    'style="width: '.self::DEFAULT_INPUT_WIDTH.'px;"'
                );
                break;
              case self::TYPE_DROPDOWN_USERGROUP:
                $element = \Html::getSelect(
                    $name,
                    UserGroup::getNameArray(),
                    $arrSetting['value'],
                    '', '', 'style="width: '.self::DEFAULT_INPUT_WIDTH.'px;"'
                );
                break;
              case self::TYPE_WYSIWYG:
                // These must be treated differently, as wysiwyg editors
                // claim the full width
                $element = new \Cx\Core\Wysiwyg\Wysiwyg($name, $value);
                $objTemplateLocal->setVariable(array(
                    'CORE_SETTINGDB_ROW' => $_ARRAYLANG[$prefix.strtoupper($name)],
                    'CORE_SETTINGDB_ROWCLASS1' => (++$i % 2 ? '1' : '2'),
                ));
                $objTemplateLocal->parseCurrentBlock();
                $objTemplateLocal->setVariable(array(
                    'CORE_SETTINGDB_ROW' => $element.'<br /><br />',
                    'CORE_SETTINGDB_ROWCLASS1' => (++$i % 2 ? '1' : '2'),
                ));
                $objTemplateLocal->parseCurrentBlock();
                // Skip the part below, all is done already
                continue 2;

              case self::TYPE_FILEUPLOAD:
//echo("SettingDb::show_section(): Setting up upload for $name, $value<br />");
                $element =
                    \Html::getInputFileupload(
                        // Set the ID only if the $value is non-empty.
                        // This toggles the file name and delete icon on or off
                        $name, ($value ? $name : false),
                        Filetype::MAXIMUM_UPLOAD_FILE_SIZE,
                        // "values" defines the MIME types allowed
                        $arrSetting['values'],
                        'style="width: '.self::DEFAULT_INPUT_WIDTH.'px;"', true,
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
//DBG::log("SettingDb::show_section(): Event: $event");
                $element =
                    \Html::getInputButton(
                        // The button itself gets a dummy name attribute value
                        '__'.$name,
                        $_ARRAYLANG[strtoupper($prefix.$name).'_LABEL'],
                        'button', false,
                        $event
                    ).
                    // The posted value is set to 1 when confirmed,
                    // before the form is posted
                    \Html::getHidden($name, 0, '');
//DBG::log("SettingDb::show_section(): Element: $element");
                break;

              case self::TYPE_TEXTAREA:
                $element =
                    \Html::getTextarea($name, $value, 80, 8, '');
//                        'style="width: '.self::DEFAULT_INPUT_WIDTH.'px;'.$value_align.'"');
                break;

              case self::TYPE_CHECKBOX:
                $arrValues = $engineType::splitValues($arrSetting['values']);
                $value_true = current($arrValues);
                $element =
                    \Html::getCheckbox($name, $value_true, false,
                        in_array($value, $arrValues));
                break;
              case self::TYPE_CHECKBOXGROUP:
                $checked = $engineType::splitValues($value);
                $element =
                    \Html::getCheckboxGroup($name, $values, $values, $checked,
                        '', '', '<br />', '', '');
                break;
// 20120508 UNTESTED!
              case self::TYPE_RADIO:
                $checked = $engineType::splitValues($value);
                $element =
                    \Html::getRadioGroup($name, $values, $values);
                break;

// More...
//              case self::TYPE_:
//                break;

              // Default to text input fields
              case self::TYPE_TEXT:
              case self::TYPE_EMAIL:
              default:
                $element =
                    \Html::getInputText(
                        $name, $value, false,
                        'style="width: '.self::DEFAULT_INPUT_WIDTH.'px;'.
                        (is_numeric($value) ? 'text-align: right;' : '').
                        '"');
            }
            
            //add Tooltip
            $toolTips='';
            if (isset($_ARRAYLANG[$prefix.strtoupper($name).'_TOOLTIP'])) {
                // generate tooltip for configuration option
                $toolTips='  <span class="icon-info tooltip-trigger"></span><span class="tooltip-message">'.$_ARRAYLANG[$prefix.strtoupper($name).'_TOOLTIP'].'</span>';
            }
            $objTemplateLocal->setVariable(array(
                'CORE_SETTINGDB_NAME' => $_ARRAYLANG[$prefix.strtoupper($name)].$toolTips,
                'CORE_SETTINGDB_VALUE' => $element,
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
//echo("SettingDb::show(objTemplateLocal, $header, $prefix): creating section $header<br />");
            $objTemplateLocal->setVariable(array(
                'CORE_SETTINGDB_SECTION' => $section,
            ));
            //$objTemplateLocal->parse('core_settingdb_section');
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
    static function show_external(
        &$objTemplateLocal, $tab_name, $content
    ) {
        $engineType=self::$engineType;
        
        if (   empty($objTemplateLocal)
            || !$objTemplateLocal->blockExists('core_settingdb_row')) {
            $objTemplateLocal = new \Cx\Core\Html\Sigma(ASCMS_DOCUMENT_ROOT.'/core/Setting/View/Template/Generic');
            if (!$objTemplateLocal->loadTemplateFile('Form.html'))
                die("Failed to load template settingDb.html");
        }

        $active_tab = (isset($_REQUEST['active_tab']) ? $_REQUEST['active_tab'] : 1);
        // The tabindex must be set in the form name in any case
        $objTemplateLocal->setGlobalVariable(array(
            'CORE_SETTINGDB_TAB_INDEX' => $engineType::$tab_index,
            'CORE_SETTINGDB_EXTERNAL' => $content,
        ));
        // Set up the tab, if any
        if (!empty($tab_name)) {
            $objTemplateLocal->setGlobalVariable(array(
                'CORE_SETTINGDB_TAB_NAME' => $tab_name,
//                'CORE_SETTINGDB_TAB_INDEX' => self::$tab_index,
                'CORE_SETTINGDB_TAB_CLASS' => ($engineType::$tab_index == $active_tab ? 'active' : ''),
                'CORE_SETTINGDB_TAB_DISPLAY' => ($engineType::$tab_index++ == $active_tab ? 'block' : 'none'),
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
     * Note that you *MUST* call {@see init()} beforehand, or your settings
     * will be unknown and thus not be stored.
     * Sets up an error message on failure.
     * @return  boolean                 True on success, null on noop,
     *                                  or false on failure
     */
    static function storeFromPost()
    {
        $engineType=self::$engineType;
        $engineType::storeFromPost();  
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
        $engineType=self::$engineType;
        return $engineType::getValue($name);  
    }
    
     
    static function add( $name, $value, $ord=false, $type='text', $values='', $group=null)
    {
        $engineType=self::$engineType;
        return $engineType::add( $name, $value, $ord=false, $type='text', $values='', $group=null);  
    }
    
    static function deleteModule()
    {
        $engineType=self::$engineType;
        return $engineType::deleteModule();  
    } 
    
    static function set($name, $value)
    {
        $engineType=self::$engineType;
        return $engineType::set($name, $value);  
    }
    
    static function updateAll()
    {
        $engineType=self::$engineType;
        return $engineType::updateAll();  
    }
    
    static function update($name)
    {
        $engineType=self::$engineType;
        return $engineType::update($name);  
    }
}
