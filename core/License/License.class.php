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
    private $frontendLocked = false;
    private $editionName;
    private $legalComponents;
    private $validTo;
    private $instId;
    private $licenseKey;
    private $message;
    private $version;
    private $customer;
    
    public function __construct(
            $state = self::LICENSE_DEMO,
            $editionName = '',
            $legalComponents = array('license'),
            $validTo = '',
            $instId = '',
            $licenseKey = '',
            $message = '',
            $version = '',
            $customer = ''
    ) {
        $this->state = $state;
        $this->editionName = $editionName;
        $this->legalComponents = $legalComponents;
        $this->validTo = $validTo;
        $this->instId = $instId;
        $this->licenseKey = $licenseKey;
        $this->message = $message;
        $this->version = $version;
        $this->customer = $customer;
    }
    
    public function getState() {
        return $this->state;
    }
    
    public function isFrontendLocked() {
        return $this->frontendLocked;
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
    
    public function getMessage() {
        return $this->message;
    }
    
    public function getVersion() {
        return $this->version;
    }
    
    public function getCustomer() {
        return $this->customer;
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
            $this->legalComponents = array('license');
            $this->frontendLocked = true;
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
        $oldpost = $_POST;
        unset($_POST);
        
        $_POST['setvalue'][75] = $this->getInstallationId();                // installationId
        $_POST['setvalue'][76] = $this->getLicenseKey();                    // licenseKey
        $_POST['setvalue'][90] = $this->getState();                         // licenseState
        $_POST['setvalue'][91] = $this->getValidToDate();                   // licenseValidTo
        $_POST['setvalue'][92] = $this->getEditionName();                   // coreCmsEdition
        
        $_POST['setvalue'][93] = $this->getMessage()->getText();            // messageText
        $_POST['setvalue'][94] = $this->getMessage()->getType();            // messageType
        $_POST['setvalue'][95] = $this->getMessage()->getLink();            // messageLink
        $_POST['setvalue'][96] = $this->getMessage()->getLinkTarget();      // messageLinkTarget
        
        $_POST['setvalue'][97] = $this->getVersion()->getNumber();          // coreCmsVersion
        $_POST['setvalue'][98] = $this->getVersion()->getCodeName();        // coreCmsCodeName
        $_POST['setvalue'][99] = $this->getVersion()->getState();           // coreCmsStatus
        $_POST['setvalue'][100] = $this->getVersion()->getReleaseDate();    // coreCmsReleaseDate
        
        $_POST['setvalue'][101] = $this->getCustomer()->getCompanyName();   // licenseHolderCompany
        $_POST['setvalue'][102] = $this->getCustomer()->getTitle();         // licenseHolderTitle
        $_POST['setvalue'][103] = $this->getCustomer()->getFirstname();     // licenseHolderFirstname
        $_POST['setvalue'][104] = $this->getCustomer()->getLastname();      // licenseHolderLastname
        $_POST['setvalue'][105] = $this->getCustomer()->getAddress();       // licenseHolderAddress
        $_POST['setvalue'][106] = $this->getCustomer()->getZip();           // licenseHolderZip
        $_POST['setvalue'][107] = $this->getCustomer()->getCity();          // licenseHolderCity
        $_POST['setvalue'][108] = $this->getCustomer()->getCountry();       // licenseHolderCountry
        $_POST['setvalue'][109] = $this->getCustomer()->getPhone();         // licenseHolderPhone
        $_POST['setvalue'][110] = $this->getCustomer()->getUrl();           // licenseHolderUrl
        $_POST['setvalue'][111] = $this->getCustomer()->getMail();          // licenseHolderMail
        
        $_POST['setvalue'][112] = $this->getVersion()->getName();           // coreCmsName
        
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
        unset($_POST);
        $_POST = $oldpost;
    }
    
    /**
     * @param \SettingDb $settings Reference to the settings manager object
     * @return \Cx\Core\License\License
     */
    public static function getCached(&$_CONFIG, $objDb) {
        $state = isset($_CONFIG['licenseState']) ? $_CONFIG['licenseState'] : self::LICENSE_DEMO;
        $validTo = isset($_CONFIG['licenseValidTo']) ? $_CONFIG['licenseValidTo'] : null;
        $editionName = isset($_CONFIG['coreCmsEdition']) ? $_CONFIG['coreCmsEdition'] : null;
        $instId = isset($_CONFIG['installationId']) ? $_CONFIG['installationId'] : null;
        $licenseKey = isset($_CONFIG['licenseKey']) ? $_CONFIG['licenseKey'] : null;
        
        $messageText = isset($_CONFIG['messageText']) ? $_CONFIG['messageText'] : null;
        $messageType = isset($_CONFIG['messageType']) ? $_CONFIG['messageType'] : null;
        $messageLink = isset($_CONFIG['messageLink']) ? $_CONFIG['messageLink'] : null;
        $messageLinkTarget = isset($_CONFIG['messageLinkTarget']) ? $_CONFIG['messageLinkTarget'] : null;
        $message = new Message($messageText, $messageType, $messageLink, $messageLinkTarget);
        
        $customerCompanyName = isset($_CONFIG['licenseHolderCompany']) ? $_CONFIG['licenseHolderCompany'] : null;
        $customerTitle = isset($_CONFIG['licenseHolderTitle']) ? $_CONFIG['licenseHolderTitle'] : null;
        $customerFirstname = isset($_CONFIG['licenseHolderFirstname']) ? $_CONFIG['licenseHolderFirstname'] : null;
        $customerLastname = isset($_CONFIG['licenseHolderLastname']) ? $_CONFIG['licenseHolderLastname'] : null;
        $customerAddress = isset($_CONFIG['licenseHolderAddress']) ? $_CONFIG['licenseHolderAddress'] : null;
        $customerZip = isset($_CONFIG['licenseHolderZip']) ? $_CONFIG['licenseHolderZip'] : null;
        $customerCity = isset($_CONFIG['licenseHolderCity']) ? $_CONFIG['licenseHolderCity'] : null;
        $customerCountry = isset($_CONFIG['licenseHolderCountry']) ? $_CONFIG['licenseHolderCountry'] : null;
        $customerPhone = isset($_CONFIG['licenseHolderPhone']) ? $_CONFIG['licenseHolderPhone'] : null;
        $customerUrl = isset($_CONFIG['licenseHolderUrl']) ? $_CONFIG['licenseHolderUrl'] : null;
        $customerMail = isset($_CONFIG['licenseHolderMail']) ? $_CONFIG['licenseHolderMail'] : null;
        $customer = new Customer(
            $customerCompanyName,
            $customerTitle,
            $customerFirstname,
            $customerLastname,
            $customerAddress,
            $customerZip,
            $customerCity,
            $customerCountry,
            $customerPhone,
            $customerUrl,
            $customerMail
        );
        
        $versionNumber = isset($_CONFIG['coreCmsVersion']) ? $_CONFIG['coreCmsVersion'] : null;
        $versionName = isset($_CONFIG['coreCmsName']) ? $_CONFIG['coreCmsName'] : null;
        $versionCodeName = isset($_CONFIG['coreCmsCodeName']) ? $_CONFIG['coreCmsCodeName'] : null;
        $versionState = isset($_CONFIG['coreCmsStatus']) ? $_CONFIG['coreCmsStatus'] : null;
        $versionReleaseDate = isset($_CONFIG['coreCmsReleaseDate']) ? $_CONFIG['coreCmsReleaseDate'] : null;
        $version = new Version($versionNumber, $versionName, $versionCodeName, $versionState, $versionReleaseDate);
        
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
        return new static(
            $state,
            $editionName,
            $activeComponents,
            $validTo,
            $instId,
            $licenseKey,
            $message,
            $version,
            $customer
        );
    }
}
