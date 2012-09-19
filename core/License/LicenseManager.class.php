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
        if (isset($_POST['save']) && isset($_POST['licenseKey'])) {
            $license = License::getCached($this->config, $this->db);
            $license->setLicenseKey(contrexx_input2db($_POST['licenseKey']));
            // save it before we check it, so we only change the license key
            $license->save(new \settingsManager(), $this->db);
            $license->check();
            $this->license = $license;
        } else if (isset($_POST['update'])) {
            $lc = LicenseCommunicator::getInstance();
            $lc->update($this->license, $this->config);
            $this->license->save(new \settingsManager(), $this->db);
        }
        $date = $this->license->getValidToDate();
        if ($date) {
            $formattedDate = date(ASCMS_DATE_FORMAT_DATE, $date);
        } else {
            $formattedDate = '';
        }
        if (!file_exists(ASCMS_TEMP_PATH . '/licenseManager.html')) {
            $lc = LicenseCommunicator::getInstance();
            $lc->update($this->license, $this->config, true, true);
        }
        if (file_exists(ASCMS_TEMP_PATH . '/licenseManager.html')) {
            $remoteTemplate = new \HTML_Template_Sigma(ASCMS_TEMP_PATH);
            $remoteTemplate->loadTemplateFile('/licenseManager.html');
            $remoteTemplate->touchBlock('licenseManager');
            $remoteTemplate->setVariable($this->lang);
            $remoteTemplate->setVariable(array(
                'LICENSE_STATE' => $this->lang['TXT_LICENSE_STATE_' . $this->license->getState()],
                'LICENSE_EDITION' => $this->license->getEditionName(),
                'LICENSE_VALID_TO' => $formattedDate,
                'INSTALLATION_ID' => $this->license->getInstallationId(),
                'LICENSE_KEY' => $this->license->getLicenseKey(),
            ));
            $this->template->setVariable('ADMIN_CONTENT', $remoteTemplate->get());
        } else {
            $this->template->setVariable('ADMIN_CONTENT', $this->lang['TXT_LICENSE_NO_TEMPLATE']);
        }
    }
}
