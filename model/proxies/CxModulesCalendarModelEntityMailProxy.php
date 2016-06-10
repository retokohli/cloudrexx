<?php

namespace Cx\Model\Proxies;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ORM. DO NOT EDIT THIS FILE.
 */
class CxModulesCalendarModelEntityMailProxy extends \Cx\Modules\Calendar\Model\Entity\Mail implements \Doctrine\ORM\Proxy\Proxy
{
    private $_entityPersister;
    private $_identifier;
    public $__isInitialized__ = false;
    public function __construct($entityPersister, $identifier)
    {
        $this->_entityPersister = $entityPersister;
        $this->_identifier = $identifier;
    }
    private function _load()
    {
        if (!$this->__isInitialized__ && $this->_entityPersister) {
            $this->__isInitialized__ = true;
            if ($this->_entityPersister->load($this->_identifier, $this) === null) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
            unset($this->_entityPersister, $this->_identifier);
        }
    }

    
    public function getId()
    {
        $this->_load();
        return parent::getId();
    }

    public function setTitle($title)
    {
        $this->_load();
        return parent::setTitle($title);
    }

    public function getTitle()
    {
        $this->_load();
        return parent::getTitle();
    }

    public function setContentText($contentText)
    {
        $this->_load();
        return parent::setContentText($contentText);
    }

    public function getContentText()
    {
        $this->_load();
        return parent::getContentText();
    }

    public function setContentHtml($contentHtml)
    {
        $this->_load();
        return parent::setContentHtml($contentHtml);
    }

    public function getContentHtml()
    {
        $this->_load();
        return parent::getContentHtml();
    }

    public function setRecipients($recipients)
    {
        $this->_load();
        return parent::setRecipients($recipients);
    }

    public function getRecipients()
    {
        $this->_load();
        return parent::getRecipients();
    }

    public function setLangId($langId)
    {
        $this->_load();
        return parent::setLangId($langId);
    }

    public function getLangId()
    {
        $this->_load();
        return parent::getLangId();
    }

    public function setActionId($actionId)
    {
        $this->_load();
        return parent::setActionId($actionId);
    }

    public function getActionId()
    {
        $this->_load();
        return parent::getActionId();
    }

    public function setIsDefault($isDefault)
    {
        $this->_load();
        return parent::setIsDefault($isDefault);
    }

    public function getIsDefault()
    {
        $this->_load();
        return parent::getIsDefault();
    }

    public function setStatus($status)
    {
        $this->_load();
        return parent::setStatus($status);
    }

    public function getStatus()
    {
        $this->_load();
        return parent::getStatus();
    }


    public function __sleep()
    {
        return array('__isInitialized__', 'id', 'title', 'contentText', 'contentHtml', 'recipients', 'langId', 'actionId', 'isDefault', 'status');
    }

    public function __clone()
    {
        if (!$this->__isInitialized__ && $this->_entityPersister) {
            $this->__isInitialized__ = true;
            $class = $this->_entityPersister->getClassMetadata();
            $original = $this->_entityPersister->load($this->_identifier);
            if ($original === null) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
            foreach ($class->reflFields AS $field => $reflProperty) {
                $reflProperty->setValue($this, $reflProperty->getValue($original));
            }
            unset($this->_entityPersister, $this->_identifier);
        }
        
    }
}