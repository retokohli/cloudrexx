<?php
namespace Cx\Core\Routing;

class ParameterParserException extends \Exception {};

/**
 * Takes an URL and tries to find the parameters.
 */
class ParameterParser {
    protected $url = null;

    /**
     * The regex used to extract the parameters.
     * 
     * This default regex allow parsing of standard GET parameters.
     *
     * @var string
     */
    protected $regex = '/^\?(([a-zA-Z0-9-]+)=([a-zA-Z0-9-]+))(&([a-zA-Z0-9-]+)=([a-zA-Z0-9-]+))*$/';

    /**
     * The parsed parameters.
     *
     * @var array
     */
    protected $params = array();

    /**
     * @param URL $url the URL
     * @throws ParameterParserException
     */
    public function __construct($url) {
        $this->url = $url;
        $this->extract();
    }

    /**
     * Saves params found in $this->url in $this->params.
     *
     * @throws ParameterParserException
     */
    protected function extract() {
        $params = $this->url->getParams();

        if($params === '') //nothing to extract
            return;

        $matches = array();
        $matchCount = preg_match($this->regex, $params, $matches);
        if($matchCount == 0)
            throw new ParameterParserException(\get_class($this) . ' couldn\'t extract parameters from parameter string ' . $params);
        
        /*
          matches now holds 
          [ 'whole string', 'first key/val pair', 'first key', 'first val', 'second key/val pair', ... ]
         */
        $pairCount = (count($matches) - 1) / 3;
        for($i = 0; $i < $pairCount; $i++) {
            $baseIndex = $i*3+1;
            $key = $matches[$baseIndex+1];
            $val = $matches[$baseIndex+2];

            $this->params[$key] = $val;
        }
    }

    /**
     * List of all parameter names.
     *
     * @return array
     */
    public function getParameterList() {
        return array_keys($this->params);
    }

    /**
     * Gets the specified parameters value
     *
     * @param string $key
     * @return string | null the val if set or null.
     */
    public function get($key) {
        if(isset($this->params[$key]))
            return $this->params[$key];

        return null;
    }
}
