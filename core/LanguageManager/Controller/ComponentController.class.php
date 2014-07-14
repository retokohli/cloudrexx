<?php
/**
 * Main controller for Language Manager
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_languagemanager
 */

namespace Cx\Core\LanguageManager\Controller;

/**
 * Main controller for Language Manager
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_languagemanager
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
        global $subMenuTitle, $_ARRAYLANG;
        $subMenuTitle = $_ARRAYLANG['TXT_LANGUAGE_SETTINGS'];

        $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'content_master.html');
        $cachedRoot = $this->cx->getTemplate()->getRoot();

        \Permission::checkAccess(22, 'static');
        $objLanguageManager = new \Cx\Core\LanguageManager\Controller\LanguageManager();
        $objLanguageManager->getLanguagePage();
                
        $this->cx->getTemplate()->setRoot($cachedRoot);        
    }
    
    /**
     * Do something after resolving is done
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function postResolve(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                global $objInit, $_LANGID, $_FRONTEND_LANGID, $_CORELANG, $_ARRAYLANG, $plainCmd;

                $objInit->_initBackendLanguage();
                $objInit->getUserFrontendLangId();

                $_LANGID = $objInit->getBackendLangId();
                $_FRONTEND_LANGID = $objInit->userFrontendLangId;
                /**
                 * Language constants
                 *
                 * Defined as follows:
                 * - BACKEND_LANG_ID is set to the visible backend language
                 *   in the backend *only*.  In the frontend, it is *NOT* defined!
                 *   It indicates a backend user and her currently selected language.
                 *   Use this in methods that are intended *for backend use only*.
                 *   It *MUST NOT* be used to determine the language for any kind of content!
                 * - FRONTEND_LANG_ID is set to the selected frontend or content language
                 *   both in the back- and frontend.
                 *   It *always* represents the language of content being viewed or edited.
                 *   Use FRONTEND_LANG_ID for that purpose *only*!
                 * - LANG_ID is set to the same value as BACKEND_LANG_ID in the backend,
                 *   and to the same value as FRONTEND_LANG_ID in the frontend.
                 *   It *always* represents the current users' selected language.
                 *   It *MUST NOT* be used to determine the language for any kind of content!
                 * @since 2.2.0
                 */
                define('FRONTEND_LANG_ID', $_FRONTEND_LANGID);
                define('BACKEND_LANG_ID', $_LANGID);
                define('LANG_ID', $_LANGID);

                /**
                 * Core language data
                 * @ignore
                 */
                // Corelang might be initialized by CSRF already...
                if (!is_array($_CORELANG) || !count($_CORELANG)) {
                    $_CORELANG = $objInit->loadLanguageData('core');
                }

                /**
                 * Module specific language data
                 * @ignore
                 */
                $_ARRAYLANG = $objInit->loadLanguageData($plainCmd);
                $_ARRAYLANG = array_merge($_ARRAYLANG, $_CORELANG);
                \Env::set('lang', $_ARRAYLANG);
                break;

            default:
                break;
        }
    }
}
