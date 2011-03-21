<?php
require_once ASCMS_CORE_MODULE_PATH.'/upload/lib/comboUploader.class.php';
/**
 * ExposedComboUploader - ComboUploader with JQuery expose
 */
class ExposedComboUploader extends ComboUploader
{
    public function __construct($backend) {
        parent::__construct($backend);
    }

    public function getXHtml(){
        global $_CORELANG;

        //back up instance name, we're going to set a temporary name for the combo uploader
        $instanceNameBak = $this->jsInstanceName;
        $this->jsInstanceName = 'exposedCombo_comboUploader_'.$this->uploadId;
        $comboXHtml = parent::getXHtml();
        $this->jsInstanceName = $instanceNameBak;

        $tpl = new HTML_Template_Sigma(ASCMS_CORE_MODULE_PATH.'/upload/template/uploaders');
        $tpl->setErrorHandling(PEAR_ERROR_DIE);
        
        $tpl->loadTemplateFile('exposedCombo.html');

        $tpl->setVariable(array(
            'COMBO_CODE' => $comboXHtml,
            'DIALOG_TITLE' => $_CORELANG['UPLOAD_EXPOSED_DIALOG_TITLE']
        ));

        //see Uploader::handleInstanceBusiness
        $this->handleInstanceBusiness($tpl,'exposedCombo');

        return $tpl->get();
    }
}
