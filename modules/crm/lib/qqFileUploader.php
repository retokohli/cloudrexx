<?php
/**
 * qqFileUploader Class CRM
 *
 * @category   qqFileUploader
 * @package    contrexx
 * @subpackage module_crm
 * @author     SoftSolutions4U Development Team <info@softsolutions4u.com>
 * @copyright  2012 and CONTREXX CMS - COMVATION AG
 * @license    trial license
 * @link       www.contrexx.com
 */

/**
 * qqFileUploader Class CRM
 *
 * @category   qqFileUploader
 * @package    contrexx
 * @subpackage module_crm
 * @author     SoftSolutions4U Development Team <info@softsolutions4u.com>
 * @copyright  2012 and CONTREXX CMS - COMVATION AG
 * @license    trial license
 * @link       www.contrexx.com
 */

class qqFileUploader
{

    /**
    * Allowed Extensions
    *
    * @access public
    * @var array
    */
    public $allowedExtensions = array();

    /**
    * Size limit
    *
    * @access public
    * @var String
    */
    public $sizeLimit = null;

    /**
    * Input name
    *
    * @access public
    * @var String
    */
    public $inputName = 'qqfile';

    /**
    * Chunks Folder
    *
    * @access public
    * @var String
    */
    public $chunksFolder = 'chunks';

    /**
    * chunks Cleanup Probability
    * Once in 1000 requests on avg
    *
    * @access public
    * @var numeric
    */
    public $chunksCleanupProbability = 0.001; 

    /**
    * chunks Expire In
    * One week
    *
    * @access public
    * @var Integer
    */
    public $chunksExpireIn = 604800; 

    /**
    * Upload name
    *
    * @access protected
    * @var String
    */
    protected $uploadName;

    /**
     * constructor
     */
    function __construct()
    {
        $this->sizeLimit = $this->toBytes(ini_get('upload_max_filesize'));
    }

    /**
     * Get the original filename
     */
    public function getName()
    {
        if (isset($_REQUEST['qqfilename']))
            return $_REQUEST['qqfilename'];

        if (isset($_FILES[$this->inputName]))
            return $_FILES[$this->inputName]['name'];
    }

    /**
     * Get the name of the uploaded file
     */
    public function getUploadName()
    {
        return $this->uploadName;
    }

    /**
     * Get the name of the thumb file
     */
    public function getThumbName()
    {
        $this->createThumbnailOfImage($this->uploadName);
    }

    /**
     * Process the upload.
     * @param string $uploadDirectory Target directory.
     * @param string $name Overwrites the name of the file.
     */
    public function handleUpload($uploadDirectory, $name = null)
    {

        if (is_writable($this->chunksFolder) &&
            1 == mt_rand(1, 1/$this->chunksCleanupProbability)){

            // Run garbage collection
            $this->cleanupChunks();
        }

        // Check that the max upload size specified in class configuration does not
        // exceed size allowed by server config
        if ($this->toBytes(ini_get('post_max_size')) < $this->sizeLimit ||
            $this->toBytes(ini_get('upload_max_filesize')) < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
            return array('error'=>"Server error. Increase post_max_size and upload_max_filesize to ".$size);
        }

        if (!is_writable($uploadDirectory) || !is_executable($uploadDirectory)){
            return array('error' => "Server error. Uploads directory isn't writable or executable.");
        }

        if(!isset($_SERVER['CONTENT_TYPE'])) {
            return array('error' => "No files were uploaded.");
        } else if (strpos(strtolower($_SERVER['CONTENT_TYPE']), 'multipart/') !== 0){
            return array('error' => "Server error. Not a multipart request. Please set forceMultipart to default value (true).");
        }

        // Get size and name

        $file = $_FILES[$this->inputName];
        $size = $file['size'];

        if ($name === null){
            $name = $this->getName();
        }

        // Validate name

        if ($name === null || $name === ''){
            return array('error' => 'File name empty.');
        }

        // Validate file size

        if ($size == 0){
            return array('error' => 'File is empty.');
        }

        if ($size > $this->sizeLimit){
            return array('error' => 'File is too large.');
        }

        // Validate file extension

        $pathinfo = pathinfo($name);
        $ext = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';

        if($this->allowedExtensions && !in_array(strtolower($ext), array_map("strtolower", $this->allowedExtensions))){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }

        // Save a chunk

        $totalParts = isset($_REQUEST['qqtotalparts']) ? (int)$_REQUEST['qqtotalparts'] : 1;

        if ($totalParts > 1){

            $chunksFolder = $this->chunksFolder;
            $partIndex = (int)$_REQUEST['qqpartindex'];
            $uuid = $_REQUEST['qquuid'];

            if (!is_writable($chunksFolder) || !is_executable($uploadDirectory)){
                return array('error' => "Server error. Chunks directory isn't writable or executable.");
            }

            $targetFolder = $this->chunksFolder.DIRECTORY_SEPARATOR.$uuid;

            if (!file_exists($targetFolder)){
                mkdir($targetFolder);
            }

            $target = $targetFolder.'/'.$partIndex;
            $success = move_uploaded_file($_FILES[$this->inputName]['tmp_name'], $target);

            // Last chunk saved successfully
            if ($success AND ($totalParts-1 == $partIndex)){

                $target = $this->getUniqueTargetPath($uploadDirectory, $name);
                $this->uploadName = basename($target);

                $target = fopen($target, 'w');

                for ($i=0; $i<$totalParts; $i++){
                    $chunk = fopen($targetFolder.'/'.$i, "w");
                    stream_copy_to_stream($chunk, $target);
                    fclose($chunk);
                }

                // Success
                fclose($target);

                for ($i=0; $i<$totalParts; $i++){
                    $chunk = fopen($targetFolder.'/'.$i, "r");
                    unlink($targetFolder.'/'.$i);
                }

                rmdir($targetFolder);

                return array("success" => true);

            }

            return array("success" => true);

        } else {

            $target = $this->getUniqueTargetPath($uploadDirectory, $name);

            if ($target){
                $this->uploadName = basename($target);

                if (move_uploaded_file($file['tmp_name'], $target)){
                    if (!\Cx\Lib\FileSystem\FileSystem::makeWritable($target)) {
                        return array('error'=> 'Could not make the file as writable.');
                    }
                    return array('success'=> true);
                }
            }

            return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
    }

    /**
     * Returns a path to use with this upload. Check that the name does not exist,
     * and appends a suffix otherwise.
     * @param string $uploadDirectory Target directory
     * @param string $filename The name of the file to use.
     */
    protected function getUniqueTargetPath($uploadDirectory, $filename)
    {
        // Allow only one process at the time to get a unique file name, otherwise
        // if multiple people would upload a file with the same name at the same time
        // only the latest would be saved.

        if (function_exists('sem_acquire')){
            $lock = sem_get(ftok(__FILE__, 'u'));
            sem_acquire($lock);
        }

        $pathinfo = pathinfo($filename);
        $base = $pathinfo['filename'];
        $ext = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';
        $ext = $ext == '' ? $ext : '.' . $ext;

        $unique = $base;
        $suffix = 0;

        // Get unique file name for the file, by appending random suffix.

        while (file_exists($uploadDirectory . DIRECTORY_SEPARATOR . $unique . $ext)){
            $suffix += rand(1, 999);
            $unique = $base.'-'.$suffix;
        }

        $result =  $uploadDirectory . DIRECTORY_SEPARATOR . $unique . $ext;

        // Create an empty target file
        if (!touch($result)){
            // Failed
            $result = false;
        }

        if (function_exists('sem_acquire')){
            sem_release($lock);
        }

        return $result;
    }

    /**
     * Deletes all file parts in the chunks folder for files uploaded
     * more than chunksExpireIn seconds ago
     */
    protected function cleanupChunks()
    {
        foreach (scandir($this->chunksFolder) as $item){
            if ($item == "." || $item == "..")
                continue;

            $path = $this->chunksFolder.DIRECTORY_SEPARATOR.$item;

            if (!is_dir($path))
                continue;

            if (time() - filemtime($path) > $this->chunksExpireIn){
                $this->removeDir($path);
            }
        }
    }

    /**
     * Removes a directory and all files contained inside
     * @param string $dir
     */
    protected function removeDir($dir)
    {
        foreach (scandir($dir) as $item){
            if ($item == "." || $item == "..")
                continue;

            unlink($dir.DIRECTORY_SEPARATOR.$item);
        }
        rmdir($dir);
    }

    /**
     * Converts a given size with units to bytes.
     * @param string $str
     */
    protected function toBytes($str)
    {
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }
        return $val;
    }

    /**
     * Create thumbnail of image
     * 
     * @param String $imageName
     * 
     * @return String
     */
    protected function createThumbnailOfImage($imageName)
    {
//               DBG::activate();
        if (empty($objImage)) {
            $objImage = new ImageManager();
        }

        $objImage->_createThumbWhq(
                CRM_ACCESS_PROFILE_IMG_PATH.'/',
                CRM_ACCESS_PROFILE_IMG_WEB_PATH.'/',
                $imageName,
                40,
                40,
                70,
                '_40X40.thumb'
            );

        return $objImage->_createThumbWhq(
                CRM_ACCESS_PROFILE_IMG_PATH.'/',
                CRM_ACCESS_PROFILE_IMG_WEB_PATH.'/',
                $imageName,
                121,
                160,
                70
            );
        
    }

    /**
     * download
     * 
     * @param String $file_directory
     * @param String $file_name
     *
     * @return null
     */
    function download($file_directory, $file_name = '')
    {
        
        $file_path = $file_directory.$file_name;
        
        if (is_file($file_path)) {
            if (!preg_match('/\.(gif|jpe?g|png)$/i', $file_name)) {
                $this->header('Content-Description: File Transfer');
                $this->header('Content-Type: application/octet-stream');
                $this->header('Content-Disposition: attachment; filename="'.$file_name.'"');
                $this->header('Content-Transfer-Encoding: binary');
            } else {
                // Prevent Internet Explorer from MIME-sniffing the content-type:
                $this->header('X-Content-Type-Options: nosniff');
                $this->header('Content-Type: '.$this->get_file_type($file_path));
                $this->header('Content-Disposition: inline; filename="'.$file_name.'"');
            }
            $this->header('Content-Length: '.$this->get_file_size($file_path));
            $this->header('Last-Modified: '.gmdate('D, d M Y H:i:s T', filemtime($file_path)));
            $this->readfile($file_path);
        }
    }

    /**
     * Get file type
     * 
     * @param String $file_path
     *
     * @return String
     */
    protected function get_file_type($file_path)
    {
        switch (strtolower(pathinfo($file_path, PATHINFO_EXTENSION))) {
            case 'jpeg':
            case 'jpg':
                return 'image/jpeg';
            case 'png':
                return 'image/png';
            case 'gif':
                return 'image/gif';
            default:
                return '';
        }
    }

    /**
     * Get file size
     *
     * @param String  $file_path
     * @param boolean $clear_stat_cache
     *
     * @return Numeric
     */
    protected function get_file_size($file_path, $clear_stat_cache = false)
    {
        if ($clear_stat_cache) {
            clearstatcache(true, $file_path);
        }
        return $this->fix_integer_overflow(filesize($file_path));

    }

    /**
     * Fix for overflowing signed 32 bit integers,
     * works for sizes up to 2^32-1 bytes (4 GiB - 1):
     * 
     * @param Integer $size
     *
     * @return Integer
     */
    protected function fix_integer_overflow($size)
    {
        if ($size < 0) {
            $size += 2.0 * (PHP_INT_MAX + 1);
        }
        return $size;
    }

    /**
     * header
     * 
     * @param String $str
     */
    protected function header($str)
    {
        header($str);
    }

    /**
     * Read the file
     * 
     * @param String $file_path
     * 
     * @return String
     */
    protected function readfile($file_path)
    {
        return readfile($file_path);
    }
}