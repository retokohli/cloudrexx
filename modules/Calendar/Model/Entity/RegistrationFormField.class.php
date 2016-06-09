<?php

namespace Cx\Modules\Calendar\Model\Entity;

/**
 * Cx\Modules\Calendar\Model\Entity\RegistrationFormField
 */
class RegistrationFormField
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var integer $required
     */
    private $required;

    /**
     * @var integer $order
     */
    private $order;

    /**
     * @var string $affiliation
     */
    private $affiliation;

    /**
     * @var Cx\Modules\Calendar\Model\Entity\RegistrationFormFieldName
     */
    private $registrationFormFieldNames;

    /**
     * @var Cx\Modules\Calendar\Model\Entity\RegistrationFormFieldValue
     */
    private $registrationFormFieldValues;

    /**
     * @var Cx\Modules\Calendar\Model\Entity\RegistrationForm
     */
    private $registrationForm;

    public function __construct()
    {
        $this->registrationFormFieldNames = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set required
     *
     * @param integer $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * Get required
     *
     * @return integer $required
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Set order
     *
     * @param integer $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * Get order
     *
     * @return integer $order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set affiliation
     *
     * @param string $affiliation
     */
    public function setAffiliation($affiliation)
    {
        $this->affiliation = $affiliation;
    }

    /**
     * Get affiliation
     *
     * @return string $affiliation
     */
    public function getAffiliation()
    {
        return $this->affiliation;
    }

    /**
     * Add registrationFormFieldNames
     *
     * @param Cx\Modules\Calendar\Model\Entity\RegistrationFormFieldName $registrationFormFieldNames
     */
    public function addRegistrationFormFieldNames(\Cx\Modules\Calendar\Model\Entity\RegistrationFormFieldName $registrationFormFieldNames)
    {
        $this->registrationFormFieldNames[] = $registrationFormFieldNames;
    }

    /**
     * Get registrationFormFieldNames
     *
     * @return Doctrine\Common\Collections\Collection $registrationFormFieldNames
     */
    public function getRegistrationFormFieldNames()
    {
        return $this->registrationFormFieldNames;
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
     * Set registrationForm
     *
     * @param Cx\Modules\Calendar\Model\Entity\RegistrationForm $registrationForm
     */
    public function setRegistrationForm(\Cx\Modules\Calendar\Model\Entity\RegistrationForm $registrationForm)
    {
        $this->registrationForm = $registrationForm;
    }

    /**
     * Get registrationForm
     *
     * @return Cx\Modules\Calendar\Model\Entity\RegistrationForm $registrationForm
     */
    public function getRegistrationForm()
    {
        return $this->registrationForm;
    }
}