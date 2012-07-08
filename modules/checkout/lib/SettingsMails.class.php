<?php

/**
 * SettingsMails
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_checkout
 */
class SettingsMails {

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
     * Get admin mail.
     *
     * @access      public
     */
    public function getAdminMail()
    {
        $objResult = $this->objDatabase->Execute('SELECT `title`, `content` FROM `'.DBPREFIX.'module_checkout_settings_mails` WHERE `id`=1');

        if ($objResult) {
            $arrAdminMail['title'] = $objResult->fields['title'];
            $arrAdminMail['content'] = $objResult->fields['content'];
            return $arrAdminMail;
        } else {
            return false;
        }
    }

    /**
     * Get customer mail.
     *
     * @access      public
     */
    public function getCustomerMail()
    {
        $objResult = $this->objDatabase->Execute('SELECT `title`, `content` FROM `'.DBPREFIX.'module_checkout_settings_mails` WHERE `id`=2');

        if ($objResult) {
            $arrCustomerMail['title'] = $objResult->fields['title'];
            $arrCustomerMail['content'] = $objResult->fields['content'];
            return $arrCustomerMail;
        } else {
            return false;
        }
    }

    /**
     * Update administrator mail.
     *
     * @access      public
     * @param       array       $arrAdminMail
     */
    public function updateAdminMail($arrAdminMail)
    {
        $objResult = $this->objDatabase->Execute('
            UPDATE `'.DBPREFIX.'module_checkout_settings_mails`
            SET	`title`="'.contrexx_raw2db($arrAdminMail['title']).'",
                `content`="'.contrexx_raw2db($arrAdminMail['content']).'"
            WHERE `id`=1
        ');

        if ($objResult) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Update customer mail.
     *
     * @access      public
     * @param       array       $arrCustomerMail
     */
    public function updateCustomerMail($arrCustomerMail)
    {
        $objResult = $this->objDatabase->Execute('
            UPDATE `'.DBPREFIX.'module_checkout_settings_mails`
            SET	`title`="'.contrexx_raw2db($arrCustomerMail['title']).'",
                `content`="'.contrexx_raw2db($arrCustomerMail['content']).'"
            WHERE `id`=2
        ');

        if ($objResult) {
            return true;
        } else {
            return false;
        }
    }
}
