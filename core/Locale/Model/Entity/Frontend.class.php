<?php

namespace Cx\Core\Locale\Model\Entity;

/**
 * Cx\Core\Locale\Model\Entity\Frontend
 */
class Frontend extends \Cx\Model\Base\EntityBase
{
    /**
     * @var string $iso_1
     */
    private $iso_1;

    /**
     * @var string $label
     */
    private $label;

    /**
     * @var string $country
     */
    private $country;

    /**
     * @var Cx\Core\Locale\Model\Entity\Frontend
     */
    private $fallbacks;

    /**
     * @var Cx\Core\Locale\Model\Entity\Locale
     */
    private $locale;

    /**
     * @var Cx\Core\Locale\Model\Entity\Frontend
     */
    private $frontend;

    /**
     * @var Cx\Core\Locale\Model\Entity\Locale
     */
    private $sourceLocale;

    public function __construct()
    {
        $this->fallbacks = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Get label
     *
     * @return string $label
     */
    public function getLabel()
    {
        return $this->label;
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
     * Get country
     *
     * @return string $country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set country
     *
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
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
     * Get locale
     *
     * @return Cx\Core\Locale\Model\Entity\Locale $locale
     */
    public function getLocale()
    {
        return $this->locale;
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
     * Get frontend
     *
     * @return Cx\Core\Locale\Model\Entity\Frontend $frontend
     */
    public function getFrontend()
    {
        return $this->frontend;
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
     * Get sourceLocale
     *
     * @return Cx\Core\Locale\Model\Entity\Locale $sourceLocale
     */
    public function getSourceLocale()
    {
        return $this->sourceLocale;
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
}