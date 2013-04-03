<?php

function _shopUpdate()
{
    global $objDatabase, $_ARRAYLANG;

    if (!defined('MODULE_INDEX')) define('MODULE_INDEX', '');

    try {
        $table_name = DBPREFIX.'module_shop_config';
        // Mind that this table does no longer exist from version 3
        if (Cx\Lib\UpdateUtil::table_exist($table_name)) {
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

        // Update Attribute price to signed.
        // price_prefix is removed for version 3.  See Attribute::errorHandler()
        $table_name = DBPREFIX.'module_shop_products_attributes_value';
        if (   Cx\Lib\UpdateUtil::table_exist($table_name)
            && Cx\Lib\UpdateUtil::column_exist($table_name, 'price_prefix')
            && Cx\Lib\UpdateUtil::table_exist(DBPREFIX.'module_shop_order_items_attributes')
            && Cx\Lib\UpdateUtil::column_exist(DBPREFIX.'module_shop_order_items_attributes', 'price_prefix')
        ) {
            $query = "
                UPDATE `$table_name`
                   SET `price`=-`price`,
                       `price_prefix`='+'
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

        // Update tables' field types and indices
        $table_name = DBPREFIX.'module_shop_article_group';
        if (   Cx\Lib\UpdateUtil::table_exist($table_name)
            && Cx\Lib\UpdateUtil::column_exist($table_name, 'name')) {
            Cx\Lib\UpdateUtil::table($table_name,
                array(
                    'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'primary' => true, 'auto_increment' => true),
                    'name' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'renamefrom' => 'name'),
                )
            );
        }
        $table_name = DBPREFIX.'module_shop_customer_group';
        if (   Cx\Lib\UpdateUtil::table_exist($table_name)
            && Cx\Lib\UpdateUtil::column_exist($table_name, 'name')) {
            Cx\Lib\UpdateUtil::table($table_name,
                array(
                    'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'primary' => true, 'auto_increment' => true),
                    'name' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                )
            );
        }
        $table_name = DBPREFIX.'module_shop_discountgroup_count_name';
        if (   Cx\Lib\UpdateUtil::table_exist($table_name)
            && Cx\Lib\UpdateUtil::column_exist($table_name, 'name')) {
            Cx\Lib\UpdateUtil::table($table_name,
                array(
                    'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'primary' => true, 'auto_increment' => true),
                    'name' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                    'unit' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                )
            );
        }
        $table_name = DBPREFIX.'module_shop_discountgroup_count_rate';
        if (Cx\Lib\UpdateUtil::table_exist($table_name)) {
            Cx\Lib\UpdateUtil::table($table_name,
                array(
                    'group_id' => array('type' => 'INT(10) UNSIGNED', 'notnull' => true, 'primary' => true, 'default' => 0),
                    'count' => array('type' => 'INT(10) UNSIGNED', 'notnull' => true, 'primary' => true, 'default' => '1'),
                    'rate' => array('type' => 'DECIMAL(5,2)', 'unsigned' => true, 'notnull' => true, 'default' => '0.0'),
                )
            );
        }
        $table_name = DBPREFIX.'module_shop_rel_discount_group';
        if (Cx\Lib\UpdateUtil::table_exist($table_name)) {
            Cx\Lib\UpdateUtil::table($table_name,
                array(
                    'customer_group_id' => array('type' => 'INT(10) UNSIGNED', 'notnull' => true, 'primary' => true, 'default' => '0'),
                    'article_group_id' => array('type' => 'INT(10) UNSIGNED', 'notnull' => true, 'primary' => true, 'default' => '0'),
                    'rate' => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.0'),
                )
            );
        }
        $table_name = DBPREFIX.'module_shop_lsv';
        if (Cx\Lib\UpdateUtil::table_exist($table_name)) {
            Cx\Lib\UpdateUtil::table($table_name,
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
        }
        $table_name = DBPREFIX.'module_shop_shipment_cost';
        if (   Cx\Lib\UpdateUtil::table_exist($table_name)
            && Cx\Lib\UpdateUtil::column_exist($table_name, 'price_free')) {
            Cx\Lib\UpdateUtil::table($table_name,
                array(
                    'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'shipper_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                    'max_weight' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false),
                    'cost' => array('type' => 'DECIMAL(10,2)', 'unsigned' => true, 'notnull' => false),
                    'price_free' => array('type' => 'DECIMAL(10,2)', 'unsigned' => true, 'notnull' => false),
                )
            );
        }
        $table_name = DBPREFIX.'module_shop_shipper';
        if (   Cx\Lib\UpdateUtil::table_exist($table_name)
            && Cx\Lib\UpdateUtil::column_exist($table_name, 'name')) {
            Cx\Lib\UpdateUtil::table($table_name,
                array(
                    'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'name' => array('type' => 'TINYTEXT'),
                    'status' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                )
            );
        }
        // Note that countries are migrated to the core_countries table
        // for version 3, and this table is then dropped.
        // See Country::errorHandler()
        $table_name = DBPREFIX.'module_shop_countries';
        if (Cx\Lib\UpdateUtil::table_exist($table_name)) {
            Cx\Lib\UpdateUtil::table($table_name,
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
        }
        // Add Category description to old table version with "catid"
        // primary key only!  Fulltext indices are added when migrating
        // to core_text anyway, so don't bother with text fields here.
        $table_name = DBPREFIX.'module_shop_categories';
        if (   Cx\Lib\UpdateUtil::table_exist($table_name)
            && Cx\Lib\UpdateUtil::column_exist($table_name, 'catid')) {
            Cx\Lib\UpdateUtil::table(
                $table_name,
                array(
                    'catid' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'primary' => true, 'auto_increment' => true),
                    'parentid' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                    'catname' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                    'catdesc' => array('type' => 'TEXT', 'notnull' => true, 'default' => ''),
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
        // Settings table fields -- this is supposed to exist; see above
/*        $table_name = DBPREFIX.'module_shop_config';
        if (Cx\Lib\UpdateUtil::table_exist($table_name)) {
            Cx\Lib\UpdateUtil::table($table_name,
                array(
                    'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'primary' => true, 'auto_increment' => true),
                    'name' => array('type' => 'VARCHAR(64)', 'notnull' => true, 'default' => ''),
                    'value' => array('type' => 'VARCHAR(255)', 'notnull', 'default' => ''),
                    'status' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                )
            );
        }*/
        $table_name = DBPREFIX.'module_shop_currencies';
        if (   Cx\Lib\UpdateUtil::table_exist($table_name)
            && Cx\Lib\UpdateUtil::column_exist($table_name, 'name')) {
            $query = "
                UPDATE `$table_name`
                SET sort_order = 0 WHERE sort_order IS NULL";
            Cx\Lib\UpdateUtil::sql($query);
            // Currencies table fields
            Cx\Lib\UpdateUtil::table($table_name,
                array(
                    'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'code' => array('type' => 'CHAR(3)', 'notnull' => true, 'default' => ''),
                    'symbol' => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => ''),
                    'name' => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => ''),
                    'rate' => array('type' => 'DECIMAL(10,4)', 'unsigned' => true, 'notnull' => true, 'default' => '1.0000'),
                    'sort_order' => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                    'status' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                    'is_default' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                )
            );
        }
        // Note that this table is migrated to access_users for version 3,
        // then dropped.
        $table_name = DBPREFIX.'module_shop_customers';
        if (Cx\Lib\UpdateUtil::table_exist($table_name)) {
            Cx\Lib\UpdateUtil::table($table_name,
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
        }
        $table_name = DBPREFIX.'module_shop_importimg';
        if (Cx\Lib\UpdateUtil::table_exist($table_name)) {
            Cx\Lib\UpdateUtil::table($table_name,
                array(
                    'img_id' => array('type' => 'INT(10) UNSIGNED', 'notnull' => true, 'primary' => true, 'auto_increment' => true),
                    'img_name' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                    'img_cats' => array('type' => 'TEXT',  'notnull' => true, 'default' => ''),
                    'img_fields_file' => array('type' => 'TEXT',  'notnull' => true, 'default' => ''),
                    'img_fields_db' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                )
            );
        }
        // Note that the following two tables are migrated to MailTemplate
        // for version 3, then dropped.
        $table_name = DBPREFIX.'module_shop_mail';
        if (Cx\Lib\UpdateUtil::table_exist($table_name)) {
            Cx\Lib\UpdateUtil::table($table_name,
                array(
                    'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'tplname' => array('type' => 'VARCHAR(60)', 'notnull' => true, 'default' => ''),
                    'protected' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                )
            );
        }
        $table_name = DBPREFIX.'module_shop_mail_content';
        if (Cx\Lib\UpdateUtil::table_exist($table_name)) {
            Cx\Lib\UpdateUtil::table($table_name,
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
        }
        // Note:  No changes necessary; the manufacturer table will be
        // completely modified in Manufacturer::errorHandler() below.
/*        $table_name = DBPREFIX.'module_shop_manufacturer';
        if (   Cx\Lib\UpdateUtil::table_exist($table_name)
            && Cx\Lib\UpdateUtil::column_exist($table_name, 'name')) {
            Cx\Lib\UpdateUtil::table($table_name,
                DBPREFIX.'',
                array(
                    'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'name' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                    'url' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                )
            );
        }*/
        $table_name = DBPREFIX.'module_shop_order_items';
        if (   Cx\Lib\UpdateUtil::table_exist($table_name)
            && Cx\Lib\UpdateUtil::column_exist($table_name, 'order_items_id')) {
            Cx\Lib\UpdateUtil::table(
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
        // Note: Removed field price_prefix for version 2.2; no changes since
        $table_name = DBPREFIX.'module_shop_order_items_attributes';
        if (   Cx\Lib\UpdateUtil::table_exist($table_name)
            && Cx\Lib\UpdateUtil::column_exist($table_name, 'price_prefix')) {
            Cx\Lib\UpdateUtil::table($table_name,
                array(
                    'orders_items_attributes_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'order_items_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                    'order_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                    'product_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                    'product_option_name' => array('type' => 'VARCHAR(32)', 'notnull' => true, 'default' => ''),
                    'product_option_value' => array('type' => 'VARCHAR(32)', 'notnull' => true, 'default' => ''),
                    'product_option_values_price' => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00'),
                )
            );
        }
        $table_name = DBPREFIX.'module_shop_orders';
        if (   Cx\Lib\UpdateUtil::table_exist($table_name)
            && Cx\Lib\UpdateUtil::column_exist($table_name, 'orderid')) {
            Cx\Lib\UpdateUtil::table(
                $table_name,
                array(
                    'orderid' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
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
                    'ship_zip' => array('type' => 'VARCHAR(10)', 'notnull' => false),
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
        $table_name = DBPREFIX.'module_shop_payment';
        if (   Cx\Lib\UpdateUtil::table_exist($table_name)
            && Cx\Lib\UpdateUtil::column_exist($table_name, 'name')) {
            Cx\Lib\UpdateUtil::table($table_name,
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
        }
        // Note:  No changes (still single language in version 3)
        $table_name = DBPREFIX.'module_shop_payment_processors';
        if (Cx\Lib\UpdateUtil::table_exist($table_name)) {
            Cx\Lib\UpdateUtil::table($table_name,
                array(
                    'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'type' => array('type' => 'ENUM(\'internal\',\'external\')', 'notnull' => true, 'default' => 'internal'),
                    'name' => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                    'description' => array('type' => 'TEXT'),
                    'company_url' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                    'status' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => false, 'default' => '1'),
                    'picture' => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                )
            );
        }
        // Note:  No changes (still single language in version 3)
        $table_name = DBPREFIX.'module_shop_pricelists';
        if (Cx\Lib\UpdateUtil::table_exist($table_name)) {
            Cx\Lib\UpdateUtil::table($table_name,
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
        }
        $table_name = DBPREFIX.'module_shop_products';
        if (   Cx\Lib\UpdateUtil::table_exist($table_name)
            && Cx\Lib\UpdateUtil::column_exist($table_name, 'title')) {
            $query = "
                UPDATE `$table_name`
                   SET `description`=''
                 WHERE `description` IS NULL";
            if ($objDatabase->Execute($query) == false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
            Cx\Lib\UpdateUtil::table(
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
        }
        // Note:  The following three tables are renamed for version 3.
        // See Attribute::errorHandler()
        $table_name = DBPREFIX.'module_shop_products_attributes';
        if (Cx\Lib\UpdateUtil::table_exist($table_name)) {
            Cx\Lib\UpdateUtil::table($table_name,
                array(
                    'attribute_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'product_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                    'attributes_name_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                    'attributes_value_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                    'sort_id' => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                )
            );
        }
        $table_name = DBPREFIX.'module_shop_products_attributes_name';
        if (Cx\Lib\UpdateUtil::table_exist($table_name)) {
            Cx\Lib\UpdateUtil::table($table_name,
                array(
                    'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'name' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                    'display_type' => array('type' => 'TINYINT(3)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                )
            );
        }
        $table_name = DBPREFIX.'module_shop_products_attributes_value';
        if (Cx\Lib\UpdateUtil::table_exist($table_name)) {
            Cx\Lib\UpdateUtil::table($table_name,
                array(
                    'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'name_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                    'value' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                    'price' => array('type' => 'DECIMAL(9,2)', 'notnull' => false, 'default' => '0.00'),
                )
            );
        }
        // Note:  Obsolete for a while already
        $table_name = DBPREFIX.'module_shop_products_downloads';
        if (Cx\Lib\UpdateUtil::table_exist($table_name)) {
            Cx\Lib\UpdateUtil::drop_table($table_name);
        }
        // Note:  The id field is removed for version 3 from the following
        // three tables
        $table_name = DBPREFIX.'module_shop_rel_countries';
        if (   Cx\Lib\UpdateUtil::table_exist($table_name)
            && Cx\Lib\UpdateUtil::column_exist($table_name, 'id')) {
            Cx\Lib\UpdateUtil::table(
                $table_name,
                array(
                    'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'zones_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                    'countries_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                )
            );
        }
        $table_name = DBPREFIX.'module_shop_rel_payment';
        if (   Cx\Lib\UpdateUtil::table_exist($table_name)
            && Cx\Lib\UpdateUtil::column_exist($table_name, 'id')) {
            Cx\Lib\UpdateUtil::table(
                $table_name,
                array(
                    'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'zones_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                    'payment_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                )
            );
        }
        // Note: This is renamed to module_shop_rel_shipper for version 3.0
        $table_name = DBPREFIX.'module_shop_rel_shipment';
        if (   Cx\Lib\UpdateUtil::table_exist($table_name)
            && Cx\Lib\UpdateUtil::column_exist($table_name, 'id')) {
            Cx\Lib\UpdateUtil::table(
                $table_name,
                array(
                    'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'zones_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                    'shipment_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                )
            );
        }
        $table_name = DBPREFIX.'module_shop_vat';
        if (   Cx\Lib\UpdateUtil::table_exist($table_name)
            && Cx\Lib\UpdateUtil::column_exist($table_name, 'class')) {
            Cx\Lib\UpdateUtil::table($table_name,
                array(
                    'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'class' => array('type' => 'TINYTEXT'),
                    'percent' => array('type' => 'DECIMAL(5,2)', 'unsigned' => true, 'notnull' => true, 'default' => '0.00'),
                )
            );
        }
        $table_name = DBPREFIX.'module_shop_zones';
        if (   Cx\Lib\UpdateUtil::table_exist($table_name)
            && Cx\Lib\UpdateUtil::column_exist($table_name, 'zones_id')) {
            Cx\Lib\UpdateUtil::table(
                $table_name,
                array(
                    'zones_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'zones_name' => array('type' => 'VARCHAR(64)', 'notnull' => true, 'default' => ''),
                    'activation_status' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                )
            );
        }

        // Contrexx 3.0.0 updates from here.
        // NOTE: All of these methods return false.

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


        // Update page templates

        // Remove
        //        [[SHOP_JAVASCRIPT_CODE]]
        Cx\Lib\UpdateUtil::migrateContentPageUsingRegex(
            array('module' => 'shop'),
            '/{SHOP_JAVASCRIPT_CODE}[\r\n]*/',
            '',
            array('content'),
            '3.0.0'
        );

        // Replace
        // In <!-- BEGIN subCategoriesRow -->...<!-- END subCategoriesRow -->
        //    [[SHOP_PRODUCT_DETAILLINK_IMAGE]] =>
        //    index.php?section=shop[[MODULE_INDEX]]&amp;catId=[[SHOP_CATEGORY_ID]]
        Cx\Lib\UpdateUtil::migrateContentPageUsingRegex(
            array('module' => 'shop'),
            '/(<!-- *BEGIN *subCategoriesRow *-->.+?)'.
            '{SHOP_PRODUCT_DETAILLINK_IMAGE}'.
            '(.+?<!-- *END *subCategoriesRow *-->)/s',
            '$1index.php?section=shop{MODULE_INDEX}&amp;catId={SHOP_CATEGORY_ID}$2',
            array('content'),
            '3.0.0'
        );
        //    [[TXT_SEE_LARGE_PICTURE]] => [[TXT_SHOP_GO_TO_CATEGORY]]
        Cx\Lib\UpdateUtil::migrateContentPageUsingRegex(
            array('module' => 'shop'),
            '/(<!-- *BEGIN *subCategoriesRow *-->.+?)'.
            '{TXT_SEE_LARGE_PICTURE}'.
            '(.+?<!-- *END *subCategoriesRow *-->)/s',
            '$1{TXT_SHOP_GO_TO_CATEGORY}$2',
            array('content'),
            '3.0.0'
        );
        //    [[SHOP_PRODUCT_...]] => [[SHOP_CATEGORY_...]]
        // There may be up to nine different such placeholders!
        $subject = NULL;
        Cx\Lib\UpdateUtil::migrateContentPageUsingRegexCallback(
            array('module' => 'shop'),
            '/(<!-- *BEGIN *subCategoriesRow *-->.+?)'.
            '{SHOP_PRODUCT_(.*?)}'.
            '(.+?<!-- *END *subCategoriesRow *-->)/s',
            function($subject) {
                preg_replace(
                    '/{SHOP_PRODUCT_(.*?)}/', '{SHOP_CATEGORY_$1}',
                    $subject);
            },
            array('content'),
            '3.0.0'
        );

        // shop/account
        // Needs to be replaced completely
        Cx\Lib\UpdateUtil::migrateContentPageUsingRegex(
            array('module' => 'shop', 'cmd' => 'account'),
            '/^.+$/s',
            <<< EOF
<div id="shop"><!-- BEGIN core_message -->
  <span class="{MESSAGE_CLASS}">{MESSAGE_TEXT}</span><!-- END core_message -->
  <div id="shop_acc_data">
    <form name="account" action="{SHOP_ACCOUNT_ACTION}" method="post"
          onsubmit="copy_address()" onreset="return shopReset()">
      <div class="customer_address">
        <h2>{TXT_CUSTOMER_ADDRESS}</h2>
        <div class="shop_text">
          <p><label>{TXT_COMPANY}</label>
            <input type="text" tabindex="1" name="company" value="{SHOP_ACCOUNT_COMPANY}" />
          </p>
          <p><label>{TXT_GREETING}<font color="#ff0000">&nbsp;*</font></label>
            <select tabindex="2" name="gender">{SHOP_ACCOUNT_PREFIX}</select>
          </p>
          <p><label>{TXT_SURNAME}<font color="#ff0000">&nbsp;*</font></label>
            <input type="text" tabindex="3" name="lastname" value="{SHOP_ACCOUNT_LASTNAME}" />
          </p>
          <p><label>{TXT_FIRSTNAME}<font color="#ff0000">&nbsp;*</font></label>
            <input type="text" tabindex="4" name="firstname" value="{SHOP_ACCOUNT_FIRSTNAME}" />
          </p>
          <p><label>{TXT_ADDRESS}<font color="#ff0000">&nbsp;*</font></label>
            <input type="text" tabindex="5" name="address" value="{SHOP_ACCOUNT_ADDRESS}" />
          </p>
          <p><label>{TXT_POSTALE_CODE}<font color="#ff0000">&nbsp;*</font></label>
            <input type="text" tabindex="6" name="zip" value="{SHOP_ACCOUNT_ZIP}" />
          </p>
          <p><label>{TXT_CITY}<font color="#ff0000">&nbsp;*</font></label>
            <input type="text" tabindex="7" name="city" value="{SHOP_ACCOUNT_CITY}" />
          </p>
          <p><label>{TXT_COUNTRY}</label>
            <select name="countryId" id="countryId" tabindex="8">
            {SHOP_ACCOUNT_COUNTRY_MENUOPTIONS}
            </select>
          </p>
          <p><label>{TXT_PHONE_NUMBER}<font color="#ff0000">&nbsp;*</font></label>
            <input type="text" tabindex="9" name="phone" value="{SHOP_ACCOUNT_PHONE}" />
          </p>
          <p><label>{TXT_FAX_NUMBER}</label>
            <input type="text" tabindex="10" name="fax" value="{SHOP_ACCOUNT_FAX}" />
          </p>
        </div>
      </div><!-- BEGIN shipping_address -->
      <div class="shipping_address">
        <h2>{TXT_SHIPPING_ADDRESS}</h2>
        <p><input type="checkbox" tabindex="21" value="1" onclick="copy_address();"
          id="equal_address" name="equal_address" {SHOP_EQUAL_ADDRESS_CHECKED} />
          <label class="description" for="equal_address">{TXT_SAME_BILLING_ADDRESS}</label>
        </p>
      </div>
      <div id="shipping_address" style="display: {SHOP_EQUAL_ADDRESS_DISPLAY};">
        <div class="shop_text">
          <p><label>{TXT_COMPANY}</label>
            <input type="text" tabindex="31" name="company2" value="{SHOP_ACCOUNT_COMPANY2}" />
          </p>
          <p><label>{TXT_GREETING}<font color="#ff0000">&nbsp;*</font></label>
            <select tabindex="32" name="gender2">{SHOP_ACCOUNT_PREFIX2}</select>
          </p>
          <p><label>{TXT_SURNAME}<font color="#ff0000">&nbsp;*</font></label>
            <input type="text" tabindex="33" name="lastname2" value="{SHOP_ACCOUNT_LASTNAME2}" />
          </p>
          <p><label>{TXT_FIRSTNAME}<font color="#ff0000">&nbsp;*</font></label>
            <input type="text" tabindex="34" name="firstname2" value="{SHOP_ACCOUNT_FIRSTNAME2}" />
          </p>
          <p><label>{TXT_ADDRESS}<font color="#ff0000">&nbsp;*</font></label>
            <input type="text" tabindex="35" name="address2" value="{SHOP_ACCOUNT_ADDRESS2}" />
          </p>
          <p><label>{TXT_POSTALE_CODE}<font color="#ff0000">&nbsp;*</font></label>
            <input type="text" tabindex="36" name="zip2" value="{SHOP_ACCOUNT_ZIP2}" size="6" />
          </p>
          <p><label>{TXT_CITY}<font color="#ff0000">&nbsp;*</font></label>
            <input type="text" tabindex="37" name="city2" value="{SHOP_ACCOUNT_CITY2}" />
          </p>
          <p><label>{TXT_COUNTRY}</label>
            <input type="hidden" name="countryId2" id="countryId2" value="{SHOP_ACCOUNT_COUNTRY2_ID}" />{SHOP_ACCOUNT_COUNTRY2}
          </p>
          <p><label>{TXT_PHONE_NUMBER}<font color="#ff0000">&nbsp;*</font></label>
            <input type="text" tabindex="38" name="phone2" value="{SHOP_ACCOUNT_PHONE2}" />
          </p>
        </div>
      </div><!-- END shipping_address --><!-- BEGIN account_details -->
      <div class="account_details">
        <h2>{TXT_YOUR_ACCOUNT_DETAILS}</h2><!-- BEGIN dont_register -->
        <p>
          <input type="checkbox" tabindex="61" value="1" id="dont_register"
                 name="dont_register" {SHOP_DONT_REGISTER_CHECKED}
                 onClick="document.getElementById('account_password').style.display = (this.checked ? 'none' : 'block');" />
          <label class="description" for="dont_register">{TXT_SHOP_ACCOUNT_DONT_REGISTER}</label>
          <br />
          {TXT_SHOP_ACCOUNT_DONT_REGISTER_NOTE}
        </p><!-- END dont_register -->
        <div class="shop_text">
          <p>
            <label>{TXT_EMAIL}<font color="#ff0000">&nbsp;*</font></label>
            <input type="text" tabindex="51" name="email" value="{SHOP_ACCOUNT_EMAIL}" />
          </p>
          <div id="account_password" style="{SHOP_ACCOUNT_PASSWORD_DISPLAY};">
            <p>
              <label>{TXT_PASSWORD}<font color="#ff0000">&nbsp;*</font></label>
              <input type="password" tabindex="52" name="password" value="" />
            </p>
            <p>{TXT_SHOP_ACCOUNT_PASSWORD_HINT}</p>
          </div>
        </div>
      </div><!-- END account_details -->
      <p>
        <input type="reset" value="{TXT_RESET}" name="reset" tabindex="71" />
        <input type="submit" value="{TXT_SHOP_CONTINUE_ARROW}" name="bsubmit" tabindex="72" />
      </p>
    </form>
  </div>
</div>
<script type="text/javascript">//<![CDATA[
function copy_address() {
  with (document.account) {
    if (jQuery("#equal_address:checked").length) {
      gender2.value = gender.value;
      company2.value = company.value;
      lastname2.value = lastname.value;
      firstname2.value = firstname.value;
      address2.value = address.value;
      zip2.value = zip.value;
      city2.value = city.value;
      phone2.value = phone.value;
      countryId2.value = countryId.value;
      jQuery("#shipping_address").hide();
    } else {
      jQuery("#shipping_address").show();
// Optionally clear the shipment address
//      gender2.value = "";
//      company2.value = "";
//      lastname2.value = "";
//      firstname2.value = "";
//      address2.value = "";
//      zip2.value = "";
//      city2.value = "";
//      phone2.value = "";
    }
  }
}
jQuery(function () {
  jQuery(".customer_address").delegate("input", "blur", function() {
    if (jQuery("#equal_address:checked").length) {
      copy_address();
    }
  });
});
// Redisplay the shipping address after the reset button has been clicked
function shopReset()
{
  if (!confirm("{TXT_SHOP_FORM_RESET_CONFIRM}")) {
    return false;
  }
  jQuery("#shipping_address").show();
  return true;
}
copy_address();
//}></script>
EOF
            ,
            array('content'),
            '3.0.0'
        );

        // shop/login
        // Needs to be replaced completely
        Cx\Lib\UpdateUtil::migrateContentPageUsingRegex(
            array('module' => 'shop', 'cmd' => 'login'),
            '/^.+$/s',
            <<< EOF
<div id="shop">
  <!-- BEGIN core_message -->
  <span class="{MESSAGE_CLASS}">{MESSAGE_TEXT}</span>
  <!-- END core_message -->
  <div class="customer_old">
    <form name="shop_login" action="index.php?section=login" method="post">
      <input name="redirect" type="hidden" value="{SHOP_LOGIN_REDIRECT}" />
      <h2>{TXT_SHOP_ACCOUNT_EXISTING_CUSTOMER}</h2>
      <p>
        <label for="username">{TXT_SHOP_EMAIL_ADDRESS}</label>
        <input type="text" maxlength="250" value="{SHOP_LOGIN_EMAIL}" id="username" name="USERNAME" />
      </p>
      <p>
        <label for="password">{TXT_SHOP_PASSWORD}</label>
        <input type="password" maxlength="50" id="password" name="PASSWORD" />
      </p>
      <p>
        <input type="submit" value="{TXT_SHOP_ACCOUNT_LOGIN}" name="login" />
      </p>
      <p>
        <a class="lostpw" href="index.php?section=login&amp;cmd=lostpw" title="{TXT_SHOP_ACCOUNT_LOST_PASSWORD}">
        {TXT_SHOP_ACCOUNT_LOST_PASSWORD}
        </a>
      </p>
    </form>
  </div>
  <div class="customer_new">
    <form name="shop_register" action="index.php?section=shop&amp;cmd=login" method="post">
      <h2>{TXT_SHOP_ACCOUNT_NEW_CUSTOMER}</h2>
      {TXT_SHOP_ACCOUNT_NOTE}<br />
      <br />
      <!-- BEGIN register -->
      <input type="submit" value="{TXT_SHOP_BUTTON_REGISTRATION}" name="baccount" />
      <!-- END register -->
      <!-- BEGIN dont_register -->
      <input type="submit" value="{TXT_SHOP_BUTTON_NO_REGISTRATION}" name="bnoaccount" />
      <!-- END dont_register -->
    </form>
  </div>
</div>
EOF
            ,
            array('content'),
            '3.0.0'
        );

        // Note:  Other templates may contain new placeholders and/or blocks,
        // however, these need to be added manually for version 3.0.0 features
        // to work.
    }
    catch (Cx\Lib\UpdateException $e) {
        return Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}
