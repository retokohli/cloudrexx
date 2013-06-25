<?php
/**
 * Command to move components
 * @author Michael Ritter <michael.ritter@comvation.com>
 */

namespace Cx\Core_Modules\Workbench\Model\Entity;

/**
 * Command to move components
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
class MoveCommand extends Command {
    
    /**
     * Command name
     * @var string
     */
    protected $name = 'move';
    
    /**
     * Command description
     * @var string
     */
    protected $description = 'Convert component types (core to core_module, etc.) and rename components';
    
    /**
     * Command synopsis
     * @var string
     */
    protected $synopsis = 'workbench(.bat) move [core|core_module|lib|module] {component_name} [core|core_module|lib|module] {new_component_name} ([customized|uncustomized])';
    
    /**
     * Command help text
     * @var string
     */
    protected $help = 'Moves specified component to the location specified.';
    
    /**
     * Execute this command
     * @param array $arguments Array of commandline arguments
     */
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
