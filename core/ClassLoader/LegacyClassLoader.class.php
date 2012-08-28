<?php
namespace Cx\Core\ClassLoader;

class LegacyClassLoader {
    private static $instance = null;
    private $tab = 0;
    private $timeUsed = 0;
    private $bytes = 0;
    private $subBytes = 0;
    private $mapTable = array();
    
    public function __construct() {
        self::$instance = $this;
        /*global $mapTable;
        require_once(dirname(__FILE__) . '/LegacyClassCache.php');
        $this->mapTable = $mapTable;*/
        if (file_exists(ASCMS_TEMP_PATH.'/legacyClassCache.tmp')) {
            $this->mapTable = unserialize(file_get_contents(ASCMS_TEMP_PATH.'/legacyClassCache.tmp'));
        }
    }
    
    public function autoload($name) {
        $startTime = microtime(true);
        if (isset($this->mapTable[$name])) {
            $this->loadClass('.'.$this->mapTable[$name], $name);
        } else {
            // class not in match table, guess path
            $parts = explode('\\', $name);
            // Let doctrine handle it's includes itself
            if (in_array($parts[0], array('Symfony', 'Doctrine', 'Gedmo', 'DoctrineExtension'))) {
                return;
            // I don't know where they come from, but there's no need to load these
            // I guess doctrine does load those
            } else if (in_array($name, array('var', 'Column', 'MappedSuperclass', 'Table', 'index', 'Entity', 'Id', 'GeneratedValue'))) {
                return;
            }
            // we do not need the namespace, it's probably wrong anyway
            $origName = $name;
            $name = end($parts);
            // start try and error...
            // files in /core
            if ($this->testLoad(ASCMS_CORE_PATH.'/'.$name.'.class.php', $origName)) { return; }
            // files in /lib
            if ($this->testLoad(ASCMS_LIBRARY_PATH.'/'.$name.'.php', $origName)) { return; }
            // files in /lib/FRAMEWORK/User
            if ($this->testLoad(ASCMS_FRAMEWORK_PATH.'/User/'.$name.'.class.php', $origName)) { return; }
            // files in /lib/FRAMEWORK
            if ($this->testLoad(ASCMS_FRAMEWORK_PATH.'/'.preg_replace('/FW/', '', $name).'.class.php', $origName)) { return; }
            // files in /lib/PEAR
            if ($this->testLoad(ASCMS_LIBRARY_PATH.'/PEAR/'.preg_replace('/_/', '/', preg_replace('/PEAR\//', '', $name . '/')).end(preg_split('/_/', $name)).'.php', $origName)) { return; }
            // files in /model/entities/Cx/Model/Base
            if ($this->testLoad(ASCMS_MODEL_PATH.'/entities/Cx/Model/Base/' . $name . '.php', $origName)) { return; }
            
            // core module and module libraries /[core_modules|modules]/{modulename}/lib/{modulename}Lib.class.php
            $moduleName = strtolower(preg_replace('/Library/', '', $name));
            // exception for mediadir
            $moduleName = preg_replace('/mediadirectory/', 'mediadir', $moduleName);
            if ($this->testLoad(ASCMS_CORE_MODULE_PATH.'/' . $moduleName . '/lib/' . $moduleName . 'Lib.class.php', $origName)) { return; }
            if ($this->testLoad(ASCMS_CORE_MODULE_PATH.'/' . $moduleName . '/lib/Lib.class.php', $origName)) { return; }
            if ($this->testLoad(ASCMS_CORE_MODULE_PATH.'/' . $moduleName . '/lib/lib.class.php', $origName)) { return; }
            if ($this->testLoad(ASCMS_CORE_MODULE_PATH.'/' . $moduleName . '/Lib.class.php', $origName)) { return; }
            if ($this->testLoad(ASCMS_MODULE_PATH.'/' . $moduleName . '/lib/' . $moduleName . 'Lib.class.php', $origName)) { return; }
            if ($this->testLoad(ASCMS_MODULE_PATH.'/' . $moduleName . '/lib/Lib.class.php', $origName)) { return; }
            if ($this->testLoad(ASCMS_MODULE_PATH.'/' . $moduleName . '/lib/lib.class.php', $origName)) { return; }
            if ($this->testLoad(ASCMS_MODULE_PATH.'/' . $moduleName . '/Lib.class.php', $origName)) { return; }
            
            // core module and module model /[core_modules|modules]/{modulename}/lib/
            $moduleName = current(preg_split('/[A-Z]/', lcfirst($name)));
            $nameWithoutModule = substr($name, strlen($moduleName));
            $nameWithoutModuleLowercase = strtolower($nameWithoutModule);
            if ($this->testLoad(ASCMS_CORE_MODULE_PATH.'/' . $moduleName . '/lib/' . $name . '.class.php', $origName)) { return; }
            if ($this->testLoad(ASCMS_CORE_MODULE_PATH.'/' . $moduleName . '/lib/' . $nameWithoutModule . '.class.php', $origName)) { return; }
            if ($this->testLoad(ASCMS_CORE_MODULE_PATH.'/' . $moduleName . '/lib/' . $nameWithoutModuleLowercase . '.class.php', $origName)) { return; }
            if ($this->testLoad(ASCMS_MODULE_PATH.'/' . $moduleName . '/lib/' . $name . '.class.php', $origName)) { return; }
            if ($this->testLoad(ASCMS_MODULE_PATH.'/' . $moduleName . '/lib/' . $nameWithoutModule . '.class.php', $origName)) { return; }
            if ($this->testLoad(ASCMS_MODULE_PATH.'/' . $moduleName . '/lib/' . $nameWithoutModuleLowercase . '.class.php', $origName)) { return; }
            // exception for data module
            if ($this->testLoad(ASCMS_MODULE_PATH.'/' . $moduleName . '/' . $name . '.class.php', $origName)) { return; }
            
            // core module and module indexes /[core_modules|modules]/{modulename}/[index.class.php|admin.class.php]
            $moduleName = strtolower($name);
            if ($this->testLoad(ASCMS_CORE_MODULE_PATH.'/' . $moduleName . '/index.class.php', $origName)) { return; }
            if ($this->testLoad(ASCMS_CORE_MODULE_PATH.'/' . $moduleName . '/admin.class.php', $origName)) { return; }
            if ($this->testLoad(ASCMS_MODULE_PATH.'/' . $moduleName . '/index.class.php', $origName)) { return; }
            if ($this->testLoad(ASCMS_MODULE_PATH.'/' . $moduleName . '/admin.class.php', $origName)) { return; }
            
            // core module and module model classes not containing module name
            if ($this->testLoad(ASCMS_MODULE_PATH.'/shop/lib/' . $name . '.class.php', $origName)) { return; }
            
            // Temporary exceptions
            // exception for filesystem. TEMPORARY!
            if ($this->testLoad(ASCMS_FRAMEWORK_PATH.'/File/'.$name.'.class.php', $origName)) {return; }
            // exception for CxJs. TEMPORARAY!
            if ($this->testLoad(ASCMS_FRAMEWORK_PATH.'/cxjs/'.$name.'.class.php', $origName)) {return; }
            if ($this->testLoad(ASCMS_FRAMEWORK_PATH.'/cxjs/'.$name.'.interface.php', $origName)) {return; }
            if ($this->testLoad(ASCMS_FRAMEWORK_PATH.'/cxjs/i18n/'.preg_replace('/JQueryUiI18nProvider/', 'jQueryUi', $name).'.class.php', $origName)) {return; }
            
            // This is sort of like giving in...
            $this->fallbackLoad($origName, $name);
        }
        $endTime = microtime(true);
        $this->timeUsed += ($endTime - $startTime);
    }
    
    private function testLoad($path, $name) {
        if (file_exists($path)) {
            $path = substr($path, strlen(ASCMS_DOCUMENT_ROOT));
            $this->loadClass($path, $name);
            $this->mapTable[$name] = $path;
            file_put_contents(ASCMS_TEMP_PATH.'/legacyClassCache.tmp', serialize($this->mapTable));
            return true;
        }
        return false;
    }
    
    /**
     * This method won't work for all files at once because max exec time is too short
     * @param type $name
     * @param type $className 
     */
    private function fallbackLoad($name, $className) {
        //echo $name . '<br />';
        $namespace = substr($name, 0, strlen($name) - strlen($className) - 1);
        $path = $this->searchClass($className, $namespace);
        if ($path === false) {
            // this class does not exist!
            return;
        }
        $this->testLoad($path, $name);
    }
    
    private function searchClass($name, $namespace, $path = ASCMS_DOCUMENT_ROOT) {
        $files = glob($path . '/*.php');
        foreach ($files as $file) {
            $adminClass = 'admin.class.php';
            $indexClass = 'index.class.php';
            if (!defined('BACKEND_LANG_ID') && substr($file, strlen($file) - strlen($adminClass)) == $adminClass) {
                continue;
            }
            if (defined('BACKEND_LANG_ID') && substr($file, strlen($file) - strlen($indexClass)) == $indexClass) {
                continue;
            }
            $fcontent = file_get_contents($file);
            // match namespace too
            $matches = array();
            
            if (preg_match('/(?:namespace\s+([\\\\\w]+);[.\n\r]*?)?(?:class|interface)\s+' . $name . '\s+(?:extends|implements)[\\\\\s\w,\n\t\r]*?\{/', $fcontent, $matches)) {
            //if (preg_match('/(?:namespace ([\\\\a-zA-Z0-9_]*);[\w\W]*)?(?:class|interface) ' . $name . '(?:[ a-zA-Z0-9\n\r\t\\\\_])*\{/', $fcontent, $matches)) {
                if (!isset($matches[1]) || $matches[1] == $namespace) {
                    return $file;
                }
            }
        }
        foreach (glob($path.'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            if (in_array($dir, array(
                ASCMS_DOCUMENT_ROOT . '/hotfix',
                ASCMS_DOCUMENT_ROOT . '/update',
                ASCMS_DOCUMENT_ROOT . '/testing'
            ))) {
                continue;
            }
            $result = $this->searchClass($name, $namespace, $dir);
            if ($result !== false) {
                return $result;
            }
        }
        return false;
    }
    
    private function loadClass($path, $name) {
        $this->tab++;
        $bytes = memory_get_peak_usage();
        require_once ASCMS_DOCUMENT_ROOT . '/' . $path;
        $bytes = memory_get_peak_usage()-$bytes;
        $this->tab--;
        $ownBytes = '';
        if ($this->tab == 0) {
            //$ownBytes = ' (' . formatBytes($bytes - $this->subBytes) . ')';
            $this->subBytes = 0;
        } else {
            $this->subBytes += $bytes;
        }
        $this->bytes += $bytes;
        //echo '<span style="color:red; margin-left: ' . (20 * $this->tab) . 'px">' . $name . ' from ' . $path . '</span><br />';
        //echo '<span style="color:red; margin-left: ' . (20 * $this->tab) . 'px">' . formatBytes($bytes) . $ownBytes . ' from ' . $name . '</span><br />';
        /*if ($this->tab == 0) {
            echo '<br />';
        }*/
    }
    
    public static function getInstance() {
        return self::$instance;
    }
    
    public function getTimeUsed() {
        return $this->timeUsed;
    }
    
    public function getRamUsed() {
        return $this->bytes;
    }
}
