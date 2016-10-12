<?php

namespace Cx\Core\Country\Model\Entity;

/**
 * Cx\Core\Country\Model\Entity\Country
 */
class Country extends \Cx\Model\Base\EntityBase
{
    /**
     * @var string $alpha2
     */
    private $alpha2;

    /**
     * @var string $alpha3
     */
    private $alpha3;

    /**
     * @var integer $ord
     */
    private $ord;

    /**
     * @var Cx\Core\Country\Model\Entity\Frontend
     */
    private $countries;

    public function __construct()
    {
        $this->countries = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set alpha2
     *
     * @param string $alpha2
     */
    public function setAlpha2($alpha2)
    {
        $this->alpha2 = $alpha2;
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
     * Set alpha3
     *
     * @param string $alpha3
     */
    public function setAlpha3($alpha3)
    {
        $this->alpha3 = $alpha3;
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
     * Set ord
     *
     * @param integer $ord
     */
    public function setOrd($ord)
    {
        $this->ord = $ord;
    }

    /**
     * Add countries
     *
     * @param Cx\Core\Country\Model\Entity\Frontend $countries
     */
    public function addCountries(\Cx\Core\Country\Model\Entity\Frontend $countries)
    {
        $this->countries[] = $countries;
    }

    /**
     * Get countries
     *
     * @return Doctrine\Common\Collections\Collection $countries
     */
    public function getCountries()
    {
        return $this->countries;
    }
}