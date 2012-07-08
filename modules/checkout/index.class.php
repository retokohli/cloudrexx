<?php

/**
 * @ignore
 */
require_once(ASCMS_MODULE_PATH.'/checkout/lib/CheckoutLibrary.class.php');
require_once(ASCMS_MODULE_PATH.'/checkout/lib/Transaction.class.php');
require_once(ASCMS_MODULE_PATH.'/checkout/lib/Country.class.php');
require_once(ASCMS_MODULE_PATH.'/checkout/lib/Yellowpay.class.php');
require_once(ASCMS_MODULE_PATH.'/checkout/lib/SettingsYellowpay.class.php');
require_once(ASCMS_MODULE_PATH.'/checkout/lib/SettingsMails.class.php');
require_once(ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php');

/**
 * Checkout
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_checkout
 */
class Checkout extends CheckoutLibrary {

    /**
     * Transaction object.
     *
     * @access      private
     * @var         Transaction
     */
    private $objTransaction;

    /**
     * Template object.
     *
     * @access      private
     * @var         HTML_TEMPLATE_SIGMA
     */
    private $objTemplate;

    /**
     * All negative and positive status messages.
     *
     * @access      private
     * @var         array
     */
    private $arrStatusMessages = array('ok' => array(), 'error' => array());

    /**
     * Constructor
     * Initialize the template and transaction object.
     *
     * @access      public
     * @param       string     $pageContent      content page
     */
    public function __construct($pageContent)
    {
        global $objDatabase;

        $this->objTransaction = new Transaction($objDatabase);

        $this->objTemplate = new HTML_Template_Sigma('.');
        $this->objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $this->objTemplate->setTemplate($pageContent);
        CSRF::add_placeholder($this->objTemplate);
    }

    /**
     * Get page depending on the result parameter.
     *
     * @access      public
     * @return      string  content page
     */
    public function getPage()
    {
        if (isset($_GET['result'])) {
            $this->registerPaymentResult();
        } else {
            $this->renderForm();
        }

        $this->parseMessages();
        return $this->objTemplate->get();
    }

    /**
     * Replace status message placeholders with the value.
     *
     * @access      private
     */
    private function parseMessages()
    {
        $this->objTemplate->setVariable(array(
            'CHECKOUT_MSG_OK'       => count($this->arrStatusMessages['ok']) ? implode('<br />', $this->arrStatusMessages['ok']) : '',
            'CHECKOUT_MSG_ERROR'    => count($this->arrStatusMessages['error']) ? implode('<br />', $this->arrStatusMessages['error']) : '',
        ));
    }

    /**
     * Generate the form and show hints if necessary.
     * If user input validation is successful a new transaction will be added.
     * In this case the form will be hidden and only a status message will be shown.
     *
     * @access      private
     */
    private function renderForm()
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIGURATION;

        //initialize variables
        $objCountry = new Country($objDatabase);
        $arrCountries = $objCountry->getAll();
        $arrFieldsToHighlight[] = array();
        $cssHighlightingClassName = 'highlight';
        $htmlRequiredFieldCode = '<span class="required_field">&#42;</span>';
        $arrSelectOptions[] = array();
        $status = '';
        $invoiceNumber = '';
        $invoiceCurrency = '';
        $invoiceAmount = '';
        $contactTitle = '';
        $contactForename = '';
        $contactSurname = '';
        $contactCompany = '';
        $contactStreet = '';
        $contactPostcode = '';
        $contactPlace = '';
        $contactCountry = '';
        $contactPhone = '';
        $contactEmail = '';

        $this->objTemplate->hideBlock('redirect');

        //validate submitted user data
        if (isset($_REQUEST['submit'])) {
            $invoiceNumber = $_REQUEST['invoice_number'];
            $invoiceCurrency = $_REQUEST['invoice_currency'];
            $invoiceAmount = $_REQUEST['invoice_amount'];
            $contactTitle = contrexx_input2raw(contrexx_strip_tags($_REQUEST['contact_title']));
            $contactForename = contrexx_input2raw(contrexx_strip_tags($_REQUEST['contact_forename']));
            $contactSurname = contrexx_input2raw(contrexx_strip_tags($_REQUEST['contact_surname']));
            $contactCompany = contrexx_input2raw(contrexx_strip_tags($_REQUEST['contact_company']));
            $contactStreet = contrexx_input2raw(contrexx_strip_tags($_REQUEST['contact_street']));
            $contactPostcode = contrexx_input2raw(contrexx_strip_tags($_REQUEST['contact_postcode']));
            $contactPlace = contrexx_input2raw(contrexx_strip_tags($_REQUEST['contact_place']));
            $contactCountry = $_REQUEST['contact_country'];
            $contactPhone = contrexx_input2raw(contrexx_strip_tags($_REQUEST['contact_phone']));
            $contactEmail = contrexx_input2raw(contrexx_strip_tags($_REQUEST['contact_email']));

            $arrUserData['numeric']['invoice_number']['name'] = $_ARRAYLANG['TXT_CHECKOUT_INVOICE_NUMBER'];
            $arrUserData['numeric']['invoice_number']['value'] = $invoiceNumber;
            $arrUserData['numeric']['invoice_number']['length'] = 11;
            $arrUserData['numeric']['invoice_number']['mandatory'] = 1;

            $arrUserData['selection']['invoice_currency']['name'] = $_ARRAYLANG['TXT_CHECKOUT_INVOICE_CURRENCY'];
            $arrUserData['selection']['invoice_currency']['value'] = $invoiceCurrency;
            $arrUserData['selection']['invoice_currency']['options'] = $this->arrCurrencies;
            $arrUserData['selection']['invoice_currency']['mandatory'] = 1;

            $arrUserData['numeric']['invoice_amount']['name'] = $_ARRAYLANG['TXT_CHECKOUT_INVOICE_AMOUNT'];
            $arrUserData['numeric']['invoice_amount']['value'] = $invoiceAmount;
            $arrUserData['numeric']['invoice_amount']['length'] = 15;
            $arrUserData['numeric']['invoice_amount']['mandatory'] = 1;

            $arrUserData['selection']['contact_title']['name'] = $_ARRAYLANG['TXT_CHECKOUT_CONTACT_TITLE'];
            $arrUserData['selection']['contact_title']['value'] = $contactTitle;
            $arrUserData['selection']['contact_title']['options'] = array(self::MISTER => '', self::MISS => '');
            $arrUserData['selection']['contact_title']['mandatory'] = 1;

            $arrUserData['text']['contact_forename']['name'] = $_ARRAYLANG['TXT_CHECKOUT_CONTACT_FORENAME'];
            $arrUserData['text']['contact_forename']['value'] = $contactForename;
            $arrUserData['text']['contact_forename']['length'] = 255;
            $arrUserData['text']['contact_forename']['mandatory'] = 1;

            $arrUserData['text']['contact_surname']['name'] = $_ARRAYLANG['TXT_CHECKOUT_CONTACT_SURNAME'];
            $arrUserData['text']['contact_surname']['value'] = $contactSurname;
            $arrUserData['text']['contact_surname']['length'] = 255;
            $arrUserData['text']['contact_surname']['mandatory'] = 1;

            $arrUserData['text']['contact_company']['name'] = $_ARRAYLANG['TXT_CHECKOUT_CONTACT_COMPANY'];
            $arrUserData['text']['contact_company']['value'] = $contactCompany;
            $arrUserData['text']['contact_company']['length'] = 255;
            $arrUserData['text']['contact_company']['mandatory'] = 0;

            $arrUserData['text']['contact_street']['name'] = $_ARRAYLANG['TXT_CHECKOUT_CONTACT_STREET'];
            $arrUserData['text']['contact_street']['value'] = $contactStreet;
            $arrUserData['text']['contact_street']['length'] = 255;
            $arrUserData['text']['contact_street']['mandatory'] = 1;

            $arrUserData['text']['contact_postcode']['name'] = $_ARRAYLANG['TXT_CHECKOUT_CONTACT_POSTCODE'];
            $arrUserData['text']['contact_postcode']['value'] = $contactPostcode;
            $arrUserData['text']['contact_postcode']['length'] = 255;
            $arrUserData['text']['contact_postcode']['mandatory'] = 1;

            $arrUserData['text']['contact_place']['name'] = $_ARRAYLANG['TXT_CHECKOUT_CONTACT_PLACE'];
            $arrUserData['text']['contact_place']['value'] = $contactPlace;
            $arrUserData['text']['contact_place']['length'] = 255;
            $arrUserData['text']['contact_place']['mandatory'] = 1;

            $arrUserData['selection']['contact_country']['name'] = $_ARRAYLANG['TXT_CHECKOUT_CONTACT_COUNTRY'];
            $arrUserData['selection']['contact_country']['value'] = intval($contactCountry);
            $arrUserData['selection']['contact_country']['options'] = $arrCountries;
            $arrUserData['selection']['contact_country']['mandatory'] = 1;

            $arrUserData['text']['contact_phone']['name'] = $_ARRAYLANG['TXT_CHECKOUT_CONTACT_PHONE'];
            $arrUserData['text']['contact_phone']['value'] = $contactPhone;
            $arrUserData['text']['contact_phone']['length'] = 255;
            $arrUserData['text']['contact_phone']['mandatory'] = 1;

            $arrUserData['email']['contact_email']['name'] = $_ARRAYLANG['TXT_CHECKOUT_CONTACT_EMAIL'];
            $arrUserData['email']['contact_email']['value'] = $contactEmail;
            $arrUserData['email']['contact_email']['length'] = 255;
            $arrUserData['email']['contact_email']['mandatory'] = 1;

            $arrFieldsToHighlight = $this->validateUserData($arrUserData);

            if (empty($arrFieldsToHighlight)) {
                //validation was successful. now add a new transaction.
                $id = $this->objTransaction->add(
                    self::WAITING,
                    $arrUserData['numeric']['invoice_number']['value'],
                    $arrUserData['selection']['invoice_currency']['value'],
                    $arrUserData['numeric']['invoice_amount']['value'],
                    $arrUserData['selection']['contact_title']['value'],
                    $arrUserData['text']['contact_forename']['value'],
                    $arrUserData['text']['contact_surname']['value'],
                    $arrUserData['text']['contact_company']['value'],
                    $arrUserData['text']['contact_street']['value'],
                    $arrUserData['text']['contact_postcode']['value'],
                    $arrUserData['text']['contact_place']['value'],
                    $arrUserData['selection']['contact_country']['value'],
                    $arrUserData['text']['contact_phone']['value'],
                    $arrUserData['email']['contact_email']['value']
                );
                if ($id) {
                    $this->arrStatusMessages['ok'][] = $_ARRAYLANG['TXT_CHECKOUT_ENTREY_SAVED_SUCCESSFULLY'];

                    $objSettingsYellowpay = new SettingsYellowpay($objDatabase);
                    $arrYellowpay = $objSettingsYellowpay->get();
                    
                    $arrShopOrder = array(
                        'PSPID'    => $arrYellowpay['pspid'],
                        'orderID'  => $id,
                        'amount'   => intval($invoiceAmount * 100),
                        'language' => strtolower(FWLanguage::getLanguageCodeById(FRONTEND_LANG_ID)).'_'.strtoupper(FWLanguage::getLanguageCodeById(FRONTEND_LANG_ID)),
                        'currency' => $this->arrCurrencies[$invoiceCurrency],
                    );
                    
                    $this->objTemplate->setVariable(array(
                        'CHECKOUT_YELLOWPAY_FORM' => Yellowpay::getForm($arrShopOrder, $_ARRAYLANG['TXT_CHECKOUT_START_PAYMENT']),
                    ));

                    if (Yellowpay::$arrError) {
                        $this->arrStatusMessages['error'][] = $_ARRAYLANG['TXT_CHECKOUT_FAILED_TO_INITIALISE_YELLOWPAY'];
                    }

                    $this->objTemplate->hideBlock('form');
                    $this->objTemplate->touchBlock('redirect');
                    return;
                } else {
                    $this->arrStatusMessages['error'][] = $_ARRAYLANG['TXT_CHECKOUT_ENTREY_SAVED_ERROR'];
                }
            }
        } else {
            //get passed data
            $invoiceNumber = !empty($_REQUEST['invoice_number']) ? $_REQUEST['invoice_number'] : '';
            $invoiceCurrency = !empty($_REQUEST['invoice_currency']) ? $_REQUEST['invoice_currency'] : '';
            $invoiceAmount = !empty($_REQUEST['invoice_amount']) ? $_REQUEST['invoice_amount'] : '';
            $contactTitle = !empty($_REQUEST['contact_title']) ? $_REQUEST['contact_title'] : '';
            $contactForename = !empty($_REQUEST['contact_forename']) ? $_REQUEST['contact_forename'] : '';
            $contactSurname = !empty($_REQUEST['contact_surname']) ? $_REQUEST['contact_surname'] : '';
            $contactCompany = !empty($_REQUEST['contact_company']) ? $_REQUEST['contact_company'] : '';
            $contactStreet = !empty($_REQUEST['contact_street']) ? $_REQUEST['contact_street'] : '';
            $contactPostcode = !empty($_REQUEST['contact_postcode']) ? $_REQUEST['contact_postcode'] : '';
            $contactPlace = !empty($_REQUEST['contact_place']) ? $_REQUEST['contact_place'] : '';
            $contactCountry = !empty($_REQUEST['contact_country']) ? $_REQUEST['contact_country'] : '';
            $contactPhone = !empty($_REQUEST['contact_phone']) ? $_REQUEST['contact_phone'] : '';
            $contactEmail = !empty($_REQUEST['contact_email']) ? $_REQUEST['contact_email'] : '';
        }

        //get currency options
        foreach ($this->arrCurrencies as $id => $currency) {
            $selected = ($id == $invoiceCurrency) ? ' selected="selected"' : '';
            $arrSelectOptions['currencies'][] = '<option value="'.$id.'"'.$selected.'>'.contrexx_raw2xhtml($currency).'</option>';
        }

        //get title options
        $selectedMister = (self::MISTER == $contactTitle) ? ' selected="selected"' : '';
        $selectedMiss = (self::MISS == $contactTitle) ? ' selected="selected"' : '';
        $arrSelectOptions['titles'][] = '<option value="'.self::MISTER.'"'.$selectedMister.'>'.$_ARRAYLANG['TXT_CHECKOUT_CONTACT_TITLE_MISTER'].'</option>';
        $arrSelectOptions['titles'][] = '<option value="'.self::MISS.'"'.$selectedMiss.'>'.$_ARRAYLANG['TXT_CHECKOUT_CONTACT_TITLE_MISS'].'</option>';

        //get country options
        if (!empty($arrCountries)) {
            foreach ($arrCountries as $id => $name) {
                $selected = $id == $contactCountry ? ' selected="selected"' : '';
                $arrSelectOptions['countries'][] = '<option value="'.$id.'"'.$selected.'>'.contrexx_raw2xhtml($name).'</option>';
            }
        }

        //add default option
        foreach ($arrSelectOptions as &$array) {
            array_unshift($array,  '<option value="0">-- '.$_ARRAYLANG['TXT_CHECKOUT_SELECTION_CHOOSE_AN_OPTION'].' --</option>');
        }

        $this->objTemplate->setVariable(array(
            'TXT_CHECKOUT_INVOICE'                  => $_ARRAYLANG['TXT_CHECKOUT_INVOICE'],
            'TXT_CHECKOUT_INVOICE_NUMBER'           => $_ARRAYLANG['TXT_CHECKOUT_INVOICE_NUMBER'].$htmlRequiredFieldCode,
            'TXT_CHECKOUT_INVOICE_CURRENCY'         => $_ARRAYLANG['TXT_CHECKOUT_INVOICE_CURRENCY'].$htmlRequiredFieldCode,
            'TXT_CHECKOUT_INVOICE_AMOUNT'           => $_ARRAYLANG['TXT_CHECKOUT_INVOICE_AMOUNT'].$htmlRequiredFieldCode,
            'TXT_CHECKOUT_CONTACT'                  => $_ARRAYLANG['TXT_CHECKOUT_CONTACT'],
            'TXT_CHECKOUT_CONTACT_TITLE'            => $_ARRAYLANG['TXT_CHECKOUT_CONTACT_TITLE'].$htmlRequiredFieldCode,
            'TXT_CHECKOUT_CONTACT_FORENAME'         => $_ARRAYLANG['TXT_CHECKOUT_CONTACT_FORENAME'].$htmlRequiredFieldCode,
            'TXT_CHECKOUT_CONTACT_SURNAME'          => $_ARRAYLANG['TXT_CHECKOUT_CONTACT_SURNAME'].$htmlRequiredFieldCode,
            'TXT_CHECKOUT_CONTACT_COMPANY'          => $_ARRAYLANG['TXT_CHECKOUT_CONTACT_COMPANY'],
            'TXT_CHECKOUT_CONTACT_STREET'           => $_ARRAYLANG['TXT_CHECKOUT_CONTACT_STREET'].$htmlRequiredFieldCode,
            'TXT_CHECKOUT_CONTACT_POSTCODE'         => $_ARRAYLANG['TXT_CHECKOUT_CONTACT_POSTCODE'].$htmlRequiredFieldCode,
            'TXT_CHECKOUT_CONTACT_PLACE'            => $_ARRAYLANG['TXT_CHECKOUT_CONTACT_PLACE'].$htmlRequiredFieldCode,
            'TXT_CHECKOUT_CONTACT_COUNTRY'          => $_ARRAYLANG['TXT_CHECKOUT_CONTACT_COUNTRY'].$htmlRequiredFieldCode,
            'TXT_CHECKOUT_CONTACT_PHONE'            => $_ARRAYLANG['TXT_CHECKOUT_CONTACT_PHONE'].$htmlRequiredFieldCode,
            'TXT_CHECKOUT_CONTACT_EMAIL'            => $_ARRAYLANG['TXT_CHECKOUT_CONTACT_EMAIL'].$htmlRequiredFieldCode,
            'CHECKOUT_INVOICE_NUMBER'               => $invoiceNumber,
            'CHECKOUT_INVOICE_CURRENCY_OPTIONS'     => count($arrSelectOptions['currencies']) ? implode($arrSelectOptions['currencies']) : '',
            'CHECKOUT_INVOICE_AMOUNT'               => $invoiceAmount,
            'CHECKOUT_CONTACT_TITLE_OPTIONS'        => count($arrSelectOptions['titles']) ? implode($arrSelectOptions['titles']) : '',
            'CHECKOUT_CONTACT_FORENAME'             => $contactForename,
            'CHECKOUT_CONTACT_SURNAME'              => $contactSurname,
            'CHECKOUT_CONTACT_COMPANY'              => $contactCompany,
            'CHECKOUT_CONTACT_STREET'               => $contactStreet,
            'CHECKOUT_CONTACT_POSTCODE'             => $contactPostcode,
            'CHECKOUT_CONTACT_PLACE'                => $contactPlace,
            'CHECKOUT_CONTACT_COUNTRY_OPTIONS'      => count($arrSelectOptions['countries']) ? implode($arrSelectOptions['countries']) : '',
            'CHECKOUT_CONTACT_PHONE'                => $contactPhone,
            'CHECKOUT_CONTACT_EMAIL'                => $contactEmail,
            'CHECKOUT_INVOICE_NUMBER_HIGHLIGHT'     => isset($arrFieldsToHighlight['invoice_number']) ? $cssHighlightingClassName : '',
            'CHECKOUT_INVOICE_CURRENCY_HIGHLIGHT'   => isset($arrFieldsToHighlight['invoice_currency']) ? $cssHighlightingClassName : '',
            'CHECKOUT_INVOICE_AMOUNT_HIGHLIGHT'     => isset($arrFieldsToHighlight['invoice_amount']) ? $cssHighlightingClassName : '',
            'CHECKOUT_CONTACT_TITLE_HIGHLIGHT'      => isset($arrFieldsToHighlight['contact_title']) ? $cssHighlightingClassName : '',
            'CHECKOUT_CONTACT_FORENAME_HIGHLIGHT'   => isset($arrFieldsToHighlight['contact_forename']) ? $cssHighlightingClassName : '',
            'CHECKOUT_CONTACT_SURNAME_HIGHLIGHT'    => isset($arrFieldsToHighlight['contact_surname']) ? $cssHighlightingClassName : '',
            'CHECKOUT_CONTACT_COMPANY_HIGHLIGHT'    => isset($arrFieldsToHighlight['contact_company']) ? $cssHighlightingClassName : '',
            'CHECKOUT_CONTACT_STREET_HIGHLIGHT'     => isset($arrFieldsToHighlight['contact_street']) ? $cssHighlightingClassName : '',
            'CHECKOUT_CONTACT_POSTCODE_HIGHLIGHT'   => isset($arrFieldsToHighlight['contact_postcode']) ? $cssHighlightingClassName : '',
            'CHECKOUT_CONTACT_PLACE_HIGHLIGHT'      => isset($arrFieldsToHighlight['contact_place']) ? $cssHighlightingClassName : '',
            'CHECKOUT_CONTACT_COUNTRY_HIGHLIGHT'    => isset($arrFieldsToHighlight['contact_country']) ? $cssHighlightingClassName : '',
            'CHECKOUT_CONTACT_PHONE_HIGHLIGHT'      => isset($arrFieldsToHighlight['contact_phone']) ? $cssHighlightingClassName : '',
            'CHECKOUT_CONTACT_EMAIL_HIGHLIGHT'      => isset($arrFieldsToHighlight['contact_email']) ? $cssHighlightingClassName : '',
        ));
        $this->objTemplate->parse('redirect');
        $this->objTemplate->parse('form');
    }

    /**
     * Validate user input data.
     *
     * @access      private
     * @param       array       $arrUserData            user input data from submitted form
     * @return      array       $arrFieldsToHighlight   contains all fields which need to be highlighted
     */
    private function validateUserData($arrUserData)
    {
        global $_ARRAYLANG;

        $arrFieldsToHighlight = array();

        foreach ($arrUserData['numeric'] as $key => $field) {
            if (!empty($field['mandatory'])) {
                if (empty($field['value'])) {
                    $msg = $_ARRAYLANG['TXT_CHECKOUT_VALIDATION_FIELD_EMPTY'];
                    $msg = str_replace('{FIELD_NAME}', $field['name'], $msg);
                    $this->arrStatusMessages['error'][] = $msg;
                    $arrFieldsToHighlight[$key] = '';
                    continue;
                }
            }
            if (strlen($field['value']) > $field['length']) {
                $msg = $_ARRAYLANG['TXT_CHECKOUT_VALIDATION_FIELD_LENGTH_EXCEEDED'];
                $msg = str_replace('{FIELD_NAME}', $field['name'], $msg);
                $msg = str_replace('{MAX_LENGTH}', $field['length'], $msg);
                $this->arrStatusMessages['error'][] = $msg;
                $arrFieldsToHighlight[$key] = '';
                continue;
            }
            if (!empty($field['value']) && !is_numeric($field['value'])) {
                $msg = $_ARRAYLANG['TXT_CHECKOUT_VALIDATION_FIELD_NOT_NUMERIC'];
                $msg = str_replace('{FIELD_NAME}', $field['name'], $msg);
                $this->arrStatusMessages['error'][] = $msg;
                $arrFieldsToHighlight[$key] = '';
                continue;
            }
            if (!empty($field['value']) && ($field['value'] < 1)) {
                $msg = $_ARRAYLANG['TXT_CHECKOUT_VALIDATION_FIELD_NOT_POSITIVE'];
                $msg = str_replace('{FIELD_NAME}', $field['name'], $msg);
                $this->arrStatusMessages['error'][] = $msg;
                $arrFieldsToHighlight[$key] = '';
                continue;
            }
        }

        foreach ($arrUserData['text'] as $key => $field) {
            if (!empty($field['mandatory'])) {
                if (empty($field['value'])) {
                    $msg = $_ARRAYLANG['TXT_CHECKOUT_VALIDATION_FIELD_EMPTY'];
                    $msg = str_replace('{FIELD_NAME}', $field['name'], $msg);
                    $this->arrStatusMessages['error'][] = $msg;
                    $arrFieldsToHighlight[$key] = '';
                    continue;
                }
            }
            if (strlen($field['value']) > $field['length']) {
                $msg = $_ARRAYLANG['TXT_CHECKOUT_VALIDATION_FIELD_LENGTH_EXCEEDED'];
                $msg = str_replace('{FIELD_NAME}', $field['name'], $msg);
                $msg = str_replace('{MAX_LENGTH}', $field['length'], $msg);
                $this->arrStatusMessages['error'][] = $msg;
                $arrFieldsToHighlight[$key] = '';
                continue;
            }
        }    

        foreach ($arrUserData['selection'] as $key => $field) {
            if (!empty($field['mandatory'])) {
                if (empty($field['value'])) {
                    $msg = $_ARRAYLANG['TXT_CHECKOUT_VALIDATION_SELECTION_EMPTY'];
                    $msg = str_replace('{FIELD_NAME}', $field['name'], $msg);
                    $this->arrStatusMessages['error'][] = $msg;
                    $arrFieldsToHighlight[$key] = '';
                    continue;
                }
            }
            if (!empty($field['value']) && !isset($field['options'][$field['value']])) {
                $msg = $_ARRAYLANG['TXT_CHECKOUT_VALIDATION_SELECTION_INVALID_OPTION'];
                $msg = str_replace('{FIELD_NAME}', $field['name'], $msg);
                $this->arrStatusMessages['error'][] = $msg;
                $arrFieldsToHighlight[$key] = '';
                continue;
            }
        }

        foreach ($arrUserData['email'] as $key => $field) {
            if (!empty($field['mandatory'])) {
                if (empty($field['value'])) {
                    $msg = $_ARRAYLANG['TXT_CHECKOUT_VALIDATION_FIELD_EMPTY'];
                    $msg = str_replace('{FIELD_NAME}', $field['name'], $msg);
                    $this->arrStatusMessages['error'][] = $msg;
                    $arrFieldsToHighlight[$key] = '';
                    continue;
                }
            }
            if (strlen($field['value']) > $field['length']) {
                $msg = $_ARRAYLANG['TXT_CHECKOUT_VALIDATION_FIELD_LENGTH_EXCEEDED'];
                $msg = str_replace('{FIELD_NAME}', $field['name'], $msg);
                $msg = str_replace('{MAX_LENGTH}', $field['length'], $msg);
                $this->arrStatusMessages['error'][] = $msg;
                $arrFieldsToHighlight[$key] = '';
                continue;
            }
            if (!empty($field['value']) && !FWValidator::isEmail($field['value'])) {
                $msg = $_ARRAYLANG['TXT_CHECKOUT_VALIDATION_INVALID_EMAIL'];
                $msg = str_replace('{FIELD_NAME}', $field['name'], $msg);
                $msg = str_replace('{MAX_LENGTH}', $field['length'], $msg);
                $this->arrStatusMessages['error'][] = $msg;
                $arrFieldsToHighlight[$key] = '';
                continue;
            }
        }

        return $arrFieldsToHighlight;
    }

    /**
     * Evaluate and register the payment result.
     * If the transaction was successful an email will be sent to the customer and administrator.
     *
     * @access      private
     */
    private function registerPaymentResult()
    {
        global $_ARRAYLANG, $_CONFIG, $objDatabase;

        //evaluate payment result
        $status = '';
        $orderId = Yellowpay::getOrderId();
        if (Yellowpay::checkin()) {
            if (abs($_GET['result']) == 1) {
                $status = self::CONFIRMED;
                $this->arrStatusMessages['ok'][] = $_ARRAYLANG['TXT_CHECKOUT_TRANSACTION_WAS_SUCCESSFUL'];

                $arrTransaction = $this->objTransaction->get(array($orderId));
                if ($arrTransaction[0]['status'] == $status) {
                    return;
                }
            } else if (($_GET['result'] == 0) || (abs($_GET['result']) == 2)) {
                $status = self::CANCELLED;
                $this->arrStatusMessages['error'][] = $_ARRAYLANG['TXT_CHECKOUT_TRANSACTION_WAS_CANCELLED'];

                $arrTransaction = $this->objTransaction->get(array($orderId));
                if ($arrTransaction[0]['status'] == $status) {
                    return;
                }
            } else {
                $this->arrStatusMessages['error'][] = $_ARRAYLANG['TXT_CHECKOUT_INVALID_TRANSACTION_STATUS'];
                return;
            }
        } else {
            $this->arrStatusMessages['error'][] = $_ARRAYLANG['TXT_CHECKOUT_SECURITY_CHECK_ERROR'];
            return;
        }

        //update transaction status
        $updateStatus = $this->objTransaction->updateStatus($orderId, $status);

        //send confirmation email (if the payment was successful)
        if ($status == self::CONFIRMED) {
            $arrTransactions = $this->objTransaction->get(array($orderId));

            if (!empty($arrTransactions)) {
                foreach ($arrTransactions as $arrTransaction) {
                    //prepare transaction data for output
                    $arrTransaction['time'] = date('j.n.Y G:i:s', $arrTransaction['time']);
                    switch ($arrTransaction['status']) {
                        case self::WAITING:
                            $arrTransaction['status'] = $_ARRAYLANG['TXT_CHECKOUT_STATUS_WAITING'];
                            break;
                        case self::CONFIRMED:
                            $arrTransaction['status'] = $_ARRAYLANG['TXT_CHECKOUT_STATUS_CONFIRMED'];
                            break;
                        case self::CANCELLED:
                            $arrTransaction['status'] = $_ARRAYLANG['TXT_CHECKOUT_STATUS_CANCELLED'];
                            break;
                    }
                    $arrTransaction['invoice_currency'] = $this->arrCurrencies[$arrTransaction['invoice_currency']];
                    $arrTransaction['invoice_amount'] = number_format($arrTransaction['invoice_amount'], 2, '.', '\'');
                    switch ($arrTransaction['contact_title']) {
                        case self::MISTER:
                            $arrTransaction['contact_title'] = $_ARRAYLANG['TXT_CHECKOUT_CONTACT_TITLE_MISTER'];
                            break;
                        case self::MISS:
                            $arrTransaction['contact_title'] = $_ARRAYLANG['TXT_CHECKOUT_CONTACT_TITLE_MISS'];
                            break;
                    }

                    //get mail templates
                    $objSettingsMail = new SettingsMails($objDatabase);
                    $arrAdminMail = $objSettingsMail->getAdminMail();
                    $arrCustomerMail = $objSettingsMail->getCustomerMail();

                    //fill up placeholders in mail templates
                    $arrPlaceholders = array(
                        'DOMAIN_URL' => $_CONFIG['domainUrl'],
                        'TRANSACTION_ID' => $arrTransaction['id'],
                        'TRANSACTION_TIME' => $arrTransaction['time'],
                        'TRANSACTION_STATUS' => $arrTransaction['status'],
                        'INVOICE_NUMBER' => $arrTransaction['invoice_number'],
                        'INVOICE_CURRENCY' => $arrTransaction['invoice_currency'],
                        'INVOICE_AMOUNT' => $arrTransaction['invoice_amount'],
                        'CONTACT_TITLE' => $arrTransaction['contact_title'],
                        'CONTACT_FORENAME' => $arrTransaction['contact_forename'],
                        'CONTACT_SURNAME' => $arrTransaction['contact_surname'],
                        'CONTACT_COMPANY' => $arrTransaction['contact_company'],
                        'CONTACT_STREET' => $arrTransaction['contact_street'],
                        'CONTACT_POSTCODE' => $arrTransaction['contact_postcode'],
                        'CONTACT_PLACE' => $arrTransaction['contact_place'],
                        'CONTACT_COUNTRY' => $arrTransaction['contact_country'],
                        'CONTACT_PHONE' => $arrTransaction['contact_phone'],
                        'CONTACT_EMAIL' => $arrTransaction['contact_email'],
                    );
                    foreach ($arrPlaceholders as $placeholder => $value) {
                        $arrAdminMail['title'] = str_replace('[['.$placeholder.']]', contrexx_raw2xhtml($value), $arrAdminMail['title']);
                        $arrAdminMail['content'] = str_replace('[['.$placeholder.']]', contrexx_raw2xhtml($value), $arrAdminMail['content']);
                        $arrCustomerMail['title'] = str_replace('[['.$placeholder.']]', contrexx_raw2xhtml($value), $arrCustomerMail['title']);
                        $arrCustomerMail['content'] = str_replace('[['.$placeholder.']]', contrexx_raw2xhtml($value), $arrCustomerMail['content']);
                    }

                    //send mail to administrator and customer
                    $this->sendConfirmationMail($_CONFIG['contactFormEmail'], $arrAdminMail);
                    $this->sendConfirmationMail($arrTransaction['contact_email'], $arrCustomerMail);
                }
            }
        }
    }

    /**
     * Send confirmation email.
     *
     * @access      private
     * @param       string      $recipient      recipient
     * @param       array       $arrMail        title and content
     */
    private function sendConfirmationMail($recipient, $arrMail)
    {
        global $_ARRAYLANG, $_CONFIG;

        $objPHPMailer = new phpmailer();

        if ($_CONFIG['coreSmtpServer'] > 0 && @include_once ASCMS_CORE_PATH.'/SmtpSettings.class.php') {
            if (($arrSmtp = SmtpSettings::getSmtpAccount($_CONFIG['coreSmtpServer'])) !== false) {
                $objPHPMailer->IsSMTP();
                $objPHPMailer->Host = $arrSmtp['hostname'];
                $objPHPMailer->Port = $arrSmtp['port'];
                $objPHPMailer->SMTPAuth = true;
                $objPHPMailer->Username = $arrSmtp['username'];
                $objPHPMailer->Password = $arrSmtp['password'];
            }
        }

        $objPHPMailer->CharSet = CONTREXX_CHARSET;
        $objPHPMailer->IsHTML(true);
        $objPHPMailer->Subject = $arrMail['title'];
        $objPHPMailer->From = $_CONFIG['contactFormEmail'];
        $objPHPMailer->FromName = $_CONFIG['domainUrl'];
        $objPHPMailer->AddAddress($recipient);
        $objPHPMailer->Body = $arrMail['content'];
        $objPHPMailer->Send();
    }
}
