<?php

/**
 * Shop Product class
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */

/**
 * Value Added Tax (VAT)
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Vat.class.php';
/**
 * Weight
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Weight.class.php';
/**
 * Distribution (aka Handler)
 */
require_once ASCMS_MODULE_PATH."/shop/lib/Distribution.class.php";
/**
 * Customer object with database layer.
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Customer.class.php';
/**
 * Product Attribute
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Attribute.class.php';
/**
 * Product Attributes - Helper and display methods
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Attributes.class.php';

require_once ASCMS_FRAMEWORK_PATH."/Image.class.php";

/**
 * Product as available in the Shop.
 *
 * Includes access methods and data layer.
 * Do not, I repeat, do not access private fields, or even try
 * to access the database directly!
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 */
class Product
{
    /**
     * Text keys
     */
    const TEXT_NAME  = 'shop_product_name';
    const TEXT_SHORT = 'shop_product_short';
    const TEXT_LONG  = 'shop_product_long';
    const TEXT_CODE  = 'shop_product_code';
    const TEXT_URI   = 'shop_product_uri';
    const TEXT_KEYS  = 'shop_product_keys';

    /**
     * @var     string          $code               Product code
     * @access  private
     */
    private $code = '';
    /**
     * @var     integer         $text_code_id         The Text ID of the
     *                                                Product code
     * @access  private
     */
    private $text_code_id = null;
    /**
     * @var     integer         $category_id         Category of the Product
     * @access  private
     */
    private $category_id = 0;
    /**
     * @var     string          $name               Product name
     * @access  private
     */
    private $name = '';
    /**
     * @var     integer         $text_name_id       The Text ID of the name
     * @access  private
     */
    private $text_name_id = 0;
    /**
     * @var     Distribution    $distribution       Distribution type
     * @access  private
     */
    private $distribution = 'delivery';
    /**
     * @var     double          $price              Product price
     * @access  private
     */
    private $price = 0.00;
    /**
     * @var     integer         $ord              Sorting order of the Product
     * @access  private
     */
    private $ord = 1;
    /**
     * @var     integer         $weight             Product weight (in grams)
     * @access  private
     */
    private $weight = 0;
    /**
     * @var     integer         $id                 The Product ID
     * @access  private
     */
    private $id = 0;
    /**
     * The status is either active (true), or inactive (false).
     * @var     boolean         $active             Product active status
     * @access  private
     */
    private $active = true;
    /**
     * @var     string          $pictures           Product pictures
     * @access  private
     */
    private $pictures = '';
    /**
     * @var     double          $resellerprice      Product price for resellers
     * @access  private
     */
    private $resellerprice = 0;
    /**
     * @var     string            $short        Product short description
     * @access  private
     */
    private $short = '';
    /**
     * @var     integer         $text_short_id  The Text ID of the
     *                                              short description
     * @access  private
     */
    private $text_short_id = 0;
    /**
     * @var     string          $long        Product long description
     * @access  private
     */
    private $long = '';
    /**
     * @var     integer         $text_long_id   The Text ID of the
     *                                              long description
     * @access  private
     */
    private $text_long_id = 0;
    /**
     * @var     integer         $stock              Product stock
     * @access  private
     */
    private $stock = 10;
    /**
     * @var     boolean         $stock_visible     Product stock visibility
     * @access  private
     */
    private $stock_visible = false;
    /**
     * @var     double          $discountprice      Product discount price
     * @access  private
     */
    private $discountprice = 0.00;
    /**
     * @var     boolean         $discount_active     Product is special offer
     * @access  private
     */
    private $discount_active = false;
    /**
     * @var     boolean         $b2b              Product available for b2b
     * @access  private
     */
    private $b2b = true;
    /**
     * @var     boolean         $b2c              Product available for b2c
     * @access  private
     */
    private $b2c = true;
    /**
     * For future use -- currently not used in the Shop!
     * @var     string          $startdate          Product startdate
     * @access  private
     */
    private $startdate = '0000-00-00';
    /**
     * For future use -- currently not used in the Shop!
     * @var     string          $endDate            Product enddate
     * @access  private
     */
    private $endDate = '0000-00-00';
    /**
     * @var     integer         $manufacturer_id     Product manufacturer ID
     * @access  private
     */
    private $manufacturer_id = 0;
    /**
     * @var     string          $uri       Product external link
     * @access  private
     */
    private $uri = '';
    /**
     * @var     integer         $text_uri_id       The Text ID for the URI
     * @access  private
     */
    private $text_uri_id = '';
    /**
     * @var     integer         $vat_id              Product VAT ID
     * @access  private
     */
    private $vat_id = 0;
    /**
     * The Product flags
     * @var string
     */
    private $flags = '';
    /**
     * The assigned (frontend) user group IDs
     *
     * Comma separated list
     * @var string
     */
    private $usergroup_ids = '';
    /**
     * The count type discount group ID
     * @var     integer
     */
    private $group_id = 0;
    /**
     * The article group ID
     * @var     integer
     */
    private $article_id = 0;
    /**
     * The list of keywords
     * @var     string
     */
    private $keywords = '';
    /**
     * @var     integer         $text_keys_id   The Text ID of the keywords
     * @access  private
     */
    private $text_keys_id = 0;
    /**
     * @var     array   $arrRelations   The relation array
     * @access  private
     */
    private $arrRelations = false;


    /**
     * Create a Product
     *
     * If the optional argument $id is set, the corresponding
     * Product is updated, if it exists.  Otherwise, a new Product is created.
     * Set the remaining object variables by calling the appropriate
     * access methods.
     * @access  public
     * @param   string  $code           The Product code
     * @param   integer $category_id     The Category ID of the Product
     * @param   string  $name           The Product name
     * @param   string  $distribution   The Distribution type
     * @param   double  $price          The Product price
     * @param   integer $active         The active status
     * @param   integer $ord          The sorting order
     * @param   integer $weight         The Product weight
     * @param   integer $id             The optional Product ID to be updated
     * @return  Product                 The Product
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function __construct(
        $code, $category_id, $name, $distribution, $price,
        $active, $ord, $weight, $id=0
    ) {
        // Assign & check
        $this->code         = strip_tags($code);
        $this->category_id   = intval($category_id);
        $this->name         = strip_tags($name);
        $this->distribution = strip_tags($distribution);
        $this->price        = floatval($price);
        $this->ord = intval($ord);
        $this->weight       = intval($weight);
        $this->id           = intval($id);
        $this->active($active);

        if ($this->ord <= 0) { $this->ord = 0; }
        // Default values for everything else as stated above

        // Enable cloning of Products with Attributes
        if ($this->id > 0) {
            $this->arrRelations =
                Attributes::getRelationArray($this->id);
        }
    }


    /**
     * The ID
     * @return  integer                             Product ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function id()
    {
        return $this->id;
    }
    /**
     * Set the ID -- NOT ALLOWED
     * See {@link Product::makeClone()}
     */

    /**
     * The Product code
     * @param   string    $code         The optional Product code
     * @return  string                  The Product code
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function code($code=null)
    {
        if (isset($code)) {
            $this->code = trim(strip_tags($code));
        }
        return $this->code;
    }

    /**
     * The Product name
     * @param   string    $name         The optional Product name
     * @return  string                  The Product name
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function name($name=null)
    {
        if (isset($name) && $name != '') {
            $this->name = trim(strip_tags($name));
        }
        return $this->name;
    }

    /**
     * The Category ID
     * @return  integer   $category_id  The optional Category ID
     * @param   integer                 The Category ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function category_id($category_id=null)
    {
        if (isset($category_id)) {
            $this->category_id = intval($category_id);
        }
        return $this->category_id;
    }

    /**
     * The Product price
     * @param   double    $price        The optional Product price
     * @return  double                  The Product price
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function price($price=null)
    {
        if (isset($price)) {
            $this->price = floatval($price);
        }
        return $this->price;
    }

    /**
     * The Product ordinal value
     * @param   integer   $ord          The optional ordinal value
     * @return  integer                 The ordinal value
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function ord($ord=null)
    {
        if (isset($ord)) {
            $this->ord = intval($ord);
        }
        return $this->ord;
    }

    /**
     * The Distribution type
     * @param   string    $distribution The optional distribution type
     * @return  string                  The distribution type
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function distribution($distribution=null)
    {
        if (isset($distribution)) {
            $this->distribution =
                (Distribution::isDistributionType($distribution)
                    ? $distribution : Distribution::getDefault());
        }
        return $this->distribution;
    }

    /**
     * The active status
     * @param   boolean   $active       The optional active status
     * @return  boolean                 The active status
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function active($active=null)
    {
        if (isset($active)) {
            $this->active = (boolean)$active;
        }
        return $this->active;
    }

    /**
     * The pictures
     * @param   string    $pictures     The optional encoded picture string
     * @return  string                  The Encoded picture string
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function pictures($pictures=null)
    {
        if (isset($pictures)) {
            $this->pictures = $pictures;
        }
        return $this->pictures;
    }

    /**
     * The reseller price
     * @param   double    $resellerprice  The optional reseller price
     * @return  double                    The reseller price
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function resellerprice($resellerprice=null)
    {
        if (isset($resellerprice)) {
            $this->resellerprice = floatval($resellerprice);
        }
        return $this->resellerprice;
    }

    /**
     * The short description
     * @param   string    $short          The optional short description
     * @return  string                    The short description
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function short($short=null)
    {
        if (isset($short)) {
            $this->short = trim($short);
        }
        return $this->short;
    }

    /**
     * The long description
     * @param   string    $long           The optional long description
     * @return  string                    The long description
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function long($long=null)
    {
        if (isset($long)) {
            $this->long = trim($long);
        }
        return $this->long;
    }

    /**
     * The stock
     * @param   integer   $stock          The optional stock
     * @return  integer                   The stock
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function stock($stock=null)
    {
        if (isset($stock)) {
            $this->stock = intval($stock);
        }
        return $this->stock;
    }

    /**
     * The stock visibility
     * @param   boolean   $stock_visible  The optional stock visibility
     * @return  boolean                   The stock visibility
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function stock_visible($stock_visible=null)
    {
        if (isset($stock_visible)) {
            $this->stock_visible = (boolean)$stock_visible;
        }
        return $this->stock_visible;
    }

    /**
     * The discount price
     * @param   double    $discountprice  The optional discount price
     * @return  double                    The discount price
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function discountprice($discountprice=null)
    {
        if (isset($discountprice)) {
            $this->discountprice = floatval($discountprice);
        }
        return $this->discountprice;
    }

    /**
     * The special offer flag
     * @param   boolean   $discount_active  The optional special offer flag
     * @return  boolean                     The special offer flag
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function discount_active($discount_active=null)
    {
        if (isset($discount_active)) {
            $this->discount_active = (boolean)$discount_active;
        }
        return $this->discount_active;
    }

    /**
     * The Product flags
     * @param   string    $flags            The optional Product flags
     * @return  string                      The Product flags
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function flags($flags=null)
    {
        if (isset($flags)) {
            $this->flags = $flags;
        }
        return $this->flags;
    }
    /**
     * Add a flag
     *
     * If the flag is already present, nothing is changed.
     * Note that the match is case sensitive.
     * @param   string    $flag             The flag to be added
     * @return  boolean                     Always true for the time being
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function addFlag($flag)
    {
        if (!preg_match("/$flag/", $this->flags)) {
            $this->flags .= ' '.$flag;
        }
        return true;
    }
    /**
     * Remove a flag
     *
     * If the flag is not present, nothing is changed.
     * Note that the match is case insensitive.
     * @param   string    $flag             The flag to be removed
     * @return  boolean                     Always true for the time being
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function removeFlag($flag)
    {
        $this->flags = trim(preg_replace("/\\s*$flag\\s*/i", ' ', $this->flags));
        return true;
    }
    /**
     * Test for a match with the Product flags.
     *
     * Note that the match is case sensitive.
     * @param   string    $flag             The Product flag to test
     * @return  boolean                     Boolean true if the flag is present,
     *                                      false otherwise.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function testFlag($flag)
    {
        return preg_match("/$flag/", $this->flags);
    }

    /**
     * The B2B flag
     * @param   boolean   $b2b              The optional B2B flag
     * @return  boolean                     The B2B flag
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function b2b($b2b=null)
    {
        if (isset($b2b)) {
            $this->b2b = (boolean)$b2b;
        }
        return $this->b2b;
    }

    /**
     * The B2C flag
     * @param   boolean   $b2c              The optional B2C flag
     * @return  boolean                     The B2C flag
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function b2c($b2c=null)
    {
        if (isset($b2c)) {
            $this->b2c = (boolean)$b2c;
        }
        return $this->b2c;
    }

    /**
     * The start date
     * @param   string    $startdate        The optional start date
     * @return  string                      The start date
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function startdate($startdate=null)
    {
        if (isset($startdate)) {
            $this->startdate =
                date(ASCMS_DATE_FORMAT_DATETIME, strtotime($startdate));
        }
        return $this->startdate;
    }

    /**
     * The end date
     * @param   string    $enddate          The optional end date
     * @return  string                      The end date
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function enddate($enddate=null)
    {
        if (isset($enddate)) {
            $this->enddate =
                date(ASCMS_DATE_FORMAT_DATETIME, strtotime($enddate));
        }
        return $this->enddate;
    }

    /**
     * The Manufacturer ID
     * @param   integer   $manufacturer     The optional Manufacturer ID
     * @return  integer                     The Manufacturer ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function manufacturer_id($manufacturer_id=null)
    {
        if (isset($manufacturer_id)) {
            $this->manufacturer_id = intval($manufacturer_id);
        }
        return $this->manufacturer_id;
    }

    /**
     * The external link
     * @param   string    $uri              The optional external link
     * @return  string                      The external link
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function uri($uri=null)
    {
        if (isset($uri)) {
            $this->uri = trim(strip_tags($uri));
        }
        return $this->uri;
    }

    /**
     * The VAT ID
     * @param   string    $vat_id           The optional VAT ID
     * @return  string                      The VAT ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function vat_id($vat_id=null)
    {
        if (isset($vat_id)) {
            $this->vat_id = intval($vat_id);
        }
        return $this->vat_id;
    }

    /**
     * The weight
     * @param   string    $weight           The optional weight
     * @return  string                      The weight
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function weight($weight=null)
    {
        if (isset($weight)) {
            $this->weight = intval($weight);
        }
        return $this->weight;
    }

    /**
     * The assigned Usergroups
     * @param   string    $usergroup_ids    The optional comma separated list
     *                                      of assigned user groups
     * @return  string                      The comma separated list
     *                                      of assigned user groups
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function usergroup_ids($usergroup_ids=null)
    {
        if (isset($usergroup_ids)) {
            $this->usergroup_ids = trim(strip_tags($usergroup_ids));
        }
        return $this->usergroup_ids;
    }

    /**
     * The keywords
     * @param   string    $keywords         The optional product keywords
     * @return  string                      The product keywords
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function keywords($keywords=null)
    {
        if (isset($keywords)) {
            $this->keywords = trim(strip_tags($keywords));
        }
        return $this->keywords;
    }


    /**
     * The visibility of the Product on the start page
     * @param   boolean   $shown_on_startpage   The optional visibility flag
     * @return  boolean                         The visibility flag
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function shown_on_startpage($shown_on_startpage=null)
    {
        if (isset($shown_on_startpage)) {
            if ($shown_on_startpage) {
                return $this->addFlag('__SHOWONSTARTPAGE__');
            }
            return $this->removeFlag('__SHOWONSTARTPAGE__');
        }
        return $this->testFlag('__SHOWONSTARTPAGE__');
    }


    /**
     * Return the correct Product price for any Customer and Product.
     *
     * Returns the reseller price if a valid Customer object is provided and
     * if this is of the type "reseller".
     * If this method is called without a valid Customer object,
     * the reseller price will never be returned.
     * @param   Customer    $objCustomer    The optional Customer object.
     * @return  double                      The Product price
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getCustomerPrice(&$objCustomer=false)
    {
        if (is_a($objCustomer, 'Customer') && $objCustomer->isReseller()) {
            return $this->resellerprice;
        }
        return $this->price;
    }


    /**
     * Return the current discounted price for any Product, if applicable.
     * @return  mixed                       The Product discount price,
     *                                      or null if there is no discount.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getDiscountedPrice()
    {
        if (!$this->hasDiscount()) return null;
        $price = $this->price;
        if ($this->discount_active) {
            $price = $this->discountprice;
        }
// NOTE: Add more conditions and rules as desired, i.e.
//        if ($this->testFlag('Outlet')) {
//            $discountRate = $this->getOutletDiscountRate();
//            $price = number_format(
//                $price * (100 - $discountRate) / 100,
//                2, '.', '');
//        }
        return $price;
    }


    /**
     * Returns boolean true if this Product has any kind of discount.
     *
     * This may either be the regular discount price if discount_active
     * is true, or the "Outlet" discount, or both.
     * Use {@link getDiscountPrice()} to get the correct discount price.
     * @return  boolean                 True if there is a discount,
     *                                  false otherwise.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function has_discount()
    {
        return $this->discount_active
// NOTE: Add more conditions and rules as desired, i.e.
//            || $this->is_outlet()
        ;
    }


    /**
     * Returns boolean true if this Product is flagged as "Outlet"
     *
     * Note that this is an example extension only.
     * @return  boolean                 True if this is "Outlet",
     *                                  false otherwise.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function is_outlet()
    {
        return $this->testFlag('Outlet');
    }


    /**
     * Return the discount rate for any Product in the virtual "Outlet"
     * Category.
     *
     * The rules for the discount are: 21% at the first date of the month,
     * plus an additional 1% per day, for a maximum rate of 51% on the 31st.
     * Note that this is an example extension only.
     * @return  integer                 The current Outlet discount rate
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getOutletDiscountRate()
    {
        $dayOfMonth = date('j');
        return 20 + $dayOfMonth;
    }


    /**
     * The count type discount group ID
     * @param   integer   $group_id       The optional group ID
     * @return  integer                   The group ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function group_id($group_id=null)
    {
        if (isset($group_id)) {
            $this->group_id = intval($group_id);
        }
        return $this->group_id;
    }

    /**
     * The article group ID
     * @param   integer   $article_id   The optional article group ID
     * @return  string                  The article group ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function article_id($article_id=null)
    {
        if (isset($article_id)) {
            $this->article_id = intval($article_id);
        }
        return $this->article_id;
    }


    /**
     * Clone the Product
     *
     * Note that this does NOT create a copy in any way, but simply clears
     * the Product ID.  Upon storing this Product, a new ID is created.
     * Also note that all Attributes *MUST* be link()ed after every
     * insert() in order for this to work properly!
     * @return      void
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function makeClone()
    {
        $this->id = 0;
    }


    /**
     * Delete this Product from the database.
     *
     * Associated Attributes and pictures are deleted with it.
     * @return  boolean                         True on success, false otherwise
     * @global  ADONewConnection  $objDatabase  Database connection object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function delete($flagDeleteImages=false)
    {
        global $objDatabase;

        if (!$this->id) return false;
        if ($flagDeleteImages) {
            // Heck, most of this should go into the ProductPicture class...
            // Split picture data into single pictures
            $arrPictures = split(':', $this->pictures);
            foreach ($arrPictures as $strPicture) {
                if (empty($strPicture)) continue;
                // Split picture into name, width, height -- all are base64
                // encoded!
                $arrPicture = explode('?', $strPicture);
                $strFileName = base64_decode($arrPicture[0]);
                // If it is the default image, skip it
                if (preg_match('/'.ShopLibrary::noPictureName.'$/', $strFileName))
                    continue;
                // Verify that no other Product uses the same picture.
                // $arrPicture[0] contains the encoded file name
                $query = "
                    SELECT picture FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
                     WHERE picture LIKE '%".addslashes($arrPicture[0])."%'";
                $objResult = $objDatabase->Execute($query);
                if ($objResult->RecordCount() == 1) {
                    // The only one -- it can be deleted.
                    // Delete the picture and thumbnail.
                    $thumbName = Image::getThumbnailPath($strFileName);
                    // Continue even if deleting the images fails
                    File::delete_file($strFileName);
                    File::delete_file($thumbName);
                }
            }
        }
        // Remove any Text records present
        if (!Text::deleteById($this->text_name_id))  return false;
        if (!Text::deleteById($this->text_short_id)) return false;
        if (!Text::deleteById($this->text_long_id))  return false;
        if (!Text::deleteById($this->text_keys_id))  return false;
        if (!Text::deleteById($this->text_code_id))  return false;
        if (!Text::deleteById($this->text_uri_id))   return false;
        // Delete the Product attribute relations and the Product itself
// TEST
        if (!Attributes::removeFromProduct($this->id)) {
        	return false;
        }
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
             WHERE id=$this->id");
        if (!$objResult) return false;
        return true;
    }


    /**
     * Test whether a record with the ID of this object is already present
     * in the database.
     * @return  boolean                     True if it exists, false otherwise
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function recordExists()
    {
        global $objDatabase;

        $query = "
            SELECT 1
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
             WHERE id=$this->id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->EOF) return false;
        return true;
    }


    /**
     * Stores the Product object in the database.
     *
     * Either updates or inserts the object, depending on the outcome
     * of the call to {@link recordExists()}.
     * Also stores associated Text records.
     * @return      boolean     True on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function store()
    {
        $this->text_name_id = Text::replace(
            $this->text_name_id, FRONTEND_LANG_ID, $this->name,
            MODULE_ID, self::TEXT_NAME);
        if (empty($this->text_name_id)) return false;
        $this->text_short_id = Text::replace(
            $this->text_short_id, FRONTEND_LANG_ID, $this->short,
            MODULE_ID, self::TEXT_SHORT);
        if (empty($this->text_short_id)) return false;
        $this->text_long_id = Text::replace(
            $this->text_long_id, FRONTEND_LANG_ID, $this->long,
            MODULE_ID, self::TEXT_LONG);
        if (empty($this->text_long_id)) return false;
        $this->text_keys_id = Text::replace(
            $this->text_keys_id, FRONTEND_LANG_ID, $this->keywords,
            MODULE_ID, self::TEXT_KEYS);
        if (empty($this->text_keys_id)) return false;
        $this->text_code_id = Text::replace(
            $this->text_code_id, FRONTEND_LANG_ID, $this->code,
            MODULE_ID, self::TEXT_CODE);
        if (empty($this->text_code_id)) return false;
        $this->text_uri_id = Text::replace(
            $this->text_uri_id, FRONTEND_LANG_ID, $this->uri,
            MODULE_ID, self::TEXT_URI);
        if (empty($this->text_uri_id)) return false;
        if ($this->recordExists()) {
            if (!$this->update()) return false;
            if (!Attributes::removeFromProduct($this->id)) return false;
        } else {
            if (!$this->insert()) return false;
        }
        // Store Attributes, if any
        if (is_array($this->arrRelations)) {
            foreach ($this->arrRelations as $value_id => $ord) {
                if (!Attributes::addOptionToProduct(
                    $value_id, $this->id, $ord
                )) return false;
            }
        }
        return true;
    }


    /**
     * Update this Product in the database.
     *
     * Note that associated Text records are not changed here, use
     * {@see store()} to do that.
     * @return      boolean                     True on success, false otherwise
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function update()
    {
        global $objDatabase;

        $query = "
            UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_products
            SET text_code_id=$this->text_code_id,
                picture='$this->pictures',
                text_name_id=$this->text_name_id,
                category_id=$this->category_id,
                distribution='$this->distribution',
                normalprice=$this->price,
                resellerprice=$this->resellerprice,
                text_short_id=$this->text_short_id,
                text_long_id=$this->text_long_id,
                stock=$this->stock,
                stock_visible=".($this->stock_visible ? 1 : 0).",
                discountprice=$this->discountprice,
                discount_active=".($this->discount_active ? 1 : 0).",
                active=".($this->active ? 1 : 0).",
                b2b=".($this->b2b ? 1 : 0).",
                b2c=".($this->b2c ? 1 : 0).",
                startdate='$this->startdate',
                enddate='$this->endDate',
                manufacturer_id=$this->manufacturer_id,
                text_uri_id=$this->text_uri_id,
                ord=$this->ord,
                vat_id=$this->vat_id,
                weight=$this->weight,
                flags='".addslashes($this->flags)."',
                usergroup_ids='$this->usergroup_ids',
                group_id=$this->group_id,
                article_id=$this->article_id,
                text_keys_id=$this->text_keys_id
          WHERE id=$this->id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        return true;
    }


    /**
     * Insert this Product into the database.
     *
     * Note that associated Text records are not changed here, use
     * {@see store()} to do that.
     * @return      boolean                     True on success, false otherwise
     * @global      ADONewConnection
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function insert()
    {
        global $objDatabase;

        $query = "
            INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_products (
                text_code_id, picture, text_name_id, category_id, distribution,
                normalprice, resellerprice,
                text_short_id, text_long_id,
                stock, stock_visible, discountprice, discount_active,
                active,
                b2b, b2c, startdate, enddate,
                manufacturer_id, text_uri_id,
                ord, vat_id, weight,
                flags, usergroup_ids,
                group_id, article_id, text_keys_id
            ) VALUES (
                $this->text_code_id, '$this->pictures',
                $this->text_name_id, $this->category_id,
                '$this->distribution',
                $this->price, $this->resellerprice,
                $this->text_short_id, $this->text_long_id,
                $this->stock, ".($this->stock_visible ? 1 : 0).",
                $this->discountprice, ".($this->discount_active ? 1 : 0).", ".
                ($this->active ? 1 : 0).", ".
                ($this->b2b ? 1 : 0).", ".($this->b2c ? 1 : 0).",
                '$this->startdate', '$this->endDate',
                $this->manufacturer_id, $this->text_uri_id,
                $this->ord, $this->vat_id, $this->weight,
                '".addslashes($this->flags)."',
                '$this->usergroup_ids',
                $this->group_id, $this->article_id,
                $this->text_keys_id
            )";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        // My brand new ID
        $this->id = $objDatabase->Insert_ID();
        return true;
    }


    /**
     * Select a Product by ID from the database.
     * @static
     * @param       integer     $id             The Product ID
     * @return      Product                     The Product object on success,
     *                                          false otherwise
     * @global      ADONewConnection
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function getById($id)
    {
        global $objDatabase;

        $arrSqlName = Text::getSqlSnippets(
            '`product`.`text_name_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_NAME
        );
        $arrSqlShort = Text::getSqlSnippets(
            '`product`.`text_short_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_SHORT
        );
        $arrSqlLong = Text::getSqlSnippets(
            '`product`.`text_long_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_LONG
        );
        $arrSqlKeyword = Text::getSqlSnippets(
            '`product`.`text_keys_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_KEYS
        );
        $arrSqlCode = Text::getSqlSnippets(
            '`product`.`text_code_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_CODE
        );
        $arrSqlUri = Text::getSqlSnippets(
            '`product`.`text_uri_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_URI
        );
        $query = "
            SELECT `product`.`id`, `product`.`category_id`,
                   `product`.`ord`, `product`.`active`, `product`.`weight`,
                   `product`.`picture`,
                   `product`.`normalprice`, `product`.`resellerprice`,
                   `product`.`discountprice`, `product`.`discount_active`,
                   `product`.`stock`, `product`.`stock_visible`,
                   `product`.`distribution`,
                   `product`.`startdate`, `product`.`enddate`,
                   `product`.`manufacturer_id`,
                   `product`.`b2b`, `product`.`b2c`,
                   `product`.`vat_id`,
                   `product`.`flags`,
                   `product`.`usergroup_ids`,
                   `product`.`group_id`, `product`.`article_id`".
                   $arrSqlName['field'].$arrSqlShort['field'].
                   $arrSqlLong['field'].$arrSqlKeyword['field'].
                   $arrSqlCode['field'].$arrSqlUri['field']."
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_products` AS `product`".
                   $arrSqlName['join'].$arrSqlShort['join'].
                   $arrSqlLong['join'].$arrSqlKeyword['join'].
                   $arrSqlCode['join'].$arrSqlUri['join']."
             WHERE `product`.`id`=$id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        if ($objResult->RecordCount() != 1) return false;

        $text_code_id = $objResult->fields[$arrSqlCode['name']];
        $strCode = $objResult->fields[$arrSqlCode['text']];
        if ($text_code_id && $strCode === null) {
            $objText = Text::getById($text_code_id, 0);
            $objText->markDifferentLanguage(FRONTEND_LANG_ID);
            $strCode = $objText->getText();
        }
        $text_name_id = $objResult->fields[$arrSqlName['name']];
        $strName = $objResult->fields[$arrSqlName['text']];
        if ($text_name_id && $strName === null) {
            $objText = Text::getById($text_name_id, 0);
            $objText->markDifferentLanguage(FRONTEND_LANG_ID);
            $strName = $objText->getText();
        }
        $text_short_id = $objResult->fields[$arrSqlShort['name']];
        $strShort = $objResult->fields[$arrSqlShort['text']];
        if ($text_short_id && $strShort === null) {
            $objText = Text::getById($text_short_id, 0);
            $objText->markDifferentLanguage(FRONTEND_LANG_ID);
            $strShort = $objText->getText();
        }
        $text_long_id = $objResult->fields[$arrSqlLong['name']];
        $strLong = $objResult->fields[$arrSqlLong['text']];
        if ($text_long_id && $strLong === null) {
            $objText = Text::getById($text_long_id, 0);
            $objText->markDifferentLanguage(FRONTEND_LANG_ID);
            $strLong = $objText->getText();
        }
        $text_uri_id = $objResult->fields[$arrSqlUri['name']];
        $strUri = $objResult->fields[$arrSqlUri['text']];
        if ($text_uri_id && $strUri === null) {
            $objText = Text::getById($text_uri_id, 0);
            $objText->markDifferentLanguage(FRONTEND_LANG_ID);
            $strUri = $objText->getText();
        }
        $text_keys_id = $objResult->fields[$arrSqlKeyword['name']];
        $strKeys = $objResult->fields[$arrSqlKeyword['text']];
        if ($text_keys_id && $strKeys === null) {
            $objText = Text::getById($text_keys_id, 0);
            $objText->markDifferentLanguage(FRONTEND_LANG_ID);
            $strKeys = $objText->getText();
        }
        $objProduct = new Product(
            $strCode,
            $objResult->fields['category_id'],
            $strName,
            $objResult->fields['distribution'],
            $objResult->fields['normalprice'],
            $objResult->fields['active'],
            $objResult->fields['ord'],
            $objResult->fields['weight'],
            $objResult->fields['id']
        );
        $objProduct->text_code_id = $text_code_id;
        $objProduct->text_name_id = $text_name_id;
        $objProduct->pictures = $objResult->fields['picture'];
        $objProduct->resellerprice = floatval($objResult->fields['resellerprice']);
        $objProduct->short = $strShort;
        $objProduct->text_short_id = $text_short_id;
        $objProduct->long = $strLong;
        $objProduct->text_long_id = $text_long_id;
        $objProduct->stock($objResult->fields['stock']);
        $objProduct->stock_visible($objResult->fields['stock_visible']);
        $objProduct->discountprice = floatval($objResult->fields['discountprice']);
        $objProduct->discount_active($objResult->fields['discount_active']);
        $objProduct->b2b($objResult->fields['b2b']);
        $objProduct->b2c($objResult->fields['b2c']);
        $objProduct->startdate($objResult->fields['startdate']);
        $objProduct->enddate($objResult->fields['enddate']);
        $objProduct->manufacturer_id = $objResult->fields['manufacturer_id'];
        $objProduct->uri = $strUri;
        $objProduct->text_uri_id = $text_uri_id;
        $objProduct->vat_id = $objResult->fields['vat_id'];
        $objProduct->flags = $objResult->fields['flags'];
        $objProduct->usergroup_ids = $objResult->fields['usergroup_ids'];
        $objProduct->group_id = $objResult->fields['group_id'];
        $objProduct->article_id = $objResult->fields['article_id'];
        $objProduct->keywords = $strKeys;
        $objProduct->text_keys_id = $text_keys_id;
        // Fetch the Product Attribute relations
        $objProduct->arrRelations =
            Attributes::getRelationArray($objProduct->id);
        return $objProduct;
    }


    /**
     * Add the given Product Attribute value ID to this object.
     *
     * Note that the relation is is only permanently created after
     * the object is store()d.
     * @param   integer     $value_id    The Product Attribute value ID
     * @param   integer     $ord      The sorting order value
     * @return  boolean                 True. Always.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function addAttribute($value_id, $ord)
    {
        $this->arrRelations[$value_id] = $ord;
        return true;
    }


    /**
     * Remove the given Product Attribute value ID from this object.
     *
     * Note that the relation is is only permanently destroyed after
     * the object is store()d.
     * Also note that this method always returns true. It cannot fail. :)
     * @param   integer     $value_id    The Product Attribute value ID
     * @return  boolean                 True. Always.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function deleteAttribute($value_id)
    {
        unset($this->arrRelations[$value_id]);
        return true;
    }


    /**
     * Remove all Product Attribute value IDs from this object.
     *
     * Note that the relations are only permanently destroyed after
     * the object is store()d.
     * @return  boolean                 True on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function clearAttributes()
    {
        $this->arrRelations = array();
        return true;
    }


    /**
     * Decrease the Product stock count
     *
     * This applies to "real", shipped goods only.  These have "delivery"
     * set as their "distribution" field value.
     * @param   integer   $quantity       The quantity to subtract
     *                                    from the stock
     * @return  boolean                   True on success, false otherwise
     */
    function decreaseStock($quantity)
    {
        global $objDatabase;

        $query = "
            UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_products
               SET stock=stock-$quantity
             WHERE id=$this->id
               AND distribution='delivery'";
        return (boolean)$objDatabase->Execute($query);
    }


    static function errorHandler()
    {
        require_once(ASCMS_CORE_PATH.'/DbTool.class.php');

DBG::activate(DBG_DB_FIREPHP);

        // Fix the Text table first
        Text::errorHandler();

        $table_name = DBPREFIX.'module_shop'.MODULE_INDEX.'_products';
        $table_structure = array(

            'id' => array('type' => 'INT(10)', 'unsigned' => true, 'auto_increment' => true, 'primary' => true),
            'text_name_id' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0', 'renamefrom' => 'title'),
            'text_short_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null, 'renamefrom' => 'shortdesc'),
            'text_long_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null, 'renamefrom' => 'description'),
            'text_code_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null, 'renamefrom' => 'product_id'),
            'text_uri_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null, 'renamefrom' => 'external_link'),
            'text_keys_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null, 'renamefrom' => 'keywords'),
            'normalprice' => array('type' => 'DECIMAL(9,2)', 'default' => '0.00'),
            'resellerprice' => array('type' => 'DECIMAL(9,2)', 'default' => '0.00'),
            'discountprice' => array('type' => 'DECIMAL(9,2)', 'default' => '0.00'),
            'discount_active' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'default' => '0', 'renamefrom' => 'is_special_offer'),
            'stock' => array('type' => 'INT(10)', 'default' => '10'),
            'stock_visible' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'default' => '1', 'renamefrom' => 'stock_visibility'),
            'active' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'default' => '1', 'renamefrom' => 'status'),
            'b2b' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'default' => '1'),
            'b2c' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'default' => '1'),
            'startdate' => array('type' => 'DATETIME', 'default' => '0000-00-00 00:00:00'),
            'enddate' => array('type' => 'DATETIME', 'default' => '0000-00-00 00:00:00'),
            'weight' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null),
            'category_id' => array('type' => 'INT(10)', 'unsigned' => true, 'renamefrom' => 'catid'),
            'vat_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null),
            'manufacturer_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null, 'renamefrom' => 'manufacturer'),
            'group_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null),
            'article_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null),
            'usergroup_ids' => array('type' => 'VARCHAR(4096)', 'notnull' => false, 'default' => null, 'renamefrom' => 'usergroup_ids'),
            'ord' => array('type' => 'INT(10)', 'default' => '0', 'renamefrom' => 'sort_order'),
            'distribution' => array('type' => 'VARCHAR(16)', 'default' => '', 'renamefrom' => 'handler'),
            'picture' => array('type' => 'VARCHAR(4096)', 'notnull' => false, 'default' => null),
            'flags' => array('type' => 'VARCHAR(4096)', 'notnull' => false, 'default' => null),
// Obsolete:
//`property1` varchar(100) COLLATE utf8_unicode_ci DEFAULT '',
//`property2` varchar(100) COLLATE utf8_unicode_ci DEFAULT '',
//`manufacturer_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
        );
        $table_index =  array(
            'group_id' => array('fields' => 'group_id',    'type' => 'KEY', ),
            'article_id' => array('fields' => 'article_id',    'type' => 'KEY', ),
            'flags' => array('fields' => 'flags',    'type' => 'FULLTEXT', ),
            'keywords' => array('fields' => 'keywords',    'type' => 'FULLTEXT', ),
        );

        if (DbTool::table_exists($table_name)) {
            if (DbTool::column_exists($table_name, 'title')) {
                // Migrate all Product strings to the Text table first
                Text::deleteByKey(self::TEXT_NAME);
                Text::deleteByKey(self::TEXT_SHORT);
                Text::deleteByKey(self::TEXT_LONG);
                Text::deleteByKey(self::TEXT_CODE);
                Text::deleteByKey(self::TEXT_URI);
                Text::deleteByKey(self::TEXT_KEYS);
                $objResult = DbTool::sql("
                    SELECT `id`, `title`, `shortdesc`, `description`,
                           `product_id`, `external_link`, `keywords`
                      FROM `$table_name`");
                if (!$objResult) {
die("Product::errorHandler(): Error: failed to query Product strings, code aerja3hbder");
                }
                while (!$objResult->EOF) {
                    $id = $objResult->fields['id'];
                    $name = $objResult->fields['title'];
                    $text_name_id = Text::replace(
                        null, FRONTEND_LANG_ID,
                        $name, MODULE_ID, self::TEXT_NAME);
                    if (!$text_name_id) {
die("Product::errorHandler(): Error: failed to migrate name '$name', code hrej5435fgdf");
                    }
                    $short = $objResult->fields['shortdesc'];
                    $text_short_id = Text::replace(
                        null, FRONTEND_LANG_ID,
                        $short, MODULE_ID, self::TEXT_SHORT);
                    if (!$text_short_id) {
die("Product::errorHandler(): Error: failed to migrate short '$short', code nadduaur344fd");
                    }
                    $long = $objResult->fields['description'];
                    $text_long_id = Text::replace(
                        null, FRONTEND_LANG_ID,
                        $long, MODULE_ID, self::TEXT_LONG);
                    if (!$text_long_id) {
die("Product::errorHandler(): Error: failed to migrate long '$long', code bwsai4wjyfd");
                    }
                    $code = $objResult->fields['product_id'];
                    $text_code_id = Text::replace(
                        null, FRONTEND_LANG_ID,
                        $code, MODULE_ID, self::TEXT_CODE);
                    if (!$text_code_id) {
die("Product::errorHandler(): Error: failed to migrate code '$code', code ajkar34rjdf");
                    }
                    $uri = $objResult->fields['external_link'];
                    $text_uri_id = Text::replace(
                        null, FRONTEND_LANG_ID,
                        $uri, MODULE_ID, self::TEXT_URI);
                    if (!$text_uri_id) {
die("Product::errorHandler(): Error: failed to migrate uri '$uri', code yfbrej43hsjd");
                    }
                    $keys = $objResult->fields['keywords'];
                    $text_keys_id = Text::replace(
                        null, FRONTEND_LANG_ID,
                        $keys, MODULE_ID, self::TEXT_KEYS);
                    if (!$text_keys_id) {
die("Product::errorHandler(): Error: failed to migrate keys '$keys', code fnyr842fhdd");
                    }
                    $objResult2 = DbTool::sql("
                        UPDATE `$table_name`
                           SET `title`='$text_name_id',
                               `shortdesc`='$text_short_id',
                               `description`='$text_long_id',
                               `product_id`='$text_code_id',
                               `external_link`='$text_uri_id',
                               `keywords`='$text_keys_id'
                         WHERE `id`=$id");
                    if (!$objResult2) {
die("Product::errorHandler(): Error: failed to update Product ID $id, code t5kjfas");
                    }
                    $objResult->MoveNext();
                }
            }
        }

        if (!DbTool::table($table_name, $table_structure, $table_index)) {
die("Product::errorHandler(): Error: failed to migrate Product table, code agkjgb7ls");
        }

        // Always
        return false;
    }

}

?>
