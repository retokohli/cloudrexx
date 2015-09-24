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
 * Class ThumbnailGenerator
 *
 * @copyright   Cloudrexx AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     cloudrexx
 * @subpackage  coremodule_mediabrowser
 */

namespace Cx\Core\MediaSource\Model\Entity;

use Cx\Core\Core\Controller\Cx;
use Cx\Model\Base\EntityBase;

/**
 * Class ThumbnailGenerator
 *
 * @copyright   Cloudrexx AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 */
class ThumbnailGenerator extends EntityBase
{
    /**
     * List with thumbnails
     * @var array
     */
    protected $thumbnails;

    /**
     * @var Cx
     */
    protected $cx;

    /**
     * @var MediaSourceManager
     */
    protected $mediaSourceManager;

    /**
     * ThumbnailGenerator constructor.
     *
     * @param $cx Cx
     * @param $mediaSourceManager MediaSourceManager
     */
    public function __construct($cx,$mediaSourceManager) {
        $this->cx = $cx;
        $this->mediaSourceManager = $mediaSourceManager;
    }

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
     * @param bool          $generateThumbnailByRatio
     * @param bool          $force Force creation of new Thumbnails. This overwrites any existing thumbnail.
     *
     * @return array Array with the relative paths to the thumbnails.
     */
    public function createThumbnail(
        $path, $fileNamePlain, $fileExtension, \ImageManager $imageManager,
        $generateThumbnailByRatio = false, $force = false
    ) {
        $thumbnails = array();
        foreach (
            $this->getThumbnails() as $thumbnail
        ) {
            if ($force) {
                \Cx\Lib\FileSystem\FileSystem::delete_file(
                    MediaSourceManager::getAbsolutePath($path) . '/'
                    . $fileNamePlain . $thumbnail['value'] . '.'
                    . $fileExtension
                );
            } elseif (\Cx\Lib\FileSystem\FileSystem::exists(
                MediaSourceManager::getAbsolutePath($path) . '/'
                . $fileNamePlain . $thumbnail['value'] . '.' . $fileExtension
            )
            ) {
                $thumbnails[] = $fileNamePlain . $thumbnail['value'] . '.'
                    . $fileExtension;
                continue;
            }
            if ($imageManager->_createThumb(
                MediaSourceManager::getAbsolutePath($path) . '/',
                '',
                $fileNamePlain . '.' . $fileExtension,
                $thumbnail['size'],
                $thumbnail['quality'],
                $fileNamePlain . $thumbnail['value'] . '.' . $fileExtension,
                $generateThumbnailByRatio
            )
            ) {
                $thumbnails[] = $fileNamePlain . $thumbnail['value'] . '.'
                    . $fileExtension;
                continue;
            }
        }
        return $thumbnails;
    }

    /**
     * @param            $filePath
     * @param bool|false $force
     *
     * @return array
     */
    public function createThumbnailFromPath($filePath, $force = false) {
        if (!file_exists($filePath)) {
            $filePath = $this->cx->getWebsitePath() . $filePath;
        }
        $fileInfo = pathinfo($filePath);
        return $this->createThumbnail(
            $fileInfo['dirname'],
            preg_replace('/\.thumb_[a-z]+/i', '', $fileInfo['filename']),
            $fileInfo['extension'], new \ImageManager(), false, $force
        );
    }

    /**
     * Loads thumbnails from database
     */
    protected function loadThumbnails() {
        $pdo              = \Cx\Core\Core\Controller\Cx::instanciate()->getDb()
            ->getPdoConnection();
        $sth              = $pdo->query(
            'SELECT id, name, size, 100 as quality,
             CONCAT(".thumb_",name) as value FROM  `'
            . DBPREFIX
            . 'settings_thumbnail`'
        );
        $this->thumbnails = $sth->fetchAll();
    }

    /**
     * Get Thumbnails from database
     * @return array
     */
    public function getThumbnails() {
        if (!$this->thumbnails) {
            $this->loadThumbnails();
        }
        return $this->thumbnails;
    }


} 
