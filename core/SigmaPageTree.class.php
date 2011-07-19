<?php 
require_once(ASCMS_CORE_PATH.'/PageTree.class.php');

/*abstract */class SigmaPageTree extends PageTree {
        protected $template = null;

    /**
     * @param $template the PEAR Sigma template.
     */
    public function setTemplate($template) {
        $this->template = $template;
    }

}