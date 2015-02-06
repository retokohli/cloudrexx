<?php

/**
 * Model event wrapper
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_event
 */

namespace Cx\Core\Event\Controller;

/**
 * Model event wrapper
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_event
 */

class ModelEventWrapper {
    protected $cx = null;
    
    public function __construct(\Cx\Core\Core\Controller\Cx $cx) {
        $this->cx = $cx;
        $this->cx->getEvents()->addEvent('model/prePersist');
        $this->cx->getEvents()->addEvent('model/postPersist');
        $this->cx->getEvents()->addEvent('model/preUpdate');
        $this->cx->getEvents()->addEvent('model/postUpdate');
        $this->cx->getEvents()->addEvent('model/preRemove');
        $this->cx->getEvents()->addEvent('model/postRemove');
        $this->cx->getEvents()->addEvent('model/onFlush');
        $this->cx->getEvents()->addEvent('model/postFlush');
        $evm = $this->cx->getDb()->getEntityManager()->getEventManager();
        $evm->addEventListener(\Doctrine\ORM\Events::prePersist,  $this);
        $evm->addEventListener(\Doctrine\ORM\Events::postPersist, $this);
        $evm->addEventListener(\Doctrine\ORM\Events::preUpdate,   $this);
        $evm->addEventListener(\Doctrine\ORM\Events::postUpdate,  $this);
        $evm->addEventListener(\Doctrine\ORM\Events::preRemove,   $this);
        $evm->addEventListener(\Doctrine\ORM\Events::postRemove,  $this);
        $evm->addEventListener(\Doctrine\ORM\Events::onFlush,     $this);
        $evm->addEventListener(\Doctrine\ORM\Events::postFlush,     $this);
    }
    
    public function prePersist(\Doctrine\ORM\Event\LifecycleEventArgs $eventArgs) {
        $this->cx->getEvents()->triggerEvent('model/prePersist', array($eventArgs));
    }
    
    public function postPersist(\Doctrine\ORM\Event\LifecycleEventArgs $eventArgs) {
        $this->cx->getEvents()->triggerEvent('model/postPersist', array($eventArgs));
    }
    
    public function preUpdate(\Doctrine\ORM\Event\LifecycleEventArgs $eventArgs) {
        $this->cx->getEvents()->triggerEvent('model/preUpdate', array($eventArgs));
    }
    
    public function postUpdate(\Doctrine\ORM\Event\LifecycleEventArgs $eventArgs) {
        $this->cx->getEvents()->triggerEvent('model/postUpdate', array($eventArgs));
    }
    
    public function preRemove(\Doctrine\ORM\Event\LifecycleEventArgs $eventArgs) {
        $this->cx->getEvents()->triggerEvent('model/preRemove', array($eventArgs));
    }
    
    public function postRemove(\Doctrine\ORM\Event\LifecycleEventArgs $eventArgs) {
        $this->cx->getEvents()->triggerEvent('model/postRemove', array($eventArgs));
    }
    
    public function onFlush(\Doctrine\Common\EventArgs $eventArgs) {
        $this->cx->getEvents()->triggerEvent('model/onFlush', array($eventArgs));
    }

    public function postFlush(\Doctrine\Common\EventArgs $eventArgs) {
        $this->cx->getEvents()->triggerEvent('model/postFlush', array($eventArgs));
    }
}
