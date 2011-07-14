<?php
/**
 * Base class for all kinds of trees such as Sitemaps and Navigation.
 */
abstract class PageTree {

    //the language id
    protected $lang = null;
    protected $rootNode = null;
    protected $depth = null;
    protected $em = null;
    
    /**
     * @param $entityManager the doctrine em
     * @param int $maxDepth maximum depth to fetch, 0 means everything
     * @param \Cx\Model\ContentManager\Node $rootNode node to use as root
     * @param int $lang the language. will render all if unset
     */
    public function __construct($entityManager, $maxDepth = 0, $rootNode = null, $lang = null) {
        $this->lang = $lang;
        $this->rootNode = $rootNode;
        $this->depth = $maxDepth;
        $this->em = $entityManager;

        $this->fetchTree();

        $this->init(); //user initializations
    }

    private function fetchTree() {
        $repo = $this->em->getRepository('Cx\Model\ContentManager\Page');

        $this->tree = $repo->getTreeByTitle($this->rootNode, $this->lang, true);
    }

    /**
     * returns the string representation of the tree.
     *
     * @return string
     */
    public function render() {
        $content = $this->renderHeader(); 
        $content += $this->internalRender($this->tree, '');
        $content += $this->renderFooter();
        return $content;
    }

    private function internalRender(&$elems, $path, $level = 0) {
        $content = '';
        foreach($elems as $title => &$elem) {
            $hasChilds = isset($elem['__childs']);
            $lang = $elem['lang'];
            $pathOfThis = $path.'/'.$title;
            $content += $this->renderElement($title, $level, $hasChilds, $lang, $pathOfThis);

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
     *
     * @return string your string representation of the element.
     */           
    abstract protected function renderElement($title, $level, $hasChilds, $lang, $path);

    protected function renderHeader($lang) {
        return '';
    }
    protected function renderFooter($lang) {
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