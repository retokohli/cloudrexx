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
    
    /**
     * Updates the license
     * @param \Cx\Core\License\License $license License to update
     * @param array $_CONFIG Config options
     * @param boolean $forceTemplate (optional) Wheter to force template delivery or not, defaults to false
     * @return void 
     */
    public function update(&$license, $_CONFIG, $forceTemplate = false) {
        $v = preg_split('#\.#', $_CONFIG['coreCmsVersion']);
        $e = $_CONFIG['coreCmsEdition'];
        if (count($v)) {
            $version = $v[0] * 10000;
            if (count($version) > 1) {
                $version += $v[1] * 100;
                if (count($version) > 2) {
                    $version += $v[2];
                }
            }
        }
        // for debugging only:
        $version = 30000;
        $srvUri = 'updatesrv1.contrexx.com';
        $srvPath = '/';
        $link = @fsockopen($srvUri,80);
        if (!isset($link) || !$link) {
            return;
        }
        
        $data = array(
            'installationId' => $license->getInstallationId(),
            'licenseKey' => $license->getLicenseKey(),
            'edition' => $license->getEditionName(),
            'version' => $_CONFIG['coreCmsVersion'],
            'domainName' => $_CONFIG['domainUrl'],
            'sendTemplate' => $forceTemplate,
        );
        $jd = new \Cx\Core\Json\JsonData();
        $a = $_SERVER['REMOTE_ADDR'];
        
        $request = new \HTTP_Request2('http://' . $srvUri . $srvPath . '?v=' . $version, \HTTP_Request2::METHOD_POST);
        $request->setHeader('X-Edition', $e);
        $request->setHeader('X-Remote-Addr', $a);
        $request->addPostParameter('data', $jd->json($data));
        try {
            $objResponse = $request->send();
            if ($objResponse->getStatus() !== 200) {
                // error
                echo 'ERROR';
            } else {
                $response = json_decode($objResponse->getBody())->license;
                //$response = $objResponse->getBody();
            }
        } catch (HTTP_Request2_Exception $objException) {
            throw $objException;
        }
        
        $installationId = $license->getInstallationId();
        $licenseKey = $license->getLicenseKey();
        if ($response->installationId != null) {
            $installationId = $response->installationId;
        }
        if ($response->key != null) {
            $licenseKey = $response->key;
        }
        $license = new \Cx\Core\License\License(
            $response->state,
            $response->edition,
            $response->legalComponents,
            $response->validTo,
            $installationId,
            $licenseKey
        );
        return;
    }
}
