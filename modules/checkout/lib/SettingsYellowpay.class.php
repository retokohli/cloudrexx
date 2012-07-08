<?php

/**
 * PaymentServiceProvider
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_checkout
 */
class SettingsYellowpay {

    /**
     * Database object.
     *
     * @access      private
     * @var         ADONewConnection
     */
    private $objDatabase;

    /**
     * Initialize the database object.
     *
     * @access      public
     */
    public function __construct($objDatabase)
    {
        $this->objDatabase = $objDatabase;
    }

    /**
     * Get settings.
     *
     * @access      public
     */
    public function get()
    {
        $objResult = $this->objDatabase->Execute('SELECT `name`, `value` FROM `'.DBPREFIX.'module_checkout_settings_yellowpay`');

        $arrYellowpay = array();
        if ($objResult) {
            while (!$objResult->EOF) {
                $arrYellowpay[$objResult->fields['name']] = $objResult->fields['value'];
                $objResult->MoveNext();
            }
            return $arrYellowpay;
        } else {
            return false;
        }
    }

    /**
     * Update settings.
     *
     * @access      public
     * @param       array       $arrYellowpay
     */
    public function update($arrYellowpay)
    {
        foreach ($arrYellowpay as $name => $value) {
            $objResult = $this->objDatabase->Execute('
                UPDATE `'.DBPREFIX.'module_checkout_settings_yellowpay`
                SET	`value`="'.contrexx_raw2db($value).'"
                WHERE `name`="'.$name.'"
            ');

            if (!$objResult) {
                return false;
            }
        }

        return true;
    }

}
