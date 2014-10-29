<?php

/**
 * Facebook
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  lib_oauth
 */

namespace Cx\Lib\OAuth;

global $cl;
$cl->loadFile(ASCMS_LIBRARY_PATH . '/services/Facebook/facebook.php');

/**
 * OAuth class for facebook authentication
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Ueli Kramer <ueli.kramer@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  lib_oauth
 */
class Facebook extends OAuth
{
    /**
     * Per Contrexx default: The permission to get the primary email address (email)
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
     * Login to facebook and get the associated contrexx user.
     */
    public function login()
    {
        self::$facebook = new \Facebook(array(
            'appId' => $this->applicationData[0],
            'secret' => $this->applicationData[1],
        ));

        $user = self::$facebook->getUser();
        if (empty($user) && empty($_GET["state"])) {
            \CSRF::header('Location: ' . self::$facebook->getLoginUrl(array('scope' => self::$permissions)));
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
