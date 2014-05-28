<?php

/**
 * Specific FrontendController for this Component. Use this to easily create a frontent view
 *
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage  modules_skeleton
 */

namespace Cx\Core_Modules\Uploader\Controller;

class UploaderController {

    public function __construct() {
        
    }

    public function handleRequest() {
        // HTTP headers for no cache etc
        header('Content-type: text/plain; charset=UTF-8');
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        // Get parameters
        $chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
        $chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
        $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
        $fileCount = $_GET['files'];


        if (\FWValidator::is_file_ending_harmless($fileName)) {
            try {
                $this->addChunk($fileName, $chunk, $chunks);
            } catch (UploaderException $e) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "' . $e->getMessage() . '"}, "id" : "id"}');
            }
        } else {
            if ($chunk == 0) {
                // only count first chunk
                // TODO: there must be a way to cancel the upload process on the client side
                $this->addHarmfulFileToResponse($fileName);
            }
        }

        if ($chunk == $chunks - 1) //upload finished
            $this->handleCallback($fileCount);

        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
    }

    /**
     * Set up uploader to only allow one single file to be uploaded
     */
    public function restrictUpload2SingleFile() {
        if (!isset($_SESSION['upload']['handlers'][$this->uploadId])) {
            $_SESSION['upload']['handlers'][$this->uploadId] = array();
        }
        // limit upload to 1 file at a time
        \ContrexxJavascript::getInstance()->setVariable('restrictUpload2SingleFile', true, "upload/widget_$this->uploadId");
        $_SESSION['upload']['handlers'][$this->uploadId]['singleFileMode'] = true;
    }

    protected function addHarmfulFileToResponse($fileName) {
        global $_ARRAYLANG;

        $response = null;
        //the response data.
        if (isset($_SESSION['upload']['handlers'][$this->uploadId]['response_data']))
            $response = UploadResponse::fromSession($_SESSION['upload']['handlers'][$this->uploadId]['response_data']);
        else
            $response = new UploadResponse();

        $response->addMessage(UploadResponse::STATUS_ERROR, $_ARRAYLANG['TXT_CORE_EXTENSION_NOT_ALLOWED'], $fileName);
        $_SESSION['upload']['handlers'][$this->uploadId]['response_data'] = $response->toSessionValue();
    }

    /**
     * Add a chunk to a file. Creates the file on first chunk, appends else.
     *
     * @param string $fileName upload name
     * @param int $chunk current chunk's number
     * @param int $chunks total chunks
     * @throws UploaderException thrown if upload becomes unusable
     */
    protected function addChunk($fileName, $chunk, $chunks) {

        //get a writable directory
        $tempPath = $_SESSION->getTempPath();
        $webTempPath = $_SESSION->getWebTempPath();
        $dirName = 'upload_' . $this->uploadId;

        $targetDir = $tempPath . '/' . $dirName;
        if (!file_exists($targetDir))
            \Cx\Lib\FileSystem\FileSystem::make_folder($webTempPath . '/' . $dirName);

        $cleanupTargetDir = false; // Remove old files
        $maxFileAge = 60 * 60; // Temp file age in seconds
        // 5 minutes execution time
        @set_time_limit(5 * 60);

        // remember the "raw" file name, we want to store all original
        // file names in the session.
        $originalFileName = $fileName;

        // Clean the fileName for security reasons
        // we're using a-zA-Z0-9 instead of \w because of the umlauts.
        // linux excludes them from \w, windows includes them. we do not want different
        // behaviours on different operating systems.
        $fileName = preg_replace('/[^a-zA-Z0-9\._-]+/', '', $fileName);

        //try to retrieve session file name for chunked uploads
        if ($chunk > 0) {
            if (isset($_SESSION['upload']['handlers'][$this->uploadId]['fileName']))
                $fileName = $_SESSION['upload']['handlers'][$this->uploadId]['fileName'];
            else
                throw new UploaderException('Session lost.');
        }
        else { //first chunk, store original file name in session
            $originalFileNames = array();
            if (isset($_SESSION['upload']['handlers'][$this->uploadId]['originalFileNames']))
                $originalFileNames = $_SESSION['upload']['handlers'][$this->uploadId]['originalFileNames'];
            $originalFileNames[$fileName] = $originalFileName;
            $_SESSION['upload']['handlers'][$this->uploadId]['originalFileNames'] = $originalFileNames;
        }

        // Make sure the fileName is unique (for chunked uploads only on first chunk, since we're using the same name)
        if (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName) && $chunk == 0) {
            $ext = strrpos($fileName, '.');
            $fileName_a = substr($fileName, 0, $ext);
            $fileName_b = substr($fileName, $ext);

            $count = 1;
            while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
                $count++;

            $fileName = $fileName_a . '_' . $count . $fileName_b;
        }
        //$fileName contains now the name we'll use for the whole upload process, so store it.
        $_SESSION['upload']['handlers'][$this->uploadId]['fileName'] = $fileName;

        // Remove old temp files
        if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
            while (($file = readdir($dir)) !== false) {
                $filePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                // Remove temp files if they are older than the max age
                if (preg_match('/\\.tmp$/', $file) && (filemtime($filePath) < time() - $maxFileAge))
                    @unlink($filePath);
            }

            closedir($dir);
        } else
            throw new UploaderException('Failed to open temp directory.');

        $contentType = '';
        // Look for the content type header
        if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
            $contentType = $_SERVER["HTTP_CONTENT_TYPE"];

        if (isset($_SERVER["CONTENT_TYPE"]))
            $contentType = $_SERVER["CONTENT_TYPE"];

        if (strpos($contentType, "multipart") !== false) {
            if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                // Open temp file
                $out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
                if ($out) {
                    // Read binary input stream and append it to temp file
                    $in = fopen($_FILES['file']['tmp_name'], "rb");

                    if ($in) {
                        while ($buff = fread($in, 4096))
                            fwrite($out, $buff);
                    } else
                        throw new UploaderException('Failed to open input stream.');

                    fclose($out);
                    unlink($_FILES['file']['tmp_name']);
                } else
                    throw new UploaderException('Failed to open output stream.');
            } else
                throw new UploaderException('Failed to move uploaded file.');
        } else {
            // Open temp file
            $out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
            if ($out) {
                // Read binary input stream and append it to temp file
                $in = fopen("php://input", "rb");

                if ($in) {
                    while ($buff = fread($in, 4096))
                        fwrite($out, $buff);
                } else
                    throw new UploaderException('Failed to open input stream.');

                fclose($out);
            } else {
                throw new UploaderException('Failed to open output stream.');
            }
        }

        // Send HTTP header to force the browser to send the next file-chunt
        // through a new connection. File-chunks that are sent through the
        // same connection get dropped by the web-server.
        header('Connection: close');
    }
    
        /**
     * Checks $fileCount against $_SESSION[upload][handlers][x][uploadedCount].
     * Takes appropriate action (calls callback if they equal).
     * @param integer $fileCount files in current uploado
     */
    public function handleCallback($fileCount) {
        if($fileCount == 1) { //one file, all done.
            $this->notifyCallback();
        }
        else {
            if(!isset($_SESSION['upload']['handlers'][$this->uploadId]['uploadedCount'])) { //multiple files, first file
                $_SESSION['upload']['handlers'][$this->uploadId]['uploadedCount'] = 1;
            }
            else {
                $count = $_SESSION['upload']['handlers'][$this->uploadId]['uploadedCount'] + 1;
                if($count == $fileCount) { //all files uploaded
                    unset($_SESSION['upload']['handlers'][$this->uploadId]['uploadedCount']);
                    $this->notifyCallback();
                }
                else {
                    $_SESSION['upload']['handlers'][$this->uploadId]['uploadedCount'] = $count;
                }
            }
        }
    }

}
