<?php
/**
 * Main controller for GuestBook
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package contrexx
 * @subpackage module_guestbook
 */

namespace Cx\Modules\GuestBook\Controller;

/**
 * Main controller for GuestBook
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package contrexx
 * @subpackage module_guestbook
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    /**
     * getControllerClasses
     * 
     * @return type
     */
    public function getControllerClasses() {
        return array();
    }

     /**
     * Load the component GuestBook.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $objTemplate, $_CORELANG, $subMenuTitle;
                
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:               
                $objGuestbook = new GuestBook(\Env::get('cx')->getPage()->getContent());
                \Env::get('cx')->getPage()->setContent($objGuestbook->getPage());
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
                $objTemplate = $this->cx->getTemplate();

                \Permission::checkAccess(9, 'static');
                $subMenuTitle = $_CORELANG['TXT_GUESTBOOK'];
                $objGuestbook = new GuestBookManager();
                $objGuestbook->getPage();
                break;
                
            default:
                break;
        }
    }

}
