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
 * @copyright   Cloudrexx AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     cloudrexx
 * @subpackage  coremodule_mediabrowser
 */

namespace Cx\Core\MediaSource\Model\Entity;

use Cx\Model\Base\EntityBase;

/**
 * Class MediaSource
 *
 * @copyright   Cloudrexx AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     cloudrexx
 * @subpackage  coremodule_mediabrowser
 */
class MediaSource extends EntityBase {

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

    /**
     * @var FileSystem
     */
    protected $fileSystem;

    function __construct($name,$humanName, $directory, $accessIds = array(), $position = '',FileSystem $fileSystem = null) {
        $this->fileSystem = $fileSystem ? $fileSystem : LocalFileSystem::createFromPath($directory[0]);
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

    /**
     * @return FileSystem
     */
    public function getFileSystem() {
        return $this->fileSystem;
    }


}