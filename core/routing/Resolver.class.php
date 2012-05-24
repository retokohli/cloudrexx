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
     * Doctrine NodeRepository
     */
    protected $nodeRepo = null;

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
     * Contains the resolved module name (if any, empty string if none)
     * @var String
     */
    protected $section = '';
    
    /**
     * Contains the resolved module command (if any, empty string if none)
     * @var String
     */
    protected $command = '';
    
    /**
     * Remembers if it's a page preview.
     * @var boolean
     */
    protected $pagePreview = 0;
    
    /**
     * Contains the history id to revert the page to an older version.
     * @var int
     */
    protected $historyId = 0;
    
    /**
     * Contains the page array from the session.
     * @var array
     */
    protected $sessionPage = array();
    
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
        $this->init($url, $lang, $entityManager, $pathOffset, $fallbackLanguages, $forceInternalRedirection);
    }
    
    
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
    public function init($url, $lang, $entityManager, $pathOffset, $fallbackLanguages, $forceInternalRedirection=false) {
        $this->url = $url;
        $this->em = $entityManager;
        $this->lang = $lang;
        $this->pathOffset = $pathOffset;
        $this->pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');
        $this->nodeRepo = $this->em->getRepository('Cx\Model\ContentManager\Node');
        $this->forceInternalRedirection = $forceInternalRedirection;
        $this->fallbackLanguages = $fallbackLanguages;
        $this->pagePreview = !empty($_GET['pagePreview']) && ($_GET['pagePreview'] == 1) ? 1 : 0;
        $this->historyId = !empty($_GET['history']) ? $_GET['history'] : 0;
        $this->sessionPage = !empty($_SESSION['page']) ? $_SESSION['page'] : array();
    }
    
    /**
     * Checks for alias request
     * @return Page or null
     */
    public function resolveAlias() {
        // This is our alias, if any
        $path = $this->url->getSuggestedTargetPath();

        //(I) see what the model has for us, aliases only.
        $result = $this->pageRepo->getPagesAtPath($path, null, null, false, \Cx\Model\ContentManager\Repository\PageRepository::SEARCH_MODE_ALIAS_ONLY);
        
        //(II) sort out errors
        if(!$result) {
            // no alias
            return null;
        }

        if(!$result['pages']) {
            // no alias
            return null;
        }
        if (count($result['pages']) != 1) {
            throw new ResolverException('Unable to match a single page for this alias (tried path ' . $path . ').');
        }
        $page = current($result['pages']);

        $this->page = $page;
        
        return $this->page;
    }

    /**
     * Does the resolving work, extends $this->url with targetPath and params.
     */
    public function resolve($internal = false) {
        $path = $this->url->getSuggestedTargetPath();

        if (!$this->page || $internal) {
            //(I) see what the model has for us, including aliases.
            $result = $this->pageRepo->getPagesAtPath($path, null, $this->lang, false, \Cx\Model\ContentManager\Repository\PageRepository::SEARCH_MODE_PAGES_ONLY);

            if ($this->pagePreview && !empty($this->sessionPage)) {
                $result['page'] = $this->getPreviewPage();
                
                $tree   = $this->pageRepo->getTreeByTitle(null, $this->lang, true, true, \Cx\Model\ContentManager\Repository\PageRepository::SEARCH_MODE_PAGES_ONLY);
                $pathes = $this->pageRepo->getPathes($path, $tree, false);
                
                $result['matchedPath']   = !empty($pathes['matchedPath'])   ? $pathes['matchedPath']   : '';
                $result['unmatchedPath'] = !empty($pathes['unmatchedPath']) ? $pathes['unmatchedPath'] : '';
            }

            //(II) sort out errors
            if(!$result) {
                throw new ResolverException('Unable to locate page (tried path ' . $path .').');
            }

            if(!$result['page']) {
                throw new ResolverException('Unable to locate page for this language. (tried path ' . $path .').');
            }

            // If an older revision was requested, revert to that in-place:
            if (!empty($this->historyId) && \Permission::checkAccess(6, 'static', true)) {
                $logRepo = $this->em->getRepository('Gedmo\Loggable\Entity\LogEntry');

                $logRepo->revert($result['page'], $this->historyId);
            }
            
            //(III) extend our url object with matched path / params
            $this->url->setTargetPath($result['matchedPath']);
            $this->url->setParams($result['unmatchedPath'] . $this->url->getSuggestedParams());

            $this->page = $result['page'];
        }
        /*
          the page we found could be a redirection.
          in this case, the URL object is overwritten with the target details and
          resolving starts over again.
         */
        $target = $this->page->getTarget();
        $isRedirection = $this->page->getType() == \Cx\Model\ContentManager\Page::TYPE_REDIRECT;
        $isAlias = $this->page->getType() == \Cx\Model\ContentManager\Page::TYPE_ALIAS;
        
        //handles alias redirections internal / disables external redirection
        $this->forceInternalRedirection = $this->forceInternalRedirection || $isAlias;
        
        if($target && ($isRedirection || $isAlias)) {
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

                $targetPath = substr($targetPage->getPath(), 1);

                $this->url->setPath($targetPath.$qs);
                $this->isRedirection = true;
                $this->resolve(true);
            }
            else { //external target - redirect via HTTP 302
                header('Location: '.$target);
                die();
            }
        }
        
        //if we followed one or more redirections, the user shall be redirected by 302.
        if($this->isRedirection && !$this->forceInternalRedirection) {
            header('Location: '.$this->page->getURL($this->pathOffset, ''));
            die();
        }
        
        $this->handleFallbackContent($this->page);
        
        if ($this->page->getType() == \Cx\Model\ContentManager\Page::TYPE_APPLICATION
                || $this->page->getType() == \Cx\Model\ContentManager\Page::TYPE_FALLBACK) {
            $this->command = $this->page->getCmd();
            $this->section = $this->page->getModule();
        }
    }

    /**
     * Returns the preview page built from the session page array.
     * @return Cx\Model\ContentManager\Page $page
     */
    private function getPreviewPage() {
        $data = $this->sessionPage;
        
        $page = $this->pageRepo->findOneById($data['pageId']);
        if (!$page) {
            $page = new \Cx\Model\ContentManager\Page();
            $node = new \Cx\Model\ContentManager\Node();
            $node->setParent($this->nodeRepo->getRoot());
            $this->nodeRepo->getRoot()->addChildren($node);
            $node->addPage($page);
            $page->setNode($node);
            
            $this->pageRepo->addVirtualPage($page);
        }
        
        unset($data['pageId']);
        $page->setLang(\FWLanguage::getLanguageIdByCode($data['lang']));
        unset($data['lang']);
        $page->updateFromArray($data);
        $page->setUpdatedAtToNow();
        $page->setActive(true);
        $page->setLinkTarget('?pagePreview=1');
        $page->setVirtual(true);
        $page->validate();
        
        return $page;
    }

    /**
     * Checks whether $page is of type 'fallback'. Loads fallback content if yes.
     * @param Cx\Model\ContentManager $page
     * @throws ResolverException
     */
    public function handleFallbackContent($page) {
        //handle untranslated pages - replace them by the right language version.
        if($page->getType() == \Cx\Model\ContentManager\Page::TYPE_FALLBACK) {
            $langId = $this->fallbackLanguages[$page->getLang()];
            $fallbackPage = $page->getNode()->getPage($langId);
            if(!$fallbackPage)
                throw new ResolverException('Followed fallback page, but couldn\'t find content of fallback Language');

            $page->getFallbackContentFrom($fallbackPage);
            $this->resolve(true);
        }
    }

    public function getPage() {
        return $this->page;
    }
    
    public function getURL() {
        return $this->url;
    }
    
    /**
     * Returns the resolved module name (if any, empty string if none)
     * @return String Module name
     */
    public function getSection() {
        return $this->section;
    }
    
    /**
     * Returns the resolved module command (if any, empty string if none)
     * @return String Module command
     */
    public function getCmd() {
        return $this->command;
    }
    
    /**
     * Sets the value of the resolved module name and command
     * This should not be called from any (core_)module!
     * For legacy requests only!
     * 
     * @param String $section Module name
     * @param String $cmd Module command
     * @todo Remove this method as soon as legacy request are no longer possible
     */
    public function setSection($section, $command = '') {
        $this->section = $section;
        $this->command = $command;
    }
}
