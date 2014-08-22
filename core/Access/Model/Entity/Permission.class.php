<?php
/**
 * Permission 
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_access
 */

namespace Cx\Core\Access\Model\Entity;

/**
 * Permission
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_access
 */

class Permission {
    /**
     * Allowed protocols
     * 
     * @var array
     */
    protected $allowedProtocols = array();
    
    /**
     * Allowed access methods
     * 
     * @var array
     */
    protected $allowedMethods   = array();
    
    /**
     * is Login required or not
     * 
     * @var mixed 
     */
    protected $requiresLogin    = null;
    
    /**
     * Allowed group users
     * 
     * @var array 
     */
    protected $allowedGroups    = array();
    
    /**
     * Callback function name
     * 
     * @var string
     */
    public $callback            = null;
    
    /**
     * Constructor
     * 
     * @param Array   $allowedProtocols
     * @param Array   $allowedMethods
     * @param Boolean $requiresLogin
     */
    public function __construct($allowedProtocols = array('http', 'https'), $allowedMethods = array('get', 'post'), $requiresLogin = true, $callback = null) {
        if (!$allowedProtocols) {
            $allowedProtocols = array('http', 'https');
        }
        if (!$allowedMethods) {
            $allowedMethods = array('get', 'post');
        }
        $this->allowedProtocols = array_map('strtolower', $allowedProtocols);
        $this->allowedMethods   = array_map('strtolower', $allowedMethods);
        $this->requiresLogin    = $requiresLogin;
        $this->callback         = $callback;
    }
    
    /**
     * Check the permissions(Is allowed protocol, Is allowed method, user's group access, user's login status)
     * 
     * @return boolean
     */
    public function hasAccess(array $params = array()) {
        $protocol = \Env::get('cx')->getRequest()->getUrl()->getProtocol();
        $method   = \Env::get('cx')->getRequest()->getHttpRequestMethod();
        
        //protocol check
        if (!empty($this->allowedProtocols) && !in_array($protocol, $this->allowedProtocols)) {
            return false;
        }
        
        //access method check
        if (!empty($this->allowedMethods) && !in_array($method, $this->allowedMethods)) {
            return false;
        }
        
        // user loggedin or not (OR) user's group access check 
        if (!empty($this->requiresLogin) && !$this->checkLoginAndUserAccess()) {
            return false;
        }
        
        //callback function check
        if (isset($this->callback) && call_user_func($this->callback, $params) !== true) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check the user's login status and user's group access
     * 
     * @return boolean
     */
    private function checkLoginAndUserAccess() {
        
        if (!$this->requiresLogin) {
            return true;
        }
        
        //check user logged in or not
        if (!\FWUser::getFWUserObject()->objUser->login()) {
            return false;
        }
        
        if (!is_array($this->requiresLogin)) {
            return true;
        }
        
        //check user's group access
        if (count(array_intersect($this->requiresLogin, \FWUser::getFWUserObject()->objUser->getAssociatedGroupIds()))) {
            return true;
        }
        
        return false;
    }
}
