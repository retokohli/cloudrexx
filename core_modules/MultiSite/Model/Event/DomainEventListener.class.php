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
            $mode = \Cx\Core\Setting\Controller\Setting::getValue('mode');
            $domain  = $eventArgs->getEntity();
            if ($domain instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Domain) {
                switch ($mode) {
                    case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
                    case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID:
                        $this->removeDnsRecord($domain, 'postRemove');
                    break;
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE:
                        $this->domainMapping($eventArgs, $mode, 'unMapDomain');
                    break;
                default:
                    break;
                }
            } elseif ($domain instanceof \Cx\Core\Net\Model\Entity\Domain) {
                $this->domainMapping($eventArgs, $mode, 'unMapDomain');
            }
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
            if ($domain instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Domain) {
                switch ($mode) {
                    case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
                    case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID:
                        $this->updateDnsRecord($domain, 'preUpdate');
                    break;
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE:
                        $this->domainMapping($eventArgs, $mode, 'updateDomain');
                    break;
                default:
                    break;
                }
            } elseif ($domain instanceof \Cx\Core\Net\Model\Entity\Domain) {
                $this->domainMapping($eventArgs, $mode, 'updateDomain');
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
            $domain  = $eventArgs->getEntity();
            if ($domain instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Domain) {
                switch ($mode) {
                    case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
                    case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID:
                        $this->addDnsRecord($domain, 'prePersist');
                    break;

                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE:
                    if($domain->getType() == \Cx\Core_Modules\MultiSite\Model\Entity\Domain::TYPE_EXTERNAL_DOMAIN) {
                        $this->domainMapping($eventArgs, $mode, 'mapDomain');
                    }
                    break;
                default:
                    break;
                }
            } elseif ($domain instanceof \Cx\Core\Net\Model\Entity\Domain) {
                $this->domainMapping($eventArgs, $mode, 'mapDomain');
            }
            //for map a domain to website
            // The mapping of $domain must only be performed for external domains. The BaseDN and FQDN must not be mapped, as they have already been mapped by the manager.
            // Note: It is important that the BaseDN and FQDN are not being stored in the DomainRepository.yml file.
            // Otherwise the domain mapping would result in an infinite loop
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
        }
    }

    public function postPersist($eventArgs) {
        \DBG::msg('MultiSite (DomainEventListener): postPersist');
        try {
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
            $mode = \Cx\Core\Setting\Controller\Setting::getValue('mode');
            $em = $eventArgs->getEntityManager();
            $domain  = $eventArgs->getEntity();
            switch ($mode) {
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE:
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID:
                    $this->updateDomainRepositoryCache($em);
                    break;
                default:
                    break;
            }
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
        }
    }

    private function updateDnsRecord($domain, $event) {
        $this->removeDnsRecord($domain, $event);
        $this->addDnsRecord($domain, $event);
    }

    private function removeDnsRecord($domain, $event) {
        $this->manipulateDnsRecord($domain, 'remove', $event);
    }

    private function addDnsRecord($domain, $event) {
        $this->manipulateDnsRecord($domain, 'add', $event);
    }

    private function manipulateDnsRecord($domain, $operation, $event) {
        switch ($domain->getType()) {
            case \CX\Core_Modules\MultiSite\Model\Entity\Domain::TYPE_FQDN:
                // FQDN shall not be persisted in postPersist event
                // as it already gets persisted in prePersist event.
                if ($event == 'postPersist') {
                    return;
                }
                $type = 'A';
                $value= $domain->getWebsite()->getIpAddress();
                break;

            case \CX\Core_Modules\MultiSite\Model\Entity\Domain::TYPE_BASE_DOMAIN:
                // In prePersist event, the DNS-record of BaseDN can't be created
                // yet, as the FQDN, on which the BaseDN depends on, has not yet
                // been flushed to the database.
                if ($event == 'prePersist' || $event == 'postPersist') {
                    return;
                }
                // In case MultiSite is operated in 'hybrid'-mode, then
                // the BaseDN is the same as FQDN. In that case we won't create the
                // BaseDN record as it would be an invalid DNS-record.
                if (\Cx\Core\Setting\Controller\Setting::getValue('mode') == \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID) {
                    return;
                }
                $type = 'CNAME';
                $value= $domain->getWebsite()->getFqdn()->getName();
                break;

            case \CX\Core_Modules\MultiSite\Model\Entity\Domain::TYPE_EXTERNAL_DOMAIN:
            default:
                $type = 'CNAME';
                
                if ($domain->getWebsite() instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
                    $value= $domain->getWebsite()->getBaseDn()->getName();
                } else {
                    $config = \Env::get('config');
                    $value  = $config['domainUrl'];
                }
                break;
        }

        // add DNS record through hosting controller
        switch ($operation) {
            case 'add':
                $hostingController = \Cx\Core_Modules\MultiSite\Controller\ComponentController::getHostingController();
                $recordId = $hostingController->addDnsRecord($type, $domain->getName(), $value, \Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain'), \Cx\Core\Setting\Controller\Setting::getValue('pleskMasterSubscriptionId'));
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

    private function domainMapping($eventArgs , $mode, $event) {
        $objJsonData = new \Cx\Core\Json\JsonData();
        \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
        
        if (empty($mode) || empty($event)) {
            return;
        }
        
        switch ($mode) {
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE:
                $hostName = \Cx\Core\Setting\Controller\Setting::getValue('managerHostname');
                        
                $installationId = \Cx\Core\Setting\Controller\Setting::getValue('managerInstallationId');  
                $secretKey = \Cx\Core\Setting\Controller\Setting::getValue('managerSecretKey');
                $httpAuth = array(
                    'httpAuthMethod' => \Cx\Core\Setting\Controller\Setting::getValue('managerHttpAuthMethod'),
                    'httpAuthUsername' => \Cx\Core\Setting\Controller\Setting::getValue('managerHttpAuthUsername'),
                    'httpAuthPassword' => \Cx\Core\Setting\Controller\Setting::getValue('managerHttpAuthPassword'),
                );
            break;
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE:
                $hostName = \Cx\Core\Setting\Controller\Setting::getValue('serviceHostname');
                        
                $installationId = \Cx\Core\Setting\Controller\Setting::getValue('serviceInstallationId');  
                $secretKey = \Cx\Core\Setting\Controller\Setting::getValue('serviceSecretKey');
            
                $httpAuth = array(
                    'httpAuthMethod' => \Cx\Core\Setting\Controller\Setting::getValue('serviceHttpAuthMethod'),
                    'httpAuthUsername' => \Cx\Core\Setting\Controller\Setting::getValue('serviceHttpAuthUsername'),
                    'httpAuthPassword' => \Cx\Core\Setting\Controller\Setting::getValue('serviceHttpAuthPassword'),
                );
            break;
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID:
                $config = \Env::get('config');
                $param['post'] = array(
                    'domainName' => $eventArgs->getEntity()->getName(),
                    'auth'       => json_encode(array('sender' => $config['domainUrl']))
                );
                $objJsonMultiSite = new \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite();
                $objJsonMultiSite->$event($param);
                return;
                break;
            default:
                return;
                break;
        }
        //post array
        $params = array(
            'domainName'        => $eventArgs->getEntity()->getName(),
            'auth'              => \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::getAuthenticationObject($secretKey, $installationId),
            'coreNetDomainId'   => $eventArgs->getEntity()->getId()
        );
            
        $objJsonData->getJson(\Cx\Core_Modules\MultiSite\Controller\ComponentController::getApiProtocol().$hostName.'/cadmin/index.php?cmd=JsonData&object=MultiSite&act='.$event, $params, false, '', $httpAuth);
    } 
    
    public function onEvent($eventName, array $eventArgs) {        
        $this->$eventName(current($eventArgs));
    }
}

