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
        $objSettings = new CalendarSettings();
        $arrCalendarData = $objSettings->getYellowpaySettings();

        $arrCalendarData = array_merge($arrCalendarData, $data);

        $language = FWLanguage::getLanguageCodeById(FRONTEND_LANG_ID);
        $language = strtolower($language).'_'.strtoupper($language);
        $yellowpayForm = Yellowpay::getForm(
            $arrCalendarData, $_ARRAYLANG['TXT_ORDER_NOW']
        );
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