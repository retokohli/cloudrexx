<?php

/**
 * Captcha
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_captcha
 */

namespace Cx\Core_Modules\Captcha\Controller;

/**
 * Captcha
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_captcha
 */
class Captcha {
    private $objCaptcha = null;

    private function __construct($config)
    {
        global $sessionObj;
        if (!isset($sessionObj)) $sessionObj = \cmsSession::getInstance();

// TODO: move to basic configuration screen (/cadmin/index.php?cmd=settings)
        $captchaConfig = array(
            'ReCaptcha' => array(
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
            case 'ReCaptcha':
                $this->objCaptcha = new ReCaptcha($config);
                break;

            case 'contrexx':
            default:
                $this->objCaptcha = new ContrexxCaptcha($config);
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
            $objCaptcha = new self(\Env::get('config'));
        }

        return $objCaptcha;
    }
}

