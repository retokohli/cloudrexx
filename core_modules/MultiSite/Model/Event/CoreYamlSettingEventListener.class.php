<?php

/**
 * This listener manage the coreYamlSetting
 *  
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Event;

/**
 * Class CoreYamlSettingEventListenerException
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class CoreYamlSettingEventListenerException extends \Exception {
    
}

/**
 * Class CoreYamlSettingEventListener
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class CoreYamlSettingEventListener implements \Cx\Core\Event\Model\Entity\EventListener {
    
    /**
     * postUpdate
     *  
     * @param object $eventArgs
     */
    public function postUpdate($eventArgs) {
        \DBG::msg('MultiSite (CoreYamlSettingEventListener): postUpdate');
        $entity = $eventArgs->getEntity();
        
        \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
        switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE:
                $websiteName = \Cx\Core\Setting\Controller\Setting::getValue('websiteName','MultiSite');
                if ($entity->getName() === 'mainDomainId' && !\FWValidator::isEmpty($websiteName)) {
                    $domainRepo = new \Cx\Core\Net\Model\Repository\DomainRepository();
                    $domain = $domainRepo->findOneBy(array('id' => $entity->getValue()));
                    $mainDomain = $domainRepo->getMainDomain();
                    if (\FWValidator::isEmpty($domain) || \FWValidator::isEmpty($mainDomain)) {
                        return;
                    }
                    
                    //send request to manager for plesk process
                    $params = array(
                        'websiteName'       => $websiteName,
                        'preMainDomainName' => $mainDomain->getName(),
                        'mainDomainName'    => $domain->getName()
                    );
                    $response = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnMyServiceServer('updateMainDomain', $params);
                    if ($response && $response->status == 'error' && $response->data->status == 'error') {
                        throw new CoreYamlSettingEventListenerException('Failed to set the main domain.');
                    }
                }
                break;
        }
    }

    /**
     * To call the event
     * 
     * @param string $eventName function name
     * @param array  $eventArgs arguments
     */
    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }
}
