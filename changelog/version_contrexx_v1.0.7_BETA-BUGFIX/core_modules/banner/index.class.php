<?PHP
/**
* Banner management Module
*
* This module will get all the news pages
*
* @copyright CONTREXX CMS - Astalavista IT Engineering GmbH Thun
* @author Astalavista Development Team <thun@astalvista.ch>
* @module banner
* @modulegroup core_modules
* @access public
* @version 1.0.0   
*/

require_once ASCMS_CORE_MODULE_PATH . '/banner/bannerLib.class.php';

class Banner extends bannerLibrary
{ 
    
	/**
    * Constructor
    *
    * @param  string  
    * @access public
    */   
    function Banner()
    { 
    	$this->__construct(); 
    }    
	
    /**
     * PHP5 constructor
     * @param  string  $pageContent
     * @global string  $_LANGID      
     * @access public     
     */
    function __construct()
    {  
	    global $_LANGID;
	    $this->_getBannerGroupStatus();
	    $this->langId = $_LANGID;
	}

	
	/**
    * Initialized the banner group array
    *
    * @global    object     $objDatabase                                                                            
    */ 
    function _getBannerGroupStatus()
    {
    	global $objDatabase;
        $query = "SELECT id,status FROM ".DBPREFIX."module_banner_groups";
        $objResult = $objDatabase->Execute($query);
	    while (!$objResult->EOF) {
		    $this->arrGroups[$objResult->fields['id']] = $objResult->fields['status'];
		    $objResult->MoveNext();
	    }
    }	
	
	
	
	/**
	* Get page
	*
	* @access public
    * @global object $objDatabase	
	* @return string bannerCode
	*/
    function getBannerCode($groupId, $pageId)
    {
    	global $objDatabase;
    	
    	$groupId = intval($groupId);
		$pageId= intval($pageId);
		
		$debugMessage = "";
			
		if($this->arrGroups[$groupId]==1) {
			///////////////////////////////////
			// The Banner group is active
			///////////////////////////////////
			if(isset($_GET['teaserId'])) {
				$teaserId=intval($_GET['teaserId']);
				
				$query = "SELECT system.banner_code AS banner_code
				            FROM ".DBPREFIX."module_banner_relations AS relation,
				                 ".DBPREFIX."module_banner_system AS system  
				           WHERE relation.group_id = ".$groupId."
				             AND relation.page_id = ".$teaserId."
				             AND relation.banner_id = system.id
				             AND relation.type='teaser'
				           LIMIT 1";	
			} else {			
				$query = "SELECT system.banner_code AS banner_code
				            FROM ".DBPREFIX."module_banner_relations AS relation,
				                 ".DBPREFIX."module_banner_system AS system  
				           WHERE relation.group_id = ".$groupId."
				             AND relation.page_id = ".$pageId."
				             AND relation.banner_id = system.id
				             AND relation.type='content'
				           LIMIT 1";
			}
			
			$objResult = $objDatabase->Execute($query);
						
			if ($objResult !== false && $objResult->RecordCount()==1) {		
					return $debugMessage.stripslashes($objResult->fields['banner_code']);		
			} else {
				///////////////////////////////////
				// show the default banner for this group
				///////////////////////////////////
				$query = "SELECT banner_code FROM ".DBPREFIX."module_banner_system WHERE parent_id = ".$groupId." AND is_default=1";	
				$objResult = $objDatabase->SelectLimit($query, 1);			
				if ($objResult !== false) {
					return $debugMessage.stripslashes($objResult->fields['banner_code']);
				} else {
					return $debugMessage;
				}			
			}
		} else {
			///////////////////////////////////
			// The Banner group is inactive
			///////////////////////////////////
			return $debugMessage;			
		}
    }
}
?>