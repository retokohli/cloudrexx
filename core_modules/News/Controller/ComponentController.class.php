<?php
/**
 * Main controller for News
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_news
 */

namespace Cx\Core_Modules\News\Controller;

/**
 * Main controller for News
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_news
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
        global $_CORELANG, $page, $objTemplate, $subMenuTitle;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                $newsObj = new News(\Env::get('cx')->getPage()->getContent());
                \Env::get('cx')->getPage()->setContent($newsObj->getNewsPage());
                $newsObj->getPageTitle(\Env::get('cx')->getPage()->getTitle());
                
                // Set the meta page description to the teaser text if displaying news details
                $teaser = $newsObj->getTeaser();
                if ($teaser !== null) //news details, else getTeaser would return null
                    $page->setMetadesc(contrexx_raw2xhtml(contrexx_strip_tags(html_entity_decode($teaser, ENT_QUOTES, CONTREXX_CHARSET))));
                \Env::get('cx')->getPage()->setTitle($newsObj->newsTitle);
                \Env::get('cx')->getPage()->setContentTitle($newsObj->newsTitle);
                \Env::get('cx')->getPage()->setMetaTitle($newsObj->newsTitle);
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
                $objTemplate = $this->cx->getTemplate();

                \Permission::checkAccess(10, 'static');
                $subMenuTitle = $_CORELANG['TXT_NEWS_MANAGER'];
                $objNews      = new NewsManager();
                $objNews->getPage();
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
        global $themesPages, $page_template;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                // Get Headlines
                $modulespath = ASCMS_CORE_MODULE_PATH.'/News/Controller/NewsHeadlines.class.php';
                if (file_exists($modulespath)) {
                    for ($i = 0; $i < 5; $i++) {
                        $visibleI = '';
                        if ($i > 0) {
                            $visibleI = (string) $i;
                        }
                        $headlinesNewsPlaceholder = '{HEADLINES' . $visibleI . '_FILE}';
                        if (
                            strpos(\Env::get('cx')->getPage()->getContent(), $headlinesNewsPlaceholder) !== false
                            || strpos($themesPages['index'], $headlinesNewsPlaceholder) !== false
                            || strpos($themesPages['sidebar'], $headlinesNewsPlaceholder) !== false
                            || strpos($page_template, $headlinesNewsPlaceholder) !== false
                           ) {
                                $category = 0;
                                $matches = array();
                                if (preg_match('/\{CATEGORY_([0-9]+)\}/', trim($themesPages['headlines' . $visibleI]), $matches)) {
                                    $category = $matches[1];
                                }
                                $newsHeadlinesObj = new NewsHeadlines($themesPages['headlines' . $visibleI]);
                                $homeHeadlines = $newsHeadlinesObj->getHomeHeadlines($category);
                                \Env::get('cx')->getPage()->setContent(str_replace($headlinesNewsPlaceholder, $homeHeadlines, \Env::get('cx')->getPage()->getContent()));
                                $themesPages['index']   = str_replace($headlinesNewsPlaceholder, $homeHeadlines, $themesPages['index']);
                                $themesPages['sidebar'] = str_replace($headlinesNewsPlaceholder, $homeHeadlines, $themesPages['sidebar']);
                                $page_template          = str_replace($headlinesNewsPlaceholder, $homeHeadlines, $page_template);
                        }
                    }
                }

                // Get Top news
                $modulespath = ASCMS_CORE_MODULE_PATH.'/News/Controller/NewsTop.class.php';
                $topNewsPlaceholder = '{TOP_NEWS_FILE}';
                if ( file_exists($modulespath)
                     && (   strpos(\Env::get('cx')->getPage()->getContent(), $topNewsPlaceholder) !== false
                            || strpos($themesPages['index'], $topNewsPlaceholder) !== false
                            || strpos($themesPages['sidebar'], $topNewsPlaceholder) !== false
                            || strpos($page_template, $topNewsPlaceholder) !== false)
                   ) {
                        $newsTopObj = new NewsTop($themesPages['top_news']);
                        $homeTopNews = $newsTopObj->getHomeTopNews();
                        \Env::get('cx')->getPage()->setContent(str_replace($topNewsPlaceholder, $homeTopNews, \Env::get('cx')->getPage()->getContent()));
                        $themesPages['index']   = str_replace($topNewsPlaceholder, $homeTopNews, $themesPages['index']);
                        $themesPages['sidebar'] = str_replace($topNewsPlaceholder, $homeTopNews, $themesPages['sidebar']);
                        $page_template          = str_replace($topNewsPlaceholder, $homeTopNews, $page_template);
                }
                        
                // Get News categories
                $modulespath = ASCMS_CORE_MODULE_PATH.'/News/Controller/NewsLibrary.class.php';
                $newsCategoriesPlaceholder = '{NEWS_CATEGORIES}';
                if ( file_exists($modulespath)
                     && (   strpos(\Env::get('cx')->getPage()->getContent(), $newsCategoriesPlaceholder) !== false
                            || strpos($themesPages['index'], $newsCategoriesPlaceholder) !== false
                            || strpos($themesPages['sidebar'], $newsCategoriesPlaceholder) !== false
                            || strpos($page_template, $newsCategoriesPlaceholder) !== false)
                   ) {
                        $newsLib = new NewsLibrary();
                        $newsCategories = $newsLib->getNewsCategories();
                            
                        \Env::get('cx')->getPage()->setContent(str_replace($newsCategoriesPlaceholder, $newsCategories, \Env::get('cx')->getPage()->getContent()));
                        $themesPages['index']   = str_replace($newsCategoriesPlaceholder, $newsCategories, $themesPages['index']);
                        $themesPages['sidebar'] = str_replace($newsCategoriesPlaceholder, $newsCategories, $themesPages['sidebar']);
                        $page_template          = str_replace($newsCategoriesPlaceholder, $newsCategories, $page_template);
                }
                        
                // Get News Archives
                $modulespath = ASCMS_CORE_MODULE_PATH.'/News/Controller/NewsLibrary.class.php';
                $newsArchivePlaceholder = '{NEWS_ARCHIVES}';
                if ( file_exists($modulespath)
                     && (  strpos(\Env::get('cx')->getPage()->getContent(), $newsArchivePlaceholder) !== false
                           || strpos($themesPages['index'], $newsArchivePlaceholder) !== false
                           || strpos($themesPages['sidebar'], $newsArchivePlaceholder) !== false
                           || strpos($page_template, $newsArchivePlaceholder) !== false)
                   ) {
                        $newsLib = new NewsLibrary();
                        $newsArchive = $newsLib->getNewsArchiveList();
                            
                        \Env::get('cx')->getPage()->setContent(str_replace($newsArchivePlaceholder, $newsArchive, \Env::get('cx')->getPage()->getContent()));
                        $themesPages['index']   = str_replace($newsArchivePlaceholder, $newsArchive, $themesPages['index']);
                        $themesPages['sidebar'] = str_replace($newsArchivePlaceholder, $newsArchive, $themesPages['sidebar']);
                        $page_template          = str_replace($newsArchivePlaceholder, $newsArchive, $page_template);
                }
                    
                // Get recent News Comments
                $modulespath = ASCMS_CORE_MODULE_PATH.'/News/Controller/NewsRecentComments.class.php';
                $newsCommentsPlaceholder = '{NEWS_RECENT_COMMENTS_FILE}';
                        
                if ( file_exists($modulespath)
                     && (  strpos(\Env::get('cx')->getPage()->getContent(), $newsCommentsPlaceholder) !== false
                           || strpos($themesPages['index'], $newsCommentsPlaceholder) !== false
                           || strpos($themesPages['sidebar'], $newsCommentsPlaceholder) !== false
                           || strpos($page_template, $newsCommentsPlaceholder) !== false)
                   ) {
                        $newsLib = new NewsRecentComments($themesPages['news_recent_comments']);
                        $newsComments = $newsLib->getRecentNewsComments();
                            
                        \Env::get('cx')->getPage()->setContent(str_replace($newsCommentsPlaceholder, $newsComments, \Env::get('cx')->getPage()->getContent()));
                        $themesPages['index']   = str_replace($newsCommentsPlaceholder, $newsComments, $themesPages['index']);
                        $themesPages['sidebar'] = str_replace($newsCommentsPlaceholder, $newsComments, $themesPages['sidebar']);
                        $page_template          = str_replace($newsCommentsPlaceholder, $newsComments, $page_template);
                }
                
                //Teasers
                 $arrMatches = array();
                // Set news teasers
                 $config = \Env::get('config');
                if ($config['newsTeasersStatus'] == '1') {
                    // set news teasers in the content
                    if (preg_match_all('/{TEASERS_([0-9A-Z_-]+)}/', \Env::get('cx')->getPage()->getContent(), $arrMatches)) {
                        /** @ignore */
                            $objTeasers = new Teasers();
                            $content = \Env::get('cx')->getPage()->getContent();
                            $objTeasers->setTeaserFrames($arrMatches[1], $content);
                            \Env::get('cx')->getPage()->setContent($content);
                    }
                    // set news teasers in the page design
                    if (preg_match_all('/{TEASERS_([0-9A-Z_-]+)}/', $page_template, $arrMatches)) {
                        /** @ignore */
                        $objTeasers = new Teasers();
                        $objTeasers->setTeaserFrames($arrMatches[1], $page_template);
                    }
                    // set news teasers in the website design
                    if (preg_match_all('/{TEASERS_([0-9A-Z_-]+)}/', $themesPages['index'], $arrMatches)) {
                        /** @ignore */
                            $objTeasers = new Teasers();
                            $objTeasers->setTeaserFrames($arrMatches[1], $themesPages['index']);
                    }
                }
                break;

            default:
                break;
        }
    }
    
    
    /**
     * Do something for search the content
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function preContentParse(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        $this->cx->getEvents()->addEventListener('SearchFindContent', new \Cx\Core_Modules\News\Model\Event\NewsEventListener());
    }
}
