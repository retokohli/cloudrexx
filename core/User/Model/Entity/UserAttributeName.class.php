<?php

namespace Cx\Core\User\Model\Entity;

/**
 * Cx\Core\User\Model\Entity\UserAttributeName
 */
class UserAttributeName extends \Cx\Model\Base\EntityBase {
    /**
     * @var integer $attribute_id
     */
    private $attribute_id;

    /**
     * @var integer $lang_id
     */
    private $lang_id;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var Cx\Core\User\Model\Entity\UserAttribute
     */
    private $userAttribute;


    /**
     * Set attribute_id
     *
     * @param integer $attributeId
     */
    public function setAttributeId($attributeId)
    {
        $this->attribute_id = $attributeId;
    }

    /**
     * Get attribute_id
     *
     * @return integer $attributeId
     */
    public function getAttributeId()
    {
        return $this->attribute_id;
    }

    /**
     * Set lang_id
     *
     * @param integer $langId
     */
    public function setLangId($langId)
    {
        $this->lang_id = $langId;
    }

    /**
     * Get lang_id
     *
     * @return integer $langId
     */
    public function getLangId()
    {
        return $this->lang_id;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set userAttribute
     *
     * @param Cx\Core\User\Model\Entity\UserAttribute $userAttribute
     */
    public function setUserAttribute(\Cx\Core\User\Model\Entity\UserAttribute $userAttribute)
    {
        $this->userAttribute = $userAttribute;
    }

    /**
     * Get userAttribute
     *
     * @return Cx\Core\User\Model\Entity\UserAttribute $userAttribute
     */
    public function getUserAttribute()
    {
        return $this->userAttribute;
    }
}