<?php
namespace Cx\Lib\Captcha;
/**
 * a Contrexx captcha interface
 */
interface CaptchaInterface {
    public function getCode($tabIndex);
    public function check();
}
