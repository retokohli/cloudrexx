<?php
/**
 * JSON Interface to Contrexx
 * @copyright   Comvation AG
 * @author      Florian Schuetz <florian.schuetz@comvation.com>
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core_json
 */

namespace Cx\Core\Json;
use \Cx\Core\Json\Adapter\JsonNode;
use \Cx\Core\Json\Adapter\JsonPage;
use \Cx\Core\Json\Adapter\JsonContentManager;

/**
 * JSON Interface to Contrexx Doctrine Database
 *
 * @api
 * @copyright   Comvation AG
 * @author      Florian Schuetz <florian.schuetz@comvation.com>
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core_json
 */
class JsonData {
    /**
     * List of adapter class names.
     * @deprecated Use component framework instead (SystemComponentController->getControllersAccessableByJson())
     * @var array List of adapter class names 
     */
    protected static $adapter_classes = array(
        '\\Cx\\Core\\Json\\Adapter\\Block' => array(
            'JsonBlock',
        ),
        '\\Cx\\Core\\Json\\Adapter\\User' => array(
            'JsonUser',
        ),
        '\\Cx\\Core\\Json\\Adapter\\Calendar' => array(
            'JsonCalendar',
        ),
        '\\Cx\\modules\\Survey\\Controller' => array(
            'JsonSurvey',
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
     * @param string $className Fully qualified or class name located in $namespace
     * @param string $namespace (optional) Namespace for non fully qualified class name
     */
    public function loadAdapter($className, $namespace = '') {
        if (substr($className, 0, 1) == '\\') {
            $adapter = $className;
        } else {
            $adapter = $namespace . '\\' . $className;
        }
        // check if its an adapter!
        
        // find proper systemcomponent instead of empty one
        $nsParts = explode('\\', $namespace);
        $possibleComponentName = $nsParts[2];
        $em = \Env::get('cx')->getDb()->getEntityManager();
        $componentRepo = $em->getRepository('Cx\Core\Core\Model\Entity\SystemComponent');
        $component = $componentRepo->findOneBy(array('name'=>$possibleComponentName));
        if (!$component) {
            // legacy adapter
            $object = new $adapter();
            $this->adapters[$object->getName()] = $object;
            return;
        }
        // new adapter
        $object = new $adapter($component->getSystemComponent(), \Env::get('cx'));
        \Env::get('init')->loadLanguageData($component->getName());
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
     * Called from jsondata() or any part of Contrexx
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
        if (in_array($method, $methods) || array_key_exists($method, $methods)) {
            $realMethod = $method;
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
    public function getJson($url, $data = array(), $secure = false, $certificateFile = '', $httpAuth=array()) {
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
        if ($this->sessionId !== null) {
            $request->addCookie(session_name(), $this->sessionId);
        }
        $request->setConfig(array(
            'ssl_verify_host' => false,
            'ssl_verify_peer' => false,
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
