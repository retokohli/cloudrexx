<?php
/**
 * Once an Upload is approved, we get here.
 * This mainly delegates work to the uploader-classes.
 */
require_once ASCMS_CORE_MODULE_PATH.'/upload/share/uploadFactory.class.php';

class UploadLib
{
    //processes uploads sent by an uploader
    public function upload() 
    {
        //create the right upload handler...
        $uploader = UploadFactory::getInstance()->uploaderFromRequest();
        //...and let him do the work.
        $uploader->handleRequest();
    }

    //gets the uploader code as requested
    public function ajaxUploaderCode()
    {
        $uploader = UploadFactory::getInstance()->uploaderFromRequest();
        die($uploader->getXHtml());
    }

    public function formUploaderFrame() {
        //send the formuploader iframe content
        $uploader = UploadFactory::getInstance()->uploaderFromRequest('form');
        die($uploader->getFrameXHtml());
    }
    
    //show the upload finished page.
    public function formUploaderFrameFinished() {
        $uploader = UploadFactory::getInstance()->uploaderFromRequest('form');
        die($uploader->getFrameFinishedXHtml());        
    }

    //gets the current folder contents for a folderwidget
    public function refreshFolder()
    {
        $folderWidget = UploadFactory::getInstance()->folderWidgetFromRequest();
        die($folderWidget->getFilesJSON());
    }

    //deletes a file upon a folderWidget's request
    public function deleteFile() {
        $fw = UploadFactory::getInstance()->folderWidgetFromRequest();      
        $fw->delete($_REQUEST['file']);        
        die();
    }
}