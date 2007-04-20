<?php
/**
 * Settings
 *
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author        Astalavista Development Team <thun@astalvista.ch>
 * @version        1.1.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_CORE_MODULE_PATH.'/cache/admin.class.php';
require_once ASCMS_CORE_PATH.'/'.'GoogleSitemap.class.php';
require_once ASCMS_CORE_PATH.'/SmtpSettings.class.php';

/**
 * Settings
 *
 * CMS Settings management
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author        Astalavista Development Team <thun@astalvista.ch>
 * @access        public
 * @version        1.1.0
 * @package     contrexx
 * @subpackage  core
 */
class settingsManager {
    var $_objTpl;
    var $strPageTitle;
    var $strSettingsFile;
    var $strErrMessage;
    var $strOkMessage;
    var $_objSmtp;

    function settingsManager()
    {
        $this->__construct();
    }

    function __construct()
    {
        $this->strSettingsFile = ASCMS_DOCUMENT_ROOT.'/config/settings.php';
    }

    /**
     * Perform the requested function depending on $_GET['act']
     *
     * @global  array   Core language
     * @global  mixed   Template
     * @return  void
     */
    function getPage()
    {
           global $_CORELANG, $objTemplate;

        $objTemplate->setVariable('CONTENT_NAVIGATION',	'<a href="?cmd=settings">'.$_CORELANG['TXT_SETTINGS_MENU_SYSTEM'].'</a>'.
                                                        '<a href="?cmd=settings&amp;act=cache">'.$_CORELANG['TXT_SETTINGS_MENU_CACHE'].'</a>'.
                                                        '<a href="?cmd=settings&amp;act=smtp">'.$_CORELANG['TXT_SETTINGS_EMAIL'].'</a>'
        );

        if(!isset($_GET['act'])){
            $_GET['act']='';
        }

        $boolShowStatus = true;

        switch ($_GET['act']) {
            case 'update':
                $this->updateSettings();
                $this->writeSettingsFile();
                $this->showSettings();
                break;

            case 'cache':
                $boolShowStatus = false;
                $objCache = &new Cache();
                $objCache->showSettings();
                break;

            case 'cache_update':
                $boolShowStatus = false;
                $objCache = &new Cache();
                $objCache->updateSettings();
                $objCache->writeCacheablePagesFile();
                $objCache->showSettings();
                $this->writeSettingsFile();
                break;

            case 'cache_empty':
                $boolShowStatus = false;
                $objCache = &new Cache();
                $objCache->deleteAllFiles();
                $objCache->showSettings();
                break;

            case 'smtp':
            	$this->smtp();
            	break;

            default:
                $this->showSettings();
        }

        if ($boolShowStatus) {
            $objTemplate->setVariable(array(
                'CONTENT_TITLE'                =>     $this->strPageTitle,
                'CONTENT_OK_MESSAGE'        =>    $this->strOkMessage,
                'CONTENT_STATUS_MESSAGE'    =>     $this->strErrMessage
            ));
        }
    }

    /**
     * Set the cms system settings
     * @global  mixed   Database
     * @global  array   Core language
     * @global  mixed   Template
     */
    function showSettings()
    {
        global $objDatabase, $_CORELANG, $objTemplate;

        $objTemplate->addBlockfile('ADMIN_CONTENT', 'settings', 'settings.html');
        $this->strPageTitle = $_CORELANG['TXT_SYSTEM_SETTINGS'];

        $objTemplate->setVariable(array(
            'TXT_TITLE_SET1'                  => $_CORELANG['TXT_SETTINGS_TITLE_MISC'],
            'TXT_TITLE_SET2'                  => $_CORELANG['TXT_SETTINGS_TITLE_CONTACT'],
            'TXT_SAVE_CHANGES'                => $_CORELANG['TXT_SAVE'],
            'TXT_RADIO_ON'                    => $_CORELANG['TXT_ACTIVATED'],
            'TXT_RADIO_OFF'                   => $_CORELANG['TXT_DEACTIVATED'],
            'TXT_SYSTEM_STATUS'               => $_CORELANG['TXT_SETTINGS_SYSTEMSTATUS'],
            'TXT_SYSTEM_STATUS_HELP'          => $_CORELANG['TXT_SETTINGS_SYSTEMSTATUS_HELP'],
            'TXT_IDS_STATUS'                  => $_CORELANG['TXT_SETTINGS_IDS'],
            'TXT_IDS_STATUS_HELP'             => $_CORELANG['TXT_SETTINGS_IDS_HELP'],
            'TXT_HISTORY_STATUS'              => $_CORELANG['TXT_SETTINGS_HISTORY'],
            'TXT_HISTORY_STATUS_HELP'         => $_CORELANG['TXT_SETTINGS_HISTORY_HELP'],
            'TXT_GOOGLESITEMAP_STATUS'        => $_CORELANG['TXT_SETTINGS_GOOGLESITEMAP'],
            'TXT_GOOGLESITEMAP_STATUS_HELP'   => $_CORELANG['TXT_SETTINGS_GOOGLESITEMAP_HELP'],
            'TXT_GLOBAL_TITLE'                => $_CORELANG['TXT_SETTINGS_GLOBAL_TITLE'],
            'TXT_GLOBAL_TITLE_HELP'           => $_CORELANG['TXT_SETTINGS_GLOBAL_TITLE_HELP'],
            'TXT_DOMAIN_URL'                  => $_CORELANG['TXT_SETTINGS_DOMAIN_URL'],
            'TXT_DOMAIN_URL_HELP'             => $_CORELANG['TXT_SETTINGS_DOMAIN_URL_HELP'],
            'TXT_PAGING_LIMIT'                => $_CORELANG['TXT_SETTINGS_PAGING_LIMIT'],
            'TXT_PAGING_LIMIT_HELP'           => $_CORELANG['TXT_SETTINGS_PAGING_LIMIT_HELP'],
            'TXT_SEARCH_RESULT'               => $_CORELANG['TXT_SETTINGS_SEARCH_RESULT'],
            'TXT_SEARCH_RESULT_HELP'          => $_CORELANG['TXT_SETTINGS_SEARCH_RESULT_HELP'],
            'TXT_SESSION_LIVETIME'            => $_CORELANG['TXT_SETTINGS_SESSION_LIVETIME'],
            'TXT_SESSION_LIVETIME_HELP'       => $_CORELANG['TXT_SETTINGS_SESSION_LIVETIME_HELP'],
            'TXT_DNS_SERVER'                  => $_CORELANG['TXT_SETTINGS_DNS_SERVER'],
            'TXT_DNS_SERVER_HELP'             => $_CORELANG['TXT_SETTINGS_DNS_SERVER_HELP'],
            'TXT_ADMIN_NAME'                  => $_CORELANG['TXT_SETTINGS_ADMIN_NAME'],
            'TXT_ADMIN_EMAIL'                 => $_CORELANG['TXT_SETTINGS_ADMIN_EMAIL'],
            'TXT_CONTACT_EMAIL'               => $_CORELANG['TXT_SETTINGS_CONTACT_EMAIL'],
            'TXT_CONTACT_EMAIL_HELP'          => $_CORELANG['TXT_SETTINGS_CONTACT_EMAIL_HELP'],
            'TXT_SEARCH_VISIBLE_CONTENT_ONLY' => $_CORELANG['TXT_SEARCH_VISIBLE_CONTENT_ONLY'],
        ));

        $objResult = $objDatabase->Execute('SELECT setid,
                                                   setname,
                                                   setvalue,
                                                   setmodule
                                            FROM '.DBPREFIX.'settings');
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $arrSettings[$objResult->fields['setname']] = $objResult->fields['setvalue'];
                $objResult->MoveNext();
            }
        }

        $objTemplate->setVariable(array(
            'SETTINGS_CONTACT_EMAIL'              => htmlentities($arrSettings['contactFormEmail'], ENT_QUOTES, CONTREXX_CHARSET),
            'SETTINGS_ADMIN_EMAIL'                => htmlentities($arrSettings['coreAdminEmail'], ENT_QUOTES, CONTREXX_CHARSET),
            'SETTINGS_ADMIN_NAME'                 => htmlentities($arrSettings['coreAdminName'], ENT_QUOTES, CONTREXX_CHARSET),
            'SETTINGS_GLOBAL_TITLE'               => htmlentities($arrSettings['coreGlobalPageTitle'], ENT_QUOTES, CONTREXX_CHARSET),
            'SETTINGS_DOMAIN_URL'                 => htmlentities($arrSettings['domainUrl'], ENT_QUOTES, CONTREXX_CHARSET),
            'SETTINGS_PAGING_LIMIT'               => intval($arrSettings['corePagingLimit']),
            'SETTINGS_SEARCH_RESULT_LENGTH'       => intval($arrSettings['searchDescriptionLength']),
            'SETTINGS_SESSION_LIFETIME'           => intval($arrSettings['sessionLifeTime']),
            'SETTINGS_DNS_SERVER'                 => htmlentities($arrSettings['dnsServer'], ENT_QUOTES, CONTREXX_CHARSET),
            'SETTINGS_IDS_RADIO_ON'               => ($arrSettings['coreIdsStatus'] == 'on') ? 'checked' : '',
            'SETTINGS_IDS_RADIO_OFF'              => ($arrSettings['coreIdsStatus'] == 'off') ? 'checked' : '',
            'SETTINGS_HISTORY_ON'                 => ($arrSettings['contentHistoryStatus'] == 'on') ? 'checked' : '',
            'SETTINGS_HISTORY_OFF'                => ($arrSettings['contentHistoryStatus'] == 'off') ? 'checked' : '',
            'SETTINGS_GOOGLESITEMAP_ON'           => ($arrSettings['googleSitemapStatus'] == 'on') ? 'checked' : '',
            'SETTINGS_GOOGLESITEMAP_OFF'          => ($arrSettings['googleSitemapStatus'] == 'off') ? 'checked' : '',
            'SETTINGS_SYSTEMSTATUS_ON'            => ($arrSettings['systemStatus'] == 'on') ? 'checked' : '',
            'SETTINGS_SYSTEMSTATUS_OFF'           => ($arrSettings['systemStatus'] == 'off') ? 'checked' : '',
            'SETTINGS_SEARCH_VISIBLE_CONTENT_ON'  => ($arrSettings['searchVisibleContentOnly'] == 'on') ? 'checked' : '',
            'SETTINGS_SEARCH_VISIBLE_CONTENT_OFF' => ($arrSettings['searchVisibleContentOnly'] == 'off') ? 'checked' : '',
        ));
    }

    /**
     * Update settings
     *
     * @global  mixed   Database
     * @global  array   Core language
     * @global  array   Configuration
     */
    function updateSettings()
    {
        global $objDatabase, $_CORELANG, $_CONFIG;

        foreach ($_POST['setvalue'] as $intId => $strValue) {
            if (intval($intId) == 43 ||
                intval($intId) == 50 ||
                intval($intId) == 54 ||
                intval($intId) == 55 ||
                intval($intId) == 56) {
                $strValue = ($strValue == 'on') ? 'on' : 'off';
            }

            if (intval($intId) == 53) {
                if (substr($strValue,0,7) == 'http://') {
                    $strValue = substr($strValue,7);
                }
            }
            $objDatabase->Execute('    UPDATE '.DBPREFIX.'settings
                                    SET setvalue="'.htmlspecialchars($strValue, ENT_QUOTES, CONTREXX_CHARSET).'"
                                    WHERE setid='.intval($intId));
        }

        if ($_POST['setvalue']['54'] == 'on') {
            $_CONFIG['googleSitemapStatus'] = 'on';
            $objGoogleSitemap = new GoogleSitemap();
            $objGoogleSitemap->writeFile();
        }

        $this->strOkMessage = $_CORELANG['TXT_SETTINGS_UPDATED'];
    }

    /**
     * Write all settings to the config file
     *
     */
    function writeSettingsFile() {
        global $objDatabase,$_CORELANG;

        if (is_writable($this->strSettingsFile)) {
            $handleFile = fopen($this->strSettingsFile,'w+');
            if ($handleFile) {
            //Header & Footer
                $strHeader    = "<?php\n";
                $strHeader .= "/**\n";
                $strHeader .= "* This file is generated by the \"settings\"-menu in your CMS.\n";
                $strHeader .= "* Do not try to edit it manually!\n";
                $strHeader .= "*/\n\n";

                $strFooter = "?>";

            //Get module-names
                $objResult = $objDatabase->Execute('SELECT id,
                                                           name
                                                    FROM '.DBPREFIX.'modules');
                if ($objResult->RecordCount() > 0) {
                    while (!$objResult->EOF) {
                        $arrModules[$objResult->fields['id']] = $objResult->fields['name'];
                        $objResult->MoveNext();
                    }
                }

            //Get values
                $objResult = $objDatabase->Execute('SELECT setname,
                                                           setmodule,
                                                           setvalue
                                                    FROM '.DBPREFIX.'settings
                                                    ORDER BY setmodule ASC,
                                                             setname ASC');
                $intMaxLen = 0;
                if ($objResult->RecordCount() > 0) {
                    while (!$objResult->EOF) {
                        $intMaxLen = (strlen($objResult->fields['setname']) > $intMaxLen) ? strlen($objResult->fields['setname']) : $intMaxLen;
                        $arrValues[$objResult->fields['setmodule']][$objResult->fields['setname']] = $objResult->fields['setvalue'];
                        $objResult->MoveNext();
                    }
                }
                $intMaxLen += strlen('$_CONFIG[\'\']') + 1; //needed for formatted output

            //Write values
                flock($handleFile, LOCK_EX); //set semaphore
                @fwrite($handleFile,$strHeader);

                foreach ($arrValues as $intModule => $arrInner) {
                    @fwrite($handleFile,"/**\n");
                    @fwrite($handleFile,"* -------------------------------------------------------------------------\n");
                    @fwrite($handleFile,"* ".ucfirst($arrModules[$intModule])."\n");
                    @fwrite($handleFile,"* -------------------------------------------------------------------------\n");
                    @fwrite($handleFile,"*/\n");

                    foreach($arrInner as $strName => $strValue) {
                        @fwrite($handleFile,sprintf("%-".$intMaxLen."s",'$_CONFIG[\''.$strName.'\']'));
                        @fwrite($handleFile,"= ");
                        @fwrite($handleFile,(is_numeric($strValue) ? $strValue : '"'.$strValue.'"').";\n");
                    }
                    @fwrite($handleFile,"\n");
                }

                @fwrite($handleFile,$strFooter);
                flock($handleFile, LOCK_UN);

                fclose($handleFile);
            }
        } else {
            $this->strOkMessage = '';
            $this->strErrMessage = $this->strSettingsFile.' '.$_CORELANG['TXT_SETTINGS_ERROR_WRITABLE'];

        }
    }

    function smtp()
    {
    	$this->_objSmtp = new SmtpSettings();

    	if (empty($_REQUEST['tpl'])) {
    		$_REQUEST['tpl'] = '';
    	}

    	switch ($_REQUEST['tpl']) {
    		case 'modify':
				$this->_smtpModify();
				break;

    		case 'delete':
    			$this->_smtpDeleteAccount();
    			$this->_smtpOverview();
    			break;

    		case 'default':
    			$this->_smtpDefaultAccount();
    			$this->_smtpOverview();
    			break;

    		default:
				$this->_smtpOverview();
    	}
    }

    function _smtpDefaultAccount()
    {
    	global $objDatabase, $_CORELANG, $_CONFIG;

    	$id = intval($_GET['id']);
    	if (($arrSmtp = $this->_objSmtp->_getSmtpAccount($id)) || (($id = 0) !== false && $arrSmtp = $this->_objSmtp->getSystemSmtpAccount())) {
    		if ($objDatabase->Execute("UPDATE `".DBPREFIX."settings` SET `setvalue` = '".$id."' WHERE `setname` = 'coreSmtpServer'")) {
    			$_CONFIG['coreSmtpServer'] = $id;
    			require_once(ASCMS_CORE_PATH.'/settings.class.php');
				$objSettings = &new settingsManager();
			    $objSettings->writeSettingsFile();
			    $this->strOkMessage .= sprintf($_CORELANG['TXT_SETTINGS_DEFAULT_SMTP_CHANGED'], htmlentities($arrSmtp['name'], ENT_QUOTES, CONTREXX_CHARSET)).'<br />';
    		} else {
    			$this->strErrMessage .= $_CORELANG['TXT_SETTINGS_CHANGE_DEFAULT_SMTP_FAILED'].'<br />';
    		}
		}
    }

    function _smtpDeleteAccount()
    {
    	global $objDatabase, $_CONFIG, $_CORELANG;

    	$id = intval($_GET['id']);

    	if (($arrSmtp = $this->_objSmtp->_getSmtpAccount($id)) !== false) {
	    	if ($id != $_CONFIG['coreSmtpServer']) {
	    		if ($objDatabase->Execute('DELETE FROM `'.DBPREFIX.'settings_smtp` WHERE `id`='.$id) !== false) {
		    		$this->strOkMessage .= sprintf($_CORELANG['TXT_SETTINGS_SMTP_DELETE_SUCCEED'], htmlentities($arrSmtp['name'], ENT_QUOTES, CONTREXX_CHARSET)).'<br />';
		    	} else {
		    		$this->strErrMessage .= sprintf($_CORELANG['TXT_SETTINGS_SMTP_DELETE_FAILED'], htmlentities($arrSmtp['name'], ENT_QUOTES, CONTREXX_CHARSET)).'<br />';
		    	}
	    	} else {
	    		$this->strErrMessage .= sprintf($_CORELANG['TXT_SETTINGS_COULD_NOT_DELETE_DEAULT_SMTP'], htmlentities($arrSmtp['name'], ENT_QUOTES, CONTREXX_CHARSET)).'<br />';
	    	}
    	}
    }

    function _smtpOverview()
    {
    	global $_CORELANG, $objTemplate, $_CONFIG;

        $objTemplate->addBlockfile('ADMIN_CONTENT', 'settings_smtp', 'settings_smtp.html');
        $this->strPageTitle = $_CORELANG['TXT_SETTINGS_EMAIL_ACCOUNTS'];

        $objTemplate->setVariable(array(
			'TXT_SETTINGS_EMAIL_ACCOUNTS'			=> $_CORELANG['TXT_SETTINGS_EMAIL_ACCOUNTS'],
			'TXT_SETTINGS_ACCOUNT'					=> $_CORELANG['TXT_SETTINGS_ACCOUNT'],
			'TXT_SETTINGS_HOST'						=> $_CORELANG['TXT_SETTINGS_HOST'],
			'TXT_SETTINGS_USERNAME'					=> $_CORELANG['TXT_SETTINGS_USERNAME'],
			'TXT_SETTINGS_STANDARD'					=> $_CORELANG['TXT_SETTINGS_STANDARD'],
			'TXT_SETTINGS_FUNCTIONS'				=> $_CORELANG['TXT_SETTINGS_FUNCTIONS'],
			'TXT_SETTINGS_ADD_NEW_SMTP_ACCOUNT'		=> $_CORELANG['TXT_SETTINGS_ADD_NEW_SMTP_ACCOUNT'],
			'TXT_SETTINGS_CONFIRM_DELETE_ACCOUNT'	=> $_CORELANG['TXT_SETTINGS_CONFIRM_DELETE_ACCOUNT'],
			'TXT_SETTINGS_OPERATION_IRREVERSIBLE'	=> $_CORELANG['TXT_SETTINGS_OPERATION_IRREVERSIBLE']
        ));

        $objTemplate->setGlobalVariable(array(
			'TXT_SETTINGS_MODFIY'					=> $_CORELANG['TXT_SETTINGS_MODFIY'],
			'TXT_SETTINGS_DELETE'					=> $_CORELANG['TXT_SETTINGS_DELETE']
        ));

        $nr = 1;
        foreach ($this->_objSmtp->getSmtpAccounts() as $id => $arrSmtp) {
        	if ($id) {
        		$objTemplate->setVariable(array(
					'SETTINGS_SMTP_ACCOUNT_ID'	=> $id,
					'SETTINGS_SMTP_ACCOUNT_JS'	=> htmlentities(addslashes($arrSmtp['name']), ENT_QUOTES, CONTREXX_CHARSET)
				));
        		$objTemplate->parse('settings_smtp_account_functions');
        	} else {
        		$objTemplate->hideBlock('settings_smtp_account_functions');
        	}
        	$objTemplate->setVariable(array(
				'SETTINGS_ROW_CLASS_ID'		=> $nr++ % 2 + 1,
				'SETTINGS_SMTP_ACCOUNT_ID'	=> $id,
				'SETTINGS_SMTP_ACCOUNT'		=> htmlentities($arrSmtp['name'], ENT_QUOTES, CONTREXX_CHARSET),
				'SETTINGS_SMTP_HOST'		=> !empty($arrSmtp['hostname']) ? htmlentities($arrSmtp['hostname'], ENT_QUOTES, CONTREXX_CHARSET) : '&nbsp;',
				'SETTINGS_SMTP_USERNAME'	=> !empty($arrSmtp['username']) ? htmlentities($arrSmtp['username'], ENT_QUOTES, CONTREXX_CHARSET) : '&nbsp;',
				'SETTINGS_SMTP_DEFAULT'		=> $id == $_CONFIG['coreSmtpServer'] ? 'checked="checked"' : ''
        	));
        	$objTemplate->parse('settings_smtp_accounts');
        }

        $objTemplate->parse('settings_smtp');
    }

    function _smtpModify()
    {
    	global $objTemplate, $_CORELANG;

    	$error = false;
    	$id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

    	if (isset($_POST['settings_smtp_save'])) {
    		$arrSmtp = array(
    			'name'		=> !empty($_POST['settings_smtp_account']) ? contrexx_stripslashes(trim($_POST['settings_smtp_account'])) : '',
    			'hostname'	=> !empty($_POST['settings_smtp_hostname']) ? contrexx_stripslashes(trim($_POST['settings_smtp_hostname'])) : '',
    			'port'		=> !empty($_POST['settings_smtp_port']) ? intval($_POST['settings_smtp_port']) : 25,
    			'username'	=> !empty($_POST['settings_smtp_username']) ? contrexx_stripslashes(trim($_POST['settings_smtp_username'])) : '',
    			'password'	=> !empty($_POST['settings_smtp_password']) ? contrexx_stripslashes($_POST['settings_smtp_password']) : ''
    		);

    		if (!$arrSmtp['port']) {
    			$arrSmtp['port'] = 25;
    		}

    		if (empty($arrSmtp['name'])) {
    			$error = true;
    			$this->strErrMessage .= $_CORELANG['TXT_SETTINGS_EMPTY_ACCOUNT_NAME_TXT'].'<br />';
    		} elseif (!$this->_objSmtp->_isUniqueSmtpAccountName($arrSmtp['name'], $id)) {
    			$error = true;
				$this->strErrMessage .= sprintf($_CORELANG['TXT_SETTINGS_NOT_UNIQUE_SMTP_ACCOUNT_NAME'], htmlentities($arrSmtp['name'])).'<br />';
    		}

    		if (empty($arrSmtp['hostname'])) {
    			$error = true;
    			$this->strErrMessage .= $_CORELANG['TXT_SETTINGS_EMPTY_SMTP_HOST_TXT'].'<br />';
    		}

    		if (!$error) {
	    		if ($id) {
					if ($this->_objSmtp->_updateSmtpAccount($id, $arrSmtp)) {
						$this->strOkMessage .= sprintf($_CORELANG['TXT_SETTINGS_SMTP_ACCOUNT_UPDATE_SUCCEED'], $arrSmtp['name']).'<br />';
						return $this->_smtpOverview();
					} else {
						$this->strErrMessage .= sprintf($_CORELANG['TXT_SETTINGS_SMTP_ACCOUNT_UPDATE_FAILED'], $arrSmtp['name']).'<br />';
					}
	    		} else {
					if ($this->_objSmtp->_addSmtpAccount($arrSmtp)) {
						$this->strOkMessage .= sprintf($_CORELANG['TXT_SETTINGS_SMTP_ACCOUNT_ADD_SUCCEED'], $arrSmtp['name']).'<br />';
						return $this->_smtpOverview();
					} else {
						$this->strErrMessage .= $_CORELANG['TXT_SETTINGS_SMTP_ACCOUNT_ADD_FAILED'].'<br />';
					}
	    		}
    		}
    	} elseif (($arrSmtp = $this->_objSmtp->_getSmtpAccount($id)) === false) {
    		$id = 0;
    		$arrSmtp = array(
    			'name'		=> '',
    			'hostname'	=> '',
    			'port'		=> 25,
    			'username'	=> '',
    			'password'	=> 0
    		);
    	}

    	$objTemplate->addBlockfile('ADMIN_CONTENT', 'settings_smtp_modify', 'settings_smtp_modify.html');
    	$this->strPageTitle = $id ? $_CORELANG['TXT_SETTINGS_MODIFY_SMTP_ACCOUNT'] : $_CORELANG['TXT_SETTINGS_ADD_NEW_SMTP_ACCOUNT'];

    	$objTemplate->setVariable(array(
    		'TXT_SETTINGS_ACCOUNT'					=> $_CORELANG['TXT_SETTINGS_ACCOUNT'],
    		'TXT_SETTINGS_NAME_OF_ACCOUNT'			=> $_CORELANG['TXT_SETTINGS_NAME_OF_ACCOUNT'],
    		'TXT_SETTINGS_SMTP_SERVER'				=> $_CORELANG['TXT_SETTINGS_SMTP_SERVER'],
    		'TXT_SETTINGS_HOST'						=> $_CORELANG['TXT_SETTINGS_HOST'],
    		'TXT_SETTINGS_PORT'						=> $_CORELANG['TXT_SETTINGS_PORT'],
    		'TXT_SETTINGS_AUTHENTICATION'			=> $_CORELANG['TXT_SETTINGS_AUTHENTICATION'],
    		'TXT_SETTINGS_USERNAME'					=> $_CORELANG['TXT_SETTINGS_USERNAME'],
    		'TXT_SETTINGS_PASSWORD'					=> $_CORELANG['TXT_SETTINGS_PASSWORD'],
    		'TXT_SETTINGS_SMTP_AUTHENTICATION_TXT'	=> $_CORELANG['TXT_SETTINGS_SMTP_AUTHENTICATION_TXT'],
    		'TXT_SETTINGS_BACK'						=> $_CORELANG['TXT_SETTINGS_BACK'],
    		'TXT_SETTINGS_SAVE'						=> $_CORELANG['TXT_SETTINGS_SAVE']
    	));

    	$objTemplate->setVariable(array(
    		'SETTINGS_SMTP_TITLE'		=> $id ? $_CORELANG['TXT_SETTINGS_MODIFY_SMTP_ACCOUNT'] : $_CORELANG['TXT_SETTINGS_ADD_NEW_SMTP_ACCOUNT'],
    		'SETTINGS_SMTP_ID'			=> $id,
    		'SETTINGS_SMTP_ACCOUNT'		=> htmlentities($arrSmtp['name'], ENT_QUOTES, CONTREXX_CHARSET),
    		'SETTINGS_SMTP_HOST'		=> htmlentities($arrSmtp['hostname'], ENT_QUOTES, CONTREXX_CHARSET),
    		'SETTINGS_SMTP_PORT'		=> $arrSmtp['port'],
    		'SETTINGS_SMTP_USERNAME'	=> htmlentities($arrSmtp['username'], ENT_QUOTES, CONTREXX_CHARSET),
    		'SETTINGS_SMTP_PASSWORD'	=> str_pad('', $arrSmtp['password'], ' ')
    	));

    	$objTemplate->parse('settings_smtp_modify');
    }
}
?>