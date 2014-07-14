<?php

/**
 * Main controller for File Browser
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_filebrowser
 */

namespace Cx\Core_Modules\FileBrowser\Controller;

/**
 * Main controller for File Browser
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_filebrowser
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
        global $objTemplate;
        $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'content_master.html');
        $objTemplate = $this->cx->getTemplate();
        $objFileBrowser = new \Cx\Core_Modules\FileBrowser\Controller\FileBrowser();
        $objFileBrowser->getPage();
        exit;
    }

}