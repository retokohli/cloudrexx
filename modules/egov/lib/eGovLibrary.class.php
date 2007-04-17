<?
/**
 * eGovLibrary
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Astalavista Development Team <thun@astalvista.ch>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_egov
 * @todo        Edit PHP DocBlocks!
 */

/**
 * eGovLibrary
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Astalavista Development Team <thun@astalvista.ch>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_egov
 */
class eGovLibrary {

	var $arrForms;
	var $arrCheckTypes;

	function GetProduktName($ProductID){
		global $objDatabase, $_ARRAYLANG;
		$query_GPN = "SELECT product_name
		          FROM ".DBPREFIX."module_egov_products
		          WHERE product_id=".$ProductID."";
		$objResult = $objDatabase->Execute($query_GPN);
		if ($objResult->RecordCount() == 1) {
			return $objResult->fields['product_name'];
		}else{
			return "";
		}
	}

	function GetProduktValue($FieldName="", $ProductID){
		global $objDatabase, $_ARRAYLANG;
		$query_GPN = "SELECT ".$FieldName."
		          FROM ".DBPREFIX."module_egov_products
		          WHERE product_id=".$ProductID."";
		$objResult = $objDatabase->Execute($query_GPN);
		if ($objResult->RecordCount() == 1) {
			return $objResult->fields[$FieldName];
		}else{
			return "";
		}
	}

	function GetEmailAdress($orderID){
		global $objDatabase;
		$ReturnValue = '';
		$query = "SELECT order_values
		          FROM ".DBPREFIX."module_egov_orders
		          WHERE order_id=".$orderID."";
		$objResult = $objDatabase->Execute($query);
		if ($objResult->RecordCount() == 1) {
			$ValuesArray = $objResult->fields["order_values"];
		}
		$ValuesArray = split(";;", $ValuesArray);
		if(is_array($ValuesArray)){
			for($y=0; $y<count($ValuesArray); $y++) {
				list ($ArrayName, $ArrayValue) = split('::', $ValuesArray[$y]);
				if($this->isEmail($ArrayValue)>0){
					$ReturnValue = $ArrayValue;
				}
			}
		}
		return $ReturnValue;
	}

	function isEmail($Text){
		$ismail = preg_match("!^\w[\w|\.|\-]+@\w[\w|\.|\-]+\.[a-zA-Z]{2,4}$!",$Text);
		return $ismail;
	}

	function ParseFormValues($Field="", $Values=""){
		$ValuesArray = split(";;", $Values);
		for($y=0; $y<count($ValuesArray); $y++) {
			if(!empty($ValuesArray[$y])){
				list ($ArrayName, $ArrayValue) = split('::', $ValuesArray[$y]);
				$FormArray[$ArrayName] = $ArrayValue;
			}
		}
		if(!empty($ValuesArray[$y])){
			return $FormArray[$Field];
		}
	}

	function MaskState($State){
		global $_ARRAYLANG;
		switch($State){
    		case 0:
    			return $_ARRAYLANG['TXT_STATE_NEW'];
    		break;
    		case 1:
    			return $_ARRAYLANG['TXT_STATE_OK'];
    		break;
    		case 2:
    			return $_ARRAYLANG['TXT_STATE_DELETED'];
    		break;
    		default:
                return 'unknown';
    	}
	}

	function GetSettings($FilenName=''){
		global $objDatabase;
		$query_GPN = "SELECT ".$FilenName."
		          FROM ".DBPREFIX."module_egov_settings";
		$objResult = $objDatabase->Execute($query_GPN);
		if ($objResult->RecordCount() == 1) {
			return $objResult->fields[$FilenName];
		}else{
			return "";
		}
	}

	function getFormFields($id){
		global $objDatabase;
		$arrFields = array();

		$objFields  = $objDatabase->Execute("SELECT id, name, type,
					attributes, is_required,
					check_type, order_id
					FROM ".DBPREFIX."module_egov_product_fields
					WHERE product=".$id." ORDER BY order_id");

		if ($objFields !== false) {
			while (!$objFields->EOF) {
				$arrFields[$objFields->fields['id']] = array(
					'name'			=> $objFields->fields['name'],
					'type'			=> $objFields->fields['type'],
					'attributes'	=> $objFields->fields['attributes'],
					'is_required'	=> $objFields->fields['is_required'],
					'check_type'	=> $objFields->fields['check_type'],
					'order_id'		=> $objFields->fields['order_id']
				);
				$objFields->MoveNext();
			}
		}
		return $arrFields;
	}

	function initCheckTypes(){
		global $objDatabase;

		$this->arrCheckTypes = array(
			1	=> array(
				'regex'	=> '.*',
				'name'	=> 'TXT_EGOV_REGEX_EVERYTHING'
			),
			2	=> array(
				'regex'	=> '^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.(([0-9]{1,3})|([a-zA-Z]{2,3})|(aero|coop|info|museum|name))$',
				'name'	=> 'TXT_EGOV_REGEX_EMAIL'
			),
			3	=> array(
				'regex'	=> '^(ht|f)tp[s]?\:\/\/[A-Za-z0-9\-\:\.\?\&\=]*$',
				'name'	=> 'TXT_EGOV_REGEX_URL'
			),
			4	=> array(
				'regex'	=> '^[A-Za-zהאבüגûפסטציט\ ]*$',
				'name'	=> 'TXT_EGOV_REGEX_TEXT'
			),
			5	=> array(
				'regex'	=> '^[0-9]*$',
				'name'	=> 'TXT_EGOV_REGEX_NUMBERS'
			)
		);
	}

	function initContactForms($allLanguages = false)
	{
		global $objDatabase, $_FRONTEND_LANGID;
		$sqlWhere='';

		$this->arrForms = array();

		$objContactForms = $objDatabase->Execute("SELECT tblForm.product_id, tblForm.product_name, tblForm.product_desc,
													tblForm.product_price, tblForm.product_per_day, tblForm.product_quantity,
													tblForm.product_target_email, tblForm.product_target_url, tblForm.product_message,
													COUNT(tblData.order_id) AS number, MAX(tblData.order_date) AS last
												FROM ".DBPREFIX."module_egov_products AS tblForm
												LEFT OUTER JOIN ".DBPREFIX."module_egov_orders AS tblData ON tblForm.product_id=tblData.order_product
												".$sqlWhere."
												GROUP BY tblForm.product_id
												ORDER BY last DESC");
		if ($objContactForms !== false) {
			while (!$objContactForms->EOF) {
				$this->arrForms[$objContactForms->fields['product_id']] = array(
					'product_name'			=> $objContactForms->fields['product_name'],
					'product_desc'			=> $objContactForms->fields['product_desc'],
					'product_price'			=> intval($objContactForms->fields['product_price']),
					'product_per_day'		=> $objContactForms->fields['product_per_day'],
					'product_quantity'		=> intval($objContactForms->fields['product_quantity']),
					'product_target_email'	=> $objContactForms->fields['product_target_email'],
					'product_target_url'	=> $objContactForms->fields['product_target_url'],
					'product_message'		=> $objContactForms->fields['product_message']
				);

				$objContactForms->MoveNext();
			}
		}
	}

	function _QuantityDropdown($id){
		$dropdownSource = '<select name="contactFormField_Quantity" id="contactFormField_Quantity">';
		$dropdownSource .= '</select>';
		return $dropdownSource;
	}

	function _GetOrdersQuantityArray($id, $datum=''){
		global $objDatabase;
		$JSquantityArray = '';

		if($datum==''){
			$datum = date("Y").'-'.date("m");
		}else{
			$dat1 = substr($datum, 0, 4);
			$dat2 = substr($datum, 4, 2);
			$datum = $dat1.'-'.$dat2;
		}
		for($x=1; $x<=31; $x++){
			$daydate = $x;
			if(strlen($daydate)<2){
				$daydate = '0'.$daydate;
			}
			$datumToSend = $datum.'-'.$daydate;
			$JSquantityArray .= 'DayArray['.$x.'] = '.$this->_GetOrderedQuantity($id, $datumToSend).';'.chr(13);
		}

		return $JSquantityArray;
	}

	function _GetOrderedQuantity($id, $datum){
		global $objDatabase;

		list($year, $month, $day) = split('[-]', $datum);
		$query = "SELECT count(*) as anzahl FROM ".DBPREFIX."module_egov_product_calendar WHERE calendar_day=".$day." and calendar_month=".$month." and calendar_year=".$year." and calendar_act=1 and calendar_product=".$id."";
		$objResult = $objDatabase->Execute($query);

		return $objResult->fields["anzahl"];
	}

	function getSourceCode($id, $preview = false, $show = false)
	{
		global $objDatabase, $_ARRAYLANG;

		$arrFields = $this->getFormFields($id);

		if($this->GetProduktValue("product_per_day", $id)=="yes"){

			$last_y = date("Y")-1;
			$query_ra = "SELECT calendar_product, calendar_order, calendar_day, calendar_month, calendar_year
			       FROM ".DBPREFIX."module_egov_product_calendar
			       WHERE calendar_product=".$id." and calendar_act=1 and calendar_year>".$last_y;
			$objResult_ra = $objDatabase->Execute($query_ra);
			if($objResult_ra !== false){
				while (!$objResult_ra->EOF){
					$ArrayRD[$objResult_ra->fields['calendar_year']][$objResult_ra->fields['calendar_month']][$objResult_ra->fields['calendar_day']]++;
					$objResult_ra->MoveNext();
				}
			}
			require_once dirname(__FILE__).'/cal/calendrier.php';
			$AnzahlTxT = $_ARRAYLANG['TXT_EGOV_QUANTITY'];
			$AnzahlDropdown = $this->_QuantityDropdown($id);
			$QuantArray = $this->_GetOrdersQuantityArray($id, $_REQUEST["date"]);

			$Datum4JS = $_REQUEST["date"];
			if($Datum4JS==''){
				$Datum4JS = date('Y').date('m').date('d');
			}
			$dat1 = substr($Datum4JS, 0, 4);
			$dat2 = substr($Datum4JS, 4, 2);
			$dat3 = substr($Datum4JS, 6, 2);
			if(substr($dat3, 0, 1)=="0"){
				$dat3 = substr($dat3, 1, 1);
			}
			$DatumJS = $dat3.'.'.$dat2.'.'.$dat1;


			$CalenderSource = calendar($DatumJS, $QuantArray, $AnzahlDropdown, $AnzahlTxT, $this->GetSettings("set_calendar_date_desc"), $this->GetSettings("set_calendar_date_label"), $ArrayRD, $this->GetProduktValue("product_quantity", $id), '', $this->GetSettings("set_calendar_background"), $this->GetSettings("set_calendar_legende_1"), $this->GetSettings("set_calendar_legende_2"), $this->GetSettings("set_calendar_legende_3"), $this->GetSettings("set_calendar_color_1"), $this->GetSettings("set_calendar_color_2"), $this->GetSettings("set_calendar_color_3"), $this->GetSettings("set_calendar_border"));

		}else{
			$CalenderSource = '';
		}

		$FormActionTarget 	= ($preview ? '../' : '')."index.php?section=egov&amp;id=".$id;
		$PayPalPaymant		= $this->GetProduktValue('product_paypal', $id);

		$sourcecode = $this->_getJsSourceCode($id, $arrFields, $preview, $show);
		$sourcecode .= $this->arrForms[$id]['text'] . "<br /><br />\n";
		$sourcecode .= "<div id=\"contactFormError\" style=\"color: red; display: none;\">";
		$sourcecode .= $_ARRAYLANG['TXT_NEW_ENTRY_ERORR'];
		$sourcecode .= "</div>\n<br />";
		$sourcecode .= "<!-- BEGIN contact_form -->\n";
		$sourcecode .= "<form action=\"".$FormActionTarget."\" ";
		$sourcecode .= "method=\"post\" enctype=\"multipart/form-data\" onsubmit=\"return checkAllFields();\" id=\"contactForm\">\n";
		$sourcecode .= "<input type=\"hidden\" name=\"send\" value=\"exe\"  />";
		$sourcecode .= "<input type=\"hidden\" name=\"paypal\" value=\"".$PayPalPaymant."\"  />";
		$sourcecode .= $CalenderSource."";
		$sourcecode .= "<table border=\"0\">\n";

		foreach ($arrFields as $fieldId => $arrField) {
			if ($arrField['is_required']) {
				$required = "<span style=\"color: red;\">*</span>";
			} else {
				$required = "";
			}

			$sourcecode .= "<tr>\n";
			$sourcecode .= "<td style=\"width:100px;\">".(($arrField['type'] != 'hidden' && $arrField['type'] != 'label') ? $arrField['name'] : '&nbsp;')." ".$required."</td>\n";
			$sourcecode .= "<td>";

			switch ($arrField['type']) {
				case 'text':
					$sourcecode .= "<input style=\"width:300px;\" type=\"text\" name=\"contactFormField_".$fieldId."\" value=\"".$arrField['attributes']."\" />\n";
					break;

				case 'label':
					$sourcecode .= $arrField['attributes']."\n";
					break;

				case 'checkbox':
					$sourcecode .= "<input type=\"checkbox\" name=\"contactFormField_".$fieldId."\" value=\"1\"".($arrField['attributes'] == '1' ? ' checked="checked"' : '')." />\n";
					break;

				case 'checkboxGroup':
					$options = explode(',', $arrField['attributes']);
					$nr = 0;
					foreach ($options as $option) {
						$sourcecode .= "<input type=\"checkbox\" name=\"contactFormField_".$fieldId."[]\" id=\"contactFormField_".$nr."_".$fieldId."\" value=\"".$option."\" /><label for=\"contactFormField_".$nr."_".$fieldId."\">".$option."</label>\n";
						$nr++;
					}
					break;

				case 'file':
					$sourcecode .= "<input style=\"width:300px;\" type=\"file\" name=\"contactFormField_".$fieldId."\" />\n";
					break;

				case 'hidden':
					$sourcecode .= "<input type=\"hidden\" name=\"contactFormField_".$fieldId."\" value=\"".$arrField['attributes']."\" />\n";
					break;

				case 'password':
					$sourcecode .= "<input style=\"width:300px;\" type=\"password\" name=\"contactFormField_".$fieldId."\" value=\"\" />\n";
					break;

				case 'radio':
					$options = explode(',', $arrField['attributes']);
					$nr = 0;
					foreach ($options as $option) {
						$sourcecode .= "<input type=\"radio\" name=\"contactFormField_".$fieldId."\" id=\"contactFormField_".$nr."_".$fieldId."\" value=\"".$option."\" /><label for=\"contactFormField_".$nr."_".$fieldId."\">".$option."</label>\n";
						$nr++;
					}
					break;

				case 'select':
					$options = explode(',', $arrField['attributes']);
					$nr = 0;
					$sourcecode .= "<select style=\"width:300px;\" name=\"contactFormField_".$fieldId."\">\n";
					foreach ($options as $option) {
						$sourcecode .= "<option>".$option."</option>\n";
					}
					$sourcecode .= "</select>\n";
					break;

				case 'textarea':
					$sourcecode .= "<textarea style=\"width:300px; height:100px;\" name=\"contactFormField_".$fieldId."\"></textarea>\n";
					break;
			}

			$sourcecode .= "</td>\n";
			$sourcecode .= "</tr>\n";
		}
		$sourcecode .= "<tr>\n";
		$sourcecode .= "<td>&nbsp;</td>\n";
		$sourcecode .= "<td>\n";
		if(count($arrFields)>0){
			$sourcecode .= "<input type=\"reset\" value=\"".$_ARRAYLANG['TXT_EGOV_DELETE']."\" /> <input type=\"submit\" name=\"submitContactForm\" value=\"".$_ARRAYLANG['TXT_EGOV_SUBMIT']."\" />\n";
		}
		$sourcecode .= "</td>\n";
		$sourcecode .= "</tr>\n";
		$sourcecode .= "</table>\n";

		$sourcecode .= "</form>";
		$sourcecode .= "<!-- END contact_form -->\n";

		return $sourcecode;
	}

	function _getJsSourceCode($id, $formFields, $preview = false, $show = false)
	{
		$code = "<script type=\"text/javascript\">\n";
		$code .= "/* <![CDATA[ */\n";

		$code .= "fields = new Array();\n";
		$this->initCheckTypes();
		foreach ($formFields as $key => $field) {
			$code .= "fields[$key] = Array(\n";
			$code .= "\t'{$field['name']}',\n";
			$code .= "\t{$field['is_required']},\n";
			if ($preview) {
				$code .= "\t'". addslashes($this->arrCheckTypes[$field['check_type']]['regex']) ."',\n";
			} elseif ($show) {
				$code .= "\t'". addslashes($this->arrCheckTypes[$field['check_type']]['regex']) ."',\n";
			} else {
				$code .= "\t'". addslashes($this->arrCheckTypes[$field['check_type']]['regex']) ."',\n";
			}
			$code .= "\t'".$field['type']."');\n";
		}
		/*
		if($this->GetProduktValue("product_per_day", $_REQUEST["id"]=="yes")){
			$code .= "fields[1000] = Array('Datum', 1, '', 'text');\n";
		}
		*/
		$code .= "var readBefore = false;\n";
		$code .= "var borderBefore = \"\";\n";

		$code .= "\nfunction checkAllFields() {\n";
		$code .= "	var isOk = true;\n";
		$code .= "	for (var field in fields) { \n";
		$code .= "		if (!readBefore) {\n";
		$code .= "			if (document.getElementsByName('contactFormField_' + field)[0]) {borderBefore = document.getElementsByName('contactFormField_' + field)[0].style.border;} else {borderBefore = '#000000';}\n";
		$code .= "			readBefore = true;\n";
		$code .= "		}\n\n";

		$code .= "		var type = fields[field][3];\n";
		$code .= "		if (type == 'text' || type == 'file' || type == 'password' || type == 'textarea') {\n";
		$code .= "			value = document.getElementsByName('contactFormField_' + field)[0].value;\n";
		$code .= "			if (value == \"\" && isRequiredNorm(fields[field][1], value)) {\n";
		$code .= "				isOk = false;\n";
		$code .= "				document.getElementsByName('contactFormField_' + field)[0].style.border = \"red 1px solid\"; \n";
		$code .= "			} else if (value != \"\" && !matchType(fields[field][2], value)) {\n";
		$code .= "				isOk = false;\n";
		$code .= "				document.getElementsByName('contactFormField_' + field)[0].style.border = \"red 1px solid\"; \n";
		$code .= "			} else {\n";
		$code .= "				document.getElementsByName('contactFormField_' + field)[0].style.border = borderBefore; \n";
		$code .= "			}\n";
		$code .= "		} else if (type == 'checkbox') {\n";
		$code .= "			if (!isRequiredCheckbox(fields[field][1], field)) {\n";
		$code .= "				isOk = false;\n";
		$code .= "			}\n";
		$code .= "		} else if (type == 'checkboxGroup') {\n";
		$code .= "			if (!isRequiredCheckBoxGroup(fields[field][1], field)) {\n";
		$code .= "				isOk = false;\n";
		$code .= "			}\n";
		$code .= "		} else if (type == 'radio') {\n";
		$code .= "			if (!isRequiredRadio(fields[field][1], field)) {\n";
		$code .= "				isOk = false;\n";
		$code .= "			}\n";
		$code .= "		}\n";
		$code .= "	}\n\n";
		$code .= "	if (!isOk) {\n";
		$code .= "		document.getElementById('contactFormError').style.display = \"block\";\n";
		$code .= "	}\n";
		$code .= "	return isOk;\n";
		$code .= "} \n\n";

		// This is for checking normal text input field if they are required.
		// If yes, it also checks if the field is set. If it is not set, it returns true.
		$code .= "function isRequiredNorm(required, value) {\n";
		$code .= "	if (required == 1) {\n";
		$code .= "		if (value == \"\") { \n";
		$code .= "			return true; \n";
		$code .= "		} \n";
		$code .= "	} \n";
		$code .= "	return false; \n";
		$code .= "} \n\n";

		// Matches the type of the value and pattern. Returns true if it matched, false if not.
		$code .= "function matchType(pattern, value) {\n";
		$code .= "	var reg = new RegExp(pattern);\n";
		$code .= "	if (value.match(reg)) {\n";
		$code .= "		return true;\n";
		$code .= "	}\n";
		$code .= "	return false;\n";
		$code .= "} \n\n";

		// Checks if a checkbox is required but not set. Returns false when finding an error.
		$code .= "function isRequiredCheckbox(required, field) {\n";
		$code .= "	if (required == 1) {\n";
		$code .= "		if (!document.getElementsByName('contactFormField_' + field)[0].checked) {\n";
		$code .= "			document.getElementsByName('contactFormField_' + field)[0].style.border = \"red 1px solid\"; \n";
		$code .= "			return false;\n";
		$code .= "		}\n";
		$code .= "	}\n";
		$code .= "	document.getElementsByName('contactFormField_' + field)[0].style.border = borderBefore; \n";
		$code .= "	return true;\n";
		$code .= "}\n\n";

		// Checks if a multile checkbox is required but not set. Returns false when finding an error.
		$code .= "function isRequiredCheckBoxGroup(required, field) {\n";
		$code .= "	if (required == true) {\n";
		$code .= "		var boxes = document.getElementsByName('contactFormField_' + field + '[]');\n";
		$code .= "		var checked = false;\n";
		$code .= "		for (var i = 0; i < boxes.length; i++) { \n";
		$code .= " 			if (boxes[i].checked) {\n";
		$code .= "				checked = true;\n";
		$code .= "			}\n";
		$code .= "		}\n";
		$code .= "		if (checked) {\n";
		$code .= "			setListBorder('contactFormField_' + field + '[]', borderBefore);\n";
		$code .= "			return true;\n";
		$code .= "		} else {\n";
		$code .= "			setListBorder('contactFormField_' + field + '[]', '1px red solid');\n";
		$code .= "			return false;\n";
		$code .= "		}\n";
		$code .= "	} else { \n";
		$code .= "		return true;\n";
		$code .= "	}\n";
		$code .= "}\n\n";

		// Checks if some radio button need to be checked. Returns false if it finds an error
		$code .= "function isRequiredRadio(required, field) {\n";
		$code .= "	if (required == 1) {\n";
		$code .= "		var buttons = document.getElementsByName('contactFormField_' + field);\n";
		$code .= "		var checked = false;\n";
		$code .= "		for (var i = 0; i < buttons.length; i++) {\n";
		$code .= "			if (buttons[i].checked) {\n";
		$code .= "				checked = true;\n";
		$code .= "			}\n";
		$code .= "		}\n";
		$code .= "		if (checked) {\n";
		$code .= "			setListBorder('contactFormField_' + field, borderBefore);\n";
		$code .= "			return true;\n";
		$code .= "		} else { \n";
		$code .= "			setListBorder('contactFormField_' + field, '1px red solid');\n";
		$code .= "			return false;\n";
		$code .= "		}\n";
		$code .= "	} else { \n";
		$code .= "		return true;\n";
		$code .="	}\n";
		$code .= "}\n\n";

		// Sets the border attribute of a group of checkboxes or radiobuttons
		$code .= "function setListBorder(field, borderColor) {\n";
		$code .= "	var boxes = document.getElementsByName(field);\n";
		$code .= "	for (var i = 0; i < boxes.length; i++) {\n";
		$code .= "		boxes[i].style.border = borderColor;\n";
		$code .= "	}\n";
		$code .= "}\n\n";

		$code .= "/* ]]> */\n";
		$code .= "</script>\n";
		return $code;
	}


}


?>
