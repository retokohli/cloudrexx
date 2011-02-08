<?php

function _dataUpdate()
{
    global $objDatabase;

    $arrTables  = $objDatabase->MetaTables('TABLES');
    if (!sizeof($arrTables)) {
        return _databaseError("MetaTables('TABLES')", 'Could not read Table metadata');
    }

    // Create neccessary tables if not present
    $tables = array(
        'module_data_categories' => "CREATE TABLE `".DBPREFIX."module_data_categories` (
              `category_id` int(4) unsigned NOT NULL default '0',
              `lang_id` int(2) unsigned NOT NULL default '0',
              `is_active` enum('0','1') NOT NULL default '1',
              `parent_id` int(10) unsigned NOT NULL default '0',
              `name` varchar(100) NOT NULL default '',
              `active` enum('0','1') NOT NULL default '1',
              `cmd` int(10) unsigned NOT NULL default '1',
              `action` enum('content','overlaybox','subcategories') NOT NULL default 'content',
              `sort` int(10) unsigned NOT NULL default '1',
              `box_height` int(10) unsigned NOT NULL default '500',
              `box_width` int(11) NOT NULL default '350',
              `template` text NOT NULL,
              PRIMARY KEY (`category_id`,`lang_id`)
            ) ENGINE=MyISAM",
        #################################################################################
        'module_data_message_to_category' => "CREATE TABLE `".DBPREFIX."module_data_message_to_category` (
              `message_id` int(6) unsigned NOT NULL default '0',
              `category_id` int(4) unsigned NOT NULL default '0',
              `lang_id` int(2) unsigned NOT NULL default '0',
              PRIMARY KEY (`message_id`,`category_id`,`lang_id`),
              KEY `category_id` (`category_id`)
            ) ENGINE=MyISAM ",
        #################################################################################
        'module_data_messages' => "CREATE TABLE `".DBPREFIX."module_data_messages` (
              `message_id` int(6) unsigned NOT NULL auto_increment,
              `user_id` int(5) unsigned NOT NULL default '0',
              `time_created` int(14) unsigned NOT NULL default '0',
              `time_edited` int(14) unsigned NOT NULL default '0',
              `hits` int(7) unsigned NOT NULL default '0',
              `active` enum('0','1') NOT NULL default '1',
              `sort` int(10) unsigned NOT NULL default '1',
              `mode` set('normal','forward') NOT NULL default 'normal',
              `release_time` int(15) NOT NULL default '0',
              `release_time_end` int(15) NOT NULL default '0',
              PRIMARY KEY (`message_id`)
            ) ENGINE=MyISAM",
        #################################################################################
            'module_data_messages_lang' => "CREATE TABLE `contrexx_module_data_messages_lang` (
              `message_id` int(6) unsigned NOT NULL default '0',
              `lang_id` int(2) unsigned NOT NULL default '0',
              `is_active` enum('0','1')  NOT NULL default '1',
              `subject` varchar(250)  NOT NULL default '',
              `content` text  NOT NULL,
              `tags` varchar(250)  NOT NULL default '',
              `image` varchar(250)  NOT NULL default '',
              `thumbnail` varchar(250)  NOT NULL,
              `thumbnail_width` tinyint(3) unsigned NOT NULL default '0',
              `thumbnail_height` tinyint(3) unsigned NOT NULL default '0',
              `attachment` varchar(255)  NOT NULL default '',
              `attachment_description` varchar(255)  NOT NULL default 'normal',
              `mode` set('normal','forward')  NOT NULL default 'normal',
              `forward_url` varchar(255)  NOT NULL default '',
              `forward_target` varchar(40)  default NULL,
              PRIMARY KEY  (`message_id`,`lang_id`)
            ) ENGINE=MyISAM ",
        #################################################################################
        'module_data_placeholders' => "CREATE TABLE `".DBPREFIX."module_data_placeholders` (
              `id` int(10) unsigned NOT NULL auto_increment,
              `type` set('cat','entry') NOT NULL default '',
              `ref_id` int(11) NOT NULL default '0',
              `placeholder` varchar(255) NOT NULL default '',
              PRIMARY KEY (`id`),
              UNIQUE KEY `placeholder` (`placeholder`)
            ) ENGINE=MyISAM",
        #################################################################################
        'module_data_settings' => "CREATE TABLE `".DBPREFIX."module_data_settings` (
              `name` varchar(50) NOT NULL default '',
              `value` text NOT NULL,
              PRIMARY KEY (`name`)
            ) ENGINE=MyISAM"
    );

      ///////////////////////////////////////////////////////////////////
     // Create tables                                                 //
    ///////////////////////////////////////////////////////////////////
    foreach ($tables as $name => $query) {
        if (in_array(DBPREFIX.$name, $arrTables)) continue;
        if (!$objDatabase->Execute($query)) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

      ///////////////////////////////////////////////////////////////////
     // Backend areas                                                 //
    ///////////////////////////////////////////////////////////////////
    $insert = "INSERT INTO `".DBPREFIX."backend_areas` (
        `parent_area_id`,
        `type`,
        `area_name`,
        `is_active`,
        `uri`,
        `target`,
        `module_id`,
        `order_id`,
        `access_id`
    ) VALUES ";

    $q_chk_level1 = "SELECT 1 FROM `".DBPREFIX."backend_areas` WHERE `uri` = 'index.php?cmd=data'";
    $r_chk_level1 = $objDatabase->SelectLimit($q_chk_level1, 1);
    if ($r_chk_level1 !== false) {
        if ($r_chk_level1->RecordCount() == 0) {
            $query = "$insert ('1','navigation','TXT_DATA_MODULE','1','index.php?cmd=data','_self','48','5','122')";
            if (!$objDatabase->Execute($query)) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
           return _databaseError($q_chk_level1, $objDatabase->ErrorMsg());
    }

    $parent_id = $objDatabase->Insert_ID();
    $q_chk_level2 = "SELECT 1 FROM `".DBPREFIX."backend_areas` WHERE `uri` = 'index.php?cmd=data&act=manageEntry'";
    $r_chk_level2 = $objDatabase->SelectLimit($q_chk_level2, 1);
    if ($r_chk_level2 !== false) {
        if ($r_chk_level2->RecordCount() == 0) {
            $query = "$insert ('$parent_id','function','TXT_DATA_ENTRY_MANAGE_TITLE','1','index.php?cmd=data&act=manageEntry','_self','48','1','123')";
            if (!$objDatabase->Execute($query)) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
           return _databaseError($q_chk_level2, $objDatabase->ErrorMsg());
    }

      ///////////////////////////////////////////////////////////////////
     // Modules entry                                                 //
    ///////////////////////////////////////////////////////////////////
    $q_chk_module = "SELECT 1 FROM `".DBPREFIX."modules` WHERE `description_variable` = 'TXT_DATA_MODULE_DESCRIPTION'";
    $r_chk_module = $objDatabase->SelectLimit($q_chk_module, 1);
    if ($r_chk_module !== false) {
        if ($r_chk_module->RecordCount() == 0) {
            $query = "
                INSERT INTO `".DBPREFIX."modules` (
                    `id`, `name`, `description_variable`,
                    `status`, `is_required`, `is_core`
                ) VALUES (
                    '48', 'data', 'TXT_DATA_MODULE_DESCRIPTION',
                    'y', '0', '0'
                )
            ";
            if (!$objDatabase->Execute($query)) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($q_chk_module, $objDatabase->ErrorMsg());
    }

      ///////////////////////////////////////////////////////////////////
     // Settings entry                                                //
    ///////////////////////////////////////////////////////////////////
    $q_chk_settings = "SELECT 1 FROM `".DBPREFIX."settings` WHERE `setname`='dataUseModule'";
    $r_chk_settings = $objDatabase->SelectLimit($q_chk_settings, 1);
    if ($r_chk_settings !== false) {
        if ($r_chk_settings->RecordCount() == 0) {
            $query = "
                INSERT INTO `".DBPREFIX."settings`
                    (`setname`, `setvalue`, `setmodule`)
                VALUES
                    ('dataUseModule', '1', '48')
            ";
            if (!$objDatabase->Execute($query)) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($q_chk_settings, $objDatabase->ErrorMsg());
    }

    $settings_query = "
INSERT INTO `".DBPREFIX."module_data_settings` (`name`, `value`) VALUES
('data_block_activated', '0'),
('data_block_messages', '3'),
('data_comments_activated', '1'),
('data_comments_anonymous', '1'),
('data_comments_autoactivate', '1'),
('data_comments_editor', 'wysiwyg'),
('data_comments_notification', '1'),
('data_comments_timeout', '30'),
('data_entry_action', 'overlaybox'),
('data_general_introduction', '150'),
('data_rss_activated', '0'),
('data_rss_comments', '10'),
('data_rss_messages', '5'),
('data_tags_hitlist', '5'),
('data_target_cmd', '1'),
('data_template_category',
'<!-- BEGIN datalist_category -->
<!-- this displays the category and the subcategories -->
<div class=\\\"datalist_block\\\">
<dl>
  <!-- BEGIN category -->
  <dt class=\\\"cattitle\\\"><div class=\\\"bg\\\"><h4>[[CATTITLE]]</h4></div></dt>
  <dd class=\\\"catcontent\\\">
    <dl>
      <!-- BEGIN entry -->
      <dt>[[TITLE]]</dt>
      <dd>
        [[IMAGE]] [[CONTENT]] <a href=\\\"[[HREF]]\\\" [[CLASS]] [[TARGET]]>[[TXT_MORE]]</a>
        <br style=\\\"clear: both;\\\" />
      </dd>
      <!-- END entry -->
    </dl>
  </dd>
  <!-- END category -->
</dl>
</div>
<!-- END datalist_category -->
<!-- BEGIN datalist_single_category-->
<!-- this displays just the entries of the category -->
<div class=\\\"datalist_block\\\">
<dl>
  <!-- BEGIN single_entry -->
  <dt class=\\\"cattitle\\\"><div class=\\\"bg\\\"><h4>[[TITLE]]</h4></div></dt>
  <dd class=\\\"catcontent2\\\">
    [[IMAGE]] <p>[[CONTENT]] <a href=\\\"[[HREF]]\\\" [[CLASS]] [[TARGET]]>[[TXT_MORE]]</a></p>
    <div style=\\\"clear: both;\\\" />
  </dd>
  <!-- END single_entry -->
</dl>
</div>
<!-- END datalist_single_category -->
'),
('data_template_entry',
'<!-- BEGIN datalist_entry-->
<div class=\\\"datalist_block\\\">
<dl>
  <dt>[[TITLE]]</dt>
  <dd>
    [[IMAGE]] [[CONTENT]] <a href=\\\"[[HREF]]\\\" [[CLASS]]>[[TXT_MORE]]</a>
    <br style=\\\"clear: both;\\\" />
  </dd>
</dl>
</div>
<!-- END datalist_entry -->
    '),
('data_template_thickbox',
'<!-- BEGIN thickbox -->
<dl class=\\\"data_module\\\">
  <dt><h6 style=\\\"margin-bottom:10px;\\\">[[TITLE]]</h6></dt>
  <dd style=\\\"clear:left;\\\">
    <!-- BEGIN image -->
    <img src=\\\"[[PICTURE]]\\\" style=\\\"float: left; margin-right: 5px;\\\" />
    <!-- END image -->
    [[CONTENT]]
    <!-- BEGIN attachment -->
    <img src=\\\"/themes/default/images/arrow.gif\\\" width=\\\"16\\\" height=\\\"8\\\" />
    <a href=\\\"javascript:void(0);\\\" onclick=\\\"window.open(\\'[[HREF]]\\', \\'attachment\\');\\\">[[TXT_DOWNLOAD]]</a>
    <!-- END attachment -->
  </dd>
</dl>
<!--<br /><img src=\\\"/themes/default/images/arrow.gif\\\" width=\\\"16\\\" height=\\\"8\\\" /><a onclick=\\\"Javascript:window.print();\\\" style=\\\"cursor:pointer;\\\">Drucken</a>-->
<!-- END thickbox -->
'),
('data_thickbox_height', '450'),
('data_thickbox_width', '400'),
('data_voting_activated', '0');
";

      ///////////////////////////////////////////////////////////////////
     // data module settings                                          //
    ///////////////////////////////////////////////////////////////////
    $query = "SELECT COUNT(*) AS recordcount FROM `".DBPREFIX."module_data_settings`";
    $objResult = $objDatabase->Execute($query);
    if ($objResult === false) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }
    if ($objResult->fields['recordcount'] == 0) {
        // module_data_settings table is empty. Fill it with default data.
        if (!$objDatabase->Execute($settings_query)) {
            return _databaseError($settings_query, $objDatabase->ErrorMsg());
        }
    }



    /*********************************************************
    * EXTENSION:    Thunbmail Image & Attachment description *
    * ADDED:        Contrexx v2.1.0                             *
    *********************************************************/
    $arrColumns = $objDatabase->MetaColumnNames(DBPREFIX.'module_data_messages_lang');
    if ($arrColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_data_messages_lang'));
        return false;
    }

    if (!in_array('thumbnail', $arrColumns)) {
        $query = "ALTER TABLE `".DBPREFIX."module_data_messages_lang` ADD `thumbnail` VARCHAR( 250 ) NOT NULL AFTER `image`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }
    if (!in_array('thumbnail_width', $arrColumns)) {
        $query = "ALTER TABLE `".DBPREFIX."module_data_messages_lang` ADD `thumbnail_width` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `thumbnail`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }
    if (!in_array('thumbnail_height', $arrColumns)) {
        $query = "ALTER TABLE `".DBPREFIX."module_data_messages_lang` ADD `thumbnail_height` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `thumbnail_width`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }
    if (!in_array('attachment_description', $arrColumns)) {
        $query = "ALTER TABLE `".DBPREFIX."module_data_messages_lang` ADD `attachment_description` VARCHAR(255) NOT NULL DEFAULT '' AFTER `attachment`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    return true;
}

?>
