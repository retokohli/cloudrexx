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
    public function postInit(\Cx\Core\Core\Controller\Cx $cx)
    {
        $componentController = $this->getComponent('MultiSite');
        if (!$componentController) {
            return;
        }
        
        \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'config', 'FileSystem');
        if (\Cx\Core\Setting\Controller\Setting::getValue('mode', 'MultiSite') != \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE) {
            return;
        }

        $updateFile = $cx->getWebsiteTempPath() . '/Update/' . \Cx\Core_Modules\Update\Model\Repository\DeltaRepository::PENDING_DB_UPDATES_YML;
        if (!file_exists($updateFile)) {
            return;
        }

        $componentController->setCustomerPanelDomainAsMainDomain();

        $updateController = $this->getController('Update');
        $updateController->applyDelta();
    }

}
