<?php
/**
 * Main controller for Podcast
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_podcast
 */

namespace Cx\Modules\Podcast\Controller;

/**
 * Main controller for Podcast
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_podcast
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
     * Load the component Podcast.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $subMenuTitle, $objTemplate, $_CORELANG;
                
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:               
                $objPodcast = new Podcast(\Env::get('cx')->getPage()->getContent());
                \Env::get('cx')->getPage()->setContent($objPodcast->getPage($podcastFirstBlock));
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
                $objTemplate = $this->cx->getTemplate();

                \Permission::checkAccess(87, 'static');
                $subMenuTitle = $_CORELANG['TXT_PODCAST'];
                $objPodcast = new PodcastManager();
                $objPodcast->getPage();
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
        global $podcastFirstBlock, $podcastContent, $_CONFIG, $cl, $podcastHomeContentInPageContent, $podcastHomeContentInPageTemplate, $podcastHomeContentInThemesPage, $page_template, $themesPages, $_ARRAYLANG, $objInit, $objPodcast, $podcastBlockPos, $contentPos;
        // get latest podcast entries
        $podcastFirstBlock = false;
        $podcastContent = null;
        if (!empty($_CONFIG['podcastHomeContent'])) {
            /** @ignore */
            if ($cl->loadFile(ASCMS_MODULE_PATH.'/Podcast/Controller/PodcastHomeContent.class.php')) {
                $podcastHomeContentInPageContent = false;
                $podcastHomeContentInPageTemplate = false;
                $podcastHomeContentInThemesPage = false;
                if (strpos(\Env::get('cx')->getPage()->getContent(), '{PODCAST_FILE}') !== false) {
                    $podcastHomeContentInPageContent = true;
                }
                if (strpos($page_template, '{PODCAST_FILE}') !== false) {
                    $podcastHomeContentInPageTemplate = true;
                }
                if (strpos($themesPages['index'], '{PODCAST_FILE}') !== false) {
                    $podcastHomeContentInThemesPage = true;
                }
                if ($podcastHomeContentInPageContent || $podcastHomeContentInPageTemplate || $podcastHomeContentInThemesPage) {
                    $_ARRAYLANG = array_merge($_ARRAYLANG, $objInit->loadLanguageData('Podcast'));
                    $objPodcast = new PodcastHomeContent($themesPages['podcast_content']);
                    $podcastContent = $objPodcast->getContent();
                    if ($podcastHomeContentInPageContent) {
                        \Env::get('cx')->getPage()->setContent(str_replace('{PODCAST_FILE}', $podcastContent, \Env::get('cx')->getPage()->getContent()));
                    }
                    if ($podcastHomeContentInPageTemplate) {
                        $page_template = str_replace('{PODCAST_FILE}', $podcastContent, $page_template);
                    }
                    if ($podcastHomeContentInThemesPage) {
                        $podcastFirstBlock = false;
                        if (strpos($_SERVER['REQUEST_URI'], 'section=Podcast')) {
                            $podcastBlockPos = strpos($themesPages['index'], '{PODCAST_FILE}');
                            $contentPos = strpos($themesPages['index'], '{CONTENT_FILE}');
                            $podcastFirstBlock = $podcastBlockPos < $contentPos ? true : false;
                        }
                        $themesPages['index'] = str_replace('{PODCAST_FILE}',
                        $objPodcast->getContent($podcastFirstBlock), $themesPages['index']);
                    }
                }
            }
        }
    }
    
    /**
     * Do something for search the content
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function preContentParse(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        $eventListener = new \Cx\Modules\Podcast\Model\Event\PodcastEventListener($this->cx);
        $this->cx->getEvents()->addEventListener('SearchFindContent', $eventListener);
        $this->cx->getEvents()->addEventListener('LoadMediaTypes', $eventListener);
    }
}
