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

use Cx\Core_Modules\MediaBrowser\Model\MediaType;

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

    protected $mediaTypes = array();

    /**
     * @var array
     */
    protected $mediaTypePaths;

    /**
     * @var MediaType[]
     */
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
        $eventHandlerInstance = $this->cx->getEvents();
        /**
         * Loads all mediatypes into $this->allMediaTypePaths
         */
        $eventHandlerInstance->triggerEvent('LoadMediaTypes', array($this));
        ksort($this->allMediaTypePaths);
        foreach ($this->allMediaTypePaths as $mediatype) {
            if ($mediatype->checkAccess()) {
                $this->mediaTypePaths[$mediatype->getName()] = $mediatype->getDirectory();
                $this->mediaTypes[$mediatype->getName()] = $mediatype;
            }
        }
    }

    public function addMediaType(MediaType $mediaType)
    {
        $this->allMediaTypePaths[$mediaType->getPosition().$mediaType->getName()] = $mediaType;
    }


    /**
     * @return array
     */
    public static function getThumbnails()
    {
        return self::$thumbnails;
    }

    /**
     * @return MediaType[]
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
        return $this->allMediaTypePaths;
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
