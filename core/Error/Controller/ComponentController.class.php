<?php
/**
 * Main controller for Error
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_error
 */

namespace Cx\Core\Error\Controller;

/**
 * Main controller for Error
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_error
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
   
    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }

     /**
     * Load your component.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
               $errorObj = new Error(\Env::get('cx')->getPage()->getContent());
               \Env::get('cx')->getPage()->setContent($errorObj->getErrorPage());
                break;
        }
    }
}