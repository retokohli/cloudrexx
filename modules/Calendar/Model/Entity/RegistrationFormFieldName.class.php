<?php

namespace Cx\Modules\Calendar\Model\Entity;

/**
 * Cx\Modules\Calendar\Model\Entity\RegistrationFormFieldName
 */
class RegistrationFormFieldName
{
    /**
     * @var integer $fieldId
     */
    private $fieldId;

    /**
     * @var integer $formId
     */
    private $formId;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var integer $langId
     */
    private $langId;

    /**
     * @var text $default
     */
    private $default;

    /**
     * @var Cx\Modules\Calendar\Model\Entity\RegistrationFormField
     */
    private $registrationFormField;


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
     * Set formId
     *
     * @param integer $formId
     */
    public function setFormId($formId)
    {
        $this->formId = $formId;
    }

    /**
     * Get formId
     *
     * @return integer $formId
     */
    public function getFormId()
    {
        return $this->formId;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
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
     * Set default
     *
     * @param text $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * Get default
     *
     * @return text $default
     */
    public function getDefault()
    {
        return $this->default;
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