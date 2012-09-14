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
    
    public function __construct($act, $template, &$_CORELANG, &$_CONFIG) {
        $this->act = $act;
        $this->template = $template;
        $this->lang = $_CORELANG;
        $this->config = $_CONFIG;
        $this->license = License::getCached($_CONFIG);
        $this->license->check();
        $this->template->setVariable('CONTENT_NAVIGATION', '
            <a href="index.php?cmd=license" class="'.($this->act == '' ? 'active' : '').'">'.$_CORELANG['TXT_LICENSE'].'</a>
            <a href="index.php?cmd=license&amp;act=settings" class="' . ($this->act == 'settings' ? 'active' : '') . '">' . $this->lang['TXT_SETTINGS'] . '</a>
        ');
    }
    
    public function getPage($_POST) {
        if ($this->act == 'settings') {
            if (isset($_POST['licenseKey'])) {
                $license = License::getCached($this->config);
                $license->setLicenseKey(contrexx_input2db($_POST['licenseKey']));
                // save it before we check it, so we only change the license key
                $license->save(new \settingsManager());
                $license->check();
                $this->license = $license;
            }
            $this->template->addBlockfile('ADMIN_CONTENT', 'licenseManager', 'licenseManager.html');
            $this->template->touchBlock('licenseManager');
            $this->template->setVariable($this->lang);
            $date = $this->license->getValidToDate();
            if ($date) {
                $formattedDate = date(ASCMS_DATE_FORMAT_DATE, $date);
            } else {
                $formattedDate = '';
            }
            $this->template->setVariable(array(
                'LICENSE_STATE' => $this->lang['TXT_LICENSE_STATE_' . $this->license->getState()],
                'LICENSE_VALID_TO' => $formattedDate,
                'INSTALLATION_ID' => $this->license->getInstallationId(),
                'LICENSE_KEY' => $this->license->getLicenseKey(),
            ));
        } else {
            if (!file_exists(ASCMS_TEMP_PATH . '/licenseManager.html')) {
                $lc = LicenseCommunicator::getInstance();
                $lc->update($this->license, $this->config, true);
            }
            if (file_exists(ASCMS_TEMP_PATH . '/licenseManager.html')) {
                $this->template->addBlockfile('ADMIN_CONTENT', 'licenseManager', ASCMS_TEMP_PATH . '/licenseManager.html');
                $this->template->touchBlock('licenseManager');
            } else {
                $this->template->setVariable('ADMIN_CONTENT', $this->lang['TXT_LICENSE_NO_TEMPLATE']);
            }
        }
    }
}
