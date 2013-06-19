<?php
/**
 * Frontend controller to easely create a frontent view
 * @author Michael Ritter <michael.ritter@comvation.com>
 */

namespace Cx\Core\Core\Model\Entity;

/**
 * Frontend controller to easely create a frontent view
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
abstract class SystemComponentFrontendController extends Controller {
    
    /**
     * This is called by the default ComponentController and does all the repeating work
     * 
     * This creates a template of the page content and calls parsePage($template)
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page Resolved page
     */
    public function getPage(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        // init component template
        $componentTemplate = new \Cx\Core\Html\Sigma('.');
        $componentTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $componentTemplate->setTemplate($page->getContent());
        
        // parse page
        $this->parsePage($componentTemplate);
        $page->setContent($componentTemplate->get());
    }
    
    /**
     * Use this to parse your frontend page
     * 
     * You will get a template based on the content of the resolved page
     * You can access Cx class using $this->cx
     * To show messages, use \Message class
     * @param \Cx\Core\Html\Sigma $template Template containing content of resolved page
     */
    public abstract function parsePage(\Cx\Core\Html\Sigma $template);
}
