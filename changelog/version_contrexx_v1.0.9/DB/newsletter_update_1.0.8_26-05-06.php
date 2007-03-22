<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Newsletter Manager Update for Version 1.0.8</title>
<style type="text/css">
<!--
table {
	border:1px solid #000000;
}

table th,td  {
	border:1px solid #000000;
}

// -->
</style>
</head>
<body>
<?php
/**

	Plazieren Sie diese Datei in das Hauptverzeichniss Ihrer Contrexx Installation
	geben Sie die http Adresse zu Ihrem CMS ein
	und klicken Sie anschliessend auf "Update starten".

*/
if (!@include_once('config/configuration.php')) {
	print "Plazieren Sie diese Datei in das Hauptverzeichniss Ihrer Contrexx Installation.";
} else {
?>
<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
<?php
	if (!isset($_POST['doUpdate'])) {
		print "Domain des upzudatenden Contrexx CMS, z.B. 'www.ihredomain.com' (ohne http:// und zusätzliche Pfade) <br />";
		print '<input size="80" type="text" name="domainURL" value="'.$_SERVER['SERVER_NAME'].'" /> <br />';
		print "<input type=\"submit\" name=\"doUpdate\" value=\"Update ausführen\" />";
	} else {
		require_once ASCMS_CORE_PATH.'/API.php';
		$errorMsg = '';
		$objDatabase = getDatabaseObject($errorMsg);
		$objUpdate = &new Update();
		$objUpdate->doUpdate();
	}
}
class Update
{
	var $_arrModuleRepository = array(
		'subscribe'	=> array(
			'content'		=> '{NEWSLETTER_MESSAGE}\r\n<!-- BEGIN newsletterForm -->\r\n<form name="newsletter" action="?section=newsletter&amp;cmd=subscribe" method="post">\r\n<input type="hidden" name="subscribe" value="exe" />\r\n<table width="100%" border="0" cellpadding="3" cellspacing="0" class="adminlist">\r\n	<tr class="row1">\r\n		<td width="12%"><b>{TXT_NEWSLETTER_EMAIL_ADDRESS}</b></td>\r\n		<td width="88%"><input type="text" name="email" size="40" maxlength="200" value="" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n	     <td width="12%">{TXT_NEWSLETTER_USER_TITLE}</td>\r\n	     <td width="88%">\r\n	<input type="radio" id="female" name="title" value="f" /> <label for="female"> {TXT_NEWSLETTER_FEMALE} </label>	<input type="radio" id="male" name="title" value="m" checked="checked" /> <label for="male">{TXT_NEWSLETTER_MALE} </label>   \r\n             </td>\r\n	</tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_LASTNAME}</td>\r\n		<td width="88%"><input type="text" name="lastname" size="40" maxlength="200" value="" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_FIRSTNAME}</td>\r\n		<td width="88%"><input type="text" name="firstname" size="40" maxlength="200" value="" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_STREET}</td>\r\n		<td width="88%"><input type="text" name="street" size="40" maxlength="200" value="" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_ZIP}</td>\r\n		<td width="88%"><input type="text" name="zip" size="40" maxlength="200" value="" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_CITY}</td>\r\n		<td width="88%"><input type="text" name="city" size="40" maxlength="200" value="" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_COUNTRY}</td>\r\n		<td width="88%"><input type="text" name="country" size="40" maxlength="200" value="" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_PHONE}</td>\r\n		<td width="88%"><input type="text" name="phone" size="40" maxlength="200" value="" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_BIRTHDAY}</td>\r\n		<td width="88%"><input type="text" name="birthday" size="40" maxlength="200" value="" /></td>\r\n	</tr>\r\n	<tr>\r\n		<td width="12%" valign="top"></td>\r\n		<td width="88%">\r\n			{NEWSLETTER_CATEGORIES}\r\n		</td>\r\n	</tr>\r\n	<tr>\r\n		<td width="12%"></td>\r\n		<td width="88%"><input type="submit" value="{TXT_NEWSLETTER_SAVE}" /></td>\r\n	</tr>\r\n</table>\r\n</form>\r\n<!-- END newsletterForm -->',
			'title'	=> 'Newsletter abonnieren',
			'displaystatus'	=> 'off',
			'displayorder'	=> 1
		),
		'unsubscribe'	=> array(
			'content'		=> '{NEWSLETTER_MESSAGE}',
			'title'			=> 'Newsletter abmelden',
			'displaystatus'	=> 'off',
			'displayorder'	=> 1,
		),
		'confirm'	=> array(
			'content'		=> '{NEWSLETTER_MESSAGE}',
			'title'			=> 'Newsletter bestätigen',
			'displaystatus'	=> 'off',
			'displayorder'	=> 1,
		),
		'profile'	=> array(
			'content'		=> '{NEWSLETTER_MESSAGE}\r\n<!-- BEGIN newsletterForm -->\r\n<form name="newsletter" action="?section=newsletter&amp;cmd=profile&code={NEWSLETTER_USER_CODE}" method="post">\r\n<input type="hidden" name="profileupdate" value="exe" />\r\n<input type="hidden" name="code" value="{NEWSLETTER_USER_CODE}" />\r\n<table width="100%" border="0" cellpadding="3" cellspacing="0" class="adminlist">\r\n	<tr class="row1">\r\n		<td width="12%"><b>{TXT_NEWSLETTER_EMAIL_ADDRESS}</b></td>\r\n		<td width="88%"><input type="text" name="email" size="40" maxlength="200" value="{NEWSLETTER_EMAIL_ADDRESS}" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n			<td width="12%">{TXT_USER_TITLE}</td>\r\n			<td width="88%">\r\n			<input type="radio" id="female" name="title" value="f" /> <label for="female"> {TXT_NEWSLETTER_FEMALE} </label>	<input type="radio" id="male" name="title" value="m" checked="checked" /> <label for="male">{TXT_NEWSLETTER_MALE} </label> </td>\r\n        </tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_LASTNAME}</td>\r\n		<td width="88%"><input type="text" name="lastname" size="40" maxlength="200" value="{NEWSLETTER_LASTNAME}" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_FIRSTNAME}</td>\r\n		<td width="88%"><input type="text" name="firstname" size="40" maxlength="200" value="{NEWSLETTER_FIRSTNAME}" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_STREET}</td>\r\n		<td width="88%"><input type="text" name="street" size="40" maxlength="200" value="{NEWSLETTER_STREET}" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_ZIP}</td>\r\n		<td width="88%"><input type="text" name="zip" size="40" maxlength="200" value="{NEWSLETTER_ZIP}" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_CITY}</td>\r\n		<td width="88%"><input type="text" name="city" size="40" maxlength="200" value="{NEWSLETTER_CITY}" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_COUNTRY}</td>\r\n		<td width="88%"><input type="text" name="country" size="40" maxlength="200" value="{NEWSLETTER_COUNTRY}" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_PHONE}</td>\r\n		<td width="88%"><input type="text" name="phone" size="40" maxlength="200" value="{NEWSLETTER_PHONE}" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_BIRTHDAY}</td>\r\n		<td width="88%"><input type="text" name="birthday" size="40" maxlength="200" value="{NEWSLETTER_BIRTHDAY}" /></td>\r\n	</tr>\r\n	<tr>\r\n		<td width="12%" valign="top"></td>\r\n		<td width="88%">\r\n			{NEWSLETTER_CATEGORIES}\r\n		</td>\r\n	</tr>\r\n	<tr>\r\n		<td width="12%"></td>\r\n		<td width="88%"><input type="submit" value="{TXT_NEWSLETTER_SAVE}" /></td>\r\n	</tr>\r\n</table>\r\n</form>\r\n<!-- END newsletterForm -->\r\n<!-- BEGIN newsletterFailed -->\r\n{NEWSLETTER_FAILED_AUTH}\r\n<!-- END newsletterFailed -->',
			'title'			=> 'Newsletter Profil bearbeiten',
			'displaystatus'	=> 'on',
			'displayorder'	=> 2
		)
	);

	function doUpdate() {
		if($this->_updateDomainURL()){
			if ($this->_createNewsletterModule()) {
				print "Das Update wurde erfolgreich ausgeführt!";
			}
		}
	}

	function _updateDomainURL(){
		global $objDatabase;
		if(!empty($_POST['domainURL']) && strpos($_POST['domainURL'], 'http://') === false){
			if(substr($_POST['domainURL'], -1) == '/'){
				$domainURL = substr($_POST['domainURL'], 0, -1);
			}else{
				$domainURL = $_POST['domainURL'];
			}
			$query = "SELECT setname from ".DBPREFIX."settings where setname = 'domainUrl' LIMIT 1";
			$objResult = $objDatabase->Execute($query);
			if($objResult->RecordCount() < 1){
				$query = "INSERT INTO ".DBPREFIX."settings VALUES ('', 'domainUrl', '$domainURL', '1')";
				if($objDatabase->Execute($query) === false){
					$this->_databaseError($query, $objDatabase->ErrorMsg());
				}
			}else{
				$query = "UPDATE ".DBPREFIX."settings set setvalue='$domainURL' WHERE setname = 'domainUrl'";
				if($objDatabase->Execute($query) === false){
					$this->_databaseError($query, $objDatabase->ErrorMsg());
				}
			}
			return true;
		}else{
			 print "Sie müssen den Domainnamen Ihrer CMS Installation angeben. <br />
					z.B. 'www.ihredomain.com', ohne 'http://' oder zusätzliche Pfade <br />
					<a href=\"".$_SERVER['HTTP_REFERER']."\"> zurück </a> ";
			 return false;
		}
	}

	function _databaseError($query, $errorMsg)
	{
		print "Datenbank Fehler bei folgedem SQL Statement:<br />";
		print $query."<br /><br />";
		print "Detailierte Informationen:<br />";
		print $errorMsg."<br /><br />";
		print "Versuchen Sie das Update erneut auszuführen!<br />";
		return false;
	}

	function _createNewsletterModule() {
		global $objDatabase;

		$arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_newsletter');
		if ($arrColumns === false) {
			print "Konnte die Spalten der Tabelle ".DBPREFIX."module_newsletter nicht ermitteln";
			return false;
		}

		// update attribute contrexx_module_newsletter.date_sent
		if ($arrColumns['DATE_SENT']->type == 'date') {
			$arrNewsletters = array();

			$query = "SELECT id, date_sent FROM ".DBPREFIX."module_newsletter";
			$objResult = $objDatabase->Execute($query);

			if ($objResult !== false) {
				while (!$objResult->EOF) {
					$arrNewsletters[$objResult->fields['id']] = mktime(0, 0, 0, substr($objResult->fields['date_sent'], 5, 2), substr($objResult->fields['date_sent'], 8, 2), substr($objResult->fields['date_sent'], 0, 4));
					$objResult->MoveNext();
				}

				$query = "ALTER TABLE ".DBPREFIX."module_newsletter CHANGE `date_sent` `date_sent` INT( 14 ) UNSIGNED NOT NULL DEFAULT '0'";
				if ($objDatabase->Execute($query) !== false) {
					if (count($arrNewsletters) > 0) {
						foreach ($arrNewsletters as $newsletterId => $dateSent) {
							$query = "UPDATE ".DBPREFIX."module_newsletter SET `date_sent`=".$dateSent." WHERE id=".$newsletterId;
							if ($objDatabase->Execute($query) === false) {
								return $this->_databaseError($query, $objDatabase->ErrorMsg());
							}
						}
					}
				}
			}
		}

		// update attribute contrexx_module_newsletter.date_create
		if ($arrColumns['DATE_CREATE']->type == 'date') {
			$arrNewsletters = array();

			$query = "SELECT id, date_create FROM ".DBPREFIX."module_newsletter";
			$objResult = $objDatabase->Execute($query);

			if ($objResult !== false) {
				while (!$objResult->EOF) {
					$arrNewsletters[$objResult->fields['id']] = mktime(0, 0, 0, substr($objResult->fields['date_create'], 5, 2), substr($objResult->fields['date_create'], 8, 2), substr($objResult->fields['date_create'], 0, 4));
					$objResult->MoveNext();
				}

				$query = "ALTER TABLE ".DBPREFIX."module_newsletter CHANGE `date_create` `date_create` INT( 14 ) UNSIGNED NOT NULL DEFAULT '0'";
				if ($objDatabase->Execute($query) !== false) {
					if (count($arrNewsletters) > 0) {
						foreach ($arrNewsletters as $newsletterId => $dateCreate) {
							$query = "UPDATE ".DBPREFIX."module_newsletter SET `date_create`=".$dateCreate." WHERE id=".$newsletterId;
							if ($objDatabase->Execute($query) === false) {
								return $this->_databaseError($query, $objDatabase->ErrorMsg());
							}
						}
					}
				}
			}
		}

		$arrTables = $objDatabase->MetaTables();
		if ($arrTables !== false) {
			if (in_array(DBPREFIX."module_newsletter_config", $arrTables)) {
				if (!in_array(DBPREFIX."module_newsletter_settings", $arrTables)) {
					$query ="CREATE TABLE ".DBPREFIX."module_newsletter_settings (`setid` smallint(6) NOT NULL auto_increment,`setname` varchar(250) NOT NULL default '',`setvalue` text NOT NULL,`status` tinyint(1) NOT NULL default '0',PRIMARY KEY  (`setid`)) TYPE=MyISAM";
					if ($objDatabase->Execute($query) === false) {
						return $this->_databaseError($query, $objDatabase->ErrorMsg());
					}
				}

				$query = "SELECT sender_email, sender_name, return_path, mails_per_run FROM ".DBPREFIX."module_newsletter_config";
				$objConfig = $objDatabase->Execute($query);
				if ($objConfig !== false) {
					$arrConfig = array(
						'sender_mail'		=> $objConfig->fields['sender_email'],
						'sender_name'		=> $objConfig->fields['sender_name'],
						'reply_mail'		=> $objConfig->fields['return_path'],
						'mails_per_run'		=> $objConfig->fields['mails_per_run'],
						'text_break_after'	=> 100,
						'test_mail'			=> '',
//						'bcc_mail'			=> '',
						'overview_entries_limit'	=> '10'
					);
				} else {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}

				foreach ($arrConfig as $property => $value) {
					$objConfig = $objDatabase->SelectLimit("SELECT setid FROM ".DBPREFIX."module_newsletter_settings WHERE setname='".$property."'", 1);
					if ($objConfig !== false && $objConfig->RecordCount() == 0) {
						$query = "INSERT INTO ".DBPREFIX."module_newsletter_settings (`setname`, `setvalue`, `status`) VALUES ('".$property."', '".$value."', 1)";
						if ($objDatabase->Execute($query) === false) {
							return $this->_databaseError($query, $objDatabase->ErrorMsg());
						}
					} else {
						return $this->_databaseError("SELECT setid FROM ".DBPREFIX."module_newsletter_settings WHERE setname='".$property."'", $objConfig->ErrorMsg());
					}
				}

				$query = "DROP TABLE ".DBPREFIX."module_newsletter_config";
				if ($objDatabase->Execute($query) === false) {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		}

		$arrColumns = $objDatabase->MetaColumns(DBPREFIX."module_newsletter");
		if ($arrColumns !== false) {
			if (strpos($arrColumns['ATTACHMENT']->type, 'set') !== false) {
				$query = "ALTER TABLE ".DBPREFIX."module_newsletter CHANGE `attachment` `attachment` ENUM( '0', '1' ) NOT NULL DEFAULT '0'";
				if ($objDatabase->Execute($query) === false) {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}
			}

			if (strpos($arrColumns['FORMAT']->type, 'set') !== false) {
				$query = "ALTER TABLE ".DBPREFIX."module_newsletter CHANGE `format` `format` ENUM( 'text', 'html', 'html/text' ) NOT NULL DEFAULT 'text'";
				if ($objDatabase->Execute($query) === false) {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		} else {
			return $this->_databaseError('', "konnte die spalten typen von ".DBPREFIX."module_newsletter nicht ermitteln!");
		}

		$arrColumns = $objDatabase->MetaColumns(DBPREFIX."module_newsletter_user");
		if ($arrColumns !== false) {
			if (!array_key_exists('TITLE', $arrColumns)) {
				$query = "ALTER TABLE ".DBPREFIX."module_newsletter_user ADD `title` ENUM( 'm', 'f', '') NOT NULL AFTER `email`";
				if ($objDatabase->Execute($query) === false) {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}
			}else{
				$query = "ALTER TABLE ".DBPREFIX."module_newsletter_user CHANGE `title` `title` ENUM( 'm', 'f', '' ) NOT NULL DEFAULT ''";
				if ($objDatabase->Execute($query) === false) {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}
			}

			// update attribute contrexx_module_user.emaildate
			if ($arrColumns['EMAILDATE']->type == 'date') {
				$arrUsers = array();

				$query = "SELECT id, emaildate FROM ".DBPREFIX."module_newsletter_user";
				$objResult = $objDatabase->Execute($query);

				if ($objResult !== false) {
					while (!$objResult->EOF) {
						$arrUsers[$objResult->fields['id']] = mktime(0, 0, 0, substr($objResult->fields['emaildate'], 5, 2), substr($objResult->fields['emaildate'], 8, 2), substr($objResult->fields['emaildate'], 0, 4));
						$objResult->MoveNext();
					}

					$query = "ALTER TABLE ".DBPREFIX."module_newsletter_user CHANGE `emaildate` `emaildate` INT( 14 ) UNSIGNED NOT NULL DEFAULT '0'";
					if ($objDatabase->Execute($query) !== false) {
						if (count($arrUsers) > 0) {
							foreach ($arrUsers as $userId => $registredDate) {
								$query = "UPDATE ".DBPREFIX."module_newsletter_user SET `emaildate`=".$registredDate." WHERE id=".$userId;
								if ($objDatabase->Execute($query) === false) {
									return $this->_databaseError($query, $objDatabase->ErrorMsg());
								}
							}
						}
					}
				}
			}
		} else {
			return $this->_databaseError('',"Konnte die Spalten der Tabelle ".DBPREFIX."module_newsletter_user nicht ermitteln!");
		}

		if (!in_array(DBPREFIX."module_newsletter_confirm_mail", $arrTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_newsletter_confirm_mail (
				`id` INT( 1 ) NOT NULL AUTO_INCREMENT ,
				`title` VARCHAR( 255 ) NOT NULL ,
				`content` LONGTEXT NOT NULL ,
				PRIMARY KEY ( `id` )
				)";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		$query = "SELECT id,title,content FROM ".DBPREFIX."module_newsletter_confirm_mail WHERE id='1'";
		$objCheckConfim = $objDatabase->SelectLimit($query,1);
		if($objCheckConfim !== false && $objCheckConfim->RecordCount() == 0){
			//insert confirm mail
			$query = "INSERT INTO `".DBPREFIX."module_newsletter_confirm_mail` ( `id` , `title` , `content` ) VALUES (1, '[[url]] Newsletteraktivierung', 'Guten Tag [[title]] [[lastname]] Ihre E-Mail Adresse wurde erfolgreich in unserer Newsletter Datenbank abgespeichert. Um Ihre E-Mail Adresse zu aktivieren, benutzen Sie bitte folgenden Link: [[code]] Mit freundlichen Grüssen [[url]] - Team Automatisch generierte Nachricht [[date]]')";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		$query = "SELECT id,title,content FROM ".DBPREFIX."module_newsletter_confirm_mail WHERE id='2'";
		$objCheckConfim = $objDatabase->SelectLimit($query,1);
		if($objCheckConfim !== false && $objCheckConfim->RecordCount() == 0){
			//insert confirm mail
			$query = "INSERT INTO `".DBPREFIX."module_newsletter_confirm_mail` ( `id` , `title` , `content` ) VALUES (2, '[[url]] Newsletteraktivierung erfolgreich', 'Guten Tag [[title]] [[lastname]] Ihre E-Mail Adresse wurde erfolgreich in unsererm Newsletter-System aktiviert. Sie werden nun in Zukunft unsere Newsletter erhalten Mit freundlichen Grüssen [[url]] - Team Automatisch generierte Nachricht [[date]]')";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}


		//replace for confirm mail
		$query = "SELECT id,title,content FROM ".DBPREFIX."module_newsletter_confirm_mail WHERE id='1'";
		$objResult = $objDatabase->Execute($query);
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				$id 		= $objResult->fields['id'];
				$subject 	= $objResult->fields['title'];
				$content 	= $objResult->fields['content'];

				//with spacer
				$array_1 = array('<-- title -->', '<-- firstname -->', '<-- lastname -->', '<-- code -->', '<-- url -->', '<-- date -->');
				$array_2 = array('[[title]]', '[[firstname]]', '[[lastname]]', '[[code]]', '[[url]]', '[[date]]');
				$mailTitle = str_replace($array_1, $array_2, $subject);
				$mailContent = str_replace($array_1, $array_2, $content);

				//without spacer
				$array_3 = array('<--title-->', '<--firstname-->', '<--lastname-->', '<--code-->', '<--url-->', '<--date-->');
				$array_4 = array('[[title]]', '[[firstname]]', '[[lastname]]', '[[code]]', '[[url]]', '[[date]]');
				$mailTitle = str_replace($array_3, $array_4, $mailTitle);
				$mailContent = str_replace($array_3, $array_4, $mailContent);

				//update
				$queryUpdate = "UPDATE ".DBPREFIX."module_newsletter_confirm_mail SET title='".$mailTitle."', content='".$mailContent."' where id='".$id."'";

				if ($objDatabase->Execute($queryUpdate) === false) {
					return $this->_databaseError($queryUpdate, $objResultUpdate->ErrorMsg());
				}

				$objResult->MoveNext();
			}
		}


		//replace for templates
		$query = "SELECT id,name,description,html,text FROM ".DBPREFIX."module_newsletter_template";
		$objResult = $objDatabase->Execute($query );
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				$id 		= $objResult->fields['id'];
				$html 		= $objResult->fields['html'];
				$text 		= $objResult->fields['text'];
				$name 		= $objResult->fields['name'];

				//with spacer
				$array_1 = array('<-- subject -->', '<-- email -->', '<-- title -->', '<-- lastname -->', '<-- firstname -->', '<-- street -->', '<-- zip -->', '<-- city -->', '<-- country -->', '<-- phone -->', '<-- birthday -->', '<-- content -->', '<-- profile_setup -->', '<-- unsubscribe -->', '<-- date -->');
				$array_2 = array('[[subject]]', '[[email]]', '[[title]]', '[[lastname]]', '[[firstname]]', '[[street]]', '[[zip]]', '[[city]]', '[[country]]', '[[phone]]', '[[birthday]]', '[[content]]', '[[profile_setup]]', '[[unsubscribe]]', '[[date]]');
				$newHtml = str_replace($array_1, $array_2, $html);
				$newText = str_replace($array_1, $array_2, $text);

				//without spacer
				$array_3 = array('<--subject-->', '<--email-->', '<--title-->', '<--lastname-->', '<--firstname-->', '<--street-->', '<--zip-->', '<--city-->', '<--country-->', '<--phone-->', '<--birthday-->', '<--content-->', '<--profile_setup-->', '<--unsubscribe-->', '<--date-->');
				$array_4 = array('[[subject]]', '[[email]]', '[[title]]', '[[lastname]]', '[[firstname]]', '[[street]]', '[[zip]]', '[[city]]', '[[country]]', '[[phone]]', '[[birthday]]', '[[content]]', '[[profile_setup]]', '[[unsubscribe]]', '[[date]]');
				$newHtml = str_replace($array_3, $array_4, $newHtml);
				$newText = str_replace($array_3, $array_4, $newText);


				//update
				$queryUpdate = "UPDATE ".DBPREFIX."module_newsletter_template SET html='".$newHtml."', text='".$newText."' where id='".$id."'";

				if ($objDatabase->Execute($queryUpdate) === false) {
					return $this->_databaseError($queryUpdate, $objResultUpdate->ErrorMsg());
				}

				$objResult->MoveNext();
			}
		}

		//replace for templates
		$query = "SELECT id,subject,content,content_text FROM ".DBPREFIX."module_newsletter";
		$objResult = $objDatabase->Execute($query );
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				$id 		= $objResult->fields['id'];
				$html 		= $objResult->fields['content'];
				$text 		= $objResult->fields['content_text'];
				$subject 	= $objResult->fields['subject'];

				//with spacer
				$array_1 = array('<-- email -->', '<-- title -->', '<-- lastname -->', '<-- firstname -->', '<-- street -->', '<-- zip -->', '<-- city -->', '<-- country -->', '<-- phone -->', '<-- birthday -->', '<-- content -->', '<-- profile_setup -->', '<-- unsubscribe -->', '<-- date -->');
				$array_2 = array('[[email]]', '[[title]]', '[[lastname]]', '[[firstname]]', '[[street]]', '[[zip]]', '[[city]]', '[[country]]', '[[phone]]', '[[birthday]]', '[[content]]', '[[profile_setup]]', '[[unsubscribe]]', '[[date]]');
				$newHtml = str_replace($array_1, $array_2, $html);
				$newText = str_replace($array_1, $array_2, $text);

				//without spacer
				$array_3 = array('<--email-->', '<--title-->', '<--lastname-->', '<--firstname-->', '<--street-->', '<--zip-->', '<--city-->', '<--country-->', '<--phone-->', '<--birthday-->', '<--content-->', '<--profile_setup-->', '<--unsubscribe-->', '<--date-->');
				$array_4 = array('[[email]]', '[[title]]', '[[lastname]]', '[[firstname]]', '[[street]]', '[[zip]]', '[[city]]', '[[country]]', '[[phone]]', '[[birthday]]', '[[content]]', '[[profile_setup]]', '[[unsubscribe]]', '[[date]]');
				$newHtml = str_replace($array_3, $array_4, $newHtml);
				$newText = str_replace($array_3, $array_4, $newText);

				//with spacer and htmlspecialchars
				$array_1 = array('&lt;-- subject --&gt;', '&lt;-- email --&gt;', '&lt;-- title --&gt;', '&lt;-- lastname --&gt;', '&lt;-- firstname --&gt;', '&lt;-- street --&gt;', '&lt;-- zip --&gt;', '&lt;-- city --&gt;', '&lt;-- country --&gt;', '&lt;-- phone --&gt;', '&lt;-- birthday --&gt;', '&lt;-- content --&gt;', '&lt;-- profile_setup --&gt;', '&lt;-- unsubscribe --&gt;', '&lt;-- date --&gt;');
				$array_2 = array('[[subject]]', '[[email]]', '[[title]]', '[[lastname]]', '[[firstname]]', '[[street]]', '[[zip]]', '[[city]]', '[[country]]', '[[phone]]', '[[birthday]]', '[[content]]', '[[profile_setup]]', '[[unsubscribe]]', '[[date]]');
				$newHtml = str_replace($array_1, $array_2, $newHtml);
				$newText = str_replace($array_1, $array_2, $newText);

				//without spacer and htmlspecialchars
				$array_3 = array('&lt;--subject--&gt;', '&lt;--email--&gt;', '&lt;--title--&gt;', '&lt;--lastname--&gt;', '&lt;--firstname--&gt;', '&lt;--street--&gt;', '&lt;--zip--&gt;', '&lt;--city--&gt;', '&lt;--country--&gt;', '&lt;--phone--&gt;', '&lt;--birthday--&gt;', '&lt;--content--&gt;', '&lt;--profile_setup--&gt;', '&lt;--unsubscribe--&gt;', '&lt;--date--&gt;');
				$array_4 = array('[[subject]]', '[[email]]', '[[title]]', '[[lastname]]', '[[firstname]]', '[[street]]', '[[zip]]', '[[city]]', '[[country]]', '[[phone]]', '[[birthday]]', '[[content]]', '[[profile_setup]]', '[[unsubscribe]]', '[[date]]');
				$newHtml = str_replace($array_3, $array_4, $newHtml);
				$newText = str_replace($array_3, $array_4, $newText);

				//update
				$queryUpdate = "UPDATE ".DBPREFIX."module_newsletter SET content='".$newHtml."', content_text='".$newText."' where id='".$id."'";


				if ($objDatabase->Execute($queryUpdate) === false) {
					return $this->_databaseError($queryUpdate, $objResultUpdate->ErrorMsg());
				}

				$objResult->MoveNext();
			}
		}

		// add required attribute to table contrexx_module_newsletter_template
		$arrColumns = $objDatabase->MetaColumns(DBPREFIX."module_newsletter_template");
		if ($arrColumns !== false) {
			if (!isset($arrColumns['REQUIRED'])) {
				$query = "ALTER TABLE ".DBPREFIX."module_newsletter_template ADD `required` INT( 1 ) NOT NULL DEFAULT '0'";
				if ($objDatabase->Execute($query) === false) {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		} else {
			return $this->_databaseError($query, "Konnte die Spalten der Datenbanktabelle ".DBPREFIX."module_newsletter_template nicht ermitteln!");
		}

		//template update table
		$query = "SELECT id FROM ".DBPREFIX."module_newsletter_template WHERE required=1";
		$objTemplates = $objDatabase->SelectLimit($query, 1);
		if ($objTemplates !== false) {
			if ($objTemplates->RecordCount() == 0) {
				$query = "SELECT id FROM ".DBPREFIX."module_newsletter_template";
				$objTemplates = $objDatabase->SelectLimit($query, 1);
				if ($objTemplates !== false) {
					if ($objTemplates->RecordCount == 0) {
						$query = "INSERT INTO ".DBPREFIX."module_newsletter_template (`name`, `description`, `html`, `text`, `required`) VALUES ('Standard', 'Standard Template', '<html><head><title>[[subject]]</title></head><body>[[content]]<br /><br />[[profile_setup]][[unsubscribe]]</body></html>', '[[content]][[profile_setup]][[unsubscribe]]', 1)";
						if ($objDatabase->Execute($query) === false) {
							return $this->_databaseError($query, $objDatabase->ErrorMsg());
						}
					} else {
						$query = "UPDATE ".DBPREFIX."module_newsletter_template SET `required`=1 WHERE id.".$objTemplates->fields['id'];
						if ($objDatabase->Execute($query) === false) {
							return $this->_databaseError($query, $objDatabase->ErrorMsg());
						}
					}
				} else {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}

			}
		} else {
			return $this->_databaseError($query, $objDatabase->ErrorMsg());
		}


		$parentModulePage = array(
			'content'	=> '{NEWSLETTER_MESSAGE}\r\n<!-- BEGIN newsletterForm -->\r\n<form name="newsletter" action="?section=newsletter&amp;cmd=subscribe" method="post">\r\n<input type="hidden" name="subscribe" value="exe" />\r\n<table width="100%" border="0" cellpadding="3" cellspacing="0" class="adminlist">\r\n	<tr class="row1">\r\n		<td width="12%"><b>{TXT_NEWSLETTER_EMAIL_ADDRESS}</b></td>\r\n		<td width="88%"><input type="text" name="email" size="40" maxlength="200" value="" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n	     <td width="12%">{TXT_NEWSLETTER_USER_TITLE}</td>\r\n	     <td width="88%">\r\n		<input type="radio" id="female" name="title" value="f" /> <label for="female"> {TXT_NEWSLETTER_FEMALE} </label>	<input type="radio" id="male" name="title" value="m" checked="checked" /> <label for="male">{TXT_NEWSLETTER_MALE} </label>\r\n             </td>\r\n	</tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_LASTNAME}</td>\r\n		<td width="88%"><input type="text" name="lastname" size="40" maxlength="200" value="" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_FIRSTNAME}</td>\r\n		<td width="88%"><input type="text" name="firstname" size="40" maxlength="200" value="" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_STREET}</td>\r\n		<td width="88%"><input type="text" name="street" size="40" maxlength="200" value="" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_ZIP}</td>\r\n		<td width="88%"><input type="text" name="zip" size="40" maxlength="200" value="" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_CITY}</td>\r\n		<td width="88%"><input type="text" name="city" size="40" maxlength="200" value="" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_COUNTRY}</td>\r\n		<td width="88%"><input type="text" name="country" size="40" maxlength="200" value="" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_PHONE}</td>\r\n		<td width="88%"><input type="text" name="phone" size="40" maxlength="200" value="" /></td>\r\n	</tr>\r\n	<tr class="row1">\r\n		<td width="12%">{TXT_NEWSLETTER_BIRTHDAY}</td>\r\n		<td width="88%"><input type="text" name="birthday" size="40" maxlength="200" value="" /></td>\r\n	</tr>\r\n	<tr>\r\n		<td width="12%" valign="top"></td>\r\n		<td width="88%">\r\n			{NEWSLETTER_CATEGORIES}\r\n		</td>\r\n	</tr>\r\n	<tr>\r\n		<td width="12%"></td>\r\n		<td width="88%"><input type="submit" value="{TXT_NEWSLETTER_SAVE}" /></td>\r\n	</tr>\r\n</table>\r\n</form>\r\n<!-- END newsletterForm -->',
			'title'	=> 'Newsletter'
		);

		$objRepository = $objDatabase->Execute("SELECT id, lang FROM ".DBPREFIX."module_repository WHERE moduleid=4 AND cmd=''");
		if ($objRepository !== false) {
			if ($objRepository->RecordCount() == 0) {
				$query = "INSERT INTO ".DBPREFIX."module_repository VALUES (
					`moduleid`,
					`content`,
					`title`,
					`expertmode`,
					`username`,
					`displayorder`,
					`lang`
					) VALUES (
					4
					'".$parentModulePage['content']."',
					'".$parentModulePage['title']."',
					'y',
					'system',
					1,
					1)";
				if ($objDatabase->Execute($query) === false) {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}

				$status = $this->_updateSubNewsletterRepositoryPages(1, $objDatabase->Insert_ID());
				if ($status !== true) {
					return $status;
				}
			} else {
				while (!$objRepository->EOF) {
					$query = "UPDATE ".DBPREFIX."module_repository SET `content`='".$parentModulePage['content']."' WHERE id=".$objRepository->fields['id'];
					if ($objDatabase->Execute($query) === false) {
						return $this->_databaseError($query, $objDatabase->ErrorMsg());
					}

					$status = $this->_updateSubNewsletterRepositoryPages($objRepository->fields['lang'], $objRepository->fields['id']);
					if ($status !== true) {
						return $status;
					}

					$objRepository->MoveNext();
				}
			}
		}

		return true;
	}

	function _updateSubNewsletterRepositoryPages($langId, $parId)
	{
		global $objDatabase;

		foreach ($this->_arrModuleRepository as $cmd => $arrPage) {
			$query = "SELECT id FROM ".DBPREFIX."module_repository WHERE moduleid=4 AND cmd='".$cmd."' AND lang=".$langId;
			$objPage = $objDatabase->SelectLimit($query, 1);
			if ($objPage !== false) {
				if ($objPage->RecordCount() == 1) {
					$query = "UPDATE ".DBPREFIX."module_repository SET `content`='".$arrPage['content']."' WHERE id=".$objPage->fields['id'];
					if ($objDatabase->Execute($query) === false) {
						return $this->_databaseError($query, $objDatabase->ErrorMsg());
					}
				} else {
					$query = "INSERT INTO ".DBPREFIX."module_repository (`moduleid`, `content`, `title`, `cmd`, `expertmode`, `parid`, `displaystatus`, `username`, `displayorder`, `lang`
						) VALUES (
						4,
						'".$arrPage['content']."',
						'".$arrPage['title']."',
						'".$cmd."',
						'y',
						".$parId.",
						'".$arrPage['displaystatus']."',
						'system',
						".$arrPage['displayorder'].",
						".$langId."
						)";
					if ($objDatabase->Execute($query) === false) {
						return $this->_databaseError($query, $objDatabase->ErrorMsg());
					}
				}
			} else {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		return true;
	}
}
?>
</form>
</body>
</html>


