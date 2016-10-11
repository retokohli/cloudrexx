<?php

namespace Cx\Core\Locale\Model\Entity;

/**
 * Cx\Core\Locale\Model\Entity\Locale
 */
class Locale extends \Cx\Model\Base\EntityBase
{
    /**
     * @var string $iso_1
     */
    private $iso_1;

    /**
     * @var string $iso_3
     */
    private $iso_3;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var boolean $source
     */
    private $source;

    /**
     * @var Cx\Core\Locale\Model\Entity\Frontend
     */
    private $iso1s;

    /**
     * @var Cx\Core\Locale\Model\Entity\Frontend
     */
    private $sourceLocales;

    /**
     * @var Cx\Core\Locale\Model\Entity\Frontend
     */
    private $languages;

    public function __construct()
    {
        $this->iso1s = new \Doctrine\Common\Collections\ArrayCollection();
        $this->sourceLocales = new \Doctrine\Common\Collections\ArrayCollection();
        $this->languages = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get iso_1
     *
     * @return string $iso1
     */
    public function getIso1()
    {
        return $this->iso_1;
    }

    /**
     * Set iso_1
     *
     * @param string $iso1
     */
    public function setIso1($iso1)
    {
        $this->iso_1 = $iso1;
    }

    /**
     * Get iso_3
     *
     * @return string $iso3
     */
    public function getIso3()
    {
        return $this->iso_3;
    }

    /**
     * Set iso_3
     *
     * @param string $iso3
     */
    public function setIso3($iso3)
    {
        $this->iso_3 = $iso3;
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
     * Get source
     *
     * @return boolean $source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set source
     *
     * @param boolean $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * Add iso1s
     *
     * @param Cx\Core\Locale\Model\Entity\Frontend $iso1s
     */
    public function addIso1s(\Cx\Core\Locale\Model\Entity\Frontend $iso1s)
    {
        $this->iso1s[] = $iso1s;
    }

    /**
     * Get iso1s
     *
     * @return Doctrine\Common\Collections\Collection $iso1s
     */
    public function getIso1s()
    {
        return $this->iso1s;
    }

    /**
     * Add sourceLocales
     *
     * @param Cx\Core\Locale\Model\Entity\Frontend $sourceLocales
     */
    public function addSourceLocales(\Cx\Core\Locale\Model\Entity\Frontend $sourceLocales)
    {
        $this->sourceLocales[] = $sourceLocales;
    }

    /**
     * Get sourceLocales
     *
     * @return Doctrine\Common\Collections\Collection $sourceLocales
     */
    public function getSourceLocales()
    {
        return $this->sourceLocales;
    }

    /**
     * Add languages
     *
     * @param Cx\Core\Locale\Model\Entity\Frontend $languages
     */
    public function addLanguages(\Cx\Core\Locale\Model\Entity\Frontend $languages)
    {
        $this->languages[] = $languages;
    }

    /**
     * Get languages
     *
     * @return Doctrine\Common\Collections\Collection $languages
     */
    public function getLanguages()
    {
        return $this->languages;
    }
}