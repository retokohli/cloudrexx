<?php

/**
 * Contrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Comvation AG 2007-2015
 * @version   Contrexx 4.0
 * 
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Contrexx" is a registered trademark of Comvation AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */
 
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

