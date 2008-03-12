<?php

define ('_SHOP_DEBUG', 0);

/**
 * Class Shop manager
 *
 * Class for the administration of the shop
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Ivan Schmid <ivan.schmid@comvation.com>
 * @version     $Id: index.inc.php,v 1.00 $
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */

/**
 * @ignore
 */
require_once ASCMS_MODULE_PATH.'/shop/shopLib.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/shop_image.class.php';
require_once ASCMS_FRAMEWORK_PATH.'/Image.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Currency.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Exchange.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Settings.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Payment.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Shipment.class.php';
require_once ASCMS_MODULE_PATH.'/shop/payments/saferpay/Saferpay.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/CSVimport.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Csv_bv.class.php';
/**
 * Weight
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Weight.class.php';
/**
 * Value Added Tax (VAT) database layer
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Vat.class.php';
/**
 * Distribution database layer
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Distribution.class.php';
/**
 * Customer database layer
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
 * Administration of the Shop
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Ivan Schmid <ivan.schmid@comvation.com>
 * @version     $Id: index.inc.php,v 1.00 $
 * @access      public
 * @package     contrexx
 * @subpackage  module_shop
 */
class shopmanager extends ShopLibrary {
    /**
     * @var HTML_Template_Sigma
     */
    var $_objTpl ;
    var $strErrMessage = '';
    var $strOkMessage  = '';
    var $pageTitle;
    var $noPictureName         = 'no_picture.gif';
    var $_defaultImage         = '';
    var $thumbnailNameSuffix   = '.thumb';
    var $categoryTreeName      = array();
    var $categoryTreeSorting   = array();
    var $categoryTreeStatus    = array();
    var $shopImagePath;
    var $shopImageWebPath;
    var $langId;
    var $arrProductText = array();

    // ProductAttributes
    var $defaultAttributeOption = 0;
    var $arrAttributes = array();
    var $defaultCurrency = '';
    var $highestIndex = 0;

    /**
     * Array of all Product Attributes
     * @access  public
     * @var     array
     */
    var $arrProductAttributes = array();

    /**
     * Currency object
     * @access  public
     * @var     Currency
     */
    var $objCurrency;

    /**
     * Exchange object
     * @access  public
     * @var     Exchange
     */
    var $objExchange;

    /**
     * Settings object
     * @access  public
     * @var     Settings
     */
    var $objSettings;

    /**
     * Payment object
     * @access  private
     * @var     Payment
     */
    var $objPayment;

    /**
     * Shipment object
     * @access  public
     * @var     Shipment
     */
    var $objShipment;

    /**
     * Distribution object
     * @access  public
     * @var     Disctribution
     */
    var $objDistribution;

    /**
     * Order Status
     * @access  public
     * @var     array
     */
    var $arrOrderStatus = array();

    var $objCSVimport;

    /**
     * VAT object
     * @access  private
     * @var     Vat
     */
    var $objVat;

    /**
     * ShopCategories helper object
     * @access  private
     * @var     ShopCategories
     */
    var $objShopCategories;

    /**
     * Products helper object
     * @access  private
     * @var     Products
     */
    var $objProducts;

    // BUGGY SOLUTION!
    // Must be in sync with the *_module_shop_payment database table!
    var $paymentHandlers = array(
        'Saferpay',
        'Paypal',
        'PostFinance_DebitDirect',
        'Internal',
        'Internal_CreditCard'
    );


    /**
     * PHP4 constructor
     *
     * @access public
     * @return shopmanager
     */
    function shopmanager()
    {
        $this->__construct();
    }


    /**
     * PHP5 constructor
     *
     * @access public
     * @return shopmanager
     */
    function __construct()
    {
        global $_ARRAYLANG, $objTemplate, $objInit;

        if (_SHOP_DEBUG) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            global $objDatabase; $objDatabase->debug = 1;
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
            global $objDatabase; $objDatabase->debug = 0;
        }

        // sigma template
        $this->_objTpl = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/shop/template');
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $this->langId=$objInit->userFrontendLangId;

        // both include ASCMS_PATH_OFFSET!
        $this->shopImagePath = ASCMS_SHOP_IMAGES_PATH.'/';
        $this->shopImageWebPath = ASCMS_SHOP_IMAGES_WEB_PATH.'/';

        $this->_defaultImage = $this->shopImageWebPath.$this->noPictureName;

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

        // TODO: Must be made static for PHP5
        $this->arrOrderStatus = array(
            SHOP_ORDER_STATUS_PENDING   => $_ARRAYLANG['TXT_SHOP_ORDER_STATUS_PENDING'],     // Pending
            SHOP_ORDER_STATUS_CONFIRMED => $_ARRAYLANG['TXT_SHOP_ORDER_STATUS_CONFIRMED'],   // Confirmed
            SHOP_ORDER_STATUS_DELETED   => $_ARRAYLANG['TXT_SHOP_ORDER_STATUS_DELETED'],     // Cancelled -> Deleted
            SHOP_ORDER_STATUS_CANCELLED => $_ARRAYLANG['TXT_SHOP_ORDER_STATUS_CANCELLED'],   // Refunded  -> Cancelled
            SHOP_ORDER_STATUS_COMPLETED => $_ARRAYLANG['TXT_SHOP_ORDER_STATUS_COMPLETED'],   // Shipped   -> Completed
            SHOP_ORDER_STATUS_PAID      => $_ARRAYLANG['TXT_SHOP_ORDER_STATUS_PAID'],        // New: Paid
            SHOP_ORDER_STATUS_SHIPPED   => $_ARRAYLANG['TXT_SHOP_ORDER_STATUS_SHIPPED'],     // New: Shipped
        );

        // Settings object
        $this->objSettings = new Settings();
        // added return value. If empty, no error occurred
        $success = $this->objSettings->storeSettings();
        if ($success) {
            $this->addMessage($_ARRAYLANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL']);
        } elseif (($success === false)) {
            $this->addError($_ARRAYLANG['TXT_DATABASE_QUERY_ERROR']);
        }
        // $success may also be '', in which case no setting has been changed.

        // Currency object
        $this->objCurrency = new Currency();

        // Exchange object
        $this->objExchange = new Exchange();

        // Payment object
        $this->objPayment = new Payment();

        // Shipment object
        $this->objShipment = new Shipment(1);

        $this->objCSVimport = new CSVimport();

        // initialize array of all countries
        $this->_initCountries();
        $this->_initConfiguration();
        $this->_initPayment();

        // VAT object
        // Used in many places, thus it's instantiated right away.
        // NOTE: There is one such object in the Settings class as well, which
        // is used for deleting, updating and adding. After this process,
        // it is outdated.  Therefore we create a new one here,
        // AFTER setting up the Settings object!
        $this->objVat = new Vat();

        // Distribution object
        // Knows all the distribution types, and creates the menu.
        $this->objDistribution = new Distribution();

        // Products object
        // The helper for every task related to Products.
        $this->objProducts = new Products();

        // ShopCategories object
        // The helper for every task related to ShopCategories.
        $this->objShopCategories = new ShopCategories();
    }


    /**
     * Set up the shop admin page
     */
    function getShopPage()
    {
        global $objTemplate, $_ARRAYLANG;

        if (isset($_SESSION['shop']['strOkMessage'])) {
            $this->addMessage($_SESSION['shop']['strOkMessage']);
            unset($_SESSION['shop']['strOkMessage']);
        }

        if (!isset($_GET['act'])) {
            $_GET['act'] = '';
        }
        switch ($_GET['act']) {
            case 'settings':
                $this->_showSettings();
                break;
            case 'exchange':
                $this->showExchange();
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
                $this->pageTitle = $_ARRAYLANG['TXT_PRODUCT_CATALOG'];
                $this->delProduct();
                $this->_products();
                break;
            case 'delcat':
                $this->delCategory();
                $this->showCategories();
                break;
            case 'edit':
                $this->pageTitle = $_ARRAYLANG['TXT_CATEGORIES'];
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
                $this->pageTitle = $_ARRAYLANG['TXT_ORDER_DETAILS'];
                $this->shopShowOrderdetails('module_shop_order_details.html',0);
                break;
            case 'editorder':
                if (isset($_REQUEST['shopSaveOrderChanges'])) {
                    $this->pageTitle = $_ARRAYLANG['TXT_ORDER_DETAILS'];
                    $this->shopStoreOrderdetails();
                    $this->shopShowOrderdetails('module_shop_order_details.html',0);
                } else {
                    $this->pageTitle = $_ARRAYLANG['TXT_EDIT_ORDER'];
                    $this->shopShowOrderdetails('module_shop_order_edit.html',1);
                }
                break;
            case 'delorder':
                $this->shopDeleteOrder();
                $this->shopShowOrders();
                break;
            case 'customers':
                $this->pageTitle = $_ARRAYLANG['TXT_CUSTOMERS_PARTNERS'];
                $this->shopShowCustomers();
                break;
            case 'customerdetails':
                $this->pageTitle = $_ARRAYLANG['TXT_CUSTOMER_DETAILS'];
                $this->shopShowCustomerDetails();
                break;
            case 'neweditcustomer':
                $this->shopNewEditCustomer();
                break;
            case 'delcustomer':
                $this->pageTitle = $_ARRAYLANG['TXT_CUSTOMERS_PARTNERS'];
                $this->shopDeleteCustomer();
                $this->shopShowCustomers();
                break;
            case 'statistics':
                $this->pageTitle = $_ARRAYLANG['TXT_STATISTIC'];
                $this->shopOrderStatistics();
                break;
            case 'pricelist':
                $this->pageTitle = $_ARRAYLANG['TXT_PDF_OVERVIEW'];
                $this->shopPricelistOverview();
                break;
            case 'pricelist_new':
                $this->pageTitle = $_ARRAYLANG['TXT_MAKE_NEW_PRICELIST'];
                $this->shopPricelistNew();
                break;
            case 'pricelist_insert':
                $this->pageTitle = $_ARRAYLANG['TXT_PDF_OVERVIEW'];
                $this->shopPricelistInsert();
                $this->shopPricelistOverview();
                break;
            case 'pricelist_edit':
                $this->pageTitle = $_ARRAYLANG['TXT_PDF_OVERVIEW'];
                $pricelistID = intval($_GET['id']);
                $this->shopPricelistEdit($pricelistID);
                break;
            case 'pricelist_update':
                $this->pageTitle = $_ARRAYLANG['TXT_PDF_OVERVIEW'];
                $pricelistID = intval($_GET['id']);
                $this->shopPriceListUpdate($pricelistID);
                $this->shopPricelistOverview();
                break;
            case 'pricelist_delete':
                $this->pageTitle = $_ARRAYLANG['TXT_PDF_OVERVIEW'];
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
            'CONTENT_TITLE'          => $this->pageTitle,
            'CONTENT_OK_MESSAGE'     => $this->strOkMessage,
            'CONTENT_STATUS_MESSAGE' => $this->strErrMessage,
            'ADMIN_CONTENT'          => $this->_objTpl->get()
        ));
    }

    /**
     * Manage manufacturer
     */
    function _manufacturer()
    {
        global $_ARRAYLANG, $objDatabase;
        $this->pageTitle = $_ARRAYLANG['TXT_SHOP_MANUFACTURER'];
        $this->_objTpl->loadTemplateFile('module_shop_manufacturer.html', true, true);

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
                    $this->addMessage($_ARRAYLANG['TXT_SHOP_MANUFACTURER_INSERT_SUCCESS']);
                } else {
                    $this->addError($_ARRAYLANG['TXT_SHOP_MANUFACTURER_INSERT_FAILED']);
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
                    $this->addMessage($_ARRAYLANG['TXT_SHOP_MANUFACTURER_UPDATE_SUCCESS']);
                } else {
                    $this->addError($_ARRAYLANG['TXT_SHOP_MANUFACTURER_UPDATE_FAILED']);
                }
            }

            if ($_REQUEST['exe'] == 'delete' && $id > 0) {
                $query = "
                    DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_manufacturer
                     WHERE id=$id
                ";
                $objResult = $objDatabase->Execute($query);
                if ($objResult) {
                    $this->addMessage($_ARRAYLANG['TXT_SHOP_MANUFACTURER_DELETE_SUCCESS']);
                } else {
                    $this->addError($_ARRAYLANG['TXT_SHOP_MANUFACTURER_DELETE_FAILED']);
                }
            }

            if ($_REQUEST['exe'] == 'deleteList') {
                foreach ($_POST['selectedManufacturerId'] as $selectedId) {
                    $query = "
                        DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_manufacturer
                         WHERE id=".intval($selectedId);
                    $objResult = $objDatabase->Execute($query);
                    if ($objResult) {
                        $this->addMessage($_ARRAYLANG['TXT_SHOP_MANUFACTURER_DELETE_SUCCESS']);
                    } else {
                        $this->addError($_ARRAYLANG['TXT_SHOP_MANUFACTURER_DELETE_FAILED']);
                    }
                }
            }
        }

        $i = 1;
        $query = '
            SELECT id, name, url
              FROM '.DBPREFIX.'module_shop'.MODULE_INDEX.'_manufacturer
          ORDER BY name
        ';
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            $this->errorHandling();
        }

        while (!$objResult->EOF) {
            $this->_objTpl->setVariable(array(
                'VALUE_ID'      => $objResult->fields['id'],
                'VALUE_NAME'    => $objResult->fields['name'],
                'SHOP_ROWCLASS' => (++$i % 2 ? 'row2' : 'row1'),
            ));
            $this->_objTpl->parse("manufacturerRow");
            $objResult->MoveNext();
        }
        $this->_objTpl->setGlobalVariable(array(
            'TXT_EDIT'   => $_ARRAYLANG['TXT_EDIT'],
            'TXT_DELETE' => $_ARRAYLANG['TXT_DELETE'],
        ));

        if ($_REQUEST['mode'] == 'update') {
            // Update the selected Manufacturer
            $query = '
                SELECT id, name, url
                  FROM '.DBPREFIX."module_shop".MODULE_INDEX."_manufacturer
                 WHERE id=$id
            ";
            $objResult = $objDatabase->Execute($query);
            $this->_objTpl->setVariable(array(
                'TXT_SHOP_INSERT_NEW_MANUFACTURER' => $_ARRAYLANG['TXT_SHOP_UPDATE_MANUFACTURER'],
                'VALUE_MANUFACTURER_NAME'          => $objResult->fields['name'],
                'VALUE_MANUFACTURER_URL'           => $objResult->fields['url'],
                'EXE_MODE'                         => 'update',
                'VALUE_ID'                         => $id,
            ));
        } else {
            // Insert a new Manufacturer
            $this->_objTpl->setVariable(array(
                'TXT_SHOP_INSERT_NEW_MANUFACTURER' => $_ARRAYLANG['TXT_SHOP_INSERT_NEW_MANUFACTURER'],
                'VALUE_MANUFACTURER_NAME'          => '',
                'VALUE_MANUFACTURER_URL'           => '',
                'EXE_MODE'                         => 'insert',
            ));
        }

        $this->_objTpl->setVariable(array(
            'TXT_NAME'                               => $_ARRAYLANG['TXT_NAME'],
            'TXT_URL'                                => $_ARRAYLANG['TXT_MANUFACTURER_URL'],
            'TXT_SHOP_INSERT_NEW_MANUFACTURER_ERROR' => $_ARRAYLANG['TXT_SHOP_INSERT_NEW_MANUFACTURER_ERROR'],
            'TXT_STORE'                              => $_ARRAYLANG['TXT_STORE'],
            'TXT_SHOP_MANUFACTURER'                  => $_ARRAYLANG['TXT_SHOP_MANUFACTURER'],
            'TXT_ID'                                 => $_ARRAYLANG['TXT_ID'],
            'TXT_NAME'                               => $_ARRAYLANG['TXT_NAME'],
            'TXT_ACTION'                             => $_ARRAYLANG['TXT_ACTION'],
            'TXT_MARKED'                             => $_ARRAYLANG['TXT_MARKED'],
            'TXT_SELECT_ALL'                         => $_ARRAYLANG['TXT_SELECT_ALL'],
            'TXT_REMOVE_SELECTION'                   => $_ARRAYLANG['TXT_REMOVE_SELECTION'],
            'TXT_SELECT_ACTION'                      => $_ARRAYLANG['TXT_SELECT_ACTION'],
            'TXT_DELETE_MARKED'                      => $_ARRAYLANG['TXT_DELETE_MARKED'],
            'TXT_ACTION_IS_IRREVERSIBLE'             => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
            'TXT_SHOP_CONFIRM_DELETE_MANUFACTURER'   => $_ARRAYLANG['TXT_SHOP_CONFIRM_DELETE_MANUFACTURER'],
            'TXT_MAKE_SELECTION'                     => $_ARRAYLANG['TXT_MAKE_SELECTION'],
        ));

    }


    /**
     * Import and Export data from/to csv
     * @author  Reto Kohli <reto.kohli@comvation.com> (parts)
     */
    function _import()
    {
        global $_ARRAYLANG, $objDatabase;

        $this->pageTitle = $_ARRAYLANG['TXT_SHOP_IMPORT_TITLE'];
        $this->_objTpl->loadTemplateFile('module_shop_import.html', true, true);
        $this->_objTpl->SetGlobalVariable(array(
            // cms offset fix for admin images/icons:
            'SHOP_CMS_OFFSET'    => ASCMS_PATH_OFFSET,
            'ASCMS_BACKEND_PATH' => ASCMS_BACKEND_PATH,
        ));

        // Delete template
        if (isset($_REQUEST["deleteImg"]) && $_REQUEST["deleteImg"] == 'exe') {
            $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_importimg WHERE img_id=".$_REQUEST["img"]."";
            if ($objDatabase->Execute($query) !== false) {
                $this->addMessage($_ARRAYLANG['TXT_SHOP_IMPORT_SUCCESSFULLY_DELETED']);
            } else {
                $this->addError($_ARRAYLANG['TXT_SHOP_IMPORT_ERROR_DELETE']);
            }
            unset($this->objCSVimport->arrImportImg);
            $this->objCSVimport->InitArray();
        }

        // Save template
        if (isset($_REQUEST['exe']) && $_REQUEST['exe'] == 'SaveImg') {
            $query = "INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_importimg (img_name, img_cats, img_fields_file, img_fields_db) ".
                "VALUES ('".$_REQUEST['ImgName']."', '".$_REQUEST['category']."', '".
                $_REQUEST['pairs_left_keys']."', '".$_REQUEST['pairs_right_keys']."')";
            if ($objDatabase->Execute($query)) {
                $this->addMessage($_ARRAYLANG['TXT_SHOP_IMPORT_SUCCESSFULLY_SAVED']);
            } else {
                $this->addError($_ARRAYLANG['TXT_SHOP_IMPORT_ERROR_SAVE']);
            }
            unset($this->objCSVimport->arrImportImg);
            $this->objCSVimport->InitArray();
        }

        // Import Categories
        // this is not subject to change, so it's hardcoded
        if (isset($_REQUEST['exe']) && $_REQUEST["exe"] == "ImportCategories") {
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
            $objCsv = new Csv_bv($_FILES["importFileCategories"]["tmp_name"]);
            $importedLines = 0;
            $line = $objCsv->NextLine();
            while ($line) {
                // the first entry is considered to be a root category!
                // if it doesn't exist, it's created by getCategoryId().
                $parentCatId = 0;
                foreach ($line as $catName) {
                    $parentCatId = $this->objCSVimport->getCategoryId(
                        $catName,
                        $parentCatId
                    );
                }
                ++$importedLines;
                $line = $objCsv->NextLine();
            }
            $this->addMessage($_ARRAYLANG['TXT_SHOP_IMPORT_SUCCESSFULLY_IMPORTED_CATEGORIES'].': '.$importedLines);
        }

        // Import
        if (isset($_REQUEST["exe"]) && $_REQUEST["exe"] == "importFileProducts") {

            if (isset($_POST['clearProducts']) && $_POST['clearProducts']) {
                $query = 'DELETE FROM '.DBPREFIX.'module_shop'.MODULE_INDEX.'_products';
                $objDatabase->Execute($query);
                $query = 'DELETE FROM '.DBPREFIX.'module_shop'.MODULE_INDEX.'_products_attributes';
                $objDatabase->Execute($query);
                // the categories need not be removed, but it is done by design!
                $query = 'DELETE FROM '.DBPREFIX.'module_shop'.MODULE_INDEX.'_categories';
                $objDatabase->Execute($query);
            }

            $strFileContent = $this->objCSVimport->GetFileContent();

            $query = '
                SELECT img_id, img_name, img_cats, img_fields_file, img_fields_db
                  FROM '.DBPREFIX.'module_shop'.MODULE_INDEX.'_importimg
                 WHERE img_id='.$_REQUEST["ImportImage"];
            $objResult = $objDatabase->Execute($query);

            $arrCategoryName = split(';', $objResult->fields['img_cats']);
            $strFirstLine = $strFileContent[0];
            $arrCategoryColumnIndex = array();
            for ($x=0; $x < count($arrCategoryName); $x++) {
                if ($arrCategoryName[$x] != '') {
                    foreach ($strFirstLine as $index => $strColumnName) {
                        if ($strColumnName == $arrCategoryName[$x]) {
                            array_push($arrCategoryColumnIndex, $index);
                        }
                    }
                }
            }

            $arrTemplateFieldName =
                split(';', $objResult->fields['img_fields_file']);
            $arrDatabaseFieldIndex = array();
            for ($x=0; $x < count($arrTemplateFieldName); $x++) {
                if ($arrTemplateFieldName[$x] != '') {
                    foreach ($strFirstLine as $index => $strColumnName) {
                        if ($strColumnName == $arrTemplateFieldName[$x]) {
                            array_push($arrDatabaseFieldIndex, $index);
                        }
                    }
                }
            }

            $arrProductFieldName = split(';', $objResult->fields['img_fields_db']);
            $arrProductDatabaseFieldName = array();
            for ($x=0; $x < count($arrProductFieldName); $x++) {
                if ($arrProductFieldName[$x] != '') {
                    $DBname = $this->objCSVimport->DBfieldsName($arrProductFieldName[$x]);
                    if (empty($arrProductDatabaseFieldName[$DBname])) {
                        $arrProductDatabaseFieldName[$DBname] = $x;
                    } else {
                        $arrProductDatabaseFieldName[$DBname] .= ';'.$x;
                    }
                }
            }

            $sql_query = array();
            for ($x=1; $x < count($strFileContent); $x++) {
                if (is_array($strFileContent[$x])) {
                    $strColumnNames = '(';
                    $strColumnValues = '(';
                    $Komma = '';
                    $counter = 0;
                    foreach ($arrProductDatabaseFieldName as $index => $strFieldName) {
                        if ($counter>0) {
                            $Komma = ',';
                        }
                        $strColumnNames .= $Komma.$index;
                        if (strpos($strFieldName, ';')) {
                            $Prod2line = split(';', $strFieldName);
                            $SpaltenValuesTmp = '';
                            for ($z=0; $z < count($Prod2line); $z++) {
                                if ($Prod2line[$z]!='') {
                                    $SpaltenValuesTmp .= $strFileContent[$x][$arrDatabaseFieldIndex[$Prod2line[$z]]]."<br />";
                                }
                            }
                            if ($strColumnValues) {
                                $strColumnValues .= $Komma.'"'.addslashes($SpaltenValuesTmp).'"';
                            }
                        } else {
                            $strColumnValues .= $Komma.'"'.addslashes($strFileContent[$x][$arrDatabaseFieldIndex[$strFieldName]]).'"';
                        }
                        $counter++;
                    }
                    $catId = 0;
                    for ($cat=0; $cat < count($arrCategoryColumnIndex); $cat++) {
                        $catName = $strFileContent[$x][$arrCategoryColumnIndex[$cat]];
                        if ($catName != '') {
                            $catId = $this->objCSVimport->getCategoryId($catName, $catId);
                        } else {
                            $catId = $this->objCSVimport->GetFirstCat();
                        }
                    }
                    if ($catId == 0) {
                        $catId = $catId = $this->objCSVimport->GetFirstCat();
                    }
                    $strColumnNames .= ', catid)';
                    $strColumnValues .= ", $catId)";
                    array_push($sql_query, "INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_products ".$strColumnNames." values".$strColumnValues);
                }
            }
            $importedLines  = 0;
            $errorLines     = 0;
            // Array of IDs of newly inserted records
            $arrId = array();
            for ($x=0; $x < count($sql_query); $x++) {
                if ($sql_query[$x] != '') {
                    $objResult = $objDatabase->Execute($sql_query[$x]);
                    if ($objResult) {
                        $arrId[] = $objDatabase->Insert_ID();
                        $importedLines++;
                    } else {
                        $errorLines++;
                    }
                }
            }

            // Fix picture field and create thumbnails
            $this->makeProductThumbnailsById($arrId);

            $this->addMessage($_ARRAYLANG['TXT_SHOP_IMPORT_SUCCESSFULLY_IMPORTED_PRODUCTS'].': '.$importedLines);
            if ($errorLines) {
                $this->addError($_ARRAYLANG['TXT_SHOP_IMPORT_NOT_SUCCESSFULLY_IMPORTED_PRODUCTS'].': '.$errorLines);
            }
        } // end import

        if (isset($_REQUEST["mode"]) && $_REQUEST["mode"] == "ImportImg") {
            $JSSelectLayer = "selectTab('import2');";
        } else {
            $JSSelectLayer = "selectTab('import1');";
        }

        $arrImages = array();
        $arrImages = $this->objCSVimport->GetImportImg();
        $Noimg = '';
        $ImportButtonStyle = '';
        if (isset($_REQUEST["mode"]) && $_REQUEST["mode"] != "ImportImg") {
            if (count($arrImages)<1) {
                $Noimg = $_ARRAYLANG['TXT_SHOP_IMPORT_NO_TEMPLATES_AVAILABLE'];
                $ImportButtonStyle = 'style="display: none;"';
            } else {
                $Noimg = "";
                $ImportButtonStyle = '';
            }
        } else {
            if (!isset($_REQUEST["exe"]) || $_REQUEST["exe"] != "SelectFields") {
                $JSnofiles     = "selectTab('import1');";
            } else {
                if ($_FILES['CSVfile']['name'] == '') {
                    $JSnofiles  = "selectTab('import4');";
                } else {
                    $JSnofiles  = "selectTab('import2');";
                    $FileFields = $this->objCSVimport->GetFileFields();
                    $FileFields = '
                         <select name="FileFields" id="file_field" style="width: 200px;" size="10">
                             '.$FileFields.'
                         </select>
                     ';
                    $DBlist = $this->objCSVimport->GetDBFields();
                    $DBlist = '
                         <select name="DbFields" id="given_field" style="width: 200px;" size="10">
                             '.$DBlist.'
                         </select>
                     ';
                }
            }
        }

        // Export groups -- hardcoded
        // ------------------------------
        if (isset($_REQUEST['group']) && $_REQUEST['group']) {
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
                        'property1', 'property2', 'status', 'b2b', 'b2c', 'startdate', 'enddate',
                        'thumbnail_percent', 'thumbnail_quality', 'manufacturer_url', 'external_link',
                        'sort_order', 'vat_id', 'weight');
                    $query =
                        "SELECT id, product_id, picture, title, catid, handler, ".
                        "normalprice, resellerprice, shortdesc, description, ".
                        "stock, stock_visibility, discountprice, is_special_offer, ".
                        "property1, property2, status, b2b, b2c, startdate, enddate, ".
                        "thumbnail_percent, thumbnail_quality, manufacturer_url, external_link, ".
                        "sort_order, vat_id, weight ".
                        "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products ".
                        "ORDER BY id ASC";
                break;
                // products - custom:
                case 'rproduct':
                    $content_location = "ProdukteRelationen.csv";
                    $fieldNames = array(
                        'product_id', 'picture', 'title',
                        'catid', 'category', 'parentcategory', 'handler',
                        'normalprice', 'resellerprice', 'discountprice', 'is_special_offer',
                        'shortdesc', 'description',
                        'stock', 'stock_visibility',
                        'status', 'b2b', 'b2c',
                        'startdate', 'enddate',
                        'manufacturer_url', 'external_link',
                        'sort_order',
                        'vat_id', 'vat_percent', 'weight',
                    );
                    $query =
                        "SELECT p.product_id, p.picture, p.title, ".
                        "p.catid, c1.catname as category, c2.catname as parentcategory, p.handler, ".
                        "p.normalprice, p.resellerprice, p.discountprice, p.is_special_offer, ".
                        "p.shortdesc, p.description, p.stock, p.stock_visibility, ".
                        "p.status, p.b2b, p.b2c, p.startdate, p.enddate, ".
                        "p.manufacturer_url, p.external_link, p.sort_order, ".
                        "".DBPREFIX."module_shop".MODULE_INDEX."_manufacturer.name as manufacturer_name, ".DBPREFIX."module_shop".MODULE_INDEX."_manufacturer.url as manufacturer_website, ".
                        "p.vat_id, v.percent as vat_percent, p.weight ".
                        "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products p ".
                        // c1.catid *MUST NOT* be NULL
                        "INNER JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_categories c1 ".
                        "ON p.catid=c1.catid ".
                        // c2.catid *MAY* be NULL (if c1.catid is root)
                        "LEFT JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_categories c2 ".
                        "ON c1.parentid=c2.catid ".
                        // vat_id, OTOH, *MAY* be NULL
                        "LEFT JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_vat v ON vat_id = v.id ".
                        // manufacturer
                        "LEFT JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_manufacturer ON ".DBPREFIX."module_shop".MODULE_INDEX."_manufacturer.id = p.manufacturer ".
                        "ORDER BY catid ASC, product_id ASC";
                break;
                // customer - plain fields:
                case 'tcustomer':
                    $content_location = "KundenTabelle.csv";
                    $fieldNames = array(
                        'customerid', 'username', 'password', 'prefix', 'company', 'firstname', 'lastname',
                        'address', 'city', 'zip', 'country_id', 'phone', 'fax', 'email',
                        'ccnumber', 'ccdate', 'ccname', 'cvc_code', 'company_note',
                        'is_reseller', 'register_date', 'customer_status');
                    $query =
                        "SELECT customerid, username, password, prefix, company, firstname, lastname, ".
                        "address, city, zip, country_id, phone, fax, email, ".
                        "ccnumber, ccdate, ccname, cvc_code, company_note, ".
                        "is_reseller, register_date, customer_status ".
                        "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_customers ".
                        "ORDER BY lastname ASC, firstname ASC";
                break;
                // customer - custom:
                case 'rcustomer':
                    $content_location = "KundenRelationen.csv";
                    $fieldNames = array(
                        'customerid', 'username', 'firstname', 'lastname', 'prefix', 'company',
                        'address', 'zip', 'city', 'countries_name',
                        'phone', 'fax', 'email', 'is_reseller', 'register_date');
                    $query =
                        "SELECT c.customerid, c.username, c.firstname, c.lastname, c.prefix, c.company, ".
                        "c.address, c.zip, c.city, n.countries_name, ".
                        "c.phone, c.fax, c.email, c.is_reseller, c.register_date ".
                        "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_customers c ".
                        "INNER JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_countries n ON c.country_id=n.countries_id ".
                        "ORDER BY c.lastname ASC, c.firstname ASC";
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
                    $query =
                        "SELECT o.orderid, o.order_sum, o.tax_price, o.currency_ship_price, o.currency_payment_price, ".
                        "o.currency_order_sum, o.order_date, o.order_status, o.ship_prefix, o.ship_company, ".
                        "o.ship_firstname, o.ship_lastname, o.ship_address, o.ship_city, o.ship_zip, ".
                        "o.ship_phone, o.customer_note, ".
                        "c.customerid, c.username, c.firstname, c.lastname, c.prefix, c.company, ".
                        "c.address, c.zip, c.city, n.countries_name, ".
                        "c.phone, c.fax, c.email, c.is_reseller, c.register_date, ".
                        "u.code AS currency_code, s.name AS shipper_name, p.name AS payment_name, ".
                        "l.holder, l.bank, l.blz ".
                        "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders o ".
                        "INNER JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_customers c ON o.customerid=c.customerid ".
                        "INNER JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_countries n ON c.country_id=n.countries_id ".
                        "INNER JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_currencies u ON o.selected_currency_id=u.id ".
                        "INNER JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_shipper s ON o.shipping_id=s.id ".
                        "INNER JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_payment p ON o.payment_id=p.id ".
                        "LEFT JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_lsv l ON o.orderid=l.order_id ".
                        "ORDER BY orderid ASC";
                break;
            } // switch

            if ($query && $objResult = $objDatabase->Execute($query)) {
                // field names
                $fileContent = '"'.join('";"', $fieldNames)."\"\n";
                while (!$objResult->EOF) {
                    $arrRow = $objResult->FetchRow();
                    $arrReplaced = array();
                    foreach ($arrRow as $field) { $arrReplaced[] = str_replace('"','""', $field); }
                    $fileContent .= '"'.join('";"', $arrReplaced)."\"\n";
                }
                // set content to filename and -type for download
                header("Content-Disposition: inline; filename=$content_location");
                header("Content-Type: text/comma-separated-values");
                echo($fileContent);
                exit();
            } else {
                $this->addError($_ARRAYLANG['TXT_SHOP_EXPORT_ERROR']);
                $this->strOkMessage  = '';
            }
        } else {
            // can't submit without a group selection
        } // if/else group
        // end export

        // make sure that language entries exist for all of
        // TXT_SHOP_EXPORT_GROUP_*, TXT_SHOP_EXPORT_GROUP_*_TIP !!
        $arrGroups = array("tproduct", "rproduct", "tcustomer", "rcustomer", "torder", "rorder");
        $i = '';
        $tipText = '';
        for ($i = 0; $i < count($arrGroups); $i++) {
            $this->_objTpl->setCurrentBlock("groupRow");
            if ($i%2) { $class="row1"; } else { $class="row2"; }
            $this->_objTpl->setVariable(array(
                'SHOP_EXPORT_GROUP'      => $_ARRAYLANG['TXT_SHOP_EXPORT_GROUP_'.strtoupper($arrGroups[$i])],
                'SHOP_EXPORT_GROUP_CODE' => $arrGroups[$i],
                'SHOP_EXPORT_INDEX'      => $i,
                'TXT_EXPORT'             => $_ARRAYLANG['TXT_EXPORT'],
                'CLASS_NAME'             => $class
            ));
            $this->_objTpl->parse("groupRow");
            $tipText .= 'Text['.$i.']=["","'.$_ARRAYLANG['TXT_SHOP_EXPORT_GROUP_'.strtoupper($arrGroups[$i]).'_TIP'].'"];';
        }

        //$ImgList     = $this->objCSVimport->GetImgListDelete("[".$_ARRAYLANG['TXT_SHOP_IMPORT_DELETE']."]");
        $ImageChoice = $this->objCSVimport->GetImageChoice($Noimg);

        $this->_objTpl->setCurrentBlock("imgRow");
        for ($x=0; $x<count($this->objCSVimport->arrImportImg); $x++) {
            if (($x % 2) == 0) {$class="row1";} else {$class="row2";}
            $this->_objTpl->setVariable(array(
                'IMG_NAME'   => $this->objCSVimport->arrImportImg[$x]["name"],
                'IMG_ID'     => $this->objCSVimport->arrImportImg[$x]["id"],
                'TXT_DELETE' => $_ARRAYLANG['TXT_SHOP_IMPORT_DELETE'],
                'CLASS_NAME' => $class,
                // cms offset fix for admin images/icons:
                'SHOP_CMS_OFFSET' => ASCMS_PATH_OFFSET,
            ));
            $this->_objTpl->parse("imgRow");
        }

        $this->_objTpl->setVariable(array(
            'SELECT_LAYER_ONLOAD' => $JSSelectLayer,
            'NO_FILES'            => (isset($JSnofiles)  ? $JSnofiles  : ''),
            'FILE_FIELDS_LIST'    => (isset($FileFields) ? $FileFields : ''),
            'DB_FIELDS_LIST'      => (isset($DBlist)     ? $DBlist     : '' ),
            'IMAGE_CHOICE'        => $ImageChoice,
            'IMPORT_BUTTON_STYLE' => $ImportButtonStyle,
            'TXT_FUNCTIONS'       => $_ARRAYLANG['TXT_FUNCTIONS']
        ));

        $this->_objTpl->setVariable(array(
            'TXT_SHOP_IMPORT_TITLE'                  => $_ARRAYLANG['TXT_SHOP_IMPORT_TITLE'],
            'TXT_SHOP_IMPORT_SELECT_TEMPLATE'        => $_ARRAYLANG['TXT_SHOP_IMPORT_SELECT_TEMPLATE'],
            'TXT_SHOP_IMPORT_IMPORT'                 => $_ARRAYLANG['TXT_SHOP_IMPORT_IMPORT'],
            'TXT_SHOP_IMPORT_IMPORTTEMPLATE'         => $_ARRAYLANG['TXT_SHOP_IMPORT_IMPORTTEMPLATE'],
            'TXT_SHOP_IMPORT_TEXTFILE'               => $_ARRAYLANG['TXT_SHOP_IMPORT_TEXTFILE'],
            'TXT_SHOP_IMPORT_DATABASE'               => $_ARRAYLANG['TXT_SHOP_IMPORT_DATABASE'],
            'TXT_SHOP_IMPORT_CATEGORIES'             => $_ARRAYLANG['TXT_SHOP_IMPORT_CATEGORIES'],
            'TXT_SHOP_IMPORT_ADD_FEW'                => $_ARRAYLANG['TXT_SHOP_IMPORT_ADD_FEW'],
            'TXT_SHOP_IMPORT_ADD_CATEGORY'           => $_ARRAYLANG['TXT_SHOP_IMPORT_ADD_CATEGORY'],
            'TXT_SHOP_IMPORT_REMOVE_CATEGORY'        => $_ARRAYLANG['TXT_SHOP_IMPORT_REMOVE_CATEGORY'],
            'TXT_SHOP_IMPORT_REMOVE_FEW'             => $_ARRAYLANG['TXT_SHOP_IMPORT_REMOVE_FEW'],
            'TXT_SHOP_IMPORT_SAVE'                   => $_ARRAYLANG['TXT_SHOP_IMPORT_SAVE'],
            'TXT_SHOP_IMPORT_SAVED_TEMPLATES'        => $_ARRAYLANG['TXT_SHOP_IMPORT_SAVED_TEMPLATES'],
            'TXT_SHOP_IMPORT_MAKE_NEW_TEMPLATE'      => $_ARRAYLANG['TXT_SHOP_IMPORT_MAKE_NEW_TEMPLATE'],
            'TXT_SHOP_IMPORT_UPLOAD'                 => $_ARRAYLANG['TXT_SHOP_IMPORT_UPLOAD'],
            'TXT_SHOP_IMPORT_NO_TEMPLATES_AVAILABLE' => $_ARRAYLANG['TXT_SHOP_IMPORT_NO_TEMPLATES_AVAILABLE'],
            'TXT_SHOP_IMPORT_MANAGE_TEMPLATES'       => $_ARRAYLANG['TXT_SHOP_IMPORT_MANAGE_TEMPLATES'],
            'TXT_SHOP_IMPORT_ENTER_TEMPLATE_NAME'    => $_ARRAYLANG['TXT_SHOP_IMPORT_ENTER_TEMPLATE_NAME'],
            'TXT_SHOP_IMPORT_WARNING'                => $_ARRAYLANG['TXT_SHOP_IMPORT_WARNING'],
            'TXT_SHOP_IMPORT_SELECT_FILE_PLEASE'     => $_ARRAYLANG['TXT_SHOP_IMPORT_SELECT_FILE_PLEASE'],
            'TXT_SHOP_IMPORT_TEMPLATE_REALLY_DELETE' => $_ARRAYLANG['TXT_SHOP_IMPORT_TEMPLATE_REALLY_DELETE'],
            'TXT_SHOP_IMPORT_TEMPLATENAME'           => $_ARRAYLANG['TXT_SHOP_IMPORT_TEMPLATENAME'],
            'TXT_SHOP_IMPORT_FILE'                   => $_ARRAYLANG['TXT_SHOP_IMPORT_FILE'],
            // export added
            'TXT_SHOP_EXPORT'                        => $_ARRAYLANG['TXT_SHOP_EXPORT'],
            'TXT_SHOP_EXPORT_DATA'                   => $_ARRAYLANG['TXT_SHOP_EXPORT_DATA'],
            'TXT_SHOP_EXPORT_SELECTION'              => $_ARRAYLANG['TXT_SHOP_EXPORT_SELECTION'],
            'TXT_SHOP_EXPORT_WARNING'                => $_ARRAYLANG['TXT_SHOP_EXPORT_WARNING'],
            // instructions added
            'SHOP_EXPORT_TIPS'                       => $tipText,
            'TXT_SHOP_IMPORT_CREATE_TEMPLATE_TIPS'   => $_ARRAYLANG['TXT_SHOP_IMPORT_CREATE_TEMPLATE_TIPS'],
            'TXT_SHOP_IMPORT_ASSIGNMENT_TIPS'        => $_ARRAYLANG['TXT_SHOP_IMPORT_ASSIGNMENT_TIPS'],
            'TXT_SHOP_IMPORT_CATEGORY_TIPS'          => $_ARRAYLANG['TXT_SHOP_IMPORT_CATEGORY_TIPS'],
            'TXT_SHOP_IMPORT_CATEGORY_REMOVE_TIPS'   => $_ARRAYLANG['TXT_SHOP_IMPORT_CATEGORY_REMOVE_TIPS'],
            'TXT_SHOP_IMPORT_ASSIGNMENT_REMOVE_TIPS' => $_ARRAYLANG['TXT_SHOP_IMPORT_ASSIGNMENT_REMOVE_TIPS'],
            'TXT_SHOP_IMPORT_TEMPLATE_SAVE_TIPS'     => $_ARRAYLANG['TXT_SHOP_IMPORT_TEMPLATE_SAVE_TIPS'],
            'TXT_SHOP_IMPORT_CHOOSE_TEMPLATE_TIPS'   => $_ARRAYLANG['TXT_SHOP_IMPORT_CHOOSE_TEMPLATE_TIPS'],
            'TXT_SHOP_EXPORT_TIPS'                   => $_ARRAYLANG['TXT_SHOP_EXPORT_TIPS'],
            'TXT_SHOP_TIP'                           => $_ARRAYLANG['TXT_SHOP_TIP'],
            'TXT_CLEAR_DATABASE_BEFORE_IMPORTING'    => $_ARRAYLANG['TXT_CLEAR_DATABASE_BEFORE_IMPORTING'],
// velok
            'TXT_SHOP_IMPORT_IMPORT_CATEGORIES'      => $_ARRAYLANG['TXT_SHOP_IMPORT_IMPORT_CATEGORIES'],
            'TXT_SHOP_CLEAR_DATABASE_BEFORE_IMPORTING_CATEGORIES' => $_ARRAYLANG['TXT_SHOP_CLEAR_DATABASE_BEFORE_IMPORTING_CATEGORIES'],
            'TXT_SHOP_IMPORT_CATEGORIES_TIPS'        => $_ARRAYLANG['TXT_SHOP_IMPORT_CATEGORIES_TIPS'],
            'TXT_SHOP_IMPORT_PRODUCTS'               => $_ARRAYLANG['TXT_SHOP_IMPORT_PRODUCTS'],
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
     * @todo    Implement a simple and elegant way to notify the user when
     *          errors occur while creating the thumbnails
     */
    function makeProductThumbnailsById($arrId)
    {
        global $objDatabase, $_CONFIG;
        require_once ASCMS_FRAMEWORK_PATH."/Image.class.php";

        if (!is_array($arrId)) {
            return false;
        }
        $objImageManager = new ImageManager();
        foreach ($arrId as $Id) {
            $shopPicture = '';
            $query = "
                SELECT picture
                FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
                WHERE id=$Id
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                continue;
            }
            $imageName = $objResult->fields['picture'];
            // only try to create thumbs from entries that contain a
            // plain text file name (i.e. from an import)
            if (   $imageName == ''
                || !preg_match('/\.(?:jpg|jpeg|gif|png)$/', $imageName)) {
                continue;
            }
            // delete old thumb - now integrated into _createThumbWhq()
            //unlink($this->shopImagePath.$imageName.$this->thumbnailNameSuffix);
            // reset the ImageManager
            $objImageManager->imageCheck = 1;
            // create thumbnail
            if ($objImageManager->_createThumbWhq(
                $this->shopImagePath,
                $this->shopImageWebPath,
                $imageName,
                $_CONFIG['shop_thumbnail_max_width'],
                $_CONFIG['shop_thumbnail_max_height'],
                $_CONFIG['shop_thumbnail_quality']
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

        $arrCurrency = array();
        $this->pageTitle = $_ARRAYLANG['TXT_PRODUCT_CHARACTERISTICS'];
        $this->_objTpl->addBlockfile('SHOP_PRODUCTS_FILE', 'shop_products_block', 'module_shop_product_attributes.html');
        $arrCurrency = $this->objCurrency->getCurrencyArray();
        $this->defaultCurrency = $arrCurrency[$this->objCurrency->defaultCurrencyId]['symbol'];
        $this->addError($this->_showAttributeOptions());
    }


    /**
     * Show product download option page
     *
     * Show the settings for the download options of the products
     *
     */
    function _showProductDownloadOptions()
    {
        global $_ARRAYLANG;

        $this->pageTitle = $_ARRAYLANG['TXT_PRODUCT_CHARACTERISTICS'];
        $this->_objTpl->addBlockfile('SHOP_PRODUCTS_FILE', 'shop_products_block', 'module_shop_product_download.html');
        /*
        $arrCurrency = $this->objCurrency->getCurrencyArray();
        $this->defaultCurrency = $arrCurrency[$this->objCurrency->defaultCurrencyId]['symbol'];
        $this->addError($this->_showAttributeOptions());
        */
    }


    /**
     * Get attribute list
     *
     * Generate the standard attribute option/value list or the one of a product
     *
     * @access  private
     * @param   string    $productId    Product Id of which its list will be displayed
     */
    function _getAttributeList($productId = 0)
    {
        global $objDatabase;
        $i = 1;
        $this->_initAttributes();

        if ($productId > 0) {
            $query =
                "SELECT attribute_id, product_id, attributes_name_id, attributes_value_id, sort_id " .
                "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes ".
                "WHERE product_id=".intval($productId);
            $objResult = $objDatabase->Execute($query);

            while (!$objResult->EOF) {
                $this->arrAttributes[$objResult->fields['attributes_name_id']]['sortid'] = $objResult->fields['sort_id'];
                $this->arrAttributes[$objResult->fields['attributes_name_id']]['values'][$objResult->fields['attributes_value_id']]['selected'] = true;
                $objResult->MoveNext();
            }
        }

        foreach ($this->arrAttributes as $attributeId => $arrAttributeValues) {
            $attributeSelected = false;
            foreach ($arrAttributeValues['values'] as $id => $arrValues) {
                if ($this->arrAttributes[$attributeId]['values'][$id]['selected'] == true) {
                    $attributeValueSelected = true;
                    $attributeSelected = true;
                } else {
                    $attributeValueSelected = false;
                }
                $this->_objTpl->setVariable(array(
                'SHOP_PRODUCTS_ATTRIBUTE_ID'             => $attributeId,
                'SHOP_PRODUCTS_ATTRIBUTE_VALUE_ID'       => $id,
                'SHOP_PRODUCTS_ATTRIBUTE_VALUE_TEXT'     => $arrValues['value'].' ('.$arrValues['price_prefix'].$arrValues['price'].' '.$this->defaultCurrency.')',
                'SHOP_PRODUCTS_ATTRIBUTE_VALUE_SELECTED' => $attributeValueSelected == true ? "checked=\"checked\"" : ""
                ));
                $this->_objTpl->parse('attributeValueList');
            }
            $this->_objTpl->setVariable(array(
            'SHOP_PRODUCTS_ATTRIBUTE_ROW_CLASS'    => $i%2 == 0 ? "row1" : "row2",
            'SHOP_PRODUCTS_ATTRIBUTE_ID'           => $attributeId,
            'SHOP_PRODUCTS_ATTRIBUTE_NAME'         => $arrAttributeValues['name'],
            'SHOP_PRODUCTS_ATTRIBUTE_SELECTED'     => $attributeSelected == true ? "checked=\"checked\"" : "",
            'SHOP_PRODUCTS_ATTRIBUTE_DISPLAY_TYPE' => $attributeSelected == true ? "block" : "none",
                'SHOP_PRODUCTS_ATTRIBUTE_SORTID'  =>
                    (isset($arrAttributeValues['sortid'])
                        ? $arrAttributeValues['sortid']
                        : 0
                    )
            ));
            $this->_objTpl->parse('attributeList');
            $i++;
        }
    }


    /**
     * Show attribute options
     *
     * Generate the attribute option/value list for its configuration
     *
     * @access    private
     */
    function _showAttributeOptions()
    {
        global $_ARRAYLANG, $objDatabase;

        $this->_initAttributes();
        $arrAttributes = $this->arrAttributes;

        $rowClass = 1;
        // unsed: $arrAttributNames = array();

        // delete option
        if (isset($_GET['delId']) && !empty($_GET['delId'])) {
            $this->addError($this->_deleteAttributeOption($_GET['delId']));
        } elseif (!empty($_GET['delProduct']) && isset($_POST['selectedOptionId']) && !empty($_POST['selectedOptionId'])) {
            $this->addError($this->_deleteAttributeOption($_POST['selectedOptionId']));
        }
        // store new option
        if (isset($_POST['addAttributeOption']) && !empty($_POST['addAttributeOption'])) {
            $this->addError($this->_storeNewAttributeOption());
        }
        // update attribute options
        if (isset($_POST['updateAttributeOptions']) && !empty($_POST['updateAttributeOptions'])) {
            $this->addError($this->_updateAttributeOptions());
        }

        // set language variables
        $this->_objTpl->setVariable(array(
        'TXT_DEFINE_NAME_FOR_OPTION'  => $_ARRAYLANG['TXT_DEFINE_NAME_FOR_OPTION'],
        'TXT_DEFINE_VALUE_FOR_OPTION' => $_ARRAYLANG['TXT_DEFINE_VALUE_FOR_OPTION'],
        'TXT_CONFIRM_DELETE_OPTION'   => $_ARRAYLANG['TXT_CONFIRM_DELETE_OPTION'],
        'TXT_ACTION_IS_IRREVERSIBLE'  => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
        'TXT_MAKE_SELECTION'          => $_ARRAYLANG['TXT_MAKE_SELECTION'],
        'TXT_SELECT_ACTION'           => $_ARRAYLANG['TXT_SELECT_ACTION'],
        'TXT_DELETE'                  => $_ARRAYLANG['TXT_DELETE'],
        'TXT_SAVE_CHANGES'            => $_ARRAYLANG['TXT_SAVE_CHANGES'],
        'TXT_CHECKBOXES_OPTION'       => $_ARRAYLANG['TXT_CHECKBOXES_OPTION'],
        'TXT_RADIOBUTTON_OPTION'      => $_ARRAYLANG['TXT_RADIOBUTTON_OPTION'],
        'TXT_MENU_OPTION'             => $_ARRAYLANG['TXT_MENU_OPTION'],
        'TXT_SHOP_MENU_OPTION_DUTY'   => $_ARRAYLANG['TXT_SHOP_MENU_OPTION_DUTY']
        ));
        $this->_objTpl->setGlobalVariable(array(
        'TXT_OPTIONS'                 => $_ARRAYLANG['TXT_OPTIONS'],
        'TXT_ADD'                     => $_ARRAYLANG['TXT_ADD'],
        'TXT_NAME'                    => $_ARRAYLANG['TXT_NAME'],
        'TXT_VALUES'                  => $_ARRAYLANG['TXT_VALUES'],
        'TXT_FUNCTIONS'               => $_ARRAYLANG['TXT_FUNCTIONS'],
        'TXT_VALUE'                   => $_ARRAYLANG['TXT_VALUE'],
        'TXT_PRICE'                   => $_ARRAYLANG['TXT_PRICE'],
        'TXT_PRICE_PREFIX'            => $_ARRAYLANG['TXT_PRICE_PREFIX'],
        'TXT_ADD_NEW_VALUE'           => $_ARRAYLANG['TXT_ADD_NEW_VALUE'],
        'TXT_EDIT_OPTION'             => $_ARRAYLANG['TXT_EDIT_OPTION'],
        'TXT_DELETE_OPTION'           => $_ARRAYLANG['TXT_DELETE_OPTION'],
        'TXT_MARKED'                  => $_ARRAYLANG['TXT_MARKED'],
        'TXT_SELECT_ALL'              => $_ARRAYLANG['TXT_SELECT_ALL'],
        'TXT_REMOVE_SELECTION'        => $_ARRAYLANG['TXT_REMOVE_SELECTION'],
        'TXT_REMOVE_SELECTED_VALUE'   => $_ARRAYLANG['TXT_REMOVE_SELECTED_VALUE'],
        'TXT_DISPLAY_AS'              => $_ARRAYLANG['TXT_DISPLAY_AS']
        ));

        $this->arrAttributes = array();
        $this->_initAttributes();
        $arrAttributes = $this->arrAttributes;

        foreach ($arrAttributes as $attributeId => $arrValues) {
            $this->_objTpl->setCurrentBlock('attributeList');
            $this->_objTpl->setVariable(array(
            'SHOP_PRODUCT_ATTRIBUTE_ROW_CLASS'         => $rowClass%2 == 0 ? "row2" : "row1",
            'SHOP_PRODUCT_ATTRIBUTE_ID'                => $attributeId,
            'SHOP_PRODUCT_ATTRIBUTE_NAME'              => $arrValues['name'],
            'SHOP_PRODUCT_ATTRIBUTE_VALUE_MENU'        => $this->_getAttributeValueMenu($attributeId,"attributeValueList[$attributeId]", $arrValues['values'],"","setSelectedValue($attributeId)","width:200px;"),
            'SHOP_PRODUCT_ATTRIBUTE_VALUE_INPUTBOXES'  => $this->_getAttributeInputBoxes($attributeId,'attributeValue', 'value',32,'width:170px;'),
            'SHOP_PRODUCT_ATTRIBUTE_PRICE_INPUTBOXES'  => $this->_getAttributeInputBoxes($attributeId,'attributePrice','price',9,'width:170px;text-align:right;'),
            'SHOP_PRODUCT_ATTRIBUTE_PRICEPREFIX_MENUS' => $this->_getAttributePricePrefixMenu($attributeId,'attributePricePrefix', $arrValues['values']),
            'SHOP_PRODUCT_ATTRIBUTE_DISPLAY_TYPE'      => $this->_getAttributeDisplayTypeMenu($attributeId, $arrValues['displayType'])
            ));
            $this->_objTpl->parseCurrentBlock();
            $rowClass++;
        }

        $this->_objTpl->setVariable(array(
        'SHOP_PRODUCT_ATTRIBUTE_JS_VARS'  => $this->_getAttributeJSVars()."\nindex = ".$this->highestIndex.";\n",
        'SHOP_PRODUCT_ATTRIBUTE_CURRENCY' => $this->defaultCurrency
        ));
    }


    function _getAttributeDisplayTypeMenu($attributeId, $displayTypeId = '0')
    {
        global $_ARRAYLANG;

        $menu = "<select name=\"attributeDisplayType[".$attributeId."]\" size=\"1\" style=\"width:170px;\">\n";
        $menu .= "<option value=\"0\" ".($displayTypeId == '0' ? "selected=\"selected\"" : "").">".$_ARRAYLANG['TXT_MENU_OPTION']."</option>\n";
        $menu .= "<option value=\"3\" ".($displayTypeId == '3' ? "selected=\"selected\"" : "").">".$_ARRAYLANG['TXT_SHOP_MENU_OPTION_DUTY']."</option>\n";
        $menu .= "<option value=\"1\" ".($displayTypeId == '1' ? "selected=\"selected\"" : "").">".$_ARRAYLANG['TXT_RADIOBUTTON_OPTION']."</option>\n";
        $menu .= "<option value=\"2\" ".($displayTypeId == '2' ? "selected=\"selected\"" : "").">".$_ARRAYLANG['TXT_CHECKBOXES_OPTION']."</option>\n";
        $menu .= "</select>\n";

        return $menu;
    }


    /**
     * Initialize attributes
     *
     * Initialize the array $this->arrAttributes
     *
     * @access    private
     */
    function _initAttributes()
    {
        global $objDatabase;

        // get attributes
        $query =
            "SELECT name.id AS nameId, ".
                "name.name AS nameTxt, ".
                "name.display_type AS displayType, ".
                "value.id AS valueId, ".
                "value.name_id AS valueNameId, ".
                "value.value AS valueTxt, ".
                "value.price AS price, ".
                "value.price_prefix AS price_prefix ".
            "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name AS name, ".
                    DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value AS value ".
            "WHERE value.name_id = name.id ".
            "ORDER BY nameTxt, valueId ASC";

        if (($objResult = $objDatabase->Execute($query)) !== false) {
            while (!$objResult->EOF) {
                if (!isset($this->arrAttributes[$objResult->fields['nameId']]['name'])) {
                    $this->arrAttributes[$objResult->fields['nameId']]['name'] = $objResult->fields['nameTxt'];
                    $this->arrAttributes[$objResult->fields['nameId']]['displayType'] = $objResult->fields['displayType'];
                }
                $this->arrAttributes[$objResult->fields['nameId']]['values'][$objResult->fields['valueId']] = array(
                    'id'           => $objResult->fields['valueId'],
                    'value'        => $objResult->fields['valueTxt'],
                    'price'        => $objResult->fields['price'],
                    'price_prefix' => $objResult->fields['price_prefix'],
                    'selected'     => false
                );
                $this->highestIndex = $objResult->fields['valueId'] > $this->highestIndex
                    ? $objResult->fields['valueId']
                    : $this->highestIndex;
                $objResult->MoveNext();
            }
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
        global $objDatabase, $_ARRAYLANG;

        $statusMessage = "";
        $arrAttributeList = array();
        $arrAttributeValue = array();
        $arrAttributePrice = array();
        $arrAttributePricePrefix = array();


        if (empty($_POST['optionName'][0])) {
            return $_ARRAYLANG['TXT_DEFINE_NAME_FOR_OPTION'];
        } elseif (!is_array($_POST['attributeValueList'][0])) {
            return $_ARRAYLANG['TXT_DEFINE_VALUE_FOR_OPTION'];
        }

        // unused: $arrAttributesDb = $this->arrAttributes;
        $arrAttributeList = $_POST['attributeValueList'];
        $arrAttributeValue = $_POST['attributeValue'];
        $arrAttributePrice = $_POST['attributePrice'];
        $arrAttributePricePrefix = $_POST['attributePricePrefix'];

        $query = "INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name (name, display_type) VALUES ('".addslashes($_POST['optionName'][0])."','".intval($_POST['attributeDisplayType'][0])."')";

        if ($objDatabase->Execute($query) !== false) {
            $nameId = $objDatabase->Insert_Id();

            foreach ($arrAttributeList[0] as $id) {
                // insert new attribute value
                $query = "INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value (name_id, value, price, price_prefix) VALUES ($nameId, '".addslashes($arrAttributeValue[$id])."', '".floatval($arrAttributePrice[$id])."', '".addslashes($arrAttributePricePrefix[$id])."')";
                $objDatabase->Execute($query);
            }
        }

        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value");
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name");

        return $statusMessage;
    }


    /**
     * Update attribute options
     *
     * Update the attribute option/value list
     *
     * @access    private
     * @return    string    $statusMessage    Status message
     */
    function _updateAttributeOptions()
    {
        global $objDatabase;

        $statusMessage = "";

        $arrAttributesDb = array();
        $arrAttributeList = array();
        $arrAttributeValue = array();
        $arrAttributePrice = array();
        $arrAttributePricePrefix = array();

        $arrAttributesDb = $this->arrAttributes;
        $arrAttributeName = $_POST['optionName'];
        $arrAttributeList = $_POST['attributeValueList'];
        $arrAttributeValue = $_POST['attributeValue'];
        $arrAttributePrice = $_POST['attributePrice'];
        $arrAttributePricePrefix = $_POST['attributePricePrefix'];

        // update attribute names
        foreach ($arrAttributeName as $id => $name) {
            if (isset($arrAttributesDb[$id])) {
                if ($name != $arrAttributesDb[$id]['name'] || $_POST['attributeDisplayType'][$id] != $arrAttributesDb[$id]['displayType']) {
                    $query = "UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name Set name='".addslashes($name)."', display_type='".intval($_POST['attributeDisplayType'][$id])."' WHERE id=".intval($id);
                    $objDatabase->Execute($query);
                }
            }
        }

        foreach ($arrAttributeList as $attributeId => $arrAttributeValueIds) {
            foreach ($arrAttributeValueIds as $id) {
                if (isset($arrAttributesDb[$attributeId]['values'][$id])) {
                    // update attribute value
                    $updateString = "";
                    if ($arrAttributeValue[$id] != $arrAttributesDb[$attributeId]['values'][$id]['value']) {
                        $updateString .= "value = '".addslashes($arrAttributeValue[$id])."', ";
                    }
                    if ($arrAttributePrice[$id] != $arrAttributesDb[$attributeId]['values'][$id]['price']) {
                        $updateString .= " price = '".floatval($arrAttributePrice[$id])."', ";
                    }
                    if ($arrAttributePricePrefix[$id] != $arrAttributesDb[$attributeId]['values'][$id]['price_prefix']) {
                        $updateString .= " price_prefix = '".addslashes($arrAttributePricePrefix[$id])."', ";
                    }
                    if (strlen($updateString)>0) {
                        $query = "UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value Set ".substr($updateString,0,strlen($updateString)-2)." WHERE id=".intval($id);
                        $objDatabase->Execute($query);
                    }
                } else {
                    // insert new attribute value
                    $query = "INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value (name_id, value, price, price_prefix) VALUES (".intval($attributeId).", '".addslashes($arrAttributeValue[$id])."', '".floatval($arrAttributePrice[$id])."', '".addslashes($arrAttributePricePrefix[$id])."')";
                    $objDatabase->Execute($query);
                }
                unset($arrAttributesDb[$attributeId]['values'][$id]);
            }
        }

        foreach ($arrAttributesDb as $arrAttributes) {
            foreach ($arrAttributes['values'] as $arrValue) {
                $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes WHERE attributes_value_id=".intval($arrValue['id']);
                if ($objDatabase->Execute($query)) {
                    $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value WHERE id=".intval($arrValue['id']);
                    $objDatabase->Execute($query);
                }
            }
        }

        // delete the option if it has no options
        $arrAttributeKeys = array_keys($this->arrAttributes);
        foreach ($arrAttributeKeys as $attributeId) {
            if (!array_key_exists($attributeId, $arrAttributeList)) {
                $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name WHERE id=".intval($attributeId);
                $objDatabase->Execute($query);
            }
        }

        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value");
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name");
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes");

        return $statusMessage;
    }


    /**
     * Delete attribute option
     *
     * Delete the selected attribute option(s)
     *
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
            $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes WHERE attributes_name_id=".intval($optionId);
            if ($objDatabase->Execute($query)) {
                $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value WHERE name_id=".intval($optionId);
                if ($objDatabase->Execute($query)) {
                    $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name WHERE id=".intval($optionId);
                    $objDatabase->Execute($query);
                }
            }
        }
        return $_ARRAYLANG['TXT_OPTION_SUCCESSFULLY_DELETED'];
    }


    /**
     * Get attribute inputboxes
     *
     * Generate a list of the inputboxes with the values of an attribute option
     *
     * @access    private
     * @param    integer    $attributeId    Id of the attribute option
     * @param    string    $name    Name of the inputboxes
     * @param    string    $content    Attribute value type
     * @param    integer    $maxlength    Maxlength of the inputboxes
     * @param    string    $style    CSS-Style declaration for the inputboxes
     * @return    string    $inputBoxes    List with the generated inputboxes
     */
    function _getAttributeInputBoxes($attributeId, $name, $content, $maxlength, $style = '')
    {
        $inputBoxes = "";
        $select = true;

        foreach ($this->arrAttributes[$attributeId]['values'] as $id => $arrValue) {
            $inputBoxes .= "<input type=\"text\" name=\"".$name."[$id]\" id=\"".$name."[$id]\" value=\"$arrValue[$content]\" maxlength=\"$maxlength\" style=\"display:".($select == true ? "inline" : "none").";$style\" onchange=\"updateAttributeValueList($attributeId, $id)\" />";
            if ($select) {
                $select = false;
            }
        }
        return $inputBoxes;
    }


    /**
     * Get attribute price prefix menu
     *
     * Generates the attribute price prefix menus
     *
     * @access  private
     * @param   integer $attributeId    Id of the attribute option
     * @param   string  $name           Name of the menus
     * @param   string  $pricePrefix    Price prefix of the option value
     * @return  string                  Contains the price prefix menu of the given attribute option
     * @todo    Argument $attributeId is never used un this method.  Remove.
     */
    function _getAttributePricePrefixMenu($attributeId, $name, $arrValues)
    {
        $select = true;
        $menu = "";

        foreach ($arrValues as $id => $arrValue) {
            $menu .=
                "<select style=\"width:50px;display:".
                ($select == true ? "inline" : "none").
                ";\" name=\"".$name."[$id]\" id=\"".$name."[$id]\" size=\"1\"".
                "onchange='updateAttributeValueList($attributeId)'>\n".
                "<option value=\"+\" ".
                ($arrValue['price_prefix'] != "-" ? "selected=\"selected\"" : "").
                ">+</option>\n".
                "<option value=\"-\" ".
                ($arrValue['price_prefix'] == "-" ? "selected=\"selected\"" : "").
                ">-</option>\n".
                "</select>\n";
            if ($select) {
                $select = false;
            }
        }
        return $menu;
    }


    /**
     * Generate a javascript variables list of the attributes
     *
     * @access    private
     * @return    string    $jsVars    Javascript variables list
     */
    function _getAttributeJSVars()
    {
        foreach ($this->arrAttributes as $attributeId => $arrValues) {
            reset($arrValues['values']);
            $arrValue = current($arrValues['values']);
            $jsVars .= "attributeValueId[$attributeId] = ".$arrValue['id'].";\n";
        }
        return $jsVars;
    }


    /**
     * Get attribute value menu
     *
     * Generate the attribute value list of each option
     *
     * @access  private
     * @param   integer $attributeId    Id of the attribute option
     * @param   string  $name           Name of the menu
     * @param   array   $arrValues      Value ids of the attribute option
     * @param   integer $selectedId     Id of the selected value
     * @param   string  $onchange       Javascript onchange event of the menu
     * @param   string  $style          CSS-declaration of the menu
     * @return  string  $menu           Contains the value menus
     */
    function _getAttributeValueMenu($attributeId, $name, $arrValues, $selectedId, $onchange, $style)
    {
        global $_ARRAYLANG;

        $selected = false;
        $select = false;

        $menu = "<select name=\"".$name."[]\" id=\"".$name."[]\" size=\"1\" onchange=\"$onchange\" style=\"$style\">\n";
        foreach ($arrValues as $id => $arrValue) {
            if ($selected == false) {
                if ($selectedId == "" || $selectedId == $id) {
                    $select = true;
                    $selected = true;
                }
            } else {
                $select = false;
            }
            $menu .= "<option value=\"$id\" ".($select == true ? "selected=\"selected\"" : "").">".$arrValue['value']." (".$arrValue['price_prefix'].$arrValue['price']." $this->defaultCurrency)</option>\n";
        }
        $menu .= "</select>";
        $menu .= "<br /><a href=\"javascript:{}\" id=\"attributeValueMenuLink[$attributeId]\" style=\"display:none;\" onclick=\"removeSelectedValues($attributeId)\" title=\"".$_ARRAYLANG['TXT_REMOVE_SELECTED_VALUE']."\" alt=\"".$_ARRAYLANG['TXT_REMOVE_SELECTED_VALUE']."\">".$_ARRAYLANG['TXT_REMOVE_SELECTED_VALUE']."</a>";
        return $menu;
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

        // modulo counter for rows
        $i=0;

        $this->pageTitle= $_ARRAYLANG['TXT_SETTINGS'];
        $this->_objTpl->loadTemplateFile('module_shop_settings.html', true, true);

        //set language variables
        $this->_objTpl->setGlobalVariable(array(
            // Global variables
            'TXT_ADD_ALL'                      => $_ARRAYLANG['TXT_ADD_ALL'],
            'TXT_ADD_SELECTION'                => $_ARRAYLANG['TXT_ADD_SELECTION'],
            'TXT_REMOVE_ALL'                   => $_ARRAYLANG['TXT_REMOVE_ALL'],
            'TXT_REMOVE_SELECTION'             => $_ARRAYLANG['TXT_REMOVE_SELECTION'],
            'TXT_ADD'                          => $_ARRAYLANG['TXT_ADD'],
            'TXT_STORE'                        => $_ARRAYLANG['TXT_STORE'],
            'TXT_ACTIVE'                       => $_ARRAYLANG['TXT_ACTIVE'],
            'TXT_ACTION'                       => $_ARRAYLANG['TXT_ACTION'],
            'TXT_NAME'                         => $_ARRAYLANG['TXT_NAME'],
            'TXT_FEE'                          => $_ARRAYLANG['TXT_FEE'],
            'TXT_FREE_OF_CHARGE'               => $_ARRAYLANG['TXT_FREE_OF_CHARGE'],
            'TXT_FREE_OF_CHARGE_TIP'           => $_ARRAYLANG['TXT_FREE_OF_CHARGE_TIP'],
            'TXT_ZONE'                         => $_ARRAYLANG['TXT_ZONE'],
            'TXT_MAIL_TEMPLATES'               => $_ARRAYLANG['TXT_MAIL_TEMPLATES'],
            'TXT_CURRENCIES'                   => $_ARRAYLANG['TXT_CURRENCIES'],
            'TXT_GENERAL_SETTINGS'             => $_ARRAYLANG['TXT_GENERAL_SETTINGS'],
            'TXT_GENERAL'                      => $_ARRAYLANG['TXT_GENERAL'],
            'TXT_CURRENCY_CONVERTER'           => $_ARRAYLANG['TXT_CURRENCY_CONVERTER'],
            'TXT_RATE'                         => $_ARRAYLANG['TXT_RATE'],
            'TXT_SYMBOL'                       => $_ARRAYLANG['TXT_SYMBOL'],
            'TXT_ID'                           => $_ARRAYLANG['TXT_ID'],
            'TXT_STANDARD'                     => $_ARRAYLANG['TXT_STANDARD'],
            'TXT_SHIPPING_METHODS'             => $_ARRAYLANG['TXT_SHIPPING_METHODS'],
            'TXT_SHIPPING_METHOD'              => $_ARRAYLANG['TXT_SHIPPING_METHOD'],
            'TXT_LANGUAGE'                     => $_ARRAYLANG['TXT_LANGUAGE'],
            'TXT_HANDLER'                      => $_ARRAYLANG['TXT_HANDLER'],
            'TXT_PAYMENT_HANDLER'              => $_ARRAYLANG['TXT_PAYMENT_HANDLER'],
            'TXT_SEPARATED_WITH_COMMAS'        => $_ARRAYLANG['TXT_SEPARATED_WITH_COMMAS'],
            'TXT_CONFIRMATION_EMAILS'          => $_ARRAYLANG['TXT_CONFIRMATION_EMAILS'],
            'TXT_CONTACT_COMPANY'              => $_ARRAYLANG['TXT_CONTACT_COMPANY'],
            'TXT_CONTACT_ADDRESS'              => $_ARRAYLANG['TXT_CONTACT_ADDRESS'],
            'TXT_PHONE_NUMBER'                 => $_ARRAYLANG['TXT_PHONE_NUMBER'],
            'TXT_FAX_NUMBER'                   => $_ARRAYLANG['TXT_FAX_NUMBER'],
            'TXT_SHOP_EMAIL'                   => $_ARRAYLANG['TXT_SHOP_EMAIL'],
            'TXT_STATEMENT'                    => $_ARRAYLANG['TXT_STATEMENT'],
            'TXT_AUTORIZATION'                 => $_ARRAYLANG['TXT_AUTORIZATION'],
            'TXT_CODE'                         => "ISO-CODE",
            'TXT_STATEMENT'                    => $_ARRAYLANG['TXT_STATEMENT'],
            'TXT_STATEMENT'                    => $_ARRAYLANG['TXT_STATEMENT'],
            'TXT_COUNTRY'                      => $_ARRAYLANG['TXT_COUNTRY'],
            'TXT_ZONES'                        => $_ARRAYLANG['TXT_ZONES'],
            'TXT_EDIT'                         => $_ARRAYLANG['TXT_EDIT'],
            'TXT_DELETE'                       => $_ARRAYLANG['TXT_DELETE'],
            'TXT_ACTION_IS_IRREVERSIBLE'       => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
            'TXT_CONFIRM_DELETE_CURRENCY'      => $_ARRAYLANG['TXT_CONFIRM_DELETE_CURRENCY'],
            'TXT_PAYMENT_TYPES'                => $_ARRAYLANG['TXT_PAYMENT_TYPES'],
            'TXT_PAYMENT_TYPE'                 => $_ARRAYLANG['TXT_PAYMENT_TYPE'],
            'TXT_PAYMENT_LSV'                  => $_ARRAYLANG['TXT_PAYMENT_LSV'],
            'TXT_PAYMENT_LSV_FEE'              => $_ARRAYLANG['TXT_PAYMENT_LSV_FEE'],
            'TXT_CONFIRM_DELETE_PAYMENT'       => $_ARRAYLANG['TXT_CONFIRM_DELETE_PAYMENT'],
            'TXT_CONFIRM_DELETE_SHIPMENT'      => $_ARRAYLANG['TXT_CONFIRM_DELETE_SHIPMENT'],
            'TXT_DELIVERY_COUNTRIES'           => $_ARRAYLANG['TXT_DELIVERY_COUNTRIES'],
            'TXT_DELIVERY_COUNTRY'             => $_ARRAYLANG['TXT_DELIVERY_COUNTRY'],
            'TXT_SAFERPAY'                     => $_ARRAYLANG['TXT_SAFERPAY'],
            'TXT_ACCOUNT_ID'                   => $_ARRAYLANG['TXT_ACCOUNT_ID'],
            'TXT_USE_TEST_ACCOUNT'             => $_ARRAYLANG['TXT_USE_TEST_ACCOUNT'],
            'TXT_FINALIZE_PAYMENT'             => $_ARRAYLANG['TXT_FINALIZE_PAYMENT'],
            'TXT_INDICATE_PAYMENT_WINDOW_AS'   => $_ARRAYLANG['TXT_INDICATE_PAYMENT_WINDOW_AS'],
            'TXT_PAYPAL'                       => $_ARRAYLANG['TXT_PAYPAL'],
            'TXT_PAYPAL_EMAIL_ACCOUNT'         => $_ARRAYLANG['TXT_PAYPAL_EMAIL_ACCOUNT'],
            'TXT_SHOP_PAYPAL_DEFAULT_CURRENCY' => $_ARRAYLANG['TXT_SHOP_PAYPAL_DEFAULT_CURRENCY'],
            'TXT_YELLOWPAY_POSTFINANCE'        => $_ARRAYLANG['TXT_YELLOWPAY_POSTFINANCE'],
            'TXT_SHOP_ID'                      => $_ARRAYLANG['TXT_SHOP_ID'],
            'TXT_HASH_SEED'                    => $_ARRAYLANG['TXT_HASH_SEED'],
            'TXT_IMMEDIATE'                    => $_ARRAYLANG['TXT_IMMEDIATE'],
            'TXT_DEFERRED'                     => $_ARRAYLANG['TXT_DEFERRED'],
            // country settings
            'TXT_COUNTRY_LIST'                 => $_ARRAYLANG['TXT_COUNTRY_LIST'],
            'TXT_DISPLAY_IT_IN_THE_SHOP'       => $_ARRAYLANG['TXT_DISPLAY_IT_IN_THE_SHOP'],
            'TXT_DONT_DISPLAY_IT_IN_THE_SHOP'  => $_ARRAYLANG['TXT_DONT_DISPLAY_IT_IN_THE_SHOP'],
            'TXT_SELECT_COUNTRIES'             => $_ARRAYLANG['TXT_SELECT_COUNTRIES'],
            'TXT_SELECT_SEVERAL_COUNTRIES'     => $_ARRAYLANG['TXT_SELECT_SEVERAL_COUNTRIES'],
            // zone settings
            'TXT_CONFIRM_DELETE_ZONE'          => $_ARRAYLANG['TXT_CONFIRM_DELETE_ZONE'],
            'TXT_ZONE_NAME'                    => $_ARRAYLANG['TXT_ZONE_NAME'],
            'TXT_ZONE_LIST'                    => $_ARRAYLANG['TXT_ZONE_LIST'],
            'TXT_SETTINGS'                     => $_ARRAYLANG['TXT_SETTINGS'],
            'TXT_SELECTED_COUNTRIES'           => $_ARRAYLANG['TXT_SELECTED_COUNTRIES'],
            'TXT_AVAILABLE_COUNTRIES'          => $_ARRAYLANG['TXT_AVAILABLE_COUNTRIES'],
            // weight
            'TXT_SHIPPING_MAX_WEIGHT'          => $_ARRAYLANG['TXT_SHIPPING_MAX_WEIGHT'],
            'TXT_MAX_WEIGHT_TIP'               => $_ARRAYLANG['TXT_MAX_WEIGHT_TIP'],
            'TXT_FREE_OF_CHARGE'               => $_ARRAYLANG['TXT_FREE_OF_CHARGE'],
            'TXT_SHIPPING_FEE'                 => $_ARRAYLANG['TXT_SHIPPING_FEE'],
            // VAT (Value Added Tax)
            'TXT_ACTIVATE_TAXES'               => $_ARRAYLANG['TXT_ACTIVATE_TAXES'],
            'TXT_TAX_DETAILS'                  => $_ARRAYLANG['TXT_TAX_DETAILS'],
            'TXT_TAX_NUMBER'                   => $_ARRAYLANG['TXT_TAX_NUMBER'],
            'TXT_TAX_NEW'                      => $_ARRAYLANG['TXT_TAX_NEW'],
            'TXT_TAX_RATES'                    => $_ARRAYLANG['TXT_TAX_RATES'],
            'TXT_TAX'                          => $_ARRAYLANG['TXT_TAX'],
            'TXT_TAXES'                        => $_ARRAYLANG['TXT_TAXES'],
            'TXT_TAX_CONFIRM_DELETE'           => $_ARRAYLANG['TXT_TAX_CONFIRM_DELETE'],
            'TXT_INCLUDED'                     => $_ARRAYLANG['TXT_INCLUDED'],
            'TXT_EXCLUSIVE'                    => $_ARRAYLANG['TXT_EXCLUSIVE'],
            'TXT_TAX_DEFAULT'                  => $_ARRAYLANG['TXT_TAX_DEFAULT'],
            'TXT_TAX_SET_ALL'                  => $_ARRAYLANG['TXT_TAX_SET_ALL'],
            'TXT_TAX_SET_UNSET'                => $_ARRAYLANG['TXT_TAX_SET_UNSET'],
            'TXT_TAX_CONFIRM_SET_ALL'          => $_ARRAYLANG['TXT_TAX_CONFIRM_SET_ALL'],
            'TXT_TAX_CONFIRM_SET_UNSET'        => $_ARRAYLANG['TXT_TAX_CONFIRM_SET_UNSET'],
            // Image settings
            'TXT_SHOP_IMAGE_SETTINGS'          => $_ARRAYLANG['TXT_SHOP_IMAGE_SETTINGS'],
            'TXT_SHOP_THUMBNAIL_MAX_WIDTH'     => $_ARRAYLANG['TXT_SHOP_THUMBNAIL_MAX_WIDTH'],
            'TXT_SHOP_THUMBNAIL_MAX_HEIGHT'    => $_ARRAYLANG['TXT_SHOP_THUMBNAIL_MAX_HEIGHT'],
            'TXT_SHOP_THUMBNAIL_QUALITY'       => $_ARRAYLANG['TXT_SHOP_THUMBNAIL_QUALITY'],
        ));

        if (!isset($_GET['tpl'])) {
            $_GET['tpl'] = '';
        }

        switch ($_GET['tpl']) {
            case "currency":
                // start show currencies
                // $this->objSettings->_storeCurrencies();
                $this->_objTpl->addBlockfile('SHOP_SETTINGS_FILE', 'settings_block', 'module_shop_settings_currency.html');
                $this->_objTpl->setCurrentBlock('shopCurrency');
                $arrCur = $this->objCurrency->getCurrencyArray();
                foreach ($arrCur as $currency) {
                    $class = (++$i % 2 ? 'row1' : 'row2');
                    $statusCheck = ($currency['status'] ? 'checked="checked"' : '');
                    $standardCheck = ($currency['is_default'] ? 'checked="checked"' : '');
                    $this->_objTpl->setVariable(array(
                        'SHOP_CURRENCY_STYLE'    => $class,
                        'SHOP_CURRENCY_ID'       => $currency['id'],
                        'SHOP_CURRENCY_CODE'     => $currency['code'],
                        'SHOP_CURRENCY_SYMBOL'   => $currency['symbol'],
                        'SHOP_CURRENCY_NAME'     => $currency['name'],
                        'SHOP_CURRENCY_RATE'     => $currency['rate'],
                        'SHOP_CURRENCY_ACTIVE'   => $statusCheck,
                        'SHOP_CURRENCY_STANDARD' => $standardCheck
                    ));
                    $this->_objTpl->parseCurrentBlock();
                }
                // end show currencies
                break;
            case "payment":
                // start show payment
                $this->_objTpl->addBlockfile('SHOP_SETTINGS_FILE', 'settings_block', 'module_shop_settings_payment.html');

                $this->_objTpl->setCurrentBlock('shopPayment');
                foreach ($this->arrPayment as $id => $data) {
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

                    $class = (($i % 2) == 0) ? "row2" : "row1";
                    $statusCheck =(intval($data['status']) == 1) ? "checked='checked'" : "";
                    $this->_objTpl->setVariable(array(
                        'SHOP_PAYMENT_STYLE'         => $class,
                        'SHOP_PAYMENT_ID'            => $data['id'],
                        'SHOP_PAYMENT_NAME'          => $data['name'],
                        'SHOP_PAYMENT_HANDLER'       => $this->_getPaymentHandlerMenu("paymentHandler[".$data['id']."]", $data['processor_id']),
                        'SHOP_PAYMENT_COST'          => $data['costs'],
                        'SHOP_PAYMENT_COST_FREE_SUM' => $data['costs_free_sum'],
                        'SHOP_ZONE_SELECTION'        => $this->_getZonesMenu("paymentZone[".$data['id']."]", $zone_id),
                        'SHOP_PAYMENT_STATUS'        => $statusCheck,
                    ));
                    $this->_objTpl->parseCurrentBlock();
                    $i++;
                }

                $this->_objTpl->setVariable(array(
                    'SHOP_PAYMENT_HANDLER_NEW' => $this->_getPaymentHandlerMenu("paymentHandler_new"),
                    'SHOP_ZONE_SELECTION_NEW'  => $this->_getZonesMenu("paymentZone_new")
                ));

                // end show payment
                break;
            case "shipment":
                // start show shipment
                $this->_objTpl->addBlockfile('SHOP_SETTINGS_FILE', 'settings_block', 'module_shop_settings_shipment.html');
                $this->_objTpl->setGlobalVariable(
                    'SHOP_CURRENCY', $this->objCurrency->getDefaultCurrencySymbol()
                );

                $arrShippers  = $this->objShipment->getShippersArray();
                $arrShipments = $this->objShipment->getShipmentsArray();
                $i=0;
                foreach ($arrShippers as $sid => $arrShipper) {
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
                    $this->_objTpl->setCurrentBlock('shopShipment');
                    // show all possible shipment conditions for each shipper
                    if (isset($arrShipments[$sid])) {
                        foreach ($arrShipments[$sid] as $cid => $arrConditions) {
                            $class = ($i++ % 2 ? "row1" : "row2");
                            $this->_objTpl->setVariable(array(
                                'SHOP_SHIPMENT_STYLE'      => $class,
                                'SHOP_SHIPPER_ID'          => $sid,
                                'SHOP_SHIPMENT_ID'         => $cid,
                                'SHOP_SHIPMENT_MAX_WEIGHT' => $arrConditions['max_weight'],
                                'SHOP_SHIPMENT_PRICE_FREE' => $arrConditions['price_free'],
                                'SHOP_SHIPMENT_COST'       => $arrConditions['cost'],
                            ));
                            //$this->_objTpl->parseCurrentBlock();
                            $this->_objTpl->parse('shopShipment');
                        }
                    }

                    // parse outer block after inner block (see above for why)
                    $this->_objTpl->setCurrentBlock('shopShipper');
                    $class = ($i++ % 2 ? "row1" : "row2");
                    $statusCheck = ($arrShipper['status'] ? "checked='checked'" : "");
                    $this->_objTpl->setVariable(array(
                        'SHOP_SHIPMENT_STYLE'      => $class,
                        'SHOP_SHIPPER_ID'          => $sid,
                        'SHOP_SHIPPER_MENU'        => $this->objShipment->getShipperMenu(0, $sid),
                        'SHOP_ZONE_SELECTION'      => $this->_getZonesMenu("shipmentZone[$sid]", $zone_id),
                        'SHOP_SHIPPER_STATUS'      => $statusCheck,
                        // field not used anymore
                        //'SHOP_SHIPMENT_LANG_ID'    => $this->_getLanguageMenu("shipmentLanguage[$sid]", $val['lang_id']),
                    ));

                    //$this->_objTpl->setCurrentBlock('shopShipper');
                    $this->_objTpl->parse('shopShipper');
                }
                $this->_objTpl->setVariable(array('SHOP_ZONE_SELECTION_NEW' => $this->_getZonesMenu("shipmentZoneNew", 1)));
                // end show shipment
                break;
            case "countries":
                // start show countries
                $this->_objTpl->addBlockfile('SHOP_SETTINGS_FILE', 'settings_block', 'module_shop_settings_countries.html');
                $selected ="";
                $notSelected ="";
                foreach ($this->arrCountries as $cId => $data) {
                    if ($data['activation_status'] == 1) {
                        $selected .="<option value=\"".$cId."\">".$data['countries_name']."</option>\n";
                    } else {
                        $notSelected .="<option value=\"".$cId."\">".$data['countries_name']."</option>\n";
                    }
                }
                $this->_objTpl->setVariable(array(
                'SHOP_COUNTRY_SELECTED_OPTIONS'    => $selected,
                'SHOP_COUNTRY_NOTSELECTED_OPTIONS' => $notSelected
                ));
                // end show countries
                break;
            case "zones":
                // start show zones
                $this->_objTpl->addBlockfile('SHOP_SETTINGS_FILE', 'settings_block', 'module_shop_settings_zones.html');
                //$this->_objTpl->setCurrentBlock('shopZones');
                $arrZones = $this->_getZones();
                $selectFirst = '';
                foreach ($arrZones as $zId => $zValues) {
                    if ($zId != 1) {
                        if (!$selectFirst) {
                            $selectFirst = $zId;
                        }
                        $strZoneOptions .= "<option value=\"".$zId."\" ".($selectFirst == $zId ? "selected=\"selected\"" : "").">".$zValues['zones_name']."</option>\n";
                        $strSelectedCountries = NULL;
                        $strCountryList = NULL;
                        $query = "SELECT r.countries_id AS countries_id, c.countries_name AS countries_name ".
                                 "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries AS r, ".
                                         DBPREFIX."module_shop".MODULE_INDEX."_countries AS c ".
                                  "WHERE r.zones_id=$zId ".
                                    "AND c.countries_id=r.countries_id ".
                                    "AND c.activation_status=1 ".
                               "ORDER BY countries_name";
                        $objResult = $objDatabase->Execute($query);
                        $arrSelectedCountries = array();
                        while ($objResult && !$objResult->EOF) {
                            $strSelectedCountries .= "<option value=\"".$objResult->fields['countries_id']."\">".$objResult->fields['countries_name']."</option>\n";
                            $arrSelectedCountries[] = $objResult->fields['countries_id'];
                            $objResult->MoveNext();
                        }

                        if (count($arrSelectedCountries)>0) {
                            $query = "SELECT countries_id, countries_name ".
                                     "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_countries ".
                                     "WHERE activation_status=1 ".
                                       "AND countries_id!=".implode(" AND countries_id!=", $arrSelectedCountries).
                                     " ORDER BY countries_name";
                        } else {
                            $query = "SELECT countries_id, countries_name ".
                                     "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_countries ".
                                      "WHERE activation_status=1 ".
                                   "ORDER BY countries_name";
                        }
                        $objResult = $objDatabase->Execute($query);
                        while (!$objResult->EOF) {
                            $strCountryList .= "<option value=\"".$objResult->fields['countries_id']."\">".$objResult->fields['countries_name']."</option>\n";
                            $objResult->MoveNext();
                        }
                        $this->_objTpl->setCurrentBlock('shopZones');
                        $this->_objTpl->setVariable(array(
                            'SHOP_ZONE_ID'                         => $zId,
                            'ZONE_ACTIVE_STATUS'                   => ($zValues['activation_status'] ? "checked=\"checked\"" : "") ,
                            'SHOP_ZONE_NAME'                       => $zValues['zones_name'],
                            'SHOP_ZONE_DISPLAY_STYLE'              => ($selectFirst == $zId ? "display:block" : "display:none"),
                            'SHOP_ZONE_SELECTED_COUNTRIES_OPTIONS' => $strSelectedCountries,
                            'SHOP_COUNTRY_LIST_OPTIONS'            => $strCountryList
                        ));
                        $this->_objTpl->parseCurrentBlock();
                    }
                }

                foreach ($this->arrCountries as $cValues) {
                    if ($cValues['activation_status'] == 1) {
                        $strZoneCountryList .="<option value=\"".$cValues['countries_id']."\">".$cValues['countries_name']."</option>\n";
                    }
                }

                $this->_objTpl->setVariable(array(
                    'SHOP_ZONES_OPTIONS'     => $strZoneOptions,
                    'SHOP_ZONE_COUNTRY_LIST' => $strZoneCountryList
                ));

                // end show zones
                break;
            case "mail":
                $strMailSelectedTemplates = '';
                $strMailTemplates = '';

                $objLanguage = new FWLanguage();
                // gets indexed language array
                $arrLanguage = $objLanguage->getLanguageArray();

                $this->_objTpl->addBlockfile('SHOP_SETTINGS_FILE', 'settings_block', 'module_shop_settings_mail.html');

                //set language vars
                $this->_objTpl->setVariable(array(
                    'TXT_MAIL_TEMPLATES'                   => $_ARRAYLANG['TXT_MAIL_TEMPLATES'],
                    'TXT_REPLACEMENT_DIRECTORY'            => $_ARRAYLANG['TXT_REPLACEMENT_DIRECTORY'],
                    'TXT_ADD'                              => $_ARRAYLANG['TXT_ADD'],
                    'TXT_SEND_TEMPLATE'                    => $_ARRAYLANG['TXT_SEND_TEMPLATE'],
                    'TXT_TEMPLATE'                         => $_ARRAYLANG['TXT_TEMPLATE'],
                    'TXT_FUNCTIONS'                        => $_ARRAYLANG['TXT_FUNCTIONS'],
                    'TXT_ORDER'                            => $_ARRAYLANG['TXT_ORDER'],
                    'TXT_REPLACEMENT'                      => $_ARRAYLANG['TXT_REPLACEMENT'],
                    'TXT_ORDER_NR'                         => $_ARRAYLANG['TXT_ORDER_NR'],
                    'TXT_CUSTOMER_INFORMATIONS'            => $_ARRAYLANG['TXT_CUSTOMER_INFORMATIONS'],
                    'TXT_CUSTOMER_NR'                      => $_ARRAYLANG['TXT_CUSTOMER_NR'],
                    'TXT_SHIPPING_ADDRESS'                 => $_ARRAYLANG['TXT_SHIPPING_ADDRESS'],
                    'TXT_ORDER_DETAILS'                    => $_ARRAYLANG['TXT_ORDER_DETAILS'],
                    'TXT_ORDER_SUM'                        => $_ARRAYLANG['TXT_ORDER_SUM'],
                    'TXT_EMAIL_ADDRESS'                    => $_ARRAYLANG['TXT_EMAIL_ADDRESS'],
                    'TXT_USERNAME'                         => $_ARRAYLANG['TXT_USERNAME'],
                    'TXT_PASSWORD'                         => $_ARRAYLANG['TXT_PASSWORD'],
                    'TXT_OTHER'                            => $_ARRAYLANG['TXT_OTHER'],
                    'TXT_DATE'                             => $_ARRAYLANG['TXT_DATE'],
                    'TXT_CUSTOMER_REMARKS'                 => $_ARRAYLANG['TXT_CUSTOMER_REMARKS'],
                    'TXT_REPLACEMENT_NOT_AVAILABLE'        => $_ARRAYLANG['TXT_REPLACEMENT_NOT_AVAILABLE'],
                    'TXT_NEW_TEMPLATE'                     => $_ARRAYLANG['TXT_NEW_TEMPLATE'],
                    'TXT_TEMPLATE_NAME'                    => $_ARRAYLANG['TXT_TEMPLATE_NAME'],
                    'TXT_SENDER'                           => $_ARRAYLANG['TXT_SENDER'],
                    'TXT_EMAIL'                            => $_ARRAYLANG['TXT_EMAIL'],
                    'TXT_NAME'                             => $_ARRAYLANG['TXT_NAME'],
                    'TXT_SUBJECT'                          => $_ARRAYLANG['TXT_SUBJECT'],
                    'TXT_MESSAGE'                          => $_ARRAYLANG['TXT_MESSAGE'],
                    'TXT_STORE_AS_NEW_TEMPLATE'            => $_ARRAYLANG['TXT_STORE_AS_NEW_TEMPLATE'],
                    'TXT_STORE'                            => $_ARRAYLANG['TXT_STORE'],
                    'TXT_RECEIPTOR_ADDRESS'                => $_ARRAYLANG['TXT_RECEIPTOR_ADDRESS'],
                    'TXT_SEPARATED_WITH_COMMAS'            => $_ARRAYLANG['TXT_SEPARATED_WITH_COMMAS'],
                    'TXT_SEND'                             => $_ARRAYLANG['TXT_SEND'],
                    'TXT_CANNOT_DELETE_TEMPLATE_LANGUAGE'  => $_ARRAYLANG['TXT_CANNOT_DELETE_TEMPLATE_LANGUAGE'],
                    'TXT_CONFIRM_DELETE_TEMPLATE_LANGUAGE' => $_ARRAYLANG['TXT_CONFIRM_DELETE_TEMPLATE_LANGUAGE'],
                    'TXT_CONFIRM_DELETE_TEMPLATE'          => $_ARRAYLANG['TXT_CONFIRM_DELETE_TEMPLATE'],
                    'TXT_ACTION_IS_IRREVERSIBLE'           => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
                    'TXT_PLEASE_SET_RECEIPTOR_ADDRESS'     => $_ARRAYLANG['TXT_PLEASE_SET_RECEIPTOR_ADDRESS'],
                    'TXT_SET_MAIL_FROM_ADDRESS'            => $_ARRAYLANG['TXT_SET_MAIL_FROM_ADDRESS'],
                    'TXT_ADDRESS_CUSTOMER'                 => $_ARRAYLANG['TXT_ADDRESS_CUSTOMER'],
                    'TXT_SHOP_COMPANY'                     => $_ARRAYLANG['TXT_SHOP_COMPANY'],
                    'TXT_SHOP_PREFIX'                      => $_ARRAYLANG['TXT_SHOP_PREFIX'],
                    'TXT_SHOP_FIRSTNAME'                   => $_ARRAYLANG['TXT_SHOP_FIRSTNAME'],
                    'TXT_SHOP_LASTNAME'                    => $_ARRAYLANG['TXT_SHOP_LASTNAME'],
                    'TXT_SHOP_ADDRESS'                     => $_ARRAYLANG['TXT_SHOP_ADDRESS'],
                    'TXT_SHOP_ZIP'                         => $_ARRAYLANG['TXT_SHOP_ZIP'],
                    'TXT_SHOP_CITY'                        => $_ARRAYLANG['TXT_SHOP_CITY'],
                    'TXT_SHOP_COUNTRY'                     => $_ARRAYLANG['TXT_SHOP_COUNTRY'],
                    'TXT_SHOP_PHONE'                       => $_ARRAYLANG['TXT_SHOP_PHONE'],
                    'TXT_SHOP_FAX'                         => $_ARRAYLANG['TXT_SHOP_FAX'],
                    'TXT_SHOP_SHIPPING_INFORMATIONS'       => $_ARRAYLANG['TXT_SHOP_SHIPPING_INFORMATIONS'],
                    'TXT_SHOP_ORDER_TIME'                  => $_ARRAYLANG['TXT_SHOP_ORDER_TIME']
                ));

                // set config vars
                $this->_objTpl->setVariable(array(
                    'SHOP_MAIL_COLS'           => count($arrLanguage) + 2,
                ));
                $this->_objTpl->setGlobalVariable(array(
                    'TXT_SEND_TEMPLATE'        => $_ARRAYLANG['TXT_SEND_TEMPLATE'],
                    'TXT_DELETE'               => $_ARRAYLANG['TXT_DELETE'],
                    'TXT_EDIT'                 => $_ARRAYLANG['TXT_EDIT'],
                    'SHOP_MAIL_LANG_COL_WIDTH' => intval(70 / count($arrLanguage))
                ));

                // send template
                if (isset($_POST['shopMailSend']) && !empty($_POST['shopMailSend'])) {
                    if (isset($_POST['shopMailTo']) && !empty($_POST['shopMailTo'])) {
                        $arrMailTo         = explode(",", $_POST['shopMailTo']);
                        $shopMailFrom      = $_POST['shopMailFromAddress'];
                        $shopMailFromText  = $_POST['shopMailFromName'];
                        $shopMailSubject   = str_replace('<DATE>', date("d.m.Y"), $_POST['shopMailSubject']);
                        $shopMailBody      = str_replace('<DATE>', date("d.m.Y"), $_POST['shopMailBody']);
                        foreach ($arrMailTo as $shopMailTo) {
                            $shopMailTo    = trim($shopMailTo);
                            $blnMailResult = Shop::shopSendMail($shopMailTo, $shopMailFrom, $shopMailFromText, $shopMailSubject, $shopMailBody);
                            if ($blnMailResult) {
                                $this->addMessage(sprintf($_ARRAYLANG['TXT_EMAIL_SEND_SUCCESSFULLY'], $shopMailTo));
                            } else {
                                $this->addError($_ARRAYLANG['TXT_MESSAGE_SEND_ERROR']);
                            }
                        }
                    } else {
                        $this->addError($_ARRAYLANG['TXT_PLEASE_SET_RECEIPTOR_ADDRESS']);
                    }
                }

                // Get all templates
                $objResult = $objDatabase->Execute("SELECT id, tpl_id, lang_id FROM ".DBPREFIX."module_shop".MODULE_INDEX."_mail_content");
                $arrTemplateMails = array();
                while (!$objResult->EOF) {
                    if (!isset($arrTemplateMails[$objResult->fields['tpl_id']])) {
                        $arrTemplateMails[$objResult->fields['tpl_id']] = array();
                    }
                    array_push($arrTemplateMails[$objResult->fields['tpl_id']], $objResult->fields['lang_id']);
                    $objResult->MoveNext();
                }

                // Generate title row of the template list
                foreach ($arrLanguage as $langValues) {
                    if ($langValues['frontend']) {
                        $this->_objTpl->setVariable(array('SHOP_MAIL_LANGUAGE' => $langValues['name'],));
                        $this->_objTpl->parse('shopMailLanguages');
                    }
                    if ($langValues['is_default'] == "true") {
                        $defaultLang = $langValues['id'];
                    }
                }

                // Generate template rows of the template list with the language status
                $objResult = $objDatabase->Execute("SELECT id, tplname, protected FROM ".DBPREFIX."module_shop".MODULE_INDEX."_mail");
                $i=1;
                // Generate each row
                while (!$objResult->EOF) {
                    foreach ($arrLanguage as $langValues) {
                        if ($langValues['frontend']) {
                            $this->_objTpl->setVariable(array(
                                'SHOP_MAIL_CLASS'  => "row".(fmod($i,2)+1),
                                'SHOP_MAIL_STATUS' => (in_array($langValues['id'], $arrTemplateMails[$objResult->fields['id']]) ? "<a href=\"javascript:loadTpl(".$objResult->fields['id'].",".$langValues['id'].",'shopMailEdit')\" title=\"".$_ARRAYLANG['TXT_EDIT']."\"><img src=\"images/icons/check.gif\" width=\"15\" height=\"15\" alt=\"".$_ARRAYLANG['TXT_EDIT']."\" border=\"0\" /></a>" : "&nbsp;"),
                            ));
                            $this->_objTpl->parse('shopMailLanguagesStatus');
                        }
                    }
                    // increment numbers of rows
                    $i++;

                    $this->_objTpl->setVariable(array(
                        'SHOP_TEMPLATE_ID'        => $objResult->fields['id'],
                        'SHOP_LANGUAGE_ID'        => $defaultLang,
                        'SHOP_MAIL_TEMPLATE_NAME' =>
                            (substr($objResult->fields['tplname'],0,4) == "TXT_"
                                ? $_ARRAYLANG[$objResult->fields['tplname']]
                                : $objResult->fields['tplname']).
                            ($objResult->fields['protected']
                                ? "&nbsp;(".$_ARRAYLANG['TXT_SYSTEM_TEMPLATE'].")"
                                : "")
                    ));
                    $this->_objTpl->parse('shopMailTemplates');

                    // generate dropdown template-list
                    $selected = isset($_GET['tplId']) && !empty($_GET['tplId']) ? ($_GET['tplId'] == $objResult->fields['id'] ? 1 : 0) : 0;
                    $strMailSelectedTemplates .= "<option value=\"".$objResult->fields['id']."\" ".($selected ? "selected=\"selected\"" : "").">".(substr($objResult->fields['tplname'],0,4) == "TXT_" ? $_ARRAYLANG[$objResult->fields['tplname']] : $objResult->fields['tplname']).($objResult->fields['protected'] ? "&nbsp;(".$_ARRAYLANG['TXT_SYSTEM_TEMPLATE'].")" : "")."</option>\n";
                    $strMailTemplates .= "<option value=\"".$objResult->fields['id']."\">".(substr($objResult->fields['tplname'],0,4) == "TXT_" ? $_ARRAYLANG[$objResult->fields['tplname']] : $objResult->fields['tplname']).($objResult->fields['protected'] ? "&nbsp;(".$_ARRAYLANG['TXT_SYSTEM_TEMPLATE'].")" : "")."</option>\n";

                    // get the name of the loaded template to edit
                    if (isset($_GET['tplId']) && !empty($_GET['tplId']) && $_GET['strTab'] == "shopMailEdit") {
                        if ($objResult->fields['id'] == $_GET['tplId']) {
                            $this->_objTpl->setVariable(array('SHOP_MAIL_TEMPLATE' => (substr($objResult->fields['tplname'],0,4) == "TXT_" ? $_ARRAYLANG[$objResult->fields['tplname']] : $objResult->fields['tplname'])));
                        }
                    }
                    $objResult->MoveNext();
                }

                // Load template or show template overview
                if (isset($_GET['strTab']) && !empty($_GET['strTab'])) {
                    switch ($_GET['strTab']) {
                        case 'shopMailEdit':
                            if ($_GET['tplId'] != 0) {
                                // set the source template to load
                                if (isset($_GET['portLangId']) && !empty($_GET['portLangId'])) {
                                    $langId = $_GET['portLangId'];
                                } else {
                                    $langId = $_GET['langId'];
                                }

                                // Generate language menu
                                $langMenu = "<select name=\"langId\" size=\"1\" onchange=\"loadTpl(document.shopFormEdit.elements['tplId'].value,this.value,'shopMailEdit');\">\n";
                                foreach ($arrLanguage as $langValues)
                                {
                                    if ($langValues['frontend']) {
                                        $selected = ($_GET['langId'] == $langValues['id'] ? "selected=\"selected\"" : "");
                                        $langMenu .= "<option value=\"".$langValues['id']."\" $selected>".$langValues['name']."</option>\n";
                                    }
                                }
                                $langMenu .= "</select>";
                                $langMenu .= "&nbsp;<input type=\"checkbox\" id=\"portMail\" name=\"portMail\" value=\"1\" />&nbsp;".$_ARRAYLANG['TXT_COPY_TO_NEW_LANGUAGE'];

                                // Get the content of the template
                                $query = "SELECT id, from_mail, xsender, subject, message FROM ".DBPREFIX."module_shop".MODULE_INDEX."_mail_content WHERE tpl_id=".intval($_GET['tplId'])." AND lang_id=".intval($langId);
                                $objResult = $objDatabase->Execute($query);
                                if (!$objResult->EOF) {
                                    $this->_objTpl->setVariable(array(
                                    'SHOP_MAIL_ID'   => (isset($_GET['portLangId']) ? "" : $objResult->fields['id']),
                                    'SHOP_MAIL_NAME' => $objResult->fields['xsender'],
                                    'SHOP_MAIL_SUBJ' => $objResult->fields['subject'],
                                    'SHOP_MAIL_MSG'  => $objResult->fields['message'],
                                    'SHOP_MAIL_FROM' => $objResult->fields['from_mail']
                                    ));
                                }
                                $this->_objTpl->setVariable(array(
                                'SHOP_LOADD_TEMPLATE_ID' => $_GET['tplId'],
                                'SHOP_LOADD_LANGUAGE_ID' => $_GET['langId']
                                ));

                                $this->_objTpl->touchBlock('saveToOther');
                            } else {
                                $this->_objTpl->hideBlock('saveToOther');
                                // set the default sender
                                $this->_objTpl->setVariable(array('SHOP_MAIL_FROM' => $this->arrConfig['email']['value']));
                            }
                            break;
                        case 'shopMailSend':
                            // Generate language menu
                            $langMenu = "<select name='langId' size='1' onchange=\"loadTpl(document.shopFormSend.elements['tplId'].value,this.value,'shopMailSend');\">\n";
                            foreach ($arrLanguage as $langValues)
                            {
                                if ($langValues['frontend']) {
                                    $selected = (isset($_GET['langId']) && $_GET['langId'] == $langValues['id']
                                        ? "selected='selected'"
                                        : '');
                                    $langMenu .= "<option value='".$langValues['id']."' $selected>".$langValues['name']."</option>\n";
                                }
                            }
                            $langMenu .= '</select>';

                            // Get the content of the template
                            $tplId = (isset($_GET['tplId']) ? intval($_GET['tplId']) : '');
                            $langId = (isset($_GET['langId']) ? intval($_GET['langId']) : '');
                            $query = "
                                SELECT id, from_mail, xsender, subject, message
                                  FROM ".DBPREFIX."module_shop".MODULE_INDEX."_mail_content
                                 WHERE ".($tplId ? "tpl_id=$tplId AND " : '').
                                         ($langId ? "lang_id=$langId AND " : '')."1";
                            $objResult = $objDatabase->Execute($query);
                            if (!$objResult->EOF) {
                                $this->_objTpl->setVariable(array(
                                    'SHOP_MAIL_ID_SEND'   =>    $objResult->fields['id'],
                                    'SHOP_MAIL_NAME_SEND' => $objResult->fields['xsender'],
                                    'SHOP_MAIL_SUBJ_SEND' => $objResult->fields['subject'],
                                    'SHOP_MAIL_MSG_SEND'  => $objResult->fields['message'],
                                    'SHOP_MAIL_FROM_SEND' => $objResult->fields['from_mail'],
                                ));
                            } else {
                                $this->_objTpl->setVariable(
                                  'SHOP_MAIL_FROM_SEND', $this->arrConfig['email']['value']
                                );
                            }
                            break;
                    }
                    $this->_objTpl->setVariable(array(
                        'SHOP_MAIL_OVERVIEW_STYLE'      => "display:none",
                        'SHOP_MAILTAB_OVERVIEW_CLASS'   => "",
                        'SHOP_MAIL_EDIT_STYLE'          => ($_GET['strTab'] == "shopMailEdit" ? "display:block" : "display:none"),
                        'SHOP_MAILTAB_EDIT_CLASS'       => ($_GET['strTab'] == "shopMailEdit" ? "active" : ""),
                        'SHOP_MAIL_EDIT_TEMPLATES'      => ($_GET['strTab'] == "shopMailEdit" ? $strMailSelectedTemplates : $strMailTemplates),
                        'SHOP_MAIL_EDIT_LANGS'          => ($_GET['strTab'] == "shopMailEdit" ? ($_GET['tplId'] != 0 ? $langMenu : "<input type=\"hidden\" name=\"langId\" value=\"".$defaultLang."\" />") : "<input type=\"hidden\" name=\"langId\" value=\"".$defaultLang."\" />"),
                        'SHOP_MAIL_SEND_STYLE'          => ($_GET['strTab'] == "shopMailSend" ? "display:block" : "display:none"),
                        'SHOP_MAILTAB_SEND_CLASS'       => ($_GET['strTab'] == "shopMailSend" ? "active" : ""),
                        'SHOP_MAIL_SEND_TEMPLATES'      => ($_GET['strTab'] == "shopMailSend" ? $strMailSelectedTemplates : $strMailTemplates),
                        'SHOP_MAIL_SEND_LANGS'          => ($_GET['strTab'] == "shopMailSend" ? (isset($_GET['tplId']) ? $langMenu : "<input type=\"hidden\" name=\"langId\" value=\"".$defaultLang."\" />") : "<input type=\"hidden\" name=\"langId\" value=\"".$defaultLang."\" />"),
                        'SHOP_MAIL_TO'                  => ($_GET['strTab'] == "shopMailSend" ? (isset($_GET['shopMailTo']) ? $_GET['shopMailTo'] : "") : "")
                    ));
                } else {
                    $this->_objTpl->setVariable(array(
                        'SHOP_MAIL_OVERVIEW_STYLE'      => "display:block",
                        'SHOP_MAILTAB_OVERVIEW_CLASS'   => "active",
                        'SHOP_MAIL_EDIT_STYLE'          => "display:none",
                        'SHOP_MAILTAB_EDIT_CLASS'       => "",
                        'SHOP_MAIL_EDIT_TEMPLATES'      => $strMailTemplates,
                        'SHOP_MAIL_EDIT_LANGS'          => "<input type=\"hidden\" name=\"langId\" value=\"".$defaultLang."\" />",
                        'SHOP_MAIL_SEND_STYLE'          => "display:none",
                        'SHOP_MAILTAB_SEND_CLASS'       => "",
                        'SHOP_MAIL_SEND_TEMPLATES'      => $strMailTemplates,
                        'SHOP_MAIL_SEND_LANGS'          => "<input type=\"hidden\" name=\"langId\" value=\"".$defaultLang."\" />",
                        'SHOP_MAIL_TO'                  => "",
                        'SHOP_MAIL_FROM'                => $this->arrConfig['email']['value'],
                        'SHOP_MAIL_FROM_SEND'           => $this->arrConfig['email']['value'],
                    ));
                } // end: Load template or show template overview
                break;
            default:
                // Shop general settings template
                $this->_objTpl->addBlockfile('SHOP_SETTINGS_FILE', 'settings_block', 'module_shop_settings_general.html');
                $status = ($this->objVat->isEnabled()) ? 'checked="checked"' : '';
                $display = ($this->objVat->isEnabled()) ? 'block' : 'none';
                $included = ($this->objVat->isIncluded() ? 'checked="checked"' : '');
                $excluded = ($this->objVat->isIncluded() ? '' : 'checked="checked"');
                $saferpayStatus = ($this->arrConfig['saferpay_id']['status'] == 1) ? 'checked="checked"' : '';
                $saferpayTestStatus = ($this->arrConfig['saferpay_use_test_account']['status'] == 1) ? 'checked="checked"' : '';
                $yellowpayStatus = ($this->arrConfig['yellowpay_id']['status'] == 1) ? 'checked="checked"' : '';;
                $paypalStatus = ($this->arrConfig['paypal_account_email']['status'] == 1) ? 'checked="checked"' : '';

                if ($this->arrConfig['yellowpay_delivery_payment_type']['value'] == 'deferred') {
                    $yellowpayDeferred = "selected='selected'";
                    $yellowpayImmediate = "";
                } else {
                    $yellowpayImmediate = "selected='selected'";
                    $yellowpayDeferred = "";
                }

                $countryIdMenu = "<select name='country_id'>";
                foreach ($this->arrCountries as $cId => $data) {
                    if ($data['activation_status'] == 1) {
                        $countryIdMenu .="<option value='".$cId."' ".($cId == $this->arrConfig['country_id']['value'] ? "selected='selected'" : '').'>'.$data['countries_name']."</option>\n";
                    }
                }
                $countryIdMenu .= '</select>';

                // create saferpay window option menu
                $objSaferpay = new Saferpay();
                $arrSaferpayWindowOption = $objSaferpay->arrWindowOption;
                $strSaferpayWindowOptionMenu = "<select name='saferpay_window_option' id='saferpay_window_option'>\n";
                foreach ($arrSaferpayWindowOption as $windowOptionId => $strWindowOption) {
                    $strSaferpayWindowOptionMenu .= "<option value='$windowOptionId' ".($windowOptionId == $this->arrConfig['saferpay_window_option']['value'] ? "selected='selected'" : '').'>'.$_ARRAYLANG[$strWindowOption]."</option>\n";
                }
                $strSaferpayWindowOptionMenu .= "</select>\n";

                // start value added tax (VAT) display
                // fill in the VAT fields of the template
                $i = 0;
                foreach ($this->objVat->getRateArray() as $id => $rate) {
                    if (($i++ % 2) == 0) $class="row1"; else $class="row2";
                    $this->_objTpl->setVariable(array(
                    'SHOP_ROWCLASS'  => $class,
                    'SHOP_TAX_ID'    => $id,
                    'SHOP_TAX_CLASS' => $this->objVat->getClass($id),
                    'SHOP_TAX_RATE'  => $rate
                    ));
                    $this->_objTpl->parse("taxRow");
                }
                // end value added tax (VAT)

                $this->_objTpl->setVariable(array(
                    'SHOP_TAX_STATUS'                   => $status,
                    'SHOP_TAX_NUMBER'                   => $this->arrConfig['tax_number']['value'],
                    'SHOP_TAX_INCLUDED_STATUS'          => $included,
                    'SHOP_TAX_EXCLUDED_STATUS'          => $excluded,
                    'SHOP_TAX_DISPLAY_STATUS'           => $display,
                    'SHOP_TAX_DEFAULT_MENU'             => $this->objVat->getLongMenuString(
                                                               $this->arrConfig['tax_default_id']['value'], 'tax_default_id'
                                                           ),
                    'SHOP_SAFERPAY_ID'                  => $this->arrConfig['saferpay_id']['value'],
                    'SHOP_SAFERPAY_STATUS'              => $saferpayStatus,
                    'SHOP_SAFERPAY_TEST_ID'             => $this->arrConfig['saferpay_use_test_account']['value'],
                    'SHOP_SAFERPAY_TEST_STATUS'         => $saferpayTestStatus,
                    'SHOP_SAFERPAY_FINALIZE_PAYMENT'    => $this->arrConfig['saferpay_finalize_payment']['value'] == 1 ? "checked=\"checked\"" : "",
                    'SHOP_SAFERPAY_WINODW_OPTION_MENU'  => $strSaferpayWindowOptionMenu,
                    'SHOP_YELLOWPAY_ID'                 => $this->arrConfig['yellowpay_id']['value'],
                    'SHOP_YELLOWPAY_STATUS'             => $yellowpayStatus,
                    'SHOP_YELLOWPAY_HASH_SEED'          => $this->arrConfig['yellowpay_hash_seed']['value'],
                    'SHOP_YELLOWPAY_IMMEDIATE_STATUS'   => $yellowpayImmediate,
                    'SHOP_YELLOWPAY_DEFERRED_STATUS'    => $yellowpayDeferred,
                    'SHOP_CONFIRMATION_EMAILS'          => $this->arrConfig['confirmation_emails']['value'],
                    'SHOP_CONTACT_EMAIL'                => $this->arrConfig['email']['value'],
                    'SHOP_CONTACT_COMPANY'              => $this->arrConfig['shop_company']['value'],
                    'SHOP_CONTACT_ADDRESS'              => $this->arrConfig['shop_address']['value'],
                    'SHOP_CONTACT_TEL'                  => $this->arrConfig['telephone']['value'],
                    'SHOP_CONTACT_FAX'                  => $this->arrConfig['fax']['value'],
                    'SHOP_PAYPAL_EMAIL'                 => $this->arrConfig['paypal_account_email']['value'],
                    'SHOP_PAYPAL_STATUS'                => $paypalStatus,
                    'SHOP_PAYPAL_DEFAULT_CURRENCY_MENU' => $this->_getPayPalAcceptedCurrencyCodesMenu(),
                    // lsv settings
                    'SHOP_PAYMENT_LSV_STATUS'           => ($this->arrConfig['payment_lsv_status']['status'] ? 'checked="checked"' : ''),
                    'SHOP_PAYMENT_DEFAULT_CURRENCY'     => $this->objCurrency->getDefaultCurrencySymbol(),
                    //
                    'SHOP_GENERAL_COUNTRY'              => $countryIdMenu,
                    // image settings (thumbnails)
                    'SHOP_THUMBNAIL_MAX_WIDTH'          => $this->arrConfig['shop_thumbnail_max_width']['value'],
                    'SHOP_THUMBNAIL_MAX_HEIGHT'         => $this->arrConfig['shop_thumbnail_max_height']['value'],
                    'SHOP_THUMBNAIL_QUALITY'            => $this->arrConfig['shop_thumbnail_quality']['value'],
                ));
                break;
        }
        $this->_objTpl->parse('settings_block');
    }


    function _getPayPalAcceptedCurrencyCodesMenu()
    {
        require_once ASCMS_MODULE_PATH .'/shop/payments/paypal/Paypal.class.php';
        $objPayPal = new PayPal();

        $menu = "<select name=\"paypal_default_currency\">\n";
        foreach ($objPayPal->arrAcceptedCurrencyCodes as $code) {
            $menu .= "<option".($this->arrConfig['paypal_default_currency']['value'] == $code ? ' selected="selected"' : "").">".$code."</option>\n";
        }
        $menu .= "</select>\n";
        return $menu;
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
        global $objDatabase, $_ARRAYLANG;
        $this->pageTitle= $_ARRAYLANG['TXT_EXPORT']."/".$_ARRAYLANG['TXT_IMPORT'];

        // Exchange content
        if (isset($_POST['handler']) && !empty($_POST['handler'])) {
            $strMethod = substr($_POST['handler'],0,6);
            $strStep = substr($_POST['handler'],7);
            $this->_objTpl->setTemplate($this->objExchange->selectExchangeContent($strMethod, $strStep));
        } else {
            $this->_objTpl->setTemplate($this->objExchange->selectExchangeContent());
        }
    }


    function showCategories()
    {
        global $objDatabase, $_ARRAYLANG;

        $i = 1;
        $this->pageTitle = $_ARRAYLANG['TXT_CATEGORIES'];
        $this->_objTpl->loadTemplateFile('module_shop_categories.html', true, true);
        $this->_objTpl->setVariable(array(
            'TXT_ARTICLEGROUPS'          => $_ARRAYLANG['TXT_ARTICLE_GROUPS'],
            'TXT_NEW_MAIN_ARTICLE_GROUP' => $_ARRAYLANG['TXT_NEW_MAIN_ARTICLE_GROUP'],
            'TXT_ACTIVE'                 => $_ARRAYLANG['TXT_ACTIVE'],
            'TXT_TOTAL'                  => $_ARRAYLANG['TXT_TOTAL'],
            'TXT_NAME'                   => $_ARRAYLANG['TXT_NAME'],
            'TXT_STORE'                  => $_ARRAYLANG['TXT_STORE'],
            'TXT_ID'                     => $_ARRAYLANG['TXT_ID'],
            'TXT_ACTION'                 => $_ARRAYLANG['TXT_ACTION'],
            'TXT_CONFIRM_DELETE_SHOP_CATEGORIES' => $_ARRAYLANG['TXT_CONFIRM_DELETE_SHOP_CATEGORIES'],
            'TXT_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
            'TXT_DESCRIPTION'            => $_ARRAYLANG['TXT_DESCRIPTION'],
            'TXT_ACCEPT_CHANGES'         => $_ARRAYLANG['TXT_ACCEPT_CHANGES'],
            'TXT_DELETE_MARKED'          => $_ARRAYLANG['TXT_DELETE_MARKED'],
            'TXT_MARKED'                 => $_ARRAYLANG['TXT_MARKED'],
            'TXT_SELECT_ALL'             => $_ARRAYLANG['TXT_SELECT_ALL'],
            'TXT_REMOVE_SELECTION'       => $_ARRAYLANG['TXT_REMOVE_SELECTION'],
            'TXT_MAKE_SELECTION'         => $_ARRAYLANG['TXT_MAKE_SELECTION'],
            'TXT_SELECT_ACTION'          => $_ARRAYLANG['TXT_SELECT_ACTION'],
            'TXT_SHOP_EDIT_OR_ADD_IMAGE' => $_ARRAYLANG['TXT_SHOP_EDIT_OR_ADD_IMAGE'],
            'TXT_SHOP_CATEGORY_IMAGE'    => $_ARRAYLANG['TXT_SHOP_CATEGORY_IMAGE'],
            'TXT_SHOP_CATEGORY_VIRTUAL'  => $_ARRAYLANG['TXT_SHOP_CATEGORY_VIRTUAL'],
            'TXT_SHOP_CATEGORY_PARENT'   => $_ARRAYLANG['TXT_SHOP_CATEGORY_PARENT'],
        ));

        $this->_objTpl->setGlobalVariable(array(
            'TXT_STATUS'                 => $_ARRAYLANG['TXT_STATUS'],
            'TXT_EDIT'                   => $_ARRAYLANG['TXT_EDIT'],
            'TXT_PREVIEW'                => $_ARRAYLANG['TXT_PREVIEW'],
            'TXT_DELETE'                 => $_ARRAYLANG['TXT_DELETE']
        ));

        // Get the tree array of all ShopCategories
        $arrShopCategories =
            $this->objShopCategories->getTreeArray(true, false, false);
        $this->_objTpl->setVariable(
            'SHOP_TOTAL_CATEGORIES',
            $this->objShopCategories->getTreeNodeCount()
        );
        // Edit the selected category
        if (!empty($_REQUEST['modCatId'])) {
            $id = $_REQUEST['modCatId'];
            $arrShopCategory = $this->objShopCategories->getArrayById($id);
            $picture = $arrShopCategory['picture'];
            if ($picture == '') {
                $picture = $this->_defaultImage;
            } else {
                $picture = $this->shopImageWebPath.$picture.$this->thumbnailNameSuffix;
            }
            $this->_objTpl->setVariable(array(
                'TXT_ADD_NEW_SHOP_GROUP' => $_ARRAYLANG['TXT_EDIT_PRODUCT_GROUP'],
                'SHOP_MOD_CAT_ID'        => $id,
                'SHOP_SELECTED_CAT_NAME' => $arrShopCategory['name'],
                'SHOP_CAT_MENU'          =>
                    $this->objShopCategories->getShopCategoriesMenu(
                        $arrShopCategory['parentId'], false
                    ),
                'SHOP_PICTURE_IMG_HREF'  => $picture,
                'SHOP_IMAGE_WIDTH'       => $this->arrConfig['shop_thumbnail_max_width']['value'],
                'SHOP_IMAGE_HEIGHT'      => $this->arrConfig['shop_thumbnail_max_height']['value'],
                'SHOP_SELECTED_CATEGORY_VIRTUAL_CHECKED' =>
                    ($arrShopCategory['virtual'] ? ' checked="checked"' : ''),
                'SHOP_SELECTED_CATEGORY_STATUS_CHECKED' =>
                    ($arrShopCategory['status'] ? ' checked="checked"' : ''),
            ));
        } else {
            $this->_objTpl->setVariable(array(
                'TXT_ADD_NEW_SHOP_GROUP' => $_ARRAYLANG['TXT_ADD_NEW_PRODUCT_GROUP'],
                'SHOP_MOD_CAT_ID' => '',
                'SHOP_SELECTED_CAT_NAME' => '',
                'SHOP_CAT_MENU' =>
                    $this->objShopCategories->getShopCategoriesMenu(0, false),
                'SHOP_PICTURE_IMG_HREF'  => $this->_defaultImage,
                'SHOP_SELECTED_CATEGORY_VIRTUAL_CHECKED' => '',
                'SHOP_SELECTED_CATEGORY_STATUS_CHECKED' => ' checked="checked"',
            ));
        }

        $this->_objTpl->setCurrentBlock('catRow');
        foreach ($arrShopCategories as $arrShopCategory) {
             $id = $arrShopCategory['id'];
            $this->_objTpl->setVariable(array(
                'SHOP_ROWCLASS'       => (++$i % 2 ? 'row2' : 'row1'),
                'SHOP_CAT_ID'         => $id,
                'SHOP_CAT_NAME'       =>
                    htmlentities(
                        $arrShopCategory['name'], ENT_QUOTES, CONTREXX_CHARSET
                    ),
                'SHOP_CAT_SORTING'    => $arrShopCategory['sorting'],
                'SHOP_CAT_LEVELSPACE' =>
                    str_repeat('|----', $arrShopCategory['level']),
                'SHOP_CAT_STATUS'     =>
                    ($arrShopCategory['status']
                        ? $_ARRAYLANG['TXT_ACTIVE']
                        : $_ARRAYLANG['TXT_INACTIVE']
                    ),
                'SHOP_CAT_STATUS_CHECKED' =>
                    ($arrShopCategory['status'] ? ' checked="checked"' : ''),
                'SHOP_CAT_STATUS_PICTURE' =>
                    ($arrShopCategory['status']
                        ? 'status_green.gif'
                        : 'status_red.gif'
                    ),
                'SHOP_CAT_VIRTUAL_CHECKED' =>
                    ($arrShopCategory['virtual'] ? ' checked="checked"' : ''),
            ));
            $this->_objTpl->parse('catRow');
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

        $query =
            "SELECT catid, parentid, catname, catsorting, catstatus ".
            "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories ".
            "WHERE catstatus=1 AND parentId=$parentId".
            "ORDER BY parentid ASC, catsorting ASC";
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
            if (!$objShopCategory) {
                return false;
            }
            // Check validity of the IDs of the category and its parent.
            // If the values are identical, leave the parent ID alone!
            if ($id != $parentid) {
                $objShopCategory->setParentId($parentid);
            }
            $objShopCategory->setName($name);
            $objShopCategory->setStatus($status);
        } else {
            // Add new ShopCategory
            $objShopCategory = new ShopCategory(
                $name, $parentid, $status, 0
            );
        }
        // Ignore the picture if it's the default image!
        // Storing it would be pointless, and we should
        // use the picture of a contained Product instead.
        if ($picture == $this->_defaultImage) {
            $picture = '';
        } else {
            $objImage = new ImageManager();
            if (!$objImage->_createThumbWhq(
                $this->shopImagePath,
                $this->shopImageWebPath,
                $picture,
                $this->arrConfig['shop_thumbnail_max_width']['value'],
                $this->arrConfig['shop_thumbnail_max_height']['value'],
                $this->arrConfig['shop_thumbnail_quality']['value']
            )) {
                $this->strErrMessage .= $_ARRAYLANG['TXT_SHOP_ERROR_CREATING_CATEGORY_THUMBNAIL'];
            }
        }
        $objShopCategory->setPicture(basename($picture));
        $objShopCategory->setVirtual($virtual);
        if (!$objShopCategory->store()) {
            $this->strErrMessage .= $_ARRAYLANG['TXT_DATABASE_QUERY_ERROR'];
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
                $arrProducts = array();
                $blnDelCat = true;
                // Check whether this category has subcategories
                $query = "SELECT catid FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories WHERE 1 AND parentid=".intval($cId);
                $objResult = $objDatabase->Execute($query);
                if ($objResult->RecordCount() > 0) {
                    $blnDelCat = false;
                    $this->addError($_ARRAYLANG['TXT_CATEGORY_NOT_DELETED_BECAUSE_IN_USE']."&nbsp;(".$_ARRAYLANG['TXT_CATEGORY']."&nbsp;".$cId.")");
                }

                // Check whether the category can be deleted
                if ($blnDelCat) {
                    // Check whether products exist in this category
                    $query = "
                        SELECT id FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
                         WHERE 1 AND catid=".intval($cId);
                    $objResult = $objDatabase->Execute($query);
                    while (!$objResult->EOF) {
                        array_push($arrProducts, $objResult->fields['id']);
                        $objResult->MoveNext();
                    }

                    // Delete the products in the category
                    if (count($arrProducts) > 0) {
                        foreach ($arrProducts as $id) {
                            // Check whether there are orders with this
                            // product ID
                            $query = "SELECT 1 FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items WHERE productid=".$id;
                            $objResult = $objDatabase->Execute($query);
                            if ($objResult->RecordCount() > 0) {
                                $this->addError($_ARRAYLANG['TXT_COULD_NOT_DELETE_ALL_PRODUCTS']."&nbsp;(".$_ARRAYLANG['TXT_CATEGORY']."&nbsp;".$cId.")");
                                $blnDelCat = false;
                            } else {
                                $this->delProduct($id);
                            }
                        }
                    }

                    // Delete the category
                    if ($blnDelCat) {
                        $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories WHERE catid=".intval($cId);
                        if (!$objDatabase->Execute($query)) {
                            $this->errorHandling();
                        } else {
                            $blnDeletedCat = true;
                        }
                    }
                }
            }
            if ($blnDeletedCat) {
                $this->addMessage($_ARRAYLANG['TXT_DELETED_CATEGORY_AND_PRODUCTS']);
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
        global $objDatabase;

        $arrProductId = array();
        if (empty($productId)) {
            if (!empty($_REQUEST['id'])) {
                $arrProductId[] = $_REQUEST['id'];
            } elseif (!empty($_REQUEST['selectedProductId'])) {
                // This argument is an array!
                $arrProductId = $_REQUEST['selectedProductId'];
            }
        } else {
            if ($productId > 0) {
                $arrProductId[] = $productId;
            }
        }

        $result = true;
        if (count($arrProductId) > 0) {
            foreach ($arrProductId as $id) {
                $objProduct = Product::getById($id);
                if (!Products::deleteByCode($objProduct->getCode(), false)) {
                    $result = false;
                }
            }
        }
        return $result;
    }


    function delFile($file)
    {
        @unlink($file);
        clearstatcache();
        if (@file_exists($file)) {
            $filesys = eregi_replace("/","\\", $file);
            @system("del $filesys");
            clearstatcache();

            // don't work in safemode
            if (@file_exists($file)) {
                @chmod ($file, 0775);
                @unlink($file);
            }
        }
        clearstatcache();
        if (@file_exists($file)) {
            return false;
        } else {
            return true;
        }
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
        global $objDatabase, $_ARRAYLANG, $_FILES, $_CONFIG;

        // init default values
        $shopProductId            =  0;

        $shopProductName          = '';
        $shopProductIdentifier    = '';
        $shopCatMenu              = '';
        $shopCustomerPrice        = 0;
        $shopResellerPrice        = 0;
        $shopSpecialOffer         = 0;
        $shopDiscount             = 0;
        $shopTaxId                = 0;
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

// TODO: Is $shopTempThumbnailName, and its session equivalent,
// still in use anywhere?
        if (isset($_SESSION['shopPM']['TempThumbnailName'])) {
            $shopTempThumbnailName = $_SESSION['shopPM']['TempThumbnailName'];
            unset($_SESSION['shopPM']['TempThumbnailName']);
        }

        $shopProductId = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
        $objProduct = false;

        // Store Product data if form is sent
        if (isset($_POST['shopProductName'])) {
            $shopProductName          = contrexx_addslashes(strip_tags($_POST['shopProductName']));
            $shopProductIdentifier    = contrexx_addslashes(strip_tags($_POST['shopProductIdentifier']));
            $shopCatMenu              = intval($_POST['shopCatMenu']);
            $shopCustomerPrice        = floatval($_POST['shopCustomerPrice']);
            $shopResellerPrice        = floatval($_POST['shopResellerPrice']);
            $shopSpecialOffer         =
                (isset($_POST['shopSpecialOffer'])
                    ? intval($_POST['shopSpecialOffer'])
                    : 0
                );
            $shopDiscount             = floatval($_POST['shopDiscount']);
            $shopTaxId                = (isset($_POST['shopTaxId']) ? $_POST['shopTaxId'] : 0);
            $shopWeight               = Weight::getWeight($_POST['shopWeight']);
            $shopDistribution         = $_POST['distribution'];
            $shopShortDescription     = contrexx_addslashes($_POST['shopShortDescription']);
            $shopDescription          = contrexx_addslashes($_POST['shopDescription']);
            $shopStock                = intval($_POST['shopStock']);
            $shopStockVisibility      =
                (isset($_POST['shopStockVisibility']) ? 1 : 0);
            $shopManufacturerUrl      = htmlspecialchars(contrexx_addslashes(strip_tags($_POST['shopManufacturerUrl'])), ENT_QUOTES, CONTREXX_CHARSET);
            $shopArticleActive        =
                (isset($_POST['shopArticleActive']) ? 1 : 0);
            $shopB2B                  = intval($_POST['shopB2B']);
            $shopB2C                  = intval($_POST['shopB2C']);
            $shopStartdate            = !empty($_POST['shopStartdate']) ? contrexx_addslashes($_POST['shopStartdate']) : '0000-00-00 00:00:00';
            $shopEnddate              = !empty($_POST['shopEnddate']) ? contrexx_addslashes($_POST['shopEnddate']) : '0000-00-00 00:00:00';
            // begin image attributes
            // these are all unused!
//            $shopImageWidth           = intval($_POST['shopImageWidth']);
//            $shopThumbnailPercentSize = intval($_POST['shopThumbnailPercentSize']);
//            $shopImageQuality         = intval($_POST['shopImageQuality']);
//            $shopImageName            = contrexx_addslashes(strip_tags($_POST['shopImageName']));
//            $shopImageOverwrite       = intval($_POST['shopImageOverwrite']);
            $shopManufacturerId       = intval($_POST["shopManufacturerId"]);
            $shopFlags                =
                (isset($_POST['shopFlags'])
                    ? join(' ', $_POST['shopFlags'])
                    : ''
                );

            // check incoming picture file paths
            for ($i = 1; $i <= 3; $i++) {
                $imageDir = dirname($_POST['productImage'.$i]).'/';
                if ($imageDir != $this->shopImageWebPath) {
                    // copy image to shop image folder
                    $imageFile = basename($_POST['productImage'.$i]);
                    if (file_exists(ASCMS_PATH.$this->shopImageWebPath.$imageFile)) {
                        $this->addError(
                            $this->shopImageWebPath.$imageFile.': '.
                            $_ARRAYLANG['TXT_SHOP_FILE_EXISTS']
                        );
                    }
                    if (!copy(ASCMS_PATH.$imageDir.$imageFile,
                        ASCMS_PATH.$this->shopImageWebPath.$imageFile)) {
                        $this->addError(
                            $imageDir.$imageFile.': '.
                            $_ARRAYLANG['TXT_SHOP_COULD_NOT_COPY_FILE']
                        );
                    }
                }
            }
            // add all to pictures DBstring
            $shopImageName =
                 base64_encode(basename($_POST['productImage1']))
            .'?'.base64_encode($_POST['productImage1_width'])
            .'?'.base64_encode($_POST['productImage1_height'])
            .':'.base64_encode(basename($_POST['productImage2']))
            .'?'.base64_encode($_POST['productImage2_width'])
            .'?'.base64_encode($_POST['productImage2_height'])
            .':'.base64_encode(basename($_POST['productImage3']))
            .'?'.base64_encode($_POST['productImage3_width'])
            .'?'.base64_encode($_POST['productImage3_height']);
        }

        if (isset($_POST['shopStoreProduct'])) {
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
            if ($shopProductIdentifier != '') {
                $arrProduct = Products::getByCustomId($shopProductIdentifier);
            } else {
                $arrProduct = array($objProduct);
            }
            if (!is_array($arrProduct)) {
                return false;
            }
            foreach ($arrProduct as $objProduct) {

                // Update product
                $objProduct = Product::getById($shopProductId);

                $objProduct->setCode($shopProductIdentifier);
// TODO: Only change the parent ShopCategory for a Product
// that is in a real ShopCategory.
//                    $objProduct->setShopCategoryId($shopCatMenu);
                $objProduct->setName($shopProductName);
                $objProduct->setDistribution($shopDistribution);
                $objProduct->setPrice($shopCustomerPrice);
                $objProduct->setStatus($shopArticleActive);
                $objProduct->setWeight($shopWeight);
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

                // Remove old Product Attributes.
                // They are re-added below.
                $objProduct->clearAttributes();

                // Add new product attributes
                if (isset($_POST['productOptionsValues'])
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
            Products::changeFlagsByProductCode(
                $shopProductIdentifier, $shopFlags
            );

            if ($shopProductId > 0) {
                $_SESSION['shop']['strOkMessage'] = $_ARRAYLANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL'];
            } else {
                $_SESSION['shop']['strOkMessage'] = $_ARRAYLANG['TXT_DATA_RECORD_ADDED_SUCCESSFUL'];
            }

            if (   !empty($shopTempThumbnailName)
                && file_exists($this->shopImagePath.$shopTempThumbnailName)) {
                @unlink($this->shopImagePath.$shopTempThumbnailName);
            }

            $objImage = new ImageManager();
            $arrImages = $this->_getShopImagesFromBase64String($shopImageName);
            // create thumbnails if not available
            foreach ($arrImages as $arrImage) {
                if (   !empty($arrImage['img'])
                    && $arrImage['img'] != $this->noPictureName) {
                    if (!$objImage->_createThumbWhq(
                        $this->shopImagePath,
                        $this->shopImageWebPath,
                        $arrImage['img'],
                        $this->arrConfig['shop_thumbnail_max_width']['value'],
                        $this->arrConfig['shop_thumbnail_max_height']['value'],
                        $this->arrConfig['shop_thumbnail_quality']['value']
                    )) {
                        $this->addError(sprintf($_ARRAYLANG['TXT_SHOP_COULD_NOT_CREATE_THUMBNAIL'], $arrImage['img']));
                    }
                }
            }

            switch ($_POST['shopAfterStoreAction']) {
                case "newEmpty":
                    header("Location: index.php?cmd=shop".MODULE_INDEX."&act=products&tpl=manage");
                    exit();
                case "newTemplate":
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
        $this->_objTpl->addBlockfile('SHOP_PRODUCTS_FILE', 'shop_products_block', 'module_shop_product_manage.html');

        // begin language variables
        $this->_objTpl->setVariable(array(
            'TXT_PRODUCT_ID'              => $_ARRAYLANG['TXT_PRODUCT_ID'],
            'TXT_SHOP_PRODUCT_CUSTOM_ID'  => $_ARRAYLANG['TXT_SHOP_PRODUCT_CUSTOM_ID'],
            'TXT_MANUFACTURER_URL'        => $_ARRAYLANG['TXT_MANUFACTURER_URL'],
            'TXT_WITH_HTTP'               => $_ARRAYLANG['TXT_WITH_HTTP'],
            'TXT_PRODUCT_INFORMATIONS'    => $_ARRAYLANG['TXT_PRODUCT_INFORMATIONS'],
            'TXT_ADD_NEW'                 => $_ARRAYLANG['TXT_ADD_NEW'],
            'TXT_OVERWRITE'               => $_ARRAYLANG['TXT_OVERWRITE'],
            'TXT_IMAGES_WITH_SAME_NAME'   => $_ARRAYLANG['TXT_IMAGES_WITH_SAME_NAME'],
            'TXT_ACTION_AFTER_SAVEING'    => $_ARRAYLANG['TXT_ACTION_AFTER_SAVEING'],
            'TXT_PRODUCT_CATALOG'         => $_ARRAYLANG['TXT_PRODUCT_CATALOG'],
            'TXT_ADD_PRODUCTS'            => $_ARRAYLANG['TXT_ADD_PRODUCTS'],
            'TXT_FROM_TEMPLATE'           => $_ARRAYLANG['TXT_FROM_TEMPLATE'],
            'TXT_PRODUCT_NAME'            => $_ARRAYLANG['TXT_PRODUCT_NAME'],
            'TXT_CUSTOMER_PRICE'          => $_ARRAYLANG['TXT_CUSTOMER_PRICE'],
            'TXT_ID'                      => $_ARRAYLANG['TXT_ID'],
            'TXT_RESELLER_PRICE'          => $_ARRAYLANG['TXT_RESELLER_PRICE'],
            'TXT_SHORT_DESCRIPTION'       => $_ARRAYLANG['TXT_SHORT_DESCRIPTION'],
            'TXT_DESCRIPTION'             => $_ARRAYLANG['TXT_DESCRIPTION'],
            'TXT_ACTIVE'                  => $_ARRAYLANG['TXT_ACTIVE'],
            'TXT_CATEGORY'                => $_ARRAYLANG['TXT_CATEGORY'],
            'TXT_STOCK'                   => $_ARRAYLANG['TXT_STOCK'],
            'TXT_SPECIAL_OFFER'           => $_ARRAYLANG['TXT_SPECIAL_OFFER'],
            'TXT_IMAGE_WIDTH'             => $_ARRAYLANG['TXT_IMAGE_WIDTH'],
            'TXT_IMAGE'                   => $_ARRAYLANG['TXT_IMAGE'],
            'TXT_THUMBNAIL_SIZE'          => $_ARRAYLANG['TXT_THUMBNAIL_SIZE'],
            'TXT_QUALITY'                 => $_ARRAYLANG['TXT_QUALITY'],
            'TXT_STORE'                   => $_ARRAYLANG['TXT_STORE'],
            'TXT_RESET'                   => $_ARRAYLANG['TXT_RESET'],
            'TXT_ENABLED_FILE_EXTENSIONS' => $_ARRAYLANG['TXT_ENABLED_FILE_EXTENSIONS'],
            'TXT_ACTIVE'                  => $_ARRAYLANG['TXT_ACTIVE'],
            'TXT_INACTIVE'                => $_ARRAYLANG['TXT_INACTIVE'],
            'TXT_START_DATE'              => $_ARRAYLANG['TXT_START_DATE'],
            'TXT_END_DATE'                => $_ARRAYLANG['TXT_END_DATE'],
            'TXT_THUMBNAIL_SIZE'          => $_ARRAYLANG['TXT_THUMBNAIL_SIZE'],
            'TXT_THUMBNAIL_PREVIEW'       => $_ARRAYLANG['TXT_THUMBNAIL_PREVIEW'],
            'TXT_THUMBNAIL_SETTINGS'      => $_ARRAYLANG['TXT_THUMBNAIL_SETTINGS'],
            'TXT_IMAGE_DIMENSION'         => $_ARRAYLANG['TXT_IMAGE_DIMENSION'],
            'TXT_PRODUCT_STATUS'          => $_ARRAYLANG['TXT_PRODUCT_STATUS'],
            'TXT_IMAGE_SIZE'              => $_ARRAYLANG['TXT_IMAGE_SIZE'],
            'TXT_PIXEL'                   => $_ARRAYLANG['TXT_PIXEL'],
            'TXT_PRODUCT_IMAGE'           => $_ARRAYLANG['TXT_PRODUCT_IMAGE'],
            'TXT_OPTIONS'                 => $_ARRAYLANG['TXT_OPTIONS'],
            'TXT_IMAGE_UPLOAD'            => $_ARRAYLANG['TXT_IMAGE_UPLOAD'],
            'TXT_IMAGE_INFORMATIONS'      => $_ARRAYLANG['TXT_IMAGE_INFORMATIONS'],
            'TXT_IMAGE_NAME'              => $_ARRAYLANG['TXT_IMAGE_NAME'],
            'TXT_PRODUCT_OPTIONS'         => $_ARRAYLANG['TXT_PRODUCT_OPTIONS'],
            'TXT_SHOP_EDIT_OR_ADD_IMAGE'  => $_ARRAYLANG['TXT_SHOP_EDIT_OR_ADD_IMAGE'],
            'TXT_TAX_RATE'                => $_ARRAYLANG['TXT_TAX_RATE'],
            'TXT_WEIGHT'                  => $_ARRAYLANG['TXT_WEIGHT'],
            'TXT_DISTRIBUTION'            => $_ARRAYLANG['TXT_DISTRIBUTION'],
            'TXT_SHOP_MANUFACTURER'       => $_ARRAYLANG['TXT_SHOP_MANUFACTURER'],
        ));
        // end language variables

        // if new entry, set default values
        if (!isset($_REQUEST['id'])) { //OR $_REQUEST['new']
            $this->_objTpl->setVariable(array(
                'SHOP_CAT_MENU'                      =>
                    $this->objShopCategories->getShopCategoriesMenu($shopCatMenu, false),
                'SHOP_PRODUCT_CUSTOM_ID'             => '',
                'SHOP_DATE'                          => date("Y-m-d H:m"),
                'SHOP_ARTICLE_ACTIVE'                => $shopArticleActive,
                'SHOP_B2B'                           => $shopB2B,
                'SHOP_B2C'                           => $shopB2C,
                'SHOP_THUMBNAIL_LINK'                => "",
//                'SHOP_SELECTED_THUMBNAIL_PERCENTAGE' => "<option value=\"".$shopThumbnailPercentSize."\" selected=\"selected\">$shopThumbnailPercentSize%</option>",
//                'SHOP_SELECTED_THUMBNAIL_QUALITY'    => "<option value=\"".$shopImageQuality."\" selected=\"selected\">$shopImageQuality%</option>",
                'SHOP_COMMENT_START'                 => "<!--",
                'SHOP_COMMENT_END'                   => "-->",
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
            $objProduct = new Product('', 0, '', '', 0, 0, 0, 0);
        }

        if ($objProduct->isB2B()) {
            $shopB2B = 'checked="checked"';
        }
        if ($objProduct->isB2C()) {
            $shopB2C = 'checked="checked"';
        }
        if ($objProduct->getStatus()) {
            $shopArticleActive = 'checked="checked"';
        }
        if ($objProduct->isStockVisible()) {
            $shopStockVisibility = 'checked="checked"';
        }
        if ($objProduct->isSpecialOffer) {
            $shopSpecialOffer = 'checked="checked"';
        } else {
            $shopSpecialOffer = '';
        }

        // extract product image infos (path, width, height)
        $arrImages = $this->_getShopImagesFromBase64String(
            $objProduct->getPictures()
        );

        $shopFlagsSelection =
            ShopCategories::getVirtualCategoriesSelectionForFlags(
                $objProduct->getFlags()
            );
        if ($shopFlagsSelection) {
            $this->_objTpl->setVariable(array(
                'TXT_SHOP_FLAGS'       => $_ARRAYLANG['TXT_SHOP_FLAGS'],
                'SHOP_FLAGS_SELECTION' => $shopFlagsSelection,
            ));
        }

        $this->_objTpl->setVariable(array(
            'SHOP_PRODUCT_ID'             =>
                (isset($_REQUEST['new']) ? 0 : $objProduct->getId()),
            'SHOP_PRODUCT_CUSTOM_ID'      => $objProduct->getCode(),
            'SHOP_DATE'                   => date("Y-m-d H:m"),
            'SHOP_PRODUCT_NAME'           => $objProduct->getName(),
            'SHOP_CAT_MENU'               =>
                $this->objShopCategories->getShopCategoriesMenu(
                    $objProduct->getShopCategoryId(), false
                ),
            'SHOP_CUSTOMER_PRICE'         =>
                Currency::formatPrice($objProduct->getPrice()),
            'SHOP_RESELLER_PRICE'         =>
                Currency::formatPrice($objProduct->getResellerPrice()),
            'SHOP_DISCOUNT'               =>
                Currency::formatPrice($objProduct->getDiscountPrice()),
            'SHOP_SPECIAL_OFFER'          => $shopSpecialOffer,
            'SHOP_TAX'                    =>
                $this->objVat->getLongMenuString(
                    $objProduct->getVatId(), 'shopTaxId', "style='width: 220px'"
                ),
            'SHOP_WEIGHT'                 =>
                Weight::getWeightString($objProduct->getWeight()),
            'SHOP_DISTRIBUTION_MENU'      =>
                $this->objDistribution->getDistributionMenu(
                    $objProduct->getDistribution(),
                    'distribution',
                    "style='width: 220px'"
                ),
            'SHOP_SHORT_DESCRIPTION'      =>
                get_wysiwyg_editor(
                    'shopShortDescription',
                    $objProduct->getShortDesc(),
                    'shop'
                ),
            'SHOP_DESCRIPTION'            =>
                get_wysiwyg_editor(
                    'shopDescription',
                    $objProduct->getDescription(),
                    'shop'
                ),
            'SHOP_STOCK'                  => $objProduct->getStock(),
            'SHOP_MANUFACTURER_URL'       =>
                htmlentities(
                    $objProduct->getExternalLink(),
                    ENT_QUOTES, CONTREXX_CHARSET
                ),
            'SHOP_STARTDATE'              => $objProduct->getStartDate(),
            'SHOP_ENDDATE'                => $objProduct->getEndDate(),
            'SHOP_ARTICLE_ACTIVE'         => $shopArticleActive,
            'SHOP_B2B'                    => $shopB2B,
            'SHOP_B2C'                    => $shopB2C,
            'SHOP_STOCK_VISIBILITY'       => $shopStockVisibility,
            'SHOP_MANUFACTURER_SELECT'    =>
                $this->getManufacturerMenu($objProduct->getManufacturerId()),
            'SHOP_PICTURE1_IMG_SRC'       =>
                (!empty($arrImages[1]['img']) && is_file($this->shopImagePath.$arrImages[1]['img'].$this->thumbnailNameSuffix)
                    ? $this->shopImageWebPath.$arrImages[1]['img'].$this->thumbnailNameSuffix
                    : $this->_defaultImage
                ),
            'SHOP_PICTURE2_IMG_SRC'       =>
                (!empty($arrImages[2]['img']) && is_file($this->shopImagePath.$arrImages[2]['img'].$this->thumbnailNameSuffix)
                    ? $this->shopImageWebPath.$arrImages[2]['img'].$this->thumbnailNameSuffix
                    : $this->_defaultImage
                ),
            'SHOP_PICTURE3_IMG_SRC'       =>
                (!empty($arrImages[3]['img']) && is_file($this->shopImagePath.$arrImages[3]['img'].$this->thumbnailNameSuffix)
                    ? $this->shopImageWebPath.$arrImages[3]['img'].$this->thumbnailNameSuffix
                    : $this->_defaultImage
                ),
            'SHOP_PICTURE1_IMG_SRC_NO_THUMB' =>
                (!empty($arrImages[1]['img']) && is_file($this->shopImagePath.$arrImages[1]['img'])
                    ? $this->shopImageWebPath.$arrImages[1]['img']
                    : $this->_defaultImage
                ),
            'SHOP_PICTURE2_IMG_SRC_NO_THUMB' =>
                (!empty($arrImages[2]['img']) && is_file($this->shopImagePath.$arrImages[2]['img'])
                    ? $this->shopImageWebPath.$arrImages[2]['img']
                    : $this->_defaultImage
                ),
            'SHOP_PICTURE3_IMG_SRC_NO_THUMB' =>
                (!empty($arrImages[3]['img']) && is_file($this->shopImagePath.$arrImages[3]['img'])
                    ? $this->shopImageWebPath.$arrImages[3]['img']
                    : $this->_defaultImage
                ),
            'SHOP_PICTURE1_IMG_WIDTH'  => $arrImages[1]['width'],
            'SHOP_PICTURE1_IMG_HEIGHT' => $arrImages[1]['height'],
            'SHOP_PICTURE2_IMG_WIDTH'  => $arrImages[2]['width'],
            'SHOP_PICTURE2_IMG_HEIGHT' => $arrImages[2]['height'],
            'SHOP_PICTURE3_IMG_WIDTH'  => $arrImages[3]['width'],
            'SHOP_PICTURE3_IMG_HEIGHT' => $arrImages[3]['height'],
        ));

        if ($shopProductId == 0) {
            $this->_objTpl->setVariable(array(
            'SHOP_ID_COMMENT_START' => "<!--",
            'SHOP_ID_COMMENT_END'   => "-->",
            ));
        }
        return false;
    }


    /**
     * Show the stored orders
     * @access  public
     * @global  mixed   $objDatabase
     * @global  array   $_ARRAYLANG
     * @global  array   $_CONFIG
     * @author  Reto Kohli <reto.kohli@comvation.com> (parts)
     */
    function shopShowOrders()
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        $i = 0; // Used for rowclass
        $shopSearchPattern = '';
        $arrCurrency = $this->objCurrency->getCurrencyArray();

        // Update the order status if valid
        if (isset($_GET['changeOrderStatus']) &&
            intval($_GET['changeOrderStatus']) >= SHOP_ORDER_STATUS_PENDING &&
            intval($_GET['changeOrderStatus']) <= SHOP_ORDER_STATUS_COUNT &&
            !empty($_GET['orderId'])) {
            $query = "
                UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_orders
                   SET order_status='".intval($_GET['changeOrderStatus'])."',
                       modified_by ='".$_SESSION['auth']['username']."',
                       last_modified=NOW()
                 WHERE orderid=".intval($_GET['orderId']);
            $objDatabase->Execute($query);
        }

        // Send an email to the customer
        if (   !empty($_GET['shopSendMail'])
            && !empty($_GET['orderId'])) {
            $query = "
                SELECT c.email, o.last_modified, customer_lang
                  FROM ".DBPREFIX."module_shop".MODULE_INDEX."_customers c,
                       ".DBPREFIX."module_shop".MODULE_INDEX."_orders o
                 WHERE o.customerid=c.customerid
                   AND o.orderid=".intval($_GET['orderId']);
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
                $shopMailTo = $objResult->fields['email'];
                $shopLastModified = $objResult->fields['last_modified'];
            }
            $customerLang = $objResult->fields['customer_lang'];
            $langId = FWLanguage::getLangIdByIso639_1($customerLang);
            $arrShopMailtemplate = Shop::shopSetMailtemplate(2, $langId);
            $shopMailFrom = $arrShopMailtemplate['mail_from'];
            $shopMailFromText = $arrShopMailtemplate['mail_x_sender'];
            $shopMailSubject = $arrShopMailtemplate['mail_subject'];
            $shopMailBody = $arrShopMailtemplate['mail_body'];
            $shopMailSubject = str_replace("<DATE>", $shopLastModified, $shopMailSubject);
            $result = Shop::shopSendMail($shopMailTo, $shopMailFrom, $shopMailFromText, $shopMailSubject, $shopMailBody);
            if ($result) {
                $this->addMessage(sprintf($_ARRAYLANG['TXT_EMAIL_SEND_SUCCESSFULLY'], $shopMailTo));
            } else {
                $this->addError($_ARRAYLANG['TXT_MESSAGE_SEND_ERROR']);
            }
        }

        // Load template
        $this->pageTitle = $_ARRAYLANG['TXT_ORDERS'];
        $this->_objTpl->loadTemplateFile('module_shop_orders.html', true, true);

        // Set up filter and display options
        $shopCustomerOrderField = 'order_date';
        $shopCustomerOrder = "$shopCustomerOrderField DESC";
        $shopOrderStatus = -1;
        $shopCustomerType = -1;
        $shopSearchTerm = '';
        if (!empty($_POST['shopSearchTerm'])) {
            $shopSearchTerm = htmlspecialchars(
                $_POST['shopSearchTerm'], ENT_QUOTES, CONTREXX_CHARSET
            );
            $shopSearchPattern .=
                " AND (company LIKE '%$shopSearchTerm%'
                    OR firstname LIKE '%$shopSearchTerm%'
                    OR lastname LIKE '%$shopSearchTerm%'
                    OR address LIKE '%$shopSearchTerm%'
                    OR city LIKE '%$shopSearchTerm%'
                    OR phone LIKE '%$shopSearchTerm%'
                    OR email LIKE '%$shopSearchTerm%')";
        }
        if (isset($_POST['shopCustomerType'])) {
            $shopCustomerType = $_POST['shopCustomerType'];
            if ($shopCustomerType == 1) {
                $shopSearchPattern .= ' AND is_reseller=1';
            }
        }
        if (isset($_POST['shopOrderStatus'])
         && $_POST['shopOrderStatus'] >= 0
         && $_POST['shopOrderStatus'] <= SHOP_ORDER_STATUS_COUNT) {
            $shopOrderStatus = intval($_POST['shopOrderStatus']);
            $shopSearchPattern = " AND order_status='$shopOrderStatus'";
            // Check "Show pending orders" as well if these are selected
            if ($shopOrderStatus == SHOP_ORDER_STATUS_PENDING) {
                $_POST['shopShowPendingOrders'] = 1;
            }
        }
        if (isset($_POST['shopListSort'])) {
            $shopCustomerOrderField =
                addslashes(strip_tags($_POST['shopListSort']));
            $shopCustomerOrder = "$shopCustomerOrderField DESC";
        }
        // let the user choose whether to see pending orders or not
        if (!isset($_POST['shopShowPendingOrders'])) {
            $shopSearchPattern .=
                ' AND order_status!='.SHOP_ORDER_STATUS_PENDING;
        } else {
            $this->_objTpl->setVariable(
                'SHOP_SHOW_PENDING_ORDERS_CHECKED', ' checked="checked"'
            );
        }
        if (!empty($_POST['shopListLetter'])) {
            $shopLetter = htmlspecialchars(
                $_POST['shopListLetter'], ENT_QUOTES, CONTREXX_CHARSET
            );
            $shopListOrder = addslashes(strip_tags($_POST['shopListSort']));
            $shopSearchPattern .= " AND LEFT($shopListOrder, 1)='$shopLetter'";
        }

        $this->_objTpl->setVariable(array(
            'TXT_CUSTOMER_TYP'              => $_ARRAYLANG['TXT_CUSTOMER_TYP'],
            'TXT_CUSTOMER'                  => $_ARRAYLANG['TXT_CUSTOMER'],
            'TXT_RESELLER'                  => $_ARRAYLANG['TXT_RESELLER'],
            'TXT_FIRST_NAME'                => $_ARRAYLANG['TXT_FIRST_NAME'],
            'TXT_LAST_NAME'                 => $_ARRAYLANG['TXT_LAST_NAME'],
            'TXT_COMPANY'                   => $_ARRAYLANG['TXT_COMPANY'],
            'TXT_SORT_ORDER'                => $_ARRAYLANG['TXT_SORT_ORDER'],
            'TXT_ID'                        => $_ARRAYLANG['TXT_ID'],
            'TXT_DATE'                      => $_ARRAYLANG['TXT_DATE'],
            'TXT_NAME'                      => $_ARRAYLANG['TXT_NAME'],
            'TXT_ORDER_SUM'                 => $_ARRAYLANG['TXT_ORDER_SUM'],
            'TXT_ACTION'                    => $_ARRAYLANG['TXT_ACTION'],
            'TXT_CONFIRM_DELETE_ORDER'      => $_ARRAYLANG['TXT_CONFIRM_DELETE_ORDER'],
            'TXT_ACTION_IS_IRREVERSIBLE'    => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
            'TXT_CONFIRM_CHANGE_STATUS'     => $_ARRAYLANG['TXT_CONFIRM_CHANGE_STATUS'],
            'TXT_SEARCH'                    => $_ARRAYLANG['TXT_SEARCH'],
            'TXT_SEND_TEMPLATE_TO_CUSTOMER' =>
                str_replace('TXT_ORDER_COMPLETE',
                            $_ARRAYLANG['TXT_ORDER_COMPLETE'],
                            $_ARRAYLANG['TXT_SEND_TEMPLATE_TO_CUSTOMER']
                ),
            'TXT_MARKED'                    => $_ARRAYLANG['TXT_MARKED'],
            'TXT_SELECT_ALL'                => $_ARRAYLANG['TXT_SELECT_ALL'],
            'TXT_REMOVE_SELECTION'          => $_ARRAYLANG['TXT_REMOVE_SELECTION'],
            'TXT_SELECT_ACTION'             => $_ARRAYLANG['TXT_SELECT_ACTION'],
            'TXT_MAKE_SELECTION'            => $_ARRAYLANG['TXT_MAKE_SELECTION'],
            'TXT_SHOP_SHOW_PENDING_ORDERS'  => $_ARRAYLANG['TXT_SHOP_SHOW_PENDING_ORDERS'],
            'SHOP_SEARCH_TERM'              => $shopSearchTerm,
            'SHOP_ORDER_STATUS_MENU'        =>
                $this->getOrderStatusMenu($shopOrderStatus),
            'SHOP_CUSTOMER_TYPE_MENU'       =>
                Customers::getCustomerTypeMenu($shopCustomerType),
            'SHOP_CUSTOMER_SORT_MENU'       =>
                Customers::getCustomerSortMenu($shopCustomerOrderField),
        ));
        $this->_objTpl->setGlobalVariable(array(
            'TXT_STATUS'       => $_ARRAYLANG['TXT_STATUS'],
            'TXT_VIEW_DETAILS' => $_ARRAYLANG['TXT_VIEW_DETAILS'],
            'TXT_EDIT'         => $_ARRAYLANG['TXT_EDIT'],
            'TXT_DELETE'       => $_ARRAYLANG['TXT_DELETE'],
            'SHOP_CURRENCY'    => $arrCurrency[$this->objCurrency->defaultCurrencyId]['symbol']
        ));

        // create "search order status" listbox
        $strShopOrderSearchStatus = '<option value="5" selected="selected">-- '.$_ARRAYLANG['TXT_STATUS']." --</option>\n";
        foreach ($this->arrOrderStatus as $orderId => $orderStatus) {
            $strShopOrderSearchStatus .= "<option value='$orderId'>$orderStatus</option>\n";
        }
        $this->_objTpl->setVariable(array('SHOP_ORDER_SEARCH_STATUS' => $strShopOrderSearchStatus));

        // check whether a search has been requested
        $shopCustomerOrder = "order_date DESC";
        if (isset($_POST['shopSearchOrders']) OR isset($_POST['shopListLetter'])) {
            if (   $_POST['shopOrderStatus'] >= 0
                && $_POST['shopOrderStatus'] <= SHOP_ORDER_STATUS_COUNT) {
                $shopOrderStatus = intval($_POST['shopOrderStatus']);
                $shopSearchPattern = " AND order_status='$shopOrderStatus'";
            }

            if ($_POST['shopCustomer'] <= 1) {
                $shopCustomer = intval($_POST['shopCustomer']);
                $shopSearchPattern .= " AND is_reseller=$shopCustomer";
            }
            if (!empty($_POST['shopSearchTerm'])) {
                $searchTerm = htmlspecialchars($_POST['shopSearchTerm'], ENT_QUOTES, CONTREXX_CHARSET);
                $shopSearchPattern .= " AND (company LIKE '%$searchTerm%' OR firstname LIKE '%$searchTerm%' OR lastname LIKE '%$searchTerm%' OR address LIKE '%$searchTerm%'  OR city LIKE '%$searchTerm%' OR phone LIKE '%$searchTerm%' OR email LIKE '%$searchTerm%')";
            }
            if ($_POST['shopListLetter'] != '') {
                $shopLetter = htmlspecialchars($_POST['shopListLetter'], ENT_QUOTES, CONTREXX_CHARSET);
                $shopListOrder = addslashes(strip_tags($_POST['shopListSort']));
                $shopSearchPattern .= " AND LEFT($shopListOrder, 1)='$shopLetter' ";
            }
            if (isset($_POST['shopListSort'])) {
                $shopCustomerOrder = addslashes(strip_tags($_POST['shopListSort']))." DESC";
            }
            // let the user choose whether to see pending orders or not
            if (!isset($_POST['shopShowPendingOrders'])) {
                $shopSearchPattern = " AND order_status!='0'";
            } else {
                $this->_objTpl->setVariable(
                    'SHOP_SHOW_PENDING_ORDERS_CHECKED', 'checked="checked"'
                );
            }
        }

        // create sql query
        $query = "
            SELECT orderid, firstname, lastname, company,
                   currency_order_sum, selected_currency_id,
                   order_date, customer_note, order_status
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_customers c,
                   ".DBPREFIX."module_shop".MODULE_INDEX."_orders o
             WHERE c.customerid=o.customerid
                   $shopSearchPattern
          ORDER BY $shopCustomerOrder
        ";
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
                '&amp;cmd=shop'.MODULE_INDEX.'&amp;act=orders',
                $_ARRAYLANG['TXT_ORDERS'],
                $viewPaging
            );
        }
        $objResult = $objDatabase->SelectLimit($query, $shopPagingLimit, $pos);
        if (!$objResult) {
            // if query has errors, call errorhandling
            $this->errorHandling();
        } else {
            if ($objResult->RecordCount() < 1) {
                $this->_objTpl->hideBlock('orderTable');
            } else {
                $this->_objTpl->setCurrentBlock('orderRow');
                while (!$objResult->EOF) {
                    // PHP5! $tipNote = (strlen($objResult['customer_note'])>0) ? php_strip_whitespace($objResult['customer_note']) : '';
                    $tipNote = $objResult->fields['customer_note'];
                    $tipLink = (!empty($tipNote)
                        ? '<img src="images/icons/comment.gif" onmouseout="htm()" onmouseover="stm(Text['.
                          $objResult->fields['orderid'].'],Style[0])" width="11" height="10" alt="" title="" />'
                        : ''
                    );
                    // set currency id
                    $this->objCurrency->activeCurrencyId = $objResult->fields['selected_currency_id'];
                    $orderId = $objResult->fields['orderid'];
                    $orderStatus = $objResult->fields['order_status'];
                    $this->_objTpl->setVariable(array(
                        'SHOP_ROWCLASS'     =>
                            ($orderStatus == 0
                                ? 'rowWarn'
                                : (++$i % 2 ? 'row1' : 'row2')
                            ),
                        'SHOP_ORDERID'      => $orderId,
                        'SHOP_TIP_ID'       => $orderId,
                        'SHOP_TIP_NOTE'     =>
                            ereg_replace(
                                "\r\n|\n|\r", '<br />', htmlentities(strip_tags($tipNote))
                            ),
                        'SHOP_TIP_LINK'     => $tipLink,
                        'SHOP_DATE'         => $objResult->fields['order_date'],
                        'SHOP_NAME'         =>
                            strlen($objResult->fields['company']) > 1
                                ? trim($objResult->fields['company'])
                                : $objResult->fields['firstname'].' '.
                                  $objResult->fields['lastname'],
                        'SHOP_ORDER_SUM'    =>
                            $this->objCurrency->getDefaultCurrencyPrice(
                                $objResult->fields['currency_order_sum']),
                        'SHOP_ORDER_STATUS' => $this->getOrderStatusMenu(
                            $orderStatus,
                            'shopOrderStatusId['.$orderId.']',
                            'changeOrderStatus('.
                                $orderId.','.
                                $orderStatus.
                                ', this.value)'
                        ),
                    ));
                    $this->_objTpl->parse('orderRow');
                    $this->_objTpl->parse('tipMessageRow');
                    $objResult->MoveNext();
                }
            }
            $this->_objTpl->setVariable('SHOP_ORDER_PAGING', $paging);
        }
    }


    /**
     * Set up details of the selected order
     *
     * @access  public
     * @param   string  $templateName   Name of the template file
     * @param   integer $type           1: edit order, 0: just display it
     * @global  mixed   $objDatabase    Database
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
        $this->_objTpl->loadTemplateFile($templateName, true, true);

        // begin language variables
        $this->_objTpl->setVariable(array(
            'TXT_ORDER'                => $_ARRAYLANG['TXT_ORDER'],
            'TXT_ORDERNUMBER'          => $_ARRAYLANG['TXT_ORDERNUMBER'],
            'TXT_ORDERDATE'            => $_ARRAYLANG['TXT_ORDERDATE'],
            'TXT_ORDERSTATUS'          => $_ARRAYLANG['TXT_ORDERSTATUS'],
            'TXT_ORDER_SUM'            => $_ARRAYLANG['TXT_ORDER_SUM'],
            'TXT_BILL'                 => $_ARRAYLANG['TXT_BILL'],
            'TXT_SHIPPING_METHOD'      => $_ARRAYLANG['TXT_SHIPPING_METHOD'],
            'TXT_LAST_EDIT'            => $_ARRAYLANG['TXT_LAST_EDIT'],
            'TXT_BILLING_ADDRESS'      => $_ARRAYLANG['TXT_BILLING_ADDRESS'],
            'TXT_SHIPPING_ADDRESS'     => $_ARRAYLANG['TXT_SHIPPING_ADDRESS'],
            'TXT_COMPANY'              => $_ARRAYLANG['TXT_COMPANY'],
            'TXT_PREFIX'               => $_ARRAYLANG['TXT_PREFIX'],
            'TXT_FIRST_NAME'           => $_ARRAYLANG['TXT_FIRST_NAME'],
            'TXT_LAST_NAME'            => $_ARRAYLANG['TXT_LAST_NAME'],
            'TXT_ADDRESS'              => $_ARRAYLANG['TXT_ADDRESS'],
            'TXT_ZIP_CITY'             => $_ARRAYLANG['TXT_ZIP_CITY'],
            'TXT_PHONE'                => $_ARRAYLANG['TXT_PHONE'],
            'TXT_EMAIL'                => $_ARRAYLANG['TXT_EMAIL'],
            'TXT_COUNTRY'              => $_ARRAYLANG['TXT_COUNTRY'],
            'TXT_FAX'                  => $_ARRAYLANG['TXT_FAX'],
            'TXT_PAYMENT_INFORMATIONS' => $_ARRAYLANG['TXT_PAYMENT_INFORMATIONS'],
            'TXT_CREDIT_CARD_OWNER'    => $_ARRAYLANG['TXT_CREDIT_CARD_OWNER'],
            'TXT_CARD_NUMBER'          => $_ARRAYLANG['TXT_CARD_NUMBER'],
            'TXT_CVC_CODE'             => $_ARRAYLANG['TXT_CVC_CODE'],
            'TXT_EXPIRY_DATE'          => $_ARRAYLANG['TXT_EXPIRY_DATE'],
            'TXT_PAYMENT_TYPE'         => $_ARRAYLANG['TXT_PAYMENT_TYPE'],
            'TXT_NUMBER'               => $_ARRAYLANG['TXT_NUMBER'],
            'TXT_PRODUCT_ID'           => $_ARRAYLANG['TXT_ID'],
            'TXT_SHOP_PRODUCT_CUSTOM_ID' => $_ARRAYLANG['TXT_SHOP_PRODUCT_CUSTOM_ID'],
            'TXT_PRODUCT_NAME'         => $_ARRAYLANG['TXT_PRODUCT_NAME'],
            'TXT_PRODUCT_PRICE'        => $_ARRAYLANG['TXT_PRODUCT_PRICE'],
            'TXT_SUM'                  => $_ARRAYLANG['TXT_SUM'],
            'TXT_SHIPPING_PRICE'       => $_ARRAYLANG['TXT_SHIPPING_PRICE'],
            'TXT_PAYMENT_COSTS'        => $_ARRAYLANG['TXT_PAYMENT_COSTS'],
            'TXT_TOTAL'                => $_ARRAYLANG['TXT_TOTAL'],
            'TXT_CUSTOMER_REMARKS'     => $_ARRAYLANG['TXT_CUSTOMER_REMARKS'],
            'TXT_STORE'                => $_ARRAYLANG['TXT_STORE'],
            'TXT_EDIT'                 => $_ARRAYLANG['TXT_EDIT'],
            'TXT_IP_ADDRESS'           => $_ARRAYLANG['TXT_IP_ADDRESS'],
            'TXT_BROWSER_VERSION'      => $_ARRAYLANG['TXT_BROWSER_VERSION'],
            'TXT_CLIENT_HOST'          => $_ARRAYLANG['TXT_CLIENT_HOST'],
            'TXT_BROWSER_LANGUAGE'     => $_ARRAYLANG['TXT_BROWSER_LANGUAGE'],
            'TXT_SEND_MAIL_TO_ADDRESS' => $_ARRAYLANG['TXT_SEND_MAIL_TO_ADDRESS'],
            // inserted VAT, weight here
            // change header depending on whether the tax is included or excluded
            'TXT_TAX_RATE'             => ($this->objVat->isIncluded()
                                            ? $_ARRAYLANG['TXT_TAX_PREFIX_INCL']
                                            : $_ARRAYLANG['TXT_TAX_PREFIX_EXCL']),
            'TXT_WEIGHT'               => $_ARRAYLANG['TXT_WEIGHT'],
            'TXT_TOTAL_WEIGHT'         => $_ARRAYLANG['TXT_TOTAL_WEIGHT'],
            'TXT_NET_PRICE'            => $_ARRAYLANG['TXT_NET_PRICE'],
            'TXT_WARNING_SHIPPER_WEIGHT' => $_ARRAYLANG['TXT_WARNING_SHIPPER_WEIGHT'],
        ));

        $this->_objTpl->setGlobalVariable(array(
            'TXT_VIEW_DETAILS'         => $_ARRAYLANG['TXT_VIEW_DETAILS']
        ));

        $shopOrderId = intval($_REQUEST['orderid']);

        // lsv data
        $query = "
            SELECT * FROM contrexx_module_shop_lsv
             WHERE order_id=$shopOrderId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            $this->errorHandling();
        }
        if ($objResult->RecordCount() == 1) {
            $this->_objTpl->hideBlock('creditCard');
            $this->_objTpl->setVariable(array(
                'TXT_ACCOUNT_HOLDER'  => $_ARRAYLANG['TXT_ACCOUNT_HOLDER'],
                'TXT_ACCOUNT_BANK'    => $_ARRAYLANG['TXT_ACCOUNT_BANK'],
                'TXT_ACCOUNT_BLZ'     => $_ARRAYLANG['TXT_ACCOUNT_BLZ'],
                'SHOP_ACCOUNT_HOLDER' => $objResult->fields['holder'],
                'SHOP_ACCOUNT_BANK'   => $objResult->fields['bank'],
                'SHOP_ACCOUNT_BLZ'    => $objResult->fields['blz'],
            ));
        } else {
            $this->_objTpl->hideBlock('lsv');
        }

        // used below; will contain the Products from the database
        $arrProducts = array();

        // Order and Customer query (no products/order items)
        $query = "SELECT o.orderid, o.customerid, o.selected_currency_id, o.currency_order_sum, ".
            "o.order_date, o.order_status, o.last_modified, o.customerid, o.ship_prefix, ".
            "o.ship_company, o.ship_firstname, o.ship_lastname, o.ship_address, o.ship_zip, ".
            "o.ship_city, o.ship_country_id, o.ship_phone, o.currency_ship_price, o.tax_price, ".
            "o.shipping_id, o.payment_id, o.currency_payment_price, o.customer_ip, o.customer_host, ".
            "o.customer_lang, o.customer_browser, o.customer_note, o.modified_by, ".
            "c.customerid, c.prefix, c.company, c.firstname, c.lastname, c.address, c.zip, c.city, ".
            "c.country_id, c.phone, c.fax, c.ccnumber, c.cvc_code, c.ccdate, c.ccname, ".
            "c.company_note, c.email, c.is_reseller ".
            "FROM  ".DBPREFIX."module_shop".MODULE_INDEX."_customers AS c, ".DBPREFIX."module_shop".MODULE_INDEX."_orders AS o ".
            "WHERE c.customerid = o.customerid AND o.orderid = $shopOrderId";
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
                $orderStatus                         = $objResult->fields['order_status'];
                $this->objCurrency->activeCurrencyId = $selectedCurrencyId;
                $arrCurrency                         = $this->objCurrency->getCurrencyArray();
                $shipperName                         = $this->objShipment->getShipperName($shippingId);
                $this->_objTpl->setVariable(array(
                'SHOP_CUSTOMER_ID'      => $objResult->fields['customerid' ],
                'SHOP_ORDERID'          => $objResult->fields['orderid'],
                'SHOP_DATE'             => $objResult->fields['order_date'],
                'SHOP_ORDER_STATUS'     => ($type == 1
                    ? $this->getOrderStatusMenu(
                        $orderStatus,
                        'shopOrderStatusId',
                        'swapSendToStatus(this.value)'
                      )
                    : $this->arrOrderStatus[$orderStatus]),
                'SHOP_SEND_MAIL_STYLE'  =>
                    ($orderStatus == SHOP_ORDER_STATUS_CONFIRMED
                        ? 'display: inline;'
                        : 'display: none;'
                    ),
                'SHOP_SEND_MAIL_STATUS' =>
                    ($type == 1
                        ? ($orderStatus != SHOP_ORDER_STATUS_CONFIRMED
                            ? 'checked="checked"'
                            : ''
                          )
                        : ''
                    ),
                'SHOP_ORDER_SUM'        => $this->objCurrency->getDefaultCurrencyPrice($shopCurrencyOrderSum),
                'SHOP_DEFAULT_CURRENCY' => $arrCurrency[$this->objCurrency->defaultCurrencyId]['symbol'],
                'SHOP_PREFIX'           => $objResult->fields['prefix'],
                'SHOP_COMPANY'          => $objResult->fields['company'],
                'SHOP_FIRSTNAME'        => $objResult->fields['firstname'],
                'SHOP_LASTNAME'         => $objResult->fields['lastname'],
                'SHOP_ADDRESS'          => $objResult->fields['address'],
                'SHOP_ZIP'              => $objResult->fields['zip'],
                'SHOP_CITY'             => $objResult->fields['city'],
                'SHOP_COUNTRY'          => $this->arrCountries[$countryId]['countries_name'],
                'SHOP_SHIP_PREFIX'      => $objResult->fields['ship_prefix'],
                'SHOP_SHIP_COMPANY'     => $objResult->fields['ship_company'],
                'SHOP_SHIP_FIRSTNAME'   => $objResult->fields['ship_firstname'],
                'SHOP_SHIP_LASTNAME'    => $objResult->fields['ship_lastname'],
                'SHOP_SHIP_ADDRESS'     => $objResult->fields['ship_address'],
                'SHOP_SHIP_ZIP'         => $objResult->fields['ship_zip'],
                'SHOP_SHIP_CITY'        => $objResult->fields['ship_city'],
                'SHOP_SHIP_COUNTRY'     =>
                    ($type == 1
                        ?   $this->_getCountriesMenu('shopShipCountry', $objResult->fields['ship_country_id'])
                        :   $this->arrCountries[$objResult->fields['ship_country_id']]['countries_name']
                    ),
                'SHOP_SHIP_PHONE'       => $objResult->fields['ship_phone'],
                'SHOP_PHONE'            => $objResult->fields['phone'],
                'SHOP_FAX'              => $objResult->fields['fax'],
                'SHOP_EMAIL'            => $shopMailTo,
                'SHOP_PAYMENTTYPE'      => $this->arrPayment[$paymentId]['name'],
                'SHOP_CCNUMBER'         => $objResult->fields['ccnumber'],
                'SHOP_CCDATE'           => $objResult->fields['ccdate'],
                'SHOP_CCNAME'           => $objResult->fields['ccname'],
                'SHOP_CVC_CODE'         => $objResult->fields['cvc_code'],
                'SHOP_CUSTOMER_NOTE'    => $objResult->fields['customer_note'],
                'SHOP_CUSTOMER_IP'      =>
                    $objResult->fields['customer_ip'] == ''
                        ? '&nbsp;'
                        : '<a href="?cmd=nettools&amp;tpl=whois&amp;address='.
                          $objResult->fields['customer_ip'].'" title="'.$_ARRAYLANG['TXT_SHOW_DETAILS'].'">'.
                          $objResult->fields['customer_ip'].'</a>',
                'SHOP_CUSTOMER_HOST'    =>
                    $objResult->fields['customer_host'] == ''
                        ? '&nbsp;'
                        : '<a href="?cmd=nettools&amp;tpl=whois&amp;address='.
                          $objResult->fields['customer_host'].'" title="'.$_ARRAYLANG['TXT_SHOW_DETAILS'].'">'.
                          $objResult->fields['customer_host'].'</a>',
                'SHOP_CUSTOMER_LANG'    => $objResult->fields['customer_lang'] == '' ? '&nbsp;' : $objResult->fields['customer_lang'],
                'SHOP_CUSTOMER_BROWSER' => $objResult->fields['customer_browser'] == '' ? '&nbsp;' : $objResult->fields['customer_browser'],
                'SHOP_COMPANY_NOTE'     => $objResult->fields['company_note'],
                'SHOP_LAST_MODIFIED'    => $shopLastModified == 0 ? $_ARRAYLANG['TXT_ORDER_WASNT_YET_EDITED'] : $shopLastModified.'&nbsp;'.$_ARRAYLANG['TXT_EDITED_BY'].'&nbsp;'.$objResult->fields['modified_by'],
                'SHOP_SHIPPING_TYPE'    => $shipperName,
                ));

                // set shipment price or remove it from the details overview if empty
                if ($objResult->fields['currency_ship_price'] != 0) {
                    $this->_objTpl->setVariable(array('SHOP_SHIPPING_PRICE' => $objResult->fields['currency_ship_price']));
                } else {
//                    if ($type != 1) {
//                        $this->_objTpl->hideBlock('shopShipmentPrice');
//                    } else {
                        $this->_objTpl->setVariable(array('SHOP_SHIPPING_PRICE' => '0.00'));
//                    }
                }

                // set payment price or remove it from the details overview if empty
                if ($objResult->fields['currency_payment_price'] != 0) {
                    $this->_objTpl->setVariable(array('SHOP_PAYMENT_PRICE' => $objResult->fields['currency_payment_price']));
                } else {
//                    if ($type != 1) {
//                        $this->_objTpl->hideBlock('shopPaymentPrice');
//                    } else {
                        $this->_objTpl->setVariable(array('SHOP_PAYMENT_PRICE' => '0.00'));
//                    }
                }

                $this->_objTpl->setGlobalVariable(array(
                    'SHOP_CURRENCY' => $arrCurrency[$selectedCurrencyId]['symbol']
                ));

                // set the handler of the payment method
                $ppName = $this->objPayment->getPaymentProcessorName(
                    $this->arrPayment[$paymentId]['processor_id']
                );
                if ($ppName) {
                    $this->_objTpl->setVariable(array('SHOP_PAYMENT_HANDLER' => $ppName));
                } else {
                    $this->errorHandling();
                }

                // set last modified date of the order
                $this->_objTpl->setVariable(array(
                    'SHOP_LAST_MODIFIED_DATE' => $shopLastModified
                ));
            }

            if ($type == 1) {
                // edit order
                // set language vars
                $this->_objTpl->setVariable(array(
                    'TXT_PRODUCT_ALREADY_PRESENT'   => $_ARRAYLANG['TXT_PRODUCT_ALREADY_PRESENT'],
                    'TXT_SEND_TEMPLATE_TO_CUSTOMER' => str_replace('TXT_ORDER_COMPLETE', $_ARRAYLANG['TXT_ORDER_COMPLETE'], $_ARRAYLANG['TXT_SEND_TEMPLATE_TO_CUSTOMER']),
                ));

                // shipper menu and javascript array
                $strJsArrShipment = $this->objShipment->getJSArrays($this->objCurrency);
                $this->_objTpl->setVariable(array(
                    'SHOP_SHIPPING_TYP_MENU' => $this->objShipment->getShipperMenu(
                        $objResult->fields['ship_country_id'],
                        $objResult->fields['shipping_id'],
                        "javascript:calcPrice()"),
                    'SHOP_JS_ARR_SHIPMENT'   => $strJsArrShipment
                ));

/*
    This shouldn't be changed afterwards.
    The credit card or LSV information cannot be edited (yet) anyway!
                // payment menu and javascript array
                $strJsArrPayment = "arrPayment = new Array();\n";
                foreach ($this->objPayment->arrPaymentObject as $arrPaymentMethod) {
                    if ($arrPaymentMethod['status'] == 1) {
                        $strJsArrPayment .= "arrPayment[".$arrPaymentMethod['id']."] = '".$this->objCurrency->getCurrencyPrice($arrPaymentMethod['costs'])."';\n";
                    }
                }
                $this->_objTpl->setVariable(array(
                    'SHOP_PAYMENT_TYP_MENU' =>
                        $this->objPayment->getPaymentMenu(
                            $this->arrPayment[$paymentId]['id'],
                            "updatePayment(this.value)",
                            $countryId,
                            $this->objCurrency->arrCurrency
                        ),
                    'SHOP_JS_ARR_PAYMENT'   => $strJsArrPayment,
                ));
*/

                // set products menu and javascript array
                $query = '
                    SELECT id, product_id, title,
                        resellerprice, normalprice, discountprice, is_special_offer,
                        weight, vat_id
                    FROM '.DBPREFIX.'module_shop'.MODULE_INDEX.'_products
                    WHERE status=1
                ';
                $objResult = $objDatabase->Execute($query);
                if (!$objResult) {
                    $this->errorHandling();
                } else {
                    while (!$objResult->EOF) {
                        $arrProducts[$objResult->fields['id']] = array(
                            'id'               => $objResult->fields['id'],
                            'product_id'       => $objResult->fields['product_id'],
                            'title'            => $objResult->fields['title'],
                            'resellerprice'    => $objResult->fields['resellerprice'],
                            'normalprice'      => $objResult->fields['normalprice'],
                            'discountprice'    => $objResult->fields['discountprice'],
                            'is_special_offer' => $objResult->fields['is_special_offer'],
                            // Store VAT as percentage, not ID, as we will only update the order items
                            'percent'          => $this->objVat->getRate($objResult->fields['vat_id']),
                            'weight'           => Weight::getWeightString($objResult->fields['weight']),
                        );
                        $objResult->MoveNext();
                    }
                    // create javascript array containing all products;
                    // used to update the display when changing the product ID.
                    // we need the VAT rate in there as well in order to be able to correctly change the products,
                    // and the flag indicating whether the VAT is included in the prices already.
                    $strJsArrProduct = "var vat_included = ".
                        $this->objVat->isIncluded().
                        ";\nvar arrProducts = new Array();\n";
                    foreach ($arrProducts as $arrProduct) {
                        // the menu for a new product - no preselected value
                        $menu .= "<option value='".$arrProduct['id']."'>".$arrProduct['id']."</option>\n";
                        $strJsArrProduct .=
                            "arrProducts[".$arrProduct['id']."] = new Array();\n".
                            "arrProducts[".$arrProduct['id']."]['id'] = ".$arrProduct['id'].";\n".
                            "arrProducts[".$arrProduct['id']."]['product_id'] = '".$arrProduct['product_id']."';\n".
                            "arrProducts[".$arrProduct['id']."]['title'] = '".addslashes($arrProduct['title'])."';\n".
                            "arrProducts[".$arrProduct['id']."]['percent'] = '".$arrProduct['percent']."';\n".
                            "arrProducts[".$arrProduct['id']."]['weight'] = '".$arrProduct['weight']."';\n";
                        if ($isReseller) {
                            $strJsArrProduct .= "arrProducts[".$arrProduct['id']."]['price'] = '".
                            $this->objCurrency->getCurrencyPrice($arrProduct['resellerprice'])."';\n";
                        } else {
                            if ($arrProducts[$arrProduct['id']]['is_special_offer']) {
                                $strJsArrProduct .= "arrProducts[".$arrProduct['id']."]['price'] = '".
                                $this->objCurrency->getCurrencyPrice($arrProduct['discountprice'])."';\n";
                            } else {
                                $strJsArrProduct .= "arrProducts[".$arrProduct['id']."]['price'] = '".
                                $this->objCurrency->getCurrencyPrice($arrProduct['normalprice'])."';\n";
                            }
                        }
                    }
                }
                $this->_objTpl->setVariable(array(
                    'SHOP_PRODUCT_IDS_MENU_NEW' => $menu,
                    'SHOP_JS_ARR_PRODUCT'       => $strJsArrProduct
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
                array_push($arrProductOptions[$objResult->fields['order_items_id']]['options'],
                $objResult->fields['product_option_name'].": ".
                $objResult->fields['product_option_value']);
                $objResult->MoveNext();
            }
        }

        // set up the order details
        $query = "SELECT order_items_id, product_name, productid, price, quantity, vat_percent, weight ".
        "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items ".
        "WHERE orderid = $shopOrderId";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            $this->errorHandling();
        } else {
            $this->_objTpl->setCurrentBlock("orderdetailsRow");
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
                    $productName = $objResult->fields['product_name'].
                    (isset($arrProductOptions[$objResult->fields['order_items_id']])
                        ?   '<br /><i>-'.implode('; ', $arrProductOptions[$objResult->fields['order_items_id']]['options']).'</li>'
                        :   ''
                    );
                }

                $productPrice    = $objResult->fields['price'];
                $productQuantity = $objResult->fields['quantity'];
                $productVatRate  = $objResult->fields['vat_percent'];
                // $rowNetPrice means 'product times price' from here
                $rowNetPrice  = $productPrice * $productQuantity;
                $rowPrice     = $rowNetPrice; // VAT added later, if applicable
                $rowVatAmount = 0;
                $total_net_price += $rowNetPrice;

                // here, the vat has to be recalculated before setting the fields up.
                // if the VAT is excluded, it must be added here.
                // note: the old shop_order.tax_price field is no longer valid, individual
                // shop_order_items *MUST* have been UPDATEd by the time PHP parses this line.
                // note that this also implies that the tax_number.status and country_id
                // can be ignored, as they are considered when the order is placed
                // and the VAT is applied to the order accordingly.
                //if ($this->arrConfig['tax_number']['status'] == 1 && $countryId == $this->arrConfig['country_id']['value']) {

                // calculate the VAT amount per row, included or excluded
                $rowVatAmount = $this->objVat->amount($productVatRate, $rowNetPrice);
                // and add it to the total VAT amount
                $total_vat_amount += $rowVatAmount;

                if (!$this->objVat->isIncluded()) {
                    // add tax to price
                    $rowPrice += $rowVatAmount;
                }
                //} else {
                    // VAT is disabled.
                    // there shouldn't be any non-zero percentages in the order_items!
                    // but if there are, there probably has been a change and we *SHOULD*
                    // still treat them as if VAT had been enabled at the time the order
                    // was placed!
                    // that's why the if...then...else is commented out.
                //}

                $weight = $objResult->fields['weight'];
                if (intval($weight) > 0) {
                    $total_weight += $weight*$productQuantity;
                }

                // get product code (aka custom id)
                $query = "
                    SELECT product_id FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
                    WHERE id=".$objResult->fields['productid'];
                $objResult2 = $objDatabase->Execute($query);
                $productCode = '';
                if (!$objResult2 || $objResult2->EOF) {
                    $this->errorHandling();
                } else {
                    $productCode = $objResult2->fields['product_id'];
                }

                $this->_objTpl->setVariable(array(
                    'SHOP_ROWCLASS'           => (++$i % 2 ? 'row2' : 'row1'),
                    'SHOP_QUANTITY'           => $productQuantity,
                    'SHOP_PRODUCT_NAME'       => $productName,
                    'SHOP_PRODUCT_PRICE'      => Currency::formatPrice($productPrice),
                    'SHOP_PRODUCT_SUM'        => Currency::formatPrice($rowNetPrice),
                    'SHOP_P_ID'               =>
                        ($type == 1
                            ? $objResult->fields['order_items_id'] // edit order
                            // if we're only showing the order details, the product ID is only used in the product ID column
                            : $objResult->fields['productid']
                        ),    // show order
                    'SHOP_PRODUCT_CUSTOM_ID'  => $productCode,
                    // fill VAT field
                    'SHOP_PRODUCT_TAX_RATE'   =>
                        ($type == 1
                            ? $productVatRate
                            : Vat::getShort($productVatRate)
                        ),
                    'SHOP_PRODUCT_TAX_AMOUNT' => Currency::formatPrice($rowVatAmount),
                    // fill weight field only if it's set to a non-zero value
                    'SHOP_PRODUCT_WEIGHT'     => Weight::getWeightString($weight),
                ));

                // get a product menu for each product if $type == 1 (edit)
                // preselects the current product id
                // TODO: move this to Product.class.php once it's available
                if ($type == 1) {
                    $menu = '';
                    foreach ($arrProducts as $arrProduct) {
                        $menu .= '<option value="'.$arrProduct['id'].'"';
                        if ($arrProduct['id'] == $objResult->fields['productid']) {
                            $menu .= ' selected="selected"';
                        }
                        $menu .= '>'.$arrProduct['id']."</option>\n";
                    }
                    $this->_objTpl->setVariable(array(
                        'SHOP_PRODUCT_IDS_MENU' => $menu
                    ));
                }
                $this->_objTpl->parse('orderdetailsRow');
                $objResult->MoveNext();
            }

            // Show VAT with the individual products:
            // If VAT is enabled, and we're both in the same country
            // ($total_vat_amount has been set above if both conditions are met)
            // show the VAT rate.  If there is no VAT, the amount is 0 (zero, '', nil, nada).
            //if ($total_vat_amount) {
                // distinguish between included VAT, and additional VAT added to sum
                $tax_part_percentaged = ($this->objVat->isIncluded()
                    ?   $_ARRAYLANG['TXT_TAX_PREFIX_INCL']
                    :   $_ARRAYLANG['TXT_TAX_PREFIX_EXCL']
                );
                $this->_objTpl->setVariable(array(
                    'SHOP_TAX_PRICE'           => Currency::formatPrice($total_vat_amount),
                    'SHOP_PART_TAX_PROCENTUAL' => $tax_part_percentaged,
                ));
            //} else {
                // No VAT otherwise
                // remove it from the details overview if empty
                //$this->_objTpl->hideBlock('shopTaxPrice');
                //$tax_part_percentaged = $_ARRAYLANG['TXT_NO_TAX'];
            //}

            $this->_objTpl->setVariable(array(
                'SHOP_ROWCLASS_NEW'        => (++$i % 2 ? 'row2' : 'row1'),
                'SHOP_CURRENCY_ORDER_SUM'  => Currency::formatPrice($shopCurrencyOrderSum),
                'SHOP_TOTAL_WEIGHT'        => Weight::getWeightString($total_weight),
                'SHOP_NET_PRICE'           => Currency::formatPrice($total_net_price),
            ));
        }
    }


    /**
     * Store order
     *
     * @global  array   $_ARRAYLANG     Language array
     * @global  mixed   $objDatabase    Database object
     */
    function shopStoreOrderdetails()
    {
        global $objDatabase, $_ARRAYLANG;

        $shopOrderId = intval($_POST['orderid']);

        //begin language variables
        $this->_objTpl->setVariable(array(
            'TXT_ID'   => $_ARRAYLANG['TXT_ID'],
            'TXT_DATE' => $_ARRAYLANG['TXT_DATE'],
            'TXT_NAME' => $_ARRAYLANG['TXT_NAME'],
        ));

        // send an email to the customer
        if (   $_POST['shopOrderStatusId'] == SHOP_ORDER_STATUS_CONFIRMED
            && !empty($_POST['shopSendMail'])) {
            // Determine customer language
            $query = "
                SELECT customer_lang
                  FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders
                 INNER JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_customers
                 USING (customerid)
                 WHERE orderid=$shopOrderId
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult || $objResult->RecordCount() == 0) {
                return false;
            }
            $langId = FWLanguage::getLangIdByIso639_1($objResult->fields['customer_lang']);
            $arrShopMailtemplate = Shop::shopSetMailtemplate(2, $langId);
            $shopMailTo = $_POST['shopMailTo'];
            $shopMailFrom = $arrShopMailtemplate['mail_from'];
            $shopMailFromText = $arrShopMailtemplate['mail_x_sender'];
            $shopMailSubject = $arrShopMailtemplate['mail_subject'];
            $shopMailBody = $arrShopMailtemplate['mail_body'];
            $shopMailSubject = str_replace("<DATE>", $_POST['shopLastModified'], $shopMailSubject);
            $result = Shop::shopSendMail($shopMailTo, $shopMailFrom, $shopMailFromText, $shopMailSubject, $shopMailBody);
            if ($result) {
                $this->addMessage(sprintf($_ARRAYLANG['TXT_EMAIL_SEND_SUCCESSFULLY'], $shopMailTo));
            }
            else {
                $this->addError($_ARRAYLANG['TXT_MESSAGE_SEND_ERROR']);
            }
        }

        // calculate the total order sum in the selected currency of the customer
        $shopTotalOrderSum = floatval($_POST['shopShippingPrice'])
        + floatval($_POST['shopPaymentPrice']);
        // the tax amount will be set, even if it's included in the price already.
        // thus, we have to check the setting.
        if (!$this->objVat->isIncluded()) {
            $shopTotalOrderSum += floatval($_POST['shopTaxPrice']);
        }
        // store the product details and add the price of each product
        // to the total order sum $shopTotalOrderSum
        foreach ($_REQUEST['shopProductId'] AS $elem => $pId) {
            if ($_POST['shopProductList'][$elem] == 0 && stripslashes($elem) != "'new'") {
                // delete the product from the list
                $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items ".
                    "WHERE order_items_id = $elem";
                $objResult = $objDatabase->Execute($query);
                if ($objResult !== false) {
                    $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items_attributes ".
                    "WHERE order_items_id = ".
                    intval(substr(contrexx_stripslashes($elem),1,-1));
                    $objResult = $objDatabase->Execute($query);
                }
            } elseif ($_POST['shopProductList'][$elem] != 0 && stripslashes($elem) == "'new'") {
                // add a new product to the list
                $shopProductPrice = floatval($_REQUEST['shopProductPrice'][$elem]);
                $shopProductQuantity = intval($_REQUEST['shopProductQuantity'][$elem]) < 1 ? 1 : intval($_REQUEST['shopProductQuantity'][$elem]);
                $shopTotalOrderSum += $shopProductPrice * $shopProductQuantity;
                $shopProductTaxPercent = floatval($_REQUEST['shopProductTaxPercent'][$elem]);
                $shopProductWeight = Weight::getWeight($_REQUEST['shopProductWeight'][$elem]);
                $query = "INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_order_items ".
                    "(orderid, productid, product_name, price, quantity, vat_percent, weight) ".
                    "VALUES ($shopOrderId, $pId, '".
                    contrexx_strip_tags($_POST['shopProductName'][$elem]).
                    "', $shopProductPrice, $shopProductQuantity, ".
                    "$shopProductTaxPercent, $shopProductWeight)";
                $objResult = $objDatabase->Execute($query);
            } elseif ($_POST['shopProductList'][$elem] != 0 && stripslashes($elem) != "'new'") {
                // update the products information
                $shopProductPrice = floatval($_REQUEST['shopProductPrice'][$elem]);
                $shopProductQuantity = intval($_REQUEST['shopProductQuantity'][$elem]) < 1 ? 1 : intval($_REQUEST['shopProductQuantity'][$elem]);
                $shopTotalOrderSum += $shopProductPrice * $shopProductQuantity;
                $shopProductTaxPercent = floatval($_REQUEST['shopProductTaxPercent'][$elem]);
                $shopProductWeight = Weight::getWeight($_REQUEST['shopProductWeight'][$elem]);
                $query = "UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_order_items SET ".
                        "price = $shopProductPrice".
                        ", quantity = $shopProductQuantity".
                        ", productid = ".intval($_POST['shopProductList'][$elem]).
                        ", product_name='".contrexx_strip_tags($_POST['shopProductName'][$elem]).
                        "', vat_percent = $shopProductTaxPercent".
                        ", weight = $shopProductWeight".
                    " WHERE order_items_id=$elem";
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
                   modified_by='".addslashes(strip_tags($_SESSION['auth']['username']))."',
                   last_modified=now()
             WHERE orderid = $shopOrderId
        ";
        // should not be changed, see above
        // ", payment_id = ".intval($_POST['paymentId']).
        if ($objDatabase->Execute($query)) {
            $this->addMessage($_ARRAYLANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL']);
            return true;
        }
        // if query has errors, call errorhandling
        $this->errorHandling();
        return false;
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
                $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items_attributes WHERE order_id=".intval($oId);
                if ($objDatabase->Execute($query)) {
                    $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items WHERE orderid=".intval($oId);
                    if ($objDatabase->Execute($query)) {
                        $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_lsv WHERE order_id=".intval($oId);
                        if ($objDatabase->Execute($query)) {
                            $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders WHERE orderid=".intval($oId);
                            if (!$objDatabase->Execute($query)) {
                                $this->errorHandling();
                            }
                        } else {
                            $this->errorHandling();
                        }
                    } else {
                        $this->errorHandling();
                    }
                } else {
                    $this->errorHandling();
                }
            } // foreach
        }
        $this->addMessage($_ARRAYLANG['TXT_ORDER_DELETED']);
        return true;
    }


    /**
     * Show Customers
     */
    function shopShowCustomers()
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        $pos = 0;
        $i   = 0;

        //set template
        $this->_objTpl->loadTemplateFile("module_shop_customers.html", true, true);

        //begin language variables
        $this->_objTpl->setVariable(array(
        'TXT_CUSTOMERS_PARTNERS'     => $_ARRAYLANG['TXT_CUSTOMERS_PARTNERS'],
        'TXT_CUSTOMER_TYP'           => $_ARRAYLANG['TXT_CUSTOMER_TYP'],
        'TXT_CUSTOMER'               => $_ARRAYLANG['TXT_CUSTOMER'],
        'TXT_RESELLER'               => $_ARRAYLANG['TXT_RESELLER'],
        'TXT_INACTIVE'               => $_ARRAYLANG['TXT_INACTIVE'],
        'TXT_ACTIVE'                 => $_ARRAYLANG['TXT_ACTIVE'],
        'TXT_SORT_ORDER'             => $_ARRAYLANG['TXT_SORT_ORDER'],
        'TXT_LAST_NAME'              => $_ARRAYLANG['TXT_LAST_NAME'],
        'TXT_FIRST_NAME'             => $_ARRAYLANG['TXT_FIRST_NAME'],
        'TXT_ID'                     => $_ARRAYLANG['TXT_ID'],
        'TXT_COMPANY'                => $_ARRAYLANG['TXT_COMPANY'],
        'TXT_ACTION'                 => $_ARRAYLANG['TXT_ACTION'],
        'TXT_NAME'                   => $_ARRAYLANG['TXT_NAME'],
        'TXT_ADDRESS'                => $_ARRAYLANG['TXT_ADDRESS'],
        'TXT_ZIP_CITY'               => $_ARRAYLANG['TXT_ZIP_CITY'],
        'TXT_PHONE'                  => $_ARRAYLANG['TXT_PHONE'],
        'TXT_EMAIL'                  => $_ARRAYLANG['TXT_EMAIL'],
        'TXT_SEARCH'                 => $_ARRAYLANG['TXT_SEARCH'],
        'TXT_CONFIRM_DELETE_CUSTOMER'    => $_ARRAYLANG['TXT_CONFIRM_DELETE_CUSTOMER'],
        'TXT_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
        'TXT_ALL_ORDERS_WILL_BE_DELETED' => $_ARRAYLANG['TXT_ALL_ORDERS_WILL_BE_DELETED'],
        'TXT_ADD_NEW_CUSTOMER'       => $_ARRAYLANG['TXT_ADD_NEW_CUSTOMER'],
        'TXT_MARKED'                 => $_ARRAYLANG['TXT_MARKED'],
        'TXT_SELECT_ALL'             => $_ARRAYLANG['TXT_SELECT_ALL'],
        'TXT_REMOVE_SELECTION'         => $_ARRAYLANG['TXT_REMOVE_SELECTION'],
        'TXT_SELECT_ACTION'             => $_ARRAYLANG['TXT_SELECT_ACTION'],
        'TXT_MAKE_SELECTION'         => $_ARRAYLANG['TXT_MAKE_SELECTION']
        ));

        $this->_objTpl->setGlobalVariable(array(
        'TXT_STATUS'                 => $_ARRAYLANG['TXT_STATUS'],
        'TXT_VIEW_DETAILS'           => $_ARRAYLANG['TXT_VIEW_DETAILS'],
        'TXT_EDIT'                   => $_ARRAYLANG['TXT_EDIT'],
        'TXT_DELETE'                 => $_ARRAYLANG['TXT_DELETE'],
        'TXT_SEND_MAIL_TO_ADDRESS'     => $_ARRAYLANG['TXT_SEND_MAIL_TO_ADDRESS']
        ));
        //set search
        $shopSearchPattern = "";
        $shopCustomerOrder = "customerid DESC";
        if (isset($_POST['shopSearchCustomers']) OR isset($_POST['shopListLetter'])) {
            if ($_POST['shopCustomerStatus']<2) {
                $shopCustomerStatus = intval($_POST['shopCustomerStatus']);
                $shopSearchPattern = " AND customer_status = $shopCustomerStatus";

            }
            if ($_POST['shopCustomer']<2) {
                $shopCustomer = intval($_POST['shopCustomer']);
                $shopSearchPattern .= " AND is_reseller = $shopCustomer";
            }
            if ($_POST['shopSearchTerm'] != '') {
                $shopSearchTerm = htmlspecialchars($_POST['shopSearchTerm'], ENT_QUOTES, CONTREXX_CHARSET);
                $shopSearchPattern .=
                    " AND (customerid LIKE '%$shopSearchTerm%'
                        OR company LIKE '%$shopSearchTerm%'
                        OR firstname LIKE '%$shopSearchTerm%'
                        OR lastname LIKE '%$shopSearchTerm%'
                        OR address LIKE '%$shopSearchTerm%'
                        OR city LIKE '%$shopSearchTerm%'
                        OR phone LIKE '%$shopSearchTerm%'
                        OR email LIKE '%$shopSearchTerm%')";
            }
            if ($_POST['shopListLetter'] != '') {
                $shopLetter = htmlspecialchars($_POST['shopListLetter'], ENT_QUOTES, CONTREXX_CHARSET);
                $shopListOrder = addslashes(strip_tags($_POST['shopListSort']));
                $shopSearchPattern .= " AND LEFT($shopListOrder,1) = '$shopLetter' ";
            }
            if (isset($_POST['shopListSort'])) {
                $shopCustomerOrder = addslashes(strip_tags($_POST['shopListSort']))." DESC";
            }
        }
        // create query
        $query = "
            SELECT customerid, company, firstname, lastname,
                   address, city, zip, phone, email, customer_status
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_customers
             WHERE 1 $shopSearchPattern
          ORDER BY $shopCustomerOrder
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            $this->errorHandling();
        } else {
            $pos = isset($_GET['pos']) ? intval($_GET['pos']) : 0;
            $count = $objResult->RecordCount();
            if ($count == 0) {
                $this->_objTpl->hideBlock('shopCustomersOverview');
            }
            $shopPagingLimit = intval($_CONFIG['corePagingLimit']);
            $viewPaging = false; //by default, the paging view is disabled
            if ($count > $shopPagingLimit) { //if count contains more entrys than the paging limit, set paging to true
                $viewPaging = true;
            }
            $paging = getPaging($count, $pos, "&amp;cmd=shop".MODULE_INDEX."&amp;act=customers", "<b>".$_ARRAYLANG['TXT_CUSTOMERS_ENTRIES']."</b>", $viewPaging);
            //$query .= " LIMIT $pos, $shopPagingLimit";
        }
        if (!($objResult = $objDatabase->SelectLimit($query, $shopPagingLimit, $pos))) {
            //if query has errors, call errorhandling
            $this->errorHandling();
        } else {
            $this->_objTpl->setCurrentBlock("customersRow");
            while (!$objResult->EOF) {
                $shopCustomerStatus = "led_red.gif";
                if ($objResult->fields['customer_status'] == 1) {
                    $shopCustomerStatus = "led_green.gif";
                }
                if (($i % 2) == 0) {
                    $class="row1";
                } else {
                    $class="row2";
                }
                $this->_objTpl->setVariable(array(
                'SHOP_ROWCLASS'     => $class,
                'SHOP_CUSTOMERID'   => $objResult->fields['customerid'],
                'SHOP_COMPANY'      => $objResult->fields['company'] == '' ? '&nbsp;' : $objResult->fields['company'],
                'SHOP_NAME'         => $objResult->fields['firstname'].'&nbsp;'.$objResult->fields['lastname'],
                'SHOP_ADDRESS'      => $objResult->fields['address'] == '' ? '&nbsp;' : $objResult->fields['address'],
                'SHOP_ZIP'          => $objResult->fields['zip'],
                'SHOP_CITY'         => $objResult->fields['city'],
                'SHOP_PHONE'        => $objResult->fields['phone'] == '' ? '&nbsp;' : $objResult->fields['phone'],
                'SHOP_EMAIL'        => $objResult->fields['email'] == '' ? '&nbsp;' : $objResult->fields['email'],
                'SHOP_CUSTOMER_STATUS_IMAGE' => $shopCustomerStatus,
                ));
                $this->_objTpl->parse('customersRow');
                $i++;
                $objResult->MoveNext();
            }
            $this->_objTpl->setVariable('SHOP_CUSTOMER_PAGING',$paging);
        }
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
                    $this->addMessage($_ARRAYLANG['TXT_ALL_ORDERS_DELETED']);
                }
                $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_customers
                      WHERE customerid = ".intval($cId);
                if ($objDatabase->Execute($query)) {
                    $this->addMessage($_ARRAYLANG['TXT_CUSTOMER_DELETED']);
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
        //set template
        $this->_objTpl->loadTemplateFile("module_shop_customer_details.html", true, true);
        $arrCurrency = $this->objCurrency->getCurrencyArray();
        //declare var and default value
        $i = 1;
        //begin language variables
        $this->_objTpl->setVariable(array(
            'TXT_CUSTOMER_DETAILS'     => $_ARRAYLANG['TXT_CUSTOMER_DETAILS'],
            'TXT_CUSTOMER_DATA'        => $_ARRAYLANG['TXT_CUSTOMER_DATA'],
            'TXT_COMPANY'              => $_ARRAYLANG['TXT_COMPANY'],
            'TXT_PREFIX'               => $_ARRAYLANG['TXT_PREFIX'],
            'TXT_FIRST_NAME'           => $_ARRAYLANG['TXT_FIRST_NAME'],
            'TXT_LAST_NAME'            => $_ARRAYLANG['TXT_LAST_NAME'],
            'TXT_ADDRESS'              => $_ARRAYLANG['TXT_ADDRESS'],
            'TXT_ZIP_CITY'             => $_ARRAYLANG['TXT_ZIP_CITY'],
            'TXT_PHONE'                => $_ARRAYLANG['TXT_PHONE'],
            'TXT_EMAIL'                => $_ARRAYLANG['TXT_EMAIL'],
            'TXT_CUSTOMER_NUMBER'      => $_ARRAYLANG['TXT_CUSTOMER_NUMBER'],
            'TXT_CUSTOMER_TYP'         => $_ARRAYLANG['TXT_CUSTOMER_TYP'],
            'TXT_LOGIN_NAME'           => $_ARRAYLANG['TXT_LOGIN_NAME'],
            'TXT_REGISTER_DATE'        => $_ARRAYLANG['TXT_REGISTER_DATE'],
            'TXT_CUSTOMER_STATUS'      => $_ARRAYLANG['TXT_CUSTOMER_STATUS'],
            'TXT_COUNTRY'              => $_ARRAYLANG['TXT_COUNTRY'],
            'TXT_FAX'                  => $_ARRAYLANG['TXT_FAX'],
            'TXT_PAYMENT_INFORMATIONS' => $_ARRAYLANG['TXT_PAYMENT_INFORMATIONS'],
            'TXT_CREDIT_CARD_OWNER'    => $_ARRAYLANG['TXT_CREDIT_CARD_OWNER'],
            'TXT_CARD_NUMBER'          => $_ARRAYLANG['TXT_CARD_NUMBER'],
            'TXT_CVC_CODE'             => $_ARRAYLANG['TXT_CVC_CODE'],
            'TXT_EXPIRY_DATE'          => $_ARRAYLANG['TXT_EXPIRY_DATE'],
            'TXT_ORDERS'               => $_ARRAYLANG['TXT_ORDERS'],
            'TXT_ORDERNUMBER'          => $_ARRAYLANG['TXT_ORDERNUMBER'],
            'TXT_ORDERSTATUS'          => $_ARRAYLANG['TXT_ORDERSTATUS'],
            'TXT_DATE'                 => $_ARRAYLANG['TXT_DATE'],
            'TXT_CUSTOMER_STATUS'      => $_ARRAYLANG['TXT_CUSTOMER_STATUS'],
            'TXT_ORDER_SUM'            => $_ARRAYLANG['TXT_ORDER_SUM'],
            'TXT_MORE_INFORMATIONS'    => $_ARRAYLANG['TXT_MORE_INFORMATIONS'],
            'TXT_REMARK'               => $_ARRAYLANG['TXT_REMARK'],
            'TXT_EDIT_CUSTOMER'        => $_ARRAYLANG['TXT_EDIT_CUSTOMER'],
            'TXT_SEND_MAIL_TO_ADDRESS' => $_ARRAYLANG['TXT_SEND_MAIL_TO_ADDRESS']
        ));

        $this->_objTpl->setGlobalVariable(array(
            'TXT_VIEW_DETAILS'         => $_ARRAYLANG['TXT_VIEW_DETAILS']
        ));

        //get id
        $customerid = intval($_REQUEST['customerid']);
        //Check if the data must be stored
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
                                      register_date='".$shopRegisterDate."'
                              WHERE customerid=".$customerid;

                    if (!$objDatabase->Execute($query)) {
                        //if query has errors, call errorhandling
                        $this->errorHandling();
                    } else {
                        $this->addMessage($_ARRAYLANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL']);
                    }
                    //check if the logindata must be sent
                    if (isset($_POST['shopSendLoginData'])) {
                        // Determine customer language
                        $query = "
                            SELECT customer_lang
                              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_customers
                             WHERE customerid=$customerid
                        ";
                        $objResult = $objDatabase->Execute($query);
                        if (!$objResult || $objResult->RecordCount() == 0) {
                            return false;
                        }
                        $langId = FWLanguage::getLangIdByIso639_1($objResult->fields['customer_lang']);
                        // Select template for sending login data
                        $arrShopMailtemplate = Shop::shopSetMailtemplate(3, $langId);
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
                        $result = Shop::shopSendMail($shopMailTo, $shopMailFrom, $shopMailFromText, $shopMailSubject, $shopMailBody);
                        if ($result) {
                            $this->addMessage(sprintf($_ARRAYLANG['TXT_EMAIL_SEND_SUCCESSFULLY'], $shopMailTo));
                        } else {
                            $this->addError($_ARRAYLANG['TXT_MESSAGE_SEND_ERROR']);
                            return false;
                        }
                    }
                } else {
                    $this->addError($_ARRAYLANG['TXT_USERNAME_USED_BY_OTHER_CUSTOMER']);
                }
            } else {
                $this->addError($_ARRAYLANG['TXT_EMAIL_USED_BY_OTHER_CUSTOMER']);
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
                $this->_objTpl->setVariable(array(
                    'SHOP_CUSTOMERID'       => $objResult->fields['customerid'],
                    'SHOP_PREFIX'           => $objResult->fields['prefix'] == "" ? "&nbsp;" : $objResult->fields['prefix'],
                    'SHOP_LASTNAME'         => $objResult->fields['lastname'] == "" ? "&nbsp;" : $objResult->fields['lastname'],
                    'SHOP_FIRSTNAME'        => $objResult->fields['firstname'] == "" ? "&nbsp;" : $objResult->fields['firstname'],
                    'SHOP_COMPANY'          => $objResult->fields['company'] == "" ? "&nbsp;" : $objResult->fields['company'],
                    'SHOP_ADDRESS'          => $objResult->fields['address'] == "" ? "&nbsp;" : $objResult->fields['address'],
                    'SHOP_CITY'             => $objResult->fields['city'] == "" ? "&nbsp;" : $objResult->fields['city'],
                    'SHOP_USERNAME'         => $objResult->fields['username'] == "" ? "&nbsp;" : $objResult->fields['username'],
                    // unavailable
                    //'SHOP_ORDER_STATUS'     => $objResult->fields['order_status'],
                    'SHOP_COUNTRY'          => $this->arrCountries[$objResult->fields['country_id']]['countries_name'],
                    'SHOP_ZIP'              => $objResult->fields['zip'] == "" ? "&nbsp;" : $objResult->fields['zip'],
                    'SHOP_PHONE'            => $objResult->fields['phone'] == "" ? "&nbsp;" : $objResult->fields['phone'],
                    'SHOP_FAX'              => $objResult->fields['fax'] == "" ? "&nbsp;" : $objResult->fields['fax'],
                    'SHOP_EMAIL'            => $objResult->fields['email'] == "" ? "&nbsp;" : $objResult->fields['email'],
                    // unavailable
                    //'SHOP_PAYMENTTYPE'      => $objResult->fields['paymenttyp'],
                    'SHOP_CCNUMBER'         => $objResult->fields['ccnumber'] == "" ? "&nbsp;" : $objResult->fields['ccnumber'],
                    'SHOP_CCDATE'           => $objResult->fields['ccdate'] == "" ? "&nbsp;" : $objResult->fields['ccdate'],
                    'SHOP_CCNAME'           => $objResult->fields['ccname'] == "" ? "&nbsp;" : $objResult->fields['ccname'],
                    'SHOP_CVC_CODE'         => $objResult->fields['cvc_code'] == "" ? "&nbsp;" : $objResult->fields['cvc_code'],
                    'SHOP_COMPANY_NOTE'     => $objResult->fields['company_note'] == "" ? "-" : $objResult->fields['company_note'],
                    'SHOP_IS_RESELLER'      => $customerType,
                    'SHOP_REGISTER_DATE'    => $objResult->fields['register_date'],
                    'SHOP_CUSTOMER_STATUS'  => $customerStatus,
                ));
                $objResult->MoveNext();
            }//end if
        }//end else
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
            $this->_objTpl->setCurrentBlock("orderRow");
            while (!$objResult->EOF) {
                if (($i % 2) == 0) {
                    $class="row1";
                } else {
                    $class="row2";
                }
                // set currency id
                $this->objCurrency->activeCurrencyId = $objResult->fields['selected_currency_id'];
                //set edit fields
                $this->_objTpl->setVariable(array(
                    'SHOP_ROWCLASS'     => $class,
                    'SHOP_ORDER_ID'     => $objResult->fields['orderid'],
                    'SHOP_ORDER_DATE'   => $objResult->fields['order_date'],
                    'SHOP_ORDER_STATUS' => $this->arrOrderStatus[intval($objResult->fields['order_status'])],
                    'SHOP_ORDER_SUM'    => $this->objCurrency->getDefaultCurrencyPrice($objResult->fields['currency_order_sum'])." ".$arrCurrency[$this->objCurrency->defaultCurrencyId]['symbol'],
                ));
                $this->_objTpl->parse("orderRow");
                $i++;
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
        $this->_objTpl->loadTemplateFile("module_shop_edit_customer.html", true, true);

        //Check if the data must be stored
        if (isset($_POST['shopStore'])) {
            $this->shopAddCustomer();
        }

        //begin language variables
        $this->_objTpl->setVariable(array(
            'TXT_CUSTOMER_DATA'        => $_ARRAYLANG['TXT_CUSTOMER_DATA'],
            'TXT_CUSTOMER_NUMBER'      => $_ARRAYLANG['TXT_CUSTOMER_NUMBER'],
            'TXT_COMPANY'              => $_ARRAYLANG['TXT_COMPANY'],
            'TXT_PREFIX'               => $_ARRAYLANG['TXT_PREFIX'],
            'TXT_FIRST_NAME'           => $_ARRAYLANG['TXT_FIRST_NAME'],
            'TXT_LAST_NAME'            => $_ARRAYLANG['TXT_LAST_NAME'],
            'TXT_ADDRESS'              => $_ARRAYLANG['TXT_ADDRESS'],
            'TXT_ZIP_CITY'             => $_ARRAYLANG['TXT_ZIP_CITY'],
            'TXT_PHONE'                => $_ARRAYLANG['TXT_PHONE'],
            'TXT_EMAIL'                => $_ARRAYLANG['TXT_EMAIL'],
            'TXT_CUSTOMER_TYP'         => $_ARRAYLANG['TXT_CUSTOMER_TYP'],
            'TXT_CUSTOMER'             => $_ARRAYLANG['TXT_CUSTOMER'],
            'TXT_RESELLER'             => $_ARRAYLANG['TXT_RESELLER'],
            'TXT_LOGIN_NAME'           => $_ARRAYLANG['TXT_LOGIN_NAME'],
            'TXT_RESET_PASSWORD'       => $_ARRAYLANG['TXT_RESET_PASSWORD'],
            'TXT_REGISTER_DATE'        => $_ARRAYLANG['TXT_REGISTER_DATE'],
            'TXT_CUSTOMER_STATUS'      => $_ARRAYLANG['TXT_CUSTOMER_STATUS'],
            'TXT_INACTIVE'             => $_ARRAYLANG['TXT_INACTIVE'],
            'TXT_ACTIVE'               => $_ARRAYLANG['TXT_ACTIVE'],
            'TXT_COUNTRY'              => $_ARRAYLANG['TXT_COUNTRY'],
            'TXT_FAX'                  => $_ARRAYLANG['TXT_FAX'],
            'TXT_PAYMENT_INFORMATIONS' => $_ARRAYLANG['TXT_PAYMENT_INFORMATIONS'],
            'TXT_CREDIT_CARD_OWNER'    => $_ARRAYLANG['TXT_CREDIT_CARD_OWNER'],
            'TXT_CARD_NUMBER'          => $_ARRAYLANG['TXT_CARD_NUMBER'],
            'TXT_CVC_CODE'             => $_ARRAYLANG['TXT_CVC_CODE'],
            'TXT_EXPIRY_DATE'          => $_ARRAYLANG['TXT_EXPIRY_DATE'],
            'TXT_OPTIONS'              => $_ARRAYLANG['TXT_OPTIONS'],
            'TXT_ORDERNUMBER'          => $_ARRAYLANG['TXT_ORDERNUMBER'],
            'TXT_REMARK'               => $_ARRAYLANG['TXT_REMARK'],
            'TXT_SAVE_CHANGES'         => $_ARRAYLANG['TXT_SAVE_CHANGES'],
            'TXT_SEND_LOGIN_DATA'      => $_ARRAYLANG['TXT_SEND_LOGIN_DATA'],
        ));
        //set requested customerid
        $customerid = (isset($_REQUEST['customerid']) ? intval($_REQUEST['customerid']) : 0);
        if ($customerid == 0) { //create a new customer
            $this->pageTitle = $_ARRAYLANG['TXT_ADD_NEW_CUSTOMER'];
            $this->_objTpl->setVariable(array(
            'SHOP_CUSTOMERID'              => "&nbsp;",
            'SHOP_SEND_LOGING_DATA_STATUS' => "checked=\"checked\"",
            'SHOP_REGISTER_DATE'           => date("Y-m-d h:m:s"),
            'SHOP_COUNTRY'                 => $this->_getCountriesMenu("shopCountry"),
            'SHOP_CUSTOMER_ACT'            => "neweditcustomer"
            ));
        } else {    //edit user
            $this->pageTitle = $_ARRAYLANG['TXT_EDIT_CUSTOMER'];
            $this->_objTpl->setVariable(array(
            'SHOP_SEND_LOGING_DATA_STATUS' => "",
            'SHOP_CUSTOMER_ACT'            => "customerdetails&amp;customerid={SHOP_CUSTOMERID}"
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
                        $this->_objTpl->setVariable("SHOP_IS_RESELLER","selected");
                        $this->_objTpl->setVariable("SHOP_IS_CUSTOMER","");
                    } else {
                        $this->_objTpl->setVariable("SHOP_IS_RESELLER","");
                        $this->_objTpl->setVariable("SHOP_IS_CUSTOMER","selected");
                    }
                    if ($objResult->fields['customer_status'] == 1) {
                        $this->_objTpl->setVariable("SHOP_CUSTOMER_STATUS_0","");
                        $this->_objTpl->setVariable("SHOP_CUSTOMER_STATUS_1","selected");
                    } else {
                        $this->_objTpl->setVariable("SHOP_CUSTOMER_STATUS_0","selected");
                        $this->_objTpl->setVariable("SHOP_CUSTOMER_STATUS_1","");
                    }
                    //set edit fields
                    $this->_objTpl->setVariable(array(
                        'SHOP_CUSTOMERID'       => $objResult->fields['customerid'],
                        'SHOP_PREFIX'           => $objResult->fields['prefix'] == "" ? "&nbsp;" : $objResult->fields['prefix'],
                        'SHOP_LASTNAME'         => $objResult->fields['lastname'] == "" ? "&nbsp;" : $objResult->fields['lastname'],
                        'SHOP_FIRSTNAME'        => $objResult->fields['firstname'] == "" ? "&nbsp;" : $objResult->fields['firstname'],
                        'SHOP_COMPANY'          => $objResult->fields['company'] == "" ? "&nbsp;" : $objResult->fields['company'],
                        'SHOP_ADDRESS'          => $objResult->fields['address'] == "" ? "&nbsp;" : $objResult->fields['address'],
                        'SHOP_CITY'             => $objResult->fields['city'] == "" ? "&nbsp;" : $objResult->fields['city'],
                        'SHOP_USERNAME'         => $objResult->fields['username'] == "" ? "&nbsp;" : $objResult->fields['username'],
                        // unavailable
                        //'SHOP_ORDER_STATUS'     => $objResult->fields['order_status'],
                        'SHOP_COUNTRY'          => $this->_getCountriesMenu("shopCountry", $objResult->fields['country_id']),
                        'SHOP_ZIP'              => $objResult->fields['zip'] == "" ? "&nbsp;" : $objResult->fields['zip'],
                        'SHOP_PHONE'            => $objResult->fields['phone'] == "" ? "&nbsp;" : $objResult->fields['phone'],
                        'SHOP_FAX'              => $objResult->fields['fax'] == "" ? "&nbsp;" : $objResult->fields['fax'],
                        'SHOP_EMAIL'            => $objResult->fields['email'] == "" ? "&nbsp;" : $objResult->fields['email'],
                        // unavailable
                        //'SHOP_PAYMENTTYPE'      => $objResult->fields['paymenttyp'],
                        'SHOP_CCNUMBER'         => $objResult->fields['ccnumber'] == "" ? "&nbsp;" : $objResult->fields['ccnumber'],
                        'SHOP_CCDATE'           => $objResult->fields['ccdate'] == "" ? "&nbsp;" : $objResult->fields['ccdate'],
                        'SHOP_CCNAME'           => $objResult->fields['ccname'] == "" ? "&nbsp;" : $objResult->fields['ccname'],
                        'SHOP_CVC_CODE'         => $objResult->fields['cvc_code'] == "" ? "&nbsp;" : $objResult->fields['cvc_code'],
                        'SHOP_COMPANY_NOTE'     => $objResult->fields['company_note'] == "" ? "&nbsp;" : $objResult->fields['company_note'],
                        'SHOP_REGISTER_DATE'    => $objResult->fields['register_date'],
                    ));
                }
            }
        }
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

                // insert the customer data
                $query = "
                    INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_customers
                        (username, password, prefix, company, firstname,
                        lastname, address, city, zip, country_id,
                        phone, fax, email,
                        ccnumber, ccdate, ccname, cvc_code,
                        company_note, customer_status,
                        is_reseller, register_date)
                    VALUES
                        ('$shopUsername', '$shopMd5Password', '$shopPrefix',
                        '$shopCompany', '$shopFirstname', '$shopLastname',
                        '$shopAddress', '$shopCity', '$shopZip',
                        '$shopCountry', '$shopPhone', '$shopFax',
                        '$shopEmail', '$shopCcnumber', '$shopCcdate',
                        '$shopCcname', '$shopCvcCode', '$shopCompanyNote',
                        $shopCustomerStatus, $shopIsReseller, '$shopRegisterDate')
                ";
                if (!$objDatabase->Execute($query)) {
                    // if query has errors, call errorhandling
                    $this->errorHandling();
                } else {
                    $customerid = $objDatabase->Insert_ID();
                    $this->addMessage($_ARRAYLANG['TXT_SHOP_INSERTED_CUSTOMER'].", ID $customerid");
                }
                //check if the logindata must be sent
                if (isset($_POST['shopSendLoginData'])) {
                    // Select template for sending login data
                    $arrShopMailtemplate = Shop::shopSetMailtemplate(3, $_FRONTEND_LANGID);
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
                    $result = Shop::shopSendMail($shopMailTo, $shopMailFrom, $shopMailFromText, $shopMailSubject, $shopMailBody);
                    if ($result) {
                        $this->addMessage(sprintf($_ARRAYLANG['TXT_EMAIL_SEND_SUCCESSFULLY'], $shopMailTo));
                    } else {
                        $this->addError($_ARRAYLANG['TXT_MESSAGE_SEND_ERROR']);
                        return false;
                    }
                }
            } else {
                $this->addError($_ARRAYLANG['TXT_USERNAME_USED_BY_OTHER_CUSTOMER']);
                return false;
            }
        } else {
            $this->addError($_ARRAYLANG['TXT_EMAIL_USED_BY_OTHER_CUSTOMER']);
            return false;
        }
        return true;
    }


    /**
     * Get dropdown menue
     *
     * Gets backe a dropdown menu like  <option value='catid'>Catname</option>
     *
     * @param    integer  $selectedid
     * @return   string   $result
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
     * @todo    Optional argument $parcat *MUST NOT* be the first argument!
     */
    function doShopCatMenu($parcat=0, $level, $selectedid)
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
        $this->_objTpl->loadTemplateFile('module_shop_products.html',true,true);
        $this->_objTpl->setGlobalVariable(array(
            'TXT_ADD_PRODUCTS'            => $_ARRAYLANG['TXT_ADD_PRODUCTS'],
            'TXT_PRODUCT_CATALOG'         => $_ARRAYLANG['TXT_PRODUCT_CATALOG'],
            'TXT_PRODUCT_CHARACTERISTICS' => $_ARRAYLANG['TXT_PRODUCT_CHARACTERISTICS'],
            'TXT_DOWNLOAD_OPTIONS'        => $_ARRAYLANG['TXT_DOWNLOAD_OPTIONS']
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
                $this->pageTitle = $_ARRAYLANG['TXT_ADD_PRODUCTS'];
                $this->manageProduct();
                break;
            default:
                // Alternative: $this->pageTitle = $_ARRAYLANG['TXT_PRODUCT_CATALOG'];
                $this->pageTitle = $_ARRAYLANG['TXT_PRODUCT_CHARACTERISTICS'];
                $this->showProducts();
                break;
        }
        $this->_objTpl->parse('shop_products_block');
    }


    /**
     * Show Products
     *
     */
    function showProducts()
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        // Store changed values
        if (isset($_REQUEST['shopSaveProductAttributes'])) {
            $this->storeProducts();
        }

        $this->_objTpl->addBlockfile(
            'SHOP_PRODUCTS_FILE',
            'shop_products_block',
            'module_shop_product_catalog.html'
        );
        $this->_objTpl->setGlobalVariable(array(
            'TXT_CONFIRM_DELETE_PRODUCT' => $_ARRAYLANG['TXT_CONFIRM_DELETE_PRODUCT'],
            'TXT_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
            'TXT_VIEW_SPECIAL_OFFERS'    => $_ARRAYLANG['TXT_VIEW_SPECIAL_OFFERS'],
            'TXT_SEARCH'                 => $_ARRAYLANG['TXT_SEARCH'],
            'TXT_TOTAL'                  => $_ARRAYLANG['TXT_TOTAL'],
            'TXT_ID'                     => $_ARRAYLANG['TXT_ID'],
            'TXT_PRODUCT_NAME'           => $_ARRAYLANG['TXT_PRODUCT_NAME'],
            'TXT_SEQUENCE'               => $_ARRAYLANG['TXT_SEQUENCE'],
            'TXT_SHORT_DESCRIPTION'      => $_ARRAYLANG['TXT_SHORT_DESCRIPTION'],
            'TXT_SPECIAL_OFFER'          => $_ARRAYLANG['TXT_SPECIAL_OFFER'],
            'TXT_HP'                     => $_ARRAYLANG['TXT_HP'],
            'TXT_EKP'                    => $_ARRAYLANG['TXT_EKP'],
            'TXT_TAX'                    => $_ARRAYLANG['TXT_TAX'],
            'TXT_WEIGHT'                 => $_ARRAYLANG['TXT_WEIGHT'],
            'TXT_DISTRIBUTION'           => $_ARRAYLANG['TXT_DISTRIBUTION'],
            'TXT_STATUS'                 => $_ARRAYLANG['TXT_STATUS'],
            'TXT_ACTION'                 => $_ARRAYLANG['TXT_ACTION'],
            'TXT_STOCK'                  => $_ARRAYLANG['TXT_STOCK'],
            'TXT_SHOP_PRODUCT_CUSTOM_ID' => $_ARRAYLANG['TXT_SHOP_PRODUCT_CUSTOM_ID'],
            'TXT_NAME'                   => $_ARRAYLANG['TXT_NAME'],
            'TXT_ACCEPT_CHANGES'         => $_ARRAYLANG['TXT_ACCEPT_CHANGES'],
            'TXT_ALL_PRODUCT_GROUPS'     => $_ARRAYLANG['TXT_ALL_PRODUCT_GROUPS'],
            'TXT_EDIT'                   => $_ARRAYLANG['TXT_EDIT'],
            'TXT_AS_TEMPLATE'            => $_ARRAYLANG['TXT_AS_TEMPLATE'],
            'TXT_DELETE'                 => $_ARRAYLANG['TXT_DELETE'],
            'TXT_PREVIEW'                => $_ARRAYLANG['TXT_PREVIEW'],
            'TXT_PRODUCT_CATALOG'        => $_ARRAYLANG['TXT_PRODUCT_CATALOG'],
            'TXT_MARKED'                 => $_ARRAYLANG['TXT_MARKED'],
            'TXT_SELECT_ALL'             => $_ARRAYLANG['TXT_SELECT_ALL'],
            'TXT_REMOVE_SELECTION'       => $_ARRAYLANG['TXT_REMOVE_SELECTION'],
            'TXT_SELECT_ACTION'          => $_ARRAYLANG['TXT_SELECT_ACTION'],
            'TXT_MAKE_SELECTION'         => $_ARRAYLANG['TXT_MAKE_SELECTION']
        ));

        $catId = 0;
        $manufacturerId = 0;
        $flagSpecialoffer = false;
        $searchTerm = '';
        if (isset($_REQUEST['catId'])) {
            $catId = intval($_REQUEST['catId']);
        }
        if (isset($_REQUEST['manufacturerId'])) {
            $manufacturerId = intval($_REQUEST['manufacturerId']);
        }
        if (isset($_REQUEST['specialoffer'])) {
            $flagSpecialoffer = true;
        }
        if (!empty($_REQUEST['shopSearchTerm'])) {
            $searchTerm = mysql_escape_string(
                trim(stripslashes($_REQUEST['shopSearchTerm']))
            );
        }
        $pos   = isset($_GET['pos']) ? intval($_GET['pos']) : 0;
        $count = 0;

        $arrProducts = $this->objProducts->getByShopParams(
            // Mind that $count is handed over by reference.
            $count, true, $flagSpecialoffer, false, 0,
            $catId, $manufacturerId, $searchTerm, $pos
        );
        $shopPagingLimit = intval($_CONFIG['corePagingLimit']);
        // Show paging if the Product count is greater than the page limit
        if ($count > $shopPagingLimit) {
            $this->_objTpl->setVariable(
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
        $this->_objTpl->setVariable(array(
            'SHOP_CAT_MENU' =>
                $this->objShopCategories->getShopCategoriesMenu($catId, false),
            'SHOP_SEARCH_TERM' => $searchTerm,
            'SHOP_PRODUCT_TOTAL' => $count,
        ));

        $i = 0;
        $this->_objTpl->setCurrentBlock('productRow');
        foreach ($arrProducts as $objProduct) {
            $productStatus = '';
            $productStatusValue = '';
            $productStatusPicture = 'status_red.gif';
            if ($objProduct->getStatus()) {
                $productStatus = 'checked="checked"';
                $productStatusValue = 1;
                $productStatusPicture = 'status_green.gif';
            }
            $specialOffer = '';
            $specialOfferValue = '';
            if ($objProduct->isSpecialoffer()) {
                $specialOffer = 'checked="checked"';
                $specialOfferValue = 1;
            }

            $this->_objTpl->setVariable(array(
                'SHOP_ROWCLASS'                => (++$i % 2 ? 'row1' : 'row2'),
                'SHOP_PRODUCT_ID'              => $objProduct->getId(),
                'SHOP_PRODUCT_CUSTOM_ID'       => $objProduct->getCode(),
                'SHOP_PRODUCT_NAME'            => $objProduct->getName(),
                'SHOP_PRODUCT_PRICE1'          =>
                    Currency::formatPrice($objProduct->getPrice()),
                'SHOP_PRODUCT_PRICE2'          =>
                    Currency::formatPrice($objProduct->getResellerprice()),
                'SHOP_PRODUCT_DISCOUNT'        =>
                    Currency::formatPrice($objProduct->getDiscountprice()),
                'SHOP_PRODUCT_SPECIAL_OFFER'   => $specialOffer,
                'SHOP_SPECIAL_OFFER_VALUE_OLD' => $specialOfferValue,
                'SHOP_PRODUCT_TAX_MENU'        =>
                    $this->objVat->getShortMenuString(
                        $objProduct->getVatId(),
                        'taxId['.$objProduct->getId().']'
                    ),
                'SHOP_PRODUCT_TAX_ID'          =>
                    ($objProduct->getVatId()
                        ? $objProduct->getVatId() : 'NULL'
                    ),
                'SHOP_PRODUCT_WEIGHT'          => Weight::getWeightString($objProduct->getWeight()),
                'SHOP_DISTRIBUTION_MENU'       =>
                    $this->objDistribution->getDistributionMenu(
                        $objProduct->getDistribution(),
                        "distribution[".$objProduct->getId()."]"),
                'SHOP_PRODUCT_DISTRIBUTION'    => $objProduct->getDistribution(),
                'SHOP_PRODUCT_STOCK'           => $objProduct->getStock(),
                'SHOP_PRODUCT_SHORT_DESC'      => $objProduct->getShortdesc(),
                'SHOP_PRODUCT_STATUS'          => $productStatus,
                'SHOP_PRODUCT_STATUS_PICTURE'  => $productStatusPicture,
                'SHOP_ACTIVE_VALUE_OLD'        => $productStatusValue,
                'SHOP_SORT_ORDER'              => $objProduct->getOrder(),
            ));
            $this->_objTpl->parse('productRow');
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
                        $this->addError($_ARRAYLANG['TXT_WEIGHT_INVALID_IGNORED']);
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
                || $shopDistribution != $shopDistributionOld
                // Weight, see above
                || $updateProduct
            ) {

                $arrProducts =
                    ($shopProductIdentifierOld != ''
                        ? $this->objProducts->getByCustomId($shopProductIdentifierOld)
                        : array(Product::getById($id))
                );
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
                    $objProduct->setDistribution($shopDistribution);
                    $objProduct->setWeight($shopWeight);
                    if (!$objProduct->store()) {
                        $arrError[$shopProductIdentifier] = true;
                    }
                }
            }
        }
        if (empty($arrError)) {
            $this->addMessage($_ARRAYLANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL']);
            return true;
        }
        $this->addError($_ARRAYLANG['TXT_SHOP_ERROR_UPDATING_RECORD']);
        return false;
    }


    /**
     * Get some statistical stuff
     *
     * @global    array      $_ARRAYLANG
     */
    function shopOrderStatistics()
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

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

        $this->_objTpl->loadTemplateFile("module_shop_statistic.html", true, true);

        //set general language variables
        $this->_objTpl->setVariable(array(
        'TXT_TOTAL_TURNOVER'      => $_ARRAYLANG['TXT_TOTAL_TURNOVER'],
        'TXT_OVERVIEW'            => $_ARRAYLANG['TXT_OVERVIEW'],
        'TXT_BEST_MONTH'          => $_ARRAYLANG['TXT_BEST_MONTH'],
        'TXT_TURNOVER'            => $_ARRAYLANG['TXT_TURNOVER'],
        'TXT_TOTAL_ORDERS'        => $_ARRAYLANG['TXT_TOTAL_ORDERS'],
        'TXT_TOTAL_SOLD_ARITCLES' => $_ARRAYLANG['TXT_TOTAL_SOLD_ARITCLES'],
        'TXT_SELECT_STATISTIC'    => $_ARRAYLANG['TXT_SELECT_STATISTIC'],
        'TXT_FROM'                => $_ARRAYLANG['TXT_FROM'],
        'TXT_TO'                  => $_ARRAYLANG['TXT_TO'],
        'TXT_ORDERS'              => $_ARRAYLANG['TXT_ORDERS'],
        'TXT_COUNT_ARTICLES'      => $_ARRAYLANG['TXT_COUNT_ARTICLES'],
        'TXT_CUSTOMERS_PARTNERS'  => $_ARRAYLANG['TXT_CUSTOMERS_PARTNERS'],
        'TXT_PERIOD'              => $_ARRAYLANG['TXT_PERIOD'],
        'TXT_PERFORM'             => $_ARRAYLANG['TXT_PERFORM'],
        'TXT_ORDER_SUM'           => $_ARRAYLANG['TXT_ORDER_SUM'],
        'TXT_SUM'                 => $_ARRAYLANG['TXT_SUM'],
        ));
        // Get the first order date, if its empty, no order has been made yet!
        $query = "SELECT DATE_FORMAT(order_date,'%Y') AS year, DATE_FORMAT(order_date,'%m') AS month
                  FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders
                  WHERE order_status = '1' OR order_status = '4'
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
        if ($shopOrders) { //some orders has been made
            //query to get the ordersum, total orders, best month
            $query = "SELECT selected_currency_id,
                             currency_order_sum,
                             DATE_FORMAT(order_date,'%m') AS month,
                             DATE_FORMAT(order_date,'%Y') AS year
                        FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders
                       WHERE order_status = '1' OR order_status = '4'
                       ORDER BY order_date DESC";
            if (($objResult = $objDatabase->Execute($query)) !== false) {
                while (!$objResult->EOF) {
                    // set currency id
                    $this->objCurrency->activeCurrencyId = $objResult->fields['selected_currency_id'];

                    $orderSum = $this->objCurrency->getDefaultCurrencyPrice($objResult->fields['currency_order_sum']);

                    if (!isset($arrShopMonthSum[$objResult->fields['year']][$objResult->fields['month']])) {
                        $arrShopMonthSum[$objResult->fields['year']][$objResult->fields['month']] = 0;
                    }
                    $arrShopMonthSum[$objResult->fields['year']][$objResult->fields['month']] += $orderSum;
                    $shopTotalOrderSum += $orderSum;
                    $shopTotalOrders++;
                    $objResult->MoveNext();
                }

                $months = explode(",", $_ARRAYLANG['TXT_MONTH_ARRAY']);

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
            $query = "SELECT sum(A.quantity) AS shopTotalSoldProducts
                      FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items AS A,
                             ".DBPREFIX."module_shop".MODULE_INDEX."_orders AS B
                      WHERE A.orderid = B.orderid
                      AND (B.order_status = '1' OR B.order_status = '4')";
            $objResult = $objDatabase->SelectLimit($query, 1);
            if ($objResult) {
                if (!$objResult->EOF) {
                    $shopTotalSoldProducts = $objResult->fields['shopTotalSoldProducts'];
                    $objResult->MoveNext();
                }
            }

            //if an timeperiod is set, set the start and stop date
            if (isset($_REQUEST['shopSubmitDate'])) {
                $this->_objTpl->setVariable('SHOP_START_MONTH',$this->shop_getMonthDropdwonMenu(intval($_REQUEST['shopStartMonth'])));
                $this->_objTpl->setVariable('SHOP_END_MONTH',$this->shop_getMonthDropdwonMenu(intval($_REQUEST['shopStopMonth'])));
                $this->_objTpl->setVariable('SHOP_START_YEAR',$this->shop_getYearDropdwonMenu($shopOrderStartyear,intval($_REQUEST['shopStartYear'])));
                $this->_objTpl->setVariable('SHOP_END_YEAR',$this->shop_getYearDropdwonMenu($shopOrderStartyear,intval($_REQUEST['shopStopYear'])));
                $shopStartDate = intval($_REQUEST['shopStartYear'])."-".sprintf("%02s",intval($_REQUEST['shopStartMonth']))."-01 00:00:00";
                $shopStopDate = intval($_REQUEST['shopStopYear'])."-".sprintf("%02s",intval($_REQUEST['shopStopMonth']))."-".date('t',mktime(0,0,0,intval($_REQUEST['shopStopMonth']),1,intval($_REQUEST['shopStopYear'])))." 23:59:59";
            } else {   //set timeperiod to max. one year
                $shopLastYear = Date("Y");
                if ($shopOrderStartyear < Date("Y")) {
                    $shopOrderStartmonth  = Date("m");
                    $shopLastYear = Date("Y")-1;
                }
                $shopEndMonth = Date("m");
                $this->_objTpl->setVariable("SHOP_START_MONTH", $this->shop_getMonthDropdwonMenu($shopOrderStartmonth));
                $this->_objTpl->setVariable("SHOP_END_MONTH", $this->shop_getMonthDropdwonMenu($shopEndMonth));
                $this->_objTpl->setVariable("SHOP_START_YEAR", $this->shop_getYearDropdwonMenu($shopOrderStartyear, $shopLastYear));
                $this->_objTpl->setVariable("SHOP_END_YEAR", $this->shop_getYearDropdwonMenu($shopOrderStartyear,Date("Y")));
                $shopStartDate = $shopLastYear."-".$shopOrderStartmonth."-01 00:00:00";
                $shopStopDate = date("Y")."-".$shopEndMonth."-".date('t',mktime(0,0,0, $shopEndMonth,1,date("Y")))." 23:59:59";
            }
            //check if an statistic has been requested
            $shopSelectedStat = intval($_REQUEST['shopSelectStats']);
            if ($shopSelectedStat ==2) {
                //query for articles stats
                $this->_objTpl->setVariable(array(
                'TXT_COLUMN_1_DESC'       => $_ARRAYLANG['TXT_PRODUCT_NAME'],
                'TXT_COLUMN_2_DESC'       => $_ARRAYLANG['TXT_COUNT_ARTICLES'],
                'TXT_COLUMN_3_DESC'       => $_ARRAYLANG['TXT_STOCK'],
                'SHOP_ORDERS_SELECTED'    => "",
                'SHOP_ARTICLES_SELECTED'  => "selected=\"selected\"",
                'SHOP_CUSTOMERS_SELECTED' => "",
                ));
                $query =  "SELECT A.quantity AS shopColumn2, A.productid AS id, A.price AS sum, B.title AS title, B.stock AS shopColumn3, C.selected_currency_id
                          FROM  ".DBPREFIX."module_shop".MODULE_INDEX."_order_items AS A,
                                ".DBPREFIX."module_shop".MODULE_INDEX."_products AS B,
                                ".DBPREFIX."module_shop".MODULE_INDEX."_orders AS C
                          WHERE A.productid = B.id AND A.orderid = C.orderid
                          AND C.order_date >= '$shopStartDate'
                          AND C.order_date <= '$shopStopDate'
                          AND (C.order_status = '1' OR C.order_status = '4')
                          ORDER BY shopColumn2 DESC";
            } elseif ( $shopSelectedStat ==3) {
                //query for customers stats
                $this->_objTpl->setVariable(array(
                'TXT_COLUMN_1_DESC'       => $_ARRAYLANG['TXT_NAME'],
                'TXT_COLUMN_2_DESC'       => $_ARRAYLANG['TXT_COMPANY'],
                'TXT_COLUMN_3_DESC'       => $_ARRAYLANG['TXT_COUNT_ARTICLES'],
                'SHOP_ORDERS_SELECTED'    => "",
                'SHOP_ARTICLES_SELECTED'  => "",
                'SHOP_CUSTOMERS_SELECTED' => "selected=\"selected\"",
                ));
                $query = "SELECT A.currency_order_sum AS sum, A.selected_currency_id AS currency_id, C.company AS shopColumn2,sum(B.quantity) AS shopColumn3, C.lastname As lastname, C.firstname AS firstname, C.prefix AS prefix, C.customerid AS id
                           FROM  ".DBPREFIX."module_shop".MODULE_INDEX."_orders AS A,
                                ".DBPREFIX."module_shop".MODULE_INDEX."_order_items AS B,
                                ".DBPREFIX."module_shop".MODULE_INDEX."_customers AS C
                           WHERE A.orderid = B.orderid
                          AND A.customerid = C.customerid
                          AND A.order_date >= '$shopStartDate'
                          AND A.order_date <= '$shopStopDate'
                          AND (A.order_status = '1' OR A.order_status = '4')
                          GROUP BY B.orderid
                           ORDER BY sum DESC";
            } else {
                //query for order stats (default)
                //sells per month
                $this->_objTpl->setVariable(array(
                'TXT_COLUMN_1_DESC'       => $_ARRAYLANG['TXT_DATE'],
                'TXT_COLUMN_2_DESC'       => $_ARRAYLANG['TXT_COUNT_ORDERS'],
                'TXT_COLUMN_3_DESC'       => $_ARRAYLANG['TXT_COUNT_ARTICLES'],
                'SHOP_ORDERS_SELECTED'    => "selected=\"selected\"",
                'SHOP_ARTICLES_SELECTED'  => "",
                'SHOP_CUSTOMERS_SELECTED' => "",
                ));
                $query = "SELECT sum(A.quantity) AS shopColumn3, count(A.orderid) AS shopColumn2, B.selected_currency_id, B.currency_order_sum AS sum, DATE_FORMAT(B.order_date,'%m') AS month, DATE_FORMAT(B.order_date,'%Y') AS year
                           FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items AS A,
                               ".DBPREFIX."module_shop".MODULE_INDEX."_orders AS B
                          WHERE A.orderid = B.orderid
                          AND B.order_date >= '$shopStartDate'
                          AND B.order_date <= '$shopStopDate'
                          AND (B.order_status='1' OR B.order_status='4')
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
                        $this->objCurrency->activeCurrencyId = $objResult->fields['selected_currency_id'];
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
                        $arrayResults[$key]['column4'] = $arrayResults[$key]['column4'] + $objResult->fields['shopColumn2'] * $this->objCurrency->getDefaultCurrencyPrice($objResult->fields['sum']);

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
                        $this->objCurrency->activeCurrencyId = $objResult->fields['currency_id'];

                        $key = $objResult->fields['id'];
                        $shopCustomerName = ltrim($objResult->fields['prefix']." ".$objResult->fields['firstname']." ".$objResult->fields['lastname']);
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
                        $arrayResults[$key]['column4'] += $this->objCurrency->getDefaultCurrencyPrice($objResult->fields['sum']);
                        $sumColumn3 += $objResult->fields['shopColumn3'];
                        $sumColumn4 += $this->objCurrency->getDefaultCurrencyPrice($objResult->fields['sum']);
                        $objResult->MoveNext();
                    }
                } else { //it's the default statistic (orders)
                    $arrayMonths=explode(",", $_ARRAYLANG['TXT_MONTH_ARRAY']);
                    while (!$objResult->EOF) {
                        // set currency di
                        $this->objCurrency->activeCurrencyId = $objResult->fields['selected_currency_id'];

                        $key = $objResult->fields['year'].".".$objResult->fields['month'];
                        if (!isset($arrayResults[$key])) {
                            $arrayResults[$key] = array(
                                'column1' => '',
                                'column2' => 0,
                                'column3' => 0,
                                'column4' => 0,
                            );
                        }
                        $arrayResults[$key]['column1'] = $arrayMonths[intval($objResult->fields['month'])-1]." ".$objResult->fields['year'];
                        $arrayResults[$key]['column2'] = $arrayResults[$key]['column2'] +1;
                        $arrayResults[$key]['column3'] = $arrayResults[$key]['column3'] + $objResult->fields['shopColumn3'];
                        $arrayResults[$key]['column4'] = $arrayResults[$key]['column4'] + $this->objCurrency->getDefaultCurrencyPrice($objResult->fields['sum']);
                        $sumColumn2 = $sumColumn2 + 1;
                        $sumColumn3 = $sumColumn3 + $objResult->fields['shopColumn3'];
                        $sumColumn4 = $sumColumn4 + $this->objCurrency->getDefaultCurrencyPrice($objResult->fields['sum']);
                        $objResult->MoveNext();
                    }
                    krsort($arrayResults, SORT_NUMERIC);
                }
                //set block an read whole array out
                $this->_objTpl->setCurrentBlock("statisticRow");
                $arrCurrency = $this->objCurrency->getCurrencyArray();
                $i=0; //used for row-class
                if (is_array($arrayResults)) {
                    foreach ($arrayResults as $entry) {
                        if (($i % 2) == 0) {$class="row1";} else {$class="row2";}
                        $this->_objTpl->setVariable(array(
                        'SHOP_ROWCLASS'  => $class,
                        'SHOP_COLUMN_1'  => $entry['column1'],
                        'SHOP_COLUMN_2'  => $entry['column2'],
                        'SHOP_COLUMN_3'  => $entry['column3'],
                        'SHOP_COLUMN_4'  => Currency::formatPrice($entry['column4'])." ".$arrCurrency[$this->objCurrency->defaultCurrencyId]['symbol'],

                        ));
                        $this->_objTpl->parse("statisticRow");
                        $i++;
                    }
                }
            }
        } else {
            $sumColumn2 = 0;
            $arrayMonths=explode(",", $_ARRAYLANG['TXT_MONTH_ARRAY']);
            $shopActualMonth = "<option value=\"".Date("m")."\">".$arrayMonths[Date("m")-1]."</option>\n";
            $shopActualYear = "<option value=\"".Date("Y")."\">".Date("Y")."</option>\n";
            $this->_objTpl->setVariable(array(
            'SHOP_START_MONTH'        => $shopActualMonth,
            'SHOP_END_MONTH'          => $shopActualMonth,
            'SHOP_START_YEAR'         => $shopActualYear,
            'SHOP_END_YEAR'           => $shopActualYear,
            'TXT_COLUMN_1_DESC'       => $_ARRAYLANG['TXT_DATE'],
            'TXT_COLUMN_2_DESC'       => $_ARRAYLANG['TXT_COUNT_ORDERS'],
            'TXT_COLUMN_3_DESC'       => $_ARRAYLANG['TXT_COUNT_ARTICLES'],
            'SHOP_ORDERS_SELECTED'    => "selected=\"selected\"",
            'SHOP_ARTICLES_SELECTED'  => "",
            'SHOP_CUSTOMERS_SELECTED' => "",
            ));
        }
        //set the variables for the sum
        $this->_objTpl->setVariable(array(
        'SHOP_ROWCLASS'             => $i % 2 == 0 ? "row1" : "row2",
        'SHOP_TOTAL_SUM'         => Currency::formatPrice($shopTotalOrderSum)." ".$arrCurrency[$this->objCurrency->defaultCurrencyId]['symbol'],
        'SHOP_MONTH'             => $shopBestMonthDate,
        'SHOP_MONTH_SUM'         => Currency::formatPrice($shopBestMonthSum)." ".$arrCurrency[$this->objCurrency->defaultCurrencyId]['symbol'],
        'SHOP_TOTAL_ORDERS'      => $shopTotalOrders,
        'SHOP_SOLD_ARTICLES'     => $shopTotalSoldProducts,
        'SHOP_SUM_COLUMN_2'      => $sumColumn2,
        'SHOP_SUM_COLUMN_3'      => $sumColumn3,
        'SHOP_SUM_COLUMN_4'      => Currency::formatPrice($sumColumn4)." ".$arrCurrency[$this->objCurrency->defaultCurrencyId]['symbol'],
        'SHOP_STATISTIC_PAGING'  => $paging
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
                    ?   'selected="selected"'
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
        $this->addError($_ARRAYLANG['TXT_DATABASE_QUERY_ERROR']);
    }


    /**
     * Shows an overview of all pricelists
     * @global    array     $_ARRAYLANG
     * @global    mixed     $objDatabase
     */
    function shopPricelistOverview()
    {
        global $objDatabase, $_ARRAYLANG;

        $this->_objTpl->loadTemplateFile("module_shop_pricelist_overview.html", true, true);

        $this->_objTpl->setVariable(array(
            'TXT_CONFIRM_DELETE_ORDER' => $_ARRAYLANG['TXT_CONFIRM_DELETE_ORDER'],
            'TXT_DELETE_PRICELIST_MSG' => $_ARRAYLANG['TXT_DELETE_PRICELIST_MSG'],
            'TXT_ID'                   => $_ARRAYLANG['TXT_ID'],
            'TXT_NAME'                 => $_ARRAYLANG['TXT_NAME'],
            'TXT_PDF_LINK'             => $_ARRAYLANG['TXT_PDF_LINK'],
            'TXT_ACTION'               => $_ARRAYLANG['TXT_ACTION'],
            'TXT_MAKE_NEW_PRICELIST'   => $_ARRAYLANG['TXT_MAKE_NEW_PRICELIST'],
            'TXT_MAKE_SELECTION'       => $_ARRAYLANG['TXT_MAKE_SELECTION'],
            'TXT_MARKED'               => $_ARRAYLANG['TXT_MARKED'],
            'TXT_SELECT_ALL'           => $_ARRAYLANG['TXT_SELECT_ALL'],
            'TXT_REMOVE_SELECTION'     => $_ARRAYLANG['TXT_REMOVE_SELECTION'],
            'TXT_SELECT_ACTION'        => $_ARRAYLANG['TXT_SELECT_ACTION']
        ));

        $this->_objTpl->setGlobalVariable(array(
            'TXT_EDIT'                 => $_ARRAYLANG['TXT_EDIT'],
            'TXT_DELETE'               => $_ARRAYLANG['TXT_DELETE']
        ));

        $row_color = 0;

        $query = "SELECT id, name FROM ".DBPREFIX."module_shop".MODULE_INDEX."_pricelists ORDER BY name ASC";
        $objResult = $objDatabase->Execute($query);
        if ($objResult->RecordCount() > 0) { // there's a row in the database
            $this->_objTpl->setCurrentBlock("showPricelists");
            while (!$objResult->EOF) {
                if ($row_color % 2 == 0) {
                    $this->_objTpl->setVariable("PRICELIST_OVERVIEW_ROWCOLOR","row2");
                } else {
                    $this->_objTpl->setVariable("PRICELIST_OVERVIEW_ROWCOLOR","row1");
                }
                $this->_objTpl->setVariable(array(
                'PRICELIST_OVERVIEW_ID'      => $objResult->fields['id'],
                'PRICELIST_OVERVIEW_NAME'    => $objResult->fields['name'],
                'PRICELIST_OVERVIEW_PDFLINK' => "<a href='".ASCMS_PATH_OFFSET.'/modules/shop/pdf.php?plid='.$objResult->fields['id']."' target='_blank' title='".$_ARRAYLANG['TXT_DISPLAY']."'>".
                'http://'.$_SERVER['HTTP_HOST'].ASCMS_PATH_OFFSET.'/modules/shop/pdf.php?plid='.$objResult->fields['id'].'</a>'));

                $this->_objTpl->parse("showPricelists");
                $row_color++;
                $objResult->MoveNext();
            }
        } else {
            $this->_objTpl->hideBlock('shopPricelistOverview');
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

        $this->_objTpl->loadTemplateFile("module_shop_pricelist_details.html", true, true);

        $this->_objTpl->setVariable(array(
        'TXT_GENERAL_SETTINGS'   => $_ARRAYLANG['TXT_GENERAL_SETTINGS'],
        'TXT_PDF_LINK'           => $_ARRAYLANG['TXT_PDF_LINK'],
        'TXT_NAME'               => $_ARRAYLANG['TXT_NAME'],
        'TXT_LANGUAGE'           => $_ARRAYLANG['TXT_LANGUAGE'],
        'TXT_FRAME'              => $_ARRAYLANG['TXT_FRAME'],
        'TXT_DISPLAY'            => $_ARRAYLANG['TXT_DISPLAY'],
        'TXT_DONT_DISPLAY'       => $_ARRAYLANG['TXT_DONT_DISPLAY'],
        'TXT_HEADER'             => $_ARRAYLANG['TXT_HEADER'],
        'TXT_FOOTER'             => $_ARRAYLANG['TXT_FOOTER'],
        'TXT_DATE'               => $_ARRAYLANG['TXT_DATE'],
        'TXT_PAGENUMBER'         => $_ARRAYLANG['TXT_PAGENUMBER'],
        'TXT_PRODUCTSELECTION'   => $_ARRAYLANG['TXT_PRODUCTSELECTION'],
        'TXT_ALL_PRODUCTS'       => $_ARRAYLANG['TXT_ALL_PRODUCTS'],
        'TXT_SEPERATE_PRODUCTS'  => $_ARRAYLANG['TXT_SEPERATE_PRODUCTS'],
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

        $this->_objTpl->setVariable(array(
        'SHOP_PRICELIST_DETAILS_PLID'        => 'new',
        'SHOP_PRICELIST_DETAILS_ACT'         => 'pricelist_insert',
        'SHOP_PRICELIST_PDFLINK'             => '&nbsp;',
        'SHOP_PRICELIST_DETAILS_NAME'        => '',
        'SHOP_PRICELIST_DETAILS_BORDERON'    => 'checked="checked"',
        'SHOP_PRICELIST_DETAILS_BORDEROFF'   => '',
        'SHOP_PRICELIST_DETAILS_HEADERON'    => 'checked="checked"',
        'SHOP_PRICELIST_DETAILS_HEADEROFF'   => '',
        'SHOP_PRICELIST_DETAILS_HEADERLEFT'  => '',
        'SHOP_PRICELIST_DETAILS_HEADERRIGHT' => '',
        'SHOP_PRICELIST_DETAILS_FOOTERON'    => 'checked="checked"',
        'SHOP_PRICELIST_DETAILS_FOOTEROFF'   => '',
        'SHOP_PRICELIST_DETAILS_FOOTERLEFT'  => '',
        'SHOP_PRICELIST_DETAILS_FOOTERRIGHT' => '',
        'SHOP_PRICELIST_DETAILS_ALLPROD'     => 'checked="checked"',
        'SHOP_PRICELIST_DETAILS_SEPPROD'     => '',
        'SHOP_PRICELIST_DETAILS_LANGUAGE'    => $langMenu
        ));

        $selectedCategories = '*';

        $this->_objTpl->setCurrentBlock("showShopCategories");
        $this->_objTpl->setCurrentBlock("showShopCategories2");
        $this->_objTpl->setCurrentBlock("showShopCategories3");

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
        $query = "SELECT catid,catname ".
                 "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories ".
                 "WHERE parentid=0 ".
                 "ORDER BY catsorting ASC";
        $objResult = $objDatabase->Execute($query);
        while (!$objResult->EOF) {
            $this->_objTpl->setVariable("PDF_CATEGORY_NAME", $objResult->fields['catname']);
            if ($selectedCategories == '*') {
                $this->_objTpl->setVariable("PDF_CATEGORY_DISABLED",'disabled');
                $this->_objTpl->setVariable("PDF_CATEGORY_CHECKED",'');
            } else {
                $this->_objTpl->setVariable("PDF_CATEGORY_DISABLED",'');
                $this->_objTpl->setVariable("PDF_CATEGORY_CHECKED",''); //empty the field

                foreach ($selectedCategories as $checkedValue) {
                    if ($objResult->fields['catid'] == $checkedValue) { // this field is checked
                        $this->_objTpl->setVariable("PDF_CATEGORY_CHECKED",'checked');
                    }
                }
            }

            $this->_objTpl->setVariable("PDF_CATEGORY_ID", $objResult->fields['catid']);
            $this->_objTpl->setVariable("PDF_CATEGORY_ID2", $objResult->fields['catid']);
            $this->_objTpl->setVariable("PDF_CATEGORY_ID3", $objResult->fields['catid']);
            $this->_objTpl->setVariable("CATEGORY_OVERVIEW_ROWCOLOR", $row_color % 2 == 0 ? "row1" : "row2");

            $this->_objTpl->parse("showShopCategories");
            $this->_objTpl->parse("showShopCategories2");
            $this->_objTpl->parse("showShopCategories3");

            $row_color++;
            $objResult->MoveNext();
        }
    }


    /**
     * Inserts a new pricelist into the database
     *
     * @global    var        $objDatabase
     * @global    array    $_ARRAYLANG
     */
    function shopPricelistInsert()
    {
        global $objDatabase, $_ARRAYLANG;

        if ($_POST['productsAll']) {
            $selectedCategories = '*';
        }
        else
        {
            foreach ($_POST as $key => $value) {
                if (substr($key,0,14) == 'categoryNumber') {
                    $arrSelectedMainCats[$value] = $value;
                }
            }

            foreach ($arrSelectedMainCats as $key => $value) {
                $this->doCategoryTreeActiveOnly($value);
                foreach (array_keys($this->categoryTreeName) as $catKey) {
                    $selectedCategories .= $catKey.",";
                }
                $selectedCategories .= $value.","; //I also have to add the maincategory!! IMPORTANT
            }

            $selectedCategories = substr($selectedCategories,0,strlen($selectedCategories)-1); // drop the last ,
            if ($selectedCategories == '') { // no groups were selected.. so select all for preventing errors
                $selectedCategories = '*';
            }

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
        $this->addMessage($_ARRAYLANG['TXT_PRODUCT_LIST_CREATED_SUCCESSFUL']);
    }


    /**
     * Shows the Edit-Pricelist Template filled with all values
     *
     * @global    var        $objDatabase
     * @global    array    $_ARRAYLANG
     */
    function shopPricelistEdit($pricelistID)
    {
        global $objDatabase, $_ARRAYLANG;

        $this->_objTpl->loadTemplateFile("module_shop_pricelist_details.html", true, true);
        $this->_objTpl->setVariable(array(
            'TXT_GENERAL_SETTINGS'   => $_ARRAYLANG['TXT_GENERAL_SETTINGS'],
            'TXT_PDF_LINK'           => $_ARRAYLANG['TXT_PDF_LINK'],
            'TXT_NAME'               => $_ARRAYLANG['TXT_NAME'],
            'TXT_LANGUAGE'           => $_ARRAYLANG['TXT_LANGUAGE'],
            'TXT_FRAME'              => $_ARRAYLANG['TXT_FRAME'],
            'TXT_DISPLAY'            => $_ARRAYLANG['TXT_DISPLAY'],
            'TXT_DONT_DISPLAY'       => $_ARRAYLANG['TXT_DONT_DISPLAY'],
            'TXT_HEADER'             => $_ARRAYLANG['TXT_HEADER'],
            'TXT_FOOTER'             => $_ARRAYLANG['TXT_FOOTER'],
            'TXT_DATE'               => $_ARRAYLANG['TXT_DATE'],
            'TXT_PAGENUMBER'         => $_ARRAYLANG['TXT_PAGENUMBER'],
            'TXT_PRODUCTSELECTION'   => $_ARRAYLANG['TXT_PRODUCTSELECTION'],
            'TXT_ALL_PRODUCTS'       => $_ARRAYLANG['TXT_ALL_PRODUCTS'],
            'TXT_SEPERATE_PRODUCTS'  => $_ARRAYLANG['TXT_SEPERATE_PRODUCTS'],
            'TXT_STORE_PRODUCT_LIST' => $_ARRAYLANG['TXT_STORE_PRODUCT_LIST']
        ));

        $objResult = $objDatabase->Execute("SELECT * FROM ".DBPREFIX."module_shop".MODULE_INDEX."_pricelists WHERE id=".$pricelistID);

        $langId = $objResult->fields['lang_id'];
        $this->_objTpl->setVariable("SHOP_PRICELIST_DETAILS_ACT",'pricelist_update&amp;id='.$objResult->fields['id']);
        $this->_objTpl->setVariable("SHOP_PRICELIST_PDFLINK",

        "<a href=\"".ASCMS_PATH_OFFSET."/modules/shop/pdf.php?plid=".$objResult->fields['id']."\" target=\"_blank\" title=\"PDF\">".
        "http://".$_SERVER['HTTP_HOST'].ASCMS_PATH_OFFSET."/modules/shop/pdf.php?plid=".$objResult->fields['id']."</a>");

        $this->_objTpl->setVariable("SHOP_PRICELIST_DETAILS_NAME", $objResult->fields['name']);

        //are the borders on?
        if ($objResult->fields['border_on'] == 1) {
            $this->_objTpl->setVariable("SHOP_PRICELIST_DETAILS_BORDERON","checked=\"checked\"");
            $this->_objTpl->setVariable("SHOP_PRICELIST_DETAILS_BORDEROFF","");
        } else {
            $this->_objTpl->setVariable("SHOP_PRICELIST_DETAILS_BORDERON","");
            $this->_objTpl->setVariable("SHOP_PRICELIST_DETAILS_BORDEROFF","checked=\"checked\"");
        }
        //is the header on?
        if ($objResult->fields['header_on'] == 1) {
            $this->_objTpl->setVariable("SHOP_PRICELIST_DETAILS_HEADERON","checked=\"checked\"");
            $this->_objTpl->setVariable("SHOP_PRICELIST_DETAILS_HEADEROFF","");
            $this->_objTpl->setVariable("SHOP_PRICELIST_DETAILS_HEADERLEFT", $objResult->fields['header_left']);
            $this->_objTpl->setVariable("SHOP_PRICELIST_DETAILS_HEADERRIGHT", $objResult->fields['header_right']);
        } else {
            $this->_objTpl->setVariable("SHOP_PRICELIST_DETAILS_HEADERON","");
            $this->_objTpl->setVariable("SHOP_PRICELIST_DETAILS_HEADEROFF","checked=\"checked\"");
        }
        //is the footer on?
        if ($objResult->fields['footer_on'] == 1) {
            $this->_objTpl->setVariable("SHOP_PRICELIST_DETAILS_FOOTERON","checked=\"checked\"");
            $this->_objTpl->setVariable("SHOP_PRICELIST_DETAILS_FOOTEROFF","");
            $this->_objTpl->setVariable("SHOP_PRICELIST_DETAILS_FOOTERLEFT", $objResult->fields['footer_left']);
            $this->_objTpl->setVariable("SHOP_PRICELIST_DETAILS_FOOTERRIGHT", $objResult->fields['footer_right']);
        } else {
            $this->_objTpl->setVariable("SHOP_PRICELIST_DETAILS_FOOTERON","");
            $this->_objTpl->setVariable("SHOP_PRICELIST_DETAILS_FOOTEROFF","checked=\"checked\"");
        }
        //which products were selected before? All or seperate?
        if ($objResult->fields['categories'] == '*') { // all categories
            $this->_objTpl->setVariable("SHOP_PRICELIST_DETAILS_ALLPROD","checked=\"checked\"");
            $this->_objTpl->setVariable("SHOP_PRICELIST_DETAILS_SEPPROD","");
            $arrSelectedCategories = '*';
        } else {
            // I have to split the string into a nice array :)
            $this->_objTpl->setVariable("SHOP_PRICELIST_DETAILS_ALLPROD","");
            $this->_objTpl->setVariable("SHOP_PRICELIST_DETAILS_SEPPROD","checked=\"checked\"");

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
                $langMenu .= "<option value=\"".$objResult->fields['id']."\"".($objResult->fields['id'] == $langId ? " selected=\"selected\"" : "").">".$objResult->fields['name']."</option>\n";
                $objResult->MoveNext();
            }
        }
        $langMenu .= "</select>\n";
        $this->_objTpl->setVariable("SHOP_PRICELIST_DETAILS_LANGUAGE", $langMenu);

        $this->_objTpl->setCurrentBlock("showShopCategories");
        $this->_objTpl->setCurrentBlock("showShopCategories2");
        $this->_objTpl->setCurrentBlock("showShopCategories3");
        $this->shopPricelistMainCategories($arrSelectedCategories);
    }


    /**
     * Update a pricelist-entry in the database
     *
     * @global  var     $objDatabase
     * @global  array   $_ARRAYLANG
     */
    function shopPricelistUpdate($pricelistID)
    {
        global $objDatabase, $_ARRAYLANG;

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
                    $selectedCategories .= $catKey.",";
                }
                $selectedCategories .= $value.","; //I also have to add the maincategory!! IMPORTANT
            }

            $selectedCategories = substr($selectedCategories,0,strlen($selectedCategories)-1); // drop the last ,
            if ($selectedCategories == '') { // no groups were selected.. so select all for preventing errors
                $selectedCategories = '*';
            }
        }

        if (empty($_POST['pricelistName'])) {
            $_POST['pricelistName'] = $_ARRAYLANG['TXT_NO_NAME'];
        }

        $query = "UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_pricelists ".
                 "SET name=         '".addslashes(strip_tags($_POST['pricelistName']))."', ".
                     "lang_id=       ".intval($_POST['langId']).", ".
                     "border_on=     ".intval($_POST['borderOn']).", ".
                     "header_on=     ".intval($_POST['headerOn']).", ".
                     "header_left=  '".addslashes(trim($_POST['headerTextLeft']))."', ".
                     "header_right= '".addslashes(trim($_POST['headerTextRight']))."', ".
                     "footer_on=     ".intval($_POST['footerOn']).", ".
                     "footer_left=  '".addslashes(trim($_POST['footerTextLeft']))."', ".
                     "footer_right= '".addslashes(trim($_POST['footerTextRight']))."', ".
                     "categories=   '".addslashes($selectedCategories)."' ".
                 "WHERE id=".$pricelistID;
        $objDatabase->Execute($query);

        $this->addMessage($_ARRAYLANG['TXT_PRODUCT_LIST_UPDATED_SUCCESSFUL']);
    }


    /**
     * Delete a pricelist
     *
     * @global  mixed   $objDatabase
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
                    $this->addMessage($_ARRAYLANG['TXT_PRICELIST_MESSAGE_DELETED']);
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
     * @global  mixed   $objDatabase
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
            '<option value="0"></option>';
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
     * Adds the string $strErrorMessage to the error messages.
     *
     * If necessary, inserts a line break tag (<br />) between
     * error messages.
     * @param   string  $strErrorMessage    The error message to add
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function addError($strErrorMessage)
    {
        $this->strErrMessage .=
            ($this->strErrMessage != '' && $strErrorMessage != ''
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
     */
    function addMessage($strOkMessage)
    {
        $this->strOkMessage .=
            ($this->strOkMessage != '' && $strOkMessage != ''
                ? '<br />' : ''
            ).$strOkMessage;
    }

}

?>
