<?php
/**
 * DateTime class
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_linkmanager
 */

namespace Cx\Core_Modules\LinkManager\Controller;

/**
 * DateTime class
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_linkmanager
 */

class DateTime extends \DateTime {
    
    /**
     * Return the correct date and time format
     * 
     * @global array $_ARRAYLANG
     * 
     * @param datetime $time
     * 
     * @return string
     */
    public static function formattedDateAndTime($time) 
    {
        global $_ARRAYLANG;
        
        return $time->format('d.m.Y - H.i').' '.$_ARRAYLANG['TXT_MODULE_LINKMANAGER_LABEL_CLOCK'];
    }
    
    /**
     * find the difference between two date
     * 
     * @param datetime $start
     * @param datetime $end
     * 
     * @return string
     */
    public static function diffTime($start, $end)
    {
        if (empty($start) || empty($end)) {
            return;
        }
        
        $duration = $end->diff($start);
        return $duration->format('%H:%I:%S');
    }
}