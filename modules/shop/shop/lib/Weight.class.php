<?php

/**
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 */

/**
 * The Weight class provides static conversion functions for weights.
 *
 * This class is used to properly convert weights between the format used
 * in the database (grams, integer) and a format for displaying and editing
 * in the user interface (string, with units).
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @access      public
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 */
class Weight
{
    /**
     * The weight units in an array
     * @static
     * @access  private
     * @var     array
     */
    private static $arrUnits = array(
        'g',    //$_ARRAYLANG['TXT_WEIGHT_UNIT_GRAM'],
        'kg',   //$_ARRAYLANG['TXT_WEIGHT_UNIT_KILOGRAM'],
        't',    //$_ARRAYLANG['TXT_WEIGHT_UNIT_TONNE'],
    );


    /**
     * Return a string with the weight converted from grams to an appropriate unit.
     *
     * The weight is converted, and the unit chosen as follows:
     * - weight in  [        0 ..         1'000[ -> 0 .. 999.999 grams,
     * - weight in  [    1'000 ..     1'000'000[ -> 0 .. 999.999 kilograms,
     * - weight in  [1'000'000 .. 1'000'000'000[ -> 0 .. 999.999 tonnes.
     * If the weight argument is outside of the valid range as specified above,
     * '' (the empty string) is returned.
     * @static
     * @access  public
     * @param   integer $grams  The weight in grams
     * @return  string          The weight in another unit, or ''
     */
    static function getWeightString($grams)
    {
        // weight too small, too big, or no integer
        if ($grams < 1 || $grams >= 1000000000 || $grams != intval($grams))
            return '0 g';
        $unit_index = intval(log10($grams)/3);
        // unit_index shouldn't be out of range, as the weight range
        // is verified above
        if ($unit_index < 0 || $unit_index > count(self::$arrUnits))
            return '';
        // scale weight and append unit
        $weight = $grams/pow(1000, $unit_index);
        $unit   = self::$arrUnits[$unit_index];
        return "$weight $unit";
    }


    /**
     * Return the weight found in the string argument converted back to grams
     *
     * Takes a string as created by {@link getWeightString()}
     * and returns the value converted to grams, with the unit
     * removed, as an integer value ready to be written to the
     * database.
     * The unit, if missing, defaults to 'g' (grams).
     * If no float value is found at the beginning of the string,
     * if it is out of range, or if the unit is set but unknown,
     * 'NULL' will be returned.
     * Note that, as weights are stored as integers, they are
     * rounded *down* to whole grams.
     * @access  public
     * @param   string  $weight The weight in another unit
     * @return  integer         The weight in grams, or 'NULL' on error.
     */
    static function getWeight($weightString)
    {
        // store regex matches here
        $arrMatch = array();
        // numeric result value
        $grams = 0;

        if (preg_match('/^(\d*\.?\d+)\s*(\w*)$/', $weightString, $arrMatch)) {
            $weight = $arrMatch[1];
            $unit   = $arrMatch[2];
            // if the number is missing, return NULL
            if ($weight == '') {
                return 'NULL';
            }
            // if the unit is missing, default to 'g' (grams)
            if (empty($unit)) {
                $grams = intval($weight+1e-8);
            } else {
                // unit is set, look if it's known
                $unit_index = array_search($unit, self::$arrUnits);
                // if the unit is set, but unknown, return NULL
                if ($unit_index === false) {
                    return 'NULL';
                }
                // have to correct and cast to integer here, because there are precision issues
                // for some numbers otherwise (i.e. "1.001 kg" yields 1000 instead of 1001 grams)!
                $grams = intval($weight*pow(1000, $unit_index)+1e-8);
            }
            // $grams is set to an integer now, in any case.
            // check whether the weight is too small, or too big
            if ($grams < 0 || $grams >= 1000000000) {
                return 'NULL';
            }
            // return weight in grams
            return $grams;
        }
        // no match -- may be both invalid format or empty string
        return 'NULL';
    }
}

?>
