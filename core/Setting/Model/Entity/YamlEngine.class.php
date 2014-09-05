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
class YamlEngine extends Engine{
    /**
     * Path to the configuration file used as storage location
     * @var string
     */
    static $filename = null;
    static $yamlSettingRepo = null;

    /**
     * Initialize the settings entries from the file with key/value pairs
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
     * @param   string    $configRepository     An optional path
     *                                to the storage location of config files (/config) which shall be used for the engine 'File System'.
     * @return  boolean               True on success, false otherwise
     */
    static function init($section, $group=null, $configRepository = null) {
        if (!$configRepository) {
            $configRepository = \Env::get('cx')->getWebsiteConfigPath();
        }

        self::flush();
        self::$section = $section;
        self::$group = $group;
        self::$filename =  $configRepository . '/'.$section.'.yml';
        self::$yamlSettingRepo = new \Cx\Core\Setting\Model\Repository\YamlSettingRepository(self::$filename);
        self::$arrSettings = self::load();
    }

    static function load() {
        if (!empty(self::$yamlSettingRepo)) {
            $yamlSettings = self::$yamlSettingRepo->findAll();
            $yamlSettingArray = array();
            if (isset($yamlSettings)) {
                foreach ($yamlSettings As $yamlSetting) {
                    $yamlSettingArray[$yamlSetting->getName()] = array(
                        'name'    => $yamlSetting->getName(),
                        'section' => $yamlSetting->getSection(),
                        'group'   => $yamlSetting->getGroup(),
                        'value'   => $yamlSetting->getValue(),
                        'type'    => $yamlSetting->getType(),
                        'values'  => $yamlSetting->getValues(),
                        'ord'     => $yamlSetting->getOrd()
                    );
                }
                return $yamlSettingArray;
            }
        }
        return false;
    }

    /**
     * Returns the settings array for the given section and group
     * @return  array
     */
    static function getArraySetting()
    { 
        $settingArray=array();
        if (!empty(self::$group)) {
            foreach (self::$arrSettings as $value) {
                if ($value['group']==self::$group) {
                    $settingArray[$value['name']]= $value;
                }
            }
        } else {
            $settingArray=self::$arrSettings;
        }
        return $settingArray;
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
        //global $_CORELANG;
        if (!self::$changed) {
        // TODO: These messages are inapropriate when settings are stored by another piece of code, too.
        // Find a way around this.
        // Message::information($_CORELANG['TXT_CORE_SETTING_INFORMATION_NO_CHANGE']);
            return null;
        }
        // TODO: Add error messages for section errors
        if (empty(self::$section)) {
            \DBG::log("\Cx\Core\Setting\Model\Entity\YamlEngine::updateAll(): ERROR: Empty section!");
            return false;
        }
        // TODO: Add error messages for setting array errors
        if (empty(self::$arrSettings)) {
            \DBG::log("\Cx\Core\Setting\Model\Entity\YamlEngine::updateAll(): ERROR: Empty section!");
            return false;
        }
        $success = true;
        try {
            foreach (self::$arrSettings As $yamlSettingName => $yamlSettingValue) {
                $objYamlSetting = self::$yamlSettingRepo->findOneBy(array('name' => $yamlSettingName, 'section' => self::$section));
                $objYamlSetting->setValue($yamlSettingValue['value']);
            
            }
            self::$yamlSettingRepo->flush();
        } catch (\Cx\Core\Setting\Model\Entity\YamlSettingException $e) {
            \DBG::msg($e->getMessage());
            return false;
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
     * Updates the value for the given name in the settings
     *
     * The class *MUST* have been initialized before calling this
     * method using {@see init()}, and the new value been {@see set()}.
     * Sets $changed to true and returns true if the value has been
     * updated successfully.
     * Note that this method does not work for adding new settings.
     * See {@see add()} on how to do this.
     * Also note that the loaded setting is not updated,
     * @param   string    $name   The settings name
     * @return  boolean           True on successful update or if
     *                            unchanged, false on failure
     * @static
     */
    static function update($name)
    {
        // TODO: Add error messages for individual errors
        if (empty(self::$section)) {
            \DBG::log("\Cx\Core\Setting\Model\Entity\YamlEngine::update(): ERROR: Empty section!");
            return false;
        }
        // Fail if the name is invalid
        // or the setting does not exist
        if (empty($name)) {
            \DBG::log("\Cx\Core\Setting\Model\Entity\YamlEngine::update(): ERROR: Empty name!");
            return false;
        }
        if (!isset(self::$arrSettings[$name])) {
            \DBG::log("\Cx\Core\Setting\Model\Entity\YamlEngine::update(): ERROR: Unknown setting name '$name'!");
            return false;
        }
        if (!empty(self::$arrSettings)) {
            try {
                $objYamlSetting = self::$yamlSettingRepo->findOneBy(array('name' => $name, 'section' => self::$section));
                $objYamlSetting->setValue(self::$arrSettings[$name]['value']);
            
                self::$yamlSettingRepo->flush();
            } catch (\Cx\Core\Setting\Model\Entity\YamlSettingException $e) {
                \DBG::msg($e->getMessage());
                return false;
            }
            return true;
        } else {
            return false;
        }
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
        if (!isset(self::$section)) {
            // TODO: Error message
            \DBG::log("\Cx\Core\Setting\Model\Entity\YamlEngine::add(): ERROR: Empty section!");
            return false;
        }
        // Fail if the name is invalid
        if (empty($name)) {
            \DBG::log("\Cx\Core\Setting\Model\Entity\YamlEngine::add(): ERROR: Empty name!");
            return false;
        }
        // This can only be done with a non-empty group!
        // Use the current group, if present, otherwise fail
        if (!$group) {
            if (!self::$group) {
                \DBG::log("\Cx\Core\Setting\Model\Entity\YamlEngine::add(): ERROR: Empty group!");
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
            \DBG::log("\Cx\Core\Setting\Model\Entity\YamlEngine::add(): ERROR: Setting '$name' already exists and is non-empty ($old_value)");
            return false;
        }
        
        try {
            $objYamlSetting = new \Cx\Core\Setting\Model\Entity\YamlSetting($name);
            $objYamlSetting->setSection(self::$section);
            $objYamlSetting->setGroup($group);
            $objYamlSetting->setValue($value);
            $objYamlSetting->setValue($value);
            $objYamlSetting->setType($type);
            $objYamlSetting->setValues($values);
            $objYamlSetting->setOrd($ord);
        
            self::$yamlSettingRepo->add($objYamlSetting);
            self::$yamlSettingRepo->flush();
            } catch (\Cx\Core\Setting\Model\Entity\YamlSettingException $e) {
            \DBG::msg($e->getMessage());
            return false;
        }
        
        self::$arrSettings = self::load();
        return true;
    }

    /**
     * Delete one or more records from the File   
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
        // Fail if both parameter values are empty
        if (empty($name) && empty($group) && empty(self::$section)) return false;
         
        try {
            if (!empty($name) && !empty($group)) {
                $yamlSettings = self::$yamlSettingRepo->findBy(array('name' => $name, 'group' => $group, 'section' => self::$section));
            } else if (!empty ($name)) {
                $yamlSettings = self::$yamlSettingRepo->findBy(array('name' => $name, 'section' => self::$section));
            } else if (!empty ($group)) {
                $yamlSettings = self::$yamlSettingRepo->findBy(array('group' => $group, 'section' => self::$section));
            }
        
            foreach ($yamlSettings As $yamlSetting) {
                \Env::get('em')->remove($yamlSetting);
            }
            \Env::get('em')->flush();
        } catch (\Cx\Core\Setting\Model\Entity\YamlSettingException $e) {
            \DBG::msg($e->getMessage());
            return false;
        }
        
        self::$arrSettings = self::load();
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
        if (empty(self::$section))return false;
        
        try {
            $yamlSettings = self::$yamlSettingRepo->findBy(array('section' => self::$section));
            foreach ($yamlSettings As $yamlSetting) {
                \Env::get('em')->remove($yamlSetting);
            }
            \Env::get('em')->flush();
            return true;
        } catch (\Cx\Core\Setting\Model\Entity\YamlSettingException $e) {
            \DBG::msg($e->getMessage());
            return false;
        }
    }

    /**
     * Should be called whenever there's a problem with the settings
     *
     * Tries to fix or recreate the settings.
     * @return  boolean             False, always.
     * @static
     */
    static function errorHandler()
    {
        try {
            $file = new \Cx\Lib\FileSystem\File(\Env::get('cx')->getWebsiteConfigPath() . '/'.self::$section.'.yml');
            $file->touch();
            return false;
        } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
            \DBG::msg($e->getMessage());
        }
       
    } 
}
