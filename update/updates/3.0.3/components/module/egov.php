<?php

function _egovUpdate()
{
    global $objDatabase, $_ARRAYLANG;

    // Check required tables..
    $arrTables = $objDatabase->MetaTables('TABLES');
    if (!$arrTables) {
        setUpdateMsg($_ARRAYLANG['TXT_UNABLE_DETERMINE_DATABASE_STRUCTURE']);
        return false;
    }
    // Create new configuration table if missing
    if (!in_array(DBPREFIX."module_egov_configuration", $arrTables)) {
        $query = "
            CREATE TABLE ".DBPREFIX."module_egov_configuration (
              `name` varchar(255) NOT NULL default '',
              `value` text NOT NULL,
              UNIQUE KEY `name` (`name`)
            ) ENGINE=MyISAM;
        ";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    // Copy original values
    $arrField = array(
        'set_sender_name',
        'set_sender_email',
        'set_recipient_email',
        'set_state_subject',
        'set_state_email',
        'set_calendar_color_1',
        'set_calendar_color_2',
        'set_calendar_color_3',
        'set_calendar_legende_1',
        'set_calendar_legende_2',
        'set_calendar_legende_3',
        'set_calendar_background',
        'set_calendar_border',
        'set_calendar_date_label',
        'set_calendar_date_desc',
        'set_orderentry_subject',
        'set_orderentry_email',
        'set_orderentry_name',
        'set_orderentry_sender',
        'set_orderentry_recipient',
        'set_paypal_email',
        'set_paypal_currency',
        'set_paypal_ipn',
    );
    foreach ($arrField as $fieldname) {
        $query = "
            SELECT 1 FROM ".DBPREFIX."module_egov_configuration
            WHERE name='$fieldname'
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
        if ($objResult->RecordCount() == 1) {
            // The value is already there
            continue;
        }

        // Copy the original value
        $query = "
            INSERT INTO ".DBPREFIX."module_egov_configuration (name, value)
            SELECT '$fieldname', `$fieldname`
              FROM ".DBPREFIX."module_egov_settings
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    // Add new settings for Yellowpay
    $arrField = array(
        'yellowpay_accepted_payment_methods' => '',
        'yellowpay_authorization' => 'immediate',
        'yellowpay_uid' => 'demo',
        'yellowpay_hashseed' => 'demo',
        'yellowpay_shopid' => '',
        'yellowpay_use_testserver' => '1',
    );

    foreach ($arrField as $fieldname => $defaultvalue) {
        $query = "
            SELECT 1 FROM ".DBPREFIX."module_egov_configuration
            WHERE name='$fieldname'
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
        if ($objResult->RecordCount() == 1) {
            // The value is already there
            continue;
        }

        // Add the new setting with its default value
        $query = "
            INSERT INTO ".DBPREFIX."module_egov_configuration (
                name, value
            ) VALUES (
                '$fieldname', '$defaultvalue'
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }


    // products table
    if (!in_array(DBPREFIX."module_egov_products", $arrTables)) {
        $query = "
            CREATE TABLE `".DBPREFIX."module_egov_products` (
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
              `product_target_subject` varchar(255) NOT NULL,
              `product_target_body` text NOT NULL,
              `product_paypal` tinyint(1) NOT NULL default '0',
              `product_paypal_sandbox` varchar(255) NOT NULL default '',
              `product_paypal_currency` varchar(255) NOT NULL default '',
              `product_orderby` int(11) NOT NULL default '0',
              PRIMARY KEY  (`product_id`)
            ) TYPE=MyISAM;
        ";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    // Add Yellowpay field to Product table
    $arrProductColumns = $objDatabase->MetaColumns(DBPREFIX.'module_egov_products');
    if ($arrProductColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_egov_products'));
        return false;
    }
    if (!isset($arrProductColumns['YELLOWPAY'])) {
        $query = "
            ALTER TABLE ".DBPREFIX."module_egov_products
            ADD `yellowpay` TINYINT(1) unsigned NOT NULL default '0'
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    // Add quantity limit field to Product table
    if (!isset($arrProductColumns['PRODUCT_QUANTITY_LIMIT'])) {
        $query = "
            ALTER TABLE ".DBPREFIX."module_egov_products
            ADD `product_quantity_limit` TINYINT(2) unsigned NOT NULL default '1'
            AFTER `product_quantity`;
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    // Add alternative payment method name field to Product table
    if (!isset($arrProductColumns['ALTERNATIVE_NAMES'])) {
        $query = "
            ALTER TABLE ".DBPREFIX."module_egov_products
            ADD `alternative_names` TEXT NOT NULL;
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }


    /********************************
     * EXTENSION:   Timezone        *
     * ADDED:       Contrexx v3.0.0 *
     ********************************/
    try {
        \Cx\Lib\UpdateUtil::sql('ALTER TABLE `'.DBPREFIX.'module_egov_orders` CHANGE `order_date` `order_date` TIMESTAMP NOT NULL DEFAULT "0000-00-00 00:00:00"');
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }


    return true;
}



function _egovInstall()
{
    try {

        /**************************************************************************
         * EXTENSION:   Initial creation (for contrexx editions <> premium with   *
         *              version < 3.0.0 which have this module not yet installed) *
         *                                                                        *
         * ADDED:       Contrexx v3.0.1                                           *
         **************************************************************************/
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_egov_configuration',
            array(
                'name'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'value'      => array('type' => 'text', 'after' => 'name')
            ),
            array(
                'name'       => array('fields' => array('name'), 'type' => 'UNIQUE')
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_egov_configuration` (`name`, `value`)
            VALUES  ('set_calendar_background', '#FFFFFFF'),
                    ('set_calendar_border', '#C9C9C9'),
                    ('set_calendar_color_1', '#D5FFDA'),
                    ('set_calendar_color_2', '#F7FFB4'),
                    ('set_calendar_color_3', '#FFAEAE'),
                    ('set_calendar_date_desc', '(Das Datum wird durch das Anklicken im Kalender übernommen.)'),
                    ('set_calendar_date_label', 'Reservieren für das ausgewählte Datum'),
                    ('set_calendar_legende_1', 'Freie Tage'),
                    ('set_calendar_legende_2', 'Teilweise reserviert'),
                    ('set_calendar_legende_3', 'Reserviert'),
                    ('set_orderentry_email', 'Diese Daten wurden eingegeben:\r\n\r\n[[ORDER_VALUE]]\r\n'),
                    ('set_orderentry_name', 'Contrexx Demo Webseite'),
                    ('set_orderentry_recipient', 'info@example.com'),
                    ('set_orderentry_sender', 'info@example.com'),
                    ('set_orderentry_subject', 'Bestellung/Anfrage für [[PRODUCT_NAME]] eingegangen'),
                    ('set_paypal_currency', 'CHF'),
                    ('set_paypal_email', 'demo'),
                    ('set_paypal_ipn', '1'),
                    ('set_recipient_email', ''),
                    ('set_sender_email', 'info@example.com'),
                    ('set_sender_name', 'Contrexx Demo'),
                    ('set_state_email', 'Guten Tag\r\n\r\nHerzlichen Dank für Ihren Besuch bei der Contrexx Demo Webseite.\r\nIhre Bestellung/Anfrage wurde bearbeitet. Falls es sich um ein Download Produkt handelt, finden Sie ihre Bestellung im Anhang.\r\n\r\nIhre Angaben:\r\n[[ORDER_VALUE]]\r\n\r\nFreundliche Grüsse\r\nIhr Online-Team'),
                    ('set_state_subject', 'Bestellung/Anfrage: [[PRODUCT_NAME]]')
            ON DUPLICATE KEY UPDATE `name` = `name`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_egov_orders',
            array(
                'order_id'           => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'order_date'         => array('type' => 'timestamp', 'notnull' => true, 'default' => '0000-00-00 00:00:00', 'after' => 'order_id'),
                'order_ip'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'order_date'),
                'order_product'      => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'order_ip'),
                'order_values'       => array('type' => 'text', 'after' => 'order_product'),
                'order_state'        => array('type' => 'TINYINT(4)', 'notnull' => true, 'default' => '0', 'after' => 'order_values'),
                'order_quant'        => array('type' => 'TINYINT(4)', 'notnull' => true, 'default' => '1', 'after' => 'order_state')
            ),
            array(
                'order_product'      => array('fields' => array('order_product'))
            ),
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_egov_product_calendar',
            array(
                'calendar_id'            => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'calendar_product'       => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'calendar_id'),
                'calendar_order'         => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'calendar_product'),
                'calendar_day'           => array('type' => 'INT(2)', 'notnull' => true, 'default' => '0', 'after' => 'calendar_order'),
                'calendar_month'         => array('type' => 'INT(2)', 'zerofill' => true, 'default' => '00', 'after' => 'calendar_day'),
                'calendar_year'          => array('type' => 'INT(4)', 'notnull' => true, 'default' => '0', 'after' => 'calendar_month'),
                'calendar_act'           => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'calendar_year')
            ),
            array(
                'calendar_product'       => array('fields' => array('calendar_product'))
            ),
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_egov_product_fields',
            array(
                'id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'product'        => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'name'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'product'),
                'type'           => array('type' => 'ENUM(\'text\',\'label\',\'checkbox\',\'checkboxGroup\',\'file\',\'hidden\',\'password\',\'radio\',\'select\',\'textarea\')', 'notnull' => true, 'default' => 'text', 'after' => 'name'),
                'attributes'     => array('type' => 'text', 'after' => 'type'),
                'is_required'    => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0', 'after' => 'attributes'),
                'check_type'     => array('type' => 'INT(3)', 'notnull' => true, 'default' => '1', 'after' => 'is_required'),
                'order_id'       => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'check_type')
            ),
            array(
                'product'        => array('fields' => array('product'))
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_egov_product_fields` (`id`, `product`, `name`, `type`, `attributes`, `is_required`, `check_type`, `order_id`)
            VALUES  (7, 3, 'Anrede', 'select', 'Bitte w&auml;hlen Sie,Frau,Herr,Herr und Frau', '0', 1, 0),
                    (8, 3, 'Vorname', 'text', '', '1', 1, 1),
                    (9, 3, 'Nachname', 'text', '', '1', 1, 2),
                    (10, 3, 'Firma', 'text', '', '0', 1, 3),
                    (11, 3, 'Zusatz', 'text', '', '0', 1, 4),
                    (12, 3, 'Strasse', 'text', '', '0', 1, 5),
                    (13, 3, 'Nummer', 'text', '', '0', 1, 6),
                    (14, 3, 'Postleitzahl', 'text', '', '0', 1, 7),
                    (15, 3, 'Ort', 'text', '', '0', 1, 8),
                    (16, 3, 'Land', 'text', '', '0', 1, 9),
                    (17, 3, 'E-Mail', 'text', '', '1', 1, 10),
                    (18, 3, 'Bemerkungen', 'textarea', '', '0', 1, 11)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_egov_products',
            array(
                'product_id'                 => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'product_autostatus'         => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'product_id'),
                'product_name'               => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'product_autostatus'),
                'product_desc'               => array('type' => 'text', 'after' => 'product_name'),
                'product_price'              => array('type' => 'DECIMAL(11,2)', 'notnull' => true, 'default' => '0.00', 'after' => 'product_desc'),
                'product_per_day'            => array('type' => 'ENUM(\'yes\',\'no\')', 'notnull' => true, 'default' => 'no', 'after' => 'product_price'),
                'product_quantity'           => array('type' => 'TINYINT(2)', 'notnull' => true, 'default' => '0', 'after' => 'product_per_day'),
                'product_quantity_limit'     => array('type' => 'TINYINT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'product_quantity'),
                'product_target_email'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'product_quantity_limit'),
                'product_target_url'         => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'product_target_email'),
                'product_message'            => array('type' => 'text', 'after' => 'product_target_url'),
                'product_status'             => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '1', 'after' => 'product_message'),
                'product_electro'            => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'product_status'),
                'product_file'               => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'product_electro'),
                'product_sender_name'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'product_file'),
                'product_sender_email'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'product_sender_name'),
                'product_target_subject'     => array('type' => 'VARCHAR(255)', 'after' => 'product_sender_email'),
                'product_target_body'        => array('type' => 'text', 'after' => 'product_target_subject'),
                'product_paypal'             => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'product_target_body'),
                'product_paypal_sandbox'     => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'product_paypal'),
                'product_paypal_currency'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'product_paypal_sandbox'),
                'product_orderby'            => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'product_paypal_currency'),
                'yellowpay'                  => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'product_orderby'),
                'alternative_names'          => array('type' => 'text', 'after' => 'yellowpay')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_egov_products` (`product_id`, `product_autostatus`, `product_name`, `product_desc`, `product_price`, `product_per_day`, `product_quantity`, `product_quantity_limit`, `product_target_email`, `product_target_url`, `product_message`, `product_status`, `product_electro`, `product_file`, `product_sender_name`, `product_sender_email`, `product_target_subject`, `product_target_body`, `product_paypal`, `product_paypal_sandbox`, `product_paypal_currency`, `product_orderby`, `yellowpay`, `alternative_names`)
            VALUES (3, 1, 'Produkteschulung Contrexx WCMS Version 2.0 (limitierte Plätze)', 'Produkteschulung in Thun (Schweiz).<br />', '299.00', 'yes', 20, 1, 'info@example.com', '', '<p>Besten Dank f&uuml;r Ihre Anmeldung!<br />\r\nSie erhalten in K&uuml;rze eine E-Mail Nachricht mit den wichtigsten Informationen.</p>', 1, 0, '', 'Contrexx Demo', 'info@example.com', 'Bestellung/Anfrage: [[PRODUCT_NAME]]', 'Guten Tag\r\n\r\nHerzlichen Dank für Ihren Besuch bei der Contrexx Demo Webseite.\r\nIhre Bestellung/Anfrage wurde bearbeitet. Falls es sich um ein Download Produkt handelt, finden Sie ihre Bestellung im Anhang.\r\n\r\nIhre Angaben:\r\n[[ORDER_VALUE]]\r\n\r\nFreundliche Grüsse\r\nIhr Online-Team', 0, 'demo', 'CHF', 0, 0, '')
            ON DUPLICATE KEY UPDATE `product_id` = `product_id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_egov_settings',
            array(
                'set_id'                         => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0'),
                'set_sender_name'                => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'set_id'),
                'set_sender_email'               => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'set_sender_name'),
                'set_recipient_email'            => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'set_sender_email'),
                'set_state_subject'              => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'set_recipient_email'),
                'set_state_email'                => array('type' => 'text', 'after' => 'set_state_subject'),
                'set_calendar_color_1'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'set_state_email'),
                'set_calendar_color_2'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'set_calendar_color_1'),
                'set_calendar_color_3'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'set_calendar_color_2'),
                'set_calendar_legende_1'         => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'set_calendar_color_3'),
                'set_calendar_legende_2'         => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'set_calendar_legende_1'),
                'set_calendar_legende_3'         => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'set_calendar_legende_2'),
                'set_calendar_background'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'set_calendar_legende_3'),
                'set_calendar_border'            => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'set_calendar_background'),
                'set_calendar_date_label'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'set_calendar_border'),
                'set_calendar_date_desc'         => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'set_calendar_date_label'),
                'set_orderentry_subject'         => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'set_calendar_date_desc'),
                'set_orderentry_email'           => array('type' => 'text', 'after' => 'set_orderentry_subject'),
                'set_orderentry_name'            => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'set_orderentry_email'),
                'set_orderentry_sender'          => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'set_orderentry_name'),
                'set_orderentry_recipient'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'set_orderentry_sender'),
                'set_paypal_email'               => array('type' => 'text', 'after' => 'set_orderentry_recipient'),
                'set_paypal_currency'            => array('type' => 'text', 'after' => 'set_paypal_email'),
                'set_paypal_ipn'                 => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'set_paypal_currency')
            ),
            array(
                'set_id'                         => array('fields' => array('set_id'))
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_egov_settings` (`set_id`, `set_sender_name`, `set_sender_email`, `set_recipient_email`, `set_state_subject`, `set_state_email`, `set_calendar_color_1`, `set_calendar_color_2`, `set_calendar_color_3`, `set_calendar_legende_1`, `set_calendar_legende_2`, `set_calendar_legende_3`, `set_calendar_background`, `set_calendar_border`, `set_calendar_date_label`, `set_calendar_date_desc`, `set_orderentry_subject`, `set_orderentry_email`, `set_orderentry_name`, `set_orderentry_sender`, `set_orderentry_recipient`, `set_paypal_email`, `set_paypal_currency`, `set_paypal_ipn`)
            VALUES (1, 'Contrexx Demo', '', '', 'Bestellung/Anfrage: [[PRODUCT_NAME]]', 'Guten Tag\r\n\r\nHerzlichen Dank für Ihren Besuch bei der Contrexx Demo Webseite.\r\nIhre Bestellung/Anfrage wurde bearbeitet. Falls es sich um ein Download Produkt handelt, finden Sie ihre Bestellung im Anhang.\r\n\r\nIhre Angaben:\r\n[[ORDER_VALUE]]\r\n\r\nFreundliche Grüsse\r\nIhr Online-Team', '#D5FFDA', '#F7FFB4', '#FFAEAE', 'Freie Tage', 'Teilweise Reserviert', 'Reserviert', '#FFFFFFF', '#C9C9C9', 'Reservieren für das ausgewählte Datum', '(Das Datum wird durch das Anklicken im Kalender übernommen.)', 'Bestellung/Anfrage für [[PRODUCT_NAME]] eingegangen', 'Diese Daten wurden eingegeben:\r\n\r\n[[ORDER_VALUE]]\r\n', 'Contrexx Demo Webseite', '', '', '', '', 0)
            ON DUPLICATE KEY UPDATE `set_id` = `set_id`
        ");

    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}
