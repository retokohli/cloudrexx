<?php

/**
 * Contrexx
 *
 * @link      http://www.contrexx.com
 * @copyright Comvation AG 2007-2014
 * @version   Contrexx 4.0
 * 
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Contrexx" is a registered trademark of Comvation AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

include_once(ASCMS_TEST_PATH.'/testCases/ContrexxTestCase.php');
include_once(ASCMS_CORE_PATH.'/LinkSanitizer.class.php');

class LinkSanitizerTest extends ContrexxTestCase {
    public function testReplace() {
        //src, "
        $content = '<img src="index.php?cmd=a&module=b" />';      
        $result = '<img src="/cms/de/index.php?cmd=a&module=b" />';
        $this->checkSanitizing($content, $result);

        //href, '
        $content = "<a href='index.php' />";      
        $result = "<a href='/cms/de/index.php' />";
        $this->checkSanitizing($content, $result);

        //multiple matches
        $content = '<img src="first" /><img src="second" />';
        $result = '<img src="/cms/de/first" /><img src="/cms/de/second" />';
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
        $langDir = 'de/';
       
        $ls = new LinkSanitizer($offset.$langDir, $in);
        $this->assertEquals($expectedOut, $ls->replace());      
    }
}
