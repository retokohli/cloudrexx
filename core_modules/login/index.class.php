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
    * Calls the method lostPassword of the class Auth
    * and displays the lostpw page of the login module
    *
    * @access private
    * @global object $objAuth
    * @see Auth::lostPassword()
    * @return string HTML_Template_Sigma::get()
    */
    function _lostPassword()
    {
    	global $objAuth;

    	$objAuth->lostPassword($this->_objTpl);
    	return $this->_objTpl->get();
    }

    /**
    * Calls the method resetPassword of the class Auth
    * and displays the resetpw page of the login module
    *
    * @access private
    * @global object $objAuth
    * @see Auth::resetPassword()
    * @return string HTML_Template_Sigma::get()
    */
    function _resetPassword()
    {
    	global $objAuth;

    	$objAuth->resetPassword($this->_objTpl);
    	return $this->_objTpl->get();
    }

    /**
    * Displays the noaccess page of the login module
    *
    * @access private
    * @global object $objAuth
    * @global string $loginStatus
    * @global array $_CORELANG
    * @see Auth::status()
    * @return string HTML_Template_Sigma::get()
    */
    function _noaccess()
    {
    	global $objAuth, $loginStatus, $_CORELANG;

    	if (isset($_REQUEST['redirect'])) {
			$redirect = contrexx_strip_tags($_REQUEST['redirect']);
		} else {
			$redirect = "";
		}

    	$this->_objTpl->setVariable('TXT_NOT_ALLOWED_TO_ACCESS', $_CORELANG['TXT_NOT_ALLOWED_TO_ACCESS']);
    	$this->_objTpl->setVariable('LOGIN_REDIRECT', $redirect);
    	$loginStatus = $objAuth->status();
    	return $this->_objTpl->get();
    }

    /**
    * Checks if the user has been successfully authenticated
    *
    * If a user has been successfully authenticated then he will be
    * redirected to the requested page, otherwise the login page will be displayed
    *
    * @access private
    * @global object $sessionObj
    * @global object $objAuth
    * @global object $objPerm
    * @global array $_CORELANG
    * @see cmsSession::cmsSessionStatusUpdate(), contrexx_strip_tags, HTML_Template_Sigma::get()
    * @return string HTML_Template_Sigma::get()
    */
    function _login()
    {
    	global $_CORELANG, $sessionObj, $objAuth, $objPerm;

		$sessionObj->cmsSessionStatusUpdate($status="frontend");

		if (isset($_REQUEST['redirect'])) {
			$redirect = contrexx_strip_tags($_REQUEST['redirect']);
		} else {
			$redirect = "";
		}

		if ($objAuth->checkAuth() && (!isset($_REQUEST['relogin']) || $_REQUEST['relogin'] != 'true')) {
			header('Location: '.(empty($redirect) ? ASCMS_PATH_OFFSET.'/'.CONTREXX_DIRECTORY_INDEX : base64_decode($redirect)));
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