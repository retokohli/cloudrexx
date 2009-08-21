<?php

/**
 * Hotel CheckInOut
 * @version     2.2.0
 * @since       2.2.0
 * @package     contrexx
 * @subpackage  module_hotelcard
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */

/**
 * Hotel CheckInOut
 * @version     2.2.0
 * @since       2.2.0
 * @package     contrexx
 * @subpackage  module_hotelcard
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */
class HotelCheckInOut
{
    /**
     * The lower limit for the checkin time, in fractions of hours after
     * midnight
     */
    const CHECKIN_MIN = 5;
    /**
     * The upper limit for the checkin time, in fractions of hours after
     * midnight
     */
    const CHECKIN_MAX = 22;
    /**
     * The lower limit for the checkout time, in fractions of hours after
     * midnight
     */
    const CHECKOUT_MIN = 5;
    /**
     * The upper limit for the checkout time, in fractions of hours after
     * midnight
     */
    const CHECKOUT_MAX = 22;
    /**
     * Interval between times selectable in the dropdown menu, in fractions
     * of hours
     */
    const INTERVAL = 0.5;
    /**
     * Template to use for the time values in the dropdown menu
     *
     * Uses date() to format the times.
     */
    const TEMPLATE    = 'H:i';


    /**
     * Returns HTML code for a dropdown menu with the available check in times
     *
     * All option values represent times in 'HH:MM' format,
     * the visible format in the menu is defined by the
     * {@see HotelCheckInOut::TEMPLATE} constant.
     * @param   string      $name       The menu name attribute value
     * @param   string      $selected   The optional preselected index
     * @param   string      $onchange   The optional onchange attribute value
     * @param   string      $attribute  Optional additional attributes
     * @return  string                  The dropdown menu HTML code
     */
    public static function getMenuCheckin(
        $name, $selected='', $onchange='', $attribute=''
    ) {
        static $arrCheckin = array();
        if (empty($arrCheckin))
            $arrCheckin = self::getArray(self::CHECKIN_MIN, self::CHECKIN_MAX);
echo("getMenuCheckin($name, $selected, $onchange, $attribute): Made time ".var_export($arrCheckin, true)."<br />");
        return Html::getSelect(
            $name,
            $arrCheckin,
            $selected, $onchange, $attribute
        );
    }


    /**
     * Returns HTML code for a dropdown menu with the available check out times
     *
     * All option values represent times in 'HH:MM' format,
     * the visible format in the menu is defined by the
     * {@see HotelCheckInOut::TEMPLATE} constant.
     * @param   string      $name       The menu name attribute value
     * @param   string      $selected   The optional preselected index
     * @param   string      $onchange   The optional onchange attribute value
     * @param   string      $attribute  Optional additional attributes
     * @return  string                  The dropdown menu HTML code
     */
    public static function getMenuCheckout(
        $name, $selected='', $onchange='', $attribute=''
    ) {
        static $arrCheckout = array();
        if (empty($arrCheckout))
            $arrCheckout = self::getArray(self::CHECKOUT_MIN, self::CHECKOUT_MAX);
echo("getMenuCheckout($name, $selected, $onchange, $attribute): Made time ".var_export($arrCheckout, true)."<br />");
        return Html::getSelect(
            $name,
            $arrCheckout,
            $selected, $onchange, $attribute
        );
    }


    /**
     * Returns an array with the times between $time_from and $time_to
     * in intervals of self::INTERVAL
     *
     * Usable for both check in and check out times.
     * All values represent times in 'HH:MM' format,
     * the visible format in the menu is defined by the
     * {@see HotelCheckInOut::TEMPLATE} constant.
     * @param   integer   $time_from  The start time
     * @param   integer   $time_to    The end time
     * @return  array                 The times array
     */
    public static function getArray($time_from, $time_to)
    {
        $time_lo  = ($time_from-1) * 3600;
        $time_hi  = ($time_to  -1) * 3600;
        $interval = self::INTERVAL * 3600;
echo("getArray($time_from, $time_to): made time_lo $time_lo time_hi $time_hi interval $interval<br />");
        $arrTime = array();
        for ($index = $time_lo;
            $index <= $time_hi;
            $index += $interval) {
            $arrTime[date('H:i', $index)] =
                date(self::TEMPLATE, $index);
        }
echo("getArray($time_from, $time_to):: Made times ".var_export($arrTime, true)."<hr />");
        return $arrTime;
    }



}

?>
