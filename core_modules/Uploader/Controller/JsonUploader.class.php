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
use Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration;
use Cx\Lib\FileSystem\FileSystem;

/**
 * JSON Adapter for Uploader
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage  core_json
 */
class JsonUploader implements JsonAdapter
{
    protected $message = '';

    /**
     * Returns the internal name used as identifier for this adapter
     * @return String Name of this adapter
     */
    public function getName()
    {
        return 'Uploader';
    }

    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods()
    {
        return array('upload', 'createDir', 'renameFile', 'removeFile');
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString()
    {
        return $this->message;
    }

    public function upload($params)
    {
        $path_part                 = explode("/", $params['post']['path'], 2);
        $mediaBrowserConfiguration = \Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration::getInstance();
        $path                      = $mediaBrowserConfiguration->mediaTypePaths[$path_part[0]][1] . '/' . $path_part[1];

        $uploader = UploaderController::handleRequest(array(
            'allow_extensions' => 'jpg,jpeg,png',
            'target_dir'       => str_replace(ASCMS_INSTANCE_OFFSET, '', $path) // without offset
        ));

        if (!$uploader) {
            $ret = array(
                'OK'    => 0,
                'error' => array(
                    'code'    => UploaderController::getErrorCode(),
                    'message' => UploaderController::getErrorMessage()
                )
            );
        } else {
            $ret = array('OK' => 1);
        }
        return $ret;
    }

    // testcase: ?cmd=jsondata&object=Uploader&act=createDir&path=/trunk/media/archive1&dir=test5
    public function createDir($params)
    {
        global $_ARRAYLANG;

        \Env::get('init')->loadLanguageData('MediaBrowser');
        $mediaBrowserConfiguration = \Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration::getInstance();
        $mediaBrowserConfiguration->mediaTypes;
        $pathArray = explode('/', $params['get']['path']);
        $strPath = MediaBrowserConfiguration::getInstance()->mediaTypePaths[array_shift($pathArray)][0];


        $strPath.= '/'.join('/', $pathArray);
        $dir     = $params['post']['dir'] . '/';

        if (preg_match('#^[0-9a-zA-Z_\-\/]+$#', $dir)) {
            if (!FileSystem::make_folder($strPath . '/' . $dir)) {
                $this->setMessage(sprintf($_ARRAYLANG['TXT_FILEBROWSER_UNABLE_TO_CREATE_FOLDER'], $dir));
                return;
            } else {
                $this->setMessage(sprintf($_ARRAYLANG['TXT_FILEBROWSER_DIRECTORY_SUCCESSFULLY_CREATED'], $dir));
                return;
            }
        } else if (!empty($dir)) {
            // error: TXT_FILEBROWSER_INVALID_CHARACTERS
            $this->setMessage($_ARRAYLANG['TXT_FILEBROWSER_INVALID_CHARACTERS']);
            return;
        }

        $this->setMessage(sprintf($_ARRAYLANG['TXT_FILEBROWSER_UNABLE_TO_CREATE_FOLDER'], $dir));
        return;


    }


    /**
     * @param $params
     */
    public function renameFile($params)
    {
        global $_ARRAYLANG;
        $fileArray = explode('.', $params['post']['oldName']);
        $fileExtension = '';
        if (count($fileArray ) != 1){
            $fileExtension =  end($fileArray);
        }
        \Env::get('init')->loadLanguageData('MediaBrowser');
        $strPath = MediaBrowserConfiguration::getInstance()->mediaTypePaths[rtrim($params['get']['path'], "/")][0];

        $fileDot = '.';
        if (is_dir($strPath . '/' . $params['post']['oldName'])){
            $fileDot = '';
        }

        if (!FileSystem::move($strPath . '/' . $params['post']['oldName'], $strPath . '/' . $params['post']['newName'].$fileDot.$fileExtension, false)){
            $this->setMessage(sprintf($_ARRAYLANG['TXT_FILEBROWSER_FILE_UNSUCCESSFULY_RENAMED'], $params['post']['oldName']));
            return;
        }
        $this->setMessage(sprintf($_ARRAYLANG['TXT_FILEBROWSER_FILE_SUCCESSFULY_RENAMED'], $params['post']['oldName']));
    }

    /**
     * @param $params
     */
    public function removeFile($params)
    {
        global $_ARRAYLANG;

        \Env::get('init')->loadLanguageData('MediaBrowser');
        $strPath = MediaBrowserConfiguration::getInstance()->mediaTypePaths[rtrim($params['get']['path'], "/")][0];
        if (!empty($params['post']['file']['datainfo']['name']) && !empty($strPath) ){
            if (is_dir($strPath . '/' . $params['post']['file']['datainfo']['name'])){
                if (!FileSystem::delete_folder($strPath . '/' . $params['post']['file']['datainfo']['name'])){
                    $this->setMessage(sprintf($_ARRAYLANG['TXT_FILEBROWSER_DIRECTORY_UNSUCCESSFULY_REMOVED'], $params['post']['file']['datainfo']['name']));
                    return;
                }
                $this->setMessage(sprintf($_ARRAYLANG['TXT_FILEBROWSER_DIRECTORY_SUCCESSFULY_REMOVED'], $params['post']['file']['datainfo']['name']));
                return;
            }
            else {
                if (!FileSystem::delete_file($strPath . '/' . $params['post']['file']['datainfo']['name'])){
                    $this->setMessage(sprintf($_ARRAYLANG['TXT_FILEBROWSER_FILE_UNSUCCESSFULY_REMOVED'], $params['post']['file']['datainfo']['name']));
                    return;
                }
                $this->setMessage(sprintf($_ARRAYLANG['TXT_FILEBROWSER_FILE_SUCCESSFULY_REMOVED'], $params['post']['file']['datainfo']['name']));
            }
        }
    }


    /**
     * Returns default permission as object
     * @return Object
     */
    public function getDefaultPermissions()
    {
        // TODO: Implement getDefaultPermissions() method.
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }


}
