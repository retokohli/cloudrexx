<?php

/**
 * EventListener for Domain
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_net
 */

namespace Cx\Core\Net\Model\Event;

/**
 * EventListener for Domain
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_net
 */
class DomainEventListener implements \Cx\Core\Event\Model\Entity\EventListener {

    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }
    
    public function prePersist($eventArgs) {
        if ($eventArgs instanceof \Doctrine\ORM\Event\LifecycleEventArgs) {
            $objJsonData = new \Cx\Core\Json\JsonData();
            $hostName = \Cx\Core\Setting\Controller\Setting::getValue('serviceHostname');
            $secretKey = \Cx\Core\Setting\Controller\Setting::getValue('managerSecretKey');
            $installationId = \Cx\Core\Setting\Controller\Setting::getValue('managerInstallationId');
            //post array
            $params = array(
                'domainName'    => $eventArgs->getEntity()->getName(),
                'websiteId'     => $eventArgs->getEntity()->getId(),
                'auth'          => \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::getAuthenticationObject($secretKey, $installationId)
            );
        
            $objJsonData->getJson('https://'.$hostName.'/cadmin/index.php?cmd=JsonData&object=MultiSite&act=mapDomain', $params, false, '', null);
        }
    }
    
    public function postPersist($eventArgs) {
        
    }
    
    public function preRemove($eventArgs) {
        
    }
    
    public function postRemove($eventArgs) {
        if ($eventArgs instanceof \Doctrine\ORM\Event\LifecycleEventArgs) {
            $objJsonData = new \Cx\Core\Json\JsonData();
            $hostName = \Cx\Core\Setting\Controller\Setting::getValue('serviceHostname');
            $secretKey = \Cx\Core\Setting\Controller\Setting::getValue('managerSecretKey');
            $installationId = \Cx\Core\Setting\Controller\Setting::getValue('managerInstallationId');
            //post array
            $params = array(
                'domainName'    => $eventArgs->getEntity()->getName(),
                'websiteId'     => $eventArgs->getEntity()->getId(),
                'auth'          => \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::getAuthenticationObject($secretKey, $installationId)
            );
        
            $objJsonData->getJson('https://'.$hostName.'/cadmin/index.php?cmd=JsonData&object=MultiSite&act=unmapDomain', $params, false, '', null);
        }
    }
}
