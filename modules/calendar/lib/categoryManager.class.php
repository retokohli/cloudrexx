<?php
/**
 * Calendar Class Catagory Manager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_calendar
 * @todo        Edit PHP DocBlocks!
 */

class CalendarCategoryManager extends CalendarLibrary
{
    public $categoryList = array();
    
    private $onlyActive;

    function __construct($onlyActive=false){
    	$this->onlyActive = $onlyActive;
    }
    
    function getCategoryList() {
        global $objDatabase,$_LANGID;
        
        $onlyActive_where = ($this->onlyActive == true ? ' WHERE status=1' : '');
        
        $query = "SELECT category.id AS id
                    FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_category AS category
                         ".$onlyActive_where."
                ORDER BY category.pos";
        
        $objResult = $objDatabase->Execute($query);
        
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $objCategory = new CalendarCategory(intval($objResult->fields['id']));
                $this->categoryList[] = $objCategory;
                $objResult->MoveNext();
            }
        }
    }
    
    function showCategory($objTpl, $categoryId) {
        global $objInit, $_ARRAYLANG;
        
        $objCategory = new CalendarCategory(intval($categoryId));
        $this->categoryList[$categoryId] = $objCategory;
        
        $objCategory->getData();
        
        $objTpl->setVariable(array(
            $this->moduleLangVar.'_CATEGORY_ID'              => $objCategory->id,
            $this->moduleLangVar.'_CATEGORY_STATUS'          => $objCategory->status==0 ? $_ARRAYLANG['TXT_CALENDAR_INACTIVE'] : $_ARRAYLANG['TXT_CALENDAR_ACTIVE'],
            $this->moduleLangVar.'_CATEGORY_NAME'            => $objCategory->name,
            $this->moduleLangVar.'_CATEGORY_NAME_MASTER'     => $objCategory->arrData['name'][0],
        ));
    }
    
    function showCategoryList($objTpl) {
        global $_ARRAYLANG;
        
        $i=0;
        foreach ($this->categoryList as $key => $objCategory) {
        	$objTpl->setVariable(array(
                $this->moduleLangVar.'_CATEGORY_ROW'     => $i%2==0 ? 'row1' : 'row2',
                $this->moduleLangVar.'_CATEGORY_ID'      => $objCategory->id,
                $this->moduleLangVar.'_CATEGORY_LED'     => $objCategory->status==0 ? 'red' : 'green',
                $this->moduleLangVar.'_CATEGORY_STATUS'  => $objCategory->status==0 ? $_ARRAYLANG['TXT_CALENDAR_INACTIVE'] : $_ARRAYLANG['TXT_CALENDAR_ACTIVE'],
                $this->moduleLangVar.'_CATEGORY_SORT'    => $objCategory->pos,
                $this->moduleLangVar.'_CATEGORY_TITLE'   => $objCategory->name,
            ));
            
            $i++;
            $objTpl->parse('categoryList');
        }
        
        if(count($this->categoryList) == 0) {
            $objTpl->hideBlock('categoryList');
                
            $objTpl->setVariable(array(
                'TXT_'.$this->moduleLangVar.'_NO_CATEGORIES_FOUND' => $_ARRAYLANG['TXT_CALENDAR_NO_CATEGORIES_FOUND'],
            ));
                
            $objTpl->parse('emptyCategoryList');
        }
    }
    
    function getCategoryDropdown($selectedId=null, $type) {
    	global $_ARRAYLANG;
    	
        parent::getSettings();
    	$arrOptions = array();
    	
    	foreach ($this->categoryList as $key => $objCategory) {
            if($this->arrSettings['countCategoryEntries'] == 1) {
                $count = ' ('.$objCategory->countEntries().')';
            } else {
                $count = '';
            }   
            
    		$arrOptions[$objCategory->id] = $objCategory->name.$count;
    	}
    	
    	switch(intval($type)) {
    		case 1:
                $options = "<option value=''>".$_ARRAYLANG['TXT_CALENDAR_ALL_CAT']."</option>";
    			break;
            case 2:
                $options = "<option value=''>".$_ARRAYLANG['TXT_CALENDAR_PLEASE_CHOOSE']."</option>";
                break;
    		default:
    			$options = "<option value=''></option>";
                break;
    	}
    	
    	$options .= parent::buildDropdownmenu($arrOptions, $selectedId);
        
    	return $options;
    }
}