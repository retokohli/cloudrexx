<?php

/**
 * This listener is check the CronMailCriteria
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Event;

/**
 * Class CronMailEventListenerException
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class CronMailEventListenerException extends \Exception {
    
}

/**
 * Class CronMailEventListener
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class CronMailEventListener implements \Cx\Core\Event\Model\Entity\EventListener {

    /**
     * prePersist
     *  
     * @param object $eventArgs
     * @throws \Cx\Core\Error\Model\Entity\ShinyException
     */
    public function prePersist($eventArgs) {
        try {
            $cronMail = $eventArgs->getEntity();
            $this->isValidCronMailCriteria($cronMail);
        } catch (\Exception $e) {
            throw new \Cx\Core\Error\Model\Entity\ShinyException($e->getMessage());
        }
    }

    /**
     * preUpdate
     *  
     * @param object $eventArgs
     * @throws \Cx\Core\Error\Model\Entity\ShinyException
     */
    public function preUpdate($eventArgs) {
        try {
            $cronMail = $eventArgs->getEntity();
            $this->isValidCronMailCriteria($cronMail);
        } catch (\Exception $e) {
            throw new \Cx\Core\Error\Model\Entity\ShinyException($e->getMessage());
        }
    }

    /**
     * To validate the CronMail entity have atleast one CronMailCriteria
     * Otherwise Throw Exception
     * 
     * @param \Cx\Core_Modules\MultiSite\Model\Entity\CronMail $objCronMail
     * 
     * @return boolean
     * @throws \Cx\Core\Error\Model\Entity\ShinyException
     */
    public function isValidCronMailCriteria(\Cx\Core_Modules\MultiSite\Model\Entity\CronMail $objCronMail) {
        global $_ARRAYLANG;
        if (!$objCronMail) {
            return;
        }
        $cronMailCriteria = $objCronMail->getCronMailCriterias();
        if (empty($cronMailCriteria[0])) {
            throw new \Cx\Core\Error\Model\Entity\ShinyException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_CRON_MAIL_CRITERIA_EMPTY']);
        }
    }

    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }

}
