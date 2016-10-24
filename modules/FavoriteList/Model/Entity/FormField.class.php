<?php

namespace Cx\Modules\FavoriteList\Model\Entity;

/**
 * Cx\Modules\FavoriteList\Model\Entity\FormField
 */
class FormField extends \Cx\Model\Base\EntityBase
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $type
     */
    protected $type;

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var integer $required
     */
    protected $required;

    /**
     * @var integer $order
     */
    protected $order;

    /**
     * @var string $values
     */
    protected $values;

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
     * Set id
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get required
     *
     * @return integer $required
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Set required
     *
     * @param integer $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * Get order
     *
     * @return integer $order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set order
     *
     * @param integer $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * Get values
     *
     * @return string $values
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Set values
     *
     * @param string $values
     */
    public function setValues($values)
    {
        $this->values = $values;
    }
}
