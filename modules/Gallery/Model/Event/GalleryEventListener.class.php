<?php

/**
 * EventListener for Gallery
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_gallery
 */

namespace Cx\Modules\Gallery\Model\Event;
use Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration;
use Cx\Core\Model\Model\Entity\MediaType;
use Cx\Core\Core\Controller\Cx;

/**
 * EventListener for Gallery
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_gallery
 */
class GalleryEventListener implements \Cx\Core\Event\Model\Entity\EventListener {

    /**
     * @var Cx
     */
    protected $cx;

    function __construct(Cx $cx)
    {
        $this->cx = $cx;
    }

    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }
   
    public static function SearchFindContent($search) {
        $term_db = $search->getTerm();

        //For GalleryCategory
        $categoryQuery = "SELECT tblLang.gallery_id, tblLang.value AS title,
                           MATCH (tblLang.value) AGAINST ('%$term_db%') AS score
                      FROM " . DBPREFIX . "module_gallery_language AS tblLang,
                           " . DBPREFIX . "module_gallery_categories AS tblCat
                     WHERE tblLang.value LIKE ('%$term_db%')
                       AND tblLang.lang_id=" . FRONTEND_LANG_ID . "
                       AND tblLang.gallery_id=tblCat.id
                       AND tblCat.status=1";
        $categoryResult = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($search->getResultArray($categoryQuery, 'Gallery', 'showCat', 'cid=', $search->getTerm()));
        $search->appendResult($categoryResult);

        //For Gallerypicture
        $pictureQuery = "SELECT tblPic.catid AS id, tblLang.name AS title, tblLang.desc AS content,
                     MATCH (tblLang.name,tblLang.desc) AGAINST ('%$term_db%') AS score
                      FROM " . DBPREFIX . "module_gallery_pictures AS tblPic,
                           " . DBPREFIX . "module_gallery_language_pics AS tblLang,
                           " . DBPREFIX . "module_gallery_categories AS tblCat
                     WHERE (tblLang.name LIKE ('%$term_db%') OR tblLang.desc LIKE ('%$term_db%'))
                       AND tblLang.lang_id=" . FRONTEND_LANG_ID . "
                       AND tblLang.picture_id=tblPic.id
                       AND tblPic.status=1
                       AND tblCat.id=tblPic.catid
                       AND tblCat.status=1";
        $pictureResult = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($search->getResultArray($pictureQuery, 'Gallery', 'showCat', 'cid=', $search->getTerm()));
        $search->appendResult($pictureResult);
    }


    public function LoadMediaTypes(MediaBrowserConfiguration $mediaBrowserConfiguration)
    {
        global $_ARRAYLANG;
        \Env::get('init')->loadLanguageData('Gallery');
        $mediaType = new MediaType();
        $mediaType->setName('gallery');
        $mediaType->setHumanName($_ARRAYLANG['TXT_THUMBNAIL_GALLERY']);
        $mediaType->setDirectory(array(
            $this->cx->getWebsiteImagesGalleryPath(),
            $this->cx->getWebsiteImagesGalleryWebPath(),
        ));
        $mediaType->setAccessIds(array(12,67));
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }

}
