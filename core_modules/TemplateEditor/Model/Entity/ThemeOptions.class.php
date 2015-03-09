<?php

namespace Cx\Core_Modules\TemplateEditor\Model\Entity;

use Cx\Core_Modules\TemplateEditor\Model\YamlSerializable;
use Cx\Core\Html\Sigma;

/**
 *
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

    /**
     * @param $data
     */
    public function __construct($data)
    {
        global $_LANGID;
        foreach ($data['DlcInfo']['options'] as $option) {
            $optionReflection = new \ReflectionClass($option['type']);
            if ($optionReflection->getParentClass()->getName()
                == 'Cx\Core_Modules\TemplateEditor\Model\Entity\Option'
            ) {
                $this->options[$option['name']] = $optionReflection->newInstance(
                    $option['name'], isset($option['translation'][$_LANGID]) ? $option['translation'][$_LANGID] : $option['name'], $option['specific']
                );
            }
        }
    }


    /**
     * @param $data
     */
    public function handleChanges($data)
    {
        // TODO implement here
    }

    /**
     * @param Sigma $template
     */
    public function renderBackend($template)
    {
        foreach ($this->options as $option){
            $option->renderBackend($template);
        }
    }

    /**
     * @param Sigma $template
     */
    public function renderFrontend($template)
    {
        // TODO implement here
    }


    public function yamlSerialize()
    {
        // TODO: Implement yamlSerialize() method.
    }

    /**
     * @param $name
     *
     * @return Option
     */
    public function getOption($name)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : null;
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
}