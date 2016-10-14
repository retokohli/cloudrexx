<?php

namespace Cx\Core\Country\Model\Entity;

/**
 * Cx\Core\Country\Model\Entity\Country
 */
class Country extends \Cx\Model\Base\EntityBase {
    /**
     * @var string $alpha2
     */
    protected $alpha2;

    /**
     * @var string $alpha3
     */
    protected $alpha3;

    /**
     * @var integer $ord
     */
    protected $ord;

    /**
     * @var Cx\Core\Locale\Model\Entity\Locale
     */
    protected $locales;

    public function __construct()
    {
        $this->locales = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set alpha2
     *
     * @param string $alpha2
     */
    public function setAlpha2($alpha2)
    {
        $this->alpha2 = $alpha2;
    }

    /**
     * Get alpha2
     *
     * @return string $alpha2
     */
    public function getAlpha2()
    {
        return $this->alpha2;
    }

    /**
     * Set alpha3
     *
     * @param string $alpha3
     */
    public function setAlpha3($alpha3)
    {
        $this->alpha3 = $alpha3;
    }

    /**
     * Get alpha3
     *
     * @return string $alpha3
     */
    public function getAlpha3()
    {
        return $this->alpha3;
    }

    /**
     * Set ord
     *
     * @param integer $ord
     */
    public function setOrd($ord)
    {
        $this->ord = $ord;
    }

    /**
     * Get ord
     *
     * @return integer $ord
     */
    public function getOrd()
    {
        return $this->ord;
    }

    /**
     * Add locales
     *
     * @param Cx\Core\Locale\Model\Entity\Locale $locales
     */
    public function addLocales(\Cx\Core\Locale\Model\Entity\Locale $locales)
    {
        $this->locales[] = $locales;
    }

    /**
     * Get locales
     *
     * @return Doctrine\Common\Collections\Collection $locales
     */
    public function getLocales()
    {
        return $this->locales;
    }
}