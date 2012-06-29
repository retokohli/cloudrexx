<?php
require_once(ASCMS_CORE_PATH.'/pagetree/SigmaPageTree.class.php');
class SitemapPageTree extends SigmaPageTree {
    protected $spacer = null;
    const cssPrefix = "sitemap_level";
    const subTagStart = "<ul>";
    const subTagEnd = "</ul>";
   
    protected function renderHeader() {
    }
    protected function renderElement($title, $level, $hasChilds, $lang, $path) {
        $width = $level*25;
        $spacer = "<img src='".ASCMS_MODULE_IMAGE_WEB_PATH."/sitemap/spacer.gif' width='$width' height='12' alt='' />";
        $this->template->setVariable(array(
            'STYLE'     => self::cssPrefix .'_' . $level,
            'SPACER'    => $spacer,
            'NAME'      => $title,
//TODO: set TARGET
            //            'TARGET'    => $this->_sitemapPageTarget[$key],
            'URL'       => ASCMS_PATH_OFFSET.$this->virtualLanguageDirectory.$path
        ));
        
        $this->template->parse('sitemap');
    }
    protected function renderFooter() {
    }
}