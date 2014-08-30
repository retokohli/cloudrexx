<?php
/**
 * Main controller for Config
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_config
 */

namespace Cx\Core\Config\Controller;

/**
 * Main controller for Config
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_config
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }

     /**
     * Load your component.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $subMenuTitle, $_ARRAYLANG;
        $subMenuTitle = $_ARRAYLANG['TXT_SYSTEM_SETTINGS'];

        $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
        $cachedRoot = $this->cx->getTemplate()->getRoot();
        $this->cx->getTemplate()->setRoot($this->getDirectory() . '/View/Template/Backend');

        \Permission::checkAccess(17, 'static');
        $objConfig = new \Cx\Core\Config\Controller\Config();
        $objConfig->getPage();
                
        $this->cx->getTemplate()->setRoot($cachedRoot);        
    }

    public function postResolve(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        self::registerYamlSettingEventListener();
    }

    public static function registerYamlSettingEventListener() {
        $evm = \Env::get('cx')->getEvents();
        $yamlSettingEventListener = new \Cx\Core\Config\Model\Event\YamlSettingEventListener();
        $evm->addModelListener(\Doctrine\ORM\Events::preUpdate, 'Cx\\Core\\Setting\\Model\\Entity\\YamlSetting', $yamlSettingEventListener);
        $evm->addModelListener('postFlush', 'Cx\\Core\\Setting\\Model\\Entity\\YamlSetting', $yamlSettingEventListener);
    }
}
