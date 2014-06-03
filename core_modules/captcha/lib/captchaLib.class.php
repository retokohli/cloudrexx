<?php

/**
 * captchaLib
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_captcha
 */

/**
 * @ignore
 */
include_once ASCMS_LIBRARY_PATH.'/spamprotection/captcha.class.php';

/**
 * captchaLib
 * Holds captcha related actions
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_captcha
 */
class CaptchaLib {
    protected function newImage() {
        $c = new Captcha();
        $c->printNewImage();
        exit;
    }
}
