<?php

/**
 * Specific FrontendController for this Component. Use this to easily create a frontent view
 *
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage coremodule_mediabrowser
 */

namespace Cx\Core_Modules\MediaBrowser\Controller;

class MediaBrowserConfiguration {


    protected static $thumbnails;

    /**
     * @var self reference to singleton instance
     */
    protected static $instance;

    /**
     * @var \Cx\Core\Core\Controller\Cx
     */
    protected $cx;

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
    {}


    /**
     * @return array
     */
    public static function getThumbnails()
    {
        return self::$thumbnails;
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

    public $mediaTypes = array(
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
    );

    public $mediaTypePaths = array(
        'files' => array(ASCMS_CONTENT_IMAGE_PATH, ASCMS_CONTENT_IMAGE_WEB_PATH,),
        'media1' => array(ASCMS_MEDIA1_PATH, ASCMS_MEDIA1_WEB_PATH,),
        'media2' => array(ASCMS_MEDIA2_PATH, ASCMS_MEDIA2_WEB_PATH,),
        'media3' => array(ASCMS_MEDIA3_PATH, ASCMS_MEDIA3_WEB_PATH,),
        'media4' => array(ASCMS_MEDIA4_PATH, ASCMS_MEDIA4_WEB_PATH,),
        'attach' => array(ASCMS_ATTACH_PATH, ASCMS_ATTACH_WEB_PATH,),
        'shop' => array(ASCMS_SHOP_IMAGES_PATH, ASCMS_SHOP_IMAGES_WEB_PATH,),
        'gallery' => array(ASCMS_GALLERY_PATH, ASCMS_GALLERY_WEB_PATH,),
        'access' => array(ASCMS_ACCESS_PATH, ASCMS_ACCESS_WEB_PATH,),
        'mediadir' => array(ASCMS_MEDIADIR_IMAGES_PATH, ASCMS_MEDIADIR_IMAGES_WEB_PATH,),
        'downloads' => array(ASCMS_DOWNLOADS_IMAGES_PATH, ASCMS_DOWNLOADS_IMAGES_WEB_PATH,),
        'calendar' => array(ASCMS_CALENDAR_IMAGE_PATH, ASCMS_CALENDAR_IMAGE_WEB_PATH,),
        'podcast' => array(ASCMS_PODCAST_IMAGES_PATH, ASCMS_PODCAST_IMAGES_WEB_PATH,),
        'blog' => array(ASCMS_BLOG_IMAGES_PATH, ASCMS_BLOG_IMAGES_WEB_PATH,),
    );
}
