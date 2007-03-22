<?php
/**
 * Community library
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Astalavista Development Team <thun@astalvista.ch>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_community
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Community library
 *
 * provides common methods for the community classes
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Astalavista Development Team <thun@astalvista.ch>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_community
 */
class Community_Library
{
	/**
	* Configuration data
	*
	* @access public
	* @var array
	*/
	var $arrConfig = array();
	
	/**
	* Initialize the configuration variable
	*
	* @access public
	* @global object $objDatabase
	*/
	function initialize()
	{
		global $objDatabase;
		
		$objResult = $objDatabase->Execute("SELECT name, value, status FROM ".DBPREFIX."community_config");
		
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				$this->arrConfig[$objResult->fields['name']] = array(
					'value'		=> $objResult->fields['value'],
					'status'	=> $objResult->fields['status']
				);
				$objResult->MoveNext();
			}
		}
	}
}
?>
