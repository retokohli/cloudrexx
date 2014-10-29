<?php
/**
 * Calendar Class Catagory Manager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation <info@comvation.com>
 * @version     $Id: index.inc.php,v 1.00 $
 * @package     contrexx
 * @subpackage  module_calendar
 */


/**
 * CalendarCategoryManager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation <info@comvation.com>
 * @version     $Id: index.inc.php,v 1.00 $
 * @package     contrexx
 * @subpackage  module_calendar
 */
class CalendarCategoryManager extends CalendarLibrary
{
    /**
     * Category List
     * 
     * @access public
     * @var array 
     */
    public $categoryList = array();
    
    /**
     * Only Active
     * 
     * @access private
     * @var boolean 
     */
    private $onlyActive;

    /**
     * Constructor
     * 
     * @param boolean $onlyActive
     */
    function __construct($onlyActive=false){
    	$this->onlyActive = $onlyActive;
    }
    
    /**
     * Returns all the calendar categories
     * 
     * @global object  $objDatabase
     * @global integer $_LANGID
     * @return array Returns all calendar categories
     */
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
    
    /**
     * Sets the category placeholder's to the template
     * 
     * @global object $objInit
     * @global array $_ARRAYLANG
     * @param object $objTpl
     * @param integer $categoryId     
     */
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
    
    /**
     * Sets the category placeholder's to the template for the list view
     * 
     * @global array $_ARRAYLANG
     * @param object $objTpl
     */
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
                $this->moduleLangVar.'_CATEGORY_EVENTS'  => $objCategory->countEntries(true),
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
    
    /**
     * Return's the category dropdown
     * 
     * @global array $_ARRAYLANG
     * @param integer $selectedId
     * @param integer $type
     * @return string Return's the html dropdown of the categories.
     */
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