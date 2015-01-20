<?php

/**
 * Main controller for Login
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_login
 */

namespace Cx\Core_Modules\Login\Controller;

/**
 * Main controller for Login
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_login
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
        global $objTemplate, $sessionObj;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                if (!isset($sessionObj) || !is_object($sessionObj)) $sessionObj = \cmsSession::getInstance();
                $objLogin = new \Cx\Core_Modules\Login\Controller\Login(\Env::get('cx')->getPage()->getContent());
                $pageTitle = \Env::get('cx')->getPage()->getTitle();
                $pageMetaTitle = \Env::get('cx')->getPage()->getMetatitle();
                \Env::get('cx')->getPage()->setContent($objLogin->getContent($pageMetaTitle, $pageTitle));
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                if (\FWUser::getFWUserObject()->objUser->login(true)) {
                    \Cx\Core\Csrf\Controller\Csrf::header('location: index.php');
                }
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
                $objTemplate = $this->cx->getTemplate();
                $objLoginManager = new \Cx\Core_Modules\Login\Controller\LoginManager();
                $objLoginManager->getPage();
                break;

            default:
                break;
        }
    }

}
