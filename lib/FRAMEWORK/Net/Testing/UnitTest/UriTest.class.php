<?php
/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
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
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */
 
/**
 * Uri Test
 * 
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Michael Ritter <drissg@gmail.com>
 * @version     1.0.0
 * @package     cloudrexx
 * @subpackage  lib_net
 */

namespace Cx\Lib\Net\Testing\UnitTest;

/**
 * Uri Test
 * 
 * @todo add missing comments
 * @todo add test cases for all methods
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Michael Ritter <drissg@gmail.com>
 * @version     1.0.0
 * @package     cloudrexx
 * @subpackage  lib_net
 */
class UriTest extends \Cx\Core\Test\Model\Entity\ContrexxTestCase
{
    protected $absoluteUrls = array(
        'ftp://myuser:mypassword@ftp.is.co.za:21/rfc/rfc1808.txt#bla',
        'http://www.ietf.org/rfc/rfc2396.txt',
        'ldap://[2001:db8::7]/c=GB?objectClass?one',
        'telnet://192.0.2.16:80/',
        'https://example.org/absolute/URI/with/absolute/path/to/resource.txt',
    );
    
    protected $relativeUrls = array(
        '//example.org/scheme-relative/URI/with/absolute/path/to/resource.txt',
        '/relative/URI/with/absolute/path/to/resource.txt',
        'relative/path/to/resource.txt',
        '../../../resource.txt',
        './resource.txt#frag01',
        'resource.txt',
        '#frag01',
    );
    
    protected $urns = array(
        'mailto:John.Doe@example.com',
        'news:comp.infosystems.www.servers.unix',
        'tel:+1-816-555-1212',
        'urn:oasis:names:specification:docbook:dtd:xml:4.1.2',
    );
    
    public function testAbsoluteUri() {
        foreach ($this->absoluteUrls as $example) {
            $exampleUri = new \Cx\Lib\Net\Model\Entity\Uri($example);
            $this->assertEquals($example, (string) $exampleUri);
        }
    }
    
    public function testRelativeUri() {
        foreach ($this->relativeUrls as $example) {
            $exampleUri = new \Cx\Lib\Net\Model\Entity\Uri($example);
            $this->assertEquals($example, (string) $exampleUri);
        }
    }
    
    public function testUrn() {
        foreach ($this->urns as $example) {
            $exampleUri = new \Cx\Lib\Net\Model\Entity\Uri($example);
            $this->assertEquals($example, (string) $exampleUri);
        }
    }
    
    public function testAbsoluteUrl() {
        foreach ($this->absoluteUrls as $example) {
            $exampleUrl = new \Cx\Lib\Net\Model\Entity\Url($example);
            if ($example != (string) $exampleUrl) {
                die(var_export($exampleUrl, true));
            }
            $this->assertEquals($example, (string) $exampleUrl);
        }
    }
    
    public function testRelativeUrl() {
        foreach ($this->relativeUrls as $example) {
            $exampleUrl = new \Cx\Lib\Net\Model\Entity\Url($example);
            $this->assertEquals($example, (string) $exampleUrl);
        }
    }
}

