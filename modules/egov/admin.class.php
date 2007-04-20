<?php
/**
 * E-Government
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Astalavista Development Team <thun@astalvista.ch>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_egov
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once dirname(__FILE__).'/lib/eGovLibrary.class.php';


/**
 * E-Government
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Astalavista Development Team <thun@astalvista.ch>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_egov
 */
class eGov extends eGovLibrary
{

	var $_arrFormFieldTypes;
	var $_strErrMessage = '';
	var $_strOkMessage = '';

	function eGov()
	{
    	global  $objDatabase, $_ARRAYLANG, $objTemplate, $objInit;

    	$this->_arrFormFieldTypes = array(
    		'text'			=> $_ARRAYLANG['TXT_EGOV_TEXTBOX'],
    		'label'			=> $_ARRAYLANG['TXT_EGOV_TEXT'],
			'checkbox'		=> $_ARRAYLANG['TXT_EGOV_CHECKBOX'],
			'checkboxGroup'	=> $_ARRAYLANG['TXT_EGOV_CHECKBOX_GROUP'],
			'hidden'		=> $_ARRAYLANG['TXT_EGOV_HIDDEN_FIELD'],
			'password'		=> $_ARRAYLANG['TXT_EGOV_PASSWORD_FIELD'],
			'radio'			=> $_ARRAYLANG['TXT_EGOV_RADIO_BOXES'],
			'select'		=> $_ARRAYLANG['TXT_EGOV_SELECTBOX'],
			'textarea'		=> $_ARRAYLANG['TXT_EGOV_TEXTAREA']
		);

		$this->initContactForms();
    	$this->initCheckTypes();

		$this->_objTpl = &new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/egov/template');
		$this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

    	$this->imagePath = ASCMS_MODULE_IMAGE_WEB_PATH;
    	$this->langId=$objInit->userFrontendLangId;

		$objTemplate->setVariable("CONTENT_NAVIGATION","<a href='?cmd=egov'>".$_ARRAYLANG['TXT_ORDERS']."</a>
		                                                <a href='?cmd=egov&amp;act=products'>".$_ARRAYLANG['TXT_PRODUCTS']."</a>
		                                                <a href='?cmd=egov&amp;act=settings'>".$_ARRAYLANG['TXT_SETTINGS']."</a>");
	}

	function getPage()
	{
		global $objTemplate;

    	if(!isset($_GET['act'])) {
    	    $_GET['act']='';
    	}
    	switch($_GET['act']){
    		case 'save_form':
    			$this->_save_form();
    		break;
    		case 'product_edit':
    			$this->_product_edit();
    		break;
    		case 'product_copy':
    			$this->_product_copy();
    		break;
    		case 'products':
    			$this->_products();
    		break;
    		case 'settings':
    			$this->_settings();
    		break;
    		case 'order_edit':
    			$this->_order_edit();
    		break;
    		case 'orders':
    			$this->_orders();
    		break;
    		default:
                $this->_orders();
    	}
    	$objTemplate->setVariable(array(
			'CONTENT_TITLE'				=> $this->_pageTitle,
			'CONTENT_OK_MESSAGE'		=> $this->_strOkMessage,
			'CONTENT_STATUS_MESSAGE'	=> $this->_strErrMessage,
			'ADMIN_CONTENT'				=> $this->_objTpl->get()
		));
	}

	function _product_copy(){

		global $objDatabase, $_ARRAYLANG;

		$product_id 			= $_REQUEST["id"];
		$product_autostatus 	= $this->GetProduktValue("product_autostatus", $product_id);
		$product_name 			= $this->GetProduktValue("product_name", $product_id)." (copy)";
		$product_desc 			= $this->GetProduktValue("product_desc", $product_id);
		$product_price 			= $this->GetProduktValue("product_price", $product_id);
		$product_per_day 		= $this->GetProduktValue("product_per_day", $product_id);
		$product_quantity 		= $this->GetProduktValue("product_quantity", $product_id);
		$product_target_email 	= $this->GetProduktValue("product_target_email", $product_id);
		$product_target_url 	= $this->GetProduktValue("product_target_url", $product_id);
		$product_message 		= $this->GetProduktValue("product_message", $product_id);
		$product_status 		= $this->GetProduktValue("product_status", $product_id);
		$product_electro 		= $this->GetProduktValue("product_electro", $product_id);
		$product_file 			= $this->GetProduktValue("product_file", $product_id);
		$product_sender_name 	= $this->GetProduktValue("product_sender_name", $product_id);
		$product_sender_email 	= $this->GetProduktValue("product_target_subject", $product_id);
		$product_target_subject = $this->GetProduktValue("product_sender_email", $product_id);
		$product_target_body	= $this->GetProduktValue("product_target_body", $product_id);

		$arrFields = $this->getFormFields($product_id);

		if ($objDatabase->Execute("INSERT INTO ".DBPREFIX."module_egov_products
								  (`product_name`, `product_desc`,`product_price`, `product_per_day`, `product_quantity`, `product_target_email`, `product_target_url`, `product_message`, `product_status`, `product_autostatus`, `product_electro`, `product_file`, `product_sender_name`, `product_sender_email`, `product_target_subject`, `product_target_body`)
								  VALUES
								  ('".$product_name."', '".$product_desc."', '".$product_price."', '".$product_per_day."', '".$product_quantity."', '".$product_target_email."', '".$product_target_url."', '".$product_message."', '".$product_status."', '".$product_autostatus."', '".$product_electro."', '".$product_file."', '".$product_sender_name."', '".$product_sender_email."', '".$product_target_subject."', '".$product_target_body."')") !== false) {

			$ProdId = $objDatabase->Insert_ID();
			foreach ($arrFields as $fieldId => $arrField) {
				$this->_addFormField($ProdId, $arrField['name'], $arrField['type'], $arrField['attributes'], $arrField['order_id'], $arrField['is_required'], $arrField['check_type']);
			}
		}

		$_REQUEST["id"] = $ProdId;
		$this->_products();
		$this->_strOkMessage .= $_ARRAYLANG['TXT_EGOV_PRODUCT_SUCCESSFULLY_SAVED'];

	}

	function _settings(){
		global $objDatabase, $_ARRAYLANG;

		$this->_objTpl->loadTemplateFile('module_gov_settings.html');
		$this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];

		// save settings
		// ----------------------------------------
		if(isset($_REQUEST["tpl"])){
			if($_REQUEST["tpl"]=="save"){

				$paypal_ipn = $_REQUEST["PayPal_IPN"];
				if($paypal_ipn!="1"){
					$paypal_ipn = 0;
				}

				$query = "UPDATE ".DBPREFIX."module_egov_settings
							 SET set_sender_name='".strip_tags(contrexx_addslashes($_REQUEST["senderName"]))."',
							 set_sender_email='".strip_tags(contrexx_addslashes($_REQUEST["senderEmail"]))."',
							 set_recipient_email='".strip_tags(contrexx_addslashes($_REQUEST["recipientEmail"]))."',
							 set_state_email='".strip_tags(contrexx_addslashes($_REQUEST["stateEmail"]))."',
							 set_calendar_color_1='".strip_tags(contrexx_addslashes($_REQUEST["calenderColor1"]))."',
							 set_calendar_color_2='".strip_tags(contrexx_addslashes($_REQUEST["calenderColor2"]))."',
							 set_calendar_color_3='".strip_tags(contrexx_addslashes($_REQUEST["calenderColor3"]))."',
							 set_calendar_legende_1='".strip_tags(contrexx_addslashes($_REQUEST["calenderLegende1"]))."',
							 set_calendar_legende_2='".strip_tags(contrexx_addslashes($_REQUEST["calenderLegende2"]))."',
							 set_calendar_legende_3='".strip_tags(contrexx_addslashes($_REQUEST["calenderLegende3"]))."',
							 set_calendar_background='".strip_tags(contrexx_addslashes($_REQUEST["calenderBackground"]))."',
							 set_calendar_border='".strip_tags(contrexx_addslashes($_REQUEST["calenderBorder"]))."',
							 set_calendar_date_label='".strip_tags(contrexx_addslashes($_REQUEST["calenderDateLabel"]))."',
							 set_calendar_date_desc='".strip_tags(contrexx_addslashes($_REQUEST["calenderDateDesc"]))."',
							 set_state_subject='".strip_tags(contrexx_addslashes($_REQUEST["stateSubject"]))."',
							 set_orderentry_subject='".strip_tags(contrexx_addslashes($_REQUEST["orderentrySubject"]))."',
							 set_orderentry_email='".strip_tags(contrexx_addslashes($_REQUEST["orderentryEmail"]))."',
							 set_orderentry_name='".strip_tags(contrexx_addslashes($_REQUEST["orderentrysenderName"]))."',
							 set_orderentry_sender='".strip_tags(contrexx_addslashes($_REQUEST["orderentrysenderEmail"]))."',
							 set_orderentry_recipient='".strip_tags(contrexx_addslashes($_REQUEST["orderentryrecipientEmail"]))."',
							 set_paypal_email='".strip_tags(contrexx_addslashes($_REQUEST["PayPal_mail"]))."',
							 set_paypal_currency='".strip_tags(contrexx_addslashes($_REQUEST["PayPalcurrency"]))."',
							 set_paypal_ipn='".strip_tags(contrexx_addslashes($paypal_ipn))."'
							 WHERE set_id=1";
				if($objDatabase->Execute($query)){
					$this->_strOkMessage = $_ARRAYLANG['TXT_EGOV_SETTINGS_UPDATED_SUCCESSFUL'];
				}
			}
		}

		$currency 			= $this->GetSettings("set_paypal_currency", $product_id);
		$selected_CHF	 	= ($currency=="CHF") ? 'selected' : '';
		$selected_EUR	 	= ($currency=="EUR") ? 'selected' : '';
		$selected_USD	 	= ($currency=="USD") ? 'selected' : '';
		$selected_GBP	 	= ($currency=="GBP") ? 'selected' : '';
		$selected_JPY	 	= ($currency=="JPY") ? 'selected' : '';

		$ipnchecked 		= ($this->GetSettings("set_paypal_ipn")==1) ? 'checked' : '';

		// ----------------------------------------
		$this->_objTpl->setVariable(array(
    		'TXT_EGOV_SETTINGS_GENERALLY'			 =>	$_ARRAYLANG['TXT_EGOV_SETTINGS_GENERALLY'],
    		'TXT_EGOV_SETTINGS_LAYOUT'			 	 =>	$_ARRAYLANG['TXT_EGOV_SETTINGS_LAYOUT'],
    		'TXT_EGOV_SETTINGS_GENERALLY'			 =>	$_ARRAYLANG['TXT_EGOV_SETTINGS_GENERALLY'],
    		'TXT_EGOV_SENDER_NAME'			 	 	 =>	$_ARRAYLANG['TXT_EGOV_SENDER_NAME'],
    		'TXT_EGOV_SENDER_EMAIL'			 		 =>	$_ARRAYLANG['TXT_EGOV_SENDER_EMAIL'],
    		'TXT_EGOV_STANDARD_RECIPIENT'			 =>	$_ARRAYLANG['TXT_EGOV_STANDARD_RECIPIENT'],
    		'TXT_EGOV_STANDARD_STATUS_CHANGE'		 =>	$_ARRAYLANG['TXT_EGOV_STANDARD_STATUS_CHANGE'],
    		'TXT_SAVE'								 =>	$_ARRAYLANG['TXT_SAVE'],
    		'TXT_EGOV_CALENDAR_COLOR_FREE'			 =>	$_ARRAYLANG['TXT_EGOV_CALENDAR_COLOR_FREE'],
    		'TXT_EGOV_CALENDAR_COLOR_PART'			 =>	$_ARRAYLANG['TXT_EGOV_CALENDAR_COLOR_PART'],
    		'TXT_EGOV_CALENDAR_COLOR_OCCUPIED'		=>	$_ARRAYLANG['TXT_EGOV_CALENDAR_COLOR_OCCUPIED'],
    		'TXT_EGOV_PRODUCTS_CHOICE_MENU'			=>	$_ARRAYLANG['TXT_EGOV_PRODUCTS_CHOICE_MENU'],
    		'TXT_EGOV_STTINGS_CALENDAR_LEGENDE_1'	=>	$_ARRAYLANG['TXT_EGOV_STTINGS_CALENDAR_LEGENDE_1'],
    		'TXT_EGOV_STTINGS_CALENDAR_LEGENDE_2'	=>	$_ARRAYLANG['TXT_EGOV_STTINGS_CALENDAR_LEGENDE_2'],
    		'TXT_EGOV_STTINGS_CALENDAR_LEGENDE_3'	=>	$_ARRAYLANG['TXT_EGOV_STTINGS_CALENDAR_LEGENDE_3'],
    		'TXT_EGOV_STTINGS_CALENDAR_BACKGROUND'	=>	$_ARRAYLANG['TXT_EGOV_STTINGS_CALENDAR_BACKGROUND'],
    		'TXT_EGOV_STTINGS_CALENDAR_BORDERCOLOR'	=>	$_ARRAYLANG['TXT_EGOV_STTINGS_CALENDAR_BORDERCOLOR'],
    		'TXT_EGOV_STTINGS_DATE_LABEL'			=>	$_ARRAYLANG['TXT_EGOV_STTINGS_DATE_LABEL'],
    		'TXT_EGOV_STTINGS_DATE_ENTRY_DESC'		=>	$_ARRAYLANG['TXT_EGOV_STTINGS_DATE_ENTRY_DESC'],
    		'SENDER_NAME'							 => $this->GetSettings("set_sender_name"),
    		'SENDER_EMAIL'							 => $this->GetSettings("set_sender_email"),
    		'STANDARD_RECIPIENT'					 => $this->GetSettings("set_recipient_email"),
    		'STANDARD_STATE_EMAIL'					 => $this->GetSettings("set_state_email"),
    		'CALENDER_COLOR_1'						 => $this->GetSettings("set_calendar_color_1"),
    		'CALENDER_COLOR_2'						 => $this->GetSettings("set_calendar_color_2"),
    		'CALENDER_COLOR_3'						 => $this->GetSettings("set_calendar_color_3"),
    		'CALENDER_LEGENDE_1'					 => $this->GetSettings("set_calendar_legende_1"),
    		'CALENDER_LEGENDE_2'					 => $this->GetSettings("set_calendar_legende_2"),
    		'CALENDER_LEGENDE_3'					 => $this->GetSettings("set_calendar_legende_3"),
    		'CALENDER_BACKGROUND'					 => $this->GetSettings("set_calendar_background"),
	    	'CALENDER_BORDER'						 => $this->GetSettings("set_calendar_border"),
	    	'CALENDER_DATUM_LABEL'					 => $this->GetSettings("set_calendar_date_label"),
	    	'CALENDER_DATUM_DESC'					 => $this->GetSettings("set_calendar_date_desc"),
	    	'TXT_EGOV_SUBJECT'						 => $_ARRAYLANG['TXT_EGOV_SUBJECT'],
	    	'STATE_SUBJECT'							 => $this->GetSettings("set_state_subject"),
	    	'EGOV_TXT_STATE_CHANGE'					 => $_ARRAYLANG['EGOV_TXT_STATE_CHANGE'],
	    	'EGOV_TXT_ORDER_ENTRY'					 => $_ARRAYLANG['EGOV_TXT_ORDER_ENTRY'],
	    	'ORDER_ENTRY_SUBJECT'					 => $this->GetSettings("set_orderentry_subject"),
	    	'ORDER_ENTRY_EMAIL'						 => $this->GetSettings("set_orderentry_email"),
	    	'EGOV_TXT_EMAIL_TEMPLATE_FOR_CUSTOMER'	 => $_ARRAYLANG['EGOV_TXT_EMAIL_TEMPLATE_FOR_CUSTOMER'],
	    	'EGOV_TXT_EMAIL_TEMPLATE_FOR_Admin'		 => $_ARRAYLANG['EGOV_TXT_EMAIL_TEMPLATE_FOR_Admin'],
	    	'EGOV_TXT_EMAIL_TEMPLATE'				 => $_ARRAYLANG['EGOV_TXT_EMAIL_TEMPLATE'],
	    	'TXT_EGOV_PLACEHOLDERS'					 => $_ARRAYLANG['TXT_EGOV_PLACEHOLDERS'],
			'TXT_EGOV_PRODUCTNAME_PLACEHOLDER'		 => $_ARRAYLANG['TXT_EGOV_PRODUCTNAME_PLACEHOLDER'],
			'TXT_EGOV_ORDERDETAILS_PLACEHOLDER'		 => $_ARRAYLANG['TXT_EGOV_ORDERDETAILS_PLACEHOLDER'],
			'ORDER_ENTRY_SENDER_NAME'				 => $this->GetSettings("set_orderentry_name"),
			'ORDER_ENTRY_SENDER_EMAIL'				 => $this->GetSettings("set_orderentry_sender"),
			'ORDER_ENTRY_RECIPIENT'					 => $this->GetSettings("set_orderentry_recipient"),
			'TXT_EGOV_PAYMENTS'		 				=> $_ARRAYLANG['TXT_EGOV_PAYMENTS'],
			'TXT_EGOV_SANDBOX_EMAIL'				=> $_ARRAYLANG['TXT_EGOV_SANDBOX_EMAIL'],
			'TXT_EGOV_PAYPAL_CURRENCY'		 		=> $_ARRAYLANG['TXT_EGOV_PAYPAL_CURRENCY'],
			'PAYPAL_EMAIL'					 		=> $this->GetSettings("set_paypal_email"),
			'SELECTED_CHF'					=> $selected_CHF,
			'SELECTED_EUR'					=> $selected_EUR,
			'SELECTED_USD'					=> $selected_USD,
			'SELECTED_GBP'					=> $selected_GBP,
			'SELECTED_JPY'					=> $selected_JPY,
			'IPN_CHECKED'					=> $ipnchecked,
			'TXT_EGOV_PAYPAL_IPN'		 		=> $_ARRAYLANG['TXT_EGOV_PAYPAL_IPN'],
	   	));

	}

	function _save_form(){
		$this->_saveForm();
	}

	function _product_edit(){
		global $objDatabase, $_ARRAYLANG;


		$this->_objTpl->loadTemplateFile('module_gov_product_edit.html');
		if(intval($_REQUEST["id"])==0){
			$this->_pageTitle = $_ARRAYLANG['TXT_EGOV_ADD_NEW_PRODUCT'];
		}else{
			$this->_pageTitle =  $_ARRAYLANG['TXT_EGOV_EDIT_PRODUCT'];
		}

		$product_id = intval($_REQUEST["id"]);

		if ($product_id!=0) {
			$jsSubmitFunction = "updateContentSite()";
		} else {
			$jsSubmitFunction = "createContentSite()";
		}

		$TargetEmail = $this->GetProduktValue("product_target_email", $product_id);
		if($TargetEmail==""){
			$TargetEmail = $this->GetSettings("set_recipient_email");
		}

		$StatusChecked = "checked";
		if ($product_id!=0) {
			if($this->GetProduktValue("product_status", $product_id)!=1){
				$StatusChecked = '';
			}
		}

		$Automail = 0;
		$AutoJaChecked = '';
		$AutoNeinChecked = 'checked';
		if($this->GetProduktValue("product_autostatus", $product_id)==1){
			$Automail = 1;
			$AutoJaChecked = 'checked';
			$AutoNeinChecked = '';
		}

		$electro_checked = '';
		if($this->GetProduktValue("product_electro", $product_id)==1){
			$electro_checked = 'checked';
		}

		$ProductSenderName = $this->GetProduktValue("product_sender_name", $product_id);
		if($ProductSenderName==""){
			$ProductSenderName = $this->GetSettings("set_sender_name");
		}
		$ProductSenderEmail = $this->GetProduktValue("product_sender_email", $product_id);
		if($ProductSenderEmail==""){
			$ProductSenderEmail = $this->GetSettings("set_sender_email");
		}

		$ProductTargetSubject = $this->GetProduktValue("product_target_subject", $product_id);
		if($ProductTargetSubject==''){
			$ProductTargetSubject = $this->GetSettings("set_state_subject");
		}

		$ProductTargetBody = $this->GetProduktValue("product_target_body", $product_id);
		if($ProductTargetBody==''){
			$ProductTargetBody = $this->GetSettings("set_state_email");
		}

		$PayPal_yes	 = ($this->GetProduktValue("product_paypal", $product_id)==1) ? 'checked' : '';
		$PayPal_no	 = ($PayPal_yes=='') ? 'checked' : '';

		$currency 			= $this->GetProduktValue("product_paypal_currency", $product_id);
		$paypalEmail		= $this->GetProduktValue("product_paypal_sandbox", $product_id);

		if($paypalEmail==""){
			$paypalEmail = $this->GetSettings("set_paypal_email");;
		}
		if($currency==""){
			$currency = $this->GetSettings("set_paypal_currency");;
		}
		$selected_CHF	 	= ($currency=="CHF") ? 'selected' : '';
		$selected_EUR	 	= ($currency=="EUR") ? 'selected' : '';
		$selected_USD	 	= ($currency=="USD") ? 'selected' : '';
		$selected_GBP	 	= ($currency=="GBP") ? 'selected' : '';
		$selected_JPY	 	= ($currency=="JPY") ? 'selected' : '';


		$this->_objTpl->setVariable(array(
    		'TXT_ACTION_TITLE'			 	=>	$this->_pageTitle,
    		'TXT_PRODUCT_NAME'			 	=>	$_ARRAYLANG['TXT_PRODUCT_NAME'],
    		'TXT_EGOV_RECEIVER_ADDRESSES'	=>	$_ARRAYLANG['TXT_EGOV_RECEIVER_ADDRESSES'],
    		'TXT_EGOV_LIMITED_PER_DAY'		=>	$_ARRAYLANG['TXT_EGOV_LIMITED_PER_DAY'],
    		'TXT_YES'						=>	$_ARRAYLANG['TXT_YES'],
    		'TXT_NO'						=>	$_ARRAYLANG['TXT_NO'],
    		'TXT_EGOV_RESERVED_DAYS'		=>	$_ARRAYLANG['TXT_EGOV_RESERVED_DAYS'],
    		'TXT_EGOV_PRODUCT_QUANTITY'		=>  $_ARRAYLANG['TXT_EGOV_PRODUCT_QUANTITY'],
    		'TXT_EGOV_TARGET_URL'			=>  $_ARRAYLANG['TXT_EGOV_TARGET_URL'],
    		'TXT_EGOV_TARGET_MESSAGE'		=>  $_ARRAYLANG['TXT_EGOV_TARGET_MESSAGE'],
    		'TXT_EGOV_PRODUCT_PRICE'		=>  $_ARRAYLANG['TXT_EGOV_PRODUCT_PRICE'],
    		'TXT_EGOV_PRODUCT_DESC'			=>  $_ARRAYLANG['TXT_EGOV_PRODUCT_DESC'],
    		'TXT_EGOV_FORM_FIELDS'			=>  $_ARRAYLANG['TXT_EGOV_FORM_FIELDS'],
    		'TXT_EGOV_ADD_OTHER_FIELD'		=>  $_ARRAYLANG['TXT_EGOV_ADD_OTHER_FIELD'],
    		'TXT_EGOV_FIELD_NAME'			=>  $_ARRAYLANG['TXT_EGOV_FIELD_NAME'],
    		'TXT_EGOV_TYPE'					=>  $_ARRAYLANG['TXT_EGOV_TYPE'],
    		'TXT_EGOV_VALUE_S'				=>  $_ARRAYLANG['TXT_EGOV_VALUE_S'],
    		'TXT_EGOV_MANDATORY_FIELD'		=>  $_ARRAYLANG['TXT_EGOV_MANDATORY_FIELD'],
    		'TXT_BROWSE'					=>  $_ARRAYLANG['TXT_BROWSE'],
    		'TXT_EGOV_SEPARATE_MULTIPLE_VALUES_BY_COMMA' =>  $_ARRAYLANG['TXT_EGOV_SEPARATE_MULTIPLE_VALUES_BY_COMMA'],
    		'PRODUCT_FORM_DESC'				=>  get_wysiwyg_editor('contactFormDesc', $this->GetProduktValue("product_desc", $product_id), 'shop'),
    		'PRODUCT_FORM_QUIANTITY'		=>  $this->GetProduktValue("product_quantity", $product_id),
    		'PRODUCT_FORM_NAME'				=>  $this->GetProduktName($product_id),
    		'PRODUCT_FORM_EMAIL'			=>  $TargetEmail,
    		'PRODUCT_FORM_TARGET_URL'		=>  $this->GetProduktValue("product_target_url", $product_id),
    		'PRODUCT_FORM_TARGET_MESSAGE'	=>  get_wysiwyg_editor('productFormTargetMessage', $this->GetProduktValue("product_message", $product_id), 'shop'),
    		'PRODUCT_FORM_PRICE'			=>  $this->GetProduktValue("product_price", $product_id),
    		'PRODUCT_ID'					=>  $product_id,
	    	'EGOV_JS_SUBMIT_FUNCTION'		=>  $jsSubmitFunction,
	    	'TXT_SAVE'						=>  $_ARRAYLANG['TXT_SAVE'],
	    	'TXT_EGOV_CONFIRM_CREATE_CONTENT_SITE' =>  $_ARRAYLANG['TXT_EGOV_CONFIRM_CREATE_CONTENT_SITE'],
	    	'TXT_EGOV_CONFIRM_UPDATE_CONTENT_SITE' =>  $_ARRAYLANG['TXT_EGOV_CONFIRM_UPDATE_CONTENT_SITE'],
	    	'TXT_STATE' 					=>  $_ARRAYLANG['TXT_STATE'],
	    	'STATE_CHECKED'					=> $StatusChecked,
	    	'TXT_EGOV_PRODUCT_AUTO'			=> $_ARRAYLANG['TXT_EGOV_PRODUCT_AUTO'],
	    	'AUTOSTATUS_CHECKED_YES'		=> $AutoJaChecked,
	    	'AUTOSTATUS_CHECKED_NO'			=> $AutoNeinChecked,
	    	'TXT_EGOV_PRODUCT_ELECTRO'		=> $_ARRAYLANG['TXT_EGOV_PRODUCT_ELECTRO'],
	    	'ELECTRO_CHECKED'				=> $electro_checked,
	    	'TXT_EGOV_PRODUCT_SELECT_FILE'	=> $_ARRAYLANG['TXT_EGOV_PRODUCT_SELECT_FILE'],
	    	'PRODUCT_FORM_FILE'				=> $this->GetProduktValue("product_file", $product_id),
	    	'TXT_EGOV_ORDER_STATE_AUTOMAIL'	=> $_ARRAYLANG["TXT_EGOV_ORDER_STATE_AUTOMAIL"],
	    	'TXT_EGOV_BASIC_DATA'			=> $_ARRAYLANG["TXT_EGOV_BASIC_DATA"],
	    	'TXT_EGOV_EXTENDED_OPTIONS'		=> $_ARRAYLANG["TXT_EGOV_EXTENDED_OPTIONS"],
	    	'TXT_EGOV_SENDER_NAME'			=> $_ARRAYLANG['TXT_EGOV_SENDER_NAME'],
    		'TXT_EGOV_SENDER_EMAIL'			=> $_ARRAYLANG['TXT_EGOV_SENDER_EMAIL'],
    		'PRODUCT_SENDER_NAME'			=> $ProductSenderName,
    		'PRODUCT_SENDER_EMAIL'			=> $ProductSenderEmail,
    		'EGOV_TXT_EMAIL_TEMPLATE_FOR_CUSTOMER'	 => $_ARRAYLANG['EGOV_TXT_EMAIL_TEMPLATE_FOR_CUSTOMER'],
    		'TXT_EGOV_SUBJECT'				=> $_ARRAYLANG['TXT_EGOV_SUBJECT'],
    		'EGOV_TXT_EMAIL_TEMPLATE'		=> $_ARRAYLANG['EGOV_TXT_EMAIL_TEMPLATE'],
    		'PRODUCT_TARGET_SUBJECT'		=> $ProductTargetSubject,
    		'PRODUCT_TARGET_BODY'			=> $ProductTargetBody,
    		'TXT_EGOV_PRODUCTNAME_PLACEHOLDER'		 => $_ARRAYLANG['TXT_EGOV_PRODUCTNAME_PLACEHOLDER'],
			'TXT_EGOV_ORDERDETAILS_PLACEHOLDER'		 => $_ARRAYLANG['TXT_EGOV_ORDERDETAILS_PLACEHOLDER'],
			'PAYPAL_YES'				 	=> $PayPal_yes,
			'PAYPAL_NO'				 		=> $PayPal_no,
			'TXT_EGOV_ACTIVATE_PAYPAL'		=> $_ARRAYLANG['TXT_EGOV_ACTIVATE_PAYPAL'],
			'TXT_EGOV_SANDBOX_EMAIL'		=> $_ARRAYLANG['TXT_EGOV_SANDBOX_EMAIL'],
			'SANDBOX_EMAIL'					=> $paypalEmail,
			'TXT_EGOV_PAYMENTS'				=> $_ARRAYLANG['TXT_EGOV_PAYMENTS'],
			'TXT_EGOV_PAYPAL_CURRENCY'		=> $_ARRAYLANG['TXT_EGOV_PAYPAL_CURRENCY'],
			'SELECTED_CHF'					=> $selected_CHF,
			'SELECTED_EUR'					=> $selected_EUR,
			'SELECTED_USD'					=> $selected_USD,
			'SELECTED_GBP'					=> $selected_GBP,
			'SELECTED_JPY'					=> $selected_JPY,
	   	));

	   	if($this->GetProduktValue("product_per_day", $product_id)=="yes"){
			$this->_objTpl->setVariable(array(
	    		'PER_DAY_CHECKED_YES'	=>	"checked"
		   	));
	   	}else{
	   		$this->_objTpl->setVariable(array(
	    		'PER_DAY_CHECKED_NO'	=>	"checked"
		   	));
	   	}

	   	$lastFieldId = 0;
	   	if($product_id!=0){
	   		$arrFields 		= &$this->getFormFields($product_id);

	   	}else{
	   		$this->_objTpl->setVariable(array(
				'EGOV_FORM_FIELD_NAME'					=> '',
				'EGOV_FORM_FIELD_ID'					=> 1,
				'EGOV_FORM_FIELD_TYPE_MENU'				=> $this->_getFormFieldTypesMenu('contactFormFieldType[1]', 'text', 'id="contactFormFieldType_1" onchange="setFormFieldAttributeBox(this.getAttribute(\'id\'), this.value)"'),
				'EGOV_FORM_FIELD_CHECK_MENU'			=> $this->_getFormFieldCheckTypesMenu('contactFormFieldCheckType[1]', 'contactFormFieldCheckType_1', 'text', 1),
				'EGOV_FORM_FIELD_CHECK_BOX'				=> $this->_getFormFieldRequiredCheckBox('contactFormFieldRequired[1]', 'contactFormFieldRequired_1', 'text', false),
				'EGOV_FORM_FIELD_ATTRIBUTES'			=> $this->_getFormFieldAttribute(1, 'text', '')
			));
			$this->_objTpl->parse('egov_form_field_list');
			$lastFieldId = 1;
	   	}

	   	if (isset($arrFields) && is_array($arrFields)) {
			foreach ($arrFields as $fieldId => $arrField) {
				if ($arrField['is_required'] == 1 ) {
					$checked = true;
				} else {
					$checked = false;
				}

				$this->_objTpl->setVariable(array(
					'EGOV_FORM_FIELD_NAME'					=> $arrField['name'],
					'EGOV_FORM_FIELD_ID'					=> $fieldId,
					'EGOV_FORM_FIELD_TYPE_MENU'				=> $this->_getFormFieldTypesMenu('contactFormFieldType['.$fieldId.']', $arrField['type'], 'id="contactFormFieldType_'.$fieldId.'" onchange="setFormFieldAttributeBox(this.getAttribute(\'id\'), this.value)"'),
					'EGOV_FORM_FIELD_CHECK_MENU'			=> $this->_getFormFieldCheckTypesMenu('contactFormFieldCheckType['.$fieldId.']', 'contactFormFieldCheckType_'.$fieldId, $arrField['type'], $arrField['check_type']),
					'EGOV_FORM_FIELD_CHECK_BOX'				=> $this->_getFormFieldRequiredCheckBox('contactFormFieldRequired['.$fieldId.']', 'contactFormFieldRequired_'.$fieldId, $arrField['type'], $checked),
					'EGOV_FORM_FIELD_ATTRIBUTES'			=> $this->_getFormFieldAttribute($fieldId, $arrField['type'], $arrField['attributes'])
				));
				$this->_objTpl->parse('egov_form_field_list');

				$lastFieldId = $fieldId > $lastFieldId ? $fieldId : $lastFieldId;
			}
		}

		$this->_objTpl->setVariable(array(
			'CONTACT_FORM_FIELD_NEXT_ID'					=> $lastFieldId+1,
			'CONTACT_FORM_FIELD_NEXT_TEXT_TPL'				=> $this->_getFormFieldAttribute($lastFieldId+1, 'text', ''),
			'CONTACT_FORM_FIELD_LABEL_TPL'					=> $this->_getFormFieldAttribute($lastFieldId+1, 'label', ''),
			'CONTACT_FORM_FIELD_CHECK_MENU_NEXT_TPL'		=> $this->_getFormFieldCheckTypesMenu('contactFormFieldCheckType['.($lastFieldId+1).']', 'contactFormFieldCheckType_'.($lastFieldId+1), 'text', 1),
			'CONTACT_FORM_FIELD_CHECK_MENU_TPL'				=> $this->_getFormFieldCheckTypesMenu('contactFormFieldCheckType[0]', 'contactFormFieldCheckType_0', 'text', 1),
			'CONTACT_FORM_FIELD_CHECK_BOX_NEXT_TPL'			=> $this->_getFormFieldRequiredCheckBox('contactFormFieldRequired['.($lastFieldId+1).']', 'contactFormFieldRequired_'.($lastFieldId+1), 'text', false),
			'CONTACT_FORM_FIELD_CHECK_BOX_TPL'				=> $this->_getFormFieldRequiredCheckBox('contactFormFieldRequired[0]', 'contactFormFieldRequired_0', 'text', false),
			'CONTACT_FORM_FIELD_TYPE_MENU_TPL'				=> $this->_getFormFieldTypesMenu('contactFormFieldType['.($lastFieldId+1).']', key($this->_arrFormFieldTypes), 'id="contactFormFieldType_'.($lastFieldId+1).'" onchange="setFormFieldAttributeBox(this.getAttribute(\'id\'), this.value)"'),
			'CONTACT_FORM_FIELD_TEXT_TPL'					=> $this->_getFormFieldAttribute(0, 'text', ''),
			'CONTACT_FORM_FIELD_CHECKBOX_TPL'				=> $this->_getFormFieldAttribute(0, 'checkbox', 0),
			'CONTACT_FORM_FIELD_CHECKBOX_GROUP_TPL'			=> $this->_getFormFieldAttribute(0, 'checkboxGroup', ''),
			'CONTACT_FORM_FIELD_HIDDEN_TPL'					=> $this->_getFormFieldAttribute(0, 'hidden', ''),
			'CONTACT_FORM_FIELD_RADIO_TPL'					=> $this->_getFormFieldAttribute(0, 'radio', ''),
			'CONTACT_FORM_FIELD_SELECT_TPL'					=> $this->_getFormFieldAttribute(0, 'select', ''),
			'CONTACT_JS_SUBMIT_FUNCTION'					=> $jsSubmitFunction
		));


	}

	function _products($SaveError=''){
		global $objDatabase, $_ARRAYLANG;

		$this->_objTpl->loadTemplateFile('module_gov_products.html');
		$this->_pageTitle = $_ARRAYLANG['TXT_EGOV_EDIT_PRODUCT'];
		// delete
		// -----------------------------------------------------------------
		if(isset($_REQUEST["delete"])){
			if($_REQUEST["delete"]=="yes"){
				$objDatabase->Execute('	DELETE
					FROM	'.DBPREFIX.'module_egov_products
					WHERE	product_id='.$_REQUEST["id"].'
				');
				$objDatabase->Execute('	DELETE
					FROM	'.DBPREFIX.'module_egov_product_fields
					WHERE	product='.$_REQUEST["id"].'
				');
			}
		}
		// -----------------------------------------------------------------

		// save product
		// -----------------------------------------------------------------
		if($_REQUEST["tpl"]=="save" && $SaveError==''){
			$this->_strOkMessage .= $_ARRAYLANG['TXT_EGOV_PRODUCT_SUCCESSFULLY_SAVED'];
		}
		if($_REQUEST["tpl"]=="save" && $SaveError==1){
			$this->_strErrMessage .= $_ARRAYLANG['TXT_EGOV_FORM_FIELD_UNIQUE_MSG'];
		}
		if($_REQUEST["tpl"]=="save" && $SaveError==2){
			$this->_strErrMessage .= $_ARRAYLANG['TXT_EGOV_FILE_ERROR'];
		}
		// -----------------------------------------------------------------

		// Position
		// -----------------------------------------------------------------
		if(isset($_REQUEST["Direction"])){

			$query = "SELECT count(*) as anzahl FROM ".DBPREFIX."module_egov_products";
			$objResult = $objDatabase->Execute($query);
			if ($objResult->RecordCount() == 1) {
				$anzahl = $objResult->fields["anzahl"];
			}

			if($_REQUEST["Direction"]=="up"){
				$NewPosition = $this->GetProduktValue('product_orderby', $_REQUEST["id"])-1;
			}
			if($_REQUEST["Direction"]=="down"){
				$NewPosition = $this->GetProduktValue('product_orderby', $_REQUEST["id"])+1;
			}
			if($NewPosition<0){
				$NewPosition = 0;
			}
			if($NewPosition>$anzahl){
				$NewPosition = $anzahl;
			}

				$query = "SELECT product_id  FROM ".DBPREFIX."module_egov_products WHERE product_orderby=".$NewPosition;
				$objResult = $objDatabase->Execute($query);
				if ($objResult->RecordCount() == 1) {
					$TauschID = $objResult->fields["product_id"];
				}
				$query = "SELECT product_orderby  FROM ".DBPREFIX."module_egov_products WHERE product_id=".$_REQUEST["id"];
				$objResult = $objDatabase->Execute($query);
				if ($objResult->RecordCount() == 1) {
					$TauschPosition = $objResult->fields["product_orderby"];
				}

				$query = "UPDATE ".DBPREFIX."module_egov_products
							 SET product_orderby=".$TauschPosition."
							 WHERE product_id=".$TauschID."";
				if($objDatabase->Execute($query)){
					$this->_strOkMessage = $_ARRAYLANG['TXT_EGOV_PRODUCT_SUCCESSFULLY_SAVED'];
				}

				$query = "UPDATE ".DBPREFIX."module_egov_products
							 SET product_orderby=".$NewPosition."
							 WHERE product_id=".$_REQUEST["id"]."";
				if($objDatabase->Execute($query)){
					$this->_strOkMessage = $_ARRAYLANG['TXT_EGOV_PRODUCT_SUCCESSFULLY_SAVED'];
				}

		}


		$this->_objTpl->setVariable(array(
    		'TXT_PRODUCTS'			 	=>	$_ARRAYLANG['TXT_PRODUCTS'],
    		'TXT_PRODUCT'			 	=>	$_ARRAYLANG['TXT_PRODUCT'],
    		'TXT_MARKED'			 	=>	$_ARRAYLANG['TXT_MARKED'],
    		'TXT_SELECT_ALL'			=>	$_ARRAYLANG['TXT_SELECT_ALL'],
    		'TXT_DESELECT_ALL'		 	=>	$_ARRAYLANG['TXT_DESELECT_ALL'],
    		'TXT_SUBMIT_SELECT'			=>	$_ARRAYLANG['TXT_SUBMIT_SELECT'],
    		'TXT_SUBMIT_DELETE'			=>	$_ARRAYLANG['TXT_SUBMIT_DELETE'],
    		'TXT_IMGALT_EDIT'			=>	$_ARRAYLANG['TXT_IMGALT_EDIT'],
    		'TXT_IMGALT_DELETE'			=>	$_ARRAYLANG['TXT_IMGALT_DELETE'],
    		'TXT_DELETE_PRODUCT'		=>	$_ARRAYLANG['TXT_DELETE_PRODUCT'],
    		'TXT_EGOV_ADD_NEW_PRODUCT'	=>	$_ARRAYLANG['TXT_EGOV_ADD_NEW_PRODUCT'],
    		'TXT_EGOV_RESERVATIONS'		=>	$_ARRAYLANG['TXT_EGOV_RESERVATIONS'],
    		'TXT_ORDERS'				=>	$_ARRAYLANG['TXT_ORDERS'],
    		'TXT_FUNCTIONS'				=>	$_ARRAYLANG['TXT_FUNCTIONS'],
    		'TXT_EGOV_SEQUENCE'			=>	$_ARRAYLANG['TXT_EGOV_SEQUENCE'],
    		'TXT_EGOV_UP'				=>	$_ARRAYLANG['TXT_EGOV_UP'],
    		'TXT_EGOV_DOWN'				=>	$_ARRAYLANG['TXT_EGOV_DOWN']
	   	));

	   	$query = "SELECT *
		          FROM ".DBPREFIX."module_egov_products
		          ORDER BY product_orderby, product_name";
		$objResult = $objDatabase->Execute($query);
		$i = 0;
		while(!$objResult->EOF) {
			$rowclass = ($i % 2) ? 'row1' : 'row2';

			$StatusImg = '<img src="images/icons/status_green.gif" width="10" height="10" border="0" alt="" />';
			if($objResult->fields["product_status"]!=1){
				$StatusImg = '<img src="images/icons/status_red.gif" width="10" height="10" border="0" alt="" />';
			}

			$query_orders = "SELECT count(*) as anzahl
		          		FROM ".DBPREFIX."module_egov_orders
		          		WHERE order_product=".$objResult->fields["product_id"];
			$objResult_orders = $objDatabase->Execute($query_orders);

			$this->_objTpl->setVariable(array(
	    		'ROWCLASS'				=>	$rowclass,
	    		'PRODUCT_NR'		 		=>	$i,
	    		'PRODUCT_ID'				=>  $objResult->fields["product_id"],
	    		'PRODUCT_NAME'				=>  $objResult->fields["product_name"],
	    		'PRODUCT_STATUS'			=>  $StatusImg,
	    		'PRODUCT_POSITION'			=>  $objResult->fields["product_orderby"],
	    		'TXT_EDIT'					=>	$_ARRAYLANG['TXT_EDIT'],
    			'TXT_DELETE'				=>	$_ARRAYLANG['TXT_DELETE'],
    			'TXT_EGOV_SOURCECODE'		=>	$_ARRAYLANG['TXT_EGOV_SOURCECODE'],
    			'ORDERS_VALUE'				=>	$objResult_orders->fields["anzahl"],
    			'TXT_EGOV_VIEW_ORDERS'		=>  $_ARRAYLANG['TXT_EGOV_VIEW_ORDERS'],
    			'TXT_IMGALT_COPY'			=>	$_ARRAYLANG['TXT_IMGALT_COPY'],
    			'TXT_COPY'					=>	$_ARRAYLANG['TXT_COPY'],
    			'TXT_EGOV_UP'				=>	$_ARRAYLANG['TXT_EGOV_UP'],
    			'TXT_EGOV_DOWN'				=>	$_ARRAYLANG['TXT_EGOV_DOWN']
	   		));

	   		$product_id = $objResult->fields["product_id"];
	   		if($this->GetProduktValue("product_per_day", $product_id)=="yes"){

		   		$LastYear = date("Y")-1;
		   		$query_rl = "SELECT *
			          FROM ".DBPREFIX."module_egov_product_calendar
			          WHERE calendar_product=".$product_id." and calendar_year>".$LastYear."
			          GROUP BY calendar_day, calendar_month, calendar_year
			          ORDER BY calendar_year, calendar_month, calendar_day";
				$objResult_rl = $objDatabase->Execute($query_rl);
				$counter = 0;
				$optionContent = '';
				$ProductQuant = $this->GetProduktValue("product_quantity", $product_id);
				while(!$objResult_rl->EOF) {

					$query_count = "SELECT count(*) as anzahl
				          FROM ".DBPREFIX."module_egov_product_calendar
				          WHERE calendar_product=".$product_id." and calendar_day=".$objResult_rl->fields["calendar_day"]." and calendar_month=".$objResult_rl->fields["calendar_month"]." and calendar_year=".$objResult_rl->fields["calendar_year"]." and calendar_act=1";
					$objResult_count = $objDatabase->Execute($query_count);

					$ReservedQuantity = '('.$objResult_count->fields["anzahl"].'/'.$ProductQuant.')';

					$optionContent .= '<option value="'.$objResult_rl->fields["calendar_id"].'">'.$objResult_rl->fields["calendar_day"].'.'.$objResult_rl->fields["calendar_month"].'.'.$objResult_rl->fields["calendar_year"].' '.$ReservedQuantity.'</option>';
					$counter++;
					$objResult_rl->MoveNext();
				}

				if($counter==0){
					$this->_objTpl->setVariable(array(
			    		'RESERVATIONS_VALUE'	=>	"",
				   	));
				}else{
					$this->_objTpl->setVariable(array(
			    		'RESERVATIONS_VALUE'	=>	'<select name="ReservedDays_'.$product_id.'" style="width: 150px;">'.$optionContent.'</select>'
				   	));
				}

	   		}

			$this->_objTpl->parse('products_list');
			$i++;
			$objResult->MoveNext();
		}
		if ($i == 0) {
			$this->_objTpl->hideBlock('products_list');
		}

	}

	function _order_edit(){
		global $objDatabase, $_ARRAYLANG;

		$this->_objTpl->loadTemplateFile('module_gov_order_edit.html');
		$this->_pageTitle = $_ARRAYLANG['TXT_ORDER_EDIT'];

		$query = "SELECT *
		          FROM ".DBPREFIX."module_egov_orders
		          WHERE order_id=".intval($_REQUEST["id"])."";
		$objResult = $objDatabase->Execute($query);

		// update
		// -----------------------------------------------------
		if(isset($_REQUEST["update"])){
			if($_REQUEST["update"]=="exe"){
				$query = "UPDATE ".DBPREFIX."module_egov_orders
							 SET order_state=".$_REQUEST["state"]."
							 WHERE order_id=".$_REQUEST["id"]."";
				if($objDatabase->Execute($query)){
					$this->_strOkMessage = $_ARRAYLANG['TXT_STATE_UPDATED_SUCCESSFUL'];
				}

				$query = "SELECT *
			          FROM ".DBPREFIX."module_egov_orders
			          WHERE order_id=".intval($_REQUEST["id"])."";
					$objResult = $objDatabase->Execute($query);
					$proId = $objResult->fields["order_product"];
					if($this->GetProduktValue("product_per_day", $proId)=="yes"){
						if(intval($_REQUEST["state"])==2 || intval($_REQUEST["state"])==0){
							$act = 0;
						}else{
							$act = 1;
						}
						$query = "UPDATE ".DBPREFIX."module_egov_product_calendar
							 SET calendar_act=".$act."
							 WHERE calendar_order=".$_REQUEST["id"]."";
						$objDatabase->Execute($query);
					}

				if($_REQUEST["ChangeStateMessage"]){

					$SubjectText = str_replace("[[PRODUCT_NAME]]", html_entity_decode($this->GetProduktValue("product_name", $proId)), $_REQUEST["email_subject"]);
					$SubjectText = html_entity_decode($SubjectText);

					$FormValue4Mail = '';
					// ------------------------------------------------------------
					$GSdata = split(";;", $objResult->fields["order_values"]);
					for($y=0; $y<count($GSdata); $y++) {
						if(!empty($GSdata[$y])){
							list ($FieldName, $FieldValue) = split('::', $GSdata[$y]);
							if($FieldName!=""){
								$FormValue4Mail .= $FieldName.': '.$FieldValue;
							}
						}
					}
					// ------------------------------------------------------------

					$BodyText = str_replace("[[ORDER_VALUE]]", $FormValue4Mail, $_REQUEST["email_text"]);
					$BodyText = str_replace("[[PRODUCT_NAME]]", html_entity_decode($this->GetProduktValue("product_name", $proId)), $BodyText);
					$BodyText = html_entity_decode($BodyText);

					$FromEmail 	= $this->GetProduktValue("product_sender_email", $proId);
					if($FromEmail==''){
						$FromEmail = $this->GetSettings("set_sender_email");
					}

					$FromName 	= $this->GetProduktValue("product_sender_name", $proId);
					if($FromName==''){
						$FromName = $this->GetSettings("set_sender_name");
					}

					$TargetMail = $_REQUEST["email"];
					if($TargetMail == ''){
						$TargetMail = $this->GetEmailAdress($order_id);
					}
					if($TargetMail!=''){
						if (@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') {
							$objMail = new phpmailer();
							$objMail->CharSet = CONTREXX_CHARSET;
							$objMail->From 		= $FromEmail;
							$objMail->FromName 	= $FromName;
							$objMail->AddReplyTo($FromEmail);
							$objMail->Subject 	= $SubjectText;
							$objMail->Priority 	= 3;
							$objMail->IsHTML(false);
							$objMail->Body 		= $BodyText;
							$objMail->AddAddress($TargetMail);
							$objMail->Send();
						}
					}

				}

			}
		}
		// -----------------------------------------------------

		$query = "SELECT *
		          FROM ".DBPREFIX."module_egov_orders
		          WHERE order_id=".intval($_REQUEST["id"])."";
		$objResult = $objDatabase->Execute($query);

		if ($objResult->RecordCount() != 1) {
			header("Location: index.php?cmd=egov&err=Wrong Order-ID");
			exit;
		}

		// selected state
		// ----------------------------------------------
		$selected_ok = '';
		$selected_deleted = '';
		if($objResult->fields["order_state"]==1){
			$selected_ok = "selected";
		}
		if($objResult->fields["order_state"]==2){
			$selected_deleted = "selected";
		}
		// ----------------------------------------------

		$this->_objTpl->setVariable(array(
    		'TXT_DATE'			 			=>	$_ARRAYLANG['TXT_DATE'],
    		'TXT_STATE'			 			=>	$_ARRAYLANG['TXT_STATE'],
    		'TXT_FUNCTIONS'		 			=>	$_ARRAYLANG['TXT_FUNCTIONS'],
    		'TXT_PRODUCT'					=>	$_ARRAYLANG['TXT_PRODUCT'],
    		'TXT_ORDER'						=>  $_ARRAYLANG['TXT_ORDER'],
    		'TXT_IP_ADDRESS'				=>  $_ARRAYLANG['TXT_IP_ADDRESS'],
    		'TXT_DATA'						=>  $_ARRAYLANG['TXT_DATA'],
    		'TXT_STATE_NEW'					=>  $_ARRAYLANG['TXT_STATE_NEW'],
    		'TXT_STATE_OK'					=>  $_ARRAYLANG['TXT_STATE_OK'],
    		'TXT_STATE_DELETED'				=>  $_ARRAYLANG['TXT_STATE_DELETED'],
    		'TXT_SEND_STATE_CHANGE_EMAIL' 	=>  $_ARRAYLANG['TXT_SEND_STATE_CHANGE_EMAIL'],
    		'TXT_CHANGE_STATE'				=>  $_ARRAYLANG['TXT_CHANGE_STATE'],
    		'TXT_EMAIL_TEXT'				=>  $_ARRAYLANG['TXT_EMAIL_TEXT'],
    		'TXT_EMAIL_ADDRESS'				=>  $_ARRAYLANG['TXT_EMAIL_ADDRESS'],
    		'TXT_SAVE_AND_SEND'				=>  $_ARRAYLANG['TXT_SAVE_AND_SEND'],
    		'TXT_SAVE'						=>  $_ARRAYLANG['TXT_SAVE'],
    		'TXT_SAVE_WITHOUT_EMAIL'		=>  $_ARRAYLANG['TXT_SAVE_WITHOUT_EMAIL'],
    		'TXT_SVE_WITH_EMAIL'			=>  $_ARRAYLANG['TXT_SVE_WITH_EMAIL'],
    		'TXT_EMPTY_EMAIL'				=>  $_ARRAYLANG['TXT_EMPTY_EMAIL'],
    		'SETTINGS_STATE_CHANGE_EMAIL' 	=>  $this->GetSettings("set_state_email"),
    		'SELECTED_STATE_OK'				=>  $selected_ok,
    		'SELECTED_STATE_DELETED'		=>  $selected_deleted,
    		'ORDER_ID'						=>  $objResult->fields["order_id"],
    		'ORDER_IP'						=>  $objResult->fields["order_ip"],
    		'ORDER_DATE'					=>  $objResult->fields["order_date"],
    		'ORDER_PRODUCT'					=>  $this->GetProduktName($objResult->fields["order_product"]),
    		'TXT_EGOV_SUBJECT'				=> $_ARRAYLANG['TXT_EGOV_SUBJECT'],
    		'STATE_SUBJECT'					=> $this->GetSettings("set_state_subject")
	   	));

	   	// form falues
		// ------------------------------------------------------------
		$GSdata = split(";;", $objResult->fields["order_values"]);
		for($y=0; $y<count($GSdata); $y++) {
			$rowclass = ($y % 2) ? 'row1' : 'row2';
			if(!empty($GSdata[$y])){
				list ($FieldName, $FieldValue) = split('::', $GSdata[$y]);
				if($FieldName!=""){
					$this->_objTpl->setVariable(array(
			    		'ROWCLASS'				 =>	$rowclass,
			    		'DATA_FIELD'			 =>	$FieldName,
			    		'DATA_VALUE'			 =>	$FieldValue
			   		));
					$this->_objTpl->parse('orders_data_row');
				}
			}
		}
		// ------------------------------------------------------------
	}

	function _orders(){

		global $objDatabase, $_ARRAYLANG;

		$this->_objTpl->loadTemplateFile('module_gov_orders_overview.html');
		$this->_pageTitle = $_ARRAYLANG['TXT_ORDERS'];

		if(isset($_REQUEST["err"])){
			$this->_strErrMessage = $_REQUEST["err"];
		}

		// delete order/s
		// -----------------------------------------------------------------
		if(isset($_REQUEST["delete"])){
			if($_REQUEST["delete"]=="yes"){
				if($_REQUEST["multi"]=="yes"){
					if (is_array($_POST['selectedOrderId'])) {
				    	foreach ($_POST['selectedOrderId'] as $intKey => $GSOrderID) {
				    		$objDatabase->Execute('	DELETE
									FROM	'.DBPREFIX.'module_egov_orders
									WHERE	order_id = '.intval($GSOrderID).'
								');
				    		$objDatabase->Execute('DELETE
									FROM	'.DBPREFIX.'module_egov_product_calendar
									WHERE	calendar_order='.intval($GSOrderID).'
								');
				    	}
				    }
				}else{
					$objDatabase->Execute('DELETE
						FROM	'.DBPREFIX.'module_egov_orders
						WHERE	order_id='.intval($_REQUEST["id"]).'
					');
					$objDatabase->Execute('DELETE
						FROM	'.DBPREFIX.'module_egov_product_calendar
						WHERE	calendar_order='.intval($_REQUEST["id"]).'
					');
				}
			}
		}
		// -----------------------------------------------------------------

		$this->_objTpl->setVariable(array(
    		'TXT_DATE'			 		=>	$_ARRAYLANG['TXT_DATE'],
    		'TXT_STATE'			 		=>	$_ARRAYLANG['TXT_STATE'],
    		'TXT_FUNCTIONS'		 		=>	$_ARRAYLANG['TXT_FUNCTIONS'],
    		'TXT_NAME'			 		=>	$_ARRAYLANG['TXT_NAME'],
    		'TXT_PRODUCT'				=>	$_ARRAYLANG['TXT_PRODUCT'],
    		'TXT_SELECT_ALL'			=>	$_ARRAYLANG['TXT_SELECT_ALL'],
    		'TXT_DESELECT_ALL'			=>	$_ARRAYLANG['TXT_DESELECT_ALL'],
    		'TXT_SUBMIT_SELECT'			=>	$_ARRAYLANG['TXT_SUBMIT_SELECT'],
    		'TXT_SUBMIT_DELETE'			=>	$_ARRAYLANG['TXT_SUBMIT_DELETE'],
    		'TXT_JS_DELETE_ALL_ORDERS'	=>	$_ARRAYLANG['TXT_JS_DELETE_ALL_ORDERS'],
    		'TXT_DELETE_ORDER'			=>  $_ARRAYLANG['TXT_DELETE_ORDER'],
    		'TXT_ORDERS'				=>  $_ARRAYLANG['TXT_ORDERS'],
	   	));

	   	$WhereStatement = '';
	   	if(isset($_REQUEST["product"])){
		   	if(intval($_REQUEST["product"])>0){
		   		$WhereStatement = ' where order_product='.$_REQUEST["product"].' ';
		   	}
	   	}

	   	$query = "SELECT *
		          FROM ".DBPREFIX."module_egov_orders ".$WhereStatement."
		          ORDER BY order_id DESC";
		$objResult = $objDatabase->Execute($query);
		$i = 0;

		while(!$objResult->EOF) {

			$rowclass = ($i % 2) ? 'row1' : 'row2';
			switch ($objResult->fields["order_state"]){
				case 0:
					$stateImg = 'status_yellow.gif';
				break;
				case 1:
					$stateImg = 'status_green.gif';
				break;
				case 2:
					$stateImg = 'status_red.gif';
				break;
			}

			$this->_objTpl->setVariable(array(
	    		'ORDERS_ROWCLASS'			=>	$rowclass,
	    		'ORDERS_NR'			 		=>	$i,
	    		'ORDER_ID'					=>  $objResult->fields["order_id"],
	    		'ORDER_DATE'				=>  $objResult->fields["order_date"],
	    		'ORDER_ID'					=>  $objResult->fields["order_id"],
	    		'ORDER_STATE'				=>  $this->MaskState($objResult->fields["order_state"]),
	    		'ORDER_PRODUCT'				=>  $this->GetProduktName($objResult->fields["order_product"]),
	    		'ORDER_NAME'				=>  $this->ParseFormValues("Name", $objResult->fields["order_values"]).' ('.$objResult->fields["order_ip"].')',
	    		'TXT_EDIT'					=>  $_ARRAYLANG['TXT_EDIT'],
	    		'TXT_DELETE'				=>  $_ARRAYLANG['TXT_DELETE'],
	    		'ORDER_STATE_IMG'			=>	$stateImg,
	   		));

			$this->_objTpl->parse('orders_row');
			$i++;
			$objResult->MoveNext();
		}

		if ($i == 0) {
			$this->_objTpl->hideBlock('orders_row');
		}

	}

	function _getFormFieldTypesMenu($name, $selectedType, $attrs = ''){

		$menu = "<select name=\"".$name."\" ".$attrs.">\n";

		foreach ($this->_arrFormFieldTypes as $type => $desc) {
			$menu .= "<option value=\"".$type."\"".($selectedType == $type ? 'selected="selected"' : '').">".$desc."</option>\n";
		}

		$menu .= "</select>\n";
		return  $menu;
	}

	function _getFormFieldCheckTypesMenu($name, $id,  $type, $selected){
		global $_ARRAYLANG;

		switch ($type) {
			case 'checkbox':
			case 'checkboxGroup':
			case 'hidden':
			case 'radio':
			case 'select':
			case 'label':
				$menu = '';
				break;

			case 'text':
			case 'file':
			case 'password':
			case 'textarea':
			default:
				$menu = "<select name=\"".$name."\" id=\"".$id."\">\n";
				foreach ($this->arrCheckTypes as $typeId => $type) {
					if ($selected == $typeId) {
						$select = "selected=\"selected\"";
					} else {
						$select = "";
					}

					$menu .= "<option value=\"".$typeId."\" $select>".$_ARRAYLANG[$type['name']]."</option>\n";
				}

				$menu .= "</select>\n";
			break;
		}
		return  $menu;
	}

	function _getFormFieldRequiredCheckBox($name, $id, $type, $selected){
		global $_ARRAYLANG;

		switch ($type) {
			case 'hidden':
			case 'select':
			case 'label':
				return '';
				break;

			default:
				return '<input type="checkbox" name="'.$name.'" id="'.$id.'" '.($selected ? 'checked="checked"' : '').' />';
				break;
		}
	}

	function _getFormFieldAttribute($id, $type, $attr){
		global $_ARRAYLANG;

		switch ($type) {
		case 'text':
			return "<input style=\"width:228px;\" type=\"text\" name=\"contactFormFieldAttribute[".$id."]\" value=\"".$attr."\" />\n";
			break;

		case 'label':
			return "<input style=\"width:228px;\" type=\"text\" name=\"contactFormFieldAttribute[".$id."]\" value=\"".$attr."\" />\n";
			break;

		case 'checkbox':
			return "<select style=\"width:228px;\" name=\"contactFormFieldAttribute[".$id."]\">\n
						<option value=\"0\"".($attr == 0 ? ' selected="selected"' : '').">".$_ARRAYLANG['TXT_EGOV_NOT_SELECTED']."</option>\n
						<option value=\"1\"".($attr == 1 ? ' selected="selected"' : '').">".$_ARRAYLANG['TXT_EGOV_SELECTED']."</option>\n
					</select>";
			break;

		case 'checkboxGroup':
			return "<input style=\"width:228px;\" type=\"text\" name=\"contactFormFieldAttribute[".$id."]\" value=\"".$attr."\" /> *\n";
			break;

		case 'hidden':
			return "<input style=\"width:228px;\" type=\"text\" name=\"contactFormFieldAttribute[".$id."]\" value=\"".$attr."\" />\n";
			break;

		case 'select':
		case 'radio':
			return "<input style=\"width:228px;\" type=\"text\" name=\"contactFormFieldAttribute[".$id."]\" value=\"".$attr."\" /> *\n";
			break;

		default:
			return '';
			break;
		}
	}

	function _saveForm()
	{
		global $_ARRAYLANG, $_CONFIG;

		if (isset($_REQUEST['saveForm'])) {
			$formId = isset($_REQUEST['formId']) ? intval($_REQUEST['formId']) : 0;

			$productName 			= isset($_POST['productFormName']) ? strip_tags(contrexx_addslashes($_POST['productFormName'])) : '';
			$contactFormDesc 		= isset($_POST["contactFormDesc"]) ? contrexx_addslashes($_POST["contactFormDesc"]) : '';
			$productFormTargetUrl	= isset($_POST["productFormTargetUrl"]) ? strip_tags(contrexx_addslashes($_POST["productFormTargetUrl"])) : '';
			$productFormTargetMessage = isset($_POST["productFormTargetMessage"]) ? contrexx_addslashes($_POST["productFormTargetMessage"]) : '';
			$productFormPerDay		= intval($_POST["productFormPerDay"]);
			$productFormQuintity	= intval($_POST["productFormQuintity"]);
			$productFormPrice		= floatval($_POST["productFormPrice"]);
			$productAutoStatus		= intval($_POST["productAutoStatus"]);
			$productFile			= isset($_POST["productFile"]) ? contrexx_addslashes($_POST['productFile']) : '';
			$productSenderName		= isset($_POST["productSenderName"]) ? strip_tags(contrexx_addslashes($_POST["productSenderName"])) : '';
			$productSenderEmail		= isset($_POST["productSenderEmail"]) ? strip_tags(contrexx_addslashes($_POST["productSenderEmail"])) : '';
			$productTargetSubject	= isset($_POST["productTargetSubject"]) ? strip_tags(contrexx_addslashes($_POST["productTargetSubject"])) : '';
			$productTargetBody		= isset($_POST["productTargetBody"]) ? strip_tags(contrexx_addslashes($_POST["productTargetBody"])) : '';
			$productPayPal			= intval($_POST["paypal"]);
			$productPayPalSandbox	= isset($_POST["sandbox_mail"]) ? strip_tags(contrexx_addslashes($_POST["sandbox_mail"])) : '';
			$productPayPalCurrency	= isset($_POST["PayPalcurrency"]) ? strip_tags(contrexx_addslashes($_POST["PayPalcurrency"])) : '';

			// Check Config-File
			// ----------------------------
			if($productFile == "config/configuration.php" || $productFile == "/config/configuration.php"){
				$productFile = '';
				$FileErr = 2;
			}else{
				$FileErr = '';
			}
			// ----------------------------

			if(isset($_POST["productState"])){
				$productState = 1;
			}else{
				$productState = 0;
			}
			if(isset($_POST["ElectroProduct"])){
				$productElectro = 1;
			}else{
				$productElectro = 0;
			}

			$arrFields = $this->_getFormFieldsFromPost($uniqueFieldNames);
			if ($uniqueFieldNames) {
				$formEmailsTmp = isset($_POST['productFormEmail']) ? explode(',', contrexx_addslashes($_POST['productFormEmail'])) : '';
				if (is_array($formEmailsTmp)) {
					$formEmails = array();
					foreach ($formEmailsTmp as $email) {
						$email = trim(contrexx_strip_tags($email));
						if (!empty($email)) {
							array_push($formEmails, $email);
						}
					}
					$formEmails = implode(',', $formEmails);
				} else {
					$formEmails = '';
				}
				if (empty($formEmails)) {
					$formEmails = $_CONFIG['contactFormEmail'];
				}
				if ($formId > 0) {
					$this->_updateProduct($formId, $productName, $contactFormDesc, $productFormTargetUrl, $productFormTargetMessage, $productFormPerDay, $productFormQuintity, $productFormPrice, $arrFields, $formEmails, $productState, $productAutoStatus, $productElectro, $productFile, $productSenderName, $productSenderEmail, $productTargetSubject, $productTargetBody, $productPayPal , $productPayPalSandbox, $productPayPalCurrency);
				} else {
					$this->_saveProduct($formId, $productName, $contactFormDesc, $productFormTargetUrl, $productFormTargetMessage, $productFormPerDay, $productFormQuintity, $productFormPrice, $arrFields, $formEmails, $productState, $productAutoStatus, $productElectro, $productFile, $productSenderName, $productSenderEmail, $productTargetSubject, $productTargetBody, $productPayPal , $productPayPalSandbox, $productPayPalCurrency);
				}
				$this->_products($FileErr);
			} else {
				$this->_products(1);
			}
		} else {
			//
		}
	}

	function _getFormFieldsFromPost(&$uniqueFieldNames)
	{
		$uniqueFieldNames = true;
		$arrFields = array();
		$arrFieldNames = array();
		$orderId = 0;

		if (isset($_POST['contactFormFieldName']) && is_array($_POST['contactFormFieldName'])) {
			foreach ($_POST['contactFormFieldName'] as $id => $fieldName) {
				$fieldName = htmlentities(strip_tags(contrexx_stripslashes($fieldName)), ENT_QUOTES, CONTREXX_CHARSET);
				$type = isset($_POST['contactFormFieldType'][$id]) && array_key_exists(contrexx_stripslashes($_POST['contactFormFieldType'][$id]), $this->_arrFormFieldTypes) ? contrexx_stripslashes($_POST['contactFormFieldType'][$id]) : key($this->_arrFormFieldTypes);
				$attributes = isset($_POST['contactFormFieldAttribute'][$id]) && !empty($_POST['contactFormFieldAttribute'][$id]) ? ($type == 'text' || $type == 'label' || $type == 'file' || $type == 'textarea' || $type == 'hidden' || $type == 'radio' || $type == 'checkboxGroup' || $type == 'password' || $type == 'select' ? htmlentities(strip_tags(contrexx_stripslashes($_POST['contactFormFieldAttribute'][$id])), ENT_QUOTES, CONTREXX_CHARSET) : intval($_POST['contactFormFieldAttribute'][$id])) : '';
				$is_required = isset($_POST['contactFormFieldRequired'][$id]) ? 1 : 0;
				$checkType = isset($_POST['contactFormFieldCheckType'][$id]) ? intval($_POST['contactFormFieldCheckType'][$id]) : 1;

				if (!in_array($fieldName, $arrFieldNames)) {
					array_push($arrFieldNames, $fieldName);
				} else {
					$uniqueFieldNames = false;
				}

				switch ($type) {
					case 'checkboxGroup':
					case 'radio':
					case 'select':
						$arrAttributes = explode(',', $attributes);
						$arrNewAttributes = array();
						foreach ($arrAttributes as $strAttribute) {
							array_push($arrNewAttributes, trim($strAttribute));
						}
						$attributes = implode(',', $arrNewAttributes);
						break;

					default:
						break;
				}

				$arrFields[intval($id)] = array(
					'name'			=> $fieldName,
					'type'			=> $type,
					'attributes'	=> $attributes,
					'order_id'		=> $orderId,
					'is_required'	=> $is_required,
					'check_type'	=> $checkType
				);

				$orderId++;
			}
		}
		return $arrFields;
	}

	function _updateProduct($formId, $productName, $contactFormDesc, $productFormTargetUrl, $productFormTargetMessage, $productFormPerDay, $productFormQuintity, $productFormPrice, $arrFields, $formEmails, $productState, $productAutoStatus, $productElectro, $productFile, $productSenderName, $productSenderEmail, $productTargetSubject, $productTargetBody, $productPayPal , $productPayPalSandbox, $productPayPalCurrency){
		global $objDatabase;

		$objDatabase->Execute("UPDATE ".DBPREFIX."module_egov_products SET product_name='".$productName."', product_desc='".$contactFormDesc."',
				product_price='".$productFormPrice."', product_per_day='".$productFormPerDay."', product_quantity='".$productFormQuintity."',
				product_target_email='".$formEmails."', product_target_url='".$productFormTargetUrl."', product_message='".$productFormTargetMessage."', product_status='".$productState."', product_autostatus='".$productAutoStatus."', product_electro='".$productElectro."', product_file='".$productFile."', product_sender_name='".$productSenderName."', product_sender_email='".$productSenderEmail."', product_target_subject='".$productTargetSubject."', product_target_body='".$productTargetBody."', product_paypal='".$productPayPal."', product_paypal_sandbox='".$productPayPalSandbox."', product_paypal_currency='".$productPayPalCurrency."' WHERE product_id=".$formId);

		$arrFormFields = $this->getFormFields($formId);
		$arrRemoveFormFields = array_diff_assoc($arrFormFields, $arrFields);

		foreach ($arrFields as $fieldId => $arrField) {
			if (isset($arrFormFields[$fieldId])) {
				$this->_updateFormField($fieldId, $arrField['name'], $arrField['type'], $arrField['attributes'], $arrField['order_id'], $arrField['is_required'], $arrField['check_type']);
			} else {
				$this->_addFormField($formId, $arrField['name'], $arrField['type'], $arrField['attributes'], $arrField['order_id'], $arrField['is_required'], $arrField['check_type']);
			}
		}

		foreach (array_keys($arrRemoveFormFields) as $fieldId) {
			$this->_deleteFormField($fieldId);
		}
	}

	function _saveProduct($formId, $productName, $contactFormDesc, $productFormTargetUrl, $productFormTargetMessage, $productFormPerDay, $productFormQuintity, $productFormPrice, $arrFields, $formEmails, $productState, $productAutoStatus, $productElectro, $productFile, $productSenderName, $productSenderEmail, $productTargetSubject, $productTargetBody, $productPayPal , $productPayPalSandbox, $productPayPalCurrency){
		global $objDatabase;

		if ($objDatabase->Execute("INSERT INTO ".DBPREFIX."module_egov_products
								  (`product_name`, `product_desc`,`product_price`, `product_per_day`, `product_quantity`, `product_target_email`, `product_target_url`, `product_message`, `product_status`, `product_autostatus`, `product_electro`, `product_file`, `product_sender_name`, `product_sender_email`, `product_target_subject`, `product_target_body`, `product_paypal`, `product_paypal_sandbox`, `product_paypal_currency`)
								  VALUES
								  ('".$productName."', '".$contactFormDesc."', '".$productFormPrice."', '".$productFormPerDay."', '".$productFormQuintity."', '".$formEmails."', '".$productFormTargetUrl."', '".$productFormTargetMessage."', '".$productState."', '".$productAutoStatus."', '".$productElectro."', '".$productFile."', '".$productSenderName."', '".$productSenderEmail."', '".$productTargetSubject."', '".$productTargetBody."', '".$productPayPal."', '".$productPayPalSandbox."', '".$productPayPalCurrency."')") !== false) {
			$formId = $objDatabase->Insert_ID();

			foreach ($arrFields as $fieldId => $arrField) {
				$this->_addFormField($formId, $arrField['name'], $arrField['type'], $arrField['attributes'], $arrField['order_id'], $arrField['is_required'], $arrField['check_type']);
			}
		}
		$_REQUEST['formId'] = $formId;

		$this->initContactForms();

	}

	function _updateFormField($id, $name, $type, $attributes, $orderId, $isRequired, $checkType)
	{
		global $objDatabase;

		$objDatabase->Execute("UPDATE ".DBPREFIX."module_egov_product_fields SET name='".$name."', type='".$type."', attributes='".addslashes($attributes)."', is_required='".$isRequired."', check_type='".$checkType."', order_id=".$orderId." WHERE id=".$id);
	}

	function _addFormField($formId, $name, $type, $attributes, $orderId, $isRequired, $checkType)
	{
		global $objDatabase;

		$objDatabase->Execute("INSERT INTO ".DBPREFIX."module_egov_product_fields (`product`, `name`, `type`, `attributes`, `order_id`, `is_required`, `check_type`) VALUES (".$formId.", '".$name."', '".$type."', '".addslashes($attributes)."', ".$orderId.", '".$isRequired."', '".$checkType."')");
	}

	function _deleteFormField($id)
	{
		global $objDatabase;

		$objDatabase->Execute("DELETE FROM ".DBPREFIX."module_egov_product_fields WHERE id=".$id);
	}


}
?>
