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
        global $_CONFIG;
        $domainRepo = new \Cx\Core\Net\Model\Repository\DomainRepository();
        $_CONFIG['domainUrl'] = $domainRepo->getMainDomain()->getName();
        \Env::set('config', $_CONFIG);
    }

    public function postResolve(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        $eventHandlerInstance = \Env::get('cx')->getEvents(); 
        $domainListener       = new \Cx\Core\Net\Model\Event\DomainEventListener();
        $eventHandlerInstance->addModelListener(\Doctrine\ORM\Events::prePersist, 'Cx\\Core\\Net\\Model\\Entity\\Domain', $domainListener);
        $eventHandlerInstance->addModelListener(\Doctrine\ORM\Events::postPersist, 'Cx\\Core\\Net\\Model\\Entity\\Domain', $domainListener);
        $eventHandlerInstance->addModelListener(\Doctrine\ORM\Events::preRemove, 'Cx\\Core\\Net\\Model\\Entity\\Domain', $domainListener);
        $eventHandlerInstance->addModelListener(\Doctrine\ORM\Events::postRemove, 'Cx\\Core\\Net\\Model\\Entity\\Domain', $domainListener);
    }
}
