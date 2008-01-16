<?php
/**
 * Shop Products
 *
 * @version     $Id: 1.0.1$
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Test!
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
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
 * Product Attribute - This is still alpha!
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/ProductAttribute.class.php';
/**
 * Product Attributes - Helper and display methods - This is still alpha!
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/ProductAttributes.class.php';


/**
 * Product as available in the Shop.
 *
 * Includes access methods and data layer.
 * Do not, I repeat, do not access private fields, or even try
 * to access the database directly!
 * @version     $Id: 1.0.1 $
 * @package     contrexx
 * @subpackage  module_shop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 */
class Product
{
    /**
     * @var     string          $code               Product code
     * @access  private
     */
    var $code;
    /**
     * @var     integer         $catId              ShopCategory of the Product
     * @access  private
     */
    var $catId;
    /**
     * @var     string          $name               Product name
     * @access  private
     */
    var $name;
    /**
     * @var     Distribution    $distribution       Distribution type
     * @access  private
     */
    var $distribution;
    /**
     * @var     double          $price              Product price
     * @access  private
     */
    var $price;
    /**
     * @var     integer         $order              Sorting order of the Product
     * @access  private
     */
    var $order;
    /**
     * @var     integer         $weight             Product weight (in grams)
     * @access  private
     */
    var $weight;
    /**
     * @var     integer         $id                 The Product ID
     * @access  private
     */
    var $id;
    /**
     * The status is either active (true), or inactive (false).
     * @var     boolean         $status             Product status
     * @access  private
     */
    var $status;
    /**
     * @var     string          $pictures           Product pictures
     * @access  private
     */
    var $pictures;
    /**
     * @var     double          $resellerPrice      Product price for resellers
     * @access  private
     */
    var $resellerPrice;
    /**
     * @var     string          $shortDesc          Product short description
     * @access  private
     */
    var $shortDesc;
        /**
     * @var     string          $description        Product description
     * @access  private
     */
    var $description;
    /**
     * @var     integer         $stock              Product stock
     * @access  private
     */
    var $stock;
    /**
     * @var     boolean         $isStockVisible     Product stock visibility
     * @access  private
     */
    var $isStockVisible;
    /**
     * @var     double          $discountPrice      Product discount price
     * @access  private
     */
    var $discountPrice;
    /**
     * @var     boolean         $isSpecialOffer     Product is special offer
     * @access  private
     */
    var $isSpecialOffer;
    /**
     * @var     string          $property1          Product property 1
     * @access  private
     */
    var $property1;
    /**
     * @var     string          $property2          Product property 2
     * @access  private
     */
    var $property2;
    /**
     * @var     boolean         $isB2B              Product available for isB2B
     * @access  private
     */
    var $isB2B;
    /**
     * @var     boolean         $isB2C              Product available for b2c
     * @access  private
     */
    var $isB2C;
    /**
     * @var     string          $startDate          Product startdate
     * @access  private
     */
    var $startDate;
    /**
     * @var     string          $endDate            Product enddate
     * @access  private
     */
    var $endDate;
    /**
     * @var     integer         $thumbnailPercent   Product thumbnail percent
     * @access  private
     */
    var $thumbnailPercent;
    /**
     * @var     integer         $thumbnailQuality   Product thumbnail quality
     * @access  private
     */
    var $thumbnailQuality;
    /**
     * @var     integer         $manufacturerId     Product manufacturer ID
     * @access  private
     */
    var $manufacturerId;
    /**
     * @var     string          $externalLink       Product external link
     * @access  private
     */
    var $externalLink;
    /**
     * @var     integer         $vatId              Product VAT ID
     * @access  private
     */
    var $vatId;
    /**
     * The Product flags
     * @var string
     */
    var $flags;
    /**
     * ProductAttribute value IDs array
     *
     * See {@link getProductAttributeValueIdArray()} for details.
     * @var     array   $arrProductAttributeValue
     * @access  private
     */
    var $arrProductAttributeValue;


    /**
     * Add or replace a Product (PHP4)
     *
     * If the optional argument $id is set, the corresponding
     * Product is updated.  Otherwise, a new Product is created.
     * Set the remaining object variables by calling the appropriate
     * access methods.
     * @access  public
     * @param   string  $code           The Product code
     * @param   integer $catId          The ShopCategory ID of the Product
     * @param   string  $name           The Product name
     * @param   string  $distribution   The Distribution type
     * @param   double  $price          The Product price
     * @param   integer $status         The status of the Product (0 or 1)
     * @param   integer $order          The sorting order
     * @param   integer $weight         The Product weight
     * @param   integer $id             The optional Product ID to be updated
     * @return  Product                 The Product
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function Product(
        $code, $catId, $name, $distribution, $price,
        $status, $order, $weight, $id=0
    ) {
        $this->__construct(
            $code, $catId, $name, $distribution, $price,
            $status, $order, $weight, $id
        );
    }


    /**
     * Add or replace a Product (PHP5)
     *
     * If the optional argument $id is set, the corresponding
     * Product is updated.  Otherwise, a new Product is created.
     * Set the remaining object variables by calling the appropriate
     * access methods.
     * @access  public
     * @param   string  $code           The Product code
     * @param   integer $catId          The ShopCategory ID of the Product
     * @param   string  $name           The Product name
     * @param   string  $distribution   The Distribution type
     * @param   double  $price          The Product price
     * @param   integer $status         The status of the Product (0 or 1)
     * @param   integer $order          The sorting order
     * @param   integer $weight         The Product weight
     * @param   integer $id             The optional Product ID to be updated
     * @return  Product                 The Product
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function __construct(
        $code, $catId, $name, $distribution, $price,
        $status, $order, $weight, $id=0
    ) {
        // Assign & check
        $this->code         = strip_tags($code);
        $this->catId        = intval($catId);
        $this->name         = strip_tags($name);
        $this->distribution = strip_tags($distribution);
        $this->price        = floatval($price);
        $this->order        = intval($order);
        $this->weight       = intval($weight);
        $this->id           = intval($id);
        $this->setStatus($status);
        if ($this->order <= 0) { $this->order = 0; }

        // Default values for everything else
        $this->pictures         = '';
        $this->resellerPrice    =  0;
        $this->shortDesc        = '';
        $this->description      = '';
        $this->stock            =  1;
        $this->isStockVisible   =  false;
        $this->discountPrice    =  0;
        $this->isSpecialOffer   =  false;
        $this->property1        = '';
        $this->property2        = '';
        $this->isB2B            =  true;
        $this->isB2C            =  true;
        $this->startDate        = '';
        $this->endDate          = '';
        $this->thumbnailPercent = 25;
        $this->thumbnailQuality = 95;
        $this->manufacturerId   =  0;
        $this->externalLink     = '';
        $this->vatId            =  0;
        $this->flags            = '';

        // Enable cloning of Products with ProductAttributes
        if ($this->id > 0) {
            $this->arrProductAttributeValue =
                ProductAttribute::getValueIdArray($this->id);
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
        $this->code = strip_tags($code);
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
     * Set the Product name
     * @param   string          $name               Product name
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get the ShopCategory ID
     * @return  integer                             ShopCategory ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getShopCategoryId()
    {
        return $this->catId;
    }
    /**
     * Set the ShopCategory ID
     * @param   integer         $shopCategoryId     ShopCategory ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setShopCategoryId($shopCategoryId)
    {
        $this->catId = intval($shopCategoryId);
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
                ?   $distribution
                :   $objDistribution->getDefault()
            );
    }

    /**
     * Get the status
     * @return  boolean                             Status
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getStatus()
    {
        return $this->status;
    }
    /**
     * Set the status
     * @param   boolean         $status              Status
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setStatus($status)
    {
        $this->status = ($status == 0 ? 0 : 1);
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
    function getShortDesc()
    {
        return $this->shortDesc;
    }
    /**
     * Set the short description
     * @param   string          $shortDesc          Short description
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setShortDesc($shortDesc)
    {
        $this->shortDesc = $shortDesc;
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
        $this->description = $description;
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
     * Get the ShopCategory flags
     *
     * Note that this is an alias for {@link getProperty1()}.
     * @return  string              The ShopCategory flags
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getFlags()
    {
        return $this->flags;
    }
    /**
     * Add a flag
     *
     * Note that the match is case insensitive.
     * @param   string              The flag to be added
     * @return  boolean             Boolean true if the flags were accepted
     *                              or already present, false otherwise
     *                              (always true for the time being).
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function addFlag($flag)
    {
        if (!preg_match("/$flag/i", $this->flags)) {
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
     * Set the ShopCategory flags
     *
     * Note that this is an alias for {@link setProperty1()}.
     * @param   string              The ShopCategory flags
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
     * Test for a match with the ShopCategory flags.
     *
     * Note that the match is case insensitive.
     * @param   string              The ShopCategory flag to test
     * @return  boolean             Boolean true if the flag is set,
     *                              false otherwise.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function testFlag($flag)
    {
        return preg_match("/$flag/i", $this->flags);
    }

    /**
     * Get the property 1
     * @return  string                              Product property 1
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getProperty1()
    {
        return $this->property1;
    }
    /**
     * Set the property 1
     * @param   string          $property1          Product property 1
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setProperty1($property1)
    {
        $this->property1 = $property1;
    }

    /**
     * Get the property 2
     * @return  string                              Product property 2
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getProperty2()
    {
        return $this->property2;
    }
    /**
     * Set the property 2
     * @param   string          $property2          Product property 2
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setProperty2($property2)
    {
        $this->property2 = $property2;
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
     * Get the thumbnail size percentage
     * @return  integer                             Thumbnail size percentage
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getThumbnailPercent()
    {
        return $this->thumbnailPercent;
    }
    /**
     * Set the thumbnail size percentage
     * @param   integer         $thumbnailPercent   Thumbnail size percentage
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setThumbnailPercent($thumbnailPercent)
    {
        $this->thumbnailPercent = intval($thumbnailPercent);
    }

    /**
     * Get the thumbnail quality percentage
     * @return  integer                             Thumbnail quality percentage
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getThumbnailQuality()
    {
        return $this->thumbnailQuality;
    }
    /**
     * Set the thumbnail quality percentage
     * @param   integer         $thumbnailQuality   Thumbnail quality percentage
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setThumbnailQuality($thumbnailQuality)
    {
        $this->thumbnailQuality = intval($thumbnailQuality);
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
     * Return the current Product discounted price for any Product.
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
     * Clone the Product
     *
     * Note that this does NOT create a copy in any way, but simply clears
     * the Product ID.  Upon storing this Product, a new ID is created.
     * Also note that all ProductAttributes *MUST* be link()ed after every
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
     * @param       integer     $productId      The Product ID
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @todo        The handling of pictures is buggy.  Pictures used by other
     *              Products are only recognised if all file names are identical
     *              and in the same order!
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function delete($flagDeleteImages=false)
    {
        global $objDatabase;

        if (!$this->id) {
            return false;
        }

        if ($flagDeleteImages) {
            // heck, most of this should go into the ProductPicture class...
            // split picture data into single pictures
            $arrPictures = split(':', $this->pictures);
            foreach ($arrPictures as $strPicture) {
                if ($strPicture != '') {
                    // split picture into name, width, height
                    $arrPicture = explode('?', $strPicture);

                    // verify that no other Product uses the same picture
                    $query = "SELECT picture FROM ".DBPREFIX."module_shop_products WHERE picture LIKE '%".$arrPicture[0]."%'";
                    $objResult = $objDatabase->Execute($query);
                    if ($objResult->RecordCount() == 1) {
                        // $arrPicture[0] contains the file name
                        $strFileName = base64_decode($arrPicture[0]);
                        // check whether it is the default image
                        if (preg_match('/'.$this->defaultThumbnail.'$/', $strFileName)) {
                            continue;
                        }
                        // delete the picture and thumbnail:
                        // split file name and extension -- in case someone
                        // finally decides that inserting '.thumb' between the
                        // file name and extension is better than the current way
                        // of doing it...
                        $fileArr = array();
                        preg_match('/(.+)(\.\w+)$/', $strFileName, $fileArr);
                        $pictureName = $fileArr[1].$fileArr[2];
                        $thumbName = $pictureName.'.thumb';
                        // Continue even if deleting the images fails
                        @unlink(ASCMS_PATH.$thumbName);
                        @unlink(ASCMS_PATH.$pictureName);
                    }
                }
            }
        }

        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_shop_products_attributes
             WHERE product_id=$this->id
        ");
        if (!$objResult) {
            return false;
        }
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_shop_products
             WHERE id=$this->id
        ");
        if (!$objResult) {
            return false;
        }
        return true;
    }


    /**
     * Test whether a record with the ID of this object is already present
     * in the database.
     * @return  boolean                     True if it exists, false otherwise
     * @global  mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function recordExists()
    {
        global $objDatabase;

        $query = "
            SELECT 1
              FROM ".DBPREFIX."module_shop_products
             WHERE id=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        if ($objResult->RecordCount() == 1) {
            return true;
        }
        return false;
    }


    /**
     * Stores the Product object in the database.
     *
     * Either updates or inserts the object, depending on the outcome
     * of the call to {@link recordExists()}.
     * @return      boolean     True on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function store()
    {
        if ($this->recordExists()) {
            if (!$this->update()) {
                return false;
            }
            if (!ProductAttributes::deleteByProductId($this->id)) {
                return false;
            }
        } else {
            if (!$this->insert()) {
                return false;
            }
        }
        // Store ProductAttributes, if any
        if (is_array($this->arrProductAttributeValue)) {
            foreach ($this->arrProductAttributeValue as $arrValue) {
                if (!ProductAttributes::addValueToProduct(
                    $arrValue['valueId'],
                    $this->id,
                    $arrValue['order']
                )) {
                    return false;
                }
            }
        }
        return true;
    }


    /**
     * Update this Product in the database.
     * Returns the result of the query.
     *
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function update()
    {
        global $objDatabase;

        $query = "
            UPDATE ".DBPREFIX."module_shop_products
            SET product_id='".addslashes($this->code)."',
                picture='$this->pictures',
                title='".addslashes($this->name)."',
                catid=$this->catId,
                handler='$this->distribution',
                normalprice=$this->price,
                resellerprice=$this->resellerPrice,
                shortdesc='".addslashes($this->shortDesc)."',
                description='".addslashes($this->description)."',
                stock=$this->stock,
                stock_visibility=".($this->isStockVisible ? 1 : 0).",
                discountprice=$this->discountPrice,
                is_special_offer=".($this->isSpecialOffer ? 1 : 0).",
                property1='".addslashes($this->property1)."',
                property2='".addslashes($this->property2)."',
                status=".($this->status ? 1 : 0).",
                b2b=".($this->isB2B ? 1 : 0).",
                b2c=".($this->isB2C ? 1 : 0).",
                startdate='$this->startDate',
                enddate='$this->endDate',
                thumbnail_percent=$this->thumbnailPercent,
                thumbnail_quality=$this->thumbnailQuality,
                manufacturer=$this->manufacturerId,
                external_link='".addslashes($this->externalLink)."',
                sort_order=$this->order,
                vat_id=$this->vatId,
                weight=$this->weight,
                flags='".addslashes($this->flags)."'
          WHERE id=$this->id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        return true;
    }


    /**
     * Insert this Product into the database.
     *
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function insert()
    {
        global $objDatabase;

        $query = "
            INSERT INTO ".DBPREFIX."module_shop_products (
                product_id, picture, title, catid, handler,
                normalprice, resellerprice, shortdesc, description,
                stock, stock_visibility, discountprice, is_special_offer,
                property1, property2,
                status,
                b2b, b2c, startdate, enddate,
                manufacturer, external_link,
                sort_order, vat_id, weight,
                flags
            ) VALUES ('".
                addslashes($this->code)."', '$this->pictures', '".
                addslashes($this->name)."', $this->catId,
                '$this->distribution',
                $this->price, $this->resellerPrice, '".
                addslashes($this->shortDesc)."', '".
                addslashes($this->description)."',
                $this->stock, ".
                ($this->isStockVisible ? 1 : 0).",
                $this->discountPrice, ".
                ($this->isSpecialOffer ? 1 : 0).", '".
                addslashes($this->property1)."', '".
                addslashes($this->property2)."', ".
                ($this->status ? 1 : 0).", ".
                ($this->isB2B ? 1 : 0).", ".
                ($this->isB2C ? 1 : 0).",
                '$this->startDate', '$this->endDate',
                $this->manufacturerId, '".
                addslashes($this->externalLink)."',
                $this->order, $this->vatId, $this->weight,
                '".addslashes($this->flags)."'
            )";
// No longer in use:
//                thumbnail_percent, thumbnail_quality,
//                $this->thumbnailPercent, $this->thumbnailQuality,
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        // my brand new ID
        $this->id = $objDatabase->Insert_ID();

        return true;
    }


    /**
     * Select a Product by ID from the database.
     *
     * @static
     * @param       integer     $id             The Product ID
     * @return      Product                     The Product object on success,
     *                                          false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getById($id)
    {
        global $objDatabase;

        $query = "SELECT * FROM ".DBPREFIX."module_shop_products WHERE id=$id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        if ($objResult->RecordCount() != 1) {
            return false;
        }
        // constructor also read ProductAttributes if ID > 0
        $objProduct = new Product(
            $objResult->fields['product_id'],
            $objResult->fields['catid'],
            stripslashes($objResult->fields['title']),
            $objResult->fields['handler'],
            $objResult->fields['normalprice'],
            $objResult->fields['status'],
            $objResult->fields['sort_order'],
            $objResult->fields['weight'],
            $objResult->fields['id']
        );
        $objProduct->pictures         = $objResult->fields['picture'];
        $objProduct->resellerPrice    = $objResult->fields['resellerprice'];
        $objProduct->shortDesc        = stripslashes($objResult->fields['shortdesc']);
        $objProduct->description      = stripslashes($objResult->fields['description']);
        $objProduct->stock            = $objResult->fields['stock'];
        $objProduct->setStockVisible($objResult->fields['stock_visibility']);
        $objProduct->discountPrice    = $objResult->fields['discountprice'];
        $objProduct->setSpecialOffer($objResult->fields['is_special_offer']);
        $objProduct->property1        = stripslashes($objResult->fields['property1']);
        $objProduct->property2        = stripslashes($objResult->fields['property2']);
        $objProduct->setB2B($objResult->fields['b2b']);
        $objProduct->setB2C($objResult->fields['b2c']);
        $objProduct->startDate        = $objResult->fields['startdate'];
        $objProduct->endDate          = $objResult->fields['enddate'];
        $objProduct->thumbnailPercent = $objResult->fields['thumbnail_percent'];
        $objProduct->thumbnailQuality = $objResult->fields['thumbnail_quality'];
        $objProduct->manufacturerId   = $objResult->fields['manufacturer'];
        $objProduct->externalLink     = stripslashes($objResult->fields['external_link']);
        $objProduct->vatId            = $objResult->fields['vat_id'];
        $objProduct->flags            = stripslashes($objResult->fields['flags']);
        // also fetch the ProductAttribute value IDs
        $objProduct->arrProductAttributeValue =
            ProductAttributes::getProductValueArray($objProduct->id);
        return $objProduct;
    }


    /**
     * Add the given Product Attribute value ID to this object.
     *
     * Note that the relation is is only permanently created after
     * the object is store()d.
     * @param   integer     $valueId    The Product Attribute value ID
     * @param   integer     $order      The sorting order value
     * @return  boolean                 True. Always.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function addAttribute($valueId, $order)
    {
        $this->arrProductAttributeValue[] = array(
            'valueId' => $valueId,
            'order'   => $order,
        );
        return true;
    }


    /**
     * Remove the given Product Attribute value ID from this object.
     *
     * Note that the relation is is only permanently destroyed after
     * the object is store()d.
     * Also note that this method always returns true. It cannot fail. :)
     * @param   integer     $valueId    The Product Attribute value ID
     * @return  boolean                 True. Always.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function deleteAttribute($valueId)
    {
        foreach ($this->arrProductAttributeValue as $index => $arrValue) {
        	if ($arrValue['valueId'] == $valueId) {
                array_splice($this->arrProductAttributeValue, $index, 1);
                return true;
        	}
        }
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
        $this->arrProductAttributeValue = array();
        return true;
    }


    /**
     * Return the five products least recently added to the database.
     *
     * Note that this just selects the five Products with the greatest ID,
     * thus this will yield unexpected results if the IDs are set
     * by any other means than the AUTO_INCREMENT mechanism.
     * @return  array                   The array of the five Product objects
     * @global  mixed   $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function lastFive()
    {
        global $objDatabase;

        // select last five products added to the database
        $query = "
            SELECT id, DISTINCT product_id
              FROM ".DBPREFIX."module_shop_products
             WHERE status=1
          ORDER BY id DESC
        ";
        $objResult = $objDatabase->SelectLimit($query, 5);
        if (!$objResult) {
            return false;
        }
        $arrProduct = array();
        while (!$objResult->EOF) {
            $arrProduct[] = Product::getById($objResult->fields['id']);
            $objResult->MoveNext();
        }
        return $arrProduct;
    }
}

?>
