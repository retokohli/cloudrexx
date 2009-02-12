<?php

function _shopUpdate()
{
    global $objDatabase, $_ARRAYLANG;

    $query = "
        ALTER TABLE `".DBPREFIX."module_shop_products`
        CHANGE `title` `title` varchar(255) NOT NULL default ''
    ";
    if ($objDatabase->Execute($query) === false) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

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

    // Products table fields
    $arrProductColumns = $objDatabase->MetaColumns(DBPREFIX.'module_shop_products');
    if ($arrProductColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_shop_products'));
        return false;
    }

    // Add flags field to Product table
    if (!isset($arrProductColumns['FLAGS'])) {
        $query = "
            ALTER TABLE ".DBPREFIX."module_shop_products
            ADD flags varchar(255) NOT NULL default ''
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }
    $arrIndexes = $objDatabase->MetaIndexes(DBPREFIX.'module_shop_products');
    if ($arrIndexes === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_shop_products'));
        return false;
    }
    if (!isset($arrIndexes['flags']['columns'])) {
        $query = "
            ALTER TABLE ".DBPREFIX."module_shop_products
            ADD FULLTEXT (flags)
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    // Add usergroups field to Product table
    if (!isset($arrProductColumns['USERGROUPS'])) {
        $query = "
            ALTER TABLE ".DBPREFIX."module_shop_products
            ADD usergroups varchar(255) NOT NULL default ''
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    // Categories table fields
    $arrCategoriesColumns = $objDatabase->MetaColumns(DBPREFIX.'module_shop_categories');
    if ($arrCategoriesColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_shop_categories'));
        return false;
    }

    // Add picture field to Shop Category table
    if (!isset($arrCategoriesColumns['PICTURE'])) {
        $query = "
            ALTER TABLE ".DBPREFIX."module_shop_categories
            ADD picture varchar(255) NOT NULL default ''
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    // Add flags field to Shop Category table
    if (!isset($arrCategoriesColumns['FLAGS'])) {
        $query = "
            ALTER TABLE ".DBPREFIX."module_shop_categories
            ADD flags varchar (255) NOT NULL default ''
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }
    $arrIndexes = $objDatabase->MetaIndexes(DBPREFIX.'module_shop_categories');
    if ($arrIndexes === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_shop_categories'));
        return false;
    }
    if (!isset($arrIndexes['flags']['columns'])) {
        $query = "
            ALTER TABLE ".DBPREFIX."module_shop_categories
            ADD FULLTEXT (flags)
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }


    // Add Yellowpay payment methods default setting
    // Shop thumbnail default settings: shop_thumbnail_max_width
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


    // Update yellowpay payment processor name and desription
    $query = "
        UPDATE `".DBPREFIX."module_shop_payment_processors`
        SET `name`='yellowpay',
            `description`='Yellowpay vereinfacht das Inkasso im Online-Shop. Ihre Kunden bezahlen die Eink�ufe direkt mit dem Gelben Konto oder einer Kreditkarte. Ihr Plus: Mit den Zahlungsarten \"PostFinanceCard\", \"yellownet\" und \"yellowbill\" bieten Sie 2,4 Millionen Inhaberinnen und Inhabern eines Gelben Kontos eine kundenfreundliche und sichere Zahlungsm�glichkeit.'
        WHERE `".DBPREFIX."module_shop_payment_processors`.`id`=3;
    ";
    $objResult = $objDatabase->Execute($query);
    if (!$objResult) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }


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


    // Fix Product Attribute table to handle new option types
    $query = "
        ALTER TABLE `".DBPREFIX."module_shop_products_attributes_name`
        CHANGE `display_type` `display_type` TINYINT UNSIGNED NOT NULL DEFAULT '0' ;
    ";
    $objResult = $objDatabase->Execute($query);
    if (!$objResult) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    // Change price fields to larger value range
    $arrQuery = array("
            ALTER TABLE `".DBPREFIX."module_shop_orders`
            CHANGE `order_sum` `order_sum` DECIMAL(9, 2) NOT NULL DEFAULT '0.00',
            CHANGE `currency_order_sum` `currency_order_sum` DECIMAL(9, 2) NOT NULL DEFAULT '0.00',
            CHANGE `tax_price` `tax_price` DECIMAL(9, 2) NOT NULL DEFAULT '0.00',
            CHANGE `currency_ship_price` `currency_ship_price` DECIMAL(9, 2) NOT NULL DEFAULT '0.00',
            CHANGE `currency_payment_price` `currency_payment_price` DECIMAL(9, 2) NOT NULL DEFAULT '0.00'
        ", "
            ALTER TABLE `".DBPREFIX."module_shop_order_items`
            CHANGE `price` `price` DECIMAL(9, 2) NOT NULL DEFAULT '0.00'
        ", "
            ALTER TABLE `".DBPREFIX."module_shop_order_items_attributes`
            CHANGE `product_option_values_price` `product_option_values_price` DECIMAL(9, 2) NOT NULL DEFAULT '0.00'
        ", "
            ALTER TABLE `".DBPREFIX."module_shop_products`
            CHANGE `normalprice` `normalprice` DECIMAL(9, 2) NOT NULL DEFAULT '0.00',
            CHANGE `resellerprice` `resellerprice` DECIMAL(9, 2) NOT NULL DEFAULT '0.00',
            CHANGE `discountprice` `discountprice` DECIMAL(9, 2) NOT NULL DEFAULT '0.00'
        ", "
            ALTER TABLE `".DBPREFIX."module_shop_products_attributes_value`
            CHANGE `price` `price` DECIMAL(9, 2) NULL DEFAULT '0.00'
        ",
    );
    foreach ($arrQuery as $query) {
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    return true;
}

?>
