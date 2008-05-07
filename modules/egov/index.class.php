<?php

define('_EGOV_DEBUG', 0);

/**
 * E-Government
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_egov
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once dirname(__FILE__).'/lib/eGovLibrary.class.php';
require_once dirname(__FILE__).'/lib/paypal.class.php';
/**
 * Currency: Conversion, formatting.
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Currency.class.php';
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
    private $_arrFormFieldTypes;

    /**
     * Initialize forms and template
     * @param   string  $pageContent    The page content template
     * @return  eGov                    The eGov object
     */
    function eGov($pageContent)
    {
        if (_EGOV_DEBUG) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            global $objDatabase; $objDatabase->debug = 1;
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
            global $objDatabase; $objDatabase->debug = 0;
        }

        $this->initContactForms();
        $this->pageContent = $pageContent;
        $this->objTemplate = new HTML_Template_Sigma('.');
        $this->objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $this->objTemplate->setTemplate($this->pageContent, true, true);

//eGov::addLog('Created eGov');
    }


    /**
     * Returns the page content built from the current template
     * @return  string              The page content
     */
    function getPage()
    {
        if (empty($_GET['cmd'])) {
            $_GET['cmd'] = '';
        }
        switch($_GET['cmd']) {
            case 'detail':
                $this->_ProductDetail();
            break;
            default:
                $this->_ProductsList();
        }
        return $this->objTemplate->get();
    }


    /**
     * Save any order received from the form page.
     *
     * Calls the {@see payment()} method to handle any payment being made
     * when appropriate.
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
            $FormValue .= $arrField['name'].'::'.strip_tags(contrexx_addslashes($_REQUEST['contactFormField_'.$fieldId])).';;';
        }

        $quantity = intval($_REQUEST['contactFormField_Quantity']);
        $product_amount = eGovLibrary::GetProduktValue('product_price', $product_id);
        if (eGovLibrary::GetProduktValue('product_per_day', $product_id) == 'yes') {
            $FormValue = eGovLibrary::GetSettings('set_calendar_date_label').'::'.strip_tags(contrexx_addslashes($_REQUEST['contactFormField_1000'])).';;'.$FormValue;
            $FormValue = $_ARRAYLANG['TXT_EGOV_QUANTITY'].'::'.$quantity.';;'.$FormValue;
        }

        if ($quantity <= 0) {
            return 'alert("'.$_ARRAYLANG['TXT_EGOV_SPECIFY_COUNT'].'");history.go(-1);';
        }
        $objDatabase->Execute("
            INSERT INTO ".DBPREFIX."module_egov_orders (
                order_date, order_ip, order_product, order_values
            ) VALUES (
                '$datum_db', '$ip_adress', '$product_id', '$FormValue'
            )
        ");
        $order_id = $objDatabase->Insert_ID();
//eGov::addLog("Order ID $order_id stored");

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

        // Handle any kind of payment request
        if (!empty($_REQUEST['handler'])) {
//echo("handler is set (".$_REQUEST['handler'].")<br />");
            $ReturnValue = $this->payment($order_id, $product_amount);
            if (!empty($ReturnValue)) return $ReturnValue;
        }

        $ReturnValue = '';
        // If no more payment handling is required,
        // update the order right away
        if (eGov::GetOrderValue('order_state', $order_id) == 0) {
            // If any non-empty string is returned, an error occurred.
            $ReturnValue = $this->updateOrder($order_id);
//eGov::addLog("_saveOrder(): Order ID $order_id updated, result: /$ReturnValue/");
            if (!empty($ReturnValue)) return $ReturnValue;
        }

        return '';
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
        global $objDatabase, $_ARRAYLANG, $_CONFIG;
//eGov::addLog("Updating order ID $order_id");

        $product_id = eGov::getOrderValue('order_product', $order_id);
        if (empty($product_id)) {
//eGov::addLog("Error updating order ID $order_id: No product ID found");
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
//echo("processing field $name: $value<br />");
            $FormValue4Mail .= html_entity_decode($name).': '.html_entity_decode($value)."\n";
        }
//echo("made form4mail:<br />$FormValue4Mail<br />");
/*
        if (eGovLibrary::GetProduktValue('product_per_day', $product_id) == 'yes') {
            $FormValue4Mail = html_entity_decode(eGovLibrary::GetSettings('set_calendar_date_label')).': '.$_REQUEST['contactFormField_1000']."\n".$FormValue4Mail;
            $FormValue4Mail = $_ARRAYLANG['TXT_EGOV_QUANTITY'].': '.$_REQUEST['contactFormField_Quantity']."\n".$FormValue4Mail;
        }
*/

        // Bestelleingang-Benachrichtigung || Mail für den Administrator
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
                    $objSmtpSettings = new SmtpSettings();
                    if (($arrSmtp = $objSmtpSettings->getSmtpAccount($_CONFIG['coreSmtpServer'])) !== false) {
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
//eGov::addLog("Sent mail to administrator for order ID $order_id");
            }
        }

        // Update 29.10.2006 Statusmail automatisch abschicken || Produktdatei
        if (   eGovLibrary::GetProduktValue('product_electro', $product_id) == 1
            || eGovLibrary::GetProduktValue('product_autostatus', $product_id) == 1
        ) {
            eGov::updateOrderStatus($order_id, 1);
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
                        $objSmtpSettings = new SmtpSettings();
                        if (($arrSmtp = $objSmtpSettings->getSmtpAccount($_CONFIG['coreSmtpServer'])) !== false) {
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
//eGov::addLog("Sent mail to customer for order ID $order_id");
                }
            }
        }
//eGov::addLog("Finished updating order ID $order_id");
        return '';
    }


    function payment($order_id=0, $amount=0)
    {
        $handler = $_REQUEST['handler'];
//eGov::addLog("Entering payment, order ID $order_id, handler /$handler/");

//echo("Got handler $handler<br />");
        switch ($handler) {
          case 'paypal':
            $order_id =
                (!empty($_POST['custom'])
                  ? $_POST['custom']
                  : $order_id
                );
//echo("Handler is PayPal, order_id $order_id<br />");
            return $this->paymentPaypal($order_id, $amount);
          // Payment requests
          // The following are all handled by Yellowpay.
          case 'PostFinanceCard':
          case 'yellownet':
          case 'Master':
          case 'Visa':
          case 'Amex':
          case 'Diners':
          case 'yellowbill':
//echo("Going to Yellowpay, order_id $order_id<br />");
            return $this->paymentYellowpay($order_id, $amount);
          // Returning from Yellowpay
          case 'yellowpay':
//eGov::addLog("Info: paymentYellowpay: POST:\n".var_export($_POST, true));
//eGov::addLog("Info: paymentYellowpay: GET:\n".var_export($_GET, true));
//echo("Coming from Yellowpay, order_id $order_id<br />");
            return $this->paymentYellowpayVerify($order_id);
          // Silently ignore invalid payment requests
        }
//eGov::addLog("Warning: Order ID $order_id, no match for handler /$handler/");
        return '';
    }


    function paymentPaypal($order_id, $amount=0)
    {
        global $_ARRAYLANG;

        if (isset($_GET['result'])) {
            $result = $_GET['result'];
            switch ($result) {
              case -1:
//echo("notification<br />");
                // Go validate PayPal IPN
                $this->paymentPaypalIpn($order_id, $amount);
                die();
              case 0:
//echo("payment failed<br />");
                // Payment failed
                break;
              case 1:
//echo("payment completed<br />");
                // The payment has been completed.
                // The notification with result == -1 will update the order.
                // This case only redirects the customer to the list page with
                // an appropriate message according to the status of the order.
                $order_state = eGovLibrary::GetOrderValue('order_state', $order_id);
                if ($order_state == 1) {
                    $product_id = eGovLibrary::GetOrderValue('order_product', $order_id);
                    return eGov::getSuccessMessage($product_id);
                } elseif ($order_state == 0) {
                    if (eGovLibrary::GetSettings('set_paypal_ipn') == 1) {
                        return 'alert("'.$_ARRAYLANG['TXT_EGOV_PAYPAL_IPN_PENDING']."\");\n";
                    }
                }
                break;
              case 2:
//echo("payment cancelled<br />");
                // Payment was cancelled
                return 'alert("'.$_ARRAYLANG['TXT_EGOV_PAYPAL_CANCEL']."\");\n";
            }
            return 'alert("'.$_ARRAYLANG['TXT_EGOV_PAYPAL_NOT_VALID']."\");\n";
        }

        $product_id = eGov::getOrderValue('order_product', $order_id);
        if (empty($product_id)) {
            return 'alert("'.$_ARRAYLANG['TXT_EGOV_ERROR_UPDATING_ORDER'].'");'."\n";
        }

        // Prepare payment
        $paypalUriIpn = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?section=egov&handler=paypal&result=-1";
        $paypalUriNok = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?section=egov&handler=paypal&result=0";
        $paypalUriOk  = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?section=egov&handler=paypal&result=1";
        $objPaypal = new paypal_class();

        $product_id = eGovLibrary::GetOrderValue('order_product', $order_id);
        if (empty($product_id)) {
            return 'alert("'.$_ARRAYLANG['TXT_EGOV_ERROR_PROCESSING_ORDER']."\");\n";
        }
        $product_name = eGovLibrary::GetProduktValue('product_name', $product_id);
        $product_amount = eGovLibrary::GetProduktValue('product_price', $product_id);
        $quantity =
            (eGovLibrary::GetProduktValue('product_per_day', $product_id) == 'yes'
                ? $_REQUEST['contactFormField_Quantity'] : 1
            );
//echo("$product_amount * $quantity, $amount<br />");
        if ($product_amount <= 0) {
            return '';
        }
/*
        $FormFields = "id=$product_id&send=1&";
        $arrFields = $this->getFormFields($product_id);
        foreach (array_keys($arrFields) as $fieldId) {
            $FormFields .= 'contactFormField_'.$fieldId.'='.strip_tags(contrexx_addslashes($_REQUEST['contactFormField_'.$fieldId])).'&';
        }
*/
//        $Addname = '';
        if (eGovLibrary::GetProduktValue('product_per_day', $product_id) == 'yes') {
            $FormFields .= 'contactFormField_1000='.$_REQUEST['contactFormField_1000'].'&';
            $FormFields .= 'contactFormField_Quantity='.$_REQUEST['contactFormField_Quantity'];
//            $Addname = $_REQUEST['contactFormField_1000'];
        }

        $objPaypal->add_field('business', eGovLibrary::GetProduktValue('product_paypal_sandbox', $product_id));
        $objPaypal->add_field('return', $paypalUriOk);
        $objPaypal->add_field('cancel_return', $paypalUriNok);
        $objPaypal->add_field('notify_url', $paypalUriIpn);
        $objPaypal->add_field('item_name', $product_name);
        $objPaypal->add_field('amount', $product_amount);
        $objPaypal->add_field('quantity', $quantity);
        $objPaypal->add_field('currency_code', eGovLibrary::GetProduktValue('product_paypal_currency', $product_id));
        $objPaypal->add_field('custom', $order_id);
//die();
        $objPaypal->submit_paypal_post();
        die();
    }


    function paymentPaypalIpn($order_id)
    {
        global $_ARRAYLANG;

//echo("entering IPN, order_id $order_id<br />");
        $product_id = eGovLibrary::GetOrderValue('order_product', $order_id);
        if (empty($product_id)) {
//echo("Warning: no product_id $product_id<br />");
            die(); //return 'alert("'.$_ARRAYLANG['TXT_EGOV_ERROR_PROCESSING_ORDER']."\");\n";
        }
//echo("Info: product_id $product_id<br />");
        $objPaypal = new paypal_class();
        if (!eGovLibrary::GetProduktValue('product_paypal', $product_id)) {
//echo("Warning: PayPal is disabled!");
            // How did we get here?  PayPal isn't even enabled for this product.
            die(); //return 'alert("'.$_ARRAYLANG['TXT_EGOV_PAYPAL_NOT_VALID']."\");\n";
        }
        if (eGovLibrary::GetSettings('set_paypal_ipn') == 0) {
            // PayPal IPN is disabled.
//echo("Warning: IPN is disabled!");
            die(); //return '';
        }
        if (!$objPaypal->validate_ipn()) {
            // Verification failed.
//echo("IPN failed!");
            die(); //return 'alert("'.$_ARRAYLANG['TXT_EGOV_PAYPAL_NOT_VALID']."\");\n";
        }
/*
        // PayPal IPN Confirmation by email
        $subject = 'Instant Payment Notification - Recieved Payment';
        $to = eGovLibrary::GetProduktValue('product_paypal_sandbox', $product_id);
        $body = "An instant payment notification was successfully recieved\n";
        $body .= "from ".$objPaypal->ipn_data['payer_email']." on ".date('m/d/Y');
        $body .= " at ".date('g:i A')."\n\nDetails:\n";
        foreach ($objPaypal->ipn_data as $key => $value) { $body .= "\n$key: $value"; }
        mail($to, $subject, $body);
*/
        // Update the order silently.
        $this->updateOrder($order_id);
    }


    function paymentYellowpay($order_id, $amount)
    {
        global $_ARRAYLANG, $_LANGID;
//eGov::addLog("Entered paymentYellowpay: Order ID $order_id, amount $amount");

//echo("order id $order_id<br />");
        $paymentMethods =
            (!empty($_REQUEST['handler'])
                ? $_REQUEST['handler']
                : eGovLibrary::GetSettings('yellowpay_accepted_payment_methods')
        );
//echo("Payment methods: $paymentMethods<br />");
        // Prepare payment using current settings and customer selection
        $objYellowpay = new Yellowpay(
//            $paymentMethods,
//            eGovLibrary::GetSettings('yellowpay_authorization')
        );

        $product_id = eGovLibrary::GetOrderValue('order_product', $order_id);
        if (empty($product_id)) {
//eGov::addLog("Error: paymentYellowpay: Order ID $order_id: Failed to get product ID");
            return 'alert("'.$_ARRAYLANG['TXT_EGOV_ERROR_PROCESSING_ORDER']."\");\n";
        }
        $quantity =
            (eGovLibrary::GetProduktValue('product_per_day', $product_id) == 'yes'
                ? $_REQUEST['contactFormField_Quantity'] : 1
            );
        $product_amount = (!empty($amount)
            ? $amount
            :   eGovLibrary::GetProduktValue('product_price', $product_id)
              * $quantity
        );
        $FormFields = "id=$product_id&send=1&";
        $arrFields = $this->getFormFields($product_id);
        foreach (array_keys($arrFields) as $fieldId) {
            $FormFields .= 'contactFormField_'.$fieldId.'='.strip_tags(contrexx_addslashes($_REQUEST['contactFormField_'.$fieldId])).'&';
        }
        if (eGovLibrary::GetProduktValue('product_per_day', $product_id) == 'yes') {
            $FormFields .= 'contactFormField_1000='.$_REQUEST['contactFormField_1000'].'&';
            $FormFields .= 'contactFormField_Quantity='.$_REQUEST['contactFormField_Quantity'];
        }

        $languageCode = strtoupper(FWLanguage::getLanguageCodeById($_LANGID));
//echo("langid: $_LANGID, language code: /$languageCode/<br />");
//echo("adding amount /$product_amount/<br />");

        $arrShopOrder = array(
            // From registration confirmation form
            'txtShopId'      => eGovLibrary::GetSettings('yellowpay_shopid'),
            'txtOrderTotal'  => $product_amount,
            'Hash_seed'      => eGovLibrary::GetSettings('yellowpay_hashseed'),
            'txtLangVersion' => $languageCode,
            'txtArtCurrency' => eGovLibrary::GetProduktValue('product_paypal_currency', $product_id),
            'txtOrderIDShop' => $order_id,
            'txtShopPara'    => "source=egov&order_id=$order_id",
            'txtHistoryBack' => false,
            'deliveryPaymentType' => eGovLibrary::GetSettings('yellowpay_authorization'),
            'acceptedPaymentMethods' => $paymentMethods,
        );
//var_export($arrShopOrder);echo("<br />");

        // Yellowbill additional parameters
        if (!empty($_POST['txtESR_Member'])) { $arrShopOrder['txtESR_Member'] = $_POST['txtESR_Member']; }
        if (!empty($_POST['txtESR_Ref'])) { $arrShopOrder['txtESR_Ref'] = $_POST['txtESR_Ref']; }
        if (!empty($_POST['txtBLastName'])) { $arrShopOrder['txtBLastName'] = $_POST['txtBLastName']; }
        if (!empty($_POST['txtBAddr1'])) { $arrShopOrder['txtBAddr1'] = $_POST['txtBAddr1']; }
        if (!empty($_POST['txtBZipCode'])) { $arrShopOrder['txtBZipCode'] = $_POST['txtBZipCode']; }
        if (!empty($_POST['txtBCity'])) { $arrShopOrder['txtBCity'] = $_POST['txtBCity']; }

        $yellowpayForm = $objYellowpay->getForm(
            $arrShopOrder, '', true, eGovLibrary::GetSettings(''));
//echo("got yellowpayform: ".htmlentities($yellowpayForm)."<br />");
        if (count($objYellowpay->arrError) > 0) {
            $strError = "alert(\"Yellowpay could not be initialized:\n";
//            if (_EGOV_DEBUG) {
                $strError .= join("\n", $objYellowpay->arrError);
//            }
            $strError .= '");';
//            return $strError;
//eGov::addLog("Error: paymentYellowpay: Yellowpay reported the following errors:\n$strError");
            die($strError);
        }
//eGov::addLog("Info: paymentYellowpay: Providing form");
        die(
            '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" '.
            '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
            '<html><head><title>Yellowpay</title></head><body>'.
            $yellowpayForm.'</body></html>'
        );
//        die(htmlentities($yellowpayForm));
    }


    function paymentYellowpayVerify($order_id)
    {
        global $_ARRAYLANG;
//eGov::addLog("Info: paymentYellowpayVerify: Entered, Order ID $order_id");

        $result = (isset($_GET['result']) ? $_GET['result'] : 0);
//eGov::addLog("paymentYellowpayVerify: Result is $result for order ID $order_id");
        // Silently process yellowpay notifications and die().
        if (//$result == -1 &&
               isset($_POST['txtOrderIDShop'])
            && isset($_POST['txtTransactionID'])
        ) {
            // Verification
// TODO: Implement hashback
            $order_id = $_POST['txtOrderIDShop'];
            $transaction_id = $_POST['txtTransactionID'];
            if ($transaction_id == '2684') {
//eGov::addLog("paymentYellowpayVerify: Going to update order ID $order_id");
                $this->updateOrder($order_id);
                die();
            }
//eGov::addLog("Warning: paymentYellowpayVerify: Wrong transaction ID /$transaction_id/");
            die();
        }

        if (isset($_GET['order_id'])) {
            $order_id = $_GET['order_id'];
            $product_id = eGovLibrary::GetOrderValue('order_product', $order_id);
            if (empty($product_id)) {
//eGov::addLog("Error: paymentYellowpayVerify: Order ID $order_id, failed to get product ID");
                return 'alert("'.$_ARRAYLANG['TXT_EGOV_ERROR_PROCESSING_ORDER']."\");\n";
            }
            switch ($result) {
              case 0:
//eGov::addLog("Warning: paymentYellowpayVerify: Order ID $order_id: Payment failed");
                // Payment failed
                break;
              case 1:
//eGov::addLog("Info: paymentYellowpayVerify: Order ID $order_id, payment complete");
                // The payment has been completed.
                // The notification with result == -1 will update the order.
                // This case only redirects the customer to the list page with
                // an appropriate message according to the status of the order.
                $order_state = eGovLibrary::GetOrderValue('order_state', $order_id);
                if ($order_state == 1) {
//eGov::addLog("Success: paymentYellowpayVerify: Order ID $order_id, order status is $order_state");
                    $product_id = eGovLibrary::GetOrderValue('order_product', $order_id);
                    return eGov::getSuccessMessage($product_id);
                }
//eGov::addLog("Warning: paymentYellowpayVerify: Order ID $order_id, order status is $order_state");
                break;
              case 2:
//eGov::addLog("Info: paymentYellowpayVerify: Order ID $order_id, payment cancelled");
                // Payment was cancelled
                return 'alert("'.$_ARRAYLANG['TXT_EGOV_YELLOWPAY_CANCEL']."\");\n";
            }
        }
//eGov::addLog("Info: paymentYellowpayVerify: Order ID $order_id, returning error message");
        return 'alert("'.$_ARRAYLANG['TXT_EGOV_YELLOWPAY_NOT_VALID']."\");\n";
    }


    function _ProductsList()
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        $result = '';
        if (isset($_REQUEST['result'])) {
            // Returned from payment
            $result = $this->payment();
        } elseif (isset($_REQUEST['send'])) {
            // Store order and launch payment, if necessary
            $result = $this->_saveOrder();
        }
        // Fix/replace HTML and line breaks, which will all fail in the
        // alert() call.
        $result =
            html_entity_decode(
                strip_tags(
                    preg_replace(
                        '/\<br\s*?\/?\>/', '\n',
                        preg_replace('/[\n\r]/', '', $result)
                    )
                ), ENT_QUOTES, CONTREXX_CHARSET
            );
        $this->objTemplate->setVariable(
            'EGOV_JS',
            "<script type=\"text/javascript\">\n".
            "// <![CDATA[\n$result\n// ]]>\n".
            "</script>\n"
        );

        // Show products list
        $query = "
            SELECT product_id, product_name, product_desc
              FROM ".DBPREFIX."module_egov_products
             WHERE product_status=1
             ORDER BY product_orderby, product_name
        ";
        $objResult = $objDatabase->Execute($query );
        if (!$objResult || $objResult->EOF) {
            $this->objTemplate->hideBlock('egovProducts');
            return;
        }
        while (!$objResult->EOF) {
            $this->objTemplate->setVariable(array(
                'EGOV_PRODUCT_TITLE' => $objResult->fields['product_name'],
                'EGOV_PRODUCT_ID' => $objResult->fields['product_id'],
                'EGOV_PRODUCT_DESC' => $objResult->fields['product_desc'],
                'EGOV_PRODUCT_LINK' => 'index.php?section=egov&amp;cmd=detail&amp;id='.$objResult->fields['product_id'],
            ));
            $this->objTemplate->parse('egovProducts');
            $objResult->MoveNext();
        }
    }


    function _ProductDetail()
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        if (empty($_REQUEST['id'])) {
            return;
        }
/*
        $AddSource = '';
        if (isset($_REQUEST['handler'])) {
            $AddSource =
                "\n\n<script type=\"text/javascript\">\n// <![CDATA[\n".
                ($_REQUEST['payment'] == 'cancel'
                  ? 'alert("'.$_ARRAYLANG['TXT_EGOV_PAYPAL_CANCEL']."\");\n"
                  : ''
                ).
                "// ]]>\n</script>\n";
        }
*/
        $query = "
            SELECT product_id, product_name, product_desc, product_price ".
//                   product_per_day, product_quantity, product_target_email,
//                   product_target_url, product_message
             "FROM ".DBPREFIX."module_egov_products
             WHERE product_id=".$_REQUEST['id'];
        $objResult = $objDatabase->Execute($query);
        if ($objResult && $objResult->RecordCount()) {
            $product_id = $objResult->fields['product_id'];
            $FormSource = $this->getSourceCode($product_id);
            $this->objTemplate->setVariable(array(
                'EGOV_PRODUCT_TITLE' => $objResult->fields['product_name'],
                'EGOV_PRODUCT_ID' => $objResult->fields['product_id'],
                'EGOV_PRODUCT_DESC' => $objResult->fields['product_desc'],
                'EGOV_PRODUCT_PRICE' => $objResult->fields['product_price'],
                'EGOV_FORM' => $FormSource,
//.$AddSource,
            ));
        }
        if ($this->objTemplate->blockExists('egov_price')) {
            if (intval($objResult->fields['product_price']) > 0) {
                $this->objTemplate->touchBlock('egov_price');
            } else {
                $this->objTemplate->hideBlock('egov_price');
            }
        }
    }


    /**
     * Returns a string containing Javascript for displaying the appropriate
     * success message and/or redirects for the product ID given.
     * @param   integer   $product_id     The product ID
     * @return  string                    The Javascript string
     * @static
     */
    //static
    function getSuccessMessage($product_id)
    {
        // Seems that we need to clear the $_POST array to prevent it from
        // being reposted on the target page.
        unset($_POST);
        unset($_REQUEST);
        //unset($_GET);

        $ReturnValue = '';
        if (eGovLibrary::GetProduktValue('product_message', $product_id) != '') {
            $AlertMessageTxt = preg_replace(array('/(\n|\r\n)/', '/<br\s?\/?>/i'), '\n', addslashes(html_entity_decode(eGovLibrary::GetProduktValue('product_message', $product_id), ENT_QUOTES, CONTREXX_CHARSET)));
//eGov::addLog("getSuccessMessage($product_id): eGovLibrary::getOrderValues(): Adding product message");
            $ReturnValue = 'alert("'.$AlertMessageTxt.'");'."\n";
        }
        if (eGovLibrary::GetProduktValue('product_target_url', $product_id) != '') {
//eGov::addLog("getSuccessMessage($product_id): eGovLibrary::getOrderValues(): Adding redirect to target URI");
            return
                $ReturnValue.
                'document.location.href="'.
                eGovLibrary::GetProduktValue('product_target_url', $product_id).
                '";'."\n";
        }
//eGov::addLog("getSuccessMessage($product_id): eGovLibrary::getOrderValues(): Adding redirect to home page");
//            $ReturnValue .= "history.go(-2);\n";
        return
            $ReturnValue.
            'document.location.href="'.$_SERVER['PHP_SELF']."\";\n";
    }



    function updateOrderStatus($order_id, $status)
    {
        global $objDatabase;

        $query = "
            UPDATE ".DBPREFIX."module_egov_orders
               SET order_state=$status
             WHERE order_id=$order_id
        ";
        if (!$objDatabase->Execute($query)) {
//echo("Query error:<br />$query<br />");
            return false;
        }
        $query = "
            UPDATE ".DBPREFIX."module_egov_product_calendar
               SET calendar_act=$status
             WHERE calendar_order=$order_id
        ";
        if (!$objDatabase->Execute($query)) {
//echo("Query error:<br />$query<br />");
            return false;
        }
        return true;
    }


    /**
     * Add a line to the log file
     *
     * Prepends the current date and time to the string,
     * adds a line terminator and appends this to the log file.
     * Silently terminates if the log file cannot be opened for appending.
     * @param   string   $strLine     The entry to be logged
     */
    function addLog($strLine)
    {
        $fp = fopen('egov.log', 'a');
        if (!$fp) return;
        fwrite($fp, date('Ymd His')." $strLine\n");
        fclose($fp);
    }
}

?>
