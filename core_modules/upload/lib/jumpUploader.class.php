<?php
require_once ASCMS_CORE_MODULE_PATH.'/upload/lib/uploader.class.php';
/**
 * PlUploader - Flash uploader class.
 */
class JumpUploader extends Uploader
{
    /**
     * @override
     */     
    public function handleRequest()
    {    
        // Get parameters
        $chunk = $_POST['partitionIndex'];
        $chunks = $_POST['partitionCount'];
        $fileName = contrexx_stripslashes($_FILES['file']['name']);


        // check if the file has a valid file extension
        if (!FWValidator::is_file_ending_harmless($fileName)) {
            die('Error:'.sprintf('The file %s was refused due to its file extension which is not allowed!', htmlentities($fileName, ENT_QUOTES, CONTREXX_CHARSET)));
        }

        try {
            $this->addChunk($fileName, $chunk, $chunks);
        }
        catch (UploaderException $e) {
            die('Error:'.$e->getMessage());
        }
        if($chunk == $chunks-1) //upload finished
            $this->notifyCallback();

        die(0); 
    }

    /**
     * @override
     */     
    public function getXHtml($backend = false)
    {
      $uploadPath = $this->getUploadPath('jump');

      $tpl = new HTML_Template_Sigma(ASCMS_CORE_MODULE_PATH.'/upload/template/uploaders');
      $tpl->setErrorHandling(PEAR_ERROR_DIE);
      
      $tpl->loadTemplateFile('jump.html');

      //load correct language file
      $objFWUser = FWUser::getFWUserObject();
      $lang = FWLanguage::getLanguageParameter($objFWUser->objUser->getBackendLanguage(), 'lang');
      if (!file_exists(ASCMS_CORE_MODULE_WEB_PATH.'/upload/ressources/uploaders/jump/messages_'.$lang.'.zip')) {
          $lang = $this->defaultInterfaceLanguage;
      }

      require_once ASCMS_FRAMEWORK_PATH.'/System.class.php';
      $tpl->setVariable('CHUNK_LENGTH', FWSystem::getMaxUploadFileSize()-1000);
      $tpl->setVariable('APPLET_URL', ASCMS_CORE_MODULE_WEB_PATH.'/upload/ressources/uploaders/jump/jumpLoader.jar');
      $tpl->setVariable('LANG_URL', ASCMS_CORE_MODULE_WEB_PATH.'/upload/ressources/uploaders/jump/jumpLoader.jar');
      $tpl->setVariable('UPLOAD_URL', $uploadPath);
      
      return $tpl->get();
    }
}