<?php
class EventDispatcher
{
    static private $instance;

    /**
     * @var EventHandler[]
     */
    protected $handlers = array();

    private function __construct() {

    }

    private function __clone() {}

    /**
     * @static
     * @return EventDispatcher
     */
    static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    function addHandler($event_name, EventHandler $event_handler) {
        $this->handlers[$event_name][] = $event_handler;
    }

    function triggerEvent($event_name, $context = null, $info = null) {
        if (!isset($this->handlers[$event_name])) {
            //throw new InvalidArgumentException("The event '$event_name' has been triggered, but no event handlers have been registered.");
            return false;
        }

        $event = new Event($event_name, $context, $info);

        /** @var $handler EventHandler */
        foreach($this->handlers[$event_name] as $handler) {
            if (!$event->isCancelled()) {
                $handler->handleEvent($event);
            } else {
                return false;
            }
        };
        return true;
    }

}
 
