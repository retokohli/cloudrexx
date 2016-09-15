<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
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
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * ReCaptcha
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_captcha
 */

namespace Cx\Core_Modules\Captcha\Controller;


/**
 * ReCaptcha
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_captcha
 */
class ReCaptcha implements CaptchaInterface {
    private $site_key;
    private $secret_key;
    private $error = '';

    public function __construct($config)
    {
        $captchaConfig = json_decode($config['coreCaptchaLibConfig'], true);

        if (!isset($captchaConfig['ReCaptcha'])) {
            return;
        }

        $reCAPTCHAConfig = $captchaConfig['ReCaptcha'];
        if (!isset($reCAPTCHAConfig['domains'][$config['domainUrl']])) {
            return;
        }

        $reCAPTCHAKeys    = $reCAPTCHAConfig['domains'][$config['domainUrl']];
        $this->site_key   = $reCAPTCHAKeys['site_key'];
        $this->secret_key = $reCAPTCHAKeys['secret_key'];
    }

    public function getCode($tabIndex = null)
    {
        $tabIndexAttr = '';
        if (isset($tabIndex)) {
            $tabIndexAttr = "tabindex=\"$tabIndex\"";
        }

        $lang   = \FWLanguage::getLanguageCodeById(FRONTEND_LANG_ID);
        $code   = <<<HTML
<div class="g-recaptcha" data-sitekey="{$this->site_key}"></div>
<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=$lang"></script>
HTML;
        return $code;
    }

    public function check()
    {
        $reCaptcha = new \ReCaptcha\ReCaptcha($this->secret_key, new \ReCaptcha\RequestMethod\CurlPost());
        $resp = $reCaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

        if ($resp->isSuccess()) {
            return true;
        }

        // set error message
        foreach ($resp->getErrorCodes() as $errorCode) {
            $this->error .= $errorCode;
        }
        return false;
    }
}
