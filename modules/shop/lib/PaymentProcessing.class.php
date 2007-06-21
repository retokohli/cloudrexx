<?PHP
/**
 * Payment processing manager.
 * @package     contrexx
 * @copyright   CONTREXX CMS - COMVATION AG
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */


require_once ASCMS_MODULE_PATH .'/shop/payments/saferpay/Saferpay.class.php';
require_once ASCMS_MODULE_PATH .'/shop/payments/yellowpay/Yellowpay.class.php';
require_once ASCMS_MODULE_PATH .'/shop/payments/paypal/paypal.class.php';



class PaymentProcessing
{
	/**
	* active currency code (e.g. CHF, EUR, USD)
	* @access private
	* @var string $_currencyCode
	*/
    var $_currencyCode = NULL;


	/**
	* active language code (e.g. de, en, fr)
	* @access private
	* @var string $_languageCode
	*/
    var $_languageCode = NULL;


	/**
	* Shop configuration Array
	* @access public
	* @var array $arrConfig
	*/
    var $arrConfig = array();

    /**
     * Array of all available payment processors
     * @var array
     * @access public
     */
    var $arrPaymentProcessor = array();


    /**
     * Selected processor Id
     * @var int
     * @access private
     */
    var $_processorId = NULL;


	/**
	* shop payments image path (e.g. /modules/shop/images/payments/)
	* @access private
	* @var string $imagePath
	*/
    var $_imagePath;


	/**
	* Saferpay processing object
	* @access private
	* @var object $_objSaferpay
	*/
    var $_objSaferpay;

	/**
	* Yellowpay processing object
	* @access private
	* @var object $_objYellowpay
	*/
    var $_objYellowpay;

    /**
     * PayPal processing object
     * @access private
     * @var object $_objPayPal
     */
    var $_objPayPal;


	/**
    * Constructor
    *
    * @param  string
    * @access public
    */
    function PaymentProcessing($arrConfig)
    {
    	$this->__construct($arrConfig);
    }



    /**
    * PHP5 Constructor
    * @return void
    * @desc Initialize the shipping options as an indexed array
    */
    function __construct($arrConfig)
    {
    	global $objDatabase;

    	$this->arrConfig     = $arrConfig;
    	$this->_objSaferpay  = new Saferpay();
    	$this->_objYellowpay = new Yellowpay();
    	$this->_objPayPal    = new PayPal();
	    $this->_imagePath    = ASCMS_PATH_OFFSET . '/modules/shop/images/payments/';

     	$query = "SELECT id, type, name, description, company_url, status, picture, text ".
            "FROM ".DBPREFIX."module_shop_payment_processors ".
            "ORDER BY id";

     	$objResult = $objDatabase->Execute($query);
	 	while(!$objResult->EOF) {
			$this->arrPaymentProcessor[$objResult->fields['id']]= array(
	           'id' => $objResult->fields['id'],
	           'type' => $objResult->fields['type'],
	           'name' => $objResult->fields['name'],
	           'description' => $objResult->fields['description'],
	           'company_url' => $objResult->fields['company_url'],
	           'status' => $objResult->fields['status'],
	           'picture' => $objResult->fields['picture'],
	           'text' => $objResult->fields['text']
	           );
	     	$objResult->MoveNext();
		}
    }


    /**
    * @return void
    * @param unknown $processorId
    * @desc Initialize the processor Id
    */
    function initProcessor($processorId, $currencyCode, $languageCode)
    {
    	$this->_currencyCode = $currencyCode;
    	$this->_languageCode = $languageCode;
    	$this->_processorId  = $processorId;
    }


    /**
     * Returns the name associated with the given payment processor ID
     *
     * @param   integer     $processorId    The payment processor ID
     * @return  string                      The payment processors' name
     */
    function getPaymentProcessorName($processorId)
    {
        return $this->arrPaymentProcessor[$processorId]['name'];
    }


    /**
     * Check out the payment processor associated with the payment processor
     * selected by {@link initProcessor()}.
     *
     * If the page is redirected, or has already been handled, returns the empty
     * string.
     * In the other cases, returns HTML code for the payment form and to insert
     * a picture representing the payment method.
     *
     * @return  string      Empty string, or HTML code
     */
    function checkOut()
    {
		global $_ARRAYLANG;


		switch ($this->arrPaymentProcessor[$this->_processorId]['name']) {
			case 'Saferpay_All_Cards':
				$return = $this->_SaferpayProcessor();
				break;

			case 'Saferpay_Mastercard_Multipay_CAR':
				$return = $this->_SaferpayProcessor(array('Mastercard Multipay CAR'));
				break;

			case 'Saferpay_Visa_Multipay_CAR':
				$return = $this->_SaferpayProcessor(array('Visa Multipay CAR'));
				break;

			case 'PostFinance_DebitDirect':
                $return = $this->_YellowpayProcessor();
				break;

			case 'Internal':
			    /* Redirect browser */
			    header("location: index.php?section=shop&cmd=success&handler=Internal");
			    exit;
				break;

			case 'Internal_CreditCard':
                $return = $this->_Internal_CreditCardProcessor();
				break;

			case 'Internal_Debit':
                $return = $this->_Internal_DebitProcessor();
				break;

			case 'Internal_LSV':
			    /* Redirect browser */
			    header("location: index.php?section=shop&cmd=success&handler=Internal");
			    exit;
				break;

			case 'Paypal':
				$return = $this->_PayPalProcessor();
				break;
		}

		// shows the payment picture
		$return .= $this->_getPictureCode();

		return $return;
    }




    function _getPictureCode()
    {
		$imageName = $this->arrPaymentProcessor[$this->_processorId]['picture'];
		return (!empty($imageName)) ? "<br /><br /><img src=\"".$this->_imagePath.$imageName."\" alt=\"\" title=\"\" /><br /><br />" : "";
    }


    /**
     * @return string form html code
     * @desc gets the Saferpay html code
     */
    function _SaferpayProcessor($arrCards = array())
    {
    	global $_ARRAYLANG;

    	if($this->arrConfig['saferpay_use_test_account']['status']==1) {
    		$this->_objSaferpay->isTest = true;
    	}

    	$arrShopOrder = array(
				'AMOUNT'		=> str_replace(".", "", $_SESSION['shop']['grand_total_price']),
				'CURRENCY'		=> $this->_currencyCode,
				'ORDERID'		=> $_SESSION['shop']['orderid'],
				'ACCOUNTID'		=> $this->arrConfig['saferpay_id']['value'],
				'SUCCESSLINK'	=> urlencode("http://".$_SERVER['SERVER_NAME'].'/index.php?section=shop&cmd=success&handler=saferpay'),
				'FAILLINK'		=> urlencode("http://".$_SERVER['SERVER_NAME'].'/index.php?section=shop&cmd=cart'),
				'BACKLINK'		=> urlencode("http://".$_SERVER['SERVER_NAME'].'/index.php?section=shop&cmd=cart'),
				'DESCRIPTION'	=> urlencode("\"".$_ARRAYLANG['TXT_ORDER_NR']." ".$_SESSION['shop']['orderid']."\""),
				'LANGID'		=> $this->_languageCode,
				'PROVIDERSET'	=> $arrCards
				);

		$payInitUrl = $this->_objSaferpay->payInit($arrShopOrder);
		$return = '';
		if(strtoupper(substr($payInitUrl,0,5)) == "ERROR") {
			$return .= "<font color=\"red\"><b>The Saferpay Payment processor couldn't be initialized!<br />".$payInitUrl."</b></font>";
		} else {
			$return .= "<script src=\"http://www.saferpay.com/OpenSaferpayScript.js\"></script>\n";
			switch ($this->arrConfig['saferpay_window_option']['value']){
				case 0: // iframe
					$return .= $_ARRAYLANG['TXT_ORDER_PREPARED']."<br/><br/>\n";
					$return .= "<iframe src=\"$payInitUrl\" width=\"580\" height=\"400\" scrolling=\"no\" marginheight=\"0\" marginwidth=\"0\" frameborder=\"0\" name=\"saferpay\"></iframe>\n";
					break;
				case 1: // popup
					$return .= $_ARRAYLANG['TXT_ORDER_LINK_PREPARED']."<br/><br/>\n";
					$return .= "<script language=\"javascript\" type=\"text/javascript\">
								function openSaferpay()
								{
									strUrl = '$payInitUrl';
									if(strUrl.indexOf(\"WINDOWMODE=Standalone\") == -1){
										strUrl += \"&WINDOWMODE=Standalone\";
									}
									oWin = window.open(
														strUrl,
														'SaferpayTerminal',
														'scrollbars=1,resizable=0,toolbar=0,location=0,directories=0,status=1,menubar=0,width=580,height=400'
									);

									if (oWin==null || typeof(oWin)==\"undefined\") {
										alert(\"The payment couldn't be initialized, because it seems that you are using a popup blocker!\");
									}
								}
								</script>\n";
					$return .= "<input type=\"button\" name=\"order_now\" value=\"".$_ARRAYLANG['TXT_ORDER_NOW']."\" onclick=\"openSaferpay()\" />\n";
					break;
				case 2: // new window
					$return .= $_ARRAYLANG['TXT_ORDER_LINK_PREPARED']."<br/><br/>\n";
					$return .= "<form method=\"post\" action=\"".$payInitUrl."\">\n<input type=\"Submit\" value=\"".$_ARRAYLANG['TXT_ORDER_NOW']."\">\n</form>\n";
					break;
			}
		}
		return $return;
    }



    /**
     * @return string form html code
     * @desc gets the Yellowpay html code
     */
    function _YellowpayProcessor()
    {
		global $_ARRAYLANG;
		$arrShopOrder = array(
				"txtShopId"			=> $this->arrConfig['yellowpay_id']['value'],
				"txtOrderTotal"		=> $_SESSION['shop']['grand_total_price'],
				"ShopId"			=> $this->arrConfig['yellowpay_shop_id'],
				"Hash_seed"			=> $this->arrConfig['yellowpay_hash_seed'],
				"txtLangVersion"	=> strtoupper($this->_languageCode),
				"txtArtCurrency"	=> $this->_currencyCode,
				"txtOrderIDShop"	=> $_SESSION['shop']['orderid'],
				"PaymentType"		=> "DebitDirect",
				"DeliveryPaymentType" => $this->arrConfig['yellowpay_delivery_payment_type']['value'],
				"SessionId"			=> $_SESSION['shop']['PHPSESSID']
				);

		$yellowpayForm = $this->_objYellowpay->getForm($arrShopOrder,$_ARRAYLANG['TXT_ORDER_NOW']);
		if(count($this->_objYellowpay->arrError) > 0) {
			$return .= "<font color=\"red\"><b>This payment type couldn't be initialized!</b></font>";
		} else {
			$return .= $yellowpayForm;
		}
		return $return;
    }


    /**
     * @return string form html code
     * @desc gets the PayPal html code
     */
    function  _PayPalProcessor()
    {

    	$PayPalForm = $this->_objPayPal->getForm();

    	return $PayPalForm;
    }


    function checkIn()
    {
    	$orderId = NULL;
    	$transaction = false;
    	if (isset($_GET['handler']) && !empty($_GET['handler'])) {
    		switch ($_GET['handler']) {
    			case 'saferpay':
					if ($this->arrConfig['saferpay_use_test_account']['status']==1) {
    					$this->_objSaferpay->isTest = true;
    				} else {
    					$arrShopOrder['ACCOUNTID'] = $this->arrConfig['saferpay_id']['value'];
    				}

			     	$transaction = $this->_objSaferpay->payConfirm() ? true : false;

					if (intval($this->arrConfig['saferpay_finalize_payment']['value'])==1) {
						if ($this->_objSaferpay->isTest==true) {
							$transaction = true;
						} else {
							$transaction = $this->_objSaferpay->payComplete($arrShopOrder) ? true : false;
						}
					}

					if($transaction) {
						$orderId = $this->_objSaferpay->getOrderId();
					}
    				break;
    			case 'paypal':
    				$orderId = $this->_objPayPal->payConfirm();
    				break;
    			case 'Internal':
			     	$orderId = $_SESSION['shop']['orderid'];
    				break;
    			case 'Internal_CreditCard':
			     	$orderId = $_SESSION['shop']['orderid'];
    				break;
    			case 'Internal_Debit':
			     	$orderId = $_SESSION['shop']['orderid'];
    				break;
    			case 'PostFinance_DebitDirect':
			     	$orderId = $_SESSION['shop']['orderid'];
    				break;
    			case 'Internal_LSV':
			     	$orderId = $_SESSION['shop']['orderid'];
    			default:
    				break;
    		}
    	}
    	return $orderId;
    }
}

?>
