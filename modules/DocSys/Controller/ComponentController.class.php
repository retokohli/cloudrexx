<?php
/**
 * Main controller for DocSys
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package contrexx
 * @subpackage module_docsys
 */

namespace Cx\Modules\DocSys\Controller;

/**
 * Main controller for DocSys
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package contrexx
 * @subpackage module_docsys
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
     * Load the component DocSys.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $objTemplate, $_CORELANG, $subMenuTitle;
                
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:               
                $docSysObj= new DocSys(\Env::get('cx')->getPage()->getContent());
                \Env::get('cx')->getPage()->setContent($docSysObj->getDocSysPage());
                $docSysObj->getPageTitle(\Env::get('cx')->getPage()->getTitle());
                \Env::get('cx')->getPage()->setTitle($docSysObj->docSysTitle);
                \Env::get('cx')->getPage()->setContentTitle($docSysObj->docSysTitle);
                \Env::get('cx')->getPage()->setMetaTitle($docSysObj->docSysTitle);
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
                $objTemplate = $this->cx->getTemplate();

                \Permission::checkAccess(11, 'static');
                $subMenuTitle = $_CORELANG['TXT_DOC_SYS_MANAGER'];
                $objDocSys = new DocSysManager();
                $objDocSys->getDocSysPage();
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
        $this->cx->getEvents()->addEventListener('SearchFindContent', new \Cx\Modules\DocSys\Model\Event\DocSysEventListener());
    }

}
