<?php
namespace Cx\Core\Routing;

class URLTranslatorException extends LanguageExtractorException {}
/**
 * Takes an URL and language id and finds the URLs of the translated pages.
 */

class URLTranslator extends LanguageExtractor {
    /**
     * Doctrine entity manager.
     */
    protected $em;

    /**
     * Cx\Model\ContentManager\Page repository
     */
    protected $pageRepo;

    public function __construct($db, $dbPrefix, $em) {
        parent::__construct($db, $dbPrefix);
        
        $this->em = $em;
        $this->pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');
    }

    public function getUrlInAllLanguages($page, $pageURL) {
        $urls = array();

        $sourceLang = $page->getLang();

        $langIds = array_keys($this->languageShortNames);

        foreach($langIds as $langId) {
            if($langId == $sourceLang)
                continue;

            try {
                $urls[$langId] = $this->getUrlIn($langId, $page, $pageURL);
            }
            catch(URLTranslatorException $e) {
                //page is not translated in this language.
                //no need for action, since it wasn't added to the
                //$urls array - exactly what we want.
            }
        }
        return $urls;
    }

    /**
     * Gets the URL of $page in $lang.
     */
    public function getUrlIn($lang, $page, $pageURL) {
        if(!isset($this->languageShortNames[$lang]))
            throw new URLTranslatorException("unable to translate to language with id '$lang', is this really an id?");

        $langDir = $this->languageShortNames[$lang].'/';
        $dql = 'SELECT p FROM Cx\Model\ContentManager\Page p WHERE p.node = ' . $page->getNode()->getId() . ' and p.lang = ' . $lang;
        $q = $this->em->createQuery($dql);
        $q->setHint(\Doctrine\ORM\Query::HINT_REFRESH, true);
        $q->setMaxResults(1);

        $pages = $q->getResult();
        if(count($pages) == 0)
            throw new URLTranslatorException("unable to find a translation for page '" . $page->getTitle() . "' with id '" . $page->getId() . "' on node '".$page->getNode()->getId()."' to language with id '$lang'.");
               
        $targetPage = $pages[0];       
        $targetPath = $this->pageRepo->getPath($targetPage);

        return new URL($pageURL->getDomain().$langDir.$targetPath.$pageURL->getParams());
    }

    /**
     * Get a list of language change url placeholders
     *
     * Parses the language change placeholders which can be used to switch
     * to the corresponding pages of the current page in an other language.
     * Returns a two dimensional  array of which the key represents the placeholder
     * in the form LANG_CHANGE_EN (EN is the ISO 639-1 language code of the language)
     * and its value contains the actual url that links to the corresponding page
     * in the according language.
     * @param Cx\Model\ContentManager\Page
     * @param Cx\Core\Routing\URL
     * @return array ( name => content )
     */
    protected function buildPlaceholderArray($page, $pageURL) {
        $urls = $this->getUrlInAllLanguages($page, $pageURL);
        
        $placeholders = array();
        foreach($urls as $langId => $url) {
            $langName = $this->languageShortNames[$langId];
            $name = 'LANG_CHANGE_'.strtoupper($langName);
            $selectedName = 'LANG_SELECTED_'.strtoupper($langName);
            $content = ASCMS_PATH_OFFSET.'/'.$url->getPath();
            $placeholders[$name] = $content;
            $placeholders[$selectedName] = '';
        }
        $selectedName = 'LANG_SELECTED_'.strtoupper($this->languageShortNames[$page->getId()]);
        $placeholders[$selectedName] = 'selected';
        return $placeholders;
    }

    /**
     * 
     */
    public function replaceLanguagePlaceholdersIn($page, $pageURL, &$string) {
        $placeholders = $this->buildPlaceholderArray($page, $pageURL);
        foreach($placeholders as $placeholder => $link) {
            $string = str_replace('{'.$placeholder.'}', $link, $string);
        }
    }
}