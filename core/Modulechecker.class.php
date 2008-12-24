<?php
/**
 * Module Checker
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Module Checker Class
 *
 * Checks for activated modules and plugins
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @access        public
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  core
 */
class ModuleChecker
{
    /**
    * Contains the array of the core modules name
    * @access public
    * @var array
    * @see __construct()
    */
    private $coreModules = array('contact','core', 'error', 'ids', 'login', 'home', 'news','media','nettools','search','sitemap','stats');
    private $arrModules = array();
    private $arrActiveModulesById = array();
    private $arrActiveModulesByName = array();
    private $arrInstalledModules = array();
    private $arrUsedModules = array();
    private $existsModuleFolders = false;


    /**
     * PHP5 constructor
     * @access public
     */
    function __construct()
    {
        $this->init();
        $this->moduleFoldersCheck();
    }


    function init()
    {
        global $objDatabase;

        // check the content for installed and used modules
        $objResult = $objDatabase->Execute("
            SELECT module
              FROM ".DBPREFIX."content_navigation
             WHERE module!=0 AND lang=".FRONTEND_LANG_ID."
             GROUP BY module
        ");
        if ($objResult) {
            while (!$objResult->EOF) {
                $this->arrUsedModules[] = $objResult->fields['module'];
                $objResult->MoveNext();
            }
        }
        array_push($this->arrUsedModules, 7);
        // check the module database tables for required modules
        $objResult = $objDatabase->Execute("
            SELECT id, name, is_core, is_required
              FROM ".DBPREFIX."modules
        ");
        if ($objResult) {
            while (!$objResult->EOF) {
                $moduleName = $objResult->fields['name'];
                if (!empty($moduleName)) {
                    $this->arrModules[$objResult->fields['id']] = $objResult->fields['name'];
                }
                if (   $objResult->fields['is_core'] == '1'
                    || $objResult->fields['is_required'] == '1') {
                    $this->arrActiveModulesById[$objResult->fields['id']] = $objResult->fields['name'];
                    $this->arrActiveModulesByName[$objResult->fields['name']] = $objResult->fields['id'];
                } else {
                    if (in_array($objResult->fields['id'], $this->arrUsedModules)) {
                        if (is_dir(ASCMS_MODULE_PATH.'/'.$objResult->fields['name'])) {
                            $this->arrActiveModulesById[$objResult->fields['id']] = $objResult->fields['name'];
                            $this->arrActiveModulesByName[$objResult->fields['name']] = $objResult->fields['id'];
                        }
                    }
                }
                $objResult->MoveNext();
            }
        }
    }


    function moduleFoldersCheck()
    {
        foreach($this->arrModules as $moduleName ) {
            if (is_dir(ASCMS_MODULE_PATH.'/'.$moduleName)) {
                $this->existsModuleFolders = true;
            }
        }
    }


    function getModuleStatusById($moduleId)
    {
        if (in_array($moduleId, $this->arrActiveModulesByName))
            return true;
        return false;
    }


    function getModuleStatusByName($moduleName)
    {
        if (in_array($moduleName, $this->arrActiveModulesById))
            return true;
        return false;
    }


    /**
     * Returns the ID for the module name given, if known.
     * @return  integer       The module ID on success, 0 (zero) otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @static
     * @global  ADOConnection   $objDatabase
     * @since   2.1.0
     */
    static function getModuleIdByName($moduleName)
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            SELECT `id`
              FROM `".DBPREFIX."modules`
             WHERE `name`='".addslashes($moduleName)."'
        ");
        if (!$objResult || $objResult->EOF) return 0;
        return $objResult->fields['id'];
    }

}

?>
