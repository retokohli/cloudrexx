<?php
/**
 * Forum library example
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_example_module_template
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Forum library example
 *
 * Forum library example
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_example_module_template
 */
class ForumLibraryExample
{
	var $_arrConfig = array();
	
	function ForumLibrary() 
	{
		$this->__constructor();
	}
	
	function __constructor() 
	{
		$this->_initialize();
	}
	
	function _initialize() 
	{
		global $objDatabase, $objPerm;
		
		// get config options
		$objResult = $objDatabase->Execute("SELECT `name`, `value`, `status` FROM ".DBPREFIX."module_forum_config");
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				$this->_arrConfig[$objResult->fields['name']] = array(
					'value'		=> $objResult->fields['value'],
					'status'	=> $objResult->fields['status']
				);
				$objResult->MoveNext();
			}
		}
	}
}
?>
