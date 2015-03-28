<?php
/**
 * Main controller for Session
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_session
 */

namespace Cx\Core\Session\Controller;

/**
 * Main controller for Session
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_session
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }
      /**
     * Do something before resolving is done
     * 
     * @param \Cx\Core\Routing\Url                      $request    The URL object for this request
     */
    public function preResolve(\Cx\Core\Routing\Url $request) {
        global $sessionObj;

        if (\Cx\Core\Core\Controller\Cx::instanciate()->getMode() == \Cx\Core\Core\Controller\Cx::MODE_BACKEND) {
            if (empty($sessionObj)) $sessionObj = \cmsSession::getInstance();
            $_SESSION->cmsSessionStatusUpdate('backend');
        }
    }

}
