<?php

/**
 * Calendar Class Host Manager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_calendar
 * @todo        Edit PHP DocBlocks!
 */

class CalendarFormManager extends CalendarLibrary {
    
    public $formList = array(); 
    
    private $arrInputfieldTypes = array(
        1  => 'inputtext',
        2  => 'textarea',
        3  => 'select',
        4  => 'radio',
        5  => 'checkbox',
        6  => 'mail',
        7  => 'seating',
        8  => 'agb',
        9  => 'salutation',
        10 => 'firstname',
        11 => 'lastname',
        12 => 'selectBillingAddress',
        13 => 'title',
    );
    private $arrInputfieldAffiliations = array(
        1  => 'form',
        2  => 'contact',
        3  => 'billing',
    );
    private $onlyActive;  
    
    function __construct($onlyActive=false){
        $this->onlyActive = $onlyActive;
    }
    
    function getFormList() {
        global $objDatabase,$_ARRAYLANG,$_LANGID;    
        
        $onlyActive_where = ($this->onlyActive == true ? ' WHERE status=1' : '');
        
        $query = "SELECT id AS id
                    FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_registration_form
                         ".$onlyActive_where."
                ORDER BY `order`";
        
        $objResult = $objDatabase->Execute($query);
        
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $objForm = new CalendarForm(intval($objResult->fields['id']));
                $this->formList[] = $objForm;   
                $objResult->MoveNext();
            }
        }
    }
    
    function showFormList($objTpl) 
    {
        global $objDatabase, $_ARRAYLANG;
        
        $i=0;
        foreach ($this->formList as $key => $objForm) {      
            $objTpl->setVariable(array(
                $this->moduleLangVar.'_FORM_ROW'           => $i%2==0 ? 'row1' : 'row2',
                $this->moduleLangVar.'_FORM_ID'            => $objForm->id,
                $this->moduleLangVar.'_FORM_STATUS'        => $objForm->status==0 ? 'red' : 'green',        
                $this->moduleLangVar.'_FORM_TITLE'         => $objForm->title,           
                $this->moduleLangVar.'_FORM_SORT'          => $objForm->sort,                       
            ));
            
            $i++;
            $objTpl->parse('formList');
        }
        
        if(count($this->formList) == 0) {
            $objTpl->hideBlock('formList');
        
            $objTpl->setVariable(array(
                'TXT_CALENDAR_NO_FORMS_FOUND' => $_ARRAYLANG['TXT_CALENDAR_NO_FORMS_FOUND'],
            ));
            
            $objTpl->parse('emptyFormList');
        }
    }
    
    function getFormDorpdown($selectedId=null) {
        global $_ARRAYLANG;
        
        parent::getSettings();
        $arrOptions = array();
        
        foreach ($this->formList as $key => $objForm) {       
            $arrOptions[$objForm->id] = $objForm->title;
        }      
        
        $options .= parent::buildDropdownmenu($arrOptions, $selectedId);
        
        return $options;
    }
    
    function showForm($objTpl, $formId, $intView, $arrNumSeating=array()) {
        global $objDatabase, $objInit, $_ARRAYLANG, $_LANGID;  
        
        if($formId != 0) {
            $objForm = new CalendarForm(intval($formId));
            $this->formList[$formId] = $objForm;            
            
            switch($intView) {
        	    case 1:                              
		            parent::getFrontendLanguages();
                    
                    $objTpl->setGlobalVariable(array(
                        $this->moduleLangVar.'_FORM_ID' => $objForm->id,
                        $this->moduleLangVar.'_FORM_TITLE' => $objForm->title,  
                    ));              
		            
		            $i=0;
		            foreach ($objForm->inputfields as $key => $arrInputfield) {
                        $i++;
                        $strSelectType = null;
		        	    $strSelectAffiliation = null;
		        	    
		        	    foreach ($this->arrInputfieldTypes as $id => $strType) {
		        		    $strSelected = $arrInputfield['type'] == $strType ? 'selected="selected"' : '';
		        		    $strSelectType .= '<option value="'.$strType.'" '.$strSelected.'>'.$_ARRAYLANG['TXT_CALENDAR_FORM_FIELD_'.strtoupper($strType)].'</option>';
		        	    }
                        
                        foreach ($this->arrInputfieldAffiliations as $id => $strAffiliation) {
                            $strSelected = $arrInputfield['affiliation'] == $strAffiliation ? 'selected="selected"' : '';
                            $strSelectAffiliation .= '<option value="'.$strAffiliation.'" '.$strSelected.'>'.$_ARRAYLANG['TXT_CALENDAR_FORM_FIELD_AFFILIATION_'.strtoupper($strAffiliation)].'</option>';
                        }
		        	    
                        $objTpl->setGlobalVariable(array(
                            $this->moduleLangVar.'_INPUTFIELD_ROW' => $i%2 == 0 ? 'row2' : 'row1',
                            $this->moduleLangVar.'_INPUTFIELD_ID' => $arrInputfield['id'],
                            $this->moduleLangVar.'_INPUTFIELD_ORDER' => $arrInputfield['order'],
                            $this->moduleLangVar.'_INPUTFIELD_NAME_MASTER' => $arrInputfield['name'][0],
                            $this->moduleLangVar.'_INPUTFIELD_DEFAULT_VALUE_MASTER' => $arrInputfield['default_value'][0],
                            $this->moduleLangVar.'_INPUTFIELD_REQUIRED' => $arrInputfield['required'] == 1 ? 'checked="checked"' : '',
                            $this->moduleLangVar.'_INPUTFIELD_TYPE' => $strSelectType,
                            $this->moduleLangVar.'_INPUTFIELD_AFFILIATION' => $strSelectAffiliation,
                        ));
		        	    
	                    foreach ($this->arrFrontendLanguages as $key => $arrLang) {
	                	    $objTpl->setVariable(array(
	                            $this->moduleLangVar.'_INPUTFIELD_LANG_ID'           => $arrLang['id'],
	                            'TXT_'.$this->moduleLangVar.'_INPUTFIELD_LANG_NAME'  => $arrLang['name'],
	                            $this->moduleLangVar.'_INPUTFIELD_LANG_SHORTCUT'     => $arrLang['lang'],
                                $this->moduleLangVar.'_INPUTFIELD_NAME'              => $arrInputfield['name'][$arrLang['id']],
	                        ));
	                        
	                        $objTpl->parse('inputfieldNameList');
	                    }
	                    
	                    foreach ($this->arrFrontendLanguages as $key => $arrLang) {
	                        $objTpl->setVariable(array(
	                            $this->moduleLangVar.'_INPUTFIELD_LANG_ID'           => $arrLang['id'],
	                            'TXT_'.$this->moduleLangVar.'_INPUTFIELD_LANG_NAME'  => $arrLang['name'],
	                            $this->moduleLangVar.'_INPUTFIELD_LANG_SHORTCUT'     => $arrLang['lang'],
                                $this->moduleLangVar.'_INPUTFIELD_DEFAULT_VALUE'     => $arrInputfield['default_value'][$arrLang['id']],
	                        ));
	            
	                        $objTpl->parse('inputfieldDefaultValueList');
	                    }
			            
	                    foreach ($this->arrFrontendLanguages as $key => $arrLang) {
	                        $objTpl->setVariable(array(
	                            'TXT_'.$this->moduleLangVar.'_INPUTFIELD_LANG_NAME'  => $arrLang['name'],
	                        ));
	            
	                        if(($key+1) == count($this->arrFrontendLanguages)) {
	                            $objTpl->setVariable(array(
	                                $this->moduleLangVar.'_MINIMIZE' =>  '<a href="javascript:ExpandMinimize(\'name\', '.$arrInputfield['id'].');ExpandMinimize(\'default_value\', '.$arrInputfield['id'].');ExpandMinimize(\'lang_name\', '.$arrInputfield['id'].');">&laquo;&nbsp;'.$_ARRAYLANG['TXT_CALENDAR_MINIMIZE'].'</a>',
	                            ));
	                        }
	                        
	                        $objTpl->parse('inputfieldLanguagesList');
	                    }
                            
                            if (count($this->arrFrontendLanguages) > 1) {
                                $objTpl->touchBlock('formFieldExpand');            
                            } else {
                                $objTpl->hideBlock('formFieldExpand');
                            }
                        $objTpl->parse('inputfieldList');
                    }
                                        
                    $objTpl->setGlobalVariable(array(
                        $this->moduleLangVar.'_INPUTFIELD_LAST_ID'  => $objForm->getLastInputfieldId(),
                        $this->moduleLangVar.'_INPUTFIELD_LAST_ROW' => $i%2 == 0 ? "'row2'" : "'row1'",
                        $this->moduleLangVar.'_DISPLAY_EXPAND'      => count($this->arrFrontendLanguages) > 1 ? "block" : "none",                        
                    ));
                    
		            
        		    break;
                case 2:
                    $objTpl->setVariable(array(             
                        $this->moduleLangVar.'_FORM_ID'             => $objForm->id,  
                        'TXT_'.$this->moduleLangVar.'_FIELD_NAME'   => '<label>'.$_ARRAYLANG['TXT_CALENDAR_TYPE'].'<font class="calendarRequired"> *</font></label>',
                        $this->moduleLangVar.'_FIELD_INPUT'         => '<select class="calendarSelect affiliateForm" name="registrationType"><option value="1" selected="selected"/>'.$_ARRAYLANG['TXT_CALENDAR_REG_REGISTRATION'].'</option><option value="0"/>'.$_ARRAYLANG['TXT_CALENDAR_REG_SIGNOFF'].'</option></select>',      
                        $this->moduleLangVar.'_FIELD_CLASS' => 'affiliationForm',   
                    ));  
                        
                    $objTpl->parse('calendarRegistrationField');
                
                    $selectBillingAddressStatus = false;
                
                    foreach ($objForm->inputfields as $key => $arrInputfield) { 
                        $options = array();
                        $options = explode(',', $arrInputfield['default_value'][$_LANGID]);
                        $inputfield = null;
                        
                        if(isset($_POST['registrationField'][$arrInputfield['id']])) {
                            $value = $_POST['registrationField'][$arrInputfield['id']];
                        } else {
                            $value = $arrInputfield['default_value'][$_LANGID];
                        }  
                        
                        $affiliationClass = 'affiliation'.ucfirst($arrInputfield['affiliation']);
                        
                        switch($arrInputfield['type']) {
                            case 'inputtext':     
                            case 'mail':
                            case 'firstname':
                            case 'lastname':
                                $inputfield = '<input type="text" class="calendarInputText" name="registrationField['.$arrInputfield['id'].']" value="'.$value.'" /> ';
                                break;
                            case 'textarea':
                                $inputfield = '<textarea class="calendarTextarea" name="registrationField['.$arrInputfield['id'].']">'.$value.'</textarea>';
                                break ;
                            case 'select':
                            case 'salutation':
                                $inputfield = '<select class="calendarSelect" name="registrationField['.$arrInputfield['id'].']">';
                                $selected =  empty($_POST) ? 'selected="selected"' : '';  
                                $inputfield .= '<option value="" '.$selected.'>'.$_ARRAYLANG['TXT_CALENDAR_PLEASE_CHOOSE'].'</option>';    
                                
                                foreach($options as $key => $name)  {
                                    $selected =  ($key+1 == $value)  ? 'selected="selected"' : '';        
                                    $inputfield .= '<option value="'.intval($key+1).'" '.$selected.'>'.$name.'</option>';       
                                }
                                
                                $inputfield .= '</select>'; 
                                break;
                             case 'radio':                                             
                                foreach($options as $key => $name)  { 
                                    $checked =  ($key+1 == $value) || (empty($_POST) && $key == 0) ? 'checked="checked"' : '';     
                                    
                                    $textfield = '<input type="text" class="calendarInputCheckboxAdditional" name="registrationFieldAdditional['.$arrInputfield['id'].']['.$key.']" />';
                                    $name = str_replace('[[INPUT]]', $textfield, $name);
                                    
                                    $inputfield .= '<input type="radio" class="calendarInputCheckbox" name="registrationField['.$arrInputfield['id'].']" value="'.intval($key+1).'" '.$checked.'/>&nbsp;'.$name.'<br />';  
                                }
                                break;
                             case 'checkbox':       
                                foreach($options as $key => $name)  {    
                                    $textfield = '<input type="text" class="calendarInputCheckboxAdditional" name="registrationFieldAdditional['.$arrInputfield['id'].']['.$key.']" />';
                                    $name = str_replace('[[INPUT]]', $textfield, $name);
                                    
                                    $checked =  (in_array($key+1, $_POST['registrationField'][$arrInputfield['id']]))  ? 'checked="checked"' : '';       
                                    $inputfield .= '<input '.$checked.' type="checkbox" class="calendarInputCheckbox" name="registrationField['.$arrInputfield['id'].'][]" value="'.intval($key+1).'" />&nbsp;'.$name.'<br />';  
                                }
                                break;
                            case 'seating':
                                if (count($arrNumSeating)>0 && $arrNumSeating[0] != "") {
                                    $inputfield = '<select class="calendarSelect" name="registrationField['.$arrInputfield['id'].']">';
                                    foreach ($arrNumSeating as $intNumSeating) {
                                        $selected    = $intNumSeating == $value ? 'selected="selected"' : '';
                                        $inputfield .= '<option value="'.$intNumSeating.'" '.$selected.'>'.$intNumSeating.'</option>';
                                    }
                                    $inputfield .= '</select>';
                                } else {
                                    $hide = true;
                                }
                                break;
                            case 'agb':
                                $inputfield = '<input class="calendarInputCheckbox" type="checkbox" name="registrationField['.$arrInputfield['id'].'][]" value="1" />&nbsp;'.$_ARRAYLANG['TXT_CALENDAR_AGB'].'<br />';
                                break;
                            case 'selectBillingAddress':
                                if(!$selectBillingAddressStatus) {
                                    if($_REQUEST['registrationField'][$arrInputfield['id']] == 'deviatesFromContact') {
                                        $selectDeviatesFromContact = 'selected="selected"';
                                    } else {
                                        $selectDeviatesFromContact = '';
                                    }
                                    
                                    $inputfield = '<select id="calendarSelectBillingAddress" class="calendarSelect" name="registrationField['.$arrInputfield['id'].']">';
                                    $inputfield .= '<option value="sameAsContact">'.$_ARRAYLANG['TXT_CALENDAR_SAME_AS_CONTACT'].'</option>';    
                                    $inputfield .= '<option value="deviatesFromContact" '.$selectDeviatesFromContact.'>'.$_ARRAYLANG['TXT_CALENDAR_DEVIATES_FROM_CONTACT'].'</option>';    
                                    $inputfield .= '</select>'; 
                                    $selectBillingAddressStatus = true;
                                } 
                                break;
                            case 'title':
                                $inputfield = null;
                                break;
                        }
                        
                        if($arrInputfield['type'] == 'title') {
                            $label = '<h2>'.$arrInputfield['name'][$_LANGID].'</h2>';
                        } else {
                            $required = $arrInputfield['required'] == 1 ? '<font class="calendarRequired"> *</font>' : '';
                            $label = '<label>'.$arrInputfield['name'][$_LANGID].$required.'</label>';
                        }

                        if(!$hide) {
                            $objTpl->setVariable(array(
                                'TXT_'.$this->moduleLangVar.'_FIELD_NAME' => $label,
                                $this->moduleLangVar.'_FIELD_INPUT' => $inputfield,
                                $this->moduleLangVar.'_FIELD_CLASS' => $affiliationClass,
                            ));
                        }
                        $objTpl->parse('calendarRegistrationField');
                    }
                    break;
            }
        } else {                     
            switch($intView) {
                case 1:                              
                    parent::getFrontendLanguages();
                    
                    $objTpl->setGlobalVariable(array(
                        $this->moduleLangVar.'_FORM_ID' => '',
                        $this->moduleLangVar.'_FORM_TITLE' => '',  
                    ));
                    
                    $query = "SELECT id
                                FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_registration_form_field 
                            ORDER BY id DESC
                               LIMIT 1";
                    
                    $objResult = $objDatabase->Execute($query);    
                    
                    $objTpl->setVariable(array(
                        $this->moduleLangVar.'_INPUTFIELD_LAST_ID'  => intval($objResult->fields['id']),
                        $this->moduleLangVar.'_INPUTFIELD_LAST_ROW' => "'row2'",
                        $this->moduleLangVar.'_DISPLAY_EXPAND'      => count($this->arrFrontendLanguages) > 1 ? "block" : "none",
                    ));
                    
                    break;
            }
        }
    }
}