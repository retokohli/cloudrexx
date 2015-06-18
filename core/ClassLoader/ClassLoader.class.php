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

    /**
     * Get the path to a customized version of a file
     *
     * The detection algorithm for a customized version uses the follow precedence:
     *
     * 1. If we are in FRONTEND mode and the file is part of the 'view' layer (it is located within the 'View'
     *      folder of its component), then it will return the path to the customized version of the file in the 
     *      currently active design theme (if it does exist at all).
     *      Note that the folder 'View' is being left out in the design theme as only files of the 'View' folder
     *      can be loaded from the design theme.
     *      I.e.: /themes/default/core_modules/Media/Media/Pdf.png
     *
     * 2. If a customized version exists in the /customizing folder, then the path to that one will be returned.
     *      I.e.: /customizing/core_modules/Media/View/Media/Pdf.png
     *
     * 3. If a customized version exists in the website data repository, then that one will be returned.
     *      I.e.: /websites/demo/core_modules/Media/View/Media/Pdf.png
     *
     * 4. Ensure that the original version of the file exists at least (within the code base repository) and 
     *      return that one.
     *      I.e.: /core_modules/Media/View/Media/Pdf.png
     *
     * @param   string  $file           The file to return the path from.
     * @param   boolean $isCustomized   If $isCustomized is provided, then it is set to TRUE if a customized version
     *                                  of the file does exist. Otherwise it is set to FALSE.
     * @param   boolean $isWebsite      If $isWebsite is provided, then it is set to TRUE if the file can be located
     *                                  in the website data repository. Otherwise it is set to FALSE.
     * @param   boolean $webPath        Whether or not to return the absolute file system path of the customized file.
     * @return  mixed                   Returns the path (either absolute file system path or relativ web path, based
     *                                  on $webPath) to a customized version of the file identified by $file.
     *                                  If no customized version of the file does exist, then FALSE is being returned.
     */
    public function getFilePath($file, &$isCustomized = false, &$isWebsite = false, $webPath = false) {
        // make lookup algorithm work on Windows by replacing backslashes by forward slashes
        $file = preg_replace('#\\\\#', '/', $file);

        // remove any URL arguments from the file path like '?foo=bar' or '#foo'
        $file = preg_replace('#(\?[^\?]*|\#[^\#]*)$#', '', $file);

        // using $this->cx->getCodeBaseDocumentRootPath() here instead of $this->basePath
        // makes sure that no matter where the ClassLoader gets initialized,
        // all customized files are always located in the folder /customizing.
        // This means that also the customized files of the installer will be
        // located in the folder /customizing.
        if(strpos($file, $this->cx->getWebsiteDocumentRootPath()) === 0) {
            $file = preg_replace('#^'.preg_quote($this->cx->getWebsiteDocumentRootPath(), '#').'#', '', $file);
        } else {
            $file = preg_replace('#^'.preg_quote($this->cx->getCodeBaseDocumentRootPath(), '#').'#', '', $file);
        }
        
        // load class from customizing folder
        $isCustomized = false;
        $isWebsite = false;

        // if we're running in frontend and the file is from the view layer (as in MVC),
        // then we shall see if there is a customized version of the file in the
        // loaded design theme and use that one instead of the original one.
        if (   $this->cx->getMode() == \Cx\Core\Core\Controller\Cx::MODE_FRONTEND
              // check if file is from view layer (as in MVC)
           && preg_match('#^(?:.*/)?(?:core|core_modules|modules)/[^/]+/View/#', $file)
              // check for a few system dependencies...
           && class_exists('Env', false)
              // ...if InitCMS has been initialized already
           && ($objInit = \Env::get('init'))
              // ...if frontend theme has been loaded already
           && ($currentThemesPath = $objInit->getCurrentThemesPath())
              // set up path of custom themed file
           && ($customThemeFile = '/'. $currentThemesPath . preg_replace('#^(.*/)?(core|core_modules|modules)(/[^/]+/)View/#', '\1\2\3', $file))
              // set up absolute path of custom themed file
           && ($absoluteCustomThemeFile = $this->cx->getWebsiteThemesPath() . $customThemeFile)
              // finally, check if a custom themed version of the file does exist
           && file_exists($absoluteCustomThemeFile)
              // last but not least, let's do a security check
              //    When the LegacyClassLoader is not initialized you cant load the FWValidator class
              //    where is needed for the security check
           && $this->legacyClassLoader
              //    Checks if the file is a harmless one, because you can upload anything
              //    over the ftp which probably not should be executed
           && \FWValidator::is_file_ending_harmless($file)
        ) {
           return ($webPath ? $this->cx->getWebsiteThemesWebPath() : $this->cx->getWebsiteThemesPath()) . $customThemeFile;
        }

        // check if there is a customized version of the file available and return that one instead
        if ($this->customizingPath && file_exists($this->customizingPath.$file)) {
            $isCustomized = true;
            return ($webPath ? $this->cx->getWebsiteOffsetPath() . substr($this->customizingPath, strlen($this->cx->getWebsiteDocumentRootPath())) : $this->customizingPath) . $file;
        }

        // load file from website path
        if (
            // When the LegacyClassLoader is not initialized you cant load the FWValidator class
            // where is needed for the security check
            $this->legacyClassLoader &&
            // Checks if the file is a harmless one, because you can upload anything
            // over the ftp which probably not should be executed
            \FWValidator::is_file_ending_harmless($file) &&
            file_exists($this->cx->getWebsiteDocumentRootPath().$file)
        ) {
            $isWebsite = true;
            return ($webPath ? $this->cx->getWebsiteOffsetPath() : $this->cx->getWebsiteDocumentRootPath()) . $file;
        }

        // load file from code base path
        if (file_exists($this->basePath.$file)) {
            return ($webPath ? $this->cx->getCodeBaseOffsetPath() : $this->basePath) . $file;
        }

        // lookup of file failed -> file does not exist
        return false;
    }

    /**
     * Shortcut for {@see getFilePath()} with argument $webPath set to TRUE
     */
    public function getWebFilePath($file, &$isCustomized = false, &$isWebsite = false) {
        return $this->getFilePath($file, $isCustomized, $isWebsite, true);
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

