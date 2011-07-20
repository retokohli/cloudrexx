<?php
use Doctrine\Common\Util\Debug as DoctrineDebug;

/**
 * Base class for all kinds of trees such as Sitemaps and Navigation.
 */
/*abstract */class PageTree {

    //the language id
    protected $lang = null;
    protected $rootNode = null;
    protected $depth = null;
    protected $em = null;
    protected $currentPage = null;
    protected $currentPagePath = null;
    protected $pageRepo = null;
    
    /**
     * @param $entityManager the doctrine em
     * @param int $maxDepth maximum depth to fetch, 0 means everything
     * @param \Cx\Model\ContentManager\Node $rootNode node to use as root
     * @param int $lang the language. will render all if unset
     * @param \Cx\Model\ContentManager\Node $currentPage if set, renderElement() will receive a correctly set $current flag.
     */
    public function __construct($entityManager, $maxDepth = 0, $rootNode = null, $lang = null, $currentPage = null) {
        $this->lang = $lang;
        $this->rootNode = $rootNode;
        $this->depth = $maxDepth;
        $this->em = $entityManager;
        $this->currentPage = $currentPage;

        $this->pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');

        $this->fetchTree();
        if($this->currentPage)
            $this->currentPagePath = $this->pageRepo->getPath($currentPage);
        $this->init(); //user initializations
    }

    private function fetchTree() {
        $this->tree = $this->pageRepo->getTreeByTitle($this->rootNode, $this->lang, true);
    }

    /**
     * returns the string representation of the tree.
     *
     * @return string
     */
    public function render() {
        $content = $this->addContentIfPresent($this->preRender($this->lang));
        $content .= $this->addContentIfPresent($this->renderHeader($this->lang)); 
        $content .= $this->addContentIfPresent($this->internalRender($this->tree, ''));
        $content .= $this->addContentIfPresent($this->renderFooter($this->lang));
        $content .= $this->addContentIfPresent($this->postRender($this->lang));
        return $content;
    }

    private function addContentIfPresent($content) {
        if($content)
            return $content;
        return null;
    }

    private function internalRender(&$elems, $path, $level = 1) {
        $content = '';
        foreach($elems as $title => &$elem) {
            $hasChilds = isset($elem['__childs']);
            $lang = $elem['__data']['lang'];
            $pathOfThis = $path.'/'.$title;
            $current = false;

            if($this->currentPagePath) { //current flag requested
                //are we rendering a parent page of currentPage or the currenPage itself?
                $current = substr($this->currentPagePath, 0, strlen($pathOfThis)) == $pathOfThis;                
            }

            $content .= $this->renderElement($title, $level, $hasChilds, $lang, $pathOfThis, $current);

            if($hasChilds)
                $content += $this->internalRender($elem['__childs'], $pathOfThis, $level+1);
        }
        return $content;

    }

    /**
     * Override this to do your representation of the tree.
     *
     * @param string $title
     * @param int $level 0-based level of the element
     * @param boolean $hasChilds are there children of this element? if yes, they will be processed in the subsequent calls.
     * @param int $lang language id
     * @param string $path path to this element, e.g. '/CatA/CatB'
     * @param boolean $current if a $currentPage has been specified, this will be set to true if either a parent element of the current element or the current element itself is rendered.
     *
     * @return string your string representation of the element.
     */           
    /*abstract */protected function renderElement($title, $level, $hasChilds, $lang, $path, $current){
        return ''; //workaround, abstract fucks things up somehow
    }

    protected function renderHeader($lang) {
        return '';
    }
    protected function renderFooter($lang) {
        return '';
    }
    protected function preRender($lang) {
        return '';
    }
    protected function postRender($lang) {
        return '';
    }

    /**
     * Called on construction. Override if you do not want to override the ctor.
     */
    protected function init() {
    }

    /**
     * Add more callbacks like renderElement (e.g. preLevel, postLevel) when needed.
     */
}