<?php
$_ARRAYLANG['TXT_FILEUPLOADER_CLOSE'] = 'Schliessen';
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
#require_once ASCMS_FRAMEWORK_PATH.'/System.class.php';
require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';
#require_once ASCMS_CORE_PATH.'/Tree.class.php';
#require_once(ASCMS_FRAMEWORK_PATH.DIRECTORY_SEPARATOR.'Image.class.php');

/**
 * File Uploader
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_fileuploader
 */
class FileUploader {

    private $objTpl;
    private $defaultInterfaceLanguage = 'en';
#    var $_pageTitle;
#    var $_okMessage = array();
#    var $_errMessage = array();
#    var $_arrFiles = array();
#    var $_arrDirectories = array();
    private $path;
    private $mediaType;
#    var $_iconWebPath = '';
    private $frontendLanguageId = null;

    private $moduleURI;
#    var $_absoluteURIs = false;
#    var $_mediaType = '';
#    var $_arrWebpages = array();
/*    var $_arrMediaTypes = array(
        'files'     => 'TXT_FILEBROWSER_FILES',
        'webpages'  => 'TXT_FILEBROWSER_WEBPAGES',
        'media1'    => 'TXT_FILEBROWSER_MEDIA_1',
        'media2'    => 'TXT_FILEBROWSER_MEDIA_2',
        'media3'    => 'TXT_FILEBROWSER_MEDIA_3',
        'media4'    => 'TXT_FILEBROWSER_MEDIA_4',
        'shop'      => 'TXT_FILEBROWSER_SHOP',
        'blog'      => 'TXT_FILEBROWSER_BLOG',
        'podcast'   => 'TXT_FILEBROWSER_PODCAST'
    );*/
#    var $_shopEnabled;
#    var $_blogEnabled;
#    var $_podcastEnabled;



    /**
    * Constructor
    */
    public function __construct()
    {
        $this->objTpl = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/fileUploader/template');
        $this->objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $this->mediaType = $this->getMediaType();
        $this->path = $this->getPath();
        $this->setFrontendLanguageId();

        $this->moduleURI = '?cmd=fileUploader&amp;standalone=true';
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
debug("\nFILES:".var_export($_FILES, true)."\n\n");
debug("\nPOST:".var_export($_POST, true)."\n\n");
debug("\nGET:".var_export($_GET, true)."\n\n");
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


    /**
    * Set the backend page
    */
    public function getPage()
    {

        if (!isset($_REQUEST['act'])) {
            $_REQUEST['act'] = '';
        }
        switch ($_REQUEST['act']) {
        case 'upload':
            $this->upload();
            exit;
            break;

        case 'language':
            $this->sendLanguageArchive();
            exit;
            break;

        default:
            $this->showUploadApplet();
            break;
        }
    }

    private function sendLanguageArchive()
    {error_reporting(E_ALL);ini_set('display_errors', 1);
        global $_ARRAYLANG;

        $file = str_replace(array_keys($_ARRAYLANG), $_ARRAYLANG, file_get_contents(ASCMS_MODULE_PATH.'/fileUploader/lib/fileUploader.lang'));

        require_once ASCMS_LIBRARY_PATH . '/PEAR/Download.php';
        require_once ASCMS_LIBRARY_PATH.'/pclzip/pclzip.lib.php';

//        $languageArchive = new PclZip('php://memory');
//        $languageArchive->add
        HTTP_Download::staticSend(array('data' => $file, 'contenttype' => 'application/zip', 'gzip'=> true));
    }

    /**
    * Show the file upload applet
    *
    * @access private
    * @global array
    */
    private function showUploadApplet()
    {
        global $_ARRAYLANG, $_LANGID, $objLanguage, $objInit;
/*        $arrLanguage = $objLanguage->getLanguageArray();
        if (file_exists(ASCMS_MODULE_PATH.'/b/applets/messages_'.$arrLanguage[$_LANGID]['lang'].'.zip')) {
            $lang = $arrLanguage[$_LANGID]['lang'];
        } elseif (file_exists(ASCMS_DOCUMENT_ROOT.'/lib/applets/messages_'.$arrLanguage[$objInit->defaultBackendLangId]['lang'].'.zip')) {
            $lang = $arrLanguage[$objInit->defaultBackendLangId]['lang'];
        } else {
            $lang = $this->defaultInterfaceLanguage;
        }*/


        $this->objTpl->loadTemplateFile('module_fileUploader_frame.html');

        $this->objTpl->setVariable(array(
            'TXT_FILEUPLOADER_CLOSE' => $_ARRAYLANG['TXT_FILEUPLOADER_CLOSE'],
            'CONTREXX_CHARSET'      => CONTREXX_CHARSET,
//            'FILEUPLOADER_APPLET_PATH'  => ASCMS_ADMIN_WEB_PATH.'/index.php'.$this->moduleURI.'&amp;act=language,'.ASCMS_MODULE_WEB_PATH.'/fileUploader/lib/fileUploader.jar',
            'FILEUPLOADER_APPLET_PATH'  => ASCMS_MODULE_WEB_PATH.'/fileUploader/lib/fileUploader.jar',
            'FILEUPLOADER_HANDLER_PATH' => ASCMS_ADMIN_WEB_PATH.'/index.php'.$this->moduleURI.'&amp;act=upload&amp;type='.$this->mediaType.'&amp;path='.urlencode($this->path),
            'FILEUPLOADER_PARTITION_LENGTH' => $this->getPartitionLength()
        ));

        $this->objTpl->show();
    }

    private function getPartitionLength()
    {
        return 1024*1024;
    }


    /**
     * Upload a file
     *
     * @param string $uploadFileName: the name of the file
     * @param string $tmpFileName: temporary name of th efile
     * @param string $uploadedFileName: reference to the file name after upload
     */
    private function upload()
    {
        global $_ARRAYLANG, $sessionObj;
        $file = $uploadFileName;
        $fileExtension = '';

        if (!isset($_FILES['file'])) {
debug(1);
            return false;
        } else {
            $fileName = $_FILES['file']['name'];
            $partitionIndex = $_POST['partitionIndex'];
            $partitionCount = $_POST['partitionCount'];
            $fileId = $_POST['fileId'];
            $fileLength = $_POST['fileLength'];
        }

        if (!($sessionTmpPath = $sessionObj->getTempPath())) {
debug(2);
            return false;
        }

        if (!move_uploaded_file($_FILES['file']['tmp_name'], $sessionTmpPath.'/'.$fileId.'_'.$partitionIndex)) {
debug(3);
            return false;
        }

        $partitionsLength = 0;
        for ($i = 0; $i < $partitionCount; $i++) {
            $partitionFile = $sessionTmpPath.'/'.$fileId.'_'.$i;
            if (file_exists($partitionFile)) {
                $partitionsLength += filesize($partitionFile);
            } else {
debug(4);
                return false;
            }
        }

        if ($partitionsLength != $fileLength) {
debug(5);
            return false;
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
            default:
                $strPath    = ASCMS_CONTENT_IMAGE_PATH.$this->path;
                $strWebPath = ASCMS_CONTENT_IMAGE_WEB_PATH.$this->path;
        }

        $objFile = new File();
        if (!is_writable($strPath) && !$objFile->setChmod($strPath, $strWebPath, '/')) {
debug(6);
            return false;
        }

        $file = $strPath.$fileName;
        while (file_exists($file)) {
            if (!isset($arrFile)) {
                $arrFile = pathinfo($file);
                $i = 0;
            }
            $file = $strPath.$arrFile['filename'].'-'.++$i.'.'.$arrFile['extension'];
        }

        $uploadedFileFP = fopen($file, 'a');
        for ($i = 0; $i < $partitionCount; $i++) {
            $partitionFile = $sessionTmpPath.'/'.$fileId.'_'.$i;
            $partitionFileFP = fopen($partitionFile, 'rb');
            $partitionFileContent = fread($partitionFileFP, filesize($partitionFile));
            fclose($partitionFileFP);
            fwrite($uploadedFileFP, $partitionFileContent);
            unlink($partitionFile);
        }
        fclose($file);
debug($file);
debug(7);

return true;






        $nr = 1;

        if (@file_exists($strPath.$uploadFileName)) {
            if (preg_match('/.*\.(.*)$/', $uploadFileName, $arrSubPatterns)) {
                $fileName = substr($uploadFileName, 0, strrpos($uploadFileName, '.'));
                $fileExtension = $arrSubPatterns[1];
                $file = $fileName.'-'.$nr.'.'.$fileExtension;

                while (@file_exists($strPath.$file)) {
                    $file = substr($uploadFileName, 0, strrpos($uploadFileName, '.')).'-'.$nr.'.'.$fileExtension;
                    $nr++;
                }
            } else {
                return false;
            }
        }
        $uploadedFileName = $file;

        if (move_uploaded_file($tmpFileName, $strPath.$file)) {
            if (!isset($objFile)) {
                $objFile = &new File();
            }
            $objFile->setChmod($strPath, $strWebPath, $file);
        }

        $fileType = pathinfo($strPath.$file);

        if($fileType['extension'] == 'jpg' || $fileType['extension'] == 'jpeg' || $fileType['extension'] == 'png' || $fileType['extension'] == 'gif'){
            if ($this->_createThumb($strPath, $strWebPath, $file)) {
              $this->_pushStatusMessage(sprintf($_ARRAYLANG['TXT_FILEBROWSER_THUMBNAIL_SUCCESSFULLY_CREATED'], $strWebPath.$file));
            }
        }
    }


    function _createThumb($strPath, $strWebPath, $file, $height = 80, $quality = 90)
    {
        global $_ARRAYLANG;
        $objFile = &new File();

        $_objImage = &new ImageManager();
        $tmpSize    = getimagesize($strPath.$file);
        $thumbWidth = $height / $tmpSize[1] * $tmpSize[0];
        $_objImage->loadImage($strPath.$file);
        $_objImage->resizeImage($thumbWidth, $height, $quality);
        $_objImage->saveNewImage($strPath.$file . '.thumb');

        if ($objFile->setChmod($strPath, $strWebPath, $file . '.thumb')) {
           return true;
        }
        return false;
    }

}

function debug($msg)
{
return;
$asdf = fopen('../uploader.log', 'a');

fwrite($asdf, $msg);
fclose($asdf);
}
?>
