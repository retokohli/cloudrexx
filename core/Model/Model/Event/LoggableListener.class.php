<?php

/**
 * Wrapper class for the Gedmo\Loggable\LoggableListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @version     3.1.2
 * @package     contrexx
 * @subpackage  core 
 */

namespace Cx\Core\Model\Model\Event;


class LoggableListenerException extends \Exception { }

/**
 * Wrapper class for the Gedmo\Loggable\LoggableListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @version     $Id:    Exp $
 * @package     contrexx
 * @subpackage  core
 */
class LoggableListener extends \Gedmo\Loggable\LoggableListener {
    
    /**
     * {@inheritDoc}
     */
    protected function getEventAdapter(\Doctrine\Common\EventArgs $args) {
        parent::getEventAdapter($args);
        
        $class = get_class($args);
        if (preg_match('@Doctrine\\\([^\\\]+)@', $class, $m) && $m[1] == 'ORM') {
            $this->adapters[$m[1]] = new ORM();
            $this->adapters[$m[1]]->setEventArgs($args);
        }
        if (isset($this->adapters[$m[1]])) {
            return $this->adapters[$m[1]];
        } else {
            throw new LoggableListenerException('Event mapper does not support event arg class: '.$class);
        }
    }
}
