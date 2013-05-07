<?php

namespace Cx\Core_Modules\Workbench\Model\Entity;

class DeactivateCommand extends Command {
    protected $name = 'deactivate';
    protected $description = 'Deactivates a component (core, core_module, lib, module)';
    protected $synopsis = 'workbench(.bat) deactivate [core|core_module|lib|module] {component_name}';
    protected $help = 'Deactivates a component of the specified type named {component_name}.';
    
    public function execute(array $arguments) {
        $isCore = $arguments[2] == 'core' || $arguments[2] == 'core_module';
        $isRequired = $arguments[2] == 'core';
        
        $query = '
            DELETE FROM
                `' . DBPREFIX . 'modules`
            WHERE
                `is_required` = ' . (int) $isRequired . ' AND
                `is_core` = ' . (int) $isCore . ' AND
                `name` = \'' . $arguments[3] . '\'
        ';
        if (!$this->interface->getDb()->getAdoDb()->Execute($query)) {
            throw new CommandException('Deactivating component failed');
        }
        
        $this->interface->show('TODO: Remove content pages for this component');
        
        $this->interface->show('TODO: Remove backend navigation entries for this component');
        
        $this->interface->show('Done');
    }
}
