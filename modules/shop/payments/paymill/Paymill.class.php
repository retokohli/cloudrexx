<?php

/**
 * Paymill online payment
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @version     3.1.1
 * @package     contrexx
 * @subpackage  module_shop
 */

/**
 * PostFinance online payment
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @version     3.1.1
 * @package     contrexx
 * @subpackage  module_shop  
 */
class Paymill {
    /**
     * section name
     *
     * @access  private
     * @var     string
     */
    private static $sectionName = null;

    /**
     * Error messages
     * @access  public
     * @var     array
     */
    public static $arrError = array();

    /**
     * Warning messages
     * @access  public
     * @var     array
     */
    public static $arrWarning = array();
    
    private static $formScript = <<< FORMTEMPLATE
            
            \$J(document).ready(function() {
                // 3d secure credit card form
                \$J("#card-tds-form").submit(function(event) {
                    // Deactivate submit button to avoid further clicks
                    $('.submit-button').attr("disabled", "disabled");
                    event.preventDefault();
                    try {
                        paymill.createToken({
                            number:     \$J('#card-tds-form .card-number').val(),
                            exp_month:  \$J('#card-tds-form .card-expiry-month').val(),
                            exp_year:   \$J('#card-tds-form .card-expiry-year').val(),
                            cvc:        \$J('#card-tds-form .card-cvc').val(),
                            cardholder: \$J('#card-tds-form .card-holdername').val(),
                            amount:     \$J('#card-tds-form .card-amount').val(),
                            currency:   \$J('#card-tds-form .card-currency').val()
                        }, PaymillResponseHandler);
                    } catch(e) {
                        logResponse(e.message);
                    }
                });
            });
            function PaymillResponseHandler(error, result) {
                if (error) {
                    logResponse(error.apierror)
                } else {
                    logResponse(result.token);
                    var form = \$J("#card-tds-form");
                    // Token
                    var token = result.token;

                    // Insert token into form in order to submit to server
                    form.append("<input type='hidden' name='paymillToken' value='" + token + "'/>");
                    form.get(0).submit();
                    
                    \$J(".submit-button").removeAttr("disabled");
            
                    /*\$J.getJSON('index.php', {section: 'shop', cmd: 'success', handler: 'paymill', token: result.token}, function(data) {
                        logResponse(data);
                    });*/
                }
                 
            }

            function logResponse(res) {
                // create console.log to avoid errors in old IE browsers
                if (!window.console) console = {log:function(){}};

                console.log(res);
                if(PAYMILL_TEST_MODE)
                    \$J('.debug').text(res).show().fadeOut(8000);
            }            
FORMTEMPLATE;
    
    public static function processRequest($token, $arrOrder) {
        if (empty($token)) {
            return array(
                        'status'  => 'error',
                        'message' => 'invalid token'
                       );
        }
        
        $testMode = intval(SettingDb::getValue('paymill_use_test_account')) == 0;
        $apiKey   = $testMode ? SettingDb::getValue('paymill_test_private_key') : SettingDb::getValue('paymill_live_private_key');
        
        if ($token) {
            $request = new Paymill\Request($apiKey);
            $transaction = new Paymill\Models\Request\Transaction();
            $transaction->setAmount($arrOrder['amount'])
                        ->setCurrency($arrOrder['currency'])
                        ->setToken($token)
                        ->setDescription($arrOrder['note']);

            try {
                $response = $request->create($transaction);
                $paymentId = $response->getId();
                return array('status' => 'success', 'payment_id' => $paymentId);
            } catch(PaymillException $e) {
                //Do something with the error informations below
                return array(
                        'status' => 'error',
                        'response_code' => $e->getResponseCode(),
                        'status_code' => $e->getStatusCode(),
                        'message'       => $e->getErrorMessage()
                       );                
            }
        }
    }
    
    /**
     * Creates and returns the HTML Form for requesting the payment service.
     *
     * @access  public     
     * @return  string                      The HTML form code
     */
    static function getForm($arrOrder, $landingPage = null)
    {
        global $_ARRAYLANG;
        
        if ((gettype($landingPage) != 'object') || (get_class($landingPage) != 'Cx\Core\ContentManager\Model\Entity\Page')) {
            self::$arrError[] = 'No landing page passed.';
        }

        if (($sectionName = $landingPage->getModule()) && !empty($sectionName)) {
            self::$sectionName = $sectionName;
        } else {
            self::$arrError[] = 'Passed landing page is not an application.';
        }
        
        JS::registerJS("https://bridge.paymill.com/");
        
        $testMode = intval(SettingDb::getValue('paymill_use_test_account')) == 0;        
        $apiKey   = $testMode ? SettingDb::getValue('paymill_test_public_key') : SettingDb::getValue('paymill_live_public_key');
        $mode     = $testMode ? 'true' : 'false';
        
        $code = <<< APISETTING
                var PAYMILL_PUBLIC_KEY = '$apiKey';
                var PAYMILL_TEST_MODE  = $mode;
APISETTING;
        JS::registerCode($code);
        JS::registerCode(self::$formScript);
                
        $formContent  = self::getElement('div', 'class="debug"');
        
        $formContent .= self::fieldset('');
        
        $formContent .= self::openElement('div', 'class="row"');
        $formContent .= self::getElement('label', '', $_ARRAYLANG['TXT_SHOP_CREDIT_CARD_NUMBER']);
        $formContent .= Html::getInputText('', '4012888888881881', '', 'class="card-number size="20"');
        $formContent .= self::closeElement('div');
        
        $formContent .= self::openElement('div', 'class="row"');        
        $formContent .= self::getElement('label', '', $_ARRAYLANG['TXT_SHOP_CVC']);
        $formContent .= Html::getInputText('', '123', '', 'class ="card-cvc" size="4" maxlength="4"');
        $formContent .= self::closeElement('div');
        
        $formContent .= self::openElement('div', 'class="row"');
        $formContent .= self::getElement('label', '', $_ARRAYLANG['TXT_SHOP_CARD_HOLDER']);
        $formContent .= Html::getInputText('', 'Max Mustermann', '', 'class="card-holdername" size="20"');
        $formContent .= self::closeElement('div');
        
        $formContent .= self::openElement('div', 'class="row"');
        $formContent .= self::getElement('label', '', $_ARRAYLANG['TXT_SHOP_CARD_EXPIRY']);
        $formContent .= Html::getInputText('', '12', '', 'class="card-expiry-month" size="2" maxlength="2"');
        $formContent .= Html::getInputText('', '2015', '', 'class="card-expiry-year" size="4" maxlength="4"');
        $formContent .= self::closeElement('div');
        
        $formContent .= self::openElement('div', 'class="row"');
        $formContent .= self::getElement('label', '', '&nbsp;');
        $formContent .= Html::getInputButton('', $_ARRAYLANG['TXT_SHOP_BUY_NOW'], 'submit', '', 'class="submit-button"');
        $formContent .= self::closeElement('div');
        
        $formContent .= Html::getHidden('', $arrOrder['amount'], '', 'class="card-amount" size="4"');
        $formContent .= Html::getHidden('', $arrOrder['currency'], '', 'class="card-currency" size="4"');
        
        $formContent .= self::closeElement('fieldset');
        
        $form = Html::getForm('', Cx\Core\Routing\Url::fromPage($landingPage)->toString(), $formContent, 'card-tds-form', 'POST');
        
        return $form;
    }
    
    static function fieldset($legend = false, $selfClose = false) {
        $fieldset = self::openElement('fieldset');        
        if ($legend) {
            $fieldset .= self::getElement('legend', '', $legend);
        }
        if ($selfClose) {
            $fieldset .= self::closeElement('fieldset');
        }
        
        return $fieldset;
    }
    
    static function getElement($elm, $attrbs = '', $content ='') {        
        return "<$elm ". self::getFormattedAttrbs($attrbs) . ">". $content ."</$elm> \n";
    }
    
    static function openElement($elm , $attributes = '')
    {        
        return "<$elm ". self::getFormattedAttrbs($attributes)." > \n";
    }
    
    static function closeElement($elm) 
    {        
        return "</$elm> \n";
    }
    
    static function getFormattedAttrbs($attributes) {
        $html = '';
        if (is_array($attributes)) {
            foreach ($attributes as $attribute) {
                $html .= $attribute;
            }
        } else {
            $html .= $attributes;
        }
        return $html;
    }
}
