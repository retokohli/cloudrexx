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
                error ? logResponse(error.apierror) : logResponse(result.token);
            }

            function logResponse(res) {
                // create console.log to avoid errors in old IE browsers
                if (!window.console) console = {log:function(){}};

                console.log(res);
                if(PAYMILL_TEST_MODE)
                    \$J('.debug').text(res).show().fadeOut(8000);
            }            
FORMTEMPLATE;
    
    /**
     * Creates and returns the HTML Form for requesting the payment service.
     *
     * @access  public     
     * @return  string                      The HTML form code
     */
    static function getForm()
    {
        global $_ARRAYLANG;
        
        JS::registerJS("https://bridge.paymill.com/");
        
        $testMode = (int) SettingDb::getValue('paymill_use_test_account');
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
        $formContent .= self::getElement('label', '', 'Credit card number');
        $formContent .= Html::getInputText('', '4012888888881881', '', 'class="card-number size="20"');
        $formContent .= self::closeElement('div');
        
        $formContent .= self::openElement('div', 'class="row"');        
        $formContent .= self::getElement('label', '', 'CVC');
        $formContent .= Html::getInputText('', '123', '', 'class ="card-cvc" size="4" maxlength="4"');
        $formContent .= self::closeElement('div');
        
        $formContent .= self::openElement('div', 'class="row"');
        $formContent .= self::getElement('label', '', 'Card holder');
        $formContent .= Html::getInputText('', 'Max Mustermann', '', 'class="card-holdername" size="20"');
        $formContent .= self::closeElement('div');
        
        $formContent .= self::openElement('div', 'class="row"');
        $formContent .= self::getElement('label', '', 'Expiry (MM/YYYY)');
        $formContent .= Html::getInputText('', '12', '', 'class="card-expiry-month" size="2" maxlength="2"');
        $formContent .= Html::getInputText('', '2015', '', 'class="card-expiry-year" size="4" maxlength="4"');
        $formContent .= self::closeElement('div');
        
        $formContent .= self::openElement('div', 'class="row"');
        $formContent .= self::getElement('label', '', '&nbsp;');
        $formContent .= Html::getInputButton('', 'Submit', 'submit', '', 'class="submit-button"');
        $formContent .= self::closeElement('div');
        
        $formContent .= Html::getHidden('', "123.45", '', 'class="card-amount" size="4"');
        $formContent .= Html::getHidden('', "EUR", '', 'class="card-currency" size="4"');
        
        $formContent .= self::closeElement('fieldset');
        
        $form = Html::getForm('', 'javascript:void(0)', $formContent, 'card-tds-form', 'POST');
        
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
