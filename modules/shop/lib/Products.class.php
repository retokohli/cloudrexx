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

define('PRODUCT_IMAGE_PATH',        ASCMS_SHOP_IMAGES_PATH.'/');
define('PRODUCT_IMAGE_WEB_PATH',    ASCMS_SHOP_IMAGES_WEB_PATH.'/');

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
class Products
{
    /**
     * All Product table field names
     *
     * Used to verify and create wildcard queries.
     * See {@link getWildcardQuery()}
     * @var array   $arrFieldName
     */
    var $arrFieldName = array(
        'id', 'product_id', 'picture', 'title', 'catid', 'handler',
        'normalprice', 'resellerprice', 'shortdesc', 'description',
        'stock', 'stock_visibility', 'discountprice', 'is_special_offer',
        'property1', 'property2', 'status', 'b2b', 'b2c',
        'startdate', 'enddate',
        'thumbnail_percent', 'thumbnail_quality',
        'manufacturer', 'external_link',
        'sort_order', 'vat_id', 'weight', 'flags'
    );

    /**
     * Default picture name
     * @static
     * @var     string
     */
    //static
    var $defaultThumbnail = "no_picture.gif";

    /**
     * Create a Products helper object (PHP4)
     * @access  public
     * @return  Products                The helper
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function Products()
    {
        $this->__construct();
    }

    /**
     * Create a Products helper object (PHP5)
     * @return  Products                The helper
     * @copyright   CONTREXX CMS - COMVATION AG
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
     * @global      mixed       $objDatabase    Database object
     * @copyright   CONTREXX CMS - COMVATION AG
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
                        SELECT id FROM ".DBPREFIX."module_shop_products
                        WHERE $fieldName LIKE '%".
                        addslashes($pattern)."%'";
        	    }
        	} else {
//echo("Product::getWildcardQuery(): WARNING: Unknown field name '$fieldName' ignored!<br />");
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
     * @global      mixed       $objDatabase    Database object
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getByWildcard($arrPattern)
    {
        global $objDatabase;

        $query = $this->getWildcardQuery($arrPattern);
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


    function getByCustomId($customId=0)
    {
        global $objDatabase;

        if (!$customId) {
            // No way.
            return false;
        }
        $query = "
            SELECT id FROM ".DBPREFIX."module_shop_products
             WHERE product_id=$customId
          ORDER BY id ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
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
     * @static
     * @param       integer     $productId      The Product ID
     * @param       integer     $shopCategoryId The ShopCategory ID
     * @param       integer     $manufacturerId The Manufacturer ID
     * @param       string      $pattern        A search pattern
     * @param       integer     $offset         The paging offset
     * @param       boolean     $lastFive       Flag for the last five Products
     *                                          added to the Shop
     * @return      array                       Array of Product objects,
     *                                          or false if none were found
     * @global      mixed       $objDatabase    Database object
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getByShopParams(
        $productId=0, $shopCategoryId=0, $manufacturerId=0,
        $pattern='', $offset=0, $lastFive=0
    ) {
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
              ORDER BY product_id DESC
            ";
            $objResult = $objDatabase->SelectLimit($query, 5);
            if (!$objResult) {
                return false;
            }
            $arrProduct = array();
            while (!$objResult->EOF) {
                $arrProduct[] = Product::getById($objResult->Fields('id'));
                $objResult->MoveNext();
            }
            return $arrProduct;
        }
        // Standard full featured query
        $q_search         = '';
        $q_special_offer  = 'AND (is_special_offer = 1)';
        $q2_category      = '';

        if ($shopCategoryId > 0) {
            // Select Products by ShopCategory ID
            $q_special_offer = '';
            $q2_category = "AND flags LIKE '%parent:$shopCategoryId%'";
        }
        if ($manufacturerId > 0) {
            // Select Products by Manufacturer ID
            $q_special_offer = '';
            $q_manufacturer = "AND manufacturer=$manufacturerId";
        }
        if (!empty($pattern)) {
            // select Products by search pattern
            $q_special_offer = '';
            $q_search = "
                AND (  title LIKE '%$pattern%'
                    OR description LIKE '%$pattern%'
                    OR shortdesc LIKE '%$pattern%'
                    OR product_id LIKE '%$pattern%')
                    OR id LIKE '%$pattern%')
            ";
        }
        $query = "
            SELECT id FROM ".DBPREFIX."module_shop_products AS p
             WHERE status=1
                   $q_special_offer
                   $q2_category
                   $q_manufacturer
                   $q_search
          ORDER BY sort_order ASC, id DESC
        ";
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
        $arrProduct = array();
        while (!$objResult->EOF) {
            $arrProduct[] = Product::getById($objResult->Fields('id'));
            $objResult->MoveNext();
        }
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
     * @global      mixed       $objDatabase    Database object
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function deleteByShopCategory($catId, $flagDeleteImages=false)
    {
//echo("Debug: Product::deleteByShopCategory(): catId $catId<br />");
        $arrProductId = Products::getIdArrayByShopCategory($catId);
//echo("Debug: Product::deleteByShopCategory(): catId: $catId, arrProducts: ");var_export($arrProductId);echo("<br />");
        if (!is_array($arrProductId)) {
            return false;
        }
        foreach ($arrProductId as $productId) {
            $objProduct = Product::getById($productId);
            if (!$objProduct) {
//echo("Product::deleteByShopCategory(): Error: Failed to get Product with ID $productId!<br />");
                return false;
            }
            if (!$objProduct->delete($flagDeleteImages)) {
//echo("Product::deleteByShopCategory(): Error: Failed to delete Product with ID $productId<br />");
                return false;
            }
//echo("Debug: Product::deleteByShopCategory(): Deleted ");var_export($objProduct);echo("<br />");
        }
        return true;
    }


    /**
     * Returns an array of Product IDs contained by the given
     * ShopCategory ID.
     * @static
     * @param   integer     $catId      The ShopCategory ID
     * @return  mixed                   The array of Product IDs on success,
     *                                  false otherwise.
     */
    //static
    function getIdArrayByShopCategory($catId)
    {
        global $objDatabase;

        $query = "
            SELECT id
              FROM ".DBPREFIX."module_shop_products
             WHERE catid=$catId
          ORDER BY sort_order ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
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
     */
    //static
    function getByShopCategory($catId)
    {
        global $objDatabase;

        $query = "
            SELECT id
              FROM ".DBPREFIX."module_shop_products
             WHERE catid=$catId
          ORDER BY sort_order ASC
        ";
        $objResult = $objDatabase->Execute($query);
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


    /**
     * Returns the first matching picture name found in the Products
     * within the Shop Category given by its ID.
     * @static
     * @return      string                      The image name, or the
     *                                          empty string.
     * @global      mixed       $objDatabase    Database object
     */
    //static
    function getPictureByCategoryId($catId)
    {
        global $objDatabase;

        $query = "
            SELECT picture
              FROM ".DBPREFIX."module_shop_products
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
     */
    //static
    function getShopCategoryIdArrayByFlag($strName)
    {
        global $objDatabase;

        $query = "
            SELECT DISTINCT catId
              FROM ".DBPREFIX."module_shop_products
             WHERE flags LIKE '%$strName%'
          ORDER BY catId ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
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
     * @global  mixed       $objDatabase    Database object
     */
    function makeThumbnailsById($arrId)
    {
        global $objDatabase;
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
                    "Ungültige Produkt ID '$id'!";
                continue;
            }
            $objProduct = Product::getById($id);
            if (!$objProduct) {
                $strError .= ($strError ? '<br />' : '').
                    "Ungültige Produkt ID '$id' - Konnte Produkt nicht finden!";
                continue;
            }
            $imageName = $objProduct->getPictures();
            $imagePath = PRODUCT_IMAGE_PATH.'/'.$imageName;
            // only try to create thumbs from entries that contain a
            // plain text file name (i.e. from an import)
            if (   $imageName == ''
                || !preg_match('/\.(?:jpg|jpeg|gif|png)$/', $imageName)) {
                $strError .= ($strError ? '<br />' : '').
                    "Nicht unterstütztes Bildformat: '$imageName' (Produkt ID $id)!";
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
//echo("makeProductThumbnailsById(): got image size: $width/$height<br />");
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
                "Fehlende Bilder (Produkt ID - Bildname): ".
                join(', ', array_keys($arrMissingProductPicture));
        }
        if (count($arrFailedCreatingThumb)) {
            sort($arrFailedCreatingThumb);
            $strError .= ($strError ? '<br />' : '').
                "Fehler beim erzeugen des Thumbnails bei Produkt ID: ".
                join(', ', $arrFailedCreatingThumb);
        }
        return $strError;
    }


// eiselin
    /**
     * Change the flags of all Products and their parent Artikel ShopCategories
     * with the same product_id.
     *
     * The imported data contains several instances of the same Product.
     * By design of the import, identical Products have the same
     * product_id.  This allows us to identify them and change the flags of
     * all identical Products in one go.
     * Also note that their immediate parent ShopCategories (aka "Artikel")
     * are identical as well, and *MUST* contain the same Products (originating
     * from the same "Detail" entries in the imported XML file).  They carry
     * the same flags and *MUST* be updated along with all of the contained
     * Products.
     * Thus, all Products within the same "Artikel" ShopCategory carry the
     * same flags as the containing ShopCategory itself.
     * @param   integer     $productId  The custom Product ID
     * @param   string      $strFlags   The new flags for the Product
     */
    function changeFlagsByProductCode($customId, $strNewFlags)
    {
        // Get all available flags.  These are represented by the names
        // of virtual root ShopCategories.
        $arrVirtual = ShopCategories::getVirtualCategoryNameArray();
        // The array contains the names as well as the IDs of the groups.
        // Create a new array with the names only.
        $arrGroup = array();
        foreach ($arrVirtual as $arr) {
        	$arrGroup[] = $arr['name'];
        }
//var_export($arrGroup);die();

        // Get the affected identical Products
        $arrProduct = Products::getByCustomId($customId);
        if (!is_array($arrProduct)) {
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): ERROR: Could not get Products by Code $customId!<br />");
            // No way we can do anything useful without them.
            return false;
        }
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Got Products: ");
foreach ($arrProduct as $objProduct) {
    echo("'".$objProduct->getName()."', ");
}
//echo("<br />");

        // Get the Product flags.  As they're all the same, we'll use the
        // first one here.
        // Note that this object is used for reference only and is never stored.
        // Its database entry will be updated along the way, however.
        $_objProduct = $arrProduct[0];
        $strOldFlags = $_objProduct->getFlags();
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Old flags: $strOldFlags.<br />");
        // Flag indicating whether the Artikel has been cloned already
        // for all new flags set.
        $flagCloned = false;

        // Now apply the changes to all those identical Products, their parent
        // ShopCategories, and all sibling Products within them.
        foreach ($arrProduct as $objProduct) {
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Processing Product ID ".$objProduct->getId().".<br />");
            // Get the containing "Artikel" ShopCategory.
        	$catId = $objProduct->getShopCategoryId();
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Product is in ShopCategory $catId.<br />");
            $objArtikelCategory = ShopCategory::getById($catId);
            if (!$objArtikelCategory) {
                // This should not happen!
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): ERROR: Artikel ShopCategory got missing!<br />");
                continue;
            }

            // Get parent (subgroup)
            $objSubGroupCategory =
                ShopCategory::getById($objArtikelCategory->getParentId());
            if (!$objSubGroupCategory) {
                // This should not happen!
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): ERROR: Subgroup ShopCategory got missing!<br />");
                continue;
            }
            $subgroupName = $objSubGroupCategory->getName();
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Got subgroup ShopCategory ID ".$objSubGroupCategory->getId().", name '$subgroupName'.<br />");

            // Get grandparent (group, root ShopCategory)
            $objRootCategory =
                ShopCategory::getById($objSubGroupCategory->getParentId());
            if (!$objRootCategory) {
                // This should not happen!
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): ERROR: Root ShopCategory got missing!<br />");
                continue;
            }
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Got root/group ShopCategory ID ".$objRootCategory->getId().".<br />");

            // Apply the new flags to all Products and Artikel ShopCategories.
            // Update the flags of the original "Artikel" ShopCategory first
            $objArtikelCategory->setFlags($strNewFlags);
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Set flags '$strNewFlags' to Artikel ShopCategory -> ".$objArtikelCategory->getFlags()."<br />");
            $objArtikelCategory->store();
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Updated Artikel ShopCategory ID $catId.<br />");

            // Get all sibling Products affected by the same flags
            $arrSiblingProducts = Products::getByShopCategory(
                $objArtikelCategory->getId()
            );
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Got all siblings: ");
foreach ($arrSiblingProducts as $objProduct) {
    echo("'".$objProduct->getName()."', ");
}
//echo("<br />");

            // Set the new flag set for all Products within the Artikel
            // ShopCategory.
            foreach ($arrSiblingProducts as $objProduct) {
                $objProduct->setFlags($strNewFlags);
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Set flags '$strNewFlags' to Product ID ".$objProduct->getId()."-> ".$objProduct->getFlags()."<br />");
                $objProduct->store();
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Updated Product ID ".$objProduct->getId()."<br />");
            }

            // Check whether this group is affected by the changes.
            // If its name matches one of the flags, the Artikel and subgroup
            // may have to be removed.
            $strFlag = $objRootCategory->getName();
            if (preg_match("/$strFlag/", $strNewFlags)) {
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Flag '$strFlag' still in new Flags $strNewFlags. Skipped.<br />");
                // The flag is still there, don't bother.
                continue;
            }

            // Also check whether this is a virtual root ShopCategory.
            if (in_array($strFlag, $arrGroup)) {
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Root ShopCategory name matches flag '$strFlag'.<br />");
                // It is one of the virtual roots, and the flag is missing.
                // So the The Artikel has to be removed from this group.
                $objArtikelCategory->delete();
                $objArtikelCategory = false;
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Deleted Artikel ShopCategory ID $catId.<br />");
                // And if the subgroup happens to contain no more
                // "Artikel", delete it as well.
                $arrChildren = $objSubGroupCategory->getChildrenIdArray();
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Subgroup ShopCategory ID ".$objSubGroupCategory->getId()." contains ".count($arrChildren)." Artikel ShopCategories.<br />");
                if (count($arrChildren) == 0) {
                    $objSubGroupCategory->delete();
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Subgroup ShopCategory deleted as well.<br />");
                }
                continue;
            }

            // Here, the virtual ShopCategory groups have been processed,
            // the only ones left are the "normal" ShopCategories.
            // Clone one of the Artikel ShopCategories for each of the
            // new flags set.
            if ($flagCloned) {
                // Already did that.
                continue;
            }

            // Find out what flags have been added.
            foreach ($arrGroup as $strFlag) {
                if (!preg_match("/$strFlag/", $strNewFlags)) {
                    // That flag is not present in the new flag set.
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Flag $strFlag is not present in the new flag set $strNewFlags.<br />");
                    continue;
                }
                if (preg_match("/$strFlag/", $strOldFlags)) {
                    // But it has been before.  The respective branch has
                    // been truncated above already.
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Flag $strFlag is present in the old flag set $strOldFlags.<br />");
                    continue;
                }
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Flag $strFlag is new.<br />");

                // That is a new flag for which we have to clone the Artikel.
                // Get the affected grandparent (group, root ShopCategory)
                $objTargetRootCategory =
                    ShopCategories::getChildNamed(0, $strFlag, false);
                if (!$objTargetRootCategory) {
                    // This should not happen!
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): ERROR: Root ShopCategory got missing!<br />");
                    continue;
                }
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Got root/group ShopCategory ID ".$objRootCategory->getId().".<br />");
                // Check whether the subgroup exists already
                $objTargetSubGroupCategory =
                    ShopCategories::getChildNamed(
                        $objTargetRootCategory->getId(), $subgroupName, false
                    );
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Got subgroup ShopCategory ID ".$objSubGroupCategory->getId().".<br />");
                if (!$objTargetSubGroupCategory) {
                    // Nope, add the subgroup.
                    $objSubGroupCategory->makeClone();
                    $objSubGroupCategory->setParentId($objTargetRootCategory->getId());
                    $objSubGroupCategory->store();
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Cloned missing subgroup ShopCategory named $subgroupName.<br />");
                    $objTargetSubGroupCategory = $objSubGroupCategory;
                }

                // Check whether the Artikel ShopCategory exists already
                $objTargetArtikelCategory =
                    ShopCategories::getChildNamed(
                        $objTargetSubGroupCategory->getId(),
                        $objArtikelCategory->getName(),
                        false
                    );
                if ($objTargetArtikelCategory) {
                    // The Artikel Category already exists.
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Artikel ShopCategory exists in subgroup already<br />");
                } else {
                    // Nope, clone the "Artikel" ShopCategory and add it to the
                    // subgroup.  Note that the flags have been set already
                    // and don't need to be changed again here.
                    // Also note that the cloning process includes all content
                    // of the Artikel ShopCategory, but the flags will remain
                    // unchanged. That's why the flags have already been
                    // changed right at the beginning of the process.
                    $objArtikelCategory->makeClone(true, true);
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Cloned Artikel ShopCategory ID $catId -> ".$objArtikelCategory->getId().".<br />");
                    $objArtikelCategory->setParentId($objTargetSubGroupCategory->getId());
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Changed ShopCategory parent ID ".$objArtikelCategory->getParentId().".<br />");
                    $objArtikelCategory->store();
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Updated Artikel ShopCategory ID ".$objArtikelCategory->getId().".<br />");
                    $objTargetArtikelCategory = $objArtikelCategory;
                }
            } // foreach $arrGroup
        } // foreach $arrProduct
        // And we're done!
        return true;
    }
}

/*
            // Find flags that have been removed in the new flag set
            foreach ($arrVirtual as $arrName) {
                $strFlag = trim($arrName['name']);
                if ($strFlag == '') {
                    continue;
                }
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Processing flag '$strFlag'.<br />");

                // Make sure the Artikel ShopCategory is still valid
                if (!$objArtikelCategory) {
                    continue;
                }
                // The parent of the "Artikel" ShopCategory (subgroup) may also
                // be repeated in one or more of the "virtual" root
                // ShopCategories.
                // We have to test whether this one is contained by one of
                // those, and remove it.

                // Now if the name of the root ShopCategory is the same as
                // the flag just removed above, this "Artikel" along with
                // all contained Products will vanish.
                if ($objRootCategory->getName() == $strFlag) {
                } else {
                    // The "Artikel" ShopCategory is still here.
                    // Update its flags.
                    $objArtikelCategory->removeFlag($strFlag);
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Removed flag '$strFlag' from Artikel ShopCategory -> ".$objArtikelCategory->getFlags()."<br />");
                    $objArtikelCategory->store();
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Updated Artikel ShopCategory $catId.<br />");
                    // Also update the Products
                    foreach ($arrSiblingProducts as $objProduct) {
                    	$objProduct->removeFlag($strFlag);
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Removed flag '$strFlag' from Product -> ".$objProduct->getFlags()."<br />");
                    	$objProduct->store();
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Updated contained Products in Artikel ShopCategory $catId.<br />");
                    }
                }
            }
        } // $arrProducts

        // We're not so sure now about which Prodcuts and ShopCategories are
        // still alive.  Thus, all the data is refreshed and changes applied
        // to the survivors of the first part only.
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Adding flags now.<br />");

        // Get the affected identical Products
        $arrProduct = Products::getByCustomId($customId);
        if (!is_array($arrProduct)) {
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): ERROR: Could not get Products by Code $customId!<br />");
            // No way we can do anything useful without them.
            return false;
        }

        // Now apply the changes to all their sibling Products and their parent
        // ShopCategories.
        foreach ($arrProduct as $objProduct) {
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Processing Product ID ".$objProduct->getId().".<br />");
            // Get the containing "Artikel" ShopCategory.
        	$catId = $objProduct->getShopCategoryId();
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Product is in ShopCategory $catId.<br />");
            $objArtikelCategory = ShopCategory::getById($catId);

            // Find flags that have been added
            foreach (split(' ', $strNewFlags) as $strFlag) {
                $strFlag = trim($strFlag);
                if ($strFlag == '') {
                    continue;
                }
                // Note that we use the reference Product here.
                if ($_objProduct->testFlag($strFlag)) {
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Reference Product is already present in group '$strFlag'.<br />");
                    // Already present. Next flag.
                    continue;
                }

                // Otherwise, we have to add the flag to this Product,
                // its siblings and parent, and add one clone of each,
                // maybe even the subgroup, to that root ShopCategory.

                // We'll get along by getting the parent subgroup ShopCategory
                // name, and try to find that in the affected root.
                $objSubGroupCategory =
                    ShopCategory::getById($objArtikelCategory->getParentId());
//echo("Products::changeFlagsByProductCode($customId, $strNewFlags): INFO: Got subgroup ShopCategory ID ".$objSubGroupCategory->getId().".<br />");
                $subgroupName = $objSubGroupCategory->getName();
*/

/*
// TEST
$objProduct = new Product('xyz', 1, 'test', '', 1.00, 1, 1, 1);
$objProduct->setFlags('FLAG');
$objProduct->store();
//var_export($objProduct);echo("<br />");
$id = $objProduct->getId();
Product::getById($id);
//var_export($objProduct);echo("<br />");
die();
*/

?>
