<?php
/**
 * Livecam Library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>            
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  module_livecam
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Livecam Library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @access		private
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  module_livecam
 */
class LivecamLibrary
{
	/**
	* Settings array
	*
	* @access public
	* @var array
	*/
	var $arrSettings = array();
	
	/**
    * Get settings
    *
    * Initialize the settings
    *
    * @access public
    */ 
    function getSettings()
    {
    	
    	global $objDatabase;

    	$query = "SELECT setname, setvalue FROM ".DBPREFIX."module_livecam_settings";
        $objResult = $objDatabase->Execute($query);
	    while (!$objResult->EOF) {
		    $this->arrSettings[$objResult->fields['setname']] = $objResult->fields['setvalue'];
		    $objResult->MoveNext();
	    }
	    
    }
}
?>
