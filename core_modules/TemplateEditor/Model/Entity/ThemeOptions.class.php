<?php

namespace Cx\Core_Modules\TemplateEditor\Model\Entity;

use Cx\Core\Core\Controller\Cx;
use Cx\Core\View\Model\Entity\Theme;
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
class ThemeOptions implements YamlSerializable
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
     * @param Theme $theme
     * @param       $data
     */
    public function __construct($theme, $data) {
        $this->name  = $theme->getFoldername();
        $this->data  = $data;
        $this->theme = $theme;
        foreach ($data['DlcInfo']['options'] as $option) {
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
                            $_SESSION['TemplateEditor'][$this->theme->getId(
                            )][$option['name']]->toArray()
                        );
                    }
                }
                $this->options[$option['name']] = $optionReflection->newInstance(
                    $option['name'], $option['translation'], $option['specific']
                );
            }
        }
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
    public function handleChanges($name, $data) {
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
    public function renderBackend($template) {
        foreach ($this->options as $option) {
            $option->renderBackend($template);
        }
    }

    /**
     * Call the renderFrontend method on all child options.
     *
     * @param Sigma $template
     */
    public function renderFrontend($template) {
        foreach ($this->options as $option) {
            $option->renderFrontend($template);
        }
    }

    /**
     * Serialize a class to use in a .yml file.
     * This should return a array which will be serialized by the caller.
     *
     * @return array
     */
    public function yamlSerialize() {
        $options = array();
        foreach ($this->options as $option) {
            $options[] = $option->yamlSerialize();
        }
        $this->data['DlcInfo']['options'] = $options;
        return $this->data;
    }

    /**
     * @param $name
     *
     * @return Option
     */
    public function getOption($name) {
        return array_key_exists($name, $this->options) ? $this->options[$name]
            : null;
    }

    /**
     * @param $name
     * @param $value
     */
    public function setOption($name, $value) {
        $this->options[$name] = $value;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    public function getOptionCount() {
        return count($this->options);
    }
}

Class ThemeOptionNotFoundException extends \Exception
{
}