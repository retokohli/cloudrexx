<?php

/**
 * Main controller for MemberDir
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_memberdir
 */

namespace Cx\Modules\MemberDir\Controller;

/**
 * Main controller for MemberDir
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_memberdir
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

                $objMemberDir = new MemberDir(\Env::get('cx')->getPage()->getContent());
                \Env::get('cx')->getPage()->setContent($objMemberDir->getPage());

                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:

                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'content_master.html');
                $objTemplate = $this->cx->getTemplate();

                \Permission::checkAccess(83, 'static');

                $subMenuTitle = $_CORELANG['TXT_MEMBERDIR'];
                $objMemberDirManager = new MemberDirManager();
                $objMemberDirManager->getPage();

                break;

            default:
                break;
        }
    }

    /**
     * Do something for search the content
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function preContentParse(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        $this->cx->getEvents()->addEventListener('SearchFindContent', new \Cx\Modules\Directory\Model\Event\DirectoryEventListener());
   }
}