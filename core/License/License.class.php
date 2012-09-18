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
    const LICENSE_OK = 'OK';
    const LICENSE_NOK = 'NOK';
    const LICENSE_DEMO = 'DEMO';
    const LICENSE_ERROR = 'ERROR';
    private $state;
    private $editionName;
    private $legalComponents;
    private $validTo;
    private $instId;
    private $licenseKey;
    
    public function __construct($state = self::LICENSE_DEMO, $editionName = null, $legalComponents = array(), $validTo = null, $instId = null, $licenseKey = null) {
        $this->state = $state;
        $this->editionName = $editionName;
        $this->legalComponents = $legalComponents;
        $this->validTo = $validTo;
        $this->instId = $instId;
        $this->licenseKey = $licenseKey;
    }
    
    public function getState() {
        return $this->state;
    }
    
    public function getEditionName() {
        return $this->editionName;
    }
    
    public function getLegalComponentsList() {
        return $this->legalComponents;
    }
    
    public function isInLegalComponents($componentName) {
        return in_array($componentName, $this->legalComponents);
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
    
    /**
     *
     * @global type $_POST
     * @param \settingsManager $settingsManager
     * @param \ADONewConnection $objDb 
     */
    public function save($settingsManager, $objDb) {
        // WARNING, this is the ugly way:
        global $_POST;
        unset($_POST);
        $_POST['setvalue'][90] = $this->getState();
        $_POST['setvalue'][91] = $this->getValidToDate();
        $_POST['setvalue'][92] = $this->getEditionName();
        $_POST['setvalue'][75] = $this->getInstallationId();
        $_POST['setvalue'][76] = $this->getLicenseKey();
        $settingsManager->updateSettings();
        $settingsManager->writeSettingsFile();
        
        $query = '
            UPDATE
                '.DBPREFIX.'modules
            SET
                `is_active` = \'0\'
        ';
        $objDb->Execute($query);
        $query = '
            UPDATE
                '.DBPREFIX.'modules
            SET
                `is_active` = \'1\'
            WHERE
                `name` IN(\'' . implode('\', \'', $this->getLegalComponentsList()) . '\')
        ';
        $objDb->Execute($query);
    }
    
    /**
     * @param \SettingDb $settings Reference to the settings manager object
     * @return \Cx\Core\License\License
     */
    public static function getCached(&$_CONFIG, $objDb) {
        $state = isset($_CONFIG['licenseState']) ? $_CONFIG['licenseState'] : self::LICENSE_DEMO;
        $validTo = isset($_CONFIG['licenseValidTo']) ? $_CONFIG['licenseValidTo'] : null;
        $editionName = isset($_CONFIG['editionName']) ? $_CONFIG['editionName'] : null;
        $instId = isset($_CONFIG['installationId']) ? $_CONFIG['installationId'] : null;
        $licenseKey = isset($_CONFIG['licenseKey']) ? $_CONFIG['licenseKey'] : null;
        $query = '
            SELECT
                `name`
            FROM
                '.DBPREFIX.'modules
            WHERE
                `is_active` = \'1\'
        ';
        $result = $objDb->execute($query);
        $activeComponents = array();
        if ($result) {
            while (!$result->EOF) {
                $activeComponents[] = $result->fields['name'];
                $result->MoveNext();
            }
        }
        return new static($state, $editionName, $activeComponents, $validTo, $instId, $licenseKey);
    }
}
