<?php
/**
 * This listener is responsible for the synchronization of the domains
 * {@see Cx\Core_Modules\MultiSite\Model\Entity\Domain} within the
 * components of the MultiSite system (Website Manager / Website Service / Website)
 * as well as the DNS service.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Event;

/**
 * DomainEventListenerException
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class DomainEventListenerException extends \Exception {}

/**
 * DomainEventListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class DomainEventListener implements \Cx\Core\Event\Model\Entity\EventListener {
    public function postRemove($eventArgs) {
        \DBG::msg('MultiSite (DomainEventListener): postRemove');
        try {
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
            // if MultiSite-mode set to 'manager' or 'hybrid': remove DNS record
            // if MultiSite-mode set to 'service' or 'hybrid': update Domain-Cache
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
        }
    }

    public function preUpdate($eventArgs) {
        \DBG::msg('MultiSite (DomainEventListener): preUpdate');
        try {
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
            $mode = \Cx\Core\Setting\Controller\Setting::getValue('mode');
            $domain  = $eventArgs->getEntity();
            switch ($mode) {
                case 'manager':
                case 'hybrid':
                    $this->updateDnsRecord($domain);
                    break;

                default:
                    break;
            }
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
        }
    }

    public function prePersist($eventArgs) {
        \DBG::msg('MultiSite (DomainEventListener): prePersist');
        try {
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
            $mode = \Cx\Core\Setting\Controller\Setting::getValue('mode');
            $em = $eventArgs->getEntityManager();
            $domain  = $eventArgs->getEntity();
            switch ($mode) {
                case 'manager':
                    $this->addDnsRecord($domain);
                    break;

                case 'service':
                    $this->updateDomainRepositoryCache($em);
                    break;

                case 'hybrid':
                    $this->addDnsRecord($domain);
                    $this->updateDomainRepositoryCache($em);
                    break;

                default:
                    break;
            }
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
        }
    }

    private function updateDnsRecord($domain) {
        $this->removeDnsRecord($domain);
        $this->addDnsRecord($domain);
    }

    private function removeDnsRecord($domain) {
        $this->manipulateDnsRecord($domain, 'remove');
    }

    private function addDnsRecord($domain) {
        $this->manipulateDnsRecord($domain, 'add');
    }

    private function manipulateDnsRecord($domain, $operation) {
        switch ($domain->getType()) {
            case \CX\Core_Modules\MultiSite\Model\Entity\Domain::TYPE_FQDN:
                $type = 'A';
                $value= $domain->getWebsite()->getIpAddress();
                break;

            case \CX\Core_Modules\MultiSite\Model\Entity\Domain::TYPE_BASE_DOMAIN:
                // In case MultiSite is operated in 'hybrid'-mode, then
                // the BaseDN is the same as FQDN. In that case we won't create the
                // BaseDN record as it would be an invalid DNS-record.
                if (\Cx\Core\Setting\Controller\Setting::getValue('mode') == 'hybrid') {
                    return;
                }
                $type = 'CNAME';
                $value= $domain->getWebsite()->getFqdn()->getName();
                break;

            case \CX\Core_Modules\MultiSite\Model\Entity\Domain::TYPE_EXTERNAL_DOMAIN:
            default:
                $type = 'CNAME';
                $value= $domain->getWebsite()->getBaseDn()->getName();
                break;
        }

        // add DNS record through hosting controller
        switch ($operation) {
            case 'add':
                $hostingController = \Cx\Core_Modules\MultiSite\Controller\ComponentController::getHostingController();
                $recordId = $hostingController->addDnsRecord($type, \Cx\Core\Setting\Controller\Setting::getValue('pleskMasterSubscriptionId'), $domain->getName(), $value);
                $domain->setPleskId($recordId);
                break;

            case 'remove':
                $hostingController = \Cx\Core_Modules\MultiSite\Controller\ComponentController::getHostingController();
                $hostingController->removeDnsRecord($type, $domain->getName(), $domain->getPleskId());
                break;

            default:
                break;
        }
    }

    private function updateDomainRepositoryCache($em) {
        $domainRepository = $em->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Domain');
        $domainRepository->exportDomainAndWebsite();
    }

    public function onEvent($eventName, array $eventArgs) {        
        $this->$eventName(current($eventArgs));
    }
}

