<?php
/**
 * Holds captcha related actions
 */
include_once ASCMS_LIBRARY_PATH.'/spamprotection/captcha.class.php';
class CaptchaLib {
    protected function newImage() {
        $c = new Captcha();
        $c->printNewImage();
        exit;
    }
}