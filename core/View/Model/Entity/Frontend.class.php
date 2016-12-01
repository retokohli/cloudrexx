<?php

namespace Cx\Core\View\Model\Entity;

/**
 * Cx\Core\View\Model\Entity\Frontend
 */
class Frontend extends \Cx\Model\Base\EntityBase {
    /**
     * @var string $language
     */
    protected $language;

    /**
     * @var integer $theme
     */
    protected $theme;

    /**
     * @var string $channel
     */
    protected $channel;

    /**
     * @var \Cx\Core\Locale\Model\Entity\Locale
     */
    protected $localeRelatedByIso1s;

    /**
     * Set language
     *
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Get language
     *
     * @return string $language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set theme
     *
     * @param integer $theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    /**
     * Get theme
     *
     * @return integer $theme
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Set channel
     *
     * @param string $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * Get channel
     *
     * @return string $channel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set localeRelatedByIso1s
     *
     * @param \Cx\Core\Locale\Model\Entity\Locale $localeRelatedByIso1s
     */
    public function setLocaleRelatedByIso1s(\Cx\Core\Locale\Model\Entity\Locale $localeRelatedByIso1s)
    {
        $this->localeRelatedByIso1s = $localeRelatedByIso1s;
    }

    /**
     * Get localeRelatedByIso1s
     *
     * @return \Cx\Core\Locale\Model\Entity\Locale $localeRelatedByIso1s
     */
    public function getLocaleRelatedByIso1s()
    {
        return $this->localeRelatedByIso1s;
    }
}