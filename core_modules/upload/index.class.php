<?php
/**
 * Frontend Upload Tasks 
 */
require_once ASCMS_CORE_MODULE_PATH.'/upload/lib/uploadLib.class.php';

class Upload extends UploadLib
{
    public function getPage()
    {
        $act = '';
        if(isset($_REQUEST['cmd'])) {
            $act = $_REQUEST['cmd'];
        }
        if(isset($_REQUEST['act'])) {
            $act = $_REQUEST['act'];
        }

        switch($act) {
            //uploaders
            case 'upload': //an uploader is sending data
                $this->upload();
                break;
            case 'ajaxUploaderCode':
                $this->ajaxUploaderCode();
                break;
            //uploaders - formuploader
            case 'formUploaderFrame': //send the formuploader iframe content
                $this->formUploaderFrame();
                break;
            case 'formUploaderFrameFinished': //send the formuploader iframe content
                $this->formUploaderFrameFinished();
                break;
            //uploaders - jumploader
            case 'jumpUploaderApplet': //send the jumpUploader applet
                $this->jumpUploaderApplet();
                break;
            case 'jumpUploaderL10n': //send the jumpUploader messages
                $this->jumpUploaderL10n(basename($_GET['lang']));
                break;


            //folderWidget
            case 'refreshFolder':
                $this->refreshFolder();
                break;
            case 'deleteFile': //a folderWidget wants to delete something
                $this->deleteFile();
                break;
        }
    }
}