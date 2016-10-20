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
     * @var string $iso1
     */
    protected $iso1;

    /**
     * @var \Cx\Core\Locale\Model\Entity\Language
     */
    protected $language;


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
     * @param string $iso1
     */
    public function setIso1($iso1)
    {
        $this->iso1 = $iso1;
    }

    /**
     * Get iso1
     *
     * @return string $iso1
     */
    public function getIso1()
    {
        return $this->iso1;
    }

    /**
     * Set language
     *
     * @param \Cx\Core\Locale\Model\Entity\Language $language
     */
    public function setLanguage(\Cx\Core\Locale\Model\Entity\Language $language)
    {
        $this->language = $language;
    }

    /**
     * Get language
     *
     * @return \Cx\Core\Locale\Model\Entity\Language $language
     */
    public function getLanguage()
    {
        return $this->language;
    }
}