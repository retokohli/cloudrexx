<?php
/**
 * Main controller for Crm
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_crm
 */

namespace Cx\Modules\Crm\Controller;

/**
 * Main controller for Crm
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_crm
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array('JsonCrm');
    }

     /**
     * Load your component.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $objTemplate, $_CORELANG, $subMenuTitle;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
                $objTemplate = $this->cx->getTemplate();
                
                \Permission::checkAccess(556, 'static');
                $subMenuTitle = $_CORELANG['TXT_CRM'];
                $objCrmModule = new CrmManager($this->getName());
                $objCrmModule->getPage();
                break;
        }
    }
        
    public function postResolve(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        $evm = \Env::get('cx')->getEvents();
        
        $userEventListener    = new \Cx\Modules\Crm\Model\Event\UserEventListener();
        $evm->addModelListener(\Doctrine\ORM\Events::postUpdate, 'User', $userEventListener);
    }
    
}
