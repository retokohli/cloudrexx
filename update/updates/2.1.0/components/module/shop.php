<?php

/**
 * Module Shop Update
 *
 * In this update, the shop finally becomes multilingual.
 *
 * Important note:
 * If you add anything to the update, you *should* do so *before* the
 * comment marked with "Added 20081124".
 * After that point, most of the shop tables will be fundamentally altered,
 * giving you a hard time trying to insert or update content.
 */

/* DEBUG*/
error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once(ASCMS_DOCUMENT_ROOT.'/core/Text.class.php');
require_once(ASCMS_LIBRARY_PATH.'/FRAMEWORK/Language.class.php');
//echo("Document Root: ".ASCMS_DOCUMENT_ROOT."<br />");

function _shopUpdate()
{
    global $objDatabase, $_ARRAYLANG;

echo("Database: ".var_export($objDatabase, true)."<br />");

/* DEBUG*/
$objDatabase->debug = 1;


    // Shop thumbnail default settings: shop_thumbnail_max_width
    $query = "
        SELECT 1 FROM ".DBPREFIX."module_shop_config
        WHERE name='shop_thumbnail_max_width'";
    $objResult = $objDatabase->Execute($query);
    if (!$objResult)
        return _databaseError($query, $objDatabase->ErrorMsg());
    if ($objResult->EOF) {
        $query = "
            INSERT INTO ".DBPREFIX."module_shop_config (
                name, value
            ) VALUES (
                'shop_thumbnail_max_width', '120'
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }

    // Shop thumbnail default settings: shop_thumbnail_max_height
    $query = "
        SELECT 1 FROM ".DBPREFIX."module_shop_config
        WHERE name='shop_thumbnail_max_height'";
    $objResult = $objDatabase->Execute($query);
    if (!$objResult)
        return _databaseError($query, $objDatabase->ErrorMsg());
    if ($objResult->EOF) {
        $query = "
            INSERT INTO ".DBPREFIX."module_shop_config (
                name, value
            ) VALUES (
                'shop_thumbnail_max_height', '90'
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }

    // Shop thumbnail default settings: shop_thumbnail_quality
    $query = "
        SELECT 1 FROM ".DBPREFIX."module_shop_config
        WHERE name='shop_thumbnail_quality'";
    $objResult = $objDatabase->Execute($query);
    if (!$objResult)
        return _databaseError($query, $objDatabase->ErrorMsg());
    if ($objResult->EOF) {
        $query = "
            INSERT INTO ".DBPREFIX."module_shop_config (
                name, value
            ) VALUES (
                'shop_thumbnail_quality', '80'
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }

    // VAT Settings
    // Rename a few
    $query = "UPDATE ".DBPREFIX."module_shop_config SET name='vat_number' WHERE name='tax_number'";
    $objResult = $objDatabase->Execute($query);
    if (!$objResult)
        return _databaseError($query, $objDatabase->ErrorMsg());
    $query = "UPDATE ".DBPREFIX."module_shop_config SET name='vat_default_id' WHERE name='tax_default_id'";
    $objResult = $objDatabase->Execute($query);
    if (!$objResult)
        return _databaseError($query, $objDatabase->ErrorMsg());

    // New individual VAT settings for home and foreign countries,
    // customers and resellers.
    // Enable VAT
    $query = "
        SELECT value FROM ".DBPREFIX."module_shop_config
         WHERE name='tax_enabled'";
    $objResult = $objDatabase->Execute($query);
    if (!$objResult)
        return _databaseError($query, $objDatabase->ErrorMsg());
    if (!$objResult->EOF) {
        $value = $objResult->fields['value'];
        // Apply the value to all new settings that are missing
        $query = "
            SELECT 1 FROM ".DBPREFIX."module_shop_config
             WHERE name='vat_enabled_home_customer'";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
        if ($objResult->EOF) {
            $query = "
                INSERT INTO ".DBPREFIX."module_shop_config (
                    name, value
                ) VALUES (
                    'vat_enabled_home_customer', '$value'
                )
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult)
                return _databaseError($query, $objDatabase->ErrorMsg());
        }
        $query = "
            SELECT 1 FROM ".DBPREFIX."module_shop_config
             WHERE name='vat_enabled_home_reseller'";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
        if ($objResult->EOF) {
            $query = "
                INSERT INTO ".DBPREFIX."module_shop_config (
                    name, value
                ) VALUES (
                    'vat_enabled_home_reseller', '$value'
                )
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult)
                return _databaseError($query, $objDatabase->ErrorMsg());
        }
        $query = "
            SELECT 1 FROM ".DBPREFIX."module_shop_config
             WHERE name='vat_enabled_foreign_customer'";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
        if ($objResult->EOF) {
            $query = "
                INSERT INTO ".DBPREFIX."module_shop_config (
                    name, value
                ) VALUES (
                    'vat_enabled_foreign_customer', '$value'
                )
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult)
                return _databaseError($query, $objDatabase->ErrorMsg());
        }
        $query = "
            SELECT 1 FROM ".DBPREFIX."module_shop_config
             WHERE name='vat_enabled_foreign_reseller'";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
        if ($objResult->EOF) {
            $query = "
                INSERT INTO ".DBPREFIX."module_shop_config (
                    name, value
                ) VALUES (
                    'vat_enabled_foreign_reseller', '$value'
                )
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult)
                return _databaseError($query, $objDatabase->ErrorMsg());
        }
        // Delete the old entry
        $query = "
            DELETE FROM ".DBPREFIX."module_shop_config
             WHERE name='tax_enabled'";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }

    // Include VAT
    $query = "
        SELECT value FROM ".DBPREFIX."module_shop_config
         WHERE name='tax_included'";
    $objResult = $objDatabase->Execute($query);
    if (!$objResult)
        return _databaseError($query, $objDatabase->ErrorMsg());
    if (!$objResult->EOF) {
        $value = $objResult->fields['value'];
        // Apply the value to all new settings that are missing
        $query = "
            SELECT 1 FROM ".DBPREFIX."module_shop_config
             WHERE name='vat_included_home_customer'";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
        if ($objResult->EOF) {
            $query = "
                INSERT INTO ".DBPREFIX."module_shop_config (
                    name, value
                ) VALUES (
                    'vat_included_home_customer', '$value'
                )
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult)
                return _databaseError($query, $objDatabase->ErrorMsg());
        }
        $query = "
            SELECT 1 FROM ".DBPREFIX."module_shop_config
             WHERE name='vat_included_home_reseller'";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
        if ($objResult->EOF) {
            $query = "
                INSERT INTO ".DBPREFIX."module_shop_config (
                    name, value
                ) VALUES (
                    'vat_included_home_reseller', '$value'
                )
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult)
                return _databaseError($query, $objDatabase->ErrorMsg());
        }
        $query = "
            SELECT 1 FROM ".DBPREFIX."module_shop_config
             WHERE name='vat_included_foreign_customer'";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
        if ($objResult->EOF) {
            $query = "
                INSERT INTO ".DBPREFIX."module_shop_config (
                    name, value
                ) VALUES (
                    'vat_included_foreign_customer', '$value'
                )
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult)
                return _databaseError($query, $objDatabase->ErrorMsg());
        }
        $query = "
            SELECT 1 FROM ".DBPREFIX."module_shop_config
             WHERE name='vat_included_foreign_reseller'";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
        if ($objResult->EOF) {
            $query = "
                INSERT INTO ".DBPREFIX."module_shop_config (
                    name, value
                ) VALUES (
                    'vat_included_foreign_reseller', '$value'
                )
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult)
                return _databaseError($query, $objDatabase->ErrorMsg());
        }
        // Delete the old entry
        $query = "
            DELETE FROM ".DBPREFIX."module_shop_config
             WHERE name='tax_included'";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }

    // New name for default VAT id,
    // new setting for VAT rate for other stuff (fees, post and package)
    $query = "
        SELECT value FROM ".DBPREFIX."module_shop_config
         WHERE name='tax_default_id'";
    $objResult = $objDatabase->Execute($query);
    if (!$objResult)
        return _databaseError($query, $objDatabase->ErrorMsg());
    if (!$objResult->EOF) {
        $value = $objResult->fields['value'];
        // Apply the value to the new settings that are missing
        $query = "
            SELECT 1 FROM ".DBPREFIX."module_shop_config
             WHERE name='vat_default_id'";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
        if ($objResult->EOF) {
            $query = "
                INSERT INTO ".DBPREFIX."module_shop_config (
                    name, value
                ) VALUES (
                    'vat_default_id', '$value'
                )
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult)
                return _databaseError($query, $objDatabase->ErrorMsg());
        }
        $query = "
            SELECT 1 FROM ".DBPREFIX."module_shop_config
             WHERE name='vat_other_id'";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
        if ($objResult->EOF) {
            $query = "
                INSERT INTO ".DBPREFIX."module_shop_config (
                    name, value
                ) VALUES (
                    'vat_other_id', '$value'
                )
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult)
                return _databaseError($query, $objDatabase->ErrorMsg());
        }
        // Delete the old entry
        $query = "
            DELETE FROM ".DBPREFIX."module_shop_config
             WHERE name='tax_default_id'";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }






    // Products table fields
    $arrProductColumns = $objDatabase->MetaColumns(DBPREFIX.'module_shop_products');
    if ($arrProductColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_shop_products'));
        return false;
    }
    // Products table indices
    $arrProductIndices = $objDatabase->MetaIndexes(DBPREFIX.'module_shop_products');
    if ($arrProductIndices === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_shop_products'));
        return false;
    }

/*
  OBSOLETE -- Field is migrated below anyway

    // Extend the maximum Product title length
    $query = "
        ALTER TABLE `".DBPREFIX."module_shop_products`
        CHANGE `title` `title` varchar(255) NOT NULL default ''
    ";
    if ($objDatabase->Execute($query) === false) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }
*/
    // Remove obsolete fields from Product table
    if (!isset($arrProductColumns['PROPERTY1'])) {
        $query = "
              ALTER TABLE `".DBPREFIX."module_shop_products` DROP `property1`
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }
    if (!isset($arrProductColumns['PROPERTY2'])) {
        $query = "
              ALTER TABLE `".DBPREFIX."module_shop_products` DROP `property2`
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }
    if (!isset($arrProductColumns['THUMBNAIL_PERCENT'])) {
        $query = "
              ALTER TABLE `".DBPREFIX."module_shop_products` DROP `thumbnail_percent`
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }
    if (!isset($arrProductColumns['THUMBNAIL_QUALITY'])) {
        $query = "
              ALTER TABLE `".DBPREFIX."module_shop_products` DROP `thumbnail_quality`
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }

    // Add flags field to Product table
    if (!isset($arrProductColumns['FLAGS'])) {
        $query = "
            ALTER TABLE ".DBPREFIX."module_shop_products
            ADD flags varchar(255) NOT NULL default ''
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }
    if (!isset($arrProductIndices['flags']['columns'])) {
        $query = "
            ALTER TABLE ".DBPREFIX."module_shop_products
            ADD FULLTEXT (flags)
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }

    // Add usergroups field to Product table
    if (!isset($arrProductColumns['USERGROUPS'])) {
        $query = "
            ALTER TABLE ".DBPREFIX."module_shop_products
            ADD usergroups varchar(255) NOT NULL default ''
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }

    // Add group_id field to Product table
    if (!isset($arrProductColumns['GROUP_ID'])) {
        $query = "
            ALTER TABLE ".DBPREFIX."module_shop_products
            ADD group_id int(11) unsigned NULL default NULL
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }
    if (!isset($arrProductIndices['group_id']['columns'])) {
        $query = "
            ALTER TABLE ".DBPREFIX."module_shop_products
            ADD INDEX (group_id)
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }

    // Add article_id field to Product table
    if (!isset($arrProductColumns['ARTICLE_ID'])) {
        $query = "
            ALTER TABLE ".DBPREFIX."module_shop_products
            ADD article_id int(11) unsigned NULL default NULL
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }
    if (!isset($arrProductIndices['article_id']['columns'])) {
        $query = "
            ALTER TABLE ".DBPREFIX."module_shop_products
            ADD INDEX (article_id)
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }

    // Add keywords field to Product table
    // We also have to check the possibly migrated field, see below
    if (   !isset($arrProductColumns['KEYWORDS'])
        && !isset($arrProductColumns['TEXT_KEYWORDS_ID'])) {
        $query = "
            ALTER TABLE ".DBPREFIX."module_shop_products
            ADD keywords varchar(255) NULL default NULL
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }
/*
  OBSOLETE -- This field is migrated below anyway
    if (!isset($arrProductIndices['keywords']['columns'])) {
        $query = "
            ALTER TABLE ".DBPREFIX."module_shop_products
            ADD FULLTEXT (keywords)
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }
*/


    // Categories table fields
    $arrCategoriesColumns = $objDatabase->MetaColumns(DBPREFIX.'module_shop_categories');
    if ($arrCategoriesColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_shop_categories'));
        return false;
    }
    $arrCategoriesIndices = $objDatabase->MetaIndexes(DBPREFIX.'module_shop_categories');
    if ($arrCategoriesIndices === false) {
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
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }

    // Add flags field to Shop Category table
    if (!isset($arrCategoriesColumns['FLAGS'])) {
        $query = "
            ALTER TABLE ".DBPREFIX."module_shop_categories
            ADD flags varchar(255) NOT NULL default ''
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }
    if (!isset($arrCategoriesIndices['flags']['columns'])) {
        $query = "
            ALTER TABLE ".DBPREFIX."module_shop_categories
            ADD FULLTEXT (flags)
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }


    // Customer table fields
    $arrCustomerColumns = $objDatabase->MetaColumns(DBPREFIX.'module_shop_customers');
    if ($arrCustomerColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_shop_customers'));
        return false;
    }

    // Add group_id field to Customer table
    if (!isset($arrCustomerColumns['GROUP_ID'])) {
        $query = "
            ALTER TABLE ".DBPREFIX."module_shop_customers
            ADD group_id int(11) unsigned NULL default NULL
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }


    // Add Yellowpay default settings
    $query = "
        SELECT 1 FROM ".DBPREFIX."module_shop_config
        WHERE name='yellowpay_accepted_payment_methods'";
    $objResult = $objDatabase->Execute($query);
    if (!$objResult)
        return _databaseError($query, $objDatabase->ErrorMsg());
    if ($objResult->EOF) {
        $query = "
            INSERT INTO ".DBPREFIX."module_shop_config (
                `id`, `name`, `value`, `status`
            ) VALUES (
                NULL, 'yellowpay_accepted_payment_methods', '', '1'
            );
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }

    // Change old yellowpay_delivery_payment_type setting
    // to new yellowpay_authorization_type
    $query = "
        SELECT 1 FROM ".DBPREFIX."module_shop_config
        WHERE `name`='yellowpay_delivery_payment_type'";
    $objResult = $objDatabase->Execute($query);
    if (!$objResult)
        return _databaseError($query, $objDatabase->ErrorMsg());
    if (!$objResult->EOF) {
        $query = "
            UPDATE ".DBPREFIX."module_shop_config
               SET `name`='yellowpay_authorization_type'
             WHERE `name`='yellowpay_delivery_payment_type'
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }
    // If the new yellowpay_authorization_type setting is still missing,
    // insert it.
    $query = "
        SELECT 1 FROM ".DBPREFIX."module_shop_config
        WHERE `name`='yellowpay_authorization_type'";
    $objResult = $objDatabase->Execute($query);
    if (!$objResult)
        return _databaseError($query, $objDatabase->ErrorMsg());
    if ($objResult->EOF) {
        $query = "
            INSERT INTO ".DBPREFIX."module_shop_config (
              `name`, `value`
            ) VALUES (
               'yellowpay_authorization_type', 'immediate'
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }

    // Add yellowpay test server flag setting
    $query = "
        SELECT 1 FROM ".DBPREFIX."module_shop_config
        WHERE `name`='yellowpay_use_testserver'";
    $objResult = $objDatabase->Execute($query);
    if (!$objResult)
        return _databaseError($query, $objDatabase->ErrorMsg());
    if ($objResult->EOF) {
        $query = "
            INSERT INTO ".DBPREFIX."module_shop_config (
                `name`, `value`, `status`
            ) VALUES (
                'yellowpay_use_testserver', '1', '1'
            );
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }

    // Add weight enable flag setting
    $query = "
        SELECT 1 FROM ".DBPREFIX."module_shop_config
        WHERE `name`='shop_weight_enable'";
    $objResult = $objDatabase->Execute($query);
    if (!$objResult)
        return _databaseError($query, $objDatabase->ErrorMsg());
    if ($objResult->EOF) {
        $query = "
            INSERT INTO `".DBPREFIX."module_shop_config` (
                `id`, `name`, `value`, `status`
            ) VALUES (
                NULL, 'shop_weight_enable', '1', '1'
            );
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }

    // Update Payment Service Providers
    // Payment Service Provider table fields
    $arrPspColumns = $objDatabase->MetaColumns(DBPREFIX.'module_shop_payment_processors');
    if (!$arrPspColumns) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_shop_payment_processors'));
        return false;
    }
    // No need to check the migrated field.  If it doesn't exist, it has
    // been updated and migrated already, see below.
    if (isset($arrPspColumns['NAME'])) {
        // Update yellowpay payment processor name and description
        $query = "
            UPDATE `".DBPREFIX."module_shop_payment_processors`
            SET `name`='yellowpay',
                `description`='Yellowpay vereinfacht das Inkasso im Online-Shop. Ihre Kunden bezahlen die Einkaeufe direkt mit dem Gelben Konto oder einer Kreditkarte. Ihr Plus: Mit den Zahlungsarten \"PostFinanceCard\", \"yellownet\" und \"yellowbill\" bieten Sie 2,4 Millionen Inhaberinnen und Inhabern eines Gelben Kontos eine kundenfreundliche und sichere Zahlungsmoeglichkeit.'
            WHERE `".DBPREFIX."module_shop_payment_processors`.`id`=3;
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());

        // Add datatrans payment processor name and description
        $query = "
            SELECT 1
              FROM `".DBPREFIX."module_shop_payment_processors`
             WHERE `name`='Datatrans'
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
        if ($objResult->EOF) {
            $query = "
                INSERT INTO `".DBPREFIX."module_shop_payment_processors` (
                  `id`, `type`, `name`, `description`,
                  `company_url`, `status`, `picture`, `text`
                ) VALUES (
                  '10', 'external', 'Datatrans', 'Datatrans',
                  'http://datatrans.biz/', '1', 'logo_datatrans.gif', ''
                )
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult)
                return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    // Add e-mail template for order confirmation with user account data
    // Mail template table fields
    $arrMailColumns = $objDatabase->MetaColumns(DBPREFIX.'module_shop_mail');
    if (!$arrMailColumns) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_shop_mail'));
        return false;
    }
    // No need to check the migrated field.  If it doesn't exist, it has
    // been updated and migrated already, see below.
    if (isset($arrMailColumns['TPLNAME'])) {
        // There's not much sense in looking for the ID here, as the shop
        // owner might have added more custom templates.
        // Instead, we check the count of system (protected) templates.
        // If there are four or more already, we can safely assume that either
        // - The update has been run already and the template has been added, or
        // - The shop has been customized in another way, so adding this
        //   new protected template might not yield the expected result anyway.
        // So, we'll skip this part in both cases.  The probably very low
        // number of exceptions to the assumptions above has to be fixed
        // manually.
        $query = "
            SELECT COUNT(*) as `numof_protected_templates`
              FROM ".DBPREFIX."module_shop_mail
             WHERE protected=1
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
        $numof_protected_templates = $objResult->fields['numof_protected_templates'];
        $template_id = false;
        if ($numof_protected_templates == 3) {
            $query = "
                INSERT INTO `".DBPREFIX."module_shop_mail` (
                    `tplname`, `protected`
                ) VALUES (
                    'Bestellungsbestaetigung mit Zugangsdaten', '1'
                );
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult)
                return _databaseError($query, $objDatabase->ErrorMsg());
            $template_id = $objDatabase->InsertId();
        }
        if (empty($template_id)) {
            // If the template has not been inserted above, we still have to
            // verify whether the content exists -- the update may just have
            // failed at this point before.
            // For simplicity, we use the same test as above, namely count
            // the number of protected templates.  This time, we have to
            // count distinct template IDs in the content table, however.
            $query = "
                SELECT COUNT(DISTINCT tpl_id) as `numof_protected_templates`
                  FROM ".DBPREFIX."module_shop_mail AS m
                 INNER JOIN ".DBPREFIX."module_shop_mail_content AS c
                    ON m.id=c.tpl_id
                 WHERE m.protected=1
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult)
                return _databaseError($query, $objDatabase->ErrorMsg());
            $numof_protected_templates = $objResult->fields['numof_protected_templates'];
            if ($numof_protected_templates == 3) {
                // Now there are only three content records out of four.
                // But the template itself must have been inserted already,
                // and it's got to be the last one.  Get its ID.
                $query = "
                    SELECT MAX(id) AS `id`
                      FROM `".DBPREFIX."module_shop_mail`
                     WHERE protected=1
                ";
                $objResult = $objDatabase->Execute($query);
                if (!$objResult)
                    return _databaseError($query, $objDatabase->ErrorMsg());
                $template_id = $objResult->fields['id'];
            }
        }

        // If the template ID is non-empty, we got to add the content
        if ($template_id) {
            // Set the e-mail addres to the default from the shop settings
            $query = "
                SELECT `value`
                  FROM `".DBPREFIX."module_shop_config`
                 WHERE `name`='email'
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult)
                return _databaseError($query, $objDatabase->ErrorMsg());
            $email = '';
            if (!$objResult->EOF)
                $email = $objResult->fields['value'];
            $query = "
                INSERT INTO `".DBPREFIX."module_shop_mail_content` (
                    `tpl_id`, `lang_id`, `from_mail`, `xsender`, `subject`, `message`
                ) VALUES (
                    $template_id, '1', '".addslashes($email)."', 'Contrexx Demo Online Shop',
                    'Contrexx Auftragsbestaetigung und Zugangsdaten vom <DATE>',
                    'Sehr geehrte Kundin, sehr geehrter Kunde\r\n\r\nHerzlichen Dank fuer Ihre Bestellung im Contrexx Demo Online Store.\r\n\r\nIhre Auftrags-Nr. lautet: <ORDER_ID>\r\nIhre Kunden-Nr. lautet: <CUSTOMER_ID>\r\nBestellungszeit: <ORDER_TIME>\r\n\r\n<ORDER_DATA>\r\n<LOGIN_DATA>\r\n\r\nIhre Kundenadresse:\r\n<CUSTOMER_COMPANY>\r\n<CUSTOMER_PREFIX> <CUSTOMER_FIRSTNAME> <CUSTOMER_LASTNAME>\r\n<CUSTOMER_ADDRESS>\r\n<CUSTOMER_ZIP> <CUSTOMER_CITY>\r\n<CUSTOMER_COUNTRY>\r\n\r\nLieferadresse:\r\n<SHIPPING_COMPANY>\r\n<SHIPPING_PREFIX> <SHIPPING_FIRSTNAME> <SHIPPING_LASTNAME>\r\n<SHIPPING_ADDRESS>\r\n<SHIPPING_ZIP> <SHIPPING_CITY>\r\n<SHIPPING_COUNTRY>\r\n\r\nIhre Zugangsdaten zum Shop:\r\nBenutzername: <USERNAME>\r\nPasswort: <PASSWORD>\r\n\r\nWir freuen uns auf Ihren naechsten Besuch im Online Store und\r\nwuenschen Ihnen noch einen schoenen Tag.\r\n\r\nP.S. Diese Auftragsbestaetigung wurde gesendet an: <CUSTOMER_EMAIL>\r\n\r\nMit freundlichen Gruessen\r\nIhr Contrexx Team'
                );
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult)
                return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }


    // Fix Product Attribute table to handle new option types
    $query = "
        ALTER TABLE `".DBPREFIX."module_shop_products_attributes_name`
        CHANGE `display_type` `display_type` TINYINT UNSIGNED NOT NULL DEFAULT '0' ;
    ";
    $objResult = $objDatabase->Execute($query);
    if (!$objResult)
        return _databaseError($query, $objDatabase->ErrorMsg());

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
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }



    // Added 20081124
    // Multilingual Shop

    // Prerequisites
    // A few indices need be removed prior to changing the table structure.
    // These queries fail if the respective index doesn't exist, which is
    // where we want to go anyway.  So we don't care.
    $objDatabase->Execute("
        ALTER TABLE `".DBPREFIX."module_shop_products` DROP INDEX `shopindex`
    ");
    $objDatabase->Execute("
        ALTER TABLE `".DBPREFIX."module_shop_products` DROP INDEX `keywords`
    ");

    // Make sure the *text table exists
    $arrTables  = $objDatabase->MetaTables('TABLES');
    if (empty($arrTables))
        return _databaseError("MetaTables('TABLES')", 'Could not read Table metadata');
    if (!in_array(DBPREFIX.'core_text', $arrTables)) {
        $query = "
            CREATE TABLE `".DBPREFIX."core_text` (
              `id` INT(10) UNSIGNED NOT NULL,
              `lang_id` INT(10) UNSIGNED NOT NULL,
              `module_id` INT(10) UNSIGNED DEFAULT NULL,
              `key_id` INT(10) UNSIGNED NULL DEFAULT NULL,
              `text` TEXT NOT NULL DEFAULT '',
              PRIMARY KEY `id` (`id`, `lang_id`),
              INDEX `module_id` (`module_id`),
              INDEX `key_id` (`key_id`),
              FULLTEXT `text` (`text`)
            ) ENGINE=MyISAM;
        ";
// `reference` varchar(255) default NULL,
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return _databaseError($query, $objDatabase->ErrorMsg());
    }
    // Affected Shop tables
    // Note that the indices of the tables, as well as the indices of the
    // field names, are used to determine the key_id value for the text table.
    // Formula:  (2**16)*table_index + (2**0)*field_index
    // Also change a few of the field names -- they are getting a bit long
    // and/or inconsistent.
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
/*
  This is used nowhere in the shop!
        'module_shop_products_downloads' => array(
          'field' => array('products_downloads_name', 'products_downloads_filename', ),
          'id' => array('products_downloads_id' => 0, ),
          'alter' => array(
            'products_downloads_id' => "`id` int(11) unsigned NOT NULL default '0'",
            'products_downloads_filename' => "`filename` varchar(255) collate utf8_unicode_ci NOT NULL default ''",
            'products_downloads_maxdays' => "`maxdays` int(11) unsigned default '0'",
            'products_downloads_maxcount' => "`maxcount` int(11) unsigned default '0'",
          ),
        ),
*/
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
        // Alter table names *only*
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
    $objLanguage = new FWLanguage();
    $arrLanguages = $objLanguage->getLanguageArray();
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

// TODO:  Fix the shop mail template tables like this:
/*
DROP TABLE IF EXISTS `contrexx_module_shop_mail`;
DROP TABLE IF EXISTS `contrexx_module_shop_mail_content`;

CREATE TABLE `contrexx_module_shop_mail` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `protected` tinyint(1) unsigned NOT NULL default '0',
  `text_name_id` int(11) unsigned default NULL,
  `text_from_id` int(11) unsigned default NULL,
  `text_sender_id` int(11) unsigned default NULL,
  `text_subject_id` int(11) unsigned default NULL,
  `text_message_id` int(11) unsigned default NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

-- Whatever:
INSERT INTO `contrexx_module_shop_mail` VALUES(1, 1, 245, 0, 249, 257, 265);
INSERT INTO `contrexx_module_shop_mail` VALUES(2, 1, 246, 0, 250, 258, 266);
INSERT INTO `contrexx_module_shop_mail` VALUES(3, 1, 247, 0, 251, 259, 267);
INSERT INTO `contrexx_module_shop_mail` VALUES(4, 1, 248, 0, 252, 260, 268);
*/



    return true;
}

?>
