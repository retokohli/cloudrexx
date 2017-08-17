<?php

namespace Cx\Model\Proxies\__CG__\Cx\Core_Modules\Access\Model\Entity;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class AccessId extends \Cx\Core_Modules\Access\Model\Entity\AccessId implements \Doctrine\ORM\Proxy\Proxy
{
    /**
     * @var \Closure the callback responsible for loading properties in the proxy object. This callback is called with
     *      three parameters, being respectively the proxy object to be initialized, the method that triggered the
     *      initialization process and an array of ordered parameters that were passed to that method.
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setInitializer
     */
    public $__initializer__;

    /**
     * @var \Closure the callback responsible of loading properties that need to be copied in the cloned object
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setCloner
     */
    public $__cloner__;

    /**
     * @var boolean flag indicating if this object was already initialized
     *
     * @see \Doctrine\Common\Persistence\Proxy::__isInitialized
     */
    public $__isInitialized__ = false;

    /**
     * @var array properties to be lazy loaded, with keys being the property
     *            names and values being their default values
     *
     * @see \Doctrine\Common\Persistence\Proxy::__getLazyProperties
     */
    public static $lazyPropertiesDefaults = array();



    /**
     * @param \Closure $initializer
     * @param \Closure $cloner
     */
    public function __construct($initializer = null, $cloner = null)
    {

        $this->__initializer__ = $initializer;
        $this->__cloner__      = $cloner;
    }

    /**
     * {@inheritDoc}
     * @param string $name
     */
    public function __get($name)
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__get', array($name));

        return parent::__get($name);
    }





    /**
     * 
     * @return array
     */
    public function __sleep()
    {
        if ($this->__isInitialized__) {
            return array('__isInitialized__', '' . "\0" . 'Cx\\Core_Modules\\Access\\Model\\Entity\\AccessId' . "\0" . 'id', '' . "\0" . 'Cx\\Core_Modules\\Access\\Model\\Entity\\AccessId' . "\0" . 'entity_class_name', '' . "\0" . 'Cx\\Core_Modules\\Access\\Model\\Entity\\AccessId' . "\0" . 'entity_class_id', '' . "\0" . 'Cx\\Core_Modules\\Access\\Model\\Entity\\AccessId' . "\0" . 'contrexxAccessUserAttribute', '' . "\0" . 'Cx\\Core_Modules\\Access\\Model\\Entity\\AccessId' . "\0" . 'coreAttribute', '' . "\0" . 'Cx\\Core_Modules\\Access\\Model\\Entity\\AccessId' . "\0" . 'group2', '' . "\0" . 'Cx\\Core_Modules\\Access\\Model\\Entity\\AccessId' . "\0" . 'group', 'validators', 'virtual');
        }

        return array('__isInitialized__', '' . "\0" . 'Cx\\Core_Modules\\Access\\Model\\Entity\\AccessId' . "\0" . 'id', '' . "\0" . 'Cx\\Core_Modules\\Access\\Model\\Entity\\AccessId' . "\0" . 'entity_class_name', '' . "\0" . 'Cx\\Core_Modules\\Access\\Model\\Entity\\AccessId' . "\0" . 'entity_class_id', '' . "\0" . 'Cx\\Core_Modules\\Access\\Model\\Entity\\AccessId' . "\0" . 'contrexxAccessUserAttribute', '' . "\0" . 'Cx\\Core_Modules\\Access\\Model\\Entity\\AccessId' . "\0" . 'coreAttribute', '' . "\0" . 'Cx\\Core_Modules\\Access\\Model\\Entity\\AccessId' . "\0" . 'group2', '' . "\0" . 'Cx\\Core_Modules\\Access\\Model\\Entity\\AccessId' . "\0" . 'group', 'validators', 'virtual');
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (AccessId $proxy) {
                $proxy->__setInitializer(null);
                $proxy->__setCloner(null);

                $existingProperties = get_object_vars($proxy);

                foreach ($proxy->__getLazyProperties() as $property => $defaultValue) {
                    if ( ! array_key_exists($property, $existingProperties)) {
                        $proxy->$property = $defaultValue;
                    }
                }
            };

        }
    }

    /**
     * 
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', array());
    }

    /**
     * Forces initialization of the proxy
     */
    public function __load()
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__load', array());
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitialized($initialized)
    {
        $this->__isInitialized__ = $initialized;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitializer(\Closure $initializer = null)
    {
        $this->__initializer__ = $initializer;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __getInitializer()
    {
        return $this->__initializer__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setCloner(\Closure $cloner = null)
    {
        $this->__cloner__ = $cloner;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific cloning logic
     */
    public function __getCloner()
    {
        return $this->__cloner__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     * @static
     */
    public function __getLazyProperties()
    {
        return self::$lazyPropertiesDefaults;
    }

    
    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        if ($this->__isInitialized__ === false) {
            return (int)  parent::getId();
        }


        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getId', array());

        return parent::getId();
    }

    /**
     * {@inheritDoc}
     */
    public function setEntityClassName($entityClassName)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setEntityClassName', array($entityClassName));

        return parent::setEntityClassName($entityClassName);
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityClassName()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getEntityClassName', array());

        return parent::getEntityClassName();
    }

    /**
     * {@inheritDoc}
     */
    public function setEntityClassId($entityClassId)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setEntityClassId', array($entityClassId));

        return parent::setEntityClassId($entityClassId);
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityClassId()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getEntityClassId', array());

        return parent::getEntityClassId();
    }

    /**
     * {@inheritDoc}
     */
    public function addContrexxAccessUserAttribute(\Cx\Core\User\Model\Entity\UserAttribute $contrexxAccessUserAttribute)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addContrexxAccessUserAttribute', array($contrexxAccessUserAttribute));

        return parent::addContrexxAccessUserAttribute($contrexxAccessUserAttribute);
    }

    /**
     * {@inheritDoc}
     */
    public function getContrexxAccessUserAttribute()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getContrexxAccessUserAttribute', array());

        return parent::getContrexxAccessUserAttribute();
    }

    /**
     * {@inheritDoc}
     */
    public function addCoreAttribute(\Cx\Core\User\Model\Entity\CoreAttribute $coreAttribute)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addCoreAttribute', array($coreAttribute));

        return parent::addCoreAttribute($coreAttribute);
    }

    /**
     * {@inheritDoc}
     */
    public function getCoreAttribute()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCoreAttribute', array());

        return parent::getCoreAttribute();
    }

    /**
     * {@inheritDoc}
     */
    public function addGroup2(\Cx\Core\User\Model\Entity\Group $group2)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addGroup2', array($group2));

        return parent::addGroup2($group2);
    }

    /**
     * {@inheritDoc}
     */
    public function getGroup2()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getGroup2', array());

        return parent::getGroup2();
    }

    /**
     * {@inheritDoc}
     */
    public function addGroup(\Cx\Core\User\Model\Entity\Group $group)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addGroup', array($group));

        return parent::addGroup($group);
    }

    /**
     * {@inheritDoc}
     */
    public function getGroup()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getGroup', array());

        return parent::getGroup();
    }

    /**
     * {@inheritDoc}
     */
    public function getComponentController()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getComponentController', array());

        return parent::getComponentController();
    }

    /**
     * {@inheritDoc}
     */
    public function setVirtual($virtual)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setVirtual', array($virtual));

        return parent::setVirtual($virtual);
    }

    /**
     * {@inheritDoc}
     */
    public function isVirtual()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isVirtual', array());

        return parent::isVirtual();
    }

    /**
     * {@inheritDoc}
     */
    public function validate()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'validate', array());

        return parent::validate();
    }

    /**
     * {@inheritDoc}
     */
    public function __call($methodName, $arguments)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, '__call', array($methodName, $arguments));

        return parent::__call($methodName, $arguments);
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, '__toString', array());

        return parent::__toString();
    }

}
