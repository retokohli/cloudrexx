<?php

/**
 * EventListener for Products
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_shop
 */

namespace Cx\Modules\Shop\Model\Event;

/**
 * EventListener for Products
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_shop
 */
class ProductsEventListener implements \Cx\Core\Event\Model\Entity\EventListener 
{
    
    /**
     * prePersist Event
     * 
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $eventArgs prePersist
     * 
     * @return null
     */
    public function prePersist($eventArgs) {}
    
    /**
     * postPersist Event
     * 
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $eventArgs postPersist
     * 
     * @return null
     */
    public function postPersist($eventArgs) {}
    
    /**
     * preUpdate Event
     * 
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $eventArgs preUpdate
     * 
     * @return null
     */
    public function preUpdate($eventArgs) {}
    
    /**
     * postUpdate Event
     * 
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $eventArgs postUpdate
     * 
     * @return null
     */
    public function postUpdate($eventArgs) {}
    
    /**
     * preRemove Event
     * 
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $eventArgs preRemove
     * 
     * @return null
     */
    public function preRemove($eventArgs) {}
    
    /**
     * postRemove Event
     * 
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $eventArgs postRemove
     * 
     * @return null
     */
    public function postRemove($eventArgs) {}
    
    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }
    
}
