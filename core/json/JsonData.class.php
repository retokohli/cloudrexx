<?php
/**
 * JSON Interface to Contrexx Doctrine Database
 * @copyright   Comvation AG
 * @author      Florian Schuetz <florian.schuetz@comvation.com>
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core/json
 */

namespace Cx\Core\Json;
use \Cx\Core\Json\Adapter\JsonNode;
use \Cx\Core\Json\Adapter\JsonPage;
use \Cx\Core\Json\Adapter\JsonContentManager;

/**
 * JSON Interface to Contrexx Doctrine Database
 * @copyright   Comvation AG
 * @author      Florian Schuetz <florian.schuetz@comvation.com>
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core/json
 */
class JsonData {
    
    /**
     * Namespace containing adapter classes
     * @var String Namespace
     */
    protected static $adapter_ns = '\\Cx\\Core\\Json\\Adapter\\';
    protected static $adapter_path = '/json/adapter/';
    
    /**
     * List of adapter class names. Just add yours to the list...
     * @var array List of adapter class names 
     */
    protected static $adapter_classes = array(
        'ContentManager' => array(
            'JsonNode', 'JsonPage', 'JsonContentManager',
        ),
    );
    
    /**
     * List of adapters to use (they have to implement the JsonAdapter interface)
     * @var Array List of JsonAdapters
     */
    protected $adapters = array();

    /**
     * Constructor, loads adapter classes
     * @author Michael Ritter <michael.ritter@comvation.com>
     */
    public function __construct() {
        foreach (self::$adapter_classes as $ns=>$adapters) {
            foreach ($adapters as $adapter) {
                require_once ASCMS_CORE_PATH . self::$adapter_path . strtolower($ns) . '/' . $adapter . '.class.php';
                $adapter = self::$adapter_ns . $ns . '\\' . $adapter;
                $object = new $adapter();
                $this->adapters[$object->getName()] = $object;
            }
        }
    }

    /**
     * Passes JSON data to the particular adapter and returns the result
     * Called from index.php when section is 'jsondata'
     * @author Florian Schuetz <florian.schuetz@comvation.com>
     * @author Michael Ritter <michael.ritter@comvation.com>
     * @param String $adapter Adapter name
     * @param String $method Method name
     * @param Array $arguments Arguments to pass
     * @return String JSON data to return to client
     */
    public function jsondata($adapter, $method, $arguments) {
        if (!isset($this->adapters[$adapter])) {
            return $this->getJsonError('No such adapter');
        }
        $adapter = $this->adapters[$adapter];
        $methods = $adapter->getAccessableMethods();
        $realMethod = '';
        if (in_array($method, $methods)) {
            $realMethod = $method;
        } else if (isset($methods[$method])) {
            $realMethod = $methods[$method];
        }
        if ($realMethod == '') {
            return $this->getJsonError('No such method: ' . $method);
        }
        try {
            // browsers will pass rendering of application/* MIMEs to other
            // applications, usually.
            // Skip the following line for debugging, if so desired
            header('Content-Type: application/json');

            // Disabling CSRF protection. That's no problem as long as we
            // only return associative arrays or objects!
            // https://mycomvation.com/wiki/index.php/Contrexx_Security#CSRF
            // Search for a better way to disable CSRF!
            ini_set('url_rewriter.tags', '');

            $output = call_user_func(array($adapter, $realMethod), $arguments);

            return json_encode(array(
                'status'  => 'success',
                'data'    => $output,
                'message' => $adapter->getMessagesAsString()
            ));
        } catch (\Exception $e) {
            //die($e->getTraceAsString());
            return $this->getJsonError($e->getMessage());
        }
    }
    
    /**
     * Returns the JSON code for a error message
     * @param String $message HTML encoded message
     * @author Michael Ritter <michael.ritter@comvation.com>
     * @return String JSON code
     */
    protected function getJsonError($message) {
        return json_encode(array(
            'status' => 'error',
            'message'   => $message
        ));
    }
}
