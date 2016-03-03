<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Main controller for Login
 * 
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_login
 */

namespace Cx\Core_Modules\Login\Controller;

/**
 * Main controller for Login
 * 
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_login
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {

    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }

    /**
     * Define the available commands for command line mode
     *
     * @return array
     */
    public function getCommandsForCommandMode()
    {
        return array('Login');
    }

    /**
     * Callback for excuting the command
     *
     * @param string $command   Name of command to execute
     * @param array  $arguments List of arguments for the command
     */
    public function executeCommand($command, $arguments)
    {
        switch ($command) {
            case 'Login':
            default:
                echo $this->apiLogin($arguments);
                break;
        }
    }

    /**
     * Authenticate the user
     *
     * @param array $params List of arguments for the login
     */
    public function apiLogin($params)
    {
        global $objInit, $_CORELANG;

        $_CORELANG = $objInit->loadLanguageData('core');

        $username = $params['username'] ? contrexx_input2raw($params['username']) : '';
        $password = $params['password'] ? md5(contrexx_input2raw($params['password'])) : '';

        if (!$username || !$password) {
            return $this->parseJsonMessage($_CORELANG['TXT_PASSWORD_OR_USERNAME_IS_INCORRECT'], false);
        }

        \cmsSession::getInstance();

        if (!isset($_SESSION['auth'])) {
            $_SESSION['auth'] = array();
        }
        $fwUser = \FWUser::getFWUserObject();

        // if already login, destroy session and relogin
        if ($fwUser->objUser->login()) {
            $fwUser->objUser->reset();
            $fwUser->logoutAndDestroySession();
            \cmsSession::getInstance();
        }

        if ($fwUser->objUser->auth($username, $password)) {
            $fwUser->loginUser($fwUser->objUser);
            $data = array(
                'session' => $_SESSION->sessionid
            );
            return $this->parseJsonMessage($data);
        } else {
            return $this->parseJsonMessage($_CORELANG['TXT_PASSWORD_OR_USERNAME_IS_INCORRECT'], false);
        }
    }

    /**
     * Parse the message to the json output.
     *
     * @param  string  $message message
     * @param  boolean $status  true | false (if status is true returns success json data)
     *                          if status is false returns error message json.
     *
     * @return string
     */
    public function parseJsonMessage($message, $status = true)
    {
        $json = new \Cx\Core\Json\JsonData();
        $data = array();

        if (is_array($message)) {
            $data = $message;
        } else {
            $data['message'] = $message;
        }

        if ($status) {
            return $json->json(
                array(
                    'status' => 'success',
                    'data'   => $data
                ),
                false,
                ($_GET['callback'] ? $_GET['callback'] : '')
            );
        } else {
            return $json->json(
                array(
                    'status'  => 'error',
                    'message' => $message
                ),
                false,
                ($_GET['callback'] ? $_GET['callback'] : '')
            );
        }
    }

    /**
     * Load your component.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $objTemplate, $sessionObj;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                if (!isset($sessionObj) || !is_object($sessionObj)) $sessionObj = \cmsSession::getInstance();
                $objLogin = new \Cx\Core_Modules\Login\Controller\Login(\Env::get('cx')->getPage()->getContent());
                $pageTitle = \Env::get('cx')->getPage()->getTitle();
                $pageMetaTitle = \Env::get('cx')->getPage()->getMetatitle();
                \Env::get('cx')->getPage()->setContent($objLogin->getContent($pageMetaTitle, $pageTitle));
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                if (\FWUser::getFWUserObject()->objUser->login(true)) {
                    \Cx\Core\Csrf\Controller\Csrf::header('location: index.php');
                }
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
                $objTemplate = $this->cx->getTemplate();
                $objLoginManager = new \Cx\Core_Modules\Login\Controller\LoginManager();
                $objLoginManager->getPage();
                break;

            default:
                break;
        }
    }

}
