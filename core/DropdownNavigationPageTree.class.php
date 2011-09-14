<?php
require_once(ASCMS_CORE_PATH.'/SigmaPageTree.class.php');
class DropdownNavigationPageTree extends SigmaPageTree {
   
    protected $subNavTag = '<ul id="menubuilder%s" class="menu">{SUB_MENU}</ul>';

    const StyleNameActive = "active";
    const StyleNameNormal = "inactive";

    protected $output = '';

    protected $menuIndex = 0;
    protected $navigationIds = array();

    protected function renderElement($title, $level, $hasChilds, $lang, $path, $current, $page) {
        $output = '';

        if (!isset($this->navigationIds[$level])) 
            $this->navigationIds[$level] = 1;
        else
            $this->navigationIds[$level]++;

        $blockName = 'level_'.$level;
        if ($this->template->blockExists($blockName))
            $output = trim($this->template->_blocks[$blockName]);

        if(!empty($output)) {

            $style = '';
            if ($level == 1 && $current) {
                $style = 'starter_active';
            } elseif ($level == 1) {
                $style = 'starter_normal';                
            } else {
                if($current)
                    $style = self::StyleNameActive;
                else
                    $style = self::StyleNameNormal;
            }

//TODO: Display
            $output = str_replace('{CSS_NAME}', $page->getCssName(), $output);
            $output = str_replace('{TARGET}', $page->getTarget(), $output);
            $output = str_replace('{STYLE}', $style, $output);
            $output = str_replace('{URL}', ASCMS_PATH_OFFSET.$this->virtualLanguageDirectory.$path, $output);
            $output = str_replace('{NAME}', $title, $output);
            //            $output = str_replace('{TARGET}', $target, $output);
            $output = str_replace('{NAVIGATION_ID}', $this->navigationIds[$level], $output);
            //            $output = str_replace('{CSS_NAME}', $this->data[$id]['css_name'], $output);

            if($level > 1) { //this is a childpage, we need to replace the parent's SUB_MENU tag
                $this->output = str_replace("{SUB_MENU}", $output, sprintf($this->subNavTag, $this->menuIndex++)); //sprintf for js dropdown unique ID
            }
            else { //level 1 page
                $this->output .= $output; 
            }
        }
    }

    protected function postRender() {
        $this->output = str_replace('{SUB_MENU}', '', $this->output); //remove remaining sub_menu tags
        return $this->output;
    }
}