<?php
namespace Cx\Core\Routing;

class ResolverException extends \Exception {};

/**
 * Takes an URL and tries to find the Page.
 */
class Resolver {
    protected $em = null;
    protected $url = null;
    /**
     * language id.
     * @var integer
     */
    protected $lang = null;

    /**
     * the page we found.
     * @var Cx\Model\ContentManager\Page
     */
    protected $page = null;

    /**
     * Doctrine PageRepository
     */
    protected $pageRepo = null;

    /**
     * Remembers if we've come across a redirection while resolving the URL.
     * This allow to properly redirect via 302.
     * @var boolean
     */
    protected $isRedirection = false;

    /**
     * Maps language ids to fallback language ids.
     * @var array ($languageId => $fallbackLanguageId)
     */
    protected $fallbackLanguages = null;

    /**
     * @param URL $url the url to resolve
     * @param integer $lang the language Id
     * @param $entityManager
     * @param string $pathOffset ASCMS_PATH_OFFSET
     * @param array $fallbackLangauges (languageId => fallbackLanguageId)
     * @param boolean $forceInternalRedirection does not redirect by 302 for internal redirections if set to true.
     *                this is used mainly for testing currently. 
     *                IMPORTANT: Do insert new parameters before this one if you need to and correct the tests.
     */
    public function __construct($url, $lang, $entityManager, $pathOffset, $fallbackLanguages, $forceInternalRedirection=false) {
        $this->url = $url;
        $this->em = $entityManager;
        $this->lang = $lang;
        $this->pathOffset = $pathOffset;
        $this->pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');
        $this->forceInternalRedirection = $forceInternalRedirection;

        $this->fallbackLanguages = $fallbackLanguages;

        $this->resolve();
    }

    /**
     * Does the resolving work, extends $this->url with targetPath and params.
     */
    protected function resolve() {
        $path = $this->url->getSuggestedTargetPath();

        //(I) see what the model has for us.
        $result = $this->pageRepo->getPagesAtPath($path, null, $this->lang);

        //(II) sort out errors
        if(!$result)
            throw new ResolverException('Unable to locate page (tried path ' . $path .').');

        if(!$result['page'])
            throw new ResolverException('Unable to locate page for this language. (tried path ' . $path .').');

        //(III) extend our url object with matched path / params
        $this->url->setTargetPath($result['matchedPath']);
        $this->url->setParams($result['unmatchedPath'] . $this->url->getSuggestedParams());

        $this->page = $result['page'];

        /*
          the page we found could be a redirection.
          in this case, the URL object is overwritten with the target details and
          resolving starts over again.
         */
        $target = $this->page->getTarget();
        $isRedirection = $this->page->getType() == 'redirect';
        if($target && $isRedirection) {
            if($this->page->isTargetInternal()) {
//TODO: add check for endless/circular redirection (a -> b -> a -> b ... and more complex)
                $nId = $this->page->getTargetNodeId();
                $lId = $this->page->getTargetLangId();
                $qs = $this->page->getTargetQueryString();

                $crit = array(
                    'node' => $nId
                );
                if($lId)
                    $crit['lang'] = $lId;
                else
                    $crit['lang'] = $this->lang;

                $targetPage = $this->pageRepo->findBy($crit);
                //revert to default language if we could not retrieve the current language
                if(!isset($targetPage[0])) { 
                    if($lId != 0) { //make sure we weren't already retrieving the default language
                        $crit['lang'] = $this->lang;
                        $targetPage = $this->pageRepo->findBy($crit);
                    }

                    //check whether we have a page now.
                    if(!isset($targetPage[0])) {
                        throw new ResolverException('Found invalid redirection target on page "'.$this->page->getTitle().'" with id "'.$this->page->getId().'": tried to find target page with node '.$nId.' and language '.$lId.', which does not exist.');
                    }
                }

                $targetPage = $targetPage[0];

                $targetPath = $this->pageRepo->getPath($targetPage);

                $this->url->setPath($targetPath.$qs);
                $this->isRedirection = true;
                $this->resolve();
            }
            else { //external target - redirect via HTTP 302
                header('Location: '.$target);
                die();
            }
        }
       
        //if we followed one or more redirections, the user shall be redirected by 302.
        if($this->isRedirection && !$this->forceInternalRedirection) {
            header('Location: '.$this->pageRepo->getURL($this->page, $this->pathOffset, $params));
            die();
        }

        //handle untranslated pages - replace them by the right language version.
        if($this->page->getType() == 'useFallback') {
            $langId = $this->fallbackLanguages[$this->page->getLang()];
            $fallbackPage = $this->page->getNode()->getPage($langId);
            if(!$fallbackPage)
                throw new ResolverException('Followed fallback page, but couldn\'t find content of fallback Language');

            $this->page->getFallbackContentFrom($fallbackPage);
        }
    }

    public function getPage() {
        return $this->page;
    }
}
