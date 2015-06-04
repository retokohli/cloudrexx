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
    /*
     * Constructor
     */

    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponent $systemComponent, \Cx\Core\Core\Controller\Cx $cx) {
        parent::__construct($systemComponent, $cx);
    }

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
     * @return type
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
        
        $updateController = $this->getController('Update');
        $updateController->applyDelta();
    }

}
