<?php

/**
 * Class ComponentController
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */

namespace Cx\Core_Modules\TemplateEditor\Controller;

use Cx\Core\Core\Controller\Cx;
use Cx\Core\Core\Model\Entity\SystemComponentController;
use Cx\Core\View\Model\Repository\ThemeRepository;
use Cx\Core_Modules\TemplateEditor\Model\FileStorage;
use Cx\Core_Modules\TemplateEditor\Model\Repository\ThemeOptionsRepository;

class ComponentController extends SystemComponentController
{

    /**
     * Do something before main template gets parsed
     *
     * This creates the frontend placeholders for the preview and the normal view.
     *
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
     *
     * @param \Cx\Core\Html\Sigma $template The main template
     */
    public function preFinalize(\Cx\Core\Html\Sigma $template) {
        if ($this->cx->getMode() == Cx::MODE_FRONTEND) {
            $fileStorage           = new FileStorage(
                $this->cx->getWebsiteThemesPath()
            );
            $themeOptionRepository = new ThemeOptionsRepository($fileStorage);
            $themeRepository       = new ThemeRepository();
            $themeID               = isset($_GET['preview']) ? $_GET['preview']
                : 1;
            $theme                 = $themeRepository->findById((int)$themeID);
            $themeOptions          = $themeOptionRepository->get(
                $theme
            );
            $themeOptions->renderFrontend($template);
        }
    }

    /**
     * @return array
     */
    public function getControllersAccessableByJson() {
        return array('JSONTemplateEditor');
    }

}