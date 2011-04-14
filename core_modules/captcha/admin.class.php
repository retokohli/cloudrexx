<?php
/**
 * Backend Captcha Tasks 
 */
require_once ASCMS_CORE_MODULE_PATH.'/captcha/lib/captchaLib.class.php';

class CaptchaActions extends CaptchaLib
{
    public function getPage()
    {
        $act = '';
        if(isset($_REQUEST['cmd'])) {
            $act = $_REQUEST['cmd'];
        }
        if(isset($_REQUEST['act'])) {
            $act = $_REQUEST['act'];
        }

        switch($act) {
            case 'new': //create a new image
                $this->newImage();
                break;
        }
    }
}