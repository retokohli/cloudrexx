<?php
class ProductAttributes
{
	var $_objTpl;
	var $statusMessage = "";
	var $defaultAttributeOption = 0;
	var $arrAttributes = array();
	var $defaultCurrency = "";
	var $highestIndex = 0;

	/**
	* Array of all Products attributes
	* @access public
	* @var array
	*/
	var $arrProductAttributes = array();


	function ProductAttributes($_objTpl)
	{
		$this->__construct($_objTpl);
	}


	function __construct($_objTpl)
	{
		$this->_objTpl = $_objTpl;
	}


	/**
	* Get attribute list
	*
	* Generate the standard attribute option/value list or the one of a product
	*
	* @access	private
	* @param	string	$productId	Product Id of which its list will be displayed
	*/
	function _getAttributeList($productId = 0)
	{
		global $objDatabase;
		$this->_initAttributes();

		if($productId>0) {
			$query =
                "SELECT attribute_id, product_id, attributes_name_id, attributes_value_id ".
                "FROM ".DBPREFIX."module_shop_products_attributes ".
                "WHERE product_id=".intval($productId);
			$objResult = $objDatabase($query);
			while(!$objResult->EOF) {
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
				    'SHOP_PRODUCTS_ATTRIBUTE_ID' 		    => $attributeId,
					'SHOP_PRODUCTS_ATTRIBUTE_VALUE_ID' 		=> $id,
					'SHOP_PRODUCTS_ATTRIBUTE_VALUE_TEXT'	=> $arrValues['value'].' ('.$arrValues['price_prefix'].$arrValues['price'].' '.$this->defaultCurrency.')',
					'SHOP_PRODUCTS_ATTRIBUTE_VALUE_SELECTED'	=> $attributeValueSelected == true ? "checked=\"checked\"" : ""
				));
				$this->_objTpl->parse('attributeValueList');
			}
			$this->_objTpl->setVariable(array(
				'SHOP_PRODUCTS_ATTRIBUTE_ID'	=> $attributeId,
				'SHOP_PRODUCTS_ATTRIBUTE_NAME'	=> $arrAttributeValues['name'],
				'SHOP_PRODUCTS_ATTRIBUTE_SELECTED'		=> $attributeSelected == true ? "checked=\"checked\"" : "",
				'SHOP_PRODUCTS_ATTRIBUTE_DISPLAY_TYPE'  => $attributeSelected == true ? "block" : "none",
			));
			$this->_objTpl->parse('attributeList');
		}
	}


	/**
	* Show attribute options
	*
	* Generate the attribute option/value list for its configuration
	*
	* @access	private
	*/
	function _showAttributeOptions()
	{
		global $_ARRAYLANG, $objDatabase;

		$this->_initAttributes();
		$arrAttributes = $this->arrAttributes;

		$rowClass = 1;
		$arrAttributNames = array();

		// delete option
		if (isset($_GET['delId']) && !empty($_GET['delId'])) {
			$this->statusMessage .= $this->_deleteAttributeOption($_GET['delId']);
		} elseif (isset($_POST['selectedOptionId']) && !empty($_POST['selectedOptionId'])) {
			$this->statusMessage .= $this->_deleteAttributeOption($_POST['selectedOptionId']);
		}

		// store new option
		if (isset($_POST['addAttributeOption']) && !empty($_POST['addAttributeOption'])) {
			$this->statusMessage .= $this->_storeNewAttributeOption();
		}

		// update attribute options
		if (isset($_POST['updateAttributeOptions']) && !empty($_POST['updateAttributeOptions'])) {
			$this->statusMessage .= $this->_updateAttributeOptions();
		}

		// set language variables
		$this->_objTpl->setVariable(array(
			'TXT_CONFIRM_DELETE_PRODUCT'	=> $_ARRAYLANG['TXT_CONFIRM_DELETE_PRODUCT'],
			'TXT_ACTION_IS_IRREVERSIBLE'	=> $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE']
		));

		$this->arrAttributes = array();
		$this->_initAttributes();
		$arrAttributes = $this->arrAttributes;

		foreach ($arrAttributes as $attributeId => $arrValues) {
			$this->_objTpl->setCurrentBlock('attributeList');
			$this->_objTpl->setVariable(array(
				'SHOP_PRODUCT_ATTRIBUTE_ROW_CLASS'			=> $rowClass%2 == 0 ? "row1" : "row2",
				'SHOP_PRODUCT_ATTRIBUTE_ID'					=> $attributeId,
				'SHOP_PRODUCT_ATTRIBUTE_NAME'				=> $arrValues['name'],
				'SHOP_PRODUCT_ATTRIBUTE_VALUE_MENU'			=> $this->_getAttributeValueMenu($attributeId,"attributeValueList[$attributeId]",$arrValues['values'],"","setSelectedValue($attributeId)","width:200px;"),
				'SHOP_PRODUCT_ATTRIBUTE_VALUE_INPUTBOXES'	=> $this->_getAttributeInputBoxes($attributeId,'attributeValue', 'value',32,'width:170px;'),
				'SHOP_PRODUCT_ATTRIBUTE_PRICE_INPUTBOXES'	=> $this->_getAttributeInputBoxes($attributeId,'attributePrice','price',9,'width:148px;text-align:right;'),
				'SHOP_PRODUCT_ATTRIBUTE_PRICEPREFIX_MENUS'	=> $this->_getAttributePricePrefixMenu($attributeId,'attributePricePrefix',$arrValues['values']['price_refix'])
			));
			$this->_objTpl->parseCurrentBlock();
			$rowClass++;

		}

		$this->_objTpl->setVariable(array(
			'SHOP_PRODUCT_ATTRIBUTE_JS_VARS'	=> $this->_getAttributeJSVars()."\nindex = ".$this->highestIndex.";\n",
			'SHOP_PRODUCT_ATTRIBUTE_CURRENCY'	=> $this->defaultCurrency
		));

		return $this->statusMessage;
	}


	/**
	* Initialize attributes
	*
	* Initialize the array $this->arrAttributes
	*
	* @access	private
	*/
	function _initAttributes()
	{
		global $objDatabase;
		// get attributes
		$query = "SELECT name.id AS nameId,
						 name.name AS nameTxt,
						 value.id AS valueId,
						 value.name_id AS valueNameId,
						 value.value AS valueTxt,
						 value.price AS price,
						 value.price_prefix AS pricePrefix
				    FROM ".DBPREFIX."module_shop_products_attributes_name AS name,
						 ".DBPREFIX."module_shop_products_attributes_value AS value
				   WHERE value.name_id = name.id
				   ORDER BY nameTxt, valueTxt ASC";

		if ($objResult = $objDatabase->Execute($query)) {
			while (!$objResult->EOF) {
				if (!isset($this->arrAttributes[$objResult->fields['nameId']]['name'])) {
					$this->arrAttributes[$objResult->fields['nameId']]['name'] = $objResult->fields['nameTxt'];
				}
				$this->arrAttributes[$objResult->fields['nameId']]['values'][$objResult->fields['valueId']] = array(
																							'id' 			=> $objResult->fields['valueId'],
																							'value' 		=> $objResult->fields['valueTxt'],
																							'price'			=> $objResult->fields['price'],
																							'price_prefix'	=> $objResult->fields['pricePrefix'],
																							'selected'		=> false
																							);
				$this->highestIndex = $objResult->fields['valueId'] > $this->highestIndex ? $objResult->fields['valueId'] : $this->highestIndex;
				$objResult->MoveNext();
			}
		}
	}


	/**
	* Store new attribute option
	*
	* Store a new attribute option
	*
	* @access	private
	* @return	string	$statusMessage	Status message
	*/
	function _storeNewAttributeOption()
	{
		global $objDatabase;

		$statusMessage = "";
		$arrAttributeList = array();
		$arrAttributeValue = array();
		$arrAttributePrice = array();
		$arrAttributePricePrefix = array();


		if (empty($_POST['optionName'][0])) {
			return "Sie müssen einen Namen für die Option setzen\n";
		} elseif (!is_array($_POST['attributeValueList'][0])) {
			return "Sie müssen mindestens einen Wert für die Option definieren\n";
		}

		$arrAttributesDb = $this->arrAttributes;
		$arrAttributeList = $_POST['attributeValueList'];
		$arrAttributeValue = $_POST['attributeValue'];
		$arrAttributePrice = $_POST['attributePrice'];
		$arrAttributePricePrefix = $_POST['attributePricePrefix'];

		$query = "INSERT INTO ".DBPREFIX."module_shop_products_attributes_name (name) VALUES ('".addslashes($_POST['optionName'][0])."')";

		if ($objResult = $objDatabase->Execute($query)) {
			$nameId = $objResult->Insert_Id();

			foreach ($arrAttributeList[0] as $id) {
				// insert new attribute value
				$query = "INSERT INTO ".DBPREFIX."module_shop_products_attributes_value (name_id, value, price, price_prefix) VALUES ($nameId, '".addslashes($arrAttributeValue[$id])."', '".floatval($arrAttributePrice[$id])."', '".addslashes($arrAttributePricePrefix[$id])."')";
				$objDatabase->Execute($query);
			}
		}

		$objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop_products_attributes_value");
		$objDatabse->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop_products_attributes_name");

		return $statusMessage;
	}


	/**
	* Update attribute options
	*
	* Update the attribute option/value list
	*
	* @access	private
	* @return	string	$statusMessage	Status message
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
				if ($name != $arrAttributesDb[$id]['name']) {
					$query = "UPDATE ".DBPREFIX."module_shop_products_attributes_name Set name='".addslashes($name)."' WHERE id=".intval($id);
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
					if ($arrAttributePrice[$id] != $arrAttributesDb[$attributeId]['values'][$id]['price']){
						$updateString .= " price = '".floatval($arrAttributePrice[$id])."', ";
					}
					if ($arrAttributePricePrefix[$id] != $arrAttributesDb[$attributeId]['values'][$id]['price_prefix']) {
						$updateString .= " price_prefix = '".addslashes($arrAttributePricePrefix[$id])."', ";
					}
					if (strlen($updateString)>0) {
						$query = "UPDATE ".DBPREFIX."module_shop_products_attributes_value Set ".substr($updateString,0,strlen($updateString)-2)." WHERE id=".$id;
						$objDatabase->Execute($query);
					}
				} else {
					// insert new attribute value
					$query = "INSERT INTO ".DBPREFIX."module_shop_products_attributes_value (name_id, value, price, price_prefix) VALUES (".intval($attributeId).", '".addslashes($arrAttributeValue[$id])."', '".floatval($arrAttributePrice[$id])."', '".addslashes($arrAttributePricePrefix[$id])."')";
					$objDatabase->Execute($query);
				}
				unset($arrAttributesDb[$attributeId]['values'][$id]);
			}
		}

		foreach ($arrAttributesDb as $arrAttributes) {
			foreach ($arrAttributes['values'] as $arrValue) {
				$query = "DELETE FROM ".DBPREFIX."module_shop_products_attributes WHERE attributes_value_id=".intval($arrValue['id']);
				if ($objDatabase->Execute($query)) {
					$query = "DELETE FROM ".DBPREFIX."module_shop_products_attributes_value WHERE id=".intval($arrValue['id']);
					$objDatabase->Execute($query);
				}
			}
		}

		// delete the option if it has no options
		$arrAttributeKeys = array_keys($this->arrAttributes);
		foreach ($arrAttributeKeys as $attributeId) {
			if (!array_key_exists($attributeId,$arrAttributeList)) {
				$query = "DELETE FROM ".DBPREFIX."module_shop_products_attributes_name WHERE id=".intval($attributeId);
				$objDatabase->Execute($query);
			}
		}

		$objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop_products_attributes_value");
		$objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop_products_attributes_name");
		$objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop_products_attributes");

		return $statusMessage;
	}


	/**
	* Delete attribute option
	*
	* Delete the selected attribute option(s)
	*
	* @access	private
	* @param	integer	$optionId	Id of the attribute option
	* @return	string	Status message
	*/
	function _deleteAttributeOption($optionId)
	{
		global $objDatabase;

		if (!is_array($optionId)) {
			$arrOptionIds = array($optionId);
		} else {
			$arrOptionIds = $optionId;
		}

		foreach ($arrOptionIds as $optionId) {
			$query = "DELETE FROM ".DBPREFIX."module_shop_products_attributes WHERE attributes_name_id=".intval($optionId);
			if ($objDatabse->Execute($query)) {
				$query = "DELETE FROM ".DBPREFIX."module_shop_products_attributes_value WHERE name_id=".intval($optionId);
				if ($objDatabase->Execue($query)) {
					$query = "DELETE FROM ".DBPREFIX."module_shop_products_attributes_name WHERE id=".intval($optionId);
					$objDatabase->query($query);
				}
			}
		}
		return "Option(s) deleted succesfull";
	}


	/**
	* Get attribute inputboxes
	*
	* Generate a list of the inputboxes with the values of an attribute option
	*
	* @access	private
	* @param	integer	$attributeId	Id of the attribute option
	* @param	string	$name	Name of the inputboxes
	* @param	string	$content	Attribute value type
	* @param	integer	$maxlength	Maxlength of the inputboxes
	* @param	string	$style	CSS-Style declaration for the inputboxes
	* @return	string	$inputBoxes	List with the generated inputboxes
	*/
	function _getAttributeInputBoxes($attributeId, $name, $content, $maxlength, $style = '')
	{
		$inputBoxes = "";
		$select = true;

		foreach ($this->arrAttributes[$attributeId]['values'] as $id => $arrValue) {
			$inputBoxes .= "<input type=\"text\" name=\"".$name."[$id]\" id=\"".$name."[$id]\" value=\"$arrValue[$content]\" maxlength=\"$maxlength\" style=\"display:".($select == true ? "inline" : "none").";$style\" onchange=\"updateAttributeList($attributeId, $id)\" />";
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
	* @access	private
	* @param	integer	$attributeId	Id of the attribute option
	* @param	string	$name	Name of the menus
	* @param	string	$pricePrefix	Price prefix of the option value
	* @return	string	$menu	Contains the price prefix menu of the given attribute option
	*/
	function _getAttributePricePrefixMenu($attributeId, $name, $pricePrefix)
	{
		$select = true;
		$menu = "";

		foreach ($this->arrAttributes[$attributeId]['values'] as $id => $arrValue) {
			$menu .= "<select style=\"width:50px;display:".($select == true ? "inline" : "none").";\" name=\"".$name."[$id]\" id=\"".$name."[$id]\" size=\"1\">\n";
			$menu .= "<option value=\"+\" ".($pricePrefix != "-" ? "selected=\"selected\"" : "").">+</option>\n";
			$menu .= "<option value=\"-\" ".($pricePrefix == "-" ? "selected=\"selected\"" : "").">-</option>\n";
			$menu .= "</select>\n";
			if ($select) {
				$select = false;
			}
		}
		return $menu;
	}


	/**
	* Get attribute JS vars
	*
	* Generate a javascript variables list of the attributes
	*
	* @access	private
	* @return	string	$jsVars	Javascript variables list
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
	* @access	private
	* @param	integer	$attributeId	Id of the attribute option
	* @param	string	$name	Name of the menu
	* @param	array	$arrValues	Value ids of the attribute option
	* @param	integer	$selectedId	Id of the selected value
	* @param	string	$onchange	Javascript onchange event of the menu
	* @param	string	$style	CSS-declaration of the menu
	* @return	string	$menu	Contains the value menus
	*/
	function _getAttributeValueMenu($attributeId, $name, $arrValues, $selectedId, $onchange, $style)
	{
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
		$menu .= "<br /><a href=\"javascript:{}\" id=\"attributeValueMenuLink[$attributeId]\" style=\"display:none;\" onclick=\"removeSelectedValues($attributeId)\" title=\"Ausgewählten Wert entfernen\" alt=\"Ausgewählten Wert entfernen\">Ausgewählten Wert entfernen</a>";
		return $menu;
	}

}
?>