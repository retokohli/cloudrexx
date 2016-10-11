<?php

namespace Cx\Core\Locale\Model\Entity;

/**
 * Cx\Core\Locale\Model\Entity\Backend
 */
class Backend extends \Cx\Model\Base\EntityBase
{
    /**
     * @var string $iso_1
     */
    private $iso_1;

    /**
     * @var Cx\Core\Locale\Model\Entity\Locale
     */
    private $locale;

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
}