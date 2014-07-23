<?php
/**
 * Main controller for Net
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_net
 */

namespace Cx\Core\Net\Controller;

/**
 * Main controller for Net
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_net
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }

    public function preInit(\Cx\Core\Core\Controller\Cx $cx) {
        $config     = \Env::get('config');
        $domainRepo = new \Cx\Core\Net\Model\Repository\DomainRepository();
        $config['domainUrl'] = $domainRepo->getMainDomain()->getName();
    }
}