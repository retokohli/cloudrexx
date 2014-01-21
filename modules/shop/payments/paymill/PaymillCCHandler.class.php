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
class PaymillCCHandler extends PaymillHandler {
    
   private static $formScript = <<< FORMTEMPLATE
            
            \$J(document).ready(function() {
                // 3d secure credit card form
                \$J("#card-tds-form").submit(function(event) {
                    // Deactivate submit button to avoid further clicks
                    \$J('.submit-button').attr("disabled", "disabled");
                    event.preventDefault();
                    try {
                        paymill.createToken({
                            number:     \$J('#card-tds-form .card-number').val(),
                            exp_month:  \$J('#card-tds-form .card-expiry-month').val(),
                            exp_year:   \$J('#card-tds-form .card-expiry-year').val(),
                            cvc:        \$J('#card-tds-form .card-cvc').val(),
                            cardholder: \$J('#card-tds-form .card-holdername').val(),                            
                        }, PaymillResponseHandler);
                    } catch(e) {
                        logResponse(e.message);
                    }
                });
            });
            function PaymillResponseHandler(error, result) {
                if (error) {
                    logResponse(error.apierror);
                    \$J(".submit-button").removeAttr("disabled");
                } else {
                    //logResponse(result.token);
                    var form = \$J("#card-tds-form");
                    // Token
                    var token = result.token;

                    // Insert token into form in order to submit to server
                    form.append("<input type='hidden' name='paymillToken' value='" + token + "'/>");
                    form.get(0).submit();
                    
                    \$J(".submit-button").removeAttr("disabled");            
                }
                 
            }

            function logResponse(res) {
                /*
                // create console.log to avoid errors in old IE browsers
                if (!window.console) console = {log:function(){}};

                console.log(res);
                if(PAYMILL_TEST_MODE)
                    \$J('.debug').text(res).show().fadeOut(8000);
                */
                \$J('.paymill-error-text').text(res).show().fadeOut(8000);
            }            
FORMTEMPLATE;
   
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
        
        JS::registerJS(self::$paymillJsBridge);
        
        $testMode = intval(SettingDb::getValue('paymill_use_test_account')) == 0;        
        $apiKey   = $testMode ? SettingDb::getValue('paymill_test_public_key') : SettingDb::getValue('paymill_live_public_key');
        $mode     = $testMode ? 'true' : 'false';
        
        $code = <<< APISETTING
                var PAYMILL_PUBLIC_KEY = '$apiKey';
                var PAYMILL_TEST_MODE  = $mode;
APISETTING;
        JS::registerCode($code);
        JS::registerCode(self::$formScript);
                
        $formContent  = self::getElement('div', 'class="paymill-error-text"');
        
        $formContent .= self::fieldset('');
        
        $formContent .= self::openElement('div', 'class="row"');
        $formContent .= self::getElement('label', '', $_ARRAYLANG['TXT_SHOP_CREDIT_CARD_NUMBER']);
        $formContent .= Html::getInputText('', '', '', 'class="card-number" size="20"');
        $formContent .= self::closeElement('div');
        
        $formContent .= self::openElement('div', 'class="row"');        
        $formContent .= self::getElement('label', '', $_ARRAYLANG['TXT_SHOP_CVC']);
        $formContent .= Html::getInputText('', '', '', 'class ="card-cvc" size="4" maxlength="4"');
        $formContent .= self::closeElement('div');
        
        $formContent .= self::openElement('div', 'class="row"');
        $formContent .= self::getElement('label', '', $_ARRAYLANG['TXT_SHOP_CARD_HOLDER']);
        $formContent .= Html::getInputText('', '', '', 'class="card-holdername" size="20"');
        $formContent .= self::closeElement('div');
        
        $arrMonths = array();
        for ($i=1;$i<=12;$i++) {
            $month             = str_pad($i, 2, '0', STR_PAD_LEFT);
            $arrMonths[$month] = $month;
        }
        
        $arrYears    = array();
        $currentYear = date('Y');
        for ($i=$currentYear;$i<=($currentYear+6);$i++) {
            $arrYears[$i] = $i;
        }
         
        $formContent .= self::openElement('div', 'class="row"');
        $formContent .= self::getElement('label', '', $_ARRAYLANG['TXT_SHOP_CARD_EXPIRY']);
        $formContent .= Html::getSelect('card-expiry-month', $arrMonths, '', false, '', 'class="card-expiry-month"');
        $formContent .= Html::getSelect('card-expiry-year', $arrYears, '', false, '', 'class="card-expiry-year"');        
        $formContent .= self::closeElement('div');
        
        $formContent .= self::openElement('div', 'class="row"');
        $formContent .= self::getElement('label', '', '&nbsp;');
        $formContent .= Html::getInputButton('', $_ARRAYLANG['TXT_SHOP_BUY_NOW'], 'submit', '', 'class="submit-button"');
        $formContent .= self::closeElement('div');
                
        $formContent .= Html::getHidden('handler', 'paymill_cc');
        
        $formContent .= self::closeElement('fieldset');
        
        $form = Html::getForm('', Cx\Core\Routing\Url::fromPage($landingPage)->toString(), $formContent, 'card-tds-form', 'post');
        
        return $form;
    }
}
