<?php
require_once(ASCMS_CORE_PATH.'/pagetree/SigmaPageTree.class.php');
class NavigationPageTree extends SigmaPageTree {
    protected $topLevelBlockName = null;
    protected $output = '';

    const styleNameActive = "active";
    const styleNameNormal = "inactive";

    /**
     * @see PageTree::renderElement()
     */
    protected function renderElement($title, $level, $hasChilds, $lang, $path, $current, $page) {
        $blockName = 'level_'.$level;
        $hideLevel = false;
        $hasCustomizedBlock = $this->template->blockExists($blockName);
        if($hasCustomizedBlock) {
            if(!$this->topLevelBlockName) {
                $this->topLevelBlockName = $blockName;
            }                
        }
        else {
            if ($this->topLevelBlockName) {
                //checks for the standard block e.g. "level"
                if ($this->template->blockExists('level')) {
                    $blockName = 'level';
                } else {
                    $hideLevel = true;
                }
            }
        }
        // get the parent path
        try {
            $parentPath = $page->getParent()->getPath();
        } catch (\Cx\Model\ContentManager\PageException $e) {
            $parentPath = '/';
        }
        
        if($this->topLevelBlockName && !$hideLevel &&
                $this->isPagePathActive($parentPath, $lang) && $page->isVisible()) {
//TODO: invisible childs
//      maybe the return value of this function could set whether the childs
//      are rendered.
            $style = $current ? self::styleNameActive : self::styleNameNormal;
//TODO: navigation_id
            $this->template->setCurrentBlock($blockName);
            $this->template->setVariable(array(
                'URL' => ASCMS_PATH_OFFSET.$this->virtualLanguageDirectory.$path,
                'NAME' => $title,
                'TARGET' => $page->getLinkTarget(),
                'LEVEL_INFO' => $hasChilds ? '' : 'down',
                'STYLE' => $style,
                'CSS_NAME' => $page->getCssNavName()
            ));
            $this->template->parse($blockName);
            $this->output .= $this->template->get($blockName, true);
        }
    }

    protected function postRender($lang) {
        if($this->topLevelBlockName) {
            // replaces the top level block with the complete parsed navigation
            // this is because the Sigma Template system don't support nested blocks
            // with difference object based orders
            $this->template->replaceBlock($this->topLevelBlockName, $this->output, true);
            $this->template->touchBlock($this->topLevelBlockName);
            if ($this->template->blockExists('navigation')){
                $this->template->parse('navigation');
            }

            return $this->template->get();
        }
    }
}
