<?php
/**
 * Block
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author		Astalavista Development Team <thun@astalvista.ch>
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  module_block
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH.'/block/lib/blockLib.class.php';

/**
 * Block
 *
 * block module class
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author		Astalavista Development Team <thun@astalvista.ch>
 * @access		public
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  module_block
 */
class block extends blockLibrary
{
	/**
	* Set block
	*
	* Parse a block
	*
	* @access public
	* @param array $arrBlocks
	* @param string &$code
	* @see blockLibrary::_setBlock()
	*/
	function setBlock($arrBlocks, &$code)
	{
		foreach ($arrBlocks as $blockId) {
			$this->_setBlock(intval($blockId), $code);
		}
	}
	
	/**
	* Set block Random
	*
	* Parse a block Random
	*
	* @access public
	* @param array $arrBlocks
	* @param string &$code
	* @see blockLibrary::_setBlock()
	*/
	function setBlockRandom(&$code)
	{
		$this->_setBlockRandom($code);
	}
	
	function setBlockGlobal(&$code, $pageId)
	{
		$this->_setBlockGlobal($code, $pageId);
	}
}
?>
