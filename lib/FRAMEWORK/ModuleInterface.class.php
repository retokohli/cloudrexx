<?php
class ModuleInterfaceException extends Exception {}

/**
 * An interface using which modules provide functionality to stuff outside the module itself.
 * Subclass this (xyModuleInterface extends ModuleInterface) if your module needs to be called from other parts of the cms.
 * Module Interfaces are Singletons.
 */
abstract class ModuleInterface {
    //singleton functionality: instance
    static private $instances = array();
    
    /**
     * Instance getter
     * @param string $moduleName the modules' interface you want
     * @throws ModuleInterfaceException if you provide an unknonw class
     * @return ModuleInterface
     */
    static public function getInstance($moduleName)
    {
        //all module interfaces follow the xyModuleInterface convention, extend the classname
        $className = $moduleName.'ModuleInterface';
        if(!class_exists($className))
           throw new ModuleInterfaceException("Could not find class '$className'. Did you load the appropriate header?");

        //handle instantiation
        if(!isset(self::$instances[$className])) {
            $object = new $className();
            if($object instanceof ModuleInterface) {
                self::$instances[$className] = $object;
            }
            else {
                throw new ModuleInterfaceException("'$className' is no instance of ModuleInterface. Does it extend ModuleInterface?");
            }
        }
        return self::$instances[$className];
    }
}
?>