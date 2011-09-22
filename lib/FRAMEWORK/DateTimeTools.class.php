<?php

/**
 * Date and time helper functions
 *
 * Add more methods and formats as needed.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @since       3.0.0
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  lib_framework
 */

/**
 * Date and time helper functions
 *
 * Add more methods and formats as needed.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @since       3.0.0
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  lib_framework
 */
class DateTimeTools
{
    /**
     * Months of the year in the current frontend language
     *
     * See {@see init()}
     * @var   array
     */
    private static $arrMoy = null;

    /**
     * Days of the week in the current frontend language
     *
     * See {@see init()}
     * @var   array
     */
    private static $arrDow = null;


    /**
     * Initializes internal data, called on demand by the methods that
     * depend on it
     */
    static function init()
    {
        global $_CORELANG;

        if (!self::$arrMoy) {
            self::$arrMoy = explode(',', $_CORELANG['TXT_CORE_MONTH_ARRAY']);
            unset(self::$arrMoy[0]);
        }
        if (!self::$arrDow) {
            self::$arrDow = explode(',', $_CORELANG['TXT_CORE_DAY_ARRAY']);
        }
    }


    /**
     * Registers the JavaScript code for jQueryUi.Datepicker
     *
     * Also activates jQueryUi and tries to load the current language and use
     * that as the default.
     * Add element specific defaults and code in your method.
     */
    static function addDatepickerJs()
    {
        static $language_code = null;

        // Only run once
        if ($language_code) return;
        JS::activate('jqueryui');
        $language_code = FWLanguage::getLanguageCodeById(FRONTEND_LANG_ID);
//DBG::log("Language ID ".FRONTEND_LANG_ID.", code $language_code");
        // Must load timepicker as well, because the region file accesses it
        JS::registerJS(
            'lib/javascript/jquery/ui/jquery-ui-timepicker-addon.js');
// TODO: Add more languages to the i18n folder!
        JS::registerJS(
            'lib/javascript/jquery/ui/i18n/'.
            'jquery.ui.datepicker-'.$language_code.'.js');
        JS::registerCode('
jQuery(document).ready(function($) {
  $.datepicker.setDefaults($.datepicker.regional["'.$language_code.'"]);
});
');
    }


    /**
     * Returns the date for the given timestamp in a format like
     * "32. Januamber 2010"
     *
     * Set the core language variable TXT_CORE_DATE_D_MMM_YYYY
     * accordingly for other locales.
     * $time defaults to the current time if unset.
     * @param   integer   $time   The optional timestamp
     * @return  string            The formatted date
     */
    static function date_D_MMM_YYY($time=null)
    {
        global $_CORELANG;

        if (!self::$arrMoy) self::init();
        if (!isset($time)) $time = time();
        return sprintf(
            $_CORELANG['TXT_CORE_DATE_D_MMM_YYYY'],
            date('d', $time),
            self::$arrMoy[date('n', $time)],
            date('Y', $time));
    }


    /**
     * Returns the date for the given timestamp in a format like
     * "Sondertag, 32. Januamber 2010"
     *
     * Calls {@see date_D_MMM_YYY()} to format the date and preprends
     * the weekday name.
     * Set the core language variable TXT_CORE_DATE_WWW_DATE
     * accordingly for other locales.
     * $time defaults to the current time if unset.
     * @param   integer   $time   The optional timestamp
     * @return  string            The formatted date
     */
    static function date_WWW_D_MMM_YYYY($time=null)
    {
        global $_CORELANG;

        if (!self::$arrDow) self::init();
        if (!isset($time)) $time = time();
        return sprintf(
            $_CORELANG['TXT_CORE_DATE_WWW_DATE'],
            self::$arrDow[date('w', $time)],
            self::date_D_MMM_YYY($time));
    }


    /**
     * Returns the date for the given timestamp in a format like
     * "Januamber 2010"
     *
     * $time defaults to the current time if unset.
     * @param   integer   $time   The optional timestamp
     * @return  string            The formatted date
     */
    static function date_MMM_YYYY($time=null)
    {
        global $_CORELANG;


        if (!self::$arrMoy) self::init();
        if (!isset($time)) $time = time();
        return sprintf(
            $_CORELANG['TXT_CORE_DATE_MMM_YYYY'],
            self::$arrMoy[date('n', $time)],
            date('Y', $time));
    }


    /**
     * Returns an array of the names of the months of the year
     * in the current frontend language
     *
     * Indexed by the ordinal value, one-based
     * @return  array             The month array
     */
    static function month_names()
    {
        if (!self::$arrMoy) self::init();
        return self::$arrMoy;
    }


    /**
     * Returns an array of day numbers formatted according to locale and
     * indexed by corresponding integer values
     *
     * The number of entries will always be 31 ([1..31]).
     * @return  array               The array of day numbers
     */
    static function day_numbers()
    {
        global $_CORELANG;
        static $arrDay = null;

        if (!$arrDay) {
            $arrDay = array();
            foreach (range(1, 31) as $day) {
                $arrDay[$day] =
                    sprintf($_CORELANG['TXT_CORE_DATE_D'], $day);
            }
        }
        return $arrDay;
    }

}
