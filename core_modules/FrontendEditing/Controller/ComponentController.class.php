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
class ComponentController
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
     * Add the necessary divs for the inline editing
     */
    public function preContentLoad(/*&$pageContent, &$page_title*/) {
        global $page_content, $page_title;

        $frontendEditing = new \Cx\Core_Modules\FrontendEditing\Controller\FrontendController();
        if (!$frontendEditing->frontendEditingIsActive()) {
            return;
        }

        $componentTemplate = new \Cx\Core\Html\Sigma(ASCMS_CORE_MODULE_PATH.'/' . $this->getName() . '/View/Template');
        $componentTemplate->setErrorHandling(PEAR_ERROR_DIE);

        // add div around content
        $componentTemplate->loadTemplateFile('ContentDiv.html');
        $componentTemplate->setVariable('CONTENT', $page_content);
        $page_content = $componentTemplate->get();

        // add div around the title
        $componentTemplate->loadTemplateFile('TitleDiv.html');
        $componentTemplate->setVariable('TITLE', $page_title);
        $page_title = $componentTemplate->get();
    }

    public function preFinalize() {
        // init frontend editing
        $frontendEditing = new \Cx\Core_Modules\FrontendEditing\Controller\FrontendController();
        if (!$frontendEditing->frontendEditingIsActive()) {
            return;
        }
        $frontendEditing->initFrontendEditing($this);
    }
}

