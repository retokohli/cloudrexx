<?php

/**
 * JSON Adapter for Uploader
 *
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage  core_json
 */

namespace Cx\Core_Modules\MediaBrowser\Controller;

use Cx\Core\ContentManager\Model\Entity\Page;
use Cx\Core\Core\Controller\Cx;
use Cx\Core\Core\Model\Entity\SystemComponent;
use Cx\Core\Core\Model\Entity\SystemComponentController;
use Cx\Core\Json\JsonAdapter;
use Cx\Core\Json\JsonData;
use Cx\Core\Routing\NodePlaceholder;
use Cx\Core_Modules\MediaBrowser\Model\Entity\ThumbnailGenerator;

/**
 * JSON Adapter for Uploader
 *
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 */
class JsonMediaBrowser extends SystemComponentController implements JsonAdapter
{

    /**
     * Cx instance
     *
     * @var Cx
     */
    protected $cx;

    protected $message;

    /**
     * Instantiates the object
     *
     * @param SystemComponent $systemComponent
     * @param Cx              $cx
     */
    function __construct(SystemComponent $systemComponent, Cx $cx) {
        $this->cx = $cx;
    }

    /**
     * Returns the internal name used as identifier for this adapter
     *
     * @return String Name of this adapter
     */
    public function getName() {
        return 'MediaBrowser';
    }

    /**
     * Returns an array of method names accessable from a JSON request
     *
     * @return array List of method names
     */
    public function getAccessableMethods() {
        return array(
            'getFiles', 'getSites', 'getSources', 'createThumbnails',
            'createDir', 'renameFile', 'removeFile', 
            'removeFileFromFolderWidget'=> new \Cx\Core_Modules\Access\Model\Entity\Permission(null, null, false),
            'folderWidget' => new \Cx\Core_Modules\Access\Model\Entity\Permission(null, null, false)
        );
    }

    /**
     * Returns all messages as string
     *
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString() {
        return $this->message;
    }

    /**
     * Get all media sources
     *
     * @return array
     */
    public function getSources() {
        $mediaSourceManager = $this->cx->getMediaSourceManager();
        $sources            = array();
        foreach (
            $mediaSourceManager->getMediaTypes() as $type =>
            $mediaSource
        ) {
            $sources[] = array(
                'name' => $mediaSource->getHumanName(),
                'value' => $type,
                'path' => array_values(
                    array_filter(
                        explode(
                            '/',
                            $mediaSourceManager->getMediaTypePathsbyNameAndOffset(
                                $type, 1
                            )
                        )
                    )
                )
            );
        }
        return $sources;
    }

    /**
     * Get filelist with all files
     *
     * @param $params
     *
     * @return array
     */
    public function getFiles($params) {
        $filePath  = (strlen($params['get']['path']) > 0)
            ? $params['get']['path'] : '/';
        $mediaType = (strlen($params['get']['mediatype']) > 0)
            ? $params['get']['mediatype'] : 'files';

        $mediaTypes = $this->cx->getMediaSourceManager()->getMediaTypes();
        return $mediaTypes[$mediaType]->getFileSystem()->getFileList($filePath);
    }

    /**
     * Get all content pages.
     *
     * @return array
     */
    public function getSites() {
        $jd                   = new JsonData();
        $data                 = $jd->data(
            'node', 'getTree', array('get' => array('recursive' => 'true'))
        );
        $pageStack            = array();
        $data['data']['tree'] = array_reverse($data['data']['tree']);
        foreach ($data['data']['tree'] as &$entry) {
            $entry['attr']['level'] = 0;
            array_push($pageStack, $entry);
        }
        $return = array();
        while (count($pageStack)) {
            $entry              = array_pop($pageStack);
            $page               = $entry['data'][0];
            $arrPage['level']   = $entry['attr']['level'];
            $arrPage['node_id'] = $entry['attr']['rel_id'];
            $children           = $entry['children'];
            $children           = array_reverse($children);
            foreach ($children as &$entry) {
                $entry['attr']['level'] = $arrPage['level'] + 1;
                array_push($pageStack, $entry);
            }
            $arrPage['catname']   = $page['title'];
            $arrPage['catid']     = $page['attr']['id'];
            $arrPage['lang']      = BACKEND_LANG_ID;
            $arrPage['protected'] = $page['attr']['protected'];
            $arrPage['type']      = Page::TYPE_CONTENT;
            $arrPage['alias']     = $page['title'];
            $arrPage['frontend_access_id']
                                          = $page['attr']['frontend_access_id'];
            $arrPage['backend_access_id'] = $page['attr']['backend_access_id'];
            $jsondata                     = json_decode(
                $page['attr']['data-href']
            );
            $path                         = $jsondata->path;
            if (trim($jsondata->module) != '') {
                $arrPage['type']       = Page::TYPE_APPLICATION;
                $module                = explode(' ', $jsondata->module, 2);
                $arrPage['modulename'] = $module[0];
                if (count($module) > 1) {
                    $arrPage['cmd'] = $module[1];
                }
            }

            $url = '[[' . NodePlaceholder::PLACEHOLDER_PREFIX;

// TODO: This only works for regular application pages. Pages of type fallback that are linked to an application
//       will be parsed using their node-id ({NODE_<ID>})
            if (($arrPage['type'] == Page::TYPE_APPLICATION)
//                && ($this->_mediaType !== 'alias')
            ) {
                $url .= $arrPage['modulename'];
                if (!empty($arrPage['cmd'])) {
                    $url .= '_' . $arrPage['cmd'];
                }

                $url = strtoupper($url);
            } else {
                $url .= $arrPage['node_id'];
            }


            $url .= "]]";

            $return[] = array(
                'click' =>
                    "javascript:{setUrl('$url',null,null,'"
                    . \FWLanguage::getLanguageCodeById(
                        BACKEND_LANG_ID
                    )
                    . $path . "','page')}",
                'name' => $arrPage['catname'],
                'extension' => 'Html',
                'level' => $arrPage['level'],
                'url' => $path,
                'node' => $url
            );
        }
        return $return;
    }


    /**
     * Create Thumbnails for file
     *
     * @param $params array
     *
     * @return bool
     */
    public function createThumbnails($params) {
        if (isset($params['get']['file'])) {
            ThumbnailGenerator::createThumbnailFromPath($params['get']['file']);
            return true;
        }
        return false;
    }

    /**
     * @param $params
     */
    public function createDir($params) {
        $mediaBrowserConfiguration = $this->cx->getMediaSourceManager();
        $pathArray                 = explode('/', $params['get']['path']);
        // Shift off the first element of the array to get the media type.
        $mediaType = array_shift($pathArray);
        $strPath   = $mediaBrowserConfiguration->getMediaTypePathsbyNameAndOffset(
            $mediaType, 0
        );
        $strPath .= '/' . join('/', $pathArray);
        $dir        = $params['post']['dir'] . '/';
        $this->setMessage(
            $this->cx->getMediaSourceManager()->getMediaType($mediaType)->getFileSystem()->createDirectory(
                $strPath, $dir
            )
        );
    }

    /**
     * @param $params
     */
    public function renameFile($params) {
        \Env::get('init')->loadLanguageData('MediaBrowser');
        
        $path       = !empty($params['get']['path']) ? contrexx_input2raw($params['get']['path']) : null;
        $oldName    = !empty($params['post']['oldName']) ? contrexx_input2raw($params['post']['oldName']) : null;
        $newName    = !empty($params['post']['newName']) ? contrexx_input2raw($params['post']['newName']) : null;
        
        if ($path && $oldName && $newName) {
            $pathArray = explode('/', $path);
            // Shift off the first element of the array to get the media type.
            $mediaType  = array_shift($pathArray);
            $strPath    = '/' . join('/', $pathArray);
            $this->setMessage(
                $this->cx->getMediaSourceManager()->getMediaType($mediaType)->getFileSystem()->moveFile(
                    new \Cx\Core\MediaSource\Model\Entity\LocalFile(
                        $strPath . $oldName
                    ), $newName
                )
            );
        }
    }

    /**
     * @param $params
     */
    public function removeFile($params) {        
        \Env::get('init')->loadLanguageData('MediaBrowser');
        $path     = !empty($params['get']['path']) ? contrexx_input2raw($params['get']['path']) : null;
        $filename = !empty($params['post']['file']['datainfo']['name']) ? contrexx_input2raw($params['post']['file']['datainfo']['name']) : null;
        if ($filename && $path) {
            $pathArray = explode('/', $path);
            // Shift off the first element of the array to get the media type.
            $mediaType  = array_shift($pathArray);
            $strPath    = '/' . join('/', $pathArray);
            $this->setMessage(
                $this->cx->getMediaSourceManager()->getMediaType($mediaType)->getFileSystem()->removeFile(
                    new \Cx\Core\MediaSource\Model\Entity\LocalFile(
                        $strPath . $filename
                    )
                )
            );
        }
    }


    /**
     * Returns default permission as object
     *
     * @return Object
     */
    public function getDefaultPermissions() {
        // TODO: Implement getDefaultPermissions() method.
    }


    /**
     * Folder widget
     *
     * @param array $params
     *
     * @return boolean|array
     */
    public function folderWidget($params) {
        \cmsSession::getInstance();
        
        $folderWidgetId = isset($params['get']['id']) ? contrexx_input2int($params['get']['id']) : 0;
        if (   empty($folderWidgetId)
            || empty($_SESSION['MediaBrowser']['FolderWidget'][$folderWidgetId])
        ) {
            return false;
        }

        $folder = $_SESSION['MediaBrowser']['FolderWidget'][$folderWidgetId]['folder'];

        $arrFileNames = array();
        if (!file_exists($folder)) {
            return false;
        }
        $h = opendir($folder);
        while (false !== ($f = readdir($h))) {
            // skip folders and thumbnails
            if ($f == '.' || $f == '..'
                || preg_match(
                    "/(?:\.(?:thumb_thumbnail|thumb_medium|thumb_large)\.[^.]+$)|(?:\.thumb)$/i",
                    $f
                )
            ) {
                continue;
            }
            if (!is_dir($folder . '/' . $f)) {
                array_push($arrFileNames, $f);
            }
        }
        closedir($h);

        return $arrFileNames;
    }

    /**
     * Remove the file from folder widget
     * 
     * @param array $params array from json request
     */
    public function removeFileFromFolderWidget($params)
    {
        \cmsSession::getInstance();

        $folderWidgetId = isset($params['get']['widget']) ? contrexx_input2int($params['get']['widget']) : 0;
        if (   empty($folderWidgetId)
            || empty($_SESSION['MediaBrowser']['FolderWidget'][$folderWidgetId])
            || $_SESSION['MediaBrowser']['FolderWidget'][$folderWidgetId]['mode'] == \Cx\Core_Modules\MediaBrowser\Model\Entity\FolderWidget::MODE_VIEW_ONLY
        ) {
            return false;
        }
        
        $path = !empty($params['get']['file']) ? contrexx_input2raw($params['get']['file']) : null;
        if (empty($path)) {
            return false;
        }
        $folder          = $_SESSION['MediaBrowser']['FolderWidget'][$folderWidgetId]['folder'];
        $localFileSystem = new \Cx\Core\MediaSource\Model\Entity\LocalFileSystem($folder);
        
        $file    = '/' . $path;
        $objFile = new \Cx\Core\MediaSource\Model\Entity\LocalFile($file);
        
        $this->setMessage($localFileSystem->removeFile($objFile));
        
        return array();
    }
    
    /**
     * @param mixed $message
     */
    public function setMessage($message) {
        $this->message = $message;
    }

}
