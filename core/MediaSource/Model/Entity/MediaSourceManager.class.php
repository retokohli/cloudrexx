<?php

/**
 * class MediaSourceManager
 *
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 *              Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_mediasource
 */

namespace Cx\Core\MediaSource\Model\Entity;

use Cx\Core\Core\Controller\Cx;
use Cx\Model\Base\EntityBase;

/**
 * Class MediaSourceManager
 *
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 *              Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_mediasource
 */
class MediaSourceManager extends EntityBase
{

    /**
     * @var \Cx\Core\Core\Controller\Cx
     */
    protected $cx;

    protected $mediaTypes = array();

    /**
     * @var array
     */
    protected $mediaTypePaths;

    /**
     * @var MediaSource[]
     */
    protected $allMediaTypePaths = array();

    /**
     * @param $cx Cx
     *
     * @throws \Cx\Core\Event\Controller\EventManagerException
     */
    public function __construct($cx) {
        $this->cx             = $cx;
        $eventHandlerInstance = $this->cx->getEvents();

        /**
         * Loads all mediatypes into $this->allMediaTypePaths
         */
        $eventHandlerInstance->triggerEvent('mediasource.load', array($this));

        ksort($this->allMediaTypePaths);
        foreach ($this->allMediaTypePaths as $mediaSource) {
            /**
             * @var $mediaSource MediaSource
             */
            if ($mediaSource->checkAccess()) {
                $this->mediaTypePaths[$mediaSource->getName()] = $mediaSource->getDirectory();
                $this->mediaTypes[$mediaSource->getName()] = $mediaSource;
            }
        }
    }

    /**
     * Get the absolute path from the virtual path.
     * If the path is already absolute nothing will happen to it.
     *
     * @param $virtualPath string The virtual Path
     *
     * @return string The absolute Path
     */
    public static function getAbsolutePath($virtualPath) {
        if (self::isVirtualPath(
            $virtualPath
        )
        ) {
            $pathArray = explode('/', $virtualPath);
            return Cx::instanciate()->getMediaSourceManager()
                ->getMediaTypePathsbyNameAndOffset(array_shift($pathArray), 0)
            . '/' . join(
                '/', $pathArray
            );
        }
        return $virtualPath;
    }

    /**
     * Get the web path from the virtual path.
     * If the path is already formed for web nothing will happen to it.
     *
     * @param $virtualPath string The virtual Path
     *
     * @return string The web Path
     */
    public static function getWebPath($virtualPath) {
        if ($virtualPath) {
            $file = preg_replace('#\\\\#', '/', $virtualPath);
            $file = preg_replace(
                '#' . preg_quote(
                    \Env::get('cx')->getWebsiteDocumentRootPath(), '#'
                ) . '#', '', $file
            );
            $file = preg_replace(
                '#' . preg_quote(
                    \Env::get('cx')->getCodeBaseDocumentRootPath(), '#'
                ) . '#', '', $file
            );
            return \Env::get('cx')->getWebsiteOffsetPath() . $file;
        }
        return $virtualPath;
    }

    /**
     * Checks if $subdirectory is a subdirectory of $path.
     * You can use a virtual path as a parameter.
     *
     * @param $path
     * @param $subdirectory
     *
     * @return boolean
     */
    public static function isSubdirectory($path, $subdirectory) {
        $absolutePath = self::getAbsolutePath($path);
        $absoluteSubdirectory = self::getAbsolutePath($subdirectory);
        return (boolean)preg_match(
            '#^' . preg_quote($absolutePath, '#') . '#', $absoluteSubdirectory
        );
    }

    /**
     * Checks permission
     *
     * @param $path
     *
     * @return bool
     */
    public static function checkPermissions($path) {
        foreach (
            Cx::instanciate()->getMediaSourceManager()->getMediaTypePaths() as
            $virtualPathName => $mediatype
        ) {
            if (self::isSubdirectory($virtualPathName, $path)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if a path is virtual or real.
     *
     * ``` php
     * \Cx\Core_Modules\MediaBrowser\Model\FileSystem::isVirtualPath('files/Movies'); // Returns true
     * ```
     *
     * @param $path
     *
     * @return bool
     */
    public static function isVirtualPath($path) {
        return !(strpos($path, '/') === 0);
    }

    public function addMediaType(MediaSource $mediaType) {
        $this->allMediaTypePaths[$mediaType->getPosition()
        . $mediaType->getName()] = $mediaType;
    }



    /**
     * @return MediaSource[]
     */
    public function getMediaTypes() {
        return $this->mediaTypes;
    }


    /**
     * @param $name string
     *
     * @return MediaSource
     * @throws MediaSourceException
     */
    public function getMediaType($name) {
        if(!isset($this->mediaTypes[$name])){
            throw new MediaSourceException("No such mediatype available");
        }
        return $this->mediaTypes[$name];
    }

    /**
     * @return array
     */
    public function getMediaTypePaths() {
        return $this->mediaTypePaths;
    }

    /**
     * @param $name
     *
     * @return array
     */
    public function getMediaTypePathsbyName($name) {
        return $this->mediaTypePaths[$name];
    }

    /**
     * @param $name
     * @param $offset
     *
     * @return array
     */
    public function getMediaTypePathsbyNameAndOffset($name, $offset) {
        return $this->mediaTypePaths[$name][$offset];
    }

    public function getAllMediaTypePaths() {
        return $this->allMediaTypePaths;
    }

}
