<?php
/**
* Main controller for LinkManager
*
* @copyright   Comvation AG
* @author      Project Team SS4U <info@comvation.com>
* @package     contrexx
* @subpackage  coremodule_linkmanager
*/

namespace Cx\Core_Modules\LinkManager\Controller;

/**
* Main controller for LinkManager
*
* @copyright   Comvation AG
* @author      Project Team SS4U <info@comvation.com>
* @package     contrexx
* @subpackage  coremodule_linkmanager
*/
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    
    public function getControllerClasses() {
        return array('Backend');
    }
    
    /**
     * Returns a list of JsonAdapter class names
     * 
     * The array values might be a class name without namespace. In that case
     * the namespace \Cx\{component_type}\{component_name}\Controller is used.
     * If the array value starts with a backslash, no namespace is added.
     * 
     * Avoid calculation of anything, just return an array!
     * @return array List of ComponentController classes
     */
    public function getControllersAccessableByJson(){
        return array('JsonLink');
    }
}