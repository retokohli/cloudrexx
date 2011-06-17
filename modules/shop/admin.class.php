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
// post-2.1
//require_once ASCMS_CORE_PATH.'/Text.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Mail.class.php';
require_once ASCMS_MODULE_PATH.'/shop/shopLib.class.php';
require_once ASCMS_FRAMEWORK_PATH.'/Image.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Currency.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Exchange.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Settings.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Payment.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/PaymentProcessing.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Zones.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Shipment.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Manufacturer.class.php';
require_once ASCMS_MODULE_PATH.'/shop/payments/saferpay/Saferpay.class.php';
require_once ASCMS_MODULE_PATH.'/shop/payments/yellowpay/Yellowpay.class.php';
require_once ASCMS_MODULE_PATH.'/shop/payments/datatrans/Datatrans.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/CSVimport.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Csv_bv.class.php';
/**
 * Weight
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Weight.class.php';
/**
 * VAT database layer
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Vat.class.php';
/**
 * Distribution database layer
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Distribution.class.php';
/**
 * Customer database layer -- to be added for version 2.2.0
 */
//require_once ASCMS_MODULE_PATH.'/shop/lib/Customer.class.php';
/**
 * Customers helper object
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Customers.class.php';
/**
 * ProductAttribute database layer
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/ProductAttribute.class.php';
/**
 * ProductAttributes helper class
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/ProductAttributes.class.php';
/**
 * ShopCategory database layer
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/ShopCategory.class.php';
/**
 * ShopCategories helper class
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/ShopCategories.class.php';
/**
 * Product database layer
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Product.class.php';
/**
 * Products helper class
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Products.class.php';
/**
 * Country class
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Country.class.php';
/**
 * Discount
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Discount.class.php';

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
    private static $strOkMessage  = '';
    private static $pageTitle = '';
    private static $arrCategoryTreeName = array();
    private static $defaultImage = '';
    private static $uploadDir = false;

    /**
     * Settings object
     * @access  public
     * @var     Settings
     */
    private $objSettings;

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

        // sigma template
        self::$objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/shop/template');
        CSRF::add_placeholder(self::$objTemplate);
        self::$objTemplate->setErrorHandling(PEAR_ERROR_DIE);

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

        // Settings object
        $this->objSettings = new Settings();

        // Exchange object
        // OBSOLETE: $this->objExchange = new Exchange();

        $this->objCSVimport = new CSVimport();

        // initialize array of all countries
        //$this->_initCountries();
        $this->_initConfiguration();

    }


    /**
     * Set up the shop admin page
     */
    function getPage()
    {
        global $objTemplate, $_ARRAYLANG;

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
            'ADMIN_CONTENT' => self::$objTemplate->get()
        ));
    }


    /**
     * Manage manufacturers
     */
    function _manufacturer()
    {
        global $_ARRAYLANG, $objDatabase;
        self::$pageTitle = $_ARRAYLANG['TXT_SHOP_MANUFACTURER'];
        self::$objTemplate->loadTemplateFile('module_shop_manufacturer.html', true, true);

        $id = (!empty($_REQUEST['id']) ? intval($_REQUEST['id'])  :  0);
        if (!empty($_REQUEST['exe'])) {
            $name = (!empty($_REQUEST['name']) ? $_REQUEST['name'] : '');
            $url  = (!empty($_REQUEST['url'])  ? $_REQUEST['url']  : '');
            if (preg_match('/^(?:[\w\d\-]+\.)+[\w]+/i', $url)) {
                $url = "http://$url";
            }

            // insert new manufacturer
            if ($_REQUEST['exe'] == 'insert') {
                $query = "
                    INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_manufacturer
                                (name, url) VALUES ('$name', '$url')
                ";
                $objResult = $objDatabase->Execute($query);
                if ($objResult) {
                    self::addMessage($_ARRAYLANG['TXT_SHOP_MANUFACTURER_INSERT_SUCCESS']);
                } else {
                    self::addError($_ARRAYLANG['TXT_SHOP_MANUFACTURER_INSERT_FAILED']);
                }
            }

            // update manufacturer
            if ($_REQUEST['exe'] == 'update' && $id > 0) {
                $query = "
                    UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_manufacturer
                       SET name='$name', url='$url'
                     WHERE id=$id
                ";
                $objResult = $objDatabase->Execute($query);
                if ($objResult) {
                    self::addMessage($_ARRAYLANG['TXT_SHOP_MANUFACTURER_UPDATE_SUCCESS']);
                } else {
                    self::addError($_ARRAYLANG['TXT_SHOP_MANUFACTURER_UPDATE_FAILED']);
                }
            }

            if ($_REQUEST['exe'] == 'delete' && $id > 0) {
                $query = "
                    DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_manufacturer
                     WHERE id=$id
                ";
                $objResult = $objDatabase->Execute($query);
                if ($objResult) {
                    self::addMessage($_ARRAYLANG['TXT_SHOP_MANUFACTURER_DELETE_SUCCESS']);
                } else {
                    self::addError($_ARRAYLANG['TXT_SHOP_MANUFACTURER_DELETE_FAILED']);
                }
            }

            if ($_REQUEST['exe'] == 'deleteList') {
                foreach ($_POST['selectedManufacturerId'] as $selectedId) {
                    $query = "
                        DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_manufacturer
                         WHERE id=".intval($selectedId);
                    $objResult = $objDatabase->Execute($query);
                    if ($objResult) {
                        self::addMessage($_ARRAYLANG['TXT_SHOP_MANUFACTURER_DELETE_SUCCESS']);
                    } else {
                        self::addError($_ARRAYLANG['TXT_SHOP_MANUFACTURER_DELETE_FAILED']);
                    }
                }
            }
        }

        $i = 1;
        $query = "
            SELECT id, name, url
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_manufacturer
          ORDER BY name
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            $this->errorHandling();
        }

        while (!$objResult->EOF) {
            self::$objTemplate->setVariable(array(
                'VALUE_ID' => $objResult->fields['id'],
                'VALUE_NAME' => $objResult->fields['name'],
                'SHOP_ROWCLASS' => (++$i % 2 ? 'row2' : 'row1'),
            ));
            self::$objTemplate->parse("manufacturerRow");
            $objResult->MoveNext();
        }
        self::$objTemplate->setGlobalVariable(array(
            'TXT_EDIT' => $_ARRAYLANG['TXT_EDIT'],
            'TXT_DELETE' => $_ARRAYLANG['TXT_DELETE'],
        ));

        if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'update') {
            // Update the selected Manufacturer
            $query = '
                SELECT id, name, url
                  FROM '.DBPREFIX."module_shop".MODULE_INDEX."_manufacturer
                 WHERE id=$id
            ";
            $objResult = $objDatabase->Execute($query);
            self::$objTemplate->setVariable(array(
                'TXT_SHOP_INSERT_NEW_MANUFACTURER' => $_ARRAYLANG['TXT_SHOP_UPDATE_MANUFACTURER'],
                'VALUE_MANUFACTURER_NAME' => $objResult->fields['name'],
                'VALUE_MANUFACTURER_URL' => $objResult->fields['url'],
                'EXE_MODE' => 'update',
                'VALUE_ID' => $id,
            ));
        } else {
            // Insert a new Manufacturer
            self::$objTemplate->setVariable(array(
                'TXT_SHOP_INSERT_NEW_MANUFACTURER' => $_ARRAYLANG['TXT_SHOP_INSERT_NEW_MANUFACTURER'],
                'VALUE_MANUFACTURER_NAME' => '',
                'VALUE_MANUFACTURER_URL' => '',
                'EXE_MODE' => 'insert',
            ));
        }

        self::$objTemplate->setVariable(array(
            'TXT_NAME' => $_ARRAYLANG['TXT_NAME'],
            'TXT_URL' => $_ARRAYLANG['TXT_MANUFACTURER_URL'],
            'TXT_SHOP_INSERT_NEW_MANUFACTURER_ERROR' => $_ARRAYLANG['TXT_SHOP_INSERT_NEW_MANUFACTURER_ERROR'],
            'TXT_STORE' => $_ARRAYLANG['TXT_STORE'],
            'TXT_SHOP_MANUFACTURER' => $_ARRAYLANG['TXT_SHOP_MANUFACTURER'],
            'TXT_ID' => $_ARRAYLANG['TXT_ID'],
            'TXT_NAME' => $_ARRAYLANG['TXT_NAME'],
            'TXT_ACTION' => $_ARRAYLANG['TXT_ACTION'],
            'TXT_MARKED' => $_ARRAYLANG['TXT_MARKED'],
            'TXT_SELECT_ALL' => $_ARRAYLANG['TXT_SELECT_ALL'],
            'TXT_REMOVE_SELECTION' => $_ARRAYLANG['TXT_REMOVE_SELECTION'],
            'TXT_SELECT_ACTION' => $_ARRAYLANG['TXT_SELECT_ACTION'],
            'TXT_DELETE_MARKED' => $_ARRAYLANG['TXT_DELETE_MARKED'],
            'TXT_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
            'TXT_SHOP_CONFIRM_DELETE_MANUFACTURER' => $_ARRAYLANG['TXT_SHOP_CONFIRM_DELETE_MANUFACTURER'],
            'TXT_MAKE_SELECTION' => $_ARRAYLANG['TXT_MAKE_SELECTION'],
        ));

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
                $DBname = $this->objCSVimport->DBfieldsName($arrProductFieldName[$x]);
                $arrProductDatabaseFieldName[$DBname] =
                    (isset($arrProductDatabaseFieldName[$DBname])
                        ? $arrProductDatabaseFieldName[$DBname].';'
                        : '').
                    $x;
            }

            $importedLines  = 0;
            $errorLines     = 0;
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
                        $Prod2line = split(';', $strFieldIndex);
                        $SpaltenValuesTmp = '';
                        for ($z = 0; $z < count($Prod2line); ++$z) {
                            $SpaltenValuesTmp .=
                                $arrFileContent[$x][$arrDatabaseFieldIndex[$Prod2line[$z]]].
                                '<br />';
                        }
                        $strColumnValues .=
                            ($strColumnValues ? ',' : '').
                            '"'.addslashes($SpaltenValuesTmp).'"';
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
                    if (empty($catName) && !empty($catId)) {
                        break;
                    }
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
                    REPLACE INTO ".DBPREFIX."module_shop".MODULE_INDEX."_products
                    ($strColumnNames, catid) VALUES ($strColumnValues, $catId)";
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
            $JSSelectLayer = 'selectTab("import2");';
        } else {
            $JSSelectLayer = 'selectTab("import1");';
        }

        $Noimg = '';
        $ImportButtonStyle = '';
        $arrTemplateArray = $this->objCSVimport->getTemplateArray();
        if (isset($_REQUEST['mode']) && $_REQUEST['mode'] != 'ImportImg') {
            if (count($arrTemplateArray) == 0) {
                $Noimg = $_ARRAYLANG['TXT_SHOP_IMPORT_NO_TEMPLATES_AVAILABLE'];
                $ImportButtonStyle = 'style="display: none;"';
            } else {
                $Noimg = "";
                $ImportButtonStyle = '';
            }
        } else {
            if (!isset($_REQUEST['SelectFields'])) {
                $JSnofiles     = "selectTab('import1');";
            } else {
                if ($_FILES['CSVfile']['name'] == '') {
                    $JSnofiles  = "selectTab('import4');";
                } else {
                    $JSnofiles  = "selectTab('import2');";
                    $FileFields = $this->objCSVimport->getFilefieldMenuOptions();
                    $FileFields = '
                         <select name="FileFields" id="file_field" style="width: 200px;" size="10">
                             '.$FileFields.'
                         </select>
                     ';
                    $DBlist = $this->objCSVimport->getAvailableNamesMenuOptions();
                    $DBlist = '
                         <select name="DbFields" id="given_field" style="width: 200px;" size="10">
                             '.$DBlist.'
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
                        'status', 'b2b', 'b2c', 'startdate', 'enddate',
                        'manufacturer', 'manufacturer_url', 'external_link',
                        'sort_order', 'vat_id', 'weight',
                        'flags', 'group_id', 'article_id', 'keywords'
                    );
                    $query = "
                        SELECT id, product_id, picture, title, catid, handler,
                               normalprice, resellerprice, shortdesc, description,
                               stock, stock_visibility, discountprice, is_special_offer,
                               status, b2b, b2c, startdate, enddate,
                               manufacturer, manufacturer_url, external_link,
                               sort_order, vat_id, weight,
                               flags, group_id, article_id, keywords
                          FROM ".DBPREFIX."module_shop_products
                         ORDER BY id ASC
                    ";
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
                        'status', 'b2b', 'b2c',
                        'startdate', 'enddate',
                        'manufacturer_name', 'manufacturer_website',
                        'manufacturer_url', 'external_link',
                        'sort_order',
                        'vat_percent', 'weight',
                        'discount_group', 'article_group', 'keywords'
                    );
                    // c1.catid *MUST NOT* be NULL
                    // c2.catid *MAY* be NULL (if c1.catid is root)
                    // vat_id *MAY* be NULL
                    $query = "
                        SELECT p.id, p.product_id, p.picture, p.title,
                               p.catid, c1.catname as category, c2.catname as parentcategory, p.handler,
                               p.normalprice, p.resellerprice, p.discountprice, p.is_special_offer,
                               p.shortdesc, p.description, p.stock, p.stock_visibility,
                               p.status, p.b2b, p.b2c, p.startdate, p.enddate,
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
                         ORDER BY catid ASC, product_id ASC
                    ";
                break;
                // customer - plain fields:
                case 'tcustomer':
                    $content_location = "KundenTabelle.csv";
                    $fieldNames = array(
                        'customerid', 'username', 'password', 'prefix', 'company', 'firstname', 'lastname',
                        'address', 'city', 'zip', 'country_id', 'phone', 'fax', 'email',
                        'ccnumber', 'ccdate', 'ccname', 'cvc_code', 'company_note',
                        'is_reseller', 'register_date', 'customer_status', 'group_id',
                    );
                    $query = "
                        SELECT customerid, username, password, prefix, company, firstname, lastname,
                               address, city, zip, country_id, phone, fax, email,
                               ccnumber, ccdate, ccname, cvc_code, company_note,
                               is_reseller, register_date, customer_status,
                               group_id
                          FROM ".DBPREFIX."module_shop_customers
                         ORDER BY lastname ASC, firstname ASC
                    ";
                break;
                // customer - custom:
                case 'rcustomer':
                    $content_location = "KundenRelationen.csv";
                    $fieldNames = array(
                        'customerid', 'username', 'firstname', 'lastname', 'prefix', 'company',
                        'address', 'zip', 'city', 'countries_name',
                        'phone', 'fax', 'email', 'is_reseller', 'register_date', 'group_name',
                    );
                    $query = "
                        SELECT c.customerid, c.username, c.firstname, c.lastname, c.prefix, c.company,
                               c.address, c.zip, c.city, n.countries_name,
                               c.phone, c.fax, c.email, c.is_reseller, c.register_date,
                               d.name AS group_name
                          FROM ".DBPREFIX."module_shop_customers c
                         INNER JOIN ".DBPREFIX."module_shop_countries n ON c.country_id=n.countries_id
                          LEFT JOIN ".DBPREFIX."module_shop_customer_group d ON c.group_id=d.id
                         ORDER BY c.lastname ASC, c.firstname ASC
                    ";
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
                         ORDER BY orderid ASC
                    ";
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
                'TXT_EXPORT' => $_ARRAYLANG['TXT_EXPORT'],
                'CLASS_NAME' => ($i % 2 ? 'row1' : 'row2'),
            ));
            self::$objTemplate->parse('groupRow');
            $tipText .= 'Text['.$i.']=["","'.$_ARRAYLANG['TXT_SHOP_EXPORT_GROUP_'.strtoupper($arrGroups[$i]).'_TIP'].'"];';
        }

        $ImageChoice = $this->objCSVimport->GetImageChoice($Noimg);
        $arrTemplateArray = $this->objCSVimport->getTemplateArray();
        self::$objTemplate->setCurrentBlock('imgRow');
        for ($x = 0; $x < count($arrTemplateArray); ++$x) {
            self::$objTemplate->setVariable(array(
                'IMG_NAME' => $arrTemplateArray[$x]['name'],
                'IMG_ID' => $arrTemplateArray[$x]['id'],
                'TXT_DELETE' => $_ARRAYLANG['TXT_SHOP_IMPORT_DELETE'],
                'CLASS_NAME' => ($x % 2 ? 'row2' : 'row1'),
                // cms offset fix for admin images/icons:
                'SHOP_CMS_OFFSET' => ASCMS_PATH_OFFSET,
            ));
            self::$objTemplate->parse('imgRow');
        }

        self::$objTemplate->setVariable(array(
            'SELECT_LAYER_ONLOAD' => $JSSelectLayer,
            'NO_FILES' => (isset($JSnofiles)  ? $JSnofiles  : ''),
            'FILE_FIELDS_LIST' => (isset($FileFields) ? $FileFields : ''),
            'DB_FIELDS_LIST' => (isset($DBlist)     ? $DBlist     : ''),
            'IMAGE_CHOICE' => $ImageChoice,
            'IMPORT_BUTTON_STYLE' => $ImportButtonStyle,
            'TXT_FUNCTIONS' => $_ARRAYLANG['TXT_FUNCTIONS']
        ));

        self::$objTemplate->setVariable(array(
            'TXT_SHOP_IMPORT_TITLE' => $_ARRAYLANG['TXT_SHOP_IMPORT_TITLE'],
            'TXT_SHOP_IMPORT_SELECT_TEMPLATE' => $_ARRAYLANG['TXT_SHOP_IMPORT_SELECT_TEMPLATE'],
            'TXT_SHOP_IMPORT_IMPORT' => $_ARRAYLANG['TXT_SHOP_IMPORT_IMPORT'],
            'TXT_SHOP_IMPORT_IMPORTTEMPLATE' => $_ARRAYLANG['TXT_SHOP_IMPORT_IMPORTTEMPLATE'],
            'TXT_SHOP_IMPORT_TEXTFILE' => $_ARRAYLANG['TXT_SHOP_IMPORT_TEXTFILE'],
            'TXT_SHOP_IMPORT_DATABASE' => $_ARRAYLANG['TXT_SHOP_IMPORT_DATABASE'],
            'TXT_SHOP_IMPORT_CATEGORIES' => $_ARRAYLANG['TXT_SHOP_IMPORT_CATEGORIES'],
            'TXT_SHOP_IMPORT_ADD_FEW' => $_ARRAYLANG['TXT_SHOP_IMPORT_ADD_FEW'],
            'TXT_SHOP_IMPORT_ADD_CATEGORY' => $_ARRAYLANG['TXT_SHOP_IMPORT_ADD_CATEGORY'],
            'TXT_SHOP_IMPORT_REMOVE_CATEGORY' => $_ARRAYLANG['TXT_SHOP_IMPORT_REMOVE_CATEGORY'],
            'TXT_SHOP_IMPORT_REMOVE_FEW' => $_ARRAYLANG['TXT_SHOP_IMPORT_REMOVE_FEW'],
            'TXT_SHOP_IMPORT_SAVE' => $_ARRAYLANG['TXT_SHOP_IMPORT_SAVE'],
            'TXT_SHOP_IMPORT_SAVED_TEMPLATES' => $_ARRAYLANG['TXT_SHOP_IMPORT_SAVED_TEMPLATES'],
            'TXT_SHOP_IMPORT_MAKE_NEW_TEMPLATE' => $_ARRAYLANG['TXT_SHOP_IMPORT_MAKE_NEW_TEMPLATE'],
            'TXT_SHOP_IMPORT_UPLOAD' => $_ARRAYLANG['TXT_SHOP_IMPORT_UPLOAD'],
            'TXT_SHOP_IMPORT_NO_TEMPLATES_AVAILABLE' => $_ARRAYLANG['TXT_SHOP_IMPORT_NO_TEMPLATES_AVAILABLE'],
            'TXT_SHOP_IMPORT_MANAGE_TEMPLATES' => $_ARRAYLANG['TXT_SHOP_IMPORT_MANAGE_TEMPLATES'],
            'TXT_SHOP_IMPORT_ENTER_TEMPLATE_NAME' => $_ARRAYLANG['TXT_SHOP_IMPORT_ENTER_TEMPLATE_NAME'],
            'TXT_SHOP_IMPORT_WARNING' => $_ARRAYLANG['TXT_SHOP_IMPORT_WARNING'],
            'TXT_SHOP_IMPORT_SELECT_FILE_PLEASE' => $_ARRAYLANG['TXT_SHOP_IMPORT_SELECT_FILE_PLEASE'],
            'TXT_SHOP_IMPORT_TEMPLATE_REALLY_DELETE' => $_ARRAYLANG['TXT_SHOP_IMPORT_TEMPLATE_REALLY_DELETE'],
            'TXT_SHOP_IMPORT_TEMPLATENAME' => $_ARRAYLANG['TXT_SHOP_IMPORT_TEMPLATENAME'],
            'TXT_SHOP_IMPORT_FILE' => $_ARRAYLANG['TXT_SHOP_IMPORT_FILE'],
            'TXT_SHOP_IMPORT_IMPORT_CATEGORIES' => $_ARRAYLANG['TXT_SHOP_IMPORT_IMPORT_CATEGORIES'],
            'TXT_SHOP_CLEAR_DATABASE_BEFORE_IMPORTING_CATEGORIES' => $_ARRAYLANG['TXT_SHOP_CLEAR_DATABASE_BEFORE_IMPORTING_CATEGORIES'],
            'TXT_SHOP_IMPORT_CATEGORIES_TIPS' => $_ARRAYLANG['TXT_SHOP_IMPORT_CATEGORIES_TIPS'],
            'TXT_SHOP_IMPORT_PRODUCTS' => $_ARRAYLANG['TXT_SHOP_IMPORT_PRODUCTS'],
            // export added
            'TXT_SHOP_EXPORT' => $_ARRAYLANG['TXT_SHOP_EXPORT'],
            'TXT_SHOP_EXPORT_DATA' => $_ARRAYLANG['TXT_SHOP_EXPORT_DATA'],
            'TXT_SHOP_EXPORT_SELECTION' => $_ARRAYLANG['TXT_SHOP_EXPORT_SELECTION'],
            'TXT_SHOP_EXPORT_WARNING' => $_ARRAYLANG['TXT_SHOP_EXPORT_WARNING'],
            // instructions added
            'SHOP_EXPORT_TIPS' => $tipText,
            'TXT_SHOP_IMPORT_CREATE_TEMPLATE_TIPS' => $_ARRAYLANG['TXT_SHOP_IMPORT_CREATE_TEMPLATE_TIPS'],
            'TXT_SHOP_IMPORT_ASSIGNMENT_TIPS' => $_ARRAYLANG['TXT_SHOP_IMPORT_ASSIGNMENT_TIPS'],
            'TXT_SHOP_IMPORT_CATEGORY_TIPS' => $_ARRAYLANG['TXT_SHOP_IMPORT_CATEGORY_TIPS'],
            'TXT_SHOP_IMPORT_CATEGORY_REMOVE_TIPS' => $_ARRAYLANG['TXT_SHOP_IMPORT_CATEGORY_REMOVE_TIPS'],
            'TXT_SHOP_IMPORT_ASSIGNMENT_REMOVE_TIPS' => $_ARRAYLANG['TXT_SHOP_IMPORT_ASSIGNMENT_REMOVE_TIPS'],
            'TXT_SHOP_IMPORT_TEMPLATE_SAVE_TIPS' => $_ARRAYLANG['TXT_SHOP_IMPORT_TEMPLATE_SAVE_TIPS'],
            'TXT_SHOP_IMPORT_CHOOSE_TEMPLATE_TIPS' => $_ARRAYLANG['TXT_SHOP_IMPORT_CHOOSE_TEMPLATE_TIPS'],
            'TXT_SHOP_EXPORT_TIPS' => $_ARRAYLANG['TXT_SHOP_EXPORT_TIPS'],
            'TXT_SHOP_TIP' => $_ARRAYLANG['TXT_SHOP_TIP'],
            'TXT_CLEAR_DATABASE_BEFORE_IMPORTING' => $_ARRAYLANG['TXT_CLEAR_DATABASE_BEFORE_IMPORTING'],
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
        global $objDatabase;
        require_once ASCMS_FRAMEWORK_PATH."/Image.class.php";

        if (!is_array($arrId)) return false;
        $objImageManager = new ImageManager();
        foreach ($arrId as $Id) {
            $shopPicture = '';
            $query = "
                SELECT picture
                FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
                WHERE id=$Id
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
                $this->arrConfig['shop_thumbnail_max_width']['value'],
                $this->arrConfig['shop_thumbnail_max_height']['value'],
                $this->arrConfig['shop_thumbnail_quality']['value']
            )) {
                $width  = $objImageManager->orgImageWidth;
                $height = $objImageManager->orgImageHeight;
                $shopPicture =
                    base64_encode($imageName).
                    '?'.base64_encode($width).
                    '?'.base64_encode($height).'::';
                $query = "
                    UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_products
                    SET picture='$shopPicture'
                    WHERE id=$Id
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
    function _showProductAttributes()
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
        global $_ARRAYLANG;

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
        // Clear the Product Attribute data present in ProductAttributes.
        // This may have been changed above and would thus be out of date.
        ProductAttributes::reset();

        // set language variables
        self::$objTemplate->setVariable(array(
            'TXT_DEFINE_NAME_FOR_OPTION' => $_ARRAYLANG['TXT_DEFINE_NAME_FOR_OPTION'],
            'TXT_DEFINE_VALUE_FOR_OPTION' => $_ARRAYLANG['TXT_DEFINE_VALUE_FOR_OPTION'],
            'TXT_CONFIRM_DELETE_OPTION' => $_ARRAYLANG['TXT_CONFIRM_DELETE_OPTION'],
            'TXT_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
            'TXT_MAKE_SELECTION' => $_ARRAYLANG['TXT_MAKE_SELECTION'],
            'TXT_SELECT_ACTION' => $_ARRAYLANG['TXT_SELECT_ACTION'],
            'TXT_DELETE' => $_ARRAYLANG['TXT_DELETE'],
            'TXT_SAVE_CHANGES' => $_ARRAYLANG['TXT_SAVE_CHANGES'],
            'TXT_CHECKBOXES_OPTION' => $_ARRAYLANG['TXT_CHECKBOXES_OPTION'],
            'TXT_RADIOBUTTON_OPTION' => $_ARRAYLANG['TXT_RADIOBUTTON_OPTION'],
            'TXT_MENU_OPTION' => $_ARRAYLANG['TXT_MENU_OPTION'],
            'TXT_SHOP_MENU_OPTION_DUTY' => $_ARRAYLANG['TXT_SHOP_MENU_OPTION_DUTY'],
            'TXT_SHOP_PRODUCTATTRIBUTE_CANNOT_ADD_VALUE_FOR_TYPE' => $_ARRAYLANG['TXT_SHOP_PRODUCTATTRIBUTE_CANNOT_ADD_VALUE_FOR_TYPE'],
        ));
        self::$objTemplate->setGlobalVariable(array(
            'TXT_OPTIONS' => $_ARRAYLANG['TXT_OPTIONS'],
            'TXT_ADD' => $_ARRAYLANG['TXT_ADD'],
            'TXT_NAME' => $_ARRAYLANG['TXT_NAME'],
            'TXT_VALUES' => $_ARRAYLANG['TXT_VALUES'],
            'TXT_FUNCTIONS' => $_ARRAYLANG['TXT_FUNCTIONS'],
            'TXT_VALUE' => $_ARRAYLANG['TXT_VALUE'],
            'TXT_PRICE' => $_ARRAYLANG['TXT_PRICE'],
            'TXT_ADD_NEW_VALUE' => $_ARRAYLANG['TXT_ADD_NEW_VALUE'],
            'TXT_EDIT_OPTION' => $_ARRAYLANG['TXT_EDIT_OPTION'],
            'TXT_DELETE_OPTION' => $_ARRAYLANG['TXT_DELETE_OPTION'],
            'TXT_MARKED' => $_ARRAYLANG['TXT_MARKED'],
            'TXT_SELECT_ALL' => $_ARRAYLANG['TXT_SELECT_ALL'],
            'TXT_REMOVE_SELECTION' => $_ARRAYLANG['TXT_REMOVE_SELECTION'],
            'TXT_REMOVE_SELECTED_VALUE' => $_ARRAYLANG['TXT_REMOVE_SELECTED_VALUE'],
            'TXT_DISPLAY_AS' => $_ARRAYLANG['TXT_DISPLAY_AS'],
        ));

        $rowClass = 1;
        $max_value_id = -1;
        // Init all values, stored statically in the class
        //ProductAttributes::initValueArray();
        foreach (ProductAttributes::getNameArray() as $name_id => $arrAttributeName) {
            $arrAttributeValue = ProductAttributes::getValueArrayByNameId($name_id);
            self::$objTemplate->setCurrentBlock('attributeList');
            self::$objTemplate->setVariable(array(
                'SHOP_PRODUCT_ATTRIBUTE_ROW_CLASS' => (++$rowClass % 2 ? 'row2' : 'row1'),
                'SHOP_PRODUCT_ATTRIBUTE_ID' => $name_id,
                'SHOP_PRODUCT_ATTRIBUTE_NAME' => $arrAttributeName['name'],
                'SHOP_PRODUCT_ATTRIBUTE_VALUE_MENU' => ProductAttributes::getAttributeValueMenu(
                    $name_id,
                    'attributeValueList',
                    '',
                    'setSelectedValue('.$name_id.')',
                    'width: 200px;'
                  ),
                'SHOP_PRODUCT_ATTRIBUTE_VALUE_INPUTBOXES' => ProductAttributes::getAttributeInputBoxes(
                    $name_id,
                    'attributeValue',
                    'value',
                    255,
                    'width: 170px;'
                ),
                'SHOP_PRODUCT_ATTRIBUTE_PRICE_INPUTBOXES' => ProductAttributes::getAttributeInputBoxes(
                    $name_id,
                    'attributePrice',
                    'price',
                    9,
                    'width: 170px; text-align: right;'
                ),
                'SHOP_PRODUCT_ATTRIBUTE_DISPLAY_TYPE' => ProductAttributes::getAttributeDisplayTypeMenu(
                    $name_id,
                    $arrAttributeName['type'],
                    'updateAttributeValueList('.$name_id.')'
                ),
            ));
            self::$objTemplate->parseCurrentBlock();
            foreach (array_keys($arrAttributeValue) as $value_id) {
                if ($value_id > $max_value_id) $max_value_id = $value_id;
            }
        }
        // The same for a new ProductAttribute
        self::$objTemplate->setVariable(array(
            'TXT_SHOP_PRODUCT_ATTRIBUTE_TYPE_MENU' => ProductAttributes::getAttributeDisplayTypeMenu(
                    0, 0, 'updateAttributeValueList(0)'
                ),
        ));

        self::$objTemplate->setVariable(array(
            'SHOP_PRODUCT_ATTRIBUTE_JS_VARS' =>
//$this->_getAttributeJSVars().
                ProductAttributes::getAttributeJSVars(), //"\nindex = ".$this->highestIndex.";\n",
            'SHOP_PRODUCT_ATTRIBUTE_CURRENCY' => Currency::getDefaultCurrencySymbol(),
        ));
    }


    /**
     * Show the settings for the download options of the products
     */
    function _showProductDownloadOptions()
    {
        global $_ARRAYLANG;

        self::$pageTitle = $_ARRAYLANG['TXT_PRODUCT_CHARACTERISTICS'];
        self::$objTemplate->addBlockfile('SHOP_PRODUCTS_FILE', 'shop_products_block', 'module_shop_product_download.html');
    }


    /**
     * Get attribute list
     *
     * Generate the standard attribute option/value list or the one of a product
     * @access  private
     * @param   string    $productId    Product Id of which its list will be displayed
     */
    function _getAttributeList($productId=0)
    {
        $i = 0;
        foreach (ProductAttributes::getNameArray() as $name_id => $arrAttributeName) {
            $arrRelation = array();
            // If a Product is selected, check those Product Attribute values
            // associated with it
            if ($productId)
                $arrRelation = ProductAttributes::getRelationArray($productId);
            // All values available for this Product Attribute
            $arrAttributeValues = ProductAttributes::getValueArrayByNameId($name_id);

            $nameSelected = false;
            $order = 0;
            foreach ($arrAttributeValues as $value_id => $arrAttributeValue) {
                if (in_array($value_id, array_keys($arrRelation))) {
                    $valueSelected = true;
                    $nameSelected  = true;
                    $order = $arrRelation[$value_id];
                } else {
                    $valueSelected = false;
                }
                self::$objTemplate->setVariable(array(
                    'SHOP_PRODUCTS_ATTRIBUTE_ID' => $name_id,
                    'SHOP_PRODUCTS_ATTRIBUTE_VALUE_ID' => $value_id,
                    'SHOP_PRODUCTS_ATTRIBUTE_VALUE_TEXT' => $arrAttributeValue['value'].
                        ' ('.$arrAttributeValue['price'].' '.Currency::getDefaultCurrencySymbol().')',
                    'SHOP_PRODUCTS_ATTRIBUTE_VALUE_SELECTED' => ($valueSelected ? ' checked="checked"' : ''),
                ));
                self::$objTemplate->parse('attributeValueList');
            }
            self::$objTemplate->setVariable(array(
                'SHOP_PRODUCTS_ATTRIBUTE_ROW_CLASS' => (++$i % 2 ? 'row1' : 'row2'),
                'SHOP_PRODUCTS_ATTRIBUTE_ID' => $name_id,
                'SHOP_PRODUCTS_ATTRIBUTE_NAME' => $arrAttributeName['name'],
                'SHOP_PRODUCTS_ATTRIBUTE_SELECTED' => ($nameSelected ? ' checked="checked"' : ''),
                'SHOP_PRODUCTS_ATTRIBUTE_DISPLAY_TYPE' => ($nameSelected ? 'block' : 'none'),
                'SHOP_PRODUCTS_ATTRIBUTE_SORTID' => $order,
                    //self::$arrRelation[$product_id][$value_id] = $objResult->fields['sort_id'];
            ));
            self::$objTemplate->parse('attributeList');
        }
    }


    /**
     * Store new attribute option
     *
     * Store a new attribute option
     *
     * @access    private
     * @return    string    $statusMessage    Status message
     */
    function _storeNewAttributeOption()
    {
        global $_ARRAYLANG;

        $arrAttributeList = array();
        $arrAttributeValue = array();
        $arrAttributePrice = array();
        if (empty($_POST['optionName'][0])) {
            return $_ARRAYLANG['TXT_DEFINE_NAME_FOR_OPTION'];
        } elseif (!is_array($_POST['attributeValueList'][0])) {
            return $_ARRAYLANG['TXT_DEFINE_VALUE_FOR_OPTION'];
        }
        $arrAttributeList = $_POST['attributeValueList'];
        $arrAttributeValue =
            (isset($_POST['attributeValue'])
                ? $_POST['attributeValue'] : array()
            );
        $arrAttributePrice = $_POST['attributePrice'];
        $objProductAttribute = new ProductAttribute(
            intval($_POST['attributeDisplayType'][0])
        );
        $objProductAttribute->setName($_POST['optionName'][0]);
        foreach ($arrAttributeList[0] as $id) {
            $objProductAttribute->addValue(
                $arrAttributeValue[$id],
                $arrAttributePrice[$id]
            );
        }
        if (!$objProductAttribute->store())
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

        $arrAttributeName = $_POST['optionName'];
        $arrAttributeType = $_POST['attributeDisplayType'];
        $arrAttributeList = $_POST['attributeValueList'];
        $arrAttributeValue = $_POST['attributeValue'];
        $arrAttributePrice = $_POST['attributePrice'];

        foreach ($arrAttributeList as $name_id => $arrValueIds) {
            $flagChanged = false;
            $objAttribute = ProductAttribute::getByNameId($name_id);
            if (!$objAttribute) {
                self::addError($_ARRAYLANG['TXT_SHOP_ERROR_UPDATING_RECORD']);
                return false;
            }

            $name = $arrAttributeName[$name_id];
            $type = $arrAttributeType[$name_id];
            if (   $name != $objAttribute->getName()
                || $type != $objAttribute->getType()) {
                $objAttribute->setName($name);
                $objAttribute->setType($type);
                $flagChanged = true;
            }

            $arrValueObj = $objAttribute->getValueArray();
            foreach ($arrValueIds as $value_id) {
                // Make sure these values are defined if empty:
                // The option name and price
                if (empty($arrAttributeValue[$value_id]))
                    $arrAttributeValue[$value_id] = '';
                if (empty($arrAttributePrice[$value_id]))
                    $arrAttributePrice[$value_id] = '0.00';
                if (isset($arrValueObj[$value_id])) {
                    if (   $arrAttributeValue[$value_id] != $arrValueObj[$value_id]['value']
                        || $arrAttributePrice[$value_id] != $arrValueObj[$value_id]['price']) {
                        $objAttribute->changeValue($value_id, $arrAttributeValue[$value_id], $arrAttributePrice[$value_id]);
                        $flagChanged = true;
                    }
                } else {
                    $objAttribute->addValue($arrAttributeValue[$value_id], $arrAttributePrice[$value_id]);
                    $flagChanged = true;
                }
            }

            // Delete values that are no longer present in the post
            foreach (array_keys($arrValueObj) as $value_id) {
                if (!in_array($value_id, $arrAttributeList[$name_id])) {
                    $objAttribute->deleteValueById($value_id);
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
        foreach (array_keys(ProductAttributes::getNameArray()) as $name_id) {
            if (!array_key_exists($name_id, $arrAttributeList)) {
                $objAttribute = ProductAttribute::getByNameId($name_id);
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
     * Delete attribute option
     *
     * Delete the selected attribute option(s)
     * @access    private
     * @param    integer    $optionId    Id of the attribute option
     * @return    string    Status message
     */
    function _deleteAttributeOption($optionId)
    {
        global $objDatabase, $_ARRAYLANG;

        if (!is_array($optionId)) {
            $arrOptionIds = array($optionId);
        } else {
            $arrOptionIds = $optionId;
        }

        foreach ($arrOptionIds as $optionId) {
            $query = "
                DELETE FROM ".DBPREFIX."module_shop_products_attributes
                 WHERE attributes_name_id=".intval($optionId);
            if (!$objDatabase->Execute($query)) {
                return $_ARRAYLANG['TXT_SHOP_ERROR_UPDATING_RECORD'];
            }
            $query = "DELETE FROM ".DBPREFIX."module_shop_products_attributes_value WHERE name_id=".intval($optionId);
            if (!$objDatabase->Execute($query)) {
                return $_ARRAYLANG['TXT_SHOP_ERROR_UPDATING_RECORD'];
            }
            $query = "DELETE FROM ".DBPREFIX."module_shop_products_attributes_name WHERE id=".intval($optionId);
            if (!$objDatabase->Execute($query)) {
                return $_ARRAYLANG['TXT_SHOP_ERROR_UPDATING_RECORD'];
            }
        }
        self::addMessage($_ARRAYLANG['TXT_OPTION_SUCCESSFULLY_DELETED']);
        return '';
    }


    /**
     * Generate a javascript variables list of the attributes
     *
     * OBSOLETE.  Use ProductAttribute::getAttributeJSVars() instead.
     * @access    private
     * @return    string    $jsVars    Javascript variables list
     */
    function _getAttributeJSVars()
    {
        $jsVars = '';
        foreach ($this->arrAttributes as $attributeId => $arrValues) {
            reset($arrValues['values']);
            $arrValue = current($arrValues['values']);
            $jsVars .= "attributeValueId[$attributeId] = ".$arrValue['id'].";\n";
        }
        return $jsVars;
    }


    /**
     * Set up the common elements for various settings pages
     *
     * Includes VAT, shipping, countries, zones and more
     * @access private
     */
    function _showSettings()
    {
        global $objDatabase, $_ARRAYLANG;

        // added return value. If empty, no error occurred
        $success = $this->objSettings->storeSettings();
        if ($success) {
            self::addMessage($_ARRAYLANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL']);
        } elseif ($success === false) {
            self::addError($_ARRAYLANG['TXT_DATABASE_QUERY_ERROR']);
        }
        // $success may also be '', in which case no changed setting has
        // been detected.
        // Refresh the Settings, so changes are made visible right away
        $this->_initConfiguration();

        $i = 0;
        self::$pageTitle= $_ARRAYLANG['TXT_SETTINGS'];
        self::$objTemplate->loadTemplateFile('module_shop_settings.html', true, true);

        self::$objTemplate->setGlobalVariable(array(
            'TXT_ADD_ALL' => $_ARRAYLANG['TXT_ADD_ALL'],
            'TXT_ADD_SELECTION' => $_ARRAYLANG['TXT_ADD_SELECTION'],
            'TXT_REMOVE_ALL' => $_ARRAYLANG['TXT_REMOVE_ALL'],
            'TXT_REMOVE_SELECTION' => $_ARRAYLANG['TXT_REMOVE_SELECTION'],
            'TXT_ADD' => $_ARRAYLANG['TXT_ADD'],
            'TXT_STORE' => $_ARRAYLANG['TXT_STORE'],
            'TXT_ACTIVE' => $_ARRAYLANG['TXT_ACTIVE'],
            'TXT_ACTION' => $_ARRAYLANG['TXT_ACTION'],
            'TXT_NAME' => $_ARRAYLANG['TXT_NAME'],
            'TXT_FEE' => $_ARRAYLANG['TXT_FEE'],
            'TXT_FREE_OF_CHARGE' => $_ARRAYLANG['TXT_FREE_OF_CHARGE'],
            'TXT_FREE_OF_CHARGE_TIP' => $_ARRAYLANG['TXT_FREE_OF_CHARGE_TIP'],
            'TXT_ZONE' => $_ARRAYLANG['TXT_ZONE'],
            'TXT_MAIL_TEMPLATES' => $_ARRAYLANG['TXT_MAIL_TEMPLATES'],
            'TXT_CURRENCIES' => $_ARRAYLANG['TXT_CURRENCIES'],
            'TXT_GENERAL_SETTINGS' => $_ARRAYLANG['TXT_GENERAL_SETTINGS'],
            'TXT_GENERAL' => $_ARRAYLANG['TXT_GENERAL'],
            'TXT_CURRENCY_CONVERTER' => $_ARRAYLANG['TXT_CURRENCY_CONVERTER'],
            'TXT_RATE' => $_ARRAYLANG['TXT_RATE'],
            'TXT_SYMBOL' => $_ARRAYLANG['TXT_SYMBOL'],
            'TXT_ID' => $_ARRAYLANG['TXT_ID'],
            'TXT_STANDARD' => $_ARRAYLANG['TXT_STANDARD'],
            'TXT_SHIPPING_METHODS' => $_ARRAYLANG['TXT_SHIPPING_METHODS'],
            'TXT_SHIPPING_METHOD' => $_ARRAYLANG['TXT_SHIPPING_METHOD'],
            'TXT_LANGUAGE' => $_ARRAYLANG['TXT_LANGUAGE'],
            'TXT_HANDLER' => $_ARRAYLANG['TXT_HANDLER'],
            'TXT_PAYMENT_HANDLER' => $_ARRAYLANG['TXT_PAYMENT_HANDLER'],
            'TXT_SEPARATED_WITH_COMMAS' => $_ARRAYLANG['TXT_SEPARATED_WITH_COMMAS'],
            'TXT_CONFIRMATION_EMAILS' => $_ARRAYLANG['TXT_CONFIRMATION_EMAILS'],
            'TXT_CONTACT_COMPANY' => $_ARRAYLANG['TXT_CONTACT_COMPANY'],
            'TXT_CONTACT_ADDRESS' => $_ARRAYLANG['TXT_CONTACT_ADDRESS'],
            'TXT_PHONE_NUMBER' => $_ARRAYLANG['TXT_PHONE_NUMBER'],
            'TXT_FAX_NUMBER' => $_ARRAYLANG['TXT_FAX_NUMBER'],
            'TXT_SHOP_EMAIL' => $_ARRAYLANG['TXT_SHOP_EMAIL'],
            'TXT_STATEMENT' => $_ARRAYLANG['TXT_STATEMENT'],
            'TXT_AUTORIZATION' => $_ARRAYLANG['TXT_AUTORIZATION'],
            'TXT_CODE' => "ISO-CODE",
            'TXT_STATEMENT' => $_ARRAYLANG['TXT_STATEMENT'],
            'TXT_STATEMENT' => $_ARRAYLANG['TXT_STATEMENT'],
            'TXT_COUNTRY' => $_ARRAYLANG['TXT_COUNTRY'],
            'TXT_ZONES' => $_ARRAYLANG['TXT_ZONES'],
            'TXT_SHOP_EDIT' => $_ARRAYLANG['TXT_SHOP_EDIT'],
            'TXT_DELETE' => $_ARRAYLANG['TXT_DELETE'],
            'TXT_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
            'TXT_CONFIRM_DELETE_CURRENCY' => $_ARRAYLANG['TXT_CONFIRM_DELETE_CURRENCY'],
            'TXT_PAYMENT_TYPES' => $_ARRAYLANG['TXT_PAYMENT_TYPES'],
            'TXT_PAYMENT_TYPE' => $_ARRAYLANG['TXT_PAYMENT_TYPE'],
            'TXT_PAYMENT_LSV' => $_ARRAYLANG['TXT_PAYMENT_LSV'],
            'TXT_PAYMENT_LSV_FEE' => $_ARRAYLANG['TXT_PAYMENT_LSV_FEE'],
            'TXT_CONFIRM_DELETE_PAYMENT' => $_ARRAYLANG['TXT_CONFIRM_DELETE_PAYMENT'],
            'TXT_CONFIRM_DELETE_SHIPMENT' => $_ARRAYLANG['TXT_CONFIRM_DELETE_SHIPMENT'],
            'TXT_DELIVERY_COUNTRIES' => $_ARRAYLANG['TXT_DELIVERY_COUNTRIES'],
            'TXT_DELIVERY_COUNTRY' => $_ARRAYLANG['TXT_DELIVERY_COUNTRY'],
            'TXT_SAFERPAY' => $_ARRAYLANG['TXT_SAFERPAY'],
            'TXT_ACCOUNT_ID' => $_ARRAYLANG['TXT_ACCOUNT_ID'],
            'TXT_USE_TEST_ACCOUNT' => $_ARRAYLANG['TXT_USE_TEST_ACCOUNT'],
            'TXT_FINALIZE_PAYMENT' => $_ARRAYLANG['TXT_FINALIZE_PAYMENT'],
            'TXT_INDICATE_PAYMENT_WINDOW_AS' => $_ARRAYLANG['TXT_INDICATE_PAYMENT_WINDOW_AS'],
            'TXT_PAYPAL' => $_ARRAYLANG['TXT_PAYPAL'],
            'TXT_PAYPAL_EMAIL_ACCOUNT' => $_ARRAYLANG['TXT_PAYPAL_EMAIL_ACCOUNT'],
            'TXT_SHOP_PAYPAL_DEFAULT_CURRENCY' => $_ARRAYLANG['TXT_SHOP_PAYPAL_DEFAULT_CURRENCY'],
            'TXT_YELLOWPAY_POSTFINANCE' => $_ARRAYLANG['TXT_YELLOWPAY_POSTFINANCE'],
            'TXT_SHOP_ID' => $_ARRAYLANG['TXT_SHOP_ID'],
//            'TXT_HASH_SEED' => $_ARRAYLANG['TXT_HASH_SEED'],
// Replaced by
            'TXT_SHOP_YELLOWPAY_HASH_SIGNATURE_IN' => $_ARRAYLANG['TXT_SHOP_YELLOWPAY_HASH_SIGNATURE_IN'],
            'TXT_SHOP_YELLOWPAY_HASH_SIGNATURE_OUT' => $_ARRAYLANG['TXT_SHOP_YELLOWPAY_HASH_SIGNATURE_OUT'],
            'TXT_SHOP_YELLOWPAY_PSPID' => $_ARRAYLANG['TXT_SHOP_YELLOWPAY_PSPID'],

            'TXT_IMMEDIATE' => $_ARRAYLANG['TXT_IMMEDIATE'],
            'TXT_DEFERRED' => $_ARRAYLANG['TXT_DEFERRED'],
            'TXT_SHOP_ACCEPTED_PAYMENT_METHODS' => $_ARRAYLANG['TXT_SHOP_ACCEPTED_PAYMENT_METHODS'],
            // General
            'TXT_SHOP_SHOW_PRODUCTS_ON_START_PAGE' => $_ARRAYLANG['TXT_SHOP_SHOW_PRODUCTS_ON_START_PAGE'],
            'TXT_SHOP_PRODUCT_SORTING' => $_ARRAYLANG['TXT_SHOP_PRODUCT_SORTING'],
            // country settings
            'TXT_COUNTRY_LIST' => $_ARRAYLANG['TXT_COUNTRY_LIST'],
            'TXT_DISPLAY_IT_IN_THE_SHOP' => $_ARRAYLANG['TXT_DISPLAY_IT_IN_THE_SHOP'],
            'TXT_DONT_DISPLAY_IT_IN_THE_SHOP' => $_ARRAYLANG['TXT_DONT_DISPLAY_IT_IN_THE_SHOP'],
            'TXT_SELECT_COUNTRIES' => $_ARRAYLANG['TXT_SELECT_COUNTRIES'],
            'TXT_SELECT_SEVERAL_COUNTRIES' => $_ARRAYLANG['TXT_SELECT_SEVERAL_COUNTRIES'],
            // zone settings
            'TXT_CONFIRM_DELETE_ZONE' => $_ARRAYLANG['TXT_CONFIRM_DELETE_ZONE'],
            'TXT_ZONE_NAME' => $_ARRAYLANG['TXT_ZONE_NAME'],
            'TXT_ZONE_LIST' => $_ARRAYLANG['TXT_ZONE_LIST'],
            'TXT_SETTINGS' => $_ARRAYLANG['TXT_SETTINGS'],
            'TXT_SELECTED_COUNTRIES' => $_ARRAYLANG['TXT_SELECTED_COUNTRIES'],
            'TXT_AVAILABLE_COUNTRIES' => $_ARRAYLANG['TXT_AVAILABLE_COUNTRIES'],
            // Shipping
            'TXT_SHIPPING_MAX_WEIGHT' => $_ARRAYLANG['TXT_SHIPPING_MAX_WEIGHT'],
            'TXT_MAX_WEIGHT_TIP' => $_ARRAYLANG['TXT_MAX_WEIGHT_TIP'],
            'TXT_FREE_OF_CHARGE' => $_ARRAYLANG['TXT_FREE_OF_CHARGE'],
            'TXT_SHIPPING_FEE' => $_ARRAYLANG['TXT_SHIPPING_FEE'],
            'TXT_SHOP_SETTING_WEIGHT_ENABLE' => $_ARRAYLANG['TXT_SHOP_SETTING_WEIGHT_ENABLE'],
            'TXT_SHOP_SHIPMENT_NEW' => $_ARRAYLANG['TXT_SHOP_SHIPMENT_NEW'],
            'TXT_SHOP_SHIPMENT_NEW_TIP' => $_ARRAYLANG['TXT_SHOP_SHIPMENT_NEW_TIP'],
            // VAT (Value Added Tax)
            'TXT_SHOP_VAT' => $_ARRAYLANG['TXT_SHOP_VAT'],
            // Image settings
            'TXT_SHOP_IMAGE_SETTINGS' => $_ARRAYLANG['TXT_SHOP_IMAGE_SETTINGS'],
            'TXT_SHOP_THUMBNAIL_MAX_WIDTH' => $_ARRAYLANG['TXT_SHOP_THUMBNAIL_MAX_WIDTH'],
            'TXT_SHOP_THUMBNAIL_MAX_HEIGHT' => $_ARRAYLANG['TXT_SHOP_THUMBNAIL_MAX_HEIGHT'],
            'TXT_SHOP_THUMBNAIL_QUALITY' => $_ARRAYLANG['TXT_SHOP_THUMBNAIL_QUALITY'],
            // Yellowpay settings
            'TXT_SHOP_YELLOWPAY_USE_TESTSERVER' => $_ARRAYLANG['TXT_SHOP_YELLOWPAY_USE_TESTSERVER'],
            'TXT_SHOP_YES' => $_ARRAYLANG['TXT_SHOP_YES'],
            'TXT_SHOP_NO' => $_ARRAYLANG['TXT_SHOP_NO'],
            // Datatrans settings
            'TXT_SHOP_AUTHORIZATION' => $_ARRAYLANG['TXT_SHOP_AUTHORIZATION'],
            'TXT_SHOP_DATATRANS' => $_ARRAYLANG['TXT_SHOP_DATATRANS'],
            'TXT_SHOP_DATATRANS_MERCHANT_ID' => $_ARRAYLANG['TXT_SHOP_DATATRANS_MERCHANT_ID'],
            'TXT_SHOP_DATATRANS_USE_TESTSERVER' => $_ARRAYLANG['TXT_SHOP_DATATRANS_USE_TESTSERVER'],
            'TXT_SHOP_EDIT' => $_ARRAYLANG['TXT_SHOP_EDIT'],
        ));

        if (!isset($_GET['tpl'])) {
            $_GET['tpl'] = '';
        }

        switch ($_GET['tpl']) {
            case 'currency':
                self::$objTemplate->addBlockfile('SHOP_SETTINGS_FILE', 'settings_block', 'module_shop_settings_currency.html');
                self::$objTemplate->setCurrentBlock('shopCurrency');
                foreach (Currency::getCurrencyArray() as $currency) {
                    $statusCheck = ($currency['status'] ? ' checked="checked"' : '');
                    $standardCheck = ($currency['is_default'] ? ' checked="checked"' : '');
                    self::$objTemplate->setVariable(array(
                        'SHOP_CURRENCY_STYLE' => (++$i % 2 ? 'row1' : 'row2'),
                        'SHOP_CURRENCY_ID' => $currency['id'],
                        'SHOP_CURRENCY_CODE' => $currency['code'],
                        'SHOP_CURRENCY_SYMBOL' => $currency['symbol'],
                        'SHOP_CURRENCY_NAME' => $currency['name'],
                        'SHOP_CURRENCY_RATE' => $currency['rate'],
                        'SHOP_CURRENCY_ACTIVE' => $statusCheck,
                        'SHOP_CURRENCY_STANDARD' => $standardCheck
                    ));
                    self::$objTemplate->parseCurrentBlock();
                }
                break;
            case 'payment':
                self::$objTemplate->addBlockfile('SHOP_SETTINGS_FILE', 'settings_block', 'module_shop_settings_payment.html');
                self::$objTemplate->setCurrentBlock('shopPayment');
                require_once ASCMS_MODULE_PATH.'/shop/lib/PaymentProcessing.class.php';
                foreach (Payment::getPaymentArray() as $id => $data) {
                    $query = "SELECT r.zones_id as zone_id ".
                             "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_rel_payment AS r, ".
                                     DBPREFIX."module_shop".MODULE_INDEX."_zones AS z ".
                              "WHERE z.activation_status=1 ".
                                "AND z.zones_id=r.zones_id ".
                                "AND r.payment_id=".$data['id'];
                    $objResult = $objDatabase->Execute($query);
                    if (!$objResult->EOF) {
                        $zone_id = $objResult->fields['zone_id'];
                    } else {
                        $zone_id = 0;
                    }

                    self::$objTemplate->setVariable(array(
                        'SHOP_PAYMENT_STYLE' => (++$i % 2 ? 'row2' : 'row1'),
                        'SHOP_PAYMENT_ID' => $data['id'],
                        'SHOP_PAYMENT_NAME' => $data['name'],
                        'SHOP_PAYMENT_HANDLER_MENUOPTIONS' => PaymentProcessing::getMenuoptions($data['processor_id']),
                        'SHOP_PAYMENT_COST' => $data['costs'],
                        'SHOP_PAYMENT_COST_FREE_SUM' => $data['costs_free_sum'],
                        'SHOP_ZONE_SELECTION' => Zones::getMenu(
                                $zone_id, 'paymentZone['.$data['id'].']'
                            ),
                            //$this->_getZonesMenu("paymentZone[".$data['id']."]", $zone_id),
                        'SHOP_PAYMENT_STATUS' => (intval($data['status']) ? ' checked="checked"' : ''),
                    ));
                    self::$objTemplate->parseCurrentBlock();
                }

                self::$objTemplate->setVariable(array(
                    'SHOP_PAYMENT_HANDLER_MENUOPTIONS_NEW' => // Selected PSP ID is -1 to disable the
                        // "Please select" option
                        PaymentProcessing::getMenuoptions(-1),
                    'SHOP_ZONE_SELECTION_NEW' => Zones::getMenu(0, 'paymentZone_new'),
                        //$this->_getZonesMenu('paymentZone_new')
                ));

                // end show payment
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
                    $query = "SELECT r.zones_id as zone_id ".
                             "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_rel_shipment AS r, ".
                                     DBPREFIX."module_shop".MODULE_INDEX."_zones AS z ".
                            "WHERE z.activation_status=1 ".
                              "AND z.zones_id=r.zones_id ".
                              "AND r.shipment_id=$sid";
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
                                'SHOP_SHIPMENT_PRICE_FREE' => $arrConditions['price_free'],
                                'SHOP_SHIPMENT_COST' => $arrConditions['cost'],
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
                            //$this->_getZonesMenu("shipmentZone[$sid]", $zone_id),
                        'SHOP_SHIPPER_STATUS' => ($arrShipper['status'] ? ' checked="checked"' : ''),
                        // field not used anymore
                        //'SHOP_SHIPMENT_LANG_ID' => $this->_getLanguageMenu("shipmentLanguage[$sid]", $val['lang_id']),
                    ));

                    //self::$objTemplate->setCurrentBlock('shopShipper');
                    self::$objTemplate->parse('shopShipper');
                }
                self::$objTemplate->setVariable(
                    'SHOP_ZONE_SELECTION_NEW', Zones::getMenu(0, 'shipmentZoneNew')
                );
                        //$this->_getZonesMenu('shipmentZoneNew', 1)));
                // end show shipment
                break;
            case 'countries':
                // start show countries
                self::$objTemplate->addBlockfile('SHOP_SETTINGS_FILE', 'settings_block', 'module_shop_settings_countries.html');
                $selected = '';
                $notSelected = '';
                $arrCountries = Country::getArray();
                foreach ($arrCountries as $cId => $data) {
                    if ($data['status'] == 1) {
                        $selected .=
                            '<option value="'.$cId.'">'.
                            $data['name']."</option>\n";
                    } else {
                        $notSelected .=
                            '<option value="'.$cId.'">'.
                            $data['name']."</option>\n";
                    }
                }
                self::$objTemplate->setVariable(array(
                    'SHOP_COUNTRY_SELECTED_OPTIONS' => $selected,
                    'SHOP_COUNTRY_NOTSELECTED_OPTIONS' => $notSelected
                ));
                // end show countries
                break;
            case 'zones':
                // start show zones
                self::$objTemplate->addBlockfile('SHOP_SETTINGS_FILE', 'settings_block', 'module_shop_settings_zones.html');
                //self::$objTemplate->setCurrentBlock('shopZones');
                $arrZones = Zones::getZoneArray();
                $selectFirst = false;
                $strZoneOptions = '';
                foreach ($arrZones as $zone_id => $arrZone) {
                    // Skip zone "All"
                    if ($zone_id == 1) continue;
                    $strZoneOptions .=
                        '<option value="'.$zone_id.'"'.
                        ($selectFirst ? '' : ' selected="selected"').
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
                        'ZONE_ACTIVE_STATUS' => ($arrZone['status'] ? ' checked="checked"' : '') ,
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
                // gets indexed language array
                $arrLanguage = FWLanguage::getLanguageArray();
                self::$objTemplate->addBlockfile('SHOP_SETTINGS_FILE', 'settings_block', 'module_shop_settings_mail.html');
                self::$objTemplate->setVariable(array(
                    'TXT_MAIL_TEMPLATES' => $_ARRAYLANG['TXT_MAIL_TEMPLATES'],
                    'TXT_REPLACEMENT_DIRECTORY' => $_ARRAYLANG['TXT_REPLACEMENT_DIRECTORY'],
                    'TXT_SHOP_ADD_EDIT' => $_ARRAYLANG['TXT_ADD'],
                    'TXT_SEND_TEMPLATE' => $_ARRAYLANG['TXT_SEND_TEMPLATE'],
                    'TXT_TEMPLATE' => $_ARRAYLANG['TXT_TEMPLATE'],
                    'TXT_FUNCTIONS' => $_ARRAYLANG['TXT_FUNCTIONS'],
                    'TXT_ORDER' => $_ARRAYLANG['TXT_ORDER'],
                    'TXT_REPLACEMENT' => $_ARRAYLANG['TXT_REPLACEMENT'],
                    'TXT_SHOP_ORDER_ID' => $_ARRAYLANG['TXT_SHOP_ORDER_ID'],
                    'TXT_CUSTOMER_INFORMATIONS' => $_ARRAYLANG['TXT_CUSTOMER_INFORMATIONS'],
                    'TXT_CUSTOMER_NR' => $_ARRAYLANG['TXT_CUSTOMER_NR'],
                    'TXT_SHIPPING_ADDRESS' => $_ARRAYLANG['TXT_SHIPPING_ADDRESS'],
                    'TXT_ORDER_DETAILS' => $_ARRAYLANG['TXT_ORDER_DETAILS'],
                    'TXT_ORDER_SUM' => $_ARRAYLANG['TXT_ORDER_SUM'],
                    'TXT_EMAIL_ADDRESS' => $_ARRAYLANG['TXT_SHOP_EMAIL_ADDRESS'],
                    'TXT_USERNAME' => $_ARRAYLANG['TXT_USERNAME'],
                    'TXT_PASSWORD' => $_ARRAYLANG['TXT_PASSWORD'],
                    'TXT_OTHER' => $_ARRAYLANG['TXT_OTHER'],
                    'TXT_DATE' => $_ARRAYLANG['TXT_DATE'],
                    'TXT_CUSTOMER_REMARKS' => $_ARRAYLANG['TXT_CUSTOMER_REMARKS'],
                    'TXT_REPLACEMENT_NOT_AVAILABLE' => $_ARRAYLANG['TXT_REPLACEMENT_NOT_AVAILABLE'],
                    'TXT_NEW_TEMPLATE' => $_ARRAYLANG['TXT_NEW_TEMPLATE'],
                    'TXT_TEMPLATE_NAME' => $_ARRAYLANG['TXT_TEMPLATE_NAME'],
                    'TXT_SENDER' => $_ARRAYLANG['TXT_SENDER'],
                    'TXT_EMAIL' => $_ARRAYLANG['TXT_EMAIL'],
                    'TXT_NAME' => $_ARRAYLANG['TXT_NAME'],
                    'TXT_SUBJECT' => $_ARRAYLANG['TXT_SUBJECT'],
                    'TXT_MESSAGE' => $_ARRAYLANG['TXT_MESSAGE'],
                    'TXT_STORE_AS_NEW_TEMPLATE' => $_ARRAYLANG['TXT_STORE_AS_NEW_TEMPLATE'],
                    'TXT_STORE' => $_ARRAYLANG['TXT_STORE'],
                    'TXT_SHOP_RECIPIENT_ADDRESS' => $_ARRAYLANG['TXT_SHOP_RECIPIENT_ADDRESS'],
                    'TXT_SEPARATED_WITH_COMMAS' => $_ARRAYLANG['TXT_SEPARATED_WITH_COMMAS'],
                    'TXT_SEND' => $_ARRAYLANG['TXT_SEND'],
                    'TXT_CANNOT_DELETE_TEMPLATE_LANGUAGE' => $_ARRAYLANG['TXT_CANNOT_DELETE_TEMPLATE_LANGUAGE'],
                    'TXT_CONFIRM_DELETE_TEMPLATE_LANGUAGE' => $_ARRAYLANG['TXT_CONFIRM_DELETE_TEMPLATE_LANGUAGE'],
                    'TXT_CONFIRM_DELETE_TEMPLATE' => $_ARRAYLANG['TXT_CONFIRM_DELETE_TEMPLATE'],
                    'TXT_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
                    'TXT_SHOP_PLEASE_SET_RECIPIENT_ADDRESS' => $_ARRAYLANG['TXT_SHOP_PLEASE_SET_RECIPIENT_ADDRESS'],
                    'TXT_SET_MAIL_FROM_ADDRESS' => $_ARRAYLANG['TXT_SET_MAIL_FROM_ADDRESS'],
                    'TXT_ADDRESS_CUSTOMER' => $_ARRAYLANG['TXT_ADDRESS_CUSTOMER'],
                    'TXT_SHOP_COMPANY' => $_ARRAYLANG['TXT_SHOP_COMPANY'],
                    'TXT_SHOP_PREFIX' => $_ARRAYLANG['TXT_SHOP_PREFIX'],
                    'TXT_SHOP_FIRSTNAME' => $_ARRAYLANG['TXT_SHOP_FIRSTNAME'],
                    'TXT_SHOP_LASTNAME' => $_ARRAYLANG['TXT_SHOP_LASTNAME'],
                    'TXT_SHOP_ADDRESS' => $_ARRAYLANG['TXT_SHOP_ADDRESS'],
                    'TXT_SHOP_ZIP' => $_ARRAYLANG['TXT_SHOP_ZIP'],
                    'TXT_SHOP_CITY' => $_ARRAYLANG['TXT_SHOP_CITY'],
                    'TXT_SHOP_COUNTRY' => $_ARRAYLANG['TXT_SHOP_COUNTRY'],
                    'TXT_SHOP_PHONE' => $_ARRAYLANG['TXT_SHOP_PHONE'],
                    'TXT_SHOP_FAX' => $_ARRAYLANG['TXT_SHOP_FAX'],
                    'TXT_SHOP_SHIPPING_INFORMATIONS' => $_ARRAYLANG['TXT_SHOP_SHIPPING_INFORMATIONS'],
                    'TXT_SHOP_ORDER_TIME' => $_ARRAYLANG['TXT_SHOP_ORDER_TIME'],
                    'TXT_SHOP_DOWNLOAD_USERNAME' => $_ARRAYLANG['TXT_SHOP_DOWNLOAD_USERNAME'],
                    'TXT_SHOP_DOWNLOAD_PASSWORD' => $_ARRAYLANG['TXT_SHOP_DOWNLOAD_PASSWORD'],
                    'TXT_SHOP_LOGIN_DATA' => $_ARRAYLANG['TXT_SHOP_LOGIN_DATA'],
                    'TXT_SHOP_ORDER_ID_CUSTOM' => $_ARRAYLANG['TXT_SHOP_ORDER_ID_CUSTOM'],
                ));
                // set config vars
                self::$objTemplate->setVariable(array(
                    'SHOP_MAIL_COLS' => count($arrLanguage) + 2,
                ));
                self::$objTemplate->setGlobalVariable(array(
                    'TXT_SEND_TEMPLATE' => $_ARRAYLANG['TXT_SEND_TEMPLATE'],
                    'TXT_DELETE' => $_ARRAYLANG['TXT_DELETE'],
                    'TXT_SHOP_EDIT' => $_ARRAYLANG['TXT_SHOP_EDIT'],
                    'SHOP_MAIL_LANG_COL_WIDTH' => intval(70 / count($arrLanguage))
                ));
                // send template
                if (!empty($_POST['shopMailSend'])) {
                    if (!empty($_POST['shopMailTo'])) {
                        $arrMailTo        = explode(',', $_POST['shopMailTo']);
                        $shopMailFrom     = $_POST['shopMailFromAddress'];
                        $shopMailFromText = $_POST['shopMailFromName'];
                        $shopMailSubject  = str_replace('<DATE>', date('d.m.Y'), $_POST['shopMailSubject']);
                        $shopMailBody     = str_replace('<DATE>', date('d.m.Y'), $_POST['shopMailBody']);
                        foreach ($arrMailTo as $shopMailTo) {
                            $shopMailTo    = trim($shopMailTo);
                            $blnMailResult = Mail::send($shopMailTo, $shopMailFrom, $shopMailFromText, $shopMailSubject, $shopMailBody);
                            if ($blnMailResult) {
                                self::addMessage(sprintf($_ARRAYLANG['TXT_EMAIL_SEND_SUCCESSFULLY'], $shopMailTo));
                            } else {
                                self::addError($_ARRAYLANG['TXT_MESSAGE_SEND_ERROR']);
                            }
                        }
                    } else {
                        self::addError($_ARRAYLANG['TXT_SHOP_PLEASE_SET_RECIPIENT_ADDRESS']);
                    }
                }

                // Generate title row of the template list
                $arrAvailable = array();
                foreach ($arrLanguage as $lang_id => $langValues) {
                    if ($langValues['frontend']) {
                        self::$objTemplate->setVariable(array('SHOP_MAIL_LANGUAGE' => $langValues['name'],));
                        self::$objTemplate->parse('shopMailLanguages');
                        // Get the availability of all templates
                        $arrTemplates = Mail::getTemplateArray($lang_id);
                        foreach ($arrTemplates as $template_id => $arrTemplate) {
                            $arrAvailable[$template_id][$lang_id] =
                                $arrTemplate['available'];
                        }
                    }
                    if ($langValues['is_default'] == 'true')
                        $defaultLang = $langValues['id'];
                }
                // Generate rows of the template list with the availability icon
                foreach ($arrAvailable as $template_id => $arrLangStatus) {
                    self::$objTemplate->setVariable(
                        'SHOP_MAIL_CLASS', (++$i % 2 ? 'row1' : 'row2')
                    );
                    foreach ($arrLangStatus as $lang_id => $lang_status) {
                        self::$objTemplate->setVariable(
                            'SHOP_MAIL_STATUS',
                                ($lang_status
                                    ? '<a href="javascript:loadTpl('.$template_id.','.
                                        $lang_id.',\'shopMailEdit\')" title="'.
                                        $_ARRAYLANG['TXT_SHOP_EDIT'].
                                        '"><img src="images/icons/check.gif" width="15" height="15" alt="'.
                                        $_ARRAYLANG['TXT_SHOP_EDIT'].'" border="0" /></a>'
                                    : '&nbsp;'
                                )
                        );
                        self::$objTemplate->parse('shopMailLanguagesStatus');
                    }
                    $template_name = $arrTemplates[$template_id]['name'];
                    $template_name =
                        (substr($template_name, 0, 4) == 'TXT_'
                          ? $_ARRAYLANG[$template_name] : $template_name
                        );
                    $template_protected =
                        ($arrTemplates[$template_id]['protected']
                          ? '&nbsp;('.$_ARRAYLANG['TXT_SYSTEM_TEMPLATE'].')'
                          : ''
                        );
                    self::$objTemplate->setVariable(array(
                        'SHOP_TEMPLATE_ID' => $template_id,
                        'SHOP_LANGUAGE_ID' => $defaultLang,
                        'SHOP_MAIL_TEMPLATE_NAME' => $template_name.$template_protected,
                    ));
                    self::$objTemplate->parse('shopMailTemplates');
                    // generate dropdown template-list
                    $strMailSelectedTemplates .=
                        '<option value="'.$template_id.'" '.
                        (   !empty($_GET['tplId'])
                         && $_GET['tplId'] == $template_id
                            ? ' selected="selected"' : ''
                        ).'>'.
                        $template_name.$template_protected."</option>\n";
                    $strMailTemplates .=
                        '<option value="'.$template_id.'">'.
                        $template_name.$template_protected.
                        "</option>\n";
                    // get the name of the loaded template to edit
                    if (!empty($_GET['tplId']) && $_GET['strTab'] == 'shopMailEdit') {
                        if ($template_id == $_GET['tplId']) {
                            self::$objTemplate->setVariable(
                                'SHOP_MAIL_TEMPLATE', $template_name
                            );
                        }
                    }
                }
                // Load template or show template overview
                if (!empty($_GET['strTab'])) {
                    switch ($_GET['strTab']) {
                        case 'shopMailEdit':
                            if ($_GET['tplId'] != 0) {
                                $template_id = $_GET['tplId'];
                                // set the source template to load
                                if (!empty($_GET['portLangId'])) {
                                    $lang_id = $_GET['portLangId'];
                                } else {
                                    $lang_id = $_GET['langId'];
                                }
                                // Generate language menu
                                $langMenu =
                                    '<select name="langId" size="1" '.
                                    'onchange="loadTpl(document.shopFormEdit.elements[\'tplId\'].value,this.value,\'shopMailEdit\');">'."\n";
                                foreach ($arrLanguage as $langValues) {
                                    if ($langValues['frontend']) {
                                        $langMenu .=
                                            '<option value="'.$langValues['id'].'"'.
                                            ($_GET['langId'] == $langValues['id']
                                                ? ' selected="selected"' : '').
                                            '>'.$langValues['name']."</option>\n";
                                    }
                                }
                                $langMenu .=
                                    '</select>'.
                                    '&nbsp;<input type="checkbox" id="portMail" name="portMail" value="1" />&nbsp;'.
                                    $_ARRAYLANG['TXT_COPY_TO_NEW_LANGUAGE'];
                                // Get the content of the template
                                    $arrTemplate = Mail::getTemplate(intval($template_id), $lang_id);
                                self::$objTemplate->setVariable(array(
                                    'SHOP_MAIL_ID' => (isset($_GET['portLangId']) ? '' : $template_id),
                                    'SHOP_MAIL_NAME' => $arrTemplate['sender'],
                                    'SHOP_MAIL_SUBJ' => $arrTemplate['subject'],
                                    'SHOP_MAIL_MSG' => $arrTemplate['message'],
                                    'SHOP_MAIL_FROM' => $arrTemplate['from'],
                                    'SHOP_LOADD_TEMPLATE_ID' => $_GET['tplId'],
                                    'SHOP_LOADD_LANGUAGE_ID' => $_GET['langId'],
                                    'TXT_SHOP_ADD_EDIT' => $_ARRAYLANG['TXT_EDIT'],
                                ));
                                self::$objTemplate->touchBlock('saveToOther');
                            } else {
                                self::$objTemplate->hideBlock('saveToOther');
                                // set the default sender
                                self::$objTemplate->setVariable(array(
                                    'SHOP_MAIL_FROM' => $this->arrConfig['email']['value'],
                                ));
                            }
                            break;
                        case 'shopMailSend':
                            // Generate language menu
                            $langMenu =
                                '<select name="langId" size="1" '.
                                'onchange="loadTpl(document.shopFormSend.elements[\'tplId\'].value,this.value,\'shopMailSend\');">'."\n";
                            foreach ($arrLanguage as $langValues) {
                                if ($langValues['frontend']) {
                                    $langMenu .=
                                        '<option value="'.$langValues['id'].'"'.
                                        (!empty($_GET['langId']) && $_GET['langId'] == $langValues['id']
                                            ? ' selected="selected"' : '').
                                        '>'.$langValues['name']."</option>\n";
                                }
                            }
                            $langMenu .= '</select>';
                            // Get the content of the template
                            $tplId = (isset($_GET['tplId']) ? intval($_GET['tplId']) : '');
                            $lang_id = (isset($_GET['langId']) ? intval($_GET['langId']) : '');
                            $arrTemplate = Mail::getTemplate($tplId, $lang_id);
                            if ($arrTemplate) {
                                self::$objTemplate->setVariable(array(
                                    'SHOP_MAIL_ID_SEND' => $arrTemplate['id'],
                                    'SHOP_MAIL_NAME_SEND' => $arrTemplate['sender'],
                                    'SHOP_MAIL_SUBJ_SEND' => $arrTemplate['subject'],
                                    'SHOP_MAIL_MSG_SEND' => $arrTemplate['message'],
                                    'SHOP_MAIL_FROM_SEND' => $arrTemplate['from'],
                                ));
                            } else {
                                self::$objTemplate->setVariable(
                                    'SHOP_MAIL_FROM_SEND',
                                        $this->arrConfig['email']['value']
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
                                ? ($_GET['tplId'] != 0
                                    ? $langMenu
                                    : '<input type="hidden" name="langId" value="'.
                                        $defaultLang.'" />'
                                  )
                                : '<input type="hidden" name="langId" value="'.
                                    $defaultLang.'" />'
                            ),
                        'SHOP_MAIL_SEND_STYLE' => ($_GET['strTab'] == 'shopMailSend'
                                ? 'display: block;' : 'display: none;'),
                        'SHOP_MAILTAB_SEND_CLASS' => ($_GET['strTab'] == 'shopMailSend' ? 'active' : ''),
                        'SHOP_MAIL_SEND_TEMPLATES' => ($_GET['strTab'] == 'shopMailSend'
                                ? $strMailSelectedTemplates : $strMailTemplates),
                        'SHOP_MAIL_SEND_LANGS' => ($_GET['strTab'] == 'shopMailSend'
                                ? (isset($_GET['tplId'])
                                    ? $langMenu
                                    : '<input type="hidden" name="langId" value="'.
                                        $defaultLang.'" />')
                                : '<input type="hidden" name="langId" value="'.
                                    $defaultLang.'" />'),
                        'SHOP_MAIL_TO' => (   $_GET['strTab'] == 'shopMailSend'
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
                        'SHOP_MAIL_EDIT_LANGS' => '<input type="hidden" name="langId" value="'.$defaultLang.'" />',
                        'SHOP_MAIL_SEND_STYLE' => 'display: none;',
                        'SHOP_MAILTAB_SEND_CLASS' => '',
                        'SHOP_MAIL_SEND_TEMPLATES' => $strMailTemplates,
                        'SHOP_MAIL_SEND_LANGS' => '<input type="hidden" name="langId" value="'.$defaultLang.'" />',
                        'SHOP_MAIL_TO' => '',
                        'SHOP_MAIL_FROM' => $this->arrConfig['email']['value'],
                        'SHOP_MAIL_FROM_SEND' => $this->arrConfig['email']['value'],
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
                $enabled_home_customer = $this->arrConfig['vat_enabled_home_customer']['value'];
                $included_home_customer = $this->arrConfig['vat_included_home_customer']['value'];
                $enabled_home_reseller = $this->arrConfig['vat_enabled_home_reseller']['value'];
                $included_home_reseller = $this->arrConfig['vat_included_home_reseller']['value'];
                $enabled_foreign_customer = $this->arrConfig['vat_enabled_foreign_customer']['value'];
                $included_foreign_customer = $this->arrConfig['vat_included_foreign_customer']['value'];
                $enabled_foreign_reseller = $this->arrConfig['vat_enabled_foreign_reseller']['value'];
                $included_foreign_reseller = $this->arrConfig['vat_included_foreign_reseller']['value'];
                self::$objTemplate->setVariable(array(
                    'TXT_SHOP_VAT_ACTIVE' => $_ARRAYLANG['TXT_SHOP_VAT_ACTIVE'],
                    'TXT_SHOP_VAT_DETAILS' => $_ARRAYLANG['TXT_SHOP_VAT_DETAILS'],
                    'TXT_SHOP_VAT_NUMBER' => $_ARRAYLANG['TXT_SHOP_VAT_NUMBER'],
                    'TXT_SHOP_VAT_NEW' => $_ARRAYLANG['TXT_SHOP_VAT_NEW'],
                    'TXT_SHOP_VAT_RATES' => $_ARRAYLANG['TXT_SHOP_VAT_RATES'],
                    'TXT_SHOP_VAT' => $_ARRAYLANG['TXT_SHOP_VAT'],
                    'TXT_SHOP_VAT_CONFIRM_DELETE' => $_ARRAYLANG['TXT_SHOP_VAT_CONFIRM_DELETE'],
                    'TXT_SHOP_VAT_INCLUDED' => $_ARRAYLANG['TXT_SHOP_PRICES_VAT_INCLUDED'],
                    'TXT_SHOP_VAT_EXCLUDED' => $_ARRAYLANG['TXT_SHOP_PRICES_VAT_EXCLUDED'],
                    'TXT_SHOP_VAT_DEFAULT' => $_ARRAYLANG['TXT_SHOP_VAT_DEFAULT'],
                    'TXT_SHOP_VAT_SET_ALL' => $_ARRAYLANG['TXT_SHOP_VAT_SET_ALL'],
                    'TXT_SHOP_VAT_SET_UNSET' => $_ARRAYLANG['TXT_SHOP_VAT_SET_UNSET'],
                    'TXT_SHOP_VAT_CONFIRM_SET_ALL' => $_ARRAYLANG['TXT_SHOP_VAT_CONFIRM_SET_ALL'],
                    'TXT_SHOP_VAT_CONFIRM_SET_UNSET' => $_ARRAYLANG['TXT_SHOP_VAT_CONFIRM_SET_UNSET'],
                    // VAT -- added
                    'TXT_SHOP_VAT_COUNTRY_FOREIGN' => $_ARRAYLANG['TXT_SHOP_VAT_COUNTRY_FOREIGN'],
                    'TXT_SHOP_VAT_COUNTRY_HOME' => $_ARRAYLANG['TXT_SHOP_VAT_COUNTRY_HOME'],
                    'TXT_SHOP_VAT_CUSTOMER' => $_ARRAYLANG['TXT_SHOP_VAT_CUSTOMER'],
                    'TXT_SHOP_VAT_RESELLER' => $_ARRAYLANG['TXT_SHOP_VAT_RESELLER'],
                    'TXT_SHOP_VAT_OTHER' => $_ARRAYLANG['TXT_SHOP_VAT_OTHER'],
                    'TXT_SHOP_VAT_ENABLED' => $_ARRAYLANG['TXT_SHOP_VAT_ENABLED'],
                    // Variables
                    'SHOP_VAT_NUMBER' => $this->arrConfig['vat_number']['value'],
                    'SHOP_VAT_CHECKED_HOME_CUSTOMER' => ($enabled_home_customer ? ' checked="checked"' : ''),
                    'SHOP_VAT_DISPLAY_HOME_CUSTOMER' => ($enabled_home_customer ? 'block' : 'none'),
                    'SHOP_VAT_SELECTED_HOME_CUSTOMER_INCLUDED' => ($included_home_customer ? ' selected="selected"' : ''),
                    'SHOP_VAT_SELECTED_HOME_CUSTOMER_EXCLUDED' => ($included_home_customer ? '' : ' selected="selected"'),
                    'SHOP_VAT_CHECKED_HOME_RESELLER' => ($enabled_home_reseller ? ' checked="checked"' : ''),
                    'SHOP_VAT_DISPLAY_HOME_RESELLER' => ($enabled_home_reseller ? 'block' : 'none'),
                    'SHOP_VAT_SELECTED_HOME_RESELLER_INCLUDED' => ($included_home_reseller ? ' selected="selected"' : ''),
                    'SHOP_VAT_SELECTED_HOME_RESELLER_EXCLUDED' => ($included_home_reseller ? '' : ' selected="selected"'),
                    'SHOP_VAT_CHECKED_FOREIGN_CUSTOMER' => ($enabled_foreign_customer ? ' checked="checked"' : ''),
                    'SHOP_VAT_DISPLAY_FOREIGN_CUSTOMER' => ($enabled_foreign_customer ? 'block' : 'none'),
                    'SHOP_VAT_SELECTED_FOREIGN_CUSTOMER_INCLUDED' => ($included_foreign_customer ? ' selected="selected"' : ''),
                    'SHOP_VAT_SELECTED_FOREIGN_CUSTOMER_EXCLUDED' => ($included_foreign_customer ? '' : ' selected="selected"'),
                    'SHOP_VAT_CHECKED_FOREIGN_RESELLER' => ($enabled_foreign_reseller ? ' checked="checked"' : ''),
                    'SHOP_VAT_DISPLAY_FOREIGN_RESELLER' => ($enabled_foreign_reseller ? 'block' : 'none'),
                    'SHOP_VAT_SELECTED_FOREIGN_RESELLER_INCLUDED' => ($included_foreign_reseller ? ' selected="selected"' : ''),
                    'SHOP_VAT_SELECTED_FOREIGN_RESELLER_EXCLUDED' => ($included_foreign_reseller ? '' : ' selected="selected"'),
                    'SHOP_VAT_DEFAULT_MENUOPTIONS' => Vat::getMenuoptions(
                            $this->arrConfig['vat_default_id']['value'], true
                        ),
                    'SHOP_VAT_OTHER_MENUOPTIONS' => Vat::getMenuoptions(
                            $this->arrConfig['vat_other_id']['value'], true
                        ),
                ));
                break;
            default:
                // Shop general settings template
                self::$objTemplate->addBlockfile('SHOP_SETTINGS_FILE', 'settings_block', 'module_shop_settings_general.html');

                $saferpayStatus = ($this->arrConfig['saferpay_id']['status'] == 1) ? ' checked="checked"' : '';
                $saferpayTestStatus = ($this->arrConfig['saferpay_use_test_account']['status'] == 1) ? ' checked="checked"' : '';

                require_once ASCMS_MODULE_PATH.'/shop/payments/paypal/Paypal.class.php';
                $paypalStatus = ($this->arrConfig['paypal_account_email']['status'] == 1) ? ' checked="checked"' : '';

                $yellowpayTest = $this->arrConfig['yellowpay_use_testserver']['value'];
                $yellowpayTestCheckedYes = ($yellowpayTest ? ' checked="checked"' : '');
                $yellowpayTestCheckedNo = ($yellowpayTest ? '' : ' checked="checked"');

                // Datatrans
                $datatrans_request_type = Settings::getValueByName('datatrans_request_type');
                $datatrans_merchant_id = Settings::getValueByName('datatrans_merchant_id');
                $datatrans_status = Settings::getValueByName('datatrans_status');
                $datatrans_use_testserver = Settings::getValueByName('datatrans_use_testserver');

                self::$objTemplate->setVariable(array(
                    'SHOP_SAFERPAY_ID' => $this->arrConfig['saferpay_id']['value'],
                    'SHOP_SAFERPAY_STATUS' => $saferpayStatus,
                    'SHOP_SAFERPAY_TEST_ID' => $this->arrConfig['saferpay_use_test_account']['value'],
                    'SHOP_SAFERPAY_TEST_STATUS' => $saferpayTestStatus,
                    'SHOP_SAFERPAY_FINALIZE_PAYMENT' => ($this->arrConfig['saferpay_finalize_payment']['value']
                            ? ' checked="checked"' : ''
                        ),
                    'SHOP_SAFERPAY_WINDOW_MENUOPTIONS' => Saferpay::getWindowMenuoptions(
                            $this->arrConfig['saferpay_window_option']['value']
                        ),

                    'SHOP_YELLOWPAY_SHOP_ID' => $this->arrConfig['yellowpay_shop_id']['value'],
                    'SHOP_YELLOWPAY_STATUS' =>
                        ($this->arrConfig['yellowpay_shop_id']['status'] == 1
                            ? ' checked="checked"' : ''),
//                    'SHOP_YELLOWPAY_HASH_SEED' => $this->arrConfig['yellowpay_hash_seed']['value'],
// Replaced by
                    'SHOP_YELLOWPAY_HASH_SIGNATURE_IN' => $this->arrConfig['yellowpay_hash_signature_in']['value'],
                    'SHOP_YELLOWPAY_HASH_SIGNATURE_OUT' => $this->arrConfig['yellowpay_hash_signature_out']['value'],

                    'SHOP_YELLOWPAY_ACCEPTED_PAYMENT_METHODS_CHECKBOXES' => Yellowpay::getKnownPaymentMethodCheckboxes(),
                    'SHOP_YELLOWPAY_AUTHORIZATION_TYPE_OPTIONS' => Yellowpay::getAuthorizationMenuoptions(),
                    'SHOP_YELLOWPAY_USE_TESTSERVER_YES_CHECKED' => $yellowpayTestCheckedYes,
                    'SHOP_YELLOWPAY_USE_TESTSERVER_NO_CHECKED' => $yellowpayTestCheckedNo,

                    'SHOP_DATATRANS_AUTHORIZATION_TYPE_OPTIONS' => Datatrans::getReqtypeMenuoptions($datatrans_request_type),
                    'SHOP_DATATRANS_MERCHANT_ID' => $datatrans_merchant_id,
                    'SHOP_DATATRANS_STATUS' => ($datatrans_status ? ' checked="checked"' : ''),
                    'SHOP_DATATRANS_USE_TESTSERVER_YES_CHECKED' => ($datatrans_use_testserver ? ' checked:"checked"' : ''),
                    'SHOP_DATATRANS_USE_TESTSERVER_NO_CHECKED' => ($datatrans_use_testserver ? '' : ' checked:"checked"'),
                    // Not supported
                    //'SHOP_DATATRANS_ACCEPTED_PAYMENT_METHODS_CHECKBOXES' => 0,

                    'SHOP_CONFIRMATION_EMAILS' => $this->arrConfig['confirmation_emails']['value'],
                    'SHOP_CONTACT_EMAIL' => $this->arrConfig['email']['value'],
                    'SHOP_CONTACT_COMPANY' => $this->arrConfig['shop_company']['value'],
                    'SHOP_CONTACT_ADDRESS' => $this->arrConfig['shop_address']['value'],
                    'SHOP_CONTACT_TEL' => $this->arrConfig['telephone']['value'],
                    'SHOP_CONTACT_FAX' => $this->arrConfig['fax']['value'],
                    'SHOP_PAYPAL_EMAIL' => $this->arrConfig['paypal_account_email']['value'],
                    'SHOP_PAYPAL_STATUS' => $paypalStatus,
                    'SHOP_PAYPAL_DEFAULT_CURRENCY_MENUOPTIONS' => PayPal::getAcceptedCurrencyCodeMenuoptions(
                            $this->arrConfig['paypal_default_currency']['value']
                        ),
                    // LSV settings
                    'SHOP_PAYMENT_LSV_STATUS' => ($this->arrConfig['payment_lsv_status']['status'] ? ' checked="checked"' : ''),
                    'SHOP_PAYMENT_DEFAULT_CURRENCY' => Currency::getDefaultCurrencySymbol(),
                    // Country settings
                    'SHOP_GENERAL_COUNTRY_MENUOPTIONS' => Country::getMenuoptions(
                            $this->arrConfig['country_id']['value'], false
                        ),
                    // Thumbnail settings
                    'SHOP_THUMBNAIL_MAX_WIDTH' => $this->arrConfig['shop_thumbnail_max_width']['value'],
                    'SHOP_THUMBNAIL_MAX_HEIGHT' => $this->arrConfig['shop_thumbnail_max_height']['value'],
                    'SHOP_THUMBNAIL_QUALITY' => $this->arrConfig['shop_thumbnail_quality']['value'],
                    // Enable weight setting
                    'SHOP_WEIGHT_ENABLE_CHECKED' => ($this->arrConfig['shop_weight_enable']['value']
                            ? ' checked="checked"' : ''),
                    'SHOP_SHOW_PRODUCTS_DEFAULT_OPTIONS' => Products::getDefaultViewMenuoptions(
                            $this->arrConfig['shop_show_products_default']['value']
                        ),
                    'SHOP_PRODUCT_SORTING_MENUOPTIONS' => self::getProductSortingMenuoptions(),
                ));
                break;
        }
        self::$objTemplate->parse('settings_block');
    }


    /**
     * Show exchange page
     *
     * Shows the import & export page and does the import and export work
     *
     * @access private
     * @see Exchange::selectExchangeContent()
     */
    function showExchange()
    {
        global $_ARRAYLANG;

        self::$pageTitle = $_ARRAYLANG['TXT_EXPORT']."/".$_ARRAYLANG['TXT_IMPORT'];

        // Exchange content
        if (isset($_POST['handler']) && !empty($_POST['handler'])) {
            $strMethod = substr($_POST['handler'],0,6);
            $strStep = substr($_POST['handler'],7);
            self::$objTemplate->setTemplate($this->objExchange->selectExchangeContent($strMethod, $strStep));
        } else {
            self::$objTemplate->setTemplate($this->objExchange->selectExchangeContent());
        }
    }


    function showCategories()
    {
        global $_ARRAYLANG;

        $i = 1;
        self::$pageTitle = $_ARRAYLANG['TXT_CATEGORIES'];
        self::$objTemplate->loadTemplateFile('module_shop_categories.html', true, true);

        // ID of the category to be edited, if any
        $id = (isset($_REQUEST['modCatId']) ? $_REQUEST['modCatId'] : 0);

        self::$objTemplate->setVariable(array(
            'TXT_ARTICLEGROUPS' => $_ARRAYLANG['TXT_ARTICLE_GROUPS'],
            'TXT_NEW_MAIN_ARTICLE_GROUP' => $_ARRAYLANG['TXT_NEW_MAIN_ARTICLE_GROUP'],
            'TXT_ACTIVE' => $_ARRAYLANG['TXT_ACTIVE'],
            'TXT_TOTAL' => $_ARRAYLANG['TXT_TOTAL'],
            'TXT_NAME' => $_ARRAYLANG['TXT_NAME'],
            'TXT_STORE' => $_ARRAYLANG['TXT_STORE'],
            'TXT_ID' => $_ARRAYLANG['TXT_ID'],
            'TXT_ACTION' => $_ARRAYLANG['TXT_ACTION'],
            'TXT_CONFIRM_DELETE_SHOP_CATEGORIES' => $_ARRAYLANG['TXT_CONFIRM_DELETE_SHOP_CATEGORIES'],
            'TXT_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
            'TXT_DESCRIPTION' => $_ARRAYLANG['TXT_DESCRIPTION'],
            'TXT_ACCEPT_CHANGES' => $_ARRAYLANG['TXT_ACCEPT_CHANGES'],
            'TXT_DELETE_MARKED' => $_ARRAYLANG['TXT_DELETE_MARKED'],
            'TXT_MARKED' => $_ARRAYLANG['TXT_MARKED'],
            'TXT_SELECT_ALL' => $_ARRAYLANG['TXT_SELECT_ALL'],
            'TXT_REMOVE_SELECTION' => $_ARRAYLANG['TXT_REMOVE_SELECTION'],
            'TXT_MAKE_SELECTION' => $_ARRAYLANG['TXT_MAKE_SELECTION'],
            'TXT_SELECT_ACTION' => $_ARRAYLANG['TXT_SELECT_ACTION'],
            'TXT_SHOP_EDIT_OR_ADD_IMAGE' => $_ARRAYLANG['TXT_SHOP_EDIT_OR_ADD_IMAGE'],
            'TXT_SHOP_CATEGORY_IMAGE' => $_ARRAYLANG['TXT_SHOP_CATEGORY_IMAGE'],
            'TXT_SHOP_CATEGORY_VIRTUAL' => $_ARRAYLANG['TXT_SHOP_CATEGORY_VIRTUAL'],
            'TXT_SHOP_CATEGORY_PARENT' => $_ARRAYLANG['TXT_SHOP_CATEGORY_PARENT'],
            'TXT_SHOP_CATEGORY_EDIT' => ($id
                    ? $_ARRAYLANG['TXT_SHOP_CATEGORY_EDIT']
                    : $_ARRAYLANG['TXT_SHOP_CATEGORY_NEW']
                ),
            'TXT_SHOP_CATEGORY_LIST' => $_ARRAYLANG['TXT_SHOP_CATEGORY_LIST'],
            'TXT_SHOP_CANCEL' => $_ARRAYLANG['TXT_SHOP_CANCEL'],
        ));

        self::$objTemplate->setGlobalVariable(array(
            'TXT_STATUS' => $_ARRAYLANG['TXT_STATUS'],
            'TXT_EDIT' => $_ARRAYLANG['TXT_EDIT'],
            'TXT_PREVIEW' => $_ARRAYLANG['TXT_PREVIEW'],
            'TXT_DELETE' => $_ARRAYLANG['TXT_DELETE']
        ));

        // Get the tree array of all ShopCategories
        $arrShopCategories =
            ShopCategories::getTreeArray(true, false, false);
        self::$objTemplate->setVariable(
            'SHOP_TOTAL_CATEGORIES',
            ShopCategories::getTreeNodeCount()
        );
        // Default to the list tab
        $flagEditTabActive = false;
        // Edit the selected category
        if (!empty($_REQUEST['modCatId'])) {
            // Flip view to the edit tab
            $flagEditTabActive = true;
            $arrShopCategory = ShopCategories::getArrayById($id);
            $pictureFilename = $arrShopCategory['picture'];
            $picturePath = ASCMS_SHOP_IMAGES_WEB_PATH.'/'.
                ImageManager::getThumbnailFilename($pictureFilename);
            if ($pictureFilename == '') {
                $picturePath = self::$defaultImage;
            }
            self::$objTemplate->setVariable(array(
                'TXT_ADD_NEW_SHOP_GROUP' => $_ARRAYLANG['TXT_EDIT_PRODUCT_GROUP'],
                'SHOP_MOD_CAT_ID' => $id,
                'SHOP_SELECTED_CAT_NAME' => $arrShopCategory['name'],
                'SHOP_CAT_MENUOPTIONS' => ShopCategories::getShopCategoriesMenuoptions(
                        $arrShopCategory['parentId'], false
                    ),
                'SHOP_PICTURE_IMG_HREF' => $picturePath,
                'SHOP_CATEGORY_IMAGE_FILENAME' => $pictureFilename,
                'SHOP_SELECTED_CATEGORY_VIRTUAL_CHECKED' => ($arrShopCategory['virtual'] ? ' checked="checked"' : ''),
                'SHOP_SELECTED_CATEGORY_STATUS_CHECKED' => ($arrShopCategory['status'] ? ' checked="checked"' : ''),
            ));
        } else {
            self::$objTemplate->setVariable(array(
                'TXT_ADD_NEW_SHOP_GROUP' => $_ARRAYLANG['TXT_ADD_NEW_PRODUCT_GROUP'],
                'SHOP_MOD_CAT_ID' => '',
                'SHOP_SELECTED_CAT_NAME' => '',
                'SHOP_CAT_MENUOPTIONS' => ShopCategories::getShopCategoriesMenuoptions(0, false),
                'SHOP_PICTURE_IMG_HREF' => self::$defaultImage,
                'SHOP_SELECTED_CATEGORY_VIRTUAL_CHECKED' => '',
                'SHOP_SELECTED_CATEGORY_STATUS_CHECKED' => ' checked="checked"',
            ));
        }

        $max_width = intval($this->arrConfig['shop_thumbnail_max_width']['value']);
        $max_height = intval($this->arrConfig['shop_thumbnail_max_height']['value']);
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
                        $arrShopCategory['name'], ENT_QUOTES, CONTREXX_CHARSET
                    ),
                'SHOP_CAT_SORTING' => $arrShopCategory['sorting'],
                'SHOP_CAT_LEVELSPACE' => str_repeat('|----', $arrShopCategory['level']),
                'SHOP_CAT_STATUS' => ($arrShopCategory['status']
                        ? $_ARRAYLANG['TXT_ACTIVE']
                        : $_ARRAYLANG['TXT_INACTIVE']
                    ),
                'SHOP_CAT_STATUS_CHECKED' => ($arrShopCategory['status'] ? ' checked="checked"' : ''),
                'SHOP_CAT_STATUS_PICTURE' => ($arrShopCategory['status']
                        ? 'status_green.gif'
                        : 'status_red.gif'
                    ),
                'SHOP_CAT_VIRTUAL_CHECKED' => ($arrShopCategory['virtual'] ? ' checked="checked"' : ''),
            ));
            self::$objTemplate->parse('catRow');
        }
        return true;
    }


    /**
     * Do shop categories menu
     *
     * @version  1.0      initial version
     * @param    integer  $parentId
     * @param    integer  $level
     * @return   string   $result
     */
    function doCategoryTree($parentId=0, $level=0)
    {
        global $objDatabase;

        $query = "
            SELECT catid, parentid, catname, catsorting, catstatus
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
             WHERE parentId=$parentId
          ORDER BY parentid ASC, catsorting ASC
        ";
        if (($objResult = $objDatabase->Execute($query)) === false) {
            $this->errorHandling();
            return false;
        }
        while (!$objResult->EOF) {
            $catId = $objResult->fields['catid'];
            $this->categoryTreeName[$catId] =
                $objResult->fields['catname'];
            $this->categoryTreeSorting[$catId] =
                $objResult->fields['catsorting'];
            $this->categoryTreeStatus[$catId] =
                $objResult->fields['catstatus'];
            $this->categoryTreeLevel[$catId] = $level;
            $this->doCategoryTree($catId, $level+1);
            $objResult->MoveNext();
        }
        return true;
    }


    /**
     * Do shop categories menu
     *
     * @version  1.0      initial version
     * @param    integer  $parentId
     * @param    integer  $level
     * @return   string   $result
     */
    function doCategoryTreeActiveOnly($parentId=0, $level=0)
    {
        global $objDatabase;

        $query = "
            SELECT catid, parentid, catname, catsorting, catstatus
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
             WHERE catstatus=1 AND parentId=$parentId
             ORDER BY parentid ASC, catsorting ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult === false) {
            $this->errorHandling();
            return false;
        }
        while (!$objResult->EOF) {
            $catId = $objResult->fields['catid'];
            $this->categoryTreeName[$catId] =
                $objResult->fields['catname'];
            $this->categoryTreeSorting[$catId] =
                $objResult->fields['catsorting'];
            $this->categoryTreeStatus[$catId] =
                $objResult->fields['catstatus'];
            $this->categoryTreeLevel[$catId] = $level;
            $this->doCategoryTree($catId, $level+1);
            $objResult->MoveNext();
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
        $name      = strip_tags($_POST['modCatName']);
        $id        = $_POST['modCatId'];
        $status    = (isset($_POST['modCatStatus']) ? true : false);
        $virtual   = (isset($_POST['modCatVirtual']) ? true : false);
        $parentid  = $_POST['modCatParentId'];
        $picture   = $_POST['modCatImageHref'];
        if ($id > 0) {
            // Update existing ShopCategory
            $objShopCategory = ShopCategory::getById($id);
            if (!$objShopCategory) return false;
            // Check validity of the IDs of the category and its parent.
            // If the values are identical, leave the parent ID alone!
            if ($id != $parentid) $objShopCategory->setParentId($parentid);
            $objShopCategory->setName($name);
            $objShopCategory->setStatus($status);
        } else {
            // Add new ShopCategory
            $objShopCategory = new ShopCategory($name, $parentid, $status, 0);
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
                $this->arrConfig['shop_thumbnail_max_width']['value'],
                $this->arrConfig['shop_thumbnail_max_height']['value'],
                $this->arrConfig['shop_thumbnail_quality']['value']
            )) {
                self::addError($_ARRAYLANG['TXT_SHOP_ERROR_CREATING_CATEGORY_THUMBNAIL']);
            }
        }
        $objShopCategory->setPicture($picture);
        $objShopCategory->setVirtual($virtual);
        if (!$objShopCategory->store()) {
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
            $order     = $_POST['catSorting'][$id];
            $virtual   = ($_POST['catVirtual'][$id] ? true : false);
            $status    = ($_POST['catStatus'][$id]  ? true : false);
            if ($order   != $_POST['catSorting_old'][$id]
             || $status  != $_POST['catStatus_old'][$id]
             || $virtual != $_POST['catVirtual_old'][$id]) {
                $objShopCategory = ShopCategory::getById($id);
                $objShopCategory->setSorting($order);
                $objShopCategory->setStatus($status);
                $objShopCategory->setVirtual($virtual);
                if (!$objShopCategory->store()) {
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
                $query = "SELECT catid FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories WHERE 1 AND parentid=".intval($cId);
                $objResult = $objDatabase->Execute($query);
                if ($objResult->RecordCount() > 0) {
                    self::addError($_ARRAYLANG['TXT_CATEGORY_NOT_DELETED_BECAUSE_IN_USE']."&nbsp;(".$_ARRAYLANG['TXT_CATEGORY']."&nbsp;".$cId.")");
                    continue;
                }

                // Check whether products exist in this category
                $query = "
                    SELECT id FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
                     WHERE catid=$cId
                ";
                $objResult = $objDatabase->Execute($query);
                $arrProducts = array();
                while (!$objResult->EOF) {
                    array_push($arrProducts, $objResult->fields['id']);
                    $objResult->MoveNext();
                }

                // Delete the products in the category
                if (count($arrProducts) > 0) {
                    foreach ($arrProducts as $id) {
                        // Check whether there are orders with this Product ID
                        $query = "SELECT 1 FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items WHERE productid=".$id;
                        $objResult = $objDatabase->Execute($query);
                        if ($objResult->RecordCount() > 0) {
                            self::addError($_ARRAYLANG['TXT_COULD_NOT_DELETE_ALL_PRODUCTS']."&nbsp;(".$_ARRAYLANG['TXT_CATEGORY']."&nbsp;".$cId.")");
                            continue 2;
                        }
                        $this->delProduct($id);
                    }
                }

                // Delete the category
                $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories WHERE catid=".intval($cId);
                if (!$objDatabase->Execute($query)) {
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
        } else {
            return false;
        }
    }


    /**
     * Delete one or more Products from the database.
     *
     * Checks whether either of the request parameters 'id' (integer) or
     * 'selectedProductId' (array) is present, in that order, and takes the
     * ID of the Product(s) from the first one available, if any.
     * If none of them is set, uses the value of the $productId argument,
     * if that is valid.
     * Note that this method returns true if no record was deleted because
     * no ID was supplied.
     * @param   integer     $productId      The optional Product ID
     *                                      to be deleted.
     * @return  boolean                     True on success, false otherwise
     */
    function delProduct($productId=0)
    {
        $arrProductId = array();
        if (empty($productId)) {
            if (!empty($_REQUEST['id'])) {
                $arrProductId[] = $_REQUEST['id'];
            } elseif (!empty($_REQUEST['selectedProductId'])) {
                // This argument is an array!
                $arrProductId = $_REQUEST['selectedProductId'];
            }
        } else {
            $arrProductId[] = $productId;
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
        $shopProductId            =  0;
        $shopProductName          = '';
        $shopProductIdentifier    = '';
        $shopCatMenu              = '';
        $shopCustomerPrice        = 0;
        $shopResellerPrice        = 0;
        $shopSpecialOffer         = 0;
        $shopDiscount             = 0;
        $shopTaxId                = 0;
        // Used for either the weight or download account validity duration
        $shopWeight               = 0;
        $shopDistribution         = '';
        $shopShortDescription     = '';
        $shopDescription          = '';
        $shopStock                = 10;
        $shopStockVisibility      = 1;
        $shopManufacturerId       = 0;
        $shopManufacturerUrl      = '';
        $shopArticleActive        = 1;
        $shopB2B                  = 1;
        $shopB2C                  = 1;
        $shopStartdate            = '0000-00-00 00:00:00';
        $shopEnddate              = '0000-00-00 00:00:00';
        $shopImageName            = '';
        $shopUserGroupIds         = '';
//        $shopFlags = '';
        $shopGroupId   = 0;
        $shopArticleId = 0;
        $shopKeywords  = '';

// Is $shopTempThumbnailName, and its session equivalent,
// still in use anywhere?
//        if (isset($_SESSION['shopPM']['TempThumbnailName'])) {
//            $shopTempThumbnailName = $_SESSION['shopPM']['TempThumbnailName'];
//            unset($_SESSION['shopPM']['TempThumbnailName']);
//        }

        $shopProductId = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
        $objProduct = false;

        // Store Product data if form is sent
        if (isset($_POST['shopStoreProduct'])) {
            $shopProductName          = contrexx_stripslashes(strip_tags($_POST['shopProductName']));
            $shopProductIdentifier    = contrexx_stripslashes(strip_tags($_POST['shopProductIdentifier']));
            $shopCatMenu              = intval($_POST['shopCatMenu']);
            $shopCustomerPrice        = floatval($_POST['shopCustomerPrice']);
            $shopResellerPrice        = floatval($_POST['shopResellerPrice']);
            $shopSpecialOffer         =
                (isset($_POST['shopSpecialOffer']) ? 1 : 0);
            $shopDiscount             = floatval($_POST['shopDiscount']);
            $shopTaxId                = (isset($_POST['shopTaxId']) ? $_POST['shopTaxId'] : 0);
            $shopShortDescription     = trim(contrexx_stripslashes($_POST['shopShortDescription']));
            $shopDescription          = trim(contrexx_stripslashes($_POST['shopDescription']));
            // Workaround for FCKEditor bug that inserts a single <br /> in empty fields
            if (preg_match('/^\<br\s*\/?\>$/', $shopDescription))
                $shopDescription = '';
            $shopStock                = intval($_POST['shopStock']);
            $shopStockVisibility      =
                (isset($_POST['shopStockVisibility']) ? 1 : 0);
            $shopManufacturerUrl      = htmlspecialchars(strip_tags(contrexx_stripslashes($_POST['shopManufacturerUrl'])), ENT_QUOTES, CONTREXX_CHARSET);
            $shopArticleActive        =
                (isset($_POST['shopArticleActive']) ? 1 : 0);
            $shopB2B                  = isset($_POST['shopB2B']);
            $shopB2C                  = isset($_POST['shopB2C']);
            $shopStartdate            = !empty($_POST['shopStartdate']) ? contrexx_stripslashes($_POST['shopStartdate']) : '0000-00-00 00:00:00';
            $shopEnddate              = !empty($_POST['shopEnddate']) ? contrexx_stripslashes($_POST['shopEnddate']) : '0000-00-00 00:00:00';
            $shopManufacturerId       = intval($_POST['shopManufacturerId']);
// Currently not used on the detail page
//            $shopFlags                = (isset($_POST['shopFlags'])
//                    ? join(' ', $_POST['shopFlags']) : '');
            $shopDistribution         = $_POST['shopDistribution'];
            // Different meaning of the "weight" field for downloads!
            // The getWeight() method will treat purely numeric values
            // like the validity period (in days) the same as a weight
            // without its unit and simply return its integer value.
            $shopWeight               =
                ($shopDistribution == 'delivery'
                    ? Weight::getWeight($_POST['shopWeight'])
                    : $_POST['shopAccountValidity']
                );
            // Assigned frontend groups for protected downloads
            $shopUserGroupIds =
                (isset($_POST['shopGroupsAssigned'])
                  ? implode(',', $_POST['shopGroupsAssigned'])
                  : ''
                );
            $shopGroupId   = intval($_POST['shopDiscountGroupCount']);
            $shopArticleId = intval($_POST['shopDiscountGroupArticle']);
            $shopKeywords  = contrexx_addslashes($_POST['shopKeywords']);

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
            $shopImageName =
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
            if ($shopProductId == 0) {
                $objProduct = new Product(
                    $shopProductIdentifier,
                    $shopCatMenu,
                    $shopProductName,
                    $shopDistribution,
                    $shopCustomerPrice,
                    $shopArticleActive,
                    0,
                    $shopWeight
                );
                $objProduct->store();
                $shopProductId = $objProduct->getId();
            }

            // Apply the changes to all Products with the same Product code.
// Note: This is disabled for the time being, as virtual categories are.
//            if ($shopProductIdentifier != '') {
//                $arrProduct = Products::getByCustomId($shopProductIdentifier);
//            } else {
                $arrProduct = array($objProduct);
//            }
            if (!is_array($arrProduct)) return false;

            foreach ($arrProduct as $objProduct) {
                // Update product
                $objProduct = Product::getById($shopProductId);

                $objProduct->setCode($shopProductIdentifier);
// NOTE: Only change the parent ShopCategory for a Product
// that is in a real ShopCategory.
                $objProduct->setShopCategoryId($shopCatMenu);
                $objProduct->setName($shopProductName);
                $objProduct->setPrice($shopCustomerPrice);
                $objProduct->setStatus($shopArticleActive);
                $objProduct->setResellerPrice($shopResellerPrice);
                $objProduct->setSpecialOffer($shopSpecialOffer);
                $objProduct->setDiscountPrice($shopDiscount);
                $objProduct->setVatId($shopTaxId);
                $objProduct->setShortDesc($shopShortDescription);
                $objProduct->setDescription($shopDescription);
                $objProduct->setStock($shopStock);
                $objProduct->setStockVisible($shopStockVisibility);
                $objProduct->setExternalLink($shopManufacturerUrl);
                $objProduct->setB2B($shopB2B);
                $objProduct->setB2C($shopB2C);
                $objProduct->setStartDate($shopStartdate);
                $objProduct->setEndDate($shopEnddate);
                $objProduct->setManufacturerId($shopManufacturerId);
                $objProduct->setPictures($shopImageName);
                $objProduct->setDistribution($shopDistribution);
                $objProduct->setWeight($shopWeight);
// Currently not used on the detail page
//                $objProduct->setFlags($shopFlags);
                $objProduct->setUsergroups($shopUserGroupIds);
                $objProduct->setGroupCountId($shopGroupId);
                $objProduct->setGroupArticleId($shopArticleId);
                $objProduct->setKeywords($shopKeywords);

                // Remove old Product Attributes.
                // They are re-added below.
                $objProduct->clearAttributes();

                // Add new product attributes
                if (   isset($_POST['productOptionsValues'])
                    && is_array($_POST['productOptionsValues'])) {
                    foreach ($_POST['productOptionsValues'] as $valueId => $nameId) {
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
//                $shopProductIdentifier, $shopFlags
//            );

            if ($shopProductId > 0) {
                $_SESSION['shop']['strOkMessage'] = $_ARRAYLANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL'];
            } else {
                $_SESSION['shop']['strOkMessage'] = $_ARRAYLANG['TXT_DATA_RECORD_ADDED_SUCCESSFUL'];
            }

//            if (   !empty($shopTempThumbnailName)
//                && file_exists(ASCMS_SHOP_IMAGES_PATH.'/'.$shopTempThumbnailName)) {
//                @unlink(ASCMS_SHOP_IMAGES_PATH.'/'.$shopTempThumbnailName);
//            }

            $objImage = new ImageManager();
            $arrImages = Products::getShopImagesFromBase64String($shopImageName);
            // create thumbnails if not available
            foreach ($arrImages as $arrImage) {
                if (   !empty($arrImage['img'])
                    && $arrImage['img'] != self::noPictureName) {
                    if (!$objImage->_createThumbWhq(
                        ASCMS_SHOP_IMAGES_PATH.'/',
                        ASCMS_SHOP_IMAGES_WEB_PATH.'/',
                        $arrImage['img'],
                        $this->arrConfig['shop_thumbnail_max_width']['value'],
                        $this->arrConfig['shop_thumbnail_max_height']['value'],
                        $this->arrConfig['shop_thumbnail_quality']['value']
                    )) {
                        self::addError(sprintf($_ARRAYLANG['TXT_SHOP_COULD_NOT_CREATE_THUMBNAIL'], $arrImage['img']));
                    }
                }
            }

            switch ($_POST['shopAfterStoreAction']) {
                case 'newEmpty':
                    CSRF::header("Location: index.php?cmd=shop".MODULE_INDEX."&act=products&tpl=manage");
                    exit();
                case 'newTemplate':
                    CSRF::header("Location: index.php?cmd=shop".MODULE_INDEX."&act=products&tpl=manage&id=".
                        $objProduct->getId()."&new=1"
                    );
                    exit();
                default:
                    CSRF::header("Location: index.php?cmd=shop".MODULE_INDEX."&act=products");
                    // prevent further output, go back to product overview
                    exit();
            }
        }
        // set template
        self::$objTemplate->addBlockfile('SHOP_PRODUCTS_FILE', 'shop_products_block', 'module_shop_product_manage.html');

        // begin language variables
        self::$objTemplate->setVariable(array(
            'TXT_PRODUCT_ID' => $_ARRAYLANG['TXT_PRODUCT_ID'],
            'TXT_SHOP_PRODUCT_CUSTOM_ID' => $_ARRAYLANG['TXT_SHOP_PRODUCT_CUSTOM_ID'],
            'TXT_MANUFACTURER_URL' => $_ARRAYLANG['TXT_MANUFACTURER_URL'],
            'TXT_WITH_HTTP' => $_ARRAYLANG['TXT_WITH_HTTP'],
            'TXT_PRODUCT_INFORMATIONS' => $_ARRAYLANG['TXT_PRODUCT_INFORMATIONS'],
            'TXT_ADD_NEW' => $_ARRAYLANG['TXT_ADD_NEW'],
            'TXT_OVERWRITE' => $_ARRAYLANG['TXT_OVERWRITE'],
            'TXT_IMAGES_WITH_SAME_NAME' => $_ARRAYLANG['TXT_IMAGES_WITH_SAME_NAME'],
            'TXT_ACTION_AFTER_SAVEING' => $_ARRAYLANG['TXT_ACTION_AFTER_SAVEING'],
            'TXT_PRODUCT_CATALOG' => $_ARRAYLANG['TXT_PRODUCT_CATALOG'],
            'TXT_ADD_PRODUCTS' => $_ARRAYLANG['TXT_ADD_PRODUCTS'],
            'TXT_FROM_TEMPLATE' => $_ARRAYLANG['TXT_FROM_TEMPLATE'],
            'TXT_PRODUCT_NAME' => $_ARRAYLANG['TXT_PRODUCT_NAME'],
            'TXT_CUSTOMER_PRICE' => $_ARRAYLANG['TXT_CUSTOMER_PRICE'],
            'TXT_ID' => $_ARRAYLANG['TXT_ID'],
            'TXT_RESELLER_PRICE' => $_ARRAYLANG['TXT_RESELLER_PRICE'],
            'TXT_SHORT_DESCRIPTION' => $_ARRAYLANG['TXT_SHORT_DESCRIPTION'],
            'TXT_DESCRIPTION' => $_ARRAYLANG['TXT_DESCRIPTION'],
            'TXT_ACTIVE' => $_ARRAYLANG['TXT_ACTIVE'],
            'TXT_CATEGORY' => $_ARRAYLANG['TXT_CATEGORY'],
            'TXT_STOCK' => $_ARRAYLANG['TXT_STOCK'],
            'TXT_SPECIAL_OFFER' => $_ARRAYLANG['TXT_SPECIAL_OFFER'],
            'TXT_IMAGE_WIDTH' => $_ARRAYLANG['TXT_IMAGE_WIDTH'],
            'TXT_IMAGE' => $_ARRAYLANG['TXT_IMAGE'],
            'TXT_THUMBNAIL_SIZE' => $_ARRAYLANG['TXT_THUMBNAIL_SIZE'],
            'TXT_QUALITY' => $_ARRAYLANG['TXT_QUALITY'],
            'TXT_STORE' => $_ARRAYLANG['TXT_STORE'],
            'TXT_RESET' => $_ARRAYLANG['TXT_RESET'],
            'TXT_ENABLED_FILE_EXTENSIONS' => $_ARRAYLANG['TXT_ENABLED_FILE_EXTENSIONS'],
            'TXT_ACTIVE' => $_ARRAYLANG['TXT_ACTIVE'],
            'TXT_INACTIVE' => $_ARRAYLANG['TXT_INACTIVE'],
            'TXT_START_DATE' => $_ARRAYLANG['TXT_START_DATE'],
            'TXT_END_DATE' => $_ARRAYLANG['TXT_END_DATE'],
            'TXT_THUMBNAIL_SIZE' => $_ARRAYLANG['TXT_THUMBNAIL_SIZE'],
            'TXT_THUMBNAIL_PREVIEW' => $_ARRAYLANG['TXT_THUMBNAIL_PREVIEW'],
            'TXT_THUMBNAIL_SETTINGS' => $_ARRAYLANG['TXT_THUMBNAIL_SETTINGS'],
            'TXT_IMAGE_DIMENSION' => $_ARRAYLANG['TXT_IMAGE_DIMENSION'],
            'TXT_PRODUCT_STATUS' => $_ARRAYLANG['TXT_PRODUCT_STATUS'],
            'TXT_IMAGE_SIZE' => $_ARRAYLANG['TXT_IMAGE_SIZE'],
            'TXT_PIXEL' => $_ARRAYLANG['TXT_PIXEL'],
            'TXT_PRODUCT_IMAGE' => $_ARRAYLANG['TXT_PRODUCT_IMAGE'],
            'TXT_OPTIONS' => $_ARRAYLANG['TXT_OPTIONS'],
            'TXT_IMAGE_UPLOAD' => $_ARRAYLANG['TXT_IMAGE_UPLOAD'],
            'TXT_IMAGE_INFORMATIONS' => $_ARRAYLANG['TXT_IMAGE_INFORMATIONS'],
            'TXT_IMAGE_NAME' => $_ARRAYLANG['TXT_IMAGE_NAME'],
            'TXT_PRODUCT_OPTIONS' => $_ARRAYLANG['TXT_PRODUCT_OPTIONS'],
            'TXT_SHOP_EDIT_OR_ADD_IMAGE' => $_ARRAYLANG['TXT_SHOP_EDIT_OR_ADD_IMAGE'],
            'TXT_TAX_RATE' => $_ARRAYLANG['TXT_TAX_RATE'],
            'TXT_SHOP_MANUFACTURER' => $_ARRAYLANG['TXT_SHOP_MANUFACTURER'],
            'TXT_SHOP_SELECT_ALL' => $_ARRAYLANG['TXT_SHOP_SELECT_ALL'],
            'TXT_SHOP_DESELECT_ALL' => $_ARRAYLANG['TXT_SHOP_DESELECT_ALL'],
            'TXT_SHOP_PROTECTED_DOWNLOAD' => $_ARRAYLANG['TXT_SHOP_PROTECTED_DOWNLOAD'],
            'TXT_SHOP_TIP' => $_ARRAYLANG['TXT_SHOP_TIP'],
            'TXT_SHOP_PROTECTED_DOWNLOAD_TIP' => $_ARRAYLANG['TXT_SHOP_PROTECTED_DOWNLOAD_TIP'],
            'TXT_SHOP_PRODUCT_FRONTEND_GROUPS_AVAILABLE' => $_ARRAYLANG['TXT_SHOP_PRODUCT_FRONTEND_GROUPS_AVAILABLE'],
            'TXT_SHOP_PRODUCT_FRONTEND_GROUPS_ASSIGNED' => $_ARRAYLANG['TXT_SHOP_PRODUCT_FRONTEND_GROUPS_ASSIGNED'],
            'TXT_SHOP_YES' => $_ARRAYLANG['TXT_SHOP_YES'],
            'TXT_SHOP_NO' => $_ARRAYLANG['TXT_SHOP_NO'],
            'TXT_DISTRIBUTION' => $_ARRAYLANG['TXT_DISTRIBUTION'],
            'TXT_WEIGHT' => $_ARRAYLANG['TXT_WEIGHT'],
            // User groups for protected downloads
            'TXT_SHOP_USERGROUPS' => $_ARRAYLANG['TXT_SHOP_USERGROUPS'],
            'TXT_SHOP_ACCOUNT_VALIDITY' => $_ARRAYLANG['TXT_SHOP_ACCOUNT_VALIDITY'],
            'TXT_SHOP_GROUPS_AVAILABLE' => $_ARRAYLANG['TXT_SHOP_GROUPS_AVAILABLE'],
            'TXT_SHOP_GROUPS_ASSIGNED' => $_ARRAYLANG['TXT_SHOP_GROUPS_ASSIGNED'],
            'TXT_SHOP_DISCOUNT_GROUP_COUNT' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_GROUP_COUNT'],
            'TXT_SHOP_DISCOUNT_GROUP_ARTICLE' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_GROUP_ARTICLE'],
            'TXT_SHOP_KEYWORDS' => $_ARRAYLANG['TXT_SHOP_KEYWORDS'],
            'TXT_SHOP_DEL_ICON' => $_ARRAYLANG['TXT_SHOP_DEL_ICON'],
            // Assign Delete Symbol Path
            'SHOP_DELETE_ICON'  => ASCMS_PATH_OFFSET.'/cadmin/images/icons/delete.gif',
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
            $shopProductId = intval($_REQUEST['id']);
            $this->_getAttributeList($shopProductId);
        }

        // Edit product
        if ($shopProductId > 0) {
            $objProduct = Product::getById($shopProductId);
        }
        if (!$objProduct) {
            $objProduct = new Product('', 0, '', '', 0, 1, 0, 0);
        }

        // extract product image infos (path, width, height)
        $arrImages = Products::getShopImagesFromBase64String(
            $objProduct->getPictures()
        );

//        $shopFlagsSelection =
//            ShopCategories::getVirtualCategoriesSelectionForFlags(
//                $objProduct->getFlags()
//            );
//        if ($shopFlagsSelection) {
//            self::$objTemplate->setVariable(array(
//                'TXT_SHOP_FLAGS' => $_ARRAYLANG['TXT_SHOP_FLAGS'],
//                'SHOP_FLAGS_SELECTION' => $shopFlagsSelection,
//            ));
//        }

        // The distribution type (delivery, download, or none)
        $shopDistribution = $objProduct->getDistribution();

        // Available active frontend groups, and those assigned to the product
        $objFWUser = FWUser::getFWUserObject();
        $objGroup = $objFWUser->objGroup->getGroups(array('type' => 'frontend', 'is_active' => true), array('group_id' => 'asc'));
        $shopUserGroupIds = $objProduct->getUsergroups();
        $arrAssignedFrontendGroupId = explode(',', $shopUserGroupIds);
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
        $shopGroupId   = $objProduct->getGroupCountId();
        $shopArticleId = $objProduct->getGroupArticleId();
        $shopKeywords  = $objProduct->getKeywords();
        self::$objTemplate->setVariable(array(
            'SHOP_PRODUCT_ID' => (isset($_REQUEST['new']) ? 0 : $objProduct->getId()),
            'SHOP_PRODUCT_CUSTOM_ID' => $objProduct->getCode(),
            'SHOP_DATE' => date('Y-m-d H:m'),
            'SHOP_PRODUCT_NAME' => $objProduct->getName(),
            'SHOP_CAT_MENUOPTIONS' => ShopCategories::getShopCategoriesMenuoptions(
                    $objProduct->getShopCategoryId(), false
                ),
            'SHOP_CUSTOMER_PRICE' => Currency::formatPrice($objProduct->getPrice()),
            'SHOP_RESELLER_PRICE' => Currency::formatPrice($objProduct->getResellerPrice()),
            'SHOP_DISCOUNT' => Currency::formatPrice($objProduct->getDiscountPrice()),
            'SHOP_SPECIAL_OFFER' => ($objProduct->isSpecialOffer() ? ' checked="checked"' : ''),
            'SHOP_VAT_MENUOPTIONS' => Vat::getMenuoptions(
                    $objProduct->getVatId(), true
                ),
            'SHOP_SHORT_DESCRIPTION' => get_wysiwyg_editor(
                    'shopShortDescription',
                    $objProduct->getShortDesc(),
                    'shop'
                ),
            'SHOP_DESCRIPTION' => get_wysiwyg_editor(
                    'shopDescription',
                    $objProduct->getDescription(),
                    'shop'
                ),
            'SHOP_STOCK' => $objProduct->getStock(),
            'SHOP_MANUFACTURER_URL' => htmlentities(
                    $objProduct->getExternalLink(),
                    ENT_QUOTES, CONTREXX_CHARSET
                ),
            'SHOP_STARTDATE' => $objProduct->getStartDate(),
            'SHOP_ENDDATE' => $objProduct->getEndDate(),
            'SHOP_ARTICLE_ACTIVE' => ($objProduct->getStatus() ? ' checked="checked"' : ''),
            'SHOP_B2B' => ($objProduct->isB2B() ? ' checked="checked"' : ''),
            'SHOP_B2C' => ($objProduct->isB2C() ? ' checked="checked"' : ''),
            'SHOP_STOCK_VISIBILITY' => ($objProduct->isStockVisible() ? ' checked="checked"' : ''),
            'SHOP_MANUFACTURER_MENUOPTIONS' =>
                Manufacturer::getMenuoptions($objProduct->getManufacturerId()),
            'SHOP_PICTURE1_IMG_SRC' =>
                (   !empty($arrImages[1]['img'])
                 && is_file(ASCMS_SHOP_IMAGES_PATH.'/'.
                        ImageManager::getThumbnailFilename($arrImages[1]['img']))
                    ? contrexx_raw2encodedUrl(ASCMS_SHOP_IMAGES_WEB_PATH.'/'.
                          ImageManager::getThumbnailFilename($arrImages[1]['img']))
                    : self::$defaultImage
                ),
            'SHOP_PICTURE2_IMG_SRC' =>
                (   !empty($arrImages[2]['img'])
                 && is_file(ASCMS_SHOP_IMAGES_PATH.'/'.
                        ImageManager::getThumbnailFilename($arrImages[2]['img']))
                    ? contrexx_raw2encodedUrl(ASCMS_SHOP_IMAGES_WEB_PATH.'/'.
                      ImageManager::getThumbnailFilename($arrImages[2]['img']))
                    : self::$defaultImage
                ),
            'SHOP_PICTURE3_IMG_SRC' =>
                (   !empty($arrImages[3]['img'])
                 && is_file(ASCMS_SHOP_IMAGES_PATH.'/'.
                        ImageManager::getThumbnailFilename($arrImages[3]['img']))
                    ? contrexx_raw2encodedUrl(ASCMS_SHOP_IMAGES_WEB_PATH.'/'.
                      ImageManager::getThumbnailFilename($arrImages[3]['img']))
                    : self::$defaultImage
                ),
            'SHOP_PICTURE1_IMG_SRC_NO_THUMB' => (!empty($arrImages[1]['img']) && is_file(ASCMS_SHOP_IMAGES_PATH.'/'.$arrImages[1]['img'])
                    ? ASCMS_SHOP_IMAGES_WEB_PATH.'/'.$arrImages[1]['img']
                    : self::$defaultImage
                ),
            'SHOP_PICTURE2_IMG_SRC_NO_THUMB' => (!empty($arrImages[2]['img']) && is_file(ASCMS_SHOP_IMAGES_PATH.'/'.$arrImages[2]['img'])
                    ? ASCMS_SHOP_IMAGES_WEB_PATH.'/'.$arrImages[2]['img']
                    : self::$defaultImage
                ),
            'SHOP_PICTURE3_IMG_SRC_NO_THUMB' => (!empty($arrImages[3]['img']) && is_file(ASCMS_SHOP_IMAGES_PATH.'/'.$arrImages[3]['img'])
                    ? ASCMS_SHOP_IMAGES_WEB_PATH.'/'.$arrImages[3]['img']
                    : self::$defaultImage
                ),
            'SHOP_PICTURE1_IMG_WIDTH' => $arrImages[1]['width'],
            'SHOP_PICTURE1_IMG_HEIGHT' => $arrImages[1]['height'],
            'SHOP_PICTURE2_IMG_WIDTH' => $arrImages[2]['width'],
            'SHOP_PICTURE2_IMG_HEIGHT' => $arrImages[2]['height'],
            'SHOP_PICTURE3_IMG_WIDTH' => $arrImages[3]['width'],
            'SHOP_PICTURE3_IMG_HEIGHT' => $arrImages[3]['height'],
            'SHOP_DISTRIBUTION_MENU' => Distribution::getDistributionMenu(
                    $objProduct->getDistribution(),
                    'shopDistribution',
                    'distributionChanged();',
                    'style="width: 220px"'
                ),
            'SHOP_WEIGHT' => ($shopDistribution != 'delivery'
                    ? '0 g'
                    : Weight::getWeightString($objProduct->getWeight())
                ),
            // User group menu, returns 'userGroupId'
            'SHOP_GROUPS_AVAILABLE' => $strActiveFrontendGroupOptions,
            'SHOP_GROUPS_ASSIGNED' => $strAssignedFrontendGroupOptions,
            'SHOP_ACCOUNT_VALIDITY_OPTIONS' => FWUser::getValidityMenuOptions(
                    ($shopDistribution == 'download'
                        ? $objProduct->getWeight()
                        : 0
                    )
                ),
            'SHOP_CREATE_ACCOUNT_YES_CHECKED' => (empty($shopUserGroupIds) ? '' : ' checked="checked"'),
            'SHOP_CREATE_ACCOUNT_NO_CHECKED' => (empty($shopUserGroupIds) ? ' checked="checked"' : ''),
            'SHOP_DISCOUNT_GROUP_COUNT_MENU_OPTIONS' => Discount::getMenuOptionsGroupCount($shopGroupId),
            'SHOP_DISCOUNT_GROUP_ARTICLE_MENU_OPTIONS' => Discount::getMenuOptionsGroupArticle($shopArticleId),
            'SHOP_KEYWORDS' => $shopKeywords,
        ));
        // Show the weight row if the corresponding setting is enabled
        self::$objTemplate->setVariable(
            'SHOP_WEIGHT_ENABLED', $this->arrConfig['shop_weight_enable']['value']
        );
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
        $shopSearchPattern = '';
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
        if (   !empty($_GET['shopSendMail'])
            && !empty($_GET['orderId'])) {
            $result = shopmanager::sendConfirmationMail($_GET['orderId']);
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
        $shopCustomerOrderField = 'order_date';
        $shopCustomerOrder = $shopCustomerOrderField;
        $shopOrderStatus = -1;
        $shopCustomerType = -1;
        $shopListLetter = '';
        $shopSearchTerm = '';
        $shopShowPendingOrders = '';
        if (!empty($_REQUEST['shopSearchTerm'])) {
            $shopSearchTerm = htmlspecialchars(
                $_REQUEST['shopSearchTerm'], ENT_QUOTES, CONTREXX_CHARSET
            );
            // Check if the user wants to search the pseudo "account names".
            // These may be customized with pre- or postfixes.
            // Adapt the regex as needed.
//            $arrMatch = array();
            $shopSearchAccount = '';
//                (preg_match('/^A-(\d{1,2})-?8?(\d{0,2})?/i', $shopSearchTerm, $arrMatch)
//                    ? "OR (    order_date LIKE '__".$arrMatch[1]."%'
//                           AND orderid LIKE '%".$arrMatch[2]."')"
//                    : ''
//                );
            $shopSearchPattern .=
                " AND (company LIKE '%$shopSearchTerm%'
                    OR firstname LIKE '%$shopSearchTerm%'
                    OR lastname LIKE '%$shopSearchTerm%'
                    OR address LIKE '%$shopSearchTerm%'
                    OR city LIKE '%$shopSearchTerm%'
                    OR phone LIKE '%$shopSearchTerm%'
                    OR email LIKE '%$shopSearchTerm%'
                    $shopSearchAccount)";
        }
        if (isset($_REQUEST['shopCustomerType'])) {
            $shopCustomerType = intval($_REQUEST['shopCustomerType']);
            if ($shopCustomerType == 0 || $shopCustomerType == 1) {
                $shopSearchPattern .= " AND is_reseller=$shopCustomerType";
            }
        }
        if (isset($_REQUEST['shopOrderStatus'])) {
            $shopOrderStatus = $_REQUEST['shopOrderStatus'];
            if (   is_numeric($shopOrderStatus)
                && $_REQUEST['shopOrderStatus'] >= 0
                && $_REQUEST['shopOrderStatus'] <= SHOP_ORDER_STATUS_COUNT) {
                $shopOrderStatus = intval($_REQUEST['shopOrderStatus']);
                $shopSearchPattern .= " AND order_status='$shopOrderStatus'";
                // Check "Show pending orders" as well if these are selected
                if ($shopOrderStatus == SHOP_ORDER_STATUS_PENDING) {
                    $_REQUEST['shopShowPendingOrders'] = 1;
                }
            } else {
                // Ignore.
                $shopOrderStatus = '';
            }
        }
        if (isset($_REQUEST['shopListSort'])) {
            $shopCustomerOrderField =
                addslashes(strip_tags($_REQUEST['shopListSort']));
            $shopCustomerOrder = $shopCustomerOrderField;
        }
        // let the user choose whether to see pending orders or not
        if (!isset($_REQUEST['shopShowPendingOrders'])) {
            $shopSearchPattern .=
                ' AND order_status!='.SHOP_ORDER_STATUS_PENDING;
        } else {
            self::$objTemplate->setVariable(
                'SHOP_SHOW_PENDING_ORDERS_CHECKED', ' checked="checked"'
            );
        $shopShowPendingOrders = 1;
        }
        if (!empty($_REQUEST['shopListLetter'])) {
            $shopListLetter = htmlspecialchars(
                $_REQUEST['shopListLetter'], ENT_QUOTES, CONTREXX_CHARSET
            );
            $shopListSort = addslashes(strip_tags($_REQUEST['shopListSort']));
            $shopSearchPattern .= " AND LEFT($shopListSort, 1)='$shopListLetter'";
        }

        self::$objTemplate->setVariable(array(
            'TXT_CUSTOMER_TYP' => $_ARRAYLANG['TXT_CUSTOMER_TYP'],
            'TXT_CUSTOMER' => $_ARRAYLANG['TXT_CUSTOMER'],
            'TXT_RESELLER' => $_ARRAYLANG['TXT_RESELLER'],
            'TXT_FIRST_NAME' => $_ARRAYLANG['TXT_FIRST_NAME'],
            'TXT_LAST_NAME' => $_ARRAYLANG['TXT_LAST_NAME'],
            'TXT_COMPANY' => $_ARRAYLANG['TXT_COMPANY'],
            'TXT_SORT_ORDER' => $_ARRAYLANG['TXT_SORT_ORDER'],
            'TXT_ID' => $_ARRAYLANG['TXT_ID'],
            'TXT_DATE' => $_ARRAYLANG['TXT_DATE'],
            'TXT_NAME' => $_ARRAYLANG['TXT_NAME'],
            'TXT_ORDER_SUM' => $_ARRAYLANG['TXT_ORDER_SUM'],
            'TXT_ACTION' => $_ARRAYLANG['TXT_ACTION'],
            'TXT_CONFIRM_DELETE_ORDER' => $_ARRAYLANG['TXT_CONFIRM_DELETE_ORDER'],
            'TXT_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
            'TXT_CONFIRM_CHANGE_STATUS' => $_ARRAYLANG['TXT_CONFIRM_CHANGE_STATUS'],
            'TXT_SEARCH' => $_ARRAYLANG['TXT_SEARCH'],
            'TXT_SEND_TEMPLATE_TO_CUSTOMER' => str_replace('TXT_ORDER_COMPLETE',
                $_ARRAYLANG['TXT_ORDER_COMPLETE'],
                $_ARRAYLANG['TXT_SEND_TEMPLATE_TO_CUSTOMER']
            ),
            'TXT_MARKED' => $_ARRAYLANG['TXT_MARKED'],
            'TXT_SELECT_ALL' => $_ARRAYLANG['TXT_SELECT_ALL'],
            'TXT_REMOVE_SELECTION' => $_ARRAYLANG['TXT_REMOVE_SELECTION'],
            'TXT_SELECT_ACTION' => $_ARRAYLANG['TXT_SELECT_ACTION'],
            'TXT_MAKE_SELECTION' => $_ARRAYLANG['TXT_MAKE_SELECTION'],
            'TXT_SHOP_SHOW_PENDING_ORDERS' => $_ARRAYLANG['TXT_SHOP_SHOW_PENDING_ORDERS'],
            'SHOP_SEARCH_TERM' => $shopSearchTerm,
//            'SHOP_ORDER_STATUS_MENU' =>
//                $this->getOrderStatusMenu($shopOrderStatus),
            'SHOP_ORDER_STATUS_MENUOPTIONS' => $this->getOrderStatusMenuoptions($shopOrderStatus, true),
            'SHOP_CUSTOMER_TYPE_MENUOPTIONS' => Customers::getCustomerTypeMenuoptions($shopCustomerType),
            'SHOP_CUSTOMER_SORT_MENUOPTIONS' => Customers::getCustomerSortMenuoptions($shopCustomerOrderField),
            // Protected download user account validity
            'TXT_SHOP_VALIDITY' => $_ARRAYLANG['TXT_SHOP_VALIDITY'],
        ));
        self::$objTemplate->setGlobalVariable(array(
            'TXT_STATUS' => $_ARRAYLANG['TXT_STATUS'],
            'TXT_VIEW_DETAILS' => $_ARRAYLANG['TXT_VIEW_DETAILS'],
            'TXT_EDIT' => $_ARRAYLANG['TXT_EDIT'],
            'TXT_DELETE' => $_ARRAYLANG['TXT_DELETE'],
            'SHOP_CURRENCY' => Currency::getDefaultCurrencySymbol(),
        ));

        // Create SQL query
        $query = "
            SELECT orderid, firstname, lastname, company,
                   currency_order_sum, selected_currency_id,
                   order_date, customer_note, order_status
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_customers c,
                   ".DBPREFIX."module_shop".MODULE_INDEX."_orders o
             WHERE c.customerid=o.customerid
                   $shopSearchPattern
          ORDER BY $shopCustomerOrder DESC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            $this->errorHandling();
        } else {
            $pos = (isset($_GET['pos']) ? intval($_GET['pos']) : 0);
            $count = $objResult->RecordCount();
            $shopPagingLimit = intval($_CONFIG['corePagingLimit']);
            $viewPaging = $count > $shopPagingLimit ? true : false;
            $paging = getPaging(
                $count,
                $pos,
                '&amp;cmd=shop'.MODULE_INDEX.'&amp;act=orders'.
                  ($shopSearchTerm ? '&amp;shopSearchTerm='.$shopSearchTerm : '').
                  ($shopListLetter ? '&amp;shopListLetter='.$shopListLetter : '').
                  ($shopCustomerOrder != 'customerid' ? '&amp;shopListSort='.$shopCustomerOrder : '').
                  ($shopCustomerType>-1 ? '&amp;shopCustomerType='.$shopCustomerType : '').
                  ($shopOrderStatus ? '&amp;shopOrderStatus='.$shopOrderStatus : '').
                  ($shopShowPendingOrders ? '&amp;shopShowPendingOrders='.$shopShowPendingOrders : ''),
                $_ARRAYLANG['TXT_ORDERS'],
                $viewPaging
            );
            self::$objTemplate->setVariable(array(
                'SHOP_ORDER_PAGING' => $paging,
                'SHOP_CUSTOMER_LISTLETTER' => $shopListLetter,
 //                'SHOP_LISTLETTER_MENUOPTIONS' => self::getListletterMenuoptions,
            ));
        }
        $objResult = $objDatabase->SelectLimit($query, $shopPagingLimit, $pos);
        if (!$objResult) {
            // if query has errors, call errorhandling
            $this->errorHandling();
        } else {
            if ($objResult->RecordCount() == 0) {
                self::$objTemplate->hideBlock('orderTable');
            } else {
                self::$objTemplate->setCurrentBlock('orderRow');
                while (!$objResult->EOF) {
                    $orderId = $objResult->fields['orderid'];
                    // Custom order ID may be created and used as account name.
                    // Adapt the method as needed.
                    $customOrderId = ShopLibrary::getCustomOrderId(
                        $orderId,
                        $objResult->fields['order_date']
                    );
                    $orderStatus = $objResult->fields['order_status'];
                    // Pick user account by the same name
                    $query = "
                        SELECT * FROM `".DBPREFIX."access_users`
                         WHERE username LIKE '$customOrderId-%'
                    ";
                    $objResultAccount = $objDatabase->Execute($query);
                    if (!$objResultAccount) {
                        $this->errorHandling();
                    }
                    // Determine end date
                    $endDate =
                        ($objResultAccount->fields['expiration'] > 0 ? date('d.m.Y', $objResultAccount->fields['expiration']) : '-');

                    // PHP5! $tipNote = (strlen($objResult['customer_note'])>0) ? php_strip_whitespace($objResult['customer_note']) : '';
                    $tipNote = $objResult->fields['customer_note'];
                    $tipLink = (!empty($tipNote)
                        ? '<img src="images/icons/comment.gif" onmouseout="htm()" onmouseover="stm(Text['.
                          $objResult->fields['orderid'].'],Style[0])" width="11" height="10" alt="" title="" />'
                        : ''
                    );
                    $orderId = $objResult->fields['orderid'];
                    $orderStatus = $objResult->fields['order_status'];
                    self::$objTemplate->setVariable(array(
                        'SHOP_ROWCLASS' => ($orderStatus == 0
                                ? 'rowWarn'
                                : (++$i % 2 ? 'row1' : 'row2')
                            ),
                        'SHOP_ORDERID' => $orderId,
                        'SHOP_TIP_ID' => $orderId,
                        'SHOP_TIP_NOTE' => ereg_replace(
                                "\r\n|\n|\r", '<br />', htmlentities(strip_tags($tipNote), ENT_QUOTES, CONTREXX_CHARSET)
                            ),
                        'SHOP_TIP_LINK' => $tipLink,
                        'SHOP_DATE' => $objResult->fields['order_date'],
                        'SHOP_NAME' => strlen($objResult->fields['company']) > 1
                                ? trim($objResult->fields['company'])
                                : $objResult->fields['firstname'].' '.
                                  $objResult->fields['lastname'],
                        'SHOP_ORDER_SUM' => Currency::getDefaultCurrencyPrice(
                                $objResult->fields['currency_order_sum']),
                        'SHOP_ORDER_STATUS' => $this->getOrderStatusMenu(
                            intval($orderStatus),
                            'shopOrderStatusId['.$orderId.']',
                            'changeOrderStatus('.
                                $orderId.','.
                                $orderStatus.
                                ', this.value)'
                        ),
                        // Protected download account validity end date
                        'SHOP_VALIDITY' => $endDate,
                    ));
                    self::$objTemplate->parse('orderRow');
                    self::$objTemplate->parse('tipMessageRow');
                    $objResult->MoveNext();
                }
            }
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
        $shopCurrencyOrderSum = 0;
        // recalculated VAT total
        $total_vat_amount = 0;

        // set template -- may be one of
        //  'module_shop_order_details.html'
        //  'module_shop_order_edit.html'
        self::$objTemplate->loadTemplateFile($templateName, true, true);

        $shopOrderId = intval($_REQUEST['orderid']);

        // lsv data
        $query = "
            SELECT * FROM ".DBPREFIX."module_shop_lsv
             WHERE order_id=$shopOrderId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            $this->errorHandling();
        }
        if ($objResult->RecordCount() == 1) {
            self::$objTemplate->hideBlock('creditCard');
            self::$objTemplate->setVariable(array(
                'TXT_ACCOUNT_HOLDER' => $_ARRAYLANG['TXT_ACCOUNT_HOLDER'],
                'TXT_ACCOUNT_BANK' => $_ARRAYLANG['TXT_ACCOUNT_BANK'],
                'TXT_ACCOUNT_BLZ' => $_ARRAYLANG['TXT_ACCOUNT_BLZ'],
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
                   c.company_note, c.email, c.is_reseller,
                   group_id
              FROM ".DBPREFIX."module_shop_customers AS c,
                   ".DBPREFIX."module_shop_orders AS o
             WHERE c.customerid=o.customerid
               AND o.orderid=$shopOrderId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            $this->errorHandling();
        } else  {
            // set the customer and order data, if found
            if (!$objResult->EOF) {
                $selectedCurrencyId                  = $objResult->fields['selected_currency_id'];
                $shopCurrencyOrderSum                = $objResult->fields['currency_order_sum'];
//                $shopShippingPrice                   = $objResult->fields['currency_ship_price'];
                $shopMailTo                          = $objResult->fields['email'];
                $shopLastModified                    = $objResult->fields['last_modified'];
                $countryId                           = $objResult->fields['country_id'];
                $shippingId                          = $objResult->fields['shipping_id'];
                $paymentId                           = $objResult->fields['payment_id'];
                $isReseller                          = $objResult->fields['is_reseller'];
                $ship_to_country_id = $objResult->fields['ship_country_id'];
                $orderStatus                         = $objResult->fields['order_status'];
                $shipperName                         = Shipment::getShipperName($shippingId);
                $groupCustomerId             = $objResult->fields['group_id'];
                Vat::isReseller($isReseller);
                Vat::setIsHomeCountry(
                       empty($this->arrConfig['country_id']['value'])
                    || $this->arrConfig['country_id']['value'] == $ship_to_country_id
                );
                self::$objTemplate->setVariable(array(
                    'SHOP_CUSTOMER_ID' => $objResult->fields['customerid' ],
                    'SHOP_ORDERID' => $objResult->fields['orderid'],
                    'SHOP_DATE' => $objResult->fields['order_date'],
                    'SHOP_ORDER_STATUS' => ($type == 1
                        ? $this->getOrderStatusMenu(
                            $orderStatus,
                            'shopOrderStatusId',
                            'swapSendToStatus(this.value)'
                          )
                        : $_ARRAYLANG['TXT_SHOP_ORDER_STATUS_'.$orderStatus]),
                    'SHOP_SEND_MAIL_STYLE' => ($orderStatus == SHOP_ORDER_STATUS_CONFIRMED
                            ? 'display: inline;'
                            : 'display: none;'
                        ),
                    'SHOP_SEND_MAIL_STATUS' => ($type == 1
                            ? ($orderStatus != SHOP_ORDER_STATUS_CONFIRMED
                                ? ' checked="checked"'
                                : ''
                              )
                            : ''
                        ),
                    'SHOP_ORDER_SUM' => Currency::getDefaultCurrencyPrice($shopCurrencyOrderSum),
                    'SHOP_DEFAULT_CURRENCY' => Currency::getDefaultCurrencySymbol(),
                    'SHOP_PREFIX' => $objResult->fields['prefix'],
                    'SHOP_COMPANY' => $objResult->fields['company'],
                    'SHOP_FIRSTNAME' => $objResult->fields['firstname'],
                    'SHOP_LASTNAME' => $objResult->fields['lastname'],
                    'SHOP_ADDRESS' => $objResult->fields['address'],
                    'SHOP_ZIP' => $objResult->fields['zip'],
                    'SHOP_CITY' => $objResult->fields['city'],
                    'SHOP_COUNTRY' => Country::getNameById($countryId),
                    'SHOP_SHIP_PREFIX' => $objResult->fields['ship_prefix'],
                    'SHOP_SHIP_COMPANY' => $objResult->fields['ship_company'],
                    'SHOP_SHIP_FIRSTNAME' => $objResult->fields['ship_firstname'],
                    'SHOP_SHIP_LASTNAME' => $objResult->fields['ship_lastname'],
                    'SHOP_SHIP_ADDRESS' => $objResult->fields['ship_address'],
                    'SHOP_SHIP_ZIP' => $objResult->fields['ship_zip'],
                    'SHOP_SHIP_CITY' => $objResult->fields['ship_city'],
                    'SHOP_SHIP_COUNTRY' => ($type == 1
                            ? $this->_getCountriesMenu('shopShipCountry', $ship_to_country_id)
                            : Country::getNameById($ship_to_country_id)
                        ),
                    'SHOP_SHIP_PHONE' => $objResult->fields['ship_phone'],
                    'SHOP_PHONE' => $objResult->fields['phone'],
                    'SHOP_FAX' => $objResult->fields['fax'],
                    'SHOP_EMAIL' => $shopMailTo,
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
                    'SHOP_LAST_MODIFIED' => ($shopLastModified == 0 ? $_ARRAYLANG['TXT_ORDER_WASNT_YET_EDITED'] : $shopLastModified.'&nbsp;'.$_ARRAYLANG['TXT_EDITED_BY'].'&nbsp;'.$objResult->fields['modified_by']),
                    'SHOP_SHIPPING_TYPE' => $shipperName,
                ));

                // set shipment price or remove it from the details overview if empty
                if ($objResult->fields['currency_ship_price'] != 0) {
                    self::$objTemplate->setVariable(array('SHOP_SHIPPING_PRICE' => $objResult->fields['currency_ship_price']));
                } else {
//                    if ($type != 1) {
//                        self::$objTemplate->hideBlock('shopShipmentPrice');
//                    } else {
                        self::$objTemplate->setVariable(array('SHOP_SHIPPING_PRICE' => '0.00'));
//                    }
                }

                // set payment price or remove it from the details overview if empty
                if ($objResult->fields['currency_payment_price'] != 0) {
                    self::$objTemplate->setVariable(array('SHOP_PAYMENT_PRICE' => $objResult->fields['currency_payment_price']));
                } else {
//                    if ($type != 1) {
//                        self::$objTemplate->hideBlock('shopPaymentPrice');
//                    } else {
                        self::$objTemplate->setVariable(array('SHOP_PAYMENT_PRICE' => '0.00'));
//                    }
                }

                self::$objTemplate->setGlobalVariable(array(
                    'SHOP_CURRENCY' => Currency::getCurrencySymbolById($selectedCurrencyId)
                ));

                // set the handler of the payment method
                $psp_id = Payment::getPaymentProcessorId($paymentId);
                $ppName = PaymentProcessing::getPaymentProcessorName($psp_id);
                if ($ppName) {
                    self::$objTemplate->setVariable(array('SHOP_PAYMENT_HANDLER' => $ppName));
                } else {
                    $this->errorHandling();
                }

                // set last modified date of the order
                self::$objTemplate->setVariable(array(
                    'SHOP_LAST_MODIFIED_DATE' => $shopLastModified
                ));
            }

            if ($type == 1) {
                // edit order
                // set language vars
                self::$objTemplate->setVariable(array(
                    'TXT_PRODUCT_ALREADY_PRESENT' => $_ARRAYLANG['TXT_PRODUCT_ALREADY_PRESENT'],
                    'TXT_SEND_TEMPLATE_TO_CUSTOMER' => str_replace('TXT_ORDER_COMPLETE', $_ARRAYLANG['TXT_ORDER_COMPLETE'], $_ARRAYLANG['TXT_SEND_TEMPLATE_TO_CUSTOMER']),
                ));

                // shipper menu and javascript array
                $strJsArrShipment = Shipment::getJSArrays();
                self::$objTemplate->setVariable(array(
                    'SHOP_SHIPPING_TYP_MENU' => Shipment::getShipperMenu(
                        $objResult->fields['ship_country_id'],
                        $objResult->fields['shipping_id'],
                        "javascript:calcPrice(0)"),
                    'SHOP_JS_ARR_SHIPMENT' => $strJsArrShipment
                ));

                // set products menu and javascript array
                $query = '
                    SELECT id, product_id, title,
                        resellerprice, normalprice, discountprice, is_special_offer,
                        weight, vat_id, handler,
                        group_id, article_id
                    FROM '.DBPREFIX.'module_shop'.MODULE_INDEX.'_products
                    WHERE status=1
                ';
                $objResult = $objDatabase->Execute($query);
                if (!$objResult) {
                    $this->errorHandling();
                } else {
                    while (!$objResult->EOF) {
                        $shopDistribution = $objResult->fields['handler'];
                        $arrProducts[$objResult->fields['id']] = array(
                            'id' => $objResult->fields['id'],
                            'code' => $objResult->fields['product_id'],
                            'title' => $objResult->fields['title'],
                            'resellerprice' => $objResult->fields['resellerprice'],
                            'normalprice' => $objResult->fields['normalprice'],
                            'discountprice' => $objResult->fields['discountprice'],
                            'is_special_offer' => $objResult->fields['is_special_offer'],
                            // Store VAT as percentage, not ID, as we will only update the order items
                            'percent' => Vat::getRate($objResult->fields['vat_id']),
                            'weight' => ($shopDistribution == 'delivery'
                                  ? Weight::getWeightString($objResult->fields['weight'])
                                  : '0'
                                ),
                            'group_id' => $objResult->fields['group_id'],
                            'article_id' => $objResult->fields['article_id'],
                        );
                        $objResult->MoveNext();
                    }
                    // create javascript array containing all products;
                    // used to update the display when changing the product ID.
                    // we need the VAT rate in there as well in order to be able to correctly change the products,
                    // and the flag indicating whether the VAT is included in the prices already.
                    $strJsArrProduct =
                        'var vat_included = '.intval(Vat::isIncluded()).
                        ";\nvar arrProducts = new Array();\n";
                    $menu = '';
                    foreach ($arrProducts as $arrProduct) {
                        // the menu for a new product - no preselected value
                        $menu .= "<option value='".$arrProduct['id']."'>".$arrProduct['id']."</option>\n";
                        $strJsArrProduct .=
                            "arrProducts[".$arrProduct['id']."] = new Array();\n".
                            "arrProducts[".$arrProduct['id']."]['id'] = ".$arrProduct['id'].";\n".
                            "arrProducts[".$arrProduct['id']."]['code'] = '".$arrProduct['code']."';\n".
                            "arrProducts[".$arrProduct['id']."]['title'] = '".addslashes($arrProduct['title'])."';\n".
                            "arrProducts[".$arrProduct['id']."]['percent'] = '".$arrProduct['percent']."';\n".
                            "arrProducts[".$arrProduct['id']."]['weight'] = '".$arrProduct['weight']."';\n".
                            "arrProducts[".$arrProduct['id']."]['group_id'] = '".$arrProduct['group_id']."';\n".
                            "arrProducts[".$arrProduct['id']."]['article_id'] = '".$arrProduct['article_id']."';\n";
                        $price = $arrProduct['normalprice'];
                        if ($arrProducts[$arrProduct['id']]['is_special_offer']) {
                            $price = $arrProduct['discountprice'];
                        } elseif ($isReseller) {
                            $price = $arrProduct['resellerprice'];
                        }
                        // Determine discounted price from customer and article group matrix
                        $discountCustomerRate = Discount::getDiscountRateCustomer(
                            $groupCustomerId, $arrProduct['article_id']
                        );
                        $price -= $price * $discountCustomerRate * 0.01;
                        // Determine prices for various count discounts, if any
                        $arrDiscountCountRate = Discount::getDiscountCountRateArray($arrProduct['group_id']);
                        $strJsArrProduct .=
                            "arrProducts[".$arrProduct['id']."]['price'] = new Array();\n";
                        // Order the counts in reverse, from highest to lowest
                        foreach (array_reverse($arrDiscountCountRate, true) as $count => $rate) {
                            // Deduct the customer type discount right away
                            $discountPrice = $price - ($price * $rate * 0.01);
                            $strJsArrProduct .=
                                "arrProducts[".$arrProduct['id']."]['price'][$count] = '".
                                Currency::getCurrencyPrice($discountPrice)."';\n";
                        }
                    }
                }
                self::$objTemplate->setVariable(array(
                    'SHOP_PRODUCT_IDS_MENU_NEW' => $menu,
                    'SHOP_JS_ARR_PRODUCT' => $strJsArrProduct
                ));
            } // if ($type == 1)
        }

        // get product options
        $query = "SELECT order_items_id, product_option_name, product_option_value ".
            "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items_attributes ".
            "WHERE order_id=".$shopOrderId;
        $arrProductOptions = array();
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            $this->errorHandling();
        } else {
            while (!$objResult->EOF) {
                if (!isset($arrProductOptions[$objResult->fields['order_items_id']]['options'])) {
                    $arrProductOptions[$objResult->fields['order_items_id']]['options'] = array();
                }
                $optionName = $objResult->fields['product_option_name'];
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
                    $optionName.": ".$optionValue
                );
                $objResult->MoveNext();
            }
        }

        // set up the order details
        $query = "
            SELECT order_items_id, product_name, productid, price, quantity,
                   vat_percent, weight
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items
             WHERE orderid=$shopOrderId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            $this->errorHandling();
        } else {
            self::$objTemplate->setCurrentBlock('orderdetailsRow');
            // modulo counter
            $i = 0;
            // reset totals
            $total_weight     = 0;
            $total_vat_amount = 0;
            $total_net_price  = 0;

            // products loop
            while (!$objResult->EOF) {
                if ($type == 1) {
                    $productName = $objResult->fields['product_name'];
                } else {
                    $productName = $objResult->fields['product_name'];
                    if (isset($arrProductOptions[$objResult->fields['order_items_id']])) {
                        $productName .=
                            '<i><br />- '.implode('<br />- ', $arrProductOptions[$objResult->fields['order_items_id']]['options']).'</i>';
                    }
                }

                $productId = $objResult->fields['productid'];
                // Get missing product details
                $query = "
                    SELECT product_id, handler
                    FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
                    WHERE id=$productId
                ";
                $objResult2 = $objDatabase->Execute($query);
                if (!$objResult2) {
                    $this->errorHandling();
                }
                $productCode     = $objResult2->fields['product_id'];
                $productDistribution = $objResult2->fields['handler'];
                $productPrice    = $objResult->fields['price'];
                $productQuantity = $objResult->fields['quantity'];
                $productVatRate  = $objResult->fields['vat_percent'];
                // $rowNetPrice means 'product times price' from here
                $rowNetPrice  = $productPrice * $productQuantity;
                $rowPrice     = $rowNetPrice; // VAT added later, if applicable
                $rowVatAmount = 0;
                $total_net_price += $rowNetPrice;

                // Here, the VAT has to be recalculated before setting up the
                // fields.  If the VAT is excluded, it must be added here.
                // Note: the old shop_order.tax_price field is no longer valid,
                // individual shop_order_items *MUST* have been UPDATEd by the
                // time PHP parses this line.
                // Also note that this implies that the tax_number.status and
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
                            ? $objResult->fields['order_items_id'] // edit order
                            // If we're just showing the order details, the
                            // product ID is only used in the product ID column
                            : $objResult->fields['productid'] // show order
                        ),
                    'SHOP_PRODUCT_CUSTOM_ID' => $productCode,
                    // fill VAT field
                    'SHOP_PRODUCT_TAX_RATE' => ($type == 1
                            ? $productVatRate
                            : Vat::format($productVatRate)
                        ),
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
                            $menu .= ' selected="selected"';
                        }
                        $menu .= '>'.$arrProduct['id']."</option>\n";
                    }
                    self::$objTemplate->setVariable(array(
                        'SHOP_PRODUCT_IDS_MENU' => $menu
                    ));
                }
                self::$objTemplate->parse('orderdetailsRow');
                $objResult->MoveNext();
            }

            // Show VAT with the individual products:
            // If VAT is enabled, and we're both in the same country
            // ($total_vat_amount has been set above if both conditions are met)
            // show the VAT rate.  If there is no VAT, the amount is 0 (zero, '', nil, nada).
            //if ($total_vat_amount) {
                // distinguish between included VAT, and additional VAT added to sum
                $tax_part_percentaged = (Vat::isIncluded()
                        ? $_ARRAYLANG['TXT_TAX_PREFIX_INCL']
                        : $_ARRAYLANG['TXT_TAX_PREFIX_EXCL']
                );
                self::$objTemplate->setVariable(array(
                    'SHOP_TAX_PRICE' => Currency::formatPrice($total_vat_amount),
                    'SHOP_PART_TAX_PROCENTUAL' => $tax_part_percentaged,
                ));
            //} else {
                // No VAT otherwise
                // remove it from the details overview if empty
                //self::$objTemplate->hideBlock('shopTaxPrice');
                //$tax_part_percentaged = $_ARRAYLANG['TXT_NO_TAX'];
            //}

            self::$objTemplate->setVariable(array(
                'SHOP_ROWCLASS_NEW' => (++$i % 2 ? 'row2' : 'row1'),
                'SHOP_CURRENCY_ORDER_SUM' => Currency::formatPrice($shopCurrencyOrderSum),
                'SHOP_TOTAL_WEIGHT' => Weight::getWeightString($total_weight),
                'SHOP_NET_PRICE' => Currency::formatPrice($total_net_price),
            ));
        }

        self::$objTemplate->setVariable(array(
            'TXT_ORDER' => $_ARRAYLANG['TXT_ORDER'],
            'TXT_ORDERNUMBER' => $_ARRAYLANG['TXT_ORDERNUMBER'],
            'TXT_ORDERDATE' => $_ARRAYLANG['TXT_ORDERDATE'],
            'TXT_ORDERSTATUS' => $_ARRAYLANG['TXT_ORDERSTATUS'],
            'TXT_ORDER_SUM' => $_ARRAYLANG['TXT_ORDER_SUM'],
            'TXT_BILL' => $_ARRAYLANG['TXT_BILL'],
            'TXT_SHIPPING_METHOD' => $_ARRAYLANG['TXT_SHIPPING_METHOD'],
            'TXT_LAST_EDIT' => $_ARRAYLANG['TXT_LAST_EDIT'],
            'TXT_BILLING_ADDRESS' => $_ARRAYLANG['TXT_BILLING_ADDRESS'],
            'TXT_SHIPPING_ADDRESS' => $_ARRAYLANG['TXT_SHIPPING_ADDRESS'],
            'TXT_COMPANY' => $_ARRAYLANG['TXT_COMPANY'],
            'TXT_PREFIX' => $_ARRAYLANG['TXT_PREFIX'],
            'TXT_FIRST_NAME' => $_ARRAYLANG['TXT_FIRST_NAME'],
            'TXT_LAST_NAME' => $_ARRAYLANG['TXT_LAST_NAME'],
            'TXT_ADDRESS' => $_ARRAYLANG['TXT_ADDRESS'],
            'TXT_ZIP_CITY' => $_ARRAYLANG['TXT_ZIP_CITY'],
            'TXT_PHONE' => $_ARRAYLANG['TXT_PHONE'],
            'TXT_EMAIL' => $_ARRAYLANG['TXT_EMAIL'],
            'TXT_COUNTRY' => $_ARRAYLANG['TXT_COUNTRY'],
            'TXT_FAX' => $_ARRAYLANG['TXT_FAX'],
            'TXT_PAYMENT_INFORMATIONS' => $_ARRAYLANG['TXT_PAYMENT_INFORMATIONS'],
            'TXT_CREDIT_CARD_OWNER' => $_ARRAYLANG['TXT_CREDIT_CARD_OWNER'],
            'TXT_CARD_NUMBER' => $_ARRAYLANG['TXT_CARD_NUMBER'],
            'TXT_CVC_CODE' => $_ARRAYLANG['TXT_CVC_CODE'],
            'TXT_EXPIRY_DATE' => $_ARRAYLANG['TXT_EXPIRY_DATE'],
            'TXT_PAYMENT_TYPE' => $_ARRAYLANG['TXT_PAYMENT_TYPE'],
            'TXT_NUMBER' => $_ARRAYLANG['TXT_NUMBER'],
            'TXT_PRODUCT_ID' => $_ARRAYLANG['TXT_ID'],
            'TXT_SHOP_PRODUCT_CUSTOM_ID' => $_ARRAYLANG['TXT_SHOP_PRODUCT_CUSTOM_ID'],
            'TXT_PRODUCT_NAME' => $_ARRAYLANG['TXT_PRODUCT_NAME'],
            'TXT_PRODUCT_PRICE' => $_ARRAYLANG['TXT_PRODUCT_PRICE'],
            'TXT_SUM' => $_ARRAYLANG['TXT_SUM'],
            'TXT_SHIPPING_PRICE' => $_ARRAYLANG['TXT_SHIPPING_PRICE'],
            'TXT_PAYMENT_COSTS' => $_ARRAYLANG['TXT_PAYMENT_COSTS'],
            'TXT_TOTAL' => $_ARRAYLANG['TXT_TOTAL'],
            'TXT_CUSTOMER_REMARKS' => $_ARRAYLANG['TXT_CUSTOMER_REMARKS'],
            'TXT_STORE' => $_ARRAYLANG['TXT_STORE'],
            'TXT_EDIT' => $_ARRAYLANG['TXT_EDIT'],
            'TXT_IP_ADDRESS' => $_ARRAYLANG['TXT_IP_ADDRESS'],
            'TXT_BROWSER_VERSION' => $_ARRAYLANG['TXT_BROWSER_VERSION'],
            'TXT_CLIENT_HOST' => $_ARRAYLANG['TXT_CLIENT_HOST'],
            'TXT_BROWSER_LANGUAGE' => $_ARRAYLANG['TXT_BROWSER_LANGUAGE'],
            'TXT_SEND_MAIL_TO_ADDRESS' => $_ARRAYLANG['TXT_SEND_MAIL_TO_ADDRESS'],
            // inserted VAT, weight here
            // change header depending on whether the tax is included or excluded
            'TXT_TAX_RATE' => (Vat::isIncluded()
                    ? $_ARRAYLANG['TXT_TAX_PREFIX_INCL']
                    : $_ARRAYLANG['TXT_TAX_PREFIX_EXCL']
                ),
            'TXT_TOTAL_WEIGHT' => $_ARRAYLANG['TXT_TOTAL_WEIGHT'],
            'TXT_NET_PRICE' => $_ARRAYLANG['TXT_NET_PRICE'],
            'TXT_WARNING_SHIPPER_WEIGHT' => $_ARRAYLANG['TXT_WARNING_SHIPPER_WEIGHT'],
            'TXT_WEIGHT' => $_ARRAYLANG['TXT_WEIGHT'],
            'TXT_SHOP_ACCOUNT_VALIDITY' => $_ARRAYLANG['TXT_SHOP_VALIDITY'],
        ));

        self::$objTemplate->setGlobalVariable(array(
            'TXT_VIEW_DETAILS' => $_ARRAYLANG['TXT_VIEW_DETAILS']
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

        $shopOrderId = intval($_POST['orderid']);
        $objFWUser = FWUser::getFWUserObject();

        self::$objTemplate->setVariable(array(
            'TXT_ID' => $_ARRAYLANG['TXT_ID'],
            'TXT_DATE' => $_ARRAYLANG['TXT_DATE'],
            'TXT_NAME' => $_ARRAYLANG['TXT_NAME'],
        ));

        // calculate the total order sum in the selected currency of the customer
        $shopTotalOrderSum = floatval($_POST['shopShippingPrice'])
        + floatval($_POST['shopPaymentPrice']);
        // the tax amount will be set, even if it's included in the price already.
        // thus, we have to check the setting.
        if (!Vat::isIncluded()) {
            $shopTotalOrderSum += floatval($_POST['shopTaxPrice']);
        }
        // store the product details and add the price of each product
        // to the total order sum $shopTotalOrderSum
        foreach ($_REQUEST['shopProductList'] as $orderItemId => $productId) {
            if ($orderItemId != 0 && $productId == 0) {
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
            } elseif ($orderItemId == 0 && $productId != 0) {
                // add a new product to the list
                $shopProductPrice = floatval($_REQUEST['shopProductPrice'][$orderItemId]);
                $shopProductQuantity = intval($_REQUEST['shopProductQuantity'][$orderItemId]) < 1 ? 1 : intval($_REQUEST['shopProductQuantity'][$orderItemId]);
                $shopTotalOrderSum += $shopProductPrice * $shopProductQuantity;
                $shopProductTaxPercent = floatval($_REQUEST['shopProductTaxPercent'][$orderItemId]);
                $shopProductWeight = Weight::getWeight($_REQUEST['shopProductWeight'][$orderItemId]);
                $query = "INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_order_items ".
                    "(orderid, productid, product_name, price, quantity, vat_percent, weight) ".
                    "VALUES ($shopOrderId, $productId, '".
                    contrexx_strip_tags($_POST['shopProductName'][$orderItemId]).
                    "', $shopProductPrice, $shopProductQuantity, ".
                    "$shopProductTaxPercent, $shopProductWeight)";
                $objResult = $objDatabase->Execute($query);
            } elseif ($orderItemId != 0 && $productId != 0) {
                // update the order item
                $shopProductPrice = floatval($_REQUEST['shopProductPrice'][$orderItemId]);
                $shopProductQuantity = intval($_REQUEST['shopProductQuantity'][$orderItemId]) < 1 ? 1 : intval($_REQUEST['shopProductQuantity'][$orderItemId]);
                $shopTotalOrderSum += $shopProductPrice * $shopProductQuantity;
                $shopProductTaxPercent = floatval($_REQUEST['shopProductTaxPercent'][$orderItemId]);
                $shopProductWeight = Weight::getWeight($_REQUEST['shopProductWeight'][$orderItemId]);
                $query = "UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_order_items SET ".
                        "price = $shopProductPrice".
                        ", quantity = $shopProductQuantity".
                        ", productid = ".intval($_POST['shopProductList'][$orderItemId]).
                        ", product_name='".contrexx_strip_tags($_POST['shopProductName'][$orderItemId]).
                        "', vat_percent = $shopProductTaxPercent".
                        ", weight = $shopProductWeight".
                    " WHERE order_items_id=$orderItemId";
                $objResult = $objDatabase->Execute($query);
            }
        }

        // store the order details
        $query = "
            UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_orders
               SET currency_order_sum=".floatval($shopTotalOrderSum).",
                   currency_ship_price=".floatval($_POST['shopShippingPrice']).",
                   currency_payment_price=".floatval($_POST['shopPaymentPrice']).",
                   order_status ='".intval($_POST['shopOrderStatusId'])."',
                   ship_prefix='".addslashes(strip_tags($_POST['shopShipPrefix']))."',
                   ship_company='".addslashes(strip_tags($_POST['shopShipCompany']))."',
                   ship_firstname='".addslashes(strip_tags($_POST['shopShipFirstname']))."',
                   ship_lastname='".addslashes(strip_tags($_POST['shopShipLastname']))."',
                   ship_address='".addslashes(strip_tags($_POST['shopShipAddress']))."',
                   ship_city='".addslashes(strip_tags($_POST['shopShipCity']))."',
                   ship_zip='".addslashes(strip_tags($_POST['shopShipZip']))."',
                   ship_country_id=".intval($_POST['shopShipCountry']).",
                   ship_phone='".addslashes(strip_tags($_POST['shopShipPhone']))."',
                   tax_price=".floatval($_POST['shopTaxPrice']).",
                   shipping_id=".intval($_POST['shipperId']).",
                   modified_by='".$objFWUser->objUser->getUsername()."',
                   last_modified=now()
             WHERE orderid = $shopOrderId
        ";
        // should not be changed, see above
        // ", payment_id = ".intval($_POST['paymentId']).
        if (!$objDatabase->Execute($query)) {
            $this->errorHandling();
            return false;
        } else {
            self::addMessage($_ARRAYLANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL']);
            // Send an email to the customer, if requested
            if (!empty($_POST['shopSendMail'])) {
                $result = shopmanager::sendConfirmationMail($shopOrderId);
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
    function shopDeleteOrder($shopOrderId=0)
    {
        global $objDatabase, $_ARRAYLANG;

        $arrOrderId = array();

        // prepare the array $arrOrderId with the ids of the orders to delete
        if (empty($shopOrderId)) {
            if (isset($_GET['orderId']) && !empty($_GET['orderId'])) {
                array_push($arrOrderId, $_GET['orderId']);
            } elseif (isset($_POST['selectedOrderId']) && !empty($_POST['selectedOrderId'])) {
                $arrOrderId = $_POST['selectedOrderId'];
            }
        } else {
            array_push($arrOrderId, $shopOrderId);
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
        $i   = 0;
        self::$objTemplate->loadTemplateFile("module_shop_customers.html", true, true);
        self::$objTemplate->setVariable(array(
            'TXT_OVERVIEW' => $_ARRAYLANG['TXT_OVERVIEW'],
            'TXT_SHOP_DISCOUNTS_CUSTOMER' => $_ARRAYLANG['TXT_SHOP_DISCOUNTS_CUSTOMER'],
            'TXT_SHOP_CUSTOMER_GROUPS' => $_ARRAYLANG['TXT_SHOP_CUSTOMER_GROUPS'],
            'TXT_CUSTOMERS_PARTNERS' => $_ARRAYLANG['TXT_CUSTOMERS_PARTNERS'],
            'TXT_CUSTOMER_TYP' => $_ARRAYLANG['TXT_CUSTOMER_TYP'],
            'TXT_CUSTOMER' => $_ARRAYLANG['TXT_CUSTOMER'],
            'TXT_RESELLER' => $_ARRAYLANG['TXT_RESELLER'],
//            'TXT_INACTIVE' => $_ARRAYLANG['TXT_INACTIVE'],
//            'TXT_ACTIVE' => $_ARRAYLANG['TXT_ACTIVE'],
            'TXT_SORT_ORDER' => $_ARRAYLANG['TXT_SORT_ORDER'],
            'TXT_LAST_NAME' => $_ARRAYLANG['TXT_LAST_NAME'],
            'TXT_FIRST_NAME' => $_ARRAYLANG['TXT_FIRST_NAME'],
            'TXT_ID' => $_ARRAYLANG['TXT_ID'],
            'TXT_COMPANY' => $_ARRAYLANG['TXT_COMPANY'],
            'TXT_ACTION' => $_ARRAYLANG['TXT_ACTION'],
            'TXT_NAME' => $_ARRAYLANG['TXT_NAME'],
            'TXT_ADDRESS' => $_ARRAYLANG['TXT_ADDRESS'],
            'TXT_ZIP_CITY' => $_ARRAYLANG['TXT_ZIP_CITY'],
            'TXT_PHONE' => $_ARRAYLANG['TXT_PHONE'],
            'TXT_EMAIL' => $_ARRAYLANG['TXT_EMAIL'],
            'TXT_SEARCH' => $_ARRAYLANG['TXT_SEARCH'],
            'TXT_CONFIRM_DELETE_CUSTOMER' => $_ARRAYLANG['TXT_CONFIRM_DELETE_CUSTOMER'],
            'TXT_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
            'TXT_ALL_ORDERS_WILL_BE_DELETED' => $_ARRAYLANG['TXT_ALL_ORDERS_WILL_BE_DELETED'],
            'TXT_ADD_NEW_CUSTOMER' => $_ARRAYLANG['TXT_ADD_NEW_CUSTOMER'],
            'TXT_MARKED' => $_ARRAYLANG['TXT_MARKED'],
            'TXT_SELECT_ALL' => $_ARRAYLANG['TXT_SELECT_ALL'],
            'TXT_REMOVE_SELECTION' => $_ARRAYLANG['TXT_REMOVE_SELECTION'],
            'TXT_SELECT_ACTION' => $_ARRAYLANG['TXT_SELECT_ACTION'],
            'TXT_MAKE_SELECTION' => $_ARRAYLANG['TXT_MAKE_SELECTION'],
        ));
        self::$objTemplate->setGlobalVariable(array(
//            'TXT_STATUS' => $_ARRAYLANG['TXT_STATUS'],
            'TXT_VIEW_DETAILS' => $_ARRAYLANG['TXT_VIEW_DETAILS'],
            'TXT_EDIT' => $_ARRAYLANG['TXT_EDIT'],
            'TXT_DELETE' => $_ARRAYLANG['TXT_DELETE'],
            'TXT_SEND_MAIL_TO_ADDRESS' => $_ARRAYLANG['TXT_SEND_MAIL_TO_ADDRESS'],
        ));

        $shopCustomerStatus = -1;
        $shopCustomer = -1;
        $shopSearchTerm = '';
        $shopListLetter = '';
        $shopSearchPattern = '';
        $shopCustomerOrder = 'customerid';
        if (   isset($_REQUEST['shopCustomerStatus'])
            && $_REQUEST['shopCustomerStatus'] >= 0) {
            $shopCustomerStatus = intval($_REQUEST['shopCustomerStatus']);
            $shopSearchPattern = " AND customer_status=$shopCustomerStatus";
        }
        if (   isset($_REQUEST['shopCustomer'])
            && $_REQUEST['shopCustomer'] >= 0) {
            $shopCustomer = intval($_REQUEST['shopCustomer']);
            $shopSearchPattern .= " AND is_reseller=$shopCustomer";
        }
        if (!empty($_REQUEST['shopSearchTerm'])) {
            $shopSearchTerm = contrexx_addslashes(trim(strip_tags($_REQUEST['shopSearchTerm'])));
            $shopSearchPattern .= "
                AND (   customerid LIKE '%$shopSearchTerm%'
                     OR company LIKE '%$shopSearchTerm%'
                     OR firstname LIKE '%$shopSearchTerm%'
                     OR lastname LIKE '%$shopSearchTerm%'
                     OR address LIKE '%$shopSearchTerm%'
                     OR city LIKE '%$shopSearchTerm%'
                     OR phone LIKE '%$shopSearchTerm%'
                     OR email LIKE '%$shopSearchTerm%')";
        }
        if (isset($_REQUEST['shopListSort'])) {
            $shopCustomerOrder = contrexx_addslashes(trim(strip_tags($_REQUEST['shopListSort'])));
        }
        if (!empty($_REQUEST['shopListLetter'])) {
            $shopListLetter = $_REQUEST['shopListLetter'];
            $shopSearchPattern .= " AND LEFT(`$shopCustomerOrder`, 1)='$shopListLetter'";
        }

        // create query
        $query = "
            SELECT customerid, company, firstname, lastname,
                   address, city, zip, phone, email, customer_status
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_customers
             WHERE 1 $shopSearchPattern
             ORDER BY $shopCustomerOrder DESC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            $this->errorHandling();
        } else {
            $pos = (isset($_GET['pos']) ? intval($_GET['pos']) : 0);
            $count = $objResult->RecordCount();
            if ($count == 0) {
                self::$objTemplate->hideBlock('shopCustomersOverview');
            }
            $shopPagingLimit = intval($_CONFIG['corePagingLimit']);
            $paging = getPaging(
                $count, $pos,
                '&amp;cmd=shop'.MODULE_INDEX.'&amp;act=customers'.
                  ($shopCustomerStatus >= 0 ? '&amp;shopCustomerStatus='.$shopCustomerStatus : '').
                  ($shopCustomer >= 0 ? '&amp;shopCustomer='.$shopCustomer : '').
                  ($shopSearchTerm ? '&amp;shopSearchTerm='.$shopSearchTerm : '').
                  ($shopListLetter ? '&amp;shopListLetter='.$shopListLetter : '').
                  ($shopCustomerOrder != 'customerid' ? '&amp;shopListSort='.$shopCustomerOrder : ''),
                "<b>".$_ARRAYLANG['TXT_CUSTOMERS_ENTRIES']."</b>");
            self::$objTemplate->setVariable(array(
                'SHOP_CUSTOMER_PAGING' => $paging,
                'SHOP_CUSTOMER_TERM' => htmlentities($shopSearchTerm),
                'SHOP_CUSTOMER_LISTLETTER' => $shopListLetter,
                'SHOP_CUSTOMER_TYPE_MENUOPTIONS' => Customers::getCustomerTypeMenuoptions($shopCustomer),
                'SHOP_CUSTOMER_STATUS_MENUOPTIONS' => Customers::getCustomerStatusMenuoptions($shopCustomerStatus),
                'SHOP_CUSTOMER_SORT_MENUOPTIONS' => Customers::getCustomerSortMenuoptions($shopCustomerOrder),
//                'SHOP_LISTLETTER_MENUOPTIONS' => self::getListletterMenuoptions,
            ));
        }
        if (!($objResult = $objDatabase->SelectLimit($query, $shopPagingLimit, $pos))) {
            //if query has errors, call errorhandling
            $this->errorHandling();
        } else {
            self::$objTemplate->setCurrentBlock('customersRow');
            while (!$objResult->EOF) {
                $shopCustomerStatus = "led_red.gif";
                if ($objResult->fields['customer_status'] == 1) {
                    $shopCustomerStatus = "led_green.gif";
                }
                self::$objTemplate->setVariable(array(
                    'SHOP_ROWCLASS' => (++$i % 2 ? 'row1' : 'row2'),
                    'SHOP_CUSTOMERID' => $objResult->fields['customerid'],
                    'SHOP_COMPANY' => $objResult->fields['company'] == '' ? '&nbsp;' : $objResult->fields['company'],
                    'SHOP_NAME' => $objResult->fields['firstname'].'&nbsp;'.$objResult->fields['lastname'],
                    'SHOP_ADDRESS' => $objResult->fields['address'] == '' ? '&nbsp;' : $objResult->fields['address'],
                    'SHOP_ZIP' => $objResult->fields['zip'],
                    'SHOP_CITY' => $objResult->fields['city'],
                    'SHOP_PHONE' => $objResult->fields['phone'] == '' ? '&nbsp;' : $objResult->fields['phone'],
                    'SHOP_EMAIL' => $objResult->fields['email'] == '' ? '&nbsp;' : $objResult->fields['email'],
                    'SHOP_CUSTOMER_STATUS_IMAGE' => $shopCustomerStatus,
                ));
                self::$objTemplate->parse('customersRow');
                $objResult->MoveNext();
            }
        }
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
                        $shopOrderId = $objResult->fields['orderid'];
                        $this->shopDeleteOrder($shopOrderId);
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
        $i = 1;
        //begin language variables
        self::$objTemplate->setVariable(array(
            'TXT_CUSTOMER_DETAILS' => $_ARRAYLANG['TXT_CUSTOMER_DETAILS'],
            'TXT_CUSTOMER_DATA' => $_ARRAYLANG['TXT_CUSTOMER_DATA'],
            'TXT_COMPANY' => $_ARRAYLANG['TXT_COMPANY'],
            'TXT_PREFIX' => $_ARRAYLANG['TXT_PREFIX'],
            'TXT_FIRST_NAME' => $_ARRAYLANG['TXT_FIRST_NAME'],
            'TXT_LAST_NAME' => $_ARRAYLANG['TXT_LAST_NAME'],
            'TXT_ADDRESS' => $_ARRAYLANG['TXT_ADDRESS'],
            'TXT_ZIP_CITY' => $_ARRAYLANG['TXT_ZIP_CITY'],
            'TXT_PHONE' => $_ARRAYLANG['TXT_PHONE'],
            'TXT_EMAIL' => $_ARRAYLANG['TXT_EMAIL'],
            'TXT_CUSTOMER_NUMBER' => $_ARRAYLANG['TXT_CUSTOMER_NUMBER'],
            'TXT_CUSTOMER_TYP' => $_ARRAYLANG['TXT_CUSTOMER_TYP'],
            'TXT_LOGIN_NAME' => $_ARRAYLANG['TXT_LOGIN_NAME'],
            'TXT_REGISTER_DATE' => $_ARRAYLANG['TXT_REGISTER_DATE'],
            'TXT_CUSTOMER_STATUS' => $_ARRAYLANG['TXT_CUSTOMER_STATUS'],
            'TXT_COUNTRY' => $_ARRAYLANG['TXT_COUNTRY'],
            'TXT_FAX' => $_ARRAYLANG['TXT_FAX'],
            'TXT_PAYMENT_INFORMATIONS' => $_ARRAYLANG['TXT_PAYMENT_INFORMATIONS'],
            'TXT_CREDIT_CARD_OWNER' => $_ARRAYLANG['TXT_CREDIT_CARD_OWNER'],
            'TXT_CARD_NUMBER' => $_ARRAYLANG['TXT_CARD_NUMBER'],
            'TXT_CVC_CODE' => $_ARRAYLANG['TXT_CVC_CODE'],
            'TXT_EXPIRY_DATE' => $_ARRAYLANG['TXT_EXPIRY_DATE'],
            'TXT_ORDERS' => $_ARRAYLANG['TXT_ORDERS'],
            'TXT_ORDERNUMBER' => $_ARRAYLANG['TXT_ORDERNUMBER'],
            'TXT_ORDERSTATUS' => $_ARRAYLANG['TXT_ORDERSTATUS'],
            'TXT_DATE' => $_ARRAYLANG['TXT_DATE'],
            'TXT_CUSTOMER_STATUS' => $_ARRAYLANG['TXT_CUSTOMER_STATUS'],
            'TXT_ORDER_SUM' => $_ARRAYLANG['TXT_ORDER_SUM'],
            'TXT_MORE_INFORMATIONS' => $_ARRAYLANG['TXT_MORE_INFORMATIONS'],
            'TXT_REMARK' => $_ARRAYLANG['TXT_REMARK'],
            'TXT_EDIT_CUSTOMER' => $_ARRAYLANG['TXT_EDIT_CUSTOMER'],
            'TXT_SEND_MAIL_TO_ADDRESS' => $_ARRAYLANG['TXT_SEND_MAIL_TO_ADDRESS'],
            'TXT_SHOP_DISCOUNT_GROUP_CUSTOMER' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_GROUP_CUSTOMER'],
        ));
        self::$objTemplate->setGlobalVariable(array(
            'TXT_VIEW_DETAILS' => $_ARRAYLANG['TXT_VIEW_DETAILS']
        ));

        $customerid = intval($_REQUEST['customerid']);
        if (isset($_POST['shopStore'])) {
            if ($this->_checkEmailIntegrity($_POST['shopEmail'], $customerid)) {
                if ($this->_checkUsernameIntegrity($_POST['shopUsername'], $customerid)) {
                    $shopUsername       = addslashes(strip_tags($_POST['shopUsername']));
                    $shopPassword       = $_POST['shopPassword'];
                    $shopCompany        = addslashes(strip_tags($_POST['shopCompany']));
                    $shopPrefix         = addslashes(strip_tags($_POST['shopPrefix']));
                    $shopFirstname      = addslashes(strip_tags($_POST['shopFirstname']));
                    $shopLastname       = addslashes(strip_tags($_POST['shopLastname']));
                    $shopAddress        = addslashes(strip_tags($_POST['shopAddress']));
                    $shopCity           = addslashes(strip_tags($_POST['shopCity']));
                    $shopZip            = addslashes(strip_tags($_POST['shopZip']));
                    $shopCountry        = intval($_POST['shopCountry']);
                    $shopPhone          = addslashes(strip_tags($_POST['shopPhone']));
                    $shopFax            = addslashes(strip_tags($_POST['shopFax']));
                    $shopEmail          = addslashes(strip_tags($_POST['shopEmail']));
                    $shopCcnumber       = addslashes(strip_tags($_POST['shopCcnumber']));
                    $shopCcdate         = addslashes(strip_tags($_POST['shopCcdate']));
                    $shopCcname         = addslashes(strip_tags($_POST['shopCcname']));
                    $shopCvcCode        = addslashes(strip_tags($_POST['shopCvcCode']));
                    $shopCompanyNote    = addslashes(strip_tags($_POST['shopCompanyNote']));
                    $shopCustomerStatus = intval($_POST['shopCustomerStatus']);
                    $shopIsReseller     = intval($_POST['shopCustomerClass']);
                    $shopRegisterDate   = addslashes(strip_tags($_POST['shopRegisterDate']));
                    $shopDiscountGroup  = intval($_POST['shopDiscountGroupCustomer']);
                    // update the customer informations
                    $shopMd5Password = '';
                    if ($shopPassword != '') {
                        $shopMd5Password = md5($shopPassword);
                    }
                    $shopUdatePassword = '';
                    if ($shopMd5Password != '') { //if password has been reset, set it new
                        $shopUdatePassword = ",password = '$shopMd5Password' ";
                    }

                    $query = "UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_customers
                              SET username = '".$shopUsername."' $shopUdatePassword ,
                                      prefix = '".$shopPrefix."',
                                      company ='".$shopCompany."',
                                      firstname ='".$shopFirstname."',
                                      lastname ='".$shopLastname."',
                                      address='".$shopAddress."',
                                      city='".$shopCity."',
                                      zip='".$shopZip."',
                                      country_id ='".$shopCountry."',
                                      phone='".$shopPhone."',
                                      fax='".$shopFax."',
                                      email = '".$shopEmail."',
                                      ccnumber='".$shopCcnumber."',
                                      ccdate='".$shopCcdate."',
                                      ccname='".$shopCcname."',
                                      cvc_code='".$shopCvcCode."',
                                      company_note='".$shopCompanyNote."',
                                      customer_status='".$shopCustomerStatus."',
                                      is_reseller='".$shopIsReseller."',
                                      register_date='".$shopRegisterDate."',
                                      group_id=$shopDiscountGroup
                              WHERE customerid=".$customerid;

                    if (!$objDatabase->Execute($query)) {
                        //if query has errors, call errorhandling
                        $this->errorHandling();
                    } else {
                        self::addMessage($_ARRAYLANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL']);
                    }
                    //check if the logindata must be sent
                    if (isset($_POST['shopSendLoginData'])) {
                        // Determine customer language
                        $lang_id = FRONTEND_LANG_ID;
                        $query = "
                            SELECT customer_lang
                              FROM ".DBPREFIX."module_shop_customers
                             INNER JOIN ".DBPREFIX."module_shop_orders
                             USING (customerid)
                             WHERE customerid=$customerid";
                        $objResult = $objDatabase->Execute($query);
                        if ($objResult && !$objResult->EOF) {
                            $lang_id = $objResult->fields['customer_lang'];
                            if (intval($lang_id) == 0) {
                                $lang_id = FWLanguage::getLangIdByIso639_1($objResult->fields['customer_lang']);
                            }
                        }
                        // Select template for sending login data
                        $arrShopMailtemplate = ShopLibrary::shopSetMailtemplate(3, $lang_id);
                        $shopMailTo = $_POST['shopEmail'];
                        $shopMailFrom = $arrShopMailtemplate['mail_from'];
                        $shopMailFromText = $arrShopMailtemplate['mail_x_sender'];
                        $shopMailSubject = $arrShopMailtemplate['mail_subject'];
                        $shopMailBody = $arrShopMailtemplate['mail_body'];
                        // replace variables from template
                        $shopMailBody = str_replace("<USERNAME>", $shopUsername, $shopMailBody);
                        $shopMailBody = str_replace("<PASSWORD>", $shopPassword, $shopMailBody);
                        // added
                        $shopMailBody = str_replace("<CUSTOMER_PREFIX>", $shopPrefix, $shopMailBody);
                        $shopMailBody = str_replace("<CUSTOMER_LASTNAME>", $shopLastname, $shopMailBody);
                        $result = ShopLibrary::shopSendMail($shopMailTo, $shopMailFrom, $shopMailFromText, $shopMailSubject, $shopMailBody);
                        if ($result) {
                            self::addMessage(sprintf($_ARRAYLANG['TXT_EMAIL_SEND_SUCCESSFULLY'], $shopMailTo));
                        } else {
                            self::addError($_ARRAYLANG['TXT_MESSAGE_SEND_ERROR']);
                            return false;
                        }
                    }
                } else {
                    self::addError($_ARRAYLANG['TXT_USERNAME_USED_BY_OTHER_CUSTOMER']);
                }
            } else {
                self::addError($_ARRAYLANG['TXT_EMAIL_USED_BY_OTHER_CUSTOMER']);
            }
        } //end if

        //set the customer informations
        $query = "SELECT * FROM ".DBPREFIX."module_shop".MODULE_INDEX."_customers ".
                 "WHERE customerid=$customerid";
        if (($objResult = $objDatabase->Execute($query)) === false) {
            //if query has errors, call errorhandling
            $this->errorHandling();

        } else {
            if (!$objResult->EOF) {
                //check customer typ
                $customerType = $_ARRAYLANG['TXT_CUSTOMER'];
                if ($objResult->fields['is_reseller'] == 1) {
                    $customerType = $_ARRAYLANG['TXT_RESELLER'];
                }
                //check customer status
                $customerStatus = $_ARRAYLANG['TXT_INACTIVE'];
                if ($objResult->fields['customer_status'] == 1) {
                    $customerStatus = $_ARRAYLANG['TXT_ACTIVE'];
                }
                //set edit fields
                self::$objTemplate->setVariable(array(
                    'SHOP_CUSTOMERID' => $objResult->fields['customerid'],
                    'SHOP_PREFIX' => $objResult->fields['prefix'] == "" ? "&nbsp;" : $objResult->fields['prefix'],
                    'SHOP_LASTNAME' => $objResult->fields['lastname'] == "" ? "&nbsp;" : $objResult->fields['lastname'],
                    'SHOP_FIRSTNAME' => $objResult->fields['firstname'] == "" ? "&nbsp;" : $objResult->fields['firstname'],
                    'SHOP_COMPANY' => $objResult->fields['company'] == "" ? "&nbsp;" : $objResult->fields['company'],
                    'SHOP_ADDRESS' => $objResult->fields['address'] == "" ? "&nbsp;" : $objResult->fields['address'],
                    'SHOP_CITY' => $objResult->fields['city'] == "" ? "&nbsp;" : $objResult->fields['city'],
                    'SHOP_USERNAME' => $objResult->fields['username'] == "" ? "&nbsp;" : $objResult->fields['username'],
                    // unavailable
                    //'SHOP_ORDER_STATUS' => $objResult->fields['order_status'],
                    'SHOP_COUNTRY' => Country::getNameById($objResult->fields['country_id']),
                    'SHOP_ZIP' => $objResult->fields['zip'] == "" ? "&nbsp;" : $objResult->fields['zip'],
                    'SHOP_PHONE' => $objResult->fields['phone'] == "" ? "&nbsp;" : $objResult->fields['phone'],
                    'SHOP_FAX' => $objResult->fields['fax'] == "" ? "&nbsp;" : $objResult->fields['fax'],
                    'SHOP_EMAIL' => $objResult->fields['email'] == "" ? "&nbsp;" : $objResult->fields['email'],
                    // unavailable
                    //'SHOP_PAYMENTTYPE' => $objResult->fields['paymenttyp'],
                    'SHOP_CCNUMBER' => $objResult->fields['ccnumber'] == "" ? "&nbsp;" : $objResult->fields['ccnumber'],
                    'SHOP_CCDATE' => $objResult->fields['ccdate'] == "" ? "&nbsp;" : $objResult->fields['ccdate'],
                    'SHOP_CCNAME' => $objResult->fields['ccname'] == "" ? "&nbsp;" : $objResult->fields['ccname'],
                    'SHOP_CVC_CODE' => $objResult->fields['cvc_code'] == "" ? "&nbsp;" : $objResult->fields['cvc_code'],
                    'SHOP_COMPANY_NOTE' => $objResult->fields['company_note'] == "" ? "-" : $objResult->fields['company_note'],
                    'SHOP_IS_RESELLER' => $customerType,
                    'SHOP_REGISTER_DATE' => $objResult->fields['register_date'],
                    'SHOP_CUSTOMER_STATUS' => $customerStatus,
                    'SHOP_DISCOUNT_GROUP_CUSTOMER' => Discount::getCustomerGroupName(
                            $objResult->fields['group_id']
                        ),
                ));
                $objResult->MoveNext();
            }
        }
        //set the orders
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
            self::$objTemplate->setCurrentBlock('orderRow');
            while (!$objResult->EOF) {
                $class = (++$i % 2 ? 'row1' : 'row2');
                //set edit fields
                self::$objTemplate->setVariable(array(
                    'SHOP_ROWCLASS' => $class,
                    'SHOP_ORDER_ID' => $objResult->fields['orderid'],
                    'SHOP_ORDER_ID_CUSTOM' => ShopLibrary::getCustomOrderId(
                            $objResult->fields['orderid'],
                            $objResult->fields['order_date']
                        ),
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
     * Add or update customer
     */
    function shopNewEditCustomer()
    {
        global $objDatabase, $_ARRAYLANG;
        //set template
        self::$objTemplate->loadTemplateFile("module_shop_edit_customer.html", true, true);

        //Check if the data must be stored
        if (isset($_POST['shopStore'])) {
            $this->shopAddCustomer();
        }

        //begin language variables
        self::$objTemplate->setVariable(array(
            'TXT_CUSTOMER_DATA' => $_ARRAYLANG['TXT_CUSTOMER_DATA'],
            'TXT_CUSTOMER_NUMBER' => $_ARRAYLANG['TXT_CUSTOMER_NUMBER'],
            'TXT_COMPANY' => $_ARRAYLANG['TXT_COMPANY'],
            'TXT_PREFIX' => $_ARRAYLANG['TXT_PREFIX'],
            'TXT_FIRST_NAME' => $_ARRAYLANG['TXT_FIRST_NAME'],
            'TXT_LAST_NAME' => $_ARRAYLANG['TXT_LAST_NAME'],
            'TXT_ADDRESS' => $_ARRAYLANG['TXT_ADDRESS'],
            'TXT_ZIP_CITY' => $_ARRAYLANG['TXT_ZIP_CITY'],
            'TXT_PHONE' => $_ARRAYLANG['TXT_PHONE'],
            'TXT_EMAIL' => $_ARRAYLANG['TXT_EMAIL'],
            'TXT_CUSTOMER_TYP' => $_ARRAYLANG['TXT_CUSTOMER_TYP'],
            'TXT_CUSTOMER' => $_ARRAYLANG['TXT_CUSTOMER'],
            'TXT_RESELLER' => $_ARRAYLANG['TXT_RESELLER'],
            'TXT_LOGIN_NAME' => $_ARRAYLANG['TXT_LOGIN_NAME'],
            'TXT_RESET_PASSWORD' => $_ARRAYLANG['TXT_RESET_PASSWORD'],
            'TXT_REGISTER_DATE' => $_ARRAYLANG['TXT_REGISTER_DATE'],
            'TXT_CUSTOMER_STATUS' => $_ARRAYLANG['TXT_CUSTOMER_STATUS'],
            'TXT_INACTIVE' => $_ARRAYLANG['TXT_INACTIVE'],
            'TXT_ACTIVE' => $_ARRAYLANG['TXT_ACTIVE'],
            'TXT_COUNTRY' => $_ARRAYLANG['TXT_COUNTRY'],
            'TXT_FAX' => $_ARRAYLANG['TXT_FAX'],
            'TXT_PAYMENT_INFORMATIONS' => $_ARRAYLANG['TXT_PAYMENT_INFORMATIONS'],
            'TXT_CREDIT_CARD_OWNER' => $_ARRAYLANG['TXT_CREDIT_CARD_OWNER'],
            'TXT_CARD_NUMBER' => $_ARRAYLANG['TXT_CARD_NUMBER'],
            'TXT_CVC_CODE' => $_ARRAYLANG['TXT_CVC_CODE'],
            'TXT_EXPIRY_DATE' => $_ARRAYLANG['TXT_EXPIRY_DATE'],
            'TXT_OPTIONS' => $_ARRAYLANG['TXT_OPTIONS'],
            'TXT_ORDERNUMBER' => $_ARRAYLANG['TXT_ORDERNUMBER'],
            'TXT_REMARK' => $_ARRAYLANG['TXT_REMARK'],
            'TXT_SAVE_CHANGES' => $_ARRAYLANG['TXT_SAVE_CHANGES'],
            'TXT_SEND_LOGIN_DATA' => $_ARRAYLANG['TXT_SEND_LOGIN_DATA'],
            'TXT_SHOP_DISCOUNT_GROUP_CUSTOMER' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_GROUP_CUSTOMER'],
        ));
        //set requested customerid
        $customerid = (isset($_REQUEST['customerid']) ? intval($_REQUEST['customerid']) : 0);
        if ($customerid == 0) { //create a new customer
            self::$pageTitle = $_ARRAYLANG['TXT_ADD_NEW_CUSTOMER'];
            self::$objTemplate->setVariable(array(
            'SHOP_CUSTOMERID' => "&nbsp;",
            'SHOP_SEND_LOGING_DATA_STATUS' => ' checked="checked"',
            'SHOP_REGISTER_DATE' => date("Y-m-d h:m:s"),
            'SHOP_COUNTRY' => $this->_getCountriesMenu('shopCountry'),
            'SHOP_CUSTOMER_ACT' => 'neweditcustomer'
            ));
        } else {    //edit user
            self::$pageTitle = $_ARRAYLANG['TXT_EDIT_CUSTOMER'];
            self::$objTemplate->setVariable(array(
            'SHOP_SEND_LOGING_DATA_STATUS' => "",
            'SHOP_CUSTOMER_ACT' => "customerdetails&amp;customerid={SHOP_CUSTOMERID}"
            ));

        }
        // set the customer informations
        if ($customerid > 0) {
            $query = "SELECT *
                  FROM ".DBPREFIX."module_shop".MODULE_INDEX."_customers
                  WHERE customerid = $customerid
                  ORDER BY lastname ASC";
            if (($objResult = $objDatabase->Execute($query)) === false) {
                //if query has errors, call errorhandling
                $this->errorHandling();
            } else {
                if (!$objResult->EOF) {
                    if ($objResult->fields['is_reseller'] == 1) {
                        self::$objTemplate->setVariable('SHOP_IS_RESELLER', ' selected="selected"');
                        self::$objTemplate->setVariable('SHOP_IS_CUSTOMER', '');
                    } else {
                        self::$objTemplate->setVariable('SHOP_IS_RESELLER', '');
                        self::$objTemplate->setVariable('SHOP_IS_CUSTOMER', ' selected="selected"');
                    }
                    if ($objResult->fields['customer_status'] == 1) {
                        self::$objTemplate->setVariable('SHOP_CUSTOMER_STATUS_0', '');
                        self::$objTemplate->setVariable('SHOP_CUSTOMER_STATUS_1', ' selected="selected"');
                    } else {
                        self::$objTemplate->setVariable('SHOP_CUSTOMER_STATUS_0', ' selected="selected"');
                        self::$objTemplate->setVariable('SHOP_CUSTOMER_STATUS_1', '');
                    }
                    self::$objTemplate->setVariable(array(
                        'SHOP_CUSTOMERID' => $objResult->fields['customerid'],
                        'SHOP_PREFIX' => $objResult->fields['prefix'],
                        'SHOP_LASTNAME' => $objResult->fields['lastname'],
                        'SHOP_FIRSTNAME' => $objResult->fields['firstname'],
                        'SHOP_COMPANY' => $objResult->fields['company'],
                        'SHOP_ADDRESS' => $objResult->fields['address'],
                        'SHOP_CITY' => $objResult->fields['city'],
                        'SHOP_USERNAME' => $objResult->fields['username'],
                        // unavailable
                        //'SHOP_ORDER_STATUS' => $objResult->fields['order_status'],
                        'SHOP_COUNTRY' => $this->_getCountriesMenu('shopCountry', $objResult->fields['country_id']),
                        'SHOP_ZIP' => $objResult->fields['zip'],
                        'SHOP_PHONE' => $objResult->fields['phone'],
                        'SHOP_FAX' => $objResult->fields['fax'],
                        'SHOP_EMAIL' => $objResult->fields['email'],
                        // unavailable
                        //'SHOP_PAYMENTTYPE' => $objResult->fields['paymenttyp'],
                        'SHOP_CCNUMBER' => $objResult->fields['ccnumber'],
                        'SHOP_CCDATE' => $objResult->fields['ccdate'],
                        'SHOP_CCNAME' => $objResult->fields['ccname'],
                        'SHOP_CVC_CODE' => $objResult->fields['cvc_code'],
                        'SHOP_COMPANY_NOTE' => $objResult->fields['company_note'],
                        'SHOP_REGISTER_DATE' => $objResult->fields['register_date'],
                        'SHOP_DISCOUNT_GROUP_CUSTOMER' => Discount::getMenuOptionsGroupCustomer(
                                $objResult->fields['group_id']
                            ),
                    ));
                }
            }
        } else {
            // Set up default values
            self::$objTemplate->setVariable(array(
                'SHOP_DISCOUNT_GROUP_CUSTOMER' => Discount::getMenuOptionsGroupCustomer(),
            ));
        }
        return true;
    }


    /**
     * Add a new customer to the Database.
     *
     * Sets Ok/Err messages according to the outcome.
     * @return  boolean     True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com> (parts)
     */
    function shopAddCustomer()
    {
        global $objDatabase, $_ARRAYLANG, $_FRONTEND_LANGID;

        if ($this->_checkEmailIntegrity($_POST['shopEmail'])) {
            if ($this->_checkUsernameIntegrity($_POST['shopUsername'])) {
                $shopUsername       = addslashes(strip_tags($_POST['shopUsername']));
                $shopPassword       = $_POST['shopPassword'];
                $shopMd5Password    = md5($shopPassword);
                $shopCompany        = addslashes(strip_tags($_POST['shopCompany']));
                $shopPrefix         = addslashes(strip_tags($_POST['shopPrefix']));
                $shopFirstname      = addslashes(strip_tags($_POST['shopFirstname']));
                $shopLastname       = addslashes(strip_tags($_POST['shopLastname']));
                $shopAddress        = addslashes(strip_tags($_POST['shopAddress']));
                $shopCity           = addslashes(strip_tags($_POST['shopCity']));
                $shopZip            = addslashes(strip_tags($_POST['shopZip']));
                $shopCountry        = intval($_POST['shopCountry']);
                $shopPhone          = addslashes(strip_tags($_POST['shopPhone']));
                $shopFax            = addslashes(strip_tags($_POST['shopFax']));
                $shopEmail          = addslashes(strip_tags($_POST['shopEmail']));
                $shopCcnumber       = addslashes(strip_tags($_POST['shopCcnumber']));
                $shopCcdate         = addslashes(strip_tags($_POST['shopCcdate']));
                $shopCcname         = addslashes(strip_tags($_POST['shopCcname']));
                $shopCvcCode        = addslashes(strip_tags($_POST['shopCvcCode']));
                $shopCompanyNote    = addslashes(strip_tags($_POST['shopCompanyNote']));
                $shopCustomerStatus = intval($_POST['shopCustomerStatus']);
                $shopIsReseller     = intval($_POST['shopCustomerClass']);
                $shopRegisterDate   = addslashes(strip_tags($_POST['shopRegisterDate']));
                $shopDiscountGroup  =
                    (isset($_POST['shopDiscountGroupCustomer'])
                        ? intval($_POST['shopDiscountGroupCustomer'])
                        : 0
                    );

                // insert the customer data
                $query = "
                    INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_customers
                        (username, password, prefix, company, firstname,
                        lastname, address, city, zip, country_id,
                        phone, fax, email,
                        ccnumber, ccdate, ccname, cvc_code,
                        company_note, customer_status,
                        is_reseller, register_date,
                        group_id)
                    VALUES
                        ('$shopUsername', '$shopMd5Password', '$shopPrefix',
                        '$shopCompany', '$shopFirstname', '$shopLastname',
                        '$shopAddress', '$shopCity', '$shopZip',
                        '$shopCountry', '$shopPhone', '$shopFax',
                        '$shopEmail', '$shopCcnumber', '$shopCcdate',
                        '$shopCcname', '$shopCvcCode', '$shopCompanyNote',
                        $shopCustomerStatus, $shopIsReseller, '$shopRegisterDate',
                        $shopDiscountGroup)
                ";
                if (!$objDatabase->Execute($query)) {
                    // if query has errors, call errorhandling
                    $this->errorHandling();
                } else {
                    $customerid = $objDatabase->Insert_ID();
                    self::addMessage($_ARRAYLANG['TXT_SHOP_INSERTED_CUSTOMER'].", ID $customerid");
                }
                //check if the logindata must be sent
                if (isset($_POST['shopSendLoginData'])) {
                    // Select template for sending login data
                    $arrShopMailtemplate = ShopLibrary::shopSetMailtemplate(3, $_FRONTEND_LANGID);
                    $shopMailTo = $_POST['shopEmail'];
                    $shopMailFrom = $arrShopMailtemplate['mail_from'];
                    $shopMailFromText = $arrShopMailtemplate['mail_x_sender'];
                    $shopMailSubject = $arrShopMailtemplate['mail_subject'];
                    $shopMailBody = $arrShopMailtemplate['mail_body'];
                    //replace variables from template
                    $shopMailBody = str_replace("<USERNAME>", $shopUsername, $shopMailBody);
                    $shopMailBody = str_replace("<PASSWORD>", $shopPassword, $shopMailBody);
                    // added
                    $shopMailBody = str_replace("<CUSTOMER_PREFIX>", $shopPrefix, $shopMailBody);
                    $shopMailBody = str_replace("<CUSTOMER_LASTNAME>", $shopLastname, $shopMailBody);
                    $result = ShopLibrary::shopSendmail($shopMailTo, $shopMailFrom, $shopMailFromText, $shopMailSubject, $shopMailBody);
                    if ($result) {
                        self::addMessage(sprintf($_ARRAYLANG['TXT_EMAIL_SEND_SUCCESSFULLY'], $shopMailTo));
                    } else {
                        self::addError($_ARRAYLANG['TXT_MESSAGE_SEND_ERROR']);
                        return false;
                    }
                }
            } else {
                self::addError($_ARRAYLANG['TXT_USERNAME_USED_BY_OTHER_CUSTOMER']);
                return false;
            }
        } else {
            self::addError($_ARRAYLANG['TXT_EMAIL_USED_BY_OTHER_CUSTOMER']);
            return false;
        }
        return true;
    }


    /**
     * Returns HTML code for a dropdown menu of ShopCategories
     *
     * Obsolete.  Use {@link ShopCategories::getShopCategoriesMenu()} or
     * {@link ShopCategories::getShopCategoriesMenuoptions()} instead.
     * @param    integer  $selectedid   The optional selected category ID
     * @return   string                 The HTML code of the menu
     */
    function getCatMenu($selectedid=0)
    {
        $result = $this->doShopCatMenu(0, 0, $selectedid);
        return $result;
    }


    /**
     * Returns the shop categories menu
     *
     * @version  1.0      initial version
     * @param    integer  $parcat
     * @param    integer  $level
     * @param    integer  $selectedid
     * @return   string   $result
     */
    function doShopCatMenu($parcat, $level, $selectedid)
    {
        global $objDatabase;

        $result="";
        $query = "SELECT catid, parentid, catname ".
            "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories ".
            "ORDER BY parentid ASC, catsorting ASC";
        if (($objResult = $objDatabase->Execute($query)) === false) {
            $this->errorHandling();
            return false;
        }
        while (!$objResult->EOF) {
            $navtable[$objResult->fields['parentid']][$objResult->fields['catid']] = $objResult->fields['catname'];
            $objResult->MoveNext();
        }

        $list=$navtable[$parcat];
        if (is_array($list)) {
            while (list($key, $val)=each($list)) {
                $selected = '';
                $output = str_repeat('...', $level);
                if ($selectedid == $key) {
                    $selected = "selected='selected'";
                }
                $result.= "<option value='$key' $selected>$output".htmlentities($val, ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
// fix: the following line produces infinite loops if parent == child
//                if (isset($navtable[$key])) {
                if ( ($key != $parcat) &&
                     (isset($navtable[$key])) ) {
                    $result.= $this->doShopCatMenu($key, $level+1, $selectedid);
                }
            }
        }
        return $result;
    }


    function _products()
    {
        global $_ARRAYLANG;

        self::$objTemplate->loadTemplateFile('module_shop_products.html',true,true);
        self::$objTemplate->setGlobalVariable(array(
            'TXT_ADD_PRODUCTS' => $_ARRAYLANG['TXT_ADD_PRODUCTS'],
            'TXT_PRODUCT_CATALOG' => $_ARRAYLANG['TXT_PRODUCT_CATALOG'],
            'TXT_PRODUCT_CHARACTERISTICS' => $_ARRAYLANG['TXT_PRODUCT_CHARACTERISTICS'],
            'TXT_DOWNLOAD_OPTIONS' => $_ARRAYLANG['TXT_DOWNLOAD_OPTIONS'],
            'TXT_SHOP_ARTICLE_GROUPS' => $_ARRAYLANG['TXT_SHOP_ARTICLE_GROUPS'],
            'TXT_SHOP_DISCOUNT_COUNT_GROUPS' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUNT_GROUPS'],
        ));
        if (!empty($_REQUEST['tpl'])) {
            $tpl = $_REQUEST['tpl'];
        } else {
            $tpl = '';
        }
        switch ($tpl) {
            case 'download':
                $this->_showProductDownloadOptions();
                break;
            case 'attributes':
                $this->_showProductAttributes();
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
        if (isset($_REQUEST['shopSaveProductAttributes'])) {
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
        self::$objTemplate->setGlobalVariable(array(
            'TXT_CONFIRM_DELETE_PRODUCT' => $_ARRAYLANG['TXT_CONFIRM_DELETE_PRODUCT'],
            'TXT_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
            'TXT_VIEW_SPECIAL_OFFERS' => $_ARRAYLANG['TXT_VIEW_SPECIAL_OFFERS'],
            'TXT_SEARCH' => $_ARRAYLANG['TXT_SEARCH'],
            'TXT_TOTAL' => $_ARRAYLANG['TXT_TOTAL'],
            'TXT_ID' => $_ARRAYLANG['TXT_ID'],
            'TXT_PRODUCT_NAME' => $_ARRAYLANG['TXT_PRODUCT_NAME'],
            'TXT_SEQUENCE' => $_ARRAYLANG['TXT_SEQUENCE'],
            'TXT_SHORT_DESCRIPTION' => $_ARRAYLANG['TXT_SHORT_DESCRIPTION'],
            'TXT_SPECIAL_OFFER' => $_ARRAYLANG['TXT_SPECIAL_OFFER'],
            'TXT_HP' => $_ARRAYLANG['TXT_HP'],
            'TXT_EKP' => $_ARRAYLANG['TXT_EKP'],
            'TXT_SHOP_VAT' => $_ARRAYLANG['TXT_SHOP_VAT'],
            'TXT_STATUS' => $_ARRAYLANG['TXT_STATUS'],
            'TXT_ACTION' => $_ARRAYLANG['TXT_ACTION'],
            'TXT_STOCK' => $_ARRAYLANG['TXT_STOCK'],
            'TXT_SHOP_PRODUCT_CUSTOM_ID' => $_ARRAYLANG['TXT_SHOP_PRODUCT_CUSTOM_ID'],
            'TXT_NAME' => $_ARRAYLANG['TXT_NAME'],
            'TXT_ACCEPT_CHANGES' => $_ARRAYLANG['TXT_ACCEPT_CHANGES'],
            'TXT_ALL_PRODUCT_GROUPS' => $_ARRAYLANG['TXT_ALL_PRODUCT_GROUPS'],
            'TXT_SHOP_EDIT' => $_ARRAYLANG['TXT_SHOP_EDIT'],
            'TXT_AS_TEMPLATE' => $_ARRAYLANG['TXT_AS_TEMPLATE'],
            'TXT_DELETE' => $_ARRAYLANG['TXT_DELETE'],
            'TXT_PREVIEW' => $_ARRAYLANG['TXT_PREVIEW'],
            'TXT_PRODUCT_CATALOG' => $_ARRAYLANG['TXT_PRODUCT_CATALOG'],
            'TXT_MARKED' => $_ARRAYLANG['TXT_MARKED'],
            'TXT_SELECT_ALL' => $_ARRAYLANG['TXT_SELECT_ALL'],
            'TXT_REMOVE_SELECTION' => $_ARRAYLANG['TXT_REMOVE_SELECTION'],
            'TXT_SELECT_ACTION' => $_ARRAYLANG['TXT_SELECT_ACTION'],
            'TXT_MAKE_SELECTION' => $_ARRAYLANG['TXT_MAKE_SELECTION'],
            'TXT_PRODUCT_STATUS' => $_ARRAYLANG['TXT_STATUS'],
            'TXT_DISTRIBUTION' => $_ARRAYLANG['TXT_DISTRIBUTION'],
            'TXT_SHOP_SHOW_PRODUCT_ON_START_PAGE' => $_ARRAYLANG['TXT_SHOP_SHOW_PRODUCT_ON_START_PAGE'],
            'TXT_SHOP_SHOW_PRODUCT_ON_START_PAGE_TIP' => htmlentities($_ARRAYLANG['TXT_SHOP_SHOW_PRODUCT_ON_START_PAGE_TIP'], ENT_QUOTES, CONTREXX_CHARSET),
//            'TXT_WEIGHT' => $_ARRAYLANG['TXT_WEIGHT'],
        ));

        $catId = 0;
        if (isset($_REQUEST['catId'])) {
            $catId = intval($_REQUEST['catId']);
        }
        $manufacturerId = 0;
        if (isset($_REQUEST['manufacturerId'])) {
            $manufacturerId = intval($_REQUEST['manufacturerId']);
        }
        $showOnlySpecialOffers = isset($_REQUEST['specialoffer']);
        $searchTerm = '';
        if (!empty($_REQUEST['shopSearchTerm'])) {
            $searchTerm = mysql_escape_string(
                trim(contrexx_stripslashes($_REQUEST['shopSearchTerm']))
            );
        }
        $pos   = isset($_GET['pos']) ? intval($_GET['pos']) : 0;
        $count = 0;
        // Mind that $count is handed over by reference.
        $arrProducts = Products::getByShopParams(
            $count, $pos, 0, $catId, $manufacturerId, $searchTerm,
            $showOnlySpecialOffers, false,
            self::$arrProductOrder[$this->arrConfig['product_sorting']['value']],
            '', true // Include inactive Products
        );
        $shopPagingLimit = intval($_CONFIG['corePagingLimit']);
        // Show paging if the Product count is greater than the page limit
        if ($count > $shopPagingLimit) {
            self::$objTemplate->setVariable(
                'SHOP_PRODUCT_PAGING',
                getPaging(
                    $count,
                    $pos,
                    '&amp;cmd=shop'.MODULE_INDEX.'&amp;act=products&amp;catId='.
                        $catId,
                        '<b>'.$_ARRAYLANG['TXT_PRODUCTS'].'</b>',
                    true
                )
            );
        }
        self::$objTemplate->setVariable(array(
            'SHOP_CAT_MENUOPTIONS' =>
                ShopCategories::getShopCategoriesMenuoptions($catId, false),
            'SHOP_SEARCH_TERM' => $searchTerm,
            'SHOP_PRODUCT_TOTAL' => $count,
        ));

        $i = 0;
        self::$objTemplate->setCurrentBlock('productRow');
        foreach ($arrProducts as $objProduct) {
            $productStatus = '';
            $productStatusValue = '';
            $productStatusPicture = 'status_red.gif';
            if ($objProduct->getStatus()) {
                $productStatus = ' checked="checked"';
                $productStatusValue = 1;
                $productStatusPicture = 'status_green.gif';
            }
            $specialOffer = '';
            $specialOfferValue = '';
            if ($objProduct->isSpecialoffer()) {
                $specialOffer = ' checked="checked"';
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
                        'taxId['.$objProduct->getId().']'
                    ),
                'SHOP_PRODUCT_VAT_ID' => ($objProduct->getVatId()
                        ? $objProduct->getVatId() : 'NULL'
                    ),
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
                'SHOP_SHOW_PRODUCT_ON_START_PAGE_CHECKED' => ($objProduct->isShownOnStartpage() ? ' checked="checked"' : ''),
                'SHOP_SHOW_PRODUCT_ON_START_PAGE_OLD' => ($objProduct->isShownOnStartpage() ? '1' : ''),
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
        foreach (array_keys($_POST['shopProductId']) as $id) {
            $shopProductIdentifier = $_POST['identifier'][$id];
            $shopProductIdentifierOld = $_POST['identifierOld'][$id];
            $shopSortOrder        = $_POST['shopSortOrder'][$id];
            $shopSortOrderOld     = $_POST['shopSortOrderOld'][$id];
            $shopSpecialOffer     = (isset($_POST['specialOffer'][$id]) ? 1 : 0);
            $shopSpecialOfferOld  = $_POST['specialOfferOld'][$id];
            $shopDiscount         = $_POST['discount'][$id];
            $shopDiscountOld      = $_POST['discountOld'][$id];
            $shopNormalprice      = $_POST['price1'][$id];
            $shopNormalpriceOld   = $_POST['price1Old'][$id];
            $shopResellerprice    = $_POST['price2'][$id];
            $shopResellerpriceOld = $_POST['price2Old'][$id];
            $shopStock            = $_POST['stock'][$id];
            $shopStockOld         = $_POST['stockOld'][$id];
            $shopStatus           = (isset($_POST['active'][$id]) ? 1 : 0);
            $shopStatusOld        = $_POST['activeOld'][$id];
            $shopTaxId            = (isset($_POST['taxId'][$id]) ? $_POST['taxId'][$id] : 0);
            $shopTaxIdOld         = $_POST['taxIdOld'][$id];
            $shownOnStartpage = (isset($_POST['shownonstartpage'][$id]) ? $_POST['shownonstartpage'][$id] : 0);
            $shownOnStartpageOld = (isset($_POST['shownonstartpageOld'][$id]) ? $_POST['shownonstartpageOld'][$id] : 0);
/*
    Distribution and weight have been removed from the overview due to the
    changes made to the delivery options.
            $shopDistribution     = $_POST['distribution'][$id];
            $shopDistributionOld  = $_POST['distributionOld'][$id];
            $shopWeight           = $_POST['weight'][$id];
            $shopWeightOld        = $_POST['weightOld'][$id];
            // Flag used to determine whether the record has to be
            // updated in the database
            $updateProduct = false;
            // Check whether the weight was changed
            if ($shopWeight != $shopWeightOld) {
                // Changed.
                // If it's empty, set to NULL and don't complain.
                // The NULL weight will be silently ignored by the database.
                if ($shopWeight == '') {
                    $shopWeight = 'NULL';
                } else {
                    // Check the format
                    $shopWeight = Weight::getWeight($shopWeight);
                    // The NULL weight will be silently ignored by the database.
                    if ($shopWeight === 'NULL') {
                        // 'NULL', the format was invalid. cast error
                        self::addError($_ARRAYLANG['TXT_WEIGHT_INVALID_IGNORED']);
                    } else {
                        // If getWeight() returns any other value, the format
                        // is valid.  Verify that the numeric value has changed
                        // as well; might be that the user simply removed the
                        // unit ('g').
                        if ($shopWeight != Weight::getWeight($shopWeightOld)) {
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
                $shopWeight = Weight::getWeight($shopWeightOld);
            }
*/

            // Check if any one value has been changed
            if (   $shopProductIdentifier != $shopProductIdentifierOld
                || $shopSortOrder != $shopSortOrderOld
                || $shopSpecialOffer != $shopSpecialOfferOld
                || $shopDiscount != $shopDiscountOld
                || $shopNormalprice != $shopNormalpriceOld
                || $shopResellerprice != $shopResellerpriceOld
                || $shopStock != $shopStockOld
                || $shopStatus != $shopStatusOld
                || $shopTaxId != $shopTaxIdOld
                || $shownOnStartpage != $shownOnStartpageOld
/*
                || $shopDistribution != $shopDistributionOld
                // Weight, see above
                || $updateProduct
*/
            ) {

                $arrProducts =
//                    ($shopProductIdentifierOld != ''
//                        ? Products::getByCustomId($shopProductIdentifierOld) :
                    array(Product::getById($id))
//                );
                    ;
                if (!is_array($arrProducts)) {
                    continue;
                }
                foreach ($arrProducts as $objProduct) {
                    if (!$objProduct) {
                        $arrError[$shopProductIdentifier] = true;
                        continue;
                    }
                    $objProduct->setCode($shopProductIdentifier);
                    $objProduct->setOrder($shopSortOrder);
                    $objProduct->setSpecialOffer($shopSpecialOffer);
                    $objProduct->setDiscountPrice($shopDiscount);
                    $objProduct->setPrice($shopNormalprice);
                    $objProduct->setResellerPrice($shopResellerprice);
                    $objProduct->setStock($shopStock);
                    $objProduct->setStatus($shopStatus);
                    $objProduct->setVatId($shopTaxId);
//                    $objProduct->setDistribution($shopDistribution);
//                    $objProduct->setWeight($shopWeight);
                    $objProduct->setShownOnStartpage($shownOnStartpage);
                    if (!$objProduct->store()) {
                        $arrError[$shopProductIdentifier] = true;
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
        $shopTotalSoldProducts = 0;
        $shopTotalOrderSum = 0.00;
        $shopTotalOrders = 0;
        $shopBestMonthSum = 0;
        $shopBestMonthDate = "";
        $shopOrders = false;
        $arrShopMonthSum = array();

        self::$objTemplate->loadTemplateFile("module_shop_statistic.html", true, true);

        //set general language variables
        self::$objTemplate->setVariable(array(
            'TXT_TOTAL_TURNOVER' => $_ARRAYLANG['TXT_TOTAL_TURNOVER'],
            'TXT_OVERVIEW' => $_ARRAYLANG['TXT_OVERVIEW'],
            'TXT_BEST_MONTH' => $_ARRAYLANG['TXT_BEST_MONTH'],
            'TXT_TURNOVER' => $_ARRAYLANG['TXT_TURNOVER'],
            'TXT_TOTAL_ORDERS' => $_ARRAYLANG['TXT_TOTAL_ORDERS'],
            'TXT_TOTAL_SOLD_ARITCLES' => $_ARRAYLANG['TXT_TOTAL_SOLD_ARITCLES'],
            'TXT_SELECT_STATISTIC' => $_ARRAYLANG['TXT_SELECT_STATISTIC'],
            'TXT_FROM' => $_ARRAYLANG['TXT_FROM'],
            'TXT_TO' => $_ARRAYLANG['TXT_TO'],
            'TXT_ORDERS' => $_ARRAYLANG['TXT_ORDERS'],
            'TXT_COUNT_ARTICLES' => $_ARRAYLANG['TXT_COUNT_ARTICLES'],
            'TXT_CUSTOMERS_PARTNERS' => $_ARRAYLANG['TXT_CUSTOMERS_PARTNERS'],
            'TXT_PERIOD' => $_ARRAYLANG['TXT_PERIOD'],
            'TXT_PERFORM' => $_ARRAYLANG['TXT_PERFORM'],
            'TXT_ORDER_SUM' => $_ARRAYLANG['TXT_ORDER_SUM'],
            'TXT_SUM' => $_ARRAYLANG['TXT_SUM'],
        ));
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
            $shopOrders = true;
            $shopOrderStartyear = $objResult->fields['year'];
            $shopOrderStartmonth = $objResult->fields['month'];
        }
        $i = 0;
        if ($shopOrders) { //some orders has been made
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
                    $shopTotalOrderSum += $orderSum;
                    $shopTotalOrders++;
                    $objResult->MoveNext();
                }
                $months = explode(',', $_ARRAYLANG['TXT_MONTH_ARRAY']);
                foreach ($arrShopMonthSum as $year => $arrMonth) {
                    foreach ($arrMonth as $month => $sum) {
                        if ($shopBestMonthSum < $sum) {
                            $shopBestMonthSum = $sum;
                            $shopBestMonthDate = $months[$month-1].' '.$year;
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
                    $shopTotalSoldProducts = $objResult->fields['shopTotalSoldProducts'];
                    $objResult->MoveNext();
                }
            }

            //if an timeperiod is set, set the start and stop date
            if (isset($_REQUEST['shopSubmitDate'])) {
                self::$objTemplate->setVariable('SHOP_START_MONTH',$this->shop_getMonthDropdwonMenu(intval($_REQUEST['shopStartMonth'])));
                self::$objTemplate->setVariable('SHOP_END_MONTH',$this->shop_getMonthDropdwonMenu(intval($_REQUEST['shopStopMonth'])));
                self::$objTemplate->setVariable('SHOP_START_YEAR',$this->shop_getYearDropdwonMenu($shopOrderStartyear,intval($_REQUEST['shopStartYear'])));
                self::$objTemplate->setVariable('SHOP_END_YEAR',$this->shop_getYearDropdwonMenu($shopOrderStartyear,intval($_REQUEST['shopStopYear'])));
                $shopStartDate = intval($_REQUEST['shopStartYear'])."-".sprintf("%02s",intval($_REQUEST['shopStartMonth']))."-01 00:00:00";
                $shopStopDate = intval($_REQUEST['shopStopYear'])."-".sprintf("%02s",intval($_REQUEST['shopStopMonth']))."-".date('t',mktime(0,0,0,intval($_REQUEST['shopStopMonth']),1,intval($_REQUEST['shopStopYear'])))." 23:59:59";
            } else {   //set timeperiod to max. one year
                $shopLastYear = Date('Y');
                if ($shopOrderStartyear < Date('Y')) {
                    $shopOrderStartmonth  = Date('m');
                    $shopLastYear = Date('Y')-1;
                }
                $shopEndMonth = Date('m');
                self::$objTemplate->setVariable('SHOP_START_MONTH', $this->shop_getMonthDropdwonMenu($shopOrderStartmonth));
                self::$objTemplate->setVariable('SHOP_END_MONTH', $this->shop_getMonthDropdwonMenu($shopEndMonth));
                self::$objTemplate->setVariable('SHOP_START_YEAR', $this->shop_getYearDropdwonMenu($shopOrderStartyear, $shopLastYear));
                self::$objTemplate->setVariable('SHOP_END_YEAR', $this->shop_getYearDropdwonMenu($shopOrderStartyear,Date('Y')));
                $shopStartDate = $shopLastYear."-".$shopOrderStartmonth."-01 00:00:00";
                $shopStopDate = date('Y')."-".$shopEndMonth."-".date('t',mktime(0,0,0, $shopEndMonth,1,date('Y')))." 23:59:59";
            }
            //check if an statistic has been requested
            $shopSelectedStat =
                (isset($_REQUEST['shopSelectStats'])
                    ? intval($_REQUEST['shopSelectStats'])
                    : 0
                );
            if ($shopSelectedStat == 2) {
                //query for articles stats
                self::$objTemplate->setVariable(array(
                    'TXT_COLUMN_1_DESC' => $_ARRAYLANG['TXT_PRODUCT_NAME'],
                    'TXT_COLUMN_2_DESC' => $_ARRAYLANG['TXT_COUNT_ARTICLES'],
                    'TXT_COLUMN_3_DESC' => $_ARRAYLANG['TXT_STOCK'],
                    'SHOP_ORDERS_SELECTED' => "",
                    'SHOP_ARTICLES_SELECTED' => "selected=\"selected\"",
                    'SHOP_CUSTOMERS_SELECTED' => "",
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
                        AND C.order_date >= '$shopStartDate'
                        AND C.order_date <= '$shopStopDate'
                        AND (   C.order_status=".SHOP_ORDER_STATUS_CONFIRMED."
                             OR C.order_status=".SHOP_ORDER_STATUS_COMPLETED.")
                      ORDER BY shopColumn2 DESC";
            } elseif ( $shopSelectedStat ==3) {
                //query for customers stats
                self::$objTemplate->setVariable(array(
                    'TXT_COLUMN_1_DESC' => $_ARRAYLANG['TXT_NAME'],
                    'TXT_COLUMN_2_DESC' => $_ARRAYLANG['TXT_COMPANY'],
                    'TXT_COLUMN_3_DESC' => $_ARRAYLANG['TXT_COUNT_ARTICLES'],
                    'SHOP_ORDERS_SELECTED' => "",
                    'SHOP_ARTICLES_SELECTED' => "",
                    'SHOP_CUSTOMERS_SELECTED' => "selected=\"selected\"",
                ));
                $query = "
                    SELECT A.currency_order_sum AS sum, A.selected_currency_id AS currency_id, C.company AS shopColumn2,sum(B.quantity) AS shopColumn3, C.lastname As lastname, C.firstname AS firstname, C.prefix AS prefix, C.customerid AS id
                      FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders AS A,
                           ".DBPREFIX."module_shop".MODULE_INDEX."_order_items AS B,
                           ".DBPREFIX."module_shop".MODULE_INDEX."_customers AS C
                     WHERE A.orderid=B.orderid
                       AND A.customerid=C.customerid
                       AND A.order_date>='$shopStartDate'
                       AND A.order_date<='$shopStopDate'
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
                    'SHOP_ORDERS_SELECTED' => "selected=\"selected\"",
                    'SHOP_ARTICLES_SELECTED' => "",
                    'SHOP_CUSTOMERS_SELECTED' => "",
                ));
                $query = "
                    SELECT sum(A.quantity) AS shopColumn3,
                           count(A.orderid) AS shopColumn2,
                           B.selected_currency_id, B.currency_order_sum AS sum,
                           DATE_FORMAT(B.order_date,'%m') AS month,
                           DATE_FORMAT(B.order_date,'%Y') AS year
                      FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items AS A,
                           ".DBPREFIX."module_shop".MODULE_INDEX."_orders AS B
                     WHERE A.orderid = B.orderid
                       AND B.order_date >= '$shopStartDate'
                       AND B.order_date <= '$shopStopDate'
                       AND (   B.order_status=".SHOP_ORDER_STATUS_CONFIRMED."
                            OR B.order_status=".SHOP_ORDER_STATUS_COMPLETED.")
                     GROUP BY B.orderid
                     ORDER BY year, month DESC";
            }

            $arrayResults = array();
            if (($objResult = $objDatabase->Execute($query)) === false) {    //execute the query again with paging limit set
                $this->errorHandling();
            } else {
                if ($shopSelectedStat == 2) { //it's the article statistc
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
                        $arrayResults[$key]['column1'] = "<a href='index.php?cmd=shop".MODULE_INDEX."&amp;act=products&amp;tpl=manage&amp;id=".$objResult->fields['id']."' title=\"".$objResult->fields['title']."\">".$objResult->fields['title']."</a>";
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
                } elseif ($shopSelectedStat == 3) {
                    //is customer statistic
                    while (!$objResult->EOF) {
                        // set currency id
                        Currency::setActiveCurrencyId($objResult->fields['currency_id']);

                        $key = $objResult->fields['id'];
                        $shopCustomerName = ltrim($objResult->fields['prefix'].' '.$objResult->fields['firstname'].' '.$objResult->fields['lastname']);
                        if (!isset($arrayResults[$key])) {
                            $arrayResults[$key] = array(
                                'column1' => '',
                                'column2' => 0,
                                'column3' => 0,
                                'column4' => 0,
                            );
                        }
                        $arrayResults[$key]['column1'] = "<a href='index.php?cmd=shop".MODULE_INDEX."&amp;act=customerdetails&amp;customerid=".$objResult->fields['id']."'>$shopCustomerName</a>";
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
                              'SHOP_COLUMN_4' => Currency::formatPrice($entry['column4']).' '.
                                  Currency::getDefaultCurrencySymbol(),
                        ));
                        self::$objTemplate->parse('statisticRow');
                    }
                }
            }
        } else {
            $sumColumn2 = 0;
            $arrayMonths=explode(',', $_ARRAYLANG['TXT_MONTH_ARRAY']);
            $shopActualMonth = "<option value=\"".Date('m')."\">".$arrayMonths[Date('m')-1]."</option>\n";
            $shopActualYear = "<option value=\"".Date('Y')."\">".Date('Y')."</option>\n";
            self::$objTemplate->setVariable(array(
                'SHOP_START_MONTH' => $shopActualMonth,
                'SHOP_END_MONTH' => $shopActualMonth,
                'SHOP_START_YEAR' => $shopActualYear,
                'SHOP_END_YEAR' => $shopActualYear,
                'TXT_COLUMN_1_DESC' => $_ARRAYLANG['TXT_DATE'],
                'TXT_COLUMN_2_DESC' => $_ARRAYLANG['TXT_COUNT_ORDERS'],
                'TXT_COLUMN_3_DESC' => $_ARRAYLANG['TXT_COUNT_ARTICLES'],
                'SHOP_ORDERS_SELECTED' => "selected=\"selected\"",
                'SHOP_ARTICLES_SELECTED' => "",
                'SHOP_CUSTOMERS_SELECTED' => "",
            ));
        }
        //set the variables for the sum
        self::$objTemplate->setVariable(array(
            'SHOP_ROWCLASS' => (++$i % 2 ? 'row1' : 'row2'),
            'SHOP_TOTAL_SUM' => Currency::formatPrice($shopTotalOrderSum).' '.Currency::getDefaultCurrencySymbol(),
            'SHOP_MONTH' => $shopBestMonthDate,
            'SHOP_MONTH_SUM' => Currency::formatPrice($shopBestMonthSum).' '.Currency::getDefaultCurrencySymbol(),
            'SHOP_TOTAL_ORDERS' => $shopTotalOrders,
            'SHOP_SOLD_ARTICLES' => $shopTotalSoldProducts,
            'SHOP_SUM_COLUMN_2' => $sumColumn2,
            'SHOP_SUM_COLUMN_3' => $sumColumn3,
            'SHOP_SUM_COLUMN_4' => Currency::formatPrice($sumColumn4).' '.Currency::getDefaultCurrencySymbol(),
            'SHOP_STATISTIC_PAGING' => $paging
        ));
    }


    function shop_getMonthDropdwonMenu($selectedOption='')
    {
        global $_ARRAYLANG;

        $strMenu  = '';
        $months   = explode(',', $_ARRAYLANG['TXT_MONTH_ARRAY']);
        foreach ($months as $index => $name) {
            $shopMonthNumber = $index + 1;
            $strMenu .=
                "<option value='$shopMonthNumber'".
                ($selectedOption == $shopMonthNumber
                    ?   ' selected="selected"'
                    :   ''
                ).
                ">$name</option>\n";
        }
        return $strMenu;
    }


    function shop_getYearDropdwonMenu($shopStartYear, $selectedOption='')
    {
        $strMenu     = '';
        $shopYearNow = date('Y');
        while ($shopStartYear <= $shopYearNow) {
            $strMenu .=
                "<option value='$shopStartYear'".
                ($selectedOption == $shopStartYear
                    ?   ' selected="selected"'
                    :   ''
                ).
                ">$shopStartYear</option>\n";
            ++$shopStartYear;
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
        self::$objTemplate->setVariable(array(
            'TXT_CONFIRM_DELETE_ORDER' => $_ARRAYLANG['TXT_CONFIRM_DELETE_ORDER'],
            'TXT_DELETE_PRICELIST_MSG' => $_ARRAYLANG['TXT_DELETE_PRICELIST_MSG'],
            'TXT_ID' => $_ARRAYLANG['TXT_ID'],
            'TXT_NAME' => $_ARRAYLANG['TXT_NAME'],
            'TXT_PDF_LINK' => $_ARRAYLANG['TXT_PDF_LINK'],
            'TXT_ACTION' => $_ARRAYLANG['TXT_ACTION'],
            'TXT_MAKE_NEW_PRICELIST' => $_ARRAYLANG['TXT_MAKE_NEW_PRICELIST'],
            'TXT_MAKE_SELECTION' => $_ARRAYLANG['TXT_MAKE_SELECTION'],
            'TXT_MARKED' => $_ARRAYLANG['TXT_MARKED'],
            'TXT_SELECT_ALL' => $_ARRAYLANG['TXT_SELECT_ALL'],
            'TXT_REMOVE_SELECTION' => $_ARRAYLANG['TXT_REMOVE_SELECTION'],
            'TXT_SELECT_ACTION' => $_ARRAYLANG['TXT_SELECT_ACTION']
        ));

        self::$objTemplate->setGlobalVariable(array(
            'TXT_EDIT' => $_ARRAYLANG['TXT_EDIT'],
            'TXT_DELETE' => $_ARRAYLANG['TXT_DELETE']
        ));

        $row_color = 0;

        $query = "SELECT id, name FROM ".DBPREFIX."module_shop".MODULE_INDEX."_pricelists ORDER BY name ASC";
        $objResult = $objDatabase->Execute($query);
        if ($objResult->RecordCount() > 0) { // there's a row in the database
            self::$objTemplate->setCurrentBlock('showPricelists');
            while (!$objResult->EOF) {
                if ($row_color % 2 == 0) {
                    self::$objTemplate->setVariable('PRICELIST_OVERVIEW_ROWCOLOR', 'row2');
                } else {
                    self::$objTemplate->setVariable('PRICELIST_OVERVIEW_ROWCOLOR', 'row1');
                }
                self::$objTemplate->setVariable(array(
                'PRICELIST_OVERVIEW_ID' => $objResult->fields['id'],
                'PRICELIST_OVERVIEW_NAME' => $objResult->fields['name'],
                'PRICELIST_OVERVIEW_PDFLINK' => "<a href='".ASCMS_PATH_OFFSET.'/modules/shop/pdf.php?plid='.$objResult->fields['id']."' target='_blank' title='".$_ARRAYLANG['TXT_DISPLAY']."'>".
                'http://'.$_SERVER['HTTP_HOST'].ASCMS_PATH_OFFSET.'/modules/shop/pdf.php?plid='.$objResult->fields['id'].'</a>'));

                self::$objTemplate->parse('showPricelists');
                $row_color++;
                $objResult->MoveNext();
            }
        } else {
            self::$objTemplate->hideBlock('shopPricelistOverview');
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

        self::$objTemplate->setVariable(array(
        'TXT_GENERAL_SETTINGS' => $_ARRAYLANG['TXT_GENERAL_SETTINGS'],
        'TXT_PDF_LINK' => $_ARRAYLANG['TXT_PDF_LINK'],
        'TXT_NAME' => $_ARRAYLANG['TXT_NAME'],
        'TXT_LANGUAGE' => $_ARRAYLANG['TXT_LANGUAGE'],
        'TXT_FRAME' => $_ARRAYLANG['TXT_FRAME'],
        'TXT_DISPLAY' => $_ARRAYLANG['TXT_DISPLAY'],
        'TXT_DONT_DISPLAY' => $_ARRAYLANG['TXT_DONT_DISPLAY'],
        'TXT_HEADER' => $_ARRAYLANG['TXT_HEADER'],
        'TXT_FOOTER' => $_ARRAYLANG['TXT_FOOTER'],
        'TXT_DATE' => $_ARRAYLANG['TXT_DATE'],
        'TXT_PAGENUMBER' => $_ARRAYLANG['TXT_PAGENUMBER'],
        'TXT_PRODUCTSELECTION' => $_ARRAYLANG['TXT_PRODUCTSELECTION'],
        'TXT_ALL_PRODUCTS' => $_ARRAYLANG['TXT_ALL_PRODUCTS'],
        'TXT_SEPERATE_PRODUCTS' => $_ARRAYLANG['TXT_SEPERATE_PRODUCTS'],
        'TXT_STORE_PRODUCT_LIST' => $_ARRAYLANG['TXT_STORE_PRODUCT_LIST']
        ));

        // generate langauge menu
        $langMenu = "<select name=\"langId\" size=\"1\">\n";
        $query = "SELECT id, name, is_default FROM ".DBPREFIX."languages WHERE backend=1";
        if (($objResult = $objDatabase->Execute($query)) !== false) {
            while (!$objResult->EOF) {
                $langMenu .= "<option value=\"".$objResult->fields['id']."\"".($objResult->fields['is_default'] == 'true' ? " selected=\"selected\"" : "").">".$objResult->fields['name']."</option>\n";
                $objResult->MoveNext();
            }
        }
        $langMenu .= "</select>\n";

        self::$objTemplate->setVariable(array(
        'SHOP_PRICELIST_DETAILS_PLID' => 'new',
        'SHOP_PRICELIST_DETAILS_ACT' => 'pricelist_insert',
        'SHOP_PRICELIST_PDFLINK' => '&nbsp;',
        'SHOP_PRICELIST_DETAILS_NAME' => '',
        'SHOP_PRICELIST_DETAILS_BORDERON' => ' checked="checked"',
        'SHOP_PRICELIST_DETAILS_BORDEROFF' => '',
        'SHOP_PRICELIST_DETAILS_HEADERON' => ' checked="checked"',
        'SHOP_PRICELIST_DETAILS_HEADEROFF' => '',
        'SHOP_PRICELIST_DETAILS_HEADERLEFT' => '',
        'SHOP_PRICELIST_DETAILS_HEADERRIGHT' => '',
        'SHOP_PRICELIST_DETAILS_FOOTERON' => ' checked="checked"',
        'SHOP_PRICELIST_DETAILS_FOOTEROFF' => '',
        'SHOP_PRICELIST_DETAILS_FOOTERLEFT' => '',
        'SHOP_PRICELIST_DETAILS_FOOTERRIGHT' => '',
        'SHOP_PRICELIST_DETAILS_ALLPROD' => ' checked="checked"',
        'SHOP_PRICELIST_DETAILS_SEPPROD' => '',
        'SHOP_PRICELIST_DETAILS_LANGUAGE' => $langMenu
        ));

        $selectedCategories = '*';

        self::$objTemplate->setCurrentBlock('showShopCategories');
        self::$objTemplate->setCurrentBlock('showShopCategories2');
        self::$objTemplate->setCurrentBlock('showShopCategories3');

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

        $row_color = 0;
        $query = "
            SELECT catid,catname
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
             WHERE parentid=0
             ORDER BY catsorting ASC
        ";
        $objResult = $objDatabase->Execute($query);
        while (!$objResult->EOF) {
            self::$objTemplate->setVariable('PDF_CATEGORY_NAME', $objResult->fields['catname']);
            if ($selectedCategories == '*') {
                self::$objTemplate->setVariable('PDF_CATEGORY_DISABLED', ' disabled="disabled"');
                self::$objTemplate->setVariable('PDF_CATEGORY_CHECKED', '');
            } else {
                self::$objTemplate->setVariable('PDF_CATEGORY_DISABLED', '');
                self::$objTemplate->setVariable('PDF_CATEGORY_CHECKED', ''); //empty the field

                foreach ($selectedCategories as $checkedValue) {
                    if ($objResult->fields['catid'] == $checkedValue) { // this field is checked
                        self::$objTemplate->setVariable('PDF_CATEGORY_CHECKED', ' checked="checked"');
                    }
                }
            }
            self::$objTemplate->setVariable(array(
                'PDF_CATEGORY_ID' => $objResult->fields['catid'],
                'PDF_CATEGORY_ID2' => $objResult->fields['catid'],
                'PDF_CATEGORY_ID3' => $objResult->fields['catid'],
                'CATEGORY_OVERVIEW_ROWCOLOR' => (++$row_color % 2 ? 'row1' : 'row2'),
            ));
            self::$objTemplate->parse('showShopCategories');
            self::$objTemplate->parse('showShopCategories2');
            self::$objTemplate->parse('showShopCategories3');
            $objResult->MoveNext();
        }
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
                $this->doCategoryTreeActiveOnly($value);
                foreach (array_keys($this->categoryTreeName) as $catKey) {
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
                lang_id=".intval($_POST['langId']).",
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
        self::$objTemplate->setVariable(array(
            'TXT_GENERAL_SETTINGS' => $_ARRAYLANG['TXT_GENERAL_SETTINGS'],
            'TXT_PDF_LINK' => $_ARRAYLANG['TXT_PDF_LINK'],
            'TXT_NAME' => $_ARRAYLANG['TXT_NAME'],
            'TXT_LANGUAGE' => $_ARRAYLANG['TXT_LANGUAGE'],
            'TXT_FRAME' => $_ARRAYLANG['TXT_FRAME'],
            'TXT_DISPLAY' => $_ARRAYLANG['TXT_DISPLAY'],
            'TXT_DONT_DISPLAY' => $_ARRAYLANG['TXT_DONT_DISPLAY'],
            'TXT_HEADER' => $_ARRAYLANG['TXT_HEADER'],
            'TXT_FOOTER' => $_ARRAYLANG['TXT_FOOTER'],
            'TXT_DATE' => $_ARRAYLANG['TXT_DATE'],
            'TXT_PAGENUMBER' => $_ARRAYLANG['TXT_PAGENUMBER'],
            'TXT_PRODUCTSELECTION' => $_ARRAYLANG['TXT_PRODUCTSELECTION'],
            'TXT_ALL_PRODUCTS' => $_ARRAYLANG['TXT_ALL_PRODUCTS'],
            'TXT_SEPERATE_PRODUCTS' => $_ARRAYLANG['TXT_SEPERATE_PRODUCTS'],
            'TXT_STORE_PRODUCT_LIST' => $_ARRAYLANG['TXT_STORE_PRODUCT_LIST']
        ));

        $objResult = $objDatabase->Execute("
            SELECT *
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_pricelists
             WHERE id=$pricelistID
        ");
        $lang_id = $objResult->fields['lang_id'];
        self::$objTemplate->setVariable(array(
            'SHOP_PRICELIST_DETAILS_ACT' => 'pricelist_update&amp;id='.$objResult->fields['id'],
            'SHOP_PRICELIST_PDFLINK' => '<a href="'.ASCMS_PATH_OFFSET.'/modules/shop/pdf.php?plid='.
                $objResult->fields['id'].'" target="_blank" title="PDF">'.
                'http://'.$_SERVER['HTTP_HOST'].ASCMS_PATH_OFFSET.
                '/modules/shop/pdf.php?plid='.$objResult->fields['id'].'</a>',
            'SHOP_PRICELIST_DETAILS_NAME' => $objResult->fields['name'],
        ));
        //are the borders on?
        if ($objResult->fields['border_on'] == 1) {
            self::$objTemplate->setVariable('SHOP_PRICELIST_DETAILS_BORDERON', ' checked="checked"');
            self::$objTemplate->setVariable('SHOP_PRICELIST_DETAILS_BORDEROFF', '');
        } else {
            self::$objTemplate->setVariable('SHOP_PRICELIST_DETAILS_BORDERON', '');
            self::$objTemplate->setVariable('SHOP_PRICELIST_DETAILS_BORDEROFF', ' checked="checked"');
        }
        //is the header on?
        if ($objResult->fields['header_on'] == 1) {
            self::$objTemplate->setVariable('SHOP_PRICELIST_DETAILS_HEADERON', ' checked="checked"');
            self::$objTemplate->setVariable('SHOP_PRICELIST_DETAILS_HEADEROFF', '');
            self::$objTemplate->setVariable('SHOP_PRICELIST_DETAILS_HEADERLEFT', $objResult->fields['header_left']);
            self::$objTemplate->setVariable('SHOP_PRICELIST_DETAILS_HEADERRIGHT', $objResult->fields['header_right']);
        } else {
            self::$objTemplate->setVariable('SHOP_PRICELIST_DETAILS_HEADERON', '');
            self::$objTemplate->setVariable('SHOP_PRICELIST_DETAILS_HEADEROFF', ' checked="checked"');
        }
        //is the footer on?
        if ($objResult->fields['footer_on'] == 1) {
            self::$objTemplate->setVariable('SHOP_PRICELIST_DETAILS_FOOTERON', ' checked="checked"');
            self::$objTemplate->setVariable('SHOP_PRICELIST_DETAILS_FOOTEROFF', '');
            self::$objTemplate->setVariable('SHOP_PRICELIST_DETAILS_FOOTERLEFT', $objResult->fields['footer_left']);
            self::$objTemplate->setVariable('SHOP_PRICELIST_DETAILS_FOOTERRIGHT', $objResult->fields['footer_right']);
        } else {
            self::$objTemplate->setVariable('SHOP_PRICELIST_DETAILS_FOOTERON', '');
            self::$objTemplate->setVariable('SHOP_PRICELIST_DETAILS_FOOTEROFF', ' checked="checked"');
        }
        //which products were selected before? All or seperate?
        if ($objResult->fields['categories'] == '*') { // all categories
            self::$objTemplate->setVariable('SHOP_PRICELIST_DETAILS_ALLPROD', ' checked="checked"');
            self::$objTemplate->setVariable('SHOP_PRICELIST_DETAILS_SEPPROD', '');
            $arrSelectedCategories = '*';
        } else {
            // I have to split the string into a nice array :)
            self::$objTemplate->setVariable('SHOP_PRICELIST_DETAILS_ALLPROD', '');
            self::$objTemplate->setVariable('SHOP_PRICELIST_DETAILS_SEPPROD', ' checked="checked"');

            $selectedCategories = explode(',', $objResult->fields['categories']);
            $query = "SELECT catid ".
                     "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories ".
                     "WHERE parentid = 0";
            $objResult = $objDatabase->Execute($query);
            while (!$objResult->EOF) {
                $arrMainCategories[$objResult->fields['catid']] = $objResult->fields['catid'];
                $objResult->MoveNext();
            }
            foreach ($selectedCategories as $value) {
                foreach ($arrMainCategories as $mainValue) {
                    if ($value == $mainValue) {
                        $arrSelectedCategories[$mainValue] = $mainValue;
                    }
                }
            }
        }

        // generate langauge menu
        $langMenu = "<select name=\"langId\" size=\"1\">\n";
        $query = "SELECT id, name, is_default FROM ".DBPREFIX."languages WHERE backend=1";
        if (($objResult = $objDatabase->Execute($query)) !== false) {
            while (!$objResult->EOF) {
                $langMenu .= "<option value=\"".$objResult->fields['id']."\"".($objResult->fields['id'] == $lang_id ? " selected=\"selected\"" : "").">".$objResult->fields['name']."</option>\n";
                $objResult->MoveNext();
            }
        }
        $langMenu .= "</select>\n";
        self::$objTemplate->setVariable('SHOP_PRICELIST_DETAILS_LANGUAGE', $langMenu);

        self::$objTemplate->setCurrentBlock('showShopCategories');
        self::$objTemplate->setCurrentBlock('showShopCategories2');
        self::$objTemplate->setCurrentBlock('showShopCategories3');
        $this->shopPricelistMainCategories($arrSelectedCategories);
    }


    /**
     * Update a pricelist entry in the database
     *
     * @global  var     $objDatabase
     * @global  array   $_ARRAYLANG
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
                $this->doCategoryTreeActiveOnly($value);
                foreach (array_keys($this->categoryTreeName) as $catKey) {
                    $selectedCategories .=
                        ($selectedCategories ? ',' : '').$catKey;
                }
                $selectedCategories .=
                    ($selectedCategories ? ',' : '').$value;
            }
            if (empty($selectedCategories)) $selectedCategories = '*';
        }
        if (empty($_POST['pricelistName']))
            $_POST['pricelistName'] = $_ARRAYLANG['TXT_NO_NAME'];
        $query = "
            UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_pricelists
               SET name='".addslashes(strip_tags($_POST['pricelistName']))."',
                   lang_id=".intval($_POST['langId']).",
                   border_on=".intval($_POST['borderOn']).",
                   header_on=".intval($_POST['headerOn']).",
                   header_left='".addslashes(trim($_POST['headerTextLeft']))."',
                   header_right='".addslashes(trim($_POST['headerTextRight']))."',
                   footer_on=".intval($_POST['footerOn']).",
                   footer_left='".addslashes(trim($_POST['footerTextLeft']))."',
                   footer_right='".addslashes(trim($_POST['footerTextRight']))."',
                   categories='".addslashes($selectedCategories)."'
             WHERE id=$pricelistID
        ";
        $objDatabase->Execute($query);
        self::addMessage($_ARRAYLANG['TXT_PRODUCT_LIST_UPDATED_SUCCESSFUL']);
    }


    /**
     * Delete a pricelist
     *
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

        if (count($arrPricelistId)>0) {
            foreach ($arrPricelistId as $plId) {
                $query = "
                    DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_pricelists
                     WHERE id=".intval($plId)
                ;
                if ($objDatabase->Execute($query)) {
                    self::addMessage($_ARRAYLANG['TXT_PRICELIST_MESSAGE_DELETED']);
                } else {
                    $this->errorHandling();
                }
            }
        }
    }


    /**
     * Return HTML code for the Manufacturer dropdown menu
     * @static
     * @param   integer $selectedId     The optional selected Manufacturer ID
     * @return  string                  The HTML code string
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @global  array   $_ARRAYLANG
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getManufacturerMenu($selectedId=0)
    {
        global $objDatabase, $_ARRAYLANG;

        $query = '
            SELECT id, name, url
              FROM '.DBPREFIX.'module_shop'.MODULE_INDEX.'_manufacturer
          ORDER BY name
        ';
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            $this->errorHandling();
        }

        $strMenu =
            '<select name="shopManufacturerId" style="width: 220px;">'.
            ($selectedId == 0
              ? '<option value="0">'.
                $_ARRAYLANG['TXT_SHOP_PLEASE_SELECT'].
                '</option>'
              : ''
            );
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $strMenu .=
                '<option value="'.$id.'"'.
                ($selectedId == $id ? ' selected="selected"' : '').
                '>'.$objResult->fields['name'].'</option>';
            $objResult->MoveNext();
        }
        $strMenu .= '</select>';
        return $strMenu;
    }


    /**
     * Send a confirmation mail to the Customer for the given Order ID.
     * @param   integer   $orderId      The order ID
     * @return  string                  The target e-mail address on success,
     *                                   the empty string otherwise
     */
    function sendConfirmationMail($orderId)
    {
        global $objDatabase, $_ARRAYLANG;

        // Determine the customer language ID
        $query = "
            SELECT email, last_modified, customer_lang, order_status, order_date
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders
             INNER JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_customers
             USING (customerid)
             WHERE orderid=$orderId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->RecordCount() == 0) {
            // Order not found
            return false;
        }
        $langId = $objResult->fields['customer_lang'];
        // Compatibility with old behavior of storing the ISO639-1 code
        if (!intval($langId))
            $langId = FWLanguage::getLangIdByIso639_1($langId);
        $mailTo = $objResult->fields['email'];
        $orderStatus = $objResult->fields['order_status'];
        $lastModified = $objResult->fields['last_modified'];
        // Select template for order confirmation
        $arrShopMailtemplate = Mail::getTemplate(2, $langId);
        $mailFrom = $arrShopMailtemplate['from'];
        $mailFromText = $arrShopMailtemplate['sender'];
        $mailSubject = $arrShopMailtemplate['subject'];
        $mailSubject = str_replace('<DATE>', $lastModified, $mailSubject);
        $mailSubject = str_replace(
            '<ORDER_STATUS>',
            $_ARRAYLANG['TXT_SHOP_ORDER_STATUS_'.$orderStatus],
            $mailSubject
        );
        $mailBody = $arrShopMailtemplate['message'];
        $mailBody = self::substituteOrderData($mailBody, $orderId);
        if (!Mail::send($mailTo, $mailFrom, $mailFromText, $mailSubject, $mailBody))
            return false;
        return true;
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
        self::$objTemplate->setGlobalVariable(array(
            'TXT_OVERVIEW' => $_ARRAYLANG['TXT_OVERVIEW'],
            'TXT_SHOP_CUSTOMER_GROUPS' => $_ARRAYLANG['TXT_SHOP_CUSTOMER_GROUPS'],
            'TXT_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
            'TXT_DELETE' => $_ARRAYLANG['TXT_DELETE'],
            'TXT_EDIT' => $_ARRAYLANG['TXT_EDIT'],
            'TXT_FUNCTIONS' => $_ARRAYLANG['TXT_FUNCTIONS'],
            'TXT_SHOP_CONFIRM_DELETE_DISCOUNT_GROUP' => $_ARRAYLANG['TXT_SHOP_CONFIRM_DELETE_DISCOUNT_GROUP'],
            'TXT_SHOP_DISCOUNT_GROUPS' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_GROUPS'],
            'TXT_SHOP_DISCOUNT_GROUP_NAME' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_GROUP_NAME'],
            'TXT_SHOP_DISCOUNT_GROUP_UNIT' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_GROUP_UNIT'],
            'TXT_SHOP_DISCOUNT_MIN_COUNT' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_MIN_COUNT'],
            'TXT_SHOP_DISCOUNT_RATE' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_RATE'],
            'TXT_SHOP_EDIT_DISCOUNT_GROUP' => $_ARRAYLANG['TXT_SHOP_EDIT_DISCOUNT_GROUP'],
            'TXT_SHOP_DISCOUNT_COUNT_TIP' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUNT_TIP'],
            'TXT_STORE' => $_ARRAYLANG['TXT_STORE'],
            'TXT_SHOP_CANCEL' => $_ARRAYLANG['TXT_SHOP_CANCEL'],
        ));

        // Discounts overview
        $arrDiscounts = Discount::getDiscountCountArray();
        self::$objTemplate->setCurrentBlock('shopDiscount');
        $i = 0;
        foreach ($arrDiscounts as $id => $arrDiscount) {
            $name = $arrDiscount['name'];
            $unit = $arrDiscount['unit'];
            self::$objTemplate->setVariable(array(
                'SHOP_DISCOUNT_ID' => $id,
                'SHOP_DISCOUNT_GROUP_NAME' => $name,
                'SHOP_DISCOUNT_GROUP_UNIT' => $unit,
                'SHOP_DISCOUNT_ROW_STYLE' => 'row'.((++$i % 2)+1),
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
            'SHOP_DISCOUNT_ROW_STYLE' => 'row'.((++$i % 2)+1),
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
                'SHOP_DISCOUNT_ROW_STYLE' => 'row'.((++$i % 2)+1),
            ));
            self::$objTemplate->parse('shopDiscountRate');
        }
        // Add a couple of empty rows for adding new counts and rates
        for ($j = 0; $j < 5; ++$j) {
            self::$objTemplate->setVariable(array(
                'SHOP_DISCOUNT_COUNT' => '',
                'SHOP_DISCOUNT_RATE' => '',
                'SHOP_DISCOUNT_RATE_INDEX' => $i,
                'SHOP_DISCOUNT_ROW_STYLE' => 'row'.((++$i % 2)+1),
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
            Discount::storeCustomerGroup(
                $_POST['groupName'], $_POST['id']
            );
        }
        if (isset($_GET['delete'])) {
            Discount::deleteCustomerGroup($_GET['id']);
        }

        self::$objTemplate->loadTemplateFile('module_shop_discount_groups_customer.html');
        self::$objTemplate->setGlobalVariable(array(
            'TXT_OVERVIEW' => $_ARRAYLANG['TXT_OVERVIEW'],
            'TXT_SHOP_DISCOUNTS_CUSTOMER' => $_ARRAYLANG['TXT_SHOP_DISCOUNTS_CUSTOMER'],
            'TXT_SHOP_CUSTOMER_GROUPS' => $_ARRAYLANG['TXT_SHOP_CUSTOMER_GROUPS'],
            'TXT_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
            'TXT_DELETE' => $_ARRAYLANG['TXT_DELETE'],
            'TXT_EDIT' => $_ARRAYLANG['TXT_EDIT'],
            'TXT_FUNCTIONS' => $_ARRAYLANG['TXT_FUNCTIONS'],
            'TXT_SHOP_CONFIRM_DELETE_CUSTOMER_GROUP' => $_ARRAYLANG['TXT_SHOP_CONFIRM_DELETE_CUSTOMER_GROUP'],
            'TXT_SHOP_CUSTOMER_GROUPS' => $_ARRAYLANG['TXT_SHOP_CUSTOMER_GROUPS'],
            'TXT_SHOP_CUSTOMER_GROUP_NAME' => $_ARRAYLANG['TXT_SHOP_CUSTOMER_GROUP_NAME'],
            'TXT_SHOP_EDIT_CUSTOMER_GROUP' => $_ARRAYLANG['TXT_SHOP_EDIT_CUSTOMER_GROUP'],
            'TXT_STORE' => $_ARRAYLANG['TXT_STORE'],
            'TXT_SHOP_CANCEL' => $_ARRAYLANG['TXT_SHOP_CANCEL'],
        ));

        // Group overview
        $arrGroups = Discount::getCustomerGroupArray();
        self::$objTemplate->setCurrentBlock('shopGroup');
        $i = 0;
        foreach ($arrGroups as $id => $name) {
            self::$objTemplate->setVariable(array(
                'SHOP_GROUP_ID' => $id,
                'SHOP_GROUP_NAME' => $name,
                'SHOP_ROW_STYLE' => 'row'.((++$i % 2)+1),
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
            'SHOP_ROW_STYLE' => 'row'.((++$i % 2)+1),
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
        self::$objTemplate->setGlobalVariable(array(
            'TXT_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
            'TXT_DELETE' => $_ARRAYLANG['TXT_DELETE'],
            'TXT_EDIT' => $_ARRAYLANG['TXT_EDIT'],
            'TXT_FUNCTIONS' => $_ARRAYLANG['TXT_FUNCTIONS'],
            'TXT_SHOP_CONFIRM_DELETE_ARTICLE_GROUP' => $_ARRAYLANG['TXT_SHOP_CONFIRM_DELETE_ARTICLE_GROUP'],
            'TXT_SHOP_ARTICLE_GROUPS' => $_ARRAYLANG['TXT_SHOP_ARTICLE_GROUPS'],
            'TXT_SHOP_ARTICLE_GROUP_NAME' => $_ARRAYLANG['TXT_SHOP_ARTICLE_GROUP_NAME'],
            'TXT_SHOP_EDIT_ARTICLE_GROUP' => $_ARRAYLANG['TXT_SHOP_EDIT_ARTICLE_GROUP'],
            'TXT_STORE' => $_ARRAYLANG['TXT_STORE'],
            'TXT_SHOP_CANCEL' => $_ARRAYLANG['TXT_SHOP_CANCEL'],
        ));

        // Group overview
        $arrGroups = Discount::getArticleGroupArray();
        self::$objTemplate->setCurrentBlock('shopGroup');
        $i = 0;
        foreach ($arrGroups as $id => $name) {
            self::$objTemplate->setVariable(array(
                'SHOP_GROUP_ID' => $id,
                'SHOP_GROUP_NAME' => $name,
                'SHOP_ROW_STYLE' => 'row'.((++$i % 2)+1),
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
            'SHOP_ROW_STYLE' => 'row'.((++$i % 2)+1),
        ));
        if (isset($arrGroups[$id])) {
            self::$objTemplate->setVariable(
                'SHOP_GROUP_NAME', $arrGroups[$id]
            );
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
        self::$objTemplate->setGlobalVariable(array(
            'TXT_OVERVIEW' => $_ARRAYLANG['TXT_OVERVIEW'],
            'TXT_SHOP_DISCOUNTS_CUSTOMER' => $_ARRAYLANG['TXT_SHOP_DISCOUNTS_CUSTOMER'],
            'TXT_SHOP_CUSTOMER_GROUPS' => $_ARRAYLANG['TXT_SHOP_CUSTOMER_GROUPS'],
            'TXT_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
            'TXT_DELETE' => $_ARRAYLANG['TXT_DELETE'],
            'TXT_EDIT' => $_ARRAYLANG['TXT_EDIT'],
            'TXT_FUNCTIONS' => $_ARRAYLANG['TXT_FUNCTIONS'],
            'TXT_SHOP_CONFIRM_DELETE_DISCOUNT_GROUP' => $_ARRAYLANG['TXT_SHOP_CONFIRM_DELETE_DISCOUNT_GROUP'],
            'TXT_SHOP_DISCOUNT_GROUPS' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_GROUPS'],
            'TXT_SHOP_DISCOUNT_GROUP_NAME' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_GROUP_NAME'],
            'TXT_SHOP_DISCOUNT_RATE' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_RATE'],
            'TXT_SHOP_EDIT_DISCOUNT_GROUP' => $_ARRAYLANG['TXT_SHOP_EDIT_DISCOUNT_GROUP'],
            'TXT_SHOP_DISCOUNT_COUNT_TIP' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUNT_TIP'],
            'TXT_STORE' => $_ARRAYLANG['TXT_STORE'],
            'TXT_SHOP_CANCEL' => $_ARRAYLANG['TXT_SHOP_CANCEL'],

        ));

        // Discounts overview
        $arrCustomerGroup = Discount::getCustomerGroupArray();
        $arrArticleGroup = Discount::getArticleGroupArray();
        $arrRate = Discount::getDiscountRateCustomerArray();
        $i = 0;
        // Set up the customer groups header
        self::$objTemplate->setVariable(
            'SHOP_CUSTOMER_GROUP_COUNT_PLUS_1', count($arrCustomerGroup) + 1
        );
        self::$objTemplate->setCurrentBlock('shopCustomerGroupHeader');
        self::$objTemplate->setVariable(array(
            'SHOP_DISCOUNT_ROW_STYLE' => 'row'.((++$i % 2)+1),
        ));
        self::$objTemplate->setCurrentBlock('shopCustomerGroupHeaderColumn');
        foreach ($arrCustomerGroup as $id => $strCustomerGroupName) {
            self::$objTemplate->setVariable(array(
                'SHOP_CUSTOMER_GROUP_ID' => $id,
                'SHOP_CUSTOMER_GROUP_NAME' => $strCustomerGroupName,
            ));
            self::$objTemplate->parseCurrentBlock();
        }

        foreach ($arrArticleGroup as $groupArticleId => $strArticleGroupName) {
            foreach ($arrCustomerGroup as $groupCustomerId => $strCustomerGroupName) {
                $rate = (isset($arrRate[$groupCustomerId][$groupArticleId])
                    ? $arrRate[$groupCustomerId][$groupArticleId] : 0
                );
                self::$objTemplate->setCurrentBlock('shopDiscountColumn');
                self::$objTemplate->setVariable(array(
                    'SHOP_CUSTOMER_GROUP_ID' => $groupCustomerId,
                    'SHOP_DISCOUNT_RATE' => sprintf('%2.2f', $rate),
                    'SHOP_DISCOUNT_ROW_STYLE' => 'row'.((++$i % 2)+1),
                ));
                self::$objTemplate->parseCurrentBlock();
            }
            self::$objTemplate->setCurrentBlock('shopArticleGroupRow');
            self::$objTemplate->setVariable(array(
                'SHOP_ARTICLE_GROUP_ID' => $groupArticleId,
                'SHOP_ARTICLE_GROUP_NAME' => $strArticleGroupName,
                'SHOP_DISCOUNT_ROW_STYLE' => 'row'.((++$i % 2)+1),
            ));
            self::$objTemplate->parseCurrentBlock();
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
     */
    function getProductSortingMenuoptions()
    {
        global $_ARRAYLANG;

        $activeSorting = $this->arrConfig['product_sorting']['value'];
        $arrAvailableOrder = array(
            1 => 'INDIVIDUAL',
            2 => 'ALPHABETIC',
            3 => 'PRODUCTCODE',
        );
        $strMenuOptions = '';
        foreach ($arrAvailableOrder as $index => $sorting) {
            $strMenuOptions .=
                '<option value="'.$index.'"'.
                ($activeSorting == $index ? ' selected="selected"' : '').
                '>'.$_ARRAYLANG['TXT_SHOP_PRODUCT_SORTING_'.$sorting].'</option>';
        }
        return $strMenuOptions;
    }


    /**
     * Replace all placeholders in the e-mail template with the
     * respective values from the order specified by the order ID.
     * @access  private
     * @static
     * @param   string  $body         The e-mail template
     * @param   integer $orderId      The order ID
     * @return  string                The e-mail body
     */
    static function substituteOrderData($body, $orderId)
    {
        global $objDatabase, $_ARRAYLANG;

        $orderIdCustom = ShopLibrary::getCustomOrderId($orderId);

        // Pick the order from the database
        $query = "
            SELECT c.customerid, username, email, phone, fax,
                   prefix, company, firstname, lastname,
                   address, city, zip, country_id,
                   selected_currency_id,
                   order_sum, currency_order_sum,
                   order_date, order_status,
                   ship_prefix, ship_company, ship_firstname, ship_lastname,
                   ship_address, ship_city, ship_zip, ship_country_id, ship_phone,
                   tax_price,
                   shipping_id, currency_ship_price,
                   payment_id, currency_payment_price,
                   customer_note
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders AS o
             INNER JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_customers AS c
             USING (customerid)
             WHERE orderid=$orderId
        ";
        $objResultOrder = $objDatabase->Execute($query);
        if (!$objResultOrder || $objResultOrder->RecordCount() == 0) {
            // Order not found
            return false;
        }
        $order_date = date(ASCMS_DATE_SHORT_FORMAT);
        $order_time = date(ASCMS_DATE_FORMAT);
        // Pick names of countries from the database
        $countryNameCustomer = '';
        $countryNameShipping = '';
        $query = "
            SELECT countries_name
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_countries
             WHERE countries_id=".$objResultOrder->fields['country_id'];
        $objResult = $objDatabase->Execute($query);
        if ($objResult && !$objResult->EOF) {
            $countryNameCustomer = $objResult->fields['countries_name'];
        }
        $query = "
            SELECT countries_name
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_countries
             WHERE countries_id=".$objResultOrder->fields['ship_country_id'];
        $objResult = $objDatabase->Execute($query);
        if ($objResult && !$objResult->EOF) {
            $countryNameShipping = $objResult->fields['countries_name'];
        }
        $search  = array (
            '<ORDER_ID>', '<ORDER_ID_CUSTOM>', '<DATE>',
            '<USERNAME>', '<PASSWORD>',
            '<ORDER_TIME>', '<ORDER_STATUS>', '<REMARKS>',
            '<CUSTOMER_ID>', '<CUSTOMER_EMAIL>',
            '<CUSTOMER_COMPANY>', '<CUSTOMER_PREFIX>', '<CUSTOMER_FIRSTNAME>',
            '<CUSTOMER_LASTNAME>', '<CUSTOMER_ADDRESS>', '<CUSTOMER_ZIP>',
            '<CUSTOMER_CITY>', '<CUSTOMER_COUNTRY>', '<CUSTOMER_PHONE>',
            '<CUSTOMER_FAX>',
            '<SHIPPING_COMPANY>', '<SHIPPING_PREFIX>', '<SHIPPING_FIRSTNAME>',
            '<SHIPPING_LASTNAME>', '<SHIPPING_ADDRESS>', '<SHIPPING_ZIP>',
            '<SHIPPING_CITY>', '<SHIPPING_COUNTRY>', '<SHIPPING_PHONE>',
        );
        $replace = array (
            $orderId, $orderIdCustom, $order_date,
            $objResultOrder->fields['username'],
            (isset($_SESSION['shop']['password'])
                ? $_SESSION['shop']['password'] : '******'),
            $order_time,
            $_ARRAYLANG['TXT_SHOP_ORDER_STATUS_'.$objResultOrder->fields['order_status']],
            $objResultOrder->fields['customer_note'],
            $objResultOrder->fields['customerid'], $objResultOrder->fields['email'],
            $objResultOrder->fields['company'], $objResultOrder->fields['prefix'],
            $objResultOrder->fields['firstname'], $objResultOrder->fields['lastname'],
            $objResultOrder->fields['address'], $objResultOrder->fields['zip'],
            $objResultOrder->fields['city'], $countryNameCustomer,
            $objResultOrder->fields['phone'], $objResultOrder->fields['fax'],
            $objResultOrder->fields['ship_company'], $objResultOrder->fields['ship_prefix'],
            $objResultOrder->fields['ship_firstname'], $objResultOrder->fields['ship_lastname'],
            $objResultOrder->fields['ship_address'], $objResultOrder->fields['ship_zip'],
            $objResultOrder->fields['ship_city'], $countryNameShipping,
            $objResultOrder->fields['ship_phone'],
        );
        $body = str_replace($search, $replace, $body);
        // Strip CRs
        $body = str_replace("\r", '', $body); //echo("made mail body:<br />".str_replace("\n", '<br />', htmlentities($body))."<br />");
        return $body;
    }

}

?>
