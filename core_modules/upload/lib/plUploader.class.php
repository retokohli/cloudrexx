<?php
require_once ASCMS_CORE_MODULE_PATH.'/upload/lib/uploader.class.php';
/**
 * PlUploader - Flash uploader class.
 */
class PlUploader extends Uploader
{
    /**
     * @override
     */     
    public function handleRequest()
    {    
        // HTTP headers for no cache etc
        header('Content-type: text/plain; charset=UTF-8');
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        // Get parameters
        $chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
        $chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
        $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
        
        if (!FWValidator::is_file_ending_harmless($fileName)) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "'.sprintf('The file %s was refused due to its file extension which is not allowed!', htmlentities($fileName, ENT_QUOTES, CONTREXX_CHARSET)).'"}, "id" : "id"}');
        }

        try {
            $this->addChunk($fileName, $chunk, $chunks);
        }
        catch (UploaderException $e) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "'.$e->getMessage().'"}, "id" : "id"}');
        }

        if($chunk == $chunks-1) //upload finished
            $this->notifyCallback();

        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
    }

    /**
     * @override
     */     
    public function getXHtml()
    {
      global $_CORELANG;
      // CSS dependencies
      JS::activate('cx');

      $uploadPath = $this->getUploadPath('pl');

      $tpl = new HTML_Template_Sigma(ASCMS_CORE_MODULE_PATH.'/upload/template/uploaders');
      $tpl->setErrorHandling(PEAR_ERROR_DIE);
      
      $tpl->loadTemplateFile('pl.html');
      $tpl->setVariable('FLASH_URL', ASCMS_CORE_MODULE_WEB_PATH.'/upload/ressources/uploaders/pl/plupload.flash.swf');
      $tpl->setVariable('UPLOAD_URL', $uploadPath);
      
      //I18N
      $tpl->setVariable(array(
          'UPLOAD' => $_CORELANG['UPLOAD'],
          'OTHER_UPLOADERS' => $_CORELANG['OTHER_UPLOADERS'],
          'FORM_UPLOADER' => $_CORELANG['FORM_UPLOADER'],
          'PL_UPLOADER' => $_CORELANG['PL_UPLOADER'],
          'JUMP_UPLOADER' => $_CORELANG['JUMP_UPLOADER'],

          'SELECT_FILES' => $_CORELANG['SELECT_FILES'],
          'ADD_INSTRUCTIONS' => $_CORELANG['ADD_INSTRUCTIONS'],
          'FILENAME' => $_CORELANG['FILENAME'],
          'STATUS' => $_CORELANG['STATUS'],
          'SIZE' => $_CORELANG['SIZE'],
          'ADD_FILES' => $_CORELANG['ADD_FILES'],

          'STOP_CURRENT_UPLOAD' => $_CORELANG['STOP_CURRENT_UPLOAD'],
          'DRAG_FILES_HERE' => $_CORELANG['DRAG_FILES_HERE']
      ));
      
      return $tpl->get();
    }
}
