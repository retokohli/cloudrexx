<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Cx\Core\Core\Model\Entity;
/**
 * Represents an abstraction of a component
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
class ReflectionComponent {
    protected static $componentTypes = array('core', 'core_module', 'module', 'lib');
    protected $componentName = null;
    protected $componentType = null;
    
    /**
     * Two different ways to instanciate this are supported:
     * 1. Supply an instance of \Cx\Core\Core\Model\Entity\Component
     * 2. Supply a component name and type
     * @param mixed $arg1 Either an instance of \Cx\Core\Core\Model\Entity\Component or the name of a component
     * @param string $arg2 (only if a component name was supplied as $arg1) Component type (one of core_module, module, core, lib)
     */
    public function __construct($arg1, $arg2 = null) {
        if (is_a($arg1, 'Cx\Core\Core\Model\Entity\SystemComponent')) {
            $this->componentName = $arg1->getName();
            $this->componentType = $arg1->getType();
            return;
        } else if (is_string($arg1) && $arg2 && in_array($arg2, self::$componentTypes)) {
            $this->componentName = $arg1;
            $this->componentType = $arg2;
            return;
        }
        throw new \BadMethodCallException('Pass a component or specify a component name and type');
    }
    
    /**
     * Tells wheter this component is customized or not
     * @return boolean True if customized (and customizings are active)
     */
    protected function isCustomized() {
        $basepath = ASCMS_DOCUMENT_ROOT.$this->getPathForType($this->componentType);
        $componentPath = $basepath . '/' . $this->componentName;
        return \Env::get('ClassLoader')->getFilePath($componentPath) != $componentPath;
    }
    
    /**
     * Returns wheter this component exists or not
     * @param boolean $allowCustomizing (optional) Set to false if you want to ignore customizings
     * @return boolean True if it exists, false otherwise
     */
    public function exists($allowCustomizing = true) {
        return file_exists($this->getDirectory($allowCustomizing));
    }
    
    /**
     * Returns wheter this component is valid or not. A valid component will work as expected
     * @return boolean True if valid, false otherwise
     */
    public function isValid() {
        // file system
        if (!$this->exists()) {
            return false;
        }
        
        // DB: entry in components or modules
        // DB: entry in backend areas
        // DB: existing page if necessary
        
        // what else?
        
        return true;
    }
    
    /**
     * Tells wheter this is a legacy component or not
     * @return boolean True if its a legacy one, false otherwise
     */
    public function isLegacy() {
        if (!$this->exists()) {
            return false;
        }
        if (file_exists($this->getDirectory() . '/Controller/ComponentController.class.php')) {
            return false;
        }
        return true;
    }
    
    /**************************/
    /* PROTECTED UTIL METHODS */
    /**************************/
    
}
