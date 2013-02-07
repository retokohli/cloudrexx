<?php 

/**
 * SigmaPageTree
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_pagetree
 */

namespace Cx\Core\PageTree;

/**
 * SigmaPageTree
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_pagetree
 */
abstract class SigmaPageTree extends PageTree {
        protected $template = null;

    /**
     * @param $template the PEAR Sigma template.
     */
    public function setTemplate($template) {
        $this->template = $template;
    }
}
