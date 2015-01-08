<?php

namespace Cx\Core\User\Model\Entity;

/**
 * Cx\Core\User\Model\Entity\UserAttributeName
 */
class UserAttributeName extends \Cx\Model\Base\EntityBase {
    /**
     * @var integer $attributeId
     */
    private $attributeId;

    /**
     * @var integer $langId
     */
    private $langId;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var Cx\Core\User\Model\Entity\UserAttribute
     */
    private $userAttribute;


    /**
     * Set attributeId
     *
     * @param integer $attributeId
     */
    public function setAttributeId($attributeId)
    {
        $this->attributeId = $attributeId;
    }

    /**
     * Get attributeId
     *
     * @return integer $attributeId
     */
    public function getAttributeId()
    {
        return $this->attributeId;
    }

    /**
     * Set langId
     *
     * @param integer $langId
     */
    public function setLangId($langId)
    {
        $this->langId = $langId;
    }

    /**
     * Get langId
     *
     * @return integer $langId
     */
    public function getLangId()
    {
        return $this->langId;
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