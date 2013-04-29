<?php

/**
 * Cx\Core\Component\Model\Entity\SystemComponent
 */
namespace Cx\Core\Component\Model\Entity;

/**
 * Cx\Core\Component\Model\Entity\SystemComponent
 */
class SystemComponent
{
    const TYPE_CORE = 'core';
    const TYPE_CORE_MODULE = 'core_module';
    const TYPE_MODULE = 'module';
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $name
     */
    private $name;


    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
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
     * @var enum $type
     */
    private $type;


    /**
     * Set type
     *
     * @param enum $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return enum $type
     */
    public function getType()
    {
        return $this->type;
    }
}