<?php
/**
 * Specific BackendController for this Component. Use this to easily create a backend view
 *
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */

namespace Cx\Core_Modules\MediaBrowser\Model;


use Cx\Core_Modules\MediaBrowser\Model\FileSystem;
use Cx\Core_Modules\Uploader\Controller\UploaderConfiguration;

class ThumbnailGenerator {

    const THUMBNAIL_GENERATOR_SUCCESS = 'green';
    const THUMBNAIL_GENERATOR_FAIL = 'red';
    const THUMBNAIL_GENERATOR_NEUTRAL = 'yellow';

    /**
     * Create all the thumbnails for a picture.
     *
     * @param string        $path          Path to the file. This can be a virtual path or a absolute path.
     * @param string        $fileNamePlain Plain file name without extension
     * @param string        $fileExtension Extension of the file
     * @param \ImageManager $imageManager
     *
     * <code>
     * <?php
     * \Cx\Core_Modules\MediaBrowser\Model\FileSystem::createThumbnail(
     *      'files/',
     *      'Django,
     *      'jpg',
     *      new ImageManager() // Please recycle the instance and don't create a new anonymous instance for each call.
     *                         // This is just a simple example.
     * );
     * ?>
     * </code>
     *
     * @return array With all thumbnail types and if they were generated successfully.
     */
    public static function createThumbnail(
        $path, $fileNamePlain, $fileExtension, \ImageManager $imageManager
    )
    {
        $success = Array();
        foreach (UploaderConfiguration::getInstance()->getThumbnails() as $thumbnail) {
            if (FileSystem::fileExists($path, $fileNamePlain . $thumbnail['value'] . '.' . $fileExtension)) {
                $success[$thumbnail['value']] = self::THUMBNAIL_GENERATOR_NEUTRAL;
                continue;
            }
            if ($imageManager->_createThumb(
                FileSystem::getAbsolutePath($path),
                '',
                $fileNamePlain . '.' . $fileExtension,
                $thumbnail['size'],
                $thumbnail['quality'],
                $fileNamePlain . $thumbnail['value'] . '.' . $fileExtension
            )
            ) {
                $success[$thumbnail['value']] = self::THUMBNAIL_GENERATOR_SUCCESS;
                continue;
            }
            $success[$thumbnail['value']] = self::THUMBNAIL_GENERATOR_FAIL;
        }
        return $success;
    }


} 