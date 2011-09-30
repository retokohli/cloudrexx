<?php
require_once(ASCMS_CORE_PATH.'/SigmaPageTree.class.php');

/**
 * Build nested navigation menu with unordered list
 * if [[nested_navigation]] is placed in navbar.
 * Formatting should be done with CSS.
 * Tags (ul and li) are inserted by the code.
 *
 * Navigation can be restricted to specific levels with the tag [[levels_AB]],
 * where A and B can take following values:
 *    starting level A: [1-9]
 *    ending level B: [1-9], [+] or [];
 *              [+]: any level starting from A;
 *              [] : just level A;
 *    examples: [[levels_24]] means navigation levels 2 to 4;
 *              [[levels_3+]] means any navigation levels starting from 3;
 *              [[levels_1]] means navigation level 1 only;
 */
class NestedNavigationPageTree extends SigmaPageTree {
    const CssPrefix = "menu_level_";
    const StyleNameActive = "active";
    const StyleNameNormal = "inactive";

    protected $levelFrom = 1;
    protected $levelTo = 0; //0 means unbounded
    protected $navigationIds = array();
    protected $listCompleteTree = false;

    protected $lastLevel = 0; //level of last item, used to remember how much closing tags we need.

    protected $branchNodeIds = array(); //holds all ids of the $currentPage's Node and it's parents

    public function __construct($entityManager, $maxDepth = 0, $activeNode = null, $lang = null, $currentPage = null) { 
        parent::__construct($entityManager, $maxDepth, $activeNode, $lang, $currentPage);

        //go up the branch and collect all node ids. used later in renderElement().
        $node = $currentPage->getNode();        
        while($node) {
            $this->branchNodeIds[] = $node->getId();
            $node = $node->getParent();
        }
    }
    
    protected function preRender() {
        // checks which levels to use
        // default is 1+ (all)
        $match = array();
        if (preg_match('/levels_([1-9])([1-9\+]*)(_full)?/', trim($this->template->_blocks['nested_navigation']), $match)) {
            $this->levelFrom = $match[1];
            if($match[2] != '+')
                $this->levelTo = intval($match[2]);
            $this->listCompleteTree = !empty($match[3]);
        }
    }
   
    protected function renderElement($title, $level, $hasChilds, $lang, $path, $current, $page) {
        //make sure the page to render is inside our branch
        if (!$this->isParentNodeInsideCurrentBranch($page->getNode())) {
            return '';
        }

        //are we inside the layer bounds?
        if (!$this->isLevelInsideLayerBound($level)) {
            return '';
        }

        if (!isset($this->navigationIds[$level]))
            $this->navigationIds[$level] = 0;
        else
            $this->navigationIds[$level]++;
        
        $block = trim($this->template->_blocks['level']);
        
        $output = "  <li>".$block;

        //only do uls for childs in current branch
        if ($hasChilds && $this->isNodeInsideCurrentBranch($page->getNode())) {
            $cssStyle = self::CssPrefix.($level+1);
            $output .= "\n<ul id='".$cssStyle."'>\n";
        }

        //check if we need to close any <ul>'s
        $this->lastLevel = $level;

        $style = $current ? self::StyleNameActive : self::StyleNameNormal;
        $output = str_replace('{NAME}', $title, $output);
        $output = str_replace('<li>', '<li class="'.$style.'">', $output);
        $output = str_replace('{URL}', ASCMS_PATH_OFFSET.$this->virtualLanguageDirectory.$path, $output);
        $output = str_replace('{TARGET}', $page->getLinkTarget(), $output);
        $output = str_replace('{CSS_NAME}',  $page->getCssName(), $output);
        $output = str_replace('{NAVIGATION_ID}', $this->navigationIds[$level], $output);

        return $output;
    }
    protected function postRenderElement($level, $hasChilds, $lang, $page)
    {
        //make sure the node to render is inside our branch
        if (!$this->isParentNodeInsideCurrentBranch($page->getNode())) {
            return '';
        }

        //are we inside the layer bounds?
        if (!$this->isLevelInsideLayerBound($level)) {
            return '';
        }

        $output = '';
        if ($hasChilds && $this->isNodeInsideCurrentBranch($page->getNode())) {
            $output .= "</ul>\n";
        }

        $output .= "  </li>\n";

        return $output;
    }

    private function isNodeInsideCurrentBranch($node)
    {
        if ($this->listCompleteTree) {
            return true;
        }

        return in_array($node->getId(), $this->branchNodeIds);
    }

    private function isParentNodeInsideCurrentBranch($node)
    {
        return $this->isNodeInsideCurrentBranch($node->getParent());
    }

    private function isLevelInsideLayerBound($level)
    {
        return    $level >= $this->levelFrom
               && (   $level <= $this->levelTo
                   || $this->levelTo == 0);
    }

    protected function renderHeader() {
        //wrap everyting in an <ul>
        return "<ul id='".self::CssPrefix.$this->levelFrom."'>\n";
    }
    protected function renderFooter() {
        //wrap everything in an <ul>
        $output .= "</ul>\n";

        return $output;
    }

    protected function getClosingTags($level = 0) {
        if($this->lastLevel == 0 || $level >= $this->lastLevel)
            return '';

        return str_repeat("\n</ul>\n</li>", $this->lastLevel - $level);
    }
}
