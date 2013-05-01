<?php
/**
 * Class ComponentController
 *
 * Demo module main controller, contains callback and getPage() methods
 *
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  module_demo
 * @version     1.0.0
 */

namespace Cx\Core_Modules\FrontendEditing\Controller;

/**
 * Class ComponentController
 *
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Ueli Kramer <ueli.kramer@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_frontendediting
 * @version     1.0.0
 */
class ComponentController extends \Cx\Core\Component\Model\Entity\SystemComponentController
{

    /**
     * Returns the name of this component
     * @return String Component name
     */
    public function getName()
    {
        return 'FrontendEditing';
    }
    
    /**
     * Checks whether the frontend editing is active or not
     * @return boolean
     */
    public function frontendEditingIsActive($cx) {
        global $_CONFIG;
        
        if ($cx->getMode() != \Cx\Core\Cx::MODE_FRONTEND || !$cx->getPage()) {
            return false;
        }

        // check permission and frontend editing status
        if (($cx->getUser()->objUser->getAdminStatus()
            || (   $_CONFIG['frontendEditingStatus'] == 'on'
                && \Permission::checkAccess(6, 'static', true)
                && \Permission::checkAccess(35, 'static', true)
                && (   !$cx->getPage()->isBackendProtected()
                    || Permission::checkAccess($cx->getPage()->getId(), 'page_backend', true))))
            && $cx->getPage()->getType() == 'content'
        ) {
            return true;
        }
        return false;
    }

    /**
     * Add the necessary divs for the inline editing
     */
    public function preContentLoad(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page = null) {
        
        // Is frontend editing active?
        if (!$this->frontendEditingIsActive($cx)) {
            return;
        }
        
        $componentTemplate = new \Cx\Core\Html\Sigma(ASCMS_CORE_MODULE_PATH.'/' . $this->getName() . '/View/Template');
        $componentTemplate->setErrorHandling(PEAR_ERROR_DIE);

        // add div around content
        $componentTemplate->loadTemplateFile('ContentDiv.html');
        $componentTemplate->setVariable('CONTENT', $page->getContent());
        $page->setContent($componentTemplate->get());

        // add div around the title
        $componentTemplate->loadTemplateFile('TitleDiv.html');
        $componentTemplate->setVariable('TITLE', $page->getContentTitle());
        $page->setContentTitle($componentTemplate->get());
    }

    public function preFinalize(\Cx\Core\Cx $cx, \Cx\Core\Html\Sigma $template) {
        
        // Is frontend editing active?
        if (!$this->frontendEditingIsActive($cx)) {
            return;
        }
        
        $frontendEditing = new \Cx\Core_Modules\FrontendEditing\Controller\FrontendController();
        $frontendEditing->initFrontendEditing($this);
    }

    public function load(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page = null) {}

    public function postContentLoad(\Cx\Core\Cx $cx, &$content) {}

    public function postContentParse(\Cx\Core\Cx $cx, &$content) {}

    public function postFinalize(\Cx\Core\Cx $cx) {}

    public function postResolve(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page = null) {}

    public function preContentParse(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page = null) {}

    public function preResolve(\Cx\Core\Cx $cx, \Cx\Core\Routing\Url $request) {}
}
