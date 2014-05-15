<?php
/**
 * Command to delete components
 * @author Michael Ritter <michael.ritter@comvation.com>
 */

namespace Cx\Core_Modules\Workbench\Model\Entity;

/**
 * Command to delete components
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
class UninstallCommand extends Command {
    
    /**
     * Command name
     * @var string
     */
    protected $name = 'uninstall';
    
    /**
     * Command description
     * @var string
     */
    protected $description = 'Uninstall a component (core, core_module, lib, module)';
    
    /**
     * Command synopsis
     * @var string
     */
    protected $synopsis = 'workbench(.bat) uninstall [core|core_module|lib|module] {component_name}';
    
    /**
     * Command help text
     * @var string
     */
    protected $help = 'Deactivates and deletes the component with the specified type named {component_name}.';
    
    /**
     * Execute this command
     * @param array $arguments Array of commandline arguments
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
