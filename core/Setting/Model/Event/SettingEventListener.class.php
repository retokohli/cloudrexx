<?php
/**
 * SettingEventListener
 *  
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_setting 
 */

namespace Cx\Core\Setting\Model\Event;

/**
 * SettingEventListenerException
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_setting 
 */
class SettingEventListenerException extends \Exception {}

/**
 * SettingEventListener
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_setting 
 */
class SettingEventListener implements \Cx\Core\Event\Model\Entity\EventListener {
    
    public function preUpdate($eventArgs) 
    {
        global $_CONFIG,$_ARRAYLANG;
        try {
            $objSetting = $eventArgs->getEntity();
            $value = $objSetting->getValue();
            $configObj = new \Cx\Core\Config\Controller\Config();
            
            switch ($objSetting->getName()) {
                case 'cacheUserCache':
                case 'cacheOPCache':
                case 'cacheUserCacheMemcacheConfig':
                case 'cacheProxyCacheVarnishConfig':
                case 'coreSmtpServer':
                case 'xmlSitemapStatus':
                case 'coreListProtectedPages':
                case 'newsTeasersStatus':
                case 'blockStatus':
                case 'blockRandom':
                case 'lastAccessId':
                    $_CONFIG[$objSetting->getName()] = $value;
                    break;

                case 'timezone':
                    if (!in_array($value, timezone_identifiers_list())) {
                        \Message::add($_ARRAYLANG['TXT_CORE_TIMEZONE_INVALID'], \Message::CLASS_ERROR);
                        throw new SettingEventListenerException($_ARRAYLANG['TXT_CORE_TIMEZONE_INVALID']);
                    }
                    break;
             
                case 'domainUrl':
                    $arrMatch = array();
                    if (preg_match('#^https?://(.*)$#', $value, $arrMatch)) {
                        $value = $arrMatch[1];
                    }
                    $objSetting->setValue($value);
                    $_CONFIG['domainUrl'] = htmlspecialchars($value, ENT_QUOTES, CONTREXX_CHARSET);
                    break;

                case 'cacheEnabled':
                case 'xmlSitemapStatus':
                case 'systemStatus':
                case 'searchVisibleContentOnly':
                case 'languageDetection':
                case 'frontendEditingStatus':
                case 'coreListProtectedPages':
                case 'dashboardNews':
                case 'dashboardStatistics':
                case 'passwordComplexity':
                    // this might be obsolete
                    $value = ($value == 'on') ? 'on' : 'off';
                    $objSetting->setValue($value);
                    break;

                case 'forceProtocolFrontend':
                    if ($_CONFIG['forceProtocolFrontend'] != $value) {
                        if (!$configObj->checkAccessibility($value)) {
                            $value = 'none';
                        }
                        $objSetting->setValue($value);
                        $_CONFIG['forceProtocolFrontend'] = $value;
                    }
                    break;

                case 'forceProtocolBackend':
                    if ($_CONFIG['forceProtocolBackend'] != $value) {
                        if (!$configObj->checkAccessibility($value)) {
                            $value = 'none';
                        }
                        $objSetting->setValue($value);
                        $_CONFIG['forceProtocolBackend'] = $value;
                    }
                    break;

                case 'forceDomainUrl':
                    $useHttps = $_CONFIG['forceProtocolBackend'] == 'https';
                    $protocol = 'http';
                    if ($useHttps == 'https') {
                        $protocol = 'https';
                    }
                    $value = $configObj->checkAccessibility($protocol) ? $value : 'off';
                    $objSetting->setValue($value);
                    $_CONFIG['forceDomainUrl'] = $value;
                    break;
            }
            
            \Env::set('config', $_CONFIG);
        } catch (Exception $e) {
            \DBG::msg($e->getMessage());
        }
    }
    
    public function postFlush($eventArgs) 
    {
        try {
            $configObj = new \Cx\Core\Config\Controller\Config();
            $configObj->writeSettingsFile();
        } catch (Exception $e) {
            \DBG::msg($e->getMessage());
        }
    }
    
    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }
    
}
