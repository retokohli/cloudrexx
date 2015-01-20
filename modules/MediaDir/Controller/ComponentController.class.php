<?php
/**
 * Main controller for MediaDir
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_mediadir
 */

namespace Cx\Modules\MediaDir\Controller;

/**
 * Main controller for MediaDir
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_mediadir
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
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                    $objMediaDirectory = new MediaDirectory(\Env::get('cx')->getPage()->getContent(), $this->getName());
                    $objMediaDirectory->pageTitle = \Env::get('cx')->getPage()->getTitle();
                    $pageMetaTitle = \Env::get('cx')->getPage()->getMetatitle();
                    $objMediaDirectory->metaTitle = $pageMetaTitle;
                    \Env::get('cx')->getPage()->setContent($objMediaDirectory->getPage());
                    if ($objMediaDirectory->getPageTitle() != '' && $objMediaDirectory->getPageTitle() != \Env::get('cx')->getPage()->getTitle()) {
                        \Env::get('cx')->getPage()->setTitle($objMediaDirectory->getPageTitle());
                        \Env::get('cx')->getPage()->setContentTitle($objMediaDirectory->getPageTitle());
                        \Env::get('cx')->getPage()->setMetaTitle($objMediaDirectory->getPageTitle());
                    }
                    if ($objMediaDirectory->getMetaTitle() != '') {
                        \Env::get('cx')->getPage()->setMetatitle($objMediaDirectory->getMetaTitle());
                    }
                    
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
                $objTemplate = $this->cx->getTemplate();
                \Permission::checkAccess(153, 'static');
                $subMenuTitle = $_CORELANG['TXT_MEDIADIR_MODULE'];
                $objMediaDirectory = new MediaDirectoryManager($this->getName());
                $objMediaDirectory->getPage();
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
        global $objMadiadirPlaceholders, $page_template, $themesPages;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                $objMadiadirPlaceholders = new MediaDirectoryPlaceholders($this->getName());
                // Level/Category Navbar
                if (preg_match('/{MEDIADIR_NAVBAR}/', \Env::get('cx')->getPage()->getContent())) {
                    \Env::get('cx')->getPage()->setContent(str_replace('{MEDIADIR_NAVBAR}', $objMadiadirPlaceholders->getNavigationPlacholder(), \Env::get('cx')->getPage()->getContent()));
                }
                if (preg_match('/{MEDIADIR_NAVBAR}/', $page_template)) {
                    $page_template = str_replace('{MEDIADIR_NAVBAR}', $objMadiadirPlaceholders->getNavigationPlacholder(), $page_template);
                }
                if (preg_match('/{MEDIADIR_NAVBAR}/', $themesPages['index'])) {
                    $themesPages['index'] = str_replace('{MEDIADIR_NAVBAR}', $objMadiadirPlaceholders->getNavigationPlacholder(), $themesPages['index']);
                }
                if (preg_match('/{MEDIADIR_NAVBAR}/', $themesPages['sidebar'])) {
                    $themesPages['sidebar'] = str_replace('{MEDIADIR_NAVBAR}', $objMadiadirPlaceholders->getNavigationPlacholder(), $themesPages['sidebar']);
                }
                // Latest Entries
                if (preg_match('/{MEDIADIR_LATEST}/', \Env::get('cx')->getPage()->getContent())) {
                    \Env::get('cx')->getPage()->setContent(str_replace('{MEDIADIR_LATEST}', $objMadiadirPlaceholders->getLatestPlacholder(), \Env::get('cx')->getPage()->getContent()));
                }
                if (preg_match('/{MEDIADIR_LATEST}/', $page_template)) {
                    $page_template = str_replace('{MEDIADIR_LATEST}', $objMadiadirPlaceholders->getLatestPlacholder(), $page_template);
                }
                if (preg_match('/{MEDIADIR_LATEST}/', $themesPages['index'])) {
                    $themesPages['index'] = str_replace('{MEDIADIR_LATEST}', $objMadiadirPlaceholders->getLatestPlacholder(), $themesPages['index']);
                }
                if (preg_match('/{MEDIADIR_LATEST}/', $themesPages['sidebar'])) {
                    $themesPages['sidebar'] = str_replace('{MEDIADIR_LATEST}', $objMadiadirPlaceholders->getLatestPlacholder(), $themesPages['sidebar']);
                }
                        
                break;

            default:
                break;
        }
    }
    
    /**
     * Do something after content is loaded from DB
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function postContentLoad(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $mediadirCheck, $objTemplate, $_CORELANG;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                $mediadirCheck = array();
                for ($i = 1; $i <= 10; ++$i) {
                    if ($objTemplate->blockExists('mediadirLatest_row_'.$i)){
                        array_push($mediadirCheck, $i);
                    }
                }
                if ($mediadirCheck) {
                    $objMediadir = new MediaDirectory('', $this->getName());
                    $objTemplate->setVariable('TXT_MEDIADIR_LATEST', $_CORELANG['TXT_DIRECTORY_LATEST']);
                    $objMediadir->getHeadlines($mediadirCheck);
                }
                break;
            default:
                break;
        }
    }
}
