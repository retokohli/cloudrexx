<?php

namespace Gedmo\Translatable\Entity;

/**
 * Gedmo\Translatable\Entity\Translation
 */
class Translation
{
    /**
     * @var integer $id
     */
    private $id;


    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }
}