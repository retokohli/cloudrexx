<?php
/**
 * Community
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_community
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH.'/community/lib/communityLib.class.php';

/**
 * Community
 *
 * Class with methods to manage the community
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @access public
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  module_community
 */
class Community extends Community_Library
{
	/**
	* Template object
	*
	* @access private
	* @var object
	*/
	var $_objTpl;

	/**
	* Status message
	*
	* @access private
	* @var string
	*/
	var $_statusMessage = "";

	/**
	* Constructor
	*
	* @param string $pageContent
	* @see __construct()
	*/
	function Community($pageContent)
	{
		$this->__construct($pageContent);
	}

	/**
	* PHP5 constructor
	*
	* @param string $pageContent
	* @see HTML_Template_Sigma(), HTML_Template_Sigma::setErrorHandling(), HTML_Template_Sigma::setTemplate(), initialize()
	*/
	function __construct($pageContent)
	{
		$this->_objTpl = &new HTML_Template_Sigma('.');
		$this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
		$this->_objTpl->setTemplate($pageContent);

		$this->initialize();
	}

	/**
	* Get community page
	*
	* @access public
	* @see _register(), _showRegisterPage(), _activate(), _showActivationPage(), _profile(), _showProfilePage(), $_objTpl, HTML_Template_Sigma::get()
	* @return string content
	*/
	function getCommunityPage()
	{
		if (!isset($_GET['cmd'])) {
			$_GET['cmd'] = "";
		}

		switch ($_GET['cmd']) {
		case 'register':
			$this->_register();
			$this->_showRegisterPage();
			break;

		case 'activate':
			$this->_activate();
			$this->_showActivationPage();
			break;

		case 'profile':
			$this->_profile();
			$this->_showProfilePage();
			break;

		default:
			break;
		}

		return $this->_objTpl->get();
	}

	/**
	* Register a new user
	*
	* @access private
	* @global array $_ARRAYLANG
	* @global integer $_LANGID
	*/
	function _register()
	{
		global $_ARRAYLANG, $_LANGID, $_CONFIG;

		$status = true;

		if (isset($_POST['register'])) {
			$objValidator = &new FWValidator();
			$objUser = &new FWUser();

			$_POST['username'] = contrexx_strip_tags($_POST['username']);
			$_POST['email'] = contrexx_strip_tags($_POST['email']);
			$_POST['password'] = contrexx_strip_tags($_POST['password']);
			$_POST['password2'] = contrexx_strip_tags($_POST['password2']);
			$_POST['residence'] = contrexx_strip_tags($_POST['residence']);
			$_POST['zip'] = contrexx_strip_tags($_POST['zip']);
			$_POST['firstname'] = contrexx_strip_tags($_POST['firstname']);
			$_POST['lastname'] = contrexx_strip_tags($_POST['lastname']);

			if (empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['password2']) || empty($_POST['residence']) || empty($_POST['zip'])) {
				$this->_statusMessage .= $_ARRAYLANG['TXT_FILL_OUT_ALL_REQUIRED_FIELDS']."<br />";
				$status = false;
			} else {
				if (!$objUser->checkUsernameIntegrity($_POST['username'])) {
					$this->_statusMessage .= $_ARRAYLANG['TXT_USERNAME_ALREADY_USED']."<br />";
					$status = false;
				} elseif (!$objUser->isValidUsername($_POST['username'])) {
					$this->_statusMessage .= 'Der Benutzername darf nur aus Alphanumerischen Zeichen (a-z/A-Z/0-9) und den folgenden Sonderzeichen bestehen: -_<br />';
					$status = false;
				} else {
					if (!$objValidator->isEmail($_POST['email'])) {
						$this->_statusMessage .= $_ARRAYLANG['TXT_INVALID_EMAIL_ADDRESS']."<br />";
						$status = false;
					} else {
						if (!$objUser->checkEmailIntegrity($_POST['email'])) {
							$this->_statusMessage .= $_ARRAYLANG['TXT_EMAIL_ALREADY_USED']."<br />";
							$status = false;
						}
					}
					if (strlen($_POST['password'])<6) {
						$this->_statusMessage .= $_ARRAYLANG['TXT_INVALID_PASSWORD']."<br />";
						$status = false;
					} elseif ($_POST['username'] == $_POST['password']) {
						$this->_statusMessage .= $_ARRAYLANG['TXT_PASSWORD_NOT_USERNAME_TEXT']."<br />";
						$status = false;
					} elseif ($_POST['password'] != $_POST['password2']) {
						$this->_statusMessage .= $_ARRAYLANG['TXT_PW_DONOT_MATCH']."<br />";
						$status = false;
					}
				}
			}

			if ($status) {
				$groups = $this->arrConfig['community_groups']['value'];

				if ($this->arrConfig['user_activation']['status']) {
					$activationKey = md5($_POST['username'].$_POST['password'].time());

					if ($objUser->addUser($_POST['username'], 0, $_POST['password'], $_POST['email'], $_POST['firstname'], $_POST['lastname'],  $_POST['residence'],  $_POST['zip'], $_LANGID, $groups, 0, $activationKey, time() + ($this->arrConfig['user_activation_timeout']['value'] * 3600)) !== false) {
						$sendto = $_POST['email'];
						$subject = str_replace("%HOST%", $_CONFIG['domainUrl'], $_ARRAYLANG['TXT_CONFIRM_REGISTRATION']);
						$activationLink = "http://".$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET."/index.php?section=community&cmd=activate&username=".$_POST['username']."&activationKey=".$activationKey;
						$hostLink = "http://".$_CONFIG['domainUrl'];
						$message = str_replace(array("%HOST%","%USERNAME%","%PASSWORD%", "%ACTIVATION_LINK%", "%HOST_LINK%"), array($_CONFIG['domainUrl'], $_POST['username'], $_POST['password'], $activationLink, $hostLink), $_ARRAYLANG['TXT_CONFIRM_REGISTRATION_MAIL']);

						if (@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') {
							$objMail = new phpmailer();

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
							$objMail->From = $_CONFIG['coreAdminEmail'];
							$objMail->FromName = $_CONFIG['coreAdminName'];
							$objMail->AddReplyTo($_CONFIG['coreAdminEmail']);
							$objMail->Subject = $subject;
							$objMail->IsHTML(false);
							$objMail->Body = $message;
							$objMail->AddAddress($sendto);
						}

						if ($objMail && $objMail->Send()) {
							$timeoutStr = "";
							if ($this->arrConfig['user_activation_timeout']['status']) {
								if ($this->arrConfig['user_activation_timeout']['value'] > 1) {
									$timeoutStr = $this->arrConfig['user_activation_timeout']['value']." ".$_ARRAYLANG['TXT_HOURS_IN_STR'];
								} else {
									$timeoutStr = " ".$_ARRAYLANG['TXT_HOUR_IN_STR'];
								}

								$timeoutStr = str_replace("%TIMEOUT%", $timeoutStr, $_ARRAYLANG['TXT_ACTIVATION_TIMEOUT']);
							}
							$this->_statusMessage = $_ARRAYLANG['TXT_USER_ACCOUNT_SUCCESSFULLY_CREATED']."<br /><br />".str_replace("%TIMEOUT%", $timeoutStr, $_ARRAYLANG['TXT_ACTIVATION_BY_USER_MSG']);
						} else {
							$mailSubject = str_replace("%HOST%", "http://".$_CONFIG['domainUrl'], $_ARRAYLANG['TXT_COULD_NOT_SEND_ACTIVATION_MAIL']);
							$adminEmail = '<a href="mailto:'.$_CONFIG['coreAdminEmail'].'?subject='.$mailSubject.'" title="'.$_CONFIG['coreAdminEmail'].'">'.$_CONFIG['coreAdminEmail'].'</a>';
							$this->_statusMessage = str_replace("%EMAIL%", $adminEmail, $_ARRAYLANG['TXT_COULD_NOT_SEND_EMAIL']);
						}
					} else {
						$this->_statusMessage .= $_ARRAYLANG['TXT_DATABASE_QUERY_ERROR'];
					}
				} else {
					if ($objUser->addUser($_POST['username'], 0, $_POST['password'], $_POST['email'], $_POST['firstname'], $_POST['lastname'], $_POST['residence'],  $_POST['zip'], $_LANGID, $groups, 0, $activationKey) !== false) {
						$this->_statusMessage .= $_ARRAYLANG['TXT_USER_ACCOUNT_SUCCESSFULLY_CREATED']."<br /><br />";
						$this->_statusMessage .= str_replace("%HOST%", $_CONFIG['domainUrl'], $_ARRAYLANG['TXT_ACTIVATION_BY_SYSTEM']);
					} else {
						$this->_statusMessage .= $_ARRAYLANG['TXT_DATABASE_QUERY_ERROR'];
					}
				}

				if ($this->_objTpl->blockExists('community_registration_form')) {
					$this->_objTpl->hideBlock('community_registration_form');
				}
			} else {
				if ($this->_objTpl->blockExists('community_registration_form')) {
					$this->_objTpl->touchBlock('community_registration_form');
				}
			}
		}
	}

	/**
	* Activate user account
	*
	* @access private
	* @global object $objDatabase
	* @global array $_ARRAYLANG
	* @global array $_CONFIG
	*/
	function _activate()
	{
		global $objDatabase, $_ARRAYLANG, $_CONFIG;

		if (isset($_GET['username']) && $_GET['activationKey']) {
			$username = contrexx_addslashes($_GET['username']);
			$activationKey = contrexx_addslashes($_GET['activationKey']);
			$mailSubject = str_replace("%HOST%", "http://".$_CONFIG['domainUrl'], $_ARRAYLANG['TXT_ACCOUNT_ACTIVATION_NOT_POSSIBLE']);
			$adminEmail = '<a href="mailto:'.$_CONFIG['coreAdminEmail'].'?subject='.$mailSubject.'" title="'.$_CONFIG['coreAdminEmail'].'">'.$_CONFIG['coreAdminEmail'].'</a>';
			$status = true;

			if ($this->arrConfig['user_activation_timeout']['status']) {
				$objResult = $objDatabase->Execute("SELECT restore_key_time FROM ".DBPREFIX."access_users WHERE username='".$username."' AND restore_key='".$activationKey."'");
				if ($objResult !== false) {
					if ($objResult->RecordCount() == 1) {
						if ($objResult->fields['restore_key_time'] < time()) {
							$this->_statusMessage = $_ARRAYLANG['TXT_ACTIVATION_TIME_EXPIRED'].'<br /><a href="index.php?section=community&amp;cmd=register" title="'.$_ARRAYLANG['TXT_REGISTER_NEW_ACCOUNT'].'">'.$_ARRAYLANG['TXT_REGISTER_NEW_ACCOUNT'].'</a>';
							$status = false;
						}
					} else {
						$this->_statusMessage = str_replace("%EMAIL%", $adminEmail, $_ARRAYLANG['TXT_INVALID_USERNAME_OR_ACTIVATION_KEY']);
						$status = false;
					}
				} else {
					$this->_statusMessage = $_ARRAYLANG['TXT_DATABASE_QUERY_ERROR'];
				}
			}

			if ($status) {
				if ($objDatabase->Execute("UPDATE ".DBPREFIX."access_users SET active=1, restore_key='', restore_key_time='0' WHERE username='".$username."' AND restore_key='".$activationKey."'") !== false) {
					if ($objDatabase->Affected_Rows() == 1) {
						$this->_statusMessage = $_ARRAYLANG['TXT_ACCOUNT_SUCCESSFULLY_ACTIVATED'];
					} else {
						$this->_statusMessage = str_replace("%EMAIL%", $adminEmail, $_ARRAYLANG['TXT_INVALID_USERNAME_OR_ACTIVATION_KEY']);
					}
				} else {
					$this->_statusMessage = $_ARRAYLANG['TXT_DATABASE_QUERY_ERROR'];
				}
			}
		} else {
			$this->_statusMessage = str_replace("%EMAIL%", $adminEmail, $_ARRAYLANG['TXT_INVALID_USERNAME_OR_ACTIVATION_KEY']);
		}
	}

	/**
	* Change user profile data
	*
	* @access private
	* @global object $objAuth
	* @global object $objDatabase
	* @global array $_ARRAYLANG
	*/
	function _profile()
	{
		global $objAuth, $_ARRAYLANG, $objDatabase, $_CONFIG;

		if ($objAuth->checkAuth()) {
			if (isset($_POST['change_profile'])) {
				$_POST['firstname'] = contrexx_strip_tags($_POST['firstname']);
				$_POST['lastname'] = contrexx_strip_tags($_POST['lastname']);
				$_POST['residence'] = contrexx_strip_tags($_POST['residence']);
				$_POST['profession'] = contrexx_strip_tags($_POST['profession']);
				$_POST['interests'] = contrexx_strip_tags($_POST['interests']);
				$_POST['webpage'] = contrexx_strip_tags($_POST['webpage']);
				$_POST['company'] = contrexx_strip_tags($_POST['company']);
				$_POST['street'] = contrexx_strip_tags($_POST['street']);
				$_POST['zip'] = contrexx_strip_tags($_POST['zip']);
				$_POST['phone'] = contrexx_strip_tags($_POST['phone']);
				$_POST['mobile'] = contrexx_strip_tags($_POST['mobile']);

				if ($objDatabase->Execute("UPDATE ".DBPREFIX."access_users
					SET `firstname`='".contrexx_addslashes($_POST['firstname'])."',
						`lastname`='".contrexx_addslashes($_POST['lastname'])."',
						`residence`='".contrexx_addslashes($_POST['residence'])."',
						`profession`='".contrexx_addslashes($_POST['profession'])."',
						`interests`='".contrexx_addslashes($_POST['interests'])."',
						`webpage`='".contrexx_addslashes($_POST['webpage'])."',
						`street`='".contrexx_addslashes($_POST['street'])."',
						`zip`='".contrexx_addslashes($_POST['zip'])."',
						`phone`='".contrexx_addslashes($_POST['phone'])."',
						`mobile`='".contrexx_addslashes($_POST['mobile'])."',
						`company`='".contrexx_addslashes($_POST['company'])."'
					WHERE username='".contrexx_addslashes(contrexx_strip_tags($_SESSION['auth']['username']))."'") !== false) {
					$this->_statusMessage = $_ARRAYLANG['TXT_PROFILE_DATA_SUCCESSFULLY_CHANGED'];
				} else {
					$this->_statusMessage = $_ARRAYLANG['TXT_DATABASE_QUERY_ERROR'];
				}
				$this->_objTpl->setVariable('COMMUNITY_STATUS_MESSAGE_PROFILE', $this->_statusMessage);
			} elseif (isset($_POST['change_email'])) {
				$objUser = &new FWUser();
				$objValidator = &new FWValidator();

				$_POST['email'] = contrexx_strip_tags($_POST['email']);
				$_POST['email2'] = contrexx_strip_tags($_POST['email2']);

				if (empty($_POST['email']) || empty($_POST['email2'])) {
					$this->_statusMessage = $_ARRAYLANG['TXT_FILL_OUT_ALL_REQUIRED_FIELDS'];
				} elseif ($_POST['email'] != $_POST['email2']) {
					$this->_statusMessage = $_ARRAYLANG['TXT_EMAIL_DO_NOT_MATCH'];
				} elseif (!$objValidator->isEmail($_POST['email'])) {
					$this->_statusMessage = $_ARRAYLANG['TXT_INVALID_EMAIL_ADDRESS'];
				} elseif (!$objUser->checkEmailIntegrity($_POST['email'])) {
					$this->_statusMessage = $_ARRAYLANG['TXT_EMAIL_ALREADY_USED'];
				} else {
					if ($objDatabase->Execute("UPDATE ".DBPREFIX."access_users SET email='".contrexx_addslashes($_POST['email'])."' WHERE username='".contrexx_addslashes(contrexx_strip_tags($_SESSION['auth']['username']))."'") !== false) {
						$this->_statusMessage = $_ARRAYLANG['TXT_EMAIL_SUCCESSFULLY_CHANGED'];
					} else {
						$this->_statusMessage = $_ARRAYLANG['TXT_DATABASE_QUERY_ERROR'];
					}
				}

				$this->_objTpl->setVariable('COMMUNITY_STATUS_MESSAGE_EMAIL', $this->_statusMessage);
			} elseif (isset($_POST['change_password'])) {
				$_POST['password'] = contrexx_strip_tags($_POST['password']);
				$_POST['password2'] = contrexx_strip_tags($_POST['password2']);

				if (strlen($_POST['password'])<6) {
						$this->_statusMessage .= $_ARRAYLANG['TXT_INVALID_PASSWORD']."<br />";
				} elseif (contrexx_strip_tags($_SESSION['auth']['username']) == $_POST['password']) {
					$this->_statusMessage .= $_ARRAYLANG['TXT_PASSWORD_NOT_USERNAME_TEXT']."<br />";
				} elseif ($_POST['password'] != $_POST['password2']) {
					$this->_statusMessage .= $_ARRAYLANG['TXT_PW_DONOT_MATCH']."<br />";
				} else {
					$password = md5($_POST['password']);
					if ($objDatabase->Execute("UPDATE ".DBPREFIX."access_users SET password='".$password."', restore_key='' WHERE username='".contrexx_addslashes(contrexx_strip_tags($_SESSION['auth']['username']))."'") !== false) {
						$this->_statusMessage = $_ARRAYLANG['TXT_PASSWORD_CHANGED_SUCCESSFULLY'];
						$_POST['PASSWORD'] = $_POST['password'];
						$objAuth->checkAuth();
					} else {
						$this->_statusMessage = $_ARRAYLANG['TXT_DATEBASE_QUERY_ERROR'];
					}
				}
				$this->_objTpl->setVariable('COMMUNITY_STATUS_MESSAGE_PASSWORD', $this->_statusMessage);
			}



		} else {
			header('Location: index.php?section=login&redirect='.base64_encode(ASCMS_PROTOCOL.'://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.'/index.php?section=community&cmd=profile'));
			exit;
		}
	}

	/**
	* Show the activation page
	*
	* @access private
	*/
	function _showActivationPage()
	{
		$this->_objTpl->setVariable('COMMUNITY_STATUS_MESSAGE', $this->_statusMessage);
	}

	/**
	* Show the registration page
	*
	* @access private
	* @global array $_ARRAYLANG
	*/
	function _showRegisterPage()
	{
		global $_ARRAYLANG;

		$username = "";
		$email = "";
		$zip = "";
		$residence = "";
		$firstname = '';
		$lastname = '';

		if (isset($_POST['register'])) {
			$username = htmlentities($_POST['username'], ENT_QUOTES, CONTREXX_CHARSET);
			$email = htmlentities($_POST['email'], ENT_QUOTES, CONTREXX_CHARSET);
			$zip = htmlentities($_POST['zip'], ENT_QUOTES, CONTREXX_CHARSET);
			$residence = htmlentities($_POST['residence'], ENT_QUOTES, CONTREXX_CHARSET);
            $firstname = htmlentities($_POST['firstname'], ENT_QUOTES, CONTREXX_CHARSET);
			$lastname = htmlentities($_POST['lastname'], ENT_QUOTES, CONTREXX_CHARSET);
		}

		$this->_objTpl->setVariable(array(
			'TXT_LOGIN_NAME'					=> $_ARRAYLANG['TXT_LOGIN_NAME'],
			'TXT_LOGIN_PASSWORD'				=> $_ARRAYLANG['TXT_LOGIN_PASSWORD'],
			'TXT_PASSWORD_MINIMAL_CHARACTERS'	=> $_ARRAYLANG['TXT_PASSWORD_MINIMAL_CHARACTERS'],
			'TXT_VERIFY_PASSWORD'				=> $_ARRAYLANG['TXT_VERIFY_PASSWORD'],
			'TXT_FIRST_NAME'					=> $_ARRAYLANG['TXT_FIRST_NAME'],
			'TXT_LAST_NAME'						=> $_ARRAYLANG['TXT_LAST_NAME'],
			'TXT_EMAIL'							=> $_ARRAYLANG['TXT_EMAIL'],
			'TXT_REGISTER'						=> $_ARRAYLANG['TXT_REGISTER'],
			'TXT_ALL_FIELDS_REQUIRED'			=> $_ARRAYLANG['TXT_ALL_FIELDS_REQUIRED'],
			'TXT_PASSWORD_NOT_USERNAME_TEXT'	=> $_ARRAYLANG['TXT_PASSWORD_NOT_USERNAME_TEXT'],
			'TXT_ZIP'							=> $_ARRAYLANG['TXT_ZIP'],
			'TXT_RESIDENCE'						=> $_ARRAYLANG['TXT_RESIDENCE'],
			'COMMUNITY_USERNAME'				=> $username,
			'COMMUNITY_EMAIL'					=> $email,
			'COMMUNITY_ZIP'						=> $zip,
			'COMMUNITY_RESIDENCE'				=> $residence,
			'COMMUNITY_STATUS_MESSAGE'			=> $this->_statusMessage
		));
	}

	/**
	* Show the user account profile page
	*
	* @access private
	* @global object $objDatabase
	*/
	function _showProfilePage()
	{
		global $objDatabase;

		$objResult = $objDatabase->SelectLimit("SELECT email, firstname, lastname, street, zip, phone, mobile, residence, profession, interests, webpage, company FROM ".DBPREFIX."access_users WHERE username='".contrexx_addslashes($_SESSION['auth']['username'])."'", 1);
		if ($objResult !== false) {
			$this->_objTpl->setVariable(array(
				'COMMUNITY_FIRSTNAME'	=> $objResult->fields['firstname'],
				'COMMUNITY_LASTNAME'	=> $objResult->fields['lastname'],
				'COMMUNITY_STREET'		=> $objResult->fields['street'],
				'COMMUNITY_ZIP'			=> $objResult->fields['zip'],
				'COMMUNITY_RESIDENCE'	=> $objResult->fields['residence'],
				'COMMUNITY_PROFESSION'	=> $objResult->fields['profession'],
				'COMMUNITY_INTERESTS'	=> $objResult->fields['interests'],
				'COMMUNITY_WEBPAGE'		=> $objResult->fields['webpage'],
				'COMMUNITY_EMAIL'		=> $objResult->fields['email'],
				'COMMUNITY_COMPANY'		=> $objResult->fields['company'],
				'COMMUNITY_PHONE'		=> $objResult->fields['phone'],
				'COMMUNITY_MOBILE'		=> $objResult->fields['mobile']
			));
		}
	}
}
?>
