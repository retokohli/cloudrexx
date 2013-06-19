<?php

namespace Cx\Core_Modules\Workbench\Model\Entity;

class DeleteCommand extends Command {
    protected $name = 'delete';
    protected $description = 'Deletes a component (core, core_module, lib, module)';
    protected $synopsis = 'workbench(.bat) delete [core|core_module|lib|module] {component_name}';
    protected $help = 'Deactivates and deletes the component with the specified type named {component_name}.';
    
    /**
     * @param array $arguments
     * @return type 
     */
    public function execute(array $arguments) {
        if (!$this->interface->yesNo('Do you really want to irrevocably delete ' . $arguments[2] . ' "' . $arguments[3] . '"?')) {
            return;
        }
        
        $component = new \Cx\Core\Core\Model\Entity\ReflectionComponent($arguments[3], $arguments[2]);
        $component->remove();
        
        $this->interface->show('Done');
    }
}
