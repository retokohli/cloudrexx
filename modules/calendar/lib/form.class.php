<?php
/**
 * Calendar Class Host
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_calendar
 * @todo        Edit PHP DocBlocks!
 */

class CalendarForm extends CalendarLibrary
{
    public $id;    
    public $title;            
    public $status;
    public $sort;
    public $inputfields = array();
    
    
    function __construct($id=null){
        if($id != null) {
            self::get($id);
        }
    }
    
    function get($formId) {
        global $objDatabase, $_LANGID;  
        
        parent::getFrontendLanguages();
        
        $this->id = intval($formId);
        
        $query = "SELECT id,title,status,`order`
                    FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_registration_form
                   WHERE id = '".intval($formId)."'
                   LIMIT 1";
        $objResult = $objDatabase->Execute($query);     
        if ($objResult !== false) {        
            $this->id = intval($formId);
            $this->title = $objResult->fields['title'];                        
            $this->status = intval($objResult->fields['status']);                         
            $this->sort = intval($objResult->fields['order']);
            
            $queryInputfield = "SELECT field.`id` AS `id`,
                             field.`type` AS `type`,
                             field.`required` AS `required`,
                             field.`order` AS `order`,
                             field.`affiliation` AS `affiliation`,
                             name.`name` AS `name`,
                             name.`default` AS `default`
                        FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_registration_form_field AS field,
                             ".DBPREFIX."module_".$this->moduleTablePrefix."_registration_form_field_name AS name
                       WHERE (field.`id` = name.`field_id` AND name.`lang_id` = '".intval($_LANGID)."' AND field.`form` = '".intval($this->id)."' )
                    ORDER BY field.`order`";

            $objResultInputfield = $objDatabase->Execute($queryInputfield);
            
            if ($objResultInputfield !== false) {
                while (!$objResultInputfield->EOF) {
                    $arrFieldNames = array();
                    $arrFieldDefaults = array();
                    
                    $this->inputfields[intval($objResultInputfield->fields['id'])]['id'] = intval($objResultInputfield->fields['id']);
                    $this->inputfields[intval($objResultInputfield->fields['id'])]['type'] = htmlentities($objResultInputfield->fields['type'], ENT_QUOTES, CONTREXX_CHARSET);
                    $this->inputfields[intval($objResultInputfield->fields['id'])]['required'] = intval($objResultInputfield->fields['required']);
                    $this->inputfields[intval($objResultInputfield->fields['id'])]['order'] = intval($objResultInputfield->fields['order']);     
                    $this->inputfields[intval($objResultInputfield->fields['id'])]['affiliation'] = htmlentities($objResultInputfield->fields['affiliation'], ENT_QUOTES, CONTREXX_CHARSET);       
                    
                    //$arrFieldNames[0] = htmlentities($objResultInputfield->fields['name'], ENT_QUOTES, CONTREXX_CHARSET);
                    $arrFieldNames[0] = $objResultInputfield->fields['name'];
                    //$arrFieldDefaults[0] = htmlentities($objResultInputfield->fields['default'], ENT_QUOTES, CONTREXX_CHARSET);
                    $arrFieldDefaults[0] = $objResultInputfield->fields['default'];
                    
                    foreach ($this->arrFrontendLanguages as $key => $arrLang) {
                        $queryName = "SELECT name.`name` AS `name`,
                                         name.`default` AS `default`
                                    FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_registration_form_field_name AS name
                                   WHERE (name.`field_id` = '".intval($objResultInputfield->fields['id'])."' AND name.`lang_id` = '".intval($arrLang['id'])."')
                                   LIMIT 1";
                        
                        $objResultName = $objDatabase->Execute($queryName);
                        
                        //$arrFieldNames[intval($arrLang['id'])] = !empty($objResultName->fields['name']) ? htmlentities($objResultName->fields['name'], ENT_QUOTES, CONTREXX_CHARSET) : $arrFieldNames[0];
                        $arrFieldNames[intval($arrLang['id'])] = !empty($objResultName->fields['name']) ? $objResultName->fields['name'] : $arrFieldNames[0];
                        //$arrFieldDefaults[intval($arrLang['id'])] = !empty($objResultName->fields['default']) ? htmlentities($objResultName->fields['default'], ENT_QUOTES, CONTREXX_CHARSET) : $arrFieldDefaults[0];
                        $arrFieldDefaults[intval($arrLang['id'])] = !empty($objResultName->fields['default']) ? $objResultName->fields['default'] : $arrFieldDefaults[0];
                    }
                    
                    $this->inputfields[intval($objResultInputfield->fields['id'])]['name'] = $arrFieldNames;
                    $this->inputfields[intval($objResultInputfield->fields['id'])]['default_value'] = $arrFieldDefaults;
                    
                    
                    $objResultInputfield->MoveNext();
                }
            }
        }
    }
    
    function copy() { 
        global $objDatabase, $_LANGID;
                                       
        $queryOldForm = "SELECT id,title,status,`order`
                           FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_registration_form
                          WHERE id = '".intval($this->id)."'
                          LIMIT 1";
                   
        $objResultOldForm = $objDatabase->Execute($queryOldForm);
        
        if ($objResultOldForm !== false) {
            $queryNewForm = "INSERT INTO ".DBPREFIX."module_".$this->moduleTablePrefix."_registration_form
                                  (`status`,`order`,`title`)  
                           VALUES ('0',
                                   '99',
                                   '".$objResultOldForm->fields['title']."')";
            
            $objResultNewForm = $objDatabase->Execute($queryNewForm);
            
            if($objResultNewForm === false) {
                return false;
            }  else {
                $newFormId = intval($objDatabase->Insert_ID());
                
                $queryOldFields = "SELECT id,type,required,`order`,`affiliation`   
                                     FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_registration_form_field
                                    WHERE form = '".intval($this->id)."'";
                                    
                $objResultOldFields = $objDatabase->Execute($queryOldFields); 
                                        
                if ($objResultOldFields !== false) {
                    while (!$objResultOldFields->EOF) {
                        $queryNewField = "INSERT INTO ".DBPREFIX."module_".$this->moduleTablePrefix."_registration_form_field
                                                      (`form`,`type`,`required`,`order`,`affiliation` )  
                                               VALUES ('".$newFormId."',
                                                       '".$objResultOldFields->fields['type']."',
                                                       '".$objResultOldFields->fields['required']."',
                                                       '".$objResultOldFields->fields['order']."',
                                                       '".$objResultOldFields->fields['affiliation']."')";
            
                        $objResultNewField = $objDatabase->Execute($queryNewField);  
                        $newFieldId = intval($objDatabase->Insert_ID());  
                        
                        $queryOldNames =  "SELECT `lang_id`,`name`,`default`   
                                             FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_registration_form_field_name
                                            WHERE field_id = '".intval($objResultOldFields->fields['id'])."' AND form_id = '".intval($this->id)."'";           
                                            
                        $objResultOldNames = $objDatabase->Execute($queryOldNames); 
                        
                        if ($objResultOldNames !== false) {
                            while (!$objResultOldNames->EOF) {
                                $queryNewName = "INSERT INTO ".DBPREFIX."module_".$this->moduleTablePrefix."_registration_form_field_name  
                                                              (`field_id`,`form_id`,`lang_id`,`name`,`default` )  
                                                       VALUES ('".$newFieldId."',
                                                               '".$newFormId."',
                                                               '".$objResultOldNames->fields['lang_id']."',
                                                               '".$objResultOldNames->fields['name']."',
                                                               '".$objResultOldNames->fields['default']."')";        
                    
                                $objResultNewName = $objDatabase->Execute($queryNewName);  
                                
                                $objResultOldNames->MoveNext(); 
                            }
                        }
                                  
                        $objResultOldFields->MoveNext();
                    }
                }
            }
        }                         
            
        return $newFormId;
    }
    
    function save($data) {
        global $objDatabase, $_LANGID; 
        
        if(empty($data['inputfield']) || empty($data['formTitle'])) {
            return false;
        }                        
        
        if(intval($this->id) == 0) {  
            $query = "INSERT INTO ".DBPREFIX."module_".$this->moduleTablePrefix."_registration_form
                                  (`status`,`order`,`title`)  
                           VALUES ('0',
                                   '99',
                                   '".contrexx_addslashes($data['formTitle'])."')";
            
            $objResult = $objDatabase->Execute($query);
            
            if($objResult === false) {
                return false;
            }
            
            $this->id = intval($objDatabase->Insert_ID());   
        } else {
            $query = "UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_registration_form   
                         SET `title` =  '".contrexx_addslashes($data['formTitle'])."'        
                       WHERE id = '".intval($this->id)."'";
                        
            $objResult = $objDatabase->Execute($query) ;   
            
            if($objResult === false) {
                return false;
            }
        }
                                 
        if(intval($this->id) != 0) {  
            if(!self::saveInputfields($data)) {
                return false;   
            }
        } else {
            return false; 
        }      
                      
        return true;
    }
    
    
    function saveInputfields($data) {
        global $objDatabase, $_LANGID;    
                
        parent::getFrontendLanguages();
        
        $objResult = $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_registration_form_field_name WHERE form_id = '".$this->id."'");
        if($objResult !== false) {
            $objResult = $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_registration_form_field WHERE form = '".$this->id."'"); 
            if($objResult !== false) {
	            foreach ($data['inputfield'] as $intFieldId => $arrField) {
		            $query = "INSERT INTO ".DBPREFIX."module_".$this->moduleTablePrefix."_registration_form_field
                                          (`id`,`form`,`type`,`required`,`order`,`affiliation`)  
                                   VALUES ('".intval($intFieldId)."','".intval($this->id)."','".contrexx_addslashes($arrField['type'])."','".intval($arrField['required'])."','".intval($arrField['order'])."','".contrexx_addslashes($arrField['affiliation'])."')";
		            
		            $objResult = $objDatabase->Execute($query);
		            
		            if($objResult !== false) {
		            	foreach ($this->arrFrontendLanguages as $key => $arrLang) {
		            		if($arrLang['id'] == $_LANGID) {
		            			if($arrField['name'][0] != $arrField['name'][$arrLang['id']]) {
		            				if($arrField['name'][0] == $this->inputfields[$intFieldId]['name'][intval($arrLang['id'])]) {
		            					$strFieldName = !empty($arrField['name'][intval($arrLang['id'])]) ? $arrField['name'][intval($arrLang['id'])] : $arrField['name'][0];
		            				} else {
		            					$strFieldName = $arrField['name'][0];
		            				}
		            			} else {
		            				$strFieldName = $arrField['name'][0];
		            			}
		            		    if($arrField['default_value'][0] != $arrField['default_value'][$arrLang['id']]) {
                                    if($arrField['default_value'][0] == $this->inputfields[$intFieldId]['default_value'][intval($arrLang['id'])]) {
                                        $strFieldDefault = !empty($arrField['default_value'][intval($arrLang['id'])]) ? $arrField['default_value'][intval($arrLang['id'])] : $arrField['default_value'][0];
                                    } else {
                                        $strFieldDefault = $arrField['default_value'][0];
                                    }
                                } else {
                                    $strFieldDefault = $arrField['default_value'][0];
                                }
		            		} else {
		            			$strFieldName = !empty($arrField['name'][intval($arrLang['id'])]) ? $arrField['name'][intval($arrLang['id'])] : $arrField['name'][0];
		            			$strFieldDefault = !empty($arrField['default_value'][intval($arrLang['id'])]) ? $arrField['default_value'][intval($arrLang['id'])] : $arrField['default_value'][0];
		            		}
                            
                            if($arrField['type'] == 'select' || $arrField['type'] == 'radio') {
                                $tmpFieldDefault = array();
                                $tmpFieldDefault = explode(",", $strFieldDefault);
                                $finalFieldDefault = array();       
                                
                                foreach($tmpFieldDefault as $key => $tmpValue) {
                                    if($tmpValue != '' && $tmpValue != ' ') {
                                       $finalFieldDefault[] = $tmpValue; 
                                    }
                                }
                                
                                $strFieldDefault = join(",", $finalFieldDefault); 
                            }
		            		
		            		$query = "INSERT INTO ".DBPREFIX."module_".$this->moduleTablePrefix."_registration_form_field_name
                                                  (`field_id`,`form_id`,`lang_id`,`name`,`default`)  
                                           VALUES ('".intval($intFieldId)."',
                                                   '".intval($this->id)."',
                                                   '".intval($arrLang['id'])."',
                                                   '".contrexx_addslashes($strFieldName)."',
                                                   '".contrexx_addslashes($strFieldDefault)."')";
                    
                            $objResult = $objDatabase->Execute($query);
		            	}
		            } else {
			            return false;
			        }
		        }
            } else {
	            return false;
	        }
        } else {
            return false;
        }
        
        return true;
    }        
    
    function delete(){
        global $objDatabase;
        
        $query = "DELETE FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_registration_form
                        WHERE id = '".intval($this->id)."'";
        
        $objResult = $objDatabase->Execute($query);
        
        if ($objResult !== false) {      
            return true;
        } else {
            return false;
        }
    }   
    
    
    function switchStatus(){
        global $objDatabase;
        
        if($this->status == 1) {
            $formStatus = 0;
        } else {
            $formStatus = 1;
        }
        
        
        $query = "UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_registration_form   
                     SET status = '".intval($formStatus)."'
                   WHERE id = '".intval($this->id)."'";
        
        $objResult = $objDatabase->Execute($query);
        
        if ($objResult !== false) {
            return true;
        } else {
            return false;
        }
    }
             
    
    function saveOrder($order) {
        global $objDatabase, $_LANGID;    
                  
        $query = "UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_registration_form
                     SET `order` = '".intval($order)."'          
                   WHERE id = '".intval($this->id)."'";
                               
        $objResult = $objDatabase->Execute($query);   
        
        if ($objResult !== false) {
            return true;
        } else {
            return false;
        }
    }
    
    
    
    function getLastInputfieldId(){
        global $objDatabase;
        
        $query = "SELECT id
                    FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_registration_form_field 
                ORDER BY id DESC
                   LIMIT 1";
        
        $objResult = $objDatabase->Execute($query);
        
        if($objResult !== false) {
            return intval($objResult->fields['id']);
        } else {
        	return false;
        }
    }
}