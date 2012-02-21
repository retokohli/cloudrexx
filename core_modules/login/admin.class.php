<?php

/**
 * Login
 * @copyright   CONTREXX WCMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_login
 */
class LoginManager {

    /**
     * Template object
     *
     * @access  public
     * @var     object
     */
    public $objTemplate;

    /**
     * Constructor
     *
     * @access  publice
     */
    public function __construct()
    {
    	$this->objTemplate = new HTML_Template_Sigma(ASCMS_CORE_MODULE_PATH.'/login/template');
        CSRF::add_placeholder($this->objTemplate);
		$this->objTemplate->setErrorHandling(PEAR_ERROR_DIE);
    }

    /**
     * Get the requested Page.
     *
     * @access  public
     */
    public function getPage()
    {
        $_GET['act'] = empty($_GET['act']) ? '' : $_GET['act'];

    	switch ($_GET['act']) {
            case 'lostpw':
                $this->showPasswordLost();
                break;
            case 'resetpw':
                $this->showPasswordReset();
                break;
            case 'captcha':
                $this->getCaptcha();
                break;
            default:
                $this->showLogin();
        }

        $this->objTemplate->show();
        exit();
    }

    /**
     * Show the password lost mask.
     *
     * @access  private
     * @global  array    $_CORELANG
     * @global  FWUser   $objFWUser
     */
    private function showPasswordLost()
    {
        global $_CORELANG, $objFWUser;

        $this->objTemplate->loadTemplateFile('../../../cadmin/template/ascms/index.html');
        $this->objTemplate->addBlockfile('CONTENT_FILE', 'CONTENT_BLOCK', 'login_lost_password.html');
        $this->objTemplate->setVariable(array(
            'TITLE' => $_CORELANG['TXT_RESET_PASSWORD'],
            'TXT_LOST_PASSWORD_TEXT' => $_CORELANG['TXT_LOST_PASSWORD_TEXT'],
            'TXT_EMAIL' => $_CORELANG['TXT_EMAIL'],
            'TXT_RESET_PASSWORD' => $_CORELANG['TXT_RESET_PASSWORD'],
            'TXT_BACK_TO_LOGIN' => $_CORELANG['TXT_BACK_TO_LOGIN'],
        ));
        if (isset($_POST['email'])) {
            $email = contrexx_stripslashes($_POST['email']);
            if (($objFWUser->restorePassword($email))) {
                $statusMessage = str_replace("%EMAIL%", $email, $_CORELANG['TXT_LOST_PASSWORD_MAIL_SENT']);
                if ($this->objTemplate->blockExists('login_lost_password')) {
                    $this->objTemplate->hideBlock('login_lost_password');
                }
            } else {
                $statusMessage = $objFWUser->getErrorMsg();
            }
            $this->objTemplate->setVariable('LOGIN_STATUS_MESSAGE', $statusMessage);
        }
    }

    /**
     * Show the password reset mask.
     *
     * @access  private
     * @global  array    $_CORELANG
     * @global  FWUser   $objFWUser
     */
    private function showPasswordReset()
    {
        global $_CORELANG, $objFWUser;

        $this->objTemplate->loadTemplateFile('../../../cadmin/template/ascms/index.html');
        $this->objTemplate->addBlockfile('CONTENT_FILE', 'CONTENT_BLOCK', 'login_reset_password.html');
        $this->objTemplate->setVariable('TITLE', $_CORELANG['TXT_SET_NEW_PASSWORD']);
        // TODO: Why oh why isn't function resetPassword() located in the AccessLibrary?
        $username = isset($_POST['username']) ? contrexx_stripslashes($_POST['username']) : (isset($_GET['username']) ? contrexx_stripslashes($_GET['username']) : '');
        $restoreKey = isset($_POST['restore_key']) ? contrexx_stripslashes($_POST['restore_key']) : (isset($_GET['restoreKey']) ? contrexx_stripslashes($_GET['restoreKey']) : '');
        $password = isset($_POST['password']) ? trim(contrexx_stripslashes($_POST['password'])) : '';
        $confirmedPassword = isset($_POST['password2']) ? trim(contrexx_stripslashes($_POST['password2'])) : '';
        $statusMessage = '';
        if (isset($_POST['reset_password'])) {
            if ($objFWUser->resetPassword($username, $restoreKey, $password, $confirmedPassword, true)) {
                $statusMessage = $_CORELANG['TXT_PASSWORD_CHANGED_SUCCESSFULLY'];
                if ($this->objTemplate->blockExists('login_reset_password')) {
                    $this->objTemplate->hideBlock('login_reset_password');
                }
            } else {
                $statusMessage = $objFWUser->getErrorMsg();
                $this->objTemplate->setVariable(array(
                    'TXT_USERNAME' => $_CORELANG['TXT_USERNAME'],
                    'TXT_PASSWORD' => $_CORELANG['TXT_PASSWORD'],
                    'TXT_VERIFY_PASSWORD' => $_CORELANG['TXT_VERIFY_PASSWORD'],
                    'TXT_PASSWORD_MINIMAL_CHARACTERS' => $_CORELANG['TXT_PASSWORD_MINIMAL_CHARACTERS'],
                    'TXT_SET_PASSWORD_TEXT' => $_CORELANG['TXT_SET_PASSWORD_TEXT'],
                    'TXT_SET_NEW_PASSWORD' => $_CORELANG['TXT_SET_NEW_PASSWORD'],
                    'TXT_BACK_TO_LOGIN' => $_CORELANG['TXT_BACK_TO_LOGIN'],
                ));
                $this->objTemplate->parse('login_reset_password');
            }
        } elseif (!$objFWUser->resetPassword($username, $restoreKey, $password, $confirmedPassword)) {
            $statusMessage = $objFWUser->getErrorMsg();
            if ($this->objTemplate->blockExists('login_reset_password')) {
                $this->objTemplate->hideBlock('login_reset_password');
            }
        } else {
            $this->objTemplate->setVariable(array(
                'TXT_USERNAME' => $_CORELANG['TXT_USERNAME'],
                'TXT_PASSWORD' => $_CORELANG['TXT_PASSWORD'],
                'TXT_VERIFY_PASSWORD' => $_CORELANG['TXT_VERIFY_PASSWORD'],
                'TXT_PASSWORD_MINIMAL_CHARACTERS' => $_CORELANG['TXT_PASSWORD_MINIMAL_CHARACTERS'],
                'TXT_SET_PASSWORD_TEXT' => $_CORELANG['TXT_SET_PASSWORD_TEXT'],
                'TXT_SET_NEW_PASSWORD' => $_CORELANG['TXT_SET_NEW_PASSWORD'],
                'TXT_BACK_TO_LOGIN' => $_CORELANG['TXT_BACK_TO_LOGIN'],
            ));
            $this->objTemplate->parse('login_reset_password');
        }
        $this->objTemplate->setVariable(array(
            'LOGIN_STATUS_MESSAGE' => $statusMessage,
            'LOGIN_USERNAME' => htmlentities($username, ENT_QUOTES, CONTREXX_CHARSET),
            'LOGIN_RESTORE_KEY' => htmlentities($restoreKey, ENT_QUOTES, CONTREXX_CHARSET),
        ));
    }

    /**
     * Generate a captcha image.
     *
     * @access  private
     */
    private function getCaptcha()
    {
        FWCaptcha::getInstance()->getPage();
    }

    /**
     * Show the login mask.
     *
     * @access  private
     * @global  array    $_CORELANG
     * @global  FWUser   $objFWUser
     */
    private function showLogin()
    {
        global $_CORELANG, $objFWUser;

        $this->objTemplate->loadTemplateFile('../../../cadmin/template/ascms/index.html', true, true);
        $this->objTemplate->addBlockfile('CONTENT_FILE', 'CONTENT_BLOCK', 'login.html');
        $this->objTemplate->setVariable(array(
            'REDIRECT_URL' => (!empty($_POST['redirect'])) ? $_POST['redirect'] : basename(getenv('REQUEST_URI')),
            'TXT_LOGIN' => $_CORELANG['TXT_LOGIN'],
            'TITLE' => $_CORELANG['TXT_LOGIN'],
            'TXT_ENTER_A_USERNAME' => $_CORELANG['TXT_ENTER_A_USERNAME'],
            'TXT_ENTER_A_PASSWORD' => $_CORELANG['TXT_ENTER_A_PASSWORD'],
            'TXT_USER_NAME' => $_CORELANG['TXT_USER_NAME'],
            'TXT_PASSWORD' => $_CORELANG['TXT_PASSWORD'],
            'TXT_PASSWORD_LOST' => $_CORELANG['TXT_PASSWORD_LOST'],
            'UID' => isset($_COOKIE['username']) ? $_COOKIE['username'] : '',
            'LOGIN_ERROR_MESSAGE' => $objFWUser->getErrorMsg(),
            'JAVASCRIPT' => JS::getCode(),
        ));
    }

}

?>