<?php

/**
 * Shop Customer helper class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 */

/**
 * Shop Customer helper methods
 *
 * All static.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 */
class Customers
{
    /*
     * OBSOLETE -- All the methods here are static
     * Create a Customers helper object (PHP5)
    function __construct()
    {
    }
     */


    /**
     * Returns a string with HTML code for the Customer type dropdown menu.
     * @param   integer     $selectedType   The optional preselected type
     * @return  string                      The Menu HTML code
     * @static
     * @todo    Remove that type array from the method code
     */
    static function getCustomerTypeMenu($selectedType=-1)
    {
        global $_ARRAYLANG;

        $arrType = array(
            -1 => 'TXT_CUSTOMER_TYP',
             0 => 'TXT_CUSTOMER',
             1 => 'TXT_RESELLER'
        );
        $strMenu = '';
        foreach ($arrType as $index => $strType) {
            $strMenu .=
                '<option value="'.$index.'"'.
                ($selectedType == $index ? ' selected="selected"' : '').
                '>'.
                ($index == -1
                    ? '-- '.$_ARRAYLANG[$strType].' --'
                    : $_ARRAYLANG[$strType]
                ).
                '</option>';
        }
        return $strMenu;
    }


    /**
     * Returns a string with HTML code for the Customer sorting dropdown menu.
     * @param   integer     $selectedField  The optional preselected field name
     * @return  string                      The Menu HTML code
     * @todo    Remove that field array from the method code
     * @static
     */
    static function getCustomerSortMenu($selectedField='')
    {
        global $_ARRAYLANG;

        $arrField = array(
            'lastname'  => 'TXT_LAST_NAME',
            'firstname' => 'TXT_FIRST_NAME',
            'company'   => 'TXT_COMPANY'
        );
        $strMenu = '';
        foreach ($arrField as $index => $strField) {
            $strMenu .=
                '<option value="'.$index.'"'.
                ($selectedField == $index ? ' selected="selected"' : '').
                '>'.$_ARRAYLANG[$strField].'</option>';
        }
        return $strMenu;
    }

}

?>
