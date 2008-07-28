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

/**
 * Debug mode
 */
define('_EGOV_DEBUG', 0);

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
    function __construct($pageContent)
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
                $strResult = $this->_ProductDetail();
            break;
            case 'order_edit':
                $strResult = $this->editOrder();
            break;
            default:
                $strResult = $this->_ProductsList();
        }
        if (!empty($strResult)) {
            $this->objTemplate->setVariable('EGOV_STATUS_MESSAGE', $strResult);
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

        if (empty($_REQUEST['productId'])) {
            return eGov::getAlert(
                $_ARRAYLANG['TXT_EGOV_MISSING_PRODUCT_ID'],
                'index.php?section=egov'
            );
        }
        $productId = intval($_REQUEST['productId']);
        $datum_db = date('Y-m-d H:i:s');
        $ip_adress = $_SERVER['REMOTE_ADDR'];

        $arrFields = eGovLibrary::getFormFields($productId);
        $FormValue = '';
        foreach ($arrFields as $fieldId => $arrField) {
            $FormValue .= $arrField['name'].'::'.strip_tags(contrexx_addslashes($_REQUEST['contactFormField_'.$fieldId])).';;';
        }

        $quantity = 0;
        $product_amount = eGovLibrary::GetProduktValue('product_price', $productId);
        if (eGovLibrary::GetProduktValue('product_per_day', $productId) == 'yes') {
            $quantity = intval($_REQUEST['contactFormField_Quantity']);
            if ($quantity <= 0) {
                return 'alert("'.$_ARRAYLANG['TXT_EGOV_SPECIFY_COUNT'].'");history.go(-1);';
            }
            $FormValue =
                $_ARRAYLANG['TXT_EGOV_QUANTITY'].'::'.$quantity.';;'.
                eGovLibrary::GetSettings('set_calendar_date_label').'::'.
                strip_tags(contrexx_addslashes($_REQUEST['contactFormField_1000'])).';;'.
                $FormValue;
        }

        $objDatabase->Execute("
            INSERT INTO ".DBPREFIX."module_egov_orders (
                order_date, order_ip, order_product, order_values
            ) VALUES (
                '$datum_db', '$ip_adress', '$productId', '$FormValue'
            )
        ");
        $orderId = $objDatabase->Insert_ID();
        if (eGovLibrary::GetProduktValue('product_per_day', $productId) == 'yes') {
            list ($calD, $calM, $calY) = split('[.]', $_REQUEST['contactFormField_1000']);
            for($x = 0; $x < $quantity; ++$x) {
                $objDatabase->Execute("
                    INSERT INTO ".DBPREFIX."module_egov_product_calendar (
                        calendar_product, calendar_order, calendar_day,
                        calendar_month, calendar_year
                    ) VALUES (
                        '$productId', '$orderId', '$calD',
                        '$calM', '$calY'
                    )
                ");
            }
        }

        $ReturnValue = '';
        $newStatus = 1;
        // Handle any kind of payment request
        if (!empty($_REQUEST['handler'])) {
            $ReturnValue = $this->payment($orderId, $product_amount);
            if (intval($ReturnValue) > 0) {
                $newStatus = $ReturnValue;
                $ReturnValue = '';
            }
            if (!empty($ReturnValue)) return $ReturnValue;
        }

        // If no more payment handling is required,
        // update the order right away
        if (eGov::GetOrderValue('order_state', $orderId) == 0) {
            // If any non-empty string is returned, an error occurred.
            $ReturnValue = eGov::updateOrder($orderId, $newStatus);
            if (!empty($ReturnValue)) return $ReturnValue;
        }

        return eGov::getSuccessMessage($productId);
    }


    /**
     * Update the order status and send the confirmation mail
     * according to the settings
     *
     * The resulting javascript code displays a message box or
     * does some page redirect.
     * @param   integer   $orderId       The order ID
     * @return  string                    Javascript code
     * @static
     */
    static function updateOrder($orderId, $newStatus=1)
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        $productId = eGov::getOrderValue('order_product', $orderId);
        if (empty($productId)) {
            return 'alert("'.$_ARRAYLANG['TXT_EGOV_ERROR_UPDATING_ORDER'].'");'."\n";
        }

        // Has this order been updated already?
        $orderStatus = eGovLibrary::GetOrderValue('order_state', $orderId);
        if ($orderStatus != 0) {
            // Do not resend mails!
            return '';
        }

        $arrFields = eGovLibrary::getOrderValues($orderId);
        $FormValue4Mail = '';
        $arrMatch = array();
        foreach ($arrFields as $name => $value) {
            // If the value matches a calendar date, prefix the string with
            // the day of the week
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
        // Bestelleingang-Benachrichtigung || Mail für den Administrator
        $recipient = eGovLibrary::GetProduktValue('product_target_email', $productId);
        if (empty($recipient)) {
            $recipient = eGovLibrary::GetSettings('set_orderentry_recipient');
        }
        if (!empty($recipient)) {
            $SubjectText = str_replace('[[PRODUCT_NAME]]', html_entity_decode(eGovLibrary::GetProduktValue('product_name', $productId)), eGovLibrary::GetSettings('set_orderentry_subject'));
            $SubjectText = html_entity_decode($SubjectText);
            $BodyText = str_replace('[[ORDER_VALUE]]', $FormValue4Mail, eGovLibrary::GetSettings('set_orderentry_email'));
            $BodyText = html_entity_decode($BodyText);
            $replyAddress = eGovLibrary::GetEmailAdress($orderId);
            if (empty($replyAddress)) {
                $replyAddress = eGovLibrary::GetSettings('set_orderentry_sender');
            }
            if (@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') {
                $objMail = new phpmailer();
                if (!empty($_CONFIG['coreSmtpServer']) && @include_once ASCMS_CORE_PATH.'/SmtpSettings.class.php') {
                    $arrSmtp = SmtpSettings::getSmtpAccount($_CONFIG['coreSmtpServer']);
                    if ($arrSmtp !== false) {
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
        if (   eGovLibrary::GetProduktValue('product_electro', $productId) == 1
            || eGovLibrary::GetProduktValue('product_autostatus', $productId) == 1
        ) {
            eGovLibrary::updateOrderStatus($orderId, $newStatus);
            $TargetMail = eGovLibrary::GetEmailAdress($orderId);
            if ($TargetMail != '') {
                $FromEmail = eGovLibrary::GetProduktValue('product_sender_email', $productId);
                if ($FromEmail == '') {
                    $FromEmail = eGovLibrary::GetSettings('set_sender_email');
                }
                $FromName = eGovLibrary::GetProduktValue('product_sender_name', $productId);
                if ($FromName == '') {
                    $FromName = eGovLibrary::GetSettings('set_sender_name');
                }
                $SubjectDB = eGovLibrary::GetProduktValue('product_target_subject', $productId);
                if ($SubjectDB == '') {
                    $SubjectDB = eGovLibrary::GetSettings('set_state_subject');
                }
                $SubjectText = str_replace('[[PRODUCT_NAME]]', html_entity_decode(eGovLibrary::GetProduktValue('product_name', $productId)), $SubjectDB);
                $SubjectText = html_entity_decode($SubjectText);
                $BodyDB = eGovLibrary::GetProduktValue('product_target_body', $productId);
                if ($BodyDB == '') {
                    $BodyDB = eGovLibrary::GetSettings('set_state_email');
                }
                $BodyText = str_replace('[[ORDER_VALUE]]', $FormValue4Mail, $BodyDB);
                $BodyText = str_replace('[[PRODUCT_NAME]]', html_entity_decode(eGovLibrary::GetProduktValue('product_name', $productId)), $BodyText);
                $BodyText = html_entity_decode($BodyText);
                if (@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') {
                    $objMail = new phpmailer();
                    if ($_CONFIG['coreSmtpServer'] > 0 && @include_once ASCMS_CORE_PATH.'/SmtpSettings.class.php') {
                        $arrSmtp = SmtpSettings::getSmtpAccount($_CONFIG['coreSmtpServer']);
                        if ($arrSmtp !== false) {
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
                    if (eGovLibrary::GetProduktValue('product_electro', $productId) == 1) {
                        $objMail->AddAttachment(ASCMS_PATH.eGovLibrary::GetProduktValue('product_file', $productId));
                    }
                    $objMail->Send();
                }
            }
        }
        return '';
    }


    function payment($orderId=0, $amount=0)
    {
        $handler = $_REQUEST['handler'];
        switch ($handler) {
          case 'paypal':
            $orderId =
                (!empty($_POST['custom'])
                  ? $_POST['custom']
                  : $orderId
                );
            return $this->paymentPaypal($orderId, $amount);
          // Payment requests
          // The following are all handled by Yellowpay.
          case 'PostFinanceCard':
          case 'yellownet':
          case 'Master':
          case 'Visa':
          case 'Amex':
          case 'Diners':
          case 'yellowbill':
            return $this->paymentYellowpay($orderId, $amount);
          // Returning from Yellowpay
          case 'yellowpay':
            return $this->paymentYellowpayVerify($orderId);
          // Silently ignore invalid payment requests
        }
        // Unknown payment handler provided.
        // Should be one of the alternative payment methods,
        // use the alternative status as return value.
        return 3;
        //return $_ARRAYLANG['TXT_EGOV_PAYMENT_NOT_COMPLETED'];
    }


    function paymentPaypal($orderId, $amount=0)
    {
        global $_ARRAYLANG;

        if (isset($_GET['result'])) {
            $result = $_GET['result'];
            switch ($result) {
              case -1:
                // Go validate PayPal IPN
                $this->paymentPaypalIpn($orderId, $amount);
                die();
              case 0:
                // Payment failed
                break;
              case 1:
                // The payment has been completed.
                // The notification with result == -1 will update the order.
                // This case only redirects the customer to the list page with
                // an appropriate message according to the status of the order.
                $order_state = eGovLibrary::GetOrderValue('order_state', $orderId);
                if ($order_state == 1) {
                    $productId = eGovLibrary::GetOrderValue('order_product', $orderId);
                    return eGov::getSuccessMessage($productId);
                } elseif ($order_state == 0) {
                    if (eGovLibrary::GetSettings('set_paypal_ipn') == 1) {
                        return 'alert("'.$_ARRAYLANG['TXT_EGOV_PAYPAL_IPN_PENDING']."\");\n";
                    }
                }
                break;
              case 2:
                // Payment was cancelled
                return 'alert("'.$_ARRAYLANG['TXT_EGOV_PAYPAL_CANCEL']."\");\n";
            }
            return 'alert("'.$_ARRAYLANG['TXT_EGOV_PAYPAL_NOT_VALID']."\");\n";
        }

        $productId = eGov::getOrderValue('order_product', $orderId);
        if (empty($productId)) {
            return 'alert("'.$_ARRAYLANG['TXT_EGOV_ERROR_UPDATING_ORDER'].'");'."\n";
        }

        // Prepare payment
        $paypalUriIpn = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?section=egov&handler=paypal&result=-1";
        $paypalUriNok = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?section=egov&handler=paypal&result=0";
        $paypalUriOk  = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?section=egov&handler=paypal&result=1";
        $objPaypal = new paypal_class();

        $productId = eGovLibrary::GetOrderValue('order_product', $orderId);
        if (empty($productId)) {
            return 'alert("'.$_ARRAYLANG['TXT_EGOV_ERROR_PROCESSING_ORDER']."\");\n";
        }
        $product_name = eGovLibrary::GetProduktValue('product_name', $productId);
// Alternatively, the reservation date may be added to the product name
//        $date =
//            (eGovLibrary::GetProduktValue('product_per_day', $productId) == 'yes'
//                ? ' '.$_REQUEST['contactFormField_1000']
//                : ''
//            );
//        $product_name = eGovLibrary::GetProduktValue('product_name', $productId).$date;
        $product_amount = eGovLibrary::GetProduktValue('product_price', $productId);
        $quantity =
            (eGovLibrary::GetProduktValue('product_per_day', $productId) == 'yes'
                ? $_REQUEST['contactFormField_Quantity'] : 1
            );
        // The amount may be overridden by the optional parameter
        if ($amount > 0) {
            $product_amount = $amount;
        };

        if ($product_amount <= 0) {
            return '';
        }
        $objPaypal->add_field('business', eGovLibrary::GetProduktValue('product_paypal_sandbox', $productId));
        $objPaypal->add_field('return', $paypalUriOk);
        $objPaypal->add_field('cancel_return', $paypalUriNok);
        $objPaypal->add_field('notify_url', $paypalUriIpn);
        $objPaypal->add_field('item_name', $product_name);
        $objPaypal->add_field('amount', $product_amount);
        $objPaypal->add_field('quantity', $quantity);
        $objPaypal->add_field('currency_code', eGovLibrary::GetProduktValue('product_paypal_currency', $productId));
        $objPaypal->add_field('custom', $orderId);
//die();
        $objPaypal->submit_paypal_post();
        die();
    }


    function paymentPaypalIpn($orderId)
    {
        global $_ARRAYLANG;

        $productId = eGovLibrary::GetOrderValue('order_product', $orderId);
        if (empty($productId)) {
            die(); //return 'alert("'.$_ARRAYLANG['TXT_EGOV_ERROR_PROCESSING_ORDER']."\");\n";
        }
        $objPaypal = new paypal_class();
        if (!eGovLibrary::GetProduktValue('product_paypal', $productId)) {
            // How did we get here?  PayPal isn't even enabled for this product.
            die(); //return 'alert("'.$_ARRAYLANG['TXT_EGOV_PAYPAL_NOT_VALID']."\");\n";
        }
        if (eGovLibrary::GetSettings('set_paypal_ipn') == 0) {
            // PayPal IPN is disabled.
            die(); //return '';
        }
        if (!$objPaypal->validate_ipn()) {
            // Verification failed.
            die(); //return 'alert("'.$_ARRAYLANG['TXT_EGOV_PAYPAL_NOT_VALID']."\");\n";
        }
/*
        // PayPal IPN Confirmation by email
        $subject = 'Instant Payment Notification - Recieved Payment';
        $to = eGovLibrary::GetProduktValue('product_paypal_sandbox', $productId);
        $body = "An instant payment notification was successfully recieved\n";
        $body .= "from ".$objPaypal->ipn_data['payer_email']." on ".date('m/d/Y');
        $body .= " at ".date('g:i A')."\n\nDetails:\n";
        foreach ($objPaypal->ipn_data as $key => $value) { $body .= "\n$key: $value"; }
        mail($to, $subject, $body);
*/
        // Update the order silently.
        $this->updateOrder($orderId);
    }


    function paymentYellowpay($orderId, $amount)
    {
        global $_ARRAYLANG, $_LANGID;

        $paymentMethods =
            (!empty($_REQUEST['handler'])
                ? $_REQUEST['handler']
                : eGovLibrary::GetSettings('yellowpay_accepted_payment_methods')
        );
        // Prepare payment using current settings and customer selection
        $objYellowpay = new Yellowpay(
//            $paymentMethods,
//            eGovLibrary::GetSettings('yellowpay_authorization')
        );

        $productId = eGovLibrary::GetOrderValue('order_product', $orderId);
        if (empty($productId)) {
            return 'alert("'.$_ARRAYLANG['TXT_EGOV_ERROR_PROCESSING_ORDER']."\");\n";
        }
        $quantity =
            (eGovLibrary::GetProduktValue('product_per_day', $productId) == 'yes'
                ? $_REQUEST['contactFormField_Quantity'] : 1
            );
        $product_amount = (!empty($amount)
            ? $amount
            :   eGovLibrary::GetProduktValue('product_price', $productId)
              * $quantity
        );
        $FormFields = "id=$productId&send=1&";
        $arrFields = $this->getFormFields($productId);
        foreach (array_keys($arrFields) as $fieldId) {
            $FormFields .= 'contactFormField_'.$fieldId.'='.strip_tags(contrexx_addslashes($_REQUEST['contactFormField_'.$fieldId])).'&';
        }
        if (eGovLibrary::GetProduktValue('product_per_day', $productId) == 'yes') {
            $FormFields .= 'contactFormField_1000='.$_REQUEST['contactFormField_1000'].'&';
            $FormFields .= 'contactFormField_Quantity='.$_REQUEST['contactFormField_Quantity'];
        }

        $languageCode = strtoupper(FWLanguage::getLanguageCodeById($_LANGID));
        $arrShopOrder = array(
            // From registration confirmation form
            'txtShopId'      => eGovLibrary::GetSettings('yellowpay_shopid'),
            'txtOrderTotal'  => $product_amount,
            'Hash_seed'      => eGovLibrary::GetSettings('yellowpay_hashseed'),
            'txtLangVersion' => $languageCode,
            'txtArtCurrency' => eGovLibrary::GetProduktValue('product_paypal_currency', $productId),
            'txtOrderIDShop' => $orderId,
            'txtShopPara'    => "source=egov&order_id=$orderId",
            'txtHistoryBack' => false,
            'deliveryPaymentType' => eGovLibrary::GetSettings('yellowpay_authorization'),
            'acceptedPaymentMethods' => $paymentMethods,
        );

        // Get auxiliary input field names and values from the order
        $arrOrderValue = eGovLibrary::getOrderValues($orderId);

// NOTE: The $_POST array field names here must match the names used
// in the form!
// TODO: Use language sensitive $_ARRAYLANG entries as indices
        if (!empty($_POST['txtESR_Member'])) {
            $arrShopOrder['txtESR_Member'] = $_POST['txtESR_Member'];
        }
        if (!empty($_POST['txtESR_Ref'])) {
            $arrShopOrder['txtESR_Ref'] = $_POST['txtESR_Ref'];
        }
        if (!empty($_POST['txtBLastName'])) {
            $arrShopOrder['txtBLastName'] = $_POST['txtBLastName'];
        } elseif (!empty($_POST['Nachname'])) {
            $arrShopOrder['txtBLastName'] = $_POST['Nachname'];
        } elseif (!empty($arrOrderValue['Nachame'])) {
            $arrShopOrder['txtBLastName'] = $arrOrderValue['Nachame'];
        }
        if (!empty($_POST['txtBAddr1'])) {
            $arrShopOrder['txtBAddr1'] = $_POST['txtBAddr1'];
        } elseif (!empty($_POST['Adresse'])) {
            $arrShopOrder['txtBLastName'] = $_POST['Adresse'];
        } elseif (!empty($arrOrderValue['Adresse'])) {
            $arrShopOrder['txtBAddr1'] = $arrOrderValue['Adresse'];
        }
        if (!empty($_POST['txtBZipCode'])) {
            $arrShopOrder['txtBZipCode'] = $_POST['txtBZipCode'];
        } elseif (!empty($_POST['PLZ'])) {
            $arrShopOrder['txtBLastName'] = $_POST['PLZ'];
        } elseif (!empty($arrOrderValue['PLZ'])) {
            $arrShopOrder['txtBZipCode'] = $arrOrderValue['PLZ'];
        }
        if (!empty($_POST['txtBCity'])) {
            $arrShopOrder['txtBCity'] = $_POST['txtBCity'];
        } elseif (!empty($_POST['Ort'])) {
            $arrShopOrder['txtBLastName'] = $_POST['Ort'];
        } elseif (!empty($arrOrderValue['Ort'])) {
            $arrShopOrder['txtBCity'] = $arrOrderValue['Ort'];
        }
        if (!empty($_POST['Anrede'])) {
            $arrShopOrder['txtBTitle'] = $_POST['Anrede'];
        } elseif (!empty($arrOrderValue['Anrede'])) {
            $arrShopOrder['txtBTitle'] = $arrOrderValue['Anrede'];
        }
        if (!empty($_POST['Vorname'])) {
            $arrShopOrder['txtBFirstName'] = $_POST['Vorname'];
        } elseif (!empty($arrOrderValue['Vorname'])) {
            $arrShopOrder['txtBFirstName'] = $arrOrderValue['Vorname'];
        }
        if (!empty($_POST['Land'])) {
            $arrShopOrder['txtBCountry'] = $_POST['Land'];
        } elseif (!empty($arrOrderValue['Land'])) {
            $arrShopOrder['txtBCountry'] = $arrOrderValue['Land'];
        }
        if (!empty($_POST['Telefon'])) {
            $arrShopOrder['txtBTel'] = $_POST['Telefon'];
        } elseif (!empty($arrOrderValue['Telefon'])) {
            $arrShopOrder['txtBTel'] = $arrOrderValue['Telefon'];
        }
        if (!empty($_POST['Fax'])) {
            $arrShopOrder['txtBFax'] = $_POST['Fax'];
        } elseif (!empty($arrOrderValue['Fax'])) {
            $arrShopOrder['txtBFax'] = $arrOrderValue['Fax'];
        }
        if (!empty($_POST['E-Mail'])) {
            $arrShopOrder['txtBEmail'] = $_POST['E-Mail'];
        } elseif (!empty($arrOrderValue['E-Mail'])) {
            $arrShopOrder['txtBEmail'] = $arrOrderValue['E-Mail'];
        }

        $isTest = eGovLibrary::GetSettings('yellowpay_use_testserver');
        $yellowpayForm = $objYellowpay->getForm(
            $arrShopOrder, '', true, $isTest
            // Without autopost (for debugging and testing):
            //$arrShopOrder, '', false, $isTest
        );
        if (count($objYellowpay->arrError) > 0) {
            $strError = "alert(\"Yellowpay could not be initialized:\n";
            if (_EGOV_DEBUG) {
                $strError .= join("\n", $objYellowpay->arrError);
            }
            $strError .= '");';
            return $strError;
        }
        die(
            '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" '.
            '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
            '<html><head><title>Yellowpay</title></head><body>'.
            $yellowpayForm.'</body></html>'
        );
        // Test/debug: die(htmlentities($yellowpayForm));
    }


    function paymentYellowpayVerify($orderId)
    {
        global $_ARRAYLANG;

        $result = (isset($_GET['result']) ? $_GET['result'] : 0);
        // Silently process yellowpay notifications and die().
        if (//$result == -1 &&
               isset($_POST['txtOrderIDShop'])
            && isset($_POST['txtTransactionID'])
        ) {
            // Verification
// TODO: Implement hashback
            $orderId = $_POST['txtOrderIDShop'];
/*  Test server only!
            $transaction_id = $_POST['txtTransactionID'];
            if ($transaction_id == '2684') {
*/
                $this->updateOrder($orderId);
                die();
/*
            }
            die();
*/
        }

        $strReturn = '';
        if (isset($_GET['order_id'])) {
            $orderId = $_GET['order_id'];
            $productId = eGovLibrary::GetOrderValue('order_product', $orderId);
            if (empty($productId)) {
                $strReturn = 'alert("'.$_ARRAYLANG['TXT_EGOV_ERROR_PROCESSING_ORDER']."\");\n";
            }
            switch ($result) {
              case 0:
                // Payment failed
                $strReturn = 'alert("'.$_ARRAYLANG['TXT_EGOV_YELLOWPAY_NOT_VALID']."\");\n";
                break;
              case 1:
                // The payment has been completed.
                // The notification with result == -1 will update the order.
                // This case only redirects the customer with
                // an appropriate message according to the status of the order.
                $order_state = eGovLibrary::GetOrderValue('order_state', $orderId);
                if ($order_state == 1) {
                    $productId = eGovLibrary::GetOrderValue('order_product', $orderId);
                    $strReturn = eGov::getSuccessMessage($productId);
                } else {
                    $strReturn = 'alert("'.$_ARRAYLANG['TXT_EGOV_YELLOWPAY_NOT_VALID']."\");\n";
                }
                break;
              case 2:
                // Payment was cancelled
                $strReturn = 'alert("'.$_ARRAYLANG['TXT_EGOV_YELLOWPAY_CANCEL']."\");\n";
            }
        }
        return
            $strReturn.
            'document.location.href="'.$_SERVER['PHP_SELF']."?section=egov\";\n";
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
                'EGOV_PRODUCT_LINK' => 'index.php?section=egov&amp;cmd=detail&amp;productId='.$objResult->fields['product_id'],
            ));
            $this->objTemplate->parse('egovProducts');
            $objResult->MoveNext();
        }
    }


    function _ProductDetail()
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        if (empty($_REQUEST['productId'])) {
            return;
        }
        $query = "
            SELECT product_id, product_name, product_desc, product_price ".
             "FROM ".DBPREFIX."module_egov_products
             WHERE product_id=".$_REQUEST['productId'];
        $objResult = $objDatabase->Execute($query);
        if ($objResult && $objResult->RecordCount()) {
            $productId = $objResult->fields['product_id'];
            $FormSource = eGovLibrary::getSourceCode($productId);
            $this->objTemplate->setVariable(array(
                'EGOV_PRODUCT_TITLE' => $objResult->fields['product_name'],
                'EGOV_PRODUCT_ID' => $objResult->fields['product_id'],
                'EGOV_PRODUCT_DESC' => $objResult->fields['product_desc'],
                'EGOV_PRODUCT_PRICE' => $objResult->fields['product_price'],
                'EGOV_FORM' => $FormSource,
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


    function editOrder()
    {
        global $_ARRAYLANG;

        // Verify that all parameters are present
        if (empty($_GET['orderId']) || empty($_GET['hash'])) {
            return eGov::getAlert(
                $_ARRAYLANG['TXT_EGOV_MISSING_ORDER_PARAMETERS'],
                'index.php?section=egov'
            );

        }
        // Verify that all parameters are correct
        $orderId = $_GET['orderId'];
        $hash = $_GET['hash'];
//echo("id: $orderId, made hash: ".eGovLibrary::getOrderHash($orderId)."<br />");die();
        if (!eGovLibrary::verifyOrderHash($orderId, $hash)) {
            return eGov::getAlert(
                $_ARRAYLANG['TXT_EGOV_INVALID_ORDER_PARAMETERS'],
                'index.php?section=egov'
//                'index.php?section=egov&cmd=order_edit&orderId='.$orderId.
//                '&hash='.eGovLibrary::getOrderHash($orderId)
            );
        }

        // So the data has been verified.
        // Verify the order date now and disallow editing within two days
        // prior to the event
        $arrOrderValues = eGovLibrary::getOrderValues($orderId);
        $orderDate = $arrOrderValues['Reservieren für das ausgewählte Datum'];
        // Day, month, year
        $arrDate = array();
        // If the date cannot be determined, the timestamp will still
        // be set to the current time, and thus the order can't be edited
        // (there must be something wrong with it anyway).
        $timestampOrder = time();
//echo("Got order date $orderDate<br />");
        if (preg_match('/(\d{1,2})\.(\d{1,2})\.(\d{4})/', $orderDate, $arrDate)) {
            // [Hour, min, sec,] month, day, year
            $timestampOrder = mktime(
                null, null, null, $arrDate[2], $arrDate[1], $arrDate[3]
            );
//echo("1) Made order timestamp $timestampOrder, date ".date('d.m.Y H:i:s', $timestampOrder)."<br />");
        }
        // Less than two days before the event?
        if (strtotime('+2 days') > $timestampOrder) {
//echo("2) Now timestamp $timestampNow, date ".date('d.m.Y H:i:s', $timestampNow)."<br />"."Order timestamp $timestampOrder, date ".date('d.m.Y H:i:s', $timestampOrder)."<br />");
            return eGov::getAlert(
                $_ARRAYLANG['TXT_EGOV_CANNOT_EDIT_PAST_DATE'],
                'index.php?section=egov'
            );
        }

        // If the order has been cancelled already, it cannot be edited anymore.
        $orderStatus = eGovLibrary::GetOrderValue('order_state', $orderId);
        if ($orderStatus == 2) {
            return eGov::getAlert(
                $_ARRAYLANG['TXT_EGOV_ORDER_CANCELLED_ALREADY'],
                'index.php?section=egov'
            );
        }

        // See if the customer wants to cancel her reservation now.
        if (!empty($_GET['cancel'])) {
//echo("going to cancel the order with ID $orderId<br />");
            if (eGovLibrary::cancelOrder($orderId)) {
//echo("order with ID $orderId cancelled successfully<br />");
                // Send confirmation e-mails to admin and customer.
                // Ignore the return value.
                eGovLibrary::sendConfirmationMail($orderId);
                return eGov::getAlert(
                    $_ARRAYLANG['TXT_EGOV_ORDER_CANCELLED'],
                    'index.php?section=egov'
                );
            }
//echo("failed to cancel the order with ID $orderId<br />");
            return eGov::getAlert(
                $_ARRAYLANG['TXT_EGOV_ORDER_CANCELLING_FAILED'],
                'index.php?section=egov'
            );
        }

        if (isset($_POST['order_store'])) {
            if ($this->_saveOrder()) {
                return eGov::getAlert(
                    $_ARRAYLANG['TXT_EGOV_ORDER_UPDATED'],
                    'index.php?section=egov'
                );
            }
            return eGov::getAlert(
                $_ARRAYLANG['TXT_EGOV_ORDER_UPDATING_FAILED'],
                'index.php?section=egov'
            );
        }

        // Set up the order for editing.
        $productId = eGovLibrary::GetOrderValue('order_product', $orderId);
        $this->objTemplate->setVariable(
// TODO: use the preview flag as a switch to fill in the order data
            'EGOV_FORM', eGovLibrary::getSourceCode($productId, $orderId)
        );
/*  Template fields:
[[EGOV_PRODUCT_TITLE]]
[[EGOV_STATUS_MESSAGE]]
[[EGOV_FORM]]
 */
//echo(var_export($arrOrderValues, true)."<br />");
//$hash = eGovLibrary::getOrderHash($orderId);
//echo("Hash $hash<br />");
        return '';

    }


    /**
     * Returns a javascript string containing an alert box with the
     * message, if any, and/or redirect to the target URI, if any.
     *
     * Note that this also adds the appropriate script tags.
     * When both arguments are empty, only the script tags are returned.
     * Any HTML entities in the message string are replaced by their
     * repective characters.
     * @param   string  $strMessage     The optional message
     * @param   string  $strTargetUri   The optional target URI
     * @return  string                  The javascript
     */
    function getAlert($strMessage='', $strTargetUri='')
    {
        return
            '<script language="JavaScript" type="text/javascript">'."\n".
            '// <![CDATA['."\n".
            (!empty($strMessage)
                ? 'alert("'.
                  html_entity_decode($strMessage, ENT_QUOTES, CONTREXX_CHARSET).
                  '");'."\n"
                : ''
            ).
            (!empty($strTargetUri)
                ? 'document.location.href="'.$strTargetUri.'";'."\n"
                : ''
            ).
            '// ]]>'."\n".
            '</script>'."\n";
    }


    /*
     * Returns a string containing Javascript for displaying the appropriate
     * success message and/or redirects for the product ID given.
     * @param   integer   $productId     The product ID
     * @return  string                    The Javascript string
     * @static
     */
    static function getSuccessMessage($productId)
    {
        // Seems that we need to clear the $_POST array to prevent it from
        // being reposted on the target page.
        unset($_POST);
        unset($_REQUEST);
        //unset($_GET);

        $ReturnValue = '';
        if (eGovLibrary::GetProduktValue('product_message', $productId) != '') {
            $AlertMessageTxt = preg_replace(array('/(\n|\r\n)/', '/<br\s?\/?>/i'), '\n', addslashes(html_entity_decode(eGovLibrary::GetProduktValue('product_message', $productId), ENT_QUOTES, CONTREXX_CHARSET)));
            $ReturnValue = 'alert("'.$AlertMessageTxt.'");'."\n";
        }
        if (eGovLibrary::GetProduktValue('product_target_url', $productId) != '') {
            return
                $ReturnValue.
                'document.location.href="'.
                eGovLibrary::GetProduktValue('product_target_url', $productId).
                '";'."\n";
        }
        // Old: $ReturnValue .= "history.go(-2);\n";
        return
            $ReturnValue.
            'document.location.href="'.$_SERVER['PHP_SELF']."?section=egov\";\n";
    }

}

?>
