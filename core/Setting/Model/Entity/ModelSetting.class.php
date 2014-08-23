<?php
/**
 * ModelSetting Entity
 *
 * A entity that represents a modelSetting.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_setting
 */

namespace Cx\Core\Setting\Model\Entity;

/**
 * ModelSetting Entity
 *
 * A entity that represents a modelSetting.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_setting
 */
class ModelSettingException extends \Exception {};

/**
 * ModelSetting Entity
 *
 * A entity that represents a modelSetting.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @package     contrexx
 * @subpackage  core_setting
 */
class ModelSetting extends \Cx\Core\Model\Model\Entity\YamlEntity {
    /**
     * Primary identifier of the modelSetting
     * @var integer
     */
    protected $id;

    /**
     * Setting name of the modelSetting
     * @var string
     */
    protected $name;
    
    /**
     * modelSetting's section name
     * @var string
     */
    protected $section;
    
    /**
     *modelSetting's group name
     * @var string
     */
    protected $group;
    
    /**
     * modelSetting's default value
     * @var string
     */
    protected $value;
    
    /**
     * modelSetting's type
     * @var string
     */
    protected $type;
    
    /**
     * modelSetting's values
     * @var string
     */
    protected $values;
    
    /**
     * modelSetting's order number
     * @var integer
     */
    protected $ord;

    /**
     * Constructor to initialize a new modelSetting.
     * @param   string  $name   Setting name of the modelSetting
     */
    public function __construct($name) {
        $this->name = $name;
    }

    /**
     * Set primary identifier of modelSetting
     * @param   integer $id Primary identifier for modelSetting
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Return primary identifier of modelSetting
     * @return  integer Primary identifier of modelSetting
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set a modelSetting name
     * @param   string $name    modelSetting name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Return the modelSetting name
     * @return  string name of modelSetting name
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Return the section name
     * @return string $section
     */
    public function getSection() {
        return $this->section;
    }
    
    /**
     * Set the section name
     * @param string $section
     */
    public function setSection($section) {
        $this->section = $section;
    }
    
    /**
     * Set the group name
     * @param string $group
     */
    public function setGroup($group) {
        $this->group = $group;
    }
    
    /**
     * Return the group name
     * @return string $group
     */
    public function getGroup() {
        return $this->group;
    }
    
    /**
     * Set the setting's value
     * @param string $value
     */
    public function setValue($value) {
        $this->value = $value;
    }
    
    /**
     * Return the setting's value
     * @return string $value
     */
    public function getValue() {
        return $this->value;
    }
    
    /**
     * Set the setting's type
     * @param string $type
     */
    public function setType($type) {
        $this->type = $type;
    }
    
    /**
     * Return the setting's type
     * @return string $type
     */
    public function getType() {
        return $this->type;
    }
    
    /**
     * Set the settings values
     * @param string $values
     */
    public function setValues($values) {
        $this->values = $values;
    }
    
    /**
     * Return the setting's values
     * @return string $values
     */
    public function getValues() {
        return $this->values;
    }
    
    /**
     * Set the setting's order
     * @param integer $ord
     */
    public function setOrd($ord) {
        $this->ord =  $ord;
    }
    
    /**
     * Return the setting's order
     * @return string
     */
    public function getOrd() {
        return $this->ord;
    }
}

