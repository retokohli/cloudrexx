<?php
/**
 * Main controller for Egov
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package contrexx
 * @subpackage module_egov
 */

namespace Cx\Modules\Egov\Controller;

/**
 * Main controller for Egov
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package contrexx
 * @subpackage module_egov
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
     * Load the component Egov.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $objTemplate, $_CORELANG, $subMenuTitle;
                
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:               
                $objEgov = new Egov(\Env::get('cx')->getPage()->getContent());
                \Env::get('cx')->getPage()->setContent($objEgov->getPage());
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
                $objTemplate = $this->cx->getTemplate();

                \Permission::checkAccess(109, 'static');
                $subMenuTitle = $_CORELANG['TXT_EGOVERNMENT'];
                $objEgov = new EgovManager();
                $objEgov->getPage();
                break;

            default:
                break;
        }
    }

}
