<?php
/**
 * Shop Customer
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     $Id: 1.0.0$
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Test!
 */


/**
 * Customer as used in the Shop.
 *
 * Includes access methods and data layer.
 * Do not, I repeat, do not access private fields, or even try
 * to access the database directly!
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     $Id: 1.0.0$
 * @package     contrexx
 * @subpackage  module_shop
 */
class Customers
{
    /**
     * Create a Customers helper object (PHP4)
     */
    function Customers()
    {
        $this->__construct();
    }


    /**
     * Create a Customers helper object (PHP5)
     */
    function __construct()
    {

    }


    /**
     * Returns a string with HTML code for the Customer type dropdown menu.
     * @param   integer     $selectedType   The optional preselected type
     * @return  string                      The Menu HTML code
     */
    function getCustomerTypeMenu($selectedType=-1)
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
                (   $index == -1
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
     */
    function getCustomerSortMenu($selectedField='')
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
