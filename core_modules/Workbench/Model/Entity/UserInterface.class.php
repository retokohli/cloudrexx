<?php

namespace Cx\Core_Modules\Workbench\Model\Entity;

abstract class UserInterface {
    protected $cx = null;
    private $commands = array();
    private $workbench = null;
    protected $silent = false;
    
    public function __construct($cx) {
        $this->cx = $cx;
        \Env::get('ClassLoader')->loadFile(ASCMS_CORE_PATH.'/Typing/Model/Entity/AutoBoxedObject.class.php');
        \Env::get('ClassLoader')->loadFile(ASCMS_CORE_PATH.'/Typing/Model/Entity/Primitives.class.php');
        $this->commands = array(
            /* FINISHED COMMANDS */
            'db' => new DbCommand($this), // wrapper for doctrine commandline tools
            
            /* BETA COMMANDS */
            'create' => new CreateCommand($this), // create new component
            'delete' => new DeleteCommand($this), // delete a component
            'activate' => new ActivateCommand($this), // activate a component
            'deactivate' => new DeactivateCommand($this), // deactivate a component
            'move' => new MoveCommand($this), // convert component types (core to core_module, etc.) and rename components
            'copy' => new CopyCommand($this), // copy components
            //'remove' => new RemoveCommand($this), // remove workbench from installation
            //'test' => new TestCommand($this), // run UnitTests
            
            /* FUTURE COMMANDS */
            //'treenav' => new TreeNavCommand($this), // recursive tree view of backend navigation
            //'addnav' => new AddNavCommand($this), // add a backend navigation entry
            //'rmnav' => new RmNavCommand($this), // remove a backend navigation entry
            //'mvnav' => new MvNavCommand($this), // move a backend navigation entry (remove and add new)
            //'export' => new ExportCommand($this), // export contrexx files without workbench
            //'publish' => new PublishCommand($this), // publish component to contrexx app repo (after successful unit testing)
            //'install' => new InstallCommand($this), // install a component from contrexx app repo
            //'update' => new UpdateCommand($this), // port a component to this version of contrexx
            //'upgrade' => new UpgradeCommand($this), // upgrade a component to current or current beta version
            //'push' => new PushCommand($this), // Pushes this installation to a FTP server
            //'pack' => new PackCommand($this), // Create install/update package of current installation
        );
    }
    
    public function commandExists($commandName) {
        return isset($this->commands[$commandName]);
    }

    /**
     *
     * @param type $commandName
     * @return Command 
     */
    public function getCommand($commandName) {
        if (!$this->commandExists($commandName)) {
            return null;
        }
        return $this->commands[$commandName];
    }
    
    public function executeCommand($commandName, $arguments, $silent = false) {
        $cachedSilence = $this->silent;
        $this->silent = $silent;
        $command = $this->getCommand($commandName);
        if (!$command) {
            return false;
        }
        $ret = $command->execute($arguments);
        $this->silent = $cachedSilence;
        return $ret;
    }
    
    public function getCommands() {
        return $this->commands;
    }
    
    /**
     *
     * @return \Cx\Core_Modules\Workbench\Controller\Workbench
     */
    public function getWorkbench() {
        if (!$this->workbench) {
            $this->workbench = new \Cx\Core_Modules\Workbench\Controller\Workbench();
        }
        return $this->workbench;
    }
    
    public function getConfigVar($name) {
        return $this->getWorkbench()->getConfigEntry($name);
    }
    
    public function setConfigVar($name, $value) {
        return $this->getWorkbench()->setConfigEntry($name, $value);
    }
    
    /**
     * @return \Cx\Core\Db\Db
     */
    public abstract function getDb();
    
    public abstract function input($description, $defaultValue = '');
    
    public abstract function yesNo($question);
    
    public abstract function show($message);
    
    /**
     * Accepts an array in the form array({something}=>{title}, 'children'=>{recursion})
     */
    public abstract function tree(array $tree, $displayindex = 0);
}
