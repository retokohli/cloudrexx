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
        
        $component = new \Cx\Core\Core\Model\Entity\ReflectionComponent($arguments[3], $arguments[2]);
        $component->create();
        
        $this->interface->show('Done');
    }
}
