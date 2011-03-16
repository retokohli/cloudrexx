<?php

/**
 * Shop Customer
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Test!
 */

/*

Changes to the customer table:
ALTER TABLE `contrexx_module_shop_customers`
ADD `group_id` INT(10) UNSIGNED NULL DEFAULT NULL;

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
class Customer
{
    /**
     * All Customer table field names.
     *
     * This is used to generate queries in {@see getByWildcards()}.
     * Note that the ID, countryId and password fields are excluded here!
     * Use the keys here as the field names in the pattern array argument
     * for {@see getByWildcards()}, not the real database table field names!
     * @var array   $fieldNames
     */
    private $fieldNames = array(
        'userName'       => 'username',
        'prefix'         => 'prefix',
        'firstName'      => 'firstname',
        'lastName'       => 'lastname',
        'company'        => 'company',
        'address'        => 'address',
        'city'           => 'city',
        'zip'            => 'zip',
        'phone'          => 'phone',
        'fax'            => 'fax',
        'email'          => 'email',
        'ccNumber'       => 'ccnumber',
        'ccDate'         => 'ccdate',
        'ccName'         => 'ccname',
        'ccCode'         => 'cvc_code',
        'companyNote'    => 'company_note',
        'registerDate'   => 'register_date',
    );

    /**
     * @var     string          $prefix     The customers' prefix (Sir, Madam, etc.)
     * @access  private
     */
    private $prefix = '';
    /**
     * @var     string          $firstName  The customers' first name
     * @access  private
     */
    private $firstName = '';
    /**
     * @var     string          $lastName   The customers' last name
     * @access  private
     */
    private $lastName = '';
    /**
     * @var     string          $company    The customers' company
     * @access  private
     */
    private $company = '';
    /**
     * @var     string          $address    The customers' address (street and number)
     * @access  private
     */
    private $address = '';
    /**
     * @var     string          $city       The customers' city
     * @access  private
     */
    private $city = '';
    /**
     * @var     string          $zip        The customers' zip code
     * @access  private
     */
    private $zip = '';
    /**
     * @var     string          $countryId  The customer country's ID
     * @access  private
     */
    private $countryId = 0;
    /**
     * @var     string          $phone      The customers' phone number
     * @access  private
     */
    private $phone = '';
    /**
     * @var     string          $fax        The customers' fax number
     * @access  private
     */
    private $fax = '';
    /**
     * @var     string          $email      The customers' e-mail address
     * @access  private
     */
    private $email = '';
    /**
     * @var     string          $ccNumber   The customers' credit card number
     * @access  private
     */
    private $ccNumber = '';
    /**
     * @var     string          $ccDate     The customers' credit card expiry date
     * @access  private
     */
    private $ccDate = '';
    /**
     * @var     string          $ccName     The customers' credit card holder name
     * @access  private
     */
    private $ccName = '';
    /**
     * @var     string          $ccCode     The customers' credit card verification code
     * @access  private
     */
    private $ccCode = '';
    /**
     * @var     string          $userName   The customers' user name
     * @access  private
     */
    private $userName = '';
    /**
     * @var     string          $password   The customers' password
     * @access  private
     */
    private $password = '';
    /**
     * @var     string      $companyNote   A note regarding the customers' company
     * @access  private
     */
    private $companyNote = '';
    /**
     * @var     boolean     $resellerStatus   True if the customer is a reseller
     * @access  private
     */
    private $resellerStatus = false;
    /**
     * @var     string      $registerDate   The date the customer was inserted into the database
     * @access  private
     */
    private $registerDate = '0000-00-00';
    /**
     * @var     boolean     $activeStatus   The customers' active status (0: inactive, 1: active)
     * @access  private
     */
    private $activeStatus = true;

    /**
     * The ID of the customer group
     * @var     integer
     */
    private $groupId = 0;

    /**
     * Create a Customer
     *
     * If the optional argument $id is set, the corresponding
     * Customer is updated if she exists.
     * Otherwise, a new Customer is created.
     * Set the remaining object variables by calling the appropriate
     * access methods.
     * @access  public
     * @param   string  $prefix     The customers' prefix (Sir, Madam, etc.)
     * @param   string  $firstName  The customers' first name
     * @param   string  $lastName   The customers' last name
     * @param   string  $company    The customers' company
     * @param   string  $address    The customers' address (street and number)
     * @param   string  $city       The customers' city
     * @param   string  $zip        The customers' zip code
     * @param   integer $countryId  The customer country's ID
     * @param   string  $phone      The customers' phone number
     * @param   string  $fax        The customers' fax number
     * @return  Customer            The Customer
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function __construct(
        $prefix, $firstName, $lastName, $company,
        $address, $city, $zip, $countryId,
        $phone, $fax, $id=0)
    {
        // assign & check
        $this->id        = intval($id);
        $this->prefix    = strip_tags(trim($prefix));
        $this->firstName = strip_tags(trim($firstName));
        $this->lastName  = strip_tags(trim($lastName));
        $this->company   = strip_tags(trim($company));
        $this->address   = strip_tags(trim($address));
        $this->city      = strip_tags(trim($city));
        $this->zip       = strip_tags(trim($zip));
        $this->countryId = intval($countryId);
        $this->phone     = strip_tags(trim($phone));
        $this->fax       = strip_tags(trim($fax));
        // The remaining fields keep their default values for the time being.
    }


    /**
     * Authenticate a Customer using his user name and password.
     * @param   string  $userName   The user name
     * @param   string  $password   The password
     * @return  Customer    The Customer object on success, false otherwise.
     */
    function authenticate($userName, $password)
    {
        global $objDatabase;

        $query = "
            SELECT customerid
            FROM ".DBPREFIX."module_shop".MODULE_INDEX."_customers
            WHERE username='$userName'
              AND password='$password'
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        if ($objResult->RecordCount() != 1) {
            return false;
        }
        $id = $objResult->fields['customerid'];
        return Customer::getById($id);
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
     * Set the ID -- NOT ALLOWED
     * @see {Customer::makeClone()}
     */

    /**
     * Get the prefix
     * @return  string         The prefix (Sir, Madam, etc.)
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getPrefix()
    {
        return $this->prefix;
    }
    /**
     * Set the prefix
     * @param   string         $prefix     The prefix
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setPrefix($prefix)
    {
        $this->prefix = strip_tags(trim($prefix, " \t"));
    }

    /**
     * Get the first name
     * @return  string      The first name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getFirstName()
    {
        return $this->firstName;
    }
    /**
     * Set the first name
     * @param   string         $firstName   The first name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setFirstName($firstName)
    {
        $this->firstName = strip_tags(trim($firstName, " \t"));
    }

    /**
     * Get the last name
     * @return  string    The last name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getLastName()
    {
        return $this->lastName;
    }
    /**
     * Set the last name
     * @param   string         $lastName    The last name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setLastName($lastName)
    {
        $this->lastName = strip_tags(trim($lastName, " \t"));
    }

    /**
     * Get the company name
     * @return  string         The company name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getCompany()
    {
        return $this->company;
    }
    /**
     * Set the company name
     * @param   string         $company     The company name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setCompany($company)
    {
        $this->company = strip_tags(trim($company, " \t"));
    }

    /**
     * Get the address
     * @return  string                 The address (street and number)
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getAddress()
    {
        return $this->address;
    }
    /**
     * Set the address
     * @param   string         $address     The address (street and number)
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setAddress($address)
    {
        $this->address = strip_tags(trim($address, " \t"));
    }

    /**
     * Get the city name
     * @return  string         The city name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getCity()
    {
        return $this->city;
    }
    /**
     * Set the city name
     * @param   string         $city    The city name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setCity($city)
    {
        $this->city = strip_tags(trim($city, " \t"));
    }

    /**
     * Get the zip code
     * @return  string         The zip code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getZip()
    {
        return $this->zip;
    }
    /**
     * Set the zip code
     * @param   string         $zip         The zip code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setZip($zip)
    {
        $this->zip = strip_tags(trim($zip, " \t"));
    }

    /**
     * Get the country ID
     * @return  integer         $countryId      The country ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getCountryId()
    {
        return $this->countryId;
    }
    /**
     * Set the country ID
     * @param   integer         $countryId       The country ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setCountryId($countryId)
    {
        $this->countryId = intval($countryId);
    }

    /**
     * Get the phone number
     * @return  string         The phone number
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getPhone()
    {
        return $this->phone;
    }
    /**
     * Set the phone
     * @param  string         $phone        The phone number
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setPhone($phone)
    {
        $this->phone = strip_tags(trim($phone, " \t"));
    }

    /**
     * Get the fax number
     * @return  string         The fax number
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getFax()
    {
        return $this->fax;
    }
    /**
     * Set the fax number
     * @param   string         $fax       The fax number
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setFax($fax)
    {
        $this->fax = strip_tags(trim($fax, " \t"));
    }

    /**
     * Get the email address
     * @return  string          The email address
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getEmail()
    {
        return $this->email;
    }
    /**
     * Set the email address
     * @param   string         $email       The email address
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setEmail($email)
    {
        $this->email = strip_tags(trim($email, " \t"));
    }

    /**
     * Get the credit card number
     * @return  string         $ccNumber            The credit card number
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getCcNumber()
    {
        return $this->ccNumber;
    }
    /**
     * Set the ccNumber
     * @param   string         The credit card number
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setCcNumber($ccNumber)
    {
        $this->ccNumber = strip_tags(trim($ccNumber, " \t"));
    }

    /**
     * Get the credit card expiry date
     * @return  string         The credit card expiry date
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getCcDate()
    {
        return $this->ccDate;
    }
    /**
     * Set the credit card expiry date
     * @param   string         $ccDate    The credit card expiry date
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setCcDate($ccDate)
    {
        $this->ccDate = strip_tags(trim($ccDate, " \t"));
    }

    /**
     * Get the credit card holders' name
     * @return  string         The credit card holders' name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getCcName()
    {
        return $this->ccName;
    }
    /**
     * Set the credit card holders' name
     * @param   string         $ccName      The credit card holders' name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setCcName($ccName)
    {
        $this->ccName = strip_tags(trim($ccName, " \t"));
    }

    /**
     * Get the credit card code
     * @return  string         The credit card code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getCcCode()
    {
        return $this->ccCode;
    }
    /**
     * Set the credit card code
     * @param   string         $ccCode      The credit card code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setCcCode($ccCode)
    {
        $this->ccCode = strip_tags(trim($ccCode, " \t"));
    }

    /**
     * Get the user name
     * @return  string         The user name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getUserName()
    {
        return $this->userName;
    }
    /**
     * Set the user name
     * @param   string         $userName    The user name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setUserName($userName)
    {
        $this->userName = strip_tags(trim($userName, " \t"));
    }

    /**
     * Get the md5 hash of the password
     * @return  string         The md5 hash of the password
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getPasswordMd5()
    {
        return $this->password;
    }
    /**
     * Set the password
     * @param   string         $password    The password
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setPassword($password)
    {
        $this->password = md5($password);
    }

    /**
     * Get the company note
     * @return  string         The company note
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getCompanyNote()
    {
        return $this->companyNote;
    }
    /**
     * Set the company note
     * @param   string         $companyNote    The company note
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setCompanyNote($companyNote)
    {
        $this->companyNote = strip_tags(trim($companyNote, " \t"));
    }

    /**
     * Get the reseller status
     * @return  boolean                         True if the customer is a
     *                                          reseller, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function isReseller()
    {
        return $this->resellerStatus;
    }
    /**
     * Set the reseller status
     *
     * The reseller status is set to true if the argument
     * evaluates to boolean true, false otherwise.
     * @param   boolean     $resellerStatus    The reseller status value
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setResellerStatus($resellerStatus)
    {
        $this->resellerStatus = ($resellerStatus ? true : false);
    }

    /**
     * Get the register date
     * @return  string         The register date
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getRegisterDate()
    {
        return $this->registerDate;
    }
    /**
     * Set the register date
     * @param   string         $registerDate    The register date
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setRegisterDate($registerDate)
    {
        $this->registerDate = strip_tags(trim($registerDate, " \t"));
    }

    /**
     * Get the active status
     *
     * The customer is inactive if his activeStatus is false, active otherwise.
     * @return  boolean         The active status
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getActiveStatus()
    {
        return $this->activeStatus;
    }
    /**
     * Set the active status
     *
     * The active status value is set to true if the argument evaluates to
     * the boolean true value, false otherwise.
     * @param   boolean     $activeStatus    The active status
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setActiveStatus($activeStatus)
    {
        $this->activeStatus = ($activeStatus ? true : false);
    }

    /**
     * Get the customer group ID
     * @return  string         The customer group ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getGroupId()
    {
        return $this->groupId;
    }
    /**
     * Set the customer group ID
     * @param   string         $groupId       The customer group ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setGroupId($groupId)
    {
        $this->groupId = intval($groupId);
    }


    /**
     * Clone the Customer
     *
     * Note that this does NOT create a copy in any way, but simply clears
     * the Customer ID.  Upon storing this Customer, a new ID is created.
     * @return  void
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function makeClone()
    {
        $this->id = '';
    }


    /**
     * Delete the Customer specified by its ID from the database.
     * @param   integer     $customerId The Customer ID
     * @return  boolean                 True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function delete()
    {
        global $objDatabase;

        if (!$this->id) {
            return false;
        }
        $query = "
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_customers
            WHERE customerid=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        return true;
    }


    /**
     * Stores the Customer object in the database.
     *
     * Either updates (id > 0) or inserts (id == 0) the object.
     * @return  boolean     True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function store()
    {
        if ($this->id > 0) {
            return ($this->update());
        }
        return ($this->insert());
    }


    /**
     * Update this Customer in the database.
     * @return  boolean     True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function update()
    {
        global $objDatabase;

        $query = "
            UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_customers
               SET prefix='".contrexx_addslashes($this->prefix)."',
                   firstname='".contrexx_addslashes($this->firstName)."',
                   lastname='".contrexx_addslashes($this->lastName)."',
                   company='".contrexx_addslashes($this->company)."',
                   address='".contrexx_addslashes($this->address)."',
                   city='".contrexx_addslashes($this->city)."',
                   zip='".contrexx_addslashes($this->zip)."',
                   country_id='".contrexx_addslashes($this->countryId)."',
                   phone='".contrexx_addslashes($this->phone)."',
                   fax='".contrexx_addslashes($this->fax)."',
                   email='".contrexx_addslashes($this->email)."',
                   ccnumber='".contrexx_addslashes($this->ccNumber)."',
                   ccdate='".contrexx_addslashes($this->ccDate)."',
                   ccname='".contrexx_addslashes($this->ccName)."',
                   cvc_code='".contrexx_addslashes($this->ccCode)."',
                   username='".contrexx_addslashes($this->userName)."',
                   password='".contrexx_addslashes($this->password)."',
                   company_note='".contrexx_addslashes($this->companyNote )."',
                   is_reseller=".($this->resellerStatus ? 1 : 0).",
                   register_date='".contrexx_addslashes($this->registerDate)."',
                   customer_status=".($this->activeStatus ? 1 : 0).",
                   group_id=".intval($this->groupId)."
             WHERE customerid=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        return true;
    }


    /**
     * Insert this Customer into the database.
     * Returns the result of the query.
     *
     * @return  boolean         True on success, false otherwise
     */
    function insert()
    {
        global $objDatabase;

        $query = "
            INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_customers (
                prefix, firstname, lastname, company, address, city, zip,
                country_id, phone, fax, email,
                ccnumber, ccdate, ccname, cvc_code,
                username, password, company_note, is_reseller,
                customer_status, register_date,
                group_id
            ) VALUES (
                '".contrexx_addslashes($this->prefix)."',
                '".contrexx_addslashes($this->firstName)."',
                '".contrexx_addslashes($this->lastName)."',
                '".contrexx_addslashes($this->company)."',
                '".contrexx_addslashes($this->address)."',
                '".contrexx_addslashes($this->city)."',
                '".contrexx_addslashes($this->zip)."',
                 ".intval($this->countryId).",
                '".contrexx_addslashes($this->phone)."',
                '".contrexx_addslashes($this->fax)."',
                '".contrexx_addslashes($this->email)."',
                '".contrexx_addslashes($this->ccNumber)."',
                '".contrexx_addslashes($this->ccDate)."',
                '".contrexx_addslashes($this->ccName)."',
                '".contrexx_addslashes($this->ccCode)."',
                '".contrexx_addslashes($this->userName)."',
                '".contrexx_addslashes($this->password)."',
                '".contrexx_addslashes($this->companyNote )."',
                ".($this->resellerStatus ? 1 : 0).",
                ".($this->activeStatus ? 1 : 0).",
                NOW(),
                ".intval($this->groupId)."
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        // my brand new ID
        $this->id = $objDatabase->Insert_ID();
        return true;
    }


    /**
     * Select a Customer by ID from the database.
     * @static
     * @param   integer     $id     The Customer ID
     * @return  Customer            The Customer object on success,
     *                              false otherwise
     */
    //static
    function getById($id)
    {
        global $objDatabase;

        $query = "
            SELECT *
            FROM ".DBPREFIX."module_shop".MODULE_INDEX."_customers
            WHERE customerid=$id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        if ($objResult->RecordCount() != 1) {
            return false;
        }
        $objCustomer = new Customer(
            $objResult->fields['prefix'],
            $objResult->fields['firstname'],
            $objResult->fields['lastname'],
            $objResult->fields['company'],
            $objResult->fields['address'],
            $objResult->fields['city'],
            $objResult->fields['zip'],
            $objResult->fields['country_id'],
            $objResult->fields['phone'],
            $objResult->fields['fax'],
            $objResult->fields['customerid']
        );
        $objCustomer->email    = $objResult->fields['email'];
        $objCustomer->ccNumber = $objResult->fields['ccnumber'];
        $objCustomer->ccDate   = $objResult->fields['ccdate'];
        $objCustomer->ccName   = $objResult->fields['ccname'];
        $objCustomer->ccCode   = $objResult->fields['cvc_code'];
        $objCustomer->userName = $objResult->fields['username'];
        $objCustomer->password = $objResult->fields['password'];
        $objCustomer->companyNote    = $objResult->fields['company_note'];
        $objCustomer->resellerStatus = ($objResult->fields['is_reseller'] ? true : false);
        $objCustomer->registerDate   = $objResult->fields['register_date'];
        $objCustomer->activeStatus   = ($objResult->fields['customer_status'] ? true : false);
        $objCustomer->groupId = $objResult->fields['group_id'];
        return $objCustomer;
    }


    /**
     * Returns an array of Customer objects found by wildcard.
     *
     * Takes an array of patterns as an argument, whose keys represent
     * customer fields.  {@see $this->fieldNames} for a list of the valid
     * names.
     * The customer table is then queried with the values of the respective
     * fields as wildcards, adding appropriate SQL syntax.
     * @static
     * @param   array   $arrPattern     The pattern array.
     * @return  array                   An array of Customers on success,
     *                                  false otherwise
     */
    //static
    function getByWildcard($arrPattern)
    {
        global $objDatabase;

        $query = '';
        foreach ($arrPattern as $fieldName => $pattern) {
            if (in_array($fieldName, array_keys($this->fieldNames))) {
                if ($query) {
                    $query .= "
                        OR ".$this->fieldNames[$fieldName]." LIKE '%".
                        contrexx_addslashes($pattern)."%'";
                } else {
                    $query  = "
                        SELECT customerid FROM ".DBPREFIX."module_shop".MODULE_INDEX."_customers
                        WHERE ".$this->fieldNames[$fieldName]." LIKE '%".
                        contrexx_addslashes($pattern)."%'";
                }
            }
        }
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $arrCustomer = array();
        while (!$objResult->EOF) {
            $arrCustomer[] = Customer::getById($objResult->fields['id']);
            $objResult->MoveNext();
        }
        return $arrCustomer;
    }


    /**
     * Return a textual representation of the object
     * for testing and debugging purposes.
     */
    function toString()
    {
        return "
            id        : $this->id,<br />
            prefix    : $this->prefix,<br />
            firstName : $this->firstName,<br />
            lastName  : $this->lastName,<br />
            company   : $this->company,<br />
            address   : $this->address,<br />
            city      : $this->city,<br />
            zip       : $this->zip,<br />
            countryId : $this->countryId,<br />
            phone     : $this->phone,<br />
            fax       : $this->fax,<br />
            email     : $this->email,<br />
            ccNumber  : $this->ccNumber,<br />
            ccDate    : $this->ccDate,<br />
            ccName    : $this->ccName,<br />
            ccCode    : $this->ccCode,<br />
            userName  : $this->userName,<br />
            password  : $this->password,<br />
            companyNote : $this->companyNote,<br />
            resellerStatus : ".($this->resellerStatus ? 1 : 0).",<br />
            registerDate : $this->registerDate,<br />
            activeStatus : ".($this->activeStatus ? 1 : 0).",<br />
            groupId : $this->groupId<br />
        ";
    }

}

?>
