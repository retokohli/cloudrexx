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

use Cx\Core\Routing\Url as Url;

include_once(ASCMS_TEST_PATH.'/testCases/ContrexxTestCase.php');

class URLTest extends \ContrexxTestCase {
    public function testDomainAndPath() {
        $url = new Url('http://example.com/');
        $this->assertEquals('http://example.com/', $url->getDomain());
        $this->assertEquals('', $url->getPath());

        $url = new Url('http://example.com/Test');
        $this->assertEquals('http://example.com/', $url->getDomain());
        $this->assertEquals('Test', $url->getPath());

        $url = new Url('http://example.com/Second/Test/?a=asfd');
        $this->assertEquals('http://example.com/', $url->getDomain());
        $this->assertEquals('Second/Test/?a=asfd', $url->getPath());

        $this->assertEquals(false, $url->isRouted());
    }

    public function testSuggestions() {
        $url = new Url('http://example.com/Test');
        $this->assertEquals('Test', $url->getSuggestedTargetPath());
        $this->assertEquals('', $url->getSuggestedParams());

        $url = new Url('http://example.com/Test?foo=bar');
        $this->assertEquals('Test', $url->getSuggestedTargetPath());
        $this->assertEquals('?foo=bar', $url->getSuggestedParams());
    }

    /**
     * @expectedException \Cx\Core\Routing\UrlException
     */
    public function testMalformedConstruction() {
        $url = new Url('htp://example.com/');
    }
    
}