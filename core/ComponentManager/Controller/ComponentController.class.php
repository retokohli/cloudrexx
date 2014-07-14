<?php
/**
 * Main controller for Component Manager
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package contrexx
 * @subpackage core_componentmanager
 */

namespace Cx\Core\ComponentManager\Controller;

/**
 * Main controller for Component Manager
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package contrexx
 * @subpackage core_componentmanager
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    /**
     * getControllerClasses
     * 
     * @return type
     */
    public function getControllerClasses() {
        return array();
    }

     /**
     * Load the component Component Manager.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $_ARRAYLANG, $subMenuTitle;
        $subMenuTitle = $_ARRAYLANG['TXT_MODULE_MANAGER'];
        
        \Permission::checkAccess(23, 'static');
        $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'content_master.html');
        $cachedRoot = $this->cx->getTemplate()->getRoot();
        $this->cx->getTemplate()->setRoot($this->getDirectory() . '/View/Template/Backend');
        
        $objComponentManager = new \Cx\Core\ComponentManager\Controller\ComponentManager();
        $objComponentManager->getModulesPage();  
        
        $this->cx->getTemplate()->setRoot($cachedRoot); 
    }

}