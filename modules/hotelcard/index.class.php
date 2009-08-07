<?php

define('_HOTELCARD_DEBUG', 0);

/**
 * Class Hotelcard
 *
 * Frontend for the Hotelcard module
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.1.1
 * @package     contrexx
 * @subpackage  module_hotelcard
 * @uses        modules/hotelcard/lib/Config.class.php
 * @todo        Update the @uses
 */

/** @ignore */
require_once ASCMS_CORE_PATH.'/Filetype.class.php';
require_once ASCMS_CORE_PATH.'/Imagetype.class.php';
require_once ASCMS_CORE_PATH.'/Image.class.php';
require_once ASCMS_CORE_PATH.'/Sorting.class.php';
require_once ASCMS_CORE_PATH.'/Text.class.php';
require_once ASCMS_FRAMEWORK_PATH.'/Language.class.php';
require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Config.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Designer.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Download.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Product.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Property.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Reference.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/RelProductCategory.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/RelProductProperty.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/RelProductReference.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/RelUserContact.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/constants.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/sorting.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Material.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Manufacturer.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Line.class.php';
//lib/FRAMEWORK/File.class.php

define('HOTELCARD_REFERENCE_VIEW_COUNT', 2);

/**
 * Class Hotelcard
 *
 * Frontend for the Hotelcard module.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.1.1
 * @package     contrexx
 * @subpackage  module_hotelcard
 */
class Hotelcard
{
    /**
     * Status / error message
     * @var string
     */
    private $statusMessage;
    /**
     * Page template
     * @var HTML_Template_Sigma
     */
    private $objTemplate;
    /**
     * Configuration
     * @var   Config
     */
    private $objConfig;
    /**
     * Page Title
     *
     * Only used by index.php if its not the empty string
     * @var   string
     */
    private $page_title = '';


    /**
     * Constructor
     * @param   string    $pageContent      The page content template
     * @global  integer   $_LANGID          The frontend language ID
     * @access  public
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function Hotelcard($pageContent)
    {
        self::$this->pageContent = $pageContent;
        // PEAR Sigma template
        $this->objTemplate = new HTML_Template_Sigma('.');
        $this->objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $this->objTemplate->setTemplate($this->pageContent, true, true);

        // Initialize here
        $this->objConfig = new Config();

    }


    /**
     * Determine the page to be shown and call appropriate methods.
     * @return  string            The finished HTML page content
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getPage()
    {
        if (isset($_GET['cmd'])) {
            $_GET['act'] = $_GET['cmd'];
        }
        if (!isset($_GET['act'])) {
            $_GET['act'] = '';
        }

        // Flag for error handling
        $result = true;

        switch($_GET['act']) {
            case 'products':
                $result &= $this->showProducts();
                break;
            case 'product':
                $result &= $this->showProduct();
                break;
            case 'designers':
                $result &= $this->showDesigners();
                break;
            case 'designer':
                $result &= $this->showDesigner();
                break;
            case 'references':
                $result &= $this->showReferences();
                break;
            case 'reference':
                $result &= $this->showReference();
                break;
            case 'contacts':
                $result &= $this->showContacts();
                break;
// Added 20081014
            case 'materials':
                $result &= $this->showMaterials();
                break;
            case 'overview':
            default:
                $result &= $this->showOverview();
        }
        $result &= ($this->statusMessage != '');
        if (!$result) {
            self::errorHandler();
            $this->addMessage('An error has occurred.  Please try reloading the page.');
        }
        $this->objTemplate->setVariable('HOTELCARD_STATUS', htmlspecialchars($this->statusMessage, ENT_QUOTES, CONTREXX_CHARSET));
        return $this->objTemplate->get();

    }


    /**
     * Set up the home page with a list of all Categories
     * and contained Products.
     * @return    boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function showOverview()
    {
        global $_LANGID;

        $arrCategories = Category::getArrayByLanguageId($_LANGID);
        if (!is_array($arrCategories)) {
            return false;
        }

        // dont list 'Neuheiten' (ID=5)! remove it from array
        for ($i=0; $i < count($arrCategories); $i++) {
            $catId = $arrCategories[$i]['category_id'];
            //echo("catid: $catId i: $i<br>");
            if ( $catId == 5 ) {
                array_splice($arrCategories, $i, 1);
            }
        }

        // Count Products, start a new column for each new Category or after
        // a fixed number of Products.
        $limitProductsInColumn = 11;
        foreach ($arrCategories as $arrCategory) {
            $categoryId = $arrCategory['category_id'];
            // List all products within that category
            $arrProducts = Product::getArrayByCategoryId($categoryId, $_LANGID);
            if (!is_array($arrProducts)) return false;
            // Skip empty categories.
            if (count($arrProducts) == 0) continue;

            // List the Category itself
            $this->objTemplate->setCurrentBlock('category');
            $this->objTemplate->setVariable(array(
                'HOTELCARD_CATEGORY_ID' => $categoryId,
                'HOTELCARD_CATEGORY_NAME' => htmlspecialchars($arrCategory['name'], ENT_QUOTES, CONTREXX_CHARSET),
                'HOTELCARD_CATEGORY_DESC' => htmlspecialchars($arrCategory['desc'], ENT_QUOTES, CONTREXX_CHARSET),
            ));

            // Sort Products by name for this view *only*
            usort($arrProducts, 'cmp_name');
            // Number of Products in the current column
            $numofProductsInColumn = 0;
            $this->objTemplate->setCurrentBlock('product');
            foreach ($arrProducts as $arrProduct) {

                // check if this product is new (catId=5) KEVIN
                $newProductLink = Product::getIsNewProductLink($arrProduct['product_id'], 5);

                $this->objTemplate->setVariable(array(
                    'HOTELCARD_PRODUCT_ID' => $arrProduct['product_id'],
                    'HOTELCARD_PRODUCT_NAME' => (htmlspecialchars($arrProduct['name'], ENT_QUOTES, CONTREXX_CHARSET).$newProductLink),
                    'HOTELCARD_PRODUCT_DESC' => htmlspecialchars($arrProduct['desc'], ENT_QUOTES, CONTREXX_CHARSET),
                ));
                $this->objTemplate->parseCurrentBlock();
                $numofProductsInColumn = ++$numofProductsInColumn % $limitProductsInColumn;
                if ($numofProductsInColumn == $limitProductsInColumn - 1) {
                    // Column full, start a fresh one.
                    $this->objTemplate->parse('category');
                    // Insert an empty row in the next column
                    $this->objTemplate->touchBlock('productempty');
                }
            }
            // The category block may be parsed a second time here
            // after just completing a column above.  This won't bother us
            // because no placeholder is set and the parsing results in
            // an empty block (aka nothing).
            $this->objTemplate->parse('category');
        }
        return true;
    }


    /**
     * Set up the Products page with a list of all Categories
     * and contained Products with Images.
     * @return    boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function showProducts()
    {
        global $_LANGID;

        $categoryId = (isset($_GET['category_id']) ? $_GET['category_id'] : false);

        $arrCategories = Category::getArrayByLanguageId($_LANGID);
        foreach ($arrCategories as $arrCategory) {
            $categoryStyle = 'inactive';
            $arrProducts = Product::getArrayByCategoryId($arrCategory['category_id'], $_LANGID);
            if (count($arrProducts) == 0) {
                continue;
            }
            if (!$categoryId || $categoryId == $arrCategory['category_id']) {
                if ($categoryId == $arrCategory['category_id']) {
                    $categoryStyle = 'active';
                }
                // List all products within that category
                $this->objTemplate->setCurrentBlock('product');
                foreach ($arrProducts as $arrProduct) {
                    $this->objTemplate->setVariable(array(
                        'HOTELCARD_PRODUCT_ID' => $arrProduct['product_id'],
                        'HOTELCARD_PRODUCT_NAME' => htmlspecialchars($arrProduct['name'], ENT_QUOTES, CONTREXX_CHARSET),
                        'HOTELCARD_PRODUCT_DESC' => htmlspecialchars($arrProduct['desc'], ENT_QUOTES, CONTREXX_CHARSET),
                        'HOTELCARD_PRODUCT_IMAGE' => $arrProduct['images'][HOTELCARD_ORD_IMAGE_PRODUCT_OVERVIEW]['path'],
                    ));
                    $this->objTemplate->parseCurrentBlock();
                }
                $this->objTemplate->setCurrentBlock('category');
                $this->objTemplate->setVariable(array(
                    'HOTELCARD_CATEGORY_NAME' => htmlspecialchars($arrCategory['name'], ENT_QUOTES, CONTREXX_CHARSET),
                ));
                $this->objTemplate->parseCurrentBlock();
            }
            $this->objTemplate->setCurrentBlock('level_2');
            $this->objTemplate->setVariable(array(
                'HOTELCARD_CATEGORY_ID' => $arrCategory['category_id'],
                'HOTELCARD_CATEGORY_NAME' => htmlspecialchars($arrCategory['name'], ENT_QUOTES, CONTREXX_CHARSET),
                'HOTELCARD_NAVIGATION_STYLE' => $categoryStyle,
            ));
            $this->objTemplate->parseCurrentBlock();
        }
        return true;
    }


    /**
     * Set up the Product page with a single Product,
     * Product variants, Description and details
     * @return    boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function showProduct()
    {
        global $_ARRAYLANG, $_LANGID;

        $this->showCategoryNavigation();

        $product_id = (isset($_GET['product_id']) ? $_GET['product_id'] : 0);
        if (!$product_id) {
            return false;
        }

        $objProduct = Product::getById($product_id, $_LANGID);
        // Images of Product variants
        $ord = HOTELCARD_ORD_IMAGE_PRODUCT_VARIANT;
        $objImage = Image::getById($objProduct->getImageId(), $ord);
        $this->objTemplate->setCurrentBlock('variant');
        while ($objImage) {
            $name = '';
            $objText = Text::getById($objImage->getTextId(), $_LANGID);
            if ($objText) {
                $name = $objText->getName();
            }
            $this->objTemplate->setVariable(array(
                'HOTELCARD_PRODUCT_ID' => $objProduct->getId(),
                'HOTELCARD_PRODUCT_IMAGE' => $objImage->getPath(),
                'HOTELCARD_PRODUCT_IMAGE_NAME' => $name,
            ));
            $this->objTemplate->parseCurrentBlock();
            $objImage = Image::getById($objProduct->getImageId(), ++$ord);
        }

        // Next & Previous Product-Ids
        $arrLinNavi = Product::getPrevNextIds($product_id);

        // Product details
        $objTextProduct = Text::getById($objProduct->getTextId(), $_LANGID);
        $objTextExtension = Text::getById($objProduct->getExtensionId(), $_LANGID);
        $objImage = Image::getById($objProduct->getImageId(), HOTELCARD_ORD_IMAGE_PRODUCT_MAIN);
        $this->objTemplate->setVariable(array(
            'HOTELCARD_PRODUCT_ID' => $objProduct->getId(),
            'HOTELCARD_PRODUCT_NAME' => htmlspecialchars($objTextProduct->getName(), ENT_QUOTES, CONTREXX_CHARSET),
            'HOTELCARD_PRODUCT_DESC' => htmlspecialchars($objTextProduct->getDesc(), ENT_QUOTES, CONTREXX_CHARSET),
            'HOTELCARD_PRODUCT_EXTENSION' => htmlspecialchars($objTextExtension->getDesc(), ENT_QUOTES, CONTREXX_CHARSET),
            'HOTELCARD_PRODUCT_IMAGE' => $objImage->getPath(),
            'TXT_HOTELCARD_PRODUCT_EXTENSION' => $_ARRAYLANG['TXT_HOTELCARD_PRODUCT_EXTENSION'],
            'HOTELCARD_PRODUCT_LINEARNAVI_PREV_LINK' => $arrLinNavi['prevId'],
            'HOTELCARD_PRODUCT_LINEARNAVI_NEXT_LINK' => $arrLinNavi['nextId'],
            'HOTELCARD_PRODUCT_LINEARNAVI_PREV_TITLE' => $_ARRAYLANG['TXT_HOTELCARD_PRODUCT_LINEARNAVI_PREV_TITLE'],
            'HOTELCARD_PRODUCT_LINEARNAVI_NEXT_TITLE' => $_ARRAYLANG['TXT_HOTELCARD_PRODUCT_LINEARNAVI_NEXT_TITLE'],
        ));
        // Page title
        $this->page_title = $objTextProduct->getName();
        if ($objProduct->getYear()) {
            $this->objTemplate->setVariable(array(
                'TXT_HOTELCARD_PRODUCT_YEAR' => $_ARRAYLANG['TXT_HOTELCARD_PRODUCT_YEAR'],
                'HOTELCARD_PRODUCT_YEAR' => $objProduct->getYear(),
            ));
        }
        // Designer details
        $arrDesignerId = RelProductDesigner::getDesignerIdArrayByProductId($product_id);
        if (count($arrDesignerId) > 0) {
            $strDesignerId = join(',', $arrDesignerId);
            $arrDesigners = Designer::getArrayByLanguageId($_LANGID, $strDesignerId, true);
            $this->objTemplate->setCurrentBlock('designer');
            foreach ($arrDesigners as $arrDesigner) {
                $objTextDesigner = Text::getById($arrDesigner['text_id'], $_LANGID);
                $imagePath = $arrDesigner['images'][HOTELCARD_ORD_IMAGE_DESIGNER_OVERVIEW]['path'];
                $designerName = htmlspecialchars($objTextDesigner->getName(), ENT_QUOTES, CONTREXX_CHARSET);
                // only show visible!
                if ( $arrDesigner['visible'] == 1 ) {
                    $designerHtmlLink    = "<a href='index.php?section=hotelcard&amp;cmd=designer&amp;designer_id=".$arrDesigner['designer_id']."'>
                                            ".$designerName."
                                              </a>";
                }else{
                    $designerHtmlLink    = $designerName;
                }

                $this->objTemplate->setVariable(array(
                    'HOTELCARD_DESIGNER_ID' => $arrDesigner['designer_id'],
                    'HOTELCARD_DESIGNER_NAME' => $designerName,
                    'HOTELCARD_DESIGNER_DESC' => htmlspecialchars($objTextDesigner->getDesc(), ENT_QUOTES, CONTREXX_CHARSET),
                    'HOTELCARD_DESIGNER_IMAGE' => $imagePath,
                    'HOTELCARD_DESIGNER_LINK' => $designerHtmlLink,
                    'TXT_HOTELCARD_DESIGNER' => $_ARRAYLANG['TXT_HOTELCARD_DESIGNER'],
                ));
                $this->objTemplate->parse('designer');
            }
            // In block "txtDesigner", invisible if no designers are present.
            $this->objTemplate->setVariable(
                'TXT_HOTELCARD_DESIGNERS', $_ARRAYLANG['TXT_HOTELCARD_DESIGNERS']
            );
        }

        // References details
        $arrReferenceIdOrd = RelProductReference::getReferenceIdOrdArrayByProductId($product_id);
// TODO:  Is it correct to show invisible (inactive) References here as well?
        $arrReferences = Reference::getArrayByProductId($product_id, $_LANGID, false);
        if (count($arrReferenceIdOrd) > 0) {
            $this->objTemplate->setCurrentBlock('reference');
            foreach ($arrReferences as $arrReference) {
                $reference_id = $arrReference['reference_id'];
                foreach ($arrReferenceIdOrd[$reference_id] as $ord) {
                    $arrImage = Image::getArrayById($arrReference['image_id'], $ord);
                    $strName = '';
                    $objText = Text::getById($arrImage[$ord]['text_id']);
                    if ($objText) $strName = $objText->getName();
                    $this->objTemplate->setVariable(array(
                        'HOTELCARD_REFERENCE_ID' => $reference_id,
                        'HOTELCARD_REFERENCE_NAME' => htmlspecialchars($arrReference['name'], ENT_QUOTES, CONTREXX_CHARSET),
                        'HOTELCARD_REFERENCE_IMAGE' => $arrImage[$ord]['path'],
                        'HOTELCARD_REFERENCE_IMAGE_NAME' => htmlspecialchars($strName, ENT_QUOTES, CONTREXX_CHARSET),
                    ));
                    $this->objTemplate->parse('reference');
                }
                $this->objTemplate->parse('references');
            }
            // In block "txtReference", invisible if no references are present.
            $this->objTemplate->setVariable(
                'TXT_HOTELCARD_REFERENCES', $_ARRAYLANG['TXT_HOTELCARD_REFERENCES']
            );
        }

        // Downloads details
        $arrDownloads = Download::getArrayByLanguageId($_LANGID, $objProduct->getId(), 0);

        // Sort array by Download-Filename KEVIN
        foreach ($arrDownloads as $key => $row) {
            $arrTemp[$key]  = $row['name'];
        }
        array_multisort($arrTemp, SORT_ASC, $arrDownloads);

        if (count($arrDownloads) > 0) {
            $this->objTemplate->setCurrentBlock('download');
            foreach ($arrDownloads as $arrDownload) {
                $objTextDownload = Text::getById($arrDownload['text_id'], $_LANGID);
                $strFileExtension = strtoupper(File::getExtension($arrDownload['path']));
                $strFileSize = File::getSizeString(filesize($arrDownload['path']));

                // check extension: if zip, add target=_blank
                $strFileExtension == 'PDF' ? $strLinkTarget = "target='_blank'" : $strLinkTarget = ""; // KEVIN

                // check if language of downloadfile matches gui language: // KEVIN
                $downloadLangIdIsGuiLang = Download::checkDownloadLangIdWithGuiLang($arrDownload['language_id'], $_LANGID);
                if ($downloadLangIdIsGuiLang) {
                    $this->objTemplate->setVariable(array(
                        'HOTELCARD_DOWNLOAD_ID' => $arrDownload['download_id'],
                        'HOTELCARD_DOWNLOAD_NAME' => htmlspecialchars($objTextDownload->getName(), ENT_QUOTES, CONTREXX_CHARSET),
                        'HOTELCARD_DOWNLOAD_DESC' => htmlspecialchars($objTextDownload->getDesc(), ENT_QUOTES, CONTREXX_CHARSET),
                        'HOTELCARD_DOWNLOAD_TYPE' => $arrDownload['type'],
                        'HOTELCARD_DOWNLOAD_EXTENSION' => $strFileExtension,
                        'HOTELCARD_DOWNLOAD_PATH' => $arrDownload['path'],
                        'HOTELCARD_DOWNLOAD_SIZE' => $strFileSize,
                        'HOTELCARD_DOWNLOAD_LINKTARGET' => $strLinkTarget,
                    ));
                    $this->objTemplate->parseCurrentBlock();
                }
            }
            $this->objTemplate->setVariable(
                'TXT_HOTELCARD_DOWNLOADS', $_ARRAYLANG['TXT_HOTELCARD_DOWNLOADS']
            );
        }
        return true;
    }


    /**
     * Set up the page with a list of all Designers
     * @return    boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function showDesigners()
    {
        global $_ARRAYLANG, $_LANGID;

        $arrDesigners = Designer::getArrayByLanguageId($_LANGID);
        $this->objTemplate->setVariable(
            'TXT_HOTELCARD_DESIGNER', $_ARRAYLANG['TXT_HOTELCARD_DESIGNER']
        );
        $this->objTemplate->setCurrentBlock('designer');
        foreach ($arrDesigners as $arrDesigner) {
            // only show visible!
            if ( $arrDesigner['visible'] == 1 ) {
                $imagePath =
                    (isset($arrDesigner['images'][HOTELCARD_ORD_IMAGE_DESIGNER_OVERVIEW])
                        ? $arrDesigner['images'][HOTELCARD_ORD_IMAGE_DESIGNER_OVERVIEW]['path']
                        : Image::$default
                    );
                $this->objTemplate->setVariable(array(
                    'HOTELCARD_DESIGNER_ID' => $arrDesigner['designer_id'],
                    'HOTELCARD_DESIGNER_NAME' => htmlspecialchars($arrDesigner['name'], ENT_QUOTES, CONTREXX_CHARSET),
                    'HOTELCARD_DESIGNER_DESC' => htmlspecialchars($arrDesigner['desc'], ENT_QUOTES, CONTREXX_CHARSET),
                    'HOTELCARD_DESIGNER_IMAGE' => $imagePath,
                ));
                $this->objTemplate->parseCurrentBlock();
            }
        }
        return true;
    }


    /**
     * Set up the page with a single Designer and Categories with her Products
     * @return    boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function showDesigner()
    {
        global $_ARRAYLANG, $_LANGID;

        $designerId = (isset($_GET['designer_id']) ? $_GET['designer_id'] : 0);
        if (!$designerId) {
            return false;
        }

        // Collect all Designer details
        $objDesigner = Designer::getById($designerId, $_LANGID);
        $textId = $objDesigner->getTextId();
        $objText = Text::getById($textId, $_LANGID);
        if (!$objText) {
            $objText = new Text($_LANGID, $textId);
        }

        // Next & Previous Product-Ids
        $arrLinNavi = Designer::getPrevNextIds($designerId);

        // Designer details
        $objImage = Image::getById($objDesigner->getImageId(), HOTELCARD_ORD_IMAGE_DESIGNER_MAIN);
        $this->objTemplate->setVariable(array(
            'HOTELCARD_DESIGNER_ID' => $designerId,
            'HOTELCARD_DESIGNER_NAME' => htmlspecialchars($objText->getName(), ENT_QUOTES, CONTREXX_CHARSET),
            'HOTELCARD_DESIGNER_DESC' => htmlspecialchars($objText->getDesc(), ENT_QUOTES, CONTREXX_CHARSET),
            'HOTELCARD_DESIGNER_IMAGE' => $objImage->getPath(),
            'TXT_HOTELCARD_REFERENCES' => $_ARRAYLANG['TXT_HOTELCARD_REFERENCES'],
            'TXT_HOTELCARD_PRODUCTS' => $_ARRAYLANG['TXT_HOTELCARD_PRODUCTS'],
            'HOTELCARD_DESIGNER_LINEARNAVI_PREV_LINK' => $arrLinNavi['prevId'],
            'HOTELCARD_DESIGNER_LINEARNAVI_NEXT_LINK' => $arrLinNavi['nextId'],
            'HOTELCARD_DESIGNER_LINEARNAVI_PREV_TITLE' => $_ARRAYLANG['TXT_HOTELCARD_DESIGNER_LINEARNAVI_PREV_TITLE'],
            'HOTELCARD_DESIGNER_LINEARNAVI_NEXT_TITLE' => $_ARRAYLANG['TXT_HOTELCARD_DESIGNER_LINEARNAVI_NEXT_TITLE'],
        ));
        // Page title
        $this->page_title = $objText->getName();

        // Find Products associated to the Designer
        $arrProductIds = RelProductDesigner::getProductIdArrayByDesignerId($designerId);
        $arrProducts = array();
        $arrCategories = array();
        $arrReferences = array();
        if (!empty($arrProductIds)) {
            $strProductIds = implode(',', $arrProductIds);
            $arrProducts = Product::getArrayByLanguageId($_LANGID, true, $strProductIds);
            $strCategoryIds = implode(',', RelProductCategory::getCategoryIdArrayByProductId($strProductIds));
            $arrCategories = Category::getArrayByLanguageId($_LANGID, true, $strCategoryIds);
            $arrReferences = Reference::getArrayByLanguageId($_LANGID, true, $strProductIds);
        }

        // Dont list 'Neuheiten' (ID=5)! remove neuheiten-id from array KEVIN
        for ($i=0; $i < count($arrCategories); $i++) {
            $catId = $arrCategories[$i]['category_id'];
            //echo("catid: $catId i: $i<br>");
            if ( $catId == 5 ) {
                array_splice($arrCategories, $i, 1);
            }
        }


        // Categories and contained Products
        foreach ($arrCategories as $arrCategory) {
            $categoryId = $arrCategory['category_id'];
            $this->objTemplate->setCurrentBlock('product');
            foreach ($arrProducts as $arrProduct) {
                $arrProductIds = RelProductCategory::getProductIdArrayByCategoryId($categoryId);
                if (in_array($arrProduct['product_id'], $arrProductIds)) {

                    // check if this product is new (catId=5) KEVIN
                    $newProductLink = Product::getIsNewProductLink($arrProduct['product_id'], 5);
    //echo($arrProduct['name'].", PROD-LINK: ".$newProductLink."<br>");

                    $this->objTemplate->setVariable(array(
                        'HOTELCARD_PRODUCT_ID' => $arrProduct['product_id'],
                        'HOTELCARD_PRODUCT_NAME' => (htmlspecialchars($arrProduct['name'], ENT_QUOTES, CONTREXX_CHARSET).$newProductLink),
                        'HOTELCARD_PRODUCT_DESC' => htmlspecialchars($arrProduct['desc'], ENT_QUOTES, CONTREXX_CHARSET),
                    ));
                    $this->objTemplate->parseCurrentBlock();
                }
            }
            $this->objTemplate->setCurrentBlock('category');
            $this->objTemplate->setVariable(array(
                'HOTELCARD_CATEGORY_ID' => $categoryId,
                'HOTELCARD_CATEGORY_NAME' => htmlspecialchars($arrCategory['name'], ENT_QUOTES, CONTREXX_CHARSET),
                'HOTELCARD_CATEGORY_DESC' => htmlspecialchars($arrCategory['desc'], ENT_QUOTES, CONTREXX_CHARSET),
            ));
            $this->objTemplate->parseCurrentBlock();
        }
        // References
        $this->objTemplate->setCurrentBlock('reference');
        foreach ($arrReferences as $arrReference) {
            $this->objTemplate->setVariable(array(
                'HOTELCARD_REFERENCE_ID' => $arrReference['reference_id'],
                'HOTELCARD_REFERENCE_NAME' => htmlspecialchars($arrReference['name'], ENT_QUOTES, CONTREXX_CHARSET),
                'HOTELCARD_REFERENCE_DESC' => htmlspecialchars($arrReference['desc'], ENT_QUOTES, CONTREXX_CHARSET),
                'HOTELCARD_REFERENCE_IMAGE' => $arrReference['images'][HOTELCARD_ORD_IMAGE_REFERENCE]['path'],
            ));
            $this->objTemplate->parseCurrentBlock();
        }
        return true;
    }


    /**
     * Set up the page with a list of all References
     *
     * This method decides which view to set up
     * @return    boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function showReferences()
    {
        global $_ARRAYLANG;

        $this->showReferenceNavigation();

        $view = (isset($_GET['view']) ? $_GET['view'] : 0);
        $this->objTemplate->setGlobalVariable(array(
            'TXT_HOTELCARD_PRODUCTS' => $_ARRAYLANG['TXT_HOTELCARD_PRODUCTS'],
            'TXT_HOTELCARD_REFERENCES' => $_ARRAYLANG['TXT_HOTELCARD_REFERENCE_VIEW_'.$view],
        ));

        if ($view == 1) {
            return $this->showReferencesAtoZ();
        }
        return $this->showReferencesByProductAtoZ();
    }


    /**
     * Set up the page with a list of all References
     *
     * This view is grouped by References.
     * It shows Reference names, images and descriptions.
     * The thumbnail images are linked with their lightbox view.
     * Mind:  No Products are shown here!
     * @return    boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function showReferencesAtoZ()
    {
        global $_ARRAYLANG, $_LANGID;

        if ($this->objTemplate->blockExists('referenceview1'))
            $this->objTemplate->hideBlock('referenceview1');
        $arrReferences = Reference::getArrayByLanguageId($_LANGID);
        usort($arrReferences, 'cmp_name');
        foreach ($arrReferences as $arrReference) {
            $referenceId = $arrReference['reference_id'];
            foreach ($arrReference['images'] as $arrImage) {
                $strName = '';
                $text_id = $arrImage['text_id'];
                $objText = Text::getById($text_id, $_LANGID);
                if ($objText) $strName = $objText->getName();
                $this->objTemplate->setVariable(array(
                    'HOTELCARD_REFERENCE_IMAGE' => $arrImage['path'],
                    'HOTELCARD_REFERENCE_IMAGE_NAME' => htmlspecialchars($strName, ENT_QUOTES, CONTREXX_CHARSET),
                    'HOTELCARD_REFERENCE_ID' => $referenceId,
                    'HOTELCARD_REFERENCE_NAME' => htmlspecialchars($arrReference['name'], ENT_QUOTES, CONTREXX_CHARSET),
                    'HOTELCARD_REFERENCE_DESC' => htmlspecialchars($arrReference['desc'], ENT_QUOTES, CONTREXX_CHARSET),
                ));
                $this->objTemplate->parse('referenceimage0');
            }
            $this->objTemplate->setVariable(array(
                'HOTELCARD_REFERENCE_ID' => $referenceId,
                'HOTELCARD_REFERENCE_NAME' => htmlspecialchars($arrReference['name'], ENT_QUOTES, CONTREXX_CHARSET),
                'HOTELCARD_REFERENCE_DESC' => htmlspecialchars($arrReference['desc'], ENT_QUOTES, CONTREXX_CHARSET),
            ));
            $this->objTemplate->parse('referenceview0');
        }
        return true;
    }


    /**
     * Set up the page with a list of all References
     *
     * This view is grouped by Products.
     * It shows Product names, Reference images and their descriptions.
     * The thumbnail images are linked with their lightbox view.
     * The Product names link to the Products page.
     * Note that this method "overrides" the sctive flag of the References,
     * thus displaying inactive ones as well.  This contrasts
     * {@link showReferencesAtoZ()} where inactive references are hidden
     * as expected.
     * @return    boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function showReferencesByProductAtoZ()
    {
        global $_ARRAYLANG, $_LANGID;

        if ($this->objTemplate->blockExists('referenceview0'))
            $this->objTemplate->hideBlock('referenceview0');


        $arrProducts = Product::getArrayByLanguageId($_LANGID);
        usort($arrProducts, 'cmp_name');
        foreach ($arrProducts as $arrProduct) {
            $product_id = $arrProduct['product_id'];
            $arrReferenceIdOrd = RelProductReference::getReferenceIdOrdArrayByProductId($product_id);
            if (empty($arrReferenceIdOrd)) continue;
            // Note: This is a special use of the active flag:
            // Show inactive References on this page as well!
            $arrReferences = Reference::getArrayByProductId($product_id, $_LANGID, false);
            if (empty($arrReferences)) continue;

            // Get reference download-link (added by Kevin)
            $arrRefLink = Download::getRefLinkById( $product_id, $_LANGID );

            foreach ($arrReferences as $arrReference) {
                $reference_id = $arrReference['reference_id'];
                $objTextReference = Text::getById($arrReference['text_id'], $_LANGID);
                if (!$objTextReference) {
                    $objTextReference = new Text($_LANGID, $arrReference['text_id']);
                }
                foreach ($arrReferenceIdOrd[$reference_id] as $ord) {
                    $arrImage = Image::getArrayById($arrReference['image_id'], $ord);
                    $strName = '';
                    $text_id = $arrImage[$ord]['text_id'];
                    $objText = Text::getById($text_id, $_LANGID);
                    if ($objText) $strName = $objText->getName();
                    $this->objTemplate->setVariable(array(
                        'HOTELCARD_REFERENCE_IMAGE' => $arrImage[$ord]['path'],
                        'HOTELCARD_REFERENCE_IMAGE_NAME' => htmlspecialchars($strName, ENT_QUOTES, CONTREXX_CHARSET),
                        'HOTELCARD_REFERENCE_ID' => $reference_id,
                        'HOTELCARD_REFERENCE_NAME' => htmlspecialchars($arrReference['name'], ENT_QUOTES, CONTREXX_CHARSET),
                        'HOTELCARD_REFERENCE_DESC' => htmlspecialchars($arrReference['desc'], ENT_QUOTES, CONTREXX_CHARSET),
                    ));
                    $this->objTemplate->parse('referenceimage1');
                }
            }

            $this->objTemplate->setVariable(array(
                'HOTELCARD_PRODUCT_ID' => $arrProduct['product_id'],
                'HOTELCARD_PRODUCT_NAME' => htmlspecialchars($arrProduct['name'], ENT_QUOTES, CONTREXX_CHARSET),
                'HOTELCARD_PRODUCT_DESC' => htmlspecialchars($arrProduct['desc'], ENT_QUOTES, CONTREXX_CHARSET),
                'HOTELCARD_REF_DOWNLOAD_LINK' => $arrRefLink, // KEVIN
            ));
            $this->objTemplate->parse('referenceview1');
        }
        return true;
    }


    /**
     * Set up the page with a single Reference,
     * with Products and details
     * @return    boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function showReference()
    {
        global $_LANGID;

        $this->showReferenceNavigation();

        $referenceId = (isset($_GET['reference_id']) ? $_GET['reference_id'] : 0);
        if (!$referenceId) {
            return false;
        }

        // Collect all Reference details
        $objReference = Reference::getById($referenceId);
        $textId = $objReference->getTextId();
        $objText = Text::getById($textId, $_LANGID);
        if (!$objText) {
            $objText = new Text($_LANGID, $textId);
        }
        $imageId = $objReference->getImageId();
        $arrImages = Image::getArrayById($imageId);
        foreach ($arrImages as $arrImage) {
            $this->objTemplate->setVariable(array(
//                'HOTELCARD_REFERENCE_ID' => $referenceId,
                'HOTELCARD_REFERENCE_NAME' => htmlspecialchars($objText->getName(), ENT_QUOTES, CONTREXX_CHARSET),
//                'HOTELCARD_REFERENCE_DESC' => $objText->getDesc(),
//                'HOTELCARD_REFERENCE_IMAGE_ID' => $arrImage['image_id'],
                'HOTELCARD_REFERENCE_IMAGE' => $arrImage['path'],
                'HOTELCARD_REFERENCE_IMAGE_NAME' => $arrImage['name'],
            ));

            // List all Products associated with that Image
            $arrProductId = RelProductReference::getProductIdArrayByReferenceId($referenceId, $arrImage['ord']);
            $strProductId = implode(',', $arrProductId);
            $arrProducts = Product::getArrayByLanguageId($_LANGID, true, $strProductId);
            foreach ($arrProducts as $arrProduct) {
                $this->objTemplate->setVariable(array(
                    'HOTELCARD_PRODUCT_ID' => $arrProduct['product_id'],
                    'HOTELCARD_PRODUCT_NAME' => htmlspecialchars($arrProduct['name'], ENT_QUOTES, CONTREXX_CHARSET),
                    'HOTELCARD_PRODUCT_DESC' => htmlspecialchars($arrProduct['desc'], ENT_QUOTES, CONTREXX_CHARSET),
                    'HOTELCARD_PRODUCT_IMAGE' => $arrProduct['images'][HOTELCARD_ORD_IMAGE_PRODUCT_OVERVIEW]['path'],
                ));
                $this->objTemplate->parse('product');
            }
            $this->objTemplate->parse('reference');
        }
        $this->objTemplate->setVariable(array(
            'HOTELCARD_REFERENCE_NAME' => htmlspecialchars($objText->getName(), ENT_QUOTES, CONTREXX_CHARSET),
            'HOTELCARD_REFERENCE_DESC' => htmlspecialchars($objText->getDesc(), ENT_QUOTES, CONTREXX_CHARSET),
        ));
        return true;
    }


    function showContacts()
    {
        global $_LANGID, $_ARRAYLANG, $objDatabase;

        $selectedContinentId = (isset($_GET['continent']) ? $_GET['continent'] : '');
        $selectedCountryId = (isset($_GET['country']) ? $_GET['country'] : '');
        $selectedRegionId = (isset($_GET['region']) ? $_GET['region'] : '');
        $this->objTemplate->setVariable(array(
            'TXT_HOTELCARD_CONTACT_VIEW_0' => $_ARRAYLANG['TXT_HOTELCARD_CONTACT_VIEW_0'],
            'TXT_HOTELCARD_CONTACT_VIEW_1' => $_ARRAYLANG['TXT_HOTELCARD_CONTACT_VIEW_1'],
        ));

        $objFWUser = FWUser::getFWUserObject();
        $objUsers = $objFWUser->objUser;
        if (!$objUsers) return false;
        $objUsers = $objUsers->getUsers(
            array(
                'group_id' => HOTELCARD_CONFIG_CONTACT_GROUP,
            )
        );

        // Region hierarchy for the navigation
        $arrHierarchy = array();
        // Array of strings with various contact types
        $arrContact = array();
        $userContinentId = '';
        $userCountryId   = '';
        $userRegionId    = '';
        $userTypeId      = '';
        while (!$objUsers->EOF) {
            $user_id = $objUsers->getId();
            $arrRelations = RelUserContact::getArrayByUserId($user_id);

            if (empty($arrRelations)) {
                $objUsers->next();
                continue;
            }
            foreach ($arrRelations as $arrRelation) {
                $userContinentId = $arrRelation['continent_id'];
                $userCountryId   = $arrRelation['country_id'];
                $userRegionId    = $arrRelation['region_id'];
                $userTypeId      = $arrRelation['type_id'];
                $userOrd         = $arrRelation['ord'];

                // Add any combination of continent, country, and region to the hierarchy
                // for the navigation
                $arrHierarchy[$userContinentId][$userCountryId][$userRegionId] = true;

                // Skip contacts that do not match at least continent and country.
                // If there is a zero region ID in the contact, show it even if
                // there is no region selected
                if (   empty($selectedContinentId) || $userContinentId != $selectedContinentId
                    || empty($selectedCountryId)   || $userCountryId != $selectedCountryId
                    || ($userRegionId == 0 && !empty($selectedRegionId))
                    || ($userRegionId != 0 && $userRegionId != $selectedRegionId)) {
                    continue;
                }

                $firstName    = $objUsers->getProfileAttribute('firstname');
                $lastName     = $objUsers->getProfileAttribute('lastname');
                $company      = $objUsers->getProfileAttribute('company');
                $address      = $objUsers->getProfileAttribute('address');
                $zip          = $objUsers->getProfileAttribute('zip');
                $city         = $objUsers->getProfileAttribute('city');
                $phoneOffice  = $objUsers->getProfileAttribute('phone_office');
// TODO: Unused
//                $country      = $objUsers->getProfileAttribute('country');
                $phoneMobile  = $objUsers->getProfileAttribute('phone_mobile');
                $fax          = $objUsers->getProfileAttribute('phone_fax');
                $url          = $objUsers->getProfileAttribute('website');
                $email          = $objUsers->getEmail();
                $url          = '<a href="'.$objUsers->getProfileAttribute('website').'" target="_blank">'.$objUsers->getProfileAttribute('website').'</a>';
                $email        = '<a href="mailto:'.$objUsers->getEmail().'">'.$objUsers->getEmail().'</a>';

                // LAND LABEL AUS CONTREXX_LIB_COUNTRY ZIEHEN:
                $countryNameId = $objUsers->getProfileAttribute('country');
                $query = "SELECT name FROM ".DBPREFIX."lib_country WHERE id=$countryNameId";
                $objResult = $objDatabase->Execute($query);
                $countryName = '';
                if ($objResult && !$objResult->EOF) {
                    $countryName = $objResult->fields['name'];
                }

                if (empty($arrContact[$userTypeId][$userOrd]))
                    $arrContact[$userTypeId][$userOrd] = '';

                $arrContact[$userTypeId][$userOrd] .=
                    ($firstName || $lastName ? "$firstName $lastName<br />" : '').
                    ($company ? "$company<br />" : '').
                    ($address ? "$address<br />" : '').
                    ($zip || $city ? "$zip $city<br />" : '').
                    ($countryName ? "$countryName<br />" : '').
                    ($phoneOffice ? $_ARRAYLANG['TXT_HOTELCARD_CONTACT_PHONE_OFFICE'].": $phoneOffice<br />" : '').
                    ($fax ? $_ARRAYLANG['TXT_HOTELCARD_CONTACT_FAX'].": $fax<br />" : '').
                    ($phoneMobile ? $_ARRAYLANG['TXT_HOTELCARD_CONTACT_PHONE_MOBILE'].": $phoneMobile<br />" : '').
                    ($email ? "$email<br />" : '').
                    ($url ? "$url<br />" : '')."<br />";
                    //("<br />");
            }
            $objUsers->next();
        }

        $arrContinentName = RelUserContact::getContinentArray($_LANGID);
        $arrCountryName   = RelUserContact::getCountryArray($_LANGID);
        $arrRegionName    = RelUserContact::getRegionArray($_LANGID);

        // Navigation level 1
        foreach (array_keys($arrHierarchy) as $continentId) {
            $continentName = $arrContinentName[$continentId];
            $this->objTemplate->setVariable(array(
                'HOTELCARD_CONTACT_NAVIGATION1_CODE1' => $continentId,
                'HOTELCARD_CONTACT_NAVIGATION1_NAME'  => $continentName,
                'HOTELCARD_CONTACT_NAVIGATION1_STYLE' => ($continentId == $selectedContinentId ? '_active' : ''),
            ));
            $this->objTemplate->parse('navigation1');

            if ($continentId == $selectedContinentId) {
                // Navigation level 2
                foreach ($arrCountryName as $countryId => $countryName) {
                    if (empty($arrHierarchy[$continentId][$countryId])) continue;
                    $this->objTemplate->setVariable(array(
                        'HOTELCARD_CONTACT_NAVIGATION2_CODE1' => $continentId,
                        'HOTELCARD_CONTACT_NAVIGATION2_CODE2' => $countryId,
                        'HOTELCARD_CONTACT_NAVIGATION2_NAME'  => $countryName,
                        'HOTELCARD_CONTACT_NAVIGATION2_STYLE' => ($countryId == $selectedCountryId ? '_active' : ''),
                    ));
                    $this->objTemplate->parse('navigation2');
                }
            }
        }

        if ($selectedCountryId) {
            // Navigation level 3
            foreach ($arrRegionName as $regionId => $regionName) {
                if (empty($arrHierarchy[$selectedContinentId][$selectedCountryId][$regionId])) continue;
                $this->objTemplate->setVariable(array(
                    'HOTELCARD_CONTACT_NAVIGATION3_CODE1' => $selectedContinentId,
                    'HOTELCARD_CONTACT_NAVIGATION3_CODE2' => $selectedCountryId,
                    'HOTELCARD_CONTACT_NAVIGATION3_CODE3' => $regionId,
                    'HOTELCARD_CONTACT_NAVIGATION3_NAME'  => $regionName,
                    'HOTELCARD_CONTACT_NAVIGATION3_STYLE' => ($regionId == $selectedRegionId ? '_active' : ''),
                ));
                $this->objTemplate->parse('navigation3');
            }
        }

        ksort($arrContact);
        foreach ($arrContact as $type => $arrType) {
            ksort($arrType);
            $strContacts = '';
            foreach ($arrType as $strContact) {
                if (empty($strContact)) continue;
                $strContacts .= $strContact;
            }
            $this->objTemplate->setVariable(array(
                'HOTELCARD_CONTACT_TITLE'   => $_ARRAYLANG['TXT_HOTELCARD_CONTACT_TYPE_'.$type],
                'HOTELCARD_CONTACT_CONTENT' => $strContacts,
            ));
            $this->objTemplate->parse('column');
        }
        return true;
    }


    /**
     * Set up the Category navigation bar
     *
     * This may be called for whatever page that needs to display it.
     * Note that you must include the corresponding block in the template.
     * The same is done in {@link showProducts()}, but includes the
     * Products contained within each Category there.
     * @return    boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function showCategoryNavigation()
    {
        global $_LANGID;

        $categoryId = (isset($_GET['category_id']) ? $_GET['category_id'] : false);

        $arrCategories = Category::getArrayByLanguageId($_LANGID);
        $this->objTemplate->setCurrentBlock('level_2');
        foreach ($arrCategories as $arrCategory) {
            $categoryStyle = 'inactive';
            $arrProducts = Product::getArrayByCategoryId($arrCategory['category_id'], $_LANGID);
            if (count($arrProducts) == 0) {
                continue;
            }
            if ($categoryId == $arrCategory['category_id']) {
                $categoryStyle = 'active';
            }
            $this->objTemplate->setVariable(array(
                'HOTELCARD_CATEGORY_ID' => $arrCategory['category_id'],
                'HOTELCARD_CATEGORY_NAME' => htmlspecialchars($arrCategory['name'], ENT_QUOTES, CONTREXX_CHARSET),
                'HOTELCARD_NAVIGATION_STYLE' => $categoryStyle,
            ));
            $this->objTemplate->parseCurrentBlock();
        }
        return true;
    }


    function showReferenceNavigation()
    {
        global $_LANGID, $_ARRAYLANG;

        $view = (isset($_GET['view']) ? $_GET['view'] : 0);

        $this->objTemplate->setCurrentBlock('level_2');
        for ($i = 0; $i < HOTELCARD_REFERENCE_VIEW_COUNT; ++$i) {
            $style = 'inactive';
            if ($view == $i) {
                $style = 'active';
            }
            $this->objTemplate->setVariable(array(
                'HOTELCARD_REFERENCE_VIEW_INDEX' => $i,
                'HOTELCARD_REFERENCE_VIEW_NAME' => $_ARRAYLANG['TXT_HOTELCARD_REFERENCE_VIEW_'.$i],
                'HOTELCARD_REFERENCE_VIEW_STYLE' => $style,
            ));
            $this->objTemplate->parseCurrentBlock();
        }
        return true;
    }


    /**
     * Handle any error occurring in this class.
     *
     * Tries to fix known problems with the database.
     * @global  mixed     $objDatabase    Database object
     * @return  boolean                   False.  Always.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function errorHandler()
    {
        global $objDatabase;

        // Verify that the module is installed
        $query = "
            SELECT 1
              FROM ".DBPREFIX."modules
             WHERE name='hotelcard'
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        if ($objResult->RecordCount() == 0) {
            $query = "
                INSERT INTO ".DBPREFIX."modules (
                  `id`, `name`, `description_variable`,
                  `status`, `is_required`, `is_core`
                ) VALUES (
                  '101', 'hotelcard', 'TXT_HOTELCARD_MODULE_DESCRIPTION',
                  'y', '0', '0'
                )
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                return false;
            }
        }

        // Verify that the backend area is present
        $query = "
            SELECT 1
              FROM ".DBPREFIX."backend_areas
             WHERE uri='index.php?cmd=hotelcard'
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        if ($objResult->RecordCount() == 0) {
            $query = "
                INSERT INTO ".DBPREFIX."backend_areas (
                  `area_id`, `parent_area_id`, `type`, `area_name`,
                  `is_active`, `uri`, `target`, `module_id`, `order_id`, `access_id`
                ) VALUES (
                  126, '2', 'navigation', 'TXT_HOTELCARD',
                  '1', 'index.php?cmd=hotelcard', '_self', '101', '0', '126'
                );
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                return false;
            }
        }

        return false;
    }


    /**
     * Adds the string to the status messages.
     *
     * If necessary, inserts a line break tag (<br />) between
     * messages.
     * @param   string  $strMessage       The message to add
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function addMessage($strMessage)
    {
        $this->statusMessage .=
            ($this->statusMessage ? '<br />' : '').
            $strMessage;
    }

// Added 20081014

    /**
     * Set up the Products page with a list of all Categories
     * and contained Products with Images.
     * @return    boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function showMaterials()
    {
        global $_LANGID, $_ARRAYLANG;

        // The predecessors and successors of the currently selected ones
        // are initialized to the same values.
        // See below for the cases when they need to be changed.
        $selected_material_id =
            (isset($_GET['material_id']) ? $_GET['material_id'] : false);
        $selected_manufacturer_id =
            (isset($_GET['manufacturer_id']) ? $_GET['manufacturer_id'] : false);
        $selected_line_id =
            (isset($_GET['line_id']) ? $_GET['line_id'] : false);
        $selected_image_ord =
            (isset($_GET['image_ord']) ? $_GET['image_ord'] : false);

        // All the Materials, Manufacturers, and Lines
        $arrMaterials = Material::getArrayByLanguageId($_LANGID);
        $arrManufacturers = Manufacturer::getArrayByLanguageId($_LANGID);
        $arrLines = Line::getArrayByLanguageId($_LANGID);
        $arrLines = Line::setLineLastChildId($arrLines); // KEVIN

// Change 20081022
        // If the Material only is selected, choose the first Line.
        // The first Material and Manufacturer will be activated below.
        if (empty($selected_manufacturer_id) && !empty($selected_material_id)) {
            foreach ($arrLines as $arrLine) {
                if ($arrLine['material_id'] == $selected_material_id) {
                    $selected_line_id = $arrLine['line_id'];
                    break;
                }
            }
        }

        // If the Line ID is set, Material and Manufacturer IDs should be
        // omitted from the parameters.
        // If they are not, they are redundant at best, and may even be wrong!
        // Determine them in this case.
        if ($selected_line_id) {
            $selected_material_id = $arrLines[$selected_line_id]['material_id'];
            $selected_manufacturer_id = $arrLines[$selected_line_id]['manufacturer_id'];
        }

        // If no Material is selected, show the overview,
        // which includes a title.
        if (empty($selected_material_id)) {
            $this->objTemplate->setVariable(
                'TXT_HOTELCARD_MATERIALS',
                    $_ARRAYLANG['TXT_HOTELCARD_MATERIALS']
            );
        }

        // The array storing the order of all the data available
        $arrOrder = array();

        // Flags enabling the current Material and Manufacturer to be shown.
        // If there is no common Line, they may be skipped.
        $flagHaveLine = false;
        $flagHaveManufacturer = false;

        // Loop through all the available data no matter what.
        // Even though only parts of it may be displayed, it needs
        // to be processed and ordered for the "linear navigation".
        // See below for more.
        foreach ($arrMaterials as $material_id => $arrMaterial) {

            // If the Material name equals "BREAK", insert a column break
            if ($arrMaterial['name'] == 'BREAK') {
                $this->objTemplate->parse('materialcolumn');
            }

            foreach ($arrManufacturers as $manufacturer_id => $arrManufacturer) {
                foreach ($arrLines as $line_id => $arrLine) {

                    // If this combination of Material and Manufacturer
                    // is not associated with that Line, skip int
                    if (   $arrLine['material_id'] != $material_id
                        || $arrLine['manufacturer_id'] != $manufacturer_id)
                        continue;

                    // The current Manufacturer and Material share
                    // a common Line
                    $flagHaveLine = true;

                    foreach ($arrLine['images'] as $image_ord => $arrImage) {

                        // Take note of the order for all the parameters
                        // available (and thus needed).
                        $arrOrder[] =
                            $material_id.
                            ($selected_manufacturer_id ? ",$manufacturer_id" : '').
                            ($selected_line_id ? ",$line_id,$image_ord" : '');

                        // Show the Image links for the current Line only.
                        // Conditions:
                        // - This Line is selected, which implies that
                        // - The associated Material and Manufacturer are selected as well
                        if ($line_id != $selected_line_id) continue;

                        $this->objTemplate->setVariable(array(
//                            'HOTELCARD_IMAGE_MATERIAL_ID' => $material_id,
//                            'HOTELCARD_IMAGE_MANUFACTURER_ID' => $manufacturer_id,
                            'HOTELCARD_IMAGE_LINE_ID' => $line_id,
                            'HOTELCARD_IMAGE_ORD' => $image_ord,
                            'HOTELCARD_IMAGE_INDEX' => $image_ord-HOTELCARD_ORD_IMAGE_LINE+1,
                        ));

                        // Activate the first Image if none is selected
                        if (empty($selected_image_ord)) $selected_image_ord = HOTELCARD_ORD_IMAGE_LINE;
                        // Highlight the selected Image link, display the Image

                        // GROSSES BILD SOLL AUCH AUF DOWNLOAD-PDF LINKEN! KEVIN
                        $arrDownloads = Download::getArrayByLanguageId($_LANGID, 0, $line_id);
                        if (!empty($arrDownloads)) {
                            // JEWEILS NUR EIN DOWNLOAD PRO LINIE! DH ES GIBT NUR EINEN LINK
                            foreach ($arrDownloads as $arrDownload) {
                                $objTextDownload = Text::getById($arrDownload['text_id'], $_LANGID);
                                $strFileExtension = strtoupper(File::getExtension($arrDownload['path']));
                                $strFileSize = File::getSizeString(filesize($arrDownload['path']));

                                // check extension: if PDF, add target=_blank
                                $strFileExtension == 'PDF' ? $strLinkTarget = "target='_blank'" : $strLinkTarget = ""; // KEVIN
                                //echo("fild:".$strFileExtension."<br>");

                                // check if language of downloadfile matches gui language: // KEVIN
                                $downloadLangIdIsGuiLang = Download::checkDownloadLangIdWithGuiLang($arrDownload['language_id'], $_LANGID);
                            }
                        }

                        if ($image_ord == $selected_image_ord ) {
                            $objText = Text::getById($arrImage['text_id'], $_LANGID);
                            $name = '';
                            if ($objText) $name = $objText->getName();
//echo("DOWNLOAD-PATH: ".$arrDownload['path']."<br>");
                            $this->objTemplate->setVariable(array(
                                'HOTELCARD_IMAGE_STYLE' => '_active',
                                'HOTELCARD_IMAGE_HREF' => $arrImage['path'],
                                'HOTELCARD_IMAGE_NAME' => $name,
                                'HOTELCARD_DWNLD_NAME' => htmlspecialchars($objTextDownload->getName(), ENT_QUOTES, CONTREXX_CHARSET), // KEVIN NEW
                                'HOTELCARD_DWNLD_PATH' => $arrDownload['path'],
                                'HOTELCARD_DWNLD_SIZE' => $strFileSize,
                                'HOTELCARD_DWNLD_LNKTGT' => $strLinkTarget,
                            ));
                        }
                        // Hide the link if
                        // - the Image is selected
                        if (   $selected_line_id == $line_id
                            && $selected_image_ord == $image_ord) {
                            $this->objTemplate->hideBlock('imagelink1');
                        } else {
                            $this->objTemplate->touchBlock('imagelink2');
                        }
                        $this->objTemplate->parse('image');
                    }

                    // Download, shown under the same conditions as the Images:
                    // - This Line is selected, which implies that
                    // - The associated Material and Manufacturer are selected as well
                    if ($line_id == $selected_line_id) {
                        $arrDownloads = Download::getArrayByLanguageId($_LANGID, 0, $line_id);
                        if (!empty($arrDownloads)) {
                            foreach ($arrDownloads as $arrDownload) {
                                $objTextDownload = Text::getById($arrDownload['text_id'], $_LANGID);
                                $strFileExtension = strtoupper(File::getExtension($arrDownload['path']));
                                $strFileSize = File::getSizeString(filesize($arrDownload['path']));

                                // check extension: if PDF, add target=_blank
                                $strFileExtension == 'PDF' ? $strLinkTarget = "target='_blank'" : $strLinkTarget = ""; // KEVIN
                                //echo("fild:".$strFileExtension."<br>");

                                // check if language of downloadfile matches gui language: // KEVIN
                                $downloadLangIdIsGuiLang = Download::checkDownloadLangIdWithGuiLang($arrDownload['language_id'], $_LANGID);
                                if ($downloadLangIdIsGuiLang) {
                                    $this->objTemplate->setVariable(array(
                                        'HOTELCARD_DOWNLOAD_ID' => $arrDownload['download_id'],
                                        'HOTELCARD_DOWNLOAD_NAME' => htmlspecialchars($objTextDownload->getName(), ENT_QUOTES, CONTREXX_CHARSET),
                                        'HOTELCARD_DOWNLOAD_DESC' => htmlspecialchars($objTextDownload->getDesc(), ENT_QUOTES, CONTREXX_CHARSET),
                                        'HOTELCARD_DOWNLOAD_TYPE' => $arrDownload['type'],
                                        'HOTELCARD_DOWNLOAD_EXTENSION' => $strFileExtension,
                                        'HOTELCARD_DOWNLOAD_PATH' => $arrDownload['path'],
                                        'HOTELCARD_DOWNLOAD_SIZE' => $strFileSize,
                                        'HOTELCARD_DOWNLOAD_LINKTARGET' => $strLinkTarget,
                                    ));
                                    $this->objTemplate->parse('download');
                                }
                            }
                        }
                    }

                    // Show the Line if either
                    // - Its Material and Manufacturer are selected, or
                    // - Nothing is selected at all (overview)
                    if (   empty($selected_material_id)
                        || (   $selected_material_id == $material_id
                            && $selected_manufacturer_id == $manufacturer_id)) {
                        $this->objTemplate->setVariable(array(
//                            'HOTELCARD_LINE_MATERIAL_ID' => $material_id,
//                            'HOTELCARD_LINE_MANUFACTURER_ID' => $manufacturer_id,
                            'HOTELCARD_LINE_ID' => $line_id,
                            'HOTELCARD_LINE_NAME' => $arrLine['name'],
                            'HOTELCARD_PRICE_GROUP' =>
                                $_ARRAYLANG['TXT_HOTELCARD_PRICE_GROUP_'.$arrLine['price_group_id']],
                            'HOTELCARD_PRICE_GROUP_IMG' => $arrLine['price_group_id'],
                            'HOTELCARD_LINE_LASTELEMENT' => ($arrLine['isLastChild'] == true ? ' isLastChild' : ''), // HACK KEVIN: IE KENNT :last-child nicht!!!
                        ));
                        // Activate the selected Line
                        if ($line_id == $selected_line_id) {
                            $this->objTemplate->setVariable(
                                'HOTELCARD_LINE_STYLE', '_active'
                            );
                        }

                        // Hide the link if
                        // - the Line is selected
                        if ($selected_line_id == $line_id) {
                            $this->objTemplate->hideBlock('linelink1');
                        } else {
                            $this->objTemplate->touchBlock('linelink2');
                        }
                        $this->objTemplate->parse('line');
                    }
                }

                // The above loop produced no Line associated with
                // both the current Material and Manufacturer and thus
                // may be skipped.
                if (!$flagHaveLine) continue;

                // Clear the flag for the next round
                $flagHaveLine = false;

                // Show the current Manufacturer if
                // - There is a common Line with the Manufacturer and
                //   the current Material (okay, see flag above), and
                // - Either no Material or the current Material is selected:
                if (   empty($selected_material_id)
                    || $selected_material_id == $material_id) {
                    $this->objTemplate->setVariable(array(
                        'HOTELCARD_MANUFACTURER_MATERIAL_ID' => $material_id,
                        'HOTELCARD_MANUFACTURER_ID' => $manufacturer_id,
                        'HOTELCARD_MANUFACTURER_NAME' => $arrManufacturer['name'],
                    ));
                    if ($manufacturer_id == $selected_manufacturer_id) {
                        $this->objTemplate->setVariable(
                            'HOTELCARD_MANUFACTURER_STYLE', '_active'
                        );
                    }
                    // Hide the link if
                    // - no Material is selected (overview), or
                    // - this Manufacturer is selected
                    if (   empty($selected_material_id)
                        || $selected_manufacturer_id == $manufacturer_id) {
                        $this->objTemplate->hideBlock('manufacturerlink1');
                    } else {
                        $this->objTemplate->touchBlock('manufacturerlink2');
                    }
                    $this->objTemplate->parse('manufacturer');
                }

                // There is at least one Manufacturer associated with
                // the current Material by means of a Line
                $flagHaveManufacturer = true;
            }

            // If there is no Manufacturer associated with the current
            // Material, skip it.
            if (!$flagHaveManufacturer) continue;

            // Clear the flag for the next round
            $flagHaveManufacturer = false;

            // Now the first condition for showing this Material are met:
            // - There is a Manufacturer associated with it.
            // In addition, we need to check that either
            // - no Material is selected (overview), or
            // - this is the selected Material
            if (   empty($selected_material_id)
                || $selected_material_id == $material_id) {
                $this->objTemplate->setVariable(array(
                    'HOTELCARD_MATERIAL_ID' => $material_id,
                    'HOTELCARD_MATERIAL_NAME' => $arrMaterial['name'],
                ));
                $this->objTemplate->parse('material');
            }

            // List the Material in the subnavigation.
            // This only applies if at least any Material is selected,
            // so the subnavigation is not visible in the overview.
            if ($selected_material_id) {
                $this->objTemplate->setVariable(array(
                    'HOTELCARD_VIEW_MATERIAL_ID' => $material_id,
                    'HOTELCARD_VIEW_MATERIAL_STYLE' =>
                        ($material_id == $selected_material_id ? '_active' : ''),
                    'HOTELCARD_VIEW_MATERIAL_NAME' => strip_tags($arrMaterial['name']),
                ));
                $this->objTemplate->parse('level_2');
            }
        }

        // Now that all parameters are fixed, set up the navigation.
        $currentIndex =
            ($selected_material_id ? $selected_material_id : '').
            ($selected_manufacturer_id ? ",$selected_manufacturer_id" : '').
            ($selected_line_id ? ",$selected_line_id,$selected_image_ord" : '');

        // No selection, no navigation.
        if (empty($currentIndex)) return true;

        $currentKey = array_search($currentIndex, $arrOrder);
        $prevKey = ($currentKey == 0 ? count($arrOrder)-1 : $currentKey-1);
        $nextKey = ($currentKey == count($arrOrder)-1 ? 0 : $currentKey+1);

        $arrPrevIds = preg_split('/,/', $arrOrder[$prevKey], null, PREG_SPLIT_NO_EMPTY);
        $arrNextIds = preg_split('/,/', $arrOrder[$nextKey], null, PREG_SPLIT_NO_EMPTY);
        $prev_material_id = (isset($arrPrevIds[0]) ? $arrPrevIds[0] : 0);
        $prev_manufacturer_id = (isset($arrPrevIds[1]) ? $arrPrevIds[1] : 0);
        $prev_line_id = (isset($arrPrevIds[2]) ? $arrPrevIds[2] : 0);
        $prev_image_id = (isset($arrPrevIds[3]) ? $arrPrevIds[3] : 0);
        $next_material_id = (isset($arrNextIds[0]) ? $arrNextIds[0] : 0);
        $next_manufacturer_id = (isset($arrNextIds[1]) ? $arrNextIds[1] : 0);
        $next_line_id = (isset($arrNextIds[2]) ? $arrNextIds[2] : 0);
        $next_image_id = (isset($arrNextIds[3]) ? $arrNextIds[3] : 0);

        $this->objTemplate->setVariable(array(
            'HOTELCARD_LINNAV_QUERY_PREV' =>
                (empty($selected_line_id) && $prev_material_id ? "&amp;material_id=$prev_material_id" : '').
                (empty($selected_line_id) && $prev_manufacturer_id ? "&amp;manufacturer_id=$prev_manufacturer_id" : '').
                ($prev_line_id ? "&amp;line_id=$prev_line_id" : '').
                ($prev_image_id ? "&amp;image_ord=$prev_image_id" : ''),
            'HOTELCARD_LINNAV_QUERY_NEXT' =>
                (empty($selected_line_id) && $next_material_id ? "&amp;material_id=$next_material_id" : '').
                (empty($selected_line_id) && $next_manufacturer_id ? "&amp;manufacturer_id=$next_manufacturer_id" : '').
                ($next_line_id ? "&amp;line_id=$next_line_id" : '').
                ($next_image_id ? "&amp;image_ord=$next_image_id" : ''),
            'TXT_HOTELCARD_LINNAV_PREV' => $_ARRAYLANG['TXT_HOTELCARD_LINNAV_PREV'],
            'TXT_HOTELCARD_LINNAV_NEXT' => $_ARRAYLANG['TXT_HOTELCARD_LINNAV_NEXT'],
        ));
        return true;
    }


    function getPageTitle()
    {
        return $this->page_title;
    }

}

?>
