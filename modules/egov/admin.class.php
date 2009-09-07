<?php

/**
 * E-Government
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_egov
 * @todo        Edit PHP DocBlocks!
 */


/*
ALTER TABLE `contrexx_module_egov_products`
ADD `product_quantity_limit` TINYINT(2) UNSIGNED NOT NULL DEFAULT '1'
AFTER `product_quantity`;

INSERT INTO contrexx_module_egov_configuration (`name`, `value`)
VALUES ('yellowpay_use_testserver', 0);

ALTER TABLE `contrexx_module_egov_products`
ADD `alternative_names` VARCHAR(255) NOT NULL DEFAULT '';
*/

/*
Changes to the database:

UPDATE `contrexx_module_shop_payment_processors`
SET `name` = 'yellowpay',
    `description` = 'Yellowpay vereinfacht das Inkasso im Online-Shop. Ihre Kunden bezahlen die Eink�ufe direkt mit dem Gelben Konto oder einer Kreditkarte. Ihr Plus: Mit den Zahlungsarten "PostFinance Debit Direct" und "PostFinance Yellownet" bieten Sie 2,4 Millionen Inhaberinnen und Inhabern eines Gelben Kontos eine kundenfreundliche und sichere Zahlungsm�glichkeit.'
WHERE `contrexx_module_shop_payment_processors`.`id`=3;

INSERT INTO `contrexx_module_shop_config` (`id`, `name`, `value`, `status`)
VALUES (NULL, 'yellowpay_accepted_payment_methods', '', '1');

CREATE TABLE `contrexx_module_egov_configuration` (
  `name` varchar(255) NOT NULL default '',
  `value` text NOT NULL default '',
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM;

(Copy the settings from the old settings table to the new configuration table)

INSERT INTO `contrexx_module_egov_configuration` (`name`, `value`)
VALUES
('yellowpay_accepted_payment_methods', ''),
('yellowpay_authorization', 'immediate'),
('yellowpay_uid', 'demo'),
('yellowpay_hashseed', 'demo'),
('yellowpay_shopid', 'demo');

ALTER TABLE `contrexx_module_egov_products`
ADD `yellowpay` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';

*/

/**
 * Includes
 */
require_once dirname(__FILE__).'/lib/eGovLibrary.class.php';
/**
 * Yellowpay payment handling
 */
require_once ASCMS_MODULE_PATH.'/shop/payments/yellowpay/Yellowpay.class.php';


/**
 * E-Government
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_egov
 */
class eGov extends eGovLibrary
{
    /**
     * Template
     * @var HTML_Template_Sigma
     */
    private $objTemplate;
    private $_arrFormFieldTypes;
    private $_strErrMessage = '';
    private $_strOkMessage = '';


    function __construct()
    {
        global $_ARRAYLANG, $objTemplate, $objInit;

        $this->_arrFormFieldTypes = array(
            'text' => $_ARRAYLANG['TXT_EGOV_TEXTBOX'],
            'label' => $_ARRAYLANG['TXT_EGOV_TEXT'],
            'checkbox' => $_ARRAYLANG['TXT_EGOV_CHECKBOX'],
            'checkboxGroup' => $_ARRAYLANG['TXT_EGOV_CHECKBOX_GROUP'],
            'hidden' => $_ARRAYLANG['TXT_EGOV_HIDDEN_FIELD'],
            'password' => $_ARRAYLANG['TXT_EGOV_PASSWORD_FIELD'],
            'radio' => $_ARRAYLANG['TXT_EGOV_RADIO_BOXES'],
            'select' => $_ARRAYLANG['TXT_EGOV_SELECTBOX'],
            'textarea' => $_ARRAYLANG['TXT_EGOV_TEXTAREA']
        );

        $this->initContactForms();

        $this->objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/egov/template');
        CSRF::add_placeholder($this->objTemplate);
        $this->objTemplate->setErrorHandling(PEAR_ERROR_DIE);

        $this->imagePath = ASCMS_MODULE_IMAGE_WEB_PATH;
        $this->langId=$objInit->userFrontendLangId;

        $objTemplate->setVariable("CONTENT_NAVIGATION","<a href='index.php?cmd=egov'>".$_ARRAYLANG['TXT_ORDERS']."</a>
                                                        <a href='index.php?cmd=egov&amp;act=products'>".$_ARRAYLANG['TXT_PRODUCTS']."</a>
                                                        <a href='index.php?cmd=egov&amp;act=settings'>".$_ARRAYLANG['TXT_SETTINGS']."</a>");
    }


    function getPage()
    {
        global $objTemplate;

        if (!isset($_GET['act'])) {
            $_GET['act']='';
        }
        switch($_GET['act']) {
            case 'save_form':
                $this->_saveForm();
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
            case 'detail':
                $this->_ProductDetail();
                break;
            case 'reservationproduct':
                $this->chooseReservationProduct();
                break;
            default:
                $this->_orders();
        }

        $this->objTemplate->setGlobalVariable('ASCMS_BACKEND_PATH', ASCMS_BACKEND_PATH);

        $objTemplate->setVariable(array(
            'CONTENT_TITLE' => $this->_pageTitle,
            'CONTENT_OK_MESSAGE' => $this->_strOkMessage,
            'CONTENT_STATUS_MESSAGE' => $this->_strErrMessage,
            'ADMIN_CONTENT' => $this->objTemplate->get()
        ));
    }


    function _product_copy() {

        global $objDatabase, $_ARRAYLANG;

        $product_id = $_REQUEST['id'];
        $product_autostatus     = eGovLibrary::GetProduktValue("product_autostatus", $product_id);
        $product_name             = eGovLibrary::GetProduktValue("product_name", $product_id)." (copy)";
        $product_desc             = eGovLibrary::GetProduktValue("product_desc", $product_id);
        $product_price             = eGovLibrary::GetProduktValue("product_price", $product_id);
        $product_per_day = eGovLibrary::GetProduktValue('product_per_day', $product_id);
        $product_quantity         = eGovLibrary::GetProduktValue("product_quantity", $product_id);
        $product_target_email     = eGovLibrary::GetProduktValue("product_target_email", $product_id);
        $product_target_url     = eGovLibrary::GetProduktValue("product_target_url", $product_id);
        $product_message         = eGovLibrary::GetProduktValue("product_message", $product_id);
        $product_status         = eGovLibrary::GetProduktValue("product_status", $product_id);
        $product_electro         = eGovLibrary::GetProduktValue("product_electro", $product_id);
        $product_file             = eGovLibrary::GetProduktValue("product_file", $product_id);
        $product_sender_name     = eGovLibrary::GetProduktValue("product_sender_name", $product_id);
        $product_sender_email     = eGovLibrary::GetProduktValue("product_target_subject", $product_id);
        $product_target_subject = eGovLibrary::GetProduktValue("product_sender_email", $product_id);
        $product_target_body    = eGovLibrary::GetProduktValue("product_target_body", $product_id);

        $arrFields = eGovLibrary::getFormFields($product_id);
        if ($objDatabase->Execute("
            INSERT INTO ".DBPREFIX."module_egov_products (
                `product_name`, `product_desc`,`product_price`,
                `product_per_day`, `product_quantity`,
                `product_target_email`, `product_target_url`,
                `product_message`, `product_status`,
                `product_autostatus`, `product_electro`, `product_file`,
                `product_sender_name`, `product_sender_email`,
                `product_target_subject`, `product_target_body`
            ) VALUES (
                '$product_name', '$product_desc', '$product_price',
                '$product_per_day', '$product_quantity',
                '$product_target_email', '$product_target_url',
                '$product_message', '$product_status',
                '$product_autostatus', '$product_electro', '$product_file',
                '$product_sender_name', '$product_sender_email',
                '$product_target_subject', '$product_target_body'
            )
        ")) {
            $ProdId = $objDatabase->Insert_ID();
            foreach ($arrFields as $arrField) {
                $this->_addFormField($ProdId, $arrField['name'], $arrField['type'], $arrField['attributes'], $arrField['order_id'], $arrField['is_required'], $arrField['check_type']);
            }
        }
        $_REQUEST['id'] = $ProdId;
        $this->_strOkMessage .= $_ARRAYLANG['TXT_EGOV_PRODUCT_SUCCESSFULLY_SAVED'];
        $this->_products();
    }


    function _settings()
    {
        global $_ARRAYLANG;

        $this->objTemplate->loadTemplateFile('module_gov_settings.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];

        // save settings
        if (   isset($_REQUEST['tpl'])
            && $_REQUEST['tpl'] == 'save') {
            if ($this->storeSettings()) {
                $this->_strOkMessage = $_ARRAYLANG['TXT_EGOV_SETTINGS_UPDATED_SUCCESSFUL'];
            } else {
                $this->_strErrMessage = $_ARRAYLANG['TXT_EGOV_SETTINGS_UPDATE_FAILED'];
            }
        }

        $currency = eGovLibrary::GetSettings('set_paypal_currency');
        $currencyMenuoptions = eGov::getCurrencyMenuoptions($currency);
        $ipnchecked =
            (eGovLibrary::GetSettings('set_paypal_ipn') == 1
                ? 'checked="checked"' : ''
            );
        $objYellowpay = new Yellowpay(
            eGovLibrary::GetSettings('yellowpay_accepted_payment_methods'),
            eGovLibrary::GetSettings('yellowpay_authorization')
        );
        $yellowpayTest = eGovLibrary::GetSettings('yellowpay_use_testserver');
        $yellowpayTestCheckedYes = ($yellowpayTest ? 'checked="checked"' : '');
        $yellowpayTestCheckedNo = ($yellowpayTest ? '' : 'checked="checked"');

        $this->objTemplate->setVariable(array(
            'TXT_EGOV_CALENDAR_COLOR_FREE' => $_ARRAYLANG['TXT_EGOV_CALENDAR_COLOR_FREE'],
            'TXT_EGOV_CALENDAR_COLOR_OCCUPIED' => $_ARRAYLANG['TXT_EGOV_CALENDAR_COLOR_OCCUPIED'],
            'TXT_EGOV_CALENDAR_COLOR_PART' => $_ARRAYLANG['TXT_EGOV_CALENDAR_COLOR_PART'],
            'TXT_EGOV_ORDERDETAILS_PLACEHOLDER' => $_ARRAYLANG['TXT_EGOV_ORDERDETAILS_PLACEHOLDER'],
            'TXT_EGOV_PAYMENTS' => $_ARRAYLANG['TXT_EGOV_PAYMENTS'],
            'TXT_EGOV_PAYPAL' => $_ARRAYLANG['TXT_EGOV_PAYPAL'],
            'TXT_EGOV_PAYPAL_CURRENCY' => $_ARRAYLANG['TXT_EGOV_PAYPAL_CURRENCY'],
            'TXT_EGOV_PAYPAL_EMAIL' => $_ARRAYLANG['TXT_EGOV_PAYPAL_EMAIL'],
            'TXT_EGOV_PAYPAL_IPN' => $_ARRAYLANG['TXT_EGOV_PAYPAL_IPN'],
            'TXT_EGOV_PLACEHOLDERS' => $_ARRAYLANG['TXT_EGOV_PLACEHOLDERS'],
            'TXT_EGOV_PRODUCTNAME_PLACEHOLDER' => $_ARRAYLANG['TXT_EGOV_PRODUCTNAME_PLACEHOLDER'],
            'TXT_EGOV_PRODUCTS_CHOICE_MENU' => $_ARRAYLANG['TXT_EGOV_PRODUCTS_CHOICE_MENU'],
            'TXT_EGOV_SANDBOX_EMAIL' => $_ARRAYLANG['TXT_EGOV_SANDBOX_EMAIL'],
            'TXT_EGOV_SENDER_EMAIL' => $_ARRAYLANG['TXT_EGOV_SENDER_EMAIL'],
            'TXT_EGOV_SENDER_NAME' => $_ARRAYLANG['TXT_EGOV_SENDER_NAME'],
            'TXT_EGOV_SETTINGS_GENERALLY' => $_ARRAYLANG['TXT_EGOV_SETTINGS_GENERALLY'],
            'TXT_EGOV_SETTINGS_LAYOUT' => $_ARRAYLANG['TXT_EGOV_SETTINGS_LAYOUT'],
            'TXT_EGOV_STANDARD_RECIPIENT' => $_ARRAYLANG['TXT_EGOV_STANDARD_RECIPIENT'],
            'TXT_EGOV_STANDARD_STATUS_CHANGE' => $_ARRAYLANG['TXT_EGOV_STANDARD_STATUS_CHANGE'],
            'TXT_EGOV_STTINGS_CALENDAR_BACKGROUND' => $_ARRAYLANG['TXT_EGOV_STTINGS_CALENDAR_BACKGROUND'],
            'TXT_EGOV_STTINGS_CALENDAR_BORDERCOLOR' => $_ARRAYLANG['TXT_EGOV_STTINGS_CALENDAR_BORDERCOLOR'],
            'TXT_EGOV_STTINGS_CALENDAR_LEGENDE_1' => $_ARRAYLANG['TXT_EGOV_STTINGS_CALENDAR_LEGENDE_1'],
            'TXT_EGOV_STTINGS_CALENDAR_LEGENDE_2' => $_ARRAYLANG['TXT_EGOV_STTINGS_CALENDAR_LEGENDE_2'],
            'TXT_EGOV_STTINGS_CALENDAR_LEGENDE_3' => $_ARRAYLANG['TXT_EGOV_STTINGS_CALENDAR_LEGENDE_3'],
            'TXT_EGOV_STTINGS_DATE_ENTRY_DESC' => $_ARRAYLANG['TXT_EGOV_STTINGS_DATE_ENTRY_DESC'],
            'TXT_EGOV_STTINGS_DATE_LABEL' => $_ARRAYLANG['TXT_EGOV_STTINGS_DATE_LABEL'],
            'TXT_EGOV_SUBJECT' => $_ARRAYLANG['TXT_EGOV_SUBJECT'],
            'TXT_EGOV_YELLOWPAY' => $_ARRAYLANG['TXT_EGOV_YELLOWPAY'],
            'TXT_EGOV_YELLOWPAY_ACCEPTED_PAYMENT_METHODS' => $_ARRAYLANG['TXT_EGOV_YELLOWPAY_ACCEPTED_PAYMENT_METHODS'],
            'TXT_EGOV_YELLOWPAY_AUTHORIZATION' => $_ARRAYLANG['TXT_EGOV_YELLOWPAY_AUTHORIZATION'],
            'TXT_EGOV_YELLOWPAY_HASHSEED' => $_ARRAYLANG['TXT_EGOV_YELLOWPAY_HASHSEED'],
            'TXT_EGOV_YELLOWPAY_UID' => $_ARRAYLANG['TXT_EGOV_YELLOWPAY_UID'],
            'TXT_EGOV_YELLOWPAY_POSTFINANCECARD' => $_ARRAYLANG['TXT_EGOV_YELLOWPAY_POSTFINANCECARD'],
            'TXT_EGOV_YELLOWPAY_YELLOWNET' => $_ARRAYLANG['TXT_EGOV_YELLOWPAY_YELLOWNET'],
            'TXT_EGOV_YELLOWPAY_MASTER' => $_ARRAYLANG['TXT_EGOV_YELLOWPAY_MASTER'],
            'TXT_EGOV_YELLOWPAY_VISA' => $_ARRAYLANG['TXT_EGOV_YELLOWPAY_VISA'],
            'TXT_EGOV_YELLOWPAY_AMEX' => $_ARRAYLANG['TXT_EGOV_YELLOWPAY_AMEX'],
            'TXT_EGOV_YELLOWPAY_DINERS' => $_ARRAYLANG['TXT_EGOV_YELLOWPAY_DINERS'],
            'TXT_EGOV_YELLOWPAY_YELLOWBILL' => $_ARRAYLANG['TXT_EGOV_YELLOWPAY_YELLOWBILL'],
            'TXT_SAVE' => $_ARRAYLANG['TXT_SAVE'],
            'CALENDER_BACKGROUND' => eGovLibrary::GetSettings("set_calendar_background"),
            'CALENDER_BORDER' => eGovLibrary::GetSettings("set_calendar_border"),
            'CALENDER_COLOR_1' => eGovLibrary::GetSettings("set_calendar_color_1"),
            'CALENDER_COLOR_2' => eGovLibrary::GetSettings("set_calendar_color_2"),
            'CALENDER_COLOR_3' => eGovLibrary::GetSettings("set_calendar_color_3"),
            'CALENDER_DATUM_DESC' => eGovLibrary::GetSettings("set_calendar_date_desc"),
            'CALENDER_DATUM_LABEL' => eGovLibrary::GetSettings("set_calendar_date_label"),
            'CALENDER_LEGENDE_1' => eGovLibrary::GetSettings("set_calendar_legende_1"),
            'CALENDER_LEGENDE_2' => eGovLibrary::GetSettings("set_calendar_legende_2"),
            'CALENDER_LEGENDE_3' => eGovLibrary::GetSettings("set_calendar_legende_3"),
            'EGOV_YELLOWPAY_UID' => eGovLibrary::GetSettings('yellowpay_uid'),
            'EGOV_YELLOWPAY_SHOPID' => eGovLibrary::GetSettings('yellowpay_shopid'),
            'TXT_EGOV_YELLOWPAY_SHOPID' => $_ARRAYLANG['TXT_EGOV_YELLOWPAY_SHOPID'],
            'EGOV_YELLOWPAY_HASHSEED' => eGovLibrary::GetSettings('yellowpay_hashseed'),
            'EGOV_YELLOWPAY_AUTHORIZATION_MENUOPTIONS' => $objYellowpay->getAuthorizationMenuoptions(),
            'EGOV_YELLOWPAY_ACCEPTED_PAYMENT_METHODS_CHECKBOXES' => $objYellowpay->getKnownPaymentMethodCheckboxes(),
            'TXT_EGOV_EMAIL_TEMPLATE' => $_ARRAYLANG['TXT_EGOV_EMAIL_TEMPLATE'],
            'TXT_EGOV_EMAIL_TEMPLATE_FOR_Admin' => $_ARRAYLANG['TXT_EGOV_EMAIL_TEMPLATE_FOR_Admin'],
            'TXT_EGOV_EMAIL_TEMPLATE_FOR_CUSTOMER' => $_ARRAYLANG['TXT_EGOV_EMAIL_TEMPLATE_FOR_CUSTOMER'],
            'TXT_EGOV_ORDER_ENTRY' => $_ARRAYLANG['TXT_EGOV_ORDER_ENTRY'],
            'TXT_EGOV_STATE_CHANGE' => $_ARRAYLANG['TXT_EGOV_STATE_CHANGE'],
            'IPN_CHECKED' => $ipnchecked,
            'ORDER_ENTRY_EMAIL' => eGovLibrary::GetSettings("set_orderentry_email"),
            'ORDER_ENTRY_RECIPIENT' => eGovLibrary::GetSettings("set_orderentry_recipient"),
            'ORDER_ENTRY_SENDER_EMAIL' => eGovLibrary::GetSettings("set_orderentry_sender"),
            'ORDER_ENTRY_SENDER_NAME' => eGovLibrary::GetSettings("set_orderentry_name"),
            'ORDER_ENTRY_SUBJECT' => eGovLibrary::GetSettings("set_orderentry_subject"),
            'PAYPAL_EMAIL' => eGovLibrary::GetSettings("set_paypal_email"),
            'SENDER_EMAIL' => eGovLibrary::GetSettings("set_sender_email"),
            'SENDER_NAME' => eGovLibrary::GetSettings("set_sender_name"),
            'STANDARD_RECIPIENT' => eGovLibrary::GetSettings("set_recipient_email"),
            'STANDARD_STATE_EMAIL' => eGovLibrary::GetSettings("set_state_email"),
            'STATE_SUBJECT' => eGovLibrary::GetSettings("set_state_subject"),
            'TXT_EGOV_GENERAL' => $_ARRAYLANG['TXT_EGOV_GENERAL'],
            'TXT_EGOV_DEFAULT_CURRENCY' => $_ARRAYLANG['TXT_EGOV_DEFAULT_CURRENCY'],
            'EGOV_CURRENCY_MENUOPTIONS' => $currencyMenuoptions,
            'TXT_EGOV_YELLOWPAY_USE_TESTSERVER' => $_ARRAYLANG['TXT_EGOV_YELLOWPAY_USE_TESTSERVER'],
            'EGOV_YELLOWPAY_USE_TESTSERVER_YES_CHECKED' => $yellowpayTestCheckedYes,
            'EGOV_YELLOWPAY_USE_TESTSERVER_NO_CHECKED' => $yellowpayTestCheckedNo,
            'TXT_EGOV_YES' => $_ARRAYLANG['TXT_EGOV_YES'],
            'TXT_EGOV_NO' => $_ARRAYLANG['TXT_EGOV_NO'],
        ));
    }


    function _product_edit()
    {
        global $_ARRAYLANG;

        $this->objTemplate->loadTemplateFile('module_gov_product_edit.html');
        if (intval($_REQUEST['id']) == 0) {
            $this->_pageTitle = $_ARRAYLANG['TXT_EGOV_ADD_NEW_PRODUCT'];
        } else {
            $this->_pageTitle = $_ARRAYLANG['TXT_EGOV_EDIT_PRODUCT'];
        }
        $product_id = intval($_REQUEST['id']);
        if ($product_id != 0) {
            $jsSubmitFunction = 'updateContentSite()';
        } else {
            $jsSubmitFunction = 'createContentSite()';
        }
        $TargetEmail = eGovLibrary::GetProduktValue('product_target_email', $product_id);
        if ($TargetEmail == '') {
            $TargetEmail = eGovLibrary::GetSettings('set_recipient_email');
        }
        $StatusChecked = 'checked="checked"';
        if ($product_id != 0) {
            if (eGovLibrary::GetProduktValue('product_status', $product_id) != 1) {
                $StatusChecked = '';
            }
        }
        $AutoJaChecked = '';
        $AutoNeinChecked = 'checked="checked"';
        if (eGovLibrary::GetProduktValue('product_autostatus', $product_id) == 1) {
            $AutoJaChecked = 'checked="checked"';
            $AutoNeinChecked = '';
        }
        $electro_checked = '';
        if (eGovLibrary::GetProduktValue('product_electro', $product_id) == 1) {
            $electro_checked = 'checked="checked"';
        }
        $ProductSenderName = eGovLibrary::GetProduktValue('product_sender_name', $product_id);
        if ($ProductSenderName == '') {
            $ProductSenderName = eGovLibrary::GetSettings('set_sender_name');
        }
        $ProductSenderEmail = eGovLibrary::GetProduktValue('product_sender_email', $product_id);
        if ($ProductSenderEmail == '') {
            $ProductSenderEmail = eGovLibrary::GetSettings('set_sender_email');
        }
        $ProductTargetSubject = eGovLibrary::GetProduktValue('product_target_subject', $product_id);
        if ($ProductTargetSubject == '') {
            $ProductTargetSubject = eGovLibrary::GetSettings('set_state_subject');
        }
        $ProductTargetBody = eGovLibrary::GetProduktValue('product_target_body', $product_id);
        if ($ProductTargetBody == '') {
            $ProductTargetBody = eGovLibrary::GetSettings('set_state_email');
        }
        $PaypayCheckedYes = (eGovLibrary::GetProduktValue('product_paypal', $product_id) == 1 ? 'checked="checked"' : '');
        $PaypayCheckedNo = ($PaypayCheckedYes == '' ? 'checked="checked"' : '');
        $currency = eGovLibrary::GetProduktValue('product_paypal_currency', $product_id);
        $paypalEmail = eGovLibrary::GetProduktValue('product_paypal_sandbox', $product_id);
        if ($paypalEmail == '') {
            $paypalEmail = eGovLibrary::GetSettings('set_paypal_email');;
        }
        if ($currency == '') {
            $currency = eGovLibrary::GetSettings('set_paypal_currency');;
        }
        $currencyMenuoptions = eGov::getCurrencyMenuoptions($currency);

        $objYellowpay = new Yellowpay(
            eGovLibrary::GetSettings('yellowpay_accepted_payment_methods'),
            eGovLibrary::GetSettings('yellowpay_authorization')
        );
        $yellowpayCheckedYes = (eGovLibrary::GetProduktValue('yellowpay', $product_id) ? 'checked="checked"' : '');
        $yellowpayCheckedNo = ($yellowpayCheckedYes == '' ? 'checked="checked"' : '');

        $this->objTemplate->setVariable(array(
            'TXT_ACTION_TITLE' => $this->_pageTitle,
            'TXT_PRODUCT_NAME' => $_ARRAYLANG['TXT_PRODUCT_NAME'],
            'TXT_EGOV_RECEIVER_ADDRESSES' => $_ARRAYLANG['TXT_EGOV_RECEIVER_ADDRESSES'],
            'TXT_EGOV_LIMITED_PER_DAY' => $_ARRAYLANG['TXT_EGOV_LIMITED_PER_DAY'],
            'TXT_EGOV_YES' => $_ARRAYLANG['TXT_EGOV_YES'],
            'TXT_EGOV_NO' => $_ARRAYLANG['TXT_EGOV_NO'],
            'TXT_EGOV_RESERVED_DAYS' => $_ARRAYLANG['TXT_EGOV_RESERVED_DAYS'],
            'TXT_EGOV_PRODUCT_QUANTITY' => $_ARRAYLANG['TXT_EGOV_PRODUCT_QUANTITY'],
            'TXT_EGOV_TARGET_URL' => $_ARRAYLANG['TXT_EGOV_TARGET_URL'],
            'TXT_EGOV_TARGET_MESSAGE' => $_ARRAYLANG['TXT_EGOV_TARGET_MESSAGE'],
            'TXT_EGOV_PRODUCT_PRICE' => $_ARRAYLANG['TXT_EGOV_PRODUCT_PRICE'],
            'TXT_EGOV_PRODUCT_DESC' => $_ARRAYLANG['TXT_EGOV_PRODUCT_DESC'],
            'TXT_EGOV_FORM_FIELDS' => $_ARRAYLANG['TXT_EGOV_FORM_FIELDS'],
            'TXT_EGOV_ADD_OTHER_FIELD' => $_ARRAYLANG['TXT_EGOV_ADD_OTHER_FIELD'],
            'TXT_EGOV_FIELD_NAME' => $_ARRAYLANG['TXT_EGOV_FIELD_NAME'],
            'TXT_EGOV_TYPE' => $_ARRAYLANG['TXT_EGOV_TYPE'],
            'TXT_EGOV_VALUE_S' => $_ARRAYLANG['TXT_EGOV_VALUE_S'],
            'TXT_EGOV_MANDATORY_FIELD' => $_ARRAYLANG['TXT_EGOV_MANDATORY_FIELD'],
            'TXT_BROWSE' => $_ARRAYLANG['TXT_BROWSE'],
            'TXT_EGOV_SEPARATE_MULTIPLE_VALUES_BY_COMMA' => $_ARRAYLANG['TXT_EGOV_SEPARATE_MULTIPLE_VALUES_BY_COMMA'],
            'PRODUCT_FORM_DESC' => get_wysiwyg_editor('contactFormDesc', eGovLibrary::GetProduktValue("product_desc", $product_id), 'shop'),
            'PRODUCT_FORM_QUANTITY' => eGovLibrary::GetProduktValue("product_quantity", $product_id),
            'PRODUCT_FORM_NAME' => eGovLibrary::GetProduktValue('product_name', $product_id),
            'PRODUCT_FORM_EMAIL' => $TargetEmail,
            'PRODUCT_FORM_TARGET_URL' => eGovLibrary::GetProduktValue("product_target_url", $product_id),
            'PRODUCT_FORM_TARGET_MESSAGE' => get_wysiwyg_editor('productFormTargetMessage', eGovLibrary::GetProduktValue("product_message", $product_id), 'shop'),
            'PRODUCT_FORM_PRICE' => eGovLibrary::GetProduktValue("product_price", $product_id),
            'PRODUCT_ID' => $product_id,
            'EGOV_JS_SUBMIT_FUNCTION' => $jsSubmitFunction,
            'TXT_SAVE' => $_ARRAYLANG['TXT_SAVE'],
            'TXT_EGOV_CONFIRM_CREATE_CONTENT_SITE' => $_ARRAYLANG['TXT_EGOV_CONFIRM_CREATE_CONTENT_SITE'],
            'TXT_EGOV_CONFIRM_UPDATE_CONTENT_SITE' => $_ARRAYLANG['TXT_EGOV_CONFIRM_UPDATE_CONTENT_SITE'],
            'TXT_STATE' => $_ARRAYLANG['TXT_STATE'],
            'STATE_CHECKED' => $StatusChecked,
            'TXT_EGOV_PRODUCT_AUTO' => $_ARRAYLANG['TXT_EGOV_PRODUCT_AUTO'],
            'AUTOSTATUS_CHECKED_YES' => $AutoJaChecked,
            'AUTOSTATUS_CHECKED_NO' => $AutoNeinChecked,
            'TXT_EGOV_PRODUCT_ELECTRO' => $_ARRAYLANG['TXT_EGOV_PRODUCT_ELECTRO'],
            'ELECTRO_CHECKED' => $electro_checked,
            'TXT_EGOV_PRODUCT_SELECT_FILE' => $_ARRAYLANG['TXT_EGOV_PRODUCT_SELECT_FILE'],
            'PRODUCT_FORM_FILE' => eGovLibrary::GetProduktValue("product_file", $product_id),
            'TXT_EGOV_ORDER_STATE_AUTOMAIL' => $_ARRAYLANG["TXT_EGOV_ORDER_STATE_AUTOMAIL"],
            'TXT_EGOV_BASIC_DATA' => $_ARRAYLANG["TXT_EGOV_BASIC_DATA"],
            'TXT_EGOV_EXTENDED_OPTIONS' => $_ARRAYLANG["TXT_EGOV_EXTENDED_OPTIONS"],
            'TXT_EGOV_SENDER_NAME' => $_ARRAYLANG['TXT_EGOV_SENDER_NAME'],
            'TXT_EGOV_SENDER_EMAIL' => $_ARRAYLANG['TXT_EGOV_SENDER_EMAIL'],
            'PRODUCT_SENDER_NAME' => $ProductSenderName,
            'PRODUCT_SENDER_EMAIL' => $ProductSenderEmail,
            'TXT_EGOV_EMAIL_TEMPLATE_FOR_CUSTOMER' => $_ARRAYLANG['TXT_EGOV_EMAIL_TEMPLATE_FOR_CUSTOMER'],
            'TXT_EGOV_SUBJECT' => $_ARRAYLANG['TXT_EGOV_SUBJECT'],
            'TXT_EGOV_EMAIL_TEMPLATE' => $_ARRAYLANG['TXT_EGOV_EMAIL_TEMPLATE'],
            'PRODUCT_TARGET_SUBJECT' => $ProductTargetSubject,
            'PRODUCT_TARGET_BODY' => $ProductTargetBody,
            'TXT_EGOV_PRODUCTNAME_PLACEHOLDER' => $_ARRAYLANG['TXT_EGOV_PRODUCTNAME_PLACEHOLDER'],
            'TXT_EGOV_ORDERDETAILS_PLACEHOLDER' => $_ARRAYLANG['TXT_EGOV_ORDERDETAILS_PLACEHOLDER'],
            'PAYPAL_YES' => $PaypayCheckedYes,
            'PAYPAL_NO' => $PaypayCheckedNo,
            'TXT_EGOV_ACTIVATE_PAYPAL' => $_ARRAYLANG['TXT_EGOV_ACTIVATE_PAYPAL'],
            'TXT_EGOV_SANDBOX_EMAIL' => $_ARRAYLANG['TXT_EGOV_SANDBOX_EMAIL'],
            'SANDBOX_EMAIL' => $paypalEmail,
            'TXT_EGOV_PAYMENTS' => $_ARRAYLANG['TXT_EGOV_PAYMENTS'],
            'TXT_EGOV_PAYPAL_CURRENCY' => $_ARRAYLANG['TXT_EGOV_PAYPAL_CURRENCY'],
            'TXT_EGOV_PLACEHOLDERS' => $_ARRAYLANG['TXT_EGOV_PLACEHOLDERS'],
            'TXT_EGOV_PAYPAL' => $_ARRAYLANG['TXT_EGOV_PAYPAL'],
            'TXT_EGOV_YELLOWPAY' => $_ARRAYLANG['TXT_EGOV_YELLOWPAY'],
            'TXT_EGOV_YELLOWPAY_UID' => $_ARRAYLANG['TXT_EGOV_YELLOWPAY_UID'],
            'TXT_EGOV_YELLOWPAY_HASHSEED' => $_ARRAYLANG['TXT_EGOV_YELLOWPAY_HASHSEED'],
            'TXT_EGOV_YELLOWPAY_AUTHORIZATION' => $_ARRAYLANG['TXT_EGOV_YELLOWPAY_AUTHORIZATION'],
            'TXT_EGOV_YELLOWPAY_ACCEPTED_PAYMENT_METHODS' => $_ARRAYLANG['TXT_EGOV_YELLOWPAY_ACCEPTED_PAYMENT_METHODS'],
            'TXT_EGOV_ACTIVATE_YELLOWPAY' => $_ARRAYLANG['TXT_EGOV_ACTIVATE_YELLOWPAY'],
            'EGOV_YELLOWPAY_UID' => eGovLibrary::GetSettings('yellowpay_uid'),
            'EGOV_YELLOWPAY_HASHSEED' => eGovLibrary::GetSettings('yellowpay_hashseed'),
            'EGOV_YELLOWPAY_SHOPID' => eGovLibrary::GetSettings('yellowpay_shopid'),
            'TXT_EGOV_YELLOWPAY_SHOPID' => $_ARRAYLANG['TXT_EGOV_YELLOWPAY_SHOPID'],
            'EGOV_YELLOWPAY_AUTHORIZATION_MENUOPTIONS' => $objYellowpay->getAuthorizationMenuoptions(),
            'EGOV_YELLOWPAY_ACCEPTED_PAYMENT_METHODS_CHECKBOXES' => $objYellowpay->getKnownPaymentMethodCheckboxes(),
            'TXT_EGOV_ACTIVATE_YELLOWPAY' => $_ARRAYLANG['TXT_EGOV_ACTIVATE_YELLOWPAY'],
            'YELLOWPAY_CHECKED_YES' => $yellowpayCheckedYes,
            'YELLOWPAY_CHECKED_NO' => $yellowpayCheckedNo,
            'TXT_EGOV_GENERAL' => $_ARRAYLANG['TXT_EGOV_GENERAL'],
            'TXT_EGOV_DEFAULT_CURRENCY' => $_ARRAYLANG['TXT_EGOV_DEFAULT_CURRENCY'],
            'EGOV_CURRENCY_MENUOPTIONS' => $currencyMenuoptions,
            'EGOV_PRODUCT_QUANTITY_LIMIT' => eGovLibrary::GetProduktValue('product_quantity_limit', $product_id),
            'TXT_EGOV_PRODUCT_QUANTITY_LIMIT' => $_ARRAYLANG['TXT_EGOV_PRODUCT_QUANTITY_LIMIT'],
            // Alternative payment methods, comma separated
            'TXT_EGOV_ALTERNATIVE_PAYMENT_METHODS' => $_ARRAYLANG['TXT_EGOV_ALTERNATIVE_PAYMENT_METHODS'],
            'TXT_EGOV_ALTERNATIVE_PAYMENT_NAMES' => $_ARRAYLANG['TXT_EGOV_ALTERNATIVE_PAYMENT_NAMES'],
            'ALTERNATIVE_NAMES' => eGovLibrary::GetProduktValue('alternative_names', $product_id),
        ));

        if (eGovLibrary::GetProduktValue('product_per_day', $product_id) == 'yes') {
            $this->objTemplate->setVariable(
                'PER_DAY_CHECKED_YES', 'checked="checked"'
            );
        } else {
            $this->objTemplate->setVariable(
                'PER_DAY_CHECKED_NO', 'checked="checked"'
            );
        }

        $lastFieldId = 0;
        if ($product_id != 0) {
            $arrFields = eGovLibrary::getFormFields($product_id);
        } else {
            $this->objTemplate->setVariable(array(
                'EGOV_FORM_FIELD_NAME' => '',
                'EGOV_FORM_FIELD_ID' => 1,
                'EGOV_FORM_FIELD_TYPE_MENU' => $this->_getFormFieldTypesMenu('contactFormFieldType[1]', 'text', 'id="contactFormFieldType_1" onchange="setFormFieldAttributeBox(this.getAttribute(\'id\'), this.value)"'),
                'EGOV_FORM_FIELD_CHECK_MENU' => $this->_getFormFieldCheckTypesMenu('contactFormFieldCheckType[1]', 'contactFormFieldCheckType_1', 'text', 1),
                'EGOV_FORM_FIELD_CHECK_BOX' => $this->_getFormFieldRequiredCheckBox('contactFormFieldRequired[1]', 'contactFormFieldRequired_1', 'text', false),
                'EGOV_FORM_FIELD_ATTRIBUTES' => $this->_getFormFieldAttribute(1, 'text', '')
            ));
            $this->objTemplate->parse('egov_form_field_list');
            $lastFieldId = 1;
        }
        if (isset($arrFields) && is_array($arrFields)) {
            foreach ($arrFields as $fieldId => $arrField) {
                $this->objTemplate->setVariable(array(
                    'EGOV_FORM_FIELD_NAME' => $arrField['name'],
                    'EGOV_FORM_FIELD_ID' => $fieldId,
                    'EGOV_FORM_FIELD_TYPE_MENU' => $this->_getFormFieldTypesMenu(
                            'contactFormFieldType['.$fieldId.']',
                            $arrField['type'],
                            'id="contactFormFieldType_'.$fieldId.
                              '" onchange="setFormFieldAttributeBox(this.getAttribute(\'id\'), this.value)"'
                        ),
                    'EGOV_FORM_FIELD_CHECK_MENU' => $this->_getFormFieldCheckTypesMenu(
                            'contactFormFieldCheckType['.$fieldId.']',
                            'contactFormFieldCheckType_'.$fieldId,
                            $arrField['type'],
                            $arrField['check_type']
                        ),
                    'EGOV_FORM_FIELD_CHECK_BOX' => $this->_getFormFieldRequiredCheckBox(
                            'contactFormFieldRequired['.$fieldId.']',
                            'contactFormFieldRequired_'.$fieldId,
                            $arrField['type'],
                            ($arrField['is_required'] == 1 ? true : false)
                        ),
                    'EGOV_FORM_FIELD_ATTRIBUTES' => $this->_getFormFieldAttribute(
                            $fieldId, $arrField['type'], $arrField['attributes']
                        ),
                ));
                $this->objTemplate->parse('egov_form_field_list');
                $lastFieldId =
                    ($fieldId > $lastFieldId ? $fieldId : $lastFieldId);
            }
        }

        $this->objTemplate->setVariable(array(
            'CONTACT_FORM_FIELD_NEXT_ID' => $lastFieldId+1,
            'CONTACT_FORM_FIELD_NEXT_TEXT_TPL' => $this->_getFormFieldAttribute($lastFieldId+1, 'text', ''),
            'CONTACT_FORM_FIELD_LABEL_TPL' => $this->_getFormFieldAttribute($lastFieldId+1, 'label', ''),
            'CONTACT_FORM_FIELD_CHECK_MENU_NEXT_TPL' => $this->_getFormFieldCheckTypesMenu('contactFormFieldCheckType['.($lastFieldId+1).']', 'contactFormFieldCheckType_'.($lastFieldId+1), 'text', 1),
            'CONTACT_FORM_FIELD_CHECK_MENU_TPL' => $this->_getFormFieldCheckTypesMenu('contactFormFieldCheckType[0]', 'contactFormFieldCheckType_0', 'text', 1),
            'CONTACT_FORM_FIELD_CHECK_BOX_NEXT_TPL' => $this->_getFormFieldRequiredCheckBox('contactFormFieldRequired['.($lastFieldId+1).']', 'contactFormFieldRequired_'.($lastFieldId+1), 'text', false),
            'CONTACT_FORM_FIELD_CHECK_BOX_TPL' => $this->_getFormFieldRequiredCheckBox('contactFormFieldRequired[0]', 'contactFormFieldRequired_0', 'text', false),
            'CONTACT_FORM_FIELD_TYPE_MENU_TPL' => $this->_getFormFieldTypesMenu('contactFormFieldType['.($lastFieldId+1).']', key($this->_arrFormFieldTypes), 'id="contactFormFieldType_'.($lastFieldId+1).'" onchange="setFormFieldAttributeBox(this.getAttribute(\'id\'), this.value)"'),
            'CONTACT_FORM_FIELD_TEXT_TPL' => $this->_getFormFieldAttribute(0, 'text', ''),
            'CONTACT_FORM_FIELD_CHECKBOX_TPL' => $this->_getFormFieldAttribute(0, 'checkbox', 0),
            'CONTACT_FORM_FIELD_CHECKBOX_GROUP_TPL' => $this->_getFormFieldAttribute(0, 'checkboxGroup', ''),
            'CONTACT_FORM_FIELD_HIDDEN_TPL' => $this->_getFormFieldAttribute(0, 'hidden', ''),
            'CONTACT_FORM_FIELD_RADIO_TPL' => $this->_getFormFieldAttribute(0, 'radio', ''),
            'CONTACT_FORM_FIELD_SELECT_TPL' => $this->_getFormFieldAttribute(0, 'select', ''),
            'CONTACT_JS_SUBMIT_FUNCTION' => $jsSubmitFunction
        ));
    }


    function _products($SaveError='')
    {
        global $objDatabase, $_ARRAYLANG;

        $this->objTemplate->loadTemplateFile('module_gov_products.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_EGOV_EDIT_PRODUCT'];

        // delete
        if (isset($_REQUEST['delete'])) {
            $objDatabase->Execute("
                DELETE FROM ".DBPREFIX."module_egov_products
                 WHERE product_id=".$_REQUEST['id']
            );
            $objDatabase->Execute("
                DELETE FROM ".DBPREFIX."module_egov_product_fields
                 WHERE product=".$_REQUEST['id']
            );
        }

        // save product
        if (isset($_REQUEST['tpl']) && $_REQUEST['tpl'] == 'save') {
            switch ($SaveError) {
              case '':
                $this->_strOkMessage .= $_ARRAYLANG['TXT_EGOV_PRODUCT_SUCCESSFULLY_SAVED'];
                break;
              case 1:
                $this->_strErrMessage .= $_ARRAYLANG['TXT_EGOV_FORM_FIELD_UNIQUE_MSG'];
                break;
              case 2:
                $this->_strErrMessage .= $_ARRAYLANG['TXT_EGOV_FILE_ERROR'];
                break;
            }
        }

        // Position
        if (isset($_REQUEST['Direction'])) {
            $query = "
                SELECT count(*) AS anzahl
                  FROM ".DBPREFIX."module_egov_products
            ";
            $objResult = $objDatabase->Execute($query);
            if ($objResult->RecordCount() == 1) {
                $anzahl = $objResult->fields['anzahl'];
            }
            if ($_REQUEST['Direction']=='up') {
                $NewPosition = eGovLibrary::GetProduktValue('product_orderby', $_REQUEST['id'])-1;
            }
            if ($_REQUEST['Direction']=='down') {
                $NewPosition = eGovLibrary::GetProduktValue('product_orderby', $_REQUEST['id'])+1;
            }
            if ($NewPosition < 0) {
                $NewPosition = 0;
            }
            if ($NewPosition > $anzahl) {
                $NewPosition = $anzahl;
            }

            $query = "
                SELECT product_id
                  FROM ".DBPREFIX."module_egov_products
                 WHERE product_orderby=$NewPosition
            ";
            $objResult = $objDatabase->Execute($query);
            if ($objResult->RecordCount() == 1) {
                $TauschID = $objResult->fields['product_id'];
            }
            $query = "
                SELECT product_orderby
                  FROM ".DBPREFIX."module_egov_products
                 WHERE product_id=".$_REQUEST['id'];
            $objResult = $objDatabase->Execute($query);
            if ($objResult->RecordCount() == 1) {
                $TauschPosition = $objResult->fields['product_orderby'];
            }
            $query = "
                UPDATE ".DBPREFIX."module_egov_products
                   SET product_orderby=".$TauschPosition."
                 WHERE product_id=$TauschID
            ";
            if ($objDatabase->Execute($query)) {
                $this->_strOkMessage = $_ARRAYLANG['TXT_EGOV_PRODUCT_SUCCESSFULLY_SAVED'];
            }
            $query = "
                UPDATE ".DBPREFIX."module_egov_products
                   SET product_orderby=$NewPosition
                 WHERE product_id=".$_REQUEST['id'];
            if ($objDatabase->Execute($query)) {
                $this->_strOkMessage = $_ARRAYLANG['TXT_EGOV_PRODUCT_SUCCESSFULLY_SAVED'];
            }
        }
        $this->objTemplate->setVariable(array(
            'TXT_PRODUCTS' => $_ARRAYLANG['TXT_PRODUCTS'],
            'TXT_PRODUCT' => $_ARRAYLANG['TXT_PRODUCT'],
            'TXT_MARKED' => $_ARRAYLANG['TXT_MARKED'],
            'TXT_SELECT_ALL' => $_ARRAYLANG['TXT_SELECT_ALL'],
            'TXT_DESELECT_ALL' => $_ARRAYLANG['TXT_DESELECT_ALL'],
            'TXT_SUBMIT_SELECT' => $_ARRAYLANG['TXT_SUBMIT_SELECT'],
            'TXT_SUBMIT_DELETE' => $_ARRAYLANG['TXT_SUBMIT_DELETE'],
            'TXT_IMGALT_EDIT' => $_ARRAYLANG['TXT_IMGALT_EDIT'],
            'TXT_IMGALT_DELETE' => $_ARRAYLANG['TXT_IMGALT_DELETE'],
            'TXT_DELETE_PRODUCT' => $_ARRAYLANG['TXT_DELETE_PRODUCT'],
            'TXT_EGOV_ADD_NEW_PRODUCT' => $_ARRAYLANG['TXT_EGOV_ADD_NEW_PRODUCT'],
            'TXT_EGOV_RESERVATIONS' => $_ARRAYLANG['TXT_EGOV_RESERVATIONS'],
            'TXT_ORDERS' => $_ARRAYLANG['TXT_ORDERS'],
            'TXT_FUNCTIONS' => $_ARRAYLANG['TXT_FUNCTIONS'],
            'TXT_EGOV_SEQUENCE' => $_ARRAYLANG['TXT_EGOV_SEQUENCE'],
            'TXT_EGOV_UP' => $_ARRAYLANG['TXT_EGOV_UP'],
            'TXT_EGOV_DOWN' => $_ARRAYLANG['TXT_EGOV_DOWN'],
            'TXT_EGOV_RESERVATION' => $_ARRAYLANG['TXT_EGOV_RESERVATION'],
        ));

        $query = "
            SELECT *
              FROM ".DBPREFIX."module_egov_products
             ORDER BY product_orderby, product_name
        ";
        $objResult = $objDatabase->Execute($query);
        $i = 0;
        while(!$objResult->EOF) {
            $StatusImg = '<img src="images/icons/status_green.gif" width="10" height="10" border="0" alt="" />';
            if ($objResult->fields["product_status"]!=1) {
                $StatusImg = '<img src="images/icons/status_red.gif" width="10" height="10" border="0" alt="" />';
            }

            $query_orders = "
                SELECT count(*) as anzahl
                  FROM ".DBPREFIX."module_egov_orders
                 WHERE order_product=".$objResult->fields['product_id'];
            $objResult_orders = $objDatabase->Execute($query_orders);

            $this->objTemplate->setVariable(array(
                'ROWCLASS' => (++$i % 2 ? 'row2' : 'row1'),
                'PRODUCT_ID' => $objResult->fields['product_id'],
                'PRODUCT_NAME' => $objResult->fields['product_name'],
                'PRODUCT_STATUS' => $StatusImg,
                'PRODUCT_POSITION' => $objResult->fields['product_orderby'],
                'TXT_EDIT' => $_ARRAYLANG['TXT_EDIT'],
                'TXT_DELETE' => $_ARRAYLANG['TXT_DELETE'],
                'TXT_EGOV_SOURCECODE' => $_ARRAYLANG['TXT_EGOV_SOURCECODE'],
                'ORDERS_VALUE' => $objResult_orders->fields['anzahl'],
                'TXT_EGOV_VIEW_ORDERS' => $_ARRAYLANG['TXT_EGOV_VIEW_ORDERS'],
                'TXT_COPY' => $_ARRAYLANG['TXT_COPY'],
                'TXT_EGOV_UP' => $_ARRAYLANG['TXT_EGOV_UP'],
                'TXT_EGOV_DOWN' => $_ARRAYLANG['TXT_EGOV_DOWN'],
            ));

            $product_id = $objResult->fields['product_id'];
            if (eGovLibrary::GetProduktValue('product_per_day', $product_id) == 'yes') {

                $LastYear = date('Y')-1;
                $query_rl = "
                    SELECT *
                      FROM ".DBPREFIX."module_egov_product_calendar
                     WHERE calendar_product=".$product_id." and calendar_year>".$LastYear."
                     GROUP BY calendar_day, calendar_month, calendar_year
                     ORDER BY calendar_year, calendar_month, calendar_day
                ";
                $objResult_rl = $objDatabase->Execute($query_rl);
                $counter = 0;
                $optionContent = '';
                $ProductQuant = eGovLibrary::GetProduktValue('product_quantity', $product_id);
                while(!$objResult_rl->EOF) {
                    $query_count = "
                        SELECT count(*) as anzahl
                          FROM ".DBPREFIX."module_egov_product_calendar
                         WHERE calendar_product=$product_id
                           AND calendar_day=".$objResult_rl->fields['calendar_day']."
                           AND calendar_month=".$objResult_rl->fields['calendar_month']."
                           AND calendar_year=".$objResult_rl->fields['calendar_year']."
                           AND calendar_act=1
                    ";
                    $objResult_count = $objDatabase->Execute($query_count);
                    $ReservedQuantity =
                        '('.$objResult_count->fields['anzahl'].'/'.$ProductQuant.')';
                    $optionContent .=
                        '<option value="'.$objResult_rl->fields['calendar_id'].'">'.
                        $objResult_rl->fields['calendar_day'].'.'.
                        $objResult_rl->fields['calendar_month'].'.'.
                        $objResult_rl->fields['calendar_year'].' '.
                        $ReservedQuantity.'</option>';
                    ++$counter;
                    $objResult_rl->MoveNext();
                }

                if ($counter == 0) {
                    $this->objTemplate->setVariable(
                        'RESERVATIONS_VALUE', ''
                    );
                } else {
                    $this->objTemplate->setVariable(
                        'RESERVATIONS_VALUE',
                        '<select name="ReservedDays_'.$product_id.
                        '" style="width: 150px;">'.$optionContent.'</select>'
                    );
                }
            }
            $this->objTemplate->parse('products_list');
            $objResult->MoveNext();
        }
        if ($i == 0) {
            $this->objTemplate->hideBlock('products_list');
        }
    }


    function _order_edit()
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        $this->objTemplate->loadTemplateFile('module_gov_order_edit.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_ORDER_EDIT'];
        $order_id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : 0);
        $productId = eGovLibrary::GetOrderValue('order_product', $order_id);

        if (isset($_REQUEST['update'])) {
            $query = "
                UPDATE ".DBPREFIX."module_egov_orders
                   SET order_state=".$_REQUEST['state']."
                 WHERE order_id=$order_id
            ";
            if ($objDatabase->Execute($query)) {
                $this->_strOkMessage = $_ARRAYLANG['TXT_STATE_UPDATED_SUCCESSFUL'];
            }

            $query = "
                SELECT *
                  FROM ".DBPREFIX."module_egov_orders
                 WHERE order_id=$order_id
            ";
            $objResult = $objDatabase->Execute($query);
            if (eGovLibrary::GetProduktValue('product_per_day', $productId) == 'yes') {
                if (   intval($_REQUEST['state']) == 2
                    || intval($_REQUEST['state']) == 0) {
                    $act = 0;
                } else {
                    $act = 1;
                }
                $query = "UPDATE ".DBPREFIX."module_egov_product_calendar
                     SET calendar_act=$act
                     WHERE calendar_order=$order_id
                ";
                $objDatabase->Execute($query);
            }

            if (isset($_REQUEST['ChangeStateMessage'])) {
                $SubjectText = str_replace(
                    '[[PRODUCT_NAME]]',
                    html_entity_decode(eGovLibrary::GetProduktValue('product_name', $productId)),
                    $_REQUEST['email_subject']
                );
                $SubjectText = html_entity_decode($SubjectText);

                $FormValue4Mail = '';
                $GSdata = split(";;", $objResult->fields['order_values']);
                for ($y = 0; $y < count($GSdata); ++$y) {
                    if (!empty($GSdata[$y])) {
                        list ($FieldName, $FieldValue) = split('::', $GSdata[$y]);
                        if ($FieldName != '') {
                            $FormValue4Mail .= $FieldName.': '.$FieldValue;
                        }
                    }
                }

                $BodyText = str_replace(
                    '[[ORDER_VALUE]]', $FormValue4Mail, $_REQUEST['email_text']
                );
                $BodyText = str_replace(
                    '[[PRODUCT_NAME]]',
                    html_entity_decode(eGovLibrary::GetProduktValue('product_name', $productId)),
                    $BodyText
                );
                    $BodyText = html_entity_decode($BodyText);

                $FromEmail = eGovLibrary::GetProduktValue('product_sender_email', $productId);
                if ($FromEmail == '') {
                    $FromEmail = eGovLibrary::GetSettings('set_sender_email');
                }

                $FromName = eGovLibrary::GetProduktValue('product_sender_name', $productId);
                if ($FromName == '') {
                    $FromName = eGovLibrary::GetSettings('set_sender_name');
                }

                $TargetMail = $_REQUEST['email'];
                if ($TargetMail == '') {
                    $TargetMail = eGovLibrary::GetEmailAdress($order_id);
                }
                if ($TargetMail != '') {
                    if (@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') {
                        $objMail = new phpmailer();
                        if ($_CONFIG['coreSmtpServer'] > 0 && @include_once ASCMS_CORE_PATH.'/SmtpSettings.class.php') {
                            if (($arrSmtp = SmtpSettings::getSmtpAccount($_CONFIG['coreSmtpServer'])) !== false) {
                                $objMail->IsSMTP();
                                $objMail->Host = $arrSmtp['hostname'];
                                $objMail->Port = $arrSmtp['port'];
                                $objMail->SMTPAuth = true;
                                $objMail->Username = $arrSmtp['username'];
                                $objMail->Password = $arrSmtp['password'];
                            }
                        }
                        $objMail->CharSet = CONTREXX_CHARSET;
                        $objMail->From = $FromEmail;
                        $objMail->FromName = $FromName;
                        $objMail->AddReplyTo($FromEmail);
                        $objMail->Subject = $SubjectText;
                        $objMail->Priority = 3;
                        $objMail->IsHTML(false);
                        $objMail->Body = $BodyText;
                        $objMail->AddAddress($TargetMail);
// TODO: Verify the result and show an error if sending the mail fails!
                        $objMail->Send();
                    }
                }
            }
        }

        $query = "
            SELECT *
              FROM ".DBPREFIX."module_egov_orders
             WHERE order_id=".intval($_REQUEST['id']);
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->RecordCount() != 1) {
            CSRF::header('Location: index.php?cmd=egov&err=Wrong Order ID');
            exit;
        }

        $mailBody = eGovLibrary::GetProduktValue('product_target_body', $productId);
        $this->objTemplate->setVariable(array(
            'TXT_DATE' => $_ARRAYLANG['TXT_DATE'],
            'TXT_STATE' => $_ARRAYLANG['TXT_STATE'],
            'TXT_FUNCTIONS' => $_ARRAYLANG['TXT_FUNCTIONS'],
            'TXT_PRODUCT' => $_ARRAYLANG['TXT_PRODUCT'],
            'TXT_ORDER' => $_ARRAYLANG['TXT_ORDER'],
            'TXT_IP_ADDRESS' => $_ARRAYLANG['TXT_IP_ADDRESS'],
            'TXT_DATA' => $_ARRAYLANG['TXT_DATA'],
            'TXT_STATE_NEW' => $_ARRAYLANG['TXT_STATE_NEW'],
            'TXT_STATE_OK' => $_ARRAYLANG['TXT_STATE_OK'],
            'TXT_STATE_DELETED' => $_ARRAYLANG['TXT_STATE_DELETED'],
            'TXT_SEND_STATE_CHANGE_EMAIL' => $_ARRAYLANG['TXT_SEND_STATE_CHANGE_EMAIL'],
            'TXT_CHANGE_STATE' => $_ARRAYLANG['TXT_CHANGE_STATE'],
            'TXT_EMAIL_TEXT' => $_ARRAYLANG['TXT_EMAIL_TEXT'],
            'TXT_EMAIL_ADDRESS' => $_ARRAYLANG['TXT_EMAIL_ADDRESS'],
            'TXT_SAVE_AND_SEND' => $_ARRAYLANG['TXT_SAVE_AND_SEND'],
            'TXT_SAVE' => $_ARRAYLANG['TXT_SAVE'],
            'TXT_SAVE_WITHOUT_EMAIL' => $_ARRAYLANG['TXT_SAVE_WITHOUT_EMAIL'],
            'TXT_SVE_WITH_EMAIL' => $_ARRAYLANG['TXT_SVE_WITH_EMAIL'],
            'TXT_EMPTY_EMAIL' => $_ARRAYLANG['TXT_EMPTY_EMAIL'],
            'SETTINGS_STATE_CHANGE_EMAIL' => $mailBody,
            'ORDER_ID' => $objResult->fields["order_id"],
            'ORDER_IP' => $objResult->fields["order_ip"],
            'ORDER_DATE' => $objResult->fields["order_date"],
            'ORDER_PRODUCT' => eGovLibrary::GetProduktValue('product_name', $objResult->fields['order_product']),
            'TXT_EGOV_SUBJECT' => $_ARRAYLANG['TXT_EGOV_SUBJECT'],
            'STATE_SUBJECT' => eGovLibrary::GetSettings("set_state_subject"),
            'EGOV_TARGET_EMAIL' => eGovLibrary::GetEmailAdress($order_id),
            'EGOV_ORDER_STATUS_MENUOPTIONS' =>
                eGov::getStatusMenuOptions($objResult->fields['order_state']),
//            'SELECTED_STATE_OK' => $selected_ok,
//            'SELECTED_STATE_DELETED' => $selected_deleted,
        ));

        // form falues
        $GSdata = split(';;', $objResult->fields['order_values']);
        $y = 0;
        foreach ($GSdata as $value) {
            if (empty($value)) continue;
            list ($FieldName, $FieldValue) = split('::', $value);
            $this->objTemplate->setVariable(array(
                'ROWCLASS' => (++$y % 2 ? 'row2' : 'row1'),
                'DATA_FIELD' => $FieldName,
                'DATA_VALUE' => $FieldValue,
            ));
            $this->objTemplate->parse('orders_data_row');
        }
    }


    function _orders()
    {
        global $objDatabase, $_ARRAYLANG;

        $this->objTemplate->loadTemplateFile('module_gov_orders_overview.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_ORDERS'];

        if (isset($_REQUEST['err'])) {
            $this->_strErrMessage = $_REQUEST['err'];
        }

        // delete orders
        if (isset($_REQUEST['delete'])) {
            if (isset($_REQUEST['multi'])) {
                if (is_array($_POST['selectedOrderId'])) {
                    foreach ($_POST['selectedOrderId'] as $GSOrderID) {
                        $objDatabase->Execute("
                            DELETE
                              FROM ".DBPREFIX."module_egov_orders
                             WHERE order_id=".intval($GSOrderID)
                        );
                        $objDatabase->Execute("
                            DELETE
                              FROM ".DBPREFIX."module_egov_product_calendar
                             WHERE calendar_order=".intval($GSOrderID)
                        );
                    }
                }
            } else {
                $objDatabase->Execute("
                    DELETE
                      FROM ".DBPREFIX."module_egov_orders
                     WHERE order_id=".intval($_REQUEST['id'])
                );
                $objDatabase->Execute("
                    DELETE
                      FROM ".DBPREFIX."module_egov_product_calendar
                     WHERE calendar_order=".intval($_REQUEST['id'])
                );
            }
        }
        $this->objTemplate->setVariable(array(
            'TXT_DATE' => $_ARRAYLANG['TXT_DATE'],
            'TXT_STATE' => $_ARRAYLANG['TXT_STATE'],
            'TXT_FUNCTIONS' => $_ARRAYLANG['TXT_FUNCTIONS'],
            'TXT_NAME' => $_ARRAYLANG['TXT_NAME'],
            'TXT_PRODUCT' => $_ARRAYLANG['TXT_PRODUCT'],
            'TXT_SELECT_ALL' => $_ARRAYLANG['TXT_SELECT_ALL'],
            'TXT_DESELECT_ALL' => $_ARRAYLANG['TXT_DESELECT_ALL'],
            'TXT_SUBMIT_SELECT' => $_ARRAYLANG['TXT_SUBMIT_SELECT'],
            'TXT_SUBMIT_DELETE' => $_ARRAYLANG['TXT_SUBMIT_DELETE'],
            'TXT_JS_DELETE_ALL_ORDERS' => $_ARRAYLANG['TXT_JS_DELETE_ALL_ORDERS'],
            'TXT_DELETE_ORDER' => $_ARRAYLANG['TXT_DELETE_ORDER'],
            'TXT_ORDERS' => $_ARRAYLANG['TXT_ORDERS'],
            'TXT_IP_ADDRESS' => $_ARRAYLANG['TXT_IP_ADDRESS'],
            'TXT_EGOV_ADD_RESERVATION' => $_ARRAYLANG['TXT_EGOV_ADD_RESERVATION'],
        ));

        $query = "
            SELECT *
              FROM ".DBPREFIX."module_egov_orders
             ".(!empty($_REQUEST['product'])
                ? 'WHERE order_product='.$_REQUEST["product"] : ''
               )."
             ORDER BY order_id DESC
        ";
        $objResult = $objDatabase->Execute($query);
        $i = 0;
        while(!$objResult->EOF) {
            switch ($objResult->fields['order_state']) {
                case 0:
                    $stateImg = 'status_yellow.gif';
                break;
                case 1:
                    $stateImg = 'status_green.gif';
                break;
                case 2:
                    $stateImg = 'status_red.gif';
                break;
                case 3:
                    $stateImg = 'status_yellow.gif';
            }
            $this->objTemplate->setVariable(array(
                'ORDERS_ROWCLASS' => (++$i % 2 ? 'row2' : 'row1'),
                'ORDER_ID' => $objResult->fields['order_id'],
                'ORDER_DATE' => $objResult->fields['order_date'],
                'ORDER_ID' => $objResult->fields['order_id'],
                'ORDER_STATE' => eGovLibrary::MaskState($objResult->fields['order_state']),
                'ORDER_PRODUCT' => eGovLibrary::GetProduktValue('product_name', $objResult->fields['order_product']),
                'ORDER_NAME' =>
                    $this->ParseFormValues('Vorname', $objResult->fields['order_values']).
                    ' '.
                    $this->ParseFormValues('Name', $objResult->fields['order_values']),
                'TXT_EDIT' => $_ARRAYLANG['TXT_EDIT'],
                'TXT_DELETE' => $_ARRAYLANG['TXT_DELETE'],
                'ORDER_STATE_IMG' => $stateImg,
                'ORDER_IP' => $objResult->fields['order_ip'],
            ));
            $this->objTemplate->parse('orders_row');
            $objResult->MoveNext();
        }
        if ($i == 0) {
            $this->objTemplate->hideBlock('orders_row');
        }
    }


    function _getFormFieldTypesMenu($name, $selectedType, $attrs = '')
    {
        $menu = '<select name="'.$name.'" '.$attrs.">\n";
        foreach ($this->_arrFormFieldTypes as $type => $desc) {
            $menu .=
                '<option value="'.$type.'"'.
                ($selectedType == $type ? ' selected="selected"' : '').
                '>'.$desc."</option>\n";
        }
        $menu .= "</select>\n";
        return $menu;
    }


    function _getFormFieldCheckTypesMenu($name, $id,  $type, $selected)
    {
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
                $menu = '<select name="'.$name.'" id="'.$id."\">\n";
                foreach (eGovLibrary::$arrCheckTypes as $typeId => $type) {
                    $menu .=
                        '<option value="'.$typeId.'"'.
                        ($selected == $typeId
                          ? 'selected="selected"' : ''
                        ).
                        '>'.$_ARRAYLANG[$type['name']]."</option>\n";
                }
                $menu .= "</select>\n";
                break;
        }
        return $menu;
    }


    function _getFormFieldRequiredCheckBox($name, $id, $type, $selected)
    {
        switch ($type) {
            case 'hidden':
            case 'select':
            case 'label':
                return '';
            default:
                return '<input type="checkbox" name="'.$name.'" id="'.$id.'" '.($selected ? 'checked="checked"' : '').' />';
        }
    }


    function _getFormFieldAttribute($id, $type, $attr)
    {
        global $_ARRAYLANG;

        switch ($type) {
        case 'text':
            return
                '<input style="width:228px;" type="text" '.
                'name="contactFormFieldAttribute['.$id.']" value="'.$attr."\" />\n";
        case 'label':
            return
                '<input style="width:228px;" type="text" '.
                'name="contactFormFieldAttribute['.$id.']" value="'.$attr."\" />\n";
        case 'checkbox':
            return
                '<select style="width:228px;" '.
                'name="contactFormFieldAttribute['.$id."]\">\n".
                '  <option value="0"'.($attr == 0 ? ' selected="selected"' : '').'>'.$_ARRAYLANG['TXT_EGOV_NOT_SELECTED']."</option>\n".
                '  <option value="1"'.($attr == 1 ? ' selected="selected"' : '').'>'.$_ARRAYLANG['TXT_EGOV_SELECTED']."</option>\n".
                "</select>\n";
        case 'checkboxGroup':
            return
                '<input style="width:228px;" type="text" '.
                'name="contactFormFieldAttribute['.$id.']" value="'.$attr."\" /> *\n";
        case 'hidden':
            return
                '<input style="width:228px;" type="text" '.
                'name="contactFormFieldAttribute['.$id.']" value="'.$attr."\" />\n";
        case 'select':
        case 'radio':
            return '<input style="width:228px;" type="text" '.
            'name="contactFormFieldAttribute['.$id.']" value="'.$attr."\" /> *\n";
        }
        // default:
        return '';
    }


    /**
     * Store the form data for the product
     */
    function _saveForm()
    {
        global $_CONFIG;

        if (empty($_REQUEST['saveForm'])) {
            return true;
        }
        $formId = (isset($_REQUEST['formId']) ? intval($_REQUEST['formId']) : 0);
        $productName = isset($_POST['productFormName']) ? contrexx_addslashes(strip_tags($_POST['productFormName'])) : '';
        $contactFormDesc = isset($_POST['contactFormDesc']) ? contrexx_addslashes($_POST['contactFormDesc']) : '';
        $productFormTargetUrl = isset($_POST['productFormTargetUrl']) ? contrexx_addslashes(strip_tags($_POST['productFormTargetUrl'])) : '';
        $productFormTargetMessage = isset($_POST['productFormTargetMessage']) ? contrexx_addslashes($_POST['productFormTargetMessage']) : '';
        $productFormPerDay = intval($_POST['productFormPerDay']);
        $productFormQuantity = intval($_POST['productFormQuantity']);
        $productQuantityLimit = intval($_POST['productQuantityLimit']);
        $productFormPrice = floatval($_POST['productFormPrice']);
        $productAutoStatus = intval($_POST['productAutoStatus']);
        $productFile = isset($_POST['productFile']) ? contrexx_addslashes($_POST['productFile']) : '';
        $productSenderName = isset($_POST['productSenderName']) ? contrexx_addslashes(strip_tags($_POST['productSenderName'])) : '';
        $productSenderEmail = isset($_POST['productSenderEmail']) ? contrexx_addslashes(strip_tags($_POST['productSenderEmail'])) : '';
        $productTargetSubject = isset($_POST['productTargetSubject']) ? contrexx_addslashes(strip_tags($_POST['productTargetSubject'])) : '';
        $productTargetBody = isset($_POST['productTargetBody']) ? contrexx_addslashes(strip_tags($_POST['productTargetBody'])) : '';
        $productPayPal = intval($_POST['paypal']);
        $productPayPalSandbox = isset($_POST['sandbox_mail']) ? contrexx_addslashes(strip_tags($_POST['sandbox_mail'])) : '';
        $productPayPalCurrency = isset($_POST['general_currency']) ? contrexx_addslashes(strip_tags($_POST['general_currency'])) : '';
        $productYellowpay = intval($_POST['yellowpay_enable']);

        // Alternative payment methods, comma separated list
        $productAlternativePaymentMethods = isset($_POST['alternative_names']) ? contrexx_addslashes(strip_tags($_POST['alternative_names'])) : '';

        if ($productQuantityLimit < 1)
            $productQuantityLimit = 1;
        if ($productQuantityLimit >= $productFormQuantity)
            $productQuantityLimit = $productFormQuantity-1;

        $FileErr = '';
        // Disallow the config file to be used as product file
        if (   $productFile == 'config/configuration.php'
            || $productFile == '/config/configuration.php') {
            $productFile = '';
            $FileErr = 2;
        }
        $productState = (isset($_POST['productState']) ? 1 : 0);
        $productElectro = (isset($_POST['ElectroProduct']) ? 1 : 0);

        $uniqueFieldNames = true;
        $arrFields = $this->_getFormFieldsFromPost($uniqueFieldNames);
        if (!$uniqueFieldNames) {
            $this->_products(1);
            return false;
        }
        $formEmailsTmp = (isset($_POST['productFormEmail']) ? explode(',', contrexx_addslashes($_POST['productFormEmail'])) : '');
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
        $result = false;
        if ($formId > 0) {
            $result = $this->_updateProduct(
                $formId, $productName, $contactFormDesc, $productFormTargetUrl,
                $productFormTargetMessage, $productFormPerDay,
                $productFormQuantity, $productQuantityLimit, $productFormPrice,
                $arrFields, $formEmails, $productState, $productAutoStatus,
                $productElectro, $productFile, $productSenderName,
                $productSenderEmail, $productTargetSubject, $productTargetBody,
                $productPayPal , $productPayPalSandbox, $productPayPalCurrency,
                $productYellowpay, $productAlternativePaymentMethods
            );
        } else {
            $result = $this->_saveProduct(
                $formId, $productName, $contactFormDesc, $productFormTargetUrl,
                $productFormTargetMessage, $productFormPerDay,
                $productFormQuantity, $productQuantityLimit, $productFormPrice,
                $arrFields, $formEmails, $productState, $productAutoStatus,
                $productElectro, $productFile, $productSenderName,
                $productSenderEmail, $productTargetSubject, $productTargetBody,
                $productPayPal , $productPayPalSandbox, $productPayPalCurrency,
                $productYellowpay, $productAlternativePaymentMethods
            );
        }
        $this->_products($FileErr);
        return $result;
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
                    'name' => $fieldName,
                    'type' => $type,
                    'attributes' => $attributes,
                    'order_id' => $orderId,
                    'is_required' => $is_required,
                    'check_type' => $checkType,
                );
                ++$orderId;
            }
        }
        return $arrFields;
    }


    function _updateProduct(
        $formId, $productName, $contactFormDesc,
        $productFormTargetUrl, $productFormTargetMessage,
        $productFormPerDay, $productFormQuantity, $productQuantityLimit,
        $productFormPrice, $arrFields, $formEmails,
        $productState, $productAutoStatus, $productElectro, $productFile,
        $productSenderName, $productSenderEmail,
        $productTargetSubject, $productTargetBody,
        $productPayPal, $productPayPalSandbox, $productPayPalCurrency,
        $productYellowpay, $productAlternativePaymentMethods
    ) {
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_products
               SET product_name='$productName',
                   product_desc='$contactFormDesc',
                   product_price='$productFormPrice',
                   product_per_day='$productFormPerDay',
                   product_quantity='$productFormQuantity',
                   product_quantity_limit='$productQuantityLimit',
                   product_target_email='$formEmails',
                   product_target_url='$productFormTargetUrl',
                   product_message='$productFormTargetMessage',
                   product_status='$productState',
                   product_autostatus='$productAutoStatus',
                   product_electro='$productElectro',
                   product_file='$productFile',
                   product_sender_name='$productSenderName',
                   product_sender_email='$productSenderEmail',
                   product_target_subject='$productTargetSubject',
                   product_target_body='$productTargetBody',
                   product_paypal='$productPayPal',
                   product_paypal_sandbox='$productPayPalSandbox',
                   product_paypal_currency='$productPayPalCurrency',
                   yellowpay='$productYellowpay',
                   alternative_names='$productAlternativePaymentMethods'
             WHERE product_id=$formId
        ");
        if (!$objResult) return false;

        $arrFormFields = eGovLibrary::getFormFields($formId);
        $arrRemoveFormFields = array_diff_assoc($arrFormFields, $arrFields);

        $result = true;
        foreach ($arrFields as $fieldId => $arrField) {
            if (isset($arrFormFields[$fieldId])) {
                $result &= $this->_updateFormField($fieldId, $arrField['name'], $arrField['type'], $arrField['attributes'], $arrField['order_id'], $arrField['is_required'], $arrField['check_type']);
            } else {
                $result &= $this->_addFormField($formId, $arrField['name'], $arrField['type'], $arrField['attributes'], $arrField['order_id'], $arrField['is_required'], $arrField['check_type']);
            }
        }
        foreach (array_keys($arrRemoveFormFields) as $fieldId) {
            $result &= $this->_deleteFormField($fieldId);
        }
        return $result;
    }


    function _saveProduct(
        $formId, $productName, $contactFormDesc,
        $productFormTargetUrl, $productFormTargetMessage,
        $productFormPerDay, $productFormQuantity, $productQuantityLimit,
        $productFormPrice, $arrFields, $formEmails,
        $productState, $productAutoStatus, $productElectro, $productFile,
        $productSenderName, $productSenderEmail,
        $productTargetSubject, $productTargetBody,
        $productPayPal, $productPayPalSandbox, $productPayPalCurrency,
        $productYellowpay, $productAlternativePaymentMethods
    ) {
        global $objDatabase;

        $result = false;
        if ($objDatabase->Execute("
            INSERT INTO ".DBPREFIX."module_egov_products (
                `product_name`, `product_desc`,
                `product_price`, `product_per_day`, `product_quantity`,
                `product_quantity_limit`,
                `product_target_email`, `product_target_url`,
                `product_message`, `product_status`,
                `product_autostatus`, `product_electro`, `product_file`,
                `product_sender_name`, `product_sender_email`,
                `product_target_subject`, `product_target_body`,
                `product_paypal`, `product_paypal_sandbox`,
                `product_paypal_currency`,
                `yellowpay`,
                `alternative_names`
            ) VALUES (
                '$productName', '$contactFormDesc',
                '$productFormPrice', '$productFormPerDay', '$productFormQuantity',
                '$productQuantityLimit',
                '$formEmails', '$productFormTargetUrl',
                '$productFormTargetMessage', '$productState',
                '$productAutoStatus', '$productElectro', '$productFile',
                '$productSenderName', '$productSenderEmail',
                '$productTargetSubject', '$productTargetBody',
                '$productPayPal', '$productPayPalSandbox',
                '$productPayPalCurrency',
                '$productYellowpay',
                '$productAlternativePaymentMethods'
            )
        ")) {
            $formId = $objDatabase->Insert_ID();
            $result = true;
            foreach ($arrFields as $arrField) {
                $result &= $this->_addFormField($formId, $arrField['name'], $arrField['type'], $arrField['attributes'], $arrField['order_id'], $arrField['is_required'], $arrField['check_type']);
            }
        }
// TODO: What if the insert failed?
        $_REQUEST['formId'] = $formId;
        $result &= $this->initContactForms();
        return $result;
    }


    function _updateFormField(
        $id, $name, $type, $attributes, $orderId, $isRequired, $checkType
    ) {
        global $objDatabase;

        if ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_product_fields
               SET name='$name',
                   type='$type',
                   attributes='".addslashes($attributes)."',
                   is_required='$isRequired',
                   check_type='$checkType',
                   order_id=$orderId
             WHERE id=$id
        ")) return true;
        return false;
    }


    function _addFormField(
        $formId, $name, $type, $attributes, $orderId, $isRequired, $checkType
    ) {
        global $objDatabase;

        if ($objDatabase->Execute("
            INSERT INTO ".DBPREFIX."module_egov_product_fields (
                `product`, `name`, `type`, `attributes`, `order_id`,
                `is_required`, `check_type`
            ) VALUES (
                $formId, '$name', '$type',
                '".addslashes($attributes)."', $orderId,
                '$isRequired', '$checkType'
            )
        ")) return true;
        return false;
    }


    function _deleteFormField($id)
    {
        global $objDatabase;

        if ($objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_egov_product_fields
             WHERE id=$id
        ")) return true;
        return false;
    }


    function storeSettings()
    {
        global $objDatabase;

        $result = true;
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".contrexx_addslashes(strip_tags($_REQUEST['senderName']))."'
             WHERE `name`='set_sender_name'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".contrexx_addslashes(strip_tags($_REQUEST['senderEmail']))."'
             WHERE `name`='set_sender_email'
        ") ? true : false);
/*
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".contrexx_addslashes(strip_tags($_REQUEST['recipientEmail']))."'
             WHERE `name`='set_recipient_email'
        ") ? true : false);
*/
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".contrexx_addslashes(strip_tags($_REQUEST['stateEmail']))."'
             WHERE `name`='set_state_email'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".contrexx_addslashes(strip_tags($_REQUEST['calenderColor1']))."'
             WHERE `name`='set_calendar_color_1'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".contrexx_addslashes(strip_tags($_REQUEST['calenderColor2']))."'
             WHERE `name`='set_calendar_color_2'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".contrexx_addslashes(strip_tags($_REQUEST['calenderColor3']))."'
             WHERE `name`='set_calendar_color_3'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".contrexx_addslashes(strip_tags($_REQUEST['calenderLegende1']))."'
             WHERE `name`='set_calendar_legende_1'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".contrexx_addslashes(strip_tags($_REQUEST['calenderLegende2']))."'
             WHERE `name`='set_calendar_legende_2'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".contrexx_addslashes(strip_tags($_REQUEST['calenderLegende3']))."'
             WHERE `name`='set_calendar_legende_3'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".contrexx_addslashes(strip_tags($_REQUEST['calenderBackground']))."'
             WHERE `name`='set_calendar_background'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".contrexx_addslashes(strip_tags($_REQUEST['calenderBorder']))."'
             WHERE `name`='set_calendar_border'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".contrexx_addslashes(strip_tags($_REQUEST['calenderDateLabel']))."'
             WHERE `name`='set_calendar_date_label'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".contrexx_addslashes(strip_tags($_REQUEST['calenderDateDesc']))."'
             WHERE `name`='set_calendar_date_desc'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".contrexx_addslashes(strip_tags($_REQUEST['stateSubject']))."'
             WHERE `name`='set_state_subject'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".contrexx_addslashes(strip_tags($_REQUEST['orderentrySubject']))."'
             WHERE `name`='set_orderentry_subject'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".contrexx_addslashes(strip_tags($_REQUEST['orderentryEmail']))."'
             WHERE `name`='set_orderentry_email'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".contrexx_addslashes(strip_tags($_REQUEST['orderentrysenderName']))."'
             WHERE `name`='set_orderentry_name'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".contrexx_addslashes(strip_tags($_REQUEST['orderentrysenderEmail']))."'
             WHERE `name`='set_orderentry_sender'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".contrexx_addslashes(strip_tags($_REQUEST['orderentryrecipientEmail']))."'
             WHERE `name`='set_orderentry_recipient'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".contrexx_addslashes(strip_tags($_REQUEST['PayPal_mail']))."'
             WHERE `name`='set_paypal_email'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".contrexx_addslashes(strip_tags($_REQUEST['general_currency']))."'
             WHERE `name`='set_paypal_currency'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".(isset($_REQUEST['PayPal_IPN']) ? 1 : 0)."'
             WHERE `name`='set_paypal_ipn'
        ") ? true : false);
        $strAcceptedPM = (isset($_POST['yellowpay_accepted_payment_methods'])
            ? addslashes(join(',', $_POST['yellowpay_accepted_payment_methods']))
            : ''
        );
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='$strAcceptedPM'
             WHERE `name`='yellowpay_accepted_payment_methods'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".(isset($_REQUEST['yellowpay_uid']) ? $_REQUEST['yellowpay_uid'] : '')."'
             WHERE `name`='yellowpay_uid'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".(isset($_REQUEST['yellowpay_shopid']) ? $_REQUEST['yellowpay_shopid'] : '')."'
             WHERE `name`='yellowpay_shopid'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".(isset($_REQUEST['yellowpay_hashseed']) ? $_REQUEST['yellowpay_hashseed'] : '')."'
             WHERE `name`='yellowpay_hashseed'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".(isset($_REQUEST['yellowpay_authorization']) ? $_REQUEST['yellowpay_authorization'] : '')."'
             WHERE `name`='yellowpay_authorization'
        ") ? true : false);
        $result &= ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_egov_configuration
               SET `value`='".(isset($_REQUEST['yellowpay_use_testserver']) ? $_REQUEST['yellowpay_use_testserver'] : '')."'
             WHERE `name`='yellowpay_use_testserver'
        ") ? true : false);
        return $result;
    }


    function getCurrencyMenuoptions($selected='')
    {
        return
            '<option value="CHF"'.
              ($selected == 'CHF' ? ' selected="selected"' : '').
              '>(CHF) Schweizer Franken</option>'.
            '<option value="EUR"'.
              ($selected == 'EUR' ? ' selected="selected"' : '').
              '>(EUR) Euro</option>'.
            '<option value="USD"'.
              ($selected == 'USD' ? ' selected="selected"' : '').
              '>(USD) US Dollar</option>'.
            '<option value="GBP"'.
              ($selected == 'GBP' ? ' selected="selected"' : '').
              '>(GBP) Britische Pfund</option>'.
            '<option value="JPY"'.
              ($selected == 'JPY' ? ' selected="selected"' : '').
              '>(JPY) Yen</option>';
    }


    function _ProductDetail()
    {
        global $objDatabase, $_ARRAYLANG;

        if (isset($_POST['submitContactForm'])) {
            eGov::_saveOrder();
        }

        $this->objTemplate->loadTemplateFile('module_gov_product_insert.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_EGOV_RESERVATION'];

        $this->objTemplate->setVariable(array(
            'TXT_EGOV_RESERVATION' => $_ARRAYLANG['TXT_EGOV_RESERVATION'],
        ));

        if (empty($_REQUEST['id'])) {
            return false;
        }
        $product_id =$_REQUEST['id'];

        $query = "
            SELECT product_id, product_name, product_desc, product_price ".
             "FROM ".DBPREFIX."module_egov_products
             WHERE product_id=$product_id
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult && $objResult->RecordCount()) {
            $FormSource = $this->getSourceCodeBackend($product_id, false, true);
            $this->objTemplate->setVariable(array(
                'EGOV_PRODUCT_TITLE' => $objResult->fields['product_name'],
                'EGOV_PRODUCT_ID' => $objResult->fields['product_id'],
                'EGOV_PRODUCT_DESC' => $objResult->fields['product_desc'],
                'EGOV_FORM' => $FormSource,
            ));
        }
        return true;
    }


    /**
     * Save any order received from the form page.
     * @return  string              The status message if an error occurred,
     *                              the empty string otherwise
     */
    function _saveOrder()
    {
        global $objDatabase, $_ARRAYLANG;

        $product_id = intval($_REQUEST['id']);
        $datum_db = date('Y-m-d H:i:s');
        $ip_adress = $_SERVER['REMOTE_ADDR'];

        $arrFields = eGovLibrary::getFormFields($product_id);
        $FormValue = '';
        foreach ($arrFields as $fieldId => $arrField) {
            $FormValue .= $arrField['name'].'::'.contrexx_addslashes(strip_tags($_REQUEST['contactFormField_'.$fieldId])).';;';
        }

        $quantity = 0;
        if (eGovLibrary::GetProduktValue('product_per_day', $product_id) == 'yes') {
            $quantity = intval($_REQUEST['contactFormField_Quantity']);
            $FormValue = eGovLibrary::GetSettings('set_calendar_date_label').'::'.contrexx_addslashes(strip_tags($_REQUEST['contactFormField_1000'])).';;'.$FormValue;
            $FormValue = $_ARRAYLANG['TXT_EGOV_QUANTITY'].'::'.$quantity.';;'.$FormValue;
        }

        $objDatabase->Execute("
            INSERT INTO ".DBPREFIX."module_egov_orders (
                order_date, order_ip, order_product, order_values
            ) VALUES (
                '$datum_db', '$ip_adress', '$product_id', '$FormValue'
            )
        ");
        $order_id = $objDatabase->Insert_ID();

        if (eGovLibrary::GetProduktValue('product_per_day', $product_id) == 'yes') {
            list ($calD, $calM, $calY) = split('[.]', $_REQUEST['contactFormField_1000']);
            for($x = 0; $x < $quantity; ++$x) {
                $objDatabase->Execute("
                    INSERT INTO ".DBPREFIX."module_egov_product_calendar (
                        calendar_product, calendar_order, calendar_day,
                        calendar_month, calendar_year
                    ) VALUES (
                        '$product_id', '$order_id', '$calD',
                        '$calM', '$calY'
                    )
                ");
            }
        }

        // update the order right away
        if (eGov::GetOrderValue('order_state', $order_id) == 0) {
            // If any non-empty string is returned, an error occurred.
            $ReturnValue = $this->updateOrder($order_id);
            if (!empty($ReturnValue)) {
                $this->_strErrMessage = $_ARRAYLANG['TXT_EGOV_ERROR_ADDING_RESERVATION'];
                return false;
            }
        }
        $this->_strOkMessage = $_ARRAYLANG['TXT_EGOV_RESERVATION_ADDED_SUCCESSFULLY'];
        return true;
    }


    /**
     * Update the order status and send the confirmation mail
     * according to the settings
     *
     * The resulting javascript code displays a message box or
     * does some page redirect.
     * @param   integer   $order_id       The order ID
     * @return  string                    Javascript code
     */
    function updateOrder($order_id)
    {
        global $_ARRAYLANG, $_CONFIG;

        $product_id = eGov::getOrderValue('order_product', $order_id);
        if (empty($product_id)) {
            return 'alert("'.$_ARRAYLANG['TXT_EGOV_ERROR_UPDATING_ORDER'].'");'."\n";
        }

        // Has this order been updated already?
        if (eGov::GetOrderValue('order_state', $order_id) == 1) {
            // Do not resend mails!
            return '';
        }

        $arrFields = eGovLibrary::getOrderValues($order_id);
        $FormValue4Mail = '';
        foreach ($arrFields as $name => $value) {
            // If the value matches a calendar date, prefix the string with
            // the day of the week
            $arrMatch = array();
            if (preg_match('/^(\d\d?)\.(\d\d?)\.(\d\d\d\d)$/', $value, $arrMatch)) {
                // ISO-8601 numeric representation of the day of the week
                // 1 (for Monday) through 7 (for Sunday)
                $dotwNumber =
                    date('N', mktime(1,1,1,$arrMatch[2],$arrMatch[1],$arrMatch[3]));
                $dotwName = $_ARRAYLANG['TXT_EGOV_DAYNAME_'.$dotwNumber];
                $value = "$dotwName, $value";
            }

            $FormValue4Mail .= html_entity_decode($name).': '.html_entity_decode($value)."\n";
        }
        // Bestelleingang-Benachrichtigung || Mail f�r den Administrator
        $recipient = eGovLibrary::GetProduktValue('product_target_email', $product_id);
        if (empty($recipient)) {
            $recipient = eGovLibrary::GetSettings('set_orderentry_recipient');
        }
        if (!empty($recipient)) {
            $SubjectText = str_replace('[[PRODUCT_NAME]]', html_entity_decode(eGovLibrary::GetProduktValue('product_name', $product_id)), eGovLibrary::GetSettings('set_orderentry_subject'));
            $SubjectText = html_entity_decode($SubjectText);
            $BodyText = str_replace('[[ORDER_VALUE]]', $FormValue4Mail, eGovLibrary::GetSettings('set_orderentry_email'));
            $BodyText = html_entity_decode($BodyText);
            $replyAddress = eGovLibrary::GetEmailAdress($order_id);
            if (empty($replyAddress)) {
                $replyAddress = eGovLibrary::GetSettings('set_orderentry_sender');
            }
            if (@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') {
                $objMail = new phpmailer();
                if (!empty($_CONFIG['coreSmtpServer']) && @include_once ASCMS_CORE_PATH.'/SmtpSettings.class.php') {
                    if (($arrSmtp = SmtpSettings::getSmtpAccount($_CONFIG['coreSmtpServer'])) !== false) {
                        $objMail->IsSMTP();
                        $objMail->Host = $arrSmtp['hostname'];
                        $objMail->Port = $arrSmtp['port'];
                        $objMail->SMTPAuth = true;
                        $objMail->Username = $arrSmtp['username'];
                        $objMail->Password = $arrSmtp['password'];
                    }
                }
                $objMail->CharSet = CONTREXX_CHARSET;
                $objMail->From = eGovLibrary::GetSettings('set_orderentry_sender');
                $objMail->FromName = eGovLibrary::GetSettings('set_orderentry_name');
                $objMail->AddReplyTo($replyAddress);
                $objMail->Subject = $SubjectText;
                $objMail->Priority = 3;
                $objMail->IsHTML(false);
                $objMail->Body = $BodyText;
                $objMail->AddAddress($recipient);
                $objMail->Send();
            }
        }

        // Update 29.10.2006 Statusmail automatisch abschicken || Produktdatei
        if (   eGovLibrary::GetProduktValue('product_electro', $product_id) == 1
            || eGovLibrary::GetProduktValue('product_autostatus', $product_id) == 1
        ) {
            eGovLibrary::updateOrderStatus($order_id, 1);
            $TargetMail = eGovLibrary::GetEmailAdress($order_id);
            if ($TargetMail != '') {
                $FromEmail = eGovLibrary::GetProduktValue('product_sender_email', $product_id);
                if ($FromEmail == '') {
                    $FromEmail = eGovLibrary::GetSettings('set_sender_email');
                }
                $FromName = eGovLibrary::GetProduktValue('product_sender_name', $product_id);
                if ($FromName == '') {
                    $FromName = eGovLibrary::GetSettings('set_sender_name');
                }
                $SubjectDB = eGovLibrary::GetProduktValue('product_target_subject', $product_id);
                if ($SubjectDB == '') {
                    $SubjectDB = eGovLibrary::GetSettings('set_state_subject');
                }
                $SubjectText = str_replace('[[PRODUCT_NAME]]', html_entity_decode(eGovLibrary::GetProduktValue('product_name', $product_id)), $SubjectDB);
                $SubjectText = html_entity_decode($SubjectText);
                $BodyDB = eGovLibrary::GetProduktValue('product_target_body', $product_id);
                if ($BodyDB == '') {
                    $BodyDB = eGovLibrary::GetSettings('set_state_email');
                }
                $BodyText = str_replace('[[ORDER_VALUE]]', $FormValue4Mail, $BodyDB);
                $BodyText = str_replace('[[PRODUCT_NAME]]', html_entity_decode(eGovLibrary::GetProduktValue('product_name', $product_id)), $BodyText);
                $BodyText = html_entity_decode($BodyText);
                if (@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') {
                    $objMail = new phpmailer();
                    if ($_CONFIG['coreSmtpServer'] > 0 && @include_once ASCMS_CORE_PATH.'/SmtpSettings.class.php') {
                        if (($arrSmtp = SmtpSettings::getSmtpAccount($_CONFIG['coreSmtpServer'])) !== false) {
                            $objMail->IsSMTP();
                            $objMail->Host = $arrSmtp['hostname'];
                            $objMail->Port = $arrSmtp['port'];
                            $objMail->SMTPAuth = true;
                            $objMail->Username = $arrSmtp['username'];
                            $objMail->Password = $arrSmtp['password'];
                        }
                    }
                    $objMail->CharSet = CONTREXX_CHARSET;
                    $objMail->From = $FromEmail;
                    $objMail->FromName = $FromName;
                    $objMail->AddReplyTo($FromEmail);
                    $objMail->Subject = $SubjectText;
                    $objMail->Priority = 3;
                    $objMail->IsHTML(false);
                    $objMail->Body = $BodyText;
                    $objMail->AddAddress($TargetMail);
                    if (eGovLibrary::GetProduktValue('product_electro', $product_id) == 1) {
                        $objMail->AddAttachment(ASCMS_PATH.eGovLibrary::GetProduktValue('product_file', $product_id));
                    }
                    $objMail->Send();
                }
            }
        }
        return '';
    }


    function chooseReservationProduct()
    {
        global $objDatabase, $_ARRAYLANG;

        $this->objTemplate->loadTemplateFile('module_gov_choose_product.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_EGOV_PRODUCT_FOR_RESERVATION'];

        $this->objTemplate->setVariable(array(
            'TXT_PRODUCT' => $_ARRAYLANG['TXT_PRODUCT'],
            'TXT_EGOV_CHOOSE_PRODUCT_FOR_RESERVATION' => $_ARRAYLANG['TXT_EGOV_CHOOSE_PRODUCT_FOR_RESERVATION'],
        ));

        $query = "
            SELECT *
              FROM ".DBPREFIX."module_egov_products
             ORDER BY product_orderby, product_name
        ";
        $objResult = $objDatabase->Execute($query);
        $i = 0;
        while(!$objResult->EOF) {
            $StatusImg = '<img src="images/icons/status_green.gif" width="10" height="10" border="0" alt="" />';
            if ($objResult->fields["product_status"]!=1) {
                $StatusImg = '<img src="images/icons/status_red.gif" width="10" height="10" border="0" alt="" />';
            }

            $this->objTemplate->setVariable(array(
                'ROWCLASS' => (++$i % 2 ? 'row2' : 'row1'),
                'PRODUCT_ID' => $objResult->fields['product_id'],
                'PRODUCT_NAME' => $objResult->fields['product_name'],
                'PRODUCT_STATUS' => $StatusImg,
            ));

            $this->objTemplate->parse('products_list');
            $objResult->MoveNext();
        }
        if ($i == 0) {
            $this->objTemplate->hideBlock('products_list');
        }

    }


    static function getStatusMenuOptions($selected)
    {
        global $_ARRAYLANG;

        $arrState = array(
            0 => $_ARRAYLANG['TXT_STATE_DELETED'],
            1 => $_ARRAYLANG['TXT_STATE_OK'],
            2 => $_ARRAYLANG['TXT_STATE_NEW'],
            3 => $_ARRAYLANG['TXT_STATE_ALTERNATIVE'],
        );
        $strMenuOptions = '';
        foreach ($arrState as $index => $status) {
            $strMenuOptions .=
                '<option value="'.$index.'"'.
                ($index == $selected ? ' selected="selected"' : '').
                '>'.$status.'</option>';
        }
        return $strMenuOptions;
    }
}

?>
