<?php

/**
 * Main controller for ContentWorkflow
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_contentworkflow
 */

namespace Cx\Core\ContentWorkflow\Controller;

/**
 * Main controller for ContentWorkflow
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_contentworkflow
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {

    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }

    /**
     * Load your component.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $subMenuTitle, $_ARRAYLANG, $act, $objTemplate, $objDatabase, $objInit;
        $subMenuTitle = $_ARRAYLANG['TXT_CONTENT_HISTORY'];

        $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
        $cachedRoot = $this->cx->getTemplate()->getRoot();
        $this->cx->getTemplate()->setRoot($this->getDirectory() . '/View/Template/Backend');

        \Permission::checkAccess(21, 'static');
        $objViewManager = new \Cx\Core\ContentWorkflow\Controller\ContentWorkflow($act, $objTemplate, $objDatabase, $objInit);
        $objViewManager->getPage();

        $this->cx->getTemplate()->setRoot($cachedRoot);
    }

}