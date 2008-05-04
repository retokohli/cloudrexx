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

class FWUser extends User_Setting
{
	var $arrStatusMsg = array(
		'ok'		=> array(),
		'error'		=> array()
	);


	var $backendMode;

	var $objUser;
	var $objGroup;

	function __construct($backend = false)
	{
		parent::__construct();

		$this->setMode($backend);

		$this->objUser = new User();
		$this->objGroup = new UserGroup();
	}

	function setMode($backend = false)
	{
		$this->backendMode = $backend;
	}

	function isBackendMode()
	{
		return $this->backendMode;
	}

	function checkAuth()
    {
        global $sessionObj, $_CORELANG;

        $username = isset($_POST['USERNAME']) && $_POST['USERNAME'] != '' ? contrexx_stripslashes($_POST['USERNAME']) : null;
        $password = isset($_POST['PASSWORD']) && $_POST['PASSWORD'] != '' ? md5(contrexx_stripslashes($_POST['PASSWORD'])) : null;
        $validationCode = isset($_POST['secid2']) && $_POST['secid2'] != '' ? contrexx_stripslashes($_POST['secid2']) : false;

        if (isset($username) && isset($password)) {
            if ($this->isBackendMode()) {
                if (!$this->checkCode($validationCode)) {
                    $this->arrStatusMsg['error'][] = $_CORELANG['TXT_SECURITY_CODE_IS_INCORRECT'];
                    return false;
                }
            }

			if ($this->objUser->auth($username, $password, $this->isBackendMode())) {
				if ($this->isBackendMode()) {
					// sets cookie for 30 days
					setcookie("username", $this->objUser->getUsername(), time()+3600*24*30);
					$this->log();
				}
				$sessionObj->cmsSessionUserUpdate($this->objUser->getId());
				return true;
			} else {
				$this->arrStatusMsg['error'][] = $_CORELANG['TXT_PASSWORD_OR_USERNAME_IS_INCORRECT'];
			}
        }

        $sessionObj->cmsSessionUserUpdate();
        $sessionObj->cmsSessionStatusUpdate($this->isBackendMode() ? 'backend' : 'frontend');
        return false;
    }

    function logout()
    {
    	if (isset($_SESSION['auth'])) {
    		unset($_SESSION['auth']);
    	}
		session_destroy();

        if ($this->backendMode) {
        	header('Location: ../'.CONTREXX_DIRECTORY_INDEX);
        } else {
            header('Location: '.(!empty($_REQUEST['redirect']) ? urldecode($_REQUEST['redirect']) : CONTREXX_DIRECTORY_INDEX.'?section=login'));
        }
        exit;
    }

    /**
     * Log the user session.
     *
     * Create a log entry in the database containing the users' details.
     * @global mixed  Database
     */
    function log()
    {
        global $objDatabase;

        if (!isset($_SESSION['auth']['log'])) {
            $remote_host = @gethostbyaddr($_SERVER['REMOTE_ADDR']);
            $referer = get_magic_quotes_gpc() ? strip_tags((strtolower($_SERVER['HTTP_REFERER']))) : addslashes(strip_tags((strtolower($_SERVER['HTTP_REFERER']))));
            $httpUserAgent = get_magic_quotes_gpc() ? strip_tags($_SERVER['HTTP_USER_AGENT']) : addslashes(strip_tags($_SERVER['HTTP_USER_AGENT']));
            $httpAcceptLanguage = get_magic_quotes_gpc() ? strip_tags($_SERVER['HTTP_ACCEPT_LANGUAGE']) : addslashes(strip_tags($_SERVER['HTTP_ACCEPT_LANGUAGE']));

            $objFWUser = FWUser::getFWUserObject();
            $objDatabase->Execute("INSERT INTO ".DBPREFIX."log
                                        SET userid=".$objFWUser->objUser->getId().",
                                            datetime = ".$objDatabase->DBTimeStamp(time()).",
                                            useragent = '".$httpUserAgent."',
                                            userlanguage = '".$httpAcceptLanguage."',
                                            remote_addr = '".strip_tags($_SERVER['REMOTE_ADDR'])."',
                                            remote_host = '".$remote_host."',
                                            http_x_forwarded_for = '".(isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? strip_tags($_SERVER['HTTP_X_FORWARDED_FOR']) : '')."',
                                            http_via = '".(isset($_SERVER['HTTP_VIA']) ? strip_tags($_SERVER['HTTP_VIA']) : '')."',
                                            http_client_ip = '".(isset($_SERVER['HTTP_CLIENT_IP']) ? strip_tags($_SERVER['HTTP_CLIENT_IP']) : '')."',
                                            referer ='".$referer."'");
            $_SESSION['auth']['log']=true;
        }
    }

    function getErrorMsg()
    {
    	return implode('<br />', $this->arrStatusMsg['error']);
    }

    /**
     * Checks the code from the security image.
     *
     * This function compares the security image code with the
     * code present in the current session.
     * @access private
     * @return bool true if the images are identical
     */
    function checkCode($validationCode)
    {
        return $_SESSION['auth']['secid'] === $validationCode;
    }

	function setLoggedInInfos()
	{
		global $_CORELANG, $objTemplate;

		if (!$this->objUser->login()) {
			return false;
		}

		$objTemplate->setVariable(array(
			'LOGGING_STATUS'	=> $_CORELANG['TXT_LOGGED_IN_AS']." ".htmlentities($this->objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET),

		));
	}

	/**
    * Restore password of user account
    *
    * Sends an email with instructions on how to reset the password to the user specified by an e-mail address.
    *
    * @param  string $email
    * @global array $_CORELANG
    * @global array $_CONFIG
    * @global integer $_LANGID
    */
    public function restorePassword($email)
    {
        global $_CORELANG, $_CONFIG, $_LANGID;

		if ($objUser = $this->objUser->getUsers(array('email' => $email), null, null, null, 1)) {
			$objUser->setRestoreKey();

			if ($objUser->store() &&
				(
					$this->objMail->load('reset_pw', $_LANGID) ||
					$this->objMail->load('reset_pw')
				) &&
				(include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') &&
				($objMail = new PHPMailer()) !== false
			) {
				if ($_CONFIG['coreSmtpServer'] > 0 && @include_once ASCMS_CORE_PATH.'/SmtpSettings.class.php') {
					$objSmtpSettings = new SmtpSettings();
					if (($arrSmtp = $objSmtpSettings->getSmtpAccount($_CONFIG['coreSmtpServer'])) !== false) {
						$objMail->IsSMTP();
						$objMail->Host = $arrSmtp['hostname'];
						$objMail->Port = $arrSmtp['port'];
						$objMail->SMTPAuth = true;
						$objMail->Username = $arrSmtp['username'];
						$objMail->Password = $arrSmtp['password'];
					}
				}

				$objMail->CharSet = CONTREXX_CHARSET;
				$objMail->From = $this->objMail->getSenderMail();
				$objMail->FromName = $this->objMail->getSenderName();
				$objMail->AddReplyTo($this->objMail->getSenderMail());
				$objMail->Subject = $this->objMail->getSubject();

				if ($this->isBackendMode()) {
					$restorLink = strtolower(ASCMS_PROTOCOL)."://".$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.ASCMS_BACKEND_PATH."/index.php?cmd=resetpw&username=".urlencode($objUser->getUsername())."&restoreKey=".$objUser->getRestoreKey();
				} else {
                    $restorLink = strtolower(ASCMS_PROTOCOL)."://".$_CONFIG['domainUrl'].CONTREXX_SCRIPT_PATH."?section=login&cmd=resetpw&username=".urlencode($objUser->getUsername())."&restoreKey=".$objUser->getRestoreKey();
                }

				if (in_array($this->objMail->getFormat(), array('multipart', 'text'))) {
					$this->objMail->getFormat() == 'text' ? $objMail->IsHTML(false) : false;
					$objMail->{($this->objMail->getFormat() == 'text' ? '' : 'Alt').'Body'} = str_replace(
						array(
							'[[USERNAME]]',
							'[[URL]]',
							'[[SENDER]]'
						),
						array(
							$objUser->getUsername(),
							$restorLink,
							$this->objMail->getSenderName()
						),
						$this->objMail->getBodyText()
					);
				}
				if (in_array($this->objMail->getFormat(), array('multipart', 'html'))) {
					$this->objMail->getFormat() == 'html' ? $objMail->IsHTML(true) : false;
					$objMail->Body = str_replace(
						array(
							'[[USERNAME]]',
							'[[URL]]',
							'[[SENDER]]'
						),
						array(
							htmlentities($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET),
							$restorLink,
							htmlentities($this->objMail->getSenderName(), ENT_QUOTES, CONTREXX_CHARSET)
						),
						$this->objMail->getBodyHtml()
					);
				}

				$objMail->AddAddress($objUser->getEmail());


				if ($objMail->Send()) {
					return true;
				} else {
					$this->arrStatusMsg['error'][] = str_replace("%EMAIL%", $email, $_CORELANG['TXT_EMAIL_NOT_SENT']);
				}
			} else {
				$this->arrStatusMsg['error'][] = str_replace("%EMAIL%", $email, $_CORELANG['TXT_EMAIL_NOT_SENT']);
			}
		} else {
            $this->arrStatusMsg['error'][] = $_CORELANG['TXT_ACCOUNT_WITH_EMAIL_DOES_NOT_EXIST']."<br />";
        }

        return false;
    }

    /**
    * Reset the password of the user using a reset form.
    * @access  public
    * @param   mixed  $objTemplate Template
    * @global  mixed  Database
    * @global  array  Core language
    */
    function resetPassword($username, $restoreKey, $password = null, $confirmedPassword = null, $store = false)
    {
        global $_CORELANG;

        $userFilter = array(
        	'username'			=> $username,
        	'restore_key'		=> $restoreKey,
        	'restore_key_time'	=> array(
				array(
					'>' => time()
				),
				'=' => time()
			),
        	'active'			=> 1
        );

		if ($objUser = $this->objUser->getUsers($userFilter, null, null, null, 1)) {
			if ($store) {
				if ($objUser->setPassword($password, $confirmedPassword, true) &&
					$objUser->releaseRestoreKey() &&
					$objUser->store()
				) {
					return true;
				} else {
					$this->arrStatusMsg['error'] = array_merge($this->arrStatusMsg['error'], $objUser->getErrorMsg());
				}
			} else {
				return true;
			}
		} else {
			$this->arrStatusMsg['error'][] = $_CORELANG['TXT_INVALID_USER_ACCOUNT'];
		}

		return false;
    }

    public static function showCurrentlyOnlineUsers()
    {
		$arrSettings = User_Setting::getSettings();
		return $arrSettings['block_currently_online_users']['status'];
    }

    public static function showLastActivUsers()
    {
		$arrSettings = User_Setting::getSettings();
		return $arrSettings['block_last_active_users']['status'];
    }

    public static function showLatestRegisteredUsers()
    {
		$arrSettings = User_Setting::getSettings();
		return $arrSettings['block_latest_reg_users']['status'];
    }

    public static function showBirthdayUsers()
    {
		$arrSettings = User_Setting::getSettings();
		return $arrSettings['block_birthday_users']['status'];
    }

    public static function getFWUserObject()
	{
		global $objInit;

		static $objFWUser;

		if (!isset($objFWUser)) {
			$objFWUser = new FWUser($objInit->mode == 'backend');
		}

		return $objFWUser;
	}

	public static function getValidityMenuOptions($selectedValidity = null, $attrs = null)
	{
		foreach (User_Setting::getUserValidities() as $validity) {
			$strValidity = FWUser::getValidityString($validity);
			$strOptions .=
			// Use original value in days as option value.
			'<option value="'.$validity.'"'.
			($selectedValidity === $validity
			? ' selected="selected"' : ''
			).(!empty($attrs) ? ' '.$attrs : null).
			'>'.$strValidity.'</option>';
		}

        return $strOptions;
	}

	/**
     * Returns a pretty textual representation of the validity period
     * specified by the $validity argument.
     *
     * @param   integer   $validity     Validity period in days
     * @return  string                  The textual representation
     */
    public static function getValidityString($validity)
    {
        global $_CORELANG;

        $unit = 'DAY';
        if ($validity == 0) {
            $validity = '';
            $unit = $_CORELANG['TXT_USERS_UNLIMITED'];
        } else {
            if ($validity >= 30) {
                $unit = 'MONTH';
                $validity = intval($validity/30);
                if ($validity >= 12) {
                    $unit = 'YEAR';
                    $validity = intval($validity/12);
                }
            }
            $unit =
                $_CORELANG['TXT_USERS_'.$unit.
                ($validity > 1 ? 'S' : '')];
        }
        return "$validity $unit";
    }
}


?>
