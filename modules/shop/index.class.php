<?php

define('_SHOP_DEBUG', 0);

/*

Customization for Shop (or any other module) cloning

Below, "#" always stands for the module index number, and * represents
the variable remainder of any table name, parameter line or the like.
Perform the following steps for each new instance:

- Copy all necessary database tables, naming them like
      DBPREFIX."module_shop#_*"
  E.g.: The table contrexx_module_shop_categories is copied to
  contrexx_module_shop2_categories.
  Copy the structure in any case; the table content may be copied, or not.

- Fix the code to be able to access the cloned tables.  Use the constant
  MODULE_INDEX to refer to different instances:
      From: ".DBPREFIX."module_shop*
      To:   ".DBPREFIX."module_shop".MODULE_INDEX."*
  Note: Mind the quotes (" or ')!

- Fix any URI referring to any shop page, both in the code and all the
  templates (frontend and backend!) like this:
  - Code:
      From: index.php?section=shop*
      To:   index.php?section=shop".MODULE_INDEX."*

    Note: Mind the quotes (" or ')!

    You also have to add a line to your constructor (or getPage() method,
    or wherever appropriate) in your module that sets the MODULE_INDEX
    placeholder in every template *just after loading it*.
    In any case, make sure that it is set *before* any blocks are parsed
    that rely on it!
      // Global module index for clones
      $this->objTemplate->setGlobalVariable('MODULE_INDEX', MODULE_INDEX);

  - Templates and language variables:
      From: index.php?section=shop*
      To:   index.php?section=shop{MODULE_INDEX}*

- Clone the module and backend area
    INSERT INTO `contrexx_backend_areas` VALUES(0, 2, 'navigation', 'TXT_SHOP#', 1, 'index.php?cmd=shop#', '_self', 116, 0, [1]13);
    INSERT INTO `contrexx_modules` VALUES(116, 'shop#', 'TXT_SHOP#_MODULE_DESCRIPTION', 'y', 0, 0);

    [1] Note:  If you need to be able to configure access rights independently
               for individual clones, add 1000*MODULE_INDEX to the original ID.
               Fix the rights verification in the code that loads your module:


- Add the new language variables from the previous step to lang/xy/backend.php

- Copy the frontend templates, change their module parameter to match the
  new module index.

- Add backend user permissions for the cloned module as desired

And finally:

- Test:
  - You can see and access all instances of the Shop in the modules
    section of the backend.
  - You can create or change a Product, Category, Setting, etc. in any
    Shop without changing the content of any other instance.
  - You can access all the Shops and complete a purchase.

 */


/**
 * The Shop
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Ivan Schmid <ivan.schmid@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Common methods for both frontend and backend of the Shop.
 */
require_once ASCMS_MODULE_PATH.'/shop/shopLib.class.php';
/**
 * Currency: Conversion, formatting.
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Currency.class.php';
/**
 * Payment: Selection of the payment method.
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Payment.class.php';
/**
 * Payment Processing: Provides functionality for the payment process.
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/PaymentProcessing.class.php';
/**
 * Shipment: Selection of the Shipper and Shipment conditions.
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Shipment.class.php';
/**
 * Vat: Calculation of the VAT amounts, formatting.
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Vat.class.php';
/**
 * Weight: Formatting and conversion.
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Weight.class.php';
/**
 * Customer object with database layer.
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Customer.class.php';
/**
 * ShopCategory: Database layer
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/ShopCategory.class.php';
/**
 * ShopCategories: Various Helpers and display functions.
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/ShopCategories.class.php';
/**
 * Discount: Custom calculations for discounts
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Discount.class.php';


/**
 * Check whether a session will be required and has to get inizialized
 * @return  boolean     True if a session is required, false otherwise.
 */
function shopUseSession()
{
    if (!empty($_COOKIE['PHPSESSID'])) {
        return true;
    } elseif (!empty($_REQUEST['currency'])) {
        return true;
    } else {
        $command = '';
        if (!empty($_GET['cmd'])) {
            $command = $_GET['cmd'];
        } elseif (!empty($_GET['act'])) {
            $command = $_GET['act'];
        }
        if (in_array($command, array('', 'discounts', 'details', 'terms', 'cart'))) {
            if (   $command == 'details'
                && isset($_REQUEST['referer'])
                && $_REQUEST['referer'] == 'cart'
            ) {
                return true;
            } elseif ($command == 'cart' && (isset($_REQUEST['productId']) || (isset($_GET['remoteJs']) && $_GET['remoteJs'] == 'addProduct' && !empty($_GET['product'])))) {
                return true;
            }
            return false;
        } else {
            return true;
        }
    }
}


/**
 * Shop
 *
 * @todo    Extract code from this class and move it to other classes:
 *          Customer, Product, ...
 * @todo    It doesn't really make sense to extend ShopLibrary.  Instead, dissolve
 *          ShopLibrary into classes like Shop, Zone, Country, Payment, etc.
 */
class Shop extends ShopLibrary
{
    var $pageContent;

// TODO: remove
    var $arrCategoriesSorted = array();
    var $arrCategoriesTable = array();
    var $arrCategoriesName = array();
    var $arrParentCategoriesId = array();
    var $arrParentCategoriesTable = array();

    var $statusMessage = '';
    var $thumbnailNameSuffix = '.thumb';
    var $is_reseller = 0;
    var $is_auth = 0;
    var $noPictureName = 'no_picture.gif';
    var $shopImageWebPath;
    var $shopImagePath;
    var $inactiveStyleName = 'inactive';
    var $activeStyleName = 'active';
    var $arrProductAttributes = array();
    var $langId;
    var $_defaultImage = '';

    /**
     * Active currency unit name (e.g. 'CHF', 'EUR', 'USD')
     * @var     string
     * @access  private
     */
    var $aCurrencyUnitName;

    /**
     * Currency navbar indicator
     * @var     boolean
     * @access  private
     */
    var $_hideCurrencyNavbar = false;

    /**
     * The PEAR Template Sigma object
     * @var     HTML_Template_Sigma
     * @access  private
     */
    var $objTemplate;

    /**
     * The Customer object
     * @var     Customer
     * @access  private
     * @see     lib/Customer.class.php
     */
    var $objCustomer;

    /**
     * Object of the payment offerer
     * @var     Payment
     * @access  private
     * @see     lib/Payment.class.php
     */
    var $objPayment;

    /**
     * The Payment Processing object
     * @var     PaymentProcessing
     * @access  private
     * @see     lib/PaymentProcessing.class.php
     */
    var $objProcessing;

    /**
     * The Shipment object
     * @var     Shipment
     * @access  private
     * @see     lib/Shipment.class.php
     */
    var $objShipment;

    /**
     * The Currency object
     * @var     Currency
     * @access  private
     * @see     lib/Currency.class.php
     */
    var $objCurrency;

    /**
     * The VAT object
     * @var     Vat
     * @access  private
     * @see     lib/Vat.class.php
     */
    var $objVat;

    /**
     * The ShopCategories helper object
     * @var ShopCategories
     */
    var $objShopCategories;


    /**
     * PHP4 constructor
     *
     * @param  string
     * @access public
     */
    function Shop($pageContent)
    {
        global $_LANGID, $objDatabase;

        if (_SHOP_DEBUG) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            $objDatabase->debug = 1;
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
            $objDatabase->debug = 0;
        }

        $this->langId = $_LANGID;
        $this->pageContent = $pageContent;
        $this->shopImageWebPath = ASCMS_SHOP_IMAGES_WEB_PATH.'/';
        $this->shopImagePath = ASCMS_SHOP_IMAGES_PATH.'/';
        $this->_defaultImage = $this->shopImageWebPath.$this->noPictureName;

        // PEAR Sigma template
        $this->objTemplate = new HTML_Template_Sigma('.');
        $this->objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $this->objTemplate->setTemplate($this->pageContent, true, true);
        // Global module index for clones
        $this->objTemplate->setGlobalVariable('MODULE_INDEX', MODULE_INDEX);

        // Currency object
        $this->objCurrency = new Currency();
        $this->aCurrencyUnitName = $this->objCurrency->getActiveCurrencySymbol();

        // Shipment object - ignoreStatus == false; see Shipment::Shipment()
        $this->objShipment = new Shipment(0);

        // Payment object
        $this->objPayment = new Payment();

        // initialize the countries array
        $this->_initCountries();

        // Check session and user data, log in if present
        $this->_authenticate();

        $this->_initConfiguration();
        $this->_initPayment();

        // VAT object -- Create this only after the configuration
        // ($this->arrConfig) has been set up!
        $this->objVat = new Vat();

        // The ShopCategories helper object
        $this->objShopCategories = new ShopCategories();

        // initialize the product options names and values array
        $this->initProductAttributes();

        // Payment processing object
        $this->objProcessing = new PaymentProcessing($this->arrConfig);

        $query = "SELECT catid, parentid, catname ".
            "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories ".
            "WHERE catstatus=1 ".
            "ORDER BY parentid ASC, catsorting ASC";
        $objResult = $objDatabase->Execute($query);
        while (!$objResult->EOF) {
            $this->arrCategoriesTable[$objResult->fields['parentid']][$objResult->fields['catid']]
                = $objResult->fields['catname'];
            $this->arrCategoriesName[$objResult->fields['catid']]
                = $objResult->fields['catname'];
            $this->arrParentCategoriesId[$objResult->fields['catid']]
                = $objResult->fields['parentid'];
            $this->arrParentCategoriesTable[$objResult->fields['catid']][$objResult->fields['parentid']]
                = $objResult->fields['catname'];
            $objResult->MoveNext();
        }
    }


    /**
     * Initialize product attributes from database
     *
     * @global  mixed   $objDatabase    Database
     * @see     $arrProductAttributes
     */
    function initProductAttributes()
    {
        global $objDatabase;

        // get product attributes
        $query = "SELECT attributes.product_id AS productId,
                         attributes.attributes_name_id AS nameId,
                         attributes.attributes_value_id AS valueId,
                         attributes.sort_id AS sort_id,
                         value.id as attrValId,
                         name.name AS nameTxt,
                         name.display_type as type,
                         value.value AS valueTxt,
                         value.price AS price,
                         value.price_prefix AS pricePrefix
                    FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes AS attributes,
                         ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name AS name,
                         ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value AS value
                   WHERE attributes.attributes_name_id = name.id
                     AND attributes.attributes_value_id = value.id
                   ORDER BY sort_id, attrValId ASC";

        if (($objResult = $objDatabase->Execute($query)) !== false) {
            while (!$objResult->EOF) {
                if (!isset($this->arrProductAttributes[$objResult->fields['productId']][$objResult->fields['nameId']])) {
                    $this->arrProductAttributes[$objResult->fields['productId']][$objResult->fields['nameId']] = array(
                        'name'         => $objResult->fields['nameTxt'],
                        'type'         => $objResult->fields['type']
                    );
                }

                $this->arrProductAttributes[$objResult->fields['productId']][$objResult->fields['nameId']]['values'][$objResult->fields['valueId']] = array(
                        'id'            => $objResult->fields['valueId'],
                        'value'         => $objResult->fields['valueTxt'],
                        'price'         => $objResult->fields['price'],
                        'price_prefix'  => $objResult->fields['pricePrefix'],
                );
                $objResult->MoveNext();
            }
        }
    }


    function getShopPage()
    {
        global $themesPages;

        $this->objTemplate->setVariable('SHOPNAVBAR_FILE', $this->getShopNavbar($themesPages['shopnavbar']));
        if (isset($_GET['cmd'])) {
            switch($_GET['cmd']) {
                case 'shipment':
                    $_GET['act'] = 'shipment';
                    break;
                case 'success':
                    $_GET['act'] = 'success';
                    break;
                case 'confirm':
                    $_GET['act'] = 'confirm';
                    break;
                case 'lsv':
                    $_GET['act'] = 'lsv';
                    break;
                case 'einzug':
                    $_GET['act'] = 'einzug';
                    break;
                case 'payment':
                    $_GET['act'] = 'payment';
                    break;
                case 'account':
                    $_GET['act'] = 'account';
                    break;
                case 'cart':
                    $_GET['act'] = 'cart';
                    break;
                case 'products':
                case 'details': // Redirected to products explicitly
                    $_GET['act'] = 'products';
                    break;
                case 'discounts':
                    $_GET['act'] = 'discounts';
                    break;
                case 'lastFive':
                    $_GET['act'] = 'lastFive';
                    break;
                case 'login':
                    $_GET['act'] = 'login';
                    break;
                case 'sendpass':
                    $_GET['act'] = 'sendpass';
                    break;
                case 'changepass';
                    $_GET['act'] = 'changepass';
                    break;
                default:
                    $_GET['act'] = 'products';
                    break;
            }
        }

        if (isset($_GET['act'])) {
            switch($_GET['act']) {
                case 'shipment':
                    $this->showShipmentTerms();
                    break;
                case 'success':
                    $this->success();
                    break;
                case 'confirm':
                    $this->confirm();
                    break;
                case 'lsv':
                    $this->lsv();
                    break;
                case 'einzug':
                    $this->einzug();
                    break;
                case 'payment':
                    $this->payment();
                    break;
                case 'account':
                    $this->account();
                    break;
                case 'cart':
                    $this->cart();
                    break;
                case 'products':
                    $this->products();
                    break;
                case 'discounts':
                    $this->discounts();
                    break;
                case 'lastFive':
                    $this->products();
                    break;
                case 'login':
                    $this->login();
                    break;
                case 'destroy':
                    $this->destroyCart();
                    $this->products();
                    break;
                case 'paypalIpnCheck':
                    require_once ASCMS_MODULE_PATH.'/shop/payments/paypal/Paypal.class.php';
                    $objPaypal = new PayPal;
                    $objPaypal->ipnCheck();
                    exit;
                    break;
                case 'sendpass':
                    $this->_sendpass();
                    break;
                case 'changepass';
                    $this->_changepass();
                    break;
                // Test for PayPal IPN.
                // *DO NOT* remove this!  Needed for site testing.
                case 'testIpn':
                    require_once ASCMS_MODULE_PATH."/shop/payments/paypal/Paypal.class.php";
                    $objPaypal = new PayPal;
                    $objPaypal->testIpn(); // die()s!
                // Test for PayPal IPN validation
                // *DO NOT* remove this!  Needed for site testing.
                case 'testIpnValidate':
                    require_once ASCMS_MODULE_PATH."/shop/payments/paypal/Paypal.class.php";
                    $objPaypal = new PayPal;
                    $objPaypal->testIpnValidate(); // die()s!
                default:
                    $this->products();
                    break;
            }
        } else {
            $this->products();
        }
        $this->objTemplate->setVariable('SHOP_STATUS', $this->statusMessage);
        return $this->objTemplate->get();
    }


    function getShopNavbar($shopNavbarContent)
    {
        global $objDatabase, $_ARRAYLANG;

        static $strContent;
        if ($strContent) {
            return $strContent;
        }

        $objTpl = new HTML_Template_Sigma('.');
        $objTpl->setErrorHandling(PEAR_ERROR_DIE);
        $objTpl->setTemplate($shopNavbarContent, true, true);

        if ($this->objCustomer) {
            if ($this->objCustomer->getCompany()) {
                $loginInfo = $this->objCustomer->getCompany().'<br />';
            } else {
                $loginInfo =
                    $this->objCustomer->getPrefix().' '.
                    $this->objCustomer->getLastName().'<br />';
            }
            $loginStatus = $_ARRAYLANG['TXT_LOGGED_IN_AS'].'<br />';
        } else {
            $loginInfo   = '';
            $loginStatus = $_ARRAYLANG['TXT_LOGGED_IN_AS_SHOP_GUEST'];
        }
        $objTpl->setGlobalVariable(array(
            'SHOP_CART_INFO'    => $this->showCartInfo(),
            'SHOP_LOGIN_STATUS' => $loginStatus,
            'SHOP_LOGIN_INFO'   => $loginInfo,
            'TXT_SHOP_SHOW_CART' => $_ARRAYLANG['TXT_SHOP_SHOW_CART'],
            'TXT_SHOP_CART_CONTENT' => $_ARRAYLANG['TXT_SHOP_CART_CONTENT'],
            'TXT_SHOP_CART_IS_LOADING' => $_ARRAYLANG['TXT_SHOP_CART_IS_LOADING'],
            'TXT_SHOP_CART_PRODUCTS_VALUE' => $_ARRAYLANG['TXT_SHOP_CART_PRODUCTS_VALUE'],
        ));

        // start currencies
        if (!$this->_hideCurrencyNavbar) {
            if ($objTpl->blockExists('shopCurrencies')) {
                $objTpl->setCurrentBlock('shopCurrencies');

                $curNavbar = $this->objCurrency->getCurrencyNavbar();
                if (!empty($curNavbar)) {
                    $objTpl->setVariable('SHOP_CURRENCIES', $curNavbar);
                    $objTpl->setVariable('TXT_CURRENCIES', $_ARRAYLANG['TXT_CURRENCIES']);
                }
                $objTpl->parseCurrentBlock('shopCurrencies');
            }
        }
        // end currencies

        if ($objTpl->blockExists('shopNavbar')) {
            $selectedCatId = 0;
            if (isset($_REQUEST['catId'])) {
                $selectedCatId = intval($_REQUEST['catId']);
            }

            // Array of all visible ShopCategories
            $arrShopCategoryTree = $this->objShopCategories->getTreeArray(
                false, true, true, $selectedCatId, 0, 0
            );
            // The trail of IDs to the selected ShopCategory,
            // built along with the tree array when calling getTreeArray().
            $arrTrail = $this->objShopCategories->getTrailArray($selectedCatId);

            // Build the display of ShopCategories
            foreach ($arrShopCategoryTree as $arrShopCategory) {
                $level = $arrShopCategory['level'];
                // Skip levels too deep: if ($level >= 2) { continue; }
                $id = $arrShopCategory['id'];

                // Only the visible ShopCategories are stored in
                // $arrShopCategoryTree.  $arrTrail contains the full list
                // of IDs from root to selected, however.

                $style = 'shopnavbar'.($level+1);
                if (in_array($id, $arrTrail)) {
                    $style .= '_active';
                }
                $objTpl->setVariable(array(
                    'SHOP_CATEGORY_STYLE'  => $style,
                    'SHOP_CATEGORY_ID'     => $id,
                    'SHOP_CATEGORY_NAME'   =>
                        str_repeat('&nbsp;', 3*$level).
                        str_replace('"', '&quot;', $arrShopCategory['name']),
                ));
                $objTpl->parse("shopNavbar");
            }
        }
        $strContent = $objTpl->get();
        return $strContent;
    }


    /**
     * Put the JavsScript cart into the requested webpage
     *
     * Generate the base structure of the JavsScript cart and put it
     * in the template block shopJsCart.
     * @access  public
     * @global  array   $_ARRAYLANG Language array
     * @param   string  $cartTpl    Template of the JavaScript cart
     * @return  string              The parsed JavaScript cart template
     */
    function setJsCart($cartTpl)
    {
        global $_ARRAYLANG;

        $jsCart = '';
        $cartProductsTpl = '';
        if (empty($_REQUEST['section'])
         || $_REQUEST['section'] != 'shop'
         || empty($_REQUEST['cmd'])
         || $_REQUEST['cmd'] == 'details') {
            $arrMatch = '';
            if (preg_match('#^([\n\r]?[^<]*<.*id=["\']shopJsCart["\'][^>]*>)(([\n\r].*)*)(</[^>]*>[^<]*[\n\r]?)$#', $cartTpl, $arrMatch)) {
                $cartTpl = preg_replace('/\{([A-Z0-9_-]+)\}/', '[[\\1]]', $arrMatch[2]);

                $regs = '';
                if (preg_match_all('@<!--\s+BEGIN\s+(shopJsCartProducts)\s+-->(.*)<!--\s+END\s+\1\s+-->@sm', $cartTpl, $regs, PREG_SET_ORDER)) {
                    $cartProductsTpl = preg_replace('/\{([A-Z0-9_-]+)\}/', '[[\\1]]', $regs[0][2]);
                    $cartTpl = preg_replace('@(<!--\s+BEGIN\s+(shopJsCartProducts)\s+-->.*<!--\s+END\s+\2\s+-->)@sm', '[[SHOP_JS_CART_PRODUCTS]]', $cartTpl);
                }

                $jsCart =
                    $arrMatch[1].$_ARRAYLANG['TXT_SHOP_CART_IS_LOADING'].$arrMatch[4]."\n".
                    "<script type=\"text/javascript\" src=\"modules/shop/lib/html2dom.js\"></script>\n".
                    "<script type=\"text/javascript\">\n".
                    "// <![CDATA[\n".
                    "cartTpl = '".preg_replace(array("/'/", '/[\n\r]/', '/\//'), array("\\'", '\n','\\/'), $cartTpl)."';\n".
                    "cartProductsTpl = '".preg_replace(array("/'/", '/[\n\r]/'), array("\\'", '\n'), $cartProductsTpl)."';\n".
                    "if (typeof(objCart) != 'undefined') {shopGenerateCart();};\n".
                    "// ]]>\n".
                    "</script>\n";
                if ($_REQUEST['section'] != 'shop') {
                    $jsCart .= Shop::getJavascriptCode();
                }
            }
        }
        return $jsCart;
    }


    /**
     * Returns an array of all parent category names.
     *
     * The array is ordered from the innermost category at index 0
     * to the root category at the end of the array.
     * @param     integer  $currentid
     * @return    integer  $allparents
     */
    function _makeArrCurrentCategories($currentid=1)
    {
        // Array of parent categories names, ordered from
        // innermost category at index 0 to root category
        // at the end of the category.
        $arrParentIds = array();
        while ($currentid != 0) {
            if (   isset($this->arrParentCategoriesTable[$currentid])
                && is_array($this->arrParentCategoriesTable[$currentid])) {
                // parent ID => parent category name
                $arrParentName  = $this->arrParentCategoriesTable[$currentid];
                $result         = each($arrParentName);
                $arrParentIds[] = $result[0];
                $currentid      = $result[0];
            } else {
                $arrParentIds[] = 0;
                $currentid      = 0;
            }
        }
        return $arrParentIds;
    }


    /**
     * Change the customers' password
     *
     * If no customer is logged in, redirects to the login page.
     *
     */
    function _changepass()
    {
        global $objDatabase, $_ARRAYLANG;

        if (!isset($_SESSION['shop']['username'])) {
            header('Location: index.php?section=shop'.MODULE_INDEX.'&cmd=login');
            exit;
        }

        $this->objTemplate->setVariable(array(
            'SHOP_PASSWORD_CURRENT' => $_ARRAYLANG['SHOP_PASSWORD_CURRENT'],
            'SHOP_PASSWORD_NEW'     => $_ARRAYLANG['SHOP_PASSWORD_NEW'],
            'SHOP_PASSWORD_CONFIRM' => $_ARRAYLANG['SHOP_PASSWORD_CONFIRM'],
            'SHOP_PASSWORD_CHANGE'  => $_ARRAYLANG['SHOP_PASSWORD_CHANGE'],
        ));
        $this->objTemplate->parse('shop_change_password');

        if (isset($_POST['shopNewPassword'])) {
            if (!empty($_POST['shopNewPassword'])) {
                if (isset($_POST['shopCurrentPassword']) && !empty($_POST['shopCurrentPassword'])) {
                    if (isset($_POST['shopConfirmPassword']) && $_POST['shopNewPassword'] == $_POST['shopConfirmPassword']) {
                        if (strlen($_POST['shopNewPassword']) >= 6) {
                            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_customers SET `password`='".md5(contrexx_stripslashes($_POST['shopNewPassword']))."' WHERE username='".addslashes($_SESSION['shop']['username'])."' AND `password`='".md5(contrexx_stripslashes($_POST['shopCurrentPassword']))."'");
                            if ($objDatabase->Affected_Rows() == 1) {
                                $status = $_ARRAYLANG['TXT_SHOP_PASSWORD_CHANGED_SUCCESSFULLY'];
                            } else {
                                $status = $_ARRAYLANG['TXT_SHOP_WRONG_CURRENT_PASSWORD'];
                            }
                        } else {
                            $status = $_ARRAYLANG['TXT_PASSWORD_MIN_CHARS'];
                        }
                    } else {
                        $status = $_ARRAYLANG['TXT_SHOP_PASSWORD_NOT_CONFIRMED'];
                    }
                } else {
                    $status = $_ARRAYLANG['TXT_SHOP_ENTER_CURRENT_PASSWORD'];
                }
            } else {
                $status = $_ARRAYLANG['TXT_SHOP_SPECIFY_NEW_PASSWORD'];
            }

            $this->objTemplate->setVariable(array(
                'SHOP_PASSWORD_STATUS'  => $status.'<br />',
            ));
            $this->objTemplate->parse('shop_change_password_status');
        }
    }


    function _sendpass()
    {
        global $objDatabase, $_ARRAYLANG;

        $this->objTemplate->setVariable(array(
            'SHOP_PASSWORD_ENTER_EMAIL' => $_ARRAYLANG['SHOP_PASSWORD_ENTER_EMAIL'],
            'TXT_NEXT'                  => $_ARRAYLANG['TXT_NEXT'],
        ));
        $this->objTemplate->parse('shop_sendpass');
        if (isset($_POST['shopEmail']) && !empty($_POST['shopEmail'])) {
            $mail = contrexx_addslashes($_POST['shopEmail']);
            $query = "SELECT customerid,
                             username,
                             prefix,
                             lastname FROM ".DBPREFIX."module_shop".MODULE_INDEX."_customers WHERE email='".$mail."'";
            $objResult = $objDatabase->SelectLimit($query, 1);
            if ($objResult !== false) {
                if ($objResult->RecordCount() == 1) {
                    $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRESTUVWXYZ+-.,;:_!?$='#%&/()";
                    $passwordLength = rand(6, 8);
                    $password = '';
                    for ($char = 0; $char < $passwordLength; $char++) {
                        $password .= substr($chars, rand(0, 80), 1);
                    }

                    if ($objDatabase->Execute("UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_customers SET password='".md5($password)."' WHERE customerid=".$objResult->fields['customerid']) !== false) {
                        // Select template for sending login data
                        $arrShopMailtemplate = Shop::shopSetMailtemplate(3, $this->langId);
                        $shopMailFrom = $arrShopMailtemplate['mail_from'];
                        $shopMailFromText = $arrShopMailtemplate['mail_x_sender'];
                        $shopMailSubject = $arrShopMailtemplate['mail_subject'];
                        $shopMailBody = $arrShopMailtemplate['mail_body'];
                        //replace variables from template
                        $shopMailBody = str_replace("<USERNAME>", $objResult->fields['username'], $shopMailBody);
                        $shopMailBody = str_replace("<PASSWORD>", $password, $shopMailBody);
                        $shopMailBody = str_replace("<CUSTOMER_PREFIX>", $objResult->fields['prefix'], $shopMailBody);
                        $shopMailBody = str_replace("<CUSTOMER_LASTNAME>", $objResult->fields['lastname'], $shopMailBody);
                        $result = Shop::shopSendMail($mail, $shopMailFrom, $shopMailFromText, $shopMailSubject, $shopMailBody);

                        if ($result) {
                            $status = $_ARRAYLANG['TXT_SHOP_ACCOUNT_DETAILS_SENT_SUCCESSFULLY'];
                        } else {
                            $status = $_ARRAYLANG['TXT_SHOP_UNABLE_TO_SEND_EMAIL'];
                        }
                    } else {
                        $status = $_ARRAYLANG['TXT_SHOP_UNABLE_SET_NEW_PASSWORD'];
                    }
                } else {
                    $status = $_ARRAYLANG['TXT_SHOP_NO_ACCOUNT_WITH_EMAIL'];
                }
            } else {
                $status = $_ARRAYLANG['TXT_DATABASE_QUERY_ERROR'];
            }

            $this->objTemplate->setVariable(array(
                'SHOP_PASSWORD_STATUS'      => $status,
            ));
            $this->objTemplate->parse('shop_sendpass_status');
        }
    }


    /**
     * Set up the subcategories block in the current shop page.
     * @param   integer     $parentId   The optional parent ShopCategory ID,
     *                                  defaults to 0 (zero).
     * @return  boolean                 True on success, false otherwise
     * @global  array       $_ARRAYLANG Language array
     */
    function showCategories($parentId=0)
    {
        global $_ARRAYLANG;

        if ($parentId > 0) {
            $objShopCategory = ShopCategory::getById($parentId);
            // If we can't get this ShopCategory, it most probably does
            // not exist.
            if (!$objShopCategory) {
                if ($parentId > 0) {
                    // Retry using the root.
                    $this->showCategories(0);
                }
                // Otherwise, there's no point in looking for its
                // children either.
                return false;
            }
            // Show the parent ShopCategorys' image, if available
            $imageName = $objShopCategory->getPicture();
            if ($imageName
             && $this->objTemplate->blockExists('shopCategoryImage')) {
                $this->objTemplate->setCurrentBlock('shopCategoryImage');
                $this->objTemplate->setVariable(array(
                    'SHOP_CATEGORY_IMAGE'     =>
                        ASCMS_SHOP_IMAGES_WEB_PATH.'/category/'.$imageName,
                    'SHOP_CATEGORY_IMAGE_ALT' => $objShopCategory->getName(),
                ));
            }
        }

        // Get all active child categories with parent ID $parentId
        $arrShopCategory =
            ShopCategories::getChildCategoriesById($parentId, true);
        if (!is_array($arrShopCategory)) {
            return false;
        }
        $cell = 0;
        $this->objTemplate->setCurrentBlock();
        // For all child categories do...
        foreach ($arrShopCategory as $objShopCategory) {
            $id        = $objShopCategory->getId();
            $catName   = $objShopCategory->getName();
            $imageName = $objShopCategory->getPicture();
            $thumbnailPath = $this->_defaultImage;
            if ($imageName) {
                $imageName = $imageName;
            } else {
                // Look for a picture in the Products.
                $imageName = Products::getPictureByCategoryId($id);
            }
            if (!$imageName) {
                // Look for a picture in the subcategories and their Products.
                $imageName = ShopCategories::getPictureById($id);
            }
            if ($imageName) {
                // Image found!  Use that instead of the default.
                $thumbnailPath =
                    $this->shopImageWebPath.
                    $imageName.$this->thumbnailNameSuffix;
            }
            $this->objTemplate->setVariable(array(
                'SHOP_PRODUCT_TITLE'            => htmlentities($catName, ENT_QUOTES, CONTREXX_CHARSET),
                'SHOP_PRODUCT_THUMBNAIL'        => $thumbnailPath,
                'TXT_ADD_TO_CARD'               => $_ARRAYLANG['TXT_SHOP_GO_TO_CATEGORY'],
                'SHOP_PRODUCT_DETAILLINK_IMAGE' =>
                    "index.php?section=shop".MODULE_INDEX."&amp;catId=$id",
                'SHOP_PRODUCT_SUBMIT_FUNCTION'  => "location.replace('index.php?section=shop'.MODULE_INDEX.'&catId=$id')",
                'SHOP_PRODUCT_SUBMIT_TYPE'      => "button",
            ));
            // Add flag images for flagged ShopCategories
            $strImage = '';
            $arrVirtual = ShopCategories::getVirtualCategoryNameArray();
            foreach ($arrVirtual as $strFlag) {
                if ($objShopCategory->testFlag($strFlag)) {
                    $strImage .=
                        '<img src="images/content/'.$strFlag.
                        '.jpg" alt="'.$strFlag.'" />';
                }
            }
            if ($strImage) {
                $this->objTemplate->setVariable(
                    'SHOP_CATEGORY_FLAG_IMAGE', $strImage
                );
            }

            if ($this->objTemplate->blockExists('subCategories')) {
                $this->objTemplate->parse('subCategories');
                    if (++$cell % 4 == 0) {
                    $this->objTemplate->parse('subCategoriesRow');
                }
            }
        }
        return true;
    }


    /**
     * Recursively search the categories for a valid product picture.
     *
     * Searches the category given by the $catId argument first.  If no
     * pictures are found, recursively searches all child categories
     * (depth first).
     * OBSOLETE -- Replaced by {@link ShopCategories::getPictureById()}
     * @param   integer     $catId  The top category to search
     * @return  string              The product picture path on success,
     *                              false otherwise.
     */
    function getFirstProductPictureFromCategory($catId=0)
    {
        global $objDatabase;

        // Look for pictures in products from that category first
        $queryProduct = "
            SELECT picture
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
             WHERE catid=$catId
               AND picture!=''
          ORDER BY sort_order
        ";
        $objResultProduct = $objDatabase->SelectLimit($queryProduct, 1);
        if ($objResultProduct && $objResultProduct->RecordCount() > 0) {
            // got a picture!
            $arrImages = $this->_getShopImagesFromBase64String(
                $objResultProduct->fields['picture']
            );
            $picturePath = $this->shopImageWebPath.$arrImages[1]['img'];
            return $picturePath;
        }
        // no picture in that category, try its subcategories
        $querySubCat = "
            SELECT catid
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
             WHERE parentid=$catId
        ";
        $objResultSubCat = $objDatabase->Execute($querySubCat);
        // query failed, or no more subcategories? - give up
        if (!$objResultSubCat || $objResultSubCat->RecordCount() == 0) {
            return false;
        }
        while (!$objResultSubCat->EOF) {
            $childCatId = $objResultSubCat->fields['catid'];
            $picturePath =
                $this->getFirstProductPictureFromCategory($childCatId);
            if ($picturePath) {
                return $picturePath;
            }
            $objResultSubCat->MoveNext();
        }
        // no more subcategories, no picture -- give up
        return false;
    }


    /**
     * old version
     *
     * @param   integer $parentId
     * @todo    Remove.
     */
    function getCategoriesOld($parentId)
    {
        global $objDatabase, $_ARRAYLANG;

        if ($parentId == '') {
            $parentId = 0;
        }

        $query = "SELECT catid, catname
                    FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
                   WHERE parentid=".$parentId.' AND catstatus != 0
                   ORDER BY catsorting ASC, catname ASC';
        $objResult = $objDatabase->Execute($query);

        $this->objTemplate->setCurrentBlock('shopProductRow');
        $cell = 1;
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                //$file          = '';
                $thumbnailPath = '';
                $catId_Pic     = $objResult->fields['catid'];
                $catId_Link    = $objResult->fields['catid'];
                $catName       = $objResult->fields['catname'];

                if ($parentId == 0) {
                    $querySubCat = "SELECT catid FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories ".
                                   "WHERE parentid=".$catId_Pic;
                    $objResultSubCat = $objDatabase->SelectLimit($querySubCat, 1);
                    if (!$objResultSubCat->EOF) {
                        $catId_Pic = $objResultSubCat->fields['catid'];
                    }
                }

                $queryProduct = "SELECT picture ".
                    "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products ".
                    "WHERE catid=".$catId_Pic." ORDER BY sort_order";
                $objResultProduct = $objDatabase->SelectLimit($queryProduct, 1);
                if ($objResultProduct) {
                    $arrImages = $this->_getShopImagesFromBase64String($objResultProduct->fields['picture']);
                }
                // no product picture available
                if (!$arrImages
                 || $arrImages[1]['img'] == ''
                 || $arrImages[1]['img'] == $this->noPictureName) {
                    $thumbnailPath = $this->_defaultImage;
                } else {
                    // path offset is saved WITHOUT the image path!
                    $thumbnailPath = $this->shopImageWebPath.$arrImages[1]['img'].$this->thumbnailNameSuffix;
                }

                $this->objTemplate->setVariable(array(
                    'SHOP_PRODUCT_TITLE'            => str_replace('"', '&quot;', $catName),
                    'SHOP_PRODUCT_THUMBNAIL'        => $thumbnailPath,
                    'TXT_ADD_TO_CARD'               => $_ARRAYLANG['TXT_SHOP_GO_TO_CATEGORY'],
                    'SHOP_PRODUCT_DETAILLINK_IMAGE' => "index.php?section=shop".MODULE_INDEX."&amp;catId=".$catId_Link,
                    'SHOP_PRODUCT_SUBMIT_FUNCTION'  => "location.replace('index.php?section=shop".MODULE_INDEX."&catId=".$catId_Link."')",
                    'SHOP_PRODUCT_SUBMIT_TYPE'      => "button",
                ));

                if ($this->objTemplate->blockExists('subCategories')) {
                    $this->objTemplate->parse('subCategories');
                       if ($cell++ % 4 == 0) {
                        $this->objTemplate->parse('subCategoriesRow');
                    }
                }
                $objResult->MoveNext();
            }
        }
    }


    /**
     * Set up the shop page with products and discounts
     *
     * @return unknown                      Sometimes boolean, sometimes void
     * @global  mixed       $objDatabase    Database object
     * @global  array       $_ARRAYLANG     Language array
     * @global  array       $_CONFIG        Core configuration array, see {@link /config/settings.php}
     * @global  string(?)   $themesPages    Themes pages(?)
     * @todo    Determine return type
     * @todo    Documentation!
     */
    function products()
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG, $themesPages;

        // This now defaults to false, but may be switched back on
        // by popular request.
        $flagSpecialoffer = false;//true;
        $flagLastFive =
            (isset($_REQUEST['lastFive'])  ? true                   : false);
        $productId =
            (isset($_REQUEST['productId']) ? $_REQUEST['productId'] : 0);
        $catId =
            (isset($_REQUEST['catId'])     ? $_REQUEST['catId']     : 0);
        $manufacturerId =
            (isset($_REQUEST['manId'])     ? $_REQUEST['manId']     : 0);
        $term =
            (isset($_REQUEST['term'])
                ? stripslashes(trim($_REQUEST['term'])) : ''
            );
        $pos =
            (isset($_REQUEST['pos'])       ? $_REQUEST['pos']       : 0);

        if ($catId && $term == '') {
            $this->showCategories($catId);
        } elseif ($term == '') {
            $this->showCategories(0);
        }

        if ($this->objTemplate->blockExists('shopNextCategoryLink')) {
            $nextCat = ShopCategories::getNextShopCategoriesId($catId);
            $objShopCategory = ShopCategory::getById($nextCat);
            $this->objTemplate->setVariable(array(
                'SHOP_NEXT_CATEGORY_ID'    => $nextCat,
                'SHOP_NEXT_CATEGORY_TITLE' => str_replace('"', '&quot;', $objShopCategory->getName()),
                'TXT_SHOP_GO_TO_CATEGORY'  => $_ARRAYLANG['TXT_SHOP_GO_TO_CATEGORY'],
            ));
            $this->objTemplate->parse('shopNextCategoryLink');
        }

        $this->objTemplate->setGlobalVariable(array(
            'TXT_ADD_TO_CARD'            => $_ARRAYLANG['TXT_ADD_TO_CARD'],
            'TXT_PRODUCT_ID'             => $_ARRAYLANG['TXT_PRODUCT_ID'],
            'TXT_SHOP_PRODUCT_CUSTOM_ID' => $_ARRAYLANG['TXT_SHOP_PRODUCT_CUSTOM_ID'],
            'TXT_WEIGHT'                 => $_ARRAYLANG['TXT_WEIGHT'],
            'TXT_SHOP_CATEGORIES'        => $_ARRAYLANG['TXT_SHOP_CATEGORIES'],
            'TXT_SHOP_NORMALPRICE'       => $_ARRAYLANG['TXT_SHOP_NORMALPRICE'],
            'TXT_SHOP_DISCOUNTPRICE'     => $_ARRAYLANG['TXT_SHOP_DISCOUNTPRICE'],
            'SHOP_JAVASCRIPT_CODE'       => $this->getJavascriptCode(),
        ));
// Moved to getShopPage()
//        $this->objTemplate->setVariable('SHOPNAVBAR_FILE', $this->getShopNavbar($themesPages['shopnavbar']));

        if (isset($_REQUEST['referer']) && $_REQUEST['referer'] == 'cart') {
            $cartProdId = $productId;
            $productId = $_SESSION['shop']['cart']['products'][$productId]['id'];
        }

        $shopMenu =
            '<form action="index.php?section=shop'.MODULE_INDEX.'" method="post">'.
            '<input type="text" name="term" value="'.htmlentities($term, ENT_QUOTES, CONTREXX_CHARSET).'" style="width:150px;" />&nbsp;'.
            '<select name="catId" style="width:150px;">'.'<option value="0">'.$_ARRAYLANG['TXT_ALL_PRODUCT_GROUPS'].'</option>'.$this->objShopCategories->getShopCategoriesMenu($catId, true, 0).'</select>&nbsp;'.$this->_getManufacturerMenu($manufacturerId).'<input type="submit" name="Submit" value="'.$_ARRAYLANG['TXT_SEARCH'].'" style="width:66px;" /></form>';
        $this->objTemplate->setVariable("SHOP_MENU", $shopMenu);
        $this->objTemplate->setVariable("SHOP_CART_INFO", $this->showCartInfo());

        $pagingCatId = '';
        $pagingManId = '';
        $pagingTerm  = '';
        if ($catId > 0 && $term == '') {
            $flagSpecialoffer = false;
            $pagingCatId = "&amp;catId=$catId";
        }
        if ($manufacturerId > 0) {
            $flagSpecialoffer = false;
            $pagingManId =
                "&amp;manId=$manufacturerId";
        }
        if ($term != '') {
            $flagSpecialoffer = false;
            $pagingTerm =
                '&amp;term='.htmlentities($term, ENT_QUOTES, CONTREXX_CHARSET);
        }

        // The Product count is passed by reference and set to the total
        // number of records, though only as many as specified by the core
        // paging limit are returned in the array.
        $count = '0';
        if ($productId > 0) {
          $objProduct = Product::getById($productId);
            if ($objProduct) {
                $count = 1;
                $catId = $objProduct->getShopCategoryId();
                $arrProduct = array($objProduct);
            }
        } else {
            $arrProduct = Products::getByShopParams(
                $count, false, $flagSpecialoffer, $flagLastFive,
                $productId, $catId, $manufacturerId,
                $term, $pos
            );
        }

        $paging     = '';
        $detailLink = '';
        if ($count == 0) {
            $paging = $_ARRAYLANG['TXT_SELECT_SUB_GROUP'];
        } elseif ($_CONFIG['corePagingLimit']) { // From /config/settings.php
            $paging = getPaging(
                $count,
                $pos,
                '&amp;section=shop'.MODULE_INDEX.''.$pagingCatId.$pagingManId.$pagingTerm,
                '',
                true
            );
        }
        if (isset($this->arrCategoriesName[$catId])) {
            $this->objTemplate->setVariable(array(
                'TXT_PRODUCTS_IN_CATEGORY' => $_ARRAYLANG['TXT_PRODUCTS_IN_CATEGORY'],
                'SHOP_CATEGORY_NAME'       => str_replace('"', '&quot;', $this->arrCategoriesName[$catId]),
            ));
        }
        $this->objTemplate->setVariable(array(
            'SHOP_PRODUCT_PAGING' => $paging,
            'SHOP_PRODUCT_TOTAL'  => $count,
        ));

        if ($count == 0) {
            $this->objTemplate->hideBlock('shopProductRow');
            return true;
        }
        $formId = 0;
        $this->objTemplate->setCurrentBlock('shopProductRow');
        foreach ($arrProduct as $objProduct) {
            $productSubmitFunction = '';
            $arrPictures = $this->_getShopImagesFromBase64String($objProduct->getPictures());
            $havePicture = false;
            $arrProductImages = array();
            foreach ($arrPictures as $index => $image) {
                if (   empty($image['img'])
                    || $image['img'] == $this->noPictureName) {
                    // We have at least one picture on display already.
                    // No need to show "no picture" three times!
                    if ($havePicture) { continue; }
                    $thumbnailPath = $this->_defaultImage;
                    $pictureLink = ''; //"javascript:alert('".$_ARRAYLANG['TXT_NO_PICTURE_AVAILABLE']."');";
                } elseif ($image['width'] && $image['height']) {
                    $thumbnailPath = $this->shopImageWebPath.$image['img'].$this->thumbnailNameSuffix;
                    $pictureLink = "javascript:viewPicture('".$this->shopImageWebPath.$image['img']."','width=".($image['width']+25).",height=".($image['height']+25)."')";
                } else {
                    $thumbnailPath = $this->shopImageWebPath.$image['img'].$this->thumbnailNameSuffix;
                    $pictureLink = '';
                }
                $arrProductImages[] = array(
                    'THUMBNAIL'       => $thumbnailPath,
                    'THUMBNAIL_LINK'  => $pictureLink,
// TODO: Where are SHOP_PRODUCT_POPUP_LINK_x
//             and SHOP_PRODUCT_POPUP_LINK_NAME_x used?
                    'POPUP_LINK'      => $pictureLink,
                    'POPUP_LINK_NAME' => $_ARRAYLANG['TXT_SHOP_IMAGE'].' '.$index,
                );
                $havePicture = true;
            }
            $i = 1;
            foreach ($arrProductImages as $arrProductImage) {
                $this->objTemplate->setVariable(array(
                    'SHOP_PRODUCT_THUMBNAIL_'.$i => $arrProductImage['THUMBNAIL'],
                ));
                if (!empty($arrProductImage['THUMBNAIL_LINK'])) {
                    $this->objTemplate->setVariable(array(
                        'SHOP_PRODUCT_THUMBNAIL_LINK_'.$i => $arrProductImage['THUMBNAIL_LINK'],
                        'TXT_SEE_LARGE_PICTURE'           => $_ARRAYLANG['TXT_SEE_LARGE_PICTURE'],
                    ));
                } else {
                    $this->objTemplate->setVariable(array(
                        'TXT_SEE_LARGE_PICTURE' => $objProduct->getName(),
                    ));
                }
/*
                if ($this->objTemplate->blockExists('productImage_'.$i)) {
                    $this->objTemplate->parse('productImage_'.$i);
                }
*/

                if ($arrProductImage['POPUP_LINK']) {
                    $this->objTemplate->setVariable(
                        'SHOP_PRODUCT_POPUP_LINK_'.$i, $arrProductImage['POPUP_LINK']
                    );
                }
                $this->objTemplate->setVariable(
                    'SHOP_PRODUCT_POPUP_LINK_NAME_'.$i, $arrProductImage['POPUP_LINK_NAME']
                );
                ++$i;
            }

            $stock = ($objProduct->isStockVisible()
                ? $_ARRAYLANG['TXT_STOCK'].': '.
                  intval($objProduct->getStock())
                : ''
            );

            $manufacturerName = '';
            $manufacturerUrl  = '';
            $manufacturerId   = $objProduct->getManufacturerId();
            if ($manufacturerId) {
                $manufacturerName =
                    $this->_getManufacturerName($manufacturerId);
                $manufacturerUrl  =
                    $this->_getManufacturerUrl($manufacturerId);
                if ($manufacturerUrl) {
                    $manufacturerUrl =
                        '<a href="'.$manufacturerUrl.'</a>';
                }
            }

// TODO: This is the old Product field for the Manufacturer URI.
// This is now extended by the Manufacturer table and should thus
// get a new purpose.  As it is product specific, it could be
// renamed and reused as a link to individual Products!
            $manufacturerLink = (strlen($objProduct->getExternalLink())
                ? '<a href="'.$objProduct->getExternalLink().
                  '" title="'.$_ARRAYLANG['TXT_MANUFACTURER_URL'].
                  '" target="_blank">'.
                  $_ARRAYLANG['TXT_MANUFACTURER_URL'].'</a>'
                : ''
            );

            $price = $this->objCurrency->getCurrencyPrice(
                $objProduct->getCustomerPrice($this->objCustomer)
            );
            $discountPrice = $objProduct->getDiscountPrice();
            if ($discountPrice > 0) {
                $price = "<s>$price</s>";
                $discountPrice =
                    $this->objCurrency->getCurrencyPrice($discountPrice);
            }

            $shortDescription = $objProduct->getShortDesc();
            $longDescription  = $objProduct->getDescription();

            $detailLink = false;
            if ($productId == 0 && !empty($longDescription)) {
                $detailLink =
                    '<a href="index.php?section=shop'.MODULE_INDEX.'&amp;cmd=details&amp;productId='.
                    $objProduct->getId().
                    '" title="'.$_ARRAYLANG['TXT_MORE_INFORMATIONS'].'">'.
                    $_ARRAYLANG['TXT_MORE_INFORMATIONS'].'</a>';
            }

            // Check Product flags.
            // Only the meter flag is currently implemented and in use.
            $flagMeter = $objProduct->testFlag('__METER__');

            // Submit button name
            if (isset($_GET['cmd']) && $_GET['cmd'] == 'details'
             && isset($_GET['referer']) && $_GET['referer'] == 'cart') {
                $productSubmitName = "updateProduct[$cartProdId]";
                $productSubmitFunction = $this->productOptions(
                    $objProduct->getId(), $formId, $cartProdId
                );
            } else {
                $productSubmitName = 'addProduct';
                $productSubmitFunction = $this->productOptions(
                    $objProduct->getId(), $formId
                );
            }
            $shopProductFormName = "shopProductForm$formId";

            $this->objTemplate->setVariable(array(
                'SHOP_ROWCLASS'                   => (++$formId % 2 ? 'row2' : 'row1'),
                'SHOP_PRODUCT_ID'                 => $objProduct->getId(),
                'SHOP_PRODUCT_CUSTOM_ID'          => htmlentities($objProduct->getCode(), ENT_QUOTES, CONTREXX_CHARSET),
                'SHOP_PRODUCT_TITLE'              => htmlentities($objProduct->getName(), ENT_QUOTES, CONTREXX_CHARSET),
                'SHOP_PRODUCT_DESCRIPTION'        => $shortDescription,
                'SHOP_PRODUCT_DETAILDESCRIPTION'  => $longDescription,
                'SHOP_MANUFACTURER_NAME'          => $manufacturerName,
                'SHOP_MANUFACTURER_URL'           => $manufacturerUrl,
                'SHOP_MANUFACTURER_LINK'          => $manufacturerLink,
                'SHOP_PRODUCT_FORM_NAME'          => $shopProductFormName,
                'SHOP_PRODUCT_SUBMIT_NAME'        => $productSubmitName,
                'SHOP_PRODUCT_SUBMIT_FUNCTION'    => $productSubmitFunction,
                // Meter flag
                'TXT_SHOP_PRODUCT_COUNT'          =>
                    ($flagMeter
                        ? $_ARRAYLANG['TXT_SHOP_PRODUCT_METER']
                        : $_ARRAYLANG['TXT_SHOP_PRODUCT_COUNT']
                    ),
            ));
            if ($price > 0) {
                $this->objTemplate->setVariable(array(
                    'SHOP_PRODUCT_PRICE'      => $price,
                    'SHOP_PRODUCT_PRICE_UNIT' => $this->aCurrencyUnitName,
                ));
            }
            // Only show the discount price if it's actually in use,
            // avoid an "empty <font> tag" HTML warning
            if ($discountPrice > 0) {
                $this->objTemplate->setVariable(array(
                    'SHOP_PRODUCT_DISCOUNTPRICE'      => $discountPrice,
                    'SHOP_PRODUCT_DISCOUNTPRICE_UNIT' => $this->aCurrencyUnitName,
                ));
            }
            // Special outlet ShopCategory with discounts varying daily.
            // TODO: This should be implemented in a more generic way.
            if ($objProduct->isOutlet()) {
                $this->objTemplate->setVariable(array(
                    'TXT_SHOP_DISCOUNT_TODAY'   =>
                        $_ARRAYLANG['TXT_SHOP_DISCOUNT_TODAY'],
                    'SHOP_DISCOUNT_TODAY'       =>
                        $objProduct->getOutletDiscountRate().'%',
                    'TXT_SHOP_PRICE_TODAY'      =>
                        $_ARRAYLANG['TXT_SHOP_PRICE_TODAY'],
                    'SHOP_PRICE_TODAY'          =>
                        $this->objCurrency->getCurrencyPrice(
                            $objProduct->getDiscountedPrice()
                        ),
                    'SHOP_PRICE_TODAY_UNIT'     =>
                        $this->aCurrencyUnitName,
                ));
            }
            if ($objProduct->isStockVisible) {
                $this->objTemplate->setVariable(array(
                    'SHOP_PRODUCT_STOCK' => $stock,
                ));
            }
            if ($detailLink) {
                $this->objTemplate->setVariable(array(
                    'SHOP_PRODUCT_DETAILLINK' => $detailLink,
                ));
            }
            $shopDistribution = $objProduct->getDistribution();
            $productWeight = '';
            if ($shopDistribution == 'delivery') {
                $productWeight = $objProduct->getWeight();
            }

            // Hide the weight if it is zero or disabled in the configuration
            if (   $productWeight > 0
                && $this->arrConfig['shop_weight_enable']['value']) {
                $this->objTemplate->setVariable(array(
                    'TXT_SHOP_PRODUCT_WEIGHT' => $_ARRAYLANG['TXT_SHOP_PRODUCT_WEIGHT'],
                    'SHOP_PRODUCT_WEIGHT'     => Weight::getWeightString($productWeight),
                ));
            }
            if ($this->objVat->isEnabled()) {
                $this->objTemplate->setVariable(array(
                    'SHOP_PRODUCT_TAX_PREFIX' =>
                        ($this->objVat->isIncluded()
                            ? $_ARRAYLANG['TXT_TAX_PREFIX_INCL']
                            : $_ARRAYLANG['TXT_TAX_PREFIX_EXCL']
                         ),
                    'SHOP_PRODUCT_TAX'        =>
                        $this->objVat->getShort($objProduct->getVatId())
                ));
            }

            // Add flag images for flagged Products
            $strImage = '';
            $strFlags = $objProduct->getFlags();
            $arrVirtual = ShopCategories::getVirtualCategoryNameArray();
            foreach (split(' ', $strFlags) as $strFlag) {
                if (in_array($strFlag, $arrVirtual)) {
                    $strImage .=
                        '<img src="images/content/'.$strFlag.
                        '.jpg" alt="'.$strFlag.'" />';
                }
            }
            if ($strImage) {
                $this->objTemplate->setVariable(
                    'SHOP_PRODUCT_FLAG_IMAGE', $strImage
                );
            }
            $this->objTemplate->parse('shopProductRow');
        }
        return true;
    }


    /**
     * Set up the HTML elements for all the Product Attributes of any Product.
     *
     * The following types of Attributes are supported:
     * 0    Dropdown menu, customers may select no (the default) or one option.
     * 1    Radio buttons, customers need to select one option.
     * 2    Checkboxes, customers may select no, one or several options.
     * 3    Dropdown menu, customers need to select one option.
     * Types 1 and 3 are functionally identical, they only differ by
     * the kind of widget being used.
     * The individual Product Attributes carry a unique ID enabling the
     * JavaScript code contained within the Shop page to verify that
     * all mandatory choices have been made before any Product can
     * be added to the cart.
     * @param   integer     $product_Id     The Product ID
     * @param   string      $formName       The name of the HTML form containing
     *                                      the Product and options
     * @param   integer     $cartProdId     The optional cart Product ID,
     *                                      false if not applicable.
     * @return  string                      The string with the HTML code
     */
    function productOptions($product_Id, $formName, $cartProdId=false)
    {
        global $_ARRAYLANG;

        // check if the product option block is set in the template
        if (   $this->objTemplate->blockExists('shopProductOptionsRow')
            && $this->objTemplate->blockExists('shopProductOptionsValuesRow')) {
            $domId = 0;
            $checkOptionIds = '';

            if ($cartProdId !== false) {
                $product_Id =
                    $_SESSION['shop']['cart']['products'][$cartProdId]['id'];
            }

            // check options
            if (!isset($this->arrProductAttributes[$product_Id])) {
                $this->objTemplate->hideBlock("shopProductOptionsRow");
                $this->objTemplate->hideBlock("shopProductOptionsValuesRow");
            } else {
                foreach ($this->arrProductAttributes[$product_Id]
                            as $optionId => $arrOptionDetails) {
                    $selectValues = '';
                    // create head of option menu/checkbox/radiobutton
                    switch ($arrOptionDetails['type']) {
                      case '0': // Dropdown menu (optional attribute)
                      // There is no hidden input field here, as there is no
                      // mandatory choice, the status need not be verified.
                        $selectValues =
                            '<select name="productOption['.$optionId.
                            ']" id="productOption-'.
                            $product_Id.'-'.$optionId.'-'.$domId.
                            '" style="width:180px;">'."\n".
                            '<option value="0">'.
                            $arrOptionDetails['name'].'&nbsp;'.
                            $_ARRAYLANG['TXT_CHOOSE']."</option>\n";
                        break;
                      case '1': // Radio buttons
                        // The hidden input field carries the name of the
                        // Product Attribute.
                        $selectValues =
                            '<input type="hidden" id="productOption-'.
                            $product_Id.'-'.$optionId.
                            '" value="'.$arrOptionDetails['name'].
                            '" />'."\n";
                        $checkOptionIds .= "$optionId;";
                        break;
                      // No container for checkboxes (2), as there is no
                      // mandatory choice, their status need not be verified.
                      case '3': // Dropdown menu (mandatory attribute)
                        $selectValues =
                            '<input type="hidden" id="productOption-'.
                            $product_Id.'-'.$optionId.
                            '" value="'.$arrOptionDetails['name'].'" />'."\n".
                            '<select name="productOption['.$optionId.
                            ']" id="productOption-'.
                            $product_Id.'-'.$optionId.'-'.$domId.
                            '" style="width:180px;">'."\n".
                            // If there is only one option to choose from,
                            // why bother the customer at all?
                            (count($arrOptionDetails['values']) > 1
                                ? '<option value="0">'.
                                  $arrOptionDetails['name'].'&nbsp;'.
                                  $_ARRAYLANG['TXT_CHOOSE']."</option>\n"
                                : ''
                            );
                        $checkOptionIds .= "$optionId;";
                        break;
                    }

                    $i = 0;
                    foreach ($arrOptionDetails['values'] as $valueId => $arrValues) {
                        $valuePrice = '';
                        $selected   = false;
                        // price prefix
                        if ($arrValues['price'] != '') {
                            if ($arrValues['price'] != '0.00') {
                                $currencyPrice = $this->objCurrency->getCurrencyPrice($arrValues['price']);
                                $valuePrice    = '&nbsp;&nbsp;('.$arrValues['price_prefix'].'&nbsp;'.$currencyPrice.'&nbsp;'.$this->aCurrencyUnitName.')';
                            }
                        }
                        // mark the option value as selected if it was before
                        // and this site was requested from the cart
                        if (   $cartProdId !== false
                            && isset($_SESSION['shop']['cart']['products'][$cartProdId]['options'][$optionId])) {
                            if (in_array($valueId, $_SESSION['shop']['cart']['products'][$cartProdId]['options'][$optionId])) {
                                $selected = true;
                            }
                        }
                        // create option menu/checkbox/radiobutton
                        switch ($arrOptionDetails['type']) {
                          case '0': // Dropdown menu (optional attribute)
                            $selectValues .=
                                '<option value="'.$valueId.'" '.
                                ($selected ? 'selected="selected"' : '').
                                ' >'.$arrValues['value'].$valuePrice.
                                "</option>\n";
                            break;
                          case '1': // Radio buttons
                            $selectValues .=
                                '<input type="radio" name="productOption['.
                                $optionId.']" id="productOption-'.
                                $product_Id.'-'.$optionId.'-'.$domId.
                                '" value="'.$valueId.'"'.
                                ($selected ? ' checked="checked"' : '').
                                ' /><label for="productOption-'.
                                $product_Id.'-'.$optionId.'-'.$domId.
                                '">&nbsp;'.$arrValues['value'].$valuePrice.
                                "</label><br />\n";
                            break;
                          case '2': // Checkboxes
                            $selectValues .=
                                '<input type="checkbox" name="productOption['.
                                $optionId.']['.$i.']" id="productOption-'.
                                $product_Id.'-'.$optionId.'-'.$domId.
                                '" value="'.$valueId.'"'.
                                ($selected ? ' checked="checked"' : '').
                                ' /><label for="productOption-'.
                                $product_Id.'-'.$optionId.'-'.$domId.
                                '">&nbsp;'.$arrValues['value'].$valuePrice.
                                "</label><br />\n";
                            break;
                          case '3': // Dropdown menu (mandatory attribute)
                            $selectValues .=
                                '<option value="'.$valueId.'"'.
                                ($selected ? ' selected="selected"' : '').
                                ' >'.$arrValues['value'].$valuePrice.
                                "</option>\n";
                            break;
                        }
                        $i++;
                        $domId++;
                    }
                    // create foot of option menu/checkbox/radiobutton
                    switch ($arrOptionDetails['type']) {
                        case '0':
                            $selectValues .= "</select><br />\n";
                            break;
                        case '1':
                            $selectValues .= "<br />";
                            break;
                        case '2':
                            $selectValues .= "<br />";
                            break;
                        case '3':
                            $selectValues .= "</select><br />\n";
                            break;
                    }

                    // pre-version 1.1 spelling error fixed
                    // left old spelling for comatibility (obsolete)
                    if ($this->objTemplate->placeholderExists('SHOP_PRODCUT_OPTION')) {
                        $this->objTemplate->setVariable('SHOP_PRODCUT_OPTION', $selectValues);
                    }
                    if ($this->objTemplate->placeholderExists('SHOP_PRODUCT_OPTION')) {
                        $this->objTemplate->setVariable('SHOP_PRODUCT_OPTION', $selectValues);
                    }
                    $this->objTemplate->setVariable(array(
                        'SHOP_PRODUCT_OPTIONS_NAME'  => $arrOptionDetails['name'],
                        'SHOP_PRODUCT_OPTIONS_TITLE' =>
                            '<a href="javascript:{}" onclick="toggleOptions('.
                            $product_Id.')" title="'.
                            $_ARRAYLANG['TXT_OPTIONS'].'">'.
                            $_ARRAYLANG['TXT_OPTIONS']."</a>\n",
                    ));

                    $this->objTemplate->parse('shopProductOptionsValuesRow');
                    //end products options values block
                }
                $this->objTemplate->parse('shopProductOptionsRow');
            }
        }
        return
            "return checkProductOption('shopProductForm$formName', ".
            "$product_Id, '".
            substr($checkOptionIds, 0, strlen($checkOptionIds)-1)."');";
    }


    function discounts()
    {
        global $objDatabase, $_ARRAYLANG, $themesPages;

        ////////////////////////////////////////////
        // Add-on: Shop Headlines in startpage
        ////////////////////////////////////////////
        // $this->objTemplate->setVariable(getShopNews());
        ////////////////////////////////////////////

        $q = "SELECT *
                FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products AS p
                INNER JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_categories AS c USING (catid)
               WHERE p.is_special_offer=1
                 AND p.status=1
                 AND c.catstatus=1
            ORDER BY p.sort_order";

        $objResult = $objDatabase->Execute($q);
        if (!$objResult) {
            $this->errorHandling();
            return false;
        }
        $count = $objResult->RecordCount();
        $i = 1;
        while (!$objResult->EOF) {
            $arrImages = $this->_getShopImagesFromBase64String($objResult->fields['picture']);

            // no product picture available
            if (!$arrImages
             || $arrImages[1]['img'] == ''
             || $arrImages[1]['img'] == $this->noPictureName) {
                $arrThumbnailPath[$i] = $this->_defaultImage;
            } else {
                $arrThumbnailPath[$i] = $this->shopImageWebPath.$arrImages[1]['img'].$this->thumbnailNameSuffix;
            }

            $price = $this->_getProductPrice($objResult->fields['normalprice'], $objResult->fields['resellerprice']);
            $arrPrice[$i] = "<s>".$price."</s>";
            $arrDiscountPrice[$i] = $this->objCurrency->getCurrencyPrice($objResult->fields['discountprice']);

            $arrDetailLink[$i] = "index.php?section=shop".MODULE_INDEX."&amp;cmd=details&amp;productId=".$objResult->fields['id'];
            $arrTitle[$i] = $objResult->fields['title'];
            ++$i;
            $objResult->MoveNext();
        }

        $this->objTemplate->setGlobalVariable(array(
            'TXT_PRICE_NOW'  => $_ARRAYLANG['TXT_PRICE_NOW'],
            'TXT_INSTEAD_OF' => $_ARRAYLANG['TXT_INSTEAD_OF']
        ));

        for ($i=1; $i <= $count; $i = $i+2) {
            if (!empty($arrTitle[$i+1])) {
                $this->objTemplate->setCurrentBlock('shopProductRow2');
                $this->objTemplate->setVariable(array(
                    'SHOP_PRODUCT_TITLE'                => str_replace('"', '&quot;', $arrTitle[$i+1]),
                    'SHOP_PRODUCT_THUMBNAIL'            => $arrThumbnailPath[$i+1],
                    'SHOP_PRODUCT_PRICE'                => $arrPrice[$i+1],
                    'SHOP_PRODUCT_DISCOUNTPRICE'        => $arrDiscountPrice[$i+1],
                    //'SHOP_PRODUCT_PRICE1_2'             => $arrPrice_2[$i+1],
                    //'SHOP_PRODUCT_DISCOUNTPRICE1_2'     => $arrDiscountPrice_2[$i+1],
                    'SHOP_PRODUCT_PRICE_UNIT'           => $this->aCurrencyUnitName,
                    'SHOP_PRODUCT_DISCOUNTPRICE_UNIT'   => $this->aCurrencyUnitName,
                    'SHOP_PRODUCT_DETAILLINK'           => $arrDetailLink[$i+1],
                ));
                $this->objTemplate->parse('shopProductRow2');
            }
            $this->objTemplate->setCurrentBlock('shopProductRow1');
            $this->objTemplate->setVariable(array(
                'SHOP_PRODUCT_TITLE'                => str_replace('"', '&quot;', $arrTitle[$i]),
                'SHOP_PRODUCT_THUMBNAIL'            => $arrThumbnailPath[$i],
                'SHOP_PRODUCT_PRICE'                => $arrPrice[$i],
                'SHOP_PRODUCT_DISCOUNTPRICE'        => $arrDiscountPrice[$i],
                //'SHOP_PRODUCT_PRICE1_2'             => $arrPrice_2[$i],
                //'SHOP_PRODUCT_DISCOUNTPRICE1_2'     => $arrDiscountPrice_2[$i],
                'SHOP_PRODUCT_PRICE_UNIT'           => $this->aCurrencyUnitName,
                'SHOP_PRODUCT_DISCOUNTPRICE_UNIT'   => $this->aCurrencyUnitName,
                'SHOP_PRODUCT_DETAILLINK'           => $arrDetailLink[$i],
            ));
            $this->objTemplate->parse('shopProductRow1');
        }
        return true;
    }


    function showCartInfo()
    {
        global $_ARRAYLANG;

        $cartInfo = '';
        if (isset($_SESSION['shop'])) {
            $cartInfo = $_ARRAYLANG['TXT_EMPTY_SHOPPING_CART'];
            if (isset($_SESSION['shop']['cart']) && $this->calculateItems($_SESSION['shop']['cart'])>0) {
                $cartInfo = $_ARRAYLANG['TXT_SHOPPING_CART'].' '.$this->calculateItems($_SESSION['shop']['cart']).
                            " ".$_ARRAYLANG['TXT_SHOPPING_CART_VALUE'].' '.$this->_calculatePrice($_SESSION['shop']['cart']).
                            " ".$this->aCurrencyUnitName;
                $cartInfo = "<a href=\"index.php?section=shop".MODULE_INDEX."&amp;cmd=cart\" title=\"".$cartInfo."\">$cartInfo</a>";
            }
        }
        return $cartInfo;
    }


    /**
     * Empty the shopping cart
     */
    function destroyCart()
    {
        if (isset($_SESSION['shop'])) {
            unset($_SESSION['shop']);
        }
    }


    function _calculatePrice($cart)
    {
        global $objDatabase;
        $price = 0.00;
        if (is_array($cart['products']) && count($cart['products']) > 0) {
            foreach ($cart['products'] as $arrProduct) {
                $objResult = $objDatabase->SelectLimit("SELECT normalprice,
                                   resellerprice,
                                   discountprice,
                                   is_special_offer
                              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
                             WHERE id = ".$arrProduct['id'], 1);
                if ($objResult !== false && $objResult->RecordCount() == 1) {
                    $item_price = $this->_getProductPrice($objResult->fields['normalprice'], $objResult->fields['resellerprice'], $objResult->fields['discountprice'], $objResult->fields['is_special_offer']);
                    $optionsPrice = !empty($arrProduct['optionPrice']) ? $arrProduct['optionPrice'] : 0;
                    $price +=($item_price+$optionsPrice)*$arrProduct['quantity'];
                }
            }
        }
        return Currency::formatPrice($price);
    }


    /**
     * Returns the actual product price in the active currency, depending on the
     * customer and special offer status.
     *
     * @param   double  $normalPrice        The standard price
     * @param   double  $resellerPrice      The reseller price
     * @param   double  $discountPrice      The discount price
     * @param   boolean $is_special_offer   A flag indicating a special offer (true)
     * @return  double                      The price converted to the active currency
     */
    function _getProductPrice(
        $normalPrice, $resellerPrice='0.00',
        $discountPrice='0.00', $is_special_offer=0)
    {
        if ($is_special_offer == 1 AND $discountPrice != '0.00') {
            $price = $discountPrice;
        }
        else {
            if ($this->is_reseller == 1 AND $resellerPrice != '0.00') {
                $price = $resellerPrice;
            } else {
                $price = $normalPrice;
            }
        }
        return $this->objCurrency->getCurrencyPrice($price);
    }


    /**
     * Returns the shipping cost converted to the active currency.
     *
     * @param   integer $shipperId   The ID of the selected shipper
     * @param   double  $price       The total order value
     * @param   integer $weight      The total order weight in grams
     * @return  double               The shipping cost in the customers' currency
     */
    function _calculateShipmentPrice($shipperId, $price, $weight)
    {
        $shipmentPrice = $this->objShipment->calculateShipmentPrice(
            $shipperId, $price, $weight
        );
        return $this->objCurrency->getCurrencyPrice($shipmentPrice);
    }


    /**
     * Returns the actual payment fee according to the payment ID and
     * the total order price.
     *
     * @todo    A lot of this belongs to the Payment class.
     * @param   integer     $paymentId  The payment ID
     * @param   double      $totalPrice The total order price
     * @return  string                  The payment fee, formatted by
     *                                  {@link Currency::getCurrencyPrice()}
     */
    function _calculatePaymentPrice($paymentId, $totalPrice)
    {
        $paymentPrice = 0;
        if (!$paymentId) return $paymentPrice;
        if ($this->objPayment->arrPaymentObject[$paymentId]['costs_free_sum'] == 0
         || $totalPrice < $this->objPayment->arrPaymentObject[$paymentId]['costs_free_sum']) {
            $paymentPrice = $this->objPayment->arrPaymentObject[$paymentId]['costs'];
        }
        return $this->objCurrency->getCurrencyPrice($paymentPrice);
    }


    /**
     * Returns the total number of items in the cart
     *
     * @param   array   $cart   The cart array
     * @return  integer         Number of items present in the Cart
     * @todo    Move to Cart class.
     */
    function calculateItems($cart)
    {
        $items = 0;
        if (is_array($cart['products'])) {
            foreach ($cart['products'] as $arrProduct) {
                $items += $arrProduct['quantity'];
            }
        }
        return $items;
    }


    /**
     * Returns the JavaScript functions used by the shop
     *
     * @global  array   $_ARRAYLANG         Language array
     * @global  array   $_CONFIGURATION     Core configuration array, see {@link /config/settings.php}
     * @return  string                      string containung the JavaScript functions
     *
     */
    function getJavascriptCode()
    {
        global $_ARRAYLANG, $_CONFIGURATION;
        $javascriptCode =
"<script language=\"JavaScript\" type=\"text/javascript\">
// <![CDATA[
function viewPicture(picture,features)
{
    window.open(picture,'',features);
}

function toggleOptions(productId)
{
    if (document.getElementById('product_options_layer'+productId)) {
        if (document.getElementById('product_options_layer'+productId).style.display == 'none') {
            document.getElementById('product_options_layer'+productId).style.display = 'block';
        } else {
            document.getElementById('product_options_layer'+productId).style.display = 'none';
        }
    }
}

function checkProductOption(objForm, productId, strProductOptionIds)
{
    // The list of Product Attribute IDs, joined by semicolons.
    var arrOptionIds = strProductOptionIds.split(\";\");
    // Assume that the selection is okay
    var status = true;
    // Remember invalid or missing choices in order to prompt the user
    var arrFailOptions = new Array();
    var elType = '';

    // check each option
    for (i = 0; i < arrOptionIds.length; i++) {
        // The name of the Product Attribute currently being processed.
        // Only set for attributes with mandatory choice
        // (types 1 (Radiobutton), and 3 (Dropdown menu).
        optionName = '';
        checkStatus = false;
        // get options from form
        for (el = 0; el < document.forms[objForm].elements.length; el++) {
            // check if the element has a id attribute
            var formElement = document.forms[objForm].elements[el];
            if (formElement.getAttribute('id')) {
                // check whether the element belongs to the option
                var searchName = 'productOption-'+productId+'-'+arrOptionIds[i];
                var elementId = formElement.getAttribute('id');
                if (elementId.substr(0, searchName.length) == searchName) {
                    // check if the element has a type attribute
                    if (formElement.type) {
                        elType = formElement.type;
                        switch (elType) {
                            case 'radio':
                                if (formElement.checked == true) {
                                    checkStatus = true;
                                }
                                break;
                            case 'select-one':
                                if (formElement.value > 0) {
                                    checkStatus = true;
                                }
                                break;
                            case 'hidden':
                                optionName = formElement.value;
                                break;
                        }
                    }
                }
            }
        } // end for
        // If the option name is empty, the Product Attribute is not
        // a mandatory choice.
        if (   optionName != \"\"
            && checkStatus == false
            && (elType == 'radio' || elType == 'select-one')) {
            status = false;
            arrFailOptions.push(optionName);
        }
    } // end for
    if (status == false) {
        msg = \"{$_ARRAYLANG['TXT_MAKE_DECISION_FOR_OPTIONS']}:\\n\";
        for (i = 0;i < arrFailOptions.length;i++) {
            msg += \"- \"+arrFailOptions[i]+\"\\n\";
        }
        document.getElementById('product_options_layer'+productId).style.display = 'block';
        alert(msg);
        return false;
    } else {
        ".
(!$_CONFIGURATION['custom']['shopJsCart']
? "return true;\n}\n}"
: "addProductToCart(objForm);
        return false;
    }
}

function countObjects(obj)
{
    n = 0;
    for (i in obj) {
        n++;
    }
    return n;
}

function addProductToCart(objForm)
{
    objCart = {products:new Array(),info:{}};
    objProduct = {id:0,title:'',options:{},info:{},quantity:0};
    productOptionRe = /productOption\\[([0-9]+)\\]/;
    updateProductRe = /updateProduct\\[([0-9]+)\\]/;
    updateProduct = '';

    // Default to one product in case the quantity field is not used
    objProduct.quantity = 1;
    // get productId
    for (i = 0; i < document.forms[objForm].getElementsByTagName('input').length; i++) {
        formElement = document.forms[objForm].getElementsByTagName('input')[i];
        if (typeof(formElement.name) != 'undefined') {
            if (formElement.name == 'productId') {
                objProduct.id = formElement.value;
            }
            if (formElement.name == 'productTitle') {
                objProduct.title = formElement.value;
            }
            if (formElement.name == 'productQuantity') {
                objProduct.quantity = formElement.value;
            }
            arrUpdateProduct = updateProductRe.exec(formElement.name);
            if (arrUpdateProduct != null) {
                updateProduct = '&updateProduct='+arrUpdateProduct[1];
            }
        }
    }

    // get product options of the new product
    for (el = 0; el < document.forms[objForm].elements.length; el++) {
        var formElement = document.forms[objForm].elements[el];

        arrName = productOptionRe.exec(formElement.getAttribute('name'));
        if (arrName != null) {
            optionId = arrName[1];

            switch (formElement.type) {
                case 'radio':
                    if (formElement.checked == true) {
                        objProduct.options[optionId] = formElement.value;
                    }
                    break;

                case 'checkbox':
                    if (formElement.checked == true) {
                        if (typeof(objProduct.options[optionId]) == 'undefined') {
                            objProduct.options[optionId] = new Array();
                        }
                        objProduct.options[optionId].push(formElement.value);
                    }
                    break;

                case 'select-one':
                    objProduct.options[optionId] = formElement.value;
                    break;

                default:
                    break;
            }
        }
    }

    // generate product string
    arrOptions = new Array();
    for (optionId in objProduct.options) {
        if (typeof(objProduct.options[optionId]) == 'object') {
            arrOptions.push(optionId+'\\:['+objProduct.options[optionId].join(',')+']');
        } else {
            arrOptions.push(optionId+'\\:'+objProduct.options[optionId]+'');
        }
    }

    productStr = '{id:'+objProduct.id+',options:{'+arrOptions.join(',')+'},quantity:'+objProduct.quantity+'}';
    sendReq('&product='+encodeURIComponent(productStr)+updateProduct, 1);
    return false;
}

function getXMLHttpRequestObj()
{
    var objXHR;
    if (window.XMLHttpRequest) {
        objXHR = new XMLHttpRequest();
    } else if (window.ActiveXObject) {
        objXHR = new ActiveXObject('Microsoft.XMLHTTP');
    }
    return objXHR;
}

objHttp = getXMLHttpRequestObj();
var request_active = false;

function sendReq(data, type)
{
    if (request_active) {
        return false;
    } else {
        request_active = true;
    }

    if (type == 1) {
        // add product
        objHttp.open('get', 'index.php?section=shop".MODULE_INDEX."&cmd=cart&remoteJs=addProduct'+data, true);
        objHttp.onreadystatechange = shopUpdateCart;
    } else {//if ..
        //more requests here...
    }
    objHttp.send(null);
}

function shopUpdateCart()
{
    if (objHttp.readyState == 4 && objHttp.status == 200) {
        response = objHttp.responseText;

        try {
            eval('objCart = '+response);

            if (typeof(document.getElementById('shopJsCart')) != 'undefined') {
                shopGenerateCart();
            }
        } catch(e) {}
        request_active = false;
    } else {
        return false;
    }
}

function shopGenerateCart()
{
    cart = '';

    if (countObjects(objCart.products)) {
        for (i = 0; i < objCart.products.length; i++) {
            cartProduct = cartProductsTpl.replace('[[SHOP_JS_PRODUCT_QUANTITY]]', objCart.products[i].quantity);
            cartProduct = cartProduct.replace('[[SHOP_JS_PRODUCT_TITLE]]', objCart.products[i].title+objCart.products[i].options);
            cartProduct = cartProduct.replace('[[SHOP_JS_PRODUCT_PRICE]]', objCart.products[i].price);
            cartProduct = cartProduct.replace('[[SHOP_JS_TOTAL_PRICE_UNIT]]', objCart.unit);
            cartProduct = cartProduct.replace('[[SHOP_JS_PRODUCT_ID]]', objCart.products[i].cart_id);
            cart += cartProduct;
        }

        cart = cartTpl.replace('[[SHOP_JS_CART_PRODUCTS]]', cart);
        cart = cart.replace('[[SHOP_JS_PRDOCUT_COUNT]]', objCart.itemcount);
        cart = cart.replace('[[SHOP_JS_TOTAL_PRICE]]', objCart.totalprice);
        cart = cart.replace('[[SHOP_JS_TOTAL_PRICE_UNIT]]', objCart.unit);

        try {
            if (html2dom.getDOM(cart, 'shopJsCart') !== false) {
                document.getElementById('shopJsCart').innerHTML = '';
                eval(html2dom.result);
            } else {
                throw 'error';
            }
        } catch(e) {
            document.getElementById('shopJsCart').innerHTML = '".$_ARRAYLANG['TXT_SHOP_COULD_NOT_LOAD_CART']."';
        }
    } else {
        document.getElementById('shopJsCart').innerHTML = '".$_ARRAYLANG['TXT_EMPTY_SHOPPING_CART']."';
    }
}

sendReq('', 1);

")."
// ]]>
</script>
";
        return  $javascriptCode;
    }


    function errorHandling()
    {
        global $_ARRAYLANG;
        $this->addMessage($_ARRAYLANG['TXT_DATABASE_QUERY_ERROR']);
    }


    /**
     * Get dropdown menu
     *
     * Gets back a dropdown menu like  <option value='catid'>Catname</option>
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
     * Do shop categories menu
     *
     * @param    integer  $parcat
     * @param    integer  $level
     * @param    integer  $selectedid
     * @return   string   $result
     */
    function doShopCatMenu($parcat=0, $level, $selectedid)
    {
        $strMenu = '';
        $list = $this->arrCategoriesTable[$parcat];
        if (is_array($list)) {
            foreach ($list as $id => $name) {
                $output = str_repeat('&nbsp;', $level*3);
                $name   = htmlentities($name, ENT_QUOTES, CONTREXX_CHARSET);
                $strMenu .=
                    '<option value="'.$id.'"'.
                    ($selectedid == $id ? ' selected="selected"' : '').
                    ">$output$name</option>\n";
// fix: the following line produces infinite loops if parent == child
//                if (isset($this->arrCategoriesTable[$id])) {
                if ( ($id != $parcat) &&
                   (isset($this->arrCategoriesTable[$id])) ) {
                    $strMenu .=
                        $this->doShopCatMenu($id, $level+1, $selectedid);
                }
            }
        }
        return $strMenu;
    }


    /**
     * Authenticate a Customer
     *
     * @global  mixed   $objDatabase    Database object
     * @global  mixed   $sessionObj     Session object
     * @return  boolean                 True if the Customer could be
     *                                  authenticated successfully,
     *                                  false otherwise.
     * @access private
     */
    function _authenticate()
    {
        global $objDatabase, $sessionObj;

        if (   isset($_SESSION['shop']['username'])
            && isset($_SESSION['shop']['password'])) {
            $username = mysql_escape_string($_SESSION['shop']['username']);
            $password = md5(mysql_escape_string($_SESSION['shop']['password']));
            $this->objCustomer = Customer::authenticate($username, $password);
            if ($this->objCustomer) {
                $_SESSION['shop']['email'] = $this->objCustomer->getEmail();
                // update the session information both in the session object
                // and in the database
                 $sessionObj->cmsSessionUserUpdate($this->objCustomer->getId());
                return true;
            }
        }
        if (!empty($sessionObj)) {
            $sessionObj->cmsSessionUserUpdate();
            $sessionObj->cmsSessionStatusUpdate('shop');
        }
        return false;
    }


    /**
     * Set up the page for viewing the cart and progress with shopping.
     *
     * @see _getJsonProduct(), _addProductToCart(), _updateCartProductsQuantity(),
     *      _gotoLoginPage(), _parseCart(), _showCart(), _sendJsonCart()
     */
    function cart()
    {
        $oldCartProdId = null;
        $this->_initCart();

        if (!empty($_GET['remoteJs'])) {
            $arrProduct = $this->_getJsonProduct($oldCartProdId);
        } else {
            $arrProduct = $this->_getPostProduct($oldCartProdId);
        }
        $this->_addProductToCart($arrProduct, $oldCartProdId);
        $this->_updateCartProductsQuantity();
        $arrProducts = $this->_parseCart();
        // *MUST NOT* return if continue is set
        $this->_gotoLoginPage();

        if (empty($_GET['remoteJs'])) {
            $this->_showCart($arrProducts);
        } else {
            $this->_sendJsonCart($arrProducts);
        }
    }


    /**
     * Initialises the cart
     *
     * Doesn't change the cart if it already exists.
     * Also stores the default shipping country ID from the configuration
     * into the session.
     * @see arrConfig
     */
    function _initCart()
    {
        if (!isset($_SESSION['shop']['cart'])) {
            $_SESSION['shop']['cart']['products'] = array();
            $_SESSION['shop']['cart']['items'] = 1;
            $_SESSION['shop']['cart']['total_price'] = "0.00";
        }
        // check countries
        $_SESSION['shop']['countryId2'] = isset($_POST['countryId2']) ? intval($_POST['countryId2']) : $this->arrConfig['country_id']['value'];
    }


    /**
     * Gets a product that has been sent through JSON
     *
     * The parameter $oldCartProdId specifies the product nr of the cart.
     * @param   integer $oldCartProdId      The product no. in the cart.
     * @return  array                       Array of the product
     */
    function _getJsonProduct(&$oldCartProdId)
    {
        if (   empty($_REQUEST['product'])
            || !include_once(ASCMS_LIBRARY_PATH.'/PEAR/Services/JSON.php')) {
            return false;
        } else {
            if (isset($_REQUEST['updateProduct'])) {
                $oldCartProdId = intval($_REQUEST['updateProduct']);
            }
            $objJson = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
            return $objJson->decode($_REQUEST['product']);
        }
    }


    /**
     * Sends the cart through JSON
     *
     * @param array $arrProducts
     * @see aCurrencyUnitName, Services_JSON::encode
     */
    function _sendJsonCart($arrProducts)
    {
        /**
         * @ignore
         */
        if (!include_once(ASCMS_LIBRARY_PATH.'/PEAR/Services/JSON.php')) {
             die('Could not load JSON library');
        } else {
            $arrCart = array(
                'products'   => $arrProducts,
                'totalprice' => $_SESSION['shop']['cart']['total_price'],
                'itemcount'  => $_SESSION['shop']['cart']['items'],
                'unit'       => $this->aCurrencyUnitName
            );
            $objJson = new Services_JSON();
            die ($objJson->encode($arrCart));
        }
    }


    /**
     * Gets a product that has been sent through a POST request
     *
     * The reference parameter $oldCartProdId specifies the product ID of the cart and
     * is changed to the first key found in $_REQUEST['updateProduct'], if any.
     *
     * @param   integer     $oldCartProdId  Cart product ID
     * @return  array                       Product array of the product that has been
     *                                      specified by the productId field in a (POST) request.
     * @todo    Documentation: Be more elaborate about the meaning of $oldCartProdId
     */
    function _getPostProduct(&$oldCartProdId)
    {
        $arrProduct = array();

        if (isset($_REQUEST['updateProduct']) && is_array($_REQUEST['updateProduct'])) {
            $keys = array_keys($_REQUEST['updateProduct']);
            $oldCartProdId = intval($keys[0]);
        }

        if (isset($_REQUEST['productId'])) {
            $arrProduct = array(
                'id'       => intval($_REQUEST['productId']),
                'options'  => (!empty($_POST['productOption'])
                    ? $_POST['productOption'] : array()
                ),
                'quantity' => (!empty($_POST['productQuantity'])
                    ? $_POST['productQuantity'] : 1
                ),
            );
        }
        return $arrProduct;
    }


    function _addProductToCart($arrNewProduct, $oldCartProdId = null)
    {
        if (is_array($arrNewProduct) && isset($arrNewProduct['id'])) {
            // Add new product to cart
            $isNewProduct = true;
            if (count($_SESSION['shop']['cart']['products']) > 0) {
                foreach ($_SESSION['shop']['cart']['products'] as $cartProdId => $arrProduct) {
                    // check if the same product is already in the cart
                    if ($arrProduct['id'] == $arrNewProduct['id'] && (!isset($oldCartProdId) || $oldCartProdId != $cartProdId)) {
                        if (isset($arrNewProduct['options']) && count($arrNewProduct['options'] > 0)) {
                            $arrCartProductOptions = array_keys($arrProduct['options']);
                            $arrProductOptions = array_keys($arrNewProduct['options']);
                            // check for the same options
                            if ($arrCartProductOptions == $arrProductOptions) {
                                // check for the same option values
                                foreach ($arrNewProduct['options'] as $optionId => $valueId) {
                                    if (is_array($arrProduct['options'][$optionId])) {
                                        $arrPostValues = array();
                                        if (is_array($valueId)) {
                                            $arrPostValues = array_values($valueId);
                                        } else {
                                            if ($valueId != 0) {
                                                array_push($arrPostValues, $valueId);
                                            }
                                        }

                                        if ($arrPostValues != $arrProduct['options'][$optionId]) {
                                            continue 2;
                                        }
                                    } else {
                                        if (!isset($arrProduct['options'][$optionId][$valueId])) {
                                            continue 2;
                                        }
                                    }
                                }
                                $isNewProduct = false;
                                break;
                            }
                        } elseif (count($arrProduct['options']) == 0) {
                            $isNewProduct = false;
                            break;
                        }
                    }
                }
            }

            if ($isNewProduct) {
                if (isset($oldCartProdId)) {
                    $cartProdId = $oldCartProdId;
                } else {
                    // $arrNewProduct['id'] may be undefined!
                    $arrProduct = array(
                        'id' => $arrNewProduct['id'],
                        'quantity' => $arrNewProduct['quantity']
                    );
                    array_push($_SESSION['shop']['cart']['products'], $arrProduct);
                    $arrKeys = array_keys($_SESSION['shop']['cart']['products']);
                    $cartProdId = $arrKeys[count($arrKeys)-1];
                }
            } else {
                if (isset($oldCartProdId)) {
                    if ($oldCartProdId != $cartProdId) {
                        $_SESSION['shop']['cart']['products'][$cartProdId]['quantity'] +=
                            $_SESSION['shop']['cart']['products'][$oldCartProdId]['quantity'];
                        unset($_SESSION['shop']['cart']['products'][$oldCartProdId]);
                    }
                } else {
                    $_SESSION['shop']['cart']['products'][$cartProdId]['quantity'] +=
                        $arrNewProduct['quantity'];
                }
            }

            //options array
            $_SESSION['shop']['cart']['products'][$cartProdId]['options'] = array();
            if (isset($arrNewProduct['options']) && count($arrNewProduct['options']) > 0) {
                foreach ($arrNewProduct['options'] as $optionId => $valueId) {
                    if (!isset($_SESSION['shop']['cart']['products'][$cartProdId]['options'][$optionId])) {
                        $_SESSION['shop']['cart']['products'][$cartProdId]['options'][intval($optionId)] = array();
                    }

                    if (is_array($valueId) && count($valueId) != 0) {
                        foreach ($valueId as $id) {
                            array_push($_SESSION['shop']['cart']['products'][$cartProdId]['options'][intval($optionId)], intval($id));
                        }
                    } elseif (!empty($valueId)) {
                        array_push($_SESSION['shop']['cart']['products'][$cartProdId]['options'][intval($optionId)], intval($valueId));
                    }
                }
            }
        }
    }


    function _updateCartProductsQuantity()
    {
        // Update quantity to cart
        if (isset($_POST['update']) || isset($_POST['continue'])) {
            if (is_array($_SESSION['shop']['cart']['products']) && !empty($_SESSION['shop']['cart']['products'])) {
                foreach (array_keys($_SESSION['shop']['cart']['products']) as $cartProdId) {
                    // Remove Products
                    if ($_REQUEST['quantity'][$cartProdId] == 0 && empty($_REQUEST['quantity'][$cartProdId])) {
                        unset($_SESSION['shop']['cart']['products'][$cartProdId]);
                    } else {
                        $_SESSION['shop']['cart']['products'][$cartProdId]['quantity'] = intval($_REQUEST['quantity'][$cartProdId]);
                    }
                }
            }
        }
    }


    function _gotoLoginPage()
    {
        // go to the next step
        if (isset($_POST['continue'])) {
            header("Location: index.php?section=shop".MODULE_INDEX."&cmd=login");
            exit;
        }
    }


    /**
     * Parse cart products
     *
     * Generates an array with all products that are in the cart and returns them
     * in an array.
     * Additionally it also computes the new count of total products in the cart
     * and calculates the total amount.
     * @global  mixed   $objDatabase    Database object
     * @return  array                   Array with all products that are stored in the cart
     */
    function _parseCart()
    {
        global $objDatabase, $_CONFIG;

        $arrProducts      = array();
        $total_price      = 0;
        $total_tax_amount = 0;
        $total_weight     = 0;

        if (is_array($_SESSION['shop']['cart']['products']) && !empty($_SESSION['shop']['cart']['products'])) {
            foreach ($_SESSION['shop']['cart']['products'] as $cartProdId => $arrProduct) {
                $objResult = $objDatabase->Execute("
                    SELECT title, catid, product_id, handler,
                           normalprice, resellerprice, discountprice,
                           is_special_offer, vat_id, weight
                      FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
                     WHERE status=1
                       AND id=".$arrProduct['id']
                );
                if (!$objResult || $objResult->RecordCount() == 0) {
                    unset($_SESSION['shop']['cart']['products'][$cartProdId]);
                    continue;
                }
                $productOptions      = '';
                $productOptionsPrice =  0;
                // get option names
                foreach ($_SESSION['shop']['cart']['products'][$cartProdId]['options'] as $optionId => $arrValueIds) {
                    foreach ($arrValueIds as $valueId) {
                        $productOptions .= ' ['.$this->arrProductAttributes[$arrProduct['id']][$optionId]['values'][$valueId]['value'].'] ';
                        $productOptionsPrice += $this->arrProductAttributes[$arrProduct['id']][$optionId]['values'][$valueId]['price_prefix'] == "+" ? $this->arrProductAttributes[$arrProduct['id']][$optionId]['values'][$valueId]['price'] : -$this->arrProductAttributes[$arrProduct['id']][$optionId]['values'][$valueId]['price'];
                    }
                }
                if ($productOptionsPrice != 0) {
                    $_SESSION['shop']['cart']['products'][$cartProdId]['optionPrice'] = $this->objCurrency->getCurrencyPrice($productOptionsPrice);
                    $priceOptions = $this->objCurrency->getCurrencyPrice($productOptionsPrice);
                } else {
                    $priceOptions = 0;
                }
                $price = $this->_getProductPrice(
                    $objResult->fields['normalprice'],
                    $objResult->fields['resellerprice'],
                    $objResult->fields['discountprice'],
                    $objResult->fields['is_special_offer']
                );
                $quantity = $_SESSION['shop']['cart']['products'][$cartProdId]['quantity'];

                $itemprice  = $price + $priceOptions;
                $price      = $itemprice * $quantity;
                $handler = $objResult->fields['handler'];
                $itemweight =
                  ($handler == 'delivery' ? $objResult->fields['weight'] : 0);
                $weight     = $itemweight * $quantity;
                $tax_rate   = $this->objVat->getRate($objResult->fields['vat_id']);
                // calculate the amount if it's excluded; we'll add it later.
                // if it's included, we don't care.
                // if it's disabled, it's set to zero.
                $tax_amount = $this->objVat->amount($tax_rate, $price);

                $total_price      += $price;
                $total_tax_amount += $tax_amount;
                $total_weight     += $weight;

                array_push($arrProducts, array(
                    'id'             => $arrProduct['id'],
                    'product_id'     => $objResult->fields['product_id'],
                    'cart_id'        => $cartProdId,
                    'title'          => empty($_GET['remoteJs']) ? $objResult->fields['title'] : htmlspecialchars((strtolower($_CONFIG['coreCharacterEncoding']) == 'utf-8' ? $objResult->fields['title'] : utf8_encode($objResult->fields['title']))),
                    'options'        => $productOptions,
                    'price'          => Currency::formatPrice($price),
                    'price_unit'     => $this->aCurrencyUnitName,
                    'quantity'       => $quantity,
                    'itemprice'      => Currency::formatPrice($itemprice),
                    'itemprice_unit' => $this->aCurrencyUnitName,
                    'percent'        => $tax_rate,
                    'tax_amount'     => Currency::formatPrice($tax_amount),
                    'itemweight'     => $itemweight, // in grams!
                    'weight'         => $weight,
                ));
                // require shipment if the distribution type is 'delivery'
                if ($objResult->fields['handler'] == 'delivery') {
                    $_SESSION['shop']['shipment'] = true;
                }
            }
        }
        $_SESSION['shop']['cart']['total_price']      = Currency::formatPrice($total_price);//$this->_calculatePrice($_SESSION['shop']['cart']);
        $_SESSION['shop']['cart']['total_tax_amount'] = Currency::formatPrice($total_tax_amount);
        // Round prices to 5 cents if the currency is CHF (*MUST* for Saferpay)
        if ($this->aCurrencyUnitName == 'CHF') {
            $_SESSION['shop']['cart']['total_price']      = Currency::formatPrice(round(20*$total_price)/20);
            $_SESSION['shop']['cart']['total_tax_amount'] = Currency::formatPrice(round(20*$total_tax_amount)/20);
        }
        $_SESSION['shop']['cart']['items']            = $this->calculateItems($_SESSION['shop']['cart']);
        $_SESSION['shop']['cart']['total_weight']     = $total_weight; // in grams!
        return $arrProducts;
    }


    /**
     * Show parsed cart
     *
     * Generate the shop cart page (?section=shop&cmd=cart).
     *
     * @global  array $_ARRAYLANG   Language array
     * @param   array $arrProducts  Array with all the products taken from the cart,
     *                              see {@link _parseCart()}
     * @see _getCountriesMenu(), HTML_Template_Sigma::setVariable(), HTML_Template_Sigma::parse(), HTML_Template_Sigma::hideBlock()
     */
    function _showCart($arrProducts)
    {
        global $_ARRAYLANG;

        // hide currency navbar
        // $this->_hideCurrencyNavbar=true;

        $i = 0;
        if (count($arrProducts)) {
            foreach ($arrProducts as $arrProduct) {
                // The fields that don't apply have been set to ''
                // (empty string) already -- see _parseCart().
                $this->objTemplate->setVariable(array(
                    'SHOP_PRODUCT_ROW'            => (++$i % 2 ? 'row2' : 'row1'),
                    'SHOP_PRODUCT_ID'             => $arrProduct['id'],
                    'SHOP_PRODUCT_CUSTOM_ID'      => $arrProduct['product_id'],
                    'SHOP_PRODUCT_CART_ID'        => $arrProduct['cart_id'],
                    'SHOP_PRODUCT_TITLE'          => str_replace('"', '&quot;', $arrProduct['title']).'<br />',
                    'SHOP_PRODUCT_OPTIONS'        => $arrProduct['options'],
                    'SHOP_PRODUCT_PRICE'          => $arrProduct['price'],  // items * qty
                    'SHOP_PRODUCT_PRICE_UNIT'     => $arrProduct['price_unit'],
                    'SHOP_PRODUCT_QUANTITY'       => $arrProduct['quantity'],
                    'SHOP_PRODUCT_ITEMPRICE'      => $arrProduct['itemprice'],
                    'SHOP_PRODUCT_ITEMPRICE_UNIT' => $arrProduct['itemprice_unit'],
                ));
                if ($this->arrConfig['shop_weight_enable']['value']) {
                    $this->objTemplate->setVariable(array(
                        'SHOP_PRODUCT_WEIGHT' => Weight::getWeightString($arrProduct['weight']),
                        'TXT_WEIGHT'          => $_ARRAYLANG['TXT_TOTAL_WEIGHT'],
                    ));
                }
                if ($this->objVat->isEnabled()) {
                    $this->objTemplate->setVariable(array(
                        // avoid a lonely '%' percent sign in case 'percent' is unset
                        'SHOP_PRODUCT_TAX_RATE'       =>
                            ($arrProduct['percent']
                                ? Vat::format($arrProduct['percent'])
                                : '-'
                            ),
                        'SHOP_PRODUCT_TAX_AMOUNT'     =>
                            '('.$arrProduct['tax_amount'].'&nbsp;'.
                            $arrProduct['itemprice_unit'].')',
                    ));
                }
                $this->objTemplate->parse("shopCartRow");
            }
        } else {
            $this->objTemplate->hideBlock("shopCart");
        }

        $this->objTemplate->setVariable(array(
            'TXT_PRODUCT_ID'               => $_ARRAYLANG['TXT_ID'],
            'TXT_SHOP_PRODUCT_CUSTOM_ID'   => $_ARRAYLANG['TXT_SHOP_PRODUCT_CUSTOM_ID'],
            'TXT_PRODUCT'                  => $_ARRAYLANG['TXT_PRODUCT'],
            'TXT_UNIT_PRICE'               => $_ARRAYLANG['TXT_UNIT_PRICE'],
            'TXT_QUANTITY'                 => $_ARRAYLANG['TXT_QUANTITY'],
            'TXT_TOTAL'                    => $_ARRAYLANG['TXT_TOTAL'],
            'TXT_INTER_TOTAL'              => $_ARRAYLANG['TXT_INTER_TOTAL'],
            'TXT_SHIP_COUNTRY'             => $_ARRAYLANG['TXT_SHIP_COUNTRY'],
            'TXT_UPDATE'                   => $_ARRAYLANG['TXT_UPDATE'],
            'TXT_NEXT'                     => $_ARRAYLANG['TXT_NEXT'],
            'TXT_EMPTY_CART'               => $_ARRAYLANG['TXT_EMPTY_CART'],
            'TXT_CONTINUE_SHOPPING'        => $_ARRAYLANG['TXT_CONTINUE_SHOPPING'],
            'SHOP_PRODUCT_TOTALITEM'       => $_SESSION['shop']['cart']['items'],
            'SHOP_PRODUCT_TOTALPRICE'      => $_SESSION['shop']['cart']['total_price'],
            'SHOP_PRODUCT_TOTALPRICE_UNIT' => $this->aCurrencyUnitName,
            'SHOP_TOTAL_WEIGHT'            => Weight::getWeightString($_SESSION['shop']['cart']['total_weight']),
        ));
        if ($this->objVat->isEnabled()) {
            $this->objTemplate->setVariable(array(
                'TXT_TAX_PREFIX'               =>
                    ($this->objVat->isIncluded()
                        ? $_ARRAYLANG['TXT_TAX_PREFIX_INCL']
                        : $_ARRAYLANG['TXT_TAX_PREFIX_EXCL']
                    ),
                'SHOP_TOTAL_TAX_AMOUNT'        =>
                    '('.$_SESSION['shop']['cart']['total_tax_amount']
                    .'&nbsp;'.$this->aCurrencyUnitName.')',

            ));
        }
        $this->objTemplate->setVariable(
            'SHOP_COUNTRIES_MENU',
                ($_SESSION['shop']['shipment']
                  ? $this->_getCountriesMenu(
                      'countryId2',
                      $_SESSION['shop']['countryId2'],
                      "document.forms['shopForm'].submit()"
                    )
                  : '-'
                )
        );
    }


    /**
     * Show the login page
     *
     * @global  array   $_ARRAYLANG Language array
     * @see _authenticate(), is_auth(), HTML_Template_Sigma::setVariable()
     */
    function login()
    {
        global $_ARRAYLANG;

        $redirect = (isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : '');
        if ($this->objCustomer) {
            if ($redirect) {
                if ($redirect == 'shop') {
                    header('Location: index.php?section=shop');
                    exit;
                }
            }
            // redirect to the checkout page
            header('Location: index.php?section=shop&cmd=account');
            exit;
        } else {
            $loginUsername = '';
            if (!empty($_REQUEST['username']) && !empty($_REQUEST['password'])) {
                // check authentification
                $_SESSION['shop']['username'] = htmlspecialchars(
                    addslashes(strip_tags($_REQUEST['username'])),
                    ENT_QUOTES, CONTREXX_CHARSET);
                $_SESSION['shop']['password'] =
                    addslashes(strip_tags($_REQUEST['password']));
                $loginUsername = $_SESSION['shop']['username'];
                if ($this->_authenticate()) {
                    $this->login();
                } else {
                    $this->addMessage($_ARRAYLANG['TXT_SHOP_UNKNOWN_CUSTOMER_ACCOUNT']);
                }
            }

            $this->objTemplate->setVariable(array(
                'TXT_SHOP_ACCOUNT_TITLE'             => $_ARRAYLANG['TXT_SHOP_ACCOUNT_TITLE'],
                'TXT_SHOP_ACCOUNT_NEW_CUSTOMER'      => $_ARRAYLANG['TXT_SHOP_ACCOUNT_NEW_CUSTOMER'],
                'TXT_SHOP_ACCOUNT_NOTE'              => $_ARRAYLANG['TXT_SHOP_ACCOUNT_NOTE'],
                'TXT_SHOP_ACCOUNT_EXISTING_CUSTOMER' => $_ARRAYLANG['TXT_SHOP_ACCOUNT_EXISTING_CUSTOMER'],
                'TXT_SHOP_ACCOUNT_LOGIN'             => $_ARRAYLANG['TXT_SHOP_ACCOUNT_LOGIN'],
                'TXT_NEXT'                           => $_ARRAYLANG['TXT_NEXT'],
                'TXT_EMAIL_ADDRESS'                  => $_ARRAYLANG['TXT_EMAIL_ADDRESS'],
                'TXT_PASSWORD'                       => $_ARRAYLANG['TXT_PASSWORD'],
                'SHOP_LOGIN_EMAIL'                   => $loginUsername,
                'SHOP_LOGIN_ACTION'                  => 'index.php?section=shop'.MODULE_INDEX.'&amp;cmd=login',
// TODO: Change the name of this placeholder to SHOP_STATUS and remove this.
                'SHOP_LOGIN_STATUS'                  => $this->statusMessage,
                'SHOP_REDIRECT'                      =>
                    (!empty($redirect) ? "&redirect=$redirect" : ''),
            ));
        }
    }


    function account()
    {
        $status = $this->_checkAccountForm();
        if (empty($status)) $this->_gotoPaymentPage();
        $this->_configAccount();
        $this->_parseAccountDetails();
        $this->_getAccountPage($status);
    }


    function _checkAccountForm()
    {
        global $_ARRAYLANG;

        // initialise variables
        $status = '';

        // check the submission
        if (empty($_POST['prefix']) ||
            empty($_POST['lastname']) ||
            empty($_POST['firstname']) ||
            empty($_POST['address']) ||
            empty($_POST['zip']) ||
            empty($_POST['city']) ||
            empty($_POST['phone']) ||
            empty($_POST['prefix2']) ||
            empty($_POST['lastname2']) ||
            empty($_POST['firstname2']) ||
            empty($_POST['address2']) ||
            empty($_POST['zip2']) ||
            empty($_POST['city2']) ||
            empty($_POST['phone2']) ||
            (empty($_POST['email']) && !$this->objCustomer) ||
            (empty($_POST['password']) && !$this->objCustomer)
        ) {
            $status = $_ARRAYLANG['TXT_FILL_OUT_ALL_REQUIRED_FIELDS'];
        }

        if (!empty($_POST['password']) && !$this->objCustomer) {
            if (strlen(trim($_POST['password'])) < 6) {
                $status .= '<br />'.$_ARRAYLANG['TXT_INVALID_PASSWORD'];
            }
        }

        if (!empty($_POST['email']) && !$this->objCustomer) {
            if (!$this->shopCheckEmail($_POST['email'])) {
                $status .= '<br />'.$_ARRAYLANG['TXT_INVALID_EMAIL_ADDRESS'];
            }
            if (!$this->_checkEmailIntegrity($_POST['email'])) {
                $status .=
                    ($status ? '<br />' : '').
                    $_ARRAYLANG['TXT_EMAIL_USED_BY_OTHER_CUSTOMER'];
            }
        }
        return $status;
    }


    function _gotoPaymentPage()
    {
        global $objDatabase;

        foreach($_POST as $key => $value) {
            $value = contrexx_addslashes(strip_tags(trim($value)));
            $_SESSION['shop'][$key] = htmlspecialchars($value, ENT_QUOTES, CONTREXX_CHARSET);
        }

        if (!isset($_SESSION['shop']['customer_note'])) {
            $_SESSION['shop']['customer_note'] = '';
        }
        if (!isset($_SESSION['shop']['agb'])) {
            $_SESSION['shop']['agb'] = '';
        }

        $query = "SELECT catid FROM ".DBPREFIX."content_navigation AS nav, ".
            DBPREFIX."modules AS modules ".
            "WHERE modules.name='shop' AND nav.module=modules.id ".
            "AND nav.cmd='payment' AND activestatus='1' AND lang=".$this->langId;
        $objResult = $objDatabase->SelectLimit($query, 1);

        if ($objResult) {
            if ($objResult->RecordCount() == 1) {
                header("Location: index.php?section=shop".MODULE_INDEX."&cmd=payment");
            } else {
                header("Location: index.php?section=shop".MODULE_INDEX."&cmd=confirm");
            }
            exit;
        }
    }


    function _configAccount()
    {
        // hide currency navbar
        $this->_hideCurrencyNavbar = true;

        if (isset($_POST) && is_array($_POST)) {
            foreach($_POST as $key => $value) {
                $value = get_magic_quotes_gpc() ? strip_tags(trim($value)) : addslashes(strip_tags(trim($value)));
                $_SESSION['shop'][$key] = htmlspecialchars($value, ENT_QUOTES, CONTREXX_CHARSET);
            }

            if (isset($_POST['equalAddress'])) {
                $_SESSION['shop']['equalAddress'] = "checked='checked'";
            } else {
                if (!isset($_SESSION['shop']['equalAddress'])) {
                    $_SESSION['shop']['equalAddress'] = '';
                }
            }

            if (isset($_POST['countryId'])) {
                $_SESSION['shop']['countryId'] = intval($_POST['countryId']);
            } else {
                if (!isset($_SESSION['shop']['countryId'])) {
                    $_SESSION['shop']['countryId'] = $_SESSION['shop']['countryId2'];
                }
            }
        }
    }


    function _parseAccountDetails()
    {
        global $objDatabase;

        // customer already logged in
        if ($this->objCustomer) {
            $this->objTemplate->setVariable(array(
                'SHOP_ACCOUNT_COMPANY'       => $this->objCustomer->getCompany(),
                'SHOP_ACCOUNT_PREFIX'        => $this->objCustomer->getPrefix(),
                'SHOP_ACCOUNT_LASTNAME'      => $this->objCustomer->getLastname(),
                'SHOP_ACCOUNT_FIRSTNAME'     => $this->objCustomer->getFirstname(),
                'SHOP_ACCOUNT_ADDRESS'       => $this->objCustomer->getAddress(),
                'SHOP_ACCOUNT_ZIP'           => $this->objCustomer->getZip(),
                'SHOP_ACCOUNT_CITY'          => $this->objCustomer->getCity(),
                'SHOP_ACCOUNT_COUNTRY'       =>
                    $this->arrCountries[$this->objCustomer->getCountryId()]['countries_name'],
                'SHOP_ACCOUNT_EMAIL'         => $this->objCustomer->getEmail(),
                'SHOP_ACCOUNT_PHONE'         => $this->objCustomer->getPhone(),
                'SHOP_ACCOUNT_FAX'           => $this->objCustomer->getFax(),
                'SHOP_ACCOUNT_ACTION'        => "?section=shop".MODULE_INDEX."&amp;cmd=payment"
            ));
            $this->objTemplate->hideBlock('account_details');
        } else {
            $this->objTemplate->setVariable(array(
                // the $_SESSION fields may be undefined!
                'SHOP_ACCOUNT_COMPANY'       => (isset($_SESSION['shop']['company'])    ? stripslashes($_SESSION['shop']['company']) : ''),
                'SHOP_ACCOUNT_PREFIX'        => (isset($_SESSION['shop']['prefix'])     ? stripslashes($_SESSION['shop']['prefix']) : ''),
                'SHOP_ACCOUNT_LASTNAME'      => (isset($_SESSION['shop']['lastname'])   ? stripslashes($_SESSION['shop']['lastname']) : ''),
                'SHOP_ACCOUNT_FIRSTNAME'     => (isset($_SESSION['shop']['firstname'])  ? stripslashes($_SESSION['shop']['firstname']) : ''),
                'SHOP_ACCOUNT_ADDRESS'       => (isset($_SESSION['shop']['address'])    ? stripslashes($_SESSION['shop']['address']) : ''),
                'SHOP_ACCOUNT_ZIP'           => (isset($_SESSION['shop']['zip'])        ? stripslashes($_SESSION['shop']['zip']) : ''),
                'SHOP_ACCOUNT_CITY'          => (isset($_SESSION['shop']['city'])       ? stripslashes($_SESSION['shop']['city']) : ''),
                'SHOP_ACCOUNT_COUNTRY'       => $this->_getCountriesMenu('countryId', $_SESSION['shop']['countryId']),
                'SHOP_ACCOUNT_EMAIL'         => (isset($_SESSION['shop']['email'])      ? stripslashes($_SESSION['shop']['email']) : ''),
                'SHOP_ACCOUNT_PHONE'         => (isset($_SESSION['shop']['phone'])      ? stripslashes($_SESSION['shop']['phone']) : ''),
                'SHOP_ACCOUNT_FAX'           => (isset($_SESSION['shop']['fax'])        ? stripslashes($_SESSION['shop']['fax']) : ''),
                'SHOP_ACCOUNT_EQUAL_ADDRESS' => $_SESSION['shop']['equalAddress']
            ));
        }
        if ($_SESSION['shop']['shipment']) {
            $this->objTemplate->setVariable(array(
                'SHOP_ACCOUNT_COMPANY2'      => (isset($_SESSION['shop']['company2'])   ? stripslashes($_SESSION['shop']['company2']) : ''),
                'SHOP_ACCOUNT_PREFIX2'       => (isset($_SESSION['shop']['prefix2'])    ? stripslashes($_SESSION['shop']['prefix2']) : ''),
                'SHOP_ACCOUNT_LASTNAME2'     => (isset($_SESSION['shop']['lastname2'])  ? stripslashes($_SESSION['shop']['lastname2']) : ''),
                'SHOP_ACCOUNT_FIRSTNAME2'    => (isset($_SESSION['shop']['firstname2']) ? stripslashes($_SESSION['shop']['firstname2']) : ''),
                'SHOP_ACCOUNT_ADDRESS2'      => (isset($_SESSION['shop']['address2'])   ? stripslashes($_SESSION['shop']['address2']) : ''),
                'SHOP_ACCOUNT_ZIP2'          => (isset($_SESSION['shop']['zip2'])       ? stripslashes($_SESSION['shop']['zip2']) : ''),
                'SHOP_ACCOUNT_CITY2'         => (isset($_SESSION['shop']['city2'])      ? stripslashes($_SESSION['shop']['city2']) : ''),
                'SHOP_ACCOUNT_COUNTRY2'      => $this->arrCountries[$_SESSION['shop']['countryId2']]['countries_name'],
                'SHOP_ACCOUNT_PHONE2'        => (isset($_SESSION['shop']['phone2'])     ? stripslashes($_SESSION['shop']['phone2']) : ''),
                'SHOP_ACCOUNT_EQUAL_ADDRESS' => $_SESSION['shop']['equalAddress']
            ));
        }
    }


    function _getAccountPage($status)
    {
        global $_ARRAYLANG;

        $this->objTemplate->setVariable(array(
            'TXT_CUSTOMER_ADDRESS'     => $_ARRAYLANG['TXT_CUSTOMER_ADDRESS'],
            'TXT_SHIPPING_ADDRESS'     => $_ARRAYLANG['TXT_SHIPPING_ADDRESS'],
            'TXT_REQUIRED_FIELDS'      => $_ARRAYLANG['TXT_REQUIRED_FIELDS'],
            'TXT_SAME_BILLING_ADDRESS' => $_ARRAYLANG['TXT_SAME_BILLING_ADDRESS'],
            'TXT_COMPANY'              => $_ARRAYLANG['TXT_COMPANY'],
            'TXT_GREETING'             => $_ARRAYLANG['TXT_GREETING'],
            'TXT_SURNAME'              => $_ARRAYLANG['TXT_SURNAME'],
            'TXT_FIRSTNAME'            => $_ARRAYLANG['TXT_FIRSTNAME'],
            'TXT_ADDRESS'              => $_ARRAYLANG['TXT_ADDRESS'],
            'TXT_POSTALE_CODE'         => $_ARRAYLANG['TXT_POSTALE_CODE'],
            'TXT_CITY'                 => $_ARRAYLANG['TXT_CITY'],
            'TXT_COUNTRY'              => $_ARRAYLANG['TXT_COUNTRY'],
            'TXT_PASSWORD'             => $_ARRAYLANG['TXT_PASSWORD'],
            'TXT_EMAIL'                => $_ARRAYLANG['TXT_EMAIL'],
            'TXT_YOUR_ACCOUNT_DETAILS' => $_ARRAYLANG['TXT_YOUR_ACCOUNT_DETAILS'],
            'TXT_PASSWORD_MIN_CHARS'   => $_ARRAYLANG['TXT_PASSWORD_MIN_CHARS'],
            'TXT_NEXT'                 => $_ARRAYLANG['TXT_NEXT'],
            'TXT_RESET'                => $_ARRAYLANG['TXT_RESET'],
            'TXT_PHONE_NUMBER'         => $_ARRAYLANG['TXT_PHONE_NUMBER'],
            'TXT_FAX_NUMBER'           => $_ARRAYLANG['TXT_FAX_NUMBER']
        ));

        $this->objTemplate->setVariable(array(
            'SHOP_ACCOUNT_STATUS' => $status,
            'SHOP_ACCOUNT_ACTION' => "?section=shop".MODULE_INDEX."&amp;cmd=account"
        ));
    }


    /**
     * Set up payment page including dropdown menus for shipment and payment options.
     *
     * @return  void
     * @link    _getShipperMenu
     * @link    _initPaymentDetails
     * @link    _getPaymentMenu
     * @link    _setPaymentTaxes
     * @link    _checkPaymentDetails
     * @link    _getPaymentPage
     */
    function payment()
    {
        // call first, because the _initPaymentDetails method requires the
        // shipmentId which is stored in the session array by this.
        $this->_getShipperMenu();
        $this->_initPaymentDetails();
        $this->_setPaymentTaxes();
        $paymentStatus = $this->_checkPaymentDetails();
        $this->_getPaymentPage($paymentStatus);
    }


    function _initPaymentDetails()
    {
        // Reloading or loading without sessions
        if (!isset($_SESSION['shop']['cart'])) {
            header("Location: index.php?section=shop".MODULE_INDEX."");
            exit;
        }

        // hide currency navbar
        $this->_hideCurrencyNavbar = true;

        $_SESSION['shop']['customer_note'] = isset($_POST['customer_note']) ? htmlspecialchars($_POST['customer_note'], ENT_QUOTES, CONTREXX_CHARSET) : "";
        if (isset($_POST['check'])) {
            $_SESSION['shop']['agb'] = (isset($_POST['agb']) && !empty($_POST['agb'])) ? "checked='checked'" : "";
        }

        // uses default currency
        $_SESSION['shop']['total_price'] = $this->_calculatePrice($_SESSION['shop']['cart']);
        // if shipperId is not set, there is no use in trying to determine a shipment_price
        if (isset($_SESSION['shop']['shipperId'])) {
            $shipmentPrice = $this->_calculateShipmentPrice(
                $_SESSION['shop']['shipperId'],
                $_SESSION['shop']['cart']['total_price'],
                $_SESSION['shop']['cart']['total_weight']
            );
            // anything wrong with this kind of shipping?
            if ($shipmentPrice == -1) {
                unset($_SESSION['shop']['shipperId']);
                $_SESSION['shop']['shipment_price'] = '0.00';
            } else {
                $_SESSION['shop']['shipment_price'] = $shipmentPrice;
            }
        } else {
            $_SESSION['shop']['shipment_price'] = '0.00';
        }

        $_SESSION['shop']['items'] = $this->calculateItems($_SESSION['shop']['cart']);

        if (isset($_POST['paymentId'])) {
            $_SESSION['shop']['paymentId'] = intval($_POST['paymentId']);
        }

        // $_SESSION['shop']['paymentId'] may still be unset!
        // determine any valid value for it
        if (!isset($_SESSION['shop']['paymentId'])) {
            $arrPaymentId = $this->objPayment->getCountriesRelatedPaymentIdArray($_SESSION['shop']['countryId'], $this->objCurrency->arrCurrency);
            $_SESSION['shop']['paymentId'] = next($arrPaymentId);
        }

        if ($this->objPayment->arrPaymentObject[$_SESSION['shop']['paymentId']]['processor_id'] != 2) {
            if (isset($_SESSION['shop']['currencyIdPrev'])) {
                $_SESSION['shop']['currencyId'] = $_SESSION['shop']['currencyIdPrev'];
                unset($_SESSION['shop']['currencyIdPrev']);
                header('Location: index.php?section=shop'.MODULE_INDEX.'&cmd=payment');
                exit;
            }
        } else {
            require_once ASCMS_MODULE_PATH."/shop/payments/paypal/Paypal.class.php";
            $objPaypal = new PayPal;
            if (!in_array($this->objCurrency->getActiveCurrencyCode(), $objPaypal->arrAcceptedCurrencyCodes)) {
                foreach ($this->objCurrency->arrCurrency as $arrCurrency) {
                    if ($arrCurrency['status'] && $arrCurrency['code'] == $this->arrConfig['paypal_default_currency']['value']) {
                        $_SESSION['shop']['currencyIdPrev'] = $_SESSION['shop']['currencyId'];
                        $_SESSION['shop']['currencyId'] = $arrCurrency['id'];
                        header('Location: index.php?section=shop'.MODULE_INDEX.'&cmd=payment');
                        exit;
                    }
                }
            }
        }
        $_SESSION['shop']['payment_price'] = $this->_calculatePaymentPrice($_SESSION['shop']['paymentId'], $_SESSION['shop']['total_price']);
    }


    /**
     * Determines the shipper ID to be used, if any, stores it in
     * $_SESSION['shop']['shipperId'], and returns the shipment dropdown menu.
     * If no shipping is desired, returns an empty string.
     *
     * - If $_SESSION['shop']['shipment'] evaluates to true:
     *   - If $_SESSION['shop']['shipperId'] is set, it is changed to the value
     *     of the shipment ID returned in $_POST['shipperId'], if the latter is set.
     *   - Otherwise, sets $_SESSION['shop']['shipperId'] to the default value
     *     obtained by calling {@see Shipment::getCountriesRelatedShippingIdArray()}
     *     with the country ID found in $_SESSION['shop']['countryId'].
     *   - Returns the shipment dropdown menu as returned by
     *     {@see Shipment::getShipmentMenu()}.
     * - If $_SESSION['shop']['shipment'] evaluates to false, does nothing, but simply
     *   returns an empty string.
     * @return  string  Shipment dropdown menu, or an empty string
     */
    function _getShipperMenu()
    {
        if (empty($_SESSION['shop']['shipment'])) {
            return '';
        }
        // get shipment stuff
        $arrShipmentId = $this->objShipment->getCountriesRelatedShippingIdArray($_SESSION['shop']['countryId']);
        if (!isset($_SESSION['shop']['shipperId']) || empty($_SESSION['shop']['shipperId'])) {
            // get default shipment Id
            $_SESSION['shop']['shipperId'] = current($arrShipmentId);
        } else {
            $_SESSION['shop']['shipperId'] = isset($_POST['shipperId']) ? intval($_POST['shipperId']) : $_SESSION['shop']['shipperId'];
        }
        $menu = $this->objShipment->getShipperMenu(
            $_SESSION['shop']['countryId'],
            $_SESSION['shop']['shipperId'],
            "document.forms['shopForm'].submit()"
        );
        return $menu;
    }


    /**
     * Determines the payment ID to be used, if any, stores it in
     * $_SESSION['shop']['paymentId'], and returns the payment dropdown menu.
     * If there is nothing to pay (products or shipping), returns an empty string.
     *
     * - If $_SESSION['shop']['shipment'] evaluates to true, or $_SESSION['shop']['total_price']
     *   is greater than zero:
     *   - If $_SESSION['shop']['paymentId'] is set, it is changed to the value
     *     of the paymentId ID returned in $_POST['paymentId'], if the latter is set.
     *   - Otherwise, sets $_SESSION['shop']['paymentId'] to the first value
     *     found in $arrPaymentId {@see Payment::getCountriesRelatedPaymentIdArray()}
     *     with the country ID found in $_SESSION['shop']['countryId'].
     *   - Returns the payment dropdown menu as returned by
     *     {@see Payment::getPaymentMenu()}.
     * - If $_SESSION['shop']['shipment'] evaluates to false, and the order price is zero,
     *   does nothing, but simply returns an empty string.
     * @return  string  Payment dropdown menu, or an empty string
     */
    function _getPaymentMenu()
    {
        if ($_SESSION['shop']['shipment'] || $_SESSION['shop']['total_price'] > 0) {
            // get payment stuff
            $arrPaymentId = $this->objPayment->getCountriesRelatedPaymentIdArray($_SESSION['shop']['countryId'], $this->objCurrency->arrCurrency);
            if (isset($_SESSION['shop']['paymentId'])) {
                $_SESSION['shop']['paymentId'] = isset($_POST['paymentId']) ? intval($_POST['paymentId']) : $_SESSION['shop']['paymentId'];
            } else {
                // get default payment Id
                $_SESSION['shop']['paymentId'] = next($arrPaymentId);
            }
            return $this->objPayment->getPaymentMenu(
                $_SESSION['shop']['paymentId'],
                "document.forms['shopForm'].submit()",
                $_SESSION['shop']['countryId'],
                $this->objCurrency->arrCurrency
            );
        } else {
            return '';
        }
    }


    /**
     * Set up price and VAT related information for payment page.
     *
     * Depending on the VAT settings, sets fields in the global $_SESSION['shop']
     * array variable, namely  'grand_total_price', 'tax_price', 'tax_products_txt',
     * 'tax_grand_txt', and 'tax_procentual'.
     */
    function _setPaymentTaxes()
    {
        global $_ARRAYLANG;

        // are taxes enabled?
        if ($this->objVat->isEnabled()) {
            // taxes are included
            if ($this->objVat->isIncluded()) {
                // home country equals shop country; tax is included already
                if ($_SESSION['shop']['countryId'] == intval($this->arrConfig['country_id']['value'])) {

                    $_SESSION['shop']['tax_price'] = $_SESSION['shop']['cart']['total_tax_amount'];
                    $_SESSION['shop']['grand_total_price']  = Currency::formatPrice(
                        $_SESSION['shop']['total_price']    +
                        $_SESSION['shop']['payment_price']  +
                        $_SESSION['shop']['shipment_price']
                    );
                    $_SESSION['shop']['tax_products_txt']   = $_ARRAYLANG['TXT_TAX_INCLUDED'];
                    $_SESSION['shop']['tax_grand_txt']      = $_ARRAYLANG['TXT_TAX_INCLUDED'];
                } else {
                    // foreign country; subtract tax from total price taxes
                    // must use every single orderitem in the cart to calculate the VAT now
                    $_SESSION['shop']['tax_price'] = $_SESSION['shop']['cart']['total_tax_amount'];
                    $_SESSION['shop']['grand_total_price']  = Currency::formatPrice(
                        $_SESSION['shop']['total_price']    +
                        $_SESSION['shop']['payment_price']  +
                        $_SESSION['shop']['shipment_price'] -
                        $_SESSION['shop']['tax_price']
                    );
                    $_SESSION['shop']['tax_products_txt']   = $_ARRAYLANG['TXT_TAX_INCLUDED'];
                    $_SESSION['shop']['tax_grand_txt']      = $_ARRAYLANG['TXT_TAX_EXCLUDED'];
                }
            } else {
                // VAT is excluded
                if ($_SESSION['shop']['countryId'] == intval($this->arrConfig['country_id']['value'])) {
                    // home country equals shop country; add tax.
                    // the VAT on the products has already been calculated and set in the cart.
                    // now we add the default tax to the shipping and payment cost.
                    $_SESSION['shop']['tax_price'] = Currency::formatPrice(
                        $_SESSION['shop']['cart']['total_tax_amount'] +
                        $this->objVat->calculateDefaultTax(
                            $_SESSION['shop']['payment_price'] +
                            $_SESSION['shop']['shipment_price']
                        ));
                    $_SESSION['shop']['grand_total_price'] = Currency::formatPrice(
                        $_SESSION['shop']['total_price']    +
                        $_SESSION['shop']['payment_price']  +
                        $_SESSION['shop']['shipment_price'] +
                        $_SESSION['shop']['tax_price']);
                    $_SESSION['shop']['tax_products_txt']   = $_ARRAYLANG['TXT_TAX_EXCLUDED'];
                    $_SESSION['shop']['tax_grand_txt']      = $_ARRAYLANG['TXT_TAX_INCLUDED'];
                } else {
                    // foreign country; do not add tax
                    $_SESSION['shop']['tax_price']         = "0.00";
                    $_SESSION['shop']['grand_total_price'] = Currency::formatPrice(
                        $_SESSION['shop']['total_price']   +
                        $_SESSION['shop']['payment_price'] +
                        $_SESSION['shop']['shipment_price']
                    );
                    $_SESSION['shop']['tax_products_txt'] = $_ARRAYLANG['TXT_TAX_EXCLUDED'];
                    $_SESSION['shop']['tax_grand_txt']    = $_ARRAYLANG['TXT_TAX_EXCLUDED'];
                }
            }
        } else {
            // tax is disabled
            $_SESSION['shop']['tax_price']         = "0.00";
            $_SESSION['shop']['tax_products_txt']  = '';
            $_SESSION['shop']['tax_grand_txt']     = '';
            $_SESSION['shop']['grand_total_price'] = Currency::formatPrice(
                $_SESSION['shop']['total_price']   +
                $_SESSION['shop']['payment_price'] +
                $_SESSION['shop']['shipment_price']);
        }
    }


    function _checkPaymentDetails()
    {
        global $_ARRAYLANG;

        $status = '';
        $status_LSV = 1;

        // added initializing of the payment processor below
        // in order to determine whether to show the LSV form.
        $processorId = $this->objPayment->arrPaymentObject[$_SESSION['shop']['paymentId']]['processor_id'];
        $processorName = $this->objProcessing->getPaymentProcessorName($processorId);
        if ($processorName == 'Internal_LSV') {
            $status_LSV = $this->showLSV();
        }

        // Process
        if (isset($_POST['check'])) {
            // shipment status is true, if either
            // - no shipment is desired, or
            // - the shipperId is set already (and the shipment conditions were validated)
            $shipmentStatus = ($_SESSION['shop']['shipment']
                ? isset($_SESSION['shop']['shipperId'])
                : true);
            // payment status is true, if either
            // - the total price is zero (or less!?), including tax and shipment, or
            // - the paymentId is set and valid, and the LSV status evaluates to true.
            // luckily, shipping, taxes, and price have been handled in _setPaymentTaxes()
            // above already, so we'll only have to check grand_total_price...!
            $paymentStatus  =
                ($_SESSION['shop']['grand_total_price'] <= 0)
                 ||
                (isset($_SESSION['shop']['paymentId']) && $_SESSION['shop']['paymentId'] && $status_LSV);
            // agb status is true, if either
            // - the agb placeholder does not exist
            // - the agb checkbox has been checked
            $agbStatus = ($this->objTemplate->placeholderExists('SHOP_AGB')
                ? (!empty($_POST['agb']) ? true : false) : true
            );

            if ($agbStatus && $shipmentStatus && $paymentStatus) {
                // everything is set and valid
                header("Location: index.php?section=shop".MODULE_INDEX."&cmd=confirm");
                exit;
            } else {
                // something is missing od invalid
                if (!$shipmentStatus) {
                    // ask the customer to pick a different shipper if the last selected did not work
                    $status  = $_ARRAYLANG['TXT_SHIPPER_NO_GOOD']."<br />";
                }
                if (!$paymentStatus) {
                    $status .= $_ARRAYLANG['TXT_FILL_OUT_ALL_REQUIRED_FIELDS']."<br />";
                }
                if (!$agbStatus) {
                    $status .= $_ARRAYLANG['TXT_ACCEPT_AGB']."<br />";
                }
            }
        }
        return $status;
    }


    /**
     * Set up the LSV page (internal LSV form provider)
     *
     * Currently, this is implemented as a part of the payment page.
     * @global  array   $_ARRAYLANG     Language array
     * @return  boolean                 True if the account information is complete,
     *                                  false otherwise
     */
    function showLSV()
    {
        global $_ARRAYLANG;

        if (isset($_POST['account_holder']) && $_POST['account_holder']
         && isset($_POST['account_bank'])   && $_POST['account_bank']
         && isset($_POST['account_blz'])    && $_POST['account_blz']    )  {
            // accept form content if it's complete (but it's not verified!)
            $_SESSION['shop']['account_holder'] = $_POST['account_holder'];
            $_SESSION['shop']['account_bank']   = $_POST['account_bank'];
            $_SESSION['shop']['account_blz']    = $_POST['account_blz'];
        }

        // set up the page
        $this->objTemplate->setVariable(array(
            'SHOP_ACCOUNT_HOLDER'       => (isset($_SESSION['shop']['account_holder'])
                                            ? $_SESSION['shop']['account_holder'] : ''),
            'SHOP_ACCOUNT_BANK'         => (isset($_SESSION['shop']['account_bank'])
                                            ? $_SESSION['shop']['account_bank']   : ''),
            'SHOP_ACCOUNT_BLZ'          => (isset($_SESSION['shop']['account_blz'])
                                            ? $_SESSION['shop']['account_blz']    : ''),
            'TXT_PAYMENT_LSV'           => $_ARRAYLANG['TXT_PAYMENT_LSV'],
            'TXT_PAYMENT_LSV_NOTE'      => $_ARRAYLANG['TXT_PAYMENT_LSV_NOTE'],
            'TXT_ACCOUNT_HOLDER'        => $_ARRAYLANG['TXT_ACCOUNT_HOLDER'],
            'TXT_ACCOUNT_BANK'          => $_ARRAYLANG['TXT_ACCOUNT_BANK'],
            'TXT_ACCOUNT_BLZ'           => $_ARRAYLANG['TXT_ACCOUNT_BLZ'],
        ));

        if (isset($_SESSION['shop']['account_holder']) && $_SESSION['shop']['account_holder']
         && isset($_SESSION['shop']['account_bank'])   && $_SESSION['shop']['account_bank']
         && isset($_SESSION['shop']['account_blz'])    && $_SESSION['shop']['account_blz']   ) {
            return true;
        }
        return false;
    }


    /**
     * Set up the "einzug" page with the user information form for LSV
     *
     * @todo Fill in the order summary automatically.
     *  Problem: If the order is big enough, it may not fit into the
     *  visible text area, thus causing some order items to be cut off
     *  by printing.  This issue should be resolved by replacing the
     *  <textarea> with a variable height element, such as a table, or
     *  a simple div.
     * @global  mixed   $objDatabase    Database object
     * @global  array   $_ARRAYLANG     Language array
     */
    function einzug()
    {
        global $objDatabase, $_ARRAYLANG;

        $shopAddress = ($this->arrConfig['shop_address']['value']
            ? $this->arrConfig['shop_address']['value']
            : ''
        );
        $shopAddress = preg_replace('/[\012\015]+/', ', ', $shopAddress);

/*
this information should be read and stored in the session
right after the customer logs in!
        // fill in the address for known customers
        if ($this->objCustomer) {
            $_SESSION['shop']['prefix']    = $this->objCustomer->getPrefix();
            $_SESSION['shop']['firstname'] = $this->objCustomer->getFirstName();
            $_SESSION['shop']['lastname']  = $this->objCustomer->getLastName();
            $_SESSION['shop']['address']   = $this->objCustomer->getAddress();
            $_SESSION['shop']['zip']       = $this->objCustomer->getZip();
            $_SESSION['shop']['city']      = $this->objCustomer->getCity();
            $_SESSION['shop']['phone']     = $this->objCustomer->getPhone();
            $_SESSION['shop']['fax']       = $this->objCustomer->getFax();
            $_SESSION['shop']['email']     = $this->objCustomer->getEmail();
        }
*/

        $this->objTemplate->setVariable(array(
            'SHOP_CUSTOMER_PREFIX'     => (isset($_SESSION['shop']['prefix'])    ? stripslashes($_SESSION['shop']['prefix'])    : ''),
            'SHOP_CUSTOMER_FIRST_NAME' => (isset($_SESSION['shop']['firstname']) ? stripslashes($_SESSION['shop']['firstname']) : ''),
            'SHOP_CUSTOMER_LAST_NAME'  => (isset($_SESSION['shop']['lastname'])  ? stripslashes($_SESSION['shop']['lastname'])  : ''),
            'SHOP_CUSTOMER_ADDRESS'    => (isset($_SESSION['shop']['address'])   ? stripslashes($_SESSION['shop']['address'])   : ''),
            'SHOP_CUSTOMER_ZIP'        => (isset($_SESSION['shop']['zip'])       ? stripslashes($_SESSION['shop']['zip'])       : ''),
            'SHOP_CUSTOMER_CITY'       => (isset($_SESSION['shop']['city'])      ? stripslashes($_SESSION['shop']['city'])      : ''),
            'SHOP_CUSTOMER_PHONE'      => (isset($_SESSION['shop']['phone'])     ? stripslashes($_SESSION['shop']['phone'])     : ''),
            'SHOP_CUSTOMER_FAX'        => (isset($_SESSION['shop']['fax'])       ? stripslashes($_SESSION['shop']['fax'])       : ''),
            'SHOP_CUSTOMER_EMAIL'      => (isset($_SESSION['shop']['email'])     ? stripslashes($_SESSION['shop']['email'])     : ''),
            // TODO: automatically insert product list into products field
            //'SHOP_LSV_EE_PRODUCTS'     => '',
        ));

        $this->objTemplate->setVariable(array(
            'SHOP_CUSTOMER_BANK'          =>  (isset($_SESSION['shop']['account_bank']) ? $_SESSION['shop']['account_bank']   : ''),
            'SHOP_CUSTOMER_BANKCODE'      =>  (isset($_SESSION['shop']['account_blz'])  ? $_SESSION['shop']['account_blz']    : ''),
            'SHOP_CUSTOMER_ACCOUNT'       =>  '', // not available
            'SHOP_DATE'                   =>  date("j.n.Y"),
            'TXT_SHOP_LSV_EE_TITLE'       => $_ARRAYLANG['TXT_SHOP_LSV_EE_TITLE'],
            'TXT_SHOP_LSV_EE_INFO'        => $_ARRAYLANG['TXT_SHOP_LSV_EE_INFO'],
            'TXT_SHOP_LSV_EE_ADDRESS'     => $_ARRAYLANG['TXT_SHOP_LSV_EE_ADDRESS'],
            'TXT_SHOP_LSV_EE_FAX'         => $_ARRAYLANG['TXT_SHOP_LSV_EE_FAX'],
            'TXT_SHOP_LSV_EE_TEXT'        => $_ARRAYLANG['TXT_SHOP_LSV_EE_TEXT'],
            'TXT_SHOP_LSV_EE_RECIPIENT'   => $_ARRAYLANG['TXT_SHOP_LSV_EE_RECIPIENT'],
            'TXT_SHOP_LSV_EE_REASON'      => $_ARRAYLANG['TXT_SHOP_LSV_EE_REASON'],
            'TXT_SHOP_LSV_EE_DESCRIPTION' => $_ARRAYLANG['TXT_SHOP_LSV_EE_DESCRIPTION'],
            'TXT_SHOP_LSV_EE_CUSTOMER'    => $_ARRAYLANG['TXT_SHOP_LSV_EE_CUSTOMER'],
            'TXT_SHOP_LSV_EE_FIRST_NAME'  => $_ARRAYLANG['TXT_SHOP_LSV_EE_FIRST_NAME'],
            'TXT_SHOP_LSV_EE_LAST_NAME'   => $_ARRAYLANG['TXT_SHOP_LSV_EE_LAST_NAME'],
            'TXT_SHOP_LSV_EE_STREETNO'    => $_ARRAYLANG['TXT_SHOP_LSV_EE_STREETNO'],
            'TXT_SHOP_LSV_EE_ZIP'         => $_ARRAYLANG['TXT_SHOP_LSV_EE_ZIP'],
            'TXT_SHOP_LSV_EE_CITY'        => $_ARRAYLANG['TXT_SHOP_LSV_EE_CITY'],
            'TXT_SHOP_LSV_EE_PHONE'       => $_ARRAYLANG['TXT_SHOP_LSV_EE_PHONE'],
            'TXT_SHOP_LSV_EE_FAXNO'       => $_ARRAYLANG['TXT_SHOP_LSV_EE_FAXNO'],
            'TXT_SHOP_LSV_EE_EMAIL'       => $_ARRAYLANG['TXT_SHOP_LSV_EE_EMAIL'],
            'TXT_SHOP_LSV_EE_BANK'        => $_ARRAYLANG['TXT_SHOP_LSV_EE_BANK'],
            'TXT_SHOP_LSV_EE_BANKCODE'    => $_ARRAYLANG['TXT_SHOP_LSV_EE_BANKCODE'],
            'TXT_SHOP_LSV_EE_ACCOUNT'     => $_ARRAYLANG['TXT_SHOP_LSV_EE_ACCOUNT'],
            'TXT_SHOP_LSV_EE_DATE'        => $_ARRAYLANG['TXT_SHOP_LSV_EE_DATE'],
            'TXT_SHOP_LSV_EE_SIGNATURE'   => $_ARRAYLANG['TXT_SHOP_LSV_EE_SIGNATURE'],
            'TXT_SHOP_LSV_EE_PRINT'       => $_ARRAYLANG['TXT_SHOP_LSV_EE_PRINT'],
            'SHOP_FAX'                    => $this->arrConfig['fax']['value'],
            'SHOP_COMPANY'                => $this->arrConfig['shop_company']['value'],
            'SHOP_ADDRESS'                => $shopAddress,
        ));
    }


    /**
     * Set up the common fields of the payment page
     *
     * @param   unknown_type    $paymentStatus  Payment status
     * @param   unknown_type    $paymentMenu    Payment dropdown menu, {@see _getPaymentMenu()}
     * @param   unknown_type    $shipmentMenu   Shipment dropdown menu, {@see _getShipperMenu()}
     * @return  void
     */
    function _getPaymentPage($paymentStatus)
    {
        global $_ARRAYLANG;

        if ($_SESSION['shop']['cart']['total_weight'] > 0
            && $this->arrConfig['shop_weight_enable']['value']) {
            $this->objTemplate->setVariable(array(
                'TXT_TOTAL_WEIGHT'        => $_ARRAYLANG['TXT_TOTAL_WEIGHT'],
                'SHOP_TOTAL_WEIGHT'       => Weight::getWeightString($_SESSION['shop']['cart']['total_weight']),
            ));
        }

        if ($_SESSION['shop']['shipment']) {
            $this->objTemplate->setVariable(array(
                'SHOP_SHIPMENT_PRICE'     => $_SESSION['shop']['shipment_price'],
                'SHOP_SHIPMENT_MENU'      => $this->_getShipperMenu(),
                'TXT_SHIPPING_METHODS'    => $_ARRAYLANG['TXT_SHIPPING_METHODS'],
            ));
        }

        if (   $_SESSION['shop']['total_price']
            || $_SESSION['shop']['shipment_price']
            || $_SESSION['shop']['tax_price']) {
            $this->objTemplate->setVariable(array(
                'SHOP_PAYMENT_PRICE'      => $_SESSION['shop']['payment_price'],
                'SHOP_PAYMENT_MENU'       => $this->_getPaymentMenu(),
                'TXT_PAYMENT_TYPES'       => $_ARRAYLANG['TXT_PAYMENT_TYPES'],
            ));
        }

        $this->objTemplate->setVariable(array(
            'SHOP_UNIT'               => $this->aCurrencyUnitName,
            'SHOP_TOTALITEM'          => $_SESSION['shop']['items'],
            'SHOP_TOTALPRICE'         => $_SESSION['shop']['total_price'],
            'SHOP_GRAND_TOTAL'        => $_SESSION['shop']['grand_total_price'],
            'SHOP_CUSTOMERNOTE'       => $_SESSION['shop']['customer_note'],
            'SHOP_AGB'                => $_SESSION['shop']['agb'],
            'SHOP_STATUS'             => $paymentStatus,
            'SHOP_ACCOUNT_STATUS'     => $paymentStatus,
            'TXT_PRODUCTS'            => $_ARRAYLANG['TXT_PRODUCTS'],
            'TXT_TOTALLY_GOODS'       => $_ARRAYLANG['TXT_TOTALLY_GOODS'],
            'TXT_PRODUCT_S'           => $_ARRAYLANG['TXT_PRODUCT_S'],
            'TXT_ORDER_SUM'           => $_ARRAYLANG['TXT_ORDER_SUM'],
            'TXT_COMMENTS'            => $_ARRAYLANG['TXT_COMMENTS'],
            'TXT_TAC'                 => $_ARRAYLANG['TXT_TAC'],
            'TXT_ACCEPT_TAC'          => $_ARRAYLANG['TXT_ACCEPT_TAC'],
            'TXT_UPDATE'              => $_ARRAYLANG['TXT_UPDATE'],
            'TXT_NEXT'                => $_ARRAYLANG['TXT_NEXT'],
            'TXT_TOTAL_PRICE'         => $_ARRAYLANG['TXT_TOTAL_PRICE'],
        ));
        if ($this->objVat->isEnabled()) {
            $this->objTemplate->setVariable(array(
                'SHOP_TAX_PRICE'          =>
                    $_SESSION['shop']['tax_price'].
                    '&nbsp;'.$this->aCurrencyUnitName,
                'SHOP_TAX_PRODUCTS_TXT'   => $_SESSION['shop']['tax_products_txt'],
                'SHOP_TAX_GRAND_TXT'      => $_SESSION['shop']['tax_grand_txt'],
                'TXT_TAX_RATE'            => $_ARRAYLANG['TXT_TAX_RATE'],
                'TXT_TAX_PREFIX'          =>
                    ($this->objVat->isIncluded()
                        ? $_ARRAYLANG['TXT_TAX_PREFIX_INCL']
                        : $_ARRAYLANG['TXT_TAX_PREFIX_EXCL']
                    ),
            ));
        }
        // Custom.
        // Enable if Discount class is customized and in use.
        //$this->showCustomerDiscount($_SESSION['shop']['cart']['total_price']);
    }


    /**
     * Generate the final overview of the order for the customer to confirm,
     * after that forward her to the payment provider
     * @access public
     */
    function confirm()
    {
        global $objDatabase, $_ARRAYLANG;

        // if the cart is missing, return to the shop
        if (!isset($_SESSION['shop']['cart'])) {
            header("Location: index.php?section=shop".MODULE_INDEX."");
            exit;
        }
        // hide currency navbar
        $this->_hideCurrencyNavbar = true;

        // the customer clicked the confirm button now;
        // this won't be the case the first time this method is called.
        if (isset($_POST['process'])) {
            // verify that the order hasn't yet been saved
            // (and has thus not yet been confirmed)
            if (isset($_SESSION['shop']['orderId'])) {
                $this->addMessage($_ARRAYLANG['TXT_ORDER_ALREADY_PLACED']);
                return false;
            }

            // no more confirmation
            $this->objTemplate->hideBlock("shopConfirm");
            // store the customer, register the order
            $customer_ip      = htmlspecialchars($_SERVER['REMOTE_ADDR'], ENT_QUOTES, CONTREXX_CHARSET);
            $customer_host    = substr(htmlspecialchars(@gethostbyaddr($_SERVER['REMOTE_ADDR']), ENT_QUOTES, CONTREXX_CHARSET), 0, 100);
            $customer_lang    = substr(htmlspecialchars(getenv('HTTP_ACCEPT_LANGUAGE'), ENT_QUOTES, CONTREXX_CHARSET), 0, 255);
            $customer_browser = substr(htmlspecialchars(getenv('HTTP_USER_AGENT'), ENT_QUOTES, CONTREXX_CHARSET), 0, 100);
            if (!$this->objCustomer) {
                // new customer
                $this->objCustomer = new Customer(
                    $_SESSION['shop']['prefix'],
                    $_SESSION['shop']['firstname'],
                    $_SESSION['shop']['lastname'],
                    $_SESSION['shop']['company'],
                    $_SESSION['shop']['address'],
                    $_SESSION['shop']['city'],
                    $_SESSION['shop']['zip'],
                    $_SESSION['shop']['countryId'],
                    $_SESSION['shop']['phone'],
                    $_SESSION['shop']['fax']
                );
                $this->objCustomer->setUserName($_SESSION['shop']['email']);
                $this->objCustomer->setEmail($_SESSION['shop']['email']);
                $this->objCustomer->setPassword($_SESSION['shop']['password']);
                $this->objCustomer->setActiveStatus(1);

//todo: this might belong somewhere else
                $_SESSION['shop']['username'] = trim($_SESSION['shop']['email'], " \t");
            } else {
                // update the Customer object from the session array
                // (she may have edited it)
                $this->objCustomer->setPrefix($_SESSION['shop']['prefix']);
                $this->objCustomer->setFirstName($_SESSION['shop']['firstname']);
                $this->objCustomer->setLastName($_SESSION['shop']['lastname']);
                $this->objCustomer->setCompany($_SESSION['shop']['company']);
                $this->objCustomer->setAddress($_SESSION['shop']['address']);
                $this->objCustomer->setCity($_SESSION['shop']['city']);
                $this->objCustomer->setZip($_SESSION['shop']['zip']);
                $this->objCustomer->setCountryId($_SESSION['shop']['countryId']);
                $this->objCustomer->setPhone($_SESSION['shop']['phone']);
                $this->objCustomer->setFax($_SESSION['shop']['fax']);
            }
// todo: this information definitely belongs to the order object!
            $this->objCustomer->setCcNumber(isset($_SESSION['shop']['ccnumber']) ? $_SESSION['shop']['ccnumber'] : '');
            $this->objCustomer->setCcDate  (isset($_SESSION['shop']['ccdate'])   ? $_SESSION['shop']['ccdate']   : '');
            $this->objCustomer->setCcName  (isset($_SESSION['shop']['ccname'])   ? $_SESSION['shop']['ccname']   : '');
            $this->objCustomer->setCcCode  (isset($_SESSION['shop']['cvcCode'])  ? $_SESSION['shop']['cvcCode']  : '');
            // insert or update the customer
            $this->objCustomer->store();

// todo: is this really needed?
            $_SESSION['shop']['customerid'] = $this->objCustomer->getId();

            // Add to order table
            $query = "
                INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_orders (
                    customerid, selected_currency_id, currency_order_sum,
                    order_date, order_status, ship_company, ship_prefix,
                    ship_firstname, ship_lastname, ship_address, ship_city,
                    ship_zip, ship_country_id, ship_phone, currency_ship_price,
                    shipping_id, payment_id, currency_payment_price,
                    customer_ip, customer_host, customer_lang,
                    customer_browser, customer_note
                ) VALUES (
                ".$this->objCustomer->getId().",
                '{$_SESSION['shop']['currencyId']}',
                '{$_SESSION['shop']['grand_total_price']}',
                NOW(),
                '0',
                '".trim($_SESSION['shop']['company2']," \t")."',
                '".trim($_SESSION['shop']['prefix2']," \t")."',
                '".trim($_SESSION['shop']['firstname2']," \t")."',
                '".trim($_SESSION['shop']['lastname2']," \t")."',
                '".trim($_SESSION['shop']['address2']," \t")."',
                '".trim($_SESSION['shop']['city2']," \t")."',
                '".trim($_SESSION['shop']['zip2']," \t")."',
                '".intval($_SESSION['shop']['countryId2'])."',
                '".trim($_SESSION['shop']['phone2']," \t")."',
                '{$_SESSION['shop']['shipment_price']}', ".
                (   isset($_SESSION['shop']['shipperId'])
                 && $_SESSION['shop']['shipperId']
                    ? $_SESSION['shop']['shipperId']
                    : 0
                ).",
                {$_SESSION['shop']['paymentId']},
                '{$_SESSION['shop']['payment_price']}',
                '$customer_ip',
                '$customer_host',
                '$customer_lang',
                '$customer_browser',
                '{$_SESSION['shop']['customer_note']}')
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                // $orderId is unset!
                $this->addMessage($_ARRAYLANG['TXT_ERROR_STORING_CUSTOMER_DATA']);
                return false;
            }
            $orderid = $objDatabase->Insert_ID();
            $_SESSION['shop']['orderid'] = $orderid;
            // The products will be tested one by one below.
            // If any single one of them requires delivery, this
            // flag will be set to true.
            // This is used to determine the order status at the
            // end of the shopping process.
            $_SESSION['shop']['isDelivery'] = false;

            foreach ($_SESSION['shop']['cart']['products'] as $arrProduct) {
                $query = "
                    SELECT title, normalprice, resellerprice,
                           discountprice, is_special_offer,
                           vat_id, weight, handler
                      FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
                     WHERE status=1 AND id=".$arrProduct['id'];
                $objResult = $objDatabase->Execute($query);
                if (!$objResult) {
                    unset($_SESSION['shop']['orderid']);
                    $this->addMessage($_ARRAYLANG['TXT_ERROR_LOOKING_UP_ORDER']);
                    return false;
                }
                $productId       = $arrProduct['id'];
                $productName     = $objResult->fields['title'];
                $productPrice    =
                    $this->_getProductPrice(
                        $objResult->fields['normalprice'],
                        $objResult->fields['resellerprice'],
                        $objResult->fields['discountprice'],
                        $objResult->fields['is_special_offer']
                    ) +
                    (isset($arrProduct['optionPrice'])
                        ? $arrProduct['optionPrice']
                        : 0
                    );
                $productQuantity = $arrProduct['quantity'];
                $productVatId    = $objResult->fields['vat_id'];
                $productVatRate  = ($productVatId && $this->objVat->getRate($productVatId) ? $this->objVat->getRate($productVatId) : '0.00');
                // Test the distribution method for delivery
                $productDistribution = $objResult->fields['handler'];
                if ($productDistribution == 'delivery') {
                    $_SESSION['shop']['isDelivery'] = true;
                }
                $productWeight   = ($productDistribution == 'delivery'
                    ? $objResult->fields['weight'] : 0); // grams
                if ($productWeight == '') { $productWeight = 0; }
                // Add to order items table
                $query = "
                    INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_order_items (
                        orderid, productid, product_name,
                        price, quantity, vat_percent, weight
                    ) VALUES (
                        $orderid, $productId, '".addslashes($productName)."',
                        '$productPrice', '$productQuantity',
                        '$productVatRate', '$productWeight'
                    )
                ";
                $objResult = $objDatabase->Execute($query);
                if (!$objResult) {
                    unset($_SESSION['shop']['orderid']);
                    $this->addMessage($_ARRAYLANG['TXT_ERROR_INSERTING_ORDER_ITEM']);
                    return false;
                }
                $orderItemsId = $objDatabase->Insert_ID();
                foreach ($arrProduct['options'] as $optionId => $arrValueIds) {
                    foreach ($arrValueIds as $valueId) {
                        // add product attributes to order items attribute table
                        $query = "INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_order_items_attributes ".
                            "SET order_items_id=$orderItemsId, ".
                                "order_id=$orderid, ".
                                "product_id=$productId, ".
                                "product_option_name='".$this->arrProductAttributes[$arrProduct['id']][$optionId]['name']."', ".
                                "product_option_value='".$this->arrProductAttributes[$arrProduct['id']][$optionId]['values'][$valueId]['value']."', ".
                                "product_option_values_price='".$this->objCurrency->getCurrencyPrice($this->arrProductAttributes[$arrProduct['id']][$optionId]['values'][$valueId]['price'])."', ".
                                "price_prefix='".$this->arrProductAttributes[$arrProduct['id']][$optionId]['values'][$valueId]['price_prefix']."'";
                        $objResult = $objDatabase->Execute($query);
                        if (!$objResult) {
                            unset($_SESSION['shop']['orderid']);
                            $this->addMessage($_ARRAYLANG['TXT_ERROR_INSERTING_ORDER_ITEM_ATTRIBUTE']);
                            return false;
                        }
                    }
                }

                // Update Product stock
// TODO: Only decrease the count for non-electronic products
                $query = "
                    UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_products
                       SET stock=stock-$productQuantity
                     WHERE id=$productId
                ";
                $objResult = $objDatabase->Execute($query);
                if (!$objResult) {
                    // The order does not fail because of that!
                    $this->addMessage($_ARRAYLANG['TXT_ERROR_UPDATING_STOCK']); // Fehler: Bestand kann nicht aktualisiert werden"
                }
            } // foreach product in cart

            $processorId = $this->objPayment->arrPaymentObject[$_SESSION['shop']['paymentId']]['processor_id'];
            $processorName = $this->objProcessing->getPaymentProcessorName($processorId);
             // other payment methods
            $objLanguage = new FWLanguage();
            $this->objProcessing->initProcessor(
                $processorId,
                $this->objCurrency->getActiveCurrencyCode(),
                $objLanguage->getLanguageParameter($this->langId, 'lang')
            );

            // if the processor is Internal_LSV, and there is account information,
            // store the information
            if ($processorName == 'Internal_LSV') {
                if (isset($_SESSION['shop']['account_holder']) && $_SESSION['shop']['account_holder']
                 && isset($_SESSION['shop']['account_bank'])   && $_SESSION['shop']['account_bank']
                 && isset($_SESSION['shop']['account_blz'])    && $_SESSION['shop']['account_blz']   ) {
                    $query = "INSERT INTO contrexx_module_shop_lsv ".
                        "(order_id, holder, bank, blz) VALUES (".
                        $orderid.", '".
                        contrexx_addslashes($_SESSION['shop']['account_holder'])."', '".
                        contrexx_addslashes($_SESSION['shop']['account_bank'])."', '".
                        contrexx_addslashes($_SESSION['shop']['account_blz'])."')";
                    $objResult = $objDatabase->Execute($query);
                    if (!$objResult) {
                        unset($_SESSION['shop']['orderid']);
                        $this->addMessage($_ARRAYLANG['TXT_ERROR_INSERTING_ACCOUNT_INFORMATION']);
                    }
                 } else {
                     // failure!
                     unset($_SESSION['shop']['orderid']);
                     $this->addMessage($_ARRAYLANG['TXT_ERROR_ACCOUNT_INFORMATION_NOT_AVAILABLE']);
                 }
            }

            $_SESSION['shop']['orderid_checkin'] = $orderid;
            $strProcessorType =
                $this->objProcessing->getCurrentPaymentProcessorType();

            // Test whether the selected payment method can be
            // considered an instant or deferred one.
            // This is used to set the order status at the end
            // of the shopping process.
            $_SESSION['shop']['isInstantPayment'] = false;
            if ($strProcessorType == 'external') {
                // For the sake of simplicity, all external payment
                // methods are considered to be 'instant'.
                // All currently implemented internal methods require
                // further action from the merchant, and thus are
                // considered to be 'deferred'.
                $_SESSION['shop']['isInstantPayment'] = true;
            }

/* -> *MUST* be updated on success() page, like all other payment methods
            if ($strProcessorType == 'internal') {
                $this->updateOrderStatus($orderid);
            }
*/

            // Show payment processing page.
            // Note that some internal payments are redirected away
            // from this page in checkOut():
            // 'Internal', 'Internal_LSV'
            $this->objTemplate->setVariable(
                'SHOP_PAYMENT_PROCESSING',
                $this->objProcessing->checkOut()
            );

            // for all payment methods showing a form here,
            // clear the order ID.
            // The order may be resubmitted and the payment retried.
            unset($_SESSION['shop']['orderid']);
            // Custom.
            // Enable if Discount class is customized and in use.
            //$this->showCustomerDiscount($_SESSION['shop']['cart']['total_price']);
        } else {
            // Show confirmation page.
            $this->objTemplate->hideBlock("shopProcess");
            $this->objTemplate->setCurrentBlock("shopCartRow");
            foreach ($_SESSION['shop']['cart']['products'] as $arrProduct) {
                $objResult = $objDatabase->Execute("
                    SELECT product_id, title, catid,
                           normalprice, resellerprice,
                           discountprice, is_special_offer,
                           vat_id, weight, handler
                      FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
                     WHERE status=1 AND id=".$arrProduct['id']
                );
                if ($objResult) {
                    $price = $this->_getProductPrice(
                        $objResult->fields['normalprice'],
                        $objResult->fields['resellerprice'],
                        $objResult->fields['discountprice'],
                        $objResult->fields['is_special_offer']
                    );
                    if (isset($arrProduct['optionPrice'])) {
                        $priceOptions = $arrProduct['optionPrice'];
                    } else {
                        $priceOptions = 0;
                    }

                    $productOptions = '';
                    if (is_array($arrProduct['options'])
                     && count($arrProduct['options']) > 0) {
                        $productOptions = '<br /><i>';
                        foreach ($arrProduct['options'] as $optionId => $arrValueIds) {
                            if (count($arrValueIds) > 0) {
                                $productOptions .=
                                    ($productOptions ? '<br />' : '').'-'.
                                    $this->arrProductAttributes[$arrProduct['id']][$optionId]['name'].': ';
                                $productOptionsValues = '';
                                foreach ($arrValueIds as $valueId) {
                                    $productOptionsValues .=
                                        ($productOptionsValues ? '; ' : '').
                                        $this->arrProductAttributes[$arrProduct['id']][$optionId]['values'][$valueId]['value'];
                                }
                                $productOptions .= $productOptionsValues;
                            }
                        }
                        $productOptions .= '</i>';
                    }

                    // Test the distribution method for delivery
                    $productDistribution = $objResult->fields['handler'];
                    $weight = ($productDistribution == 'delivery'
                        ? Weight::getWeightString($objResult->fields['weight'])
                        : '-'
                    );
                    $vatId      = $objResult->fields['vat_id'];
                    $vatRate    = $this->objVat->getRate($vatId);
                    $vatPercent = $this->objVat->getShort($vatId);
                    $vatAmount  = $this->objVat->amount($vatRate, $price+$priceOptions);

                    $this->objTemplate->setVariable(array(
                        'SHOP_PRODUCT_ID'           => $arrProduct['id'],
                        'SHOP_PRODUCT_CUSTOM_ID'    => $objResult->fields['product_id'],
                        'SHOP_PRODUCT_TITLE'        =>
                            str_replace(
                                '"', '&quot;',
                                $objResult->fields['title'].'<br /><i>'.$productOptions).'</i>',
                        'SHOP_PRODUCT_PRICE'        => Currency::formatPrice(($price+$priceOptions)*$arrProduct['quantity']),
                        'SHOP_PRODUCT_QUANTITY'     => $arrProduct['quantity'],
                        'SHOP_PRODUCT_ITEMPRICE'    => Currency::formatPrice($price+$priceOptions),
                        'SHOP_UNIT'                 => $this->aCurrencyUnitName,
                    ));
                    if ($this->arrConfig['shop_weight_enable']['value']) {
                        $this->objTemplate->setVariable(array(
                            'SHOP_PRODUCT_WEIGHT' => $weight,
                            'TXT_WEIGHT'          => $_ARRAYLANG['TXT_WEIGHT'],
                        ));
                    }
                    if ($this->objVat->isEnabled()) {
                        $this->objTemplate->setVariable(array(
                            'SHOP_PRODUCT_TAX_RATE'   => $vatPercent,
                            'SHOP_PRODUCT_TAX_AMOUNT' =>
                                Currency::formatPrice($vatAmount).
                                '&nbsp;'.$this->aCurrencyUnitName,
                        ));
                    }
                    $this->objTemplate->parse("shopCartRow");
                }
            }
            // determine payment method
            $strPayment = $this->objPayment->arrPaymentObject[$_SESSION['shop']['paymentId']]['name'];

            $this->objTemplate->setVariable(array(
                'SHOP_UNIT'             => $this->aCurrencyUnitName,
                'SHOP_TOTALITEM'        => $_SESSION['shop']['items'],
                'SHOP_PAYMENT_PRICE'    => $_SESSION['shop']['payment_price'],
                'SHOP_TOTALPRICE'       => $_SESSION['shop']['total_price'],
                'SHOP_PAYMENT'          => $strPayment,
                'SHOP_GRAND_TOTAL'      => $_SESSION['shop']['grand_total_price'],
                'SHOP_CUSTOMERNOTE'     => $_SESSION['shop']['customer_note'],
                'SHOP_COMPANY'          => stripslashes($_SESSION['shop']['company']),
                'SHOP_PREFIX'           => stripslashes($_SESSION['shop']['prefix']),
                'SHOP_LASTNAME'         => stripslashes($_SESSION['shop']['lastname']),
                'SHOP_FIRSTNAME'        => stripslashes($_SESSION['shop']['firstname']),
                'SHOP_ADDRESS'          => stripslashes($_SESSION['shop']['address']),
                'SHOP_ZIP'              => stripslashes($_SESSION['shop']['zip']),
                'SHOP_CITY'             => stripslashes($_SESSION['shop']['city']),
                'SHOP_COUNTRY'          => $this->arrCountries[$_SESSION['shop']['countryId']]['countries_name'],
                'SHOP_EMAIL'            => stripslashes($_SESSION['shop']['email']),
                'SHOP_PHONE'            => stripslashes($_SESSION['shop']['phone']),
                'SHOP_FAX'              => stripslashes($_SESSION['shop']['fax']),
                'SHOP_COMPANY2'         => stripslashes($_SESSION['shop']['company2']),
                'SHOP_PREFIX2'          => stripslashes($_SESSION['shop']['prefix2']),
                'SHOP_LASTNAME2'        => stripslashes($_SESSION['shop']['lastname2']),
                'SHOP_FIRSTNAME2'       => stripslashes($_SESSION['shop']['firstname2']),
                'SHOP_ADDRESS2'         => stripslashes($_SESSION['shop']['address2']),
                'SHOP_ZIP2'             => stripslashes($_SESSION['shop']['zip2']),
                'SHOP_CITY2'            => stripslashes($_SESSION['shop']['city2']),
                'SHOP_COUNTRY2'         => $this->arrCountries[$_SESSION['shop']['countryId2']]['countries_name'],
                'SHOP_PHONE2'           => stripslashes($_SESSION['shop']['phone2']),
                'TXT_ORDER_INFOS'       => $_ARRAYLANG['TXT_ORDER_INFOS'],
                'TXT_ID'                => $_ARRAYLANG['TXT_ID'],
                'TXT_SHOP_PRODUCT_CUSTOM_ID' => $_ARRAYLANG['TXT_SHOP_PRODUCT_CUSTOM_ID'],
                'TXT_PRODUCT'           => $_ARRAYLANG['TXT_PRODUCT'],
                'TXT_UNIT_PRICE'        => $_ARRAYLANG['TXT_UNIT_PRICE'],
                'TXT_QUANTITY'          => $_ARRAYLANG['TXT_QUANTITY'],
                'TXT_TOTAL'             => $_ARRAYLANG['TXT_TOTAL'],
                'TXT_INTER_TOTAL'       => $_ARRAYLANG['TXT_INTER_TOTAL'],
                'TXT_PRODUCT_S'         => $_ARRAYLANG['TXT_PRODUCT_S'],
                'TXT_PAYMENT_TYPE'      => $_ARRAYLANG['TXT_PAYMENT_TYPE'],
                'TXT_TOTAL_PRICE'       => $_ARRAYLANG['TXT_TOTAL_PRICE'],
                'TXT_ADDRESS_CUSTOMER'  => $_ARRAYLANG['TXT_ADDRESS_CUSTOMER'],
                'TXT_COMMENTS'          => $_ARRAYLANG['TXT_COMMENTS'],
                'TXT_ORDER_NOW'         => $_ARRAYLANG['TXT_ORDER_NOW'],
            ));
            if ($this->objVat->isEnabled()) {
                $this->objTemplate->setVariable(array(
                    'TXT_TAX_RATE'          => $_ARRAYLANG['TXT_TAX_RATE'],
                    'SHOP_TAX_PRICE'        => $_SESSION['shop']['tax_price'],
                    'SHOP_TAX_PRODUCTS_TXT' => $_SESSION['shop']['tax_products_txt'],
                    'SHOP_TAX_GRAND_TXT'    => $_SESSION['shop']['tax_grand_txt'],
                    'TXT_TAX_PREFIX'        =>
                        ($this->objVat->isIncluded()
                            ? $_ARRAYLANG['TXT_TAX_PREFIX_INCL']
                            : $_ARRAYLANG['TXT_TAX_PREFIX_EXCL']
                        ),
               ));
            }
            if (isset($_SESSION['shop']['shipperId']) && $_SESSION['shop']['shipperId']) {
                $this->objTemplate->setVariable(array(
                    'SHOP_SHIPMENT_PRICE'   => $_SESSION['shop']['shipment_price'],
                    'SHOP_SHIPMENT' =>
                        $this->objShipment->getShipperName($_SESSION['shop']['shipperId']),
                    'TXT_SHIPPING_METHOD'   => $_ARRAYLANG['TXT_SHIPPING_METHOD'],
                    'TXT_SHIPPING_ADDRESS'  => $_ARRAYLANG['TXT_SHIPPING_ADDRESS'],
                ));
            }
            // Custom.
            // Enable if Discount class is customized and in use.
            //$this->showCustomerDiscount($_SESSION['shop']['cart']['total_price']);
        } // end if process
        return true;
    }


    /**
     * The payment process has completed successfully.
     *
     * Check the data from the payment provider.
     * @access public
     */
    function success()
    {
        global $_ARRAYLANG;

        // The payment result is mandatory.
        // If it's missing, the order is cancelled.
        $result =
            (isset($_GET['result'])
                ? $_GET['result'] : SHOP_PAYMENT_RESULT_CANCEL
            );

        // The payment handler
        $handler = (isset($_GET['handler']) ? $_GET['handler'] : '');
        // The handler parameter is mandatory!
        // If it's missing, the order is cancelled.
        // Also, this method *MUST NOT* be called by the PayPal IPN handler.
        if (   $handler == ''
            || $handler == 'PaypalIPN') {
            $result = SHOP_PAYMENT_RESULT_CANCEL;
        }

        // Hide the currency navbar
        $this->_hideCurrencyNavbar = true;

        $orderId = $this->objProcessing->checkIn();
        // True is returned for internal payment types only.
        if ($orderId === true) {
            if (empty($_SESSION['shop']['orderid_checkin'])) {
                $orderId = 0;
            } else {
                // Internal payment method: update the status in any case.
                $orderId = $_SESSION['shop']['orderid_checkin'];
                $result = SHOP_PAYMENT_RESULT_SUCCESS;
            }
        }
        if (!$orderId
           || (   isset($_SESSION['shop']['orderid_checkin'])
               && $_SESSION['shop']['orderid_checkin'] != $orderId)) {
            // Zero or false:
            // The payment failed miserably or was faked.
            // The order is cancelled in both cases.
            // If both IDs are set but differ, the request might be
            // fake.  Cancel the order with the ID from the session!
            $result = SHOP_PAYMENT_RESULT_CANCEL;
            if (!empty($_SESSION['shop']['orderid_checkin'])) {
                $orderId = $_SESSION['shop']['orderid_checkin'];
            } else {
                $orderId = 0;
            }
        }

        // We need a valid order ID from here.
        // The respective order state, if available, is updated
        // in updateOrderStatus().
        if (intval($orderId) > 0) {
            // Default new order status: As long as it's pending (0, zero),
            // updateOrderStatus() will choose the new value automatically.
            $newOrderStatus = SHOP_ORDER_STATUS_PENDING;
            if (   $result == SHOP_PAYMENT_RESULT_FAIL
                || $result == SHOP_PAYMENT_RESULT_CANCEL) {
                // Cancel the order both if the payment failed or if it
                // has been cancelled.
                $newOrderStatus = SHOP_ORDER_STATUS_CANCELLED;
            }
            $newOrderStatus = $this->updateOrderStatus($orderId, $newOrderStatus, $handler);
            switch ($newOrderStatus) {
                case SHOP_ORDER_STATUS_CONFIRMED:
                case SHOP_ORDER_STATUS_PAID:
                case SHOP_ORDER_STATUS_SHIPPED:
                case SHOP_ORDER_STATUS_COMPLETED:
                    $this->addMessage($_ARRAYLANG['TXT_ORDER_PROCESSED']);
                    // Custom.
                    // Enable if Discount class is customized and in use.
                    //$this->showCustomerDiscount($_SESSION['shop']['cart']['total_price']);
                    break;
                case SHOP_ORDER_STATUS_PENDING:
                    // Pending orders must be stated as such.
                    // Certain payment methods (like PayPal with IPN) might
                    // be confirmed a little later and must cause the
                    // confirmation mail to be sent.
                    $this->addMessage(
                        $_ARRAYLANG['TXT_SHOP_ORDER_PENDING'].'<br /><br />'.
                        $_ARRAYLANG['TXT_SHOP_ORDER_WILL_BE_CONFIRMED']
                    );
                    break;
                case SHOP_ORDER_STATUS_DELETED:
                case SHOP_ORDER_STATUS_CANCELLED:
                    $this->addMessage(
                        $_ARRAYLANG['TXT_SHOP_PAYMENT_FAILED'].'<br /><br />'.
                        $_ARRAYLANG['TXT_SHOP_ORDER_CANCELLED']
                    );
                    break;
            }
        } else {
            $this->addMessage($_ARRAYLANG['TXT_NO_PENDING_ORDER']);
        }
        $this->destroyCart();
        // clear backup ID, avoid success() from being run again
        unset($_SESSION['shop']['orderid_checkin']);
        // Avoid any output if the result is negative
        if ($result < 0) {
            die('');
        }
    }


    /**
     * Mark the current order as confirmed, paid, shipped, cancelled, deleted,
     * or completed.
     *
     * If the order exists and has the pending status (order_status == 0),
     * it is updated according to the payment and distribution type.
     * If the optional argument $newOrderStatus is set and not zero,
     * the order status is set to that value instead.
     * If either the order ID is invalid, or if the update fails, this method
     * returns zero.
     * @access  private
     * @static
     * @param   integer $orderId    The ID of the current order
     * @param   integer $newOrderStatus The optional new order status.
     * @return  integer             The new order status (may be zero)
     *                              if the order status can be changed
     *                              accordingly, zero otherwise
     */
    //static
    function updateOrderStatus($orderId, $newOrderStatus=0, $handler='')
    {
        global $objDatabase;

        if ($handler == '') {
            return SHOP_ORDER_STATUS_CANCELLED;
        }
        $orderId = intval($orderId);
        if ($orderId == 0) {
            return SHOP_ORDER_STATUS_CANCELLED;
        }
        $query = "
            SELECT order_status, payment_id, shipping_id
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders
             WHERE orderid=$orderId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return SHOP_ORDER_STATUS_CANCELLED;
        }
        $orderStatus = $objResult->fields['order_status'];

        // Never change a non-pending status!
        // Whether a payment was successful or not, the status must be
        // left alone.
        if ($orderStatus != SHOP_ORDER_STATUS_PENDING) {
            // The status of the order is not pending.
            // This may be due to a wrong order ID, a page reload,
            // or a PayPal IPN that has been received already.
            // No order status is changed automatically in these cases!
            // Leave it as it is.
            return $orderStatus;
        }

        $paymentId = $objResult->fields['payment_id'];
        $processorId = Payment::getPaymentProcessorId($paymentId);
        $processorName = PaymentProcessing::getPaymentProcessorName($processorId);
        // The payment processor *MUST* match the handler
        // returned.  In the case of PayPal, the order status is only
        // updated if this method is called by Paypal::ipnCheck() with the
        // 'PaypalIPN' handler argument or if the new order status is
        // set to force the order to be cancelled.
        if ($processorName == 'Paypal') {
            if (   $handler != 'PaypalIPN'
                && $newOrderStatus != SHOP_ORDER_STATUS_CANCELLED
            ) {
                return $orderStatus;
            }
        } elseif (!preg_match("/^$handler/i", $processorName)) {
            return SHOP_ORDER_STATUS_CANCELLED;
        }

        // Only if the optional new order status argument is zero,
        // determine the new status automatically.
        if ($newOrderStatus == SHOP_ORDER_STATUS_PENDING) {
            // The new order status is determined by two properties:
            // - The method of payment (instant/deferred), and
            // - The method of delivery (if any).
            // If the payment takes place instantly (currently, all
            // external payments processors are considered to do so),
            // and there is no delivery needed (because it's all
            // downloads), the order status is switched to 'completed'
            // right away.
            // If only one of these conditions is met, the status is set to
            // 'paid', or 'delivered' respectively.
            // If neither condition is met, the status is set to 'confirmed'.
            $newOrderStatus = SHOP_ORDER_STATUS_CONFIRMED;
            $paymentId = $objResult->fields['payment_id'];
            $processorId = Payment::getPaymentProcessorId($paymentId);
            $processorType = PaymentProcessing::getCurrentPaymentProcessorType($processorId);
            $shippingId = $objResult->fields['shipping_id'];
            if ($processorType == 'external') {
                // External payment types are considered instant.
                // See $_SESSION['shop']['isInstantPayment'].
                if ($shippingId == 0) {
                    // instant, download -> completed
                    $newOrderStatus = SHOP_ORDER_STATUS_COMPLETED;
                } else {
                    // There is a shipper, so this order will bedelivered.
                    // See $_SESSION['shop']['isDelivery'].
                    // instant, delivery -> paid
                    $newOrderStatus = SHOP_ORDER_STATUS_PAID;
                }
            } else {
                // Internal payment types are considered deferred.
                if ($shippingId == 0) {
                    // deferred, download -> shipped
                    $newOrderStatus = SHOP_ORDER_STATUS_SHIPPED;
                }
                //else { deferred, delivery -> confirmed }
            }
        }

        $query = "
            UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_orders
               SET order_status='$newOrderStatus'
             WHERE orderid=$orderId
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
            if (   $newOrderStatus == SHOP_ORDER_STATUS_CONFIRMED
                || $newOrderStatus == SHOP_ORDER_STATUS_PAID
                || $newOrderStatus == SHOP_ORDER_STATUS_SHIPPED
                || $newOrderStatus == SHOP_ORDER_STATUS_COMPLETED) {
                Shop::_sendProcessedMail($orderId);
// TODO: Implement a way to show this when the template is available
//                $this->addMessage('<br />'.$_ARRAYLANG['TXT_SHOP_UNABLE_TO_SEND_EMAIL']);
                }
                // The shopping cart *MUST* be flushed right after this method
                // returns a true value (greater than zero).
                // If the new order status is zero however, the cart may
                // be left alone and the payment process can be tried again.
                return $newOrderStatus;
            }
        // The query failed, but all the data is okay.
        // Don't cancel the order, leave it as it is and let the shop
        // manager handle this.  Return pending status.
        return SHOP_ORDER_STATUS_PENDING;
    }


    /**
     * Send a confirmation e-mail with the order data
     * @static
     * @param   integer   $orderId    The order ID
     * @return  boolean               True on success, false otherwise
     * @access  private
     */
    // static
    function _sendProcessedMail($orderId)
    {
        global $objDatabase;

        // Determine the customer language ID
        $query = "
            SELECT email, customer_lang
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
        $customerLang = $objResult->fields['customer_lang'];
        $langId = FWLanguage::getLangIdByIso639_1($customerLang);
        // Select template for order confirmation
        $arrShopMailtemplate = Shop::shopSetMailtemplate(1, $langId);
        $mailTo = $objResult->fields['email'];
        $mailFrom = $arrShopMailtemplate['mail_from'];
        $mailFromText = $arrShopMailtemplate['mail_x_sender'];
        $mailSubject = $arrShopMailtemplate['mail_subject'];
        $mailBody = $arrShopMailtemplate['mail_body'];
        $today = date('d.m.Y');
        $mailSubject = str_replace('<DATE>', $today, $mailSubject);
        $mailBody = Shop::_generateEmailBody($mailBody, $orderId, $langId);
        $return = true;
        if (!Shop::shopSendmail($mailTo, $mailFrom, $mailFromText, $mailSubject, $mailBody)) {
            $return = false;
        }
        // Get mail address(es) for confirmation mails
        $query = "
            SELECT value
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_config
             WHERE name='confirmation_emails'
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult && !$objResult->EOF) {
            $copies = explode(',', trim($objResult->fields['value']));
            foreach($copies as $sendTo) {
                Shop::shopSendMail($sendTo, $mailFrom, $mailFromText, $mailSubject, $mailBody);
            }
        }
        return $return;
    }


    /**
     * Replace all placeholders in the e-mail template with the
     * respective values from the order specified by the order ID.
     *
     * Note that this method is now independent of the current session.
     * The language of the mail template is determined by the browser
     * language range stored with the order.
     * Note: The password is no longer available in the session
     * if the confirmation is sent after paying with PayPal!
     * In that case, it is replaced by asterisks in the confirmation mail.
     * @access  private
     * @static
     * @param   string  $body         The e-mail template
     * @param   integer $orderId      The order ID
     * @param   integer $langId       The language ID
     * @return  string                The e-mail body
     */
    //static
    function _generateEmailBody($body, $orderId, $langId)
    {
        global $objDatabase, $_ARRAYLANG, $objDatabase;

        $today     = date(ASCMS_DATE_SHORT_FORMAT);
        $orderTime = date(ASCMS_DATE_FORMAT);
        $cartTxt   = '';
        $taxTxt    = '';

        $loginData =
            $_ARRAYLANG['TXT_SHOP_URI_FOR_DOWNLOAD'].":\n".
            'http://'.$_SERVER['SERVER_NAME'].
            "/index.php?section=download\n";
        $orderIdCustom = ShopLibrary::getCustomOrderId($orderId, date('Y'));

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
        // Determine the Currency code
        $strCurrencyCode = Currency::getCodeById(
            $objResultOrder->fields['selected_currency_id']
        );
        if (!$strCurrencyCode) {
            return false;
        }

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

        // Pick the order items from the database
        // order items: order_items_id, orderid, productid, product_name,
        //              price, quantity, vat_percent, weight
        $query = "
            SELECT order_items_id, productid, product_name, price, quantity
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items
             WHERE orderid=$orderId
        ";
        $objResultItem = $objDatabase->Execute($query);
        if (!$objResultItem || $objResultItem->RecordCount() == 0) {
            // Order not found
            return false;
        }

        $orderItemCount = 0;
        $priceTotalItems = 0;
        while (!$objResultItem->EOF) {
            $orderItemId = $objResultItem->fields['order_items_id'];
            $productId = $objResultItem->fields['productid'];
            $orderItemName = substr($objResultItem->fields['product_name'], 0, 40);
            $orderItemPrice = $objResultItem->fields['price'];
            $orderItemQuantity = $objResultItem->fields['quantity'];
// TODO:      $orderItemVatPercent = $objResultItem->fields['vat_percent'];

            // Pick missing Product data
            $query = "
               SELECT product_id, handler, usergroups, weight
                 FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products
                WHERE id=$productId
            ";
            $objResultProduct = $objDatabase->Execute($query);
            if (!$objResultProduct || $objResultProduct->RecordCount() == 0) {
                $objResultItem->MoveNext();
                continue;
            }
            $productCode = $objResultProduct->fields['product_id'];
//            $productHandler = $objResultProduct->fields['handler'];
//            $productUserGroupId = $objResultProduct->fields['usergroups'];
//            $productWeight = $objResultProduct->fields['weight'];

            // Pick the order items attributes from the database
            $query = "
                SELECT product_option_name, product_option_value
                  FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items_attributes
                 WHERE order_items_id=$orderItemId
                 ORDER BY product_option_name ASC
            ";
            $objResultAttribute = $objDatabase->Execute($query);
            $productOptions = '';
            // Any attributes?
            if ($objResultAttribute && $objResultAttribute->RecordCount() > 0) {
                $productOptions = ' (';
                $optionNamePrevious = '';
                while (!$objResultAttribute->EOF) {
                    $optionName = $objResultAttribute->fields['product_option_name'];
                    $optionValue = $objResultAttribute->fields['product_option_value'];
                    if ($optionName != $optionNamePrevious) {
                        if ($optionNamePrevious) {
                            $productOptions .= '; ';
                        }
                        $productOptions .= $optionName.': '.$optionValue;
                        $optionNamePrevious = $optionName;
                    } else {
                        $productOptions .= ', '.$optionValue;
                    }
                    $objResultAttribute->MoveNext();
                }
                $productOptions .= ')';
            }

            // Product details
            $cartTxt .=
                $productId.' | '.$productCode.' | '.
                $orderItemName.$productOptions.' | '.
                $orderItemPrice.' '.$strCurrencyCode.' | '.
                $orderItemQuantity.' | '.
                Currency::formatPrice(
                    $orderItemPrice*$orderItemQuantity
                ).' '.
                $strCurrencyCode."\n";
            $orderItemCount += $orderItemQuantity;
            $priceTotalItems += $orderItemPrice*$orderItemQuantity;

            // Add an account for every single instance of every Product
            for ($instance = 1; $instance <= $orderItemQuantity; ++$instance) {
                $validity = 0; // Default to unlimited validity
                // In case there are protected downloads in the cart,
                // collect the group IDs
                $arrUsergroupId = array();
                if ($objResultProduct->fields['handler'] == 'download') {
                    $usergroupIds = $objResultProduct->fields['usergroups'];
                    if ($usergroupIds != '') {
                        $arrUsergroupId = explode(',', $usergroupIds);
                        $validity = $objResultProduct->fields['weight'];
                    }
                }
                // create an account that belongs to all collected
                // user groups, if any.
                if (count($arrUsergroupId) > 0) {
                    // Replace the mail template body.
                    // This one includes the <LOGIN_DATA> placeholder for
                    // the user name and password for the download.
                    $arrTemplate = self::shopSetMailtemplate(4, $langId);
                    $body = $arrTemplate['mail_body'];
                    // The login names are created from the order ID,
                    // with product ID and instance number appended.
                    $userpass = uniqid();

                    $objUser = new User();
                    $objUser->setUsername("$orderIdCustom-$productId-$instance");
                    $objUser->setPassword($userpass);
                    $objUser->setEmail($objResultOrder->fields['email']);
                    $objUser->setAdminStatus(false);
                    $objUser->setActiveStatus(true);
                    $objUser->setGroups($arrUsergroupId);
                    $objUser->setValidityTimePeriod($validity);
                    $objUser->setFrontendLanguage($this->langId);
                    $objUser->setBackendLanguage($this->langId);
                    $objUser->setProfile(array(
                        'firstname'        => $objResultOrder->fields['firstname'],
                        'lastname'        => $objResultOrder->fields['lastname'],
                        'company'        => $objResultOrder->fields['company'],
                        'address'        => $objResultOrder->fields['address'],
                        'zip'            => $objResultOrder->fields['zip'],
                        'city'            => $objResultOrder->fields['city'],
                        'country'        => $objResultOrder->fields['country_id'],
                        'phone_office'    => $objResultOrder->fields['phone'],
                        'phone_fax'        => $objResultOrder->fields['fax']
                    ));

                    if (!$objUser->store()) {
                        $this->statusMessage .= implode('<br />', $objUser->getErrorMsg());
                        return false;
                    } else {
                       $loginData .=
                          $_ARRAYLANG['TXT_SHOP_LOGINNAME'].": ".htmlentities($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET)."\n".
                          $_ARRAYLANG['TXT_PASSWORD'].": $userpass".
                          "\n\n";
                    }
                }
            }
            $objResultItem->MoveNext();
        }

        $taxTxt = '';
        $objVat = new Vat();
        if ($objVat->isEnabled()) {
            // taxes are enabled
            $taxTxt = ($objVat->isIncluded()
                ? $_ARRAYLANG['TXT_TAX_INCLUDED']
                : $_ARRAYLANG['TXT_TAX_EXCLUDED']
            ).' '.
            Currency::formatPrice(
                $objResultOrder->fields['tax_price']
            ).' '.$strCurrencyCode;
        }
        $priceTotalItems =
            Currency::formatPrice($priceTotalItems).' '.$strCurrencyCode;
        $orderSum =
            $objResultOrder->fields['currency_order_sum'].' '.
            $strCurrencyCode;

        $shipperId =
            (isset($_SESSION['shop']['shipperId'])
                ? $_SESSION['shop']['shipperId']
                : 0
            );
        $shipperName =
            ($shipperId > 0
                ? $this->objShipment->getShipperName($shipperId)
                : ''
            );
        $paymentId =
            (isset($_SESSION['shop']['paymentId'])
                ? $_SESSION['shop']['paymentId']
                : 0
            );
        $paymentName =
            (isset($this->objPayment->arrPaymentObject[$paymentId])
                ? $this->objPayment->arrPaymentObject[$paymentId]['name']
                : ''
            );
        $orderData =
"-----------------------------------------------------------------\n".
$_ARRAYLANG['TXT_ORDER_INFOS']."\n".
"-----------------------------------------------------------------\n".
$_ARRAYLANG['TXT_ID'].' | '.
$_ARRAYLANG['TXT_SHOP_PRODUCT_CUSTOM_ID'].' | '.
$_ARRAYLANG['TXT_PRODUCT'].' | '.
$_ARRAYLANG['TXT_UNIT_PRICE'].' | '.
$_ARRAYLANG['TXT_QUANTITY'].' | '.
$_ARRAYLANG['TXT_TOTAL']."\n".
"-----------------------------------------------------------------\n".
$cartTxt.
"-----------------------------------------------------------------\n".
$_ARRAYLANG['TXT_INTER_TOTAL'].': '.$orderItemCount.' '.
$_ARRAYLANG['TXT_PRODUCT_S'].' '.
Currency::formatPrice($priceTotalItems).' '.
$strCurrencyCode."\n".
"-----------------------------------------------------------------\n".
$_ARRAYLANG['TXT_PAYMENT_TYPE'].': '.$paymentName.' '.
$_ARRAYLANG['TXT_SHIPPING_METHOD'].': '.
$shipperName.' '.
Currency::formatPrice($objResultOrder->fields['currency_ship_price']).' '.
$strCurrencyCode."\n".
$_ARRAYLANG['TXT_PAYMENT_TYPE'].': '.
$paymentName.' '.
Currency::formatPrice($objResultOrder->fields['currency_payment_price']).' '.
$strCurrencyCode."\n".
$taxTxt."\n".
"-----------------------------------------------------------------\n".
$_ARRAYLANG['TXT_TOTAL_PRICE'].': '.
$orderSum."\n".
"-----------------------------------------------------------------\n";

        $search  = array (
            '<ORDER_ID>', '<DATE>',
            '<USERNAME>', '<PASSWORD>',
            '<ORDER_DATA>', '<ORDER_SUM>', '<ORDER_TIME>', '<REMARKS>',
            '<CUSTOMER_ID>', '<CUSTOMER_EMAIL>',
            '<CUSTOMER_COMPANY>', '<CUSTOMER_PREFIX>', '<CUSTOMER_FIRSTNAME>',
            '<CUSTOMER_LASTNAME>', '<CUSTOMER_ADDRESS>', '<CUSTOMER_ZIP>',
            '<CUSTOMER_CITY>', '<CUSTOMER_COUNTRY>', '<CUSTOMER_PHONE>',
            '<CUSTOMER_FAX>',
            '<SHIPPING_COMPANY>', '<SHIPPING_PREFIX>', '<SHIPPING_FIRSTNAME>',
            '<SHIPPING_LASTNAME>', '<SHIPPING_ADDRESS>', '<SHIPPING_ZIP>',
            '<SHIPPING_CITY>', '<SHIPPING_COUNTRY>', '<SHIPPING_PHONE>',
            '<LOGIN_DATA>',
        );
        $replace = array (
            $orderId, $today,
            $objResultOrder->fields['username'],
            (isset($_SESSION['shop']['password'])
                ? $_SESSION['shop']['password'] : '******'),
            $orderData, $orderSum, $orderTime, $objResultOrder->fields['customer_note'],
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
            ($loginData ? $_ARRAYLANG['TXT_SHOP_LOGINDATA']."\n\n".$loginData : ''),
        );
        $body = str_replace($search, $replace, $body);
        // Strip CRs
        $body = str_replace("\r", '', $body); //echo("made mail body:<br />".str_replace("\n", '<br />', htmlentities($body))."<br />");
        return $body;
    }


    /**
     * Get the Manufacturer dropdown menu HTML code string.
     *
     * Used in the Product search form, see {@link products()}.
     *
     * Create the table like this:
     * CREATE TABLE `contrexx_module_shop_manufacturer` (
     *   `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
     *   `name` VARCHAR(255) NOT NULL,
     *   `url` VARCHAR(255) NOT NULL
     * ) ENGINE = MYISAM
     *
     * @static
     * @param   integer $selectedId     The optional preselected Manufacturer ID
     * @return  string                  The Manufacturer dropdown menu HTML code
     * @global  mixed   $objDatabase    Database object
     * @global  array   $_ARRAYLANG     Language array
     * @todo    Move this to the Manufacturer class!
     */
    //static
    function _getManufacturerMenu($selectedId=0)
    {
        global $objDatabase, $_ARRAYLANG;

        $query = '
            SELECT id, name, url
              FROM '.DBPREFIX.'module_shop_manufacturer
          ORDER BY name';
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            $this->errorHandling();
        }

        $strMenu = '';
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $strMenu .=
                '<option value="'.$objResult->fields['id'].'"'.
                ($selectedId == $id ? ' selected="selected"' : '').
                '>'.$objResult->fields['name'].'</option>';
            $objResult->MoveNext();
        }

        return ($strMenu
            ? '<select name="manufacturerId" style="width: 180px;">'.
              '<option value="0">'.$_ARRAYLANG['TXT_ALL_MANUFACTURER'].
              '</option>'.$strMenu.'</select>'
            : ''
        );
    }


    /**
     * Returns the name of the Manufacturer with the given ID
     *
     * @static
     * @param   integer $id             The Manufacturer ID
     * @return  string                  The Manufacturer name on success,
     *                                  or the empty string on failure
     * @global  mixed   $objDatabase    Database object
     * @todo    Move this to the Manufacturer class!
     */
    //static
    function _getManufacturerName($id)
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            SELECT name
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_manufacturer
             WHERE id=$id
        ");
        if ($objResult) {
            return $objResult->fields['name'];
        }
        return '';
    }


    /**
     * Returns the URL of the Manufacturers' homepage for the given ID
     *
     * @static
     * @param   integer $id             The Manufacturer ID
     * @return  string                  The Manufacturer URL on success,
     *                                  or the empty string on failure
     * @global  mixed   $objDatabase    Database object
     * @todo    Move this to the Manufacturer class!
     */
    //static
    function _getManufacturerUrl($id)
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            SELECT url
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_manufacturer
             WHERE id=$id
        ");
        if ($objResult) {
            return $objResult->fields['url'];
        }
        return '';
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


    /**
     * Show the total pending order and the resulting discount amount.
     *
     * This method fails if there is no Customer logged in or if there
     * is some weird problem with the Discount class.
     * @param   double  $orderAmount        The amount of the current order
     * @global  array   $_ARRAYLANG         Language array
     * @return  boolean                     True on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function showCustomerDiscount($orderAmount)
    {
        global $_ARRAYLANG;

        if (!$this->objCustomer) {
            return false;
        }
        $objDiscount = new Discount($this->objCustomer->getId(), $orderAmount);
        if (!$objDiscount) {
            return false;
        }
        // Calculate Customer "discount".
        // If this is false or zero, we don't bother to set anything at all.
        $newTotalOrderAmount = $objDiscount->getNewTotalOrderAmount();
        if ($newTotalOrderAmount) {
            $newDiscountAmount = $objDiscount->getNewDiscountAmount();
            $totalOrderAmount = $objDiscount->getTotalOrderAmount();
            $discountAmount = $objDiscount->getDiscountAmount();
            $this->objTemplate->setVariable(array(
                'TXT_SHOP_CUSTOMER_TOTAL_ORDER_AMOUNT' => $_ARRAYLANG['TXT_SHOP_CUSTOMER_TOTAL_ORDER_AMOUNT'],
                'TXT_SHOP_CUSTOMER_DISCOUNT_AMOUNT'    => $_ARRAYLANG['TXT_SHOP_CUSTOMER_DISCOUNT_AMOUNT'],
                'TXT_SHOP_CUSTOMER_NEW_TOTAL_ORDER_AMOUNT' => $_ARRAYLANG['TXT_SHOP_CUSTOMER_NEW_TOTAL_ORDER_AMOUNT'],
                'TXT_SHOP_CUSTOMER_NEW_DISCOUNT_AMOUNT'    => $_ARRAYLANG['TXT_SHOP_CUSTOMER_NEW_DISCOUNT_AMOUNT'],
                'SHOP_CUSTOMER_TOTAL_ORDER_AMOUNT'     => number_format($totalOrderAmount, 2, '.', '').' '.$this->aCurrencyUnitName,
                'SHOP_CUSTOMER_DISCOUNT_AMOUNT'        => number_format($discountAmount, 2, '.', '').' '.$this->aCurrencyUnitName,
                'SHOP_CUSTOMER_NEW_TOTAL_ORDER_AMOUNT' => number_format($newTotalOrderAmount, 2, '.', '').' '.$this->aCurrencyUnitName,
                'SHOP_CUSTOMER_NEW_DISCOUNT_AMOUNT'    => number_format($newDiscountAmount, 2, '.', '').' '.$this->aCurrencyUnitName,
                'TXT_SHOP_CUSTOMER_DISCOUNT_DETAILS'   => $_ARRAYLANG['TXT_SHOP_CUSTOMER_DISCOUNT_DETAILS'],
            ));
        }
        return true;
    }


// noser
    /**
     * Deletes the order with the given ID.
     *
     * Also removes related order items, attributes, the customer, and the
     * user accounts created for the downloads.
     * @param   integer   $orderId        The order ID
     * @return  boolean                   True on success, false otherwise
     * @global  mixed     $objDatabase    Database object
     */
    function deleteOrder($orderId)
    {
        global $objDatabase;

        $query = "
            SELECT customerid, order_date
              FROM ".DBPREFIX."module_shop_orders
             WHERE orderid=$orderId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $customerId = $objResult->fields['customerid'];
        $orderDate = $objResult->fields['order_date'];

        $query = "
            DELETE FROM ".DBPREFIX."module_shop_order_items_attributes
             WHERE order_id=$orderId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }

        $query = "
            DELETE FROM ".DBPREFIX."module_shop_order_items
             WHERE orderid=$orderId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }

        $query = "
            DELETE FROM ".DBPREFIX."module_shop_orders
             WHERE orderid=$orderId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }

        $query = "
            DELETE FROM ".DBPREFIX."module_shop_customers
             WHERE customerid=$customerId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }

        $orderIdCustom = ShopLibrary::getCustomOrderId($orderId, $orderDate);
        $objFWUser = FWUser::getFWUserObject();
        $objUser = $objFWUser->objUser->getUsers(array('username' => $orderIdCustom.'-%'));
        if ($objUser) {
            while (!$objUser->EOF) {
                if (!$objUser->delete()) {
                    return false;
                }
                $objUser->next();
            }
        }
        return true;
    }


    /**
     * Set up the template block with the shipment terms and conditions
     *
     * Please *DO NOT* remove this method, despite the site terms and
     * conditions have been removed from the Shop!
     * This has been requested by some shopkeepers and may be used at will.
     * @global  array   $_ARRAYLANG     Language array
     */
    function showShipmentTerms()
    {
        global $_ARRAYLANG;

        if ($this->objTemplate->blockExists('shopShipper')) {
            $arrShipment = $this->objShipment->getShipmentConditions();
            foreach ($arrShipment as $strShipperName => $arrContent) {
                $strCountries  = join(', ', $arrContent['countries']);
                $arrConditions = $arrContent['conditions'];
                $this->objTemplate->setCurrentBlock('shopShipment');
                foreach ($arrConditions as $arrData) {
                    $this->objTemplate->setVariable(array(
                        'SHOP_MAX_WEIGHT' => $arrData['max_weight'],
                        'SHOP_COST_FREE'  => $arrData['price_free'],
                        'SHOP_COST'       => $arrData['cost'],
                        'SHOP_UNIT'       => $this->aCurrencyUnitName,
                    ));
                    $this->objTemplate->parseCurrentBlock();
                }
                $this->objTemplate->setCurrentBlock('shopShipper');
                $this->objTemplate->setVariable(array(
                    'SHOP_SHIPPER'   => $strShipperName,
                    'SHOP_COUNTRIES' => $strCountries,
                ));
            $this->objTemplate->setVariable(array(
                'TXT_SHOP_SHIPMENT_CONDITIONS' => $_ARRAYLANG['TXT_SHOP_SHIPMENT_CONDITIONS'],
                'TXT_SHIPPING_METHOD'          => $_ARRAYLANG['TXT_SHIPPING_METHOD'],
                'TXT_SHOP_SHIPMENT_COUNTRIES'  => $_ARRAYLANG['TXT_SHOP_SHIPMENT_COUNTRIES'],
                'TXT_SHIPPING_MAX_WEIGHT'      => $_ARRAYLANG['TXT_SHIPPING_MAX_WEIGHT'],
                'TXT_FREE_OF_CHARGE'           => $_ARRAYLANG['TXT_FREE_OF_CHARGE'],
                'TXT_FEE'                      => $_ARRAYLANG['TXT_FEE'],
            ));
            $this->objTemplate->parseCurrentBlock();
            }
        }
    }


}

?>
