<?php

/**
 * DefaultController
 *
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage  module_mediabrowser
 */

namespace Cx\Core_Modules\MediaBrowser\Controller;

/**
 * DefaultController Description
 *
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage coremodule_mediabrowser
 */
class DefaultController extends \Cx\Core\Core\Model\Entity\Controller {

    /**
     * DefaultController for the DefaultView
     * 
     * @param \Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController
     * @param \Cx\Core\Core\Controller\Cx $cx
     * @param \Cx\Core\Html\Sigma $template
     * @param type $submenu
     */
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController, \Cx\Core\Core\Controller\Cx $cx, \Cx\Core\Html\Sigma $template, $submenu = null) {
        parent::__construct($systemComponentController, $cx);
        $this->template = $template;
        $this->cx = $cx;

        $this->showDefault();  
    }

    public function showDefault() {
        $mediaBrowser = \Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserController::initialize();

        // mithilfe des Objekts sollen die Attribute generiert werden können
        
        
        //$mediaBrowser->setAttr('startview', 'sitestructure');
        //$this->template->setVariable("MB_EXAMPLE_BUTTON", $mediaBrowser->getButton('Test'));        
    }

}
