<?php

namespace Cx\Core_Modules\TemplateEditor\Model\Entity;

use Cx\Core\Core\Controller\Cx;
use Cx\Core\View\Model\Entity\Theme;
use Cx\Core_Modules\TemplateEditor\Model\Repository\PresetRepository;
use Cx\Core_Modules\TemplateEditor\Model\YamlSerializable;
use Cx\Core\Html\Sigma;

/**
 * Class ThemeOptions
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class OptionSet extends \Cx\Model\Base\EntityBase implements YamlSerializable
{

    /**
     * @var Option[]
     */
    protected $options;

    /**
     * @var $name
     */
    protected $name;

    protected $data;

    /**
     * The associated theme to the option set.
     *
     * @var Theme
     */
    protected $theme;

    /**
     * @var PresetRepository
     */
    protected $presetRepository;

    protected $activePreset;
    protected $tmpPreset;

    /**
     * @param Theme $theme
     * @param       $data
     */
    public function __construct($theme, $data)
    {
        $this->name             = $theme->getFoldername();
        $this->data             = $data;
        $this->theme            = $theme;
        $presetStorage
                                = new \Cx\Core_Modules\TemplateEditor\Model\PresetFileStorage(
            $this->cx->getWebsiteThemesPath() . '/' . $theme->getFoldername()
        );
        $this->presetRepository = new PresetRepository($presetStorage);

        if (!isset($data['activePreset'])) {
            $data['activePreset'] = 'Default';
        }
        $activePreset       = $data['activePreset'];
        $this->activePreset = $this->presetRepository->getByName($activePreset);
        $this->applyPreset($this->activePreset);
    }


    /**
     * Pass the changes to the option directly to handle it.
     * It returns the data which should be saved into the session.
     *
     * @param $name
     * @param $data
     *
     * @throws ThemeOptionNotFoundException
     */
    public function handleChanges($name, $data)
    {
        if (!array_key_exists($name, $this->options)) {
            throw new ThemeOptionNotFoundException();
        }
        return $this->options[$name]->handleChange($data);
    }

    /**
     * Call the renderBackend method on all child options.
     *
     * @param Sigma $template
     */
    public function renderOptions($template)
    {
        foreach ($this->options as $option) {
            $option->renderOptionField($template);
        }
    }

    /**
     * Call the renderFrontend method on all child options.
     *
     * @param Sigma $template
     */
    public function renderTheme($template)
    {
        foreach ($this->options as $option) {
            $option->renderTheme($template);
        }
    }

    /**
     * Serialize a class to use in a .yml file.
     * This should return a array which will be serialized by the caller.
     *
     * @return array
     */
    public function yamlSerialize()
    {
        $yaml = array('activePreset' => $this->activePreset->getName());
        $options = array();
        foreach ($this->options as $option) {
            $options[] = $option->yamlSerialize();
        }
        $yaml['options'] = $options;
        return $yaml;
    }

    /**
     * @param $name
     *
     * @return Option
     */
    public function getOption($name)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name]
            : null;
    }

    /**
     * @param $name
     * @param $value
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function getOptionCount()
    {
        return count($this->options);
    }

    public function getPresets()
    {
        return $this->presetRepository->findAll();
    }

    /**
     * @return Preset
     */
    public function getActivePreset()
    {
        return $this->activePreset;
    }

    /**
     * @return PresetRepository
     */
    public function getPresetRepository()
    {
        return $this->presetRepository;
    }

    /**
     * @return Preset
     */
    public function getChangedPreset()
    {
        $preset = $this->tmpPreset;
        foreach ($this->options as $name => $option) {
            $preset->setOption($name,  $option->getValue());
        }
        return $preset;
    }

    public function applyPreset(Preset $preset)
    {
        $this->tmpPreset = $preset;
        /* Saving data so later on we can reuse it
           without worrying about older applied presets */
        $data               = $this->data;
        foreach ($data['options'] as &$emptyOption) {
            if ($presetOption = $preset->getOption($emptyOption['name'])) {
                if (!is_array($emptyOption['specific'])) {
                    $emptyOption['specific'] = array();
                }
                $emptyOption['specific'] = array_merge(
                    $emptyOption['specific'], $presetOption->getValue()
                );
            }
        }

        $this->options = array();
        foreach ($data['options'] as $option) {
            $optionReflection = new \ReflectionClass($option['type']);
            if ($optionReflection->getParentClass()->getName()
                == 'Cx\Core_Modules\TemplateEditor\Model\Entity\Option'
            ) {
                if (Cx::instanciate()->getMode() == Cx::MODE_BACKEND
                    || ((Cx::instanciate()->getUser()->getFWUserObject(
                        )->objUser->login())
                        && isset($_GET['templateEditor']))
                ) {
                    if (isset($_SESSION['TemplateEditor'][$this->theme->getId(
                        )][$option['name']])) {
                        $option['specific'] = array_merge(
                            $option['specific'],
                            $_SESSION['TemplateEditor']
                            [$this->theme->getId()]
                            [$option['name']]->toArray()
                        );
                    }
                }
                $this->options[$option['name']]
                    = $optionReflection->newInstance(
                    $option['name'], $option['translation'], $option['specific']
                );
            }
        }
    }

    /**
     * @param Preset $preSet
     */
    public function setActivePreset(Preset $preSet)
    {
        $this->activePreset = $preSet;
    }
}

Class ThemeOptionNotFoundException extends \Exception
{
}