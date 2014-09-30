<?php

/**
 * Main controller for Search
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_search
 */

namespace Cx\Core_Modules\Search\Controller;

/**
 * Main controller for Search
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_search
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {

    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }

    public function preContentLoad(\Cx\Core\ContentManager\Model\Entity\Page $page)
    {
        $eventHandlerInstance = $this->cx->getEvents();
        $eventHandlerInstance->addEvent('SearchFindContent');
    }

    /**
     * Load your component.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $subMenuTitle, $objTemplate, $_CORELANG, $act;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                $pos = (isset($_GET['pos'])) ? intval($_GET['pos']) : '';
                $objSearch = new \Cx\Core_Modules\Search\Controller\Search();
                \Env::get('cx')->getPage()->setContent($objSearch->getPage($pos, \Env::get('cx')->getPage()->getContent()));
                break;
            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $subMenuTitle = $_CORELANG['TXT_SEARCH'];
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
                $cachedRoot = $this->cx->getTemplate()->getRoot();
                $this->cx->getTemplate()->setRoot($this->getDirectory() . '/View/Template/Backend');
                
                $objSearchManager = new \Cx\Core_Modules\Search\Controller\SearchManager($act, $objTemplate, $this->cx->getLicense());
                $objSearchManager->getPage();
                
                $this->cx->getTemplate()->setRoot($cachedRoot);        
                break;
            default:
                break;
        }
    }
}
