<?php

namespace Cx\Core\Locale\Model\Entity;

/**
 * Cx\Core\Locale\Model\Entity\Locale
 */
class Locale extends \Cx\Model\Base\EntityBase {
    /**
     * @var string $iso_1
     */
    private $iso_1;

    /**
     * @var string $iso_3
     */
    private $iso_3;

    /**
     * @var boolean $source
     */
    private $source;

    /**
     * @var Cx\Core\Locale\Model\Entity\Backend
     */
    private $iso1Backends;

    /**
     * @var Cx\Core\Locale\Model\Entity\Frontend
     */
    private $sourceLocales;

    /**
     * @var Cx\Core\Locale\Model\Entity\Frontend
     */
    private $iso1Frontends;

    /**
     * @var Cx\Core\Locale\Model\Entity\Frontend
     */
    private $languages;

    public function __construct()
    {
        $this->iso1Backends = new \Doctrine\Common\Collections\ArrayCollection();
    $this->sourceLocales = new \Doctrine\Common\Collections\ArrayCollection();
    $this->iso1Frontends = new \Doctrine\Common\Collections\ArrayCollection();
    $this->languages = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Get iso_1
     *
     * @return string $iso1
     */
    public function getIso1()
    {
        return $this->iso_1;
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
     * Get iso_3
     *
     * @return string $iso3
     */
    public function getIso3()
    {
        return $this->iso_3;
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
     * Get source
     *
     * @return boolean $source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Add iso1Backends
     *
     * @param Cx\Core\Locale\Model\Entity\Backend $iso1Backends
     */
    public function addIso1Backends(\Cx\Core\Locale\Model\Entity\Backend $iso1Backends)
    {
        $this->iso1Backends[] = $iso1Backends;
    }

    /**
     * Get iso1Backends
     *
     * @return Doctrine\Common\Collections\Collection $iso1Backends
     */
    public function getIso1Backends()
    {
        return $this->iso1Backends;
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
     * Add iso1Frontends
     *
     * @param Cx\Core\Locale\Model\Entity\Frontend $iso1Frontends
     */
    public function addIso1Frontends(\Cx\Core\Locale\Model\Entity\Frontend $iso1Frontends)
    {
        $this->iso1Frontends[] = $iso1Frontends;
    }

    /**
     * Get iso1Frontends
     *
     * @return Doctrine\Common\Collections\Collection $iso1Frontends
     */
    public function getIso1Frontends()
    {
        return $this->iso1Frontends;
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