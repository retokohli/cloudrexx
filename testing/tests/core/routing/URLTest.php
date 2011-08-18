<?php
include_once ASCMS_CORE_PATH.'/routing/URL.class.php';
use Cx\Core\Routing\URL as URL;

class URLTestCase extends \PHPUnit_Framework_TestCase {
    public function testConstruction() {
        $url = new URL('http://example.com/');
        $this->assertEquals('http://example.com/', $url->getDomain());
        $this->assertEquals('', $url->getPath());

        $url = new URL('http://example.com/Test');
        $this->assertEquals('http://example.com/', $url->getDomain());
        $this->assertEquals('Test', $url->getPath());

        $url = new URL('http://example.com/Second/Test/?a=asfd');
        $this->assertEquals('http://example.com/', $url->getDomain());
        $this->assertEquals('Second/Test/?a=asfd', $url->getPath());

    }

    /**
     * @expectedException \Cx\Core\Routing\URLException
     */
    public function testMalformedConstruction1() {
        $url = new URL('htp://example.com/');
    }
    
}