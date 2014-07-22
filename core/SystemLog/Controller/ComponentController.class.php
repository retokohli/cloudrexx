<?php
/**
 * Main controller for SystemLog
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package contrexx
 * @subpackage core_systemlog
 */

namespace Cx\Core\SystemLog\Controller;

/**
 * Main controller for SystemLog
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package contrexx
 * @subpackage core_systemlog
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    
    public function getControllerClasses() {
        return array();
    }

     /**
     * Load the component SystemLog.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $_ARRAYLANG, $subMenuTitle;
        $subMenuTitle = $_ARRAYLANG['TXT_SYSTEM_LOGS'];
        
        \Permission::checkAccess(55, 'static');
        $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
        $cachedRoot = $this->cx->getTemplate()->getRoot();
        $this->cx->getTemplate()->setRoot($this->getDirectory() . '/View/Template/Backend');
        
        $objSystemLog = new \Cx\Core\SystemLog\Controller\SystemLog();
        $objSystemLog->getLogPage();  
        
        $this->cx->getTemplate()->setRoot($cachedRoot); 
    }

}