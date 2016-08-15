<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

function _mediadirUpdate()
{
    global $_ARRAYLANG, $_CORELANG, $objUpdate, $_CONFIG;

    //create / update tables
    try {
        if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')) {
            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_mediadir_categories',
                array(
                      'id'                     => array('type' => 'INT(7)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                        'parent_id'              => array('type' => 'INT(7)', 'after' => 'id'),
                        'order'                  => array('type' => 'INT(7)', 'after' => 'parent_id'),
                        'show_subcategories'     => array('type' => 'INT(11)', 'after' => 'order'),
                        'show_entries'           => array('type' => 'INT(1)', 'after' => 'show_subcategories'),
                        'picture'                => array('type' => 'mediumtext', 'after' => 'show_entries'),
                        'active'                 => array('type' => 'INT(1)', 'after' => 'picture')
                )
           );

           \Cx\Lib\UpdateUtil::table(
               DBPREFIX.'module_mediadir_categories_names',
               array(
                     'lang_id'                    => array('type' => 'INT(1)'),
                        'category_id'                => array('type' => 'INT(7)', 'after' => 'lang_id'),
                        'category_name'              => array('type' => 'VARCHAR(255)', 'after' => 'category_id'),
                        'category_description'       => array('type' => 'mediumtext', 'after' => 'category_name')
               ),
               array(
                        'lang_id'                    => array('fields' => array('lang_id')),
                        'category_id'                => array('fields' => array('category_id'))
               )
           );

          \Cx\Lib\UpdateUtil::table(
              DBPREFIX.'module_mediadir_comments',
              array(
                  'id'                 => array('type' => 'INT(7)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                  'entry_id'           => array('type' => 'INT(7)', 'after' => 'id'),
                  'added_by'           => array('type' => 'VARCHAR(255)', 'after' => 'entry_id'),
                  'date'               => array('type' => 'VARCHAR(100)', 'after' => 'added_by'),
                  'ip'                 => array('type' => 'VARCHAR(100)', 'after' => 'date'),
                  'name'               => array('type' => 'VARCHAR(255)', 'after' => 'ip'),
                  'mail'               => array('type' => 'VARCHAR(255)', 'after' => 'name'),
                  'url'                => array('type' => 'VARCHAR(255)', 'after' => 'mail'),
                  'notification'       => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'url'),
                  'comment'            => array('type' => 'mediumtext', 'after' => 'notification')
              )
          );

          \Cx\Lib\UpdateUtil::table(
              DBPREFIX.'module_mediadir_entries',
              array(
                  'id'                         => array('type' => 'INT(10)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                  'order'                      => array('type' => 'INT(7)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
                  'form_id'                    => array('type' => 'INT(7)', 'after' => 'order'),
                  'create_date'                => array('type' => 'INT(50)', 'after' => 'form_id'),
                  'update_date'                => array('type' => 'INT(50)', 'after' => 'create_date'),
                  'validate_date'              => array('type' => 'INT(50)', 'after' => 'update_date'),
                  'added_by'                   => array('type' => 'INT(10)', 'after' => 'validate_date'),
                  'updated_by'                 => array('type' => 'INT(10)', 'after' => 'added_by'),
                  'lang_id'                    => array('type' => 'INT(1)', 'after' => 'updated_by'),
                  'hits'                       => array('type' => 'INT(10)', 'after' => 'lang_id'),
                  'popular_hits'               => array('type' => 'INT(10)', 'after' => 'hits'),
                  'popular_date'               => array('type' => 'VARCHAR(20)', 'after' => 'popular_hits'),
                  'last_ip'                    => array('type' => 'VARCHAR(50)', 'after' => 'popular_date'),
                  'ready_to_confirm'           => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'last_ip'),
                  'confirmed'                  => array('type' => 'INT(1)', 'after' => 'ready_to_confirm'),
                  'active'                     => array('type' => 'INT(1)', 'after' => 'confirmed'),
                  'duration_type'              => array('type' => 'INT(1)', 'after' => 'active'),
                  'duration_start'             => array('type' => 'INT(50)', 'after' => 'duration_type'),
                  'duration_end'               => array('type' => 'INT(50)', 'after' => 'duration_start'),
                  'duration_notification'      => array('type' => 'INT(1)', 'after' => 'duration_end'),
                  'translation_status'         => array('type' => 'VARCHAR(255)', 'after' => 'duration_notification')
              ),
              array(
                    'lang_id'                    => array('fields' => array('lang_id')),
                  'active'                     => array('fields' => array('active'))
              )
          );

          \Cx\Lib\UpdateUtil::table(
              DBPREFIX.'module_mediadir_form_names',
              array(
                  'lang_id'                => array('type' => 'INT(1)'),
                  'form_id'                => array('type' => 'INT(7)', 'after' => 'lang_id'),
                  'form_name'              => array('type' => 'VARCHAR(255)', 'after' => 'form_id'),
                  'form_description'       => array('type' => 'mediumtext', 'notnull' => true, 'after' => 'form_name')
              )
          );
        }
        if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '5.0.0')) {
            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_mediadir_forms',
                array(
                    'id'                         => array('type' => 'INT(7)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'order'                      => array('type' => 'INT(7)', 'after' => 'id'),
                    'picture'                    => array('type' => 'mediumtext', 'after' => 'order'),
                    'active'                     => array('type' => 'INT(1)', 'after' => 'picture'),
                    'use_level'                  => array('type' => 'INT(1)', 'after' => 'active'),
                    'use_category'               => array('type' => 'INT(1)', 'after' => 'use_level'),
                    'use_ready_to_confirm'       => array('type' => 'INT(1)', 'after' => 'use_category'),
                    'entries_per_page'           => array('type' => 'INT(7)', 'notnull' => true, 'default' => '0', 'after' => 'use_ready_to_confirm'),
                    'cmd'                        => array('type' => 'VARCHAR(50)', 'after' => 'entries_per_page')
                )
            );
        }

        if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')) {
            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_mediadir_inputfield_names',
                array(
                    'lang_id'                => array('type' => 'INT(10)'),
                    'form_id'                => array('type' => 'INT(7)', 'after' => 'lang_id'),
                    'field_id'               => array('type' => 'INT(10)', 'after' => 'form_id'),
                    'field_name'             => array('type' => 'VARCHAR(255)', 'after' => 'field_id'),
                    'field_default_value'    => array('type' => 'mediumtext', 'after' => 'field_name'),
                    'field_info'             => array('type' => 'mediumtext', 'after' => 'field_default_value')
                    ),
                array(
                    'field_id'               => array('fields' => array('field_id')),
                    'lang_id'                => array('fields' => array('lang_id'))
                )
            );

            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_mediadir_inputfield_types',
                array(
                    'id'             => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'name'           => array('type' => 'VARCHAR(255)', 'after' => 'id'),
                    'active'         => array('type' => 'INT(1)', 'after' => 'name'),
                    'multi_lang'     => array('type' => 'INT(1)', 'after' => 'active'),
                    'exp_search'     => array('type' => 'INT(7)', 'after' => 'multi_lang'),
                    'dynamic'        => array('type' => 'INT(1)', 'after' => 'exp_search'),
                    'comment'        => array('type' => 'VARCHAR(255)', 'after' => 'dynamic')
                    ),
                array(
                    'name'           => array('fields' => array('name'), 'type' => 'UNIQUE'),
                )
            );

            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_mediadir_inputfield_verifications',
                array(
                    'id'         => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'name'       => array('type' => 'VARCHAR(255)', 'after' => 'id'),
                    'regex'      => array('type' => 'VARCHAR(255)', 'after' => 'name')
                    ),
                array(
                    'name'       => array('fields' => array('name'), 'type' => 'UNIQUE')
                )
            );

            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_mediadir_level_names',
                array(
                    'lang_id'                => array('type' => 'INT(1)'),
                    'level_id'               => array('type' => 'INT(7)', 'after' => 'lang_id'),
                    'level_name'             => array('type' => 'VARCHAR(255)', 'after' => 'level_id'),
                    'level_description'      => array('type' => 'mediumtext', 'after' => 'level_name')
                ),
                array(
                    'lang_id'                => array('fields' => array('lang_id')),
                    'category_id'            => array('fields' => array('level_id'))
                )
            );

            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_mediadir_levels',
                array(
                    'id'                 => array('type' => 'INT(7)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'parent_id'          => array('type' => 'INT(7)', 'after' => 'id'),
                    'order'              => array('type' => 'INT(7)', 'after' => 'parent_id'),
                    'show_sublevels'     => array('type' => 'INT(11)', 'after' => 'order'),
                    'show_categories'    => array('type' => 'INT(1)', 'after' => 'show_sublevels'),
                    'show_entries'       => array('type' => 'INT(1)', 'after' => 'show_categories'),
                    'picture'            => array('type' => 'mediumtext', 'after' => 'show_entries'),
                    'active'             => array('type' => 'INT(1)', 'after' => 'picture')
                )
            );

            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_mediadir_mail_actions',
                array(
                    'id'                     => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'name'                   => array('type' => 'VARCHAR(255)', 'after' => 'id'),
                    'default_recipient'      => array('type' => 'ENUM(\'admin\',\'author\')', 'after' => 'name'),
                    'need_auth'              => array('type' => 'INT(11)', 'after' => 'default_recipient')
                )
            );

            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_mediadir_mails',
                array(
                    'id'             => array('type' => 'INT(7)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'title'          => array('type' => 'VARCHAR(255)', 'after' => 'id'),
                    'content'        => array('type' => 'longtext', 'after' => 'title'),
                    'recipients'     => array('type' => 'mediumtext', 'after' => 'content'),
                    'lang_id'        => array('type' => 'INT(1)', 'after' => 'recipients'),
                    'action_id'      => array('type' => 'INT(1)', 'after' => 'lang_id'),
                    'is_default'     => array('type' => 'INT(1)', 'after' => 'action_id'),
                    'active'         => array('type' => 'INT(1)', 'after' => 'is_default')
                )
            );

            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_mediadir_masks',
                array(
                    'id'         => array('type' => 'INT(7)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'title'      => array('type' => 'VARCHAR(255)', 'after' => 'id'),
                    'fields'     => array('type' => 'mediumtext', 'after' => 'title'),
                    'active'     => array('type' => 'INT(11)', 'after' => 'fields'),
                    'form_id'    => array('type' => 'INT(11)', 'after' => 'active')
                )
            );

            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_mediadir_order_rel_forms_selectors',
                array(
                    'selector_id'        => array('type' => 'INT(7)'),
                    'form_id'            => array('type' => 'INT(7)', 'after' => 'selector_id'),
                    'selector_order'     => array('type' => 'INT(7)', 'after' => 'form_id'),
                    'exp_search'         => array('type' => 'INT(1)', 'notnull' => true, 'after' => 'selector_order')
                )
            );

            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_mediadir_rel_entry_categories',
                array(
                    'entry_id'       => array('type' => 'INT(10)'),
                    'category_id'    => array('type' => 'INT(10)', 'after' => 'entry_id')
                ),
                array(
                    'entry_id'       => array('fields' => array('entry_id')),
                    'category_id'    => array('fields' => array('category_id'))
                )
            );
        }


        if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.1.2')) {
            \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_mediadir_inputfields',
                array(
                    'id'                 => array('type' => 'INT(10)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'form'               => array('type' => 'INT(7)', 'after' => 'id'),
                    'type'               => array('type' => 'INT(10)', 'after' => 'form'),
                    'verification'       => array('type' => 'INT(10)', 'after' => 'type'),
                    'search'             => array('type' => 'INT(10)', 'after' => 'verification'),
                    'required'           => array('type' => 'INT(10)', 'after' => 'search'),
                    'order'              => array('type' => 'INT(10)', 'after' => 'required'),
                    'show_in'            => array('type' => 'INT(10)', 'after' => 'order'),
                    'context_type'       => array('type' => 'ENUM(\'none\',\'title\',\'content\',\'address\',\'zip\',\'city\',\'country\')', 'after' => 'show_in')
                )
            );
        }


        if (   $objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')
            || detectCx3Version() == 'rc1'
        ) {
            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_mediadir_rel_entry_inputfields_clean',
                array(
                    'id'             => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'entry_id'       => array('type' => 'INT(7)', 'after' => 'id'),
                    'lang_id'        => array('type' => 'INT(7)', 'after' => 'entry_id'),
                    'form_id'        => array('type' => 'INT(7)', 'after' => 'lang_id'),
                    'field_id'       => array('type' => 'INT(7)', 'after' => 'form_id'),
                    'value'          => array('type' => 'longtext', 'after' => 'field_id')
                ),
                array(
                    'value'          => array('fields' => array('value'), 'type' => 'FULLTEXT')
                )
            );

            \Cx\Lib\UpdateUtil::sql('
                INSERT INTO `'.DBPREFIX.'module_mediadir_rel_entry_inputfields_clean`
                SELECT NULL, `entry_id`, `lang_id`, `form_id`, `field_id`, `value`
                FROM `'.DBPREFIX.'module_mediadir_rel_entry_inputfields`
                GROUP BY `entry_id`, `form_id`, `field_id`, `lang_id`, `value`
            ');

            \Cx\Lib\UpdateUtil::sql('
                TRUNCATE `'.DBPREFIX.'module_mediadir_rel_entry_inputfields`
            ');

            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_mediadir_rel_entry_inputfields',
                array(
                    'entry_id'       => array('type' => 'INT(7)'),
                    'lang_id'        => array('type' => 'INT(7)', 'after' => 'entry_id'),
                    'form_id'        => array('type' => 'INT(7)', 'after' => 'lang_id'),
                    'field_id'       => array('type' => 'INT(7)', 'after' => 'form_id'),
                    'value'          => array('type' => 'longtext', 'after' => 'field_id')
                ),
                array(
                    'entry_id'       => array('fields' => array('entry_id','lang_id','form_id','field_id'), 'type' => 'UNIQUE'),
                    'value'          => array('fields' => array('value'), 'type' => 'FULLTEXT')
                )
            );

            \Cx\Lib\UpdateUtil::sql('
                INSERT IGNORE INTO `'.DBPREFIX.'module_mediadir_rel_entry_inputfields`
                SELECT `entry_id`, `lang_id`, `form_id`, `field_id`, `value`
                FROM `'.DBPREFIX.'module_mediadir_rel_entry_inputfields_clean`
                ORDER BY `id` DESC
            ');

            \Cx\Lib\UpdateUtil::sql('
                DROP TABLE `'.DBPREFIX.'module_mediadir_rel_entry_inputfields_clean`
            ');
        }


        if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')) {
            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_mediadir_rel_entry_levels',
                array(
                    'entry_id'       => array('type' => 'INT(10)'),
                    'level_id'       => array('type' => 'INT(10)', 'after' => 'entry_id')
                ),
                array(
                    'entry_id'       => array('fields' => array('entry_id')),
                    'category_id'    => array('fields' => array('level_id'))
                )
            );

            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_mediadir_settings',
                array(
                    'id'         => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'name'       => array('type' => 'VARCHAR(100)', 'after' => 'id'),
                    'value'      => array('type' => 'VARCHAR(255)', 'after' => 'name')
                ),
                array(
                    'name'       => array('fields' => array('name'), 'type' => 'UNIQUE')
                )
            );

            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_mediadir_settings_num_categories',
                array(
                    'group_id'           => array('type' => 'INT(1)'),
                    'num_categories'     => array('type' => 'VARCHAR(10)', 'notnull' => true, 'after' => 'group_id')
                )
            );

            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_mediadir_settings_num_entries',
                array(
                    'group_id'       => array('type' => 'INT(1)'),
                    'num_entries'    => array('type' => 'VARCHAR(10)', 'notnull' => true, 'default' => 'n', 'after' => 'group_id')
                )
            );

            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_mediadir_settings_num_levels',
                array(
                    'group_id'       => array('type' => 'INT(1)'),
                    'num_levels'     => array('type' => 'VARCHAR(10)', 'notnull' => true, 'after' => 'group_id')
                )
            );

            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_mediadir_settings_perm_group_forms',
                array(
                    'group_id'           => array('type' => 'INT(7)'),
                    'form_id'            => array('type' => 'INT(1)', 'after' => 'group_id'),
                    'status_group'       => array('type' => 'INT(1)', 'notnull' => true, 'after' => 'form_id')
                )
            );

            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_mediadir_votes',
                array(
                    'id'             => array('type' => 'INT(7)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'entry_id'       => array('type' => 'INT(7)', 'after' => 'id'),
                    'added_by'       => array('type' => 'VARCHAR(255)', 'after' => 'entry_id'),
                    'date'           => array('type' => 'VARCHAR(100)', 'after' => 'added_by'),
                    'ip'             => array('type' => 'VARCHAR(100)', 'after' => 'date'),
                    'vote'           => array('type' => 'INT(11)', 'after' => 'ip')
                )
            );
      
            // remove the script tag at the beginning of the mediadir pages
            \Cx\Lib\UpdateUtil::migrateContentPageUsingRegex(array('module' => 'mediadir'), '/^\s*(<script[^>]+>.+?Shadowbox.+?<\/script>)+/sm', '', array('content'), '3.0.4');
        
        }
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')) {
        //insert default settings
        try {
            //mediadir_settings
            $arrValues = array(array(1,'settingsShowCategoryDescription','1'),array(2,'settingsShowCategoryImage','1'),array(3,'settingsCategoryOrder','1'),array(4,'settingsShowLevels','1'),array(5,'settingsShowLevelDescription','0'),array(6,'settingsShowLevelImage','0'),array(7,'settingsLevelOrder','1'),array(8,'settingsConfirmNewEntries','1'),array(9,'categorySelectorOrder','9'),array(10,'levelSelectorOrder','10'),array(11,'settingsConfirmUpdatedEntries','0'),array(12,'settingsCountEntries','0'),array(13,'settingsThumbSize','120'),array(14,'settingsNumGalleryPics','10'),array(15,'settingsEncryptFilenames','1'),array(16,'settingsAllowAddEntries','1'),array(17,'settingsAllowDelEntries','1'),array(18,'settingsAllowEditEntries','1'),array(19,'settingsAddEntriesOnlyCommunity','1'),array(20,'settingsLatestNumXML','10'),array(21,'settingsLatestNumOverview','5'),array(22,'settingsLatestNumBackend','5'),array(23,'settingsLatestNumFrontend','10'),array(24,'settingsPopularNumFrontend','10'),array(25,'settingsPopularNumRestore','30'),array(26,'settingsLatestNumHeadlines','6'),array(27,'settingsGoogleMapStartposition','46.749647513758326,7.6300048828125,8'),array(28,'settingsAllowVotes','1'),array(29,'settingsVoteOnlyCommunity','0'),array(30,'settingsAllowComments','1'),array(31,'settingsCommentOnlyCommunity','0'),array(32,'settingsGoogleMapAllowKml','0'),array(33,'settingsShowEntriesInAllLang','1'),array(34,'settingsPagingNumEntries','10'),array(35,'settingsGoogleMapType','0'),array(36,'settingsClassificationPoints','5'),array(37,'settingsClassificationSearch','1'),array(38,'settingsEntryDisplaydurationType','1'),array(39,'settingsEntryDisplaydurationValue','0'),array(40,'settingsEntryDisplaydurationValueType','1'),array(41,'settingsEntryDisplaydurationNotification','0'),array(42,'categorySelectorExpSearch','9'),array(43,'levelSelectorExpSearch','10'),array(44,'settingsTranslationStatus','0'),array(45,'settingsReadyToConfirm','0'),array(46,'settingsImageFilesize','300'),array(47,'settingsActiveLanguages','1,2,3'),array(48,'settingsFrontendUseMultilang','0'),array(49,'settingsIndividualEntryOrder','0'));
            foreach($arrValues as $arrValue) {
                if(\Cx\Lib\UpdateUtil::sql('SELECT 1 FROM '.DBPREFIX.'module_mediadir_settings WHERE name="'.$arrValue[1].'"')->EOF) {
                    \Cx\Lib\UpdateUtil::sql('INSERT INTO '.DBPREFIX.'module_mediadir_settings VALUES('.$arrValue[0].',"'.$arrValue[1].'","'.$arrValue[2].'")');
                }
            }

            //mediadir_settings_num_categories
            $arrValues = array(array(3,'n'),array(4,'n'),array(5,'n'));
            foreach($arrValues as $arrValue) {
                if(\Cx\Lib\UpdateUtil::sql('SELECT 1 FROM '.DBPREFIX.'module_mediadir_settings_num_categories WHERE group_id='.$arrValue[0])->EOF) {
                    \Cx\Lib\UpdateUtil::sql('INSERT INTO '.DBPREFIX.'module_mediadir_settings_num_categories VALUES('.$arrValue[0].',"'.$arrValue[1].'")');
                }
            }

            //mediadir_settings_num_entries
            $arrValues = array(array(3,'n'),array(4,'n'),array(5,'n'));
            foreach($arrValues as $arrValue) {
                if(\Cx\Lib\UpdateUtil::sql('SELECT 1 FROM '.DBPREFIX.'module_mediadir_settings_num_entries WHERE group_id='.$arrValue[0])->EOF) {
                    \Cx\Lib\UpdateUtil::sql('INSERT INTO '.DBPREFIX.'module_mediadir_settings_num_entries VALUES('.$arrValue[0].',"'.$arrValue[1].'")');
                }
            }

            //mediadir_settings_num_levels
            $arrValues = array(array(3,'n'),array(4,'n'),array(5,'n'));
            foreach($arrValues as $arrValue) {
                if(\Cx\Lib\UpdateUtil::sql('SELECT 1 FROM '.DBPREFIX.'module_mediadir_settings_num_levels WHERE group_id='.$arrValue[0])->EOF) {
                    \Cx\Lib\UpdateUtil::sql('INSERT INTO '.DBPREFIX.'module_mediadir_settings_num_levels VALUES('.$arrValue[0].',"'.$arrValue[1].'")');
                }
            }

            //mediadir_inputfield_verifications
            $arrValues = array(array(1,'normal','.*'),array(2,'e-mail',"^[a-zäàáâöôüûñéè0-9!\\#\\$\\%\\&\\''\\*\\+\\/\\=\\?\\^_\\`\\{\\|\\}\\~-]+(?:\\.[a-zäàáâöôüûñéè0-9!\\#\\$\\%\\&\\" . '"' . "'\\*\\+\\/\\=\\?\\^_\\`\\{\\|\\}\\~-]+)*@(?:[a-zäàáâöôüûñéè0-9](?:[a-zäàáâöôüûñéè0-9-]*[a-zäàáâöôüûñéè0-9])?\\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$"),array(3,'url','^(?:(?:ht|f)tps?\:\/\/)?((([\wÄÀÁÂÖÔÜÛÑÉÈäàáâöôüûñéè\d-]{1,}\.)+[a-z]{2,})|((?:(?:25[0-5]|2[0-4]\d|[01]\d\d|\d?\d)(?:(\.?\d)\.)) {4}))(?:[\w\d]+)?(\/[\w\d\-\.\?\,\'\/\\\+\&\%\$\#\=\~]*)?$'),array(4,'letters','^[A-Za-zÄÀÁÂÖÔÜÛÑÉÈäàáâöôüûñéè\ ]*[A-Za-zÄÀÁÂÖÔÜÛÑÉÈäàáâöôüûñéè]+[A-Za-zÄÀÁÂÖÔÜÛÑÉÈäàáâöôüûñéè\ ]*$'),array(5,'numbers','^[0-9]*$'));
            foreach($arrValues as $arrValue) {
                if(\Cx\Lib\UpdateUtil::sql('SELECT 1 FROM '.DBPREFIX.'module_mediadir_inputfield_verifications WHERE name="'.$arrValue[1].'"')->EOF) {
                    \Cx\Lib\UpdateUtil::sql('INSERT INTO '.DBPREFIX.'module_mediadir_inputfield_verifications VALUES('.$arrValue[0].',"'.$arrValue[1].'","'.$arrValue[2].'")');
                } else {
                    \Cx\Lib\UpdateUtil::sql('UPDATE '.DBPREFIX.'module_mediadir_inputfield_verifications SET `regex`="'.$arrValue[2].'" WHERE `name`="'.$arrValue[1].'"');
                }
            }

            //mediadir_mail_actions
            $arrValues = array(array(1,'newEntry','admin',0),array(2,'entryAdded','author',1),array(3,'entryConfirmed','author',1),array(4,'entryVoted','author',1),array(5,'entryDeleted','author',1),array(6,'entryEdited','author',1),array(8,'newComment','author',1),array(9,'notificationDisplayduration','admin',0));
            foreach($arrValues as $arrValue) {
                if(\Cx\Lib\UpdateUtil::sql('SELECT 1 FROM '.DBPREFIX.'module_mediadir_mail_actions WHERE name="'.$arrValue[1].'"')->EOF) {
                    \Cx\Lib\UpdateUtil::sql('INSERT INTO '.DBPREFIX.'module_mediadir_mail_actions VALUES('.$arrValue[0].',"'.$arrValue[1].'","'.$arrValue[2].'",'.$arrValue[3].')');
                }
            }

            //only insert mails if the table is empty
            if(\Cx\Lib\UpdateUtil::sql('SELECT 1 FROM '.DBPREFIX.'module_mediadir_mails')->EOF) {
                \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_mails` (`id`, `title`, `content`, `recipients`, `lang_id`, `action_id`, `is_default`, `active`) VALUES
    ('23', '[[URL]] - Eintrag erfolgreich bearbeitet', 'Hallo [[FIRSTNAME]] [[LASTNAME]] ([[USERNAME]])

Ihr Eintrag mit dem Titel \"[[TITLE]]\" auf [[URL]] wurde erfolgreich bearbeitet. 

Benutzen Sie folgenden Link um direkt zu Ihrem Eintrag zu gelangen:
[[LINK]]

Freundliche Grüsse
[[URL]]-Team

-- 
Diese Nachricht wurde am [[DATE]] automatisch von Contrexx auf http://[[URL]] generiert.', '', '1', '6', '1', '0'),
('22', '[[URL]] - Eintrag erfolgreich gelöscht', 'Hallo [[FIRSTNAME]] [[LASTNAME]] ([[USERNAME]])

Ihr Eintrag mit dem Titel \"[[TITLE]]\" auf [[URL]] wurde erfolgreich gelöscht. 

Freundliche Grüsse
Ihr [[URL]]-Team

-- 
Diese Nachricht wurde am [[DATE]] automatisch von Contrexx auf http://[[URL]] generiert.', '', '1', '5', '1', '0'),
('21', '[[URL]] - Eintrag wurde bewertet', 'Hallo [[FIRSTNAME]] [[LASTNAME]] ([[USERNAME]])

Zu Ihrem Eintrag mit dem Titel \"[[TITLE]]\" auf [[URL]] wurde eine Bewertung abgegeben. 

Benutzen Sie folgenden Link um direkt zu Ihrem Eintrag zu gelangen:
[[LINK]]

Freundliche Grüsse
Ihr [[URL]]-Team

-- 
Diese Nachricht wurde am [[DATE]] automatisch von Contrexx auf http://[[URL]] generiert.', '', '1', '4', '1', '0'),
('20', '[[URL]] - Ihr Eintrag wurde aufgeschaltet', 'Guten Tag,

Ihr Eintrag \"[[TITLE]]\" wurde geprüft und ist ab sofort einsehbar.

Benutzen Sie folgenden Link um direkt zu ihrem Eintrag zu gelangen:
[[LINK]]


Freundliche Grüsse
Ihr [[URL]]-Team


-- 
Diese Nachricht wurde am [[DATE]] automatisch von Contrexx auf http://[[URL]] generiert.', '', '1', '3', '1', '0'),
('19', '[[URL]] - Eintrag erfolgteich eingetragen', 'Hallo [[FIRSTNAME]] [[LASTNAME]] ([[USERNAME]])

Ihr Eintrag mit dem Titel \"[[TITLE]]\" wurde auf [[URL]] erfolgreich eingetragen. 


Freundliche Grüsse
Ihr [[URL]]-Team

-- 
Diese Nachricht wurde am [[DATE]] automatisch von Contrexx auf http://[[URL]] generiert.', '', '1', '2', '1', '0'),
    ('24', '[[URL]] - Neuer Kommentar hinzugefügt', 'Hallo [[FIRSTNAME]] [[LASTNAME]] ([[USERNAME]])

Zu Ihrem Eintrag mit dem Titel \"[[TITLE]]\" auf [[URL]] wurde ein neuer Kommentar hinzugefügt. 

Benutzen Sie folgenden Link um direkt zu Ihrem Eintrag zu gelangen:
[[LINK]]

Freundliche Grüsse
Ihr [[URL]]-Team


-- 
Diese Nachricht wurde am [[DATE]] automatisch von Contrexx auf http://[[URL]] generiert.', '', '1', '8', '1', '0'),
('32', '[[URL]] - Neuer Eintrag zur Prüfung freigegeben', 'Guten Tag,

Auf http://[[URL]] wurde ein neuer Eintrag mit dem Titel \"[[TITLE]]\" erfasst. Bitte prüfen Sie diesen und geben Sie ihn gegebenenfalls frei.


-- 
Diese Nachricht wurde am [[DATE]] automatisch von Contrexx auf http://[[URL]] generiert.', '', '1', '1', '1', '0'),
    ('33', '[[URL]] - Die Anzeigedauer eines Eintrages läuft ab', 'Hallo Admin

Auf [[URL]] läuft in Kürze die Anzeigedauer des Eintrages \"[[TITLE]]\" ab.

Freundliche Grüsse
Ihr [[URL]]-Team

-- 
Diese Nachricht wurde am [[DATE]] automatisch von Contrexx auf http://[[URL]] generiert.', '', '1', '9', '1', '0');");
            }
        }
        catch (\Cx\Lib\UpdateException $e) {
            // we COULD do something else here..
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }


    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '5.0.0')) {
        try {
            // add new options
            \Cx\Lib\UpdateUtil::sql("INSERT IGNORE INTO `".DBPREFIX."module_mediadir_settings` (`name`, `value`) VALUES ('showLatestEntriesInOverview', '1')");
            \Cx\Lib\UpdateUtil::sql("INSERT IGNORE INTO `".DBPREFIX."module_mediadir_settings` (`name`, `value`) VALUES ('showLatestEntriesInWebdesignTmpl', '1')");


            // update inputfield_types
            \Cx\Lib\UpdateUtil::sql("TRUNCATE `".DBPREFIX."module_mediadir_inputfield_types`");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('1', 'text', '1', '1', '1', '0', '')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('2', 'textarea', '1', '1', '1', '0', '')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('3', 'dropdown', '1', '0', '1', '0', '')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('4', 'radio', '1', '0', '1', '0', '')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('5', 'checkbox', '1', '0', '1', '0', '')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('7', 'file', '1', '1', '0', '0', '')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('8', 'image', '1', '1', '0', '0', '')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('9', 'gallery', '0', '0', '0', '0', 'not yet developed')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('10', 'podcast', '0', '0', '0', '0', 'not yet developed')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('11', 'classification', '1', '0', '1', '0', '')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('12', 'link', '1', '1', '0', '0', '')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('13', 'linkGroup', '1', '1', '0', '0', '')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('14', 'rss', '0', '0', '0', '0', 'not yet developed')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('15', 'googleMap', '1', '0', '0', '0', '')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('16', 'addStep', '0', '0', '0', '0', '')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('17', 'fieldGroup', '0', '0', '0', '0', 'not yet developed')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('18', 'label', '0', '0', '0', '0', 'not yet developed')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('19', 'wysiwyg', '1', '1', '0', '0', '')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('20', 'mail', '1', '1', '0', '0', '')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('21', 'googleWeather', '1', '0', '0', '0', '')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('22', 'relation', '0', '0', '0', '0', 'developed for OSEC (unstable)')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('23', 'relation_group', '0', '0', '0', '0', 'developed for OSEC (unstable)')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('24', 'accounts', '0', '0', '0', '0', 'developed for OSEC (unstable)')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('25', 'country', '1', '0', '0', '0', '')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('26', 'productAttributes', '0', '0', '1', '0', '')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('27', 'downloads', '0', '1', '0', '1', 'developed for CADexchange.ch (unstable)')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('28', 'responsibles', '0', '1', '0', '1', 'developed for CADexchange.ch (unstable)')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('29', 'references', '0', '1', '0', '1', 'developed for CADexchange.ch (unstable)')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('30', 'title', '0', '0', '0', '0', 'developed for CADexchange.ch (unstable)')");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_mediadir_inputfield_types` (`id`, `name`, `active`, `multi_lang`, `exp_search`, `dynamic`, `comment`) VALUES ('31', 'range', '1', '0', '1', '0', '')");

            //following queries for changing the path from images/mediadir into images/MediaDir
            \Cx\Lib\UpdateUtil::sql("UPDATE `" . DBPREFIX . "module_mediadir_categories`
                                     SET `picture` = REPLACE(`picture`, 'images/mediadir', 'images/MediaDir')
                                     WHERE `picture` LIKE ('" . ASCMS_PATH_OFFSET . "/images/mediadir%')");
            \Cx\Lib\UpdateUtil::sql("UPDATE `" . DBPREFIX . "module_mediadir_forms`
                                     SET `picture` = REPLACE(`picture`, 'images/mediadir', 'images/MediaDir')
                                     WHERE `picture` LIKE ('" . ASCMS_PATH_OFFSET . "/images/mediadir%')");
            \Cx\Lib\UpdateUtil::sql("UPDATE `" . DBPREFIX . "module_mediadir_levels`
                                     SET `picture` = REPLACE(`picture`, 'images/mediadir', 'images/MediaDir')
                                     WHERE `picture` LIKE ('" . ASCMS_PATH_OFFSET . "/images/mediadir%')");
            \Cx\Lib\UpdateUtil::sql("UPDATE `" . DBPREFIX . "module_mediadir_rel_entry_inputfields`
                                     SET `value` = REPLACE(`value`, 'images/mediadir', 'images/MediaDir')
                                     WHERE `value` LIKE ('" . ASCMS_PATH_OFFSET . "/images/mediadir%')");
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }

        //Update script for moving the folder
        $imagePath       = ASCMS_DOCUMENT_ROOT . '/images';
        $sourceImagePath = $imagePath . '/mediadir';
        $targetImagePath = $imagePath . '/MediaDir';

        try {
            \Cx\Lib\UpdateUtil::migrateOldDirectory($sourceImagePath, $targetImagePath);
        } catch (\Exception $e) {
            \DBG::log($e->getMessage());
            setUpdateMsg(sprintf(
                $_ARRAYLANG['TXT_UNABLE_TO_MOVE_DIRECTORY'],
                $sourceImagePath, $targetImagePath
            ));
            return false;
        }
        // migrate path to images and media
        $pathsToMigrate = \Cx\Lib\UpdateUtil::getMigrationPaths();
        $attributes = array(
            'module_mediadir_rel_entry_inputfields' => 'value',
            'module_mediadir_categories'            => 'picture',
            'module_mediadir_levels'                => 'picture',
            'module_mediadir_mails'                 => 'content',
            'module_mediadir_categories_names'      => 'category_description',
            'module_mediadir_comments'              => 'comment',
            'module_mediadir_forms'                 => 'picture',
            'module_mediadir_level_names'           => 'level_description',
        );
        try {
            foreach ($attributes as $table => $attribute) {
                foreach ($pathsToMigrate as $oldPath => $newPath) {
                    \Cx\Lib\UpdateUtil::migratePath(
                        '`' . DBPREFIX . $table . '`',
                        '`' . $attribute . '`',
                        $oldPath,
                        $newPath
                    );
                }
            }
        } catch (\Cx\Lib\Update_DatabaseException $e) {
            \DBG::log($e->getMessage());
            setUpdateMsg(sprintf(
                $_ARRAYLANG['TXT_UNABLE_TO_MIGRATE_MEDIA_PATH'],
                'Medienverzeichnis (MediaDir)'
            ));
            return false;
        }
    }
    return true;
}

function tryButDontWorry($sql) {
    try {
        \Cx\Lib\UpdateUtil::sql($sql);
    }
    catch (\Cx\Lib\UpdateException $e) {
        //nothing.
    }
}
