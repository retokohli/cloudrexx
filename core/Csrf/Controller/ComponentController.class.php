<?php
/**
 * Main controller for Csrf
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_csrf
 */

namespace Cx\Core\Csrf\Controller;

/**
 * Main controller for Csrf
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_csrf
 */


class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController 
{
    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }

    /**
     * Do something after resolving is done
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function postResolve(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $plainCmd, $cmd, $_CORELANG;
        
        
        // CSRF code needs to be even in the login form. otherwise, we
        // could not do a super-generic check later.. NOTE: do NOT move
        // this above the "new cmsSession" line!
        Csrf::add_code();

        // CSRF protection.
        // Note that we only do the check as long as there's no
        // cmd given; this is so we can reload the main screen if
        // the check has failed somehow.
        // fileBrowser is an exception, as it eats CSRF codes like
        // candy. We're doing \Cx\Core\Csrf\Controller\Csrf::check_code() in the relevant
        // parts in the module instead.
        // The CSRF code needn't to be checked in the login module
        // because the user isn't logged in at this point.
        // TODO: Why is upload excluded? The CSRF check doesn't take place in the upload module!
        if (!empty($plainCmd) && !empty($cmd) and !in_array($plainCmd, array('FileBrowser', 'Upload', 'Login', 'Home'))) {
            // Since language initialization in in the same hook as this
            // and we cannot define the order of module-processing,
            // we need to check if language is already initialized:
            if (!is_array($_CORELANG) || !count($_CORELANG)) {
                $objInit = \Env::get('init');
                $objInit->_initBackendLanguage();
                $_CORELANG = $objInit->loadLanguageData('core');
            }
            Csrf::check_code();
        }
                
    }
    /**
     * Do something after content is loaded from DB
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function postContentLoad(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $objTemplate;
        
        Csrf::add_placeholder($objTemplate);
               
    }
}

