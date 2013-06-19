<?php

namespace Cx\Core_Modules\Workbench\Model\Entity;

class CopyCommand extends Command {
    protected $name = 'copy';
    protected $description = 'Copy components';
    protected $synopsis = 'workbench(.bat) copy [core|core_module|lib|module] {component_name} [core|core_module|lib|module] {new_component_name} ([customized|uncustomized])';
    protected $help = 'Copies specified component to the location specified.';
    
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
        
        if (!$this->interface->yesNo('This comes without any warranty. Are your sure you want to copy the component?')) {
            return;
        }
        
        $oldComponent->copy($newComponent->getName(), $newComponent->getType(), $toBeCustomized);
        
        $this->interface->show('Done');
    }
}
