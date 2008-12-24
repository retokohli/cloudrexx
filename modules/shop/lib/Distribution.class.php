<?php

/**
 * Distribution class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 */

/**
 * Provides methods for handling different distribution types
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @access      public
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 */
class Distribution
{
    /**
     * The types of distribution
     * @static
     * @access  private
     * @var     array
     */
    private static $arrDistributionTypes = array(
        'delivery',
        'download',
        'none',
    );

    /**
     * The default distribution type
     *
     * Must be set to one of the values of {@link $arrDistributionTypes}.
     * @static
     * @access  private
     * @var     string
     */
    private static $defaultDistributionType = 'delivery';


    /**
     * OBSOLETE -- All is static now.
     * Set up a Distribution object (PHP5)
     *
     * Mind that there is one additional delivery type here, as compared to
     * the database, called 'undefined' (index 0, zero). This type *MUST NOT* be tried to
     * be written to the database, but is only used to determine whether
     * the user actually chose a valid distribution type.
     * @access      public
     * @return      Distribution object
    function __construct()
    {
    }
     */


    /**
     * Verifies whether the string argument is the name of a valid
     * Distribution type.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @param   string      $string
     * @return  boolean                 True for valid distribution types,
     *                                  false otherwise
     * @static
     */
    static function isDistributionType($string)
    {
        if (array_search($string, self::$arrDistributionTypes) !== false)
            return true;
        return false;
    }


    /**
     * Returns the default distribution type as string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @return  string                  The default distribution type
     * @static
     */
    static function getDefault()
    {
        return self::$defaultDistributionType;
    }


    /**
     * Returns a string containing the HTML code for the distribution type
     * dropdown menu.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @param   string  $selected   The distribution type to preselect
     * @param   string  $menuName   The name and ID for the select element
     * @param   string  $selectAttributes   Optional attributes for the select tag
     * @return  string              The dropdown menu code
     * @static
     */
    static function getDistributionMenu(
        $selected='', $menuName='shopDistribution',
        $onChange='', $selectAttributes='')
    {
        $menu =
            "<select name='$menuName' id='$menuName'".
            ($selectAttributes ? ' '.$selectAttributes : '').
            ($onChange         ? ' onchange="'.$onChange.'"' : '').">".
            self::getDistributionMenuoptions($selected).
            "</select>\n";
        return $menu;
    }


    /**
     * Returns a string containing the HTML code for the distribution type
     * dropdown menu options.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @param   string  $selected   The distribution type to preselect
     * @return  string              The HTML dropdown menu options code
     * @static
     */
    static function getDistributionMenuoptions($selected='')
    {
        global $_ARRAYLANG;

        $menuoptions = ($selected == ''
            ? '<option value="" selected="selected">'.
              $_ARRAYLANG['TXT_SHOP_PLEASE_SELECT'].
              "</option>\n"
            : ''
        );
        foreach (self::$arrDistributionTypes as $type) {
            $menuoptions .=
                '<option value="'.$type.'"'.
                ($selected == $type
                    ? ' selected="selected"' : ''
                ).'>'.$_ARRAYLANG['TXT_DISTRIBUTION_'.strtoupper($type)].
                "</option>\n";
        }
        return $menuoptions;
    }

}

?>
