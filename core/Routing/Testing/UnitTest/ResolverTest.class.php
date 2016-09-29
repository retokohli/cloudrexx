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
        $expectedSlug = null
    ) {
        global $url;

        if (null === $expectedSlug) {
            $expectedSlug = $inputSlug;
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

            //array(1, '?section=Access', 'Simple-content-page'), // it uses global variable $_REQUEST
        );
    }
}
