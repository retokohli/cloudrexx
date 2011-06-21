<?php

namespace Gedmo\Loggable\Entity;

/**
 * Gedmo\Loggable\Entity\AbstractLogEntry
 */
class AbstractLogEntry
{
    /**
     * @var string $action
     */
    private $action;

    /**
     * @var datetime $loggedAt
     */
    private $loggedAt;

    /**
     * @var integer $version
     */
    private $version;


    /**
     * Set action
     *
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Get action
     *
     * @return string $action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set loggedAt
     *
     * @param datetime $loggedAt
     */
    public function setLoggedAt($loggedAt)
    {
        $this->loggedAt = $loggedAt;
    }

    /**
     * Get loggedAt
     *
     * @return datetime $loggedAt
     */
    public function getLoggedAt()
    {
        return $this->loggedAt;
    }

    /**
     * Set version
     *
     * @param integer $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Get version
     *
     * @return integer $version
     */
    public function getVersion()
    {
        return $this->version;
    }
}