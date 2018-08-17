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
 * @author      Sam Hawkes <sam.hawkes@comvation.com>
 * @package     cloudrexx
 */


namespace Cx\Core\MediaSource\Model\Event;

class MediaSourceEventListener extends \Cx\Core\Event\Model\Entity\DefaultEventListener
{
    /**
     *  Add event - add new index
     *
     * @param $info array information from file/ directory
     *
     * @return void
     */
    protected function mediaSourceAdd($info)
    {
        $this->index($info);
    }

    /**
     *  Edit event - update an index
     *
     * @param $info array information from file/ directory
     *
     * @return void
     */
    protected function mediaSourceEdit($info)
    {
        $this->index($info);
    }

    /**
     *  Remove event - remove an index
     *
     * @param $info array information from file/ directory
     *
     * @return void
     */
    protected function mediaSourceRemove($fileInfo)
    {
        $fullPath = $fileInfo['path'] . $fileInfo['name'];
        $file = new \Cx\Core\MediaSource\Model\Entity\LocalFile(
            $fullPath, null
        );
        $indexer = $this->cx->getComponent('MediaSource')->getIndexer(
            $file->getExtension()
        );

        if (empty($indexer)) {
            return;
        }

        $indexer->clearIndex($fullPath);
    }

    /**
     * Get all file paths and get the appropriate index for each file to be able
     * to index the file
     */
    protected function index($fileInfo)
    {
        $filePaths = array();
        $fullPath = $fileInfo['path'];
        $fullOldPath = $fileInfo['oldPath'];
        $path = $fullPath;
        $tmpPath = $fileInfo['tmpPath'];

        if (!empty($fullOldPath)) {
            $path = $fullOldPath;
        }

        if (is_dir($fullOldPath)) {
            $mediaSource = new \Cx\Core\MediaSource\Model\Entity\MediaSource(
                '', '', $fullOldPath
            );
            // Get all files and directories
            $fileList = $mediaSource->getFileSystem()->getFileList(
                $fullOldPath
            );
            // Get an array with all file paths
            $filePaths = $this->getAllFilePaths(
                $fileList,
                $path,
                '',
                array()
            );
        } else {
            array_push($filePaths, $path);
        }

        foreach ($filePaths as $path) {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $indexer = $this->cx->getComponent('MediaSource')->getIndexer(
                $extension
            );

            if (!$indexer) {
                continue;
            }

            $filePath = str_replace($fullOldPath, $fullPath, $path);

            $indexer->index($filePath, $path, $tmpPath);
        }
    }

    /**
     * Returns an array with all file paths of all files in this directory,
     * including files located in another directory.
     *
     * @param $fileList array  all files and directorys
     * @param $path     string path from this directory
     * @param $folder   string in this directory
     * @param $result   array  existing result
     *
     * @return array
     */
    protected function getAllFilePaths(
        $fileList, $path, $folder, $result
    ) {
        foreach ($fileList as $fileEntryKey =>$fileListEntry) {
            $newPath = $path . '/' . $folder .  $fileEntryKey;
            if (is_dir($newPath)) {
                $result = $this->getAllFilePaths(
                    $fileListEntry, $path, $fileEntryKey .'/', $result
                );
            } else if (is_file($newPath)) {
                array_push($result, $newPath);
            }
        }
        return $result;
    }

    public function onEvent($eventName, array $eventArgs)
    {
        $methodName = $eventName;
        if (!method_exists($this, $eventName)) {
            $eventNameParts = preg_split('/[.:\/]/', $eventName);
            $methodName = lcfirst(
                implode(
                    '', array_map('ucfirst', $eventNameParts)
                )
            );
        }
        $this->$methodName($eventArgs);
    }
}