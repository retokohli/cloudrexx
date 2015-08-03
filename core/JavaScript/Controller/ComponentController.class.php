<?php
/**
 * Main controller for JavaScript
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_javascript
 */

namespace Cx\Core\JavaScript\Controller;

/**
 * Main controller for JavaScript
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_javascript
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
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                // Load the JS helper class and set the offset
                \JS::setOffset('../');
                \JS::activate('backend');
                \JS::activate('cx');
                \JS::activate('chosen');
                break;
        }
    }
}