<?php
/**
 * Main controller for Upload
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_upload
 */

namespace Cx\Core_Modules\Upload\Controller;

/**
 * Main controller for Upload
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_upload
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
     * Load the component Upload.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $objTemplate;
        $objUploadModule = new UploadManager();
        $objUploadModule->getPage();
    }
    
    /**
     * Do something before resolving is done
     * 
     * @param \Cx\Core\Routing\Url $request The URL object for this request
     */
    public function preResolve(\Cx\Core\Routing\Url $request) {
        switch ($this->cx->getMode()) {
            
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                if (isset($_REQUEST['section']) && $_REQUEST['section'] == 'Upload') {
                    $_REQUEST['standalone'] = 'true';
                }
            
            break;
        }
    }
    
    /**
     * Do something after resolving is done
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page The resolved page
     */
    public function postResolve(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $sessionObj;
        switch ($this->cx->getMode()) {
            
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                if (isset($_REQUEST['section']) && $_REQUEST['section'] == 'Upload') {
                    if (!isset($sessionObj) || !is_object($sessionObj)) $sessionObj = \cmsSession::getInstance(); // initialize session object                            
                    $objUploadModule = new Upload();
                    $objUploadModule->getPage();
                    //execution never reaches this point
                }

            break;
        default :
            break;
        }
    }
}   