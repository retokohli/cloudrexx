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
            && Cx\Lib\UpdateUtil::column_exist($table_name, 'price_prefix')) {
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
                    'rate' => array('type' => 'DECIMAL(10,6)', 'unsigned' => true, 'notnull' => true, 'default' => '1.000000'),
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
                    'text' => array('type' => 'TEXT'),
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



function _shopInstall()
{
    try {

        /**************************************************************************
         * EXTENSION:   Initial creation (for contrexx editions <> premium with   *
         *              version < 3.0.0 which have this module not yet installed) *
         *                                                                        *
         * ADDED:       Contrexx v3.0.1                                           *
         **************************************************************************/
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'core_country',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'alpha2' => array('type' => 'CHAR(2)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'alpha3' => array('type' => 'CHAR(3)', 'notnull' => true, 'default' => '', 'after' => 'alpha2'),
                'ord' => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'alpha3'),
                'active' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'ord'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."core_country` (`id`, `alpha2`, `alpha3`, `ord`, `active`)
            VALUES  (1, 'AF', 'AFG', 0, 0),
                    (2, 'AL', 'ALB', 0, 0),
                    (3, 'DZ', 'DZA', 0, 0),
                    (4, 'AS', 'ASM', 0, 0),
                    (5, 'AD', 'AND', 0, 0),
                    (6, 'AO', 'AGO', 0, 0),
                    (7, 'AI', 'AIA', 0, 0),
                    (8, 'AQ', 'ATA', 0, 0),
                    (9, 'AG', 'ATG', 0, 0),
                    (10, 'AR', 'ARG', 0, 0),
                    (11, 'AM', 'ARM', 0, 0),
                    (12, 'AW', 'ABW', 0, 0),
                    (13, 'AU', 'AUS', 0, 0),
                    (14, 'AT', 'AUT', 0, 1),
                    (15, 'AZ', 'AZE', 0, 0),
                    (16, 'BS', 'BHS', 0, 0),
                    (17, 'BH', 'BHR', 0, 0),
                    (18, 'BD', 'BGD', 0, 0),
                    (19, 'BB', 'BRB', 0, 0),
                    (20, 'BY', 'BLR', 0, 0),
                    (21, 'BE', 'BEL', 0, 0),
                    (22, 'BZ', 'BLZ', 0, 0),
                    (23, 'BJ', 'BEN', 0, 0),
                    (24, 'BM', 'BMU', 0, 0),
                    (25, 'BT', 'BTN', 0, 0),
                    (26, 'BO', 'BOL', 0, 0),
                    (27, 'BA', 'BIH', 0, 0),
                    (28, 'BW', 'BWA', 0, 0),
                    (29, 'BV', 'BVT', 0, 0),
                    (30, 'BR', 'BRA', 0, 0),
                    (31, 'IO', 'IOT', 0, 0),
                    (32, 'BN', 'BRN', 0, 0),
                    (33, 'BG', 'BGR', 0, 0),
                    (34, 'BF', 'BFA', 0, 0),
                    (35, 'BI', 'BDI', 0, 0),
                    (36, 'KH', 'KHM', 0, 0),
                    (37, 'CM', 'CMR', 0, 0),
                    (38, 'CA', 'CAN', 0, 0),
                    (39, 'CV', 'CPV', 0, 0),
                    (40, 'KY', 'CYM', 0, 0),
                    (41, 'CF', 'CAF', 0, 0),
                    (42, 'TD', 'TCD', 0, 0),
                    (43, 'CL', 'CHL', 0, 0),
                    (44, 'CN', 'CHN', 0, 0),
                    (45, 'CX', 'CXR', 0, 0),
                    (46, 'CC', 'CCK', 0, 0),
                    (47, 'CO', 'COL', 0, 0),
                    (48, 'KM', 'COM', 0, 0),
                    (49, 'CG', 'COG', 0, 0),
                    (50, 'CK', 'COK', 0, 0),
                    (51, 'CR', 'CRI', 0, 0),
                    (52, 'CI', 'CIV', 0, 0),
                    (53, 'HR', 'HRV', 0, 0),
                    (54, 'CU', 'CUB', 0, 0),
                    (55, 'CY', 'CYP', 0, 0),
                    (56, 'CZ', 'CZE', 0, 0),
                    (57, 'DK', 'DNK', 0, 0),
                    (58, 'DJ', 'DJI', 0, 0),
                    (59, 'DM', 'DMA', 0, 0),
                    (60, 'DO', 'DOM', 0, 0),
                    (61, 'TP', 'TMP', 0, 0),
                    (62, 'EC', 'ECU', 0, 0),
                    (63, 'EG', 'EGY', 0, 0),
                    (64, 'SV', 'SLV', 0, 0),
                    (65, 'GQ', 'GNQ', 0, 0),
                    (66, 'ER', 'ERI', 0, 0),
                    (67, 'EE', 'EST', 0, 0),
                    (68, 'ET', 'ETH', 0, 0),
                    (69, 'FK', 'FLK', 0, 0),
                    (70, 'FO', 'FRO', 0, 0),
                    (71, 'FJ', 'FJI', 0, 0),
                    (72, 'FI', 'FIN', 0, 0),
                    (73, 'FR', 'FRA', 0, 0),
                    (74, 'FX', 'FXX', 0, 0),
                    (75, 'GF', 'GUF', 0, 0),
                    (76, 'PF', 'PYF', 0, 0),
                    (77, 'TF', 'ATF', 0, 0),
                    (78, 'GA', 'GAB', 0, 0),
                    (79, 'GM', 'GMB', 0, 0),
                    (80, 'GE', 'GEO', 0, 0),
                    (81, 'DE', 'DEU', 0, 1),
                    (82, 'GH', 'GHA', 0, 0),
                    (83, 'GI', 'GIB', 0, 0),
                    (84, 'GR', 'GRC', 0, 0),
                    (85, 'GL', 'GRL', 0, 0),
                    (86, 'GD', 'GRD', 0, 0),
                    (87, 'GP', 'GLP', 0, 0),
                    (88, 'GU', 'GUM', 0, 0),
                    (89, 'GT', 'GTM', 0, 0),
                    (90, 'GN', 'GIN', 0, 0),
                    (91, 'GW', 'GNB', 0, 0),
                    (92, 'GY', 'GUY', 0, 0),
                    (93, 'HT', 'HTI', 0, 0),
                    (94, 'HM', 'HMD', 0, 0),
                    (95, 'HN', 'HND', 0, 0),
                    (96, 'HK', 'HKG', 0, 0),
                    (97, 'HU', 'HUN', 0, 0),
                    (98, 'IS', 'ISL', 0, 0),
                    (99, 'IN', 'IND', 0, 0),
                    (100, 'ID', 'IDN', 0, 0),
                    (101, 'IR', 'IRN', 0, 0),
                    (102, 'IQ', 'IRQ', 0, 0),
                    (103, 'IE', 'IRL', 0, 0),
                    (104, 'IL', 'ISR', 0, 0),
                    (105, 'IT', 'ITA', 0, 0),
                    (106, 'JM', 'JAM', 0, 0),
                    (107, 'JP', 'JPN', 0, 0),
                    (108, 'JO', 'JOR', 0, 0),
                    (109, 'KZ', 'KAZ', 0, 0),
                    (110, 'KE', 'KEN', 0, 0),
                    (111, 'KI', 'KIR', 0, 0),
                    (112, 'KP', 'PRK', 0, 0),
                    (113, 'KR', 'KOR', 0, 0),
                    (114, 'KW', 'KWT', 0, 0),
                    (115, 'KG', 'KGZ', 0, 0),
                    (116, 'LA', 'LAO', 0, 0),
                    (117, 'LV', 'LVA', 0, 0),
                    (118, 'LB', 'LBN', 0, 0),
                    (119, 'LS', 'LSO', 0, 0),
                    (120, 'LR', 'LBR', 0, 0),
                    (121, 'LY', 'LBY', 0, 0),
                    (122, 'LI', 'LIE', 0, 1),
                    (123, 'LT', 'LTU', 0, 0),
                    (124, 'LU', 'LUX', 0, 0),
                    (125, 'MO', 'MAC', 0, 0),
                    (126, 'MK', 'MKD', 0, 0),
                    (127, 'MG', 'MDG', 0, 0),
                    (128, 'MW', 'MWI', 0, 0),
                    (129, 'MY', 'MYS', 0, 0),
                    (130, 'MV', 'MDV', 0, 0),
                    (131, 'ML', 'MLI', 0, 0),
                    (132, 'MT', 'MLT', 0, 0),
                    (133, 'MH', 'MHL', 0, 0),
                    (134, 'MQ', 'MTQ', 0, 0),
                    (135, 'MR', 'MRT', 0, 0),
                    (136, 'MU', 'MUS', 0, 0),
                    (137, 'YT', 'MYT', 0, 0),
                    (138, 'MX', 'MEX', 0, 0),
                    (139, 'FM', 'FSM', 0, 0),
                    (140, 'MD', 'MDA', 0, 0),
                    (141, 'MC', 'MCO', 0, 0),
                    (142, 'MN', 'MNG', 0, 0),
                    (143, 'MS', 'MSR', 0, 0),
                    (144, 'MA', 'MAR', 0, 0),
                    (145, 'MZ', 'MOZ', 0, 0),
                    (146, 'MM', 'MMR', 0, 0),
                    (147, 'NA', 'NAM', 0, 0),
                    (148, 'NR', 'NRU', 0, 0),
                    (149, 'NP', 'NPL', 0, 0),
                    (150, 'NL', 'NLD', 0, 0),
                    (151, 'AN', 'ANT', 0, 0),
                    (152, 'NC', 'NCL', 0, 0),
                    (153, 'NZ', 'NZL', 0, 0),
                    (154, 'NI', 'NIC', 0, 0),
                    (155, 'NE', 'NER', 0, 0),
                    (156, 'NG', 'NGA', 0, 0),
                    (157, 'NU', 'NIU', 0, 0),
                    (158, 'NF', 'NFK', 0, 0),
                    (159, 'MP', 'MNP', 0, 0),
                    (160, 'NO', 'NOR', 0, 0),
                    (161, 'OM', 'OMN', 0, 0),
                    (162, 'PK', 'PAK', 0, 0),
                    (163, 'PW', 'PLW', 0, 0),
                    (164, 'PA', 'PAN', 0, 0),
                    (165, 'PG', 'PNG', 0, 0),
                    (166, 'PY', 'PRY', 0, 0),
                    (167, 'PE', 'PER', 0, 0),
                    (168, 'PH', 'PHL', 0, 0),
                    (169, 'PN', 'PCN', 0, 0),
                    (170, 'PL', 'POL', 0, 0),
                    (171, 'PT', 'PRT', 0, 0),
                    (172, 'PR', 'PRI', 0, 0),
                    (173, 'QA', 'QAT', 0, 0),
                    (174, 'RE', 'REU', 0, 0),
                    (175, 'RO', 'ROM', 0, 0),
                    (176, 'RU', 'RUS', 0, 0),
                    (177, 'RW', 'RWA', 0, 0),
                    (178, 'KN', 'KNA', 0, 0),
                    (179, 'LC', 'LCA', 0, 0),
                    (180, 'VC', 'VCT', 0, 0),
                    (181, 'WS', 'WSM', 0, 0),
                    (182, 'SM', 'SMR', 0, 0),
                    (183, 'ST', 'STP', 0, 0),
                    (184, 'SA', 'SAU', 0, 0),
                    (185, 'SN', 'SEN', 0, 0),
                    (186, 'SC', 'SYC', 0, 0),
                    (187, 'SL', 'SLE', 0, 0),
                    (188, 'SG', 'SGP', 0, 0),
                    (189, 'SK', 'SVK', 0, 0),
                    (190, 'SI', 'SVN', 0, 0),
                    (191, 'SB', 'SLB', 0, 0),
                    (192, 'SO', 'SOM', 0, 0),
                    (193, 'ZA', 'ZAF', 0, 0),
                    (194, 'GS', 'SGS', 0, 0),
                    (195, 'ES', 'ESP', 0, 0),
                    (196, 'LK', 'LKA', 0, 0),
                    (197, 'SH', 'SHN', 0, 0),
                    (198, 'PM', 'SPM', 0, 0),
                    (199, 'SD', 'SDN', 0, 0),
                    (200, 'SR', 'SUR', 0, 0),
                    (201, 'SJ', 'SJM', 0, 0),
                    (202, 'SZ', 'SWZ', 0, 0),
                    (203, 'SE', 'SWE', 0, 0),
                    (204, 'CH', 'CHE', 0, 1),
                    (205, 'SY', 'SYR', 0, 0),
                    (206, 'TW', 'TWN', 0, 0),
                    (207, 'TJ', 'TJK', 0, 0),
                    (208, 'TZ', 'TZA', 0, 0),
                    (209, 'TH', 'THA', 0, 0),
                    (210, 'TG', 'TGO', 0, 0),
                    (211, 'TK', 'TKL', 0, 0),
                    (212, 'TO', 'TON', 0, 0),
                    (213, 'TT', 'TTO', 0, 0),
                    (214, 'TN', 'TUN', 0, 0),
                    (215, 'TR', 'TUR', 0, 0),
                    (216, 'TM', 'TKM', 0, 0),
                    (217, 'TC', 'TCA', 0, 0),
                    (218, 'TV', 'TUV', 0, 0),
                    (219, 'UG', 'UGA', 0, 0),
                    (220, 'UA', 'UKR', 0, 0),
                    (221, 'AE', 'ARE', 0, 0),
                    (222, 'GB', 'GBR', 0, 0),
                    (223, 'US', 'USA', 0, 0),
                    (224, 'UM', 'UMI', 0, 0),
                    (225, 'UY', 'URY', 0, 0),
                    (226, 'UZ', 'UZB', 0, 0),
                    (227, 'VU', 'VUT', 0, 0),
                    (228, 'VA', 'VAT', 0, 0),
                    (229, 'VE', 'VEN', 0, 0),
                    (230, 'VN', 'VNM', 0, 0),
                    (231, 'VG', 'VGB', 0, 0),
                    (232, 'VI', 'VIR', 0, 0),
                    (233, 'WF', 'WLF', 0, 0),
                    (234, 'EH', 'ESH', 0, 0),
                    (235, 'YE', 'YEM', 0, 0),
                    (236, 'YU', 'YUG', 0, 0),
                    (237, 'ZR', 'ZAR', 0, 0),
                    (238, 'ZM', 'ZMB', 0, 0),
                    (239, 'ZW', 'ZWE', 0, 0)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'core_mail_template',
            array(
                'key' => array('type' => 'tinytext'),
                'section' => array('type' => 'tinytext', 'after' => 'key'),
                'text_id' => array('type' => 'INT(10)', 'unsigned' => true, 'after' => 'section'),
                'html' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'text_id'),
                'protected' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'html'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        Cx\Lib\UpdateUtil::sql("
            ALTER TABLE `".DBPREFIX."core_mail_template`
            ADD PRIMARY KEY (`key` (32), `section` (32))
        ");
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."core_mail_template` (`key`, `section`, `text_id`, `html`, `protected`)
            VALUES  ('customer_login', 'shop', 1, 1, 1),
                    ('order_complete', 'shop', 2, 1, 1),
                    ('order_confirmation', 'shop', 3, 1, 1)
            ON DUPLICATE KEY UPDATE `key` = `key`
        ");


        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'core_setting',
            array(
                'section' => array('type' => 'VARCHAR(32)', 'default' => '', 'primary' => true),
                'name' => array('type' => 'VARCHAR(255)', 'default' => '', 'primary' => true),
                'group' => array('type' => 'VARCHAR(32)', 'default' => '', 'primary' => true),
                'type' => array('type' => 'VARCHAR(32)', 'notnull' => true, 'default' => 'text', 'after' => 'group'),
                'value' => array('type' => 'text', 'notnull' => true, 'after' => 'type'),
                'values' => array('type' => 'text', 'notnull' => true, 'after' => 'value'),
                'ord' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'values'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."core_setting` (`section`, `name`, `group`, `type`, `value`, `values`, `ord`)
            VALUES  ('core', 'numof_countries_per_page_backend', 'country', 'text', '30', '', 101),
                    ('shop', 'address', 'config', 'text', 'MaxMuster AG\r\nFirmenstrasse 1\r\n4321 Irgendwo', '', 20),
                    ('shop', 'company', 'config', 'text', 'MaxMuster AG', '', 19),
                    ('shop', 'country_id', 'config', 'text', '204', '', 13),
                    ('shop', 'datatrans_active', 'config', 'text', '1', '', 29),
                    ('shop', 'datatrans_merchant_id', 'config', 'text', '123456789', '', 28),
                    ('shop', 'datatrans_request_type', 'config', 'text', 'CAA', '', 30),
                    ('shop', 'datatrans_use_testserver', 'config', 'text', '1', '', 31),
                    ('shop', 'email', 'config', 'text', 'webmaster@contrexx.local', '', 1),
                    ('shop', 'email_confirmation', 'config', 'text', 'webmaster@contrexx.local', '', 5),
                    ('shop', 'fax', 'config', 'text', '012 3456790', '', 7),
                    ('shop', 'numof_coupon_per_page_backend', 'config', 'text', '25', '', 58),
                    ('shop', 'numof_customers_per_page_backend', 'config', 'text', '25', '', 55),
                    ('shop', 'numof_mailtemplate_per_page_backend', 'config', 'text', '25', '', 57),
                    ('shop', 'numof_manufacturers_per_page_backend', 'config', 'text', '25', '', 56),
                    ('shop', 'numof_orders_per_page_backend', 'config', 'text', '25', '', 54),
                    ('shop', 'numof_products_per_page_backend', 'config', 'text', '25', '', 216),
                    ('shop', 'numof_products_per_page_frontend', 'config', 'text', '25', '', 53),
                    ('shop', 'orderitems_amount_max', 'config', 'text', '0', '', 45),
                    ('shop', 'payment_lsv_active', 'config', 'text', '1', '', 18),
                    ('shop', 'paypal_account_email', 'config', 'text', 'info@example.com', '', 9),
                    ('shop', 'paypal_active', 'config', 'text', '1', '', 10),
                    ('shop', 'paypal_default_currency', 'config', 'text', 'EUR', '', 17),
                    ('shop', 'postfinance_accepted_payment_methods', 'config', 'text', '', '', 25),
                    ('shop', 'postfinance_active', 'config', 'text', '1', '', 12),
                    ('shop', 'postfinance_authorization_type', 'config', 'text', 'SAL', '', 8),
                    ('shop', 'postfinance_hash_signature_in', 'config', 'text', 'sech10zeichenminimum', '', 47),
                    ('shop', 'postfinance_hash_signature_out', 'config', 'text', 'sech10zeichenminimum', '', 48),
                    ('shop', 'postfinance_mobile_ijustwanttotest', 'config', 'text', '1', '', 51),
                    ('shop', 'postfinance_mobile_sign', 'config', 'text', 'geheime_signatur', '', 50),
                    ('shop', 'postfinance_mobile_status', 'config', 'text', '0', '', 52),
                    ('shop', 'postfinance_mobile_webuser', 'config', 'text', 'Benutzername', '', 49),
                    ('shop', 'postfinance_shop_id', 'config', 'text', 'demoShop', '', 11),
                    ('shop', 'postfinance_use_testserver', 'config', 'text', '1', '', 26),
                    ('shop', 'product_sorting', 'config', 'text', '1', '', 27),
                    ('shop', 'register', 'config', 'dropdown', 'optional', '0:mandatory,1:optional,2:none', 46),
                    ('shop', 'saferpay_active', 'config', 'text', '1', '', 3),
                    ('shop', 'saferpay_finalize_payment', 'config', 'text', '1', '', 15),
                    ('shop', 'saferpay_id', 'config', 'text', '12345-12345678', '', 2),
                    ('shop', 'saferpay_use_test_account', 'config', 'text', '1', '', 14),
                    ('shop', 'saferpay_window_option', 'config', 'text', '2', '', 16),
                    ('shop', 'show_products_default', 'config', 'text', '1', '', 32),
                    ('shop', 'telephone', 'config', 'text', '012 3456789', '', 6),
                    ('shop', 'thumbnail_max_height', 'config', 'text', '999', '', 22),
                    ('shop', 'thumbnail_max_width', 'config', 'text', '180', '', 21),
                    ('shop', 'thumbnail_quality', 'config', 'text', '95', '', 23),
                    ('shop', 'user_profile_attribute_customer_group_id', 'config', 'dropdown_user_custom_attribute', '2', '', 351),
                    ('shop', 'user_profile_attribute_notes', 'config', 'dropdown_user_custom_attribute', '1', '', 352),
                    ('shop', 'usergroup_id_customer', 'config', 'dropdown_usergroup', '6', '', 341),
                    ('shop', 'usergroup_id_reseller', 'config', 'dropdown_usergroup', '7', '', 342),
                    ('shop', 'vat_default_id', 'config', 'text', '1', '', 41),
                    ('shop', 'vat_enabled_foreign_customer', 'config', 'text', '0', '', 33),
                    ('shop', 'vat_enabled_foreign_reseller', 'config', 'text', '0', '', 34),
                    ('shop', 'vat_enabled_home_customer', 'config', 'text', '1', '', 35),
                    ('shop', 'vat_enabled_home_reseller', 'config', 'text', '1', '', 36),
                    ('shop', 'vat_included_foreign_customer', 'config', 'text', '0', '', 37),
                    ('shop', 'vat_included_foreign_reseller', 'config', 'text', '0', '', 38),
                    ('shop', 'vat_included_home_customer', 'config', 'text', '1', '', 39),
                    ('shop', 'vat_included_home_reseller', 'config', 'text', '1', '', 40),
                    ('shop', 'vat_other_id', 'config', 'text', '1', '', 42),
                    ('shop', 'weight_enable', 'config', 'text', '0', '', 24),
                    ('egov', 'postfinance_shop_id', 'config', 'text', 'Ihr Kontoname', '', 1),
                    ('egov', 'postfinance_active', 'config', 'checkbox', '0', '1', 2),
                    ('egov', 'postfinance_authorization_type', 'config', 'dropdown', 'SAL', 'RES:Reservation,SAL:Verkauf', 3),
                    ('egov', 'postfinance_hash_signature_in', 'config', 'text', 'Mindestens 16 Buchstaben, Ziffern und Zeichen', '', 5),
                    ('egov', 'postfinance_hash_signature_out', 'config', 'text', 'Mindestens 16 Buchstaben, Ziffern und Zeichen', '', 6),
                    ('egov', 'postfinance_use_testserver', 'config', 'checkbox', '1', '1', 7),
                    ('shop', 'use_js_cart', 'config', 'checkbox', '1', '1', 47),
                    ('shop', 'shopnavbar_on_all_pages', 'config', 'checkbox', '1', '1', 48),
                    ('filesharing', 'permission', 'config', 'text', 'off', '', 0)
            ON DUPLICATE KEY UPDATE `section` = `section`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'core_text',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'lang_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'primary' => true, 'after' => 'id'),
                'section' => array('type' => 'VARCHAR(32)', 'notnull' => true, 'default' => '', 'primary' => true, 'after' => 'lang_id'),
                'key' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'primary' => true, 'after' => 'section'),
                'text' => array('type' => 'text', 'after' => 'key'),
            ),
            array(
                'text' => array('fields' => array('text'), 'type' => 'FULLTEXT'),
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."core_text` (`id`, `lang_id`, `section`, `key`, `text`)
            VALUES  (1, 1, 'core', 'core_country_name', 'Afghanistan'),
                    (1, 1, 'shop', 'attribute_name', 'Zusatzleistungen'),
                    (1, 1, 'shop', 'core_mail_template_bcc', ''),
                    (1, 1, 'shop', 'core_mail_template_cc', ''),
                    (1, 1, 'shop', 'core_mail_template_from', 'webmaster@contrexx.local'),
                    (1, 1, 'shop', 'core_mail_template_message', '[CUSTOMER_SALUTATION]\r\n\r\nHier Ihre Zugangsdaten zum Shop:[[CUSTOMER_LOGIN]\r\nBenutzername: [CUSTOMER_USERNAME]\r\nPasswort: [CUSTOMER_PASSWORD][CUSTOMER_LOGIN]]\r\n\r\nMit freundlichen Grüssen\r\nIhr [SHOP_COMPANY] Online Shop Team\r\n\r\n[SHOP_HOMEPAGE]\r\n'),
                    (1, 1, 'shop', 'core_mail_template_message_html', '[CUSTOMER_SALUTATION]<br />\r\n<br />\r\nHier Ihre Zugangsdaten zum Shop:<br /><!-- [[CUSTOMER_LOGIN] -->\r\nBenutzername: [CUSTOMER_USERNAME]<br />\r\nPasswort: [CUSTOMER_PASSWORD]<br /><!-- [CUSTOMER_LOGIN]] -->\r\n<br />\r\nMit freundlichen Gr&uuml;ssen<br />\r\nIhr [SHOP_COMPANY] Online Shop Team<br />\r\n<br />\r\n[SHOP_HOMEPAGE]<br />\r\n'),
                    (1, 1, 'shop', 'core_mail_template_name', 'Zugangsdaten'),
                    (1, 1, 'shop', 'core_mail_template_reply', 'webmaster@contrexx.local'),
                    (1, 1, 'shop', 'core_mail_template_sender', 'Contrexx Demo'),
                    (1, 1, 'shop', 'core_mail_template_subject', 'Zugangsdaten'),
                    (1, 1, 'shop', 'core_mail_template_to', 'webmaster@contrexx.local'),
                    (1, 1, 'shop', 'discount_group_article', 'Telefone'),
                    (1, 1, 'shop', 'discount_group_customer', 'Neukunden'),
                    (1, 1, 'shop', 'discount_group_name', 'Mengenrabatt'),
                    (1, 1, 'shop', 'discount_group_unit', 'Stück'),
                    (1, 1, 'shop', 'manufacturer_name', 'Comvation Internet Solutions'),
                    (1, 1, 'shop', 'manufacturer_uri', 'http://www.comvation.com'),
                    (1, 1, 'shop', 'option_name', 'Leder-Etui'),
                    (12, 1, 'shop', 'product_uri', 'http://www.htc.com/de/'),
                    (1, 1, 'shop', 'shipper_name', 'PostPac Priority'),
                    (1, 1, 'shop', 'vat_class', 'Nicht Taxpflichtig'),
                    (1, 1, 'shop', 'zone_name', 'All'),
                    (1, 2, 'shop', 'category_name', 'Gadgets'),
                    (1, 2, 'shop', 'currency_name', 'Schweizer Franken'),
                    (2, 1, 'core', 'core_country_name', 'Albania'),
                    (2, 1, 'shop', 'core_mail_template_bcc', ''),
                    (2, 1, 'shop', 'core_mail_template_cc', ''),
                    (2, 1, 'shop', 'core_mail_template_from', 'webmaster@contrexx.local'),
                    (2, 1, 'shop', 'core_mail_template_message', '[CUSTOMER_SALUTATION]\r\n\r\nIhre Bestellung wurde ausgeführt. Sie werden in den nächsten Tagen ihre Lieferung erhalten.\r\n\r\nHerzlichen Dank für das Vertrauen.\r\nWir würden uns freuen, wenn Sie uns weiterempfehlen und wünschen Ihnen noch einen schönen Tag.\r\n\r\nMit freundlichen Grüssen\r\nIhr [SHOP_COMPANY] Online Shop Team\r\n\r\n[SHOP_HOMEPAGE]\r\n'),
                    (2, 1, 'shop', 'core_mail_template_name', 'Auftrag abgeschlossen'),
                    (2, 1, 'shop', 'core_mail_template_reply', 'webmaster@contrexx.local'),
                    (2, 1, 'shop', 'core_mail_template_sender', 'Contrexx Demo'),
                    (2, 1, 'shop', 'core_mail_template_subject', 'Auftrag abgeschlossen'),
                    (2, 1, 'shop', 'core_mail_template_to', 'webmaster@contrexx.local'),
                    (2, 1, 'shop', 'discount_group_customer', 'Stammkunden'),
                    (2, 1, 'shop', 'manufacturer_name', 'Apple, Inc.'),
                    (2, 1, 'shop', 'manufacturer_uri', 'http://www.apple.com/'),
                    (2, 1, 'shop', 'option_name', 'Pimp my Handy Kit'),
                    (2, 1, 'shop', 'payment_name', 'VISA, Mastercard'),
                    (2, 1, 'shop', 'shipper_name', 'Express Post'),
                    (2, 1, 'shop', 'vat_class', 'Deutschland Normalsatz'),
                    (2, 1, 'shop', 'zone_name', 'Schweiz'),
                    (3, 1, 'core', 'core_country_name', 'Algeria'),
                    (3, 1, 'shop', 'core_mail_template_bcc', ''),
                    (3, 1, 'shop', 'core_mail_template_cc', ''),
                    (3, 1, 'shop', 'core_mail_template_from', 'webmaster@contrexx.local'),
                    (3, 1, 'shop', 'core_mail_template_message', '[CUSTOMER_SALUTATION],\r\n\r\nHerzlichen Dank für Ihre Bestellung im [SHOP_COMPANY] Online Shop.\r\n\r\nIhre Auftrags-Nr. lautet: [ORDER_ID]\r\nIhre Kunden-Nr. lautet: [CUSTOMER_ID]\r\nBestellungszeit: [ORDER_DATE] [ORDER_TIME]\r\n\r\n------------------------------------------------------------------------\r\nBestellinformationen\r\n------------------------------------------------------------------------[[ORDER_ITEM]\r\nID:             [PRODUCT_ID]\r\nArtikel Nr.:    [PRODUCT_CODE]\r\nMenge:          [PRODUCT_QUANTITY]\r\nBeschreibung:   [PRODUCT_TITLE][[PRODUCT_OPTIONS]\r\n                [PRODUCT_OPTIONS][PRODUCT_OPTIONS]]\r\nStückpreis:      [PRODUCT_ITEM_PRICE] [CURRENCY]                       Total [PRODUCT_TOTAL_PRICE] [CURRENCY][[USER_DATA]\r\nBenutzername:   [USER_NAME]\r\nPasswort:       [USER_PASS][USER_DATA]][[COUPON_DATA]\r\nGutschein Code: [COUPON_CODE][COUPON_DATA]][ORDER_ITEM]]\r\n------------------------------------------------------------------------\r\nZwischensumme:    [ORDER_ITEM_COUNT] Artikel                             [ORDER_ITEM_SUM] [CURRENCY][[DISCOUNT_COUPON]\r\nGutschein Code: [DISCOUNT_COUPON_CODE]   [DISCOUNT_COUPON_AMOUNT] [CURRENCY][DISCOUNT_COUPON]]\r\n------------------------------------------------------------------------[[SHIPMENT]\r\nVersandart:     [SHIPMENT_NAME]   [SHIPMENT_PRICE] [CURRENCY][SHIPMENT]][[PAYMENT]\r\nBezahlung:      [PAYMENT_NAME]   [PAYMENT_PRICE] [CURRENCY][PAYMENT]][[TAX]\r\n[TAX_TEXT]                   [TAX_PRICE] [CURRENCY][TAX]]\r\n------------------------------------------------------------------------\r\nGesamtsumme                                                [ORDER_SUM] [CURRENCY]\r\n------------------------------------------------------------------------\r\n\r\nIhre Kundenadresse:\r\n[CUSTOMER_COMPANY]\r\n[CUSTOMER_FIRSTNAME] [CUSTOMER_LASTNAME]\r\n[CUSTOMER_ADDRESS]\r\n[CUSTOMER_ZIP] [CUSTOMER_CITY]\r\n[CUSTOMER_COUNTRY][[SHIPPING_ADDRESS]\r\n\r\n\r\nLieferadresse:\r\n[SHIPPING_COMPANY]\r\n[SHIPPING_FIRSTNAME] [SHIPPING_LASTNAME]\r\n[SHIPPING_ADDRESS]\r\n[SHIPPING_ZIP] [SHIPPING_CITY]\r\n[SHIPPING_COUNTRY][SHIPPING_ADDRESS]]\r\n\r\nIhr Link zum Online Store: [SHOP_HOMEPAGE][[CUSTOMER_LOGIN]\r\n\r\nIhre Zugangsdaten zum Shop:\r\nBenutzername:   [CUSTOMER_USERNAME]\r\nPasswort:       [CUSTOMER_PASSWORD][CUSTOMER_LOGIN]]\r\n\r\nWir freuen uns auf Ihren nächsten Besuch im [SHOP_COMPANY] Online Store und wünschen Ihnen noch einen schönen Tag.\r\n\r\nP.S. Diese Auftragsbestätigung wurde gesendet an: [CUSTOMER_EMAIL]\r\n\r\nMit freundlichen Grüssen\r\nIhr [SHOP_COMPANY] Online Shop Team\r\n\r\n[SHOP_HOMEPAGE]\r\n'),
                    (3, 1, 'shop', 'core_mail_template_message_html', '[CUSTOMER_SALUTATION],<br />\r\n<br />\r\nHerzlichen Dank f&uuml;r Ihre Bestellung im [SHOP_COMPANY] Online Shop.<br />\r\n<br />\r\nIhre Auftrags-Nr. lautet: [ORDER_ID]<br />\r\nIhre Kunden-Nr. lautet: [CUSTOMER_ID]<br />\r\nBestellungszeit: [ORDER_DATE] [ORDER_TIME]<br />\r\n<br />\r\n<br />\r\n<table cellspacing=\"1\" cellpadding=\"1\" style=\"border: 0;\">\r\n  <tbody>\r\n    <tr>\r\n      <td colspan=\"6\">Bestellinformationen</td>\r\n    </tr>\r\n    <tr>\r\n      <td><div style=\"text-align: right;\">ID</div></td>\r\n      <td><div style=\"text-align: right;\">Artikel Nr.</div></td>\r\n      <td><div style=\"text-align: right;\">Menge</div></td>\r\n      <td>Beschreibung</td>\r\n      <td><div style=\"text-align: right;\">St&uuml;ckpreis</div></td>\r\n      <td><div style=\"text-align: right;\">Total</div></td>\r\n    </tr><!--[[ORDER_ITEM]-->\r\n    <tr>\r\n      <td><div style=\"text-align: right;\">[PRODUCT_ID]</div></td>\r\n      <td><div style=\"text-align: right;\">[PRODUCT_CODE]</div></td>\r\n      <td><div style=\"text-align: right;\">[PRODUCT_QUANTITY]</div></td>\r\n      <td>[PRODUCT_TITLE]<!--[[PRODUCT_OPTIONS]--><br />\r\n        [PRODUCT_OPTIONS]<!--[PRODUCT_OPTIONS]]--></td>\r\n      <td><div style=\"text-align: right;\">[PRODUCT_ITEM_PRICE] [CURRENCY]</div></td>\r\n      <td><div style=\"text-align: right;\">[PRODUCT_TOTAL_PRICE] [CURRENCY]</div></td>\r\n    </tr><!--[[USER_DATA]-->\r\n    <tr>\r\n      <td colspan=\"3\">&nbsp;</td>\r\n      <td>Benutzername: [USER_NAME]<br />Passwort: [USER_PASS]</td>\r\n      <td colspan=\"2\">&nbsp;</td>\r\n    </tr><!--[USER_DATA]]--><!--[[COUPON_DATA]-->\r\n    <tr>\r\n      <td colspan=\"3\">&nbsp;</td>\r\n      <td>Gutschein Code: [COUPON_CODE]</td>\r\n      <td colspan=\"2\">&nbsp;</td>\r\n    </tr><!--[COUPON_DATA]]--><!--[ORDER_ITEM]]-->\r\n    <tr style=\"border-top: 4px none;\">\r\n      <td colspan=\"2\">Zwischensumme</td>\r\n      <td><div style=\"text-align: right;\">[ORDER_ITEM_COUNT]</div></td>\r\n      <td colspan=\"2\">Artikel</td>\r\n      <td><div style=\"text-align: right;\">[ORDER_ITEM_SUM] [CURRENCY]</div></td>\r\n    </tr><!--[[DISCOUNT_COUPON]-->\r\n    <tr style=\"border-top: 4px none;\">\r\n      <td colspan=\"3\">Gutscheincode</td>\r\n      <td colspan=\"2\">[DISCOUNT_COUPON_CODE]</td>\r\n      <td><div style=\"text-align: right;\">[DISCOUNT_COUPON_AMOUNT] [CURRENCY]</div></td>\r\n    </tr><!--[DISCOUNT_COUPON]]-->\r\n    <tr style=\"border-top: 2px none;\">\r\n      <td colspan=\"3\">Versandart</td>\r\n      <td colspan=\"2\">[SHIPMENT_NAME]</td>\r\n      <td><div style=\"text-align: right;\">[SHIPMENT_PRICE] [CURRENCY]</div></td>\r\n    </tr>\r\n    <tr style=\"border-top: 2px none;\">\r\n      <td colspan=\"3\">Bezahlung</td>\r\n      <td colspan=\"2\">[PAYMENT_NAME]</td>\r\n      <td><div style=\"text-align: right;\">[PAYMENT_PRICE] [CURRENCY]</div></td>\r\n    </tr>\r\n    <tr style=\"border-top: 2px none;\">\r\n      <td colspan=\"5\">[TAX_TEXT]</td>\r\n      <td><div style=\"text-align: right;\">[TAX_PRICE] [CURRENCY]</div></td>\r\n    </tr>\r\n    <tr style=\"border-top: 4px none;\">\r\n      <td colspan=\"5\">Gesamtsumme</td>\r\n      <td><div style=\"text-align: right;\">[ORDER_SUM] [CURRENCY]</div></td>\r\n    </tr>\r\n  </tbody>\r\n</table>\r\n<br />\r\n<br />\r\nIhre Kundenadresse:<br />\r\n[CUSTOMER_COMPANY]<br />\r\n[CUSTOMER_FIRSTNAME] [CUSTOMER_LASTNAME]<br />\r\n[CUSTOMER_ADDRESS]<br />\r\n[CUSTOMER_ZIP] [CUSTOMER_CITY]<br />\r\n[CUSTOMER_COUNTRY]<br /><!--[[SHIPPING_ADDRESS]-->\r\n<br />\r\n<br />\r\nLieferadresse:<br />\r\n[SHIPPING_COMPANY]<br />\r\n[SHIPPING_FIRSTNAME] [SHIPPING_LASTNAME]<br />\r\n[SHIPPING_ADDRESS]<br />\r\n[SHIPPING_ZIP] [SHIPPING_CITY]<br />\r\n[SHIPPING_COUNTRY]<br /><!--[SHIPPING_ADDRESS]]-->\r\n<br />\r\n<br />\r\nIhr Link zum Online Store: [SHOP_HOMEPAGE]<br /><!--[[CUSTOMER_LOGIN]-->\r\n<br />\r\nIhre Zugangsdaten zum Shop:<br />\r\nBenutzername:   [CUSTOMER_USERNAME]<br />\r\nPasswort:       [CUSTOMER_PASSWORD]<br /><!--[CUSTOMER_LOGIN]]-->\r\n<br />\r\nWir freuen uns auf Ihren n&auml;chsten Besuch im [SHOP_COMPANY] Online Store und w&uuml;nschen Ihnen noch einen sch&ouml;nen Tag.<br />\r\n<br />\r\nP.S. Diese Auftragsbest&auml;tigung wurde gesendet an: [CUSTOMER_EMAIL]<br />\r\n<br />\r\nMit freundlichen Gr&uuml;ssen<br />\r\nIhr [SHOP_COMPANY] Online Shop Team<br />\r\n<br />\r\n[SHOP_HOMEPAGE]<br />\r\n<br />\r\n'),
                    (3, 1, 'shop', 'core_mail_template_name', 'Bestellungsbestätigung'),
                    (3, 1, 'shop', 'core_mail_template_reply', 'webmaster@contrexx.local'),
                    (3, 1, 'shop', 'core_mail_template_sender', 'Contrexx Demo'),
                    (3, 1, 'shop', 'core_mail_template_subject', 'Bestellungsbestätigung'),
                    (3, 1, 'shop', 'core_mail_template_to', 'webmaster@contrexx.local'),
                    (3, 1, 'shop', 'discount_group_customer', 'Goldkunden'),
                    (3, 1, 'shop', 'shipper_name', 'Schweizerische Post'),
                    (3, 1, 'shop', 'vat_class', 'Deutschland ermässigt'),
                    (3, 1, 'shop', 'zone_name', 'Deutschland'),
                    (3, 2, 'shop', 'category_name', 'Mitgliedschaft'),
                    (4, 1, 'core', 'core_country_name', 'American Samoa'),
                    (4, 1, 'shop', 'shipper_name', 'Direct to Me'),
                    (4, 1, 'shop', 'vat_class', 'Deutschland stark ermässigt'),
                    (4, 2, 'shop', 'currency_name', 'Euro'),
                    (5, 1, 'core', 'core_country_name', 'Andorra'),
                    (5, 1, 'shop', 'vat_class', 'Deutschland Zwischensatz 1'),
                    (5, 2, 'shop', 'currency_name', 'United States Dollars'),
                    (6, 1, 'core', 'core_country_name', 'Angola'),
                    (1, 1, 'shop', 'currency_name', 'Schweizer Franken'),
                    (3, 1, 'shop', 'option_name', 'Headset'),
                    (13, 1, 'shop', 'product_name', 'Contrexx® Premium'),
                    (3, 1, 'shop', 'manufacturer_name', 'HTC'),
                    (3, 1, 'shop', 'manufacturer_uri', 'http://www.htc.com/'),
                    (11, 1, 'shop', 'category_name', 'Mitgliedschaft'),
                    (6, 1, 'shop', 'vat_class', 'Deutschland Zwischensatz 2'),
                    (7, 1, 'core', 'core_country_name', 'Anguilla'),
                    (7, 1, 'shop', 'vat_class', 'Österreich Normalsatz'),
                    (8, 1, 'core', 'core_country_name', 'Antarctica'),
                    (8, 1, 'shop', 'vat_class', 'Österreich ermässigt'),
                    (9, 1, 'core', 'core_country_name', 'Antigua and Barbuda'),
                    (9, 1, 'shop', 'payment_name', 'Nachnahme'),
                    (9, 1, 'shop', 'vat_class', 'Österreich Zwischensatz'),
                    (10, 1, 'core', 'core_country_name', 'Argentina'),
                    (10, 1, 'shop', 'vat_class', 'Schweiz'),
                    (11, 1, 'core', 'core_country_name', 'Armenia'),
                    (11, 1, 'shop', 'vat_class', 'Schweiz ermässigt 1'),
                    (12, 1, 'core', 'core_country_name', 'Aruba'),
                    (12, 1, 'shop', 'payment_name', 'Paypal'),
                    (12, 1, 'shop', 'vat_class', 'Schweiz ermässigt 2'),
                    (13, 1, 'core', 'core_country_name', 'Australia'),
                    (13, 1, 'shop', 'payment_name', 'LSV'),
                    (13, 1, 'shop', 'vat_class', 'Great Britain'),
                    (14, 1, 'core', 'core_country_name', 'Österreich'),
                    (14, 1, 'shop', 'payment_name', 'PostFinance (PostCard, Kreditkarte)'),
                    (14, 1, 'shop', 'vat_class', 'Great Britain reduced'),
                    (15, 1, 'core', 'core_country_name', 'Azerbaijan'),
                    (15, 1, 'shop', 'payment_name', 'Datatrans'),
                    (16, 1, 'core', 'core_country_name', 'Bahamas'),
                    (17, 1, 'core', 'core_country_name', 'Bahrain'),
                    (18, 1, 'core', 'core_country_name', 'Bangladesh'),
                    (19, 1, 'core', 'core_country_name', 'Barbados'),
                    (20, 1, 'core', 'core_country_name', 'Belarus'),
                    (21, 1, 'core', 'core_country_name', 'Belgium'),
                    (22, 1, 'core', 'core_country_name', 'Belize'),
                    (23, 1, 'core', 'core_country_name', 'Benin'),
                    (24, 1, 'core', 'core_country_name', 'Bermuda'),
                    (25, 1, 'core', 'core_country_name', 'Bhutan'),
                    (26, 1, 'core', 'core_country_name', 'Bolivia'),
                    (27, 1, 'core', 'core_country_name', 'Bosnia and Herzegowina'),
                    (28, 1, 'core', 'core_country_name', 'Botswana'),
                    (29, 1, 'core', 'core_country_name', 'Bouvet Island'),
                    (30, 1, 'core', 'core_country_name', 'Brazil'),
                    (31, 1, 'core', 'core_country_name', 'British Indian Ocean Territory'),
                    (32, 1, 'core', 'core_country_name', 'Brunei Darussalam'),
                    (33, 1, 'core', 'core_country_name', 'Bulgaria'),
                    (34, 1, 'core', 'core_country_name', 'Burkina Faso'),
                    (35, 1, 'core', 'core_country_name', 'Burundi'),
                    (36, 1, 'core', 'core_country_name', 'Cambodia'),
                    (37, 1, 'core', 'core_country_name', 'Cameroon'),
                    (38, 1, 'core', 'core_country_name', 'Canada'),
                    (39, 1, 'core', 'core_country_name', 'Cape Verde'),
                    (40, 1, 'core', 'core_country_name', 'Cayman Islands'),
                    (41, 1, 'core', 'core_country_name', 'Central African Republic'),
                    (42, 1, 'core', 'core_country_name', 'Chad'),
                    (43, 1, 'core', 'core_country_name', 'Chile'),
                    (44, 1, 'core', 'core_country_name', 'China'),
                    (45, 1, 'core', 'core_country_name', 'Christmas Island'),
                    (46, 1, 'core', 'core_country_name', 'Cocos (Keeling) Islands'),
                    (47, 1, 'core', 'core_country_name', 'Colombia'),
                    (48, 1, 'core', 'core_country_name', 'Comoros'),
                    (49, 1, 'core', 'core_country_name', 'Congo'),
                    (50, 1, 'core', 'core_country_name', 'Cook Islands'),
                    (51, 1, 'core', 'core_country_name', 'Costa Rica'),
                    (52, 1, 'core', 'core_country_name', 'Cote D''Ivoire'),
                    (53, 1, 'core', 'core_country_name', 'Croatia'),
                    (54, 1, 'core', 'core_country_name', 'Cuba'),
                    (55, 1, 'core', 'core_country_name', 'Cyprus'),
                    (56, 1, 'core', 'core_country_name', 'Czech Republic'),
                    (57, 1, 'core', 'core_country_name', 'Denmark'),
                    (58, 1, 'core', 'core_country_name', 'Djibouti'),
                    (59, 1, 'core', 'core_country_name', 'Dominica'),
                    (60, 1, 'core', 'core_country_name', 'Dominican Republic'),
                    (61, 1, 'core', 'core_country_name', 'East Timor'),
                    (62, 1, 'core', 'core_country_name', 'Ecuador'),
                    (63, 1, 'core', 'core_country_name', 'Egypt'),
                    (64, 1, 'core', 'core_country_name', 'El Salvador'),
                    (65, 1, 'core', 'core_country_name', 'Equatorial Guinea'),
                    (66, 1, 'core', 'core_country_name', 'Eritrea'),
                    (67, 1, 'core', 'core_country_name', 'Estonia'),
                    (68, 1, 'core', 'core_country_name', 'Ethiopia'),
                    (69, 1, 'core', 'core_country_name', 'Falkland Islands (Malvinas)'),
                    (70, 1, 'core', 'core_country_name', 'Faroe Islands'),
                    (71, 1, 'core', 'core_country_name', 'Fiji'),
                    (72, 1, 'core', 'core_country_name', 'Finland'),
                    (73, 1, 'core', 'core_country_name', 'France'),
                    (74, 1, 'core', 'core_country_name', 'France, Metropolitan'),
                    (75, 1, 'core', 'core_country_name', 'French Guiana'),
                    (76, 1, 'core', 'core_country_name', 'French Polynesia'),
                    (77, 1, 'core', 'core_country_name', 'French Southern Territories'),
                    (78, 1, 'core', 'core_country_name', 'Gabon'),
                    (79, 1, 'core', 'core_country_name', 'Gambia'),
                    (80, 1, 'core', 'core_country_name', 'Georgia'),
                    (81, 1, 'core', 'core_country_name', 'Deutschland'),
                    (82, 1, 'core', 'core_country_name', 'Ghana'),
                    (83, 1, 'core', 'core_country_name', 'Gibraltar'),
                    (84, 1, 'core', 'core_country_name', 'Greece'),
                    (85, 1, 'core', 'core_country_name', 'Greenland'),
                    (86, 1, 'core', 'core_country_name', 'Grenada'),
                    (87, 1, 'core', 'core_country_name', 'Guadeloupe'),
                    (88, 1, 'core', 'core_country_name', 'Guam'),
                    (89, 1, 'core', 'core_country_name', 'Guatemala'),
                    (90, 1, 'core', 'core_country_name', 'Guinea'),
                    (91, 1, 'core', 'core_country_name', 'Guinea-bissau'),
                    (92, 1, 'core', 'core_country_name', 'Guyana'),
                    (93, 1, 'core', 'core_country_name', 'Haiti'),
                    (94, 1, 'core', 'core_country_name', 'Heard and Mc Donald Islands'),
                    (95, 1, 'core', 'core_country_name', 'Honduras'),
                    (96, 1, 'core', 'core_country_name', 'Hong Kong'),
                    (97, 1, 'core', 'core_country_name', 'Hungary'),
                    (98, 1, 'core', 'core_country_name', 'Iceland'),
                    (99, 1, 'core', 'core_country_name', 'India'),
                    (100, 1, 'core', 'core_country_name', 'Indonesia'),
                    (101, 1, 'core', 'core_country_name', 'Iran (Islamic Republic of)'),
                    (102, 1, 'core', 'core_country_name', 'Iraq'),
                    (103, 1, 'core', 'core_country_name', 'Ireland'),
                    (104, 1, 'core', 'core_country_name', 'Israel'),
                    (105, 1, 'core', 'core_country_name', 'Italy'),
                    (106, 1, 'core', 'core_country_name', 'Jamaica'),
                    (107, 1, 'core', 'core_country_name', 'Japan'),
                    (108, 1, 'core', 'core_country_name', 'Jordan'),
                    (109, 1, 'core', 'core_country_name', 'Kazakhstan'),
                    (110, 1, 'core', 'core_country_name', 'Kenya'),
                    (111, 1, 'core', 'core_country_name', 'Kiribati'),
                    (112, 1, 'core', 'core_country_name', 'Korea, Democratic People''s Republic of'),
                    (113, 1, 'core', 'core_country_name', 'Korea, Republic of'),
                    (114, 1, 'core', 'core_country_name', 'Kuwait'),
                    (115, 1, 'core', 'core_country_name', 'Kyrgyzstan'),
                    (116, 1, 'core', 'core_country_name', 'Lao People''s Democratic Republic'),
                    (117, 1, 'core', 'core_country_name', 'Latvia'),
                    (118, 1, 'core', 'core_country_name', 'Lebanon'),
                    (119, 1, 'core', 'core_country_name', 'Lesotho'),
                    (120, 1, 'core', 'core_country_name', 'Liberia'),
                    (121, 1, 'core', 'core_country_name', 'Libyan Arab Jamahiriya'),
                    (122, 1, 'core', 'core_country_name', 'Liechtenstein'),
                    (123, 1, 'core', 'core_country_name', 'Lithuania'),
                    (124, 1, 'core', 'core_country_name', 'Luxembourg'),
                    (125, 1, 'core', 'core_country_name', 'Macau'),
                    (126, 1, 'core', 'core_country_name', 'Macedonia, The Former Yugoslav Republic of'),
                    (127, 1, 'core', 'core_country_name', 'Madagascar'),
                    (128, 1, 'core', 'core_country_name', 'Malawi'),
                    (129, 1, 'core', 'core_country_name', 'Malaysia'),
                    (130, 1, 'core', 'core_country_name', 'Maldives'),
                    (131, 1, 'core', 'core_country_name', 'Mali'),
                    (132, 1, 'core', 'core_country_name', 'Malta'),
                    (133, 1, 'core', 'core_country_name', 'Marshall Islands'),
                    (134, 1, 'core', 'core_country_name', 'Martinique'),
                    (135, 1, 'core', 'core_country_name', 'Mauritania'),
                    (136, 1, 'core', 'core_country_name', 'Mauritius'),
                    (137, 1, 'core', 'core_country_name', 'Mayotte'),
                    (138, 1, 'core', 'core_country_name', 'Mexico'),
                    (139, 1, 'core', 'core_country_name', 'Micronesia, Federated States of'),
                    (140, 1, 'core', 'core_country_name', 'Moldova, Republic of'),
                    (141, 1, 'core', 'core_country_name', 'Monaco'),
                    (142, 1, 'core', 'core_country_name', 'Mongolia'),
                    (143, 1, 'core', 'core_country_name', 'Montserrat'),
                    (144, 1, 'core', 'core_country_name', 'Morocco'),
                    (145, 1, 'core', 'core_country_name', 'Mozambique'),
                    (146, 1, 'core', 'core_country_name', 'Myanmar'),
                    (147, 1, 'core', 'core_country_name', 'Namibia'),
                    (148, 1, 'core', 'core_country_name', 'Nauru'),
                    (149, 1, 'core', 'core_country_name', 'Nepal'),
                    (150, 1, 'core', 'core_country_name', 'Netherlands'),
                    (151, 1, 'core', 'core_country_name', 'Netherlands Antilles'),
                    (152, 1, 'core', 'core_country_name', 'New Caledonia'),
                    (153, 1, 'core', 'core_country_name', 'New Zealand'),
                    (154, 1, 'core', 'core_country_name', 'Nicaragua'),
                    (155, 1, 'core', 'core_country_name', 'Niger'),
                    (156, 1, 'core', 'core_country_name', 'Nigeria'),
                    (157, 1, 'core', 'core_country_name', 'Niue'),
                    (158, 1, 'core', 'core_country_name', 'Norfolk Island'),
                    (159, 1, 'core', 'core_country_name', 'Northern Mariana Islands'),
                    (160, 1, 'core', 'core_country_name', 'Norway'),
                    (161, 1, 'core', 'core_country_name', 'Oman'),
                    (162, 1, 'core', 'core_country_name', 'Pakistan'),
                    (163, 1, 'core', 'core_country_name', 'Palau'),
                    (164, 1, 'core', 'core_country_name', 'Panama'),
                    (165, 1, 'core', 'core_country_name', 'Papua New Guinea'),
                    (166, 1, 'core', 'core_country_name', 'Paraguay'),
                    (167, 1, 'core', 'core_country_name', 'Peru'),
                    (168, 1, 'core', 'core_country_name', 'Philippines'),
                    (169, 1, 'core', 'core_country_name', 'Pitcairn'),
                    (170, 1, 'core', 'core_country_name', 'Poland'),
                    (171, 1, 'core', 'core_country_name', 'Portugal'),
                    (172, 1, 'core', 'core_country_name', 'Puerto Rico'),
                    (173, 1, 'core', 'core_country_name', 'Qatar'),
                    (174, 1, 'core', 'core_country_name', 'Reunion'),
                    (175, 1, 'core', 'core_country_name', 'Romania'),
                    (176, 1, 'core', 'core_country_name', 'Russian Federation'),
                    (177, 1, 'core', 'core_country_name', 'Rwanda'),
                    (178, 1, 'core', 'core_country_name', 'Saint Kitts and Nevis'),
                    (179, 1, 'core', 'core_country_name', 'Saint Lucia'),
                    (180, 1, 'core', 'core_country_name', 'Saint Vincent and the Grenadines'),
                    (181, 1, 'core', 'core_country_name', 'Samoa'),
                    (182, 1, 'core', 'core_country_name', 'San Marino'),
                    (183, 1, 'core', 'core_country_name', 'Sao Tome and Principe'),
                    (184, 1, 'core', 'core_country_name', 'Saudi Arabia'),
                    (185, 1, 'core', 'core_country_name', 'Senegal'),
                    (186, 1, 'core', 'core_country_name', 'Seychelles'),
                    (187, 1, 'core', 'core_country_name', 'Sierra Leone'),
                    (188, 1, 'core', 'core_country_name', 'Singapore'),
                    (189, 1, 'core', 'core_country_name', 'Slovakia (Slovak Republic)'),
                    (190, 1, 'core', 'core_country_name', 'Slovenia'),
                    (191, 1, 'core', 'core_country_name', 'Solomon Islands'),
                    (192, 1, 'core', 'core_country_name', 'Somalia'),
                    (193, 1, 'core', 'core_country_name', 'South Africa'),
                    (194, 1, 'core', 'core_country_name', 'South Georgia and the South Sandwich Islands'),
                    (195, 1, 'core', 'core_country_name', 'Spain'),
                    (196, 1, 'core', 'core_country_name', 'Sri Lanka'),
                    (197, 1, 'core', 'core_country_name', 'St. Helena'),
                    (198, 1, 'core', 'core_country_name', 'St. Pierre and Miquelon'),
                    (199, 1, 'core', 'core_country_name', 'Sudan'),
                    (200, 1, 'core', 'core_country_name', 'Suriname'),
                    (201, 1, 'core', 'core_country_name', 'Svalbard and Jan Mayen Islands'),
                    (202, 1, 'core', 'core_country_name', 'Swaziland'),
                    (203, 1, 'core', 'core_country_name', 'Sweden'),
                    (204, 1, 'core', 'core_country_name', 'Schweiz'),
                    (205, 1, 'core', 'core_country_name', 'Syrian Arab Republic'),
                    (206, 1, 'core', 'core_country_name', 'Taiwan'),
                    (207, 1, 'core', 'core_country_name', 'Tajikistan'),
                    (208, 1, 'core', 'core_country_name', 'Tanzania, United Republic of'),
                    (209, 1, 'core', 'core_country_name', 'Thailand'),
                    (210, 1, 'core', 'core_country_name', 'Togo'),
                    (211, 1, 'core', 'core_country_name', 'Tokelau'),
                    (212, 1, 'core', 'core_country_name', 'Tonga'),
                    (213, 1, 'core', 'core_country_name', 'Trinidad and Tobago'),
                    (214, 1, 'core', 'core_country_name', 'Tunisia'),
                    (215, 1, 'core', 'core_country_name', 'Turkey'),
                    (216, 1, 'core', 'core_country_name', 'Turkmenistan'),
                    (217, 1, 'core', 'core_country_name', 'Turks and Caicos Islands'),
                    (218, 1, 'core', 'core_country_name', 'Tuvalu'),
                    (219, 1, 'core', 'core_country_name', 'Uganda'),
                    (220, 1, 'core', 'core_country_name', 'Ukraine'),
                    (221, 1, 'core', 'core_country_name', 'United Arab Emirates'),
                    (222, 1, 'core', 'core_country_name', 'United Kingdom'),
                    (223, 1, 'core', 'core_country_name', 'United States'),
                    (224, 1, 'core', 'core_country_name', 'United States Minor Outlying Islands'),
                    (225, 1, 'core', 'core_country_name', 'Uruguay'),
                    (226, 1, 'core', 'core_country_name', 'Uzbekistan'),
                    (227, 1, 'core', 'core_country_name', 'Vanuatu'),
                    (228, 1, 'core', 'core_country_name', 'Vatican City State (Holy See)'),
                    (229, 1, 'core', 'core_country_name', 'Venezuela'),
                    (230, 1, 'core', 'core_country_name', 'Viet Nam'),
                    (231, 1, 'core', 'core_country_name', 'Virgin Islands (British)'),
                    (232, 1, 'core', 'core_country_name', 'Virgin Islands (U.S.)'),
                    (233, 1, 'core', 'core_country_name', 'Wallis and Futuna Islands'),
                    (234, 1, 'core', 'core_country_name', 'Western Sahara'),
                    (235, 1, 'core', 'core_country_name', 'Yemen'),
                    (236, 1, 'core', 'core_country_name', 'Yugoslavia'),
                    (237, 1, 'core', 'core_country_name', 'Zaire'),
                    (238, 1, 'core', 'core_country_name', 'Zambia'),
                    (239, 1, 'core', 'core_country_name', 'Zimbabwe'),
                    (4, 2, 'shop', 'category_name', 'Lorem ipsum1'),
                    (4, 2, 'shop', 'category_description', 'lorem ipsum lorem ipsum lorem ipsum lorem ipsum'),
                    (5, 2, 'shop', 'category_name', 'Lorem ipsum2'),
                    (5, 2, 'shop', 'category_description', 'lorem ipsum lorem ipsum lorem ipsum lorem ipsum'),
                    (6, 2, 'shop', 'category_name', 'Lorem ipsum3'),
                    (6, 2, 'shop', 'category_description', 'lorem ipsum lorem ipsum lorem ipsum lorem ipsum'),
                    (12, 1, 'shop', 'product_short', 'Als eines der ersten Smartphones besitzt das HTC One X einen Quad-Core-Prozessor &ndash; d.h. die Leistung verteilt sich auf 4 Rechenkerne. Dies macht das HTC One X extrem schnell und leistungsf&auml;hig.'),
                    (12, 1, 'shop', 'product_long', '<table border=\"0\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"width: 120px;\">\r\n				<strong>Gr&ouml;&szlig;e:</strong></td>\r\n			<td>\r\n				134,36 x 69,9 x 8,9 mm</td>\r\n		</tr>\r\n		<tr>\r\n			<td>\r\n				<strong>Gewicht:</strong></td>\r\n			<td>\r\n				130 g mit Akku</td>\r\n		</tr>\r\n		<tr>\r\n			<td>\r\n				<strong>Display:</strong></td>\r\n			<td>\r\n				HD 720P Touchscreen</td>\r\n		</tr>\r\n		<tr>\r\n			<td>\r\n				<strong>Bildschirm:</strong></td>\r\n			<td>\r\n				4,7&ldquo; (1280 x 720 Aufl&ouml;sung)</td>\r\n		</tr>\r\n	</tbody>\r\n</table>'),
                    (12, 1, 'shop', 'product_keys', 'HTC, HTC One X'),
                    (12, 1, 'shop', 'product_code', ''),
                    (10, 1, 'shop', 'category_description', 'Alle Versionen des Contrexx Content Management Systems zur Verwaltung Ihrer Website.'),
                    (12, 1, 'shop', 'product_name', 'HTC One X'),
                    (10, 1, 'shop', 'category_name', 'Contrexx CMS Software'),
                    (9, 1, 'shop', 'category_name', 'Mobile Phones'),
                    (9, 1, 'shop', 'category_description', 'Mobile Phones von über 10 Marken wie Apple, HTC & Samsung.'),
                    (8, 2, 'shop', 'category_name', 'Iphone'),
                    (8, 2, 'shop', 'category_description', 'lorem ipsum lorem ipsum lorem ipsum lorem ipsum'),
                    (13, 1, 'shop', 'product_code', ''),
                    (13, 1, 'shop', 'product_uri', 'http://www.contrexx.com'),
                    (13, 1, 'shop', 'product_keys', 'Contrexx CMS'),
                    (13, 1, 'shop', 'product_long', 'Mit dem Contrexx CMS stehen Ihnen &uuml;ber 20 Anwendungen zur Verf&uuml;gung, beispielsweise ein kompletter Online Shop, ein umfangreiches Newsletter-Modul und eine mehrsprachige Website.'),
                    (13, 1, 'shop', 'product_short', 'Contrexx&reg; CMS f&uuml;r die schnelle Verwaltung Ihrer Website.'),
                    (7, 2, 'shop', 'category_name', 'Samsung'),
                    (7, 2, 'shop', 'category_description', 'lorem ipsum lorem ipsum lorem ipsum lorem ipsum'),
                    (1, 2, 'shop', 'manufacturer_name', 'Samsung'),
                    (1, 2, 'shop', 'manufacturer_uri', 'http://www.samsung.com'),
                    (2, 2, 'shop', 'manufacturer_name', 'Apple, Inc.'),
                    (2, 2, 'shop', 'manufacturer_uri', 'http://www.apple.com/'),
                    (4, 1, 'shop', 'manufacturer_name', 'Contrexx'),
                    (4, 1, 'shop', 'manufacturer_uri', 'http://www.contrexx.com'),
                    (11, 1, 'shop', 'category_description', 'Zum Erwerben von Mitgliedschaften bei verschiedenen Vereienen und Organisationen.'),
                    (14, 1, 'shop', 'product_name', 'Mitglied von der MaxMuster-Foundation'),
                    (14, 1, 'shop', 'product_short', 'Unterst&uuml;tzen Sie die MaxMuster-Foundation und werden Sie noch heute Mitglied! Sie profitieren von MItgliederrabatten an verschiedenen Anl&auml;ssen.'),
                    (14, 1, 'shop', 'product_long', 'Die Foundation hilft Schulen und Ausbildungsinstitutionen seit 10 Jahren sich technisch weiterzuentwickeln und den Lernenden so verbesserte Ausbildungsm&ouml;glichkeiten zu bieten. Durch eine Mitgliedschaft erhalten Sie Rabatte an verschiedenen Firmenanl&auml;ssen der MaxMuster AG.'),
                    (14, 1, 'shop', 'product_keys', ''),
                    (14, 1, 'shop', 'product_code', ''),
                    (14, 1, 'shop', 'product_uri', ''),
                    (5, 1, 'shop', 'manufacturer_name', 'MaxMuster AG'),
                    (5, 1, 'shop', 'manufacturer_uri', ''),
                    (2, 1, 'shop', 'core_mail_template_message_html', '[CUSTOMER_SALUTATION]<br />\r\n<br />\r\nIhre Bestellung wurde ausgef&uuml;hrt. Sie werden in den n&auml;chsten Tagen ihre Lieferung erhalten.<br />\r\n<br />\r\nHerzlichen Dank f&uuml;r das Vertrauen.<br />\r\nWir w&uuml;rden uns freuen, wenn Sie uns weiterempfehlen und w&uuml;nschen Ihnen noch einen sch&ouml;nen Tag.<br />\r\n<br />\r\nMit freundlichen Gr&uuml;ssen<br />\r\nIhr [SHOP_COMPANY] Online Shop Team<br />\r\n<br />\r\n[SHOP_HOMEPAGE]'),
                    (4, 1, 'shop', 'currency_name', 'Euro'),
                    (5, 1, 'shop', 'currency_name', 'United States Dollars')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

         \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_article_group',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_article_group` (`id`)
            VALUES (1)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_attribute',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'type' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'id'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_attribute` (`id`, `type`)
            VALUES (1, 0)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_categories',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'parent_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'ord' => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'parent_id'),
                'active' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'ord'),
                'picture' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'active'),
                'flags' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'picture'),
            ),
            array(
                'flags' => array('fields' => array('flags'), 'type' => 'FULLTEXT'),
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_categories` (`id`, `parent_id`, `ord`, `active`, `picture`, `flags`)
            VALUES  (9, 0, 0, 1, 'htc_one_x_small.jpg', ''),
                    (10, 0, 0, 1, 'contrexx_premium.jpg', ''),
                    (11, 0, 0, 1, 'become_a_member.jpg', '')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

// TODO: This is obsolete in version 3.  Drop!
/*        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_countries',
            array(
                'countries_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'countries_name' => array('type' => 'VARCHAR(64)', 'notnull' => true, 'default' => '', 'after' => 'countries_id'),
                'countries_iso_code_2' => array('type' => 'CHAR(2)', 'notnull' => true, 'default' => '', 'after' => 'countries_name'),
                'countries_iso_code_3' => array('type' => 'CHAR(3)', 'notnull' => true, 'default' => '', 'after' => 'countries_iso_code_2'),
                'activation_status' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'countries_iso_code_3'),
            ),
            array(
                'INDEX_COUNTRIES_NAME' => array('fields' => array('countries_name')),
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_countries` (`countries_id`, `countries_name`, `countries_iso_code_2`, `countries_iso_code_3`, `activation_status`)
            VALUES  (1, 'Afghanistan', 'AF', 'AFG', 0),
                    (2, 'Albania', 'AL', 'ALB', 0),
                    (3, 'Algeria', 'DZ', 'DZA', 0),
                    (4, 'American Samoa', 'AS', 'ASM', 0),
                    (5, 'Andorra', 'AD', 'AND', 0),
                    (6, 'Angola', 'AO', 'AGO', 0),
                    (7, 'Anguilla', 'AI', 'AIA', 0),
                    (8, 'Antarctica', 'AQ', 'ATA', 0),
                    (9, 'Antigua and Barbuda', 'AG', 'ATG', 0),
                    (10, 'Argentina', 'AR', 'ARG', 0),
                    (11, 'Armenia', 'AM', 'ARM', 0),
                    (12, 'Aruba', 'AW', 'ABW', 0),
                    (13, 'Australia', 'AU', 'AUS', 0),
                    (14, 'Österreich', 'AT', 'AUT', 1),
                    (15, 'Azerbaijan', 'AZ', 'AZE', 0),
                    (16, 'Bahamas', 'BS', 'BHS', 0),
                    (17, 'Bahrain', 'BH', 'BHR', 0),
                    (18, 'Bangladesh', 'BD', 'BGD', 0),
                    (19, 'Barbados', 'BB', 'BRB', 0),
                    (20, 'Belarus', 'BY', 'BLR', 0),
                    (21, 'Belgium', 'BE', 'BEL', 0),
                    (22, 'Belize', 'BZ', 'BLZ', 0),
                    (23, 'Benin', 'BJ', 'BEN', 0),
                    (24, 'Bermuda', 'BM', 'BMU', 0),
                    (25, 'Bhutan', 'BT', 'BTN', 0),
                    (26, 'Bolivia', 'BO', 'BOL', 0),
                    (27, 'Bosnia and Herzegowina', 'BA', 'BIH', 0),
                    (28, 'Botswana', 'BW', 'BWA', 0),
                    (29, 'Bouvet Island', 'BV', 'BVT', 0),
                    (30, 'Brazil', 'BR', 'BRA', 0),
                    (31, 'British Indian Ocean Territory', 'IO', 'IOT', 0),
                    (32, 'Brunei Darussalam', 'BN', 'BRN', 0),
                    (33, 'Bulgaria', 'BG', 'BGR', 0),
                    (34, 'Burkina Faso', 'BF', 'BFA', 0),
                    (35, 'Burundi', 'BI', 'BDI', 0),
                    (36, 'Cambodia', 'KH', 'KHM', 0),
                    (37, 'Cameroon', 'CM', 'CMR', 0),
                    (38, 'Canada', 'CA', 'CAN', 0),
                    (39, 'Cape Verde', 'CV', 'CPV', 0),
                    (40, 'Cayman Islands', 'KY', 'CYM', 0),
                    (41, 'Central African Republic', 'CF', 'CAF', 0),
                    (42, 'Chad', 'TD', 'TCD', 0),
                    (43, 'Chile', 'CL', 'CHL', 0),
                    (44, 'China', 'CN', 'CHN', 0),
                    (45, 'Christmas Island', 'CX', 'CXR', 0),
                    (46, 'Cocos (Keeling) Islands', 'CC', 'CCK', 0),
                    (47, 'Colombia', 'CO', 'COL', 0),
                    (48, 'Comoros', 'KM', 'COM', 0),
                    (49, 'Congo', 'CG', 'COG', 0),
                    (50, 'Cook Islands', 'CK', 'COK', 0),
                    (51, 'Costa Rica', 'CR', 'CRI', 0),
                    (52, 'Cote D''Ivoire', 'CI', 'CIV', 0),
                    (53, 'Croatia', 'HR', 'HRV', 0),
                    (54, 'Cuba', 'CU', 'CUB', 0),
                    (55, 'Cyprus', 'CY', 'CYP', 0),
                    (56, 'Czech Republic', 'CZ', 'CZE', 0),
                    (57, 'Denmark', 'DK', 'DNK', 0),
                    (58, 'Djibouti', 'DJ', 'DJI', 0),
                    (59, 'Dominica', 'DM', 'DMA', 0),
                    (60, 'Dominican Republic', 'DO', 'DOM', 0),
                    (61, 'East Timor', 'TP', 'TMP', 0),
                    (62, 'Ecuador', 'EC', 'ECU', 0),
                    (63, 'Egypt', 'EG', 'EGY', 0),
                    (64, 'El Salvador', 'SV', 'SLV', 0),
                    (65, 'Equatorial Guinea', 'GQ', 'GNQ', 0),
                    (66, 'Eritrea', 'ER', 'ERI', 0),
                    (67, 'Estonia', 'EE', 'EST', 0),
                    (68, 'Ethiopia', 'ET', 'ETH', 0),
                    (69, 'Falkland Islands (Malvinas)', 'FK', 'FLK', 0),
                    (70, 'Faroe Islands', 'FO', 'FRO', 0),
                    (71, 'Fiji', 'FJ', 'FJI', 0),
                    (72, 'Finland', 'FI', 'FIN', 0),
                    (73, 'France', 'FR', 'FRA', 0),
                    (74, 'France, Metropolitan', 'FX', 'FXX', 0),
                    (75, 'French Guiana', 'GF', 'GUF', 0),
                    (76, 'French Polynesia', 'PF', 'PYF', 0),
                    (77, 'French Southern Territories', 'TF', 'ATF', 0),
                    (78, 'Gabon', 'GA', 'GAB', 0),
                    (79, 'Gambia', 'GM', 'GMB', 0),
                    (80, 'Georgia', 'GE', 'GEO', 0),
                    (81, 'Deutschland', 'DE', 'DEU', 1),
                    (82, 'Ghana', 'GH', 'GHA', 0),
                    (83, 'Gibraltar', 'GI', 'GIB', 0),
                    (84, 'Greece', 'GR', 'GRC', 0),
                    (85, 'Greenland', 'GL', 'GRL', 0),
                    (86, 'Grenada', 'GD', 'GRD', 0),
                    (87, 'Guadeloupe', 'GP', 'GLP', 0),
                    (88, 'Guam', 'GU', 'GUM', 0),
                    (89, 'Guatemala', 'GT', 'GTM', 0),
                    (90, 'Guinea', 'GN', 'GIN', 0),
                    (91, 'Guinea-bissau', 'GW', 'GNB', 0),
                    (92, 'Guyana', 'GY', 'GUY', 0),
                    (93, 'Haiti', 'HT', 'HTI', 0),
                    (94, 'Heard and Mc Donald Islands', 'HM', 'HMD', 0),
                    (95, 'Honduras', 'HN', 'HND', 0),
                    (96, 'Hong Kong', 'HK', 'HKG', 0),
                    (97, 'Hungary', 'HU', 'HUN', 0),
                    (98, 'Iceland', 'IS', 'ISL', 0),
                    (99, 'India', 'IN', 'IND', 0),
                    (100, 'Indonesia', 'ID', 'IDN', 0),
                    (101, 'Iran (Islamic Republic of)', 'IR', 'IRN', 0),
                    (102, 'Iraq', 'IQ', 'IRQ', 0),
                    (103, 'Ireland', 'IE', 'IRL', 0),
                    (104, 'Israel', 'IL', 'ISR', 0),
                    (105, 'Italy', 'IT', 'ITA', 0),
                    (106, 'Jamaica', 'JM', 'JAM', 0),
                    (107, 'Japan', 'JP', 'JPN', 0),
                    (108, 'Jordan', 'JO', 'JOR', 0),
                    (109, 'Kazakhstan', 'KZ', 'KAZ', 0),
                    (110, 'Kenya', 'KE', 'KEN', 0),
                    (111, 'Kiribati', 'KI', 'KIR', 0),
                    (112, 'Korea, Democratic People''s Republic of', 'KP', 'PRK', 0),
                    (113, 'Korea, Republic of', 'KR', 'KOR', 0),
                    (114, 'Kuwait', 'KW', 'KWT', 0),
                    (115, 'Kyrgyzstan', 'KG', 'KGZ', 0),
                    (116, 'Lao People''s Democratic Republic', 'LA', 'LAO', 0),
                    (117, 'Latvia', 'LV', 'LVA', 0),
                    (118, 'Lebanon', 'LB', 'LBN', 0),
                    (119, 'Lesotho', 'LS', 'LSO', 0),
                    (120, 'Liberia', 'LR', 'LBR', 0),
                    (121, 'Libyan Arab Jamahiriya', 'LY', 'LBY', 0),
                    (122, 'Liechtenstein', 'LI', 'LIE', 1),
                    (123, 'Lithuania', 'LT', 'LTU', 0),
                    (124, 'Luxembourg', 'LU', 'LUX', 0),
                    (125, 'Macau', 'MO', 'MAC', 0),
                    (126, 'Macedonia, The Former Yugoslav Republic of', 'MK', 'MKD', 0),
                    (127, 'Madagascar', 'MG', 'MDG', 0),
                    (128, 'Malawi', 'MW', 'MWI', 0),
                    (129, 'Malaysia', 'MY', 'MYS', 0),
                    (130, 'Maldives', 'MV', 'MDV', 0),
                    (131, 'Mali', 'ML', 'MLI', 0),
                    (132, 'Malta', 'MT', 'MLT', 0),
                    (133, 'Marshall Islands', 'MH', 'MHL', 0),
                    (134, 'Martinique', 'MQ', 'MTQ', 0),
                    (135, 'Mauritania', 'MR', 'MRT', 0),
                    (136, 'Mauritius', 'MU', 'MUS', 0),
                    (137, 'Mayotte', 'YT', 'MYT', 0),
                    (138, 'Mexico', 'MX', 'MEX', 0),
                    (139, 'Micronesia, Federated States of', 'FM', 'FSM', 0),
                    (140, 'Moldova, Republic of', 'MD', 'MDA', 0),
                    (141, 'Monaco', 'MC', 'MCO', 0),
                    (142, 'Mongolia', 'MN', 'MNG', 0),
                    (143, 'Montserrat', 'MS', 'MSR', 0),
                    (144, 'Morocco', 'MA', 'MAR', 0),
                    (145, 'Mozambique', 'MZ', 'MOZ', 0),
                    (146, 'Myanmar', 'MM', 'MMR', 0),
                    (147, 'Namibia', 'NA', 'NAM', 0),
                    (148, 'Nauru', 'NR', 'NRU', 0),
                    (149, 'Nepal', 'NP', 'NPL', 0),
                    (150, 'Netherlands', 'NL', 'NLD', 0),
                    (151, 'Netherlands Antilles', 'AN', 'ANT', 0),
                    (152, 'New Caledonia', 'NC', 'NCL', 0),
                    (153, 'New Zealand', 'NZ', 'NZL', 0),
                    (154, 'Nicaragua', 'NI', 'NIC', 0),
                    (155, 'Niger', 'NE', 'NER', 0),
                    (156, 'Nigeria', 'NG', 'NGA', 0),
                    (157, 'Niue', 'NU', 'NIU', 0),
                    (158, 'Norfolk Island', 'NF', 'NFK', 0),
                    (159, 'Northern Mariana Islands', 'MP', 'MNP', 0),
                    (160, 'Norway', 'NO', 'NOR', 0),
                    (161, 'Oman', 'OM', 'OMN', 0),
                    (162, 'Pakistan', 'PK', 'PAK', 0),
                    (163, 'Palau', 'PW', 'PLW', 0),
                    (164, 'Panama', 'PA', 'PAN', 0),
                    (165, 'Papua New Guinea', 'PG', 'PNG', 0),
                    (166, 'Paraguay', 'PY', 'PRY', 0),
                    (167, 'Peru', 'PE', 'PER', 0),
                    (168, 'Philippines', 'PH', 'PHL', 0),
                    (169, 'Pitcairn', 'PN', 'PCN', 0),
                    (170, 'Poland', 'PL', 'POL', 0),
                    (171, 'Portugal', 'PT', 'PRT', 0),
                    (172, 'Puerto Rico', 'PR', 'PRI', 0),
                    (173, 'Qatar', 'QA', 'QAT', 0),
                    (174, 'Reunion', 'RE', 'REU', 0),
                    (175, 'Romania', 'RO', 'ROM', 0),
                    (176, 'Russian Federation', 'RU', 'RUS', 0),
                    (177, 'Rwanda', 'RW', 'RWA', 0),
                    (178, 'Saint Kitts and Nevis', 'KN', 'KNA', 0),
                    (179, 'Saint Lucia', 'LC', 'LCA', 0),
                    (180, 'Saint Vincent and the Grenadines', 'VC', 'VCT', 0),
                    (181, 'Samoa', 'WS', 'WSM', 0),
                    (182, 'San Marino', 'SM', 'SMR', 0),
                    (183, 'Sao Tome and Principe', 'ST', 'STP', 0),
                    (184, 'Saudi Arabia', 'SA', 'SAU', 0),
                    (185, 'Senegal', 'SN', 'SEN', 0),
                    (186, 'Seychelles', 'SC', 'SYC', 0),
                    (187, 'Sierra Leone', 'SL', 'SLE', 0),
                    (188, 'Singapore', 'SG', 'SGP', 0),
                    (189, 'Slovakia (Slovak Republic)', 'SK', 'SVK', 0),
                    (190, 'Slovenia', 'SI', 'SVN', 0),
                    (191, 'Solomon Islands', 'SB', 'SLB', 0),
                    (192, 'Somalia', 'SO', 'SOM', 0),
                    (193, 'South Africa', 'ZA', 'ZAF', 0),
                    (194, 'South Georgia and the South Sandwich Islands', 'GS', 'SGS', 0),
                    (195, 'Spain', 'ES', 'ESP', 0),
                    (196, 'Sri Lanka', 'LK', 'LKA', 0),
                    (197, 'St. Helena', 'SH', 'SHN', 0),
                    (198, 'St. Pierre and Miquelon', 'PM', 'SPM', 0),
                    (199, 'Sudan', 'SD', 'SDN', 0),
                    (200, 'Suriname', 'SR', 'SUR', 0),
                    (201, 'Svalbard and Jan Mayen Islands', 'SJ', 'SJM', 0),
                    (202, 'Swaziland', 'SZ', 'SWZ', 0),
                    (203, 'Sweden', 'SE', 'SWE', 0),
                    (204, 'Schweiz', 'CH', 'CHE', 1),
                    (205, 'Syrian Arab Republic', 'SY', 'SYR', 0),
                    (206, 'Taiwan', 'TW', 'TWN', 0),
                    (207, 'Tajikistan', 'TJ', 'TJK', 0),
                    (208, 'Tanzania, United Republic of', 'TZ', 'TZA', 0),
                    (209, 'Thailand', 'TH', 'THA', 0),
                    (210, 'Togo', 'TG', 'TGO', 0),
                    (211, 'Tokelau', 'TK', 'TKL', 0),
                    (212, 'Tonga', 'TO', 'TON', 0),
                    (213, 'Trinidad and Tobago', 'TT', 'TTO', 0),
                    (214, 'Tunisia', 'TN', 'TUN', 0),
                    (215, 'Turkey', 'TR', 'TUR', 0),
                    (216, 'Turkmenistan', 'TM', 'TKM', 0),
                    (217, 'Turks and Caicos Islands', 'TC', 'TCA', 0),
                    (218, 'Tuvalu', 'TV', 'TUV', 0),
                    (219, 'Uganda', 'UG', 'UGA', 0),
                    (220, 'Ukraine', 'UA', 'UKR', 0),
                    (221, 'United Arab Emirates', 'AE', 'ARE', 0),
                    (222, 'United Kingdom', 'GB', 'GBR', 0),
                    (223, 'United States', 'US', 'USA', 0),
                    (224, 'United States Minor Outlying Islands', 'UM', 'UMI', 0),
                    (225, 'Uruguay', 'UY', 'URY', 0),
                    (226, 'Uzbekistan', 'UZ', 'UZB', 0),
                    (227, 'Vanuatu', 'VU', 'VUT', 0),
                    (228, 'Vatican City State (Holy See)', 'VA', 'VAT', 0),
                    (229, 'Venezuela', 'VE', 'VEN', 0),
                    (230, 'Viet Nam', 'VN', 'VNM', 0),
                    (231, 'Virgin Islands (British)', 'VG', 'VGB', 0),
                    (232, 'Virgin Islands (U.S.)', 'VI', 'VIR', 0),
                    (233, 'Wallis and Futuna Islands', 'WF', 'WLF', 0),
                    (234, 'Western Sahara', 'EH', 'ESH', 0),
                    (235, 'Yemen', 'YE', 'YEM', 0),
                    (236, 'Yugoslavia', 'YU', 'YUG', 0),
                    (237, 'Zaire', 'ZR', 'ZAR', 0),
                    (238, 'Zambia', 'ZM', 'ZMB', 0),
                    (239, 'Zimbabwe', 'ZW', 'ZWE', 0)
            ON DUPLICATE KEY UPDATE `countries_id` = `countries_id`
        ");*/

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_currencies',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'code' => array('type' => 'CHAR(3)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'symbol' => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => '', 'after' => 'code'),
                'rate' => array('type' => 'DECIMAL(10,6)', 'unsigned' => true, 'notnull' => true, 'default' => '1.000000', 'after' => 'symbol'),
                'ord' => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'rate'),
                'active' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'ord'),
                'default' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'active'),
                'increment' => array('type' => 'DECIMAL(6,5)', 'unsigned' => true, 'notnull' => true, 'default' => '0.01', 'after' => 'default'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_currencies` (`id`, `code`, `symbol`, `rate`, `ord`, `active`, `default`, `increment`)
            VALUES  (1, 'CHF', 'CHF', '1.000000', 1, 1, 1, '0.05'),
                    (2, 'EUR', '€', '0.830000', 2, 1, 0, '0.01'),
                    (3, 'USD', 'USD', '1.050000', 0, 1, 0, '0.01')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_customer_group',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_customer_group` (`id`)
            VALUES  (1),
                    (2),
                    (3)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_discount_coupon',
            array(
                'code' => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => '', 'primary' => true),
                'customer_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'code'),
                'payment_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'customer_id'),
                'product_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'payment_id'),
                'start_time' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'product_id'),
                'end_time' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'start_time'),
                'uses' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'end_time'),
                'global' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'uses'),
                'minimum_amount' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'notnull' => true, 'default' => '0.00', 'after' => 'global'),
                'discount_amount' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'notnull' => true, 'default' => '0.00', 'after' => 'minimum_amount'),
                'discount_rate' => array('type' => 'DECIMAL(3,0)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'discount_amount'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_discount_coupon` (`code`, `customer_id`, `payment_id`, `product_id`, `start_time`, `end_time`, `uses`, `global`, `minimum_amount`, `discount_amount`, `discount_rate`)
            VALUES ('contrexx', 0, 0, 0, 1336946400, 0, 1410065408, 1, '0.00', '0.00', '10')
            ON DUPLICATE KEY UPDATE `code` = `code`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_discountgroup_count_name',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_discountgroup_count_name` (`id`)
            VALUES (1)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_discountgroup_count_rate',
            array(
                'group_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'count' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'primary' => true, 'after' => 'group_id'),
                'rate' => array('type' => 'DECIMAL(5,2)', 'unsigned' => true, 'notnull' => true, 'default' => '0.00', 'after' => 'count'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_discountgroup_count_rate` (`group_id`, `count`, `rate`)
            VALUES  (1, 5, '5.00'),
                    (1, 25, '10.00'),
                    (1, 100, '15.00')
            ON DUPLICATE KEY UPDATE `group_id` = `group_id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_importimg',
            array(
                'img_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'img_name' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'img_id'),
                'img_cats' => array('type' => 'text', 'after' => 'img_name'),
                'img_fields_file' => array('type' => 'text', 'after' => 'img_cats'),
                'img_fields_db' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'img_fields_file'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_lsv',
            array(
                'order_id' => array('type' => 'INT(10)', 'unsigned' => true, 'primary' => true),
                'holder' => array('type' => 'tinytext', 'after' => 'order_id'),
                'bank' => array('type' => 'tinytext', 'after' => 'holder'),
                'blz' => array('type' => 'tinytext', 'after' => 'bank'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );

// TODO: Obsolete in version 3.  Drop!
/*        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_mail',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'tplname' => array('type' => 'VARCHAR(60)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'protected' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'tplname'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_mail` (`id`, `tplname`, `protected`)
            VALUES  (1, 'Bestellungsbestätigung', 1),
                    (2, 'Auftrag abgeschlossen', 1),
                    (3, 'Logindaten', 1),
                    (4, 'Bestellungsbestätigung mit Zugangsdaten', 1)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_mail_content',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'tpl_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'lang_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'tpl_id'),
                'from_mail' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'lang_id'),
                'xsender' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'from_mail'),
                'subject' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'xsender'),
                'message' => array('type' => 'text', 'after' => 'subject'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_mail_content` (`id`, `tpl_id`, `lang_id`, `from_mail`, `xsender`, `subject`, `message`)
            VALUES  (1, 1, 1, 'info@example.com', 'Contrexx-Demo Online Shop', 'Contrexx Auftragsbestätigung vom <DATE>', 'Sehr geehrte(r) <CUSTOMER_PREFIX> <CUSTOMER_LASTNAME>\r\n\r\nHerzlichen Dank für Ihre Bestellung im Contrexx Demo Online Store.\r\n\r\nIhre Auftrags-Nr. lautet: <ORDER_ID>\r\nIhre Kunden-Nr. lautet: <CUSTOMER_ID>\r\nBestellungszeit: <ORDER_TIME>\r\n\r\n<ORDER_DATA>\r\n\r\nIhre Kundenadresse:\r\n<CUSTOMER_COMPANY>\r\n<CUSTOMER_PREFIX> <CUSTOMER_FIRSTNAME> <CUSTOMER_LASTNAME>\r\n<CUSTOMER_ADDRESS>\r\n<CUSTOMER_ZIP> <CUSTOMER_CITY>\r\n<CUSTOMER_COUNTRY>\r\n\r\n\r\nLieferadresse:\r\n<SHIPPING_COMPANY>\r\n<SHIPPING_PREFIX> <SHIPPING_FIRSTNAME> <SHIPPING_LASTNAME>\r\n<SHIPPING_ADDRESS>\r\n<SHIPPING_ZIP> <SHIPPING_CITY>\r\n<SHIPPING_COUNTRY>\r\n\r\nIhr Link zum Online Store: http://www.contrexx.com/\r\n\r\nIhre Zugangsdaten zum Shop:\r\nBenutzername: <USERNAME>\r\nPasswort: <PASSWORD>\r\n\r\nWir freuen uns auf Ihren nächsten Besuch im Online Store und\r\nwünschen Ihnen noch einen schönen Tag.\r\n\r\nP.S. Diese Auftragsbestätigung wurde gesendet an: <CUSTOMER_EMAIL>\r\n\r\nMit freundlichen Grüssen\r\nIhr Contrexx Demo Online Store Team\r\n\r\nhttp://www.contrexx.com/\r\n'),
                    (2, 2, 1, 'info@example.com', 'Contrexx Demo Shop', 'Ihre Bestellung wurde am <DATE> ausgeführt', 'Sehr geehrte(r) <CUSTOMER_PREFIX> <CUSTOMER_LASTNAME>\r\n\r\nIhre Bestellung wurde ausgeführt. Sie werden in den nächsten Tagen ihre Lieferung erhalten.\r\n\r\nMit freundlichen Grüssen\r\nIhr Contrexx Demo Online Store Team\r\n\r\nhttp://www.contrexx.com/\r\n'),
                    (3, 3, 1, 'info@example.com', 'Contrexx Demo Shop', 'Logindaten für  Contrexx Demo Shop', 'Sehr geehrte(r) <CUSTOMER_PREFIX> <CUSTOMER_LASTNAME>\r\n\r\nHier Ihre Zugangsdaten zum Shop:\r\nBenutzername: <USERNAME>\r\nPasswort: <PASSWORD>\r\n\r\nMit freundlichen Grüssen\r\nIhr Contrexx Demo Online Store Team\r\n\r\nhttp://www.contrexx.com/\r\n'),
                    (4, 4, 1, 'info@example.com', 'Contrexx Demo Online Shop', 'Contrexx Auftragsbestätigung und Zugangsdaten vom <DATE>', 'Sehr geehrte(r) <CUSTOMER_PREFIX> <CUSTOMER_LASTNAME>\r\n\r\nHerzlichen Dank für Ihre Bestellung im Contrexx Demo Online Store.\r\n\r\nIhre Auftrags-Nr. lautet: <ORDER_ID>\r\nIhre Kunden-Nr. lautet: <CUSTOMER_ID>\r\nBestellungszeit: <ORDER_TIME>\r\n\r\n<ORDER_DATA>\r\n<LOGIN_DATA>\r\n\r\nIhre Kundenadresse:\r\n<CUSTOMER_COMPANY>\r\n<CUSTOMER_PREFIX> <CUSTOMER_FIRSTNAME> <CUSTOMER_LASTNAME>\r\n<CUSTOMER_ADDRESS>\r\n<CUSTOMER_ZIP> <CUSTOMER_CITY>\r\n<CUSTOMER_COUNTRY>\r\n\r\nLieferadresse:\r\n<SHIPPING_COMPANY>\r\n<SHIPPING_PREFIX> <SHIPPING_FIRSTNAME> <SHIPPING_LASTNAME>\r\n<SHIPPING_ADDRESS>\r\n<SHIPPING_ZIP> <SHIPPING_CITY>\r\n<SHIPPING_COUNTRY>\r\n\r\n\r\nIhre Zugangsdaten zum Shop:\r\nBenutzername: <USERNAME>\r\nPasswort: <PASSWORD>\r\n\r\nWir freuen uns auf Ihren nächsten Besuch im Online Store und\r\nwünschen Ihnen noch einen schönen Tag.\r\n\r\nP.S. Diese Auftragsbestätigung wurde gesendet an: <CUSTOMER_EMAIL>\r\n\r\nMit freundlichen Grüssen\r\nIhr Contrexx Demo Online Store Team\r\n\r\nhttp://www.contrexx.com/\r\n'),
                    (5, 1, 2, 'info@example.com', 'Contrexx-Demo Online Shop', 'Contrexx Order Confirmation, <DATE>', 'Dear <CUSTOMER_PREFIX> <CUSTOMER_LASTNAME>\r\n\r\nThank you for your order at the Contrexx Demo Online Store.\r\n\r\nYour Order-Nr: <ORDER_ID>\r\nYour Customer-Nr: <CUSTOMER_ID>\r\nOrder Time: <ORDER_TIME>\r\n\r\n<ORDER_DATA>\r\n\r\nYour Address:\r\n<CUSTOMER_COMPANY>\r\n<CUSTOMER_PREFIX> <CUSTOMER_FIRSTNAME> <CUSTOMER_LASTNAME>\r\n<CUSTOMER_ADDRESS>\r\n<CUSTOMER_ZIP> <CUSTOMER_CITY>\r\n<CUSTOMER_COUNTRY>\r\n\r\n\r\nYour Shipping Address:\r\n<SHIPPING_COMPANY>\r\n<SHIPPING_PREFIX> <SHIPPING_FIRSTNAME> <SHIPPING_LASTNAME>\r\n<SHIPPING_ADDRESS>\r\n<SHIPPING_ZIP> <SHIPPING_CITY>\r\n<SHIPPING_COUNTRY>\r\n\r\nYour link to the Online Store: http://www.contrexx.com/\r\n\r\nYour account data:\r\nUsername: <USERNAME>\r\nPassword: <PASSWORD>\r\n\r\nWe''re looking forward to your next visit in our Online Shop.\r\nHave a nice day!\r\n\r\nP.S. This order confirmation was sent to: <CUSTOMER_EMAIL>\r\n\r\nYours sincerely,\r\nContrexx Demo Online Store Team\r\n\r\nhttp://www.contrexx.com/\r\n'),
                    (6, 2, 3, 'info@example.com', 'Contrexx Demo Shop', 'Ihre Bestellung wurde am <DATE> ausgeführt', 'Sehr geehrte(r) <CUSTOMER_PREFIX> <CUSTOMER_LASTNAME>\r\n\r\nIhre Bestellung wurde ausgeführt. Sie werden in den nächsten Tagen ihre Lieferung erhalten.\r\n\r\nMit freundlichen Grüssen\r\nIhr Contrexx Demo Online Store Team\r\n\r\nhttp://www.contrexx.com/\r\n'),
                    (7, 2, 2, 'info@example.com', 'Contrexx Demo Shop', 'Your order has been processed on the <DATE>', 'Dear <CUSTOMER_PREFIX> <CUSTOMER_LASTNAME>\r\n\r\nYour order has been processed. The shipment will arrive in the next few days.\r\n\r\nYours sincerely\r\nContrexx Demo Online Store Team\r\n\r\nhttp://www.contrexx.com/\r\n'),
                    (8, 1, 3, 'info@example.com', 'Contrexx-Demo Online Shop', 'Contrexx Auftragsbestätigung vom <DATE>', 'Sehr geehrte(r) <CUSTOMER_PREFIX> <CUSTOMER_LASTNAME>\r\n\r\nHerzlichen Dank für Ihre Bestellung im Contrexx Demo Online Store.\r\n\r\nIhre Auftrags-Nr. lautet: <ORDER_ID>\r\nIhre Kunden-Nr. lautet: <CUSTOMER_ID>\r\nBestellungszeit: <ORDER_TIME>\r\n\r\n<ORDER_DATA>\r\n\r\nIhre Kundenadresse:\r\n<CUSTOMER_COMPANY>\r\n<CUSTOMER_PREFIX> <CUSTOMER_FIRSTNAME> <CUSTOMER_LASTNAME>\r\n<CUSTOMER_ADDRESS>\r\n<CUSTOMER_ZIP> <CUSTOMER_CITY>\r\n<CUSTOMER_COUNTRY>\r\n\r\n\r\nLieferadresse:\r\n<SHIPPING_COMPANY>\r\n<SHIPPING_PREFIX> <SHIPPING_FIRSTNAME> <SHIPPING_LASTNAME>\r\n<SHIPPING_ADDRESS>\r\n<SHIPPING_ZIP> <SHIPPING_CITY>\r\n<SHIPPING_COUNTRY>\r\n\r\nIhr Link zum Online Store: http://www.contrexx.com/\r\n\r\nIhre Zugangsdaten zum Shop:\r\nBenutzername: <USERNAME>\r\nPasswort: <PASSWORD>\r\n\r\nWir freuen uns auf Ihren nächsten Besuch im Online Store und\r\nwünschen Ihnen noch einen schönen Tag.\r\n\r\nP.S. Diese Auftragsbestätigung wurde gesendet an: <CUSTOMER_EMAIL>\r\n\r\nMit freundlichen Grüssen\r\nIhr Contrexx Demo Online Store Team\r\n\r\nhttp://www.contrexx.com/\r\n'),
                    (9, 3, 2, 'info@example.com', 'Contrexx Demo Shop', 'Account data for Contrexx Demo Shop', 'Dear <CUSTOMER_PREFIX> <CUSTOMER_LASTNAME>\r\n\r\nYour account to the shop:\r\nUsername: <USERNAME>\r\nPassword: <PASSWORD>\r\n\r\nYours sincerely\r\nContrexx Demo Online Store Team\r\n\r\nhttp://www.contrexx.com/\r\n'),
                    (10, 3, 3, 'info@example.com', 'Contrexx Demo Shop', 'Logindaten für  Contrexx Demo Shop', 'Sehr geehrte(r) <CUSTOMER_PREFIX> <CUSTOMER_LASTNAME>\r\n\r\nHier Ihre Zugangsdaten zum Shop:\r\nBenutzername: <USERNAME>\r\nPasswort: <PASSWORD>\r\n\r\nMit freundlichen Grüssen\r\nIhr Contrexx Demo Online Store Team\r\n\r\nhttp://www.contrexx.com/\r\n'),
                    (11, 4, 2, 'info@example.com', 'Contrexx Demo Online Shop', 'Contrexx Order Confiration with Account, <DATE>', 'Dear <CUSTOMER_PREFIX> <CUSTOMER_LASTNAME>\r\n\r\nThank you for your order at the Contrexx Demo Online Store.\r\n\r\nYour Order-Nr: <ORDER_ID>\r\nYour Customer-Nr: <CUSTOMER_ID>\r\nOrder Time: <ORDER_TIME>\r\n\r\n<ORDER_DATA>\r\n<LOGIN_DATA>\r\n\r\nYour Address:\r\n<CUSTOMER_COMPANY>\r\n<CUSTOMER_PREFIX> <CUSTOMER_FIRSTNAME> <CUSTOMER_LASTNAME>\r\n<CUSTOMER_ADDRESS>\r\n<CUSTOMER_ZIP> <CUSTOMER_CITY>\r\n<CUSTOMER_COUNTRY>\r\n\r\n\r\nYour Shipping Address:\r\n<SHIPPING_COMPANY>\r\n<SHIPPING_PREFIX> <SHIPPING_FIRSTNAME> <SHIPPING_LASTNAME>\r\n<SHIPPING_ADDRESS>\r\n<SHIPPING_ZIP> <SHIPPING_CITY>\r\n<SHIPPING_COUNTRY>\r\n\r\n\r\nYour account data:\r\nUsername: <USERNAME>\r\nPassword: <PASSWORD>\r\n\r\nWe''re looking forward to your next visit in our Online Shop.\r\nHave a nice day!\r\n\r\nP.S. This order confirmation was sent to: <CUSTOMER_EMAIL>\r\n\r\nYours sincerely,\r\nContrexx Demo Online Store Team\r\n\r\nhttp://www.contrexx.com/\r\n'),
                    (12, 4, 3, 'info@example.com', 'Contrexx Demo Online Shop', 'Contrexx Auftragsbestätigung und Zugangsdaten vom <DATE>', 'Sehr geehrte(r) <CUSTOMER_PREFIX> <CUSTOMER_LASTNAME>\r\n\r\nHerzlichen Dank für Ihre Bestellung im Contrexx Demo Online Store.\r\n\r\nIhre Auftrags-Nr. lautet: <ORDER_ID>\r\nIhre Kunden-Nr. lautet: <CUSTOMER_ID>\r\nBestellungszeit: <ORDER_TIME>\r\n\r\n<ORDER_DATA>\r\n<LOGIN_DATA>\r\n\r\nIhre Kundenadresse:\r\n<CUSTOMER_COMPANY>\r\n<CUSTOMER_PREFIX> <CUSTOMER_FIRSTNAME> <CUSTOMER_LASTNAME>\r\n<CUSTOMER_ADDRESS>\r\n<CUSTOMER_ZIP> <CUSTOMER_CITY>\r\n<CUSTOMER_COUNTRY>\r\n\r\nLieferadresse:\r\n<SHIPPING_COMPANY>\r\n<SHIPPING_PREFIX> <SHIPPING_FIRSTNAME> <SHIPPING_LASTNAME>\r\n<SHIPPING_ADDRESS>\r\n<SHIPPING_ZIP> <SHIPPING_CITY>\r\n<SHIPPING_COUNTRY>\r\n\r\n\r\nIhre Zugangsdaten zum Shop:\r\nBenutzername: <USERNAME>\r\nPasswort: <PASSWORD>\r\n\r\nWir freuen uns auf Ihren nächsten Besuch im Online Store und\r\nwünschen Ihnen noch einen schönen Tag.\r\n\r\nP.S. Diese Auftragsbestätigung wurde gesendet an: <CUSTOMER_EMAIL>\r\n\r\nMit freundlichen Grüssen\r\nIhr Contrexx Demo Online Store Team\r\n\r\nhttp://www.contrexx.com/\r\n')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");*/

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_manufacturer',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_manufacturer` (`id`)
            VALUES  (1),
                    (2),
                    (3),
                    (4),
                    (5)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_option',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'attribute_id' => array('type' => 'INT(10)', 'unsigned' => true, 'after' => 'id'),
                'price' => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00', 'after' => 'attribute_id'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_option` (`id`, `attribute_id`, `price`)
            VALUES  (1, 1, '19.00'),
                    (2, 1, '400.00'),
                    (3, 1, '19.90')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_order_attributes',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'item_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'attribute_name' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'item_id'),
                'option_name' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'attribute_name'),
                'price' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'notnull' => true, 'default' => '0.00', 'after' => 'option_name'),
            ),
            array(
                'item_id' => array('fields' => array('item_id')),
            ),
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_order_items',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'order_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'product_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'order_id'),
                'product_name' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'product_id'),
                'price' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'notnull' => true, 'default' => '0.00', 'after' => 'product_name'),
                'quantity' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'price'),
                'vat_rate' => array('type' => 'DECIMAL(5,2)', 'unsigned' => true, 'notnull' => false, 'after' => 'quantity'),
                'weight' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'vat_rate'),
            ),
            array(
                'order' => array('fields' => array('order_id')),
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`, `vat_rate`, `weight`)
            VALUES  (1, 1, 10, 'Lorem ipsum2', '1800.00', 1, '0.00', 0),
                    (2, 2, 10, 'Lorem ipsum2', '1800.00', 1, '0.00', 0),
                    (3, 3, 10, 'Lorem ipsum2', '1800.00', 1, '0.00', 0),
                    (4, 4, 10, 'Lorem ipsum2', '1800.00', 1, '0.00', 0)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_orders',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'customer_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'currency_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'customer_id'),
                'sum' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'notnull' => true, 'default' => '0.00', 'after' => 'currency_id'),
                'date_time' => array('type' => 'timestamp', 'notnull' => true, 'default' => '0000-00-00 00:00:00', 'after' => 'sum'),
                'status' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'date_time'),
                'gender' => array('type' => 'VARCHAR(50)', 'notnull' => false, 'after' => 'status'),
                'company' => array('type' => 'VARCHAR(100)', 'notnull' => false, 'after' => 'gender'),
                'firstname' => array('type' => 'VARCHAR(40)', 'notnull' => false, 'after' => 'company'),
                'lastname' => array('type' => 'VARCHAR(100)', 'notnull' => false, 'after' => 'firstname'),
                'address' => array('type' => 'VARCHAR(40)', 'notnull' => false, 'after' => 'lastname'),
                'city' => array('type' => 'VARCHAR(50)', 'notnull' => false, 'after' => 'address'),
                'zip' => array('type' => 'VARCHAR(10)', 'notnull' => false, 'after' => 'city'),
                'country_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'after' => 'zip'),
                'phone' => array('type' => 'VARCHAR(20)', 'notnull' => false, 'after' => 'country_id'),
                'vat_amount' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'notnull' => true, 'default' => '0.00', 'after' => 'phone'),
                'shipment_amount' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'notnull' => true, 'default' => '0.00', 'after' => 'vat_amount'),
                'shipment_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'after' => 'shipment_amount'),
                'payment_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'shipment_id'),
                'payment_amount' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'notnull' => true, 'default' => '0.00', 'after' => 'payment_id'),
                'ip' => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => '', 'after' => 'payment_amount'),
                'host' => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'ip'),
                'lang_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'host'),
                'browser' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'lang_id'),
                'note' => array('type' => 'text', 'after' => 'browser'),
                'modified_on' => array('type' => 'timestamp', 'notnull' => false, 'default' => NULL, 'after' => 'note'),
                'modified_by' => array('type' => 'VARCHAR(50)', 'notnull' => false, 'after' => 'modified_on'),
                'billing_gender' => array('type' => 'VARCHAR(50)', 'notnull' => false, 'after' => 'modified_by'),
                'billing_company' => array('type' => 'VARCHAR(100)', 'notnull' => false, 'after' => 'billing_gender'),
                'billing_firstname' => array('type' => 'VARCHAR(40)', 'notnull' => false, 'after' => 'billing_company'),
                'billing_lastname' => array('type' => 'VARCHAR(100)', 'notnull' => false, 'after' => 'billing_firstname'),
                'billing_address' => array('type' => 'VARCHAR(40)', 'notnull' => false, 'after' => 'billing_lastname'),
                'billing_city' => array('type' => 'VARCHAR(50)', 'notnull' => false, 'after' => 'billing_address'),
                'billing_zip' => array('type' => 'VARCHAR(10)', 'notnull' => false, 'after' => 'billing_city'),
                'billing_country_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'after' => 'billing_zip'),
                'billing_phone' => array('type' => 'VARCHAR(20)', 'notnull' => false, 'after' => 'billing_country_id'),
                'billing_fax' => array('type' => 'VARCHAR(20)', 'notnull' => false, 'after' => 'billing_phone'),
                'billing_email' => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'billing_fax'),
            ),
            array(
                'status' => array('fields' => array('status')),
            ),
            'MyISAM',
            'cx3upgrade'
        );
// TODO:  This dataset is crap!
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_orders` (`id`, `customer_id`, `currency_id`, `sum`, `date_time`, `status`, `gender`, `company`, `firstname`, `lastname`, `address`, `city`, `zip`, `country_id`, `phone`, `vat_amount`, `shipment_amount`, `shipment_id`, `payment_id`, `payment_amount`, `ip`, `host`, `lang_id`, `browser`, `note`, `modified_on`, `modified_by`, `billing_gender`, `billing_company`, `billing_firstname`, `billing_lastname`, `billing_address`, `billing_city`, `billing_zip`, `billing_country_id`, `billing_phone`, `billing_fax`, `billing_email`)
            VALUES  (1, 2, 1, '1802.00', '2012-07-09 06:07:38', 0, 'gender_male', 'ss4u', 'Tim', 'Alexander', '12, Cherooke', 'Louisville', '988011', 14, '81777202', '0.00', '0.00', 2, 12, '2.00', '122.165.78.217', 'ABTS-TN-Static-217.78.165.122.airtelbroadband.in', 2, 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:10.0) Gecko/20100101 Firefox/10.0', 'none', NULL, NULL, 'gender_male', 'ss4u', 'Tim', 'Alexander', '12, Cherooke', 'Louisville', '988011', 14, '81777202', '2341244', ''),
                    (2, 3, 1, '1802.00', '2012-07-09 07:50:59', 0, 'gender_male', 'adasfd', 'sdfsdf', 'sdafsf', 'sdfsdf', 'sdfsdf', 'sdfsd', 81, '65844548', '0.00', '0.00', 1, 2, '2.00', '122.165.78.217', 'ABTS-TN-Static-217.78.165.122.airtelbroadband.in', 2, 'Mozilla/5.0 (Windows NT 6.1; rv:13.0) Gecko/20100101 Firefox/13.0.1', 'safsdf', NULL, NULL, 'gender_male', 'adasfd', 'sdfsdf', 'sdafsf', 'sdfsdf', 'sdfsdf', 'sdfsd', 81, '65844548', '87845445', ''),
                    (3, 4, 1, '1802.00', '2012-07-09 07:59:47', 0, 'gender_female', 'ss', 'rebecca', 'stone', 'Bright street', 'bern', '123131234', 204, '1243141112', '0.00', '0.00', 1, 12, '2.00', '122.165.78.217', 'ABTS-TN-Static-217.78.165.122.airtelbroadband.in', 2, 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:10.0) Gecko/20100101 Firefox/10.0', '', NULL, NULL, 'gender_female', 'ss', 'rebecca', 'stone', 'Bright street', 'bern', '123131234', 204, '1243141112', '', ''),
                    (4, 1, 1, '1802.00', '2012-07-10 06:17:28', 0, 'gender_male', 'company', 'CMS', 'System Benutzer', 'address', 'city', 'zip', 204, 'phone', '0.00', '0.00', 1, 2, '2.00', '122.165.73.242', 'ABTS-TN-Static-242.73.165.122.airtelbroadband.in', 2, 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:10.0) Gecko/20100101 Firefox/10.0', 'gdsasghdggds', NULL, NULL, 'gender_male', 'company', 'CMS', 'System Benutzer', 'address', 'city', 'zip', 204, 'phone', 'fax', '')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_payment',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'processor_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'fee' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'notnull' => true, 'default' => '0.00', 'after' => 'processor_id'),
                'free_from' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'notnull' => true, 'default' => '0.00', 'after' => 'fee'),
                'ord' => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'free_from'),
                'active' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'ord'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_payment` (`id`, `processor_id`, `fee`, `free_from`, `ord`, `active`)
            VALUES  (2, 1, '2.00', '20000.00', 0, 1),
                    (9, 4, '10.00', '15000.00', 0, 1),
                    (12, 2, '2.00', '10000.00', 0, 1),
                    (13, 9, '0.00', '0.00', 0, 1),
                    (14, 3, '0.00', '0.00', 0, 1),
                    (15, 10, '2.00', '1000.00', 0, 1)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_payment_processors',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'type' => array('type' => 'ENUM(\'internal\',\'external\')', 'notnull' => true, 'default' => 'internal', 'after' => 'id'),
                'name' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'type'),
                'description' => array('type' => 'text', 'after' => 'name'),
                'company_url' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'description'),
                'status' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'default' => '1', 'after' => 'company_url'),
                'picture' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'status'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_payment_processors` (`id`, `type`, `name`, `description`, `company_url`, `status`, `picture`, `text`)
            VALUES  (1, 'external', 'Saferpay', 'Saferpay is a comprehensive Internet payment platform, specially developed for commercial applications. It provides a guarantee of secure payment processes over the Internet for merchants as well as for cardholders. Merchants benefit from the easy integration of the payment method into their e-commerce platform, and from the modularity with which they can take account of current and future requirements. Cardholders benefit from the security of buying from any shop that uses Saferpay.', 'http://www.saferpay.com/', 1, 'logo_saferpay.gif', ''),
                    (2, 'external', 'Paypal', 'With more than 40 million member accounts in over 45 countries worldwide, PayPal is the world''s largest online payment service. PayPal makes sending money as easy as sending email! Any PayPal member can instantly and securely send money to anyone in the U.S. with an email address. PayPal can also be used on a web-enabled cell phone. In the future, PayPal will be available to use on web-enabled pagers and other handheld devices.', 'http://www.paypal.com/', 1, 'logo_paypal.gif', ''),
                    (3, 'external', 'yellowpay', 'PostFinance vereinfacht das Inkasso im Online-Shop.', 'http://www.postfinance.ch/', 1, 'logo_postfinance.gif', ''),
                    (4, 'internal', 'Internal', 'Internal no forms', '', 1, '', ''),
                    (5, 'internal', 'Internal_CreditCard', 'Internal with a Credit Card form', '', 1, '', ''),
                    (6, 'internal', 'Internal_Debit', 'Internal with a Bank Debit Form', '', 1, '', ''),
                    (7, 'external', 'Saferpay_Mastercard_Multipay_CAR', 'Saferpay is a comprehensive Internet payment platform, specially developed for commercial applications. It provides a guarantee of secure payment processes over the Internet for merchants as well as for cardholders. Merchants benefit from the easy integration of the payment method into their e-commerce platform, and from the modularity with which they can take account of current and future requirements. Cardholders benefit from the security of buying from any shop that uses Saferpay.', 'http://www.saferpay.com/', 1, 'logo_saferpay.gif', ''),
                    (8, 'external', 'Saferpay_Visa_Multipay_CAR', 'Saferpay is a comprehensive Internet payment platform, specially developed for commercial applications. It provides a guarantee of secure payment processes over the Internet for merchants as well as for cardholders. Merchants benefit from the easy integration of the payment method into their e-commerce platform, and from the modularity with which they can take account of current and future requirements. Cardholders benefit from the security of buying from any shop that uses Saferpay.', 'http://www.saferpay.com/', 1, 'logo_saferpay.gif', ''),
                    (9, 'internal', 'Internal_LSV', 'LSV with internal form', '', 1, '', ''),
                    (10, 'external', 'Datatrans', 'Die professionelle und komplette Payment-Lösung - all inclusive. Ein einziges Interface für sämtliche Zahlungsmethoden (Kreditkarten, Postcard, Kundenkarten). Mit variablem Angebot für unterschiedliche Kundenbedürfnisse.', 'http://datatrans.biz/', 1, 'logo_datatrans.gif', ''),
                    (11, 'external', 'mobilesolutions', 'PostFinance Mobile', 'https://postfinance.mobilesolutions.ch/', 1, 'logo_postfinance_mobile.gif', '')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_pricelists',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name' => array('type' => 'VARCHAR(25)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'lang_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'name'),
                'border_on' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'lang_id'),
                'header_on' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'border_on'),
                'header_left' => array('type' => 'text', 'notnull' => false, 'after' => 'header_on'),
                'header_right' => array('type' => 'text', 'notnull' => false, 'after' => 'header_left'),
                'footer_on' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'header_right'),
                'footer_left' => array('type' => 'text', 'notnull' => false, 'after' => 'footer_on'),
                'footer_right' => array('type' => 'text', 'notnull' => false, 'after' => 'footer_left'),
                'categories' => array('type' => 'text', 'after' => 'footer_right'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_pricelists` (`id`, `name`, `lang_id`, `border_on`, `header_on`, `header_left`, `header_right`, `footer_on`, `footer_left`, `footer_right`, `categories`)
            VALUES (1, 'Beispiel Preisliste', 1, 1, 1, 'Beispiel Preisliste', NULL, 1, '<--DATE-->', '<--PAGENUMBER-->', '*')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_products',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'picture' => array('type' => 'VARCHAR(4096)', 'notnull' => false, 'after' => 'id'),
                'category_id' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'picture'),
                'distribution' => array('type' => 'VARCHAR(16)', 'notnull' => true, 'default' => '', 'after' => 'category_id'),
                'normalprice' => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00', 'after' => 'distribution'),
                'resellerprice' => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00', 'after' => 'normalprice'),
                'stock' => array('type' => 'INT(10)', 'notnull' => true, 'default' => '10', 'after' => 'resellerprice'),
                'stock_visible' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'stock'),
                'discountprice' => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00', 'after' => 'stock_visible'),
                'discount_active' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'discountprice'),
                'active' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'discount_active'),
                'b2b' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'active'),
                'b2c' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'b2b'),
                'date_start' => array('type' => 'timestamp', 'notnull' => true, 'default' => '0000-00-00 00:00:00', 'after' => 'b2c'),
                'date_end' => array('type' => 'timestamp', 'notnull' => true, 'default' => '0000-00-00 00:00:00', 'after' => 'date_start'),
                'manufacturer_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'after' => 'date_end'),
                'ord' => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0', 'after' => 'manufacturer_id'),
                'vat_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'after' => 'ord'),
                'weight' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'after' => 'vat_id'),
                'flags' => array('type' => 'VARCHAR(4096)', 'notnull' => false, 'after' => 'weight'),
                'group_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'after' => 'flags'),
                'article_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'after' => 'group_id'),
                'usergroup_ids' => array('type' => 'VARCHAR(4096)', 'notnull' => false, 'after' => 'article_id'),
            ),
            array(
                'group_id' => array('fields' => array('group_id')),
                'article_id' => array('fields' => array('article_id')),
                'flags' => array('fields' => array('flags'), 'type' => 'FULLTEXT'),
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_products` (`id`, `picture`, `category_id`, `distribution`, `normalprice`, `resellerprice`, `stock`, `stock_visible`, `discountprice`, `discount_active`, `active`, `b2b`, `b2c`, `date_start`, `date_end`, `manufacturer_id`, `ord`, `vat_id`, `weight`, `flags`, `group_id`, `article_id`, `usergroup_ids`)
            VALUES  (12, 'aHRjX29uZV94LmpwZw==?NTIw?Mjkx:?MA==?MA==:?MA==?MA==', '9', 'delivery', '549.90', '0.00', 50, 0, '500.00', 1, 1, 1, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 3, 0, 10, 0, '', 0, 0, ''),
                    (13, 'Y29udHJleHhfcHJlbWl1bS5qcGc=?NDA5?NDgw:?MA==?MA==:?MA==?MA==', '10', 'delivery', '948.00', '0.00', 10000, 0, '0.00', 0, 1, 1, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 4, 0, 10, 0, '', 0, 0, ''),
                    (14, 'bWl0Z2xpZWRzY2hhZnQuanBn?NTIy?NTM4:?MA==?MA==:?MA==?MA==', '11', 'none', '60.00', '0.00', 100000, 0, '0.00', 0, 1, 1, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 5, 0, 10, 0, '', 0, 0, '')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

// TODO:  Obsolete in version 3.  Drop!
/*        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_products_downloads',
            array(
                'products_downloads_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'products_downloads_name' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'products_downloads_id'),
                'products_downloads_filename' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'products_downloads_name'),
                'products_downloads_maxdays' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0', 'after' => 'products_downloads_filename'),
                'products_downloads_maxcount' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0', 'after' => 'products_downloads_maxdays'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );*/

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_rel_countries',
            array(
                'zone_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'country_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'zone_id'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_rel_countries` (`zone_id`, `country_id`)
            VALUES  (1, 1),
                    (1, 2),
                    (1, 3),
                    (1, 4),
                    (1, 5),
                    (1, 6),
                    (1, 7),
                    (1, 8),
                    (1, 9),
                    (1, 10),
                    (1, 11),
                    (1, 12),
                    (1, 13),
                    (1, 14),
                    (1, 15),
                    (1, 16),
                    (1, 17),
                    (1, 18),
                    (1, 19),
                    (1, 20),
                    (1, 21),
                    (1, 22),
                    (1, 23),
                    (1, 24),
                    (1, 25),
                    (1, 26),
                    (1, 27),
                    (1, 28),
                    (1, 29),
                    (1, 30),
                    (1, 31),
                    (1, 32),
                    (1, 33),
                    (1, 34),
                    (1, 35),
                    (1, 36),
                    (1, 37),
                    (1, 38),
                    (1, 39),
                    (1, 40),
                    (1, 41),
                    (1, 42),
                    (1, 43),
                    (1, 44),
                    (1, 45),
                    (1, 46),
                    (1, 47),
                    (1, 48),
                    (1, 49),
                    (1, 50),
                    (1, 51),
                    (1, 52),
                    (1, 53),
                    (1, 54),
                    (1, 55),
                    (1, 56),
                    (1, 57),
                    (1, 58),
                    (1, 59),
                    (1, 60),
                    (1, 61),
                    (1, 62),
                    (1, 63),
                    (1, 64),
                    (1, 65),
                    (1, 66),
                    (1, 67),
                    (1, 68),
                    (1, 69),
                    (1, 70),
                    (1, 71),
                    (1, 72),
                    (1, 73),
                    (1, 74),
                    (1, 75),
                    (1, 76),
                    (1, 77),
                    (1, 78),
                    (1, 79),
                    (1, 80),
                    (1, 81),
                    (3, 81),
                    (1, 82),
                    (1, 83),
                    (1, 84),
                    (1, 85),
                    (1, 86),
                    (1, 87),
                    (1, 88),
                    (1, 89),
                    (1, 90),
                    (1, 91),
                    (1, 92),
                    (1, 93),
                    (1, 94),
                    (1, 95),
                    (1, 96),
                    (1, 97),
                    (1, 98),
                    (1, 99),
                    (1, 101),
                    (1, 102),
                    (1, 103),
                    (1, 104),
                    (1, 105),
                    (1, 106),
                    (1, 107),
                    (1, 108),
                    (1, 109),
                    (1, 110),
                    (1, 111),
                    (1, 112),
                    (1, 113),
                    (1, 114),
                    (1, 115),
                    (1, 116),
                    (1, 117),
                    (1, 118),
                    (1, 119),
                    (1, 120),
                    (1, 121),
                    (1, 122),
                    (2, 122),
                    (1, 123),
                    (1, 124),
                    (1, 125),
                    (1, 126),
                    (1, 127),
                    (1, 128),
                    (1, 129),
                    (1, 130),
                    (1, 131),
                    (1, 132),
                    (1, 133),
                    (1, 134),
                    (1, 135),
                    (1, 136),
                    (1, 137),
                    (1, 138),
                    (1, 139),
                    (1, 140),
                    (1, 141),
                    (1, 142),
                    (1, 143),
                    (1, 144),
                    (1, 145),
                    (1, 146),
                    (1, 147),
                    (1, 148),
                    (1, 149),
                    (1, 150),
                    (1, 151),
                    (1, 152),
                    (1, 153),
                    (1, 154),
                    (1, 155),
                    (1, 156),
                    (1, 157),
                    (1, 158),
                    (1, 159),
                    (1, 160),
                    (1, 161),
                    (1, 162),
                    (1, 163),
                    (1, 164),
                    (1, 165),
                    (1, 166),
                    (1, 167),
                    (1, 168),
                    (1, 169),
                    (1, 170),
                    (1, 171),
                    (1, 172),
                    (1, 173),
                    (1, 174),
                    (1, 175),
                    (1, 176),
                    (1, 177),
                    (1, 178),
                    (1, 179),
                    (1, 180),
                    (1, 181),
                    (1, 182),
                    (1, 183),
                    (1, 184),
                    (1, 185),
                    (1, 186),
                    (1, 187),
                    (1, 188),
                    (1, 189),
                    (1, 190),
                    (1, 191),
                    (1, 192),
                    (1, 193),
                    (1, 194),
                    (1, 195),
                    (1, 196),
                    (1, 197),
                    (1, 198),
                    (1, 199),
                    (1, 200),
                    (1, 201),
                    (1, 202),
                    (1, 203),
                    (1, 204),
                    (2, 204),
                    (1, 205),
                    (1, 206),
                    (1, 207),
                    (1, 208),
                    (1, 209),
                    (1, 210),
                    (1, 211),
                    (1, 212),
                    (1, 213),
                    (1, 214),
                    (1, 215),
                    (1, 216),
                    (1, 217),
                    (1, 218),
                    (1, 219),
                    (1, 220),
                    (1, 221),
                    (1, 222),
                    (1, 223),
                    (1, 224),
                    (1, 225),
                    (1, 226),
                    (1, 227),
                    (1, 228),
                    (1, 229),
                    (1, 230),
                    (1, 231),
                    (1, 232),
                    (1, 233),
                    (1, 234),
                    (1, 235),
                    (1, 236),
                    (1, 237),
                    (1, 238),
                    (1, 239)
            ON DUPLICATE KEY UPDATE `zone_id` = `zone_id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_rel_customer_coupon',
            array(
                'code' => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => '', 'primary' => true),
                'customer_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'code'),
                'order_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'customer_id'),
                'count' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'order_id'),
                'amount' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'notnull' => true, 'default' => '0.00', 'after' => 'count'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_rel_discount_group',
            array(
                'customer_group_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'article_group_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'customer_group_id'),
                'rate' => array('type' => 'DECIMAL(9,2)', 'notnull' => true, 'default' => '0.00', 'after' => 'article_group_id'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_rel_discount_group` (`customer_group_id`, `article_group_id`, `rate`)
            VALUES  (2, 1, '5.00'),
                    (3, 1, '10.00')
            ON DUPLICATE KEY UPDATE `customer_group_id` = `customer_group_id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_rel_payment',
            array(
                'zone_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'payment_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'zone_id'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_rel_payment` (`zone_id`, `payment_id`)
            VALUES  (1, 2),
                    (1, 9),
                    (1, 12),
                    (1, 13),
                    (1, 14),
                    (2, 15)
            ON DUPLICATE KEY UPDATE `zone_id` = `zone_id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_rel_product_attribute',
            array(
                'product_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'option_id' => array('type' => 'INT(10)', 'unsigned' => true, 'after' => 'product_id', 'primary' => true),
                'ord' => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0', 'after' => 'option_id'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_rel_product_attribute` (`product_id`, `option_id`, `ord`)
            VALUES  (12, 1, 0),
                    (12, 3, 0)
            ON DUPLICATE KEY UPDATE `product_id` = `product_id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_rel_shipper',
            array(
                'zone_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'shipper_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'zone_id'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_rel_shipper` (`zone_id`, `shipper_id`)
            VALUES  (1, 1),
                    (1, 2),
                    (1, 3),
                    (1, 4)
            ON DUPLICATE KEY UPDATE `zone_id` = `zone_id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_shipment_cost',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'shipper_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'max_weight' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'after' => 'shipper_id'),
                'fee' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'notnull' => false, 'after' => 'max_weight'),
                'free_from' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'notnull' => false, 'after' => 'fee'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_shipment_cost` (`id`, `shipper_id`, `max_weight`, `fee`, `free_from`)
            VALUES  (1, 1, 1000, '20.00', '100.00'),
                    (2, 2, 250, '35.00', '150.00'),
                    (3, 3, 1000, '10.00', '100.00'),
                    (4, 4, 1000, '89.00', '1000.00'),
                    (5, 1, 10000, '50.00', '1000.00'),
                    (6, 2, 2000, '55.00', '300.00'),
                    (7, 3, 10000, '25.00', '1000.00')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_shipper',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'active' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'id'),
                'ord' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'active'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_shipper` (`id`, `active`, `ord`)
            VALUES  (1, 1, 0),
                    (2, 1, 0),
                    (3, 1, 0),
                    (4, 1, 0)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_vat',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'rate' => array('type' => 'DECIMAL(5,2)', 'unsigned' => true, 'notnull' => true, 'default' => '0.00', 'after' => 'id'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_vat` (`id`, `rate`)
            VALUES  (1, '0.00'),
                    (2, '19.00'),
                    (3, '7.00'),
                    (4, '5.50'),
                    (5, '9.00'),
                    (6, '16.00'),
                    (7, '20.00'),
                    (8, '10.00'),
                    (9, '12.00'),
                    (10, '8.00'),
                    (11, '3.60'),
                    (12, '2.40'),
                    (13, '17.50'),
                    (14, '5.00')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_shop_zones',
            array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'active' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'id'),
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_shop_zones` (`id`, `active`)
            VALUES  (1, 1),
                    (2, 1),
                    (3, 1)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}
