<?php

/**
 * Handling Request
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_routing
 */

namespace Cx\Core\Routing\Model\Entity;

/**
 * Handling Request
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_routing
 */

class Request {
    
    /**
     * HTTP requested method
     * 
     * @var String
     */
    protected $httpRequestMethod;
    
    /**
     * Resolved url object 
     * 
     * @var object
     */
    protected $url;
    
    /**
     * Constructor to initialize the $httpRequestMethod and $url
     * 
     * @param String $method
     * @param Object $resolvedUrl
     */
    public function __construct($method, \Cx\Core\Routing\Url $resolvedUrl) {
        $this->httpRequestMethod = strtolower($method);
        $this->url               = $resolvedUrl;
    }
    
    /**
     * Get the httpRequest method
     * 
     * @return String
     */
    public function getHttpRequestMethod() {
        return $this->httpRequestMethod;
    }
    
    /**
     * Get the resolved url object
     * 
     * @return Object
     */
    public function getUrl() {
        return $this->url;
    }
}

