<?php

/**
 * Class ComponentController
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_update
 */

namespace Cx\Core_Modules\Update\Controller;

/**
 * Class ComponentController
 *
 * The main Update component
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_update
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    
    /**
     * Get the controller classes
     * 
     * @return array array of the controller classes.
     */
    public function getControllerClasses() {
        return array('Update');
    }

    /**
     * postInit
     * 
     * @param \Cx\Core\Core\Controller\Cx $cx
     * 
     * @return null
     */
    public function postInit(\Cx\Core\Core\Controller\Cx $cx) {

        \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'config', 'FileSystem');
        if (\Cx\Core\Setting\Controller\Setting::getValue('mode', 'MultiSite') != \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE) {
            return;
        }

        $updateFile = \Env::get('cx')->getWebsiteTempPath() . '/Update/PendingDbUpdates.yml';
        if (!file_exists($updateFile)) {
            return;
        }
        
        //To initialize the variable \Cx\Core_Modules\MultiSite\Controller\ComponentController::cxMainDomain
        $componentRepo = \Env::get('em')->getRepository('Cx\Core\Core\Model\Entity\SystemComponent');
        $component = $componentRepo->findOneBy(array('name' => 'MultiSite'));
        if (!$component) {
            return;
        }
        $componentController = $component->getSystemComponentController();
        $componentController->setCustomerPanelDomainAsMainDomain();
                    
        $updateController = $this->getController('Update');
        $updateController->applyDelta();
    }

}
