<?php
require_once ASCMS_CORE_MODULE_PATH.'/upload/lib/uploader.class.php';
/**
 * ComboUploader - Displays a FormUploader and possibilities to invoke other types of Uploaders.
 */
class ComboUploader extends Uploader
{
    /**
     * @var array ( 'first_uploader_type', 'another_type', ... )
     */
    private $enabledUploaders = array();

    public function __construct($backend)
    {
        parent::__construct($backend);
    }

    /**
     * Which uploaders should I display?
     * @param $uploaders array e.g. ('pl','form')
     */
    public function setEnabledUploaders($uploaders)
    {
        $this->enabledUploaders = $uploaders;
    }

    /**
     * @override
     */     
    public function handleRequest()
    {
        //we do not care. requests are handled by the respective uploader instances
    }

    /**
     * @override
     */     
    public function getXHtml()
    {
        global $_CORELANG;

        //JS / CSS dependencies
        JS::activate('cx');
        JS::registerJS('lib/javascript/swfobject.js');
        JS::registerJS('lib/javascript/deployJava.js');
        JS::registerJS('core_modules/upload/js/uploaders/combo/combo.js');
        JS::registerCSS('core_modules/upload/css/uploaders/combo/combo.css');

        require_once ASCMS_CORE_MODULE_PATH.'/upload/share/uploadFactory.class.php';
        $formUploader = UploadFactory::getInstance()->newUploader('form',$this->uploadId);

        //i18n of uploader descriptions
        $formUploaderDescription = $_CORELANG['FORM_UPLOADER'];
        $plUploaderDescription = $_CORELANG['PL_UPLOADER'];
        $jumpUploaderDescription = $_CORELANG['JUMP_UPLOADER'];
        $alternativesCaption = $_CORELANG['OTHER_UPLOADERS'];

        //combuploader js config: available uploaders
        $uploaders = array("{type:'form',description:'".$formUploaderDescription."'}");
        if(in_array('pl',$this->enabledUploaders))
            array_push($uploaders,"{type:'pl',description:'".$plUploaderDescription."'}");
        if(in_array('jump', $this->enabledUploaders))
            array_push($uploaders,"{type:'jump',description:'".$jumpUploaderDescription."'}");

        $uploaders = '['.join(',',$uploaders).']';

        $cmdOrSection = $this->isBackendRequest ? 'cmd' : 'section';
        $actOrCmd = $this->isBackendRequest ? 'act' : 'cmd';
        //from where the combouploader gets the code on an uploader switch
        $switchUrl;
        //from where the combouploader gets the response for finished uploads
        $responseUrl;
        if($this->isBackendRequest) {
            $switchUrl = ASCMS_ADMIN_WEB_PATH.'/index.php?'.$cmdOrSection.'=upload&'.$actOrCmd.'=ajaxUploaderCode';
            $responseUrl = ASCMS_ADMIN_WEB_PATH.'/index.php?'.$cmdOrSection.'=upload&'.$actOrCmd.'=response';
        }
        else {
            $switchUrl = CONTREXX_SCRIPT_PATH.'?'.$cmdOrSection.'=upload&'.$actOrCmd.'=ajaxUploaderCode';
            $responseUrl = CONTREXX_SCRIPT_PATH.'?'.$cmdOrSection.'=upload&'.$actOrCmd.'=response';
        }
                
        $tpl = new HTML_Template_Sigma(ASCMS_CORE_MODULE_PATH.'/upload/template/uploaders');
        $tpl->setErrorHandling(PEAR_ERROR_DIE);
        
        $tpl->loadTemplateFile('combo.html');

        $tpl->setVariable(array(
             'CONFIG_UPLOADERS_JS' => $uploaders,
             'RESPONSE_URL' => $responseUrl,
             'UPLOAD_ID' => $this->uploadId,
             'SWITCH_URL' => $switchUrl,
             'OTHER_UPLOADERS_CAPTION' => $_CORELANG['OTHER_UPLOADERS'],
             'TXT_CORE_UPLOAD_MORE' => $_CORELANG['TXT_CORE_UPLOAD_MORE'],
             'TXT_CORE_FINISH_UPLOADING' => $_CORELANG['TXT_CORE_FINISH_UPLOADING'],
             'TXT_CORE_FILES_UPLOADED' => $_CORELANG['TXT_CORE_FILES_UPLOADED']
        ));

        $tpl->setVariable('UPLOADER_CODE', $formUploader->getXHtml());

        //see Uploader::handleInstanceBusiness
        $this->handleInstanceBusiness($tpl,'cu');
        
        return $tpl->get();
    }
}