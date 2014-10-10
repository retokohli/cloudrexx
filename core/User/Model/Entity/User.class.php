<?php

namespace Cx\Core\User\Model\Entity;

/**
 * Cx\Core\User\Model\Entity\User
 */
class User extends \Cx\Model\Base\EntityBase {
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $is_admin
     */
    private $is_admin;

    /**
     * @var string $username
     */
    private $username;

    /**
     * @var string $password
     */
    private $password;

    /**
     * @var string $auth_token
     */
    private $auth_token;

    /**
     * @var integer $auth_token_timeout
     */
    private $auth_token_timeout;

    /**
     * @var integer $regdate
     */
    private $regdate;

    /**
     * @var integer $expiration
     */
    private $expiration;

    /**
     * @var integer $validity
     */
    private $validity;

    /**
     * @var integer $last_auth
     */
    private $last_auth;

    /**
     * @var integer $last_auth_status
     */
    private $last_auth_status;

    /**
     * @var integer $last_activity
     */
    private $last_activity;

    /**
     * @var string $email
     */
    private $email;

    /**
     * @var string $email_access
     */
    private $email_access;

    /**
     * @var integer $frontend_lang_id
     */
    private $frontend_lang_id;

    /**
     * @var integer $backend_lang_id
     */
    private $backend_lang_id;

    /**
     * @var integer $active
     */
    private $active;

    /**
     * @var integer $verified
     */
    private $verified;

    /**
     * @var integer $primary_group
     */
    private $primary_group;

    /**
     * @var string $profile_access
     */
    private $profile_access;

    /**
     * @var string $restore_key
     */
    private $restore_key;

    /**
     * @var integer $restore_key_time
     */
    private $restore_key_time;

    /**
     * @var string $u2u_active
     */
    private $u2u_active;

    /**
     * @var Cx\Core\User\Model\Entity\UserProfile
     */
    private $userProfile;

    /**
     * @var Cx\Core\User\Model\Entity\Group
     */
    private $group;

    public function __construct()
    {
        $this->group = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set is_admin
     *
     * @param integer $isAdmin
     */
    public function setIsAdmin($isAdmin)
    {
        $this->is_admin = $isAdmin;
    }

    /**
     * Get is_admin
     *
     * @return integer $isAdmin
     */
    public function getIsAdmin()
    {
        return $this->is_admin;
    }

    /**
     * Set username
     *
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Get username
     *
     * @return string $username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Get password
     *
     * @return string $password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set auth_token
     *
     * @param string $authToken
     */
    public function setAuthToken($authToken)
    {
        $this->auth_token = $authToken;
    }

    /**
     * Get auth_token
     *
     * @return string $authToken
     */
    public function getAuthToken()
    {
        return $this->auth_token;
    }

    /**
     * Set auth_token_timeout
     *
     * @param integer $authTokenTimeout
     */
    public function setAuthTokenTimeout($authTokenTimeout)
    {
        $this->auth_token_timeout = $authTokenTimeout;
    }

    /**
     * Get auth_token_timeout
     *
     * @return integer $authTokenTimeout
     */
    public function getAuthTokenTimeout()
    {
        return $this->auth_token_timeout;
    }

    /**
     * Set regdate
     *
     * @param integer $regdate
     */
    public function setRegdate($regdate)
    {
        $this->regdate = $regdate;
    }

    /**
     * Get regdate
     *
     * @return integer $regdate
     */
    public function getRegdate()
    {
        return $this->regdate;
    }

    /**
     * Set expiration
     *
     * @param integer $expiration
     */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;
    }

    /**
     * Get expiration
     *
     * @return integer $expiration
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * Set validity
     *
     * @param integer $validity
     */
    public function setValidity($validity)
    {
        $this->validity = $validity;
    }

    /**
     * Get validity
     *
     * @return integer $validity
     */
    public function getValidity()
    {
        return $this->validity;
    }

    /**
     * Set last_auth
     *
     * @param integer $lastAuth
     */
    public function setLastAuth($lastAuth)
    {
        $this->last_auth = $lastAuth;
    }

    /**
     * Get last_auth
     *
     * @return integer $lastAuth
     */
    public function getLastAuth()
    {
        return $this->last_auth;
    }

    /**
     * Set last_auth_status
     *
     * @param integer $lastAuthStatus
     */
    public function setLastAuthStatus($lastAuthStatus)
    {
        $this->last_auth_status = $lastAuthStatus;
    }

    /**
     * Get last_auth_status
     *
     * @return integer $lastAuthStatus
     */
    public function getLastAuthStatus()
    {
        return $this->last_auth_status;
    }

    /**
     * Set last_activity
     *
     * @param integer $lastActivity
     */
    public function setLastActivity($lastActivity)
    {
        $this->last_activity = $lastActivity;
    }

    /**
     * Get last_activity
     *
     * @return integer $lastActivity
     */
    public function getLastActivity()
    {
        return $this->last_activity;
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email_access
     *
     * @param string $emailAccess
     */
    public function setEmailAccess($emailAccess)
    {
        $this->email_access = $emailAccess;
    }

    /**
     * Get email_access
     *
     * @return string $emailAccess
     */
    public function getEmailAccess()
    {
        return $this->email_access;
    }

    /**
     * Set frontend_lang_id
     *
     * @param integer $frontendLangId
     */
    public function setFrontendLangId($frontendLangId)
    {
        $this->frontend_lang_id = $frontendLangId;
    }

    /**
     * Get frontend_lang_id
     *
     * @return integer $frontendLangId
     */
    public function getFrontendLangId()
    {
        return $this->frontend_lang_id;
    }

    /**
     * Set backend_lang_id
     *
     * @param integer $backendLangId
     */
    public function setBackendLangId($backendLangId)
    {
        $this->backend_lang_id = $backendLangId;
    }

    /**
     * Get backend_lang_id
     *
     * @return integer $backendLangId
     */
    public function getBackendLangId()
    {
        return $this->backend_lang_id;
    }

    /**
     * Set active
     *
     * @param integer $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * Get active
     *
     * @return integer $active
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set verified
     *
     * @param integer $verified
     */
    public function setVerified($verified)
    {
        $this->verified = $verified;
    }

    /**
     * Get verified
     *
     * @return integer $verified
     */
    public function getVerified()
    {
        return $this->verified;
    }

    /**
     * Set primary_group
     *
     * @param integer $primaryGroup
     */
    public function setPrimaryGroup($primaryGroup)
    {
        $this->primary_group = $primaryGroup;
    }

    /**
     * Get primary_group
     *
     * @return integer $primaryGroup
     */
    public function getPrimaryGroup()
    {
        return $this->primary_group;
    }

    /**
     * Set profile_access
     *
     * @param string $profileAccess
     */
    public function setProfileAccess($profileAccess)
    {
        $this->profile_access = $profileAccess;
    }

    /**
     * Get profile_access
     *
     * @return string $profileAccess
     */
    public function getProfileAccess()
    {
        return $this->profile_access;
    }

    /**
     * Set restore_key
     *
     * @param string $restoreKey
     */
    public function setRestoreKey($restoreKey)
    {
        $this->restore_key = $restoreKey;
    }

    /**
     * Get restore_key
     *
     * @return string $restoreKey
     */
    public function getRestoreKey()
    {
        return $this->restore_key;
    }

    /**
     * Set restore_key_time
     *
     * @param integer $restoreKeyTime
     */
    public function setRestoreKeyTime($restoreKeyTime)
    {
        $this->restore_key_time = $restoreKeyTime;
    }

    /**
     * Get restore_key_time
     *
     * @return integer $restoreKeyTime
     */
    public function getRestoreKeyTime()
    {
        return $this->restore_key_time;
    }

    /**
     * Set u2u_active
     *
     * @param string $u2uActive
     */
    public function setU2uActive($u2uActive)
    {
        $this->u2u_active = $u2uActive;
    }

    /**
     * Get u2u_active
     *
     * @return string $u2uActive
     */
    public function getU2uActive()
    {
        return $this->u2u_active;
    }

    /**
     * Set userProfile
     *
     * @param Cx\Core\User\Model\Entity\UserProfile $userProfile
     */
    public function setUserProfile(\Cx\Core\User\Model\Entity\UserProfile $userProfile)
    {
        $this->userProfile = $userProfile;
    }

    /**
     * Get userProfile
     *
     * @return Cx\Core\User\Model\Entity\UserProfile $userProfile
     */
    public function getUserProfile()
    {
        return $this->userProfile;
    }

    /**
     * Add group
     *
     * @param Cx\Core\User\Model\Entity\Group $group
     */
    public function addGroup(\Cx\Core\User\Model\Entity\Group $group)
    {
        $this->group[] = $group;
    }

    /**
     * Get group
     *
     * @return Doctrine\Common\Collections\Collection $group
     */
    public function getGroup()
    {
        return $this->group;
    }
}
