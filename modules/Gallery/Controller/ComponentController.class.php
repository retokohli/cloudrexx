<?php
/**
 * Main controller for Gallery
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_gallery
 */

namespace Cx\Modules\Gallery\Controller;

/**
 * Main controller for Gallery
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_gallery
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
                $objGallery = new Gallery(\Env::get('cx')->getPage()->getContent());
                \Env::get('cx')->getPage()->setContent($objGallery->getPage());

                $topGalleryName = $objGallery->getTopGalleryName();
                if ($topGalleryName) {
                    \Env::get('cx')->getPage()->setTitle($topGalleryName);
                    \Env::get('cx')->getPage()->setContentTitle($topGalleryName);
                    \Env::get('cx')->getPage()->setMetaTitle($topGalleryName);
                }
               
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
                $objTemplate = $this->cx->getTemplate();
                \Permission::checkAccess(12, 'static');
                $subMenuTitle = $_CORELANG['TXT_GALLERY_TITLE'];
                $objGalleryManager = new GalleryManager();
                $objGalleryManager->getPage();
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
        global $page_template, $themesPages, $latestImage;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                $objGalleryHome = new GalleryHomeContent();
                if ($objGalleryHome->checkRandom()) {
                    if (preg_match('/{GALLERY_RANDOM}/', \Env::get('cx')->getPage()->getContent())) {
                        \Env::get('cx')->getPage()->setContent(str_replace('{GALLERY_RANDOM}', $objGalleryHome->getRandomImage(), \Env::get('cx')->getPage()->getContent()));
                    }
                    if (preg_match('/{GALLERY_RANDOM}/', $page_template))  {
                        $page_template = str_replace('{GALLERY_RANDOM}', $objGalleryHome->getRandomImage(), $page_template);
                    }
                    if (preg_match('/{GALLERY_RANDOM}/', $themesPages['index'])) {
                        $themesPages['index'] = str_replace('{GALLERY_RANDOM}', $objGalleryHome->getRandomImage(), $themesPages['index']);
                    }
                    if (preg_match('/{GALLERY_RANDOM}/', $themesPages['sidebar'])) {
                        $themesPages['sidebar'] = str_replace('{GALLERY_RANDOM}', $objGalleryHome->getRandomImage(), $themesPages['sidebar']);
                    }
                }
                if ($objGalleryHome->checkLatest()) {
                    $latestImage = $objGalleryHome->getLastImage();
                    if (preg_match('/{GALLERY_LATEST}/', \Env::get('cx')->getPage()->getContent())) {
                        \Env::get('cx')->getPage()->setContent(str_replace('{GALLERY_LATEST}', $latestImage, \Env::get('cx')->getPage()->getContent()));
                    }
                    if (preg_match('/{GALLERY_LATEST}/', $page_template)) {
                        $page_template = str_replace('{GALLERY_LATEST}', $latestImage, $page_template);
                    }
                    if (preg_match('/{GALLERY_LATEST}/', $themesPages['index'])) {
                        $themesPages['index'] = str_replace('{GALLERY_LATEST}', $latestImage, $themesPages['index']);
                    }
                    if (preg_match('/{GALLERY_LATEST}/', $themesPages['sidebar'])) {
                        $themesPages['sidebar'] = str_replace('{GALLERY_LATEST}', $latestImage, $themesPages['sidebar']);
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
        $this->cx->getEvents()->addEventListener('SearchFindContent', new \Cx\Modules\Gallery\Model\Event\GalleryEventListener());
    }

}
