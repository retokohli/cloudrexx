<?php
function _egovUpdate()
{
	global $objDatabase;
	/********************************************************************
	 * Create tables
	 *
	 */
	$arrTables = $objDatabase->MetaTables('TABLES');
	if ($arrTables !== false) {
		if (!in_array(DBPREFIX."module_egov_orders", $arrTables)) {
			$query = "CREATE TABLE `".DBPREFIX."module_egov_orders` (
				  `order_id` int(11) NOT NULL auto_increment,
				  `order_date` datetime NOT NULL default '0000-00-00 00:00:00',
				  `order_ip` varchar(255) NOT NULL default '',
				  `order_product` int(11) NOT NULL default '0',
				  `order_values` text NOT NULL,
				  `order_state` tinyint(4) NOT NULL default '0',
				  `order_quant` tinyint(4) NOT NULL default '1',
				  PRIMARY KEY  (`order_id`),
				  KEY `order_product` (`order_product`)
				) TYPE=MyISAM";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}

			$query = "INSERT INTO `".DBPREFIX."module_egov_orders` VALUES (1, '2006-11-14 10:08:00', '127.0.0.1', 1, 'Name::Muster;;Vorname::Hans;;E-Mail::noreply@example.com;;', 1, 1)";
			$objDatabase->Execute($query);
		}

		if (!in_array(DBPREFIX."module_egov_product_calendar", $arrTables)) {
			$query = "CREATE TABLE `".DBPREFIX."module_egov_product_calendar` (
				`calendar_id` int(11) NOT NULL auto_increment,
				`calendar_product` int(11) NOT NULL default '0',
				`calendar_order` int(11) NOT NULL default '0',
				`calendar_day` int(2) NOT NULL default '0',
				`calendar_month` int(2) NOT NULL default '0',
				`calendar_year` int(4) NOT NULL default '0',
				`calendar_act` tinyint(1) NOT NULL default '0',
				PRIMARY KEY  (`calendar_id`),
				KEY `calendar_product` (`calendar_product`)
				) TYPE=MyISAM";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."module_egov_product_fields", $arrTables)) {
			$query = "CREATE TABLE `".DBPREFIX."module_egov_product_fields` (
				`id` int(10) unsigned NOT NULL auto_increment,
				`product` int(10) unsigned NOT NULL default '0',
				`name` varchar(255) NOT NULL default '',
				`type` enum('text','label','checkbox','checkboxGroup','file','hidden','password','radio','select','textarea') NOT NULL default 'text',
				`attributes` text NOT NULL,
				`is_required` set('0','1') NOT NULL default '0',
				`check_type` int(3) NOT NULL default '1',
				`order_id` smallint(5) unsigned NOT NULL default '0',
				PRIMARY KEY  (`id`),
				KEY `product` (`product`)
				) TYPE=MyISAM";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."module_egov_products", $arrTables)) {
			$query = "CREATE TABLE `".DBPREFIX."module_egov_products` (
				`product_id` int(11) NOT NULL auto_increment,
				`product_autostatus` tinyint(1) NOT NULL default '0',
				`product_name` varchar(255) NOT NULL default '',
				`product_desc` text NOT NULL,
				`product_price` decimal(11,2) NOT NULL default '0.00',
				`product_per_day` enum('yes','no') NOT NULL default 'no',
				`product_quantity` tinyint(2) NOT NULL default '0',
				`product_target_email` varchar(255) NOT NULL default '',
				`product_target_url` varchar(255) NOT NULL default '',
				`product_message` text NOT NULL,
				`product_status` tinyint(1) NOT NULL default '1',
				`product_electro` tinyint(1) NOT NULL default '0',
				`product_file` varchar(255) NOT NULL default '',
				`product_sender_name` varchar(255) NOT NULL default '',
				`product_sender_email` varchar(255) NOT NULL default '',
				PRIMARY KEY  (`product_id`)
				) TYPE=MyISAM";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}

			$query = "INSERT INTO `".DBPREFIX."module_egov_products` VALUES (1, 1, 'Contrexx Logo', 'Bestellen Sie sich das Contrexx Logo:', 0.00, 'no', 0, '', '', 'Vielen Dank f&uuml;r Ihre Bestellung! Sie werden das Logo per E-Mail erhalten.', 1, 1, '/images/content/negative_72dpi.png', 'Contrexx Online Desk', 'noreply@example.com')";
			if ($objDatabase->Execute($query) !== false) {
				$objDatabase->Execute("INSERT INTO `".DBPREFIX."module_egov_product_fields` VALUES (1, 1, 'Name', 'text', '', '0', 1, 0)");
				$objDatabase->Execute("INSERT INTO `".DBPREFIX."module_egov_product_fields` VALUES (2, 1, 'Vorname', 'text', '', '0', 1, 1)");
				$objDatabase->Execute("INSERT INTO `".DBPREFIX."module_egov_product_fields` VALUES (3, 1, 'E-Mail', 'text', '', '1', 2, 2)");
			}
		}

		if (!in_array(DBPREFIX."module_egov_settings", $arrTables)) {
			$query = "CREATE TABLE `".DBPREFIX."module_egov_settings` (
				`set_id` int(11) NOT NULL default '0',
				`set_sender_name` varchar(255) NOT NULL default '',
				`set_sender_email` varchar(255) NOT NULL default '',
				`set_recipient_email` varchar(255) NOT NULL default '',
				`set_state_subject` varchar(255) NOT NULL default '',
				`set_state_email` text NOT NULL,
				`set_calendar_color_1` varchar(255) NOT NULL default '',
				`set_calendar_color_2` varchar(255) NOT NULL default '',
				`set_calendar_color_3` varchar(255) NOT NULL default '',
				`set_calendar_legende_1` varchar(255) NOT NULL default '',
				`set_calendar_legende_2` varchar(255) NOT NULL default '',
				`set_calendar_legende_3` varchar(255) NOT NULL default '',
				`set_calendar_background` varchar(255) NOT NULL default '',
				`set_calendar_border` varchar(255) NOT NULL default '',
				`set_calendar_date_label` varchar(255) NOT NULL default '',
				`set_calendar_date_desc` varchar(255) NOT NULL default '',
				`set_orderentry_subject` varchar(255) NOT NULL default '',
				`set_orderentry_email` text NOT NULL,
				`set_orderentry_name` varchar(255) NOT NULL default '',
				`set_orderentry_sender` varchar(255) NOT NULL default '',
				`set_orderentry_recipient` varchar(255) NOT NULL default '',
				KEY `set_id` (`set_id`)
				) TYPE=MyISAM";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		$query = "SELECT 1 FROM `".DBPREFIX."module_egov_settings`";
		$objSettings = $objDatabase->SelectLimit($query, 1);
		if ($objSettings !== false) {
			if ($objSettings->RecordCount() == 0) {
				$query = "INSERT INTO `".DBPREFIX."module_egov_settings` VALUES (1, 'Contrexx Demo', '', '', 'Bestellung/Anfrage: [[PRODUCT_NAME]]', 'Guten Tag\r\n\r\nHerzlichen Dank für Ihren Besuch bei der Contrexx Demo Webseite.\r\nIhre Bestellung/Anfrage wurde bearbeitet. Falls es sich um ein Download Produkt handelt, finden Sie ihre Bestellung im Anhang.\r\n\r\nIhre Angaben:\r\n[[ORDER_VALUE]]\r\n\r\nFreundliche Grüsse\r\nIhr Online-Team', '#D5FFDA', '#F7FFB4', '#FFAEAE', 'Freie Tage', 'Teilweise Reserviert', 'Reserviert', '#FFFFFFF', '#C9C9C9', 'Reservieren für das ausgewählte Datum', '(Das Datum wird durch das Anklicken im Kalender übernommen.)', 'Bestellung/Anfrage für [[PRODUCT_NAME]] eingegangen', 'Diese Daten wurden eingegeben:\r\n\r\n[[ORDER_VALUE]]\r\n', 'Contrexx Demo Webseite', '', '')";
				if ($objDatabase->Execute($query) === false) {
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		} else {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}

	} else {
		print "Die Struktur der Datenkbank konnte nicht ermittelt werden!";
		return false;
	}

	$arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."module_egov_products");
	if (!is_array($arrColumns)) {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_egov_products konnte nicht ermittelt werden!";
		return false;
	}

	if (!in_array('product_target_subject', $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_egov_products` ADD `product_target_subject` VARCHAR( 255 ) NOT NULL AFTER `product_sender_email`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array('product_target_body', $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_egov_products` ADD `product_target_body` TEXT NOT NULL AFTER `product_target_subject`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	return true;
}
?>