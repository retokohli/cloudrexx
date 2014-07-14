<?php
/**
 * Main controller for Recommend
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package contrexx
 * @subpackage module_recommend
 */

namespace Cx\Modules\Recommend\Controller;

/**
 * Main controller for Recommend
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package contrexx
 * @subpackage module_recommend
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
     * Load the component Recommend.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $objTemplate, $_CORELANG, $subMenuTitle;
                
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:               
                $objRecommend = new Recommend(\Env::get('cx')->getPage()->getContent());
                \Env::get('cx')->getPage()->setContent($objRecommend->getPage());
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'content_master.html');
                $objTemplate = $this->cx->getTemplate();

                \Permission::checkAccess(64, 'static');
                $subMenuTitle = $_CORELANG['TXT_RECOMMEND'];
                $objCalendar = new RecommendManager();
                $objCalendar->getPage();
                break;
                
            default:
                break;
        }
    }

}
