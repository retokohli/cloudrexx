<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Main controller for MediaDir
 * 
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 */

namespace Cx\Modules\MediaDir\Controller;
use Cx\Modules\MediaDir\Model\Event\MediaDirEventListener;

/**
 * Main controller for MediaDir
 * 
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
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
        global $mediadirCheck, $objTemplate, $_CORELANG, $objInit;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                $mediadirCheck = array();
                for ($i = 1; $i <= 10; ++$i) {
                    if ($objTemplate->blockExists('mediadirLatest_row_'.$i)){
                        array_push($mediadirCheck, $i);
                    }
                }
                if ($mediadirCheck || $objTemplate->blockExists('mediadirLatest')) {
                    $objInit->loadLanguageData('MediaDir');
                    
                    $objMediadir = new MediaDirectory('', $this->getName());
                    $objTemplate->setVariable('TXT_MEDIADIR_LATEST', $_CORELANG['TXT_DIRECTORY_LATEST']);
                }
                if ($mediadirCheck) {
                    $objMediadir->getHeadlines($mediadirCheck);
                }
                if ($objTemplate->blockExists('mediadirLatest')){
                    $objMediadirForms = new \Cx\Modules\MediaDir\Controller\MediaDirectoryForm(null, 'MediaDir');
                    $foundOne = false;
                    foreach ($objMediadirForms->getForms() as $key => $arrForm) {
                        if ($objTemplate->blockExists('mediadirLatest_form_'.$arrForm['formCmd'])) {
                            $objMediadir->getLatestEntries($key, 'mediadirLatest_form_'.$arrForm['formCmd']);
                            $foundOne = true;
                        }
                    }
                    //for the backward compatibility
                    if(!$foundOne) {
                        $objMediadir->getLatestEntries();
                    }
                }
                break;
            default:
                break;
        }
    }

    /**
     * Register your event listeners here
     *
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * CALCULATE YOUR STUFF AS LATE AS POSSIBLE.
     * Keep in mind, that you can also register your events later.
     * Do not do anything else here than initializing your event listeners and
     * list statements like
     * $this->cx->getEvents()->addEventListener($eventName, $listener);
     */
    public function registerEventListeners() {
        $eventListener = new MediaDirEventListener($this->cx);
        $this->cx->getEvents()->addEventListener('SearchFindContent',$eventListener);
        $this->cx->getEvents()->addEventListener('mediasource.load', $eventListener);
    }

    /**
     * Called for additional, component specific resolving
     * 
     * If /en/Path/to/Page is the path to a page for this component
     * a request like /en/Path/to/Page/with/some/parameters will
     * give an array like array('with', 'some', 'parameters') for $parts
     * 
     * This may be used to redirect to another page
     * @param array $parts List of additional path parts
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page Resolved virtual page
     */
    public function resolve($parts, $page) {
        if (empty($parts)) {
            return;
        }

        $objMediaDirectoryEntry = new MediaDirectoryEntry($this->getName());
        if (!$objMediaDirectoryEntry->arrSettings['usePrettyUrls']) {
            return;
        }

        $detailPage = $page;
        $slugCount = count($parts);
        $cmd = $page->getCmd();
        $slug = array_pop($parts);

        $entryId = $objMediaDirectoryEntry->findOneBySlug($slug);
        if ($entryId) {
            if (substr($cmd,0,6) != 'detail') {
                $formId = null;
                $formData = $objMediaDirectoryEntry->getFormData();
                foreach ($formData as $arrForm) {
                    if ($arrForm['formCmd'] == $cmd) {
                        $formId= $arrForm['formId'];
                        break;
                    }
                }

                $detailPage = $objMediaDirectoryEntry->getApplicationPageByEntry($formId);
                if (!$detailPage) {
                    return;
                }
                // TODO: we need an other method that does also load the additional infos (template, css, etc.)
                //       this new method must also be used for symlink pages
                $page->setContentOf($detailPage, true);
                //$page->getFallbackContentFrom($detailPage);
                $_GET['cmd']     = $_POST['cmd']     = $_REQUEST['cmd']     = $detailPage->getCmd();
            }

            $this->cx->getRequest()->getUrl()->setParam('eid', $entryId);

            if (empty($parts)) {
                $this->setCanonicalPage($detailPage);
                return;
            }

            $slug = array_pop($parts);
        }


        // let's check if a category exists by the supplied slug
        $objMediaDirectoryCategory = new MediaDirectoryCategory(null, null, 0, $this->getName());
        $categoryId = $objMediaDirectoryCategory->findOneBySlug($slug);
        if ($categoryId) {
            $this->cx->getRequest()->getUrl()->setParam('cid', $categoryId);
            $this->setCanonicalPage($detailPage);
            return;
        }

        /*if (empty($parts)) {
            return;
        }

        $objMediaDirectoryEntry = new MediaDirectoryEntry($this->getName());
        if (!$objMediaDirectoryEntry->arrSettings['usePrettyUrls']) {
            return;
        }

        $cmd = $page->getCmd();
        if ($cmd == 'detail' || substr($cmd,0,6) == 'detail') {
            $entrySlug = array_pop($parts);
        }

        $entryId = $objMediaDirectoryEntry->findOneByName($entrySlug);
        if ($entryId) {
            $this->cx->getRequest()->getUrl()->setParam('eid', $entryId);
        }*/
    }

    protected function setCanonicalPage($canonicalPage) {
        $canonicalUrl = \Cx\Core\Routing\Url::fromPage($canonicalPage, $this->cx->getRequest()->getUrl()->getParamArray());
        header('Link: <' . $canonicalUrl->toString() . '>; rel="canonical"');
    }
}
