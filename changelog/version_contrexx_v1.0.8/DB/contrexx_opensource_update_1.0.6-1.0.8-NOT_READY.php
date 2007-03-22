<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Update from Version 1.0.6 to 1.0.8</title>
</head>
<body>
<?php
/**

	Plazieren Sie diese Datei in das Hauptverzeichniss Ihrer Contrexx Installation
	und klicken Sie anschliessend auf "Update starten".

*/
if (!@include_once('config/configuration.php')) {
	print "Plazieren Sie diese Datei in das Hauptverzeichniss Ihrer Contrexx Installation.";
} else {
?>
<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
<?php
	if (!isset($_POST['doUpdate'])) {
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
	var $_arrDbTables;

	function Update() {
		$this->__constructor();
	}

	function __constructor() {
		global $objDatabase;

		$this->_arrDbTables = $objDatabase->MetaTables('TABLES');
	}

	function doUpdate() {

			if ($this->_fixStatistics()) {
				if ($this->_updateBackendAreas()) {
					if ($this->_updateModules()) {
						if ($this->_updateSettings()) {
							if ($this->_updateNewsTeaserTemplates()) {
								if ($this->_updateNewsletterModule()) {
									if ($this->_updateContent()) {
										if ($this->_updateCalendarModule()) {
											if ($this->_updateModuleRepository()) {
												if ($this->_createContactModule()) {
													if ($this->_createBannerModule()) {
														if ($this->_createBlockModule()) {
															if ($this->_createLivecamModule()) {
																if ($this->_createFeedNewsMLExtension()) {
																	if ($this->_createRecommendModule()) {
																		if ($this->_createWorkflowModule()) {
																			print "update ok";
																		}
																	}
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
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

	function _fixStatistics()
	{
		global $objDatabase;

		// javascript bugfix
		$query = "SELECT id, support, count FROM ".DBPREFIX."stats_javascript";
		$objResult = $objDatabase->Execute($query);
		if ($objResult !== false) {
			if ($objResult->RecordCount() == 0) {
				$query = "INSERT INTO `".DBPREFIX."stats_javascript` (`id`, `support`, `count`) VALUES (1, '0', 0)";
				if ($objDatabase->Execute($query) !== false) {
					$query = "INSERT INTO `".DBPREFIX."stats_javascript` (`id`, `support`, `count`) VALUES (2, '1', 0)";
					if ($objDatabase->Execute($query) === false) {
						return $this->_databaseError($query, $objDatabase->ErrorMsg());
					}
				} else {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		} else {
			return $this->_databaseError($query, $objDatabase->ErrorMsg());
		}

		// update settings
		$query = "SELECT id FROM ".DBPREFIX."stats_config WHERE name='count_visitor_number'";
		$objResult = $objDatabase->Execute($query);
		if ($objResult !== false) {
			if ($objResult->RecordCount() == 0) {
				$query = "INSERT INTO ".DBPREFIX."stats_config ( `id` , `name` , `value` , `status` ) VALUES ('', 'count_visitor_number', '', '0')";
				if ($objDatabase->Execute($query) === false) {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		} else {
			return $this->_databaseError($query, $objDatabase->ErrorMsg());
		}

		// update spiders statistics
		$objSpider = $objDatabase->Execute("SELECT id, page, count FROM ".DBPREFIX."stats_spiders");
		if ($objSpider !== false) {
			$arrIndexedPages = array();
			while (!$objSpider->EOF) {
				if (!isset($arrIndexedPages[$objSpider->fields['page']])) {
					$arrIndexedPages[$objSpider->fields['page']] = array();
				}
				array_push($arrIndexedPages[$objSpider->fields['page']], $objSpider->fields['count'] = $objSpider->fields['id']);
				$objSpider->MoveNext();
			}

			if (count($arrIndexedPages) > 0) {
				foreach ($arrIndexedPages as $page => $indexes) {
					if (count($indexes)>1) {
						krsort($indexes);
						reset($indexes);
						$id = current($indexes);
						$query = "DELETE FROM ".DBPREFIX."stats_spiders WHERE page='".$page."' AND id!=".$id;
						if ($objDatabase->Execute($query) === false) {
							return $this->_databaseError($query, $objDatabase->ErrorMsg());
						}
					}
				}
			}
		} else {
			return $this->_databaseError($query, $objDatabase->ErrorMsg());
		}
		return true;
	}

	function _updateBackendAreas()
	{
		global $objDatabase;

		$arrNewAreas = array(
			61	=> "INSERT INTO ".DBPREFIX."backend_areas ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('62', '3', 'navigation', 'TXT_BANNER_ADMINISTRATION', '1', 'index.php?cmd=banner', '_self', '28', '1', '61')",
			64	=> "INSERT INTO ".DBPREFIX."backend_areas ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('', '2', 'navigation', 'TXT_RECOMMEND', '1', 'index.php?cmd=recommend', '_self', '27', '0', '64')",
			65	=> "INSERT INTO ".DBPREFIX."backend_areas ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('65', '12', 'function', 'TXT_GALLERY_MENU_OVERVIEW', '1', '', '_self', '0', '1', '65')",
			66	=> "INSERT INTO ".DBPREFIX."backend_areas ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('66', '12', 'function', 'TXT_GALLERY_MENU_NEW_CATEGORY', '1', '', '_self', '0', '2', '66')",
			67	=> "INSERT INTO ".DBPREFIX."backend_areas ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('67', '12', 'function', 'TXT_GALLERY_MENU_UPLOAD', '1', '', '_self', '0', '3', '67')",
			68	=> "INSERT INTO ".DBPREFIX."backend_areas ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('68', '12', 'function', 'TXT_GALLERY_MENU_IMPORT', '1', '', '_self', '0', '4', '68')",
			69	=> "INSERT INTO ".DBPREFIX."backend_areas ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('69', '12', 'function', 'TXT_GALLERY_MENU_VALIDATE', '1', '', '_self', '0', '5', '69')",
			70	=> "INSERT INTO ".DBPREFIX."backend_areas ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('70', '12', 'function', 'TXT_GALLERY_MENU_SETTINGS', '1', '', '_self', '0', '6', '70')",
			71	=> "INSERT INTO ".DBPREFIX."backend_areas ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('71', '62', 'function', 'TXT_BANNER_MENU_OVERVIEW', '1', '', '_self', '0', '1', '71')",
			72	=> "INSERT INTO ".DBPREFIX."backend_areas ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('72', '62', 'function', 'TXT_BANNER_MENU_GROUP_ADD', '1', '', '_self', '0', '1', '72')",
			73	=> "INSERT INTO ".DBPREFIX."backend_areas ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('73', '62', 'function', 'TXT_BANNER_MENU_BANNER_NEW', '1', '', '_self', '0', '1', '73')",
			74	=> "INSERT INTO ".DBPREFIX."backend_areas ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('74', '62', 'function', 'TXT_BANNER_MENU_SETTINGS', '1', '', '_self', '0', '1', '74')",
			75	=> "INSERT INTO ".DBPREFIX."backend_areas ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('75', '1', 'navigation', 'TXT_CONTENT_HISTORY', '1', 'index.php?cmd=workflow', '_self', '1', '3', '75')",
			76	=> "INSERT INTO ".DBPREFIX."backend_areas ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('', '2', 'navigation', 'TXT_BLOCK_SYSTEM', '1', 'index.php?cmd=block', '_self', '7', '0', '76')",
			77	=> "INSERT INTO ".DBPREFIX."backend_areas ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('77', '75', 'function', 'TXT_DELETED_RESTORE', '1', '', '_self', '0', '1', '77')",
			78	=> "INSERT INTO ".DBPREFIX."backend_areas ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('78', '75', 'function', 'TXT_WORKFLOW_VALIDATE', '1', '', '_self', '0', '1', '78')",
			79	=> "INSERT INTO ".DBPREFIX."backend_areas ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('79', '6', 'function', 'TXT_ACTIVATE_HISTORY', '1', '', '_self', '0', '6', '79')",
			80	=> "INSERT INTO ".DBPREFIX."backend_areas ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('80', '6', 'function', 'TXT_HISTORY_DELETE_ENTRY', '1', '', '_self', '0', '7', '80')",

			82	=> "INSERT INTO ".DBPREFIX."backend_areas ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('82', '2', 'navigation', 'TXT_LIVECAM', '1', 'index.php?cmd=livecam', '_self', '30', '0', '82')",

			84	=> "INSERT INTO ".DBPREFIX."backend_areas ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('', '1', 'navigation', 'TXT_CONTACTS', '1', 'index.php?cmd=contact', '_self', '0', '7', '84')",
			85	=> "INSERT INTO ".DBPREFIX."backend_areas ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('', '90', 'function', 'TXT_CONTACT_SETTINGS', '1', 'index.php?cmd=contact&amp;act=settings', '_self', '6', '0', '85')",
		);

		$arrUpdateAreas = array(
			"UPDATE ".DBPREFIX."backend_areas SET `order_id` = '4' WHERE `area_id` =7 LIMIT 1",
			"UPDATE ".DBPREFIX."backend_areas SET `order_id` = '4' WHERE `area_id` =32 LIMIT 1",
			"UPDATE ".DBPREFIX."backend_areas SET `order_id` = '8' WHERE `area_id` =8 LIMIT 1"
		);

		// add new backend areas
		foreach ($arrNewAreas as $accessId => $insertQuery) {
			$query = "SELECT area_id FROM ".DBPREFIX."backend_areas WHERE access_id=".$accessId;
			$objResult = $objDatabase->Execute($query);
			if ($objResult !== false) {
				if ($objResult->RecordCount() == 0) {
					if ($objDatabase->Execute($insertQuery) === false) {
						return $this->_databaseError($insertQuery, $objDatabase->ErrorMsg());
					}
				}
			} else {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		// update backend areas
		foreach ($arrUpdateAreas as $query) {
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}
		return true;
	}

	function _updateModules()
	{
		global $objDatabase;

		$arrNewModules = array(
			'banner'	=> "INSERT INTO ".DBPREFIX."modules ( `id` , `name` , `description_variable` , `status` , `is_required` , `is_core` ) VALUES ('28' , 'banner', 'TXT_BANNER_MODULE_DESCRIPTION', 'y', '0', '1')",
			'block'		=> "INSERT INTO ".DBPREFIX."modules ( `id` , `name` , `description_variable` , `status` , `is_required` , `is_core` ) VALUES ('7' , 'block', 'TXT_BLOCK_MODULE_DESCRIPTION', 'y', '0', '0')",
			'livecam'	=> "INSERT INTO ".DBPREFIX."modules ( `id` , `name` , `description_variable` , `status` , `is_required` , `is_core` ) VALUES ('30', 'livecam', 'TXT_LIVECAM_MODULE_DESCRIPTION', 'y', '0', '0')",
			'recommend'	=> "INSERT INTO ".DBPREFIX."modules ( `id` , `name` , `description_variable` , `status` , `is_required` , `is_core` ) VALUES ('27', 'recommend', 'TXT_RECOMMEND_MODULE_DESCRIPTION', 'y', '0', '0')"
		);

		$arrUpdateModules = array(
			"UPDATE ".DBPREFIX."modules SET `status` = 'n' WHERE `name` = 'fileBrowser'",
			"UPDATE ".DBPREFIX."modules SET `is_required` = '1' WHERE `name` = 'contact'"
		);

		// add new modules
		foreach ($arrNewModules as $name => $insertQuery) {
			$query = "SELECT id FROM ".DBPREFIX."modules WHERE name='".$name."'";
			$objResult = $objDatabase->Execute($query);
			if ($objResult !== false) {
				if ($objResult->RecordCount() == 0) {
					if ($objDatabase->Execute($insertQuery) === false) {
						return $this->_databaseError($insertQuery, $objDatabase->ErrorMsg());
					}
				}
			} else {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		// update modules
		foreach ($arrUpdateModules as $query) {
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}
		return true;
	}

	function _updateSettings()
	{
		global $objDatabase;

		$arrNewOptions = array(
			'calendarheadlines'			=> "INSERT INTO ".DBPREFIX."settings ( `setid` , `setname` , `setvalue` , `setmodule` ) VALUES ('', 'calendarheadlines', '1', '21')",
			'calendarheadlinescount'	=> "INSERT INTO ".DBPREFIX."settings ( `setid` , `setname` , `setvalue` , `setmodule` ) VALUES ('', 'calendarheadlinescount', '5', '21')",
			'contentHistoryStatus'		=> "INSERT INTO ".DBPREFIX."settings ( `setid` , `setname` , `setvalue` , `setmodule` ) VALUES ('', 'contentHistoryStatus', 'on', 1)",
			'blockStatus'				=> "INSERT INTO ".DBPREFIX."settings ( `setid` , `setname` , `setvalue` , `setmodule` ) VALUES ('', 'blockStatus', '0', '7')",
			'calendarheadlinescat'		=> "INSERT INTO ".DBPREFIX."settings ( `setid` , `setname` , `setvalue` , `setmodule` ) VALUES ('', 'calendarheadlinescat', '0', '21')",
			'calendardefaultcount'		=> "INSERT INTO ".DBPREFIX."settings ( `setid` , `setname` , `setvalue` , `setmodule` ) VALUES ('', 'calendardefaultcount', '10', '21')",
			'feedNewsMLStatus'			=> "INSERT INTO ".DBPREFIX."settings ( `setid` , `setname` , `setvalue` , `setmodule` ) VALUES ('', 'feedNewsMLStatus', '0', '22')"
		);

		// add new options
		foreach ($arrNewOptions as $name => $insertQuery) {
			$query = "SELECT setid FROM ".DBPREFIX."settings WHERE setname='".$name."'";
			$objResult = $objDatabase->Execute($query);
			if ($objResult !== false) {
				if ($objResult->RecordCount() == 0) {
					if ($objDatabase->Execute($insertQuery) === false) {
						return $this->_databaseError($insertQuery, $objDatabase->ErrorMsg());
					}
				}
			} else {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}
		return true;
	}

	function _updateNewsTeaserTemplates()
	{
		global $objDatabase;

		$query = "SELECT id, html FROM ".DBPREFIX."module_news_teaser_frame_templates WHERE html LIKE '%{TEASER_DATE}%'";
		$objResult = $objDatabase->Execute($query);
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				$arrTemplates[$objResult->fields['id']] = $objResult->fields['html'];
				$objResult->MoveNext();
			}

			if (count($arrTemplates) > 0) {
				foreach ($arrTemplates as $id => $html) {
					$newHtml = str_replace('{TEASER_DATE}', '{TEASER_LONG_DATE}', $html);
					$query = "UPDATE ".DBPREFIX."module_news_teaser_frame_templates SET html='".$html."' WHERE id=".$id;
					if ($objDatabase->Execute($query) === false) {
						return $this->_databaseError($query, $objDatabase->ErrorMsg());
					}
				}
			}
		} else {
			return $this->_databaseError($query, $objDatabase->ErrorMsg());
		}

		return true;
	}

	function _updateNewsletterModule()
	{
		global $objDatabase, $_CONFIG;

		if (!in_array(DBPREFIX."module_newsletter", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_newsletter (  `id` int(11) NOT NULL auto_increment,  `subject` varchar(255) NOT NULL default '',  `template` int(11) NOT NULL default '0',  `content` text NOT NULL,  `content_text` text NOT NULL,  `attachment` set('0','1') NOT NULL default '',  `format` set('html/text','html','text') NOT NULL default '',  `priority` tinyint(1) NOT NULL default '0',  `sender_email` varchar(255) NOT NULL default '',  `sender_name` varchar(255) NOT NULL default '',  `return_path` varchar(255) NOT NULL default '',  `status` int(1) NOT NULL default '0',  `count` int(11) NOT NULL default '0',  `date_create` date NOT NULL default '0000-00-00',  `date_sent` date NOT NULL default '0000-00-00',  `tmp_copy` tinyint(1) NOT NULL default '0',  PRIMARY KEY  (`id`),  KEY `id` (`id`)) TYPE=MyISAM AUTO_INCREMENT=1" ;
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."module_newsletter_attachment", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_newsletter_attachment (  `id` int(11) NOT NULL auto_increment,  `newsletter` int(11) NOT NULL default '0',  `file_name` varchar(255) NOT NULL default '',  `file_nr` tinyint(1) NOT NULL default '0',  PRIMARY KEY  (`id`),  KEY `newsletter` (`newsletter`)) TYPE=MyISAM AUTO_INCREMENT=1";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."module_newsletter_category", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_newsletter_category (  `id` int(11) NOT NULL auto_increment,  `status` tinyint(1) NOT NULL default '0',  `name` varchar(255) NOT NULL default '',  PRIMARY KEY  (`id`),  KEY `id` (`id`)) TYPE=MyISAM AUTO_INCREMENT=1";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."module_newsletter_config", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_newsletter_config (  `id` int(11) NOT NULL default '0',  `sender_email` varchar(255) NOT NULL default '',  `sender_name` varchar(255) NOT NULL default '',  `return_path` varchar(255) NOT NULL default '',  `profile_setup_html` text NOT NULL,  `profile_setup_text` text NOT NULL,  `unsubscribe_html` text NOT NULL,  `unsubscribe_text` text NOT NULL,  `mails_per_run` int(11) NOT NULL default '0',  PRIMARY KEY  (`id`)) TYPE=MyISAM";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."module_newsletter_rel_cat_news", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_newsletter_rel_cat_news (  `id` int(11) NOT NULL auto_increment,  `newsletter` int(11) NOT NULL default '0',  `category` int(11) NOT NULL default '0',  PRIMARY KEY  (`id`)) TYPE=MyISAM AUTO_INCREMENT=1";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."module_newsletter_rel_user_cat", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_newsletter_rel_user_cat (  `id` int(11) NOT NULL auto_increment,  `user` int(11) NOT NULL default '0',  `category` int(11) NOT NULL default '0',  PRIMARY KEY  (`id`),  KEY `user` (`user`)) TYPE=MyISAM AUTO_INCREMENT=1";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."module_newsletter_template", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_newsletter_template (  `id` int(11) NOT NULL auto_increment,  `name` varchar(255) NOT NULL default '',  `description` varchar(255) NOT NULL default '',  `html` text NOT NULL,  `text` text NOT NULL,  PRIMARY KEY  (`id`)) TYPE=MyISAM AUTO_INCREMENT=2";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in-array(DBPREFIX."module_newsletter_tmp_sending", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_newsletter_tmp_sending (  `id` int(11) NOT NULL auto_increment,  `newsletter` int(11) NOT NULL default '0',  `email` varchar(255) NOT NULL default '',  `sendt` tinyint(1) NOT NULL default '0',  PRIMARY KEY  (`id`),  KEY `email` (`email`)) TYPE=MyISAM AUTO_INCREMENT=1";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in-array(DBPREFIX."module_newsletter_tmp_sending", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_newsletter_user (  `id` int(11) NOT NULL auto_increment,  `code` varchar(255) NOT NULL default '',  `email` varchar(255) NOT NULL default '',  `lastname` varchar(255) NOT NULL default '',  `firstname` varchar(255) NOT NULL default '',  `street` varchar(255) NOT NULL default '',  `zip` varchar(255) NOT NULL default '',  `city` varchar(255) NOT NULL default '',  `country` varchar(255) NOT NULL default '',  `phone` varchar(255) NOT NULL default '',  `birthday` varchar(100) NOT NULL default '',  `status` int(1) NOT NULL default '0',  `emaildate` date NOT NULL default '0000-00-00',  PRIMARY KEY  (`id`),  KEY `emailadress` (`email`)) TYPE=MyISAM AUTO_INCREMENT=1";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		// add newsletter template
		$query = "SELECT id FROM ".DBPREFIX."module_newsletter_template WHERE id=1";
		$objResult = $objDatabase->Execute($query);
		if ($objResult !== false) {
			if ($objResult->RecordCount() == 0) {
				$query = "INSERT INTO ".DBPREFIX."module_newsletter_template VALUES (1, 'Standard', 'Standard Template, Contrexx 2006', '<html>\r\n<head>\r\n<title><-- subject --></title>\r\n</head>\r\n<body>\r\n<-- content -->\r\n<br /><br />\r\n<-- profile_setup --><br />\r\n<-- unsubscribe -->\r\n</body>\r\n</html>', '<-- content -->\r\n\r\n<-- profile_setup -->\r\n<-- unsubscribe -->')";
				if ($objDatabase->Execute($query) === false) {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		} else {
			return $this->_databaseError($query, $objDatabase->ErrorMsg());
		}

		// add config stuff
		$query = "SELECT id FROM ".DBPREFIX."module_newsletter_config WHERE id=1";
		$objResult = $objDatabase->Execute($query);
		if ($objResult !== false) {
			if ($objResult->RecordCount() == 0) {
				$query = "SELECT setname, setvalue FROM ".DBPREFIX."newsletter_settings WHERE setname='newsletterSenderName' OR setname='newsletterSenderEmail' OR setname='newsletterSendLimit'";
				$objResult = $objDatabase->Execute($query);
				if ($objResult !== false) {
					$coreAdminName = "";
					$fromMail = "";
					$sendLimit = 30;

					while (!$objResult->EOF) {
						switch ($objResult->fields['setname']) {
							case 'newsletterSenderName':
								$coreAdminName = $objResult->fields['setvalue'];
								break;

							case 'newsletterSenderEmail':
								$fromMail = $objResult->fields['setvalue'];
								break;

							case 'newsletterSendLimit':
								$sendLimit = $objResult->fields['setvalue'];
								break;
						}
						$objResult->MoveNext();
					}
				} else {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}

				$query = "INSERT INTO ".DBPREFIX."module_newsletter_config VALUES (1, '".$fromMail."', '".$coreAdminName."', '".$fromMail."', 'Profil bearbeiten', 'Profil bearbeiten', 'Newsletter Abmelden', 'Newsletter Abmelden', ".$sendLimit.")";
				if ($objDatabase->Execute($query) === false) {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		} else {
			return $this->_databaseError($query, $objDatabase->ErrorMsg());
		}


		// get sender name
		if (in_array(DBPREFIX."newsletter_settings", $this->_arrDbTables)) {
			$query = "SELECT setvalue FROM ".DBPREFIX."newsletter_settings WHERE setname='newsletterSenderName'";
			$objResult = $objDatabase->Execute($query);
			if ($objResult !== false) {
				$senderName = $objResult->fields['setvalue'];
			} else {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		// add newsletters
		if (in_array(DBPREFIX."newsletter_lists", $this->_arrDbTables)) {
			$query = "SELECT listid, listname, listemailfrom FROM ".DBPREFIX."newsletter_lists";
			$objResult = $objDatabase->Execute($query);
			if ($objResult !== false) {
				$arrLists = array();
				while (!$objResult->EOF) {
					$arrLists[$objResult->fields['listid']] = array(
						'listname'		=> $objResult->fields['listname'],
						'listemailfrom'	=> $objResult->fields['listemailfrom']
					);
					$objResult->MoveNext();
				}

				if (count($arrLists) > 0) {
					foreach ($arrLists as $id => $arrList) {
						$query = "SELECT id FROM ".DBPREFIX."module_newsletter_category WHERE name='".$arrList['listname']."'";
						$objResult = $objDatabase->Execute($query);
						if ($objResult !== false) {
							if ($objResult->RecordCount() == 0) {
								$query = "INSERT INTO ".DBPREFIX."module_newsletter_category VALUES ('', 1, '".$arrList['listname']."')";
								if ($objDatabase->Execute($query) !== false) {
									$categoryId = $objDatabase->Insert_ID();
								} else {
									return $this->_databaseError($query, $objDatabase->ErrorMsg());
								}
							} else {
								$categoryId = $objResult->fields['id'];
							}
						} else {
							return $this->_databaseError($query, $objDatabase->ErrorMsg());
						}

						$query = "SELECT id FROM ".DBPREFIX."module_newsletter_rel_cat_news WHERE category=".$categoryId;
						$objResult = $objDatabase->Execute($query);
						if ($objResult !== false) {
							if ($objResult->RecordCount() == 0) {
								$query = "INSERT INTO ".DBPREFIX."module_newsletter VALUES ('', 'Test Newsletter', 1, '<br />Test Newsletter HTML<br /><br />&lt;-- profile_setup --&gt;<br />&lt;-- unsubscribe --&gt;', '\r\nTest Newsletter Text\r\n\r\n<-- profile_setup -->\r\n<-- unsubscribe -->', '0', 'html/text', 3, '".$arrList['listemailfrom']."', '".$senderName."', '".$arrList['listemailfrom']."', 0, 0, '2006-01-10', '0000-00-00', 0)";
								if ($objDatabase->Execute($query) !== false) {
									$newsletterId = $objDatabase->Insert_ID();
									$query = "INSERT INTO ".DBPREFIX."module_newsletter_rel_cat_news (`newsletter`, `category`) VALUES (".$newsletterId.", ".$categoryId.")";
									if ($objDatabase->Execute($query) === false) {
										$errorMsg = $objDatabase->ErrorMsg();
										$objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter WHERE id=".$newsletterId);
										return $this->_databaseError($query, $errorMsg);
									}
								} else {
									return $this->_databaseError($query, $objDatabase->ErrorMsg());
								}
							}
						} else {
							return $this->_databaseError($query, $objDatabase->ErrorMsg());
						}
					}
				}
			} else {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}

			$query = "DROP TABLE ".DBPREFIX."newsletter_lists";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (in_array(DBPREFIX."newsletter_archiv", $this->_arrDbTables)) {
			$query = "DROP TABLE ".DBPREFIX."newsletter_archiv";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (in_array(DBPREFIX."newsletter_settings", $this->_arrDbTables)) {
			$query = "DROP TABLE ".DBPREFIX."newsletter_settings";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}


		if (in_array(DBPREFIX."newsletter_emails", $this->_arrDbTables)) {
			$selectQuery = "SELECT emailid, emailadress, emailvalidate, emaillistid, emaildate FROM ".DBPREFIX."newsletter_emails LIMIT 0, 30";
			do {
				$objUsers = $objDatabase->Execute($selectQuery);
				if ($objUsers !== false) {
					$arrUsers = array();
					while (!$objUsers->EOF) {
						$arrUsers[$objUsers->fields['emailid']] = array(
							'email'		=> $objUsers->fields['emailadress'],
							'status'	=> $objUsers->fields['emailvalidate'],
							'date'		=> $objUsers->fields['emaildate'],
							'category'	=> $objUsers->fields['emaillistid']
						);
						$objUsers->MoveNext();
					}

					if (count($arrUsers) > 0) {
						foreach ($arrUsers as $id => $arrUser) {
							// add user
							$query = "SELECT id FROM ".DBPREFIX."module_newsletter_user WHERE email='".$arrUser['email']."'";
							$objResult = $objDatabase->Execute($query);
							if ($objResult !== false) {
								if ($objResult->RecordCount() == 0) {
									$query = "INSERT INTO ".DBPREFIX."module_newsletter_user VALUES ('', '".$this->_newsletterEmailCode()."', '".$arrUser['email']."', '', '', '', '', '', '', '', '', ".$arrUser['status'].", '".$arrUser['date']."')";
									if ($objDatabase->Execute($query) !== false) {
										$userId = $objDatabase->Insert_ID();
									} else {
										return $this->_databaseError($query, $objDatabase->ErrorMsg());
									}
								} else {
									$userId = $objResult->fields['id'];
								}

								// associated user with category
								$query = "SELECT id FROM ".DBPREFIX."module_newsletter_rel_user_cat WHERE user=".$userId." AND category=".$arrUser['category'];
								$objResult = $objDatabase->Execute($query);
								if ($objResult !== false) {
									if ($objResult->RecordCount() == 0) {
										$query = "INSERT INTO ".DBPREFIX."module_newsletter_rel_user_cat VALUES ('', ".$userId.", ".$arrUser['category'].")";
										if ($objDatabase->Execute($query) === false) {
											return $this->_databaseError($query, $objDatabase->ErrorMsg());
										}
									}
								} else {
									return $this->_databaseError($query, $objDatabase->ErrorMsg());
								}

								$query = "DELETE FROM ".DBPREFIX."newsletter_emails WHERE emailid=".$id;
								if ($objDatabase->Execute($query) === false) {
									return $this->_databaseError($query, $objDatabase->ErrorMsg());
								}
							} else {
								return $this->_databaseError($query, $objDatabase->ErrorMsg());
							}
						}
					}
				} else {
					return $this->_databaseError($selectQuery, $objDatabase->ErrorMsg());
				}
			} while ($objUsers->RecordCount() > 0);

			$query = "DROP TABLE ".DBPREFIX."newsletter_emails";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		return true;
	}

	function _newsletterEmailCode(){
		$ReturnVar = '';
		$pool = "qwertzupasdfghkyxcvbnm";
		$pool .= "23456789";
		$pool .= "WERTZUPLKJHGFDSAYXCVBNM";
		srand ((double)microtime()*1000000);
		for($index = 0; $index < 10; $index++){
			$ReturnVar .= substr($pool,(rand()%(strlen ($pool))), 1);
		}
		return $ReturnVar;
	}

	function _updateContent()
	{
		global $objDatabase;

		$arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."content_navigation");
		if (!is_array($arrColumns)) {
			print "Konnte die Spaltennamen der Tabelle ".DBPREFIX."content_navigation nicht ermitteln!";
			return false;
		}

		if (!in_array("activestatus", $arrColumns)) {
			$query = "ALTER TABLE ".DBPREFIX."content_navigation ADD `activestatus` SET( '0', '1' ) DEFAULT '1' NOT NULL AFTER `displaystatus`";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array("is_validated", $arrColumns)) {
			$query = "ALTER TABLE ".DBPREFIX."content_navigation ADD `is_validated` SET( '0', '1' ) DEFAULT '1' NOT NULL AFTER `catid`";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array("cachingstatus", $arrColumns)) {
			$query = "ALTER TABLE ".DBPREFIX."content_navigation ADD `cachingstatus` SET('0','1') DEFAULT '1' NOT NULL AFTER `activestatus`";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		$arrContentColumns = $objDatabase->MetaColumnNames(DBPREFIX."content");
		if (!is_array($arrContentColumns)) {
			print "Konnte die Spaltennamen der Tabelle ".DBPREFIX."content nicht ermitteln!";
			return false;
		}

		if (!in_array("metatitle", $arrContentColumns)) {
			$query = "ALTER TABLE ".DBPREFIX."content ADD `metatitle` VARCHAR(250) NOT NULL AFTER `title`";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		$query = "UPDATE ".DBPREFIX."content SET `metatitle` = `title`";
		if ($objDatabase->Execute($query) === false) {
			return $this->_databaseError($query, $objDatabase->ErrorMsg());
		}

		return true;
	}

	function _updateCalendarModule()
	{
		global $objDatabase;

		$arrColumnNames = $objDatabase->MetaColumnNames(DBPREFIX."module_calendar");
		if (!is_array($arrColumnNames)) {
			print "Kann die Spaltennamen der Tabelle ".DBPREFIX."module_calendar nicht ermitteln!";
			return false;
		}

		// Neue Start - und Enddatumsfelder
		if (!in_array("startdate", $arrColumnNames)) {
			$query = "ALTER TABLE ".DBPREFIX."module_calendar ADD `startdate` INT( 14 ) NOT NULL AFTER `catid`";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}
		if (!in_array("enddate", $arrColumnNames)) {
			$query = "ALTER TABLE ".DBPREFIX."module_calendar ADD `enddate` INT( 14 ) NOT NULL AFTER `startdate`";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		// Active Feld
		if (!in_array("active", $arrColumnNames)) {
			$query = "ALTER TABLE ".DBPREFIX."module_calendar ADD `active` TINYINT( 1 ) DEFAULT '1' NOT NULL AFTER `id`";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		// Volltextsuche
		$arrIndexes = $objDatabase->MetaIndexes();
		if (!is_array($arrIndexes)) {
			print "Konnte nicht überprüfen, ob der Index 'name' für die Tabelle ".DBPREFIX."mdoule_calendar bereits existiert!";
			return false;
		}
		if (!in_array('name', $arrIndexes)) {
			$query = "ALTER TABLE ".DBPREFIX."module_calendar ADD FULLTEXT `name` (`name`, `comment`, `place`)";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (in_array("date", $arrColumnNames) && in_array("time", $arrColumnNames) && in_array("end_date", $arrColumnNames) && in_array("end_time", $arrColumnNames)) {
			$query = "SELECT id, date, time, end_date, end_time, startdate, enddate FROM ".DBPREFIX."module_calendar";
			$objResult = $objDatabase->Execute($query);
			if ($objResult !== false) {
				// Geht jeden Datensatz durch und ändert die Zeit
				while (!$objResult->EOF) {
					if (empty($objResult->fields['startdate']) && empty($objResult->fields['enddate'])) {
					    $date = explode("/", $objResult->fields['date']);
					    $hours = substr($objResult->fields['time'], 0, 2);
					    $minutes = substr($objResult->fields['time'], 2, 2);

					    $end_date = explode("/", $objResult->fields['end_date']);
					    $end_hours = substr($objResult->fields['end_time'], 0, 2);
					    $end_minutes = substr($objResult->fields['end_time'], 2, 2);

					    $startdate = mktime($hours, $minutes, 0, $date[0], $date[1], $date[2]);
					    $enddate = mktime($end_hours, $end_minutes, 0, $end_date[0], $end_date[1], $end_date[2]);

					    $query = "UPDATE ".DBPREFIX."module_calendar SET startdate = ".$startdate.", enddate = ".$enddate." WHERE id = ".$objResult->fields['id'];
					    if ($objDatabase->Execute($query) === false) {
					    	return $this->_databaseError($query, $objDatabase->ErrorMsg());
					    }
					}
				    $objResult->MoveNext();
				}
			} else {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		// drop unneeded columns
		$arrDropColumns = array(
			'date',
			'time',
			'end_date',
			'end_time',
			'sort'
		);
		foreach ($arrDropColumns as $column) {
			if (in_array($column, $arrColumnNames)) {
				$query = "ALTER TABLE ".DBPREFIX."module_calendar` DROP `".$column."`";
				if ($objDatabase->Execute($query) === false) {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		}

		return true;
	}

	function _updateModuleRepository()
	{
		global $objDatabase;

		$query = "TRUNCATE TABLE ".DBPREFIX."module_repository";
		if ($objDatabase->Execute($query) === false) {
			return $this->_databaseError($query, $objDatabase->ErrorMsg());
		}

		$arrRepository = array(
			3	=> array(
				''	=> 'INSERT INTO '.DBPREFIX.<<<SQL
						module_repository VALUES (508, 3, '<table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"2\\" border=\\"0\\">\r\n    <tbody>\r\n        <tr>\r\n            <td colspan=\\"2\\">Sie sind hier: {GALLERY_CATEGORY_TREE}</td>\r\n        </tr>\r\n        <!-- BEGIN galleryCategories -->\r\n        <tr>\r\n            <td colspan=\\"2\\"><hr size=\\"1\\" /></td>\r\n        </tr>\r\n        <tr class=\\"row{GALLERY_STYLE}\\">\r\n            <td width=\\"1%\\" valign=\\"top\\" align=\\"left\\">{GALLERY_CATEGORY_IMAGE}</td>\r\n            <td valign=\\"top\\"><b>{GALLERY_CATEGORY_NAME}</b><br />{GALLERY_CATEGORY_INFO}<br />{GALLERY_CATEGORY_DESCRIPTION}</td>\r\n        </tr>\r\n        <!-- END galleryCategories -->\r\n        <tr>\r\n            <td colspan=\\"2\\"><hr size=\\"1\\" /></td>\r\n        </tr>\r\n    </tbody>\r\n</table>\r\n<!-- CATEGORY END AND IMAGES START -->   <!-- BEGIN galleryImageBlock --> {GALLERY_JAVASCRIPT}\r\n<table width=\\"100%\\" cellspacing=\\"1\\" cellpadding=\\"0\\" border=\\"0\\">\r\n    <tbody>\r\n        <tr>\r\n            <td colspan=\\"3\\">{GALLERY_CATEGORY_COMMENT}<br /></td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\\"3\\"><hr size=\\"1\\" /></td>\r\n        </tr>\r\n        <!-- BEGIN galleryShowImages -->\r\n        <tr>\r\n            <td width=\\"33%\\" valign=\\"top\\" align=\\"center\\" id=\\"gallery\\"> {GALLERY_IMAGE1}<br /> {GALLERY_IMAGE_LINK1} </td>\r\n            <td width=\\"33%\\" valign=\\"top\\" align=\\"center\\" id=\\"gallery\\"> {GALLERY_IMAGE2}<br /> {GALLERY_IMAGE_LINK2} </td>\r\n            <td width=\\"33%\\" valign=\\"top\\" align=\\"center\\" id=\\"gallery\\"> {GALLERY_IMAGE3}<br /> {GALLERY_IMAGE_LINK3} </td>\r\n        </tr>\r\n        <!-- END galleryShowImages -->\r\n    </tbody>\r\n</table>\r\n<!-- END galleryImageBlock -->', 'Bildergalerie', '', 'n', 0, 'on', 'system', 2, '1')
SQL
				),
			4	=> array(
				'profile'	=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (525, 4, '{NEWSLETTER}', 'Newsletter Profil bearbeiten', 'profile', 'y', 521, 'off', 'system', 2, '1')
SQL
				,
				'confirm'	=> 'INSERT INTO '.DBPREFIX.<<<SQL
								'module_repository VALUES (524, 4, '{NEWSLETTER}', 'Newsletter bestätigen', 'confirm', 'y', 521, 'off', 'system', 1, '1')
SQL
				,
				'unsubscribe'	=> 'INSERT INTO '.DBPREFIX.<<<SQL
									module_repository VALUES (523, 4, '{NEWSLETTER}', 'Newsletter abmelden', 'unsubscribe', 'y', 521, 'off', 'system', 1, '1')
SQL
				,
				'subscribe'		=> 'INSERT INTO '.DBPREFIX.<<<SQL
									module_repository VALUES (522, 4, '{NEWSLETTER}', 'Newsletter abonnieren', 'subscribe', 'y', 521, 'off', 'system', 1, '1')
SQL
				,
				''				=> 'INSERT INTO '.DBPREFIX.<<<SQL
									module_repository VALUES (521, 4, '{NEWSLETTER}', 'Newsletter', '', 'y', 0, 'on', 'system', 1, '1')
SQL
				),
			5	=> array(
				''	=> 'INSERT INTO '.DBPREFIX.<<<SQL
									module_repository VALUES (342, 5, '<form action="index.php" method="get">\r\n	<input name="term" value="{SEARCH_TERM}" size="30" maxlength="100" />\r\n	<input value="search" name="section" type="hidden" />\r\n	<input value="{TXT_SEARCH}" name="Submit" type="submit" />\r\n</form>\r\n<br />\r\n{SEARCH_TITLE}<br />\r\n<!-- BEGIN searchrow -->\r\n	{LINK} {COUNT_MATCH}<br />\r\n	{SHORT_CONTENT}<br />\r\n<!-- END searchrow -->\r\n<br />\r\n{SEARCH_PAGING}\r\n<br />\r\n<br />', 'Suchen', '', 'y', 0, 'off', 'system', 110, '1')
SQL
				),
			6	=> array(
				''	=> 'INSERT INTO '.DBPREFIX.<<<SQL
									module_repository VALUES (339, 6, 'Ihre Adresse<br/>\r\nIhre Email Adresse <br/>\r\n<br/>\r\n<form enctype=\\"multipart/form-data\\" method=\\"post\\" action=\\"index.php?section=contact&amp;id=3&amp;cmd=thanks\\" name=\\"ContactForm\\">\r\n    <table width=\\"80%\\" border=\\"0\\">\r\n        <tbody>\r\n            <tr>\r\n                <td><b>Name</b></td>\r\n                <td><input type=\\"text\\" name=\\"Vorname\\" size=\\"35\\" style=\\"width: 80%;\\"/> </td>\r\n            </tr>\r\n            <tr>\r\n                <td>Firmenname</td>\r\n                <td><input type=\\"text\\" name=\\"Name\\" size=\\"35\\" style=\\"width: 80%;\\"/> </td>\r\n            </tr>\r\n            <tr>\r\n                <td>Strasse</td>\r\n                <td><input type=\\"text\\" name=\\"Strasse\\" size=\\"35\\" style=\\"width: 80%;\\"/> </td>\r\n            </tr>\r\n            <tr>\r\n                <td>PLZ</td>\r\n                <td><input type=\\"text\\" name=\\"PLZ\\" size=\\"35\\" style=\\"width: 80%;\\"/> </td>\r\n            </tr>\r\n            <tr>\r\n                <td>Ort</td>\r\n                <td><input type=\\"text\\" name=\\"Ort\\" size=\\"35\\" style=\\"width: 80%;\\"/> </td>\r\n            </tr>\r\n            <tr>\r\n                <td>Land</td>\r\n                <td><input type=\\"text\\" name=\\"Land\\" size=\\"35\\" style=\\"width: 80%;\\"/> </td>\r\n            </tr>\r\n            <tr>\r\n                <td><b>Telefon</b></td>\r\n                <td><input type=\\"text\\" name=\\"Telefon\\" size=\\"35\\" style=\\"width: 80%;\\"/> </td>\r\n            </tr>\r\n            <tr>\r\n                <td>Fax</td>\r\n                <td><input type=\\"text\\" name=\\"Fax\\" size=\\"35\\" style=\\"width: 80%;\\"/> </td>\r\n            </tr>\r\n            <tr>\r\n                <td>E-Mail</td>\r\n                <td><input type=\\"text\\" name=\\"EMail\\" size=\\"35\\" style=\\"width: 80%;\\"/> </td>\r\n            </tr>\r\n            <tr>\r\n                <td>Bemerkungen&nbsp;</td>\r\n                <td><textarea cols=\\"30\\" rows=\\"7\\" name=\\"Bemerkungen\\" style=\\"width: 80%;\\"></textarea></td>\r\n            </tr>\r\n            <tr>\r\n                <td>Datei<br/>\r\n                </td>\r\n                <td>\r\n                <div align=\\"left\\"><input type=\\"file\\" name=\\"file\\" style=\\"width: 80%;\\"/> </div>\r\n                </td>\r\n            </tr>\r\n            <tr>\r\n                <td>&nbsp;</td>\r\n                <td><input type=\\"reset\\" name=\\"Reset\\" value=\\"Löschen\\"/> &nbsp;&nbsp;&nbsp;  <input type=\\"submit\\" name=\\"Submit\\" value=\\"Senden\\"/>  </td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>', 'Kontakt', '', 'n', 0, 'on', 'system', 3, '1')
SQL
				,
				'thanks'	=> 'INSERT INTO '.DBPREFIX.<<<SQL
									module_repository VALUES (340, 6, 'Formulardaten erhalten', 'Formulardaten erhalten', 'thanks', 'y', 339, 'off', 'daeppen', 0, '1')
SQL
				),
			8	=> array(
				'feed'	=> 'INSERT INTO '.DBPREFIX.<<<SQL
									module_repository VALUES (498, 8, 'F&uuml;gen Sie den folgenden Code in Ihre eigene Webseite ein, um das RSS Feed von {NEWS_HOSTNAME} auf Ihrer Webseite einzubinden:<br /> <br />\r\n<form>\r\n<textarea style=\\"width: 98%; font-size: 95%;\\" wrap=\\"PHYSICAL\\" rows=\\"18\\" name=\\"code\\">{NEWS_RSS2JS_CODE}</textarea>\r\n<input type=button value=\\"Alles markieren\\" onclick=\\"javascript:this.form.code.focus();this.form.code.select();\\" name=\\"button\\">\r\n</form>\r\n\r\n<br />\r\nGemäss obigem Beispiel sieht die Ausgabe dann folgendermassen aus:<br /><br />\r\n<script language=\\"JavaScript\\" type=\\"text/javascript\\">\r\n<!--\r\n// Diese Variablen sind optional\r\nvar rssFeedFontColor = \\"#000000\\"; // Schriftfarbe\r\nvar rssFeedFontSize = 8; // Schriftgrösse\r\nvar rssFeedFont = \\"Verdana, Arial\\"; // Schriftart\r\nvar rssFeedLimit = 10; // Anzahl anzuzeigende Newsmeldungen\r\nvar rssFeedShowDate = true; // Datum der Newsmeldung anzeigen\r\nvar rssFeedTarget = \\"_blank\\"; // _blank | _parent | _self | _top\r\n// -->\r\n</script>\r\n<script type=\\"text/javascript\\" language=\\"JavaScript\\" src=\\"{NEWS_RSS2JS_URL}\\"></script>\r\n<noscript>\r\n<a href=\\"{NEWS_RSS_FEED_URL}\\">{NEWS_HOSTNAME} - News anzeigen</a>\r\n</noscript>', 'News Feed', 'feed', 'n', 495, 'on', 'system', 1, '1')
SQL
				,
				''		=> 'INSERT INTO '.DBPREFIX.<<<SQL
							module_repository VALUES (495, 8, '<form name=\\"formNews\\" action=\\"index.php?section=news\\" method=\\"post\\">\r\n    <select onchange=\\"this.form.submit()\\" name=\\"category\\">\r\n    <option value=\\"\\" selected=\\"selected\\">{NEWS_NO_CATEGORY}</option>\r\n{NEWS_CAT_DROPDOWNMENU}</select>\r\n</form>\r\n<br/>\r\n<table id=\\"news\\" cellspacing=\\"0\\" cellpadding=\\"5\\" width=\\"100%\\" border=\\"0\\">\r\n<tr>\r\n<td nowrap=\\"nowrap\\" width=\\"15%\\">{TXT_DATE}</th>\r\n<td nowrap=\\"nowrap\\" width=\\"70%\\">{TXT_TITLE}</th>\r\n<td nowrap=\\"nowrap\\" width=\\"15%\\">{TXT_CATEGORY}</th>\r\n</tr>\r\n<!-- BEGIN newsrow -->\r\n<tr>\r\n<td nowrap=\\"nowrap\\" width=\\"15%\\">{NEWS_DATE}&nbsp;&nbsp;</td>\r\n<td width=\\"70%\\"><b>{NEWS_LINK}</b>&nbsp;&nbsp;</td>\r\n<td nowrap=\\"nowrap\\" width=\\"15%\\">{NEWS_CATEGORY}</td>\r\n</tr>\r\n<!-- END newsrow -->\r\n</table>\r\n<br/>\r\n{NEWS_PAGING}<br/>\r\n<br/>', 'News', '', 'n', 0, 'on', 'system', 11, '1')
SQL
				,
				'details'	=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (496, 8, 'Veröffentlicht am: {NEWS_DATE}\r\n<br /><br />\r\n{NEWS_TEXT} <br />\r\n{NEWS_SOURCE}<br />\r\n{NEWS_URL} \r\n<br />\r\n{NEWS_LASTUPDATE}<br />', 'Newsmeldung', 'details', 'y', 495, 'off', 'system', 1, '1')
SQL
				,
				'submit'	=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (497, 8, '<b>{NEWS_STATUS_MESSAGE}</b>\r\n<form action=\\"index.php?section=news&amp;cmd=submit\\" method=\\"post\\">\r\n<table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"5\\" border=\\"0\\">\r\n    <tbody>\r\n        <tr>\r\n            <th colspan=\\"2\\">{TXT_NEWS_MESSAGE}</th>\r\n        </tr>\r\n        <tr>\r\n            <td width=\\"20%\\">{TXT_TITLE}</td>\r\n            <td width=\\"80%\\"><input type=\\"text\\" style=\\"width: 250px;\\" name=\\"newsTitle\\" value=\\"{NEWS_TITLE}\\" maxlength=\\"250\\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td width=\\"20%\\">{TXT_CATEGORY}</td>\r\n            <td width=\\"80%\\"><select style=\\"width: 250px;\\" name=\\"newsCat\\">{NEWS_CAT_MENU}</select></td>\r\n        </tr>\r\n        <tr>\r\n            <td width=\\"20%\\">{TXT_EXTERNAL_SOURCE}</td>\r\n            <td width=\\"80%\\"><input type=\\"text\\" style=\\"width: 250px;\\" name=\\"newsSource\\" value=\\"{NEWS_SOURCE}\\" maxlength=\\"250\\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td width=\\"20%\\">{TXT_LINK} #1</td>\r\n            <td width=\\"80%\\"><input type=\\"text\\" style=\\"width: 250px;\\" name=\\"newsUrl1\\" value=\\"{NEWS_URL1}\\" maxlength=\\"250\\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td width=\\"20%\\">{TXT_LINK} #2</td>\r\n            <td width=\\"80%\\"><input type=\\"text\\" style=\\"width: 250px;\\" name=\\"newsUrl2\\" value=\\"{NEWS_URL2}\\" maxlength=\\"250\\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <th colspan=\\"2\\"><br />{TXT_NEWS_CONTENT}</th>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\\"2\\">{NEWS_TEXT}</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\\"2\\"><input type=\\"submit\\" name=\\"submitNews\\" value=\\"{TXT_SUBMIT_NEWS}\\" /></td>\r\n        </tr>\r\n    </tbody>\r\n</table>\r\n</form>', 'Newsanmelden', 'submit', 'y', 495, 'on', 'system', 1, '1')
SQL
				),
			9	=> array(
				''	=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (350, 9, '{MEDIA_JAVASCRIPT}\r\n<table id=\\"media\\" cellspacing=\\"0\\" cellpadding=\\"5\\" width=\\"100%\\" border=\\"0\\">\r\n    <tbody>\r\n        <tr class=\\"head\\">\r\n            <td align=\\"center\\" width=\\"16\\"><strong>#</strong></td>\r\n            <td colspan=\\"2\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_NAME_HREF}\\"><strong>{TXT_MEDIA_FILE_NAME}</strong></a> {MEDIA_NAME_ICON}</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_SIZE_HREF}\\" name=\\"sort_size\\"><strong>{TXT_MEDIA_FILE_SIZE}</strong></a> {MEDIA_SIZE_ICON} </td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_TYPE_HREF}\\" name=\\"sort_type\\"><strong>{TXT_MEDIA_FILE_TYPE}</strong></a> {MEDIA_TYPE_ICON} </td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_DATE_HREF}\\" name=\\"sort_date\\"><strong>{TXT_MEDIA_FILE_DATE}</strong></a> {MEDIA_DATE_ICON} </td>\r\n        </tr>\r\n        <tr class=\\"row2\\" valign=\\"middle\\">\r\n            <td align=\\"center\\" width=\\"16\\"><img title=\\"base\\" height=\\"16\\" alt=\\"base\\" src=\\"images/modules/media/_base.gif\\" width=\\"16\\"/> </td>\r\n            <td colspan=\\"5\\"><strong><a title=\\"{MEDIA_TREE_NAV_MAIN}\\" href=\\"{MEDIA_TREE_NAV_MAIN_HREF}\\">{MEDIA_TREE_NAV_MAIN}</a></strong> <!-- BEGIN mediaTreeNavigation --><a href=\\"{MEDIA_TREE_NAV_DIR_HREF}\\">&nbsp;{MEDIA_TREE_NAV_DIR} /</a> <!-- END mediaTreeNavigation --></td>\r\n        </tr>\r\n        <!-- BEGIN mediaDirectoryTree -->\r\n        <tr class=\\"{MEDIA_DIR_TREE_ROW}\\" valign=\\"middle\\">\r\n            <td width=\\"16\\">&nbsp;</td>\r\n            <td align=\\"center\\" width=\\"16\\"><img title=\\"icon\\" height=\\"16\\" alt=\\"icon\\" src=\\"{MEDIA_FILE_ICON}\\" width=\\"16\\"/></td>\r\n            <td width=\\"100%\\"><a title=\\"{MEDIA_FILE_NAME}\\" href=\\"{MEDIA_FILE_NAME_HREF}\\">{MEDIA_FILE_NAME}</a></td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_SIZE}&nbsp;</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_TYPE}&nbsp;</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_DATE}&nbsp;</td>\r\n        </tr>\r\n        <!-- END mediaDirectoryTree --><!-- BEGIN mediaEmptyDirectory -->\r\n        <tr class=\\"row1\\">\r\n            <td>&nbsp;</td>\r\n            <td colspan=\\"5\\">{TXT_MEDIA_DIR_EMPTY}</td>\r\n        </tr>\r\n        <!-- END mediaEmptyDirectory -->\r\n    </tbody>\r\n</table>', 'Media Archiv #1', '', 'y', 0, 'on', 'system', 1, '2')
SQL
				),
			10	=> array(
				''	=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (315, 10, '<b><a href="index.php?section=guestbook&amp;cmd=post" title="Eintragen">Eintragen</a></b><br />\r\n{GUESTBOOK_TOTAL_ENTRIES} Einträge im Gästebuch.<br />\r\n{GUESTBOOK_PAGING}<br />\r\n{GUESTBOOK_STATUS}\r\n<table cellspacing="1" cellpadding="1" width="100%" border="0">\r\n	<!-- BEGIN guestbook_row -->\r\n		<tr class="{GUESTBOOK_ROWCLASS}"> \r\n			<td valign="top"><img alt="" hspace="0" src="images/modules/guestbook/post.gif"  border="0" />{GUESTBOOK_DATE} - <strong>{GUESTBOOK_NICK}</strong> {GUESTBOOK_GENDER} {GUESTBOOK_LOCATION}</td>\r\n			<td  valign="top" nowrap="nowrap"><div align="right">{GUESTBOOK_EMAIL} {GUESTBOOK_URL} </div></td>\r\n		</tr>\r\n		<tr class="{GUESTBOOK_ROWCLASS}"> \r\n			<td valign="top" colspan="2"><hr noshade="noshade" size="1" />{GUESTBOOK_COMMENT}<br /><br /></td>\r\n		</tr>\r\n	<!-- END guestbook_row -->\r\n</table>\r\n<br />', 'Gästebuch', '', 'n', 0, 'on', 'system', 8, '1')
SQL
				,
				'post'	=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (316, 10, '{GUESTBOOK_JAVASCRIPT}\r\nSie können sich hier ins Gästebuch eintragen. <br /> Damit der Eintrag klappt, sollten mindestens alle mit einem <font color="red">*</font> \r\nmarkierten Felder ausgefüllt werden. \r\n<br />\r\n<form name="GuestbookForm" action="index.php?section=guestbook" method="post" onsubmit="return validate(this)">\r\n<br />\r\n<b>Name:</b><font color="red"> *</font> <br />\r\n<input style="width: 350px;" maxlength="255" size="60" name="nickname" id="nickname" /> <br /><br /><b>Kommentar:</b><font color="red"> *</font> \r\n<br />\r\n<textarea style="width: 350px;" name="comment" id="comment" rows="6" cols="60"></textarea><br /><br /><b>Geschlecht: </b><font color="red">*</font>\r\n<br />\r\n<input type="radio" checked="checked" value="F" name="malefemale" /> Weiblich<br />\r\n<input type="radio" value="M" name="malefemale" /> Männlich<br /><br /><b>Wohnort:</b> <font color="red">*</font>\r\n<br />\r\n<input style="width: 350px;" maxlength="255" size="60" name="location" id="location" /> <br /><b>E-mail:</b>&nbsp;<font color="#ff0000">*</font>\r\n<br />\r\n<input style="width: 350px;" maxlength="255" size="60" name="email" id="email" /> <br /><b>Homepage:</b>\r\n<br />\r\n<input style="width: 350px;" name="url" value="http://" size="60" maxlength="255" /> \r\n<br />\r\n<br />\r\n<input type="reset" value="&nbsp;Reset&nbsp;" name="Submit" />&nbsp;&nbsp;\r\n<input type="submit" value="&nbsp;Speichern&nbsp;" name="Submit" /> \r\n</form>', 'Eintragen', 'post', 'y', 315, 'on', 'system', 1, '1')
SQL
				),
			11	=> array(
				''	=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (441, 11, '<table width=\\"100%\\">\r\n<!-- BEGIN sitemap -->\r\n<tr>\r\n<td id=\\"{STYLE}\\">{SPACER}<a href=\\"{URL}\\" title=\\"{NAME}\\">{NAME}</a></td>\r\n</tr>\r\n<!-- END sitemap -->\r\n</table>', 'Sitemap', '', 'y', 0, 'on', 'system', 111, '1')
SQL
				),
			13	=> array(
				''	=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (345, 13, '<p>Ihre Eingabe wurde vom <b>ASTALAVISTA&reg; Angriffserkennungs System</b> als unzul&auml;ssig erkannt. <br/><br/>Einige besondere Zeichenfolgen werden vom Intrusion Detection System gefiltert und vom Intrusion Response System blockiert. Wenn Sie finden, dass diese Meldung unrechterweise erscheint, nehmen Sie doch bitte mit uns <a href="mailto:ivan.schmid%20AT%20astalavista%20DOT%20ch">Kontakt</a> auf.<br/><br/><i><b>Aktive Arbitrary Input Module:</b></i> \r\n</p><ul>\r\n<li>SQL Injection \r\n</li><li>Cross-Site Scripting \r\n</li><li>Session Hijacking<br/><br/></li></ul>', 'Alert System', '', 'n', 0, 'off', 'system', 111, '1')
SQL
				),
			14	=> array(
				''	=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (344, 14, '<table cellspacing="0" cellpadding="0" width="100%" border="0">\r\n<tbody>\r\n<tr>\r\n<td scope="col">\r\n<div align="left">{ERROR_NUMBER} {ERROR_MESSAGE} <br /><br /><strong>Das gewünschte Dokument existiert nicht an dieser Stelle.</strong><br /><br />Das von Ihnen gesuchte Dokument wurde möglicherweise umbenannt, verschoben oder gelöscht. Es existieren mehrere Möglichkeiten, um ein Dokument zu finden. Sie können auf die Homepage zurückkehren, das Dokument mit Stichworten suchen oder unsere Help Site konsultieren. Um von der letztbesuchten Seite aus weiterzufahren, klicken Sie bitte auf die Schaltfläche ''Zurück'' Ihres Browsers. <br /><br />The document you requested does not exist at this location.<br />The document you are looking for may have been renamed, moved or deleted. There are several ways to locate a document. You can return to the Homepage, search for the document using keywords or consult our Help Site. To continue on from the last page you visited, please press the ''Back'' button of your browser. <br /></div></td></tr></tbody></table>', 'Fehlermeldung', '', 'n', 0, 'off', 'system', 111, '1')
SQL
				),
			15	=> array(
				''	=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (305, 15, '<p>Herzlich Willkommen auf der deutschsprachigen <strong>Contrexx&reg; CMS Demo Website</strong>! </p>\r\n<p>Contrexx&reg; erm&ouml;glicht Ihnen individuelles, eigenh&auml;ndiges und einfaches Aktualisieren Ihrer Website, Ihres Intranets oder Extranets. Weder HTML- noch Programmierungskenntnisse sind notwendig, um Ihr Projekt auf dem neusten Stand zu halten. Bearbeiten Sie Ihren Internet-Auftritt als w&uuml;rden Sie mit Word arbeiten. Viele integrierte Module wie etwa (Newsletter, News, EShop usw.) helfen Ihnen, das Maximum aus Ihrem Internet-Auftritt heraus zu holen.<br/>\r\n<br/>\r\n<strong>Unterhalten Sie Ihren Internetauftritt selbst.&nbsp; Schnell. Einfach. Sicher. <br/>\r\nOhne Software. Ohne Programmierkenntnisse.</strong></p>\r\n<table cellspacing=\\"1\\" cellpadding=\\"1\\" width=\\"100%\\" border=\\"0\\">\r\n    <tbody>\r\n        <tr>\r\n            <td onmouseover=\\"this.style.backgroundColor=\\''#FFD966\\'';\\" style=\\"TEXT-ALIGN: justify\\" onmouseout=\\"this.style.backgroundColor=\\''\\'';\\">&nbsp;Die wichtigsten Vorteile, welche Contrexx&reg; CMS&nbsp;auszeichnen:<br/>\r\n            &gt; Einfache und intuitive Bedienung<br/>\r\n            &gt; Modularer und flexibler Aufbau<br/>\r\n            &gt; Grosse Anzahl an optionalen Modulen<br/>\r\n            &gt; Komfortable Trennung von Design und Inhalt<br/>\r\n            &gt; Browserbasierende Administrationsanwendung<br/>\r\n            &gt; Dezentrale Pflege durch mehrere Benutzer<br/>\r\n            &gt; Bew&auml;hrte und zukunftssichere L&ouml;sung<br/>\r\n            &gt; Geringe Anschaffungs- und Betriebskosten</td>\r\n        </tr>\r\n    </tbody>\r\n</table>\r\n<p>Weitere Informationen zu diesem <a href=\\"http://www.contrexx.com/\\" target=\\"_blank\\">innovativen Web Content Management System</a>.</p>', 'Willkommen', '', 'n', 0, 'on', 'system', 0, '1')
SQL
				),
			16	=> array(
				'success'	=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (405, 16, '<font color="red"><b>{SHOP_STATUS}</b></font><br />', 'Transaktionsstatus', 'success', 'n', 398, 'off', 'system', 7, '1')
SQL
				,
				'details'	=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (406, 16, '{SHOP_JAVASCRIPT_CODE} \r\n{SHOP_MENU}<br />\r\n{SHOP_CART_INFO}<br />\r\n{SHOP_PRODUCT_PAGING}\r\n<table width="100%" cellspacing="4" cellpadding="0" border="0">\r\n<tr> \r\n<td width="100%" height="20" background="images/modules/shop/dotted_line.gif"><img width="1" height="20" border="0" alt="" src="images/modules/shop/pixel.gif" /></td>		\r\n</tr>\r\n</table>\r\n<!-- BEGIN shopProductRow -->\r\n<form method="post" action="index.php?section=shop&amp;cmd=cart" name="{SHOP_PRODUCT_FORM_NAME}" id="{SHOP_PRODUCT_FORM_NAME}">\r\n<input type="hidden" value="{SHOP_PRODUCT_ID}" name="productId" />\r\n<table width="100%" cellspacing="3" cellpadding="1" border="0">\r\n<tr> \r\n<td colspan="4"><strong>{SHOP_PRODUCT_TITLE}</strong></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" style="vertical-align:top;"><a href="{SHOP_PRODUCT_THUMBNAIL_LINK}"><img border="0" alt="{TXT_SEE_LARGE_PICTURE}" src="{SHOP_PRODUCT_THUMBNAIL}" /></a></td>\r\n<td width="75%" colspan="3" valign="top"><small><i><strong>{TXT_PRODUCT_ID}:</strong> {SHOP_PRODUCT_ID}</i></small> <br />\r\n{SHOP_PRODUCT_DESCRIPTION}<br />{SHOP_PRODUCT_DETAILDESCRIPTION}\r\n<br />\r\n<!-- BEGIN shopProductOptionsRow -->\r\n<table width="100%" cellspacing="0" cellpadding="0" border="0">\r\n<tr>\r\n<td>\r\n<strong>{SHOP_PRODUCT_OPTIONS_TITLE}</strong><br><br >\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\n<div id="product_options_layer{SHOP_PRODUCT_ID}" style="display:none;">\r\n<table width="100%" cellspacing="0" cellpadding="0" border="0">\r\n<!-- BEGIN shopProductOptionsValuesRow -->\r\n<tr>\r\n<td width="150" style="vertical-align:top;">\r\n{SHOP_PRODUCT_OPTIONS_NAME}:\r\n</td>\r\n<td>{SHOP_PRODCUT_OPTION}</td>\r\n</tr>\r\n<!-- END shopProductOptionsValuesRow -->\r\n</table>\r\n</div>\r\n</td>\r\n</tr>\r\n</table>\r\n<!-- END shopProductOptionsRow -->\r\n<br />{SHOP_PRODUCT_STOCK}<br />{SHOP_MANUFACTURER_LINK}</td>\r\n</tr>\r\n<tr>     \r\n<td colspan="4">{SHOP_PRODUCT_DETAILLINK}</td>\r\n</tr>\r\n<tr>   \r\n<td colspan="3"><b><font color="red">{SHOP_PRODUCT_DISCOUNTPRICE} {SHOP_PRODUCT_DISCOUNTPRICE_UNIT} </font> {SHOP_PRODUCT_PRICE} {SHOP_PRODUCT_PRICE_UNIT} </b>\r\n</td>   \r\n<td>\r\n<div align="right"><input type="submit" value="{TXT_ADD_TO_CARD}" name="{SHOP_PRODUCT_SUBMIT_NAME}" onclick="{SHOP_PRODUCT_SUBMIT_FUNCTION}" /></div></td>\r\n</td>\r\n</tr>\r\n<tr>   \r\n<td height="20" background="images/modules/shop/dotted_line.gif" colspan="4"><img width="1" height="20" border="0" alt="" src="images/modules/shop/pixel.gif" /></td>\r\n</tr>	\r\n</table>\r\n</form>\r\n<!-- END shopProductRow -->\r\n<p>{SHOP_PRODUCT_PAGING}', 'Detaillierte Produktedaten', 'details', 'y', 398, 'off', 'system', 97, '1')
SQL
				,
				'confirm'	=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (404, 16, '<b><font color="#ff0000">{SHOP_STATUS}</font></b>\r\n<!-- BEGIN shopConfirm -->\r\n<form action="index.php?section=shop&amp;cmd=confirm" name="shopForm" method="post">\r\n  <table cellspacing="1" cellpadding="2" width="100%" border="0">\r\n  <tr>\r\n    <td nowrap="nowrap" colspan="5"><b>{TXT_ORDER_INFOS}</b></td>\r\n  </tr>\r\n  <tr>\r\n    <td nowrap><b>{TXT_ID}</b></td>\r\n    <td><b>{TXT_PRODUCT}</b></td>\r\n    <td nowrap><b>{TXT_UNIT_PRICE}</b></td>\r\n    <td nowrap><b>{TXT_QUANTITY}</b></td>\r\n    <td nowrap><div align="right"><b>{TXT_TOTAL}</b></div></td>\r\n  </tr>\r\n  <tr>\r\n    <td colspan="5" nowrap><hr width="100%" color="#cccccc" noShade size="1" />\r\n    </td>\r\n  </tr>\r\n  <!-- BEGIN shopCartRow -->\r\n  <tr style="vertical-align:top;">\r\n    <td nowrap>{SHOP_PRODUCT_ID}</td>\r\n    <td>{SHOP_PRODUCT_TITLE}</td>\r\n    <td nowrap>{SHOP_PRODUCT_ITEMPRICE} {SHOP_UNIT}</td>\r\n    <td nowrap>{SHOP_PRODUCT_QUANTITY}</td>\r\n    <td nowrap><div align="right">{SHOP_PRODUCT_PRICE} {SHOP_UNIT}<br>\r\n    </div></td>\r\n  </tr>\r\n  <!-- END shopCartRow -->\r\n  <tr>\r\n    <td colspan="5"><hr width="100%" color="#cccccc" noShade size=1>\r\n    </td>\r\n  </tr>\r\n<tr>\r\n<td colspan="3" valign="top"><b>{TXT_INTER_TOTAL}</b>{SHOP_TAX_PRODUCTS_TXT}</td>\r\n<td valign="top" nowrap="nowrap">{SHOP_TOTALITEM} {TXT_PRODUCT_S}</td>\r\n<td width="30%" colspan="-1" valign="top" nowrap="nowrap">\r\n<div align="right"><b>{SHOP_TOTALPRICE} {SHOP_UNIT}</b></div></td></tr>\r\n<tr>\r\n<td colspan="5">\r\n<hr width="100%" color="#cccccc" noshade="noshade" size="1" /></td></tr>\r\n<tr valign="top">\r\n<td colspan="4"><strong>{TXT_SHIPPING_METHOD}:</strong> {SHOP_SHIPMENT}\r\n</td>\r\n<td><div align="right">{SHOP_SHIPMENT_PRICE} {SHOP_UNIT}</div></td>\r\n</tr>\r\n<tr valign="top">\r\n<td colspan="4"><strong>{TXT_PAYMENT_TYPE}:</strong> {SHOP_PAYMENT}  \r\n</td>\r\n<td><div align="right"> {SHOP_PAYMENT_PRICE} {SHOP_UNIT}</div></td>\r\n</tr>\r\n<tr>\r\n<td colspan="4" valign="top" nowrap="nowrap">{TXT_PROCENTUAL_TAX_PART}: {SHOP_TAX_PROCENTUAL}</td>\r\n<td width="30%" colspan="-1" valign="top" nowrap="nowrap">\r\n<div align="right">{TXT_TAX_PART} {SHOP_TAX_PRICE} {SHOP_UNIT}</div></td></tr>\r\n<tr>\r\n<td colspan="4" valign="top" nowrap="nowrap"><b>{TXT_TOTAL_PRICE}</b>{SHOP_TAX_GRAND_TXT}</td>\r\n<td width="30%" colspan="-1" valign="top" nowrap="nowrap">\r\n<div align="right"><b>{SHOP_GRAND_TOTAL} {SHOP_UNIT}</b></div></td></tr>\r\n<tr>\r\n<td colspan="5">\r\n<hr width="100%" color="#000000" noshade="noshade" size="1" /></td></tr>\r\n<tr>\r\n  <td colspan="5">\r\n  <TABLE  cellSpacing=2 cellPadding=1 width="100%" border=0>\r\n      <TR>\r\n        <TD noWrap rowspan="2" width="49%"><b>{TXT_ADDRESS_CUSTOMER}</b></TD>\r\n        <TD rowspan="2" width="1%"></TD>\r\n      </TR>\r\n      <TR>\r\n        <TD width="50%"><b>{TXT_SHIPPING_ADDRESS}</b></TD>\r\n      </TR>\r\n      <TR valign="top">\r\n        <TD noWrap width="49%">{SHOP_COMPANY}<br>\r\n        {SHOP_PREFIX}<br>\r\n        {SHOP_LASTNAME}<br>\r\n        {SHOP_FIRSTNAME}<br>\r\n        {SHOP_ADDRESS}<br>\r\n        {SHOP_ZIP} {SHOP_CITY}<br>\r\n        {SHOP_COUNTRY}<br>\r\n        <br>\r\n        {SHOP_PHONE}<br>\r\n        {SHOP_FAX}<br>\r\n        {SHOP_EMAIL}</TD>\r\n        <TD width="1%"></TD>\r\n        <TD width="50%">{SHOP_COMPANY2}<br>\r\n        {SHOP_PREFIX2}<br>\r\n        {SHOP_LASTNAME2}<br>\r\n        {SHOP_FIRSTNAME2}<br>\r\n        {SHOP_ADDRESS2}<br>\r\n        {SHOP_ZIP2} {SHOP_CITY2}<br>\r\n        {SHOP_COUNTRY2}<br>\r\n        <br>\r\n        {SHOP_PHONE2}</TD>\r\n      </TR>\r\n      <TR>\r\n        <TD noWrap colspan="3"><hr width="100%" color="black" noShade size="1" />\r\n        </TD>\r\n      </TR>\r\n  </TABLE>\r\n  </td>\r\n</tr>\r\n<tr>\r\n  <td colspan="5"><b>{TXT_COMMENTS}</b></td>\r\n</tr>\r\n<tr>\r\n  <td colspan="4">{SHOP_CUSTOMERNOTE}</td>\r\n  <td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n  <td colspan="5"><hr width="100%" color="#000000" noshade="noshade" size="1" /></td>\r\n</tr>\r\n<tr>\r\n  <td colspan="5"><div align="right"><input type="submit" value="{TXT_ORDER_NOW}" name="process" /></div></td>\r\n</tr>\r\n</table>\r\n</form>\r\n<!-- END shopConfirm -->\r\n<!-- BEGIN shopProcess -->\r\n{TXT_ORDER_PREPARED} <br/>\r\n{SHOP_PAYMENT_PROCESSING}\r\n<!-- END shopProcess -->', 'Bestellen', 'confirm', 'y', 398, 'off', 'system', 6, '1')
SQL
				,
				'payment'	=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (403, 16, '<b><font color="#ff0000">{SHOP_STATUS}</font></b>\r\n<form action="index.php?section=shop&amp;cmd=payment" name="shopForm" method="post">\r\n<table cellspacing="0" cellpadding="0" width="100%" border="0">\r\n<tr valign="middle">\r\n<td align="center">\r\n<table cellspacing="1" cellpadding="2" width="100%" border="0">\r\n<tr>\r\n<td nowrap="nowrap" colspan="2"><b>{TXT_PRODUCTS}</b></td></tr>\r\n<tr>\r\n<td nowrap="nowrap" colspan="2">\r\n<hr width="100%" color="#000000" noshade="noshade" size="1" /></td></tr>\r\n<tr>\r\n<td valign="top"><b>{TXT_TOTALLY_GOODS} </b>{SHOP_TAX_PRODUCTS_TXT}<b>&nbsp;&nbsp;&nbsp;&nbsp;</b>{SHOP_TOTALITEM} {TXT_PRODUCT_S}</td>\r\n<td width="30%" colspan="-1" valign="top" nowrap="nowrap">\r\n<div align="right"><b>{SHOP_TOTALPRICE} {SHOP_UNIT}</b></div></td></tr>\r\n<tr>\r\n<td colspan="2">\r\n<hr width="100%" color="#cccccc" noshade="noshade" size="1" /></td></tr>\r\n<tr valign="top">\r\n<td><strong>{TXT_SHIPPING_METHODS}</strong><br />\r\n{SHOP_SHIPMENT_MENU}\r\n</td>\r\n<td><div align="right"><br>\r\n  {SHOP_SHIPMENT_PRICE} {SHOP_UNIT}</div></td>\r\n</tr>\r\n<tr valign="top">\r\n<td><strong>{TXT_PAYMENT_TYPES}</strong><br />\r\n{SHOP_PAYMENT_MENU}  \r\n</td>\r\n<td><div align="right"> <br>\r\n  {SHOP_PAYMENT_PRICE} {SHOP_UNIT}</div></td>\r\n</tr>\r\n<tr>\r\n<td valign="top" nowrap="nowrap">{TXT_PROCENTUAL_TAX_PART} {SHOP_TAX_PROCENTUAL}</td>\r\n<td width="30%" colspan="-1" valign="top" nowrap="nowrap">\r\n<div align="right">{TXT_TAX_PART} {SHOP_TAX_PRICE} {SHOP_UNIT}</div></td></tr>\r\n<tr>\r\n<td valign="top" nowrap="nowrap"><b>{TXT_TOTAL_PRICE}</b>{SHOP_TAX_GRAND_TXT}</td>\r\n<td width="30%" colspan="-1" valign="top" nowrap="nowrap">\r\n<div align="right"><b>{SHOP_GRAND_TOTAL} {SHOP_UNIT}</b></div></td></tr>\r\n<tr>\r\n<td colspan="2">\r\n<hr width="100%" color="#000000" noshade="noshade" size="1" /></td></tr>\r\n<tr>\r\n<td colspan="2"><b>{TXT_COMMENTS}</b></td>\r\n</tr>\r\n<tr>\r\n<td colspan="2"><textarea name="customer_note" rows="4" cols="52">{SHOP_CUSTOMERNOTE}</textarea> \r\n</td></tr>\r\n<tr>\r\n<td colspan="2">\r\n<hr width="100%" color="#000000" noshade="noshade" size="1" /></td></tr>\r\n<tr>\r\n<td colspan="2"><b>{TXT_TAC}</b></td></tr>\r\n<tr>\r\n<td colspan="2">\r\n<input type="checkbox" value="checked" name="agb" {SHOP_AGB} /> <font color="#ff0000">&nbsp;</font>{TXT_ACCEPT_TAC}</td></tr>\r\n<tr>\r\n<td colspan="2">\r\n<input type="submit" value="{TXT_UPDATE}" name="refresh" /> \r\n<input type="submit" value="{TXT_NEXT}" name="check" /> \r\n</td>\r\n</tr>\r\n</table>\r\n</td>\r\n</tr>\r\n</table>\r\n</form>', 'Bezahlung und Versand', 'payment', 'y', 398, 'off', 'system', 5, '1')
SQL
				,
				'account'	=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (401, 16, '<script language=\\"JavaScript\\" type=\\"text/javascript\\">\r\n<!--  \r\nfunction shopCopyText()  \r\n{\r\n	with (document.shop){\r\n		if(equalAddress.checked) {\r\n			prefix2.value= prefix.value;\r\n			company2.value= company.value;\r\n			lastname2.value= lastname.value;\r\n			firstname2.value= firstname.value;\r\n			address2.value=address.value;\r\n			zip2.value= zip.value;\r\n			city2.value= city.value;\r\n			phone2.value= phone.value;				\r\n			return true;\r\n		} else {	\r\n			prefix2.value= \\"\\";\r\n			company2.value= \\"\\";\r\n			lastname2.value= \\"\\";\r\n			firstname2.value= \\"\\";\r\n			address2.value=\\"\\";\r\n			zip2.value= \\"\\";\r\n			city2.value= \\"\\";\r\n			phone2.value= \\"\\";\r\n			return true;\r\n		}\r\n	}\r\n}\r\n-->\r\n</script>\r\n<form name=\\"shop\\" action=\\"{SHOP_ACCOUNT_ACTION}\\" method=\\"post\\">\r\n<table cellspacing=\\"2\\" cellpadding=\\"1\\" width=\\"100%\\" border=\\"0\\">\r\n<tbody>\r\n<tr>\r\n<td colspan=\\"2\\"><b>{TXT_CUSTOMER_ADDRESS}</b></td>\r\n</tr>\r\n<tr>\r\n<td colspan=\\"2\\"><font color=\\"#ff0000\\">* </font>{TXT_REQUIRED_FIELDS}<br />\r\n  <table cellspacing=\\"0\\" cellpadding=\\"0\\" width=\\"100%\\" border=\\"0\\">\r\n    <tbody>\r\n      <tr>\r\n        <td><b><font color=\\"#ff0000\\">{SHOP_ACCOUNT_STATUS}</font></b></td>\r\n      </tr>\r\n    </tbody>\r\n  </table>\r\n  </td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n  <td colspan=\\"2\\" nowrap=\\"nowrap\\">&nbsp;  </td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_COMPANY}</td>\r\n<td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_COMPANY}\\" name=\\"company\\" tabindex=\\"1\\" /> </td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_GREETING}</td>\r\n<td><input maxlength=\\"50\\" size=\\"30\\" value=\\"{SHOP_ACCOUNT_PREFIX}\\" name=\\"prefix\\" tabindex=\\"2\\" /> <b><font color=\\"#ff0000\\">*</font></b></td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_SURNAME}</td>\r\n<td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_LASTNAME}\\" name=\\"lastname\\" tabindex=\\"3\\" /> <b><font color=\\"#ff0000\\">*</font></b></td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_FIRSTNAME}&nbsp;&nbsp;</td>\r\n<td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_FIRSTNAME}\\" name=\\"firstname\\" tabindex=\\"4\\" /> <b><font color=\\"#ff0000\\">*</font></b></td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_ADDRESS}</td>\r\n<td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_ADDRESS}\\" name=\\"address\\" tabindex=\\"5\\" /> <b><font color=\\"#ff0000\\">*</font></b></td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_POSTALE_CODE}</td>\r\n<td><input size=\\"6\\" value=\\"{SHOP_ACCOUNT_ZIP}\\" name=\\"zip\\" tabindex=\\"6\\" /> <b><font color=\\"#ff0000\\">*</font></b> {TXT_CITY} <input value=\\"{SHOP_ACCOUNT_CITY}\\" name=\\"city\\" tabindex=\\"7\\" /> <b><font color=\\"#ff0000\\">*</font></b> </td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_COUNTRY}</td>\r\n<td>{SHOP_ACCOUNT_COUNTRY}</td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_PHONE_NUMBER}</td>\r\n<td><input value=\\"{SHOP_ACCOUNT_PHONE}\\" name=\\"phone\\" tabindex=\\"8\\" /> <b><font color=\\"#ff0000\\">*</font></b></td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_FAX_NUMBER}</td>\r\n<td><input value=\\"{SHOP_ACCOUNT_FAX}\\" name=\\"fax\\" tabindex=\\"9\\" /> </td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n  <td colspan=\\"2\\" nowrap=\\"nowrap\\"><hr width=\\"100%\\" color=\\"#000000\\" noshade=\\"noshade\\" size=\\"1\\" /></td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"nowrap\\"><b>{TXT_SHIPPING_ADDRESS}</b></td>\r\n  <td>&nbsp;</td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"nowrap\\">&nbsp;</td>\r\n  <td><input type=\\"checkbox\\" value=\\"checked\\" name=\\"equalAddress\\" onClick=\\"shopCopyText();\\" {SHOP_ACCOUNT_EQUAL_ADDRESS} tabindex=\\"10\\" />\r\n{TXT_SAME_BILLING_ADDRESS}</td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_COMPANY}</td>\r\n  <td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_COMPANY2}\\" name=\\"company2\\" tabindex=\\"11\\" /></td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_GREETING}</td>\r\n  <td><input maxlength=\\"50\\" size=\\"30\\" value=\\"{SHOP_ACCOUNT_PREFIX2}\\" name=\\"prefix2\\" tabindex=\\"12\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b></td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_SURNAME}</td>\r\n  <td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_LASTNAME2}\\" name=\\"lastname2\\" tabindex=\\"13\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b></td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_FIRSTNAME}&nbsp;&nbsp; </td>\r\n  <td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_FIRSTNAME2}\\" name=\\"firstname2\\" tabindex=\\"14\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b></td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_ADDRESS}</td>\r\n  <td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_ADDRESS2}\\" name=\\"address2\\" tabindex=\\"15\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b></td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_POSTALE_CODE}</td>\r\n  <td><input size=\\"6\\" value=\\"{SHOP_ACCOUNT_ZIP2}\\" name=\\"zip2\\" tabindex=\\"16\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b> {TXT_CITY}\r\n      <input value=\\"{SHOP_ACCOUNT_CITY2}\\" name=\\"city2\\" tabindex=\\"17\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b> </td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_COUNTRY}</td>\r\n  <td>{SHOP_ACCOUNT_COUNTRY2}</td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_PHONE_NUMBER}</td>\r\n  <td><input value=\\"{SHOP_ACCOUNT_PHONE2}\\" name=\\"phone2\\" tabindex=\\"18\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b></td>\r\n  </tr>\r\n<!-- BEGIN account_details -->\r\n<tr valign=\\"top\\">\r\n  <td colspan=\\"2\\" nowrap=\\"NOWRAP\\"><hr width=\\"100%\\" color=\\"#000000\\" noshade=\\"noshade\\" size=\\"1\\" /></td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\"><b>{TXT_YOUR_ACCOUNT_DETAILS}</b></td>\r\n  <td>&nbsp;</td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"nowrap\\">{TXT_EMAIL}</td>\r\n  <td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_EMAIL}\\" name=\\"email\\" tabindex=\\"19\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b> </td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"nowrap\\">{TXT_PASSWORD}</td>\r\n  <td><input type=\\"password\\" size=\\"30\\" value=\\"\\" name=\\"password\\" tabindex=\\"20\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b><br />\r\n    {TXT_PASSWORD_MIN_CHARS}</td>\r\n</tr>\r\n<!-- END account_details -->\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">&nbsp;</td>\r\n  <td>&nbsp;</td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n  <td colspan=\\"2\\" nowrap=\\"NOWRAP\\"><input type=\\"reset\\" value=\\"{TXT_RESET}\\" name=\\"reset\\" tabindex=\\"21\\" />\r\n    <input type=\\"submit\\" value=\\"{TXT_NEXT}  >>\\" name=\\"Submit\\" tabindex=\\"22\\" /></td>\r\n  </tr>\r\n</tbody>\r\n</table>\r\n<br />\r\n</form>', 'Kontoangaben', 'account', 'y', 398, 'off', 'system', 4, '1')
SQL
				,
				'cart'		=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (400, 16, '<!-- BEGIN shopCart -->\r\n<form action =\\"index.php?section=shop&amp;cmd=cart\\" name=\\"shopForm\\" method =\\"post\\">\r\n  <table width=\\"100%\\" cellpadding=\\"0\\" cellspacing=\\"0\\" border=\\"0\\">\r\n    <tr valign=\\"middle\\"> \r\n      <td align=\\"center\\">\r\n        <table width=\\"100%\\" border=\\"0\\" cellpadding=\\"2\\" cellspacing=\\"1\\">\r\n          <tr> \r\n            <td colspan=\\"5\\"> \r\n              <hr width=\\"100%\\" noshade=\\"noshade\\" color=\\"black\\" size=\\"1\\" />\r\n            </td>\r\n          </tr>\r\n          <tr valign=\\"top\\"> \r\n            <td width=\\"10%\\"><div align=\\"left\\"><b>{TXT_PRODUCT_ID}</b></div></td>\r\n            <td width=\\"45%\\"><div align=\\"left\\"><b>{TXT_PRODUCT}</b></div></td>\r\n            <td width=\\"15%\\"><div align=\\"left\\"><b>{TXT_UNIT_PRICE}</b></div></td>\r\n            <td width=\\"12%\\"><div align=\\"left\\"><b>{TXT_QUANTITY}</b></div></td>\r\n            <td width=\\"25%\\"><div align=\\"right\\"><b>{TXT_TOTAL}</b></div></td>\r\n          </tr>\r\n          <tr> \r\n            <td colspan=\\"5\\" valign=\\"top\\"> \r\n              <hr width=\\"100%\\" color=\\"#cccccc\\" noshade=\\"noshade\\" size=\\"1\\" />\r\n            </td>\r\n          </tr>\r\n          <!-- BEGIN shopCartRow -->\r\n          <tr valign=\\"top\\"> \r\n            <td><div align=\\"left\\">{SHOP_PRODUCT_ID}</div></td>\r\n            <td><div align=\\"left\\"><a href =\\"?section=shop&amp;cmd=details&amp;referer=cart&amp;productId={SHOP_PRODUCT_CART_ID}\\">{SHOP_PRODUCT_TITLE}</a>{SHOP_PRODUCT_OPTIONS}</div></td>\r\n            <td><div align=\\"left\\">{SHOP_PRODUCT_ITEMPRICE} {SHOP_PRODUCT_ITEMPRICE_UNIT}</div></td>\r\n            <td><div align=\\"left\\"><input class=\\"form\\" type=\\"text\\" name=\\"quantity[{SHOP_PRODUCT_CART_ID}]\\" value=\\"{SHOP_PRODUCT_QUANTITY}\\" size=\\"3\\" />\r\n            </div></td>\r\n            <td width=\\"25%\\"> \r\n              <div align=\\"right\\">{SHOP_PRODUCT_PRICE} {SHOP_PRODUCT_PRICE_UNIT} </div>\r\n            </td>\r\n          </tr>\r\n          <!-- END shopCartRow -->\r\n          <tr> \r\n            <td colspan=\\"5\\" valign=\\"top\\"> \r\n              <hr width=\\"100%\\" color=\\"#cccccc\\" noshade=\\"noshade\\" size=\\"1\\" />\r\n            </td>\r\n          </tr>\r\n          <tr> \r\n            <td colspan=\\"3\\" valign=\\"top\\"><div align=\\"left\\"><b>{TXT_INTER_TOTAL}</b></div></td>\r\n            <td width=\\"17%\\" valign=\\"top\\"><div align=\\"left\\"><b>{SHOP_PRODUCT_TOTALITEM}</b></div></td>\r\n            <td width=\\"25%\\" valign=\\"top\\"> \r\n              <div align=\\"right\\"><b>{SHOP_PRODUCT_TOTALPRICE} {SHOP_PRODUCT_TOTALPRICE_UNIT}<br />\r\n                </b> </div>\r\n            </td>\r\n          </tr>\r\n          <tr> \r\n            <td colspan=\\"5\\" valign=\\"top\\"> \r\n              <hr width=\\"100%\\" color=\\"black\\" noshade=\\"noshade\\" size=\\"1\\" />\r\n            </td>\r\n          </tr>\r\n          <tr> \r\n            <td valign=\\"top\\"> \r\n              <strong>{TXT_SHIP_COUNTRY}</strong></td>\r\n            <td colspan=\\"3\\" valign=\\"top\\">{SHOP_COUNTRIES_MENU} </td>\r\n            <td valign=\\"top\\"><div align=\\"right\\">\r\n                <input type=\\"submit\\" name=\\"update\\" value=\\"{TXT_UPDATE}\\" />\r\n            </div></td>\r\n          </tr>\r\n          <tr>\r\n            <td colspan=\\"5\\" valign=\\"top\\">&nbsp;</td>\r\n          </tr>\r\n          <tr>\r\n            <td colspan=\\"5\\" valign=\\"top\\"><div align=\\"right\\">\r\n                <input type=\\"submit\\" name=\\"continue\\" value=\\"{TXT_NEXT}  >>\\" />\r\n            </div></td>\r\n          </tr>\r\n        </table>\r\n      </td>\r\n  </tr>\r\n</table>\r\n</form>\r\n<!-- END shopCart -->\r\n<br />\r\n<b><a href=\\"index.php?section=shop\\" title=\\"{TXT_CONTINUE_SHOPPING}\\">{TXT_CONTINUE_SHOPPING}</a><br />\r\n<a href=\\"index.php?section=shop&amp;act=destroy\\" title=\\"{TXT_EMPTY_CART}\\">{TXT_EMPTY_CART}</a></b>\r\n<br />', 'Ihr Warenkorb', 'cart', 'y', 398, 'on', 'system', 2, '1')
SQL
				,
				'discounts'	=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (399, 16, '<!-- BEGIN shopProductRow1 -->\r\n<table border=\\"0\\" width=\\"100%\\">\r\n<tr valign=\\"top\\"> \r\n<td border=\\"0\\">\r\n<b>{SHOP_PRODUCT_TITLE}</b>\r\n<br>\r\n<a href=\\"{SHOP_PRODUCT_DETAILLINK}\\"><img src=\\"{SHOP_PRODUCT_THUMBNAIL}\\" border=\\"0\\" alt=\\"{SHOP_PRODUCT_TITLE}\\" /></a>\r\n<br />\r\n     {TXT_PRICE_NOW} {SHOP_PRODUCT_DISCOUNTPRICE} {SHOP_PRODUCT_DISCOUNTPRICE_UNIT} {TXT_INSTEAD_OF}\r\n      {SHOP_PRODUCT_PRICE} {SHOP_PRODUCT_PRICE_UNIT} <br>\r\n</td>\r\n<!-- BEGIN shopProductRow2 -->\r\n<td border=\\"0\\" width=\\"50%\\"><b>{SHOP_PRODUCT_TITLE}</b>\r\n<br>\r\n<a href=\\"{SHOP_PRODUCT_DETAILLINK}\\"><img src=\\"{SHOP_PRODUCT_THUMBNAIL}\\" border=\\"0\\" alt=\\"{SHOP_PRODUCT_TITLE}\\"></a>\r\n<br>\r\n      {TXT_PRICE_NOW} {SHOP_PRODUCT_DISCOUNTPRICE} {SHOP_PRODUCT_DISCOUNTPRICE_UNIT} {TXT_INSTEAD_OF} {SHOP_PRODUCT_PRICE} \r\n      {SHOP_PRODUCT_PRICE_UNIT} \r\n</td>\r\n<!-- END shopProductRow2 -->\r\n</tr>\r\n</table>\r\n<!-- END shopProductRow1 -->', 'Sonderangebote', 'discounts', 'y', 398, 'on', 'system', 1, '1')
SQL
				,
				''			=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (398, 16, '{SHOP_JAVASCRIPT_CODE} \r\n{SHOP_MENU}<br />\r\n{SHOP_CART_INFO}<br />\r\n{SHOP_PRODUCT_PAGING}\r\n<table width=\\"100%\\" cellspacing=\\"4\\" cellpadding=\\"0\\" border=\\"0\\">\r\n<tr> \r\n<td width=\\"100%\\" height=\\"20\\" style=\\"background-image: url(images/modules/shop/dotted_line.gif);\\"><img width=\\"1\\" height=\\"20\\" border=\\"0\\" alt=\\"\\" src=\\"images/modules/shop/pixel.gif\\" /></td>		\r\n</tr>\r\n</table>\r\n<!-- BEGIN shopProductRow -->\r\n<form method=\\"post\\" action=\\"index.php?section=shop&amp;cmd=cart\\" name=\\"{SHOP_PRODUCT_FORM_NAME}\\" id=\\"{SHOP_PRODUCT_FORM_NAME}\\">\r\n<input type=\\"hidden\\" value=\\"{SHOP_PRODUCT_ID}\\" name=\\"productId\\" />\r\n<table width=\\"100%\\" cellspacing=\\"3\\" cellpadding=\\"1\\" border=\\"0\\">\r\n<tr> \r\n<td colspan=\\"4\\"><strong>{SHOP_PRODUCT_TITLE}</strong></td>\r\n</tr>\r\n<tr>\r\n<td width=\\"25%\\" style=\\"vertical-align:top;\\"><a href=\\"{SHOP_PRODUCT_THUMBNAIL_LINK}\\"><img border=\\"0\\" alt=\\"{TXT_SEE_LARGE_PICTURE}\\" src=\\"{SHOP_PRODUCT_THUMBNAIL}\\" /></a></td>\r\n<td width=\\"75%\\" colspan=\\"3\\" valign=\\"top\\"><small><i><strong>{TXT_PRODUCT_ID}:</strong> {SHOP_PRODUCT_ID}</i></small> <br />\r\n{SHOP_PRODUCT_DESCRIPTION}<br />\r\n<br />\r\n<!-- BEGIN shopProductOptionsRow -->\r\n<table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"0\\" border=\\"0\\">\r\n<tr>\r\n<td>\r\n<strong>{SHOP_PRODUCT_OPTIONS_TITLE}</strong><br /><br />\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\n<div id=\\"product_options_layer{SHOP_PRODUCT_ID}\\" style=\\"display:none;\\">\r\n<table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"0\\" border=\\"0\\">\r\n<!-- BEGIN shopProductOptionsValuesRow -->\r\n<tr>\r\n<td width=\\"150\\" style=\\"vertical-align:top;\\">\r\n{SHOP_PRODUCT_OPTIONS_NAME}:\r\n</td>\r\n<td>{SHOP_PRODCUT_OPTION}</td>\r\n</tr>\r\n<!-- END shopProductOptionsValuesRow -->\r\n</table>\r\n</div>\r\n</td>\r\n</tr>\r\n</table>\r\n<!-- END shopProductOptionsRow -->\r\n<br />{SHOP_PRODUCT_STOCK}<br />{SHOP_MANUFACTURER_LINK}</td>\r\n</tr>\r\n<tr>     \r\n<td colspan=\\"4\\">{SHOP_PRODUCT_DETAILLINK}</td>\r\n</tr>\r\n<tr>   \r\n<td colspan=\\"3\\"><b><font color=\\"red\\">{SHOP_PRODUCT_DISCOUNTPRICE} {SHOP_PRODUCT_DISCOUNTPRICE_UNIT} </font> {SHOP_PRODUCT_PRICE} {SHOP_PRODUCT_PRICE_UNIT} </b>\r\n</td>   \r\n<td>\r\n<div align=\\"right\\"><input type=\\"submit\\" value=\\"{TXT_ADD_TO_CARD}\\" name=\\"{SHOP_PRODUCT_SUBMIT_NAME}\\" onclick=\\"{SHOP_PRODUCT_SUBMIT_FUNCTION}\\" /></div></td>\r\n</tr>\r\n<tr>   \r\n<td height=\\"20\\" style=\\"background-image: url(images/modules/shop/dotted_line.gif);\\" colspan=\\"4\\"><img width=\\"1\\" height=\\"20\\" border=\\"0\\" alt=\\"\\" src=\\"images/modules/shop/pixel.gif\\" /></td>\r\n</tr>	\r\n</table>\r\n</form>\r\n<!-- END shopProductRow -->\r\n{SHOP_PRODUCT_PAGING}\r\n', 'Online Shop', '', 'y', 0, 'on', 'system', 5, '1')
SQL
				,
				'terms'		=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (407, 16, 'Hier k&ouml;nnen Sie Ihre eigenen Allgemeinen Gesch&auml;ftsbedingungen hineinschreiben.<br/>', 'Allgemeinen Geschäftsbedingungen', 'terms', 'n', 398, 'on', 'system', 98, '1')
SQL
				,
				'login'		=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (408, 16, '<TABLE cellSpacing="2" cellPadding="1" width="100%" border="0">\r\n  <TR> \r\n    <TD colSpan="2"><B>Eine Online-Bestellung ist einfach.</B></TD>\r\n  </TR>\r\n  <TR> \r\n    <TD align=right colSpan=2> \r\n      <DIV align=left><B><FONT color="red">{SHOP_LOGIN_STATUS}</FONT></B></DIV>\r\n    </TD>\r\n  </TR>\r\n  <TR>\r\n    <TD align="right" colSpan="2">\r\n      <hr width="100%" color="black" noShade size="1">\r\n    </TD>\r\n  </TR>\r\n</TABLE>\r\n  <TABLE cellSpacing="2" cellPadding="1" width="100%" border="0">\r\n<FORM name="shop" action="?section=shop&cmd=account" method="post">  \r\n    <TR> \r\n      <TD width="7%"> </TD>\r\n      <TD width="93%"> </TD>\r\n    </TR>\r\n    <TR> \r\n      <TD colSpan=2><b>Ich bin ein neuer Kunde. </b></TD>\r\n    </TR>\r\n    <TR> \r\n      <TD colSpan=2>Durch Ihre Anmeldung bei uns sind Sie in der Lage schneller \r\n        zu bestellen, kennen jederzeit den Status Ihrer Bestellung und haben immer \r\n        eine aktuelle &Uuml;bersicht &uuml;ber Ihre bisherigen Bestellungen.<br>\r\n      </TD>\r\n    </TR>\r\n    <TR> \r\n      <TD colSpan=2> <br>\r\n        <input type=submit value="Weiter &gt;&gt;" name="login">\r\n        <br><br>\r\n      </TD>\r\n    </TR>\r\n    <TR> \r\n      <TD colSpan=2> \r\n        <hr width="100%" color=black noShade size=1>\r\n      </TD>\r\n    </TR>\r\n	</FORM>\r\n  </TABLE>\r\n  <TABLE class=text cellSpacing=2 cellPadding=1 width="100%" border=0>\r\n<FORM name=shop action="{SHOP_LOGIN_ACTION}" method=post>\r\n    <TR> \r\n      <TD colSpan=2><b>Ich bin bereits Kunde.</b></TD>\r\n    </TR>\r\n    <TR> \r\n      <TD width="7%" nowrap>E-Mail Adresse: </TD>\r\n      <TD width="93%"> \r\n        <INPUT maxLength=250 size=30 value="{SHOP_LOGIN_EMAIL}" name="username">\r\n      </TD>\r\n    </TR>\r\n    <TR> \r\n      <TD width="7%">Passwort: </TD>\r\n      <TD width="93%"> \r\n        <INPUT type=password maxLength=50 size=30 name="password">\r\n      </TD>\r\n    </TR>\r\n    <TR> \r\n      <TD width="7%"> </TD>\r\n      <TD width="93%"> \r\n        <INPUT type=submit value="Anmleden &gt;&gt;" name=login>\r\n      </TD>\r\n    </TR>\r\n	</FORM>\r\n  </TABLE>', 'Mein Konto', 'login', 'y', 398, 'off', '', 99, '1')
SQL
				,
				'development'	=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (409, 16, '<table cellspacing=2 cellpadding=1 width="100%" border=0>\r\n  <tbody> \r\n  <tr> \r\n    <td colspan=2><b>Mein Konto</b></td>\r\n  </tr>\r\n  <tr> \r\n    <td colspan=2> Nutzen Sie das Konto um Ihre Bestellungen und Ihre Daten komfortabel \r\n      zu kontrollieren und zu verwalten.<br>\r\n    </td>\r\n  </tr>\r\n  <tr valign="top"> \r\n    <td noWrap rowspan="4" width="20%"><a href="?section=shop&cmd=logout">Log-Out</a><br>\r\n      <a href="?section=shop&cmd=delete">Konto löschen</a></td>\r\n    <td width="92%"> <a href="?section=shop&cmd=orders">Meine Bestellungen \r\n      ansehen</a></td>\r\n  </tr>\r\n  <tr valign="top"> \r\n    <td width="92%"> <a href="?section=shop&cmd=mod">Meine Konto-Daten ändern</a></td>\r\n  </tr>\r\n  <tr valign="top"> \r\n    <td width="92%"> <br>\r\n      <table width="100%" border="0" cellspacing="0" cellpadding="0">\r\n        <tr valign="top"> \r\n          <td colspan="2"><b>eMail-Adresse</b><font color="#C1C0D0"><br>\r\n            </font>{SHOP_EMAIL}</td>\r\n        </tr>\r\n        <tr valign="top"> \r\n          <td><b>Kundennummer</b><font color="#C1C0D0"><br>\r\n            </font>{SHOP_CUSTOMERID}<br>\r\n            <br>\r\n            Zahlungsart<br>\r\n            {SHOP_PAYMENT}</td>\r\n          <td width="61%"><b>Rechnungsadresse</b><font color="#C1C0D0"><br>\r\n            </font>{SHOP_SIGN}<br>\r\n            {SHOP_FIRSTNAME} {SHOP_LASTNAME}<br>\r\n            {SHOP_ADDRESS} <br>\r\n            {SHOP_ZIP}  {SHOP_CITY}<br>\r\n            {SHOP_COUNTRY} </td>\r\n        </tr>\r\n      </table>\r\n      <br>\r\n    </td>\r\n  </tr>\r\n  <tr valign="top"> \r\n    <td> <a href="?section=shop&cmd=modpass">Mein Passwort und </a><a href="?section=shop&cmd=modemail">eMail-Adresse ändern</a> </td>\r\n  </tr>\r\n  </tbody> \r\n</table>', 'Konto Übersicht', 'development', 'y', 408, 'off', '', 0, '1')
SQL
				,
				'sendpass'		=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (410, 16, '<FORM name=shop action={SHOP_CHECKOUT_ACTION} method=post>\r\n  <TABLE class=text cellSpacing=2 cellPadding=1 width="100%" border=0>\r\n    <TBODY> \r\n    <TR> \r\n      <TD colSpan=2><B>Passwort Hilfe</B></TD>\r\n    </TR>\r\n    <TR> \r\n      <TD colSpan=2><B><FONT color=red>{SHOP_PASSWORD_STATUS}</FONT></B></TD>\r\n    </TR>\r\n    <tr> \r\n      <td noWrap colspan="2"><br>\r\n        Geben Sie die E-Mail-Adresse für Ihr Konto bei Sat-com Multimedia \r\n        ein. </td>\r\n    </tr>\r\n    <TR> \r\n      <TD noWrap width="8%"> \r\n        <input size=50 value={SHOP_PASSWORD_EMAIL} name=email>\r\n      </TD>\r\n      <TD width="92%"> \r\n        <input type=submit value=Weiter name=pay>\r\n      </TD>\r\n    </TR>\r\n    <TR> \r\n      <TD noWrap colspan="2"><br>\r\n        Nachdem Sie den "Weiter"-Knopf angeklickt haben, schicken wir \r\n        Ihnen eine Benachrichtigung per E-Mail mit einem neuen Passwort. <br>\r\n        <br>\r\n        <br>\r\n        <br>\r\n        <br>\r\n        Wenn Sie Ihr Passwort vergessen haben und sich Ihre alte E-Mail-Adresse \r\n        nicht weiter verwenden lässt, Sie aber kein neues Konto eröffnen \r\n        wollen, dann können Sie sich telefonisch bei uns melden. </TD>\r\n    </TR>\r\n    </TBODY> \r\n  </TABLE>\r\n  <BR>\r\n<HR width="100%" color=black noShade SIZE=1>\r\n</FORM>', 'Passwort Hilfe', 'sendpass', 'y', 408, 'off', '', 0, '1')
SQL
				),
			17	=> array(
				''				=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (325, 17, '<br />\r\n<form name=\\"VotingForm\\" action=\\"?section=voting\\" method=\\"post\\">\r\n<table width=\\"100%\\" border=\\"0\\">\r\n<tr> \r\n<td><b>{VOTING_TITLE}</b>{VOTING_DATE}</td>\r\n</tr>\r\n<tr> \r\n<td class=\\"desc\\"> {VOTING_RESULTS_TEXT}<br />\r\n{VOTING_RESULTS_TOTAL_VOTES}{TXT_SUBMIT} </td>\r\n</tr>\r\n</table>\r\n</form>\r\n<table width=\\"100%\\" border=\\"0\\">\r\n<tr> \r\n<td valign=\\"top\\" colspan=\\"2\\" class=\\"title\\"><b>{VOTING_OLDER_TITLE}</b></td>\r\n</tr>\r\n<tr> \r\n<td valign=\\"top\\" nowrap=\\"nowrap\\"><b>{TXT_DATE}</b></td>\r\n<td valign=\\"top\\" nowrap=\\"nowrap\\"><b>{TXT_TITLE}</b></td>\r\n</tr>\r\n<!-- BEGIN votingRow -->\r\n<tr class=\\"{VOTING_LIST_CLASS}\\"> \r\n<td nowrap=\\"nowrap\\">{VOTING_OLDER_DATE}</td>\r\n<td nowrap=\\"nowrap\\">{VOTING_OLDER_TEXT}</td>\r\n</tr>\r\n<!-- END votingRow -->\r\n</table>\r\n<br />{VOTING_PAGING}', 'Voting', '', 'y', 0, 'on', 'system', 111, '1')
SQL
				),
			18	=> array(
				'noaccess'		=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (467, 18, '<img width=\\"100\\" height=\\"100\\" src=\\"images/modules/login/stop_hand.gif\\" alt=\\"\\" /><br />{TXT_NOT_ALLOWED_TO_ACCESS}', 'Zugriff verweigert', 'noaccess', 'n', 464, 'off', 'system', 0, '1')
SQL
				,
				'resetpw'		=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (466, 18, '<form method=\\"post\\" action=\\"index.php?section=login&amp;cmd=resetpw\\">\r\n    <input type=\\"hidden\\" name=\\"restore_key\\" value=\\"{LOGIN_RESTORE_KEY}\\" /> <input type=\\"hidden\\" name=\\"username\\" value=\\"{LOGIN_USERNAME}\\" />\r\n    <table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"2\\" border=\\"0\\" summary=\\"set new password form\\">\r\n        <tbody>\r\n            <!-- BEGIN login_reset_password -->\r\n            <tr>\r\n                <td width=\\"70%\\" colspan=\\"2\\">{TXT_SET_PASSWORD_TEXT}</td>\r\n                <td width=\\"30%\\" rowspan=\\"5\\">&nbsp;&nbsp;&nbsp;&nbsp;<img width=\\"32\\" height=\\"32\\" align=\\"middle\\" src=\\"images/modules/login/lost_pw.gif\\" alt=\\"login key\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">{TXT_USERNAME}</td>\r\n                <td width=\\"40%\\">{LOGIN_USERNAME}</td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">{TXT_PASSWORD}&nbsp;{TXT_PASSWORD_MINIMAL_CHARACTERS}</td>\r\n                <td width=\\"40%\\"><input type=\\"password\\" maxlength=\\"50\\" size=\\"30\\" name=\\"password\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\" rowspan=\\"2\\" style=\\"vertical-align: top;\\">{TXT_VERIFY_PASSWORD}</td>\r\n                <td width=\\"40%\\"><input type=\\"password\\" maxlength=\\"50\\" size=\\"30\\" name=\\"password2\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td><input type=\\"submit\\" value=\\"{TXT_SET_NEW_PASSWORD}\\" name=\\"reset_password\\" /></td>\r\n            </tr>\r\n            <!-- END login_reset_password -->\r\n            <tr>\r\n                <td colspan=\\"2\\" style=\\"color: rgb(255, 0, 0); font-weight: bold;\\"><br />{LOGIN_STATUS_MESSAGE}</td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>', 'Neues Passwort setzen', 'resetpw', 'n', 464, 'off', 'system', 0, '1')
SQL
				,
				'lostpw'		=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (465, 18, '<form method=\\"post\\" action=\\"index.php?section=login&amp;cmd=lostpw\\">\r\n    <table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"2\\" border=\\"0\\" summary=\\"lost password form\\">\r\n        <tbody>\r\n            <!-- BEGIN login_lost_password -->\r\n            <tr>\r\n                <td width=\\"70%\\" colspan=\\"2\\">{TXT_LOST_PASSWORD_TEXT}</td>\r\n                <td width=\\"30%\\" rowspan=\\"3\\">&nbsp;&nbsp;&nbsp;&nbsp;<img width=\\"32\\" height=\\"32\\" align=\\"middle\\" src=\\"images/modules/login/lost_pw.gif\\" alt=\\"login key\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\" rowspan=\\"2\\" style=\\"vertical-align: top;\\">{TXT_EMAIL}:</td>\r\n                <td width=\\"40%\\"><input type=\\"text\\" maxlength=\\"255\\" style=\\"width: 100%;\\" size=\\"30\\" name=\\"email\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td><input type=\\"submit\\" name=\\"restore_pw\\" value=\\"{TXT_RESET_PASSWORD}\\" /></td>\r\n            </tr>\r\n            <!-- END login_lost_password -->\r\n            <tr>\r\n                <td colspan=\\"3\\" style=\\"color: rgb(255, 0, 0); font-weight: bold;\\"><br />{LOGIN_STATUS_MESSAGE}</td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>', 'Passwort vergessen?', 'lostpw', 'n', 464, 'off', 'system', 0, '1')
SQL
				,
				''				=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (464, 18, '<form method=\\"post\\" action=\\"index.php?section=login\\" name=\\"loginForm\\">\r\n    <input type=\\"hidden\\" value=\\"{LOGIN_REDIRECT}\\" name=\\"redirect\\" />\r\n    <table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"2\\" border=\\"0\\">\r\n        <tbody>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">{TXT_USER_NAME}:</td>\r\n                <td width=\\"40%\\"><input type=\\"\\" name=\\"USERNAME\\" value=\\"\\" size=\\"30\\" /></td>\r\n                <td width=\\"30%\\" rowspan=\\"3\\">&nbsp;&nbsp;&nbsp;&nbsp;<img width=\\"20\\" height=\\"28\\" align=\\"middle\\" src=\\"/images/modules/login/login_key.gif\\" alt=\\"\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\" rowspan=\\"3\\" style=\\"vertical-align: top;\\">{TXT_PASSWORD}:</td>\r\n                <td width=\\"40%\\"><input type=\\"password\\" name=\\"PASSWORD\\" value=\\"\\" size=\\"30\\" /> </td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"40%\\"><input type=\\"submit\\" name=\\"login\\" value=\\"{TXT_LOGIN}\\" size=\\"15\\" class=\\"input\\" /> </td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"40%\\" colspan=\\"2\\"><a title=\\"{TXT_LOST_PASSWORD}\\" href=\\"index.php?section=login&amp;cmd=lostpw\\">{TXT_PASSWORD_LOST}</a></td>\r\n            </tr>\r\n            <tr>\r\n                <td style=\\"color: rgb(255, 0, 0); font-weight: bold;\\" colspan=\\"3\\"><br />{LOGIN_STATUS_MESSAGE}</td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>', 'Login', '', 'n', 0, 'off', 'system', 130, '1')
SQL
				),
			19	=> array(
				'details'		=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (314, 19, '{DOCSYS_TEXT} <br />\r\nVeröffentlicht am {DOCSYS_DATE} unter dem Titel {DOCSYS_TITLE}\r\n{DOCSYS_AUTHOR} <br />\r\n{DOCSYS_SOURCE}<br />\r\n{DOCSYS_URL} \r\n<br />\r\n{DOCSYS_LASTUPDATE}<br />', 'Documents', 'details', 'y', 313, 'off', 'system', 0, '1')
SQL
				,
				''				=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (313, 19, '<form name=\\"docSys\\" action=\\"index.php?section=docsys\\" method=\\"post\\">\r\n    <select onchange=\\"javascript:this.form.submit()\\" name=\\"category\\">\r\n    <option value=\\"\\" selected=\\"selected\\">{DOCSYS_NO_CATEGORY}</option>\r\n    {DOCSYS_CAT_MENU}</select>\r\n</form>\r\n<br/>\r\n<table id=\\"docsys\\" cellspacing=\\"0\\" cellpadding=\\"2\\" width=\\"100%\\" border=\\"0\\">\r\n        <tr>\r\n            <td nowrap=\\"nowrap\\" width=\\"5%\\"><b>Datum</b></td>\r\n            <td width=\\"100%\\"><b>Titel</b></td>\r\n            <td nowrap=\\"nowrap\\"><b>Kategorie</b></td>\r\n        </tr>\r\n        <!-- BEGIN row -->\r\n        <tr>\r\n            <td nowrap=\\"nowrap\\">{DOCSYS_DATE}&nbsp;&nbsp;</td>\r\n            <td width=\\"100%\\"><b>{DOCSYS_LINK}</b>&nbsp;&nbsp;{DOCSYS_AUTHOR}</td>\r\n            <td nowrap=\\"nowrap\\">{DOCSYS_CATEGORY}</td>\r\n        </tr>\r\n        <!-- END row -->\r\n</table>\r\n<br/>\r\n{DOCSYS_PAGING}<br/>\r\n<br/>', 'Dokumenten System', '', 'y', 0, 'on', 'system', 5, '1')
SQL
				),
			21	=> array(
				'eventlist'		=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (531, 21, '<!-- START calendar_event_list.html --> {CALENDAR_JAVASCRIPT}  {CALENDAR_CATEGORIES}<br />\r\n<table style=\\"width: 100%;\\" class=\\"calendar_eventlist\\">\r\n    <tbody>\r\n        <tr>\r\n            <th style=\\"text-align: left;\\">{TXT_CALENDAR_STARTDATE}</th>\r\n            <th style=\\"text-align: left;\\">{TXT_CALENDAR_TITLE}</th>\r\n            <th style=\\"text-align: left;\\">{TXT_CALENDAR_PLACE}</th>\r\n        </tr>\r\n        <!-- BEGIN event -->\r\n        <tr>\r\n            <td style=\\"width: 110px;\\">{CALENDAR_STARTDATE}&nbsp;{CALENDAR_STARTTIME}</td>\r\n            <td><a href=\\"?section=calendar&amp;cmd=event&amp;id={CALENDAR_ID}\\">{CALENDAR_TITLE}</a></td>\r\n            <td style=\\"width: 80px;\\">{CALENDAR_PLACE}</td>\r\n        </tr>\r\n        <!-- END event -->\r\n    </tbody>\r\n</table>\r\n<!-- END calendar_event_list.html -->', 'Auflistung aller Events', 'eventlist', 'n', 529, 'on', 'system', 1, '1')
SQL
				,
				'boxes'			=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (532, 21, '<!-- START calendar_show.html -->\r\n{CALENDAR_JAVASCRIPT}\r\n\r\n<!-- BEGIN boxes -->\r\n<div style=\\"margin: auto; width: 200px;\\">\r\n{CALENDAR_CATEGORIES}\r\n<br />\r\n{CALENDAR}\r\n</div>\r\n<!-- END boxes -->\r\n\r\n<!-- BEGIN list -->\r\n<div>\r\n{CALENDAR_CATEGORIES}\r\n<br /><br />\r\n<h3>{CALENDAR_DATE}</h3>\r\n<table class=\\"calendar_eventlist\\" style=\\"width: 100%;\\">\r\n	<tr>\r\n		<th style=\\"text-align: left;\\">{TXT_CALENDAR_STARTDATE}</th>\r\n		<th style=\\"text-align: left;\\">{TXT_CALENDAR_TITLE}</th>\r\n		<th style=\\"text-align: left;\\">{TXT_CALENDAR_PLACE}</th>\r\n	</tr>\r\n	<!-- BEGIN event -->\r\n	<tr>\r\n		<td style=\\"width: 80px;\\">{CALENDAR_STARTDATE} {CALENDAR_STARTTIME}</td>\r\n		<td><a href=\\"?section=calendar&amp;cmd=event&amp;id={CALENDAR_ID}\\">{CALENDAR_TITLE}</a></td>\r\n		<td style=\\"width: 80px;\\">{CALENDAR_PLACE}</td>\r\n	</tr>\r\n	<!-- END event -->\r\n</table>\r\n</div>\r\n<!-- END list -->\r\n<!-- END calendar_show.html -->', 'Drei Boxen Ansicht', 'boxes', 'n', 529, 'on', 'system', 111, '1')
SQL
				,
				'event'			=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (530, 21, '<!-- START calendar_show_note.html -->\r\n<table cellspacing=\\"0\\" cellpadding=\\"3\\" width=\\"100%\\" border=\\"0\\">\r\n	<tr>\r\n		<td class=\\"title\\" nowrap=\\"nowrap\\">\r\n			{TXT_CALENDAR_DATE}:\r\n		</td>\r\n		<td class=\\"title\\" nowrap=\\"nowrap\\">\r\n			<b>{CALENDAR_TITLE}</b>\r\n		</td>\r\n	</tr>\r\n	<tr>\r\n		<td width=\\"100\\">\r\n			{TXT_CALENDAR_CAT}:\r\n		</td>\r\n		<td>\r\n			{CALENDAR_CAT}\r\n		</td>\r\n	</tr>\r\n	<tr>\r\n		<td width=\\"100\\">\r\n			{TXT_CALENDAR_NAME}:\r\n		</td>\r\n		<td>\r\n			{CALENDAR_NAME}\r\n		</td>	\r\n	</tr>\r\n	<tr>\r\n		<td width=\\"100\\">\r\n			{TXT_CALENDAR_PLACE}:\r\n		</td>\r\n		<td>\r\n			{CALENDAR_PLACE}\r\n		</td>\r\n	</tr>\r\n	<!--\r\n	<tr>\r\n		<td width=\\"100\\">\r\n			{TXT_CALENDAR_PRIORITY}:\r\n		</td>\r\n		<td>\r\n			<img src=\\"images/modules/calendar/{CALENDAR_PRIORITY_GIF}.gif\\" align=\\"absmiddle\\"> ({CALENDAR_PRIORITY})\r\n		</td>\r\n	</tr>\r\n	-->\r\n	<tr>\r\n		<td width=\\"100\\">\r\n			{TXT_CALENDAR_START}:\r\n		</td>\r\n		<td>\r\n			{CALENDAR_START}\r\n		</td>\r\n	</tr>\r\n	<tr>\r\n		<td width=\\"100\\">\r\n			{TXT_CALENDAR_END}:\r\n		</td>\r\n		<td>\r\n			{CALENDAR_END}\r\n		</td>\r\n	</tr>\r\n	<tr>\r\n		<td width=\\"100\\" valign=\\"top\\">\r\n			{TXT_CALENDAR_COMMENT}:\r\n		</td>\r\n		<td>\r\n			{CALENDAR_COMMENT}\r\n		</td>\r\n	</tr>\r\n\r\n	<!-- BEGIN infolink -->\r\n    <tr class=\\"row1\\">\r\n        	<td width=\\"100\\" valign=\\"top\\"> {TXT_CALENDAR_INFO}: </td>\r\n        	<td><a href=\\"{CALENDAR_INFO_HREF}\\">{CALENDAR_INFO}</a></td>\r\n        </tr>\r\n	<!-- END infolink -->\r\n</table><br />\r\n<a href=\\"javascript:history.back()\\">{TXT_CALENDAR_BACK}</a>\r\n<!-- END calendar_show_note.html -->', 'Veranstaltungs Information', 'event', 'y', 529, 'off', 'system', 1, '1')
SQL
				,
				''				=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (529, 21, '<!-- START calendar_standard_view.html --> {CALENDAR_JAVASCRIPT}\r\n<div style=\\"margin: auto; width: 200px;\\"> {CALENDAR} {CALENDAR_CATEGORIES} <br /> <br /> </div>\r\n<span style=\\"font-size: 11px; font-weight: bold;\\">{TXT_CALENDAR_SEARCH}:</span>\r\n<form action=\\"?section=calendar&amp;act=search\\" method=\\"post\\" id=\\"searchform\\">\r\n    <table style=\\"font-size: 11px;\\">\r\n        <tbody>\r\n            <tr>\r\n                <td>{TXT_CALENDAR_FROM}:</td>\r\n                <td style=\\"padding-left: 5px;\\"><input type=\\"text\\" name=\\"startDate\\" id=\\"DPC_edit1_YYYY-MM-DD\\" value=\\"{CALENDAR_DATEPICKER_START}\\" style=\\"padding: 2px; width: 8em;\\" /></td>\r\n                <td style=\\"padding-left: 15px;\\">{TXT_CALENDAR_KEYWORD}:</td>\r\n                <td style=\\"padding-left: 5px;\\"><input type=\\"text\\" name=\\"keyword\\" style=\\"padding: 2px;\\" value=\\"{CALENDAR_SEARCHED_KEYWORD}\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td>{TXT_CALENDAR_TILL}:</td>\r\n                <td style=\\"padding-left: 5px;\\"><input type=\\"text\\" name=\\"endDate\\" id=\\"DPC_edit2_YYYY-MM-DD\\" value=\\"{CALENDAR_DATEPICKER_END}\\" style=\\"padding: 2px; width: 8em;\\" /></td>\r\n                <td style=\\"padding-left: 15px;\\">&nbsp;</td>\r\n                <td style=\\"padding-left: 5px;\\"><input type=\\"submit\\" value=\\"{TXT_CALENDAR_SEARCH}\\" /></td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>\r\n<div style=\\"width: 100%; margin-top: 15px;\\">\r\n<table cellspacing=\\"0\\" cellpadding=\\"0\\" class=\\"calendar_eventlist\\" style=\\"width: 100%;\\">\r\n    <tbody>\r\n        <tr>\r\n            <th style=\\"text-align: left;\\">{TXT_CALENDAR_STARTDATE}</th>\r\n            <th style=\\"text-align: left;\\">{TXT_CALENDAR_TITLE}</th>\r\n            <th style=\\"text-align: left;\\">{TXT_CALENDAR_PLACE}</th>\r\n        </tr>\r\n        <!-- BEGIN event -->\r\n        <tr>\r\n            <td style=\\"width: 110px;\\">{CALENDAR_STARTDATE}&nbsp;{CALENDAR_STARTTIME}</td>\r\n            <td><a href=\\"?section=calendar&amp;cmd=event&amp;id={CALENDAR_ID}\\">{CALENDAR_TITLE}</a></td>\r\n            <td style=\\"width: 80px;\\">{CALENDAR_PLACE}</td>\r\n        </tr>\r\n        <!-- END event -->\r\n    </tbody>\r\n</table>\r\n</div>\r\n<!-- END calendar_standard_view.html -->', 'Standard Ansicht', '', 'n', 0, 'on', 'system', 1, '1')
SQL
				),
			22	=> array(
				''				=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (506, 22, '<!-- START feed.html -->\r\n{FEED_NO_NEWSFEED}\r\n<!-- BEGIN feed_table -->\r\n<table cellspacing=\\"0\\" cellpadding=\\"0\\" border=\\"0\\">\r\n  <tr> \r\n    <td valign=\\"top\\" nowrap=\\"nowrap\\"> \r\n      <!-- BEGIN feed_cat -->\r\n      <b>{FEED_CAT_NAME}</b><br />\r\n      <!-- BEGIN feed_news -->\r\n      &nbsp;&nbsp;&nbsp;&nbsp;<a href=\\"{FEED_NEWS_LINK}\\">{FEED_NEWS_NAME}</a><br />\r\n      <!-- END feed_news -->\r\n      <!-- END feed_cat -->\r\n    </td>\r\n  </tr>\r\n  <tr> \r\n    <td valign=\\"top\\" nowrap=\\"nowrap\\">\r\n      <div  style=\\"overflow:auto;width: 500px;\\">  <br />\r\n      <!-- BEGIN feed_show_news -->\r\n      <br /><b>{FEED_CAT}</b> &gt; <b>{FEED_PAGE}</b> ({FEED_TITLE})<br />\r\n      {FEED_IMAGE} {TXT_FEED_LAST_UPTDATE}: {FEED_TIME}<br />\r\n      <br />\r\n      <ul>\r\n	  \r\n      <!-- BEGIN feed_output_news -->      \r\n       <li><a href=\\"{FEED_LINK}\\" target=\\"_blank\\">{FEED_NAME}</a></li>     \r\n      <!-- END feed_output_news --> \r\n      </ul></div>\r\n      <!-- END feed_show_news -->\r\n    </td>\r\n  </tr>\r\n</table>\r\n<!-- END feed_table -->\r\n<!-- END feed.html -->', 'News-Syndication', '', 'y', 0, 'on', 'system', 4, '1')
SQL
				,
				'newsML'		=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (507, 22, '{NEWSML_TITLE}<br /><br />{NEWSML_TEXT}<br /> <a href="javascript:window.history.back();">&lt; zur&uuml;ck</a>', 'Newsmeldung', 'newsML', 'y', 506, 'on', 'system', 1, '1')
SQL
				),
			23	=> array(
				''				=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (468, 23, '<table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"0 border=\\"0\\">\r\n<tbody>\r\n<tr>\r\n<td><a href=\\"index.php?section=community&amp;cmd=register\\">Mitglied werden</a></td>\r\n</tr>\r\n<tr>\r\n<td><a href=\\"index.php?section=community&amp;cmd=profile\\">Mein Profil</a></td>\r\n</tr>\r\n</tbody>\r\n</table>', 'Community', '', 'y', 0, 'on', 'system', 111, '1')
SQL
				,
				'register'		=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (469, 23, '{COMMUNITY_STATUS_MESSAGE}<br /> <!-- BEGIN community_registration_form -->\r\n<form method=\\"post\\" action=\\"index.php?section=community&amp;cmd=register\\">\r\n    <table width=\\"100%\\" cellspacing=\\"5\\" cellpadding=\\"0\\" border=\\"0\\" summary=\\"registration\\">\r\n        <tbody>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">{TXT_LOGIN_NAME}:&nbsp;<font color=\\"red\\">*</font></td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" value=\\"{COMMUNITY_USERNAME}\\" maxlength=\\"40\\" size=\\"30\\" name=\\"username\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">{TXT_LOGIN_PASSWORD}:&nbsp;<font color=\\"red\\">*</font></td>\r\n                <td width=\\"70%\\"><input type=\\"password\\" maxlength=\\"50\\" size=\\"30\\" name=\\"password\\" />&nbsp;{TXT_PASSWORD_MINIMAL_CHARACTERS}</td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">{TXT_VERIFY_PASSWORD}:&nbsp;<font color=\\"red\\">*</font></td>\r\n                <td width=\\"70%\\"><input type=\\"password\\" maxlength=\\"50\\" size=\\"30\\" name=\\"password2\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">{TXT_EMAIL}: <font color=\\"red\\">*</font></td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" value=\\"{COMMUNITY_EMAIL}\\" maxlength=\\"255\\" size=\\"30\\" name=\\"email\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\">&nbsp;</td>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\"><input type=\\"submit\\" name=\\"register\\" value=\\"{TXT_REGISTER}\\" /><br /><br />[<font color=\\"red\\">*</font>] {TXT_ALL_FIELDS_REQUIRED} {TXT_PASSWORD_NOT_USERNAME_TEXT}</td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>\r\n<!-- END community_registration_form -->', 'Registration', 'register', 'n', 468, 'off', 'system', 0, '1')
SQL
				,
				'profile'		=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (470, 23, '<form action=\\"index.php?section=community&amp;cmd=profile\\" method=\\"post\\">\r\n    <table width=\\"100%\\" cellspacing=\\"5\\" cellpadding=\\"0\\" border=\\"0\\">\r\n        <tbody>\r\n            <tr>\r\n                <th colspan=\\"2\\">Pers&ouml;nliche Angaben</th>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\">{COMMUNITY_STATUS_MESSAGE_PROFILE}</td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Vorname</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"firstname\\" value=\\"{COMMUNITY_FIRSTNAME}\\" tabindex=\\"1\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Nachname</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"lastname\\" value=\\"{COMMUNITY_LASTNAME}\\" tabindex=\\"2\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Wohnort</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"residence\\" value=\\"{COMMUNITY_RESIDENCE}\\" tabindex=\\"3\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Beruf</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"profession\\" value=\\"{COMMUNITY_PROFESSION}\\" tabindex=\\"4\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Interessen</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"interests\\" value=\\"{COMMUNITY_INTERESTS}\\" tabindex=\\"5\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Webseite</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"webpage\\" value=\\"{COMMUNITY_WEBPAGE}\\" tabindex=\\"6\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\"><input type=\\"submit\\" name=\\"change_profile\\" value=\\"Angaben Ändern\\" tabindex=\\"7\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\"><hr /></td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>\r\n<form action=\\"index.php?section=community&amp;cmd=profile\\" method=\\"post\\">\r\n    <table width=\\"100%\\" cellspacing=\\"5\\" cellpadding=\\"0\\" border=\\"0\\">\r\n        <tbody>\r\n            <tr>\r\n                <th colspan=\\"2\\">E-Mail Adresse &auml;ndern</th>\r\n            </tr>\r\n        </tbody>\r\n        <tbody>\r\n            <tr>\r\n                <td colspan=\\"2\\">{COMMUNITY_STATUS_MESSAGE_EMAIL}</td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Aktuelle E-Mail Adresse</td>\r\n                <td width=\\"70%\\">{COMMUNITY_EMAIL}</td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Neue E-Mail Adresse</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"email\\" value=\\"{COMMUNITY_NEW_EMAIL}\\" tabindex=\\"8\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">E-Mail best&auml;tigen</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"email2\\" value=\\"\\" tabindex=\\"9\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\"><input type=\\"submit\\" name=\\"change_email\\" value=\\"E-Mail Ändern\\" tabindex=\\"10\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\"><hr /></td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>\r\n<form action=\\"index.php?section=community&amp;cmd=profile\\" method=\\"post\\">\r\n    <table width=\\"100%\\" cellspacing=\\"5\\" cellpadding=\\"0\\" border=\\"0\\">\r\n        <tbody>\r\n            <tr>\r\n                <th colspan=\\"2\\">Kennwort &auml;ndern</th>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\">{COMMUNITY_STATUS_MESSAGE_PASSWORD}</td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Neues Kennwort</td>\r\n                <td width=\\"70%\\"><input type=\\"password\\" name=\\"password\\" value=\\"\\" tabindex=\\"11\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Kennwort best&auml;tigen</td>\r\n                <td width=\\"70%\\"><input type=\\"password\\" name=\\"password2\\" value=\\"\\" tabindex=\\"12\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\"><input type=\\"submit\\" name=\\"change_password\\" value=\\"Kennwort Ändern\\" tabindex=\\"13\\" /></td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>', 'Mein Profil', 'profile', 'n', 468, 'off', 'system', 0, '1')
SQL
				,
				'activate'		=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (471, 23, '{COMMUNITY_STATUS_MESSAGE}', 'Benutzerkonto aktivieren', 'activate', 'y', 468, 'off', 'system', 0, '1')
SQL
				),
			24	=> array(
				''				=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (351, 24, '{MEDIA_JAVASCRIPT}\r\n<table id=\\"media\\" cellspacing=\\"0\\" cellpadding=\\"5\\" width=\\"100%\\" border=\\"0\\">\r\n    <tbody>\r\n        <tr class=\\"head\\">\r\n            <td align=\\"center\\" width=\\"16\\"><strong>#</strong></td>\r\n            <td colspan=\\"2\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_NAME_HREF}\\"><strong>{TXT_MEDIA_FILE_NAME}</strong></a> {MEDIA_NAME_ICON}</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_SIZE_HREF}\\" name=\\"sort_size\\"><strong>{TXT_MEDIA_FILE_SIZE}</strong></a> {MEDIA_SIZE_ICON} </td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_TYPE_HREF}\\" name=\\"sort_type\\"><strong>{TXT_MEDIA_FILE_TYPE}</strong></a> {MEDIA_TYPE_ICON} </td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_DATE_HREF}\\" name=\\"sort_date\\"><strong>{TXT_MEDIA_FILE_DATE}</strong></a> {MEDIA_DATE_ICON} </td>\r\n        </tr>\r\n        <tr class=\\"row2\\" valign=\\"middle\\">\r\n            <td align=\\"center\\" width=\\"16\\"><img title=\\"base\\" height=\\"16\\" alt=\\"base\\" src=\\"images/modules/media/_base.gif\\" width=\\"16\\"/> </td>\r\n            <td colspan=\\"5\\"><strong><a title=\\"{MEDIA_TREE_NAV_MAIN}\\" href=\\"{MEDIA_TREE_NAV_MAIN_HREF}\\">{MEDIA_TREE_NAV_MAIN}</a></strong> <!-- BEGIN mediaTreeNavigation --><a href=\\"{MEDIA_TREE_NAV_DIR_HREF}\\">&nbsp;{MEDIA_TREE_NAV_DIR} /</a> <!-- END mediaTreeNavigation --></td>\r\n        </tr>\r\n        <!-- BEGIN mediaDirectoryTree -->\r\n        <tr class=\\"{MEDIA_DIR_TREE_ROW}\\" valign=\\"middle\\">\r\n            <td width=\\"16\\">&nbsp;</td>\r\n            <td align=\\"center\\" width=\\"16\\"><img title=\\"icon\\" height=\\"16\\" alt=\\"icon\\" src=\\"{MEDIA_FILE_ICON}\\" width=\\"16\\"/></td>\r\n            <td width=\\"100%\\"><a title=\\"{MEDIA_FILE_NAME}\\" href=\\"{MEDIA_FILE_NAME_HREF}\\">{MEDIA_FILE_NAME}</a></td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_SIZE}&nbsp;</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_TYPE}&nbsp;</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_DATE}&nbsp;</td>\r\n        </tr>\r\n        <!-- END mediaDirectoryTree --><!-- BEGIN mediaEmptyDirectory -->\r\n        <tr class=\\"row1\\">\r\n            <td>&nbsp;</td>\r\n            <td colspan=\\"5\\">{TXT_MEDIA_DIR_EMPTY}</td>\r\n        </tr>\r\n        <!-- END mediaEmptyDirectory -->\r\n    </tbody>\r\n</table>', 'Media Archiv #2', '', 'y', 0, 'on', 'system', 2, '2')
SQL
				),
			25	=> array(
				''				=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (352, 25, '{MEDIA_JAVASCRIPT}\r\n<table id=\\"media\\" cellspacing=\\"0\\" cellpadding=\\"5\\" width=\\"100%\\" border=\\"0\\">\r\n    <tbody>\r\n        <tr class=\\"head\\">\r\n            <td align=\\"center\\" width=\\"16\\"><strong>#</strong></td>\r\n            <td colspan=\\"2\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_NAME_HREF}\\"><strong>{TXT_MEDIA_FILE_NAME}</strong></a> {MEDIA_NAME_ICON}</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_SIZE_HREF}\\" name=\\"sort_size\\"><strong>{TXT_MEDIA_FILE_SIZE}</strong></a> {MEDIA_SIZE_ICON} </td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_TYPE_HREF}\\" name=\\"sort_type\\"><strong>{TXT_MEDIA_FILE_TYPE}</strong></a> {MEDIA_TYPE_ICON} </td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_DATE_HREF}\\" name=\\"sort_date\\"><strong>{TXT_MEDIA_FILE_DATE}</strong></a> {MEDIA_DATE_ICON} </td>\r\n        </tr>\r\n        <tr class=\\"row2\\" valign=\\"middle\\">\r\n            <td align=\\"center\\" width=\\"16\\"><img title=\\"base\\" height=\\"16\\" alt=\\"base\\" src=\\"images/modules/media/_base.gif\\" width=\\"16\\"/> </td>\r\n            <td colspan=\\"5\\"><strong><a title=\\"{MEDIA_TREE_NAV_MAIN}\\" href=\\"{MEDIA_TREE_NAV_MAIN_HREF}\\">{MEDIA_TREE_NAV_MAIN}</a></strong> <!-- BEGIN mediaTreeNavigation --><a href=\\"{MEDIA_TREE_NAV_DIR_HREF}\\">&nbsp;{MEDIA_TREE_NAV_DIR} /</a> <!-- END mediaTreeNavigation --></td>\r\n        </tr>\r\n        <!-- BEGIN mediaDirectoryTree -->\r\n        <tr class=\\"{MEDIA_DIR_TREE_ROW}\\" valign=\\"middle\\">\r\n            <td width=\\"16\\">&nbsp;</td>\r\n            <td align=\\"center\\" width=\\"16\\"><img title=\\"icon\\" height=\\"16\\" alt=\\"icon\\" src=\\"{MEDIA_FILE_ICON}\\" width=\\"16\\"/></td>\r\n            <td width=\\"100%\\"><a title=\\"{MEDIA_FILE_NAME}\\" href=\\"{MEDIA_FILE_NAME_HREF}\\">{MEDIA_FILE_NAME}</a></td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_SIZE}&nbsp;</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_TYPE}&nbsp;</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_DATE}&nbsp;</td>\r\n        </tr>\r\n        <!-- END mediaDirectoryTree --><!-- BEGIN mediaEmptyDirectory -->\r\n        <tr class=\\"row1\\">\r\n            <td>&nbsp;</td>\r\n            <td colspan=\\"5\\">{TXT_MEDIA_DIR_EMPTY}</td>\r\n        </tr>\r\n        <!-- END mediaEmptyDirectory -->\r\n    </tbody>\r\n</table>', 'Media Archiv #3', '', 'y', 0, 'on', 'system', 3, '2')
SQL
				),
			27	=> array(
				''				=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (500, 27, '{RECOM_STATUS} <!-- BEGIN recommend_form --> {RECOM_TEXT} {RECOM_SCRIPT}\r\n<form action=\\"index.php?section=recommend&amp;act=sendRecomm\\" method=\\"post\\" name=\\"recommend\\">\r\n    <input type=\\"hidden\\" name=\\"uri\\" value=\\"{RECOM_REFERER}\\" /> <input type=\\"hidden\\" name=\\"female_salutation_text\\" value=\\"{RECOM_FEMALE_SALUTATION_TEXT}\\" /> <input type=\\"hidden\\" name=\\"male_salutation_text\\" value=\\"{RECOM_MALE_SALUTATION_TEXT}\\" /> <input type=\\"hidden\\" name=\\"preview_text\\" value=\\"{RECOM_PREVIEW}\\" />\r\n    <table style=\\"width: 90%;\\">\r\n        <tbody>\r\n            <tr>\r\n                <td style=\\"width: 40%; padding-bottom: 15px;\\">{RECOM_TXT_RECEIVER_NAME}:</td>\r\n                <td style=\\"padding-bottom: 15px; width: 60%;\\"><input type=\\"text\\" name=\\"receivername\\" maxlength=\\"100\\" value=\\"{RECOM_RECEIVER_NAME}\\" style=\\"width: 100%;\\" onchange=\\"update();\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td style=\\"padding-bottom: 15px;\\">{RECOM_TXT_RECEIVER_MAIL}:</td>\r\n                <td style=\\"padding-bottom: 15px;\\"><input type=\\"text\\" name=\\"receivermail\\" maxlength=\\"100\\" value=\\"{RECOM_RECEIVER_MAIL}\\" style=\\"width: 100%;\\" onchange=\\"update();\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td valign=\\"top\\" style=\\"padding-bottom: 15px;\\">{RECOM_TXT_GENDER}:</td>\r\n                <td style=\\"padding-bottom: 15px;\\"><input type=\\"radio\\" name=\\"gender\\" style=\\"border: medium none ; margin-left: 0px;\\" value=\\"female\\" onclick=\\"update();\\" />{RECOM_TXT_FEMALE}<br /> 		<input type=\\"radio\\" name=\\"gender\\" style=\\"border: medium none ; margin-left: 0px;\\" value=\\"male\\" onclick=\\"update();\\" />{RECOM_TXT_MALE}</td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"100\\" style=\\"padding-bottom: 15px;\\">{RECOM_TXT_SENDER_NAME}:</td>\r\n                <td style=\\"padding-bottom: 15px;\\"><input type=\\"text\\" name=\\"sendername\\" maxlength=\\"100\\" value=\\"{RECOM_SENDER_NAME}\\" style=\\"width: 100%;\\" onchange=\\"update();\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td style=\\"padding-bottom: 15px;\\">{RECOM_TXT_SENDER_MAIL}:</td>\r\n                <td style=\\"padding-bottom: 15px;\\"><input type=\\"text\\" name=\\"sendermail\\" maxlength=\\"100\\" value=\\"{RECOM_SENDER_MAIL}\\" style=\\"width: 100%;\\" onchange=\\"update();\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td valign=\\"top\\">{RECOM_TXT_COMMENT}:</td>\r\n                <td style=\\"padding-bottom: 15px;\\"><textarea rows=\\"7\\" cols=\\"30\\" name=\\"comment\\" style=\\"width: 100%;\\" onchange=\\"update();\\">{RECOM_COMMENT}</textarea></td>\r\n            </tr>\r\n            <tr>\r\n                <td valign=\\"top\\">{RECOM_TXT_PREVIEW}:</td>\r\n                <td style=\\"padding-bottom: 15px;\\"> 	<textarea name=\\"preview\\" style=\\"width: 100%; height: 200px;\\" readonly=\\"\\"></textarea></td>\r\n            </tr>\r\n            <tr>\r\n                <td>&nbsp;</td>\r\n                <td><input type=\\"submit\\" value=\\"Senden\\" /> <input type=\\"reset\\" value=\\"Löschen\\" /></td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>\r\n<!-- END recommend_form -->', 'Seite weiterempfehlen', '', 'n', 0, 'off', 'system', 1000, '1')
SQL
				),
			30	=> array(
				''				=> 'INSERT INTO '.DBPREFIX.<<<SQL
								module_repository VALUES (502, 30, '{LIVECAM_JAVASCRIPT}\r\n<form action=\\"index.php?section=livecam\\" method=\\"post\\" name=\\"form\\">\r\n	<input type=\\"submit\\" value=\\"Aktuelles Bild\\" tabindex=\\"1\\" accesskey=\\"A\\" name=\\"act[current]\\" />&nbsp;<input type=\\"submit\\" value=\\"Heute\\" name=\\"act[today]\\" style=\\"border-width: 1px;\\" size=\\"12\\" />&nbsp;<input type=\\"text\\" style=\\"border-width: 1px;\\" size=\\"12\\" value=\\"{LIVECAM_DATE}\\" id=\\"DPC_datum\\" name=\\"date\\" />&nbsp;<input type=\\"submit\\" value=\\"Archiv Anzeigen\\" name=\\"act[archive]\\" style=\\"border-width: 1px;\\" size=\\"12\\" />\r\n</form>\r\n<br />\r\n{LIVECAM_STATUS_MESSAGE}<br />\r\n<!-- BEGIN livecamPicture -->\r\n<a href=\\"?section=livecam&amp;act=today\\" title=\\"{LIVECAM_IMAGE_TEXT}\\"><img width=\\"640\\" height=\\"480\\" border=\\"0\\" alt=\\"{LIVECAM_IMAGE_TEXT}\\" src=\\"{LIVECAM_CURRENT_IMAGE}\\" /></a><br />\r\nDie Seite wird jede Minute automatisch aktualisiert.  <a onclick=\\"javascript:document.location.reload();\\" href=\\"index.php?section=livecam\\">Aktualisieren.</a>\r\n<!-- END livecamPicture -->\r\n<!-- BEGIN livecamArchive -->\r\n<table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"0\\" border=\\"0\\">\r\n    <tbody>\r\n        <tr>\r\n            <td colspan=\\"3\\">\r\n            <h2>Archiv {LIVECAM_DATE}</h2>\r\n            </td>\r\n        </tr>\r\n        <!-- BEGIN livecamArchiveRow -->\r\n        <tr>\r\n            <td>\r\n            <p><!-- BEGIN livecamArchivePicture1 -->\r\n<a href=\\"{LIVECAM_PICTURE_URL}\\" title=\\"{LIVECAM_PICTURE_TIME}\\"><img src=\\"{LIVECAM_THUMBNAIL_URL}\\" border=\\"0\\" alt=\\"{LIVECAM_PICTURE_TIME}\\" /></a><br />{LIVECAM_PICTURE_TIME}<!-- END livecamArchivePicture1 --><br /></p>\r\n            </td>\r\n            <td>\r\n            <p><!-- BEGIN livecamArchivePicture2 -->\r\n<a href=\\"{LIVECAM_PICTURE_URL}\\" title=\\"{LIVECAM_PICTURE_TIME}\\"><img src=\\"{LIVECAM_THUMBNAIL_URL}\\" border=\\"0\\" alt=\\"{LIVECAM_PICTURE_TIME}\\" /></a><br />{LIVECAM_PICTURE_TIME}<!-- END livecamArchivePicture2 --><br /></p>\r\n            </td>\r\n            <td>\r\n            <p><!-- BEGIN livecamArchivePicture3 -->\r\n<a href=\\"{LIVECAM_PICTURE_URL}\\" title=\\"{LIVECAM_PICTURE_TIME}\\"><img src=\\"{LIVECAM_THUMBNAIL_URL}\\" border=\\"0\\" alt=\\"{LIVECAM_PICTURE_TIME}\\" /></a><br />{LIVECAM_PICTURE_TIME}<!-- END livecamArchivePicture3 --><br /></p>\r\n            </td>\r\n        </tr>\r\n        <!-- END livecamArchiveRow -->\r\n    </tbody>\r\n</table>\r\n<!-- END livecamArchive -->', 'Livebild ansehen', '', 'y', 0, 'on', 'system', 1, '1')
SQL
				)
		);

		foreach ($arrRepository as $moduleId => $arrPages) {
			foreach ($arrPages as $cmd => $insertQuery) {
				$query = "SELECT id FROM ".DBPREFIX."module_repository WHERE moduleid=".$moduleId." AND cmd='".$cmd."'";
				$objResult = $objDatabase->Execute($query);
				if ($objResult !== false) {
					if ($objResult->RecordCount() == 0) {
						if ($objDatabase->Execute($insertQuery) === false) {
							return $this->_databaseError($insertQuery, $objDatabase->ErrorMsg());
						}
					}
				} else {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		}

		return true;
	}

	function _createContactModule()
	{
		global $objDatabase;

		// create table contrexx_module_contact_form
		if (!in_array(DBPREFIX."module_contact_form", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_contact_form (`id` int(10) unsigned NOT NULL auto_increment, `name` varchar(255) NOT NULL default '', `mails` text NOT NULL, PRIMARY KEY  (`id`)) TYPE=MyISAM AUTO_INCREMENT=2";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		// create table contrexx_module_contact_form_data
		if (!in_array(DBPREFIX."module_contact_form_data", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_contact_form_data ( `id` int(10) unsigned NOT NULL auto_increment, `id_form` int(10) unsigned NOT NULL default '0', `time` int(14) unsigned NOT NULL default '0', `host` varchar(255) NOT NULL default '', `lang` varchar(64) NOT NULL default '', `browser` varchar(255) NOT NULL default '', `ipaddress` varchar(15) NOT NULL default '', `data` text NOT NULL, PRIMARY KEY  (`id`)) TYPE=MyISAM AUTO_INCREMENT=1";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		// create table contrexx_module_contact_form_field
		if (!in_array(DBPREFIX."module_contact_form_field", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_contact_form_field ( `id` int(10) unsigned NOT NULL auto_increment, `id_form` int(10) unsigned NOT NULL default '0', `name` varchar(255) NOT NULL default '', `type` enum('text','checkbox','checkboxGroup','file','hidden','password','radio','select','textarea') NOT NULL default 'text', `attributes` text NOT NULL, `order_id` smallint(5) unsigned NOT NULL default '0', PRIMARY KEY  (`id`)) TYPE=MyISAM AUTO_INCREMENT=12";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		// create table contrexx_module_contact_settings
		if (!in_array(DBPREFIX."module_contact_settings", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_contact_settings ( `setid` smallint(6) NOT NULL auto_increment, `setname` varchar(250) NOT NULL default '', `setvalue` text NOT NULL, `status` tinyint(1) NOT NULL default '0', PRIMARY KEY  (`setid`)) TYPE=MyISAM AUTO_INCREMENT=2";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		// insert settings
		$query = "SELECT setid FROM ".DBPREFIX."module_contact_settings WHERE setname='fileUploadDepositionPath'";
		$objResult = $objDatabase->Execute($query);
		if ($objResult !== false) {
			if ($objResult->RecordCount() == 0) {
				$query = "INSERT INTO ".DBPREFIX."module_contact_settings VALUES (1, 'fileUploadDepositionPath', '/images/attach', 1)";
				if ($objDatabase->Execute($query) === false) {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		} else {
			return $this->_databaseError($query, $objDatabase->ErrorMsg());
		}

		// update contact form emails
		$query = "SELECT setid, setname, setvalue FROM ".DBPREFIX."settings WHERE setname LIKE 'contactFormEmail%'";
		$objResult = $objDatabase->Execute($query);
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				if ($objResult->fields['setname'] == 'contactFormEmail') {
					$key = 0;
				} else {
					$key = $objResult->fields['setid'];
				}

				$id = intval(substr($objResult->fields['setname'],16));

				$arrEmails[$id] = array(
					'settings_key'	=> $key,
					'email'			=> $objResult->fields['setvalue']
				);
				$objResult->MoveNext();
			}

			if (is_array($arrEmails)) {
				foreach ($arrEmails as $id => $arrEmail) {
					if ($arrEmail['settings_key'] == 0 || !empty($arrEmail['email'])) {
						$query = "SELECT id FROM ".DBPREFIX."module_contact_form WHERE id = ".$id;
						$objResult = $objDatabase->Execute($query);
						if ($objResult !== false) {
							if ($objResult->RecordCount() == 0) {
								$query = "INSERT INTO ".DBPREFIX."module_contact_form VALUES (".$id.", 'Kontaktformular ".$id."', '".$arrEmail['email']."')";
								if ($objDatabase->Execute($query) === false) {
									return $this->_databaseError($query, $objDatabase->ErrorMsg());
								}
							}
						} else {
							return $this->_databaseError($query, $objDatabase->ErrorMsg());
						}
					}

					if ($arrEmail['settings_key'] > 0) {
						$query = "DELETE FROM ".DBPREFIX."settings WHERE setid=".$arrEmail['settings_key'];
						if ($objDatabase->Execute($query) === false) {
							return $this->_databaseError($query, $objDatabase->ErrorMsg());
						}
					}
				}
			}
		} else {
			$this->_databaseError($query, $objDatabase->ErrorMsg());
		}
		return true;
	}

	function _createBannerModule()
	{
		global $objDatabase;

		// create table contrexx_module_banner_groups
		if (!in_array(DBPREFIX."module_banner_groups", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_banner_groups (  `id` int(11) NOT NULL auto_increment,  `name` varchar(255) NOT NULL default '',  `description` varchar(255) NOT NULL default '',  `placeholder_name` varchar(100) NOT NULL default '',  `status` int(1) NOT NULL default '1',  `is_deleted` set('0','1') NOT NULL default '0',  PRIMARY KEY  (`id`)) TYPE=MyISAM";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		// insert banners
		$arrBanners = array(
			1	=> "INSERT INTO ".DBPREFIX."module_banner_groups VALUES (1, 'Full Banner - Header', '468 x 60 Pixel', '[[BANNER_GROUP_1]]', 1, '0')",
			2	=> "INSERT INTO ".DBPREFIX."module_banner_groups VALUES (2, 'Full Banner - Footer', '468 x 60 Pixel', '[[BANNER_GROUP_2]]', 1, '0')",
			3	=> "INSERT INTO ".DBPREFIX."module_banner_groups VALUES (3, 'Half Banner', '234 x 60 Pixel', '[[BANNER_GROUP_3]]', 1, '0')",
			4	=> "INSERT INTO ".DBPREFIX."module_banner_groups VALUES (4, 'Button 1', '120 x 90 Pixel', '[[BANNER_GROUP_3]]', 1, '0')",
			5	=> "INSERT INTO ".DBPREFIX."module_banner_groups VALUES (5, 'Button 2', '120 x 60 Pixel', '[[BANNER_GROUP_5]]', 1, '0')",
			6	=> "INSERT INTO ".DBPREFIX."module_banner_groups VALUES (6, 'Square Pop-Up', '250 x 250 Pixel', '[[BANNER_GROUP_6]]', 1, '0')",
			7	=> "INSERT INTO ".DBPREFIX."module_banner_groups VALUES (7, 'Skyscraper', '120 x 600 Pixel', '[[BANNER_GROUP_7]]', 1, '0')",
			8	=> "INSERT INTO ".DBPREFIX."module_banner_groups VALUES (8, 'Wide Skyscraper', '160 x 600 Pixel', '[[BANNER_GROUP_8]]', 1, '0')",
			9	=> "INSERT INTO ".DBPREFIX."module_banner_groups VALUES (9, 'Half Page Ad', '300 x 600 Pixel', '[[BANNER_GROUP_9]]', 1, '0')",
			10	=> "INSERT INTO ".DBPREFIX."module_banner_groups VALUES (10, 'Popup-Window', 'Werbung Aufklappfenster', '[[BANNER_GROUP_10]]', 1, '0')"
		);
		foreach ($arrBanners as $bannerId => $insertQuery) {
			$query = "SELECT id FROM ".DBPREFIX."module_banner_groups WHERE id=".$bannerId;
			$objResult = $objDatabase->Execute($query);
			if ($objResult !== false) {
				if ($objResult->RecordCount() == 0) {
					if ($objDatabase->Execute($insertQuery) === false) {
						return $this->_databaseError($insertQuery, $objDatabase->ErrorMsg());
					}
				}
			} else {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		// create table contrexx_module_banner_relations
		if (!in_array(DBPREFIX."module_banner_relations")) {
			$query = "CREATE TABLE ".DBPREFIX."module_banner_relations (  `banner_id` int(11) NOT NULL default '0',  `group_id` tinyint(4) NOT NULL default '0',  `page_id` int(11) NOT NULL default '0',  `type` set('content','news','teaser') NOT NULL default 'content',  KEY `banner_id` (`banner_id`,`group_id`,`page_id`)) TYPE=MyISAM";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		// create table contrexx_module_banner_settings
		if (!in_array(DBPREFIX."module_banner_settings")) {
			$query = "CREATE TABLE ".DBPREFIX."module_banner_settings (  `name` varchar(50) NOT NULL default '',  `value` varchar(250) NOT NULL default '',  KEY `name` (`name`)) TYPE=MyISAM";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		// add banner setting options
		$arrBannerSettingsOptions = array(
			'news_banner'		=> "INSERT INTO ".DBPREFIX."module_banner_settings VALUES ('news_banner', '0')",
			'content_banner'	=> "INSERT INTO ".DBPREFIX."module_banner_settings VALUES ('content_banner', '1')",
			'teaser_banner'		=> "INSERT INTO ".DBPREFIX."module_banner_settings VALUES ('teaser_banner', '1')"
		);
		foreach ($arrBannerSettingsOptions as $name => $insertQuery) {
			$query = "SELECT name FROM ".DBPREFIX."module_banner_settings WHERE name='".$name."'";
			$objResult = $objDatabase->Execute($query);
			if ($objResult !== false) {
				if ($objResult->RecordCount() == 0) {
					if ($objDatabase->Execute($insertQuery) === false) {
						return $this->_databaseError($insertQuery, $objDatabase->ErrorMsg());
					}
				}
			} else {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		// create table contrexx_module_banner_system
		if (!in_array(DBPREFIX."module_banner_system")) {
			$query = "CREATE TABLE ".DBPREFIX."module_banner_system (  `id` int(11) NOT NULL auto_increment,  `parent_id` int(11) NOT NULL default '0',  `name` varchar(150) NOT NULL default '',  `banner_code` mediumtext NOT NULL,  `status` int(1) NOT NULL default '1',  `is_default` tinyint(2) unsigned NOT NULL default '0',  PRIMARY KEY  (`id`)) TYPE=MyISAM";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}
		return true;
	}

	function _createBlockModule()
	{
		global $objDatabase;

		if (!in_array(DBPREFIX."module_block_blocks", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_block_blocks (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,`content` TEXT NOT NULL ,PRIMARY KEY ( `id` ))";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		$query = "SELECT id FROM ".DBPREFIX."module_block_blocks";
		$objResult = $objDatabase->SelectLimit($query, 1);
		if ($objResult !== false) {
			if ($objResult->RecordCount() == 0) {
				$query = "INSERT INTO ".DBPREFXI."module_block_blocks ( `id` , `content` ) VALUES ('', 'Test block')";
				if ($objDatabase->Execute($query) === false) {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		}
		return true;
	}

	function _createLivecamModule()
	{
		global $objDatabase;

		if (!in_array(DBPREFIX."module_livecam_settings", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_livecam_settings (`setid` INT UNSIGNED NOT NULL AUTO_INCREMENT ,`setname` VARCHAR( 255 ) NOT NULL ,`setvalue` TEXT NOT NULL ,PRIMARY KEY ( `setid` ) )";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		$arrLivecamOptions = array(
			'currentImageUrl'	=> "INSERT INTO ".DBPREFIX."module_livecam_settings ( `setid` , `setname` , `setvalue` ) VALUES ('', 'currentImageUrl', 'http://heimenschwand.ch/webcam/current.jpg'), ('', 'archivePath', '/webcam')",
			'thumbnailPath'		=> "INSERT INTO ".DBPREFIX."module_livecam_settings ( `setid` , `setname` , `setvalue` ) VALUES ('', 'thumbnailPath', '/webcam/thumbs')"
		);
		foreach ($arrLivecamOptions as $name => $insertQuery) {
			$query = "SELECT setid FROM ".DBPREFIX."module_livecam_settings WHERE setname='".$name."'";
			$objResult = $objDatabase->Execute($query);
			if ($objResult !== false) {
				if ($objResult->RecordCount() == 0) {
					if ($objDatabase->Execute($insertQuery) === false) {
						return $this->_databaseError($insertQuery, $objDatabase->ErrorMsg());
					}
				}
			} else {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}
		return true;
	}

	function _createFeedNewsMLExtension()
	{
		global $objDatabase;

		if (!in_array(DBPREFIX."module_feed_newsml_categories", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_feed_newsml_categories (  `id` int(10) unsigned NOT NULL auto_increment,  `providerId` text NOT NULL,  `name` varchar(40) NOT NULL default '',  `subjectCodes` text NOT NULL,  `showSubjectCodes` enum('all','only','exclude') NOT NULL default 'all',  `template` text NOT NULL,  `limit` smallint(6) NOT NULL default '0',  `auto_update` tinyint(1) NOT NULL default '0',  PRIMARY KEY  (`id`)) TYPE=MyISAM";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."module_feed_newsml_documents", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_feed_newsml_documents (  `id` int(10) unsigned NOT NULL auto_increment,  `publicIdentifier` text NOT NULL,  `providerId` text NOT NULL,  `dateId` int(8) unsigned NOT NULL default '0',  `newsItemId` text NOT NULL,  `revisionId` smallint(5) unsigned NOT NULL default '0',  `thisRevisionDate` int(14) NOT NULL default '0',  `urgency` smallint(5) unsigned NOT NULL default '0',  `subjectCode` int(10) unsigned NOT NULL default '0',  `headLine` varchar(67) NOT NULL default '',  `dataContent` text NOT NULL,  PRIMARY KEY  (`id`)) TYPE=MyISAM";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."module_feed_newsml_providers", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_feed_newsml_providers (  `id` int(10) unsigned NOT NULL auto_increment,  `providerId` text NOT NULL,  `name` varchar(40) NOT NULL default '',  `path` text NOT NULL,  PRIMARY KEY  (`id`)) TYPE=MyISAM AUTO_INCREMENT=2";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		$query = "SELECT id FROM ".DBPREFIX."module_feed_newsml_providers WHERE providerId='".www.sda-ats.ch."'";
		$objResult = $objDatabase->Execute($query);
		if ($objResult !== false) {
			if ($objResult->RecordCount() == 0) {
				$query = "INSERT INTO ".DBPREFIX."module_feed_newsml_providers VALUES (1, 'www.sda-ats.ch', 'sda-Online', '/sportnews')";
				if ($objDatabase->Execute($query) === false) {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		}
		return true;
	}

	function _createRecommendModule()
	{
		global $objDatabase;

		if (!in_array(DBPREFIX."module_recommend", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_recommend (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,`name` VARCHAR( 255 ) NOT NULL ,`value` TEXT NOT NULL, `lang_id` INT DEFAULT '1' NOT NULL, PRIMARY KEY ( `id` ) )";
			if ($objDatabase->Execute($query)) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		$arrOptions = array(
			'body'				=> "INSERT INTO ".DBPREFIX."module_recommend ( `id` , `name` , `value` , `lang_id` ) VALUES ('', 'body', 'Sehr geehrte(r) Herr/Frau <RECEIVER_NAME>\n\nFolgende Seite wurde ihnen von <SENDER_NAME> (<SENDER_MAIL>) empfohlen:\n\n<URL>\n\nAnmerkung von <SENDER_NAME>:\n\n<COMMENT>', '1')",
			'subject'			=> "INSERT INTO ".DBPREFIX."module_recommend ( `id` , `name` , `value` , `lang_id` ) VALUES ('', 'subject', 'Seitenempfehlung von <SENDER_NAME>', '1')",
			'salutation_female'	=> "INSERT INTO ".DBPREFIX."module_recommend ( `id` , `name` , `value` , `lang_id` ) VALUES ('', 'salutation_female', 'Liebe', '1')",
			'salutation_male'	=> "INSERT INTO ".DBPREFIX."module_recommend ( `id` , `name` , `value` , `lang_id` ) VALUES ('', 'salutation_male', 'Lieber', '1')"
		);
		foreach ($arrOptions as $name => $insertQuery) {
			$query = "SELECT id FROM ".DBPREFIX."module_recommend WHERE name='".$name."'";
			$objResult = $objDatabase->Execute($query);
			if ($objResult !== false) {
				if ($objResult->RecordCount() == 0) {
					if ($objDatabase->Execute($insertQuery) === false) {
						return $this->_databaseError($insertQuery, $objDatabase->ErrorMsg());
					}
				}
			} else {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}
		return true;
	}

	function _createWorkflowModule()
	{
		global $objDatabase;

		if (!in_array(DBPREFIX."content_history", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."content_history (  `id` smallint(8) unsigned NOT NULL default '0',  `page_id` smallint(7) unsigned NOT NULL default '0',  `content` mediumtext NOT NULL,  `title` varchar(250) NOT NULL default '',  `metatitle` varchar(250) NOT NULL default '',  `metadesc` varchar(250) NOT NULL default '',  `metakeys` varchar(250) NOT NULL default '',  `metarobots` varchar(7) NOT NULL default 'index',  `css_name` varchar(50) NOT NULL default '',  `redirect` varchar(255) NOT NULL default '',  `expertmode` set('y','n') NOT NULL default 'n',  PRIMARY KEY  (`id`),  FULLTEXT KEY `fulltextindex` (`title`,`content`),  INDEX ( `page_id` ))";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."content_navigation_history", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."content_navigation_history (  `id` smallint(8) unsigned NOT NULL default '0' auto_increment,  `is_active` set ('0','1') NOT NULL default '0',  `catid` smallint(6) unsigned NOT NULL,  `parcat` smallint(6) unsigned NOT NULL default '0',  `catname` varchar(100) NOT NULL default '',  `target` varchar(10) NOT NULL default '',  `displayorder` smallint(6) unsigned NOT NULL default '1000',  `displaystatus` set('on','off') NOT NULL default 'on',  `activestatus` set('0','1') NOT NULL default '1',  `cachingstatus` set('0','1') NOT NULL default '1',  `username` varchar(40) NOT NULL default '',  `changelog` int(14) default NULL,  `cmd` varchar(50) NOT NULL default '',  `lang` tinyint(2) unsigned NOT NULL default '1',  `module` tinyint(2) unsigned NOT NULL default '0',  `startdate` date NOT NULL default '0000-00-00',  `enddate` date NOT NULL default '0000-00-00',  `protected` tinyint(4) NOT NULL default '0',  `frontend_access_id` int(11) unsigned NOT NULL default '0',  `backend_access_id` int(11) unsigned NOT NULL default '0',  `themes_id` int(4) NOT NULL default '0',  PRIMARY KEY  (`id`),  INDEX ( `catid` ))";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."content_logfile", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."content_logfile (`id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,`action` SET( 'new', 'update', 'delete' ) DEFAULT 'new' NOT NULL ,`history_id` INT( 10 ) UNSIGNED NOT NULL ,`is_validated` SET('0','1') NOT NULL default '0',PRIMARY KEY ( `id` ))";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}
		return true;
	}


	function _updateBlockModule107to108()
	{
		global $objDatabase;

		$arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."module_block_blocks");
		if (!is_array($arrColumns) || count($arrColumns) == 0) {
			print "Konnte die Spalten der Tabelle ".DBPREFIX."module_block_blocks nicht ermitteln!";
			return false;
		}

		if (!in_array("name", $arrColumns)) {
			$query = "ALTER TABLE ".DBPREFIX."module_block_blocks ADD `name` VARCHAR( 255 ) NOT NULL";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array("active", $arrColumns)) {
			$query = "ALTER TABLE ".DBPREFIX."module_block_blocks ADD `active` INT( 1 ) NOT NULL";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array("random", $arrColumns)) {
			$query = "ALTER TABLE ".DBPREFIX."module_block_blocks ADD `random` INT( 1 ) DEFAULT '0' NOT NULL AFTER `name`";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		return true;
	}

	function _updateSettings107to108()
	{
		global $objDatabase;

		$query = "SELECT setid FROM ".DBPREFIX."settings WHERE setname='blockRandom' AND setmodule=7";
		$objResult = $objDatabase->Execute($query);
		if ($objResult !== false) {
			if ($objResult->RecordCount() == 0) {
				$query = "INSERT INTO ".DBPREFIX."settings ( `setid` , `setname` , `setvalue` , `setmodule` ) VALUES ( '', 'blockRandom', '1', '7')";
				if ($objDatabase->Execute($query) === false) {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		} else {
			return $this->_databaseError($query, $objDatabase->ErrorMsg());
		}

		return true;
	}

	function _updateGuestbook107to108()
	{
		global $objDatabase;

		$query = "SELECT name FROM ".DBPREFIX."module_guestbook_settings WHERE name='guestbook_replace_at'";
		$objResult = $objDatabase->Execute($query);
		if ($objResult !== false) {
			if ($objResult->RecordCount() == 0) {
				$query = "INSERT INTO ".DBPREFIX."module_guestbook_settings ( `name` , `value` ) VALUES ( 'guestbook_replace_at', '1')";
				if ($objDatabase->Execute($query) === false) {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		} else {
			return $this->_databaseError($query, $objDatabase->ErrorMsg());
		}

		return true;
	}

	function _updateBackendAreas107to108()
	{
		global $objDatabase;

		$query = "SELECT access_id FROM ".DBPREFIX."backend_areas WHERE area_id=64 AND access_id=64";
		$objResult = $objDatabase->Execute($query);
		if ($objResult !== false) {
			if ($objResult->RecordCount() == 0) {
				$query = "UPDATE ".DBPREFIX."backend_areas` SET `access_id` = '64' WHERE `area_id` =64";
				if ($objDatabase->Execute($query) === false) {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		} else {
			return $this->_databaseError($query, $objDatabase->ErrorMsg());
		}

		return true;
	}

	function _createLostAndFoundSite107to108()
	{
		global $objDatabase;

		$objLanguage = FWLanguage();
		$arrLanguages = $objLanguage->getLanguageArray();
		foreach ($arrLanguages as $id => $arrLanguage) {
			$query = "SELECT catid FROM ".DBPREFIX."content_navigation WHERE cmd='lost_and_found' AND lang=".$id;
			$objResult = $objDatabase->Execute($query);
			if ($objResult !== false) {
				if ($objResult->RecordCount() == 0) {
					$query = "INSERT INTO ".DBPREFIX."content_navigation VALUES ('', '1', 0, 'Lost & Found', '', 9999, 'off', '0', '1', 'system', 1132500836, 'lost_and_found', ".$id.", 1, '0000-00-00', '0000-00-00', 0, 0, 0, 0)";
					if ($objDatabase->Execute($query) === false) {
						return $this->_databaseError($query, $objDatabase->ErrorMsg());
					}
					$catId = $objDatabase->Insert_ID();
				} else {
					$catId = $objResult->fields['catid'];
				}

				$query = "SELECT id FROM ".DBPREFIX."content WHERE id=".$catId;
				$objResult = $objDatabase->Execute($query);
				if ($objResult !== false) {
					if ($objResult->RecordCount() == 0) {
						$query = "INSERT INTO ".DBPREFIX."content` VALUES (".$catId.", 'Wiederhergestellte Seiten werden unter dieser Kategorie eingefügt.', 'Lost &amp; Found', 'Lost &amp; Found', 'Lost & Found', 'Lost & Found', 'index', '', '', 'y')";
						if ($objDatabase->Execute($query) === false) {
							return $this->_databaseError($query, $objDatabase->ErrorMsg());
						}
					}
				} else {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}
			} else {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}

}
?>
</form>
</body>
</html>