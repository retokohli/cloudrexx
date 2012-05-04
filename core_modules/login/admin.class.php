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
        $this->objTemplate = new HTML_Template_Sigma(ASCMS_DOCUMENT_ROOT);
        CSRF::add_placeholder($this->objTemplate);
        $this->objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $this->objTemplate->loadTemplateFile('/cadmin/template/ascms/index.html');
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
                break;
        }

        $this->objTemplate->setVariable('CONTREXX_CHARSET', CONTREXX_CHARSET);
        $this->objTemplate->show();
        exit();
    }

    /**
     * Show the password lost mask.
     *
     * @access  private
     * @global  array    $_ARRAYLANG
     * @global  FWUser   $objFWUser
     */
    private function showPasswordLost()
    {
        global $_ARRAYLANG, $objFWUser;

        $this->objTemplate->addBlockfile('CONTENT_FILE', 'CONTENT_BLOCK', '/core_modules/login/template/login_lost_password.html');
        $this->objTemplate->setVariable(array(
            'TITLE'                     => $_ARRAYLANG['TXT_LOGIN_RESET_PASSWORD'],
            'TXT_LOGIN_LOST_PASSWORD'   => $_ARRAYLANG['TXT_LOGIN_LOST_PASSWORD'],
            'TXT_LOGIN_EMAIL'           => $_ARRAYLANG['TXT_LOGIN_EMAIL'],
            'TXT_LOGIN_RESET_PASSWORD'  => $_ARRAYLANG['TXT_LOGIN_RESET_PASSWORD'],
            'TXT_LOGIN_BACK_TO_LOGIN'   => $_ARRAYLANG['TXT_LOGIN_BACK_TO_LOGIN'],
        ));
        if ($this->objTemplate->blockExists('back_to_login')) {
            $this->objTemplate->hideBlock('back_to_login');
        }
        if (isset($_POST['email'])) {
            $email = contrexx_stripslashes($_POST['email']);
            if (($objFWUser->restorePassword($email))) {
                $statusMessage = str_replace("%EMAIL%", $email, $_ARRAYLANG['TXT_LOGIN_LOST_PASSWORD_MAIL_SENT']);
                if ($this->objTemplate->blockExists('login_lost_password')) {
                    $this->objTemplate->hideBlock('login_lost_password');
                }
                if ($this->objTemplate->blockExists('back_to_login')) {
                    $this->objTemplate->touchBlock('back_to_login');
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
     * @global  array    $_ARRAYLANG
     * @global  FWUser   $objFWUser
     */
    private function showPasswordReset()
    {
        global $_ARRAYLANG, $objFWUser;

        $this->objTemplate->addBlockfile('CONTENT_FILE', 'CONTENT_BLOCK', '/core_modules/login/template/login_reset_password.html');
        $this->objTemplate->setVariable(array(
            'TITLE'                     => $_ARRAYLANG['TXT_LOGIN_SET_NEW_PASSWORD'],
            'TXT_LOGIN_BACK_TO_LOGIN'   => $_ARRAYLANG['TXT_LOGIN_BACK_TO_LOGIN'],
        ));
        if ($this->objTemplate->blockExists('back_to_login')) {
            $this->objTemplate->hideBlock('back_to_login');
        }
        // TODO: Why oh why isn't function resetPassword() located in the AccessLibrary?
        $username = isset($_POST['username']) ? contrexx_stripslashes($_POST['username']) : (isset($_GET['username']) ? contrexx_stripslashes($_GET['username']) : '');
        $restoreKey = isset($_POST['restore_key']) ? contrexx_stripslashes($_POST['restore_key']) : (isset($_GET['restoreKey']) ? contrexx_stripslashes($_GET['restoreKey']) : '');
        $password = isset($_POST['password']) ? trim(contrexx_stripslashes($_POST['password'])) : '';
        $confirmedPassword = isset($_POST['password2']) ? trim(contrexx_stripslashes($_POST['password2'])) : '';
        $statusMessage = '';
        if (isset($_POST['reset_password'])) {
            if ($objFWUser->resetPassword($username, $restoreKey, $password, $confirmedPassword, true)) {
                $statusMessage = $_ARRAYLANG['TXT_LOGIN_PASSWORD_CHANGED_SUCCESSFULLY'];
                if ($this->objTemplate->blockExists('login_reset_password')) {
                    $this->objTemplate->hideBlock('login_reset_password');
                }
                if ($this->objTemplate->blockExists('back_to_login')) {
                    $this->objTemplate->touchBlock('back_to_login');
                }
            } else {
                $statusMessage = $objFWUser->getErrorMsg();
                $this->objTemplate->setVariable(array(
                    'TXT_LOGIN_USERNAME'                    => $_ARRAYLANG['TXT_LOGIN_USERNAME'],
                    'TXT_LOGIN_PASSWORD'                    => $_ARRAYLANG['TXT_LOGIN_PASSWORD'],
                    'TXT_LOGIN_VERIFY_PASSWORD'             => $_ARRAYLANG['TXT_LOGIN_VERIFY_PASSWORD'],
                    'TXT_LOGIN_PASSWORD_MINIMAL_CHARACTERS' => $_ARRAYLANG['TXT_LOGIN_PASSWORD_MINIMAL_CHARACTERS'],
                    'TXT_LOGIN_SET_PASSWORD_TEXT'           => $_ARRAYLANG['TXT_LOGIN_SET_PASSWORD_TEXT'],
                    'TXT_LOGIN_SET_NEW_PASSWORD'            => $_ARRAYLANG['TXT_LOGIN_SET_NEW_PASSWORD'],
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
                'TXT_LOGIN_USERNAME'                    => $_ARRAYLANG['TXT_LOGIN_USERNAME'],
                'TXT_LOGIN_PASSWORD'                    => $_ARRAYLANG['TXT_LOGIN_PASSWORD'],
                'TXT_LOGIN_VERIFY_PASSWORD'             => $_ARRAYLANG['TXT_LOGIN_VERIFY_PASSWORD'],
                'TXT_LOGIN_PASSWORD_MINIMAL_CHARACTERS' => $_ARRAYLANG['TXT_LOGIN_PASSWORD_MINIMAL_CHARACTERS'],
                'TXT_LOGIN_SET_PASSWORD_TEXT'           => $_ARRAYLANG['TXT_LOGIN_SET_PASSWORD_TEXT'],
                'TXT_LOGIN_SET_NEW_PASSWORD'            => $_ARRAYLANG['TXT_LOGIN_SET_NEW_PASSWORD'],
            ));
            $this->objTemplate->parse('login_reset_password');
        }
        $this->objTemplate->setVariable(array(
            'LOGIN_STATUS_MESSAGE'  => $statusMessage,
            'LOGIN_USERNAME'        => htmlentities($username, ENT_QUOTES, CONTREXX_CHARSET),
            'LOGIN_RESTORE_KEY'     => htmlentities($restoreKey, ENT_QUOTES, CONTREXX_CHARSET),
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
     * @global  array    $_ARRAYLANG
     * @global  FWUser   $objFWUser
     */
    private function showLogin()
    {
        global $_ARRAYLANG, $objFWUser;

        $this->objTemplate->addBlockfile('CONTENT_FILE', 'CONTENT_BLOCK', '/core_modules/login/template/login.html');
        $this->objTemplate->setVariable(array(
            'TITLE'                         => $_ARRAYLANG['TXT_LOGIN_LOGIN'],
            'TXT_LOGIN_LOGIN'               => $_ARRAYLANG['TXT_LOGIN_LOGIN'],
            'TXT_LOGIN_ENTER_A_USERNAME'    => $_ARRAYLANG['TXT_LOGIN_ENTER_A_USERNAME'],
            'TXT_LOGIN_ENTER_A_PASSWORD'    => $_ARRAYLANG['TXT_LOGIN_ENTER_A_PASSWORD'],
            'TXT_LOGIN_ENTER_CAPTCHA'       => $_ARRAYLANG['TXT_LOGIN_ENTER_CAPTCHA'],
            'TXT_LOGIN_USERNAME'            => $_ARRAYLANG['TXT_LOGIN_USERNAME'],
            'TXT_LOGIN_PASSWORD'            => $_ARRAYLANG['TXT_LOGIN_PASSWORD'],
            'TXT_LOGIN_PASSWORD_LOST'       => $_ARRAYLANG['TXT_LOGIN_PASSWORD_LOST'],
            'TXT_LOGIN_REMEMBER_ME'         => $_ARRAYLANG['TXT_LOGIN_REMEMBER_ME'],
            'REMEMBER_ME_CHECKED'           => isset($_SESSION['auth']['loginRememberMe']) ? 'checked="checked"' : '',
            'REDIRECT_URL'                  => !empty($_POST['redirect']) ? $_POST['redirect'] : basename(getenv('REQUEST_URI')),
            'JAVASCRIPT'                    => JS::getCode(),
        ));

        if (FWCaptcha::getInstance()->check()) {
            $this->objTemplate->setVariable('LOGIN_ERROR_MESSAGE', $objFWUser->getErrorMsg());
        }
        if (isset($_SESSION['auth']['loginLastAuthFailed'])) {
            $this->objTemplate->setVariable(array(
                'TXT_LOGIN_SECURITY_CODE'   => $_ARRAYLANG['TXT_LOGIN_SECURITY_CODE'],
                'CAPTCHA_CODE'              => FWCaptcha::getInstance()->getCode(4),
            ));
            $this->objTemplate->parse('captcha');
        } else {
            $this->objTemplate->hideBlock('captcha');
        }
    }

}
