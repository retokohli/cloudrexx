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
 * Main controller for Gallery
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_gallery
 */

namespace Cx\Modules\Gallery\Controller;

/**
 * Main controller for Gallery
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_gallery
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }

    /**
     * Returns a list of JsonAdapter class names
     *
     * @return array List of ComponentController classes
     */
    public function getControllersAccessableByJson() {
        return array('JsonGallery');
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
        global $page_template, $themesPages, $_LANGID;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                $cache = $this->cx->getComponent('Cache');
                //Parse gallery random image
                $objGalleryHome  = new GalleryHomeContent();
                $imageIds        = $objGalleryHome->getImageIds();
                $esiContentInfos = array();
                if ($imageIds) {
                    foreach ($imageIds as $imgId) {
                        $esiContentInfos[] = array(
                            'Gallery',
                            'getImageById',
                            array('imgId' => $imgId, 'langId' => $_LANGID)
                        );
                    }
                }

                $galleryRandomImg = $cache->getRandomizedEsiContent(
                    $esiContentInfos
                );
                $content = $this->cx->getPage()->getContent();
                if (preg_match('/{GALLERY_RANDOM}/', $content)) {
                    $this->parseContentIntoTpl(
                        $content,
                        $galleryRandomImg,
                        '{GALLERY_RANDOM}'
                    );
                    $this->cx->getPage()->setContent($content);
                }
                $this->parseContentIntoTpl(
                    $page_template,
                    $galleryRandomImg,
                    '{GALLERY_RANDOM}'
                );
                $this->parseContentIntoTpl(
                    $themesPages['index'],
                    $galleryRandomImg,
                    '{GALLERY_RANDOM}'
                );
                $this->parseContentIntoTpl(
                    $themesPages['sidebar'],
                    $galleryRandomImg,
                    '{GALLERY_RANDOM}'
                );

                //Parse gallery latest image
                $galleryLatestImg = $cache->getEsiContent(
                    'Gallery',
                    'getLastImage'
                );
                $pageContent = $this->cx->getPage()->getContent();
                if (preg_match('/{GALLERY_LATEST}/', $pageContent)) {
                    $this->parseContentIntoTpl(
                        $pageContent,
                        $galleryLatestImg,
                        '{GALLERY_LATEST}'
                    );
                    $this->cx->getPage()->setContent($pageContent);
                }
                $this->parseContentIntoTpl(
                    $page_template,
                    $galleryLatestImg,
                    '{GALLERY_LATEST}'
                );
                $this->parseContentIntoTpl(
                    $themesPages['index'],
                    $galleryLatestImg,
                    '{GALLERY_LATEST}'
                );
                $this->parseContentIntoTpl(
                    $themesPages['sidebar'],
                    $galleryLatestImg,
                    '{GALLERY_LATEST}'
                );
                break;
            default:
                break;
        }
    }

    /**
     * Parse the gallery content into template content
     *
     * @param string $template template content
     * @param string $content  parsing content
     * @param string $pattern  pattern
     *
     * @return null
     */
    public function parseContentIntoTpl(&$template, $content, $pattern)
    {
        if (empty($template) || empty($pattern)) {
            return;
        }

        if (preg_match('/' . $pattern . '/', $template)) {
            $template = str_replace($pattern, $content, $template);
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
        $eventListener = new \Cx\Modules\Gallery\Model\Event\GalleryEventListener($this->cx);
        $this->cx->getEvents()->addEventListener('SearchFindContent', $eventListener);
        $this->cx->getEvents()->addEventListener('mediasource.load', $eventListener);
        $this->cx->getEvents()->addEventListener('clearEsiCache', $eventListener);
    }

}
