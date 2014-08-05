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
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
            
            $hostName = \Cx\Core\Setting\Controller\Setting::getValue('serviceHostname');
                        
            $installationId = \Cx\Core\Setting\Controller\Setting::getValue('serviceInstallationId');  
            $secretKey = \Cx\Core\Setting\Controller\Setting::getValue('serviceSecretKey');
            $httpAuth = array(
                'httpAuthMethod' => \Cx\Core\Setting\Controller\Setting::getValue('serviceHttpAuthMethod'),
                'httpAuthUsername' => \Cx\Core\Setting\Controller\Setting::getValue('serviceHttpAuthUsername'),
                'httpAuthPassword' => \Cx\Core\Setting\Controller\Setting::getValue('serviceHttpAuthPassword'),
            );;
            //post array
            $params = array(
                'domainName'    => $eventArgs->getEntity()->getName(),                
                'auth'          => \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::getAuthenticationObject($secretKey, $installationId)
            );
            
            $objJsonData->getJson('https://'.$hostName.'/cadmin/index.php?cmd=JsonData&object=MultiSite&act=mapDomain', $params, false, '', $httpAuth);

        }
    }
    
    public function postPersist($eventArgs) {
        
    }
    
    public function preRemove($eventArgs) {
        
    }
    
    public function postRemove($eventArgs) {
        if ($eventArgs instanceof \Doctrine\ORM\Event\LifecycleEventArgs) {
            $objJsonData = new \Cx\Core\Json\JsonData();
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
            
            $hostName = \Cx\Core\Setting\Controller\Setting::getValue('serviceHostname');
                        
            $installationId = \Cx\Core\Setting\Controller\Setting::getValue('serviceInstallationId');  
            $secretKey = \Cx\Core\Setting\Controller\Setting::getValue('serviceSecretKey');
            
            $httpAuth = array(
                'httpAuthMethod' => \Cx\Core\Setting\Controller\Setting::getValue('serviceHttpAuthMethod'),
                'httpAuthUsername' => \Cx\Core\Setting\Controller\Setting::getValue('serviceHttpAuthUsername'),
                'httpAuthPassword' => \Cx\Core\Setting\Controller\Setting::getValue('serviceHttpAuthPassword'),
            );
              
            //post array
            $params = array(
                'domainName'    => $eventArgs->getEntity()->getName(),                
                'auth'          => \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::getAuthenticationObject($secretKey, $installationId)
            );
        
            $objJsonData->getJson('https://'.$hostName.'/cadmin/index.php?cmd=JsonData&object=MultiSite&act=unmapDomain', $params, false, '', $httpAuth);
        }
    }
}
