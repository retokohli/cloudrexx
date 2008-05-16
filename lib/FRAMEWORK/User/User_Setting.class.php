<?php

class User_Setting
{
    /**
     * The user mail object
     * @var   User_Setting_Mail
     */
    private $objMail;

    function User_Setting()
    {
        $this->objMail = new User_Setting_Mail();
    }


    function getSettings($reload=false)
    {
        global $objDatabase;
        static $arrSettings = array();

        if (empty($arrSettings) || $reload) {
// TODO: This seems to be bogus
            $arrDebugBackTrace = debug_backtrace();
            $objSetting = $objDatabase->Execute('SELECT `key`, `value`, `status` FROM `'.DBPREFIX.'access_settings`');
            if ($objSetting !== false) {
                while (!$objSetting->EOF) {
                    $arrSettings[$objSetting->fields['key']] = array(
                        'value'  => $objSetting->fields['value'],
                        'status' => $objSetting->fields['status']
                    );
                    $objSetting->MoveNext();
                }
            }
        }
        return $arrSettings;
    }


    function setSettings($arrSettings)
    {
        global $objDatabase;

        $status = true;
        foreach ($arrSettings as $key => $arrSetting) {
            if ($objDatabase->Execute('UPDATE `'.DBPREFIX.'access_settings` SET `value` = \''.contrexx_addslashes($arrSetting['value']).'\', `status` = '.intval($arrSetting['status']).' WHERE `key` = \''.contrexx_addslashes($key).'\'') === false) {
                $status = false;
            }
        }
        return $status;
    }


    /**
     * Returns an array containing the available account validity
     * time periods in days in ascending order.
     * @return  array                   The time periods
     * @global  mixed   $objDatabase    Database object
     * @static
     */
    public static function getUserValidities()
    {
        global $objDatabase;
        static $arrValidityPeriod = array();

        if (empty($arrValidityPeriod)) {
            $objRecordSet = $objDatabase->Execute("
                SELECT `validity`
                  FROM `".DBPREFIX."access_user_validity`
                 ORDER BY `validity` ASC"
            );
            if ($objRecordSet) {
                while (!$objRecordSet->EOF) {
                    $arrValidityPeriod[] = $objRecordSet->fields['validity'];
                    $objRecordSet->MoveNext();
                }
            }
        }
        return $arrValidityPeriod;
    }
}

?>
