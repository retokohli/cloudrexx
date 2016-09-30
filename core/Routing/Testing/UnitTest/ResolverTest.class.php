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
 * ResolverTest
 * 
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @author      SS4U <ss4u.comvation@gmail.com>
 * @version     1.0.0
 * @package     cloudrexx
 * @subpackage  core_resolver
 */

namespace Cx\Core\Routing\Testing\UnitTest;

/**
 * ResolverTest
 * 
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @author      SS4U <ss4u.comvation@gmail.com>
 * @version     1.0.0
 * @package     cloudrexx
 * @subpackage  core_resolver
 */
class ResolverTest extends \Cx\Core\Test\Model\Entity\DatabaseTestCase
{
    protected $mockFallbackLanguages = array(
        1 => 2,
        2 => 3
    );

    /**
     * Constructs a test case with the given name.
     *
     * @param string    $name
     * @param array     $data
     * @param string    $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->dataSetFolder = $this->cx->getCodeBaseCorePath() . '/Routing/Testing/UnitTest/Data';
    }

    /**
     * @dataProvider resolverDataProvider Data value provider
     */
    public function testResolver(
        $language = null,
        $inputSlug = '',
        $expectedSlug = null,
        $request = array()
    ) {
        global $url;

        if (null === $expectedSlug) {
            $expectedSlug = $inputSlug;
        }
        if (!empty($request)) {
            $_REQUEST = array_merge($_REQUEST, $request);
            $_GET     = array_merge($_GET, $request);
        }
        $urlString = '';
        if (null !== $language) {
            $langCode   = \FWLanguage::getLanguageCodeById($language);
            $urlString .= '/' . $langCode;
        }
        $urlString .= '/'. $inputSlug;
        try {
            \DBG::log('http://example.com' . $urlString);
            $url      = new \Cx\Core\Routing\Url('http://example.com' . $urlString);
            $resolver = new \Cx\Core\Routing\Resolver($url, $language, self::$em, '', $this->mockFallbackLanguages, false);
            $resolver->resolve();
        } catch (\Exception $ex) {
            // Nothing to do
        }
        $p = $resolver->getPage();
        $this->assertInstanceOf('\Cx\Core\ContentManager\Model\Entity\Page', $p);
        $this->assertEquals($expectedSlug, $p->getSlug());
    }

    /**
     * Test records for the testResolver method
     *
     * @return array
     */
    public function resolverDataProvider()
    {
        return array(
            // Content
            array(2, 'Simple-content-page'),
            // Application
            array(2, 'Simple-application-page'),
            // Fallback -> Content
            array(1, 'Fallback-to-content-page'),
            // Fallback -> Application
            array(1, 'Fallback-to-application-page'),
            // Symlink -> Content
            array(2, 'Simple-symlink-to-content-page'),
            // Symlink -> Application
            array(2, 'Simple-symlink-to-application-page'),
            // Redirect -> Content
            array(2, 'Simple-redirect-to-content-page', 'Simple-content-page'),
            // Redirect -> Application
            array(2, 'Simple-redirect-to-application-page', 'Simple-application-page'),
            // Alias -> Content
            array(null, 'Simple-alias-for-content-page', 'Simple-content-page'),
            // Alias -> Application
            array(null, 'Simple-alias-for-application-page', 'Simple-application-page'),
            // Redirect -> Symlink -> Content
            array(2, 'Redirect-to-symlink-content-page', 'Simple-symlink-to-content-page'),
            // Redirect -> Symlink -> Application
            array(2, 'Redirect-to-symlink-application-page', 'Simple-symlink-to-application-page'),
            // Redirect -> Fallback -> Content
            array(1, 'Redirect-to-fallback-content-page', 'Simple-content-page'),
            // Redirect -> Fallback -> Application
            array(1, 'Redirect-to-fallback-application-page', 'Simple-application-page'),
            // Fallback -> Symlink -> Content
            array(1, 'Fallback-symlink-to-content-page'),
            // Fallback -> Symlink -> Application
            array(1, 'Fallback-symlink-to-application-page'),
            // Symlink -> Redirect -> Content
            array(2, 'Symlink-to-redirect-to-content', 'Simple-content-page'),
            // Symlink -> Redirect -> Application
            array(2, 'Symlink-to-redirect-to-application', 'Simple-application-page'),
            // Symlink -> Fallback -> Content
            array(1, 'Symlink-to-fallback-to-content'),
            // Symlink -> Fallback -> Application
            array(1, 'Symlink-to-fallback-to-application'),
            // Alias -> Redirect -> Content
            array(null, 'alias-redirect-to-content-page', 'Simple-content-page'),
            // Alias -> Redirect -> Application
            array(null, 'alias-redirect-to-application-page', 'Simple-application-page'),
            // Alias -> Fallback -> Content
            array(null, 'alias-fallback-to-content-page'),
            // Alias -> Fallback -> Application
            array(null, 'alias-fallback-to-application-page'),
            // Alias -> Symlink -> Content
            array(null, 'alias-symlink-to-content-page', 'Simple-symlink-to-content-page'),
            // Alias -> Symlink -> Application
            array(null, 'alias-symlink-to-application-page', 'Simple-symlink-to-application-page'),
            // Symlink -> Fallback -> Redirect -> Content
            array(1, 'symlink-fallback-to-redirect-to-content', 'Simple-content-page'),
            // Symlink -> Fallback -> Redirect -> Application
            array(1, 'symlink-fallback-to-redirect-to-application', 'Simple-application-page'),
            // Symlink -> Redirect -> Fallback -> Content
            array(1, 'symlink-redirect-to-fallback-to-content'),
            // Symlink -> Redirect -> Fallback -> Application
            array(1, 'symlink-redirect-to-fallback-to-application'),
            // Fallback -> Symlink -> Redirect -> Content
            array(1, 'Fallback-symlink-to-redirect-to-content', 'Simple-content-page'),
            // Fallback -> Symlink -> Redirect -> Application
            array(1, 'Fallback-symlink-to-redirect-to-application', 'Simple-application-page'),
            // Fallback -> Redirect -> Symlink -> Content
            array(1, 'Fallback-redirect-to-symlink-content-page', 'Simple-symlink-to-content-page'),
            // Fallback -> Redirect -> Symlink -> Application
            array(1, 'Fallback-redirect-to-symlink-application-page', 'Simple-symlink-to-application-page'),
            // Redirect -> Symlink -> Fallback -> Content
            array(1, 'redirect-to-symlink-to-fallback-to-content', 'Symlink-to-fallback-to-content'),
            // Redirect -> Symlink -> Fallback -> Application
            array(1, 'redirect-to-symlink-to-fallback-to-application', 'Symlink-to-fallback-to-application'),
            // Redirect -> Fallback -> Symlink -> Content
            array(1, 'redirect-to-fallback-to-symlink-to-content', 'Simple-symlink-to-content-page'),
            // Redirect -> Fallback -> Symlink -> Application
            array(1, 'redirect-to-fallback-to-symlink-to-application', 'Simple-symlink-to-application-page'),
            // Alias -> Symlink -> Redirect -> Content
            array(null, 'alias-symlink-redirect-to-content', 'Symlink-to-redirect-to-content'),
            // Alias -> Symlink -> Redirect -> Application
            array(null, 'alias-symlink-to-redirect-to-application', 'Symlink-to-redirect-to-application'),
            // Alias -> Fallback -> Symlink -> Content
            array(null, 'alias-fallback-symlink-to-content-page'),
            // Alias -> Fallback -> Symlink -> Application
            array(null, 'alias-fallback-symlink-to-application-page'),
            // Alias -> Fallback -> Redirect -> Content
            array(null, 'alias-fallback-redirect-to-content-page'),
            // Alias -> Fallback -> Redirect -> Application
            array(null, 'alias-fallback-redirect-to-application-page'),
            // Alias -> Redirect -> Symlink -> Content
            array(null, 'alias-redirect-to-symlink-content-page', 'Simple-symlink-to-content-page'),
            // Alias -> Redirect -> Symlink -> Application
            array(null, 'alias-redirect-to-symlink-application-page', 'Simple-symlink-to-application-page'),
            // Alias -> Redirect -> Fallback -> Content
            array(null, 'alias-redirect-to-fallback-content-page'),
            // Alias -> Redirect -> Fallback -> Application
            array(null, 'alias-redirect-to-fallback-application-page'),
            // Alias -> Symlink -> Redirect -> Fallback -> Content
            array(null, 'alias-symlink-redirect-to-fallback-to-content'),
            // Alias -> Symlink -> Redirect -> Fallback -> Application
            array(null, 'alias-symlink-redirect-to-fallback-to-application'),
            // Alias -> Symlink -> Fallback -> Redirect -> Content
            array(null, 'alias-symlink-fallback-to-redirect-to-content'),
            // Alias -> Symlink -> Fallback -> Redirect -> Application
            array(null, 'alias-symlink-fallback-to-redirect-to-application'),
            // Alias -> Fallback -> Redirect -> Symlink -> Content
            array(null, 'alias-Fallback-redirect-to-symlink-content-page'),
            // Alias -> Fallback -> Redirect -> Symlink -> Application
            array(null, 'alias-Fallback-redirect-to-symlink-application-page'),
            // Alias -> Fallback -> Symlink -> Redirect -> Content
            array(null, 'alias-Fallback-symlink-to-redirect-to-content'),
            // Alias -> Fallback -> Symlink -> Redirect -> Application
            array(null, 'alias-Fallback-symlink-to-redirect-to-application'),
            // Alias -> Redirect -> Fallback -> Symlink -> Content
            array(null, 'alias-redirect-to-fallback-to-symlink-to-content'),
            // Alias -> Redirect -> Fallback -> Symlink -> Application
            array(null, 'alias-redirect-to-fallback-to-symlink-to-application'),
            // Alias -> Redirect -> Symlink -> Fallback -> Content
            array(null, 'alias-redirect-to-symlink-to-fallback-to-content'),
            // Alias -> Redirect -> Symlink -> Fallback -> Application
            array(null, 'alias-redirect-to-symlink-to-fallback-to-application'),
            
            // duplicate slugs
            array(2, 'News'),
            array(2, 'Duplicate-News'),
            array(2, 'Home'),
            array(2, 'Duplicate-Home'),

            // test home page
            array(2, '', 'Home'),

            // legacy page test
            array(2, '?section=Access', 'Simple-application-page', array('section' => 'Access')),
        );
    }

    /**
     * @expectedException Cx\Core\Core\Controller\InstanceException
     */
    public function testInExistPage()
    {
        global $url;

        $url      = new \Cx\Core\Routing\Url('http://example.com/en/not-exists-url');
        $resolver = new \Cx\Core\Routing\Resolver($url, 2, self::$em, '', $this->mockFallbackLanguages, false);
        $resolver->resolve();
    }

}
