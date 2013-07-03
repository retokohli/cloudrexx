<?php

/**
 * Calendar Class Categroy
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_calendar
 * @todo        Edit PHP DocBlocks!
 */

class CalendarCategory extends CalendarLibrary
{
    public $id;
    public $name;
    public $pos;
    public $status;
    public $arrData = array();
    
    function __construct($id=null){
        if($id != null) {
            self::get($id);
        }
    }
    
    function get($catId) {
        global $objDatabase, $_LANGID;
        
        $query = "SELECT category.`id` AS `id`, 
                         category.`pos` AS `pos`, 
                         category.`status` AS `status`, 
                         name.`name` AS `name`
                    FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_category AS category,
                         ".DBPREFIX."module_".$this->moduleTablePrefix."_category_name AS name
                   WHERE category.id = '".intval($catId)."'
                     AND category.id = name.cat_id
                     AND name.lang_id = '".intval($_LANGID)."'
                   LIMIT 1";
        
        $objResult = $objDatabase->Execute($query);
        
        if ($objResult !== false) {
        	$this->id = intval($catId);
	        $this->name = $objResult->fields['name'];
	        $this->pos = intval($objResult->fields['pos']);
	        $this->status = intval($objResult->fields['status']);
        }
    }
    
    function getData() {
        global $objDatabase, $_LANGID;
        
        //get category name(s)
        $query = "SELECT `name`,`lang_id` 
                    FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_category_name
                   WHERE cat_id = '".intval($this->id)."'";
        
        $objResult = $objDatabase->Execute($query);
        
        if ($objResult !== false) {
            while (!$objResult->EOF) {
            	if($objResult->fields['lang_id'] == $_LANGID) {
            		$this->arrData['name'][0] = htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET);
            	}
                $this->arrData['name'][intval($objResult->fields['lang_id'])] = htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET);
                $objResult->MoveNext();
            }
        }
        
        //get category host(s)
        $query = "SELECT `name`,`id` 
                    FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_host
                   WHERE cat_id = '".intval($this->id)."'
                     AND confirmed = '1'";
        
        $objResult = $objDatabase->Execute($query);
        
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $this->arrData['hosts'][intval($objResult->fields['id'])] = htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET);
                $objResult->MoveNext();
            }
        }
    }
    
    function switchStatus(){
        global $objDatabase;
        
        if($this->status == 1) {
            $categoryStatus = 0;
        } else {
            $categoryStatus = 1;
        }
        
        
        $query = "UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_category
                     SET status = '".intval($categoryStatus)."'
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
                  
        $query = "UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_category
                     SET `pos` = '".intval($order)."'          
                   WHERE id = '".intval($this->id)."'";
                               
        $objResult = $objDatabase->Execute($query);   
        
        if ($objResult !== false) {
            return true;
        } else {
            return false;
        }
    }
    
    function save($data) {
        global $objDatabase, $_LANGID;
    	
    	$arrHosts = array();
    	$arrHosts = $data['selectedHosts'];
    	$arrNames = array();
        $arrNames = $data['name'];
        
    	if(intval($this->id) == 0) {
    		$query = "INSERT INTO ".DBPREFIX."module_".$this->moduleTablePrefix."_category
    		                      (`pos`,`status`)
                           VALUES ('0','0')";
    		
	        $objResult = $objDatabase->Execute($query);
	        
    		if($objResult === false) {
                return false;
            }
            
            $this->id = intval($objDatabase->Insert_ID());
    	}
    	
    	//names
    	$query = "DELETE FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_category_name
                        WHERE cat_id = '".intval($this->id)."'";
            
        $objResult = $objDatabase->Execute($query);
        
        if ($objResult !== false) {
        	foreach ($arrNames as $langId => $categoryName) {
        		if($langId != 0) {
	        		$categoryName = $categoryName=='' ? $arrNames[0] : $categoryName;
	        		
	        		if($_LANGID == $langId) {
	        			$categoryName = $arrNames[0] != $this->name ? $arrNames[0] : $categoryName;
	        		}
	        		
	        		$query = "INSERT INTO ".DBPREFIX."module_".$this->moduleTablePrefix."_category_name
	                                      (`cat_id`,`lang_id`,`name`)
	                               VALUES ('".intval($this->id)."','".intval($langId)."','".contrexx_addslashes(contrexx_strip_tags($categoryName))."')";
	            
	                $objResult = $objDatabase->Execute($query);
        		}
        	}
        	
	        if ($objResult !== false) {
                //hosts
		        foreach ($arrHosts as $key => $hostId) {
			        $query = "UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_host
			                     SET cat_id = '".intval($this->id)."'          
			                   WHERE id = '".intval($hostId)."'";
			            
			        $objResult = $objDatabase->Execute($query);
		        }
		        
		        if ($objResult !== false) {
		            return true;
		        } else {
		            return false;
		        }
	        } else {
	            return false;
	        }
        } else {
        	return false;
        }
    }
    
    function delete(){
        global $objDatabase;
        
        $query = "DELETE FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_category
                        WHERE id = '".intval($this->id)."'";
        
        $objResult = $objDatabase->Execute($query);
        
        if ($objResult !== false) {
        	$query = "DELETE FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_category_name
	                        WHERE cat_id = '".intval($this->id)."'";
	        
	        $objResult = $objDatabase->Execute($query);
	        
	        if ($objResult !== false) {
	        	$query = "UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_host
	        	             SET cat_id = '0'          
	                       WHERE cat_id = '".intval($this->id)."'";
	            
	            $objResult = $objDatabase->Execute($query);
	            if ($objResult !== false) {
	            	return true;
	            } else {
	            	return false;
	            }
	        } else {
                return false;
	        }
        } else {
            return false;
        }
    }
    
    function countEntries(){
        global $objDatabase;  
        
        // get startdate
        if (!empty($_GET['from'])) {
            $startDate = parent::getDateTimestamp($_GET['from']); 
        } else if ($_GET['cmd'] == 'archive') {                             
            $startDate = null; 
        } else {
            $startDay   = isset($_GET['day']) ? $_GET['day'] : date("d", mktime());   
            $startMonth = isset($_GET['month']) ? $_GET['month'] : date("m", mktime()); 
            $startYear  = isset($_GET['year']) ? $_GET['year'] : date("Y", mktime());     
            $startDay = $_GET['cmd'] == 'boxes' ? 1 : $startDay;        
            $startDate = mktime(0, 0, 0, $startMonth, $startDay, $startYear);  
        }                   
        
        // get enddate
        if (!empty($_GET['till'])) {
            $endDate = parent::getDateTimestamp($_GET['till']); 
        } else if ($_GET['cmd'] == 'archive') {
            $endDate = mktime(); 
        } else {
            $endDay   = isset($_GET['endDay']) ? $_GET['endDay'] : date("d", mktime());   
            $endMonth = isset($_GET['endMonth']) ? $_GET['endMonth'] : date("m", mktime()); 
            $endYear  = isset($_GET['endYear']) ? $_GET['endYear'] : date("Y", mktime());            
            $endYear = empty($_GET['endYear']) && empty($_GET['endMonth']) ? $endYear+10: $endYear;          
            $endDate = mktime(23, 59, 59, $endMonth, $endDay, $endYear);      
        }
        
        $searchTerm = !empty($_GET['term']) ? contrexx_addslashes($_GET['term']) : null;
        
        $objEventManager = new CalendarEventManager($startDate,$endDate,$this->id,$searchTerm);
        $objEventManager->getEventList();            
        $count = count($objEventManager->eventList);
        
        return $count;
    }
}