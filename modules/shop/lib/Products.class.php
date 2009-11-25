<?php

/**
 * Products helper class
 *
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Test!
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.1.0
 */

/**
 * Storage path for product images (absolute path)
 */
define('PRODUCT_IMAGE_PATH',     ASCMS_SHOP_IMAGES_PATH.'/');
/**
 * Storage path for product images (relativ pat)
 */
define('PRODUCT_IMAGE_WEB_PATH', ASCMS_SHOP_IMAGES_WEB_PATH.'/');

define('SHOP_PRODUCT_DEFAULT_VIEW_NONE',      0);
define('SHOP_PRODUCT_DEFAULT_VIEW_MARKED',    1);
define('SHOP_PRODUCT_DEFAULT_VIEW_DISCOUNTS', 2);
define('SHOP_PRODUCT_DEFAULT_VIEW_LASTFIVE',  3);
define('SHOP_PRODUCT_DEFAULT_VIEW_COUNT',     4);

/**
 * Product helper object
 *
 * Provides methods for accessing sets of Products, displaying menus
 * and the like.
 * @package     contrexx
 * @subpackage  module_shop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.1.0
 */
class Products
{
    /**
     * Returns an array of Product objects sharing the same Product code.
     * @param   string      $customId   The Product code
     * @return  mixed                   The array of matching Product objects
     *                                  on success, false otherwise.
     * @static
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function getByCustomId($customId)
    {
        global $objDatabase;

        if (empty($customId)) return false;
        $query = "
            SELECT id FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
             WHERE product_id='$customId'
             ORDER BY id ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
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
     * The $count parameter is set to the number of records found.
     * @param   integer     $count          The desired number of Products,
     *                                      by reference
     * @param   integer     $offset         The Product offset
     * @param   integer     $productId      The Product ID
     * @param   integer     $shopCategoryId The ShopCategory ID
     * @param   integer     $manufacturerId The Manufacturer ID
     * @param   string      $pattern        A search pattern
     * @param   boolean     $flagSpecialoffer Limit results to special offers
     *                                      if true.  Disabled if either
     *                                      the Product ID, Category ID,
     *                                      Manufacturer ID, or the search
     *                                      pattern is non-empty.
     * @param   boolean     $flagLastFive   Limit results to the last five
     *                                      Products added to the Shop if true.
     *                                      Note: You may specify an integer
     *                                      count as well, this will set the
     *                                      limit accordingly.
     * @param   integer     $orderSetting   The sorting order setting, defaults
     *                                      to the order field value ascending,
     *                                      Product ID descending
     * @param   boolean     $flagIsReseller The reseller status of the
     *                                      current customer, ignored if
     *                                      it's the empty string
     * @param   boolean     $flagShowInactive   Include inactive Products
     *                                      if true.  Backend use only!
     * @return  array                       Array of Product objects,
     *                                      or false if none were found
     * @static
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function getByShopParams(
        &$count, $offset=0,
        $productId=0, $shopCategoryId=0, $manufacturerId=0, $pattern='',
        $flagSpecialoffer=false, $flagLastFive=false,
        $orderSetting='',
        $flagIsReseller='',
        $flagShowInactive=false
    ) {
        global $objDatabase, $_CONFIG;

        // Do not show any Products if no selection is made at all
        if (   empty($productId)
            && empty($shopCategoryId)
            && empty($manufacturerId)
            && empty($pattern)
            && empty($flagSpecialoffer)
            && empty($flagLastFive)
            && empty($flagShowInactive) // Backend only!
        ) return array();
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
        if (empty($orderSetting))
            $orderSetting = 'p.sort_order ASC, p.id DESC';

        // Limit Products visible to resellers or non-resellers.
        $queryReseller =
            ($flagIsReseller === true
              ? 'AND b2b=1'
              : ($flagIsReseller === false
                  ? 'AND b2c=1' : ''
                )
            );

        $queryCount = "SELECT COUNT(*) as numof_products";
        if (   $flagLastFive
            || $flagSpecialoffer === SHOP_PRODUCT_DEFAULT_VIEW_LASTFIVE) {
            // select last five products added to the database
            $querySelect = "SELECT id";
            $queryTail = "
                  FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
                 WHERE 1
                   ".($flagShowInactive ? '' : 'AND p.status=1 ')."
                   $queryReseller
                 ORDER BY product_id DESC
            ";
            $count = ($flagLastFive !== true ? $flagLastFive : 5);
        } else {
            // Build standard full featured query
            $q_special_offer =
                (   $flagSpecialoffer === SHOP_PRODUCT_DEFAULT_VIEW_DISCOUNTS
                 || $flagSpecialoffer === true // Old behavior!
                  ? 'AND is_special_offer=1'
                  : ($flagSpecialoffer === SHOP_PRODUCT_DEFAULT_VIEW_MARKED
                     ? "AND flags LIKE '%__SHOWONSTARTPAGE__%'" : ''
                    )
                );
            $q1_category     = '';
            $q2_category     = '';
            $q1_manufacturer = '';
            $q2_manufacturer = '';
            $q_search        = '';
            if ($shopCategoryId > 0) {
                // select Products by ShopCategory ID
                $q_special_offer = '';
                $q1_category = 'INNER JOIN '.DBPREFIX.'module_shop'.MODULE_INDEX.'_categories AS c ON c.catid=p.catid';
                $q2_category =
                    "AND p.catid=$shopCategoryId".
                    ($flagShowInactive ? '' : ' AND c.catstatus=1');
            }
            if ($manufacturerId > 0) {
                // select Products by Manufacturer ID
                $q_special_offer = '';
                $q1_manufacturer = 'INNER JOIN '.DBPREFIX.'module_shop'.MODULE_INDEX.'_manufacturer AS m ON m.id=p.manufacturer';
                $q2_manufacturer = "AND p.manufacturer=$manufacturerId";
            }
            if (!empty($pattern)) {
                // select Products by search pattern
                $q_special_offer = '';
                $q_search = "
                    AND (p.title LIKE '%$pattern%'
                        OR p.description LIKE '%$pattern%'
                        OR p.shortdesc LIKE '%$pattern%'
                        OR p.product_id LIKE '%$pattern%'
                        OR p.id LIKE '%$pattern%')
                        OR p.keywords LIKE '%$pattern%'
                ";
            }
            $querySelect = "SELECT p.id";
            $queryCount = "SELECT COUNT(*) as numof_products";
            $queryTail = "
                  FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products AS p
                       $q1_category $q1_manufacturer
                 WHERE ".($flagShowInactive ? '1' : 'p.status=1')."
                   $queryReseller $q_special_offer
                   $q2_category $q2_manufacturer $q_search
              ".($orderSetting ? "ORDER BY $orderSetting" : '');
        }
        $limit =
            ($count > 0
                ? $count
                : (!empty($_CONFIG['corePagingLimit'])
                    ? $_CONFIG['corePagingLimit']
                    : 10
                  )
            );
        $count = 0;
//        if ($limit) {
        $objResult = $objDatabase->SelectLimit($querySelect.$queryTail, $limit, $offset);
//        } else {
//            $objResult = $objDatabase->Execute($querySelect.$queryTail);
//        }
        if (!$objResult) return false;
        $arrProduct = array();
        while (!$objResult->EOF) {
            $product_id = $objResult->fields['id'];
            $objProduct = Product::getById($product_id);
            if ($objProduct)
                $arrProduct[$product_id] = $objProduct;
            $objResult->MoveNext();
        }
        $objResult = $objDatabase->Execute($queryCount.$queryTail);
        if (!$objResult) return false;
        $count = $objResult->fields['numof_products'];
        return $arrProduct;
    }


    /**
     * Delete Products from the ShopCategory given by its ID.
     *
     * If deleting one of the Products fails, aborts and returns false
     * immediately without trying to delete the remaining Products.
     * Deleting the ShopCategory after this method failed will most
     * likely result in Product bodies in the database!
     * @param       integer     $catid          The ShopCategory ID
     * @return      boolean                     True on success, false otherwise
     * @static
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function deleteByShopCategory($catId, $flagDeleteImages=false)
    {
        $arrProductId = Products::getIdArrayByShopCategory($catId);
        if (!is_array($arrProductId)) return false;
        // Look whether this is within a virtual ShopCategory
        $virtualContainer = '';
        $parentId = $catId;
        do {
            $objShopCategory = ShopCategory::getById($parentId);
            if (!$objShopCategory) return false;
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
            if (!$objProduct) return false;
            if ($virtualContainer != ''
             && $objProduct->getFlags() != '') {
                // Virtual ShopCategories and their content depends on
                // the Product objects' flags.
                foreach ($arrProductId as $objProduct) {
                    $objProduct->removeFlag($virtualContainer);
                    if (!Products::changeFlagsByProductCode(
                        $objProduct->getCode(),
                        $objProduct->getFlags()
                    )) return false;
                }
            } else {
                // Normal, non-virtual ShopCategory.
                // Remove all Products having the same Product code.
                if (!Products::deleteByCode(
                    $objProduct->getCode(),
                    $flagDeleteImages)
                ) return false;
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
     * @static
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function deleteByCode($productCode, $flagDeleteImages)
    {
        if (empty($productCode)) return false;
        $arrProduct = Products::getByCustomId($productCode);
        if ($arrProduct === false) return false;
        $result = true;
        foreach ($arrProduct as $objProduct) {
            if (!$objProduct->delete($flagDeleteImages)) $result = false;
        }
        return $result;
    }


    /**
     * Returns an array of Product IDs contained by the given
     * ShopCategory ID.
     * @param   integer     $catId      The ShopCategory ID
     * @return  mixed                   The array of Product IDs on success,
     *                                  false otherwise.
     * @static
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function getIdArrayByShopCategory($catId)
    {
        global $objDatabase;

        $query = "
            SELECT id
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
             WHERE catid=$catId
          ORDER BY sort_order ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $arrProductId = array();
        while (!$objResult->EOF) {
            $arrProductId[] = $objResult->fields['id'];
            $objResult->MoveNext();
        }
        return $arrProductId;
    }


    /**
     * Returns the first matching picture name found in the Products
     * within the Shop Category given by its ID.
     * @return  string                      The image name, or the
     *                                      empty string.
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @todo    Apply the order setting!
     */
    static function getPictureByCategoryId($catId)
    {
        global $objDatabase;

        $query = "
            SELECT picture
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
             WHERE catid=$catId
               AND picture!=''
          ORDER BY sort_order ASC
        ";
        $objResult = $objDatabase->SelectLimit($query, 1);
        if ($objResult && $objResult->RecordCount() > 0) {
            // Got a picture
            $arrImages = Products::getShopImagesFromBase64String(
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
     * @param   string  $strName    The name of the flag to match
     * @return  mixed               The array of ShopCategory IDs on success,
     *                              false otherwise.
     * @static
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function getShopCategoryIdArrayByFlag($strName)
    {
        global $objDatabase;

        $query = "
            SELECT DISTINCT catId
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
             WHERE flags LIKE '%$strName%'
          ORDER BY catId ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
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
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function makeThumbnailsById($arrId)
    {
        global $_ARRAYLANG;

        require_once ASCMS_FRAMEWORK_PATH.'/Image.class.php';

        if (!is_array($arrId))
            //$this->addMessage("Keine Produkt IDs zum erstellen der Thumbnails vorhanden ($id).");
            return false;

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
            $thumb_name = ImageManager::getThumbnailFilename($imagePath);
            if (   file_exists($thumb_name)
                && filemtime($thumb_name) > filemtime($imagePath)) {
                //$this->addMessage("Hinweis: Thumbnail fuer Produkt ID '$id' existiert bereits");
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
     * Apply the flags to all Products matching the given Product code
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
     * @static
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function changeFlagsByProductCode($productCode, $strNewFlags)
    {
        if (empty($productCode)) return false;
        // Get all available flags.  These are represented by the names
        // of virtual root ShopCategories.
        $arrVirtual = ShopCategories::getVirtualCategoryNameArray();

        // Get the affected identical Products
        $arrProduct = Products::getByCustomId($productCode);
        // No way we can do anything useful without them.
        if (count($arrProduct) == 0) return false;

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
            if (!$objArticleCategory) continue;

            // Get parent (subgroup)
            $objSubGroupCategory =
                ShopCategory::getById($objArticleCategory->getParentId());
            // This should not happen!
            if (!$objSubGroupCategory) continue;
            $subgroupName = $objSubGroupCategory->getName();

            // Get grandparent (group, root ShopCategory)
            $objRootCategory =
                ShopCategory::getById($objSubGroupCategory->getParentId());
            if (!$objRootCategory) continue;

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
            if (preg_match("/$strFlag/", $strNewFlags))
                // The flag is still there, don't bother.
                continue;

            // Also check whether this is a virtual root ShopCategory.
            if (in_array($strFlag, $arrVirtual)) {
                // It is one of the virtual roots, and the flag is missing.
                // So the Article has to be removed from this group.
                $objArticleCategory->delete();
                $objArticleCategory = false;
                // And if the subgroup happens to contain no more
                // "Article", delete it as well.
                $arrChildren = $objSubGroupCategory->getChildrenIdArray();
                if (count($arrChildren) == 0)
                    $objSubGroupCategory->delete();
                continue;
            }

            // Here, the virtual ShopCategory groups have been processed,
            // the only ones left are the "normal" ShopCategories.
            // Clone one of the Article ShopCategories for each of the
            // new flags set.
            // Already did that?
            if ($flagCloned) continue;

            // Find out what flags have been added.
            foreach ($arrVirtual as $strFlag) {
                // That flag is not present in the new flag set.
                if (!preg_match("/$strFlag/", $strNewFlags)) continue;
                // But it has been before.  The respective branch has
                // been truncated above already.
                if (preg_match("/$strFlag/", $strOldFlags)) continue;

                // That is a new flag for which we have to clone the Article.
                // Get the affected grandparent (group, root ShopCategory)
                $objTargetRootCategory =
                    ShopCategories::getChildNamed(0, $strFlag, false);
                if (!$objTargetRootCategory) continue;
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


    /**
     * Returns an array of image names, widths and heights from
     * the base64 encoded string taken from the database
     *
     * The array returned looks like
     *  array(
     *    1 => array(
     *      'img' => <image1>,
     *      'width' => <image1.width>,
     *      'height' => <image1.height>
     *    ),
     *    2 => array( ... ), // The same as above, three times in total
     *    3 => array( ... ),
     * )
     * @param   string  $base64Str  The base64 encoded image string
     * @return  array               The decoded image array
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getShopImagesFromBase64String($base64Str)
    {
        // Pre-init array to avoid "undefined index" notices
        $arrPictures = array(
            1 => array('img' => '', 'width' => 0, 'height' => 0),
            2 => array('img' => '', 'width' => 0, 'height' => 0),
            3 => array('img' => '', 'width' => 0, 'height' => 0)
        );
        if (strpos($base64Str, ':') === false)
            // have to return an array with the desired number of elements
            // and an empty file name in order to show the "dummy" picture(s)
            return $arrPictures;
        $i = 0;
        foreach (explode(':', $base64Str) as $imageData) {
            list($shopImage, $shopImage_width, $shopImage_height) = explode('?', $imageData);
            $shopImage        = base64_decode($shopImage);
            $shopImage_width  = base64_decode($shopImage_width);
            $shopImage_height = base64_decode($shopImage_height);
            $arrPictures[++$i] = array(
                'img'    => $shopImage,
                'width'  => $shopImage_width,
                'height' => $shopImage_height,
            );
        }
        return $arrPictures;
    }


    /**
     * Returns HTML code for dropdown menu options to choose the default
     * view on the Shop starting page.
     *
     * Possible choices are defined by global constants
     * SHOP_PRODUCT_DEFAULT_VIEW_* and corresponding language variables.
     * @static
     * @param   integer   $selected     The optional preselected view index
     * @return  string                  The HTML menu options
     */
    static function getDefaultViewMenuoptions($selected='')
    {
        global $_ARRAYLANG;

        $strMenuoptions = '';
        for ($i = 0; $i < SHOP_PRODUCT_DEFAULT_VIEW_COUNT; ++$i) {
            $strMenuoptions .=
                "<option value='$i'".
                ($selected == $i ? ' selected="selected"' : '').'>'.
                $_ARRAYLANG['TXT_SHOP_PRODUCT_DEFAULT_VIEW_'.$i].
                "</option>\n";
        }
        return $strMenuoptions;
    }

}

?>
