<?php

namespace Cx\Core_Modules\Workbench\Model\Entity;

class CreateCommand extends Command {
    protected $name = 'create';
    protected $description = 'Creates a new component (core, core_module, lib, module)';
    protected $synopsis = 'workbench(.bat) create [core|core_module|lib|module] {component_name}';
    protected $help = 'Creates and activates a new component of the specified type named {component_name}.';
    
    /**
     * @todo Activate newly created component
     * @param array $arguments 
     */
    public function execute(array $arguments) {
        $this->createComponent(string($arguments[2]), string($arguments[3]));
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
    
    /**
     * @todo Use FileSystem
     * @param \String $type
     * @param \String $name
     * @return type
     * @throws CommandException 
     */
    public function createComponent(\String &$type, \String &$name) {
        $systemName = $this->escapeSystemName($name);
        switch ($type) {
            case 'core':
                $base = ASCMS_CORE_PATH;
                break;
            
            case 'core_module':
                $base = ASCMS_CORE_MODULE_PATH;
                break;
            
            case 'module':
                $base = ASCMS_MODULE_PATH;
                break;
            
            case 'lib':
                \Cx\Lib\FileSystem\FileSystem::make_folder(ASCMS_LIBRARY_PATH.'/'.$systemName);
                $this->interface->show('done');
                return;
                break;
            
            default:
                throw new CommandException('No such component type: "' . $type . '"');
                break;
        }
        $this->createNonLibComponent(string($base . '/' . $systemName));
        $this->interface->show('done');
    }
    
    /**
     * @todo Use FileSystem
     * @todo Add component.class.php
     * @todo Add some sample files (.yml, tests, etc.)
     * @todo Add DB entry
     * @param \String $folder 
     */
    private function createNonLibComponent(\String &$folder) {
        $folder = string($folder . '/');
        \Cx\Lib\FileSystem\FileSystem::make_folder($folder);
        \Cx\Lib\FileSystem\FileSystem::make_folder($folder.'Controller');
        \Cx\Lib\FileSystem\FileSystem::make_folder($folder.'Meta');
        \Cx\Lib\FileSystem\FileSystem::make_folder($folder.'Model');
        \Cx\Lib\FileSystem\FileSystem::make_folder($folder.'Model/Data');
        \Cx\Lib\FileSystem\FileSystem::make_folder($folder.'Model/Entity');
        \Cx\Lib\FileSystem\FileSystem::make_folder($folder.'Model/Event');
        \Cx\Lib\FileSystem\FileSystem::make_folder($folder.'Model/Repository');
        \Cx\Lib\FileSystem\FileSystem::make_folder($folder.'Model/Yaml');
        \Cx\Lib\FileSystem\FileSystem::make_folder($folder.'Test');
        \Cx\Lib\FileSystem\FileSystem::make_folder($folder.'View');
        \Cx\Lib\FileSystem\FileSystem::make_folder($folder.'View/Media');
        \Cx\Lib\FileSystem\FileSystem::make_folder($folder.'View/Script');
        \Cx\Lib\FileSystem\FileSystem::make_folder($folder.'View/Style');
        \Cx\Lib\FileSystem\FileSystem::make_folder($folder.'View/Template');
    }
}
