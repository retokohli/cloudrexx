<?php
/**
 * JSON Adapter for ContentManager
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core/json
 */

namespace Cx\Core\Json\Adapter\ContentManager;
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
        return array('saveToggleStatuses', 'getAccess');
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString() {
        return '';
    }
    
    /**
     * Saves the toggle statuses in the session.
     * @param Array $params Client parameters
     * @author Yannic Tschanz <yannic.tschanz@comvation.com>
     */
    public function saveToggleStatuses($params) {
        $arrToggleStatuses = array();
        foreach ($params['post'] as $tabKey => $tabValue) {
            if (is_array($tabValue)) {
                foreach ($tabValue as $toggleKey => $toggleValue) {
                    $arrToggleStatuses[contrexx_input2raw($tabKey)][contrexx_input2raw($toggleKey)] = contrexx_input2raw($toggleValue);
                }
            } else {
                $arrToggleStatuses[contrexx_input2raw($tabKey)] = contrexx_input2raw($tabValue);
            }
        }
        $_SESSION['contentManager']['toggleStatuses'] = $arrToggleStatuses;
    }
    
    /**
     * Returns an array containing the permissions of the current user
     * The array has the following keys with boolean values:
     *  global      If this is false, cannot do anything in the content manager
     *  delete      If this is true, the user can delete pages and nodes
     *  create      If this is true, the user can create pages and nodes
     *  access      If this is true, the user can change access to pages
     *  publish     If this is true, the user can publish or decline drafts
     * @todo Move this method to the ContentManager class and use it everywhere
     * @return array Array containing the permissions of the current user
     */
    public function getAccess() {
        $global =   \Permission::checkAccess(6, 'static', true) &&
                    \Permission::checkAccess(35, 'static', true);
        return array(
            'global'    => $global,
            'delete'    => $global && \Permission::checkAccess(26, 'static', true),
            'create'    => $global && \Permission::checkAccess(5, 'static', true),
            'access'    => $global && \Permission::checkAccess(36, 'static', true),
            'publish'   => $global && \Permission::checkAccess(78, 'static', true),
        );
    }
}
