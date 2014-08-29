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
        switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID:
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE:
                $websiteConfigPath = \Cx\Core\Setting\Controller\Setting::getValue('websitePath') . '/' . $website->getName() . \Env::get('cx')->getConfigFolderName();
                if (!file_exists($websiteConfigPath)) {
                    break;
                }
                \Cx\Core\Setting\Controller\Setting::init('MultiSite', '', 'FileSystem', $websiteConfigPath);
                \Cx\Core\Setting\Controller\Setting::set('websiteState', $website->getStatus());
                \Cx\Core\Setting\Controller\Setting::update('websiteState');
                // we must re-initialize the original MultiSite settings of the main installation
                \Cx\Core\Setting\Controller\Setting::init('MultiSite', '', 'FileSystem');
                break;

            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
// TODO: updating ip address of domain is somehow redundant. it should only be done if ip address of website has changed
                $domains = $website->getDomains();
                foreach ($domains as $domain) {
                    \DBG::msg('Update domain (map to new IP of Website): '.$domain->getName());
                    \Env::get('cx')->getEvents()->triggerEvent('model/preUpdate', array(new \Doctrine\ORM\Event\LifecycleEventArgs($domain, $em)));
                }

                //hostName
                $websiteServiceServer = $website->getWebsiteServiceServer();

                $params = array(
                'websiteId'   => $website->getId(),
                'status'      => $website->getStatus(),
                );
                \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnServiceServer('setWebsiteState', $params, $websiteServiceServer);
                break;
        }
    }

    public function payComplete($eventArgs) {
        \DBG::msg('MultiSite (WebsiteEventListener): payComplete');
        $subscription = $eventArgs->getEntity();
        $website      = $subscription->getProductEntity();
        if ($website instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
            return $website->setup();
        }
    }

    public function onEvent($eventName, array $eventArgs) {        
        $this->$eventName(current($eventArgs));
    }
}
