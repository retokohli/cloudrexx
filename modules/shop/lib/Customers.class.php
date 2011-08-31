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


    /**
     * Returns the Users matching the given parameter values
     *
     * Parameters are like for {@see User::getUsers()}, without the attributes
     * @param   array     $filter       The optional filter array
     * @param   string    $search       The optional search string
     * @param   array     $arrSort      The optional order array
     * @param   integer   $limit        The optional upper limit
     * @param   integer   $offset       The optional offset
     * @return  Customer                The Customer object on success,
     *                                  false(?) otherwise
     */
    static function get(
        $filter=null, $search=null, $arrSort=null, $limit=null, $offset=0
    ) {
        if (is_null(self::$objCustomer))
            self::$objCustomer = new Customer();
        return self::$objCustomer->getUsers(
            $filter, $search, $arrSort, null, $limit, $offset);
    }


    /**
     * Returns the number of Customers in the Usergroups with IDs present
     * in the given array
     *
     * If $arrGroupId is empty, both final customers and resellers will
     * automatically be included.
     * Mind that the Customers are counted separately for each Usergroup,
     * thus the latter must not overlap, since you don't want to count some
     * Customers twice!
     * @param   array     $arrGroupId   The optional array of Usergroup IDs
     * @return  integer                 The Customer count on success,
     *                                  0 (zero) otherwise
     */
    static function getCount($arrGroupId=null)
    {
        global $objFWUser;

        if (empty($arrGroupId)) {
            $arrGroupId = array(
                SettingDb::getValue('usergroup_id_reseller'),
                SettingDb::getValue('usergroup_id_customer'));
        }
        $user_count = 0;
        foreach ($arrGroupId as $usergroup_id) {
            $objGroup = $objFWUser->objGroup->getGroup($usergroup_id);
            if (!$objGroup) {
DBG::log("Customers::getCount(): ERROR: Failed to get Usergroup for ID $usergroup_id");
                continue;
            }
DBG::log("Customers::getCount(): Group ID $usergroup_id: Count ".$objGroup->getUserCount());
            $user_count += $objGroup->getUserCount();
        }
        return $user_count;
    }


    /**
     * Returns a string with HTML code for the Customer type
     * dropdown menu options
     * @param   integer     $selected       The optional preselected type
     * @param   boolean     $include_none   Prepend a null element if true.
     *                                      Defaults to false
     * @return  string                      The Menuoptions HTML code
     * @static
     */
    static function getTypeMenuoptions($selected, $include_none=false)
    {
        global $_ARRAYLANG;

        $arrType = ($include_none
            ? array(
                '' => '-- '.$_ARRAYLANG['TXT_CUSTOMER_TYP'].' --')
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
     * @param   integer     $selected       The optional preselected status
     * @param   boolean     $include_none   Prepend a null element if true.
     *                                      Defaults to false
     * @return  string                      The Menuoptions HTML code
     * @static
     */
    static function getActiveMenuoptions($selected, $include_none=false)
    {
        global $_ARRAYLANG;

        $arrStatus = ($include_none
            ? array('' => '-- '.$_ARRAYLANG['TXT_STATUS'].' --', )
            : array())
          + array(
             0 => $_ARRAYLANG['TXT_INACTIVE'],
             1 => $_ARRAYLANG['TXT_ACTIVE'], );
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
        ++$customer_id;
        ++$format;
die("Customer::getNameById(): Obsolete method called");
/*
        $objCustomer = Customer::getById($customer_id);
        if (!$objCustomer) return false;
        if (!isset($format)) $format = '%2$s %1$s (%3$u)';
        return sprintf($format,
            $objCustomer->firstname(),
            $objCustomer->lastname(),
            $objCustomer->id()
        );
*/
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
        $objCustomer = Customers::get(
            ($inactive ? null : array('active' => true)),
            null,
            array('lastname' => 'ASC', 'firstname' => 'ASC', ));
        $arrNames = array();
        while (!$objCustomer->EOF) {
//$objCustomer = new Customer();
            $name = $objCustomer->getName($format);
            $arrNames[$objCustomer->id()] = $name;
            $objCustomer->next();
        }
        return $arrNames;
    }


    /**
     * Returns HTML options for selecting the gender in any menu
     * @param   string  $selected   The optional preselected gender
     *                              as defined in
     *                              {@see User_Profile_Attribute::$arrCoreAttributes}
     * @return  string              The HTML options string
     */
    static function getGenderMenuoptions($selected=null)
    {
        global $_ARRAYLANG;

        return Html::getOptions(array(
            'gender_male' => $_ARRAYLANG['TXT_SHOP_GENDER_MALE'],
            'gender_female' => $_ARRAYLANG['TXT_SHOP_GENDER_FEMALE'],
        ), $selected);
    }
}

?>
