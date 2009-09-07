<?php
/**
 * Login
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version       1.0.0
 * @package     contrexx
 * @subpackage  core_module_login
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Login
 *
 * Class to login into the system
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version       1.0.0
 * @package     contrexx
 * @subpackage  core_module_login
 */
class Login
{
	var $_statusMessage;
	var $_objTpl;


    /**
    * constructor
    */
    function Login($pageContent)
    {
       $this->__construct($pageContent);
    }

    /**
    * constructor
    */
    function __construct($pageContent)
    {
    	$this->_objTpl = &new HTML_Template_Sigma('.');
        CSRF::add_placeholder($this->_objTpl);
		$this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
		$this->_objTpl->setTemplate($pageContent);
    }

    /**
    * Get content
    *
    * Get the login pages
    *
    * @access	public
    * @see _lostPassword(), _resetPassword(), _noaccess(), _login()
    * @return	mixed	Template content
    */
    function getContent()
    {
    	if (!isset($_GET['cmd'])) {
    		$_GET['cmd'] = "";
    	}

    	switch ($_GET['cmd']) {
    	case 'lostpw':
    		return $this->_lostPassword();
    		break;

    	case 'resetpw':
    		return $this->_resetPassword();
    		break;

    	case 'noaccess':
    		return $this->_noaccess();
    		break;

    	default:
    		return $this->_login();
    		break;
    	}
    }

    /**
    * Calls the method restorePassword of the class FWUser
    * and displays the lostpw page of the login module
    *
    * @global array
    * @see FWUser::restorePassword()
    * @return string HTML_Template_Sigma::get()
    */
    function _lostPassword()
    {
    	global $_CORELANG;

        // set language variables
        $this->_objTpl->setVariable(array(
            'TXT_LOST_PASSWORD_TEXT'    => $_CORELANG['TXT_LOST_PASSWORD_TEXT'],
            'TXT_EMAIL'					=> $_CORELANG['TXT_EMAIL'],
            'TXT_RESET_PASSWORD'        => $_CORELANG['TXT_RESET_PASSWORD']
        ));

        if (isset($_POST['email'])) {
        	$objFWUser = FWUser::getFWUserObject();
            $email = contrexx_stripslashes($_POST['email']);

            if (($objFWUser->restorePassword($email))) {
				$statusMessage = str_replace("%EMAIL%", $email, $_CORELANG['TXT_LOST_PASSWORD_MAIL_SENT']);
				if ($this->_objTpl->blockExists('login_lost_password')) {
					$this->_objTpl->hideBlock('login_lost_password');
				}
            } else {
            	$statusMessage = $objFWUser->getErrorMsg();
            }

            $this->_objTpl->setVariable(array(
                'LOGIN_STATUS_MESSAGE'        => $statusMessage
            ));
        }

        return $this->_objTpl->get();
    }

    /**
    * Calls the method resetPassword of the class FWUser
    * and displays the resetpw page of the login module
    *
    * @access private
    * @see FWUser::resetPassword()
    * @return string HTML_Template_Sigma::get()
    */
    function _resetPassword()
    {
    	global $_CORELANG;

    	$objFWUser = FWUser::getFWUserObject();
    	$username = isset($_POST['username']) ? contrexx_stripslashes($_POST['username']) : (isset($_GET['username']) ? contrexx_stripslashes($_GET['username']) : '');
        $restoreKey = isset($_POST['restore_key']) ? contrexx_stripslashes($_POST['restore_key']) : (isset($_GET['restoreKey']) ? contrexx_stripslashes($_GET['restoreKey']) : '');
        $password = isset($_POST['password']) ? trim(contrexx_stripslashes($_POST['password'])) : '';
        $confirmedPassword = isset($_POST['password2']) ? trim(contrexx_stripslashes($_POST['password2'])) : '';
        $statusMessage = '';

		if (isset($_POST['reset_password'])) {
			if ($objFWUser->resetPassword($username, $restoreKey, $password, $confirmedPassword, true)) {
				$statusMessage = $_CORELANG['TXT_PASSWORD_CHANGED_SUCCESSFULLY'];
				if ($this->_objTpl->blockExists('login_reset_password')) {
	                $this->_objTpl->hideBlock('login_reset_password');
	            }
			} else {
				$statusMessage = $objFWUser->getErrorMsg();

				$this->_objTpl->setVariable(array(
					'TXT_USERNAME'						=> $_CORELANG['TXT_USERNAME'],
					'TXT_PASSWORD'						=> $_CORELANG['TXT_PASSWORD'],
					'TXT_VERIFY_PASSWORD'				=> $_CORELANG['TXT_VERIFY_PASSWORD'],
					'TXT_PASSWORD_MINIMAL_CHARACTERS'	=> $_CORELANG['TXT_PASSWORD_MINIMAL_CHARACTERS'],
					'TXT_SET_PASSWORD_TEXT'				=> $_CORELANG['TXT_SET_PASSWORD_TEXT'],
					'TXT_SET_NEW_PASSWORD'				=> $_CORELANG['TXT_SET_NEW_PASSWORD'],
				));

				$this->_objTpl->parse('login_reset_password');
			}
		} elseif (!$objFWUser->resetPassword($username, $restoreKey, $password, $confirmedPassword)) {
			$statusMessage = $objFWUser->getErrorMsg();
			if ($this->_objTpl->blockExists('login_reset_password')) {
                $this->_objTpl->hideBlock('login_reset_password');
            }
		} else {
			$this->_objTpl->setVariable(array(
				'TXT_USERNAME'						=> $_CORELANG['TXT_USERNAME'],
				'TXT_PASSWORD'						=> $_CORELANG['TXT_PASSWORD'],
				'TXT_VERIFY_PASSWORD'				=> $_CORELANG['TXT_VERIFY_PASSWORD'],
				'TXT_PASSWORD_MINIMAL_CHARACTERS'	=> $_CORELANG['TXT_PASSWORD_MINIMAL_CHARACTERS'],
				'TXT_SET_PASSWORD_TEXT'				=> $_CORELANG['TXT_SET_PASSWORD_TEXT'],
				'TXT_SET_NEW_PASSWORD'				=> $_CORELANG['TXT_SET_NEW_PASSWORD'],
			));

			$this->_objTpl->parse('login_reset_password');
		}

		$this->_objTpl->setVariable(array(
			'LOGIN_STATUS_MESSAGE'	=> $statusMessage,
			'LOGIN_USERNAME'		=> htmlentities($username, ENT_QUOTES, CONTREXX_CHARSET),
			'LOGIN_RESTORE_KEY'		=> htmlentities($restoreKey, ENT_QUOTES, CONTREXX_CHARSET)
		));

		return $this->_objTpl->get();
    }

    /**
    * Displays the noaccess page of the login module
    *
    * @global array
    * @return string HTML_Template_Sigma::get()
    */
    function _noaccess()
    {
    	global $_CORELANG;

    	if (isset($_REQUEST['redirect'])) {
			$redirect = contrexx_strip_tags($_REQUEST['redirect']);
		} else {
			$redirect = '';
		}

    	$this->_objTpl->setVariable('TXT_NOT_ALLOWED_TO_ACCESS', $_CORELANG['TXT_NOT_ALLOWED_TO_ACCESS']);
    	$this->_objTpl->setVariable('LOGIN_REDIRECT', $redirect);
    	return $this->_objTpl->get();
    }

    /**
    * Checks if the user has been successfully authenticated
    *
    * If a user has been successfully authenticated then he will be
    * redirected to the requested page, otherwise the login page will be displayed
    *
    * @access private
    * @global array
    * @see cmsSession::cmsSessionStatusUpdate(), contrexx_strip_tags, HTML_Template_Sigma::get()
    * @return string HTML_Template_Sigma::get()
    */
    function _login()
    {
    	global $_CORELANG;

		$objFWUser = FWUser::getFWUserObject();

		if (isset($_REQUEST['redirect'])) {
			$redirect = contrexx_strip_tags($_REQUEST['redirect']);
		} else {
			$redirect = "";
		}

		if ((!isset($_REQUEST['relogin']) || $_REQUEST['relogin'] != 'true') && $objFWUser->objUser->login() || $objFWUser->checkAuth()) {
			CSRF::header('Location: '.(empty($redirect) ? CONTREXX_SCRIPT_PATH : base64_decode($redirect)));
			exit;
		} else {
			if (isset($_POST['login'])) {
				$this->_statusMessage = $_CORELANG['TXT_PASSWORD_OR_USERNAME_IS_INCORRECT'];
			}
		}

    	$this->_objTpl->setVariable(array(
	    	'TXT_USER_NAME'			=> $_CORELANG['TXT_USER_NAME'],
	  		'TXT_PASSWORD'			=> $_CORELANG['TXT_PASSWORD'],
		   	'TXT_LOGIN'				=> $_CORELANG['TXT_LOGIN'],
		   	'TXT_PASSWORD_LOST'		=> $_CORELANG['TXT_PASSWORD_LOST'],
		   	'LOGIN_REDIRECT'		=> $redirect,
		   	'LOGIN_STATUS_MESSAGE'	=> $this->_statusMessage
    	));

    	return $this->_objTpl->get();
	}
}
?>
