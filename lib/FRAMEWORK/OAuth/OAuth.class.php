<?php

namespace Cx\Lib\OAuth;

class OAuth_Exception extends \Exception
{
}

/**
 * OAuth superclass
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Ueli Kramer <ueli.kramer@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  lib_oauth
 */

abstract class OAuth implements OAuthInterface
{
    /**
     * @var array the necessary data to connect to the social media platform
     */
    protected $applicationData = array();

    /**
     * Sets the application id and secret key for login usage of social media platform
     * For google there is also an api key
     *
     * @param array the application configuration data
     */
    public function setApplicationData($applicationData)
    {
        $this->applicationData = $applicationData;
    }

    /**
     * Searchs for an user with the given user id of the social media platform.
     * If there is no user, create one and directly log in.
     *
     * @param string $oauth_id the user id of the social media platform
     */
    protected function getContrexxUser($oauth_id)
    {
        //\DBG::activate();
        $arrSettings = \User_Setting::getSettings();

        $provider = $this::OAUTH_PROVIDER;
        $FWUser = \FWUser::getFWUserObject();
        $objUser = $FWUser->objUser->getByNetwork($provider, $oauth_id);
        if (!$objUser) {
            $objUser = new \User();
            $objUser->setUsername($this->getEmail());
            $objUser->setEmail($this->getEmail());
            $objUser->setActiveStatus(1);
            $objUser->setAdminStatus(0);
            $objUser->setGroups(explode(',', $arrSettings['assigne_to_groups']['value']));
            $objUser->setProfile(
                array(
                    'firstname' => array($this->getFirstname()),
                    'lastname' => array($this->getLastname()),
                )
            );
            if (!$objUser->store() && !$FWUser->objUser->login()) {
                // if the email address already exists but not with the given oauth-provider
                throw new OAuth_Exception;
            } elseif($FWUser->objUser->login()) {
                $objUser = $FWUser->objUser;
            }
            $objUser->loadNetworks();
            $objUser->setNetwork($provider, $oauth_id);
        }
        $FWUser->loginUser($objUser);
    }

    /**
     * @static
     * @return array the configuration parameters as language array key
     */
    public static function configParams()
    {
        return $configParams = array(
            'TXT_ACCESS_SOCIALLOGIN_PROVIDER_APP_ID',
            'TXT_ACCESS_SOCIALLOGIN_PROVIDER_SECRET',
        );
    }

    public function getEmail()
    {
        /** @var $userdata array */
        return $this::$userdata['email'];
    }

    public function getFirstname()
    {
        /** @var $userdata array */
        return $this::$userdata['first_name'];
    }

    public function getLastname()
    {
        /** @var $userdata array */
        return $this::$userdata['last_name'];
    }
}