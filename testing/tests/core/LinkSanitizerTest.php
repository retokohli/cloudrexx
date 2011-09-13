<?php
include_once('../testCases/ContrexxTestCase.php');
include_once(ASCMS_CORE_PATH.'/LinkSanitizer.class.php');

class LinkSanitizerTest extends ContrexxTestCase {
    public function testReplace() {
        //src, "
        $content = '<img src="index.php?cmd=a&module=b" />';      
        $result = '<img src="/cms/index.php?cmd=a&module=b" />';
        $this->checkSanitizing($content, $result);

        //href, '
        $content = "<a href='index.php' />";      
        $result = "<a href='/cms/index.php' />";
        $this->checkSanitizing($content, $result);

        //multiple matches
        $content = '<img src="first" /><img src="second" />';
        $result = '<img src="/cms/first" /><img src="/cms/second" />';
        $this->checkSanitizing($content, $result);

        //absolute links preserval
        $content = '<a href="/cms/index.php" />'; 
        $result = $content;
        $this->checkSanitizing($content, $result);

        //absolute links preserval
        $content = '<a href="/images/pic.jpg" />';      
        $result = $content;
        $this->checkSanitizing($content, $result);

        //foreign links preserval
        $content = '<a href="http://www.google.ch" />';      
        $result = $content;
        $this->checkSanitizing($content, $result);

    }

    protected function checkSanitizing($in, $expectedOut) {
        $offset = '/cms/';
       
        $ls = new LinkSanitizer($offset, $in);
        $this->assertEquals($expectedOut, $ls->replace());      
    }
}
