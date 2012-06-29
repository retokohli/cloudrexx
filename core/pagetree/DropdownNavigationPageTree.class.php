<?php
require_once(ASCMS_CORE_PATH.'/pagetree/SigmaPageTree.class.php');
class DropdownNavigationPageTree extends SigmaPageTree {
   
    protected $subNavTag = '<ul id="menubuilder%s" class="menu">{SUB_MENU}</ul>';
    private $cache = array();
    private $previousLevel = 1;

    const StyleNameActive = "active";
    const StyleNameNormal = "inactive";
    const StyleNameActiveStarter = 'starter_active';
    const StyleNameNormalStarter = 'starter_normal';

    protected $menuIndex = 0;
    protected $navigationIds = array();

    protected function renderElement($title, $level, $hasChilds, $lang, $path, $current, $page) {
        $output = '';
        $isSubNavigation = false;

        $blockName = 'level_'.$level;
        $childBlockName = 'level_'.($level + 1);
        $parentBlockName = 'level_'.($level - 1);

        // check if there is a html-template present for the currently parsed level
        // if not, there is no point on going any further from here
        if (!$this->template->blockExists($blockName)) {
            return;
        }

        // check if we're parsing a subnavigation point and if the parent block
        // even contains the {SUB_MENU} placeholder.
        // if not, we do obviously not wanna parse this level (aka subnavigation)
        if ($level > 1) {
            if ($this->template->blockExists($parentBlockName)) {
                if (strpos($this->template->_blocks[$parentBlockName], '{SUB_MENU}') === false) {
                    return;
                }
            } else {
                // if current page is a child node, but its parent won't be parsed, then we shall not parse the page
                return;
            }
            $isSubNavigation = true;
        }

        $output = trim($this->template->_blocks[$blockName]);

        // in case the template block used for parsing the current navigation is empty,
        // we can stop parsing the current level.
        if (empty($output)) {
            return;
        }

        // set navigation IDs
        if (!isset($this->navigationIds[$level])) {
            $this->navigationIds[$level] = 1;
        } else { 
            $this->navigationIds[$level]++;
        }

        //\DBG::msg('PAGE: '.$title);
        //\DBG::msg('LEVEL '.$level.' TEMPLATE: '.$output);

        if ($level == 1 && $current) {
            $style = self::StyleNameActiveStarter;
        } elseif ($level == 1) {
            $style = self::StyleNameNormalStarter;
        } elseif ($current) {
            $style = self::StyleNameActive;
        } else {
            $style = self::StyleNameNormal;
        }

        // parse navigation entry
        $output = str_replace('{NAME}', contrexx_raw2xhtml($title), $output);
        $output = str_replace('{URL}', ASCMS_PATH_OFFSET.$this->virtualLanguageDirectory.contrexx_raw2encodedUrl($path), $output);
        $output = str_replace('{TARGET}', $page->getLinkTarget(), $output);
        $output = str_replace('{CSS_NAME}', $page->getCssNavName(), $output);
        $output = str_replace('{NAVIGATION_ID}', $this->navigationIds[$level], $output);
        $output = str_replace('{STYLE}', $style, $output);

        $this->injectParsedSubnavigations($level);

        if ($this->previousLevel < $level) {
            // we're descending (moving down)
            // we're parsing the first page of a subnavigation

            $this->cache[$blockName] = str_replace("{SUB_MENU}", $output.'{NEXT_MENU}', sprintf($this->subNavTag, $this->menuIndex++)); //sprintf for js dropdown unique ID
            //\DBG::msg('__first SUB ('.$level.'): '.$this->cache[$blockName]);
        } else {
            // we're parsing the next page on the same level (first page of this level has already been parsed)

            if ($isSubNavigation) {
                // parsing a page on a subnavigation level > 1
                $this->cache[$blockName] = str_replace("{NEXT_MENU}", $output.'{NEXT_MENU}', $this->cache[$blockName]);
                //\DBG::msg('__additional SUB-PAGE ('.$level.'): '.$this->cache[$blockName]);
            } else {
                // parsing page on main navigation level = 1
                if (!isset($this->cache[$blockName])) {
                    $this->cache[$blockName] = '';
                }
                $this->cache[$blockName] .= $output;
                //\DBG::msg('__additional PAGE ('.$level.'): '.$this->cache[$blockName]);
            }
        }

        // remember the currently parsed level.
        // we will need this information when parsing the next page to determine
        // if we're going to parse a subnavigation or not
        $this->previousLevel = $level;
    }

    /**
     * @todo: add docbloc
     */
    private function injectParsedSubnavigations($currentLevel = 1)
    {
        for ($level = $this->previousLevel; $level >= $currentLevel; $level--) {
            $this->injectSubnavigation($level, 'level_'.$level, 'level_'.($level+1));
        }
    }

    /**
     * This method fills out the placeholder {SUB_MENU} of the previous parsed
     * page.
     *
     * @access private
     * @param   int Level of the page within the content structure (1 = first level, 2 = second level...)
     * @param   string  Name of the template block used for the selected level $level (i.e. 'level_1')
     * @param   string  Name of the template block used by the child pages of the selected level $level (i.e. 'level_2')
     */
    private function injectSubnavigation($level, $blockName, $childBlockName)
    {
        if ($this->previousLevel > $level) {
            // we're ascending (moving up) > we just parsed the subnavigation of the previous page on the same level
            // therefore, we'll have to inject the subnavigation of the previous page into that pages html code

            if (isset($this->cache[$childBlockName])) {
                // remove SUB_MENU placeholder from finished parsed sub-pages
                $subNavigationOfPreviousPage = str_replace(array('{NEXT_MENU}', '{SUB_MENU}'), '', $this->cache[$childBlockName]);
                unset($this->cache[$childBlockName]);

                // inject SUB_MENU into previously parsed page of the same level
                $this->cache[$blockName] = str_replace("{SUB_MENU}", $subNavigationOfPreviousPage, $this->cache[$blockName]);
                //\DBG::msg("INJECT SUBNAVIGATION of LEVEL {$this->previousLevel} INTO PREVIOUS PAGE of LEVEL $level");
                //\DBG::msg("CURRENT LEVEL: ".$this->cache[$blockName]);
            }
        } elseif ($this->previousLevel == $level) {
            //\DBG::msg('STRIP SUB_MENU from previous PAGE of level '.$level);
            if (isset($this->cache[$blockName])) {
                $this->cache[$blockName] = str_replace("{SUB_MENU}", '', $this->cache[$blockName]);
            }
        }
    }
    
    protected function postRender($lang)
    {
        $this->injectParsedSubnavigations();
        return str_replace('{SUB_MENU}', '', $this->cache['level_1']); //remove remaining sub_menu tags
    }
}
