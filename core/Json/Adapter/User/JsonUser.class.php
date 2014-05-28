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
        return array('getUserById', 'getUsers', 'loginUser', 'logoutUser', 'lostPassword', 'setPassword');
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


    /**
     * Logs the current User in.
     *
     * @param string $_POST['USERNAME']
     * @param string $_POST['PASSWORD']
     * @return false on failure and array with userdata on success
     */
    public function loginUser() {
        $objFWUser = \FWUser::getFWUserObject();
        if ($userId = $objFWUser->checkLogin($crossDomain = true)) {
            $user = $objFWUser->objUser->getUser($userId);
            $objFWUser->loginUser($user);
            $user->setRestoreKey();
            $user->store();
            return array(
                'username' => $user->getUsername(),
                'groupIds' => $user->getAssociatedGroupIds(),
                'isAdmin'  => $user->getAdminStatus(),
                'backendLanguage' => $user->getBackendLanguage(),
                'key'      => $user->getRestoreKey(),
            );
        }
        return false;
    }

    /**
     * Logs the current User out.
     *
     * @return boolean
     */
    public function logoutUser() {
        \FWUser::getFWUserObject()->logoutAndDestroySession();
        return true;
    }

    /**
     * Sends a Email with a new tomporary Password to the user with given email
     *
     * @param string $arguments['get']['email'] || $arguments['post']['email']
     * @return boolean
     */
    public function lostPassword($arguments) {
        global $_ARRAYLANG;
        if (empty($arguments['get']['email']) && empty($arguments['post']['email'])) {
            throw new \Exception("not enough arguments!");
        }
        $sendMail = isset($arguments['post']['sendMail']) && $arguments['post']['sendMail'] == 'true';
        $lang = $arguments['post']['language'];
        \Env::get('ClassLoader')->loadFile(ASCMS_CORE_MODULE_PATH.'/access/lang/' . $lang . '/frontend.php');

        $email = contrexx_stripslashes(!empty($arguments['get']['email']) ? $arguments['get']['email'] : $arguments['post']['email']);
        $objFWUser = \FWUser::getFWUserObject();
        if ($restoreLink = $objFWUser->restorePassword($email, $sendMail)) {
            $this->messages[] = $_ARRAYLANG['TXT_ACCESS_FORGOT_PASSWORD_SENT'];
            return array(
                'restoreLink' => $restoreLink,
            );
        }
        throw new \Exception($objFWUser->getErrorMsg());
    }

    /**
     * Sets a new Password for a user.
     *
     * @param string $arguments['get']['userId'] || $arguments['post']['userId']
     * @param string $arguments['get']['password'] || $arguments['post']['password']
     * @param string $arguments['get']['repeatPassword'] || $arguments['post']['repeatPassword']
     * @return boolean
     */
    public function setPassword($arguments) {
        $post = $arguments['post'];
        if ((empty($post['userId']) && empty($post['userId'])) ||
                (empty($post['password']) && empty($post['password'])) ||
                (empty($post['repeatPassword']) && empty($post['repeatPassword']))) {
            throw new \Exception("insufficient arguments!");
        }
        $objFWUser = \FWUser::getFWUserObject();
        $arrPermissionIds = $objFWUser->objGroup->getGroups()->getStaticPermissionIds();
        if (!$objFWUser->objUser->login()) {
            throw new \Exception("you are not logged in");
        }
        if ($objFWUser->objUser->getAdminStatus() || (in_array('18', $arrPermissionIds) && in_array('36', $arrPermissionIds))) {
            $password = contrexx_stripslashes(!empty($post['password']) ? $post['password'] : $post['password']);
            $password2 = contrexx_stripslashes(!empty($post['repeatPassword']) ? $post['repeatPassword'] : $post['repeatPassword']);


            $userEmail = !empty($post['userId']) ? $post['userId'] : $post['userId'];
            $arrSearch = array('email' => $userEmail);

            if ($objUser = $objFWUser->objUser->getUsers(null, $arrSearch)) {
                if (!$objUser) {
                    throw new \Exception("No user found!");
                }
            }
            if (!$objUser->setPassword($password, $password2)) {
                throw new \Exception(current($objUser->error_msg));
            }
            return $objUser->store();
        }
        throw new \Exception("no access!!");
    }
}