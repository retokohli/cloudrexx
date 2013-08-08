<?php
/**
 * This is the superclass for all Controller classes
 * 
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core_core
 * @version     3.1.0
 */
namespace Cx\Core\Core\Model\Entity;

/**
 * This is the superclass for all Controller classes
 * 
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core_core
 * @version     3.1.0
 */
abstract class Controller {
    
    /**
     * Main class instance
     * @var \Cx\Core\Core\Controller\Cx
     */
    protected $cx = null;
    
    /**
     * SystemComponentController for this Component
     * @var \Cx\Core\Core\Model\Entity\SystemComponentController
     */
    private $systemComponentController = null;
    
    /**
     * Creates new controller
     * @param SystemComponentController $systemComponentController Main controller for this system component
     * @param \Cx\Core\Core\Controller\Cx $cx Main class instance
     */
    public function __construct(SystemComponentController $systemComponentController, \Cx\Core\Core\Controller\Cx $cx) {
        $this->cx = $cx;
        $this->systemComponentController = $systemComponentController;
        $this->systemComponentController->registerController($this);
    }
    
    /**
     * Returns the main controller
     * @return SystemComponentController Main controller for this system component
     */
    public function getSystemComponentController() {
        return $this->systemComponentController;
    }
    
    /**
     * Route methods like getName(), getType(), getDirectory(), etc.
     * @param string $methodName Name of method to call
     * @param array $arguments List of arguments for the method to call
     * @return mixed Return value of the method to call
     */
    public function __call($methodName, $arguments) {
        return call_user_func_array(array($this->systemComponentController, $methodName), $arguments);
    }
}
