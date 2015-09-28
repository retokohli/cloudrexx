<?php

namespace Cx\Core_Modules\TemplateEditor\Controller;

use Cx\Core\Core\Controller\Cx;
use Cx\Core\Json\JsonAdapter;
use Cx\Core\View\Model\Repository\ThemeRepository;
use Cx\Core_Modules\TemplateEditor\Model\OptionSetFileStorage;
use Cx\Core_Modules\TemplateEditor\Model\Repository\OptionSetRepository;

/**
 * Class JSONTemplateEditor
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class JsonController extends \Cx\Core\Core\Model\Entity\Controller implements JsonAdapter
{


    /**
     * Returns the internal name used as identifier for this adapter
     *
     * @return String Name of this adapter
     */
    public function getName()
    {
        return 'TemplateEditor';
    }

    /**
     * Returns an array of method names accessable from a JSON request
     *
     * @return array List of method names
     */
    public function getAccessableMethods()
    {
        if (!\Permission::checkAccess(47, 'static', true)) {
            return array();
        }
        return array(
            'updateOption', 'saveOptions', 'activatePreset', 'addPreset',
            'removePreset', 'resetPreset'
        );
    }

    /**
     * Returns all messages as string
     *
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString()
    {
        return "";
    }

    /**
     * Returns default permission as object
     *
     * @return Object
     */
    public function getDefaultPermissions()
    {
        return null;
    }


    /**
     * Save the options to the component.yml file.
     *
     * @param array $params
     */
    public function saveOptions($params)
    {
        $themeID         = isset($params['get']['tid']) ? $params['get']['tid'] : 1;
        $themeRepository = new ThemeRepository();
        $theme           = $themeRepository->findById($themeID);
        if (!isset($_SESSION['TemplateEditor'])) {
            $_SESSION['TemplateEditor'] = array();
        }
        if (!isset($_SESSION['TemplateEditor'][$themeID])) {
            $_SESSION['TemplateEditor'][$themeID] = array();
        }
        $fileStorage           = new OptionSetFileStorage(
            $this->cx->getWebsiteThemesPath()
        );
        $themeOptionRepository = new OptionSetRepository($fileStorage);

        $themeOptions     = $themeOptionRepository->get(
            $theme
        );
        $themeOptions->applyPreset(
            $themeOptions->getPresetRepository()->getByName(
                $_SESSION['TemplateEditor'][$themeID]['activePreset']
            )
        );
        $presetRepository = $themeOptions->getPresetRepository();
        $preset           = $themeOptions->getChangedPreset();
        $presetRepository->save($preset);
    }

    /**
     * Update the value of a option for a specific template.
     *
     * @param array $params
     *
     * @return array
     */
    public function updateOption($params)
    {
        global $_ARRAYLANG;

        \Env::get('init')->loadLanguageData('TemplateEditor');
        $themeID         = isset($params['get']['tid']) ? $params['get']['tid'] : 1;
        $themeRepository = new ThemeRepository();
        $theme           = $themeRepository->findById($themeID);
        if (!isset($_SESSION['TemplateEditor'])) {
            $_SESSION['TemplateEditor'] = array();
        }
        if (!isset($_SESSION['TemplateEditor'][$themeID])) {
            $_SESSION['TemplateEditor'][$themeID] = array();
        }
        $fileStorage           = new OptionSetFileStorage(
            $this->cx->getWebsiteThemesPath()
        );
        $themeOptionRepository = new OptionSetRepository($fileStorage);

        $themeOptions = $themeOptionRepository->get(
            $theme
        );
        $themeOptions->applyPreset(
            $themeOptions->getPresetRepository()->getByName(
                $_SESSION['TemplateEditor'][$themeID]['activePreset']
            )
        );
        if (empty($params['post']['optionName'])
            && !preg_match(
                '/^[a-z_]+$/i', $params['post']['optionName']
            )
        ) {
            throw new \LogicException(
                "This method needs a valid name to work."
            );
        }
        if (empty($params['post']['optionData'])) {
            throw new \LogicException(
                $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_VALUE_EMPTY']
            );
        }
        $data = $themeOptions->handleChanges(
            $params['post']['optionName'], $params['post']['optionData']
        );
        $_SESSION['TemplateEditor'][$themeID][$params['post']['optionName']]
              = $data;
        return $data;
    }

    /**
     * Activate a preset
     *
     * @param $params
     */
    public function activatePreset($params)
    {
        $presetName            = filter_var(
            $params['post']['preset'], FILTER_SANITIZE_STRING
        );
        $themeID               = isset($params['post']['tid']) ? filter_var(
            $params['post']['tid'], FILTER_VALIDATE_INT
        ) : 1;
        $themeRepository       = new ThemeRepository();
        $theme                 = $themeRepository->findById($themeID);
        $fileStorage           = new OptionSetFileStorage(
            $this->cx->getWebsiteThemesPath()
        );
        $themeOptionRepository = new OptionSetRepository($fileStorage);

        $themeOptions = $themeOptionRepository->get(
            $theme
        );
        $preset       = $themeOptions->getPresetRepository()->getByName(
            $presetName
        );
        $themeOptions->setActivePreset($preset);
        $themeOptionRepository->save($themeOptions);
    }

    /**
     * Add a new preset
     *
     * @param $params
     *
     * @return array
     */
    public function addPreset($params)
    {
        global $_ARRAYLANG;

        \Env::get('init')->loadLanguageData('TemplateEditor');
        $presetName            = filter_var(
            $params['post']['preset'], FILTER_SANITIZE_STRING
        );
        if (empty($presetName) || !preg_match("/^[a-z0-9]+$/i",$presetName)){
            throw new \LogicException(
                $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_NEW_PRESET_TEXT_NOT_ALLOWED_CHARACTERS']
            );
        }
        $presetPresetName      = isset($params['post']['tid']) ? filter_var(
            $params['post']['presetpreset'], FILTER_SANITIZE_STRING
        ) : 1;
        $themeID               = isset($params['post']['tid']) ? filter_var(
            $params['post']['tid'], FILTER_VALIDATE_INT
        ) : 1;
        $themeRepository       = new ThemeRepository();
        $theme                 = $themeRepository->findById($themeID);
        $fileStorage           = new OptionSetFileStorage(
            $this->cx->getWebsiteThemesPath()
        );
        $themeOptionRepository = new OptionSetRepository($fileStorage);
        $optionSet = $themeOptionRepository->get(
            $theme
        );
        $preset = $optionSet->getPresetRepository()->getByName(
            $presetPresetName
        );
        $preset->setName($presetName);
        $_SESSION['TemplateEditor'][$themeID]['activePreset'] = $presetName;
        $optionSet->getPresetRepository()->save($preset);
        return array('preset' => $presetName);
    }

    /**
     * Remove a preset
     *
     * @param $params
     */
    public function removePreset($params)
    {
        global $_ARRAYLANG;

        \Env::get('init')->loadLanguageData('TemplateEditor');
        $presetName            = filter_var(
            $params['post']['preset'], FILTER_SANITIZE_STRING
        );
        /**
         * Default shouldn't be deletable
         */
        if ($presetName == 'Default'){
            throw new \LogicException($_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_REMOVE_PRESET_DEFAULT_WARNING']);
        }
        $themeID               = isset($params['post']['tid']) ? filter_var(
            $params['post']['tid'], FILTER_VALIDATE_INT
        ) : 1;
        $themeRepository       = new ThemeRepository();
        $theme                 = $themeRepository->findById($themeID);
        $fileStorage           = new OptionSetFileStorage(
            $this->cx->getWebsiteThemesPath()
        );
        $themeOptionRepository = new OptionSetRepository($fileStorage);
        $themeOptions = $themeOptionRepository->get(
            $theme
        );
        $preset = $themeOptions->getPresetRepository()->getByName(
            $presetName
        );
        if ($themeOptions->getActivePreset()->getName() == $preset->getName()){
            $themeOptions->setActivePreset($themeOptions->getPresetRepository()->getByName(
                'Default'
            ));
            $themeOptionRepository->save($themeOptions);
        }
        $themeOptions->getPresetRepository()->remove($preset);
    }

    /**
     * Reset a preset
     *
     * @param $params
     */
    public function resetPreset($params){
        $themeID               = isset($params['post']['tid']) ? filter_var(
            $params['post']['tid'], FILTER_VALIDATE_INT
        ) : 1;
        $activePreset = $_SESSION['TemplateEditor'][$themeID]['activePreset'];
        $_SESSION['TemplateEditor'][$themeID] = array();
        $_SESSION['TemplateEditor'][$themeID]['activePreset'] = $activePreset;
    }
}