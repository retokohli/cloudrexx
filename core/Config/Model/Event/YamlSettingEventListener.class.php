<?php
/**
 * YamlSettingEventListener
 *  
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_config
 */

namespace Cx\Core\Config\Model\Event;

/**
 * YamlSettingEventListenerException
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @package     contrexx
 * @subpackage  core_config
 */
class YamlSettingEventListenerException extends \Exception {}

/**
 * YamlSettingEventListener
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @package     contrexx
 * @subpackage  core_config
 */
class YamlSettingEventListener implements \Cx\Core\Event\Model\Entity\EventListener {
    public function preUpdate($eventArgs) {
        global $_CONFIG,$_ARRAYLANG;
        try {
            $objSetting = $eventArgs->getEntity();
            $value = $objSetting->getValue();
            
            switch ($objSetting->getName()) {
                case 'timezone':
                    if (!in_array($value, timezone_identifiers_list())) {
                        \Message::add($_ARRAYLANG['TXT_CORE_TIMEZONE_INVALID'], \Message::CLASS_ERROR);
                        throw new YamlSettingEventListenerException($_ARRAYLANG['TXT_CORE_TIMEZONE_INVALID']);
                    }
                    break;
             
                case 'domainUrl':
                    $arrMatch = array();
                    if (preg_match('#^https?://(.*)$#', $value, $arrMatch)) {
                        $value = $arrMatch[1];
                    }
                    $value = htmlspecialchars($value, ENT_QUOTES, CONTREXX_CHARSET);
                    $objSetting->setValue($value);
                    break;

                case 'forceProtocolFrontend':
                    if ($_CONFIG['forceProtocolFrontend'] != $value) {
                        if (!\Cx\Core\Config\Controller\Config::checkAccessibility($value)) {
                            $value = 'none';
                        }
                        $objSetting->setValue($value);
                    }
                    break;

                case 'forceProtocolBackend':
                    if ($_CONFIG['forceProtocolBackend'] != $value) {
                        if (!\Cx\Core\Config\Controller\Config::checkAccessibility($value)) {
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
                    $value = \Cx\Core\Config\Controller\Config::checkAccessibility($protocol) ? $value : 'off';
                    $objSetting->setValue($value);
                    break;
            }
        } catch (YamlSettingEventListenerException $e) {
            \DBG::msg($e->getMessage());
        }
    }

    public function postFlush($eventArgs) {
        try {
            \Cx\Core\Config\Controller\Config::updatePhpCache();
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
        }
    }
    
    public function onEvent($eventName, array $eventArgs) {
        \DBG::msg(__METHOD__);
        $this->$eventName(current($eventArgs));
    }
}
