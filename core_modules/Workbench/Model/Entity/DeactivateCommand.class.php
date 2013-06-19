<?php

namespace Cx\Core_Modules\Workbench\Model\Entity;

class DeactivateCommand extends Command {
    protected $name = 'deactivate';
    protected $description = 'Deactivates a component (core, core_module, lib, module)';
    protected $synopsis = 'workbench(.bat) deactivate [core|core_module|lib|module] {component_name}';
    protected $help = 'Deactivates a component of the specified type named {component_name}.';
    
    public function execute(array $arguments) {
        
        $component = new \Cx\Core\Core\Model\Entity\ReflectionComponent($arguments[3], $arguments[2]);
        $component->deactivate();
        
        $this->interface->show('Done');
    }
}
