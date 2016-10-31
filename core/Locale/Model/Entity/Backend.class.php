<?php

namespace Cx\Core\Locale\Model\Entity;

/**
 * Cx\Core\Locale\Model\Entity\Backend
 */
class Backend extends \Cx\Model\Base\EntityBase {
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var \Cx\Core\Locale\Model\Entity\Language
     */
    protected $iso1;


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
     * Set iso1
     *
     * @param \Cx\Core\Locale\Model\Entity\Language $iso1
     */
    public function setIso1(\Cx\Core\Locale\Model\Entity\Language $iso1)
    {
        $this->iso1 = $iso1;
    }

    /**
     * Get iso1
     *
     * @return \Cx\Core\Locale\Model\Entity\Language $iso1
     */
    public function getIso1()
    {
        return $this->iso1;
    }
}