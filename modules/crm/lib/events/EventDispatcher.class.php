<?php
/**
 * EventDispatcher Class CRM
 *
 * @category   EventDispatcher
 * @package    contrexx
 * @subpackage module_crm
 * @author     SoftSolutions4U Development Team <info@softsolutions4u.com>
 * @copyright  2012 and CONTREXX CMS - COMVATION AG
 * @license    trial license
 * @link       www.contrexx.com
 */

/**
 * EventDispatcher Class CRM
 *
 * @category   EventDispatcher
 * @package    contrexx
 * @subpackage module_crm
 * @author     SoftSolutions4U Development Team <info@softsolutions4u.com>
 * @copyright  2012 and CONTREXX CMS - COMVATION AG
 * @license    trial license
 * @link       www.contrexx.com
 */
class EventDispatcher
{
    /**
    * Class object
    *
    * @access private
    * @var object
    */
    static private $instance;

    /**
    * handler
    *
    * @access protected
    * @var EventHandler[]
    */
    protected $handlers = array();

    /**
     * Constructor
     */
    private function __construct()
    {

    }

    /**
     * Dublicate copy
     *
     * @return null
     */
    private function __clone() {}

    /**
     * Get instance of the class
     *
     * @static
     * @return EventDispatcher
     */
    static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Add handler
     * 
     * @param String       $event_name    event name
     * @param EventHandler $event_handler event handler
     *
     * @return null
     */
    function addHandler($event_name, EventHandler $event_handler)
    {
        $this->handlers[$event_name][] = $event_handler;
    }

    /**
     * Trigger the event
     * 
     * @param String $event_name event name
     * @param String $context    event context
     * @param String $info       event info
     *
     * @return boolean
     */
    function triggerEvent($event_name, $context = null, $info = null)
    {
        if (!isset($this->handlers[$event_name])) {
            //throw new InvalidArgumentException("The event '$event_name' has been triggered, but no event handlers have been registered.");
            return false;
        }

        $event = new Event($event_name, $context, $info);

        /** @var $handler EventHandler */
        foreach ($this->handlers[$event_name] as $handler) {
            if (!$event->isCancelled()) {
                $handler->handleEvent($event);
            } else {
                return false;
            }
        };
        return true;
    }

}
 
