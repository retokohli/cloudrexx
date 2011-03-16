<?php

/**
 * Hotel Rating
 * @version     2.2.0
 * @since       2.2.0
 * @package     contrexx
 * @subpackage  module_hotelcard
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */

/**
 * Hotel Rating
 * @version     2.2.0
 * @since       2.2.0
 * @package     contrexx
 * @subpackage  module_hotelcard
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */
class HotelRating
{
    /**
     * Start ratings at value... (may be highest or lowest value)
     */
    const RATING_FROM = 5;
    /**
     * End ratings at value... (may be highest or lowest value)
     */
    const RATING_TO   = 1;
    /**
     * If true, an additional option for "not rated" is added
     */
    const INCLUDE_NOT_RATED = true;
    /**
     * Symbol to use for the rating, is multiplied according to the value
     */
    const STAR        = '*';
    /**
     * Template to use for the rating options in the dropdown menu
     *
     * Uses sprintf() to replace the following, all optional:
     * %1$s is replaced by the rating value
     * %2$s is replaced by a string of value times the star symbol
     */
    const TEMPLATE    = '%1$s - %2$s';


    /**
     * Returns an array with the available ratings
     * @return  array             The ratings array
     */
    public static function getArray()
    {
        global $_ARRAYLANG;
        static $arrRating = array();

        if (empty($arrRating)) {
//echo("HotelRating::getArray(): Making rating array...<br />");
            if (self::INCLUDE_NOT_RATED)
                $arrRating['-'] = $_ARRAYLANG['TXT_HOTELCARD_RATING_NONE'];
            for ($index = self::RATING_FROM;
                 (   (   self::RATING_FROM < self::RATING_TO
                      && $index <= self::RATING_TO)
                  || $index >= self::RATING_TO);
                $index += (self::RATING_FROM < self::RATING_TO ? 1 : -1)) {
                $rating = self::getString($index);
//echo("HotelRating::getArray(): Adding index $index => $rating<br />");
                $arrRating[$index] = $rating;
            }
        }
//echo("HotelRating::getArray(): Made rating array ".var_export($arrRating, true)."<br />");
        return $arrRating;
    }


    /**
     * Returns a pretty string representing the given numerical rating
     *
     * If the $rating value is outside the limits defined by the
     * RATING_FROM and RATING_TO class constants, returns the empty string.
     * @param   string      $rating     The numerical rating value
     * @return  string                  The string representation of the rating
     */
    public static function getString($rating)
    {
//echo("HotelRating::getString($rating):  Entered<br />");
        if (    self::RATING_FROM < self::RATING_TO
            && ($rating < self::RATING_FROM || $rating > self::RATING_TO)
            ||  $rating > self::RATING_FROM || $rating < self::RATING_TO) {
//echo("HotelRating::getString($rating):  Invalid rating<br />");
            return '';
        }
        return sprintf(self::TEMPLATE, $rating, str_repeat(self::STAR, $rating));
    }

}

?>
