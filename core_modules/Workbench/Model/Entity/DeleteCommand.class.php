<?php

namespace Cx\Core_Modules\Workbench\Model\Entity;

class DeleteCommand extends Command {
    protected $name = 'delete';
    protected $description = 'Deletes a component (core, core_module, lib, module)';
    protected $synopsis = 'workbench(.bat) delete [core|core_module|lib|module] {component_name}';
    protected $help = 'Deactivates and deletes the component with the specified type named {component_name}.';
    
    /**
     * @todo Deactivate component before delete
     * @todo Check if component exists
     * @todo Set type and name
     * @todo Remove DB entry for component
     * @param array $arguments
     * @return type 
     */
    public function execute(array $arguments) {
        if (!$this->interface->yesNo('Do you really want to irrevocably delete?')) {
            $this->interface->show('Nothing to do then...');
            return;
        }
        $type = $arguments[2];
        $name = $arguments[3];
        $this->deleteComponent($type, $this->escapeSystemName(string($name)));
        $this->interface->show('Component deleted...');
    }
    
    /**
     * @todo Make sure component exists
     * @param type $type
     * @param type $systemName
     * @throws CommandException 
     */
    public function deleteComponent($type, $systemName) {
        $webPath = '';
        $path = '';
        switch ($type) {
            case 'core':
                $webPath = ASCMS_PATH_OFFSET.ASCMS_CORE_FOLDER;
                $path = ASCMS_CORE_PATH;
                break;
            
            case 'core_module':
                $webPath = ASCMS_PATH_OFFSET.ASCMS_CORE_MODULE_FOLDER;
                $path = ASCMS_CORE_MODULE_PATH;
                break;
            
            case 'lib':
                $webPath = ASCMS_PATH_OFFSET.ASCMS_LIBRARY_FOLDER;
                $path = ASCMS_LIBRARY_PATH;
                break;
            
            case 'module':
                $webPath = ASCMS_PATH_OFFSET.ASCMS_MODULE_FOLDER;
                $path = ASCMS_MODULE_PATH;
                break;
            
            default:
                throw new CommandException('No such component type: "' . $type . '"');
                break;
        }
        $fs = new \Cx\Lib\FileSystem\FileSystem();
        $fs->delDir($path.'/', $webPath, $systemName);
    }
    
    /**
     * @todo Escape name
     * @param \String $name
     * @return type 
     */
    public function escapeSystemName(\String &$name) {
        $systemName = string($name);
        
        return $systemName;
    }
}
