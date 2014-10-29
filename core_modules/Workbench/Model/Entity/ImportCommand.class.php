<?php
/**
 * Command to install components
 * @author Michael Ritter <michael.ritter@comvation.com>
 */

namespace Cx\Core_Modules\Workbench\Model\Entity;

/**
 * Command to install components
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
class ImportCommand extends Command {
    
    /**
     * Command name
     * @var string
     */
    protected $name = 'import';
    
    /**
     * Command description
     * @var string
     */
    protected $description = 'Installs a component (core, core_module, lib, module, template, etc.)';
    
    /**
     * Command synopsis
     * @var string
     */
    protected $synopsis = 'workbench(.bat) import {path to zip package}';
    
    /**
     * Command help text
     * @var string
     */
    protected $help = 'Installs a component using the specified zip package.';
    
    /**
     * Execute this command
     * @param array $arguments Array of commandline arguments
     */
    public function execute(array $arguments) {
        // The following tasks will be done by Apps:
            // Get package info
            // Recursively get all dependencies
            // Foreach package to install (sorted, the one without any dependencies first):
                // Download package
        \DBG::activate(DBG_PHP);
        $comp = new \Cx\Core\Core\Model\Entity\ReflectionComponent($arguments[2]);
        $comp->install();
    }
}
