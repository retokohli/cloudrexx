<?php

/**
 * Class Shop manager
 *
 * Class for the administration of the shop
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @author      Ivan Schmid <ivan.schmid@comvation.com>
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 */

/**
 * @ignore
 */
require_once ASCMS_CORE_PATH.'/Country.class.php';
require_once ASCMS_CORE_PATH.'/Html.class.php';
require_once ASCMS_CORE_PATH.'/MailTemplate.class.php';
require_once ASCMS_CORE_PATH.'/SettingDb.class.php';
require_once ASCMS_CORE_PATH.'/Text.class.php';
require_once ASCMS_FRAMEWORK_PATH.'/Image.class.php';
require_once ASCMS_MODULE_PATH.'/shop/shopLib.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Attribute.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Attributes.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/CSVimport.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Csv_bv.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Currency.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Customer.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Customers.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Discount.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Distribution.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Manufacturer.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Payment.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/PaymentProcessing.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Product.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Products.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Shipment.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/ShopCategory.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/ShopCategories.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Vat.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Weight.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Zones.class.php';
require_once ASCMS_MODULE_PATH.'/shop/payments/saferpay/Saferpay.class.php';
require_once ASCMS_MODULE_PATH.'/shop/payments/yellowpay/Yellowpay.class.php';
require_once ASCMS_MODULE_PATH.'/shop/payments/datatrans/Datatrans.class.php';
// OBSOLETE
//require_once ASCMS_MODULE_PATH.'/shop/lib/Settings.class.php';
//require_once ASCMS_MODULE_PATH.'/shop/lib/Exchange.class.php';

/**
 * Administration of the Shop
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @author      Ivan Schmid <ivan.schmid@comvation.com>
 * @access      public
 * @package     contrexx
 * @subpackage  module_shop
 * @version     2.1.0
 */
class shopmanager extends ShopLibrary
{
    /**
     * The Template object
     * @var   HTML_Template_Sigma
     */
    private static $objTemplate;
    private static $strErrMessage = '';
    private static $strOkMessage = '';
    private static $pageTitle = '';
    private static $arrCategoryTreeName = array();
    private static $defaultImage = '';
    private static $uploadDir = false;

    /**
     * CSV Import class
     * @var CSVimport
     */
    private $objCSVimport;


    /**
     * Constructor
     * @access  public
     * @return  shopmanager
     */
    function __construct()
    {
        global $_ARRAYLANG, $objTemplate;

        SettingDb::init('config');
        self::$defaultImage = ASCMS_SHOP_IMAGES_WEB_PATH.'/'.ShopLibrary::noPictureName;
        $objTemplate->setVariable(
            'CONTENT_NAVIGATION',
            "<a href='index.php?cmd=shop".MODULE_INDEX."'>".$_ARRAYLANG['TXT_SHOP_INDEX']."</a>".
            "<a href='index.php?cmd=shop".MODULE_INDEX."&amp;act=cat'>".$_ARRAYLANG['TXT_CATEGORIES']."</a>".
            "<a href='index.php?cmd=shop".MODULE_INDEX."&amp;act=products'>".$_ARRAYLANG['TXT_PRODUCTS']."</a>".
            "<a href='index.php?cmd=shop".MODULE_INDEX."&amp;act=manufacturer'>".$_ARRAYLANG['TXT_SHOP_MANUFACTURER']."</a>".
            "<a href='index.php?cmd=shop".MODULE_INDEX."&amp;act=customers'>".$_ARRAYLANG['TXT_CUSTOMERS_PARTNERS']."</a>".
            "<a href='index.php?cmd=shop".MODULE_INDEX."&amp;act=orders'>".$_ARRAYLANG['TXT_ORDERS']."</a>".
            "<a href='index.php?cmd=shop".MODULE_INDEX."&amp;act=statistics'>".$_ARRAYLANG['TXT_STATISTIC']."</a>".
            "<a href='index.php?cmd=shop".MODULE_INDEX."&amp;act=import'>".$_ARRAYLANG['TXT_IMPORT_EXPORT']."</a>".
            "<a href='index.php?cmd=shop".MODULE_INDEX."&amp;act=pricelist'>".$_ARRAYLANG['TXT_PDF_OVERVIEW']."</a>".
            "<a href='index.php?cmd=shop".MODULE_INDEX."&amp;act=settings'>".$_ARRAYLANG['TXT_SETTINGS']."</a>"
        );
        self::$objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/shop/template');
        self::$objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        self::$objTemplate->setGlobalVariable(
            $_ARRAYLANG
          + array(
            'SHOP_CURRENCY' => Currency::getActiveCurrencySymbol(),
            'CSRF_PARAM' => CSRF::param()
        ));

// TODO: Necessary?
        $this->objCSVimport = new CSVimport();
    }


    /**
     * Set up the shop admin page
     */
    function getPage()
    {
        global $objTemplate, $_ARRAYLANG;

DBG::activate(DBG_DB_FIREPHP);

        if (isset($_SESSION['shop']['strOkMessage'])) {
            self::addMessage($_SESSION['shop']['strOkMessage']);
            unset($_SESSION['shop']['strOkMessage']);
        }

        if (!isset($_GET['act'])) {
            $_GET['act'] = '';
        }
        switch ($_GET['act']) {
            case 'settings':
                $this->_showSettings();
                break;
            case 'cat':
                $this->showCategories();
                break;
            case 'newcat':
                $this->addModCategory();
                $this->showCategories();
                break;
            case 'modAllCategories':
                $this->modAllCategories();
                $this->showCategories();
                break;
            case 'delProduct':
            case 'deleteProduct':
                self::$pageTitle = $_ARRAYLANG['TXT_PRODUCT_CATALOG'];
                $this->delProduct();
                $this->_products();
                break;
            case 'delcat':
                $this->delCategory();
                $this->showCategories();
                break;
            case 'edit':
                self::$pageTitle = $_ARRAYLANG['TXT_CATEGORIES'];
                $this->modModules();
                $this->showModules();
                break;
            case 'products':
                $this->_products();
                break;
            case 'orders':
                $this->shopShowOrders();
                break;
            case 'orderdetails':
                self::$pageTitle = $_ARRAYLANG['TXT_ORDER_DETAILS'];
                $this->shopShowOrderdetails('module_shop_order_details.html',0);
                break;
            case 'editorder':
                if (isset($_REQUEST['shopSaveOrderChanges'])) {
                    self::$pageTitle = $_ARRAYLANG['TXT_ORDER_DETAILS'];
                    $this->shopStoreOrderdetails();
                    $this->shopShowOrderdetails('module_shop_order_details.html',0);
                } else {
                    self::$pageTitle = $_ARRAYLANG['TXT_EDIT_ORDER'];
                    $this->shopShowOrderdetails('module_shop_order_edit.html',1);
                }
                break;
            case 'delorder':
                $this->shopDeleteOrder();
                $this->shopShowOrders();
                break;
            case 'customers':
                self::$pageTitle = $_ARRAYLANG['TXT_CUSTOMERS_PARTNERS'];
                $this->shopShowCustomers();
                break;
            case 'customerdetails':
                self::$pageTitle = $_ARRAYLANG['TXT_CUSTOMER_DETAILS'];
                $this->shopShowCustomerDetails();
                break;
            case 'neweditcustomer':
                $this->shopNewEditCustomer();
                break;
            case 'delcustomer':
                self::$pageTitle = $_ARRAYLANG['TXT_CUSTOMERS_PARTNERS'];
                $this->shopDeleteCustomer();
                $this->shopShowCustomers();
                break;
            case 'statistics':
                self::$pageTitle = $_ARRAYLANG['TXT_STATISTIC'];
                $this->shopOrderStatistics();
                break;
            case 'pricelist':
                self::$pageTitle = $_ARRAYLANG['TXT_PDF_OVERVIEW'];
                $this->shopPricelistOverview();
                break;
            case 'pricelist_new':
                self::$pageTitle = $_ARRAYLANG['TXT_MAKE_NEW_PRICELIST'];
                $this->shopPricelistNew();
                break;
            case 'pricelist_insert':
                self::$pageTitle = $_ARRAYLANG['TXT_PDF_OVERVIEW'];
                $this->shopPricelistInsert();
                $this->shopPricelistOverview();
                break;
            case 'pricelist_edit':
                self::$pageTitle = $_ARRAYLANG['TXT_PDF_OVERVIEW'];
                $pricelistID = intval($_GET['id']);
                $this->shopPricelistEdit($pricelistID);
                break;
            case 'pricelist_update':
                self::$pageTitle = $_ARRAYLANG['TXT_PDF_OVERVIEW'];
                $pricelistID = intval($_GET['id']);
                $this->shopPriceListUpdate($pricelistID);
                $this->shopPricelistOverview();
                break;
            case 'pricelist_delete':
                self::$pageTitle = $_ARRAYLANG['TXT_PDF_OVERVIEW'];
                $this->shopPricelistDelete();
                $this->shopPricelistOverview();
                break;
            case 'import':
                $this->_import();
                break;
            case 'manufacturer':
                $this->_manufacturer();
                break;
            default:
                $this->shopShowOrders();
                break;
        }

        $objTemplate->setVariable(array(
            'CONTENT_TITLE' => self::$pageTitle,
            'CONTENT_OK_MESSAGE' => self::$strOkMessage,
            'CONTENT_STATUS_MESSAGE' => self::$strErrMessage,
            'ADMIN_CONTENT' => self::$objTemplate->get(),
        ));
    }


    /**
     * Manage manufacturers
     */
    function _manufacturer()
    {
        global $_ARRAYLANG;

        self::$pageTitle = $_ARRAYLANG['TXT_SHOP_MANUFACTURER'];
        self::$objTemplate->loadTemplateFile('module_shop_manufacturer.html', true, true);

        $id = (!empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
        if (!empty($_REQUEST['exe'])) {
            $name = (!empty($_REQUEST['name']) ? $_REQUEST['name'] : '');
            $url = (!empty($_REQUEST['url'])  ? $_REQUEST['url']  : '');
            if (preg_match('/^(?:[\w\d\-]+\.)+[\w]+/i', $url)) {
                $url = "http://$url";
            }
            // Insert a new manufacturer
            if ($_REQUEST['exe'] == 'insert') {
                if (Manufacturer::insert($name, $url)) {
                    self::addMessage($_ARRAYLANG['TXT_SHOP_MANUFACTURER_INSERT_SUCCESS']);
                } else {
                    self::addError($_ARRAYLANG['TXT_SHOP_MANUFACTURER_INSERT_FAILED']);
                }
            }
            // Update a manufacturer
            if ($_REQUEST['exe'] == 'update' && $id > 0) {
                if (Manufacturer::update($name, $url, $id)) {
                    self::addMessage($_ARRAYLANG['TXT_SHOP_MANUFACTURER_UPDATE_SUCCESS']);
                } else {
                    self::addError($_ARRAYLANG['TXT_SHOP_MANUFACTURER_UPDATE_FAILED']);
                }
            }
            // Delete any single manufacturer
            if ($_REQUEST['exe'] == 'delete' && $id > 0) {
                if (Manufacturer::delete($id)) {
                    self::addMessage($_ARRAYLANG['TXT_SHOP_MANUFACTURER_DELETE_SUCCESS']);
                } else {
                    self::addError($_ARRAYLANG['TXT_SHOP_MANUFACTURER_DELETE_FAILED']);
                }
            }
            // Delete selected manufacturers
            if ($_REQUEST['exe'] == 'deleteList') {
                $result = true;
                foreach ($_POST['selectedManufacturerId'] as $id) {
                    if (!Manufacturer::delete($id)) {
                        $result = false;
                        break;
                    }
                }
                if ($result) {
                    self::addMessage($_ARRAYLANG['TXT_SHOP_MANUFACTURER_DELETE_SUCCESS']);
                } else {
                    self::addError($_ARRAYLANG['TXT_SHOP_MANUFACTURER_DELETE_FAILED']);
                }
            }
            // Clear old static data in the class, as it may have changed
            Manufacturer::reset();
        }

        $i = 1;
        $arrManufacturers = Manufacturer::getArray();
        foreach ($arrManufacturers as $manufacturer_id => $arrManufacturer) {
            self::$objTemplate->setVariable(array(
                'VALUE_ID' => $manufacturer_id,
                'VALUE_NAME' => $arrManufacturer['name'],
                'SHOP_ROWCLASS' => (++$i % 2 ? 'row1' : 'row2'),
            ));
            self::$objTemplate->parse("manufacturerRow");
        }
        if (   isset($_REQUEST['mode'])
            && $_REQUEST['mode'] == 'update'
            && isset($arrManufacturers[$id])) {
            // Edit the selected manufacturer
            self::$objTemplate->setVariable(array(
                'TXT_SHOP_INSERT_NEW_MANUFACTURER' => $_ARRAYLANG['TXT_SHOP_UPDATE_MANUFACTURER'],
                'VALUE_MANUFACTURER_NAME' => $arrManufacturers[$id]['name'],
                'VALUE_MANUFACTURER_URL' => $arrManufacturers[$id]['url'],
                'EXE_MODE' => 'update',
                'VALUE_ID' => $id,
            ));
        } else {
            // Insert a new Manufacturer
            self::$objTemplate->setVariable(array(
                'VALUE_MANUFACTURER_NAME' => '',
                'VALUE_MANUFACTURER_URL' => '',
                'EXE_MODE' => 'insert',
            ));
        }
    }


    /**
     * Import and Export data from/to csv
     * @author  Reto Kohli <reto.kohli@comvation.com> (parts)
     */
    function _import()
    {
        global $_ARRAYLANG, $objDatabase;

        self::$pageTitle = $_ARRAYLANG['TXT_SHOP_IMPORT_TITLE'];
        self::$objTemplate->loadTemplateFile('module_shop_import.html', true, true);

        // Delete template
        if (isset($_REQUEST['deleteImg'])) {
            $query = "
                DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_importimg
                 WHERE img_id=".$_REQUEST['img'];
            if ($objDatabase->Execute($query)) {
                self::addMessage($_ARRAYLANG['TXT_SHOP_IMPORT_SUCCESSFULLY_DELETED']);
            } else {
                self::addError($_ARRAYLANG['TXT_SHOP_IMPORT_ERROR_DELETE']);
            }
            $this->objCSVimport->initTemplateArray();
        }

        // Save template
        if (isset($_REQUEST['SaveImg'])) {
            $query = "
                INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_importimg (
                    img_name, img_cats, img_fields_file, img_fields_db
                ) VALUES (
                    '".$_REQUEST['ImgName']."',
                    '".$_REQUEST['category']."',
                    '".$_REQUEST['pairs_left_keys']."',
                    '".$_REQUEST['pairs_right_keys']."'
                )
            ";
            if ($objDatabase->Execute($query)) {
                self::addMessage($_ARRAYLANG['TXT_SHOP_IMPORT_SUCCESSFULLY_SAVED']);
            } else {
                self::addError($_ARRAYLANG['TXT_SHOP_IMPORT_ERROR_SAVE']);
            }
            $this->objCSVimport->initTemplateArray();
        }

        // Import Categories
        // This is not subject to change, so it's hardcoded
        if (isset($_REQUEST['ImportCategories'])) {
            // delete existing categories on request only!
            // mind that this necessarily also clears all products and
            // their associated attributes!
            if (isset($_POST['clearCategories']) && $_POST['clearCategories']) {
                $query = 'DELETE FROM '.DBPREFIX.'module_shop'.MODULE_INDEX.'_products';
                $objDatabase->Execute($query);
                $query = 'DELETE FROM '.DBPREFIX.'module_shop'.MODULE_INDEX.'_products_attributes';
                $objDatabase->Execute($query);
                $query = 'DELETE FROM '.DBPREFIX.'module_shop'.MODULE_INDEX.'_categories';
                $objDatabase->Execute($query);
            }
            $objCsv = new Csv_bv($_FILES['importFileCategories']['tmp_name']);
            $importedLines = 0;
            $arrCategoryLevel = array(0,0,0,0,0,0,0,0,0,0);
            $line = $objCsv->NextLine();
            while ($line) {
                $level = 0;
                foreach ($line as $catName) {
                    ++$level;
                    if (!empty($catName)) {
                        $parentCatId = $this->objCSVimport->getCategoryId(
                            $catName,
                            $arrCategoryLevel[$level-1]
                        );
                        $arrCategoryLevel[$level] = $parentCatId;
                    }
                }
                ++$importedLines;
                $line = $objCsv->NextLine();
            }
            self::addMessage($_ARRAYLANG['TXT_SHOP_IMPORT_SUCCESSFULLY_IMPORTED_CATEGORIES'].': '.$importedLines);
        }

        // Import
        if (isset($_REQUEST['importFileProducts'])) {
            if (isset($_POST['clearProducts']) && $_POST['clearProducts']) {
                $query = 'DELETE FROM '.DBPREFIX.'module_shop'.MODULE_INDEX.'_products';
                $objDatabase->Execute($query);
                $query = 'DELETE FROM '.DBPREFIX.'module_shop'.MODULE_INDEX.'_products_attributes';
                $objDatabase->Execute($query);
                // The categories need not be removed, but it is done by design!
                $query = 'DELETE FROM '.DBPREFIX.'module_shop'.MODULE_INDEX.'_categories';
                $objDatabase->Execute($query);
            }
            $arrFileContent = $this->objCSVimport->GetFileContent();
            $query = '
                SELECT img_id, img_name, img_cats, img_fields_file, img_fields_db
                  FROM '.DBPREFIX.'module_shop'.MODULE_INDEX.'_importimg
                 WHERE img_id='.$_REQUEST['ImportImage'];
            $objResult = $objDatabase->Execute($query);

            $arrCategoryName = preg_split(
                '/;/', $objResult->fields['img_cats'], null, PREG_SPLIT_NO_EMPTY
            );
            $arrFirstLine = $arrFileContent[0];
            $arrCategoryColumnIndex = array();
            for ($x=0; $x < count($arrCategoryName); ++$x) {
                foreach ($arrFirstLine as $index => $strColumnName) {
                    if ($strColumnName == $arrCategoryName[$x]) {
                        $arrCategoryColumnIndex[] = $index;
                    }
                }
            }

            $arrTemplateFieldName = preg_split(
                '/;/', $objResult->fields['img_fields_file'],
                null, PREG_SPLIT_NO_EMPTY
            );
            $arrDatabaseFieldIndex = array();
            for ($x=0; $x < count($arrTemplateFieldName); ++$x) {
                foreach ($arrFirstLine as $index => $strColumnName) {
                    if ($strColumnName == $arrTemplateFieldName[$x]) {
                        $arrDatabaseFieldIndex[] = $index;
                    }
                }
            }

            $arrProductFieldName = preg_split(
                '/;/', $objResult->fields['img_fields_db'],
                null, PREG_SPLIT_NO_EMPTY
            );
            $arrProductDatabaseFieldName = array();
            for ($x = 0; $x < count($arrProductFieldName); ++$x) {
                $dbname = $this->objCSVimport->DBfieldsName($arrProductFieldName[$x]);
                $arrProductDatabaseFieldName[$dbname] =
                    (isset($arrProductDatabaseFieldName[$dbname])
                        ? $arrProductDatabaseFieldName[$dbname].';'
                        : '').
                    $x;
            }

            $importedLines = 0;
            $errorLines = 0;
            // Array of IDs of newly inserted records
            $arrId = array();
            for ($x = 1; $x < count($arrFileContent); ++$x) {
                $strColumnNames = '';
                $strColumnValues = '';
                $counter = 0;
                foreach ($arrProductDatabaseFieldName as $index => $strFieldIndex) {
                    $strColumnNames .=
                        ($strColumnNames ? ',' : '').
                        $index;
                    if (strpos($strFieldIndex, ';')) {
                        $prod2line = split(';', $strFieldIndex);
                        $spaltenValuesTmp = '';
                        for ($z = 0; $z < count($prod2line); ++$z) {
                            $spaltenValuesTmp .=
                                $arrFileContent[$x][$arrDatabaseFieldIndex[$prod2line[$z]]].
                                '<br />';
                        }
                        $strColumnValues .=
                            ($strColumnValues ? ',' : '').
                            '"'.addslashes($spaltenValuesTmp).'"';
                    } else {
                        $strColumnValues .=
                            ($strColumnValues ? ',' : '').
                            '"'.addslashes($arrFileContent[$x][$arrDatabaseFieldIndex[$strFieldIndex]]).'"';
                    }
                    ++$counter;
                }
                $catId = false;
                for ($cat=0; $cat < count($arrCategoryColumnIndex); $cat++) {
                    $catName = $arrFileContent[$x][$arrCategoryColumnIndex[$cat]];
                    if (empty($catName)) {
                        $catId = $this->objCSVimport->GetFirstCat();
                    } else {
                        $catId = $this->objCSVimport->getCategoryId($catName, $catId);
                    }
                }
                if ($catId == 0) {
                    $catId = $this->objCSVimport->GetFirstCat();
                }
                $query = "
                    INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_products
                    ($strColumnNames, catid) VALUES ($strColumnValues, $catId)
                ";
                $objResult = $objDatabase->Execute($query);
                if ($objResult) {
                    $arrId[] = $objDatabase->Insert_ID();
                    ++$importedLines;
                } else {
                    ++$errorLines;
                }
            }

            // Fix picture field and create thumbnails
            $this->makeProductThumbnailsById($arrId);

            self::addMessage($_ARRAYLANG['TXT_SHOP_IMPORT_SUCCESSFULLY_IMPORTED_PRODUCTS'].': '.$importedLines);
            if ($errorLines) {
                self::addError($_ARRAYLANG['TXT_SHOP_IMPORT_NOT_SUCCESSFULLY_IMPORTED_PRODUCTS'].': '.$errorLines);
            }
        } // end import

        if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'ImportImg') {
            $jsSelectLayer = 'selectTab("import2");';
        } else {
            $jsSelectLayer = 'selectTab("import1");';
        }

        $noimg = '';
        $importButtonStyle = '';
        $arrTemplateArray = $this->objCSVimport->getTemplateArray();
        if (isset($_REQUEST['mode']) && $_REQUEST['mode'] != 'ImportImg') {
            if (count($arrTemplateArray) == 0) {
                $noimg = $_ARRAYLANG['TXT_SHOP_IMPORT_NO_TEMPLATES_AVAILABLE'];
                $importButtonStyle = 'style="display: none;"';
            } else {
                $noimg = "";
                $importButtonStyle = '';
            }
        } else {
            if (!isset($_REQUEST['SelectFields'])) {
                $jsnofiles = "selectTab('import1');";
            } else {
                if ($_FILES['CSVfile']['name'] == '') {
                    $jsnofiles = "selectTab('import4');";
                } else {
                    $jsnofiles = "selectTab('import2');";
                    $fileFields = $this->objCSVimport->getFilefieldMenuOptions();
                    $fileFields = '
                         <select name="FileFields" id="file_field" style="width: 200px;" size="10">
                             '.$fileFields.'
                         </select>
                     ';
                    $dblist = $this->objCSVimport->getAvailableNamesMenuOptions();
                    $dblist = '
                         <select name="DbFields" id="given_field" style="width: 200px;" size="10">
                             '.$dblist.'
                         </select>
                     ';
                }
            }
        }

        // Export groups -- hardcoded
        if (isset($_REQUEST['group'])) {
            $query = '';
            $fieldNames = '';
            switch ($_REQUEST['group']) {
                // products - plain fields:
                case 'tproduct':
                    $content_location = "ProdukteTabelle.csv";
                    $fieldNames = array(
                        'id', 'product_id', 'picture', 'title', 'catid', 'handler',
                        'normalprice', 'resellerprice', 'shortdesc', 'description',
                        'stock', 'stock_visibility', 'discountprice', 'is_special_offer',
                        'active', 'b2b', 'b2c', 'startdate', 'enddate',
                        'manufacturer', 'manufacturer_url', 'external_link',
                        'ord', 'vat_id', 'weight',
                        'flags', 'group_id', 'article_id', 'keywords', );
                    $query = "
                        SELECT id, product_id, picture, title, catid, handler,
                               normalprice, resellerprice, shortdesc, description,
                               stock, stock_visibility, discountprice, is_special_offer,
                               active, b2b, b2c, startdate, enddate,
                               manufacturer, manufacturer_url, external_link,
                               sort_order, vat_id, weight,
                               flags, group_id, article_id, keywords
                          FROM ".DBPREFIX."module_shop_products
                         ORDER BY id ASC";
                break;
                // products - custom:
                case 'rproduct':
                    $content_location = "ProdukteRelationen.csv";
                    $fieldNames = array(
                        'id', 'product_id', 'picture', 'title',
                        'catid', 'category', 'parentcategory', 'handler',
                        'normalprice', 'resellerprice', 'discountprice', 'is_special_offer',
                        'shortdesc', 'description',
                        'stock', 'stock_visibility',
                        'active', 'b2b', 'b2c',
                        'startdate', 'enddate',
                        'manufacturer_name', 'manufacturer_website',
                        'manufacturer_url', 'external_link',
                        'ord',
                        'vat_percent', 'weight',
                        'discount_group', 'article_group', 'keywords', );
                    // c1.catid *MUST NOT* be NULL
                    // c2.catid *MAY* be NULL (if c1.catid is root)
                    // vat_id *MAY* be NULL
                    $query = "
                        SELECT p.id, p.product_id, p.picture, p.title,
                               p.catid, c1.catname as category, c2.catname as parentcategory, p.handler,
                               p.normalprice, p.resellerprice, p.discountprice, p.is_special_offer,
                               p.shortdesc, p.description, p.stock, p.stock_visibility,
                               p.active, p.b2b, p.b2c, p.startdate, p.enddate,
                               m.name as manufacturer_name,
                               m.url as manufacturer_website,
                               p.manufacturer_url, p.external_link,
                               p.sort_order,
                               v.percent as vat_percent, p.weight,
                               d.name AS discount_group,
                               a.name AS article_group,
                               p.keywords
                          FROM ".DBPREFIX."module_shop_products p
                         INNER JOIN ".DBPREFIX."module_shop_categories c1 ON p.catid=c1.catid
                          LEFT JOIN ".DBPREFIX."module_shop_categories c2 ON c1.parentid=c2.catid
                          LEFT JOIN ".DBPREFIX."module_shop_vat v ON vat_id=v.id
                          LEFT JOIN ".DBPREFIX."module_shop_manufacturer as m ON m.id = p.manufacturer
                          LEFT JOIN ".DBPREFIX."module_shop_discountgroup_count_name as d ON d.id = p.group_id
                          LEFT JOIN ".DBPREFIX."module_shop_article_group as a ON a.id = p.article_id
                         ORDER BY catid ASC, product_id ASC";
                break;
                // customer - plain fields:
                case 'tcustomer':
                    $content_location = "KundenTabelle.csv";
                    $fieldNames = array(
                        'customerid', 'username', 'password', 'prefix', 'company', 'firstname', 'lastname',
                        'address', 'city', 'zip', 'country_id', 'phone', 'fax', 'email',
                        'ccnumber', 'ccdate', 'ccname', 'cvc_code', 'company_note',
                        'is_reseller', 'register_date', 'customer_status', 'group_id', );
                    $query = "
                        SELECT customerid, username, password, prefix, company, firstname, lastname,
                               address, city, zip, country_id, phone, fax, email,
                               ccnumber, ccdate, ccname, cvc_code, company_note,
                               is_reseller, register_date, customer_status,
                               group_id
                          FROM ".DBPREFIX."module_shop_customers
                         ORDER BY lastname ASC, firstname ASC";
                break;
                // customer - custom:
                case 'rcustomer':
                    $content_location = "KundenRelationen.csv";
                    $fieldNames = array(
                        'customerid', 'username', 'firstname', 'lastname', 'prefix', 'company',
                        'address', 'zip', 'city', 'countries_name',
                        'phone', 'fax', 'email', 'is_reseller', 'register_date', 'group_name', );
                    $query = "
                        SELECT c.customerid, c.username, c.firstname, c.lastname, c.prefix, c.company,
                               c.address, c.zip, c.city, n.countries_name,
                               c.phone, c.fax, c.email, c.is_reseller, c.register_date,
                               d.name AS group_name
                          FROM ".DBPREFIX."module_shop_customers c
                         INNER JOIN ".DBPREFIX."module_shop_countries n ON c.country_id=n.countries_id
                          LEFT JOIN ".DBPREFIX."module_shop_customer_group d ON c.group_id=d.id
                         ORDER BY c.lastname ASC, c.firstname ASC";
                break;
                // orders - plain fields:
                case 'torder':
                    $content_location = "BestellungenTabelle.csv";
                    $fieldNames = array(
                        'orderid', 'customerid', 'selected_currency_id', 'order_sum', 'currency_order_sum',
                        'order_date', 'order_status', 'ship_prefix', 'ship_company', 'ship_firstname', 'ship_lastname',
                        'ship_address', 'ship_city', 'ship_zip', 'ship_country_id', 'ship_phone',
                        'tax_price', 'currency_ship_price', 'shipping_id', 'payment_id', 'currency_payment_price',
                        'customer_ip', 'customer_host', 'customer_lang', 'customer_browser', 'customer_note',
                        'last_modified', 'modified_by');
                    $query =
                        "SELECT orderid, customerid, selected_currency_id, order_sum, currency_order_sum, ".
                        "order_date, order_status, ship_prefix, ship_company, ship_firstname, ship_lastname, ".
                        "ship_address, ship_city, ship_zip, ship_country_id, ship_phone, ".
                        "tax_price, currency_ship_price, shipping_id, payment_id, currency_payment_price, ".
                        "customer_ip, customer_host, customer_lang, customer_browser, customer_note, ".
                        "last_modified, modified_by ".
                        "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders ORDER BY orderid ASC";
                break;
                // orders - custom:
                case 'rorder':
                    $content_location = "BestellungenRelationen.csv";
                    $fieldNames = array(
                        'orderid', 'order_sum', 'tax_price', 'currency_ship_price', 'currency_payment_price',
                        'currency_order_sum', 'order_date', 'order_status', 'ship_prefix', 'ship_company',
                        'ship_firstname', 'ship_lastname', 'ship_address', 'ship_city', 'ship_zip',
                        'ship_phone', 'customer_note',
                        'customerid', 'username', 'firstname', 'lastname', 'prefix', 'company',
                        'address', 'zip', 'city', 'countries_name',
                        'phone', 'fax', 'email', 'is_reseller', 'register_date',
                        'currency_code', 'shipper_name', 'payment_name',
                        'account_number', 'bank_name', 'bank_code');
                    $query = "
                        SELECT o.orderid, o.order_sum, o.tax_price, o.currency_ship_price, o.currency_payment_price,
                               o.currency_order_sum, o.order_date, o.order_status, o.ship_prefix, o.ship_company,
                               o.ship_firstname, o.ship_lastname, o.ship_address, o.ship_city, o.ship_zip,
                               o.ship_phone, o.customer_note,
                               c.customerid, c.username, c.firstname, c.lastname, c.prefix, c.company,
                               c.address, c.zip, c.city, n.countries_name,
                               c.phone, c.fax, c.email, c.is_reseller, c.register_date,
                               u.code AS currency_code, s.name AS shipper_name, p.name AS payment_name,
                               l.holder, l.bank, l.blz
                          FROM ".DBPREFIX."module_shop_orders o
                         INNER JOIN ".DBPREFIX."module_shop_customers c ON o.customerid=c.customerid
                         INNER JOIN ".DBPREFIX."module_shop_countries n ON c.country_id=n.countries_id
                         INNER JOIN ".DBPREFIX."module_shop_currencies u ON o.selected_currency_id=u.id
                          LEFT JOIN ".DBPREFIX."module_shop_shipper s ON o.shipping_id=s.id
                          LEFT JOIN ".DBPREFIX."module_shop_payment p ON o.payment_id=p.id
                          LEFT JOIN ".DBPREFIX."module_shop_lsv l ON o.orderid=l.order_id
                         ORDER BY orderid ASC";
                break;
            } // switch

            if ($query && $objResult = $objDatabase->Execute($query)) {
                // field names
                $fileContent = '"'.join('";"', $fieldNames)."\"\n";
                while (!$objResult->EOF) {
                    $arrRow = $objResult->FetchRow();
                    $arrReplaced = array();
                    // Decode the pictures
                    foreach ($arrRow as $index => $field) {
                        if ($index == 'picture') {
                            $arrPictures = Products::getShopImagesFromBase64String($field);
                            $field =
                                'http://'.
                                $_SERVER['HTTP_HOST'].'/'.
                                ASCMS_SHOP_IMAGES_WEB_PATH.'/'.
                                $arrPictures[1]['img'];
                        }
                        $arrReplaced[] = str_replace('"', '""', $field);
                    }
                    $fileContent .= '"'.join('";"', $arrReplaced)."\"\n";
                }
                // Test the output for UTF8!
                if (strtoupper(CONTREXX_CHARSET) == 'UTF-8') {
                    $fileContent = utf8_decode($fileContent);
                }
                // set content to filename and -type for download
                header("Content-Disposition: inline; filename=$content_location");
                header("Content-Type: text/comma-separated-values");
                echo($fileContent);
                exit();
            } else {
                self::addError($_ARRAYLANG['TXT_SHOP_EXPORT_ERROR']);
            }
        } else {
            // can't submit without a group selection
        } // if/else group
        // end export

        // make sure that language entries exist for all of
        // TXT_SHOP_EXPORT_GROUP_*, TXT_SHOP_EXPORT_GROUP_*_TIP !!
        $arrGroups = array('tproduct', 'rproduct', 'tcustomer', 'rcustomer', 'torder', 'rorder');
        $tipText = '';
        for ($i = 0; $i < count($arrGroups); ++$i) {
            self::$objTemplate->setCurrentBlock('groupRow');
            self::$objTemplate->setVariable(array(
                'SHOP_EXPORT_GROUP' => $_ARRAYLANG['TXT_SHOP_EXPORT_GROUP_'.strtoupper($arrGroups[$i])],
                'SHOP_EXPORT_GROUP_CODE' => $arrGroups[$i],
                'SHOP_EXPORT_INDEX' => $i,
                'CLASS_NAME' => ($i % 2 ? 'row1' : 'row2'),
            ));
            self::$objTemplate->parse('groupRow');
            $tipText .= 'Text['.$i.']=["","'.$_ARRAYLANG['TXT_SHOP_EXPORT_GROUP_'.strtoupper($arrGroups[$i]).'_TIP'].'"];';
        }

        $imageChoice = $this->objCSVimport->GetImageChoice($noimg);
        $arrTemplateArray = $this->objCSVimport->getTemplateArray();
        self::$objTemplate->setCurrentBlock('imgRow');
        for ($x = 0; $x < count($arrTemplateArray); ++$x) {
            self::$objTemplate->setVariable(array(
                'IMG_NAME' => $arrTemplateArray[$x]['name'],
                'IMG_ID' => $arrTemplateArray[$x]['id'],
                'CLASS_NAME' => ($x % 2 ? 'row2' : 'row1'),
                // cms offset fix for admin images/icons:
                'SHOP_CMS_OFFSET' => ASCMS_PATH_OFFSET,
            ));
            self::$objTemplate->parse('imgRow');
        }

        self::$objTemplate->setVariable(array(
            'SELECT_LAYER_ONLOAD' => $jsSelectLayer,
            'NO_FILES' => (isset($jsnofiles)  ? $jsnofiles  : ''),
            'FILE_FIELDS_LIST' => (isset($fileFields) ? $fileFields : ''),
            'DB_FIELDS_LIST' => (isset($dblist)     ? $dblist     : ''),
            'IMAGE_CHOICE' => $imageChoice,
            'IMPORT_BUTTON_STYLE' => $importButtonStyle,
            // Export: instructions added
            'SHOP_EXPORT_TIPS' => $tipText,
        ));
    }


    /**
     * Create thumbnails and update corresponding Product records
     *
     * Scans all Products with their IDs listed in the array.  If a non-empty
     * picture string is encountered, tries to load the file of the same name
     * and to create a thumbnail.  If it succeeds, it also updates the
     * original records' picture field with the fixed entry.
     * Note that only single file names are supported!
     * Also note that this method only returns false upon encountering
     * a database error.  It silently skips records which contain no or
     * invalid image names, thumbnails that cannot be created, and records
     * which refuse to be updated!
     * The reasoning behind this is that this method is currently only called
     * from within the {@link _import()} method.  The focus lies on importing
     * Products; whether or not thumbnails can be created is secondary, as the
     * process can be repeated if there is a problem.
     * @param   array   $arrId  Array of Product IDs
     * @return  boolean         True on success, false otherwise.
     *                          Note that everything except an illegal
     *                          argument (a non-array) is considered a
     *                          success!
     * @internal    NTH: Implement a simple and elegant way to notify the user
     *              when errors occur while creating the thumbnails
     */
    function makeProductThumbnailsById($arrId)
    {
        global $objDatabase, $_CONFIG;
        require_once ASCMS_FRAMEWORK_PATH."/Image.class.php";

        if (!is_array($arrId)) return false;
        $objImageManager = new ImageManager();
        foreach ($arrId as $id) {
            $picture = '';
            $query = "
                SELECT picture
                FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
                WHERE id=$id
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) continue;
            $imageName = $objResult->fields['picture'];
            // only try to create thumbs from entries that contain a
            // plain text file name (i.e. from an import)
            if (   $imageName == ''
                || !preg_match('/\.(?:jpg|jpeg|gif|png)$/', $imageName))
                continue;
            // Note:  Old thumb is deleted in _createThumbWhq()
            // reset the ImageManager
            $objImageManager->imageCheck = 1;
            // create thumbnail
            if ($objImageManager->_createThumbWhq(
                ASCMS_SHOP_IMAGES_PATH.'/',
                ASCMS_SHOP_IMAGES_WEB_PATH.'/',
                $imageName,
                $_CONFIG['thumbnail_max_width'],
                $_CONFIG['thumbnail_max_height'],
                $_CONFIG['thumbnail_quality']
            )) {
                $width = $objImageManager->orgImageWidth;
                $height = $objImageManager->orgImageHeight;
                $picture =
                    base64_encode($imageName).
                    '?'.base64_encode($width).
                    '?'.base64_encode($height).'::';
                $query = "
                    UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_products
                    SET picture='$picture'
                    WHERE id=$id
                ";
                $objResult = $objDatabase->Execute($query);
            }
        }
        return true;
    }


    /**
     * Show product attributes page
     *
     * Show the settings for the attributes of the products
     *
     */
    function _showAttributes()
    {
        global $_ARRAYLANG;

        self::$pageTitle = $_ARRAYLANG['TXT_PRODUCT_CHARACTERISTICS'];
        self::$objTemplate->addBlockfile('SHOP_PRODUCTS_FILE', 'shop_products_block', 'module_shop_product_attributes.html');
        self::addError($this->_showAttributeOptions());
    }


    /**
     * Show attribute options
     *
     * Generate the attribute option/value list for its configuration
     * @access    private
     */
    function _showAttributeOptions()
    {
        global $_ARRAYLANG, $_CONFIG;

        // delete option
        if (isset($_GET['delId']) && !empty($_GET['delId'])) {
            self::addError($this->_deleteAttributeOption($_GET['delId']));
        } elseif (!empty($_GET['delProduct']) && !empty($_POST['selectedOptionId'])) {
            self::addError($this->_deleteAttributeOption($_POST['selectedOptionId']));
        }
        // store new option
        if (!empty($_POST['addAttributeOption']))
            $this->_storeNewAttributeOption();
        // update attribute options
        if (!empty($_POST['updateAttributeOptions']))
            $this->_updateAttributeOptions();
        // Clear the Product Attribute data present in Attributes.
        // This may have been changed above and would thus be out of date.
        Attributes::reset();

        $count = 0;
        $pos = (isset($_REQUEST['pos'])
            ? intval($_REQUEST['pos'])
            : (isset($_SESSION['shop']['pos_attribute'])
                ? $_SESSION['shop']['pos_attribute'] : 0));
        $_SESSION['shop']['pos_attribute'] = $pos;
        $limit = $_CONFIG['corePagingLimit'];
        $order = "`id` ASC";
        $filter = (isset($_REQUEST['filter'])
            ? contrexx_stripslashes($_REQUEST['filter']) : null);
        $arrAttributes = ProductAttribute::getArray(
            $count, $pos, $limit, $order, $filter);
//DBG::log("shopmanager::_showAttributeOptions(): count ".count($arrAttributes)." of $count, pos $pos, limit $limit, order $order, filter $filter");
        $rowClass = 1;

// TODO: Test and fix

        foreach ($arrAttributes as $attribute_id => $arrAttributeName) {
            self::$objTemplate->setCurrentBlock('attributeList');
            self::$objTemplate->setVariable(array(
                'SHOP_PRODUCT_ATTRIBUTE_ROW_CLASS' => (++$rowClass % 2 ? 'row2' : 'row1'),
                'SHOP_PRODUCT_ATTRIBUTE_ID' => $attribute_id,
                'SHOP_PRODUCT_ATTRIBUTE_NAME' => $arrAttributeName['name'],
                'SHOP_PRODUCT_ATTRIBUTE_VALUE_MENU' =>
                    Attributes::getOptionMenu(
                        $attribute_id, 'option_id', '',
                        'setSelectedValue('.$attribute_id.')', 'width: 200px;'),
                'SHOP_PRODUCT_ATTRIBUTE_VALUE_INPUTBOXES' =>
                    Attributes::getInputs(
                        $attribute_id, 'option_value', 'value',
                        255, 'width: 170px;'),
                'SHOP_PRODUCT_ATTRIBUTE_PRICE_INPUTBOXES' =>
                    Attributes::getInputs(
                        $attribute_id, 'attributePrice', 'price',
                        9, 'width: 170px; text-align: right;'),
                'SHOP_PRODUCT_ATTRIBUTE_DISPLAY_TYPE' =>
                    Attributes::getDisplayTypeMenu(
                        $attribute_id, $arrAttributeName['type'],
                        'updateOptionList('.$attribute_id.')'),
            ));
            self::$objTemplate->parseCurrentBlock();
        }
        // The same for a new Attribute
        self::$objTemplate->setVariable(array(
            'SHOP_PRODUCT_ATTRIBUTE_TYPE_MENU' =>
                Attributes::getDisplayTypeMenu(
                    0, 0, 'updateOptionList(0)'),
            'SHOP_PRODUCT_ATTRIBUTE_JS_VARS' =>
                Attributes::getAttributeJSVars(),
            'SHOP_PRODUCT_ATTRIBUTE_CURRENCY' => Currency::getDefaultCurrencySymbol(),
            'SHOP_PAGING' => getPaging(
                $count, $pos,
                '&cmd=shop&act=products&tpl=attributes',
                $_ARRAYLANG['TXT_PRODUCT_CHARACTERISTICS'],
                true, $limit),
        ));
    }


    /**
     * Get attribute list
     *
     * Generate the standard attribute option/value list or the one of a product
     * @access  private
     * @param   string    $product_id    Product Id of which its list will be displayed
     */
    function _getAttributeList($product_id=0)
    {
        $i = 0;
        foreach (Attributes::getNameArray() as $attribute_id => $arrAttributeName) {
            $arrRelation = array();
            // If a Product is selected, check those Product Attribute values
            // associated with it
            if ($product_id)
                $arrRelation = Attributes::getRelationArray($product_id);
            // All values available for this Product Attribute
            $arrOptions = Attributes::getOptionArrayByAttributeId($attribute_id);

            $nameSelected = false;
            $order = 0;
            foreach ($arrOptions as $option_id => $arrOption) {
                if (in_array($option_id, array_keys($arrRelation))) {
                    $valueSelected = true;
                    $nameSelected = true;
                    $order = $arrRelation[$option_id];
                } else {
                    $valueSelected = false;
                }
                self::$objTemplate->setVariable(array(
                    'SHOP_PRODUCTS_ATTRIBUTE_ID' => $attribute_id,
                    'SHOP_PRODUCTS_ATTRIBUTE_VALUE_ID' => $option_id,
                    'SHOP_PRODUCTS_ATTRIBUTE_VALUE_TEXT' => $arrOption['value'].
                        ' ('.$arrOption['price'].' '.Currency::getDefaultCurrencySymbol().')',
                    'SHOP_PRODUCTS_ATTRIBUTE_VALUE_SELECTED' => ($valueSelected ? HTML_ATTRIBUTE_CHECKED : ''),
                ));
                self::$objTemplate->parse('optionList');
            }
            self::$objTemplate->setVariable(array(
                'SHOP_PRODUCTS_ATTRIBUTE_ROW_CLASS' => (++$i % 2 ? 'row1' : 'row2'),
                'SHOP_PRODUCTS_ATTRIBUTE_ID' => $attribute_id,
                'SHOP_PRODUCTS_ATTRIBUTE_NAME' => $arrAttributeName['name'],
                'SHOP_PRODUCTS_ATTRIBUTE_SELECTED' => ($nameSelected ? HTML_ATTRIBUTE_CHECKED : ''),
                'SHOP_PRODUCTS_ATTRIBUTE_DISPLAY_TYPE' => ($nameSelected ? 'block' : 'none'),
                'SHOP_PRODUCTS_ATTRIBUTE_SORTID' => $order,
            ));
            self::$objTemplate->parse('attributeList');
        }
    }


    /**
     * Store a new attribute option
     * @access    private
     * @return    string    $statusMessage    Status message
     */
    function _storeNewAttributeOption()
    {
        global $_ARRAYLANG;

        $arrAttributeList = array();
        $arrOptionValue = array();
        $arrOptionPrice = array();
        if (empty($_POST['option_name'][0])) {
            return $_ARRAYLANG['TXT_DEFINE_NAME_FOR_OPTION'];
        } elseif (!is_array($_POST['option_id'][0])) {
            return $_ARRAYLANG['TXT_DEFINE_VALUE_FOR_OPTION'];
        }
        $arrAttributeList = $_POST['option_id'];
        $arrOptionValue =
            (isset($_POST['option_value'])
                ? $_POST['option_value'] : array()
            );
        $arrOptionPrice = $_POST['attributePrice'];
        $objAttribute = new Attribute(
            intval($_POST['attributeDisplayType'][0])
        );
        $objAttribute->setName($_POST['option_name'][0]);
        foreach ($arrAttributeList[0] as $id) {
            $objAttribute->addOption(
                $arrOptionValue[$id],
                $arrOptionPrice[$id]
            );
        }
        if (!$objAttribute->store())
            return $_ARRAYLANG['TXT_SHOP_ERROR_INSERTING_PRODUCTATTRIBUTE'];
        return '';
    }


    /**
     * Update attribute options
     *
     * Update the attribute option/value list
     * @access    private
     * @return    string    $statusMessage    Status message
     */
    function _updateAttributeOptions()
    {
        global $objDatabase, $_ARRAYLANG;

        $arrAttributeName = $_POST['option_name'];
        $arrAttributeType = $_POST['attributeDisplayType'];
        $arrAttributeList = $_POST['option_id'];
        $arrOptionValue = $_POST['option_value'];
        $arrOptionPrice = $_POST['attributePrice'];

        foreach ($arrAttributeList as $attribute_id => $arrOptionIds) {
            $flagChanged = false;
            $objAttribute = Attribute::getById($attribute_id);
            if (!$objAttribute) {
                self::addError($_ARRAYLANG['TXT_SHOP_ERROR_UPDATING_RECORD']);
                return false;
            }

            $name = $arrAttributeName[$attribute_id];
            $type = $arrAttributeType[$attribute_id];
            if (   $name != $objAttribute->getName()
                || $type != $objAttribute->getType()) {
                $objAttribute->setName($name);
                $objAttribute->setType($type);
                $flagChanged = true;
            }

            $arrOptions = $objAttribute->getOptionArray();
            foreach ($arrOptionIds as $option_id) {
                // Make sure these values are defined if empty:
                // The option name and price
                if (empty($arrOptionValue[$option_id]))
                    $arrOptionValue[$option_id] = '';
                if (empty($arrOptionPrice[$option_id]))
                    $arrOptionPrice[$option_id] = '0.00';
                if (isset($arrOptions[$option_id])) {
                    if (   $arrOptionValue[$option_id] != $arrOptions[$option_id]['value']
                        || $arrOptionPrice[$option_id] != $arrOptions[$option_id]['price']) {
                        $objAttribute->changeValue($option_id, $arrOptionValue[$option_id], $arrOptionPrice[$option_id]);
                        $flagChanged = true;
                    }
                } else {
                    $objAttribute->addOption($arrOptionValue[$option_id], $arrOptionPrice[$option_id]);
                    $flagChanged = true;
                }
            }

            // Delete values that are no longer present in the post
            foreach (array_keys($arrOptions) as $option_id) {
                if (!in_array($option_id, $arrAttributeList[$attribute_id])) {
                    $objAttribute->deleteValueById($option_id);
                }
            }

            if ($flagChanged) {
                if (!$objAttribute->store()) {
                    self::addError($_ARRAYLANG['TXT_SHOP_ERROR_UPDATING_RECORD']);
                    return false;
                } else {
                    self::addMessage($_ARRAYLANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL']);
                }
            }
        }

/*
        // Delete Product Attributes with no values
        foreach (array_keys(Attributes::getNameArray()) as $attribute_id) {
            if (!array_key_exists($attribute_id, $arrAttributeList)) {
                $objAttribute = Attribute::getById($attribute_id);
                if (!$objAttribute)
                    return $_ARRAYLANG['TXT_SHOP_ERROR_UPDATING_RECORD'];
                if (!$objAttribute->delete())
                    return $_ARRAYLANG['TXT_SHOP_ERROR_UPDATING_RECORD'];
            }
        }
*/
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop_products_attributes_value");
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop_products_attributes_name");
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop_products_attributes");
        return true;
    }


    /**
     * Delete one or more Product Attribute options
     * @access  private
     * @param   mixed     $option_id    The option ID or an array of IDs
     * @return  string                  Status message
     */
    function _deleteAttributeOption($option_id)
    {
        global $objDatabase, $_ARRAYLANG;

        if (!is_array($option_id)) {
            $arrOptionId = array($option_id);
        } else {
            $arrOptionId = &$option_id;
        }
        foreach ($arrOptionId as $option_id) {
            if (!Attributes::deleteValueById($option_id))
                return $_ARRAYLANG['TXT_SHOP_ERROR_UPDATING_RECORD'];
        }
        self::addMessage($_ARRAYLANG['TXT_OPTION_SUCCESSFULLY_DELETED']);
        return '';
    }


    /**
     * Set up the common elements for various settings pages
     *
     * Includes VAT, shipping, countries, zones and more
     * @access private
     */
    function _showSettings()
    {
        global $objDatabase, $_ARRAYLANG, $_CORELANG;

        // added return value. If empty, no error occurred
        $success = ShopSettings::storeSettings();
        if ($success) {
            self::addMessage($_ARRAYLANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL']);
        } elseif ($success === false) {
            self::addError($_ARRAYLANG['TXT_DATABASE_QUERY_ERROR']);
        }
        // $success may also be '', in which case no changed setting has
        // been detected.
        // Refresh the Settings, so changes are made visible right away
        SettingDb::flush();

        $i = 0;
        self::$pageTitle= $_ARRAYLANG['TXT_SETTINGS'];
        self::$objTemplate->loadTemplateFile('module_shop_settings.html', true, true);

        if (empty($_GET['tpl'])) $_GET['tpl'] = '';
        switch ($_GET['tpl']) {
            case 'currency':
                self::$objTemplate->addBlockfile('SHOP_SETTINGS_FILE', 'settings_block', 'module_shop_settings_currency.html');
                self::$objTemplate->setCurrentBlock('shopCurrency');
                foreach (Currency::getCurrencyArray() as $currency) {
                    $activeCheck = ($currency['active'] ? HTML_ATTRIBUTE_CHECKED : '');
                    $standardCheck = ($currency['default'] ? HTML_ATTRIBUTE_CHECKED : '');
                    self::$objTemplate->setVariable(array(
                        'SHOP_CURRENCY_STYLE' => (++$i % 2 ? 'row1' : 'row2'),
                        'SHOP_CURRENCY_ID' => $currency['id'],
                        'SHOP_CURRENCY_CODE' => $currency['code'],
                        'SHOP_CURRENCY_SYMBOL' => $currency['symbol'],
                        'SHOP_CURRENCY_NAME' => $currency['name'],
                        'SHOP_CURRENCY_RATE' => $currency['rate'],
                        'SHOP_CURRENCY_ACTIVE' => $activeCheck,
                        'SHOP_CURRENCY_STANDARD' => $standardCheck
                    ));
                    self::$objTemplate->parseCurrentBlock();
                }
                break;
            case 'payment':
                self::$objTemplate->addBlockfile('SHOP_SETTINGS_FILE', 'settings_block', 'module_shop_settings_payment.html');
                self::$objTemplate->setCurrentBlock('shopPayment');
                require_once ASCMS_MODULE_PATH.'/shop/lib/PaymentProcessing.class.php';
                foreach (Payment::getArray() as $id => $data) {
// TODO: Move to Zones class
                    $query = "
                        SELECT r.zone_id
                          FROM ".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment AS r
                          JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_zones AS z
                            ON z.id=r.zone_id
                         WHERE z.active=1
                           AND r.payment_id=".$data['id'];
                    $objResult = $objDatabase->Execute($query);
                    if (!$objResult->EOF) {
                        $zone_id = $objResult->fields['zone_id'];
                    } else {
                        $zone_id = 0;
                    }
                    self::$objTemplate->setVariable(array(
                        'SHOP_PAYMENT_STYLE' => 'row'.(++$i % 2 + 1),
                        'SHOP_PAYMENT_ID' => $data['id'],
                        'SHOP_PAYMENT_NAME' => $data['name'],
                        'SHOP_PAYMENT_HANDLER_MENUOPTIONS' =>
                            PaymentProcessing::getMenuoptions($data['processor_id']),
                        'SHOP_PAYMENT_COST' => $data['fee'],
                        'SHOP_PAYMENT_COST_FREE_SUM' => $data['free_from'],
                        'SHOP_ZONE_SELECTION' => Zones::getMenu(
                            $zone_id, 'paymentZone['.$data['id'].']'
                        ),
                        'SHOP_PAYMENT_STATUS' => (intval($data['active'])
                            ? HTML_ATTRIBUTE_CHECKED : ''),
                    ));
                    self::$objTemplate->parseCurrentBlock();
                }
                self::$objTemplate->setVariable(array(
                    'SHOP_PAYMENT_HANDLER_MENUOPTIONS_NEW' =>
                        // Selected PSP ID is -1 to disable the "please select" option
                        PaymentProcessing::getMenuoptions(-1),
                    'SHOP_ZONE_SELECTION_NEW' => Zones::getMenu(0, 'paymentZone_new'),
                ));
                break;
            case 'shipment':
                // start show shipment
                self::$objTemplate->addBlockfile('SHOP_SETTINGS_FILE', 'settings_block', 'module_shop_settings_shipment.html');
                self::$objTemplate->setGlobalVariable(
                    'SHOP_CURRENCY', Currency::getDefaultCurrencySymbol()
                );

                $arrShipments = Shipment::getShipmentsArray();
                $i = 0;
                foreach (Shipment::getShippersArray() as $sid => $arrShipper) {
// TODO: Move to Zones class
                    $query = "
                        SELECT `r`.`zone_id`
                          FROM `".DBPREFIX."module_shop".MODULE_INDEX."_rel_shipment` AS `r`,
                               `".DBPREFIX."module_shop".MODULE_INDEX."_zones` AS `z`
                         WHERE `z`.`active`=1
                           AND `z`.`id`=`r`.`zone_id`
                           AND `r`.`shipper_id`=$sid";
                    $objResult = $objDatabase->Execute($query);
                    if (!$objResult->EOF) {
                        $zone_id = $objResult->fields['zone_id'];
                    } else {
                        $zone_id = 0;
                    }
                    // fill inner block first (outer block first doesn't seem to work!)
                    self::$objTemplate->setCurrentBlock('shopShipment');
                    // show all possible shipment conditions for each shipper
                    if (isset($arrShipments[$sid])) {
                        foreach ($arrShipments[$sid] as $cid => $arrConditions) {
                            self::$objTemplate->setVariable(array(
                                'SHOP_SHIPMENT_STYLE' => (++$i % 2 ? 'row1' : 'row2'),
                                'SHOP_SHIPPER_ID' => $sid,
                                'SHOP_SHIPMENT_ID' => $cid,
                                'SHOP_SHIPMENT_MAX_WEIGHT' => $arrConditions['max_weight'],
                                'SHOP_SHIPMENT_PRICE_FREE' => $arrConditions['free_from'],
                                'SHOP_SHIPMENT_COST' => $arrConditions['fee'],
                            ));
                            //self::$objTemplate->parseCurrentBlock();
                            self::$objTemplate->parse('shopShipment');
                        }
                    }

                    // parse outer block after inner block (see above for why)
                    self::$objTemplate->setCurrentBlock('shopShipper');
                    self::$objTemplate->setVariable(array(
                        'SHOP_SHIPMENT_STYLE' => (++$i % 2 ? 'row1' : 'row2'),
                        'SHOP_SHIPPER_ID' => $sid,
                        'SHOP_SHIPPER_MENU' => Shipment::getShipperMenu(0, $sid),
                        'SHOP_ZONE_SELECTION' => Zones::getMenu($zone_id, 'shipmentZone['.$sid.']'),
                        'SHOP_SHIPPER_STATUS' => ($arrShipper['active'] ? HTML_ATTRIBUTE_CHECKED : ''),
                        // field not used anymore
                        //'SHOP_SHIPMENT_LANG_ID' => $this->_getLanguageMenu("shipmentLanguage[$sid]", $val['lang_id']),
                    ));
                    self::$objTemplate->parse('shopShipper');
                }
                self::$objTemplate->setVariable(
                    'SHOP_ZONE_SELECTION_NEW', Zones::getMenu(0, 'shipmentZoneNew')
                );
                // end show shipment
                break;
            case 'countries':
                // start show countries
                self::$objTemplate->addBlockfile('SHOP_SETTINGS_FILE', 'settings_block', 'module_shop_settings_countries.html');
                $selected = '';
                $notSelected = '';
                foreach (Country::getArray($count=0) as $country_id => $arrCountry) {
                    if (empty($arrCountry['active'])) {
                        $notSelected .=
                            '<option value="'.$country_id.'">'.
                            $arrCountry['name']."</option>\n";
                    } else {
                        $selected .=
                            '<option value="'.$country_id.'">'.
                            $arrCountry['name']."</option>\n";
                    }
                }
                self::$objTemplate->setVariable(array(
                    'SHOP_COUNTRY_SELECTED_OPTIONS' => $selected,
                    'SHOP_COUNTRY_NOTSELECTED_OPTIONS' => $notSelected,
                ));
                // end show countries
                break;
            case 'zones':
                // start show zones
                self::$objTemplate->addBlockfile('SHOP_SETTINGS_FILE', 'settings_block', 'module_shop_settings_zones.html');
                $arrZones = Zones::getZoneArray();
                $selectFirst = false;
                $strZoneOptions = '';
                foreach ($arrZones as $zone_id => $arrZone) {
                    // Skip zone "All"
                    if ($zone_id == 1) continue;
                    $strZoneOptions .=
                        '<option value="'.$zone_id.'"'.
                        ($selectFirst ? '' : HTML_ATTRIBUTE_SELECTED).
                        '>'.$arrZone['name']."</option>\n";
                    $arrCountryInZone = Country::getArraysByZoneId($zone_id);
                    $strSelectedCountries = '';
                    foreach ($arrCountryInZone['in'] as $country_id => $arrCountry) {
                        $strSelectedCountries .=
                            '<option value="'.$country_id.'">'.
                            $arrCountry['name'].
                            "</option>\n";
                    }
                    $strCountryList = '';
                    foreach ($arrCountryInZone['out'] as $country_id => $arrCountry) {
                        $strCountryList .=
                            '<option value="'.$country_id.'">'.
                            $arrCountry['name'].
                            "</option>\n";
                    }
                    self::$objTemplate->setVariable(array(
                        'SHOP_ZONE_ID' => $zone_id,
                        'ZONE_ACTIVE_STATUS' => ($arrZone['active'] ? HTML_ATTRIBUTE_CHECKED : '') ,
                        'SHOP_ZONE_NAME' => $arrZone['name'],
                        'SHOP_ZONE_DISPLAY_STYLE' => ($selectFirst ? 'display: none;' : 'display: block;'),
                        'SHOP_ZONE_SELECTED_COUNTRIES_OPTIONS' => $strSelectedCountries,
                        'SHOP_COUNTRY_LIST_OPTIONS' => $strCountryList
                    ));
                    self::$objTemplate->parse('shopZones');
                    $selectFirst = true;
                }
                self::$objTemplate->setVariable(array(
                    'SHOP_ZONES_OPTIONS' => $strZoneOptions,
                    'SHOP_ZONE_COUNTRY_LIST' => Country::getMenuoptions(),
                ));
                break;
            case 'mail':
                $strMailSelectedTemplates = '';
                $strMailTemplates = '';
                $arrLanguage = FWLanguage::getLanguageArray();
                self::$objTemplate->addBlockfile('SHOP_SETTINGS_FILE', 'settings_block', 'module_shop_settings_mail.html');
                self::$objTemplate->setVariable(array(
                    'TXT_SHOP_ADD_EDIT' => $_ARRAYLANG['TXT_ADD'],
                    'SHOP_MAIL_HTML_CHECKBOX' => Html::getCheckbox('html', 1),
                    'SHOP_MAIL_MSG_HTML' => get_wysiwyg_editor(
                        'message_html', ''),
                ));
                self::$objTemplate->setGlobalVariable(array(
                    'SHOP_MAIL_COLS' => count($arrLanguage) + 2,
                    'SHOP_MAIL_LANG_COL_WIDTH' => intval(70 / count($arrLanguage))
                ));
                // send template
                if (!empty($_POST['send'])) {
                    if (!empty($_POST['to'])) {
                        $strMailTos = contrexx_stripslashes($_POST['to']);
                        $message_html = (isset($_POST['message_html'])
                            ? contrexx_stripslashes($_POST['message_html']) : '');
                        $html = !empty($message_html);
                        $arrMailtemplate = array(
                            'from' => contrexx_stripslashes($_POST['from']),
                            'sender' => contrexx_stripslashes($_POST['sender']),
                            'subject' => contrexx_stripslashes($_POST['subject']),
                            'message' => contrexx_stripslashes($_POST['message']),
                            'message_html' => $message_html,
                            'html' => $html,
                            'to' => $strMailTos,
                        );
                        if (MailTemplate::send($arrMailtemplate)) {
                            self::addMessage(sprintf($_ARRAYLANG['TXT_EMAIL_SEND_SUCCESSFULLY'], $strMailTos));
                        } else {
                            self::addError($_ARRAYLANG['TXT_MESSAGE_SEND_ERROR']);
                        }
                    } else {
                        self::addError($_ARRAYLANG['TXT_SHOP_PLEASE_SET_RECIPIENT_ADDRESS']);
                    }
                }

                if (isset($_REQUEST['delTplId'])) {
                    if (MailTemplate::deleteTemplate(
                        contrexx_stripslashes($_REQUEST['delTplId']))) {
                        self::addMessage(MailTemplate::getMessages());
                    } else {
                        self::addError(MailTemplate::getErrors());
                    }
                }
                // Generate title row of the template list
                $defaultLang = FWLanguage::getDefaultLangId();
                foreach ($arrLanguage as $lang_id => $langValues) {
                    if (!$langValues['frontend']) continue;
                    self::$objTemplate->setVariable(
                        'SHOP_MAIL_LANGUAGE', $langValues['name']);
                    self::$objTemplate->parse('shopMailLanguages');
                }
                // Get a list of all Template keys
                $count = 0;
                $arrTemplates = MailTemplate::getTemplateArray(0, '', 0, null, $count);
                // Generate rows of the template list with the availability icon
                foreach (array_keys($arrTemplates) as $key) {
                    $template_name = '';
                    foreach ($arrLanguage as $lang_id => $langValues) {
                        if (!$langValues['frontend']) continue;
                        $arrTemplate = MailTemplate::getTemplate($key, $lang_id);
                        self::$objTemplate->setVariable(
                            'SHOP_MAIL_STATUS',
                                '<a href="javascript:loadTpl(\''.$key.'\','.
                                $lang_id.',\'shopMailEdit\')" title="'.
                                $_CORELANG['TXT_CORE_MAILTEMPLATE_EDIT'].
                                '"><img src="images/icons/'.
                                ($arrTemplate['available']
                                  ? 'edit' : 'newdoc').
                                '.gif" width="15" height="15" alt="'.
                                $_CORELANG['TXT_CORE_MAILTEMPLATE_NEW'].
                                '" border="0" /></a>'
                        );
                        self::$objTemplate->parse('shopMailLanguagesStatus');
                        if ($lang_id == FRONTEND_LANG_ID)
                            $template_name = $arrTemplate['name'];
                    }
                    $template_protected =
                        ($arrTemplate['protected']
                          ? '&nbsp;('.$_ARRAYLANG['TXT_SYSTEM_TEMPLATE'].')'
                          : ''
                        );
                    self::$objTemplate->setVariable(array(
                        'SHOP_TEMPLATE_ID' => $key,
                        'SHOP_LANGUAGE_ID' => $defaultLang,
                        'SHOP_MAIL_TEMPLATE_NAME' => $template_name.$template_protected,
                        'SHOP_MAIL_CLASS' => 'row'.(++$i % 2 + 1),
                    ));
                    self::$objTemplate->parse('shopMailTemplates');
                    // generate dropdown template-list
                    $strMailSelectedTemplates .=
                        '<option value="'.$key.'" '.
                        (   !empty($_GET['tplId']) && $_GET['tplId'] == $key
                            ? HTML_ATTRIBUTE_SELECTED : '').
                        '>'.$template_name.$template_protected."</option>\n";
                    $strMailTemplates .=
                        '<option value="'.$key.'">'.
                        $template_name.$template_protected.
                        "</option>\n";
                    // get the name of the loaded template to edit
                    if (!empty($_GET['tplId']) && $_GET['strTab'] == 'shopMailEdit') {
                        if ($key == $_GET['tplId']) {
                            self::$objTemplate->setVariable(
                                'SHOP_MAIL_TEMPLATE', $template_name);
                        }
                    }
                }
                // Load template or show template overview
                if (!empty($_GET['strTab'])) {
                    switch ($_GET['strTab']) {
                        case 'shopMailEdit':
                            if ($_GET['tplId']) {
                                $key = $_GET['tplId'];
                                // set the source template to load
                                if (!empty($_GET['portLangId'])) {
                                    $lang_id = $_GET['portLangId'];
                                } else {
                                    $lang_id = $_GET['lang_id'];
                                }
                                // Generate language menu
                                $langMenu =
                                    '<select name="lang_id" size="1" '.
                                    'onchange="loadTpl(document.shopFormEdit.elements[\'tplId\'].value,this.value,\'shopMailEdit\');">'."\n";
                                foreach ($arrLanguage as $langValues) {
                                    if ($langValues['frontend']) {
                                        $langMenu .=
                                            '<option value="'.$langValues['id'].'"'.
                                            ($_GET['lang_id'] == $langValues['id']
                                                ? HTML_ATTRIBUTE_SELECTED : '').
                                            '>'.$langValues['name']."</option>\n";
                                    }
                                }
                                $langMenu .=
                                    '</select>'.
                                    '&nbsp;<input type="checkbox" id="portMail" name="portMail" value="1" />&nbsp;'.
                                    $_ARRAYLANG['TXT_COPY_TO_NEW_LANGUAGE'];
                                // Get the content of the template
                                    $arrTemplate = MailTemplate::getTemplate($key, $lang_id);
                                self::$objTemplate->setVariable(array(
                                    'TXT_SHOP_ADD_EDIT' => $_ARRAYLANG['TXT_EDIT'],
                                    'SHOP_MAIL_KEY' => $key,
                                    'SHOP_MAIL_ID' => (isset($_GET['portLangId']) ? '' : $key),
                                    'SHOP_MAIL_NAME' => $arrTemplate['sender'],
                                    'SHOP_MAIL_SUBJ' => $arrTemplate['subject'],
                                    'SHOP_MAIL_MSG' => $arrTemplate['message'],
                                    'SHOP_MAIL_HTML_CHECKBOX' => Html::getCheckbox(
                                        'html', 1, false, $arrTemplate['html']),
                                    'SHOP_MAIL_MSG_HTML' => get_wysiwyg_editor(
                                        'message_html', $arrTemplate['message_html']),
                                    'SHOP_MAIL_FROM' => $arrTemplate['from'],
                                    'SHOP_LOADD_LANGUAGE_ID' => $_GET['lang_id'],
                                    'SHOP_TEMPLATE_KEY' => $arrTemplate['key'],
                                    'SHOP_TEMPLATE_KEY_READONLY' =>
                                        ($arrTemplate['protected'] ? HTML_ATTRIBUTE_READONLY : ''),
                                ));
//                                self::$objTemplate->touchBlock('saveToOther');
                            } else {
//                                self::$objTemplate->hideBlock('saveToOther');
                                // set the default sender
                                self::$objTemplate->setVariable(
                                    'SHOP_MAIL_FROM',
                                    SettingDb::getValue('email'));
                            }
                            break;
                        case 'shopMailSend':
                            // Generate language menu
                            $langMenu =
                                '<select name="lang_id" size="1" '.
                                'onchange="loadTpl(document.shopFormSend.elements[\'tplId\'].value,this.value,\'shopMailSend\');">'."\n";
                            foreach ($arrLanguage as $langValues) {
                                if ($langValues['frontend']) {
                                    $langMenu .=
                                        '<option value="'.$langValues['id'].'"'.
                                        (!empty($_GET['lang_id']) && $_GET['lang_id'] == $langValues['id']
                                            ? HTML_ATTRIBUTE_SELECTED : '').
                                        '>'.$langValues['name']."</option>\n";
                                }
                            }
                            $langMenu .= '</select>';
                            // Get the content of the template
                            $key = (isset($_GET['tplId']) ? $_GET['tplId'] : '');
                            $lang_id = (isset($_GET['lang_id']) ? intval($_GET['lang_id']) : '');
                            $arrTemplate = MailTemplate::getTemplate($key, $lang_id);
                            if ($arrTemplate) {
                                self::$objTemplate->setVariable(array(
                                    'SHOP_MAIL_ID_SEND' => $arrTemplate['key'],
                                    'SHOP_MAIL_NAME_SEND' => $arrTemplate['sender'],
                                    'SHOP_MAIL_SUBJ_SEND' => $arrTemplate['subject'],
                                    'SHOP_MAIL_MSG_SEND' => $arrTemplate['message'],
                                    'SHOP_MAIL_FROM_SEND' => $arrTemplate['from'],
                                ));
                                if ($arrTemplate['html']) {
                                    self::$objTemplate->setVariable(
                                        'SHOP_MAIL_MSG_HTML_SEND',
                                        get_wysiwyg_editor(
                                            'shopMailBodyHtml',
                                            $arrTemplate['message_html']));
                                }
                            } else {
                                self::$objTemplate->setVariable(
                                    'SHOP_MAIL_FROM_SEND',
                                        SettingDb::getValue('email')
                                );
                            }
                            break;
                    }
                    self::$objTemplate->setVariable(array(
                        'SHOP_MAIL_OVERVIEW_STYLE' => 'display: none;',
                        'SHOP_MAILTAB_OVERVIEW_CLASS' => '',
                        'SHOP_MAIL_EDIT_STYLE' => ($_GET['strTab'] == 'shopMailEdit'
                            ? 'display: block;' : 'display: none;'),
                        'SHOP_MAILTAB_EDIT_CLASS' => ($_GET['strTab'] == 'shopMailEdit' ? 'active' : ''),
                        'SHOP_MAIL_EDIT_TEMPLATES' => ($_GET['strTab'] == 'shopMailEdit'
                            ? $strMailSelectedTemplates : $strMailTemplates),
                        'SHOP_MAIL_EDIT_LANGS' => ($_GET['strTab'] == 'shopMailEdit'
                            ? ($_GET['tplId']
                                ? $langMenu
                                : '<input type="hidden" name="lang_id" value="'.
                                    $defaultLang.'" />'
                              )
                            : '<input type="hidden" name="lang_id" value="'.
                              $defaultLang.'" />'),
                        'SHOP_MAIL_SEND_STYLE' => ($_GET['strTab'] == 'shopMailSend'
                            ? 'display: block;' : 'display: none;'),
                        'SHOP_MAILTAB_SEND_CLASS' => ($_GET['strTab'] == 'shopMailSend' ? 'active' : ''),
                        'SHOP_MAIL_SEND_TEMPLATES' => ($_GET['strTab'] == 'shopMailSend'
                            ? $strMailSelectedTemplates : $strMailTemplates),
                        'SHOP_MAIL_SEND_LANGS' => ($_GET['strTab'] == 'shopMailSend'
                            ? (isset($_GET['tplId'])
                                ? $langMenu
                                : '<input type="hidden" name="lang_id" value="'.
                                    $defaultLang.'" />')
                            : '<input type="hidden" name="lang_id" value="'.
                                $defaultLang.'" />'),
                        'SHOP_MAIL_TO' =>
                            (   $_GET['strTab'] == 'shopMailSend'
                             && isset($_GET['shopMailTo'])
                                ? $_GET['shopMailTo'] : ''),
                    ));
                } else {
                    self::$objTemplate->setVariable(array(
                        'SHOP_MAIL_OVERVIEW_STYLE' => 'display: block;',
                        'SHOP_MAILTAB_OVERVIEW_CLASS' => 'active',
                        'SHOP_MAIL_EDIT_STYLE' => 'display: none;',
                        'SHOP_MAILTAB_EDIT_CLASS' => '',
                        'SHOP_MAIL_EDIT_TEMPLATES' => $strMailTemplates,
                        'SHOP_MAIL_EDIT_LANGS' => '<input type="hidden" name="lang_id" value="'.$defaultLang.'" />',
                        'SHOP_MAIL_SEND_STYLE' => 'display: none;',
                        'SHOP_MAILTAB_SEND_CLASS' => '',
                        'SHOP_MAIL_SEND_TEMPLATES' => $strMailTemplates,
                        'SHOP_MAIL_SEND_LANGS' => '<input type="hidden" name="lang_id" value="'.$defaultLang.'" />',
                        'SHOP_MAIL_TO' => '',
                        'SHOP_MAIL_FROM' => SettingDb::getValue('email'),
                        'SHOP_MAIL_FROM_SEND' => SettingDb::getValue('email'),
                    ));
                } // end: Load template or show template overview
                break;
            case 'vat':
                // Shop general settings template
                self::$objTemplate->addBlockfile('SHOP_SETTINGS_FILE', 'settings_block', 'module_shop_settings_vat.html');

                // start value added tax (VAT) display
                // fill in the VAT fields of the template
                $i = 0;
                foreach (Vat::getArray() as $id => $arrVat) {
                    self::$objTemplate->setVariable(array(
                        'SHOP_ROWCLASS' => (++$i % 2 ? 'row1' : 'row2'),
                        'SHOP_VAT_ID' => $id,
                        'SHOP_VAT_RATE' => $arrVat['rate'],
                        'SHOP_VAT_CLASS' => $arrVat['class'],
                    ));
                    self::$objTemplate->parse('vatRow');
                }
                $enabled_home_customer = SettingDb::getValue('vat_enabled_home_customer');
                $included_home_customer = SettingDb::getValue('vat_included_home_customer');
                $enabled_home_reseller = SettingDb::getValue('vat_enabled_home_reseller');
                $included_home_reseller = SettingDb::getValue('vat_included_home_reseller');
                $enabled_foreign_customer = SettingDb::getValue('vat_enabled_foreign_customer');
                $included_foreign_customer = SettingDb::getValue('vat_included_foreign_customer');
                $enabled_foreign_reseller = SettingDb::getValue('vat_enabled_foreign_reseller');
                $included_foreign_reseller = SettingDb::getValue('vat_included_foreign_reseller');
                self::$objTemplate->setVariable(array(
                    'SHOP_VAT_NUMBER' => SettingDb::getValue('vat_number'),
                    'SHOP_VAT_CHECKED_HOME_CUSTOMER' => ($enabled_home_customer ? HTML_ATTRIBUTE_CHECKED : ''),
                    'SHOP_VAT_DISPLAY_HOME_CUSTOMER' => ($enabled_home_customer ? 'block' : 'none'),
                    'SHOP_VAT_SELECTED_HOME_CUSTOMER_INCLUDED' => ($included_home_customer ? HTML_ATTRIBUTE_SELECTED : ''),
                    'SHOP_VAT_SELECTED_HOME_CUSTOMER_EXCLUDED' => ($included_home_customer ? '' : HTML_ATTRIBUTE_SELECTED),
                    'SHOP_VAT_CHECKED_HOME_RESELLER' => ($enabled_home_reseller ? HTML_ATTRIBUTE_CHECKED : ''),
                    'SHOP_VAT_DISPLAY_HOME_RESELLER' => ($enabled_home_reseller ? 'block' : 'none'),
                    'SHOP_VAT_SELECTED_HOME_RESELLER_INCLUDED' => ($included_home_reseller ? HTML_ATTRIBUTE_SELECTED : ''),
                    'SHOP_VAT_SELECTED_HOME_RESELLER_EXCLUDED' => ($included_home_reseller ? '' : HTML_ATTRIBUTE_SELECTED),
                    'SHOP_VAT_CHECKED_FOREIGN_CUSTOMER' => ($enabled_foreign_customer ? HTML_ATTRIBUTE_CHECKED : ''),
                    'SHOP_VAT_DISPLAY_FOREIGN_CUSTOMER' => ($enabled_foreign_customer ? 'block' : 'none'),
                    'SHOP_VAT_SELECTED_FOREIGN_CUSTOMER_INCLUDED' => ($included_foreign_customer ? HTML_ATTRIBUTE_SELECTED : ''),
                    'SHOP_VAT_SELECTED_FOREIGN_CUSTOMER_EXCLUDED' => ($included_foreign_customer ? '' : HTML_ATTRIBUTE_SELECTED),
                    'SHOP_VAT_CHECKED_FOREIGN_RESELLER' => ($enabled_foreign_reseller ? HTML_ATTRIBUTE_CHECKED : ''),
                    'SHOP_VAT_DISPLAY_FOREIGN_RESELLER' => ($enabled_foreign_reseller ? 'block' : 'none'),
                    'SHOP_VAT_SELECTED_FOREIGN_RESELLER_INCLUDED' => ($included_foreign_reseller ? HTML_ATTRIBUTE_SELECTED : ''),
                    'SHOP_VAT_SELECTED_FOREIGN_RESELLER_EXCLUDED' => ($included_foreign_reseller ? '' : HTML_ATTRIBUTE_SELECTED),
                    'SHOP_VAT_DEFAULT_MENUOPTIONS' => Vat::getMenuoptions(
                        SettingDb::getValue('vat_default_id'), true),
                    'SHOP_VAT_OTHER_MENUOPTIONS' => Vat::getMenuoptions(
                        SettingDb::getValue('vat_other_id'), true),
                ));
                break;

// Coupon codes
            case 'coupon':
                // Shop general settings template
                self::$objTemplate->addBlockfile('SHOP_SETTINGS_FILE', 'settings_block', 'module_shop_discount_coupon.html');
                if (!Coupon::edit(self::$objTemplate)) {
                    self::addError(join('<br />', Coupon::getErrors()));
                } else {
                    if (Coupon::getMessages()) {
                        self::addMessage(join('<br />', Coupon::getMessages()));
                    }
                }
                break;

            default:
                // Shop general settings template
                self::$objTemplate->addBlockfile('SHOP_SETTINGS_FILE', 'settings_block', 'module_shop_settings_general.html');

                $saferpayStatus = (SettingDb::getValue('saferpay_active') ? HTML_ATTRIBUTE_CHECKED : '');
                $saferpayTestStatus = (SettingDb::getValue('saferpay_use_test_account') ? HTML_ATTRIBUTE_CHECKED : '');

                require_once ASCMS_MODULE_PATH.'/shop/payments/paypal/Paypal.class.php';
                $paypalStatus = (SettingDb::getValue('paypal_active') ? HTML_ATTRIBUTE_CHECKED : '');

                $yellowpayTest = SettingDb::getValue('postfinance_use_testserver');
                $yellowpayTestCheckedYes = ($yellowpayTest ? HTML_ATTRIBUTE_CHECKED : '');
                $yellowpayTestCheckedNo = ($yellowpayTest ? '' : HTML_ATTRIBUTE_CHECKED);

                // Datatrans
                $datatrans_request_type = SettingDb::getValue('datatrans_request_type');
                $datatrans_merchant_id = SettingDb::getValue('datatrans_merchant_id');
                $datatrans_active = SettingDb::getValue('datatrans_active');
                $datatrans_use_testserver = SettingDb::getValue('datatrans_use_testserver');

                self::$objTemplate->setVariable(array(
                    'SHOP_SAFERPAY_ID' => SettingDb::getValue('saferpay_id'),
                    'SHOP_SAFERPAY_STATUS' => $saferpayStatus,
                    'SHOP_SAFERPAY_TEST_ID' => SettingDb::getValue('saferpay_use_test_account'),
                    'SHOP_SAFERPAY_TEST_STATUS' => $saferpayTestStatus,
                    'SHOP_SAFERPAY_FINALIZE_PAYMENT' => (SettingDb::getValue('saferpay_finalize_payment')
                        ? HTML_ATTRIBUTE_CHECKED : ''),
                    'SHOP_SAFERPAY_WINDOW_MENUOPTIONS' => Saferpay::getWindowMenuoptions(
                        SettingDb::getValue('saferpay_window_option')),
                    'SHOP_YELLOWPAY_SHOP_ID' => SettingDb::getValue('postfinance_shop_id'),
                    'SHOP_YELLOWPAY_STATUS' =>
                        (SettingDb::getValue('postfinance_shop_id')
                            ? HTML_ATTRIBUTE_CHECKED : ''),
//                    'SHOP_YELLOWPAY_HASH_SEED' => SettingDb::getValue('postfinance_hash_seed'),
// Replaced by
                    'SHOP_YELLOWPAY_HASH_SIGNATURE_IN' => SettingDb::getValue('postfinance_hash_signature_in'),
                    'SHOP_YELLOWPAY_HASH_SIGNATURE_OUT' => SettingDb::getValue('postfinance_hash_signature_out'),
                    'SHOP_YELLOWPAY_ACCEPTED_PAYMENT_METHODS_CHECKBOXES' => Yellowpay::getKnownPaymentMethodCheckboxes(),
                    'SHOP_YELLOWPAY_AUTHORIZATION_TYPE_OPTIONS' => Yellowpay::getAuthorizationMenuoptions(),
                    'SHOP_YELLOWPAY_USE_TESTSERVER_YES_CHECKED' => $yellowpayTestCheckedYes,
                    'SHOP_YELLOWPAY_USE_TESTSERVER_NO_CHECKED' => $yellowpayTestCheckedNo,
                    // Added 20100222 -- Reto Kohli
                    'SHOP_POSTFINANCE_MOBILE_WEBUSER' => SettingDb::getValue('postfinance_mobile_webuser'),
                    'SHOP_POSTFINANCE_MOBILE_SIGN' => SettingDb::getValue('postfinance_mobile_sign'),
                    'SHOP_POSTFINANCE_MOBILE_IJUSTWANTTOTEST_CHECKED' =>
                        (SettingDb::getValue('postfinance_mobile_ijustwanttotest')
                          ? HTML_ATTRIBUTE_CHECKED : ''),
                    'SHOP_POSTFINANCE_MOBILE_STATUS' =>
                        (SettingDb::getValue('postfinance_mobile_status')
                          ? HTML_ATTRIBUTE_CHECKED : ''),
                    'SHOP_DATATRANS_AUTHORIZATION_TYPE_OPTIONS' => Datatrans::getReqtypeMenuoptions($datatrans_request_type),
                    'SHOP_DATATRANS_MERCHANT_ID' => $datatrans_merchant_id,
                    'SHOP_DATATRANS_STATUS' => ($datatrans_active ? HTML_ATTRIBUTE_CHECKED : ''),
                    'SHOP_DATATRANS_USE_TESTSERVER_YES_CHECKED' => ($datatrans_use_testserver ? ' checked:"checked"' : ''),
                    'SHOP_DATATRANS_USE_TESTSERVER_NO_CHECKED' => ($datatrans_use_testserver ? '' : ' checked:"checked"'),
                    // Not supported
                    //'SHOP_DATATRANS_ACCEPTED_PAYMENT_METHODS_CHECKBOXES' => 0,
                    'SHOP_CONFIRMATION_EMAILS' => SettingDb::getValue('email_confirmation'),
                    'SHOP_CONTACT_EMAIL' => SettingDb::getValue('email'),
                    'SHOP_CONTACT_COMPANY' => SettingDb::getValue('company'),
                    'SHOP_CONTACT_ADDRESS' => SettingDb::getValue('address'),
                    'SHOP_CONTACT_TEL' => SettingDb::getValue('telephone'),
                    'SHOP_CONTACT_FAX' => SettingDb::getValue('fax'),
                    'SHOP_PAYPAL_EMAIL' => SettingDb::getValue('paypal_account_email'),
                    'SHOP_PAYPAL_STATUS' => $paypalStatus,
                    'SHOP_PAYPAL_DEFAULT_CURRENCY_MENUOPTIONS' => PayPal::getAcceptedCurrencyCodeMenuoptions(
                        SettingDb::getValue('paypal_default_currency')),
                    // LSV settings
                    'SHOP_PAYMENT_LSV_STATUS' => (SettingDb::getValue('payment_lsv_active') ? HTML_ATTRIBUTE_CHECKED : ''),
                    'SHOP_PAYMENT_DEFAULT_CURRENCY' => Currency::getDefaultCurrencySymbol(),
                    // Country settings
                    'SHOP_GENERAL_COUNTRY_MENUOPTIONS' => Country::getMenuoptions(
                        SettingDb::getValue('country_id'), false),
                    // Thumbnail settings
                    'SHOP_THUMBNAIL_MAX_WIDTH' => SettingDb::getValue('thumbnail_max_width'),
                    'SHOP_THUMBNAIL_MAX_HEIGHT' => SettingDb::getValue('thumbnail_max_height'),
                    'SHOP_THUMBNAIL_QUALITY' => SettingDb::getValue('thumbnail_quality'),
                    // Enable weight setting
                    'SHOP_WEIGHT_ENABLE_CHECKED' => (SettingDb::getValue('weight_enable')
                        ? HTML_ATTRIBUTE_CHECKED : ''),
                    'SHOP_SHOW_PRODUCTS_DEFAULT_OPTIONS' => Products::getDefaultViewMenuoptions(
                        SettingDb::getValue('show_products_default')),
                    'SHOP_PRODUCT_SORTING_MENUOPTIONS' => self::getProductSortingMenuoptions(),
                    // Order amount upper limit
                    'SHOP_ORDERITEMS_AMOUNT_MAX' => Currency::formatPrice(
                        SettingDb::getValue('orderitems_amount_max')),
                    'SHOP_CURRENCY_CODE' => Currency::getCurrencyCodeById(
                        Currency::getDefaultCurrencyId()),
                ));
                break;
        }
        self::$objTemplate->parse('settings_block');
    }


    function showCategories()
    {
        global $_ARRAYLANG;

        $i = 1;
        self::$pageTitle = $_ARRAYLANG['TXT_CATEGORIES'];
        self::$objTemplate->loadTemplateFile('module_shop_categories.html', true, true);

        // ID of the category to be edited, if any
        $id = (isset($_REQUEST['modCatId']) ? $_REQUEST['modCatId'] : 0);
        // Get the tree array of all ShopCategories
        $arrShopCategories =
            ShopCategories::getTreeArray(true, false, false);

        self::$objTemplate->setVariable(array(
            'TXT_SHOP_CATEGORY_ADD_OR_EDIT' => ($id
                ? $_ARRAYLANG['TXT_SHOP_CATEGORY_EDIT']
                : $_ARRAYLANG['TXT_SHOP_CATEGORY_NEW']),
            'SHOP_TOTAL_CATEGORIES', ShopCategories::getTreeNodeCount()
        ));
        // Default to the list tab
        $flagEditTabActive = false;
        // Edit the selected category
        if ($id) {
            // Flip view to the edit tab
            $flagEditTabActive = true;
            $objCategory = ShopCategory::getById($id);
            $pictureFilename = $objCategory->getPicture();
            $picturePath = ASCMS_SHOP_IMAGES_WEB_PATH.'/'.
                ImageManager::getThumbnailFilename($pictureFilename);
            if ($pictureFilename == '') {
                $picturePath = self::$defaultImage;
            }
            self::$objTemplate->setVariable(array(
                'TXT_ADD_NEW_SHOP_GROUP' => $_ARRAYLANG['TXT_EDIT_PRODUCT_GROUP'],
                'SHOP_MOD_CAT_ID' => $id,
                'SHOP_SELECTED_CAT_NAME' => $objCategory->getName(),
                'SHOP_CAT_MENUOPTIONS' => ShopCategories::getMenuoptions(
                    $objCategory->getParentId(), false),
                'SHOP_PICTURE_IMG_HREF' => $picturePath,
                'SHOP_CATEGORY_IMAGE_FILENAME' => $pictureFilename,
                'SHOP_SELECTED_CATEGORY_VIRTUAL_CHECKED' => ($objCategory->isVirtual() ? HTML_ATTRIBUTE_CHECKED : ''),
                'SHOP_SELECTED_CATEGORY_STATUS_CHECKED' => ($objCategory->active() ? HTML_ATTRIBUTE_CHECKED : ''),
                'SHOP_CATEGORY_DESCRIPTION' => $objCategory->getDescription(),
            ));
        } else {
            self::$objTemplate->setVariable(array(
                'TXT_ADD_NEW_SHOP_GROUP' => $_ARRAYLANG['TXT_ADD_NEW_PRODUCT_GROUP'],
                'SHOP_MOD_CAT_ID' => '',
                'SHOP_SELECTED_CAT_NAME' => '',
                'SHOP_CAT_MENUOPTIONS' => ShopCategories::getMenuoptions(0, false),
                'SHOP_PICTURE_IMG_HREF' => self::$defaultImage,
                'SHOP_SELECTED_CATEGORY_VIRTUAL_CHECKED' => '',
                'SHOP_SELECTED_CATEGORY_STATUS_CHECKED' => HTML_ATTRIBUTE_CHECKED,
            ));
        }

        $max_width = intval(SettingDb::getValue('thumbnail_max_width'));
        $max_height = intval(SettingDb::getValue('thumbnail_max_height'));
        if (empty($max_width)) $max_width = 1e5;
        if (empty($max_height)) $max_height = 1e5;

        self::$objTemplate->setVariable(array(
            'SHOP_CATEGORY_EDIT_ACTIVE' => ($flagEditTabActive ? 'active' : ''),
            'SHOP_CATEGORY_EDIT_DISPLAY' => ($flagEditTabActive ? 'block' : 'none'),
            'SHOP_CATEGORY_LIST_ACTIVE' => ($flagEditTabActive ? '' : 'active'),
            'SHOP_CATEGORY_LIST_DISPLAY' => ($flagEditTabActive ? 'none' : 'block'),
            'SHOP_IMAGE_WIDTH' => $max_width,
            'SHOP_IMAGE_HEIGHT' => $max_height,
        ));

        self::$objTemplate->setCurrentBlock('catRow');
        foreach ($arrShopCategories as $arrShopCategory) {
            $id = $arrShopCategory['id'];
            self::$objTemplate->setVariable(array(
                'SHOP_ROWCLASS' => (++$i % 2 ? 'row2' : 'row1'),
                'SHOP_CAT_ID' => $id,
                'SHOP_CAT_NAME' => htmlentities(
                    $arrShopCategory['name'], ENT_QUOTES, CONTREXX_CHARSET),
                'SHOP_CAT_SORTING' => $arrShopCategory['ord'],
                'SHOP_CAT_LEVELSPACE' => str_repeat('|----', $arrShopCategory['level']),
                'SHOP_CAT_STATUS' => ($arrShopCategory['active']
                    ? $_ARRAYLANG['TXT_ACTIVE']
                    : $_ARRAYLANG['TXT_INACTIVE']),
                'SHOP_CAT_STATUS_CHECKED' => ($arrShopCategory['active'] ? HTML_ATTRIBUTE_CHECKED : ''),
                'SHOP_CAT_STATUS_PICTURE' => ($arrShopCategory['active']
                    ? 'status_green.gif' : 'status_red.gif'),
                'SHOP_CAT_VIRTUAL_CHECKED' => ($arrShopCategory['virtual'] ? HTML_ATTRIBUTE_CHECKED : ''),
            ));
            self::$objTemplate->parse('catRow');
        }
        return true;
    }


    /**
     * Insert or update a ShopCategory with data provided in the request.
     * @return  boolean                 True on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com> (parts)
     */
    function addModCategory()
    {
        global $_ARRAYLANG;

        if (empty($_POST['modCatName'])) {
            return true;
        }
        $name = strip_tags($_POST['modCatName']);
        $id = $_POST['modCatId'];
        $active = (isset($_POST['modCatActive']) ? true : false);
        $virtual = (isset($_POST['modCatVirtual']) ? true : false);
        $parentid = $_POST['modCatParentId'];
        $picture = $_POST['modCatImageHref'];
        $description = $_POST['modCatDesc'];

        if ($id > 0) {
            // Update existing ShopCategory
            $objCategory = ShopCategory::getById($id);
            if (!$objCategory) return false;
            // Check validity of the IDs of the category and its parent.
            // If the values are identical, leave the parent ID alone!
            if ($id != $parentid) $objCategory->setParentId($parentid);
            $objCategory->setName($name);
            $objCategory->setDescription($description);
            $objCategory->setStatus($active);
        } else {
            // Add new ShopCategory
            $objCategory = new ShopCategory(
                $name, $description, $parentid, $active, 0);
        }
        // Ignore the picture if it's the default image!
        // Storing it would be pointless, and we should
        // use the picture of a contained Product instead.
        if (   $picture == self::$defaultImage
            || !self::moveImage($picture)) {
            $picture = '';
        } else {
            $objImage = new ImageManager();
            if (!$objImage->_createThumbWhq(
                ASCMS_SHOP_IMAGES_PATH.'/',
                ASCMS_SHOP_IMAGES_WEB_PATH.'/',
                $picture,
                SettingDb::getValue('thumbnail_max_width'),
                SettingDb::getValue('thumbnail_max_height'),
                SettingDb::getValue('thumbnail_quality')
            )) {
                self::addError($_ARRAYLANG['TXT_SHOP_ERROR_CREATING_CATEGORY_THUMBNAIL']);
            }
        }
        $objCategory->setPicture($picture);
        $objCategory->setVirtual($virtual);
        if (!$objCategory->store()) {
            self::addError($_ARRAYLANG['TXT_DATABASE_QUERY_ERROR']);
            return false;
        }
        // Avoid showing/editing the modified ShopCategory again.
        // showCategories() tests the $_REQUEST array!
        $_REQUEST['modCatId'] = 0;
        return true;
    }


    /**
     * Update all ShopCategories with the data provided by the request.
     * @return  boolean                 True on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com> (parts)
     */
    function modAllCategories()
    {
        foreach ($_POST['catId'] as $id) {
            $order = $_POST['ord'][$id];
            $virtual = ($_POST['virtual'][$id] ? true : false);
            $active = ($_POST['active'][$id]  ? true : false);
            if ($order   != $_POST['ord_old'][$id]
             || $active  != $_POST['active_old'][$id]
             || $virtual != $_POST['virtual_old'][$id]) {
                $objCategory = ShopCategory::getById($id);
                $objCategory->setSorting($order);
                $objCategory->setStatus($active);
                $objCategory->setVirtual($virtual);
                if (!$objCategory->store()) {
                    return false;
                }
            }
        }
        return true;
    }


    /**
     * Delete a ShopCategory.
     *
     * Only succeeds if there are no subcategories, and if all contained
     * Products can be deleted as well.  Products that are present in any
     * order won't be deleted.
     * @param   integer     $categoryId     The optional ShopCategory ID.
     *                                      If this is no valid ID, the
     *                                      ID is taken from the request
     *                                      parameters $_GET['id'] or
     *                                      $_POST['selectedCatId'], in this
     *                                      order.
     * @return  boolean                     True on success, false otherwise.
     */
    function delCategory($categoryId=0)
    {
        global $objDatabase, $_ARRAYLANG;

        $arrCategoryId = array();
        $blnDeletedCat = false;

        if (empty($categoryId)) {
            if (isset($_GET['id']) && !empty($_GET['id'])) {
                array_push($arrCategoryId, $_GET['id']);
            } elseif (isset($_POST['selectedCatId']) && !empty($_POST['selectedCatId'])) {
                $arrCategoryId = $_POST['selectedCatId'];
            }
        } else {
            array_push($arrCategoryId, $categoryId);
        }

        if (count($arrCategoryId) > 0) {
            $arrCategoryId = array_reverse($arrCategoryId);
            foreach ($arrCategoryId as $cId) {
                // Check whether this category has subcategories
                $arrChildId =
                    ShopCategories::getChildCategoryIdArray($cId, false);
                if (count($arrChildId)) {
                    self::addError(
                        $_ARRAYLANG['TXT_CATEGORY_NOT_DELETED_BECAUSE_IN_USE'].
                        "&nbsp;(".$_ARRAYLANG['TXT_CATEGORY']."&nbsp;".$cId.")");
                    continue;
                }

                // Get Products in this category
                $count = 1e9;
                $arrProducts = Products::getByShopParams(
                    $count, 0, 0, $cId, 0, '', false, false, '', null, true);
                // Delete the products in the category
                foreach ($arrProducts as $objProduct) {
                    // Check whether there are orders with this Product ID
                    $id = $objProduct->getId();
                    $query = "
                        SELECT 1
                          FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items
                         WHERE productid=".$id;
                    $objResult = $objDatabase->Execute($query);
                    if ($objResult->RecordCount()) {
                        self::addError(
                            $_ARRAYLANG['TXT_COULD_NOT_DELETE_ALL_PRODUCTS'].
                            "&nbsp;(".$_ARRAYLANG['TXT_CATEGORY']."&nbsp;".$cId.")");
                        continue 2;
                    }
                }
                if (!Products::deleteByShopCategory($cId)) {
                    self::addError($_ARRAYLANG['TXT_ERROR_DELETING_PRODUCT'].
                        "&nbsp;(".$_ARRAYLANG['TXT_CATEGORY']."&nbsp;".$cId.")");
                    continue;
                }

                // Delete the category
                if (!ShopCategories::deleteById($cId)) {
                    $this->errorHandling();
                } else {
                    $blnDeletedCat = true;
                }
            }
            if ($blnDeletedCat) {
                self::addMessage($_ARRAYLANG['TXT_DELETED_CATEGORY_AND_PRODUCTS']);
            }
            $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_categories");
            $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_products");
            return true;
        }
        return false;
    }


    /**
     * Delete one or more Products from the database.
     *
     * Checks whether either of the request parameters 'id' (integer) or
     * 'selectedProductId' (array) is present, in that order, and takes the
     * ID of the Product(s) from the first one available, if any.
     * If none of them is set, uses the value of the $product_id argument,
     * if that is valid.
     * Note that this method returns true if no record was deleted because
     * no ID was supplied.
     * @param   integer     $product_id     The optional Product ID
     *                                      to be deleted.
     * @return  boolean                     True on success, false otherwise
     */
    function delProduct($product_id=0)
    {
        $arrProductId = array();
        if (empty($product_id)) {
            if (!empty($_REQUEST['id'])) {
                $arrProductId[] = $_REQUEST['id'];
            } elseif (!empty($_REQUEST['selectedProductId'])) {
                // This argument is an array!
                $arrProductId = $_REQUEST['selectedProductId'];
            }
        } else {
            $arrProductId[] = $product_id;
        }

        $result = true;
        if (count($arrProductId) > 0) {
            foreach ($arrProductId as $id) {
                $objProduct = Product::getById($id);
                if (!$objProduct) continue;
//                $code = $objProduct->getCode();
//                if (empty($code)) {
                    $result &= $objProduct->delete();
//                } else {
//                    $result &= !Products::deleteByCode($objProduct->getCode());
//                }
            }
        }
        return $result;
    }


    function delFile($file)
    {
        @unlink($file);
        clearstatcache();
        if (@file_exists($file)) {
            $filesys = eregi_replace('/', '\\', $file);
            @system('del '.$filesys);
            clearstatcache();
            // don't work in safemode
            if (@file_exists($file)) {
                @chmod ($file, 0775);
                @unlink($file);
            }
        }
        clearstatcache();
        if (@file_exists($file)) return false;
        return true;
    }


    /**
     * Manage products
     *
     * Add and edit products
     * @access  public
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com> (parts)
     */
    function manageProduct()
    {
        global $_ARRAYLANG, $_FILES;

        // Default values
        $productId =  0;
        $productName = '';
        $productIdentifier = '';
        $category_id = '';
        $customerPrice = 0;
        $resellerPrice = 0;
        $specialOffer = 0;
        $discount = 0;
        $vat_id = 0;
        // Used for either the weight or download account validity duration
        $weight = 0;
        $distribution = '';
        $shortDescription = '';
        $description = '';
        $stock = 10;
        $stockVisibility = 1;
        $manufacturerId = 0;
        $manufacturerUrl = '';
        $articleActive = 1;
        $b2b = 1;
        $b2c = 1;
        $startdate = '0000-00-00 00:00:00';
        $enddate = '0000-00-00 00:00:00';
        $imageName = '';
        $userGroupIds = '';
//        $flags = '';
        $groupId = 0;
        $articleId = 0;
        $keywords = '';

// Is $tempThumbnailName, and its session equivalent,
// still in use anywhere?
//        if (isset($_SESSION['shopPM']['TempThumbnailName'])) {
//            $tempThumbnailName = $_SESSION['shopPM']['TempThumbnailName'];
//            unset($_SESSION['shopPM']['TempThumbnailName']);
//        }

        $productId = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
        $objProduct = false;

        // Store Product data if form is sent
        if (isset($_POST['store'])) {
            $productName = contrexx_stripslashes(strip_tags($_POST['productName']));
            $productIdentifier = contrexx_stripslashes(strip_tags($_POST['productCode']));
            $category_id = intval($_POST['category_id']);
            $customerPrice = floatval($_POST['CustomerPrice']);
            $resellerPrice = floatval($_POST['ResellerPrice']);
            $specialOffer =
                (isset($_POST['SpecialOffer']) ? 1 : 0);
            $discount = floatval($_POST['Discount']);
            $vat_id = (isset($_POST['TaxId']) ? $_POST['TaxId'] : 0);
            $shortDescription = contrexx_stripslashes($_POST['ShortDescription']);
            $description = contrexx_stripslashes($_POST['Description']);
            $stock = intval($_POST['Stock']);
            $stockVisibility =
                (isset($_POST['StockVisibility']) ? 1 : 0);
            $manufacturerUrl = htmlspecialchars(strip_tags(contrexx_stripslashes($_POST['ManufacturerUrl'])), ENT_QUOTES, CONTREXX_CHARSET);
            $articleActive =
                (isset($_POST['ArticleActive']) ? 1 : 0);
            $b2b = isset($_POST['B2B']);
            $b2c = isset($_POST['B2C']);
            $startdate = !empty($_POST['Startdate']) ? contrexx_stripslashes($_POST['Startdate']) : '0000-00-00 00:00:00';
            $enddate = !empty($_POST['Enddate']) ? contrexx_stripslashes($_POST['Enddate']) : '0000-00-00 00:00:00';
            $manufacturerId = intval($_POST['ManufacturerId']);
// Currently not used on the detail page
//            $flags = (isset($_POST['Flags'])
//                    ? join(' ', $_POST['Flags']) : '');
            $distribution = $_POST['Distribution'];
            // Different meaning of the "weight" field for downloads!
            // The getWeight() method will treat purely numeric values
            // like the validity period (in days) the same as a weight
            // without its unit and simply return its integer value.
            $weight =
                ($distribution == 'delivery'
                    ? Weight::getWeight($_POST['Weight'])
                    : $_POST['AccountValidity']
                );
            // Assigned frontend groups for protected downloads
            $userGroupIds =
                (isset($_POST['GroupsAssigned'])
                  ? implode(',', $_POST['GroupsAssigned'])
                  : ''
                );
            $groupId = intval($_POST['DiscountGroupCount']);
            $articleId = intval($_POST['DiscountGroupArticle']);
            $keywords = contrexx_addslashes($_POST['Keywords']);

            for ($i = 1; $i <= 3; ++$i) {
                // Images outside the above directory are copied to the shop image folder.
                // Note that the image paths below do not include the document root, but
                // are relative to it.
                $picture = contrexx_stripslashes($_POST['productImage'.$i]);
                // Ignore the picture if it's the default image!
                // Storing it would be pointless.
                // Images outside the above directory are copied to the shop image folder.
                // Note that the image paths below do not include the document root, but
                // are relative to it.
                if (   $picture == self::$defaultImage
                    || !self::moveImage($picture)) {
                    $picture = '';
                }
                // Update the posted path (used below)
                $_POST['productImage'.$i] = $picture;
            }
            // add all to pictures DBstring
            $imageName =
                     base64_encode($_POST['productImage1'])
                .'?'.base64_encode($_POST['productImage1_width'])
                .'?'.base64_encode($_POST['productImage1_height'])
                .':'.base64_encode($_POST['productImage2'])
                .'?'.base64_encode($_POST['productImage2_width'])
                .'?'.base64_encode($_POST['productImage2_height'])
                .':'.base64_encode($_POST['productImage3'])
                .'?'.base64_encode($_POST['productImage3_width'])
                .'?'.base64_encode($_POST['productImage3_height']);

            // A Product was edited and is about to be stored.
            // Note that the flags of the Product *MUST NOT* be changed
            // when inserting or updating the Product data, as the original
            // flags are needed for their own update later.

            // Add a new product
            if ($productId == 0) {
                $objProduct = new Product(
                    $productIdentifier,
                    $category_id,
                    $productName,
                    $distribution,
                    $customerPrice,
                    $articleActive,
                    0,
                    $weight
                );
                $objProduct->store();
                $productId = $objProduct->getId();
            }

            // Apply the changes to all Products with the same Product code.
// Note: This is disabled for the time being, as virtual categories are.
//            if ($productIdentifier != '') {
//                $arrProduct = Products::getByCustomId($productIdentifier);
//            } else {
                $arrProduct = array($objProduct);
//            }
            if (!is_array($arrProduct)) return false;

            foreach ($arrProduct as $objProduct) {
                // Update product
                $objProduct = Product::getById($productId);

                $objProduct->setCode($productIdentifier);
// NOTE: Only change the parent ShopCategory for a Product
// that is in a real ShopCategory.
                $objProduct->setShopCategoryId($category_id);
                $objProduct->setName($productName);
                $objProduct->setPrice($customerPrice);
                $objProduct->setStatus($articleActive);
                $objProduct->setResellerPrice($resellerPrice);
                $objProduct->setSpecialOffer($specialOffer);
                $objProduct->setDiscountPrice($discount);
                $objProduct->setVatId($vat_id);
                $objProduct->setShortDesc($shortDescription);
                $objProduct->setDescription($description);
                $objProduct->setStock($stock);
                $objProduct->setStockVisible($stockVisibility);
                $objProduct->setExternalLink($manufacturerUrl);
                $objProduct->setB2B($b2b);
                $objProduct->setB2C($b2c);
                $objProduct->setStartDate($startdate);
                $objProduct->setEndDate($enddate);
                $objProduct->setManufacturerId($manufacturerId);
                $objProduct->setPictures($imageName);
                $objProduct->setDistribution($distribution);
                $objProduct->setWeight($weight);
// Currently not used on the detail page
//                $objProduct->setFlags($flags);
                $objProduct->setUsergroups($userGroupIds);
                $objProduct->setGroupCountId($groupId);
                $objProduct->setGroupArticleId($articleId);
                $objProduct->setKeywords($keywords);

                // Remove old Product Attributes.
                // They are re-added below.
                $objProduct->clearAttributes();

                // Add new product attributes
                if (   isset($_POST['options'])
                    && is_array($_POST['options'])) {
                    foreach ($_POST['options'] as $valueId => $nameId) {
                        $order = intval($_POST['productOptionsSortId'][$nameId]);
                        $objProduct->addAttribute(intval($valueId), $order);
                    }
                }
                $objProduct->store();
            }

            // Add/remove Categories and Products to/from
            // virtual ShopCategories.
            // Note that this *MUST* be called *AFTER* the Product is updated
            // or inserted.
// Virtual categories are disabled for the time being
//            Products::changeFlagsByProductCode(
//                $productIdentifier, $flags
//            );

            if ($productId > 0) {
                $_SESSION['shop']['strOkMessage'] = $_ARRAYLANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL'];
            } else {
                $_SESSION['shop']['strOkMessage'] = $_ARRAYLANG['TXT_DATA_RECORD_ADDED_SUCCESSFUL'];
            }

//            if (   !empty($tempThumbnailName)
//                && file_exists(ASCMS_SHOP_IMAGES_PATH.'/'.$tempThumbnailName)) {
//                @unlink(ASCMS_SHOP_IMAGES_PATH.'/'.$tempThumbnailName);
//            }

            $objImage = new ImageManager();
            $arrImages = Products::getShopImagesFromBase64String($imageName);
            // create thumbnails if not available
            foreach ($arrImages as $arrImage) {
                if (   !empty($arrImage['img'])
                    && $arrImage['img'] != ShopLibrary::noPictureName) {
                    if (!$objImage->_createThumbWhq(
                        ASCMS_SHOP_IMAGES_PATH.'/',
                        ASCMS_SHOP_IMAGES_WEB_PATH.'/',
                        $arrImage['img'],
                        SettingDb::getValue('thumbnail_max_width'),
                        SettingDb::getValue('thumbnail_max_height'),
                        SettingDb::getValue('thumbnail_quality')
                    )) {
                        self::addError(sprintf($_ARRAYLANG['TXT_SHOP_COULD_NOT_CREATE_THUMBNAIL'], $arrImage['img']));
                    }
                }
            }

            switch ($_POST['AfterStoreAction']) {
                case 'newEmpty':
                    header("Location: index.php?cmd=shop".MODULE_INDEX."&act=products&tpl=manage");
                    exit();
                case 'newTemplate':
                    header("Location: index.php?cmd=shop".MODULE_INDEX."&act=products&tpl=manage&id=".
                        $objProduct->getId()."&new=1"
                    );
                    exit();
                default:
                    header("Location: index.php?cmd=shop".MODULE_INDEX."&act=products");
                    // prevent further output, go back to product overview
                    exit();
            }
        }
        // set template
        self::$objTemplate->addBlockfile('SHOP_PRODUCTS_FILE', 'shop_products_block', 'module_shop_product_manage.html');

        // begin language variables
        self::$objTemplate->setVariable(array(
            // User groups for protected downloads
            // Assign Delete Symbol Path
            'SHOP_DELETE_ICON' => ASCMS_PATH_OFFSET.'/cadmin/images/icons/delete.gif',
            'SHOP_NO_PICTURE_ICON' => self::$defaultImage
        ));
        // end language variables

        // if new entry, set default values
        if (!isset($_REQUEST['id'])) { //OR $_REQUEST['new']
            self::$objTemplate->setVariable(array(
                'SHOP_COMMENT_START' => '<!--',
                'SHOP_COMMENT_END' => '-->',
            ));
            $this->_getAttributeList();
        } else {
            $productId = intval($_REQUEST['id']);
            $this->_getAttributeList($productId);
        }

        // Edit product
        if ($productId > 0) {
            $objProduct = Product::getById($productId);
        }
        if (!$objProduct) {
            $objProduct = new Product('', 0, '', '', 0, 1, 0, 0);
        }

        // extract product image infos (path, width, height)
        $arrImages = Products::getShopImagesFromBase64String(
            $objProduct->getPictures()
        );

//        $flagsSelection =
//            ShopCategories::getVirtualCategoriesSelectionForFlags(
//                $objProduct->getFlags()
//            );
//        if ($flagsSelection) {
//            self::$objTemplate->setVariable(
//                'SHOP_FLAGS_SELECTION', $flagsSelection);
//        }

        // The distribution type (delivery, download, or none)
        $distribution = $objProduct->getDistribution();

        // Available active frontend groups, and those assigned to the product
        $objFWUser = FWUser::getFWUserObject();
        $objGroup = $objFWUser->objGroup->getGroups(array('type' => 'frontend', 'is_active' => true), array('group_id' => 'asc'));
        $userGroupIds = $objProduct->getUsergroups();
        $arrAssignedFrontendGroupId = explode(',', $userGroupIds);
        $strActiveFrontendGroupOptions = '';
        $strAssignedFrontendGroupOptions = '';
        while ($objGroup && !$objGroup->EOF) {
            $strOption =
                '<option value="'.$objGroup->getId().'">'.
                htmlentities($objGroup->getName(), ENT_QUOTES, CONTREXX_CHARSET).
                '</option>';
            if (in_array($objGroup->getId(), $arrAssignedFrontendGroupId)) {
                $strAssignedFrontendGroupOptions .= $strOption;
            } else {
                $strActiveFrontendGroupOptions .= $strOption;
            }
            $objGroup->next();
        }
        $groupId = $objProduct->getGroupCountId();
        $articleId = $objProduct->getGroupArticleId();
        $keywords = $objProduct->getKeywords();
        self::$objTemplate->setVariable(array(
            'SHOP_PRODUCT_ID' => (isset($_REQUEST['new']) ? 0 : $objProduct->getId()),
            'SHOP_PRODUCT_CUSTOM_ID' => $objProduct->getCode(),
            'SHOP_DATE' => date('Y-m-d H:m'),
            'SHOP_PRODUCT_NAME' => $objProduct->getName(),
            'SHOP_CAT_MENUOPTIONS' => ShopCategories::getMenuoptions(
                $objProduct->getShopCategoryId(), false),
            'SHOP_CUSTOMER_PRICE' => Currency::formatPrice($objProduct->getPrice()),
            'SHOP_RESELLER_PRICE' => Currency::formatPrice($objProduct->getResellerPrice()),
            'SHOP_DISCOUNT' => Currency::formatPrice($objProduct->getDiscountPrice()),
            'SHOP_SPECIAL_OFFER' => ($objProduct->isSpecialOffer() ? HTML_ATTRIBUTE_CHECKED : ''),
            'SHOP_VAT_MENUOPTIONS' => Vat::getMenuoptions(
                $objProduct->getVatId(), true),
            'SHOP_SHORT_DESCRIPTION' => get_wysiwyg_editor(
                'shortDescription',
                $objProduct->getShortDesc(),
                'shop'),
            'SHOP_DESCRIPTION' => get_wysiwyg_editor(
                'description',
                $objProduct->getDescription(),
                'shop'),
            'SHOP_STOCK' => $objProduct->getStock(),
            'SHOP_MANUFACTURER_URL' => htmlentities(
                $objProduct->getExternalLink(),
                ENT_QUOTES, CONTREXX_CHARSET),
            'SHOP_STARTDATE' => $objProduct->getStartDate(),
            'SHOP_ENDDATE' => $objProduct->getEndDate(),
            'SHOP_ARTICLE_ACTIVE' => ($objProduct->active() ? HTML_ATTRIBUTE_CHECKED : ''),
            'SHOP_B2B' => ($objProduct->isB2B() ? HTML_ATTRIBUTE_CHECKED : ''),
            'SHOP_B2C' => ($objProduct->isB2C() ? HTML_ATTRIBUTE_CHECKED : ''),
            'SHOP_STOCK_VISIBILITY' => ($objProduct->isStockVisible() ? HTML_ATTRIBUTE_CHECKED : ''),
            'SHOP_MANUFACTURER_MENUOPTIONS' =>
                Manufacturer::getMenuoptions($objProduct->getManufacturerId()),
            'SHOP_PICTURE1_IMG_SRC' =>
                (   !empty($arrImages[1]['img'])
                 && is_file(ASCMS_SHOP_IMAGES_PATH.'/'.
                        ImageManager::getThumbnailFilename($arrImages[1]['img']))
                    ? FWValidator::getEscapedSource(ASCMS_SHOP_IMAGES_WEB_PATH.'/'.
                          ImageManager::getThumbnailFilename($arrImages[1]['img']))
                    : self::$defaultImage),
            'SHOP_PICTURE2_IMG_SRC' =>
                (   !empty($arrImages[2]['img'])
                 && is_file(ASCMS_SHOP_IMAGES_PATH.'/'.
                        ImageManager::getThumbnailFilename($arrImages[2]['img']))
                    ? FWValidator::getEscapedSource(ASCMS_SHOP_IMAGES_WEB_PATH.'/'.
                      ImageManager::getThumbnailFilename($arrImages[2]['img']))
                    : self::$defaultImage),
            'SHOP_PICTURE3_IMG_SRC' =>
                (   !empty($arrImages[3]['img'])
                 && is_file(ASCMS_SHOP_IMAGES_PATH.'/'.
                        ImageManager::getThumbnailFilename($arrImages[3]['img']))
                    ? FWValidator::getEscapedSource(ASCMS_SHOP_IMAGES_WEB_PATH.'/'.
                      ImageManager::getThumbnailFilename($arrImages[3]['img']))
                    : self::$defaultImage),
            'SHOP_PICTURE1_IMG_SRC_NO_THUMB' => (!empty($arrImages[1]['img']) && is_file(ASCMS_SHOP_IMAGES_PATH.'/'.$arrImages[1]['img'])
                    ? ASCMS_SHOP_IMAGES_WEB_PATH.'/'.$arrImages[1]['img']
                    : self::$defaultImage),
            'SHOP_PICTURE2_IMG_SRC_NO_THUMB' => (!empty($arrImages[2]['img']) && is_file(ASCMS_SHOP_IMAGES_PATH.'/'.$arrImages[2]['img'])
                    ? ASCMS_SHOP_IMAGES_WEB_PATH.'/'.$arrImages[2]['img']
                    : self::$defaultImage),
            'SHOP_PICTURE3_IMG_SRC_NO_THUMB' => (!empty($arrImages[3]['img']) && is_file(ASCMS_SHOP_IMAGES_PATH.'/'.$arrImages[3]['img'])
                    ? ASCMS_SHOP_IMAGES_WEB_PATH.'/'.$arrImages[3]['img']
                    : self::$defaultImage),
            'SHOP_PICTURE1_IMG_WIDTH' => $arrImages[1]['width'],
            'SHOP_PICTURE1_IMG_HEIGHT' => $arrImages[1]['height'],
            'SHOP_PICTURE2_IMG_WIDTH' => $arrImages[2]['width'],
            'SHOP_PICTURE2_IMG_HEIGHT' => $arrImages[2]['height'],
            'SHOP_PICTURE3_IMG_WIDTH' => $arrImages[3]['width'],
            'SHOP_PICTURE3_IMG_HEIGHT' => $arrImages[3]['height'],
            'SHOP_DISTRIBUTION_MENU' => Distribution::getDistributionMenu(
                $objProduct->getDistribution(),
                'distribution',
                'distributionChanged();',
                'style="width: 220px"'),
            'SHOP_WEIGHT' => ($distribution == 'delivery'
                ? Weight::getWeightString($objProduct->getWeight())
                : '0 g'),
            // User group menu, returns 'userGroupId'
            'SHOP_GROUPS_AVAILABLE' => $strActiveFrontendGroupOptions,
            'SHOP_GROUPS_ASSIGNED' => $strAssignedFrontendGroupOptions,
            'SHOP_ACCOUNT_VALIDITY_OPTIONS' => FWUser::getValidityMenuOptions(
                ($distribution == 'download'
                  ? $objProduct->getWeight() : 0)),
            'SHOP_CREATE_ACCOUNT_YES_CHECKED' => (empty($userGroupIds) ? '' : HTML_ATTRIBUTE_CHECKED),
            'SHOP_CREATE_ACCOUNT_NO_CHECKED' => (empty($userGroupIds) ? HTML_ATTRIBUTE_CHECKED : ''),
            'SHOP_DISCOUNT_GROUP_COUNT_MENU_OPTIONS' => Discount::getMenuOptionsGroupCount($groupId),
            'SHOP_DISCOUNT_GROUP_ARTICLE_MENU_OPTIONS' => Discount::getMenuOptionsGroupArticle($articleId),
            'SHOP_KEYWORDS' => $keywords,
            // Enable JavaScript functionality for the weight if enabled
            'SHOP_WEIGHT_ENABLED' =>
                SettingDb::getValue('weight_enable')
        ));
        return true;
    }


    /**
     * Show the stored orders
     * @access  public
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @global  array   $_ARRAYLANG
     * @global  array   $_CONFIG
     * @author  Reto Kohli <reto.kohli@comvation.com> (parts)
     */
    function shopShowOrders()
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        $i = 0; // Used for rowclass
        $searchPattern = '';
        $objFWUser = FWUser::getFWUserObject();

        // Update the order status if valid
        if (isset($_GET['changeOrderStatus']) &&
            intval($_GET['changeOrderStatus']) >= SHOP_ORDER_STATUS_PENDING &&
            intval($_GET['changeOrderStatus']) <= SHOP_ORDER_STATUS_COUNT &&
            !empty($_GET['orderId'])) {
            $query = "
                UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_orders
                   SET order_status='".intval($_GET['changeOrderStatus'])."',
                       modified_by ='".$objFWUser->objUser->getUsername()."',
                       last_modified=NOW()
                 WHERE orderid=".intval($_GET['orderId']);
            $objDatabase->Execute($query);
        }

        // Send an email to the customer
        if (   !empty($_GET['sendMail'])
            && !empty($_GET['orderId'])) {
            $result = ShopLibrary::sendConfirmationMail($_GET['orderId']);
            if (!empty($result)) {
                self::addMessage(sprintf($_ARRAYLANG['TXT_EMAIL_SEND_SUCCESSFULLY'], $result));
            } else {
                self::addError($_ARRAYLANG['TXT_MESSAGE_SEND_ERROR']);
            }
        }

        // Load template
        self::$pageTitle = $_ARRAYLANG['TXT_ORDERS'];
        self::$objTemplate->loadTemplateFile('module_shop_orders.html', true, true);

        // Set up filter and display options
        $customerOrderField = 'order_date';
        $order = $customerOrderField;
        $order_status = -1;
        $customer_type = -1;
        $listletter = '';
        $searchterm = '';
        if (!empty($_REQUEST['searchterm'])) {
            $searchTerm = htmlspecialchars(
                $_REQUEST['searchterm'], ENT_QUOTES, CONTREXX_CHARSET
            );
            // Check if the user wants to search the pseudo "account names".
            // These may be customized with pre- or postfixes.
            // Adapt the regex as needed.
//            $arrMatch = array();
            $searchAccount = '';
//                (preg_match('/^A-(\d{1,2})-?8?(\d{0,2})?/i', $searchTerm, $arrMatch)
//                    ? "OR (    order_date LIKE '__".$arrMatch[1]."%'
//                           AND orderid LIKE '%".$arrMatch[2]."')"
//                    : ''
//                );
            $searchPattern .=
                " AND (company LIKE '%$searchTerm%'
                    OR firstname LIKE '%$searchTerm%'
                    OR lastname LIKE '%$searchTerm%'
                    OR address LIKE '%$searchterm%'
                    OR city LIKE '%$searchterm%'
                    OR phone LIKE '%$searchterm%'
                    OR email LIKE '%$searchterm%'
                    $searchAccount)";
        }
        if (isset($_REQUEST['customer_type'])) {
            $customer_type = intval($_REQUEST['customer_type']);
            if ($customer_type == 0 || $customer_type == 1) {
                $searchPattern .= " AND is_reseller=$customer_type";
            }
        }
        if (isset($_REQUEST['order_status'])) {
            $order_status = $_REQUEST['order_status'];
            if (   is_numeric($order_status)
                && $_REQUEST['order_status'] >= 0
                && $_REQUEST['order_status'] <= SHOP_ORDER_STATUS_COUNT) {
                $order_status = intval($_REQUEST['order_status']);
                $searchPattern .= " AND order_status='$order_status'";
                // Check "Show pending orders" as well if these are selected
                if ($order_status == SHOP_ORDER_STATUS_PENDING) {
                    $_REQUEST['pendingorders'] = 1;
                }
            } else {
                // Ignore.
                $order_status = '';
            }
        }
        if (isset($_REQUEST['listsort'])) {
            $customerOrderField =
                addslashes(strip_tags($_REQUEST['listsort']));
            $order = $customerOrderField;
        }
        // let the user choose whether to see pending orders or not
        if (!isset($_REQUEST['showpendingorders'])) {
            $searchPattern .=
                ' AND order_status!='.SHOP_ORDER_STATUS_PENDING;
        } else {
            self::$objTemplate->setVariable(
                'SHOP_SHOW_PENDING_ORDERS_CHECKED', HTML_ATTRIBUTE_CHECKED);
        }
        if (!empty($_REQUEST['listletter'])) {
            $listletter = htmlspecialchars(
                $_REQUEST['listletter'], ENT_QUOTES, CONTREXX_CHARSET
            );
            $listsort = addslashes(strip_tags($_REQUEST['listsort']));
            $searchPattern .= " AND LEFT($listsort, 1)='$listletter'";
        }

        self::$objTemplate->setVariable(array(
            'TXT_SEND_TEMPLATE_TO_CUSTOMER' => str_replace('TXT_ORDER_COMPLETE',
                $_ARRAYLANG['TXT_ORDER_COMPLETE'],
                $_ARRAYLANG['TXT_SEND_TEMPLATE_TO_CUSTOMER']),
            'SHOP_SEARCH_TERM' => $searchterm,
//            'SHOP_ORDER_STATUS_MENU' =>
//                $this->getOrderStatusMenu($order_status),
            'SHOP_ORDER_STATUS_MENUOPTIONS' => $this->getOrderStatusMenuoptions($order_status, true),
            'SHOP_CUSTOMER_TYPE_MENUOPTIONS' => Customers::getTypeMenuoptions($customer_type),
            'SHOP_CUSTOMER_SORT_MENUOPTIONS' => Customers::getSortMenuoptions($customerOrderField),
            // Protected download user account validity
        ));
        self::$objTemplate->setGlobalVariable(
            'SHOP_CURRENCY', Currency::getDefaultCurrencySymbol());

        // Create SQL query
        $query = "
            SELECT orderid, firstname, lastname, company,
                   currency_order_sum, selected_currency_id,
                   order_date, customer_note, order_status
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_customers c,
                   ".DBPREFIX."module_shop".MODULE_INDEX."_orders o
             WHERE c.customerid=o.customerid
                   $searchPattern
          ORDER BY $order DESC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            $this->errorHandling();
        } else {
            $pos = (isset($_GET['pos']) ? intval($_GET['pos']) : 0);
            $count = $objResult->RecordCount();
            $limit = intval($_CONFIG['corePagingLimit']);
            $viewPaging = $count > $limit ? true : false;
            $paging = getPaging(
                $count,
                $pos,
                '&amp;cmd=shop'.MODULE_INDEX.'&amp;act=orders'.
                  ($searchterm ? '&amp;searchterm='.$searchterm : '').
                  ($listletter ? '&amp;listletter='.$listletter : '').
                  ($order != 'customerid' ? '&amp;listsort='.$order : ''),
                $_ARRAYLANG['TXT_ORDERS'],
                $viewPaging
            );
            self::$objTemplate->setVariable(array(
                'SHOP_ORDER_PAGING' => $paging,
                'SHOP_CUSTOMER_LISTLETTER' => $listletter,
 //                'SHOP_LISTLETTER_MENUOPTIONS' => self::getListletterMenuoptions,
            ));
        }
        $objResult = $objDatabase->SelectLimit($query, $limit, $pos);
        if (!$objResult) {
            // if query has errors, call errorhandling
            $this->errorHandling();
        } else {
            if ($objResult->RecordCount() == 0) {
                self::$objTemplate->hideBlock('orderTable');
            } else {
                self::$objTemplate->setCurrentBlock('orderRow');
                while (!$objResult->EOF) {
                    $order_id = $objResult->fields['orderid'];
                    // Custom order ID may be created and used as account name.
                    // Adapt the method as needed.
                    $order_id_custom = ShopLibrary::getCustomOrderId(
                        $order_id,
                        $objResult->fields['order_date']
                    );
                    $order_status = $objResult->fields['order_status'];
                    // Pick user account by the same name
                    $query = "
                        SELECT * FROM `".DBPREFIX."access_users`
                         WHERE username LIKE '$order_id_custom-%'";
                    $objResultAccount = $objDatabase->Execute($query);
                    if (!$objResultAccount) {
                        $this->errorHandling();
                    }
                    // Determine end date
                    $endDate =
                        ($objResultAccount->fields['expiration'] > 0
                            ? date('d.m.Y', $objResultAccount->fields['expiration'])
                            : '-'
                        );
                    // PHP5! $tipNote = (strlen($objResult['customer_note'])>0) ? php_strip_whitespace($objResult['customer_note']) : '';
                    $tipNote = $objResult->fields['customer_note'];
                    $tipLink = (!empty($tipNote)
                        ? '<img src="images/icons/comment.gif" onmouseout="htm()" onmouseover="stm(Text['.
                          $objResult->fields['orderid'].'],Style[0])" width="11" height="10" alt="" title="" />'
                        : ''
                    );
                    $order_id = $objResult->fields['orderid'];
                    $order_status = $objResult->fields['order_status'];
                    self::$objTemplate->setVariable(array(
                        'SHOP_ROWCLASS' => ($order_status == 0
                            ? 'rowWarn' : (++$i % 2 ? 'row1' : 'row2')),
                        'SHOP_ORDERID' => $order_id,
                        'SHOP_TIP_ID' => $order_id,
                        'SHOP_TIP_NOTE' => ereg_replace(
                            "\r\n|\n|\r", '<br />',
                            htmlentities(strip_tags($tipNote),
                                ENT_QUOTES, CONTREXX_CHARSET)),
                        'SHOP_TIP_LINK' => $tipLink,
                        'SHOP_DATE' => $objResult->fields['order_date'],
                        'SHOP_NAME' => (strlen($objResult->fields['company']) > 1
                            ? trim($objResult->fields['company'])
                            : $objResult->fields['firstname'].' '.
                              $objResult->fields['lastname']),
                        'SHOP_ORDER_SUM' => Currency::getDefaultCurrencyPrice(
                            $objResult->fields['currency_order_sum']),
                        'SHOP_ORDER_STATUS' => $this->getOrderStatusMenu(
                            intval($order_status),
                            'order_status['.$order_id.']',
                            'changeOrderStatus('.
                              $order_id.','.$order_status.', this.value)'),
                        // Protected download account validity end date
                        'SHOP_VALIDITY' => $endDate,
                    ));
                    self::$objTemplate->parse('orderRow');
                    self::$objTemplate->parse('tipMessageRow');
                    $objResult->MoveNext();
                }
            }
            self::$objTemplate->setVariable('SHOP_ORDER_PAGING', $paging);
        }
    }


    /**
     * Set up details of the selected order
     * @access  public
     * @param   string  $templateName   Name of the template file
     * @param   integer $type           1: edit order, 0: just display it
     * @global  ADONewConnection  $objDatabase    Database connection object    Database
     * @global  array   $_ARRAYLANG     Language array
     * @author  Reto Kohli <reto.kohli@comvation.com> (parts)
     */
    function shopShowOrderdetails($templateName, $type)
    {
        global $objDatabase, $_ARRAYLANG;
        // initalize vars
        // The order total -- in the currency chosen by the customer
        $currencyOrderSum = 0;
        // recalculated VAT total
        $total_vat_amount = 0;

        // set template -- may be one of
        //  'module_shop_order_details.html'
        //  'module_shop_order_edit.html'
        self::$objTemplate->loadTemplateFile($templateName, true, true);

        $orderId = intval($_REQUEST['orderid']);

        // lsv data
        $query = "
            SELECT * FROM ".DBPREFIX."module_shop_lsv
             WHERE order_id=$orderId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            $this->errorHandling();
        }
        if ($objResult->RecordCount() == 1) {
            self::$objTemplate->hideBlock('creditCard');
            self::$objTemplate->setVariable(array(
                'SHOP_ACCOUNT_HOLDER' => $objResult->fields['holder'],
                'SHOP_ACCOUNT_BANK' => $objResult->fields['bank'],
                'SHOP_ACCOUNT_BLZ' => $objResult->fields['blz'],
            ));
        } else {
            self::$objTemplate->hideBlock('lsv');
        }

        // used below; will contain the Products from the database
        $arrProducts = array();

        // Order and Customer query (no products/order items)
        $query = "
            SELECT o.orderid, o.customerid, o.selected_currency_id,
                   o.currency_order_sum, o.order_date, o.order_status,
                   o.last_modified, o.customerid, o.ship_prefix,
                   o.ship_company, o.ship_firstname, o.ship_lastname,
                   o.ship_address, o.ship_zip, o.ship_city, o.ship_country_id,
                   o.ship_phone, o.currency_ship_price, o.tax_price,
                   o.shipping_id, o.payment_id, o.currency_payment_price,
                   o.customer_ip, o.customer_host, o.customer_lang,
                   o.customer_browser, o.customer_note, o.modified_by,
                   c.customerid, c.prefix, c.company, c.firstname, c.lastname,
                   c.address, c.zip, c.city, c.country_id, c.phone, c.fax,
                   c.ccnumber, c.cvc_code, c.ccdate, c.ccname,
                   c.company_note, c.email, c.is_reseller, c.group_id
              FROM ".DBPREFIX."module_shop_customers AS c,
                   ".DBPREFIX."module_shop_orders AS o
             WHERE c.customerid=o.customerid
               AND o.orderid=$orderId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            $this->errorHandling();
        } else  {
            // set the customer and order data, if found
            if (!$objResult->EOF) {
                $selectedCurrencyId = $objResult->fields['selected_currency_id'];
                $currencyOrderSum = $objResult->fields['currency_order_sum'];
//                $shippingPrice = $objResult->fields['currency_ship_price'];
                $mailTo = $objResult->fields['email'];
                $lastModified = $objResult->fields['last_modified'];
                $countryId = $objResult->fields['country_id'];
                $shippingId = $objResult->fields['shipping_id'];
                $paymentId = $objResult->fields['payment_id'];
                $isReseller = $objResult->fields['is_reseller'];
                $ship_to_country_id = $objResult->fields['ship_country_id'];
                $order_status = $objResult->fields['order_status'];
                $shipperName = Shipment::getShipperName($shippingId);
                $groupCustomerId = $objResult->fields['group_id'];
                Vat::isReseller($isReseller);
                Vat::setIsHomeCountry(
                    SettingDb::getValue('country_id') == $ship_to_country_id);
                self::$objTemplate->setGlobalVariable(
                    'SHOP_CURRENCY',
                    Currency::getCurrencySymbolById($selectedCurrencyId));
                self::$objTemplate->setVariable(array(
                    'SHOP_CUSTOMER_ID' => $objResult->fields['customerid' ],
                    'SHOP_ORDERID' => $objResult->fields['orderid'],
                    'SHOP_DATE' => $objResult->fields['order_date'],
                    'SHOP_ORDER_STATUS' => ($type == 1
                        ? $this->getOrderStatusMenu(
                            $order_status,
                            'orderstatusid',
                            'swapSendToStatus(this.value)')
                        : $_ARRAYLANG['TXT_SHOP_ORDER_STATUS_'.$order_status]),
                    'SHOP_SEND_MAIL_STYLE' => ($order_status == SHOP_ORDER_STATUS_CONFIRMED
                        ? 'display: inline;' : 'display: none;'),
                    'SHOP_SEND_MAIL_STATUS' => ($type == 1
                        ? ($order_status != SHOP_ORDER_STATUS_CONFIRMED
                            ? HTML_ATTRIBUTE_CHECKED : '')
                        : ''),
                    'SHOP_ORDER_SUM' => Currency::getDefaultCurrencyPrice($currencyOrderSum),
                    'SHOP_DEFAULT_CURRENCY' => Currency::getDefaultCurrencySymbol(),
                    'SHOP_TITLE' => $objResult->fields['title'],
                    'SHOP_COMPANY' => $objResult->fields['company'],
                    'SHOP_FIRSTNAME' => $objResult->fields['firstname'],
                    'SHOP_LASTNAME' => $objResult->fields['lastname'],
                    'SHOP_ADDRESS' => $objResult->fields['address'],
                    'SHOP_ZIP' => $objResult->fields['zip'],
                    'SHOP_CITY' => $objResult->fields['city'],
                    'SHOP_COUNTRY' => Country::getNameById($countryId),
                    'SHOP_SHIP_TITLE' => $objResult->fields['ship_title'],
                    'SHOP_SHIP_COMPANY' => $objResult->fields['ship_company'],
                    'SHOP_SHIP_FIRSTNAME' => $objResult->fields['ship_firstname'],
                    'SHOP_SHIP_LASTNAME' => $objResult->fields['ship_lastname'],
                    'SHOP_SHIP_ADDRESS' => $objResult->fields['ship_address'],
                    'SHOP_SHIP_ZIP' => $objResult->fields['ship_zip'],
                    'SHOP_SHIP_CITY' => $objResult->fields['ship_city'],
                    'SHOP_SHIP_COUNTRY' => ($type == 1
                        ? Country::getMenu('shipcountry', $ship_to_country_id)
                        : Country::getNameById($ship_to_country_id)),
                    'SHOP_SHIP_PHONE' => $objResult->fields['ship_phone'],
                    'SHOP_PHONE' => $objResult->fields['phone'],
                    'SHOP_FAX' => $objResult->fields['fax'],
                    'SHOP_EMAIL' => $mailTo,
                    'SHOP_PAYMENTTYPE' => Payment::getProperty($paymentId, 'name'),
                    'SHOP_CCNUMBER' => $objResult->fields['ccnumber'],
                    'SHOP_CCDATE' => $objResult->fields['ccdate'],
                    'SHOP_CCNAME' => $objResult->fields['ccname'],
                    'SHOP_CVC_CODE' => $objResult->fields['cvc_code'],
                    'SHOP_CUSTOMER_NOTE' => $objResult->fields['customer_note'],
                    'SHOP_CUSTOMER_IP' => $objResult->fields['customer_ip'] == ''
                        ? '&nbsp;'
                        : '<a href="index.php?cmd=nettools&amp;tpl=whois&amp;address='.
                          $objResult->fields['customer_ip'].'" title="'.$_ARRAYLANG['TXT_SHOW_DETAILS'].'">'.
                          $objResult->fields['customer_ip'].'</a>',
                    'SHOP_CUSTOMER_HOST' => $objResult->fields['customer_host'] == ''
                        ? '&nbsp;'
                        : '<a href="index.php?cmd=nettools&amp;tpl=whois&amp;address='.
                          $objResult->fields['customer_host'].'" title="'.$_ARRAYLANG['TXT_SHOW_DETAILS'].'">'.
                          $objResult->fields['customer_host'].'</a>',
                    'SHOP_CUSTOMER_LANG' => $objResult->fields['customer_lang'] == '' ? '&nbsp;' : $objResult->fields['customer_lang'],
                    'SHOP_CUSTOMER_BROWSER' => $objResult->fields['customer_browser'] == '' ? '&nbsp;' : $objResult->fields['customer_browser'],
                    'SHOP_COMPANY_NOTE' => $objResult->fields['company_note'],
                    'SHOP_LAST_MODIFIED' => ($lastModified == 0 ? $_ARRAYLANG['TXT_ORDER_WASNT_YET_EDITED'] : $lastModified.'&nbsp;'.$_ARRAYLANG['TXT_EDITED_BY'].'&nbsp;'.$objResult->fields['modified_by']),
                    'SHOP_SHIPPING_TYPE' => $shipperName,
                ));
                $psp_id = Payment::getPaymentProcessorId($paymentId);
                $ppName = PaymentProcessing::getPaymentProcessorName($psp_id);
                if (!$ppName) $this->errorHandling();
                self::$objTemplate->setVariable(array(
                    'SHOP_SHIPPING_PRICE' =>
                        $objResult->fields['currency_ship_price'],
                    'SHOP_PAYMENT_PRICE' =>
                        $objResult->fields['currency_payment_price'],
                    'SHOP_PAYMENT_HANDLER' => $ppName,
                    'SHOP_LAST_MODIFIED_DATE' => $lastModified
                ));
            }
            if ($type == 1) {
                // edit order
                $strJsArrShipment = Shipment::getJSArrays();
                self::$objTemplate->setVariable(array(
                    'TXT_SEND_TEMPLATE_TO_CUSTOMER' =>
                        str_replace(
                            'TXT_ORDER_COMPLETE',
                            $_ARRAYLANG['TXT_ORDER_COMPLETE'],
                            $_ARRAYLANG['TXT_SEND_TEMPLATE_TO_CUSTOMER']),
                    'SHOP_SHIPPING_TYP_MENU' => Shipment::getShipperMenu(
                        $objResult->fields['ship_country_id'],
                        $objResult->fields['shipping_id'],
                        "javascript:calcPrice(0)"),
                    'SHOP_JS_ARR_SHIPMENT' => $strJsArrShipment,
                    'SHOP_PRODUCT_IDS_MENU_NEW' => Products::getMenuoptions(),
                    'SHOP_JS_ARR_PRODUCT' =>
                        Products::getJavascriptArray($groupCustomerId, $isReseller),
                ));
            }
        }

        // get product options
        $query = "SELECT order_items_id, product_option_name, product_option_value ".
            "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items_attributes ".
            "WHERE order_id=".$orderId;
        $arrProductOptions = array();
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            $this->errorHandling();
        } else {
            while (!$objResult->EOF) {
                if (!isset($arrProductOptions[$objResult->fields['order_items_id']]['options'])) {
                    $arrProductOptions[$objResult->fields['order_items_id']]['options'] = array();
                }
                $option_name = $objResult->fields['product_option_name'];
                $optionValueOriginal = $objResult->fields['product_option_value'];
                $optionValue = ShopLibrary::stripUniqidFromFilename($optionValueOriginal);
                // Link an uploaded image name to its file
                if (   $optionValue != $optionValueOriginal
                    && file_exists(ASCMS_PATH.'/'.$this->uploadDir.'/'.$optionValueOriginal)) {
                    $optionValue =
                        '<a href="'.$this->uploadDir.'/'.
                        $optionValueOriginal.'" target="uploadimage">'.
                        $optionValue.'</a>';
                }
                array_push(
                    $arrProductOptions[$objResult->fields['order_items_id']]['options'],
                    $option_name.": ".$optionValue
                );
                $objResult->MoveNext();
            }
        }

        // set up the order details
        $query = "
            SELECT order_items_id, product_name, productid, price, quantity,
                   vat_percent, weight
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items
             WHERE orderid=$orderId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            $this->errorHandling();
        } else {
            self::$objTemplate->setCurrentBlock('orderdetailsRow');
            // modulo counter
            $i = 0;
            // reset totals
            $total_weight = 0;
            $total_vat_amount = 0;
            $total_net_price = 0;

            // products loop
            while (!$objResult->EOF) {
                if ($type == 1) {
                    $productName = $objResult->fields['product_name'];
                } else {
                    $productName = $objResult->fields['product_name'];
                    if (isset($arrProductOptions[$objResult->fields['order_items_id']])) {
                        $productName .=
                            '<i><br />- '.
                            implode(
                                '<br />- ',
                                $arrProductOptions[$objResult->fields['order_items_id']]['options']).
                            '</i>';
                    }
                }

                $product_id = $objResult->fields['productid'];
                // Get missing product details
                $query = "
                    SELECT product_id, handler
                    FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
                    WHERE id=$product_id";
                $objResult2 = $objDatabase->Execute($query);
                if (!$objResult2) {
                    $this->errorHandling();
                }
                $productCode = $objResult2->fields['product_id'];
                $productDistribution = $objResult2->fields['handler'];
                $productPrice = $objResult->fields['price'];
                $productQuantity = $objResult->fields['quantity'];
                $productVatRate = $objResult->fields['vat_percent'];
                // $rowNetPrice means 'product times price' from here
                $rowNetPrice = $productPrice * $productQuantity;
                $rowPrice = $rowNetPrice; // VAT added later, if applicable
                $rowVatAmount = 0;
                $total_net_price += $rowNetPrice;

                // Here, the VAT has to be recalculated before setting up the
                // fields.  If the VAT is excluded, it must be added here.
                // Note: the old shop_order.tax_price field is no longer valid,
                // individual shop_order_items *MUST* have been UPDATEd by the
                // time PHP parses this line.
                // Also note that this implies that the vat_number and
                // country_id can be ignored, as they are considered when the
                // order is placed and the VAT is applied to the order
                // accordingly.

                // calculate the VAT amount per row, included or excluded
                $rowVatAmount = Vat::amount($productVatRate, $rowNetPrice);
                // and add it to the total VAT amount
                $total_vat_amount += $rowVatAmount;

                if (!Vat::isIncluded()) {
                    // Add tax to price
                    $rowPrice += $rowVatAmount;
                }
                //else {
                    // VAT is disabled.
                    // there shouldn't be any non-zero percentages in the order_items!
                    // but if there are, there probably has been a change and we *SHOULD*
                    // still treat them as if VAT had been enabled at the time the order
                    // was placed!
                    // that's why the else {} block is commented out.
                //}

                $weight = '-';
                if ($productDistribution != 'download') {
                    $weight = $objResult->fields['weight'];
                    if (intval($weight) > 0) {
                        $total_weight += $weight*$productQuantity;
                    }
                }

                self::$objTemplate->setVariable(array(
                    'SHOP_ROWCLASS' => (++$i % 2 ? 'row2' : 'row1'),
                    'SHOP_QUANTITY' => $productQuantity,
                    'SHOP_PRODUCT_NAME' => $productName,
                    'SHOP_PRODUCT_PRICE' => Currency::formatPrice($productPrice),
                    'SHOP_PRODUCT_SUM' => Currency::formatPrice($rowNetPrice),
                    'SHOP_P_ID' => ($type == 1
                        ? $objResult->fields['order_items_id']
                        // If we're just showing the order details, the
                        // product ID is only used in the product ID column
                        : $objResult->fields['productid']),
                    'SHOP_PRODUCT_CUSTOM_ID' => $productCode,
                    // fill VAT field
                    'SHOP_PRODUCT_TAX_RATE' => ($type == 1
                        ? $productVatRate
                        : Vat::format($productVatRate)),
                    'SHOP_PRODUCT_TAX_AMOUNT' => Currency::formatPrice($rowVatAmount),
                    'SHOP_PRODUCT_WEIGHT' => Weight::getWeightString($weight),
                    'SHOP_ACCOUNT_VALIDITY' => FWUser::getValidityString($weight),
                ));

                // Get a product menu for each Product if $type == 1 (edit).
                // Preselects the current Product ID.
                // Move this to Product.class.php!
                if ($type == 1) {
                    $menu = '';
                    foreach ($arrProducts as $arrProduct) {
                        $menu .= '<option value="'.$arrProduct['id'].'"';
                        if ($arrProduct['id'] == $objResult->fields['productid']) {
                            $menu .= HTML_ATTRIBUTE_SELECTED;
                        }
                        $menu .= '>'.$arrProduct['id']."</option>\n";
                    }
                    self::$objTemplate->setVariable(
                        'SHOP_PRODUCT_IDS_MENU', $menu);
                }
                self::$objTemplate->parse('orderdetailsRow');
                $objResult->MoveNext();
            }

            // Show VAT with the individual products:
            // If VAT is enabled, and we're both in the same country
            // ($total_vat_amount has been set above if both conditions are met)
            // show the VAT rate.
            // If there is no VAT, the amount is 0 (zero).
            //if ($total_vat_amount) {
                // distinguish between included VAT, and additional VAT added to sum
                $tax_part_percentaged = (Vat::isIncluded()
                    ? $_ARRAYLANG['TXT_TAX_PREFIX_INCL']
                    : $_ARRAYLANG['TXT_TAX_PREFIX_EXCL']);
                self::$objTemplate->setVariable(array(
                    'SHOP_TAX_PRICE' => Currency::formatPrice($total_vat_amount),
                    'SHOP_PART_TAX_PROCENTUAL' => $tax_part_percentaged,
                ));
            //} else {
                // No VAT otherwise
                // remove it from the details overview if empty
                //self::$objTemplate->hideBlock('taxprice');
                //$tax_part_percentaged = $_ARRAYLANG['TXT_NO_TAX'];
            //}
            self::$objTemplate->setVariable(array(
                'SHOP_ROWCLASS_NEW' => (++$i % 2 ? 'row2' : 'row1'),
                'SHOP_CURRENCY_ORDER_SUM' => Currency::formatPrice($currencyOrderSum),
                'SHOP_TOTAL_WEIGHT' => Weight::getWeightString($total_weight),
                'SHOP_NET_PRICE' => Currency::formatPrice($total_net_price),
            ));
        }

        self::$objTemplate->setVariable(array(
            'TXT_PRODUCT_ID' => $_ARRAYLANG['TXT_ID'],
            // inserted VAT, weight here
            // change header depending on whether the tax is included or excluded
            'TXT_TAX_RATE' => (Vat::isIncluded()
                ? $_ARRAYLANG['TXT_TAX_PREFIX_INCL']
                : $_ARRAYLANG['TXT_TAX_PREFIX_EXCL']),
            'TXT_SHOP_ACCOUNT_VALIDITY' => $_ARRAYLANG['TXT_SHOP_VALIDITY'],
        ));
    }


    /**
     * Store order
     *
     * @global  array   $_ARRAYLANG     Language array
     * @global  ADONewConnection  $objDatabase    Database connection object    Database object
     */
    function shopStoreOrderdetails()
    {
        global $objDatabase, $_ARRAYLANG;

        $orderId = intval($_POST['orderid']);
        $objFWUser = FWUser::getFWUserObject();

        // calculate the total order sum in the selected currency of the customer
        $totalOrderSum =
            floatval($_POST['ShippingPrice'])
          + floatval($_POST['PaymentPrice']);
        // the tax amount will be set, even if it's included in the price already.
        // thus, we have to check the setting.
        if (!Vat::isIncluded()) {
            $totalOrderSum += floatval($_POST['TaxPrice']);
        }
        // store the product details and add the price of each product
        // to the total order sum $totalOrderSum
        foreach ($_REQUEST['productlist'] as $orderItemId => $product_id) {
            if ($orderItemId != 0 && $product_id == 0) {
                // delete the product from the list
                $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items ".
                    "WHERE order_items_id = $orderItemId";
                $objResult = $objDatabase->Execute($query);
                if ($objResult !== false) {
                    $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items_attributes ".
                    "WHERE order_items_id = ".
                    intval(substr(contrexx_stripslashes($orderItemId),1,-1));
                    $objResult = $objDatabase->Execute($query);
                }
            } elseif ($orderItemId == 0 && $product_id != 0) {
                // add a new product to the list
                $productPrice = floatval($_REQUEST['productprice'][$orderItemId]);
                $productQuantity = intval($_REQUEST['productquantity'][$orderItemId]) < 1 ? 1 : intval($_REQUEST['productquantity'][$orderItemId]);
                $totalOrderSum += $productPrice * $productQuantity;
                $productTaxPercent = floatval($_REQUEST['producttaxpercent'][$orderItemId]);
                $productWeight = Weight::getWeight($_REQUEST['productweight'][$orderItemId]);
                $query = "INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_order_items ".
                    "(orderid, productid, product_name, price, quantity, vat_percent, weight) ".
                    "VALUES ($orderId, $product_id, '".
                    contrexx_strip_tags($_POST['ProductName'][$orderItemId]).
                    "', $productPrice, $productQuantity, ".
                    "$productTaxPercent, $productWeight)";
                $objResult = $objDatabase->Execute($query);
            } elseif ($orderItemId != 0 && $product_id != 0) {
                // update the order item
                $productPrice = floatval($_REQUEST['productprice'][$orderItemId]);
                $productQuantity = intval($_REQUEST['productquantity'][$orderItemId]) < 1 ? 1 : intval($_REQUEST['productquantity'][$orderItemId]);
                $totalOrderSum += $productPrice * $productQuantity;
                $productTaxPercent = floatval($_REQUEST['producttaxpercent'][$orderItemId]);
                $productWeight = Weight::getWeight($_REQUEST['productweight'][$orderItemId]);
                $query = "UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_order_items SET ".
                        "price = $productPrice".
                        ", quantity = $productQuantity".
                        ", productid = ".intval($_POST['ProductList'][$orderItemId]).
                        ", product_name='".contrexx_strip_tags($_POST['ProductName'][$orderItemId]).
                        "', vat_percent = $productTaxPercent".
                        ", weight = $productWeight".
                    " WHERE order_items_id=$orderItemId";
                $objResult = $objDatabase->Execute($query);
            }
        }

        // store the order details
        $query = "
            UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_orders
               SET currency_order_sum=".floatval($totalOrderSum).",
                   currency_ship_price=".floatval($_POST['ShippingPrice']).",
                   currency_payment_price=".floatval($_POST['PaymentPrice']).",
                   order_status ='".intval($_POST['OrderStatusId'])."',
                   ship_prefix='".addslashes(strip_tags($_POST['ShipPrefix']))."',
                   ship_company='".addslashes(strip_tags($_POST['ShipCompany']))."',
                   ship_firstname='".addslashes(strip_tags($_POST['ShipFirstname']))."',
                   ship_lastname='".addslashes(strip_tags($_POST['ShipLastname']))."',
                   ship_address='".addslashes(strip_tags($_POST['ShipAddress']))."',
                   ship_city='".addslashes(strip_tags($_POST['ShipCity']))."',
                   ship_zip='".addslashes(strip_tags($_POST['ShipZip']))."',
                   ship_country_id=".intval($_POST['ShipCountry']).",
                   ship_phone='".addslashes(strip_tags($_POST['ShipPhone']))."',
                   tax_price=".floatval($_POST['TaxPrice']).",
                   shipping_id=".intval($_POST['shipperId']).",
                   modified_by='".$objFWUser->objUser->getUsername()."',
                   last_modified=now()
             WHERE orderid = $orderId";
        // should not be changed, see above
        // ", payment_id = ".intval($_POST['paymentId']).
        if (!$objDatabase->Execute($query)) {
            $this->errorHandling();
            return false;
        } else {
            self::addMessage($_ARRAYLANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL']);
            // Send an email to the customer, if requested
            if (!empty($_POST['SendMail'])) {
                $result = ShopLibrary::sendConfirmationMail($orderId);
                if (!empty($result)) {
                    self::addMessage(sprintf($_ARRAYLANG['TXT_EMAIL_SEND_SUCCESSFULLY'], $result));
                } else {
                    self::addError($_ARRAYLANG['TXT_MESSAGE_SEND_ERROR']);
                    return false;
                }
            }
        }
        return true;
    }


    /**
     * Delete Order
     *
     * @version  1.0      initial version
     * @param    integer  $selectedid
     * @return   string   $result
     */
    function shopDeleteOrder($orderId=0)
    {
        global $objDatabase, $_ARRAYLANG;

        $arrOrderId = array();

        // prepare the array $arrOrderId with the ids of the orders to delete
        if (empty($orderId)) {
            if (isset($_GET['orderId']) && !empty($_GET['orderId'])) {
                array_push($arrOrderId, $_GET['orderId']);
            } elseif (isset($_POST['selectedOrderId']) && !empty($_POST['selectedOrderId'])) {
                $arrOrderId = $_POST['selectedOrderId'];
            }
        } else {
            array_push($arrOrderId, $orderId);
        }

        // delete each selected order
        if (count($arrOrderId) > 0) {
            foreach ($arrOrderId as $oId) {
                // Delete files uploaded with the order
                $query = "
                    SELECT product_option_value
                      FROM ".DBPREFIX."module_shop_order_items_attributes
                     WHERE order_id=$oId
                ";
                $objResult = $objDatabase->Execute($query);
                if (!$objResult) {
                    $this->errorHandling();
                } else {
                    while (!$objResult->EOF) {
                        $filename =
                            ASCMS_PATH.'/'.$this->uploadDir.'/'.
                            $objResult->fields['product_option_value'];
                        if (file_exists($filename)) {
                            if (!@unlink($filename)) {
                                self::addError(sprintf($_ARRAYLANG['TXT_SHOP_ERROR_DELETING_FILE'], $filename));
                            }
                        }
                        $objResult->MoveNext();
                    }
                }
                $query = "
                    DELETE FROM ".DBPREFIX."module_shop_order_items_attributes
                     WHERE order_id=".intval($oId);
                if (!$objDatabase->Execute($query)) {
                    $this->errorHandling();
                }
                $query = "
                    DELETE FROM ".DBPREFIX."module_shop_order_items
                     WHERE orderid=".intval($oId);
                if (!$objDatabase->Execute($query)) {
                    $this->errorHandling();
                }
                $query = "
                    DELETE FROM ".DBPREFIX."module_shop_lsv
                     WHERE order_id=".intval($oId);
                if (!$objDatabase->Execute($query)) {
                    $this->errorHandling();
                }
                $query = "
                    DELETE FROM ".DBPREFIX."module_shop_orders
                     WHERE orderid=".intval($oId);
                if (!$objDatabase->Execute($query)) {
                    $this->errorHandling();
                    return false;
                }
            } // foreach
        }
        self::addMessage($_ARRAYLANG['TXT_ORDER_DELETED']);
        return true;
    }


    /**
     * Show Customers
     */
    function shopShowCustomers()
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        $template = (isset($_GET['tpl']) ? $_GET['tpl'] : '');
        if ($template == 'discounts') {
            return $this->showDiscountCustomer();
        }
        if ($template == 'groups') {
            return $this->showCustomerGroups();
        }
        $pos = 0;
        $i = 0;
        self::$objTemplate->loadTemplateFile("module_shop_customers.html", true, true);

        $customer_active = -1;
        $customer_type = -1;
        $searchterm = '';
        $listletter = '';
        $arrFilter = array(
            'group' => SettingDb::getValue('usergroup_id_customer'));
        $order = 'customerid';
        if (   isset($_REQUEST['active'])
            && $_REQUEST['active'] >= 0) {
            $customer_active = intval($_REQUEST['active']);
            $arrFilter['active'] = $customer_active;
        }
        if (   isset($_REQUEST['is_reseller'])
            && $_REQUEST['is_reseller'] >= 0) {
            $customer_type = intval($_REQUEST['is_reseller']);
            $arrFilter['group'] = SettingDb::getValue('usergroup_id_reseller');
        }
        if (!empty($_REQUEST['searchterm'])) {
            $searchterm = trim(strip_tags(contrexx_stripslashes($_REQUEST['searchterm'])));
        }
        if (!empty($_REQUEST['listsort'])) {
            $order = contrexx_stripslashes($_REQUEST['listsort']);
        }
        if (!empty($_REQUEST['listletter'])) {
            $listletter = $_REQUEST['listletter'];
// TODO: Like that?
            $searchterm = $listletter.'%';
        }

        $count = 0;
        $pos = (isset($_GET['pos']) ? intval($_GET['pos']) : 0);
        $objCustomer = Customers::get(
            $arrFilter, $searchterm, $order, null, null, $pos);
        while (!$objCustomer->EOF) {
            self::$objTemplate->setVariable(array(
                'SHOP_ROWCLASS' => 'row'.(++$i % 2 + 1),
                'SHOP_CUSTOMERID' => $objCustomer->getId(),
                'SHOP_COMPANY' => $objCustomer->getCompany(),
                'SHOP_NAME' => $objCustomer->getFirstname(),
                'SHOP_ADDRESS' => $objCustomer->getAddress(),
                'SHOP_ZIP' => $objCustomer->getZip(),
                'SHOP_CITY' => $objCustomer->getCity(),
                'SHOP_PHONE' => $objCustomer->getPhone(),
                'SHOP_EMAIL' => $objCustomer->getEmail(),
                'SHOP_CUSTOMER_STATUS_IMAGE' =>
                    ($objCustomer->getActiveStatus()
                      ? 'led_green.gif' : 'led_red.gif'),
            ));
            self::$objTemplate->parse('customersRow');
            $objCustomer->next();
        }
//        if ($count == 0) self::$objTemplate->hideBlock('customersoverview');
        $paging = getPaging(
            $count, $pos,
            '&amp;cmd=shop'.MODULE_INDEX.'&amp;act=customers'.
              ($customer_active >= 0 ? '&amp;active='.$customer_active : '').
              ($customer_type >= 0 ? '&amp;is_reseller='.$customer_type : '').
              ($searchterm ? '&amp;searchterm='.$searchterm : '').
              ($listletter ? '&amp;listletter='.$listletter : '').
              ($order != 'customerid' ? '&amp;listsort='.$order : ''),
            "<b>".$_ARRAYLANG['TXT_CUSTOMERS_ENTRIES']."</b>");
        self::$objTemplate->setVariable(array(
            'SHOP_CUSTOMER_PAGING' => $paging,
            'SHOP_CUSTOMER_TERM' => htmlentities($searchterm),
            'SHOP_CUSTOMER_LISTLETTER' => $listletter,
            'SHOP_CUSTOMER_TYPE_MENUOPTIONS' => Customers::getTypeMenuoptions($customer_type),
            'SHOP_CUSTOMER_STATUS_MENUOPTIONS' => Customers::getActiveMenuoptions($customer_active),
            'SHOP_CUSTOMER_SORT_MENUOPTIONS' => Customers::getSortMenuoptions($order),
//            'SHOP_LISTLETTER_MENUOPTIONS' => self::getListletterMenuoptions,
        ));
        return true;
    }


    /**
     * Delete Customer
     */
    function shopDeleteCustomer()
    {
        global $objDatabase, $_ARRAYLANG;

        $arrCustomerId = array();

        if (isset($_GET['customerId']) && !empty($_GET['customerId'])) {
            $arrCustomerId = array(0 => $_GET['customerId']);
        } elseif (isset($_POST['selectedCustomerId']) && !empty($_POST['selectedCustomerId'])) {
            $arrCustomerId = $_POST['selectedCustomerId'];
        }

        if (count($arrCustomerId) > 0) {
            foreach ($arrCustomerId as $cId) {
                $query = "SELECT orderid FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders
                      WHERE customerid = ".intval($cId);

                if (($objResult = $objDatabase->Execute($query)) !== false) {
                    while (!$objResult->EOF) {
                        $orderId = $objResult->fields['orderid'];
                        $this->shopDeleteOrder($orderId);
                        $objResult->MoveNext();
                    }
                    self::addMessage($_ARRAYLANG['TXT_ALL_ORDERS_DELETED']);
                }
                $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_customers
                      WHERE customerid = ".intval($cId);
                if ($objDatabase->Execute($query)) {
                    self::addMessage($_ARRAYLANG['TXT_CUSTOMER_DELETED']);
                } else {
                    $this->errorHandling();
                }
            }
        }
        return true;
    }


    /**
     * Set up the customer details
     */
    function shopShowCustomerDetails()
    {
        global $objDatabase, $_ARRAYLANG;

        self::$objTemplate->loadTemplateFile("module_shop_customer_details.html", true, true);

        if (isset($_POST['store'])) {
            self::storeCustomerFromPost();
        }
        $customerid = intval($_REQUEST['customerid']);
        $objCustomer = Customer::getById($customerid);
        if (!$objCustomer) {
            self::addError($_ARRAYLANG['TXT_SHOP_CUSTOMER_ERROR_NOT_FOUND']);
            return false;
        }

        $customer_type = $_ARRAYLANG['TXT_CUSTOMER'];
        if ($objCustomer->isReseller()) {
            $customer_type = $_ARRAYLANG['TXT_RESELLER'];
        }
        $active = $_ARRAYLANG['TXT_INACTIVE'];
        if ($objCustomer->getActiveStatus()) {
            $active = $_ARRAYLANG['TXT_ACTIVE'];
        }
        self::$objTemplate->setVariable(array(
            'SHOP_CUSTOMERID' => $objCustomer->getId(),
            'SHOP_TITLE' => $objCustomer->getTitle(),
            'SHOP_LASTNAME' => $objCustomer->getLastName(),
            'SHOP_FIRSTNAME' => $objCustomer->getFirstName(),
            'SHOP_COMPANY' => $objCustomer->getCompany(),
            'SHOP_ADDRESS' => $objCustomer->getAddress(),
            'SHOP_CITY' => $objCustomer->getCity(),
            'SHOP_USERNAME' => $objCustomer->getUserName(),
            'SHOP_COUNTRY' => Country::getNameById($objCustomer->getCountryId()),
            'SHOP_ZIP' => $objCustomer->getZip(),
            'SHOP_PHONE' => $objCustomer->getPhone(),
            'SHOP_FAX' => $objCustomer->getFax(),
            'SHOP_EMAIL' => $objCustomer->getEmail(),
            'SHOP_CCNUMBER' => $objCustomer->getCcNumber(),
            'SHOP_CCDATE' => $objCustomer->getCcDate(),
            'SHOP_CCNAME' => $objCustomer->getCcName(),
            'SHOP_CVC_CODE' => $objCustomer->getCcCode(),
            'SHOP_COMPANY_NOTE' => $objCustomer->getCompanyNote(),
            'SHOP_IS_RESELLER' => $customer_type,
            'SHOP_REGISTER_DATE' => $objCustomer->getRegisterDate(),
            'SHOP_CUSTOMER_STATUS' => $active,
            'SHOP_DISCOUNT_GROUP_CUSTOMER' => Discount::getCustomerGroupName(
                $objCustomer->getGroupId()),
        ));

        $query = "SELECT order_date,orderid,order_status, selected_currency_id, currency_order_sum ".
                  "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders ".
                  "WHERE customerid = $customerid ".
                  "ORDER BY order_date DESC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            //if query has errors, call errorhandling
            $this->errorHandling();
        } else {
            Currency::init($objResult->fields['selected_currency_id']);
            $i = 1;
            while (!$objResult->EOF) {
                //set edit fields
                self::$objTemplate->setVariable(array(
                    'SHOP_ROWCLASS' => 'row'.(++$i % 2 + 1),
                    'SHOP_ORDER_ID' => $objResult->fields['orderid'],
                    'SHOP_ORDER_ID_CUSTOM' => ShopLibrary::getCustomOrderId(
                        $objResult->fields['orderid'],
                        $objResult->fields['order_date']),
                    'SHOP_ORDER_DATE' => $objResult->fields['order_date'],
                    'SHOP_ORDER_STATUS' => $_ARRAYLANG['TXT_SHOP_ORDER_STATUS_'.$objResult->fields['order_status']],
                    'SHOP_ORDER_SUM' => Currency::getDefaultCurrencyPrice($objResult->fields['currency_order_sum']).' '.Currency::getDefaultCurrencySymbol(),
                ));
                self::$objTemplate->parse('orderRow');
                $objResult->MoveNext();
            }
        }
        return true;
    }


    /**
     * Add or update a Customer
     * @author    Reto Kohli <reto.kohli@comvation.com>
     */
    function shopNewEditCustomer()
    {
        global $objDatabase, $_ARRAYLANG;

        self::$objTemplate->loadTemplateFile("module_shop_edit_customer.html", true, true);

        $customerid = (isset($_REQUEST['customerid'])
            ? intval($_REQUEST['customerid']) : null);
        if (isset($_POST['store'])) {
            $customerid = $this->storeCustomerFromPost();
        }
        if ($customerid) {
            $objCustomer = Customer::getById($customerid);
            if (!$objCustomer) {
                self::addError($_ARRAYLANG['TXT_SHOP_CUSTOMER_ERROR_LOADING']);
                return Customer::errorHandler();
            }
            self::$pageTitle = $_ARRAYLANG['TXT_EDIT_CUSTOMER'];
            $username = $objCustomer->getUsername();
            $password = '';
            $company = $objCustomer->getCompany();
            $title = $objCustomer->getTitle();
            $firstname = $objCustomer->getFirstname();
            $lastname = $objCustomer->getLastname();
            $address = $objCustomer->getAddress();
            $city = $objCustomer->getCity();
            $zip = $objCustomer->getZip();
            $country_id = $objCustomer->getCountryId();
            $phone = $objCustomer->getPhone();
            $fax = $objCustomer->getFax();
            $email = $objCustomer->getEmail();
            $companynote = $objCustomer->getCompanynote();
            $is_reseller = $objCustomer->isReseller();
            $registerdate = $objCustomer->getRegistrationDate();
            $active = $objCustomer->getActiveStatus();
// TODO: How do we handle this with Users?
//            $discount_group = intval($_POST['discountgroupcustomer']);
            $lang_id = (isset($_POST['customer_lang_id'])
                ? $_POST['customer_lang_id'] : FRONTEND_LANG_ID);
        } else {
            $objCustomer = new Customer();
            self::$pageTitle = $_ARRAYLANG['TXT_ADD_NEW_CUSTOMER'];
            self::$objTemplate->setVariable(array(
                'SHOP_SEND_LOGING_DATA_STATUS' => HTML_ATTRIBUTE_CHECKED,
                'SHOP_REGISTER_DATE' => $_ARRAYLANG['TXT_SHOP_TIME_NOW'],
            ));
            $username = trim(strip_tags(contrexx_stripslashes($_POST['username'])));
            $password = trim(strip_tags(contrexx_stripslashes($_POST['password'])));
            $company = trim(strip_tags(contrexx_stripslashes($_POST['company'])));
            $title = trim(strip_tags(contrexx_stripslashes($_POST['title'])));
            $firstname = trim(strip_tags(contrexx_stripslashes($_POST['firstname'])));
            $lastname = trim(strip_tags(contrexx_stripslashes($_POST['lastname'])));
            $address = trim(strip_tags(contrexx_stripslashes($_POST['address'])));
            $city = trim(strip_tags(contrexx_stripslashes($_POST['city'])));
            $zip = trim(strip_tags(contrexx_stripslashes($_POST['zip'])));
            $country_id = intval($_POST['country_id']);
            $phone = trim(strip_tags(contrexx_stripslashes($_POST['phone'])));
            $fax = trim(strip_tags(contrexx_stripslashes($_POST['fax'])));
            $email = trim(strip_tags(contrexx_stripslashes($_POST['email'])));
            $companynote = trim(strip_tags(contrexx_stripslashes($_POST['companynote'])));
            $is_reseller = (isset($_POST['customerclass'])
                ? $_POST['customerclass'] : -1);
            $active = !empty($_POST['active']);
// TODO: How do we handle this with Users?
//            $discount_group = intval($_POST['discountgroupcustomer']);
            $lang_id = (isset($_POST['customer_lang_id'])
                ? $_POST['customer_lang_id'] : FRONTEND_LANG_ID);
            $customerid = null;
        }

//$objAttribute = new User_Profile_Attribute();
//die(var_export($objAttribute->, true));

die(var_export($objCustomer->objAttribute->getById('title')->getMenuOptionValue(), true));
die(var_export($objCustomer->getProfileAttribute('title'), true));

        self::$objTemplate->setVariable(array(
            'SHOP_CUSTOMERID' => $objCustomer->getId(),
            'SHOP_COMPANY'    => $company,
            'SHOP_TITLE'      => $title,
            'SHOP_LASTNAME'   => $lastname,
            'SHOP_FIRSTNAME'  => $firstname,
            'SHOP_ADDRESS'    => $address,
            'SHOP_ZIP'        => $zip,
            'SHOP_CITY'       => $city,
            'SHOP_EMAIL'      => $email,
            'SHOP_PHONE'      => $phone,
            'SHOP_FAX'        => $fax,
            'SHOP_USERNAME'   => $username,
            'SHOP_PASSWORD'   => $password,
            'SHOP_COMPANY_NOTE' => $companynote,
            'SHOP_REGISTER_DATE' => $registerdate,
            'SHOP_COUNTRY_MENUOPTIONS' =>
                Country::getMenuoptions($country_id),
            'SHOP_DISCOUNT_GROUP_CUSTOMER' =>
                Discount::getMenuOptionsGroupCustomer(
// TODO: How?
//                    $objCustomer->?
                ),
            'SHOP_CUSTOMER_TYPE_MENUOPTIONS' =>
                Customers::getTypeMenuoptions($is_reseller),
            'SHOP_CUSTOMER_ACTIVE_MENUOPTIONS' =>
                Customers::getActiveMenuoptions($active),
            'SHOP_LANG_ID_MENUOPTIONS' => FWLanguage::getMenuoptions($lang_id),
        ));
        return true;
    }


    /**
     * Store a customer
     *
     * Sets okay or error messages according to the outcome.
     * @return  integer       The Customer ID on success, null otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function storeCustomerFromPost()
    {
        global $_ARRAYLANG, $_CORELANG;

        $username = trim(strip_tags(contrexx_stripslashes($_POST['username'])));
        $password = trim(strip_tags(contrexx_stripslashes($_POST['password'])));
        $company = trim(strip_tags(contrexx_stripslashes($_POST['company'])));
        $title = trim(strip_tags(contrexx_stripslashes($_POST['title'])));
        $firstname = trim(strip_tags(contrexx_stripslashes($_POST['firstname'])));
        $lastname = trim(strip_tags(contrexx_stripslashes($_POST['lastname'])));
        $address = trim(strip_tags(contrexx_stripslashes($_POST['address'])));
        $city = trim(strip_tags(contrexx_stripslashes($_POST['city'])));
        $zip = trim(strip_tags(contrexx_stripslashes($_POST['zip'])));
        $country_id = intval($_POST['country_id']);
        $phone = trim(strip_tags(contrexx_stripslashes($_POST['phone'])));
        $fax = trim(strip_tags(contrexx_stripslashes($_POST['fax'])));
        $email = trim(strip_tags(contrexx_stripslashes($_POST['email'])));
        $companynote = trim(strip_tags(contrexx_stripslashes($_POST['companynote'])));
        $customer_active = intval($_POST['active']);
        $is_reseller = intval($_POST['customerclass']);
//        $registerdate = trim(strip_tags(contrexx_stripslashes($_POST['registerdate'])));
// TODO: How do we handle this with Users?
//        $discount_group = intval($_POST['discountgroupcustomer']);
        $lang_id = (isset($_POST['customer_lang_id'])
            ? $_POST['customer_lang_id'] : FRONTEND_LANG_ID);
        $customerid = intval($_REQUEST['customerid']);
        $objCustomer = Customer::getById($customerid);
        if (!$objCustomer) $objCustomer = new Customer();
        $objCustomer->setTitle($title);
        $objCustomer->setCompany($company);
        $objCustomer->setFirstName($firstname);
        $objCustomer->setLastName($lastname);
        $objCustomer->setAddress($address);
        $objCustomer->setCity($city);
        $objCustomer->setZip($zip);
        $objCustomer->setCountryId($country_id);
        $objCustomer->setPhone($phone);
        $objCustomer->setFax($fax);
        $objCustomer->setEmail($email);
        $objCustomer->setCompanyNote($companynote);
        $objCustomer->setActiveStatus($customer_active);
        $objCustomer->setResellerStatus($is_reseller);
        // Set automatically
        //$objCustomer->setRegisterDate($registerdate);
// TODO: How do we handle this with Users?
//        $objCustomer->setGroupId($discount_group);
        $objCustomer->setUserName($username);
        if ($password != '') {
            $objCustomer->setPassword($password);
        }
        $objCustomer->setFrontendLanguage($lang_id);
        if (!$objCustomer->store()) {
            self::addError($_ARRAYLANG['TXT_SHOP_CUSTOMER_ERROR_STORING']);
            self::addError(join('<br />', $objCustomer->getErrorMsg()));
            return null;
        }
        self::addMessage($_ARRAYLANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL']);

        if (isset($_POST['sendlogindata'])) {
            $lang_id = $objCustomer->getFrontendLanguage();
            // Select template for sending login data
            $arrMailtemplate = array(
                'key' => 'shop_mail_template_login',
                'lang_id' => $lang_id,
                'to' => $email,
                'substitution' => $objCustomer->getSubstitutionArray(),
            );
            if (!MailTemplate::send($arrMailtemplate)) {
                self::addError($_ARRAYLANG['TXT_MESSAGE_SEND_ERROR']);
                return null;
            }
            self::addMessage(sprintf($_ARRAYLANG['TXT_EMAIL_SEND_SUCCESSFULLY'], $email));
        }
        return $objCustomer->getId();
    }


    function _products()
    {
        global $_ARRAYLANG;

        self::$objTemplate->loadTemplateFile('module_shop_products.html',true,true);
        if (!empty($_REQUEST['tpl'])) {
            $tpl = $_REQUEST['tpl'];
        } else {
            $tpl = '';
        }
        switch ($tpl) {
            case 'attributes':
                $this->_showAttributes();
                break;
            case 'manage':
                self::$pageTitle = $_ARRAYLANG['TXT_ADD_PRODUCTS'];
                $this->manageProduct();
                break;
            case 'discounts':
                self::$pageTitle = $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUNT_GROUPS'];
                $this->showDiscountGroupsCount();
                break;
            case 'groups':
                self::$pageTitle = $_ARRAYLANG['TXT_SHOP_ARTICLE_GROUPS'];
                $this->showArticleGroups();
                break;
            default:
                // Alternative: self::$pageTitle = $_ARRAYLANG['TXT_PRODUCT_CATALOG'];
                self::$pageTitle = $_ARRAYLANG['TXT_PRODUCT_CHARACTERISTICS'];
                $this->showProducts();
        }
        self::$objTemplate->parse('shop_products_block');
    }


    /**
     * Show Products
     */
    function showProducts()
    {
        global $_ARRAYLANG, $_CONFIG;

        // Store changed values
        if (isset($_REQUEST['saveAttributes'])) {
            $this->storeProducts();
        }

        //initialize variable
        $pos = isset($_GET['pos']) ? intval($_GET['pos']) : 0;
        $i=1;

        self::$objTemplate->addBlockfile(
            'SHOP_PRODUCTS_FILE',
            'shop_products_block',
            'module_shop_product_catalog.html'
        );
//        self::$objTemplate->setGlobalVariable(array(
// TODO: See if this text fits
//            'TXT_PRODUCT_STATUS' => $_ARRAYLANG['TXT_STATUS'],
//            'TXT_SHOP_SHOW_PRODUCT_ON_START_PAGE_TIP' => htmlentities(
//                $_ARRAYLANG['TXT_SHOP_SHOW_PRODUCT_ON_START_PAGE_TIP'],
//                ENT_QUOTES, CONTREXX_CHARSET),
//        ));

        $catId = 0;
        if (isset($_REQUEST['catId'])) {
            $catId = intval($_REQUEST['catId']);
        }
        $manufacturerId = 0;
        if (isset($_REQUEST['manufacturerId'])) {
            $manufacturerId = intval($_REQUEST['manufacturerId']);
        }
// Not applicable in the backend
//        $flagSpecialoffer = '';
//        if (isset($_REQUEST['specialoffer'])) {
//            $flagSpecialoffer = true;
//        }
        $searchTerm = '';
        if (!empty($_REQUEST['searchterm'])) {
            $searchTerm = mysql_escape_string(
                trim(contrexx_stripslashes($_REQUEST['searchterm']))
            );
        }
        $pos = isset($_GET['pos']) ? intval($_GET['pos']) : 0;
        $count = 0;
        // Mind that $count is handed over by reference.
        $arrProducts = Products::getByShopParams(
            $count, $pos, 0, $catId, $manufacturerId, $searchTerm,
            false, false,
            self::$arrProductOrder[SettingDb::getValue('product_sorting')],
            '', true // Include inactive Products
        );
        $limit = intval($_CONFIG['corePagingLimit']);
        // Show paging if the Product count is greater than the page limit
        if ($count > $limit) {
            self::$objTemplate->setVariable('SHOP_PRODUCT_PAGING',
                getPaging($count, $pos,
                  '&amp;cmd=shop'.MODULE_INDEX.'&amp;act=products&amp;catId='.
                  $catId, '<b>'.$_ARRAYLANG['TXT_PRODUCTS'].'</b>', true));
        }
        self::$objTemplate->setVariable(array(
            'SHOP_CAT_MENUOPTIONS' =>
                ShopCategories::getMenuoptions($catId, false),
            'SHOP_SEARCH_TERM' => $searchTerm,
            'SHOP_PRODUCT_TOTAL' => $count,
        ));

        $i = 0;
        self::$objTemplate->setCurrentBlock('productRow');
        foreach ($arrProducts as $objProduct) {
            $productStatus = '';
            $productStatusValue = '';
            $productStatusPicture = 'status_red.gif';
            if ($objProduct->active()) {
                $productStatus = HTML_ATTRIBUTE_CHECKED;
                $productStatusValue = 1;
                $productStatusPicture = 'status_green.gif';
            }
            $specialOffer = '';
            $specialOfferValue = '';
            if ($objProduct->isSpecialoffer()) {
                $specialOffer = HTML_ATTRIBUTE_CHECKED;
                $specialOfferValue = 1;
            }

            self::$objTemplate->setVariable(array(
                'SHOP_ROWCLASS' => (++$i % 2 ? 'row1' : 'row2'),
                'SHOP_PRODUCT_ID' => $objProduct->getId(),
                'SHOP_PRODUCT_CUSTOM_ID' => $objProduct->getCode(),
                'SHOP_PRODUCT_NAME' => $objProduct->getName(),
                'SHOP_PRODUCT_PRICE1' => Currency::formatPrice($objProduct->getPrice()),
                'SHOP_PRODUCT_PRICE2' => Currency::formatPrice($objProduct->getResellerprice()),
                'SHOP_PRODUCT_DISCOUNT' => Currency::formatPrice($objProduct->getDiscountprice()),
                'SHOP_PRODUCT_SPECIAL_OFFER' => $specialOffer,
                'SHOP_SPECIAL_OFFER_VALUE_OLD' => $specialOfferValue,
                'SHOP_PRODUCT_VAT_MENU' => Vat::getShortMenuString(
                    $objProduct->getVatId(),
                    'taxId['.$objProduct->getId().']'),
                'SHOP_PRODUCT_VAT_ID' => ($objProduct->getVatId()
                    ? $objProduct->getVatId() : 'NULL'),
                'SHOP_PRODUCT_DISTRIBUTION' => $objProduct->getDistribution(),
                'SHOP_PRODUCT_STOCK' => $objProduct->getStock(),
                'SHOP_PRODUCT_SHORT_DESC' => $objProduct->getShortdesc(),
                'SHOP_PRODUCT_STATUS' => $productStatus,
                'SHOP_PRODUCT_STATUS_PICTURE' => $productStatusPicture,
                'SHOP_ACTIVE_VALUE_OLD' => $productStatusValue,
                'SHOP_SORT_ORDER' => $objProduct->getOrder(),
//                'SHOP_DISTRIBUTION_MENU' => Distribution::getDistributionMenu($objProduct->getDistribution(), "distribution[".$objProduct->getId()."]"),
//                'SHOP_PRODUCT_WEIGHT' => Weight::getWeightString($objProduct->getWeight()),
                'SHOP_DISTRIBUTION' => $_ARRAYLANG['TXT_DISTRIBUTION_'.
                    strtoupper($objProduct->getDistribution())],
                'SHOP_SHOW_PRODUCT_ON_START_PAGE_CHECKED' =>
                    ($objProduct->isShownOnStartpage()
                      ? HTML_ATTRIBUTE_CHECKED : ''),
                'SHOP_SHOW_PRODUCT_ON_START_PAGE_OLD' =>
                    ($objProduct->isShownOnStartpage() ? '1' : ''),
// This is used when the Product name can be edited right on the overview
                'SHOP_TITLE' => htmlentities(
                    $objProduct->getName(), ENT_QUOTES, CONTREXX_CHARSET),
            ));
            self::$objTemplate->parse('productRow');
        }
        return true;
    }


    /**
     * Store any Products that have been modified.
     *
     * Takes the Product data directly from the various fields of the
     * $_POST array.  Only updates the database records for Products that
     * have at least one of their values changed.
     * @return  boolean                     True on success, false otherwise.
     * @global  array       $_ARRAYLANG     Language array
     */
    function storeProducts()
    {
        global $_ARRAYLANG;

        $arrError = array();
        foreach (array_keys($_POST['ProductId']) as $id) {
            $productIdentifier = $_POST['identifier'][$id];
            $productIdentifierOld = $_POST['identifierOld'][$id];
            $sortOrder = $_POST['SortOrder'][$id];
            $sortOrderOld = $_POST['SortOrderOld'][$id];
            $specialOffer = (isset($_POST['specialOffer'][$id]) ? 1 : 0);
            $specialOfferOld = $_POST['specialOfferOld'][$id];
            $discount = $_POST['discount'][$id];
            $discountOld = $_POST['discountOld'][$id];
            $normalprice = $_POST['price1'][$id];
            $normalpriceOld = $_POST['price1Old'][$id];
            $resellerprice = $_POST['price2'][$id];
            $resellerpriceOld = $_POST['price2Old'][$id];
            $stock = $_POST['stock'][$id];
            $stockOld = $_POST['stockOld'][$id];
            $status = (isset($_POST['active'][$id]) ? 1 : 0);
            $statusOld = $_POST['activeOld'][$id];
            $vat_id = (isset($_POST['taxId'][$id]) ? $_POST['taxId'][$id] : 0);
            $vat_id_old = $_POST['taxIdOld'][$id];
            $shownOnStartpage = (isset($_POST['shownonstartpage'][$id]) ? $_POST['shownonstartpage'][$id] : 0);
            $shownOnStartpageOld = (isset($_POST['shownonstartpageOld'][$id]) ? $_POST['shownonstartpageOld'][$id] : 0);
// This is used when the Product name can be edited right on the overview
            $title = $_POST['title'][$id];
            $titleOld = $_POST['titleOld'][$id];

/*
    Distribution and weight have been removed from the overview due to the
    changes made to the delivery options.
            $distribution = $_POST['distribution'][$id];
            $distributionOld = $_POST['distributionOld'][$id];
            $weight = $_POST['weight'][$id];
            $weightOld = $_POST['weightOld'][$id];
            // Flag used to determine whether the record has to be
            // updated in the database
            $updateProduct = false;
            // Check whether the weight was changed
            if ($weight != $weightOld) {
                // Changed.
                // If it's empty, set to NULL and don't complain.
                // The NULL weight will be silently ignored by the database.
                if ($weight == '') {
                    $weight = 'NULL';
                } else {
                    // Check the format
                    $weight = Weight::getWeight($weight);
                    // The NULL weight will be silently ignored by the database.
                    if ($weight === 'NULL') {
                        // 'NULL', the format was invalid. cast error
                        self::addError($_ARRAYLANG['TXT_WEIGHT_INVALID_IGNORED']);
                    } else {
                        // If getWeight() returns any other value, the format
                        // is valid.  Verify that the numeric value has changed
                        // as well; might be that the user simply removed the
                        // unit ('g').
                        if ($weight != Weight::getWeight($weightOld)) {
                            // Really changed
                            $updateProduct = true;
                        }
                        // Otherwise, the new amd old values are the same.
                    }
                }
            }
            if ($updateProduct === false) {
                // reset the weight to the old and, hopefully, correct value,
                // in case the record is updated anyway
                $weight = Weight::getWeight($weightOld);
            }
*/

            // Check if any one value has been changed
            if (   $productIdentifier != $productIdentifierOld
                || $sortOrder != $sortOrderOld
                || $specialOffer != $specialOfferOld
                || $discount != $discountOld
                || $normalprice != $normalpriceOld
                || $resellerprice != $resellerpriceOld
                || $stock != $stockOld
                || $status != $statusOld
                || $vat_id != $vat_id_old
                || $shownOnStartpage != $shownOnStartpageOld
// This is used when the Product name can be edited right on the overview
                || $title != $titleOld
/*
                || $distribution != $distributionOld
                // Weight, see above
                || $updateProduct
*/
            ) {
                $arrProducts =
//                    ($productIdentifierOld != ''
//                        ? Products::getByCustomId($productIdentifierOld) :
                    array(Product::getById($id))
//                );
                    ;
                if (!is_array($arrProducts)) {
                    continue;
                }
                foreach ($arrProducts as $objProduct) {
                    if (!$objProduct) {
                        $arrError[$productIdentifier] = true;
                        continue;
                    }
                    $objProduct->setCode($productIdentifier);
                    $objProduct->setOrder($sortOrder);
                    $objProduct->setSpecialOffer($specialOffer);
                    $objProduct->setDiscountPrice($discount);
                    $objProduct->setPrice($normalprice);
                    $objProduct->setResellerPrice($resellerprice);
                    $objProduct->setStock($stock);
                    $objProduct->setStatus($status);
                    $objProduct->setVatId($vat_id);
//                    $objProduct->setDistribution($distribution);
//                    $objProduct->setWeight($weight);
                    $objProduct->setShownOnStartpage($shownOnStartpage);
// This is used when the Product name can be edited right on the overview
                    $objProduct->setName(contrexx_stripslashes($title));
                    if (!$objProduct->store()) {
                        $arrError[$productIdentifier] = true;
                    }
                }
            }
        }
        if (empty($arrError)) {
            self::addMessage($_ARRAYLANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL']);
            return true;
        }
        self::addError($_ARRAYLANG['TXT_SHOP_ERROR_UPDATING_RECORD']);
        return false;
    }


    /**
     * Get some statistical stuff
     *
     * @global    ADONewConnection
     * @global    array      $_ARRAYLANG
     */
    function shopOrderStatistics()
    {
        global $objDatabase, $_ARRAYLANG;

        $paging = "";
        $sumColumn4 = 0;
        $sumColumn3 = 0;
        $sumColumn2 ="";
        $totalSoldProducts = 0;
        $totalOrderSum = 0.00;
        $totalOrders = 0;
        $bestMonthSum = 0;
        $bestMonthDate = "";
        $orders = false;
        $arrShopMonthSum = array();

        self::$objTemplate->loadTemplateFile("module_shop_statistic.html", true, true);

        // Get the first order date, if its empty, no order has been made yet!
        $query = "
            SELECT DATE_FORMAT(order_date,'%Y') AS year, DATE_FORMAT(order_date,'%m') AS month
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders
             WHERE order_status=".SHOP_ORDER_STATUS_CONFIRMED."
                OR order_status=".SHOP_ORDER_STATUS_COMPLETED."
             ORDER BY order_date asc";
        $objResult = $objDatabase->SelectLimit($query, 1);
        if (!$objResult) {
            $this->errorHandling();
        }
        if (!$objResult->EOF) {
            $orders = true;
            $orderStartyear = $objResult->fields['year'];
            $orderStartmonth = $objResult->fields['month'];
        }
        $i = 0;
        if ($orders) { //some orders has been made
            //query to get the ordersum, total orders, best month
            $query = "
                SELECT selected_currency_id, currency_order_sum,
                       DATE_FORMAT(order_date,'%m') AS month,
                       DATE_FORMAT(order_date,'%Y') AS year
                  FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders
                 WHERE order_status=".SHOP_ORDER_STATUS_CONFIRMED."
                    OR order_status=".SHOP_ORDER_STATUS_COMPLETED."
                 ORDER BY order_date DESC";
            if (($objResult = $objDatabase->Execute($query)) !== false) {
                while (!$objResult->EOF) {
                    $orderSum = Currency::getDefaultCurrencyPrice($objResult->fields['currency_order_sum']);
                    if (!isset($arrShopMonthSum[$objResult->fields['year']][$objResult->fields['month']])) {
                        $arrShopMonthSum[$objResult->fields['year']][$objResult->fields['month']] = 0;
                    }
                    $arrShopMonthSum[$objResult->fields['year']][$objResult->fields['month']] += $orderSum;
                    $totalOrderSum += $orderSum;
                    $totalOrders++;
                    $objResult->MoveNext();
                }
                $months = explode(',', $_ARRAYLANG['TXT_MONTH_ARRAY']);
                foreach ($arrShopMonthSum as $year => $arrMonth) {
                    foreach ($arrMonth as $month => $sum) {
                        if ($bestMonthSum < $sum) {
                            $bestMonthSum = $sum;
                            $bestMonthDate = $months[$month-1].' '.$year;
                        }
                    }
                }
            } else {
                $this->errorHandling();
            }

            //get the total sum of sold products
            $query = "
                SELECT sum(A.quantity) AS shopTotalSoldProducts
                  FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items AS A,
                       ".DBPREFIX."module_shop".MODULE_INDEX."_orders AS B
                 WHERE A.orderid=B.orderid
                   AND (   B.order_status=".SHOP_ORDER_STATUS_CONFIRMED."
                        OR B.order_status=".SHOP_ORDER_STATUS_COMPLETED.")";
            $objResult = $objDatabase->SelectLimit($query, 1);
            if ($objResult) {
                if (!$objResult->EOF) {
                    $totalSoldProducts = $objResult->fields['shopTotalSoldProducts'];
                    $objResult->MoveNext();
                }
            }

            //if an timeperiod is set, set the start and stop date
            if (isset($_REQUEST['submitdate'])) {
                self::$objTemplate->setVariable(array(
                    'SHOP_START_MONTH' => $this->shop_getMonthDropdwonMenu(
                        intval($_REQUEST['startmonth'])),
                    'SHOP_END_MONTH' => $this->shop_getMonthDropdwonMenu(
                        intval($_REQUEST['stopmonth'])),
                    'SHOP_START_YEAR' => $this->shop_getYearDropdwonMenu(
                        $orderStartyear, intval($_REQUEST['startyear'])),
                    'SHOP_END_YEAR' => $this->shop_getYearDropdwonMenu(
                        $orderStartyear, intval($_REQUEST['stopyear'])),
                ));
// TODO: Aww, use strtotime() here
                $startDate =
                    intval($_REQUEST['startyear'])."-".
                    sprintf("%02s", intval($_REQUEST['startmonth'])).
                    "-01 00:00:00";
// TODO ... and here
                $stopDate =
                    intval($_REQUEST['stopyear'])."-".
                    sprintf("%02s", intval($_REQUEST['stopmonth'])).
                    "-".
                    date(
                      't',
                      mktime(0, 0, 0,
                        intval($_REQUEST['stopmonth']),
                        1,
                        intval($_REQUEST['stopyear']))
                    )." 23:59:59";
            } else {   //set timeperiod to max. one year
                $lastYear = Date('Y');
                if ($orderStartyear < Date('Y')) {
                    $orderStartmonth = Date('m');
                    $lastYear = Date('Y')-1;
                }
                $endMonth = Date('m');
                self::$objTemplate->setVariable(array(
                    'SHOP_START_MONTH' =>
                        $this->shop_getMonthDropdwonMenu($orderStartmonth),
                    'SHOP_END_MONTH' =>
                        $this->shop_getMonthDropdwonMenu($endMonth),
                    'SHOP_START_YEAR' =>
                        $this->shop_getYearDropdwonMenu(
                            $orderStartyear, $lastYear),
                    'SHOP_END_YEAR' =>
                        $this->shop_getYearDropdwonMenu(
                            $orderStartyear, date('Y')),
                ));
                $startDate =
                    $lastYear."-".$orderStartmonth."-01 00:00:00";
                $stopDate =
                    date('Y')."-".$endMonth."-".
                    date('t', mktime(0, 0, 0, $endMonth, 1, date('Y'))).
                    " 23:59:59";
            }
            //check if a statistic has been requested
            $selectedStat =
                (isset($_REQUEST['selectstats'])
                    ? intval($_REQUEST['selectstats']) : 0);
            if ($selectedStat == 2) {
                //query for articles stats
                self::$objTemplate->setVariable(array(
                    'TXT_COLUMN_1_DESC' => $_ARRAYLANG['TXT_PRODUCT_NAME'],
                    'TXT_COLUMN_2_DESC' => $_ARRAYLANG['TXT_COUNT_ARTICLES'],
                    'TXT_COLUMN_3_DESC' => $_ARRAYLANG['TXT_STOCK'],
                    'SHOP_ORDERS_SELECTED' => '',
                    'SHOP_ARTICLES_SELECTED' => HTML_ATTRIBUTE_SELECTED,
                    'SHOP_CUSTOMERS_SELECTED' => '',
                ));
                $query = "
                    SELECT A.quantity AS shopColumn2, A.productid AS id,
                           A.price AS sum,
                           B.title AS title, B.stock AS shopColumn3,
                           C.selected_currency_id
                      FROM  ".DBPREFIX."module_shop".MODULE_INDEX."_order_items AS A,
                            ".DBPREFIX."module_shop".MODULE_INDEX."_products AS B,
                            ".DBPREFIX."module_shop".MODULE_INDEX."_orders AS C
                      WHERE A.productid=B.id AND A.orderid=C.orderid
                        AND C.order_date >= '$startDate'
                        AND C.order_date <= '$stopDate'
                        AND (   C.order_status=".SHOP_ORDER_STATUS_CONFIRMED."
                             OR C.order_status=".SHOP_ORDER_STATUS_COMPLETED.")
                      ORDER BY shopColumn2 DESC";
            } elseif ( $selectedStat == 3) {
                // query for customer stats
                self::$objTemplate->setVariable(array(
                    'TXT_COLUMN_1_DESC' => $_ARRAYLANG['TXT_NAME'],
                    'TXT_COLUMN_2_DESC' => $_ARRAYLANG['TXT_COMPANY'],
                    'TXT_COLUMN_3_DESC' => $_ARRAYLANG['TXT_COUNT_ARTICLES'],
                    'SHOP_ORDERS_SELECTED' => '',
                    'SHOP_ARTICLES_SELECTED' => '',
                    'SHOP_CUSTOMERS_SELECTED' => HTML_ATTRIBUTE_SELECTED,
                ));
                $query = "
                    SELECT A.currency_order_sum AS sum,
                           A.selected_currency_id AS currency_id,
                           C.company AS shopColumn2,
                           sum(B.quantity) AS shopColumn3,
                           C.lastname
                           C.firstname
                           C.prefix
                           C.customerid AS id
                      FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders AS A,
                           ".DBPREFIX."module_shop".MODULE_INDEX."_order_items AS B,
                           ".DBPREFIX."module_shop".MODULE_INDEX."_customers AS C
                     WHERE A.orderid=B.orderid
                       AND A.customerid=C.customerid
                       AND A.order_date>='$startDate'
                       AND A.order_date<='$stopDate'
                       AND (   A.order_status=".SHOP_ORDER_STATUS_CONFIRMED."
                            OR A.order_status=".SHOP_ORDER_STATUS_COMPLETED.")
                     GROUP BY B.orderid
                     ORDER BY sum DESC";
            } else {
                //query for order stats (default)
                //sells per month
                self::$objTemplate->setVariable(array(
                    'TXT_COLUMN_1_DESC' => $_ARRAYLANG['TXT_DATE'],
                    'TXT_COLUMN_2_DESC' => $_ARRAYLANG['TXT_COUNT_ORDERS'],
                    'TXT_COLUMN_3_DESC' => $_ARRAYLANG['TXT_COUNT_ARTICLES'],
                    'SHOP_ORDERS_SELECTED' => HTML_ATTRIBUTE_SELECTED,
                    'SHOP_ARTICLES_SELECTED' => '',
                    'SHOP_CUSTOMERS_SELECTED' => '',
                ));
                $query = "
                    SELECT sum(A.quantity) AS shopColumn3,
                           count(A.orderid) AS shopColumn2,
                           B.selected_currency_id,
                           B.currency_order_sum AS sum,
                           DATE_FORMAT(B.order_date,'%m') AS month,
                           DATE_FORMAT(B.order_date,'%Y') AS year
                      FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items AS A,
                           ".DBPREFIX."module_shop".MODULE_INDEX."_orders AS B
                     WHERE A.orderid = B.orderid
                       AND B.order_date >= '$startDate'
                       AND B.order_date <= '$stopDate'
                       AND (   B.order_status=".SHOP_ORDER_STATUS_CONFIRMED."
                            OR B.order_status=".SHOP_ORDER_STATUS_COMPLETED.")
                     GROUP BY B.orderid
                     ORDER BY year, month DESC";
            }

            $arrayResults = array();
            if (($objResult = $objDatabase->Execute($query)) === false) {    //execute the query again with paging limit set
                $this->errorHandling();
            } else {
                if ($selectedStat == 2) { //it's the article statistc
                    while (!$objResult->EOF) {
                        // set currency id
                        Currency::setActiveCurrencyId($objResult->fields['selected_currency_id']);
                        $key = $objResult->fields['id'];
                        if (!isset($arrayResults[$key])) {
                            $arrayResults[$key] = array(
                                'column1' => '',
                                'column2' => 0,
                                'column3' => 0,
                                'column4' => 0,
                            );
                        }
                        $arrayResults[$key]['column2'] = $arrayResults[$key]['column2'] + $objResult->fields['shopColumn2'];
                        $arrayResults[$key]['column1'] = "<a href='?cmd=shop".MODULE_INDEX."&amp;act=products&amp;tpl=manage&amp;id=".$objResult->fields['id']."' title=\"".$objResult->fields['title']."\">".$objResult->fields['title']."</a>";
                        $arrayResults[$key]['column3'] = $objResult->fields['shopColumn3'];
                        $arrayResults[$key]['column4'] = $arrayResults[$key]['column4'] + $objResult->fields['shopColumn2'] * Currency::getDefaultCurrencyPrice($objResult->fields['sum']);
                        $objResult->MoveNext();
                    }
                    if (is_array($arrayResults)) {
                        foreach ($arrayResults AS $entry) {
                            $sumColumn2 = $sumColumn2 + $entry['column2'];
                            $sumColumn3 = $sumColumn3 + $entry['column3'];
                            $sumColumn4 = $sumColumn4 + $entry['column4'];
                        }
                        rsort($arrayResults);
                    }
                } elseif ($selectedStat == 3) {
                    //is customer statistic
                    while (!$objResult->EOF) {
                        // set currency id
                        Currency::setActiveCurrencyId($objResult->fields['currency_id']);
                        $key = $objResult->fields['id'];
                        $customerName = ltrim($objResult->fields['prefix'].' '.$objResult->fields['firstname'].' '.$objResult->fields['lastname']);
                        if (!isset($arrayResults[$key])) {
                            $arrayResults[$key] = array(
                                'column1' => '',
                                'column2' => 0,
                                'column3' => 0,
                                'column4' => 0,
                            );
                        }
                        $arrayResults[$key]['column1'] = "<a href='index.php?cmd=shop".MODULE_INDEX."&amp;act=customerdetails&amp;customerid=".$objResult->fields['id']."'>$customerName</a>";
                        $arrayResults[$key]['column2'] = $objResult->fields['shopColumn2'];
                        $arrayResults[$key]['column3'] += $objResult->fields['shopColumn3'];
                        $arrayResults[$key]['column4'] += Currency::getDefaultCurrencyPrice($objResult->fields['sum']);
                        $sumColumn3 += $objResult->fields['shopColumn3'];
                        $sumColumn4 += Currency::getDefaultCurrencyPrice($objResult->fields['sum']);
                        $objResult->MoveNext();
                    }
                } else { //it's the default statistic (orders)
                    $arrayMonths=explode(',', $_ARRAYLANG['TXT_MONTH_ARRAY']);
                    while (!$objResult->EOF) {
                        $key = $objResult->fields['year'].".".$objResult->fields['month'];
                        if (!isset($arrayResults[$key])) {
                            $arrayResults[$key] = array(
                                'column1' => '',
                                'column2' => 0,
                                'column3' => 0,
                                'column4' => 0,
                            );
                        }
                        $arrayResults[$key]['column1'] = $arrayMonths[intval($objResult->fields['month'])-1].' '.$objResult->fields['year'];
                        $arrayResults[$key]['column2'] = $arrayResults[$key]['column2'] +1;
                        $arrayResults[$key]['column3'] = $arrayResults[$key]['column3'] + $objResult->fields['shopColumn3'];
                        $arrayResults[$key]['column4'] = $arrayResults[$key]['column4'] + Currency::getDefaultCurrencyPrice($objResult->fields['sum']);
                        $sumColumn2 = $sumColumn2 + 1;
                        $sumColumn3 = $sumColumn3 + $objResult->fields['shopColumn3'];
                        $sumColumn4 = $sumColumn4 + Currency::getDefaultCurrencyPrice($objResult->fields['sum']);
                        $objResult->MoveNext();
                    }
                    krsort($arrayResults, SORT_NUMERIC);
                }
                //set block an read whole array out
                self::$objTemplate->setCurrentBlock('statisticRow');
                if (is_array($arrayResults)) {
                    foreach ($arrayResults as $entry) {
                        self::$objTemplate->setVariable(array(
                            'SHOP_ROWCLASS' => (++$i % 2 ? 'row1' : 'row2'),
                            'SHOP_COLUMN_1' => $entry['column1'],
                            'SHOP_COLUMN_2' => $entry['column2'],
                            'SHOP_COLUMN_3' => $entry['column3'],
                            'SHOP_COLUMN_4' =>
                                Currency::formatPrice($entry['column4']).' '.
                                Currency::getDefaultCurrencySymbol(),
                        ));
                        self::$objTemplate->parse('statisticRow');
                    }
                }
            }
        } else {
            $sumColumn2 = 0;
            $arrayMonths=explode(',', $_ARRAYLANG['TXT_MONTH_ARRAY']);
            $actualMonth = "<option value=\"".Date('m')."\">".$arrayMonths[Date('m')-1]."</option>\n";
            $actualYear = "<option value=\"".Date('Y')."\">".Date('Y')."</option>\n";
            self::$objTemplate->setVariable(array(
                'SHOP_START_MONTH' => $actualMonth,
                'SHOP_END_MONTH' => $actualMonth,
                'SHOP_START_YEAR' => $actualYear,
                'SHOP_END_YEAR' => $actualYear,
                'TXT_COLUMN_1_DESC' => $_ARRAYLANG['TXT_DATE'],
                'TXT_COLUMN_2_DESC' => $_ARRAYLANG['TXT_COUNT_ORDERS'],
                'TXT_COLUMN_3_DESC' => $_ARRAYLANG['TXT_COUNT_ARTICLES'],
                'SHOP_ORDERS_SELECTED' => HTML_ATTRIBUTE_SELECTED,
                'SHOP_ARTICLES_SELECTED' => '',
                'SHOP_CUSTOMERS_SELECTED' => '',
            ));
        }
        //set the variables for the sum
        self::$objTemplate->setVariable(array(
            'SHOP_ROWCLASS' => (++$i % 2 ? 'row1' : 'row2'),
            'SHOP_TOTAL_SUM' =>
                Currency::formatPrice($totalOrderSum).' '.
                Currency::getDefaultCurrencySymbol(),
            'SHOP_MONTH' => $bestMonthDate,
            'SHOP_MONTH_SUM' =>
                Currency::formatPrice($bestMonthSum).' '.
                Currency::getDefaultCurrencySymbol(),
            'SHOP_TOTAL_ORDERS' => $totalOrders,
            'SHOP_SOLD_ARTICLES' => $totalSoldProducts,
            'SHOP_SUM_COLUMN_2' => $sumColumn2,
            'SHOP_SUM_COLUMN_3' => $sumColumn3,
            'SHOP_SUM_COLUMN_4' =>
                Currency::formatPrice($sumColumn4).' '.
                Currency::getDefaultCurrencySymbol(),
            'SHOP_STATISTIC_PAGING' => $paging,
        ));
    }


    function shop_getMonthDropdwonMenu($selectedOption='')
    {
        global $_ARRAYLANG;

        $strMenu = '';
        $months = explode(',', $_ARRAYLANG['TXT_MONTH_ARRAY']);
        foreach ($months as $index => $name) {
            $monthNumber = $index + 1;
            $strMenu .=
                "<option value='$monthNumber'".
                ($selectedOption == $monthNumber
                    ? HTML_ATTRIBUTE_SELECTED : '').
                ">$name</option>\n";
        }
        return $strMenu;
    }


    function shop_getYearDropdwonMenu($startYear, $selectedOption='')
    {
        $strMenu = '';
        $yearNow = date('Y');
        while ($startYear <= $yearNow) {
            $strMenu .=
                "<option value='$startYear'".
                ($selectedOption == $startYear
                    ?   HTML_ATTRIBUTE_SELECTED
                    :   ''
                ).
                ">$startYear</option>\n";
            ++$startYear;
        }
        return $strMenu;
    }


    /**
     * Set the database query error Message
     * @global    array      $_ARRAYLANG
     */
    function errorHandling()
    {
        global $_ARRAYLANG;

        self::addError($_ARRAYLANG['TXT_DATABASE_QUERY_ERROR']);
    }


    /**
     * Shows an overview of all pricelists
     * @global    array     $_ARRAYLANG
     * @global  ADONewConnection  $objDatabase    Database connection object
     */
    function shopPricelistOverview()
    {
        global $objDatabase, $_ARRAYLANG;

        self::$objTemplate->loadTemplateFile("module_shop_pricelist_overview.html", true, true);
        $row_color = 0;
        $query = "SELECT id, name FROM ".DBPREFIX."module_shop".MODULE_INDEX."_pricelists ORDER BY name ASC";
        $objResult = $objDatabase->Execute($query);
        if ($objResult->EOF) {
            self::$objTemplate->hideBlock('shopPricelistOverview');
            return;
        }
        self::$objTemplate->setCurrentBlock('showPricelists');
        while (!$objResult->EOF) {
            self::$objTemplate->setVariable(array(
                'PRICELIST_OVERVIEW_ROWCOLOR' => 'row'.(++$row_color % 2 + 1),
                'PRICELIST_OVERVIEW_ID' => $objResult->fields['id'],
                'PRICELIST_OVERVIEW_NAME' => $objResult->fields['name'],
                'PRICELIST_OVERVIEW_PDFLINK' =>
                    "<a href='".ASCMS_PATH_OFFSET.'/modules/shop/pdf.php?plid='.
                    $objResult->fields['id']."' target='_blank' title='".
                    $_ARRAYLANG['TXT_DISPLAY']."'>".
                    'http://'.$_SERVER['HTTP_HOST'].ASCMS_PATH_OFFSET.
                    '/modules/shop/pdf.php?plid='.$objResult->fields['id'].'</a>'
            ));
            self::$objTemplate->parse('showPricelists');
            $row_color++;
            $objResult->MoveNext();
        }
    }


    /**
     * Shows an overview of all pricelists
     * @version 1.0     initial version
     * @global  array   $_ARRAYLANG
     */
    function shopPricelistNew()
    {
        global $objDatabase, $_ARRAYLANG;

        self::$objTemplate->loadTemplateFile("module_shop_pricelist_details.html", true, true);

        // generate langauge menu
        $langMenu = "<select name=\"lang_id\" size=\"1\">\n";
// TODO: Use FWLanguage
        $query = "
            SELECT id, name, is_default
              FROM ".DBPREFIX."languages
             WHERE backend=1";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
            while (!$objResult->EOF) {
                $langMenu .=
                    "<option value=\"".$objResult->fields['id']."\"".
                    ($objResult->fields['is_default'] == 'true'
                        ? " selected=\"selected\"" : "").
                    ">".$objResult->fields['name']."</option>\n";
                $objResult->MoveNext();
            }
        }
        $langMenu .= "</select>\n";

        self::$objTemplate->setVariable(array(
            'SHOP_PRICELIST_DETAILS_PLID' => 'new',
            'SHOP_PRICELIST_DETAILS_ACT' => 'pricelist_insert',
            'SHOP_PRICELIST_PDFLINK' => '&nbsp;',
            'SHOP_PRICELIST_DETAILS_NAME' => '',
            'SHOP_PRICELIST_DETAILS_BORDERON' => HTML_ATTRIBUTE_CHECKED,
            'SHOP_PRICELIST_DETAILS_BORDEROFF' => '',
            'SHOP_PRICELIST_DETAILS_HEADERON' => HTML_ATTRIBUTE_CHECKED,
            'SHOP_PRICELIST_DETAILS_HEADEROFF' => '',
            'SHOP_PRICELIST_DETAILS_HEADERLEFT' => '',
            'SHOP_PRICELIST_DETAILS_HEADERRIGHT' => '',
            'SHOP_PRICELIST_DETAILS_FOOTERON' => HTML_ATTRIBUTE_CHECKED,
            'SHOP_PRICELIST_DETAILS_FOOTEROFF' => '',
            'SHOP_PRICELIST_DETAILS_FOOTERLEFT' => '',
            'SHOP_PRICELIST_DETAILS_FOOTERRIGHT' => '',
            'SHOP_PRICELIST_DETAILS_ALLPROD' => HTML_ATTRIBUTE_CHECKED,
            'SHOP_PRICELIST_DETAILS_SEPPROD' => '',
            'SHOP_PRICELIST_DETAILS_LANGUAGE' => $langMenu
        ));
        $selectedCategories = '*';
        $this->shopPricelistMainCategories($selectedCategories);
    }


    /**
     * Returns the Maincategories for the PDF-Selections
     *
     * @global    var        $objDatabase
     */
    function shopPricelistMainCategories($selectedCategories)
    {
        global $objDatabase;

        $arrCategories = ShopCategories::getChildCategoriesById(0, false);
        if (empty($arrCategories)) return false;
        $row_color = 0;
        foreach ($arrCategories as $objCategory) {
            $category_id = $objCategory->getId();
            $in_selected = preg_match(
                '/(?:^|,)'.$category_id.'(?:,|$)/',
                $selectedCategories);
            if ($selectedCategories != '*' && !$in_selected) continue;
            self::$objTemplate->setVariable(
                'PDF_CATEGORY_NAME', $objCategory->getName());
            if ($selectedCategories == '*') {
                self::$objTemplate->setVariable(
                    'PDF_CATEGORY_DISABLED', HTML_ATTRIBUTE_DISABLED);
            } else {
                if ($in_selected) {
                    self::$objTemplate->setVariable(
                        'PDF_CATEGORY_CHECKED', HTML_ATTRIBUTE_CHECKED);
                }
            }
            self::$objTemplate->setVariable(array(
                'PDF_CATEGORY_ID' => $category_id,
                'PDF_CATEGORY_ID2' => $category_id,
                'PDF_CATEGORY_ID3' => $category_id,
                'CATEGORY_OVERVIEW_ROWCOLOR' => 'row'.(++$row_color % 2 + 1),
            ));
            self::$objTemplate->parse('showShopCategories');
            self::$objTemplate->parse('showShopCategories2');
            self::$objTemplate->parse('showShopCategories3');
        }
        return true;
    }


    /**
     * Inserts a new pricelist into the database
     * @global    var        $objDatabase
     * @global    array    $_ARRAYLANG
     */
    function shopPricelistInsert()
    {
        global $objDatabase, $_ARRAYLANG;

        $selectedCategories = '';
        if ($_POST['productsAll']) {
            $selectedCategories = '*';
        } else {
            foreach ($_POST as $key => $value) {
                if (substr($key,0,14) == 'categoryNumber') {
                    $arrSelectedMainCats[$value] = $value;
                }
            }
            foreach ($arrSelectedMainCats as $key => $value) {
                foreach (array_keys(ShopCategory::getCategoryTree($value, true))
                        as $catKey) {
                    $selectedCategories .= $catKey.',';
                }
                // Add the root category
                $selectedCategories .= $value;
            }
            // If no groups were selected, select all.  Prevents errors.
            if (empty($selectedCategories)) $selectedCategories = '*';
        }

        if (empty($_POST['pricelistName'])) {
            $_POST['pricelistName'] = $_ARRAYLANG['TXT_NO_NAME'];
        }

        $query = "
            INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_pricelists
            SET id='',
                name='".addslashes(strip_tags($_POST['pricelistName']))."',
                lang_id=".intval($_POST['lang_id']).",
                border_on=".intval($_POST['borderOn']).",
                header_on=".intval($_POST['headerOn']).",
                header_left='".addslashes(trim($_POST['headerTextLeft']))."',
                header_right='".addslashes(trim($_POST['headerTextRight']))."',
                footer_on=".intval($_POST['footerOn']).",
                footer_left='".addslashes(trim($_POST['footerTextLeft']))."',
                footer_right='".addslashes(trim($_POST['footerTextRight']))."',
                categories='".addslashes($selectedCategories)."'";
        $objDatabase->Execute($query);
        self::addMessage($_ARRAYLANG['TXT_PRODUCT_LIST_CREATED_SUCCESSFUL']);
    }


    /**
     * Edit a pricelist
     * @global    var        $objDatabase
     * @global    array    $_ARRAYLANG
     */
    function shopPricelistEdit($pricelistID)
    {
        global $objDatabase, $_ARRAYLANG;

        self::$objTemplate->loadTemplateFile("module_shop_pricelist_details.html", true, true);

        $objResult = $objDatabase->Execute("
            SELECT `id`, `name`, `lang_id`,
                   `border_on`,
                   `header_on`, `header_left`, `header_right`
                   `footer_on`, `footer_left`, `footer_right`
                   `categories`
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_pricelists
             WHERE id=$pricelistID");
        self::$objTemplate->setVariable(array(
            'SHOP_PRICELIST_DETAILS_ACT' =>
                'pricelist_update&amp;id='.$objResult->fields['id'],
            'SHOP_PRICELIST_PDFLINK' =>
                '<a href="'.ASCMS_PATH_OFFSET.'/modules/shop/pdf.php?plid='.
                $objResult->fields['id'].'" target="_blank" title="PDF">'.
                'http://'.$_SERVER['HTTP_HOST'].ASCMS_PATH_OFFSET.
                '/modules/shop/pdf.php?plid='.$objResult->fields['id'].'</a>',
            'SHOP_PRICELIST_DETAILS_NAME' => $objResult->fields['name'],
            'SHOP_PRICELIST_DETAILS_LANGUAGE' => Html::getSelect(
                'lang_id', FWLanguage::getNameArray(),
                $objResult->fields['id'], false, '', 'size="1"'),
            ($objResult->fields['border_on']
                ? 'SHOP_PRICELIST_DETAILS_BORDERON'
                : 'SHOP_PRICELIST_DETAILS_BORDEROFF') => HTML_ATTRIBUTE_CHECKED,
            ($objResult->fields['header_on']
                ? 'SHOP_PRICELIST_DETAILS_HEADERON'
                : 'SHOP_PRICELIST_DETAILS_HEADEROFF') => HTML_ATTRIBUTE_CHECKED,
            'SHOP_PRICELIST_DETAILS_HEADERLEFT' =>
                ($objResult->fields['header_on']
                  ? $objResult->fields['header_left'] : ''),
            'SHOP_PRICELIST_DETAILS_HEADERRIGHT' =>
                ($objResult->fields['header_on']
                  ? $objResult->fields['header_right'] : ''),
            ($objResult->fields['footer_on']
                ? 'SHOP_PRICELIST_DETAILS_FOOTERON'
                : 'SHOP_PRICELIST_DETAILS_FOOTEROFF') => HTML_ATTRIBUTE_CHECKED,
            'SHOP_PRICELIST_DETAILS_FOOTERLEFT' =>
                ($objResult->fields['footer_on']
                  ? $objResult->fields['footer_left'] : ''),
            'SHOP_PRICELIST_DETAILS_FOOTERRIGHT' =>
                ($objResult->fields['footer_on']
                  ? $objResult->fields['footer_right'] : ''),
        ));
        //which products were selected before? All or seperate?
        if ($objResult->fields['categories'] == '*') { // all categories
            self::$objTemplate->setVariable(
                'SHOP_PRICELIST_DETAILS_ALLPROD', HTML_ATTRIBUTE_CHECKED);
        } else {
            // I have to split the string into a nice array :)
            self::$objTemplate->setVariable(array(
                'SHOP_PRICELIST_DETAILS_SEPPROD' => HTML_ATTRIBUTE_CHECKED,
            ));
        }
        $this->shopPricelistMainCategories($objResult->fields['categories']);
    }


    /**
     * Update a pricelist entry in the database
     * @param   integer   $pricelistID      The pricelist ID
     * @global  mixed     $objDatabase
     * @global  array     $_ARRAYLANG
     */
    function shopPricelistUpdate($pricelistID)
    {
        global $objDatabase, $_ARRAYLANG;

        $selectedCategories = '';
        if ($_POST['productsAll']) {
            $selectedCategories = '*';
        } else {
            foreach ($_POST as $key => $value) {
                if (substr($key, 0, 14) == 'categoryNumber') {
                    $arrSelectedMainCats[$value] = $value;
                }
            }
            foreach ($arrSelectedMainCats as $key => $value) {
                foreach (array_keys(ShopCategory::getCategoryTree($value, true))
                        as $catKey) {
                    $selectedCategories .= $catKey.',';
                }
                $selectedCategories .= $value;
            }
            if (empty($selectedCategories)) $selectedCategories = '*';
        }
        if (empty($_POST['pricelistName']))
            $_POST['pricelistName'] = $_ARRAYLANG['TXT_NO_NAME'];
        $query = "
            UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_pricelists
               SET name='".addslashes(strip_tags($_POST['pricelistName']))."',
                   lang_id=".intval($_POST['lang_id']).",
                   border_on=".intval($_POST['borderOn']).",
                   header_on=".intval($_POST['headerOn']).",
                   header_left='".addslashes(trim($_POST['headerTextLeft']))."',
                   header_right='".addslashes(trim($_POST['headerTextRight']))."',
                   footer_on=".intval($_POST['footerOn']).",
                   footer_left='".addslashes(trim($_POST['footerTextLeft']))."',
                   footer_right='".addslashes(trim($_POST['footerTextRight']))."',
                   categories='".addslashes($selectedCategories)."'
             WHERE id=$pricelistID";
        $objDatabase->Execute($query);
        self::addMessage($_ARRAYLANG['TXT_PRODUCT_LIST_UPDATED_SUCCESSFUL']);
    }


    /**
     * Delete a pricelist
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @global  array   $_ARRAYLANG
     */
    function shopPricelistDelete($pricelistID='')
    {
        global $objDatabase, $_ARRAYLANG;

        $arrPricelistId = array();
        if (empty($pricelistID)) {
            if (isset($_GET['id']) && !empty($_GET['id'])) {
                array_push($arrPricelistId, $_GET['id']);
            } elseif (!empty($_POST['selectedPricelistId'])) {
                $arrPricelistId = $_POST['selectedPricelistId'];
            }
        } else {
            array_push($arrPricelistId, $pricelistID);
        }
        if (count($arrPricelistId)) {
            foreach ($arrPricelistId as $plId) {
                $query = "
                    DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_pricelists
                     WHERE id=".intval($plId);
                if (!$objDatabase->Execute($query)) {
                    $this->errorHandling();
                    return false;
                }
                self::addMessage($_ARRAYLANG['TXT_PRICELIST_MESSAGE_DELETED']);
            }
        }
        return true;
    }


    /**
     * Send an e-mail to the Customer with the confirmation that the Order
     * with the given Order ID has been processed
     * @param   integer   $order_id     The order ID
     * @return  boolean                 True on success, false otherwise
     */
    static function sendProcessedMail($order_id)
    {
        global $objDatabase, $_ARRAYLANG;

        $arrSubstitution = self::getOrderSubstitutionArray($order_id);
        $lang_id = $arrSubstitution['LANG_ID'];
        // Select template for: "Your order has been processed"
        $arrMailtemplate = array(
            'key' => 2,
            'lang_id' => $lang_id,
            'to' =>
                $arrSubstitution['CUSTOMER_EMAIL'],
                //.','.SettingDb::getValue('email_confirmation'),
            'substitution' => &$arrSubstitution,
        );
        return MailTemplate::send($arrMailtemplate);
    }


    /**
     * Adds the string $strErrorMessage to the error messages.
     *
     * If necessary, inserts a line break tag (<br />) between
     * error messages.
     * @param   string  $strErrorMessage    The error message to add
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @static
     */
    static function addError($strErrorMessage)
    {
        self::$strErrMessage .=
            (self::$strErrMessage != '' && $strErrorMessage != ''
                ? '<br />' : ''
            ).$strErrorMessage;
    }


    /**
     * Adds the string $strOkMessage to the success messages.
     *
     * If necessary, inserts a line break tag (<br />) between
     * messages.
     * @param   string  $strOkMessage       The message to add
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @static
     */
    static function addMessage($strOkMessage)
    {
        self::$strOkMessage .=
            (self::$strOkMessage != '' && $strOkMessage != ''
                ? '<br />' : ''
            ).$strOkMessage;
    }


    /**
     * Show the count discount editing page
     * @return    boolean             True on success, false otherwise
     * @author    Reto Kohli <reto.kohli@comvation.com>
     */
    function showDiscountGroupsCount()
    {
        global $_ARRAYLANG;

        if (isset($_POST['discountStore'])) {
            $this->shopStoreDiscountCount();
        }
        if (isset($_GET['deleteDiscount'])) {
            $this->shopDeleteDiscountCount();
        }

        self::$objTemplate->addBlockfile('SHOP_PRODUCTS_FILE', 'shop_products_block', 'module_shop_discount_groups_count.html');

        // Discounts overview
        $arrDiscounts = Discount::getDiscountCountArray();
        $i = 0;
        foreach ($arrDiscounts as $id => $arrDiscount) {
            $name = $arrDiscount['name'];
            $unit = $arrDiscount['unit'];
            self::$objTemplate->setVariable(array(
                'SHOP_DISCOUNT_ID' => $id,
                'SHOP_DISCOUNT_GROUP_NAME' => $name,
                'SHOP_DISCOUNT_GROUP_UNIT' => $unit,
                'SHOP_DISCOUNT_ROW_STYLE' => 'row'.(++$i % 2 + 1),
            ));
            self::$objTemplate->parse('shopDiscount');
        }

        // Add/edit Discount
        $id = 0;
        $arrDiscountRates = array();
        if (!empty($_GET['editDiscount'])) {
            $id = intval($_GET['id']);
            $arrDiscountRates = Discount::getDiscountCountRateArray($id);
            self::$objTemplate->setGlobalVariable(array(
                'SHOP_DISCOUNT_EDIT_CLASS' => 'active',
                'SHOP_DISCOUNT_EDIT_DISPLAY' => 'block',
                'SHOP_DISCOUNT_LIST_CLASS' => '',
                'SHOP_DISCOUNT_LIST_DISPLAY' => 'none',
                'TXT_ADD_OR_EDIT' => $_ARRAYLANG['TXT_EDIT'],
            ));
        } else {
            self::$objTemplate->setGlobalVariable(array(
                'SHOP_DISCOUNT_EDIT_CLASS' => '',
                'SHOP_DISCOUNT_EDIT_DISPLAY' => 'none',
                'SHOP_DISCOUNT_LIST_CLASS' => 'active',
                'SHOP_DISCOUNT_LIST_DISPLAY' => 'block',
                'TXT_ADD_OR_EDIT' => $_ARRAYLANG['TXT_ADD'],
            ));
        }
        self::$objTemplate->setCurrentBlock('shopDiscountName');
        self::$objTemplate->setVariable(array(
            'SHOP_DISCOUNT_ID_EDIT' => $id,
            'SHOP_DISCOUNT_ROW_STYLE' => 'row'.(++$i % 2 + 1),
        ));
        if (isset($arrDiscounts[$id])) {
            $arrDiscount = $arrDiscounts[$id];
            $name = $arrDiscount['name'];
            $unit = $arrDiscount['unit'];
            self::$objTemplate->setVariable(array(
                'SHOP_DISCOUNT_GROUP_NAME' => $name,
                'SHOP_DISCOUNT_GROUP_UNIT' => $unit,
            ));
        }
        self::$objTemplate->parse('shopDiscountName');
        self::$objTemplate->setCurrentBlock('shopDiscountRate');
        foreach ($arrDiscountRates as $count => $rate) {
            self::$objTemplate->setVariable(array(
                'SHOP_DISCOUNT_COUNT' => $count,
                'SHOP_DISCOUNT_RATE' => $rate,
                'SHOP_DISCOUNT_RATE_INDEX' => $i,
                'SHOP_DISCOUNT_ROW_STYLE' => 'row'.(++$i % 2 + 1),
            ));
            self::$objTemplate->parse('shopDiscountRate');
        }
        // Add a couple of empty rows for adding new counts and rates
        for ($j = 0; $j < 5; ++$j) {
            self::$objTemplate->setVariable(array(
                'SHOP_DISCOUNT_COUNT' => '',
                'SHOP_DISCOUNT_RATE' => '',
                'SHOP_DISCOUNT_RATE_INDEX' => $i,
                'SHOP_DISCOUNT_ROW_STYLE' => 'row'.(++$i % 2 + 1),
            ));
            self::$objTemplate->parse('shopDiscountRate');
        }
        return true;
    }


    /**
     * Store the count discounts after editing
     * @return    boolean             True on success, false otherwise
     * @author    Reto Kohli <reto.kohli@comvation.com>
     */
    function shopStoreDiscountCount()
    {
        if (!isset($_POST['discountId'])) return true;
        $discountId = $_POST['discountId'];
        $discountGroupName = $_POST['discountGroupName'];
        $discountGroupUnit = $_POST['discountGroupUnit'];
        $arrDiscountCount = $_POST['discountCount'];
        $arrDiscountRate = $_POST['discountRate'];
        return Discount::storeDiscountCount(
            $discountId, $discountGroupName, $discountGroupUnit,
            $arrDiscountCount, $arrDiscountRate
        );
    }


    /**
     * Delete the count discount selected by its ID from the GET request
     * @return    boolean             True on success, false otherwise
     * @author    Reto Kohli <reto.kohli@comvation.com>
     */
    function shopDeleteDiscountCount()
    {
        if (!isset($_GET['id'])) return true;
        $discountId = $_GET['id'];
        return Discount::deleteDiscountCount($discountId);
    }


    /**
     * Show the customer groups for editing
     * @return    boolean             True on success, false otherwise
     * @author    Reto Kohli <reto.kohli@comvation.com>
     */
    function showCustomerGroups()
    {
        global $_ARRAYLANG;

        if (isset($_POST['store'])) {
            Discount::storeCustomerGroup($_POST['groupName'], $_POST['id']);
        }
        if (isset($_GET['delete'])) {
            Discount::deleteCustomerGroup($_GET['id']);
        }

        self::$objTemplate->loadTemplateFile('module_shop_discount_groups_customer.html');

        // Group overview
        $arrGroups = Discount::getCustomerGroupArray();
        self::$objTemplate->setCurrentBlock('shopGroup');
        $i = 0;
        foreach ($arrGroups as $id => $name) {
            self::$objTemplate->setVariable(array(
                'SHOP_GROUP_ID' => $id,
                'SHOP_GROUP_NAME' => $name,
                'SHOP_ROW_STYLE' => 'row'.(++$i % 2 + 1),
            ));
            self::$objTemplate->parse('shopGroup');
        }

        // Add/edit Group
        $id = 0;
        if (!empty($_GET['edit'])) {
            $id = intval($_GET['id']);
            self::$objTemplate->setGlobalVariable(array(
                'SHOP_GROUP_EDIT_CLASS' => 'active',
                'SHOP_GROUP_EDIT_DISPLAY' => 'block',
                'SHOP_GROUP_LIST_CLASS' => '',
                'SHOP_GROUP_LIST_DISPLAY' => 'none',
                'TXT_ADD_OR_EDIT' => $_ARRAYLANG['TXT_EDIT'],
            ));
        } else {
            self::$objTemplate->setGlobalVariable(array(
                'SHOP_GROUP_EDIT_CLASS' => '',
                'SHOP_GROUP_EDIT_DISPLAY' => 'none',
                'SHOP_GROUP_LIST_CLASS' => 'active',
                'SHOP_GROUP_LIST_DISPLAY' => 'block',
                'TXT_ADD_OR_EDIT' => $_ARRAYLANG['TXT_ADD'],
            ));
        }
        self::$objTemplate->setCurrentBlock('shopGroupName');
        self::$objTemplate->setVariable(array(
            'SHOP_GROUP_ID_EDIT' => $id,
            'SHOP_ROW_STYLE' => 'row'.(++$i % 2 + 1),
        ));
        if (isset($arrGroups[$id])) {
            self::$objTemplate->setVariable(
                'SHOP_GROUP_NAME', $arrGroups[$id]
            );
        }
        self::$objTemplate->parse('shopGroupName');
        return true;
    }


    /**
     * Show the article groups for editing
     * @return    boolean             True on success, false otherwise
     * @author    Reto Kohli <reto.kohli@comvation.com>
     */
    function showArticleGroups()
    {
        global $_ARRAYLANG;

        if (isset($_POST['store'])) {
            Discount::storeArticleGroup(
                $_POST['groupName'], $_POST['id']
            );
        }
        if (isset($_GET['delete'])) {
            Discount::deleteArticleGroup($_GET['id']);
        }

        self::$objTemplate->addBlockfile('SHOP_PRODUCTS_FILE', 'shop_products_block', 'module_shop_article_groups.html');

        // Group overview
        $arrGroups = Discount::getArticleGroupArray();
        self::$objTemplate->setCurrentBlock('shopGroup');
        $i = 0;
        foreach ($arrGroups as $id => $name) {
            self::$objTemplate->setVariable(array(
                'SHOP_GROUP_ID' => $id,
                'SHOP_GROUP_NAME' => $name,
                'SHOP_ROW_STYLE' => 'row'.(++$i % 2 + 1),
            ));
            self::$objTemplate->parseCurrentBlock();
        }

        // Add/edit Group
        $id = 0;
        if (!empty($_GET['edit'])) {
            $id = intval($_GET['id']);
            self::$objTemplate->setGlobalVariable(array(
                'SHOP_GROUP_EDIT_CLASS' => 'active',
                'SHOP_GROUP_EDIT_DISPLAY' => 'block',
                'SHOP_GROUP_LIST_CLASS' => '',
                'SHOP_GROUP_LIST_DISPLAY' => 'none',
                'TXT_ADD_OR_EDIT' => $_ARRAYLANG['TXT_EDIT'],
            ));
        } else {
            self::$objTemplate->setGlobalVariable(array(
                'SHOP_GROUP_EDIT_CLASS' => '',
                'SHOP_GROUP_EDIT_DISPLAY' => 'none',
                'SHOP_GROUP_LIST_CLASS' => 'active',
                'SHOP_GROUP_LIST_DISPLAY' => 'block',
                'TXT_ADD_OR_EDIT' => $_ARRAYLANG['TXT_ADD'],
            ));
        }
        self::$objTemplate->setCurrentBlock('shopGroupName');
        self::$objTemplate->setVariable(array(
            'SHOP_GROUP_ID_EDIT' => $id,
            'SHOP_ROW_STYLE' => 'row'.(++$i % 2 + 1),
        ));
        if (isset($arrGroups[$id])) {
            self::$objTemplate->setVariable('SHOP_GROUP_NAME', $arrGroups[$id]);
        }
        self::$objTemplate->parseCurrentBlock();
        return true;
    }


    /**
     * Show the customer and article group discounts for editing.
     *
     * Handles storing of the discounts as well.
     * @return    boolean             True on success, false otherwise
     * @author    Reto Kohli <reto.kohli@comvation.com>
     */
    function showDiscountCustomer()
    {
        global $_ARRAYLANG;

        if (!empty($_POST['store'])) {
            $this->shopStoreDiscountCustomer();
        }

        self::$objTemplate->loadTemplateFile("module_shop_discount_customer.html");

        // Discounts overview
        $arrCustomerGroup = Discount::getCustomerGroupArray();
        $arrArticleGroup = Discount::getArticleGroupArray();
        $arrRate = Discount::getDiscountRateCustomerArray();
        $i = 0;
        // Set up the customer groups header
        self::$objTemplate->setVariable(array(
            'SHOP_CUSTOMER_GROUP_COUNT_PLUS_1' => count($arrCustomerGroup) + 1,
            'SHOP_DISCOUNT_ROW_STYLE' => 'row'.(++$i % 2 + 1),
        ));
        foreach ($arrCustomerGroup as $id => $strCustomerGroupName) {
            self::$objTemplate->setVariable(array(
                'SHOP_CUSTOMER_GROUP_ID' => $id,
                'SHOP_CUSTOMER_GROUP_NAME' => $strCustomerGroupName,
            ));
            self::$objTemplate->parse('shopCustomerGroupHeaderColumn');
        }
        foreach ($arrArticleGroup as $groupArticleId => $strArticleGroupName) {
            foreach ($arrCustomerGroup as $groupCustomerId => $strCustomerGroupName) {
                $rate = (isset($arrRate[$groupCustomerId][$groupArticleId])
                    ? $arrRate[$groupCustomerId][$groupArticleId] : 0);
                self::$objTemplate->setVariable(array(
                    'SHOP_CUSTOMER_GROUP_ID' => $groupCustomerId,
                    'SHOP_DISCOUNT_RATE' => sprintf('%2.2f', $rate),
                    'SHOP_DISCOUNT_ROW_STYLE' => 'row'.(++$i % 2 + 1),
                ));
                self::$objTemplate->parse('shopDiscountColumn');
            }
            self::$objTemplate->setVariable(array(
                'SHOP_ARTICLE_GROUP_ID' => $groupArticleId,
                'SHOP_ARTICLE_GROUP_NAME' => $strArticleGroupName,
                'SHOP_DISCOUNT_ROW_STYLE' => 'row'.(++$i % 2 + 1),
            ));
            self::$objTemplate->parseCurrentBlock('shopArticleGroupRow');
        }
        return true;
    }


    /**
     * Store the customer and article group discount rates after editing
     * @return    boolean             True on success, false otherwise
     * @author    Reto Kohli <reto.kohli@comvation.com>
     */
    function shopStoreDiscountCustomer()
    {
        return Discount::storeDiscountCustomer($_POST['discountRate']);
    }


    /**
     * Deletes the customer group selected by its ID from the GET request
     * @return    boolean             True on success, false otherwise
     * @author    Reto Kohli <reto.kohli@comvation.com>
     */
    function shopDeleteCustomerGroup()
    {
        if (empty($_GET['id'])) return true;
        return Discount::deleteCustomerGroup($_GET['id']);
    }


    /**
     * Deletes the article group selected by its ID from the GET request
     * @return    boolean             True on success, false otherwise
     * @author    Reto Kohli <reto.kohli@comvation.com>
     */
    function shopDeleteArticleGroup()
    {
        if (empty($_GET['id'])) return true;
        return Discount::deleteCustomerGroup($_GET['id']);
    }


    /**
     * Returns the HTML dropdown menu options for the
     * product sorting order menu in the settings
     * @return    string            The HTML code string
     * @author    Reto Kohli <reto.kohli@comvation.com>
     * @todo      Move to Products class
     */
    function getProductSortingMenuoptions()
    {
        global $_ARRAYLANG;

        $activeSorting = SettingDb::getValue('product_sorting');
        $arrAvailableOrder = array(
            1 => 'INDIVIDUAL',
            2 => 'ALPHABETIC',
            3 => 'PRODUCTCODE',
        );
        $strMenuOptions = '';
        foreach ($arrAvailableOrder as $index => $sorting) {
            $strMenuOptions .=
                '<option value="'.$index.'"'.
                ($activeSorting == $index ? HTML_ATTRIBUTE_SELECTED : '').
                '>'.$_ARRAYLANG['TXT_SHOP_PRODUCT_SORTING_'.$sorting].'</option>';
        }
        return $strMenuOptions;
    }

}

?>
