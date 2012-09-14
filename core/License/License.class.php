<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Cx\Core\License;

/**
 * Description of License
 *
 * @author ritt0r
 */
class License {
    const LICENSE_OK = 1;
    const LICENSE_NOK = -1;
    const LICENSE_DEMO = 2;
    const LICENSE_ERROR = 3;
    private $state;
    private $validTo;
    private $instId;
    private $licenseKey;
    
    public function __construct($state = self::LICENSE_DEMO, $validTo = null, $instId = null, $licenseKey = null) {
        $this->state = $state;
        $this->validTo = $validTo;
        $this->instId = $instId;
        $this->licenseKey = $licenseKey;
    }
    
    public function getState() {
        return $this->state;
    }
    
    public function getValidToDate() {
        return $this->validTo;
    }
    
    public function setValidToDate($timestamp) {
        $this->validTo = $timestamp;
    }
    
    public function getInstallationId() {
        return $this->instId;
    }
    
    public function getLicenseKey() {
        return $this->licenseKey;
    }
    
    public function setLicenseKey($key) {
        $this->licenseKey = $key;
    }
    
    public function check() {
        $validTo = 0;
        switch ($this->state) {
            case self::LICENSE_DEMO:
            case self::LICENSE_OK:
                $validTo = $this->validTo;
                break;
            case self::LICENSE_ERROR:
                $validTo = time() + 60*60*24*$this->grayzoneTime;
                break;
        }
        if (empty($this->instId) || empty($this->licenseKey) || $validTo < time()) {
            $this->state = self::LICENSE_NOK;
            $validTo = 0;
        }
    }
    
    public function save($settingsManager) {
        // WARNING, this is the ugly way:
        global $_POST;
        unset($_POST);
        $_POST['setvalue'][90] = $this->getState();
        $_POST['setvalue'][91] = $this->getValidToDate();
        $_POST['setvalue'][75] = $this->getInstallationId();
        $_POST['setvalue'][76] = $this->getLicenseKey();
        $settingsManager->updateSettings();
        $settingsManager->writeSettingsFile();
    }
    
    /**
     * @param \SettingDb $settings Reference to the settings manager object
     * @return License
     */
    public static function getCached(&$_CONFIG) {
        $state = isset($_CONFIG['licenseState']) ? $_CONFIG['licenseState'] : self::LICENSE_DEMO;
        $validTo = isset($_CONFIG['licenseValidTo']) ? $_CONFIG['licenseValidTo'] : null;
        $instId = isset($_CONFIG['installationId']) ? $_CONFIG['installationId'] : null;
        $licenseKey = isset($_CONFIG['licenseKey']) ? $_CONFIG['licenseKey'] : null;
        return new static($state, $validTo, $instId, $licenseKey);
    }
}
