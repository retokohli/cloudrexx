<?php

/**
 * JSON Adapter for Calendar module
 * @copyright   Comvation AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  core_json
 */

namespace Cx\Core\Json\Adapter\Calendar;
use \Cx\Core\Json\JsonAdapter;

/**
 * JSON Adapter for Calendar module
 * @copyright   Comvation AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  core_json
 */
class JsonCalendar implements JsonAdapter {
    /**
     * List of messages
     * @var Array 
     */
    private $messages = array();
    
    /**
     * Returns the internal name used as identifier for this adapter
     * @return String Name of this adapter
     */
    public function getName() {
        return 'calendar';
    }
    
    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods() {
        return array('getExeceptionDates');
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString() {
        return implode('<br />', $this->messages);
    }
    
    /**
     * Returns all series dates from the given post data
     *       
     * @return array Array of dates
     */
    public function getExeceptionDates() {
        global $objInit, $_CORELANG;
        
        if (!\FWUser::getFWUserObject()->objUser->login() || $objInit->mode != 'backend') {
            throw new \Exception($_CORELANG['TXT_ACCESS_DENIED_DESCRIPTION']);
        }
        
        $calendarLib = new \CalendarLibrary();       
        return $calendarLib->getExeceptionDates();        
    }
}
