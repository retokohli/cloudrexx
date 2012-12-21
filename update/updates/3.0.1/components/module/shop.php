<?php

function _shopUpdate()
{
    global $objDatabase, $_ARRAYLANG;

    try {
        $table_name = DBPREFIX.'module_shop_config';
        // Mind that this table does no longer exist from version 3
        if (\Cx\Lib\UpdateUtil::table_exist($table_name)) {
            // Shop settings
            // Shop thumbnail default settings: shop_thumbnail_max_width
            $query = "
                SELECT 1 FROM `$table_name`
                WHERE name='shop_thumbnail_max_width'";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
                if ($objResult->RecordCount() == 0) {
                    $query = "
                        INSERT INTO `$table_name` (
                            name, value
                        ) VALUES (
                            'shop_thumbnail_max_width', '120'
                        )";
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
                SELECT 1 FROM `$table_name`
                WHERE name='shop_thumbnail_max_height'";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
                if ($objResult->RecordCount() == 0) {
                    $query = "
                        INSERT INTO `$table_name` (
                            name, value
                        ) VALUES (
                            'shop_thumbnail_max_height', '90'
                        )";
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
                SELECT 1 FROM `$table_name`
                WHERE name='shop_thumbnail_quality'";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
                if ($objResult->RecordCount() == 0) {
                    $query = "
                        INSERT INTO `$table_name` (
                            name, value
                        ) VALUES (
                            'shop_thumbnail_quality', '80'
                        )";
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
                SELECT 1 FROM `$table_name`
                WHERE name='yellowpay_accepted_payment_methods'";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
                if ($objResult->RecordCount() == 0) {
                    $query = "
                        INSERT INTO `$table_name` (
                            `id`, `name`, `value`, `status`
                        ) VALUES (
                            NULL, 'yellowpay_accepted_payment_methods', '', '1'
                        )";
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
                SELECT 1 FROM `$table_name`
                WHERE `name`='yellowpay_delivery_payment_type'";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
                if ($objResult->RecordCount() == 1) {
                    $query = "
                        UPDATE `$table_name`
                           SET `name`='yellowpay_authorization_type'
                         WHERE `name`='yellowpay_delivery_payment_type'";
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
                SELECT 1 FROM `$table_name`
                WHERE `name`='yellowpay_use_testserver'";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
                if ($objResult->RecordCount() == 0) {
                    $query = "
                        INSERT INTO `$table_name` (
                            `id`, `name`, `value`, `status`
                        ) VALUES (
                            NULL, 'yellowpay_use_testserver', '1', '1'
                        )";
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
                SELECT 1 FROM `$table_name`
                WHERE `name`='shop_weight_enable'";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
                if ($objResult->RecordCount() == 0) {
                    $query = "
                        INSERT INTO `$table_name` (
                            `id`, `name`, `value`, `status`
                        ) VALUES (
                            NULL, 'shop_weight_enable', '1', '1'
                        )";
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
                SELECT 1 FROM `$table_name`
                WHERE `name`='shop_show_products_default'";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return _databaseError($query, $objDatabase->ErrorMsg());
            if ($objResult->RecordCount() == 0) {
                $query = "
                    INSERT INTO `$table_name` (
                        `name`, `value`
                    ) VALUES (
                        'shop_show_products_default', '1'
                    )";
                $objResult = $objDatabase->Execute($query);
                if (!$objResult)
                    return _databaseError($query, $objDatabase->ErrorMsg());
            }


            // Update VAT settings
            $query = "
                SELECT `value` FROM `$table_name`
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
                        SELECT 1 FROM `$table_name`
                        WHERE `name`='$strSetting'";
                    $objResult = $objDatabase->Execute($query);
                    if (!$objResult) return _databaseError($query, $objDatabase->ErrorMsg());
                    if ($objResult->RecordCount() == 0) {
                        $query = "
                            INSERT INTO `$table_name` (
                                `name`, `value`
                            ) VALUES (
                                '$strSetting', '$flagVatEnabled'
                            )";
                        $objResult = $objDatabase->Execute($query);
                        if (!$objResult)
                            return _databaseError($query, $objDatabase->ErrorMsg());
                    }
                }
            }

            $query = "
                SELECT `value` FROM `$table_name`
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
                        SELECT 1 FROM `$table_name`
                        WHERE `name`='$strSetting'";
                    $objResult = $objDatabase->Execute($query);
                    if (!$objResult) return _databaseError($query, $objDatabase->ErrorMsg());
                    if ($objResult->RecordCount() == 0) {
                        $query = "
                            INSERT INTO `$table_name` (
                                `name`, `value`
                            ) VALUES (
                                '$strSetting', '$flagVatIncluded'
                            )";
                        $objResult = $objDatabase->Execute($query);
                        if (!$objResult)
                            return _databaseError($query, $objDatabase->ErrorMsg());
                    }
                }
            }

            $query = "
                DELETE FROM `$table_name`
                WHERE `name`='tax_enabled' OR `name`='tax_included'";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return _databaseError($query, $objDatabase->ErrorMsg());
        }

        // Payment Service Provider table

        // Update yellowpay PSP name and description
        $query = "
            UPDATE `".DBPREFIX."module_shop_payment_processors`
            SET `name`='yellowpay',
                `description`='PostFinance Payment Service Providing. Inkasso im Onlineshop.'
            WHERE `".DBPREFIX."module_shop_payment_processors`.`id`=3";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }

        // Update Attribute price to signed.
        $table_name = DBPREFIX.'module_shop_products_attributes_value';
        if (   \Cx\Lib\UpdateUtil::table_exist($table_name)
            && \Cx\Lib\UpdateUtil::column_exist($table_name, 'price_prefix')) {
            $query = "
                UPDATE `$table_name`
                   SET `price`=-`price`
                   SET `price_prefix`='+'
                WHERE `price`>0
                  AND `price_prefix`='-'";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult)
                return _databaseError($query, $objDatabase->ErrorMsg());
            $query = "
                UPDATE `".DBPREFIX."module_shop_order_items_attributes`
                   SET `product_option_values_price`=-`product_option_values_price`
                WHERE `product_option_values_price`>0
                  AND `price_prefix`='-'";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult)
                return _databaseError($query, $objDatabase->ErrorMsg());
        }


        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_shop_article_group',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'primary' => true, 'auto_increment' => true),
                'name' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'renamefrom' => 'name'),
            )
        );

		\Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_shop_customer_group',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'primary' => true, 'auto_increment' => true),
                'name' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
            )
        );


        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_shop_discountgroup_count_name',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'primary' => true, 'auto_increment' => true),
                'name' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'unit' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
            )
        );


        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_shop_discountgroup_count_rate',
            array(
                'group_id' => array('type' => 'INT(10) UNSIGNED', 'notnull' => true, 'primary' => true, 'default' => 0),
                'count' => array('type' => 'INT(10) UNSIGNED', 'notnull' => true, 'primary' => true, 'default' => '1'),
                'rate' => array('type' => 'DECIMAL(5,2)', 'unsigned' => true, 'notnull' => true, 'default' => '0.0'),
            )
        );


        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_shop_rel_discount_group',
            array(
                'customer_group_id' => array('type' => 'INT(10) UNSIGNED', 'notnull' => true, 'primary' => true, 'default' => '0'),
                'article_group_id' => array('type' => 'INT(10) UNSIGNED', 'notnull' => true, 'primary' => true, 'default' => '0'),
                'rate' => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.0'),
            )
        );

// ok   die("HERE");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_lsv',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true, 'renamefrom' => 'order_id'),
                'order_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'holder' => array('type' => 'TINYTEXT'),
                'bank' => array('type' => 'TINYTEXT'),
                'blz' => array('type' => 'TINYTEXT'),
            ),
            array(
                'order_id' => array('fields' => array('order_id'), 'type' => 'UNIQUE'),
            )
        );


        // Shipment cost table fields
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_shipment_cost',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'shipper_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'max_weight' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false),
                'cost' => array('type' => 'DECIMAL(10,2)', 'unsigned' => true, 'notnull' => false),
                'price_free' => array('type' => 'DECIMAL(10,2)', 'unsigned' => true, 'notnull' => false),
            )
        );


        // Shipper table fields
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_shipper',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name' => array('type' => 'TINYTEXT'),
                'status' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
            )
        );


        // Countries table fields
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_countries',
            array(
                'countries_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'primary' => true, 'auto_increment' => true),
                'countries_name' => array('type' => 'VARCHAR(64)', 'notnull' => true, 'default' => ''),
                'countries_iso_code_2' => array('type' => 'CHAR(2)', 'notnull' => true, 'default' => ''),
                'countries_iso_code_3' => array('type' => 'CHAR(3)', 'notnull' => true, 'default' => ''),
                'activation_status' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
            ),
            array(
                'countries_name' => array('fields' => array('countries_name')),
            )
        );

        // Categories table fields
        $table_name = DBPREFIX.'module_shop_categories';
        if (   \Cx\Lib\UpdateUtil::table_exist($table_name)) {
            if (\Cx\Lib\UpdateUtil::column_exist($table_name, 'catid')) {
                \Cx\Lib\UpdateUtil::table(
                    $table_name,
                    array(
                        'catid' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'primary' => true, 'auto_increment' => true),
                        'parentid' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                        'catname' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                        'catsorting' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '100'),
                        'catstatus' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                        'picture' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                        'flags' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                    ),
                    array(
                        'flags' => array('fields' => array('flags'), 'type' => 'FULLTEXT'),
                    )
                );
            }
        }


        // Settings table fields
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_config',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'primary' => true, 'auto_increment' => true),
                'name' => array('type' => 'VARCHAR(64)', 'notnull' => true, 'default' => ''),
                'value' => array('type' => 'VARCHAR(255)', 'notnull', 'default' => ''),
                'status' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
            )
        );


        $table_name = DBPREFIX.'module_shop_currencies';
        if (   \Cx\Lib\UpdateUtil::table_exist($table_name)) {
            if (\Cx\Lib\UpdateUtil::column_exist($table_name, 'sort_order')) {
                $query = "
                    UPDATE `$table_name`
                    SET sort_order = 0 WHERE sort_order IS NULL";
                if ($objDatabase->Execute($query) == false) {
                    return _databaseError($query, $objDatabase->ErrorMsg());
                }
            }
        }
        // Currencies table fields
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_currencies',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'code' => array('type' => 'CHAR(3)', 'notnull' => true, 'default' => ''),
                'symbol' => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => ''),
                'name' => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => ''),
                'rate' => array('type' => 'DECIMAL(10,6)', 'unsigned' => true, 'notnull' => true, 'default' => '1.000000'),
                'sort_order' => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'status' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                'is_default' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
            )
        );


        // Customers table fields
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_customers',
            array(
                'customerid' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'username' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'password' => array('type' => 'VARCHAR(32)', 'notnull' => true, 'default' => ''),
                'prefix' => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => ''),
                'company' => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                'firstname' => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => ''),
                'lastname' => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                'address' => array('type' => 'VARCHAR(40)', 'notnull' => true, 'default' => ''),
                'city' => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => ''),
                'zip' => array('type' => 'VARCHAR(10)', 'notnull' => false),
                'country_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false),
                'phone' => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => ''),
                'fax' => array('type' => 'VARCHAR(25)', 'notnull' => true, 'default' => ''),
                'email' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'ccnumber' => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                'ccdate' => array('type' => 'VARCHAR(10)', 'notnull' => true, 'default' => ''),
                'ccname' => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                'cvc_code' => array('type' => 'VARCHAR(5)', 'notnull' => true, 'default' => ''),
                'company_note' => array('type' => 'TEXT', 'notnull' => true),
                'is_reseller' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'register_date' => array('type' => 'DATETIME', 'notnull' => true, 'default' => '0000-00-00 00:00:00'),
                'customer_status' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'group_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false),
            )
        );


        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_shop_importimg',
            array(
                'img_id' => array('type' => 'INT(10) UNSIGNED', 'notnull' => true, 'primary' => true, 'auto_increment' => true),
                'img_name' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'img_cats' => array('type' => 'TEXT',  'notnull' => true, 'default' => ''),
                'img_fields_file' => array('type' => 'TEXT',  'notnull' => true, 'default' => ''),
                'img_fields_db' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
            )
        );


        // Mail table fields
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_mail',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'tplname' => array('type' => 'VARCHAR(60)', 'notnull' => true, 'default' => ''),
                'protected' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
            )
        );


        // Mail Content table fields
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_mail_content',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'tpl_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'lang_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'from_mail' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'xsender' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'subject' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'message' => array('type' => 'TEXT', 'notnull' => true),
            )
        );


        // Manufacturer table fields
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_manufacturer',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'url' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
            )
        );


        // Order items table fields
        $table_name = DBPREFIX.'module_shop_order_items';
        if (   \Cx\Lib\UpdateUtil::table_exist($table_name)
            && \Cx\Lib\UpdateUtil::column_exist($table_name, 'order_items_id')) {
            \Cx\Lib\UpdateUtil::table(
                $table_name,
                array(
                    'order_items_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'orderid' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                    'productid' => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                    'product_name' => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                    'price' => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
                    'quantity' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                    'vat_percent' => array('type' => 'DECIMAL(5,2)', 'unsigned' => true, 'notnull' => false),
                    'weight' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false),
                )
            );
        }


        // Order items attributes table fields
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_order_items_attributes',
            array(
                'orders_items_attributes_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'order_items_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'order_id'      => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'product_id'    => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'product_option_name' => array('type' => 'VARCHAR(32)', 'notnull' => true, 'default' => ''),
                'product_option_value' => array('type' => 'VARCHAR(32)', 'notnull' => true, 'default' => ''),
                'product_option_values_price' => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
            )
        );


        // Order table fields
        $table_name = DBPREFIX.'module_shop_orders';
        if (   \Cx\Lib\UpdateUtil::table_exist($table_name)
            && \Cx\Lib\UpdateUtil::column_exist($table_name, 'orderid')) {
            \Cx\Lib\UpdateUtil::table(
                $table_name,
                array(
                    'orderid'   => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'customerid' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                    'selected_currency_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                    'order_sum' => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
                    'currency_order_sum' => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
                    'order_date' => array('type' => 'DATETIME', 'notnull' => true, 'default' => '0000-00-00 00:00:00'),
                    'order_status' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                    'ship_prefix' => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => ''),
                    'ship_company' => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                    'ship_firstname' => array('type' => 'VARCHAR(40)', 'notnull' => true, 'default' => ''),
                    'ship_lastname' => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                    'ship_address' => array('type' => 'VARCHAR(40)', 'notnull' => true, 'default' => ''),
                    'ship_city' => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => ''),
                    'ship_zip'  => array('type' => 'VARCHAR(10)', 'notnull' => false),
                    'ship_country_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false),
                    'ship_phone' => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => ''),
                    'tax_price' => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
                    'currency_ship_price' => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
                    'shipping_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false),
                    'payment_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false),
                    'currency_payment_price' => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
                    'customer_ip' => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => ''),
                    'customer_host' => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                    'customer_lang' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                    'customer_browser' => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                    'customer_note' => array('type' => 'TEXT'),
                    'last_modified' => array('type' => 'DATETIME', 'notnull' => true, 'default' => '0000-00-00 00:00:00'),
                    'modified_by' => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => ''),
                ),
                array(
                    'order_status' => array('fields' => array('order_status')),
                )
            );
        }


        // Payment table fields
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_payment',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name' => array('type' => 'VARCHAR(50)', 'notnull' => false),
                'processor_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'costs' => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
                'costs_free_sum' => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
                'sort_order' => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => false, 'default' => '0'),
                'status' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => false, 'default' => '1'),
            )
        );


        // Payment processors table fields
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_payment_processors',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'type' => array('type' => 'ENUM(\'internal\',\'external\')', 'notnull' => true, 'default' => 'internal'),
                'name' => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                'description' => array('type' => 'TEXT'),
                'company_url' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'status' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => false, 'default' => '1'),
                'picture' => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                'text' => array('type' => 'TEXT'),
            )
        );


        // Pricelists table fields
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_pricelists',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name' => array('type' => 'VARCHAR(25)', 'notnull' => true, 'default' => ''),
                'lang_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'border_on' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                'header_on' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                'header_left' => array('type' => 'TEXT', 'notnull' => false),
                'header_right' => array('type' => 'TEXT', 'notnull' => false),
                'footer_on' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'footer_left' => array('type' => 'TEXT', 'notnull' => false),
                'footer_right' => array('type' => 'TEXT', 'notnull' => false),
                'categories' => array('type' => 'TEXT'),
            )
        );


        $table_name = DBPREFIX.'module_shop_products';
        if (   \Cx\Lib\UpdateUtil::table_exist($table_name)
            && \Cx\Lib\UpdateUtil::column_exist($table_name, 'description')) {
            $query = "
                UPDATE `$table_name`
                SET `description`= '' WHERE `description` IS NULL";
            if ($objDatabase->Execute($query) == false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
        // Products table fields
        \Cx\Lib\UpdateUtil::table(
            $table_name,
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'product_id' => array('type' => 'VARCHAR(100)'),
                'picture' => array('type' => 'TEXT'),
                'title' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'catid' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                'handler' => array('type' => 'ENUM(\'none\',\'delivery\',\'download\')', 'notnull' => true, 'default' => 'delivery'),
                'normalprice' => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
                'resellerprice' => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
                'shortdesc' => array('type' => 'TEXT'),
                'description' => array('type' => 'TEXT'),
                'stock' => array('type' => 'INT(10)', 'notnull' => true, 'default' => '10'),
                'stock_visibility' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                'discountprice' => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
                'is_special_offer' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'property1' => array('type' => 'VARCHAR(100)', 'notnull' => false, 'default' => ''),
                'property2' => array('type' => 'VARCHAR(100)', 'notnull' => false, 'default' => ''),
                'status' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                'b2b' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                'b2c' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                'startdate' => array('type' => 'DATETIME', 'notnull' => true, 'default' => '0000-00-00 00:00:00'),
                'enddate' => array('type' => 'DATETIME', 'notnull' => true, 'default' => '0000-00-00 00:00:00'),
                'thumbnail_percent' => array('type' => 'TINYINT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'thumbnail_quality' => array('type' => 'TINYINT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'manufacturer' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'manufacturer_url' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'external_link' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'sort_order' => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'vat_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false),
                'weight' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false),
                'flags' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'usergroups' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'group_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false),
                'article_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false),
                'keywords' => array('type' => 'TEXT'),
            ),
            array(
                'group_id' => array('fields' => array('group_id')),
                'article_id' => array('fields' => array('article_id')),
                'shopindex' => array('fields' => array('title','description'), 'type' => 'FULLTEXT'),
                'flags' => array('fields' => array('flags'), 'type' => 'FULLTEXT'),
                'keywords' => array('fields' => array('keywords'), 'type' => 'FULLTEXT'),
            )
        );


        // Products attributes table fields
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_products_attributes',
            array(
                'attribute_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'product_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'attributes_name_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'attributes_value_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'sort_id' => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
            )
        );


        // Products attributes name table fields
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_products_attributes_name',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'display_type' => array('type' => 'TINYINT(3)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
            )
        );


        // Products attributes value table fields
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_products_attributes_value',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'value' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'price' => array('type' => 'DECIMAL(9,2)', 'notnull' => false, 'default' => '0.00'),
            )
        );


        // Products downloads table fields
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_products_downloads',
            array(
                'products_downloads_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'products_downloads_name' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'products_downloads_filename' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'products_downloads_maxdays' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => '0'),
                'products_downloads_maxcount' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => '0'),
            )
        );


        // Rel countries table fields
        $table_name = DBPREFIX.'module_shop_rel_countries';
        if (   \Cx\Lib\UpdateUtil::table_exist($table_name)
            && \Cx\Lib\UpdateUtil::column_exist($table_name, 'id')) {
            \Cx\Lib\UpdateUtil::table(
                $table_name,
                array(
                    'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'zones_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                    'countries_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                )
            );
        }


        // Rel payment table fields
        $table_name = DBPREFIX.'module_shop_rel_payment';
        if (   \Cx\Lib\UpdateUtil::table_exist($table_name)
            && \Cx\Lib\UpdateUtil::column_exist($table_name, 'id')) {
            \Cx\Lib\UpdateUtil::table(
                $table_name,
                array(
                    'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'zones_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                    'payment_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                )
            );
        }


        // Rel shipment table fields
        // Note: This is renamed to module_shop_rel_shipper from version 3.0
        $table_name = DBPREFIX.'module_shop_rel_shipment';
        if (\Cx\Lib\UpdateUtil::table_exist($table_name)) {
            \Cx\Lib\UpdateUtil::table(
                $table_name,
                array(
                    'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'zones_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                    'shipment_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                )
            );
        }


        // Vat table fields
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_vat',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'class' => array('type' => 'TINYTEXT'),
                'percent' => array('type' => 'DECIMAL(5,2)', 'unsigned' => true, 'notnull' => true, 'default' => '0.00'),
            )
        );


        // Zones table fields
        $table_name = DBPREFIX.'module_shop_zones';
        if (   \Cx\Lib\UpdateUtil::table_exist($table_name)
            && \Cx\Lib\UpdateUtil::column_exist($table_name, 'zones_id')) {
            \Cx\Lib\UpdateUtil::table(
                $table_name,
                array(
                    'zones_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'zones_name' => array('type' => 'VARCHAR(64)', 'notnull' => true, 'default' => ''),
                    'activation_status' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                )
            );
        }


        // Contrexx 3.0.0 updates from here.
        // NOTE: All of these methods return false on success

        Attribute::errorHandler();


        Coupon::errorHandler();
        // Prerequisites:
        //        ShopSettings::errorHandler();

        //ShopSettings::errorHandler(); // Called by Coupon::errorHandler();Customer::errorHandler();Order::errorHandler();ShopCategory::errorHandler();
        // Prerequisites:
        //        SettingDb::errorHandler();


        Currency::errorHandler();
        // Prerequisites:
        //        Text::errorHandler();

        //Text::errorHandler(); // Called by Currency::errorHandler();Product::errorHandler();Payment::errorHandler();ShopCategory::errorHandler();


        Product::errorHandler();
        // Prerequisites:
        //        Text::errorHandler();
        //        Discount::errorHandler(); // Called by Customer::errorHandler();
        //        Manufacturer::errorHandler();
        // Postrequisites:
        //        Customer::errorHandler();

        //Discount::errorHandler(); // Called by Customer::errorHandler();

        //Manufacturer::errorHandler(); // Called by Product::errorHandler();
        // Prerequisites:
        //        Text::errorHandler();

        //Customer::errorHandler(); // Called by Product::errorHandler();
        // Prerequisites:
        //        ShopSettings::errorHandler();
        //        Country::errorHandler(); // Called by Order::errorHandler();
        //        Order::errorHandler(); // Calls required Country::errorHandler();
        //        Discount::errorHandler(); // Called by Product::errorHandler();

        //Order::errorHandler(); // Called by Customer::errorHandler();
        // Prerequisites:
        //        ShopSettings::errorHandler();
        //        Country::errorHandler();


        ShopMail::errorHandler();
        // Prerequisites:
        //        MailTemplate::errorHandler();


        Payment::errorHandler();
        // Prerequisites:
        //        Text::errorHandler();
        //        Zones::errorHandler();
        //        Yellowpay::errorHandler();

        //Zones::errorHandler(); // Called by Payment::errorHandler();Shipment::errorHandler();
        // Prerequisites:
        //        Text::errorHandler();

        //Yellowpay::errorHandler(); // Called by Payment::errorHandler();
        // Prerequisites:
        //        SettingDb::errorHandler();


        PaymentProcessing::errorHandler();


        Shipment::errorHandler();
        // Prerequisites:
        //        Zones::errorHandler();
        // TODO: Check for and resolve recursion!


        ShopCategory::errorHandler();
        // Prerequisites:
        //        Text::errorHandler();
        //        ShopSettings::errorHandler();


        Vat::errorHandler();



        // Finally, see what page templates need to be updated



    }
    catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}
