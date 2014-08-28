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
            
            if (isset($_POST['debugging'])) {
                $configObj->updateDebugSettings($_POST['debugging']);
            }
            if (isset($_POST['timezone']) && !in_array((!empty($_POST['timezone']) ? $_POST['timezone'] : ''), timezone_identifiers_list())) {
                \Message::add($_ARRAYLANG['TXT_CORE_TIMEZONE_INVALID'], \Message::CLASS_ERROR);
                return;
            }
             
            switch ($objSetting->getName()) {
                case 'domainUrl':
                    $arrMatch = array();
                    if (preg_match('#^https?://(.*)$#', $value, $arrMatch)) {
                        $value = $arrMatch[1];
                    }
                    $objSetting->setValue(htmlspecialchars($value, ENT_QUOTES, CONTREXX_CHARSET));
                    break;
                case 'xmlSitemapStatus':
                    $objSetting->setValue($value);
                    break;
                case 'coreListProtectedPages':
                    $objSetting->setValue($value);
                    break;
                case 'cacheEnabled':
                case 'xmlSitemapStatus':
                case 'systemStatus':
                case 'languageDetection':
                case 'frontendEditingStatus':
                case 'coreListProtectedPages':
                case 'dashboardNews':
                case 'dashboardStatistics':
                case 'passwordComplexity':
                    $value = ($value == 'on') ? 'on' : 'off';
                    $objSetting->setValue($value);
                    break;
                case 'forceProtocolFrontend':
                    if ($_CONFIG['forceProtocolFrontend'] != $value) {
                        if (!$configObj->checkAccessibility($value)) {
                            $value = 'none';
                        }
                        $objSetting->setValue($value);
                    }
                    break;
                case 'forceProtocolBackend':
                    
                    if ($_CONFIG['forceProtocolBackend'] != $value) {
                        if (!$configObj->checkAccessibility($value)) {
                            $value = 'none';
                        }
                        
                        $objSetting->setValue($value);
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
                    break;
                    
            }
            
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