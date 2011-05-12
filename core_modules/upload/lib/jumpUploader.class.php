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
      global $objInit;
      $uploadPath = $this->getUploadPath('jump');

      $tpl = new HTML_Template_Sigma(ASCMS_CORE_MODULE_PATH.'/upload/template/uploaders');
      $tpl->setErrorHandling(PEAR_ERROR_DIE);
      
      $tpl->loadTemplateFile('jump.html');

      $basePath = 'index.php?';
      $basePath .= ($this->isBackendRequest ? 'cmd=upload&act' : 'section=upload&cmd'); //act and cmd vary 
      $appletPath = $basePath.'=jumpUploaderApplet';
      $l10nPath = $basePath.'=jumpUploaderL10n';

      $langId;
      if(!$this->isBackendRequest)
          $langId = $objInit->getFrontendLangId();
      else //backend
          $langId = $objInit->getBackendLangId();
      $langCode = FWLanguage::getLanguageCodeById($langId);
      if (!file_exists(ASCMS_CORE_MODULE_PATH.'/upload/ressources/uploaders/jump/messages_'.$langCode.'.zip')) {
          $langCode = 'en';
      }
      $l10nPath .= '&lang='.$langCode;

      require_once ASCMS_FRAMEWORK_PATH.'/System.class.php';
      $tpl->setVariable('CHUNK_LENGTH', FWSystem::getMaxUploadFileSize()-1000);
      $tpl->setVariable('APPLET_URL', $appletPath);
      $tpl->setVariable('LANG_URL', $l10nPath);
      $tpl->setVariable('UPLOAD_URL', $uploadPath);
      
      return $tpl->get();
    }
}