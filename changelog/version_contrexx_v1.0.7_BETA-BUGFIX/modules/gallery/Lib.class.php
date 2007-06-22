<?PHP
/**
* Modul Gallery
*
* Library for the Gallery 
*
* @copyright CONTREXX CMS - COMVATION AG
* @author Comvation Development Team <info@comvation.com>  
* @module gallery
* @modulegroup modules
* @access public
* @version 1.0.0
*/


class GalleryLibrary
{
	
	/**
    * Gets the gallery settings
    *
    * @global  object  $objDatabase                                                                             
    */ 
    function getSettings()
    {   	
    	global $objDatabase;
    	$objResult = $objDatabase->Execute("SELECT name,value FROM ".DBPREFIX."module_gallery_settings");
    	while (!$objResult->EOF) {
    		$this->arrSettings[$objResult->fields('name')] = $objResult->fields['value'];
    		$objResult->MoveNext();
    	}   
    } 
}
?>