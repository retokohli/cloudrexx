<?php
function _coreUpdate()
{
	global $objDatabase, $_CONFIG, $_arrSuccessMsg;

	$arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."access_users");
	if (!is_array($arrColumns)) {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."access_users konnte nicht ermittelt werden!";
		return false;
	}

	if (!in_array('company', $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."access_users` ADD `company` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `webpage`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	} else {
		$query = "ALTER TABLE `".DBPREFIX."access_users` CHANGE `company` `company` VARCHAR( 255 ) NOT NULL DEFAULT ''";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array('zip', $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."access_users` ADD `zip` INT( 6 ) NOT NULL DEFAULT '0' AFTER `company`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	} else {
		$query = "ALTER TABLE `".DBPREFIX."access_users` CHANGE `zip` `zip` INT( 6 ) NOT NULL DEFAULT '0'";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array('phone', $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."access_users` ADD `phone` VARCHAR( 20 ) NOT NULL DEFAULT '' AFTER `zip`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	} else {
		$query = "ALTER TABLE `".DBPREFIX."access_users` CHANGE `phone` `phone` VARCHAR( 20 ) NOT NULL DEFAULT ''";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array('mobile', $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."access_users` ADD `mobile` VARCHAR( 20 ) NOT NULL DEFAULT '' AFTER `phone`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	} else {
		$query = "ALTER TABLE `".DBPREFIX."access_users` CHANGE `mobile` `mobile` VARCHAR( 20 ) NOT NULL DEFAULT ''";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array('street', $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."access_users` ADD `street` VARCHAR( 100 ) NOT NULL  DEFAULT '' AFTER `mobile`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	} else {
		$query = "ALTER TABLE `".DBPREFIX."access_users` CHANGE `street` `street` VARCHAR( 100 ) NOT NULL  DEFAULT ''";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (in_array('birthday', $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."access_users` DROP `birthday`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (in_array('show_birthday', $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."access_users` DROP `show_birthday`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	// update workflow
	$arrIndexes = $objDatabase->MetaIndexes(DBPREFIX."content_logfile");
	if ($arrIndexes === false) {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."content_logfile konnte nicht ermittelt werden!";
		return false;
	}

	if (!isset($arrIndexes['history_id'])) {
		$query = "ALTER TABLE `".DBPREFIX."content_logfile` ADD INDEX ( `history_id` )";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$query = "SELECT `frontend_access_id`, `backend_access_id` FROM `".DBPREFIX."content_navigation` WHERE `frontend_access_id` != 0 OR `backend_access_id` != 0";
	$objContent = $objDatabase->Execute($query);
	if ($objContent) {
		$arrAccessIds = array();

		while (!$objContent->EOF) {
			if ($objContent->fields['frontend_access_id'] > 0) {
				array_push($arrAccessIds, $objContent->fields['frontend_access_id']);
			}
			if ($objContent->fields['backend_access_id'] > 0) {
				array_push($arrAccessIds, $objContent->fields['backend_access_id']);
			}
			$objContent->MoveNext();
		}

		$arrAccessIdCount = array_count_values($arrAccessIds);

		$query = "SELECT `setvalue` FROM `".DBPREFIX."settings` WHERE `setname` = 'lastAccessId'";
		$objSettings = $objDatabase->SelectLimit($query, 1);
		if ($objSettings) {
			if ($objSettings->RecordCount() == 1) {
				$lastRightId = $objSettings->fields['setvalue'];
			} else {
				print "Konnte das Berechtigungssystem nicht aktualisieren!";
				return false;
			}
		} else {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}

		$query = "SELECT
			MAX(tblNav.`frontend_access_id`) AS maxFN,
			MAX(tblNav.`backend_access_id`) AS maxBN,
			MAX(tblHistory.`frontend_access_id`) AS maxFH,
			MAX(tblHistory.`backend_access_id`) AS maxBH
			FROM `".DBPREFIX."content_navigation` AS tblNav,
			`".DBPREFIX."content_navigation_history` AS tblHistory";
		$objResult = $objDatabase->SelectLimit($query, 1);
		if ($objResult) {
			if ($objResult->RecordCount() > 0) {
				$arrLastRightId = array(
					$objResult->fields['maxFN'],
					$objResult->fields['maxBN'],
					$objResult->fields['maxFH'],
					$objResult->fields['maxBH']
				);
				if ($lastRightId < max($arrLastRightId)) {
					$lastRightId = max($arrLastRightId);
					$query = "UPDATE `".DBPREFIX."settings` SET `setvalue` = '".$lastRightId."' WHERE `setname` = 'lastAccessId'";
					if ($objDatabase->Execute($query) === false) {
						return _databaseError($query, $objDatabase->ErrorMsg());
					}
				}
			}
		} else {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}

		require_once ASCMS_FRAMEWORK_PATH.'/User.class.php';
		$objUser = &new FWUser();
		$arrGroups = $objUser->getGroups();
		if (!is_array($arrGroups)) {
			print "Konnte die vorhandenen System Gruppen nicht ermitteln!";
			return false;
		}

		foreach ($arrAccessIdCount as $accessId => $count) {
			if ($count > 1) {
				// get associated groups
				$arrAssociatedGroups = array();
				$query = "SELECT `group_id` FROM `".DBPREFIX."access_group_dynamic_ids` WHERE `access_id` = ".$accessId;
				$objAccess = $objDatabase->Execute($query);
				if ($objAccess) {
					while (!$objAccess->EOF) {
						array_push($arrAssociatedGroups, $objAccess->fields['group_id']);
						$objAccess->MoveNext();
					}
					$arrAssociatedGroups = array_unique($arrAssociatedGroups);

					// get protected pages
					$query = "SELECT `catid`, `frontend_access_id`, `backend_access_id` FROM `".DBPREFIX."content_navigation` WHERE `frontend_access_id` = ".$accessId." OR `backend_access_id` = ".$accessId;
					$objContent = $objDatabase->Execute($query);
					if ($objContent) {
						while (!$objContent->EOF) {
							if ($objContent->fields['frontend_access_id'] > 0 && $arrAccessIdCount[$objContent->fields['frontend_access_id']] > 1) {
								$lastRightId++;

								$query = "SELECT 1 FROM `".DBPREFIX."content_navigation` WHERE `frontend_access_id` = ".$lastRightId." OR `backend_access_id` = ".$lastRightId;
								$objResult = $objDatabase->SelectLimit($query, 1);
								if ($objResult) {
									if ($objResult->RecordCount() == 1) {
										$lastRightId++;
									}
								} else {
									return _databaseError($query, $objDatabase->ErrorMsg());
								}

								$query = "UPDATE `".DBPREFIX."settings` SET `setvalue` = '".$lastRightId."' WHERE `setname` = 'lastAccessId'";
								if ($objDatabase->Execute($query) === false) {
									return _databaseError($query, $objDatabase->ErrorMsg());
								}

								$query = "UPDATE `".DBPREFIX."content_navigation` SET `frontend_access_id` = ".$lastRightId." WHERE `catid` = ".$objContent->fields['catid'];
								if ($objDatabase->Execute($query) === false) {
									return _databaseError($query, $objDatabase->ErrorMsg());
								}

								// don't check if the following query will be successful!
								$query = "UPDATE `".DBPREFIX."content_navigation_history` SET `frontend_access_id` = ".$lastRightId." WHERE `catid` = ".$objContent->fields['catid']." AND `frontend_access_id` = ".$accessId;
								$objDatabase->Execute($query);

								foreach ($arrAssociatedGroups as $groupId) {
									if ($arrGroups[$groupId]['type'] == 'frontend') {
										// don't check if the following query will be successful, because it is more important that this function continues than a group assiciation won't be applyed!
										$objDatabase->Execute("INSERT INTO `".DBPREFIX."access_group_dynamic_ids` (`access_id`, `group_id`) VALUES (".$lastRightId.", ".$groupId.")");
									}
								}
							}

							if ($objContent->fields['backend_access_id'] > 0 && $arrAccessIdCount[$objContent->fields['backend_access_id']] > 1) {
								$lastRightId++;

								$query = "SELECT 1 FROM `".DBPREFIX."content_navigation` WHERE `frontend_access_id` = ".$lastRightId." OR `backend_access_id` = ".$lastRightId;
								$objResult = $objDatabase->SelectLimit($query, 1);
								if ($objResult) {
									if ($objResult->RecordCount() == 1) {
										$lastRightId++;
									}
								} else {
									return _databaseError($query, $objDatabase->ErrorMsg());
								}

								$query = "UPDATE `".DBPREFIX."settings` SET `setvalue` = '".$lastRightId."' WHERE `setname` = 'lastAccessId'";
								if ($objDatabase->Execute($query) === false) {
									return _databaseError($query, $objDatabase->ErrorMsg());
								}

								$query = "UPDATE `".DBPREFIX."content_navigation` SET `backend_access_id` = ".$lastRightId." WHERE `catid` = ".$objContent->fields['catid'];
								if ($objDatabase->Execute($query) === false) {
									return _databaseError($query, $objDatabase->ErrorMsg());
								}

								// don't check if the following query will be successful!
								$query = "UPDATE `".DBPREFIX."content_navigation_history` SET `backend_access_id` = ".$lastRightId." WHERE `catid` = ".$objContent->fields['catid']." AND `backend_access_id` = ".$accessId;
								$objDatabase->Execute($query);

								foreach ($arrAssociatedGroups as $groupId) {
									if ($arrGroups[$groupId]['type'] == 'backend') {
										// don't check if the following query will be successful, because it is more important that this function continues than a group assiciation won't be applyed!
										$objDatabase->Execute("INSERT INTO `".DBPREFIX."access_group_dynamic_ids` (`access_id`, `group_id`) VALUES (".$lastRightId.", ".$groupId.")");
									}
								}
							}

							$objContent->MoveNext();
						}
					} else {
						return _databaseError($query, $objDatabase->ErrorMsg());
					}
				} else {
					return _databaseError($query, $objDatabase->ErrorMsg());
				}

				// don't check if the following query will be successful, because we won't either be able to execute it again!
				$objDatabase->Execute("DELETE FROM `".DBPREFIX."access_group_dynamic_ids` WHERE `access_id` = ".$accessId);
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = "SELECT `catid` FROM `".DBPREFIX."content_navigation`";
	$objContentNavigation = $objDatabase->Execute($query);
	if ($objContentNavigation !== false) {
		$arrContentSiteIds = array();
		while (!$objContentNavigation->EOF) {
			array_push($arrContentSiteIds, $objContentNavigation->fields['catid']);
			$objContentNavigation->MoveNext();
		}

		$query = "DELETE FROM `".DBPREFIX."content` WHERE `id` != ".implode(' AND `id` != ', $arrContentSiteIds);
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	// update content
	if (_isNewerVersion($_CONFIG['coreCmsVersion'], '1.0.9.10')) {
		$arrModules = array();
		$query = "SELECT `id`, `name` FROM `".DBPREFIX."modules`";
		$objResult = $objDatabase->Execute($query);
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				$arrModules[$objResult->fields['id']] = $objResult->fields['name'];
				$objResult->MoveNext();
			}
		} else {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}

    	$query = "
    		SELECT
	    		c.`id`,
	    		c.`content`,
	    		c.`title`,
	    		c.`metatitle`,
	    		c.`metadesc`,
	    		c.`metakeys`,
	    		c.`metarobots`,
	    		c.`css_name`,
	    		c.`redirect`,
	    		c.`expertmode`,
	    		n.`catid`,
	    		n.`parcat`,
	    		n.`catname`,
	    		n.`target`,
	    		n.`displayorder`,
	    		n.`displaystatus`,
	    		n.`cachingstatus`,
	    		n.`cmd`,
	    		n.`lang`,
	    		n.`module`,
	    		n.`startdate`,
	    		n.`enddate`,
	    		n.`protected`,
	    		n.`frontend_access_id`,
	    		n.`backend_access_id`,
	    		n.`themes_id`
    		FROM `".DBPREFIX."content` AS c
    		INNER JOIN `".DBPREFIX."content_navigation` AS n ON n.`catid` = c.`id`
    		WHERE n.`username` != 'sys_update'";
    	$objContent = $objDatabase->Execute($query);
    	if ($objContent !== false) {
    		$hasContact = false;
    		$arrFailedPages = array();
    		while (!$objContent->EOF) {
    			$query = "UPDATE
    				`".DBPREFIX."content` AS c INNER JOIN `".DBPREFIX."content_navigation` AS n ON n.`catid` = c.`id`
    			SET
    				c.`content` = '".addslashes(stripslashes($objContent->fields['content']))."',
    				c.`title` = '".$objContent->fields['title']."',
    				c.`metatitle` = '".$objContent->fields['metatitle']."',
    				c.`metadesc` = '".$objContent->fields['metadesc']."',
    				c.`metakeys` = '".$objContent->fields['metakeys']."',
    				c.`metarobots` = '".$objContent->fields['metarobots']."',
    				c.`css_name` = '".$objContent->fields['css_name']."',
    				c.`redirect` = '".$objContent->fields['redirect']."',
    				n.`catname` = '".$objContent->fields['catname']."',
    				n.`username` = 'sys_update',
    				n.`changelog` = ".time()."
    			WHERE
    				c.`id` = ".$objContent->fields['id'];
    			if ($objDatabase->Execute($query) === false) {
    				$s = isset($arrModules[$objContent->fields['module']]) ? $arrModules[$objContent->fields['module']] : '';
					$c = $objContent->fields['cmd'];
					$section = ($s=="") ? "" : "&amp;section=$s";
					$cmd = ($c=="") ? "" : "&amp;cmd=$c";

					switch ($s) {
						case 'gallery':
    						$query = "SELECT `id`, `catid` FROM `".DBPREFIX."module_gallery_pictures`";
    						$objGallery = $objDatabase->SelectLimit($query, 1);
    						if ($objGallery !== false) {
								if ($objGallery->RecordCount() == 1) {
									$s .= "&amp;cid=".$objGallery->fields['catid']."&amp;pId=".$objGallery->fields['id'];
								}
    						} else {
    							return _databaseError($query, $objDatabase->ErrorMsg());
							}
							break;

						case 'contact':
							$hasContact = true;
							break;

						default:
							break;
					}

					$link = (!empty($s)) ? "?section=".$s.$cmd : "?page=".$objContent->fields['catid'].$section.$cmd;
    				$arrFailedPages[$objContent->fields['id']] = array('title' => $objContent->fields['catname'], 'link' => 'index.php'.$link.'&amp;langId='.$objContent->fields['lang']);
    			} else {
	    			$objDatabase->Execute("UPDATE `".DBPREFIX."content_navigation_history` SET `is_active` = '0' WHERE `catid` = ".$objContent->fields['id']);
	    			$objDatabase->Execute("
	    				INSERT INTO `".DBPREFIX."content_navigation_history`
						SET
							`is_active` = '1',
							`catid` = ".$objContent->fields['id'].",
							`parcat` = ".$objContent->fields['parcat'].",
							`catname` = '".$objContent->fields['catname']."',
							`target` = '".$objContent->fields['target']."',
							`displayorder` = ".$objContent->fields['displayorder'].",
							`displaystatus` = '".$objContent->fields['displaystatus']."',
							`cachingstatus` = '".$objContent->fields['cachingstatus']."',
							`username` = 'sys_update',
							`changelog` = ".time().",
							`cmd` = '".$objContent->fields['cmd']."',
							`lang` = ".$objContent->fields['lang'].",
							`module` = ".$objContent->fields['module'].",
							`startdate` = '".$objContent->fields['startdate']."',
							`enddate` = '".$objContent->fields['enddate']."',
							`protected` = ".$objContent->fields['protected'].",
							`frontend_access_id` = ".$objContent->fields['frontend_access_id'].",
							`backend_access_id` = ".$objContent->fields['backend_access_id'].",
							`themes_id` = ".$objContent->fields['themes_id']
					);

					$historyId = $objDatabase->Insert_ID();

					$objDatabase->Execute("
						INSERT INTO `".DBPREFIX."content_history`
						SET
							`id` = ".$historyId.",
							`page_id` = ".$objContent->fields['id'].",
							`content` = '".addslashes(stripslashes($objContent->fields['content']))."',
							`title` = '".$objContent->fields['title']."',
							`metatitle` = '".$objContent->fields['metatitle']."',
							`metadesc` = '".$objContent->fields['metadesc']."',
							`metakeys` = '".$objContent->fields['metakeys']."',
							`metarobots` = '".$objContent->fields['metarobots']."',
							`css_name` = '".$objContent->fields['css_name']."',
							`redirect` = '".$objContent->fields['redirect']."',
							`expertmode` = '".$objContent->fields['expertmode']."'
					");

					$objDatabase->Execute("
						INSERT INTO	`".DBPREFIX."content_logfile`
						SET
							`action` = 'update',
							`history_id` = ".$historyId.",
							`is_validated` = '1'
					");
    			}

    			$objContent->MoveNext();
    		}

    		if (count($arrFailedPages)) {
    			array_push($_arrSuccessMsg, 'Bitte überprüfen Sie bei den folgenden Inhaltsseiten das Layout und dessen Funktion auf ihre Korrektheit!');

        			$pages = '<ul>';
        			foreach ($arrFailedPages as $arrPage) {
        				$pages .= "<li><a href='".$arrPage['link']."' target='_blank'>".$arrPage['title']." (".$arrPage['link'].")</a></li>";
        			}
        			$pages .= '</ul>';
        			array_push($_arrSuccessMsg, $pages);
    		}

    	} else {
    		return _databaseError($query, $objDatabase->ErrorMsg());
    	}
	}

	$arrIndexes = $objDatabase->MetaIndexes(DBPREFIX.'content_navigation');
	if ($arrIndexes === false) {
		print "Die Struktur der Tabelle '".DBPREFIX."content_navigation' konnte nicht ermittelt werden!";
		return false;
	}

	if (isset($arrIndexes['catid'])) {
		$query = "ALTER TABLE `".DBPREFIX."content_navigation` DROP INDEX `catid`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$query = "SELECT css_name FROM ".DBPREFIX."content_navigation";

	if ($objDatabase->Execute($query) === false) {
		$query = "ALTER TABLE `".DBPREFIX."content_navigation` ADD `css_name` VARCHAR( 255 ) NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$query = "SELECT css_name FROM ".DBPREFIX."content_navigation_history";

	if ($objDatabase->Execute($query) === false) {
		$query = "ALTER TABLE `".DBPREFIX."content_navigation_history` ADD `css_name` VARCHAR( 255 ) NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	return true;
}
?>