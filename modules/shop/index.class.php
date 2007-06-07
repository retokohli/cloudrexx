<?php
/**
 * The shop
 *
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Ivan Schmid <ivan.schmid@astalavista.ch>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */

/**
 * ignore
 */
require_once ASCMS_MODULE_PATH.'/shop/shopLib.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Currency.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Payment.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Shipment.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/PaymentProcessing.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Vat.class.php';
require_once ASCMS_MODULE_PATH.'/shop/lib/Weight.class.php';

/**
 * Use session in show
 *
 * Check if the session will be required and has to get inizialized
 *
 * @return boolean
 */
function shopUseSession()
{
	if (!empty($_COOKIE['PHPSESSID'])) {
		return true;
	} else {
	    $command = '';

	    if (!empty($_GET['cmd'])) {
	        $command = $_GET['cmd'];
	    } elseif (!empty($_GET['act'])) {
	        $command = $_GET['act'];
	    }

	    $arrSessionNotRequired = array(
	        '',
	        'discounts',
	        'details',
	        'terms'
	    );

	    if (in_array($command, $arrSessionNotRequired)) {
	        if ($command == 'details' && isset($_REQUEST['referer']) && $_REQUEST['referer'] == 'cart') {
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
class Shop extends ShopLibrary {
    var $pageContent;
    var $objTemplate;
    var $arrCategoriesSorted = array();
    var $arrCategoriesTable = array();
    var $arrCategoriesName = array();
    var $arrParentCategoriesId = array();
    var $arrParentCategoriesTable = array();
    var $statusMessage = "";
    var $thumbnailNameSuffix = ".thumb";
    var $customerId;
    var $customerUsername;
    var $customerPrefix;
    var $customerCompany;
    var $customerLastname;
    var $is_reseller = 0;
    var $is_auth = 0;
    var $noPictureName = "no_picture.gif";
    var $shopImageWebPath;
    var $shopImagePath;
    var $inactiveStyleName = "inactive";
    var $activeStyleName = "active";
    var $arrProductAttributes = array();
    var $langId;
    var $_defaultImage = '';


    /**
     * Object of the payment offerer
     * @access private
     * @var array
     * @see Payment.class.php
     */
    var $objPayment;

    /**
     * Object of the payment processing
     * @access private
     * @var array
     * @see PaymentProcessing.class.php
     */
    var $objProcessing;

    /**
     * Shipment object
     * @access public
     * @var array
     */
    var $objShipment;

    /**
     * Currency object
     * @access public
     * @var object Currency
     */
    var $objCurrency;

    /**
     * Active currency unit name( e.g. CHF, EUR, USD )
     * @access private
     * @var string
     */
    var $aCurrencyUnitName;

    /**
     * Currency navbar indicator (true|false)
     * @access private
     * @var boolean
     */
    var $_hideCurrencyNavbar = false;


    /**
     * @var mixed   $objVat VAT object
     */
    var $objVat;


    /**
     * PHP4 constructor
     *
     * @param  string
     * @access public
     */
    function Shop($pageContent)
    {
        $this->__construct($pageContent);
    }


    /**
     * PHP5 constructor
     *
     * @param  string
     * @access public
     */
    function __construct($pageContent = '')
    {
        global $_LANGID, $objDatabase;

        if (0) {
            global $objDatabase; $objDatabase->debug = 1;
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
        }

        $this->langId = $_LANGID;
        $this->pageContent = $pageContent;
        $this->shopImageWebPath = ASCMS_SHOP_IMAGES_WEB_PATH . '/';
        $this->shopImagePath = ASCMS_SHOP_IMAGES_PATH. '/';
        $this->_defaultImage = $this->shopImageWebPath.$this->noPictureName;

        // sigma template
        $this->objTemplate = new HTML_Template_Sigma('.');
        $this->objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $this->objTemplate->setTemplate($this->pageContent, true, true);

        // Currency object
        // see Currency.class.php
        $this->objCurrency = new Currency();
        $this->aCurrencyUnitName = $this->objCurrency->getActiveCurrencySymbol();

        // Shipment object - ignoreStatus == false; see Shipment::Shipment()
        $this->objShipment = new Shipment(0);

        // Payment object
        // see Payment.class.php
        $this->objPayment = new Payment();

        // initialize the countries array
        $this->_initCountries();

        // initialize the shipment array
        // replaced by Shipment.class!
        //$this->_initShipment();

        if (shopUseSession()) {
            $this->_authenticate();
        }

        $this->_initConfiguration();
        $this->_initPayment();

        // VAT object
        // Create this only after the configuration ($this->arrConfig) has been set up!
        $this->objVat = new Vat();

        // initialize the product options names and values array
        $this->initProductAttributes();

        // Payment processing object
        // see PaymentProcessing.class.php
        $this->objProcessing = new PaymentProcessing($this->arrConfig);

        $query = "SELECT catid, parentid, catname ".
            "FROM ".DBPREFIX."module_shop_categories ".
            "WHERE catstatus=1 ".
            "ORDER BY parentid ASC, catsorting ASC";
        $objResult = $objDatabase->Execute($query);
        while (!$objResult->EOF) {
            $this->arrCategoriesTable[$objResult->fields['parentid']][$objResult->fields['catid']]
                = stripslashes($objResult->fields['catname']);
            $this->arrCategoriesName[$objResult->fields['catid']]
                = stripslashes($objResult->fields['catname']);
            $this->arrParentCategoriesId[$objResult->fields['catid']]
                = $objResult->fields['parentid'];
            $this->arrParentCategoriesTable[$objResult->fields['catid']][$objResult->fields['parentid']]
                = stripslashes($objResult->fields['catname']);
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
                    FROM ".DBPREFIX."module_shop_products_attributes AS attributes,
                         ".DBPREFIX."module_shop_products_attributes_name AS name,
                         ".DBPREFIX."module_shop_products_attributes_value AS value
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


    // needed
    function getShopPage()
    {
        if (isset($_GET['cmd'])) {
            switch($_GET['cmd']) {
                case "success":
                    $_GET['act'] = "success";
                    break;
                case "confirm":
                    $_GET['act'] = "confirm";
                    break;
                case "lsv":
                    $_GET['act'] = "lsv";
                    break;
                case "einzug":
                    $_GET['act'] = "einzug";
                    break;
                case "payment":
                    $_GET['act'] = "payment";
                    break;
                case "account":
                    $_GET['act'] = "account";
                    break;
                case "cart":
                    $_GET['act'] = "cart";
                    break;
                case "products":
                    $_GET['act'] = "products";
                    break;
                case "discounts":
                    $_GET['act'] = "discounts";
                    break;
                case "lastFive":
                    $_GET['act'] = "lastFive";
                    break;
                case "login":
                    $_GET['act'] = "login";
                    break;
                case "terms":
                    $_GET['act'] = "terms";
                    break;
                case "sendpass":
                    $_GET['act'] = "sendpass";
                    break;
                case "changepass";
                    $_GET['act'] = "changepass";
                    break;
                default:
                    //$_GET['act'] = "products";
                    break;
            }
        }

        if (isset($_GET['act'])) {
            switch($_GET['act']) {
                case "terms":
                    $this->terms();
                    break;
                case "success":
                    $this->success();
                    break;
                case "confirm":
                    $this->confirm();
                    break;
                case "lsv":
                    $this->lsv();
                    break;
                case "einzug":
                    $this->einzug();
                    break;
                case "payment":
                    $this->payment();
                    break;
                case "account":
                    $this->account();
                    break;
                case "cart":
                    $this->cart();
                    break;
                case "products":
                    $this->products();
                    break;
                case "discounts":
                    $this->discounts();
                    break;
                case "lastFive":
                    $this->products();
                    break;
                case "login":
                    $this->login();
                    break;
                case "destroy":
                    $this->destroyCart();
                    $this->products();
                    break;
                case "paypalIpnCheck":
                    require_once ASCMS_MODULE_PATH."/shop/payments/paypal/paypal.class.php";
                    $objPaypal = new PayPal;
                    $objPaypal->ipnCheck();
                    exit;
                    break;
                case "sendpass":
                    $this->_sendpass();
                    break;
                case "changepass";
                    $this->_changepass();
                    break;
                default:
                    $this->products();
                    break;
            }
        }
        else {
            $this->products();
        }
        return $this->objTemplate->get();
    }


    function getShopNavbar($shopNavbarContent)
    {
        global $objDatabase, $_ARRAYLANG;
        $objTpl = new HTML_Template_Sigma('.');
        $objTpl->setErrorHandling(PEAR_ERROR_DIE);
        $objTpl->setTemplate($shopNavbarContent, true, true);

        if ($this->is_auth==1) {
            if (empty($this->customerCompany)) {
               $loginInfo = $this->customerPrefix." ".$this->customerLastname."<br />";
            } else {
               $loginInfo = $this->customerCompany."<br />";
            }
            $loginStatus = $_ARRAYLANG['TXT_LOGGED_IN_AS']."<br />";
        } else {
            $loginInfo = "";
            $loginStatus = $_ARRAYLANG['TXT_LOGGED_IN_AS_SHOP_GUEST'];
        }
        $objTpl->setVariable("SHOP_CART_INFO", $this->showCartInfo());
        $objTpl->setVariable("SHOP_LOGIN_STATUS", $loginStatus);
        $objTpl->setVariable("SHOP_LOGIN_INFO", $loginInfo);

        // start currencies
        if (!$this->_hideCurrencyNavbar) {
            if ($objTpl->blockExists("shopCurrencies")) {
                $objTpl->setCurrentBlock("shopCurrencies");

                $curNavbar = $this->objCurrency->getCurrencyNavbar();
                if (!empty($curNavbar)) {
                    $objTpl->setVariable("SHOP_CURRENCIES", $curNavbar);
                    $objTpl->setVariable("TXT_CURRENCIES", $_ARRAYLANG['TXT_CURRENCIES']);
                }
                $objTpl->parseCurrentBlock("shopCurrencies");
                // end currencies
            }
        }

        if($objTpl->blockExists("shopNavbar")) {
            $objTpl->setCurrentBlock("shopNavbar");
        }
        $currentCatId = 0;
        if (isset($_GET['catId'])) {
            $currentCatId = intval($_GET['catId']);
        }
        $treeArray = $this->_makeArrCategories();
        $arrayTreeParents = $this->_makeArrCurrentCategories($currentCatId);
        $thisTree = 0;
        $topLevelId = 0;
        $i_style = 1;

        foreach ($treeArray as $k => $v) {
            $expand = false;
            $level = intval($v);
            $pcat = $this->arrParentCategoriesId[$k];
            if ($level == 0) {
                $expand = true;
                $topLevelId = $currentCatId;
            } else {
                $pcat = $this->arrParentCategoriesId[$k];
                if ($k==$currentCatId) {
                    $thisTree = 1;
                } else {
                    $thisTree = 0;
                }
                if (in_array ($pcat, $arrayTreeParents) || $pcat == $topLevelId || $thisTree) {
                   $expand = true;
                }
            }
            if ($expand) {
                $width ="";
                $style ="";
                //$style_no = "";
                $styleLevel = 0;
                if ($level!=0) {
                    $count= $level*3;
                    for ($i = 1; $i <= $count; $i++) {$width .="&nbsp;&nbsp;";}
                } else {
                    //$style_no = "_$i_style";
                    $i_style++;
                }
                $styleLevel = $level+1;
                //$style = "shopnav$styleLevel$style_no";
                $style = "shopnavbar".$styleLevel;
                if ($currentCatId==$k) {
                    $style .= "_active";
                }
                $objTpl->setVariable(array(
                    'SHOP_CATEGORY_STYLE'  => $style,
                    'SHOP_CATEGORY_ID'     => $k,
                    'SHOP_CATEGORY_OFFSET' => $width,
                    'SHOP_CATEGORY_NAME'   => str_replace('"', '&quot;', $this->arrCategoriesName[$k]),
                ));
                $objTpl->parseCurrentBlock("shopNavbar");
            }
        }
        return $objTpl->get();
    }


    /**
     * Do admin tree array
     *
     * @param    integer  $parcat
     * @param    integer  $level
     * @param    integer  $maxlevel
     * @return   array    $this->treeArray
     */
    function _makeArrCategories($parcat=0, $level=0)
    {
        $list = $this->arrCategoriesTable[$parcat];
        if (is_array($list)) {
            // $key is the child catid, $val the category name
            while (list($key, $val) = each($list)) {
                $this->arrCategoriesSorted[$key]=$level;
// fix: the following line produces infinite loops if parent == child
//                if (isset($this->arrCategoriesTable[$key])) {
                if ( ($parcat != $key) &&
                     (isset($this->arrCategoriesTable[$key])) ) {
                    $this->_makeArrCategories($key, $level+1);
                }
            }
        }
        return $this->arrCategoriesSorted;
    }


    /**
     * Put the JavsScript cart into the requested webpage
     *
     * Generate the base structure of the JavsScript cart and put it
     * in the template block shopJsCart.
     *
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
        if (empty($_REQUEST['section']) || $_REQUEST['section'] != 'shop' || empty($_REQUEST['cmd']) || $_REQUEST['cmd'] == 'details') {

            $arrMatch = '';
            if (preg_match('#^([\n\r]?[^<]*<.*id=["\']shopJsCart["\'][^>]*>)(([\n\r].*)*)(</[^>]*>[^<]*[\n\r]?)$#', $cartTpl, $arrMatch)) {
                $cartTpl = preg_replace('/\{([A-Z0-9_-]+)\}/', '[[\\1]]', $arrMatch[2]);

                $regs = '';
                if (preg_match_all('@<!--\s+BEGIN\s+(shopJsCartProducts)\s+-->(.*)<!--\s+END\s+\1\s+-->@sm', $cartTpl, $regs, PREG_SET_ORDER)) {
                    $cartProductsTpl = preg_replace('/\{([A-Z0-9_-]+)\}/', '[[\\1]]', $regs[0][2]);
                    $cartTpl = preg_replace('@(<!--\s+BEGIN\s+(shopJsCartProducts)\s+-->.*<!--\s+END\s+\2\s+-->)@sm', '[[SHOP_JS_CART_PRODUCTS]]', $cartTpl);
                }

                $jsCart = $arrMatch[1].$_ARRAYLANG['TXT_SHOP_CART_IS_LOADING'].$arrMatch[4]."\n";
                $jsCart .= "<script type=\"text/javascript\" src=\"modules/shop/lib/html2dom.js\"></script>\n";

                $jsCart .= "<script type=\"text/javascript\">\n";
                $jsCart .= "// <![CDATA[\n";
                $jsCart .= "cartTpl = '".preg_replace(array("/'/", '/[\n\r]/', '/\//'), array("\\'", '\n','\\/'), $cartTpl)."';\n";
                $jsCart .= "cartProductsTpl = '".preg_replace(array("/'/", '/[\n\r]/'), array("\\'", '\n'), $cartProductsTpl)."';\n";
                $jsCart .= "if (typeof(objCart) != 'undefined') {shopGenerateCart();};\n";
                $jsCart .= "// ]]>\n";
                $jsCart .= "</script>\n";
                if ($_REQUEST['section'] != 'shop') {
					$jsCart .= Shop::getJavascriptCode();
				}
            }
        }
        return $jsCart;
    }


    /**
     * Get trail
     *
     * @param     integer  $currentid
     * @return    integer  $allparents
     */
    function _makeArrCurrentCategories($currentid=1)
    {
        $a = array();
        while ($currentid != 0) {
            $x = $this->arrParentCategoriesTable[$currentid];
            if (!is_array($x)) {
                $a[]       = 0;
                $currentid = 0;
            } else {
                $result = each($x);
                $a[]       = $result[0];
                $currentid = $result[0];
            }
        }
        return $a;
    }


    function _changepass()
    {
        global $objDatabase, $_ARRAYLANG;

        if (!isset($_SESSION['shop']['username'])) {
            header('Location: index.php?section=shop&cmd=login');
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
                            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop_customers SET `password`='".md5(contrexx_stripslashes($_POST['shopNewPassword']))."' WHERE username='".addslashes($_SESSION['shop']['username'])."' AND `password`='".md5(contrexx_stripslashes($_POST['shopCurrentPassword']))."'");
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
                'SHOP_PASSWORD_STATUS'  => $status."<br />",
            ));
            $this->objTemplate->parse('shop_change_password_status');
        } else {
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
                             lastname FROM ".DBPREFIX."module_shop_customers WHERE email='".$mail."'";
            $objResult = $objDatabase->SelectLimit($query, 1);
            if ($objResult !== false) {
                if ($objResult->RecordCount() == 1) {
                    $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRESTUVWXYZ+-.,;:_!?$='#%&/()";
                    $passwordLength = rand(6, 8);
                    $password = '';
                    for ($char = 0; $char < $passwordLength; $char++) {
                        $password .= substr($chars, rand(0, 80), 1);
                    }

                    if ($objDatabase->Execute("UPDATE ".DBPREFIX."module_shop_customers SET password='".md5($password)."' WHERE customerid=".$objResult->fields['customerid']) !== false) {
                        $this->shopSetMailtemplate(3);
                        $shopMailFrom = $this->arrShopMailTemplate['mail_from'];
                        $shopMailFromText = $this->arrShopMailTemplate['mail_x_sender'];
                        $shopMailSubject = $this->arrShopMailTemplate['mail_subject'];
                        $shopMailBody = $this->arrShopMailTemplate['mail_body'];
                        //replace variables from template
                        $shopMailBody = str_replace("<USERNAME>",$objResult->fields['username'],$shopMailBody);
                        $shopMailBody = str_replace("<PASSWORD>",$password,$shopMailBody);
                        $shopMailBody = str_replace("<CUSTOMER_PREFIX>",$objResult->fields['prefix'],$shopMailBody);
                        $shopMailBody = str_replace("<CUSTOMER_LASTNAME>",$objResult->fields['lastname'],$shopMailBody);
                        $result = $this->shopSendmail($mail,$shopMailFrom,$shopMailFromText,$shopMailSubject,$shopMailBody,"");

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
     *
     *
     * @param integer $parentId
     * @todo    Documentation!
     * @todo    Fix template field 'TXT_ADD_TO_CARD' being filled with string constant
     *          instead of value from $_ARRAYLANG
     */
    function getCategories($parentId)
    {
        global $objDatabase;

        if($parentId == '') {
            $parentId = 0;
        }

        $query = "SELECT catid, catname
                    FROM ".DBPREFIX."module_shop_categories
                   WHERE parentid=".$parentId.' AND catstatus != 0
                   ORDER BY catsorting ASC, catname ASC';
        $objResult = $objDatabase->Execute($query);

        $this->objTemplate->setCurrentBlock('shopProductRow');
        $cell = 1;
        if($objResult !== false) {
            while (!$objResult->EOF) {
                //$file          = '';
                $thumbnailPath = '';
                $catId_Pic     = $objResult->fields['catid'];
                $catId_Link    = $objResult->fields['catid'];
                $catName       = $objResult->fields['catname'];

                if ($parentId == 0) {
                    $querySubCat = "SELECT catid FROM ".DBPREFIX."module_shop_categories ".
                                   "WHERE parentid=".$catId_Pic;
                    $objResultSubCat = $objDatabase->SelectLimit($querySubCat, 1);
                    if (!$objResultSubCat->EOF) {
                        $catId_Pic = $objResultSubCat->fields['catid'];
                    }
                }

                $queryProduct = "SELECT picture ".
                    "FROM ".DBPREFIX."module_shop_products ".
                    "WHERE catid=".$catId_Pic." ORDER BY sort_order";
                $objResultProduct = $objDatabase->SelectLimit($queryProduct, 1);
                if ($objResultProduct) {
                    $arrImages = $this->_getShopImagesFromBase64String($objResultProduct->fields['picture']);
                }

                // no product picture available
                if (!isset($arrImages) || $arrImages[1]['img'] == '') {
                    $thumbnailPath = $this->_defaultImage;
                } else {
                    // path offset is saved WITHOUT the image path!
                    $thumbnailPath = $this->shopImageWebPath.$arrImages[1]['img'].$this->thumbnailNameSuffix;
                }

                $this->objTemplate->setVariable(array(
                    'SHOP_PRODUCT_TITLE'            => str_replace('"', '&quot;', $catName),
                    'SHOP_PRODUCT_THUMBNAIL'        => $thumbnailPath,
                    'TXT_ADD_TO_CARD'               => "zur Kategorie",
                    'SHOP_PRODUCT_DETAILLINK_IMAGE' => "index.php?section=shop&amp;catId=".$catId_Link,
                    'SHOP_PRODUCT_SUBMIT_FUNCTION'  => "location.replace('index.php?section=shop&catId=".$catId_Link."')",
                    'SHOP_PRODUCT_SUBMIT_TYPE'      => "button",
                ));

                if ($this->objTemplate->blockExists('subCategories')) {
                    $this->objTemplate->parse('subCategories');
                       if($cell++ % 4 == 0) {
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

        /*start categories list*/
        $this->rowSplitter = 1;
        if (!isset($_REQUEST['catId']) && !isset($_REQUEST['productId'])) {
            $this->getCategories('');
        } else {
            if (!isset($_REQUEST['productId'])) {
                $this->getCategories($_REQUEST['catId']);
            }
        }
        /*end categories list*/

        // Check payment validation
        // Return URI after payment was successful
        if (isset($_GET['handler']) && !empty($_GET['handler'])) {
            switch ($_GET['handler']) {
                case "yellowpay":
                    $this->_checkPaymentValidation($_POST['txtOrderIDShop'],$_POST['SessionId']);
                    exit;
            }
        }

        // initialize variabes
        $pos        = 0;
        $paging     = "";
        $class      = "";
        $detailLink = "";
        $catId      = isset($_REQUEST['catId']) ? intval($_REQUEST['catId']) : 0;
        $ManufacturerId = isset($_REQUEST['ManufacturerId']) ? intval($_REQUEST['ManufacturerId']) : 0;
        $term       = isset($_REQUEST['term'])  ? stripslashes(trim($_REQUEST['term'])) : '';

        $treeArray = $this->_makeArrCategories();
        $arrCats = array_keys($treeArray);
        if (!$catId) {
            $catId = 0; //$arrCats[0];
        }
        if ($this->objTemplate->blockExists('shopNextCategoryLink')) {
            $nextCat = isset($arrCats[array_search($catId, $arrCats)+1]) ? $arrCats[array_search($catId, $arrCats)+1] : $arrCats[0];
            $this->objTemplate->setVariable(array(
                'SHOP_NEXT_CATEGORY_ID'    => $nextCat,
                'SHOP_NEXT_CATEGORY_TITLE' => str_replace('"', '&quot;', $this->arrCategoriesName[$nextCat]),
                'TXT_SHOP_GO_TO_CATEGORY'  => $_ARRAYLANG['TXT_SHOP_GO_TO_CATEGORY'],
            ));
            $this->objTemplate->parse('shopNextCategoryLink');
        }

        $this->objTemplate->setGlobalVariable(array(
            'TXT_SEE_LARGE_PICTURE'      => $_ARRAYLANG['TXT_SEE_LARGE_PICTURE'],
            'TXT_ADD_TO_CARD'            => $_ARRAYLANG['TXT_ADD_TO_CARD'],
            'TXT_PRODUCT_ID'             => $_ARRAYLANG['TXT_PRODUCT_ID'],
            'TXT_SHOP_PRODUCT_CUSTOM_ID' => $_ARRAYLANG['TXT_SHOP_PRODUCT_CUSTOM_ID'],
            'TXT_WEIGHT'                 => $_ARRAYLANG['TXT_WEIGHT'],
            'TXT_SHOP_CATEGORIES'        => $_ARRAYLANG['TXT_SHOP_CATEGORIES'],
            'SHOP_JAVASCRIPT_CODE'       => $this->getJavascriptCode(),
        ));
        $this->objTemplate->setVariable('SHOPNAVBAR_FILE', $this->getShopNavbar($themesPages['shopnavbar']));

        $productId = isset($_REQUEST['productId']) ? intval($_REQUEST['productId']) : 0;
        if (isset($_REQUEST['referer']) && $_REQUEST['referer'] == 'cart') {
            $cartProdId = $productId;
            $productId = $_SESSION['shop']['cart']['products'][$productId]['id'];
        }

        $shopMenuOptions = $this->getCatMenu($catId);
        $shopMenu = '<form action="index.php?section=shop" method="post">';
        $shopMenu .= '<input type="text" name="term" value="'.htmlentities($term, ENT_QUOTES, CONTREXX_CHARSET).'" /><br />';
        $shopMenu .= '<select name="catId">';
        $shopMenu .= '<option value="0">'.$_ARRAYLANG['TXT_ALL_PRODUCT_GROUPS'].'</option>';
        $shopMenu .= $shopMenuOptions.'</select><br />';
        $shopMenu .= $this->_GetManufacturerSelect();
        $shopMenu .= '<input type="submit" name="Submit" value="'.$_ARRAYLANG['TXT_SEARCH'].'" /></form>';
        $this->objTemplate->setVariable("SHOP_MENU", $shopMenu);
        $this->objTemplate->setVariable("SHOP_CART_INFO", $this->showCartInfo());

        $q_search = "";
        // replaced by discounts()
        $q_special_offer = '';//"AND (is_special_offer = 1) ";
        $q1_category = "";
        $q2_category = "";
        $pagingTermQuery = "";
        $pagingCatIdQuery = "";

        // consider the category iff it has been requested, not if it has been
        // determined above
        if ($catId > 0) {
           $q_special_offer = "";
           $q1_category = "," .DBPREFIX."module_shop_categories AS c";
           $q2_category = "AND ( p.catid = c.catid
                           AND c.catid = $catId) ";
           $pagingCatIdQuery = "&amp;catId=".$catId;
        }

        if ($ManufacturerId > 0) {
           $q1_manufacturer = " AND manufacturer=".$ManufacturerId." ";
        }

        if (!empty($term)) {
           $q_special_offer = "";
           $q_search = "
               AND (  p.title LIKE '%".mysql_escape_string($term)."%'
                   OR p.description LIKE '%".mysql_escape_string($term)."%'
                   OR p.shortdesc LIKE '%".mysql_escape_string($term)."%'
                   OR p.product_id LIKE '%".mysql_escape_string($term)."%'
                   OR p.id LIKE '%".mysql_escape_string($term)."%')
           ";
           $pagingTermQuery = "&amp;term=".htmlentities($term, ENT_QUOTES, CONTREXX_CHARSET);
        }

        $objResult = '';
        $count = 0;
        if (isset($_GET['cmd']) && $_GET['cmd'] == 'lastFive') {
            $query = "SELECT * FROM ".DBPREFIX."module_shop_products ".
                     "WHERE status=1 ".
                     "ORDER BY id DESC";
            $objResult = $objDatabase->SelectLimit($query, 5);
            $count = $objResult->RecordCount();
        } else {
            if ($productId != 0) {
                $q = "SELECT *
                        FROM ".DBPREFIX."module_shop_products
                       WHERE id=$productId";
            } else {
                $q =
                    "SELECT p.id, p.product_id, p.picture, p.title, ".
                        "p.normalprice, p.resellerprice, p.shortdesc, ".
                        "p.description, p.stock, p.stock_visibility, ".
                        "p.manufacturer, p.manufacturer_url, p.discountprice, p.is_special_offer, ".
                        "p.status, p.sort_order, p.vat_id, p.weight ".
                    "FROM ".DBPREFIX."module_shop_products AS p $q1_category ".
                    "WHERE status=1 $q_special_offer $q2_category $q_search $q1_manufacturer ".
                    "ORDER BY p.sort_order ASC, p.id DESC";
            }
            $objResult = $objDatabase->Execute($q);
            $count = $objResult->RecordCount();
            $paging = '';
            if ($count == 0) {
                $paging = $_ARRAYLANG['TXT_SELECT_SUB_GROUP'];
            } elseif ($_CONFIG['corePagingLimit']) { // $_CONFIG from /config/settings.php
                $pos = (isset($_GET['pos']) ? intval($_GET['pos']) : 0);
                $paging = getPaging(
                    $count,
                    $pos,
                    "&amp;section=shop".$pagingCatIdQuery.$pagingTermQuery,
                    '',
                    true);
                if (!($objResult = $objDatabase->SelectLimit($q, $_CONFIG['corePagingLimit'], $pos))) {
                    $this->errorHandling();
                    return false;
                }
            }
            if ($this->arrCategoriesName[$catId] != '') {
                $this->objTemplate->setVariable(array(
                    'TXT_PRODUCTS_IN_CATEGORY' => $_ARRAYLANG['TXT_PRODUCTS_IN_CATEGORY'],
                    'SHOP_CATEGORY_NAME'       => str_replace('"', '&quot;', $this->arrCategoriesName[$catId]),
                ));
            }
            $this->objTemplate->setVariable(array(
                'SHOP_PRODUCT_PAGING'      => $paging,
                'SHOP_PRODUCT_TOTAL'       => $count,
            ));
        }

        $this->objTemplate->setCurrentBlock('shopProductRow');
        if ($count > 0) {
            $formId = 0;
            while (!$objResult->EOF) {
                $productSubmitFunction = "";
                // if (($i % 2) == 0) {$class="row1";} else {$class="row2";}
                $arrPictures = $this->_getShopImagesFromBase64String($objResult->fields['picture']);
                $havePicture = false;
                foreach ($arrPictures as $index => $image) {
                    if (empty($image['img']) || $image['width'] == '' || $image['height'] == '') {
                        // we have at least one picture on display already.
                        // no need to show "no picture" three times!
                       if ($havePicture) { continue; }
                       $thumbnailPath = $this->_defaultImage;
                       $pictureLink = "javascript:alert('".$_ARRAYLANG['TXT_NO_PICTURE_AVAILABLE']."');";
                    } else {
                       $thumbnailPath = $this->shopImageWebPath.$image['img'].$this->thumbnailNameSuffix;
                       $pictureLink = "javascript:viewPicture('".$this->shopImageWebPath.$image['img']."','width=".($image['width']+25).",height=".($image['height']+25)."')";
                    }
                    $havePicture = true;

                    $this->objTemplate->setVariable(array(
                        'SHOP_PRODUCT_THUMBNAIL_'.$index       => $thumbnailPath,
                        'SHOP_PRODUCT_THUMBNAIL_LINK_'.$index  => $pictureLink,
                    ));
                    $this->objTemplate->setVariable(array(
                        'SHOP_PRODUCT_POPUP_LINK_'.$index      => $pictureLink,
                        'SHOP_PRODUCT_POPUP_LINK_NAME_'.$index => $_ARRAYLANG['TXT_SHOP_IMAGE'].' '.$index,
                    ));

                    if ($thumbnailPath != $this->_defaultImage.'.thumb' && $thumbnailPath != $this->_defaultImage && $thumbnailPath != '.thumb') {
                        // condition by Reto ('cause block not found)
                        if ($this->objTemplate->blockExists('productImage'.$index)) {
                           $this->objTemplate->parse('productImage'.$index);
                        }
                        if ($this->objTemplate->blockExists('productImageLink'.$index)) {
                            $this->objTemplate->parse('productImageLink'.$index);
                        }
                    } else {
                        // condition by Reto ('cause block not found)
                        if ($this->objTemplate->blockExists('productImage'.$index)) {
                            $this->objTemplate->hideBlock('productImage'.$index);
                        }
                        if ($this->objTemplate->blockExists('productImageLink'.$index)) {
                            $this->objTemplate->hideBlock('productImageLink'.$index);
                        }
                    }
                }

                // no product picture available
//                if (empty($fileArr[1])) {
//                    $thumbnailPath = $this->shopImageWebPath.$this->noPictureName;
//                    $pictureLink = "javascript:alert('".$_ARRAYLANG['TXT_NO_PICTURE_AVAILABLE']."');";
//                } else {
//                    $file = $fileArr[0].$this->thumbnailNameSuffix.".".$fileArr[1];
//                    $thumbnailPath = $this->shopImageWebPath.$file;
//                    $picturePath = $this->shopImageWebPath.$objResult->fields['picture'];
//                    $size=getimagesize($this->shopImagePath.$objResult->fields['picture']);
//                    $width=$size[0]+25;
//                    $height=$size[1]+25;
//                    $pictureLink = "javascript:viewPicture('".$picturePath."','width=".$width.",height=".$height."')";
//                }

                // Show the stock
                $stock = ($objResult->fields['stock_visibility']==1) ? $_ARRAYLANG['TXT_STOCK'].": ".intval($objResult->fields['stock']) : "";

                $manufacturerName 	= $objResult->fields['manufacturer'] > 0 ? $this->_GetManufacturer($objResult->fields['manufacturer'], 'name') : "";
                $manufacturerUrl 	= $this->_GetManufacturer($objResult->fields['manufacturer'], 'url') != '' ? "<a href=\"".$this->_GetManufacturer($objResult->fields['manufacturer'], 'url')."\" title=\"".$this->_GetManufacturer($objResult->fields['manufacturer'], 'url')."\" target=\"_blank\">".$this->_GetManufacturer($objResult->fields['manufacturer'], 'url')."</a>" : "";

                // Show the manufacturer hyperlink
                $manufacturerLink = strlen($objResult->fields['manufacturer_url'])>10 ? "<a href=\"".$objResult->fields['manufacturer_url']."\" title=\"".$_ARRAYLANG['TXT_MANUFACTURER_URL']."\" target=\"_blank\">".$_ARRAYLANG['TXT_MANUFACTURER_URL']."</a>" : "";

                // Show the price
                $price = $this->_getProductPrice($objResult->fields['normalprice'], $objResult->fields['resellerprice']);
                $discountPrice_Unit = $this->aCurrencyUnitName;
                // if no discountprice
                if ($objResult->fields['discountprice'] == "0.00" OR $objResult->fields['is_special_offer'] == 0) {
                    $discountPrice = "";
                    $discountPrice_Unit = "";
                } else {
                    $price = "<s>".$price."</s>";
                    $discountPrice = $this->objCurrency->getCurrencyPrice($objResult->fields['discountprice']);
                }

                $longDescription = $objResult->fields['description'];
                $shortDescription = $objResult->fields['shortdesc'];

                if ($productId == 0) {
                    $description = $shortDescription;
                    if (!empty($longDescription)) {
                        $detailLink = "<a href=\"index.php?section=shop&amp;cmd=details&amp;productId=".$objResult->fields['id']."\" title=\"".$_ARRAYLANG['TXT_MORE_INFORMATIONS']."\">".$_ARRAYLANG['TXT_MORE_INFORMATIONS']."</a>";
                    } else {
                        $detailLink = "";
                    }
                } else {
                    $description = $shortDescription;
                }

                // set submit button name
                if (isset($_GET['cmd']) && $_GET['cmd'] == "details" && isset($_GET['referer']) && $_GET['referer'] == "cart") {
                        $productSubmitName = "updateProduct[".$cartProdId."]";
                        $productSubmitFunction = $this->productOptions($objResult->fields['id'], $formId, $cartProdId);
                } else {
                        $productSubmitName = "addProduct";
                        $productSubmitFunction = $this->productOptions($objResult->fields['id'], $formId);
                }

                //call product options and set the submit name with the return value
                $shopProductFormName = "shopProductForm$formId";
                $this->objTemplate->setVariable(array(
                    'SHOP_ROWCLASS'                   => $class,
                    'SHOP_PRODUCT_ID'                 => $objResult->fields['id'],
                    'SHOP_PRODUCT_CUSTOM_ID'          => str_replace('"', '&quot;', $objResult->fields['product_id']),
                    'SHOP_PRODUCT_TITLE'              => str_replace('"', '&quot;', $objResult->fields['title']),
                    'SHOP_PRODUCT_DESCRIPTION'        => str_replace('"', '&quot;', $description),
                    'SHOP_PRODUCT_DETAILDESCRIPTION'  => str_replace('"', '&quot;', $longDescription),
                    'SHOP_PRODUCT_PRICE'              => ($price ? $price : ''),
                    'SHOP_PRODUCT_PRICE_UNIT'         => ($price ? $this->aCurrencyUnitName : ''),
                    'SHOP_PRODUCT_DISCOUNTPRICE'      => $discountPrice,
                    'SHOP_PRODUCT_DISCOUNTPRICE_UNIT' => $discountPrice_Unit,
                    'SHOP_PRODUCT_WEIGHT'             => Weight::getWeightString($objResult->fields['weight']),
                    'SHOP_PRODUCT_STOCK'              => $stock,
                    'SHOP_MANUFACTURER_NAME'          => $manufacturerName,
                    'SHOP_MANUFACTURER_URL'           => $manufacturerUrl,
                    'SHOP_MANUFACTURER_LINK'          => $manufacturerLink,
                    'SHOP_PRODUCT_DETAILLINK'         => $detailLink,
                    'SHOP_PRODUCT_FORM_NAME'          => $shopProductFormName,
                    'SHOP_PRODUCT_SUBMIT_NAME'        => $productSubmitName,
                    'SHOP_PRODUCT_SUBMIT_FUNCTION'    => $productSubmitFunction,
                ));
                if ($this->objVat->isEnabled()) {
                    $this->objTemplate->setVariable(array(
                        'SHOP_PRODUCT_TAX_PREFIX' =>
                            ($this->objVat->isIncluded()
                                ? $_ARRAYLANG['TXT_TAX_PREFIX_INCL']
                                : $_ARRAYLANG['TXT_TAX_PREFIX_EXCL']
                             ),
                        'SHOP_PRODUCT_TAX'        => $this->objVat->getShort($objResult->fields['vat_id'])
                   ));
                }
                $this->objTemplate->parse('shopProductRow');
                $formId++;
                $objResult->MoveNext();
            }
        } else {
            $this->objTemplate->hideBlock('shopProductRow');
        }
    }


    function productOptions($product_Id, $formName, $cartProdId = false)
    {
        global $_ARRAYLANG;

        // check if the product option block is set in the template
        if ($this->objTemplate->blockExists('shopProductOptionsRow') && $this->objTemplate->blockExists('shopProductOptionsValuesRow')) {
            $domId = 0;
            $checkOptionIds = "";

            if ($cartProdId !== false) {
                $product_Id = $_SESSION['shop']['cart']['products'][$cartProdId]['id'];
            }

            //start products options block
            $this->objTemplate->setCurrentBlock('shopProductOptionsRow');

            //start products options values block
            $this->objTemplate->setCurrentBlock('shopProductOptionsValuesRow');

            //check options
            if (!isset($this->arrProductAttributes[$product_Id])) {
                //hideblocks
                $this->objTemplate->hideBlock("shopProductOptionsRow");
                $this->objTemplate->hideBlock("shopProductOptionsValuesRow");
            } else {
                foreach ($this->arrProductAttributes[$product_Id] as $optionId => $arrOptionDetails) {
                    $selectValues = "";

                    // create head of option menu/checkbox/radiobutton
                    switch ($arrOptionDetails['type']) {
                        case '0':
                            $selectValues = "<select name=\"productOption[".$optionId."]\" id=\"productOption-".$product_Id."-{$optionId}-{$domId}\" style=\"width:180px;\">\n";
                            $selectValues .= "<option value=\"0\">".$arrOptionDetails['name']."&nbsp;".$_ARRAYLANG['TXT_CHOOSE']."</option>\n";
                            break;
                        case '1':
                            $selectValues = "<input type=\"hidden\" id=\"productOption-".$product_Id."-{$optionId}\" value=\"{$arrOptionDetails['name']}\" />\n";
                            $checkOptionIds .= "$optionId;";
                            break;

                        case '3':
                            $selectValues = "<input type=\"hidden\" id=\"productOption-".$product_Id."-{$optionId}\" value=\"{$arrOptionDetails['name']}\" />\n";
                            $selectValues .= "<select name=\"productOption[".$optionId."]\" id=\"productOption-".$product_Id."-{$optionId}-{$domId}\" style=\"width:180px;\">\n";
                            $selectValues .= "<option value=\"0\">".$arrOptionDetails['name']."&nbsp;".$_ARRAYLANG['TXT_CHOOSE']."</option>\n";
                            $checkOptionIds .= "$optionId;";
                            break;
                    }

                    $i = 0;
                    foreach ($arrOptionDetails['values'] as $valueId => $arrValues) {
                        $valuePrice = "";
                        $selected = false;

                        //price prefix
                        if ($arrValues['price'] != '') {
                            if ($arrValues['price'] != '0.00') {
                                $currencyPrice = $this->objCurrency->getCurrencyPrice($arrValues['price']);
                                $valuePrice    = '&nbsp;&nbsp;('.$arrValues['price_prefix'].'&nbsp;'.$currencyPrice.'&nbsp;'.$this->aCurrencyUnitName.')';
                            }
                        }

                        // mark the option value as selected if it was already selected and this site was requested from the cart
                        if ($cartProdId !== false && isset($_SESSION['shop']['cart']['products'][$cartProdId]['options'][$optionId])) {
                            if (in_array($valueId, $_SESSION['shop']['cart']['products'][$cartProdId]['options'][$optionId])) {
                                $selected = true;
                            }
                        }

                        // create option menu/checkbox/radiobutton
                        switch ($arrOptionDetails['type']) {
                            case '0':
                                $selectValues .= "<option value=\"$valueId\" ".($selected == true ? "selected=\"selected\"" : "")." >".$arrValues['value'].$valuePrice."</option>\n";
                                break;
                            case '1':
                                $selectValues .= "<input type=\"radio\" name=\"productOption[".$optionId."]\" id=\"productOption-".$product_Id."-{$optionId}-{$domId}\" value=\"$valueId\" ".($selected == true ? "checked=\"checked\"" : "")." /><label for=\"productOption-".$product_Id."-{$optionId}-{$domId}\">&nbsp;".$arrValues['value'].$valuePrice."</label><br />\n";
                                break;
                            case '2':
                                $selectValues .= "<input type=\"checkbox\" name=\"productOption[".$optionId."][$i]\" id=\"productOption-".$product_Id."-{$optionId}-{$domId}\" value=\"$valueId\" ".($selected == true ? "checked=\"checked\"" : "")." /><label for=\"productOption-".$product_Id."-{$optionId}-{$domId}\">&nbsp;".$arrValues['value'].$valuePrice."</label><br />\n";
                                break;
                            case '3':
                                $selectValues .= "<option value=\"$valueId\" ".($selected == true ? "selected=\"selected\"" : "")." >".$arrValues['value'].$valuePrice."</option>\n";
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

                    // initialize variables

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
                        'SHOP_PRODUCT_OPTIONS_TITLE' => "<a href=\"javascript:{}\" onclick=\"toggleOptions($product_Id)\" title=\"".$_ARRAYLANG['TXT_OPTIONS']."\">".$_ARRAYLANG['TXT_OPTIONS']."</a>\n",
                    ));

                    $this->objTemplate->parse('shopProductOptionsValuesRow');
                    //end products options values block
                }
                $this->objTemplate->parse('shopProductOptionsRow');
            }
        }
        return "return checkProductOption('shopProductForm{$formName}',$product_Id,'".substr($checkOptionIds,0,strlen($checkOptionIds)-1)."');";
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
                FROM ".DBPREFIX."module_shop_products
               WHERE is_special_offer = 1
                 AND status = 1
            ORDER BY sort_order";

        $objResult = $objDatabase->Execute($q);
        if (!$objResult) {
            $this->errorHandling();
            return false;
        }
        $count = $objResult->RecordCount();
        $i = 1;

        while (!$objResult->EOF) {
//            $fileArr = explode (".", $objResult->fields['picture']);
//            $file = $fileArr[0].$this->thumbnailNameSuffix.".".$fileArr[1];
//            $arrThumbnailPath[$i] = $this->shopImageWebPath.$file;

            $arrImages = $this->_getShopImagesFromBase64String($objResult->fields['picture']);

            // no product picture available
            if (!isset($arrImages) || $arrImages[1]['img'] == '') {
                $arrThumbnailPath[$i] = $this->_defaultImage;
            } else {
                $arrThumbnailPath[$i] = $this->shopImageWebPath.$arrImages[1]['img'].$this->thumbnailNameSuffix;
            }

            $price = $this->_getProductPrice($objResult->fields['normalprice'], $objResult->fields['resellerprice']);

            if ($objResult->fields['discountprice'] == 0) {
                $arrPrice[$i]         = $price;
                $arrDiscountPrice[$i] = "";
            } else {
                $arrPrice[$i] = "<s>".$price."</s>";
                $arrDiscountPrice[$i] = $this->objCurrency->getCurrencyPrice($objResult->fields['discountprice']);
            }
            $arrDetailLink[$i] = "index.php?section=shop&amp;cmd=details&amp;productId=".$objResult->fields['id'];
            $arrTitle[$i] = $objResult->fields['title'];
            $i++;
            $objResult->MoveNext();
        }

        $this->objTemplate->setGlobalVariable(array(
            'TXT_PRICE_NOW'  => $_ARRAYLANG['TXT_PRICE_NOW'],
            'TXT_INSTEAD_OF' => $_ARRAYLANG['TXT_INSTEAD_OF']
        ));

        for ($i=1; $i <= $count; $i = $i+2) {
                if (!empty($arrTitle[$i+1])) {
                    $this->objTemplate->setCurrentBlock("shopProductRow2");
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
                    $this->objTemplate->parse("shopProductRow2");
                }
                $this->objTemplate->setCurrentBlock("shopProductRow1");
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
                $this->objTemplate->parse("shopProductRow1");
        }
        return true;
    }


    function showCartInfo()
    {
        global $_ARRAYLANG;
        $cartInfo = "";

        if (isset($_SESSION['shop'])) {
            $cartInfo = $_ARRAYLANG['TXT_EMPTY_SHOPPING_CART'];
            if (isset($_SESSION['shop']['cart']) && $this->calculateItems($_SESSION['shop']['cart'])>0) {
                $cartInfo = $_ARRAYLANG['TXT_SHOPPING_CART']." ".$this->calculateItems($_SESSION['shop']['cart']).
                            " ".$_ARRAYLANG['TXT_SHOPPING_CART_VALUE']." ".$this->_calculatePrice($_SESSION['shop']['cart']).
                            " ".$this->aCurrencyUnitName;
                $cartInfo = "<a href=\"index.php?section=shop&amp;cmd=cart\" title=\"".$cartInfo."\">$cartInfo</a>";
            }
        }
        return $cartInfo;
    }


    function destroyCart()
    {
        if ($_SESSION['shop']) {
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
                              FROM ".DBPREFIX."module_shop_products
                             WHERE id = ".$arrProduct['id'], 1);
                if ($objResult !== false && $objResult->RecordCount() == 1) {
                    $item_price = $this->_getProductPrice($objResult->fields['normalprice'], $objResult->fields['resellerprice'],$objResult->fields['discountprice'],$objResult->fields['is_special_offer']);
                    $optionsPrice = !empty($arrProduct['optionPrice']) ? $arrProduct['optionPrice'] : 0;
                    $price +=($item_price+$optionsPrice)*$arrProduct['quantity'];
                }
            }
        }
        return $this->objCurrency->formatPrice($price);
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
    function _getProductPrice($normalPrice, $resellerPrice="0.00", $discountPrice="0.00", $is_special_offer=0)
    {
        if ($is_special_offer==1 AND $discountPrice!="0.00") {
            $price = $discountPrice;
        }
        else {
            if ($this->is_reseller==1 AND $resellerPrice!="0.00") {
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
        $shipmentPrice = $this->objShipment->calculateShipmentPrice($shipperId, $price, $weight);
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
    var arrOptionIds = strProductOptionIds.split(\";\");
    var status = true;
    var arrFailOptions = new Array();
    var optionName = '';
// local variables!
//    var formEl = '';
//    var elId = '';
    var elType = '';

    // check each option
    for (i = 0; i < arrOptionIds.length; i++) {
        checkStatus = false;

        // get options from form
        for (el = 0; el < document.forms[objForm].elements.length; el++) {
            // check if the element has a id attribute
            var formEl = document.forms[objForm].elements[el];
            if (formEl.getAttribute('id')) {
                // check if the element belongs to the option
                var searchName = 'productOption-'+productId+'-'+arrOptionIds[i];
                var elId = formEl.getAttribute('id');
                if (elId.substr(0,searchName.length) == searchName) {
                    // check if the element has a type attribute
                    if (formEl.type) {
                        elType = formEl.type;
                        switch (elType) {
                            case 'radio':
                                if (formEl.checked == true) {
                                    checkStatus = true;
                                }
                                break;

                            case 'select-one':
                                if (formEl.value > 0) {
                                    checkStatus = true;
                                }
                                break;

                            case 'hidden':
                                optionName = formEl.value;
                                break;
                        }

                    }
                }
            }
        } // end for
        if (checkStatus == false && (elType == 'radio' || elType == 'select-one')) {
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
?       "return true;\n}\n}"
:       "addProductToCart(objForm);
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
    objProduct = {id:0,title:'',options:{},info:{}};
    productOptionRe = /productOption\[([0-9]+)\]/;
    updateProductRe = /updateProduct\[([0-9]+)\]/;
    updateProduct = '';

    // get productId
    for (i = 0; i < document.forms[objForm].getElementsByTagName('input').length; i++) {
        formEl = document.forms[objForm].getElementsByTagName('input')[i];
        if (typeof(formEl.name) != 'undefined') {
            if (formEl.name == 'productId') {
                objProduct.id = formEl.value;
            }
            if (formEl.name == 'productTitle') {
                objProduct.title = formEl.value;
            }
            arrUpdateProduct = updateProductRe.exec(formEl.name);
            if (arrUpdateProduct != null) {
                updateProduct = '&updateProduct='+arrUpdateProduct[1];
            }
        }
    }

    // get product options of the new product
    for (el = 0; el < document.forms[objForm].elements.length; el++) {
        var formEl = document.forms[objForm].elements[el];

        arrName = productOptionRe.exec(formEl.getAttribute('name'));
        if (arrName != null) {
            optionId = arrName[1];

            switch (formEl.type) {
                case 'radio':
                    if (formEl.checked == true) {
                        objProduct.options[optionId] = formEl.value;
                    }
                    break;

                case 'checkbox':
                    if (formEl.checked == true) {
                        if (typeof(objProduct.options[optionId]) == 'undefined') {
                            objProduct.options[optionId] = new Array();
                        }
                        objProduct.options[optionId].push(formEl.value);
                    }
                    break;

                case 'select-one':
                    objProduct.options[optionId] = formEl.value;
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
            arrOptions.push(optionId+'\:['+objProduct.options[optionId].join(',')+']');
        } else {
            arrOptions.push(optionId+'\:'+objProduct.options[optionId]+'');
        }
    }

    productStr = '{id:'+objProduct.id+',options:{'+arrOptions.join(',')+'}}';

    sendReq('&product='+encodeURIComponent(productStr)+updateProduct, 1);

    return false;
}

function getXMLHttpRequestObj()
{
    var objXHR;
    if(window.XMLHttpRequest) {
        objXHR = new XMLHttpRequest();
    } else if(window.ActiveXObject) {
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
        objHttp.open('get', 'index.php?section=shop&cmd=cart&remoteJs=addProduct'+data, true);
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
        for (i in objCart.products) {
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
        $this->statusMessage.= $_ARRAYLANG['TXT_DATABASE_QUERY_ERROR']."<br />";
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
         $result = $this->doShopCatMenu($parcat=0, 0, $selectedid);
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
        $result = "";
        $list = $this->arrCategoriesTable[$parcat];
        if (is_array($list)) {
            while (list($key,$val)=each($list)) {
                $output = "";
                $selected = "";
                if ($level != 0) {
                    $count = $level*3;
                    for ($i = 1; $i <= $count; $i++) {
                        $output .="&nbsp;";
                    }
                }
                if ($selectedid == $key) {
                    $selected= "selected=\"selected\"";
                }
                $val = htmlentities($val, ENT_QUOTES, CONTREXX_CHARSET);
                $result.= "<option value=\"".$key."\" $selected>$output$val</option>\n";
// fix: the following line produces infinite loops if parent == child
//                if (isset($this->arrCategoriesTable[$key])) {
                if ( ($key != $parcat) &&
                   (isset($this->arrCategoriesTable[$key])) ) {
                    $result.= $this->doShopCatMenu($key, $level+1, $selectedid);
                }
            }
        }
        return $result;
    }


    /**
     * Function protectShop
     *
     * @global    object     $objDatabase
     * @return    boolean    result
     * @access private
     */
    function _authenticate()
    {
        global $objDatabase, $sessionObj;
        if (isset($_SESSION['shop']['username']) && isset($_SESSION['shop']['password'])) {
            $username = mysql_escape_string($_SESSION['shop']['username']);
            $password = md5(mysql_escape_string($_SESSION['shop']['password']));

            $query = "SELECT password,
                                   username,
                                   email,
                                   customerid,
                                   prefix,
                                   company,
                                   lastname,
                                   is_reseller
                              FROM ".DBPREFIX."module_shop_customers
                             WHERE username = '$username'
                               AND password='$password'";

            if ($objResult = $objDatabase->Execute($query)) {
                if (!$objResult->EOF) {
                    $this->is_reseller = $objResult->fields['is_reseller'];
                    $this->is_auth = 1;
                    $this->customerId= $objResult->fields['customerid'];
                    $this->customerUsername= $objResult->fields['username'];
                    $this->customerPrefix= $objResult->fields['prefix'];
                    $this->customerCompany= $objResult->fields['company'];
                    $this->customerLastname= $objResult->fields['lastname'];
                    $_SESSION['shop']['email'] = $objResult->fields['email'];
                    $sessionObj->cmsSessionUserUpdate($username=$objResult->fields['username']);

                    return true;
                }
            }
        }
        $sessionObj->cmsSessionUserUpdate($username="unknown");
        $sessionObj->cmsSessionStatusUpdate($status="shop");
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
        $this->_gotoLoginPage();
        // *MUST NOT* return if continue is set
        $arrProducts = $this->_parseCart();

        if (empty($_GET['remoteJs'])) {
            $this->_showCart($arrProducts);
        } else {
            $this->_sendJsonCart($arrProducts);
        }
    }


    /**
     * Inizialises the cart
     *
     * @global array $_SESSION
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
     *
     *
     * @param integer $oldCartProdId
     * @return array Returns an array of the product that has been sent through JSON
     */
    function _getJsonProduct(&$oldCartProdId)
    {
        if (empty($_REQUEST['product']) || !include_once(ASCMS_LIBRARY_PATH.'/PEAR/Services/JSON.php')) {
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
            die($objJson->encode($arrCart));
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
                'id'      => intval($_REQUEST['productId']),
                'options' => !empty($_POST['productOption']) ? $_POST['productOption'] : array()
            );
        }
        return $arrProduct;
    }


    function _addProductToCart($arrNewProduct, $oldCartProdId = null)
    {
        if (is_array($arrNewProduct) && isset($arrNewProduct['id'])) {
            // Add new product to cart
            $isNewProduct = true;
            if (count($_SESSION['shop']['cart']['products'])>0) {
                foreach ($_SESSION['shop']['cart']['products'] as $cartProdId => $arrProduct) {
                    // check if the same product is already in the cart
                    if ($arrProduct['id'] == $arrNewProduct['id'] && (!isset($oldCartProdId) || $oldCartProdId != $cartProdId)) {
                        if (isset($arrNewProduct['options']) && count($arrNewProduct['options']>0)) {
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
                    $arrProduct = array('id' => $arrNewProduct['id'], 'quantity' => 1);
                    array_push($_SESSION['shop']['cart']['products'], $arrProduct);
                    $arrKeys = array_keys($_SESSION['shop']['cart']['products']);
                    $cartProdId = $arrKeys[count($arrKeys)-1];
                }
            } else {
                if (isset($oldCartProdId)) {
                    if ($oldCartProdId != $cartProdId) {
                        $_SESSION['shop']['cart']['products'][$cartProdId]['quantity'] += $_SESSION['shop']['cart']['products'][$oldCartProdId]['quantity'];
                        unset($_SESSION['shop']['cart']['products'][$oldCartProdId]);
                    }
                } else {
                    $_SESSION['shop']['cart']['products'][$cartProdId]['quantity']++;
                }
            }

            //options array
            $_SESSION['shop']['cart']['products'][$cartProdId]['options'] = array();
            if (isset($arrNewProduct['options']) && count($arrNewProduct['options'])>0) {
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
                foreach ($_SESSION['shop']['cart']['products'] as $cartProdId => $arrProduct ) {
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
            header("Location: index.php?section=shop&cmd=login");
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
        global $objDatabase;

        $arrProducts      = array();
        $shipment         = false;
        $total_price      = 0;
        $total_tax_amount = 0;
        $total_weight     = 0;

        if (is_array($_SESSION['shop']['cart']['products']) && !empty($_SESSION['shop']['cart']['products'])) {
            foreach ($_SESSION['shop']['cart']['products'] as $cartProdId => $arrProduct) {
                $objResult = $objDatabase->SelectLimit(
                    "SELECT title, catid, product_id, handler, ".
                        "normalprice, resellerprice, discountprice, is_special_offer, ".
                        "vat_id, weight ".
                    "FROM ".DBPREFIX."module_shop_products ".
                    "WHERE status = 1 AND id = ".$arrProduct['id'], 1);

                if ($objResult !== false && $objResult->RecordCount()==1) {
                    $productOptions = '';
                    $productOptionsPrice = 0;

                    //get option names
                    foreach ($_SESSION['shop']['cart']['products'][$cartProdId]['options'] as $optionId => $arrValueIds) {
                        foreach ($arrValueIds as $valueId) {
                            $productOptions .= '['.$this->arrProductAttributes[$arrProduct['id']][$optionId]['values'][$valueId]['value'].'] ';
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
                    $itemweight = $objResult->fields['weight'];
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
                        'title'          => empty($_GET['remoteJs']) ? $objResult->fields['title'] : htmlspecialchars(utf8_encode($objResult->fields['title'])),
                        'options'        => $productOptions,
                        'price'          => $this->objCurrency->formatPrice($price),
                        'price_unit'     => $this->aCurrencyUnitName,
                        'quantity'       => $quantity,
                        'itemprice'      => $this->objCurrency->formatPrice($itemprice),
                        'itemprice_unit' => $this->aCurrencyUnitName,
                        'percent'        => $tax_rate,
                        'tax_amount'     => $this->objCurrency->formatPrice($tax_amount),
                        'itemweight'     => $itemweight, // in grams!
                        'weight'         => $weight,
                    ));

// the handler field is NOT set, edited or supported in any way in the backend!
// so we just suppose that everything is delivered for the time being.
                    if (!$shipment && $objResult->fields['handler'] == 'delivery') {
                    	$shipment = true;
                    }
                } else {
                    unset($_SESSION['shop']['cart']['products'][$cartProdId]);
                }
            }
        }

        $_SESSION['shop']['shipment']                 = $shipment;
        $_SESSION['shop']['cart']['total_price']      = $this->objCurrency->formatPrice($total_price);//$this->_calculatePrice($_SESSION['shop']['cart']);
        $_SESSION['shop']['cart']['total_tax_amount'] = $this->objCurrency->formatPrice($total_tax_amount);
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

        if (count($arrProducts)) {
            foreach ($arrProducts as $arrProduct) {
                // those fields that don't apply have been set to ''
                // (empty string) already -- see _parseCart().
                $this->objTemplate->setVariable(array(
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
                    'SHOP_PRODUCT_WEIGHT'         => Weight::getWeightString($arrProduct['weight']),

                ));
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
            'TXT_PRODUCT_ID'               => $_ARRAYLANG['TXT_SHOP_ID'],
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
            'TXT_WEIGHT'                   => $_ARRAYLANG['TXT_TOTAL_WEIGHT'],
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
        if ($_SESSION['shop']['shipment']) {
            $this->objTemplate->setVariable('SHOP_COUNTRIES_MENU', $this->_getCountriesMenu($menuName="countryId2", $selectedId=$_SESSION['shop']['countryId2'], $onchange="document.forms['shopForm'].submit()"));
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

        if ($this->is_auth==1) {
            // redirect to the checkout page
            header("Location: index.php?section=shop&cmd=account");
            exit;
        } else {
            $statusMessage = "";
            $loginUsername = "";

            if (!empty($_REQUEST['username']) AND !empty($_REQUEST['password'])) {
                // check authentification
                $_SESSION['shop']['username']= htmlspecialchars(addslashes(strip_tags($_REQUEST['username'])), ENT_QUOTES, CONTREXX_CHARSET);
                $_SESSION['shop']['password']=addslashes(strip_tags($_REQUEST['password']));
                $loginUsername = $_SESSION['shop']['username'];
                if ($this->_authenticate()) {
                    if (isset($_REQUEST['redirect']) && $_REQUEST['redirect'] == "shop") {
                        header("Location: index.php?section=shop");
                        exit;
                    } else {
                        header("Location: index.php?section=shop&cmd=account");
                        exit;
                    }
                } else {
                    $statusMessage = $_ARRAYLANG['txtNoValidCustomerAccount'];
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
                'SHOP_LOGIN_ACTION'                  => "?section=shop&amp;cmd=login",
                'SHOP_LOGIN_STATUS'                  => $statusMessage,
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
        $status = "";

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
            (empty($_POST['email']) && $this->is_auth == 0) ||
            (empty($_POST['password']) && $this->is_auth == 0)
        ) {
            $status = $_ARRAYLANG['TXT_FILL_OUT_ALL_REQUIRED_FIELDS'];
        }

        if (!empty($_POST['password']) && $this->is_auth == 0) {
            if (strlen(trim($_POST['password'])) < 6) {
                $status .= "<br />".$_ARRAYLANG['TXT_INVALID_PASSWORD'];
            }
        }

        if (!empty($_POST['email']) && $this->is_auth == 0) {
            if (!$this->shopCheckEmail($_POST['email'])) {
                $status .= "<br />".$_ARRAYLANG['TXT_INVALID_EMAIL_ADDRESS'];
            }
            if (!$this->_checkEmailIntegrity($_POST['email'])) {
                $status .= (empty($status) ? "" : "<br />").$_ARRAYLANG['TXT_EMAIL_USED_BY_OTHER_CUSTOMER'];
            }
        }

        return $status;
    }


    function _gotoPaymentPage()
    {
        global $objDatabase;

        foreach($_POST as $key => $value) {
            $value = get_magic_quotes_gpc() ? strip_tags(trim($value)) : addslashes(strip_tags(trim($value)));
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

        if ($objResult !== false) {
            if ($objResult->RecordCount() == 1) {
                header("Location: index.php?section=shop&cmd=payment");
            } else {
                header("Location: index.php?section=shop&cmd=confirm");
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
                    $_SESSION['shop']['equalAddress'] = "";
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
        if ($this->is_auth==1) {
            $query = "SELECT * FROM ".DBPREFIX."module_shop_customers WHERE customerid=".$this->customerId;
            $objResult = $objDatabase->SelectLimit($query, 1);

            $this->objTemplate->setVariable(array(
                'SHOP_ACCOUNT_COMPANY'       => $objResult->fields['company'],
                'SHOP_ACCOUNT_PREFIX'        => $objResult->fields['prefix'],
                'SHOP_ACCOUNT_LASTNAME'      => $objResult->fields['lastname'],
                'SHOP_ACCOUNT_FIRSTNAME'     => $objResult->fields['firstname'],
                'SHOP_ACCOUNT_ADDRESS'       => $objResult->fields['address'],
                'SHOP_ACCOUNT_ZIP'           => $objResult->fields['zip'],
                'SHOP_ACCOUNT_CITY'          => $objResult->fields['city'],
                'SHOP_ACCOUNT_COUNTRY'       => $this->arrCountries[$objResult->fields['country_id']]['countries_name'],
                //'SHOP_ACCOUNT_EMAIL'         => $objResult->fields['email'],
                'SHOP_ACCOUNT_PHONE'         => $objResult->fields['phone'],
                'SHOP_ACCOUNT_FAX'           => $objResult->fields['fax'],
                'SHOP_ACCOUNT_ACTION'        => "?section=shop&amp;cmd=payment"
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
            'SHOP_ACCOUNT_ACTION' => "?section=shop&amp;cmd=account"
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
        $shipmentMenu = $this->_getShipperMenu();
        $this->_initPaymentDetails();
        $this->_setPaymentTaxes();
        $paymentStatus = $this->_checkPaymentDetails();
        $this->_getPaymentPage($paymentStatus);
    }


    function _initPaymentDetails()
    {
         // Reloading or loading without sessions
        if (!isset($_SESSION['shop']['cart'])) {
            header("Location: index.php?section=shop");
            exit;
        }

        // hide currency navbar
        $this->_hideCurrencyNavbar=true;

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
                header('Location: index.php?section=shop&cmd=payment');
                exit;
            }
        } else {
            require_once ASCMS_MODULE_PATH."/shop/payments/paypal/paypal.class.php";
            $objPaypal = new PayPal;
            if (!in_array($this->objCurrency->getActiveCurrencyCode(), $objPaypal->arrAcceptedCurrencyCodes)) {
                foreach ($this->objCurrency->arrCurrency as $arrCurrency) {
                    if ($arrCurrency['status'] && $arrCurrency['code'] == $this->arrConfig['paypal_default_currency']['value']) {
                        $_SESSION['shop']['currencyIdPrev'] = $_SESSION['shop']['currencyId'];
                        $_SESSION['shop']['currencyId'] = $arrCurrency['id'];
                        header('Location: index.php?section=shop&cmd=payment');
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
        if (isset($_SESSION['shop']['shipment']) && $_SESSION['shop']['shipment']) {
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
        } else {
            return '';
        }
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
                    $_SESSION['shop']['grand_total_price']  = $this->objCurrency->formatPrice(
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
                    $_SESSION['shop']['grand_total_price']  = $this->objCurrency->formatPrice(
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
                    $_SESSION['shop']['tax_price'] = $this->objCurrency->formatPrice(
                        $_SESSION['shop']['cart']['total_tax_amount'] +
                        $this->objVat->calculateDefaultTax(
                            $_SESSION['shop']['payment_price'] +
                            $_SESSION['shop']['shipment_price']
                        ));
                    $_SESSION['shop']['grand_total_price'] = $this->objCurrency->formatPrice(
                        $_SESSION['shop']['total_price']    +
                        $_SESSION['shop']['payment_price']  +
                        $_SESSION['shop']['shipment_price'] +
                        $_SESSION['shop']['tax_price']);
                    $_SESSION['shop']['tax_products_txt']   = $_ARRAYLANG['TXT_TAX_EXCLUDED'];
                    $_SESSION['shop']['tax_grand_txt']      = $_ARRAYLANG['TXT_TAX_INCLUDED'];
                } else {
                    // foreign country; do not add tax
                    $_SESSION['shop']['tax_price']         = "0.00";
                    $_SESSION['shop']['grand_total_price'] = $this->objCurrency->formatPrice(
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
            $_SESSION['shop']['tax_products_txt']  = "";
            $_SESSION['shop']['tax_grand_txt']     = "";
            $_SESSION['shop']['grand_total_price'] = $this->objCurrency->formatPrice(
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
            $agbStatus = ($this->objTemplate->placeholderExists('SHOP_AGB') ? (!empty($_POST['agb']) ? true : false) : true);

            if ($agbStatus && $shipmentStatus && $paymentStatus) {
                // everything is set and valid
                header("Location: index.php?section=shop&cmd=confirm");
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
            // so it is lsv
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
             return 1;
        }
        return 0;
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

        $shopAddress = ($this->arrConfig['shop_address']['value'] ? $this->arrConfig['shop_address']['value'] : '');
        $shopAddress = preg_replace('/[\012\015]+/', ', ', $shopAddress);

        $objResult = '';
        if ($this->is_auth==1) {
            $query = "SELECT * FROM ".DBPREFIX."module_shop_customers WHERE customerid=".$this->customerId;
            $objResult = $objDatabase->SelectLimit($query, 1);
            $_SESSION['shop']['lastname']  = (isset($_SESSION['shop']['lastname'])  ? $_SESSION['shop']['lastname']  : $objResult->fields['lastname']);
            $_SESSION['shop']['firstname'] = (isset($_SESSION['shop']['firstname']) ? $_SESSION['shop']['firstname'] : $objResult->fields['firstname']);
            $_SESSION['shop']['address']   = (isset($_SESSION['shop']['address'])   ? $_SESSION['shop']['address']   : $objResult->fields['address']);
            $_SESSION['shop']['zip']       = (isset($_SESSION['shop']['zip'])       ? $_SESSION['shop']['zip']       : $objResult->fields['zip']);
            $_SESSION['shop']['city']      = (isset($_SESSION['shop']['city'])      ? $_SESSION['shop']['city']      : $objResult->fields['city']);
            $_SESSION['shop']['phone']     = (isset($_SESSION['shop']['phone'])     ? $_SESSION['shop']['phone']     : $objResult->fields['phone']);
            $_SESSION['shop']['fax']       = (isset($_SESSION['shop']['fax'])       ? $_SESSION['shop']['fax']       : $objResult->fields['fax']);
            $_SESSION['shop']['email']     = (isset($_SESSION['shop']['email'])     ? $_SESSION['shop']['email']     : $objResult->fields['email']);
        }

        // TODO: automatically insert product list into products field
        //'SHOP_LSV_EE_PRODUCTS'        => '',

        $this->objTemplate->setVariable(array(
            'SHOP_CUSTOMER_FIRST_NAME'    =>  (isset($_SESSION['shop']['firstname'])    ? stripslashes($_SESSION['shop']['firstname']) : ''),
            'SHOP_CUSTOMER_LAST_NAME'     =>  (isset($_SESSION['shop']['lastname'])     ? stripslashes($_SESSION['shop']['lastname']) : ''),
            'SHOP_CUSTOMER_ADDRESS'       =>  (isset($_SESSION['shop']['address'])      ? stripslashes($_SESSION['shop']['address']) : ''),
            'SHOP_CUSTOMER_ZIP'           =>  (isset($_SESSION['shop']['zip'])          ? stripslashes($_SESSION['shop']['zip']) : ''),
            'SHOP_CUSTOMER_CITY'          =>  (isset($_SESSION['shop']['city'])         ? stripslashes($_SESSION['shop']['city']) : ''),
            'SHOP_CUSTOMER_PHONE'         =>  (isset($_SESSION['shop']['phone'])        ? stripslashes($_SESSION['shop']['phone']) : ''),
            'SHOP_CUSTOMER_FAX'           =>  (isset($_SESSION['shop']['fax'])          ? stripslashes($_SESSION['shop']['fax']) : ''),
            'SHOP_CUSTOMER_EMAIL'         =>  (isset($_SESSION['shop']['email'])        ? stripslashes($_SESSION['shop']['email']) : ''),
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
     * Set up the page with the terms and conditions
     */
    function terms()
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


    /**
     * Set up the common fields of the payment page
     *
     * @param   unknown_type    $paymentStatus  Payment status
     * @param   unknown_type    $paymentMenu    Payment dropdown menu, {@see _getPaymentMenu()}
     * @param   unknown_type    $shipmentMenu   Shipment dropdown menu, {@see _getShipperMenu()}
     * @return  void
     * @todo    The methods for creating the dropdown menus could well be called
     *          from within here, instead of passing the strings around as arguments
     *          -- see {@see payment()} for the reason why this isn't the case yet.
     */
    function _getPaymentPage($paymentStatus)
    {
        global $_ARRAYLANG;
        $this->objTemplate->setVariable(array(
            'SHOP_UNIT'               => $this->aCurrencyUnitName,
            'SHOP_TOTALITEM'          => $_SESSION['shop']['items'],
            'SHOP_SHIPMENT_PRICE'     => $_SESSION['shop']['shipment_price'],
            'SHOP_PAYMENT_PRICE'      => $_SESSION['shop']['payment_price'],
            'SHOP_TOTALPRICE'         => $_SESSION['shop']['total_price'],
            'SHOP_SHIPMENT_MENU'      => $this->_getShipperMenu(),
            'SHOP_GRAND_TOTAL'        => $_SESSION['shop']['grand_total_price'],
            'SHOP_CUSTOMERNOTE'       => $_SESSION['shop']['customer_note'],
            'SHOP_AGB'                => $_SESSION['shop']['agb'],
            'SHOP_STATUS'             => $paymentStatus,
            'SHOP_PAYMENT_MENU'       => $this->_getPaymentMenu(),
            'SHOP_TOTAL_WEIGHT'       => Weight::getWeightString($_SESSION['shop']['cart']['total_weight']),
            'TXT_PRODUCTS'            => $_ARRAYLANG['TXT_PRODUCTS'],
            'TXT_TOTALLY_GOODS'       => $_ARRAYLANG['TXT_TOTALLY_GOODS'],
            'TXT_PRODUCT_S'           => $_ARRAYLANG['TXT_PRODUCT_S'],
            'TXT_SHIPPING_METHODS'    => $_ARRAYLANG['TXT_SHIPPING_METHODS'],
            'TXT_PAYMENT_TYPES'       => $_ARRAYLANG['TXT_PAYMENT_TYPES'],
            'TXT_ORDER_SUM'           => $_ARRAYLANG['TXT_ORDER_SUM'],
            'TXT_COMMENTS'            => $_ARRAYLANG['TXT_COMMENTS'],
            'TXT_TAC'                 => $_ARRAYLANG['TXT_TAC'],
            'TXT_ACCEPT_TAC'          => $_ARRAYLANG['TXT_ACCEPT_TAC'],
            'TXT_UPDATE'              => $_ARRAYLANG['TXT_UPDATE'],
            'TXT_NEXT'                => $_ARRAYLANG['TXT_NEXT'],
            'TXT_TOTAL_PRICE'         => $_ARRAYLANG['TXT_TOTAL_PRICE'],
            'TXT_TOTAL_WEIGHT'        => $_ARRAYLANG['TXT_TOTAL_WEIGHT'],
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
    }


    /**
     * Generate the complete overview of the order and generate the link to the payment offerer
     *
     * @access public
     */
    function confirm()
    {
        global $objDatabase, $_ARRAYLANG;

        // if the cart is missing, return to the shop
        if (!isset($_SESSION['shop']['cart'])) {
            header("Location: index.php?section=shop");
            exit;
        }
        // initalize variables
        $statusMessage = "";

        // hide currency navbar
        $this->_hideCurrencyNavbar=true;

        // Show payment processing page
        if (isset($_POST['process'])) {
            $this->objTemplate->hideBlock("shopConfirm");

            // check for page reloading
            if (!isset($_SESSION['shop']['orderid'])) {
                // register the order
                $customer_ip = htmlspecialchars($_SERVER['REMOTE_ADDR'], ENT_QUOTES, CONTREXX_CHARSET);
                $customer_host = htmlspecialchars(@gethostbyaddr($_SERVER['REMOTE_ADDR']), ENT_QUOTES, CONTREXX_CHARSET);
                $customer_lang = htmlspecialchars(getenv('HTTP_ACCEPT_LANGUAGE'), ENT_QUOTES, CONTREXX_CHARSET);
                $customer_browser = htmlspecialchars(getenv('HTTP_USER_AGENT'), ENT_QUOTES, CONTREXX_CHARSET);
                $customerid;
                $query = '';
                if ($this->is_auth == 1) {
                    $customerid = $this->customerId;
                    // update customer
                    $query = "
                        UPDATE ".DBPREFIX."module_shop_customers
                           SET company='".trim($_SESSION['shop']['company']," \t")."',
                           prefix='".trim($_SESSION['shop']['prefix']," \t")."',
                           firstname='".trim($_SESSION['shop']['firstname']," \t")."',
                           lastname='".trim($_SESSION['shop']['lastname']," \t")."',
                           address='".trim($_SESSION['shop']['address']," \t")."',
                           city='".trim($_SESSION['shop']['city']," \t")."',
                           zip='".trim($_SESSION['shop']['zip']," \t")."',
                           country_id='".intval($_SESSION['shop']['countryId'])."',
                           phone='".trim($_SESSION['shop']['phone']," \t")."',
                           fax='".trim($_SESSION['shop']['fax']," \t")."', ".
                           // the following fields are undefined in the first run!
                           "ccnumber='".(isset($_SESSION['shop']['ccnumber']) ? trim($_SESSION['shop']['ccnumber']," \t") : '')."',
                           ccdate='{".  (isset($_SESSION['shop']['ccdate'])   ?      $_SESSION['shop']['ccdate']          : '')."}',
                           ccname='".   (isset($_SESSION['shop']['ccname'])   ? trim($_SESSION['shop']['ccname']," \t")   : '')."',
                           cvc_code='". (isset($_SESSION['shop']['cvcCode'])  ? trim($_SESSION['shop']['cvcCode']," \t")  : '')."'
                     WHERE customerid=".$customerid;
                    $objResult = $objDatabase->Execute($query);
                } else {
                    // Add to customer table
                    $query =
                        "INSERT INTO ".DBPREFIX."module_shop_customers
                        (username, password, company, prefix, firstname, lastname,
                        address, city, zip, country_id, phone, fax, email,
                        ccnumber, ccdate, ccname, cvc_code,
                        customer_status, register_date)
                        VALUES ('".
                        trim($_SESSION['shop']['email']," \t")."', '".
                        md5($_SESSION['shop']['password'])."', '".
                        trim($_SESSION['shop']['company']," \t")."', '".
                        trim($_SESSION['shop']['prefix']," \t")."', '".
                        trim($_SESSION['shop']['firstname']," \t")."', '".
                        trim($_SESSION['shop']['lastname']," \t")."', '".
                        trim($_SESSION['shop']['address']," \t")."', '".
                        trim($_SESSION['shop']['city']," \t")."', '".
                        trim($_SESSION['shop']['zip']," \t")."', ".
                        intval($_SESSION['shop']['countryId']).", '".
                        trim($_SESSION['shop']['phone']," \t")."', '".
                        trim($_SESSION['shop']['fax']," \t")."', '".
                        trim($_SESSION['shop']['email']," \t")."', '".
                        trim($_SESSION['shop']['ccnumber']," \t")."', '".
                        $_SESSION['shop']['ccdate']."', '".
                        trim($_SESSION['shop']['ccname']," \t")."', '".
                        trim($_SESSION['shop']['cvcCode']," \t")."', ".
                        "1, NOW())";
                    $objResult = $objDatabase->Execute($query);
                    $customerid = $objDatabase->Insert_ID();
                }
                $_SESSION['shop']['customerid'] = $customerid;

                // Add to order table
                $query = "
                    INSERT INTO ".DBPREFIX."module_shop_orders
                    (customerid, selected_currency_id, currency_order_sum,
                    order_date, order_status, ship_company, ship_prefix,
                    ship_firstname, ship_lastname, ship_address, ship_city,
                    ship_zip, ship_country_id, ship_phone, currency_ship_price,
                    shipping_id, payment_id, currency_payment_price,
                    customer_ip, customer_host, customer_lang,
                    customer_browser, customer_note)
                    VALUES
                    ($customerid,
                    '{$_SESSION['shop']['currencyId']}',
                    '{$_SESSION['shop']['grand_total_price']}',
                    NOW(),
                    0,
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
                    (isset($_SESSION['shop']['shipperId']) && $_SESSION['shop']['shipperId']
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
                if ($objResult) {
                    $orderid = $objDatabase->Insert_ID();
                    $_SESSION['shop']['orderid'] = $orderid;

                    foreach ($_SESSION['shop']['cart']['products'] as $cartProdId => $arrProduct) {
                        $objResult = $objDatabase->SelectLimit(
                            "SELECT title, normalprice, resellerprice, discountprice, is_special_offer, ".
                                "vat_id, weight ".
                            "FROM ".DBPREFIX."module_shop_products ".
                            "WHERE status = 1 AND id = ".$arrProduct['id'], 1);
                        if ($objResult && $objResult->RecordCount() == 1) {
                            $price = $this->_getProductPrice($objResult->fields['normalprice'], $objResult->fields['resellerprice'],$objResult->fields['discountprice'],$objResult->fields['is_special_offer']);
                            $productName = $objResult->fields['title'];
                            $weight      = $objResult->fields['weight']; // grams
                            if ($weight == '') { $weight = 0; }
                            $vatId       = $objResult->fields['vat_id'];
                            $vatRate     = ($vatId ? $this->objVat->getRate($vatId) : '0.00');

                            // Add to order items table
                            $query = "INSERT INTO ".DBPREFIX."module_shop_order_items ".
                                "SET orderid=$orderid, ".
                                    "productid=".$arrProduct['id'].", ".
                                    "product_name='$productName', ".
                                    "price=".($price+
                                        (isset($arrProduct['optionPrice']) ? $arrProduct['optionPrice'] : 0)).", ".
                                    "quantity=".$arrProduct['quantity'].', '.
                                    "vat_percent=$vatRate, ".
                                    "weight=$weight";
                            $objResult = $objDatabase->Execute($query);
                            if ($objResult) {
                                $orderItemsId = $objDatabase->Insert_ID();

                                foreach ($arrProduct['options'] as $optionId => $arrValueIds) {
                                    foreach ($arrValueIds as $valueId) {
                                        // add product attributes to order items attribute table
                                        $query = "INSERT INTO ".DBPREFIX."module_shop_order_items_attributes ".
                                            "SET order_items_id=$orderItemsId, ".
                                                "order_id=$orderid, ".
                                                "product_id=".$arrProduct['id'].", ".
                                                "product_option_name='".$this->arrProductAttributes[$arrProduct['id']][$optionId]['name']."', ".
                                                "product_option_value='".$this->arrProductAttributes[$arrProduct['id']][$optionId]['values'][$valueId]['value']."', ".
                                                "product_option_values_price='".$this->objCurrency->getCurrencyPrice($this->arrProductAttributes[$arrProduct['id']][$optionId]['values'][$valueId]['price'])."', ".
                                                "price_prefix='".$this->arrProductAttributes[$arrProduct['id']][$optionId]['values'][$valueId]['price_prefix']."'";
                                        $objResult = $objDatabase->Execute($query);
                                        if (!$objResult) {
                                            unset($_SESSION['shop']['orderid']);
                                            $statusMessage .= $_ARRAYLANG['TXT_ERROR_INSERTING_ORDER_ITEM_ATTRIBUTE'];
                                        }
                                    }
                                }
                            } else {
                                unset($_SESSION['shop']['orderid']);
                                $statusMessage .= $_ARRAYLANG['TXT_ERROR_INSERTING_ORDER_ITEM'];
                            }
                        } else {
                            unset($_SESSION['shop']['orderid']);
                            $statusMessage .= $_ARRAYLANG['TXT_ERROR_LOOKING_UP_ORDER'];
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
                                addslashes($_SESSION['shop']['account_holder'])."', '".
                                addslashes($_SESSION['shop']['account_bank'])."', '".
                                addslashes($_SESSION['shop']['account_blz'])."')";
                            $objResult = $objDatabase->Execute($query);
                            if (!$objResult) {
                                unset($_SESSION['shop']['orderid']);
                                $statusMessage .= $_ARRAYLANG['TXT_ERROR_INSERTING_ACCOUNT_INFORMATION'];
                            }
                         } else {
                             // failure!
                             unset($_SESSION['shop']['orderid']);
                             $statusMessage .= $_ARRAYLANG['TXT_ERROR_ACCOUNT_INFORMATION_NOT_AVAILABLE'];
                         }
                    }
                    $this->objTemplate->setVariable('SHOP_PAYMENT_PROCESSING', $this->objProcessing->checkOut());
                } else {    // if ($objResult) (order)
                    // $orderId is unset!
                    $statusMessage .= $_ARRAYLANG['TXT_ERROR_STORING_CUSTOMER_DATA'];
                }
            } else {
                // some message here:
                // your order has already been placed!
                $statusMessage .= $_ARRAYLANG['TXT_ORDER_ALREADY_PLACED'];
            }
        } else {
            // set up the page for the user to confirm the order

            // make sure the page isn't called "cold"
            // (index.php?section=shop&cmd=confirm with no session yields a lot of notices).
            // suppose that if the customers' last name is set, so is the rest
            // -- there may be a better alternative though.
            if (!isset($_SESSION['shop']['lastname'])) {
                header('Location: index.php?section=shop');
                exit();
            }
            // Show confirmation page
            $this->objTemplate->hideBlock("shopProcess");
            $this->objTemplate->setCurrentBlock("shopCartRow");
            foreach ($_SESSION['shop']['cart']['products'] as $cartProdId => $arrProduct) {
                $objResult = $objDatabase->SelectLimit(
                    "SELECT title, catid, normalprice, resellerprice, discountprice, is_special_offer, ".
                        "vat_id, weight ".
                    "FROM ".DBPREFIX."module_shop_products ".
                    "WHERE status = 1 AND id = ".$arrProduct['id'], 1);
                if ($objResult !== false && $objResult->RecordCount()==1) {
                    $price = $this->_getProductPrice($objResult->fields['normalprice'], $objResult->fields['resellerprice'],$objResult->fields['discountprice'], $objResult->fields['is_special_offer']);

                    if (isset($arrProduct['optionPrice'])) {
                        $priceOptions = $arrProduct['optionPrice'];
                    } else {
                        $priceOptions = 0;
                    }

                    if (isset($arrProduct['options'])) {
                        $productOptions = "<br /><i>";
                        foreach ($arrProduct['options'] as $optionId => $arrValueIds) {
                            $productOptions .= "-".$this->arrProductAttributes[$arrProduct['id']][$optionId]['name'].": ";
                            foreach ($arrValueIds as $valueId) {
                                $productOptions .= $this->arrProductAttributes[$arrProduct['id']][$optionId]['values'][$valueId]['value']."; ";
                            }
                            $productOptions = substr($productOptions,0,strlen($productOptions)-2)."<br />";
                        }
                        $productOptions = substr($productOptions,0,strlen($productOptions)-6)."</i>";
                    } else {
                        $productOptions = "";
                    }

                    $weight     = $objResult->fields['weight']; // grams
                    $weight     = Weight::getWeightString($weight);
                    $vatId      = $objResult->fields['vat_id'];
                    $vatRate    = $this->objVat->getRate($vatId);
                    $vatPercent = $this->objVat->getShort($vatId);
                    $vatAmount  = $this->objVat->amount($vatRate, $price+$priceOptions);

                    $this->objTemplate->setVariable(array(
                        'SHOP_PRODUCT_ID'           => $arrProduct['id'],
                        'SHOP_PRODUCT_CUSTOM_ID'    => $arrProduct['product_id'],
                        'SHOP_PRODUCT_TITLE'        => str_replace('"', '&quot;', $objResult->fields['title'].$productOptions),
                        'SHOP_PRODUCT_PRICE'        => $this->objCurrency->formatPrice(($price+$priceOptions)*$arrProduct['quantity']),
                        'SHOP_PRODUCT_QUANTITY'     => $arrProduct['quantity'],
                        'SHOP_PRODUCT_ITEMPRICE'    => $this->objCurrency->formatPrice($price+$priceOptions),
                        'SHOP_UNIT'                 => $this->aCurrencyUnitName,
                        'SHOP_PRODUCT_WEIGHT'       => $weight,
                    ));
                    if ($this->objVat->isEnabled()) {
                        $this->objTemplate->setVariable(array(
                            'SHOP_PRODUCT_TAX_RATE'     => $vatPercent,
                            'SHOP_PRODUCT_TAX_AMOUNT'   =>
                                $this->objCurrency->formatPrice($vatAmount).
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
                'TXT_ID'                => $_ARRAYLANG['TXT_SHOP_ID'],
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
                'TXT_WEIGHT'            => $_ARRAYLANG['TXT_WEIGHT'],
                'TXT_SHOP_PRODUCT_CUSTOM_ID' => $_ARRAYLANG['TXT_SHOP_PRODUCT_CUSTOM_ID'],
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
        } // end if process
        $this->objTemplate->setVariable('SHOP_STATUS', $statusMessage);
    }


    /**
     * Check the data from the payment provider.
     * @access public
     */
    function success()
    {
        global $_ARRAYLANG;
        // hide currency navbar
        $this->_hideCurrencyNavbar=true;

        // check for active session otherwise redirect to shop start page
//        if (!isset($_SESSION['shop']['cart'])) {
//            header("Location: ?section=shop");
//            exit;
//        }

        $orderId = $this->objProcessing->checkIn();

        if (isset($orderId) && !empty($orderId) && $this->_checkPaymentValidation($orderId)) {
            $statusMessage = $_ARRAYLANG['TXT_ORDER_PROCESSED'];
        } else {
            $statusMessage = $_ARRAYLANG['TXT_NO_PENDING_ORDER'];
        }
        $this->objTemplate->setVariable('SHOP_STATUS',$statusMessage);
        $this->destroyCart();
    }


    /**
     * Check payment validation
     *
     * If the order exists and it wasn't already set to paid, do this and send an mail to the customer and the shop owners
     *
     * @access private
     * @return boolean validation
     * @see shopSendmail()
     */
    function _checkPaymentValidation($orderId)
    {
        global $objDatabase;

        $query = "SELECT order_status FROM ".DBPREFIX."module_shop_orders WHERE orderid = ".intval($orderId);
        $objResult = $objDatabase->Execute($query);
        if ($objResult->fields['order_status'] == 1) {
            $this->_sendProcessedMail();
            return true;
        } else {
            $query = "UPDATE ".DBPREFIX."module_shop_orders ".
                     "SET order_status='1' WHERE orderid =".intval($orderId);

            if ($objResult = $objDatabase->Execute($query)) {
                $this->_sendProcessedMail();
                return true;
            } else {
                return false;
            }
        }
    }


    /**
     * Sends an email with the order data
     *
     * @access private
     * @see _checkPaymentValidation()
     */
    function _sendProcessedMail()
    {
        $this->shopSetMailtemplate(1);
        $mailTo = $_SESSION['shop']['email'];
        $mailFrom = $this->arrShopMailTemplate['mail_from'];
        $mailFromText = $this->arrShopMailTemplate['mail_x_sender'];
        $mailSubject = $this->arrShopMailTemplate['mail_subject'];
        $mailBody = $this->arrShopMailTemplate['mail_body'];
        $today = date("d.m.Y");
        $mailSubject = str_replace("<DATE>",$today,$mailSubject);
        $mailBody = $this->_generateEmailBody($mailBody);
        $this->shopSendmail($mailTo,$mailFrom,$mailFromText,$mailSubject,$mailBody);
        $copies = explode(",",trim($this->arrConfig['confirmation_emails']['value']));
        foreach($copies as $sendTo) {
            $this->shopSendmail($sendTo,$mailFrom,$mailFromText,$mailSubject,$mailBody);
        }
    }


    /**
     * replace all substitute symbols and generate the email body
     *
     * @access private
     * @return string emailbody
     * @param string emailbody
     */
    function _generateEmailBody($body)
    {
        global $objDatabase, $_ARRAYLANG, $objDatabase;

        $body=stripslashes($body);
        $today = date(ASCMS_DATE_SHORT_FORMAT);
        $orderTime = date(ASCMS_DATE_FORMAT);
        $cartTxt = '';
        $taxTxt  = '';

        foreach ($_SESSION['shop']['cart']['products'] as $cartProdId => $arrProduct) {
            $objResult = $objDatabase->SelectLimit("
               SELECT product_id,
                      title,
                      catid,
                      normalprice,
                      resellerprice,
                      discountprice,
                      is_special_offer
                 FROM ".DBPREFIX."module_shop_products
                WHERE status = 1
                  AND id = ".$arrProduct['id'], 1);
            if ($objResult !== false && $objResult->RecordCount()==1) {
                $price = $this->_getProductPrice($objResult->fields['normalprice'], $objResult->fields['resellerprice'], $objResult->fields['discountprice'],$objResult->fields['is_special_offer']);
                $productName = substr($objResult->fields['title'], 0, 40);

                if (isset($arrProduct['optionPrice'])) {
                    $price += $arrProduct['optionPrice'];
                } else {
                    $priceOptions = 0;
                }

                if (isset($arrProduct['options']) && count($arrProduct['options'])>0) {
                    $productOptions = " (";
                    foreach ($arrProduct['options'] as $optionId => $arrValueIds) {
                        $productOptions .= $this->arrProductAttributes[$arrProduct['id']][$optionId]['name'].": ";
                        foreach ($arrValueIds as $valueId) {
                            $productOptions .= $this->arrProductAttributes[$arrProduct['id']][$optionId]['values'][$valueId]['value'].", ";
                        }
                        $productOptions = substr($productOptions,0,strlen($productOptions)-2)."; ";
                    }
                    $productOptions = substr($productOptions,0,strlen($productOptions)-2).")";
                } else {
                    $productOptions = "";
                }


                $cartTxt .= $arrProduct['id']." | ".
                            $objResult->fields['product_id']." | ".
                            $productName.$productOptions." | ".
                            $price." ".$this->aCurrencyUnitName." | ".
                            $arrProduct['quantity']." | ".
                            $this->objCurrency->formatPrice($price*$arrProduct['quantity'])." ".$this->aCurrencyUnitName."\n";
                $taxTxt = '';
                if ($this->objVat->isEnabled()) {
                    // taxes are enabled
                    $taxTxt = ($this->objVat->isIncluded()
                        ? $_ARRAYLANG['TXT_TAX_INCLUDED']
                        : $_ARRAYLANG['TXT_TAX_EXCLUDED']
                    )." ".$_SESSION['shop']['tax_price']." ".$this->aCurrencyUnitName;
                }
            }
        }
        $orderData =
"-----------------------------------------------------------------\n".
$_ARRAYLANG['TXT_ORDER_INFOS']."\n".
"-----------------------------------------------------------------\n".
$_ARRAYLANG['TXT_SHOP_ID']." | ".$_ARRAYLANG['TXT_SHOP_PRODUCT_CUSTOM_ID']." | ".$_ARRAYLANG['TXT_PRODUCT']." | ".$_ARRAYLANG['TXT_UNIT_PRICE']." | ".$_ARRAYLANG['TXT_QUANTITY']." | ".$_ARRAYLANG['TXT_TOTAL']."\n".
"-----------------------------------------------------------------\n".
$cartTxt.
"-----------------------------------------------------------------\n".
$_ARRAYLANG['TXT_INTER_TOTAL'].": ".$_SESSION['shop']['items']." ".$_ARRAYLANG['TXT_PRODUCT_S']." ".$_SESSION['shop']['total_price']." ".$this->aCurrencyUnitName."\n".
"-----------------------------------------------------------------\n".
$_ARRAYLANG['TXT_SHIPPING_METHOD'].": ".$this->objShipment->getShipperName($_SESSION['shop']['shipperId'])." ".$_SESSION['shop']['shipment_price']." ".$this->aCurrencyUnitName."\n".
$_ARRAYLANG['TXT_PAYMENT_TYPE'].": ".$this->objPayment->arrPaymentObject[$_SESSION['shop']['paymentId']]['name']." ".$_SESSION['shop']['payment_price']." ".$this->aCurrencyUnitName."\n".
$taxTxt."\n".
"-----------------------------------------------------------------\n".
$_ARRAYLANG['TXT_TOTAL_PRICE'].": ".$_SESSION['shop']['grand_total_price']." ".$this->aCurrencyUnitName."\n".
"-----------------------------------------------------------------\n";

        $orderSum = $_SESSION['shop']['grand_total_price']." ".$this->aCurrencyUnitName;
        $search  = array ('<ORDER_ID>', '<DATE>',
                          '<USERNAME>', '<PASSWORD>',
                          '<ORDER_DATA>', '<ORDER_SUM>', '<ORDER_TIME>', '<REMARKS>',
                          '<CUSTOMER_ID>', '<CUSTOMER_EMAIL>',
                          '<CUSTOMER_COMPANY>', '<CUSTOMER_PREFIX>', '<CUSTOMER_FIRSTNAME>',
                          '<CUSTOMER_LASTNAME>', '<CUSTOMER_ADDRESS>', '<CUSTOMER_ZIP>',
                          '<CUSTOMER_CITY>', '<CUSTOMER_COUNTRY>', '<CUSTOMER_PHONE>',
                          '<CUSTOMER_FAX>',
                          '<SHIPPING_COMPANY>', '<SHIPPING_PREFIX>', '<SHIPPING_FIRSTNAME>',
                          '<SHIPPING_LASTNAME>', '<SHIPPING_ADDRESS>', '<SHIPPING_ZIP>',
                          '<SHIPPING_CITY>', '<SHIPPING_COUNTRY>', '<SHIPPING_PHONE>'
                          );
        $replace = array ($_SESSION['shop']['orderid'], $today,
                          $_SESSION['shop']['email'], $_SESSION['shop']['password'],
                          $orderData, $orderSum, $orderTime, strip_tags($_SESSION['shop']['customer_note']),
                          $_SESSION['shop']['customerid'], $_SESSION['shop']['email'],
                          stripslashes($_SESSION['shop']['company']), stripslashes($_SESSION['shop']['prefix']), stripslashes($_SESSION['shop']['firstname']),
                          stripslashes($_SESSION['shop']['lastname']), stripslashes($_SESSION['shop']['address']), stripslashes($_SESSION['shop']['zip']),
                          stripslashes($_SESSION['shop']['city']), $this->arrCountries[$_SESSION['shop']['countryId']]['countries_name'], stripslashes($_SESSION['shop']['phone']),
                          stripslashes($_SESSION['shop']['fax']),
                          stripslashes($_SESSION['shop']['company2']), stripslashes($_SESSION['shop']['prefix2']), stripslashes($_SESSION['shop']['firstname2']),
                          stripslashes($_SESSION['shop']['lastname2']), stripslashes($_SESSION['shop']['address2']), stripslashes($_SESSION['shop']['zip2']),
                          stripslashes($_SESSION['shop']['city2']), $this->arrCountries[$_SESSION['shop']['countryId2']]['countries_name'], stripslashes($_SESSION['shop']['phone2']));
        $body = str_replace($search, $replace, $body);
        return $body;
    }
    /**
     * get manufacturer select options for "$shopmenu" (SearchForm)
     *
     */
    function _GetManufacturerSelect(){
    	global $objDatabase, $_ARRAYLANG, $objDatabase;
    	$ManufacturerSelect = '<select name="ManufacturerId" style="width: 220px;">';
        $ManufacturerSelect .= '<option value="0">'.$_ARRAYLANG["TXT_ALL_MANUFACTURER"].'</option>';
        $query = 'SELECT id, name, url FROM '.DBPREFIX.'module_shop_manufacturer ORDER BY name';
	    $objResult = $objDatabase->Execute($query);
	    $Count = 0;
	    while ($objResult && !$objResult->EOF) {
	    	if(isset($_REQUEST["ManufacturerId"])){
		    	if(intval($_REQUEST["ManufacturerId"])==$objResult->fields['id']){
		    		$SelectedText = 'selected';
		    	}else{
		    		$SelectedText = '';
		    	}
	    	}else{
	    		$SelectedText = '';
	    	}
	    	$ManufacturerSelect .= '<option value="'.$objResult->fields['id'].'" '.$SelectedText.'>'.$objResult->fields['name'].'</option>';
	    	$Count++;
	        $objResult->MoveNext();
	    }
        $ManufacturerSelect .= '</select><br />';
        if($Count>0){
        	return $ManufacturerSelect;
        }else{
        	return '';
        }
    }

    /**
     * get manufacturer name or url
     *
     */
     function _GetManufacturer($Manufacturer, $Field='name'){
     	global $objDatabase;
     	$objResult = $objDatabase->SelectLimit("SELECT ".$Field." FROM ".DBPREFIX."module_shop_manufacturer WHERE id = ".$Manufacturer, 1);
            if ($objResult !== false && $objResult->RecordCount()==1) {
            	return $objResult->fields[$Field];
            }else{
            	return '';
            }
     }
}

?>
