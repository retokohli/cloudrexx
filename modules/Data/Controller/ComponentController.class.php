<?php
/**
 * Main controller for Data
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package contrexx
 * @subpackage module_data
 */

namespace Cx\Modules\Data\Controller;

/**
 * Main controller for Data
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package contrexx
 * @subpackage module_data
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
     * Load the component Data.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $subMenuTitle, $objTemplate, $_CORELANG;
                
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:               
                $objData = new \Cx\Modules\Data\Controller\Data(\Env::get('cx')->getPage()->getContent());
                \Env::get('cx')->getPage()->setContent($objData->getPage());
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'content_master.html');
                $objTemplate = $this->cx->getTemplate();

                \Permission::checkAccess(146, 'static'); // ID !!
                $subMenuTitle = $_CORELANG['TXT_DATA_MODULE'];
                $objData = new \Cx\Modules\Data\Controller\DataAdmin();
                $objData->getPage();
                break;

            default:
                break;
        }
    }

    /**
    * Do something before content is loaded from DB
    * 
    * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
    */
    public function preContentLoad(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $_CONFIG, $cl, $lang, $objInit, $dataBlocks, $lang, $dataBlocks, $themesPages, $page_template;
        // Initialize counter and track search engine robot
        if ($_CONFIG['dataUseModule'] && $cl->loadFile(ASCMS_MODULE_PATH.'/Data/Controller/DataBlocks.class.php')) {
            $lang = $objInit->loadLanguageData('Data');
            $dataBlocks = new \Cx\Modules\Data\Controller\DataBlocks($lang);
            \Env::get('cx')->getPage()->setContent($dataBlocks->replace(\Env::get('cx')->getPage()->getContent()));
            $themesPages = $dataBlocks->replace($themesPages);
            $page_template = $dataBlocks->replace($page_template);
        }
  
    }

}
