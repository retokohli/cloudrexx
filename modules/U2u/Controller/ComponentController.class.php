<?php

/**
 * Main controller for U2u
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_u2u
 */

namespace Cx\Modules\U2u\Controller;

/**
 * Main controller for U2u
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_u2u
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
        global $_CORELANG, $subMenuTitle, $objTemplate;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                
                $objU2u = new U2u(\Env::get('cx')->getPage()->getContent());
                \Env::get('cx')->getPage()->setContent($objU2u->getPage(\Env::get('cx')->getPage()->getMetatitle(), \Env::get('cx')->getPage()->getTitle()));

                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:

                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
                $objTemplate = $this->cx->getTemplate();

                \Permission::checkAccess(149, 'static');
                
                $subMenuTitle = $_CORELANG['TXT_U2U_MODULE'];
                $objU2uAdmin = new U2uAdmin();
                $objU2uAdmin->getPage();

                break;

            default:
                break;
        }
    }

}