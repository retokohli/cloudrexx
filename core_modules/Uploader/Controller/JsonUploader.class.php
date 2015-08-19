<?php

/**
 * JSON Adapter for Uploader
 *
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage  core_json
 */

namespace Cx\Core_Modules\Uploader\Controller;

use Cx\Core\Core\Model\Entity\SystemComponentController;
use \Cx\Core\Json\JsonAdapter;
use Cx\Core\Model\RecursiveArrayAccess;
use Cx\Lib\FileSystem\FileSystem;

/**
 * JSON Adapter for Uploader
 *
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 */
class JsonUploader extends SystemComponentController implements JsonAdapter
{

    /**
     * Message which gets displayed.
     *
     * @var string
     */
    protected $message = '';


    /**
     * Returns the internal name used as identifier for this adapter
     *
     * @return String Name of this adapter
     */
    public function getName()
    {
        return 'Uploader';
    }

    /**
     * Returns an array of method names accessable from a JSON request
     *
     * @return array List of method names
     */
    public function getAccessableMethods()
    {
        return array('upload' => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('https','http'), array('post'), false));
    }

    /**
     * Returns all messages as string
     *
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString()
    {
        return $this->message;
    }

    /**
     * Upload handler.
     *
     * @param $params
     *
     * @return array
     * @throws UploaderException
     */
    public function upload($params)
    {
        global $_ARRAYLANG;
        $id = null;
        if (isset($params['get']['id']) && preg_match('/^[a-z0-9]+$/i', $params['get']['id'])
        ) {
            $id = ($params['get']['id']);
            $uploadedFileCount = isset($params['get']['uploadedFileCount']) ? intval($params['get']['uploadedFileCount']) : 0;
            $path = $_SESSION->getTempPath() . '/'.$id.'/';
            $tmpPath = $path;
        } elseif (isset($params['post']['path'])) {
            $path_part = explode("/", $params['post']['path'], 2);
            $mediaSourceManager
                = $this->cx->getMediaSourceManager();
            $path = $mediaSourceManager->getMediaTypePathsbyNameAndOffset($path_part[0],0)
                . '/' . $path_part[1];

            $tmpPath = $_SESSION->getTempPath();
        } else {
            return array(
                'OK' => 0,
                'error' => array(
                    'message' => 'No id specified'
                )
            );
        }
        $allowedExtensions = false;
        if (isset($_SESSION['uploader']['handlers'][$id]['config']['allowed-extensions'])) {
            $allowedExtensions = $_SESSION['uploader']['handlers'][$id]['config']['allowed-extensions'];
        }
        $uploader = UploaderController::handleRequest(
            array(
                'allow_extensions' => is_array($allowedExtensions) ? explode(', ', $allowedExtensions) : $allowedExtensions,
                'target_dir' => $path,
                'tmp_dir' => $tmpPath
            )
        );

        $fileLocation = array(
            $uploader['path'],
            str_replace($this->cx->getWebsitePath(), '', $uploader['path'])
        );


        $response = new UploadResponse();
        if (isset($_SESSION['uploader']['handlers'][$id]['callback'])) {

            /**
             * @var $callback RecursiveArrayAccess
             * @var $data RecursiveArrayAccess
             */
            $callback = $_SESSION['uploader']['handlers'][$id]['callback'];
            $data = $_SESSION['uploader']['handlers'][$id]['data'];

            if (   isset($_SESSION['uploader']['handlers'][$id]['config']['upload-limit'])
                && $_SESSION['uploader']['handlers'][$id]['config']['upload-limit'] <= $uploadedFileCount
                ) {
                return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_CORE_MODULE_UPLOADER_MAX_LIMIT_REACHED']);
            }

            if (!is_string($callback)) {
                $callback = $callback->toArray();
            }

            if ($data){
                $data = $data->toArray();
            }

            $filePath = dirname( $uploader['path']);
            if (!is_array($callback)) {
                $class = new \ReflectionClass($callback);
                if ($class->implementsInterface(
                    '\Cx\Core_Modules\Uploader\Model\UploadCallbackInterface'
                )
                ) {
                    /**
                     * @var \Cx\Core_Modules\Uploader\Model\UploadCallbackInterface $callbackInstance
                     */
                    $callbackInstance = $class->newInstance($this->cx);
                    $fileLocation = $callbackInstance->uploadFinished(
                        $filePath, str_replace(
                            $this->cx->getWebsiteTempPath(), $this->cx->getWebsiteTempWebPath(),
                            $filePath
                        ), $data,
                        $id,
                        $uploader,
                        $response
                    );
                }
            } else {
                $fileLocation = call_user_func(
                    array($callback[1], $callback[2]), $filePath,
                    str_replace(
                        $this->cx->getWebsiteTempPath(), $this->cx->getWebsiteTempWebPath(), $filePath
                    ), $data, $id, $uploader, $response
                );
            }
            \Cx\Lib\FileSystem\FileSystem::move(
                $uploader['path'], $fileLocation[0] . '/' . $uploader['name'],
                true
            );
            \Cx\Lib\FileSystem\FileSystem::delete_file($uploader['path']);

            if (isset($fileLocation[2])){
                $uploader['name'] = $fileLocation[2];
            }
            $fileLocation = array(
                $fileLocation[0] . '/' . $uploader['name'],
                $fileLocation[1] . '/' . $uploader['name']
            );
        }

        if ($response->getWorstStatus()) {
                $result = $response->getResponse();
                return array(
                    'OK' => 0,
                    'file' => $fileLocation,
                    'response' => $result['messages']
                );
        }
        if (isset($uploader['error'])) {
            throw new UploaderException(UploaderController::getErrorCode());
        } else {
            return array(
                'OK' => 1,
                'file' => $fileLocation
            );
        }
    }


    /**
     * Returns default permission as object
     *
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
