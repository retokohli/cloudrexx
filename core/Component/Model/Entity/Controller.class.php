<?php
/**
 * This is the superclass for all Controller classes
 * Decorator for SystemComponent
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
namespace Cx\Core\Component\Model\Entity;

/**
 * This is the superclass for all Controller classes
 * Decorator for SystemComponent
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
abstract class Controller {
    /**
     * @var Cx\Core\Component\Model\Entity\SystemComponent
     */
    protected $systemComponent;
    
    /**
     * Initializes a controller
     * @param \Cx\Core\Component\Model\Entity\SystemComponent $systemComponent SystemComponent to decorate
     */
    public function __construct(\Cx\Core\Component\Model\Entity\SystemComponent $systemComponent) {
        $this->systemComponent = $systemComponent;
    }
    
    /**
     * Returns the SystemComponent this Controller decorates
     * @return \Cx\Core\Component\Model\Entity\SystemComponent
     */
    public function getSystemComponent() {
        return $this->systemComponent;
    }
    
    /**
     * Decoration: all methods that are not specified in this or child classes
     * call the corresponding method of the decorated SystemComponent
     * @param string $methodName Name of method to call
     * @param array $arguments List of arguments for the method to call
     * @return mixed Return value of the method to call
     */
    public function __call($methodName, $arguments) {
        return call_user_func(array($this->systemComponent, $methodName), $arguments);
    }
}
