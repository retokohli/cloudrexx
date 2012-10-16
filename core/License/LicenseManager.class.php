<?php

namespace Cx\Core\License;

class LicenseManager {
    /**
     * @var string 
     */
    private $act;
    /**
     * @var \HTML_Template_Sigma
     */
    private $template;
    /**
     * @var array
     */
    private $lang;
    /**
     * @var array
     */
    private $config;
    /**
     * @var License
     */
    private $license;
    /**
     * @var \ADONewConnection
     */
    private $db;
    
    public function __construct($act, $template, &$_CORELANG, &$_CONFIG, &$objDb) {
        $this->act = $act;
        $this->template = $template;
        $this->lang = $_CORELANG;
        $this->config = $_CONFIG;
        $this->db = $objDb;
        $this->license = License::getCached($_CONFIG, $this->db, $_CORELANG);
        $this->license->check();
        $this->template->setVariable('CONTENT_NAVIGATION', '
            <a href="index.php?cmd=license" class="active">'.$_CORELANG['TXT_LICENSE'].'</a>
        ');
    }
    
    public function getPage($_POST) {
        if (\FWUser::getFWUserObject()->objUser->getAdminStatus()) {
            if (isset($_POST['save']) && isset($_POST['licenseKey'])) {
                $license = License::getCached($this->config, $this->db, $_CORELANG);
                $license->setLicenseKey(contrexx_input2db($_POST['licenseKey']));
                // save it before we check it, so we only change the license key
                $license->save(new \settingsManager(), $this->db);
                $license->check();
                $this->license = $license;
            } else if (isset($_POST['update'])) {
                $lc = LicenseCommunicator::getInstance($this->config);
                $lc->update($this->license, $this->config, true, false, $this->lang);
                $this->license->save(new \settingsManager(), $this->db);
            }
        }
        $date = $this->license->getValidToDate();
        if ($date) {
            $formattedValidityDate = date(ASCMS_DATE_FORMAT_DATE, $date);
        } else {
            $formattedValidityDate = '';
        }
        $date = $this->license->getCreatedAtDate();
        if ($date) {
            $formattedCreateDate = date(ASCMS_DATE_FORMAT_DATE, $date);
        } else {
            $formattedCreateDate = '';
        }
        if (!file_exists(ASCMS_TEMP_PATH . '/licenseManager.html')) {
            $lc = LicenseCommunicator::getInstance($this->config);
            $lc->update($this->license, $this->config, true, true, $this->lang);
            $this->license->save(new \settingsManager(), $this->db);
        }
        if (file_exists(ASCMS_TEMP_PATH . '/licenseManager.html')) {
            \JS::activate('cx');
            $remoteTemplate = new \HTML_Template_Sigma(ASCMS_TEMP_PATH);
            $remoteTemplate->loadTemplateFile('/licenseManager.html');
            
            if (isset($_POST['save']) && isset($_POST['licenseKey'])) {
                $remoteTemplate->setVariable('STATUS_TYPE', 'okbox');
                $remoteTemplate->setVariable('STATUS_MESSAGE', $this->lang['TXT_LICENSE_SAVED']);
            } else if (isset($_POST['update'])) {
                $remoteTemplate->setVariable('STATUS_TYPE', 'okbox');
                $remoteTemplate->setVariable('STATUS_MESSAGE', $this->lang['TXT_LICENSE_UPDATED']);
            }
            
            $remoteTemplate->setVariable($this->lang);
            
            $remoteTemplate->setVariable(array(
                'LICENSE_STATE' => $this->lang['TXT_LICENSE_STATE_' . $this->license->getState()],
                'LICENSE_EDITION' => contrexx_raw2xhtml($this->license->getEditionName()),
                'INSTALLATION_ID' => contrexx_raw2xhtml($this->license->getInstallationId()),
                'LICENSE_KEY' => contrexx_raw2xhtml($this->license->getLicenseKey()),
                'LICENSE_VALID_TO' => contrexx_raw2xhtml($formattedValidityDate),
                'LICENSE_CREATED_AT' => contrexx_raw2xhtml($formattedCreateDate),
                'LICENSE_REQUEST_INTERVAL' => contrexx_raw2xhtml($this->license->getRequestInterval()),
                'LICENSE_GRAYZONE_DAYS' => contrexx_raw2xhtml($this->license->getGrayzoneTime()),
                'LICENSE_FRONTENT_OFFSET_DAYS' => contrexx_raw2xhtml($this->license->getFrontendLockTime()),
                
                'LICENSE_PARTNER_TITLE' => contrexx_raw2xhtml($this->license->getPartner()->getTitle()),
                'LICENSE_PARTNER_LASTNAME' => contrexx_raw2xhtml($this->license->getPartner()->getLastname()),
                'LICENSE_PARTNER_FIRSTNAME' => contrexx_raw2xhtml($this->license->getPartner()->getFirstname()),
                'LICENSE_PARTNER_COMPANY' => contrexx_raw2xhtml($this->license->getPartner()->getCompanyName()),
                'LICENSE_PARTNER_ADDRESS' => contrexx_raw2xhtml($this->license->getPartner()->getAddress()),
                'LICENSE_PARTNER_ZIP' => contrexx_raw2xhtml($this->license->getPartner()->getZip()),
                'LICENSE_PARTNER_CITY' => contrexx_raw2xhtml($this->license->getPartner()->getCity()),
                'LICENSE_PARTNER_COUNTRY' => contrexx_raw2xhtml($this->license->getPartner()->getCountry()),
                'LICENSE_PARTNER_PHONE' => contrexx_raw2xhtml($this->license->getPartner()->getPhone()),
                'LICENSE_PARTNER_URL' => contrexx_raw2xhtml($this->license->getPartner()->getUrl()),
                'LICENSE_PARTNER_MAIL' => contrexx_raw2xhtml($this->license->getPartner()->getMail()),
                
                'LICENSE_CUSTOMER_TITLE' => contrexx_raw2xhtml($this->license->getCustomer()->getTitle()),
                'LICENSE_CUSTOMER_LASTNAME' => contrexx_raw2xhtml($this->license->getCustomer()->getLastname()),
                'LICENSE_CUSTOMER_FIRSTNAME' => contrexx_raw2xhtml($this->license->getCustomer()->getFirstname()),
                'LICENSE_CUSTOMER_COMPANY' => contrexx_raw2xhtml($this->license->getCustomer()->getCompanyName()),
                'LICENSE_CUSTOMER_ADDRESS' => contrexx_raw2xhtml($this->license->getCustomer()->getAddress()),
                'LICENSE_CUSTOMER_ZIP' => contrexx_raw2xhtml($this->license->getCustomer()->getZip()),
                'LICENSE_CUSTOMER_CITY' => contrexx_raw2xhtml($this->license->getCustomer()->getCity()),
                'LICENSE_CUSTOMER_COUNTRY' => contrexx_raw2xhtml($this->license->getCustomer()->getCountry()),
                'LICENSE_CUSTOMER_PHONE' => contrexx_raw2xhtml($this->license->getCustomer()->getPhone()),
                'LICENSE_CUSTOMER_URL' => contrexx_raw2xhtml($this->license->getCustomer()->getUrl()),
                'LICENSE_CUSTOMER_MAIL' => contrexx_raw2xhtml($this->license->getCustomer()->getMail()),
                
                'VERSION_NUMBER' => contrexx_raw2xhtml($this->license->getVersion()->getNumber()),
                'VERSION_NUMBER_INT' => contrexx_raw2xhtml($this->license->getVersion()->getNumber(true)),
                'VERSION_NAME' => contrexx_raw2xhtml($this->license->getVersion()->getName()),
                'VERSION_CODENAME' => contrexx_raw2xhtml($this->license->getVersion()->getCodeName()),
                'VERSION_STATE' => contrexx_raw2xhtml($this->license->getVersion()->getState()),
                'VERSION_RELEASE_DATE' => contrexx_raw2xhtml($this->license->getVersion()->getReleaseDate()),
            ));
            
            if ($remoteTemplate->blockExists('legalComponents')) {
                foreach ($this->license->getLegalComponentsList() as $component) {
                    $remoteTemplate->setVariable('LICENSE_LEGAL_COMPONENT', contrexx_raw2xhtml($component));
                    $remoteTemplate->parse('legalComponents');
                }
            }
            
            if ($remoteTemplate->blockExists('licenseDomain')) {
                foreach ($this->license->getRegisteredDomains() as $domain) {
                    $remoteTemplate->setVariable('LICENSE_DOMAIN', contrexx_raw2xhtml($domain));
                    $remoteTemplate->parse('licenseDomain');
                }
            }
            
            $message = $this->license->getMessage(\FWLanguage::getLanguageCodeById(BACKEND_LANG_ID));
            if ($message && strlen($message->getText())) {
                $remoteTemplate->setVariable('MESSAGE_TITLE', contrexx_raw2xhtml($message->getText()));
                $remoteTemplate->setVariable('MESSAGE_LINK', contrexx_raw2xhtml($message->getLink()));
                $remoteTemplate->setVariable('MESSAGE_LINK_TARGET', contrexx_raw2xhtml($message->getLinkTarget()));
                $remoteTemplate->setVariable('MESSAGE_TYPE', contrexx_raw2xhtml($message->getType()));
            } else {
                if ($remoteTemplate->blockExists('message')) {
                    $remoteTemplate->hideBlock('message');
                }
            }
            
            if (\FWUser::getFWUserObject()->objUser->getAdminStatus()) {
                $remoteTemplate->touchBlock('licenseAdmin');
                $remoteTemplate->hideBlock('licenseNotAdmin');
            } else {
                $remoteTemplate->hideBlock('licenseAdmin');
                $remoteTemplate->touchBlock('licenseNotAdmin');
                $remoteTemplate->setVariable('LICENSE_ADMIN_MAIL', contrexx_raw2xhtml($this->config['coreAdminEmail']));
            }
            
            $this->template->setVariable('ADMIN_CONTENT', $remoteTemplate->get());
        } else {
            $this->template->setVariable('ADMIN_CONTENT', $this->lang['TXT_LICENSE_NO_TEMPLATE']);
        }
    }
}
