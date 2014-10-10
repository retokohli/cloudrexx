<?php

namespace Cx\Core\User\Model\Entity;

/**
 * Cx\Core\User\Model\Entity\UserProfile
 */
class UserProfile extends \Cx\Model\Base\EntityBase {
    /**
     * @var integer $user_id
     */
    private $user_id;

    /**
     * @var string $gender
     */
    private $gender;

    /**
     * @var string $firstname
     */
    private $firstname;

    /**
     * @var string $lastname
     */
    private $lastname;

    /**
     * @var string $company
     */
    private $company;

    /**
     * @var string $address
     */
    private $address;

    /**
     * @var string $city
     */
    private $city;

    /**
     * @var string $zip
     */
    private $zip;

    /**
     * @var integer $country
     */
    private $country;

    /**
     * @var string $phone_office
     */
    private $phone_office;

    /**
     * @var string $phone_private
     */
    private $phone_private;

    /**
     * @var string $phone_mobile
     */
    private $phone_mobile;

    /**
     * @var string $phone_fax
     */
    private $phone_fax;

    /**
     * @var string $birthday
     */
    private $birthday;

    /**
     * @var string $website
     */
    private $website;

    /**
     * @var string $profession
     */
    private $profession;

    /**
     * @var string $interests
     */
    private $interests;

    /**
     * @var string $signature
     */
    private $signature;

    /**
     * @var string $picture
     */
    private $picture;

    /**
     * @var Cx\Core\User\Model\Entity\User
     */
    private $users;

    /**
     * @var Cx\Core\User\Model\Entity\ProfileTitle
     */
    private $userTitle;

    /**
     * @var Cx\Core\User\Model\Entity\UserAttribute
     */
    private $userAttribute;

    public function __construct()
    {
        $this->userAttribute = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set user_id
     *
     * @param integer $userId
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;
    }

    /**
     * Get user_id
     *
     * @return integer $userId
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set gender
     *
     * @param string $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * Get gender
     *
     * @return string $gender
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * Get firstname
     *
     * @return string $firstname
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * Get lastname
     *
     * @return string $lastname
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set company
     *
     * @param string $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * Get company
     *
     * @return string $company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set address
     *
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * Get address
     *
     * @return string $address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set city
     *
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * Get city
     *
     * @return string $city
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set zip
     *
     * @param string $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * Get zip
     *
     * @return string $zip
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set country
     *
     * @param integer $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * Get country
     *
     * @return integer $country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set phone_office
     *
     * @param string $phoneOffice
     */
    public function setPhoneOffice($phoneOffice)
    {
        $this->phone_office = $phoneOffice;
    }

    /**
     * Get phone_office
     *
     * @return string $phoneOffice
     */
    public function getPhoneOffice()
    {
        return $this->phone_office;
    }

    /**
     * Set phone_private
     *
     * @param string $phonePrivate
     */
    public function setPhonePrivate($phonePrivate)
    {
        $this->phone_private = $phonePrivate;
    }

    /**
     * Get phone_private
     *
     * @return string $phonePrivate
     */
    public function getPhonePrivate()
    {
        return $this->phone_private;
    }

    /**
     * Set phone_mobile
     *
     * @param string $phoneMobile
     */
    public function setPhoneMobile($phoneMobile)
    {
        $this->phone_mobile = $phoneMobile;
    }

    /**
     * Get phone_mobile
     *
     * @return string $phoneMobile
     */
    public function getPhoneMobile()
    {
        return $this->phone_mobile;
    }

    /**
     * Set phone_fax
     *
     * @param string $phoneFax
     */
    public function setPhoneFax($phoneFax)
    {
        $this->phone_fax = $phoneFax;
    }

    /**
     * Get phone_fax
     *
     * @return string $phoneFax
     */
    public function getPhoneFax()
    {
        return $this->phone_fax;
    }

    /**
     * Set birthday
     *
     * @param string $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    /**
     * Get birthday
     *
     * @return string $birthday
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set website
     *
     * @param string $website
     */
    public function setWebsite($website)
    {
        $this->website = $website;
    }

    /**
     * Get website
     *
     * @return string $website
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set profession
     *
     * @param string $profession
     */
    public function setProfession($profession)
    {
        $this->profession = $profession;
    }

    /**
     * Get profession
     *
     * @return string $profession
     */
    public function getProfession()
    {
        return $this->profession;
    }

    /**
     * Set interests
     *
     * @param string $interests
     */
    public function setInterests($interests)
    {
        $this->interests = $interests;
    }

    /**
     * Get interests
     *
     * @return string $interests
     */
    public function getInterests()
    {
        return $this->interests;
    }

    /**
     * Set signature
     *
     * @param string $signature
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;
    }

    /**
     * Get signature
     *
     * @return string $signature
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Set picture
     *
     * @param string $picture
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
    }

    /**
     * Get picture
     *
     * @return string $picture
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Set users
     *
     * @param Cx\Core\User\Model\Entity\User $users
     */
    public function setUsers(\Cx\Core\User\Model\Entity\User $users)
    {
        $this->users = $users;
    }

    /**
     * Get users
     *
     * @return Cx\Core\User\Model\Entity\User $users
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set userTitle
     *
     * @param Cx\Core\User\Model\Entity\ProfileTitle $userTitle
     */
    public function setUserTitle(\Cx\Core\User\Model\Entity\ProfileTitle $userTitle)
    {
        $this->userTitle = $userTitle;
    }

    /**
     * Get userTitle
     *
     * @return Cx\Core\User\Model\Entity\ProfileTitle $userTitle
     */
    public function getUserTitle()
    {
        return $this->userTitle;
    }

    /**
     * Add userAttribute
     *
     * @param Cx\Core\User\Model\Entity\UserAttribute $userAttribute
     */
    public function addUserAttribute(\Cx\Core\User\Model\Entity\UserAttribute $userAttribute)
    {
        $this->userAttribute[] = $userAttribute;
    }

    /**
     * Get userAttribute
     *
     * @return Doctrine\Common\Collections\Collection $userAttribute
     */
    public function getUserAttribute()
    {
        return $this->userAttribute;
    }
}