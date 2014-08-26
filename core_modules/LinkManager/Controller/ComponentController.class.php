<?php
/**
* Main controller for LinkManager
*
* @copyright   Comvation AG
* @author      Project Team SS4U <info@comvation.com>
* @package     contrexx
* @subpackage  module_linkmanager
*/

namespace Cx\Core_Modules\LinkManager\Controller;

/**
* Main controller for LinkManager
*
* @copyright   Comvation AG
* @author      Project Team SS4U <info@comvation.com>
* @package     contrexx
* @subpackage  module_linkmanager
*/
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    
    public function getControllerClasses() {
        return array('Backend');
    }
}