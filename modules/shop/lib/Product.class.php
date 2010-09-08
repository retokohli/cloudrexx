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
    const TEXT_NAME      = 'shop_product_name';
    const TEXT_SHORTDESC = 'shop_product_shortdesc';
    const TEXT_LONGDESC  = 'shop_product_longdesc';
    const TEXT_KEYWORDS  = 'shop_product_keywords';

    /**
     * @var     string          $code               Product code
     * @access  private
     */
    private $code = '';
    /**
     * @var     integer         $categoryId         ShopCategory of the Product
     * @access  private
     */
    private $categoryId = 0;
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
     * @var     integer         $order              Sorting order of the Product
     * @access  private
     */
    private $order = 1;
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
     * @var     double          $resellerPrice      Product price for resellers
     * @access  private
     */
    private $resellerPrice = 0;
    /**
     * @var     string            $shortdesc        Product short description
     * @access  private
     */
    private $shortdesc = '';
    /**
     * @var     integer         $text_shortdesc_id  The Text ID of the
     *                                              short description
     * @access  private
     */
    private $text_shortdesc_id = 0;
    /**
     * @var     string          $description        Product description
     * @access  private
     */
    private $description = '';
    /**
     * @var     integer         $text_longdesc_id   The Text ID of the
     *                                              long description
     * @access  private
     */
    private $text_longdesc_id = 0;
    /**
     * @var     integer         $stock              Product stock
     * @access  private
     */
    private $stock = 10;
    /**
     * @var     boolean         $isStockVisible     Product stock visibility
     * @access  private
     */
    private $isStockVisible = false;
    /**
     * @var     double          $discountPrice      Product discount price
     * @access  private
     */
    private $discountPrice = 0.00;
    /**
     * @var     boolean         $isSpecialOffer     Product is special offer
     * @access  private
     */
    private $isSpecialOffer = false;
    /**
     * @var     boolean         $isB2B              Product available for isB2B
     * @access  private
     */
    private $isB2B = true;
    /**
     * @var     boolean         $isB2C              Product available for b2c
     * @access  private
     */
    private $isB2C = true;
    /**
     * For future use -- currently not used in the Shop!
     * @var     string          $startDate          Product startdate
     * @access  private
     */
    private $startDate = '0000-00-00';
    /**
     * For future use -- currently not used in the Shop!
     * @var     string          $endDate            Product enddate
     * @access  private
     */
    private $endDate = '0000-00-00';
    /**
     * @var     integer         $manufacturerId     Product manufacturer ID
     * @access  private
     */
    private $manufacturerId = 0;
    /**
     * @var     string          $externalLink       Product external link
     * @access  private
     */
    private $externalLink = '';
    /**
     * @var     integer         $vatId              Product VAT ID
     * @access  private
     */
    private $vatId = 0;
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
    private $usergroups = '';
    /**
     * The count type discount group ID
     * @var     integer
     */
    private $groupCountId = 0;
    /**
     * The article group ID
     * @var     integer
     */
    private $groupArticleId = 0;
    /**
     * The list of keywords
     * @var     string
     */
    private $keywords = '';
    /**
     * @var     integer         $text_keywords_id   The Text ID of the keywords
     * @access  private
     */
    private $text_keywords_id = 0;
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
     * @param   integer $categoryId     The ShopCategory ID of the Product
     * @param   string  $name           The Product name
     * @param   string  $distribution   The Distribution type
     * @param   double  $price          The Product price
     * @param   integer $active         The active status
     * @param   integer $order          The sorting order
     * @param   integer $weight         The Product weight
     * @param   integer $id             The optional Product ID to be updated
     * @return  Product                 The Product
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function __construct(
        $code, $categoryId, $name, $distribution, $price,
        $active, $order, $weight, $id=0
    ) {
        // Assign & check
        $this->code         = strip_tags($code);
        $this->categoryId   = intval($categoryId);
        $this->name         = strip_tags($name);
        $this->distribution = strip_tags($distribution);
        $this->price        = floatval($price);
        $this->order = intval($order);
        $this->weight       = intval($weight);
        $this->id           = intval($id);
        $this->setActive($active);

        if ($this->order <= 0) { $this->order = 0; }
        // Default values for everything else as stated above

        // Enable cloning of Products with Attributes
        if ($this->id > 0) {
            $this->arrRelations =
                Attributes::getRelationArray($this->id);
        }
    }


    /**
     * Get the ID
     * @return  integer                             Product ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getId()
    {
        return $this->id;
    }
    /**
     * Set the ID -- NOT ALLOWED
     * See {@link Product::makeClone()}
     */

    /**
     * Get the Product code
     * @return  string                              Product code
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getCode()
    {
        return $this->code;
    }
    /**
     * Set the Product code
     * @param   string          $code               Product code
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setCode($code)
    {
        $this->code = trim(strip_tags($code));
    }

    /**
     * Get the Product name
     * @return  string                              Product name
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getName()
    {
        return $this->name;
    }
    /**
     * Set the Product name.
     * @param   string          $name               Product name
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setName($name)
    {
        $this->name = trim(strip_tags($name));
    }

    /**
     * Get the ShopCategory ID
     * @return  integer                             ShopCategory ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getShopCategoryId()
    {
        return $this->categoryId;
    }
    /**
     * Set the ShopCategory ID
     * @param   integer         $shopCategoryId     ShopCategory ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setShopCategoryId($shopCategoryId)
    {
        $this->categoryId = intval($shopCategoryId);
    }

    /**
     * Get the Product price
     * @return  double                              Product price
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getPrice()
    {
        return $this->price;
    }
    /**
     * Set the Product price
     * @param   double          $price              Product price
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setPrice($price)
    {
        $this->price = floatval($price);
    }

    /**
     * Get the Product sorting order
     * @return  integer                             Sorting order
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getOrder()
    {
        return $this->order;
    }
    /**
     * Set the Product sorting order
     * @param   integer         $order              Sorting order
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setOrder($order)
    {
        $this->order = intval($order);
    }

    /**
     * Get the Distribution type
     * @return  string                              Distribution type
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getDistribution()
    {
        return $this->distribution;
    }
    /**
     * Set the Distribution type
     * @param   string          $distribution       Distribution type
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setDistribution($distribution)
    {
        // fix this to be real static for PHP5
        $objDistribution = new Distribution();
        $this->distribution =
            ($objDistribution->isDistributionType($distribution)
                ? $distribution : $objDistribution->getDefault()
            );
    }

    /**
     * Get the active status
     * @return  boolean                             Active status
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getActive()
    {
        return $this->active;
    }
    /**
     * Set the active status
     * @param   boolean         $active              Active status
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setActive($active)
    {
        $this->active = ($active ? true : false);
    }

    /**
     * Get the pictures
     * @return  string                              Encoded picture string
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getPictures()
    {
        return $this->pictures;
    }
    /**
     * Set the pictures
     * @param   string          $pictures           Encoded picture string
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setPictures($pictures)
    {
        $this->pictures = $pictures;
    }

    /**
     * Get the reseller price
     * @return  double                              Reseller price
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getResellerPrice()
    {
        return $this->resellerPrice;
    }
    /**
     * Set the reseller price
     * @param   double          $resellerPrice      Reseller price
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setResellerPrice($resellerPrice)
    {
        $this->resellerPrice = floatval($resellerPrice);
    }

    /**
     * Get the short description
     * @return  string                              Short description
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getshortdesc()
    {
        return $this->shortdesc;
    }
    /**
     * Set the short description
     * @param   string          $shortdesc          Short description
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setshortdesc($shortdesc)
    {
        $this->shortdesc = trim($shortdesc);
    }

    /**
     * Get the description
     * @return  string                              Long description
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getDescription()
    {
        return $this->description;
    }
    /**
     * Set the description
     * @param   string          $description        Long description
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setDescription($description)
    {
        $this->description = trim($description);
    }

    /**
     * Get the stock
     * @return  integer                             Stock
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getStock()
    {
        return $this->stock;
    }
    /**
     * Set the stock
     * @param   integer         $stock              Stock
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setStock($stock)
    {
        $this->stock = intval($stock);
    }

    /**
     * Get the stock visibility
     * @return  boolean                             Stock visibility
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function isStockVisible()
    {
        return $this->isStockVisible;
    }
    /**
     * Set the stock visibility
     * @param   boolean         $isStockVisible     Stock visibility
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setStockVisible($isStockVisible)
    {
        $this->isStockVisible = ($isStockVisible ? true : false);
    }

    /**
     * Get the discount price
     * @return  double                              Discount price
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getDiscountPrice()
    {
        return $this->discountPrice;
    }
    /**
     * Set the discount price
     * @param   double          $discountPrice      Discount price
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setDiscountPrice($discountPrice)
    {
        $this->discountPrice = floatval($discountPrice);
    }

    /**
     * Get the special offer flag
     * @return  boolean                             Is special offer
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function isSpecialOffer()
    {
        return $this->isSpecialOffer;
    }
    /**
     * Set the special offer flag
     * @param   boolean         $isSpecialOffer     Is special offer
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setSpecialOffer($isSpecialOffer)
    {
        $this->isSpecialOffer = ($isSpecialOffer ? true : false);
    }

    /**
     * Get the Product flags
     * @return  string              The Product flags
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getFlags()
    {
        return $this->flags;
    }
    /**
     * Add a flag
     *
     * Note that the match is case sensitive.
     * @param   string              The flag to be added
     * @return  boolean             Boolean true if the flags were accepted
     *                              or already present, false otherwise
     *                              (always true for the time being).
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
     * Note that the match is case insensitive.
     * @param   string              The flag to be removed
     * @return  boolean             Boolean true if the flags could be removed
     *                              or wasn't present, false otherwise
     *                              (always true for the time being).
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function removeFlag($flag)
    {
        $this->flags = trim(preg_replace("/\\s*$flag\\s*/i", ' ', $this->flags));
        return true;
    }
    /**
     * Set the Product flags
     * @param   string              The Product flags
     * @return  boolean             Boolean true if the flags were accepted,
     *                              false otherwise
     *                              (Always true for the time being).
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setFlags($flags)
    {
        $this->flags = $flags;
    }

    /**
     * Test for a match with the Product flags.
     *
     * Note that the match is case sensitive.
     * @param   string              The Product flag to test
     * @return  boolean             Boolean true if the flag is set,
     *                              false otherwise.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function testFlag($flag)
    {
        return preg_match("/$flag/", $this->flags);
    }

    /**
     * Get the B2B flag
     * @return  boolean                             Is B2B
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function isB2B()
    {
        return $this->isB2B;
    }
    /**
     * Set the B2B flag
     * @param   boolean         $isB2B              Is B2B
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setB2B($isB2B)
    {
        $this->isB2B = ($isB2B ? true : false);
    }

    /**
     * Get the B2C flag
     * @return  boolean                             Is B2C
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function isB2C()
    {
        return $this->isB2C;
    }
    /**
     * Set the B2C flag
     * @param   boolean         $isB2C              Is B2C
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setB2C($isB2C)
    {
        $this->isB2C = ($isB2C ? true : false);
    }

    /**
     * Get the start date
     * @return  string                              Start date
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getStartDate()
    {
        return $this->startDate;
    }
    /**
     * Set the start date
     * @param   string          $startDate          Start date
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * Get the end date
     * @return  string                              End date
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getEndDate()
    {
        return $this->endDate;
    }
    /**
     * Set the end date
     * @param   string          $endDate            End date
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * Get the Manufacturer ID
     * @return  integer                             Manufacturer ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getManufacturerId()
    {
        return $this->manufacturerId;
    }
    /**
     * Set the Manufacturer ID
     * @param   integer         $manufacturer       Manufacturer ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setManufacturerId($manufacturerId)
    {
        $this->manufacturerId = $manufacturerId;
    }

    /**
     * Get the external link
     * @return  string                              External link
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getExternalLink()
    {
        return $this->externalLink;
    }
    /**
     * Set the external link
     * @param   string          $externalLink       External link
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setExternalLink($externalLink)
    {
        $this->externalLink = $externalLink;
    }

    /**
     * Get the VAT Id
     * @return  string                              VAT Id
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getVatId()
    {
        return $this->vatId;
    }
    /**
     * Set the VAT Id
     * @param   string          $vatId              VAT Id
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setVatId($vatId)
    {
        $this->vatId = intval($vatId);
    }

    /**
     * Get the weight
     * @return  string                              Weight
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getWeight()
    {
        return $this->weight;
    }
    /**
     * Set the weight
     * @param   string          $weight             Weight
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setWeight($weight)
    {
        $this->weight = intval($weight);
    }

    /**
     * Get the assigned user groups
     * @return  string                               Comma separated list of
     *                                               assigned user groups
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getUsergroups()
    {
        return $this->usergroups;
    }
    /**
     * Set the assigned user groups
     * @param   string          $usergroups         Comma separated list of
     *                                              assigned user groups
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setUsergroups($usergroups)
    {
        $this->usergroups = $usergroups;
    }

    /**
     * Get the keywords
     * @return  string                               The product keywords
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getKeywords()
    {
        return $this->keywords;
    }
    /**
     * Set the keywords
     * @param   string          $keywords         The product keywords
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }


    /**
     * Get the visibility of the Product on the start page
     * @return  boolean                             Visibility
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function isShownOnStartpage()
    {
        return $this->testFlag('__SHOWONSTARTPAGE__');
    }
    /**
     * Set the visibility of the Product on the start page
     * @param   boolean         $shownOnStartpage   Visibility
     * @return  boolean         True if the flag could be set or cleared
     *                          successfully, false otherwise.
     *
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setShownOnStartpage($shownOnStartpage)
    {
        if ($shownOnStartpage) {
            return $this->addFlag('__SHOWONSTARTPAGE__');
        }
        return $this->removeFlag('__SHOWONSTARTPAGE__');
    }


    /**
     * Return the correct Product price for any Customer and Product.
     *
     * Note that if this method is called without a valid Customer object,
     * no reseller price will be returned.
     * @param   Customer    $objCustomer    The optional Customer object.
     * @return  double                      The Product price
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getCustomerPrice($objCustomer=false)
    {
        if (is_a($objCustomer, 'Customer') && $objCustomer->isReseller()) {
            return $this->resellerPrice;
        }
        return $this->price;
    }


    /**
     * Return the current discounted price for any Product, if applicable.
     * @return  mixed                       The Product discount price,
     *                                      or false if there is no discount.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getDiscountedPrice()
    {
        if ($this->hasDiscount()) {
            $price = $this->price;
            if ($this->isSpecialOffer) {
                $price = $this->discountPrice;
            }
            if ($this->testFlag('Outlet')) {
                $discountRate = $this->getOutletDiscountRate();
                $price = number_format(
                    $price * (100 - $discountRate) / 100,
                    2, '.', '');
            }
            return $price;
        }
        return false;
    }


    /**
     * Returns boolean true if this Product has any kind of discount.
     *
     * This may either be the regular discount price if isSpecialOffer
     * is true, or the "Outlet" discount, or both.
     * Use {@link getDiscountPrice()} to get the correct discount price.
     * @return  boolean                 True if there is a discount,
     *                                  false otherwise.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function hasDiscount()
    {
        return $this->isSpecialOffer || $this->testFlag('Outlet');
    }


    /**
     * Returns boolean true if this Product is in the "Outlet" Category.
     * @return  boolean                 True if this is in the "Outlet",
     *                                  false otherwise.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function isOutlet()
    {
        return $this->testFlag('Outlet');
    }


    /**
     * Return the discount rate for any Product in the virtual "Outlet"
     * ShopCategory.
     *
     * The rules for the discount are: 21% at the first date of the month,
     * plus an additional 1% per day, for a maximum rate of 51% on the 31st.
     * @return  integer                 The current Outlet discount rate
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getOutletDiscountRate()
    {
        $dayOfMonth = date('j');
        return 20 + $dayOfMonth;
    }


    /**
     * Get the count type discount group ID
     * @return  string         The group ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getGroupCountId()
    {
        return $this->groupCountId;
    }
    /**
     * Set the count type discount group ID
     * @param   string         $groupCountId       The group ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setGroupCountId($groupCountId)
    {
        $this->groupCountId = intval($groupCountId);
    }

    /**
     * Get the article group ID
     * @return  string         The group ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getGroupArticleId()
    {
        return $this->groupArticleId;
    }
    /**
     * Set the article group ID
     * @param   string         $groupArticleId       The group ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setGroupArticleId($groupArticleId)
    {
        $this->groupArticleId = intval($groupArticleId);
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
     * Delete the Product specified by its ID from the database.
     *
     * Associated Attributes and pictures are deleted with it.
     * @param   integer           $productId    The Product ID
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
                    // Delete the picture and thumbnail:
                    // Split file name and extension -- in case someone
                    // finally decides that inserting '.thumb' between the
                    // file name and extension is better than the current way
                    // of doing it...
                    $fileArr = array();
                    preg_match('/(.+)(\.\w+)$/', $strFileName, $fileArr);
                    $pictureName = $fileArr[1].$fileArr[2];
                    $thumbName = $pictureName.ShopLibrary::thumbnailSuffix;
                    // Continue even if deleting the images fails
                    File::delete_file(ASCMS_PATH_OFFSET.'/'.$thumbName);
                    File::delete_file(ASCMS_PATH_OFFSET.'/'.$pictureName);
                }
            }
        }
        // Remove any Text records present
        if (!Text::deleteById($this->text_name_id))      return false;
        if (!Text::deleteById($this->text_shortdesc_id)) return false;
        if (!Text::deleteById($this->text_longdesc_id))  return false;
        if (!Text::deleteById($this->text_keywords_id))  return false;
        // Delete the Product attribute relations and the Product itself
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes
             WHERE product_id=$this->id");
        if (!$objResult) return false;
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
            $this->text_name_id, FRONTEND_LANG_ID, $this->name);
        if (empty($this->text_name_id)) return false;
        $this->text_shortdesc_id = Text::replace(
            $this->text_shortdesc_id, FRONTEND_LANG_ID, $this->shortdesc);
        if (empty($this->text_shortdesc_id)) return false;
        $this->text_longdesc_id = Text::replace(
            $this->text_longdesc_id, FRONTEND_LANG_ID, $this->description);
        if (empty($this->text_longdesc_id)) return false;
        $this->text_keywords_id = Text::replace(
            $this->text_keywords_id, FRONTEND_LANG_ID, $this->keywords);
        if (empty($this->text_keywords_id)) return false;
        if ($this->recordExists()) {
            if (!$this->update()) return false;
            if (!Attributes::removeFromProduct($this->id))
                return false;
        } else {
            if (!$this->insert()) return false;
        }
        // Store Attributes, if any
        if (is_array($this->arrRelations)) {
            foreach ($this->arrRelations as $value_id => $order) {
                if (!Attributes::addOptionToProduct(
                    $value_id, $this->id, $order
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
            SET product_id='".addslashes($this->code)."',
                picture='$this->pictures',
                text_name_id=$this->text_name_id,
                catid=$this->categoryId,
                handler='$this->distribution',
                normalprice=$this->price,
                resellerprice=$this->resellerPrice,
                text_shortdesc_id=$this->text_shortdesc_id,
                text_longdesc_id=$this->text_longdesc_id,
                stock=$this->stock,
                stock_visibility=".($this->isStockVisible ? 1 : 0).",
                discountprice=$this->discountPrice,
                is_special_offer=".($this->isSpecialOffer ? 1 : 0).",
                active=".($this->active ? 1 : 0).",
                b2b=".($this->isB2B ? 1 : 0).",
                b2c=".($this->isB2C ? 1 : 0).",
                startdate='$this->startDate',
                enddate='$this->endDate',
                manufacturer=$this->manufacturerId,
                external_link='".addslashes($this->externalLink)."',
                sort_order=$this->order,
                vat_id=$this->vatId,
                weight=$this->weight,
                flags='".addslashes($this->flags)."',
                usergroups='$this->usergroups',
                group_id=$this->groupCountId,
                article_id=$this->groupArticleId,
                text_keywords_id=$this->text_keywords_id
          WHERE id=$this->id
        ";
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
                product_id, picture, text_name_id, catid, handler,
                normalprice, resellerprice,
                text_shortdesc_id, text_longdesc_id,
                stock, stock_visibility, discountprice, is_special_offer,
                active,
                b2b, b2c, startdate, enddate,
                manufacturer, external_link,
                sort_order, vat_id, weight,
                flags, usergroups,
                group_id, article_id, text_keywords_id
            ) VALUES ('".
                addslashes($this->code)."', '$this->pictures',
                $this->text_name_id,
                $this->categoryId,
                '$this->distribution',
                $this->price, $this->resellerPrice,
                $this->text_shortdesc_id,
                $this->text_longdesc_id,
                $this->stock, ".
                ($this->isStockVisible ? 1 : 0).",
                $this->discountPrice, ".
                ($this->isSpecialOffer ? 1 : 0).", ".
                ($this->active ? 1 : 0).", ".
                ($this->isB2B ? 1 : 0).", ".
                ($this->isB2C ? 1 : 0).",
                '$this->startDate', '$this->endDate',
                $this->manufacturerId, '".
                addslashes($this->externalLink)."',
                $this->order, $this->vatId, $this->weight,
                '".addslashes($this->flags)."',
                '$this->usergroups',
                $this->groupCountId, $this->groupArticleId,
                $this->text_keywords_id
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
            '`product`.`text_shortdesc_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_SHORTDESC
        );
        $arrSqlLong = Text::getSqlSnippets(
            '`product`.`text_longdesc_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_LONGDESC
        );
        $arrSqlKeyword = Text::getSqlSnippets(
            '`product`.`text_keywords_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_KEYWORDS
        );
        $query = "
            SELECT `id`, `product_id`, `catid`,
                   `sort_order`, `active`, `weight`, `picture`,
                   `normalprice`, `resellerprice`, `discountprice`, `is_special_offer`,
                   `stock`, `stock_visibility`,
                   `handler`, `startdate`, `enddate`, `manufacturer`,
                   `b2b`, `b2c`, `vat_id`, `external_link`,
                   `flags`, `usergroups`, `group_id`, `article_id`".
                   $arrSqlName['field'].$arrSqlShort['field'].
                   $arrSqlLong['field'].$arrSqlKeyword['field']."
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_products` AS `product`".
                   $arrSqlName['join'].$arrSqlShort['join'].
                   $arrSqlLong['join'].$arrSqlKeyword['join']."
             WHERE id=$id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        if ($objResult->RecordCount() != 1) return false;
        $objProduct = new Product(
            $objResult->fields['product_id'],
            $objResult->fields['catid'],
            $objResult->fields[$arrSqlName['text']],
            $objResult->fields['handler'],
            $objResult->fields['normalprice'],
            $objResult->fields['active'],
            $objResult->fields['ord'],
            $objResult->fields['weight'],
            $objResult->fields['id']
        );
        $objProduct->text_name_id      = $objResult->fields[$arrSqlName['name']];
        $objProduct->pictures          = $objResult->fields['picture'];
        $objProduct->resellerPrice     = floatval($objResult->fields['resellerprice']);
        $objProduct->shortdesc         = $objResult->fields[$arrSqlShort['text']];
        $objProduct->text_shortdesc_id = $objResult->fields[$arrSqlShort['name']];
        $objProduct->description       = $objResult->fields[$arrSqlLong['text']];
        $objProduct->text_longdesc_id  = $objResult->fields[$arrSqlLong['name']];
        $objProduct->stock             = intval($objResult->fields['stock']);
        $objProduct->setStockVisible($objResult->fields['stock_visibility']);
        $objProduct->discountPrice     = floatval($objResult->fields['discountprice']);
        $objProduct->setSpecialOffer($objResult->fields['is_special_offer']);
        $objProduct->setB2B($objResult->fields['b2b']);
        $objProduct->setB2C($objResult->fields['b2c']);
        $objProduct->startDate         = $objResult->fields['startdate'];
        $objProduct->endDate           = $objResult->fields['enddate'];
        $objProduct->manufacturerId    = intval($objResult->fields['manufacturer']);
        $objProduct->externalLink      = $objResult->fields['external_link'];
        $objProduct->vatId             = intval($objResult->fields['vat_id']);
        $objProduct->flags             = $objResult->fields['flags'];
        $objProduct->usergroups        = $objResult->fields['usergroups'];
        $objProduct->groupCountId      = intval($objResult->fields['group_id']);
        $objProduct->groupArticleId    = intval($objResult->fields['article_id']);
        $objProduct->keywords          = $objResult->fields[$arrSqlKeyword['text']];
        $objProduct->text_keywords_id  = $objResult->fields[$arrSqlKeyword['name']];
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
     * @param   integer     $order      The sorting order value
     * @return  boolean                 True. Always.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function addAttribute($value_id, $order)
    {
        $this->arrRelations[$value_id] = $order;
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
     * set as their "handler" field value.
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
               AND handler='delivery'";
        return (boolean)$objDatabase->Execute($query);
    }

}

?>
