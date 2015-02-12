<?php
/**
 * Main controller for Downloads
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_downloads
 */

namespace Cx\Modules\Downloads\Controller;
use Cx\Modules\Downloads\Model\Event\DownloadsEventListener;

/**
 * Main controller for Downloads
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_downloads
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
                $objDownloadsModule = new Downloads(\Env::get('cx')->getPage()->getContent());
                \Env::get('cx')->getPage()->setContent($objDownloadsModule->getPage());
                $downloads_pagetitle = $objDownloadsModule->getPageTitle();
                if ($downloads_pagetitle) {
                    \Env::get('cx')->getPage()->setTitle($downloads_pagetitle);
                    \Env::get('cx')->getPage()->setContentTitle($downloads_pagetitle);
                    \Env::get('cx')->getPage()->setMetaTitle($downloads_pagetitle);
                }
               
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
                $objTemplate = $this->cx->getTemplate();
                $subMenuTitle = $_CORELANG['TXT_DOWNLOADS'];
                $objDownloadsModule = new DownloadsManager();
                $objDownloadsModule->getPage();
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
        global $arrMatches, $cl, $objDownloadLib, $downloadBlock, $matches, $objDownloadsModule;;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                // Set download groups
                if (preg_match_all('/{DOWNLOADS_GROUP_([0-9]+)}/', \Env::get('cx')->getPage()->getContent(), $arrMatches)) {
                    /** @ignore */
                    if ($cl->loadFile(ASCMS_MODULE_PATH.'/Downloads/Controller/DownloadsLibrary.class.php')) {
                        $objDownloadLib = new DownloadsLibrary();
                        $objDownloadLib->setGroups($arrMatches[1], \Env::get('cx')->getPage()->getContent());
                    }
                }

                //--------------------------------------------------------
                // Parse the download block 'downloads_category_#ID_list'
                //--------------------------------------------------------
                $content = \Env::get('cx')->getPage()->getContent();
                $downloadBlock = preg_replace_callback(
                    "/<!--\s+BEGIN\s+downloads_category_(\d+)_list\s+-->(.*)<!--\s+END\s+downloads_category_\g1_list\s+-->/s",
                    function($matches) {
                        \Env::get('init')->loadLanguageData('Downloads');
                        if (isset($matches[2])) {
                            $objDownloadsModule = new Downloads($matches[2], array('category' => $matches[1]));
                            return $objDownloadsModule->getPage();
                        }
                    },
                    $content);
                \Env::get('cx')->getPage()->setContent($downloadBlock);
                break;

            default:
                break;
        }

        
    }

    public function preContentParse(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        $eventListener = new DownloadsEventListener($this->cx);
        $this->cx->getEvents()->addEventListener('LoadMediaTypes', $eventListener);
    }

}
