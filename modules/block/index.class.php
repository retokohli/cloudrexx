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
    public static function setBlocks(&$content, $page)
    {
        $config = Env::get('config');

        $objBlock = new block();

        if (!is_array($content)) {
            $arrTemplates = array(&$content);
        } else {
            $arrTemplates = &$content;
        }

        foreach ($arrTemplates as &$template) { 
            // Set blocks [[BLOCK_<ID>]]
            if (preg_match_all('/{'.$objBlock->blockNamePrefix.'([0-9]+)}/', $template, $arrMatches)) {
                $objBlock->setBlock($arrMatches[1], $template);
            }

            // Set global block [[BLOCK_GLOBAL]]
            if (preg_match('/{'.$objBlock->blockNamePrefix.'GLOBAL}/', $template)) {
                $objBlock->setBlockGlobal($template, $page->getNode()->getId());
            }

            /* Set random blocks [[BLOCK_RANDOMIZER]], [[BLOCK_RANDOMIZER_2]],
                                 [[BLOCK_RANDOMIZER_3]], [[BLOCK_RANDOMIZER_4]] */
            if ($config['blockRandom'] == '1') {
                $placeholderSuffix = '';

                $randomBlockIdx = 1;
                while ($randomBlockIdx <= 4) {
                    $blockPlaceholderRegexp = '/{'.$objBlock->blockNamePrefix.'RANDOMIZER'.$placeholderSuffix.'}/';
                 
                    if (preg_match('/{'.$objBlock->blockNamePrefix.'RANDOMIZER}/', $template)) {
                        $objBlock->setBlockRandom($template, $randomBlockIdx);
                    }

                    $randomBlockIdx++;
                    $placeholderSuffix = '_'.$randomBlockIdx;
                }
            }
        }
    }


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
