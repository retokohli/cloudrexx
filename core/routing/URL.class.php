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
     * @var string Virtual language directory
     */
    protected $langDir = '';
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
        $matchCount = preg_match('/^(https?:\/\/[^\/]+\/)(.*)?/', $url, $matches);
        if($matchCount == 0) {
            throw new URLException('Malformed URL: ' . $url);
        }

        $this->domain = $matches[1];
        if(count($matches) > 2) {
            $this->setPath($matches[2]);
        } else {
            $this->suggest();
        }
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
        if ($this->state == self::SUGGESTED) {
            return;
        }
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
        $pathOffset = substr(ASCMS_PATH_OFFSET, 1);
        if (substr($path, 0, strlen($pathOffset)) == $pathOffset) {
            $path = substr($path, strlen($pathOffset) + 1);
        }
        $path = explode('/', $path);
        if (\FWLanguage::getLanguageIdByCode($path[0]) !== false) {
            $this->langDir = $path[0];
            unset($path[0]);
        }
        $path = implode('/', $path);
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
                // hide CSRF-Protection
                if ($param[0] == 'csrf') {
                    unset($params[$key]);
                    continue;
                }
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

    public function getTargetPath() {
        return $this->targetPath;
    }

    public function getParams() {
        return $this->params;
    }
    
    /**
     * @author Michael Ritter <michael.ritter@comvation.com>
     * @return array 
     */
    public function getParamArray() {
        return $this->params2Array();
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
        $host = $_CONFIG['domainUrl'];
        $protocol = ASCMS_PROTOCOL;

        $getParams = '';
        foreach($get as $k => $v) {
            if($k == '__cap') //skip captured request from mod_rewrite
                continue; 
            $joiner='&';
            if ($getParams == '') {
                $joiner='?';
            }
            $getParams .= $joiner.urlencode($k).'='.urlencode($v);
        }

        return new URL($protocol.'://'.$host.'/'.$request.$getParams);
    }
    
    /**
     * Returns an Url object for module, cmd and lang
     * @todo There could be more than one page using the same module and cmd per lang
     * @param string $module Module name
     * @param string $cmd (optional) Module command, default is empty string
     * @param int $lang (optional) Language to use, default is FRONTENT_LANG_ID
     * @param array $parameters (optional) HTTP GET parameters to append
     * @param string $protocol (optional) The protocol to use
     * @return \Cx\Core\Routing\URL Url object for the supplied module, cmd and lang
     */
    public static function fromModuleAndCmd($module, $cmd = '', $lang = '', $parameters = array(), $protocol = '') {
        if ($lang == '') {
            $lang = FRONTEND_LANG_ID;
        }
        $pageRepo = \Env::get('em')->getRepository('Cx\Model\ContentManager\Page');
        $page = $pageRepo->findOneBy(array(
            'module' => $module,
            'cmd' => $cmd,
            'lang' => $lang,
        ));
        return static::fromPage($page, $parameters, $protocol);
    }
    
    /**
     * Returns the URL object for a page id
     * @param int $pageId ID of the page you'd like the URL to
     * @param array $parameters (optional) HTTP GET parameters to append
     * @param string $protocol (optional) The protocol to use
     * @return \Cx\Core\Routing\URL Url object for the supplied page id 
     */
    public static function fromPageId($pageId, $parameters = array(), $protocol = '') {
        $pageRepo = \Env::get('em')->getRepository('Cx\Model\ContentManager\Page');
        $page = $pageRepo->findOneBy(array(
            'id' => $pageId,
        ));
        return static::fromPage($page, $parameters, $protocol);
    }
    
    /**
     * Returns the URL object for a page
     * @global type $_CONFIG
     * @param \Cx\Model\ContentManager\Page $page Page to get the URL to
     * @param array $parameters (optional) HTTP GET parameters to append
     * @param string $protocol (optional) The protocol to use
     * @return \Cx\Core\Routing\URL Url object for the supplied page
     */
    public static function fromPage($page, $parameters = array(), $protocol = '') {
        global $_CONFIG;
        
        if ($protocol == '') {
            $protocol = ASCMS_PROTOCOL;
        }
        $host = $_CONFIG['domainUrl'];
        $offset = ASCMS_PATH_OFFSET;
        $path = $page->getPath();
        $langDir = \FWLanguage::getLanguageCodeById($page->getLang());
        $getParams = '';
        if (count($parameters)) {
            $paramArray = array();
            foreach ($parameters as $key=>$value) {
                $paramArray[] = $key . '=' . $value;
            }
            $getParams = '?' . implode('&', $paramArray);
        }
        return new URL($protocol.'://'.$host.$offset.'/'.$langDir.$path.$getParams);
    }
    
    public function toString() {
        return $this->domain . substr($this, 1);
    }
    
    public function getLangDir() {
        $lang_dir = '';

        if ($this->langDir == '') {
            $lang_dir = \FWLanguage::getLanguageCodeById(FRONTEND_LANG_ID) . '/';
        } else {
            $lang_dir = $this->langDir;
        }

        return $lang_dir;
    }
    
    public function setLangDir($langDir) {
        $this->lang_dir = $langDir;
    }

    /**
     * Returns URL without hostname for use in internal links.
     * Use $this->toString() for full URL including protocol and hostname
     * @todo this should only return $this->protocol . '://' . $this->host . '/' . $this->path . $this->getParamsForUrl();
     * @return type 
     */
    public function __toString() {
        return ASCMS_PATH_OFFSET . '/' .
                $this->getLangDir() . '/' .
                $this->path; // contains path (except for PATH_OFFSET and virtual language dir) and params
    }
}
