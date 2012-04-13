<?php
include_once ASCMS_CORE_PATH.'/routing/URL.class.php';
use Cx\Core\Routing\URL as URL;

include_once('../testCases/ContrexxTestCase.php');

class URLTest extends \ContrexxTestCase {
    public function testDomainAndPath() {
        $url = new URL('http://example.com/');
        $this->assertEquals('http://example.com/', $url->getDomain());
        $this->assertEquals('', $url->getPath());

        $url = new URL('http://example.com/Test');
        $this->assertEquals('http://example.com/', $url->getDomain());
        $this->assertEquals('Test', $url->getPath());

        $url = new URL('http://example.com/Second/Test/?a=asfd');
        $this->assertEquals('http://example.com/', $url->getDomain());
        $this->assertEquals('Second/Test/?a=asfd', $url->getPath());

        $this->assertEquals(false, $url->isRouted());
    }

    public function testSuggestions() {
        $url = new URL('http://example.com/Test');
        $this->assertEquals('Test', $url->getSuggestedTargetPath());
        $this->assertEquals('', $url->getSuggestedParams());

        $url = new URL('http://example.com/Test?foo=bar');
        $this->assertEquals('Test', $url->getSuggestedTargetPath());
        $this->assertEquals('?foo=bar', $url->getSuggestedParams());
    }

    /**
     * @expectedException \Cx\Core\Routing\URLException
     */
    public function testMalformedConstruction() {
        $url = new URL('htp://example.com/');
    }
    
}