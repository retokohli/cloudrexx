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

use Cx\Core\MediaSource\Model\Entity\LocalFileSystem;
use Cx\Core\MediaSource\Model\Entity\File;

/**
 * Class ViewManagerFileSystem
 *
 * @copyright   Cloudrexx AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_viewmanager
 */
class ViewManagerFileSystem extends LocalFileSystem
{
    /**
     * @var LocalFileSystem
     */
    protected $codeBaseFileSystem;
    /**
     * @var LocalFileSystem
     */
    protected $serverWebsiteFileSystem;

    function __construct($path)
    {
        parent::__construct($path);

        if ($path != $this->cx->getCodeBaseThemesPath()) {
            $this->codeBaseFileSystem
                = new LocalFileSystem($this->cx->getCodeBaseThemesPath());
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
                    = new LocalFileSystem($serverWebsitePath->documentRootPath . \Cx\Core\Core\Controller\Cx::FOLDER_NAME_THEMES);
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
        $codeBaseFileList = array();
        if ($this->codeBaseFileSystem) {
            $codeBaseFileList = $this->codeBaseFileSystem->getFileList(
                $directory, $recursive, true
            );
        }
        $serverWebsiteFileList = array();
        if ($this->serverWebsiteFileSystem) {
            $serverWebsiteFileList = $this->serverWebsiteFileSystem->getFileList(
                $directory, $recursive, true
            );
        }
        $mergedFiles = $this->mergeFileList($codeBaseFileList, $serverWebsiteFileList);

        $fileList = parent::getFileList($directory, $recursive, true);
        return $this->mergeFileList($mergedFiles, $fileList);
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
        $resultFileList = $a;
        if (!is_array($b)) {
            return $b;
        }
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
     * @param File $file
     *
     * @return boolean True on success, false otherwise
     */
    public function isDirectory(File $file)
    {
        return is_dir($this->getFullPath($file));
    }

    /**
     * Check whether file is directory
     *
     * @param File $file
     *
     * @return boolean True on success, false otherwise
     */
    public function isFile(File $file)
    {
        return is_file($this->getFullPath($file));
    }

    /**
     * Check whether file exists in the filesytem
     *
     * @param File $file
     *
     * @return boolean True when exists, false otherwise
     */
    public function fileExists(File $file)
    {
        return file_exists($this->getFullPath($file));
    }

    public function readFile(
        File $file
    ) {
        return file_get_contents($this->getFullPath($file));
    }

    /**
     * @param File $file
     *
     * @return string
     */
    public function getFullPath(File $file)
    {
        if ($file->getApplicationTemplateFile()) {
            if (file_exists($this->cx->getWebsiteDocumentRootPath() . $file->__toString())) {
                $basePath = $this->cx->getWebsiteDocumentRootPath();
            } else {
                $basePath = $this->cx->getCodeBaseDocumentRootPath();
            }
        } elseif (file_exists($this->getRootPath() . '/' . $file->__toString())) {
            $basePath = $this->getRootPath();
        } elseif ($this->codeBaseFileSystem && file_exists($this->codeBaseFileSystem->getRootPath() . '/' . $file->__toString())) {
            $basePath = $this->codeBaseFileSystem->getRootPath();
        }
        return $basePath . '/' . $file->__toString();
    }

    /**
     * Check whether the file is read only
     * 
     * @param File $file
     *
     * @return boolean
     */
    public function isReadOnly(File $file)
    {
        if ($file->getApplicationTemplateFile()) {
            if (file_exists($this->cx->getWebsiteDocumentRootPath() . $file->__toString())) {
                return false;
            }
            return true;
        } elseif ($this->fileExists($file)) {
            return false;
        } elseif ($this->codeBaseFileSystem && file_exists($this->codeBaseFileSystem->getRootPath() . '/' . $file->__toString())) {
            return true;
        }
    }

    /**
     * Check whether the file is resettable
     *
     * @param File $file
     *
     * @return boolean
     */
    public function isResettable(File $file)
    {
        if (   $this->cx->getWebsiteThemesPath() != $this->cx->getCodeBaseThemesPath()
            && $this->codeBaseFileSystem
            && $this->fileExists($file)
            && file_exists($this->codeBaseFileSystem->getRootPath() . '/' . $file->__toString())
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check whether the file is image
     *
     * @param File $file
     *
     * @return boolean
     */
    public function isImageFile(File $file)
    {
        return $this->isImage($file->getExtension());
    }
}
