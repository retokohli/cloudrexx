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
    }

    public function onEvent($eventName, array $eventArgs) {        
        $this->$eventName(current($eventArgs));
    }
}
