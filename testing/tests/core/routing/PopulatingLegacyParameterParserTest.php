<?php
include_once ASCMS_CORE_PATH.'/routing/URL.class.php';
include_once ASCMS_CORE_PATH.'/routing/PopulatingLegacyParameterParser.class.php';

include_once('../testCases/ContrexxTestCase.php');

use Cx\Core\Routing\PopulatingLegacyParameterParser as PopulatingLegacyParameterParser;
use Cx\Core\Routing\URL as URL;

class PopulatingLegacyParameterParserTest extends \ContrexxTestCase {
    public function testPopulating() {
        $url = new URL('http://example.com/Test/?foo=bar');
        //mock the resolver (he'd normally set the params)
        $url->setParams('?foo=bar');

        //our mock $_GET and $_POST
        $get = array();
        $request = array();
        $parser = new PopulatingLegacyParameterParser($url, $get, $request);

        $this->assertEquals('bar', $get['foo']);
        $this->assertEquals('bar', $request['foo']);
    }
}