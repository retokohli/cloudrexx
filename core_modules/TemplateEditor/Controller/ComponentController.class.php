<?php
/**
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core_Modules\TemplateEditor\Controller;

use Cx\Core\Core\Controller\Cx;
use Cx\Core\Core\Model\Entity\SystemComponentController;
use Cx\Core\View\Model\Repository\ThemeRepository;
use Cx\Core_Modules\TemplateEditor\Model\FileStorage;
use Cx\Core_Modules\TemplateEditor\Model\Repository\ThemeOptionsRepository;

class ComponentController extends SystemComponentController
{


    public function preFinalize(\Cx\Core\Html\Sigma $template)
    {
        if ($this->cx->getMode() == Cx::MODE_FRONTEND) {
            if (isset($_GET['templateEditor'])) {
                $fileStorage = new FileStorage(
                    $this->cx->getWebsiteThemesPath()
                );
                $themeOptionRepository = new ThemeOptionsRepository($fileStorage);
                $themeRepository = new ThemeRepository();
                $themeID = isset($_GET['preview']) ? $_GET['preview'] : 1;
                $theme = $themeRepository->findById($themeID);
                $themeOptions = $themeOptionRepository->get(
                    $theme
                );
                $themeOptions->renderFrontend($template);
            }
        }
    }

    public function getControllersAccessableByJson() {
        return array('JSONTemplateEditor');
    }

}