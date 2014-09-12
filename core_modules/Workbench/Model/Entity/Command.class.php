<?php
/**
 * Base class for workbench commands
 * @author Michael Ritter <michael.ritter@comvation.com>
 */

namespace Cx\Core_Modules\Workbench\Model\Entity;

/**
 * Exceptions thrown by commands should be CommandExceptions
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
class CommandException extends \Cx\Core_Modules\Workbench\Controller\WorkbenchException {};

/**
 * Base class for workbench commands
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
abstract class Command {
    /**
     * Currently used user interface
     * @var UserInterface
     */
    protected $interface;
    
    /**
     * Command name
     * @var string
     */
    protected $name;
    
    /**
     * Command description
     * @var string
     */
    protected $description;
    
    /**
     * Command synopsis
     * @var string
     */
    protected $synopsis;
    
    /**
     * Command help text
     * @var string
     */
    protected $help;
    
    /**
     * Reference to Cx instance
     * @var \Cx\Core\Core\Controller\Cx
     */
    protected $cx;
    
    /**
     * Loads a command
     * @param UserInterface $owner 
     */
    public function __construct(UserInterface $owner) {
        $this->interface = $owner;
        $this->cx = \Cx\Core\Core\Controller\Cx::instanciate();
    }
    
    /**
     * Returns the name of this command
     * @return string Command name
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Returns the description for this command
     * @return string Command description
     */
    public function getDescription() {
        return $this->description;
    }
    
    /**
     * Returns the synopsis for this command
     * @return string Command synopsis
     */
    public function getSynopsis() {
        return $this->synopsis;
    }
    
    /**
     * Returns the help text for this command
     * @return string Command help text
     */
    public function getHelp() {
        return $this->help;
    }
    
    /**
     * Execute this command
     * @param array $arguments Array of commandline arguments
     */
    public abstract function execute(array $arguments);
}
