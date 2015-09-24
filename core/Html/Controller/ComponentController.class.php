<?php
/**
 * Main controller for Html
 * 
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_html
 */

namespace Cx\Core\Html\Controller;

/**
 * This class is used as controller for core html. It is also a SystemComponentController
 * Its used to handle json request to ViewGenerator and FormGenerator
 * 
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_html
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    /**
     * Returns all Controller class names for this component (except this)
     * 
     * @return array List of Controller class names (without namespace)
     */
    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array('JsonHtml', 'ViewGeneratorJson');
    }

    /**
     * Returns a list of JsonAdapter class names
     * 
     * @return array List of ComponentController classes
     */
    public function getControllersAccessableByJson() {
        return array('JsonHtmlController', 'ViewGeneratorJsonController');
    }
}
