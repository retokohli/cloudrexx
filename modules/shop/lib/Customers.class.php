<?php

/**
 * Shop Customer
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.1.0
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
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 */
class Customers
{
    /**
     * Static Customer
     * @var   Customer
     */
    private static $objCustomer = null;


    /**
     * Create a Customers helper object (PHP5)
     */
    function __construct()
    {
    }


    static function get(
        $filter=null, $search=null, $arrSort=null,
        $arrAttributes=null, $limit=null, $offset=0
    ) {
        if (is_null(self::$objCustomer))
            self::$objCustomer = new Customer();
        return self::$objCustomer->getUsers(
            $filter, $search, $arrSort,
            $arrAttributes, $limit, $offset);
    }


    /**
     * Returns a string with HTML code for the Customer type
     * dropdown menu options
     * @param   integer     $selected   The optional preselected type
     * @return  string                  The Menuoptions HTML code
     * @static
     */
    static function getTypeMenuoptions($selected=-1)
    {
        global $_ARRAYLANG;

        $arrType = ($selected < 0
            ? array(
                -1 => '-- '.$_ARRAYLANG['TXT_CUSTOMER_TYP'].' --')
            : array())
          + array(
            0 => $_ARRAYLANG['TXT_CUSTOMER'],
            1 => $_ARRAYLANG['TXT_RESELLER'],
        );
        return Html::getOptions($arrType, $selected);
    }


    /**
     * Returns a string with HTML code for the Customer status
     * dropdown menu options
     * @param   integer     $selected   The optional preselected status
     * @return  string                  The Menuoptions HTML code
     * @static
     */
    static function getActiveMenuoptions($selected)
    {
        global $_ARRAYLANG;

        $arrStatus = array(
            -1 => '-- '.$_ARRAYLANG['TXT_STATUS'].' --',
             0 => $_ARRAYLANG['TXT_INACTIVE'],
             1 => $_ARRAYLANG['TXT_ACTIVE'],
        );
        return Html::getOptions($arrStatus, $selected);
    }


    /**
     * Returns a string with HTML code for the Customer sorting
     * dropdown menu options
     * @param   integer     $selected   The optional preselected order
     * @return  string                  The Menuoptions HTML code
     * @static
     */
    static function getSortMenuoptions($selected='id')
    {
        global $_ARRAYLANG;

        $arrField = array(
            'id'        => $_ARRAYLANG['TXT_SHOP_ID'],
            'lastname'  => $_ARRAYLANG['TXT_LAST_NAME'],
            'firstname' => $_ARRAYLANG['TXT_FIRST_NAME'],
            'company'   => $_ARRAYLANG['TXT_COMPANY'],
        );
        return Html::getOptions($arrField, $selected);
    }


    /**
     * Returns a string representing the name of a customer
     *
     * The format of the string is determined by the optional
     * $format parameter in sprintf() format:
     *  - %1$s : First name
     *  - %2$s : Last name
     *  - %3$u : ID
     * Defaults to '%2$s %1$s (%3$u)'
     * @param   integer   $customer_id    The Customer ID
     * @param   string    $format         The optional format string
     * @return  string                    The Customer name
     */
    static function getNameById($customer_id, $format=null)
    {
        $objCustomer = Customer::getById($customer_id);
        if (!$objCustomer) return false;
        if (!isset($format)) $format = '%2$s %1$s (%3$u)';
        return sprintf(
            $format,
            $objCustomer->getFirstName(),
            $objCustomer->getLastName(),
            $objCustomer->getId()
        );
    }


    /**
     * Returns an array of Customer names, ordered by last names, ascending
     *
     * If $inactive is true, inactive Customers are included.
     * See {@see getNameById()} for details on the $format parameter.
     * @param   boolean   $inactive     Include inactive Customers if true.
     *                                  Defaults to false
     * @param   string    $format       The optional format string
     * @return  array                   The array of Customer names
     */
    static function getNameArray($inactive=false, $format=null)
    {
        global $objDatabase;

        $query = "
            SELECT `customerid`
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_customers
             WHERE 1".
            ($inactive ? '' : ' AND `customer_status`=1')."
             ORDER BY lastname ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $arrNames = array();
        while (!$objResult->EOF) {
            $customer_id = $objResult->fields['customerid'];
            $arrNames[$customer_id] = self::getNameById($customer_id, $format);
            $objResult->MoveNext();
        }
        return $arrNames;
    }

}

?>
