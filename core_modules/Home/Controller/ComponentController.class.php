<?php
/**
 * Main controller for Home
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_home
 */

namespace Cx\Core_Modules\Home\Controller;

/**
 * Main controller for Home
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_home
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
        global $_CORELANG, $subMenuTitle, $objTemplate;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'content_master.html');
                $cachedRoot = $this->cx->getTemplate()->getRoot();
                $this->cx->getTemplate()->setRoot($this->getDirectory() . '/View/Template/Backend');

                $objTemplate->setVariable('CONTAINER_DASHBOARD_CLASS', 'dashboard');
                $objFWUser = \FWUser::getFWUserObject();
                $subMenuTitle = $_CORELANG['TXT_WELCOME_MESSAGE'].", <a href='index.php?cmd=Access&amp;act=user&amp;tpl=modify&amp;id=".$objFWUser->objUser->getId()."' title='".$objFWUser->objUser->getId()."'>".($objFWUser->objUser->getProfileAttribute('firstname') || $objFWUser->objUser->getProfileAttribute('lastname') ? htmlentities($objFWUser->objUser->getProfileAttribute('firstname'), ENT_QUOTES, CONTREXX_CHARSET).' '.htmlentities($objFWUser->objUser->getProfileAttribute('lastname'), ENT_QUOTES, CONTREXX_CHARSET) : htmlentities($objFWUser->objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET))."</a>";
                $objAdminNav = new Home();
                $objAdminNav->getPage();

                $this->cx->getTemplate()->setRoot($cachedRoot);
                break;
        }
    }
}