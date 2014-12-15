<?php

/**
 * Specific FrontendController for this Component. Use this to easily create a frontent view
 *
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */

namespace Cx\Core_Modules\MediaBrowser\Controller;

use Cx\Core_Modules\MediaBrowser\Model\FileSystem;
use Cx\Lib\FileSystem\File;

class MediaBrowserConfiguration
{

    protected static $thumbnails;

    /**
     * @var self reference to singleton instance
     */
    protected static $instance;

    /**
     * @var \Cx\Core\Core\Controller\Cx
     */
    protected $cx;

    protected $mediaTypes
        = array(
            'files' => 'TXT_FILEBROWSER_FILES',
            'webpages' => 'TXT_FILEBROWSER_WEBPAGES',
            'media1' => 'TXT_FILEBROWSER_MEDIA_1',
            'media2' => 'TXT_FILEBROWSER_MEDIA_2',
            'media3' => 'TXT_FILEBROWSER_MEDIA_3',
            'media4' => 'TXT_FILEBROWSER_MEDIA_4',
            'attach' => 'TXT_FILE_UPLOADS',
            'shop' => 'TXT_FILEBROWSER_SHOP',
            'gallery' => 'TXT_THUMBNAIL_GALLERY',
            'access' => 'TXT_USER_ADMINISTRATION',
            'mediadir' => 'TXT_MEDIADIR_MODULE',
            'downloads' => 'TXT_DOWNLOADS',
            'calendar' => 'TXT_CALENDAR',
            'podcast' => 'TXT_FILEBROWSER_PODCAST',
            'blog' => 'TXT_FILEBROWSER_BLOG',
            'Wysiwyg' => 'TXT_FILEBROWSER_WYSIWYG',
        );

    protected $mediaTypePaths;
    protected $allMediaTypePaths;

    /**
     * gets the instance via lazy initialization (created on first usage)
     *
     * @return self
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * is not allowed to call from outside: private!
     *
     */
    protected function __construct()
    {
        $this->cx = \Env::get('cx');

        $this->allMediaTypePaths
            = array(
            'files' => array(
                $this->cx->getWebsiteImagesContentPath(),
                $this->cx->getWebsiteImagesContentWebPath(),
            ),
            'media1' => array(
                $this->cx->getWebsiteMediaarchive1Path(),
                $this->cx->getWebsiteMediaarchive1WebPath(),
            ),
            'media2' => array(
                $this->cx->getWebsiteMediaarchive2Path(),
                $this->cx->getWebsiteMediaarchive2WebPath(),
            ),
            'media3' => array(
                $this->cx->getWebsiteMediaarchive3Path(),
                $this->cx->getWebsiteMediaarchive3WebPath(),
            ),
            'media4' => array(
                $this->cx->getWebsiteMediaarchive4Path(),
                $this->cx->getWebsiteMediaarchive4WebPath(),
            ),
            'media5' => array(
                $this->cx->getWebsiteMediaarchive5Path(),
                $this->cx->getWebsiteMediaarchive5WebPath(),
            ),
            'shop' => array(
                $this->cx->getWebsiteImagesShopPath(),
                $this->cx->getWebsiteImagesShopWebPath(),
            ),
            'gallery' => array(
                $this->cx->getWebsiteImagesGalleryPath(),
                $this->cx->getWebsiteImagesGalleryWebPath(),
            ),
            'access' => array(
                $this->cx->getWebsiteImagesAccessPath(),
                $this->cx->getWebsiteImagesAccessWebPath(),
            ),
            'mediadir' => array(
                $this->cx->getWebsiteImagesMediaDirPath(),
                $this->cx->getWebsiteImagesMediaDirWebPath(),
            ),
            'downloads' => array(
                $this->cx->getWebsiteImagesDownloadsPath(),
                $this->cx->getWebsiteImagesDownloadsWebPath(),
            ),
            'calendar' => array(
                $this->cx->getWebsiteImagesCalendarPath(),
                $this->cx->getWebsiteImagesCalendarWebPath(),
            ),
            'podcast' => array(
                $this->cx->getWebsiteImagesPodcastPath(),
                $this->cx->getWebsiteImagesPodcastWebPath(),
            ),
            'blog' => array(
                $this->cx->getWebsiteImagesBlogPath(),
                $this->cx->getWebsiteImagesBlogWebPath(),
            ),
            'Wysiwyg' => array(
                $this->cx->getWebsiteImagesPath() . '/wysiwyg',
                $this->cx->getWebsiteImagesWebPath() . '/wysiwyg',
            ),
        );

        foreach ($this->allMediaTypePaths as $mediatype => $path) {
            if (FileSystem::checkMediaTypePermission($mediatype)) {
                $this->mediaTypePaths[$mediatype] = $path;
            } else {
                unset($this->mediaTypes[$mediatype]);
            }
        }


    }


    /**
     * @return array
     */
    public static function getThumbnails()
    {
        return self::$thumbnails;
    }

    /**
     * @return array
     */
    public function getMediaTypes()
    {
        return $this->mediaTypes;
    }

    /**
     * @return array
     */
    public function getMediaTypePaths()
    {
        return $this->mediaTypePaths;
    }

    /**
     * @return array
     */
    public function getMediaTypePathsbyName($name)
    {
        return $this->mediaTypePaths[$name];
    }

    /**
     * @return array
     */
    public function getMediaTypePathsbyNameAndOffset($name, $offset)
    {
        return $this->mediaTypePaths[$name][$offset];
    }

    public function getAllMediaTypePaths()
    {

    }

    /**
     * prevent the instance from being cloned
     *
     * @return void
     */
    protected function __clone()
    {
    }

    /**
     * prevent from being unserialized
     *
     * @return void
     */
    protected function __wakeup()
    {
    }


}
