<?php

namespace Cx\Core_Modules\Workbench\Model\Entity;

class TreeNavCommand extends NavCommand {
    protected $name = 'treenav';
    protected $description = 'Shows the backend navigation as a tree';
    protected $synopsis = 'workbench(.bat) navtree';
    protected $help = 'Shows the backend navigation as a tree';
    
    public function execute(array $arguments) {
        $this->interface->tree($this->getEntries(), 'area_name');
    }
}
