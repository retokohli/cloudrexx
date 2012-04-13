<?php
namespace Cx\Core\Routing;

require_once ASCMS_CORE_PATH.'/routing/ParameterParser.class.php';

class PopulatingLegacyParameterParserException extends ParameterParserException {};

/**
 * Extends ParameterParser by automatically populating values to $_GET and $_REQUEST
 */
class PopulatingLegacyParameterParser extends ParameterParser {

    /**
     * @var array reference to $_GET
     */
    protected $get;
    /**
     * @var array reference to $_REQUEST
     */
    protected $request;

    /**
     * @param URL $url the URL
     * @param array $get $_GET
     * @param array $request $_REQUEST
     */
    public function __construct($url, &$get, &$request) {
        parent::__construct($url);

        $this->get = &$get;
        $this->request = &$request;

        $this->populate();
    }

    /**
     * Populates $this->params to $_GET and $_REQUEST.
     */
    protected function populate() {
        $this->get = array_merge($this->get, $this->params);
        $this->request = array_merge($this->request, $this->params);
    }   
}
