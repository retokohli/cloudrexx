///////////////////////////////
// modules/shop/index.class.php
///////////////////////////////

/**
 * Use session in show
 * 
 * Check if the session will be required and has to get inizialized
 *
 * @return boolean
 */
function shopUseSession()
{
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



///////////////////////////////
// modules/shop/index.class.php - im Constructor
///////////////////////////////

if (shopUseSession()) {
	$this->_authenticate();
}
		

///////////////////////////////
//index.php
///////////////////////////////

//-------------------------------------------------------
// eCommerce Module
//-------------------------------------------------------
    case "shop":
		$modulespath = "modules/shop/index.class.php";
		if (file_exists($modulespath)) require_once($modulespath);
		else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
		if (shopUseSession() && (!isset($sessionObj) || !is_object($sessionObj))) $sessionObj=&new cmsSession();
		$shopObj = &new Shop($page_content);
		$objTemplate->setVariable('CONTENT_TEXT', $shopObj->getShopPage());
		$objTemplate->setVariable('SHOPNAVBAR_FILE', $shopObj->getShopNavbar($themesPages['shopnavbar']));
		$boolShop = true;
	break;
