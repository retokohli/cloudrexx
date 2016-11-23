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
 * ResolverTestProtectedPages
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
 * ResolverTestProtectedPages
 * 
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @author      SS4U <ss4u.comvation@gmail.com>
 * @version     1.0.0
 * @package     cloudrexx
 * @subpackage  core_resolver
 */
class ProtectedPagesResolverTest extends \Cx\Core\Test\Model\Entity\DatabaseTestCase
{

    /**
     * Domain url of the installation
     *
     * @var string
     */
    protected $domainUrl;

    /**
     * Mock user array test data
     *
     * @var array
     */
    protected $mockUsers = array(
        2 => array(
            'email'   => 'test@contrexx.com',
            'session' => '34hqpg9a94rpbj8r89hlhs6443',
        ),
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
        global $_CONFIG;

        parent::__construct($name, $data, $dataName);
        $this->dataSetFolder = $this->cx->getCodeBaseCorePath() . '/Routing/Testing/UnitTest/Data';

        // $_CONFIG is not defined in cli mode
        $this->domainUrl = ASCMS_PROTOCOL . '://' . $_CONFIG['domainUrl'] . $this->cx->getCodeBaseOffsetPath();
        $this->domainUrl = 'http://www.clx.local';
    }

    /**
     * Override the parent, returns null database operation class
     *
     * @return \PHPUnit_Extensions_Database_Operation_Null
     */
    public function getSetUpOperation()
    {
        return new \PHPUnit_Extensions_Database_Operation_Null();
    }

    /**
     * Sends the request to given urlSlug and
     * return the response header
     *
     * @param string    $urlSlug    Url string
     * @param integer   $userId     User id, incase request needs user login
     * @param boolean   $redirect   Boolean to follow redirects
     *
     * @return \HTTP_Request2_Response
     */
    protected function getResponse($urlSlug, $userId = 0, $redirect= true)
    {
        $url = new \Cx\Core\Routing\Url($this->domainUrl . $urlSlug);
        $url->setParams(array(
            'runTest'   => 1,
            'component' => 'Routing',
            'dataSet'   => 'ProtectedPagesDataSet',
        ));
        $request = new \HTTP_Request2(
             $url->toString(),
            \HTTP_Request2::METHOD_POST
        );
        if ($userId) {
            $request->addCookie(
                'PHPSESSID',
                $this->mockUsers[$userId]['session']
            );
        }
        $request->setConfig(array(
            'ssl_verify_host' => false,
            'ssl_verify_peer' => false,
            'follow_redirects' => $redirect ? true : false,
            'strict_redirects' => $redirect ? true : false,
        ));
        $response = $request->send();

        return $response;
    }

    /**
     * @dataProvider resolverDataProvider Data value provider
     */
    public function testProtectedPageResolver(
        $language = null,
        $userId = null,
        $inputSlug = '',
        $expectedSlug = null,
        $requiresLogin = false
    ) {
        if ($expectedSlug === null) {
            $expectedSlug = $inputSlug;
        }
        $expectedUrlString = $langCode = $urlString = '';
        if ($language !== null) {
            $langCode           = \FWLanguage::getLanguageCodeById($language);
            $urlString         .= '/' . $langCode;
            $expectedUrlString .= '/' . $langCode;
        }
        $urlString         .= '/'. $inputSlug;
        $expectedUrlString .= '/'. $expectedSlug;
        $expectedStatus     = !$requiresLogin ? 200 : 302;

        $langUrlString      = (!empty($langCode) ? '/'. $langCode : '');
        $response = $this->getResponse($urlString, $userId, !$requiresLogin);
        $this->assertTrue($response->getStatus() == $expectedStatus);
        if ($requiresLogin) {
            $this->assertTrue($response->isRedirect());
            $redirectionUrl = new \Cx\Core\Routing\Url($response->getHeader('location'));
            $redirectionUrlString = $langUrlString . '/' . $redirectionUrl->getSuggestedTargetPath();
            $this->assertEquals(
                $langUrlString . '/Login',
                $redirectionUrlString
            );
        }
        $effectiveUrl       = new \Cx\Core\Routing\Url($response->getEffectiveUrl());
        $effectiveUrlString = $langUrlString . '/' . $effectiveUrl->getSuggestedTargetPath();

        $this->assertEquals($expectedUrlString, $effectiveUrlString);
    }

    /**
     * Test records for the testResolver method
     *
     * @return array
     */
    public function resolverDataProvider()
    {
        return array(
            array(1, 2, 'Simple-content-page'),
            array(1, null, 'Simple-content-page', null, true),
            array(1, 2, 'SymLink-page'),
            array(1, null, 'SymLink-page', null, true),
            array(1, 2, 'Application-page'),
            array(1, null, 'Application-page', null, true),
            array(1, 2, 'Redirect-page', 'Simple-content-page'),
            array(1, null, 'Redirect-page', null, true),

            // test home page
            array(2, ''),
        );
    }
}
