<?php
require_once ASCMS_CORE_MODULE_PATH.'/upload/lib/uploader.class.php';
/**
 * FormUploader - Class for upload via HTML input-tags.
 */
class FormUploader extends Uploader
{
    /**
     * @override
     */     
    public function handleRequest()
    {
        global $sessionObj;
        global $_FILES;

        //get a writable directory
        $targetDir = $sessionObj->getTempPath().'/upload_'.$this->uploadId;
        @mkdir($targetDir);
        
        //move all uploaded file to this upload's temp directory
        foreach($_FILES["uploaderFiles"]["error"] as $key => $error) {
            if($error == UPLOAD_ERR_OK) {
                $tmpName = $_FILES["uploaderFiles"]["tmp_name"][$key];
                $name = $_FILES["uploaderFiles"]["name"][$key];
                @move_uploaded_file($tmpName,$targetDir.'/'.$name);
            }
        }

        //and call back.
        $this->notifyCallback();
        //redirect the user where he belongs
        $this->redirect();
    }

    /**
     * @override
     */     
    public function getXHtml()
    {
        $iframeUrl = '';   
        if($this->isBackendRequest)
            $iframeUrl = ASCMS_ADMIN_WEB_PATH.'/index.php?cmd=upload&act=formUploaderFrame&uploadId='.$this->uploadId;
        else
            $iframeUrl = ASCMS_PATH_OFFSET.'/index.php?section=upload&cmd=formUploaderFrame&uploadId='.$this->uploadId;
      
        $tpl = new HTML_Template_Sigma(ASCMS_CORE_MODULE_PATH.'/upload/template/uploaders');
        $tpl->setErrorHandling(PEAR_ERROR_DIE);

        $tpl->loadTemplateFile('form.html');
        $tpl->setVariable('IFRAME_URL', $iframeUrl);
        
        return $tpl->get();
    }

    public function getFrameXHtml() {
        //JS / CSS dependencies
        JS::activate('cx');
        JS::registerCSS('core_modules/upload/css/uploaders/form/formUploader.css');
        JS::registerJS('core_modules/upload/js/uploaders/form/formUploader.js');
        
        $uploadPath = $this->getUploadPath('form');

        $redirectUrl = '';
        $redirectUrl = '';   
        if($this->isBackendRequest)
            $redirectUrl = ASCMS_ADMIN_WEB_PATH.'/index.php?cmd=upload&act=formUploaderFrameFinished&uploadId='.$this->uploadId;
        else
            $redirectUrl = ASCMS_PATH_OFFSET.'/index.php?section=upload&cmd=formUploaderFrameFinished&uploadId='.$this->uploadId;
        $this->setRedirectUrl($redirectUrl);
      
        $tpl = new HTML_Template_Sigma(ASCMS_CORE_MODULE_PATH.'/upload/template/uploaders');
        $tpl->setErrorHandling(PEAR_ERROR_DIE);
        
        $tpl->loadTemplateFile('formFrame.html');
        $tpl->setVariable('UPLOAD_URL', $uploadPath);
        $tpl->setVariable('INCLUDES', JS::getCode());
        $tpl->setVariable('CXJS_INIT_JS', ContrexxJavascript::getInstance()->initJs());
        
        require_once ASCMS_FRAMEWORK_PATH.'/System.class.php';
        $tpl->setVariable('MAX_FILE_SIZE', FWSystem::getMaxUploadFileSize()-1000);
        
        return $tpl->get();
    }

    public function getFrameFinishedXHtml() {
        global $_CORELANG;
        $tpl = new HTML_Template_Sigma(ASCMS_CORE_MODULE_PATH.'/upload/template/uploaders');
        $tpl->setErrorHandling(PEAR_ERROR_DIE);

        $tpl->loadTemplateFile('formFrameFinished.html');

        $tpl->setVariable('FINISHED_MESSAGE',htmlentities($_CORELANG['UPLOAD_FINISHED'], ENT_QUOTES, CONTREXX_CHARSET));        
       
        return $tpl->get();
    }
}