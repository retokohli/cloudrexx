<?php

/**
 * Block
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_block
 * @todo        Edit PHP DocBlocks!
 */

/**
 * @ignore
 */
require_once ASCMS_MODULE_PATH.'/block/lib/blockLib.class.php';

/**
 * Block
 *
 * block module class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
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
    function setBlockRandom(&$code, $id)
    {
        $this->_setBlockRandom($code, $id);
    }

    function setBlockGlobal(&$code, $pageId)
    {
        $this->_setBlockGlobal($code, $pageId);
    }
}
?>
