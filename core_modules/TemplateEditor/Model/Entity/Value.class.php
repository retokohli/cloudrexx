<?php
/**
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core_Modules\TemplateEditor\Model\Entity;
use Cx\Core_Modules\TemplateEditor\Model\YamlSerializable;

/**
 * Class Value
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class Value implements YamlSerializable
{
    /**
     * Option name
     * @var string
     */
    protected $name;

    /**
     * Array with values
     * @var array
     */
    protected $value;

    /**
     * Value constructor.
     *
     * @param $name
     * @param $value
     */
    public function __construct($name, $value)
    {
        $this->name  = $name;
        $this->value = $value;
    }

    /**
     * Get the saved value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the saved value.
     *
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get the name.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name.
     *
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }


    /**
     * Serialize a class to use in a .yml file.
     * This should return a array which will be serialized by the caller.
     *
     * @return array
     */
    public function yamlSerialize()
    {
        return $this->value;
    }
}