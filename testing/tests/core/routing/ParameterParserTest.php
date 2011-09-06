<?php
include_once ASCMS_CORE_PATH.'/routing/URL.class.php';
include_once ASCMS_CORE_PATH.'/routing/ParameterParser.class.php';

use Cx\Core\Routing\ParameterParser as ParameterParser;
use Cx\Core\Routing\URL as URL;

include_once('../testCases/ContrexxTestCase.php');

class ParameterParserTest extends \ContrexxTestCase {
    public function testExtracting() {
        $url = new URL('http://example.com/Test/?foo=bar');
        //mock the resolver (he'd normally set the params)
        $url->setParams('?foo=bar');

        $parser = new ParameterParser($url);

        $this->assertEquals('bar', $parser->get('foo'));
        $this->assertEquals(null, $parser->get('foo2'));

        $url = new URL('http://example.com/Test/?foo=bar&x=y');
        //mock the resolver (he'd normally set the params)
        $url->setParams('?foo=bar&x=y');

        $parser = new ParameterParser($url);

        $this->assertEquals('bar', $parser->get('foo'));
        $this->assertEquals('y', $parser->get('x'));
    }
}