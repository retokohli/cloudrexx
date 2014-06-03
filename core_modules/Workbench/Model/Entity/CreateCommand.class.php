<?php
/**
 * Command to create components
 * @author Michael Ritter <michael.ritter@comvation.com>
 */

namespace Cx\Core_Modules\Workbench\Model\Entity;

/**
 * Command to create components
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
class CreateCommand extends Command {
    
    /**
     * Command name
     * @var string
     */
    protected $name = 'create';
    
    /**
     * Command description
     * @var string
     */
    protected $description = 'Creates a new component (core, core_module, lib, module)';
    
    /**
     * Command synopsis
     * @var string
     */
    protected $synopsis = 'workbench(.bat) create [core|core_module|lib|module] {component_name}';
    
    /**
     * Command help text
     * @var string
     */
    protected $help = 'Creates and activates a new component of the specified type named {component_name}.';
    
    /**
     * Execute this command
     * @param array $arguments Array of commandline arguments
     */
    public function execute(array $arguments) {
        
        $component = new \Cx\Core\Core\Model\Entity\ReflectionComponent($arguments[3], $arguments[2]);
        $component->create();
        
        $this->interface->show('Done');
    }
}
