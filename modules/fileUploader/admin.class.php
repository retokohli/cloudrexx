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
require_once ASCMS_MODULE_PATH.'/fileUploader/lib/FileUploaderLib.class.php';

/**
 * File Uploader
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_fileuploader
 */
class FileUploader extends FileUploaderLib
{
    /**
    * Constructor
    */
    public function __construct()
    {
        parent::__construct();
        $this->moduleURI = '?cmd=fileUploader&amp;standalone=true';
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

        default:
            $this->showUploadApplet();
            break;
        }
    }


    /**
    * Show the file upload applet
    * @access private
    * @global array
    */
    private function showUploadApplet()
    {
        global $_ARRAYLANG;

        $objFWUser = FWUser::getFWUserObject();
        $lang = FWLanguage::getLanguageParameter($objFWUser->objUser->getBackendLanguage(), 'lang');
        if (!file_exists(ASCMS_MODULE_PATH.'/fileUploader/lib/lang/messages_'.$lang.'.zip')) {
            $lang = $this->defaultInterfaceLanguage;
        }
        $this->objTpl->loadTemplateFile('module_fileUploader_frame.html');
        $this->objTpl->setVariable(array(
            'TXT_FILEUPLOADER_CLOSE'        => $_ARRAYLANG['TXT_FILEUPLOADER_CLOSE'],
            'CONTREXX_CHARSET'              => CONTREXX_CHARSET,
            'FILEUPLOADER_APPLET_PATH'      => ASCMS_MODULE_WEB_PATH.'/fileUploader/lib/fileUploader.jar',
            'FILEUPLOADER_LANG_PATH'        => ASCMS_MODULE_WEB_PATH.'/fileUploader/lib/lang/messages_'.$lang.'.zip',
            'FILEUPLOADER_HANDLER_PATH'     => ASCMS_ADMIN_WEB_PATH.'/index.php'.$this->moduleURI.'&amp;act=upload&amp;type='.$this->mediaType.'&amp;path='.urlencode($this->path),
            'FILEUPLOADER_PARTITION_LENGTH' => $this->getPartitionLength()
        ));
        $this->objTpl->show();
    }
}

?>
