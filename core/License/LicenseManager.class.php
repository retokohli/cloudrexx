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
        $this->license = License::getCached($_CONFIG, $this->db);
        $this->license->check();
        $this->template->setVariable('CONTENT_NAVIGATION', '
            <a href="index.php?cmd=license" class="active">'.$_CORELANG['TXT_LICENSE'].'</a>
        ');
    }
    
    public function getPage($_POST) {
        if (\FWUser::getFWUserObject()->objUser->getAdminStatus()) {
            if (isset($_POST['save']) && isset($_POST['licenseKey'])) {
                $license = License::getCached($this->config, $this->db);
                $license->setLicenseKey(contrexx_input2db($_POST['licenseKey']));
                // save it before we check it, so we only change the license key
                $license->save(new \settingsManager(), $this->db);
                $license->check();
                $this->license = $license;
            } else if (isset($_POST['update'])) {
                $lc = LicenseCommunicator::getInstance($this->config);
                $lc->update($this->license, $this->config, true);
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
            $lc->update($this->license, $this->config, true, true);
            $this->license->save(new \settingsManager(), $this->db);
        }
        if (file_exists(ASCMS_TEMP_PATH . '/licenseManager.html')) {
            \JS::activate('cx');
            $remoteTemplate = new \HTML_Template_Sigma(ASCMS_TEMP_PATH);
            $remoteTemplate->loadTemplateFile('/licenseManager.html');
            $remoteTemplate->setVariable($this->lang);
            $remoteTemplate->setVariable(array(
                'LICENSE_STATE' => $this->lang['TXT_LICENSE_STATE_' . $this->license->getState()],
                'LICENSE_EDITION' => $this->license->getEditionName(),
                'LICENSE_HOLDER_TITLE' => $this->license->getCustomer()->getTitle(),
                'LICENSE_HOLDER_LASTNAME' => $this->license->getCustomer()->getLastname(),
                'LICENSE_HOLDER_FIRSTNAME' => $this->license->getCustomer()->getFirstname(),
                'LICENSE_HOLDER_COMPANY' => $this->license->getCustomer()->getCompanyName(),
                'LICENSE_HOLDER_ADDRESS' => $this->license->getCustomer()->getAddress(),
                'LICENSE_HOLDER_ZIP' => $this->license->getCustomer()->getZip(),
                'LICENSE_HOLDER_CITY' => $this->license->getCustomer()->getCity(),
                'LICENSE_HOLDER_COUNTRY' => $this->license->getCustomer()->getCountry(),
                'LICENSE_HOLDER_PHONE' => $this->license->getCustomer()->getPhone(),
                'LICENSE_HOLDER_URL' => $this->license->getCustomer()->getUrl(),
                'LICENSE_HOLDER_MAIL' => $this->license->getCustomer()->getMail(),
                'LICENSE_VALID_TO' => $formattedValidityDate,
                'LICENSE_CREATED_AT' => $formattedCreateDate,
                'INSTALLATION_ID' => $this->license->getInstallationId(),
                'LICENSE_KEY' => $this->license->getLicenseKey(),
            ));
            if ($remoteTemplate->blockExists('licenseDomain')) {
                foreach ($this->license->getRegisteredDomains() as $domain) {
                    $remoteTemplate->setVariable('LICENSE_DOMAIN', $domain);
                    $remoteTemplate->parse('licenseDomain');
                }
            }
            $remoteTemplate->setVariable('MESSAGE_TITLE', 'message');
            if (\FWUser::getFWUserObject()->objUser->getAdminStatus()) {
                $remoteTemplate->touchBlock('licenseAdmin');
                $remoteTemplate->hideBlock('licenseNotAdmin');
            } else {
                $remoteTemplate->hideBlock('licenseAdmin');
                $remoteTemplate->touchBlock('licenseNotAdmin');
            }
            $this->template->setVariable('ADMIN_CONTENT', $remoteTemplate->get());
        } else {
            $this->template->setVariable('ADMIN_CONTENT', $this->lang['TXT_LICENSE_NO_TEMPLATE']);
        }
    }
}
