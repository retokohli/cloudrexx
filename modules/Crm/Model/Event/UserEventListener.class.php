<?php

/**
 * UserEventListener

 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Modules\Crm\Model\Event;

/**
 * UserEventListenerException
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_crm
 */
class UserEventListenerException extends \Exception {}

/**
 * UserEventListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_crm
 */
class UserEventListener implements \Cx\Core\Event\Model\Entity\EventListener {
    
    public function postUpdate($eventArgs) {
        
        $objUsers = $eventArgs->getEntity();
        $objUsers->objAttribute->first();
        while (!$objUsers->objAttribute->EOF) {
            $arrProfile[$objUsers->objAttribute->getId()][] = $objUsers->getProfileAttribute($objUsers->objAttribute->getId());
            $objUsers->objAttribute->next();
        }
        
        $crmId = $objUsers->getCrmUserId();
        if(!empty($crmId)) {
            $objCrmLib = new \Cx\Modules\Crm\Controller\CrmLibrary('Crm');
            $objCrmLib->setContactPersonProfile($arrProfile, $objUsers->getId());
        }
    }


    public function onEvent($eventName, array $eventArgs) {        
        $this->$eventName(current($eventArgs));
    }
}