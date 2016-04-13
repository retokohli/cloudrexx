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


namespace Cx\Core\ViewManager\Model\Entity;

/**
 * Class ViewManagerFileSystem
 *
 * @copyright   Cloudrexx AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_viewmanager
 */
class ViewManagerFileSystem extends \Cx\Core\MediaSource\Model\Entity\LocalFileSystem
{
    /**
     * @var \Cx\Core\MediaSource\Model\Entity\LocalFileSystem
     */
    protected $codeBaseFileSystem;
    /**
     * @var \Cx\Core\MediaSource\Model\Entity\LocalFileSystem
     */
    protected $serverWebsiteFileSystem;

    function __construct($path)
    {
        parent::__construct($path);

        if ($path != $this->cx->getCodeBaseThemesPath()) {
            $this->codeBaseFileSystem
                = new \Cx\Core\MediaSource\Model\Entity\LocalFileSystem($this->cx->getCodeBaseThemesPath());
        }

        //Initialize the server website path and its themes path
        //if the current website mode is set as client and server website repository is set
        $websiteMode     = \Cx\Core\Setting\Controller\Setting::getValue('website_mode','MultiSite');
        $serverWebsiteId = \Cx\Core\Setting\Controller\Setting::getValue('website_server','MultiSite');
        if ($websiteMode == \Cx\Core_Modules\MultiSite\Controller\ComponentController::WEBSITE_MODE_CLIENT && !empty($serverWebsiteId)) {
            $response = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSiteController::executeCommandOnMyServiceServer('getServerWebsitePath', array('websiteId' => $serverWebsiteId));
            if ($response && $response->status == 'success' && !empty($response->data->serverWebsitePath)) {
                $serverWebsitePath = $response->data->serverWebsitePath;
                $this->serverWebsiteFileSystem
                    = new \Cx\Core\MediaSource\Model\Entity\LocalFileSystem($serverWebsitePath->documentRootPath . \Cx\Core\Core\Controller\Cx::FOLDER_NAME_THEMES);
            }
        }
    }

    /**
     * @param            $directory
     * @param bool|false $recursive
     *
     * @return array
     */
    public function getFileList($directory, $recursive = false)
    {
        $fileList = array();
        if ($this->codeBaseFileSystem) {
            $fileList = $this->codeBaseFileSystem->getFileList(
                $directory, $recursive, true
            );
        }
        if ($this->serverWebsiteFileSystem) {
            $serverWebsiteFileList = $this->serverWebsiteFileSystem->getFileList(
                $directory, $recursive, true
            );
            $fileList = $this->mergeFileList($fileList, $serverWebsiteFileList);
        }
        if (file_exists(rtrim($this->getRootPath() . '/' . $directory,'/'))) {
            $websiteFileList = parent::getFileList($directory, $recursive);
            $fileList        = $this->mergeFileList($fileList, $websiteFileList);
        }
        return $fileList;
    }

    /**
     * Merge two file lists into one
     *
     * @param $a
     * @param $b
     *
     * @return array
     */
    public function mergeFileList($a, $b)
    {
        if (empty($b)) {
            return $a;
        }
        if (empty($a)) {
            return $b;
        }
        if (!is_array($b)) {
            return $b;
        }
        $resultFileList = $a;
        foreach ($b as $name => $directory) {
            $filesList = $this->mergeFileList(
                    isset($resultFileList[$name]) ? $resultFileList[$name] : '', $directory
            );
            $resultFileList[$name] = $filesList;
        }
        return $resultFileList;
    }

    /**
     * Check whether file is directory
     *
     * @param \Cx\Core\MediaSource\Model\Entity\File $file
     *
     * @return boolean True on success, false otherwise
     */
    public function isDirectory(\Cx\Core\MediaSource\Model\Entity\File $file)
    {
        return is_dir($this->getFullPath($file));
    }

    /**
     * Check whether file is directory
     *
     * @param \Cx\Core\MediaSource\Model\Entity\File $file
     *
     * @return boolean True on success, false otherwise
     */
    public function isFile(\Cx\Core\MediaSource\Model\Entity\File $file)
    {
        return is_file($this->getFullPath($file));
    }

    /**
     * Check whether file exists in the filesytem
     *
     * @param \Cx\Core\MediaSource\Model\Entity\File $file
     *
     * @return boolean True when exists, false otherwise
     */
    public function fileExists(\Cx\Core\MediaSource\Model\Entity\File $file)
    {
        return file_exists($this->getFullPath($file));
    }

    /**
     * Read the contents from given file,
     * Check whether the file exists before calling this function
     *
     * @param \Cx\Core\MediaSource\Model\Entity\File $file
     *
     * @return string file content
     */
    public function readFile(
        \Cx\Core\MediaSource\Model\Entity\File $file
    ) {
        return file_get_contents($this->getFullPath($file));
    }

    /**
     * Get full path of the given file,
     * If file is application template then load from website/codebase path
     * else
     * Path will be checked in the following order
     * 1. website repository
     * 2. server website repository
     * 3. codebase repository
     *
     * @param \Cx\Core\MediaSource\Model\Entity\File $file
     *
     * @return string
     */
    public function getFullPath(\Cx\Core\MediaSource\Model\Entity\File $file)
    {
        $basePath = $this->getRootPath();
        if ($file->isApplicationTemplateFile()) {
            if (file_exists($this->cx->getWebsiteDocumentRootPath() . $file->__toString())) {
                $basePath = $this->cx->getWebsiteDocumentRootPath();
            } else {
                $basePath = $this->cx->getCodeBaseDocumentRootPath();
            }
        } elseif (file_exists($this->getRootPath() . '/' . $file->__toString())) {
            $basePath = $this->getRootPath();
        } elseif ($this->serverWebsiteFileSystem && file_exists($this->serverWebsiteFileSystem->getRootPath() . '/' . $file->__toString())) {
            $basePath = $this->serverWebsiteFileSystem->getRootPath();
        } elseif ($this->codeBaseFileSystem && file_exists($this->codeBaseFileSystem->getRootPath() . '/' . $file->__toString())) {
            $basePath = $this->codeBaseFileSystem->getRootPath();
        }
        return $basePath . '/' . $file->__toString();
    }

    /**
     * Check whether the file is read only
     * 
     * @param \Cx\Core\MediaSource\Model\Entity\File $file
     *
     * @return boolean
     */
    public function isReadOnly(\Cx\Core\MediaSource\Model\Entity\File $file)
    {
        if (file_exists($this->getRootPath() . '/' . $file->__toString())) {
            return false;
        }
        return true;
    }

    /**
     * Check whether the file is resettable
     *
     * @param \Cx\Core\MediaSource\Model\Entity\File $file
     *
     * @return boolean
     */
    public function isResettable(\Cx\Core\MediaSource\Model\Entity\File $file)
    {
        $isFileExistsInWebsite = file_exists($this->getRootPath() . '/' . $file->__toString());
        if (   $this->serverWebsiteFileSystem
            && $isFileExistsInWebsite
            && file_exists($this->serverWebsiteFileSystem->getRootPath() . '/' . $file->__toString())
        ) {
            return true;
        }
        if (   $this->cx->getWebsiteThemesPath() != $this->cx->getCodeBaseThemesPath()
            && $this->codeBaseFileSystem
            && $isFileExistsInWebsite
            && file_exists($this->codeBaseFileSystem->getRootPath() . '/' . $file->__toString())
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check whether the file is image
     *
     * @param \Cx\Core\MediaSource\Model\Entity\File $file
     *
     * @return boolean
     */
    public function isImageFile(\Cx\Core\MediaSource\Model\Entity\File $file)
    {
        return $this->isImage($file->getExtension());
    }
}
