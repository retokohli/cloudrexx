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
    private $requestInterval = 1;
    private $lastUpdate;
    
    public function __construct(&$_CONFIG) {
        if (self::$instance) {
            throw new \BadMethodCallException('Cannot construct a second instance, use ::getInstance()');
        }
        $this->requestInterval = $_CONFIG['licenseUpdateInterval'];
        $this->lastUpdate = $_CONFIG['licenseSuccessfulUpdate'];
        self::$instance = $this;
    }
    
    /**
     *
     * @return \Cx\Core\License\LicenseCommunicator 
     */
    public function getInstance(&$_CONFIG) {
        if (!self::$instance) {
            new self($_CONFIG);
        }
        return self::$instance;
    }
    
    /**
     * Tells wheter its time to update or not
     * @return boolean True if license is outdated, false otherwise
     */
    public function isTimeToUpdate() {
        $offset = $this->requestInterval *60*60;
        // if offset date lies in future, we do not update yet
        return ($this->lastUpdate + $offset <= time());
    }
    
    /**
     * Updates the license
     * @param \Cx\Core\License\License $license License to update
     * @param array $_CONFIG Config options
     * @param boolean $forceTemplate (optional) Wheter to force template delivery or not, defaults to false
     * @return void 
     */
    public function update(&$license, $_CONFIG, $forceUpdate = false, $forceTemplate = false) {
        if (!$forceUpdate && !$this->isTimeToUpdate($_CONFIG)) {
            return;
        }
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
        $a = $_SERVER['REMOTE_ADDR'];
        
        $request = new \HTTP_Request2('http://' . $srvUri . $srvPath . '?v=' . $version, \HTTP_Request2::METHOD_POST);
        $request->setHeader('X-Edition', $e);
        $request->setHeader('X-Remote-Addr', $a);
        $request->addPostParameter('data', \Cx\Core\Json\JsonData::json($data));
        try {
            $objResponse = $request->send();
            if ($objResponse->getStatus() !== 200) {
                $license->setState(License::LICENSE_ERROR);
                $license->check();
                return;
            } else {
                //echo $objResponse->getBody();
                $response = json_decode($objResponse->getBody());
            }
        } catch (HTTP_Request2_Exception $objException) {
            $license->setState(License::LICENSE_ERROR);
            $license->check();
            return;
        }
        
        // create new license
        $installationId = $license->getInstallationId();
        $licenseKey = $license->getLicenseKey();
        if ($response->license->installationId != null) {
            $installationId = $response->license->installationId;
        }
        if ($response->license->key != null) {
            $licenseKey = $response->license->key;
        }
        if (!empty($response->common->template)) {
            if (\FWUser::getFWUserObject()->objUser->getAdminStatus()) {
                try {
                    $file = new \Cx\Lib\FileSystem\File(ASCMS_TEMP_PATH.'/licenseManager.html');
                    $file->write($response->common->template);
                } catch (\Cx\Lib\FileSystem\FileSystemException $e) {}
            }
        }
        $this->requestInterval = $response->license->settings->requestInterval;
        if (!is_int($this->requestInterval) || $this->requestInterval < 0 || $this->requestInterval > (365*24)) {
            $this->requestInterval = 1;
        }
        $message = new \Cx\Core\License\Message(
            contrexx_raw2xhtml($response->license->message->text),
            contrexx_raw2xhtml($response->license->message->type),
            contrexx_raw2xhtml($response->license->message->link),
            contrexx_raw2xhtml($response->license->message->linkTarget)
        );
        $customer = new \Cx\Core\License\Customer(
            $response->license->customer->companyName,
            $response->license->customer->title,
            $response->license->customer->firstname,
            $response->license->customer->lastname,
            $response->license->customer->address,
            $response->license->customer->zip,
            $response->license->customer->city,
            $response->license->customer->country,
            $response->license->customer->phone,
            $response->license->customer->url,
            $response->license->customer->mail
        );
        $version = new \Cx\Core\License\Version(
            $response->versions->currentStable->number,
            $response->versions->currentStable->name,
            $response->versions->currentStable->codeName,
            $response->versions->currentStable->state,
            $response->versions->currentStable->releaseDate
        );
        $license = new \Cx\Core\License\License(
            $response->license->state,
            $response->license->edition,
            $response->license->legalComponents,
            $response->license->validTo,
            $installationId,
            $licenseKey,
            $message,
            $version,
            $customer,
            $response->license->settings->grayZoneTime,
            $response->license->settings->frontendLockTime,
            $this->requestInterval,
            0,
            time()
        );
        return;
    }
    
    public function addJsUpdateCode() {
        $lc = LicenseCommunicator::getInstance($this->config);
        if ($lc->isTimeToUpdate($this->config)) {
            \JS::activate('jquery');
            \JS::registerCode('
                jQuery(document).ready(function() {
                    var messageBar = jQuery("#message_message");
                    var oldMsg = messageBar.children("a").html();
                    var oldClass = messageBar.parent().attr("class");
                    var oldLink = messageBar.children("a").attr("href");
                    var oldTarget = messageBar.children("a").attr("target");
                    messageBar.children("a").attr("href", "#");
                    messageBar.children("a").attr("target", "_self");
                    messageBar.children("a").html("Lizenz wird aktualisiert...");
                    messageBar.parent().attr("class", "message okbox");
                    var revertMessage = function() {
                        messageBar.children("a").html(oldMsg);
                        messageBar.parent().attr("class", oldClass);
                        messageBar.children("a").attr("href", oldLink);
                        messageBar.children("a").attr("target", oldTarget);
                    }
                    jQuery.get(
                        "../core/License/versioncheck.php"
                    ).success(function(data) {
                        data = jQuery.parseJSON(data);
                        messageBar.children("a").html(data.text);
                        messageBar.parent().attr("class", "message " + data.class);
                        messageBar.children("a").attr("href", data.link);
                        messageBar.children("a").attr("target", data.target);
                    }).error(function(data) {
                        revertMessage();
                    });
                });
            ');
        }
    }
}
