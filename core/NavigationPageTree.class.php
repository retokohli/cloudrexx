<?php
require_once(ASCMS_CORE_PATH.'/SigmaPageTree.class.php');
class NavigationPageTree extends SigmaPageTree {
    protected $topLevelBlockName = null;
    protected $output = '';

    const styleNameActive = "active";
    const styleNameNormal = "inactive";

    protected function renderElement($title, $level, $hasChilds, $lang, $path, $current) {
        $blockName = 'level_'.$level;
        $hideLevel = false;
        $hasCustomizedBlock = $this->template->blockExists($blockName);
        if($hasCustomizedBlock) {
            if(!$this->topLevelBlockName) {
                $this->topLevelBlockName = $blockName;
            }                
        }
        else {
            if($this->topLevelBlockName) {
                //checks for the standard block e.g. "level"
                if ($this->template->blockExists('level'))
                    $blockName = 'level';
                else
                    $hideLevel = true;
            }
        }

        if($this->topLevelBlockName && !$hideLevel) {
            $style = $current ? self::styleNameActive : self::styleNameNormal;
//TODO: target
//TODO: display
//TODO: navigation_id
//TODO: css_name
            $this->template->setCurrentBlock($blockName);
            $this->template->setVariable(array(
                                               'URL' => $path,
                                               'NAME' => $title,
                                               'TARGET' => '_self',
                                               'LEVEL_INFO' => $hasChilds ? '' : 'down',
                                               'STYLE' => $style
            ));
            $this->template->parse($blockName);
            $this->output .= $this->template->get($blockName, true);
        }
    }

    protected function postRender() {
        if($this->topLevelBlockName) {
            // replaces the top level block with the complete parsed navigation
            // this is because the Sigma Template system don't support nested blocks
            // with difference object based orders
            $this->template->replaceBlock($this->topLevelBlockName, $this->output, true);
            $this->template->touchBlock($this->topLevelBlockName);
            if ($this->template->blockExists('navigation')){
                $this->template->parse('navigation');
            }
        }
    }
}