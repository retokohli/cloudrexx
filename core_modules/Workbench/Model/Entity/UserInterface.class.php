<?php

namespace Cx\Core_Modules\Workbench\Model;

abstract class UserInterface {
    private $commands = array();
    
    public function __construct() {
        \Env::get('cl')->loadFile(ASCMS_CORE_PATH.'/Typing/Model/Entity/AutoBoxedObject.class.php');
        \Env::get('cl')->loadFile(ASCMS_CORE_PATH.'/Typing/Model/Entity/Primitives.class.php');
        $this->commands = array(
            'create' => new CreateCommand($this), // create new component
            'delete' => new DeleteCommand($this), // delete a component
            //'activate' => new ActivateCommand(), // activate a component
            //'deactivate' => new DeactivateCommand(), // deactivate a component
            //'export' => new ExportCommand(), // export contrexx files without workbench
            //'remove' => new RemoveCommand(), // remove workbench from installation
            //'update' => new UpdateCommand(), // port a component to this version of contrexx
            //'convert' => new ConvertCommand(), // convert component types (core to core_module, etc.)
            //'publish' => new PublishCommand(), // publish component to contrexx app repo (after successful unit testing)
            //'test' => new TestCommand(), // run UnitTests
            //'db' => new DbCommmand(), // wrapper for doctrine commandline tools
        );
    }
    
    public function commandExists($commandName) {
        return isset($this->commands[$commandName]);
    }

    public function getCommand($commandName) {
        if (!$this->commandExists($commandName)) {
            return null;
        }
        return $this->commands[$commandName];
    }
    
    public function getCommands() {
        return $this->commands;
    }
    
    public abstract function yesNo($question);
    
    public abstract function show($message);
}
