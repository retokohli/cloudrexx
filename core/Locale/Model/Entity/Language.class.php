<?php

namespace Cx\Core\Locale\Model\Entity;

/**
 * Cx\Core\Locale\Model\Entity\Language
 */
class Language extends \Cx\Model\Base\EntityBase {
    /**
     * @var string $iso1
     */
    protected $iso1;

    /**
     * @var string $iso3
     */
    protected $iso3;

    /**
     * @var boolean $source
     */
    protected $source;

    /**
     * @var \Cx\Core\Locale\Model\Entity\Backend
     */
    protected $backend;

    /**
     * @var \Cx\Core\View\Model\Entity\Frontend
     */
    protected $frontends;

    /**
     * @var \Cx\Core\Locale\Model\Entity\Locale
     */
    protected $localeRelatedBySourceLanguages;

    /**
     * @var \Cx\Core\Locale\Model\Entity\Locale
     */
    protected $localeRelatedByIso1s;

    public function __construct()
    {
        $this->frontends = new \Doctrine\Common\Collections\ArrayCollection();
    $this->localeRelatedBySourceLanguages = new \Doctrine\Common\Collections\ArrayCollection();
    $this->localeRelatedByIso1s = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set iso1
     * TODO: Remove this method after doctrine update
     *
     * @param string $iso1
     */
    public function setId($iso1) {
        $this->setIso1($iso1);
    }

    /**
     * Get iso1
     * This method fixes the error when getting the identifier in the metadata class info,
     * which automatically calls getId() on an entity to get the identifier
     * TODO: Remove this method after doctrine update
     *
     * @return  string $iso1
     */
    public function getId() {
        return $this->getIso1();
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
     * Set iso3
     *
     * @param string $iso3
     */
    public function setIso3($iso3)
    {
        $this->iso3 = $iso3;
    }

    /**
     * Get iso3
     *
     * @return string $iso3
     */
    public function getIso3()
    {
        return $this->iso3;
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
     * Set backend
     *
     * @param \Cx\Core\Locale\Model\Entity\Backend $backend
     */
    public function setBackend(\Cx\Core\Locale\Model\Entity\Backend $backend)
    {
        $this->backend = $backend;
    }

    /**
     * Get backend
     *
     * @return \Cx\Core\Locale\Model\Entity\Backend $backend
     */
    public function getBackend()
    {
        return $this->backend;
    }

    /**
     * Add frontends
     *
     * @param \Cx\Core\View\Model\Entity\Frontend $frontends
     */
    public function addFrontends(\Cx\Core\View\Model\Entity\Frontend $frontends)
    {
        $this->frontends[] = $frontends;
    }

    /**
     * Get frontends
     *
     * @return \Doctrine\Common\Collections\Collection $frontends
     */
    public function getFrontends()
    {
        return $this->frontends;
    }

    /**
     * Add localeRelatedBySourceLanguages
     *
     * @param \Cx\Core\Locale\Model\Entity\Locale $localeRelatedBySourceLanguages
     */
    public function addLocaleRelatedBySourceLanguages(\Cx\Core\Locale\Model\Entity\Locale $localeRelatedBySourceLanguages)
    {
        $this->localeRelatedBySourceLanguages[] = $localeRelatedBySourceLanguages;
    }

    /**
     * Get localeRelatedBySourceLanguages
     *
     * @return \Doctrine\Common\Collections\Collection $localeRelatedBySourceLanguages
     */
    public function getLocaleRelatedBySourceLanguages()
    {
        return $this->localeRelatedBySourceLanguages;
    }

    /**
     * Add localeRelatedByIso1s
     *
     * @param \Cx\Core\Locale\Model\Entity\Locale $localeRelatedByIso1s
     */
    public function addLocaleRelatedByIso1s(\Cx\Core\Locale\Model\Entity\Locale $localeRelatedByIso1s)
    {
        $this->localeRelatedByIso1s[] = $localeRelatedByIso1s;
    }

    /**
     * Get localeRelatedByIso1s
     *
     * @return \Doctrine\Common\Collections\Collection $localeRelatedByIso1s
     */
    public function getLocaleRelatedByIso1s()
    {
        return $this->localeRelatedByIso1s;
    }
}