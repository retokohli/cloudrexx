<?php
namespace Cx\Core\ClassLoader;

class ClassLoader {
    private $basePath;
    private $customizingPath;
    private $legacyClassLoader = null;
    
    /**
     * To use LegacyClassLoader config.php and set_constants.php must be loaded
     * If they are not present, set $useLegacyAsFallback to false!
     * @param String $basePath Base directory to load files (e.g. ASCMS_DOCUMENT_ROOT)
     * @param boolean $useLegacyAsFallback (optional) Wheter to use LegacyClassLoader too (default) or not
     */
    public function __construct($basePath, $useLegacyAsFallback = true, $customizingPath = null) {
        $this->basePath = $basePath;
        $this->customizingPath = $customizingPath;
        spl_autoload_register(array($this, 'autoload'));
        if ($useLegacyAsFallback) {
            $this->legacyClassLoader = new LegacyClassLoader();
        }
    }
    
    /**
     * This needs to be public because Doctrine tries to load a class using all
     * registered autoloaders.
     * @param type $name
     * @return type void
     */
    public function autoload($name) {
        //print $name."<br>";
        if ($this->load($name, $path)) {
            return;
        }
        if ($path) {
            //echo '<b>' . $name . ': ' . $path . '</b><br />';
        }
        $this->loadLegacy($name);
    }
    
    private function load($name, &$resolvedPath) {
        if (substr($name, 0, 1) == '\\') {
            $name = substr($name, 1);
        }
        $parts = explode('\\', $name);
        // new classes should be in namespace \Cx\something
        if (!in_array(current($parts), array('Cx'/*, 'Doctrine'*/, 'Gedmo'/*, 'Symfony'*/)) || count($parts) < 3) {
            return false;
        }
        
        if ($parts[0] == 'Cx') {
            // Exception for model, its within /model/[entities|events]/cx/model/
            if ($parts[1] == 'Model') {
                $third = 'entities';
                if ($parts[2] == 'Events') {
                    $third = 'events';
                }
                $parts = array_merge(array('Cx', 'Model', $third), $parts);
                
            // Exception for lib, its within /model/FRAMEWORK/
            } else if ($parts[1] == 'Lib') {
                unset($parts[0]);
                unset($parts[1]);
                $parts = array_merge(array('Cx', 'Lib', 'FRAMEWORK'), $parts);
            }
        
        // Exception for overwritten gedmo classes, they are within /model/entities/Gedmo
        // This is not ideal, maybe move the classes somewhere
        } else if ($parts[0] == 'Gedmo') {
            $parts = array_merge(array('Cx', 'Model', 'entities'), $parts);
        }
        
        // we don't need the Cx part
        unset($parts[0]);
        // core, lib, model, etc. are lowercase by design
        $parts[1] = strtolower($parts[1]);
        // but we need the original class name to find the correct file name
        $className = end($parts);
        unset($parts[count($parts)]);
        reset($parts);
        
        // find matching path
        $path = '';
        foreach ($parts as $part) {
            $part = '/' . $part;
            if (!is_dir($this->basePath . $path . $part)) {
                break;
            }
            $path .= $part;
        }
        $className = preg_replace('/Exception/', '', $className);
        $resolvedPath = $path . '/' . $className . '.class.php';
        
        // load class from customizing folder
        if ($this->customizingPath && file_exists($this->customizingPath . $path . '/' . $className . '.class.php')) {
            require_once($this->customizingPath . $path . '/' . $className . '.class.php');
            return true;
        } else if ($this->customizingPath && file_exists($this->customizingPath . $path . '/' . $className . '.interface.php')) {
            require_once($this->customizingPath . $path . '/' . $className . '.interface.php');
            return true;
        
        // load class from basepath
        } else if (file_exists($this->basePath . $path . '/' . $className . '.class.php')) {
            //echo $name . ' :: ' . $path . '/' . $className . '<br />';
            require_once($this->basePath . $path . '/' . $className . '.class.php');
            return true;
        } else if (file_exists($this->basePath . $path . '/' . $className . '.interface.php')) {
            require_once($this->basePath . $path . '/' . $className . '.interface.php');
            return true;
        }
        //echo '<span style="color: red;">' . implode('\\', $parts) . '</span>';
        return false;
    }
    
    private function loadLegacy($name) {
        if ($this->legacyClassLoader) {
            $this->legacyClassLoader->autoload($name);
        }
    }
}
