<?php
/**
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core_Modules\TemplateEditor\Model\Entity;


use Cx\Core_Modules\TemplateEditor\Model\YamlSerializable;

class Preset implements YamlSerializable
{
    protected $name;

    /**
     * @var Value[]
     */
    protected $optionValues = [];

    /**
     * @param $name
     * @param $options
     *
     * @return Preset
     */
    public static function createFromArray($name,$options)
    {
        $preset = new Preset();
        $preset->setName($name);
        foreach ($options['options'] as $option) {
            $preset->setOption($option['name'], $option['specific']);
        }
        return $preset;
    }

    /**
     * Serialize a class to use in a .yml file.
     * This should return a array which will be serialized by the caller.
     *
     * @return array
     */
    public function yamlSerialize()
    {
        $yml = array('options' => array());
        foreach ($this->optionValues as $option){
            $yml['options'][] = array('name' => $option->getName(),
               'specific' => $option->getValue());
        }
        return $yml;
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

    /**
     * @param mixed $optionValues
     */
    public function setOptionValues($optionValues)
    {
        $this->optionValues = $optionValues;
    }

    /**
     * @param $name
     * @param $value
     */
    public function setOption($name, $value)
    {
        $this->optionValues[$name] = new Value($name, $value);
    }

    public function getOption($name)
    {
        return $this->optionValues[$name];
    }

}