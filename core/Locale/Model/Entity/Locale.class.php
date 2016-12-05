<?php

namespace Cx\Core\Locale\Model\Entity;

/**
 * Cx\Core\Locale\Model\Entity\Locale
 */
class Locale extends \Cx\Model\Base\EntityBase {
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $label
     */
    protected $label;

    /**
     * @var \Cx\Core\Locale\Model\Entity\Locale
     */
    protected $locales;

    /**
     * @var \Cx\Core\Locale\Model\Entity\Language
     */
    protected $iso1;

    /**
     * @var \Cx\Core\Country\Model\Entity\Country
     */
    protected $country;

    /**
     * @var \Cx\Core\Locale\Model\Entity\Locale
     */
    protected $fallback;

    /**
     * @var \Cx\Core\Locale\Model\Entity\Language
     */
    protected $sourceLanguage;

    /**
     * Locale constructor.
     *
     * Creates new instance of \Cx\Core\Locale\Model\Entity\Locale
     */
    public function __construct()
    {
        $this->locales = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
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
     * Set label
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Get label
     *
     * @return string $label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Add locales
     *
     * @param \Cx\Core\Locale\Model\Entity\Locale $locales
     */
    public function addLocales(\Cx\Core\Locale\Model\Entity\Locale $locales)
    {
        $this->locales[] = $locales;
    }

    /**
     * Get locales
     *
     * @return \Doctrine\Common\Collections\Collection $locales
     */
    public function getLocales()
    {
        return $this->locales;
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

    /**
     * Set country
     *
     * @param \Cx\Core\Country\Model\Entity\Country $country
     */
    public function setCountry(\Cx\Core\Country\Model\Entity\Country $country = null)
    {
        $this->country = $country;
    }

    /**
     * Get country
     *
     * @return \Cx\Core\Country\Model\Entity\Country $country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set fallback
     *
     * @param \Cx\Core\Locale\Model\Entity\Locale $fallback
     */
    public function setFallback(\Cx\Core\Locale\Model\Entity\Locale $fallback = null)
    {
        $this->fallback = $fallback;
    }

    /**
     * Get fallback
     *
     * @return \Cx\Core\Locale\Model\Entity\Locale $fallback
     */
    public function getFallback()
    {
        return $this->fallback;
    }

    /**
     * Set sourceLanguage
     *
     * @param \Cx\Core\Locale\Model\Entity\Language $sourceLanguage
     */
    public function setSourceLanguage(\Cx\Core\Locale\Model\Entity\Language $sourceLanguage)
    {
        $this->sourceLanguage = $sourceLanguage;
    }

    /**
     * Get sourceLanguage
     *
     * @return \Cx\Core\Locale\Model\Entity\Language $sourceLanguage
     */
    public function getSourceLanguage()
    {
        return $this->sourceLanguage;
    }

    public function __toString()
    {
        return $this->getLabel();
    }
}