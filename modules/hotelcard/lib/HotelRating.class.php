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
     * OBSOLETE -- Replaced by language variable
     * $_ARRAYLANG['TXT_HOTELCARD_RATING_STAR']
     *
     * Symbol to use for the rating, is multiplied according to the value
     */
    //const STAR        = '*';
    /**
     * OBSOLETE -- Replaced by language variable
     * $_ARRAYLANG['TXT_HOTELCARD_RATING_TEMPLATE']
     *
     * Template to use for the rating options in the dropdown menu
     *
     * Uses sprintf() to replace the following, all optional:
     * %1$s is replaced by the rating value
     * %2$s is replaced by a string of value times the star symbol
     */
    //const TEMPLATE    = '%1$s - %2$s';


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
                $rating = self::getString_edit($index);
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
        global $_ARRAYLANG;

//echo("HotelRating::getString($rating):  Entered<br />");
//        if (    self::RATING_FROM < self::RATING_TO
//            && ($rating < self::RATING_FROM || $rating > self::RATING_TO)
//            ||  $rating > self::RATING_FROM || $rating < self::RATING_TO) {
//            return '';
//        }
        // In the backend, show empty ratings, too
        $rating = intval($rating);
        if (defined('BACKEND_LANG_ID')) {
            if (    self::RATING_FROM < self::RATING_TO
                && ($rating < self::RATING_FROM || $rating > self::RATING_TO)
                ||  $rating > self::RATING_FROM || $rating < self::RATING_TO) {
                return '0'; //$_ARRAYLANG['TXT_HOTELCARD_RATING_NONE'];
            }
            return sprintf(
                $_ARRAYLANG['TXT_HOTELCARD_RATING_TEMPLATE_VIEW'],
                $rating,
                str_repeat(
                    $_ARRAYLANG['TXT_HOTELCARD_RATING_STAR_VIEW'], $rating));
        }
        // Skip empty or invalid ratings in the frontend
        if (    self::RATING_FROM < self::RATING_TO
            && ($rating < self::RATING_FROM || $rating > self::RATING_TO)
            ||  $rating > self::RATING_FROM || $rating < self::RATING_TO) {
            return $_ARRAYLANG['TXT_HOTELCARD_RATING_NONE'];
        }

        return sprintf(
            $_ARRAYLANG['TXT_HOTELCARD_RATING_TEMPLATE_VIEW'],
            $rating,
            str_repeat(
                $_ARRAYLANG['TXT_HOTELCARD_RATING_STAR_VIEW'], $rating));
    }


    /**
     * Returns a pretty string representing the given numerical rating
     *
     * If the $rating value is outside the limits defined by the
     * RATING_FROM and RATING_TO class constants, returns the empty string.
     * @param   string      $rating     The numerical rating value
     * @return  string                  The string representation of the rating
     */
    public static function getString_edit($rating)
    {
        global $_ARRAYLANG;

//echo("HotelRating::getString($rating):  Entered<br />");
//        if (    self::RATING_FROM < self::RATING_TO
//            && ($rating < self::RATING_FROM || $rating > self::RATING_TO)
//            ||  $rating > self::RATING_FROM || $rating < self::RATING_TO) {
//            return '';
//        }
        // In the backend, show empty ratings, too
        $rating = intval($rating);
        if (defined('BACKEND_LANG_ID')) {
            if (    self::RATING_FROM < self::RATING_TO
                && ($rating < self::RATING_FROM || $rating > self::RATING_TO)
                ||  $rating > self::RATING_FROM || $rating < self::RATING_TO) {
                return '0'; //$_ARRAYLANG['TXT_HOTELCARD_RATING_NONE'];
            }
            return sprintf(
                $_ARRAYLANG['TXT_HOTELCARD_RATING_TEMPLATE_EDIT'],
                $rating,
                str_repeat(
                    $_ARRAYLANG['TXT_HOTELCARD_RATING_STAR_EDIT'], $rating));
        }
        // Skip empty or invalid ratings in the frontend
        if (    self::RATING_FROM < self::RATING_TO
            && ($rating < self::RATING_FROM || $rating > self::RATING_TO)
            ||  $rating > self::RATING_FROM || $rating < self::RATING_TO) {
            return $_ARRAYLANG['TXT_HOTELCARD_RATING_NONE'];
        }
        return sprintf(
            $_ARRAYLANG['TXT_HOTELCARD_RATING_TEMPLATE_EDIT'],
            $rating,
            str_repeat(
                $_ARRAYLANG['TXT_HOTELCARD_RATING_STAR_EDIT'], $rating));
    }

}

?>
