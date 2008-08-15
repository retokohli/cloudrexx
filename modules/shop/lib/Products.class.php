<?php
/**
 * Products helper class
 *
 * @version     $Id: 1.0.1$
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Test!
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 */

/**
 * Storage path for product images (absolute path)
 */
define('PRODUCT_IMAGE_PATH',        ASCMS_SHOP_IMAGES_PATH.'/');
/**
 * Storage path for product images (relativ pat)
 */
define('PRODUCT_IMAGE_WEB_PATH',    ASCMS_SHOP_IMAGES_WEB_PATH.'/');

/**
 * Product helper object
 *
 * Provides methods for accessing sets of Products, displaying menus
 * and the like.
 * @version     $Id: 1.0.1 $
 * @package     contrexx
 * @subpackage  module_shop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 */
class Products
{
    /**
     * All Product table field names
     *
     * Used to verify and create wildcard queries.
     * See {@link getWildcardQuery()}
     * @var array   $arrFieldName
     */
    private $arrFieldName = array(
        'id', 'product_id', 'picture', 'title', 'catid', 'handler',
        'normalprice', 'resellerprice', 'shortdesc', 'description',
        'stock', 'stock_visibility', 'discountprice', 'is_special_offer',
        'property1', 'property2', 'status', 'b2b', 'b2c',
        'startdate', 'enddate',
        'thumbnail_percent', 'thumbnail_quality',
        'manufacturer', 'external_link',
        'sort_order', 'vat_id', 'weight', 'flags', 'usergroups',
    );


    /**
     * Create a Products helper object
     * @access  public
     * @return  Products                The helper
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function __construct()
    {
    }


    /**
     * Returns the query for Product objects made from a wildcard pattern.
     * @param       array       $arrPattern     The array of patterns
     *                                          to look for
     * @return      string                      The query string
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getWildcardQuery($arrPattern)
    {
        $query = '';
        foreach ($arrPattern as $fieldName => $pattern) {
            if (in_array($fieldName, $this->arrFieldName)) {
                if ($query) {
                    $query .= "
                        OR $fieldName LIKE '%".
                        addslashes($pattern)."%'";
                } else {
                    $query  = "
                        SELECT id FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
                        WHERE $fieldName LIKE '%".
                        addslashes($pattern)."%'";
                }
            }
        }
        return $query;
    }


    /**
     * Returns an array of Product objects found by wildcard.
     *
     * @param       string      $pattern        The pattern to look for
     * @return      array                       An array of Products on success,
     *                                          false otherwise
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getByWildcard($arrPattern)
    {
        global $objDatabase;

        $query = $this->getWildcardQuery($arrPattern);
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
//echo("Error: Query failed:<br />$query<br />");
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
     * Returns an array of Product objects sharing the same Product code.
     * @param   string      $customId   The Product code
     * @return  mixed                   The array of matching Product objects
     *                                  on success, false otherwise.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getByCustomId($customId)
    {
        global $objDatabase;

        if (empty($customId)) {
            // No way.
            return false;
        }
        $query = "
            SELECT id FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
             WHERE product_id='$customId'
          ORDER BY id ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
//echo("Error: Query failed:<br />$query<br />");
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
     * Return the five products least recently added to the database.
     *
     * OBSOLETE -- Superseeded by {@link Shop::getByShopParams()}.
     * Note that this just selects the five Products with the highest ID,
     * thus this will yield unexpected results if the IDs are set
     * by any other means than the AUTO_INCREMENT mechanism.
     * @return  array                   The array of the five Product objects
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @author      Reto Kohli <reto.kohli@comvation.com>
    function lastFive()
    {
        global $objDatabase;

        // select last five products added to the database
        $query = "
            SELECT id, DISTINCT product_id
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
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
     */


    /**
     * Returns an array of Products selected by parameters as available in
     * the Shop.
     *
     * The $count parameter is set to the number of records found.
     * @param   integer     $count          The desired number of Products,
     *                                      by reference
     * @param   integer     $offset         The Product offset
     * @param   integer     $productId      The Product ID
     * @param   integer     $shopCategoryId The ShopCategory ID
     * @param   integer     $manufacturerId The Manufacturer ID
     * @param   string      $pattern        A search pattern
     * @param   boolean     $flagSpecialoffer Flag for special offers
     * @param   boolean     $flagLastFive   Flag for the last five Products
     *                                      added to the Shop
     * @param   boolean     $flagShowInactive   Include inactive Products
     *                                      if true.  Backend use only!
     * @param   integer     $orderSetting   The sorting order setting
     * @return  array                       Array of Product objects,
     *                                      or false if none were found
     * @global      ADONewConnection
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getByShopParams(
        &$count, $offset=0,
        $productId=0, $shopCategoryId=0, $manufacturerId=0, $pattern='',
        $flagSpecialoffer=false, $flagLastFive=false, $orderSetting=1,
        $flagShowInactive=false
    ) {
        global $objDatabase, $_CONFIG;

//echo("getByShopParams($count, $offset, $productId, $shopCategoryId, $manufacturerId, $pattern, $flagSpecialoffer, $flagLastFive, $orderSetting, $flagShowInactive): entered<br />");

        if ($productId) {
            // select single Product by ID
            $objProduct = Product::getById($productId);
            if ($objProduct) {
                $count = 1;
                return array($objProduct);
            }
            $count = 0;
            return false;
        }

        $queryCount = "SELECT COUNT(*) as numof_products";
        if ($flagLastFive) {
            // select last five products added to the database
            $querySelect = "SELECT id";
            $queryTail = "
                  FROM ".DBPREFIX."module_shop_products
                 ".($flagShowInactive ? '' : 'WHERE status=1 ')."
                 ORDER BY product_id DESC
            ";
            $count = 5;
        } else {
            // Build standard full featured query
            $q_special_offer  =
                ($flagSpecialoffer ? 'AND is_special_offer=1 ' : '');
            $q1_category      = '';
            $q2_category      = '';
            $q1_manufacturer  = '';
            $q2_manufacturer  = '';
            $q_search         = '';
            if ($shopCategoryId > 0) {
                // select Products by ShopCategory ID
                $q_special_offer = '';
                $q1_category = 'INNER JOIN '.DBPREFIX.'module_shop_categories AS c USING (catid)';
                $q2_category = "
                    AND p.catid=$shopCategoryId
                ";
            }
            if ($manufacturerId > 0) {
                // select Products by Manufacturer ID
                $q_special_offer = '';
                $q1_manufacturer = 'INNER JOIN '.DBPREFIX.'module_shop_manufacturer AS m ON m.id=p.manufacturer';
                $q2_manufacturer = "
                    AND p.manufacturer=$manufacturerId
                ";
            }
            if (!empty($pattern)) {
                // select Products by search pattern
                $q_special_offer = '';
                $q_search = "
                    AND (p.title LIKE '%$pattern%'
                        OR p.description LIKE '%$pattern%'
                        OR p.shortdesc LIKE '%$pattern%'
                        OR p.keywords LIKE '%$pattern%'
                        OR p.product_id LIKE '%$pattern%')
                        OR p.id LIKE '%$pattern%')
                ";
            }
            $querySelect = "SELECT p.id";
            $queryCount = "SELECT COUNT(*) as numof_products";
            $queryTail = "
                  FROM ".DBPREFIX."module_shop_products AS p
                       $q1_category $q1_manufacturer
                 WHERE ".($flagShowInactive ? '1' : 'status=1')."
                       $q_special_offer
                       $q2_category $q2_manufacturer
                       $q_search
              ORDER BY ".
                ($orderSetting == 2
                  ? 'p.title ASC, p.product_id ASC' // Alphabetic
                  : ($orderSetting == 3
                      ? 'p.product_id ASC, p.title ASC' // Product Code
                      // Default; $orderSetting == 1
                      : 'p.sort_order ASC, p.id DESC' // Individual
                    )
                );
        }
        if ($count == 0) {
            if ($_CONFIG['corePagingLimit']) { // $_CONFIG from /config/settings.php
                $count = $_CONFIG['corePagingLimit'];
            }
        }
        if ($count) {
            $objResult = $objDatabase->SelectLimit($querySelect.$queryTail, $count, $offset);
        } else {
            $objResult = $objDatabase->Execute($querySelect.$queryTail);
        }
        if (!$objResult) {
            return false;
        }
        $arrProduct = array();
        while (!$objResult->EOF) {
            $arrProduct[] = Product::getById($objResult->fields['id']);
            $objResult->MoveNext();
        }
//echo("getByShopParams(): got ".count($arrProduct)." products from $offset<br />");
//var_export($objResult);
        $objResult = $objDatabase->Execute($queryCount.$queryTail);
        if (!$objResult) {
            return false;
        }
        $count = $objResult->fields['numof_products'];
//echo("getByShopParams(): $count products total<br />");
        return $arrProduct;
    }


    /**
     * Delete Products from the ShopCategory given by its ID.
     *
     * If deleting one of the Products fails, aborts and returns false
     * immediately without trying to delete the remaining Products.
     * Deleting the ShopCategory after this method failed will most
     * likely result in Product bodies in the database!
     * @static
     * @param       integer     $catid          The ShopCategory ID
     * @return      boolean                     True on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function deleteByShopCategory($catId, $flagDeleteImages=false)
    {
        $arrProductId = Products::getIdArrayByShopCategory($catId);
        if (!is_array($arrProductId)) {
            return false;
        }
        // Look whether this is within a virtual ShopCategory
        $virtualContainer = '';
        $parentId = $catId;
        do {
            $objShopCategory = ShopCategory::getById($parentId);
            if (!$objShopCategory) {
                return false;
            }
            if ($objShopCategory->isVirtual()) {
                // The name of any virtual ShopCategory is used to mark
                // Products within
                $virtualContainer = $objShopCategory->getName();
                break;
            }
            $parentId = $objShopCategory->getParentId();
        } while ($parentId != 0);

        // Remove the Products in one way or another
        foreach ($arrProductId as $productId) {
            $objProduct = Product::getById($productId);
            if (!$objProduct) {
                return false;
            }
            if ($virtualContainer != ''
             && $objProduct->getFlags() != '') {
                // Virtual ShopCategories and their content depends on
                // the Product objects' flags.
                foreach ($arrProductId as $objProduct) {
                    $objProduct->removeFlag($virtualContainer);
                    if (!Products::changeFlagsByProductCode(
                        $objProduct->getCode(),
                        $objProduct->getFlags()
                    )) {
                        return false;
                    }
                }
            } else {
                // Normal, non-virtual ShopCategory.
                // Remove all Products having the same Product code.
                if (!Products::deleteByCode(
                    $objProduct->getCode(),
                    $flagDeleteImages)
                ) {
                    return false;
                }
            }
        }
        return true;
    }


    /**
     * Delete Products bearing the given Product code from the database.
     * @param   integer     $productCode        The Product code. This *MUST*
     *                                          be non-empty!
     * @param   boolean     $flagDeleteImages   If true, Product images are
     *                                          deleted as well
     * @return  boolean                         True on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function deleteByCode($productCode, $flagDeleteImages)
    {
        if (empty($productCode)) {
            return false;
        }
        $arrProduct = Products::getByCustomId($productCode);
        if ($arrProduct === false) {
            return false;
        }
        $result = true;
        foreach ($arrProduct as $objProduct) {
            if (!$objProduct->delete($flagDeleteImages)) {
                $result = false;
            }
        }
        return $result;
    }


    /**
     * Returns an array of Product IDs contained by the given
     * ShopCategory ID.
     * @static
     * @param   integer     $catId      The ShopCategory ID
     * @return  mixed                   The array of Product IDs on success,
     *                                  false otherwise.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getIdArrayByShopCategory($catId)
    {
        global $objDatabase;

        $query = "
            SELECT id
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
             WHERE catid=$catId
          ORDER BY sort_order ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
//echo("Error: Query failed:<br />$query<br />");
            return false;
        }
        $arrProductId = array();
        while (!$objResult->EOF) {
            $arrProductId[] = $objResult->fields['id'];
            $objResult->MoveNext();
        }
        return $arrProductId;
    }


    /**
     * Returns an array of Product objects contained by the ShopCategory
     * with the given ID.
     * @static
     * @param   integer     $catId      The ShopCategory ID
     * @return  mixed                   The array of Product IDs on success,
     *                                  false otherwise.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getByShopCategory($catId)
    {
        global $objDatabase;

        $query = "
            SELECT id
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
             WHERE catid=$catId
          ORDER BY sort_order ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
//echo("Error: Query failed:<br />$query<br />");
            return false;
        }
        $arrProduct = array();
        while (!$objResult->EOF) {
            $arrProduct[] = Product::getById($objResult->fields['id']);
            $objResult->MoveNext();
        }
        return $arrProduct;
    }


    /**
     * Returns the first matching picture name found in the Products
     * within the Shop Category given by its ID.
     * @static
     * @return      string                      The image name, or the
     *                                          empty string.
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getPictureByCategoryId($catId)
    {
        global $objDatabase;

        $query = "
            SELECT picture
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
             WHERE catid=$catId
               AND picture!=''
          ORDER BY sort_order
        ";
        $objResult = $objDatabase->SelectLimit($query, 1);
        if ($objResult && $objResult->RecordCount() > 0) {
            // Got a picture
            $arrImages = $this->_getShopImagesFromBase64String(
                $objResult->fields['picture']
            );
            $imageName = $arrImages[1]['img'];
            return $imageName;
        }
        // No picture found here
        return '';
    }


    /**
     * Returns an array of ShopCategory IDs containing Products with
     * their flags containing the given string.
     * @static
     * @param   string  $strName    The name of the flag to match
     * @return  mixed               The array of ShopCategory IDs on success,
     *                              false otherwise.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getShopCategoryIdArrayByFlag($strName)
    {
        global $objDatabase;

        $query = "
            SELECT DISTINCT catId
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
             WHERE flags LIKE '%$strName%'
          ORDER BY catId ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
//echo("Error: Query failed:<br />$query<br />");
            return false;
        }
        $arrShopCategoryId = array();
        while (!$objResult->EOF) {
            $arrShopCategoryId[] = $objResult->Fields['catid'];
            $objResult->MoveNext();
        }
        return $arrShopCategoryId;
    }


    /**
     * Create thumbnails and update the corresponding Product records
     *
     * Scans the Products with the given IDs.  If a non-empty picture string
     * with a reasonable extension is encountered, determines whether
     * the corresponding thumbnail is available and up to date or not.
     * If not, tries to load the file and to create a thumbnail.
     * If it succeeds, it also updates the picture field with the base64
     * encoded entry containing the image width and height.
     * Note that only single file names are supported!
     * Also note that this method returns a string with information about
     * problems that were encountered.
     * It skips records which contain no or invalid image
     * names, thumbnails that cannot be created, and records which refuse
     * to be updated!
     * The reasoning behind this is that this method is currently only called
     * from within some {@link _import()} methods.  The focus lies on importing
     * Products; whether or not thumbnails can be created is secondary, as the
     * process can be repeated if there is a problem.
     * @param   integer     $arrId      The array of Product IDs
     * @return  string                  Empty string on success, a string
     *                                  with error messages otherwise.
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @global  array
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function makeThumbnailsById($arrId)
    {
        global $objDatabase, $_ARRAYLANG;

        require_once ASCMS_FRAMEWORK_PATH.'/Image.class.php';

        if (!is_array($arrId)) {
            //$this->addMessage("Keine Produkt IDs zum erstellen der Thumbnails vorhanden ($id).");
            return false;
        }

        // Collect and group errors
        $arrMissingProductPicture = array();
        $arrFailedCreatingThumb   = array();
        $strError = '';

        $objImageManager = new ImageManager();
        foreach ($arrId as $id) {
            if ($id <= 0) {
                $strError .= ($strError ? '<br />' : '').
                    sprintf($_ARRAYLANG['TXT_SHOP_INVALID_PRODUCT_ID'], $id);
                continue;
            }
            $objProduct = Product::getById($id);
            if (!$objProduct) {
                $strError .= ($strError ? '<br />' : '').
                    sprintf($_ARRAYLANG['TXT_SHOP_INVALID_PRODUCT_ID'], $id);
                continue;
            }
            $imageName = $objProduct->getPictures();
            $imagePath = PRODUCT_IMAGE_PATH.'/'.$imageName;
            // only try to create thumbs from entries that contain a
            // plain text file name (i.e. from an import)
            if (   $imageName == ''
                || !preg_match('/\.(?:jpg|jpeg|gif|png)$/', $imageName)) {
                $strError .= ($strError ? '<br />' : '').
                    sprintf(
                        $_ARRAYLANG['TXT_SHOP_UNSUPPORTED_IMAGE_FORMAT'],
                        $imageName, $id
                    );
                continue;
            }
            // if the picture is missing, skip it.
            if (!file_exists($imagePath)) {
                $arrMissingProductPicture["$id - $imageName"] = 1;
                continue;
            }
            $thumbResult = true;
            $width  = 0;
            $height = 0;
            // If the thumbnail exists and is newer than the picture,
            // don't create it again.
            if (file_exists($imagePath.'.thumb')
             && filemtime($imagePath.'.thumb') > filemtime($imagePath)) {
                //$this->addMessage("Hinweis: Thumbnail für Produkt ID '$id' existiert bereits");
                // Need the original size to update the record, though
                list($width, $height) =
                    $objImageManager->_getImageSize($imagePath);
            } else {
                // Create thumbnail, get the original size.
                // Deleting the old thumb beforehand is integrated into
                // _createThumbWhq().
                $thumbResult = $objImageManager->_createThumbWhq(
                    PRODUCT_IMAGE_PATH,
                    PRODUCT_IMAGE_WEB_PATH,
                    $imageName,
                    $this->arrConfig['shop_thumbnail_max_width']['value'],
                    $this->arrConfig['shop_thumbnail_max_height']['value'],
                    $this->arrConfig['shop_thumbnail_quality']['value']
                );
                $width  = $objImageManager->orgImageWidth;
                $height = $objImageManager->orgImageHeight;
            }
            // The database needs to be updated, however, as all Products
            // have been imported.
            if ($thumbResult) {
                $shopPicture =
                    base64_encode($imageName).
                    '?'.base64_encode($width).
                    '?'.base64_encode($height).
                    ':??:??';
                $objProduct->setPictures($shopPicture);
                $objProduct->store();
            } else {
                $arrFailedCreatingThumb[] = $id;
            }
        }
        if (count($arrMissingProductPicture)) {
            ksort($arrMissingProductPicture);
            $strError .= ($strError ? '<br />' : '').
                $_ARRAYLANG['TXT_SHOP_MISSING_PRODUCT_IMAGES'].' '.
                join(', ', array_keys($arrMissingProductPicture));
        }
        if (count($arrFailedCreatingThumb)) {
            sort($arrFailedCreatingThumb);
            $strError .= ($strError ? '<br />' : '').
                $_ARRAYLANG['TXT_SHOP_ERROR_CREATING_PRODUCT_THUMBNAIL'].' '.
                join(', ', $arrFailedCreatingThumb);
        }
        return $strError;
    }


    /**
     * Apply the flags of all Products to the virtual ShopCategories
     *
     * Any Product and ShopCategory carrying one or more of the names
     * of any ShopCategory marked as "__VIRTUAL__" is cloned and added
     * to that category.  Those having any such flags removed are deleted
     * from the respective category.  Identical copies of the same Products
     * are recognized by their "product_id" (the Product code).
     *
     * Note that in this current version, only the flags of Products are
     * tested and applied.  Products are cloned and added together with
     * their immediate parent ShopCategories (aka "Article").
     *
     * Thus, all Products within the same "Article" ShopCategory carry the
     * same flags, as does the containing ShopCategory itself.
     * @param   integer     $productCode  The Product code (*NOT* the ID).
     *                                    This must be non-empty!
     * @param   string      $strFlags     The new flags for the Product
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function changeFlagsByProductCode($productCode, $strNewFlags)
    {
        if (empty($productCode)) {
            return false;
        }
        // Get all available flags.  These are represented by the names
        // of virtual root ShopCategories.
        $arrVirtual = ShopCategories::getVirtualCategoryNameArray();

        // Get the affected identical Products
        $arrProduct = Products::getByCustomId($productCode);
        if (count($arrProduct) == 0) {
            // No way we can do anything useful without them.
            return false;
        }

        // Get the Product flags.  As they're all the same, we'll use the
        // first one here.
        // Note that this object is used for reference only and is never stored.
        // Its database entry will be updated along the way, however.
        $_objProduct = $arrProduct[0];
        $strOldFlags = $_objProduct->getFlags();
        // Flag indicating whether the article has been cloned already
        // for all new flags set.
        $flagCloned = false;

        // Now apply the changes to all those identical Products, their parent
        // ShopCategories, and all sibling Products within them.
        foreach ($arrProduct as $objProduct) {
            // Get the containing article ShopCategory.
            $catId = $objProduct->getShopCategoryId();
            $objArticleCategory = ShopCategory::getById($catId);
            if (!$objArticleCategory) {
                // This should not happen!
                continue;
            }

            // Get parent (subgroup)
            $objSubGroupCategory =
                ShopCategory::getById($objArticleCategory->getParentId());
            if (!$objSubGroupCategory) {
                // This should not happen!
                continue;
            }
            $subgroupName = $objSubGroupCategory->getName();

            // Get grandparent (group, root ShopCategory)
            $objRootCategory =
                ShopCategory::getById($objSubGroupCategory->getParentId());
            if (!$objRootCategory) {
                // This should not happen!
                continue;
            }

            // Apply the new flags to all Products and Article ShopCategories.
            // Update the flags of the original Article ShopCategory first
            $objArticleCategory->setFlags($strNewFlags);
            $objArticleCategory->store();

            // Get all sibling Products affected by the same flags
            $arrSiblingProducts = Products::getByShopCategory(
                $objArticleCategory->getId()
            );

            // Set the new flag set for all Products within the Article
            // ShopCategory.
            foreach ($arrSiblingProducts as $objProduct) {
                $objProduct->setFlags($strNewFlags);
                $objProduct->store();
            }

            // Check whether this group is affected by the changes.
            // If its name matches one of the flags, the Article and subgroup
            // may have to be removed.
            $strFlag = $objRootCategory->getName();
            if (preg_match("/$strFlag/", $strNewFlags)) {
                // The flag is still there, don't bother.
                continue;
            }

            // Also check whether this is a virtual root ShopCategory.
            if (in_array($strFlag, $arrVirtual)) {
                // It is one of the virtual roots, and the flag is missing.
                // So the Article has to be removed from this group.
                $objArticleCategory->delete();
                $objArticleCategory = false;
                // And if the subgroup happens to contain no more
                // "Article", delete it as well.
                $arrChildren = $objSubGroupCategory->getChildrenIdArray();
                if (count($arrChildren) == 0) {
                    $objSubGroupCategory->delete();
                }
                continue;
            }

            // Here, the virtual ShopCategory groups have been processed,
            // the only ones left are the "normal" ShopCategories.
            // Clone one of the Article ShopCategories for each of the
            // new flags set.
            if ($flagCloned) {
                // Already did that.
                continue;
            }

            // Find out what flags have been added.
            foreach ($arrVirtual as $strFlag) {
                if (!preg_match("/$strFlag/", $strNewFlags)) {
                    // That flag is not present in the new flag set.
                    continue;
                }
                if (preg_match("/$strFlag/", $strOldFlags)) {
                    // But it has been before.  The respective branch has
                    // been truncated above already.
                    continue;
                }

                // That is a new flag for which we have to clone the Article.
                // Get the affected grandparent (group, root ShopCategory)
                $objTargetRootCategory =
                    ShopCategories::getChildNamed(0, $strFlag, false);
                if (!$objTargetRootCategory) {
                    // This should not happen!
                    continue;
                }
                // Check whether the subgroup exists already
                $objTargetSubGroupCategory =
                    ShopCategories::getChildNamed(
                        $objTargetRootCategory->getId(), $subgroupName, false
                    );
                if (!$objTargetSubGroupCategory) {
                    // Nope, add the subgroup.
                    $objSubGroupCategory->makeClone();
                    $objSubGroupCategory->setParentId($objTargetRootCategory->getId());
                    $objSubGroupCategory->store();
                    $objTargetSubGroupCategory = $objSubGroupCategory;
                }

                // Check whether the Article ShopCategory exists already
                $objTargetArticleCategory =
                    ShopCategories::getChildNamed(
                        $objTargetSubGroupCategory->getId(),
                        $objArticleCategory->getName(),
                        false
                    );
                if ($objTargetArticleCategory) {
                    // The Article Category already exists.
                } else {
                    // Nope, clone the "Article" ShopCategory and add it to the
                    // subgroup.  Note that the flags have been set already
                    // and don't need to be changed again here.
                    // Also note that the cloning process includes all content
                    // of the Article ShopCategory, but the flags will remain
                    // unchanged. That's why the flags have already been
                    // changed right at the beginning of the process.
                    $objArticleCategory->makeClone(true, true);
                    $objArticleCategory->setParentId($objTargetSubGroupCategory->getId());
                    $objArticleCategory->store();
                    $objTargetArticleCategory = $objArticleCategory;
                }
            } // foreach $arrVirtual
        } // foreach $arrProduct
        // And we're done!
        return true;
    }
}

?>
