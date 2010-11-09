<?php

/**
 * The Shop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @author      Ivan Schmid <ivan.schmid@comvation.com>
 * @access      public
 * @package     contrexx
 * @subpackage  module_shop
 * @version     2.1.0
 */

/**
 * Text objects
 */
require_once ASCMS_CORE_PATH.'/Text.class.php';
/**
 * Country
 */
require_once ASCMS_CORE_PATH.'/Country.class.php';
/**
 * Mail
 */
require_once ASCMS_CORE_PATH.'/Mailtemplate.class.php';
require_once ASCMS_FRAMEWORK_PATH."/Image.class.php";
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
 * Attribute: Object
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Attribute.class.php';
/**
 * Attribute: Various Helpers and display functions
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Attributes.class.php';
/**
 * Discount: Custom calculations for discounts
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Discount.class.php';
/**
 * Manufacturer: Name and URL
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Manufacturer.class.php';


/**
 * Shop
 * @internal    Extract code from this class and move it to other classes:
 *              Customer, Product, Order, ...
 * @internal    It doesn't really make sense to extend ShopLibrary.
 *              Instead, dissolve ShopLibrary into classes like
 *              Shop, Zones, Country, Payment, etc.
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @author      Ivan Schmid <ivan.schmid@comvation.com>
 * @access      public
 * @package     contrexx
 * @subpackage  module_shop
 * @version     2.1.0
 */
class Shop extends ShopLibrary
{
    private $pageContent;

    private static $statusMessage = '';
    private static $inactiveStyleName = 'inactive';
    private static $activeStyleName = 'active';
    private static $defaultImage = '';
    private static $uploadDir = false;

    /**
     * Currency navbar indicator
     * @var     boolean
     * @access  private
     */
    private $_hideCurrencyNavbar = false;

    /**
     * The PEAR Template Sigma object
     * @var     HTML_Template_Sigma
     * @access  private
     */
    private $objTemplate;

    /**
     * The Customer object
     * @var     Customer
     * @access  private
     * @see     lib/Customer.class.php
     */
    private $objCustomer;

    /**
     * The Payment Processing object
     * @var     PaymentProcessing
     * @access  private
     * @see     lib/PaymentProcessing.class.php
     */
    private $objProcessing;

    /**
     * The Shipment object
     * @var     Shipment
     * @access  private
     * @see     lib/Shipment.class.php
     */
    private $objShipment;


    /**
     * Constructor
     * @param  string
     * @access public
     */
    function __construct($pageContent)
    {
        $this->pageContent = $pageContent;
        self::$defaultImage = ASCMS_SHOP_IMAGES_WEB_PATH.'/'.ShopLibrary::noPictureName;
        $this->uploadDir = ASCMS_PATH_OFFSET.'/upload';

        // PEAR Sigma template
        $this->objTemplate = new HTML_Template_Sigma('.');
        $this->objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $this->objTemplate->setTemplate($this->pageContent, true, true);
        // Global module index for clones
        $this->objTemplate->setGlobalVariable('MODULE_INDEX', MODULE_INDEX);

        // Check session and user data, log in if present
        $this->_authenticate();

        Vat::isReseller($this->objCustomer && $this->objCustomer->isReseller());
        // May be omitted, those structures are initialized on first use
        Vat::init();
        Currency::init();
        Payment::init();
        Shipment::init();

        // Payment processing object
        $this->objProcessing = new PaymentProcessing($this->arrConfig);
    }


    /**
     * Returns the Shop page for the present parameters
     * @return  string                The Shop page content
     */
    function getPage()
    {
//DBG::activate(DBG_ERROR_FIREPHP);
DBG::activate(DBG_DB_FIREPHP);

        // Global placeholders that are used on more (almost) all pages.
        // Add more as desired.
        $this->objTemplate->setGlobalVariable(array(
            'SHOP_CURRENCY_CODE' => Currency::getActiveCurrencyCode(),
            'SHOP_CURRENCY_SYMBOL' => Currency::getActiveCurrencySymbol(),
        ));

        if (!isset($_GET['cmd'])) $_GET['cmd'] = '';
        if (!isset($_GET['act'])) $_GET['act'] = $_GET['cmd'];
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
            case 'discounts':
                $this->discounts();
                break;
            case 'login':
                $this->login();
                break;
            case 'paypalIpnCheck':
                require_once ASCMS_MODULE_PATH.'/shop/payments/paypal/Paypal.class.php';
                $objPaypal = new PayPal;
                $objPaypal->ipnCheck();
                exit;
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
                PayPal::testIpn(); // die()s!
            // Test for PayPal IPN validation
            // *DO NOT* remove this!  Needed for site testing.
            case 'testIpnValidate':
                require_once ASCMS_MODULE_PATH."/shop/payments/paypal/Paypal.class.php";
                PayPal::testIpnValidate(); // die()s!
            // Test mail body generation
            // *DO NOT* remove this!  Needed for site testing.
            case 'testMail':
//MailTemplate::errorHandler();die();
                $order_id = (isset($_GET['order_id']) ? $_GET['order_id'] : 10);
                $arrSubstitution =
                    ShopLibrary::getOrderSubstitutionArray($order_id);
                $arrMailtemplate = array(
                    'key'          => 1,
                    'lang_id'      => $arrSubstitution['LANG_ID'],
                    'substitution' => &$arrSubstitution,
                    'to' => 'reto.kohli@comvation.com',
//                            $arrSubstitution['CUSTOMER_EMAIL'].','.
//                            SettingDb::getValue('email_confirmation'),
                );
                $customer_id = $arrMailtemplate['substitution']['CUSTOMER_ID'];
                $objCustomer = Customer::getById($customer_id);
                if (!$objCustomer) die("No Customer for ID $customer_id");
                $arrMailtemplate['substitution'] += $objCustomer->getSubstitutionArray();
//die(nl2br(htmlentities(var_export($this->arrConfig, true), ENT_QUOTES, CONTREXX_CHARSET)));
                echo(nl2br(htmlentities(var_export($arrMailtemplate, true), ENT_QUOTES, CONTREXX_CHARSET)));
                die(MailTemplate::send($arrMailtemplate));

            case 'destroy':
                $this->destroyCart();
                // No break on purpose
            case 'lastFive':
            case 'products':
            default:
                $this->products();
        }
        $this->objTemplate->setVariable('SHOP_STATUS', self::$statusMessage);
DBG::deactivate();
        return $this->objTemplate->get();
    }


    function getNavbar($shopNavbarContent)
    {
        global $_ARRAYLANG;
        static $strContent;

        // Note: This is valid only as long as the content is the same every
        // time this method is called!
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
                    $this->objCustomer->getTitle().' '.
                    $this->objCustomer->getLastName().'<br />';
            }
            $loginStatus = $_ARRAYLANG['TXT_LOGGED_IN_AS'];
            // Show link to change the password
            $objTpl->setVariable(
                'TXT_SHOP_CHANGE_PASSWORD', $_ARRAYLANG['TXT_SHOP_CHANGE_PASSWORD']
            );
        } else {
            // Show login form if the customer is not logged in already.
            $loginInfo   = '';
            $loginStatus = $_ARRAYLANG['TXT_LOGGED_IN_AS_SHOP_GUEST'];
            // $redirect contains something like "section=shop&cmd=details&productId=1"
            if (isset($_REQUEST['redirect'])) {
                $redirect = $_REQUEST['redirect'];
            } else {
                $queryString = $_SERVER['QUERY_STRING'];
                $redirect = base64_encode(preg_replace('/\&?act\=\w*/', '', $queryString));
            }
            $objTpl->setVariable(array(
                'SHOP_LOGIN_ACTION' => 'index.php?section=shop&amp;cmd=login&amp;redirect='.$redirect,
                'TXT_EMAIL_ADDRESS' => $_ARRAYLANG['TXT_EMAIL'],
                'TXT_PASSWORD' => $_ARRAYLANG['TXT_PASSWORD'],
                'TXT_SHOP_ACCOUNT_LOGIN' => $_ARRAYLANG['TXT_SHOP_ACCOUNT_LOGIN'],
            ));
        }
        $objTpl->setGlobalVariable(array(
            'SHOP_CART_INFO'    => $this->showCartInfo(),
            'SHOP_LOGIN_STATUS' => $loginStatus,
            'SHOP_LOGIN_INFO'   => $loginInfo,
            'TXT_SHOP_SHOW_CART' => $_ARRAYLANG['TXT_SHOP_SHOW_CART'],
            'TXT_SHOP_CART_CONTENT' => $_ARRAYLANG['TXT_SHOP_CART_CONTENT'],
            'TXT_SHOP_CART_IS_LOADING' => $_ARRAYLANG['TXT_SHOP_CART_IS_LOADING'],
            'TXT_SHOP_CART_PRODUCTS_VALUE' => $_ARRAYLANG['TXT_SHOP_CART_PRODUCTS_VALUE'],
            'TXT_TOTAL' => $_ARRAYLANG['TXT_TOTAL'],
            'TXT_SHOP_MAKE_ORDER' => $_ARRAYLANG['TXT_SHOP_MAKE_ORDER'],
            'TXT_SHOP_NEW_ORDER' => $_ARRAYLANG['TXT_SHOP_NEW_ORDER'],
            'TXT_EMPTY_CART' => $_ARRAYLANG['TXT_EMPTY_CART'],
            'TXT_PRODUCTS' => $_ARRAYLANG['TXT_PRODUCTS'],
            'TXT_SHOP_LOGIN_INFO' => $_ARRAYLANG['TXT_SHOP_LOGIN_INFO'],
        ));

        // start currencies
        if (!$this->_hideCurrencyNavbar) {
            if ($objTpl->blockExists('shopCurrencies')) {
                $objTpl->setCurrentBlock('shopCurrencies');
                $curNavbar = Currency::getCurrencyNavbar();
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
                $objCategory = ShopCategory::getById($selectedCatId);
                if (!$objCategory) $selectedCatId = 0;
            }
            if (empty($selectedCatId) && isset($_REQUEST['productId'])) {
                $product_id = $_REQUEST['productId'];
                if (isset($_REQUEST['referer']) && $_REQUEST['referer'] == 'cart') {
                    $product_id = $_SESSION['shop']['cart']['products'][$product_id]['id'];
                }
                $objProduct = Product::getById($product_id);
                if ($objProduct) {
                    $selectedCatId = $objProduct->category_id();
                }
            }

            // Array of all visible ShopCategories
            $arrShopCategoryTree = ShopCategories::getTreeArray(
                false, true, true, $selectedCatId, 0, 0
            );
            // The trail of IDs to the selected ShopCategory,
            // built along with the tree array when calling getTreeArray().
            $arrTrail = ShopCategories::getTrailArray($selectedCatId);

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

        $cartTpl = str_replace(
            array(
                '{TXT_SHOP_SHOW_CART}',
                '{TXT_SHOP_CART_CONTENT}',
                '{TXT_SHOP_CART_IS_LOADING}',
                '{TXT_SHOP_CART_PRODUCTS_VALUE}',
                '{TXT_TOTAL}',
                '{TXT_SHOP_MAKE_ORDER}',
                '{TXT_SHOP_NEW_ORDER}',
                '{TXT_EMPTY_CART}',
                '{TXT_PRODUCTS}',
                '{TXT_SHOP_LOGIN_INFO}',
            ),
            array(
                $_ARRAYLANG['TXT_SHOP_SHOW_CART'],
                $_ARRAYLANG['TXT_SHOP_CART_CONTENT'],
                $_ARRAYLANG['TXT_SHOP_CART_IS_LOADING'],
                $_ARRAYLANG['TXT_SHOP_CART_PRODUCTS_VALUE'],
                $_ARRAYLANG['TXT_TOTAL'],
                $_ARRAYLANG['TXT_SHOP_MAKE_ORDER'],
                $_ARRAYLANG['TXT_SHOP_NEW_ORDER'],
                $_ARRAYLANG['TXT_EMPTY_CART'],
                $_ARRAYLANG['TXT_PRODUCTS'],
                $_ARRAYLANG['TXT_SHOP_LOGIN_INFO'],
            ),
            $cartTpl
        );
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
     * Change the customers' password
     *
     * If no customer is logged in, redirects to the login page.
     */
    function _changepass()
    {
        global $_ARRAYLANG;

        if (!$this->objCustomer) {
            header('Location: index.php?section=shop'.MODULE_INDEX.'&cmd=login');
            exit;
        }

        $this->objTemplate->setVariable(array(
            'SHOP_PASSWORD_CURRENT' => $_ARRAYLANG['SHOP_PASSWORD_CURRENT'],
            'SHOP_PASSWORD_NEW'     => $_ARRAYLANG['SHOP_PASSWORD_NEW'],
            'SHOP_PASSWORD_CONFIRM' => $_ARRAYLANG['SHOP_PASSWORD_CONFIRM'],
            'SHOP_PASSWORD_CHANGE'  => $_ARRAYLANG['SHOP_PASSWORD_CHANGE'],
        ));

        if (isset($_POST['shopNewPassword'])) {
            if (empty($_POST['shopCurrentPassword'])) {
                $status = $_ARRAYLANG['TXT_SHOP_ENTER_CURRENT_PASSWORD'];
            } elseif (md5(contrexx_stripslashes($_POST['shopCurrentPassword'])) != $this->objCustomer->getPasswordMd5()) {
                $status = $_ARRAYLANG['TXT_SHOP_WRONG_CURRENT_PASSWORD'];
            } elseif (empty($_POST['shopNewPassword'])) {
                $status = $_ARRAYLANG['TXT_SHOP_SPECIFY_NEW_PASSWORD'];
            } elseif (   isset($_POST['shopConfirmPassword'])
                      && $_POST['shopNewPassword'] != $_POST['shopConfirmPassword']) {
                $status = $_ARRAYLANG['TXT_SHOP_PASSWORD_NOT_CONFIRMED'];
            } elseif (strlen($_POST['shopNewPassword']) < 6) {
                $status = $_ARRAYLANG['TXT_PASSWORD_MIN_CHARS'];
            } else {
                $this->objCustomer->setPassword(contrexx_stripslashes($_POST['shopNewPassword']));
                $status = $_ARRAYLANG['TXT_SHOP_PASSWORD_CHANGED_SUCCESSFULLY'];
            }
            $this->objTemplate->setVariable(
                'SHOP_PASSWORD_STATUS', $status.'<br />'
            );
        }
    }


    /**
     * Send the Customer login data
     * @param   string   $email     The e-mail address
     * @return  boolean             True on success, false otherwise
     */
    static function sendLogin($email, $password)
    {
        global $_ARRAYLANG;

        $arrCustomer = Customer::getByWildcard(array('email' => $email, ));
        if (count($arrCustomer) != 1) {
            self::addMessage($_ARRAYLANG['TXT_SHOP_NO_ACCOUNT_WITH_EMAIL']);
            return false;
        }
        $objCustomer = current($arrCustomer);
        if (!$objCustomer) return false;
        $arrSubstitution = $objCustomer->getSubstitutionArray();
        if (!$arrSubstitution) return false;
        $arrSubstitution['CUSTOMER_PASSWORD'] = $password;
        // Defaults to FRONTEND_LANG_ID
        $arrMailtemplate = array(
            'key'          => 3,
            'substitution' => &$arrSubstitution,
            'to'           => $objCustomer->getEmail(),
        );
        return MailTemplate::send($arrMailtemplate);
    }


    /**
     * Shows the form for entering the e-mail address
     *
     * After a valid address has been posted back, creates a new password
     * and sends it to the Customer
     * @return    boolean                   True on success, false otherwise
     */
    function _sendpass()
    {
        global $_ARRAYLANG;

        // See block 'shop_sendpass' below
        $this->objTemplate->setVariable(array(
            'SHOP_PASSWORD_ENTER_EMAIL' => $_ARRAYLANG['SHOP_PASSWORD_ENTER_EMAIL'],
            'TXT_NEXT'                  => $_ARRAYLANG['TXT_NEXT'],
        ));
        if (empty($_POST['shopEmail'])) return true;
        $email = contrexx_stripslashes($_POST['shopEmail']);
        $password = User::makePassword();
        if (!self::sendLogin($email, $password)) {
            self::addMessage($_ARRAYLANG['TXT_SHOP_UNABLE_TO_SEND_EMAIL']);
            return false;
        }
        if (!Customer::updatePassword($email, $password)) {
            self::addMessage($_ARRAYLANG['TXT_SHOP_UNABLE_SET_NEW_PASSWORD']);
            return false;
        }
        self::addMessage($_ARRAYLANG['TXT_SHOP_ACCOUNT_DETAILS_SENT_SUCCESSFULLY']);
        $this->objTemplate->hideBlock('shop_sendpass');
        return true;
    }


    /**
     * Set up the subcategories block in the current shop page.
     * @param   integer     $parent_id   The optional parent ShopCategory ID,
     *                                  defaults to 0 (zero).
     * @return  boolean                 True on success, false otherwise
     * @global  array
     */
    function showCategories($parent_id=0)
    {
        global $_ARRAYLANG;

        if ($parent_id > 0) {
            $objCategory = ShopCategory::getById($parent_id);
            // If we can't get this ShopCategory, it most probably does
            // not exist.
            if (!$objCategory) {
                if ($parent_id > 0) {
                    // Retry using the root.
                    $this->showCategories(0);
                }
                // Otherwise, there's no point in looking for its
                // children either.
                return false;
            }
            // Show the parent ShopCategorys' image, if available
            $imageName = $objCategory->getPicture();
            if ($imageName
             && $this->objTemplate->blockExists('shopCategoryImage')) {
                $this->objTemplate->setCurrentBlock('shopCategoryImage');
                $this->objTemplate->setVariable(array(
                    'SHOP_CATEGORY_IMAGE'     =>
                        ASCMS_SHOP_IMAGES_WEB_PATH.'/category/'.$imageName,
                    'SHOP_CATEGORY_IMAGE_ALT' => $objCategory->getName(),
                ));
            }
        }

        // Get all active child categories with parent ID $parent_id
        $arrShopCategory =
            ShopCategories::getChildCategoriesById($parent_id, true);
        if (!is_array($arrShopCategory)) {
            return false;
        }
        $cell = 0;
        $arrDefaultImageSize = false;
        $this->objTemplate->setCurrentBlock();
        // For all child categories do...
        foreach ($arrShopCategory as $objCategory) {
            $id        = $objCategory->getId();
            $catName   = $objCategory->getName();
            $imageName = $objCategory->getPicture();
            $thumbnailPath = self::$defaultImage;
            $description = $objCategory->getDescription();
            $description = nl2br(htmlentities($description, ENT_QUOTES, CONTREXX_CHARSET));
            $description = preg_replace('/[\n\r]/', '', $description);

            if (empty($arrDefaultImageSize)) {
                $arrDefaultImageSize = getimagesize(ASCMS_PATH.self::$defaultImage);
                $this->scaleImageSizeToThumbnail($arrDefaultImageSize);
            }
            $arrSize = $arrDefaultImageSize;
            if (!$imageName) {
                // Look for a picture in the Products.
                $imageName = Products::getPictureByCategoryId($id);
            }
            if (!$imageName) {
                // Look for a picture in the subcategories and their Products.
                $imageName = ShopCategories::getPictureById($id);
            }
            if ($imageName) {
                $thumb_name = ImageManager::getThumbnailFilename($imageName);
                if (file_exists(ASCMS_SHOP_IMAGES_PATH.'/'.$thumb_name)) {
                    // Image found!  Use that instead of the default.
                    $thumbnailPath =
                        ASCMS_SHOP_IMAGES_WEB_PATH.'/'.$thumb_name;
                    $arrSize = getimagesize(ASCMS_PATH.$thumbnailPath);
                    $this->scaleImageSizeToThumbnail($arrSize);
                }
            }

            $this->objTemplate->setVariable(array(
                'SHOP_PRODUCT_TITLE'            => htmlentities($catName, ENT_QUOTES, CONTREXX_CHARSET),
                'SHOP_PRODUCT_THUMBNAIL'        => $thumbnailPath,
                'SHOP_PRODUCT_THUMBNAIL_SIZE'   => $arrSize[3],
                'TXT_ADD_TO_CARD'               => $_ARRAYLANG['TXT_SHOP_GO_TO_CATEGORY'],
                'SHOP_PRODUCT_DETAILLINK_IMAGE' => 'index.php?section=shop'.MODULE_INDEX.'&amp;catId='.$id,
                'SHOP_PRODUCT_SUBMIT_FUNCTION'  => 'location.replace("index.php?section=shop'.MODULE_INDEX.'&catId='.$id.'")',
                'SHOP_PRODUCT_SUBMIT_TYPE'      => "button",
                'SHOP_PRODUCT_CAT_ID'           => $id,
                'SHOP_PRODUCT_CAT_DESCRIPTION'  => $description,

            ));
            // Add flag images for flagged ShopCategories
            $strImage = '';
            $arrVirtual = ShopCategories::getVirtualCategoryNameArray();
            foreach ($arrVirtual as $strFlag) {
                if ($objCategory->testFlag($strFlag)) {
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
     * Set up the shop page with products and discounts
     *
     * @return  boolean                      True on success, false otherwise
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @global  array       $_ARRAYLANG     Language array
     * @global  array       $_CONFIG        Core configuration array, see {@link /config/settings.php}
     * @global  string(?)   $themesPages    Themes pages(?)
     */
    function products()
    {
        global $_ARRAYLANG;

        JS::activate('shadowbox');
        SettingDb::init();

        $flagSpecialoffer = intval(SettingDb::getValue('show_products_default'));
        $flagLastFive =
            (isset($_REQUEST['lastFive'])  ? true                   : false);
        $product_id =
            (isset($_REQUEST['productId']) ? $_REQUEST['productId'] : 0);
        $catId =
            (isset($_REQUEST['catId'])     ? $_REQUEST['catId']     : 0);
        $manufacturerId =
            (isset($_REQUEST['manId'])     ? $_REQUEST['manId']     : 0);
        $term =
            (isset($_REQUEST['term'])
                ? trim(contrexx_stripslashes($_REQUEST['term'])) : ''
            );
        $pos =
            (isset($_REQUEST['pos'])       ? $_REQUEST['pos']       : 0);

        $shopMenu =
            '<input type="text" name="term" value="'.
            htmlentities($term, ENT_QUOTES, CONTREXX_CHARSET).
            '" style="width:150px;" />&nbsp;'.
            '<select name="catId" style="width:150px;">'.
            '<option value="0">'.$_ARRAYLANG['TXT_ALL_PRODUCT_GROUPS'].
            '</option>'.ShopCategories::getMenuoptions($catId).
            '</select>&nbsp;'.Manufacturer::getMenu($manufacturerId).
            '<input type="submit" name="Submit" value="'.$_ARRAYLANG['TXT_SEARCH'].
            '" style="width:66px;" />';
        $this->objTemplate->setGlobalVariable(
            $_ARRAYLANG
          + array(
            'SHOP_MENU' => $shopMenu,
            'SHOP_SEARCH_TERM' => htmlentities($term, ENT_QUOTES, CONTREXX_CHARSET),
            'SHOP_CART_INFO' => $this->showCartInfo(),
            'SHOP_JAVASCRIPT_CODE' => $this->getJavascriptCode(),
            // New from 3.0.0 - More flexible Shop menu
            'SHOP_CATEGORIES_MENUOPTIONS' =>
                ShopCategories::getMenuoptions($catId, true, 0, true),
            'SHOP_MANUFACTURER_MENUOPTIONS' =>
                Manufacturer::getMenuoptions($manufacturerId, true),
        ));

        if ($catId && $term == '') {
            $this->showCategories($catId);
        } elseif ($term == '') {
            $this->showCategories(0);
        }

        if ($this->objTemplate->blockExists('shopNextCategoryLink')) {
            $nextCat = ShopCategory::getNextShopCategoryId($catId);
            $objCategory = ShopCategory::getById($nextCat);
            $this->objTemplate->setVariable(array(
                'SHOP_NEXT_CATEGORY_ID'    => $nextCat,
                'SHOP_NEXT_CATEGORY_TITLE' => str_replace('"', '&quot;', $objCategory->getName()),
            ));
            $this->objTemplate->parse('shopNextCategoryLink');
        }
// Moved to index.php
//        $this->objTemplate->setVariable(
//            'SHOPNAVBAR_FILE',
//            $this->getNavbar($themesPages['shopnavbar']));

        if (isset($_REQUEST['referer']) && $_REQUEST['referer'] == 'cart') {
            $cartProdId = $product_id;
            $product_id = $_SESSION['shop']['cart']['products'][$product_id]['id'];
        }

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
        $count = 0;
        $arrProduct = Products::getByShopParams(
            $count, $pos,
            $product_id, $catId, $manufacturerId, $term,
            $flagSpecialoffer, $flagLastFive,
            Products::$arrProductOrder[SettingDb::getValue('product_sorting')],
            $this->objCustomer && $this->objCustomer->isReseller()
        );

        $detailLink = '';
        if ($count == 0) {
            $this->objTemplate->hideBlock('shopProductRow');
            return true;
        }
        $objCategory = ShopCategory::getById($catId);
        if ($objCategory) {
            $this->objTemplate->setVariable(array(
                'SHOP_CATEGORY_NAME' =>
                    str_replace('"', '&quot;', $objCategory->getName()),
                // New (3.0.0)
                'SHOP_PRODUCTS_IN_CATEGORY' => sprintf(
                    $_ARRAYLANG['TXT_SHOP_PRODUCTS_IN_CATEGORY'],
                    htmlentities($objCategory->getName(),
                        ENT_QUOTES, CONTREXX_CHARSET)),
            ));
        }
        $this->objTemplate->setVariable(array(
            'SHOP_PRODUCT_PAGING' => getPaging(
                $count, $pos,
                '&amp;section=shop'.MODULE_INDEX.
                  $pagingCatId.$pagingManId.$pagingTerm,
                '', true),
            'SHOP_PRODUCT_TOTAL'  => $count,
        ));


        $formId = 0;
        $arrDefaultImageSize = false;
        /** @var   Product $objProduct = null; */
        $flagUpload = false;
        foreach ($arrProduct as $objProduct) {
            $id = $objProduct->id();
//DBG::log("Product ID $id, Product ".var_export($objProduct, true));
            $productSubmitFunction = '';
            $arrPictures = Products::getShopImagesFromBase64String($objProduct->pictures());
            $havePicture = false;
            $arrProductImages = array();
            foreach ($arrPictures as $index => $image) {
                if (   empty($image['img'])
                    || $image['img'] == ShopLibrary::noPictureName) {
                    // We have at least one picture on display already.
                    // No need to show "no picture" three times!
                    if ($havePicture) { continue; }
                    $thumbnailPath = self::$defaultImage;
                    $pictureLink = ''; //"javascript:alert('".$_ARRAYLANG['TXT_NO_PICTURE_AVAILABLE']."');";
                    if (empty($arrDefaultImageSize)) {
                        $arrDefaultImageSize = getimagesize(ASCMS_PATH.self::$defaultImage);
                        $this->scaleImageSizeToThumbnail($arrDefaultImageSize);
                    }
                    $arrSize = $arrDefaultImageSize;
                } else {
                    $thumbnailPath = ASCMS_SHOP_IMAGES_WEB_PATH.'/'.
                        ImageManager::getThumbnailFilename($image['img']);
                    if ($image['width'] && $image['height']) {
                        $pictureLink =
                            FWValidator::getEscapedSource(ASCMS_SHOP_IMAGES_WEB_PATH.'/'.$image['img']).
                            // Hack ahead!
                            '" rel="shadowbox[1]';
                            // Thumbnail display size
                            $arrSize = array($image['width'], $image['height']);
                    } else {
                        $pictureLink = '';
                            $arrSize = getimagesize(ASCMS_PATH.$thumbnailPath);
                    }
                    $this->scaleImageSizeToThumbnail($arrSize);
                }
                $arrProductImages[] = array(
                    'THUMBNAIL'       => FWValidator::getEscapedSource($thumbnailPath),
                    'THUMBNAIL_SIZE'  => $arrSize[3],
                    'THUMBNAIL_LINK'  => $pictureLink,
                    'POPUP_LINK'      => $pictureLink,
                    'POPUP_LINK_NAME' => $_ARRAYLANG['TXT_SHOP_IMAGE'].' '.$index,
                );
                $havePicture = true;
            }
            $i = 1;
            foreach ($arrProductImages as $arrProductImage) {
                $this->objTemplate->setVariable(array(
                    'SHOP_PRODUCT_THUMBNAIL_'.$i => $arrProductImage['THUMBNAIL'],
                    'SHOP_PRODUCT_THUMBNAIL_SIZE_'.$i => $arrProductImage['THUMBNAIL_SIZE'],
                ));
                if (!empty($arrProductImage['THUMBNAIL_LINK'])) {
                    $this->objTemplate->setVariable(array(
                        'SHOP_PRODUCT_THUMBNAIL_LINK_'.$i => $arrProductImage['THUMBNAIL_LINK'],
                        'TXT_SEE_LARGE_PICTURE'           => $_ARRAYLANG['TXT_SEE_LARGE_PICTURE'],
                    ));
                } else {
                    $this->objTemplate->setVariable(array(
                        'TXT_SEE_LARGE_PICTURE' => $objProduct->name(),
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

            $stock = ($objProduct->stock_visible()
                ? $_ARRAYLANG['TXT_STOCK'].': '.
                  intval($objProduct->stock())
                : ''
            );

            $price = $this->_getProductPrice(
                $id,
                0,    // No options yet
                1,    // Apply discount for one article
                true  // Ignore special offers
            );
            // If there is a discountprice and it's enabled
            if (   $objProduct->discountprice() > 0
                && $objProduct->discount_active()) {
                $price = '<s>'.$price.'</s>';
                $discountPrice = $this->_getProductPrice(
                    $id,
                    0,    // No options yet
                    1,    // Apply discount for one article
                    false // Consider special offers
                );
            } else {
                $discountPrice = '';
            }

            $groupCountId = $objProduct->group_id();
            $groupArticleId = $objProduct->article_id();
            $groupCustomerId = 0;
            if ($this->objCustomer) {
                $groupCustomerId = $this->objCustomer->getGroupId();
            }
            $this->showDiscountInfo(
                $groupCustomerId, $groupArticleId, $groupCountId, 1
            );

/* OLD
            $price = Currency::getCurrencyPrice(
                $objProduct->getCustomerPrice($this->objCustomer)
            );
            $discountPrice = '';
            $discount_active = $objProduct->discount_active();
            if ($discount_active) {
                $discountPrice = $objProduct->discountprice();
                if ($discountPrice > 0) {
                    $price = "<s>$price</s>";
                    $discountPrice =
                        Currency::getCurrencyPrice($discountPrice);
                }
            }
*/
            $shortDescription = $objProduct->short();
            $longDescription  = $objProduct->long();

            $detailLink = false;
            if ($product_id == 0 && !empty($longDescription)) {
                $detailLink =
                    '<a href="index.php?section=shop'.MODULE_INDEX.'&amp;cmd=details&amp;productId='.
                    $objProduct->id().
                    '" title="'.$_ARRAYLANG['TXT_MORE_INFORMATIONS'].'">'.
                    $_ARRAYLANG['TXT_MORE_INFORMATIONS'].'</a>';
            }

            // Check Product flags.
            // Only the meter flag is currently implemented and in use.
            $flagMeter = $objProduct->testFlag('__METER__');

            // Submit button name and function.
            // Calling productOptions() also sets the $flagMultipart variable
            // to the appropriate encoding type for the form if
            // any upload fields are in use.
            $flagMultipart = false;
            if (isset($_GET['cmd']) && $_GET['cmd'] == 'details'
             && isset($_GET['referer']) && $_GET['referer'] == 'cart') {
                $productSubmitName = "updateProduct[$cartProdId]";
                $productSubmitFunction = $this->productOptions(
                    $objProduct->id(), $formId, $cartProdId, $flagMultipart
                );
            } else {
                $productSubmitName = 'addProduct';
                $productSubmitFunction = $this->productOptions(
                    $objProduct->id(), $formId, false, $flagMultipart
                );
            }
            // Should be used by getJavaScript()
            if ($flagMultipart) $flagUpload = true;
            $shopProductFormName = "shopProductForm$formId";

            $this->objTemplate->setVariable(array(
                'SHOP_ROWCLASS'                   => (++$formId % 2 ? 'row2' : 'row1'),
                'SHOP_PRODUCT_ID'                 => $objProduct->id(),
                'SHOP_PRODUCT_CUSTOM_ID'          => htmlentities($objProduct->code(), ENT_QUOTES, CONTREXX_CHARSET),
                'SHOP_PRODUCT_TITLE'              => htmlentities($objProduct->name(), ENT_QUOTES, CONTREXX_CHARSET),
                'SHOP_PRODUCT_DESCRIPTION'        => $shortDescription,
                'SHOP_PRODUCT_DETAILDESCRIPTION'  => $longDescription,
                'SHOP_PRODUCT_FORM_NAME'          => $shopProductFormName,
                'SHOP_PRODUCT_SUBMIT_NAME'        => $productSubmitName,
                'SHOP_PRODUCT_SUBMIT_FUNCTION'    => $productSubmitFunction,
                'SHOP_FORM_ENCTYPE'               =>
                    ($flagMultipart ? ' enctype="multipart/form-data"' : ''),
                // Meter flag
                'TXT_SHOP_PRODUCT_COUNT'          =>
                    ($flagMeter
                        ? $_ARRAYLANG['TXT_SHOP_PRODUCT_METER']
                        : $_ARRAYLANG['TXT_SHOP_PRODUCT_COUNT']
                    ),
            ));

            $manufacturerName = '';
            $manufacturerUrl  = '';
            $manufacturerId   = $objProduct->manufacturer_id();
            if ($manufacturerId) {
                $manufacturerName =
                    Manufacturer::getNameById($manufacturerId, FRONTEND_LANG_ID);
                $manufacturerUrl  =
                    Manufacturer::getUrlById($manufacturerId, FRONTEND_LANG_ID);
            }
            if (!empty($manufacturerUrl) || !empty($manufacturerName)) {
                if (empty($manufacturerName)) {
                    $manufacturerName = $manufacturerUrl;
                }
                if (!empty($manufacturerUrl)) {
                    $manufacturerName =
                        '<a href="'.$manufacturerUrl.'">'.
                        $manufacturerName.'</a>';
                }
                $this->objTemplate->setVariable(array(
                    'SHOP_MANUFACTURER_LINK'     => $manufacturerName,
                    'TXT_SHOP_MANUFACTURER_LINK' => $_ARRAYLANG['TXT_SHOP_MANUFACTURER_LINK'],
                ));
            }

            // This is the old Product field for the Manufacturer URI.
            // This is now extended by the Manufacturer table and should thus
            // get a new purpose.  As it is product specific, it could be
            // renamed and reused as a link to individual Products!
            $externalLink = $objProduct->uri();
            if (!empty($externalLink)) {
                $this->objTemplate->setVariable(array(
                    'SHOP_EXTERNAL_LINK'     =>
                    '<a href="'.$externalLink.
                    '" title="'.$_ARRAYLANG['TXT_SHOP_EXTERNAL_LINK'].
                    '" target="_blank">'.
                    $_ARRAYLANG['TXT_SHOP_EXTERNAL_LINK'].'</a>',
                ));
            }

            if ($price) {
                $this->objTemplate->setVariable(array(
                    'SHOP_PRODUCT_PRICE'      => $price,
                    'SHOP_PRODUCT_PRICE_UNIT' => Currency::getActiveCurrencySymbol(),
                ));
            }
            // Only show the discount price if it's actually in use,
            // avoid an "empty <font> tag" HTML warning
            if ($discountPrice) {
                $this->objTemplate->setVariable(array(
                    'SHOP_PRODUCT_DISCOUNTPRICE'      => $discountPrice,
                    'SHOP_PRODUCT_DISCOUNTPRICE_UNIT' => Currency::getActiveCurrencySymbol(),
                ));
            }
            // Special outlet ShopCategory with discounts varying daily.
            // This should be implemented in a more generic way, in the
            // Discount class maybe.
            if ($objProduct->is_outlet()) {
                $this->objTemplate->setVariable(array(
                    'TXT_SHOP_DISCOUNT_TODAY'   =>
                        $_ARRAYLANG['TXT_SHOP_DISCOUNT_TODAY'],
                    'SHOP_DISCOUNT_TODAY'       =>
                        $objProduct->getOutletDiscountRate().'%',
                    'TXT_SHOP_PRICE_TODAY'      =>
                        $_ARRAYLANG['TXT_SHOP_PRICE_TODAY'],
                    'SHOP_PRICE_TODAY'          =>
                        Currency::getCurrencyPrice(
                            $objProduct->getDiscountedPrice()
                        ),
                    'SHOP_PRICE_TODAY_UNIT'     =>
                        Currency::getActiveCurrencySymbol(),
                ));
            }
            if ($objProduct->stock_visible()) {
                $this->objTemplate->setVariable(array(
                    'SHOP_PRODUCT_STOCK' => $stock,
                ));
            }
            if ($detailLink) {
                $this->objTemplate->setVariable(array(
                    'SHOP_PRODUCT_DETAILLINK' => $detailLink,
                ));
            }
            $distribution = $objProduct->distribution();
            $productWeight = '';
            if ($distribution == 'delivery') {
                $productWeight = $objProduct->weight();
            }

            // Hide the weight if it is zero or disabled in the configuration
            if (   $productWeight > 0
                && SettingDb::getValue('weight_enable')) {
                $this->objTemplate->setVariable(array(
                    'TXT_SHOP_PRODUCT_WEIGHT' => $_ARRAYLANG['TXT_SHOP_PRODUCT_WEIGHT'],
                    'SHOP_PRODUCT_WEIGHT'     => Weight::getWeightString($productWeight),
                ));
            }
            if (Vat::isEnabled()) {
                $this->objTemplate->setVariable(array(
                    'SHOP_PRODUCT_TAX_PREFIX' =>
                        (Vat::isIncluded()
                            ? $_ARRAYLANG['TXT_SHOP_VAT_PREFIX_INCL']
                            : $_ARRAYLANG['TXT_SHOP_VAT_PREFIX_EXCL']
                         ),
                    'SHOP_PRODUCT_TAX'        =>
                        Vat::getShort($objProduct->vat_id())
                ));
            }

            // Add flag images for flagged Products
            $strImage = '';
            $strFlags = $objProduct->flags();
            $arrVirtual = ShopCategories::getVirtualCategoryNameArray(FRONTEND_LANG_ID);
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
     * 4    Optional text field
     * 5    Mandatory text field
     * 6    Optional file upload field
     * 7    Mandatory file upload field
     * Types 1 and 3 are functionally identical, they only differ by
     * the kind of widget being used.
     * The individual Product Attributes carry a unique ID enabling the
     * JavaScript code contained within the Shop page to verify that
     * all mandatory choices have been made before any Product can
     * be added to the cart.
     * @param   integer     $product_id     The Product ID
     * @param   string      $formName       The name of the HTML form containing
     *                                      the Product and options
     * @param   integer     $cartProdId     The optional cart Product ID,
     *                                      false if not applicable.
     * @param   boolean     $flagUpload     If a product has an upload
     *                                      Attribute associated with it,
     *                                      this parameter will be set to true
     * @return  string                      The string with the HTML code
     */
    function productOptions($product_id, $formName, $cartProdId=false, &$flagUpload=false)
    {
        global $_ARRAYLANG;

        // Semicolon separated list of Attribute name IDs to verify
        // before the Product is added to the cart
        $checkOptionIds = '';
        // check if the product option block exists in the template
        if (   $this->objTemplate->blockExists('shopProductOptionsRow')
            && $this->objTemplate->blockExists('shopProductOptionsValuesRow')) {
            $domId = 0;
            // I the cart Product ID is non-false, use the Product ID
            // associated with it in the cart.
            // This is needed to correctly update those Products.
            if ($cartProdId !== false)
                $product_id =
                    $_SESSION['shop']['cart']['products'][$cartProdId]['id'];
            $count = 0;
            $arrAttributes = Attributes::getArray(
                $count, null, null, null, array('product_id' => $product_id));
DBG::log("Attributes: ".var_export($arrAttributes, true));
            // When there are no Attributes for this Product, hide the
            // options blocks
            if (empty($arrAttributes)) {
                $this->objTemplate->hideBlock('shopProductOptionsRow');
                $this->objTemplate->hideBlock('shopProductOptionsValuesRow');
            } else {
                // Loop through the Attribute Names for the Product
                foreach ($arrAttributes as $attribute_id => $objAttribute) {
                    $arrOptions = Attributes::getOptionArrayByAttributeId($attribute_id);
                    $arrRelation = Attributes::getRelationArray($product_id);
                    // This attribute does not apply for this product
                    if (empty($arrRelation)) continue;
                    $selectValues = '';
                    // create head of option menu/checkbox/radiobutton
                    switch ($objAttribute->getType()) {
                      case '0': // Dropdown menu (optional attribute)
                      // There is no hidden input field here, as there is no
                      // mandatory choice, the status need not be verified.
                        $selectValues =
                            '<select name="productOption['.$attribute_id.
                            ']" id="productOption-'.
                            $product_id.'-'.$attribute_id.'-'.$domId.
                            '" style="width:180px;">'."\n".
                            '<option value="0">'.
                            $objAttribute->getName().'&nbsp;'.
                            $_ARRAYLANG['TXT_CHOOSE']."</option>\n";
                        break;
                      case '1': // Radio buttons
                        // The hidden input field carries the name of the
                        // Attribute, indicating a mandatory option.
                        $selectValues =
                            '<input type="hidden" id="productOption-'.
                            $product_id.'-'.$attribute_id.
                            '" value="'.$objAttribute->getName().'" />'."\n";
                        $checkOptionIds .= "$attribute_id;";
                        break;
                      // No container for checkboxes (2), as there is no
                      // mandatory choice, their status need not be verified.
                      case '3': // Dropdown menu (mandatory attribute)
                        $selectValues =
                            '<input type="hidden" id="productOption-'.
                            $product_id.'-'.$attribute_id.
                            '" value="'.$objAttribute->getName().'" />'."\n".
                            '<select name="productOption['.$attribute_id.
                            ']" id="productOption-'.
                            $product_id.'-'.$attribute_id.'-'.$domId.
                            '" style="width:180px;">'."\n".
                            // If there is only one option to choose from,
                            // why bother the customer at all?
                            (count($arrOptions) > 1
                                ? '<option value="0">'.
                                  $objAttribute->getName().'&nbsp;'.
                                  $_ARRAYLANG['TXT_CHOOSE']."</option>\n"
                                : ''
                            );
                        $checkOptionIds .= "$attribute_id;";
                        break;

                      case '4': // Text field, optional
                        break;
                      case '5': // Text field, mandatory
                        $selectValues =
                            '<input type="hidden" id="productOption-'.
                            $product_id.'-'.$attribute_id.
                            '" value="'.$objAttribute->getName().'" />'."\n";
                        $checkOptionIds .= "$attribute_id;";
                        break;

                      case '6': // Upload field, optional
                        break;
                      case '7': // Upload field, mandatory
                        $selectValues =
                            '<input type="hidden" id="productOption-'.
                            $product_id.'-'.$attribute_id.
                            '" value="'.$objAttribute->getName().'" />'."\n";
                        $checkOptionIds .= "$attribute_id;";
                        break;
                    }

                    $i = 0;
                    foreach ($arrOptions as $option_id => $arrOption) {
                        // This option does not apply to this product
                        if (!isset($arrRelation[$option_id])) continue;
                        $option_price = '';
                        $selected   = false;
                        // Show the price only if non-zero
                        if ($arrOption['price'] != 0) {
                            $option_price =
                                '&nbsp;('.Currency::getCurrencyPrice($arrOption['price']).
                                '&nbsp;'.Currency::getActiveCurrencySymbol().')';
                        }
                        // mark the option value as selected if it was before
                        // and this page was requested from the cart
                        if (   $cartProdId !== false
                            && isset($_SESSION['shop']['cart']['products'][$cartProdId]['options'][$attribute_id])) {
                            if (in_array($option_id, $_SESSION['shop']['cart']['products'][$cartProdId]['options'][$attribute_id]))
                                $selected = true;
                        }
                        // create option menu/checkbox/radiobutton
                        switch ($objAttribute->getType()) {
                          case '0': // Dropdown menu (optional attribute)
                            $selectValues .=
                                '<option value="'.$option_id.'" '.
                                ($selected ? 'selected="selected"' : '').
                                ' >'.$arrOption['value'].$option_price.
                                "</option>\n";
                            break;
                          case '1': // Radio buttons
                            $selectValues .=
                                '<input type="radio" name="productOption['.
                                $attribute_id.']" id="productOption-'.
                                $product_id.'-'.$attribute_id.'-'.$domId.
                                '" value="'.$option_id.'"'.
                                ($selected ? ' checked="checked"' : '').
                                ' /><label for="productOption-'.
                                $product_id.'-'.$attribute_id.'-'.$domId.
                                '">&nbsp;'.$arrOption['value'].$option_price.
                                "</label><br />\n";
                            break;
                          case '2': // Checkboxes
                            $selectValues .=
                                '<input type="checkbox" name="productOption['.
                                $attribute_id.']['.$i.']" id="productOption-'.
                                $product_id.'-'.$attribute_id.'-'.$domId.
                                '" value="'.$option_id.'"'.
                                ($selected ? ' checked="checked"' : '').
                                ' /><label for="productOption-'.
                                $product_id.'-'.$attribute_id.'-'.$domId.
                                '">&nbsp;'.$arrOption['value'].$option_price.
                                "</label><br />\n";
                            break;
                          case '3': // Dropdown menu (mandatory attribute)
                            $selectValues .=
                                '<option value="'.$option_id.'"'.
                                ($selected ? ' selected="selected"' : '').
                                ' >'.$arrOption['value'].$option_price.
                                "</option>\n";
                            break;
                          case '4': // Text field, optional
                          case '5': // Text field, mandatory
//                            $option_price = '&nbsp;';
                            $selectValues .=
                                '<input type="text" name="productOption['.$attribute_id.
                                ']" id="productOption-'.$product_id.'-'.$attribute_id.'-'.$domId.
                                '" value="'.$arrOption['value'].'" style="width:180px;" />'.
                                '<label for="productOption-'.$product_id.'-'.$attribute_id.'-'.$domId.'">'.
                                $option_price."</label><br />\n";
                            break;
                          case '6': // UploadText field, optional
                          case '7': // Text field, mandatory
//                            $option_price = '&nbsp;';
                            $selectValues .=
                                '<input type="file" name="productOption['.$attribute_id.
                                ']" id="productOption-'.$product_id.'-'.$attribute_id.'-'.$domId.
                                '" style="width:180px;" />'.
                                '<label for="productOption-'.$product_id.'-'.$attribute_id.'-'.$domId.'">'.
                                $option_price."</label><br />\n";
                            break;
                        }
                        ++$i;
                        ++$domId;
                    }
                    // create foot of option menu/checkbox/radiobutton
                    switch ($objAttribute->getType()) {
                        case '0':
                            $selectValues .= "</select><br />\n";
                            break;
                        case '1':
                            $selectValues .= "<br />\n";
                            break;
                        case '2':
                            $selectValues .= "\n";
                            break;
                        case '3':
                            $selectValues .= "</select><br />\n";
                            break;

/* Nothing to to
                        case '4': // Text field, optional
                        case '5': // Text field, mandatory
*/
                        // Set enctype in form if one of these is present
                        case '6': // UploadText field, optional
                        case '7': // Text field, mandatory
// $flagUpload is passed by reference and causes an analyzer warning -- ignore!
                            $flagUpload = true;
                    }

                    $this->objTemplate->setVariable(array(
                        // pre-version 1.1 spelling error fixed
                        // left old spelling for comatibility (obsolete)
                        'SHOP_PRODCUT_OPTION' => $selectValues,
                        'SHOP_PRODUCT_OPTION' => $selectValues,
                        'SHOP_PRODUCT_OPTIONS_NAME'  => $objAttribute->getName(),
                        'SHOP_PRODUCT_OPTIONS_TITLE' =>
                            '<a href="javascript:{}" onclick="toggleOptions('.
                            $product_id.')" title="'.
                            $_ARRAYLANG['TXT_OPTIONS'].'">'.
                            $_ARRAYLANG['TXT_OPTIONS']."</a>\n",
                    ));

                    $this->objTemplate->parse('shopProductOptionsValuesRow');
                }
                $this->objTemplate->parse('shopProductOptionsRow');
            }
        }
        return
            "return checkProductOption('shopProductForm$formName', ".
            "$product_id, '".
            substr($checkOptionIds, 0, strlen($checkOptionIds)-1)."');";
    }


    /**
     * @todo    Rewrite this using the Product class
     * @return  boolean             True on success, false otherwise
     */
    function discounts()
    {
        global $objDatabase, $_ARRAYLANG;

        $query = "
            SELECT p.id, p.title, p.picture,
                   p.normalprice, p.resellerprice, p.discountprice
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products AS p
             INNER JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_categories AS c USING (catid)
             WHERE p.discount_active=1
               AND p.active=1
               AND c.active=1
             ORDER BY p.ord";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            $this->errorHandling();
            return false;
        }
        $count = $objResult->RecordCount();
        $i = 1;
        $arrDefaultImageSize = false;
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $arrImages = Products::getShopImagesFromBase64String($objResult->fields['picture']);
            // no product picture available
            if (!$arrImages
             || $arrImages[1]['img'] == ''
             || $arrImages[1]['img'] == ShopLibrary::noPictureName) {
                $arrThumbnailPath[$i] = self::$defaultImage;
                if (empty($arrDefaultImageSize)) {
                    $arrDefaultImageSize = getimagesize(ASCMS_SHOP_IMAGES_PATH.'/'.self::$defaultImage);
                    $this->scaleImageSizeToThumbnail($arrDefaultImageSize);
                }
                $arrSize = $arrDefaultImageSize;
            } else {
                if ($arrImages[1]['width'] && $arrImages[1]['height']) {
                    $arrThumbnailPath[$i] = ASCMS_SHOP_IMAGES_WEB_PATH.'/'.
                        ImageManager::getThumbnailFilename($arrImages[1]['img']);
                    // Thumbnail display size
                    $arrSize = array($arrImages[1]['width'], $arrImages[1]['height']);
                } else {
                    $arrThumbnailPath[$i] = ASCMS_SHOP_IMAGES_WEB_PATH.'/'.
                        ImageManager::getThumbnailFilename($arrImages[1]['img']);
                    $arrSize = getimagesize(ASCMS_PATH.$arrThumbnailPath[$i]);
                }
                $this->scaleImageSizeToThumbnail($arrSize);
            }
            $price = $this->_getProductPrice(
                $id,
                0,    // No options yet
                1,    // Apply discount for one article
                true  // Ignore special offers
            );
            if ($objResult->fields['discountprice'] == 0) {
                $arrPrice[$i] = $price;
                $arrDiscountPrice[$i] = '';
            } else {
                $arrPrice[$i] = '<s>'.$price.'</s>';
                $arrDiscountPrice[$i] = $this->_getProductPrice(
                    $id,
                    0,    // No options yet
                    1,    // Apply discount for one article
                    false // Consider special offers
                );
            }
            $arrDetailLink[$i] = 'index.php?section=shop&amp;cmd=details&amp;productId='.$objResult->fields['id'];
            $arrThumbnailSize[$i] = $arrSize[3];
            $arrTitle[$i] = $objResult->fields['title'];
            ++$i;
            $objResult->MoveNext();
        }

        $this->objTemplate->setGlobalVariable(array(
            'TXT_PRICE_NOW'  => $_ARRAYLANG['TXT_PRICE_NOW'],
            'TXT_INSTEAD_OF' => $_ARRAYLANG['TXT_INSTEAD_OF']
        ));

        for ($i = 1; $i <= $count; $i += 2) {
            if (!empty($arrTitle[$i+1])) {
                $this->objTemplate->setCurrentBlock('shopProductRow2');
                $this->objTemplate->setVariable(array(
                    'SHOP_PRODUCT_TITLE'                => str_replace('"', '&quot;', $arrTitle[$i+1]),
                    'SHOP_PRODUCT_THUMBNAIL'            => $arrThumbnailPath[$i+1],
                    'SHOP_PRODUCT_THUMBNAIL_SIZE'     => $arrThumbnailSize[$i+1],
                    'SHOP_PRODUCT_PRICE'                => $arrPrice[$i+1],
                    'SHOP_PRODUCT_DISCOUNTPRICE'        => $arrDiscountPrice[$i+1],
                    'SHOP_PRODUCT_PRICE_UNIT'           => Currency::getActiveCurrencySymbol(),
                    'SHOP_PRODUCT_DISCOUNTPRICE_UNIT'   => Currency::getActiveCurrencySymbol(),
                    'SHOP_PRODUCT_DETAILLINK'           => $arrDetailLink[$i+1],
                    'SHOP_PRODUCT_DISCOUNT_COUNT'     =>
                        Shop::getDiscountCountString($objResult->fields['group_id']),
                ));
                $this->objTemplate->parse('shopProductRow2');
            }
            $this->objTemplate->setCurrentBlock('shopProductRow1');
            $this->objTemplate->setVariable(array(
                'SHOP_PRODUCT_TITLE'                => str_replace('"', '&quot;', $arrTitle[$i]),
                'SHOP_PRODUCT_THUMBNAIL'            => $arrThumbnailPath[$i],
                'SHOP_PRODUCT_THUMBNAIL_SIZE'     => $arrThumbnailSize[$i],
                'SHOP_PRODUCT_PRICE'                => $arrPrice[$i],
                'SHOP_PRODUCT_DISCOUNTPRICE'        => $arrDiscountPrice[$i],
                'SHOP_PRODUCT_PRICE_UNIT'           => Currency::getActiveCurrencySymbol(),
                'SHOP_PRODUCT_DISCOUNTPRICE_UNIT'   => Currency::getActiveCurrencySymbol(),
                'SHOP_PRODUCT_DETAILLINK'           => $arrDetailLink[$i],
                'SHOP_PRODUCT_DISCOUNT_COUNT'     =>
                    Shop::getDiscountCountString($objResult->fields['group_id']),
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
                            " ".Currency::getActiveCurrencySymbol();
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
        if (isset($_SESSION['shop'])) unset($_SESSION['shop']);
        $this->objCustomer = false;
    }


    /**
     * Calculate the total price of all products in the cart
     * @param   array   $cart   The cart array
     * @return  double          The total price
     */
    function _calculatePrice($cart)
    {
//        global $objDatabase;

        $price = 0;
        if (!empty($cart['products'])) {
            $count = 0;
            foreach ($cart['products'] as $arrProduct) {
                $optionPrice = 0;
                //foreach ($arrProduct['options'] as $arrOptionIds) {
                foreach ($arrProduct['options'] as $attribute_id => $arrOptionIds) {
                    $optionPrice = Attributes::getOptionPriceSum($attribute_id, $arrOptionIds);
                }
                $item_price =
                    $this->_getProductPrice(
                        $arrProduct['id'],
                        $optionPrice,
                        $arrProduct['quantity']
                    );
                $price += $item_price * $arrProduct['quantity'];
            }
        }
        return Currency::formatPrice($price);
    }


    /**
     * Returns the actual product price in the active currency, depending on the
     * customer and special offer status.
     *
     * Note that this also sets up several discount information placeholders
     * in the current block of the template.
     * @param   integer $product_id         The Product ID
     * @param   double  $priceOptions       The price for Attributes,
     *                                      if any, or 0 (zero)
     * @param   integer $count              The number of products, defaults
     *                                      to 1 (one)
     * @param   boolean $flagIgnoreSpecialoffer
     *                                      If true, special offers are ignored.
     *                                      This is needed to actually determine
     *                                      both prices in the products view.
     *                                      Defaults to false.
     * @return  double                      The price converted to the active currency
     * @global  array   $_ARRAYLANG         Language array
     * @author    Reto Kohli <reto.kohli@comvation.com>
     */
    function _getProductPrice(
        $product_id, $priceOptions=0, $count=1, $flagIgnoreSpecialoffer=false)
    {
        $objProduct = Product::getById($product_id);
        if (!$objProduct) return false;
        $normalPrice = $objProduct->price();
        $resellerPrice = $objProduct->resellerprice();
        $discountPrice = $objProduct->discountprice();
        $is_special_offer = $objProduct->discount_active();
        $groupCountId = $objProduct->group_id();
        $groupArticleId = $objProduct->article_id();
        if (   !$flagIgnoreSpecialoffer
            && $is_special_offer == 1
            && $discountPrice != 0) {
            $price = $discountPrice;
        } else {
            if (   $this->objCustomer
                && $this->objCustomer->isReseller()
                && $resellerPrice != 0) {
                $price = $resellerPrice;
            } else {
                $price = $normalPrice;
            }
        }
        $price += $priceOptions;
        $rateCustomer = 0;
        if ($this->objCustomer) {
            $rateCustomer = 1e9;
            foreach ($this->objCustomer->getAssociatedGroupIds() as $groupCustomerId) {
                $rateTmp = Discount::getDiscountRateCustomer(
                    $groupCustomerId, $groupArticleId
                );
                if ($rateTmp < $rateCustomer) {
                    $rateCustomer = $rateTmp;
                }
            }
            $price -= ($price * $rateCustomer * 0.01);
        }
        $rateCount = 0;
        if ($count > 0) {
            $rateCount = Discount::getDiscountRateCount($groupCountId, $count);
            $price -= ($price * $rateCount * 0.01);
        }
        $price = Currency::getCurrencyPrice($price);
        return $price;
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
        $shipmentPrice = Shipment::calculateShipmentPrice(
            $shipperId, $price, $weight
        );
        return Currency::getCurrencyPrice($shipmentPrice);
    }


    /**
     * Returns the actual payment fee according to the payment ID and
     * the total order price.
     *
     * @internal    A lot of this belongs to the Payment class.
     * @param       integer     $payment_id The payment ID
     * @param       double      $totalPrice The total order price
     * @return      string                  The payment fee, formatted by
     *                                      {@link Currency::getCurrencyPrice()}
     */
    function _calculatePaymentPrice($payment_id, $totalPrice)
    {
        $paymentPrice = 0;
        if (!$payment_id) return $paymentPrice;
        if (  Payment::getProperty($payment_id, 'free_from') == 0
           || $totalPrice < Payment::getProperty($payment_id, 'free_from')) {
            $paymentPrice = Payment::getProperty($payment_id, 'fee');
        }
        return Currency::getCurrencyPrice($paymentPrice);
    }


    /**
     * Returns the total number of items in the cart
     *
     * @param       array   $cart   The cart array
     * @return      integer         Number of items present in the Cart
     * @internal    Move to Cart class.
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
     * If at least one Product has an upload field Attribute associated
     * with it, the $flagUpload parameter *MUST* be set to true.  Note that this
     * will force the respective product form to use mutipart/form-data encoding
     * and disable the JSON cart for the complete page.
     * @param   boolean $flagUpload         Force the POST cart to be used if true
     * @global  array   $_ARRAYLANG         Language array
     * @global  array   $_CONFIGURATION     Core configuration array, see {@link /config/settings.php}
     * @return  string                      string containung the JavaScript functions
     *
     */
    function getJavascriptCode($flagUpload=false)
    {
        global $_ARRAYLANG, $_CONFIGURATION;

        $javascriptCode =
"<script language=\"JavaScript\" type=\"text/javascript\">
// <![CDATA[
// Obsolete
function viewPicture(picture,features)
{
    window.open(picture,'',features);
}

Shadowbox.loadSkin('classic','lib/javascript/shadowbox/src/skin/');
Shadowbox.loadLanguage('en', 'lib/javascript/shadowbox/src/lang');
Shadowbox.loadPlayer(['flv', 'html', 'iframe', 'img', 'qt', 'swf', 'wmp'], 'lib/javascript/shadowbox/src/player');
window.onload = function() {
    Shadowbox.init();
};

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
        // Only set for attributes with mandatory choice:
        // Types 1 (Radiobutton), 3 (Dropdown menu),
        // 5 (mandatory text), 7 (mandatory file).
        option_name = '';
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

                            case 'text':
                                if (formElement.value != '') {
                                    checkStatus = true;
                                }
                                break;
                            case 'file':
                                if (formElement.value != '') {
                                    checkStatus = true;
                                }
                                break;

                            case 'hidden':
                                option_name = formElement.value;
                                break;
                        }
                    }
                }
            }
        } // end for
        // If the option name is empty, the Product Attribute is not
        // a mandatory choice.
        if (   option_name != \"\"
            && checkStatus == false
            && elType != 'hidden') {
            status = false;
            arrFailOptions.push(option_name);
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
    } else {".
($flagUpload || !$_CONFIGURATION['custom']['shopJsCart']
? "
        return true;
    }
}
" : "
        addProductToCart(objForm);
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

// Remove a single product from the cart
function deleteProduct(product_index)
{
  quantityElement = document.getElementById('quantity-'+product_index);
  if (!quantityElement) return;
  quantityElement.value = 0;
  document.shopForm.submit();
}

function addProductToCart(objForm)
{
    objCart = {products:new Array(),info:{}};
    // Default to one product in case the quantity field is not used
    objProduct = {id:0,options:{},quantity:1}; // Obsolete: ,title:'',info:{}
    productOptionRe = /productOption\\[([0-9]+)\\]/;
    updateProductRe = /updateProduct\\[([0-9]+)\\]/;
    updateProduct = '';

    // Collect basic product information
    for (i = 0; i < document.forms[objForm].getElementsByTagName('input').length; i++) {
        formElement = document.forms[objForm].getElementsByTagName('input')[i];
        if (typeof(formElement.name) != 'undefined') {
            if (formElement.name == 'productId')
                objProduct.id = formElement.value;
            if (formElement.name == 'productTitle')
                objProduct.title = formElement.value;
            if (formElement.name == 'productQuantity')
                objProduct.quantity = formElement.value;
            arrUpdateProduct = updateProductRe.exec(formElement.name);
            if (arrUpdateProduct != null)
                updateProduct = '&updateProduct='+arrUpdateProduct[1];
        }
    }

    // Collect product options
    for (el = 0; el < document.forms[objForm].elements.length; ++el) {
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
                // 20081216 Added Text Attributes -- Reto Kohli
                case 'text':
                    if (formElement.value != '') {
                        objProduct.options[optionId] = formElement.value;
                    }
                    break;
                // File uploads are recognised automatically;
                // no need to add the option ID
                default:
                    break;
            }
        }
    }
    // Create product string
    // 20081217 -- Reto Kohli
    // Fixed encoding of string parameters -- now uses prototype.js
    productStr = Object.toJSON(objProduct);
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
    if (request_active) return false;
    request_active = true;
    if (type == 1) {
        // add product
        objHttp.open('get', 'index.php?section=shop".MODULE_INDEX."&".htmlentities(session_name(), ENT_QUOTES, CONTREXX_CHARSET)."=".htmlentities(session_id(), ENT_QUOTES, CONTREXX_CHARSET)."&cmd=cart&remoteJs=addProduct'+data, true);
        objHttp.onreadystatechange = shopUpdateCart;
    }
    // elseif ..
    //  more requests here...
    //}

    // Optionally show a popup
    //if (data != '') showPopup('popUpLayer');

    objHttp.send(null);
}

// Timeout in ms
var popUpTimeout = 2000;

function showPopup(id)
{
    var obj = document.getElementById(id);
    if (!obj) {
alert('Cannot find element '+id);
        return;
    }
    obj.style.display = 'none';
    var width = parseInt(obj.style.width);
    var height = parseInt(obj.style.height);
    var left = centerX(width);
    var top = centerY(height);
    obj.style.left = left+'px';
    obj.style.top = top+'px';
    obj.style.display = '';
    setTimeout(\"hidePopup('\"+id+\"')\", popUpTimeout);
}

function hidePopup(id)
{
    var obj = document.getElementById(id);
    if (obj) {
        obj.innerHtml = '';
        obj.style.display = 'none';
    }
}

function centerX(width)
{
    var x;
    if (self.innerWidth) {
        // all except Explorer
        x = self.innerWidth;
    }
    else if (document.documentElement
     && document.documentElement.clientWidth) {
        // Explorer 6 Strict Mode
        x = document.documentElement.clientWidth;
    } else {
        // other Explorers
        x = document.body.clientWidth;
    }
    return parseInt((x-width)/2);
}

function centerY(height)
{
    var y;
    if (self.innerHeight) {
        // all eycept Eyplorer
        y = self.innerHeight;
    }
    else if (document.documentElement
     && document.documentElement.clientHeight) {
        // Eyplorer 6 Strict Mode
        y = document.documentElement.clientHeight;
    } else {
        // other Eyplorers
        y = document.body.clientHeight;
    }
    return parseInt((y-height)/2);
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
        self::addMessage($_ARRAYLANG['TXT_DATABASE_QUERY_ERROR']);
    }


    /**
     * Authenticate a Customer
     *
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @return  boolean                 True if the Customer could be
     *                                  authenticated successfully,
     *                                  false otherwise.
     * @access private
     */
    function _authenticate()
    {
        if (   isset($_SESSION['shop']['username'])
            && isset($_SESSION['shop']['password'])) {
            $username = mysql_escape_string($_SESSION['shop']['username']);
            $password = md5(mysql_escape_string($_SESSION['shop']['password']));
            $this->objCustomer = Customer::auth($username, $password);
            if ($this->objCustomer) {
                $_SESSION['shop']['email'] = $this->objCustomer->getEmail();
                return true;
            }
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

        if (empty($_GET['remoteJs'])) {
            $arrProduct = $this->_getPostProduct($oldCartProdId);
        } else {
            $arrProduct = $this->_getJsonProduct($oldCartProdId);
        }

DBG::log("cart(): Got Product: ".var_export($arrProduct, true));

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
            $_SESSION['shop']['cart']['total_price'] = '0.00';
        }
        // check countries
        $_SESSION['shop']['countryId2'] =
            (isset($_POST['countryId2'])
              ? intval($_POST['countryId2'])
              : (empty($_SESSION['shop']['countryId2'])
                  ? SettingDb::getValue('country_id')
                  : $_SESSION['shop']['countryId2']
                )
            );
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
        }
        if (isset($_REQUEST['updateProduct'])) {
            $oldCartProdId = intval($_REQUEST['updateProduct']);
        }
        $objJson = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        $strProduct = contrexx_stripslashes($_REQUEST['product']);
//ShopLibrary::addLog("Product Stripped: $strProduct");
        $arrProduct = $objJson->decode($strProduct);
//ShopLibrary::addLog('Decoded Product: '.var_export($arrProduct, true));
        return $arrProduct;
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
                'unit'       => Currency::getActiveCurrencySymbol()
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
     * @internal    Documentation: Be more elaborate about the meaning of $oldCartProdId
     */
    function _getPostProduct(&$oldCartProdId)
    {
        $arrProduct = array();

        if (isset($_REQUEST['updateProduct']) && is_array($_REQUEST['updateProduct'])) {
            $keys = array_keys($_REQUEST['updateProduct']);
            $oldCartProdId = intval($keys[0]);
        }

        if (isset($_REQUEST['productId'])) {
            $arrOptions = array();
            // Add names of uploaded files to options array, so they will be
            // recognized and added to the product in the cart
            if (!empty($_FILES['productOption']['name'])) {
                $arrOptions = $_FILES['productOption']['name'];
            }
            if (!empty($_POST['productOption'])) {
                $arrOptions = $arrOptions + $_POST['productOption'];
            }
            $arrProduct = array(
                'id'       => intval($_REQUEST['productId']),
                'options'  => $arrOptions,
                'quantity' => (!empty($_POST['productQuantity'])
                    ? $_POST['productQuantity'] : 1
                ),
            );
        }
        return $arrProduct;
    }


    function _addProductToCart($arrNewProduct, $oldCartProdId=null)
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
                            $arrProductOptions = array();
                            foreach ($arrNewProduct['options'] as $attribute_id => $value) {
                                if (empty($value)) {
                                    continue;
                                }
                                $arrProductOptions[] = $attribute_id;
                            }
                            // check for the same options
                            if ($arrCartProductOptions == $arrProductOptions) {
                                // check for the same option values
                                foreach ($arrNewProduct['options'] as $attribute_id => $value) {
                                    if (empty($value)) continue;
                                    if (is_array($arrProduct['options'][$attribute_id])) {
                                        $arrPostValues = array();
                                        if (is_array($value)) {
                                            $arrPostValues = array_values($value);
                                        } else {
                                            array_push($arrPostValues, $value);
                                        }
                                        if ($arrPostValues != $arrProduct['options'][$attribute_id]) {
                                            continue 2;
                                        }
                                    } else {
                                        if (!isset($arrProduct['options'][$attribute_id][$value])) {
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
            // Do not add zero or negative quantities
            $quantity = intval($arrNewProduct['quantity']);
            if ($quantity <= 0) return;

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
                foreach ($arrNewProduct['options'] as $attribute_id => $option_id) {
                    // Get Attribute
                    $objAttribute = Attribute::getById($attribute_id);
                    if (!$objAttribute) continue;
                    $type = $objAttribute->getType();
                    if (   $type == Attribute::TYPE_TEXT_OPTIONAL
                        || $type == Attribute::TYPE_TEXT_MANDATORY) {
                        if ($option_id == '') continue;
                    }
                    if (   $type == Attribute::TYPE_UPLOAD_OPTIONAL
                        || $type == Attribute::TYPE_UPLOAD_MANDATORY) {
                        $option_id = $this->uploadFile($attribute_id);
                        if ($option_id == '') {
                            continue;
                        }
                    }
                    if (!isset($_SESSION['shop']['cart']['products'][$cartProdId]['options'][$attribute_id])) {
                        $_SESSION['shop']['cart']['products'][$cartProdId]['options'][intval($attribute_id)] = array();
                    }
                    if (is_array($option_id) && count($option_id) != 0) {
                        foreach ($option_id as $id) {
                            array_push($_SESSION['shop']['cart']['products'][$cartProdId]['options'][intval($attribute_id)], $id);
                        }
                    } elseif (!empty($option_id)) {
                        array_push($_SESSION['shop']['cart']['products'][$cartProdId]['options'][intval($attribute_id)],
                            contrexx_stripslashes($option_id));
                    }
                }
            }
        }
    }


    function _updateCartProductsQuantity()
    {
        // Update quantity to cart
        if (empty($_SESSION['shop']['cart']['products'])) return;
        foreach (array_keys($_SESSION['shop']['cart']['products']) as $cartId) {
            // Remove Products
            if (isset($_REQUEST['quantity'][$cartId])) {
                if (intval($_REQUEST['quantity'][$cartId] < 1)) {
                    unset($_SESSION['shop']['cart']['products'][$cartId]);
                } else {
                    $_SESSION['shop']['cart']['products'][$cartId]['quantity'] =
                        intval($_REQUEST['quantity'][$cartId]);
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
     * Generates an array with all products that are in the cart and returns
     * them in an array.
     * Additionally it also computes the new count of total products in the
     * cart and calculates the total amount.
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @return  array                             Array with all products
     *                                            that are stored in the cart
     */
    function _parseCart()
    {
        global $objDatabase, $_CONFIG;

        $arrProducts      = array();
        $total_price      = 0;
        $total_vat_amount = 0;
        $total_weight     = 0;
        // No shipment by default.  Only if at least one Product with
        // type "delivery" is encountered, it is switched on.
        $_SESSION['shop']['shipment'] = false;

        if (is_array($_SESSION['shop']['cart']['products']) && !empty($_SESSION['shop']['cart']['products'])) {
            foreach ($_SESSION['shop']['cart']['products'] as $cartProdId => $arrProduct) {
                $objProduct = Product::getById($arrProduct['id']);
                if (!$objProduct) {
                    unset($_SESSION['shop']['cart']['products'][$cartProdId]);
                    continue;
                }
                $productOptions      = '';
                $productOptionsPrice =  0;
                // get option names
                foreach ($_SESSION['shop']['cart']['products'][$cartProdId]['options'] as $attribute_id => $arrOptionIds) {
                    $objAttribute = Attribute::getById($attribute_id);
                    // Should be tested!
                    if (!$objAttribute) {
                        unset($_SESSION['shop']['cart']['products'][$cartProdId]['options'][$attribute_id]);
                        continue;
                    }
                    $arrOptions = $objAttribute->getOptionArray();
                    foreach ($arrOptionIds as $option_id) {
                        // Note that the options are indexed starting from 1!
                        // For types 4..7, the value entered in the text box is
                        // stored in $option_id.  Overwrite the value taken from
                        // the database.
                        if ($objAttribute->getType() >= Attribute::TYPE_TEXT_OPTIONAL) {
                            $arrOption = current($arrOptions);
                            $arrOption['value'] = $option_id;
                        } else {
                            $arrOption = $arrOptions[$option_id];
                        }
                        if (!is_array($arrOption)) continue;
                        $optionValue = ShopLibrary::stripUniqidFromFilename($arrOption['value']);
                        if (   $optionValue != $arrOption['value']
                            && file_exists(ASCMS_PATH.'/'.$this->uploadDir.'/'.$arrOption['value'])) {
                                $optionValue =
                                    '<a href="'.$this->uploadDir.'/'.$arrOption['value'].
                                    '" target="uploadimage">'.$optionValue.'</a>';
                        }
                        $productOptions .= " [$optionValue]";
                        $productOptionsPrice += $arrOption['price'];
                    }
                }
                if ($productOptionsPrice != 0) {
                    $_SESSION['shop']['cart']['products'][$cartProdId]['optionPrice'] =
                        $productOptionsPrice;
                }
                $quantity = $_SESSION['shop']['cart']['products'][$cartProdId]['quantity'];
                $itemprice = $this->_getProductPrice(
                    $arrProduct['id'],
                    $productOptionsPrice,
                    $quantity
                );
                $price      = $itemprice * $quantity;
                $handler = $objProduct->distribution();
                $itemweight =
                    ($handler == 'delivery' ? $objProduct->weight() : 0);
                $weight     = $itemweight * $quantity;
                $vat_rate   = Vat::getRate($objProduct->vat_id());
                // calculate the amount if it's excluded; we'll add it later.
                // if it's included, we don't care.
                // if it's disabled, it's set to zero.
                $vat_amount = Vat::amount($vat_rate, $price);

                $total_price      += $price;
                $total_vat_amount += $vat_amount;
                $total_weight     += $weight;

                array_push($arrProducts, array(
                    'id'             => $arrProduct['id'],
                    'product_id'     => $objProduct->code(),
                    'cart_id'        => $cartProdId,
                    'title'          =>
                        (empty($_GET['remoteJs'])
                          ? $objProduct->name()
                          : htmlspecialchars(
                              (strtolower($_CONFIG['coreCharacterEncoding']) == 'utf-8'
                                ? $objProduct->name()
                                : utf8_encode($objProduct->name())
                              ),
                              ENT_QUOTES, CONTREXX_CHARSET
                            )
                        ),
                    'options'        => $productOptions,
                    'price'          => Currency::formatPrice($price),
//                    'price_unit'     => Currency::getActiveCurrencySymbol(),
                    'quantity'       => $quantity,
                    'itemprice'      => Currency::formatPrice($itemprice),
//                    'itemprice_unit' => Currency::getActiveCurrencySymbol(),
                    'percent'        => $vat_rate,
                    'vat_amount'     => Currency::formatPrice($vat_amount),
                    'itemweight'     => $itemweight, // in grams!
                    'weight'         => $weight,
                    'group_id' => $objProduct->group_id(),
                    'article_id' => $objProduct->article_id(),
                ));
                // require shipment if the distribution type is 'delivery'
                if ($objProduct->distribution() == 'delivery') {
                    $_SESSION['shop']['shipment'] = true;
                }
            }
        }

        $total_discount_amount = 0;
        $coupon_code = (isset($_SESSION['shop']['coupon_code'])
            ? $_SESSION['shop']['coupon_code'] : '');
        $payment_id = (isset($_SESSION['shop']['paymentId'])
            ? $_SESSION['shop']['paymentId'] : 0);
        // Either the payment ID or the code are needed
        if ($payment_id || $coupon_code) {
            $customer_id = ($this->objCustomer ? $this->objCustomer->getId() : 0);
//DBG::log("Shop::_parseCart(): GLOBAL; Got Coupon code $coupon_code");
            $objCoupon = Coupon::get(
                $coupon_code, $total_price,  $customer_id, 0, $payment_id);
//            if ($objCoupon) {
//DBG::log("Shop::_parseCart(): GLOBAL; Got Coupon ".var_export($objCoupon, true));
                // This Coupon applies to the whole order
//                $discount_amount = $objCoupon->getDiscountAmount($total_price);
//                $total_discount_amount += $discount_amount;
//                $total_price -= $discount_amount;
//DBG::log("Shop::_parseCart(): GLOBAL; total price $total_price, discount_amount $discount_amount, total discount $total_discount_amount");
//            }
//DBG::log("Shop::_parseCart(): Got Coupon code $coupon_code");
            foreach ($arrProducts as $cartProdId => $arrProduct) {
                if (!$objCoupon)
                    $objCoupon = Coupon::get(
                        $coupon_code, $total_price, $customer_id,
                        $arrProduct['id'], $payment_id);
                if ($objCoupon) {
//DBG::log("Shop::_parseCart(): Got Coupon ".var_export($objCoupon, true));
                    $discount_amount = $objCoupon->getDiscountAmount($arrProduct['price']);
                    $total_discount_amount += $discount_amount;
                    $total_price -= $discount_amount;
                    $arrProduct['price'] -= $discount_amount;
                    $arrProduct['discount_amount'] = $discount_amount;
                    $arrProduct['coupon_string'] = $objCoupon->getString();
//DBG::log("Shop::_parseCart(): price ".$arrProduct['price'].", discount_amount $discount_amount, total $total_discount_amount");
                }
                if ($objCoupon && $objCoupon->product_id()) {
                    // This Coupon applies to that Product *only*
                    $objCoupon = null;
                }
            }
        }

        $_SESSION['shop']['total_discount_amount'] = $total_discount_amount;
        $_SESSION['shop']['cart']['total_price']      = Currency::formatPrice($total_price);//$this->_calculatePrice($_SESSION['shop']['cart']);
        $_SESSION['shop']['cart']['total_vat_amount'] = Currency::formatPrice($total_vat_amount);
        // Round prices to 5 cents if the currency is CHF (*MUST* for Saferpay)
        if (Currency::getActiveCurrencyCode() == 'CHF') {
            $_SESSION['shop']['cart']['total_price']      = Currency::formatPrice(round(20*$total_price)/20);
            $_SESSION['shop']['cart']['total_vat_amount'] = Currency::formatPrice(round(20*$total_vat_amount)/20);
        }
        $_SESSION['shop']['cart']['items']            = $this->calculateItems($_SESSION['shop']['cart']);
        $_SESSION['shop']['cart']['total_weight']     = $total_weight; // in grams!
        $this->_setPaymentTaxes();
        return $arrProducts;
    }


    /**
     * Show parsed cart
     *
     * Generate the shop cart page (?section=shop&cmd=cart).
     * @global  array $_ARRAYLANG   Language array
     * @param   array $arrProducts  Array with all the products taken from the cart,
     *                              see {@link _parseCart()}
     */
    function _showCart($arrProducts)
    {
        global $_ARRAYLANG;

        // hide currency navbar
        // $this->_hideCurrencyNavbar=true;
        $i = 0;
        if (count($arrProducts)) {
            foreach ($arrProducts as $arrProduct) {
                $groupCountId = $arrProduct['group_id'];
                $groupArticleId = $arrProduct['article_id'];
                $groupCustomerId = 0;
                if ($this->objCustomer) {
                    $groupCustomerId = $this->objCustomer->getGroupId();
                }
                $this->showDiscountInfo(
                    $groupCustomerId, $groupArticleId,
                    $groupCountId, $arrProduct['quantity']
                );

                if (isset($arrProduct['discount_string'])) {
//DBG::log("Shop::_showCart(): Product ID ".$arrProduct['id'].": ".$arrProduct['discount_string']);
                    $this->objTemplate->setVariable(array(
                        'SHOP_DISCOUNT_COUPON_STRING' =>
                            $arrProduct['discount_string'],
                    ));
                }

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
                    'SHOP_PRODUCT_PRICE_UNIT'     => Currency::getActiveCurrencySymbol(),
                    'SHOP_PRODUCT_QUANTITY'       => $arrProduct['quantity'],
                    'SHOP_PRODUCT_ITEMPRICE'      => $arrProduct['itemprice'],
                    'SHOP_PRODUCT_ITEMPRICE_UNIT' => Currency::getActiveCurrencySymbol(),
                ));
                if (SettingDb::getValue('weight_enable')) {
                    $this->objTemplate->setVariable(array(
                        'SHOP_PRODUCT_WEIGHT' => Weight::getWeightString($arrProduct['weight']),
                        'TXT_WEIGHT'          => $_ARRAYLANG['TXT_TOTAL_WEIGHT'],
                    ));
                }
                if (Vat::isEnabled()) {
                    $this->objTemplate->setVariable(array(
                        // avoid a lonely '%' percent sign in case 'percent' is unset
                        'SHOP_PRODUCT_TAX_RATE'       =>
                            ($arrProduct['percent']
                                ? Vat::format($arrProduct['percent'])
                                : '-'
                            ),
                        'SHOP_PRODUCT_TAX_AMOUNT'     =>
                            '('.$arrProduct['vat_amount'].'&nbsp;'.
                            Currency::getActiveCurrencySymbol().')',
                    ));
                }
                $this->objTemplate->parse('shopCartRow');
            }
        } else {
            $this->objTemplate->hideBlock('shopCart');
        }

        $this->objTemplate->setGlobalVariable(array(
            'TXT_PRODUCT_ID'               => $_ARRAYLANG['TXT_ID'],
            'TXT_SHOP_PRODUCT_CUSTOM_ID'   => $_ARRAYLANG['TXT_SHOP_PRODUCT_CUSTOM_ID'],
            'TXT_PRODUCT'                  => $_ARRAYLANG['TXT_PRODUCT'],
            'TXT_UNIT_PRICE'               => $_ARRAYLANG['TXT_UNIT_PRICE'],
            'TXT_QUANTITY'                 => $_ARRAYLANG['TXT_QUANTITY'],
            'TXT_TOTAL'                    => $_ARRAYLANG['TXT_TOTAL'],
            'TXT_INTER_TOTAL'              => $_ARRAYLANG['TXT_INTER_TOTAL'],
            'TXT_UPDATE'                   => $_ARRAYLANG['TXT_UPDATE'],
            'TXT_EMPTY_CART'               => $_ARRAYLANG['TXT_EMPTY_CART'],
            'TXT_CONTINUE_SHOPPING'        => $_ARRAYLANG['TXT_CONTINUE_SHOPPING'],
            'SHOP_PRODUCT_TOTALITEM'       => $_SESSION['shop']['cart']['items'],
            'SHOP_PRODUCT_TOTALPRICE'      => Currency::formatPrice(
                  $_SESSION['shop']['cart']['total_price']),
            'SHOP_PRODUCT_TOTALPRICE_UNIT' => Currency::getActiveCurrencySymbol(),
            'SHOP_TOTAL_WEIGHT'            => Weight::getWeightString($_SESSION['shop']['cart']['total_weight']),
            'SHOP_PRICE_UNIT' => Currency::getActiveCurrencySymbol(),
        ));

        if (!empty($_SESSION['shop']['total_discount_amount'])) {
            $total_discount_amount = $_SESSION['shop']['total_discount_amount'];
//DBG::log("Shop::_showCart(): Total: Amount $total_discount_amount");
            $this->objTemplate->setVariable(array(
//                'SHOP_DISCOUNT_COUPON_TOTAL_AMOUNT' => $coupon_string,
                'SHOP_DISCOUNT_COUPON_TOTAL' =>
                    $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_AMOUNT_TOTAL'],
                'SHOP_DISCOUNT_COUPON_TOTAL_AMOUNT' => Currency::formatPrice(
                    -$total_discount_amount),
            ));
        }

        if (Vat::isEnabled()) {
            $this->objTemplate->setVariable(array(
                'TXT_TAX_PREFIX'               =>
                    (Vat::isIncluded()
                        ? $_ARRAYLANG['TXT_SHOP_VAT_PREFIX_INCL']
                        : $_ARRAYLANG['TXT_SHOP_VAT_PREFIX_EXCL']
                    ),
                // Removed parenthesess for 2.0.2
                // Add them to the template if desired!
                'SHOP_TOTAL_TAX_AMOUNT'        =>
                    $_SESSION['shop']['cart']['total_vat_amount']
                    .'&nbsp;'.Currency::getActiveCurrencySymbol(),

            ));
        }
        if ($_SESSION['shop']['shipment']) {
            $this->objTemplate->setVariable(array(
                'TXT_SHIP_COUNTRY'    => $_ARRAYLANG['TXT_SHIP_COUNTRY'],
                'SHOP_COUNTRIES_MENU' =>
                    Country::getMenu(
                        'countryId2', $_SESSION['shop']['countryId2'],
                        true, "document.forms['shopForm'].submit()"),
            ));
        }
        if (   SettingDb::getValue('orderitems_amount_max') > 0
            && SettingDb::getValue('orderitems_amount_max') <
                  $_SESSION['shop']['cart']['total_price']
        ) {
            $this->objTemplate->setVariable(
                'TXT_SHOP_NOTE_AMOUNT_LIMIT_REACHED',
                    sprintf(
                        $_ARRAYLANG['TXT_SHOP_ORDERITEMS_AMOUNT_MAX'],
                        Currency::formatPrice(
                            SettingDb::getValue('orderitems_amount_max')),
                        Currency::getActiveCurrencySymbol()));
        } else {
            $this->objTemplate->setVariable(
                'TXT_NEXT', $_ARRAYLANG['TXT_NEXT']);
        }
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

        $redirect = Shop::processRedirect();

        $loginUsername = '';
        if (!empty($_POST['username']) && !empty($_POST['password'])) {
            // check authentification
            $_SESSION['shop']['username'] = htmlspecialchars(
                addslashes(strip_tags($_POST['username'])),
                ENT_QUOTES, CONTREXX_CHARSET);
            $_SESSION['shop']['password'] =
                addslashes(strip_tags($_POST['password']));
            $loginUsername = $_SESSION['shop']['username'];
            if ($this->_authenticate()) {
                // Initialize the Customer data in the session, so that the account
                // page may be skipped
                $_SESSION['shop']['company']    = $this->objCustomer->getCompany();
                $_SESSION['shop']['prefix']     = $this->objCustomer->getPrefix();
                $_SESSION['shop']['lastname']   = $this->objCustomer->getLastname();
                $_SESSION['shop']['firstname']  = $this->objCustomer->getFirstname();
                $_SESSION['shop']['address']    = $this->objCustomer->getAddress();
                $_SESSION['shop']['zip']        = $this->objCustomer->getZip();
                $_SESSION['shop']['city']       = $this->objCustomer->getCity();
                $_SESSION['shop']['countryId']  = $this->objCustomer->getCountryId();
                $_SESSION['shop']['email']      = $this->objCustomer->getEmail();
                $_SESSION['shop']['phone']      = $this->objCustomer->getPhone();
                $_SESSION['shop']['fax']        = $this->objCustomer->getFax();
                // Optionally also initialize the shipment address
                /*
                $_SESSION['shop']['company2']   = $this->objCustomer->getCompany();
                $_SESSION['shop']['prefix2']    = $this->objCustomer->getPrefix();
                $_SESSION['shop']['lastname2']  = $this->objCustomer->getLastname();
                $_SESSION['shop']['firstname2'] = $this->objCustomer->getFirstname();
                $_SESSION['shop']['address2']   = $this->objCustomer->getAddress();
                $_SESSION['shop']['zip2']       = $this->objCustomer->getZip();
                $_SESSION['shop']['city2']      = $this->objCustomer->getCity();
                $_SESSION['shop']['countryId2'] = $this->objCustomer->getCountryId();
                $_SESSION['shop']['phone2']     = $this->objCustomer->getPhone();
                $_SESSION['shop']['equalAddress'] = ' checked="checked"';
                */
                // The user has just been logged in.
                // Refresh the cart, considering possible discounts
                $this->_parseCart();
                // Reenter login() in order to be redirected to the next page
                $this->login();
            } else {
                self::addMessage($_ARRAYLANG['TXT_SHOP_UNKNOWN_CUSTOMER_ACCOUNT']);
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
            'SHOP_LOGIN_ACTION'                  =>
                'index.php?section=shop&amp;cmd=login'.
                (!empty($redirect) ? "&amp;redirect=$redirect" : ''),
            // Should be replaced by the global SHOP_STATUS placeholder.
            'SHOP_LOGIN_STATUS'                  => self::$statusMessage,
        ));
    }


    function processRedirect()
    {
        $redirect = (isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : '');
        // The Customer object is initialized upon successful authentication.
        if (!$this->objCustomer) return $redirect;

        // Redirect if the login was completed successfully:
//        // Special redirects
//        if ($redirect == 'shop') {
//            header('Location: index.php?section=shop');
//            exit;
//        }
//         //Add more special redirects here as needed.
        // General redirects, base64 encoded
        if (!empty($redirect)) {
            $decodedRedirect = base64_decode($redirect);
            if (!empty($decodedRedirect)) {
                header('Location: index.php?'.$decodedRedirect);
                exit;
            }
        }
        // Default: Redirect to the account page
        header('Location: index.php?section=shop&cmd=account');
        exit;
    }


    function account()
    {
        $this->_configAccount();
        $status = '';
        // Only verify the form *after* it has been posted
        if (   isset($_POST['lastname'])) {
            $status = $this->_checkAccountForm();
            if (empty($status)) $this->_gotoPaymentPage();
        }
        $this->showAccount($status);
    }


    function _configAccount()
    {
        // hide currency navbar
        $this->_hideCurrencyNavbar = true;

        if (empty($_POST) || !is_array($_POST)) return;
        foreach ($_POST as $key => $value) {
            $_SESSION['shop'][$key] =
                trim(strip_tags(contrexx_stripslashes($value)));
        }

        if (   empty($_SESSION['shop']['prefix2'])
            || empty($_SESSION['shop']['lastname2'])
            || empty($_SESSION['shop']['firstname2'])
            || empty($_SESSION['shop']['address2'])
            || empty($_SESSION['shop']['zip2'])
            || empty($_SESSION['shop']['city2'])
            || empty($_SESSION['shop']['phone2'])
            || empty($_SESSION['shop']['countryId2'])
        ) {
            $_SESSION['shop']['equalAddress'] = '';
        } else {
            if (!empty($_POST['equalAddress'])) {
                // Copy address
                $_SESSION['shop']['company2'] = $_SESSION['shop']['company'];
                $_SESSION['shop']['prefix2'] = $_SESSION['shop']['prefix'];
                $_SESSION['shop']['lastname2'] = $_SESSION['shop']['lastname'];
                $_SESSION['shop']['firstname2'] = $_SESSION['shop']['firstname'];
                $_SESSION['shop']['address2'] = $_SESSION['shop']['address'];
                $_SESSION['shop']['zip2'] = $_SESSION['shop']['zip'];
                $_SESSION['shop']['city2'] = $_SESSION['shop']['city'];
                $_SESSION['shop']['phone2'] = $_SESSION['shop']['phone'];
                $_SESSION['shop']['countryId2'] = $_SESSION['shop']['countryId'];
                $_SESSION['shop']['equalAddress'] = ' checked="checked"';
            }
        }
        if (empty($_SESSION['shop']['countryId'])) {
// TODO: Verify this
            // countryId2 is set in _initCart() already
            $_SESSION['shop']['countryId'] = $_SESSION['shop']['countryId2'];
        }

        // Fill missing arguments with empty strings
        if (empty($_SESSION['shop']['company2']))   $_SESSION['shop']['company2']   = '';
        if (empty($_SESSION['shop']['prefix2']))    $_SESSION['shop']['prefix2']    = '';
        if (empty($_SESSION['shop']['lastname2']))  $_SESSION['shop']['lastname2']  = '';
        if (empty($_SESSION['shop']['firstname2'])) $_SESSION['shop']['firstname2'] = '';
        if (empty($_SESSION['shop']['address2']))   $_SESSION['shop']['address2']   = '';
        if (empty($_SESSION['shop']['zip2']))       $_SESSION['shop']['zip2']       = '';
        if (empty($_SESSION['shop']['city2']))      $_SESSION['shop']['city2']      = '';
        if (empty($_SESSION['shop']['phone2']))     $_SESSION['shop']['phone2']     = '';
        if (empty($_SESSION['shop']['countryId2'])) $_SESSION['shop']['countryId2'] = 0;
    }


    function _checkAccountForm()
    {
        global $_ARRAYLANG;

        // initialise variables
        $status = '';

        // Verify the form
        // Note that the Country IDs are either set before or
        // come from a dropdown menu
        if (empty($_SESSION['shop']['prefix']) ||
            empty($_SESSION['shop']['lastname']) ||
            empty($_SESSION['shop']['firstname']) ||
            empty($_SESSION['shop']['address']) ||
            empty($_SESSION['shop']['zip']) ||
            empty($_SESSION['shop']['city']) ||
//            empty($_SESSION['shop']['countryId']) ||
            empty($_SESSION['shop']['phone']) ||
            $_SESSION['shop']['shipment'] &&
            (empty($_SESSION['shop']['prefix2']) ||
            empty($_SESSION['shop']['lastname2']) ||
            empty($_SESSION['shop']['firstname2']) ||
            empty($_SESSION['shop']['address2']) ||
            empty($_SESSION['shop']['zip2']) ||
            empty($_SESSION['shop']['city2']) ||
//            empty($_SESSION['shop']['countryId2']) ||
            empty($_SESSION['shop']['phone2'])) ||
            (empty($_SESSION['shop']['email']) && !$this->objCustomer) ||
            (empty($_SESSION['shop']['password']) && !$this->objCustomer)
        ) {
            $status = $_ARRAYLANG['TXT_FILL_OUT_ALL_REQUIRED_FIELDS'];
        }

        if (!empty($_POST['password']) && !$this->objCustomer) {
            if (strlen(trim($_POST['password'])) < 6) {
                $status .= '<br />'.$_ARRAYLANG['TXT_INVALID_PASSWORD'];
            }
        }

        if (!empty($_POST['email']) && !$this->objCustomer) {
            if (!FWValidator::isEmail($_POST['email'])) {
                $status .= '<br />'.$_ARRAYLANG['TXT_INVALID_EMAIL_ADDRESS'];
            }
            if (!$this->_checkEmailIntegrity($_POST['email'])) {
                $_POST['email'] = '';
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

        if (!isset($_SESSION['shop']['customer_note'])) {
            $_SESSION['shop']['customer_note'] = '';
        }
        if (!isset($_SESSION['shop']['agb'])) {
            $_SESSION['shop']['agb'] = '';
        }

// TODO: Use ContentManager class to find the page
        $query = "SELECT catid FROM ".DBPREFIX."content_navigation AS nav, ".
            DBPREFIX."modules AS modules ".
            "WHERE modules.name='shop' AND nav.module=modules.id ".
            "AND nav.cmd='payment' AND activestatus='1' AND lang=".FRONTEND_LANG_ID;
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


    function showAccount($status)
    {
        global $_ARRAYLANG;

        $this->objTemplate->setVariable($_ARRAYLANG
          + array(
            'SHOP_ACCOUNT_STATUS' => $status,
            'SHOP_ACCOUNT_ACTION' => "index.php?section=shop".MODULE_INDEX."&amp;cmd=account"
        ));
        // Customer already logged in.
        // Show the details stored in the database as default values.
        // Once the (changed) values are posted back, they are stored
        // in the session!
        if ($this->objCustomer && empty($_SESSION['shop']['address'])) {
            $company = $this->objCustomer->getCompany();
            $prefix  = $this->objCustomer->getTitle();
            $lastname = $this->objCustomer->getLastname();
            $firstname = $this->objCustomer->getFirstname();
            $address = $this->objCustomer->getAddress();
            $zip = $this->objCustomer->getZip();
            $city = $this->objCustomer->getCity();
            // New template - since 2.1.0
            $country_id = $this->objCustomer->getCountryId();
            $email = $this->objCustomer->getEmail();
            $phone = $this->objCustomer->getPhone();
            $fax = $this->objCustomer->getFax();
        } else {
            // The $_SESSION fields may still be undefined!
            $company = (isset($_SESSION['shop']['company'])
                ? $_SESSION['shop']['company'] : '');
            $prefix = (isset($_SESSION['shop']['prefix'])
                ? $_SESSION['shop']['prefix'] : '');
            $lastname = (isset($_SESSION['shop']['lastname'])
                ? $_SESSION['shop']['lastname'] : '');
            $firstname = (isset($_SESSION['shop']['firstname'])
                ? $_SESSION['shop']['firstname'] : '');
            $address = (isset($_SESSION['shop']['address'])
                ? $_SESSION['shop']['address'] : '');
            $zip = (isset($_SESSION['shop']['zip'])
                ? $_SESSION['shop']['zip'] : '');
            $city = (isset($_SESSION['shop']['city'])
                ? $_SESSION['shop']['city'] : '');
            $country_id = (isset($_SESSION['shop']['countryId'])
                ? $_SESSION['shop']['countryId'] : 0);
            $email = (isset($_SESSION['shop']['email'])
                ? $_SESSION['shop']['email'] : '');
            $phone = (isset($_SESSION['shop']['phone'])
                ? $_SESSION['shop']['phone'] : '');
            $fax = (isset($_SESSION['shop']['fax'])
                ? $_SESSION['shop']['fax'] : '');
        }
        $this->objTemplate->setVariable(array(
            'SHOP_ACCOUNT_COMPANY'   => htmlentities($company, ENT_QUOTES, CONTREXX_CHARSET),
            'SHOP_ACCOUNT_PREFIX'    => htmlentities($prefix, ENT_QUOTES, CONTREXX_CHARSET),
            'SHOP_ACCOUNT_LASTNAME'  => htmlentities($lastname, ENT_QUOTES, CONTREXX_CHARSET),
            'SHOP_ACCOUNT_FIRSTNAME' => htmlentities($firstname, ENT_QUOTES, CONTREXX_CHARSET),
            'SHOP_ACCOUNT_ADDRESS'   => htmlentities($address, ENT_QUOTES, CONTREXX_CHARSET),
            'SHOP_ACCOUNT_ZIP'       => htmlentities($zip, ENT_QUOTES, CONTREXX_CHARSET),
            'SHOP_ACCOUNT_CITY'      => htmlentities($city, ENT_QUOTES, CONTREXX_CHARSET),
            'SHOP_ACCOUNT_EMAIL'     => htmlentities($email, ENT_QUOTES, CONTREXX_CHARSET),
            'SHOP_ACCOUNT_PHONE'     => htmlentities($phone, ENT_QUOTES, CONTREXX_CHARSET),
            'SHOP_ACCOUNT_FAX'       => htmlentities($fax, ENT_QUOTES, CONTREXX_CHARSET),
            'SHOP_ACCOUNT_ACTION'    => "index.php?section=shop".MODULE_INDEX."&amp;cmd=account",
            // New template - since 2.1.0
            'SHOP_ACCOUNT_COUNTRY_MENUOPTIONS' =>
                Country::getMenuoptions($country_id, true),
            // Old template
            // Compatibility with 2.0 and older versions
            'SHOP_ACCOUNT_COUNTRY'   => Country::getMenu('countryId', $country_id),
        ));

        if (empty($_SESSION['shop']['shipment'])) {
            $this->objTemplate->hideBlock('shopShipmentAddress');
        } else {
            $this->objTemplate->setVariable(array(
                'SHOP_ACCOUNT_COMPANY2'      => (empty($_SESSION['shop']['company2'])
                    ? '' : htmlentities($_SESSION['shop']['company2'], ENT_QUOTES, CONTREXX_CHARSET)),
                'SHOP_ACCOUNT_PREFIX2'       => (empty($_SESSION['shop']['prefix2'])
                    ? '' : htmlentities($_SESSION['shop']['prefix2'], ENT_QUOTES, CONTREXX_CHARSET)),
                'SHOP_ACCOUNT_LASTNAME2'     => (empty($_SESSION['shop']['lastname2'])
                    ? '' : htmlentities($_SESSION['shop']['lastname2'], ENT_QUOTES, CONTREXX_CHARSET)),
                'SHOP_ACCOUNT_FIRSTNAME2'    => (empty($_SESSION['shop']['firstname2'])
                    ? '' : htmlentities($_SESSION['shop']['firstname2'], ENT_QUOTES, CONTREXX_CHARSET)),
                'SHOP_ACCOUNT_ADDRESS2'      => (empty($_SESSION['shop']['address2'])
                    ? '' : htmlentities($_SESSION['shop']['address2'], ENT_QUOTES, CONTREXX_CHARSET)),
                'SHOP_ACCOUNT_ZIP2'          => (empty($_SESSION['shop']['zip2'])
                    ? '' : htmlentities($_SESSION['shop']['zip2'], ENT_QUOTES, CONTREXX_CHARSET)),
                'SHOP_ACCOUNT_CITY2'         => (empty($_SESSION['shop']['city2'])
                    ? '' : htmlentities($_SESSION['shop']['city2'], ENT_QUOTES, CONTREXX_CHARSET)),
                'SHOP_ACCOUNT_COUNTRY2'      =>
                    Country::getNameById($_SESSION['shop']['countryId2']),
                'SHOP_ACCOUNT_COUNTRY2_ID'   => $_SESSION['shop']['countryId2'],
                'SHOP_ACCOUNT_PHONE2'        => (empty($_SESSION['shop']['phone2'])
                    ? '' : htmlentities($_SESSION['shop']['phone2'], ENT_QUOTES, CONTREXX_CHARSET)),
                'SHOP_ACCOUNT_EQUAL_ADDRESS' => (empty($_SESSION['shop']['equalAddress'])
                    ? '' : HTML_ATTRIBUTE_CHECKED),
                'SHOP_SHIPPING_ADDRESS_DISPLAY' => (empty($_SESSION['shop']['equalAddress'])
                    ? 'block' : 'none'),
            ));
        }
        if ($this->objCustomer)
            $this->objTemplate->hideBlock('account_details');
    }


    /**
     * Set up payment page including dropdown menus for shipment and payment options.
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
        if (!$this->verifySessionAddress()) {
            header("Location: index.php?section=shop".MODULE_INDEX);
            exit;
        }
        // call first, because the _initPaymentDetails method requires the
        // shipmentId which is stored in the session array by this.
        $this->_getShipperMenu();
        $this->_initPaymentDetails();
//        $this->_setPaymentTaxes();
        $paymentStatus = $this->_checkPaymentDetails();
        $this->_getPaymentPage($paymentStatus);
    }


    function _initPaymentDetails()
    {
        // Coupon code
        if (isset($_POST['coupon_code'])) {
            $_SESSION['shop']['coupon_code'] =
                contrexx_stripslashes($_POST['coupon_code']);
        }
        // The Payment ID must be known and up to date when the cart is
        // parsed in order to consider payment dependent Coupons
        if (isset($_POST['paymentId']))
            $_SESSION['shop']['paymentId'] = intval($_POST['paymentId']);
        // $_SESSION['shop']['paymentId'] may still be unset!
        // determine any valid value for it
        if (   !empty($_SESSION['shop']['total_price'])
            && empty($_SESSION['shop']['paymentId'])) {
            $arrPaymentId = Payment::getCountriesRelatedPaymentIdArray(
                $_SESSION['shop']['countryId'], Currency::getCurrencyArray());
            $_SESSION['shop']['paymentId'] = current($arrPaymentId);
        }

        // Update the cart, apply discount amount
        $this->_parseCart();

        // hide currency navbar
        $this->_hideCurrencyNavbar = true;

        if (isset($_POST['customer_note']))
            $_SESSION['shop']['customer_note'] =
                contrexx_stripslashes($_POST['customer_note']);

        if (isset($_POST['agb']))
            $_SESSION['shop']['agb'] = HTML_ATTRIBUTE_CHECKED;

        // Uses default currency
        $_SESSION['shop']['total_price'] = $_SESSION['shop']['cart']['total_price'];
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

        $_SESSION['shop']['payment_price'] =
            $this->_calculatePaymentPrice(
                $_SESSION['shop']['paymentId'],
                $_SESSION['shop']['total_price']
            );
        $this->_setPaymentTaxes();
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
     *     {@see Shipment::getShipperMenu()}.
     * - If $_SESSION['shop']['shipment'] evaluates to false, does nothing, but simply
     *   returns an empty string.
     * @return  string  Shipment dropdown menu, or an empty string
     */
    function _getShipperMenu()
    {
        // Only show the menu if shipment is needed and the ship-to
        // country is known
        if (   empty($_SESSION['shop']['shipment'])
            || empty($_SESSION['shop']['countryId2'])) {
            $_SESSION['shop']['shipperId'] = null;
            return '';
        }
        // Choose a shipment in this order from
        // - post, if present,
        // - session, if present,
        // - none.
        if (   empty($_SESSION['shop']['shipperId'])
            || isset($_POST['shipperId'])) {
            $_SESSION['shop']['shipperId'] =
                (isset($_POST['shipperId'])
                  ? intval($_POST['shipperId'])
                  : (isset($_SESSION['shop']['shipperId'])
                      ? $_SESSION['shop']['shipperId'] : 0
                    )
                );
        }
        $menu = Shipment::getShipperMenu(
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
            $arrPaymentId = Payment::getCountriesRelatedPaymentIdArray(
                $_SESSION['shop']['countryId'], Currency::getCurrencyArray()
            );
            if (isset($_SESSION['shop']['paymentId'])) {
                $_SESSION['shop']['paymentId'] = isset($_POST['paymentId']) ? intval($_POST['paymentId']) : $_SESSION['shop']['paymentId'];
            } else {
                // get default payment Id
                $_SESSION['shop']['paymentId'] = current($arrPaymentId);
            }
            return Payment::getPaymentMenu(
                $_SESSION['shop']['paymentId'],
                "document.forms['shopForm'].submit()",
                $_SESSION['shop']['countryId']
            );
        }
        return '';
    }


    /**
     * Set up price and VAT related information for payment page.
     *
     * Depending on the VAT settings, sets fields in the global $_SESSION['shop']
     * array variable, namely  'grand_total_price', 'vat_price', 'vat_products_txt',
     * 'vat_grand_txt', and 'vat_procentual'.
     */
    function _setPaymentTaxes()
    {
        global $_ARRAYLANG;

        if (empty($_SESSION['shop']['payment_price']))
            $_SESSION['shop']['payment_price'] = 0;
        if (empty($_SESSION['shop']['shipment_price']))
            $_SESSION['shop']['shipment_price'] = 0;

        Vat::setIsHomeCountry(
               empty($_SESSION['shop']['countryId2'])
            || $_SESSION['shop']['countryId2'] == SettingDb::getValue('country_id')
        );
        // VAT enabled?
        if (Vat::isEnabled()) {
            // VAT included?
            if (Vat::isIncluded()) {
                // home country equals shop country; VAT is included already
                if (Vat::getIsHomeCountry()) {
                    $_SESSION['shop']['vat_price'] = Currency::formatPrice(
                        $_SESSION['shop']['cart']['total_vat_amount'] +
                        Vat::calculateOtherTax(
                              $_SESSION['shop']['payment_price']
                            + $_SESSION['shop']['shipment_price']
                        )
                    );
                    $_SESSION['shop']['grand_total_price'] = Currency::formatPrice(
                          $_SESSION['shop']['cart']['total_price']
                        + $_SESSION['shop']['payment_price']
                        + $_SESSION['shop']['shipment_price']
                    );
                    $_SESSION['shop']['vat_products_txt']   = $_ARRAYLANG['TXT_SHOP_VAT_INCLUDED'];
                    $_SESSION['shop']['vat_grand_txt']      = $_ARRAYLANG['TXT_SHOP_VAT_INCLUDED'];
                } else {
                    // foreign country; subtract VAT from grand total price
                    // must use every single orderitem in the cart to calculate the VAT now
                    $_SESSION['shop']['vat_price'] = $_SESSION['shop']['cart']['total_vat_amount'];
                    $_SESSION['shop']['grand_total_price']  = Currency::formatPrice(
                          $_SESSION['shop']['cart']['total_price']
                        + $_SESSION['shop']['payment_price']
                        + $_SESSION['shop']['shipment_price']
                        - $_SESSION['shop']['vat_price']
                    );
                    $_SESSION['shop']['vat_products_txt']   = $_ARRAYLANG['TXT_SHOP_VAT_INCLUDED'];
                    $_SESSION['shop']['vat_grand_txt']      = $_ARRAYLANG['TXT_SHOP_VAT_EXCLUDED'];
                }
            } else {
                // VAT is excluded
                if (Vat::getIsHomeCountry()) {
                    // home country equals shop country; add VAT.
                    // the VAT on the products has already been calculated and set in the cart.
                    // now we add the default VAT to the shipping and payment cost.
                    $_SESSION['shop']['vat_price'] = Currency::formatPrice(
                        $_SESSION['shop']['cart']['total_vat_amount'] +
                        Vat::calculateOtherTax(
                            $_SESSION['shop']['payment_price'] +
                            $_SESSION['shop']['shipment_price']
                        ));
                    $_SESSION['shop']['grand_total_price'] = Currency::formatPrice(
                          $_SESSION['shop']['cart']['total_price']
                        + $_SESSION['shop']['payment_price']
                        + $_SESSION['shop']['shipment_price']
                        + $_SESSION['shop']['vat_price']);
                    $_SESSION['shop']['vat_products_txt']   = $_ARRAYLANG['TXT_SHOP_VAT_EXCLUDED'];
                    $_SESSION['shop']['vat_grand_txt']      = $_ARRAYLANG['TXT_SHOP_VAT_INCLUDED'];
                } else {
                    // foreign country; do not add VAT
                    $_SESSION['shop']['vat_price']         = '0.00';
                    $_SESSION['shop']['grand_total_price'] = Currency::formatPrice(
                          $_SESSION['shop']['cart']['total_price']
                        + $_SESSION['shop']['payment_price']
                        + $_SESSION['shop']['shipment_price']);
                    $_SESSION['shop']['vat_products_txt'] = $_ARRAYLANG['TXT_SHOP_VAT_EXCLUDED'];
                    $_SESSION['shop']['vat_grand_txt']    = $_ARRAYLANG['TXT_SHOP_VAT_EXCLUDED'];
                }
            }
        } else {
            // VAT is disabled
            $_SESSION['shop']['vat_price']         = '0.00';
            $_SESSION['shop']['vat_products_txt']  = '';
            $_SESSION['shop']['vat_grand_txt']     = '';
            $_SESSION['shop']['grand_total_price'] = Currency::formatPrice(
                  $_SESSION['shop']['cart']['total_price']
                + $_SESSION['shop']['payment_price']
                + $_SESSION['shop']['shipment_price']);
        }
    }


    function _checkPaymentDetails()
    {
        global $_ARRAYLANG;

        $status = '';
        $status_LSV = 1;

        // Added initializing of the payment processor below
        // in order to determine whether to show the LSV form.
        $processor_id = 0;
        $processor_name = '';
        if (!empty($_SESSION['shop']['paymentId']))
            $processor_id = Payment::getPaymentProcessorId($_SESSION['shop']['paymentId']);
        if (!empty($processor_id))
            $processor_name = PaymentProcessing::getPaymentProcessorName($processor_id);
        if ($processor_name == 'Internal_LSV') {
            $status_LSV = $this->showLSV();
        }

        // Process
        if (isset($_POST['check'])) {
            // shipment status is true, if either
            // - no shipment is desired, or
            // - the shipperId is set already (and the shipment conditions were validated)
            $shipmentStatus =
                ($_SESSION['shop']['shipment']
                    ? isset($_SESSION['shop']['shipperId']) : true
                );
            // payment status is true, if either
            // - the total price is zero (or less!?), including VAT and shipment, or
            // - the paymentId is set and valid, and the LSV status evaluates to true.
            // luckily, shipping, VAT, and price have been handled in _setPaymentTaxes()
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
     * @internal    Todo: Fill in the order summary automatically.
     * @internal
     *  Problem: If the order is big enough, it may not fit into the
     *  visible text area, thus causing some order items to be cut off
     *  by printing.  This issue should be resolved by replacing the
     *  <textarea> with a variable height element, such as a table, or
     *  a simple div.
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @global  array   $_ARRAYLANG     Language array
     */
    function einzug()
    {
        global $_ARRAYLANG;

        $shopAddress = (SettingDb::getValue('shop_address')
            ? SettingDb::getValue('shop_address')
            : ''
        );
        $shopAddress = preg_replace('/[\012\015]+/', ', ', $shopAddress);

/*
This information should be read and stored in the session
right after the customer logs in!
        // fill in the address for known customers
        if ($this->objCustomer) {
            $_SESSION['shop']['prefix']     = $this->objCustomer->getTitle();
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
            'SHOP_CUSTOMER_TITLE'      => (isset($_SESSION['shop']['prefix'])     ? stripslashes($_SESSION['shop']['prefix'])    : ''),
            'SHOP_CUSTOMER_FIRST_NAME' => (isset($_SESSION['shop']['firstname']) ? stripslashes($_SESSION['shop']['firstname']) : ''),
            'SHOP_CUSTOMER_LAST_NAME'  => (isset($_SESSION['shop']['lastname'])  ? stripslashes($_SESSION['shop']['lastname'])  : ''),
            'SHOP_CUSTOMER_ADDRESS'    => (isset($_SESSION['shop']['address'])   ? stripslashes($_SESSION['shop']['address'])   : ''),
            'SHOP_CUSTOMER_ZIP'        => (isset($_SESSION['shop']['zip'])       ? stripslashes($_SESSION['shop']['zip'])       : ''),
            'SHOP_CUSTOMER_CITY'       => (isset($_SESSION['shop']['city'])      ? stripslashes($_SESSION['shop']['city'])      : ''),
            'SHOP_CUSTOMER_PHONE'      => (isset($_SESSION['shop']['phone'])     ? stripslashes($_SESSION['shop']['phone'])     : ''),
            'SHOP_CUSTOMER_FAX'        => (isset($_SESSION['shop']['fax'])       ? stripslashes($_SESSION['shop']['fax'])       : ''),
            'SHOP_CUSTOMER_EMAIL'      => (isset($_SESSION['shop']['email'])     ? stripslashes($_SESSION['shop']['email'])     : ''),
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
            'SHOP_FAX'                    => SettingDb::getValue('fax'),
            'SHOP_COMPANY'                => SettingDb::getValue('shop_company'),
            'SHOP_ADDRESS'                => $shopAddress,
        ));
    }


    /**
     * Set up the common fields of the payment page
     *
     * @param   string    $paymentStatus  Payment status
     * @return  void
     */
    function _getPaymentPage($paymentStatus)
    {
        global $_ARRAYLANG;

        if (   $_SESSION['shop']['cart']['total_weight'] > 0
            && SettingDb::getValue('weight_enable')) {
            $this->objTemplate->setVariable(array(
                'TXT_TOTAL_WEIGHT'        => $_ARRAYLANG['TXT_TOTAL_WEIGHT'],
                'SHOP_TOTAL_WEIGHT'       => Weight::getWeightString($_SESSION['shop']['cart']['total_weight']),
            ));
        }

        if (empty($_SESSION['shop']['shipment'])) {
            unset($_SESSION['shop']['shipperId']);
        } else {
            $this->objTemplate->setVariable(array(
                'SHOP_SHIPMENT_PRICE'  => $_SESSION['shop']['shipment_price'],
                'SHOP_SHIPMENT_MENU'   => $this->_getShipperMenu(),
                'TXT_SHIPPING_METHODS' => $_ARRAYLANG['TXT_SHIPPING_METHODS'],
            ));
        }

        if (   $_SESSION['shop']['total_price']
            || $_SESSION['shop']['shipment_price']
            || $_SESSION['shop']['vat_price']) {
            $this->objTemplate->setVariable(array(
                'SHOP_PAYMENT_PRICE'      => $_SESSION['shop']['payment_price'],
                'SHOP_PAYMENT_MENU'       => $this->_getPaymentMenu(),
                'TXT_PAYMENT_TYPES'       => $_ARRAYLANG['TXT_PAYMENT_TYPES'],
            ));
        }

        if (empty($_SESSION['shop']['coupon_code'])) {
            $_SESSION['shop']['coupon_code'] = '';
        }
        $total_discount_amount = 0;
        if (!empty($_SESSION['shop']['total_discount_amount'])) {
            $total_discount_amount = $_SESSION['shop']['total_discount_amount'];
            $this->objTemplate->setVariable(array(
                'SHOP_DISCOUNT_COUPON_TOTAL' =>
                    $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_AMOUNT_TOTAL'],
                'SHOP_DISCOUNT_COUPON_TOTAL_AMOUNT' =>
                    Currency::formatPrice(-$total_discount_amount),
            ));
        }

        $this->objTemplate->setVariable(array(
            'SHOP_UNIT'               => Currency::getActiveCurrencySymbol(),
            'SHOP_TOTALITEM'          => $_SESSION['shop']['items'],
            'SHOP_TOTALPRICE'         => Currency::formatPrice(
                  $_SESSION['shop']['cart']['total_price']
                + $_SESSION['shop']['total_discount_amount']),
            'SHOP_GRAND_TOTAL'        => Currency::formatPrice(
                  $_SESSION['shop']['grand_total_price']),
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
            // Coupon code
            'TXT_SHOP_DISCOUNT_COUPON_CODE' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_CODE'],
            'SHOP_DISCOUNT_COUPON_CODE'     => $_SESSION['shop']['coupon_code'],
        ));
        if (Vat::isEnabled()) {
            $this->objTemplate->setVariable(array(
                'SHOP_TAX_PRICE'          =>
                    $_SESSION['shop']['vat_price'].
                    '&nbsp;'.Currency::getActiveCurrencySymbol(),
                'SHOP_TAX_PRODUCTS_TXT'   => $_SESSION['shop']['vat_products_txt'],
                'SHOP_TAX_GRAND_TXT'      => $_SESSION['shop']['vat_grand_txt'],
                'TXT_TAX_RATE'            => $_ARRAYLANG['TXT_SHOP_VAT_RATE'],
                'TXT_TAX_PREFIX'          =>
                    (Vat::isIncluded()
                        ? $_ARRAYLANG['TXT_SHOP_VAT_PREFIX_INCL']
                        : $_ARRAYLANG['TXT_SHOP_VAT_PREFIX_EXCL']
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

        // if the cart or address is missing, return to the shop
        if (!$this->verifySessionAddress()) {
            header('Location: index.php?section=shop'.MODULE_INDEX);
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
                self::addMessage($_ARRAYLANG['TXT_ORDER_ALREADY_PLACED']);
                return false;
            }

            // no more confirmation
            $this->objTemplate->hideBlock("shopConfirm");
            // store the customer, register the order
            $customer_ip      = $_SERVER['REMOTE_ADDR'];
            $customer_host    = substr(@gethostbyaddr($_SERVER['REMOTE_ADDR']), 0, 100);
            $customer_browser = substr(getenv('HTTP_USER_AGENT'), 0, 100);
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
                // Currently, the e-mail address is set as the user name
                $_SESSION['shop']['username'] = trim($_SESSION['shop']['email'], " \t");
            } else {
                // update the Customer object from the session array
                // (she may have edited it)
                $this->objCustomer->setTitle($_SESSION['shop']['prefix']);
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
/*  Move to order class
            $this->objCustomer->setCcNumber(isset($_SESSION['shop']['ccnumber']) ? $_SESSION['shop']['ccnumber'] : '');
            $this->objCustomer->setCcDate  (isset($_SESSION['shop']['ccdate'])   ? $_SESSION['shop']['ccdate']   : '');
            $this->objCustomer->setCcName  (isset($_SESSION['shop']['ccname'])   ? $_SESSION['shop']['ccname']   : '');
            $this->objCustomer->setCcCode  (isset($_SESSION['shop']['cvcCode'])  ? $_SESSION['shop']['cvcCode']  : '');
*/
            // insert or update the customer
// TODO: The $result should be tested
//            $result = $this->objCustomer->store();
            if (!$this->objCustomer->store()) {
            	self::addMessage($_ARRAYLANG['TXT_SHOP_CUSTOMER_ERROR_STORING']);
            	return false;
            }
            // Probably used nowhere
            //$_SESSION['shop']['customerid'] = $this->objCustomer->getId();

            // Clear the ship-to country if there is no shipping
            if (empty($_SESSION['shop']['shipment'])) {
                $_SESSION['shop']['countryId2'] = 0;
            }

            // Add to order table
            $query = "
                INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_orders (
                    customerid, selected_currency_id, currency_order_sum,
                    order_date, order_status, ship_company, ship_prefix,
                    ship_firstname, ship_lastname, ship_address, ship_city,
                    ship_zip, ship_country_id, ship_phone,
                    tax_price, currency_ship_price,
                    shipping_id, payment_id, currency_payment_price,
                    customer_ip, customer_host, customer_lang,
                    customer_browser, customer_note
                ) VALUES (
                    ".$this->objCustomer->getId().",
                    '{$_SESSION['shop']['currencyId']}',
                    '{$_SESSION['shop']['grand_total_price']}',
                    NOW(),
                    '0',
                    '".addslashes($_SESSION['shop']['company2'])."',
                    '".addslashes($_SESSION['shop']['prefix2'])."',
                    '".addslashes($_SESSION['shop']['firstname2'])."',
                    '".addslashes($_SESSION['shop']['lastname2'])."',
                    '".addslashes($_SESSION['shop']['address2'])."',
                    '".addslashes($_SESSION['shop']['city2'])."',
                    '".addslashes($_SESSION['shop']['zip2'])."',
                    '".intval($_SESSION['shop']['countryId2'])."',
                    '".addslashes($_SESSION['shop']['phone2'])."',
                    '{$_SESSION['shop']['vat_price']}',
                    '{$_SESSION['shop']['shipment_price']}', ".
                    (   isset($_SESSION['shop']['shipperId'])
                     && $_SESSION['shop']['shipperId']
                        ? $_SESSION['shop']['shipperId'] : 0).",
                    {$_SESSION['shop']['paymentId']},
                    '{$_SESSION['shop']['payment_price']}',
                    '".addslashes($customer_ip)."',
                    '".addslashes($customer_host)."',
                    '".FRONTEND_LANG_ID."',
                    '".addslashes($customer_browser)."',
                    '".addslashes($_SESSION['shop']['customer_note'])."'
                )
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                // $order_id is unset!
                self::addMessage($_ARRAYLANG['TXT_ERROR_STORING_CUSTOMER_DATA']);
                return false;
            }
            $order_id = $objDatabase->Insert_ID();
            $_SESSION['shop']['orderid'] = $order_id;
            // The products will be tested one by one below.
            // If any single one of them requires delivery, this
            // flag will be set to true.
            // This is used to determine the order status at the
            // end of the shopping process.
            $_SESSION['shop']['isDelivery'] = false;

            foreach ($_SESSION['shop']['cart']['products'] as $arrProduct) {
                $objProduct = Product::getById($arrProduct['id']);
                if (!$objProduct) {
                    unset($_SESSION['shop']['orderid']);
                    self::addMessage($_ARRAYLANG['TXT_ERROR_LOOKING_UP_ORDER']);
                    return false;
                }
                $product_id = $arrProduct['id'];
                $productName = $objProduct->name();
                $priceOptions = (!empty($arrProduct['optionPrice'])
                    ? $arrProduct['optionPrice'] : 0
                );
                $productQuantity = $arrProduct['quantity'];
                $productPrice    = $this->_getProductPrice(
                    $product_id,
                    $priceOptions,
                    $productQuantity
                );

                $productVatId = $objProduct->vat_id();
                $productVatRate =
                    ($productVatId && Vat::getRate($productVatId)
                        ? Vat::getRate($productVatId) : '0.00'
                    );
                // Test the distribution method for delivery
                $productDistribution = $objProduct->distribution();
                if ($productDistribution == 'delivery') {
                    $_SESSION['shop']['isDelivery'] = true;
                }
                $productWeight   = ($productDistribution == 'delivery'
                    ? $objProduct->weight() : 0); // grams
                if ($productWeight == '') { $productWeight = 0; }
                // Add to order items table
                $query = "
                    INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_order_items (
                        orderid, productid, product_name,
                        price, quantity, vat_percent, weight
                    ) VALUES (
                        $order_id, $product_id, '".addslashes($productName)."',
                        '$productPrice', '$productQuantity',
                        '$productVatRate', '$productWeight'
                    )";
                $objResult = $objDatabase->Execute($query);
                if (!$objResult) {
                    unset($_SESSION['shop']['orderid']);
                    self::addMessage($_ARRAYLANG['TXT_ERROR_INSERTING_ORDER_ITEM']);
                    return false;
                }
                $orderItemsId = $objDatabase->Insert_ID();
                foreach ($arrProduct['options'] as $attribute_id => $arrOptionIds) {
                    $objAttribute = Attribute::getById($attribute_id);
                    if (!$objAttribute) {
                        continue;
                    }
                    $name = $objAttribute->getName();
                    foreach ($arrOptionIds as $option_id) {
                        if ($objAttribute->getType() >= Attribute::TYPE_TEXT_OPTIONAL) {
                            // There is no value ID stored for text and upload
                            // fields.  Thus, we use the name ID to get the
                            // options' values.
                            $arrOptions = Attributes::getOptionArrayByAttributeId($attribute_id);
                            // There is exactly one option record for these
                            // types.  Use this and overwrite the "default"
                            // value with the text or file name.
                            $arrOption = current($arrOptions);
                            $arrOption['value'] = $option_id;
                        } else {
                            // There is exactly one option record for the option
                            // ID given.  Use this.
                            $arrOptions = Attributes::getOptionArrayByAttributeId($attribute_id);
                            $arrOption = $arrOptions[$option_id];
                        }
                        if (!is_array($arrOption)) {
                            continue;
                        }
                        // add product attributes to order items attribute table
                        $query = "
                            INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_order_items_attributes
                               SET order_items_id=$orderItemsId,
                                order_id=$order_id,
                                product_id=$product_id,
                                attribute_name='".addslashes($name)."',
                                option_value='".addslashes($arrOption['value'])."',
                                option_price='".$arrOption['price']."'";
                        $objResult = $objDatabase->Execute($query);
                        if (!$objResult) {
                            unset($_SESSION['shop']['orderid']);
                            self::addMessage($_ARRAYLANG['TXT_ERROR_INSERTING_ORDER_ITEM_ATTRIBUTE']);
                            return false;
                        }
                    }
                }
            } // foreach product in cart

            // Add Attribute for the Coupon Code and discount amount
            // Note the negative sign for the amount!
            if (isset($_SESSION['shop']['coupon_code'])) {
                $query = "
                    INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_order_items_attributes (
                        order_id, order_items_id, product_id,
                        product_option_name,
                        product_option_value,
                        product_option_values_price
                    ) VALUES (
                        $order_id, 0, 0,
                        '".addslashes(Coupon::COUPON_ATTRIBUTE_NAME)."',
                        '".addslashes($_SESSION['shop']['coupon_code'])."',
                        ".-$_SESSION['shop']['total_discount_amount']."
                    )";
                $objResult = $objDatabase->Execute($query);
                if (!$objResult) {
                    unset($_SESSION['shop']['orderid']);
                    self::addMessage($_ARRAYLANG['TXT_ERROR_INSERTING_ORDER_ITEM_ATTRIBUTE']);
                    return false;
                }
            }

            $processor_id = Payment::getProperty($_SESSION['shop']['paymentId'], 'processor_id');
            $processor_name = PaymentProcessing::getPaymentProcessorName($processor_id);
             // other payment methods
            $this->objProcessing->initProcessor(
                $processor_id,
                Currency::getActiveCurrencyCode(),
                FWLanguage::getLanguageParameter(FRONTEND_LANG_ID, 'lang')
            );

            // if the processor is Internal_LSV, and there is account information,
            // store the information.
            if ($processor_name == 'Internal_LSV') {
                if (isset($_SESSION['shop']['account_holder']) && $_SESSION['shop']['account_holder']
                 && isset($_SESSION['shop']['account_bank'])   && $_SESSION['shop']['account_bank']
                 && isset($_SESSION['shop']['account_blz'])    && $_SESSION['shop']['account_blz']   ) {
                    $query = "INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_lsv ".
                        "(order_id, holder, bank, blz) VALUES (".
                        $order_id.", '".
                        contrexx_addslashes($_SESSION['shop']['account_holder'])."', '".
                        contrexx_addslashes($_SESSION['shop']['account_bank'])."', '".
                        contrexx_addslashes($_SESSION['shop']['account_blz'])."')";
                    $objResult = $objDatabase->Execute($query);
                    if (!$objResult) {
                        unset($_SESSION['shop']['orderid']);
                        self::addMessage($_ARRAYLANG['TXT_ERROR_INSERTING_ACCOUNT_INFORMATION']);
                    }
                 } else {
                     // failure!
                     unset($_SESSION['shop']['orderid']);
                     self::addMessage($_ARRAYLANG['TXT_ERROR_ACCOUNT_INFORMATION_NOT_AVAILABLE']);
                 }
            }

            $_SESSION['shop']['orderid_checkin'] = $order_id;
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

            // Show payment processing page.
            // Note that some internal payments are redirected away
            // from this page in checkOut():
            // 'Internal', 'Internal_LSV'
            $this->objTemplate->setVariable(
                'SHOP_PAYMENT_PROCESSING',
                $this->objProcessing->checkOut()
            );
            // For all payment methods showing a form here:
            // Send the Customer login separately, as the password possibly
            // won't be available later
            self::sendLogin(
                $this->objCustomer->getEmail(),
                $_SESSION['shop']['password']);

            // clear the order ID.
            // The order may be resubmitted and the payment retried.
            unset($_SESSION['shop']['orderid']);
            // Custom.
            // Enable if Discount class is customized and in use.
            //$this->showCustomerDiscount($_SESSION['shop']['cart']['total_price']);
        } else {
            // Show confirmation page.
            $this->objTemplate->hideBlock('shopProcess');
            $this->objTemplate->setCurrentBlock('shopCartRow');
            // It may be necessary to refresh the cart here, as the customer
            // may return to the cart, then press "Back".
            $this->_initPaymentDetails();
            foreach ($_SESSION['shop']['cart']['products'] as $arrProduct) {
                $objProduct = Product::getById($arrProduct['id']);
                if (!$objProduct) {
                    continue;
                }
                $priceOptions = (empty($arrProduct['optionPrice'])
                    ? 0 : $arrProduct['optionPrice']
                );
                // Note:  The Attribute options' price is added
                // to the price here!
                $price = $this->_getProductPrice(
                    $arrProduct['id'],
                    $priceOptions,
                    $arrProduct['quantity']
                );

                $productOptions = '';
                if (is_array($arrProduct['options'])
                 && count($arrProduct['options']) > 0) {
                    foreach ($arrProduct['options'] as $attribute_id => $arrOptionIds) {
                        if (count($arrOptionIds) > 0) {
                            $objAttribute = Attribute::getById($attribute_id);
                            // Should be tested!
                            //if (!$objAttribute) { ... }
                            $productOptions .=
                                ($productOptions ? '<br />' : '').'- '.
                                Attribute::getNameById($attribute_id).': ';
                            $productOptionsValues = '';
                            foreach ($arrOptionIds as $option_id) {
                                if (intval($option_id)) {
                                    $optionValue =
                                        Attributes::getOptionNameById($option_id);
                                } else {
                                    $optionValue = ShopLibrary::stripUniqidFromFilename($option_id);
                                    if (   $optionValue != $option_id
                                        && file_exists(ASCMS_PATH.'/'.$this->uploadDir.'/'.$option_id)) {
                                            $optionValue =
                                                '<a href="'.$this->uploadDir.'/'.$option_id.
                                                '" target="uploadimage">'.$optionValue.'</a>';
                                    }
                                }
                                $productOptionsValues .=
                                    ($productOptionsValues ? ', ' : '').
                                    $optionValue;
                            }
                            $productOptions .= $productOptionsValues;
                        }
                    }
                }

                // Test the distribution method for delivery
                $productDistribution = $objProduct->distribution();
                $weight = ($productDistribution == 'delivery'
                    ? Weight::getWeightString($objProduct->weight())
                    : '-'
                );
                $vatId      = $objProduct->vat_id();
                $vatRate    = Vat::getRate($vatId);
                $vatPercent = Vat::getShort($vatId);
                $vatAmount  = Vat::amount(
                    $vatRate,
                    ($price+$priceOptions)*$arrProduct['quantity']
                );

                $this->objTemplate->setVariable(array(
                    'SHOP_PRODUCT_ID'           => $arrProduct['id'],
                    'SHOP_PRODUCT_CUSTOM_ID'    => $objProduct->code(),
/*
Version for shops without products having text or file upload attributes
                    'SHOP_PRODUCT_TITLE'        =>
                        htmlentities($objProduct->name(), ENT_QUOTES, CONTREXX_CHARSET),
                    'SHOP_PRODUCT_OPTIONS'      =>
                        '<i>'.$productOptions.'</i>',
*/
                    'SHOP_PRODUCT_TITLE'        =>
                        str_replace(
                            '"', '&quot;',
                            $objProduct->name().
                            ($productOptions
                              ? '<br /><i>'.$productOptions.'</i>' : '')),
                    'SHOP_PRODUCT_OPTIONS'      =>
                        '<i>'.$productOptions.'</i>',
                    'SHOP_PRODUCT_PRICE'        => Currency::formatPrice(($price)*$arrProduct['quantity']),
                    'SHOP_PRODUCT_QUANTITY'     => $arrProduct['quantity'],
                    'SHOP_PRODUCT_ITEMPRICE'    => Currency::formatPrice($price),
                    'SHOP_UNIT'                 => Currency::getActiveCurrencySymbol(),
                ));
                if (SettingDb::getValue('weight_enable')) {
                    $this->objTemplate->setVariable(array(
                        'SHOP_PRODUCT_WEIGHT' => $weight,
                        'TXT_WEIGHT'          => $_ARRAYLANG['TXT_WEIGHT'],
                    ));
                }
                if (Vat::isEnabled()) {
                    $this->objTemplate->setVariable(array(
                        'SHOP_PRODUCT_TAX_RATE'   => $vatPercent,
                        'SHOP_PRODUCT_TAX_AMOUNT' =>
                            Currency::formatPrice($vatAmount).
                            '&nbsp;'.Currency::getActiveCurrencySymbol(),
                    ));
                }
                $this->objTemplate->parse("shopCartRow");
            }

            $total_discount_amount = 0;
            if (!empty($_SESSION['shop']['total_discount_amount'])) {
                $total_discount_amount = $_SESSION['shop']['total_discount_amount'];
                $this->objTemplate->setVariable(array(
                    'SHOP_DISCOUNT_COUPON_TOTAL' =>
                        $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_AMOUNT_TOTAL'],
                    'SHOP_DISCOUNT_COUPON_TOTAL_AMOUNT' =>
                        Currency::formatPrice(-$total_discount_amount),
                ));
            }

            $this->objTemplate->setVariable(array(
                'SHOP_UNIT'             => Currency::getActiveCurrencySymbol(),
                'SHOP_TOTALITEM'        => $_SESSION['shop']['items'],
                'SHOP_PAYMENT_PRICE'    => $_SESSION['shop']['payment_price'],
                'SHOP_TOTALPRICE'       => $_SESSION['shop']['total_price'],
                'SHOP_PAYMENT'          =>
                    Payment::getProperty($_SESSION['shop']['paymentId'], 'name'),
                'SHOP_GRAND_TOTAL'      => Currency::formatPrice(
                      $_SESSION['shop']['grand_total_price']),
                'SHOP_COMPANY'          => stripslashes($_SESSION['shop']['company']),
                'SHOP_TITLE'            => stripslashes($_SESSION['shop']['prefix']),
                'SHOP_LASTNAME'         => stripslashes($_SESSION['shop']['lastname']),
                'SHOP_FIRSTNAME'        => stripslashes($_SESSION['shop']['firstname']),
                'SHOP_ADDRESS'          => stripslashes($_SESSION['shop']['address']),
                'SHOP_ZIP'              => stripslashes($_SESSION['shop']['zip']),
                'SHOP_CITY'             => stripslashes($_SESSION['shop']['city']),
                'SHOP_COUNTRY'          => Country::getNameById($_SESSION['shop']['countryId']),
                'SHOP_EMAIL'            => stripslashes($_SESSION['shop']['email']),
                'SHOP_PHONE'            => stripslashes($_SESSION['shop']['phone']),
                'SHOP_FAX'              => stripslashes($_SESSION['shop']['fax']),
                'SHOP_COMPANY2'         => stripslashes($_SESSION['shop']['company2']),
                'SHOP_TITLE2'           => stripslashes($_SESSION['shop']['prefix2']),
                'SHOP_LASTNAME2'        => stripslashes($_SESSION['shop']['lastname2']),
                'SHOP_FIRSTNAME2'       => stripslashes($_SESSION['shop']['firstname2']),
                'SHOP_ADDRESS2'         => stripslashes($_SESSION['shop']['address2']),
                'SHOP_ZIP2'             => stripslashes($_SESSION['shop']['zip2']),
                'SHOP_CITY2'            => stripslashes($_SESSION['shop']['city2']),
                'SHOP_COUNTRY2'         => Country::getNameById($_SESSION['shop']['countryId2']),
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
                'TXT_ORDER_NOW'         => $_ARRAYLANG['TXT_ORDER_NOW'],
            ));

            $total_discount_amount = $_SESSION['shop']['total_discount_amount'];
            if ($total_discount_amount) {
                $this->objTemplate->setVariable(array(
                    'SHOP_DISCOUNT_COUPON_TOTAL' =>
                        $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_AMOUNT_TOTAL'],
                    'SHOP_DISCOUNT_COUPON_TOTAL_AMOUNT' => Currency::formatPrice(
                        -$total_discount_amount),
                ));
            }

            if (!empty($_SESSION['shop']['customer_note'])) {
                $this->objTemplate->setVariable(array(
                    'TXT_COMMENTS'          => $_ARRAYLANG['TXT_COMMENTS'],
                    'SHOP_CUSTOMERNOTE'     => $_SESSION['shop']['customer_note'],
                ));
            }

            if (Vat::isEnabled()) {
                $this->objTemplate->setVariable(array(
                    'TXT_TAX_RATE'          => $_ARRAYLANG['TXT_SHOP_VAT_RATE'],
                    'SHOP_TAX_PRICE'        => $_SESSION['shop']['vat_price'],
                    'SHOP_TAX_PRODUCTS_TXT' => $_SESSION['shop']['vat_products_txt'],
                    'SHOP_TAX_GRAND_TXT'    => $_SESSION['shop']['vat_grand_txt'],
                    'TXT_TAX_PREFIX'        =>
                        (Vat::isIncluded()
                            ? $_ARRAYLANG['TXT_SHOP_VAT_PREFIX_INCL']
                            : $_ARRAYLANG['TXT_SHOP_VAT_PREFIX_EXCL']
                        ),
               ));
            }
// TODO: Make sure in payment() that those two are either both empty or
// both non-empty!
            if (   empty($_SESSION['shop']['shipment'])
                && empty($_SESSION['shop']['shipperId'])) {
                $this->objTemplate->hideBlock('shopShipmentAddress');
            } else {
                $this->objTemplate->setVariable(array(
                    'SHOP_SHIPMENT_PRICE'   => $_SESSION['shop']['shipment_price'],
                    'SHOP_SHIPMENT' =>
                        Shipment::getShipperName($_SESSION['shop']['shipperId']),
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

        // Hide the currency navbar
        $this->_hideCurrencyNavbar = true;

        // Use the Order ID stored in the session, if possible.
        // Otherwise, get it from the payment processor.
        $order_id = (empty($_SESSION['shop']['orderid_checkin'])
            ? PaymentProcessing::getOrderId()
            : $_SESSION['shop']['orderid_checkin']);
//DBG::log("success(): Restored Order ID ".var_export($order_id, true));

        // Default new order status: As long as it's pending (0, zero),
        // updateOrderStatus() will choose the new value automatically.
        $newOrderStatus = SHOP_ORDER_STATUS_PENDING;

        $checkinresult = $this->objProcessing->checkIn();
//DBG::log("success(): CheckIn Result ".var_export($checkinresult, true));

        if ($checkinresult === false) {
            // Failed payment.  Cancel the order.
            $newOrderStatus = SHOP_ORDER_STATUS_CANCELLED;
//DBG::log("success(): Order ID is *false*, new Status $newOrderStatus");
        } elseif ($checkinresult === true) {
            // True is returned for successful payments.
            // Update the status in any case.
            $newOrderStatus = SHOP_ORDER_STATUS_PENDING;
//DBG::log("success(): Order ID is *true*, new Status $newOrderStatus");
        } elseif ($checkinresult === null) {
            // checkIn() returns null if no change to the order status
            // is necessary or appropriate
            $newOrderStatus = SHOP_ORDER_STATUS_PENDING;
//DBG::log("success(): Order ID is *null* (new Status $newOrderStatus)");
        }

        // Also, this method *MUST NOT* be called by the PayPal IPN handler.
        if (isset($_GET['handler']) && $_GET['handler'] == 'PaypalIPN') {
            $newOrderStatus = SHOP_ORDER_STATUS_CANCELLED;
//DBG::log("success(): This method *MUST NOT* be called for the PayPal IPN URL! Handler in Request: ".$_GET['handler'].", Status: $newOrderStatus");
        }

        // Verify the Order ID with the session, if available
        if (   isset($_SESSION['shop']['orderid_checkin'])
            && $order_id != $_SESSION['shop']['orderid_checkin']) {
            // Cancel the Order with the ID from the session, not the
            // possibly faked one from the request!
//DBG::log("success(): Order ID $order_id is not ".$_SESSION['shop']['orderid_checkin'].", new Status $newOrderStatus");
            $order_id = $_SESSION['shop']['orderid_checkin'];
            $newOrderStatus = SHOP_ORDER_STATUS_CANCELLED;
            $checkinresult = false;
        }
//DBG::log("success(): Verification complete, Order ID ".var_export($order_id, true).", Status: $newOrderStatus");

        if (is_numeric($order_id)) {
            // The respective order state, if available, is updated.
            // The only exception is when $checkinresult is null.
            if (isset($checkinresult)) {
                $newOrderStatus =
                    Shop::updateOrderStatus($order_id, $newOrderStatus);
//DBG::log("success(): Updated Order Status to $newOrderStatus (Order ID $order_id)");
            } else {
                // The old status is the new status
                $newOrderStatus = $this->getOrderStatus($order_id);
            }
            switch ($newOrderStatus) {
                case SHOP_ORDER_STATUS_CONFIRMED:
                case SHOP_ORDER_STATUS_PAID:
                case SHOP_ORDER_STATUS_SHIPPED:
                case SHOP_ORDER_STATUS_COMPLETED:
                    self::addMessage($_ARRAYLANG['TXT_ORDER_PROCESSED']);
                    // Custom.
                    // Enable if Discount class is customized and in use.
                    //$this->showCustomerDiscount($_SESSION['shop']['cart']['total_price']);
                    break;
                case SHOP_ORDER_STATUS_PENDING:
                    // Pending orders must be stated as such.
                    // Certain payment methods (like PayPal with IPN) might
                    // be confirmed a little later and must cause the
                    // confirmation mail to be sent.
                    self::addMessage(
                        $_ARRAYLANG['TXT_SHOP_ORDER_PENDING'].'<br /><br />'.
                        $_ARRAYLANG['TXT_SHOP_ORDER_WILL_BE_CONFIRMED']
                    );
                    break;
                case SHOP_ORDER_STATUS_DELETED:
                case SHOP_ORDER_STATUS_CANCELLED:
                    self::addMessage(
                        $_ARRAYLANG['TXT_SHOP_PAYMENT_FAILED'].'<br /><br />'.
                        $_ARRAYLANG['TXT_SHOP_ORDER_CANCELLED']
                    );
                    break;
            }
        } else {
            self::addMessage($_ARRAYLANG['TXT_NO_PENDING_ORDER']);
        }
        // Avoid any output if the result is negative
        if (   isset($_REQUEST['result'])
            && $_REQUEST['result'] < 0) die('');
        $this->objTemplate->setVariable($_ARRAYLANG);
        // Comment this for testing, so you can reuse the same account and cart
// COMMENT OUT FOR TEST ONLY
        $this->destroyCart();
        // clear backup ID, avoid success() from being run again
        unset($_SESSION['shop']['orderid_checkin']);
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
     * @param   integer $order_id    The ID of the current order
     * @param   integer $newOrderStatus The optional new order status.
     * @param   string  $handler    The Payment type name in use
     * @return  integer             The new order status (may be zero)
     *                              if the order status can be changed
     *                              accordingly, zero otherwise
     */
    static function updateOrderStatus($order_id, $newOrderStatus=0)
    {
        global $objDatabase;

        $handler = (isset($_GET['handler']) ? $_GET['handler'] : '');
        $order_id = intval($order_id);
        if ($order_id == 0) {
            return SHOP_ORDER_STATUS_CANCELLED;
        }
        $query = "
            SELECT order_status, payment_id, shipping_id
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders
             WHERE orderid=$order_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->EOF) {
            return SHOP_ORDER_STATUS_CANCELLED;
        }
        $order_status = $objResult->fields['order_status'];

        // Never change a non-pending status!
        // Whether a payment was successful or not, the status must be
        // left alone.
        if ($order_status != SHOP_ORDER_STATUS_PENDING) {
            // The status of the order is not pending.
            // This may be due to a wrong order ID, a page reload,
            // or a PayPal IPN that has been received already.
            // No order status is changed automatically in these cases!
            // Leave it as it is.
            return $order_status;
        }

        // Determine and verify the payment handler
        $payment_id = $objResult->fields['payment_id'];
//if (!$payment_id) DBG::log("updateOrderStatus($order_id, $newOrderStatus): Failed to find Payment ID for Order ID $order_id");
        $processor_id = Payment::getPaymentProcessorId($payment_id);
//if (!$processor_id) DBG::log("updateOrderStatus($order_id, $newOrderStatus): Failed to find Processor ID for Payment ID $payment_id");
        $processorName = PaymentProcessing::getPaymentProcessorName($processor_id);
//if (!$processorName) DBG::log("updateOrderStatus($order_id, $newOrderStatus): Failed to find Processor Name for Processor ID $processor_id");
        // The payment processor *MUST* match the handler
        // returned.  In the case of PayPal, the order status is only
        // updated if this method is called by Paypal::ipnCheck() with the
        // 'PaypalIPN' handler argument or if the new order status is
        // set to force the order to be cancelled.
        if ($processorName == 'Paypal') {
            if (   $handler != 'PaypalIPN'
                && $newOrderStatus != SHOP_ORDER_STATUS_CANCELLED
            ) {
                return $order_status;
            }
        } elseif (
               $handler
            && !preg_match("/^$handler/i", $processorName)) {
//DBG::log("updateOrderStatus($order_id, $newOrderStatus): Mismatching Handlers: Order $processorName, Request ".$_GET['handler']);
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
            $processorType =
                PaymentProcessing::getCurrentPaymentProcessorType($processor_id);
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
             WHERE orderid=$order_id";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
            if (   $newOrderStatus == SHOP_ORDER_STATUS_CONFIRMED
                || $newOrderStatus == SHOP_ORDER_STATUS_PAID
                || $newOrderStatus == SHOP_ORDER_STATUS_SHIPPED
                || $newOrderStatus == SHOP_ORDER_STATUS_COMPLETED) {
                ShopLibrary::sendConfirmationMail($order_id);
// Implement a way to show this when the template is available
//                self::addMessage('<br />'.$_ARRAYLANG['TXT_SHOP_UNABLE_TO_SEND_EMAIL']);
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
     * Adds the string to the status messages.
     *
     * If necessary, inserts a line break tag (<br />) between
     * messages.
     * @param   string  $strMessage       The message to add
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function addMessage($strMessage)
    {
        self::$statusMessage .=
            (self::$statusMessage ? '<br />' : '').
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
                'SHOP_CUSTOMER_TOTAL_ORDER_AMOUNT'     => number_format($totalOrderAmount, 2, '.', '').' '.Currency::getActiveCurrencySymbol(),
                'SHOP_CUSTOMER_DISCOUNT_AMOUNT'        => number_format($discountAmount, 2, '.', '').' '.Currency::getActiveCurrencySymbol(),
                'SHOP_CUSTOMER_NEW_TOTAL_ORDER_AMOUNT' => number_format($newTotalOrderAmount, 2, '.', '').' '.Currency::getActiveCurrencySymbol(),
                'SHOP_CUSTOMER_NEW_DISCOUNT_AMOUNT'    => number_format($newDiscountAmount, 2, '.', '').' '.Currency::getActiveCurrencySymbol(),
                'TXT_SHOP_CUSTOMER_DISCOUNT_DETAILS'   => $_ARRAYLANG['TXT_SHOP_CUSTOMER_DISCOUNT_DETAILS'],
            ));
        }
        return true;
    }


    /**
     * Set up the template block with the shipment terms and conditions
     *
     * Please *DO NOT* remove this method, despite the site terms and
     * conditions have been removed from the Shop!
     * This has been requested by some shopkeepers and may be used at will.
     * @global    array   $_ARRAYLANG     Language array
     * @author    Reto Kohli <reto.kohli@comvation.com>
     */
    function showShipmentTerms()
    {
        global $_ARRAYLANG;

        if ($this->objTemplate->blockExists('shopShipper')) {
            $arrShipment = Shipment::getShipmentConditions();
            foreach ($arrShipment as $strShipperName => $arrContent) {
                $strCountries  = join(', ', $arrContent['countries']);
                $arrConditions = $arrContent['conditions'];
                $this->objTemplate->setCurrentBlock('shopShipment');
                foreach ($arrConditions as $arrData) {
                    $this->objTemplate->setVariable(array(
                        'SHOP_MAX_WEIGHT' => $arrData['max_weight'],
                        'SHOP_COST_FREE'  => $arrData['free_from'],
                        'SHOP_COST'       => $arrData['fee'],
                        'SHOP_UNIT'       => Currency::getActiveCurrencySymbol(),
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


    /**
     * Set up the full set of discount information placeholders
     * @param   integer   $groupCustomerId    The customer group ID of the current customer
     * @param   integer   $groupArticleId     The article group ID of the current article
     * @param   integer   $groupCountId       The count discount group ID of the current article
     * @param   integer   $count              The number of articles to be used for the count discount
     * @author    Reto Kohli <reto.kohli@comvation.com>
     */
    function showDiscountInfo(
        $groupCustomerId, $groupArticleId, $groupCountId, $count
    ) {
        global $_ARRAYLANG;

        // Pick the unit for this product (count, meter, kilo, ...)
        $unit = Discount::getUnit($groupCountId);
        if (!empty($unit)) {
            $this->objTemplate->setVariable(
                'SHOP_PRODUCT_UNIT', $unit
            );
        }

        if ($groupCustomerId > 0) {
            $rateCustomer = Discount::getDiscountRateCustomer(
                $groupCustomerId, $groupArticleId
            );
            if ($rateCustomer > 0) {
                $this->objTemplate->setVariable(array(
                    'SHOP_DISCOUNT_RATE_CUSTOMER' => $rateCustomer,
                    'TXT_SHOP_DISCOUNT_RATE_CUSTOMER' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_RATE_CUSTOMER'],
                ));
            }
        }

        if ($groupCountId > 0) {
            $rateCount = Discount::getDiscountRateCount($groupCountId, $count);
            $listCount = Shop::getDiscountCountString($groupCountId);
            if ($rateCount > 0) {
                // Show discount rate if applicable
                $this->objTemplate->setVariable(
                    'SHOP_DISCOUNT_RATE_COUNT', $rateCount
                );
            }
            if (!empty($listCount)) {
                // Show discount rate string if applicable
                $this->objTemplate->setVariable(
                    'SHOP_DISCOUNT_RATE_COUNT_LIST', $listCount
                );
            }
        }
    }


    /**
     * Returns a string representation of the count type discounts
     * applicable for the given discount group ID, if any.
     * @param   integer   $groupCountId       The discount group ID
     * @return  string                        The string representation
     * @global  array     $_ARRAYLANG         Language array
     * @author    Reto Kohli <reto.kohli@comvation.com>
     * @static
     */
    static function getDiscountCountString($groupCountId)
    {
        global $_ARRAYLANG;

        $arrDiscount = Discount::getDiscountCountArray();
        $arrRate = Discount::getDiscountCountRateArray($groupCountId);
        $strDiscounts = '';
        if (!empty($arrRate)) {
            $unit = '';
            if (isset($arrDiscount[$groupCountId])) {
                $unit = $arrDiscount[$groupCountId]['unit'];
            }
            foreach ($arrRate as $count => $rate) {
                $strDiscounts .=
                    ($strDiscounts != '' ? ', ' : '').
                    $_ARRAYLANG['TXT_SHOP_DISCOUNT_FROM'].' '.
                    $count.' '.$unit.
                    $_ARRAYLANG['TXT_SHOP_DISCOUNT_TO'].' '.
                    $rate.'%';
            }
            $strDiscounts =
                $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUNT'].' '.
                $strDiscounts;
        }
        return $strDiscounts;
    }


    /**
     * Upload a file to be associated with a product in the cart
     * @param   integer   $productAttributeId   The Attribute ID the
     *                                          file belongs to
     * @return  string                          The file name on success,
     *                                          the empty string otherwise
     * @author    Reto Kohli <reto.kohli@comvation.com>
     */
    function uploadFile($productAttributeId)
    {
        global $_ARRAYLANG;

        if (empty($_FILES['productOption']['tmp_name'][$productAttributeId])) {
            return '';
        }
        $uploadFileName = $_FILES['productOption']['tmp_name'][$productAttributeId];
        $originalFileName = $_FILES['productOption']['name'][$productAttributeId];
        $arrMatch = array();
        $filename = '';
        $fileext  = '';
        if (preg_match('/(.+)(\.[^.]+)/', $originalFileName, $arrMatch)) {
            $filename = $arrMatch[1];
            $fileext  = $arrMatch[2];
        } else {
            $filename = $originalFileName;
        }
        if (   $fileext == '.jpg'
            || $fileext == '.gif'
            || $fileext == '.png') {
            $newFileDir = ASCMS_PATH.'/'.$this->uploadDir;
            $newFileName = $filename.'['.uniqid().']'.$fileext;
            $newFilePath = $newFileDir.'/'.$newFileName;
            if (move_uploaded_file($uploadFileName, $newFilePath)) {
                return $newFileName;
            }
            self::addMessage($_ARRAYLANG['TXT_SHOP_ERROR_UPLOADING_FILE']);
        } else {
            self::addMessage(sprintf($_ARRAYLANG['TXT_SHOP_ERROR_WRONG_FILETYPE'], $fileext));
        }
        return '';
    }


    /**
     * Returns true if the cart is non-empty and all necessary address
     * information has been stored in the session
     * @return    boolean               True on success, false otherwise
     */
    function verifySessionAddress()
    {
        global $_ARRAYLANG;

        // check the submission
        if (   empty($_SESSION['shop']['cart'])
            || empty($_SESSION['shop']['prefix'])
            || empty($_SESSION['shop']['lastname'])
            || empty($_SESSION['shop']['firstname'])
            || empty($_SESSION['shop']['address'])
            || empty($_SESSION['shop']['zip'])
            || empty($_SESSION['shop']['city'])
            || empty($_SESSION['shop']['phone'])
            || (empty($_SESSION['shop']['email']) && !$this->objCustomer)
            || (empty($_SESSION['shop']['password']) && !$this->objCustomer)
            || (!empty($_SESSION['shop']['shipment'])
            && (   empty($_SESSION['shop']['prefix2'])
                || empty($_SESSION['shop']['lastname2'])
                || empty($_SESSION['shop']['firstname2'])
                || empty($_SESSION['shop']['address2'])
                || empty($_SESSION['shop']['zip2'])
                || empty($_SESSION['shop']['city2'])
                || empty($_SESSION['shop']['phone2'])))
        ) return false;
        return true;
    }


    function getOrderStatus($order_id)
    {
        global $objDatabase;

        $query = "
            SELECT order_status
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders
             WHERE orderid=$order_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->EOF) return false;
        return $objResult->fields['order_status'];
    }


    function getOrderPaymentId($order_id)
    {
        global $objDatabase;

        $query = "
            SELECT payment_id
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders
             WHERE orderid=$order_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->EOF) return false;
        return $objResult->fields['payment_id'];
    }

}

?>
