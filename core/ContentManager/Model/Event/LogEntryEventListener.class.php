<?php

/**
 * This listener is done for the Log entry objects.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  model_events
 */

namespace Cx\Core\ContentManager\Model\Event;

/**
 * LogEntryEventListenerException
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  model_events
 */
class LogEntryEventListenerException extends \Exception {}

/**
 * LogEntryEventListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  model_events
 */
class LogEntryEventListener implements \Cx\Core\Event\Model\Entity\EventListener {
    public function onFlush($eventArgs) {
        global $objCache;
        $objCache->clearCache();
    }
    
    public function onEvent($eventName, $eventArgs) {
        $this->$eventName(current($eventArgs));
    }
}
