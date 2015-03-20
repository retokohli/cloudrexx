<?php

/**
 * CrmCustomerEventListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Event;

/**
 * Class CrmCustomerEventListenerException
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class CrmCustomerEventListenerException extends \Exception {}

/**
 * Class CrmCustomerEventListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class CrmCustomerEventListener implements \Cx\Core\Event\Model\Entity\EventListener {

    /**
     * PrePersist Event
     * 
     * @param type $eventArgs
     */
    public function prePersist($eventArgs) {
        \DBG::msg('Multisite (CrmCustomerEventListener): prePersist');
        global $_ARRAYLANG;
        try {
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', '', 'FileSystem');
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE:
                    $options    = \Cx\Core_Modules\MultiSite\Controller\ComponentController::getModuleAdditionalDataByType('Crm');
                    if (!empty($options['Customer']) && $options['Customer'] > 0) {
                        $objCrm = new \Cx\Modules\Crm\Controller\CrmManager('crm');
                        $query = $objCrm->getContactsQuery(array('contactSearch' => 2));
                        $usageCount = $objCrm->countRecordEntries($query);
                        $crmCustomerCount = !empty($usageCount) ? $usageCount : 0;
                        if ($crmCustomerCount >= $options['Customer']) {
                            throw new \Cx\Core\Error\Model\Entity\ShinyException(sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MAXIMUM_CUSTOMERS_REACHED'], $options['Customer']).' <a href="index.php?cmd=Crm&act=customers">'.$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_GO_TO_OVERVIEW'].'</a>');
                        }
                    }
                    break;
                default:
                    break;
            }
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
            throw new \Cx\Core\Error\Model\Entity\ShinyException($e->getMessage());
        }
    }

    /**
     * To call the event listener
     * 
     * @param string $eventName event name 
     * @param array  $eventArgs event arguments
     */
    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }

}
