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
    /**
     * @var \Cx\Core\Html\Sigma
     */
    protected $template = null;

    /**
     * @param $template the PEAR Sigma template.
     */
    public function setTemplate($template) {
        $this->template = $template;
    }
    protected function preRender($lang) {
        if ($this->template->placeholderExists('LEVELS_FULL') || $this->template->placeholderExists('levels_full')) {
            $this->rootNode = \Env::get('em')->getRepository('Cx\Core\ContentManager\Model\Entity\Node')->getRoot();
        }
        $this->realPreRender($lang);
    }
    
    protected abstract function realPreRender($lang);
}
