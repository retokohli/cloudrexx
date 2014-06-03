<?php

/**
 * Model event listener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_event
 */

namespace Cx\Core\Event\Model\Entity;

/**
 * Model event listener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_event
 */

class ModelEventListener implements EventListener {
    protected $entityClass = null;
    protected $listener = null;
    
    public function __construct($event, $entityClass, $listener) {
        if (!is_callable($listener) && !($listener instanceof \Cx\Core\Event\Model\Entity\EventListener)) {
            throw new \Cx\Core\Event\Controller\EventManagerException('Listener must be callable or implement EventListener interface!');
        }
        $this->entityClass = $entityClass;
        $this->listener = $listener;
    }
    
    public function onEvent($eventName, $eventArgs) {
        $eventArgs = current($eventArgs);
        if (
            $eventArgs instanceof \Doctrine\ORM\Event\LifecycleEventArgs &&
            get_class($eventArgs->getEntity()) != $this->entityClass &&
            get_class($eventArgs->getEntity()) != 'Cx\\Model\\Proxies\\' . str_replace('\\', '', $this->entityClass) . 'Proxy'
        ) {
            return;
        }
        $eventName = substr($eventName, 6);
        if (is_callable($this->listener)) {
            $listener = $this->listener;
            $listener($eventName, array($eventArgs));
        } else {
            $this->listener->onEvent($eventName, array($eventArgs));
        }
    }
}
