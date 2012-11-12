<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Cx\Core_Modules\License;

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
     * @return \Cx\Core_Modules\License\LicenseCommunicator 
     */
    public static function getInstance(&$_CONFIG) {
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
     * @param \Cx\Core_Modules\License\License $license License to update
     * @param array $_CONFIG Config options
     * @param boolean $forceTemplate (optional) Wheter to force template delivery or not, defaults to false
     * @return void 
     */
    public function update(&$license, $_CONFIG, $forceUpdate = false, $forceTemplate = false, $_CORELANG = array()) {
        if (!$forceUpdate && !$this->isTimeToUpdate($_CONFIG)) {
            return;
        }
        $v = preg_split('#\.#', $_CONFIG['coreCmsVersion']);
        $e = $_CONFIG['coreCmsEdition'];
        
        $version = current($v);
        unset($v[key($v)]);
        foreach ($v as $part) {
            $version *= 100;
            $version += $part;
        }
        
        $srvUri = 'updatesrv1.contrexx.com';
        $srvPath = '/';
        // for debugging only:
        /*$version = 30000;
        $link = @fsockopen($srvUri,80);
        if (!isset($link) || !$link) {
            $license->setState(License::LICENSE_ERROR);
            $license->setGrayzoneMessages(array(\FWLanguage::getLanguageCodeById(LANG_ID) => new Message(\FWLanguage::getLanguageCodeById(LANG_ID), $_CORELANG['TXT_LICENSE_COMMUNICATION_ERROR'])));
            $license->check();
            return;
        }*/
        
        $data = array(
            'installationId' => $license->getInstallationId(),
            'licenseKey' => $license->getLicenseKey(),
            'edition' => $license->getEditionName(),
            'version' => $_CONFIG['coreCmsVersion'],
            'versionstate' => $_CONFIG['coreCmsStatus'],
            'domainName' => $_CONFIG['domainUrl'],
            'sendTemplate' => $forceTemplate,
        );
        $a = $_SERVER['REMOTE_ADDR'];
        
        $request = new \HTTP_Request2('http://' . $srvUri . $srvPath . '?v=' . $version, \HTTP_Request2::METHOD_POST);
        $request->setHeader('X-Edition', $e);
        $request->setHeader('X-Remote-Addr', $a);
        $jd = new \Cx\Core\Json\JsonData();
        $request->addPostParameter('data', $jd->json($data));
        try {
            $objResponse = $request->send();
            if ($objResponse->getStatus() !== 200) {
                $license->setState(License::LICENSE_ERROR);
                $license->setGrayzoneMessages(array(\FWLanguage::getLanguageCodeById(LANG_ID) => new Message(\FWLanguage::getLanguageCodeById(LANG_ID), $_CORELANG['TXT_LICENSE_COMMUNICATION_ERROR'])));
                $license->check();
                return;
            } else {
                //echo $objResponse->getBody();
                $response = json_decode($objResponse->getBody());
            }
        } catch (HTTP_Request2_Exception $objException) {
            $license->setState(License::LICENSE_ERROR);
            $license->setGrayzoneMessages(array(\FWLanguage::getLanguageCodeById(LANG_ID) => new Message(\FWLanguage::getLanguageCodeById(LANG_ID), $_CORELANG['TXT_LICENSE_COMMUNICATION_ERROR'])));
            $license->check();
            return;
        }
        
        $upgradeUrl = $response->license->upgradeUrl;
        if ($response->license->partner->upgradeUrl) {
            $upgradeUrl = $response->license->partner->upgradeUrl;
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
        $dashboardMessages = array();
        foreach ($response->license->messages->dashboard as $lang=>$message) {
            $dashboardMessages[$lang] = new \Cx\Core_Modules\License\Message(
                $lang,
                $message->text,
                $message->type,
                $message->link,
                $message->linkTarget,
                $message->showInDashboard
            );
        }
        $licenseManagementMessages = array();
        foreach ($response->license->messages->licenseManagement as $lang=>$message) {
            $licenseManagementMessages[$lang] = new \Cx\Core_Modules\License\Message(
                $lang,
                $message->text,
                $message->type,
                $message->link,
                $message->linkTarget,
                $message->showInDashboard
            );
        }
        $gzMessages = array();
        foreach ($response->license->messages->grayZone as $lang=>$message) {
            $gzMessages[$lang] = new \Cx\Core_Modules\License\Message(
                $lang,
                $message->text,
                $message->type,
                $message->link,
                $message->linkTarget,
                $message->showInDashboard
            );
        }
        $partner = new \Cx\Core_Modules\License\Person(
            $response->license->partner->companyName,
            $response->license->partner->title,
            $response->license->partner->firstname,
            $response->license->partner->lastname,
            $response->license->partner->address,
            $response->license->partner->zip,
            $response->license->partner->city,
            $response->license->partner->country,
            $response->license->partner->phone,
            $response->license->partner->url,
            $response->license->partner->mail
        );
        $customer = new \Cx\Core_Modules\License\Person(
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
        $version = new \Cx\Core_Modules\License\Version(
            $response->versions->currentStable->number,
            $response->versions->currentStable->name,
            $response->versions->currentStable->codeName,
            $response->versions->currentStable->state,
            $response->versions->currentStable->releaseDate
        );
        $license = new \Cx\Core_Modules\License\License(
            $response->license->state,
            $response->license->edition,
            $response->license->availableComponents,
            $response->license->legalComponents,
            $response->license->validTo,
            $response->license->createdAt,
            $response->license->registeredDomains,
            $installationId,
            $licenseKey,
            $licenseManagementMessages,
            $license->getVersion(),
            $partner,
            $customer,
            $response->license->settings->grayZoneTime,
            $gzMessages,
            $response->license->settings->frontendLockTime,
            $this->requestInterval,
            0,
            time(),
            $upgradeUrl,
            $response->license->isUpgradable == 'true',
            $dashboardMessages
        );

        $license->check();

        return;
    }
    
    public function addJsUpdateCode(&$_CORELANG) {
        $lc = LicenseCommunicator::getInstance($this->config);
        if ($lc->isTimeToUpdate($this->config)) {
            \JS::activate('jquery');
            \JS::registerCode('
                jQuery(document).ready(function() {
                    var licenseMessage      = jQuery("#license_message");
                    var cloneLicenseMessage = jQuery("#license_message").clone();
                    
                    licenseMessage.attr("class", "infobox");
                    licenseMessage.text("' . $_CORELANG['TXT_LICENSE_UPDATING'] . '");
                    
                    var revertMessage = function() {
                        licenseMessage.replaceWith(cloneLicenseMessage);
                    }
                    
                    jQuery.get(
                        "../core_modules/License/versioncheck.php"
                    ).success(function(data) {
                        var data = jQuery.parseJSON(data);
                        revertMessage();
                        
                        licenseMessage.attr("class", "upgrade " + data.class);
                        licenseMessage.children("a:first").attr("href", data.link);
                        licenseMessage.children("a:first").attr("target", data.target);
                        licenseMessage.children("a:first").html(data.text);
                    }).error(function(data) {
                        revertMessage();
                    });
                });
            ');
        }
    }
}
