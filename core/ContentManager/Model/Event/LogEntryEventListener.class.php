<?php

/**
 * This listener is done for the Log entry objects.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_contentmanager
 */

namespace Cx\Core\ContentManager\Model\Event;

/**
 * LogEntryEventListenerException
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_contentmanager
 */
class LogEntryEventListenerException extends \Exception {}

/**
 * LogEntryEventListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_contentmanager
 */
class LogEntryEventListener implements \Cx\Core\Event\Model\Entity\EventListener {
    public function onFlush($eventArgs) {}
    
    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }
}
