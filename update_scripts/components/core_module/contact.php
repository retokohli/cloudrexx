<?php
function _contactUpdate()
{
	global $objDatabase;

	$arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."module_contact_form");
	if (!is_array($arrColumns)) {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_contact_form konnte nicht ermittelt werden!";
		return false;
	}

	if (!in_array("subject", $arrColumns)) {
		$query = "ALTER TABLE ".DBPREFIX."module_contact_form ADD `subject` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `mails`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	} else {
		$query = "ALTER TABLE ".DBPREFIX."module_contact_form CHANGE `subject` `subject` VARCHAR( 255 ) NOT NULL DEFAULT ''";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array("text", $arrColumns)) {
		$query = "ALTER TABLE ".DBPREFIX."module_contact_form ADD `text` TEXT NOT NULL AFTER `subject`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array("feedback", $arrColumns)) {
		$query = "ALTER TABLE ".DBPREFIX."module_contact_form ADD `feedback` TEXT NOT NULL AFTER `text`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array("langId", $arrColumns) || (isset($_SESSION['update']['setLangId']) && $_SESSION['update']['setLangId'])) 	{
		$query = "SELECT id, name, `charset` FROM ".DBPREFIX."languages WHERE frontend=1";
		$objResult = $objDatabase->Execute($query);
		if ($objResult !== false) {
			if ($objResult->RecordCount() > 1) {
				$arrLanguages = array();
				while (!$objResult->EOF) {
					$arrLanguages[$objResult->fields['id']] = array(
						'name'	=> $objResult->fields['name'],
						'charset'	=> $objResult->fields['charset']
					);
					$objResult->MoveNext();
				}

				$query = "SELECT id, name FROM ".DBPREFIX."module_contact_form";
				$objResult = $objDatabase->Execute($query);
				if ($objResult !== false) {
					$arrContactForms = array();
					while (!$objResult->EOF) {
						if (isset($_POST['contactForm'])) {
							$arrContactForms[$objResult->fields['id']] = intval($_POST['contactForm'][$objResult->fields['id']]);
						} else {
							$arrContactForms[$objResult->fields['id']] = $objResult->fields['name'];
						}
						$objResult->MoveNext();
					}

					if (!isset($_POST['contactForm'])) {
						print "Wählen Sie für ".((count($arrContactForms)>1) ? "jedes" : "das folgende")." Kontaktformular die Frontend Sprache aus:<br /><br />\n";
						print "<table cellpadding='2' cellspacing='0' border='0'>\n";
						print "<tbody>\n";
						print "<tr>\n";
						print "<th>ID</th><th>Name</th><th>Sprache</th>\n";
						print "</tr>\n";

						foreach ($arrContactForms as $id => $name) {
							print "<tr>\n";
							print "<td>".$id."</td>\n";
							print "<td>".$name."</td>\n";
							print "<td>\n<select name='contactForm[".$id."]' size='1'>\n";
							foreach ($arrLanguages as $languageId => $arrLanguage) {
								print "<option value='".$languageId."'>".$arrLanguage['name']." (".$arrLanguage['charset'].")</option>\n";
							}
							print "</select>\n</td>\n";
							print "</tr>\n";
						}

						print "</tbody>\n";
						print "</table>\n";
						print "<br />\n";
						print "<input type=\"submit\" name=\"doUpdate\" value=\"Kontaktformular".((count($arrContactForms)>1) ? "e" : "")." aktualisieren\" />";

						return false;
					}
				} else {
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			} elseif ($objResult->RecordCount() == 1) {
				$langId = $objResult->fields['id'];
			} else {
				$langId = 1;
			}

			if (isset($langId) || isset($_POST['contactForm'])) {
				if (!in_array("langId", $arrColumns)) {
					$query = "ALTER TABLE ".DBPREFIX."module_contact_form ADD `langId` TINYINT( 2 ) UNSIGNED DEFAULT '1' NOT NULL AFTER `feedback`";
					if ($objDatabase->Execute($query) === false) {
						return _databaseError($query, $objDatabase->ErrorMsg());
					}
				}

				if (isset($langId)) {
					$query = "UPDATE ".DBPREFIX."module_contact_form SET `langId`=".$langId;
					if ($objDatabase->Execute($query) === false) {
						$_SESSION['update']['setLangId'] = true;
						return _databaseError($query, $objDatabase->ErrorMsg());
					}
				} else {
					foreach ($arrContactForms as $id => $langId) {
						$query = "UPDATE ".DBPREFIX."module_contact_form SET `langId`=".$langId." WHERE id=".$id;
						if ($objDatabase->Execute($query) === false) {
							$_SESSION['update']['setLangId'] = true;
							return _databaseError($query, $objDatabase->ErrorMsg());
						}
					}
				}
				if (isset($_SESSION['update']['setLangId']) && $_SESSION['update']['setLangId']) {
					$_SESSION['update']['setLangId'] = false;
				}
			}
		} else {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array("showForm", $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_contact_form` ADD `showForm` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `feedback`"	;
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$arrColumns = null;
	$arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."module_contact_form_field");
	if (!is_array($arrColumns)) {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_contact_form_field konnte nicht ermittelt werden!";
		return false;
	}

	if (!in_array("is_required", $arrColumns)) {
		$query = "ALTER TABLE ".DBPREFIX."module_contact_form_field ADD `is_required` SET( '0', '1' ) DEFAULT '0' NOT NULL AFTER `attributes`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array("check_type", $arrColumns)) {
		$query = "ALTER TABLE ".DBPREFIX."module_contact_form_field ADD `check_type` INT( 3 ) DEFAULT '1' NOT NULL AFTER `is_required`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$query = "ALTER TABLE `".DBPREFIX."module_contact_form_field` CHANGE `type` `type` ENUM( 'text', 'label', 'checkbox', 'checkboxGroup', 'date', 'file', 'hidden', 'password', 'radio', 'select', 'textarea' ) NOT NULL DEFAULT 'text'";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$arrContactSettings = array(
		'spamProtectionWordList'	=> 'poker,casino,viagra,sex,porn,pussy,fucking',
		'fieldMetaDate'				=> '0',
		'fieldMetaHost'				=> '0',
		'fieldMetaLang'				=> '0',
		'fieldMetaIP'				=> '0'
	);

	foreach ($arrContactSettings as $setname => $setvalue) {
		$query = "SELECT setid FROM ".DBPREFIX."module_contact_settings WHERE setname='".$setname."'";
		$objResult = $objDatabase->SelectLimit($query, 1);

		if ($objResult !== false) {
			if ($objResult->RecordCount() == 0) {
				$query = "INSERT INTO ".DBPREFIX."module_contact_settings ( `setid` , `setname` , `setvalue` , `status` ) VALUES ('', '".$setname."', '".$setvalue."', '1')";
				if ($objDatabase->Execute($query) === false) {
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		} else {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}

	}

	return true;
}
?>