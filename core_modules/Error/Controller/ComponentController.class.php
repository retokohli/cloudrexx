<?php
/**
 * Main controller for Error
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_error
 */

namespace Cx\Core_Modules\Error\Controller;

/**
 * Main controller for Error
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_error
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {

    public function getControllerClasses() {
        return array('Frontend');
    }

    /**
     * Register event-listener for Routing/PageNotFound-Event
     * @throws \Cx\Core\Event\Controller\EventManagerException
     */
    public function registerEventListeners() {
        $this->cx->getEvents()->addEventListener('Routing/PageNotFound', $this->getController('Frontend'));
    }
}