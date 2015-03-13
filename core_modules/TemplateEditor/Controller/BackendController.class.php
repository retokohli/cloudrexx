<?php

/**
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core_Modules\TemplateEditor\Controller;


use Cx\Core\Html\Sigma;
use Cx\Core\View\Model\Entity\Theme;
use Cx\Core_Modules\TemplateEditor\Model\Entity\ThemeOptions;
use Cx\Core_Modules\TemplateEditor\Model\FileStorage;
use Cx\Core_Modules\TemplateEditor\Model\Repository\ThemeOptionsRepository;
use Cx\Core\Core\Model\Entity\SystemComponentBackendController;
use Cx\Core\Routing\Url;
use Cx\Core\View\Model\Repository\ThemeRepository;

class BackendController extends SystemComponentBackendController
{
    /**
     * @var ThemeRepository
     */
    private $themeRepository;
    /**
     * @var ThemeOptionsRepository
     */
    private $themeOptionRepository;

    /**
     * @var ThemeOptions
     */
    private $themeOptions;

    /**
     * @var Theme
     */
    private $theme;

    /**
     * Returns a list of available commands (?act=XY)
     *
     * @return array List of acts
     */
    public function getCommands()
    {
        return array();
    }

    /**
     * Use this to parse your backend page
     *
     * You will get the template located in /View/Template/{CMD}.html
     * You can access Cx class using $this->cx
     * To show messages, use \Message class
     *
     * @param \Cx\Core\Html\Sigma $template Template for current CMD
     * @param array               $cmd      CMD separated by slashes
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, array $cmd)
    {
        \Permission::checkAccess(47, 'static');
        $fileStorage           = new FileStorage(
            $this->cx->getWebsiteThemesPath()
        );
        $themeOptionRepository = new ThemeOptionsRepository($fileStorage);
        $this->themeOptionRepository = $themeOptionRepository;
        $this->themeRepository = new ThemeRepository();
        $themeID         = isset($_GET['tid']) ? $_GET['tid'] : 1;
        $this->theme = $this->themeRepository->findById($themeID);
        $this->themeOptions = $this->themeOptionRepository->get(
            $this->theme
        );

        $this->showOverview($template);
    }

    /**
     * Creates the main overview for this component.
     *
     * @param $template
     *
     * @throws \Cx\Core\Routing\UrlException
     */
    public function showOverview(Sigma $template)
    {
        global $_ARRAYLANG;
        \JS::registerJS('core_modules/TemplateEditor/View/Script/spectrum.js');
        $template->loadTemplateFile(
            $this->cx->getCodeBaseCoreModulePath()
            . '/TemplateEditor/View/Template/Backend/Default.html'
        );
        /**
         * @var $themes Theme[]
         */
        $themes = $this->themeRepository->findAll();
        foreach ($themes as $theme){
            $template->setVariable(array(
                'TEMPLATEEDITOR_LAYOUT_NAME' => $theme->getThemesname(),
                'TEMPLATEEDITOR_LAYOUT_ID' => $theme->getId()
            ));
            if ($this->theme->getId() == $theme->getId()){
                $template->setVariable(array(
                    'TEMPLATEEDITOR_LAYOUT_ACTIVE' => 'selected'
                ));
            }
            $template->parse('layouts');
        }

        $this->themeOptions->renderBackend($template);
        $template->setVariable(
            'TEMPLATEEDITOR_IFRAME_URL', Url::fromModuleAndCmd(
            'home', '', null,
            array('preview' => $this->theme->getId(), 'templateEditor' => 1)
        )
        );
        $template->setGlobalVariable($_ARRAYLANG);
        $template->setVariable(
            'TEMPLATEEDITOR_BACKURL', './index.php?cmd=ViewManager'
        );
        \ContrexxJavascript::getInstance()->setVariable(
            'themeid',$this->theme->getId(), 'TemplateEditor'
        );
    }

}