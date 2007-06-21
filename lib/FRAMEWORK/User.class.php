<?php
/**
 * Framework user
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  lib_framework
 * @todo        Edit PHP DocBlocks!
 */
class FWUser
{
	var $userId;
	var $_arrUserGroups = array();
	var $_arrUsers;
	var $_arrGroups;

//
//	function getUserGroups($userId = 0) {
//		global $objDatabase;
//
//		if ($userId != 0 && !isset($this->_arrUserGroups[$userId])) {
//			$objResult = $objDatabase->Execute("SELECT groups FROM ".DBPREFIX."access_users WHERE id=".$userId);
//			if ($objResult !== false && !$objResult->EOF) {
//				$this->_arrUserGroups[$userId] = explode(',', $objResult->fields['groups']);
//			}
//		}
//
//		return $this->_arrUserGroups[$userId];
//	}

	function getUsers($reload = false)
	{
		if (!is_array($this->_arrUsers) || $reload) {
			$this->_initUsers();
		}
		return $this->_arrUsers;
	}

	function _initUsers()
	{
		global $objDatabase;

		$this->_arrUsers = array();
		$objResult = $objDatabase->Execute("SELECT `id`,
													`levelid`,
													`is_admin`,
													`username`,
													`regdate`,
													`email`,
													`firstname`,
													`lastname`,
													`langId`,
													`active`,
													`groups`,
													`restore_key`,
													`restore_key_time`
												FROM ".DBPREFIX."access_users");
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				$this->_arrUsers[$objResult->fields['id']] = array(
					'id'		=> $objResult->fields['id'],
					'levelid'	=> $objResult->fields['levelid'],
					'is_admin'	=> $objResult->fields['is_admin'],
					'username'	=> $objResult->fields['username'],
					'regdate'	=> $objResult->fields['regdate'],
					'email'		=> $objResult->fields['email'],
					'firstname'	=> $objResult->fields['firstname'],
					'lastname'	=> $objResult->fields['lastname'],
					'langId'	=> $objResult->fields['langId'],
					'active'	=> $objResult->fields['active'],
					'groups'	=> $objResult->fields['groups'],
					'restore_key'	=> $objResult->fields['restore_key'],
					'restore_key_time'	=> $objResult->fields['restore_key_time']
				);
				$objResult->MoveNext();
			}
		}
	}

	function addUser($username, $is_admin, $password, $email, $firstname, $lastname, $residence, $zip, $langId, $arrGroups = "", $active = 1, $restoreKey = "", $restoreKeyTime = "")
	{
		global $objDatabase;

		$password = md5($password);
		$groups = (is_array($arrGroups) && count($arrGroups)>0) ? implode(",", $arrGroups) : $arrGroups;
		$is_admin = $is_admin ? 1 : 0;

		if ($objDatabase->Execute("INSERT INTO ".DBPREFIX."access_users
				                    (`username`, `is_admin`, `password`, `email`, `firstname`, `lastname`, `residence`, `zip`, `langId`, `regdate`, `groups`, `active`, `restore_key`, `restore_key_time`)
				                    VALUES ('".contrexx_addslashes($username)."', '".intval($is_admin)."', '".contrexx_addslashes($password)."', '".contrexx_addslashes($email)."', '".contrexx_addslashes($firstname)."', '".contrexx_addslashes($lastname)."', '".contrexx_addslashes($residence)."', '".contrexx_addslashes($zip)."', ".intval($langId).", CURDATE(), '".contrexx_addslashes($groups)."', ".intval($active).", '".contrexx_addslashes($restoreKey)."', '".intval($restoreKeyTime)."')") !== false) {
			return $objDatabase->Insert_ID();
        } else {
        	return false;
        }
	}

	function updateUser($userId, $username, $is_admin, $password, $email, $firstname, $lastname, $langId, $arrGroups = "", $active = 1)
	{
		global $objDatabase;


		$strPassword = empty($password) ? "" : "`password`='".md5($password)."',";
		$groups = (is_array($arrGroups) && count($arrGroups)>0) ? implode(",", $arrGroups) : "";
		$is_admin = empty($is_admin) ? "" : "`is_admin`=".($is_admin ? 1 : 0).",";

		if ($objDatabase->Execute("UPDATE ".DBPREFIX."access_users
				                    SET `username`='".$username."',"
				                    	.$is_admin
				                    	.$strPassword."
				                    	`email`='".$email."',
				                    	`firstname`='".$firstname."',
				                    	`lastname`='".$lastname."',
				                    	`langId`=".$langId.",
				                    	`groups`='".$groups."',
				                    	`active`=".$active."
				                    WHERE id=".$userId) !== false) {
			return true;
        } else {
        	return false;
        }
	}

	function getGroups()
	{
		if (!is_array($this->_arrGroups)) {
			$this->_initGroups();
		}
		return $this->_arrGroups;
	}

	function getUserGroups($userId, $inverse = false)
	{
		$arrUserGroups = array();

		$arrAllGroups = $this->getGroups();
		$arrUsers = $this->getUsers();
		$arrGroups = array();

		if (isset($arrUsers[$userId])) {
			$arrGroups = explode(",", $arrUsers[$userId]['groups']);
		} else {
			return $arrUserGroups;
		}

		foreach ($arrAllGroups as $groupId => $arrGroup) {
			if (!$inverse && in_array($groupId, $arrGroups)) {
				$arrUserGroups[$groupId] = $arrGroup;
			} elseif ($inverse && !in_array($groupId, $arrGroups)) {
				$arrUserGroups[$groupId] = $arrGroup;
			}
		}

		return $arrUserGroups;
	}

	function _initGroups()
	{
		global $objDatabase;

		$this->_arrGroups = array();
		$objResult = $objDatabase->Execute("SELECT `group_id`, `group_name`, `type`, `is_active` FROM ".DBPREFIX."access_user_groups ORDER BY group_name");
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				$this->_arrGroups[$objResult->fields['group_id']] = array(
					'id'		=> $objResult->fields['group_id'],
					'name'		=> $objResult->fields['group_name'],
					'type'		=> $objResult->fields['type'],
					'is_active'	=> $objResult->fields['is_active']
				);
				$objResult->MoveNext();
			}
		}
	}

		/**
	* Checks that the email address isn't already used by an other user
	*
	* @access private
	* @global $objDatabase
	* @param string $email
	* @return boolen true if the email address isn't already used by an other user
	*/
	function checkEmailIntegrity($email, $userId = 0)
	{
		global $objDatabase;

		$this->_removeOutdatedAccounts();

		if ($userId != 0) {
			$objResult = $objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."access_users WHERE email='".contrexx_addslashes($email)."' AND id !=".$userId, 1);
		} else {
			$objResult = $objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."access_users WHERE email='".contrexx_addslashes($email)."'", 1);
		}
		if ($objResult !== false && $objResult->RecordCount() == 0) {
			return true;
		}
		return false;
	}

	/**
	* Checks that the username isn't already used by an other user
	*
	* @access private
	* @global $objDatabase
	* @param string $email
	* @return boolen
	*/
	function checkUsernameIntegrity($username, $userId = 0)
	{
		global $objDatabase;

		$this->_removeOutdatedAccounts();

		if ($userId != 0) {
			$objResult = $objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."access_users WHERE username='".contrexx_addslashes($username)."' AND id !=".$userId, 1);
		} else {
			$objResult = $objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."access_users WHERE username='".contrexx_addslashes($username)."'", 1);
		}
		if ($objResult !== false && $objResult->RecordCount() == 0) {
			return true;
		}
		return false;
	}

	function _removeOutdatedAccounts()
	{
		global $objDatabase;

		$objDatabase->Execute("DELETE FROM ".DBPREFIX."access_users WHERE active=0 AND restore_key!='' AND restore_key_time<".time());
	}

	/**
	 * Is valid username
	 *
	 * Checks if the username is valid.
	 *
	 * @param string $username
	 * @return boolean
	 */
	function isValidUsername($username)
	{
		if (preg_match('/^[a-zA-Z0-9-_]+$/', $username)) {
			return true;
		} else {
			return false;
		}
	}
}
?>