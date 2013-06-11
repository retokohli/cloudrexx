<?php
/**
 * This is the superclass for all Controller classes
 * 
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
namespace Cx\Core\Component\Model\Entity;

/**
 * This is the superclass for all Controller classes
 * 
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
abstract class Controller {
    
    /**
     * SystemComponentController for this Component
     * @var \Cx\Core\Component\Model\Entity\SystemComponentController
     */
    private $systemComponentController = null;
    
    /**
     * Creates new controller
     * @param SystemComponentController $systemComponentController Main controller for this system component
     */
    public function __construct(SystemComponentController $systemComponentController) {
        $this->systemComponentController = $systemComponentController;
        $this->systemComponentController->registerController();
    }
    
    /**
     * Returns the main controller
     * @return SystemComponentController Main controller for this system component
     */
    public function getSystemComponentController() {
        return $this->systemComponentController;
    }
}
