<?php
/**
 * Main controller for Survey
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_survey
 */

namespace Cx\modules\Survey\Controller;

/**
 * Main controller for Survey
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_survey
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
        global $page_metatitle, $_CORELANG, $subMenuTitle, $objTemplate;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                $objSurvey = new Survey(\Env::get('cx')->getPage()->getContent());
                \Env::get('cx')->getPage()->setContent($objSurvey->getPage());
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'content_master.html');
                $objTemplate = $this->cx->getTemplate();
                
                \Permission::checkAccess(111, 'static');
                $subMenuTitle = $_CORELANG['TXT_SURVEY'];
                $objSurvey = new SurveyManager();
                $objSurvey->getPage();
                break;

            default:
                break;
        }
    }
}
