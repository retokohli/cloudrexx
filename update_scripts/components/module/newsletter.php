<?php
function _newsletterUpdate()
{
	global $objDatabase;

	$arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_newsletter');
	if ($arrColumns === false) {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_newsletter konnte nicht ermittelt werden!";
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
							return _databaseError($query, $objDatabase->ErrorMsg());
						}
					}
				}
			} else {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		} else {
			return _databaseError($query, $objDatabase->ErrorMsg());
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
							return _databaseError($query, $objDatabase->ErrorMsg());
						}
					}
				}
			} else {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		} else {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$arrIndexes = $objDatabase->MetaIndexes(DBPREFIX.'module_newsletter');
	if ($arrIndexes !== false) {
		if (isset($arrIndexes['id'])) {
			$query = "ALTER TABLE `".DBPREFIX."module_newsletter` DROP INDEX `id`";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_newsletter konnte nicht ermittelt werden!";
		return false;
	}

	$arrTables = $objDatabase->MetaTables('TABLES');
	if (!$arrTables) {
		print "Die Struktur der Datenbank konnte nicht ermittelt werden!";
		return false;
	}

	if (in_array(DBPREFIX."module_newsletter_config", $arrTables)) {
		if (!in_array(DBPREFIX."module_newsletter_settings", $arrTables)) {
			$query ="CREATE TABLE ".DBPREFIX."module_newsletter_settings (`setid` smallint(6) NOT NULL auto_increment,`setname` varchar(250) NOT NULL default '',`setvalue` text NOT NULL,`status` tinyint(1) NOT NULL default '0',PRIMARY KEY  (`setid`)) TYPE=MyISAM";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		$query = "SELECT sender_email, sender_name, return_path, mails_per_run FROM ".DBPREFIX."module_newsletter_config";
		$objConfig = $objDatabase->Execute($query);
		if ($objConfig !== false) {
			$arrConfig = array(
				'sender_mail'				=> $objConfig->fields['sender_email'],
				'sender_name'				=> $objConfig->fields['sender_name'],
				'reply_mail'				=> $objConfig->fields['return_path'],
				'mails_per_run'				=> $objConfig->fields['mails_per_run'],
				'text_break_after'			=> 100,
				'test_mail'					=> '',
				'overview_entries_limit'	=> '10'
			);
		} else {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}

		foreach ($arrConfig as $property => $value) {
			$query = "SELECT setid FROM ".DBPREFIX."module_newsletter_settings WHERE setname='".$property."'";
			$objConfig = $objDatabase->SelectLimit($query, 1);
			if ($objConfig !== false) {
			 	if ($objConfig->RecordCount() == 0) {
					$query = "INSERT INTO ".DBPREFIX."module_newsletter_settings (`setname`, `setvalue`, `status`) VALUES ('".$property."', '".contrexx_addslashes($value)."', 1)";
					if ($objDatabase->Execute($query) === false) {
						return _databaseError($query, $objDatabase->ErrorMsg());
					}
			 	}
			} else {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		$query = "DROP TABLE ".DBPREFIX."module_newsletter_config";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$arrColumns = $objDatabase->MetaColumns(DBPREFIX."module_newsletter");
	if ($arrColumns !== false) {
		if (strpos($arrColumns['ATTACHMENT']->type, 'set') !== false) {
			$query = "ALTER TABLE ".DBPREFIX."module_newsletter CHANGE `attachment` `attachment` ENUM( '0', '1' ) NOT NULL DEFAULT '0'";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (strpos($arrColumns['FORMAT']->type, 'set') !== false) {
			$query = "ALTER TABLE ".DBPREFIX."module_newsletter CHANGE `format` `format` ENUM( 'text', 'html', 'html/text' ) NOT NULL DEFAULT 'text'";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_newsletter konnte nicht ermittelt werden!";
		return false;
	}

	$arrColumns = $objDatabase->MetaColumns(DBPREFIX."module_newsletter_user");
	if ($arrColumns !== false) {
		if (!array_key_exists('TITLE', $arrColumns)) {
			$query = "ALTER TABLE ".DBPREFIX."module_newsletter_user ADD `title` ENUM( 'm', 'f' ) NULL AFTER `email`";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		} else {
			$query = "ALTER TABLE ".DBPREFIX."module_newsletter_user CHANGE `title` `title` ENUM( 'm', 'f' ) NULL";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!array_key_exists('COMPANY', $arrColumns)) {
			$query = "ALTER TABLE ".DBPREFIX."module_newsletter_user ADD `company` VARCHAR( 255 )NOT NULL DEFAULT '' AFTER `firstname`";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		} else {
			$query = "ALTER TABLE ".DBPREFIX."module_newsletter_user CHANGE `company` `company` VARCHAR( 255 )NOT NULL DEFAULT ''";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		$query = "ALTER TABLE ".DBPREFIX."module_newsletter_user CHANGE `birthday` `birthday` VARCHAR( 10 )NOT NULL DEFAULT '00-00-0000'";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
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
								return _databaseError($query, $objDatabase->ErrorMsg());
							}
						}
					}
				} else {
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			} else {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_newsletter_user konnte nicht ermittelt werden!";
		return false;
	}

	$arrIndexes = $objDatabase->MetaIndexes(DBPREFIX."module_newsletter_user");
	if ($arrIndexes !== false) {
		if (!isset($arrIndexes['email']['unique'])) {
			$query = "ALTER TABLE `".DBPREFIX."module_newsletter_user` ADD UNIQUE ( `email` )";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (isset($arrIndexes['emailadress'])) {
			$query = "ALTER TABLE `".DBPREFIX."module_newsletter_user` DROP INDEX `emailadress`";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_newsletter_user konnte nicht ermittelt werden!";
		return false;
	}

	if (!in_array(DBPREFIX."module_newsletter_confirm_mail", $arrTables)) {
		$query = "CREATE TABLE ".DBPREFIX."module_newsletter_confirm_mail (
			`id` INT( 1 ) NOT NULL AUTO_INCREMENT ,
			`title` VARCHAR( 255 ) NOT NULL DEFAULT '',
			`content` LONGTEXT NOT NULL ,
			PRIMARY KEY ( `id` )
			)";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	} else {
		$query = "ALTER TABLE `".DBPREFIX."module_newsletter_confirm_mail` CHANGE `title` `title` VARCHAR( 255 ) NOT NULL DEFAULT ''";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$arrIndexes = $objDatabase->MetaIndexes(DBPREFIX."module_newsletter_category");
	if ($arrIndexes !== false) {
		if (isset($arrIndexes['id'])) {
			$query = "ALTER TABLE `".DBPREFIX."module_newsletter_category` DROP INDEX `id`";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!isset($arrIndexes['name'])) {
			$query = "ALTER TABLE `".DBPREFIX."module_newsletter_category` ADD INDEX ( `name` )";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_newsletter_category konnte nicht ermittelt werden!";
		return false;
	}

	$query = "SELECT id,title,content FROM ".DBPREFIX."module_newsletter_confirm_mail WHERE id='1'";
	$objCheckConfim = $objDatabase->SelectLimit($query,1);
	if ($objCheckConfim) {
		if ($objCheckConfim->RecordCount() == 0) {
			//insert confirm mail
			$query = "INSERT INTO `".DBPREFIX."module_newsletter_confirm_mail` ( `id` , `title` , `content` ) VALUES (1, '[[url]] Newsletteraktivierung', 'Guten Tag [[title]] [[lastname]] Ihre E-Mail Adresse wurde erfolgreich in unserer Newsletter Datenbank abgespeichert. Um Ihre E-Mail Adresse zu aktivieren, benutzen Sie bitte folgenden Link: [[code]] Mit freundlichen Grüssen [[url]] - Team Automatisch generierte Nachricht [[date]]')";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = "SELECT id,title,content FROM ".DBPREFIX."module_newsletter_confirm_mail WHERE id='2'";
	$objCheckConfim = $objDatabase->SelectLimit($query,1);
	if ($objCheckConfim) {
		if ($objCheckConfim->RecordCount() == 0) {
			//insert confirm mail
			$query = "INSERT INTO `".DBPREFIX."module_newsletter_confirm_mail` ( `id` , `title` , `content` ) VALUES (2, '[[url]] Newsletteraktivierung erfolgreich', 'Guten Tag [[title]] [[lastname]] Ihre E-Mail Adresse wurde erfolgreich in unsererm Newsletter-System aktiviert. Sie werden nun in Zukunft unsere Newsletter erhalten Mit freundlichen Grüssen [[url]] - Team Automatisch generierte Nachricht [[date]]')";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
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
			$query = "UPDATE ".DBPREFIX."module_newsletter_confirm_mail SET title='".addslashes($mailTitle)."', content='".addslashes($mailContent)."' where id='".$id."'";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}

			$objResult->MoveNext();
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}


	//replace for templates
	$query = "SELECT id,description,html,text FROM ".DBPREFIX."module_newsletter_template";
	$objResult = $objDatabase->Execute($query );
	if ($objResult !== false) {
		while (!$objResult->EOF) {
			$id 		= $objResult->fields['id'];
			$html 		= $objResult->fields['html'];
			$text 		= $objResult->fields['text'];

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
			$query = "UPDATE ".DBPREFIX."module_newsletter_template SET html='".addslashes($newHtml)."', text='".addslashes($newText)."' where id='".$id."'";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}

			$objResult->MoveNext();
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
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
			$query = "UPDATE ".DBPREFIX."module_newsletter SET content='".addslashes($newHtml)."', content_text='".addslashes($newText)."' where id='".$id."'";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}

			$objResult->MoveNext();
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	// add required attribute to table contrexx_module_newsletter_template
	$arrColumns = $objDatabase->MetaColumns(DBPREFIX."module_newsletter_template");
	if ($arrColumns !== false) {
		if (!isset($arrColumns['REQUIRED'])) {
			$query = "ALTER TABLE ".DBPREFIX."module_newsletter_template ADD `required` INT( 1 ) NOT NULL DEFAULT '0'";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_newsletter_template konnte nicht ermittelt werden!";
		return false;
	}

	//template update table
	$query = "SELECT id FROM ".DBPREFIX."module_newsletter_template WHERE required=1";
	$objTemplates = $objDatabase->SelectLimit($query, 1);
	if ($objTemplates !== false) {
		if ($objTemplates->RecordCount() == 0) {
			$query = "SELECT id FROM ".DBPREFIX."module_newsletter_template";
			$objTemplates = $objDatabase->SelectLimit($query, 1);
			if ($objTemplates !== false) {
				if ($objTemplates->RecordCount() == 0) {
					$query = "INSERT INTO ".DBPREFIX."module_newsletter_template (`name`, `description`, `html`, `text`, `required`) VALUES ('Standard', 'Standard Template', '<html><head><title>[[subject]]</title></head><body>[[content]]<br /><br />[[profile_setup]][[unsubscribe]]</body></html>', '[[content]][[profile_setup]][[unsubscribe]]', 1)";
					if ($objDatabase->Execute($query) === false) {
						return _databaseError($query, $objDatabase->ErrorMsg());
					}
				} else {
					$query = "UPDATE ".DBPREFIX."module_newsletter_template SET `required`=1 WHERE id = ".$objTemplates->fields['id'];
					if ($objDatabase->Execute($query) === false) {
						return _databaseError($query, $objDatabase->ErrorMsg());
					}
				}
			} else {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}

		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	// reassociate the $arrColumns array due to the reassignement of the primary key caused by the drop statement before
	$arrColumns = $objDatabase->MetaColumns(DBPREFIX."module_newsletter_rel_cat_news");
	if ($arrColumns !== false) {
		$key = array();
		foreach ($arrColumns as $column) {
			if (!empty($column->primary_key)) {
				$key[] = $column->name;
			}
		}

		if (count($key) == 0) {
			$query = "ALTER TABLE `".DBPREFIX."module_newsletter_rel_cat_news` ADD PRIMARY KEY ( `newsletter` , `category` )";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_newsletter_rel_cat_news konnte nicht ermittelt werden!";
		return false;
	}

	// update structure of table contrexx_module_newsletter_rel_user_cat
	$arrColumns = $objDatabase->MetaColumns(DBPREFIX."module_newsletter_rel_user_cat");
	if ($arrColumns !== false) {
		if (isset($arrColumns['ID'])) {
			$query = "ALTER TABLE `".DBPREFIX."module_newsletter_rel_user_cat` DROP `id`";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_newsletter_rel_user_cat konnte nicht ermittelt werden!";
		return false;
	}

	$arrIndexes = $objDatabase->MetaIndexes(DBPREFIX."module_newsletter_rel_user_cat");
	if ($arrIndexes !== false) {
		if (isset($arrIndexes['user'])) {
			$query = "ALTER TABLE `".DBPREFIX."module_newsletter_rel_user_cat` DROP INDEX `user`";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_newsletter_rel_user_cat konnte nicht ermittelt werden!";
		return false;
	}

	// reassociate the $arrColumns array due to the reassignement of the primary key caused by the drop statement before
	$arrColumns = $objDatabase->MetaColumns(DBPREFIX."module_newsletter_rel_user_cat");
	if ($arrColumns !== false) {
		$key = array();
		foreach ($arrColumns as $column) {
			if (!empty($column->primary_key)) {
				$key[] = $column->name;
			}
		}

		if (count($key) == 0) {
			$query = "ALTER TABLE `".DBPREFIX."module_newsletter_rel_user_cat` ADD PRIMARY KEY ( `user` , `category` )";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_newsletter_rel_user_cat konnte nicht ermittelt werden!";
		return false;
	}

	// update structure of table contrexx_module_newsletter_user
	$arrIndexes = $objDatabase->MetaIndexes(DBPREFIX."module_newsletter_user");
	if ($arrIndexes !== false) {
		if (!isset($arrIndexes['status'])) {
			$query = "ALTER TABLE `".DBPREFIX."module_newsletter_user` ADD INDEX ( `status` )";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_newsletter_user konnte nicht ermittelt werden!";
		return false;
	}

	$arrTables = array();
	$arrTables = $objDatabase->MetaTables('TABLES');


	if (!in_array(DBPREFIX.'module_newsletter_system', $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_newsletter_system` (
				  `sysid` int(7) NOT NULL auto_increment,
				  `sysname` varchar(255) NOT NULL default '',
				  `sysvalue` varchar(255) NOT NULL default '',
				  `type` int(1) NOT NULL default '0',
				  PRIMARY KEY  (`sysid`)
				) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$query = "SELECT sysid FROM ".DBPREFIX."module_newsletter_system WHERE sysid='1'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck->RecordCount() == 0) {
		$query = 	"INSERT INTO `".DBPREFIX."module_newsletter_system` ( `sysid` , `sysname` , `sysvalue` , `type` )
					VALUES (
					NULL , 'defUnsubscribe', '1', '1'
					);";

		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	return true;
}
?>