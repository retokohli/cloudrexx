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
require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';
require_once(ASCMS_FRAMEWORK_PATH.'/Image.class.php');

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
    private $defaultPartitionLength = 1048576;
    private $path;
    private $mediaType;
    private $frontendLanguageId = null;

    private $moduleURI;

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

        case 'applet':
            $this->sendApplet();
            exit;
            break;

        default:
            $this->showUploadApplet();
            break;
        }
    }

    private function sendApplet()
    {

            $a = ASCMS_ADMIN_WEB_PATH.'/index.php'.$this->moduleURI.'&amp;act=upload&amp;type='.$this->mediaType.'&amp;path='.urlencode($this->path);
            $b =  $this->getPartitionLength();
        $jnlpXML = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<jnlp spec="1.0+" codebase="http://dev.contrexxlabs.com/" > 
<information>
    <title>Time Check</title>
    <vendor>Java Developer Connection</vendor>
    <homepage href="/jdc" />
    <description>Demonstration of JNLP</description>
</information> 
<security> <j2ee-application-client-permissions/> </security> 
<resources> <j2se version="1.2+" /> <jar href="/tmp/jumploader_z.jar"/> </resources> 
<applet-desc
            documentBase="http://dev.contrexxlabs.com/"
            name="jumpLoaderApplet"
            main-class="jmaster.jumploader.app.JumpLoaderApplet"
            width="800"
            height="600">

        <param name="uc_uploadUrl" value="$a" />
        <param name="uc_partitionLength" value="$b" />
        <param name="gc_loggingLevel" value="INFO" />
        <param name="ac_fireAppletInitialized" value="true"/>
        <param name="ac_fireUploaderStatusChanged" value="true"/>
		<param name="vc_uploadListViewName" value="_compact"/>
		<param name="vc_useThumbs" value="false"/>
    </applet-desc>
</jnlp>
XML;
//<application-desc main-class="jmaster.jumploader.app.JumpLoaderApplet" />
        header('Content-Type: application/x-java-jnlp-file');
        die($jnlpXML);

        header('Content-Length: '.(filesize(ASCMS_MODULE_PATH.'/fileUploader/lib/fileUploader.jar')));
        header('Content-Type: application/java-archive');
        header('Content-Disposition: attachment; filename="fileUploader.jar"');
        die(file_get_contents(ASCMS_MODULE_PATH.'/fileUploader/lib/fileUploader.jar'));
    }

    private function sendLanguageArchive()
    {
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
            'FILEUPLOADER_APPLET_PATH'  => ASCMS_ADMIN_WEB_PATH.'/fileUploader.jar',
//            'FILEUPLOADER_APPLET_PATH'  => '/tmp/jumploader_z.jar',
//            'FILEUPLOADER_APPLET_PATH'  => ASCMS_ADMIN_WEB_PATH.'/index.php'.$this->moduleURI.'&amp;act=applet',
            'FILEUPLOADER_HANDLER_PATH' => ASCMS_ADMIN_WEB_PATH.'/index.php'.$this->moduleURI.'&amp;act=upload&amp;type='.$this->mediaType.'&amp;path='.urlencode($this->path),
            'FILEUPLOADER_PARTITION_LENGTH' => $this->getPartitionLength()
        ));

        $this->objTpl->show();
    }

    private function getPartitionLength()
    {
        return $this->defaultPartitionLength;
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
            die('Error:No file has been uploaded!');
        } else {
            $fileName = $_FILES['file']['name'];
            $partitionIndex = $_POST['partitionIndex'];
            $partitionCount = $_POST['partitionCount'];
            $fileId = $_POST['fileId'];
            $fileLength = $_POST['fileLength'];
        }

        if (!($sessionTmpPath = $sessionObj->getTempPath())) {
            die('Error:Unable to create a temporary session path!');
        }

        if (!move_uploaded_file($_FILES['file']['tmp_name'], $sessionTmpPath.'/'.$fileId.'_'.$partitionIndex)) {
            die('Error:Unable to load the uploaded file!');
        }

        $partitionsLength = 0;
        for ($i = 0; $i < $partitionCount; $i++) {
            $partitionFile = $sessionTmpPath.'/'.$fileId.'_'.$i;
            if (file_exists($partitionFile)) {
                $partitionsLength += filesize($partitionFile);
            } else {
                die('Error:A partition is missing');
            }
        }

        if ($partitionsLength != $fileLength) {
            die('Error:Defragmented file doesn\'t match its original file size!');
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
            die('Error:Unsufficent file permissions to store uploaded file!');
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

        // create thumbnail if the uploaded file is an image
        $fileExtension = pathinfo($file, PATHINFO_EXTENSION);
        if (in_array($fileExtension, array('jpg', 'jpeg', 'png', 'gif'))) {
            ImageManager::_createThumb($strPath.'/', $strWebPath.'/', basename($file));
        }

        // file has been succesfully uploaded
        die(basename($file));
    }
}
?>
