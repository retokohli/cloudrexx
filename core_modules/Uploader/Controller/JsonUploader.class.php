<?php

/**
 * JSON Adapter for Uploader
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage  core_json
 */

namespace Cx\Core_Modules\Uploader\Controller;

use \Cx\Core\Json\JsonAdapter;

/**
 * JSON Adapter for Uploader
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage  core_json
 */
class JsonUploader implements JsonAdapter {

    /**
     * Returns the internal name used as identifier for this adapter
     * @return String Name of this adapter
     */
    public function getName() {
        return 'Uploader';
    }

    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods() {
        return array('upload', 'createDir');
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString() {
        return '';
    }

    public function upload($params) {        
        $path_part = explode("/", $params['post']['path'], 2);
        $mediaBrowserConfiguration = \Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration::get();
        $path = $mediaBrowserConfiguration->mediaTypePaths[$path_part[0]][1] .'/'. $path_part[1];
        
        $uploader = UploaderController::handleRequest(array(
                    'allow_extensions' => 'jpg,jpeg,png',
                    'target_dir' => str_replace(ASCMS_INSTANCE_OFFSET, '', $path) // without offset
        ));
        
        if (!$uploader) {
            $ret = array(
                'OK' => 0,
                'error' => array(
                    'code' => UploaderController::getErrorCode(),
                    'message' => UploaderController::getErrorMessage()
                )
            );
        } else {
            $ret = array('OK' => 1);
        }
        return $ret;
    }

    // testcase: ?cmd=jsondata&object=Uploader&act=createDir&path=/trunk/media/archive1&dir=test5
    public function createDir($params) {
        global $_ARRAYLANG;
        $mediaBrowserConfiguration = \Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration::get();
        $mediaBrowserConfiguration->mediaTypes;

        $strPath = ASCMS_INSTANCE_PATH .'/'. $params['get']['path'];
        $strWebPath = '/'.$params['get']['path'];
        $dir = $params['get']['dir'];
        
        var_dump($strWebPath);


        if (preg_match('#^[0-9a-zA-Z_\-]+$#', $dir)) {
            $objFile = new \File();
            if (!$objFile->mkDir($strPath, $strWebPath, $dir)) {
                return $_ARRAYLANG['TXT_FILEBROWSER_UNABLE_TO_CREATE_FOLDER'];
            } else {
                return $_ARRAYLANG['TXT_FILEBROWSER_DIRECTORY_SUCCESSFULLY_CREATED'];
            }
        } else if (!empty($dir)) {
            // error: TXT_FILEBROWSER_INVALID_CHARACTERS
            return $_ARRAYLANG['TXT_FILEBROWSER_INVALID_CHARACTERS'];
        }
    }

}
