<?php
/**
 * Printshop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation AG <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_printshop
 */
error_reporting(E_ALL);ini_set('display_errors',1);
//$objDatabase->debug=1;

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH.'/printshop/lib/printshopLib.class.php';

/**
 * PrintshopAdmin
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation AG <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_printshop
 */
class PrintshopAdmin extends PrintshopLibrary {

    var $_objTpl;
    var $_strPageTitle  = '';
    var $_strErrMessage = '';
    var $_strOkMessage  = '';

    /**
    * Constructor   -> Create the module-menu and an internal template-object
    * @global   InitCMS
    * @global   HTML_Template_Sigma
    * @global   array
    */
    function __construct()
    {
        global $objInit, $objTemplate, $_ARRAYLANG;
        parent::__construct();
        $this->_objTpl = &new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/printshop/template');
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $this->_intLanguageId = $objInit->userFrontendLangId;

        $objFWUser = FWUser::getFWUserObject();
        $this->_intCurrentUserId = $objFWUser->objUser->getId();
        $objTemplate->setVariable('CONTENT_NAVIGATION','    <a href="?cmd=printshop">'.$_ARRAYLANG['TXT_PRINTSHOP_OVERVIEW'].'</a>
                                                            <a href="?cmd=printshop&amp;act=type">'.$_ARRAYLANG['TXT_PRINTSHOP_TYPE_TITLE'].'</a>
                                                            <a href="?cmd=printshop&amp;act=format">'.$_ARRAYLANG['TXT_PRINTSHOP_FORMAT_TITLE'].'</a>
                                                            <a href="?cmd=printshop&amp;act=front">'.$_ARRAYLANG['TXT_PRINTSHOP_FRONT_TITLE'].'</a>
                                                            <a href="?cmd=printshop&amp;act=back">'.$_ARRAYLANG['TXT_PRINTSHOP_BACK_TITLE'].'</a>
                                                            <a href="?cmd=printshop&amp;act=weight">'.$_ARRAYLANG['TXT_PRINTSHOP_WEIGHT_TITLE'].'</a>
                                                            <a href="?cmd=printshop&amp;act=paper">'.$_ARRAYLANG['TXT_PRINTSHOP_PAPER_TITLE'].'</a>
                                                            <a href="?cmd=printshop&amp;act=settings">'.$_ARRAYLANG['TXT_PRINTSHOP_SETTINGS_TITLE'].'</a>
                                                    ');
    }


    /**
    * Perform the right operation depending on the $_GET-params
    *
    * @global   HTML_Template_Sigma
    */
    function getPage() {
        global $objTemplate;

        if(!isset($_GET['act'])) {
            $_GET['act'] = '';
        }

        switch($_GET['act']){
            case 'settings':
                $this->showSettings();
            break;

            case 'saveSettings':
                Permission::checkAccess(150, 'static');
                $this->saveSettings();
                $this->showSettings();
            break;
            case 'type':
            case 'format':
            case 'front':
            case 'back':
            case 'weight':
            case 'paper':
                Permission::checkAccess(150, 'static');

                $attribute = contrexx_addslashes($_GET['act']);
                if(!empty($_POST['json'])){
                    switch($_POST['json']){
                        case 'save':
                            $arrEntry = $this->editAttribute($attribute, intval($_POST['id']));
                            die(json_encode(array(
                                'id' => $arrEntry['id'],
                                'name' => $arrEntry['name'],
                                'exists' => $arrEntry['exists']
                            )));
                        break;
                        case 'delete':
                            $message = $this->deleteAttribute($attribute, intval($_POST['id']));
                            die(json_encode(array('message' => $message)));
                        break;
                        default:
                    }
                }
                $this->showAttributes($attribute);
            break;

            default:
                Permission::checkAccess(150, 'static');
                $this->showOverview();
        }

        $objTemplate->setVariable(array(
            'CONTENT_TITLE'             => $this->_strPageTitle,
            'CONTENT_OK_MESSAGE'        => $this->_strOkMessage,
            'CONTENT_STATUS_MESSAGE'    => $this->_strErrMessage,
            'ADMIN_CONTENT'             => $this->_objTpl->get()
        ));
    }


    /**
     * Shows the print item overview
     *
     * @global  array
     * @global  array
     */
    function showOverview() {
        global $_CORELANG, $_ARRAYLANG;

        $this->_strPageTitle = $_CORELANG['TXT_PRINTSHOP_OVERVIEW'];
        $this->_objTpl->loadTemplateFile('module_printshop_main.html', true, true);

        $arrEntries = $this->_getEntries();


        foreach ($this->_availableAttributes as $attribute) {
            $this->_objTpl->setVariable(array(
                'ENTRY_'.strtoupper($attribute).'_DD' => $_ARRAYLANG['TXT_PRINTSHOP_'.strtoupper($attribute).'_TITLE']." ".$this->createAttribtueDropDown($attribute),
            ));
        }

        if(count($arrEntries) > 0){
            foreach ($arrEntries as $arrEntry) {
                $this->_objTpl->setVariable(array(
                    'TXT_ADD_SUBMIT'                    =>  $_CORELANG['TXT_SAVE']
                ));

                $this->_objTpl->parse('showEntry');
            }
        }
    }


    /**
     * removes attribute if not used by any products anymore
     *
     * @param string $attribute
     * @param int $id
     * @return string
     */
    function deleteAttribute($attribute, $id){
        global $objDatabase, $_ARRAYLANG;
$objDatabase->debug=0;
        if(!$this->_isValidAtrribute($attribute)){
            return false;
        }
        $message = 'ok';
        $query = 'SELECT 1 FROM `'.DBPREFIX.'module_printshop_product`
                  WHERE  `'.$attribute.'`='.$id;
        $objRS = $objDatabase->SelectLimit($query, 1);
        $attributeName = $this->_getAttributeName($attribute, $id);
        if($objRS->RecordCount() > 0){
            $message = sprintf($_ARRAYLANG['TXT_PRINTSHOP_ATTRIBUTE_STILL_USED'], $attributeName);
        }else{
            $query = 'DELETE FROM `'.DBPREFIX.'module_printshop_'.$attribute.'`
                      WHERE `id` = '.$id;
            $objDatabase->Execute($query);
        }
        return $message;
    }


    /**
     * edit the print attributes
     *
     * @param string $attribute
     * @param int $id
     * @return array
     */
    function editAttribute($attribute, $id){
        global $objDatabase;
$objDatabase->debug=0;

        if(!$this->_isValidAtrribute($attribute)){
            return false;
        }
        $attributeValue = contrexx_addslashes(trim($_POST['ps_attribute_name']));

        $query = 'SELECT `id` FROM `'.DBPREFIX.'module_printshop_'.$attribute.'`
                  WHERE `'.$attribute."`='$attributeValue'";
        $objRS = $objDatabase->SelectLimit($query, 1);

        if($objRS->RecordCount() > 0){
            $alreadyExists = true;
            if($id == 0){
                $id = $objRS->fields['id'];
            }else{
                return array('id' => $id, 'name' => $attributeValue, 'exists' => $alreadyExists);
            }
        }else{
            $alreadyExists = false;
            if($id == 0){
                $id = 'NULL';
            }
        }

        $query = 'INSERT INTO `'.DBPREFIX.'module_printshop_'.$attribute.'` (`id`, `'.$attribute."`)
                    VALUES ($id, '$attributeValue')
                  ON DUPLICATE KEY
                    UPDATE  `id`         = $id,
                            `$attribute` = '".$attributeValue."'";
        $objDatabase->Execute($query);
        $insertID = $objDatabase->Insert_ID();
        if($insertID > 0 ){
            $id = $insertID;
        }
        return array('id' => $id, 'name' => $attributeValue, 'exists' => $alreadyExists);
    }

    /**
     * Show the print attributes
     *
     * @param string $attribute
     */
    function showAttributes($attribute){
        global $_ARRAYLANG, $_CORELANG;
        $this->_strPageTitle = $_ARRAYLANG['TXT_PRINTSHOP_SETTINGS_TITLE'];
        $this->_objTpl->loadTemplateFile('module_printshop_attributes.html', true, true);

        $this->_objTpl->setGlobalVariable(array(
            'TXT_PRINTSHOP_ATTRIBUTE'               => $_ARRAYLANG['TXT_PRINTSHOP_'.strtoupper($attribute).'_TITLE'],
            'PRINTSHOP_ATTRIBUTE'                   => $attribute,
            'TXT_PRINTSHOP_NO_ENTRY'                => $_ARRAYLANG['TXT_PRINTSHOP_NO_ENTRY'],
            'TXT_PRINTSHOP_ADD_ATTRIBUTE'           => $_ARRAYLANG['TXT_PRINTSHOP_ADD_ATTRIBUTE'],
            'TXT_PRINTSHOP_UPDATE_ATTRIBUTE'        => $_ARRAYLANG['TXT_PRINTSHOP_UPDATE_ATTRIBUTE'],
            'TXT_PRINTSHOP_ENTER_ATTRIBUTE'         => $_ARRAYLANG['TXT_PRINTSHOP_ENTER_ATTRIBUTE'],

            'TXT_PRINTSHOP_ATTRIBUTE_NAME'          => $_ARRAYLANG['TXT_PRINTSHOP_ATTRIBUTE_NAME'],
            'TXT_PRINTSHOP_SUBTITLE_ACTIONS'        => $_ARRAYLANG['TXT_PRINTSHOP_SUBTITLE_ACTIONS'],
            'TXT_PRINTSHOP_DELETE_ENTRY'            => $_ARRAYLANG['TXT_PRINTSHOP_DELETE_ENTRY'],
            'TXT_PRINTSHOP_DELETE_SELECTED'         => $_ARRAYLANG['TXT_PRINTSHOP_DELETE_SELECTED'],
            'TXT_PRINTSHOP_ALREADY_EXISTS'          => $_ARRAYLANG['TXT_PRINTSHOP_ALREADY_EXISTS'],

            'TXT_SELECT_ALL'                        => $_CORELANG['TXT_SELECT_ALL'],
            'TXT_DESELECT_ALL'                      => $_CORELANG['TXT_DESELECT_ALL'],
            'TXT_MULTISELECT_SELECT'                => $_CORELANG['TXT_MULTISELECT_SELECT'],
            'TXT_MULTISELECT_DELETE'                => $_CORELANG['TXT_MULTISELECT_DELETE'],
            'DIRECTORY_INDEX'                       => CONTREXX_DIRECTORY_INDEX,
        ));

        $arrAttributes = $this->_getAttributes($attribute);

        if($arrAttributes){
            foreach ($arrAttributes as $index => $arrAttribute) {

                $this->_objTpl->setVariable(array(
                    'ENTRY_ROWCLASS'            => $index % 2 ? 'row2' : 'row1',
                    'PRINTSHOP_ATTRIBUTE_ID'    => $arrAttribute['id'],
                    'PRINTSHOP_ATTRIBUTE_NAME'  => $arrAttribute['name'],
                ));
                $this->_objTpl->parse('showEntry');
            }
        }else{
            $this->_objTpl->touchBlock('noEntry');
        }
    }


    /**
     * Shows the settings-page of the printshop-module.
     *
     * @global  array
     * @global  array
     */
    function showSettings() {
        global $_CORELANG, $_ARRAYLANG;

        $this->_strPageTitle = $_CORELANG['TXT_PRINTSHOP_SETTINGS_TITLE'];
        $this->_objTpl->loadTemplateFile('module_printshop_settings.html', true, true);

        $this->_objTpl->setVariable(array(
            'TXT_PRINTSHOP_SETTINGS_TITLE'      => $_ARRAYLANG['TXT_PRINTSHOP_SETTINGS_TITLE'],
            'TXT_PRINTSHOP_EMAIL_HELP'          => $_ARRAYLANG['TXT_PRINTSHOP_EMAIL_HELP'],
            'TXT_PRINTSHOP_ORDER_EMAIL'         => $_ARRAYLANG['TXT_PRINTSHOP_ORDER_EMAIL'],
            'TXT_SAVE'                          => $_CORELANG['TXT_SAVE']
        ));

        $this->_objTpl->setVariable(array(
            'PRINTSHOP_SETTINGS_ORDER_EMAIL'    => $this->_arrSettings['orderEmail'],
        ));
    }



    /**
     * Validate and save the settings from $_POST into the database.
     *
     * @global  ADONewConnection
     * @global  array
     */
    function saveSettings() {
        global $objDatabase, $_ARRAYLANG;


        if(!empty($_POST['save_settings']) && !empty($_POST['orderEmail'])) {
            $objDatabase->Execute(' UPDATE '.DBPREFIX.'module_printshop_settings
                                    SET `value` = "'.contrexx_addslashes($_POST['orderEmail']).'"
                                    WHERE `name` = "orderEmail"
                                ');
        }

        $this->_arrSettings = $this->createSettingsArray();

        $this->_strOkMessage = $_ARRAYLANG['TXT_PRINTSHOP_SETTINGS_SAVE_SUCCESSFULL'];
    }
}
