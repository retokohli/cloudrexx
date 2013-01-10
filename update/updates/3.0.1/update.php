<?php

require_once UPDATE_PATH . '/lib/FRAMEWORK/FileSystem/FileInterface.interface.php';
require_once UPDATE_PATH . '/lib/FRAMEWORK/FileSystem/File.class.php';
require_once UPDATE_PATH . '/lib/FRAMEWORK/FileSystem/FileSystem.class.php';
require_once UPDATE_PATH . '/lib/FRAMEWORK/FileSystem/FileSystemFile.class.php';
require_once UPDATE_PATH . '/lib/FRAMEWORK/FileSystem/FTPFile.class.php';

function executeContrexxUpdate($updateRepository = true, $updateBackendAreas = true, $updateModules = true) {
    global $_ARRAYLANG, $_CORELANG, $_CONFIG, $objDatabase, $objUpdate;

    $_SESSION['contrexx_update']['copyFilesFinished'] = !empty($_SESSION['contrexx_update']['copyFilesFinished']) ? $_SESSION['contrexx_update']['copyFilesFinished'] : false;
    
    // Copy cx files to the root directory
    if (!$_SESSION['contrexx_update']['copyFilesFinished']) {
        if (!copyCxFilesToRoot(dirname(__FILE__) . '/cx_files', ASCMS_PATH . ASCMS_PATH_OFFSET)) {
            return false;
        }
        $_SESSION['contrexx_update']['copyFilesFinished'] = true;

        // we need to stop the script here to force a reinitialization of the update system
        // this is required so that the new constants from config/set_constants.php are loaded
        setUpdateMsg($_CORELANG['TXT_UPDATE_PROCESS_HALTED'], 'title');
        setUpdateMsg($_CORELANG['TXT_UPDATE_PROCESS_HALTED_TIME_MSG'].'<br />', 'msg');
        setUpdateMsg('Installation der neuen Dateien abgeschlossen.<br /><br />', 'msg');
        setUpdateMsg('<input type="submit" value="'.$_CORELANG['TXT_CONTINUE_UPDATE'].'" name="updateNext" /><input type="hidden" name="processUpdate" id="processUpdate" />', 'button');
        return false;
    }
    unset($_SESSION['contrexx_update']['copiedCxFilesIndex']);
    
    /**
     * This needs to be initialized before loading config/doctrine.php
     * Because we overwrite the Gedmo model (so we need to load our model
     * before doctrine loads the Gedmo one)
     */
    require_once(ASCMS_CORE_PATH . '/ClassLoader/ClassLoader.class.php');
    $cl = new \Cx\Core\ClassLoader\ClassLoader(ASCMS_DOCUMENT_ROOT, true);
    Env::set('ClassLoader', $cl);
    
    // Doctrine configuration
    $incDoctrineStatus = require_once(UPDATE_PATH . '/config/doctrine.php');
    Env::set('incDoctrineStatus', $incDoctrineStatus);
    
    $userData = array(
        'id'   => $_SESSION['contrexx_update']['user_id'],
        'name' => $_SESSION['contrexx_update']['username'],
    );
    $loggableListener = \Env::get('loggableListener');
    $loggableListener->setUsername(json_encode($userData));
    
    // Reinitialize FWLanguage. Now with fallback (doctrine).
    FWLanguage::init();
    
    if (!isset($_SESSION['contrexx_update']['update']['done'])) {
        $_SESSION['contrexx_update']['update']['done'] = array();
    }
    
    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')) {
        DBG::msg('Installed version: '.$_CONFIG['coreCmsVersion']);
        Env::get('ClassLoader')->loadFile(dirname(__FILE__) . '/ContentMigration.class.php');
        $contentMigration = new \Cx\Update\Cx_3_0_1\ContentMigration();
        
        // Migrate statistics - this must be done before migrating to the new content architecture
        if (empty($_SESSION['contrexx_update']['content_stats'])) {
            DBG::msg('Migrate stats');
            if ($status = $contentMigration->migrateStatistics()) {
                $_SESSION['contrexx_update']['content_stats'] = true;
                if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                    return false;
                }
            } else {
                return false;
            }
        }
        
        // Migrate content
        if (empty($_SESSION['contrexx_update']['content_migrated'])) {
            DBG::msg('Migrate content');
            if ($status = $contentMigration->migrate()) {
                $_SESSION['contrexx_update']['content_migrated'] = true;
                if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                    return false;
                }
            } else {
                return false;
            }
        }
        
        // Migrate aliases
        if (empty($_SESSION['contrexx_update']['aliases_migrated'])) {
            DBG::msg('Migrate aliases');
            if ($status = $contentMigration->migrateAliases()) {
                $_SESSION['contrexx_update']['aliases_migrated'] = true;
                if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                    return false;
                }
            } else {
                return false;
            }
        }
        
        // Group pages if more than one language
        if (empty($_SESSION['contrexx_update']['pages_grouped'])) {
            DBG::msg('Group pages');
            $pageGrouping = $contentMigration->pageGrouping();
            
            if ($pageGrouping === true) {
                $_SESSION['contrexx_update']['pages_grouped'] = true;
                if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                    return false;
                }
            } else if ($pageGrouping === false) {
                return false;
            } else if (!empty($pageGrouping)) {
                $arrDialogData = array(
                    'langs'        => $contentMigration->langs,
                    'similarPages' => $contentMigration->similarPages,
                    'defaultLang'  => $contentMigration::$defaultLang,
                );
                
                setUpdateMsg('Inhaltsseiten gruppieren', 'title');
                setUpdateMsg($pageGrouping, 'msg');
                setUpdateMsg('<input type="submit" value="' . $_CORELANG['TXT_UPDATE_NEXT'] . '" name="updateNext" /><input type="hidden" name="processUpdate" id="processUpdate" />', 'button');
                setUpdateMsg($arrDialogData, 'dialog');
                return false;
            }
        }

        // Drop old tables
        if (empty($_SESSION['contrexx_update']['old_tables_dropped'])) {
            if ($contentMigration->dropOldTables()) {
                $_SESSION['contrexx_update']['old_tables_dropped'] = true;
                if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                    return false;
                }
            }
        }
        
        // Migrate blocks
        if (empty($_SESSION['contrexx_update']['blocks_migrated'])) {
            DBG::msg('Migrate blocks');
            if ($contentMigration->migrateBlocks()) {
                $_SESSION['contrexx_update']['blocks_migrated'] = true;
                if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                    return false;
                }
            } else {
                return false;
            }
        }
    } else {
        // we are updating from 3.0.0 rc1, rc2, stable or 3.0.0.1
        if (!include_once(dirname(__FILE__) . '/updateRc.php')) {
            setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/updateRc.php'));
            return false;
        }
        
        $arrUpdate = $objUpdate->getLoadedVersionInfo();
        $_CONFIG['coreCmsVersion'] = $arrUpdate['cmsVersion'];

        if (!in_array('coreLicense', $_SESSION['contrexx_update']['update']['done'])) {
            $lupd = new License();
            $result = $lupd->update();
            // ignore error to allow offline installations
            /*if ($result === false) {
                if (empty($objUpdate->arrStatusMsg['title'])) {
                    setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_LICENSE_DATA']), 'title');
                }
                return false;
            } else {*/
                $_SESSION['contrexx_update']['update']['done'][] = 'coreLicense';
            //}
        }
        
        _response();

        return true;
    }
    
    $arrDirs = array('core_module', 'module');
    $updateStatus = true;

    if (!include_once(dirname(__FILE__) . '/components/core/core.php')) {
        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/core.php'));
        return false;
    } elseif (UPDATE_UTF8 && !include_once(dirname(__FILE__) . '/components/core/utf8.php')) {
        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/utf8.php'));
        return false;
    } elseif (!include_once(dirname(__FILE__) . '/components/core/backendAreas.php')) {
        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/backendAreas.php'));
        return false;
    } elseif (!include_once(dirname(__FILE__) . '/components/core/modules.php')) {
        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/modules.php'));
        return false;
    } elseif (!include_once(dirname(__FILE__) . '/components/core/settings.php')) {
        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/settings.php'));
        return false;
    }

    if (!in_array('configSettings', $_SESSION['contrexx_update']['update']['done'])) {
        $result = _writeNewConfigurationFile();
        if ($result === false) {
            return false;
        }

        if (UPDATE_UTF8) {
            $result = _utf8Update();
            if ($result === false) {
                if (empty($objUpdate->arrStatusMsg['title'])) {
                    setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UTF_CONVERSION']), 'title');
                }
                return false;
            }
        }

        $result = _writeNewConfigurationFile(true);
        if ($result === false) {
            if (empty($objUpdate->arrStatusMsg['title'])) {
                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UTF_CONVERSION']), 'title');
            }
            return false;
        } else {
            $_SESSION['contrexx_update']['update']['done'][] = 'configSettings';
        }
    }

    if (UPDATE_UTF8) {
        $query = 'SET CHARACTER SET utf8';
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if (!in_array('coreUpdate', $_SESSION['contrexx_update']['update']['done'])) {
        $result = _coreUpdate();
        if ($result === false) {
            if (empty($objUpdate->arrStatusMsg['title'])) {
                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_CORE_SYSTEM']), 'title');
            }
            return false;
        } else {
            $_SESSION['contrexx_update']['update']['done'][] = 'coreUpdate';
        }
    }


    $missedModules = array();
    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')) {
        $missedModules = getMissedModules();
        $conflictedModules = getConflictedModules($missedModules);
        if (!empty($conflictedModules)) {
            $conflictedModulesList = '';
            foreach ($conflictedModules as $moduleName => $moduleTables) {
                $conflictedModulesList = '<li><strong>'.$moduleName.':</strong> '.implode(', ', $moduleTables).'</li>';
            }
            setUpdateMsg($_CORELANG['TXT_CONFLICTED_MODULES_TITLE'], 'title');
            setUpdateMsg($_CORELANG['TXT_CONFLICTED_MODULES_DESCRIPTION'].'<ul>'.$conflictedModulesList.'</ul>', 'msg');
            setUpdateMsg('<input type="submit" value="'.$_CORELANG['TXT_UPDATE_TRY_AGAIN'].'" name="updateNext" /><input type="hidden" name="processUpdate" id="processUpdate" />', 'button');
            return false;
        }
    }
    foreach ($arrDirs as $dir) {
        $dh = opendir(dirname(__FILE__).'/components/'.$dir);
        if ($dh) {
            while (($file = readdir($dh)) !== false) {
                if (!in_array($file, $_SESSION['contrexx_update']['update']['done'])) {
                    $fileInfo = pathinfo(dirname(__FILE__).'/components/'.$dir.'/'.$file);

                    if ($fileInfo['extension'] == 'php') {
                        DBG::msg("--------- updating $file ------");

                        if (!include_once(dirname(__FILE__).'/components/'.$dir.'/'.$file)) {
                            setUpdateMsg($_CORELANG['TXT_UPDATE_ERROR'], 'title');
                            setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__).'/components/'.$dir.'/'.$file));
                            return false;
                        }

                        if (!in_array($fileInfo['filename'], $missedModules)) {
                            $function = '_'.$fileInfo['filename'].'Update';
                            if (function_exists($function)) {
                                $result = $function();
                                if ($result === false) {
                                    if (empty($objUpdate->arrStatusMsg['title'])) {
                                        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $file), 'title');
                                    }
                                    return false;
                                }
                            } else {
                                setUpdateMsg($_CORELANG['TXT_UPDATE_ERROR'], 'title');
                                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UPDATE_COMPONENT_CORRUPT'], '.'.$fileInfo['filename'], $file));
                                return false;
                            }
                        } else {
                            $function = '_'.$fileInfo['filename'].'Install';
                            if (function_exists($function)) {
                                $result = $function();
                                if ($result === false) {
                                    if (empty($objUpdate->arrStatusMsg['title'])) {
                                        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $file), 'title');
                                    }
                                    return false;
                                } else {
                                    // fetch module info from components/core/module.php
                                    $arrModule = getModuleInfo($fileInfo['filename']);
                                    if ($arrModule) {
                                        try {
                                            \Cx\Lib\UpdateUtil::sql("INSERT INTO ".DBPREFIX."modules ( `id` , `name` , `description_variable` , `status` , `is_required` , `is_core` , `distributor` ) VALUES ( ".$arrModule['id']." , '".$arrModule['name']."', '".$arrModule['description_variable']."', '".$arrModule['status']."', '".$arrModule['is_required']."', '".$arrModule['is_core']."', 'Comvation AG') ON DUPLICATE KEY UPDATE `id` = `id`");
                                        } catch (\Cx\Lib\UpdateException $e) {
                                            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
                                        }
                                    } else {
                                        DBG::msg('unable to register module '.$fileInfo['filename']);
                                    }
                                }
                            } else {
                                setUpdateMsg($_CORELANG['TXT_UPDATE_ERROR'], 'title');
                                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UPDATE_COMPONENT_CORRUPT'], '.'.$fileInfo['filename'], $file));
                                return false;
                            }
                        }
                    }

                    $_SESSION['contrexx_update']['update']['done'][] = $file;
                }
            }
        } else {
            setUpdateMsg($_CORELANG['TXT_UPDATE_ERROR'], 'title');
            setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_DIR_COMPONENTS'], dirname(__FILE__).'/components/'.$dir));
            return false;
        }

        closedir($dh);
    }

    if (!in_array('coreSettings', $_SESSION['contrexx_update']['update']['done'])) {
        $result = _updateSettings();
        if ($result === false) {
            if (empty($objUpdate->arrStatusMsg['title'])) {
                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_BASIC_CONFIGURATION']), 'title');
            }
            return false;
        } else {
            $result = _writeNewConfigurationFile();
            if ($result === false) {
                return false;
            }
            $_SESSION['contrexx_update']['update']['done'][] = 'coreSettings';
        }
    }

    if (!in_array('coreModules', $_SESSION['contrexx_update']['update']['done'])) {
        if ($updateModules) {
            $result = _updateModules();
            if ($result === false) {
                if (empty($objUpdate->arrStatusMsg['title'])) {
                    setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_MODULES']), 'title');
                }
                return false;
            } else {
                $_SESSION['contrexx_update']['update']['done'][] = 'coreModules';
            }
        }
    }

    if (!in_array('coreBackendAreas', $_SESSION['contrexx_update']['update']['done'])) {
        if ($updateBackendAreas) {
            $result = _updateBackendAreas();
            if ($result === false) {
                if (empty($objUpdate->arrStatusMsg['title'])) {
                    setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_SECURITY_SYSTEM']), 'title');
                }
                return false;
            } else {
                $_SESSION['contrexx_update']['update']['done'][] = 'coreBackendAreas';
            }
        }
    }

    if (!in_array('coreModuleRepository', $_SESSION['contrexx_update']['update']['done'])) {
        if ($updateRepository) {
            $result = _updateModuleRepository();
            if ($result === false) {
                if (empty($objUpdate->arrStatusMsg['title'])) {
                    setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_MODULE_REPOSITORY']), 'title');
                }
                return false;
            } else {
                $_SESSION['contrexx_update']['update']['done'][] = 'coreModuleRepository';
            }
        }
    }
    
    $arrUpdate = $objUpdate->getLoadedVersionInfo();
    $_CONFIG['coreCmsVersion'] = $arrUpdate['cmsVersion'];

    if (!in_array('coreLicense', $_SESSION['contrexx_update']['update']['done'])) {
        $lupd = new License();
        $result = $lupd->update();
        // ignore error to allow offline installations
        /*if ($result === false) {
            if (empty($objUpdate->arrStatusMsg['title'])) {
                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_LICENSE_DATA']), 'title');
            }
            return false;
        } else {*/
            $_SESSION['contrexx_update']['update']['done'][] = 'coreLicense';
        //}
    }
    
    _response();

    return true;
}

function _response() {
    global $_ARRAYLANG;
    setUpdateMsg($_ARRAYLANG['TXT_README_MSG'], 'msg');
}

function getMissedModules() {
    $installedModules = array();
    $result = \Cx\Lib\UpdateUtil::sql('SELECT `name`, `description_variable` FROM `'.DBPREFIX.'modules` WHERE `status` = "y" ORDER BY `name` ASC');
    if ($result) {
        while (!$result->EOF) {
            $installedModules[] = $result->fields['name'];
            $result->MoveNext();
        }
    }

    $missedModules = array();
    $potentialMissedModules = array('blog', 'calendar', 'directory', 'docsys', 'egov', 'feed', 'forum', 'gallery', 'guestbook', 'livecam', 'market', 'memberdir', 'newsletter', 'podcast', 'shop', 'voting', 'downloads', 'ecard', 'jobs', 'knowledge', 'mediadir');
    foreach ($potentialMissedModules as $module) {
        if (!in_array($module, $installedModules)) {
            $missedModules[] = $module;
        }
    }

    return $missedModules;
}


function getConflictedModules($missedModules) {
    $potentialMissedTables = array(
        'blog' => array(
            DBPREFIX.'module_blog_comments',
            DBPREFIX.'module_blog_categories',
            DBPREFIX.'module_blog_messages',
            DBPREFIX.'module_blog_messages_lang',
            DBPREFIX.'module_blog_message_to_category',
            DBPREFIX.'module_blog_networks',
            DBPREFIX.'module_blog_networks_lang',
            DBPREFIX.'module_blog_settings',
            DBPREFIX.'module_blog_votes',
        ),
        'calendar' => array(
            DBPREFIX.'module_calendar',
            DBPREFIX.'module_calendar_categories',
            DBPREFIX.'module_calendar_form_data',
            DBPREFIX.'module_calendar_form_fields',
            DBPREFIX.'module_calendar_registrations',
            DBPREFIX.'module_calendar_settings',
            DBPREFIX.'module_calendar_style',
        ),
        'directory' => array(
            DBPREFIX.'module_directory_categories',
            DBPREFIX.'module_directory_dir',
            DBPREFIX.'module_directory_inputfields',
            DBPREFIX.'module_directory_levels',
            DBPREFIX.'module_directory_mail',
            DBPREFIX.'module_directory_rel_dir_cat',
            DBPREFIX.'module_directory_rel_dir_level',
            DBPREFIX.'module_directory_settings',
            DBPREFIX.'module_directory_settings_google',
            DBPREFIX.'module_directory_vote',
        ),
        'docsys' => array(
            DBPREFIX.'module_docsys',
            DBPREFIX.'module_docsys_categories',
            DBPREFIX.'module_docsys_entry_category',
        ),
        'egov' => array(
            DBPREFIX.'module_egov_configuration',
            DBPREFIX.'module_egov_orders',
            DBPREFIX.'module_egov_products',
            DBPREFIX.'module_egov_product_calendar',
            DBPREFIX.'module_egov_product_fields',
            DBPREFIX.'module_egov_settings',
        ),
        'feed' => array(
            DBPREFIX.'module_feed_category',
            DBPREFIX.'module_feed_news',
            DBPREFIX.'module_feed_newsml_association',
            DBPREFIX.'module_feed_newsml_categories',
            DBPREFIX.'module_feed_newsml_documents',
            DBPREFIX.'module_feed_newsml_providers',
        ),
        'forum' => array(
            DBPREFIX.'module_forum_access',
            DBPREFIX.'module_forum_categories',
            DBPREFIX.'module_forum_categories_lang',
            DBPREFIX.'module_forum_notification',
            DBPREFIX.'module_forum_postings',
            DBPREFIX.'module_forum_rating',
            DBPREFIX.'module_forum_settings',
            DBPREFIX.'module_forum_statistics',
        ),
        'gallery' => array(
            DBPREFIX.'module_gallery_categories',
            DBPREFIX.'module_gallery_comments',
            DBPREFIX.'module_gallery_language',
            DBPREFIX.'module_gallery_language_pics',
            DBPREFIX.'module_gallery_pictures',
            DBPREFIX.'module_gallery_settings',
            DBPREFIX.'module_gallery_votes',
        ),
        'guestbook' => array(
            DBPREFIX.'module_guestbook',
            DBPREFIX.'module_guestbook_settings',
        ),
        'livecam' => array(
            DBPREFIX.'module_livecam',
            DBPREFIX.'module_livecam_settings',
        ),
        'market' => array(
            DBPREFIX.'module_market',
            DBPREFIX.'module_market_categories',
            DBPREFIX.'module_market_mail',
            DBPREFIX.'module_market_paypal',
            DBPREFIX.'module_market_settings',
            DBPREFIX.'module_market_spez_fields'
        ),
        'memberdir' => array(
            DBPREFIX.'module_memberdir_directories',
            DBPREFIX.'module_memberdir_name',
            DBPREFIX.'module_memberdir_settings',
            DBPREFIX.'module_memberdir_values'
        ),
        'newsletter' => array(
            DBPREFIX.'module_newsletter',
            DBPREFIX.'module_newsletter_access_user',
            DBPREFIX.'module_newsletter_attachment',
            DBPREFIX.'module_newsletter_category',
            DBPREFIX.'module_newsletter_confirm_mail',
            DBPREFIX.'module_newsletter_email_link',
            DBPREFIX.'module_newsletter_email_link_feedback',
            DBPREFIX.'module_newsletter_rel_cat_news',
            DBPREFIX.'module_newsletter_rel_usergroup_newsletter',
            DBPREFIX.'module_newsletter_rel_user_cat',
            DBPREFIX.'module_newsletter_settings',
            DBPREFIX.'module_newsletter_template',
            DBPREFIX.'module_newsletter_tmp_sending',
            DBPREFIX.'module_newsletter_user',
            DBPREFIX.'module_newsletter_user_title',
        ),
        'podcast' => array(
            DBPREFIX.'module_podcast_category',
            DBPREFIX.'module_podcast_medium',
            DBPREFIX.'module_podcast_rel_category_lang',
            DBPREFIX.'module_podcast_rel_medium_category',
            DBPREFIX.'module_podcast_settings',
            DBPREFIX.'module_podcast_template',
        ),
        'shop' => array(
            DBPREFIX.'core_text',
            DBPREFIX.'core_setting',
            DBPREFIX.'core_mail_template',
            DBPREFIX.'core_country',
            DBPREFIX.'module_shop_article_group',
            DBPREFIX.'module_shop_attribute',
            DBPREFIX.'module_shop_categories',
            DBPREFIX.'module_shop_countries',
            DBPREFIX.'module_shop_currencies',
            DBPREFIX.'module_shop_customer_group',
            DBPREFIX.'module_shop_discountgroup_count_name',
            DBPREFIX.'module_shop_discountgroup_count_rate',
            DBPREFIX.'module_shop_discount_coupon',
            DBPREFIX.'module_shop_importimg',
            DBPREFIX.'module_shop_lsv',
            DBPREFIX.'module_shop_mail',
            DBPREFIX.'module_shop_mail_content',
            DBPREFIX.'module_shop_manufacturer',
            DBPREFIX.'module_shop_option',
            DBPREFIX.'module_shop_orders',
            DBPREFIX.'module_shop_order_attributes',
            DBPREFIX.'module_shop_order_items',
            DBPREFIX.'module_shop_payment',
            DBPREFIX.'module_shop_payment_processors',
            DBPREFIX.'module_shop_pricelists',
            DBPREFIX.'module_shop_products',
            DBPREFIX.'module_shop_products_downloads',
            DBPREFIX.'module_shop_rel_countries',
            DBPREFIX.'module_shop_rel_customer_coupon',
            DBPREFIX.'module_shop_rel_discount_group',
            DBPREFIX.'module_shop_rel_payment',
            DBPREFIX.'module_shop_rel_product_attribute',
            DBPREFIX.'module_shop_rel_shipper',
            DBPREFIX.'module_shop_shipment_cost',
            DBPREFIX.'module_shop_shipper',
            DBPREFIX.'module_shop_vat',
            DBPREFIX.'module_shop_zones',
        ),
        'voting' => array(
            DBPREFIX.'voting_additionaldata',
            DBPREFIX.'voting_email',
            DBPREFIX.'voting_rel_email_system',
            DBPREFIX.'voting_results',
            DBPREFIX.'voting_system',
        ),
        'downloads' => array(
            DBPREFIX.'module_downloads_category',
            DBPREFIX.'module_downloads_category_locale',
            DBPREFIX.'module_downloads_download',
            DBPREFIX.'module_downloads_download_locale',
            DBPREFIX.'module_downloads_group',
            DBPREFIX.'module_downloads_group_locale',
            DBPREFIX.'module_downloads_rel_download_category',
            DBPREFIX.'module_downloads_rel_download_download',
            DBPREFIX.'module_downloads_rel_group_category',
            DBPREFIX.'module_downloads_settings',
        ),
        'ecard' => array(
            DBPREFIX.'module_ecard_ecards',
            DBPREFIX.'module_ecard_settings',
        ),
        'jobs' => array(
            DBPREFIX.'module_jobs',
            DBPREFIX.'module_jobs_categories',
            DBPREFIX.'module_jobs_location',
            DBPREFIX.'module_jobs_rel_loc_jobs',
            DBPREFIX.'module_jobs_settings',
        ),
        'knowledge' => array(
            DBPREFIX.'module_knowledge_articles',
            DBPREFIX.'module_knowledge_article_content',
            DBPREFIX.'module_knowledge_categories',
            DBPREFIX.'module_knowledge_categories_content',
            DBPREFIX.'module_knowledge_settings',
            DBPREFIX.'module_knowledge_tags',
            DBPREFIX.'module_knowledge_tags_articles',
        ),
        'mediadir' => array(
            DBPREFIX.'module_mediadir_categories',
            DBPREFIX.'module_mediadir_categories_names',
            DBPREFIX.'module_mediadir_comments',
            DBPREFIX.'module_mediadir_entries',
            DBPREFIX.'module_mediadir_forms',
            DBPREFIX.'module_mediadir_form_names',
            DBPREFIX.'module_mediadir_inputfields',
            DBPREFIX.'module_mediadir_inputfield_names',
            DBPREFIX.'module_mediadir_inputfield_types',
            DBPREFIX.'module_mediadir_inputfield_verifications',
            DBPREFIX.'module_mediadir_levels',
            DBPREFIX.'module_mediadir_level_names',
            DBPREFIX.'module_mediadir_mails',
            DBPREFIX.'module_mediadir_mail_actions',
            DBPREFIX.'module_mediadir_masks',
            DBPREFIX.'module_mediadir_order_rel_forms_selectors',
            DBPREFIX.'module_mediadir_rel_entry_categories',
            DBPREFIX.'module_mediadir_rel_entry_inputfields',
            DBPREFIX.'module_mediadir_rel_entry_inputfields_clean1',
            DBPREFIX.'module_mediadir_rel_entry_levels',
            DBPREFIX.'module_mediadir_settings',
            DBPREFIX.'module_mediadir_settings_num_categories',
            DBPREFIX.'module_mediadir_settings_num_entries',
            DBPREFIX.'module_mediadir_settings_num_levels',
            DBPREFIX.'module_mediadir_settings_perm_group_forms',
            DBPREFIX.'module_mediadir_votes'
        ),
    );

    $conflictedModules = array();
    foreach ($missedModules as $module) {
        foreach ($potentialMissedTables[$module] as $table) {
            if (\Cx\Lib\UpdateUtil::table_exist($table)) {
                $result = \Cx\Lib\UpdateUtil::sql('SHOW TABLE STATUS WHERE `Name` = "'.$table.'"');
                if ($result && ($result->RecordCount() > 0) && (strpos($result->fields['Comment'], 'cx3upgrade') === false)) {
                    $conflictedModules[$module][] = $table;
                }
            }
        }
    }

    return $conflictedModules;
}

function _updateModuleRepository() {
    global $_CORELANG, $objUpdate, $objDatabase;

    $count = 0;

    $dh = opendir(dirname(__FILE__) . '/components/core');
    if ($dh) {

	$query = "TRUNCATE TABLE ".DBPREFIX."module_repository";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}
        
        try {
            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_repository',
                array(
                    'id'                 => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true),
                    'moduleid'           => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'id'),
                    'content'            => array('type' => 'mediumtext', 'after' => 'moduleid'),
                    'title'              => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'content'),
                    'cmd'                => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => '', 'after' => 'title'),
                    'expertmode'         => array('type' => 'SET(\'y\',\'n\')', 'notnull' => true, 'default' => 'n', 'after' => 'cmd'),
                    'parid'              => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'expertmode'),
                    'displaystatus'      => array('type' => 'SET(\'on\',\'off\')', 'notnull' => true, 'default' => 'on', 'after' => 'parid'),
                    'username'           => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'displaystatus'),
                    'displayorder'       => array('type' => 'SMALLINT(6)', 'notnull' => true, 'default' => '100', 'after' => 'username')
                ),
                array(
                    'contentid'          => array('fields' => array('id'), 'type' => 'UNIQUE'),
                    'fulltextindex'      => array('fields' => array('title','content'), 'type' => 'FULLTEXT')
                )
            );
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
        
        while (($file = readdir($dh)) !== false) {
            if (preg_match('#^repository_([0-9]+)\.php$#', $file, $arrFunction)) {
                if (!in_array($file, $_SESSION['contrexx_update']['update']['done'])) {
                    if (function_exists('memory_get_usage')) {
                        if (!checkMemoryLimit()) {
                            return false;
                        }
                    } else {
                        $count++;
                    }

                    if (!include_once(dirname(__FILE__) . '/components/core/' . $file)) {
                        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/' . $file));
                        return false;
                    }
                    $function = '_updateModuleRepository_' . $arrFunction[1];
                    if (function_exists($function)) {
                        DBG::msg("---------------------- update: calling $function() ---------");
                        $result = $function();
                        if ($result === false) {
                            if (empty($objUpdate->arrStatusMsg['title'])) {
                                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $file), 'title');
                            }
                            return false;
                        }
                    } else {
                        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UPDATE_COMPONENT_CORRUPT'], $_CORELANG['TXT_UPDATE_MODULE_REPOSITORY'], $arrFunction[1]));
                        return false;
                    }

                    $_SESSION['contrexx_update']['update']['done'][] = $file;

                    if ($count == 10) {
                        setUpdateMsg($_CORELANG['TXT_UPDATE_PROCESS_HALTED'], 'title');
                        setUpdateMsg($_CORELANG['TXT_UPDATE_PROCESS_HALTED_RAM_MSG'] . '<br /><br />', 'msg');
                        setUpdateMsg('<input type="submit" value="' . $_CORELANG['TXT_CONTINUE_UPDATE'] . '" name="updateNext" /><input type="hidden" name="processUpdate" id="processUpdate" />', 'button');
                        return false;
                    }
                }
            }
        }
    } else {
        setUpdateMsg($_CORELANG['TXT_UPDATE_UNABLE_LOAD_REPOSITORY_PARTS']);
        return false;
    }

    closedir($dh);

    return true;
}

function copyCxFilesToRoot($src, $dst)
{
    global $_CORELANG;

    static $copiedCxFilesIndex = 0;

    $approxFileCount2copy = 3809;

    $src = str_replace('\\', '/', $src);
    $dst = str_replace('\\', '/', $dst);
    $dir = opendir($src);

    $arrCurrentFolderStructure = array();
    while ($file = readdir($dir)) {
        if (!in_array($file, array('.', '..'))) {
            $arrCurrentFolderStructure[] = $file;
        }
    }
    sort($arrCurrentFolderStructure);

    if (!isset($_SESSION['contrexx_update']['copiedCxFilesTotal'])) {
        $_SESSION['contrexx_update']['copiedCxFilesTotal'] = 0;
    }

    foreach ($arrCurrentFolderStructure as $file) {
        if (!checkMemoryLimit() || !checkTimeoutLimit()) {
            $_SESSION['contrexx_update']['copiedCxFilesIndex'] = $copiedCxFilesIndex;
            setUpdateMsg('Vorgang wurde beim Installieren der neuen Dateien unterbrochen.<br />Fortschritt: '. floor(80/$approxFileCount2copy*$_SESSION['contrexx_update']['copiedCxFilesTotal']) .'%<br /><br />', 'msg');
            return false;
        }

        $srcPath = $src . '/' . $file;
        $dstPath = $dst . '/' . $file;

        if (is_dir($srcPath)) {
            \Cx\Lib\FileSystem\FileSystem::make_folder($dstPath);
            if (!copyCxFilesToRoot($srcPath, $dstPath)) {
                return false;
            }
        } else {
            $copiedCxFilesIndex++;

            if (isset($_SESSION['contrexx_update']['copiedCxFilesIndex']) && $copiedCxFilesIndex <= $_SESSION['contrexx_update']['copiedCxFilesIndex']) {
                continue;
            }

            $_SESSION['contrexx_update']['copiedCxFilesTotal']++;

            try {
                $objFile = new \Cx\Lib\FileSystem\File($srcPath);
                $objFile->copy($dstPath, true);
            } catch (\Exception $e) {
                $copiedCxFilesIndex--;
                $_SESSION['contrexx_update']['copiedCxFilesIndex'] = $copiedCxFilesIndex;
                $_SESSION['contrexx_update']['copiedCxFilesTotal']--;
                setUpdateMsg('Folgende Datei konnte nicht kopiert werden:<br />' . ASCMS_PATH_OFFSET . '/' . $file);
                return false;
            }
        }
    }

    closedir($dir);
    return true;
}


class License {
    
    public function __construct() {
        
    }
    
    public function update() {
        global $documentRoot, $sessionObj, $_CONFIG;
        
        if (!@include_once(ASCMS_DOCUMENT_ROOT.'/lib/PEAR/HTTP/Request2.php')) {
            return false;
        }

        $_GET['force'] = 'true';
        $_GET['silent'] = 'true';
        $documentRoot = ASCMS_DOCUMENT_ROOT;
        
        $userId = 1;
        $sessionObj->cmsSessionUserUpdate($userId);
        
        $_CONFIG['licenseUpdateInterval'] = 0;
        $_CONFIG['licenseSuccessfulUpdate'] = 0;
        $_CONFIG['installationId'] = '';
        $_CONFIG['licenseKey'] = '';
        $_CONFIG['licenseState'] = '';
        
        $return = @include_once(ASCMS_DOCUMENT_ROOT.'/core_modules/License/versioncheck.php');
        
        // we force a version number update. if the license update failed
        // version number will not be upgraded yet:
        \Cx\Lib\UpdateUtil::sql('UPDATE `rc1`.`contrexx_settings` SET `setvalue` = \'3.0.1\' WHERE `contrexx_settings`.`setid` = 97');
        $settingsManager = new \settingsManager();
        $settingsManager->writeSettingsFile();
        
        return ($return === true);
    }
}
