<?php

/**
 * CrmCrmContactEventListener

 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */

namespace Cx\Modules\Order\Model\Event;

/**
 * CrmCrmContactEventListenerException
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */
class CrmCrmContactEventListenerException extends \Exception {}

/**
 * CrmCrmContactEventListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */
class CrmCrmContactEventListener implements \Cx\Core\Event\Model\Entity\EventListener {
    
    /**
     * preRemove event
     * 
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $eventArgs
     */
    public function preRemove($eventArgs) {
        global $_ARRAYLANG;
        
        $em = $eventArgs->getEntityManager();
        $crmEntity = $eventArgs->getEntity();
        if ($crmEntity->contactType == 2) {
            $orderRepo = $em->getRepository('\Cx\Modules\Order\Model\Entity\Order');
            if ($orderRepo->hasOrderByCrmId($crmEntity->id)) {
                throw new \Cx\Core\Error\Model\Entity\ShinyException($_ARRAYLANG['TXT_MODULE_CRM_DELETE_USER_ERROR_MSG']);
            }
        }
        
    }

    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }
}
