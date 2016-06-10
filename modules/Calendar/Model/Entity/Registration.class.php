<?php

namespace Cx\Modules\Calendar\Model\Entity;

/**
 * Cx\Modules\Calendar\Model\Entity\Registration
 */
class Registration
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $date
     */
    private $date;

    /**
     * @var string $hostName
     */
    private $hostName;

    /**
     * @var string $ipAddress
     */
    private $ipAddress;

    /**
     * @var integer $type
     */
    private $type;

    /**
     * @var string $key
     */
    private $key;

    /**
     * @var integer $userId
     */
    private $userId;

    /**
     * @var integer $langId
     */
    private $langId;

    /**
     * @var integer $export
     */
    private $export;

    /**
     * @var integer $paymentMethod
     */
    private $paymentMethod;

    /**
     * @var integer $paid
     */
    private $paid;

    /**
     * @var Cx\Modules\Calendar\Model\Entity\RegistrationFormFieldValue
     */
    private $registrationFormFieldValues;

    /**
     * @var Cx\Modules\Calendar\Model\Entity\Event
     */
    private $event;

    public function __construct()
    {
        $this->registrationFormFieldValues = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set date
     *
     * @param integer $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * Get date
     *
     * @return integer $date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set hostName
     *
     * @param string $hostName
     */
    public function setHostName($hostName)
    {
        $this->hostName = $hostName;
    }

    /**
     * Get hostName
     *
     * @return string $hostName
     */
    public function getHostName()
    {
        return $this->hostName;
    }

    /**
     * Set ipAddress
     *
     * @param string $ipAddress
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    }

    /**
     * Get ipAddress
     *
     * @return string $ipAddress
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * Set type
     *
     * @param integer $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return integer $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set key
     *
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Get key
     *
     * @return string $key
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Get userId
     *
     * @return integer $userId
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set langId
     *
     * @param integer $langId
     */
    public function setLangId($langId)
    {
        $this->langId = $langId;
    }

    /**
     * Get langId
     *
     * @return integer $langId
     */
    public function getLangId()
    {
        return $this->langId;
    }

    /**
     * Set export
     *
     * @param integer $export
     */
    public function setExport($export)
    {
        $this->export = $export;
    }

    /**
     * Get export
     *
     * @return integer $export
     */
    public function getExport()
    {
        return $this->export;
    }

    /**
     * Set paymentMethod
     *
     * @param integer $paymentMethod
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * Get paymentMethod
     *
     * @return integer $paymentMethod
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * Set paid
     *
     * @param integer $paid
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;
    }

    /**
     * Get paid
     *
     * @return integer $paid
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * Add registrationFormFieldValues
     *
     * @param Cx\Modules\Calendar\Model\Entity\RegistrationFormFieldValue $registrationFormFieldValues
     */
    public function addRegistrationFormFieldValues(\Cx\Modules\Calendar\Model\Entity\RegistrationFormFieldValue $registrationFormFieldValues)
    {
        $this->registrationFormFieldValues[] = $registrationFormFieldValues;
    }

    /**
     * Get registrationFormFieldValues
     *
     * @return Doctrine\Common\Collections\Collection $registrationFormFieldValues
     */
    public function getRegistrationFormFieldValues()
    {
        return $this->registrationFormFieldValues;
    }

    /**
     * Set event
     *
     * @param Cx\Modules\Calendar\Model\Entity\Event $event
     */
    public function setEvent(\Cx\Modules\Calendar\Model\Entity\Event $event)
    {
        $this->event = $event;
    }

    /**
     * Get event
     *
     * @return Cx\Modules\Calendar\Model\Entity\Event $event
     */
    public function getEvent()
    {
        return $this->event;
    }
}