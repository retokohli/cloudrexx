<?php

/**
 * EventListener for Domain
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_net
 */

namespace Cx\Core\Net\Model\Event;

/**
 * EventListener for Domain
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_net
 */
class DomainEventListener implements \Cx\Core\Event\Model\Entity\EventListener {

    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }
    
    public function prePersist($eventArgs) {
        //code here
    }
    
    public function postPersist($eventArgs) {
        
    }
    
    public function preRemove($eventArgs) {
        
    }
    
    public function postRemove($eventArgs) {
        
    }
}
