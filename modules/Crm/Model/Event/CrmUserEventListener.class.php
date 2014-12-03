<?php

/**
 * CrmUserEventListener

 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_crm
 */

namespace Cx\Modules\Crm\Model\Event;

/**
 * CrmUserEventListenerException
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_crm
 */
class CrmUserEventListenerException extends \Exception {}

/**
 * CrmUserEventListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_crm
 */
class CrmUserEventListener implements \Cx\Core\Event\Model\Entity\EventListener {
    
    /**
     * prePersist event
     * 
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $eventArgs
     */
    public function prePersist($eventArgs) {}
    
    /**
     * postPersist event
     * 
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $eventArgs
     */
    public function postPersist($eventArgs) {}
    
    /**
     * preUpdate event
     * 
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $eventArgs
     */
    public function preUpdate($eventArgs) {}
    
    /**
     * postUpdate event
     * 
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $eventArgs
     */
    public function postUpdate($eventArgs) {}

    /**
     * 
     * @param array $eventArgs
     */
    public function preRemove($eventArgs) {
        global $_ARRAYLANG;
        
        $em = $eventArgs->getEntityManager();
        $crmEntity = $eventArgs->getEntity();
        $orderRepo = $em->getRepository('\Cx\Modules\Order\Model\Entity\Order');
        if ($orderRepo->hasOrderByCrmId($crmEntity->id)) {
            throw new \Cx\Core\Error\Model\Entity\ShinyException($_ARRAYLANG['TXT_MODULE_CRM_DELETE_USER_ERROR_MSG']);
        }
    }

    /**
     * 
     * @param array $eventArgs
     */
    public function postRemove($eventArgs) {}
    
    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }
}
