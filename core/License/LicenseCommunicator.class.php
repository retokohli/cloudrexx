<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Cx\Core\License;

/**
 * Description of LicenseCommunicator
 *
 * @author ritt0r
 */
class LicenseCommunicator {
    private static $instance = null;
    
    public function __construct() {
        if (self::$instance) {
            throw new \BadMethodCallException('Cannot construct a second instance, use ::getInstance()');
        }
        self::$instance = $this;
    }
    
    /**
     *
     * @return \Cx\Core\License\LicenseCommunicator 
     */
    public function getInstance() {
        if (!self::$instance) {
            new self();
        }
        return self::$instance;
    }
    
    public function update($license, $forceTemplate = false) {
        /**
         * request: {
         *      installationId, // optional?
         *      licenseKey,
         *      domainName,
         *      sendTemplate,   // optional
         * }
         */
        $v = preg_split('#\.#', $_CONFIG['coreCmsVersion']);
        $e = $_CONFIG['coreCmsEdition'];

        $version = $v[0]  * 10000 + $v[1]  * 100 + $v[2];
        //$link = @fsockopen('www.contrexx.com',80);
        if (!$link) {
            exit;
        }
        
        $r = $_SERVER['HTTP_REFERER'];
        $a = $_SERVER['REMOTE_ADDR'];
        fwrite($link,
            "GET /updatecenter/check.php?v=$version HTTP/1.1\n".
            "Host: www.contrexx.com\n".
            "Referer: $r\n".
            "X-Edition: $e\n".
            "X-Remote-Addr: $a\n".
            "Connection: close\n\n"
        );
    }
}
