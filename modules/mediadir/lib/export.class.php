<?php         
/**
 * Media  Directory
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @subpackage  module_mediadir
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH . '/mediadir/lib/lib.class.php';  
require_once ASCMS_MODULE_PATH . '/mediadir/lib/form.class.php';   
require_once ASCMS_MODULE_PATH . '/mediadir/lib/inputfield.class.php';   
require_once ASCMS_MODULE_PATH . '/mediadir/lib/entry.class.php';   

class mediaDirectoryExport extends mediaDirectoryLibrary
{
    private $csvSeparator = ';'; 
    private $elementSeparator = ','; 
    /**
     * Constructor
     */
    function __construct()
    {                                 
        //nothing...
    }
    
    function exportCSV($intFormId, $arrCategoryIds=null, $arrLevelIds=null, $intMaskId=null)
    {
        global $_ARRAYLANG, $_CORELANG, $_LANGID, $objDatabase;      
        
        if($intFormId != null) {
            $objValidator = &new FWValidator();                                                                                 
            $arrEntries = array(); 
            $arrEntriesData = array();
            $arrInputfields = array();   
            $arrMask = array();     
            
            if($intMaskId != null && $intMaskId != 0) { 
                $objResultMask = $objDatabase->Execute("SELECT
                                                    fields, form_id 
                                                FROM
                                                    ".DBPREFIX."module_".$this->moduleTablePrefix."_masks     
                                                WHERE id = '".$intMaskId."'     
                                               ");
                if ($objResultMask !== false) {
                    $arrMask = explode(',', $objResultMask->fields['fields']);  
                    $intFormId = $objResultMask->fields['form_id'];                   
                }
            }            
            
            $objForm = new mediaDirectoryForm($intFormId);             
            $objInputfields = new mediaDirectoryInputfield($intFormId);    
            $strFilename = contrexx_raw2encodedUrl($objForm->arrForms[$intFormId]['formName'][0])."_".mktime().".csv"; 
                                              
            if($arrCategoryIds != null) {                                      
                foreach($arrCategoryIds as $intKey => $intCategoryId) {
                    if($arrLevelIds != null) {
                        $strDatabaseLevel = ",".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_levels AS level";
                        $strLevels = join(',', $arrLevelIds);
                        $strWhereLevel = " AND ((cat.entry_id = level.entry_id) AND (level.level_id IN (".$strLevels.")))"; 
                    }
                    
                    $objResultCategories = $objDatabase->Execute("SELECT
                                                                        cat.entry_id AS entryId 
                                                                    FROM
                                                                        ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_categories AS cat
                                                                        ".$strDatabaseLevel."      
                                                                    WHERE
                                                                        cat.category_id ='".$intCategoryId."'
                                                                        ".$strWhereLevel."      
                                                                   ");
                    if ($objResultCategories !== false) {
                        while (!$objResultCategories->EOF) {
                            $arrEntries[$objResultCategories->fields['entryId']] = $objResultCategories->fields['entryId'];
                            $objResultCategories->MoveNext();
                        }                   
                    }
                }
            } else if($arrLevelIds != null) { 
                foreach($arrLevelIds as $intKey => $intLevelId) {
                    $objResultLevels = $objDatabase->Execute("SELECT
                                                                        level.entry_id AS entryId 
                                                                    FROM
                                                                        ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_levels AS level       
                                                                    WHERE
                                                                        level.level_id ='".$intLevelId."'     
                                                                   ");
                    if ($objResultLevels !== false) {
                        while (!$objResultLevels->EOF) {
                            $arrEntries[$objResultLevels->fields['entryId']] = $objResultLevels->fields['entryId'];
                            $objResultLevels->MoveNext();
                        }                   
                    }
                }  
            } else {
                $objEntry = new mediaDirectoryEntry(); 
                $objEntry->getEntries(null, null, null, null, null, null, true, null, 'n', null, null, $intFormId);  
                
                foreach($objEntry->arrEntries as $intEntryId => $arrEntry) {
                    $arrEntries[$intEntryId] = $intEntryId;    
                }  
            }                  
            
            foreach($arrEntries as $intKey => $intEntryId) {  
                $objResultEntry = $objDatabase->Execute("SELECT     
                                                                entry.value AS value, entry.form_id AS formId, entry.field_id AS fieldId 
                                                            FROM
                                                                ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_inputfields AS entry     
                                                            WHERE
                                                                entry.entry_id ='".$intEntryId."'
                                                            AND 
                                                                entry.lang_id ='".$_LANGID."'           
                                                           ");
                if ($objResultEntry !== false) {
                    while (!$objResultEntry->EOF) {
                        if($objResultEntry->fields['formId'] == $intFormId) {
                            $arrEntriesData[$intEntryId][$objResultEntry->fields['fieldId']] = $objResultEntry->fields['value'];   
                        }  
                        $objResultEntry->MoveNext();
                    }                   
                }
            }
            
            foreach($objInputfields->arrInputfields as $intFieldId => $arrField) { 
                $arrInputfields[$arrField['order']]['id'] = $intFieldId;
                $arrInputfields[$arrField['order']]['name'] = $arrField['name'][0];        
            }   
            
            ksort($arrInputfields);   
            
            header("Content-Type: text/comma-separated-values; charset=".CONTREXX_CHARSET, true);
            header("Content-Disposition: attachment; filename=\"$strFilename\"", true);      
                                                                                         
            foreach($arrInputfields as $intKey => $arrField) {
                if($intMaskId == null || ($arrMask != null && in_array($arrField['id'], $arrMask))) {  
                    print self::escapeCsvValue($arrField['name']).$this->csvSeparator;    
                } 
            }
              
            print "\r\n";  
            
            foreach($arrEntriesData as $intEntryId => $arrEntry) {            
                foreach($arrInputfields as $intFieldOrder => $arrField) { 
                    if($intMaskId == null || ($arrMask != null && in_array($arrField['id'], $arrMask))) { 
                        switch($arrField['id']) {
                            case 1:
                                $arrCategories = self::getCategoriesLevels(1, $intEntryId);
                                $strCategories = join($this->elementSeparator, $arrCategories);
                                print $strCategories.$this->csvSeparator;   
                                break;
                            case 2:
                                $arrLevels = self::getCategoriesLevels(2, $intEntryId);
                                $strLevels = join($this->elementSeparator, $arrLevels);
                                print $strLevels.$this->csvSeparator;  
                                break;
                            default:
                                $strFieldValue = $arrEntriesData[$intEntryId][$arrField['id']];
                                $strFieldValue = strip_tags($strFieldValue);
                                $strFieldValue = self::escapeCsvValue($strFieldValue);
                                $strFieldValue = html_entity_decode($strFieldValue, ENT_QUOTES, CONTREXX_CHARSET);  
                                
                                if(CONTREXX_CHARSET == 'UTF-8') {  
                                    $strFieldValue = utf8_decode($strFieldValue);      
                                } 
                                                                                                   
                                print $strFieldValue.$this->csvSeparator;
                                break;
                        }     
                    }   
                }
                
                print "\r\n";       
            }        
            exit();  
        } else {
            return false;
        }  
    }
    
    
    function escapeCsvValue($value)
    {             
        $valueModified = stripslashes($value);                                                           
        $valueModified = preg_replace('/\r\n/', " ", $valueModified);      
        $valueModified = str_replace('"', '""', $valueModified);         

        if ($valueModified != $value || preg_match('/['.$this->csvSeparator.'\n]+/', $value)) {
            $value = '"'.$valueModified.'"';
        } 
         
        return $value;
    } 
    
    function getCategoriesLevels($intType, $intEntryId=null)
    {
        global $objDatabase, $_LANGID;
        
        $arrList = array();
        
        if($intType == 1) {
            //categories
            $query = "SELECT
                    cat_rel.`category_id` AS `elm_id`,
                    cat_name.`category_name` AS `elm_name`
                  FROM
                    ".DBPREFIX."module_mediadir_rel_entry_categories AS cat_rel,
                    ".DBPREFIX."module_mediadir_categories_names AS cat_name
                  WHERE
                    cat_rel.`category_id` = cat_name.`category_id`
                  AND
                    cat_rel.`entry_id` = '".intval($intEntryId)."'
                  AND
                    cat_name.`lang_id` = '".intval($_LANGID)."'
                  ORDER BY
                    cat_name.`category_name` ASC
                  ";              
        } else {
            //levels
            $query = "SELECT
                    level_rel.`level_id` AS `elm_id`,
                    level_name.`level_name` AS `elm_name`
                  FROM
                    ".DBPREFIX."module_mediadir_rel_entry_levels AS level_rel,
                    ".DBPREFIX."module_mediadir_level_names AS level_name
                  WHERE
                    level_rel.`level_id` = level_name.`level_id`
                  AND
                    level_rel.`entry_id` = '".intval($intEntryId)."'
                  AND
                    level_name.`lang_id` = '".intval($_LANGID)."'
                  ORDER BY
                    level_name.`level_name` ASC
                  ";               
        }
        
        $objEntryCategoriesLevels = $objDatabase->Execute($query);              
        
        if ($objEntryCategoriesLevels !== false) {      
            while (!$objEntryCategoriesLevels->EOF) {
                $strValue = strip_tags($objEntryCategoriesLevels->fields['elm_name']);     
                $strValue = self::escapeCsvValue($strValue);
                $strValue = html_entity_decode($strValue, ENT_QUOTES, CONTREXX_CHARSET);  
                
                if(CONTREXX_CHARSET == 'UTF-8') {  
                    $strValue = utf8_decode($strValue);      
                } 
                $arrList[] = $objEntryCategoriesLevels->fields['elm_name'];
                $objEntryCategoriesLevels->MoveNext();
            }       
        }
        
        return $arrList;
    }
}
?>
