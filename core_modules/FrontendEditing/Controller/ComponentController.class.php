<?php
/**
 * Class ComponentController
 *
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Ueli Kramer <ueli.kramer@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_frontendediting
 * @version     1.0.0
 */

namespace Cx\Core_Modules\FrontendEditing\Controller;

/**
 * Class ComponentController
 *
 * The main controller for the frontend editing
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
     *
     * The frontend editing is deactivated for application pages except the home page
     *
     * @param \Cx\Core\Cx $cx
     * @return bool
     */
    public function frontendEditingIsActive(\Cx\Core\Cx $cx)
    {
        global $_CONFIG;

        if ($cx->getMode() != \Cx\Core\Cx::MODE_FRONTEND || !$cx->getPage()) {
            return false;
        }

        // check permission and frontend editing status
        // @todo: add check for mobile phone ( if it is a mobile phone, don't show the frontend editing )
        if ($_CONFIG['frontendEditingStatus'] != 'on') {
            return false;
        }
        if ($cx->getUser()->objUser->getAdminStatus() ||
            (
                \Permission::checkAccess(6, 'static', true) &&
                \Permission::checkAccess(35, 'static', true) &&
                (
                    !$cx->getPage()->isBackendProtected() ||
                    Permission::checkAccess($cx->getPage()->getId(), 'page_backend', true)
                )
            )
        ) {
            return true;
        }
        return false;
    }

    /**
     * Add the necessary divs for the inline editing around the content and around the title
     *
     * @param \Cx\Core\Cx $cx
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page
     */
    public function preContentLoad(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page = null)
    {
        // Is frontend editing active?
        if (!$this->frontendEditingIsActive($cx)) {
            return;
        }

        $componentTemplate = new \Cx\Core\Html\Sigma(ASCMS_CORE_MODULE_PATH . '/' . $this->getName() . '/View/Template');
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

    /**
     * When the frontend editing is active for this page init the frontend editing
     *
     * @param \Cx\Core\Cx $cx
     * @param \Cx\Core\Html\Sigma $template
     */
    public function preFinalize(\Cx\Core\Cx $cx, \Cx\Core\Html\Sigma $template)
    {
        // Is frontend editing active?
        if (!$this->frontendEditingIsActive($cx)) {
            return;
        }

        $frontendEditing = new \Cx\Core_Modules\FrontendEditing\Controller\FrontendController();
        $frontendEditing->initFrontendEditing($this, $cx);
    }

    public function load(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page = null)
    {
    }

    public function postContentLoad(\Cx\Core\Cx $cx, &$content)
    {
    }

    public function postContentParse(\Cx\Core\Cx $cx, &$content)
    {
    }

    public function postFinalize(\Cx\Core\Cx $cx)
    {
    }

    public function postResolve(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page = null)
    {
    }

    public function preContentParse(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page = null)
    {
    }

    public function preResolve(\Cx\Core\Cx $cx, \Cx\Core\Routing\Url $request)
    {
    }
}
