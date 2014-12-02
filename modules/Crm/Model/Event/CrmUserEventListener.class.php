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
        $this->isValidToDeleteUser($eventArgs);
    }

    /**
     * 
     * @param array $eventArgs
     */
    public function postRemove($eventArgs) {
        $this->isValidToDeleteUser($eventArgs);
    }
    
    /**
     * If the CRM user have a order, Throw a ShinyException
     * 
     * @param array $eventArgs
     * @throws \Cx\Core\Error\Model\Entity\ShinyException
     */
    public function isValidToDeleteUser($eventArgs) {
        global $_ARRAYLANG;
        
        $em = $eventArgs[0]->getEntityManager();
        $orderRepo = $em->getRepository('\Cx\Modules\Order\Model\Entity\Order');
        if ($orderRepo->hasOrderByCrmId($eventArgs[1])) {
            throw new \Cx\Core\Error\Model\Entity\ShinyException($_ARRAYLANG['TXT_MODULE_CRM_DELETE_USER_ERROR_MSG']);
        }
    }

    public function onEvent($eventName, array $eventArgs) {
        if ($eventName == 'preRemove' || $eventName == 'postRemove') {
            $this->$eventName($eventArgs);
            return;
        }
        $this->$eventName(current($eventArgs));
    }
}
