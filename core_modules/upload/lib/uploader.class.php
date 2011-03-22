<?php
/**
 * Exceptions thrown by uploader
 */
class UploaderException extends Exception
{
}

/**
 * Base class for all kinds of uploaders.
 */
abstract class Uploader
{
    /**
     * @see Uploader::callbackData
     * @var Array callback data as passed to @link Uploader::callbackData
     */
    protected $callbackData;

    /**
     * @see Uploader::setUploadId
     * @var int this upload's id. 1-based.
     */
    protected $uploadId;

    /**
     * @see Uploader::setJsInstanceName()
     * @var string
     */
    protected $jsInstanceName;

    /**
     * Whether we're handling a backend request.
     * @var bool
     */
    protected $isBackendRequest;

    /**
     * user-defined data assigned with the upload.
     */
    protected $data = null;

    /**
     * @see FormUploader::setRedirectUrl()
     * @var string
     */
    public $redirectUrl = null;

    /**
     * @param boolean $backend whether this is a backend request or not
     */
    public function __construct($backend)
    {
       $this->isBackendRequest = $backend;
    }
    /**
     * Set a callback to be called when uploading has finished.
     *
     * The callback will be called with the arguments
     * * $tempPath, containing the path to the folder where the files are
     * * $data, containing the data set by @link Uploader::setData
     * * $uploadId, containing the id of the current upload
     *
     * The callback can either return null if he moves the files himself or
     * { <path_string> , <web_path_string> } if the files should be moved
     *
     * @param Array $callbackData { 
     *   <classFilePath>,
     *   <className> | <classReference>,
     *   <functionName>
     * }
     * @param boolean $updateSession if a new callback is set, this will update the
     *   session. defaults to true.
     */
    public function setFinishedCallback($callbackData, $updateSession = true)
    {
        $this->callbackData = $callbackData;
        if($updateSession) //write callback to session
            $_SESSION['upload_callback_'.$this->uploadId] = $this->callbackData = $callbackData;
    }

    /**
     * Used by the factory to set the Url where the User is redirected to after a successful upload
     * , relative to cmsRoot, e.g. "index.php?cmd=test". Mainly for iframe-using uploaders.
     * Not all uploaders may need a redirect, chunked uploading happens without redirect for example.
     * Redirection is triggered via @link Uploader::redirect()
     * @param string $url the url, beginning with ASCMS_PATH_OFFSET or ASCMS_ADMIN_WEB_PATH
     * @param boolean $updateSession if a new url is set, this will update the
     *   session. defaults to true.
     */
    public function setRedirectUrl($url, $updateSession = true) {
        if($updateSession)
            $_SESSION['upload_redirect_url_'.$this->uploadId] = $url;

        global $_CONFIG;        
        $this->redirectUrl = /*"http://".$_CONFIG['domainUrl'].*/$url;
    }

    /**
     * Redirects to the url previously set by @link Uploader::setRedirectUrl()
     * @throws UploaderException if redirect url is not set
     */
    protected function redirect() {
        if($this->redirectUrl == null)
            throw new UploaderException('tried to redirect without a redirect url set via Uploader::setRedirectUrl()!');
        CSRF::header('Location: ' . $this->redirectUrl);
        die();
    }

    /**
     * Each upload has a unique id. Use this function to set it.
     *
     * @param int $id
     */
    public function setUploadId($id)
    {
        $this->uploadId = $id;
        if(isset($_SESSION['upload_callback_'.$this->uploadId]))
            $this->callbackData = $_SESSION['upload_callback_'.$this->uploadId];
    }

    /**
     * Returns the id of the current upload
     * @return int
     */
    public function getUploadId()
    {
        return $this->uploadId;
    }

    /**
     * Sets the name used to make the uploader's Javascript object accessible.
     * @param string $name
     */
    public function setJsInstanceName($name) {
        $this->jsInstanceName = $name;
    }

    /**
     * Gets the Name that shall be used for this Uploaders Javascript object.
     * @return string
     */
    protected function getJsInstanceName() {
        return $this->jsInstanceName;
    }

    /**
     * Checks whether the Js Instance name is set.
     * @return boolean
     */
    protected function hasJsInstanceName() {
        return !is_null($this->jsInstanceName);
    }

    /**
     * Takes care of setting the Javascript instance if needed.
     * Sets the placeholder JS_INSTANCE_CODE in $tpl.
     * Uploader templates do have to include this placeholder.
     * This is no nice coding, but it saves us a lot of lines and messing with blocks.
     *
     * @param Object $tpl the template
     * @param string $objectName the object name to assign in the JS part
     */
    protected function handleInstanceBusiness($tpl, $objectName) {
        if($this->hasJsInstanceName())
            $tpl->setVariable('JS_INSTANCE_CODE', "cx.instances.set('".$this->getJsInstanceName()."',".$objectName.",'uploader');");
        else
            $tpl->setVariable('JS_INSTANCE_CODE', '//remark: no instance name set');

        $tpl->setVariable('UPLOAD_ID', $this->uploadId);
    }

    /**
     * Used to set user-defined data assigned to this upload.
     *
     * This data is passed as an argument to the callback set by @link Uploader::setFinishedCallback().
     *
     * @param $data the data
     */
    public function setData($data) {
        $this->data = $data;
        //store data to session
        $_SESSION['upload_data_'.$this->uploadId] = $data;
    }

    /**
     * Used to retrieve user-defined data assigned to this upload.
     */
    public function getData() {
        if($this->data != null) { //$data is set, this means it's up to date
            return $this->data;
        }
        else if(isset($_SESSION['upload_data_'.$this->uploadId])) //try to recover data from session
        {
            $this->data = $_SESSION['upload_data_'.$this->uploadId];
            return $this->data; //cache for future gets
        }
        else { //nothing set yet, return null
            return null;
        }
    }

    /**
     * Notifies the callback. Invoked on upload completion.
     */
    public function notifyCallback()
    {
        global $sessionObj;

        //temporary path where files were uploaded
        $tempDir = '/upload_'.$this->uploadId;
        $tempPath = $sessionObj->getTempPath().$tempDir;
        $tempWebPath = $sessionObj->getWebTempPath().$tempDir;

        //we're going to call the callbck, so the data is not needed anymore
        //well... not quite sure. need it again in contact form.
        //todo: code session cleanup properly if time.
        //$this->cleanupCallbackData();

        $classFile = $this->callbackData[0];
        //call the callback, get return code
        if($classFile != null) {
            if(!file_exists($classFile))
                throw new UploaderException("Class file '$classFile' specified for callback does not exist!");
            require_once $this->callbackData[0];
        }

        $ret = call_user_func(array($this->callbackData[1],$this->callbackData[2]),$tempPath,$tempWebPath,$this->getData(), $this->uploadId);
        
        //the callback could have returned a path where he wants the files moved to
        if(!is_null($ret)) { //we need to move the files
            //gather target information
            $path = pathinfo($ret[0]);
            $pathWeb = pathinfo($ret[1]);
            //make sure the target directory is writable
            $fm = new File();
            $fm->setChmod($path['dirname'], $pathWeb['dirname'], $path['basename']);

            //revert $path to whole path instead of pathinfo path for copying
            $path = $path['dirname'].'/'.$path['basename'];
            
            //move everything uploaded to target dir
            $h = opendir($tempPath);
            while(false !== ($f = readdir($h))) {
                //skip . and ..
                if($f == '.' || $f == '..')
                    continue;

                rename($tempPath.'/'.$f, $path.'/'.$f);
            }
            closedir($h);
        }
        //delete the files left
        $h = opendir($tempPath);
        while(false !== ($f = readdir($h))) {
            if($f != '..' && $f != '.')
                @unlink($tempPath.'/'.$f);
        }
        //delete the folder
        @rmdir($tempPath);

        closedir($h);
    }

    /**
     * Cleans up the session - unsets the callback data stored for this upload
     */
    protected function cleanupCallbackData() {
        unset($_SESSION['upload_callback_'.$this->uploadId]);
    }

    /**
     * Implement to handle upload requests.
     *
     * Call @link Uploader::notifyCallback() from this method if the upload is finished.
     */
    abstract public function handleRequest();

    /**
     * Implement to return the XHtml needed to display the uploader.
     *
     * @return string XHtml-Code for the uploader
     */
    abstract public function getXHtml();

    /**
     * Gets the correct upload path
     * Handles the section/cmd naming differences and location of index.php.
     *
     * @param string $type the uploadType to specify
     * @return string the path, uploadId, uploadType, cmd/section and act/cmd set as get parameters.
     */
    protected function getUploadPath($type)
    {
        $uploadPath = '';
        if($this->isBackendRequest)
            $uploadPath = ASCMS_ADMIN_WEB_PATH.'/index.php?cmd=upload&act=upload';
        else
            $uploadPath = ASCMS_PATH_OFFSET.'/index.php?section=upload&cmd=upload';          
        $uploadPath .= '&uploadId='.$this->uploadId.'&uploadType='.$type;
        return $uploadPath;
    }

    /**
     * Add a chunk to a file. Creates the file on first chunk, appends else.
     *
     * @param string $fileName upload name
     * @param int $chunk current chunk's number
     * @param int $chunks total chunks
     * @throws UploaderException thrown if upload becomes unusable
     */
    protected function addChunk($fileName, $chunk, $chunks)
    {
        global $sessionObj;
      
        //create a file manager
        require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';
        $fm = new File();
        
        //get a writable directory
        $tempPath = $sessionObj->getTempPath();
        $webTempPath = $sessionObj->getWebTempPath();
        $dirName = 'upload_'.$this->uploadId;

        $targetDir = $tempPath.'/'.$dirName;
        if(!file_exists($targetDir))
            $fm->mkdir($tempPath, $webTempPath, '/'.$dirName);

        $cleanupTargetDir = false; // Remove old files
        $maxFileAge = 60 * 60; // Temp file age in seconds

        // 5 minutes execution time
        @set_time_limit(5 * 60);

        // Clean the fileName for security reasons
        $fileName = preg_replace('/[^\w\._]+/', '', $fileName);

        //try to retrieve session file name for chunked uploads
        if ($chunk > 0)
            if(isset($_SESSION['upload_fileName_'.$this->uploadId]))
                $fileName = $_SESSION['upload_fileName_'.$this->uploadId];
            else
                throw new UploaderException('Session lost.');


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
        $_SESSION['upload_fileName_'.$this->uploadId] = $fileName;

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
            } else
                throw new UploaderException('Failed to open output stream.');
           }
    }
}