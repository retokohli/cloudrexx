<?php
/**
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @copyright   Comvation AG
 * @link        http://www.comvation.com/
 * @package     comvation
 * @subpackage  module_topics
 */

namespace Cx\Modules\Topics\Entity;

/**
 * Split the URL into parameters for the FrontendController
 * @author  Reto Kohli <reto.kohli@comvation.com>
 */
class FrontendParameter
{
    /**
     * Flags are optionally added to the API request.
     *
     * Current flags are mutually exclusive,
     * and must follow the detail Entry slug, if present.
     */
    const FLAG_FULLTEXT = 'fulltext';
    const FLAG_ALPHAINDEX = 'alphaindex';

    /**
     * The base URL of the current page, without any optional arguments
     *
     * The string does not include the domain name, it has the form:
     *  /locale-system/component-name
     * @see $url_page
     * @var string
     */
    protected $url_base = null;
    /**
     * The URL of the current page or API request, with locales properly set
     *
     * The string does not include the domain name, it has the form:
     *  /locale-system/component-name[/slug-category[/locale-list[/locale-detail[/slug-entry[/...]]]]]
     * Note that any of the optional parts may be empty, in particular the
     * Category and Entry slugs.  Any of the locales will be filled with
     * defaults if empty.
     * @var string
     */
    protected $url_page = null;
    /**
     * The current system locale
     *
     * This is the virtual language folder part of the URL.
     * @var string
     */
    protected $locale_system = null;
    /**
     * The locale to use for the entry list
     * @var string
     */
    protected $locale_list = null;
    /**
     * The locale to use for the entry detail
     * @var string
     */
    protected $locale_detail = null;
    /**
     * The Category ID
     *
     * Stays null unless a valid Category slug is given.
     * @var \Cx\Modules\Topics\Model\Entity\Category
     */
    protected $category = null;
    /**
     * The Category slug
     *
     * Will be set to any given Category slug, even if not valid.
     * @var string
     */
    protected $slug_category = null;
    /**
     * The fulltext flag
     *
     * Set to true if present in the request URL.
     * @var boolean
     */
    protected $fulltext = null;
    /**
     * The alpha index flag
     *
     * Set to true if present in the request URL.
     * @var boolean
     */
    protected $alphaindex = null;
    /**
     * The Entry
     *
     * Stays null unless a valid Entry slug is given.
     * @var \Cx\Modules\Topics\Model\Entity\Entry
     */
    protected $entry = null;
    /**
     * The Entry slug
     *
     * Will be set to any given Entry slug, even if not valid.
     * @var string
     */
    protected $slug_entry = null;
    /**
     * If true, parameter values in the request have become inconsistent
     *
     * Applies to the following situations:
     *  - If locale are changed by the visitor, slugs need to be translated.
     *  - If an invalid locale or Category is specified, it is reset
     * May be set while constructing, and causes a redirect to a fixed
     * page URL.
     * Note that unknown Entry slugs do not trigger this, as only an error
     * message needs to be displayed ("Entry does not exist", or such).
     * @var boolean
     */
    protected $inconsistent_url = false;

    /**
     * Construct from the request URL
     *
     * Expects zero to four path components of the URL right after the page
     * address (e.g. de/Topics) to look like
     *      [/slug-category[/locale-list[/locale-detail[/slug-entry[/...]]]]]
     * The category slug is expected to be in the system locale,
     * the entry slug in list locale (as shown in the index).
     * Additional components after the entry slug are ignored FTTB.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function __construct(\Cx\Core\Core\Controller\Cx $cx)
    {
        $this->init();
        // Load Entities and translate according to the current locales:
        $this->translateCategory($cx); // to system locale
        $this->translateEntry($cx); // to detail locale
    }

    /**
     * Set up all properties from the request
     *
     * Takes as much as possible from the current URL, and completes
     * missing values using defaults:
     *  - The system locale is taken from the request URL
     *  - Any other locale defaults to the system locale
     *  - Category and Entry slugs default to the empty string
     */
    public function init()
    {
        $url_page = \Cx\Core\Routing\Url::fromRequest();
        // Determine the default locale without $init being set up:
        // Apparently, these are all unset for API calls:
        //  $init->getFrontendLangId()
        //  FRONTEND_LANG_ID
        $this->initLocaleSystemFromUrl($url_page);
        $url_base = \Cx\Core\Routing\Url::fromModuleAndCmd('Topics');
        // Mind that the system locale may be different:
        //  Base: /de/Topics, URL: /fr/Topics/a/b/c/d/...
        if ($this->locale_system) {
            // Replace the possibly wrong system locale in the base URL,
            // so that it corresponds to the current page URL:
            //  Base: /de/Topics, URL: /fr/Topics/a/b/c/d/...
            //  => new Base: /fr/Topics
            $url_base = preg_replace(
                '/^\\/(\w\w)\\//',
                '/'.$this->locale_system.'/', $url_base);
        }
        $this->url_base = $url_base;
        $this->url_page = $url_page;
        $this->locale_detail = $this->locale_list = $this->locale_system;
        $this->slug_category = $this->slug_entry = '';
        $parameters = preg_replace(
            '/' . preg_quote($url_base, '/') . '\\/?/', '', $url_page);
        $parts = explode('/', $parameters);
        // For missing parameters, defaults apply.
        // Note that empty parts MUST NOT overwrite default values,
        // or methods like translateEntry() may fail miserably!
        if (!empty($parts[4])) {
            switch ($parts[4]) {
                case self::FLAG_FULLTEXT:
                    // Mind that detail locale and Entry slug may be empty.
                    $this->fulltext = true;
                    break;
                case self::FLAG_ALPHAINDEX:
                    // Suppress the Entry list; send "A" ... "Z" index only,
                    // with absolute and complete URLs
                    $this->alphaindex = true;
                    break;
            }
        }
        if (!empty($parts[3])) {
            // Detail locale and Entry slug SHOULD be present
            $this->slug_entry = $parts[3];
        }
        if (!empty($parts[2])) {
            // Detail locale and Entry slug SHOULD be present
            $this->locale_detail = $parts[2];
        }
        if (!empty($parts[1])) {
            // Category slug may be empty (all Entries).
            $this->locale_list = $parts[1];
        }
        if (!empty($parts[0])) {
            // Category slug and list locale SHOULD be present
            $this->slug_category = $parts[0];
        }
    }

    /**
     * Translate the Category into the current system locale
     * @param   \Cx\Core\Core\Controller\Cx     $cx
     * @return  void
     */
    protected function translateCategory(\Cx\Core\Core\Controller\Cx $cx)
    {
        if (!$this->slug_category) {
            return;
        }
        $em = $cx->getDb()->getEntityManager();
        $translationRepo = $em->getRepository(
            'Cx\\Core\\Locale\\Model\\Entity\\Translation');
        // As shown and selected in the controls
        $translation = $translationRepo->findOneBy(array(
            // Note that the system language may change independently
            // of the rest of the URL!  Thus, search the slug in any locale.
            //'locale' => $this->locale_system, // *MUST* IGNORE!
            'objectClass' => 'Cx\\Modules\\Topics\\Model\\Entity\\Category',
            'field' => 'slug',
            'content' => $this->slug_category,
        ));
        if (!$translation) {
            \DBG::log("FrontendParameter::translateEntry(): ERROR: No Translation for Category slug {$this->slug_category}");//, locale $this->locale_system");
            $this->slug_category = '';
            $this->inconsistent_url = true;
            return;
        }
        $em->clear();
        // NO WORK (cached!):
        $cx->getDb()->getTranslationListener()
            ->setTranslatableLocale($this->locale_system);
        $categoryRepo = $em->getRepository(
            'Cx\\Modules\\Topics\\Model\\Entity\\Category');
        $category = $categoryRepo->find($translation->getForeignKey());
        $category->setTranslatableLocale($this->locale_system);
        $em->refresh($category);
        $this->category = $category;
        if ($this->slug_category !== $category->getSlug()) {
            $this->inconsistent_url = true;
        }
        $this->slug_category = $category->getSlug();
    }

    /**
     * Translate the Entry into the current list locale
     * @param   \Cx\Core\Core\Controller\Cx     $cx
     * @return  void
     */
    protected function translateEntry(\Cx\Core\Core\Controller\Cx $cx)
    {
        if (!$this->slug_entry) {
            return;
        }
        $em = $cx->getDb()->getEntityManager();
        $translationRepo = $em->getRepository(
            'Cx\\Core\\Locale\\Model\\Entity\\Translation');
        // As shown and selected in the list
        $translation = $translationRepo->findOneBy(array(
            // May be any previously active list locale
            //'locale' => 'IGNORE',
            'objectClass' => 'Cx\\Modules\\Topics\\Model\\Entity\\Entry',
            'field' => 'slug',
            'content' => $this->slug_entry,
        ));
        if (!$translation) {
            // This MUST NOT happen.
            // If it does, the Entry data is inconsistent.
            \DBG::log("FrontendParameter::translateEntry(): ERROR: No Translation for Entry slug: {$this->slug_entry}");
            return;
        }
        $entryRepo = $em->getRepository(
            'Cx\\Modules\\Topics\\Model\\Entity\\Entry');
        $cx->getDb()->getTranslationListener()
            ->setTranslatableLocale($this->locale_list);
        $entry = $entryRepo->find($translation->getForeignKey());
        $this->entry = $entry;
        $this->slug_entry = $entry->getSlug();
    }


    /**
     * Initialise the system locale from the given relative URL
     *
     * Expects a URL of the form /<lc>[/<component-or-slug>[/...]].
     * <lc> must be a two-letter string representing the current system locale.
     * This method is separate from {@link init()} because the language ID
     * has to be determined before that calls
     * \Cx\Core\Routing\Url::fromModuleAndCmd().
     * @param   string  $url_page
     * @return  void
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function initLocaleSystemFromUrl($url_page)
    {
        $this->locale_system = null;
        $match = null;
        if (preg_match('/^\\/(\w\w)(?:\\/|$)/', $url_page, $match)) {
            $this->locale_system = $match[1];
            // The constant FRONTEND_LANG_ID is not defined in API mode.
            // This is required by \Cx\Core\Routing\Url::fromModuleAndCmd(),
            // called in init(), however.
            // Note that its actual value is not relevant, as the template is
            // identical for all languages (FTTB, at least).
            // Use an arbitrary, but valid, Language ID: The default frontend
            // language is okay, the real one from the URL even better.
            if (!defined('FRONTEND_LANG_ID')) {
                define('FRONTEND_LANG_ID',
                    \FWLanguage::getFrontendIdByLocale($this->locale_system));
            }
        }
    }

    /**
     * Returns the base URL
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getUrlBase()
    {
        return $this->url_base;
    }

    /**
     * Returns the complete page or API request URL
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getUrlPage()
    {
        return
            $this->url_base . '/' .
            $this->slug_category . '/' .
            $this->locale_list . '/' .
            $this->locale_detail . '/' .
            $this->slug_entry;
    }

    /**
     * If true, inconsistency has been detected in the request URL
     *
     * Unless loaded by the API, the page should then be redirected to
     * $url_page in order to normalize the URL.
     * @return  boolean
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function isUrlInconsistent()
    {
        return $this->inconsistent_url;
    }

    /**
     * Returns the current system locale
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getLocaleSystem()
    {
        return $this->locale_system;
    }

    /**
     * Returns the current list locale
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getLocaleList()
    {
        return $this->locale_list;
    }

    /**
     * Returns the current detail locale
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getLocaleDetail()
    {
        return $this->locale_detail;
    }

    /**
     * Returns the current Category in system locale
     * @return  \Cx\Modules\Topics\Model\Entity\Category
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Returns the current Category slug
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getSlugCategory()
    {
        return $this->slug_category;
    }

    /**
     * Returns the current Entry
     * @return  \Cx\Modules\Topics\Model\Entity\Entry
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * Returns the current Entry slug
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getSlugEntry()
    {
        return $this->slug_entry;
    }

    /**
     * Returns the current fulltext status
     * @return  boolean
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getFulltext()
    {
        return $this->fulltext;
    }

    /**
     * Returns the current alpha index status
     * @return  boolean
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getAlphaindex()
    {
        return $this->alphaindex;
    }

}
