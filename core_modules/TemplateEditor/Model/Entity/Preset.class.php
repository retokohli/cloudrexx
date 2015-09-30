<?php
/**
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core_Modules\TemplateEditor\Model\Entity;


use Cx\Core_Modules\TemplateEditor\Model\YamlSerializable;

/**
 * Class Preset
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class Preset implements YamlSerializable
{
    /**
     * Name of the preset
     *
     * @var string
     */
    protected $name;

    /**
     * @var Value[]
     */
    protected $optionValues = [];

    /**
     * Create a preset form a raw options array.
     *
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
     * Get the name
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name
     *
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Set optionvalues.
     *
     * @param mixed $optionValues
     */
    public function setOptionValues($optionValues)
    {
        $this->optionValues = $optionValues;
    }

    /**
     * Set a option
     *
     * @param $name
     * @param $value
     */
    public function setOption($name, $value)
    {
        $this->optionValues[$name] = new Value($name, $value);
    }

    /**
     * Get a option
     *
     * @param $name
     *
     * @return Value
     */
    public function getOption($name)
    {
        return $this->optionValues[$name];
    }

    /**
     * Checks if given name is a valid preset name.
     *
     * @param $presetName
     *
     * @return bool
     */
    public static function isValidPresetName($presetName){
        global $_ARRAYLANG;
        if (empty($presetName) || !preg_match("/^[a-z0-9]+$/i",$presetName)){
            return false;
        }
        return true;
    }

}