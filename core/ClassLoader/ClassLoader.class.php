<?php
namespace Cx\Core\ClassLoader;

class ClassLoader {
    private $legacyClassLoader;
    
    public function __construct() {
        spl_autoload_register(array($this, 'autoload'));
        $this->legacyClassLoader = new LegacyClassLoader();
    }
    
    private function autoload($name) {
        if ($this->load($name, $path)) {
            return;
        }
        if ($path) {
            //echo '<b>' . $name . ': ' . $path . '</b><br />';
        }
        $this->loadLegacy($name);
    }
    
    private function load($name, &$resolvedPath) {
        $parts = explode('\\', $name);
        // new classes should be in namespace \Cx\something
        if (!in_array(current($parts), array('Cx'/*, 'Doctrine'*/, 'Gedmo'/*, 'Symfony'*/)) || count($parts) < 3) {
            return false;
        }
        
        // Exception for model, its within /model/[entities|events]/cx/model/
        if ($parts[0] == 'Cx' && $parts[1] == 'Model') {
            $third = 'Entities';
            if ($parts[2] == 'Events') {
                $third = 'Events';
            }
            $parts = array_merge(array('Cx', 'Model', $third), $parts);
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
        $path = ASCMS_DOCUMENT_ROOT;
        foreach ($parts as $part) {
            $part = '/' . $part;
            if (!is_dir($path . $part)) {
                break;
            }
            $path .= $part;
        }
        $className = preg_replace('/Exception/', '', $className);
        $resolvedPath = $path . '/' . $className . '.class.php';
        if (file_exists($path . '/' . $className . '.class.php')) {
            //echo $name . ' :: ' . $path . '/' . $className . '<br />';
            require_once($path . '/' . $className . '.class.php');
            return true;
        }
        //echo '<span style="color: red;">' . implode('\\', $parts) . '</span>';
        return false;
    }
    
    private function loadLegacy($name) {
        $this->legacyClassLoader->autoload($name);
    }
}
