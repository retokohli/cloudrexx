<?php
/**
 * Main controller for Net Tools
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package contrexx
 * @subpackage coremodule_nettools
 */

namespace Cx\Core_Modules\NetTools\Controller;

/**
 * Main controller for Net Tools
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package contrexx
 * @subpackage coremodule_nettools
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    /**
     * getControllerClasses
     * 
     * @return type
     */
    public function getControllerClasses() {
        return array();
    }

     /**
     * Load the component Net Tools.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $subMenuTitle, $objTemplate, $_CORELANG;
                
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:               
                $objNetTools = new \Cx\Core_Modules\NetTools\Controller\NetTools(\Env::get('cx')->getPage()->getContent());
                \Env::get('cx')->getPage()->setContent($objNetTools->getPage());
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
                $objTemplate = $this->cx->getTemplate();

                $subMenuTitle = $_CORELANG['TXT_NETWORK_TOOLS'];
                $nettools = new \Cx\Core_Modules\NetTools\Controller\NetToolsManager();
                $nettools->getContent();
                break;

            default:
                break;
        }
    }

}
