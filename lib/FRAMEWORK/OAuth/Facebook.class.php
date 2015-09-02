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
 * Facebook
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  lib_oauth
 */

namespace Cx\Lib\OAuth;

global $cl;
$cl->loadFile(ASCMS_LIBRARY_PATH . '/services/Facebook/facebook.php');

/**
 * OAuth class for facebook authentication
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Ueli Kramer <ueli.kramer@comvation.com>
 * @version     1.0.0
 * @package     cloudrexx
 * @subpackage  lib_oauth
 */
class Facebook extends OAuth
{
    /**
     * Per Cloudrexx default: The permission to get the primary email address (email)
     *
     * @var array the permissions to ask for
     */
    private static $permissions = array(
        'email',
    );

    /**
     * @var the object of the third party library
     */
    private static $facebook;

    /**
     * @var the user data of the logged in social media user
     */
    protected static $userdata;

    const OAUTH_PROVIDER = 'facebook';

    /**
     * Login to facebook and get the associated cloudrexx user.
     */
    public function login()
    {
        self::$facebook = new \Facebook(array(
            'appId' => $this->applicationData[0],
            'secret' => $this->applicationData[1],
        ));

        $user = self::$facebook->getUser();
        if (empty($user) && empty($_GET["state"])) {
            \Cx\Core\Csrf\Controller\Csrf::header('Location: ' . self::$facebook->getLoginUrl(array('scope' => self::$permissions)));
            exit;
        }

        self::$userdata = $this->getUserData();
        $this->getContrexxUser($user);
    }

    /**
     * Get all the user data from facebook server.
     *
     * @return array
     */
    public function getUserData()
    {
        return self::$facebook->api('/me');
    }
}
