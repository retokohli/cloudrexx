<?php

namespace Cx\Core_Modules\Workbench\Model\Entity;

class CommandException extends \Cx\Core_Modules\Workbench\Controller\WorkbenchException {};

abstract class Command {
    protected $interface;
    protected $name;
    protected $description;
    protected $synopsis;
    protected $help;
    
    public function __construct(UserInterface $owner) {
        $this->interface = $owner;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
    public function getSynopsis() {
        return $this->synopsis;
    }
    
    public function getHelp() {
        return $this->help;
    }
    
    public abstract function execute(array $arguments);
}
