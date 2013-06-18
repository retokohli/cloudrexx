<?php

namespace Cx\Core\Event\Model\Entity;

interface EventListener {
    
    public function onEvent($eventName, $eventArgs);
}
