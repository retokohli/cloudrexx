<?php
/**
 * Command to pack components into a zip file
 * @author Michael Ritter <michael.ritter@comvation.com>
 */

namespace Cx\Core_Modules\Workbench\Model\Entity;

/**
 * Command to pack components into a zip file
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
class ExportCommand extends Command {
    
    /**
     * Command name
     * @var string
     */
    protected $name = 'export';
    
    /**
     * Command description
     * @var string
     */
    protected $description = 'Export a component (core, core_module, lib, module, template, etc.)';
    
    /**
     * Command synopsis
     * @var string
     */
    protected $synopsis = 'workbench(.bat) export [core|core_module|lib|module] {component_name} ([customized|uncustomized]) {path to zip package}';
    
    /**
     * Command help text
     * @var string
     */
    protected $help = 'Packs a component to a zip package at the specified location.';
    
    /**
     * Execute this command
     * @param array $arguments Array of commandline arguments
     */
    public function execute(array $arguments) {        
        $comp = new \Cx\Core\Core\Model\Entity\ReflectionComponent($arguments[3], $arguments[2]);
        $customized = false;
        if (isset($arguments[5])) {
            if ($arguments[5] == 'customized') {
                $customized = true;
            }
            $arguments[4] = $arguments[5];
        }
        $zipPath = $arguments[4];
        
        if (file_exists($zipPath)) {
            if (!$this->interface->yesNo('File is already exists. Do you want to overwrite it?')) {
                return;
            }
        }
        
        $comp->pack($zipPath, $customized);
        
        $this->interface->show('Component exported successfully.');
    }
}
