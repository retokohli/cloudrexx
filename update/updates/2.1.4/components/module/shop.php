<?php

function _shopUpdate()
{
    global $objDatabase, $_ARRAYLANG;


    // Shop settings
    // Shop thumbnail default settings: shop_thumbnail_max_width
    $query = "
        SELECT 1 FROM ".DBPREFIX."module_shop_config
        WHERE name='shop_thumbnail_max_width'";
    $objResult = $objDatabase->Execute($query);
    if ($objResult) {
        if ($objResult->RecordCount() == 0) {
            $query = "
                INSERT INTO ".DBPREFIX."module_shop_config (
                    name, value
                ) VALUES (
                    'shop_thumbnail_max_width', '120'
                )
            ";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
            } else {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    // Shop thumbnail default settings: shop_thumbnail_max_height
    $query = "
        SELECT 1 FROM ".DBPREFIX."module_shop_config
        WHERE name='shop_thumbnail_max_height'";
    $objResult = $objDatabase->Execute($query);
    if ($objResult) {
        if ($objResult->RecordCount() == 0) {
            $query = "
                INSERT INTO ".DBPREFIX."module_shop_config (
                    name, value
                ) VALUES (
                    'shop_thumbnail_max_height', '90'
                )
            ";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
            } else {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    // Shop thumbnail default settings: shop_thumbnail_quality
    $query = "
        SELECT 1 FROM ".DBPREFIX."module_shop_config
        WHERE name='shop_thumbnail_quality'";
    $objResult = $objDatabase->Execute($query);
    if ($objResult) {
        if ($objResult->RecordCount() == 0) {
            $query = "
                INSERT INTO ".DBPREFIX."module_shop_config (
                    name, value
                ) VALUES (
                    'shop_thumbnail_quality', '80'
                )
            ";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
            } else {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }


    // Add Yellowpay payment methods default settings:
    // Accepted payment methods
    $query = "
        SELECT 1 FROM ".DBPREFIX."module_shop_config
        WHERE name='yellowpay_accepted_payment_methods'";
    $objResult = $objDatabase->Execute($query);
    if ($objResult) {
        if ($objResult->RecordCount() == 0) {
            $query = "
                INSERT INTO ".DBPREFIX."module_shop_config (
                    `id`, `name`, `value`, `status`
                ) VALUES (
                    NULL, 'yellowpay_accepted_payment_methods', '', '1'
                );
            ";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
            } else {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    // Change old yellowpay_delivery_payment_type setting
    // to new yellowpay_authorization_type
    $query = "
        SELECT 1 FROM ".DBPREFIX."module_shop_config
        WHERE `name`='yellowpay_delivery_payment_type'";
    $objResult = $objDatabase->Execute($query);
    if ($objResult) {
        if ($objResult->RecordCount() == 1) {
            $query = "
                UPDATE ".DBPREFIX."module_shop_config
                   SET `name`='yellowpay_authorization_type'
                 WHERE `name`='yellowpay_delivery_payment_type'
            ";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
            } else {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    // Add yellowpay test server flag setting
    $query = "
        SELECT 1 FROM ".DBPREFIX."module_shop_config
        WHERE `name`='yellowpay_use_testserver'";
    $objResult = $objDatabase->Execute($query);
    if ($objResult) {
        if ($objResult->RecordCount() == 0) {
            $query = "
                INSERT INTO ".DBPREFIX."module_shop_config (
                    `id`, `name`, `value`, `status`
                ) VALUES (
                    NULL, 'yellowpay_use_testserver', '1', '1'
                );
            ";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
            } else {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    // Add weight enable flag setting
    $query = "
        SELECT 1 FROM ".DBPREFIX."module_shop_config
        WHERE `name`='shop_weight_enable'";
    $objResult = $objDatabase->Execute($query);
    if ($objResult) {
        if ($objResult->RecordCount() == 0) {
            $query = "
                INSERT INTO `".DBPREFIX."module_shop_config` (
                    `id`, `name`, `value`, `status`
                ) VALUES (
                    NULL, 'shop_weight_enable', '1', '1'
                );
            ";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
            } else {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }


    // Add shop_show_products_default:
    // Which products are shown on the first shop page?
    $query = "
        SELECT 1 FROM ".DBPREFIX."module_shop_config
        WHERE `name`='shop_show_products_default'";
    $objResult = $objDatabase->Execute($query);
    if (!$objResult) return _databaseError($query, $objDatabase->ErrorMsg());
    if ($objResult->RecordCount() == 0) {
        $query = "
            INSERT INTO `".DBPREFIX."module_shop_config` (
                `name`, `value`
            ) VALUES (
                'shop_show_products_default', '1'
            );
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }


    // Update VAT settings
    $query = "
        SELECT `value` FROM ".DBPREFIX."module_shop_config
        WHERE `name`='tax_enabled'";
    $objResult = $objDatabase->Execute($query);
    if (!$objResult) return _databaseError($query, $objDatabase->ErrorMsg());
    if ($objResult->RecordCount()) {
   	    $flagVatEnabled = $objResult->fields['value'];
	    $arrVatEnabled = array(
	        'vat_enabled_foreign_customer',
	        'vat_enabled_foreign_reseller',
	        'vat_enabled_home_customer',
	        'vat_enabled_home_reseller',
	    );
	    foreach ($arrVatEnabled as $strSetting) {
	        $query = "
	            SELECT 1 FROM ".DBPREFIX."module_shop_config
	            WHERE `name`='$strSetting'";
	        $objResult = $objDatabase->Execute($query);
	        if (!$objResult) return _databaseError($query, $objDatabase->ErrorMsg());
	        if ($objResult->RecordCount() == 0) {
	            $query = "
	                INSERT INTO `".DBPREFIX."module_shop_config` (
	                    `name`, `value`
	                ) VALUES (
	                    '$strSetting', '$flagVatEnabled'
	                );
	            ";
	            $objResult = $objDatabase->Execute($query);
	            if (!$objResult)
	                return _databaseError($query, $objDatabase->ErrorMsg());
	        }
	    }
    }

    $query = "
        SELECT `value` FROM ".DBPREFIX."module_shop_config
        WHERE `name`='tax_included'";
    $objResult = $objDatabase->Execute($query);
    if (!$objResult) return _databaseError($query, $objDatabase->ErrorMsg());
    if ($objResult->RecordCount()) {
        $flagVatIncluded = $objResult->fields['value'];
	    $arrVatIncluded = array(
	        'vat_included_foreign_customer',
	        'vat_included_foreign_reseller',
	        'vat_included_home_customer',
	        'vat_included_home_reseller',
	    );
	    foreach ($arrVatIncluded as $strSetting) {
	        $query = "
	            SELECT 1 FROM ".DBPREFIX."module_shop_config
	            WHERE `name`='$strSetting'";
	        $objResult = $objDatabase->Execute($query);
	        if (!$objResult) return _databaseError($query, $objDatabase->ErrorMsg());
	        if ($objResult->RecordCount() == 0) {
	            $query = "
	                INSERT INTO `".DBPREFIX."module_shop_config` (
	                    `name`, `value`
	                ) VALUES (
	                    '$strSetting', '$flagVatIncluded'
	                );
	            ";
	            $objResult = $objDatabase->Execute($query);
	            if (!$objResult)
	                return _databaseError($query, $objDatabase->ErrorMsg());
	        }
	    }
    }

    $query = "
        DELETE FROM ".DBPREFIX."module_shop_config
        WHERE `name`='tax_enabled' OR `name`='tax_included'
    ";
    $objResult = $objDatabase->Execute($query);
    if (!$objResult) return _databaseError($query, $objDatabase->ErrorMsg());



    // Payment Service Provider table

    // Update yellowpay PSP name and description
    $query = "
        UPDATE `".DBPREFIX."module_shop_payment_processors`
        SET `name`='yellowpay',
            `description`='Yellowpay vereinfacht das Inkasso im Online-Shop. Ihre Kunden bezahlen die Einkäufe direkt mit dem Gelben Konto oder einer Kreditkarte. Ihr Plus: Mit den Zahlungsarten \"PostFinanceCard\", \"yellownet\" und \"yellowbill\" bieten Sie 2,4 Millionen Inhaberinnen und Inhabern eines Gelben Kontos eine kundenfreundliche und sichere Zahlungsmöglichkeit.'
        WHERE `".DBPREFIX."module_shop_payment_processors`.`id`=3;
    ";
    $objResult = $objDatabase->Execute($query);
    if (!$objResult) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }


    // Mail tables

    // Add e-mail template for order confirmation with user account data
    $query = "
        SELECT 1
          FROM ".DBPREFIX."module_shop_mail
         WHERE id=4
           AND protected=1
    ";
    $objResult = $objDatabase->Execute($query);
    if ($objResult) {
        if ($objResult->RecordCount() == 0) {
            $query = "
                INSERT INTO `".DBPREFIX."module_shop_mail` (
                    `id`, `tplname`, `protected`
                ) VALUES (
                    '4', 'Bestellungsbestätigung mit Zugangsdaten', '1'
                );
            ";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
            } else {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    $query = "
        SELECT 1
          FROM ".DBPREFIX."module_shop_mail_content
         WHERE id=4
           AND tpl_id=4
           AND lang_id=1
    ";
    $objResult = $objDatabase->Execute($query);
    if ($objResult) {
        if ($objResult->RecordCount() == 0) {
            $query = "
                INSERT INTO `".DBPREFIX."module_shop_mail_content` (
                    `id`, `tpl_id`, `lang_id`, `from_mail`, `xsender`, `subject`, `message`
                ) VALUES (
                    '4', '4', '1', 'nospam@contrexx.com', 'Contrexx Demo Online Shop',
                    'Contrexx Auftragsbestätigung und Zugangsdaten vom <DATE>',
                    'Sehr geehrte Kundin, sehr geehrter Kunde\r\n\r\nHerzlichen Dank für Ihre Bestellung im Contrexx Demo Online Store.\r\n\r\nIhre Auftrags-Nr. lautet: <ORDER_ID>\r\nIhre Kunden-Nr. lautet: <CUSTOMER_ID>\r\nBestellungszeit: <ORDER_TIME>\r\n\r\n<ORDER_DATA>\r\n<LOGIN_DATA>\r\n\r\nIhre Kundenadresse:\r\n<CUSTOMER_COMPANY>\r\n<CUSTOMER_PREFIX> <CUSTOMER_FIRSTNAME> <CUSTOMER_LASTNAME>\r\n<CUSTOMER_ADDRESS>\r\n<CUSTOMER_ZIP> <CUSTOMER_CITY>\r\n<CUSTOMER_COUNTRY>\r\n\r\nLieferadresse:\r\n<SHIPPING_COMPANY>\r\n<SHIPPING_PREFIX> <SHIPPING_FIRSTNAME> <SHIPPING_LASTNAME>\r\n<SHIPPING_ADDRESS>\r\n<SHIPPING_ZIP> <SHIPPING_CITY>\r\n<SHIPPING_COUNTRY>\r\n\r\nIhr Link zum Online Store: http://demo.astalavistacms.com/\r\n\r\nIhre Zugangsdaten zum Shop:\r\nBenutzername: <USERNAME>\r\nPasswort: <PASSWORD>\r\n\r\nWir freuen uns auf Ihren nächsten Besuch im Online Store und\r\nwünschen Ihnen noch einen schönen Tag.\r\n\r\nP.S. Diese Auftragsbestätigung wurde gesendet an: <CUSTOMER_EMAIL>\r\n\r\nMit freundlichen Grüssen\r\nIhr Contrexx Team'
                );
            ";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
            } else {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }





    // Update Attribute price to signed.
    if (UpdateUtil::column_exist(DBPREFIX.'module_shop_products_attributes_value', 'price_prefix')) {
        $query = "
            UPDATE `".DBPREFIX."module_shop_products_attributes_value`
               SET `price`=-`price`
            WHERE `price`>0
              AND `price_prefix`='-';
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }

    if (UpdateUtil::column_exist(DBPREFIX.'module_shop_order_items_attributes', 'price_prefix')) {
        $query = "
            UPDATE `".DBPREFIX."module_shop_order_items_attributes`
               SET `product_option_values_price`=-`product_option_values_price`
            WHERE `product_option_values_price`>0
              AND `price_prefix`='-';
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }

/**
    // Leave those for now; update is easier like that:
    // - Delete price prefix from attributes (updated above)
    // - Drop some other obsolete fields
    $arrQuery = array(
            'price_prefix' => 'module_shop_order_items_attributes',
            'property1' => 'module_shop_products',
            'property2' => 'module_shop_products',
            'thumbnail_percent' => 'module_shop_products',
            'thumbnail_quality' => 'module_shop_products',
    );
    foreach ($arrQuery as $field => $table) {
        $objResult = $objDatabase->Execute("
            ALTER TABLE `".DBPREFIX."$table` (
            DROP `$field`
        ");
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }
*/




    try {
		UpdateUtil::table(
			DBPREFIX.'core_mail_template',
			array(
				'key'                        => array('type' => 'tinytext'),
				'module_id'                  => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'key'),
				'text_name_id'               => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'after' => 'module_id'),
				'text_from_id'               => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'after' => 'text_name_id'),
				'text_sender_id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'after' => 'text_from_id'),
				'text_reply_id'              => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'after' => 'text_sender_id'),
				'text_to_id'                 => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'after' => 'text_reply_id'),
				'text_cc_id'                 => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'after' => 'text_to_id'),
				'text_bcc_id'                => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'after' => 'text_cc_id'),
				'text_subject_id'            => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'after' => 'text_bcc_id'),
				'text_message_id'            => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'after' => 'text_subject_id'),
				'text_message_html_id'       => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'after' => 'text_message_id'),
				'text_attachments_id'        => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'after' => 'text_message_html_id'),
				'text_inline_id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'after' => 'text_attachments_id'),
				'html'                       => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'text_inline_id'),
				'protected'                  => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'html')
			)
		);
		
		UpdateUtil::sql("INSERT INTO `contrexx_core_mail_template` VALUES('1', 16, 1, 2, 3, 0, 0, 0, 0, 4, 5, 21, 0, 0, 1, 1);
						 INSERT INTO `contrexx_core_mail_template` VALUES('2', 16, 6, 7, 8, 0, 0, 0, 0, 9, 10, 22, 0, 0, 1, 1);
						 INSERT INTO `contrexx_core_mail_template` VALUES('3', 16, 11, 12, 13, 0, 0, 0, 0, 14, 15, 23, 0, 0, 1, 1);
						 INSERT INTO `contrexx_core_mail_template` VALUES('4', 16, 16, 17, 18, 0, 0, 0, 0, 19, 20, 0, 0, 0, 0, 1);");
		
		UpdateUtil::table(
			DBPREFIX.'core_text',
			array(
				'id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true),
				'lang_id'        => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'id'),
				'module_id'      => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'lang_id'),
				'key'            => array('type' => 'tinytext', 'after' => 'module_id'),
				'text'           => array('type' => 'text', 'after' => 'key')
			),
			array(
				'module_id'      => array('fields' => array('module_id')),
				'key'            => array('fields' => array('key'(32)),
				'text'           => array('fields' => array('text'), 'type' => 'FULLTEXT')
			)
		);
		
		UpdateUtil::sql("INSERT INTO `contrexx_core_text` (`id`, `lang_id`, `module_id`, `key`, `text`) VALUES
						(1, 1, 16, 'core_mail_template_name', 'Bestellungsbestätigung'),
						(2, 1, 16, 'core_mail_template_from', 'info@example.org'),
						(3, 1, 16, 'core_mail_template_sender', 'Contrexx Online Shop'),
						(4, 1, 16, 'core_mail_template_subject', 'Auftragsbestätigung vom [ORDER_DATE]'),
						(5, 1, 16, 'core_mail_template_message', 'Sehr geehrte(r) [CUSTOMER_PREFIX] [CUSTOMER_LASTNAME]\r\n\r\nHerzlichen Dank für Ihre Bestellung in unserem Online-Shop.\r\n\r\nIhre Auftrags-Nr. lautet: [ORDER_ID]\r\nIhre Kunden-Nr. lautet: [CUSTOMER_ID]\r\nBestellzeit: [ORDER_TIME]\r\n\r\n------------------------------------------------------------------------\r\nBestellinformationen\r\n------------------------------------------------------------------------[ORDER_ITEM]\r\nMenge:          [PRODUCT_QUANTITY]\r\nBeschreibung:   [PRODUCT_TITLE] [PRODUCT_OPTIONS]\r\nStückpreis:     [PRODUCT_ITEM_PRICE] [CURRENCY]                    Total [PRODUCT_TOTAL_PRICE] [CURRENCY][USER_DATA]\r\nBenutzername: [USER_NAME] Passwort: [USER_PASS][USER_DATA][COUPON_DATA]\r\nGutschein Code: [COUPON_CODE][COUPON_DATA][ORDER_ITEM]\r\n------------------------------------------------------------------------\r\nZwischensumme:  [ORDER_ITEM_COUNT] Artikel   [ORDER_ITEM_SUM] [CURRENCY]\r\n------------------------------------------------------------------------\r\nVersandart:     [SHIPPING_NAME]              [SHIPPING_PRICE] [CURRENCY]\r\nBezahlung:      [PAYMENT_NAME]                [PAYMENT_PRICE] [CURRENCY]\r\n[TAX_TEXT]:     [TAX_PRICE] [CURRENCY]\r\n------------------------------------------------------------------------\r\nGesamtsumme                                       [ORDER_SUM] [CURRENCY]\r\n------------------------------------------------------------------------\r\n\r\nIhre Kundenadresse:\r\n[CUSTOMER_COMPANY]\r\n[CUSTOMER_PREFIX] [CUSTOMER_FIRSTNAME] [CUSTOMER_LASTNAME]\r\n[CUSTOMER_ADDRESS]\r\n[CUSTOMER_ZIP] [CUSTOMER_CITY]\r\n[CUSTOMER_COUNTRY]\r\n\r\n\r\nLieferadresse:\r\n[SHIPPING_COMPANY]\r\n[SHIPPING_PREFIX] [SHIPPING_FIRSTNAME] [SHIPPING_LASTNAME]\r\n[SHIPPING_ADDRESS]\r\n[SHIPPING_ZIP] [SHIPPING_CITY]\r\n[SHIPPING_COUNTRY]\r\n\r\nIhre Zugangsdaten zum Online Shop:\r\nBenutzername: [CUSTOMER_USERNAME]\r\nPasswort: [CUSTOMER_PASSWORD]\r\n\r\nWir freuen uns auf Ihren nächsten Besuch im Online Shop.\r\n\r\nFreundliche Grüsse\r\n\r\n\r\nDiese Auftragsbestätigung wurde gesendet an: [CUSTOMER_EMAIL]\r\n'),
						(6, 1, 16, 'core_mail_template_name', 'Auftrag abgeschlossen'),
						(7, 1, 16, 'core_mail_template_from', 'info@example.org'),
						(8, 1, 16, 'core_mail_template_sender', 'Contrexx Online Shop'),
						(9, 1, 16, 'core_mail_template_subject', 'Ihre Bestellung wurde am [ORDER_DATE] ausgeführt'),
						(10, 1, 16, 'core_mail_template_message', 'Sehr geehrte(r) [CUSTOMER_PREFIX] [CUSTOMER_LASTNAME]\r\n\r\nIhre Bestellung wurde ausgeführt. Sie werden in den nächsten Tagen Ihre Lieferung erhalten.\r\n\r\nFreundliche Grüsse\r\n'),
						(11, 1, 16, 'core_mail_template_name', 'Logindaten'),
						(12, 1, 16, 'core_mail_template_from', 'info@example.org'),
						(13, 1, 16, 'core_mail_template_sender', 'Contrexx Online Shop'),
						(14, 1, 16, 'core_mail_template_subject', 'Logindaten für Contrexx Online Shop'),
						(15, 1, 16, 'core_mail_template_message', 'Sehr geehrte(r) [CUSTOMER_PREFIX] [CUSTOMER_LASTNAME]\r\n\r\nHier Ihre Zugangsdaten zum Shop:\r\nBenutzername: [CUSTOMER_USERNAME]\r\nPasswort: [CUSTOMER_PASSWORD]\r\n\r\nFreundliche Grüsse\r\n'),
						(16, 1, 16, 'core_mail_template_name', 'Bestellungsbestätigung mit Zugangsdaten'),
						(17, 1, 16, 'core_mail_template_from', 'info@example.org'),
						(18, 1, 16, 'core_mail_template_sender', 'Contrexx Online Shop'),
						(19, 1, 16, 'core_mail_template_subject', 'Auftragsbestätigung und Zugangsdaten vom <DATE>'),
						(20, 1, 16, 'core_mail_template_message', 'Sehr geehrte(r) <CUSTOMER_PREFIX> <CUSTOMER_LASTNAME>\r\n\r\nHerzlichen Dank für Ihre Bestellung in unserem Online-Shop.\r\n\r\nIhre Auftrags-Nr. lautet: <ORDER_ID>\r\nIhre Kunden-Nr. lautet: <CUSTOMER_ID>\r\nBestellungszeit: <ORDER_TIME>\r\n\r\n<ORDER_DATA>\r\n<LOGIN_DATA>\r\n\r\nIhre Kundenadresse:\r\n<CUSTOMER_COMPANY>\r\n<CUSTOMER_PREFIX> <CUSTOMER_FIRSTNAME> <CUSTOMER_LASTNAME>\r\n<CUSTOMER_ADDRESS>\r\n<CUSTOMER_ZIP> <CUSTOMER_CITY>\r\n<CUSTOMER_COUNTRY>\r\n\r\nLieferadresse:\r\n<SHIPPING_COMPANY>\r\n<SHIPPING_PREFIX> <SHIPPING_FIRSTNAME> <SHIPPING_LASTNAME>\r\n<SHIPPING_ADDRESS>\r\n<SHIPPING_ZIP> <SHIPPING_CITY>\r\n<SHIPPING_COUNTRY>\r\n\r\n\r\nIhre Zugangsdaten zum Shop:\r\nBenutzername: <USERNAME>\r\nPasswort: <PASSWORD>\r\n\r\nWir freuen uns auf Ihren nächsten Besuch im Online Shop.\r\n\r\nP.S. Diese Auftragsbestätigung wurde gesendet an: <CUSTOMER_EMAIL>\r\n\r\nFreundliche Grüsse'),
						(24, 1, 16, 'core_mail_template_message_html', '&nbsp;'),
						(21, 1, 16, 'core_mail_template_message_html', 'Sehr geehrte(r) [CUSTOMER_PREFIX] [CUSTOMER_LASTNAME]<br />\n<br />\n&nbsp; Herzlichen Dank f&uuml;r Ihre Bestellung in unserem Online-Shop.<br />\n<br />\nIhre Auftrags-Nr. lautet: [ORDER_ID]<br />\nIhre Kunden-Nr. lautet: [CUSTOMER_ID]<br />\nBestellungszeit: [ORDER_TIME]<br />\n<br />\n<br />\n<table cellspacing=\"1\" cellpadding=\"1\" style=\"border: 0px none rgb(0, 0, 0);\">\n    <tbody>\n        <tr>\n            <td colspan=\"6\">Bestellinformationen</td>\n        </tr>\n        <tr>\n            <td>\n            <div style=\"text-align: right;\">Menge</div>\n            </td>\n            <td>Beschreibung</td>\n            <td>\n            <div style=\"text-align: right;\">St&uuml;ckpreis</div>\n            </td>\n            <td>\n            <div style=\"text-align: right;\">Total</div>\n            </td>\n        </tr>\n        [ORDER_ITEM]\n        <tr>\n            <td>\n            <div style=\"text-align: right;\">[PRODUCT_QUANTITY]</div>\n            </td>\n            <td>[PRODUCT_TITLE] [PRODUCT_OPTIONS]</td>\n            <td>\n            <div style=\"text-align: right;\">[PRODUCT_ITEM_PRICE] [CURRENCY]</div>\n            </td>\n            <td>\n            <div style=\"text-align: right;\">[PRODUCT_TOTAL_PRICE] [CURRENCY]</div>\n            </td>\n        </tr>\n        [USER_DATA]\n        <tr>\n            <td colspan=\"1\">&nbsp;</td>\n            <td>Benutzername: [USER_NAME]<br />\n            Passwort: [USER_PASS]</td>\n            <td colspan=\"2\">&nbsp;</td>\n        </tr>\n        [USER_DATA][COUPON_DATA]\n        <tr>\n            <td colspan=\"1\">&nbsp;</td>\n            <td>Gutschein Code: [COUPON_CODE]</td>\n            <td colspan=\"2\">&nbsp;</td>\n        </tr>\n        [COUPON_DATA][ORDER_ITEM]\n        <tr style=\"border-top: 4px none;\">\n            <td>\n            <div style=\"text-align: right;\">[ORDER_ITEM_COUNT] Artikel</div>\n            </td>\n            <td colspan=\"2\">Zwischensumme</td>\n            <td>\n            <div style=\"text-align: right;\">[ORDER_ITEM_SUM] [CURRENCY]</div>\n            </td>\n        </tr>\n        <tr style=\"border-top: 2px none;\">\n            <td colspan=\"1\">Versandart</td>\n            <td colspan=\"2\">[SHIPPING_NAME]</td>\n            <td>\n            <div style=\"text-align: right;\">[SHIPPING_PRICE] [CURRENCY]</div>\n            </td>\n        </tr>\n        <tr style=\"border-top: 2px none;\">\n            <td colspan=\"1\">Bezahlung</td>\n            <td colspan=\"2\">[PAYMENT_NAME]</td>\n            <td>\n            <div style=\"text-align: right;\">[PAYMENT_PRICE] [CURRENCY]</div>\n            </td>\n        </tr>\n        <tr style=\"border-top: 4px none;\">\n            <td colspan=\"3\">Gesamtsumme</td>\n            <td>\n            <div style=\"text-align: right;\">[ORDER_SUM] [CURRENCY]</div>\n            </td>\n        </tr>\n    </tbody>\n</table>\n<br />\n<br />\nIhre Kundenadresse:<br />\n[CUSTOMER_COMPANY]<br />\n[CUSTOMER_PREFIX] [CUSTOMER_FIRSTNAME] [CUSTOMER_LASTNAME]<br />\n[CUSTOMER_ADDRESS]<br />\n[CUSTOMER_ZIP] [CUSTOMER_CITY]<br />\n[CUSTOMER_COUNTRY]<br />\n<br />\n<br />\nLieferadresse:<br />\n[SHIPPING_COMPANY]<br />\n[SHIPPING_PREFIX] [SHIPPING_FIRSTNAME] [SHIPPING_LASTNAME]<br />\n[SHIPPING_ADDRESS]<br />\n[SHIPPING_ZIP] [SHIPPING_CITY]<br />\n[SHIPPING_COUNTRY]<br />\n<br />\n<br />\nIhre Zugangsdaten zum Online Shop:<br />\nBenutzername: [CUSTOMER_USERNAME]<br />\nPasswort: [CUSTOMER_PASSWORD]<br />\n<br />\nWir freuen uns auf Ihren n&auml;chsten Besuch im Online Shop.<br />\n<br />\nFreundliche Gr&uuml;sse<br />\n<br />\nDiese Auftragsbest&auml;tigung wurde gesendet an: [CUSTOMER_EMAIL]<br />'),
						(22, 1, 16, 'core_mail_template_message_html', 'Sehr geehrte(r) [CUSTOMER_PREFIX] [CUSTOMER_LASTNAME]<br />\n<br />\nIhre Bestellung wurde ausgef&uuml;hrt. Sie werden in den n&auml;chsten Tagen Ihre Lieferung erhalten.<br />\n<br />\nFreundliche Gr&uuml;sse<br />\n<br />'),
						(23, 1, 16, 'core_mail_template_message_html', 'Sehr geehrte(r) [CUSTOMER_PREFIX] [CUSTOMER_LASTNAME]<br />\n<br />\nHier Ihre Zugangsdaten zum Shop:<br />\nBenutzername: [CUSTOMER_USERNAME]<br />\nPasswort: [CUSTOMER_PASSWORD]<br />\n<br />\nFreundliche Gr&uuml;sse<br />\n<br />');");
	
        UpdateUtil::table(/*{{{module_shop_article_group*/
            DBPREFIX . 'module_shop_article_group',
            array(
                'id'               => array('type' =>    'INT(10)', 'unsigned' => true,          'notnull' => true, 'primary'     => true,      'auto_increment' => true),
                'name'             => array('type' =>    'VARCHAR(255)', 'notnull' => true, 'default'     => '',        'renamefrom' => 'name'),
            )
        );/*}}}*/


		UpdateUtil::table(/*{{{module_shop_customer_group*/
            DBPREFIX . 'module_shop_customer_group',
            array(
                'id'   => array('type' => 'INT(10)', 'unsigned' => true,      'notnull' => true, 'primary' => true, 'auto_increment' => true),
                'name' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
            )
        );/*}}}*/


        UpdateUtil::table(/*{{{module_shop_discountgroup_count_name*/
            DBPREFIX . 'module_shop_discountgroup_count_name',
            array(
                'id'   => array('type' => 'INT(10)', 'unsigned' => true,      'notnull' => true, 'primary' => true, 'auto_increment' => true),
                'name' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'unit' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
            )
        );/*}}}*/


        UpdateUtil::table(/*{{{module_shop_discountgroup_count_rate*/
            DBPREFIX . 'module_shop_discountgroup_count_rate',
            array(
                'group_id' => array('type' => 'INT(10) UNSIGNED', 'notnull' => true, 'primary' => true, 'default' => 0),
                'count'    => array('type' => 'INT(10) UNSIGNED', 'notnull' => true, 'primary' => true, 'default'        => '1'),
                'rate'     => array('type' => 'DECIMAL(5,2)', 'unsigned' => true,     'notnull' => true, 'default' => '0.0'),
            )
        );/*}}}*/


        UpdateUtil::table(/*{{{module_shop_rel_discount_group*/
            DBPREFIX . 'module_shop_rel_discount_group',
            array(
                'customer_group_id'    => array('type' => 'INT(10) UNSIGNED', 'notnull' => true, 'primary' => true, 'default' => '0'),
                'article_group_id'     => array('type' => 'INT(10) UNSIGNED', 'notnull' => true, 'primary' => true, 'default' => '0'),
                'rate'                 => array('type' => 'DECIMAL(9,2)',     'notnull' => true, 'default' => '0.0'),
            )
        );/*}}}*/


        UpdateUtil::table(/*{{{module_shop_lsv*/
            DBPREFIX.'module_shop_lsv',
            array(
                'id'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true, 'renamefrom' => 'order_id'),
                'order_id'   => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'holder'     => array('type' => 'TINYTEXT'),
                'bank'       => array('type' => 'TINYTEXT'),
                'blz'        => array('type' => 'TINYTEXT')
            ),
            array(
                'order_id'   => array('fields' => array('order_id'), 'type' => 'UNIQUE')
            )
        );/*}}}*/


        // Shipment cost table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_shipment_cost',
            array(
                'id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'shipper_id'     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'max_weight'     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false),
                'cost'           => array('type' => 'DECIMAL(10,2)', 'unsigned' => true, 'notnull' => false),
                'price_free'     => array('type' => 'DECIMAL(10,2)', 'unsigned' => true, 'notnull' => false)
            )
        );


        // Shipper table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_shipper',
            array(
                'id'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'       => array('type' => 'TINYTEXT'),
                'status'     => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0')
            )
        );


        // Countries table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_countries',
            array(
                'countries_id'          => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'primary' => true, 'auto_increment' => true),
                'countries_name'        => array('type' => 'VARCHAR(64)', 'notnull' => true, 'default' => ''),
                'countries_iso_code_2'  => array('type' => 'CHAR(2)', 'notnull' => true, 'default' => ''),
                'countries_iso_code_3'  => array('type' => 'CHAR(3)', 'notnull' => true, 'default' => ''),
                'activation_status'     => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
            ),
            array(
                'INDEX_COUNTRIES_NAME'  => array('fields' => array('countries_name'))
            )
        );


        // Categories table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_categories',
            array(
                'catid'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'primary' => true, 'auto_increment' => true),
                'parentid'      => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'catname'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'catsorting'    => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '100'),
                'catstatus'     => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                'picture'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'flags'         => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '')
            ),
            array(
                'flags'         => array('fields' => array('flags'), 'type' => 'FULLTEXT')
            )
        );


        // Settings table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_config',
            array(
                'id'        => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'primary' => true, 'auto_increment' => true),
                'name'      => array('type' => 'VARCHAR(64)', 'notnull' => true, 'default' => ''),
                'value'     => array('type' => 'VARCHAR(255)', 'notnull', 'default' => ''),
                'status'    => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1')
            )
        );


        $query = "
            UPDATE ".DBPREFIX."module_shop_currencies
            SET sort_order = 0 WHERE sort_order IS NULL";
        if ($objDatabase->Execute($query) == false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
        // Currencies table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_currencies',
            array(
                'id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'code'           => array('type' => 'CHAR(3)', 'notnull' => true, 'default' => ''),
                'symbol'         => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => ''),
                'name'           => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => ''),
                'rate'           => array('type' => 'DECIMAL(10,6)', 'unsigned' => true, 'notnull' => true, 'default' => '1.000000'),
                'sort_order'     => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'status'         => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                'is_default'     => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0')
            )
        );


        // Customers table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_customers',
            array(
                'customerid'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'username'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'password'           => array('type' => 'VARCHAR(32)', 'notnull' => true, 'default' => ''),
                'prefix'             => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => ''),
                'company'            => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                'firstname'          => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => ''),
                'lastname'           => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                'address'            => array('type' => 'VARCHAR(40)', 'notnull' => true, 'default' => ''),
                'city'               => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => ''),
                'zip'                => array('type' => 'VARCHAR(10)', 'notnull' => false),
                'country_id'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false),
                'phone'              => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => ''),
                'fax'                => array('type' => 'VARCHAR(25)', 'notnull' => true, 'default' => ''),
                'email'              => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'ccnumber'           => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                'ccdate'             => array('type' => 'VARCHAR(10)', 'notnull' => true, 'default' => ''),
                'ccname'             => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                'cvc_code'           => array('type' => 'VARCHAR(5)', 'notnull' => true, 'default' => ''),
                'company_note'       => array('type' => 'TEXT', 'notnull' => true),
                'is_reseller'        => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'register_date'      => array('type' => 'DATETIME', 'notnull' => true, 'default' => '0000-00-00 00:00:00'),
                'customer_status'    => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'group_id'           => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false)
            )
        );


        UpdateUtil::table(/*{{{module_shop_importimg*/
            DBPREFIX . 'module_shop_importimg',
            array(
                'img_id'          => array('type' => 'INT(10) UNSIGNED', 'notnull' => true, 'primary' => true, 'auto_increment' => true),
                'img_name'        => array('type' => 'VARCHAR(255)',     'notnull' => true, 'default' => ''),
                'img_cats'        => array('type' => 'TEXT',             'notnull' => true, 'default' => ''),
                'img_fields_file' => array('type' => 'TEXT',             'notnull' => true, 'default' => ''),
                'img_fields_db'   => array('type' => 'VARCHAR(255)',     'notnull' => true, 'default' => ''),
            )
        );/*}}}*/


        // Mail table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_mail',
            array(
                'id'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'tplname'    => array('type' => 'VARCHAR(60)', 'notnull' => true, 'default' => ''),
                'protected'  => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0')
            )
        );


        // Mail Content table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_mail_content',
            array(
                'id'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'tpl_id'     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'lang_id'    => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'from_mail'  => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'xsender'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'subject'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'message'    => array('type' => 'TEXT', 'notnull' => true)
            )
        );


        // Manufacturer table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_manufacturer',
            array(
                'id'     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'   => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'url'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '')
            )
        );


        // Order items table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_order_items',
            array(
                'order_items_id'     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'orderid'            => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'productid'          => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                'product_name'       => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                'price'              => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
                'quantity'           => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                'vat_percent'        => array('type' => 'DECIMAL(5,2)', 'unsigned' => true, 'notnull' => false),
                'weight'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false)
            )
        );


        // Order items attributes table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_order_items_attributes',
            array(
                'orders_items_attributes_id'     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'order_items_id'                 => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'order_id'                       => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'product_id'                     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'product_option_name'            => array('type' => 'VARCHAR(32)', 'notnull' => true, 'default' => ''),
                'product_option_value'           => array('type' => 'VARCHAR(32)', 'notnull' => true, 'default' => ''),
                'product_option_values_price'    => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00')
            )
        );


        // Order table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_orders',
            array(
                'orderid'                    => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'customerid'                 => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'selected_currency_id'       => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'order_sum'                  => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
                'currency_order_sum'         => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
                'order_date'                 => array('type' => 'DATETIME', 'notnull' => true, 'default' => '0000-00-00 00:00:00'),
                'order_status'               => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'ship_prefix'                => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => ''),
                'ship_company'               => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                'ship_firstname'             => array('type' => 'VARCHAR(40)', 'notnull' => true, 'default' => ''),
                'ship_lastname'              => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                'ship_address'               => array('type' => 'VARCHAR(40)', 'notnull' => true, 'default' => ''),
                'ship_city'                  => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => ''),
                'ship_zip'                   => array('type' => 'VARCHAR(10)', 'notnull' => false),
                'ship_country_id'            => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false),
                'ship_phone'                 => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => ''),
                'tax_price'                  => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
                'currency_ship_price'        => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
                'shipping_id'                => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false),
                'payment_id'                 => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false),
                'currency_payment_price'     => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
                'customer_ip'                => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => ''),
                'customer_host'              => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                'customer_lang'              => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'customer_browser'           => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                'customer_note'              => array('type' => 'TEXT'),
                'last_modified'              => array('type' => 'DATETIME', 'notnull' => true, 'default' => '0000-00-00 00:00:00'),
                'modified_by'                => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => '')
            ),
            array(
                'order_status'               => array('fields' => array('order_status'))
            )
        );


        // Payment table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_payment',
            array(
                'id'                 => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'               => array('type' => 'VARCHAR(50)', 'notnull' => false),
                'processor_id'       => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'costs'              => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
                'costs_free_sum'     => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
                'sort_order'         => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => false, 'default' => '0'),
                'status'             => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => false, 'default' => '1')
            )
        );


        // Payment processors table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_payment_processors',
            array(
                'id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'type'           => array('type' => 'ENUM(\'internal\',\'external\')', 'notnull' => true, 'default' => 'internal'),
                'name'           => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                'description'    => array('type' => 'TEXT'),
                'company_url'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'status'         => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => false, 'default' => '1'),
                'picture'        => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                'text'           => array('type' => 'TEXT')
            )
        );


        // Pricelists table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_pricelists',
            array(
                'id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'           => array('type' => 'VARCHAR(25)', 'notnull' => true, 'default' => ''),
                'lang_id'        => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'border_on'      => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                'header_on'      => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                'header_left'    => array('type' => 'TEXT', 'notnull' => false),
                'header_right'   => array('type' => 'TEXT', 'notnull' => false),
                'footer_on'      => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'footer_left'    => array('type' => 'TEXT', 'notnull' => false),
                'footer_right'   => array('type' => 'TEXT', 'notnull' => false),
                'categories'     => array('type' => 'TEXT')
            )
        );


        $query = "
            UPDATE ".DBPREFIX."module_shop_products
            SET description = '' WHERE description IS NULL";
        if ($objDatabase->Execute($query) == false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
        // Products table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_products',
            array(
                'id'                 => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'product_id'         => array('type' => 'VARCHAR(100)'),
                'picture'            => array('type' => 'TEXT'),
                'title'              => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'catid'              => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                'handler'            => array('type' => 'ENUM(\'none\',\'delivery\',\'download\')', 'notnull' => true, 'default' => 'delivery'),
                'normalprice'        => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
                'resellerprice'      => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
                'shortdesc'          => array('type' => 'TEXT'),
                'description'        => array('type' => 'TEXT'),
                'stock'              => array('type' => 'INT(10)', 'notnull' => true, 'default' => '10'),
                'stock_visibility'   => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                'discountprice'      => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
                'is_special_offer'   => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'property1'          => array('type' => 'VARCHAR(100)', 'notnull' => false, 'default' => ''),
                'property2'          => array('type' => 'VARCHAR(100)', 'notnull' => false, 'default' => ''),
                'status'             => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                'b2b'                => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                'b2c'                => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                'startdate'          => array('type' => 'DATETIME', 'notnull' => true, 'default' => '0000-00-00 00:00:00'),
                'enddate'            => array('type' => 'DATETIME', 'notnull' => true, 'default' => '0000-00-00 00:00:00'),
                'thumbnail_percent'  => array('type' => 'TINYINT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'thumbnail_quality'  => array('type' => 'TINYINT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'manufacturer'       => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'manufacturer_url'   => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'external_link'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'sort_order'         => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'vat_id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false),
                'weight'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false),
                'flags'              => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'usergroups'         => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'group_id'           => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false),
                'article_id'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false),
                'keywords'           => array('type' => 'TEXT')
            ),
            array(
                'group_id'           => array('fields' => array('group_id')),
                'article_id'         => array('fields' => array('article_id')),
                'shopindex'          => array('fields' => array('title','description'), 'type' => 'FULLTEXT'),
                'flags'              => array('fields' => array('flags'), 'type' => 'FULLTEXT'),
                'keywords'           => array('fields' => array('keywords'), 'type' => 'FULLTEXT')
            )
        );


        // Products attributes table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_products_attributes',
            array(
                'attribute_id'           => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'product_id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'attributes_name_id'     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'attributes_value_id'    => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'sort_id'                => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0')
            )
        );


        // Products attributes name table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_products_attributes_name',
            array(
                'id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'display_type'   => array('type' => 'TINYINT(3)', 'unsigned' => true, 'notnull' => true, 'default' => '0')
            )
        );


        // Products attributes value table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_products_attributes_value',
            array(
                'id'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name_id'    => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'value'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'price'      => array('type' => 'DECIMAL(9,2)', 'notnull' => false, 'default' => '0.00')
            )
        );


        // Products downloads table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_products_downloads',
            array(
                'products_downloads_id'          => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'products_downloads_name'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'products_downloads_filename'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'products_downloads_maxdays'     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => '0'),
                'products_downloads_maxcount'    => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => '0')
            )
        );


        // Rel countries table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_rel_countries',
            array(
                'id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'zones_id'       => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'countries_id'   => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0')
            )
        );


        // Rel payment table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_rel_payment',
            array(
                'id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'zones_id'       => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'payment_id'     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0')
            )
        );


        // Rel shipment table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_rel_shipment',
            array(
                'id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'zones_id'       => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'shipment_id'    => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0')
            )
        );


        // Vat table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_vat',
            array(
                'id'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'class'      => array('type' => 'TINYTEXT'),
                'percent'    => array('type' => 'DECIMAL(5,2)', 'unsigned' => true, 'notnull' => true, 'default' => '0.00')
            )
        );


        // Zones table fields
        UpdateUtil::table(
            DBPREFIX.'module_shop_zones',
            array(
                'zones_id'           => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'zones_name'         => array('type' => 'VARCHAR(64)', 'notnull' => true, 'default' => ''),
                'activation_status'  => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1')
            )
        );
    }
    catch (UpdateException $e) {
        return UpdateUtil::DefaultActionHandler($e);
    }




/*
NOT FOR VERSION 2.1
    // Fix weird field names -- some are getting a bit long and/or inconsistent.
    // The full force of this will be released in the next version only.
    $arrAffectedShopTables = array(
          'module_shop_article_group' => array(
          'field' => array('name' => 'text_name_id', ),
          'id' => array('id' => 0, ),
        ),
        'module_shop_categories' => array(
          'field' => array('catname' => 'text_name_id', ),
          'id' => array('catid' => 0, ),
          'alter' => array(
            'catid' => "`id` int(11) unsigned NOT NULL auto_increment",
            'parentid' => "`parent_id` int(11) unsigned NOT NULL default '0'",
            'catsorting' => "`sort_order` smallint(4) unsigned NOT NULL default '100'",
            'catstatus' => "`status` tinyint(1) unsigned NOT NULL default '1'",
          ),
        ),
        'module_shop_countries' => array(
          'field' => array('countries_name' => 'text_name_id', ),
          'id' => array('countries_id' => 0, ),
          'alter' => array(
            'countries_id' => "`id` int(11) unsigned NOT NULL auto_increment",
            'countries_iso_code_2' => "`iso_code_2` char(2) collate utf8_unicode_ci NOT NULL default ''",
            'countries_iso_code_3' => "`iso_code_3` char(3) collate utf8_unicode_ci NOT NULL default ''",
            'activation_status' => "`status` tinyint(1) unsigned NOT NULL default '1'",
          ),
        ),
        'module_shop_currencies' => array(
          'field' => array('name' => 'text_name_id', ),
          'id' => array('id' => 0, ),
        ),
        'module_shop_customer_group' => array(
          'field' => array('name' => 'text_name_id', ),
          'id' => array('id' => 0, ),
        ),
        'module_shop_discountgroup_count_name' => array(
          'field' => array('name' => 'text_name_id', ),
          'id' => array('id' => 0, ),
        ),
        'module_shop_mail' => array(
          'field' => array('tplname' => 'text_name_id', ),
          'id' => array('id' => 0, ),
        ),
        'module_shop_mail_content' => array(
          'field' => array(
            'xsender' => 'text_xsender_id',
            'subject' => 'text_subject_id',
            'message' => 'text_message_id',
          ),
          'id' => array('id' => 0, ),
          // remove:  'lang_id' int(11) unsigned NOT NULL default '0',
        ),
        'module_shop_manufacturer' => array(
          'field' => array(
            'name' => 'text_name_id',
            'url' => 'text_url_id',
          ),
          'id' => array('id' => 0, ),
        ),
        'module_shop_payment' => array(
          'field' => array('name' => 'text_name_id', ),
          'id' => array('id' => 0, ),
        ),
        'module_shop_payment_processors' => array(
          'field' => array(
            'name' => 'text_name_id',
            'description' => 'text_description_id',
            'company_url' => 'text_company_url_id',
            'picture' => 'text_picture_id',
            'text' => 'text_id',
          ),
          'id' => array('id' => 0, ),
        ),
        'module_shop_products' => array(
          'field' => array(
            'title' => 'text_title_id',
            'shortdesc' => 'text_shortdesc_id',
            'description' => 'text_description_id',
            'keywords' => 'text_keywords_id',
          ),
          'id' => array('id' => 0, ),
          // Fix:  FULLTEXT KEY 'shopindex' ('title','description'),
          // Fix:  FULLTEXT KEY 'keywords' ('keywords')
        ),
        'module_shop_products_attributes_name' => array(
          'field' => array('name' => 'text_name_id', ),
          'id' => array('id' => 0, ),
        ),
        'module_shop_products_attributes_value' => array(
          'field' => array('value' => 'text_value_id', ),
          'id' => array('id' => 0, ),
        ),
//  This is used nowhere in the shop!
//        'module_shop_products_downloads' => array(
//          'field' => array('products_downloads_name', 'products_downloads_filename', ),
//          'id' => array('products_downloads_id' => 0, ),
//          'alter' => array(
//            'products_downloads_id' => "`id` int(11) unsigned NOT NULL default '0'",
//            'products_downloads_filename' => "`filename` varchar(255) collate utf8_unicode_ci NOT NULL default ''",
//            'products_downloads_maxdays' => "`maxdays` int(11) unsigned default '0'",
//            'products_downloads_maxcount' => "`maxcount` int(11) unsigned default '0'",
//          ),
//        ),
          'module_shop_shipper' => array(
          'field' => array('name' => 'text_name_id', ),
          'id' => array('id' => 0, ),
        ),
        'module_shop_vat' => array(
          'field' => array('class' => 'text_class_id', ),
          'id' => array('id' => 0, ),
        ),
        'module_shop_zones' => array(
          'field' => array('zones_name' => 'text_name_id', ),
          'id' => array('zones_id' => 0, ),
          'alter' => array(
            'zones_id' => "`id` int(11) unsigned NOT NULL auto_increment",
            'activation_status' => "`status` tinyint(1) unsigned NOT NULL default '1'",
          ),
        ),
        // Alter foreign keys referring to table names *only*
        'module_shop_rel_countries' => array(
          'alter' => array(
            'zones_id' => "`zone_id` int(11) unsigned NOT NULL default '0'",
            'countries_id' => "`country_id` int(11) unsigned NOT NULL default '0'",
          ),
        ),
        'module_shop_rel_payment' => array(
          'alter' => array(
            'zones_id' => "`zone_id` int(11) unsigned NOT NULL default '0'",
          ),
        ),
        'module_shop_rel_shipment' => array(
          'alter' => array(
            'zones_id' => "`zone_id` int(11) unsigned NOT NULL default '0'",
          ),
        ),
    );

    // Find all Shop modules' IDs
    // Note that some custom installations do have more than one shop instance!
    $query = "
        SELECT `id`, `name` FROM `".DBPREFIX."modules` WHERE `name` LIKE 'shop%'
    ";
    $objResult = $objDatabase->Execute($query);
    if (!$objResult)
        return _databaseError($query, $objDatabase->ErrorMsg());
    while (!$objResult->EOF) {
        $arrShop[$objResult->fields['id']] = $objResult->fields['name'];
        $objResult->MoveNext();
    }

    // Determine the default language ID.
    // Used below as the language ID for the text records created
    $lang_id = 1;
    $arrLanguages = FWLanguage::getLanguageArray();
    if (empty($arrLanguages) || !is_array($arrLanguages)) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_shop_products'));
        return false;
    }
    foreach ($arrLanguages as $arrLanguage) {
        if ($arrLanguage['is_default']) {
            $lang_id = $arrLanguage['id'];
            break;
        }
    }

    // Determine the next Text ID
    $text_id = Text::nextId();
//echo("Text ID: $text_id<br />");

    // For all Shops
    foreach ($arrShop as $module_shop_id => $module_shop_name) {
        // Table index, starts at 1 for each module
        $table_index = 0;
        // For all tables affected
        foreach ($arrAffectedShopTables as $table_name => $arrTableInfo) {
            ++$table_index;

//echo("Table info: ".var_export($arrTableInfo, true)."<br />");
            // Build a reference base name from the table name
//            $reference_base = preg_replace('^module_shop_', '', $table_name);
            // The actual table name must also contain the shop module index
            $table_name = preg_replace('/shop/', $module_shop_name, $table_name, 1);

            // The fields of the current table
            $arrTableColumns = $objDatabase->MetaColumns(DBPREFIX.$table_name);
//echo("Table columns: ".var_export($arrTableColumns, true)."<br />");
            if ($arrTableColumns === false) {
                setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_shop_products'));
                return false;
            }

/*
            // The current tables' primary keys
            if (isset($arrTableInfo['id'])) {
                $arrPrimaryKey = $arrTableInfo['id'];
//echo("Primary keys: ".var_export($arrPrimaryKey, true)."<br />");
                // Field index, starts at 1 for each table
                $field_index = 0;
                // For all fields affected
                foreach ($arrTableInfo['field'] as $field_name => $new_field_name) {
                    ++$field_index;

//echo("Field name: $field_name, looking for it in ".var_export($arrTableColumns, true)."<br />");
                    // Skip fields that do not exist (they have probably been
                    // converted already)
                    if (!isset($arrTableColumns[strtoupper($field_name)])) {
//echo("Field name $field_name NOT found, skipping<br />");
                        continue;
                    }
//echo("Field name $field_name found, processing<br />");

                    // Add the field name to the reference base
                    // and make the reference name uppercase
//                    $reference_name = strtoupper("$reference_base_$field_name");
                    // The key ID for the current table and field
                    $key_id = ((1<<16)*$table_index) + ((1<<0)*$field_index);
                    // Pick the field values
                    $query = "
                        SELECT `".join('`, `', array_keys($arrPrimaryKey))."`, `$field_name`
                          FROM ".DBPREFIX."$table_name
                    ";
                    $objResult = $objDatabase->Execute($query);
                    if (!$objResult)
                        return _databaseError($query, $objDatabase->ErrorMsg());
                    while (!$objResult->EOF) {
                        // Primary key values
                        foreach (array_keys($arrPrimaryKey) as $primary_key) {
                            $arrPrimaryKey[$primary_key] =
                                $objResult->fields[$primary_key];
                        }
                        // The actual text
                        $text_value = $objResult->fields[$field_name];
                        // Insert the field value into the text table.
                        // The Text class insert() method is not used here
                        // for speed reasons.
                        $query = "
                            INSERT INTO ".DBPREFIX."core_text (
                            `id`, `lang_id`,
                            `module_id`, `key_id`, `text`
                        ) VALUES (
                            $text_id, $lang_id,
                            $module_shop_id, $key_id,
                            '".addslashes($text_value)."'
                        )";
// Removed:
// `reference`,
// ".addslashes($reference_name).",
                        $objResult2 = $objDatabase->Execute($query);
                        if (!$objResult2)
                            return _databaseError($query, $objDatabase->ErrorMsg());
                        // Update the original field (remember the Text ID)
                        $query = '';
                        foreach ($arrPrimaryKey as $primary_key_name => $primary_key_value) {
                            $query .=
                                ($query ? ' AND ' : '').
                                "`$primary_key_name`='".
                                addslashes($primary_key_value)."'";
                        }
                        $query = "
                            UPDATE ".DBPREFIX."$table_name
                               SET `$field_name`='$text_id'
                             WHERE ".$query;
                        $objResult2 = $objDatabase->Execute($query);
                        if (!$objResult2)
                            return _databaseError($query, $objDatabase->ErrorMsg());
                        ++$text_id;
                        $objResult->MoveNext();
                    }
                    // Change the name of the original text field to the new
                    // name given as the array value.
                    $query = "
                        ALTER TABLE ".DBPREFIX."$table_name
                       CHANGE `$field_name` `$new_field_name` INT(11) unsigned NULL DEFAULT NULL
                    ";
                    $objResult = $objDatabase->Execute($query);
                    if (!$objResult)
                        return _databaseError($query, $objDatabase->ErrorMsg());
                }
            }

            // Alter some weird column names
            if (isset($arrTableInfo['alter'])) {
                $arrAlterField = $arrTableInfo['alter'];
                foreach ($arrAlterField as $field_name => $new_column_definition) {
                    if (!isset($arrTableColumns[strtoupper($field_name)])) {
//echo("Alter: field name $field_name NOT found, skipping<br />");
                        continue;
                    }
                    // Alter the field name to the new name given
                    // as the array value.
                    $query = "
                        ALTER TABLE ".DBPREFIX."$table_name
                       CHANGE `$field_name` $new_column_definition
                    ";
                    $objResult = $objDatabase->Execute($query);
                    if (!$objResult)
                        return _databaseError($query, $objDatabase->ErrorMsg());

                }
            }
        }
    }
*/


    return true;
}

?>
