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

use Cx\Core\DataSource\Model\Entity\DataSource;

/**
 * Class MediaSource
 *
 * @copyright   Cloudrexx AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     cloudrexx
 * @subpackage  coremodule_mediabrowser
 */
class MediaSource extends DataSource {

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

    /**
     * @var bool if indexer is activated
     */
    protected $isIndexActivated;

    /**
     * @var \Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController
     */
    protected $systemComponentController;

    public function __construct($name,$humanName, $directory, $accessIds = array(), $position = '',FileSystem $fileSystem = null, \Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController = null) {
        $this->fileSystem = $fileSystem ? $fileSystem : LocalFileSystem::createFromPath($directory[0], $isIndexActivated = true);
        $this->name      = $name;
        $this->position  = $position;
        $this->humanName = $humanName;
        $this->directory = $directory;
        $this->accessIds = $accessIds;
        $this->setIsIndexerActivated($isIndexActivated);

        // Sets provided SystemComponentController
        $this->systemComponentController = $systemComponentController;
        if (!$this->systemComponentController) {
            // Searches a SystemComponentController intelligently by RegEx on backtrace stack frame
            $traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $trace = end($traces);
            if (empty($trace['class'])) {
                throw new MediaBrowserException('No SystemComponentController for ' . __CLASS__ . ' can be found');
            }
            $matches = array();
            preg_match(
                '/Cx\\\\(?:Core|Core_Modules|Modules)\\\\([^\\\\]*)\\\\/',
                $trace['class'],
                $matches
            );
            $this->systemComponentController = $this->getComponent($matches[1]);
        }
    }

    /**
     * Define if indexer is activated
     *
     * @param $activated
     *
     * @return void
     */
    public function setIsIndexerActivated($activated)
    {
        $this->isIndexActivated = $activated;
    }

    /**
     * Get information if indexer is activated
     *
     * @return bool
     */
    public function getIsIndexerActivated()
    {
        return $this->isIndexActivated;
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

    /**
     * @return \Cx\Core\Core\Model\Entity\SystemComponentController
     */
    public function getSystemComponentController() {
        return $this->systemComponentController;
    }

    /**
     * Gets one or more entries from this DataSource
     *
     * If an argument is not provided, no restriction is made for this argument.
     * So if this is called without any arguments, all entries of this
     * DataSource are returned.
     * If no entry is found, an empty array is returned.
     * @param string $elementId (optional) ID of the element if only one is to be returned
     * @param array $filter (optional) field=>value-type condition array, only supports = for now
     * @param array $order (optional) field=>order-type array, order is either "ASC" or "DESC"
     * @param int $limit (optional) If set, no more than $limit results are returned
     * @param int $offset (optional) Entry to start with
     * @param array $fieldList (optional) Limits the result to the values for the fields in this list
     * @throws \Exception If something did not go as planned
     * @return array Two dimensional array (/table) of results (array($row=>array($fieldName=>$value)))
     */
    public function get(
        $elementId = null,
        $filter = array(),
        $order = array(),
        $limit = 0,
        $offset = 0,
        $fieldList = array()
    ) {
        throw new \Exception('Not yet implemented');
    }

    /**
     * Adds a new entry to this DataSource
     * @param array $data Field=>value-type array. Not all fields may be required.
     * @throws \Exception If something did not go as planned
     */
    public function add($data) {
        throw new \Exception('Not yet implemented');
    }

    /**
     * Updates an existing entry of this DataSource
     * @param string $elementId ID of the element to update
     * @param array $data Field=>value-type array. Not all fields are required.
     * @throws \Exception If something did not go as planned
     */
    public function update($elementId, $data) {
        throw new \Exception('Not yet implemented');
    }

    /**
     * Drops an entry from this DataSource
     * @param string $elementId ID of the element to update
     * @throws \Exception If something did not go as planned
     */
    public function remove($elementId) {
        throw new \Exception('Not yet implemented');
    }

    /**
     * Get all matches from search term.
     *
     * @param $searchterm string term to search
     *
     * @throws \Cx\Core\Core\Model\Entity\SystemComponentException
     * @return array all file names that match
     */
    public function getFileSystemMatches($searchterm, $path)
    {
        $config = \Env::get('config');
        $fullPath = $this->getDirectory()[0] . $path;
        $fileList = array();
        $searchResult = array();

        if (is_dir($fullPath)) {
            $fileList = $this->getFileSystem()->getFileList($path);
        } else {
            $fileEntry = $this->getFileSystem()->getFileFromPath($fullPath);
            array_push($fileList, $fileEntry);
        }

        $files = $this->getAllFilesAsObject($fileList, $fullPath, array());
        foreach ($files as $file) {
            $fileInformation = array();
            $filePath = $file->getPath() . '/' . $file->getFullName();
            $fileWebPath = $file->getPath() . '/' . $file->getFullName();
            $content = '';
            if ($this->isIndexActivated) {
                $indexer = $this->getComponentController()->getIndexer(
                    $file->getExtension()
                );
                if (!empty($indexer)) {
                    $match = $indexer->getMatch($searchterm, $filePath);
                    if (!empty($match)) {
                        $content = substr(
                            $match->getContent(), 0, $config[
                            'searchDescriptionLength'
                            ]
                        ).'...';
                    }
                }
            }

            if (strpos(strtolower($file->getName()), strtolower($searchterm))
                === false && empty($content)) {
                continue;
            }

            $fileInformation['Score'] = 100;
            $fileInformation['Title'] = ucfirst($file->getName());
            $fileInformation['Content'] = $content;
            $link = explode('/var/www/html', $fileWebPath);
            $fileInformation['Link'] = $link[1];
            $fileInformation['Component'] = $this->getHumanName();
            array_push($searchResult, $fileInformation);
        }
        return $searchResult;
    }

    /**
     * Returns an array with all file paths of all files in this directory,
     * including files located in another directory.
     *
     * @param $fileList array  all files and directorys
     * @param $path     string path from this directory
     * @param $result   array  existing result
     *
     * @return array
     */
    protected function getAllFilesAsObject($fileList, $path, $result)
    {
        foreach ($fileList as $fileEntryKey => $fileListEntry) {
            $newPath = $path  . $fileEntryKey;
            if (is_dir($newPath)) {
                $newPath = $path . $fileEntryKey;
                $result = $this->getAllFilesAsObject(
                    $fileListEntry, $newPath .'/', $result
                );
            } else if (is_file($newPath)) {
                $file = new \Cx\Core\MediaSource\Model\Entity\LocalFile(
                    $newPath, $this->getFileSystem()
                );
                array_push($result, $file);
            }
        }
        return $result;
    }
}
