<?php

namespace Cx\Core_Modules\Workbench\Model\Entity;

class MoveCommand extends Command {
    protected $name = 'move';
    protected $description = 'Convert component types (core to core_module, etc.) and rename components';
    protected $synopsis = 'workbench(.bat) move [core|core_module|lib|module] {component_name} [core|core_module|lib|module] {new_component_name} ([customized|uncustomized])';
    protected $help = 'Moves specified component to the location specified.';
    
    public function execute(array $arguments) {
        $oldComponent = array($arguments[2], $arguments[3]);
        $newComponent = array($arguments[4], $arguments[5]);
        
        if ($oldComponent == $newComponent) {
            $this->interface->show('Nothing to do');
            return;
        }
        
        if (!is_dir($this->getComponentDirectory($oldComponent))) {
            throw new CommandException('No such component "' . $oldComponent[1] . '" of type ' . $oldComponent[0]);
        }
        
        $wasCustomized = $this->isCustomized($oldComponent);
        
        // 1st step: change type and name
        $this->changeTypeAndName($oldComponent, $newComponent);
        
        // 2nd step: change customizing status (or re-set it correctly)
        if (!isset($arguments[6])) {
            $arguments[6] = ($wasCustomized ? 'customized' : 'uncustomized');
        }
        $this->changeCustomizingStatus($oldComponent, $arguments[6]);
        
        // 3th step: fix namespaces
        $this->fixNamespaces($newComponent);
        
        // 4th step: reactivate component
        $this->interface->executeCommand('deactivate', array(null, null, $arguments[2], $arguments[3]), true);
        $this->interface->executeCommand('activate', array(null, null, $arguments[4], $arguments[5]), true);
        
        $this->interface->show('TODO: Fix backend navigation entries');
        
        $this->interface->show('Done');
    }
    
    protected function changeCustomizingStatus(&$component, $target) {
        global $_CONFIG;
        
        if ($target != 'customized' && $target != 'uncustomized') {
            throw new CommandException('Customizing status can be "customized" or "uncustomized"');
        }
        
        $isCustomized = $this->isCustomized($component);
        if ($isCustomized == ($target == 'customized')) {
            // nothing to do
            return;
        }
        
        if ($isCustomized) {
            // uncustomize
            try {
                $objFile = new \Cx\Lib\FileSystem\File($this->getComponentDirectory($component));
                $objFile->move(ASCMS_DOCUMENT_ROOT.$this->getPathForType($component[0]) . '/' . $component[1]);
            } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
                \DBG::msg($e->getMessage());
            }
        } else {
            // customize
            if (!isset($_CONFIG['useCustomizings']) || $_CONFIG['useCustomizings'] != 'on') {
                if (!$this->interface->yesNo('Customizing is not activated. Are you sure you want to move this component to customizing?')) {
                    return;
                }
            }
            try {
                $objFile = new \Cx\Lib\FileSystem\File($this->getComponentDirectory($component));
                $objFile->move(ASCMS_CUSTOMIZING_PATH.$this->getPathForType($component[0]) . '/' . $component[1]);
            } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
                \DBG::msg($e->getMessage());
            }
        }
    }
    
    protected function changeTypeAndName($oldComponent, $newComponent) {
        if ($oldComponent[0] == $newComponent[0] && $oldComponent[1] == $newComponent[1]) {
            // nothing to do
            return;
        }
        
        // move to correct type and name directory
        try {
            $objFile = new \Cx\Lib\FileSystem\File($this->getComponentDirectory($oldComponent));
            $objFile->move($this->getComponentDirectory($newComponent, false));
        } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
            \DBG::msg($e->getMessage());
        }
    }
    
    protected function fixNamespaces($component) {
        // get base namespace for component
        $namespace = $this->getNamespaceForType($component[0]);
        
        $this->interface->show('TODO: Fix namespaces');
        // recursively loop over all *.php files and fix all namespaces
        while ($file) {
            
        }
    }
    
    /**
     * Returns the absolute path to this component's location in the file system
     * @param array $component Array like array(0=>{type},1=>{name})
     * @return string 
     */
    protected function getComponentDirectory($component, $allowCustomizing = true) {
        $basepath = ASCMS_DOCUMENT_ROOT.$this->getPathForType($component[0]);
        $componentPath = $basepath . '/' . $component[1];
        if (!$allowCustomizing) {
            return $componentPath;
        }
        return \Env::get('ClassLoader')->getFilePath($componentPath);
    }
    
    /**
     * Tells wheter this component is customized or not
     * @param array $component Array like array(0=>{type},1=>{name})
     * @return boolean 
     */
    protected function isCustomized($component) {
        $basepath = ASCMS_DOCUMENT_ROOT.$this->getPathForType($component[0]);
        $componentPath = $basepath . '/' . $component[1];
        return \Env::get('ClassLoader')->getFilePath($componentPath) != $componentPath;
    }
    
    /**
     * Returns the namespace for a component type
     * @param string $type Component type
     * @return string Namespace
     * @throws CommandException 
     */
    protected function getNamespaceForType($type) {
        switch ($type) {
            case 'core':
                return 'Cx\\Core';
                break;
            case 'core_module':
                return 'Cx\\Core_Modules';
                break;
            case 'module':
                return 'Cx\\Modules';
                break;
            default:
                throw new CommandException('No such component type "' . $type . '"');
                break;
        }
    }
    
    /**
     * Returns the type folder (relative to document root)
     * @param string $type Component type
     * @return string Componen type folder relative to document root
     * @throws CommandException 
     */
    protected function getPathForType($type) {
        switch ($type) {
            case 'core':
                return ASCMS_CORE_FOLDER;
                break;
            case 'core_module':
                return ASCMS_CORE_MODULE_FOLDER;
                break;
            case 'module':
                return ASCMS_MODULE_FOLDER;
                break;
            default:
                throw new CommandException('No such component type "' . $type . '"');
                break;
        }
    }
}
