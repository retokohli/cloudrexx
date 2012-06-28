<?php
namespace Cx\Core\Routing;

class URLException extends \Exception {};

/**
 * An URL container.
 */
class URL {
    /**
     * http or https
     * @todo Implement protocol support (at the moment only http is supported)
     * @var String Containing the URL protocol
     */
    protected $protocol = 'http';
    /**
     * http://example.com
     * @var string
     */
    protected $domain = null;
    /**
     * The/Module?a=10&b=foo
     * index.php?section=x&cmd=y
     * The/Special/Module/With/Params
     * @var string
     */
    protected $path = null;
    /**
     * The/Module
     * index.php
     * The/Special/Module/With/Params
     * @var string
     */
    protected $suggestedTargetPath = '';
    /**
     * ?a=10&b=foo
     * ?section=x&cmd=y
     *
     * @var string
     */
    protected $suggestedParams = '';

    /**
     * The/Module
     * Found/Path/To/Module
     * The/Special/Module
     * @var string
     */
    protected $targetPath = null;
    /**
     * ?a=10&b=foo
     * 
     * /With/Param
     * @var string
     */
    protected $params = null;

    //the different states of an url
    const SUGGESTED = 1;
    const ROUTED = 2;

    protected $state = 0;

    /**
     * Initializes $domain and $path.
     * @param string $url http://example.com/Test
     */
    public function __construct($url) {
        $matches = array();
        $matchCount = preg_match('/^(http:\/\/[^\/]+\/)(.*)?/', $url, $matches);
        if($matchCount == 0) {
            throw new URLException('Malformed URL: ' . $url);
        }

        $this->domain = $matches[1];
        if(count($matches) > 2) {
            $this->path = $matches[2];
        }

        $this->suggest();
    }
    
    /**
     * Whether the routing already treated this url
     */
    public function isRouted() {
        return $this->state >= self::ROUTED;
    }

    /**
     * sets $this->suggestedParams and $this->suggestedTargetPath
     */
    public function suggest() {
        $matches = array();
        $matchCount = preg_match('/([^\?]+)(.*)/', $this->path, $matches);

        if($matchCount == 0) {//seemingly, no parameters are set.
            $this->suggestedTargetPath = $this->path;
            $this->suggestedParams = '';
        }
        else {
            $this->suggestedTargetPath = $matches[1];
            $this->suggestedParams = $matches[2];
        }

        $this->state = self::SUGGESTED;
    }

    public function getDomain() {
        return $this->domain;
    }

    public function getPath() {
        return $this->path;
    }

    public function setPath($path) {
        $this->path = $path;
        $this->suggest();
    }

    public function setTargetPath($path) {
        $this->state = self::ROUTED;
        $this->targetPath = $path;
    }

    public function setParams($params) {
        $this->state = self::ROUTED;
        $this->params = $params;
    }
    
    /**
     * @author Michael Ritter <michael.ritter@comvation.com>
     * @todo most of the work done in this method should be done somewhere else (constructor?)
     * @param type $name
     * @param type $value 
     */
    public function setParam($name, $value) {
        // quick and dirty fix, see @todo...
        $params = $this->params2Array();
        $params[$name] = $value;
        $path = explode('?', $this->path);
        $this->path = $path[0];
        $this->path .= $this->array2Params($params);
    }
    
    /**
     * @author Michael Ritter <michael.ritter@comvation.com>
     * @return array 
     */
    private function params2Array() {
        $path = explode('?', $this->path);
        if (count($path) > 1) {
            $params = explode('&', $path[1]);
            foreach ($params as $key=>$param) {
                $param = explode('=', $param);
                $params[$param[0]] = $param[1];
                unset($params[$key]);
            }
        } else {
            $params = array();
        }
        return $params;
    }
    
    /**
     * @author Michael Ritter <michael.ritter@comvation.com>
     * @param string $params
     * @return string 
     */
    private function array2Params($params) {
        $path = '';
        if (count($params)) {
            foreach ($params as $key=>$value) {
                $params[$key] = $key . '=' . $value;
            }
            $path .= '?' . implode('&', $params);
        }
        return $path;
    }
    
    /**
     * Call this as soon as you want to use this url as a target
     * @author Michael Ritter <michael.ritter@comvation.com>
     */
    public function updateCsrf() {
        $params = $this->params2Array();
        if (isset($params['csrf'])) {
            $this->setParam('csrf', \CSRF::code());
        }
    }

    public function getTargetPath() {
        return $this->targetPath;
    }

    public function getParams() {
        return $this->params;
    }

    public function getSuggestedTargetPath() {
        return $this->suggestedTargetPath;
    }

    public function setSuggestedTargetPath($path) {
        $this->suggestedTargetPath = $path;
    }

    public function setSuggestedParams($params) {
        $this->suggestedParams = $params;
    }

    public function getSuggestedParams() {
        return $this->suggestedParams;
    }


    /**
     * @param $string request the captured request
     * @param $string pathOffset ASCMS_PATH_OFFSET
     */
    public static function fromCapturedRequest($request, $pathOffset, $get) {
        global $_CONFIG;
        
        if(substr($request, 0, strlen($pathOffset)) != $pathOffset)
            throw new URLException("'$request' doesn't seem to start with provided offset '$pathOffset'");

        //cut offset
        $request = substr($request, strlen($pathOffset)+1);
//TODO: correct host
        $host = $_CONFIG['domainUrl'];

//TODO: implement correct protocol finder
        $protocol = 'http';

        $getParams = '';
        foreach($get as $k => $v) {
            if($k == '__cap') //skip captured request from mod_rewrite
                continue; 
            $joiner='&';
            if($getParams == '')
                $joiner='?';
            $getParams .= $joiner.urlencode($k).'='.urlencode($v);
        }

        return new URL($protocol.'://'.$host.'/'.$request.$getParams);
    }

    /**
     * @todo this should only return $this->protocol . '://' . $this->host . '/' . $this->path . $this->getParamsForUrl();
     * @return type 
     */
    public function __toString() {
        $lang_dir = '';
        if (!defined('BACKEND_LANG_ID')) {
            // we are in frontend mode, so we do use virtual language dirs
            $lang_dir = \FWLanguage::getLanguageCodeById(FRONTEND_LANG_ID) . '/';
        }
        return $this->domain . // contains protocol, hostname and a trailing slash
                substr(ASCMS_PATH_OFFSET, 1) . '/' .
                $lang_dir .
                $this->path; // contains path (except for PATH_OFFSET and virtual language dir) and params
    }
}