<?php
/**
 * JSON Adapter for User class
 * @copyright   Comvation AG
 * @author      Michael Räss <michael.raess@comvation.com>
 * @package     contrexx
 * @subpackage  core/json
 */

namespace Cx\Core\Json\Adapter\User;
use \Cx\Core\Json\JsonAdapter;

/**
 * JSON Adapter for Block module
 * @copyright   Comvation AG
 * @author      Michael Räss <michael.raess@comvation.com>
 * @package     contrexx
 * @subpackage  core/json
 */
class JsonUser implements JsonAdapter {
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
        return 'user';
    }
    
    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods() {
        return array('getUsers');
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString() {
        return implode('<br />', $this->messages);
    }
    
    /**
     * Return all users according to the given term
     * @return array List of users
     */
    public function getUsers() {
        $term = !empty($_GET['term']) ? trim($_GET['term']) : '';
        
        $arrSearch = array(
            'company'   => $term,
            'firstname' => $term,
            'lastname'  => $term,
            'username'  => $term,
        );
        $arrAttributes = array(
            'company', 'firstname', 'lastname', 'username',
        );
        
        $arrUsers  = array();
        $objFWUser = \FWUser::getFWUserObject();
        
        if ($objUser = $objFWUser->objUser->getUsers(null, $arrSearch, null, $arrAttributes)) {
            while (!$objUser->EOF) {
                $id    = $objUser->getId();
                $title = $objFWUser->getParsedUserTitle($objUser);
                
                $arrUsers[$id] = $title;
                $objUser->next();
            }
        }
        
        return $arrUsers;
    }
}
