<?php

function _loginUpdate()
{
    global $objUpdate, $_CONFIG;


    // only update if installed version is at least a version 2.0.0
    // older versions < 2.0 have a complete other structure of the content page and must therefore completely be reinstalled
    if (!$objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '2.0.0')) {
        try {
            // migrate content page to version 3.0.1
            $search = array(
            '/(.*)/ms',
            );
            $callback = function($matches) {
                $content = $matches[1];
                if (empty($content)) {
                    return null;
                }

                // add missing captcha template block
                if (!preg_match('/<!--\s+BEGIN\s+captcha\s+-->.*<!--\s+END\s+captcha\s+-->/ms', $content)) {
                    $content = preg_replace('/(<input[^>]+name\s*=\s*[\'"]PASSWORD[\'"][^>]*>.*?<\/p>)(\s+)/ms', '$1$2<!-- BEGIN captcha -->$2<p><label for="coreCaptchaCode">{TXT_CORE_CAPTCHA}</label>{CAPTCHA_CODE}</p>$2<!-- END captcha -->', $content);
                }

                return $content;
            };

            \Cx\Lib\UpdateUtil::migrateContentPageUsingRegexCallback(array('module' => 'login', 'cmd' => ''), $search, $callback, array('content'), '3.0.1');
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }

    return true;
}

