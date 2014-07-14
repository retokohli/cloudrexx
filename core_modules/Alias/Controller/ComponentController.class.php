<?php
/**
 * Main controller for Alias
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_alias
 */

namespace Cx\Core_Modules\Alias\Controller;

/**
 * Main controller for Alias
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_alias
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
        global $subMenuTitle, $_ARRAYLANG, $objTemplate;
        $subMenuTitle = $_ARRAYLANG['TXT_ALIAS_ADMINISTRATION'];

        $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'content_master.html');
        $objTemplate = $this->cx->getTemplate();

        \Permission::checkAccess(115, 'static');
        $objAliasManager = new \Cx\Core_Modules\Alias\Controller\AliasManager();
        $objAliasManager->getPage();
    }
}
