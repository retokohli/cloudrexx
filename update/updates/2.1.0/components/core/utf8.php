<?php
function _utf8Update()
{
	global $objUpdate, $_DBCONFIG, $objDatabase, $_ARRAYLANG, $_CORELANG;

	if (!isset($_DBCONFIG['charset']) || $_DBCONFIG['charset'] != 'utf8') {
		$preferedCollation = 'utf8_unicode_ci';

		$arrCollations = _getUtf8Collations();
		if (!is_array($arrCollations)) {
			return $arrCollations;
		}

		if (empty($_SESSION['contrexx_update']['update']['core']['utf8_collation'])) {
			if (isset($_POST['dbCollation']) && in_array($objUpdate->stripslashes($_POST['dbCollation']), $arrCollations)) {
				$_SESSION['contrexx_update']['update']['core']['utf8_collation'] = $objUpdate->stripslashes($_POST['dbCollation']);
			} else {
				$collationMenu = '<select name="dbCollation">';
				foreach ($arrCollations as $collation) {
					$collationMenu .= '<option value="'.$collation.'"'.($collation == $preferedCollation ? ' selected="selected"' : '').'>'.$collation.'</option>';
				}
				$collationMenu .= '</select><br />';

				setUpdateMsg($_ARRAYLANG['TXT_SELECT_DB_COLLATION'], 'title');
				setUpdateMsg(sprintf($_ARRAYLANG['TXT_SELECT_DB_COLLATION_MSG'].'<br /><br />', $collationMenu), 'msg');
				setUpdateMsg('<input type="submit" value="'.$_CORELANG['TXT_CONTINUE_UPDATE'].'" name="updateNext" /><input type="hidden" name="processUpdate" id="processUpdate" />', 'button');
				return false;
			}
		}

		// SET DATABASE CHARSET AND COLLATION
		$query = "SHOW CREATE DATABASE `".$_DBCONFIG['database']."`";
		$objDbStatement = $objDatabase->Execute($query);
		if ($objDbStatement !== false) {
			if (!preg_match('#DEFAULT\sCHARACTER\sSET\sutf8\sCOLLATE\s'.$_SESSION['contrexx_update']['update']['core']['utf8_collation'].'#s', $objDbStatement->fields['Create Database'])) {
				$query = "ALTER DATABASE `".$_DBCONFIG['database']."` DEFAULT CHARACTER SET utf8 COLLATE ".$objUpdate->addslashes($_SESSION['contrexx_update']['update']['core']['utf8_collation']);
				if ($objDatabase->Execute($query) === false) {
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		} else {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}


		// CHANGE TABLE CHARSET AND COLLATION
		$arrContrexxTables = array(
			DBPREFIX.'module_alias_source',
			DBPREFIX.'module_alias_target',
			DBPREFIX.'module_block_blocks',
			DBPREFIX.'module_block_rel_lang',
			DBPREFIX.'module_block_rel_pages',
			DBPREFIX.'module_block_settings',
			DBPREFIX.'module_calendar',
			DBPREFIX.'module_calendar_categories',
			DBPREFIX.'module_calendar_style',
			DBPREFIX.'community_config',
			DBPREFIX.'module_contact_form',
			DBPREFIX.'module_contact_form_data',
			DBPREFIX.'module_contact_form_field',
			DBPREFIX.'module_contact_settings',
			DBPREFIX.'access_group_dynamic_ids',
			DBPREFIX.'access_group_static_ids',
			DBPREFIX.'access_users',
			DBPREFIX.'access_user_groups',
			DBPREFIX.'backend_areas',
			DBPREFIX.'backups',
			DBPREFIX.'content',
			DBPREFIX.'content_history',
			DBPREFIX.'content_logfile',
			DBPREFIX.'content_navigation',
			DBPREFIX.'content_navigation_history',
			DBPREFIX.'ids',
			DBPREFIX.'languages',
			DBPREFIX.'log',
			DBPREFIX.'modules',
			DBPREFIX.'module_repository',
			DBPREFIX.'sessions',
			DBPREFIX.'settings',
			DBPREFIX.'settings_smtp',
			DBPREFIX.'skins',
			DBPREFIX.'module_directory_access',
			DBPREFIX.'module_directory_categories',
			DBPREFIX.'module_directory_dir',
			DBPREFIX.'module_directory_inputfields',
			DBPREFIX.'module_directory_levels',
			DBPREFIX.'module_directory_mail',
			DBPREFIX.'module_directory_rel_dir_cat',
			DBPREFIX.'module_directory_rel_dir_level',
			DBPREFIX.'module_directory_settings',
			DBPREFIX.'module_directory_settings_google',
			DBPREFIX.'module_directory_vote',
			DBPREFIX.'module_docsys',
			DBPREFIX.'module_docsys_categories',
			DBPREFIX.'module_egov_orders',
			DBPREFIX.'module_egov_products',
			DBPREFIX.'module_egov_product_calendar',
			DBPREFIX.'module_egov_product_fields',
			DBPREFIX.'module_egov_settings',
			DBPREFIX.'module_feed_category',
			DBPREFIX.'module_feed_news',
			DBPREFIX.'module_feed_newsml_association',
			DBPREFIX.'module_feed_newsml_categories',
			DBPREFIX.'module_feed_newsml_documents',
			DBPREFIX.'module_feed_newsml_providers',
			DBPREFIX.'module_forum_access',
			DBPREFIX.'module_forum_categories',
			DBPREFIX.'module_forum_categories_lang',
			DBPREFIX.'module_forum_notification',
			DBPREFIX.'module_forum_postings',
			DBPREFIX.'module_forum_settings',
			DBPREFIX.'module_forum_statistics',
			DBPREFIX.'module_gallery_categories',
			DBPREFIX.'module_gallery_comments',
			DBPREFIX.'module_gallery_language',
			DBPREFIX.'module_gallery_language_pics',
			DBPREFIX.'module_gallery_pictures',
			DBPREFIX.'module_gallery_settings',
			DBPREFIX.'module_gallery_votes',
			DBPREFIX.'module_guestbook',
			DBPREFIX.'module_guestbook_settings',
			DBPREFIX.'module_livecam_settings',
			DBPREFIX.'module_market',
			DBPREFIX.'module_market_access',
			DBPREFIX.'module_market_categories',
			DBPREFIX.'module_market_mail',
			DBPREFIX.'module_market_paypal',
			DBPREFIX.'module_market_settings',
			DBPREFIX.'module_market_spez_fields',
			DBPREFIX.'module_memberdir_directories',
			DBPREFIX.'module_memberdir_name',
			DBPREFIX.'module_memberdir_settings',
			DBPREFIX.'module_memberdir_values',
			DBPREFIX.'module_news',
			DBPREFIX.'module_news_access',
			DBPREFIX.'module_news_categories',
			DBPREFIX.'module_news_settings',
			DBPREFIX.'module_news_teaser_frame',
			DBPREFIX.'module_news_teaser_frame_templates',
			DBPREFIX.'module_news_ticker',
			DBPREFIX.'module_newsletter',
			DBPREFIX.'module_newsletter_attachment',
			DBPREFIX.'module_newsletter_category',
			DBPREFIX.'module_newsletter_confirm_mail',
			DBPREFIX.'module_newsletter_rel_cat_news',
			DBPREFIX.'module_newsletter_rel_user_cat',
			DBPREFIX.'module_newsletter_settings',
			DBPREFIX.'module_newsletter_system',
			DBPREFIX.'module_newsletter_template',
			DBPREFIX.'module_newsletter_tmp_sending',
			DBPREFIX.'module_newsletter_user',
			DBPREFIX.'module_newsletter_user_title',
			DBPREFIX.'module_podcast_category',
			DBPREFIX.'module_podcast_medium',
			DBPREFIX.'module_podcast_rel_category_lang',
			DBPREFIX.'module_podcast_rel_medium_category',
			DBPREFIX.'module_podcast_settings',
			DBPREFIX.'module_podcast_template',
			DBPREFIX.'module_recommend',
			DBPREFIX.'module_shop_categories',
			DBPREFIX.'module_shop_config',
			DBPREFIX.'module_shop_countries',
			DBPREFIX.'module_shop_currencies',
			DBPREFIX.'module_shop_customers',
			DBPREFIX.'module_shop_importimg',
			DBPREFIX.'module_shop_lsv',
			DBPREFIX.'module_shop_mail',
			DBPREFIX.'module_shop_mail_content',
			DBPREFIX.'module_shop_orders',
			DBPREFIX.'module_shop_order_items',
			DBPREFIX.'module_shop_order_items_attributes',
			DBPREFIX.'module_shop_payment',
			DBPREFIX.'module_shop_payment_processors',
			DBPREFIX.'module_shop_pricelists',
			DBPREFIX.'module_shop_products',
			DBPREFIX.'module_shop_products_attributes',
			DBPREFIX.'module_shop_products_attributes_name',
			DBPREFIX.'module_shop_products_attributes_value',
			DBPREFIX.'module_shop_products_downloads',
			DBPREFIX.'module_shop_rel_countries',
			DBPREFIX.'module_shop_rel_payment',
			DBPREFIX.'module_shop_rel_shipment',
			DBPREFIX.'module_shop_shipment_cost',
			DBPREFIX.'module_shop_shipper',
			DBPREFIX.'module_shop_vat',
			DBPREFIX.'module_shop_zones',
			DBPREFIX.'stats_browser',
			DBPREFIX.'stats_colourdepth',
			DBPREFIX.'stats_config',
			DBPREFIX.'stats_country',
			DBPREFIX.'stats_hostname',
			DBPREFIX.'stats_javascript',
			DBPREFIX.'stats_operatingsystem',
			DBPREFIX.'stats_referer',
			DBPREFIX.'stats_requests',
			DBPREFIX.'stats_requests_summary',
			DBPREFIX.'stats_screenresolution',
			DBPREFIX.'stats_search',
			DBPREFIX.'stats_spiders',
			DBPREFIX.'stats_spiders_summary',
			DBPREFIX.'stats_visitors',
			DBPREFIX.'stats_visitors_summary',
			DBPREFIX.'voting_email',
			DBPREFIX.'voting_rel_email_system',
			DBPREFIX.'voting_results',
			DBPREFIX.'voting_system'
		);

		$query = "SHOW TABLE STATUS LIKE '".DBPREFIX."%'";
		$objInstalledTable = $objDatabase->Execute($query);
		if ($objInstalledTable !== false) {
			while (!$objInstalledTable->EOF) {
				$arrInstalledTables[$objInstalledTable->fields['Name']] = $objInstalledTable->fields['Collation'];
				$objInstalledTable->MoveNext();
			}
		} else {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}

		foreach ($arrContrexxTables as $table) {
			$converted = false;

			if (in_array($table, array_keys($arrInstalledTables))) {
				if ($arrInstalledTables[$table] == $_SESSION['contrexx_update']['update']['core']['utf8_collation']) {
					continue;
				} else {
					if (!in_array($table.'_new', $arrInstalledTables)) {
						$objTableStructure = $objDatabase->Execute("SHOW CREATE TABLE `".$table."`");
						if ($objTableStructure === false) {
							return _databaseError($query, $objDatabase->ErrorMsg());
						}

						$objTableStructure->fields['Create Table'] = preg_replace(
							array(
								'/'.$table.'/',
								'/default current_timestamp on update current_timestamp/i',
								'/collate[\s|=][a-z0-9_]+/i',
								'/default charset=[a-z0-9_]+/i',
								'/engine=myisam/i'
							),
							array(
								$table.'_new',
								'',
							),
							$objTableStructure->fields['Create Table']
						);

						$query = $objTableStructure->fields['Create Table']." TYPE=MyISAM DEFAULT CHARSET=utf8 COLLATE=".$objUpdate->addslashes($_SESSION['contrexx_update']['update']['core']['utf8_collation']).";\n";
						if ($objDatabase->Execute($query) === false) {
							return _databaseError($query, $objDatabase->ErrorMsg());
						}
					}

					$query = "SELECT COUNT(1) AS rowCount FROM `".$table."`";
					if (($objResult = $objDatabase->SelectLimit($query, 1)) !== false) {
						$oriCount = $objResult->fields['rowCount'];
					} else {
						return _databaseError($query, $objDatabase->ErrorMsg());
					}

					$query = "SELECT COUNT(1) AS rowCount FROM `".$table."_new`";
					if (($objResult = $objDatabase->SelectLimit($query, 1)) !== false) {
						$newCount = $objResult->fields['rowCount'];
					} else {
						return _databaseError($query, $objDatabase->ErrorMsg());
					}

					if ($oriCount !== $newCount) {
						$query = "TRUNCATE TABLE `".$table."_new`";
						if ($objDatabase->Execute($query) === false) {
							return _databaseError($query, $objDatabase->ErrorMsg());
						}

						$query = "INSERT INTO `".$table."_new` SELECT * FROM `".$table."`";
						if ($objDatabase->Execute($query) === false) {
							return _databaseError($query, $objDatabase->ErrorMsg());
						}
					}

					$query = "DROP TABLE `".$table."`";
					if ($objDatabase->Execute($query) === false) {
						return _databaseError($query, $objDatabase->ErrorMsg());
					}

					$converted = true;
				}
			}

			if (in_array($table.'_new', $arrInstalledTables) || $converted) {
				$query = "RENAME TABLE `".$table."_new`  TO `".$table."`";
				if ($objDatabase->Execute($query) === false) {
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		}

		if (!isset($_SESSION['contrexx_update']['update']['utf'])) {
			if (_convertThemes2UTF()) {
				$_SESSION['contrexx_update']['update']['utf'] = true;
			} else {
				return false;
			}
		}
	}

	return true;
}

function _getUtf8Collations()
{
	global $objDatabase;

	$arrCollate = array();

	$query = 'SHOW COLLATION';
	$objCollation = $objDatabase->Execute($query);
	if ($objCollation !== false) {
		while (!$objCollation->EOF) {
			if ($objCollation->fields['Charset'] == 'utf8') {
				$arrCollate[] = $objCollation->fields['Collation'];
			}
			$objCollation->MoveNext();
		}

		return $arrCollate;
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}
}

function _convertThemes2UTF()
{
	global $objDatabase, $_CORELANG, $_ARRAYLANG;

	// get installed themes
	$query = 'SELECT themesname, foldername FROM `'.DBPREFIX.'skins`';
	$objTheme = $objDatabase->Execute($query);
	$arrThemes = array();
	if ($objTheme !== false) {
		while (!$objTheme->EOF) {
			$arrThemes[$objTheme->fields['themesname']] = $objTheme->fields['foldername'];
			$objTheme->MoveNext();
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	if (count($arrThemes)) {
		foreach ($arrThemes as $path) {
			if (!isset($_SESSION['contrexx_update']['update']['utf_themes'][$path])) {
				$_SESSION['contrexx_update']['update']['utf_themes'][$path] = array();
			}
			$dh = @opendir(ASCMS_THEMES_PATH.'/'.$path);
			if ($dh !== false) {
				while (($file = @readdir($dh)) !== false) {
					if (substr($file, -5) == '.html') {
						if (!in_array($file, $_SESSION['contrexx_update']['update']['utf_themes'][$path])) {
							$content = file_get_contents(ASCMS_THEMES_PATH.'/'.$path.'/'.$file);
					    	$fh = @fopen(ASCMS_THEMES_PATH.'/'.$path.'/'.$file, 'wb');
					    	if ($fh !== false) {
					    		$status = true;
						    	if (@fwrite($fh, utf8_encode($content)) !== false) {
						    		$_SESSION['contrexx_update']['update']['utf_themes'][$path][] = $file;
						    	} else {
						    		setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_CONVERT_FILE'], ASCMS_THEMES_PATH.'/'.$path.'/'.$file));
						    		$status = false;
						    	}
						    	@fclose($fh);

						    	if (!$status) {
						    		return false;
						    	}
					    	} else {
					    		setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_WRITE_FILE'], ASCMS_THEMES_PATH.'/'.$path.'/'.$file));
					    		setUpdateMsg(sprintf($_ARRAYLANG['TXT_SET_WRITE_PERMISSON_TO_FILE'], ASCMS_THEMES_PATH.'/'.$path.'/'.$file, $_CORELANG['TXT_UPDATE_TRY_AGAIN']), 'msg');
					    		return false;
					    	}
						}
					}
				}

				@closedir($dh);
			}
		}
	}

	return true;
}
?>
