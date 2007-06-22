<?php
/**
 * Class Yellowpay
 *
 * Interface for the payment mask yellowpay
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author       Thomas Däppen <thomas.daeppen@comvation.com>
 * @version      $Id:  Exp $
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */

// error_reporting(E_ALL);

class Yellowpay
{
	/**
	 * Return string of the function getForm()
	 * @access private
	 * @var string
	 * @see getForm(), addToForm()
	 */
	var $form;

	/**
	* Determine the transation mode
	* @access private
	* @var boolean
	* @see getForm()
	*/
	var $is_test = NULL;

	/**
	* Information that was handed over to the class
	* @access private
	* @var array
	* @see Yellowpay(), __construct(), checkPaymentTypeKeys(), checkOtherKeys(), checkKey(), addToForm(), ifExist()
	*/
	var $arrShopOrder = array();

	/**
	* Error messages
	* @access public
	* @var array
	* @see getForm(), checkKey()
	*/
	var $arrError = array();

	/**
	* Warning messages
	* @access public
	* @var array
	* @see checkPaymentTypeKeys(), checkKey()
	*/
	var $arrWarning = array();

	/**
	* Language codes
	* @access private
	* @var array
	* @see checkKey()
	*/
	var $arrLangVersion = array(
								"DE"=>2055,
								"US"=>2057,
								"IT"=>2064,
								"FR"=>4108
								);

	/**
	* Currency codes
	* @access private
	* @var array
	* @see checkKey()
	*/
	var $arrArtCurrency = array("CHF","USD","EUR");

	/**
	* Delivery payment types
	* @access private
	* @var array
	* @see checkKey()
	*/
	var $arrDeliveryPaymentType = array("immediate","deferred");

	/**
	* Payment types
	* @access private
	* @var array
	* @see checkPaymentTypeKeys()
	*/
	var $arrPaymentType = array(
								"DebitDirect"=>array(),
								"yellownet"=>array(),
								"Master"=>array(),
								"Visa"=>array(),
								"Amex"=>array(),
								"Diners"=>array(),
								"yellowbill"=>array("txtESR_Member","txtBLastName","txtBAddr1","txtBZipCode","txtBCity")
								);

	/**
	 * Constructor:
	 */
	function Yellowpay($is_test=false)
	{
		$this->__construct($is_test);
	}

	function __construct($is_test=false)
	{
		$this->is_test = $is_test;
	}


	/**
	 * Creates the HTML-Form and returns it.
	 *
	 * Creates the HTML-Form for requesting the yellowpay-service.
	 *
	 * @access public
	 * @return HTML-Form on success, nothing on failure
	 * @see checkRequiredKeys(), checkPaymentTypeKeys(), checkOtherKeys()
	 */
	function getForm($arrShopOrder,$submitValue="send")
	{
		global $_ARRAYLANG;
		$this->arrShopOrder = $arrShopOrder;

		if(!$this->is_test){
			// active modus
			$this->form ="<form action=\"https://yellowpay.postfinance.ch/checkout/Yellowpay.aspx?userctrl=Invisible\" method=\"post\">\n";
		} else {
			// in test modus
		    $this->form ="<form action=\"https://yellowpaytest.postfinance.ch/checkout/Yellowpay.aspx?userctrl=Invisible\" method=\"post\">\n";
		}

		$this->checkRequiredKeys();
		$this->checkPaymentTypeKeys();
		$this->checkOtherKeys();

		$this->form .= "<input type=\"submit\" name=\"submit\" value=\"$submitValue\" />\n";
		$this->form .= "</form>";

		return $this->form;
	}



	/**
	 * Check head keys
	 *
	 * Checks if all head keys were set correctly.
	 *
	 * @access private
	 * @see checkKey()
	 */
	function checkRequiredKeys()
	{
		$this->getHash();
		$this->checkKey("txtShopId");
		$this->checkKey("txtLangVersion");
		$this->checkKey("txtOrderTotal");
		$this->checkKey("txtArtCurrency");
	}

	/**
	 * Check payment keys
	 *
	 * Checks if all keys for the payment type were set correctly.
	 *
	 * @access private
	 * @see ifExist(), addToForm(), checkKey()
	 */
	function checkPaymentTypeKeys()
	{
		if ($this->ifExist("PaymentType"))
		{
			if (array_key_exists($this->arrShopOrder["PaymentType"],$this->arrPaymentType))
			{
				$this->arrShopOrder['TxtUseDynPM'] = "true";
				$this->addToForm("TxtUseDynPM");

				$this->arrShopOrder['txtPM_'.$this->arrShopOrder["PaymentType"].'_Status'] = "true";
				$this->addToForm("txtPM_".$this->arrShopOrder["PaymentType"]."_Status");

				foreach ($this->arrPaymentType[$this->arrShopOrder["PaymentType"]] as $key)
				{
					$this->checkKey($key);
				}
			}
			else
			{
				$this->arrWarning[] = "PaymentType(".$this->arrShopOrder["PaymentType"].") isn't valid.";
			}
			unset($this->arrShopOrder["PaymentType"]);
		}
	}

	/**
	 * Check optional keys
	 *
	 * Checks if all other (optional) keys were set correctly.
	 *
	 * @access private
	 * @see checkKey()
	 */
	function checkOtherKeys()
	{
		unset($this->arrShopOrder["ShopId"]);
		unset($this->arrShopOrder["Hash_seed"]);
		foreach (array_keys($this->arrShopOrder) as $key)
		{
			$this->checkKey($key);
		}
	}

	/**
	 * Checks and adds a keys
	 *
	 * Checks the value of a key for correctness.
	 * Then if the key was set correctly, he will be added to the HTML-Form.
	 *
	 * @access private
	 * @param string Key to check for correctness
	 * @return boolean True on success, false on failure
	 * @see ifExist, addToForm()
	 */
	function checkKey($key)
	{
		if ($this->ifExist($key))
		{
			switch ($key) {
				case "txtShopId":
					if (strlen($this->arrShopOrder[$key]) > 30)
					{
						$this->arrShopOrder[$key] = substr($this->arrShopOrder[$key],0,30);
						$this->arrWarning[] = $key." was cut to 30 characters.";
					}
					elseif ($this->arrShopOrder[$key] == "")
					{
						$this->arrError[] = $key." isn't valid";
					}
					break;
				case "txtLangVersion":
					if (array_key_exists(strtoupper($this->arrShopOrder[$key]),$this->arrLangVersion))
					{
						$this->arrShopOrder[$key] = $this->arrLangVersion[strtoupper($this->arrShopOrder[$key])];
					}
					else
					{
						$this->arrShopOrder[$key] = $this->arrLangVersion['US'];
						$this->arrWarning[] = $key." was set to US";
					}
					break;
				case "txtOrderTotal":
					if (!ereg("^[0-9]+\.[0-9]{1,2}$",$this->arrShopOrder[$key]))
					{
						$this->arrShopOrder[$key] = Currency::formatPrice($this->arrShopOrder[$key]);
						$this->arrWarning[] = $key." was reformated to ".$this->arrShopOrder[$key];
					}
					if ($this->arrShopOrder[$key] <= 0)
					{
						$this->arrError[] = $key." isn't valid.";
					}
					break;
				case "txtArtCurrency":
					if (!in_array(strtoupper($this->arrShopOrder[$key]),$this->arrArtCurrency))
					{
						$this->arrShopOrder[$key] = $this->arrArtCurrency[0];
						$this->arrWarning[] = $key." was set to ".$this->arrArtCurrency[0];
					}
					$this->arrShopOrder[$key] = strtoupper($this->arrShopOrder[$key]);
					break;
				case "txtOrderIDShop":
					if (strlen($this->arrShopOrder[$key]) > 18)
					{
						$this->arrShopOrder[$key] = substr($this->arrShopOrder[$key],0,18);
						$this->arrWarning[] = $key." was cut to 18 characters.";
					}
					break;
				case "DeliveryPaymentType":
					if (!in_array($this->arrShopOrder[$key],$this->arrDeliveryPaymentType))
					{
						$this->arrShopOrder[$key] = $this->arrDeliveryPaymentType['1'];
						$this->arrWarning[] = $key." was set to \"{$this->arrDeliveryPaymentType['1']}\".";
					}
					break;
				case "txtESR_Member":
					if (!ereg("^[0-9]{1,2}-[0-9]{1,6}-[0-9]$",$this->arrShopOrder[$key]))
					{
						$this->arrError[] = $key." isn't valid.";
					}
					break;
				case "txtESR_Ref":
					if (!strlen($this->arrShopOrder[$key]) == 16 or !strlen($this->arrShopOrder[$key]) == 27)
					{
						$this->arrWarning[] = $key." isn't valid.";
					}
					break;
				case "txtShopPara":
					if (strlen($this->arrShopOrder[$key]) > 255)
					{
						$this->arrShopOrder[$key] = substr($this->arrShopOrder[$key],0,255);
						$this->arrWarning[] = $key." was cut to 255 characters.";
					}
					break;
				case "txtBTitle":
					if (strlen($this->arrShopOrder[$key]) > 30)
					{
						$this->arrShopOrder[$key] = substr($this->arrShopOrder[$key],0,30);
						$this->arrWarning[] = $key." was cut to 30 characters.";
					}
					break;
				case "txtBLastName":
					if (strlen($this->arrShopOrder[$key]) > 40)
					{
						$this->arrShopOrder[$key] = substr($this->arrShopOrder[$key],0,40);
						$this->arrWarning[] = $key." was cut to 40 characters.";
					}
					break;
				case "txtBFirstName":
					if (strlen($this->arrShopOrder[$key]) > 40)
					{
						$this->arrShopOrder[$key] = substr($this->arrShopOrder[$key],0,40);
						$this->arrWarning[] = $key." was cut to 40 characters.";
					}
					break;
				case "txtBAddr1":
					if (strlen($this->arrShopOrder[$key]) > 40)
					{
						$this->arrShopOrder[$key] = substr($this->arrShopOrder[$key],0,40);
						$this->arrWarning[] = $key." was cut to 40 characters.";
					}
					break;
				case "txtBZipCode":
					if (strlen($this->arrShopOrder[$key]) > 10)
					{
						$this->arrShopOrder[$key] = substr($this->arrShopOrder[$key],0,10);
						$this->arrWarning[] = $key." was cut to 10 characters.";
					}
					break;
				case "txtBCity":
					if (strlen($this->arrShopOrder[$key]) > 40)
					{
						$this->arrShopOrder[$key] = substr($this->arrShopOrder[$key],0,40);
						$this->arrWarning[] = $key." was cut to 40 characters.";
					}
					break;
				case "txtBCountry":
					if (strlen($this->arrShopOrder[$key]) > 2)
					{
						$this->arrError[] = $key." isn't valid. 2 character ISO country code.";
					}
					else
					{
						$this->arrShopOrder[$key] = strtoupper($this->arrShopOrder[$key]);
					}
					break;
				case "txtBTel":
					if (strlen($this->arrShopOrder[$key]) > 40)
					{
						$this->arrShopOrder[$key] = substr($this->arrShopOrder[$key],0,40);
						$this->arrWarning[] = $key." was cut to 40 characters.";
					}
					break;
				case "txtBFax":
					if (strlen($this->arrShopOrder[$key]) > 40)
					{
						$this->arrShopOrder[$key] = substr($this->arrShopOrder[$key],0,40);
						$this->arrWarning[] = $key." was cut to 40 characters.";
					}
					break;
				case "txtBEmail":
					if (strlen($this->arrShopOrder[$key]) > 40)
					{
						$this->arrShopOrder[$key] = substr($this->arrShopOrder[$key],0,40);
						$this->arrWarning[] = $key." was cut to 40 characters.";
					}
					break;
				default:
					$this->arrWarning[] = $key." isn't a valid key!";
					break;
			}
			$this->addToForm($key);
			return true;
		}
		else
		{
			$this->arrError[] = $key." is needed for this operation!";
			return false;
		}
	}

	/**
	* Gets the hash of the order
	*
	* Generates the txtHash key and adds it to the HTML-form
	*
	*/
	function getHash()
	{
		if ($this->arrShopOrder["ShopId"] == ""){
			$this->arrError[] = "ShopId isn't valid";
		}
		if ($this->arrShopOrder["Hash_seed"] == ""){
			$this->arrError[] = "Hash_seed isn't valid";
		}
		$this->arrShopOrder["txtHash"] = md5($this->arrShopOrder["ShopId"].$this->arrShopOrder["txtArtCurrency"].$this->arrShopOrder["txtOrderTotal"].$this->arrShopOrder["Hash_seed"]);
		$this->addToForm("txtHash");
	}

	/**
	 * Adds a key to the HTML-Form
	 *
	 * It also deletes the key from the array $arrShopOrder after it was put in the HTML-Form.
	 *
	 * @param string Key to be added to the HTML-Form
	 */
	function addToForm($key)
	{
		$this->form .= "<input type=\"hidden\" name=\"$key\" value=\"{$this->arrShopOrder[$key]}\" />\n";
		unset($this->arrShopOrder[$key]);
	}

	/**
	 * Checks the existence of a key
	 *
	 * Checks if the given key exists in the array arrShopOrder.
	 * @param string Key to check for its existence
	 * @return boolean True on succedd, false on failure
	 */
	function ifExist($key)
	{
		if (array_key_exists($key,$this->arrShopOrder))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
?>