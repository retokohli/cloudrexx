<?php

namespace Cx\Core_Modules\Workbench\Model\Entity;

class MoveCommand extends Command {
    protected $name = 'move';
    protected $description = 'Convert component types (core to core_module, etc.) and rename components';
    protected $synopsis = 'workbench(.bat) move [core|core_module|lib|module] {component_name} [core|core_module|lib|module] {new_component_name} ([customized|uncustomized])';
    protected $help = 'Moves specified component to the location specified.';
    
    public function execute(array $arguments) {
        $oldComponent = new \Cx\Core\Core\Model\Entity\ReflectionComponent($arguments[3], $arguments[2]);
        $newComponent = new \Cx\Core\Core\Model\Entity\ReflectionComponent($arguments[5], $arguments[4]);
        
        if ($oldComponent == $newComponent) {
            $this->interface->show('Nothing to do');
            return;
        }
        
        if (!$oldComponent->exists()) {
            throw new CommandException('No such component "' . $oldComponent->getName() . '" of type ' . $oldComponent->getType());
        }
        
        $toBeCustomized = false;
        if (isset($arguments[6])) {
            $toBeCustomized = $arguments[6] == 'customized';
        }
        
        if (!$this->interface->yesNo('This comes without any warranty. Are your sure you want to move the component?')) {
            return;
        }
        
        $oldComponent->move($newComponent->getName(), $newComponent->getType(), $toBeCustomized);
        
        $this->interface->show('Done');
    }
}
