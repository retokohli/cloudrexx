<?php
/**
 * JSON Adapter for Block module
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core/json
 */

namespace Cx\Core\Json\Adapter\Block;
use \Cx\Core\Json\JsonAdapter;

/**
 * JSON Adapter for Block module
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core/json
 */
class JsonBlock implements JsonAdapter {
    /**
     * List of messages
     * @var Array 
     */
    private $messages = array();
    
    /**
     * Returns the internal name used as identifier for this adapter
     * @return String Name of this adapter
     */
    public function getName() {
        return 'block';
    }
    
    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods() {
        return array('getBlocks');
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString() {
        return implode('<br />', $this->messages);
    }
    
    /**
     * Returns all available blocks for each language
     * @return array List of blocks (lang => id )
     */
    public function getBlocks() {
        $blockLib = new \BlockLibrary();
        $blocks = $blockLib->getBlocks();
        $data = array();
        foreach ($blocks as $id=>$block) {
            $data[$id] = $block['name'];
        }
        return $data;
    }
}
