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
require_once ASCMS_MODULE_PATH . '/shop/lib/Vat.class.php';
/**
 * Weight
 */
require_once ASCMS_MODULE_PATH . '/shop/lib/Weight.class.php';
/**
 * Distribution (aka Handler)
 */
require_once ASCMS_MODULE_PATH . "/shop/lib/Distribution.class.php";
/**
 * Product Attribute - This is still alpha!
 */
require_once ASCMS_MODULE_PATH . '/shop/lib/ProductAttribute.class.php';


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
     * All Product table field names
     *
     * Might be used to generate queries.
     * @var array   $fieldNames
     */
    var $fieldNames = array(
        'id', 'product_id', 'picture', 'title', 'catid', 'handler',
        'normalprice', 'resellerprice', 'shortdesc', 'description',
        'stock', 'stock_visibility', 'discountprice', 'is_special_offer',
        'property1', 'property2', 'status', 'b2b', 'b2c',
        'startdate', 'enddate',
        'thumbnail_percent', 'thumbnail_quality',
        'manufacturer', 'external_link',
        'sort_order', 'vat_id', 'weight'
    );

    /**
     * Default picture name
     * @static
     * @var     string
     */
    //static
    var $defaultThumbnail = "no_picture.gif";

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
     * @var     float           $price              Product price
     * @access  private
     */
    var $price;
    /**
     * @var     integer         $sorting            Sorting order of the Product
     * @access  private
     */
    var $sorting;
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
     * @var     float           $resellerPrice      Product price for resellers
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
     * @var     boolean         $isVisible          Product visibility
     * @access  private
     */
    var $isVisible;
    /**
     * @var     float           $discountPrice      Product discount price
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
     * @var     array   $arrProductAttributeValueId
     *                                      ProductAttribute value IDs array
     * @access  private
     */
    var $arrProductAttributeValueId;


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
     * @param   float   $price          The Product price
     * @param   integer $status         The status of the Product (0 or 1)
     * @param   integer $sorting        The sorting order
     * @param   integer $weight         The Product weight
     * @param   integer $id             The optional Product ID to be updated
     * @return  Product                 The Product
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function Product(
        $code, $catId, $name, $distribution, $price,
        $status, $sorting, $weight, $id=0
    ) {
        $this->__construct(
            $code, $catId, $name, $distribution, $price,
            $status, $sorting, $weight, $id
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
     * @param   float   $price          The Product price
     * @param   integer $status         The status of the Product (0 or 1)
     * @param   integer $sorting        The sorting order
     * @param   integer $weight         The Product weight
     * @param   integer $id             The optional Product ID to be updated
     * @return  Product                 The Product
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function __construct(
        $code, $catId, $name, $distribution, $price,
        $status, $sorting, $weight, $id=0
    ) {
        // warn / debug
        if (!intval($catId)) {
            echo("WARNING: Product::__construct(): called with illegal category ID<br />");
        }

        // assign & check
        $this->code         = strip_tags($code);
        $this->catId        = intval($catId);
        $this->name         = strip_tags($name);
        $this->distribution = strip_tags($distribution);
        $this->price        = floatval($price);
        $this->sorting      = intval($sorting);
        $this->weight       = intval($weight);
        $this->id           = intval($id);
        $this->status       = ($status == 0 ? 0 : 1);

        if ($this->sorting <= 0) { $this->sorting = 0; }

        // default values for everything else
        $this->pictures         = '';
        $this->resellerPrice    =  0;
        $this->shortDesc        = '';
        $this->description      = '';
        $this->stock            =  1;
        $this->isVisible        =  0;
        $this->discountPrice    =  0;
        $this->isSpecialOffer   =  0;
        $this->property1        = '';
        $this->property2        = '';
        $this->isB2B            =  1;
        $this->isB2C            =  1;
        $this->startDate        = '';
        $this->endDate          = '';
        $this->thumbnailPercent = 25;
        $this->thumbnailQuality = 95;
        $this->manufacturerId   =  0;
        $this->externalLink     = '';
        $this->vatId            =  0;

        // enable cloning of Products with ProductAttributes
        $this->arrProductAttributeValueId =
            ProductAttribute::getProductAttributeValueIdArray($this->id);
    }


    /**
     * Get the ID
     * @return  integer                             Product ID
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getId()
    {
        return $this->id;
    }
    /**
     * Set the ID -- NOT ALLOWED
     * See {@link Product::clone()}
     */

    /**
     * Get the Product code
     * @return  string                              Product code
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getCode()
    {
        return $this->code;
    }
    /**
     * Set the Product code
     * @param   string          $code               Product code
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Get the Product name
     * @return  string                              Product name
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getName()
    {
        return $this->name;
    }
    /**
     * Set the Product name
     * @param   string          $name               Product name
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get the ShopCategory ID
     * @return  integer                             ShopCategory ID
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getShopCategoryId()
    {
        return $this->catId;
    }
    /**
     * Set the ShopCategory ID
     * @param   integer         $shopCategoryId     ShopCategory ID
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setShopCategoryId($shopCategoryId)
    {
        $this->ShopCategoryId = intval($shopCategoryId);
    }

    /**
     * Get the Product price
     * @return  float                               Product price
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getPrice()
    {
        return $this->price;
    }
    /**
     * Set the Product price
     * @param   float           $price              Product price
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setPrice($price)
    {
        $this->price = floatval($price);
    }

    /**
     * Get the Product sorting order
     * @return  integer                             Sorting order
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getOrder()
    {
        return $this->order;
    }
    /**
     * Set the Product sorting order
     * @param   integer         $order              Sorting order
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setOrder($order)
    {
        $this->order = intval($order);
    }

    /**
     * Get the Distribution type
     * @return  string                              Distribution type
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getDistribution()
    {
        return $this->distribution;
    }
    /**
     * Set the Distribution type
     * @param   string          $distribution       Distribution type
     * @copyright   CONTREXX CMS - COMVATION AG
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
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getStatus()
    {
        return $this->status;
    }
    /**
     * Set the status
     * @param   boolean         $status              Status
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setStatus($status)
    {
        $this->status = ($status == 0 ? 0 : 1);
    }

    /**
     * Get the pictures
     * @return  string                              Encoded picture string
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getPictures()
    {
        return $this->pictures;
    }
    /**
     * Set the pictures
     * @param   string          $pictures           Encoded picture string
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setPictures($pictures)
    {
        $this->pictures = $pictures;
    }

    /**
     * Get the reseller price
     * @return  float                               Reseller price
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getResellerPrice()
    {
        return $this->resellerPrice;
    }
    /**
     * Set the reseller price
     * @param   float           $resellerPrice      Reseller price
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setResellerPrice($resellerPrice)
    {
        $this->resellerPrice = floatval($resellerPrice);
    }

    /**
     * Get the short description
     * @return  string                              Short description
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getShortDesc()
    {
        return $this->shortDesc;
    }
    /**
     * Set the short description
     * @param   string          $shortDesc          Short description
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setShortDesc($shortDesc)
    {
        $this->shortDesc = $shortDesc;
    }

    /**
     * Get the description
     * @return  string                              Long description
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getDescription()
    {
        return $this->description;
    }
    /**
     * Set the description
     * @param   string          $description        Long description
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get the stock
     * @return  integer                             Stock
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getStock()
    {
        return $this->stock;
    }
    /**
     * Set the stock
     * @param   integer         $stock              Stock
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setStock($stock)
    {
        $this->stock = intval($stock);
    }

    /**
     * Get the stock visibility
     * @return  boolean                             Stock visibility
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function isVisible()
    {
        return $this->isVisible;
    }
    /**
     * Set the stock visibility
     * @param   boolean         $isVisible          Stock visibility
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setVisible($isVisible)
    {
        $this->isVisible = ($isVisible == 0 ? 0 : 1);
    }

    /**
     * Get the discount price
     * @return  float                               Discount price
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getDiscountPrice()
    {
        return $this->discountPrice;
    }
    /**
     * Set the discount price
     * @param   float           $discountPrice      Discount price
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setDiscountPrice($discountPrice)
    {
        $this->discountPrice = floatval($discountPrice);
    }

    /**
     * Get the special offer flag
     * @return  boolean                             Is special offer
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function isSpecialOffer()
    {
        return $this->isSpecialOffer;
    }
    /**
     * Set the special offer flag
     * @param   boolean         $isSpecialOffer     Is special offer
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setSpecialOffer($isSpecialOffer)
    {
        $this->isSpecialOffer = ($isSpecialOffer == 0 ? 0 : 1);
    }

    /**
     * Get the property 1
     * @return  string                              Product property 1
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getProperty1()
    {
        return $this->property1;
    }
    /**
     * Set the property 1
     * @param   string          $property1          Product property 1
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setproperty1($property1)
    {
        $this->property1 = $property1;
    }

    /**
     * Get the property 2
     * @return  string                              Product property 2
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getproperty2()
    {
        return $this->property2;
    }
    /**
     * Set the property 2
     * @param   string          $property2          Product property 2
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setproperty2($property2)
    {
        $this->property2 = $property2;
    }

    /**
     * Get the B2B flag
     * @return  boolean                             Is B2B
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function isB2B()
    {
        return $this->isB2B;
    }
    /**
     * Set the B2B flag
     * @param   boolean         $isB2B              Is B2B
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setB2B($isB2B)
    {
        $this->isB2B = ($isB2B == 0 ? 0 : 1);
    }

    /**
     * Get the B2C flag
     * @return  boolean                             Is B2C
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function isB2C()
    {
        return $this->isB2C;
    }
    /**
     * Set the B2C flag
     * @param   boolean         $isB2C              Is B2C
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setB2C($isB2C)
    {
        $this->isB2C = ($isB2C == 0 ? 0 : 1);
    }

    /**
     * Get the start date
     * @return  string                              Start date
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getStartDate()
    {
        return $this->startDate;
    }
    /**
     * Set the start date
     * @param   string          $startDate          Start date
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * Get the end date
     * @return  string                              End date
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getEndDate()
    {
        return $this->endDate;
    }
    /**
     * Set the end date
     * @param   string          $endDate            End date
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * Get the thumbnail size percentage
     * @return  integer                             Thumbnail size percentage
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getThumbnailPercent()
    {
        return $this->thumbnailPercent;
    }
    /**
     * Set the thumbnail size percentage
     * @param   integer         $thumbnailPercent   Thumbnail size percentage
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setThumbnailPercent($thumbnailPercent)
    {
        $this->thumbnailPercent = intval($thumbnailPercent);
    }

    /**
     * Get the thumbnail quality percentage
     * @return  integer                             Thumbnail quality percentage
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getThumbnailQuality()
    {
        return $this->thumbnailQuality;
    }
    /**
     * Set the thumbnail quality percentage
     * @param   integer         $thumbnailQuality   Thumbnail quality percentage
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setThumbnailQuality($thumbnailQuality)
    {
        $this->thumbnailQuality = intval($thumbnailQuality);
    }

    /**
     * Get the Manufacturer ID
     * @return  integer                             Manufacturer ID
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getManufacturerId()
    {
        return $this->manufacturerId;
    }
    /**
     * Set the Manufacturer ID
     * @param   integer         $manufacturer       Manufacturer ID
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setManufacturerId($manufacturerId)
    {
        $this->manufacturerId = $manufacturerId;
    }

    /**
     * Get the external link
     * @return  string                              External link
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getExternalLink()
    {
        return $this->externalLink;
    }
    /**
     * Set the external link
     * @param   string          $externalLink       External link
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setExternalLink($externalLink)
    {
        $this->externalLink = $externalLink;
    }

    /**
     * Get the VAT Id
     * @return  string                              VAT Id
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getVatId()
    {
        return $this->vatId;
    }
    /**
     * Set the VAT Id
     * @param   string          $vatId              VAT Id
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setVatId($vatId)
    {
        $this->vatId = intval($vatId);
    }

    /**
     * Get the weight
     * @return  string                              Weight
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getWeight()
    {
        return $this->weight;
    }
    /**
     * Set the weight
     * @param   string          $weight             Weight
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setWeight($weight)
    {
        $this->weight = intval($weight);
    }


    /**
     * Clone the Product
     *
     * Note that this does NOT create a copy in any way, but simply clears
     * the Product ID.  Upon storing this Product, a new ID is created.
     * Also note that all ProductAttributes *MUST* be link()ed after every
     * insert() in order for this to work properly!
     * @return      void
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function makeClone() {
        $this->id = '';
    }


    /**
     * Delete the Product specified by its ID from the database.
     *
     * Associated Attributes and pictures are deleted with it.
     * @param   integer     $productId  The Product ID
     * @return  boolean                 True on success, false otherwise
     * @todo    The handling of pictures is buggy.  Pictures used by other
     *          Products are only recognised if all file names are identical
     *          and in the same order!
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function delete()
    {
        global $objDatabase;
//echo("Debug: Product::delete(): entered<br />");

        if (!$this->id) {
echo("Product::delete(): Error: This Product is missing the Product ID<br />");
            return false;
        }

        // heck, most of this should go into the ProductPicture class...
        // split picture data into single pictures
        $arrPictures = split(':', $this->pictures);
        foreach ($arrPictures as $strPicture) {
//echo("Debug: Product::delete(): strPicture: '$strPicture'<br />");
            if ($strPicture != '') {
                // split picture into name, width, height
                $arrPicture = explode('?', $strPicture);
//echo("Debug: Product::delete(): picture: '".$arrPicture[0]."'<br />");

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
//echo("Debug: Product::delete(): pictureName $pictureName, thumbName $thumbName<br />");
//echo("Debug: Product::delete(): split filename: ");var_export($fileArr);echo("<br />thumbname: $thumbName<br />");
                    if (!@unlink(ASCMS_PATH."$thumbName"  )) { // ".ASCMS_SHOP_IMAGES_PATH."/
echo("Product::delete(): Warning: Failed to delete the thumbnail file '".ASCMS_PATH."$thumbName'<br />");
                        // should continue despite the warning - maybe the super user
                        // has just "rm -rf *"ed the picture for us. ;)
                        //return true;
                    }
//echo("Debug: Product::delete(): deleted thumbnail '".ASCMS_PATH."$thumbName'<br />");
                    if (!@unlink(ASCMS_PATH."$pictureName")) {
echo("Product::delete(): Warning: Failed to delete the picture file '".ASCMS_PATH."$pictureName'<br />");
                        // should continue despite the warning - maybe the super user
                        // has just "rm -rf *"ed the picture for us. ;)
                        //return true;
                    }
//echo("Debug: Product::delete(): deleted picture '".ASCMS_PATH."$pictureName'<br />");
                }
            }
        }

        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_shop_products
             WHERE id=$this->id
        ");
        if (!$objResult) {
echo("Product::delete(): Error: Failed to delete the Product from the database<br />");
            return false;
        }
        $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_shop_products_attributes
             WHERE product_id=$this->id
        ");
        if (!$objResult) {
echo("Product::delete(): Error: Failed to delete the Product Attributes from the database<br />");
            return false;
        }
        return true;
    }


    /**
     * Stores the Product object in the database.
     *
     * Either updates (id > 0) or inserts (id == 0) the object.
     *
     * @return      boolean     True on success, false otherwise
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function store()
    {
        if ($this->id > 0) {
            return ($this->update());
        }
        return ($this->insert());
    }


    /**
     * Update this Product in the database.
     * Returns the result of the query.
     *
     * @return      boolean         True on success, false otherwise
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function update()
    {
        global $objDatabase;

        $query = "
            UPDATE ".DBPREFIX."module_shop_products
            SET product_id='".contrexx_addslashes($this->code)."',
                picture='$this->pictures',
                title='".contrexx_addslashes($this->name)."',
                catid=$this->catId,
                handler='$this->distribution',
                normalprice=$this->price,
                resellerprice=$this->resellerPrice,
                shortdesc='".contrexx_addslashes($this->shortDesc)."',
                description='".contrexx_addslashes($this->description)."',
                stock=$this->stock,
                stock_visibility=$this->isVisible,
                discountprice=$this->discountPrice,
                is_special_offer=$this->isSpecialOffer,
                property1='".contrexx_addslashes($this->property1)."',
                property2='".contrexx_addslashes($this->property2)."',
                status=$this->status,
                b2b=$this->isB2B,
                b2c=$this->isB2C,
                startdate='$this->startDate',
                enddate='$this->endDate',
                thumbnail_percent=$this->thumbnailPercent,
                thumbnail_quality=$this->thumbnailQuality,
                manufacturer=".contrexx_addslashes($this->manufacturerId).",
                external_link='".contrexx_addslashes($this->externalLink)."',
                sort_order=$this->sorting,
                vat_id=$this->vatId,
                weight=$this->weight
          WHERE id=$this->id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
//echo("Product::update(): done<br />");
        return true;
    }


    /**
     * Insert this Product into the database.
     *
     * @return      boolean         True on success, false otherwise
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @todo        add sorting order to cloning, append order when
     *              adding ProductAttribute values here!
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
                thumbnail_percent, thumbnail_quality,
                manufacturer, external_link,
                sort_order, vat_id, weight
            ) VALUES ('".
            contrexx_addslashes($this->code)."', '$this->pictures', '".
            contrexx_addslashes($this->name)."', $this->catId,
            '$this->distribution',
            $this->price, $this->resellerPrice, '".
            contrexx_addslashes($this->shortDesc)."', '".
            contrexx_addslashes($this->description)."',
            $this->stock, $this->isVisible, $this->discountPrice,
            $this->isSpecialOffer,'".
            contrexx_addslashes($this->property1)."', '".
            contrexx_addslashes($this->property2)."', $this->status,
            $this->isB2B, $this->isB2C, '$this->startDate', '$this->endDate',
            $this->thumbnailPercent, $this->thumbnailQuality,
            $this->manufacturerId, ".
            contrexx_addslashes($this->externalLink)."',
            $this->sorting, $this->vatId, $this->weight)";
        $objResult = $objDatabase->Execute($query);
//echo("Debug: Product::insert(): query: $query<br />result: '$objResult'<br />");
        if (!$objResult) {
            return false;
        }
        // my brand new ID
        $this->id = $objDatabase->Insert_ID();
        // store ProductAttributes, if any
        if (is_array($this->arrProductAttributeValueId)) {
            foreach ($this->arrProductAttributeValueId as $valueId) {
                if (!ProductAttribute::addValueToProduct(
                    $this->id, $valueId, 0
                )) {
                    return false;
                }
            }
        }
        return true;
    }


    /**
     * Select a Product by ID from the database.
     *
     * @static
     * @param   integer     $id     The Product ID
     * @return  Product             The Product object on success, false otherwise
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getById($id)
    {
        global $objDatabase;

        $query = "SELECT * FROM ".DBPREFIX."module_shop_products WHERE id=$id";
//echo("Product::getById($id): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
//echo("Product::getById($id): objResult: '$objResult'<br />");
        if (!$objResult) {
//echo("Product::getById($id): query failed, objResult: '$objResult', count: ".$objResult->RecordCount()."<br />");
            return false;
        }
        if ($objResult->RecordCount() != 1) {
//echo("Product::getById($id): query result miscount: ".$objResult->RecordCount()."<br />");
            return false;
        }
        // constructor also read ProductAttributes if ID > 0
        $objProduct = new Product(
            $objResult->Fields('product_id'),
            $objResult->Fields('catid'),
            contrexx_stripslashes($objResult->Fields('title')),
            $objResult->Fields('handler'),
            $objResult->Fields('normalprice'),
            $objResult->Fields('status'),
            $objResult->Fields('sort_order'),
            $objResult->Fields('weight'),
            $objResult->Fields('id')
        );
        $objProduct->pictures         = $objResult->Fields('picture');
        $objProduct->resellerPrice    = $objResult->Fields('resellerprice');
        $objProduct->shortDesc        = contrexx_stripslashes($objResult->Fields('shortdesc'));
        $objProduct->description      = contrexx_stripslashes($objResult->Fields('description'));
        $objProduct->stock            = $objResult->Fields('stock');
        $objProduct->isVisible        = $objResult->Fields('stock_visibility');
        $objProduct->discountPrice    = $objResult->Fields('discountprice');
        $objProduct->isSpecialOffer   = $objResult->Fields('is_special_offer');
        $objProduct->property1        = contrexx_stripslashes($objResult->Fields('property1'));
        $objProduct->property2        = contrexx_stripslashes($objResult->Fields('property2'));
        $objProduct->isB2B            = $objResult->Fields('b2b');
        $objProduct->isB2C            = $objResult->Fields('b2c');
        $objProduct->startDate        = $objResult->Fields('startdate');
        $objProduct->endDate          = $objResult->Fields('enddate');
        $objProduct->thumbnailPercent = $objResult->Fields('thumbnail_percent');
        $objProduct->thumbnailQuality = $objResult->Fields('thumbnail_quality');
        $objProduct->manufacturerId   = contrexx_stripslashes($objResult->Fields('manufacturer'));
        $objProduct->externalLink     = contrexx_stripslashes($objResult->Fields('external_link'));
        $objProduct->vatId            = $objResult->Fields('vat_id');
        // also fetch the ProductAttribute value IDs
        $objProduct->arrProductAttributeValueId =
            ProductAttribute::getValueIdArray();
        return $objProduct;
    }


    /**
     * Returns the query for Product objects made from a wildcard pattern.
     * @static
     * @param   array       $arrPattern     The array of patterns to look for
     * @return  string                      The query string
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getWildcardQuery($arrPattern)
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
                        SELECT id FROM ".DBPREFIX."module_shop_products
                        WHERE ".$this->fieldNames[$fieldName]." LIKE '%".
                        contrexx_addslashes($pattern)."%'";
        	    }
        	} else {
//echo("Customer::getByWildcard(): illegal field name '$fieldName' ignored<br />");
        	}
        }
        return $query;
    }

    /**
     * Returns an array of Product objects found by wildcard.
     *
     * @static
     * @param   string      $pattern        The pattern to look for
     * @return  array                       An array of Products on success, false otherwise
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getByWildcard($arrPattern)
    {
        global $objDatabase;

        $query = Product::getWildcardQuery($arrPattern);
//echo("Product::getByWildcard($id): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
//echo("Product::getByWildcard($id): objResult: '$objResult'<br />");
        if (!$objResult) {
//echo("Product::getByWildcard($id): query failed, objResult: '$objResult', count: ".$objResult->RecordCount()."<br />");
            return false;
        }
        $arrProduct = array();
        while (!$objResult->EOF) {
            $arrProduct[] = Product::getById($objResult->Fields('id'));
            $objResult->MoveNext();
        }
        return $arrProduct;
    }


    /**
     * Returns an array of Products selected by parameters as available in
     * the Shop.
     *
     * @param   integer     $productId      The Product ID
     * @param   integer     $shopCategoryId The ShopCategory ID
     * @param   string      $pattern        A search pattern
     * @param   integer     $offset         The paging offset
     * @param   boolean     $lastFive       Flag for the last five Products
     *                                      added to the Shop
     * @return  array                       Array of Product objects,
     *                                      or false if none were found
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getByShopParams($productId=0, $shopCategoryId=0, $pattern='',
                             $offset=0, $lastFive=0)
    {
        global $objDatabase, $_CONFIG;

        if ($productId) {
            // select single Product by ID
            return array(Product::getById($productId));
        }

        if ($lastFive) {
            // select last five products added to the database
            $query = "
                SELECT id FROM ".DBPREFIX."module_shop_products
                 WHERE status=1
              ORDER BY product_id DESC LIMIT 5
            ";
        } else {
            // standard full featured query
            $q_search         = '';
            $q_special_offer  = 'AND (is_special_offer = 1) ';
            $q1_category      = '';
            $q2_category      = '';

            if ($shopCategoryId != 0) {
                // select Products by ShopCategory ID
                $q_special_offer = '';
                $q1_category = ', '.DBPREFIX.'module_shop_categories AS c';
                $q2_category = "
                    AND (p.catid = c.catid AND c.catid = $shopCategoryId)
                ";
            }
            if (!empty($pattern)) {
                // select Products by search pattern
                $q_special_offer = '';
                $q_search = "
                    AND (p.title LIKE '%$pattern%'
                        OR p.description LIKE '%$pattern%'
                        OR p.shortdesc LIKE '%$pattern%'
                        OR p.product_id LIKE '%$pattern%')
                        OR p.id LIKE '%$pattern%')
                ";
            }
            $query = "
                SELECT p.id FROM ".DBPREFIX."module_shop_products AS p
                       $q1_category
                 WHERE status=1
                       $q_special_offer
                       $q2_category
                       $q_search
              ORDER BY p.sort_order ASC, p.id DESC
            ";
        }
        if ($_CONFIG['corePagingLimit']) { // $_CONFIG from /config/settings.php
            $objResult = $objDatabase->SelectLimit(
                $query, $_CONFIG['corePagingLimit'], $offset
            );
        } else {
            $objResult = $objDatabase->Execute($query);
        }
        if (!$objResult) {
            return false;
        }
//var_export($objResult);
        if ($objResult->RecordCount()) {
            $arrProduct = array();
            while (!$objResult->EOF) {
                $arrProduct[] = Product::getById($objResult->Fields('id'));
                $objResult->MoveNext();
            }
            return $arrProduct;
        }
        // no Product
        return false;
    }


    /**
     * Delete Products from the ShopCategory given by its ID.
     *
     * If deleting one of the Products fails, aborts and returns false
     * immediately without trying to delete the remaining Products.
     * Deleting the ShopCategory after this method failed will most
     * likely result in Product bodies in the database!
     * @static
     * @param   integer     $catid      The ShopCategory ID
     * @return  boolean                 True on success, false otherwise
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function deleteByShopCategory($catId)
    {
//echo("Debug: Product::deleteByShopCategory(): catId $catId<br />");
        $arrProducts = Product::getByShopCategoryId($catId);
//echo("Debug: Product::deleteByShopCategory(): catId: $catId, arrProducts: ");var_export($arrProducts);echo("<br />");
        if (is_array($arrProducts)) {
            foreach ($arrProducts as $objProduct) {
                if (!$objProduct->delete()) {
echo("Product::deleteByShopCategory(): Error: Failed to delete Product<br />");
                    return false;
                }
//echo("Debug: Product::deleteByShopCategory(): Deleted ");var_export($objProduct);echo("<br />");
            }
        }
        return true;
    }

}

?>
