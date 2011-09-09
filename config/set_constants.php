<?php

/**
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  config
 * @todo        Edit PHP DocBlocks!
 */

global $_PATHCONFIG, $_DBCONFIG, $_CONFIG;
static $match = null;

/**
 * Define constants
 */
//define('ASCMS_PATH',                      dirname(dirname(dirname(realpath(__FILE__)))));
define('ASCMS_PATH',                        $_PATHCONFIG['ascms_root']);
define('ASCMS_PATH_OFFSET',                 $_PATHCONFIG['ascms_root_offset']); // example '/cms'
define('ASCMS_BACKEND_PATH',                '/cadmin');
define('ASCMS_PROTOCOL',                    empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' ? 'http' : 'https');

define('CONTREXX_ESCAPE_GPC',               get_magic_quotes_gpc());
define('CONTREXX_CHARSET',                  $_CONFIG['coreCharacterEncoding']);
define('CONTREXX_PHP5',                     version_compare(PHP_VERSION, '5', '>='));
define('CONTREXX_DIRECTORY_INDEX',          'index.php');
define('CONTREXX_VIRTUAL_LANGUAGE_PATH',    !empty($_SERVER['REDIRECT_CONTREXX_LANG_PREFIX']) ? '/'.$_SERVER['REDIRECT_CONTREXX_LANG_PREFIX'] : '');
define('CONTREXX_SCRIPT_PATH',              ASCMS_PATH_OFFSET.CONTREXX_VIRTUAL_LANGUAGE_PATH.'/'.CONTREXX_DIRECTORY_INDEX);

define('DBPREFIX',                          $_DBCONFIG['tablePrefix']);
define('ASCMS_DOCUMENT_ROOT',               ASCMS_PATH.ASCMS_PATH_OFFSET);
define('ASCMS_ADMIN_PATH',                  ASCMS_DOCUMENT_ROOT. ASCMS_BACKEND_PATH);
define('ASCMS_ADMIN_WEB_PATH',              ASCMS_PATH_OFFSET. ASCMS_BACKEND_PATH);
define('ASCMS_ADMIN_TEMPLATE_PATH',         ASCMS_DOCUMENT_ROOT. ASCMS_BACKEND_PATH.'/template/ascms');
define('ASCMS_ADMIN_TEMPLATE_WEB_PATH',     ASCMS_PATH_OFFSET. ASCMS_BACKEND_PATH.'/template/ascms');
define('ASCMS_API_PATH',                    ASCMS_DOCUMENT_ROOT.'/core/API');
define('ASCMS_FRAMEWORK_PATH',              ASCMS_DOCUMENT_ROOT.'/lib/FRAMEWORK');
define('ASCMS_BACKUP_PATH',                 ASCMS_DOCUMENT_ROOT. ASCMS_BACKEND_PATH.'/backup');
define('ASCMS_BACKUP_WEB_PATH',             ASCMS_PATH_OFFSET. ASCMS_BACKEND_PATH.'/backup');
define('ASCMS_CORE_PATH',                   ASCMS_DOCUMENT_ROOT.'/core');
define('ASCMS_CONTENT_IMAGE_PATH',          ASCMS_DOCUMENT_ROOT.'/images/content');
define('ASCMS_CONTENT_IMAGE_WEB_PATH',      ASCMS_PATH_OFFSET.'/images/content');
define('ASCMS_FEED_PATH',                   ASCMS_DOCUMENT_ROOT.'/feed');
define('ASCMS_FEED_WEB_PATH',               ASCMS_PATH_OFFSET.'/feed');
define('ASCMS_FORUM_UPLOAD_PATH',           ASCMS_DOCUMENT_ROOT.'/media/forum/upload');
define('ASCMS_FORUM_UPLOAD_WEB_PATH',       ASCMS_PATH_OFFSET.'/media/forum/upload');
define('ASCMS_GALLERY_THUMBNAIL_WEB_PATH',  ASCMS_PATH_OFFSET.'/images/gallery_thumbs');
define('ASCMS_GALLERY_THUMBNAIL_PATH',      ASCMS_DOCUMENT_ROOT.'/images/gallery_thumbs');
define('ASCMS_GALLERY_IMPORT_WEB_PATH',     ASCMS_PATH_OFFSET.'/images/gallery_import');
define('ASCMS_GALLERY_IMPORT_PATH',         ASCMS_DOCUMENT_ROOT.'/images/gallery_import');
define('ASCMS_GALLERY_WEB_PATH',            ASCMS_PATH_OFFSET.'/images/gallery');
define('ASCMS_GALLERY_PATH',                ASCMS_DOCUMENT_ROOT.'/images/gallery');
define('ASCMS_LANGUAGE_PATH',               ASCMS_DOCUMENT_ROOT.'/lang');
define('ASCMS_LIBRARY_PATH',                ASCMS_DOCUMENT_ROOT.'/lib');
define('ASCMS_MEDIA1_PATH',                 ASCMS_DOCUMENT_ROOT.'/media/archive1');
define('ASCMS_MEDIA1_WEB_PATH',             ASCMS_PATH_OFFSET.'/media/archive1');
define('ASCMS_MEDIA2_PATH',                 ASCMS_DOCUMENT_ROOT.'/media/archive2');
define('ASCMS_MEDIA2_WEB_PATH',             ASCMS_PATH_OFFSET.'/media/archive2');
define('ASCMS_MEDIA3_PATH',                 ASCMS_DOCUMENT_ROOT.'/media/archive3');
define('ASCMS_MEDIA3_WEB_PATH',             ASCMS_PATH_OFFSET.'/media/archive3');
define('ASCMS_MEDIA4_PATH',                 ASCMS_DOCUMENT_ROOT.'/media/archive4');
define('ASCMS_MEDIA4_WEB_PATH',             ASCMS_PATH_OFFSET.'/media/archive4');
define('ASCMS_MEDIA_PATH',                  ASCMS_DOCUMENT_ROOT.'/media');
define('ASCMS_MEDIA_WEB_PATH',              ASCMS_PATH_OFFSET.'/media');
define('ASCMS_MODULE_PATH',                 ASCMS_DOCUMENT_ROOT.'/modules');
define('ASCMS_MODULE_WEB_PATH',             ASCMS_PATH_OFFSET.'/modules');
define('ASCMS_CORE_MODULE_PATH',            ASCMS_DOCUMENT_ROOT.'/core_modules');
define('ASCMS_CORE_MODULE_WEB_PATH',        ASCMS_PATH_OFFSET.'/core_modules');
define('ASCMS_MODULE_IMAGE_WEB_PATH',       ASCMS_PATH_OFFSET.'/images/modules');
define('ASCMS_MODULE_IMAGE_PATH',           ASCMS_DOCUMENT_ROOT.'/images/modules');
define('ASCMS_NEWSLETTER_ATTACH_PATH',      ASCMS_DOCUMENT_ROOT.'/images/attach');
define('ASCMS_NEWSLETTER_ATTACH_WEB_PATH',  ASCMS_PATH_OFFSET.'/images/attach');
define('ASCMS_NEWSLETTER_ATTACHMENT',       ASCMS_MODULE_PATH.'/newsletter/upload');
define('ASCMS_SHOP_IMAGES_PATH',            ASCMS_DOCUMENT_ROOT.'/images/shop');
define('ASCMS_SHOP_IMAGES_WEB_PATH',        ASCMS_PATH_OFFSET.'/images/shop');
define('ASCMS_BLOG_IMAGES_PATH',            ASCMS_DOCUMENT_ROOT.'/images/blog');
define('ASCMS_BLOG_IMAGES_WEB_PATH',        ASCMS_PATH_OFFSET.'/images/blog');
define('ASCMS_PODCAST_IMAGES_PATH',         ASCMS_DOCUMENT_ROOT.'/images/podcast');
define('ASCMS_PODCAST_IMAGES_WEB_PATH',     ASCMS_PATH_OFFSET.'/images/podcast');
define('ASCMS_DOWNLOADS_IMAGES_PATH',       ASCMS_DOCUMENT_ROOT.'/images/downloads');
define('ASCMS_DOWNLOADS_IMAGES_WEB_PATH',   ASCMS_PATH_OFFSET.'/images/downloads');
define('ASCMS_DATA_IMAGES_PATH',            ASCMS_DOCUMENT_ROOT.'/images/data');
define('ASCMS_DATA_IMAGES_WEB_PATH',        ASCMS_PATH_OFFSET.'/images/data');
define('ASCMS_THEMES_WEB_PATH',             ASCMS_PATH_OFFSET.'/themes');
define('ASCMS_THEMES_PATH',                 ASCMS_DOCUMENT_ROOT.'/themes');
define('ASCMS_ACCESS_PROFILE_IMG_WEB_PATH', ASCMS_PATH_OFFSET.'/images/access/profile');
define('ASCMS_ACCESS_PROFILE_IMG_PATH',     ASCMS_DOCUMENT_ROOT.'/images/access/profile');
define('ASCMS_ACCESS_PHOTO_IMG_WEB_PATH',   ASCMS_PATH_OFFSET.'/images/access/photo');
define('ASCMS_ACCESS_PHOTO_IMG_PATH',       ASCMS_DOCUMENT_ROOT.'/images/access/photo');
//define('ASCMS_THEMES_IMAGE_PATH',           ASCMS_DOCUMENT_ROOT.'/images/themes');
//define('ASCMS_THEMES_IMAGE_WEB_PATH',       ASCMS_PATH_OFFSET.'/images/themes');
define('ASCMS_IMAGE_PATH',                  ASCMS_PATH_OFFSET.'/images');
define('ASCMS_TEMP_PATH',                   ASCMS_DOCUMENT_ROOT.'/tmp');
define('ASCMS_TEMP_WEB_PATH',               ASCMS_PATH_OFFSET.'/tmp');
define('ASCMS_DIR_PATH',                    ASCMS_DOCUMENT_ROOT.'/modules/directory');
define('ASCMS_DIR_WEB_PATH',                ASCMS_PATH_OFFSET.'/modules/directory');
define('ASCMS_DIRECTORY_FEED_PATH',         ASCMS_DOCUMENT_ROOT.'/media/directory/feeds');
define('ASCMS_DIRECTORY_FEED_WEB_PATH',     ASCMS_PATH_OFFSET.'/media/directory/feeds');
define('ASCMS_MODULE_MEDIA_PATH',           ASCMS_DOCUMENT_ROOT.'/media/directory');
define('ASCMS_MODULE_MEDIA_WEB_PATH',       ASCMS_PATH_OFFSET.'/media/directory');
define('ASCMS_MARKET_MEDIA_PATH',           ASCMS_DOCUMENT_ROOT.'/media/market');
define('ASCMS_MARKET_MEDIA_WEB_PATH',       ASCMS_PATH_OFFSET.'/media/market');
define('ASCMS_CACHE_PATH',                  ASCMS_DOCUMENT_ROOT.'/cache');
define('ASCMS_ECARD_OPTIMIZED_PATH',        ASCMS_DOCUMENT_ROOT.'/images/modules/ecard/ecards_optimized');
define('ASCMS_ECARD_OPTIMIZED_WEB_PATH',    ASCMS_PATH_OFFSET.'/images/modules/ecard/ecards_optimized');
define('ASCMS_ECARD_SEND_ECARDS_PATH',      ASCMS_DOCUMENT_ROOT.'/images/modules/ecard/send_ecards');
define('ASCMS_ECARD_SEND_ECARDS_WEB_PATH',  ASCMS_PATH_OFFSET.'/images/modules/ecard/send_ecards');
define('ASCMS_ECARD_THUMBNAIL_PATH',        ASCMS_DOCUMENT_ROOT.'/images/modules/ecard/thumbnails');
define('ASCMS_ECARD_THUMBNAIL_WEB_PATH',    ASCMS_PATH_OFFSET.'/images/modules/ecard/thumbnails');
define('ASCMS_PARTNERS_IMAGES_PATH',        ASCMS_DOCUMENT_ROOT.'/images/partners');
define('ASCMS_PARTNERS_IMAGES_WEB_PATH',    ASCMS_PATH_OFFSET.'/images/partners');
define('ASCMS_MODEL_PATH',                  ASCMS_DOCUMENT_ROOT.'/model');
define('ASCMS_MODEL_PROXIES_PATH',          ASCMS_TEMP_PATH.'/model/proxies');

// This is like the usual *_WEB_PATH, relative to ASCMS_PATH.
// Like this, only one path needs to be defined for each purpose,
// the new File class methods will prepend ASCMS_PATH themselves
// when needed.
define('ASCMS_HOTELCARD_IMAGES_FOLDER',     ASCMS_PATH_OFFSET.'/images/hotelcard');

define('ASCMS_MEDIADIR_IMAGES_WEB_PATH',    ASCMS_PATH_OFFSET.'/images/mediadir');
define('ASCMS_MEDIADIR_IMAGES_PATH',        ASCMS_DOCUMENT_ROOT.'/images/mediadir');

// For convenience, as seen in the frontend
define('ADMIN_SCRIPT_PATH',                 ASCMS_ADMIN_WEB_PATH.'/'.CONTREXX_DIRECTORY_INDEX);

// Testing
define('ASCMS_TEST_PATH',                   ASCMS_DOCUMENT_ROOT.'/testing');


/***********************************
 Proposed new folder constant format
 ***********************************
The new proposed short folder constants replace both old *_WEB_PATH and
*_PATH versions above.  They *ALWAYS* end in "_FOLDER", and are relative to
ASCMS_DOCUMENT_ROOT, so you prepend them with either that, ASCMS_PATH_OFFSET,
or nothing.  Note that the rewritten (static) File class methods take full
advantage of paths created like this.
File and folder paths created and stored this way are portable and will
never break when migrating systems!
Remove the old constants above once you have rewritten your module.
*/
// TODO: Write methods File::contrexx_require/-_once() and maybe
// File::contrexx_include()
// Common folders
define('ASCMS_MODULE_FOLDER',               '/modules');
define('ASCMS_CORE_MODULE_FOLDER',          '/core_modules');
define('ASCMS_IMAGES_FOLDER',               '/images');
// Append the MODULE_NAME to ASCMS_IMAGES_FOLDER when appropriate, i.e.
//define('ASCMS_IMAGES_HOTELCARD_F0LDER',     ASCMS_IMAGES_FOLDER.'/hotelcard');


/******************************************
 Common and localized date and time formats
 ******************************************/
// Note that these are universal and need not be localized.
define('ASCMS_DATE_FORMAT_DATE',     'Y-m-d');
define('ASCMS_DATE_FORMAT_DATETIME', 'Y-m-d H:i:s');
define('ASCMS_DATE_FORMAT_TIME',     'H:i:s');

// TODO: Localize.
define('ASCMS_DATE_FORMAT',           'H:i:s d.m.Y');
define('ASCMS_DATE_SHORT_FORMAT',     'd.m.Y');
define('ASCMS_DATE_FILE_FORMAT',      'd.m.Y H:i:s');
define('ASCMS_DATE_FORMAT_UI_DATE',   'dd.mm.yy');  // jqueryui.datepicker
// Like "Mo, 09.10.2011"
define('ASCMS_DATE_FORMAT_DOW_D_M_Y', 'w, d.m.Y');
// TODO: More localized formats for numbers, currencies, etc.
// Use with sprintf() in your methods, i.e.:
define('ASCMS_CURRENCY_FORMAT_UNIT', '%2$s %1$.2f');
// Use the above with
//  sprintf(ASCMS_CURRENCY_FORMAT_UNIT, (float)$amount, (string)$unit)
// where $unit is something like "sFr.", "$", or "�"
define('ASCMS_CURRENCY_FORMAT_CODE', '%2$s %1$.2f');
// Use the above with
//  sprintf(ASCMS_CURRENCY_FORMAT_CODE, (float)$amount, (string)$code)
// where $code is something like "CHF", "USD", or "EUR"
define('ASCMS_CURRENCY_SEPARATOR_THOUSANDS', '\'');
define('ASCMS_CURRENCY_SEPARATOR_DECIMALS', '.');
define('ASCMS_NUMBER_SEPARATOR_THOUSANDS', '\'');
define('ASCMS_NUMBER_SEPARATOR_DECIMALS', '.');
