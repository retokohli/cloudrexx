<?php
/**
 * This listener is responsible to maintain the relations
 * to the associated domains (Cx\Core_Modules\MultiSite\Model\Entity\Domain)
 * of a website.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Event;

/**
 * WebsiteEventListenerException
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class WebsiteEventListenerException extends \Exception {}

/**
 * WebsiteEventListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class WebsiteEventListener implements \Cx\Core\Event\Model\Entity\EventListener {
    public function postUpdate($eventArgs) {
        \DBG::msg('MultiSite (WebsiteEventListener): postUpdate');
        $em      = $eventArgs->getEntityManager();
        $website = $eventArgs->getEntity();
        $domains = $website->getDomains();
        foreach ($domains as $domain) {
            \DBG::msg('Update domain (map to new IP of Website): '.$domain->getName());
            \Env::get('cx')->getEvents()->triggerEvent('model/preUpdate', array(new \Doctrine\ORM\Event\LifecycleEventArgs($domain, $em)));
        }
         switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID:
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE:
                $websiteConfigPath = \Cx\Core\Setting\Controller\Setting::getValue('websitePath') . '/' . $website->getName() . \Env::get('cx')->getConfigFolderName();
                \Cx\Core\Setting\Controller\Setting::init('MultiSite', '', 'FileSystem', $websiteConfigPath);
                \Cx\Core\Setting\Controller\Setting::set('websiteState', $website->getStatus());
                \Cx\Core\Setting\Controller\Setting::update('websiteState');
                break;
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
                //hostName
                $websiteServiceServer = $website->getWebsiteServiceServer();
                $hostname = $websiteServiceServer->getHostname();
                $httpAuth = array(
                    'httpAuthMethod'   => $websiteServiceServer->getHttpAuthMethod(),
                    'httpAuthUsername' => $websiteServiceServer->getHttpAuthUsername(),
                    'httpAuthPassword' => $websiteServiceServer->getHttpAuthPassword(),
                );        
                
                $params = array(
                    'websiteId'   => $website->getId(),
                    'status'      => $website->getStatus(),
                    'auth'        => \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::getAuthenticationObject($websiteServiceServer->getSecretKey(), $websiteServiceServer->getInstallationId())
                );
                $jd = new \Cx\Core\Json\JsonData();
                $jd->getJson(\Cx\Core_Modules\MultiSite\Controller\ComponentController::getApiProtocol().$hostname.'/cadmin/index.php?cmd=JsonData&object=MultiSite&act=setWebsiteState', $params,
                false, '', $httpAuth);
                break;
        }
    }

    public function onEvent($eventName, array $eventArgs) {        
        $this->$eventName(current($eventArgs));
    }
}
