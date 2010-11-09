<?php

/**
 * Shop Customer
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Test!
 */

/**
 * Customer as used in the Shop.
 *
 * Extends the User class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  module_shop
 */
class Customer extends User
{
    /**
     * Creates a Customer
     * @access  public
     * @return  Customer            The Customer
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function __construct()
    {
        parent::__construct();
    }


    /**
     * Authenticate a Customer using his user name and password.
     * @param   string  $userName   The user name
     * @param   string  $password   The password
     * @return  Customer    The Customer object on success, false otherwise.
     */
    function auth($userName, $password)
    {
        if (!parent::auth($userName, $password)) return false;
        $objUser = FWUser::getFWUserObject()->objUser;
        $customer_id = $objUser->getId();
DBG::log("Customer::auth(): This: ".var_export($objUser, true));
DBG::log("Customer::auth(): Usergroups: ".var_export($objUser->getAssociatedGroupIds(), true));
    }


    /**
     * Get the ID
     * @return  integer         $id                 Customer ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getId()
    {
        return $this->id;
    }

    /**
     * Get the title
     * @return  string         The title
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getTitle()
    {
        return $this->getProfileAttribute('title');
    }
    /**
     * Set the title
     * @param   string         $title     The title
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setTitle($title)
    {
        return $this->setProfile(array('title' => array(0 => $title)));
    }

    /**
     * Get the first name
     * @return  string      The first name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getFirstname()
    {
        return $this->getProfileAttribute('firstname');
    }
    /**
     * Set the first name
     * @param   string         $firstname   The first name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setFirstname($firstname)
    {
        return $this->setProfile(array('firstname' => array(0 => $firstname)));
    }

    /**
     * Get the last name
     * @return  string    The last name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getLastname()
    {
        return $this->getProfileAttribute('lastname');
    }
    /**
     * Set the last name
     * @param   string         $lastname    The last name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setLastname($lastname)
    {
        return $this->setProfile(array('lastname' => array(0 => $lastname)));
    }

    /**
     * Get the company name
     * @return  string         The company name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getCompany()
    {
        return $this->getProfileAttribute('company');
    }
    /**
     * Set the company name
     * @param   string         $company     The company name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setCompany($company)
    {
        return $this->setProfile(array('company' => array(0 => $company)));
    }

    /**
     * Get the address
     * @return  string                 The address (street and number)
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getAddress()
    {
        return $this->getProfileAttribute('address');
    }
    /**
     * Set the address
     * @param   string         $address     The address (street and number)
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setAddress($address)
    {
        return $this->setProfile(array('address' => array(0 => $address)));
    }

    /**
     * Get the city name
     * @return  string         The city name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getCity()
    {
        return $this->getProfileAttribute('city');
    }
    /**
     * Set the city name
     * @param   string         $city    The city name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setCity($city)
    {
        return $this->setProfile(array('city' => array(0 => $city)));
    }

    /**
     * Get the zip code
     * @return  string         The zip code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getZip()
    {
        return $this->getProfileAttribute('zip');
    }
    /**
     * Set the zip code
     * @param   string         $zip         The zip code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setZip($zip)
    {
        return $this->setProfile(array('zip' => array(0 => $zip)));
    }

    /**
     * Get the country ID
     * @return  integer                       The Country ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getCountryId()
    {
        return $this->getProfileAttribute('country');
    }
    /**
     * Set the country ID
     * @param   integer         $country_id       The Country ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setCountryId($country_id)
    {
        return $this->setProfile(array('country' => array(0 => $country_id)));
    }

    /**
     * Get the phone number
     * @return  string                        The phone number
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getPhone()
    {
        return $this->getProfileAttribute('phone_private');
    }
    /**
     * Set the phone
     * @param  string         $phone_private  The phone number
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setPhone($phone_private)
    {
        return $this->setProfile(array('phone_private' => array(0 => $phone_private)));
    }

    /**
     * Get the fax number
     * @return  string                      The fax number
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getFax()
    {
        return $this->getProfileAttribute('phone_fax');
    }
    /**
     * Set the fax number
     * @param   string         $phone_fax   The fax number
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setFax($phone_fax)
    {
        return $this->setProfile(array('phone_fax' => array(0 => $phone_fax)));
    }

    /**
     * Get the company note
     * @return  string         The company note
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getCompanynote()
    {
        $index = SettingDb::getValue('user_attribute_notes');
        if (!$index) return null;
        return $this->getProfileAttribute($index);
    }
    /**
     * Set the company note
     * @param   string         $companynote    The company note
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setCompanynote($companynote)
    {
        $index = SettingDb::getValue('user_attribute_notes');
        if (!$index) return null;
        return $this->setProfile(array($index => array(0 => $companynote)));
    }

    /**
     * Get the reseller status
     * @return  boolean                         True if the customer is a
     *                                          reseller, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function isReseller()
    {
        $index = SettingDb::getValue('user_attribute_customer_type');
        if (!$index) return null;
        return $this->getProfileAttribute($index);
    }
    /**
     * Set the reseller status
     *
     * The reseller status is set to true if the argument
     * evaluates to boolean true, false otherwise.
     * @param   boolean     $resellerStatus    The reseller status value
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setResellerStatus($resellerstatus)
    {
        $index = SettingDb::getValue('user_attribute_reseller_status');
        if (!$index) return null;
        return $this->setProfile(array($index => array(0 => $resellerstatus)));
    }


    /**
     * Delete the Customer from the database
     *
     * Also deletes all of her orders
     * @param   integer     $customerId The Customer ID
     * @return  boolean                 True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function delete()
    {
        if (!Orders::deleteByCustomer()) return false;
        return parent::delete();
    }


    /**
     * Select a Customer by ID from the database.
     * @static
     * @param   integer     $id     The Customer ID
     * @return  Customer            The Customer object on success,
     *                              false otherwise
     */
    static function getById($id)
    {
        $objCustomer = new Customer();
        $objCustomer = $objCustomer->getUser($id);
        if (!$objCustomer) return false;
DBG::log("Customer::getById($id): Usergroups: ".var_export($this->getAssociatedGroupIds(), true));
    }


    /**
     * Returns an array of Customer data for Mailtemplate substitution
     * @return    array               The Customer data substitution array
     * @see       Mailtemplate::substitute()
     */
    function getSubstitutionArray()
    {
// See below.
//        $index_notes = SettingDb::getValue('user_attribute_notes');
//        $index_type = SettingDb::getValue('user_attribute_customer_type');
//        $index_reseller = SettingDb::getValue('user_attribute_reseller_status');
        return array(
            'CUSTOMER_USERNAME'   => $this->username,
            'CUSTOMER_PASSWORD'   => (isset($_SESSION['shop']['password'])
                ? $_SESSION['shop']['password'] : '******'),
            'CUSTOMER_ID'         => $this->id,
            'CUSTOMER_EMAIL'      => $this->email,
            'CUSTOMER_COMPANY'    => $this->company,
            'CUSTOMER_TITLE'      => $this->getProfileAttribute('title'),
            'CUSTOMER_FIRSTNAME'  => $this->getProfileAttribute('firstname'),
            'CUSTOMER_LASTNAME'   => $this->getProfileAttribute('lastname'),
            'CUSTOMER_ADDRESS'    => $this->getProfileAttribute('address'),
            'CUSTOMER_ZIP'        => $this->getProfileAttribute('zip'),
            'CUSTOMER_CITY'       => $this->getProfileAttribute('city'),
            'CUSTOMER_COUNTRY'    => Country::getNameById($this->getProfileAttribute('country')),
            'CUSTOMER_PHONE'      => $this->getProfileAttribute('phone'),
            'CUSTOMER_FAX'        => $this->getProfileAttribute('fax'),
// There are not used in any MailTemplate so far:
//            'CUSTOMER_COUNTRY_ID' => $this->getProfileAttribute('country'),
//            'CUSTOMER_NOTE'       => $this->getProfileAttribute($index_notes),
//            'CUSTOMER_TYPE'       => $this->getProfileAttribute($index_type),
//            'CUSTOMER_RESELLER'   => $this->getProfileAttribute($index_reseller),
        );
    }


    /**
     * Updates the password of the Customer with the given e-mail address
     * @param   string    $email        The e-mail address
     * @param   string    $password     The new password
     * @return  boolean                 True on success, false otherwise
     */
    static function updatePassword($email, $password)
    {
        global $objFWUser;

        $objUser = $objFWUser->objUser->getUsers(
            array('email' => $email));
        if (!$objUser) return false;
        $objUser->setPassword($password);
        return $objUser->store();
    }


    static function errorHandler()
    {
        global $objFWUser;
        require_once(ASCMS_CORE_PATH.'/DbTool.class.php');

        // To be removed at the end
        $table_name = DBPREFIX.'module_shop'.MODULE_INDEX.'_customers';
        // No use trying when it's not there
        if (!DbTool::table_exists($table_name)) return false;

        // Create missing User_Profile_Attributes
        $index_notes = SettingDb::getValue('user_attribute_notes');
        if (!$index_notes) {
            $objUpa = new User_Profile_Attribute();
            $objUpa->setNames(array(
                1 => 'Notizen',
                2 => 'Notes',
// TODO: Translate
                3 => 'Notes', 4 => 'Notes', 5 => 'Notes', 6 => 'Notes',
            ));
            $objUpa->setType('text');
            if (!$objUpa->store()) {
die("Customer::errorHandler(): Error: failed to create User_Profile_Attribute 'notes', code dkjds984hu3");
            }
            if (!SettingDb::add('user_attribute_notes', $objUpa->getId(), 61)) {
die("Customer::errorHandler(): Error: failed to add User_Profile_Attribute 'notes' setting, code haf4udfd7rdf");
            }
        }

        $arrResellerId = array();
        $arrCustomerId = array();
        $query = "
            SELECT `customer`.`customerid`, `order`.`customer_lang`,
                   `prefix`, `firstname`, `lastname`,
                   `company`, `address`, `city`, `zip`,
                   `country_id`, `phone`, `fax`, `email`,
                   `username`, `password`, `company_note`, `is_reseller`,
                   `customer_status`, `register_date`,
                   `group_id`
              FROM `$table_name` AS `customer`
             INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_orders` AS `order`
             USING `customerid`
             ORDER BY `customerid` ASC";
        $objResult = DbTool::sql($query);
        if (!$objResult) return false;
        while (!$objResult->EOF) {
//            $customer_id = $objResult->fields['customerid'];
            $lang_id = $objResult->fields['customer_lang'];
            $lang_id = FWLanguage::getLangIdByIso639_1($lang_id);
            if (!$lang_id) $lang_id = FRONTEND_LANG_ID;
            $email = $objResult->fields['email'];
            $objUser = $objFWUser->objUser->getUser(array('email' => $email));
            if (!$objUser) {
                $objCustomer = new Customer();
                $objCustomer->setTitle($objResult->fields['prefix']);
                $objCustomer->setCompany($objResult->fields['company']);
                $objCustomer->setFirstname($objResult->fields['firstname']);
                $objCustomer->setLastname($objResult->fields['lastname']);
                $objCustomer->setAddress($objResult->fields['address']);
                $objCustomer->setCity($objResult->fields['city']);
                $objCustomer->setZip($objResult->fields['zip']);
                $objCustomer->setCountryId($objResult->fields['country_id']);
                $objCustomer->setPhone($objResult->fields['phone']);
                $objCustomer->setFax($objResult->fields['fax']);
                $objCustomer->setEmail($objResult->fields['email']);
                $objCustomer->setCompanynote($objResult->fields['companynote']);
                $objCustomer->setActiveStatus($objResult->fields['customer_status']);
                // Handled by a UserGroup now, see below
                //$objCustomer->setResellerStatus($objResult->fields['is_reseller']);
                $objCustomer->setRegisterDate($objResult->fields['registerdate']);
// TODO: In what form do we migrate and handle discount groups?
//                $objCustomer->setGroupId($objResult->fields['discount_group']);
                $objCustomer->setUserName($objResult->fields['username']);
                // Directly copy the md5ed password
                $objCustomer->password = $objResult->fields['password'];
                $objCustomer->setFrontendLanguage($lang_id);
            }
            if ($objResult->fields['is_reseller']) {
                $arrResellerId[$objCustomer->getId()] = true;
            } else {
                $arrCustomerId[$objCustomer->getId()] = true;
            }
            if (!$objCustomer->store()) {
die("Customer::errorHandler(): Error: failed to store customer, code ahas3u5redsw");
            }
            // Update the Orders table with the new Customer ID
            // Note that we use the unambiguous e-mail field, not the
            // primary ID.  The latter could be inconsistent after the first
            // update already!
            if (!DbTool::sql("
                UPDATE `".DBPREFIX."module_shop_orders`
                   SET `customerid`=".$objCustomer->getId()."
                 WHERE `email`='".addslashes($email)."'")
            ) {
die("Customer::errorHandler(): Error: failed to update orders, code har7rh342dfg");
            }
        }

        // Create missing UserGroups for customers and resellers
        $group_id_customer = SettingDb::getValue('usergroup_id_customer');
        if ($group_id_customer) {
            $objGroup = $objFWUser->objGroup->getGroup($group_id_customer);
        } else {
            $objGroup = new UserGroup();
            $objGroup->setActiveStatus(true);
            $objGroup->setDescription('Online Shop Endkunden');
            $objGroup->setName('Shop Endkunden');
            $objGroup->setType('frontend');
        }
        $objGroup->setUsers(array_keys($arrCustomerId));
        if (!$objGroup->store()) {
die("Customer::errorHandler(): Error: failed to store UserGroup for customers, code aha4auza43ssa");
        }
        if (!SettingDb::add('usergroup_id_customer', $objGroup->getId(), 62)) {
die("Customer::errorHandler(): Error: failed to add UserGroup setting for customers, code ahbrh32edga");
        }
        $group_id_reseller = SettingDb::getValue('usergroup_id_reseller');
        if ($group_id_reseller) {
            $objGroup = $objFWUser->objGroup->getGroup($group_id_reseller);
        } else {
            $objGroup = new UserGroup();
            $objGroup->setActiveStatus(true);
            $objGroup->setDescription('Online Shop Wiederverkäufer');
            $objGroup->setName('Shop Wiederverkäufer');
            $objGroup->setType('frontend');
        }
        $objGroup->setUsers(array_keys($arrResellerId));
        if (!$objGroup->store()) {
die("Customer::errorHandler(): Error: failed to create UserGroup for resellers, code herzz5232fhdh");
        }
        if (!SettingDb::add('usergroup_id_reseller', $objGroup->getId(), 63)) {
die("Customer::errorHandler(): Error: failed to add UserGroup setting for resellers, code djmsuers323gs");
        }

//        DbTool::drop_table($table_name);

        // Always
        return false;
    }

}

?>
