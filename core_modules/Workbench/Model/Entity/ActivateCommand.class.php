<?php

namespace Cx\Core_Modules\Workbench\Model\Entity;

class ActivateCommand extends Command {
    protected $name = 'activate';
    protected $description = 'Activates a component (core, core_module, lib, module)';
    protected $synopsis = 'workbench(.bat) activate [core|core_module|lib|module] {component_name}';
    protected $help = 'Activates a component of the specified type named {component_name}.';
    
    public function execute(array $arguments) {
        
        $component = new \Cx\Core\Core\Model\Entity\ReflectionComponent($arguments[3], $arguments[2]);
        $component->activate();
        
        $this->interface->show('Done');
    }
}
