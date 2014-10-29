<?php

/**
 * Event manager
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_event
 */

namespace Cx\Core\Event\Controller;

/**
 * Event manager exception
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_event
 */

class EventManagerException extends \Exception {}

/**
 * Event manager
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_event
 */

class EventManager {
    protected $listeners = array();
    
    public function addEvent($eventName) {
        if (isset($this->listeners[$eventName])) {
            throw new EventManagerException('An event with this name is already added (' . $eventName . ')');
        }
        $this->listeners[$eventName] = array();
    }
    
    public function triggerEvent($eventName, $eventArgs = array()) {
        if (!isset($this->listeners[$eventName])) {
            throw new EventManagerException('No such event "' . $eventName . '"');
        }
        foreach ($this->listeners[$eventName] as $listener) {
            if (is_callable($listener)) {
                $listener($eventName, $eventArgs);
            } else {
                $listener->onEvent($eventName, $eventArgs);
            }
        }
    }
    
    public function addEventListener($eventName, $listener) {
        if (!isset($this->listeners[$eventName])) {
            throw new EventManagerException('No such event "' . $eventName . '"');
        }
        if (in_array($listener, $this->listeners, true)) {
            throw new EventManagerException('Cannot re-register event handler');
        }
        if (!is_callable($listener) && !($listener instanceof \Cx\Core\Event\Model\Entity\EventListener)) {
            throw new EventManagerException('Listener must be callable or implement EventListener interface!');
        }
        $this->listeners[$eventName][] = $listener;
    }
    
    public function addModelListener($eventName, $entityClass, $listener) {
        $this->addEventListener('model/' . $eventName, new \Cx\Core\Event\Model\Entity\ModelEventListener($eventName, $entityClass, $listener));
    }
}
