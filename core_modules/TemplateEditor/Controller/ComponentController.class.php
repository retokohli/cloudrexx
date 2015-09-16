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
use Cx\Core_Modules\TemplateEditor\Model\OptionSetFileStorage;
use Cx\Core_Modules\TemplateEditor\Model\PresetRepositoryException;
use Cx\Core_Modules\TemplateEditor\Model\Repository\OptionSetRepository;

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
            try {
                $fileStorage = new OptionSetFileStorage(
                    $this->cx->getWebsiteThemesPath()
                );
                $themeOptionRepository = new OptionSetRepository($fileStorage);
                $themeRepository = new ThemeRepository();
                $themeID = isset($_GET['preview']) ? $_GET['preview']
                    : null;
                $theme = $themeID ? $themeRepository->findById(
                    (int)$themeID
                ) : $themeRepository->getDefaultTheme();
                $themeOptions = $themeOptionRepository->get(
                    $theme
                );

                if (isset($_GET['templateEditor'])) {
                    $themeOptions->applyPreset(
                        $themeOptions->getPresetRepository()->getByName(
                            $_SESSION['TemplateEditor'][$themeID]['activePreset']
                        )
                    );
                }
                $themeOptions->renderTheme($template);
            } catch (PresetRepositoryException $e) {

            }
            catch (\Symfony\Component\Yaml\ParserException $e){

            }
        }
    }

    /**
     * @return array
     */
    public function getControllersAccessableByJson() {
        return array('JsonController');
    }

}