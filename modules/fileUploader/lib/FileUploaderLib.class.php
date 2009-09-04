<?php

/**
 * File Uploader
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_fileuploader
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';
require_once ASCMS_FRAMEWORK_PATH.'/Image.class.php';
require_once ASCMS_LIBRARY_PATH.'/FRAMEWORK/Validator.class.php';
require_once ASCMS_FRAMEWORK_PATH.'/System.class.php';

/**
 * File Uploader
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_fileuploader
 */
class FileUploaderLib {

    protected $objTpl;
    protected $defaultInterfaceLanguage = 'en';
    protected $defaultPartitionLength;
    protected $path;
    protected $mediaType;
    protected $frontendLanguageId = null;

    protected $moduleURI;

    private $uploadFileName;
    private $uploadFileExtension;
    private $uploadFileSuffix;

    /**
    * Constructor
    */
    public function __construct()
    {
        global $objInit;

        $this->objTpl = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/fileUploader/template');
        CSRF::add_placeholder($this->objTpl);
        $this->objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $this->mediaType = $this->getMediaType();
        $this->path = $this->getPath();
        $this->setFrontendLanguageId();
        $this->setPartitionLength();
        $this->moduleURI = '?section=fileUploader&amp;standalone=true';
    }

    /**
    * Get media type
    *
    * @access private
    */
    private function getMediaType()
    {
        return empty($_GET['type']) ? '' : $_GET['type'];
    }

    /**
    * Get the path
    *
    * @return string    current browsing path
    */
    private function getPath()
    {
        $path = "";
        if (isset($_GET['path']) && !stristr($_GET['path'], '..')) {
            $path = $_GET['path'];
        }
        $pos = strrpos($path, '/');
        if ($pos === false || $pos != (strlen($path)-1)) {
            $path .= "/";
        }

        return $path;
    }

    private function setFrontendLanguageId()
    {
        global $_FRONTEND_LANGID;

        if (!empty($_GET['langId']) || !empty($_POST['langId'])) {
            $this->frontendLanguageId = intval(!empty($_GET['langId']) ? $_GET['langId'] : $_POST['langId']);
        } else {
            $this->frontendLanguageId = $_FRONTEND_LANGID;
        }
    }

    private function setPartitionLength()
    {
        $this->defaultPartitionLength = FWSystem::getMaxUploadFileSize();
    }

    protected function getPartitionLength()
    {
        return $this->defaultPartitionLength-1000;
    }

    /**
     * Upload a file
     *
     * @param string $uploadFileName: the name of the file
     * @param string $tmpFileName: temporary name of th efile
     * @param string $uploadedFileName: reference to the file name after upload
     */
    public function upload($return = false)
    {
        global $_ARRAYLANG, $sessionObj;

        $fileExtension = '';

        if (!isset($_FILES['file'])) {
            die('Error:No file has been uploaded!');
        } else {
            $fileName = $_FILES['file']['name'];
            $partitionIndex = $_POST['partitionIndex'];
            $partitionCount = $_POST['partitionCount'];
            $fileId = $_POST['fileId'];
            $fileLength = $_POST['fileLength'];
        }

        // check if the file has a valid file extension
        if (!FWValidator::is_file_ending_harmless($fileName)) {
            die('Error:'.sprintf('The file %s was refused due to its file extension which is not allowed!', htmlentities($fileName, ENT_QUOTES, CONTREXX_CHARSET)));
        }

        if (!($sessionTmpPath = $sessionObj->getTempPath())) {
            die('Error:Unable to create a temporary session path!');
        }

        if (!move_uploaded_file($_FILES['file']['tmp_name'], $sessionTmpPath.'/'.$fileId.'_'.$partitionIndex)) {
            die('Error:Unable to load the uploaded file!');
        }

        switch($this->mediaType) {
            case 'media1':
            case 'archive1':
                $strPath    = ASCMS_MEDIA1_PATH.$this->path;
                $strWebPath = ASCMS_MEDIA1_WEB_PATH.$this->path;
            break;
            case 'media2':
            case 'archive2':
                $strPath    = ASCMS_MEDIA2_PATH.$this->path;
                $strWebPath = ASCMS_MEDIA2_WEB_PATH.$this->path;
            break;
            case 'media3':
            case 'archive3':
                $strPath    = ASCMS_MEDIA3_PATH.$this->path;
                $strWebPath = ASCMS_MEDIA3_WEB_PATH.$this->path;
            break;
            case 'media4':
            case 'archive4':
                $strPath    = ASCMS_MEDIA4_PATH.$this->path;
                $strWebPath = ASCMS_MEDIA4_WEB_PATH.$this->path;
            break;
            case 'shop':
                $strPath    = ASCMS_SHOP_IMAGES_PATH.$this->path;
                $strWebPath = ASCMS_SHOP_IMAGES_WEB_PATH.$this->path;
            break;
            case 'blog':
                $strPath    = ASCMS_BLOG_IMAGES_PATH.$this->path;
                $strWebPath = ASCMS_BLOG_IMAGES_WEB_PATH.$this->path;
            break;
            case 'podcast':
                $strPath    = ASCMS_PODCAST_IMAGES_PATH.$this->path;
                $strWebPath = ASCMS_PODCAST_IMAGES_WEB_PATH.$this->path;
            break;
            case 'downloads':
                $strPath    = ASCMS_DOWNLOADS_IMAGES_PATH.$this->path;
                $strWebPath = ASCMS_DOWNLOADS_IMAGES_WEB_PATH.$this->path;
            break;
            default:
                $strPath    = ASCMS_CONTENT_IMAGE_PATH.$this->path;
                $strWebPath = ASCMS_CONTENT_IMAGE_WEB_PATH.$this->path;
        }

        $objFile = new File();
        if (!is_writable($strPath) && !$objFile->setChmod($strPath, $strWebPath, '/')) {
            die('Error:Unsufficent file permissions to store uploaded file!');
        }

        $fullFile = $sessionTmpPath.'/full_'.$fileId;
        $uploadedFileFP = fopen($fullFile, 'a');
        $partitionFile = $sessionTmpPath.'/'.$fileId.'_'.$partitionIndex;
        $partitionFileFP = fopen($partitionFile, 'rb');
        $partitionFileContent = fread($partitionFileFP, filesize($partitionFile));
        fclose($partitionFileFP);
        fwrite($uploadedFileFP, $partitionFileContent);
        unlink($partitionFile);
        fclose($uploadedFileFP);

        // not all partitions have been uploaded yet, so let's stop here for now
        if ($partitionIndex != $partitionCount - 1) {
            die(0);
        }

        $fileSize = filesize($fullFile);
        if ($fileSize != $fileLength) {
            die('Error:Defragmented file doesn\'t match its original file size!');
        }

        $file = $strPath.$fileName;
        $arrFile = pathinfo($file);
        $i = '';
        $suffix = '';
        while (file_exists($file)) {
            $suffix = '-'.++$i;
            $file = $strPath.$arrFile['filename'].$suffix.'.'.$arrFile['extension'];
        }

        // move to uploaded file from the temp directory to the desired place
        if (!copy($fullFile, $file)) {
            die('Error:Unable to move the uploaded file to the desired directory!');
        }
        unlink ($fullFile);

        // create thumbnail if the uploaded file is an image
        $fileExtension = pathinfo($file, PATHINFO_EXTENSION);
        if (in_array($fileExtension, array('jpg', 'jpeg', 'png', 'gif'))) {
            ImageManager::_createThumb($strPath.'/', $strWebPath.'/', basename($file));
        }

        $this->uploadFileName = $arrFile['filename'];
        $this->uploadFileExtension = $arrFile['extension'];
        $this->uploadFileSuffix = $suffix;

        // file has been succesfully uploaded
        if ($return) {
            return true;
        }

        die(basename($file));
    }

    public function getUploadFileName()
    {
        return $this->uploadFileName;
    }
    public function getUploadFileExtension()
    {
        return $this->uploadFileExtension;
    }
    public function getUploadFileSuffix()
    {
        return $this->uploadFileSuffix;
    }
}
?>
