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
 * JSON Interface to Cloudrexx
 * @copyright   Cloudrexx AG
 * @author      Florian Schuetz <florian.schuetz@comvation.com>
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_json
 */

namespace Cx\Core\Json;

/**
 * JSON Interface to Cloudrexx Doctrine Database
 *
 * @api
 * @copyright   Cloudrexx AG
 * @author      Florian Schuetz <florian.schuetz@comvation.com>
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_json
 */
class JsonData {
    /**
     * List of adapter class names.
     * @deprecated Use component framework instead (SystemComponentController->getControllersAccessableByJson())
     * @var array List of adapter class names
     */
    protected static $adapter_classes = array(
        '\\Cx\\Core\\Json\\Adapter\\User' => array(
            'JsonUser',
        ),
        '\\Cx\\Core\\Json\\Adapter\\Calendar' => array(
            'JsonCalendar',
        ),
        '\\Cx\\Modules\\Crm\\Controller' => array(
            'JsonCrm',
        ),
    );

    /**
     * List of adapters to use (they have to implement the JsonAdapter interface)
     * @var Array List of JsonAdapters
     */
    protected $adapters = array();
    /**
     * Session id for request which we got from the login request
     * @var string $sessionId
     */
    protected $sessionId = null;

    /**
     * Constructor, loads adapter classes
     * @author Michael Ritter <michael.ritter@comvation.com>
     */
    public function __construct() {
        foreach (self::$adapter_classes as $ns=>$adapters) {
            foreach ($adapters as $adapter) {
                $this->loadAdapter($adapter, $ns);
            }
        }
    }

    /**
     * @deprecated Use component framework instead (SystemComponentController->getControllersAccessableByJson())
     */
    public static function addAdapter($className, $namespace = '\\') {
        if (!$className) {
            return;
        }
        if (is_array($className)) {
            foreach ($className as $class) {
                self::addAdapter($class, $namespace);
            }
            return;
        }
        self::$adapter_classes[$namespace][] = $className;
    }

    /**
     * Adds an adapter accessable by JSON requests.
     *
     * Either specify a fully qualified classname, or a classname and the containing
     * namespace separatly
     * @todo Adapter loading could be optimized
     * @param string $className Fully qualified or class name located in $namespace
     * @param string $namespace (optional) Namespace for non fully qualified class name
     * @throws \Exception if JsonAdapter interface is not implemented or controller can not be found
     */
    public function loadAdapter($className, $namespace = '') {
        if (substr($className, 0, 1) == '\\') {
            $adapter = $className;
        } else {
            $adapter = $namespace . '\\' . $className;
        }

        // check if its an adapter!
        if (!is_a($adapter, '\Cx\Core\Json\JsonAdapter', true)) {
            throw new \Exception('Tried to load class as JsonAdapter, but interface is not implemented: "' . $adapter . '"');
        }

        // load specified controller
        $matches = array();
        preg_match('/\\\\?Cx\\\\(?:Core|Core_Modules|Modules|modules)\\\\([^\\\\]*)/', $adapter, $matches);
        $possibleComponentName = '';
        if (isset($matches[1])) {
            $possibleComponentName = $matches[1];
        }
        $nsParts = explode('\\', $adapter);
        $controllerClass = end($nsParts);

        // legacy adapter
        if (in_array($possibleComponentName, array('Json', 'Crm'))) {
            $this->loadLegacyAdapter($adapter);
            return;
        }

        $em = \Env::get('cx')->getDb()->getEntityManager();
        $componentRepo = $em->getRepository('Cx\Core\Core\Model\Entity\SystemComponent');
        $component = $componentRepo->findOneBy(array('name'=>$possibleComponentName));
        if (!$component) {
            $this->loadLegacyAdapter($adapter);
            return;
            //throw new \Exception('JsonAdapter component could not be found: "' . $adapter . '"');
        }
        $object = $component->getController(preg_replace('/Controller$/', '', $controllerClass));
        if (!$object) {
            $this->loadLegacyAdapter($adapter, $component);
            return;
            //throw new \Exception('JsonAdapter controller could not be found: "' . $adapter . '"');
        }
        $this->adapters[$object->getName()] = $object;
    }

    /**
     * @deprecated: This load adapter in a way they shouldn't be loaded
     */
    protected function loadLegacyAdapter($adapter, $component = null) {
        \DBG::msg('Loading legacy JsonAdapter: ' . $adapter);
        if ($component) {
            $object = new $adapter($component->getSystemComponent(), \Env::get('cx'));
        } else {
            $object = new $adapter();
        }
        \Env::get('init')->loadLanguageData($object->getName());
        $this->adapters[$object->getName()] = $object;
    }

    /**
     * Passes JSON data to the particular adapter and returns the result
     * Called from index.php when section is 'jsondata'
     *
     * @author Florian Schuetz <florian.schuetz@comvation.com>
     * @author Michael Ritter <michael.ritter@comvation.com>
     * @param String $adapter Adapter name
     * @param String $method Method name
     * @param Array $arguments Arguments to pass
     * @param boolean $setContentType (optional) If true (default) the content type is set to application/json
     * @return String JSON data to return to client
     */
    public function jsondata($adapter, $method, $arguments = array(), $setContentType = true) {
        return $this->json($this->data($adapter, $method, $arguments), $setContentType);
    }

    /**
     * Parses data into JSON
     * @param array $data Data to JSONify
     * @param boolean $setContentType (optional) If true (NOT default) the content type is set to application/json
     * @return String JSON data to return to client
     */
    public function json($data, $setContentType = false) {
        if ($setContentType) {
            // browsers will pass rendering of application/* MIMEs to other
            // applications, usually.
            // Skip the following line for debugging, if so desired
            header('Content-Type: application/json');

            // Disabling CSRF protection. That's no problem as long as we
            // only return associative arrays or objects!
            // https://mycomvation.com/wiki/index.php/Contrexx_Security#CSRF
            // Search for a better way to disable CSRF!
            ini_set('url_rewriter.tags', '');
        }
        return json_encode($data);
    }

    /**
     * Passes JSON data to the particular adapter and returns the result
     * Called from jsondata() or any part of Cloudrexx
     * @author Michael Ritter <michael.ritter@comvation.com>
     * @param String $adapter Adapter name
     * @param String $method Method name
     * @param Array $arguments Arguments to pass
     * @return String data to use for further processing
     */
    public function data($adapter, $method, $arguments = array()) {
        global $_ARRAYLANG;

        if (!isset($this->adapters[$adapter])) {
            return $this->getErrorData('No such adapter');
        }
        $adapter = $this->adapters[$adapter];
        $methods = $adapter->getAccessableMethods();
        $realMethod = '';

        /*
         * $adapter->getAccessableMethods() might return two type of arrays
         * Format 1: array('method1', 'method2')
         * Format 2: array('method1' => new \Cx\Core_Modules\Access\Model\Entity\Permission())
         */
        foreach ($methods as $methodName => $methodValue) {
            if ($methodValue instanceof \Cx\Core_Modules\Access\Model\Entity\Permission) {
                $realMethod = ($methodName == $method) ? $method : '';
            } elseif ($methodValue == $method) {
                $realMethod = $method;
            }

            if (!empty($realMethod)) {
                break;
            }
        }

        if ($realMethod == '') {
            return $this->getErrorData('No such method: ' . $method);
        }
        //permission checks
        $objPermission = new \Cx\Core_Modules\Access\Model\Entity\Permission(null, null, true, null, null, null);
        $defaultPermission = $adapter->getDefaultPermissions();
        if (!empty($methods[$method]) && ($methods[$method] instanceof \Cx\Core_Modules\Access\Model\Entity\Permission)) {
            $objPermission = $methods[$method];
        } else if (!empty ($defaultPermission) && ($defaultPermission instanceof \Cx\Core_Modules\Access\Model\Entity\Permission)) {
            $objPermission = $defaultPermission;
        }

        if ($objPermission && ($objPermission instanceof \Cx\Core_Modules\Access\Model\Entity\Permission)) {
            if (!$objPermission->hasAccess($arguments)) {
                $backend = \Cx\Core\Core\Controller\Cx::instanciate()->getMode() == \Cx\Core\Core\Controller\Cx::MODE_BACKEND;
                if (!\FWUser::getFWUserObject()->objUser->login($backend)) {
                    die($this->json($this->getErrorData($_ARRAYLANG['TXT_LOGIN_NOAUTH_JSON']), true));
                }
                return $this->getErrorData('JsonData-request to method ' . $realMethod . ' of adapter ' . $adapter->getName() . ' has been rejected by not complying to the permission requirements of the requested method.');
            }
        }

        try {
            $output = call_user_func(array($adapter, $realMethod), $arguments);

            return array(
                'status'  => 'success',
                'data'    => $output,
                'message' => $adapter->getMessagesAsString()
            );
        } catch (\Exception $e) {
            //die($e->getTraceAsString());
            return $this->getErrorData($e->getMessage());
        }
    }

    public function setSessionId($sessionId) {
        $this->sessionId = $sessionId;
    }

    public function getSessionId() {
        return $this->sessionId;
    }

    /**
     * Fetches a json response via HTTP request
     * @todo Support cookies (to allow login and similiar features)
     * @param string $url URL to get json from
     * @param array $data (optional) HTTP post data
     * @param boolean $secure (optional) Wheter to verify peer using SSL or not, default false
     * @param string $certificateFile (optional) Local certificate file for non public SSL certificates
     * @param array Set an optional HTTP Authentication method and supply its login credentials.
     *              The supplied array must comply with the following structure:
     * <pre class="brush: php">
     *              $httpAuth = array(
     *                  'httpAuthMethod'   => 'none|basic|disgest',
     *                  'httpAuthUsername' => '<username>',
     *                  'httpAuthPassword' => '<password>',
     *              );
     * </pre>
     * @return mixed Decoded JSON on success, false otherwise
     */
    public function getJson($url, $data = array(), $secure = false, $certificateFile = '', $httpAuth=array(), $files = array()) {
        $request = new \HTTP_Request2($url, \HTTP_Request2::METHOD_POST);

        if (!empty($httpAuth)) {
            switch($httpAuth['httpAuthMethod']) {
                case 'basic':
                    $request->setAuth($httpAuth['httpAuthUsername'], $httpAuth['httpAuthPassword'], \HTTP_Request2::AUTH_BASIC);
                    break;
                case 'disgest':
                    $request->setAuth($httpAuth['httpAuthUsername'], $httpAuth['httpAuthPassword'], \HTTP_Request2::AUTH_DIGEST);
                    break;
                case 'none':
                default:
                    break;
            }
        }

        foreach ($data as $name=>$value) {
            $request->addPostParameter($name, $value);
        }

        if (!empty($files)) {
            foreach ($files as $fieldId => $file) {
                $request->addUpload($fieldId, $file);
            }
        }

        if ($this->sessionId !== null) {
            $request->addCookie(session_name(), $this->sessionId);
        }
        $request->setConfig(array(
            // disable ssl peer verification
            'ssl_verify_host' => false,
            'ssl_verify_peer' => false,
            // follow HTTP redirect
            'follow_redirects' => true,
            // resend original request to new location
            'strict_redirects' => true,
        ));
        $response = $request->send();
        //echo '<pre>';var_dump($response->getBody());echo '<br /><br />';
        $cookies = $response->getCookies();
        foreach ($cookies as &$cookie) {
            if ($cookie['name'] === session_name()) {
                $this->sessionId = $cookie['value'];
                break;
            }
        }
        if ($response->getStatus() != 200) {
            \DBG::msg(__METHOD__.' Request failed! Status: '.$response->getStatus());
            \DBG::msg('URL: '.$url);
            \DBG::dump($data);
            return false;
        }

        $body = json_decode($response->getBody());
        if ($body === NULL) {
            \DBG::msg(__METHOD__.' failed!');
            \DBG::dump($response->getBody());
        }
        return $body;
    }

    /**
     * Returns the JSON code for a error message
     * @param String $message HTML encoded message
     * @author Michael Ritter <michael.ritter@comvation.com>
     * @return String JSON code
     */
    public function getErrorData($message) {
        return array(
            'status' => 'error',
            'message'   => $message
        );
    }
}
