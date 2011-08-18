<?php
namespace Cx\Core\Routing;

class URLException extends \Exception {};

/**
 * An URL container.
 */
class URL {
    /**
     * http://example.com
     * @var string
     */
    protected $domain = null;
    /**
     * /The/Module?a=10&b=foo
     * /index.php?section=x&cmd=y
     * /The/Special/Module/With/Params
     * @var string
     */
    protected $path = null;

    /**
     * /The/Module
     * /Found/Path/To/Module
     * /The/Special/Module
     * @var string
     */
    protected $targetPath = null;
    /**
     * ?a=10&b=foo
     * 
     * /With/Params
     */
    protected $params = null;

    /**
     * Initializes $domain and $path.
     * @param string $url http://example.com/Test
     */
    public function __construct($url) {
        $matches = array();
        $matchCount = preg_match('/^(http:\/\/[^\/]+\/)((.+)?)?/', $url, $matches);
        if($matchCount == 0) {
            throw new URLException('Malformed URL: ' . $url);
        }

        $this->domain = $matches[1];
        if(count($matches) == 4)
            $this->path = $matches[2];
    }

    public function getDomain() {
        return $this->domain;
    }

    public function getPath() {
        return $this->path;
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function setTargetPath($path) {
        $this->targetPath = $path;
    }

    public function setParams($params) {
        $this->params = $params;
    }

    public function getTargetPath() {
        return $this->targetPath;
    }

    public function getParams() {
        return $this->params;
    }

    /**
     * takes $_SERVER as argument and returns the corresponding url
     *
     * @param array $server
     */
    public static function fromServerArray(&$server) {
        $host = $server['HTTP_HOST'];
        $uri = $server['REQUEST_URI'];
//TODO: correct rewrite
        $cutted = array();
        preg_match('/.*index.php\?(.*)$/', $uri, $cutted);
        $uri = $cutted[1];

//TODO: implement correct protocol finder
        $protocol = 'http';

        return new URL($protocol.'://'.$host.'/'.$uri);
    }
}