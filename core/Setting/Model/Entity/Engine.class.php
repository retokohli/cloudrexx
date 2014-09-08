<?php
/**
 * Specific Setting for this Component. Use this abstract class extends with the Db.class.php or FileSystem.class.php
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
abstract class Engine implements EngineInterface {
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
     * @access  protected
     */
    protected static $arrSettings = null;

    /**
     * The group last used to {@see init()} the settings.
     * Defaults to null (ignored).
     * @var     string
     * @static
     * @access  protected
     */

    protected static $group = null;
    /**
     * The section last used to {@see init()} the settings.
     * Defaults to null (which will cause an error in most methods).
     * @var     string
     * @static
     * @access  protected
     */

    protected static $section = null;
    /**
     * Changed flag
     *
     * This flag is set to true as soon as any change to the settings is detected.
     * It is cleared whenever {@see updateAll()} is called.
     * @var     boolean
     * @static
     * @access  protected
     */
    protected static $changed = false;
    /**
     * Returns the current value of the changed flag.
     *
     * If it returns true, you probably want to call {@see updateAll()}.
     * @return  boolean           True if values have been changed in memory,
     *                            false otherwise
     */
    public static function changed()
    {
        return self::$changed;
    }

    /**
     * Tab counter for the {@see show()} and {@see show_external()}
     * @var     integer
     * @access  public
     */
    public static $tab_index = 1;

    /**
     * Optionally sets and returns the value of the tab index
     * @param   integer $tab_index  The optional new tab index
     * @return  integer             The current tab index
     */
    public static function tab_index($tab_index=null)
    {
        if (isset($tab_index)) {
            self::$tab_index = intval($tab_index);
        }
        return self::$tab_index;
    }

    /**
     * Flush the stored settings
     *
     * Resets the class to its initial state.
     * Does *NOT* clear the section, however.
     * @return  void
     */
    public static function flush()
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
    public static function getArray($section, $group=null)
    {
        if (self::$section !== $section
         || self::$group !== $group) {
            if (!parent::init($section, $group)) return false;
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
    public static function getValue($name)
    {
        if (is_null(self::$arrSettings)) {
            \DBG::log("\Cx\Core\Setting\Model\Entity\Engine::getValue($name): ERROR: no settings loaded");
            return null;
        }
        if (isset(self::$arrSettings[$name]['value'])) {
            return self::$arrSettings[$name]['value'];
        };
        return null;
    }

     /**
     * Returns the true or false for given the setting name
     * 
     * If the settings have not been initialized (see {@see init()}), or
     * if no setting of that name is present in the current set, false
     * is returned.
     * @param   string    $name       The settings name
     * @return  boolean               The if setting name is exist returned true,
     *                                false otherwise
     */
    public static function isDefined($name)
    { 
        if (isset(self::$arrSettings[$name]['name'])) {
            return true;   
        }
        return false;
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
    public static function set($name, $value)
    {
        if (!isset(self::$arrSettings[$name])) {
        // \DBG::log("\Cx\Core\Setting\Model\Entity\Engine::set($name, $value): Unknown, changed: ".self::$changed);
            return false;
        }
        if (self::$arrSettings[$name]['value'] == $value) {
        // \DBG::log("\Cx\Core\Setting\Model\Entity\Engine::set($name, $value): Identical, changed: ".self::$changed);
            return null;
        }
        self::$changed = true;
        self::$arrSettings[$name]['value'] = $value;
        // \DBG::log("\Cx\Core\Setting\Model\Entity\Engine::set($name, $value): Added/updated, changed: ".self::$changed);
        return true;
    }
}
