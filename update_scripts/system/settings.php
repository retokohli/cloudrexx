<?php
function _updateSettings()
{
	global $objDatabase;

	$setVars = false;

	$arrSettings = array(
		5	=> array(
			'setname'	=> 'spamKeywords',
			'setvalue'	=> 'sex, viagra',
			'setmodule'	=> 1
		),
		49	=> array(
			'setname'	=> 'directoryHomeContent',
			'setvalue'	=> '0',
			'setmodule'	=> 12
		),
		50	=> array(
			'setname'	=> 'cacheEnabled',
			'setvalue'	=> 'off',
			'setmodule'	=> 1
		),
		52	=> array(
			'setname'	=> 'cacheExpiration',
			'setvalue'	=> '86400',
			'setmodule'	=> 1
		),
		54	=> array(
			'setname'	=> 'googleSitemapStatus',
			'setvalue'	=> 'off',
			'setmodule'	=> 1
		),
		55	=> array(
			'setname'	=> 'systemStatus',
			'setvalue'	=> 'on',
			'setmodule'	=> 1
		),
		56	=> array(
			'setname'	=> 'searchVisibleContentOnly',
			'setvalue'	=> 'on',
			'setmodule'	=> 1
		)
	);

	// add coreGlobalPageTitle
	if (!empty($_POST['coreGlobalPageTitle'])) {
		$query = "SELECT setid FROM ".DBPREFIX."settings WHERE setname='coreGlobalPageTitle'";
		$objResult = $objDatabase->SelectLimit($query, 1);
		if ($objResult) {
			if ($objResult->RecordCount() == 1) {
				$query = "UPDATE ".DBPREFIX."settings SET setvalue='".contrexx_addslashes($_POST['coreGlobalPageTitle'])."' WHERE setname='coreGlobalPageTitle'";
				if ($objDatabase->Execute($query) === false) {
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			} else {
				$query = "INSERT INTO ".DBPREFIX."settings ( `setid` , `setname` , `setvalue` , `setmodule` ) VALUES (51, 'coreGlobalPageTitle', '".contrexx_addslashes($_POST['coreGlobalPageTitle'])."', '1')";
				if ($objDatabase->Execute($query) === false) {
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		} else {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	} else {
		$query = "SELECT setvalue FROM ".DBPREFIX."settings WHERE setname = 'coreGlobalPageTitle'";
		$objResult = $objDatabase->SelectLimit($query, 1);
		if ($objResult) {
			if ($objResult->RecordCount() == 1) {
				$coreGlobalPageTitle = $objResult->fields['setvalue'];
			} else {
				$coreGlobalPageTitle = 'Contrexx CMS Demo';
			}
		} else {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}

		$setVars = true;
	}

	// add domainUrl
	if (!empty($_POST['domainURL']) && strpos($_POST['domainURL'], 'http://') === false) {
		if (substr($_POST['domainURL'], -1) == '/') {
			$domainURL = substr($_POST['domainURL'], 0, -1);
		} else {
			$domainURL = $_POST['domainURL'];
		}

		$query = "SELECT setid from ".DBPREFIX."settings WHERE setname = 'domainUrl'";
		$objResult = $objDatabase->SelectLimit($query, 1);
		if ($objResult) {
			if ($objResult->RecordCount() == 0) {
				// check if the needed setid is already used
				$query = "SELECT setname FROM ".DBPREFIX."settings WHERE setid=53";
				$objResult = $objDatabase->SelectLimit($query, 1);
				if ($objResult) {
					if ($objResult->RecordCount() == 0) {
						// add domainUrl
						$query = "INSERT INTO ".DBPREFIX."settings ( `setid` , `setname` , `setvalue` , `setmodule` ) VALUES (53, 'domainUrl', '".contrexx_addslashes($domainURL)."', '1')";
						if ($objDatabase->Execute($query) === false) {
							return _databaseError($query, $objDatabase->ErrorMsg());
						}
					} else {
						print "Die Systemkonfiguration domainUrl konnte nicht hinzugefügt werden, da bereits eine andere Systemkonfiguration fälschlicherweise die selbe Konfigurations-ID 53 verwendet!";
						return false;
					}
				} else {
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			} elseif ($objResult->fields['setid'] != 53) {
				$oldDomainUrlSetId = intval($objResult->fields['setid']);

				// check if the needed setid is already used
				$query = "SELECT setname FROM ".DBPREFIX."settings WHERE setid=53";
				$objResult = $objDatabase->SelectLimit($query, 1);
				if ($objResult) {
					if ($objResult->RecordCount() == 0) {
						// delete old domainUrl
						$query = "DELETE FROM ".DBPREFIX."settings WHERE setid=".$oldDomainUrlSetId;
						if ($objDatabase->Execute($query) === false) {
							return _databaseError($query, $objDatabase->ErrorMsg());
						}

						// add domainUrl
						$query = "INSERT INTO ".DBPREFIX."settings ( `setid` , `setname` , `setvalue` , `setmodule` ) VALUES (53, 'domainUrl', '".contrexx_addslashes($domainURL)."', '1')";
						if ($objDatabase->Execute($query) === false) {
							return _databaseError($query, $objDatabase->ErrorMsg());
						}
					} else {
						print "Die Systemkonfiguration domainUrl konnte nicht hinzugefügt werden, da bereits eine andere Systemkonfiguration fälschlicherweise die selbe Konfigurations-ID 53 verwendet!";
						return false;
					}
				} else {
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			} else {
				// update domainUrl
				$query = "UPDATE ".DBPREFIX."settings SET setvalue='".contrexx_addslashes($domainURL)."' WHERE setid = 53";
				if ($objDatabase->Execute($query) === false) {
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		} else {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	} else {
		if (isset($_POST['domainURL'])) {
			$domainURL = $_POST['domainURL'];
		} else {
			$query = "SELECT setvalue FROM ".DBPREFIX."settings WHERE setname = 'domainUrl'";
			$objResult = $objDatabase->SelectLimit($query, 1);
			if ($objResult) {
				if ($objResult->RecordCount() == 1) {
					$domainURL = $objResult->fields['setvalue'];
				} else {
					$domainURL = $_SERVER['SERVER_NAME'];
				}
			} else {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		$setVars = true;
	}

	if ($setVars) {
		print "Definieren Sie im nachfolgenden Textfeld einen globalen Seitentitel für Ihre Webseite. Diesen k&ouml;nnen Sie nach dem Update auch später bei den Grundeinstellungen in der Administrationskonsole ändern.<br />";
		print "Der globale Seitentitel können Sie mit der Variable [[GLOBAL_TITLE]] in Ihren Designs einbinden.<br /><br />";
		print '<input size="80" type="text" name="coreGlobalPageTitle" value="'.htmlentities($coreGlobalPageTitle).'" /><br /><br /><br />';

		print "Geben Sie in das nachfolgende Textfeld Ihre Domain an, auf der diese Contrexx Installation läuft.<br />";
		print "Zum Beispiel 'www.ihredomain.com' (ohne http:// oder zusätzliche Pfade!)<br />";
		print "Bei einem Domainwechsel k&ouml;nnen Sie nach dem Update auch später bei den Grundeinstellungen in der Administrationskonsole die Domain ändern.<br /><br />";
		print '<input size="80" type="text" name="domainURL" value="'.htmlentities($domainURL).'" /><br /><br />';
		print '<input type="submit" name="doUpdate" value="Mit dem Update fortfahren..." />';
		return false;
	}

	foreach ($arrSettings as $setId => $arrSetting) {
		$query = "SELECT setid FROM `".DBPREFIX."settings` WHERE `setname`='".$arrSetting['setname']."'";
		if (($objSettings = $objDatabase->SelectLimit($query, 1)) !== false) {
			if ($objSettings->RecordCount() == 0) {
				$query = "INSERT INTO `".DBPREFIX."settings` ( `setid` , `setname` , `setvalue` , `setmodule` ) VALUES (".$setId.", '".$arrSetting['setname']."', '".$arrSetting['setvalue']."', '".$arrSetting['setmodule']."')";
				if ($objDatabase->Execute($query) === false) {
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			} elseif ($objSettings->fields['setid'] != $setId) {
				print "Die Systemkonfiguration ".$arrSetting['setname']." konnte nicht hinzugefügt werden, da bereits eine andere Systemkonfiguration fälschlicherweise die selbe Konfigurations-ID ".$setId." verwendet!";
				return false;
			}
		} else {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$query = "UPDATE `".DBPREFIX."settings` SET `setmodule`=1 WHERE `setmodule`=0";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}


	// write settings
	require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';
	$objFile =& new File();

	$strFooter = '';
	$arrModules = '';

	if (!is_writable(ASCMS_DOCUMENT_ROOT.'/config/')) {
		$objFile->setChmod(ASCMS_DOCUMENT_ROOT.'/config', ASCMS_PATH_OFFSET.'/config', '/');
	}

	if (!file_exists(ASCMS_DOCUMENT_ROOT.'/config/settings.php')) {
		if (!touch(ASCMS_DOCUMENT_ROOT.'/config/settings.php')) {
			print "Die Konfigurationsdatei ".ASCMS_DOCUMENT_ROOT."/config/settings.php kann nicht erstellt werden!<br />";
			print "Setzen Sie die Zugriffsberechtigungen für das Verzeichnis ".ASCMS_DOCUMENT_ROOT."/config/ auf 777 (Unix) oder vergeben Sie auf dieses Verzeichnis Schreibrechte (Windows) und laden Sie die Seite neu!";
			return false;
		}
	}

	if (!is_writable(ASCMS_DOCUMENT_ROOT.'/config/settings.php')) {
		$objFile->setChmod(ASCMS_DOCUMENT_ROOT.'/config', ASCMS_PATH_OFFSET.'/config', '/settings.php');
	}

	if (is_writable(ASCMS_DOCUMENT_ROOT.'/config/settings.php')) {
		$handleFile = fopen(ASCMS_DOCUMENT_ROOT.'/config/settings.php','w+');
		if ($handleFile) {
			//Header & Footer
			$strHeader	= "<?php\n";
			$strHeader .= "/**\n";
			$strHeader .= "* This file is generated by the \"settings\"-menu in your CMS.\n";
			$strHeader .= "* Do not try to edit it manually!\n";
			$strHeader .= "*/\n\n";

			$strFooter .= "?>";

			//Get module-names
			$objResult = $objDatabase->Execute('SELECT	id, name FROM '.DBPREFIX.'modules');
			if ($objResult->RecordCount() > 0) {
				while (!$objResult->EOF) {
					$arrModules[$objResult->fields['id']] = $objResult->fields['name'];
					$objResult->MoveNext();
				}
			}

			//Get values
			$objResult = $objDatabase->Execute('SELECT		setname,
															setmodule,
															setvalue
												FROM		'.DBPREFIX.'settings
												ORDER BY	setmodule ASC,
															setname ASC
											');
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
		print "Die Konfigurationsdatei ".ASCMS_DOCUMENT_ROOT."/config/settings.php kann nicht geschrieben werden!<br />";
		print "Setzen Sie die Zugriffsberechtigungen für die Datei ".ASCMS_DOCUMENT_ROOT."/config/settings.php auf 777 (Unix) oder vergeben Sie auf diese Datei Schreibrechte (Windows) und laden Sie die Seite neu!";
		return false;
	}

	return true;
}
?>