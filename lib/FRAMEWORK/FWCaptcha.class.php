<?php
/**
 * @ignore
 */
include_once ASCMS_FRAMEWORK_PATH.'/Captcha/Captcha.interface.php';
class FWCaptcha {
    private $objCaptcha = null;

    private function __construct($config)
    {
        global $sessionObj;
        if (!isset($sessionObj)) $sessionObj = new cmsSession();

// TODO: move to basic configuration screen (/cadmin/index.php?cmd=settings)
        $captchaConfig = array(
            'reCAPTCHA' => array(
                'domains' => array(
                    'localhost' => array(
                        'public_key'    => '6LeiusgSAAAAACPI2stz_Qh2fVC1reRUxJuqzf7h',
                        'private_key'    => '6LeiusgSAAAAAABv3CW65svwgRMqFfTiC5NTOzOh',
                    ),
                ),
            ),
        );
        $config['coreCaptchaLib'] = '';
        $config['coreCaptchaLibConfig'] = json_encode($captchaConfig);

        switch ($config['coreCaptchaLib']) {
            case 'reCAPTCHA':
                /**
                 * @ignore
                 */
                include_once ASCMS_FRAMEWORK_PATH.'/Captcha/reCAPTCHA.class.php';
                $this->objCaptcha = new \Cx\Lib\Captcha\reCAPTCHA($config);
                break;

            case 'contrexx':
            default:
                /**
                 * @ignore
                 */
                include_once ASCMS_FRAMEWORK_PATH.'/Captcha/ContrexxCaptcha.class.php';
                $this->objCaptcha = new \Cx\Lib\Captcha\ContrexxCaptcha($config);
                break;
        }
    }

    public function __call($name, $args)
    {
        return call_user_func_array(array($this->objCaptcha, $name), $args);
    }

    public static function getInstance()
    {
        static $objCaptcha = null;

        if (!isset($objCaptcha)) {
            $objCaptcha = new FWCaptcha(Env::get('config'));
        }

        return $objCaptcha;
    }
}

