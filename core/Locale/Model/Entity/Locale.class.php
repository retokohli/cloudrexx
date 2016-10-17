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
     * @var string $iso1
     */
    protected $iso1;

    /**
     * @var string $label
     */
    protected $label;

    /**
     * @var integer $fallback
     */
    protected $fallback;

    /**
     * @var string $sourceLanguage
     */
    protected $sourceLanguage;

    /**
     * @var \Cx\Core\Locale\Model\Entity\Locale
     */
    protected $locales;

    /**
     * @var \Cx\Core\Locale\Model\Entity\Language
     */
    protected $languageRelatedByIso1;

    /**
     * @var \Cx\Core\Country\Model\Entity\Country
     */
    protected $country;

    /**
     * @var \Cx\Core\Locale\Model\Entity\Locale
     */
    protected $locale;

    /**
     * @var \Cx\Core\Locale\Model\Entity\Language
     */
    protected $languageRelatedBySourceLanguage;

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
     * Set fallback
     *
     * @param integer $fallback
     */
    public function setFallback($fallback)
    {
        $this->fallback = $fallback;
    }

    /**
     * Get fallback
     *
     * @return integer $fallback
     */
    public function getFallback()
    {
        return $this->fallback;
    }

    /**
     * Set sourceLanguage
     *
     * @param string $sourceLanguage
     */
    public function setSourceLanguage($sourceLanguage)
    {
        $this->sourceLanguage = $sourceLanguage;
    }

    /**
     * Get sourceLanguage
     *
     * @return string $sourceLanguage
     */
    public function getSourceLanguage()
    {
        return $this->sourceLanguage;
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
     * Set languageRelatedByIso1
     *
     * @param \Cx\Core\Locale\Model\Entity\Language $languageRelatedByIso1
     */
    public function setLanguageRelatedByIso1(\Cx\Core\Locale\Model\Entity\Language $languageRelatedByIso1)
    {
        $this->languageRelatedByIso1 = $languageRelatedByIso1;
    }

    /**
     * Get languageRelatedByIso1
     *
     * @return \Cx\Core\Locale\Model\Entity\Language $languageRelatedByIso1
     */
    public function getLanguageRelatedByIso1()
    {
        return $this->languageRelatedByIso1;
    }

    /**
     * Set country
     *
     * @param \Cx\Core\Country\Model\Entity\Country $country
     */
    public function setCountry(\Cx\Core\Country\Model\Entity\Country $country)
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
     * Set locale
     *
     * @param \Cx\Core\Locale\Model\Entity\Locale $locale
     */
    public function setLocale(\Cx\Core\Locale\Model\Entity\Locale $locale)
    {
        $this->locale = $locale;
    }

    /**
     * Get locale
     *
     * @return \Cx\Core\Locale\Model\Entity\Locale $locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set languageRelatedBySourceLanguage
     *
     * @param \Cx\Core\Locale\Model\Entity\Language $languageRelatedBySourceLanguage
     */
    public function setLanguageRelatedBySourceLanguage(\Cx\Core\Locale\Model\Entity\Language $languageRelatedBySourceLanguage)
    {
        $this->languageRelatedBySourceLanguage = $languageRelatedBySourceLanguage;
    }

    /**
     * Get languageRelatedBySourceLanguage
     *
     * @return \Cx\Core\Locale\Model\Entity\Language $languageRelatedBySourceLanguage
     */
    public function getLanguageRelatedBySourceLanguage()
    {
        return $this->languageRelatedBySourceLanguage;
    }
}