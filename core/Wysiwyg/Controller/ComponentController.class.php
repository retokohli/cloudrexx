<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * This is the controllers for the component
 *
 * @copyright   Cloudrexx AG
 * @author      Sebastian Brand <sebastian.brand@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_wysiwyg
 * @version     1.0.0
 */

namespace Cx\Core\Wysiwyg\Controller;

use Cx\Core\Wysiwyg\Model\Event\WysiwygEventListener;

/**
 * This is the main controller for the component
 *
 * @copyright   Cloudrexx AG
 * @author      Sebastian Brand <sebastian.brand@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_wysiwyg
 * @version     1.0.0
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController implements \Cx\Core\Event\Model\Entity\EventListener {

    /**
     * Location of the CKeditor library
     */
    const LIB_PATH = '/ckeditor/4.6.2';

    /**
     * Add the event listener
     *
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function postResolve(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        $evm = \Cx\Core\Core\Controller\Cx::instanciate()->getEvents();
        $evm->addEventListener('wysiwygCssReload', $this);
    }

    /**
     * This function controlls the events from the eventListener
     *
     * @param string $eventName Name of the event
     * @param array $eventArgs Arguments of the event
     */
    public function onEvent($eventName, array $eventArgs) {
        switch ($eventName) {
            case 'wysiwygCssReload':
                $skinId = $eventArgs[0]['skin'];
                $result = $eventArgs[1];

                foreach ($this->getCustomCSSVariables($skinId) as $key => $val) {
                    $result[$key] = $val;
                }
                break;
            default:
                break;
        }
    }

    /**
     * Returns all Controller class names for this component (except this)
     *
     * @return array List of Controller class names (without namespace)
     */
    public function getControllerClasses() {
        return array('Backend', 'Toolbar');
    }

    /**
     * Returns all Wysiwyg templates in JSON to be used by the CKEditor
     *
     * @param   integer $skinId The ID of the webdesign template (theme) to
     *                          load the Wysiwyg templates from.
     * @return  string  JSON-encoded string of Wysiwyg templates
     */
    public function getWysiwygTemplates($skinId) {
        // wysiwyg templates from webdesign template (theme)
        $templatesFromTheme = $this->getWysiwygTemplatesFromTheme($skinId);

        // wysiwyg templates from config section
        $templatesFromConfig = $this->getWysiwygTemplatesFromConfig();

        // merge templates from theme and config section together
        $templates = array_merge($templatesFromTheme, $templatesFromConfig);

        return json_encode($templates);
    }

    /**
     * Returns the Wysiwyg templates from config section
     *
     * @return  array   List of Wysiwyg templates loaded from config section
     */
    protected function getWysiwygTemplatesFromConfig() {
        // fetch templates from configuration section
        $templates = array();
        $em = $this->cx->getDb()->getEntityManager();
        $wysiwygTemplatesRepo = $em->getRepository('Cx\Core\Wysiwyg\Model\Entity\WysiwygTemplate');
        $wysiwygTemplates = $wysiwygTemplatesRepo->findBy(array('active'=>'1'));
        foreach ($wysiwygTemplates as $wysiwygTemplate) {
            $templates[] = array(
                'title'         => $wysiwygTemplate->getTitle(),
                'image'         => $wysiwygTemplate->getImagePath(),
                'description'   => $wysiwygTemplate->getDescription(),
                'html'          => $wysiwygTemplate->getHtmlContent(),
            );
        }

        return $templates;
    }

    /**
     * Returns the Wysiwyg templates from the Wysiwyg.yml file from the theme
     * identified by $skinId. If no theme can be identified by $skinId, then
     * the Wysiwyg.yml from the default theme is loaded instead.
     *
     * @param   integer $skinId The ID of the webdesign template (theme) to
     *                          load the Wysiwyg templates from.
     * @return  array   List of Wysiwyg templates loaded from Wysiwyg.yml
     *                  file from the theme identified by $skinId.
     */
    protected function getWysiwygTemplatesFromTheme($skinId) {
        // fetch templates from webdesign template (theme)
        $themeRepo = new \Cx\Core\View\Model\Repository\ThemeRepository();

        // fetch specific theme by $skinId
        $themeFolder = '';
        if (!empty($skinId)) {
            $themeFolder = $themeRepo->findById($skinId)->getFoldername();
        }

        // fetch default theme as fallback
        if (empty($themeFolder)) {
            $themeFolder = $themeRepo->getDefaultTheme()->getFoldername();
        }

        // load Wysiwyg.yml from theme
        $wysiwygDataPath = $this->cx->getClassLoader()->getFilePath($this->cx->getWebsiteThemesPath() . '/' . $themeFolder. '/Wysiwyg.yml');
        if ($wysiwygDataPath === false) {
            return array();
        }

        $wysiwygData = \Cx\Core_Modules\Listing\Model\Entity\DataSet::load($wysiwygDataPath);
        if (!$wysiwygData) {
            return array();
        }
        if (!$wysiwygData->entryExists('Templates')) {
            return array();
        }

        return $wysiwygData->getEntry('Templates');
    }


    /**
     * find all custom css variables and return an array with the values
     *
     * @param integer $skinId skin id, default is 0
     * @return array List with needed wysiwyg options
     */
    public function getCustomCSSVariables($skinId) {
        $themeRepo = new \Cx\Core\View\Model\Repository\ThemeRepository();
        $skin = '';
        $content = '';
        $cssArr = array();
        $ymlOption = array();
        $componentData = array();
        \Cx\Core\Setting\Controller\Setting::init('Wysiwyg', 'config', 'Yaml');
        if (!\Cx\Core\Setting\Controller\Setting::isDefined('specificStylesheet')
            && !\Cx\Core\Setting\Controller\Setting::add('specificStylesheet', '0', ++$i, \Cx\Core\Setting\Controller\Setting::TYPE_CHECKBOX, '1', 'config')
        ){
            throw new \Exception("Failed to add new configuration option");
        }
        if (!\Cx\Core\Setting\Controller\Setting::isDefined('replaceActualContents')
            && !\Cx\Core\Setting\Controller\Setting::add('replaceActualContents', '0', ++$i, \Cx\Core\Setting\Controller\Setting::TYPE_CHECKBOX, '1', 'config')
        ){
            throw new \Exception("Failed to add new configuration option");
        }

        // fetch theme specified by $skinId
        if (!empty($skinId)) {
            $skin = $themeRepo->findById($skinId)->getFoldername();
            $componentData = $themeRepo->findById($skinId)->getComponentData();
        }

        // fetch default theme
        if (empty($skin)) {
            $skin = $themeRepo->getDefaultTheme()->getFoldername();
            $componentData = $themeRepo->getDefaultTheme()->getComponentData();
        }

        if(\Cx\Core\Setting\Controller\Setting::getValue('specificStylesheet','Wysiwyg')){
            $path = $this->cx->getClassLoader()->getFilePath($this->cx->getCodeBaseThemesPath() . '/' . $skin . '/index.html');
            if ($path) {
                $content = file_get_contents($path);
                $cssArr = \JS::findCSS($content);
            }
        }

        if(!empty($componentData['rendering']['wysiwyg'])){
            $ymlOption = $componentData['rendering']['wysiwyg'];
        }

        if (!empty($ymlOption['css'])) {
            $filePath = $this->cx->getWebsiteThemesWebPath() . '/' . $skin . '/' . $ymlOption['css'];
            if ($this->cx->getClassLoader()->getFilePath($filePath)) {
                $cssArr[] = $filePath;
            }
        }

        return array(
            'css' => $cssArr,
            'bodyClass' => !empty($ymlOption['bodyClass'])?$ymlOption['bodyClass']:'',
            'bodyId' => !empty($ymlOption['bodyId'])?$ymlOption['bodyId']:'',
        );
    }

    /**
     * Register your event listeners here
     *
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * CALCULATE YOUR STUFF AS LATE AS POSSIBLE.
     * Keep in mind, that you can also register your events later.
     * Do not do anything else here than initializing your event listeners and
     * list statements like
     * $this->cx->getEvents()->addEventListener($eventName, $listener);
     */
    public function registerEventListeners() {
        $eventListener = new WysiwygEventListener($this->cx);
        $this->cx->getEvents()->addEventListener('mediasource.load', $eventListener);
    }

    /**
     * Get the Toolbar of the given type
     *
     * Returns the toolbar of the desired based on the restricted of functions 
     * according to user group and default setting
     * @param string    $type   Type of desired Toolbar (one of the following:
     *                          small, full, frontendEditingContent,
     *                          frontendEditingTitle or bbcode)
     * @return string           Toolbar of the desired type based on the
     *                          restrictions according to user group and default
     *                          setting
     */
    public function getToolbar($type = 'Full') {
        $toolbarController = $this->getController('Toolbar');
        return $toolbarController->getToolbar($type);
    }

    /**
     * Get the buttons that shall be removed or unchecked
     * @return string
     * @internal param bool|false $buttonsOnly If set, returns only the buttons
     *                                      no config.removedButtons prefix
     * @internal param bool|false $isAccess If set, removes the prefix
     *                                      config.removedButtons from the string
     */
    public function getRemovedButtons() {
        $toolbarController = $this->getController('Toolbar');
        $buttons = $toolbarController->getRemovedButtons();
        return $buttons;
    }

    /**
     * Get the path to the CKeditor JavaScript library
     *
     * @return  string
     */
    public function getLibraryPath() {
        return $this->cx->getLibraryFolderName() . static::LIB_PATH;
    }

    /**
     * Get the path to the CKeditor config file
     *
     * @return  string
     */
    public function getConfigPath() {
        return $this->getDirectory(true, true) . '/ckeditor.config.js.php';
    }

    /**
     * {@inheritDoc}
     */
    public function preContentLoad(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        // register CKeditor JavaScript library
        $jsLibraryPath = $this->getLibraryPath() . '/ckeditor.js';
        if (strpos($jsLibraryPath, '/') === 0) {
            $jsLibraryPath = substr($jsLibraryPath, 1);
        }
        \JS::registerJsLibrary(
            'ckeditor',
            array(
                'jsfiles'       => array(
                    $jsLibraryPath,
                ),
                'dependencies' => array('jquery'),
            )
        );
    }
}
