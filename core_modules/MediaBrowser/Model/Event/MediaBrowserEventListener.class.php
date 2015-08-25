<?php
/**
 * @copyright   Comvation AG
 * @author Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core_Modules\MediaBrowser\Model\Event;

use Cx\Core\MediaSource\Model\Entity\FileSystem;
use Cx\Core\MediaSource\Model\Entity\MediaSourceManager;
use Cx\Core\MediaSource\Model\Entity\MediaSource;
use Cx\Core\Event\Model\Entity\DefaultEventListener;
use Cx\Modules\Shop\Controller\Products;
use Cx\Modules\Shop\Controller\ShopCategories;

/**
 * Class MediaBrowserEventListener
 *
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 */
class MediaBrowserEventListener extends DefaultEventListener
{
    /**
     * @param MediaSourceManager $mediaBrowserConfiguration
     */
    public function mediasourceLoad(
        MediaSourceManager $mediaBrowserConfiguration
    ) {
        global $_ARRAYLANG;
        \Env::get('init')->loadLanguageData('MediaBrowser');
        $mediaType = new MediaSource('files',$_ARRAYLANG['TXT_FILEBROWSER_FILES'],   array(
            $this->cx->getWebsiteImagesContentPath(),
            $this->cx->getWebsiteImagesContentWebPath(),
        ),array(), 1);
        $mediaType2 = new MediaSource('base64ftp','base64.robinio.ch',   array(
            $this->cx->getWebsiteImagesContentPath(),
            $this->cx->getWebsiteImagesContentWebPath(),
        ),array(), 2, new FTPFileSystem());
            $mediaType3 = new MediaSource('shopProducts','Shop Produkte',   array(
            $this->cx->getWebsiteImagesContentPath(),
            $this->cx->getWebsiteImagesContentWebPath(),
        ),array(), 2, new ShopArticleVirtualFileSystem());
        $mediaType4 = new MediaSource('virtual','Placeholders',   array(
            $this->cx->getWebsiteImagesContentPath(),
            $this->cx->getWebsiteImagesContentWebPath(),
        ),array(), 2, new VirtualFileSystem());
        $mediaBrowserConfiguration->addMediaType($mediaType);
        $mediaBrowserConfiguration->addMediaType($mediaType2);
        $mediaBrowserConfiguration->addMediaType($mediaType3);
        $mediaBrowserConfiguration->addMediaType($mediaType4);
    }
}

class FTPFileSystem implements FileSystem {


    public function getFileList($directory, $recursive = false) {
        $conn_id = ftp_connect("eggworks.ch");
        ftp_login ($conn_id, "testing@eggworks.ch", "Rt1GUe0WKNWR");
        $files = ( ftp_nlist($conn_id, "."));
        $filesArray = [];
        foreach ($files as $file) {
            if ($file == '..' || $file == '.' || (0 === strpos($file, '.'))){
                continue;
            }
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            $filesArray[$file] = [
                'datainfo' => array(
                    'filepath' => 'http://base64.robinio.ch/'.$file,
                    'name' => $file,
                    'size' => '100tb',
                    'cleansize' => 100,
                    'extension' =>  $extension ? ucfirst($extension) : 'Dir',
                    'preview' => 'none',
                    'hasPreview' => true,
                    'active' => false,
                    'type' => '',
                    'thumbnail' => array()
                )
            ];
            $subFiles = ftp_nlist($conn_id, $file);
            foreach ($subFiles as $subFile){
                if ($subFile == '..' || $subFile == '.'){
                    continue;
                }
                $extension = pathinfo($subFile, PATHINFO_EXTENSION);
                $filesArray[$file][$subFile] = [
                    'datainfo' => array(
                        'filepath' => 'http://base64.robinio.ch/'.$file.'/'.$subFile,
                        'name' => $subFile,
                        'size' => '1mb',
                        'cleansize' => 100,
                        'extension' =>  $extension ? ucfirst($extension) : 'Dir',
                        'preview' => in_array( ucfirst($extension), array('Png', 'Jpg')) ? 'http://base64.robinio.ch/'.$file.'/'.$subFile : 'none',
                        'hasPreview' => true,
                        'active' => false,
                        'type' => '',
                        'thumbnail' => array()
                    )
                ];
            }

        }
        return $filesArray;
    }

    public function removeFile(\Cx\Core\MediaSource\Model\Entity\File $file) {
        // TODO: Implement removeFile() method.
    }

    public function moveFile(
        \Cx\Core\MediaSource\Model\Entity\File $file, $destination
    ) {
        // TODO: Implement moveFile() method.
    }

    public function writeFile(
        \Cx\Core\MediaSource\Model\Entity\File $file, $content
    ) {
        // TODO: Implement writeFile() method.
    }

    public function readFile(\Cx\Core\MediaSource\Model\Entity\File $file) {
        // TODO: Implement readFile() method.
    }

    public function isDirectory(\Cx\Core\MediaSource\Model\Entity\File $file) {
        // TODO: Implement isDirectory() method.
    }

    public function isFile(\Cx\Core\MediaSource\Model\Entity\File $file) {
        // TODO: Implement isFile() method.
    }

    public function getLink(\Cx\Core\MediaSource\Model\Entity\File $file) {
        // TODO: Implement getLink() method.
    }

    public function createDirectory($path, $directory) {
        // TODO: Implement createDirectory() method.
    }
}

class VirtualFileSystem implements FileSystem {

    public function getFileList($directory, $recursive = false) {
        $filesArray = [];
        $themes     = [
            'abstract', 'animals', 'business', 'cats', 'city', 'food',
            'nightlife', 'fashion', 'people', 'nature', 'sports', 'technics',
            'transport', 'technics',
        ];
        foreach ($themes as $theme) {
            $filesArray[$theme] = [
                'datainfo' => array(
                    'filepath' => $theme,
                    'name' => $theme,
                    'size' => '100tb',
                    'cleansize' => 100,
                    'extension' => 'Dir',
                    'preview' => 'none',
                    'hasPreview' => true,
                    'active' => false,
                    'type' => '',
                    'thumbnail' => array()
                )
            ];
            for ($i = 1; $i < 4;$i++){
                $filesArray[$theme]["400_200_".$i.".png"] = array(
                    'datainfo' => array(
                        'filepath' => 'http://lorempixel.com/400/200/'.$theme.'/'.$i.'',
                        // preselect in mediabrowser or mark a folder
                        'name' => '400_200_'.$i.'.png',
                        'size' => '100tb',
                        'cleansize' => 100,
                        'extension' => 'Png',
                        'preview' => 'http://lorempixel.com/400/200/'.$theme.'/'.$i.'',
                        'hasPreview' => true,
                        'active' => false,
                        'type' => '',
                        'thumbnail' => array()
                    )
                );
            }
        }
        return $filesArray;
    }

    public function removeFile(\Cx\Core\MediaSource\Model\Entity\File $file) {
        // TODO: Implement removeFile() method.
    }

    public function moveFile(
        \Cx\Core\MediaSource\Model\Entity\File $file, $destination
    ) {
        // TODO: Implement moveFile() method.
    }

    public function writeFile(
        \Cx\Core\MediaSource\Model\Entity\File $file, $content
    ) {
        // TODO: Implement writeFile() method.
    }

    public function readFile(\Cx\Core\MediaSource\Model\Entity\File $file) {
        // TODO: Implement readFile() method.
    }

    public function isDirectory(\Cx\Core\MediaSource\Model\Entity\File $file) {
        // TODO: Implement isDirectory() method.
    }

    public function isFile(\Cx\Core\MediaSource\Model\Entity\File $file) {
        // TODO: Implement isFile() method.
    }

    public function getLink(\Cx\Core\MediaSource\Model\Entity\File $file) {
        // TODO: Implement getLink() method.
    }

    public function createDirectory($path, $directory) {
        // TODO: Implement createDirectory() method.
    }
}


class ShopArticleVirtualFileSystem implements FileSystem {

    public function getFileList($directory, $recursive = false) {
        $categories = $arrShopCategories =
            ShopCategories::getTreeArray(true, false, false);
        $filesArray = [];
        foreach ($categories as $theme) {
            $filesArray[$theme['name']] = [
                'datainfo' => array(
                    'filepath' => $theme['name'],
                    'name' => $theme['name'],
                    'size' => '100tb',
                    'cleansize' => 100,
                    'extension' => 'Dir',
                    'preview' => 'none',
                    'hasPreview' => true,
                    'active' => false,
                    'type' => '',
                    'thumbnail' => array()
                )
            ];
            $count = 10;
            $products = Products::getByShopParams(
                $count, 0,
                null, $theme['id']
            );
            foreach ($products as $product){
                $filesArray[$theme['name']][$product->name()] = [
                    'datainfo' => array(
                        'filepath' => '[[NODE_SHOP_DETAILS]]&productId='.$product->id(),
                        // preselect in mediabrowser or mark a folder
                        'name' => $product->name(),
                        'size' => '',
                        'cleansize' => 100,
                        'extension' => 'Shop',
                        'preview' => \Cx\Core\Core\Controller\Cx::instanciate()->getWebsiteImagesShopWebPath().'/'.Products::get_image_array_from_base64($product->pictures())[1]['img'],
                        'hasPreview' => true,
                        'active' => false,
                        'type' => '',
                        'thumbnail' => array()
                    )
                ];
            }
        }
        return $filesArray;
    }

    public function removeFile(\Cx\Core\MediaSource\Model\Entity\File $file) {
        // TODO: Implement removeFile() method.
    }

    public function moveFile(
        \Cx\Core\MediaSource\Model\Entity\File $file, $destination
    ) {
        // TODO: Implement moveFile() method.
    }

    public function writeFile(
        \Cx\Core\MediaSource\Model\Entity\File $file, $content
    ) {
        // TODO: Implement writeFile() method.
    }

    public function readFile(\Cx\Core\MediaSource\Model\Entity\File $file) {
        // TODO: Implement readFile() method.
    }

    public function isDirectory(\Cx\Core\MediaSource\Model\Entity\File $file) {
        // TODO: Implement isDirectory() method.
    }

    public function isFile(\Cx\Core\MediaSource\Model\Entity\File $file) {
        // TODO: Implement isFile() method.
    }

    public function getLink(\Cx\Core\MediaSource\Model\Entity\File $file) {
        // TODO: Implement getLink() method.
    }

    public function createDirectory($path, $directory) {
        // TODO: Implement createDirectory() method.
    }
}