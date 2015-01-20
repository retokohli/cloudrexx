<?php

/**
 * Main controller for Directory
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_directory
 */

namespace Cx\Modules\Directory\Controller;

/**
 * Main controller for Directory
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_directory
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
                $objDirectory = new Directory(\Env::get('cx')->getPage()->getContent());
                \Env::get('cx')->getPage()->setContent($objDirectory->getPage());
                $directory_pagetitle = $objDirectory->getPageTitle();
                if (!empty($directory_pagetitle)) {
                    \Env::get('cx')->getPage()->setTitle($directory_pagetitle);
                    \Env::get('cx')->getPage()->setContentTitle($directory_pagetitle);
                    \Env::get('cx')->getPage()->setMetaTitle($directory_pagetitle);
                }
                if ($_GET['cmd'] == 'detail' && isset($_GET['id'])) {
                    $objTemplate->setVariable(array(
                        'DIRECTORY_ENTRY_ID' => intval($_GET['id']),
                    ));
                }
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:

                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
                $objTemplate = $this->cx->getTemplate();

                $subMenuTitle = $_CORELANG['TXT_LINKS_MODULE_DESCRIPTION'];
                $objDirectoryManager = new DirectoryManager();
                $objDirectoryManager->getPage();
                break;

            default:
                break;
        }
    }

    /*
     * Do something before content is loaded from DB
     *
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page The resolved page
     */

    public function preContentLoad(\Cx\Core\ContentManager\Model\Entity\Page $page) {

        global $_CONFIG, $cl, $dirc, $themesPages, $page_template, $themesPages;

        // get Directory Homecontent
        if ($_CONFIG['directoryHomeContent'] == '1') {
            if ($cl->loadFile(ASCMS_MODULE_PATH . '/Directory/Controller/DirHomeContent.class.php')) {

                $dirc = $themesPages['directory_content'];
                if (preg_match('/{DIRECTORY_FILE}/', \Env::get('cx')->getPage()->getContent())) {
                    \Env::get('cx')->getPage()->setContent(str_replace('{DIRECTORY_FILE}', DirHomeContent::getObj($dirc)->getContent(), \Env::get('cx')->getPage()->getContent()));
                }
                if (preg_match('/{DIRECTORY_FILE}/', $page_template)) {
                    $page_template = str_replace('{DIRECTORY_FILE}', DirHomeContent::getObj($dirc)->getContent(), $page_template);
                }
                if (preg_match('/{DIRECTORY_FILE}/', $themesPages['index'])) {
                    $themesPages['index'] = str_replace('{DIRECTORY_FILE}', DirHomeContent::getObj($dirc)->getContent(), $themesPages['index']);
                }
            }
        }
    }

    /**
     * Do something after content is loaded from DB
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function postContentLoad(\Cx\Core\ContentManager\Model\Entity\Page $page) {

        global $directoryCheck, $objTemplate, $cl, $objDirectory, $_CORELANG;

        // Directory Show Latest
        $directoryCheck = array();
        for ($i = 1; $i <= 10; $i++) {
            if ($objTemplate->blockExists('directoryLatest_row_' . $i)) {
                array_push($directoryCheck, $i);
            }
        }
        if (!empty($directoryCheck)
                /** @ignore */ && $cl->loadFile(ASCMS_MODULE_PATH . '/Directory/Controller/Directory.class.php')) {
            $objDirectory = new Directory('');
            if (!empty($directoryCheck)) {
                $objTemplate->setVariable('TXT_DIRECTORY_LATEST', $_CORELANG['TXT_DIRECTORY_LATEST']);
                $objDirectory->getBlockLatest($directoryCheck);
            }
        }
    }

    /**
     * Do something for search the content
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function preContentParse(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        $this->cx->getEvents()->addEventListener('SearchFindContent', new \Cx\Modules\Directory\Model\Event\DirectoryEventListener());
   }      
}