<?php

/**
 * SettingsGeneral
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_checkout
 */

/**
 * SettingsGeneral
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_checkout
 */
class SettingsGeneral {

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
     * @param       ADONewConnection    $objDatabase
     */
    public function __construct($objDatabase)
    {
        $this->objDatabase = $objDatabase;
    }

    /**
     * Get ePayment status.
     *
     * @access      public
     * @return      string
     * @return      boolean
     */
    public function getEpaymentStatus()
    {
        $objResult = $this->objDatabase->Execute('SELECT `value` FROM `'.DBPREFIX.'module_checkout_settings_general` WHERE `id`=1');

        if ($objResult && ($objResult->RecordCount() > 0)) {
            return $objResult->fields['value'];
        } else {
            return false;
        }
    }

    /**
     * Update ePayment status.
     *
     * @access      public
     * @param       integer     $status
     * @return      boolean
     */
    public function setEpaymentStatus($status)
    {
        $objResult = $this->objDatabase->Execute('UPDATE `'.DBPREFIX.'module_checkout_settings_general` SET `value`='.intval($status).' WHERE `id`=1');

        if ($objResult) {
            return true;
        } else {
            return false;
        }
    }
}
