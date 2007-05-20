<?php
class FWUser
{
	var $_arrUserGroups = array();
	var $_arrUsers;
	var $_arrGroups;

	var $arrSettings = array();

	var $arrStatusMsg = array(
		'ok'		=> array(),
		'error'		=> array()
	);


	var $arrGenders = array(
		''	=> 'TXT_UNKNOWN',
		'm'	=> 'TXT_MALE',
		'f'	=> 'TXT_FEMALE'
	);

	var $arrGroupTypes = array(
		'frontend',
		'backend'
	);

	/**
	 * Get settings
	 *
	 * Returns an array with the settings.
	 *
	 * @param boolean $reload
	 * @return array
	 */
	function getSettings($reload = false)
	{
		global $objDatabase;

		static $arrSettings;

		if (empty($arrSettings) || $reload) {
			$arrSettings = array();

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

	function _setSettings($arrSettings)
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
	 * Get user count
	 *
	 * Returns the number of available users.
	 *
	 * @param boolean $onlyActive
	 * @param mixed $group
	 * @return mixed
	 */
	function getUserCount($onlyActive = true, $group = false)
	{
		global $objDatabase;

		$objCount = $objDatabase->Execute("SELECT COUNT(1) AS `count` FROM `".DBPREFIX."access_users` AS tblUser".($group ? " , `".DBPREFIX."access_rel_user_group` AS tblRel WHERE tblRel.group_id=".$group." AND tblRel.user_id=tblUser.id" : "").($onlyActive ? (!$group ? " WHERE" : "")." tblUser.active = 1 " : ""));
		if ($objCount) {
			return $objCount->fields['count'];
		} else {
			return false;
		}
	}

	/**
	 * Get users
	 *
	 * Returns an array of available users in the system.
	 *
	 * @param boolean $onlyActive
	 * @param mixed $limitNumber
	 * @param integer $limitOffset
	 * @param mixed $orderBy
	 * @param string $orderDirection
	 * @return mixed
	 */
	function getUsers($onlyActive = true, $group = false, $limitNumber = false, $limitOffset = 0, $orderBy = false, $orderDirection = 'asc')
	{
		global $objDatabase;

		if ($orderBy && !in_array($orderBy, array('id', 'is_admin', 'username', 'regdate', 'email', 'firstname', 'lastname', 'langId', 'active'))) {
			$orderBy = false;
		} elseif ($orderBy) {
			if (!in_array($orderDirection, array('asc', 'desc'))) {
				$orderDirection = 'asc';
			}
		}

		$query = "SELECT
			tblUser.`id`,
			tblUser.`is_admin`,
			tblUser.`username`,
			tblUser.`regdate`,
			tblUser.`email`,
			tblProfile.`firstname`,
			tblProfile.`lastname`,
			tblUser.`langId`,
			tblUser.`active`,
			tblUser.`restore_key`,
			tblUser.`restore_key_time`
			FROM ".DBPREFIX."access_users AS tblUser
			LEFT JOIN ".DBPREFIX."access_user_profile AS tblProfile ON tblProfile.`user_id` = tblUser.`id`"
			.($group ? ", `".DBPREFIX."access_rel_user_group` AS tblRel" : "")
			.($group || $onlyActive ? " WHERE" : "")
			.($group ? " tblRel.`group_id`= ".$group." AND tblRel.`user_id` = tblUser.`id`" : "")
			.($onlyActive ? ($group ? " AND" : "")." tblUser.`active` = 1" : "")
			.($orderBy ? " ORDER BY `".$orderBy."` ".$orderDirection : "");

		if ($limitNumber) {
			$objResult = $objDatabase->SelectLimit($query, $limitNumber, $limitOffset);
		} else {
			$objResult = $objDatabase->Execute($query);
		}

		if ($objResult !== false) {
			$arrUsers = array();

			while (!$objResult->EOF) {
				$arrUsers[$objResult->fields['id']] = array(
					'is_admin'			=> $objResult->fields['is_admin'],
					'username'			=> $objResult->fields['username'],
					'regdate'			=> $objResult->fields['regdate'],
					'email'				=> $objResult->fields['email'],
					'firstname'			=> $objResult->fields['firstname'],
					'lastname'			=> $objResult->fields['lastname'],
					'langId'			=> $objResult->fields['langId'],
					'active'			=> $objResult->fields['active'],
					'restore_key'		=> $objResult->fields['restore_key'],
					'restore_key_time'	=> $objResult->fields['restore_key_time']
				);
				$objResult->MoveNext();
			}

			return $arrUsers;
		} else {
			return false;
		}
	}

	/**
	 * Get user
	 *
	 * Returns an array with informations about the user specified by $id
	 *
	 * @param integer $id
	 * @param boolean $onlyActive
	 * @return boolean
	 */
	function getUser($id, $onlyActive = true)
	{
		global $objDatabase;

		$objResult = $objDatabase->SelectLimit("SELECT
			tblUser.`is_admin`,
			tblUser.`username`,
			tblUser.`regdate`,
			tblUser.`email`,
			tblProfile.`gender`,
			tblProfile.`firstname`,
			tblProfile.`lastname`,
			tblProfile.`company`,
			tblProfile.`address`,
			tblProfile.`city`,
			tblProfile.`zip`,
			tblProfile.`country_id`,
			tblProfile.`phone_office`,
			tblProfile.`phone_private`,
			tblProfile.`phone_mobile`,
			tblProfile.`phone_fax`,
			tblProfile.`birthday`,
			tblProfile.`website`,
			tblProfile.`skype`,
			tblProfile.`profession`,
			tblProfile.`interests`,
			tblProfile.`picture`,
			tblUser.`langId`,
			tblUser.`active`,
			tblUser.`restore_key`,
			tblUser.`restore_key_time`
			FROM `".DBPREFIX."access_users` AS tblUser
			LEFT JOIN `".DBPREFIX."access_user_profile` AS tblProfile
			ON tblProfile.`user_id` = tblUser.`id`
			WHERE tblUser.`id` = ".$id.($onlyActive ? " AND tblUser.`active` = 1" : ""), 1);

		if ($objResult !== false && $objResult->RecordCount() == 1) {
			return array(
				'is_admin'			=> $objResult->fields['is_admin'],
				'username'			=> $objResult->fields['username'],
				'regdate'			=> $objResult->fields['regdate'],
				'email'				=> $objResult->fields['email'],
				'gender'			=> $objResult->fields['gender'],
				'firstname'			=> $objResult->fields['firstname'],
				'lastname'			=> $objResult->fields['lastname'],
				'company'			=> $objResult->fields['company'],
				'address'			=> $objResult->fields['address'],
				'city'				=> $objResult->fields['city'],
				'zip'				=> $objResult->fields['zip'],
				'country_id'		=> $objResult->fields['country_id'],
				'phone_office'		=> $objResult->fields['phone_office'],
				'phone_private'		=> $objResult->fields['phone_private'],
				'phone_mobile'		=> $objResult->fields['phone_mobile'],
				'phone_fax'			=> $objResult->fields['phone_fax'],
				'birthday'			=> $objResult->fields['birthday'],
				'website'			=> $objResult->fields['website'],
				'skype'				=> $objResult->fields['skype'],
				'profession'		=> $objResult->fields['profession'],
				'interests'			=> $objResult->fields['interests'],
				'picture'			=> $objResult->fields['picture'],
				'langId'			=> $objResult->fields['langId'],
				'active'			=> $objResult->fields['active'],
				'restore_key'		=> $objResult->fields['restore_key'],
				'restore_key_time'	=> $objResult->fields['restore_key_time']
			);
		} else {
			return false;
		}
	}

	/**
	 * Add user
	 *
	 * Adds a new user to the system.
	 *
	 * @param string $username
	 * @param boolean $is_admin
	 * @param unknown_type $password
	 * @param unknown_type $confirmedPassword
	 * @param unknown_type $email
	 * @param unknown_type $langId
	 * @param unknown_type $arrGroups
	 * @param unknown_type $active
	 * @param unknown_type $arrProfile
	 * @param unknown_type $restoreKey
	 * @param unknown_type $restoreKeyTime
	 * @return unknown
	 */
	function addUser($username, $is_admin = false, $password, $confirmedPassword, $email, $langId, $arrGroups = array(), $active = false, $arrProfile = array(), $restoreKey = "", $restoreKeyTime = "")
	{
		global $objDatabase, $_CORELANG;

		$error = false;

		if (!$this->isValidUsername($username)) {
			array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_INVALID_USERNAME']);
			$error = true;
		} elseif (!$this->isUniqueUsername($username)) {
			array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_USERNAME_ALREADY_USED']);
			$error = true;
		}

		if (!$this->isValidEmail($email)) {
			array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_INVALID_EMAIL_ADDRESS']);
			$error = true;
		} elseif (!$this->isUniqueEmail($email)) {
			array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_EMAIL_ALREADY_USED']);
			$error = true;
		}

		if (!$this->isValidPassword($password)) {
			array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_INVALID_PASSWORD']);
			$error = true;
		} elseif ($password != $confirmedPassword) {
			array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_PASSWORD_NOT_CONFIRMED']);
			$error = true;
		}

		if ($error) {
			return false;
		}

		if (!$this->_validateLangId($langId)) {
			return false;
		}

		$password = md5($password);
		$arrProfile = $this->_cleanProfileData($arrProfile);

		if ($objDatabase->Execute("
			INSERT INTO ".DBPREFIX."access_users (
				`username`,
				`is_admin`,
				`password`,
				`email`,
				`langId`,
				`regdate`,
				`active`,
				`restore_key`,
				`restore_key_time`
			) VALUES (
				'".addslashes($username)."',
				".intval($is_admin).",
				'".$password."',
				'".addslashes($email)."',
				".intval($langId).",
				".time().",
				".intval($active).",
				'".addslashes($restoreKey)."',
				".intval($restoreKeyTime)."
			)") !== false) {
			$userId = $objDatabase->Insert_ID();

			if (!$this->setGroupAssociations($userId, $arrGroups)) {
				array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_COULD_NOT_SET_GROUP_ASSOCIATIONS']);
				$this->deleteUser($userId);
				return false;
			}

			if ($objDatabase->Execute("
				INSERT INTO ".DBPREFIX."access_user_profile (
					`user_id`".(count($arrProfile) > 0 ? ", `".implode('`, `', array_keys($arrProfile))."`" : '')."
				) VALUES (
					".$userId.(count($arrProfile) > 0 ? ", ".implode(', ', $arrProfile) : '')."
				)"
			) === false) {
				array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_COULD_NOT_SET_PROFILE_DATA']);
				$this->deleteUser($userId);
				return false;
			}

			return true;
        } else {
        	array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_FAILED_TO_ADD_USER_ACCOUNT']);
        	return false;
        }
	}

	/**
	 * Change username
	 *
	 * Changes the username of the user spcified by $id to $username.
	 *
	 * @param integer $id
	 * @param string $username
	 * @return boolean
	 */
	function changeUsername($id, $username)
	{
		global $objDatabase, $_CORELANG;

		if ($this->isValidUsername($username)) {
			if ($this->isUniqueUsername($username, $id)) {
				if ($objDatabase->Execute("UPDATE ".DBPREFIX."access_users SET `username` = '".addslashes($username)."' WHERE `id` = ".intval($id)) !== false) {
					return true;
				}
			} else {
				array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_USERNAME_ALREADY_USED']);
			}
		} else {
			array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_INVALID_USERNAME']);
		}

		return false;
	}

	/**
	 * Change email
	 *
	 * Changes the email address of the user spcified by $id to $email.
	 *
	 * @param integer $id
	 * @param string $email
	 * @return boolean
	 */
	function changeEmail($id, $email)
	{
		global $objDatabase, $_CORELANG;

		if ($this->isValidEmail($email)) {
			if ($this->isUniqueEmail($email)) {
				if ($objDatabase->Execute("UPDATE ".DBPREFIX."access_users SET `email` = '".addslashes($email)."' WHERE `id` = ".intval($id)) !== false) {
					return true;
				}
			} else {
				array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_EMAIL_ALREADY_USED']);
			}
		} else {
			array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_INVALID_EMAIL_ADDRESS']);
		}

		return false;
	}

	/**
	 * Change password
	 *
	 * Changes the password of the user spcified by $id to $password.
	 *
	 * @param integer $id
	 * @param string $password
	 * @return boolean
	 */
	function changePassword($id, $password, $confirmedPassword)
	{
		global $objDatabase, $_CORELANG;

		if ($this->isValidPassword($password)) {
			if ($password == $confirmedPassword) {
				if ($objDatabase->Execute("UPDATE ".DBPREFIX."access_users SET `password` = '".md5($password)."' WHERE `id` = ".intval($id)) !== false) {
					return true;
				}
			} else {
				array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_PASSWORD_NOT_CONFIRMED']);
			}
		} else {
			array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_INVALID_PASSWORD']);
		}

		return false;
	}

	/**
	 * Set admin status
	 *
	 * @param integer $id
	 * @param boolean $status
	 * @return boolean
	 */
	function setAdminStatus($id, $status = false)
	{
		global $objDatabase, $_CORELANG;

		if ($status || !$this->isLastAdmin($id)) {
			if ($objDatabase->Execute("UPDATE ".DBPREFIX."access_users SET `is_admin` = ".intval($status)." WHERE `id` = ".intval($id)) !== false) {
				return true;
			}
		} else {
			array_push($this->arrStatusMsg['error'], sprintf($_CORELANG['TXT_ACCESS_CHANGE_PERM_LAST_ADMIN_USER'], $this->getUsername($id)));
		}
		return false;
	}

	/**
	 * Change language
	 *
	 * Change the language of the user specified by $id to $langId.
	 *
	 * @param integer $id
	 * @param integer $langId
	 * @return boolean
	 */
	function changeLanguage($id, $langId)
	{
		global $objDatabase;

		if ($objDatabase->Execute("UPDATE ".DBPREFIX."access_users SET `langId` = ".intval($langId)." WHERE `id` = ".intval($id)) !== false) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Change active status
	 *
	 * @param integer $id
	 * @param boolean $status
	 * @return boolean
	 */
	function changeActiveStatus($id, $status = false)
	{
		global $objDatabase;

		if ($objDatabase->Execute("UPDATE ".DBPREFIX."access_users SET `active` = ".intval($status)." WHERE `id` = ".intval($id)) !== false) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Update profile
	 *
	 * Updates the profile data of the user specified by $id
	 *
	 * @param integer $id
	 * @param array $arrProfile
	 * @return boolean
	 */
	function updateProfile($id, $arrProfile = array())
	{
		global $objDatabase, $_CORELANG;

		$arrProfile = $this->_cleanProfileData($arrProfile);

		if (count($arrProfile) > 0) {
			$arrTmp = array();

			foreach ($arrProfile as $profileKey => $profileValue) {
				array_push($arrTmp, '`'.$profileKey.'` = '.$profileValue);
			}

			if ($objDatabase->Execute("
				UPDATE ".DBPREFIX."access_user_profile SET ".implode(", ", $arrTmp)." WHERE `user_id` = ".intval($id)
			) !== false) {
				return true;
			} else {
				array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_COULD_NOT_SET_PROFILE_DATA']);
				return false;
			}
		}

		return true;
	}

	function updateUser($id, $username, $is_admin = false, $password, $confirmedPassword, $email, $langId, $arrGroups = array(), $active = false, $arrProfile = array())
	{
		global $objDatabase, $_CORELANG, $objPerm;

		if ($id == $_SESSION['auth']['userid'] && !$objPerm->checkAccess(31, 'static')) {
			array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_ACCESS_DENIED_DESCRIPTION']);
			return false;
		} elseif ($id != $_SESSION['auth']['userid'] && !$objPerm->checkAccess(109, 'static')) {
			array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_ACCESS_DENIED_DESCRIPTION']);
			return false;
		}

		$status = true;

		$arrUser = $this->getUser($id, false);

		// change username
		if ($arrUser['username'] != $username && !$this->changeUsername($id, $username)) {
			$status = false;
		}

		// change email
		if ($arrUser['email'] != $email && !$this->changeEmail($id, $email)) {
			$status = false;
		}

		// change password
		if (!empty($password) && !$this->changePassword($id, $password, $confirmedPassword)) {
			$status = false;
		}

		// change admin status
		if ($arrUser['is_admin'] != $is_admin && !$this->setAdminStatus($id, $is_admin)) {
			$status = false;
		}

		// change language
		if ($arrUser['langId'] != $langId) {
			if ($this->_validateLangId($langId)) {
				if (!$this->changeLanguage($id, $langId)) {
					$status = false;
				}
			} else {
				array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_FAILED_TO_SET_USER_LANGUAGE']);
			}
		}

		// change active status
		if ($arrUser['active'] != $active && !$this->changeActiveStatus($id, $active)) {
			$status = false;
		}

		// set group associations
		if (!$this->setGroupAssociations($id, $arrGroups)) {
			array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_COULD_NOT_SET_GROUP_ASSOCIATIONS']);
			$status = false;
		}

		if (!$this->updateProfile($id, $arrProfile)) {
			$status = false;
		}

		return $status;
	}

	/**
	 * Validate language id
	 *
	 * Checks if the language id specified by the argument $langId is a valid backend language.
	 * In the case that the specified langauge isn't valid, the default system language will be taken instead.
	 *
	 * @param integer $langId
	 * @return mixed
	 */
	function _validateLangId(&$langId)
	{
		global $objDatabase;

		$objLanguage = $objDatabase->SelectLimit("SELECT `id` FROM ".DBPREFIX."languages WHERE (`id` = ".intval($langId)." AND `backend` = 1) OR `is_default` = 'true' GROUP BY `id`", 1);
		if ($objLanguage) {
			$langId = $objLanguage->fields['id'];
			return true;
		} else {
			array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_FAILED_TO_SET_USER_LANGUAGE']);
			return false;
		}
	}

	/**
	 * Clean profile data
	 *
	 * Cleans up the profile data.
	 *
	 * @param array $arrProfile
	 * @return array
	 */
	function _cleanProfileData($arrProfile = array())
	{
		$string = 0;
		$int = 1;

		$arrProfileKeys = array(
			'gender'		=> array_keys($this->arrGenders),
			'firstname'		=> $string,
			'lastname'		=> $string,
			'company'		=> $string,
			'address'		=> $string,
			'city'			=> $string,
			'zip'			=> $string,
			'country_id'	=> $int,
			'phone_office'	=> $string,
			'phone_private'	=> $string,
			'phone_mobile'	=> $string,
			'phone_fax'		=> $string,
			'birthday'		=> $string,
			'website'		=> $string,
			'skype'			=> $string,
			'profession'	=> $string,
			'interests'		=> $string,
			'picture'		=> $string
		);

		$arrClearedProfile = array();

		foreach ($arrProfile as $key => $value) {
			if (isset($arrProfileKeys[$key])) {
				switch ($arrProfileKeys[$key]) {
					case $string:
						$arrClearedProfile[$key] = "'".addslashes($value)."'";
						break;

					case $int:
						$arrClearedProfile[$key] = intval($value);
						break;

					default:
						if (is_array($arrProfileKeys[$key]) && in_array($value, $arrProfileKeys[$key])) {
							$arrClearedProfile[$key] = "'".$value."'";
						}
						break;
				}
			}
		}

		return $arrClearedProfile;
	}

	/**
	 * Set group associations
	 *
	 * Sets the group associations of the user with the ID specified by $userId.
	 * The second argument $arrGroups must be an array which contains
	 * the ID's of the groups that should be associated to the user.
	 * Returns TRUE no success, FALSE on failure.
	 *
	 * @param integer $userId
	 * @param array $arrGroups
	 * @return boolean
	 */
	function setGroupAssociations($userId, $arrGroups = array())
	{
		global $objDatabase;

		$status = true;
		$arrCurrentGroups = $this->getUserGroups($userId, false);
		$arrAddedGroups = array_diff($arrGroups, $arrCurrentGroups);
		$arrRemovedGroups = array_diff($arrCurrentGroups, $arrGroups);

		foreach ($arrRemovedGroups as $groupId) {
			if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."access_rel_user_group WHERE `group_id` = ".intval($groupId)." AND `user_id` = ".intval($userId)) === false) {
				$status = false;
			}
		}

		foreach ($arrAddedGroups as $groupId) {
			if ($objDatabase->Execute("INSERT INTO ".DBPREFIX."access_rel_user_group (`user_id`, `group_id`) VALUES (".intval($userId).", ".intval($groupId).")") === false) {
				$status = false;
			}
		}

		return $status;
	}

	/**
	 * Set user associations
	 *
	 * Sets the user associations of the group with the ID specified by $groupId.
	 * The second argument $arrUsers must be an array which contains
	 * the ID's of the users that should be associated to the group.
	 * Returns TRUE no success, FALSE on failure.
	 *
	 * @param integer $groupId
	 * @param array $arrUsers
	 * @return boolean
	 */
	function setUserAssociations($groupId, $arrUsers = array())
	{
		global $objDatabase;

		$status = true;
		$arrCurrentUsers = $this->getGroupUsers($groupId, false);
		$arrAddedUsers = array_diff($arrUsers, $arrCurrentUsers);
		$arrRemovedUsers = array_diff($arrCurrentUsers, $arrUsers);

		foreach ($arrRemovedUsers as $userId) {
			if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."access_rel_user_group WHERE `group_id` = ".intval($groupId)." AND `user_id` = ".intval($userId)) === false) {
				$status = false;
			}
		}

		foreach ($arrAddedUsers as $userId) {
			if ($objDatabase->Execute("INSERT INTO ".DBPREFIX."access_rel_user_group (`user_id`, `group_id`) VALUES (".intval($userId).", ".intval($groupId).")") === false) {
				$status = false;
			}
		}

		return $status;
	}

	/**
	 * Delete user
	 *
	 * Deletes the user specified by $id
	 *
	 * @param integer $id
	 * @return boolean
	 */
	function deleteUser($id)
	{
		global $objDatabase, $_CORELANG;

		if (($username = $this->getUsername($id))) {
			if ($id != $_SESSION['auth']['userid']) {
				if (!$this->isLastAdmin($id)) {
					if ($objDatabase->Execute("DELETE FROM `".DBPREFIX."access_rel_user_group` WHERE `user_id` = ".$id) !== false &&
					$objDatabase->Execute("DELETE FROM `".DBPREFIX."access_user_profile` WHERE `user_id` = ".$id) !== false &&
					$objDatabase->Execute("DELETE FROM `".DBPREFIX."access_users` WHERE `id` = ".$id) !== false) {
						return true;
					} else {
						array_push($this->arrStatusMsg['error'], sprintf($_CORELANG['TXT_ACCESS_USER_DELETE_FAILED'], $username));
					}
				} else {
					array_push($this->arrStatusMsg['error'], sprintf($_CORELANG['TXT_ACCESS_LAST_ADMIN_USER'], $username));
				}
			} else {
				array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_ACCESS_UNABLE_DELETE_YOUR_USER']);
			}
		} else {
			array_push($this->arrStatusMsg['error'], sprintf($_CORELANG['TXT_ACCESS_NO_USER_WITH_ID'], $id));
		}
		return false;
	}

	/**
	 * Is last admin
	 *
	 * Checks if the user specified by $id is the last admin account in the system.
	 *
	 * @param integer $id
	 * @return boolean
	 */
	function isLastAdmin($id)
	{
		global $objDatabase;

		$objCount = $objDatabase->SelectLimit("SELECT 1 FROM ".DBPREFIX."access_users WHERE id != ".$id." AND `is_admin` = 1", 1);
		if ($objCount && $objCount->RecordCount() == 1) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Get username
	 *
	 * Returns the username of the user specified by the argument $id.
	 *
	 * @param integer $id
	 * @return mixed
	 */
	function getUsername($id)
	{
		global $objDatabase;

		$objUser = $objDatabase->SelectLimit("SELECT `username` FROM ".DBPREFIX."access_users WHERE `id` = ".$id, 1);
		if ($objUser && $objUser->RecordCount() == 1) {
			return $objUser->fields['username'];
		} else {
			return false;
		}
	}

	/**
	 * Get group count
	 *
	 * Returns the number of available groups in the system.
	 *
	 * @param boolean $onlyActive
	 * @return boolean
	 */
	function getGroupCount($onlyActive = true)
	{
		global $objDatabase;

		$objCount = $objDatabase->Execute("SELECT COUNT(1) AS `count` FROM ".DBPREFIX."access_user_groups".($onlyActive ? " WHERE `is_active` = 1" : ""));
		if ($objCount) {
			return $objCount->fields['count'];
		} else {
			return false;
		}
	}

	/**
	 * Get groups
	 *
	 * @param boolean $onlyActive
	 * @param mixed $limitNumber
	 * @param integer $limitOffset
	 * @param mixed $orderBy
	 * @param string $orderDirection
	 * @return mixed
	 */
	function getGroups($onlyActive = true, $limitNumber = false, $limitOffset = 0, $orderBy = false, $orderDirection = 'asc')
	{
		global $objDatabase;

		if ($orderBy && !in_array($orderBy, array('group_id', 'group_name', 'group_description', 'is_active', 'type'))) {
			$orderBy = false;
		} elseif ($orderBy) {
			if (!in_array($orderDirection, array('asc', 'desc'))) {
				$orderDirection = 'asc';
			}
		}

		$query = "SELECT `group_id`, `group_name`, `group_description`, `is_active`, `type` FROM ".DBPREFIX."access_user_groups".($onlyActive ? " WHERE `is_active` = 1" : "").($orderBy ? " ORDER BY ".$orderBy." ".$orderDirection : "");

		if ($limitNumber) {
			$objResult = $objDatabase->SelectLimit($query, $limitNumber, $limitOffset);
		} else {
			$objResult = $objDatabase->Execute($query);
		}

		if ($objResult !== false) {
			while (!$objResult->EOF) {
				$arrGroups[$objResult->fields['group_id']] = array(
					'id'			=> $objResult->fields['group_id'],
					'name'			=> $objResult->fields['group_name'],
					'description'	=> $objResult->fields['group_description'],
					'is_active'		=> $objResult->fields['is_active'],
					'type'			=> $objResult->fields['type']
				);
				$objResult->MoveNext();
			}

			return $arrGroups;
		} else {
			return false;
		}
	}

	/**
	 * Get group
	 *
	 * Returns an array with informations about the group specified by $id
	 *
	 * @param integer $id
	 * @param boolean $onlyActive
	 * @return mixed
	 */
	function getGroup($id, $onlyActive = true)
	{
		global $objDatabase;

		$objResult = $objDatabase->SelectLimit("SELECT `group_name`, `group_description`, `is_active`, `type` FROM ".DBPREFIX."access_user_groups WHERE `group_id`=".$id.($onlyActive ? " ` AND is_active` = 1" : ""), 1);

		if ($objResult !== false && $objResult->RecordCount() == 1) {
			return array(
				'name'			=> $objResult->fields['group_name'],
				'description'	=> $objResult->fields['group_description'],
				'is_active'		=> $objResult->fields['is_active'],
				'type'			=> $objResult->fields['type']
			);
		} else {
			return false;
		}
	}

	/**
	 * Get group users
	 *
	 * Returns the associated users of the group specified by the first arguemnt $groupId.
	 * If the second argument $onlyActive is set to false, then all users and not only the active ones are returned.
	 * In the case that an error ocourrs this method will return the boolean FALSE.
	 *
	 * @param integer $groupId
	 * @param boolean $onlyActive
	 * @return mixed
	 */
	function getGroupUsers($groupId, $onlyActive = true)
	{
		global $objDatabase;

		$arrUsers = array();

		$objUser = $objDatabase->Execute('
			SELECT
				tblRel.`user_id`
			FROM
				'.DBPREFIX.'access_rel_user_group AS tblRel,
				'.DBPREFIX.'access_users AS tblUser
			WHERE
				tblRel.`group_id` = '.$groupId.'
				AND tblUser.`id` = tblRel.`user_id`'
				.($onlyActive ? ' AND tblUser.`active` = 1' : '')
		);
		if ($objUser) {
			while (!$objUser->EOF) {
				array_push($arrUsers, $objUser->fields['user_id']);
				$objUser->MoveNext();
			}

			return $arrUsers;
		} else {
			return false;
		}
	}

	/**
	 * Get user groups
	 *
	 * Returns the associated groups of the user specified by the first arguemnt $userId.
	 * If the second argument $onlyActive is set to false, then all group and not only the active ones are returned.
	 * In the case that an error ocourrs this method will return the boolean FALSE.
	 *
	 * @param integer $userId
	 * @param boolean $onlyActive
	 * @return mixed
	 */
	function getUserGroups($userId, $onlyActive = true)
	{
		global $objDatabase;

		$arrGroups = array();

		$objGroup = $objDatabase->Execute('
			SELECT
				tblRel.`group_id`
			FROM
				'.DBPREFIX.'access_rel_user_group AS tblRel,
				'.DBPREFIX.'access_user_groups AS tblGroup
			WHERE
				tblRel.`user_id` = '.$userId.'
				AND tblGroup.`group_id` = tblRel.`group_id`'
				.($onlyActive ? ' AND tblGroup.`active` = 1' : '')
		);
		if ($objGroup) {
			while (!$objGroup->EOF) {
				array_push($arrGroups, $objGroup->fields['group_id']);

				$objGroup->MoveNext();
			}

			return $arrGroups;
		} else {
			return false;
		}
	}

	function addGroup($name, $description, $status, $type, $arrUsers, $arrStaticPermissions, $arrDynamicPermissions)
	{
		global $objDatabase, $_CORELANG;

		$error = false;

		if (!$this->isValidGroupName($name)) {
			$error = true;
			array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_INVALID_GROUP_NAME']);
		} elseif (!$this->isUniqueGroupName($name)) {
			$error = true;
			array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_DUPLICATE_GROUP_NAME']);
		}

		if (!in_array($type, $this->arrGroupTypes)) {
			$error = true;
			array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_INVALID_GROUP_TYPE']);
		}

		if ($error) {
			return false;
		}

		if ($objDatabase->Execute("
			INSERT INTO `".DBPREFIX."access_user_groups` (
				`group_name`,
				`group_description`,
				`is_active`,
				`type`
			) VALUES (
				'".addslashes($name)."',
				'".addslashes($description)."',
				".intval($status).",
				'".addslashes($type)."'
			)
		") !== false) {
			$id = $objDatabase->Insert_ID();
			if ($this->setUserAssociations($id, $arrUsers)) {
				if ($this->_setPermissions($id, $arrStaticPermissions, 'static')) {
					if ($this->_setPermissions($id, $arrDynamicPermissions, 'dynamic')) {
						return true;
					}
				}
			}
		}

		return false;
	}

	function updateGroup($id, $name, $description, $status, $arrUsers, $arrStaticPermissions, $arrDynamicPermissions)
	{
		global $objDatabase, $_CORELANG;

		$error = false;

		if (!$this->isValidGroupName($name)) {
			$error = true;
			array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_INVALID_GROUP_NAME']);
		} elseif (!$this->isUniqueGroupName($name, $id)) {
			$error = true;
			array_push($this->arrStatusMsg['error'], $_CORELANG['TXT_DUPLICATE_GROUP_NAME']);
		}

		if ($error) {
			return false;
		}

		if ($objDatabase->Execute("UPDATE `".DBPREFIX."access_user_groups` SET `group_name`='".addslashes($name)."', `group_description`='".addslashes($description)."', `is_active`=".intval($status)." WHERE `group_id`=".intval($id)) !== false) {
			if ($this->setUserAssociations($id, $arrUsers)) {
				if ($this->_setPermissions($id, $arrStaticPermissions, 'static')) {
					if ($this->_setPermissions($id, $arrDynamicPermissions, 'dynamic')) {
						return true;
					}
				}
			}
		}

		return false;
	}

	function _setPermissions($groupId, $arrPermissions, $type)
	{
		global $objDatabase;

		$arrAllowedTypes = array('static', 'dynamic');
		if (!in_array($type, $arrAllowedTypes)) {
			return false;
		}

		$arrOldRights = array();
		$objResult = $objDatabase->Execute('SELECT `access_id` FROM `'.DBPREFIX.'access_group_'.$type.'_ids` WHERE `group_id`='.intval($groupId));
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				array_push($arrOldRights, $objResult->fields['access_id']);
				$objResult->MoveNext();
			}
		} else {
			return false;
		}

		$arrRemoveRights = array_diff($arrOldRights, $arrPermissions);
		$arrAddRights = array_diff($arrPermissions, $arrOldRights);

		// remove unused right ids by the group
		foreach ($arrRemoveRights as $rightId) {
			if ($objDatabase->Execute('DELETE FROM `'.DBPREFIX.'access_group_'.$type.'_ids` WHERE `access_id`='.intval($rightId).' AND `group_id`='.intval($groupId)) === false) {
				return false;
			}
		}

		// add new right ids for the group
		foreach ($arrAddRights as $rightId) {
			if ($objDatabase->Execute('INSERT INTO `'.DBPREFIX.'access_group_'.$type.'_ids` (`access_id` , `group_id`) VALUES ('.intval($rightId).','.intval($groupId).')') === false) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Is unique email
	 *
	 * Checks if the email address specified by $email is unique in the system.
	 *
	 * @param string $email
	 * @param integer $id
	 * @return boolean
	 */
	function isUniqueEmail($email, $id = 0)
	{
		global $objDatabase;

		$this->_removeOutdatedAccounts();

		$objResult = $objDatabase->SelectLimit("SELECT 1 FROM ".DBPREFIX."access_users WHERE email='".addslashes($email)."' AND id !=".$id, 1);

		if ($objResult && $objResult->RecordCount() == 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Is valid email
	 *
	 * Checks if the email specified by the argument $email is valid.
	 *
	 * @param string $email
	 * @return boolean
	 */
	function isValidEmail($email)
	{
		$objValidator = &new FWValidator();

		if ($objValidator->isEmail($email)) {
			return true;
		} else {
			return false;
		}
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

	/**
	 * Is valid group name
	 *
	 * Checks if the group name is valid.
	 *
	 * @param string $name
	 * @return boolean
	 */
	function isValidGroupName($name)
	{
		if (preg_match('/^[a-zA-Z0-9-_]+$/', $name)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Is valid password
	 *
	 * Checks if the password is valid
	 *
	 * @param string $password
	 * @return boolean
	 */
	function isValidPassword($password)
	{
		if (strlen($password) >= 6) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Is unique username
	 *
	 * Checks if the username specified by $username is unique in the system.
	 *
	 * @param string $username
	 * @param integer $id
	 * @return boolean
	 */
	function isUniqueUsername($username, $id = 0)
	{
		global $objDatabase;

		$this->_removeOutdatedAccounts();

		$objResult = $objDatabase->SelectLimit("SELECT 1 FROM ".DBPREFIX."access_users WHERE username='".addslashes($username)."' AND id != ".$id, 1);

		if ($objResult && $objResult->RecordCount() == 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Is unique group name
	 *
	 * Checks if the group name specified by $name is unique in the system.
	 *
	 * @param string $name
	 * @param integer $id
	 * @return boolean
	 */
	function isUniqueGroupName($name, $id = 0)
	{
		global $objDatabase;

		$objResult = $objDatabase->SelectLimit("SELECT 1 FROM ".DBPREFIX."access_user_groups WHERE `group_name`='".addslashes($name)."' AND `group_id` != ".$id, 1);

		if ($objResult && $objResult->RecordCount() == 0) {
			return true;
		} else {
			return false;
		}
	}

	function _removeOutdatedAccounts()
	{
		global $objDatabase;

		$objDatabase->Execute("DELETE FROM ".DBPREFIX."access_users WHERE active=0 AND restore_key!='' AND restore_key_time<".time());
	}

	function getGenderMenu($selectedGender, $attrs)
	{
		global $_CORELANG;

		$_CORELANG['TXT_UNKNOWN'] = 'Unbekannt';
		$_CORELANG['TXT_MALE'] = 'M�nnlich';
		$_CORELANG['TXT_FEMALE'] = 'Weiblich';

		$menu = "<select".(!empty($attrs) ? " ".$attrs : "").">\n";
		foreach ($this->arrGenders as $gender => $genderTxt) {
			$menu .= "<option value=\"".$gender."\"".($selectedGender == $gender ? " selected=\"selected\"" : "").">".$_CORELANG[$genderTxt]."</option>\n";
		}
		$menu .= "</select>\n";

		return $menu;
	}

	function getCountries()
	{
		global $objDatabase;

		$arrCountries = array();
		$objCountry = $objDatabase->Execute("SELECT `id`, `name`, `iso_code_2`, `iso_code_3` FROM ".DBPREFIX."lib_country");
		if ($objCountry) {
			while (!$objCountry->EOF) {
				$arrCountries[$objCountry->fields['id']] = array(
					'name'			=> $objCountry->fields['name'],
					'iso_code_2'	=> $objCountry->fields['iso_code_2'],
					'iso_code_3'	=> $objCountry->fields['iso_code_3']
				);

				$objCountry->MoveNext();
			}

			return $arrCountries;
		} else {
			return false;
		}
	}

	function getCountryMenu($selectedCountryId, $attrs)
	{
		global $_CORELANG;

		$arrCountries = $this->getCountries();

		$menu = "<select".(!empty($attrs) ? " ".$attrs : "").">\n";
		$menu .= "<option value=\"0\" style=\"border-bottom:1px solid #000000;\">".$_CORELANG['TXT_UNKNOWN']."</option>\n";
		foreach ($arrCountries as $countryId => $arrCountry) {
			$menu .= "<option value=\"".$countryId."\"".($selectedCountryId == $countryId ? " selected=\"selected\"" : "").">".htmlentities($arrCountry['name'])."</option>\n";
		}
		$menu .= "</select>\n";

		return $menu;
	}

	function getLanguageMenu($selectedLanguageId, $attrs)
	{
		global $objDatabase;

		$objLanguage = $objDatabase->Execute("SELECT `id`, `lang`, `name`, `is_default` FROM ".DBPREFIX."languages WHERE `backend` = 1 OR `is_default` = 'true' GROUP BY `id` ORDER BY `name`");
		if ($objLanguage) {
			$menu = "<select".(!empty($attrs) ? " ".$attrs : "").">\n";

			while (!$objLanguage->EOF) {
				$menu .= "<option value=\"".$objLanguage->fields['id']."\"".(($selectedLanguageId == $objLanguage->fields['id'] || ($selectedLanguageId == 0 && $objLanguage->fields['is_default'] == 'true')) ? ' selected="selected"' : "").">".htmlentities($objLanguage->fields['name']." (".$objLanguage->fields['lang'].")", ENT_QUOTES)."</option>\n";
				$objLanguage->MoveNext();
			}

			$menu .= "</select>";

			return $menu;
		}

		return false;
	}

	function getBirthdayMenu($birthday = '', $attrsDay, $attrsMonth, $attrsYear)
	{
		global $_CORELANG;

		$arrMonths = explode(',', $_CORELANG['TXT_MONTH_ARRAY']);

		if (!empty($birthday)) {
			$birthday = (is_array($birthday)) ? $birthday : explode('-', $birthday);
			$day	= !empty($birthday[0]) ? $birthday[0] : '01';
			$month	= !empty($birthday[1]) ? $birthday[1] : '01';
			$year	= !empty($birthday[2]) ? $birthday[2] : date("Y");
		} else {
			$day	= '01';
			$month	= '01';
			$year	= date("Y");
		}

		$dropDownDay = "<select".(!empty($attrsDay) ? " ".$attrsDay : "").">\n";
		for ($i = 1; $i <= 31; $i++) {
			$dropDownDay .= "<option value=\"".str_pad($i,2,'0', STR_PAD_LEFT)."\"".(($day == str_pad($i, 2, '0', STR_PAD_LEFT)) ? ' selected="selected"' : '').">".$i."</option>\n";
		}
		$dropDownDay .= "</select>\n";

		$dropDownMonth = "<select".(!empty($attrsMonth) ? " ".$attrsMonth : "").">\n";
		for ($i = 1; $i <= 12; $i++) {
			$dropDownMonth .= "<option value=\"".str_pad($i, 2, '0', STR_PAD_LEFT)."\"".(($month == str_pad($i,2,'0',STR_PAD_LEFT)) ? ' selected="selected"' : '').">".$arrMonths[$i-1]."</option>\n";
		}
		$dropDownMonth .= "</select>\n";

		$dropDownYear = "<select".(!empty($attrsYear) ? " ".$attrsYear : "").">\n";
		for ($i = date("Y"); $i >= 1900; $i--) {
			$dropDownYear .= "<option value=\"".$i."\"".(($year == $i) ? ' selected="selected"' : '').">".$i."</option>\n";
		}
		$dropDownYear .= "</select>\n";

		return $dropDownDay.$dropDownMonth.$dropDownYear;
	}
	
	function getProfileAttributes($langId, $parentId = 0)
	{
		global $objDatabase;
		
		$objProfile = $objDatabase->Execute('
			SELECT
				a.id,
				a.type,
				a.order_id,
				a.mandatory,
				a.parent_id,
				n.name
			FROM '.DBPREFIX.'access_user_attribute AS a
			INNER JOIN '.DBPREFIX.'access_user_attribute_name AS n ON n.attribute_id = a.id
			WHERE n.lang_id = '.$langId.' AND a.parent_id = '.$parentId
		);
	}
}
?>
