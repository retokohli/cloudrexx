<?php

/**
 * Contrexx ClassLoader
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_classloader
 */
 
namespace Cx\Core\ClassLoader;

/**
 * Contrexx ClassLoader
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_classloader
 */
class ClassLoader {
    private $basePath;
    private $customizingPath;
    private $legacyClassLoader = null;
    private $cx = null;
    
    /**
     * To use LegacyClassLoader config.php and set_constants.php must be loaded
     * If they are not present, set $useLegacyAsFallback to false!
     * @param String $basePath Base directory to load files (e.g. ASCMS_DOCUMENT_ROOT)
     * @param boolean $useLegacyAsFallback (optional) Wheter to use LegacyClassLoader too (default) or not
     */
// TODO: Fix ClassCloader instantiation in:
//      update/updates/3.2.0/update.php
//      installer/classloader.inc.php
    public function __construct($cx, $useLegacyAsFallback = true, $customizingPath = null) {
        $this->cx = $cx;
        $this->basePath = $cx->getCodeBaseDocumentRootPath();
        $this->customizingPath = $customizingPath;

        // Check if there is already an other instance of the Contrexx ClassLoader running.
        // If so, we shall unregister it.
        if (class_exists('Env', false)) {
            $oldClassLoader = \Env::get('ClassLoader');
            if ($oldClassLoader) {
                spl_autoload_unregister(array($oldClassLoader, 'autoload'));
            }
        }
        spl_autoload_register(array($this, 'autoload'));
        if ($useLegacyAsFallback) {
            $this->legacyClassLoader = new LegacyClassLoader($this, $cx);
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
        if (!in_array(current($parts), array('Cx', 'Doctrine', 'Gedmo', 'DoctrineExtension', 'Symfony')) || count($parts) < 2) {
            return false;
        }
        if (substr($name, 0, 8) == 'PHPUnit_') {
            return false;
        }
        
        $suffix = '.class';
        if ($parts[0] == 'Cx') {
            // Exception for model, its within /model/[entities|events]/cx/model/
            if ($parts[1] == 'Model') {
                $third = 'entities';
                if ($parts[2] == 'Events') {
                    $third = 'events';
                }
                if ($parts[2] == 'Proxies') {
                    $third = 'proxies';
                    $suffix = '';
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
            $suffix = '';
            $parts = array_merge(array('Cx', 'Lib', 'doctrine'), $parts);
            //$parts = array_merge(array('Cx', 'Model', 'entities'), $parts);
        } else if ($parts[0] == 'Doctrine') {
            $suffix = '';
            if ($parts[1] == 'ORM') {
                $parts = array_merge(array('Cx', 'Lib', 'doctrine'), $parts);
            } else {
                $parts = array_merge(array('Cx', 'Lib', 'doctrine', 'vendor', 'doctrine-' . strtolower($parts[1]), 'lib'), $parts);
            }
        } else if ($parts[0] == 'DoctrineExtension') {
            $suffix = '';
            $parts = array_merge(array('Cx', 'Model', 'extensions'), $parts);
        } else if ($parts[0] == 'Symfony') {
            $suffix = '';
            $parts = array_merge(array('Cx', 'Lib', 'doctrine', 'vendor'), $parts);
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
            if (!is_dir($this->basePath . $path . $part) && (!$this->customizingPath || !is_dir($this->customizingPath . $path . $part))) {
                return false;
            }
            $path .= $part;
        }
        
        $resolvedPath = $path . '/' . $className . $suffix . '.php';
        if (preg_match('/Exception/', $className) && !$this->loadFile($resolvedPath)) {
            $className = preg_replace('/Exception/', '', $className);
            $resolvedPath = $path . '/' . $className . $suffix . '.php';
        }
        if ($this->loadFile($resolvedPath)) {
            return true;
        } else if ($this->loadFile($path.'/'.$className.'.interface.php')) {
            return true;
        }
        //echo '<span style="color: red;">' . implode('\\', $parts) . '</span>';
        return false;
    }
    
    public function loadFile($path) {
        
        $path = $this->getFilePath($path);
        if (!$path) {
            return false;
        }
        require_once($path);
        return true;
    }
    
    public function getFilePath($file, &$isCustomized = false) {
        $file = preg_replace('#\\\\#', '/', $file);
        // using $this->cx->getCodeBaseDocumentRootPath() here instead of $this->basePath
        // makes sure that no matter where the ClassLoader gets initialized,
        // all customized files are always located in the folder /customizing.
        // This means that also the customized files of the installer will be
        // located in the folder /customizing. 
        $regex = preg_replace('#([\(\)])#', '\\\\$1', $this->cx->getCodeBaseDocumentRootPath());
        $fileInCodeBase = preg_replace('#'.$regex.'#', '', $file);
        
        // load class from customizing folder
        if ($this->customizingPath && file_exists($this->customizingPath.$fileInCodeBase)) {
            $isCustomized = true;
            return $this->customizingPath.$fileInCodeBase;
        
        // load class from basepath
        } else if (file_exists($this->basePath.$fileInCodeBase)) {
            $isCustomized = false;
            return $this->basePath.$fileInCodeBase;
        }

        $regex = preg_replace('#([\(\)])#', '\\\\$1', $this->cx->getWebsiteThemesPath());
        $fileInWebsite = preg_replace('#'.$regex.'#', '', $file);
        
        // load class from basepath
        if (file_exists($this->cx->getWebsiteThemesPath().$fileInWebsite)) {
            $isCustomized = false;
            return $this->cx->getWebsiteThemesPath().$fileInWebsite;
        }

        return false;
    }
    
    private function loadLegacy($name) {
        if ($this->legacyClassLoader) {
            $this->legacyClassLoader->autoload($name);
        }
    }
    
    /**
     * Tests if a class is available. You may specify if legacy and customizing
     * can be used to load it if necessary.
     * @todo $useCustomizing does not work correctly if legacy is enabled
     * @param string $class Class name to look for
     * @param boolean $useLegacy (optional) Wheter to allow usage of legacy class loader or not (default false)
     * @param boolean $useCustomizing (optional) Wheter to allow usage of customizings or not (default true)
     * @return boolean True if class could be found using the allowed methods, false otherwise 
     */
    public function classExists($class, $useLegacy = false, $useCustomizing = true) {
        if ($useLegacy) {
            return class_exists($class) || interface_exists($class, false);
        }
        $legacy = $this->legacyClassLoader;
        $this->legacyClassLoader = null;
        $customizing = $this->customizingPath;
        if (!$useCustomizing) {
            $customizing = null;
        }
        $ret = class_exists($class) || interface_exists($class, false);
        $this->legacyClassLoader = $legacy;
        $this->customizingPath = $customizing;
        return $ret;
    }
}

