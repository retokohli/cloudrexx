<?php

/**
 * Captcha
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  lib_captcha
 */

namespace Cx\Lib\Captcha;

/**
 * Contrexx captcha interface
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  lib_captcha
 */
interface CaptchaInterface {
    public function getCode($tabIndex);
    public function check();
}
