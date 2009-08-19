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
        if (empty($arrCheckin)) {
            $time_lo  = (self::CHECKIN_MIN-1) * 3600;
            $time_hi  = (self::CHECKIN_MAX-1) * 3600;
            $interval = self::INTERVAL        * 3600;
echo("getMenuCheckin($name, $selected, $onchange, $attribute): made time_lo $time_lo time_hi $time_hi interval $interval");
            for ($index = $time_lo;
                $index <= $time_hi;
                $index += $interval) {
                $arrCheckin[date('H:i', $index)] =
                    date(self::TEMPLATE, $index);
            }
        }
echo("Made time array ".var_export($arrCheckin, true)."<br />");
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
        if (empty($arrCheckout)) {
            $time_lo  = (self::CHECKOUT_MIN-1) * 3600;
            $time_hi  = (self::CHECKOUT_MAX-1) * 3600;
            $interval = self::INTERVAL         * 3600;
echo("getMenuCheckout($name, $selected, $onchange, $attribute): made time_lo $time_lo time_hi $time_hi interval $interval");
            for ($index = $time_lo;
                $index <= $time_hi;
                $index += $interval) {
                $arrCheckout[date('H:m', $index)] =
                    date(self::TEMPLATE, $index);
            }
        }
echo("Made time array ".var_export($arrCheckout, true)."<br />");
        return Html::getSelect(
            $name,
            $arrCheckout,
            $selected, $onchange, $attribute
        );
    }

}

?>
