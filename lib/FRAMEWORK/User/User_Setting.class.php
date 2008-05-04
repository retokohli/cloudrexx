<?php
class User_Setting
{
	var $objMail;

	function User_Setting()
	{
		$this->__construct();
	}

	function __construct()
	{
		$this->objMail = new User_Setting_Mail();
	}

	function getSettings($reload = false)
	{
		global $objDatabase;

		static $arrSettings;

		if (empty($arrSettings) || $reload) {
			$arrSettings = array();

			$arrDebugBackTrace =  debug_backtrace();
			$objSetting = $objDatabase->Execute('SELECT `key`, `value`, `status` FROM `'.DBPREFIX.'access_settings`');
			if ($objSetting !== false) {
				while (!$objSetting->EOF) {
					$arrSettings[$objSetting->fields['key']] = array(
						'value'		=> $objSetting->fields['value'],
						'status'	=> $objSetting->fields['status']
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

	public static function getUserValidities()
	{
		global $objDatabase;

		static $arrValidities;

		if (empty($arrValidities)) {
			$arrValidities = array();

			$objValidity = $objDatabase->Execute('SELECT `validity` FROM `'.DBPREFIX.'user_validity` ORDER BY `validity` ASC');
			if ($objValidity !== false) {
				while (!$objValidity->EOF) {
					$arrValidities[] = $objValidity->fields['validity'];
					$objValidity->MoveNext();
				}
			}
		}

		return $arrValidities;
	}
}
?>
