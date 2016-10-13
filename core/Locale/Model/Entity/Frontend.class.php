<?php

namespace Cx\Core\Locale\Model\Entity;

/**
 * Cx\Core\Locale\Model\Entity\Frontend
 */
class Frontend extends \Cx\Model\Base\EntityBase {
    /**
     * @var string $iso_1
     */
    protected $iso_1;

    /**
     * @var string $label
     */
    protected $label;

    /**
     * @var Cx\Core\Locale\Model\Entity\Frontend
     */
    protected $fallbacks;

    /**
     * @var Cx\Core\Locale\Model\Entity\Locale
     */
    protected $locale;

    /**
     * @var Cx\Core\Country\Model\Entity\Country
     */
    protected $country;

    /**
     * @var Cx\Core\Locale\Model\Entity\Locale
     */
    protected $sourceLocale;

    /**
     * @var Cx\Core\Locale\Model\Entity\Frontend
     */
    protected $frontend;

    /**
     * @var Cx\Core\Locale\Model\Entity\Frontend
     */
    protected $fallback;

    public function __construct()
    {
        $this->fallbacks = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add fallbacks
     *
     * @param Cx\Core\Locale\Model\Entity\Frontend $fallbacks
     */
    public function addFallbacks(\Cx\Core\Locale\Model\Entity\Frontend $fallbacks)
    {
        $this->fallbacks[] = $fallbacks;
    }

    /**
     * Get fallbacks
     *
     * @return Doctrine\Common\Collections\Collection $fallbacks
     */
    public function getFallbacks()
    {
        return $this->fallbacks;
    }

    /**
     * Set locale
     *
     * @param Cx\Core\Locale\Model\Entity\Locale $locale
     */
    public function setLocale(\Cx\Core\Locale\Model\Entity\Locale $locale)
    {
        $this->locale = $locale;
    }

    /**
     * Get locale
     *
     * @return Cx\Core\Locale\Model\Entity\Locale $locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set country
     *
     * @param Cx\Core\Country\Model\Entity\Country $country
     */
    public function setCountry(\Cx\Core\Country\Model\Entity\Country $country)
    {
        $this->country = $country;
    }

    /**
     * Get country
     *
     * @return Cx\Core\Country\Model\Entity\Country $country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set sourceLocale
     *
     * @param Cx\Core\Locale\Model\Entity\Locale $sourceLocale
     */
    public function setSourceLocale(\Cx\Core\Locale\Model\Entity\Locale $sourceLocale)
    {
        $this->sourceLocale = $sourceLocale;
    }

    /**
     * Get sourceLocale
     *
     * @return Cx\Core\Locale\Model\Entity\Locale $sourceLocale
     */
    public function getSourceLocale()
    {
        return $this->sourceLocale;
    }

    /**
     * Set frontend
     *
     * @param Cx\Core\Locale\Model\Entity\Frontend $frontend
     */
    public function setFrontend(\Cx\Core\Locale\Model\Entity\Frontend $frontend)
    {
        $this->frontend = $frontend;
    }

    /**
     * Get frontend
     *
     * @return Cx\Core\Locale\Model\Entity\Frontend $frontend
     */
    public function getFrontend()
    {
        return $this->frontend;
    }

    /**
     * Set fallback
     *
     * @param Cx\Core\Locale\Model\Entity\Frontend $fallback
     */
    public function setFallback(\Cx\Core\Locale\Model\Entity\Frontend $fallback)
    {
        $this->fallback = $fallback;
    }

    /**
     * Get fallback
     *
     * @return Cx\Core\Locale\Model\Entity\Frontend $fallback
     */
    public function getFallback()
    {
        return $this->fallback;
    }
}