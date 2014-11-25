<?php
/**
 * System log
 * @copyright    CONTREXX CMS - COMVATION AG
 * @author       Michael Ritter <michael.ritter@comvation.com>
 * @package      contrexx
 * @subpackage   coremodule_syslog
 * @version      5.0.0
 */

namespace Cx\Core_Modules\SysLog\Controller;

/**
 * ComponentController for the system log
 * @copyright    CONTREXX CMS - COMVATION AG
 * @author       Michael Ritter <michael.ritter@comvation.com>
 * @package      contrexx
 * @subpackage   coremodule_syslog
 * @version      5.0.0
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController implements \Cx\Core\Event\Model\Entity\EventListener {
    
    /**
     * Event name to register
     * @var string
     */
    const EVENT_NAME = 'SysLog/Add';
    
    /**
     * Registers the event
     * 
     * New logs can be added using:
     * $this->cx->getEvents()->triggerEvent('SysLog/Add', array(
     *     'severity' => 'INFO',
     *     'message' => 'my log message',
     *     'data' => 'additional debugging data',
     * ));
     * @param \Cx\Core\Core\Model\Entity\SystemComponent $systemComponent SystemComponent entity for this component
     * @param \Cx\Core\Core\Controller\Cx $cx Current Cx class instance
     */
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponent $systemComponent, \Cx\Core\Core\Controller\Cx $cx) {
        parent::__construct($systemComponent, $cx);
        $cx->getEvents()->addEvent(static::EVENT_NAME);
        $cx->getEvents()->addEventListener(static::EVENT_NAME, $this);
    }
    
    /**
     * Event handler to add logs
     * 
     * We need to do this with an event handler so there's no dependency to this component
     * @param string $eventName Name of triggered event, should always be static::EVENT_NAME
     * @param array $eventArgs Supplied arguments, should be an array (see DBG message below)
     */
    public function onEvent($eventName, array $eventArgs) {
        if ($eventName != static::EVENT_NAME) {
            return;
        }
        if (
            empty($eventArgs['severity']) ||
            empty($eventArgs['message']) ||
            empty($eventArgs['data'])
        ) {
            \DBG::msg('Triggered event "SysLog/Add" with wrong arguments. I need an array with non-empty values for the keys "severity", "message" and "data"');
            return;
        }
        $this->addSysLog(new \Cx\Core_Modules\SysLog\Model\Entity\Log(
            $eventArgs['severity'],
            $eventArgs['message'],
            $eventArgs['data']
        ));
    }
    
    /**
     * Persists a system log
     * @param \Cx\Core_Modules\SysLog\Model\Entity\Log $sysLog Log to persist
     */
    protected function addSysLog($sysLog) {
        $em = $this->cx->getDb()->getEntityManager();
        $em->persist($sysLog);
        $em->flush();
    }
    
    /**
     * This component only has a backend
     */
    public function getControllerClasses() {
        return array('Backend');
    }
}

