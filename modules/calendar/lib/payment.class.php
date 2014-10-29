<?php
/**
 * Calendar 
 * 
 * @package    contrexx
 * @subpackage module_calendar
 * @author     Comvation <info@comvation.com>
 * @copyright  CONTREXX CMS - COMVATION AG
 * @version    1.00
 */


/**
 * Calendar Class Payment
 * 
 * @package    contrexx
 * @subpackage module_calendar
 * @author     Comvation <info@comvation.com>
 * @copyright  CONTREXX CMS - COMVATION AG
 * @version    1.00
 */
class CalendarPayment {
    /**
     * Returns the HTML code for the Yellowpay payment method.
     * 
     * @param array $data post data from the user
     * 
     * @return  string  HTML code
     */
    function _yellowpay($data = array())
    {
        global $_ARRAYLANG;
        $objSettings         = new CalendarSettings();
        $arrCalendarSettings = $objSettings->getYellowpaySettings();

        $arrOrder = array(
            'ORDERID'   => $data['orderID'],            
            'AMOUNT'    => $data['amount'],
            'CURRENCY'  => $data['currency'],
            'PARAMPLUS' => "section=calendar&cmd=success&handler=yellowpay",
        );
        $arrSettings = array();
        $arrSettings['postfinance_shop_id']['value']            = $arrCalendarSettings['paymentYellowpayPspid'];
        $arrSettings['postfinance_hash_signature_in']['value']  = $arrCalendarSettings['paymentYellowpayShaIn'];
        $arrSettings['postfinance_authorization_type']['value'] = $arrCalendarSettings['paymentYellowpayAuthorization'] == 0 ? 'SAL' : 'RES';
        $arrSettings['postfinance_use_testserver']['value']     = $arrCalendarSettings['paymentTestserver'];

        $landingPage = \Env::get('em')->getRepository('Cx\Core\ContentManager\Model\Entity\Page')->findOneByModuleCmdLang('calendar', 'success', FRONTEND_LANG_ID);

        $yellowpayForm = Yellowpay::getForm($arrOrder, $_ARRAYLANG['TXT_CALENDAR_START_PAYMENT'], false, $arrSettings, $landingPage);

        if (_PAYMENT_DEBUG && Yellowpay::$arrError) {
            $strError =
                '<font color="red"><b>'.
                $_ARRAYLANG['TXT_SHOP_PSP_FAILED_TO_INITIALISE_YELLOWPAY'].
                '<br /></b>';
            if (_PAYMENT_DEBUG) {
                $strError .= join('<br />', Yellowpay::$arrError); //.'<br />';
            }

            return $strError.'</font>';
        }
        return $yellowpayForm;
    }
}