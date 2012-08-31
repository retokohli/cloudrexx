<?php 

namespace Cx\Core\PageTree;

abstract class SigmaPageTree extends PageTree {
        protected $template = null;

    /**
     * @param $template the PEAR Sigma template.
     */
    public function setTemplate($template) {
        $this->template = $template;
    }
}
