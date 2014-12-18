<?php
/**
 * Main controller for Contact
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_contact
 */

namespace Cx\Core_Modules\Contact\Controller;

/**
 * Main controller for Contact
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_contact
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
        global $moduleStyleFile, $objTemplate, $_CORELANG, $subMenuTitle;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                $contactObj = new \Cx\Core_Modules\Contact\Controller\Contact(\Env::get('cx')->getPage()->getContent());
                \Env::get('cx')->getPage()->setContent($contactObj->getContactPage());
                $moduleStyleFile = $this->cx->getCodeBaseOffsetPath() . self::getPathForType($this->getType()) . '/' . $this->getName() . '/View/Style/frontend_style.css';
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
                $objTemplate = $this->cx->getTemplate();

                \Permission::checkAccess(84, 'static');
                $subMenuTitle = $_CORELANG['TXT_CONTACTS'];
                $objContact = new \Cx\Core_Modules\Contact\Controller\ContactManager();
                $objContact->getPage();
                break;

            default:
                break;
        }
    }
}
