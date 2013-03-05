<?php

/**
 * JSON Adapter for User class
 * @copyright   Comvation AG
 * @author      Michael Räss <michael.raess@comvation.com>
 * @package     contrexx
 * @subpackage  core_json
 */

namespace Cx\Core\Json\Adapter\User;
use \Cx\Core\Json\JsonAdapter;

/**
 * JSON Adapter for Block module
 * @copyright   Comvation AG
 * @author      Michael Räss <michael.raess@comvation.com>
 * @package     contrexx
 * @subpackage  core_json
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
        return array('getUserById', 'getUsers');
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString() {
        return implode('<br />', $this->messages);
    }
    
    /**
     * Returns the user with the given user id.
     * If the user does not exist then return the currently logged in user.
     * 
     * @return array User id and title
     */
    public function getUserById() {
        global $objInit, $_CORELANG;
        
        $objFWUser = \FWUser::getFWUserObject();
        
        if (!\FWUser::getFWUserObject()->objUser->login() || $objInit->mode != 'backend') {
            throw new \Exception($_CORELANG['TXT_ACCESS_DENIED_DESCRIPTION']);
        }
        
        $id = !empty($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$objUser = $objFWUser->objUser->getUser($id)) {
            $objUser = $objFWUser->objUser;
        }
        
        return array(
            'id'    => $objUser->getId(),
            'title' => $objFWUser::getParsedUserTitle($objUser),
        );
    }
    
    /**
     * Returns all users according to the given term.
     * 
     * @return array List of users
     */
    public function getUsers() {
        global $objInit, $_CORELANG;
        
        $objFWUser = \FWUser::getFWUserObject();
        
        if (!\FWUser::getFWUserObject()->objUser->login() || $objInit->mode != 'backend') {
            throw new \Exception($_CORELANG['TXT_ACCESS_DENIED_DESCRIPTION']);
        }
        
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
        $arrUsers = array();
        
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
