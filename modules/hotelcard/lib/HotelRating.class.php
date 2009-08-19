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
     * Returns HTML code for a dropdown menu with the available ratings
     *
     * Note that iff the $selected parameter is the empty sting or missing,
     * an additional option will be added on top of the others asking
     * the user to "please choose".
     * @param   string      $name       The menu name attribute value
     * @param   string      $selected   The optional preselected index
     * @param   string      $onchange   The optional onchange attribute value
     * @param   string      $attribute  Optional additional attributes
     * @return  string                  The dropdown menu HTML code
     */
    public static function getMenu($name, $selected='', $onchange='', $attribute='')
    {
        global $_ARRAYLANG;

        static $arrRating = array();
        if (empty($arrRating)) {
            if ($selected === '')
                $arrRating[] = $_ARRAYLANG['TXT_HOTELCARD_HOTEL_RATING_PLEASE_CHOOSE'];
echo("Making rating array...<br />");
            for ($index = self::RATING_FROM;
                 (   (   self::RATING_FROM < self::RATING_TO
                      && $index <= self::RATING_TO)
                  || $index >= self::RATING_TO);
                $index += (self::RATING_FROM < self::RATING_TO ? 1 : -1)) {
                $rating = self::getString($index);
echo("adding index $index => $rating<br />");
                $arrRating[$index] = $rating;
            }
        }
echo("Made rating array ".var_export($arrRating, true)."<br />");
        return Html::getSelect(
            $name,
            $arrRating,
            $selected, $onchange, $attribute
        );
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
        if ($rating < self::RATING_FROM || $rating > self::RATING_TO)
            return '';
        return sprintf(self::TEMPLATE, $rating, str_repeat(self::STAR, $rating));
    }

}

?>
