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
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @package     contrexx
 * @subpackage  module_crm
 */
class UserEventListener implements \Cx\Core\Event\Model\Entity\EventListener {
    
    public function postUpdate($eventArgs) {
        $objUser = $eventArgs->getEntity();
        $crmId = $objUser->getCrmUserId();
        if (empty($crmId)) {
            return;
        }

        $objUser->objAttribute->first();
        while (!$objUser->objAttribute->EOF) {
            $arrProfile[$objUser->objAttribute->getId()][] = $objUser->getProfileAttribute($objUser->objAttribute->getId());
            $objUser->objAttribute->next();
        }
        
        $objCrmLib = new \Cx\Modules\Crm\Controller\CrmLibrary('Crm');
        $objCrmLib->setContactPersonProfile($arrProfile, $objUser->getId(), $objUser->getFrontendLanguage());
    }

    public function onEvent($eventName, array $eventArgs) {        
        $this->$eventName(current($eventArgs));
    }
}
