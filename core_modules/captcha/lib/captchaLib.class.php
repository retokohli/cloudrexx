<?php
/**
 * Holds captcha related actions
 */
class CaptchaLib {
    protected function newImage() {
        $c = new Captcha();
        $c->printNewImage();
        exit;
    }
}