<?php

namespace Cx\Modules\Calendar\Model\Entity;

/**
 * Cx\Modules\Calendar\Model\Entity\RegistrationFormFieldValue
 */
class RegistrationFormFieldValue
{
    /**
     * @var integer $regId
     */
    private $regId;

    /**
     * @var integer $fieldId
     */
    private $fieldId;

    /**
     * @var text $value
     */
    private $value;

    /**
     * @var Cx\Modules\Calendar\Model\Entity\Registration
     */
    private $registration;

    /**
     * @var Cx\Modules\Calendar\Model\Entity\RegistrationFormField
     */
    private $registrationFormField;


    /**
     * Set regId
     *
     * @param integer $regId
     */
    public function setRegId($regId)
    {
        $this->regId = $regId;
    }

    /**
     * Get regId
     *
     * @return integer $regId
     */
    public function getRegId()
    {
        return $this->regId;
    }

    /**
     * Set fieldId
     *
     * @param integer $fieldId
     */
    public function setFieldId($fieldId)
    {
        $this->fieldId = $fieldId;
    }

    /**
     * Get fieldId
     *
     * @return integer $fieldId
     */
    public function getFieldId()
    {
        return $this->fieldId;
    }

    /**
     * Set value
     *
     * @param text $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get value
     *
     * @return text $value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set registration
     *
     * @param Cx\Modules\Calendar\Model\Entity\Registration $registration
     */
    public function setRegistration(\Cx\Modules\Calendar\Model\Entity\Registration $registration)
    {
        $this->registration = $registration;
    }

    /**
     * Get registration
     *
     * @return Cx\Modules\Calendar\Model\Entity\Registration $registration
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * Set registrationFormField
     *
     * @param Cx\Modules\Calendar\Model\Entity\RegistrationFormField $registrationFormField
     */
    public function setRegistrationFormField(\Cx\Modules\Calendar\Model\Entity\RegistrationFormField $registrationFormField)
    {
        $this->registrationFormField = $registrationFormField;
    }

    /**
     * Get registrationFormField
     *
     * @return Cx\Modules\Calendar\Model\Entity\RegistrationFormField $registrationFormField
     */
    public function getRegistrationFormField()
    {
        return $this->registrationFormField;
    }
}