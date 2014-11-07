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
    
    public function onEvent($eventName, array $eventArgs) {
        $em = current($eventArgs);
        if (
            $em instanceof \Doctrine\ORM\Event\LifecycleEventArgs &&
            get_class($em->getEntity()) != $this->entityClass &&
            get_class($em->getEntity()) != 'Cx\\Model\\Proxies\\' . str_replace('\\', '', $this->entityClass) . 'Proxy'
            // Important: the above two get_class() conditions could also be replace by the following:
            // !($eventArgs->getEntity() instanceof $this->entityClass)
            //
            // But this causes unexpected results. In case a model does extend an other model,
            // then all events registered to the base model are inherited by extending model.
            // The latter might not be wanted. Therefore events must explicitly be registered
            // to extending models and thus we shall not use the simplified 'instanceof'
            // check at this point.
        ) {
            return;
        }
        $eventName = substr($eventName, 6);
        if (is_callable($this->listener)) {
            $listener = $this->listener;
            $listener($eventName, $eventArgs);
        } else {
            $this->listener->onEvent($eventName, $eventArgs);
        }
    }
}
