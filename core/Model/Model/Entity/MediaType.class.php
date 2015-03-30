<?php

/**
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */

namespace Cx\Core\Model\Model\Entity;


class MediaType {

    /**
     * Name of the mediatype e.g. files, shop, media1
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $position;

    /**
     * Human readable name
     * @var string
     */
    protected $humanName;

    /**
     * Array with the web and normal path to the directory.
     *
     * e.g:
     * array(
     *      $this->cx->getWebsiteImagesContentPath(),
     *      $this->cx->getWebsiteImagesContentWebPath(),
     * )
     *
     * @var array
     */
    protected $directory = array();

    /**
     * Array with access ids to use with \Permission::checkAccess($id, 'static', true)
     * @var array
     */
    protected $accessIds = array();

    function __construct($name = '',$humanName = '', $directory  = array(), $accessIds = array(), $position = '') {
        $this->name      = $name;
        $this->position  = $position;
        $this->humanName = $humanName;
        $this->directory = $directory;
        $this->accessIds = $accessIds;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @param array $directory
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    /**
     * @return array
     */
    public function getAccessIds()
    {
        return $this->accessIds;
    }

    /**
     * @param array $accessIds
     */
    public function setAccessIds($accessIds)
    {
        $this->accessIds = $accessIds;
    }

    /**
     * @return bool
     */
    public function checkAccess(){
        foreach ($this->accessIds as $id){
            if (!\Permission::checkAccess($id, 'static', true)){
                return false;
            }
        }
        return true;
    }

    /**
     * @return string
     */
    public function getHumanName()
    {
        return $this->humanName;
    }

    /**
     * @param string $humanName
     */
    public function setHumanName($humanName)
    {
        $this->humanName = $humanName;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }


}