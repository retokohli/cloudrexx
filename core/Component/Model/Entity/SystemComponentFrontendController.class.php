<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Cx\Core\Component\Model\Entity;
/**
 * Description of SystemComponentFrontendController
 *
 * @author ritt0r
 */
abstract class SystemComponentFrontendController extends Controller {
    
    public function getPage(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page) {
        // init component template
        $componentTemplate = new \Cx\Core\Html\Sigma('.');
        $componentTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $componentTemplate->setTemplate($page->getContent());
        
        // parse page
        $this->parsePage($componentTemplate, $cx);
        $page->setContent($componentTemplate->get());
    }
    
    public abstract function parsePage(\Cx\Core\Html\Sigma $template, \Cx\Core\Cx $cx);
}
