<?php
/**
 * JSON Adapter for ContentManager
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core/json
 */

namespace Cx\Core\Json\Adapter;
require_once ASCMS_CORE_PATH.'/json/JsonAdapter.interface.php';
use \Cx\Core\Json\JsonAdapter;

/**
 * JSON Adapter for ContentManager
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core/json
 */
class JsonContentManager implements JsonAdapter {
    
    /**
     * Returns the internal name used as identifier for this adapter
     * @return String Name of this adapter
     */
    public function getName() {
        return 'cm';
    }
    
    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods() {
        return array('saveToggleStatuses');
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString() {
        return '';
    }
    
    /**
     * @author Yannic Tschanz <yannic.tschanz@comvation.com>
     */
    public function saveToggleStatuses($params) {
        $arrToggleStatuses = array();
        foreach ($params['post'] as $tabKey => $tabValue) {
            foreach ($tabValue as $toggleKey => $toggleValue) {
                $arrToggleStatuses[contrexx_input2raw($tabKey)][contrexx_input2raw($toggleKey)] = contrexx_input2raw($toggleValue);
            }
        }
        $_SESSION['contentManager']['toggleStatuses'] = $arrToggleStatuses;
    }
}
