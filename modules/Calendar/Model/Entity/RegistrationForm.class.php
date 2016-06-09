<?php

namespace Cx\Modules\Calendar\Model\Entity;

/**
 * Cx\Modules\Calendar\Model\Entity\RegistrationForm
 */
class RegistrationForm
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $status
     */
    private $status;

    /**
     * @var integer $order
     */
    private $order;

    /**
     * @var string $title
     */
    private $title;

    /**
     * @var Cx\Modules\Calendar\Model\Entity\Event
     */
    private $events;

    /**
     * @var Cx\Modules\Calendar\Model\Entity\RegistrationFormField
     */
    private $registrationFormFields;

    public function __construct()
    {
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
    $this->registrationFormFields = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set status
     *
     * @param integer $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return integer $status
     */
    public function getStatus()
    {
        return $this->status;
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
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Add events
     *
     * @param Cx\Modules\Calendar\Model\Entity\Event $events
     */
    public function addEvents(\Cx\Modules\Calendar\Model\Entity\Event $events)
    {
        $this->events[] = $events;
    }

    /**
     * Get events
     *
     * @return Doctrine\Common\Collections\Collection $events
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Add registrationFormFields
     *
     * @param Cx\Modules\Calendar\Model\Entity\RegistrationFormField $registrationFormFields
     */
    public function addRegistrationFormFields(\Cx\Modules\Calendar\Model\Entity\RegistrationFormField $registrationFormFields)
    {
        $this->registrationFormFields[] = $registrationFormFields;
    }

    /**
     * Get registrationFormFields
     *
     * @return Doctrine\Common\Collections\Collection $registrationFormFields
     */
    public function getRegistrationFormFields()
    {
        return $this->registrationFormFields;
    }
}